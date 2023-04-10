<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Agendamento';
$_SESSION['agendaProfissional'] = [];

include('global_assets/php/conexao.php');

// as duas lista "$visaoAtendente" e "$visaoProfissional" representam os perfis
// que podem ver a tela de atendimento na visão do atendente ou profissional respectivamente

$iUnidade = $_SESSION['UnidadeId'];
$iEmpresa = $_SESSION['EmpreId'];
$usuarioId = $_SESSION['UsuarId'];

$sql = "SELECT P.ProfiId as id,P.ProfiNome as nome,PF.ProfiCbo as cbo,PF.ProfiNome as profissao
	FROM Profissional P
	JOIN Profissao PF ON PF.ProfiId = P.ProfiProfissao
	WHERE P.ProfiUnidade = $iUnidade";
$result = $conn->query($sql);
$rowProfissionais = $result->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Agendamento</title>

	<?php include_once("head.php"); ?>

	<!-- ///////////////////////////////////////////////////////////////////////////////////// -->
	<!-- Theme JS files -->
	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
	<script src="global_assets/js/demo_pages/form_select2.js"></script>
	<script src="global_assets/js/plugins/forms/selects/bootstrap_multiselect.js"></script>
	<script src="global_assets/js/demo_pages/form_multiselect.js"></script>

	<script src="global_assets/js/demo_pages/form_layouts.js"></script>
	<script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>
    <script src="global_assets/js/plugins/editors/summernote/summernote.min.js"></script>

	<script src="global_assets/js/plugins/forms/inputs/inputmask.js"></script>

    <script src="global_assets/js/demo_pages/datatables_responsive.js"></script>
	<script src="global_assets/js/demo_pages/datatables_sorting.js"></script>
    <script src="global_assets/js/plugins/tables/datatables/datatables.min.js"></script>
	<script src="global_assets/js/plugins/tables/datatables/extensions/responsive.min.js"></script>
	<!-- /theme JS files -->

	<!-- Validação -->
	<script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
	<script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
	<script src="global_assets/js/demo_pages/form_validation.js"></script>
	<script src="global_assets/js/plugins/ui/fullcalendar/fullcalendar.min.js"></script>
	<script src="global_assets/js/plugins/ui/fullcalendar/lang/pt-br.js"></script>
	<style>
		.excluirContainer {
			width: 100%;
			height: 220px;
			padding: 10px;
			background-color: #ccc;
			color: #333;
			opacity: 0.2;
			border: 1px solid #333;
		}
		textarea{
            height:80px;
        }
		.btnCuston{
			text-transform: uppercase;
			padding: 10px;
			font-size: 12px;
			line-height: 1.3;
			border-radius: 0.25rem;
			border: 0px;
		}

		/* cabeçalhor do FullCalendar*/
		.fc-widget-header{
			background-color: #466d96;
			color: #FFF;
		}
	</style>
	<?php
		echo "<script>
				iUnidade = $iUnidade
				iEmpresa = $iEmpresa
			</script>"
	?>

	<script type="text/javascript">
		var viewerCalendar = 'agendaWeek'
		var selectCalendar = false
		var filtro = {
			status: null,
			local: null,
		}
		const socket = WebSocketConnect(iUnidade,iEmpresa)
		socket.onmessage = function (event) {
			menssage = JSON.parse(event.data)
			if(menssage.type == 'AGENDA'){
				getAgenda()
			}
		};
		$(document).ready(function(){
			let dataDe = new Date()
			// o mes aqui vem de 0 a 11 e eu quero pegar o mes anterior por isso o "-1"
			let mes = dataDe.getMonth()<1?12:dataDe.getMonth()
			let dia = '01'
			mes = mes > 9?mes:`0${mes}`
			let ano = dataDe.getFullYear()

			$('#dataFiltroDe').val(`${ano}-${mes}-${dia}`)
			$('#dataFiltroAte').val('')

			getAgenda()
			getCmbs()

			if(filtro.status || filtro.local){
				$('#filtro').attr('style','cursor: pointer; font-size:20px;color: #388E3C;')
			}else{
				$('#filtro').attr('style','cursor: pointer; font-size:20px;color: #000;')
			}

			$('#relacaoBloqueiosTable').DataTable({
				"order": [[ 0, "desc" ]],
			    autoWidth: false,
				responsive: true,
			    columnDefs: [
				{
					orderable: true,   //DataI - HoraI
					width: "15%",
					targets: [0]
				},
				{ 
					orderable: true,   //DataF - HoraF
					width: "15%",
					targets: [1]
				},
				{ 
					orderable: true,   //titulo
					width: "20%",
					targets: [2]
				},
				{ 
					orderable: true,   //profissional
					width: "20%",
					targets: [3]
				},
				{ 
					orderable: true,   //recorrente
					width: "10%",
					targets: [4]
				},				
				{ 
					orderable: true,   //repeticao
					width: "15%",
					targets: [5]
				},
				{ 
					orderable: true,   //acoes
					width: "5%",
					targets: [6]
				}],
				dom: '<"datatable-header"fl><"datatable-scroll-wrap"t><"datatable-footer"ip>',
                language: {
                    search: '<span>Filtro:</span> _INPUT_',
                    searchPlaceholder: 'filtra qualquer coluna...',
                    lengthMenu: '<span>Mostrar:</span> _MENU_',
                    paginate: {
                        'first': 'Primeira',
                        'last': 'Última',
                        'next': $('html').attr('dir') == 'rtl' ? '&larr;' : '&rarr;',
                        'previous': $('html').attr('dir') == 'rtl' ? '&rarr;' : '&larr;'
                    }
                }
			})
			$('#definirHorario').on('click', function(e){
				e.preventDefault()
				$('#page-modal-horario').fadeOut(200)

				let id = $('#idEvent').val()
				let horaAgendaInicio = $('#horaAgendaInicio').val()
				let horaAgendaFim = $('#horaAgendaFim').val()
				let horaIntervalo = $('#horaIntervalo').val()

				$.ajax({
					type: 'POST',
					url: 'filtraProfissionalAgenda.php',
					dataType: 'json',
					data:{
						'tipoRequest': 'SETHORAAGENDA',
						'id':id,
						'horaAgendaInicio':horaAgendaInicio,
						'horaAgendaFim':horaAgendaFim,
						'horaIntervalo':horaIntervalo,
					},
					success: function(response){
						refreshAgenda()
						alerta(response.titulo, response.menssagem, response.status)
					}
				});
			})
			$('#profissional').on('change', function(e){
				e.preventDefault()
				getAgenda()
			})
			$("#textObservacao").on('input', function(e){
                cantaCaracteres('textObservacao', 800, 'caracteresInputObservacao')
            })
			$('#novoAgendamento').on('click', function(e){
				e.preventDefault()
				$('#paciente').val('').change()
				$('#modalidade').val('').change()
				$('#servico').val('').change()
				$('#situacao').val('').change()
				$('#localAtendimento').val('').change()
				$('#medico').val('').change()
				getCmbs()
				const date = new Date();

				let dia = date.getDate() > 9?date.getDate():`0${date.getDate()}`
				let mes = date.getMonth()+1 > 9?date.getMonth()+1:`0${date.getMonth()+1}`
				let ano = date.getFullYear()

				let horaI = date.getHours()>9?date.getHours():`0${date.getHours()}`
				let minutoI = date.getMinutes()>9?date.getMinutes():`0${date.getMinutes()}`

				let hora = date.getHours()>9?date.getHours():`0${date.getHours()}`
				let minuto = date.getMinutes()>9?date.getMinutes():`0${date.getMinutes()}`
				
				$('#inputData').val(`${ano}-${mes}-${dia}`)
				$('#inputHora').val(`${hora}:${minuto}`)
				$('#inputHoraFim').val(`${hora}:${minuto}`)
				$('#textObservacao').val('')
				$('#idAgendamento').val('')
				$('#tituloModal').html('Novo Agendamento')

				$('#inputData').attr('readonly', false)
				$('#inputHora').attr('readonly', false)
				$('#inputHoraFim').attr('readonly', false)
				$('#textObservacao').attr('readonly', false)
				$('#paciente').attr('disabled', false)
				$('#modalidade').attr('disabled', false)
				$('#servico').attr('disabled', false)
				$('#localAtendimento').attr('disabled', false)
				$('#medico').attr('disabled', false)
				$('#agendaRecorrenteCheck').prop('checked', false)
				$('#agendaRecorrente').addClass('d-none')
				$('#addPaciente').removeClass('d-none')

				$('#agendaRecorrenteCheck').prop('checked', false)
				$('#repeticaoAgendamento').val('').change()
				$('#quantidadeRecorrenciaAgendamento').val(0)
				$('#segundaAg').prop('checked', false)
				$('#tercaAg').prop('checked', false)
				$('#quartaAg').prop('checked', false)
				$('#quintaAg').prop('checked', false)
				$('#sextaAg').prop('checked', false)
				$('#sabadoAg').prop('checked', false)
				$('#domingoAg').prop('checked', false)
				$('#dataRecorrenciaAgendamento').val('')

				$('#agendaRecorrente').addClass('d-none')
				$('#page-modal-agendamento').fadeIn(200)
			})
			$('#formAgendamentoNovo').submit(function(e){
				e.preventDefault()
			})
			$('#novoPaciente').submit(function(e){
				e.preventDefault()
			})
			$('#formFiltro').submit(function(e){
				e.preventDefault()
			})
			$('#addPaciente').submit(function(e){
				e.preventDefault()
			})
			$('#inputData').on('input',function(e){
				$.ajax({
					type: 'POST',
					url: 'filtraAgendamento.php',
					dataType: 'json',
					data: {
						'tipoRequest': 'CHECKAGENDAUNIDADE',
						'data': $('#inputData').val()
					},
					success: function(response) {
						if(response.tipo == 'error'){
							alerta(response.titulo,response.menssagem,response.tipo)
							$('#inputData').val('')
						}else{
							getCmbs()
							$('#inputHora').val('')
							$('#inputHoraFim').val('')
						}
					}
				})
			})
			$('#inputHora').on('input',function(e){
				$.ajax({
					type: 'POST',
					url: 'filtraAgendamento.php',
					dataType: 'json',
					data: {
						'tipoRequest': 'CHECKAGENDAUNIDADE',
						'data': $('#inputData').val(),
						'horaI': $('#inputHora').val(),
						'horaF': $('#inputHoraFim').val(),
					},
					success: function(response) {
						if(response.tipo == 'error'){
							alerta(response.titulo,response.menssagem,response.tipo)
							$('#inputHora').val('')
						}else{
							getCmbs()
						}
					}
				})
			})
			$('#inputHoraFim').on('input',function(e){
				$.ajax({
					type: 'POST',
					url: 'filtraAgendamento.php',
					dataType: 'json',
					data: {
						'tipoRequest': 'CHECKAGENDAUNIDADE',
						'data': $('#inputData').val(),
						'horaI': $('#inputHora').val(),
						'horaF': $('#inputHoraFim').val(),
					},
					success: function(response) {
						if(response.tipo == 'error'){
							alerta(response.titulo,response.menssagem,response.tipo)
							$('#inputHoraFim').val('')
						}else{
							getCmbs()
						}
					}
				})
			})
			$('#medico').on('change', function(e){
				// vai preencher cmbLocal
				if($(this).val()){
					$.ajax({
						type: 'POST',
						url: 'filtraAgendamento.php',
						dataType: 'json',
						data:{
							'tipoRequest': 'LOCALATENDIMENTO',
							'iMedico': $(this).val()
						},
						success: function(response) {
							$('#localAtendimento').empty()
							$('#localAtendimento').append(`<option value=''>Selecione</option>`)
							response.forEach(item => {
								let opt = `<option value="${item.id}">${item.nome}</option>`
								$('#localAtendimento').append(opt)
							})
						}
					});
				}else{
					$('#localAtendimento').empty()
					$('#localAtendimento').append(`<option value=''>Selecione</option>`)
				}
			})
			$('#inserirAgendamento').on('click', function(e){
				$.ajax({
					type: 'POST',
					url: 'filtraAgendamento.php',
					dataType: 'json',
					data: {
						'tipoRequest': 'CHECKAGENDAUNIDADE',
						'data': $('#inputData').val(),
						'horaI': $('#inputHora').val(),
						'horaF': $('#inputHoraFim').val()
					},
					success: function(response) {
						if(response.tipo == 'error'){
							alerta(response.titulo,response.menssagem,response.tipo)
							$('#inputData').val('')
							$('#inputHora').val('')
							$('#inputHoraFim').val('')
						}else{
							let menssageError = ''
							switch (menssageError) {
								case $('#data').val():
									menssageError = 'Informe a data!!';
									$('#data').focus();
									break;
								case $('#hora').val():
									menssageError = 'Informe o horário!!';
									$('#hora').focus();
									break;
								case $('#paciente').val():
									menssageError = 'Informe o paciente!!';
									$('#paciente').focus();
									break;
								case $('#modalidade').val():
									menssageError = 'Informe a modalidade!!';
									$('#modalidade').focus();
									break;
								case $('#servico').val():
									menssageError = 'Informe o serviço!!';
									$('#servico').focus();
									break;
								case $('#profissional').val():
									menssageError = 'Informe o profissional!!';
									$('#profissional').focus();
									break;
								case $('#localAtendimento').val():
									menssageError = 'Informe o local!!';
									$('#localAtendimento').focus();
									break;
								case $('#situacao').val():
									menssageError = 'Informe a Situação!!';
									$('#situacao').focus();
									break;
								default:
									menssageError = '';
									break;
							}

							if (menssageError) {
								alerta('Campo Obrigatório!', menssageError, 'error')
								return
							}

							if(!$('#idAgendamento').val() && $('#inputData').val() < updateDateTime().dataAtual || ($('#inputData').val() == updateDateTime().dataAtual && $('#inputHora').val() < updateDateTime().horaAtual)){
								alerta('Data e Hora inválida!', 'Data e hora do registro não pode ser retroativa', 'error')
								return
							}
							$.ajax({
								type: 'POST',
								url: 'filtraAgendamento.php',
								dataType: 'json',
								data: {
									'tipoRequest':'GETRECORRENCIA',
									'data': $('#inputData').val(),
									'horaI': $('#inputHora').val(),
									'horaF': $('#inputHoraFim').val(),
									'repeticaoAgendamento': $('#repeticaoAgendamento').val()?$('#repeticaoAgendamento').val():'1S',
									'quantidadeRecorrenciaAgendamento': $('#quantidadeRecorrenciaAgendamento').val(),
									'segunda': $('#segundaAg').is(':checked')?1:0,
									'terca': $('#tercaAg').is(':checked')?1:0,
									'quarta': $('#quartaAg').is(':checked')?1:0,
									'quinta': $('#quintaAg').is(':checked')?1:0,
									'sexta': $('#sextaAg').is(':checked')?1:0,
									'sabado': $('#sabadoAg').is(':checked')?1:0,
									'domingo': $('#domingoAg').is(':checked')?1:0,
									'recorrente': $('#agendaRecorrenteCheck').is(':checked')?1:0,
									'profissional': $('#medico').val()
								},
								success: function(response) {
									if(response.status == 'error'){
										alerta(response.titulo, response.menssagem, response.status)
										$('#quantidadeRecorrenciaAgendamento').val(0)
										return
									}
									$.ajax({
										type: 'POST',
										url: 'filtraAgendamento.php',
										dataType: 'json',
										data: {
											'tipoRequest': 'ADDAGENDAMENTO',
											'data':response.datas,
											'horaI':$('#inputHora').val(),
											'horaF':$('#inputHoraFim').val(),
											'paciente':$('#paciente').val(),
											'modalidade':$('#modalidade').val(),
											'servico':$('#servico').val(),
											'profissional':$('#medico').val(),
											'local':$('#localAtendimento').val(),
											'situacao':$('#situacao').val(),
											'observacao':$('#textObservacao').val(),
											'idAgendamento': $('#idAgendamento').val(),
											'repeticaoAgendamento': $('#repeticaoAgendamento').val(),
											'quantidadeRecorrenciaAgendamento': $('#quantidadeRecorrenciaAgendamento').val(),
											'segundaAg': $('#segundaAg').val(),
											'tercaAg': $('#tercaAg').val(),
											'quartaAg': $('#quartaAg').val(),
											'quintaAg': $('#quintaAg').val(),
											'sextaAg': $('#sextaAg').val(),
											'sabadoAg': $('#sabadoAg').val(),
											'domingoAg': $('#domingoAg').val(),
											'dataRecorrenciaAgendamento': $('#dataRecorrenciaAgendamento').val(),
										},
										success: function(response) {
											if(response.status == 'error'){
												alerta(response.titulo,response.menssagem,response.status)
												return
											}else{
												$('#page-modal-agendamento').fadeOut(200)
												getAgenda()
												socket.sendMenssage({
													'type':'AGENDA'
												});
											}
										}
									});
								}
							})
						}
					}
				})
			})
			$('#addPaciente').on('click', function(e){
				e.preventDefault();
				$('#page-modal-paciente').fadeIn();
			})
			$('#salvarPacienteModal').on('click', function(e) {
				e.preventDefault()

				let menssageError = ''
				switch (menssageError) {
					case $('#nomeNew').val():
						menssageError = 'Informe o nome!!';
						$('#nomeNew').focus();
						break;
					default:
						menssageError = '';
						break;
				}

				if (menssageError) {
					alerta('Campo Obrigatório!', menssageError, 'error')
					return
				}

				if($('#cpfNew').val()){
					var cpfSoNumeros = $('#cpfNew').val().replace(/[^\d]+/g, '');
					if(!validaCPF(cpfSoNumeros)){
						alerta('CPF Inválido!', 'Digite um CPF válido!!', 'error')
						return
					}
				}

				if($("#nascimentoNew").val()){
					let dataPreenchida = $("#nascimentoNew").val();
					if(!validaDataNascimento(dataPreenchida)){
						$('#nascimentoNew').val('');
						alerta('Atenção', 'Data de nascimento não pode ser futura!', 'error');
						$('#nascimentoNew').focus();
						return
					}
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
					success: function(response) {
						if (response.status == 'success') {
							alerta(response.titulo, response.menssagem, response.status)
							getCmbs({'pacienteID': response.id})
							$('#page-modal-paciente').fadeOut(200)
						} else {
							alerta(response.titulo, response.menssagem, response.status)
						}
					}
				});
			})
			$('#novoBloqueio').on('click', function(e){
				e.preventDefault()
				getFilters()
				$('#page-modal-configRelacao').fadeOut(200)
				$('#typeInsert').val('NEW')
				$('#salvarEvento').html('Inserir')
				$('#inputDataInicioBloqueio').val('')
				$('#bloqueio').val('')
				$('#justificativa').val('')
				$('#inputHoraInicioBloqueio').val('')
				$('#inputDataFimBloqueio').val('')
				$('#inputHoraFimBloqueio').val('')
				$('#recorrente').prop('checked', false)
				$('#repeticao').prop('checked', false)
				$('#segunda').prop('checked', false)
				$('#terca').prop('checked', false)
				$('#quarta').prop('checked', false)
				$('#quinta').prop('checked', false)
				$('#sexta').prop('checked', false)
				$('#sabado').prop('checked', false)
				$('#domingo').prop('checked', false)
				$('#repeticao').val('')
				$('#quantidadeRecorrencia').val('')
				$('#dataRecorrencia').val('')
				$('#cardRecorrend').addClass('d-none')
				$('#page-modal-config').fadeIn(200)
			})
			$('#config').on('click', function(e){
				e.preventDefault()
				getBloqueios()
				$('#page-modal-configRelacao').fadeIn(200)
			})
			$('#configUnidade').on('click', function(e){
				e.preventDefault()

				$.ajax({
					type: 'POST',
					url: 'filtraAgendamento.php',
					dataType: 'json',
					data: {
						'tipoRequest': 'GETCONFIG'
					},
					success: function(response) {
						if(response.id){
							$('#inputHoraAberturaUnidade').val(response.abertura)
							$('#inputHoraFechamentoUnidade').val(response.fechamento)
							$('#inputHoraInicioUnidade').val(response.almocoInicio)
							$('#inputHoraFimUnidade').val(response.almocoFim)
							$('#inputHoraIntervaloUnidade').val(response.intervalo)
							$('#observacaoUnidade').val(response.observacao)
							
							$('#segundaUnidade').prop('checked', parseInt(response.segunda)?true:false)
							$('#tercaUnidade').prop('checked', parseInt(response.terca)?true:false)
							$('#quartaUnidade').prop('checked', parseInt(response.quarta)?true:false)
							$('#quintaUnidade').prop('checked', parseInt(response.quinta)?true:false)
							$('#sextaUnidade').prop('checked', parseInt(response.sexta)?true:false)
							$('#sabadoUnidade').prop('checked', parseInt(response.sabado)?true:false)
							$('#domingoUnidade').prop('checked', parseInt(response.domingo)?true:false)

							$('#inputHoraIntervaloUnidade').children("option").each(function(index, item){
								if($(item).val() == response.intervalo){
									$(item).change()
								}
							})
						}else{
							let hora = new Date();
							hora = `${hora.getHours()>9?hora.getHours():'0'+hora.getHours()}:${hora.getMinutes()>9?hora.getMinutes():'0'+hora.getMinutes()}`
							$('#inputHoraAberturaUnidade').val(hora)
							$('#inputHoraFechamentoUnidade').val(hora)
							$('#inputHoraInicioUnidade').val(hora)
							$('#inputHoraFimUnidade').val(hora)
							$('#inputHoraIntervaloUnidade').val('')
							$('#observacaoUnidade').val('')
							
							$('#segundaUnidade').prop('checked', true)
							$('#tercaUnidade').prop('checked', true)
							$('#quartaUnidade').prop('checked', true)
							$('#quintaUnidade').prop('checked', true)
							$('#sextaUnidade').prop('checked', true)
							$('#sabadoUnidade').prop('checked', false)
							$('#domingoUnidade').prop('checked', false)
						}
						$('#page-modal-configUnidade').fadeIn(200)
					}
				});
			})
			$('#recorrente').on('change', function(e){
				if($('#recorrente').is(':checked')){
					$('#cardRecorrend').removeClass('d-none')
					$('#inputDataFimBloqueio').attr('readonly', true)
					$('#inputHoraFimBloqueio').attr('readonly', true)
				}else{
					$('#cardRecorrend').addClass('d-none')
					$('#inputDataFimBloqueio').attr('readonly', false)
					$('#inputHoraFimBloqueio').attr('readonly', false)
				}
			})
			$('#agendaRecorrenteCheck').on('change', function(e){
				if($('#agendaRecorrenteCheck').is(':checked')){
					$('#agendaRecorrente').removeClass('d-none')
				}else{
					$('#recorrente').prop('checked', false)
					$('#repeticaoAgendamento').val('').change()
					$('#quantidadeRecorrenciaAgendamento').val(0)
					$('#segundaAg').prop('checked', false)
					$('#tercaAg').prop('checked', false)
					$('#quartaAg').prop('checked', false)
					$('#quintaAg').prop('checked', false)
					$('#sextaAg').prop('checked', false)
					$('#sabadoAg').prop('checked', false)
					$('#domingoAg').prop('checked', false)
					$('#dataRecorrenciaAgendamento').val('')
					$('#agendaRecorrente').addClass('d-none')
				}
			})
			$('#filtro').on('click', function(e){
				e.preventDefault()
				getFilters(filtro)
				$('#page-modal-filtro').fadeIn(200)
			})
			$('#filtrarAgendamento').on('click', function(e){
				e.preventDefault()
				filtro.status = null
				filtro.local = null

				if($('#statusFiltro').val()){
					filtro.status = $('#statusFiltro').val()
				}
				if($('#localFiltro').val()){
					filtro.local = $('#localFiltro').val()
				}
				getAgenda(filtro)
				$('#page-modal-filtro').fadeOut(200)
			})
			$('#selecionarCalendario').on('click',function(e){
				e.preventDefault()
				selectCalendar = true
				$('#page-modal-config').fadeOut(200)
				// setTimeout(() => {
				// 	selectCalendar = false
				// }, 2000)
			})
			$('#salvarEvento').on('click', function(e){
				e.preventDefault()

				let msg = ''
				switch(msg){
					case $('#medicoConfig').val():msg="Informe o profissional";break;
					case $('#bloqueio').val():msg="Informe o Título de Bloqueio ";break;
					case $('#inputDataInicioBloqueio').val():msg="Informe a data de início";break;
					case $('#inputHoraInicioBloqueio').val():msg="Informe a hora de início";break;
					case $('#inputDataFimBloqueio').val():msg="Informe a data de fim";break;
					case $('#inputHoraFimBloqueio').val():msg="Informe a hora de fim";break;
					default:msg = '';break;
				}
				// caso seja recorrente...
				if($('#recorrente').is(':checked')){
					switch(msg){
						case $('#repeticao').val():msg="Informe a frequência de repetiçoes";break;
						case $('#quantidadeRecorrencia').val():msg="Informe a quantidade de repetiçoes";break;
						default:msg = '';break;
					}
					if(!$('#segunda').is(':checked') && !$('#terca').is(':checked') && !$('#quarta').is(':checked') && !$('#quinta').is(':checked') && !$('#sexta').is(':checked') && !$('#sabado').is(':checked') && !$('#domingo').is(':checked')){
						msg="Informe pelo menos um dia"
					}
				}else{
					if($('#inputDataInicioBloqueio').val() > $('#inputDataFimBloqueio').val() || $('#inputDataFimBloqueio').val() < updateDateTime().dataAtual || $('#inputDataInicioBloqueio').val() < updateDateTime().dataAtual){
						msg="Data inválida!!"
					}
				}


				if(msg){
					alerta('Campo Obrigatório', msg,'error')
					return
				}

				$.ajax({
					type: 'POST',
					url: 'filtraAgendamento.php',
					dataType: 'json',
					data:{
						'tipoRequest': 'ADDEVENTO',
						'type': $('#typeInsert').val(),
						'medicoConfig':$('#medicoConfig').val(),
						'bloqueio':$('#bloqueio').val(),
						'justificativa':$('#justificativa').val(),
						'inputDataInicioBloqueio':$('#inputDataInicioBloqueio').val(),
						'inputHoraInicioBloqueio':$('#inputHoraInicioBloqueio').val(),
						'inputDataFimBloqueio':$('#inputDataFimBloqueio').val(),
						'inputHoraFimBloqueio':$('#inputHoraFimBloqueio').val(),
						'recorrente': $('#recorrente').is(':checked')?1:0,
						'repeticao':$('#repeticao').val(),
						'segunda':$('#segunda').is(':checked')?1:0,
						'terca':$('#terca').is(':checked')?1:0,
						'quarta':$('#quarta').is(':checked')?1:0,
						'quinta':$('#quinta').is(':checked')?1:0,
						'sexta':$('#sexta').is(':checked')?1:0,
						'sabado':$('#sabado').is(':checked')?1:0,
						'domingo':$('#domingo').is(':checked')?1:0,
						'repeticao':$('#repeticao').val(),
						'quantidadeRecorrencia':$('#quantidadeRecorrencia').val(),
						'dataRecorrencia':$('#dataRecorrencia').val(),
					},
					success: function(response) {
						alerta(response.titulo, response.menssagem, response.status)
						getAgenda()
						$('#page-modal-config').fadeOut(200)
					}
				});
			})
			$('#salvarConfigUnidade').on('click', function(e){
				e.preventDefault()

				let msg = ''
				switch(msg){
					case $('#inputHoraAberturaUnidade').val():msg="Informe o horário de abertura";break;
					case $('#inputHoraFechamentoUnidade').val():msg="Informe o horário de fechamento";break;
					case $('#inputHoraInicioUnidade').val():msg="Informe o horário inicial de almoço";break;
					case $('#inputHoraFimUnidade').val():msg="Informe o horário final de almoço";break;
					case $('#inputHoraIntervaloUnidade').val():msg="Informe o Intervalo";break;
					default:msg = '';break;
				}

				if(msg){
					alerta('Campo Obrigatório', msg,'error')
					return
				}

				$.ajax({
					type: 'POST',
					url: 'filtraAgendamento.php',
					dataType: 'json',
					data:{
						'tipoRequest': 'ADDCONFIGUNIDADE',
						'inputHoraAberturaUnidade':$('#inputHoraAberturaUnidade').val(),
						'inputHoraFechamentoUnidade':$('#inputHoraFechamentoUnidade').val(),
						'inputHoraInicioUnidade':$('#inputHoraInicioUnidade').val(),
						'inputHoraFimUnidade':$('#inputHoraFimUnidade').val(),
						'inputHoraIntervaloUnidade':$('#inputHoraIntervaloUnidade').val(),
						'observacaoUnidade':$('#observacaoUnidade').val(),

						'segunda':$('#segundaUnidade').is(':checked')?1:0,
						'terca':$('#tercaUnidade').is(':checked')?1:0,
						'quarta':$('#quartaUnidade').is(':checked')?1:0,
						'quinta':$('#quintaUnidade').is(':checked')?1:0,
						'sexta':$('#sextaUnidade').is(':checked')?1:0,
						'sabado':$('#sabadoUnidade').is(':checked')?1:0,
						'domingo':$('#domingoUnidade').is(':checked')?1:0
					},
					success: function(response) {
						alerta(response.titulo, response.menssagem, response.status)
						$('#page-modal-configUnidade').fadeOut(200)
						getAgenda()
					}
				});
			})
			$('#quantidadeRecorrencia').on('input', function(e){
				let date = new Date($('#inputDataInicioBloqueio').val())

				if($('#repeticao').val()[1] == 'S'){
					let days = parseInt($('#repeticao').val()[0])>1?parseInt($('#repeticao').val()[0])*7:7
					days = days*$(this).val()
					date.setDate(date.getDate() + days)

					while(date.toString().split(' ')[0] != 'Sat'){
						date.setDate(date.getDate() + 1)
					}
				}

				let finalDate = `${date.getFullYear()}-${date.getMonth()>9?date.getMonth()+1:'0'+(date.getMonth()+1)}-${date.getDate()>9?date.getDate():'0'+date.getDate()}`
				$('#dataRecorrencia').val(finalDate)
			})
			$('#dataRecorrencia').on('input', function(e){
				let dateI = new Date($('#inputDataInicioBloqueio').val())
				let dateF = new Date($(this).val())

				let diffTime = Math.abs(dateI - dateF);
				let diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));				

				if($('#repeticao').val()[1] == 'S'){
					$('#quantidadeRecorrencia').val(Math.round(diffDays/7))
				}else if($('#repeticao').val()[1] == 'M'){
					$('#quantidadeRecorrencia').val(Math.round(diffDays/30))
				}
			})
			$('#repeticaoAgendamento').on('change', function(e){
				getRecorrencia()
			})
			$('#quantidadeRecorrenciaAgendamento').on('input', function(e){
				getRecorrencia()
			})
			$('#modalidade').on('change', function(e){
				$('#servico').empty();
				$('#servico').append(`<option value=''>Carregando...</option>`)
				// vai preencher cmbServicos
				$.ajax({
					type: 'POST',
					url: 'filtraAgendamento.php',
					dataType: 'json',
					data:{
						'tipoRequest': 'SERVICOS',
						'modalidade': $('#modalidade').val()
					},
					success: function(response) {
						$('#servico').empty();
						$('#servico').append(`<option value=''>Selecione</option>`)
						response.forEach(item => {
							let opt = `<option selected data-val="${item.valor}" value="${item.id}">${item.nome}</option>`
							$('#servico').append(opt)
						})
						$('#servico').val('').change()
					}
				})
			})
			$('#servico').on('change', function(e){
				$('#servico option').each(function(){
					if($('#servico').val() && $(this).val() == $('#servico').val()){
						$('#valorProduto').html(` R$ ${float2moeda($(this).data('val'))}`)
					}else{
						$('#valorProduto').html(` R$ 0,00`)
					}
				})
				// vai preencher cmbMedico
				if($(this).val()){
					$.ajax({
						type: 'POST',
						url: 'filtraAgendamento.php',
						dataType: 'json',
						data:{
							'tipoRequest': 'MEDICOS',
							'servico': $(this).val(),
							'data': $('#inputData').val(),
							'hora': $('#inputHora').val()
						},
						success: function(response) {
							$('#medico').empty()
							$('#medico').append(`<option value=''>Selecione</option>`)
							
							response.forEach(async item => {
								if(item.datasRecorrente.length && item.datasRecorrente.filter(obj => obj.data.includes($('#inputData').val())).length){
									return
								}

								// vai verificar se a data e horário selecionados estão dentro de um bloqueio pre estabelecido
								let inInterval = false
								await item.datasIntervalo.forEach(data => {
									inInterval = data.dataI <= $('#inputData').val() && data.horaI <= $('#inputHora').val() && (data.dataF > $('#inputData').val() || (data.dataF == $('#inputData').val() && data.horaF >= $('#inputHora').val()))?true:inInterval
								})

								if(inInterval){
									return
								}

								let opt = `<option value="${item.id}">${item.nome}</option>`
								$('#medico').append(opt)
							})
						}
					});
				}else{
					$('#medico').empty()
					$('#medico').append(`<option value=''>Selecione</option>`)
				}
			})
			$('#dataRecorrenciaAgendamento').on('input', function(e){
				getRecorrencia()
			})
			$('#modalConfig-close-x').on('click', function(e){
				e.preventDefault()
				$('#page-modal-config').fadeOut(200)
			})
			$('#configRelacao-close-x').on('click', () => {
				$('#page-modal-configRelacao').fadeOut(200)
			})
			$('#modalConfigUnidade-close-x').on('click', function(e){
				e.preventDefault()
				$('#page-modal-configUnidade').fadeOut(200)
			})
			$('#modalPaciente-close-x').on('click', () => {
				$('#iAtendimento').val('')
				$('#page-modal-paciente').fadeOut(200)
			})
			$('#modalFiltro-close-x').on('click', function(e){
				e.preventDefault()
				$('#page-modal-filtro').fadeOut(200)
			})
			$('#modalAgendamento-close-x').on('click', function(){
				$('#page-modal-agendamento').fadeOut(200)
			})
			$('.diasUpdate').each(function(e){
				$(this).on('change', function(el){
					getRecorrencia()
				})
			})
		})

		function getRecorrencia(){
			if(!$('#inputData').val() || !$('#inputHora').val() || !$('#medico').val()){
					// alerta('Campo necessário!!','Informe um profissional uma data e um horário!!', 'error')
					return
				}
				$.ajax({
					type: 'POST',
					url: 'filtraAgendamento.php',
					dataType: 'json',
					data: {
						'tipoRequest':'GETRECORRENCIA',
						'data': $('#inputData').val(),
						'horaI': $('#inputHora').val(),
						'horaF': $('#inputHoraFim').val(),
						'repeticaoAgendamento': $('#repeticaoAgendamento').val()?$('#repeticaoAgendamento').val():'1S',
						'quantidadeRecorrenciaAgendamento': $('#quantidadeRecorrenciaAgendamento').val(),
						'segunda': $('#segundaAg').is(':checked')?1:0,
						'terca': $('#tercaAg').is(':checked')?1:0,
						'quarta': $('#quartaAg').is(':checked')?1:0,
						'quinta': $('#quintaAg').is(':checked')?1:0,
						'sexta': $('#sextaAg').is(':checked')?1:0,
						'sabado': $('#sabadoAg').is(':checked')?1:0,
						'domingo': $('#domingoAg').is(':checked')?1:0,
						'recorrente': $('#agendaRecorrenteCheck').is(':checked')?1:0,
						'profissional': $('#medico').val()
					},
					success: function(response) {
						if(response.status == 'success'){
							let data = response.datas[response.datas.length-1]
							$('#dataRecorrenciaAgendamento').val(data)
						}else if(response.status == 'error'){
							alerta(response.titulo, response.menssagem, response.status)
							// $('#quantidadeRecorrenciaAgendamento').val(0)
							return
						}
						
					}
				})
		}

		function getBloqueios(){
			getAgenda()
			$.ajax({
				type: 'POST',
				url: 'filtraAgendamento.php',
				dataType: 'json',
				data: {
					'tipoRequest':'GETBLOQUEIOS'
				},
				success: function(response) {
					let table = $('#relacaoBloqueiosTable').DataTable().clear().draw()

					table = $('#relacaoBloqueiosTable').DataTable()
					let rowNode

					response.forEach(item => {
						rowNode = table.row.add(item).draw().node()
						$(rowNode).find('td:eq(6)').attr('class', 'text-center')
					})
				}
			})
		}

		function deletBloqueio(id){
			confirmaExclusaoAjax('filtraAgendamento.php','Deseja excluir esse bloqueio?', 'DELBLOQUEIO', id, getBloqueios)
		}

		function updateDateTime(){
			let dataAtual = new Date().toLocaleString("pt-BR", {timeZone: "America/Bahia"});
			let horaAtual = dataAtual.split(', ')[1];

			horaAtual = horaAtual.split(':');
			horaAtual = `${horaAtual[0]}:${horaAtual[1]}`
			
			dataAtual = dataAtual.split(', ')[0];
			dataAtual = dataAtual.split('/')[2]+'-'+dataAtual.split('/')[1]+'-'+dataAtual.split('/')[0];

			return {
				'dataAtual':dataAtual,
				'horaAtual':horaAtual
			}
		}

		function getAgenda(filtro){
			if($('div.fc-agendaDay-view').length){
				viewerCalendar = 'agendaDay'
			} else if($('div.fc-agendaWeek-view').length){
				viewerCalendar = 'agendaWeek'
			} else if($('div.fc-month-view').length){
				viewerCalendar = 'month'
			}

			if(filtro && (filtro.status || filtro.local)){
				$('#filtro').attr('style','cursor: pointer; font-size:20px;color: #388E3C;')
			}else{
				$('#filtro').attr('style','cursor: pointer; font-size:20px;color: #000;')
			}

			// reseta o calendário
			$('#calendarioagendamento').fullCalendar('destroy')
			$('#calendarioagendamento').html(`<div class="text-center">
			<img src='global_assets/images/lamparinas/loader.gif' style='width: 200px' alt='Carregando calendário'>
			</div>`)
			// return

			// iniciar o calendário
			$.ajax({
				type: 'POST',
				url: 'filtraAgendamento.php',
				dataType: 'json',
				data:{
					'tipoRequest': 'AGENDAMENTOS',
					'profissionais': $('#profissional').val(),
					'status':filtro && filtro.status?filtro.status:null,
					'local':filtro && filtro.local?filtro.local:null,
					'inicio': $('#dataFiltroDe').val(),
					'fim': $('#dataFiltroAte').val()
				},
				success: function(response) {
					clearModal()

					let events = []
					let cor = {
						text:'',
						back:''
					}
					response.forEach(item =>{
						switch(item.situacao.cor){
							case 'primary':cor.text = '#000';cor.back = '#82bae8';break;
							case 'secondary':cor.text = '#000';cor.back = '#c9c9c9';break;
							case 'success':cor.text = '#000';cor.back = '#FFF';break;
							case 'info':cor.text = '#000';cor.back = '#a3f0fa';break;
							case 'warning':cor.text = '#000';cor.back = '#fbc3b1';break;
							case 'danger':cor.text = '#000';cor.back = '#f2b0ab';break;
							case 'light':cor.text = '#000';cor.back = '#ffff';break;
							case 'dark':cor.text = '#000';cor.back = '#c9c9c9';break;
							case 'white':cor.text = '#000';cor.back = '#ffff';break;
							case 'blue':cor.text = '#000';cor.back = '#66c1eb';break;
							case 'green':cor.text = '#000';cor.back = '#d3e7bb';break;
							case 'red':cor.text = '#000';cor.back = '#fab7b7';break;
							case 'yellow':cor.text = '#000';cor.back = '#f8ff94';break;
							case 'black':cor.text = '#000';cor.back = '#c9c9c9';break;
							case 'orange':cor.text = '#000';cor.back = '#f6be93';break;
							default: cor.text='';cor.back = '';break;
						}

						let dataMostra = item.data.split('-')
						dataMostra = `${dataMostra[2]}/${dataMostra[1]}/${dataMostra[0]}`

						let horaMostra = item.horaInicio.split(':')
						horaMostra = `${horaMostra[0]}:${horaMostra[1]}`
						events.push({
							id: item.id,
							textColor: cor.text,
							color: cor.back,
							borderColor: '#707070',
							type: 'AGENDAMENTO',
							title: item.cliente.nome,
							text: `${item.cliente.nome}; ${dataMostra} as ${horaMostra}; ${item.situacao.nome.toUpperCase()}`,
							status: item.status,
							start: `${item.data} ${item.horaInicio.split('.')[0]}`,
							end: `${item.data} ${item.horaFim.split('.')[0]}`,
							editable: true
						})
					})

					$.ajax({
						type: 'POST',
						url: 'filtraAgendamento.php',
						dataType: 'json',
						data:{
							'tipoRequest': 'MEDICOS',
							// 'servico': $(this).val(),
							// 'data': $('#inputData').val(),
							// 'hora': $('#inputHora').val()
						},
						success: function(response) {
							response.forEach(profissional => {
								profissional.datasRecorrente.forEach(item => {
									let dataMostra = item.data.split('-')
									dataMostra = `${dataMostra[2]}/${dataMostra[1]}/${dataMostra[0]}`

									events.push({
										id: item.id,
										type: 'BLOQUEIO',
										title: `Bloqueio ${profissional.nome}`,
										text: `Bloqueio ${dataMostra} do(a) Profissional ${profissional.nome}`,
										start: `${item.data}`,
										end: null,
										color: '#bfbfbf',
										textColor: cor.text,
										borderColor: '#5c5c5c',
										editable: false
									})
								})
								profissional.datasIntervalo.forEach(item => {
									console.log(item)
									let dataMostra = item.dataI.split('-')
									dataMostra = `${dataMostra[2]}/${dataMostra[1]}/${dataMostra[0]}`

									events.push({
										id: item.id,
										type: 'BLOQUEIO',
										title: `Bloqueio ${profissional.nome}`,
										text: `Bloqueio ${dataMostra} do(a) Profissional ${profissional.nome}`,
										start: `${item.dataI} ${item.horaI.split('.')[0]}`,
										end: `${item.dataF} ${item.horaF.split('.')[0]}`,
										color: '#bfbfbf',
										borderColor: '#5c5c5c',
										editable: false
									})
								})
							})
							//cria outro calendário com as informações novas

							$.ajax({
								type: 'POST',
								url: 'filtraAgendamento.php',
								dataType: 'json',
								data:{
									'tipoRequest': 'GETCONFIG'
								},
								success:function(response){
									let arrayDaysOff = []

									if(!parseInt(response.domingo)){
										arrayDaysOff.push(0) //Domingo
									}if(!parseInt(response.segunda)){
										arrayDaysOff.push(1) //Segunda
									}if(!parseInt(response.terca)){
										arrayDaysOff.push(2) //Terça
									}if(!parseInt(response.quarta)){
										arrayDaysOff.push(3) //Quarta
									}if(!parseInt(response.quinta)){
										arrayDaysOff.push(4) //Quinta
									}if(!parseInt(response.sexta)){
										arrayDaysOff.push(5) //Sexta
									}if(!parseInt(response.sabado)){
										arrayDaysOff.push(6) //Sábado
									}

									// Initialize the calendar
									$('#calendarioagendamento').html('')
									$('#calendarioagendamento').fullCalendar({
										header: {
											left: 'prev,next today',
											center: 'title',
											right: 'month,agendaWeek,agendaDay'
										},
										editable: true,
										defaultDate: updateDateTime().dataAtual,
										events: events,
										timeZone: 'America/Bahia',
										locale: 'pt-br',
										droppable: true,
										defaultView: viewerCalendar,
										selectable: true,
										eventDurationEditable:false,
										disableResizing: true,
										hiddenDays: arrayDaysOff,
										eventClick: function(event, jsEvent, view) {
											$.ajax({
												type: 'POST',
												url: 'filtraAgendamento.php',
												dataType: 'json',
												data:{
													'tipoRequest': 'GETAGENDAMENTO',
													'id': event.id,
													'type': event.type,
												},
												success: function(response){
													if(event.type == 'AGENDAMENTO'){
														let readOnlyOption = response.data < updateDateTime().dataAtual || (response.data == updateDateTime().dataAtual && response.hora < updateDateTime().horaAtual)?true:false
														
														$('#idAgendamento').val(event.id)
														$('#inputData').val(response.data)
														$('#inputHora').val(response.horaInicio)
														$('#inputHoraFim').val(response.horaFim)
														$('#textObservacao').val(response.observacao)
														$('#tituloModal').html('Editar Agendamento')

														getCmbs({
															'pacienteID':response.cliente,
															'modalidadeID':response.modalidade,
															'servicoID':response.servico,
															'medicoID':response.profissional,
															'localAtendimentoID':response.local,
															'situacaoID':response.situacao,
														})

														$('#inputData').attr('readonly', readOnlyOption)
														$('#inputHora').attr('readonly', readOnlyOption)
														$('#inputHoraFim').attr('readonly', readOnlyOption)
														$('#textObservacao').attr('readonly', readOnlyOption)
														$('#paciente').attr('disabled', readOnlyOption)
														$('#modalidade').attr('disabled', readOnlyOption)
														$('#servico').attr('disabled', readOnlyOption)
														$('#localAtendimento').attr('disabled', readOnlyOption)
														$('#medico').attr('disabled', readOnlyOption)
														// $('#situacao').attr('disabled', readOnlyOption)
														if(readOnlyOption){
															$('#addPaciente').addClass('d-none')
															$('#agendaRecorrenteCheckContainer').addClass('d-none')
														}else{
															$('#addPaciente').removeClass('d-none')
															$('#agendaRecorrenteCheckContainer').removeClass('d-none')
														}

														$('#agendaRecorrenteCheck').prop('checked', false)
														$('#repeticaoAgendamento').val('').change()
														$('#quantidadeRecorrenciaAgendamento').val(0)
														$('#segundaAg').prop('checked', false)
														$('#tercaAg').prop('checked', false)
														$('#quartaAg').prop('checked', false)
														$('#quintaAg').prop('checked', false)
														$('#sextaAg').prop('checked', false)
														$('#sabadoAg').prop('checked', false)
														$('#domingoAg').prop('checked', false)
														$('#dataRecorrenciaAgendamento').val('')

														$('#agendaRecorrente').addClass('d-none')

														$('#page-modal-agendamento').fadeIn(200)
													}else if(event.type == 'BLOQUEIO'){
														getFilters({
															'profissional': response.profissional
														})

														if(response.dataFinal >= updateDateTime().dataAtual || response.dataF >= updateDateTime().dataAtual){
															$('#inputDataInicioBloqueio').val(response.dataI)
															$('#inputHoraInicioBloqueio').val(response.horaI)
															$('#inputDataFimBloqueio').val(response.dataF)
															$('#inputHoraFimBloqueio').val(response.horaF)
															$('#bloqueio').val(response.descricao)
															$('#justificativa').val(response.justificativa)
															$('#recorrente').prop('checked', parseInt(response.quantidade)?true:false)
															$('#segunda').prop('checked', parseInt(response.segunda)?true:false)
															$('#terca').prop('checked', parseInt(response.terca)?true:false)
															$('#quarta').prop('checked', parseInt(response.quarta)?true:false)
															$('#quinta').prop('checked', parseInt(response.quinta)?true:false)
															$('#sexta').prop('checked', parseInt(response.sexta)?true:false)
															$('#sabado').prop('checked', parseInt(response.sabado)?true:false)
															$('#domingo').prop('checked', parseInt(response.domingo)?true:false)
															$('#repeticao').val(response.repeticao)
															$('#quantidadeRecorrencia').val(response.quantidade)
															$('#typeInsert').val(event.id)
															$('#salvarEvento').html('Atualizar')

															if(parseInt(response.quantidade)){
																$('#cardRecorrend').removeClass('d-none')
																$('#dataRecorrencia').val(response.dataFinal)
															}else{
																$('#cardRecorrend').addClass('d-none')
																$('#dataRecorrencia').val('')
															}
															$('#page-modal-config').fadeIn(200)
														}else{
															alerta('Bloqueio', 'O bloqueio não pode mais ser editado!!', 'error')
														}
														
													}
												}
											})
										},
										eventDrop: function(event, jsEvent, ui, view) {
											let data = event.start.format()
											data = data.split('T')

											let horaI = event.start.format()
											horaI = horaI.split('T')

											let horaF = event.end?event.end.format():''
											horaF = horaF.split('T')
											
											if(horaI[0] < updateDateTime().dataAtual || (horaI[0] == updateDateTime().dataAtual && horaI[1] < updateDateTime().horaAtual)){
												alerta('Data e Hora inválida!', 'Data e hora do registro não pode ser retroativa', 'error')
												getAgenda()
												return
											}

											if(horaF[0] && horaF[0] < updateDateTime().dataAtual || (horaF[0] == updateDateTime().dataAtual && horaF[1] < updateDateTime().horaAtual)){
												alerta('Data e Hora inválida!', 'Data e hora do registro não pode ser retroativa', 'error')
												getAgenda()
												return
											}

											$.ajax({
												type: 'POST',
												url: 'filtraAgendamento.php',
												dataType: 'json',
												data: {
													'tipoRequest': 'CHECKAGENDAUNIDADE',
													'data': data[0],
													'horaI': horaI[1],
													'horaF': horaF[1],
												},
												success: function(response) {
													if(response.tipo == 'error'){
														alerta(response.titulo,response.menssagem,response.tipo)
														$(this).val('')
														getAgenda()
													}else{
														$.ajax({
															type: 'POST',
															url: 'filtraAgendamento.php',
															dataType: 'json',
															data:{
																'tipoRequest': 'UPDATEDATA',
																'id': event.id,
																'data': data[0],
																'horaI': horaI[1],
																'horaF': horaF[1],
															},
															success: function(response){
																socket.sendMenssage({
																	'type':'AGENDA'
																});
															}
														})
													}
												}
											})
										},
										select: function(start, end, jsEvent, view) {
											let inicio = start.format().split('T')
											let fim = end.format().split('T')
											$('#idAgendamento').val('')

											$('#tituloModal').html('Novo Agendamento')
											
											if(inicio[0] < updateDateTime().dataAtual || (inicio[0] == updateDateTime().dataAtual && inicio[1] < updateDateTime().horaAtual)){
												alerta('Data e Hora inválida!', 'Data e hora do registro não pode ser retroativa', 'error')
												getAgenda()
												return
											}
											if(selectCalendar){
												// getFilters()
												$('#inputDataInicioBloqueio').val(inicio[0])
												$('#inputDataFimBloqueio').val(fim[0])
												if(inicio[1]){
													$('#inputHoraInicioBloqueio').val(inicio[1])
													$('#inputHoraFimBloqueio').val(fim[1])
												}
												selectCalendar = false
												$('#page-modal-config').fadeIn(200)
											}else{
												$.ajax({
													type: 'POST',
													url: 'filtraAgendamento.php',
													dataType: 'json',
													data: {
														'tipoRequest': 'CHECKAGENDAUNIDADE',
														'data': inicio[0],
														'horaI': inicio[1],
														'horaF': fim.length?fim[1]:'',
													},
													success: function(response) {
														if(response.tipo == 'error'){
															alerta(response.titulo,response.menssagem,response.tipo)
															return
														}
														$.ajax({
															type: 'POST',
															url: 'filtraAgendamento.php',
															dataType: 'json',
															data: {
																'tipoRequest': 'GETCONFIG'
															},
															success: function(response) {
																if(response.tipo == 'error'){
																	alerta(response.titulo,response.menssagem,response.tipo)
																	$(this).val('')
																}else{
																	getCmbs()
																	$('#inputData').val(inicio[0])

																	let horaI = response.abertura.split(':')
																	horaI = `${horaI[0]}:${horaI[1]}`

																	horaI = inicio[1]?inicio[1]:horaI

																	$('#inputHora').val(horaI)

																	let horaF = horaI.split(':')
																	horaF[0] = parseInt(horaF[1])+parseInt(response.intervalo)>59?parseInt(horaF[0])+1:horaF[0]
																	horaF[0] = horaF[0]>23?horaF[0]-23:parseInt(horaF[0])
																	horaF[1] = parseInt(horaF[1])+parseInt(response.intervalo)>59?parseInt(horaF[1])-parseInt(response.intervalo):parseInt(horaF[1])+parseInt(response.intervalo)
																	horaF = `${horaF[0]>9?horaF[0]:'0'+horaF[0]}:${horaF[1]>9?horaF[1]:'0'+horaF[1]}`
				
																	if(fim[1]){
																		$('#inputHoraFim').val(fim[1])
																	}else{
																		$('#inputHoraFim').val(horaF)
																	}
				
																	$('#inputData').attr('readonly', false)
																	$('#inputHora').attr('readonly', false)
																	$('#inputHoraFim').attr('readonly', false)
																	$('#textObservacao').attr('readonly', false)
																	$('#paciente').attr('disabled', false)
																	$('#modalidade').attr('disabled', false)
																	$('#servico').attr('disabled', false)
																	$('#localAtendimento').attr('disabled', false)
																	$('#medico').attr('disabled', false)
																	$('#agendaRecorrente').addClass('d-none')
																	$('#addPaciente').removeClass('d-none')
																	$('#textObservacao').val('')

																	$('#agendaRecorrenteCheck').prop('checked', false)
																	$('#repeticaoAgendamento').val('').change()
																	$('#quantidadeRecorrenciaAgendamento').val(0)
																	$('#segundaAg').prop('checked', false)
																	$('#tercaAg').prop('checked', false)
																	$('#quartaAg').prop('checked', false)
																	$('#quintaAg').prop('checked', false)
																	$('#sextaAg').prop('checked', false)
																	$('#sabadoAg').prop('checked', false)
																	$('#domingoAg').prop('checked', false)
																	$('#dataRecorrenciaAgendamento').val('')

																	$('#agendaRecorrente').addClass('d-none')
				
																	$('#page-modal-agendamento').fadeIn(200)
																}
															}
														})
													}
												})
											}
										},
										eventMouseover: function(event, jsEvent, view) {
											$(this).attr('title', `${event.text}`)
										},
										eventMouseout: function(event, jsEvent, view) {
										},
										dayClick: function(event, jsEvent, view) {
											console.log('click')
										}, 
										isRTL: false
									});
								}
							})
						}
					});
				}
			});
		}

		function formatDate(start,end){
			let dataI = ''
			let dataF = ''
			// as vezes ao vir do banco o campo "start" e "end" está como string,
			// e quando ele é criado aqui vem como array. Assim precisa desse switch.
			if(start){
				switch (typeof start){
					case 'object': // "[YYYY,MM,DD,HH,mm,ss]"
						let dia = start[2] > 9?start[2]:'0'+start[2]
						let mes = start[1] > 9?(start[1]+1):'0'+(start[1]+1)

						let hora = start[3] > 9?start[3]:'0'+start[3]
						let minuto = start[4] > 9?start[4]:'0'+start[4]

						dataI = start[0]+'-'+mes+'-'+dia+'T'+hora+':'+minuto+':00';
						dataI = new Date(dataI).toLocaleString("pt-BR", {timeZone: "America/Bahia"});
						break;
					default:dataI = new Date(start).toLocaleString("pt-BR", {timeZone: "America/Bahia"});break;
				}
			}
			if(end){
				switch (typeof end){
					case 'object': // "[YYYY,MM,DD,HH,mm,ss]"
						let dia = end[2] > 9?end[2]:'0'+end[2]
						let mes = end[1] > 9?(end[1]+1):'0'+(end[1]+1)

						let hora = end[3] > 9?end[3]:'0'+end[3]
						let minuto = end[4] > 9?end[4]:'0'+end[4]

						dataF = end[0]+'-'+mes+'-'+dia+'T'+hora+':'+minuto+':00';
						dataF = new Date(dataF).toLocaleString("pt-BR", {timeZone: "America/Bahia"});
						break;
					default:dataF = new Date(end).toLocaleString("pt-BR", {timeZone: "America/Bahia"});break;
				}
			}
			return {dataI:dataI,dataF:dataF}
		}

		function clearModal(){
			$('#horaAgendaInicio').val('')
			$('#horaAgendaFim').val('')
			$('#horaIntervalo').val('')	
		}

		function getCmbs(obj){
			let oldPaciente = $('#paciente').val()
			let oldModalidade = $('#modalidade').val()
			let oldServico = $('#servico').val()
			let oldSituacao = $('#situacao').val()
			let oldLocalAtendimento = $('#localAtendimento').val()
			let oldMedico = $('#medico').val()

			$('#paciente').empty()
			$('#modalidade').empty()
			$('#servico').empty()
			$('#situacao').empty()
			$('#localAtendimento').empty()
			$('#medico').empty()

			$('#paciente').append(`<option selected value=''>Carregando...</option>`)
			$('#modalidade').append(`<option selected value=''>Carregando...</option>`)
			$('#servico').append(`<option selected value=''>Carregando...</option>`)
			$('#situacao').append(`<option data-chave='' selected value=''>Carregando...</option>`)
			$('#localAtendimento').append(`<option selected value=''>Carregando...</option>`)
			$('#medico').append(`<option selected value=''>Carregando...</option>`)
			// vai preencher cmbPaciente
			$.ajax({
				type: 'POST',
				url: 'filtraAgendamento.php',
				dataType: 'json',
				data: {
					'tipoRequest': 'PACIENTES'
				},
				success: function(response) {
					$('#paciente').empty()
					$('#paciente').append(`<option value=''>Selecione</option>`)
					response.forEach(item => {
						let opt = `<option selected value="${item.id}">${item.nome}</option>`
						$('#paciente').append(opt)
					})
					$('#paciente').val(obj && obj.pacienteID?obj.pacienteID:(oldPaciente?oldPaciente:'')).change()
				}
			})

			// vai preencher cmbModalidade
			$.ajax({
				type: 'POST',
				url: 'filtraAgendamento.php',
				dataType: 'json',
				data:{
					'tipoRequest': 'MODALIDADES'
				},
				success: function(response) {
					$('#modalidade').empty();
					$('#modalidade').append(`<option value=''>Selecione</option>`)
					
					response.forEach(item => {
						let opt = `<option selected value="${item.id}">${item.nome}</option>`
						$('#modalidade').append(opt)
					})
					$('#modalidade').val(obj && obj.modalidadeID?obj.modalidadeID:(oldModalidade?oldModalidade:'')).change()
				}
			})

			// vai preencher cmbServicos
			$.ajax({
				type: 'POST',
				url: 'filtraAgendamento.php',
				dataType: 'json',
				data:{
					'tipoRequest': 'SERVICOS',
					'modalidade': $('#modalidade').val()
				},
				success: function(response) {
					$('#servico').empty();
					$('#servico').append(`<option value=''>Selecione</option>`)
					response.forEach(item => {
						let opt = `<option selected data-val="${item.valor}" value="${item.id}">${item.nome}</option>`
						$('#servico').append(opt)
					})
					$('#servico').val(obj && obj.servicoID?obj.servicoID:(oldServico?oldServico:'')).change()
				}
			})

			// vai preencher cmbSituacao
			$.ajax({
				type: 'POST',
				url: 'filtraAgendamento.php',
				dataType: 'json',
				data:{
					'tipoRequest': 'SITUACAO'
				},
				success: async function(response) {
					$('#situacao').empty();
					$('#situacao').append(`<option value=''>Selecione</option>`)
					await response.forEach(item => {
						let opt = `<option selected value="${item.id}" data-chave='${item.chave}'>${item.nome}</option>`
						$('#situacao').append(opt)
					})
					let idAgendado=null;

					$('#situacao option').each(function(e){
						if($(this).data('chave') == 'AGENDADO'){
							idAgendado = $(this).val()
						}
					})
					$('#situacao').val(obj && obj.situacaoID?obj.situacaoID:idAgendado).change()
				}
			})

			// vai preencher cmbLocalAtendimento
			$.ajax({
				type: 'POST',
				url: 'filtraAgendamento.php',
				dataType: 'json',
				data:{
					'tipoRequest': 'LOCALATENDIMENTO'
				},
				success: function(response) {
					$('#localAtendimento').empty();
					$('#localAtendimento').append(`<option value=''>Selecione</option>`)
					response.forEach(item => {
						let opt = `<option selected value="${item.id}">${item.nome}</option>`
						$('#localAtendimento').append(opt)
					})
					$('#localAtendimento').val(obj && obj.localAtendimentoID?obj.localAtendimentoID:(oldLocalAtendimento?oldLocalAtendimento:'')).change()
				}
			})

			if(obj && obj.servicoID){
				// vai preencher cmbMedico
				$.ajax({
					type: 'POST',
					url: 'filtraAgendamento.php',
					dataType: 'json',
					data:{
						'tipoRequest': 'MEDICOS',
						'servico': obj.servicoID,
						'data': '',
						'hora': '',
					},
					success: function(response){
						$('#medico').empty()
						$('#medico').append(`<option value=''>Selecione</option>`)
						response.forEach(item => {
							let opt = `<option selected value="${item.id}">${item.nome}</option>`
							$('#medico').append(opt)
						})
						$('#medico').val(obj && obj.medicoID?obj.medicoID:(oldMedico?oldMedico:'')).change()
					}
				})
			}else{
				$('#medico').empty()
				$('#medico').append(`<option value=''>Selecione</option>`)

				$('#localAtendimento').empty();
				$('#localAtendimento').append(`<option value=''>Selecione</option>`)
			}
		}

		function getFilters(obj){
			$.ajax({
				type: 'POST',
				url: 'filtraAgendamento.php',
				dataType: 'json',
				data:{
					'tipoRequest': 'MEDICOS'
				},
				success: function(response){
					$('#medicoConfig').empty()
					$('#medicoConfig').append(`<option value=''>Selecione</option>`)
					response.forEach(item => {
						let opt = obj && obj.profissional?(obj.profissional == item.id?`<option selected value="${item.id}">${item.nome}</option>`:`<option value="${item.id}">${item.nome}</option>`):
						`<option value="${item.id}">${item.nome}</option>`
						$('#medicoConfig').append(opt)
					})
				}
			})
			$.ajax({
				type: 'POST',
				url: 'filtraAgendamento.php',
				dataType: 'json',
				data:{
					'tipoRequest': 'SITUACAO'
				},
				success: function(response){
					$('#statusFiltro').empty()
					$('#statusFiltro').append(`<option value=''>Todos</option>`)
					response.forEach(item => {
						let opt = obj && obj.status?(obj.status == item.id?`<option selected value="${item.id}">${item.nome}</option>`:`<option value="${item.id}">${item.nome}</option>`):`<option value="${item.id}">${item.nome}</option>`
						$('#statusFiltro').append(opt)
					})
				}
			})
			$.ajax({
				type: 'POST',
				url: 'filtraAgendamento.php',
				dataType: 'json',
				data:{
					'tipoRequest': 'LOCALATENDIMENTO'
				},
				success: function(response) {
					$('#localFiltro').empty()
					$('#localFiltro').append(`<option value=''>Todos</option>`)
					response.forEach(item => {
						let id = obj && obj.local? obj.local:null
						let opt = id == item.id?`<option selected value="${item.id}">${item.nome}</option>`:`<option value="${item.id}">${item.nome}</option>`
						$('#localFiltro').append(opt)
					})
				}
			})
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
				<div class="card">
					<div class="card-header header-elements-inline">
						<div class="col-lg-12 row p-0 m-0">
							<!-- titulo -->
							<div class="col-lg-2"><h3 class="card-title">Agendamentos</h3></div>
							<div class="col-lg-6">
								<select id="profissional" name="profissional[]" class="form-control multiselect-select-all-filtering" multiple="multiple">
									<?php
										foreach($rowProfissionais as $item){
											echo "<option value='$item[id]' selected>$item[nome] - $item[cbo] - $item[profissao]</option>";
										}
									?>
								</select>
							</div>
							
							<div class="col-lg-4 p-0 m-0 text-right">
								<?php
									$arrayPerfisAcesso = [
										'SUPER',
										'ADMINISTRADOR',
										'ADMINISTRADOR2'
									];
									if(in_array($_SESSION['PerfiChave'], $arrayPerfisAcesso)){
										echo "<i id='configUnidade' class='fab-icon-open icon-calendar2 px-2' style='cursor: pointer; font-size:20px;'></i>";
									}
								?>
								<i id="config" class="fab-icon-open icon-gear px-2" style="cursor: pointer; font-size:20px;"></i>
								<i id="filtro" class="fab-icon-open icon-filter3 pr-2" style="cursor: pointer; font-size:20px;"></i>
								<button id="novoAgendamento" class='btn btn-principal'>Novo Agendamento</button>
								<a href="#collapse-imprimir-relacao" class="btn bg-slate-700 btn-icon" role="button" data-toggle="collapse" data-placement="bottom" data-container="body">
									<i class="icon-printer2"></i>
								</a>
							</div>
						</div>						
					</div>
					
					<div class="card-body">
						<div class="row">
							<div class="col-md-12">
								<div id="calendarioagendamento"></div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!--Modal Editar Situação-->
			<div id="page-modal-agendamento" class="custon-modal">
				<div class="custon-modal-container" style="max-width: 900px;">
					<div class="card custon-modal-content">
						<div class="custon-modal-title mb-2" style="background-color: #466d96; color: #ffffff">
							<p id="tituloModal" class="h5">Novo Agendamento</p>
							<i id="modalAgendamento-close-x" class="fab-icon-open icon-cross2 p-3" style="cursor: pointer"></i>
						</div>
						<div class="px-0">
							<input type="hidden" id="idAgendamento" name="idAgendamento" value="">
							<form id="formAgendamentoNovo" class="form-validate-jquery">
								<div class="col-lg-12 p-0">
									<!-- linha 1 -->
									<div class="col-lg-12 row p-2 m-0">
										<div class="col-lg-2">Data <span class="text-danger">*</span></div>
										<div class="col-lg-2">Início <span class="text-danger">*</span></div>
										<div class="col-lg-2">Fim <span class="text-danger">*</span></div>
										<div class="col-lg-4">Paciente <span class="text-danger">*</span></div>
										<div class="col-lg-2">Modalidade <span class="text-danger">*</span></div>

										<div class="col-lg-2">
											<input type="date" id="inputData" name="inputData" class="form-control" required value="<?php echo date('Y-m-d')?>">
										</div>
										<div class="col-lg-2">
											<input type="time" id="inputHora" name="inputHora" class="form-control" required value="<?php echo date('H:i')?>">
										</div>
										<div class="col-lg-2">
											<input type="time" id="inputHoraFim" name="inputHoraFim" class="form-control" required value="<?php echo date('H:i')?>">
										</div>
										<div class="col-lg-4 p-0">
											<div class="row col-lg-12 p-0 m-0">
												<div class="col-lg-9">
													<select id="paciente" name="paciente" readonly class="form-control select-search" required></select>
												</div>
												<div class="col-lg-3">
													<span class="action btn btn-principal legitRipple" id="addPaciente" style="user-select: none;">
														<i class="fab-icon-open icon-add-to-list p-0" style="cursor: pointer; color: black"></i>
													</span>
												</div>
											</div>
										</div>
										<div class="col-lg-2">
											<select id="modalidade" name="modalidade" class="select-search" required></select>
										</div>
									</div>

									<!-- linha 2 -->
									<div class="col-lg-12 row p-2 m-0">
										<div class="col-lg-12 my-3 text-black-50">
											<h5 class="mb-0 font-weight-semibold">Serviços</h5>
										</div>

										<div class="col-lg-12 mb-2 row m-0 p-0">
											<!-- titulos -->
											<div class="col-lg-4">
												<label>Serviços <span class="text-danger">*</span></label>
											</div>
											<div class="col-lg-4">
												<label>Médicos <span class="text-danger">*</span></label>
											</div>
											<div class="col-lg-4">
												<label>Local do Atendimento <span class="text-danger">*</span></label>
											</div>

											<!-- campos -->
											<div class="col-lg-4">
												<select id="servico" name="servico" class="select-search" required>
													<option value=''>Selecione</option>
												</select>
											</div>
											<div class="col-lg-4">
												<select id="medico" name="medico" class="select-search" required>
													<option value=''>Selecione</option>
												</select>
											</div>
											<div class="col-lg-4">
												<select id="localAtendimento" name="localAtendimento" class="form-control form-control-select2" required>
													<option value=''>Selecione</option>
												</select>
											</div>
										</div>
									</div>

									<!-- linha 3 -->
									<div class="h5 col-lg-12 text-right pr-3">
										<label>Valor Total: </label>
										<label id="valorProduto">R$ 0,00</label>
									</div>

									<!-- linha 4 -->
									<div class="col-lg-12 p-2 m-0">
										<div class="col-lg-12">Observações</div>
										<div class="col-lg-12">
											<textarea id="textObservacao" name="textObservacao" class="form-control" rows="4" cols="4" maxLength="800" placeholder="Digite aqui as observações..."></textarea>
											<small class="text-muted form-text">
												Máx. 800 caracteres<br>
												<span id="caracteresInputObservacao"></span>
											</small>
										</div>
									</div>

									<!-- linha 5 -->
									<div class="col-lg-4">
										<div class="col-lg-12">Situação <span class="text-danger">*</span></div>

										<div class="col-lg-12">
											<select id="situacao" name="situacao" class="form-control form-control-select2" required></select>
										</div>
									</div>
									<div class="col-lg-8"></div>

									<!-- linha 6 -->
									<div class="col-lg-12 row p-3 m-0" id="agendaRecorrenteCheckContainer">
										<input type="checkbox" id="agendaRecorrenteCheck" name="agendaRecorrenteCheck">
										<div class="pl-2">Evento Recorrente</div>
									</div>

									<!-- linha 7 -->
									<div id="agendaRecorrente" class="col-lg-12 row d-none">
										<div class="col-lg-12 dropdown-divider mt-2"></div>
										<!-- linha 1 -->
										<div class="col-lg-12 row p-2 m-0">
											<div class="col-lg-6">Repete a cada <span class="text-danger">*</span></div>
											<div class="col-lg-3 mb-1">Quantidade</div>
											<div class="col-lg-3"></div>

											<div class="col-lg-6">
												<select id="repeticaoAgendamento" class="select-search" required>
													<option value=''>Selecione</option>
													<option value='1S'>1 Semana</option>
													<option value='2S'>2 Semanas</option>
													<option value='3S'>3 Semanas</option>
													<option value='4S'>4 Semanas</option>
													<!-- <option value='1M'>1 Mês</option>
													<option value='2M'>2 Meses</option>
													<option value='3M'>3 Meses</option>
													<option value='4M'>4 Meses</option> -->
												</select>
											</div>
											<div class="col-lg-3">
												<input type="number" id="quantidadeRecorrenciaAgendamento" class="form-control" required value="0" max="99">
											</div>
											<div class="col-lg-3"></div>
										</div>

										<!-- linha 2 -->
										<div class="col-lg-12 row p-2 m-0">
											<div class="col-lg-6 mb-1">Dias úteis </div>
											<div class="col-lg-6"></div>

											<div class="col-lg-2">
												<input class="diasUpdate" id="segundaAg" type="checkbox">
												Segunda-Feira
											</div>
											<div class="col-lg-2">
												<input class="diasUpdate" id="tercaAg" type="checkbox">
												Terça-Feira
											</div>
											<div class="col-lg-2">
												<input class="diasUpdate" id="quartaAg" type="checkbox">
												Quarta-Feira
											</div>
											<div class="col-lg-2">
												<input class="diasUpdate" id="quintaAg" type="checkbox">
												Quinta-Feira
											</div>
											<div class="col-lg-2">
												<input class="diasUpdate" id="sextaAg" type="checkbox">
												Sexta-Feira
											</div>
											<div class="col-lg-2"></div>
										</div>

										<!-- linha 3 -->
										<div class="col-lg-12 row p-2 m-0">
											<div class="col-lg-6 mb-1">Final de semana</div>
											<div class="col-lg-6"></div>

											<div class="col-lg-2">
												<input class="diasUpdate" id="sabadoAg" type="checkbox">
												Sábado
											</div>
											<div class="col-lg-2">
												<input class="diasUpdate" id="domingoAg" type="checkbox">
												Domingo
											</div>
											<div class="col-lg-8"></div>
										</div>

										<!-- linha 4 -->
										<div class="col-lg-12 row p-2 m-0">
											<div class="col-lg-12 mb-1">Término da recorrência</div>

											<div class="col-lg-4 mb-1">Data Final</div>
											<div class="col-lg-8"></div>

											<div class="col-lg-4">
												<input type="date" id="dataRecorrenciaAgendamento" class="form-control" required value="">
											</div>
											<div class="col-lg-8"></div>
										</div>
									</div>

									<!-- linha X -->
									<div class="col-lg-12 py-3" style="margin-top: -5px;">
										<div class="col-lg-4">
											<button class="btn btn-lg btn-principal" id="inserirAgendamento">Salvar</button>
										</div>
									</div>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>

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
													<input id="nomeNew" name="nomeNew" type="text" class="form-control" placeholder="Nome completo" required>
												</div>
												<div class="col-lg-6">
													<input id="nomeSocialNew" name="nomeSocialNew" type="text" class="form-control" placeholder="Nome Social">
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
													<input id="telefoneNew" name="telefoneNew" type="text" class="form-control" placeholder="Telefone" data-mask="(99) 9999-9999" required>
												</div>
												<div class="col-lg-2">
													<input id="celularNew" name="celularNew" type="text" class="form-control" placeholder="Celular" data-mask="(99) 99999-9999" required>
												</div>
												<div class="col-lg-4">
													<input id="emailNew" name="emailNew" type="text" class="form-control" placeholder="E-mail" required>
												</div>
											</div>

											<div class="card card-collapsed">
												<div class="card-header header-elements-inline">
													<h3 class="card-title">Outros dados</h3>
													<div class="header-elements">
														<div class="list-icons">
															<a class="list-icons-item" data-action="collapse"></a>
															<!-- <a href="perfil.php" class="list-icons-item" data-action="reload"></a> -->
															<!--<a class="list-icons-item" data-action="remove"></a>-->
														</div>
													</div>
												</div>
												<div class="col-lg-12 mb-4 row">
													<!-- titulos -->
													<div class="col-lg-4">
														<label>CPF</label>
													</div>
													<div class="col-lg-4">
														<label>CNS</label>
													</div>
													<div class="col-lg-4">
														<label>RG</label>
													</div>

													<!-- campos -->
													<div class="col-lg-4">
														<input id="cpfNew" name="cpfNew" type="text" class="form-control" placeholder="CPF" data-mask="999.999.999-99">
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
										</div>
										<div class="text-right m-2">
											<button id="salvarPacienteModal" class="btn btn-principal" role="button">Confirmar</button>
										</div>
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div id="page-modal-filtro" class="custon-modal">
				<div class="custon-modal-container" style="max-width: 900px;">
					<div class="card custon-modal-content">
						<div class="custon-modal-title mb-2" style="background-color: #466d96; color: #ffffff">
							<p id="tituloModal" class="h5">Filtro</p>
							<i id="modalFiltro-close-x" class="fab-icon-open icon-cross2 p-3" style="cursor: pointer"></i>
						</div>
						<div class="px-0">
							<form id="formFiltro" class="form-validate-jquery">
								<div class="col-lg-12 p-0">
									<!-- linha 1 -->
									<div class="col-lg-12 row p-2 m-0">
										<div class="col-lg-6">Status</div>
										<div class="col-lg-6">Local de Atendimento</div>

										<div class="col-lg-6">
											<select id="statusFiltro" name="statusFiltro" class="select-search" required>
												<option value=''>Todos</option>
											</select>
										</div>
										<div class="col-lg-6">
											<select id="localFiltro" name="localFiltro" class="select-search" required>
												<option value=''>Todos</option>
											</select>
										</div>
									</div>

									<!-- linha 2 -->
									<div class="col-lg-12 row p-2 m-0">
										<div class="col-lg-6">De</div>
										<div class="col-lg-6">Até</div>

										<div class="col-lg-6">
											<input id="dataFiltroDe" type="date" class="form-control">
										</div>
										<div class="col-lg-6">
											<input id="dataFiltroAte" type="date" class="form-control">
										</div>
									</div>

									<!-- linha X -->
									<div class="col-lg-12 py-3" style="margin-top: -5px;">
										<div class="col-lg-4">
											<button class="btn btn-lg btn-principal" id="filtrarAgendamento">Aplicar</button>
										</div>
									</div>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>

			<div id="page-modal-configUnidade" class="custon-modal">
				<div class="custon-modal-container" style="max-width: 900px;">
					<div class="card custon-modal-content">
						<div class="custon-modal-title mb-2" style="background-color: #466d96; color: #ffffff">
							<p id="tituloModal" class="h5">Configuração da Agenda</p>
							<i id="modalConfigUnidade-close-x" class="fab-icon-open icon-cross2 p-3" style="cursor: pointer"></i>
						</div>
						<div class="px-0">
							<form id="formConfig" class="form-validate-jquery">
								<div class="col-lg-12 p-0">
									<!-- linha 1 -->
									<div class="col-lg-12 row p-2 m-0">
										<div class="col-lg-6 mb-1">Dias de Funcionamento da Unidade</div>
										<div class="col-lg-6"></div>

										<div class="col-lg-1">
											<input id="segundaUnidade" name="segundaUnidade" type="checkbox">
											Seg
										</div>
										<div class="col-lg-1">
											<input id="tercaUnidade" name="tercaUnidade" type="checkbox">
											Ter
										</div>
										<div class="col-lg-1">
											<input id="quartaUnidade" name="quartaUnidade" type="checkbox">
											Qua
										</div>
										<div class="col-lg-1">
											<input id="quintaUnidade" name="quintaUnidade" type="checkbox">
											Qui
										</div>
										<div class="col-lg-1">
											<input id="sextaUnidade" name="sextaUnidade" type="checkbox">
											Sex
										</div>
										<div class="col-lg-1">
											<input id="sabadoUnidade" name="sabadoUnidade" type="checkbox">
											Sáb
										</div>
										<div class="col-lg-1">
											<input id="domingoUnidade" name="domingoUnidade" type="checkbox">
											Dom
										</div>
										<div class="col-lg-5"></div>
									</div>

									<!-- linha 2 -->
									<div class="col-lg-12 row p-2 m-0">
										<div class="col-lg-6">Observacao</div>
										<div class="col-lg-6"></div>

										<div class="col-lg-6">
											<input type="text" id="observacaoUnidade" name="observacaoUnidade" class="form-control">
										</div>
										<div class="col-lg-6"></div>
									</div>

									<!-- linha 3 -->
									<div class="col-lg-12 row p-2 m-0">
										<div class="col-lg-4">Horário de Funcionamento <span class="text-danger">*</span></div>
										<div class="col-lg-4">Horário de Almoço <span class="text-danger">*</span></div>
										<div class="col-lg-4">Intervalo Agenda <span class="text-danger">*</span></div>
									</div>

									<!-- linha 4 -->
									<div class="col-lg-12 row p-2 m-0">
										<div class="col-lg-2 text-grey-300">Abertura</div>
										<div class="col-lg-2 text-grey-300">Fechamento</div>
										<div class="col-lg-2 text-grey-300">Início</div>
										<div class="col-lg-2 text-grey-300">Fim</div>
										<div class="col-lg-4 text-grey-300">Intervalo</div>

										<div class="col-lg-2">
											<input type="time" id="inputHoraAberturaUnidade" name="inputHoraAberturaUnidade" class="form-control" required value="<?php echo date('H:i')?>">
										</div>
										<div class="col-lg-2">
											<input type="time" id="inputHoraFechamentoUnidade" name="inputHoraFechamentoUnidade" class="form-control" required value="<?php echo date('H:i')?>">
										</div>
										<div class="col-lg-2">
											<input type="time" id="inputHoraInicioUnidade" name="inputHoraInicioUnidade" class="form-control" required value="<?php echo date('H:i')?>">
										</div>
										<div class="col-lg-2">
											<input type="time" id="inputHoraFimUnidade" name="inputHoraFimUnidade" class="form-control" required value="<?php echo date('H:i')?>">
										</div>
										<div class="col-lg-4">
											<select id="inputHoraIntervaloUnidade" name="inputHoraIntervaloUnidade" class="select-search" required>
												<option value=''>Selecione</option>
												<option value='30'>30 Minutos</option>
												<option value='35'>35 Minutos</option>
												<option value='40'>40 Minutos</option>
												<option value='45'>45 Minutos</option>
												<option value='50'>50 Minutos</option>
												<option value='55'>55 Minutos</option>
												<option value='60'>60 Minutos</option>
											</select>
										</div>
									</div>

									<!-- linha X -->
									<div class="col-lg-12 py-3" style="margin-top: -5px;">
										<div class="col-lg-4">
											<button class="btn btn-lg btn-principal" id="salvarConfigUnidade">Salvar</button>
										</div>
									</div>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>

			<div id="page-modal-configRelacao" class="custon-modal">
				<div class="custon-modal-container" style="width: 90%;">
					<div class="card custon-modal-content">
						<div class="custon-modal-title mb-2" style="background-color: #466d96; color: #ffffff">
							<p id="tituloModal" class="h5">Bloqueios</p>
							<i id="configRelacao-close-x" class="fab-icon-open icon-cross2 p-3" style="cursor: pointer"></i>
						</div>
						<div class="px-0">
							<div class="col-lg-12 p-0">
								<table class="table" id="relacaoBloqueiosTable">
									<thead>
										<tr class="bg-slate text-left">
											<th>Início</th>
											<th>Fim</th>
											<th>Título</th>
											<th>Profissional</th>
											<th>Recorrente</th>
											<th>Repetição</th>
											<th class="text-center">Ações</th>
										</tr>
									</thead>
									<tbody>

									</tbody>
								</table>
							</div>
							<div class="col-lg-12 py-3" style="margin-top: -5px;">
								<div class="col-lg-4">
									<button id="novoBloqueio" class='btn btn-principal'>Novo Bloqueio</button>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div id="page-modal-config" class="custon-modal">
				<div class="custon-modal-container" style="max-width: 900px;">
					<div class="card custon-modal-content">
						<div class="custon-modal-title mb-2" style="background-color: #466d96; color: #ffffff">
							<p id="tituloModal" class="h5">Agendar Evento</p>
							<i id="modalConfig-close-x" class="fab-icon-open icon-cross2 p-3" style="cursor: pointer"></i>
						</div>
						<div class="px-0">
							<form id="formConfig" class="form-validate-jquery">
								<input id="typeInsert" type='hidden' type="text" value="NEW">
								<div class="col-lg-12 p-0">
									<!-- linha 1 -->
									<div class="col-lg-12 row p-2 m-0">
										<div class="col-lg-6">Profissional <span class="text-danger">*</span></div>
										<div class="col-lg-6"></div>

										<div class="col-lg-6">
											<select id="medicoConfig" name="medicoConfig" class="select-search" required>
												<option value=''>Selecione</option>
											</select>
										</div>
										<div class="col-lg-6"></div>
									</div>

									<!-- linha 2 -->
									<div class="col-lg-12 row p-2 m-0">
										<div class="col-lg-6">Título Bloqueio <span class="text-danger">*</span></div>
										<div class="col-lg-6">Justificativa</div>

										<div class="col-lg-6">
											<input type="text" id="bloqueio" name="bloqueio" class="form-control" required>
										</div>
										<div class="col-lg-6">
											<input type="text" id="justificativa" name="justificativa" class="form-control">
										</div>
									</div>

									<!-- linha 3 -->
									<div class="col-lg-12 row p-2 m-0">
										<div class="col-lg-3">Data Início<span class="text-danger">*</span></div>
										<div class="col-lg-2">Hora Início<span class="text-danger">*</span></div>
										<div class="col-lg-3">Data Fim<span class="text-danger">*</span></div>
										<div class="col-lg-2">Hora Fim<span class="text-danger">*</span></div>
										<div class="col-lg-2"></div>

										<div class="col-lg-3">
											<input type="date" id="inputDataInicioBloqueio" name="inputDataInicioBloqueio" class="form-control" required value="<?php echo date('Y-m-d')?>">
										</div>
										<div class="col-lg-2">
											<input type="time" id="inputHoraInicioBloqueio" name="inputHoraInicioBloqueio" class="form-control" required value="<?php echo date('H:i')?>">
										</div>

										<div class="col-lg-3">
											<input type="date" id="inputDataFimBloqueio" name="inputDataFimBloqueio" class="form-control" required value="<?php echo date('Y-m-d')?>">
										</div>
										<div class="col-lg-2">
											<input type="time" id="inputHoraFimBloqueio" name="inputHoraFimBloqueio" class="form-control" required value="<?php echo date('H:i')?>">
										</div>
										<div class="col-lg-2">
											<button class="btn btn-secondary" id="selecionarCalendario">Selecionar</button>
										</div>
									</div>

									<!-- linha 4 -->
									<div class="col-lg-12 row p-3 m-0">
										<input type="checkbox" id="recorrente" name="recorrente">
										<div class="pl-2">Bloqueio Recorrente</div>
									</div>

									<!-- cardOnOff -->
									<div id="cardRecorrend" class="d-none">
										<!-- linha 1 -->
										<div class="col-lg-12 row p-2 m-0">
											<div class="col-lg-6">Repete a cada <span class="text-danger">*</span></div>
											<div class="col-lg-3 mb-1">Quantidade</div>
											<div class="col-lg-3"></div>

											<div class="col-lg-6">
												<select id="repeticao" name="repeticao" class="select-search" required>
													<option value=''>Selecione</option>
													<option value='1S'>1 Semana</option>
													<option value='2S'>2 Semanas</option>
													<option value='3S'>3 Semanas</option>
													<option value='4S'>4 Semanas</option>
												</select>
											</div>
											<div class="col-lg-3">
												<input type="number" id="quantidadeRecorrencia" name="quantidadeRecorrencia" class="form-control" required value="0" max="99">
											</div>
											<div class="col-lg-3"></div>
										</div>

										<!-- linha 2 -->
										<div class="col-lg-12 row p-2 m-0">
											<div class="col-lg-6 mb-1">Dias úteis </div>
											<div class="col-lg-6"></div>

											<div class="col-lg-2">
												<input id="segunda" name="segunda" type="checkbox">
												Segunda-Feira
											</div>
											<div class="col-lg-2">
												<input id="terca" name="terca" type="checkbox">
												Terça-Feira
											</div>
											<div class="col-lg-2">
												<input id="quarta" name="quarta" type="checkbox">
												Quarta-Feira
											</div>
											<div class="col-lg-2">
												<input id="quinta" name="quinta" type="checkbox">
												Quinta-Feira
											</div>
											<div class="col-lg-2">
												<input id="sexta" name="sexta" type="checkbox">
												Sexta-Feira
											</div>
											<div class="col-lg-2"></div>
										</div>

										<!-- linha 3 -->
										<div class="col-lg-12 row p-2 m-0">
											<div class="col-lg-6 mb-1">Final de semana</div>
											<div class="col-lg-6"></div>

											<div class="col-lg-2">
												<input id="sabado" name="sabado" type="checkbox">
												Sábado
											</div>
											<div class="col-lg-2">
												<input id="domingo" name="domingo" type="checkbox">
												Domingo
											</div>
											<div class="col-lg-8"></div>
										</div>

										<!-- linha 4 -->
										<div class="col-lg-12 row p-2 m-0">
											<div class="col-lg-12 mb-1">Término da recorrência</div>

											<div class="col-lg-4 mb-1">Data Final</div>
											<div class="col-lg-8"></div>

											<div class="col-lg-4">
												<input type="date" id="dataRecorrencia" name="dataRecorrencia" class="form-control" required value="">
											</div>
											<div class="col-lg-8"></div>
										</div>
									</div>

									<!-- linha X -->
									<div class="col-lg-12 py-3" style="margin-top: -5px;">
										<div class="col-lg-4">
											<button class="btn btn-lg btn-principal" id="salvarEvento">Incluir</button>
										</div>
									</div>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>

			<?php include_once("footer.php"); ?>
		</div>
		<!-- /main content -->

	</div>
	<!-- /page content -->

	<?php include_once("alerta.php"); ?>

</body>

</html>
