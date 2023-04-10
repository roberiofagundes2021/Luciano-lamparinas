<?php

include_once("sessao.php");

$_SESSION['PaginaAtual'] = 'Novo Fluxo Operacional';

include('global_assets/php/conexao.php');

//Se veio do fluxo.php
if (isset($_POST['inputFluxoOperacionalId'])) {

		$iFluxoOperacional = $_POST['inputFluxoOperacionalId'];

		$sql = "SELECT *
				FROM FluxoOperacional
				WHERE FlOpeId = $iFluxoOperacional ";
		$result = $conn->query($sql);
		$row = $result->fetch(PDO::FETCH_ASSOC);

		//SubCategorias para esse fornecedor
		$sql = "SELECT SbCatId, SbCatNome
				FROM SubCategoria
				JOIN FluxoOperacionalXSubCategoria on FOXSCSubCategoria = SbCatId
				WHERE SbCatEmpresa = " . $_SESSION['EmpreId'] . " and FOXSCFluxo = $iFluxoOperacional
				ORDER BY SbCatNome ASC";
		$result = $conn->query($sql);
		$rowBD = $result->fetchAll(PDO::FETCH_ASSOC);
		
		foreach ($rowBD as $item){
			$aSubCategorias[] = $item['SbCatId'];
		}
					
		$_SESSION['msg'] = array();

} else {  //Esse else foi criado para se caso o usuário der um REFRESH na página. Nesse caso não terá POST e campos não reconhecerão o $row da consulta acima (daí ele deve ser redirecionado) e se quiser continuar editando terá que clicar no ícone da Grid novamente

	irpara("fluxo.php");
}

