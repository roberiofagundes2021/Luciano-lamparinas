<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Fluxo Realizado';

include('global_assets/php/conexao.php');


if(isset($_POST['inputDataInicio'])) {

	$dataInicio = $_POST['inputDataInicio'];
	$dataFim = $_POST['inputDataFim'];
	$iUnidade = isset($_POST['cmbUnidade']) && $_POST['cmbUnidade'] != '' ? $_POST['cmbUnidade'] : 'NULL';
	$iSetor = isset($_POST['cmbSetor']) && $_POST['cmbSetor'] != '' ? $_POST['cmbSetor'] : 'NULL';
	$iCategoria = isset($_POST['cmbCategoria']) && $_POST['cmbCategoria'] != '' ? $_POST['cmbCategoria'] : 'NULL';
	$iSubCategoria = isset($_POST['cmbSubCategoria']) && $_POST['cmbSubCategoria'] != '' ? $_POST['cmbSubCategoria'] : 'NULL';
	$iClassificacao = isset($_POST['cmbClassificacao']) && $_POST['cmbClassificacao'] != '' ? $_POST['cmbClassificacao'] : 'NULL';

	$sql = "SELECT ProduId, ProduNome, MvXPrValorUnitario, dbo.fnTotalSaidas(".$_SESSION['UnidadeId'].", ProduId, NULL, $iSetor, $iCategoria, $iSubCategoria, $iClassificacao, '$dataInicio', '$dataFim') as Saidas,
				   (MvXPrValorUnitario * dbo.fnTotalSaidas(MovimUnidade, ProduId, NULL, $iSetor, $iCategoria, $iSubCategoria, $iClassificacao, '$dataInicio', '$dataFim')) as ValorTotal
			FROM Produto
			JOIN MovimentacaoXProduto on MvXPrProduto = ProduId
			JOIN Movimentacao on MovimId = MvXPrMovimentacao
			JOIN Situacao on SituaId = MovimSituacao
			WHERE ProduEmpresa = ".$_SESSION['EmpreId']." and MovimTipo = 'S' and SituaChave = 'LIBERADO' and MovimData between '".$dataInicio."' and '".$dataFim."' ";

	if ($iUnidade != 'NULL'){
		
		if($iSetor != 'NULL'){
			$sql .= " and MovimDestinoSetor = ".$iSetor;
		} else {
			$sql .= " and MovimDestinoLocal = ".$iSetor; //Só que pra isso a combo Setor deveria vir Setores e Locais de Estoque. Será assim mesmo ou é pra vir só Setor?
		}
	}

	if ($iCategoria != 'NULL'){
		$sql .= " and ProduCategoria = ".$iCategoria;
	}

	if ($iSubCategoria != 'NULL'){
		$sql .= " and ProduSubCategoria = ".$iSubCategoria;
	}

	if ($iClassificacao != 'NULL'){
		$sql .= " and MvXPrClassificacao = ".$iClassificacao;
	}
	
	//echo $sql;die;
	
	$result = $conn->query($sql);
	$row = $result->fetchAll(PDO::FETCH_ASSOC);
	
	$cont = 0;
	
	$esconder = ' style="display:block;" ';
} else {
	$esconder = ' style="display:none;" ';
}


$timestamp = strtotime("-365 days");

