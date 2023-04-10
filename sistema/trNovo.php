<?php

include_once("sessao.php");

$_SESSION['PaginaAtual'] = 'Novo Termo de Referência';

include('global_assets/php/conexao.php');

$sql = "SELECT ParamProdutoOrcamento, ParamServicoOrcamento
		FROM Parametro
		WHERE ParamEmpresa = " . $_SESSION['EmpreId'];
$result = $conn->query($sql);
$rowParametro = $result->fetch(PDO::FETCH_ASSOC);

isset($rowParametro['ParamProdutoOrcamento']) && $rowParametro['ParamProdutoOrcamento'] == 1 ? $parametroProduto = 'ProdutoOrcamento' : $parametroProduto = 'Produto';
isset($rowParametro['ParamServicoOrcamento']) && $rowParametro['ParamServicoOrcamento'] == 1 ? $parametroServico = 'ServicoOrcamento' : $parametroServico = 'Servico';

//Se está incluindo
if (isset($_POST['inputData'])) {

	$tipoTr = '';

	if (isset($_POST['TrProduto']) && isset($_POST['TrServico'])) {
		$tipoTr = 'PS';
	} else if (isset($_POST['TrProduto'])) {
		$tipoTr = 'P';
	} else if (isset($_POST['TrServico'])) {
		$tipoTr = 'S';
	}

	try {

		$conn->beginTransaction();

		//Gera o novo Número (incremental)
		$sql = "SELECT TOP 1 isnull(TrRefNumero,0) as Numero
			 	FROM TermoReferencia
			 	Where TrRefUnidade = " . $_SESSION['UnidadeId'] . "
			 	Order By TrRefNumero desc";
		$result = $conn->query($sql);
		$rowNumero = $result->fetch(PDO::FETCH_ASSOC);

		$sNumero = (int) $rowNumero['Numero'] + 1;
		$sNumero = str_pad($sNumero, 6, "0", STR_PAD_LEFT);

		$sql = "
			INSERT 
				INTO TermoReferencia (
							TrRefNumero, 
							TrRefData, 
							TrRefCategoria, 
							TrRefConteudoInicio, 
							TrRefConteudoFim, 
							TrRefTipo,
							TrRefStatus, 
							TrRefUsuarioAtualizador, 
							TrRefUnidade, 
							TrRefTabelaProduto,
							TrRefTabelaServico,
							TrRefLiberaParcial,
							TrRefEmpresa
						)
			VALUES (
				:sNumero, 
				:dData, 
				:iCategoria, 
				:sConteudoInicio, 
				:sConteudoFim, 
				:sTipo, 
				:bStatus, 
				:iUsuarioAtualizador, 
				:iUnidade, 
				:sTabelaProduto, 
				:sTabelaServico,
				:bLiberaParcial,
				:iEmpresa
			)
		";
		$result = $conn->prepare($sql);

		$result->execute(array(
			':sNumero' => $sNumero,
			':dData' => gravaData($_POST['inputData']),
			':iCategoria' => $_POST['cmbCategoria'],
			':sConteudoInicio' => $_POST['txtareaConteudoInicio'],
			':sConteudoFim' => $_POST['txtareaConteudoFim'],
			':sTipo' => $tipoTr,
			':bStatus' => 7,
			':iUsuarioAtualizador' => $_SESSION['UsuarId'],
			':iUnidade' => $_SESSION['UnidadeId'],
			':sTabelaProduto' => $parametroProduto,
			':sTabelaServico' => $parametroServico,
			':bLiberaParcial' => 0,
			':iEmpresa' => $_SESSION['EmpreId']
		));

		// Começo do cadastro de subcategorias da TR
		$insertId = $conn->lastInsertId();

		$possuiSubCategoria = 0;

		if (isset($_POST['cmbSubCategoria']) and $_POST['cmbSubCategoria'][0] != "") {

			$possuiSubCategoria = 1;

			$sql = "
				INSERT INTO TRXSubcategoria
						(TRXSCTermoReferencia, TRXSCSubCategoria, TRXSCUnidade)
					VALUES 
						(:iTermoReferencia, :iSubCategoria, :iUnidade)";
			$result = $conn->prepare($sql);

			foreach ($_POST['cmbSubCategoria'] as $key => $value) {

				$result->execute(array(
					':iTermoReferencia' => $insertId,
					':iSubCategoria' => $value,
					':iUnidade' => $_SESSION['UnidadeId']
				));
			}
		}

		$sql = "INSERT INTO AuditTR ( AdiTRTermoReferencia, AdiTRDataHora, AdiTRUsuario, AdiTRTela, AdiTRDetalhamento)
				VALUES (:iTRTermoReferencia, :iTRDataHora, :iTRUsuario, :iTRTela, :iTRDetalhamento)";
		$result = $conn->prepare($sql);
				
		$result->execute(array(
			':iTRTermoReferencia' => $insertId ,
			':iTRDataHora' => date("Y-m-d H:i:s"),
			':iTRUsuario' => $_SESSION['UsuarId'],
			':iTRTela' =>'TERMO DE REFERÊNCIA',
			':iTRDetalhamento' =>'INCLUSÃO DO REGISTRO'
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

		$conn->commit();

		// Fim de cadastro

		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Termo de referência incluído!!!";
		$_SESSION['msg']['tipo'] = "success";
	} catch (PDOException $e) {

		$conn->rollback();

		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao incluir termo de referência!!!";
		$_SESSION['msg']['tipo'] = "error";

		echo 'Error1: ' . $e->getMessage();
		die;
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

			//Inicializa o editor de texto que será usado pelos campos "Conteúdo Personalizado - Inicialização" e "Conteúdo Personalizado - Finalização"
			$('#summernoteInicio').summernote();
			$('#summernoteFim').summernote();

			//Ao mudar a categoria, filtra a subcategoria e produto via ajax (retorno via JSON)
			$('#cmbCategoria').on('change', function(e) {

				Filtrando();

				var cmbCategoria = $('#cmbCategoria').val();

				$.getJSON('filtraSubCategoria.php?idCategoria=' + cmbCategoria, function(dados) {

					var option = null;

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

			$('#enviar').on('click', function(e) {

				e.preventDefault();

				var TrProduto = document.getElementById("TrProduto");
				var TrServico = document.getElementById("TrServico");
				/*				var parametroProduto = $('#parametroProduto').val() == 'ProdutoOrcamento' ? 1 : 0;
								var parametroServico = $('#parametroServico').val() == 'ServicoOrcamento' ? 1 : 0;
								var cmbCategoria = $('#cmbCategoria').val();
								var cmbSubCategoria = $('#cmbSubCategoria').val() != '' ? $('#cmbSubCategoria').val() : 0; */

				if (!TrProduto.checked && !TrServico.checked) {
					alerta('Atenção', 'Informe se o Termo de Referência terá Produtos e/ou Serviços!', 'error');
					$('#TrProduto').focus();
					return false;
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
								alerta('Atenção', 'A categoria ' + subCategMensagem + ' ' + tipoMensagem + ' ativos!', 'error');
							
							}
						}
					);
				} else {
					$("#formTR").submit();
				}
			});
		}); //document.ready

		//Mostra o "Filtrando..." na combo SubCategoria
		function Filtrando() {
			$('#cmbSubCategoria').empty().append('<option value="">Filtrando...</option>');
		}

		function ResetSubCategoria() {
			$('#cmbSubCategoria').empty().append('<p>Sem Subcategoria</>');
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

					<form name="formTR" id="formTR" method="post" action="trNovo.php" class="form-validate-jquery">
						<div class="card-header header-elements-inline">
							<h5 class="text-uppercase font-weight-bold">Cadastrar Novo Termo de Referência</h5>
						</div>

						<input type="hidden" id="parametroProduto" name="parametroProduto" value="<?php echo $parametroProduto; ?>">
						<input type="hidden" id="parametroServico" name="parametroServico" value="<?php echo $parametroServico; ?>">

						<div class="card-body">

							<div class="row">
								<div class="col-lg-12">
									<div class="row">

										<div class="col-lg-2">
											<div class="form-group">
												<label for="inputData">Data</label>
												<input type="text" id="inputData" name="inputData" class="form-control" value="<?php echo date('d/m/Y'); ?>" readOnly>
											</div>
										</div>
										<div class="col-lg-3">
											<div class="form-group">
												<label for="inputData">O Termo de Referência terá: <span class="text-danger">*</span></label>
												<div class="d-flex flex-row">
													<div class="p-1 m-0 d-flex flex-row">
														<input name="TrProduto" id="TrProduto" value="P" class="form-check-input-styled" type="checkbox">
														<label for="TrProduto" class="ml-1" style="margin-bottom: 2px">Produto</label>
													</div>
													<div class="p-1 m-0 d-flex flex-row">
														<input name="TrServico" id="TrServico" value="S" class="form-check-input-styled" type="checkbox">
														<label for="TrServico" class="ml-1" style="margin-bottom: 2px">Serviço</label>
													</div>
												</div>
											</div>
										</div>

										<div class="col-lg-3">
											<div class="form-group">
												<label for="cmbCategoria">Categoria <span class="text-danger">*</span></label>
												<select id="cmbCategoria" name="cmbCategoria" class="form-control form-control-select2" required>
													<option value="">Selecione</option>
													<?php
													$sql = "SELECT CategId, CategNome
															FROM Categoria
															JOIN Situacao on SituaId = CategStatus
															WHERE CategEmpresa = ".$_SESSION['EmpreId']." and SituaChave = 'ATIVO'
															ORDER BY CategNome ASC";
													$result = $conn->query($sql);
													$row = $result->fetchAll(PDO::FETCH_ASSOC);

													foreach ($row as $item) {
														print('<option value="' . $item['CategId'] . '">' . $item['CategNome'] . '</option>');
													}

													?>
												</select>
											</div>
										</div>

										<div class="col-lg-4">
											<div class="form-group" style="border-bottom:1px solid #ddd;">
												<label for="cmbSubCategoria">SubCategoria</label>
												<select id="cmbSubCategoria" name="cmbSubCategoria[]" class="form-control form-control-select2 select" multiple="multiple" data-fouc>
												</select>
											</div>
										</div>

									</div>
								</div>
							</div>

							<div class="row">
								<div class="col-lg-12">
									<div class="form-group">
										<label for="txtareaConteudo">Conteúdo Personalizado - Introdução</label>
										<!--<div id="summernote" name="txtareaConteudo"></div>-->
										<textarea rows="5" cols="5" class="form-control" id="summernoteInicio" name="txtareaConteudoInicio" placeholder="Corpo da TR (informe aqui o texto que você queira que apareça na TR)"></textarea>
									</div>
								</div>
							</div>
							<br>

							<div class="row">
								<div class="col-lg-12">
									<div class="form-group">
										<label for="txtareaConteudoFinalizacao">Conteúdo Personalizado - Finalização</label>
										<!--<div id="summernote" name="txtareaConteudo"></div>-->
										<textarea rows="5" cols="5" class="form-control" id="summernoteFim" name="txtareaConteudoFim" placeholder="Considerações Finais da TR (informe aqui o texto que você queira que apareça no término da TR)"></textarea>
									</div>
								</div>
							</div>
							<br>

							<div class="row" style="margin-top: 10px;">
								<div class="col-lg-12">
									<div class="form-group">
										<button class="btn btn-lg btn-principal" id="enviar">Incluir</button>
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