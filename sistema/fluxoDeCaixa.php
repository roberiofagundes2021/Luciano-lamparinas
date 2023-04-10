<?php 

include('global_assets/php/conexao.php');
include_once("sessao.php"); 
$_SESSION['PaginaAtual'] = 'Fluxo Realizado';

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Fluxo Realizado</title>

	<?php include_once("head.php"); ?>
	
	<!-- Theme JS files --> 
	<script src="global_assets/js/plugins/tables/datatables/datatables.min.js"></script>
	<script src="global_assets/js/plugins/tables/datatables/extensions/responsive.min.js"></script>
	<script src="global_assets/js/demo_pages/datatables_responsive.js"></script>
	<script src="global_assets/js/demo_pages/datatables_sorting.js"></script>

	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
	<script src="global_assets/js/demo_pages/form_layouts.js"></script>

	<script src="global_assets/js/plugins/forms/selects/bootstrap_multiselect.js"></script>
	<script src="global_assets/js/demo_pages/form_multiselect.js"></script>	

	<!-- Plugin para corrigir a ordenação por data. Caso a URL dê problema algum dia, salvei esses 2 arquivos na pasta global_assets/js/lamparinas -->
	<script type="text/javascript" language="javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.8.4/moment.min.js"></script>
	<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/plug-ins/1.10.10/sorting/datetime-moment.js"></script>		
	
	<!-- /theme JS files -->	
	
	<script type="text/javascript">
		$(document).ready(function() {
			$('.form-check-label > input[type="checkbox"][value="multiselect-all"]').prop( "checked", true );
		});

		//A função $(document).on... trabalha dinâmicamente, ou seja, funciona antes q o objeto seja carregado na página
		//Carrega os planos de contas sintéticos
		$(document).on("click", ".planoConta", function(){
			let planoConta = $(this).attr('id');
			let indice = planoConta.replace(/[^0-9]/g,''); //Pega apenas o número da string
			let idPlanoContaPai1 = $("#idPlanoConta"+indice).val();
			let filtroPlanoConta = $('#cmbPlanoContas').val();
			let data1 = $('#dataInicial'+indice).val();
			let dataFinal1 = $('#dataFinal'+indice).val();
			let data2 = $('#dataInicialSegundaColuna'+indice).val();
			let dataFinal2 = $('#dataFinalSegundaColuna'+indice).val();
			let data3 = $('#dataInicialTerceiraColuna'+indice).val();
			let dataFinal3 = $('#dataFinalTerceiraColuna'+indice).val();
			let data4 = $('#dataInicialQuartaColuna'+indice).val();
			let dataFinal4 = $('#dataFinalQuartaColuna'+indice).val();

			if ($('#'+planoConta).is( ".visivel" ) ) {
				$('#'+planoConta).removeClass("visivel");
				$('#'+planoConta).addClass("minimizado");
				
				$("#planoContaPai"+indice).html('')
				$('#simbolo'+indice).html('( + ) ')
				$('#simbolo'+indice).css("color","#607D8B")
			}else {
				$('#'+planoConta).removeClass("minimizado");
				$('#'+planoConta).addClass("visivel");
			
				let HTML = '';

				const urlConsultaPlanoConta = "consultaPlanoConta.php";

				var inputsValuesConsulta = {
					inputPlanoConta1: idPlanoContaPai1,
					inputFiltroPlanoConta: filtroPlanoConta,
					inputDataInicial1: data1,
					inputDataFinal1: dataFinal1,
					inputDataInicial2: data2,
					inputDataFinal2: dataFinal2,
					inputDataInicial3: data3,
					inputDataFinal3: dataFinal3,
					inputDataInicial4: data4,
					inputDataFinal4: dataFinal4
				}; 

				const msg = $('<div class="text-center"><img src="global_assets/images/lamparinas/loader.gif" style="width: 120px"></div>');
				$("#planoContaPai"+indice).html(msg)

				//Consulta Plano Conta Analítico
				$.ajax({
					type: "POST",
					url: urlConsultaPlanoConta,
					dataType: "json",
					data: inputsValuesConsulta,
					success: function(resposta) {
						if(resposta[0][0]) {
							for(let x = 0; x < resposta[0].length; x++) {
								let planoConta = resposta[0][x]
								let segundaColuna = resposta[1];
								let terceiraColuna = resposta[2];
								let quartaColuna = resposta[3];
								let cor = '';
								
								HTML = HTML + ` 
										<div class='row' style='background: #eeeeee; line-height: 3rem; box-sizing:border-box'>`;
								
								let arrayCodigo = planoConta.PlConCodigo.split('');

								/*Planos de contas q começam com o código 1 são receitas, portanto no momento ainda não possuem centros de custo*/
								if(arrayCodigo[0] == '1') {
									HTML = HTML + 
												`<div id='planoContaFilho`+planoConta.PlConId+indice+`' indice='`+indice+`' idPlanoContaFilho='`+planoConta.PlConId+`' class='col-lg-3' style='padding-left: 20px; border-right: 1px dotted black;'>
													<span title=''>`+planoConta.PlConNome+`</span>
												</div>`;
								}else {
									HTML = HTML + 
												`<div id='planoContaFilho`+planoConta.PlConId+indice+`' indice='`+indice+`' idPlanoContaFilho='`+planoConta.PlConId+`' class='col-lg-3 planoContaFilho' style='padding-left: 20px; border-right: 1px dotted black; cursor:pointer;'>
													<span title=''><span id='simboloFilho`+planoConta.PlConId+indice+`' style='font-weight: bold; color: #607D8B;'>( + ) </span>`+planoConta.PlConNome+`</span>
												</div>`;
								}

								/*Todas as contas que não tenham o primeiro código como 1, ou seja despesas, devem ser da cor vermelha*/
								cor = (arrayCodigo[0] != '1') ? ' style="color: red;"' : '';
									
								sinalPrevisto1 = planoConta.Previsto > 0 && arrayCodigo[0] != '1' ? ' - ' : '';
								sinalRealizado1 = planoConta.Realizado > 0 && arrayCodigo[0] != '1' ? ' - ' : '';
								HTML = HTML + 
											`<div class='dataOpeningBalance col-lg-2' style='border-right: 1px dotted black; text-align:center;'>
												<div class='row'>
												<div class='col-md-6'>
													<span `+cor+`>`+sinalPrevisto1+float2moeda(planoConta.Previsto)+`</span>
												</div>
									
												<div class='col-md-6'>
													<span `+cor+`>`+sinalRealizado1+float2moeda(planoConta.Realizado)+`</span>
												</div>
												</div>
											</div>`;
								
								if(segundaColuna != '') {
									sinalPrevisto2 = planoConta.Previsto2 > 0 && arrayCodigo[0] != '1' ? ' - ' : '';
									sinalRealizado2 = planoConta.Realizado2 > 0 && arrayCodigo[0] != '1' ? ' - ' : '';

									HTML = HTML + 
												`<div class='dataOpeningBalance col-lg-2' style='border-right: 1px dotted black; text-align:center;'>
													<div class='row'>
													<div class='col-md-6'>
														<span `+cor+`>`+sinalPrevisto2+float2moeda(planoConta.Previsto2)+`</span>
													</div>
										
													<div class='col-md-6'>
														<span `+cor+`>`+sinalRealizado2+float2moeda(planoConta.Realizado2)+`</span>
													</div>
													</div>
												</div>`;
								}

								if(terceiraColuna != '') {
									sinalPrevisto3 = planoConta.Previsto3 > 0 && arrayCodigo[0] != '1' ? ' - ' : '';
									sinalRealizado3 = planoConta.Realizado3 > 0 && arrayCodigo[0] != '1' ? ' - ' : '';

									HTML = HTML + 
												`<div class='dataOpeningBalance col-lg-2' style='border-right: 1px dotted black; text-align:center;'>
													<div class='row'>
													<div class='col-md-6'>
														<span `+cor+`>`+sinalPrevisto3+float2moeda(planoConta.Previsto3)+`</span>
													</div>
										
													<div class='col-md-6'>
														<span `+cor+`>`+sinalRealizado3+float2moeda(planoConta.Realizado3)+`</span>
													</div>
													</div>
												</div>`;
								}

								if(quartaColuna != '') {
									sinalPrevisto4 = planoConta.Previsto4 > 0 && arrayCodigo[0] != '1' ? ' - ' : '';
									sinalRealizado4 = planoConta.Realizado4 > 0 && arrayCodigo[0] != '1' ? ' - ' : '';

									HTML = HTML + 
												`<div class='dataOpeningBalance col-lg-2' style='border-right: 1px dotted black; text-align:center;'>
													<div class='row'>
													<div class='col-md-6'>
														<span `+cor+`>`+sinalPrevisto4+float2moeda(planoConta.Previsto4)+`</span>
													</div>
										
													<div class='col-md-6'>
														<span `+cor+`>`+sinalRealizado4+float2moeda(planoConta.Realizado4)+`</span>
													</div>
													</div>
												</div>`;
								}
								
								HTML = HTML + 
										`</div>

										<div id='centroCusto`+planoConta.PlConId+indice+`'>
										</div>`;
							}

						}else {
							HTML = HTML + `
									<div class='row' style='background: #eeeeee; line-height: 3rem; box-sizing:border-box'>
										<div class='col-lg-12 text-center'>
											<span title=''>Não há registros</span>
										</div>
									</div>`;
						}

						$("#planoContaPai"+indice).html(HTML)
					}
				})

				$('#simbolo'+indice).html('( - ) ')
				$('#simbolo'+indice).css("color","red")
			}
		});

		//Carrega os planos de contas analíticos
		$(document).on("click", ".planoContaFilho", function(){
			let planoContaFilho = $(this).attr('id');
			let idPlanoConta = $(this).attr('idPlanoContaFilho');
			let idPlanoContaFilho1 = idPlanoConta;
			let indicePlanoConta = planoContaFilho.replace(/[^0-9]/g,''); //Pega apenas o número da string
			let indice = $(this).attr('indice');
			let filtroCentroCusto = $('#cmbCentroDeCustos').val();
			let data1 = $('#dataInicial'+indice).val();
			let dataFinal1 = $('#dataFinal'+indice).val();
			let data2 = $('#dataInicialSegundaColuna'+indice).val();
			let dataFinal2 = $('#dataFinalSegundaColuna'+indice).val();
			let data3 = $('#dataInicialTerceiraColuna'+indice).val();
			let dataFinal3 = $('#dataFinalTerceiraColuna'+indice).val();
			let data4 = $('#dataInicialQuartaColuna'+indice).val();			
			let dataFinal4 = $('#dataFinalQuartaColuna'+indice).val();

			if ($('#'+planoContaFilho ).is( ".visivel" ) ) {
				$('#'+planoContaFilho).removeClass("visivel");
				$('#'+planoContaFilho).addClass("minimizado");
				
				$("#centroCusto"+indicePlanoConta).html('')
				$('#simboloFilho'+indicePlanoConta).html('( + ) ')
				$('#simboloFilho'+indicePlanoConta).css("color","#607D8B")
			}else {
				$('#'+planoContaFilho).removeClass("minimizado");
				$('#'+planoContaFilho).addClass("visivel");
			
				let HTML = '';

				const urlConsultaPlanoConta = "consultaCentroCusto.php";

				var inputsValuesConsulta = {
					inputPlanoConta1: idPlanoContaFilho1,
					inputFiltroCentroCusto: filtroCentroCusto,
					inputDataInicial1: data1,
					inputDataFinal1: dataFinal1,
					inputDataInicial2: data2,
					inputDataFinal2: dataFinal2,
					inputDataInicial3: data3,
					inputDataFinal3: dataFinal3,
					inputDataInicial4: data4,
					inputDataFinal4: dataFinal4
				}; 

				const msg = $('<div class="text-center"><img src="global_assets/images/lamparinas/loader.gif" style="width: 120px"></div>');
				$("#centroCusto"+indicePlanoConta).html(msg)
				
				//Consulta os centros de custo
				$.ajax({
					type: "POST",
					url: urlConsultaPlanoConta,
					dataType: "json",
					data: inputsValuesConsulta,
					success: function(resposta) {
						if(resposta[0][0]) {
							for(let x = 0; x < resposta[0].length; x++) {
								let centroCusto = resposta[0][x];
								let segundaColuna = resposta[1];
								let terceiraColuna = resposta[2];
								let quartaColuna = resposta[3];

								var centroCustoDescr = centroCusto.CnCusNomePersonalizado !== null ? centroCusto.CnCusNomePersonalizado : centroCusto.CnCusNome;
							
								/*No momento só há Centros de Custos nas despesas, então estão todos em vermelho*/
								cor = ' style="color: red;"';

								sinalPrevisto1 = centroCusto.Previsto > 0 ? '-' : '';
								sinalRealizado1 = centroCusto.Realizado > 0 ? '-' : '';
								
								HTML = HTML + `
										<div class='row' style='background: #f8f8f8; line-height: 3rem; box-sizing:border-box'>
											<div class='col-lg-3 planoContaFilho' style='padding-left: 40px; border-right: 1px dotted black;'>
												<span title=''>`+centroCustoDescr+`</span>
											</div>
									
											<div class='dataOpeningBalance col-lg-2' style='border-right: 1px dotted black; text-align:center;'>
												<div class='row'>
													<div class='col-md-6'>
														<span `+cor+`>`+sinalPrevisto1+float2moeda(centroCusto.Previsto)+`</span>
													</div>
										
													<div class='col-md-6'>
														<span `+cor+`>`+sinalRealizado1+float2moeda(centroCusto.Realizado)+`</span>
													</div>
												</div>
											</div>`;
								
								if(segundaColuna != '') {
									sinalPrevisto2 = centroCusto.Previsto2 > 0 ? '-' : '';
									sinalRealizado2 = centroCusto.Realizado2 > 0 ? '-' : '';
								
									HTML = HTML + 
												`<div class='dataOpeningBalance col-lg-2' style='border-right: 1px dotted black; text-align:center;'>
													<div class='row'>
													<div class='col-md-6'>
														<span `+cor+`>`+sinalPrevisto2+float2moeda(centroCusto.Previsto2)+`</span>
													</div>
										
													<div class='col-md-6'>
														<span `+cor+`>`+sinalRealizado2+float2moeda(centroCusto.Realizado2)+`</span>
													</div>
													</div>
												</div>`;
								}

								if(terceiraColuna != '') {
									sinalPrevisto3 = centroCusto.Previsto3 > 0 ? '-' : '';
									sinalRealizado3 = centroCusto.Realizado3 > 0 ? '-' : '';
								
									HTML = HTML + 
												`<div class='dataOpeningBalance col-lg-2' style='border-right: 1px dotted black; text-align:center;'>
													<div class='row'>
													<div class='col-md-6'>
														<span `+cor+`>`+sinalPrevisto3+float2moeda(centroCusto.Previsto3)+`</span>
													</div>
										
													<div class='col-md-6'>
														<span `+cor+`>`+sinalRealizado3+float2moeda(centroCusto.Realizado3)+`</span>
													</div>
													</div>
												</div>`;
								}

								if(quartaColuna != '') {
									sinalPrevisto4 = centroCusto.Previsto4 > 0 ? '-' : '';
									sinalRealizado4 = centroCusto.Realizado4 > 0 ? '-' : '';
								
									HTML = HTML + 
												`<div class='dataOpeningBalance col-lg-2' style='border-right: 1px dotted black; text-align:center;'>
													<div class='row'>
													<div class='col-md-6'>
														<span `+cor+`>`+sinalPrevisto4+float2moeda(centroCusto.Previsto4)+`</span>
													</div>
										
													<div class='col-md-6'>
														<span `+cor+`>`+sinalRealizado4+float2moeda(centroCusto.Realizado4)+`</span>
													</div>
													</div>
												</div>`;
								}
								
								HTML = HTML + 
										`</div>`;
							}

						}else {
							HTML = HTML + `
									<div class='row' style='background: #f8f8f8; line-height: 3rem; box-sizing:border-box'>
										<div class='col-lg-12 text-center'>
											<span title=''>Não há registros</span>
										</div>
									</div>`;
						}

						$("#centroCusto"+indicePlanoConta).html(HTML)
					}
				})

				$('#simboloFilho'+indicePlanoConta).html('( - ) ')
				$('#simboloFilho'+indicePlanoConta).css("color","red")
			}
		});
		
		document.addEventListener('DOMContentLoaded', () => {
			// Atribuição dos campos de filtro da tela
			const buttonDay = document.querySelector('#submitDay');
			const buttonMonth = document.querySelector('#submitMonth');
			const inputDateInitial = document.querySelector('#inputDataInicio');
			const inputDateEnd = document.querySelector('#inputDataFim');
			const submitPesquisar = document.querySelector('#submitPesquisar');
			const cmbCentroDeCustosItens = $(".centroDeCustosClass");
			/////////////////////////////////////
			cmbCentroDeCustosItens.each((i, element) => {
				
				if($(element).hasClass('multiselect-item')){
					let check = $(element).children().children().first();
                    let value = $(check).val()
					check.click((e) => {
						try {
						    $.post(
						    	url,
						    	request,
						    	(response) => {
						    		if (response) {
						    			
						    		}
						    	}
						    );
					    } catch(err) {
					        console.error('Houve um error: ',err);
					    }
					})
		
				}
			});
			////////////////////////////////////////

			inputDateInitial.addEventListener('change', (e) => {
				const monthInitial = (inputDateInitial.value).split('-')[1] ? (inputDateInitial.value).split('-')[1] : "";
				const monthEnd = (inputDateEnd.value).split('-')[1] ? (inputDateEnd.value).split('-')[1] : "";
				const dayInitial = (inputDateInitial.value).split('-')[2] ? (inputDateInitial.value).split('-')[2] : "";
				const dayEnd = (inputDateEnd.value).split('-')[2] ? (inputDateEnd.value).split('-')[2] : "";
				const typeDate = document.querySelector('.btn.active').textContent;
				const yearInitial = (inputDateInitial.value).split('-')[0] ? (inputDateInitial.value).split('-')[0] : "";
				const yearEnd = (inputDateEnd.value).split('-')[0] ? (inputDateEnd.value).split('-')[0] : "";

				if (typeDate === 'Dia') {
					if ((monthEnd !== '' && monthEnd !== null) && (monthInitial !== '' && monthInitial !== null) && (monthEnd !== monthInitial)) {
						alerta('Atenção','A data final está com um mês diferente da data que você está informando. Você só pode pesquisar períodos dentro do mesmo mês!', 'error');
						inputDateInitial.value = "";
					} else if ((dayInitial !== '' && dayInitial !== null) && (dayEnd !== '' && dayEnd !== null) && (dayInitial > dayEnd)) {
						alerta('Atenção','A data inicial tem que ser menor que a data final!', 'error');
						inputDateInitial.value = "";
					}
				}

				if (typeDate === 'Mês') {
					if ((yearInitial !== '' && yearInitial !== null) && (yearEnd !== '' && yearEnd !== null) && (yearInitial != yearEnd)) {
						alerta('Atenção','Informe um período detro do mesmo ano!', 'error');
						inputDateInitial.value = "";
					}
				}
				
			});


			inputDateEnd.addEventListener('change', (e) => {
				const monthInitial = (inputDateInitial.value).split('-')[1] ? (inputDateInitial.value).split('-')[1] : "";
				const monthEnd = (inputDateEnd.value).split('-')[1] ? (inputDateEnd.value).split('-')[1] : "";
				const dayInitial = (inputDateInitial.value).split('-')[2] ? (inputDateInitial.value).split('-')[2] : "";
				const dayEnd = (inputDateEnd.value).split('-')[2] ? (inputDateEnd.value).split('-')[2] : "";
				const typeDate = document.querySelector('.btn.active').textContent;
				const yearInitial = (inputDateInitial.value).split('-')[0] ? (inputDateInitial.value).split('-')[0] : "";
				const yearEnd = (inputDateEnd.value).split('-')[0] ? (inputDateEnd.value).split('-')[0] : "";

				if (typeDate === 'Dia') {
					if ((monthEnd !== '' && monthEnd !== null) && (monthInitial !== '' && monthInitial !== null) && (monthEnd !== monthInitial)) {
						alerta('Atenção','A data inicial está com um mês diferente da data que você está informando. Você só pode pesquisar períodos dentro do mesmo mês!', 'error');
						inputDateEnd.value = "";
					} else if ((dayInitial !== '' && dayInitial !== null) && (dayEnd !== '' && dayEnd !== null) && (dayEnd < dayInitial)) {
						alerta('Atenção','A data final tem que ser maior que a data inicial!', 'error');
						inputDateEnd.value = "";
					}
				}

				if (typeDate === 'Mês') {
					if ((yearInitial !== '' && yearInitial !== null) && (yearEnd !== '' && yearEnd !== null) && (yearInitial != yearEnd)) {
						alerta('Atenção','Informe um período detro do mesmo ano!', 'error');
						inputDateInitial.value = "";
					}
				}
			});


			buttonDay.addEventListener('click', (e) => {
				e.preventDefault();
				inputDateInitial.type = 'date';
				inputDateEnd.type = 'date';

				buttonDay.style.background = '#607D8B';
				buttonDay.style.color = 'white';
				buttonDay.classList.add('active');

				buttonMonth.style.background = '#CCCCCC';
				buttonMonth.style.color = 'black';
				buttonMonth.classList.remove('active');
			})


			buttonMonth.addEventListener('click', (e) => {
				e.preventDefault();
				inputDateInitial.type = 'month';
				inputDateEnd.type = 'month';

				buttonMonth.style.background = '#607D8B';
				buttonMonth.style.color = 'white';
				buttonMonth.classList.add('active');

				buttonDay.style.background = '#CCCCCC';
				buttonDay.style.color = 'black';
				buttonDay.classList.remove('active');
			});

			submitPesquisar.addEventListener('click', (e) => {
				e.preventDefault();
				const cmbCentroDeCustosReq = $('#cmbCentroDeCustos').val();
				const cmbPlanoContasReq = $('#cmbPlanoContas').val();
				const msg = $('<div style="width:100%; text-align:center;"><img src="global_assets/images/lamparinas/loader.gif" style="width: 120px"></div>');

				if (inputDateInitial.value === '' || inputDateInitial.value === null){
					alerta('Atenção','Informe o período inicial!', 'error');
					return false;
				}
				
				if (inputDateEnd.value === '' || inputDateEnd.value === null){
					alerta('Atenção','Informe o período final!', 'error');
					return false;
				}
				
				if (cmbCentroDeCustos === '' || cmbCentroDeCustos === null){
					alerta('Atenção','Selecione pelo menos um Centro de Custo!', 'error');
					return false;
				}
										
				if (cmbPlanoContas === '' || cmbPlanoContas === null){
					alerta('Atenção','Selecione pelo menos um Plano de Contas!', 'error');
					return false;
				}

				const getData = () => {
					let url = "fluxoDeCaixaFiltra.php";
					const typeDate = buttonDay.classList.contains('active') ? 'D' : 'M';
					const dayInitial = (inputDateInitial.value).split('-')[2] ? (inputDateInitial.value).split('-')[2] : "";
					const dayEnd = (inputDateEnd.value).split('-')[2] ? (inputDateEnd.value).split('-')[2] : "";
					const quantityDays = dayEnd - dayInitial;
					const quantityPages = Math.ceil(quantityDays / 4);

					request = {
						quantityPages: quantityPages,
						typeDate: typeDate,
						quantityDays: quantityDays,
						dayInitial: dayInitial,
						dayEnd: dayEnd,
						inputDateInitial: `${inputDateInitial.value}-01`,
						inputDateEnd: `${inputDateEnd.value}-01`,
						cmbCentroDeCustos: cmbCentroDeCustosReq,
						cmbPlanoContas: cmbPlanoContasReq,
					};
					/* Adicionando os dados do filtro para o formulário que será enviado para o exportar */

					$("#quantityPages").val(quantityPages)
					$("#typeDate").val(typeDate)
					$("#quantityDays").val(quantityDays)
					$("#dayInitial").val(dayInitial)
					$("#dayEnd").val(dayEnd)
					$("#inputData").val()
					$("#inputDataFim").val()
					$("#inputCentroDeCustos").val(cmbCentroDeCustosReq)
					$("#inputPlanoContas").val(cmbPlanoContasReq)

					const form = $("#formFluxoDeCaixaExportar").children()
					
					$('#dataResponse').html(msg);

					try {
						$.post(
							url,
							request,
							(response) => {
								if (response) {
									$('#dataResponse').html('');
									$('#dataResponse').html(response);
								}
							}
						);
					} catch(err) {
						console.error('Houve um error: ',err);
					}
				} 

				getData();

			});

		});

		function exportarTabelaExcel() {
			document.getElementById('inputDateInitial').value = document.getElementById('inputDataInicio').value;
			document.getElementById('inputDateEnd').value = document.getElementById('inputDataFim').value;

            document.formFluxoDeCaixaExportar.action = "fluxoDeCaixaExportar.php";
			document.formFluxoDeCaixaExportar.submit();
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
				<div class="row">
					<div class="col-lg-12">
						<!-- Basic responsive configuration -->
						<div class="card">
							<div class="card-header header-elements-inline">
								<h3 class="card-title">Fluxo de Caixa</h3>
							</div>

							<div class="card-body">
								<form name="formFluxoOperacional" method="post">
									<div class="row">

										<div class="col-lg-2">
											<div class="form-group">
												<label for="submitDay">Exibição </label>
												<div class="input-group">
													<button id="submitDay" class="btn active" style="background:#607D8B; color:white;">Dia</button>
													<button id="submitMonth" class="btn">Mês</button>
												</div>
											</div>
                   						 </div>

										<div class="col-lg-2">
											<div class="form-group">
												<label for="inputDataInicio">Data Início <span class="text-danger">*</span></label>
												<div class="input-group">
													<span class="input-group-prepend">
														<span class="input-group-text"><i class="icon-calendar22"></i></span>
													</span>

													<?php $dataInicio = date("Y-m-d",strtotime("-3 days")); ?>
													<input 
														type="date" 
														id="inputDataInicio"
														name="inputDataInicio" 
														min="1800-01-01"
														max="2100-12-31" 
														class="form-control"
														placeholder="Data Início"
														value="<?php echo $dataInicio ?>" 
													>
													
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
													<?php $dataFim = date("Y-m-d"); ?>
													<input 
														type="date" 
														id="inputDataFim" 
														name="inputDataFim"  
														min="1800-01-01" 
														max="2100-12-31" 
														class="form-control" 
														placeholder="Data Fim"
														value="<?php echo $dataFim ?>" 
														
													>	
														

												</div>
											</div>
										</div>

										<div class="col-lg-2">
											<div class="form-group container-cmbCentroDeCustos" >
												<label for="cmbCentroDeCustos">Centro de Custos</label>
												<select id="cmbCentroDeCustos" name="cmbCentroDeCustos" class="form-control multiselect-select-all-filtering" multiple="multiple" data-fouc>
													<?php
														$sql = "SELECT CnCusId,
																	CnCusNome,
																	CnCusNomePersonalizado
																FROM CentroCusto
																JOIN Situacao 
																	ON SituaId = CnCusStatus
																WHERE CnCusUnidade = " . $_SESSION['UnidadeId'] . " 
																and SituaChave = 'ATIVO'
															ORDER BY CnCusNome ASC";
														$result = $conn->query($sql);
														$rowCentroDeCustos = $result->fetchAll(PDO::FETCH_ASSOC);

														foreach ($rowCentroDeCustos as $item) {
                                                            $cnCusDescricao = $item['CnCusNomePersonalizado'] === NULL ? $item['CnCusNome'] : $item['CnCusNomePersonalizado'];
															print('<option value="' . $item['CnCusId'] . '" class="centroDeCustosClass" selected>' . $cnCusDescricao . '</option>');
														}
													?>
												</select>
											</div>
										</div>

										<div class="col-lg-2">
											<div class="form-group container-cmbPlanoContas">
												<label for="cmbPlanoContas">Plano de Contas</label>
												<select id="cmbPlanoContas" name="cmbPlanoContas" class="form-control multiselect-select-all-filtering" multiple="multiple" data-fouc>
													<?php
														$sql = "SELECT PlConId, PlConNome
																FROM PlanoConta
																JOIN Situacao 
																ON SituaId = PlConStatus
																WHERE PlConUnidade = " . $_SESSION['UnidadeId'] . " 
																		AND SituaChave = 'ATIVO'
																ORDER BY PlConNome ASC";
														$result = $conn->query($sql);
														$rowPlanoContas = $result->fetchAll(PDO::FETCH_ASSOC);

														foreach ($rowPlanoContas as $item) {
															print('<option value="' . $item['PlConId'] . '" selected>' . $item['PlConNome'] . '</option>');
														}

														?>
												</select>
											</div>
										</div>

										<div class="text-left col-lg-1 pt-3">
											<button id="submitPesquisar" class="btn btn-principal" style='margin-left:1rem'><i class="fas fa-search"></i></button>
										</div>
										<div class="text-left col-lg-1 pt-3">
									        <a href="#" onclick="exportarTabelaExcel()" class="btn bg-slate-700 btn-icon" role="button" data-popup="tooltip" data-placement="bottom" data-container="body" title="Exportar Produtos"><i class="icon-drawer-out"></i></a>									
								        </div>
									</div>
								</form>
							</div>
							<!-- <div class="row mb-2">
								<div class="col-lg-11">		
								</div>
								<div class="col-lg-1">
									<a href="#" style="float:left; margin-left: 5px;" onclick="exportarTabelaExcel()" class="btn bg-slate-700 btn-icon" role="button" data-popup="tooltip" data-placement="bottom" data-container="body" title="Exportar Produtos"><i class="icon-drawer-out"></i></a>									
								</div>
							</div> -->

							<div id="dataResponse"></div>

							<form id="formFluxoDeCaixaExportar" name="formFluxoDeCaixaExportar" method="post">
					            <input type="hidden" id="quantityPages" name="quantityPages">
					            <input type="hidden" id="typeDate" name="typeDate">
					            <input type="hidden" id="quantityDays" name="quantityDays">
					            <input type="hidden" id="dayInitial" name="dayInitial">
					            <input type="hidden" id="dayEnd" name="dayEnd">
					            <input type="hidden" id="inputDateInitial" name="inputDateInitial">
					            <input type="hidden" id="inputDateEnd" name="inputDateEnd">
					            <input type="hidden" id="inputCentroDeCustos" name="inputCentroDeCustos">
					            <input type="hidden" id="inputPlanoContas" name="inputPlanoContas">
				            </form>

						<!-- FIM DO CARD -->	
						</div>
						<!-- /basic responsive configuration -->
					</div>
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

</body>

</html>