$dataInicio = date('Y-m-d', $timestamp);
$dataFim = date('Y-m-d');

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Curva ABC</title>

	<?php include_once("head.php"); ?>
	
	<!-- Theme JS files --> 
	<script src="global_assets/js/plugins/tables/datatables/datatables.min.js"></script>
	<script src="global_assets/js/plugins/tables/datatables/extensions/responsive.min.js"></script>
	<script src="global_assets/js/demo_pages/datatables_responsive.js"></script>
	<script src="global_assets/js/demo_pages/datatables_sorting.js"></script>

	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
	<script src="global_assets/js/demo_pages/form_layouts.js"></script>

	<!-- Validação -->
	<script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
	<script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
	<script src="global_assets/js/demo_pages/form_validation.js"></script>	<!-- CV Documentacao: https://jqueryvalidation.org/ -->	
	
	<script src="global_assets/js/plugins/buttons/spin.min.js"></script>
	<script src="global_assets/js/plugins/buttons/ladda.min.js"></script>	
	<script src="global_assets/js/demo_pages/components_buttons.js"></script>

	<script src="global_assets/js/plugins/visualization/echarts/echarts.min.js"></script>
	<script src="global_assets/js/demo_pages/charts/echarts/lines.js"></script>
	<script src="global_assets/js/demo_pages/charts/echarts/areas.js"></script>
	<!-- /theme JS files -->	
	
	<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/echarts@4/map/js/china.js?_v_=1611323308745"></script>
	<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/echarts@4/map/js/world.js?_v_=1611323308745"></script>
	<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/echarts@4/dist/extension/bmap.js?_v_=1611323308745"></script>

	<script type="text/javascript">
		
		$(document).ready(function() {	

			$('#grafico').hide();

			FiltraSetor();
		
			//Ao mudar a categoria, filtra a subcategoria e produto via ajax (retorno via JSON)
			$('#cmbCategoria').on('change', function(e){
				
				FiltraSubCategoria();
				
				var cmbCategoria = $('#cmbCategoria').val();

				$.getJSON('filtraSubCategoria.php?idCategoria='+cmbCategoria, function (dados){
					
					var option = '<option value="">Selecione a SubCategoria</option>';
					
					if (dados.length){						
						
						$.each(dados, function(i, obj){
							option += '<option value="'+obj.SbCatId+'">'+obj.SbCatNome+'</option>';
						});						
						
						$('#cmbSubCategoria').html(option).show();
					} else {
						ResetSubCategoria();
					}					
				});				
			});

			//Ao mudar a categoria, filtra a subcategoria e produto via ajax (retorno via JSON)
			$('#cmbUnidade').on('change', function(e){
				
				FiltraSetor();
			});	
			
			Ladda.bind('.btn-ladda-progress1', {
				callback: function(instance) {
					var progress = 0;
					var interval = setInterval(function() {
						progress = Math.min(progress + Math.random() * 0.1, 1);
						instance.setProgress(progress);

						if( progress === 1 ) {
							instance.stop();
							clearInterval(interval);
						}
					}, 200);
				}
			});				
					
			$('#enviar').on('click', (e) => {
				
				e.preventDefault();		

				let inputDataInicio = $('#inputDataInicio').val();
				let inputDataFim = $('#inputDataFim').val();
				let cmbUnidade = $('#cmbUnidade').val();
				let cmbSetor = $('#cmbSetor').val();
				let cmbCategoria = $('#cmbCategoria').val();
				let cmbSubCategoria = $('#cmbSubCategoria').val();
				let cmbClassificacao = $('#cmbClassificacao').val();	

				let url = "relatorioCurvaABCFiltra.php";

				inputsValues = {
					inputDataInicio: inputDataInicio,
					inputDataFim: inputDataFim,
					cmbUnidade: cmbUnidade,
					cmbSetor: cmbSetor,
					cmbCategoria: cmbCategoria,
					cmbSubCategoria: cmbSubCategoria,
					cmbClassificacao: cmbClassificacao
				};
				console.log(inputsValues)

				$.post(
					url,
					inputsValues,
					(data) => {

						if (data) {
							$('#resultado').removeClass('justify-content-center px-2');
							$('#resultado').html(data);

							$('#grafico').show();

							let linhaX_saidasA = document.getElementById('inputSaidasA').value;
							let linhaX_saidasB = document.getElementById('inputSaidasB').value;
							let linhaX_saidasC = document.getElementById('inputSaidasC').value;

							linhaX_saidasB = parseFloat(linhaX_saidasB) + parseFloat(linhaX_saidasA);
							linhaX_saidasC = parseFloat(linhaX_saidasC) + parseFloat(linhaX_saidasB);

							let saidasA_meio = parseFloat(linhaX_saidasA) / 2;
							let saidasB_meio = (parseFloat(linhaX_saidasB) - parseFloat(linhaX_saidasA))/2 + parseFloat(linhaX_saidasA);
							let saidasC_meio = (parseFloat(linhaX_saidasC) - parseFloat(linhaX_saidasB))/2 + parseFloat(linhaX_saidasB);

							var area_basic_element1 = document.getElementById('area_basic1');

				            // Initialize chart
				            var area_basic1 = echarts.init(area_basic_element1);

				            //
				            // Chart config
				            //

							// Options
							area_basic1.setOption({

							// Define colors
							color: ['#F55246', '#009688','#339EF4'],

							// Global text styles
							textStyle: {
								fontFamily: 'Roboto, Arial, Verdana, sans-serif',
								fontSize: 13
							},

							// Chart animation duration
							animationDuration: 750,

							// Setup grid
							grid: {
								left: 0,
								right: 40,
								top: 35,
								bottom: 0,
								containLabel: true
							},

							// Add legend
							legend: {
								data: ['A', 'B', 'C'],
								itemHeight: 8,
								itemGap: 20
							},

							// Add tooltip
							tooltip: {
								trigger: 'axis',
								backgroundColor: 'rgba(0,0,0,0.75)',
								padding: [10, 15],
								textStyle: {
									fontSize: 13,
									fontFamily: 'Roboto, sans-serif'
								},
								formatter: "{a}<br/>{c} (%)",  // ao passar o mouse ele mostra (https://echarts.apache.org/en/option.html#tooltip.formatter)
								axisPointer: {  // CV: adicionei para o cursor ficar marcando a medida que eu passo o mouse no gráfico
									type: 'cross',
									label: {
										backgroundColor: '#6a7985'
									}
								}								
							},

							// Horizontal axis
							xAxis: [{
								type: 'value',  //category (esse não funciona sequencialmente, daí passei para value)
								boundaryGap: false,
								data: ['0', parseFloat(linhaX_saidasA).toFixed(2) + '(%)', parseFloat(linhaX_saidasB).toFixed(2) + '%', parseFloat(linhaX_saidasC).toFixed(2) + '%'],
								axisLabel: {
									color: '#333'
								},
								axisLine: {
									lineStyle: {
										color: '#999'
									}
								},
								splitLine: {
									show: true,
									lineStyle: {
										color: '#eee',
										type: 'dashed'
									}
								}
							}],

							// Vertical axis
							yAxis: [{
								type: 'value',
								axisLabel: {
									color: '#333'
								},
								axisLine: {
									lineStyle: {
										color: '#999'
									}
								},
								splitLine: {
									lineStyle: {
										color: '#eee'
									}
								},
								splitArea: {
									show: true,
									areaStyle: {
										color: ['rgba(250,250,250,0.1)', 'rgba(0,0,0,0.01)']
									}
								}
							}],

							// Add series
							series: [
								{
									name: 'A',
									type: 'line',
									data: [
											[0, 0], 
											[parseFloat(saidasA_meio).toFixed(2), 50],
											[parseFloat(linhaX_saidasA).toFixed(2), 80]
										  ], // 0, 33
									areaStyle: {
										normal: {
											opacity: 0.25
										}
									},
									smooth: true,
									symbolSize: 7,
									itemStyle: {
										normal: {
											borderWidth: 2
										}
									}
								},
								{
									name: 'B',
									type: 'line',
									smooth: true,
									symbolSize: 7,
									itemStyle: {
										normal: {
											borderWidth: 2
										}
									},
									data: [
											[parseFloat(linhaX_saidasA).toFixed(2), 80], 
											[parseFloat(saidasB_meio).toFixed(2), 89], // O 89 não precisa mexer
											[parseFloat(linhaX_saidasB).toFixed(2), 95]
										  ],	  // 33, 51
									areaStyle: {
										normal: {
											opacity: 0.25
										}
									}									
								},
								{
									name: 'C',
									type: 'line',
									smooth: true,
									symbolSize: 7,
									itemStyle: {
										normal: {
											borderWidth: 2
										}
									},
									areaStyle: {
										normal: {
											opacity: 0.25
										}
									},
									data: [
											[parseFloat(linhaX_saidasB).toFixed(2), 95],
											[parseFloat(saidasC_meio).toFixed(2), 98], // O 98 não precisa mexer.
											[100, 100]
									]
								}
							]
							});
					        //
					        // Resize charts
					        //

					        // Resize function
					        var triggerChartResize = function() {
					        	line_basic_element1 && line_basic1.resize();
					            area_basic_element1 && area_basic1.resize();
					        };

					        // On sidebar width change
					        $(document).on('click', '.sidebar-control', function() {
					            setTimeout(function () {
					                triggerChartResize();
					            }, 0);
					        });

					        // On window resize
					        var resizeCharts;
					        window.onresize = function () {
					            clearTimeout(resizeCharts);
					            resizeCharts = setTimeout(function () {
					                triggerChartResize();
					            }, 200);
					        };



						} else {
							semResultados();
						}
						
						//$('#enviar').removeClass('btn-ladda btn-ladda-progress');
					}
				)
			});	

			function semResultados() {
				const msg = $('<div class="card" style="width: 100%"><p class="text-center m-2">Sem resultados...</p></div>');

				$('#resultado').html(msg);
				$('#resultado').addClass('justify-content-center px-2').css('width', '100%');
			}
	
		});

		//Mostra o "Filtrando..." na combo SubCategoria
		function FiltraSubCategoria(){
			$('#cmbSubCategoria').empty().append('<option value="">Filtrando...</option>');
		}			

		//Mostra o "Filtrando..." na combo Setor
		function FiltraSetor(){

			$('#cmbSetor').empty().append('<option value="">Filtrando...</option>');

			var cmbUnidade = $('#cmbUnidade').val();

			$.getJSON('filtraSetor.php?idUnidade='+cmbUnidade, function (dados){
				
				var option = '<option value="">Selecione o Setor</option>';
				
				if (dados.length){						
					
					$.each(dados, function(i, obj){
						option += '<option value="'+obj.SetorId+'">'+obj.SetorNome+'</option>';
					});						
					
					$('#cmbSetor').html(option).show();
				} else {
					ResetSetor();
				}					
			});		

		}

		function ResetSubCategoria(){
			$('#cmbSubCategoria').empty().append('<option value="">Sem SubCategoria</option>');
		}

		function ResetSetor(){
			$('#cmbSetor').empty().append('<option value="">Sem Setor</option>');
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
				<div class="row">
					<div class="col-lg-12">
						<!-- Basic responsive configuration -->
						<div class="card">
							<div class="card-header header-elements-inline">
								<h3 class="card-title">Relatório Curva ABC</h3>
								<div class="header-elements">
									<div class="list-icons">
										<a class="list-icons-item" data-action="collapse"></a>
										<a href="#" class="list-icons-item" data-action="reload"></a>
										<!--<a class="list-icons-item" data-action="remove"></a>-->
									</div>
								</div>
							</div>

							<div class="card-body">
								<form name="formCurvaABC" method="post">

									<div class="row">

										<div class="col-lg-2">
											<div class="form-group">
												<label for="inputDataInicio">Data Início <span class="text-danger">*</span></label>
												<div class="input-group">
													<span class="input-group-prepend">
														<span class="input-group-text"><i class="icon-calendar22"></i></span>
													</span>
													<input type="date" id="inputDataInicio" name="inputDataInicio" class="form-control" placeholder="Data Início" value="<?php echo $dataInicio; ?>" required>
												</div>
											</div>
										</div>
										
										<div class="col-lg-2">
											<div class="form-group">
												<label for="inputDataFim">Data Fim <span class="text-danger">*</span></label>
												<div class="input-group">
													<span class="input-group-prepend">
														<span class="input-group-text"><i class="icon-calendar22"></i></span>
													</span>
													<input type="date" id="inputDataFim" name="inputDataFim" class="form-control" placeholder="Data Fim" value="<?php echo $dataFim; ?>" required>
												</div>
											</div>
										</div>

										<div class="col-lg-4">
											<div class="form-group">
												<label for="cmbUnidade">Unidade</label>
												<select id="cmbUnidade" name="cmbUnidade" class="form-control form-control-select2">
													
													<?php 
														$sql = "SELECT UnidaId, UnidaNome
																FROM Unidade
																JOIN Situacao on SituaId = UnidaStatus
																WHERE UnidaEmpresa = ". $_SESSION['EmpreId'] ." and SituaChave = 'ATIVO'
																ORDER BY UnidaNome ASC";
														$result = $conn->query($sql);
														$rowUnidade = $result->fetchAll(PDO::FETCH_ASSOC);
														
														foreach ($rowUnidade as $item){
															if($item['UnidaId'] == $_SESSION['UnidadeId']){
																print('<option value="' . $item['UnidaId'] . '" selected>' . $item['UnidaNome'] . '</option>');   
															} else {
																print('<option value="' . $item['UnidaId'] . '">' . $item['UnidaNome'] . '</option>');
															}
														}
													
													?>
												</select>
											</div>
										</div>

										<div class="col-lg-4">
											<div class="form-group">
												<label for="cmbSetor">Setor</label>
												<select id="cmbSetor" name="cmbSetor" class="form-control form-control-select2">
													<option value="">Selecione</option>													
												</select>
											</div>
										</div>										
									</div>
									
									<div class="row">

										<div class="col-lg-4">
											<div class="form-group">
												<label for="cmbCategoria">Categoria</label>
												<select id="cmbCategoria" name="cmbCategoria" class="form-control form-control-select2">
													<option value="">Selecione</option>
													<?php 
														$sql = "SELECT CategId, CategNome
																FROM Categoria
																JOIN Situacao on SituaId = CategStatus
																WHERE CategEmpresa = ".$_SESSION['EmpreId']." and SituaChave = 'ATIVO'
																ORDER BY CategNome ASC";
														$result = $conn->query($sql);
														$rowCategoria = $result->fetchAll(PDO::FETCH_ASSOC);
														
														foreach ($rowCategoria as $item){			
															//$seleciona = $item['CategId'] == $row['FlOpeCategoria'] ? "selected" : "";
															print('<option value="'.$item['CategId'].'">'.$item['CategNome'].'</option>');
														}
													
													?>											
												</select>
											</div>
										</div>
										
										<div class="col-lg-4">
											<label for="cmbSubCategoria">SubCategoria</label>
											<select id="cmbSubCategoria" name="cmbSubCategoria" class="form-control form-control-select2">
												<option value="">Selecione</option>
												<?php 
												 
													$sql = "SELECT SbCatId, SbCatNome
															FROM SubCategoria
															JOIN Situacao on SituaId = SbCatStatus
															WHERE SbCatEmpresa = ".$_SESSION['EmpreId']." and SituaChave = 'ATIVO'
															Order By SbCatNome ASC";
													$result = $conn->query($sql);
													$rowSubCategoria = $result->fetchAll(PDO::FETCH_ASSOC);
													
													foreach ($rowSubCategoria as $item){			
														//$seleciona = $item['SbCatId'] == $row['FlOpeSubCategoria'] ? "selected" : "";
														print('<option value="'.$item['SbCatId'].'">'.$item['SbCatNome'].'</option>');
													}
												
												?>										
											</select>
										</div>	

										<div class="col-lg-4">
											<div class="form-group">
												<label for="cmbClassificacao">Classificação</label>
												<select id="cmbClassificacao" name="cmbClassificacao/Bens" class="form-control form-control-select2" >
													<option value="">Selecione</option>
													<?php 
														$sql = "SELECT ClassId, ClassNome, ClassChave
																FROM Classificacao
																JOIN Situacao on SituaId = ClassStatus
																WHERE SituaChave = 'ATIVO'
																ORDER BY ClassNome ASC";
														$result = $conn->query($sql);
														$rowClassificacao = $result->fetchAll(PDO::FETCH_ASSOC);
														
														$seleciona = "";

														foreach ($rowClassificacao as $item){
															
															//Seta o Material de Consumo como padrão no filtro
															if ($item['ClassChave'] == 'CONSUMO'){
																$seleciona = "selected";
															} else{
																$seleciona = "";
															}
															print('<option value="'.$item['ClassId'].'" '.$seleciona.'>'.$item['ClassNome'].'</option>');
														}
													
													?>
												</select>
											</div>
										</div>
									</div>

									<div class="text-right">
										<button id="enviar" class="btn bg-success btn-ladda btn-ladda-progress1" data-style="expand-left" data-spinner-size="20" role="button">Filtrar</button> 
										<button id="imprimir" class="btn btn-secondary btn-icon" disabled>
                                            <i class="icon-printer2"> Imprimir</i>
                                        </button>
									</div>

								</form>
							</div>
						</div>
						<!-- /basic responsive configuration -->
					</div>
				</div>								
				<!-- /info blocks -->
				
				<!-- Info blocks -->		
				<div class="row" id="resultado">

				</div>				
				<!-- /info blocks -->

				<div class="card" id="grafico">
					<div class="card-body">
						<div class="chart-container">
							<div class="chart has-fixed-height" id="area_basic1"></div>
						</div>
					</div>
				</div>
			</div>
			<!-- /content area -->
			
			<?php include_once("footer.php"); ?>

		</div>
		<!-- /main content -->

	</div>
	<!-- /page content -->

	<?php include_once("alerta.php"); ?>

</body>

</html>
