<?php

include_once("sessao.php");

$_SESSION['PaginaAtual'] = 'Editar TR';

include('global_assets/php/conexao.php');

$sql = "SELECT ParamProdutoOrcamento, ParamServicoOrcamento
		FROM Parametro
		WHERE ParamEmpresa = " . $_SESSION['EmpreId'];
$result = $conn->query($sql);
$rowParametro = $result->fetch(PDO::FETCH_ASSOC);

isset($rowParametro['ParamProdutoOrcamento']) && $rowParametro['ParamProdutoOrcamento'] == 1 ? $parametroProduto = 'ProdutoOrcamento' : $parametroProduto = 'Produto';
isset($rowParametro['ParamServicoOrcamento']) && $rowParametro['ParamServicoOrcamento'] == 1 ? $parametroServico = 'ServicoOrcamento' : $parametroServico = 'Servico';
$aSubCategorias = [];

if (isset($_POST['inputTRId'])) {

	$iTR = $_POST['inputTRId'];
	$unidade = $_SESSION['UnidadeId'];
	$empresa = $_SESSION['EmpreId'];
	$userId = $_SESSION['UsuarId'];

	$sqlUsuarioEquipe = "SELECT TRXEqTermoReferencia, TRXEqUsuario, TRXEqPresidente, TRXEqUnidade
					 FROM TRXEquipe
					 WHERE TRXEqUsuario = $userId and TRXEqUnidade = $unidade and TRXEqTermoReferencia = $iTR";
	$resultUsuarioEquipe = $conn->query($sqlUsuarioEquipe);
	$UsuarioEquipe = $resultUsuarioEquipe->fetchAll(PDO::FETCH_ASSOC);

	$sql = "SELECT TrRefId, TrRefNumero, TrRefData, TrRefCategoria, TrRefConteudoInicio, TrRefConteudoFim, TrRefTipo, SituaChave
			FROM TermoReferencia
			JOIN Situacao on SituaId = TrRefStatus
			WHERE TrRefId = $iTR ";
	$result = $conn->query($sql);
	$row = $result->fetch(PDO::FETCH_ASSOC);

	$sql = "SELECT SbCatId, SbCatNome
			 FROM SubCategoria
			 JOIN TRXSubcategoria on TRXSCSubcategoria = SbCatId
			 WHERE SbCatEmpresa = $empresa and TRXSCTermoReferencia = $iTR
			 ORDER BY SbCatNome ASC";
	$result = $conn->query($sql);
	$rowBD = $result->fetchAll(PDO::FETCH_ASSOC);
	foreach ($rowBD as $item) {
		$aSubCategorias[] = $item['SbCatId'];
	}

	$sql = "SELECT *
	        FROM TRXOrcamento
	        WHERE TrXOrTermoReferencia = " . $row['TrRefId'];
	$result = $conn->query($sql);
	$rowTrOr = $result->fetchAll(PDO::FETCH_ASSOC);

	$_SESSION['msg'] = array();
} else {  //Esse else foi criado para se caso o usuário der um REFRESH na página. Nesse caso não terá POST e campos não reconhecerão o $row da consulta acima (daí ele deve ser redirecionado) e se quiser continuar editando terá que clicar no ícone da Grid novamente

	irpara("tr.php");
}

