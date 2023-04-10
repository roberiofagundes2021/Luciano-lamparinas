<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Novo Agendamento';

// vai limpar todos os dados da sessão utilizadas em agendamento
$_SESSION['SERVICOS'] = [];

include('global_assets/php/conexao.php');

$dataHoje = date("Y-m-d");

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Agendamentos</title>

	<?php include_once("head.php"); ?>

	<script src="global_assets/js/plugins/loaders/blockui.min.js"></script>
	<script src="global_assets/js/plugins/ui/ripple.min.js"></script>
	<script src="global_assets/js/plugins/ui/moment/moment.min.js"></script>
	<script src="global_assets/js/plugins/pickers/daterangepicker.js"></script>
	<script src="global_assets/js/plugins/pickers/anytime.min.js"></script>
	<script src="global_assets/js/plugins/pickers/pickadate/picker.js"></script>
	<script src="global_assets/js/plugins/pickers/pickadate/picker.date.js"></script>
	<script src="global_assets/js/plugins/pickers/pickadate/picker.time.js"></script>
	<script src="global_assets/js/plugins/pickers/pickadate/legacy.js"></script>
	<script src="global_assets/js/plugins/notifications/jgrowl.min.js"></script>

	<!-- Theme JS files -->
	<script src="global_assets/js/demo_pages/form_select2.js"></script>
	<script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>
    <script src="global_assets/js/plugins/tables/datatables/datatables.min.js"></script>
    <script src="global_assets/js/plugins/tables/datatables/extensions/responsive.min.js"></script>
    <script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
	<script src="global_assets/js/plugins/editors/summernote/summernote.min.js"></script>

	<!-- Plugin para corrigir a ordenação por data. Caso a URL dê problema algum dia, salvei esses 2 arquivos na pasta global_assets/js/lamparinas -->
	<script type="text/javascript" language="javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.8.4/moment.min.js"></script>
	<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/plug-ins/1.10.10/sorting/datetime-moment.js"></script>	

	<!-- Modal -->
	<script src="global_assets/js/plugins/notifications/bootbox.min.js"></script>
    
    <!-- Validação -->
	<script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
	<script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
	<script src="global_assets/js/demo_pages/form_validation.js"></script>
	
	<script type="text/javascript" >
		$(document).ready(function() {
			
			$('#servicoTable').hide()
			alteraSituacao('AGENDADO')
			getCmbs()
			
			$('#data').val('<?php echo $dataHoje ?>')

			$('#salvarPaciente').on('click', function(e){
				e.preventDefault()

				let nomePaciente = $('#nomePaciente').val()
				let telefone = $('#telefone').val()
				let celular = $('#celular').val()
				let email = $('#email').val()
				let observacao = $('#observacao').val()

				let menssageError = ''

				switch(menssageError){
					case nomePaciente: menssageError = 'Informe o nome';break;
					case telefone || celular: menssageError = 'Informe o telefone ou celular';break;
					case email: menssageError = 'Informe o E-mail';break;
					default: menssageError = '';break;
				}

				if(menssageError){
					alerta('Campo Obrigatório!', menssageError, 'error')
					return
				}

				$.ajax({
					type: 'POST',
					url: 'filtraAgendamento.php',
					dataType: 'json',
					data:{
						'tipoRequest': 'ADDPACIENTENOVO',
						'nomePaciente': nomePaciente,
						'telefone': telefone,
						'celular': celular,
						'email': email,
						'observacao': observacao
					},
					success: function(response) {
						if(response.status  == 'success'){
							$('#paciente').empty();
							$('#paciente').append(`<option value=''>Selecione</option>`)
							response.array.forEach(item => {
								$('#paciente').append(`<option ${item.isSelected} value="${item.id}">${item.nome}</option>`)
							})
							alerta(response.titulo, response.menssagem, response.status)
							$('#page-modal-paciente').fadeOut();
						} else {
							alerta(response.titulo, response.menssagem, response.status)
						}
					}
				});
			})

			$('#medico').on('change', function(){
				let iMedico = $(this).val()

				if(!iMedico){
					setHoraProfissional()
					setDataProfissional()
					return
				}
				$.ajax({
					type: 'POST',
					url: 'filtraAgendamento.php',
					dataType: 'json',
					data:{
						'tipoRequest': 'LOCALATENDIMENTO',
						'iMedico' : iMedico
					},
					success: function(response) {

						$('#localAtendimento').empty();
						if (response.length !== 0 ) {
							$('#localAtendimento').append(`<option value=''>Selecione</option>`);			
							response.forEach(item => {
								let opt = `<option value="${item.id}">${item.nome}</option>`
								$('#localAtendimento').append(opt)
							})

							$('#localAtendimento').focus()
						}else{
							alerta('Sem Locais Disponíveis', 'Não existe agenda disponível para esse serviço nos próximos dias para o profissional selecionado.','error')
							$('#localAtendimento').append(`<option value=''>Sem Locais Disponíveis</option>`)	
						}
						
					}
				});
			});

			$('#localAtendimento').on('change', function() {

				let localAtend = $(this).val();
				let iMedico = $('#medico').val();

				$.ajax({
					type: 'POST',
					url: 'filtraAgendamento.php',
					dataType: 'json',
					data:{
						'tipoRequest': 'SETDATAPROFISSIONAL',
						'iMedico' : iMedico,
						'localAtend': localAtend,
					},
					success: function(response) {
						if(response.status == 'success'){
							setDataProfissional(response.arrayData)
							$('#dataAtendimento').focus()
						} else {
							alerta(response.titulo, response.menssagem, response.status)
						}
					}
				});
			});

			$('#servico').on('change', function(e){
				$.ajax({
					type: 'POST',
					url: 'filtraAgendamento.php',
					dataType: 'json',
					data:{
						'tipoRequest': 'MEDICOS',
						'servico': $(this).val()
					},
					success: function(response) {
						setDataProfissional()
						setHoraProfissional()
						$('#medico').empty();
						$('#localAtendimento').empty();
						$('#medico').append(`<option value=''>Selecione</option>`)
						$('#localAtendimento').append(`<option value=''>Selecione</option>`)			

						response.forEach(item => {
							let opt = `<option value="${item.id}">${item.nome}</option>`
							$('#medico').append(opt)
						})
						$('#medico').focus()
					}
				});
			})

			$('#inserirServico').on('click', function(e){
				e.preventDefault()
				// vai preencher a tabela servico e inseri-lo no array de servicos
				let menssageError = ''
				let servico = $('#servico').val()
				let medico = $('#medico').val()
				let dataAtendimento = $('#dataAtendimento').val()
				let horaAtendimento = $('#horaAtendimento').val()
				let localAtendimento = $('#localAtendimento').val()

				switch(menssageError){
					case servico: menssageError = 'informe o serviço'; $('#servico').focus();break;
					case medico: menssageError = 'informe o médico'; $('#medico').focus();break;
					case dataAtendimento: menssageError = 'informe uma data'; $('#dataAtendimento').focus();break;
					case horaAtendimento: menssageError = 'informe o horário'; $('#horaAtendimento').focus();break;
					case localAtendimento: menssageError = 'informe o local de atendimento'; $('#localAtendimento').focus();break;
					default: menssageError = ''; break;
				}

				if(menssageError){
					alerta('Campo Obrigatório!', menssageError, 'error')
					return
				}

				$.ajax({
					type: 'POST',
					url: 'filtraAgendamento.php',
					dataType: 'json',
					data:{
						'tipoRequest': 'ADDSERVICO',
						'servico': servico,
						'medico': medico,
						'data': dataAtendimento,
						'hora': horaAtendimento,
						'local': localAtendimento
					},
					success: function(response) {
						if(response.status  == 'success'){
							resetServicoCmb()
							checkServicos()
							alerta(response.titulo, response.menssagem, response.status)
						} else {
							alerta(response.titulo, response.menssagem, response.status)
						}
					}
				});
			})

			$('#salvarAgendamento').on('click', function(e){
				e.preventDefault();
				let msg = ''
				let data = $('#data').val()
				let paciente = $('#paciente').val()
				let modalidade = $('#modalidade').val()
				let observacao = $('#observacao').val()
				let cmbSituacao = $('#cmbSituacao').val()

				switch(msg){
					case data: msg = 'informe uma data';$('#data').focus();break
					case paciente: msg = 'informe um paciente';$('#paciente').focus();break
					case modalidade: msg = 'Informe a modalidade';$('#modalidade').focus();break
					case cmbSituacao: msg = 'Informe a situação';$('#cmbSituacao').focus();break
				}

				if(msg){
					alerta('Campo Obrigatório!', msg, 'error')
					return
				}

				let servicos = false;
				$('.servicoItem').each(function(index, item){
					servicos = true;
				})

				if(!servicos){
					alerta('Campo Obrigatório!', 'Informe pelo menos um serviço', 'error')
					$('#servico').focus()
					return
				}
				
				let dados = agendamento?{
						'tipoRequest': 'ADDAGENDAMENTO',
						'data': data,
						'isUpdate': agendamento?agendamento.AgendId:false,
						'paciente': paciente,
						'modalidade': modalidade,
						'observacao': observacao,
						'cmbSituacao': cmbSituacao
					}:{
						'tipoRequest': 'ADDAGENDAMENTO',
						'data': data,
						'paciente': paciente,
						'modalidade': modalidade,
						'observacao': observacao,
						'cmbSituacao': cmbSituacao
					}
				
				$.ajax({
					type: 'POST',
					url: 'filtraAgendamento.php',
					dataType: 'json',
					data: dados,
					success: function(response) {
						if(response.status == 'success'){
							window.location.href = 'agendamento.php'
							alerta(response.titulo, response.menssagem, response.status)
						}else{
							alerta(response.titulo, response.menssagem, response.status)
						}
					}
				});
			})

			$('#addPaciente').on('click', function(e){
				e.preventDefault();
				$('#page-modal-paciente').fadeIn();
			})

			$('#modal-close-x').on('click', ()=>{
				$('#iAtendimento').val('')
				$('#page-modal-paciente').fadeOut(200);
			})
		});
		// essa funcao vai checar se ja exixte algo no array de servicos
		// (para quando atualizar a página não sumir da grid)

		function checkServicos(idAgendamento){
			$.ajax({
				type: 'POST',
				url: 'filtraAgendamento.php',
				dataType: 'json',
				data:{
					'tipoRequest': 'CHECKSERVICO',
					'iAgendamento': idAgendamento
				},
				success: async function(response) {
					statusServicos = response.array.length?true:false;
					$('#dataServico').html('').show();
					let HTML = ''
					response.array.forEach(item => {
						let exc = `<a style='color: black; cursor:pointer' onclick='excluiServico(\"${item.id}\")' class='list-icons-item'><i class='icon-bin' title='Excluir Atendimento'></i></a>`;
						let acoes = `<div class='list-icons'>
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
					})
					if(statusServicos){
						$('#servicoTable').show();
					}else{
						$('#servicoTable').hide();
					}
					$('#servicoValorTotal').html(`R$ ${float2moeda(response.valorTotal)}`).show();
					$('#dataServico').html(HTML).show();
				}
			});
		}

		function setDataProfissional(arrayData){
			$('#dataAgenda').html('')
			$('#dataAgenda').html('<input id="dataAtendimento" name="dataAtendimento" type="text" class="form-control pickadate">')

			let array = arrayData?arrayData:undefined
			// Events
			$('#dataAtendimento').pickadate({
				weekdaysShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab'],
				monthsFull: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
				monthsShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
				today: '',
				close: '',
				clear: 'Limpar',
				labelMonthNext: 'Próximo',
				labelMonthPrev: 'Anterior',
				labelMonthSelect: 'Escolha um mês na lista suspensa',
				labelYearSelect: 'Escolha um ano na lista suspensa',
				selectMonths: false,
				selectYears: false,
				showMonthsShort: true,
				closeOnSelect: true,
				closeOnClear: true,
				formatSubmit: 'dd/mm/yyyy',
				format: 'dd/mm/yyyy',
				disable: array,
				min: array && array[1],
				onStart: function() {
					// console.log('onStart event')
				},
				onRender: function() {
					$('.picker__day').each(function(){
						let hasClass = !$(this).hasClass('picker__day--disabled') // verifica se NÃO está desabilitado...
						let hasSelected = $(this).hasClass('picker__day--selected') // verifica se está selecionado...

						if(hasClass){
							$(this).addClass((hasSelected?
							'':
							'font-weight-bold text-black border picker__day--highlighted'))
						}else{
							$(this).removeClass('picker__day--highlighted');//remover o destaque do dias que n estão disponíves para agendamento
						}
					})
				},
				onOpen: function() {
					$('.picker__day').each(function(){
						let hasClass = !$(this).hasClass('picker__day--disabled') // verifica se NÃO está desabilitado...
						let hasSelected = $(this).hasClass('picker__day--selected') // verifica se está selecionado...

						if(hasClass){
							$(this).addClass((hasSelected?
							'':
							'font-weight-bold text-black border picker__day--highlighted'))
						}else{
							$(this).removeClass('picker__day--highlighted');//remover o destaque do dias que n estão disponíves para agendamento
						}
					})
				},
				onClose: function() {
					// console.log('onClose event')
				},
				onStop: function() {
					// console.log('onStop event')
				},
				onSet: function(context) {
					let data = new Date(context.select).toLocaleString("pt-BR", {timeZone: "America/Bahia"});
					data = data.split(' ')[0]; // Formatando a string padrão: "dd/mm/yyyy HH:MM:SS" => "dd/mm/yyyy"
					let iMedico = $('#medico').val();

					$.ajax({
						type: 'POST',
						url: 'filtraAgendamento.php',
						dataType: 'json',
						data:{
							'tipoRequest': 'SETHORAPROFISSIONAL',
							'data': data,
							'iMedico': iMedico
						},
						success: function(response) {
							if(response.status == 'success'){
								setHoraProfissional(response.arrayHora, response.intervalo, response.horariosIndisp)
								$('#horaAtendimento').focus()
							} else {
								alerta(response.titulo, response.menssagem, response.status)
							}
						}
					});
					// $('#horaAtendimento').focus()
				}
			});
		}

		function setHoraProfissional(array,interv, horariosIndisp){
			$('#modalHora').html('').show();
			$('#modalHora').html('<input id="horaAtendimento" name="horaAtendimento" type="text" class="form-control pickatime-disabled">');
			hInicio = array ? array[1].from : undefined;
			hFim = array ? array[1].to : undefined;
			let intervalo = interv?interv:30
			// doc: https://amsul.ca/pickadate.js/time/
			$('#horaAtendimento').pickatime({
				// Regras
				interval: intervalo,
				disable: horariosIndisp,
				// disable: [
				// 	[1,30],
				// ],

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
					// let hora = context.select
					let data = $('#dataAtendimento').val()
					let hora = $('#horaAtendimento').val()

					// data: DD/MM/YYYY => MM/DD/YYYY
					data = `${data.split('/')[1]}/${data.split('/')[0]}/${data.split('/')[2]}`

					// dataHora: MM/DD/YYYY HH:MM:SS
					let dataHora = `${data} ${hora}`

					// somente para atribuir à variável "dataHora" um valor do tipo DataTime
					dataHora = new Date(dataHora).toLocaleString("pt-BR", {timeZone: "America/Bahia"});
				},
				onStart: undefined,
				onRender: undefined,
				onOpen: undefined,
				onClose: undefined,
				onStop: undefined,
			});
		}

		// exclui servico, o id é composto pelo idServico + idMedico + idLocal; EX.: 10#20#31

		function excluiServico(id){
			$.ajax({
				type: 'POST',
				url: 'filtraAgendamento.php',
				dataType: 'json',
				data:{
					'tipoRequest': 'EXCLUISERVICO',
					'id': id
				},
				success: function(response) {
					alerta(response.titulo, response.menssagem, response.status)
					checkServicos()
				}
			});
		}

		function getCmbs(){
			// vai preencher cmbPaciente
			$.ajax({
				type: 'POST',
				url: 'filtraAgendamento.php',
				dataType: 'json',
				data:{
					'tipoRequest': 'PACIENTES'
				},
				success: function(response) {
					$('#paciente').empty();
					$('#paciente').append(`<option value=''>Selecione</option>`)
					response.forEach(item => {
						let opt = `<option value="${item.id}">${item.id} - ${item.nome}</option>`
						$('#paciente').append(opt)
					})
				}
			});
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
						let opt = `<option value="${item.id}">${item.nome}</option>`
						$('#modalidade').append(opt)
					})
				}
			});
			// vai preencher cmbServicos
			$.ajax({
				type: 'POST',
				url: 'filtraAgendamento.php',
				dataType: 'json',
				data:{
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
		}

		function resetServicoCmb(){
			// vai preencher cmbServicos
			$.ajax({
				type: 'POST',
				url: 'filtraAgendamento.php',
				dataType: 'json',
				data:{
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
			$('#medico').empty();
			$('#medico').append(`<option value=''>Selecione</option>`)
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
						let opt = `<option value="${item.id}">${item.nome}</option>`
						$('#localAtendimento').append(opt)
					})
				}
			});
			$('#dataAtendimento').val('')
			$('#horaAtendimento').val('')
		}

		function alteraSituacao(situacao, element){
			$.ajax({
				type: 'POST',
				url: 'filtraAgendamento.php',
				dataType: 'json',
				data:{
					'tipoRequest': 'SITUACOES'
				},
				success: function(response) {
					$('#iAgendamento').val($(element).data('agendamento'))
					$('#cmbSituacao').empty()
					$('#cmbSituacao').append("<option selected value=''>Selecione</option>")
					response.forEach(item => {
						let opt = item.SituaChave === situacao? `<option selected value="${item.id}">${item.nome}</option>`:`<option value="${item.id}">${item.nome}</option>`
						$('#cmbSituacao').append(opt)
					})
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
					<div class="col-lg-12">
						<div class="card">
							<!-- dados do agendamento -->
							<div id="agendamento" class="formDados card-body" style="display: block; margin-top:-10px;" >
								<div class="card-header header-elements-inline" style="margin-left:-10px;">
									<h5 class='text-uppercase font-weight-bold'>CADASTRO DO AGENDAMENTO</h5>
								</div>

								<!-- esses inputs são para validar o tipo de operação que será feito no
								arquivo agendamentoFiltra.php -->
								<input type="hidden" id="tipoRequest" name="tipoRequest" value="NOVO" />

								<div class="col-lg-12 mb-4 row form-group">
									<!-- titulos -->
									<div class="col-lg-3">
										<label>Data do registro <span class="text-danger">*</span></label>
									</div>
									<div class="col-lg-6">
										<label>Paciente <span class="text-danger">*</span></label>
									</div>
									<div class="col-lg-3">
										<label>Modalidade <span class="text-danger">*</span></label>
									</div>

									<!-- campos -->
									<div class="col-lg-3">
										<input id="data" name="data" type="date" class="form-control" readonly>
									</div>
									<div class="col-lg-6 row m-0"> 
										<div class="col-lg-10">
											<select id="paciente" name="paciente" class="select-search">
												
											</select>
										</div>
										<div class="col-lg-2">
											<span class="action btn btn-principal legitRipple" id="addPaciente" style="user-select: none;">
												<i class="fab-icon-open icon-add-to-list p-0" style="cursor: pointer; color: black"></i>
											</span>
										</div>
									</div>
									<div class="col-lg-3">
										<select id="modalidade" name="modalidade" class="select-search">
											<!--  -->
										</select>
									</div>
								</div>

								<div class="col-lg-12 my-3 text-black-50">
									<h5 class="mb-0 font-weight-semibold">Serviços</h5>
								</div>

								<div class="col-lg-12 mb-2 row">
									<!-- titulos -->
									<div class="col-lg-2">
										<label>Serviços <span class="text-danger">*</span></label>
									</div>
									<div class="col-lg-2">
										<label>Médicos <span class="text-danger">*</span></label>
									</div>
									<div class="col-lg-2">
										<label>Local do Atendimento <span class="text-danger">*</span></label>
									</div>
									<div class="col-lg-3">
										<label>Data do Atendimento <span class="text-danger">*</span></label>
									</div>
									<div class="col-lg-2">
										<label>Horário <span class="text-danger">*</span></label>
									</div>					

									<!-- campos -->
									<div class="col-lg-2">
										<select id="servico" name="servico" class="select-search" required>
											<option value=''>Selecione</option>
										</select>
									</div>
									<div class="col-lg-2">
										<select id="medico" name="medico" class="select-search" required>
											<option value=''>Selecione</option>
										</select>
									</div>
									<div class="col-lg-2">
										<select id="localAtendimento" name="localAtendimento" class="form-control form-control-select2" required>
											<option value=''>Selecione</option>
										</select>
									</div>
									<div id="dataAgenda" class="col-lg-3 input-group">
										<input id="dataAtendimento" name="dataAtendimento" type="text" class="form-control pickadate">
									</div>
									<div id="modalHora" class="col-lg-2">										
										<input id="horaAtendimento" name="horaAtendimento" type="text" class="form-control pickatime-disabled">
									</div>	
									<div class="col-lg-1" style="margin-top: -5px;">
										<a class="btn btn-lg btn-principal" id="inserirServico">Incluir</a>
									</div>
								</div>

								<div class="col-lg-12 mt-2">
									<table class="table" id="servicoTable">
										<thead>
											<tr class="bg-slate">
												<th>Serviço</th>
												<th>Médico</th>
												<th>Data do Atendimento</th>
												<th>Horário</th>
												<th>Local</th>			
												<th class="text-right">Valor</th>
												<th class="text-center">Ações</th>
											</tr>
										</thead>
										<tbody id="dataServico">
											
										</tbody>
										<tfoot>
											<tr>
												<th colspan="5" class="text-right font-weight-bold" style="font-size: 16px;">
													<div>Valor(R$):</div>
												</th>
												<th colspan="1" class="mr-1">
													<div id="servicoValorTotal" class="text-right font-weight-bold" style="font-size: 15px;">R$ 0,00</div>
												</th>
												<th colspan="1" class="mr-1">
												</th>	
											</tr>
										</tfoot>
									</table>
								</div>

								<div class="col-lg-12 mb-4 row mt-2">
									<!-- titulos -->
									<div class="col-lg-12">
										<label>Observação</label>
									</div>

									<!-- campos -->
									<div class="col-lg-12">
										<textarea id="observacao" name="observacao" class="form-control" placeholder="Observações"></textarea>
									</div>
								</div>

								<div class="col-lg-12 row">
									
									<div class="col-lg-2">
										<label>Situação <span class="text-danger">*</span></label>
										<select id="cmbSituacao" name="cmbSituacao" class="select-search" required>
											<!--  -->
										</select>
									</div>
								</div>

								<!-- botões -->
								<div class="col-lg-12 mt-4 mb-2 row">
									<button class="btn btn-lg btn-principal" id="salvarAgendamento" data-tipo="AGENDAMENTO" >salvar</button>
									<a href="agendamento.php" class="btn btn-lg" id="cancelar">Cancelar</a>
								</div>

								<!--Modal cadastrar Paciente-->
								<div id="page-modal-paciente" class="custon-modal">
									<div class="custon-modal-container" style="min-width: 350px;">
										<div class="card custon-modal-content">
											<div class="custon-modal-title mb-2">
												<p class="h3">Cadastrar Paciente</p>
											</div>
											<form id="editaSituacao" name="incluirProduto" method="POST" class="form-validate-jquery">
												<div class="card-header header-elements-inline" style="margin-left:15px;">
													<h5 class="text-uppercase font-weight-bold">Dados Pessoais do paciente</h5>
												</div>
												<div class="px-3 ">
													<div class="d-flex flex-row ">
														<div class="col-lg-12">
															<div class="col-lg-12 row">
																<div class="col-lg-12">
																	<label>Nome <span class="text-danger">*</span></label>
																</div>
																<div class="col-lg-12">
																	<input id="nomePaciente" require name="nomePaciente" type="text" class="form-control">
																</div>
															</div>
															<div class="col-lg-12 my-4 row">
																<div class="col-lg-4">
																	<label>Telefone <span class="text-danger">*</span></label>
																</div>
																<div class="col-lg-4">
																	<label>Celular <span class="text-danger">*</span></label>
																</div>
																<div class="col-lg-4">
																	<label>E-mail <span class="text-danger">*</span></label>
																</div>

																<div class="col-lg-4">
																	<input require id="telefone" name="telefone" type="text" class="form-control" placeholder="Res./Com.">
																</div>
																<div class="col-lg-4">
																	<input require id="celular" name="celular" type="text" class="form-control" placeholder="Celular">
																</div>
																<div class="col-lg-4">
																	<input require id="email" name="email" type="text" class="form-control" placeholder="E-mail">
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
												</div>
												<div class="col-lg-12 ml-2 my-4 row text-right">
													<button class="btn btn-lg btn-principal" id="salvarPaciente" data-tipo="PACIENTE" >incluir</button>
													<button type="button" class="btn btn-link legitRipple" id="modal-close-x">Cancelar</button>
												</div>
											</form>
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
	<?php include_once("alerta.php"); ?>
</body>

</html>
