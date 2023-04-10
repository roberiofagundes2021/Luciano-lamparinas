<?php

include_once("sessao.php");

$_SESSION['PaginaAtual'] = 'Novo Orçamento';

include('global_assets/php/conexao.php');

$sql = "SELECT UsuarId, UsuarNome, UsuarEmail, UsuarTelefone
		FROM Usuario
		Where UsuarId = " . $_SESSION['UsuarId'] . "
		ORDER BY UsuarNome ASC";
$result = $conn->query($sql);
$rowUsuario = $result->fetch(PDO::FETCH_ASSOC);

//////////////////////////////////////////////////////////////

$sql = "SELECT TrRefCategoria
		FROM TermoReferencia
		JOIN Categoria on CategId = TrRefCategoria
		WHERE TrRefUnidade = " . $_SESSION['UnidadeId'] . " and TrRefId = " . $_SESSION['TRId'] . "";
$result = $conn->query($sql);
$categoriaId = $result->fetch(PDO::FETCH_ASSOC);

$sql = "SELECT CategId, CategNome
		FROM Categoria															     
		WHERE CategEmpresa = ".$_SESSION['EmpreId']." and CategId = " . $categoriaId['TrRefCategoria'] . " and CategStatus = 1";
$result = $conn->query($sql);
$rowCategoria = $result->fetch(PDO::FETCH_ASSOC);


$sql = "SELECT SbCatId, SbCatNome
		FROM SubCategoria
		JOIN TRXSubcategoria on TRXSCSubcategoria = SbCatId
		WHERE SbCatEmpresa = " . $_SESSION['EmpreId'] . " and TRXSCTermoReferencia = " . $_SESSION['TRId'] . "
		ORDER BY SbCatNome ASC";
$result = $conn->query($sql);
$rowSubCategoria = $result->fetchAll(PDO::FETCH_ASSOC);
		
		$aSubCategorias = '';

	foreach ($rowSubCategoria as $item) {
		
		if ($aSubCategorias == '') {
			$aSubCategorias .= $item['SbCatId'];
		} else {
			$aSubCategorias .= ", ".$item['SbCatId'];
		}
	}

//////////////////////////////////////////////////////////////

