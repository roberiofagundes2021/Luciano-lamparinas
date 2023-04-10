<?php

include_once("sessao.php");

//$inicio1 = microtime(true);

$_SESSION['PaginaAtual'] = 'Fluxo Operacional Aditivo';

include('global_assets/php/conexao.php');

//Se veio do fluxo.php
if (isset($_POST['inputFluxoOperacionalId'])) {

	$_SESSION['FluxoId'] = $_POST['inputFluxoOperacionalId'];
	$_SESSION['Origem'] = $_POST['inputOrigem'];
}

$iFluxoOperacional = $_SESSION['FluxoId'];

//Dados do Fluxo
$sql = "SELECT FlOpeId, FlOpeNumContrato, FlOpeCategoria, ForneId, ForneNome, ForneTelefone, ForneCelular, CategNome, FlOpeCategoria,
			   FlOpeNumProcesso, FlOpeValor, FlOpeDataInicio, FlOpeDataFim, FlOpeStatus, SituaChave,
			   dbo.fnSubCategoriasIdFluxo(FlOpeEmpresa, FlOpeId) as FlOpeSubCategoria,
			   dbo.fnFimContrato(FlOpeId) as FimContrato, dbo.fnValorTotalContrato(FlOpeId) as TotalContrato
		FROM FluxoOperacional
		JOIN Fornecedor on ForneId = FlOpeFornecedor
		JOIN Categoria on CategId = FlOpeCategoria
		JOIN Situacao on SituaId = FlOpeStatus
		WHERE FlOpeUnidade = " . $_SESSION['UnidadeId'] . " and FlOpeId = " . $iFluxoOperacional;
$result = $conn->query($sql);
$row = $result->fetch(PDO::FETCH_ASSOC);

$sSubCategorias = $row['FlOpeSubCategoria'];

$sql = "SELECT AditiId, AditiNumero, AditiDtInicio, AditiDtFim, AditiValor, AditiDtCelebracao
		FROM Aditivo
		JOIN Situacao on SituaId = AditiStatus
		WHERE AditiUnidade = " . $_SESSION['UnidadeId'] . " and AditiFluxoOperacional = " . $iFluxoOperacional ." and SituaChave = 'LIBERADO' ";
$result = $conn->query($sql);
$rowAditivo = $result->fetchAll(PDO::FETCH_ASSOC);
$countAditivos = count($rowAditivo);

$sql = "SELECT ParamEmpresaPublica
			FROM Parametro
			WHERE ParamEmpresa = ". $_SESSION['EmpreId'];
	$result = $conn->query($sql);
	$rowParametros = $result->fetch(PDO::FETCH_ASSOC);

	if ($rowParametros['ParamEmpresaPublica']){
		$fluxo = "CONTRATO";
		$contrato= "Contrato";

	} else {
		$fluxo = " ";
		$contrato= "Nº Fluxo";
	}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Aditivos do Fluxo Operacional</title>

	<?php include_once("head.php"); ?>

	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>

	<script src="global_assets/js/demo_pages/form_layouts.js"></script>
	<script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>

	<script src="global_assets/js/plugins/forms/selects/bootstrap_multiselect.js"></script>

	<script src="global_assets/js/demo_pages/form_multiselect.js"></script>
	<!-- /theme JS files -->

	<!-- Adicionando Javascript -->
	<script type="text/javascript">
		$(document).ready(function () {

			//Valida Registro
			$('#enviar').on('click', function (e) {

				e.preventDefault();

				var inputValor = parseFloat($('#inputValor').val());
				var inputTotalGeral = $('#inputTotalGeral').val().replaceAll('.', '').replace(',', '.');

				//Verifica se o valor ultrapassou o total
				if (parseFloat(inputTotalGeral) > parseFloat(inputValor)) {
					alerta('Atenção', 'A soma dos totais ultrapassou o valor do contrato!', 'error');
					return false;
				}

				$("#formAditivo").submit();

			}); // enviar			

		}); //document.ready

		//Essa função foi criada para não usar $_GET e ficar mostrando os ids via URL
		function atualizaAditivo(FluxoId, AditiId, Categoria, SubCategorias, Situacao, Tipo) {

			document.getElementById('inputFluxoId').value = FluxoId;
			document.getElementById('inputAditivoId').value = AditiId;
			document.getElementById('inputCategoria').value = Categoria;
			document.getElementById('inputSubCategorias').value = SubCategorias;

			document.formAditivo.setAttribute("target", "_self");
			//alert(FluxoId + " - " + AditiId);
			//return false;

			if (Tipo == 'novo') {
				if (Situacao != 'LIBERADO' && Situacao != 'FINALIZADO') {
					alerta('Atenção',
						'Aditivos só podem ser criados com o Fluxo Operacional com a situação LIBERADO ou FINALIZADO.',
						'error');
					return false;
				}
				document.formAditivo.action = "fluxoAditivoNovo.php";
			} else if (Tipo == 'edita') {
				document.formAditivo.action = "fluxoAditivoEdita.php";
			} else if (Tipo == 'exclui') {
				confirmaExclusao(document.formAditivo, "Tem certeza que deseja excluir esse aditivo?",
					"fluxoAditivoExclui.php");
			} else if (Tipo == 'imprimir') {
				document.formAditivo.setAttribute("target", "_blank");
				document.formAditivo.action = "fluxoAditivoImprime.php";
			}

			document.formAditivo.submit();
		}
	</script>

