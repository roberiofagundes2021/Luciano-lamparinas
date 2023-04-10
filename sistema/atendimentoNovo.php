<?php

include_once("sessao.php");

$_SESSION['PaginaAtual'] = 'Novo Atendimento';

include('global_assets/php/conexao.php');

$iUnidade = $_SESSION['UnidadeId'];
$iEmpresa = $_SESSION['EmpreId'];

$_SESSION['atendimento'] = [
	'paciente' => '',
	'responsavel' => '',
	'atendimentoServicos' => []
];

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Atendimentos</title>

	<?php include_once("head.php"); ?>

	<!-- wizzard (steppers) -->

	<link href="global_assets/css/icons/icomoon/styles.css" rel="stylesheet" type="text/css">
	<link href="global_assets/css/lamparinas/layout.min.css" rel="stylesheet" type="text/css">
	<link href="global_assets/css/lamparinas/components.min.css" rel="stylesheet" type="text/css">

	<script src="global_assets/js/main/bootstrap.bundle.min.js"></script>
	<script src="global_assets/js/plugins/loaders/blockui.min.js"></script>
	<script src="global_assets/js/plugins/ui/ripple.min.js"></script>

	<script src="global_assets/js/plugins/forms/wizards/steps.min.js"></script>
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
	<script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>
	<script src="global_assets/js/plugins/forms/inputs/inputmask.js"></script>
	<script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>

	<!-- essa função deve ser devlarada aqui pois existem funções que são sobrescrevidas
	nas importações abaixo -->
	<?php
		echo "<script>
				iUnidade = $iUnidade
				iEmpresa = $iEmpresa
			</script>"
	?>
	<script>
		const socket = WebSocketConnect(iUnidade,iEmpresa)
		// menssage = JSON.parse(event.data)
		// if(menssage.type == 'AGENDA'){
		// 	getAgenda()
		// }
		$(document).ready(function() {
			// Show form
			var form = $('.steps-validation').show();
			// Initialize wizard
			$('.steps-validation').steps({
				headerTag: 'h6',
				bodyTag: 'fieldset',
				titleTemplate: '<span class="number">#index#</span> #title#',
				labels: {
					previous: '<i class="icon-arrow-left13 mr-2" /> Anterior',
					next: 'Próximo <i class="icon-arrow-right14 ml-2" />',
					finish: 'Finalizar <i class="icon-arrow-right14 ml-2" />'
				},
				transitionEffect: 'fade',
				autoFocus: true,
				onStepChanging: function(event, currentIndex, newIndex) {
					let error = $('#paciente').val() ? true : false
					if(!validaDataNascimento($("#nascimento").val())){
						alerta('Alerta','A data de Nascimento não pode ser futura', 'error');
						return false;
					}
					// esse switch serve para mostrar ou ocultar os dados da tela
					// de acordo com a etapa do steppers
					switch (newIndex) {
						case 0:
							$('#dadosPaciente').removeClass('d-none');
							($('#paciente').val() ? $('#novoPaciente').removeClass('d-none') & $('#informacoes').show() : $('#novoPaciente').addClass('d-none') & $('#informacoes').hide());
							$('#dadosResponsavel').addClass('d-none');
							$('#novoResponsavel').addClass('d-none')
							$('#dadosAtendimento').addClass('d-none');
							break;
						case 1:
							$('#dadosResponsavel').removeClass('d-none');
							($('#parentescoCadatrado').val() ? $('#novoResponsavel').removeClass('d-none') & $('#informacoes').show() : $('#novoResponsavel').addClass('d-none') & $('#informacoes').hide());
							$('#novoPaciente').addClass('d-none');
							$('#dadosPaciente').addClass('d-none');
							$('#dadosAtendimento').addClass('d-none');
							break;
						case 2:
							$('#dadosAtendimento').removeClass('d-none');
							$('#dadosResponsavel').addClass('d-none');
							$('#novoResponsavel').addClass('d-none')
							$('#dadosPaciente').addClass('d-none');
							$('#novoPaciente').addClass('d-none');
							$('#informacoes').hide();
							break;
						default:
							$('#novoPaciente').addClass('d-none');
							$('#novoResponsavel').addClass('d-none');
							$('#informacoes').hide();
							break;
					}

					// Allways allow previous action even if the current form is not valid!
					if (currentIndex > newIndex) {
						return true;
					}
					// Needed in some cases if the user went back (clean up)
					if (currentIndex < newIndex) {
						// To remove error styles
						form.find('.body:eq(' + newIndex + ') label.error').remove();
						form.find('.body:eq(' + newIndex + ') .error').removeClass('error');
					}
					form.validate().settings.ignore = ':disabled,:hidden';
					return form.valid();
				},
				onFinishing: function(event, currentIndex) {
					form.validate().settings.ignore = ':disabled';
					return form.valid();
				},
				onFinished: function(event, currentIndex) {
					event.preventDefault();
					let menssageError = ''

					switch (menssageError) {
						case $('#modalidade').val():
							menssageError = 'informe a modalidade';
							$('#modalidade').focus();
							break;
						case $('#classificacao').val():
							menssageError = 'informe a classificação';
							$('#classificacao').focus();
							break;
						case $('#classificacaoRisco').val():
							menssageError = 'informe a classificação de risco';
							$('#classificacaoRisco').focus();
							break;
						case $('#servico').val():
							menssageError = 'informe o serviço';
							$('#servico').focus();
							break;
						case $('#medicos').val():
							menssageError = 'informe o profissional';
							$('#medicos').focus();
							break;
						case $('#localAtendimento').val():
							menssageError = 'informe o local de atendimento';
							$('#localAtendimento').focus();
							break;
						case $('#horaAtendimento').val():
							menssageError = 'informe um horário';
							$('#horaAtendimento').focus();
							break;
						default:
							menssageError = '';
							break;
					}
					if (menssageError) {
						alerta('Campo Obrigatório!', menssageError, 'error')
						return
					}
					let paciente = $('#paciente').val() ? {
						'id': $('#paciente').val(),
						'prontuario': $('#prontuario').val(),
						'nome': $('#nome').val(),
						'nomeSocial': $('#nomeSocial').val(),
						'cpf': $('#cpf').val().replace(/[^\d]+/g, ''),
						'cns': $('#cns').val(),
						'rg': $('#rg').val(),
						'emissor': $('#emissor').val(),
						'uf': $('#uf').val(),
						'sexo': $('#sexo').val(),
						'nascimento': $('#nascimento').val(),
						'nomePai': $('#nomePai').val(),
						'nomeMae': $('#nomeMae').val(),
						'racaCor': $('#racaCor').val(),
						'estadoCivil': $('#estadoCivil').val(),
						'naturalidade': $('#naturalidade').val(),
						'profissao': $('#profissao').val(),
						'cep': $('#cep').val(),
						'endereco': $('#endereco').val(),
						'numero': $('#numero').val(),
						'complemento': $('#complemento').val(),
						'bairro': $('#bairro').val(),
						'cidade': $('#cidade').val(),
						'estado': $('#estado').val(),
						'contato': $('#contato').val(),
						'telefone': $('#telefone').val(),
						'celular': $('#celular').val(),
						'email': $('#email').val(),
						'observacao': $('#observacao').val()
					} : null

					let responsavel = $('#parentescoCadatrado').val() ? {
						'id': $('#parentescoCadatrado').val(),
						'nomeResp': $('#nomeResp').val(),
						'parentescoResp': $('#parentescoResp').val(),
						'nascimentoResp': $('#nascimentoResp').val(),
						'cepResp': $('#cepResp').val(),
						'enderecoResp': $('#enderecoResp').val(),
						'numeroResp': $('#numeroResp').val(),
						'complementoResp': $('#complementoResp').val(),
						'bairroResp': $('#bairroResp').val(),
						'cidadeResp': $('#cidadeResp').val(),
						'estadoResp': $('#estadoResp').val(),
						'telefoneResp': $('#telefoneResp').val(),
						'celularResp': $('#celularResp').val(),
						'emailResp': $('#emailResp').val(),
						'observacaoResp': $('#observacaoResp').val()
					} : null
					$.ajax({
						type: 'POST',
						url: 'filtraAtendimento.php',
						dataType: 'json',
						data: {
							'tipoRequest': 'SALVARATENDIMENTO',
							'status': 'NOVO',
							'tipo': 'ATENDIMENTO',
							'cliente': paciente,
							'responsavel': responsavel,
							'dataRegistro': $('#dataAtendimento').val(),
							'classificacaoRisco': $('#classificacaoRisco').val(),
							'grupo': $('#grupo').val(),
							'subgrupo': $('#subgrupo').val(),
							'modalidade': $('#modalidade').val(),
							'classificacao': $('#classificacao').val(),
							'observacao': $('#observacaoAtendimento').val(),
							'servico':$('#servico').val(),
							'medico':$('#medicos').val(),
							'localAtendimento':$('#localAtendimento').val(),
							'horaAtendimento':$('#horaAtendimento').val()
						},
						success: function(response) {
							if (response.status == 'success') {
								alerta(response.titulo, response.menssagem, response.status)
								socket.sendMenssage({
									'type':'ATENDIMENTO'
								});
								window.location.href = 'atendimento.php'
							} else {
								alerta(response.titulo, response.menssagem, response.status);
							}
						},
						error: function(response) {
							alerta(response.titulo, response.menssagem, response.status);
						}
					});
				}
			});
			$('.steps-validation').validate({
				ignore: 'input[type=hidden], .select2-search__field', // ignore hidden fields
				errorClass: 'validation-invalid-label',
				highlight: function(element, errorClass) {
					$(element).removeClass(errorClass);
				},
				unhighlight: function(element, errorClass) {
					$(element).removeClass(errorClass);
				},

				// Different components require proper error label placement
				errorPlacement: function(error, element) {

					// Unstyled checkboxes, radios
					if (element.parents().hasClass('form-check')) {
						error.appendTo(element.parents('.form-check').parent());
					}

					// Input with icons and Select2
					else if (element.parents().hasClass('form-group-feedback') || element.hasClass('select2-hidden-accessible')) {
						error.appendTo(element.parent());
					}

					// Input group, styled file input
					else if (element.parent().is('.uniform-uploader, .uniform-select') || element.parents().hasClass('input-group')) {
						error.appendTo(element.parent().parent());
					}

					// Other elements
					else {
						error.insertAfter(element);
					}
				},
				rules: {
					email: {
						email: true
					}
				}
			});

			// vai impedir de deixar o usuário seguir em frente antes de selecionar um paciente
			$('.actions a').each(function(index, element) {
				if ($(element).attr('href') == '#next') {
					$(element).attr('href', '#')
					$(element).attr('id', 'nextBTN')
					$(element).on('click', function(e) {
						$('#dadosPaciente').submit()

						if($('#paciente').val()){

							let menssagem=''
							let noDisable=true
							
							if(!validaCPF($('#cpf').val().replace(/[^\d]+/g, ''))){
								noDisable=false
								menssagem='informe um CPF'
								$('#cpf').focus()
							}
							if(!noDisable){
								alerta('Campo obrigatório!!',menssagem,'error')
							}
						}
					})
				}
			})
		})
	</script>

	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
	<script src="global_assets/js/demo_pages/form_select2.js"></script>
	<script src="global_assets/js/demo_pages/form_layouts.js"></script>
	<script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>
	<script src="global_assets/js/plugins/tables/datatables/datatables.min.js"></script>
	<script src="global_assets/js/plugins/tables/datatables/extensions/responsive.min.js"></script>
	<script src="global_assets/js/demo_pages/datatables_responsive.js"></script>
	<script src="global_assets/js/demo_pages/datatables_sorting.js"></script>
	<script src="global_assets/js/plugins/editors/summernote/summernote.min.js"></script>
	<script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
	<script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>

	<script src="global_assets/js/plugins/ui/moment/moment.min.js"></script>
	<script src="global_assets/js/plugins/pickers/daterangepicker.js"></script>
	<script src="global_assets/js/plugins/pickers/anytime.min.js"></script>
	<script src="global_assets/js/plugins/pickers/pickadate/picker.js"></script>
	<script src="global_assets/js/plugins/pickers/pickadate/picker.date.js"></script>
	<script src="global_assets/js/plugins/pickers/pickadate/picker.time.js"></script>
	<script src="global_assets/js/plugins/pickers/pickadate/legacy.js"></script>
	<script src="global_assets/js/plugins/notifications/jgrowl.min.js"></script>

	<!-- Plugin para corrigir a ordenação por data. Caso a URL dê problema algum dia, salvei esses 2 arquivos na pasta global_assets/js/lamparinas -->
	<script type="text/javascript" language="javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.8.4/moment.min.js"></script>
	<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/plug-ins/1.10.10/sorting/datetime-moment.js"></script>

	<!-- Modal -->
	<script src="global_assets/js/plugins/notifications/bootbox.min.js"></script>

	<!-- Validação -->
	<script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
	<script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
	<script src="global_assets/js/demo_pages/form_validation.js"></script>

	<script type="text/javascript">
		function validaDataNascimento(dataASerValidada){			
			//Adicionado um espaço para forçar o fuso horário de brasília		
			let dataObj = new Date(dataASerValidada+" ");
			let hoje = new Date();
			if((hoje-dataObj)<0){
				return false;				
			}
			else{
				return true;
			}
		}
	</script>

	<script type="text/javascript">
		$(document).ready(function() {
			$('#informacoes').hide()
			$('.actions').addClass('col-lg-12 row pt-2')
			$('.actions ul').addClass('col-lg-12 actionContent')
			//$('.actions').append(`<a class='col-lg-12 btn btn-lg' href='atendimento.php' id='cancelar'>cancelar</a>`)
			//$('#cancelar').insertAfter('.actionContent')

			let dataAtual = new Date().toLocaleString("pt-BR", {timeZone: "America/Bahia"})
			dataAtual = dataAtual.split(',')[0]
			// dataAtual = dataAtual.split('/')[2] + '-' + dataAtual.split('/')[1] + '-' + dataAtual.split('/')[0]
			
			$('#dataAtendimento').val(dataAtual)

			getCmbs()

			$('#incluirServico').on('click', function(e) {
				e.preventDefault();
				let menssageError = ''
				let grupo = $('#grupo').val()
				let subGrupo = $('#subgrupo').val()
				let servico = $('#servico').val()
				let medicos = $('#medicos').val()
				let dataAtendimento = $('#dataAtendimento').val()
				let horaAtendimento = $('#horaAtendimento').val()
				let localAtendimento = $('#localAtendimento').val()

				switch (menssageError) {
					case servico:
						menssageError = 'informe o serviço';
						$('#servico').focus();
						break;
					case medicos:
						menssageError = 'informe o médico';
						$('#medicos').focus();
						break;
					case $('#dataAtendimento').val():
						menssageError = 'Sem data disponível para o serviço!!';
						break;
					case horaAtendimento:
						menssageError = 'informe o horário';
						$('#horaAtendimento').focus();
						break;
					case localAtendimento:
						menssageError = 'informe o local de atendimento';
						$('#localAtendimento').focus();
						break;
					default:
						menssageError = '';
						break;
				}

				if (menssageError) {
					alerta('Campo Obrigatório!', menssageError, 'error')
					return
				}

				$.ajax({
					type: 'POST',
					url: 'filtraAtendimento.php',
					dataType: 'json',
					data: {
						'tipoRequest': 'ADICIONARSERVICO',
						'servico': servico,
						'grupo':grupo,
						'subGrupo':subGrupo,
						'medicos': medicos,
						'dataAtendimento': dataAtendimento,
						'horaAtendimento': horaAtendimento,
						'localAtendimento': localAtendimento
					},
					success: function(response) {
						if (response.status == 'success') {
							resetServicoCmb()
							alerta(response.titulo, response.menssagem, response.status)
						} else {
							alerta(response.titulo, response.menssagem, response.status);
						}
					},
					error: function(response) {
						alerta(response.titulo, response.menssagem, response.status);
					}
				});
			})

			// btn de adicionar novo responsavel
			$('#addResponsavel').on('click', function(e) {
				e.preventDefault()
				$('#page-modal-responsavel').fadeIn(200)
			})

			$('#salvarPacienteModal').on('click', function(e) {
				e.preventDefault()

				let menssageError = ''
				switch (menssageError) {
					case $('#nomeNew').val():
						menssageError = 'Informe o nome!!';
						$('#nomeNew').focus();
						break;
					case $('#cpfNew').val():
						menssageError = 'Informe o CPF!!';
						$('#cpfNew').focus();
						break;
					default:
						menssageError = '';
						break;
				}

				if (menssageError) {
					alerta('Campo Obrigatório!', menssageError, 'error')
					return
				}

				var cpfSoNumeros = $('#cpfNew').val().replace(/[^\d]+/g, '');
				if(!validaCPF(cpfSoNumeros)){
					alerta('CPF Inválido!', 'Digite um CPF válido!!', 'error')
					return
				}

				let dataPreenchida = $("#nascimentoNew").val();
				if(!validaDataNascimento(dataPreenchida)){
					$('#nascimentoNew').val('');
					alerta('Atenção', 'Data de nascimento não pode ser futura!', 'error');
					$('#nascimentoNew').focus();
					return
				}

				$.ajax({
					type: 'POST',
					url: 'filtraAtendimento.php',
					dataType: 'json',
					data: {
						'tipoRequest': 'SALVARPACIENTE',
						'prontuario': $('#prontuarioNew').val(),
						'nome': $('#nomeNew').val(),
						'nomeSocial': $('#nomeSocialNew').val(),
						'cpf': cpfSoNumeros,
						'cns': $('#cnsNew').val(),
						'rg': $('#rgNew').val(),
						'emissor': $('#emissorNew').val(),
						'uf': $('#ufNew').val(),
						'sexo': $('#sexoNew').val(),
						'nascimento': $('#nascimentoNew').val(),
						'nomePai': $('#nomePaiNew').val(),
						'nomeMae': $('#nomeMaeNew').val(),
						'racaCor': $('#racaCorNew').val(),
						'naturalidade': $('#naturalidadeNew').val(),
						'profissao': $('#profissaoNew').val(),
						'estadoCivil': $('#estadoCivilNew').val(),
						'cep': $('#cepNew').val(),
						'endereco': $('#enderecoNew').val(),
						'numero': $('#numeroNew').val(),
						'complemento': $('#complementoNew').val(),
						'bairro': $('#bairroNew').val(),
						'cidade': $('#cidadeNew').val(),
						'estado': $('#estadoNew').val(),
						'contato': $('#contatoNew').val(),
						'telefone': $('#telefoneNew').val(),
						'celular': $('#celularNew').val(),
						'email': $('#emailNew').val(),
						'observacao': $('#observacaoNew').val()
					},
					success: async function(response) {
						if (response.status == 'success') {
							alerta(response.titulo, response.menssagem, response.status)
							await getCmbs({
								'pacienteID': response.id
							})
							setPacienteAtribut(response.id)
							$('#page-modal-paciente').fadeOut(200)
						} else {
							alerta(response.titulo, response.menssagem, response.status)
						}
					}
				});
			})

			$('#salvarResponsavelModal').on('click', function(e) {
				e.preventDefault()

				let menssageError = ''

				switch (menssageError) {
					case $('#nomeRespNew').val():
						menssageError = 'Informe o nome!!';
						$('#nomeResp').focus();
						break;
					default:
						menssageError = '';
						break;
				}

				if (menssageError) {
					alerta('Campo Obrigatório!', menssageError, 'error')
					return
				}

				$.ajax({
					type: 'POST',
					url: 'filtraAtendimento.php',
					dataType: 'json',
					data: {
						'tipoRequest': 'SALVARRESPONSAVEL',
						'pacienteId': $('#paciente').val(),
						'nomeResp': $('#nomeRespNew').val(),
						'parentescoResp': $('#parentescoRespNew').val(),
						'nascimentoResp': $('#nascimentoRespNew').val(),
						'cepResp': $('#cepRespNew').val(),
						'enderecoResp': $('#enderecoRespNew').val(),
						'numeroResp': $('#numeroRespNew').val(),
						'complementoResp': $('#observacaoRespNew').val(),
						'bairroResp': $('#bairroRespNew').val(),
						'cidadeResp': $('#cidadeRespNew').val(),
						'estadoResp': $('#estadoRespNew').val(),
						'telefoneResp': $('#telefoneRespNew').val(),
						'celularResp': $('#celularRespNew').val(),
						'emailResp': $('#emailRespNew').val(),
						'observacaoResp': $('#complementoRespNew').val()
					},
					success: async function(response) {
						if (response.status == 'success') {
							alerta(response.titulo, response.menssagem, response.status)
							await getCmbs({
								'responsavelID': response.responsavel
							})
							setResponsavelAtribut(response.responsavel)
							$('#page-modal-responsavel').fadeOut(200)
						} else {
							alerta(response.titulo, response.menssagem, response.status)
						}
					}
				});
			})

			$('#addPaciente').on('click', function(e) {
				e.preventDefault();
				$('#page-modal-paciente').fadeIn(200)
			})

			$('#modalPaciente-close-x').on('click', () => {
				$('#iAtendimento').val('')
				$('#page-modal-paciente').fadeOut(200)
			})
			$('#modalResponsavel-close-x').on('click', () => {
				$('#iAtendimento').val('')
				$('#page-modal-responsavel').fadeOut(200)
			})
			$('#modalDesconto-close-x').on('click', () => {
				$('#itemDescontoId').val('')
				$('#itemDescontoValue').val('')
				$('#inputDesconto').val('')
				$('#pageModalDescontos').fadeOut(200)
			})

			$('#cpf').blur(function(element){
				let cpfSoNumeros = $(this).val().replace(/[^\d]+/g, '')
				if(!validaCPF(cpfSoNumeros)){
					$(this).val('')
					alerta('CPF Inválido!', 'Digite um CPF válido!!', 'error')
					$('#nextBTN').attr('href','#')

					$('.steps ul li').each(function(index, element) {
						$(element).attr('class', 'disabled')
					})
					return
				}
				$('#nextBTN').attr('href','#next')
			})			
			
			//Esta função será executada quando o campo cep do edita paciente perder o foco.
			$("#cep").blur(function() {
				ValidaEPreencheCEP(
					"cep",
					"endereco",
					"bairro",
					"cidade",
					"estado"
			    )
			}); 

			//Esta função será executada quando o campo cep do popup perder o foco.
			$("#cepNew").blur(function() {
				ValidaEPreencheCEP(
					"cepNew",
					"enderecoNew",
					"bairroNew",
					"cidadeNew",
					"estadoNew"
			    )
			});

			//Esta função será executada quando o campo cep do responsável edita perder o foco.
			$("#cepResp").blur(function() {
				ValidaEPreencheCEP(
					"cepResp",
					"enderecoResp",
					"bairroResp",
					"cidadeResp",
					"estadoResp"
			    )
			}); 

			//Esta função será executada quando o campo cep do responsável novo (popup) perder o foco.
			$("#cepRespNew").blur(function() {
				ValidaEPreencheCEP(
					"cepRespNew",
					"enderecoRespNew",
					"bairroRespNew",
					"cidadeRespNew",
					"estadoRespNew"
			    )
			}); 

			$('#formServicoAtendimento').submit(function(e) {
				e.preventDefault()
			})
			$('#dados').submit(function(e) {
				e.preventDefault()
			})

			$('#inputDesconto').on('input', function(item){
				let valor = $('#itemDescontoValue').val()
				let desconto = $(this).val()
				let valorF = 0

				valorF = valor - desconto

				$('#inputModalValorF').val('R$ '+float2moeda(valorF))

				$('#pageModalDescontos').fadeIn(200);
			})
			$('#setDesconto').on('click', function(item){
				$.ajax({
					type: 'POST',
					url: 'filtraAtendimento.php',
					dataType: 'json',
					data: {
						'tipoRequest': 'SETDESCONTO',
						'iServico':$('#itemDescontoId').val(),
						'desconto':$('#inputDesconto').val(),
					},
					success: function(response) {
						$('#pageModalDescontos').fadeOut(200)
						alerta(response.titulo,response.menssagem,response.status)
					}
				});
			})
			$('#paciente').on('change', function() {
				let iPaciente = $(this).val()
				setPacienteAtribut(iPaciente)
			})
			$('#servico').on('change', function(e) {
				$('#medicos').empty();
				$('#medicos').append(`<option selected value=''>Carregando...</option>`)
				$.ajax({
					type: 'POST',
					url: 'filtraAtendimento.php',
					dataType: 'json',
					data: {
						'tipoRequest': 'MEDICOS',
						'servico': $(this).val()
					},
					success: function(response) {
						$('#medicos').empty();
						$('#medicos').append(`<option value=''>Selecione</option>`)		

						response.forEach(item => {
							let opt = `<option value="${item.id}">${item.nome}</option>`
							$('#medicos').append(opt)
						})
						$('#medicos').focus()
					}
				});
			})
			$('#parentescoCadatrado').on('change', function() {
				let iResponsavel = $(this).val();
				setResponsavelAtribut(iResponsavel)
			})
			$('#grupo').on('change',function(e){
				$('#subgrupo').empty()
				$('#servico').empty()
				$('#subgrupo').append(`<option selected value=''>Carregando...</option>`)
				$('#servico').append(`<option selected value=''>Carregando...</option>`)
				// vai preencher cmbSubGrupo
				$.ajax({
					type: 'POST',
					url: 'filtraAtendimento.php',
					dataType: 'json',
					data: {
						'tipoRequest': 'SUBGRUPO',
						'grupo': $(this).val()
					},
					success: async function(response) {
						$('#subgrupo').empty();
						$('#subgrupo').append(`<option value=''>Selecione</option>`)

						await response.forEach(item => {
							$('#subgrupo').append(`<option value="${item.id}">${item.nome}</option>`)
						})

						// vai preencher cmbServicos
						$.ajax({
							type: 'POST',
							url: 'filtraAtendimento.php',
							dataType: 'json',
							data: {
								'tipoRequest': 'SERVICOS',
								'grupo':$('#grupo').val()
							},
							success: function(response) {
								$('#servico').empty();
								$('#servico').append(`<option value=''>Selecione</option>`)
								response.forEach(item => {
									let opt = `<option value="${item.id}">${item.codigo} - ${item.nome}</option>`
									$('#servico').append(opt)
								})
							}
						});
					}
				});
			})
			$('#subgrupo').on('change',function(e){
				$('#servico').empty()
				$('#servico').append(`<option selected value=''>Carregando...</option>`)
				// vai preencher cmbServicos
				$.ajax({
					type: 'POST',
					url: 'filtraAtendimento.php',
					dataType: 'json',
					data: {
						'tipoRequest': 'SERVICOS',
						'grupo':$('#grupo').val(),
						'subGrupo':$(this).val()
					},
					success: function(response) {
						$('#servico').empty();
						$('#servico').append(`<option value=''>Selecione</option>`)
						response.forEach(item => {
							let opt = `<option value="${item.id}">${item.codigo} - ${item.nome}</option>`
							$('#servico').append(opt)
						})
					}
				});
			})
			$('#medicos').on('change',function(e){
				// vai preencher cmbServicos
				$.ajax({
					type: 'POST',
					url: 'filtraAtendimento.php',
					dataType: 'json',
					data: {
						'tipoRequest': 'MEDICOS',
						'id':$(this).val()
					},
					success: function(response) {
						setHoraProfissional(response)
					}
				});
			})

			resetServicoCmb()
		});

		function getCmbs(obj) {
			$('#grupo').empty()
			$('#subgrupo').empty()
			$('#classificacaoRisco').empty()
			$('#paciente').empty()
			$('#modalidade').empty()
			$('#servico').empty()
			$('#classificacao').empty()
			$('#localAtendimento').empty()

			$('#grupo').append(`<option selected value=''>Carregando...</option>`)
			$('#subgrupo').append(`<option selected value=''>Carregando...</option>`)
			$('#classificacaoRisco').append(`<option selected value=''>Carregando...</option>`)
			$('#paciente').append(`<option selected value=''>Carregando...</option>`)
			$('#modalidade').append(`<option selected value=''>Carregando...</option>`)
			$('#servico').append(`<option selected value=''>Carregando...</option>`)
			$('#parentescoCadatrado').html("<option selected value=''>selecione</option>")
			$('#classificacao').append(`<option selected value=''>Carregando...</option>`)
			$('#localAtendimento').append(`<option selected value=''>Carregando...</option>`)
			// vai preencher cmbGrupo
			$.ajax({
				type: 'POST',
				url: 'filtraAtendimento.php',
				dataType: 'json',
				data: {
					'tipoRequest': 'GRUPO'
				},
				success: function(response) {
					$('#grupo').empty();
					$('#grupo').append(`<option value=''>Selecione</option>`)

					response.forEach(item => {
						$('#grupo').append(`<option value="${item.id}">${item.nome}</option>`)
					})
				}
			});
			// vai preencher cmbSubGrupo
			$.ajax({
				type: 'POST',
				url: 'filtraAtendimento.php',
				dataType: 'json',
				data: {
					'tipoRequest': 'SUBGRUPO'
				},
				success: function(response) {
					$('#subgrupo').empty();
					$('#subgrupo').append(`<option value=''>Selecione</option>`)

					response.forEach(item => {
						$('#subgrupo').append(`<option value="${item.id}">${item.nome}</option>`)
					})
				}
			});
			// vai preencher cmbClassificacaoRisco
			$.ajax({
				type: 'POST',
				url: 'filtraAtendimento.php',
				dataType: 'json',
				data: {
					'tipoRequest': 'CLASSIFICACAORISCOS'
				},
				success: function(response) {
					$('#classificacaoRisco').empty();
					$('#classificacaoRisco').append(`<option value=''>Selecione</option>`)

					response.forEach(item => {
						$('#classificacaoRisco').append(`<option title="${item.determinante}" value="${item.id}">${item.nome}</option>`)
					})
				}
			});
			// vai preencher cmbPaciente
			$.ajax({
				type: 'POST',
				url: 'filtraAtendimento.php',
				dataType: 'json',
				data: {
					'tipoRequest': 'PACIENTES'
				},
				success: function(response) {
					$('#paciente').empty();
					$('#paciente').append(`<option value=''>Selecione</option>`)
					let opt = ''
					response.forEach(item => {
						let id = obj && obj.pacienteID? obj.pacienteID:null
						opt = id == item.id?`<option selected value="${item.id}">${item.nome}</option>`:`<option value="${item.id}">${item.nome}</option>`
						$('#paciente').append(opt)
					})
				}
			});
			// vai preencher cmbModalidade
			$.ajax({
				type: 'POST',
				url: 'filtraAtendimento.php',
				dataType: 'json',
				data: {
					'tipoRequest': 'MODALIDADES'
				},
				success: function(response) {
					$('#modalidade').empty();
					$('#modalidade').append(`<option value=''>Selecione</option>`)
					response.forEach(item => {
						let opt = ''
						opt = `<option value="${item.id}">${item.nome}</option>`
						$('#modalidade').append(opt)
					})
				}
			});
			// vai preencher cmbServicos
			$.ajax({
				type: 'POST',
				url: 'filtraAtendimento.php',
				dataType: 'json',
				data: {
					'tipoRequest': 'SERVICOS'
				},
				success: function(response) {
					$('#servico').empty();
					$('#servico').append(`<option value=''>Selecione</option>`)
					response.forEach(item => {
						let opt = `<option value="${item.id}">${item.codigo} - ${item.nome}</option>`
						$('#servico').append(opt)
					})
				}
			});

			// incluir responsáveis cadastrados
			$.ajax({
				type: 'POST',
				url: 'filtraAtendimento.php',
				dataType: 'json',
				data: {
					'tipoRequest': 'RESPONSAVEIS'
				},
				success: function(response) {
					let opt = ''
					$('#parentescoCadatrado').html("<option selected value=''>selecione</option>")
					response.data.forEach(function(item, index) {
						let id = obj && obj.responsavelID? obj.responsavelID:null
						opt = id == item.id?`<option selected value='${item.id}'>${item.nome}</option>`:`<option value='${item.id}'>${item.nome}</option>`
						$('#parentescoCadatrado').append(opt);
					});
					
				},
				error: function(response) {}
			});
			// vai preencher cmbClassificacao
			$.ajax({
				type: 'POST',
				url: 'filtraAtendimento.php',
				dataType: 'json',
				data: {
					'tipoRequest': 'CLASSIFICACAO'
				},
				success: function(response) {
					$('#classificacao').empty();
					$('#classificacao').append(`<option value=''>Selecione</option>`)

					response.forEach(item => {
						let opt = ''
						opt = `<option value="${item.id}">${item.nome}</option>`
						$('#classificacao').append(opt)
					})
				}
			});

			//  vai preencher cmbLocalAtendimento
			$.ajax({
				type: 'POST',
				url: 'filtraAtendimento.php',
				dataType: 'json',
				data: {
					'tipoRequest' : 'LOCALATENDIMENTO'
				},
				success: function(response) {
					$('#localAtendimento').empty();
					$('#localAtendimento').append(`<option value=''>Selecione</option>`);			
					response.forEach(item => {
						let opt = `<option value="${item.id}">${item.nome}</option>`
						$('#localAtendimento').append(opt)
					})
				}
			})
		}

		// essa função vai setar os atributos nos campos quando for selecionado o paciente
		function setPacienteAtribut(id) {
			iPaciente = id?id:$('#paciente').val()
			if (iPaciente) {
				$.ajax({ 
					type: 'POST',
					url: 'filtraAtendimento.php',
					dataType: 'json',
					data: {
						'tipoRequest': 'PACIENTE',
						'iPaciente': iPaciente
					},
					success: function(response) {
						if (response.status == 'success') {
							$('#prontuario').val(response.prontuario)
							$('#nome').val(response.nome)
							$('#nomeSocial').val(response.nomeSocial)
							$('#cpf').val(response.cpf)
							$('#cns').val(response.cns)
							$('#rg').val(response.rg)
							$('#emissor').val(response.emissor)
							$('#nascimento').val(response.nascimento)
							$('#nomePai').val(response.nomePai)
							$('#nomeMae').val(response.nomeMae)
							$('#racaCor').val(response.racaCor)
							$('#estadoCivil').val(response.estadoCivil)
							$('#naturalidade').val(response.naturalidade)
							$('#profissao').val(response.profissao)
							$('#cep').val(response.cep)
							$('#endereco').val(response.endereco)
							$('#numero').val(response.numero)
							$('#complemento').val(response.complemento)
							$('#bairro').val(response.bairro)
							$('#cidade').val(response.cidade)
							$('#contato').val(response.contato)
							$('#telefone').val(response.telefone)
							$('#celular').val(response.celular)
							$('#email').val(response.email)
							$('#observacao').val(response.observacao)

							$('#uf').val(response.uf)
							$('#estado').val(response.estado)
							$('#sexo').val(response.sexo)
							$('#estadoCivil').val(response.estadoCivil)

							let noDisable=true
							if(!validaCPF(response.cpf)){
								noDisable=false
							}

							$('#nextBTN').attr('href', (noDisable? '#next':'#'))

							$('.steps ul li').each(function(index, element) {
								if (!noDisable) {
									$(element).attr('class', 'disabled')
								}
							})

							$('#uf').children("option").each(function(index, item){
								if($(item).val() == response.uf){
									$(item).change()
								}
							})
							$('#estado').children("option").each(function(index, item){
								if($(item).val() == response.estado){
									$(item).change()
								}
							})
							$('#sexo').children("option").each(function(index, item){
								if($(item).val() == response.sexo){
									$(item).change()
								}
							})
							$('#estadoCivil').children("option").each(function(index, item){
								if($(item).val() == response.estadoCivil){
									$(item).change()
								}
							})
							$('#racaCor').children("option").each(function(index, item){
								if($(item).val() == response.racaCor){
									$(item).change()
								}
							})
							$('#novoPaciente').removeClass('d-none')
							$('#informacoes').show()
						} else {
							alerta(response.titulo, response.menssagem, response.status)
							$('#novoPaciente').addClass('d-none')
							$('#informacoes').hide()
						}
					},
					error: function(response) {}
				});
			} else {
				$('#prontuario').val('')
				$('#nome').val('')
				$('#nomeSocial').val('')
				$('#cpf').val('')
				$('#cns').val('')
				$('#rg').val('')
				$('#emissor').val('')
				$('#uf').val('')
				$('#sexo').val('')
				$('#nascimento').val('')
				$('#nomePai').val('')
				$('#nomeMae').val('')
				$('#racaCor').val('')
				$('#estadoCivil').val('')
				$('#naturalidade').val('')
				$('#profissao').val('')
				$('#cep').val('')
				$('#endereco').val('')
				$('#numero').val('')
				$('#complemento').val('')
				$('#bairro').val('')
				$('#cidade').val('')
				$('#estado').val('')
				$('#contato').val('')
				$('#telefone').val('')
				$('#celular').val('')
				$('#email').val('')
				$('#observacao').val('')

				$('#novoPaciente').addClass('d-none')
				$('#informacoes').hide()
				
				$('.steps ul li').each(function(index, element) {
					$(element).attr('class', 'disabled')
				})
			}

			$('.actions a').each(function(index, element) {
				if ($(element).attr('href') == '#next' && !iPaciente) {
					$(element).attr('href', '#')
					$(element).on('click', function(e){
						$('#dadosPaciente').submit()
					})
				}
			})
		}

		// essa função vai setar os atributos nos campos quando for selecionado o responsável
		function setResponsavelAtribut(id) {
			iResponsavel = id?id:$('#parentescoCadatrado').val()
			if (iResponsavel) {
				$.ajax({
					type: 'POST',
					url: 'filtraAtendimento.php',
					dataType: 'json',
					data: {
						'tipoRequest': 'RESPONSAVEL',
						'iResponsavel': iResponsavel
					},
					success: function(response) {
						if (response.status == 'success') {
							$('#nomeResp').val(response.data.nomeResp)
							$('#parentescoResp').val(response.data.parentescoResp)
							$('#nascimentoResp').val(response.data.nascimentoResp)
							$('#cepResp').val(response.data.cepResp)
							$('#enderecoResp').val(response.data.enderecoResp)
							$('#numeroResp').val(response.data.numeroResp)
							$('#complementoResp').val(response.data.complementoResp)
							$('#bairroResp').val(response.data.bairroResp)
							$('#cidadeResp').val(response.data.cidadeResp)
							$('#estadoResp').val(response.data.estadoResp)
							$('#telefoneResp').val(response.data.telefoneResp)
							$('#celularResp').val(response.data.celularResp)
							$('#emailResp').val(response.data.emailResp)
							$('#observacaoResp').val(response.data.observacaoResp)

							$('#estadoResp').children("option").each(function(index, item){
								if($(item).val() == response.data.estadoResp){
									$(item).change()
								}
							})

							$('#novoResponsavel').removeClass('d-none')
							$('#informacoes').show()
						} else {
							alerta(response.titulo, response.menssagem, response.status)
							$('#novoResponsavel').addClass('d-none')
							$('#informacoes').hide()
						}
					},
					error: function(response) {}
				});
			} else {
				$('#nomeResp').val('')
				$('#parentescoResp').val('')
				$('#nascimentoResp').val('')
				$('#cepResp').val('')
				$('#enderecoResp').val('')
				$('#numeroResp').val('')
				$('#complementoResp').val('')
				$('#bairroResp').val('')
				$('#cidadeResp').val('')
				$('#estadoResp').val('')
				$('#telefoneResp').val('')
				$('#celularResp').val('')
				$('#emailResp').val('')
				$('#observacaoResp').val('')
				
				$('#informacoes').hide()
				$('#novoResponsavel').addClass('d-none')
			}
		}

		function excluiServico(id) {
			$.ajax({
				type: 'POST',
				url: 'filtraAtendimento.php',
				dataType: 'json',
				data: {
					'tipoRequest': 'EXCLUISERVICO',
					'id': id
				},
				success: function(response) {
					alerta(response.titulo, response.menssagem, response.status)
				}
			});
		}

		function checkServicos() {
			$.ajax({
				type: 'POST',
				url: 'filtraAtendimento.php',
				dataType: 'json',
				data: {
					'tipoRequest': 'CHECKSERVICO',
					'tipo': 'ATENDIMENTO'
				},
				success: async function(response) {
					statusServicos = response.array.length ? true : false;
					if (statusServicos) {
						$('#dataServico').html('');

						let HTML = ''
						await response.array.forEach(item => {
							if(item.status != 'rem'){
								let popup = `<i style='color:${(item.desconto && item.desconto>0?'#50b900':'#000')}; cursor:pointer'
								data-id="${item.id}" data-desconto="${item.desconto}" data-valor="${item.valor}"
								data-titulo="${item.servico}"
								class='icon-cash descontoModal' title='Descontos'></i>`

								let exc = `<a style='color: black; cursor:pointer' onclick='excluiServico(\"${item.id}\")' class='list-icons-item'><i class='icon-bin' title='Excluir Atendimento'></i></a>`;
								let acoes = `<div class='list-icons'>
											${popup}
											${exc}
										</div>`;
								HTML += `
								<tr class='servicoItem'>
									<td class="text-left">${item.servico}</td>
									<td class="text-left">${item.medico}</td>
									<td class="text-left">${item.sData}</td>
									<td class="text-left">${item.hora}</td>
									<td class="text-left">${item.local}</td>
									<td class="text-right">R$ ${float2moeda(item.valor)}</td>
									<td class="text-center">${acoes}</td>
								</tr>`
							}
						})
						$('#servicoValorTotal').html(`R$ ${float2moeda(response.valorTotal)}`).show();
						$('#servicoValorDescontoTotal').html(`R$ ${float2moeda(response.valorTotalDesconto)}`).show();
						$('#dataServico').html(HTML).show();
						$('#servicoTable').removeClass('d-none');

						$('.descontoModal').each(function(index, element){
							$(element).on('click', function(item){
								let id = $(this).data('id')
								let valor = $(this).data('valor')
								let desconto = $(this).data('desconto')
								let valorF = 0

								$('#inputDesconto').val(desconto)
								$('#itemDescontoId').val(id)
								$('#itemDescontoValue').val(valor)

								$('#inputModalValorB').val('R$ '+float2moeda(valor))

								valorF = valor - desconto

								$('#inputModalValorF').val('R$ '+float2moeda(valorF))

								$('#pageModalDescontos').fadeIn(200);
							})
						})
					} else {
						$('#servicoTable').addClass('d-none');
					}
				}
			});
		}

		function resetServicoCmb() {
			// vai preencher cmbServicos
			$.ajax({
				type: 'POST',
				url: 'filtraAtendimento.php',
				dataType: 'json',
				data: {
					'tipoRequest': 'SERVICOS'
				},
				success: function(response) {
					$('#servico').empty();
					$('#servico').append(`<option value=''>Selecione</option>`)
					response.forEach(item => {
						let opt = `<option value="${item.id}">${item.codigo} - ${item.nome}</option>`
						$('#servico').append(opt)
					})
				}
			});
			// vai preencher cmbMedicos
			$.ajax({
				type: 'POST',
				url: 'filtraAtendimento.php',
				dataType: 'json',
				data: {
					'tipoRequest': 'MEDICOS'
				},
				success: function(response) {
					$('#medicos').empty();
					$('#medicos').append(`<option value=''>Selecione</option>`)
					response.forEach(item => {
						let opt = `<option value="${item.id}">${item.nome}</option>`
						$('#medicos').append(opt)
					})
				}
			});
			// vai preencher cmbLocalAtendimento
			$.ajax({
				type: 'POST',
				url: 'filtraAtendimento.php',
				dataType: 'json',
				data: {
					'tipoRequest': 'LOCALATENDIMENTO'
				},
				success: function(response) {
					$('#localAtendimento').empty();
					$('#localAtendimento').append(`<option value=''>Selecione</option>`)
					response.forEach(item => {
						let opt = `<option value="${item.id}">${item.nome}</option>`
						$('#localAtendimento').append(opt)
					})
				}
			});
			$('#horaAtendimento').val('')
		}

		function setHoraProfissional(range) {
			$('#modalHora').html('');
			$('#modalHora').html('Carregando...')
			$.ajax({
				type: 'POST',
				url: 'filtraAtendimento.php',
				dataType: 'json',
				data: {
					'tipoRequest': 'GETFUNCIONAMENTOUNIDADE'
				},
				success: function(response) {
					$('#modalHora').html('');
					$('#modalHora').html('<input id="horaAtendimento" name="horaAtendimento" type="text" class="form-control pickatime-disabled">');
					let hInicio = response? response.inicio : undefined
					let hFim = response ? response.fim : undefined
					let intervalo = 30
					let block = range.length?range[0].bloqueio:null

					if(block && !block.from){
						block.from = hInicio
					}
					if(block && !block.to){
						block.to = hFim
					}

					// doc: https://amsul.ca/pickadate.js/time/
					$('#horaAtendimento').pickatime({
						// Regras
						interval: intervalo,
						disable: block? block : undefined,
						// disable: [{from:[9,30], to:[18,00]}],

						// Formats
						format: 'HH:i',
						formatLabel: undefined,
						formatSubmit: undefined,
						hiddenPrefix: undefined,
						hiddenSuffix: '_submit',

						// Time limits
						min: hInicio,
						max: hFim,

						// Close on a user action
						closeOnSelect: true,
						closeOnClear: true,

						// eventos
						onSet: function(context) {
							
						},
						onStart: undefined,
						onRender: undefined,
						onOpen: undefined,
						onClose: undefined,
						onStop: undefined,
					});
				}
			});
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
				<div class="row">
					<div class="col-lg-3">
						<div class="card">
							<div class="card-header header-elements-inline" style="margin-left:10px;">
								<h5 class="text-uppercase font-weight-bold">Cadastro de Atendimento</h5>
							</div>

							<form class="wizard-form steps-validation" action="#" data-fouc>
								<div class='dropdown-divider'></div>
								<h6>Paciente</h6>
								<fieldset>
									<input id="pacienteId" type="hidden" name="pacienteId" value="">
								</fieldset>

								<h6>Responsável</h6>
								<fieldset>
									<input id="responsavelId" type="hidden" name="responsavelId" value="">
								</fieldset>

								<h6>Atendimento</h6>
								<fieldset>
								</fieldset>
							</form>
						</div>

						<div class="card">
							<a class='col-lg-12 btn btn-lg' href='atendimento.php' id='cancelar'>cancelar</a>
						</div>
					</div>
					<div class="col-lg-9">
						<!-- 
							esse card a seguir vai apresentar o conteudo para que o usuário possa selecionar,
							está fora do  "<fieldset>" para que o componente possa renderizar os botões
							na parte superior da página sem nenhum conteúdo entre os botões e a linha de ação (steppers),
							todo o efeito de fadeIn e fadeOut dos componentes e páginas são feitos em JavaScript de 
							acordo com a seleção do usuário
						-->
						<div class="card" style="min-height: 270px;">
							<div id="dados">
								<form id="dadosPaciente" class="form-validate-jquery" action="#" data-fouc>
									<div class="card-header header-elements-inline" style="margin-left:10px;">
										<h5 class="text-uppercase font-weight-bold">Dados do Paciente</h5>
									</div>
									<div class="col-12 row text-center justify-content-center mb-5" id="selectPaciente">
										<div class="col-lg-12 my-3 text-black-50">
											<h5>Selecione o paciente</h5>
										</div>
										<div class="col-5">
											<select id="paciente" name="paciente" class="select-search" required>

											</select>
										</div>
										<div class="col-1">
											<span class="action btn btn-principal legitRipple" id="addPaciente" style="user-select: none;">
												NOVO PACIENTE
											</span>
										</div>
									</div>
								</form>
								<form id="dadosResponsavel" class="form-validate-jquery d-none" action="#" data-fouc>
									<div class="card-header header-elements-inline" style="margin-left:10px;">
										<h5 class="text-uppercase font-weight-bold">Dados do Responsável</h5>
									</div>
									<div class="col-12 row text-center justify-content-center mb-5" id="selectResponsavel">
										<div class="col-lg-12 my-3 text-black-50">
											<h5>Selecione o responsável</h5>
										</div>
										<div class="col-5">
											<select id="parentescoCadatrado" name="parentescoCadatrado" class="select-search">
											</select>
										</div>
										<div class="col-1">
											<span class="action btn btn-principal legitRipple" id="addResponsavel" style="user-select: none;">
												NOVO RESPONSÁVEL
											</span>
										</div>
									</div>
								</form>
								<form id="dadosAtendimento" class="form-validate-jquery d-none" action="#" data-fouc>
									<div class="card-header header-elements-inline" style="margin-left:10px;">
										<h5 class="text-uppercase font-weight-bold">Dados do Atendimento</h5>
									</div>
									<div class="card-body">
										<div class="col-lg-12 mb-4 row mt-2">
											<!-- titulos -->
											<div class="col-lg-3">
												<label>Data do Atendimento</label>
											</div>
											<div class='col-lg-3'>
												<label>Modalidade <span class='text-danger'>*</span></label>
											</div>
											<div class='col-lg-3'>
												<label>Classificação do Atendimento <span class='text-danger'>*</span></label>
											</div>
											<div class='col-lg-3'>
												<label>Classificação de risco <span class='text-danger'>*</span></label>
											</div>

											<!-- campos -->
											<div class="col-lg-3">
												<input id="dataAtendimento" name="dataAtendimento" type="text" readonly class="form-control">
											</div>
											<div class='col-lg-3'>
												<select id='modalidade' name='modalidade' class='select-search' required>
													<option value='' selected>selecionar</option>
												</select>
											</div>
											<div class='col-lg-3'>
												<select id='classificacao' name='classificacao' class='select-search' required>
													<option value='' selected>selecionar</option>
												</select>
											</div>
											<div class='col-lg-3'>
												<select id='classificacaoRisco' name='classificacaoRisco' class='select-search' required>
													<option value='' selected>selecionar</option>
												</select>
											</div>
										</div>

										<div class="col-lg-12 my-3 text-black-50">
											<h5 class="mb-0 font-weight-semibold">Serviços</h5>
										</div>

										<div class="col-lg-12 mb-4 row">
											<!-- titulos -->
											<div class="col-lg-4">
												<label>Grupo</label>
											</div>
											<div class="col-lg-4">
												<label>Sub-Grupo</label>
											</div>
											<div class="col-lg-4">
												<label>Serviço</label>
											</div>											

											<!-- campos -->
											<div class="col-lg-4">
												<select id="grupo" name="grupo" class="select-search">
													<option value="" selected>selecione</option>
												</select>
											</div>
											<div class="col-lg-4">
												<select id="subgrupo" name="subgrupo" class="form-control form-control-select2">
													<option value="" selected>Selecione</option>
												</select>
											</div>
											<div class="col-lg-4">
												<select id="servico" name="servico" class="select-search">
													<option value="" selected>selecionar</option>
												</select>
											</div>
										</div>

										<div class="col-lg-12 mb-4 row">
											<!-- titulos -->
											<div class="col-lg-5">
												<label>Profissional</label>
											</div>
											<div class="col-lg-5">
												<label>Local do Atendimento</label>
											</div>
											<div class="col-lg-2">
												<label>Horário</label>
											</div>

											<!-- campos -->
											<div class="col-lg-5">
												<select id="medicos" name="medicos" class="select-search">
													<option value="" selected>selecione</option>
												</select>
											</div>
											<div class="col-lg-5">
												<select id="localAtendimento" name="localAtendimento" class="form-control form-control-select2">
													<option value="" selected>Selecione</option>
												</select>
											</div>
											<div id="modalHora" class="col-lg-2">
												<input id="horaAtendimento" name="horaAtendimento" type="text" class="form-control pickatime-disabled">
											</div>
										</div>

										<div class="col-lg-12 mb-4 row">
											<!-- titulos -->
											<div class="col-lg-12">
												<label>Observações</label>
											</div>

											<!-- campos -->
											<div class="col-lg-12">
												<textarea id="observacaoAtendimento" name="observacaoAtendimento" class="form-control" placeholder="Observações"></textarea>
											</div>
										</div>
									</div>
								</form>
							</div>
						</div>

						<div id="informacoes" class="card">
							<div id="novoPaciente" class="d-none">
								<div class="card-body">

									<div class="card-header header-elements-inline" style="margin-left: -10px;">
										<h5 class="text-uppercase font-weight-bold">Dados Pessoais do paciente</h5>
									</div>

									<div class="col-lg-12 mb-4 row">
										<!-- titulos -->
										<div class="col-lg-3">
											<label>Prontuário</label>
										</div>
										<div class="col-lg-3">
											<label>Nome <span class="text-danger">*</span></label>
										</div>
										<div class="col-lg-3">
											<label>Nome Social</label>
										</div>
										<div class="col-lg-3">
											<label>CPF <span class="text-danger">*</span></label>
										</div>

										<!-- campos -->
										<div class="col-lg-3">
											<input id="prontuario" name="prontuario" type="text" class="form-control" placeholder="Prontuário Eletrônico" readonly>
										</div>
										<div class="col-lg-3">
											<input id="nome" name="nome" type="text" class="form-control" placeholder="Nome completo" required>
										</div>
										<div class="col-lg-3">
											<input id="nomeSocial" name="nomeSocial" type="text" class="form-control" placeholder="Nome Social">
										</div>
										<div class="col-lg-3">
											<input id="cpf" name="cpf" type="text" class="form-control" placeholder="CPF" data-mask="999.999.999-99" required>
										</div>
									</div>

									<div class="col-lg-12 mb-4 row">
										<!-- titulos -->
										<div class="col-lg-3">
											<label>CNS</label>
										</div>
										<div class="col-lg-3">
											<label>RG</label>
										</div>
										<div class="col-lg-3">
											<label>Emissor</label>
										</div>
										<div class="col-lg-3">
											<label>UF</label>
										</div>

										<!-- campos -->
										<div class="col-lg-3">
											<input id="cns" name="cns" type="text" class="form-control" placeholder="Cartão do SUS">
										</div>
										<div class="col-lg-3">
											<input id="rg" name="rg" type="text" class="form-control" placeholder="RG" data-mask="99.999.999-99">
										</div>
										<div class="col-lg-3">
											<input id="emissor" name="emissor" type="text" class="form-control" placeholder="Orgão Emissor">
										</div>
										<div class="col-lg-3">
											<select id="uf" name="uf" class="form-control form-control-select2" placeholder="UF">
												<option value="">Selecione</option>
												<option value="AC">AC</option>
												<option value="AL">AL</option>
												<option value="AP">AP</option>
												<option value="AM">AM</option>
												<option value="BA">BA</option>
												<option value="CE">CE</option>
												<option value="DF">DF</option>
												<option value="ES">ES</option>
												<option value="GO">GO</option>
												<option value="MA">MA</option>
												<option value="MT">MT</option>
												<option value="MS">MS</option>
												<option value="MG">MG</option>
												<option value="PA">PA</option>
												<option value="PB">PB</option>
												<option value="PR">PR</option>
												<option value="PE">PE</option>
												<option value="PI">PI</option>
												<option value="RJ">RJ</option>
												<option value="RN">RN</option>
												<option value="RS">RS</option>
												<option value="RO">RO</option>
												<option value="RR">RR</option>
												<option value="SC">SC</option>
												<option value="SP">SP</option>
												<option value="SE">SE</option>
												<option value="TO">TO</option>
											</select>
										</div>
									</div>

									<div class="col-lg-12 mb-4 row">
										<!-- titulos -->
										<div class="col-lg-2">
											<label>Sexo</label>
										</div>
										<div class="col-lg-2">
											<label>Data de Nascimento</label>
										</div>
										<div class="col-lg-4">
											<label>Nome do Pai</label>
										</div>
										<div class="col-lg-4">
											<label>Nome da Mãe</label>
										</div>

										<!-- campos -->
										<div class="col-lg-2">
											<select id="sexo" name="sexo" class="form-control form-control-select2">
												<option value="" selected>selecionar</option>
												<option value="M">Masculino</option>
												<option value="F">Feminino</option>
											</select>
										</div>
										<div class="col-lg-2">
											<input id="nascimento" name="nascimento" type="date" class="form-control" placeholder="dd/mm/aaaa">
										</div>
										<div class="col-lg-4">
											<input id="nomePai" name="nomePai" type="text" class="form-control" placeholder="Nome do Pai">
										</div>
										<div class="col-lg-4">
											<input id="nomeMae" name="nomeMae" type="text" class="form-control" placeholder="Nome da Mãe">
										</div>
									</div>

									<div class="col-lg-12 mb-4 row">
										<!-- titulos -->
										<div class="col-lg-3">
											<label>Raça/Cor</label>
										</div>
										<div class="col-lg-3">
											<label>Estado Civil</label>
										</div>
										<div class="col-lg-3">
											<label>Naturalidade</label>
										</div>
										<div class="col-lg-3">
											<label>Profissão</label>
										</div>

										<!-- campos -->
										<div class="col-lg-3">
											<select id="racaCor" name="racaCor" class="form-control form-control-select2">
												<option value="#">Selecione</option>
												<option value="Branca">Branca</option>
												<option value="Preta">Preta</option>
												<option value="Parda">Parda</option>
												<option value="Amarela">Amarela</option>
												<option value="Indígena">Indígena</option>
											</select>
										</div>
										<div class="col-lg-3">
											<select id="estadoCivil" name="estadoCivil" class="form-control form-control-select2">
												<option value="#">Selecione</option>
												<option value="ST">Solteiro</option>
												<option value="CS">Casado</option>
												<option value="SP">Separado</option>
												<option value="DV">Divorciado</option>
												<option value="VI">Viúvo</option>
											</select>
										</div>
										<div class="col-lg-3">
											<input type="text" id="naturalidade" name="naturalidade" class="form-control" placeholder="Naturalidade">
										</div>
										<div class="col-lg-3">
											<input id="profissao" name="profissao" type="text" class="form-control" placeholder="Profissão">
										</div>
									</div>

									<div class="col-lg-12 my-3 text-black-50">
										<h5 class="mb-0 font-weight-semibold">Endereço do Paciente</h5>
									</div>

									<div class="col-lg-12 mb-4 row">
										<!-- titulos -->
										<div class="col-lg-3">
											<label>CEP</label>
										</div>
										<div class="col-lg-4">
											<label>Endereço</label>
										</div>
										<div class="col-lg-2">
											<label>Nº</label>
										</div>
										<div class="col-lg-3">
											<label>Complemento</label>
										</div>

										<!-- campos -->
										<div class="col-lg-3">
											<input id="cep" name="cep" type="text" class="form-control" placeholder="CEP">
										</div>
										<div class="col-lg-4">
											<input id="endereco" name="endereco" type="text" class="form-control" placeholder="EX.: Rua, Av">
										</div>
										<div class="col-lg-2">
											<input id="numero" name="numero" type="text" class="form-control" placeholder="Número">
										</div>
										<div class="col-lg-3">
											<input id="complemento" name="complemento" type="text" class="form-control" placeholder="Complemento">
										</div>
									</div>

									<div class="col-lg-12 mb-4 row">
										<!-- titulos -->
										<div class="col-lg-4">
											<label>Bairro</label>
										</div>
										<div class="col-lg-4">
											<label>Cidade</label>
										</div>
										<div class="col-lg-4">
											<label>Estado</label>
										</div>

										<!-- campos -->
										<div class="col-lg-4">
											<input id="bairro" name="bairro" type="text" class="form-control" placeholder="Bairro">
										</div>
										<div class="col-lg-4">
											<input id="cidade" name="cidade" type="text" class="form-control" placeholder="Cidade">
										</div>
										<div class="col-lg-4">
											<select id="estado" name="estado" class="form-control form-control-select2" placeholder="Estado">
												<option value="#">Selecione um estado</option>
												<option value="AC">Acre</option>
												<option value="AL">Alagoas</option>
												<option value="AP">Amapá</option>
												<option value="AM">Amazonas</option>
												<option value="BA">Bahia</option>
												<option value="CE">Ceará</option>
												<option value="DF">Distrito Federal</option>
												<option value="ES">Espírito Santo</option>
												<option value="GO">Goiás</option>
												<option value="MA">Maranhão</option>
												<option value="MT">Mato Grosso</option>
												<option value="MS">Mato Grosso do Sul</option>
												<option value="MG">Minas Gerais</option>
												<option value="PA">Pará</option>
												<option value="PB">Paraíba</option>
												<option value="PR">Paraná</option>
												<option value="PE">Pernambuco</option>
												<option value="PI">Piauí</option>
												<option value="RJ">Rio de Janeiro</option>
												<option value="RN">Rio Grande do Norte</option>
												<option value="RS">Rio Grande do Sul</option>
												<option value="RO">Rondônia</option>
												<option value="RR">Roraima</option>
												<option value="SC">Santa Catarina</option>
												<option value="SP">São Paulo</option>
												<option value="SE">Sergipe</option>
												<option value="TO">Tocantins</option>
												<option value="NA">Estrangeiro</option>	
											</select>
										</div>
									</div>

									<div class="col-lg-12 my-3 text-black-50">
										<h5 class="mb-0 font-weight-semibold">Contato</h5>
									</div>

									<div class="col-lg-12 mb-4 row">
										<!-- titulos -->
										<div class="col-lg-3">
											<label>Nome</label>
										</div>
										<div class="col-lg-3">
											<label>Telefone</label>
										</div>
										<div class="col-lg-3">
											<label>Celular</label>
										</div>
										<div class="col-lg-3">
											<label>E-mail</label>
										</div>

										<!-- campos -->
										<div class="col-lg-3">
											<input id="contato" name="contato" type="text" class="form-control" placeholder="Contato">
										</div>
										<div class="col-lg-3">
											<input id="telefone" name="telefone" type="text" class="form-control" placeholder="Telefone" data-mask="(99) 9999-9999">
										</div>
										<div class="col-lg-3">
											<input id="celular" name="celular" type="text" class="form-control" placeholder="Celular" data-mask="(99) 99999-9999">
										</div>
										<div class="col-lg-3">
											<input id="email" name="email" type="text" class="form-control" placeholder="E-mail">
										</div>
									</div>

									<div class="col-lg-12 mb-4 row">
										<!-- titulos -->
										<div class="col-lg-12">
											<label>Observação</label>
										</div>

										<!-- campos -->
										<div class="col-lg-12">
											<textarea id="observacao" name="observacao" class="form-control" placeholder="Observações"></textarea>
										</div>
									</div>
								</div>
							</div>
							<div id="novoResponsavel" class="d-none">
								<div class="card-header header-elements-inline" style="margin-left:10px;">
									<h5 class="text-uppercase font-weight-bold">Dados Pessoais do responsável</h5>
								</div>
								<div class="card-body">
									<div class="col-lg-12 mb-4 row">
										<!-- titulos -->
										<div class="col-lg-4">
											<label>Nome <span class="text-danger">*</span></label>
										</div>
										<div class="col-lg-4">
											<label>Parentesco</label>
										</div>
										<div class="col-lg-4">
											<label>Nascimento</label>
										</div>

										<!-- campos -->
										<div class="col-lg-4">
											<input id="nomeResp" name="nomeResp" type="text" class="form-control" placeholder="Nome" required>
										</div>
										<div class="col-lg-4">
											<input id="parentescoResp" name="parentesco" type="text" class="form-control" placeholder="Parentesco">
										</div>
										<div class="col-lg-4">
											<input id="nascimentoResp" name="nascimentoResp" type="date" class="form-control">
										</div>
									</div>

									<div class="col-lg-12 my-3 text-black-50">
										<h5 class="mb-0 font-weight-semibold">Endereço do Responsável</h5>
									</div>

									<div class="col-lg-12 mb-4 row">
										<!-- titulos -->
										<div class="col-lg-3">
											<label>CEP</label>
										</div>
										<div class="col-lg-4">
											<label>Endereço</label>
										</div>
										<div class="col-lg-2">
											<label>Nº</label>
										</div>
										<div class="col-lg-3">
											<label>Complemento</label>
										</div>

										<!-- campos -->
										<div class="col-lg-3">
											<input id="cepResp" name="cepResp" type="text" class="form-control" placeholder="CEP">
										</div>
										<div class="col-lg-4">
											<input id="enderecoResp" name="enderecoResp" type="text" class="form-control" placeholder="EX.: Rua, Av">
										</div>
										<div class="col-lg-2">
											<input id="numeroResp" name="numeroResp" type="text" class="form-control" placeholder="Número">
										</div>
										<div class="col-lg-3">
											<input id="complementoResp" name="complementoResp" type="text" class="form-control" placeholder="Complemento">
										</div>
									</div>

									<div class="col-lg-12 mb-4 row">
										<!-- titulos -->
										<div class="col-lg-4">
											<label>Bairro</label>
										</div>
										<div class="col-lg-4">
											<label>Cidade</label>
										</div>
										<div class="col-lg-4">
											<label>Estado</label>
										</div>

										<!-- campos -->
										<div class="col-lg-4">
											<input id="bairroResp" name="bairroResp" type="text" class="form-control" placeholder="Bairro">
										</div>
										<div class="col-lg-4">
											<input id="cidadeResp" name="cidadeResp" type="text" class="form-control" placeholder="Cidade">
										</div>
										<div class="col-lg-4">
											<select id="estadoResp" name="estadoResp" class="form-control form-control-select2" placeholder="Estado">
												<option value="#">Selecione um estado</option>
												<option value="AC">Acre</option>
												<option value="AL">Alagoas</option>
												<option value="AP">Amapá</option>
												<option value="AM">Amazonas</option>
												<option value="BA">Bahia</option>
												<option value="CE">Ceará</option>
												<option value="DF">Distrito Federal</option>
												<option value="ES">Espírito Santo</option>
												<option value="GO">Goiás</option>
												<option value="MA">Maranhão</option>
												<option value="MT">Mato Grosso</option>
												<option value="MS">Mato Grosso do Sul</option>
												<option value="MG">Minas Gerais</option>
												<option value="PA">Pará</option>
												<option value="PB">Paraíba</option>
												<option value="PR">Paraná</option>
												<option value="PE">Pernambuco</option>
												<option value="PI">Piauí</option>
												<option value="RJ">Rio de Janeiro</option>
												<option value="RN">Rio Grande do Norte</option>
												<option value="RS">Rio Grande do Sul</option>
												<option value="RO">Rondônia</option>
												<option value="RR">Roraima</option>
												<option value="SC">Santa Catarina</option>
												<option value="SP">São Paulo</option>
												<option value="SE">Sergipe</option>
												<option value="TO">Tocantins</option>
												<option value="ES">Estrangeiro</option>
											</select>
										</div>
									</div>

									<div class="col-lg-12 my-3 text-black-50">
										<h5 class="mb-0 font-weight-semibold">Contato</h5>
									</div>

									<div class="col-lg-12 mb-4 row">
										<!-- titulos -->
										<div class="col-lg-4">
											<label>Telefone</label>
										</div>
										<div class="col-lg-4">
											<label>Celular</label>
										</div>
										<div class="col-lg-4">
											<label>E-mail</label>
										</div>

										<!-- campos -->
										<div class="col-lg-4">
											<input id="telefoneResp" name="telefoneResp" type="text" class="form-control" placeholder="Telefone" data-mask="(99) 9999-9999">
										</div>
										<div class="col-lg-4">
											<input id="celularResp" name="celularResp" type="text" class="form-control" placeholder="Celular" data-mask="(99) 99999-9999">
										</div>
										<div class="col-lg-4">
											<input id="emailResp" name="emailResp" type="text" class="form-control" placeholder="E-mail">
										</div>
									</div>

									<div class="col-lg-12 mb-4 row">
										<!-- titulos -->
										<div class="col-lg-12">
											<label>Observação</label>
										</div>

										<!-- campos -->
										<div class="col-lg-12">
											<textarea id="observacaoResp" name="observacaoResp" class="form-control" placeholder="Observações"></textarea>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php include_once("footer.php"); ?>
		</div>
	</div>

	<!--Modal-->
	<div id="page-modal-paciente" class="custon-modal">
		<div class="custon-modal-container" style="max-width: 800px; height: 95%;">
			<div class="card custon-modal-content" style="height: 95%;">
				<div class="custon-modal-title mb-2" style="background-color: #466d96; color: #ffffff">
					<p class="h5">Novo paciente</p>
					<i id="modalPaciente-close-x" class="fab-icon-open icon-cross2 p-3" style="cursor: pointer"></i>
				</div>
				<div class="px-0" style="overflow-y: scroll;">
					<div class="d-flex flex-row">
						<div class="col-lg-12">
							<form id="novoPaciente" name="alterarSituacao" method="POST" class="form-validate-jquery">
								<div class="form-group">

									<div class="card-header header-elements-inline" style="margin-left: -10px;">
										<h5 class="text-uppercase font-weight-bold">Dados Pessoais do paciente</h5>
									</div>

									<div class="col-lg-12 mb-4 row">
										<!-- titulos -->
										<div class="col-lg-6">
											<label>Nome <span class="text-danger">*</span></label>
										</div>
										<div class="col-lg-6">
											<label>Nome Social</label>
										</div>

										<!-- campos -->
										<div class="col-lg-6">
											<input id="nomeNew" name="nomeNew" type="text" class="form-control" placeholder="Nome completo">
										</div>
										<div class="col-lg-6">
											<input id="nomeSocialNew" name="nomeSocialNew" type="text" class="form-control" placeholder="Nome Social">
										</div>
									</div>

									<div class="col-lg-12 mb-4 row">
										<!-- titulos -->
										<div class="col-lg-4">
											<label>CPF <span class="text-danger">*</span></label>
										</div>
										<div class="col-lg-4">
											<label>CNS</label>
										</div>
										<div class="col-lg-4">
											<label>RG</label>
										</div>

										<!-- campos -->
										<div class="col-lg-4">
											<input id="cpfNew" name="cpfNew" type="text" class="form-control" placeholder="CPF" data-mask="999.999.999-99" required>
										</div>
										<div class="col-lg-4">
											<input id="cnsNew" name="cnsNew" type="text" class="form-control" placeholder="Cartão do SUS">
										</div>
										<div class="col-lg-4">
											<input id="rgNew" name="rgNew" type="text" class="form-control" placeholder="RG" data-mask="99.999.999-99">
										</div>
									</div>

									<div class="col-lg-12 mb-4 row">
										<!-- titulos -->
										<div class="col-lg-3">
											<label>Emissor</label>
										</div>
										<div class="col-lg-2">
											<label>UF</label>
										</div>
										<div class="col-lg-3">
											<label>Sexo</label>
										</div>
										<div class="col-lg-4">
											<label>Data de Nascimento</label>
										</div>

										<!-- campos -->
										<div class="col-lg-3">
											<input id="emissorNew" name="emissorNew" type="text" class="form-control" placeholder="Orgão Emissor">
										</div>
										<div class="col-lg-2">
											<select id="ufNew" name="ufNew" class="form-control form-control-select2" placeholder="UF">
												<option value="">Selecione</option>
												<option value="AC">AC</option>
												<option value="AL">AL</option>
												<option value="AP">AP</option>
												<option value="AM">AM</option>
												<option value="BA">BA</option>
												<option value="CE">CE</option>
												<option value="DF">DF</option>
												<option value="ES">ES</option>
												<option value="GO">GO</option>
												<option value="MA">MA</option>
												<option value="MT">MT</option>
												<option value="MS">MS</option>
												<option value="MG">MG</option>
												<option value="PA">PA</option>
												<option value="PB">PB</option>
												<option value="PR">PR</option>
												<option value="PE">PE</option>
												<option value="PI">PI</option>
												<option value="RJ">RJ</option>
												<option value="RN">RN</option>
												<option value="RS">RS</option>
												<option value="RO">RO</option>
												<option value="RR">RR</option>
												<option value="SC">SC</option>
												<option value="SP">SP</option>
												<option value="SE">SE</option>
												<option value="TO">TO</option>
											</select>
										</div>
										<div class="col-lg-3">
											<select id="sexoNew" name="sexoNew" class="form-control form-control-select2">
												<option value="" selected>selecionar</option>
												<option value="M">Masculino</option>
												<option value="F">Feminino</option>
											</select>
										</div>
										<div class="col-lg-4">
											<input id="nascimentoNew" name="nascimentoNew" type="date" class="form-control" placeholder="dd/mm/aaaa">
										</div>
									</div>

									<div class="col-lg-12 mb-4 row">
										<!-- titulos -->
										<div class="col-lg-6">
											<label>Nome do Pai</label>
										</div>
										<div class="col-lg-6">
											<label>Nome da Mãe</label>
										</div>

										<!-- campos -->
										<div class="col-lg-6">
											<input id="nomePaiNew" name="nomePaiNew" type="text" class="form-control" placeholder="Nome do Pai">
										</div>
										<div class="col-lg-6">
											<input id="nomeMaeNew" name="nomeMaeNew" type="text" class="form-control" placeholder="Nome da Mãe">
										</div>
									</div>

									<div class="col-lg-12 mb-4 row">
										<!-- titulos -->
										<div class="col-lg-3">
											<label>Raça/Cor</label>
										</div>
										<div class="col-lg-3">
											<label>Estado Civil</label>
										</div>
										<div class="col-lg-3">
											<label>Naturalidade</label>
										</div>
										<div class="col-lg-3">
											<label>Profissão</label>
										</div>

										<!-- campos -->
										<div class="col-lg-3">
											<select id="racaCorNew" name="racaCorNew" class="form-control form-control-select2">
												<option value="#">Selecione</option>
												<option value="Branca">Branca</option>
												<option value="Preta">Preta</option>
												<option value="Parda">Parda</option>
												<option value="Amarela">Amarela</option>
												<option value="Indígena">Indígena</option>
											</select>
										</div>
										<div class="col-lg-3">
											<select id="estadoCivilNew" name="estadoCivilNew" class="form-control form-control-select2">
												<option value="#">Selecione</option>
												<option value="ST">Solteiro</option>
												<option value="CS">Casado</option>
												<option value="SP">Separado</option>
												<option value="DV">Divorciado</option>
												<option value="VI">Viúvo</option>
											</select>
										</div>
										<div class="col-lg-3">
											<input id="naturalidadeNew" name="naturalidadeNew" type="text" class="form-control" placeholder="Naturalidade">
										</div>
										<div class="col-lg-3">
											<input id="profissaoNew" name="profissaoNew" type="text" class="form-control" placeholder="Profissão" required>
										</div>
									</div>

									<div class="col-lg-12 my-3 text-black-50">
										<h5 class="mb-0 font-weight-semibold">Endereço do Paciente</h5>
									</div>

									<div class="col-lg-12 mb-4 row">
										<!-- titulos -->
										<div class="col-lg-3">
											<label>CEP</label>
										</div>
										<div class="col-lg-4">
											<label>Endereço</label>
										</div>
										<div class="col-lg-2">
											<label>Nº</label>
										</div>
										<div class="col-lg-3">
											<label>Complemento</label>
										</div>

										<!-- campos -->
										<div class="col-lg-3">
											<input id="cepNew" name="cepNew" type="text" class="form-control" placeholder="CEP">
										</div>
										<div class="col-lg-4">
											<input id="enderecoNew" name="enderecoNew" type="text" class="form-control" placeholder="EX.: Rua, Av">
										</div>
										<div class="col-lg-2">
											<input id="numeroNew" name="numeroNew" type="text" class="form-control" placeholder="Número">
										</div>
										<div class="col-lg-3">
											<input id="complementoNew" name="complementoNew" type="text" class="form-control" placeholder="Complemento">
										</div>
									</div>

									<div class="col-lg-12 mb-4 row">
										<!-- titulos -->
										<div class="col-lg-4">
											<label>Bairro</label>
										</div>
										<div class="col-lg-4">
											<label>Cidade</label>
										</div>
										<div class="col-lg-4">
											<label>Estado</label>
										</div>

										<!-- campos -->
										<div class="col-lg-4">
											<input id="bairroNew" name="bairroNew" type="text" class="form-control" placeholder="Bairro">
										</div>
										<div class="col-lg-4">
											<input id="cidadeNew" name="cidadeNew" type="text" class="form-control" placeholder="Cidade">
										</div>
										<div class="col-lg-4">
											<select id="estadoNew" name="estadoNew" class="form-control form-control-select2" placeholder="Estado">
												<option value="#">Selecione um estado</option>
												<option value="AC">Acre</option>
												<option value="AL">Alagoas</option>
												<option value="AP">Amapá</option>
												<option value="AM">Amazonas</option>
												<option value="BA">Bahia</option>
												<option value="CE">Ceará</option>
												<option value="DF">Distrito Federal</option>
												<option value="ES">Espírito Santo</option>
												<option value="GO">Goiás</option>
												<option value="MA">Maranhão</option>
												<option value="MT">Mato Grosso</option>
												<option value="MS">Mato Grosso do Sul</option>
												<option value="MG">Minas Gerais</option>
												<option value="PA">Pará</option>
												<option value="PB">Paraíba</option>
												<option value="PR">Paraná</option>
												<option value="PE">Pernambuco</option>
												<option value="PI">Piauí</option>
												<option value="RJ">Rio de Janeiro</option>
												<option value="RN">Rio Grande do Norte</option>
												<option value="RS">Rio Grande do Sul</option>
												<option value="RO">Rondônia</option>
												<option value="RR">Roraima</option>
												<option value="SC">Santa Catarina</option>
												<option value="SP">São Paulo</option>
												<option value="SE">Sergipe</option>
												<option value="TO">Tocantins</option>
												<option value="ES">Estrangeiro</option>	
											</select>
										</div>
									</div>

									<div class="col-lg-12 my-3 text-black-50">
										<h5 class="mb-0 font-weight-semibold">Contato</h5>
									</div>

									<div class="col-lg-12 mb-4 row">
										<!-- titulos -->
										<div class="col-lg-4">
											<label>Nome</label>
										</div>
										<div class="col-lg-2">
											<label>Telefone</label>
										</div>
										<div class="col-lg-2">
											<label>Celular</label>
										</div>
										<div class="col-lg-4">
											<label>E-mail</label>
										</div>

										<!-- campos -->
										<div class="col-lg-4">
											<input id="contatoNew" name="contatoNew" type="text" class="form-control" placeholder="Contato">
										</div>
										<div class="col-lg-2">
											<input id="telefoneNew" name="telefoneNew" type="text" class="form-control" placeholder="Telefone" data-mask="(99) 9999-9999">
										</div>
										<div class="col-lg-2">
											<input id="celularNew" name="celularNew" type="text" class="form-control" placeholder="Celular" data-mask="(99) 99999-9999">
										</div>
										<div class="col-lg-4">
											<input id="emailNew" name="emailNew" type="text" class="form-control" placeholder="E-mail">
										</div>
									</div>

									<div class="col-lg-12 mb-4 row">
										<!-- titulos -->
										<div class="col-lg-12">
											<label>Observação</label>
										</div>

										<!-- campos -->
										<div class="col-lg-12">
											<textarea id="observacaoNew" name="observacaoNew" class="form-control" placeholder="Observações"></textarea>
										</div>
									</div>
								</div>
							</form>
						</div>
					</div>
					<div class="text-right m-2"><button id="salvarPacienteModal" class="btn btn-principal" role="button">Confirmar</button></div>
				</div>
			</div>
		</div>
	</div>

	<div id="page-modal-responsavel" class="custon-modal">
		<div class="custon-modal-container" style="max-width: 800px;">
			<div class="card custon-modal-content">
				<div class="custon-modal-title mb-2" style="background-color: #466d96; color: #ffffff">
					<p class="h5">Novo responsável</p>
					<i id="modalResponsavel-close-x" class="fab-icon-open icon-cross2 p-3" style="cursor: pointer"></i>
				</div>
				<div class="px-0">
					<div class="d-flex flex-row">
						<div class="col-lg-12">
							<form id="novoResponsavel" name="novoResponsavel" method="POST" class="form-validate-jquery">
								<div class="form-group">
									<div class="card-header header-elements-inline" style="margin-left: -10px;">
										<h5 class="text-uppercase font-weight-bold">Dados Pessoais do responsável</h5>
									</div>
									<div class="col-lg-12 mb-4 row">
										<!-- titulos -->
										<div class="col-lg-4">
											<label>Nome <span class="text-danger">*</span></label>
										</div>
										<div class="col-lg-4">
											<label>Parentesco</label>
										</div>
										<div class="col-lg-4">
											<label>Nascimento</label>
										</div>

										<!-- campos -->
										<div class="col-lg-4">
											<input id="nomeRespNew" name="nomeResp" type="text" class="form-control" placeholder="Nome" required>
										</div>
										<div class="col-lg-4">
											<input id="parentescoRespNew" name="parentesco" type="text" class="form-control" placeholder="Parentesco">
										</div>
										<div class="col-lg-4">
											<input id="nascimentoRespNew" name="nascimentoResp" type="date" class="form-control">
										</div>
									</div>

									<div class="col-lg-12 my-3 text-black-50">
										<h5 class="mb-0 font-weight-semibold">Endereço do Responsável</h5>
									</div>

									<div class="col-lg-12 mb-4 row">
										<!-- titulos -->
										<div class="col-lg-3">
											<label>CEP</label>
										</div>
										<div class="col-lg-4">
											<label>Endereço</label>
										</div>
										<div class="col-lg-2">
											<label>Nº</label>
										</div>
										<div class="col-lg-3">
											<label>Complemento</label>
										</div>

										<!-- campos -->
										<div class="col-lg-3">
											<input id="cepRespNew" name="cepResp" type="text" class="form-control" placeholder="CEP">
										</div>
										<div class="col-lg-4">
											<input id="enderecoRespNew" name="enderecoResp" type="text" class="form-control" placeholder="EX.: Rua, Av">
										</div>
										<div class="col-lg-2">
											<input id="numeroRespNew" name="numeroResp" type="text" class="form-control" placeholder="Número">
										</div>
										<div class="col-lg-3">
											<input id="complementoRespNew" name="complementoResp" type="text" class="form-control" placeholder="Complemento">
										</div>
									</div>

									<div class="col-lg-12 mb-4 row">
										<!-- titulos -->
										<div class="col-lg-4">
											<label>Bairro</label>
										</div>
										<div class="col-lg-4">
											<label>Cidade</label>
										</div>
										<div class="col-lg-4">
											<label>Estado</label>
										</div>

										<!-- campos -->
										<div class="col-lg-4">
											<input id="bairroRespNew" name="bairroResp" type="text" class="form-control" placeholder="Bairro">
										</div>
										<div class="col-lg-4">
											<input id="cidadeRespNew" name="cidadeResp" type="text" class="form-control" placeholder="Cidade">
										</div>
										<div class="col-lg-4">
											<select id="estadoRespNew" name="estadoRespNew" class="form-control form-control-select2" placeholder="Estado">
												<option value="#">Selecione um estado</option>
												<option value="AC">Acre</option>
												<option value="AL">Alagoas</option>
												<option value="AP">Amapá</option>
												<option value="AM">Amazonas</option>
												<option value="BA">Bahia</option>
												<option value="CE">Ceará</option>
												<option value="DF">Distrito Federal</option>
												<option value="ES">Espírito Santo</option>
												<option value="GO">Goiás</option>
												<option value="MA">Maranhão</option>
												<option value="MT">Mato Grosso</option>
												<option value="MS">Mato Grosso do Sul</option>
												<option value="MG">Minas Gerais</option>
												<option value="PA">Pará</option>
												<option value="PB">Paraíba</option>
												<option value="PR">Paraná</option>
												<option value="PE">Pernambuco</option>
												<option value="PI">Piauí</option>
												<option value="RJ">Rio de Janeiro</option>
												<option value="RN">Rio Grande do Norte</option>
												<option value="RS">Rio Grande do Sul</option>
												<option value="RO">Rondônia</option>
												<option value="RR">Roraima</option>
												<option value="SC">Santa Catarina</option>
												<option value="SP">São Paulo</option>
												<option value="SE">Sergipe</option>
												<option value="TO">Tocantins</option>
												<option value="ES">Estrangeiro</option>	
											</select>
										</div>
									</div>

									<div class="col-lg-12 my-3 text-black-50">
										<h5 class="mb-0 font-weight-semibold">Contato</h5>
									</div>

									<div class="col-lg-12 mb-4 row">
										<!-- titulos -->
										<div class="col-lg-4">
											<label>Telefone</label>
										</div>
										<div class="col-lg-4">
											<label>Celular</label>
										</div>
										<div class="col-lg-4">
											<label>E-mail</label>
										</div>

										<!-- campos -->
										<div class="col-lg-4">
											<input id="telefoneRespNew" name="telefoneResp" type="text" class="form-control" placeholder="Res. / Com.">
										</div>
										<div class="col-lg-4">
											<input id="celularRespNew" name="celularResp" type="text" class="form-control" placeholder="Celular">
										</div>
										<div class="col-lg-4">
											<input id="emailRespNew" name="emailResp" type="text" class="form-control" placeholder="E-mail">
										</div>
									</div>

									<div class="col-lg-12 mb-4 row">
										<!-- titulos -->
										<div class="col-lg-12">
											<label>Observação</label>
										</div>

										<!-- campos -->
										<div class="col-lg-12">
											<textarea id="observacaoRespNew" name="observacaoResp" class="form-control" placeholder="Observações"></textarea>
										</div>
									</div>
								</div>
							</form>
						</div>
					</div>
					<div class="text-right m-2"><button id="salvarResponsavelModal" class="btn btn-principal" role="button">Confirmar</button></div>
				</div>
			</div>
		</div>
	</div>

	<div id="pageModalDescontos" class="custon-modal">
		<div class="custon-modal-container" style="max-width: 500px;">
			<div class="card custon-modal-content">
				<div class="custon-modal-title mb-2" style="background-color: #466d96; color: #ffffff">
					<p id='tituloModal' class="h4">Desconto</p>
					<i id="modalDesconto-close-x" class="fab-icon-open icon-cross2 p-3" style="cursor: pointer"></i>
				</div>
				<div class="px-0">
					<div class="d-flex flex-row">
						<div class="col-lg-12">
							<form id="editaSituacao" name="alterarSituacao" method="POST" class="form-validate-jquery">
								<div class="form-group">
									<!--<div class="custon-modal-title">
										<i class=""></i>
										<p class="h3">Descontos</p>
										<i class=""></i>
									</div> -->
									
									<div class="p-4">
										<div class="d-flex flex-row justify-content-between">
											<div class="col-lg-12" style="text-align:left;">
												<div class="form-group row">
													<div class="col-lg-4">
														<label>Desconto</label>
													</div>
													<div class="col-lg-4">
														<label>Valor</label>
													</div>
													<div class="col-lg-4">
														<label>Valor Final</label>
													</div>

													<div class="col-lg-4">
														<input id="inputDesconto" maxLength="12" class="form-control" type="number" name="inputDesconto">
													</div>
													<div class="col-lg-4">
														<input id="inputModalValorB" maxLength="12" class="form-control" type="text" readonly>
													</div>
													<div class="col-lg-4">
														<input id="inputModalValorF" maxLength="12" class="form-control" type="text" readonly>
													</div>

													<input id="itemDescontoId" name="itemId" type="hidden" value=''>
													<input id="itemDescontoValue" name="itemId" type="hidden" value=''>
												</div>
											</div>
										</div>
									</div>
								</div>
							</form>
						</div>
					</div>
					<div class="text-right m-2">
						<button id="setDesconto" class="btn btn-principal" role="button">Confirmar</button>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!--end Modal-->

	<?php include_once("alerta.php"); ?>
</body>

</html>