if (isset($_POST['inputData'])) {

	try {

		$sql = "SELECT max(TrXOrNumero) as Numero
				FROM TRXOrcamento
				Where TrXOrUnidade = " . $_SESSION['UnidadeId'] . " and TrXOrTermoReferencia = ".$_SESSION['TRId'];
		$result = $conn->query($sql);
		$rowNumero = $result->fetch(PDO::FETCH_ASSOC);

		$sql = "SELECT ParamProdutoOrcamento, ParamServicoOrcamento
				FROM Parametro
				WHERE ParamEmpresa = " . $_SESSION['EmpreId'] . "";
		$result = $conn->query($sql);
		$rowParam = $result->fetch(PDO::FETCH_ASSOC);

		$paramProduto = $rowParam['ParamProdutoOrcamento'] == 1 ? 'ProdutoOrcamento' : 'Produto';
		$paramServico = $rowParam['ParamServicoOrcamento'] == 1 ? 'ServicoOrcamento' : 'Servico';

		$sNumero = (int) $rowNumero['Numero'] + 1;
		$sNumero = str_pad($sNumero, 6, "0", STR_PAD_LEFT);

		$sql = "INSERT INTO TRXOrcamento (TrXOrTermoReferencia, TrXOrNumero, TrXOrData, TrXOrCategoria, TrXOrConteudo, TrXOrFornecedor,
					   TrXOrSolicitante, TrXOrTabelaProduto, TrXOrTabelaServico, TrXOrStatus, TrXOrUsuarioAtualizador, TrXOrUnidade)
				VALUES (:iTR, :sNumero, :dData, :iCategoria, :sConteudo, :iFornecedor, :iSolicitante, :sTabelaProduto, :sTabelaServico,
						:bStatus, :iUsuarioAtualizador, :iUnidade)";
		$result = $conn->prepare($sql);

		$aFornecedor = explode("#", $_POST['cmbFornecedor']);
		$iFornecedor = $aFornecedor[0];

		$result->execute(array(
			':iTR' => $_SESSION['TRId'],
			':sNumero' => $sNumero,
			':dData' => gravaData($_POST['inputData']),
			':iCategoria' => $_POST['inputCategoria'] == '#' ? null : $_POST['inputCategoria'],
			':sConteudo' => $_POST['txtareaConteudo'],
			':iFornecedor' => $iFornecedor,
			':iSolicitante' => $_SESSION['UsuarId'],
			':sTabelaProduto' => $paramProduto,
			':sTabelaServico' => $paramServico,
			':bStatus' => 1,
			':iUsuarioAtualizador' => $_SESSION['UsuarId'],
			':iUnidade' => $_SESSION['UnidadeId']
		));
		$insertId = $conn->lastInsertId();

		try {
			$sql = "INSERT INTO TRXOrcamentoXSubcategoria
							(TXOXSCOrcamento, TXOXSCSubcategoria, TXOXSCUnidade)
						VALUES 
							(:iTrOrcamento, :iTrSubCategoria, :iTrUnidade)";
			$result = $conn->prepare($sql);

			foreach ($rowSubCategoria as $subcategoria) {

				$result->execute(array(
					':iTrOrcamento' => $insertId,
					':iTrSubCategoria' => $subcategoria['SbCatId'],
					':iTrUnidade' => $_SESSION['UnidadeId']
				));
			}
		} catch (PDOException $e) {
			//$conn->rollback();
			echo 'Error: ' . $e->getMessage();
			exit;
		}

		$sql = "INSERT INTO AuditTR ( AdiTRTermoReferencia, AdiTRDataHora, AdiTRUsuario, AdiTRTela, AdiTRDetalhamento)
				VALUES (:iTRTermoReferencia, :iTRDataHora, :iTRUsuario, :iTRTela, :iTRDetalhamento)";
		$result = $conn->prepare($sql);
				
		$result->execute(array(
			':iTRTermoReferencia' => $_SESSION['TRId'],
			':iTRDataHora' => date("Y-m-d H:i:s"),
			':iTRUsuario' => $_SESSION['UsuarId'],
			':iTRTela' =>'ORÇAMENTO',
			':iTRDetalhamento' =>' INCLUSÃO DO ORÇAMENTO  DE Nº '. $sNumero . ' '
		));


		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Orçamento incluído!!!";
		$_SESSION['msg']['tipo'] = "success";
	} catch (PDOException $e) {

		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao incluir orçamento!!!";
		$_SESSION['msg']['tipo'] = "error";

		echo 'Error: ' . $e->getMessage();
		die;
	}

	irpara("trOrcamento.php");
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Orçamento</title>

	<?php include_once("head.php"); ?>

	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/tables/datatables/extensions/responsive.min.js"></script>
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
	<script src="global_assets/js/demo_pages/form_select2.js"></script>
	<script src="global_assets/js/demo_pages/form_layouts.js"></script>
	<script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>
	<script src="global_assets/js/plugins/editors/summernote/summernote.min.js"></script>

	<script src="global_assets/js/plugins/forms/selects/bootstrap_multiselect.js"></script>	

	<script src="global_assets/js/demo_pages/form_multiselect.js"></script>

	<!-- Validação -->
	<script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
	<script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
	<script src="global_assets/js/demo_pages/form_validation.js"></script>	

	<!-- Adicionando Javascript -->
	<script type="text/javascript">
		$(document).ready(function() {

			$('#summernote').summernote();

			//Ao informar o fornecedor, trazer os demais dados dele (contato, e-mail, telefone)
			$('#cmbFornecedor').on('change', function(e) {

				var Fornecedor = $('#cmbFornecedor').val();
				var Forne = Fornecedor.split('#');

				$('#inputContato').val(Forne[1]);
				$('#inputEmailFornecedor').val(Forne[2]);
				if (Forne[3] != "" && Forne[3] != "(__) ____-____") {
					$('#inputTelefoneFornecedor').val(Forne[3]);
				} else {
					$('#inputTelefoneFornecedor').val(Forne[4]);
				}
			});


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

				$.getJSON('filtraFornecedor.php?idCategoria=' + cmbCategoria, function(dados) {

					var option = '<option value="#">Selecione o Fornecedor</option>';

					if (dados.length) {

						$.each(dados, function(i, obj) {
							option += '<option value="' + obj.ForneId + '#' + obj.ForneContato + '#' + obj.ForneEmail + '#' + obj.ForneTelefone + '#' + obj.ForneCelular + '">' + obj.ForneNome + '</option>';
						});

						$('#cmbFornecedor').html(option).show();
					} else {
						ResetFornecedor();
					}
				});

			});


			// Limpa os campos de fornecedor quando uma nova categoria é selecionada
			$('#cmbCategoria').on('change', function() {
				let inputContato = $('#inputContato')
				let inputEmailFornecedor = $('#inputEmailFornecedor')
				let inputTelefoneFornecedor = $('#inputTelefoneFornecedor')

				if (inputContato.val() || inputEmailFornecedor.val() || inputTelefoneFornecedor.val()) {
					inputContato.val('')
					inputEmailFornecedor.val('')
					inputTelefoneFornecedor.val('')
				}
			})

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

			$("#enviar").on('click', function(e) {

				e.preventDefault();

				var cmbCategoria = $('#cmbCategoria').val();

				if (cmbCategoria == '' || cmbCategoria == '#') {
					alerta('Atenção', 'Informe a categoria!', 'error');
					$('#cmbCategoria').focus();
					return false;
				}

				$("#formTRXOrcamento").submit();
			});

		}); //document.ready

		//Mostra o "Filtrando..." na combo SubCategoria
		function Filtrando() {
			$('#cmbSubCategoria').empty().append('<option>Filtrando...</option>');
		}

		function ResetSubCategoria() {
			$('#cmbSubCategoria').empty().append('<option>Sem Subcategoria</option>');
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

					<form name="formTRXOrcamento" id="formTRXOrcamento" method="post" class="form-validate-jquery" action="trOrcamentoNovo.php">
						<div class="card-header header-elements-inline">
							<h5 class="text-uppercase font-weight-bold">Cadastrar Novo Orçamento</h5>
						</div>

						<div class="card-body">

							<div class="row">
								<div class="col-lg-12">
									<div class="row">

										<div class="col-lg-1">
											<div class="form-group">
												<label for="inputData">Data <span class="text-danger"> *</span></label>
												<input type="text" id="inputData" name="inputData" class="form-control" value="<?php echo date('d/m/Y'); ?>" readOnly>
											</div>
										</div>

										<div class="col-lg-4">
											<div class="form-group">
												<label for="cmbCategoria">Categoria <span class="text-danger"> *</span></label>
												<div class="d-flex flex-row" style="padding-top: 7px;">
													<input type="text" class="form-control pb-0" value="<?php echo $rowCategoria['CategNome'] ?>" readOnly>
													<input type="hidden" id="inputCategoria" name="inputCategoria" class="form-control pb-0" value="<?php echo $rowCategoria['CategId'] ?>">
												</div>
											</div>
										</div>

										<div class="col-lg-7">
											<div class="form-group">
												<label for="cmbSubCategoria">SubCategoria(s)</label>
												<select id="cmbSubCategoria" name="cmbSubCategoria" class="form-control multiselect-filtering" multiple="multiple" data-fouc>
													<?php 
														$sql = "SELECT SbCatId, SbCatNome
																FROM SubCategoria
																JOIN Situacao on SituaId = SbCatStatus	
																WHERE SbCatEmpresa = ". $_SESSION['EmpreId'] ." and SbCatId in (".$aSubCategorias.")
																ORDER BY SbCatNome ASC"; 
														$result = $conn->query($sql);
														$rowSubCategoria = $result->fetchAll(PDO::FETCH_ASSOC);
														$count = count($rowSubCategoria);														
																
														foreach ( $rowSubCategoria as $item){	
															print('<option value="'.$item['SbCatId,'].'"disabled selected>'.$item['SbCatNome'].'</option>');	
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
										<label for="txtareaConteudo">Conteúdo personalizado</label>
										<!--<div id="summernote" name="txtareaConteudo"></div>-->
										<textarea rows="5" cols="5" class="form-control" id="summernote" name="txtareaConteudo" placeholder="Corpo do orçamento (informe aqui o texto que você queira que apareça no orçamento)"></textarea>
									</div>
								</div>
							</div>
							<br>

							<div class="row">
								<div class="col-lg-12">
									<h5 class="mb-0 font-weight-semibold">Dados do Fornecedor <span class="text-danger"> *</span></h5>
									<br>
									<div class="row">
										<div class="col-lg-4">
											<div class="form-group">
												<label for="cmbFornecedor">Fornecedor</label>
												<select id="cmbFornecedor" name="cmbFornecedor" class="form-control form-control-select2" required>
													<option value="">Selecione</option>
													<?php
													$sql = "SELECT ForneId, ForneNome, ForneContato, ForneEmail, ForneTelefone, ForneCelular, ForneCategoria
															FROM Fornecedor
															JOIN Situacao on SituaId = ForneStatus
															WHERE ForneEmpresa = " . $_SESSION['EmpreId'] . " and ForneCategoria = " . $rowCategoria['CategId'] . "  
															and SituaChave = 'ATIVO'
															ORDER BY ForneNome ASC";
													$result = $conn->query($sql);
													$rowFornecedor = $result->fetchAll(PDO::FETCH_ASSOC);

													foreach ($rowFornecedor as $item) {
														print('<option value="' . $item['ForneId'] . '#' . $item['ForneContato'] . '#' . $item['ForneEmail'] . '#' . $item['ForneTelefone'] . '#' . $item['ForneCelular'] . '">' . $item['ForneNome'] . '</option>');
													}

													?>
												</select>
											</div>
										</div>

										<div class="col-lg-3">
											<div class="form-group">
												<label for="inputContato">Contato</label>
												<input type="text" id="inputContato" name="inputContato" class="form-control" readOnly>
											</div>
										</div>

										<div class="col-lg-3">
											<div class="form-group">
												<label for="inputEmailFornecedor">E-mail</label>
												<input type="text" id="inputEmailFornecedor" name="inputEmailFornecedor" class="form-control" readOnly>
											</div>
										</div>

										<div class="col-lg-2">
											<div class="form-group">
												<label for="inputTelefoneFornecedor">Telefone</label>
												<input type="text" id="inputTelefoneFornecedor" name="inputTelefoneFornecedor" class="form-control" readOnly>
											</div>
										</div>
									</div>
								</div>
							</div>
							<br>

							<div class="row">
								<div class="col-lg-12">
									<h5 class="mb-0 font-weight-semibold">Dados do Solicitante</h5>
									<br>
									<div class="row">
										<div class="col-lg-6">
											<div class="form-group">
												<label for="inputNomeSolicitante">Solicitante <span class="text-danger"> *</span></label>
												<input type="text" id="inputNomeSolicitante" name="inputNomeSolicitante" class="form-control" value="<?php echo $rowUsuario['UsuarNome']; ?>" readOnly>
											</div>
										</div>

										<div class="col-lg-3">
											<div class="form-group">
												<label for="inputEmailSolicitante">E-mail <span class="text-danger"> *</span></label>
												<input type="text" id="inputEmailSolicitante" name="inputEmailSolicitante" class="form-control" value="<?php echo $rowUsuario['UsuarEmail']; ?>" readOnly>
											</div>
										</div>

										<div class="col-lg-3">
											<div class="form-group">
												<label for="inputTelefoneSolicitante">Telefone</label>
												<input type="text" id="inputTelefoneSolicitante" name="inputTelefoneSolicitante" class="form-control" value="<?php echo $rowUsuario['UsuarTelefone']; ?>" readOnly>
											</div>
										</div>
									</div>
								</div>
							</div>

							<div class="row" style="margin-top: 10px;">
								<div class="col-lg-12">
									<div class="form-group">
										<button class="btn btn-lg btn-principal" id="enviar">Incluir</button>
										<a href="trOrcamento.php" class="btn btn-basic" role="button">Cancelar</a>
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