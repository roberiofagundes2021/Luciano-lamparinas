<?php

include_once("sessao.php");

$_SESSION['PaginaAtual'] = 'Parâmetro';

include('global_assets/php/conexao.php');

if (isset($_POST['inputEmpresaId'])){
	$_SESSION['EmpresaId'] = $_POST['inputEmpresaId'];
	$_SESSION['EmpresaNome'] = $_POST['inputEmpresaNome'];
}

if (isset($_SESSION['EmpresaId'])) {
	$sql = "SELECT ParamId, ParamEmpresaPublica, ParamValorAtualizadoFluxo, ParamValorAtualizadoOrdemCompra, ParamValorObsImpreRetirada, ParamProdutoOrcamento, ParamPrecoGridProduto, ParamPrecoGridServico, ParamServicoOrcamento,ParamValidadeObrigatoria,ParamPatrimonioInicial, ParamProcessoInicial 
	        FROM Parametro
	        WHERE ParamEmpresa = " . $_SESSION['EmpresaId'] . "";
	$result = $conn->query($sql);
	$row = $result->fetch(PDO::FETCH_ASSOC);
} else {
	irpara("empresa.php");
}

if (isset($_POST['inputIdEmpresa'])) {

	if (isset($_POST['inputParamValorProduto'])) {
		$VAF = null;
		$VAOC = null;
		$_POST['inputParamValorProduto'] == 'ValorAtualizadoFluxo' ? $VAF = 1 : $VAF = 0;
		$_POST['inputParamValorProduto'] == 'ValorAtualizadoOrdemCompra' ? $VAOC = 1 : $VAOC = 0;
	}

	try {
		//var_dump($_POST);die;
		$sql = "UPDATE Parametro SET ParamEmpresaPublica = :iEmpresaPublica, ParamValorAtualizadoFluxo = :iValorAtualizadoFluxo, 
					   ParamValorAtualizadoOrdemCompra = :iValorAtualizadoOrdemCompra, ParamProdutoOrcamento = :iProdutoOrcamento, 
					   ParamServicoOrcamento = :iServicoOrcamento, ParamPrecoGridProduto = :sPrecoGridProduto, ParamPrecoGridServico = :sPrecoGridServico, ParamValidadeObrigatoria = :iValidadeObrigatoria,
					   ParamPatrimonioInicial = :sPatrimonioInicial, ParamProcessoInicial = :sProcessoInicial, ParamUsuarioAtualizador = :iUsuarioAtualizador, ParamValorObsImpreRetirada = :iValorObsImpreRetirada
				WHERE ParamEmpresa = :iEmpresa";
		$result = $conn->prepare($sql);

		$result->execute(array(
			':iEmpresaPublica' => isset($_POST['inputEmpresaPublica']) && $_POST['inputEmpresaPublica'] == "on" ? 1 : 0,
			':iValorAtualizadoFluxo' => $VAF,
			':iValorAtualizadoOrdemCompra' => $VAOC,
			':iValorObsImpreRetirada' => isset($_POST['inputValorObsImpreRetirada']) && $_POST['inputValorObsImpreRetirada'] == "on" ? 1 : 0,
			':iProdutoOrcamento' => isset($_POST['inputProdutoOrcamento']) && $_POST['inputProdutoOrcamento'] == "on" ? 1 : 0,
			':iServicoOrcamento' => isset($_POST['inputServicoOrcamento']) && $_POST['inputServicoOrcamento'] == "on" ? 1 : 0,
			':sPrecoGridProduto' => $_POST['cmbPrecoGridProduto'],
			':sPrecoGridServico' => $_POST['cmbPrecoGridServico'],
			':iValidadeObrigatoria' => isset($_POST['inputValidadeObrigatoria']) && $_POST['inputValidadeObrigatoria'] == "on" ? 1 : 0,
			':sPatrimonioInicial' =>$_POST['ImputPatrimonioInicial'] == "" ? null : $_POST['ImputPatrimonioInicial'],
			':sProcessoInicial' =>$_POST['ImputProcessoInicial'] == "" ? null : $_POST['ImputProcessoInicial'],
			':iUsuarioAtualizador' => $_SESSION['UsuarId'],
			':iEmpresa' => $_SESSION['EmpresaId']
		));
		//die;

	} catch (PDOException $e) {

		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao atualizar parâmetro!!!";
		$_SESSION['msg']['tipo'] = "error";

		echo 'Error: ' . $e->getMessage();
		die;
	}

	irpara("parametro.php");
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Parâmetro</title>

	<?php include_once("head.php"); ?>

	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>

	<script src="global_assets/js/demo_pages/form_layouts.js"></script>
	<script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>

	<script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
	<script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
	<script src="global_assets/js/demo_pages/form_validation.js"></script> <!-- CV Documentacao: https://jqueryvalidation.org/ -->

	<script src="global_assets/js/plugins/forms/styling/switch.min.js"></script>


	<script src="global_assets/js/plugins/forms/inputs/inputmask.js"></script>
	<!-- /theme JS files -->

	<!-- Adicionando Javascript -->
	<script type="text/javascript">
		$(document).ready(function() {

			//Garantindo que ninguém mude a empresa na tela de parâmetro
			//$('#cmbEmpresa').prop("disabled", true);	

			//Valida Registro Duplicado
			$('#enviar').on('click', function(e) {

				e.preventDefault();

				$("#formParametro").submit();
				alerta("Sucesso", "Parâmetro atualizado!!!", "success")

			});

		});
	</script>

</head>

<body class="navbar-top sidebar-xs">

	<?php include_once("topo.php"); ?>

	<!-- Page content -->
	<div class="page-content">

		<?php include_once("menu-left.php"); ?>

		<?php include_once("menuLeftSecundario.php"); ?>

		<!-- Main content -->
		<div class="content-wrapper">

			<?php include_once("cabecalho.php"); ?>

			<!-- Content area -->
			<div class="content">

				<!-- Info blocks -->
				<div class="card">

					<form name="formParametro" id="formParametro" method="post">
						<div class="card-header header-elements-inline">
							<h5 class="text-uppercase font-weight-bold">Parâmetros</h5>
						</div>

						<div class="card-body">
							<p class="font-size-lg">Parâmetros da empresa <b><?php echo $_SESSION['EmpresaNome']; ?></b></p>
						</div>

						<input type="hidden" id="inputIdEmpresa" name="inputIdEmpresa" class="form-control" value="<?php echo $row['ParamId']; ?>">

						<div class="card-body">

							<div class="row">
								<div class="col-lg-6">
									<!-- Switch single -->
									<div class="form-group row">
										<label class="col-lg-3 col-form-label">Empresa Pública <span class="text-danger">*</span></label>
										<div class="col-lg-9">
											<div class="form-check form-check-switch form-check-switch-left">
												<label class="form-check-label d-flex align-items-center">
													<input type="checkbox" name="inputEmpresaPublica" id="inputEmpresaPublica" data-on-text="Sim" data-off-text="Não" class="form-input-switch" <?php if ($row['ParamEmpresaPublica']) echo "checked"; ?>>
												</label>
											</div>
										</div>
									</div>
									<!-- /switch single -->
								</div>
							</div>

							<div class="row">
								<div class="col-lg-6">
									<!-- Switch group -->
									<div class="form-group row" id="inputParamValorProduto">
										<label class="col-form-label col-lg-3">Valor do Produto/Serviço será atualizado <span class="text-danger">*</span></label>
										<div class="d-flex flex-column p-2">
											<div class="form-check form-check-inline">
												<label class="form-check-label">
													<input type="radio" name="inputParamValorProduto" id="inputValorFluxo" value="ValorAtualizadoFluxo" class="form-input-styled" data-fouc <?php if ($row['ParamValorAtualizadoFluxo'] != 0) echo "checked"; ?> required>
													Fluxo Previsto
												</label>
											</div>
											<div class="form-check form-check-inline">
												<label class="form-check-label">
													<input type="radio" name="inputParamValorProduto" id="inputValorOrdemCompra" value="ValorAtualizadoOrdemCompra" class="form-input-styled" data-fouc <?php if ($row['ParamValorAtualizadoOrdemCompra'] != 0) echo "checked"; ?> required>
													Ordem de Compra/Carta Contrato
												</label>
											</div>
										</div>
									</div>
								</div>
								<!-- /switch group -->
							</div>

							<div class="row">
								<div class="col-lg-6">
									<!-- Switch single -->
									<div class="form-group row">
										<label class="col-lg-3 col-form-label">Mostrar "Observação" na impressão das saídas e transferências <span class="text-danger">*</span></label>
										<div class="col-lg-9">
											<div class="form-check form-check-switch form-check-switch-left">
												<label class="form-check-label d-flex align-items-center">
													<input type="checkbox" name="inputValorObsImpreRetirada" id="inputValorObsImpreRetirada" data-on-text="Sim" data-off-text="Não" class="form-input-switch" <?php if ($row['ParamValorObsImpreRetirada']) echo "checked"; ?>>
												</label>
											</div>
										</div>
									</div>
									<!-- /switch single -->
								</div>
							</div>
							<div class="row">
								<div class="col-lg-6">
									<!-- Switch single -->
									<div class="form-group row">
										<label class="col-lg-3 col-form-label">Usar "Produtos para Orçamento" nos Orçamentos da TR <span class="text-danger">*</span></label>
										<div class="col-lg-9">
											<div class="form-check form-check-switch form-check-switch-left">
												<label class="form-check-label d-flex align-items-center">
													<input type="checkbox" name="inputProdutoOrcamento" id="inputProdutoOrcamento" data-on-text="Sim" data-off-text="Não" class="form-input-switch" <?php if ($row['ParamProdutoOrcamento']) echo "checked"; ?>>
												</label>
											</div>
										</div>
									</div>
									<!-- /switch single -->
								</div>
							</div>
							<div class="row">
								<div class="col-lg-6">
									<!-- Switch single -->
									<div class="form-group row">
										<label class="col-lg-3 col-form-label">Usar "Serviços para Orçamento" nos Orçamentos da TR <span class="text-danger">*</span></label>
										<div class="col-lg-9">
											<div class="form-check form-check-switch form-check-switch-left">
												<label class="form-check-label d-flex align-items-center">
													<input type="checkbox" name="inputServicoOrcamento" id="inputServicoOrcamento" data-on-text="Sim" data-off-text="Não" class="form-input-switch" <?php if ($row['ParamServicoOrcamento']) echo "checked"; ?>>
												</label>
											</div>
										</div>
									</div>
									<!-- /switch single -->
								</div>
							</div>
							<div class="row">
								<div class="col-lg-3">
									<!-- Switch single -->
									<div class="form-group">
										<label for="cmbPrecoGridProduto">Coluna preço na relação dos produtos <span class="text-danger">*</span></label>
										<select id="cmbPrecoGridProduto" name="cmbPrecoGridProduto" class="form-control form-control-select2">
											<?php
											if ($row['ParamPrecoGridProduto'] == 'PRECOCUSTOFINAL') {
												print('
														<option value="PRECOCUSTOFINAL" selected>Preço de custo final</option>
														<option value="PRECOCUSTO">Preço de custo</option>
														<option value="PRECOVENDA">Preço de venda</option>
												   ');
											} else if ($row['ParamPrecoGridProduto'] == 'PRECOCUSTO') {
												print('
												        <option value="PRECOCUSTOFINAL">Preço de custo final</option>
														<option value="PRECOCUSTO" selected>Preço de custo</option>
														<option value="PRECOVENDA">Preço de venda</option>
													');
											} else {

												print('
												        <option value="PRECOCUSTOFINAL">Preço de custo final</option>
														<option value="PRECOCUSTO">Preço de custo</option>
												        <option value="PRECOVENDA" selected>Preço de venda</option>
												   ');
											}
											?>
										</select>
									</div>
									<!-- /switch single -->
								</div>
							</div>
							<div class="row">
								<div class="col-lg-3">
									<!-- Switch single -->
									<div class="form-group">
										<label for="cmbPrecoGridServico">Coluna preço na relação de Serviço <span class="text-danger">*</span></label>
										<select id="cmbPrecoGridServico" name="cmbPrecoGridServico" class="form-control form-control-select2">
											<?php
											if ($row['ParamPrecoGridServico'] == 'PRECOCUSTOFINAL') {
												print('
														<option value="PRECOCUSTOFINAL" selected>Preço de custo final</option>
														<option value="PRECOCUSTO">Preço de custo</option>
														<option value="PRECOVENDA">Preço de venda</option>
												   ');
											} else if ($row['ParamPrecoGridServico'] == 'PRECOCUSTO') {
												print('
												        <option value="PRECOCUSTOFINAL">Preço de custo final</option>
														<option value="PRECOCUSTO" selected>Preço de custo</option>
														<option value="PRECOVENDA">Preço de venda</option>
													');
											} else {

												print('
												        <option value="PRECOCUSTOFINAL">Preço de custo final</option>
														<option value="PRECOCUSTO">Preço de custo</option>
												        <option value="PRECOVENDA" selected>Preço de venda</option>
												   ');
											}
											?>
										</select>
									</div>
									<!-- /switch single -->
								</div>
							</div>
							<div class="row">
								<div class="col-lg-6">
									<!-- Switch single -->
									<div class="form-group row">
										<label class="col-lg-3 col-form-label">Validade Obrigatória <span class="text-danger">*</span></label>
										<div class="col-lg-9">
											<div class="form-check form-check-switch form-check-switch-left">
												<label class="form-check-label d-flex align-items-center">
													<input type="checkbox" name="inputValidadeObrigatoria" id="inputValidadeObrigatoria" data-on-text="Sim" data-off-text="Não" class="form-input-switch" <?php if ($row['ParamValidadeObrigatoria']) echo "checked"; ?>>
												</label>
											</div>
										</div>
									</div>
									<!-- /switch single -->
								</div>
							</div>
							<div class="row">
								<div class="col-lg-3">
									<!-- Switch single -->
									<div class="form-group">
										<label for="ImputPatrimonioInicial">Patrimônio Inicial </label>
										<input type="text" id="ImputPatrimonioInicial" name="ImputPatrimonioInicial" class="form-control" placeholder="Patrimônio Inicial" value="<?php if ($row['ParamPatrimonioInicial'] != NULL) echo $row['ParamPatrimonioInicial'] ?>" >
									</div>
									<!-- /switch single -->
								</div>
							</div>

							<div class="row">
								<div class="col-lg-3">
									<!-- Switch single -->
									<div class="form-group">
										<label for="ImputProcessoInicial">Nº do Processo Inicial </span></label>
										<input type="text" id="ImputProcessoInicial" name="ImputProcessoInicial" class="form-control" placeholder="Processo Inicial" value="<?php if ($row['ParamProcessoInicial'] != NULL) echo $row['ParamProcessoInicial'] ?>" >
									</div>
									<!-- /switch single -->
								</div>
							</div>
							<div class="row" style="margin-top: 10px;">
								<div class="col-lg-12">
									<div class="form-group">
										<button class="btn btn-lg btn-principal" id="enviar">Atualizar</button>
									</div>
								</div>
							</div>
						</div>
					</form>
					<!--<form name="formParametro" id="formParametro" method="post">
						<div class="row">
							<div class="col-md-12">
								<div class="card">
									<div class="card-header header-elements-inline">
										<h5 class="text-uppercase font-weight-bold">Parâmetros</h5>
									</div>
									<div class="card-body">
										<ul class="nav nav-tabs">
											<li class="nav-item"><a href="#parametroGeral" class="nav-link active" data-toggle="tab">Geral</a></li>
											<li class="nav-item"><a href="#parametroPS" class="nav-link" data-toggle="tab">Produto/Servico</a></li>
											<li class="nav-item"><a href="#parametroTR" class="nav-link" data-toggle="tab">Termo de Referência</a></li>
										</ul>
										<div class="tab-content">

											<div class="tab-pane fade show active" id="parametroGeral">
												<div class="form-group row mx-0">
													<p class="font-size-lg m-0 p-0 col-2">Parâmetros da empresa:</p>
													<input type="text" name="empresa" class="form-control px-1 py-0 col-3" disabled value="<?php echo $_SESSION['EmpresaNome']; ?>">
												</div>
												<div class="row">
													<div class="col-lg-6">
														
														<div class="form-group row">
															<label class="col-lg-4 col-form-label">Empresa Pública <span class="text-danger">*</span></label>
															<div class="col-lg-8">
																<div class="form-check form-check-switch form-check-switch-left">
																	<label class="form-check-label d-flex align-items-center">
																		<input type="checkbox" name="inputEmpresaPublica" id="inputEmpresaPublica" data-on-text="Sim" data-off-text="Não" class="form-input-switch" <?php if ($row['ParamEmpresaPublica']) echo "checked"; ?>>
																	</label>
																</div>
															</div>
														</div>
														
													</div>
												</div>
												<div class="row">
													<div class="col-lg-6">
														
														<div class="form-group row">
															<label class="col-lg-4 col-form-label">Mostrar "Observação" na impressão das saídas e transferências<span class="text-danger">*</span></label>
															<div class="col-lg-8">
																<div class="form-check form-check-switch form-check-switch-left">
																	<label class="form-check-label d-flex align-items-center">
																		<input type="checkbox" name="inputValorObsImpreRetirada" id="inputValorObsImpreRetirada" data-on-text="Sim" data-off-text="Não" class="form-input-switch" <?php if ($row['ParamValorObsImpreRetirada']) echo "checked"; ?>>
																	</label>
																</div>
															</div>
														</div>
													
													</div>
												</div>
											</div>

											<div class="tab-pane fade" id="parametroPS">
												<div class="row">
													<div class="col-lg-6">
														
														<div class="form-group row" id="inputParamValorProduto">
															<label class="col-form-label col-lg-3">Valor do Produto será atualizado <span class="text-danger">*</span></label>
															<div class="d-flex flex-column p-2">
																<div class="form-check form-check-inline">
																	<label class="form-check-label">
																		<input type="radio" name="inputParamValorProduto" id="inputValorFluxo" value="ValorAtualizadoFluxo" class="form-input-styled" data-fouc <?php if ($row['ParamValorAtualizadoFluxo'] != 0) echo "checked"; ?>>
																		Fluxo Previsto
																	</label>
																</div>
																<div class="form-check form-check-inline">
																	<label class="form-check-label">
																		<input type="radio" name="inputParamValorProduto" id="inputValorOrdemCompra" value="ValorAtualizadoOrdemCompra" class="form-input-styled" data-fouc <?php if ($row['ParamValorAtualizadoOrdemCompra'] != 0) echo "checked"; ?>>
																		Ordem de Compra/Carta Contrato
																	</label>
																</div>
															</div>
														</div>
													</div>
													
												</div>
												<div class="row">
													<div class="col-lg-3">
														
														<div class="form-group">
															<label for="cmbEstoqueOrigem">Origem</label>
															<select id="cmbEstoqueOrigem" name="cmbEstoqueOrigem" class="form-control form-control-select2">

															</select>
														</div>
														
													</div>
												</div>
											</div>

											<div class="tab-pane fade" id="parametroTR">
												<div class="row">
													<div class="col-lg-6">
														
														<div class="form-group row">
															<label class="col-lg-3 col-form-label">Usar "Produtos para Orçamento" nos Orçamentos da TR<span class="text-danger">*</span></label>
															<div class="col-lg-9">
																<div class="form-check form-check-switch form-check-switch-left">
																	<label class="form-check-label d-flex align-items-center">
																		<input type="checkbox" name="inputProdutoOrcamento" id="inputProdutoOrcamento" data-on-text="Sim" data-off-text="Não" class="form-input-switch" <?php if ($row['ParamProdutoOrcamento']) echo "checked"; ?>>
																	</label>
																</div>
															</div>
														</div>
														
													</div>
												</div>
												<div class="row">
													<div class="col-lg-6">
														
														<div class="form-group row">
															<label class="col-lg-3 col-form-label">Usar "Serviços para Orçamento" nos Orçamentos da TR<span class="text-danger">*</span></label>
															<div class="col-lg-9">
																<div class="form-check form-check-switch form-check-switch-left">
																	<label class="form-check-label d-flex align-items-center">
																		<input type="checkbox" name="inputServicoOrcamento" id="inputServicoOrcamento" data-on-text="Sim" data-off-text="Não" class="form-input-switch" <?php if ($row['ParamServicoOrcamento']) echo "checked"; ?>>
																	</label>
																</div>
															</div>
														</div>
														
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="row" style="margin-top: 10px;">
										<div class="col-lg-12">
											<div class="form-group">
												<button class="btn btn-lg btn-principal" id="enviar">Atualizar</button>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</form>-->
				</div>
				<!-- /card-body -->

			</div>
			<!-- /info blocks -->

			<?php include_once("footer.php"); ?>

		</div>
		<!-- /content area -->

	</div>
	<!-- /main content -->

	</div>
	<!-- /page content -->

</body>

</html>