if (isset($_POST['inputDataInicio'])) {

	try {

		$sql = "UPDATE FluxoOperacional SET FlOpeFornecedor = :iFornecedor, FlOpeCategoria = :iCategoria, FlOpeDataInicio = :dDataInicio, 
		                                    FlOpeDataFim = :dDataFim, FlOpeNumContrato = :iNumContrato, FlOpeValor = :fValor,
										    FlOpeConteudoInicio = :sConteudoInicio, FlOpeConteudoFim = :sConteudoFim, FlOpeUsuarioAtualizador = :iUsuarioAtualizador
				WHERE FlOpeId = " . $_POST['inputFluxoOperacionalId'] . "
				";
		$result = $conn->prepare($sql);

		$conn->beginTransaction();				

		$result->execute(array(
			':iFornecedor' => $_POST['cmbFornecedor'],
			':iCategoria' => $_POST['cmbCategoria'] == '' ? null : $_POST['cmbCategoria'],
			//':iSubCategoria' => $_POST['cmbSubCategoria'] == '' ? null : $_POST['cmbSubCategoria'],
			':dDataInicio' => $_POST['inputDataInicio'] == '' ? null : $_POST['inputDataInicio'],
			':dDataFim' => $_POST['inputDataFim'] == '' ? null : $_POST['inputDataFim'],
			':iNumContrato' => $_POST['inputNumContrato'],
			':fValor' => gravaValor($_POST['inputValor']),
			':sConteudoInicio' => $_POST['txtareaConteudoInicio'],
			':sConteudoFim' => $_POST['txtareaConteudoFim'],
			':iUsuarioAtualizador' => $_SESSION['UsuarId']
		));


		$sql = "DELETE FROM FluxoOperacionalXSubCategoria
				WHERE FOXSCFluxo = :iFluxo and FOXSCUnidade = :iUnidade";
		$result = $conn->prepare($sql);	
		
		$result->execute(array(
							':iFluxo' => $_POST['inputFluxoOperacionalId'],
							':iUnidade' => $_SESSION['UnidadeId']));
						
		if ($_POST['cmbSubCategoria']){
			
			try{
				$sql = "INSERT INTO FluxoOperacionalXSubCategoria
							(FOXSCFluxo, FOXSCSubCategoria, FOXSCUnidade)
						VALUES 
							(:iFluxo, :iSubCategoria, :iUnidade)";
				$result = $conn->prepare($sql);

				foreach ($_POST['cmbSubCategoria'] as $key => $value){

					$result->execute(array(
									':iFluxo' => $_POST['inputFluxoOperacionalId'],
									':iSubCategoria' => $value,
									':iUnidade' => $_SESSION['UnidadeId']
									));
				}
				
			} catch(PDOException $e) {
				$conn->rollback();
				echo 'Error: ' . $e->getMessage();exit;
			}
		}
						
        $conn->commit();

		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Fluxo Operacional alterado!!!";
		$_SESSION['msg']['tipo'] = "success";
	
	} catch (PDOException $e) {

		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao alterar Fluxo Operacional!!!";
		$_SESSION['msg']['tipo'] = "error";

		echo 'Error: ' . $e->getMessage();
		die;
	}

	irpara("fluxo.php");
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Fluxo Operacional</title>

	<?php include_once("head.php"); ?>

	<!-- Theme JS files -->
	<script src="global_assets/js/demo_pages/form_select2.js"></script>	
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
	<script src="global_assets/js/demo_pages/form_layouts.js"></script>
	<script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>

	<script src="global_assets/js/plugins/editors/summernote/summernote.min.js"></script>
	<script src="global_assets/js/demo_pages/form_checkboxes_radios.js"></script>

	<script src="global_assets/js/demo_pages/picker_date.js"></script>

	<!-- Validação -->
	<script src="global_assets/js/plugins/notifications/pnotify.min.js"></script>
	<script src="global_assets/js/demo_pages/extra_pnotify.js"></script>
	<script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
	<script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
	<script src="global_assets/js/demo_pages/form_validation.js"></script> <!-- CV Documentacao: https://jqueryvalidation.org/ -->

	<!-- Adicionando Javascript -->
	<script type="text/javascript">
		$(document).ready(function() {

			//Inicializa o editor de texto que será usado pelos campos "Conteúdo Personalizado - Inicialização" e "Conteúdo Personalizado - Finalização"
			$('#summernoteInicio').summernote();
			$('#summernoteFim').summernote();

			//Ao mudar o Fornecedor, filtra a categoria e o Orçamento via ajax (retorno via JSON)
			$('#cmbFornecedor').on('change', function(e) {

				FiltraCategoria();
				FiltraSubCategoria();

				var cmbFornecedor = $('#cmbFornecedor').val();

				$.getJSON('filtraCategoria.php?idFornecedor=' + cmbFornecedor, function(dados) {

					//var option = '<option value="#">Selecione a Categoria</option>';
					var option = '';

					if (dados.length) {

						$.each(dados, function(i, obj) {
							option += '<option value="' + obj.CategId + '">' + obj.CategNome + '</option>';
						});

						$('#cmbCategoria').html(option).show();
					} else {
						ResetCategoria();
					}
				});

				$.getJSON('filtraSubCategoria.php?idFornecedor=' + cmbFornecedor, function(dados) {

					if (dados.length) {

						var option = '';

						$.each(dados, function(i, obj) {
							option += '<option value="' + obj.SbCatId + '">' + obj.SbCatNome + '</option>';
						});

						$('#cmbSubCategoria').html(option).show();
					} else {
						ResetSubCategoria();
					}
				});

			});

			//Mostra o "Filtrando..." na combo Categoria
			function FiltraCategoria() {
				$('#cmbCategoria').empty().append('<option>Filtrando...</option>');
			}

			//Mostra o "Filtrando..." na combo SubCategoria
			function FiltraSubCategoria() {
				$('#cmbSubCategoria').empty().append('<option>Filtrando...</option>');
			}

			function ResetCategoria() {
				$('#cmbCategoria').empty().append('<option value="">Sem Categoria</option>');
			}

			function ResetSubCategoria() {
				$('#cmbSubCategoria').empty().append('<option value="">Sem SubCategoria</option>');
			}

			//Valida Registro Duplicado
			$('#enviar').on('click', function(e) {

				e.preventDefault();

				var inputDataInicio = $('#inputDataInicio').val();
				var inputDataFim = $('#inputDataFim').val();

				if (inputDataFim < inputDataInicio) {
					alerta('Atenção', 'A Data Fim deve ser maior que a Data Início!', 'error');
					$('#inputDataFim').focus();
					return false;
				}

				$("#formFluxoOperacional").submit();

			});

		});
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

					<form name="formFluxoOperacional" id="formFluxoOperacional" method="post" class="form-validate-jquery" action="fluxoEdita.php">
						<div class="card-header header-elements-inline">
							<h5 class="text-uppercase font-weight-bold">Editar Fluxo Operacional</h5>
						</div>

						<input type="hidden" id="inputFluxoOperacionalId" name="inputFluxoOperacionalId" value="<?php echo $row['FlOpeId']; ?>">

						<div class="card-body">

							<h5 class="mb-0 font-weight-semibold">Dados do Fornecedor</h5>
							<br>
							<div class="row">
								<div class="col-lg-6">
									<div class="form-group">
										<label for="cmbFornecedor">Fornecedor <span class="text-danger">*</span></label>
										<select id="cmbFornecedor" name="cmbFornecedor" class="form-control form-control-select2" required>
											<option value="">Selecione</option>
											<?php
											$sql = "SELECT ForneId, ForneRazaosocial, ForneContato, ForneEmail, ForneTelefone, ForneCelular
													FROM Fornecedor
													JOIN Situacao on SituaId = ForneStatus
													WHERE ForneEmpresa = " . $_SESSION['EmpreId'] . " and SituaChave = 'ATIVO'
													ORDER BY ForneRazaosocial ASC";
											$result = $conn->query($sql);
											$rowFornecedor = $result->fetchAll(PDO::FETCH_ASSOC);

											foreach ($rowFornecedor as $item) {
												$seleciona = $item['ForneId'] == $row['FlOpeFornecedor'] ? "selected" : "";
												print('<option value="' . $item['ForneId'] . '" ' . $seleciona . '>' . $item['ForneRazaosocial'] . '</option>');
											}

											?>
										</select>
									</div>
								</div>

								<div class="col-lg-6">
									<div class="form-group">
										<label for="cmbCategoria">Categoria <span class="text-danger">*</span></label>
										<select id="cmbCategoria" name="cmbCategoria" class="form-control form-control-select2" required>
											<option value="">Selecione</option>
											<?php
											$sql = "SELECT CategId, CategNome
													FROM Categoria
													JOIN Fornecedor on ForneCategoria = CategId
													JOIN Situacao on SituaId = CategStatus
													WHERE CategEmpresa = ".$_SESSION['EmpreId']." and ForneId = " . $row['FlOpeFornecedor'] . "
													and SituaChave = 'ATIVO'
													ORDER BY CategNome ASC";
											$result = $conn->query($sql);
											$rowCategoria = $result->fetchAll(PDO::FETCH_ASSOC);

											foreach ($rowCategoria as $item) {
												$seleciona = $item['CategId'] == $row['FlOpeCategoria'] ? "selected" : "";
												print('<option value="' . $item['CategId'] . '" ' . $seleciona . '>' . $item['CategNome'] . '</option>');
											}

											?>
										</select>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-lg-12">
									<div class="form-group" style="border-bottom:1px solid #ddd;">
										<label for="cmbSubCategoria">SubCategoria</label>
										<select id="cmbSubCategoria" name="cmbSubCategoria[]" class="form-control select" multiple="multiple" data-fouc>
											<!--<option value="#">Selecione uma subcategoria</option>-->
											<?php
												
												if (isset($row['FlOpeCategoria'])){
													
													$sql = "SELECT SbCatId, SbCatNome
															FROM SubCategoria
															JOIN FornecedorXSubCategoria on FrXSCSubCategoria = SbCatId														 
															WHERE SbCatEmpresa = ". $_SESSION['EmpreId'] ." and SbCatCategoria = ".$row['FlOpeCategoria']."
															and FrXSCFornecedor = ". $row['FlOpeFornecedor']."
															ORDER BY SbCatNome ASC";
													$result = $conn->query($sql);
													$rowSubCategoria = $result->fetchAll(PDO::FETCH_ASSOC);
													$count = count($rowSubCategoria);
													
													if($count){
														foreach ($rowSubCategoria as $item){
															$seleciona = in_array($item['SbCatId'], $aSubCategorias) ? "selected" : "";
															print('<option value="'.$item['SbCatId'].'" '. $seleciona .'>'.$item['SbCatNome'].'</option>');
														}
													} 
												}
											?>
										</select>										
									</div>
								</div>
							</div>

							<h5 class="mb-0 font-weight-semibold">Dados do Fluxo</h5>
							<br>
							<div class="row">
								<div class="col-lg-3">
									<div class="form-group">
										<label for="inputDataInicio">Data Início <span class="text-danger">*</span></label>
										<div class="input-group">
											<span class="input-group-prepend">
												<span class="input-group-text"><i class="icon-calendar22"></i></span>
											</span>
											<input type="date" id="inputDataInicio" name="inputDataInicio" class="form-control" placeholder="Data Início" value="<?php echo $row['FlOpeDataInicio']; ?>" required>
										</div>
									</div>
								</div>

								<div class="col-lg-3">
									<div class="form-group">
										<label for="inputDataFim">Data Fim <span class="text-danger">*</span></label>
										<div class="input-group">
											<span class="input-group-prepend">
												<span class="input-group-text"><i class="icon-calendar22"></i></span>
											</span>
											<input type="date" id="inputDataFim" name="inputDataFim" class="form-control" placeholder="Data Fim" value="<?php echo $row['FlOpeDataFim']; ?>" required>
										</div>
									</div>
								</div>

								<div class="col-lg-2">
									<div class="form-group">
										<label for="inputNumContrato">Número do Fluxo <span class="text-danger">*</span></label>
										<input type="text" id="inputNumContrato" name="inputNumContrato" class="form-control" placeholder="Nº do Fluxo" value="<?php echo $row['FlOpeNumContrato']; ?>" required>
									</div>
								</div>

								<div class="col-lg-4">
									<div class="form-group">
										<label for="inputValor">Valor Total <span class="text-danger">*</span></label>
										<input type="text" id="inputValor" name="inputValor" class="form-control" placeholder="Valor Total" value="<?php echo mostraValor($row['FlOpeValor']); ?>" onKeyUp="moeda(this)" maxLength="12" required>
									</div>
								</div>

							</div>

							<br>

							<div class="row">
								<div class="col-lg-12">
									<div class="form-group">
										<label for="txtareaConteudoInicio">Conteúdo personalizado - Introdução</label>
										<textarea rows="5" cols="5" class="form-control" id="summernoteInicio" name="txtareaConteudoInicio" placeholder="Corpo do TR (informe aqui o texto que você queira que apareça no TR)"><?php echo $row['FlOpeConteudoInicio']; ?></textarea>
									</div>
								</div>
							</div>
							<br>

							<div class="row">
								<div class="col-lg-12">
									<div class="form-group">
										<label for="txtareaConteudoFim">Conteúdo personalizado - Finalização</label>
										<textarea rows="5" cols="5" class="form-control" id="summernoteFim" name="txtareaConteudoFim" placeholder="Considerações Finais da TR (informe aqui o texto que você queira que apareça no término da TR)"><?php echo $row['FlOpeConteudoFim']; ?></textarea>
									</div>
								</div>
							</div>
							<br>

							<div class="row" style="margin-top: 10px;">
								<div class="col-lg-12">
									<div class="form-group">
											<?php
											$sql = "SELECT SUM(FOXPrQuantidade * FOXPrValorUnitario) as total
													FROM FluxoOperacionalXProduto
													WHERE FOXPrUnidade = " . $_SESSION['UnidadeId'] . " and FOXPrFluxoOperacional = " . $iFluxoOperacional;
											$result = $conn->query($sql);
											$rowTotal = $result->fetch(PDO::FETCH_ASSOC);
											$count = count($rowTotal);

											if ($count) {
												if ($rowTotal['total'] == $row['FlOpeValor']) {
													$bFechado = 1;
												} else {
													$bFechado = 0;
												}
											} else {
												$bFechado = 0;
											}

										if ($_POST['inputPermission']) {
											
											if ($bFechado) {
												if ($_SESSION['PerfiChave'] == 'SUPER' or $_SESSION['PerfiChave'] == 'ADMINISTRADOR' or $_SESSION['PerfiChave'] == 'CENTROADMINISTRATIVO' or $_SESSION['PerfiChave'] == 'CONTROLADORIA') {
													print('<button class="btn btn-lg btn-principal" id="enviar">Alterar</button>');
												}
											} else {
												print('<button class="btn btn-lg btn-principal" id="enviar">Alterar</button>');
											}
										}
											?>
										<a href="fluxo.php" class="btn btn-basic" role="button" id="cancelar">Cancelar</a>
									</div>
								</div>
							</div>
					</form>

				</div>
				<!-- /card-body -->

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