//Se está alterando
if (isset($_POST['inputTRData'])) {

	try {

		$tipoTr = '';

		if (isset($_POST['TrProduto']) && isset($_POST['TrServico'])) {
			$tipoTr = 'PS';
		} else if (isset($_POST['TrProduto'])) {
			$tipoTr = 'P';
		} else if (isset($_POST['TrServico'])) {
			$tipoTr = 'S';
		}

		$conn->beginTransaction();

		//Se tiver orçamento já cadastrado para essa TR grava apenas os Conteúdos Personalizados
		if (count($rowTrOr) >= 1) {
			
			$sql = "UPDATE TermoReferencia SET TrRefConteudoInicio = :sConteudoInicio, TrRefConteudoFim = :sConteudoFim, 
					TrRefUsuarioAtualizador = :iUsuarioAtualizador
					WHERE TrRefId = :iTR";
			$result = $conn->prepare($sql);

			$result->execute(array(
				':sConteudoInicio' => $_POST['txtareaConteudoInicio'],
				':sConteudoFim' => $_POST['txtareaConteudoFim'],
				':iUsuarioAtualizador' => $_SESSION['UsuarId'],
				':iTR' => $iTR
			));
		} else {

			$sql = "UPDATE TermoReferencia SET TrRefCategoria = :iCategoria, TrRefConteudoInicio = :sConteudoInicio, 
					TrRefConteudoFim = :sConteudoFim, TrRefTipo = :sTipo, TrRefUsuarioAtualizador = :iUsuarioAtualizador
					WHERE TrRefId = :iTR";
			$result = $conn->prepare($sql);

			$result->execute(array(
				':iCategoria' => $_POST['cmbCategoria'],
				':sConteudoInicio' => $_POST['txtareaConteudoInicio'],
				':sConteudoFim' => $_POST['txtareaConteudoFim'],
				':sTipo' => $tipoTr,
				':iUsuarioAtualizador' => $_SESSION['UsuarId'],
				':iTR' => $iTR
			));

			//Compara 2 arrays (se houve alteração na SubCategoria)
			if (isset($_POST['cmbSubCategoria'])) {
				if (count($aSubCategorias) != count($_POST['cmbSubCategoria']) || array_diff($aSubCategorias, $_POST['cmbSubCategoria'])) {

					$sql = "DELETE FROM TRXSubcategoria
							WHERE TRXSCTermoReferencia = :iTermoReferencia and TRXSCUnidade = :iUnidade";
					$result = $conn->prepare($sql);

					$result->execute(array(
						':iTermoReferencia' => $_POST['inputTRId'],
						':iUnidade' => $_SESSION['UnidadeId']
					));

					$possuiSubCategoria = 0;

					if (isset($_POST['cmbSubCategoria']) and $_POST['cmbSubCategoria'][0] != "") {

						$possuiSubCategoria = 1;

						$sql = "INSERT INTO TRXSubcategoria
									(TRXSCTermoReferencia, TRXSCSubcategoria, TRXSCUnidade)
								VALUES 
									(:iTermoReferencia, :iTrSubCategoria, :iTrUnidade)";
						$result = $conn->prepare($sql);

						foreach ($_POST['cmbSubCategoria'] as $key => $value) {

							$result->execute(array(
								':iTermoReferencia' => $_POST['inputTRId'],
								':iTrSubCategoria' => $value,
								':iTrUnidade' => $_SESSION['UnidadeId']
							));
						}
					}

					// Excluindo os produtos de TermoReferenciaXProduto atrelados a esta TR.
					$sql = "DELETE FROM TermoReferenciaXProduto
							WHERE TRXPrTermoReferencia = :iTr and TRXPrUnidade = :iUnidade";
					$result = $conn->prepare($sql);

					$result->execute(array(
						':iTr' => $iTR,
						':iUnidade' => $_SESSION['UnidadeId']
					));

					// Excluindo os serviços de TermoReferenciaXServico atrelados a esta TR.
					$sql = "DELETE FROM TermoReferenciaXServico
							WHERE TRXSrTermoReferencia = :iTr and TRXSrUnidade = :iUnidade";
					$result = $conn->prepare($sql);

					$result->execute(array(
						':iTr' => $iTR,
						':iUnidade' => $_SESSION['UnidadeId']
					));

					//Se for Produto e Serviço
					if ($tipoTr == 'PS') {

						//Gravando os Produtos
						include("trGravaProduto.php");

						// Gravando os Serviços
						include("trGravaServico.php");
					} else if ($tipoTr == 'P') {

						//Gravando os Produtos
						include("trGravaProduto.php");
					} else if ($tipoTr == 'S') {

						// Gravando os Serviços
						include("trGravaServico.php");
					}
				}
			}

		}

		$sql = "INSERT INTO AuditTR ( AdiTRTermoReferencia, AdiTRDataHora, AdiTRUsuario, AdiTRTela, AdiTRDetalhamento)
				VALUES (:iTRTermoReferencia, :iTRDataHora, :iTRUsuario, :iTRTela, :iTRDetalhamento)";
		$result = $conn->prepare($sql);
				
		$result->execute(array(
			':iTRTermoReferencia' => $iTR ,
			':iTRDataHora' => date("Y-m-d H:i:s"),
			':iTRUsuario' => $_SESSION['UsuarId'],
			':iTRTela' =>'TERMO DE REFERÊNCIA',
			':iTRDetalhamento' =>'MODIFICAÇÃO DO REGISTRO'
		));


		$conn->commit();

		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Termo de Referência alterado!!!";
		$_SESSION['msg']['tipo'] = "success";
	} catch (PDOException $e) {

		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao alterar Termo de Referência!!!";
		$_SESSION['msg']['tipo'] = "error";

		$conn->rollback();

		echo 'Error: ' . $e->getMessage();
		exit;
	}

	irpara("tr.php");
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Termo de Referência</title>

	<?php include_once("head.php"); ?>

	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>

	<script src="global_assets/js/demo_pages/form_layouts.js"></script>
	<script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>

	<script src="global_assets/js/plugins/editors/summernote/summernote.min.js"></script>
	<script src="global_assets/js/demo_pages/form_checkboxes_radios.js"></script>

	<!-- Validação -->
	<script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
	<script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
	<script src="global_assets/js/demo_pages/form_validation.js"></script>
	<!-- /theme JS files -->

	<!-- Adicionando Javascript -->
	<script type="text/javascript">
		$(document).ready(function() {

			function validarSubcategoria(inputValida) {
				confirmaExclusao(document.formTR, "Existem produtos com quantidades ou valores lançados em orçamentos dessa TR, portanto, a Categoria e Subcategoria não podem ser alteradas. Apenas alterações no Conteúdo Personalizado são permitidas. Confirmar alteração?", "trEdita.php");
			}

			//Inicializa o editor de texto que será usado pelos campos "Conteúdo Personalizado - Inicialização" e "Conteúdo Personalizado - Finalização"
			$('#summernoteInicio').summernote();
			$('#summernoteFim').summernote();

			//Ao mudar a categoria, filtra a subcategoria e produto via ajax (retorno via JSON)
			$('#cmbCategoria').on('change', function(e) {

				Filtrando();

				var cmbCategoria = $('#cmbCategoria').val();

				$.getJSON('filtraSubCategoria.php?idCategoria=' + cmbCategoria, function(dados) {

					var option = '<option value="#">Selecione a SubCategoria</option>';

					if (dados.length) {

						$.each(dados, function(i, obj) {
							option += '<option value="' + obj.SbCatId + '">' + obj.SbCatNome + '</option>';
						});

						$('#cmbSubCategoria').html(option).show();
					} else {
						ResetSubCategoria();
					}
				});

			});

			if ($('#inputValidar').val() >= 1) {
				$('.select2-selection__choice__remove').each((i, elem) => {
					console.log($(elem).remove());
				})

				setInterval(() => {
					if ($('.select2-container--default').hasClass('select2-container--open')) {
						$('.select2-container--default').removeClass('select2-container--open');
					}
				}, 1)
			}

			$("#enviar").on('click', function(e) {

				e.preventDefault();

				//Antes
				var inputCategoria = $('#inputTRCategoria').val();
				var inputSubCategoria = $('#inputTRSubCategoria').val();
				if (inputSubCategoria == '' || inputSubCategoria == null) {
					inputSubCategoria = '#';
				}

				var TrProduto = document.getElementById("TrProduto");
				var TrServico = document.getElementById("TrServico");

				if (!TrProduto.checked && !TrServico.checked) {
					alerta('Atenção', 'Informe se o Termo de Referência terá Produtos e/ou Serviços!', 'error');
					$('#TrProduto').focus();
					return false;
				}

				//Depois
				var cmbCategoria = $('#cmbCategoria').val();
				var cmbSubCategoria = $('#cmbSubCategoria').val();

				//Tem produto cadastrado para esse TR na tabela TermoReferenciaXProduto?
				var inputProduto = $('#inputTRProduto').val();

				//Exclui os produtos desse TR?
				var inputExclui = $('#inputTRProdutoExclui').val();

				if ($('#inputValidar').val() >= 1) {
					validarSubcategoria($('#inputValidar').val())

					$('.select2-selection__choice__remove').each((i, elem) => {
						console.log($(elem).remove());
					})

					setInterval(() => {
						if ($('.select2-container--default').hasClass('select2-container--open')) {
							$('.select2-container--default').removeClass('select2-container--open');
						}
					}, 1)
				}

				let tipoTr = '';
				let tipoMensagem = '';
				let subCategMensagem = '';

				if ($('#TrProduto').parent().hasClass('checked')) {
					tipoTr = 'P';
				}
				if ($('#TrServico').parent().hasClass('checked')) {
					tipoTr = 'S'
				}
				if ($('#TrProduto').parent().hasClass('checked') && $('#TrServico').parent().hasClass('checked')) {
					tipoTr = 'PS'
				}

				let cmbCategoriaId = $('#cmbCategoria').val();
				let cmbSubCategoriaArray = $('#cmbSubCategoria').val()

				if (cmbCategoriaId){

					$.post(
						"trVerificaProdutoServico.php", {
							tipoTr: tipoTr,
							cmbCategoriaId: cmbCategoriaId,
							cmbSubCategoriaArray: cmbSubCategoriaArray
						},
						function(resposta) {

							tipoTr == 'P' ? tipoMensagem = 'produtos' : tipoTr == 'S' ? tipoMensagem = 'serviços' : tipoMensagem = 'produtos ou serviços'

							cmbSubCategoriaArray != '' ? subCategMensagem = 'e subactegoria selecionadas não possuem' : subCategMensagem = 'selecionada não possui'

							if (resposta == 'existem produtos') {
								$("#formTR").submit();
							} else {
								alerta('Atenção', `A categoria ${subCategMensagem} ${tipoMensagem} ativos!`, 'error');
							}
						}
					);
				} else {
					$("#formTR").submit();					
				}

			}); // enviar			
		}); //document.ready

		//Mostra o "Filtrando..." na combo SubCategoria
		function Filtrando() {
			$('#cmbSubCategoria').empty().append('<option value="">Filtrando...</option>');
		}

		function ResetSubCategoria() {
			$('#cmbSubCategoria').empty().append('<option value="">Sem Subcategoria</option>');
		}
	</script>

</head>

<body class="navbar-top">

	<?php include_once("topo.php"); ?>

	<!-- Page content -->
	<div class="page-content">

		<?php include_once("menu-left.php"); ?>

		<!-- Main content -->
		<div class="content-wrapper">

			<?php include_once("cabecalho.php"); ?>

			<!-- Content area -->
			<div class="content">

				<!-- Info blocks -->
				<div class="card">

					<form name="formTR" id="formTR" method="post" class="form-validate-jquery">
						<input id="inputTRId" type="hidden" name="inputTRId" value="<?php echo $row['TrRefId'] ?>">
						<input id="inputTRNumero" type="hidden" name="inputTRNumero" value="<?php echo $row['TrRefNumero'] ?>">
						<div class="card-header header-elements-inline">
							<h5 class="text-uppercase font-weight-bold">Editar TR Nº "<?php echo $_POST['inputTRNumero']; ?>"</h5>
						</div>
						<?php
						$countExt = 0;
						foreach ($rowTrOr as $Orcamento) {
							if ($Orcamento) {
								$sql = "SELECT TXOXPValorUnitario
                                       FROM TRXOrcamentoXProduto
                                       WHERE TXOXPOrcamento = " . $Orcamento['TrXOrId'] . "";
								$result = $conn->query($sql);
								$rowOrPr = $result->fetchAll(PDO::FETCH_ASSOC);
								$countInt = 0;
								foreach ($rowOrPr as $produto) {
									if ($produto['TXOXPValorUnitario']) {
										$countInt++;
									}
								}

								if ($countInt > 0) {
									$countExt++;
								}
							}
						}
						if ($countExt >= 1) {
							print('<input type="hidden" id="inputValidar" name="inputValidar" value="' . $countExt . ' "  >');
							if ($rowBD) {
								foreach ($rowBD as $subcategoria) {
									print('<input type="hidden" class="inputSubCategoriaValidacao" name="inputSubCategoriaValidacao" value="' . $subcategoria['SbCatId'] . ' "  >');
								}
							}
						}
						?>

						<?php

						$sql = "SELECT TRXPrTermoReferencia
								FROM TermoReferenciaXProduto
								WHERE TRXPrTermoReferencia = " . $iTR . " and TRXPrUnidade = " . $_SESSION['UnidadeId'];
						$result = $conn->query($sql);
						$rowProduto = $result->fetchAll(PDO::FETCH_ASSOC);
						$countProduto = count($rowProduto);

						print('<input type="hidden" id="inputTRProduto" name="inputTRProduto" value="' . $countProduto . '" >');
						?>

						<div class="card-body">

							<div class="row">
								<div class="col-lg-12">
									<div class="row">

										<div class="col-lg-2">
											<div class="form-group">
												<label for="inputData">Data</label>
												<input type="text" id="inputData" name="inputTRData" class="form-control" value="<?php echo mostraData($row['TrRefData']); ?>" readOnly>
											</div>
										</div>
										<div class="col-lg-3">
											<div class="form-group">
												<label for="inputTipo">O Termo de Referência terá: <span class="text-danger">*</span></label>
												<div class="d-flex flex-row">
													<?php

													$disabled = "";

													if (count($rowTrOr) >= 1) {
														$disabled = " disabled ";
													}

													if ($row['TrRefTipo'] == 'PS') {
														print('
															 <div class="p-1 m-0 d-flex flex-row">
																 <input id="TrProduto" value="P" name="TrProduto" class="form-check-input-styled" type="checkbox" checked ' . $disabled . '>
																 <label for="TrProduto" class="ml-1" style="margin-bottom: 2px">Produto</label>
															 </div>
															 <div class="p-1 m-0 d-flex flex-row">
																 <input id="TrServico" value="S" name="TrServico" class="form-check-input-styled" type="checkbox" checked ' . $disabled . '>
																 <label for="TrServico" class="ml-1" style="margin-bottom: 2px">Serviço</label>
															 </div>
														');
													} else if ($row['TrRefTipo'] == 'P') {
														print('
															 <div class="p-1 m-0 d-flex flex-row">
																 <input id="TrProduto" value="P" name="TrProduto" class="form-check-input-styled" type="checkbox" checked ' . $disabled . '> 
																 <label for="TrProduto" class="ml-1" style="margin-bottom: 2px">Produto</label>
															 </div>
															 <div class="p-1 m-0 d-flex flex-row">
																 <input id="TrServico" value="S" name="TrServico" class="form-check-input-styled" type="checkbox" ' . $disabled . '>
																 <label for="TrServico" class="ml-1" style="margin-bottom: 2px">Serviço</label>
															 </div>
														 ');
													} else if ($row['TrRefTipo'] == 'S') {
														print('
															 <div class="p-1 m-0 d-flex flex-row">
																 <input id="TrProduto" value="P" name="TrProduto" class="form-check-input-styled" type="checkbox" ' . $disabled . '>
																 <label for="TrProduto" class="ml-1" style="margin-bottom: 2px">Produto</label>
															 </div>
															 <div class="p-1 m-0 d-flex flex-row">
																 <input id="TrServico" value="S" name="TrServico" class="form-check-input-styled" type="checkbox" checked ' . $disabled . '>
																 <label for="TrServico" class="ml-1" style="margin-bottom: 2px">Serviço</label>
															 </div>
														 ');
													}

													?>
												</div>
											</div>
										</div>
										<div class="col-lg-3">
											<div class="form-group">
												<label for="cmbCategoria">Categoria <span class="text-danger">*</span></label>
												<?php
												if (count($rowTrOr) >= 1) {
													print('<select id="cmbCategoria" name="cmbCategoria" class="form-control form-control-select2" value="' . $row['TrRefCategoria'] . '" disabled>');
												} else {
													print('<select id="cmbCategoria" name="cmbCategoria" class="form-control form-control-select2" required>');
												}
												?>
												<option value="">Selecione</option>
												<?php
												$sql = "SELECT CategId, CategNome
															FROM Categoria
															JOIN Situacao on SituaId = CategStatus
															WHERE CategEmpresa = ".$_SESSION['EmpreId']." and SituaChave = 'ATIVO'
														    ORDER BY CategNome ASC";
												$result = $conn->query($sql);
												$rowCategoria = $result->fetchAll(PDO::FETCH_ASSOC);

												foreach ($rowCategoria as $item) {
													$seleciona = $item['CategId'] == $row['TrRefCategoria'] ? "selected" : "";
													print('<option value="' . $item['CategId'] . '" ' . $seleciona . '>' . $item['CategNome'] . '</option>');
												}
												?>
												</select>
											</div>
										</div>

										<div class="col-lg-4">
											<div class="form-group" style="border-bottom:1px solid #ddd;">
												<label for="cmbSubCategoria">SubCategoria</label>
												<?php
												if (count($rowTrOr) >= 1) {
													print('<select id="cmbSubCategoria" name="cmbSubCategoria[]" class="form-control select form-control-select2" multiple="multiple" data-fouc disabled>');
												} else {
													print('<select id="cmbSubCategoria" name="cmbSubCategoria[]" class="form-control select form-control-select2" multiple="multiple" data-fouc>');
												}
												?>
												<?php
												if (isset($row['TrRefCategoria'])) {
													$sql = "SELECT SbCatId, SbCatNome
													        FROM SubCategoria
													        JOIN Situacao on SituaId = SbCatStatus
													        WHERE SbCatEmpresa = " . $_SESSION['EmpreId'] . " and SbCatCategoria = " . $row['TrRefCategoria'] . " and SituaChave = 'ATIVO'
													        ORDER BY SbCatNome ASC";
													$result = $conn->query($sql);
													$rowSubCategoria = $result->fetchAll(PDO::FETCH_ASSOC);
													$count = count($rowSubCategoria);

													if ($count) {
														foreach ($rowSubCategoria as $item) {
															$seleciona = in_array($item['SbCatId'], $aSubCategorias) ? "selected" : "";
															print('<option value="' . $item['SbCatId'] . '" ' . $seleciona . '>' . $item['SbCatNome'] . '</option>');
														}
													}
												}
												?>
												</select>
											</div>
										</div>

									</div>
								</div>
							</div>

							<div class="row">
								<div class="col-lg-12">
									<div class="form-group">
										<label for="txtareaConteudoInicio">Conteúdo personalizado - Introdução</label>
										<textarea rows="5" cols="5" class="form-control" id="summernoteInicio" name="txtareaConteudoInicio" placeholder="Corpo do TR (informe aqui o texto que você queira que apareça no TR)"><?php echo $row['TrRefConteudoInicio']; ?></textarea>
									</div>
								</div>
							</div>
							<br>

							<div class="row">
								<div class="col-lg-12">
									<div class="form-group">
										<label for="txtareaConteudoFim">Conteúdo personalizado - Finalização</label>
										<textarea rows="5" cols="5" class="form-control" id="summernoteFim" name="txtareaConteudoFim" placeholder="Considerações Finais da TR (informe aqui o texto que você queira que apareça no término da TR)"><?php echo $row['TrRefConteudoFim']; ?></textarea>
									</div>
								</div>
							</div>
							<br>

							<div class="row" style="margin-top: 10px;">
								<div class="col-lg-12">
									<div class="form-group">
										<?php
											// verifica se o status é diferente de "FASEINTERNAFINALIZADA" caso sim verifica
											// se o usuário tem permissão de acesso caso não verifica se ele é o diretor
											
											// esse array contem os PerfiChave que não podem permitir alteração
											$status = [
												'LIBERADOCONTABILIDADE',
												'FASEINTERNAFINALIZADA',
												'AGUARDANDOFINALIZACAO'
											];
											$dretor = isset($UsuarioEquipe['TRXEqPresidente'])?$UsuarioEquipe['TRXEqPresidente']:0;
											$permission = !in_array($row['SituaChave'], $status)?
											($_POST['inputPermission']?true:($diretor?true:false)):false;

											if ($permission) {
												print('<button type="submit" class="btn btn-lg btn-principal" id="enviar">Alterar</button>');
											}
										?>
										<a href="tr.php" class="btn btn-basic" role="button">Cancelar</a>
									</div>
								</div>
							</div>
						</div>
						<!-- /card-body -->
					</form>

				</div>
				<!-- /info blocks -->

			</div>
			<!-- /content area -->

			<?php include_once("footer.php"); ?>

		</div>
		<!-- /main content -->

	</div>
	<!-- /page content -->
</body>

</html>