</head>

<body class="navbar-top sidebar-xs">

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

					<form name="formAditivo" id="formAditivo" method="post" class="form-validate">
						<div class="card-header header-elements-inline">
							<h5 class="text-uppercase font-weight-bold">Aditivos - Fluxo Operacional Nº <?php echo $fluxo; ?> "<?php echo $row['FlOpeNumContrato']; ?>"</h5>
						</div>

						<input type="hidden" id="inputIdFluxoOperacional" name="inputIdFluxoOperacional"
							class="form-control" value="<?php echo $row['FlOpeId']; ?>">

						<input type="hidden" id="inputFluxoId" name="inputFluxoId">
						<input type="hidden" id="inputAditivoId" name="inputAditivoId">
						<input type="hidden" id="inputCategoria" name="inputCategoria">
						<input type="hidden" id="inputSubCategorias" name="inputSubCategorias">		

						<div class="card-body">

							<div class="row">
								<div class="col-lg-12">
									<div class="row">
										<div class="col-lg-6">
											<div class="form-group">
												<label for="inputFornecedor">Fornecedor</label>
												<input type="text" id="inputFornecedor" name="inputFornecedor"
													class="form-control" value="<?php echo $row['ForneNome']; ?>"
													readOnly>
												<input type="hidden" id="inputIdFornecedor" name="inputIdFornecedor"
													class="form-control" value="<?php echo $row['ForneId']; ?>">
											</div>
										</div>
										<div class="col-lg-3">
											<div class="form-group">
												<label for="inputTelefone">Telefone</label>
												<input type="text" id="inputTelefone" name="inputTelefone"
													class="form-control" value="<?php echo $row['ForneTelefone']; ?>"
													readOnly>
											</div>
										</div>
										<div class="col-lg-3">
											<div class="form-group">
												<label for="inputCelular">Celular</label>
												<input type="text" id="inputCelular" name="inputCelular"
													class="form-control" value="<?php echo $row['ForneCelular']; ?>"
													readOnly>
											</div>
										</div>
									</div>
									<div class="row">
									<div class="<?php if ($fluxo == 'CONTRATO') { echo "col-lg-3"; } else { echo "col-lg-5"; } ?>">
											<div class="form-group">
												<label for="inputCategoriaNome">Categoria</label>
												<input type="text" id="inputCategoriaNome" name="inputCategoriaNome"
													class="form-control" value="<?php echo $row['CategNome']; ?>"
													readOnly>
												<input type="hidden" id="inputIdCategoria" name="inputIdCategoria"
													class="form-control" value="<?php echo $row['FlOpeCategoria']; ?>">
											</div>
										</div>
										<div class="col-lg-5">
											<div class="form-group">
												<label for="inputSubCategoriaNome">SubCategoria(s)</label>

												<?php 
													if ($sSubCategorias == 0){
														echo '<input id="inputSemSubCategoriaNome" name="inputSemSubCategoriaNome" class="form-control" value="" readOnly >';
													} else{

														echo '<select id="inputSubCategoriaNome" name="inputSubCategoriaNome" class="form-control multiselect-filtering" multiple="multiple" data-fouc>';
															
														$sql = "SELECT SbCatId, SbCatNome
																FROM SubCategoria
																JOIN Situacao on SituaId = SbCatStatus	
																WHERE SbCatEmpresa = ". $_SESSION['EmpreId'] ." and SbCatId in (".$sSubCategorias.")
																ORDER BY SbCatNome ASC"; 
														$result = $conn->query($sql);
														$rowBD = $result->fetchAll(PDO::FETCH_ASSOC);
														$count = count($rowBD);														
																
														foreach ( $rowBD as $item){	
															print('<option value="'.$item['SbCatId,'].'"disabled selected>'.$item['SbCatNome'].'</option>');	
														}                    
														
														echo '</select>';
													}  
												?>													
											</div>
										</div>
										<div class="col-lg-2">
											<div class="form-group">
												<label for="inputContrato"><?php echo $contrato; ?></label>
												<input type="text" id="inputContrato" name="inputContrato"
													class="form-control" value="<?php echo $row['FlOpeNumContrato']; ?>"
													readOnly>
											</div>
										</div>

										<?php
											if ($fluxo == 'CONTRATO'){	
												print('
											<div class="col-lg-2">
												<div class="form-group">
													<label for="inputProcesso">Processo</label>
													<input type="text" id="inputProcesso" name="inputProcesso" class="form-control" value="' . $row['FlOpeNumProcesso'] . '" readOnly>
												</div>
											</div>	');
											}										
									   ?>	
										<!--<div class="col-lg-2">
											<div class="form-group">
												<label for="inputValor">Valor Total</label>
												<input type="text" id="inputValor" name="inputValor" class="form-control" value="<?php echo mostraValor($row['FlOpeValor']); ?>" readOnly>
											</div>
										</div>-->
									</div>
								</div>
							</div>

							<!-- Info blocks -->
							<div class="row">
								<div class="col-lg-12">
									<!-- Basic responsive configuration -->
									<div class="card">
										<div class="card-header header-elements-inline">
											<h3 class="card-title">Relação dos Aditivos</h3>
											<div class="header-elements">
												<div class="list-icons">
													<a class="list-icons-item" data-action="collapse"></a>
													<a href="fluxo.php" class="list-icons-item"
														data-action="reload"></a>
													<!--<a class="list-icons-item" data-action="remove"></a>-->
												</div>
											</div>
										</div>

										<div class="card-body">
											<div class="row">
												<div class="col-lg-6 font-size-lg">A relação abaixo faz referência aos
													aditivos do fluxo acima</div>
												<div class="col-lg-6 text-right">
													<a href="<?php echo $_SESSION['Origem']; ?>" class="btn btn-classic" role="button">Voltar</a>
													<a href="#"	onclick="atualizaAditivo('<?php echo $row['FlOpeId']; ?>', '0', '<?php echo $row['FlOpeCategoria'] ?>', '<?php echo $sSubCategorias ?>','<?php echo $row['SituaChave']; ?>', 'novo');"
														class="btn btn-principal" role="button">Novo Aditivo </a>
												</div>
											</div>
										</div>
										<table class="table" id="tblFluxo">
											<thead>
												<tr class="bg-slate">
													<th width="35%">Principal/Aditivos</th>
													<th width="15%" style="text-align:center;">Data Início</th>
													<th width="15%" style="text-align:center;">Data Fim</th>
													<th width="10%" style="text-align:right;">Valor</th>
													<th width="15%" style="text-align:center;">Data da Celebração</th>
													<th width="10%" class="text-center">Ações</th>
												</tr>
											</thead>
											<tbody>
												<?php

												print('
												<tr>
													<td>Termo Base</td>
													<td style="text-align:center;">' . mostraData($row['FlOpeDataInicio']) . '</td>
													<td style="text-align:center;">' . mostraData($row['FlOpeDataFim']) . '</td>
													<td style="text-align:right;">' . mostraValor($row['FlOpeValor']) . '</td>
													<td></td>
													<td></td>
												</tr>
												');

												$cont = 1;

												foreach ($rowAditivo as $item) {

													print('
													<tr>
														<td>' . $item['AditiNumero'] . 'º Termo Aditivo</td>
														<td style="text-align:center;">' . mostraData($item['AditiDtInicio']) . '</td>
														<td style="text-align:center;">' . mostraData($item['AditiDtFim']) . '</td>
														<td style="text-align:right;">' . mostraValor($item['AditiValor']) . '</td>
														<td style="text-align:center;">' . mostraData($item['AditiDtCelebracao']) . '</td>
														');

													print('<td class="text-center">');

													if ($cont == $countAditivos) {

														print('<div class="list-icons m-2">
																	<div class="list-icons list-icons-extended">
																	
																		<!--<a href="#"  class="list-icons-item"><i class="icon-pencil7" data-popup="tooltip" data-placement="bottom" title="Editar"></i></a>-->
																			<a href="#" onclick="atualizaAditivo(' . $row['FlOpeId'] . ', \'' . $item['AditiId'] . '\', \'' . $row['FlOpeCategoria'] . '\', \'' . $row['FlOpeSubCategoria'] . '\', \'' . $row['SituaChave'] . '\', \'exclui\', \'\');" class="list-icons-item"><i class="icon-bin" data-popup="tooltip" data-placement="bottom" title="Exluir"></i></a>		
																			<a href="#" onclick="atualizaAditivo(' . $row['FlOpeId'] . ', \'' . $item['AditiId'] . '\', \'' . $row['FlOpeCategoria'] . '\', \'' . $row['FlOpeSubCategoria'] . '\', \'' . $row['SituaChave'] . '\', \'imprimir\', \'\');" class="list-icons-item"><i class="icon-printer2" title="Imprimir Aditivo"></i></a>
																	</div>
																</div>');
													} else {
															print('
														            <div class="list-icons m-2">
														                 <a href="#" onclick="atualizaAditivo(' . $row['FlOpeId'] . ', \'' . $item['AditiId'] . '\', \'' . $row['FlOpeCategoria'] . '\', \'' . $row['FlOpeSubCategoria'] . '\', \'' . $row['SituaChave'] . '\', \'imprimir\', \'\');" class="list-icons-item"><i class="icon-printer2" title="Imprimir Aditivo"></i></a>												
															');
													}

													print('</td>');
													print('</tr>');

													$cont++;
												}

												print('
												<tr style="background-color:#eeeeee; font-weight: bold">
													<td></td>
													<td style="text-align:center;">' . mostraData($row['FlOpeDataInicio']) . '</td>
													<td style="text-align:center;">' . mostraData($row['FimContrato']) . '</td>
													<td style="text-align:right;">' . mostraValor($row['TotalContrato']) . '</td>
													<td></td>
													<td></td>
												</tr>
												');

												?>

											</tbody>
										</table>
									</div>
									<!-- /basic responsive configuration -->

								</div>
							</div>

							<!-- /info blocks -->

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

	<?php include_once("alerta.php"); ?>

	<?php //$total1 = microtime(true) - $inicio1;
		 //echo '<span style="background-color:yellow; padding: 10px; font-size:24px;">Tempo de execução do script: ' . round($total1, 2).' segundos</span>';  ?>

</body>

</html>