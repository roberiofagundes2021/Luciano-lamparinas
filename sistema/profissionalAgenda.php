<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Agenda';
$_SESSION['agendaProfissional'] = [];

include('global_assets/php/conexao.php');

// as duas lista "$visaoAtendente" e "$visaoProfissional" representam os perfis
// que podem ver a tela de atendimento na visão do atendente ou profissional respectivamente

$iUnidade = $_SESSION['UnidadeId'];
$iEmpresa = $_SESSION['EmpreId'];
$usuarioId = $_SESSION['UsuarId'];

if(isset($_POST['inputProfissionalId'])){
	$iProfissional = $_POST['inputProfissionalId'];
} else {
	irpara("profissional.php");
}

$sql = "SELECT ProfiNome
	FROM Profissional
	WHERE ProfiId = $iProfissional";
$result = $conn->query("$sql");
$rowProfissional = $result->fetch(PDO::FETCH_ASSOC);
// as requisições são feitas ao carregar a página via AJAX no arquivo filtraProfissionalAgenda.php
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Agenda</title>

	<?php include_once("head.php"); ?>

	<!-- ///////////////////////////////////////////////////////////////////////////////////// -->
	<!-- <script src="global_assets/js/demo_pages/fullcalendar_advanced.js"></script> -->

	<!-- Global stylesheets -->
	<link href="global_assets/css/icons/icomoon/styles.css" rel="stylesheet" type="text/css">
	<link href="assets/css/bootstrap.min.css" rel="stylesheet" type="text/css">
	<link href="assets/css/bootstrap_limitless.min.css" rel="stylesheet" type="text/css">
	<link href="assets/css/layout.min.css" rel="stylesheet" type="text/css">
	<link href="assets/css/components.min.css" rel="stylesheet" type="text/css">
	<link href="assets/css/colors.min.css" rel="stylesheet" type="text/css">
	<!-- /global stylesheets -->

	<!-- Core JS files -->
	<script src="global_assets/js/main/bootstrap.bundle.min.js"></script>
	<script src="global_assets/js/plugins/loaders/blockui.min.js"></script>
	<script src="global_assets/js/plugins/ui/ripple.min.js"></script>
	<!-- /core JS files -->

	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/extensions/jquery_ui/interactions.min.js"></script>
	<script src="global_assets/js/plugins/forms/styling/switchery.min.js"></script>
	<script src="global_assets/js/plugins/ui/moment/moment.min.js"></script>
	<script src="global_assets/js/plugins/ui/fullcalendar/fullcalendar.min.js"></script>
	<script src="global_assets/js/plugins/ui/fullcalendar/lang/pt-br.js"></script>
	
	<!-- /theme JS files -->
	<!-- ///////////////////////////////////////////////////////////////////////////////////// -->
	
	<!-- Theme JS files -->

    <script src="global_assets/js/plugins/tables/datatables/datatables.min.js"></script>
    <script src="global_assets/js/plugins/tables/datatables/extensions/responsive.min.js"></script>
    <script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
    <script src="global_assets/js/demo_pages/datatables_responsive.js"></script>
    <script src="global_assets/js/demo_pages/datatables_sorting.js"></script>
    <script src="global_assets/js/demo_pages/form_layouts.js"></script>
    <script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>
    <script src="global_assets/js/demo_pages/form_select2.js"></script>

	<!-- Plugin para corrigir a ordenação por data. Caso a URL dê problema algum dia, salvei esses 2 arquivos na pasta global_assets/js/lamparinas -->
	<script type="text/javascript" language="javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.8.4/moment.min.js"></script>
	<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/plug-ins/1.10.10/sorting/datetime-moment.js"></script>	

	<!-- Modal -->
	<script src="global_assets/js/plugins/notifications/bootbox.min.js"></script>
    
    <!-- Validação -->
	<script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
	<script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
	<script src="global_assets/js/demo_pages/form_validation.js"></script>
	<style>
		/* .excluirContainer{
			width:100%;
			height:220px;
			padding:10px;
			background-color:#f99d9d;
			color:red;
			opacity:0.2;
		} */
		.excluirContainer {
			width: 100%;
			height: 220px;
			padding: 10px;
			background-color: #ccc;
			color: #333;
			opacity: 0.2;
			border: 1px solid #333;
		}
	</style>
	<?php
		echo "<script>
				iUnidade = $iUnidade
				iEmpresa = $iEmpresa
			</script>"
	?>

	<script></script>

	<script type="text/javascript">
		const socket = WebSocketConnect(iUnidade,iEmpresa)
		socket.onmessage = function (event) {
			menssage = JSON.parse(event.data)
			if(menssage.type == 'AGENDA'){
				getAgenda()
			}
		};

		$(document).ready(function(){
			getAgenda()
			//$('#excluirContainer').hide();

			$('#salvarAgenda').on('click', ()=>{
				$('#salvarAgenda').html("<img src='global_assets/images/lamparinas/loader-transparente2.gif' style='width: 17px'>");
				$("#salvarAgenda").prop('disabled', true);
				$.ajax({
					type: 'POST',
					url: 'filtraProfissionalAgenda.php',
					dataType: 'json',
					data:{
						'tipoRequest': 'SALVAAGENDA',
						'iProfissional': $('#iProfissional').val()
					},
					success: function(response) {
						alerta(response.titulo, response.menssagem, response.status)
						// window.location.href = "profissional.php"
						getAgenda()
						$('#salvarAgenda').html('Salvar');
						$("#salvarAgenda").prop('disabled', false);
						socket.sendMenssage({
							'type':'AGENDA'
						});
					}
				});
			})
			$('#modal-close-x').on('click', function(){
				$('#page-modal-horario').fadeOut(200);
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
		})

		function getAgenda(){
			// iniciar o calendário
			$.ajax({
				type: 'POST',
				url: 'filtraProfissionalAgenda.php',
				dataType: 'json',
				data:{
					'tipoRequest': 'AGENDA',
					'iProfissional': $('#iProfissional').val()
				},
				success: function(response) {
					clearModal()
					// reseta o calendário
					$('#fullcalendar-external').fullCalendar('destroy')
					
					// Initialize the calendar
					$('#fullcalendar-external').fullCalendar({
						header: {
							left: 'prev,next today',
							center: 'title',
							right: 'month,agendaWeek'
						},
						editable: true,
						defaultDate: dataAtual,
						events: response,
						timeZone: 'America/Bahia',
						locale: 'pt-br',
						droppable: true,
						drop: function(arg) { //ao soltar
							// $(this).remove();
						},
						eventReceive: function(event, jsEvent, view){
							clearModal()
							let id = event.id?event.id:event._id
							let localId = event.localId
							let title = event.title
							let dtI=event.start?event.start._i:''
							let dtF=event.end?event.end._i:''
							let intervalo=event.intervalo?event.intervalo:30

							let data = formatDate(dtI, dtF)

							let horaStart = data.dataI.split(' ')[1]
							let horaEnd = data.dataF.split(' ')[1]

							$('#horaAgendaInicio').val(horaStart)
							$('#horaAgendaFim').val(horaEnd)
							$('#horaIntervalo').val(intervalo)

							$.ajax({
								type: 'POST',
								url: 'filtraProfissionalAgenda.php',
								dataType: 'json',
								data:{
									'tipoRequest': 'SETAGENDA',
									'id':id,
									'localId':localId,
									'title':title,
									'dataI':data.dataI,
									'dataF':data.dataF,
									'horaIntervalo':intervalo,
									'cor':event.color
								},
								success: function(response) {
									let dataTitulo = data.dataI.split(' ');

									$('#tituloModal').html(`Definir horário para dia ${dataTitulo[0]}`);
									$('#idEvent').val(id);
									$('#page-modal-horario').fadeIn(200);
								}
							});
						},
						eventClick: function(event, jsEvent, view) { //ao clicar em cima (pode ser removido)
							let id = event.id?event.id:event._id
							let dtI=event.start?event.start._i:''
							let dtF=event.end?event.end._i:''
							let intervalo=event.intervalo?event.intervalo:30

							let data = formatDate(dtI, dtF)

							let horaStart = data.dataI.split(' ')[1]
							let horaEnd = data.dataF.split(' ')[1]

							$('#horaAgendaInicio').val(horaStart)
							$('#horaAgendaFim').val(horaEnd)
							$('#horaIntervalo').val(intervalo)

							let dataTitulo = data.dataI.split(' ');

							$('#tituloModal').html(`Definir horário para dia ${dataTitulo[0]}`);
							$('#idEvent').val(id);
							$('#page-modal-horario').fadeIn(200);
						},
						eventDrop: function(event, jsEvent, ui, view) { // ao arrastar e soltar
							clearModal()
							let id = event.id?event.id:event._id
							let localId = event.localId
							let title = event.title
							let dtI=event.start?event.start._i:''
							let dtF=event.end?event.end._i:''
							let intervalo=event.intervalo?event.intervalo:30

							let data = formatDate(dtI, dtF)

							let horaStart = data.dataI.split(' ')[1]
							let horaEnd = data.dataF.split(' ')[1]

							$('#horaAgendaInicio').val(horaStart)
							$('#horaAgendaFim').val(horaEnd)
							$('#horaIntervalo').val(intervalo)

							$.ajax({
								type: 'POST',
								url: 'filtraProfissionalAgenda.php',
								dataType: 'json',
								data:{
									'tipoRequest': 'SETAGENDA',
									'id':id,
									'localId':localId,
									'title':title,
									'dataI':data.dataI,
									'dataF':data.dataF,
									'horaIntervalo':intervalo,
									'cor':event.color
								},
								success: function(response) {
									let dataTitulo = data.dataI.split(' ');

									$('#tituloModal').html(`Definir horário para dia ${dataTitulo[0]}`);
									$('#idEvent').val(id);
									$('#page-modal-horario').fadeIn(200);
								}
							});
						},
						eventDragStart: function(event,jsEvent){
							$('#excluirContainer').show();
						},
						eventDragStop: function(event,jsEvent) {							
							/* 	basicamente o calculo é feito olhando sempre o tamanho do componente "excluirContainer"
								pega a posição onde foi solto o item (jsEvent.pageX) depois verifica se essa
								posição é maior que a posição onde se inicia o componente e menor
								que o tamanho do mesmo(largura) somado com o valor onde ela se inicia(posicaoX)
								ou seja:
								larguraVerificacao = "true" se posicaoDrop > inicioComponente &&
								posicaoDrop < (inicioComponente+largura)
								dessa forma temos:
								larguraVerificacao = 100 > 96 && 100 < (96+200)?true:false
								aplicando a mesma lógica para a altura(posicaoY)
							*/

							let largura = $('#excluirContainer').width()
							let altura = $('#excluirContainer').height()
							let posicaoX = $('#excluirContainer').offset().left
							let posicaoY = $('#excluirContainer').offset().top

							let larguraVerificacao = jsEvent.pageX > posicaoX && jsEvent.pageX < (posicaoX + largura)?true:false
							let alturaVerificacao = jsEvent.pageY > posicaoY && jsEvent.pageY < (posicaoY + altura)?true:false

							if(larguraVerificacao && alturaVerificacao){
								new PNotify({
									title: 'Confirmação',
									text: 'Deseja excluir esse item da agenda?',
									icon: 'icon-question4',
									hide: false,
									confirm: {
										confirm: true,
										buttons: [
											{
												text: 'Sim',
												primary: true,
												click: function (notice) {
													$.ajax({
														type: 'POST',
														url: 'filtraProfissionalAgenda.php',
														dataType: 'json',
														data:{
															'tipoRequest': 'REMOVEAGENDA',
															'id': event.id?event.id:event._id
														},
														success: function(response) {
															notice.remove();
															alerta(response.titulo, response.menssagem, response.status)
															refreshAgenda()
														}
													});
												},
											},
											{
												text: 'Não',
												click: function (notice) {
													notice.remove();
												},
											},
										],
									},
									buttons: {
										closer: false,
										sticker: false,
									},
									history: {
										history: false,
									},
									addclass: 'stack-modal',
									stack: { dir1: 'down', dir2: 'right', modal: false },
								})
							}
							//$('#excluirContainer').hide();
						},
						isRTL: false
					});

					// Initialize the external events
					$('#external-events .fc-event').each(function() {
						// Different colors for events
						$(this).css({'backgroundColor': $(this).data('color'), 'borderColor': $(this).data('color')});

						// Store data so the calendar knows to render an event upon drop
						$(this).data('event', {
							title: $.trim($(this).html()), // use the element's text as the event title
							color: $(this).data('color'),
							localId: $(this).data('local'),
							intervalo: $(this).data('intervalo'),
							allDay: false,
							stick: true, // maintain when user navigates (see docs on the renderEvent method)
							start: '07:00:00',
							end: null,
						});

						// Make the event draggable using jQuery UI
						$(this).draggable({
							zIndex: 999,
							revert: true, // will cause the event to go back to its
							revertDuration: 0 // original position after the drag
						});
					});
				}
			});
		}

		function refreshAgenda(){
			// recarrega o calendário
			$.ajax({
				type: 'POST',
				url: 'filtraProfissionalAgenda.php',
				dataType: 'json',
				data:{
					'tipoRequest': 'CHECKAGENDA',
					'iProfissional': $('#iProfissional').val()
				},
				success: function(response) {
					clearModal()
					// reseta o calendário
					$('#fullcalendar-external').fullCalendar('destroy')
					
					// Initialize the calendar
					$('#fullcalendar-external').fullCalendar({
						header: {
							left: 'prev,next today',
							center: 'title',
							right: 'month,agendaWeek'
						},
						editable: true,
						defaultDate: dataAtual,
						events: response,
						timeZone: 'America/Bahia',
						locale: 'pt-br',
						droppable: true,
						drop: function(arg) { //ao soltar
							// $(this).remove();
						},
						eventReceive: function(event, jsEvent, view){
							clearModal()
							let id = event.id?event.id:event._id
							let localId = event.localId
							let title = event.title
							let dtI=event.start?event.start._i:''
							let dtF=event.end?event.end._i:''
							let intervalo=event.intervalo?event.intervalo:30

							let data = formatDate(dtI, dtF)

							$.ajax({
								type: 'POST',
								url: 'filtraProfissionalAgenda.php',
								dataType: 'json',
								data:{
									'tipoRequest': 'SETAGENDA',
									'id':id,
									'localId':localId,
									'title':title,
									'dataI':data.dataI,
									'dataF':data.dataF,
									'horaIntervalo':intervalo,
									'cor':event.color
								},
								success: function(response) {
									let dataTitulo = data.dataI.split(' ');

									$('#tituloModal').html(`Definir horário para dia ${dataTitulo[0]}`);
									$('#idEvent').val(id);
									$('#page-modal-horario').fadeIn(200);
								}
							});
						},
						eventClick: function(event, jsEvent, view) { //ao clicar em cima (pode ser removido)
							let id = event.id?event.id:event._id
							let dtI=event.start?event.start._i:''
							let dtF=event.end?event.end._i:''
							let intervalo=event.intervalo?event.intervalo:30

							let data = formatDate(dtI, dtF)

							let horaStart = data.dataI.split(' ')[1]
							let horaEnd = data.dataF.split(' ')[1]

							$('#horaAgendaInicio').val(horaStart)
							$('#horaAgendaFim').val(horaEnd)
							$('#horaIntervalo').val(intervalo)

							let dataTitulo = data.dataI.split(' ');

							$('#tituloModal').html(`Definir horário para dia ${dataTitulo[0]}`);
							$('#idEvent').val(id);
							$('#page-modal-horario').fadeIn(200);
						},
						eventDrop: function(event, jsEvent, ui, view) { // ao arrastar e soltar
							clearModal()
							let id = event.id?event.id:event._id
							let localId = event.localId
							let title = event.title
							let dtI=event.start?event.start._i:''
							let dtF=event.end?event.end._i:''
							let intervalo=event.intervalo?event.intervalo:30

							let data = formatDate(dtI, dtF)

							let horaStart = data.dataI.split(' ')[1]
							let horaEnd = data.dataF.split(' ')[1]

							$('#horaAgendaInicio').val(horaStart)
							$('#horaAgendaFim').val(horaEnd)
							$('#horaIntervalo').val(intervalo)

							$.ajax({
								type: 'POST',
								url: 'filtraProfissionalAgenda.php',
								dataType: 'json',
								data:{
									'tipoRequest': 'SETAGENDA',
									'id':id,
									'localId':localId,
									'title':title,
									'dataI':data.dataI,
									'dataF':data.dataF,
									'horaIntervalo':intervalo,
									'cor':event.color
								},
								success: function(response) {
									let dataTitulo = data.dataI.split(' ');

									$('#tituloModal').html(`Definir horário para dia ${dataTitulo[0]}`);
									$('#idEvent').val(id);
									$('#page-modal-horario').fadeIn(200);
								}
							});
						},
						eventDragStart: function(event,jsEvent){
							//$('#excluirContainer').show();
						},
						eventDragStop: function(event,jsEvent) {							
							/* 	basicamente o calculo é feito olhando sempre o tamanho do componente "excluirContainer"
								pega a posição onde foi solto o item (jsEvent.pageX) depois verifica se essa
								posição é maior que a posição onde se inicia o componente e menor
								que o tamanho do mesmo(largura) somado com o valor onde ela se inicia(posicaoX)
								ou seja:
								larguraVerificacao = "true" se posicaoDrop > inicioComponente &&
								posicaoDrop < (inicioComponente+largura)
								dessa forma temos:
								larguraVerificacao = 100 > 96 && 100 < (96+200)?true:false
								aplicando a mesma lógica para a altura(posicaoY)
							*/

							let largura = $('#excluirContainer').width()
							let altura = $('#excluirContainer').height()
							let posicaoX = $('#excluirContainer').offset().left
							let posicaoY = $('#excluirContainer').offset().top

							let larguraVerificacao = jsEvent.pageX > posicaoX && jsEvent.pageX < (posicaoX + largura)?true:false
							let alturaVerificacao = jsEvent.pageY > posicaoY && jsEvent.pageY < (posicaoY + altura)?true:false

							if(larguraVerificacao && alturaVerificacao){
								new PNotify({
									title: 'Confirmação',
									text: 'Deseja excluir esse item da agenda?',
									icon: 'icon-question4',
									hide: false,
									confirm: {
										confirm: true,
										buttons: [
											{
												text: 'Sim',
												primary: true,
												click: function (notice) {
													$.ajax({
														type: 'POST',
														url: 'filtraProfissionalAgenda.php',
														dataType: 'json',
														data:{
															'tipoRequest': 'REMOVEAGENDA',
															'id': event.id?event.id:event._id
														},
														success: function(response) {
															notice.remove();
															alerta(response.titulo, response.menssagem, response.status)
															refreshAgenda()
														}
													});
												},
											},
											{
												text: 'Não',
												click: function (notice) {
													notice.remove();
												},
											},
										],
									},
									buttons: {
										closer: false,
										sticker: false,
									},
									history: {
										history: false,
									},
									addclass: 'stack-modal',
									stack: { dir1: 'down', dir2: 'right', modal: false },
								})
							}
							//$('#excluirContainer').hide();
						},
						isRTL: false
					});

					// Initialize the external events
					$('#external-events .fc-event').each(function() {
						// Different colors for events
						$(this).css({'backgroundColor': $(this).data('color'), 'borderColor': $(this).data('color')});

						// Store data so the calendar knows to render an event upon drop
						$(this).data('event', {
							title: $.trim($(this).html()), // use the element's text as the event title
							color: $(this).data('color'),
							localId: $(this).data('local'),
							intervalo: $(this).data('intervalo'),
							allDay: false,
							stick: true, // maintain when user navigates (see docs on the renderEvent method)
							start: '07:00:00',
							end: null,
						});

						// Make the event draggable using jQuery UI
						$(this).draggable({
							zIndex: 999,
							revert: true, // will cause the event to go back to its
							revertDuration: 0 // original position after the drag
						});
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

		var dataAtual = new Date().toLocaleString("pt-BR", {timeZone: "America/Bahia"});
		dataAtual = dataAtual.split(' ')[0];
		dataAtual = dataAtual.split('/')[2]+'-'+dataAtual.split('/')[1]+'-'+dataAtual.split('/')[0];

		$.ajax({
			type: 'POST',
			url: 'filtraProfissionalAgenda.php',
			dataType: 'json',
			data:{
				'tipoRequest': 'LOCAIS',
			},
			success: function(response) {
				$('#locaisAtendimento').html('').show()
				response.forEach(function(item){
					$('#locaisAtendimento').append(
						`<div class="fc-event" style="text-shadow: 0px 0px 2px #000" data-intervalo="${item.AtLocIntervalo}" data-local="${item.idLocal}" data-color="${item.cor}">
							${item.nome}
						</div>`
					)
				});
			}
		});

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
				<form method="POST">
					<?php
						echo "<input id='iProfissional' type='hidden' value='$iProfissional'>";
					?>
				</form>
				<div class="card">
					<div class="card-header header-elements-inline">
						<h3 class="card-title">Agenda do Profissional</h3>
					</div>
					
					<div class="card-body">
						<div class="card-header bg-white" style="margin-top: -15px;">
							<div class="row">
								<div class="col-lg-9">
									<p class="font-size-lg" style="margin-left: -18px;">A relação abaixo faz referência à agenda do profissional <b><?php echo $rowProfissional['ProfiNome']; ?></b></p>
								</div>
								<div class="col-lg-3 text-right" style="margin-top: -10px;">
									<a href="profissional.php" class="btn" id="cancelar">Cancelar</a>
									<button id="salvarAgenda" class="btn btn-principal" role="button" >Salvar</button>									
								</div>
							</div>
						</div>
						<br>
						<div class="row">
							<div class="col-md-3" style="margin-top: 25px;">
								<div class="mb-3" id="external-events">
									<h5 class="text-uppercase font-weight-bold">Locais de atendimento</h5>
									<div id="locaisAtendimento" class="fc-events-container mb-3">
									</div>

									<div class="">
										<div id="excluirContainer" class="excluirContainer text-center">
											<i style="font-size:50px; padding-top:70px" class="fab-icon-open icon-trash"></i>
										</div>
									</div>
								</div>
							</div>

							<div class="col-md-9">
								<div id="fullcalendar-external"></div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!--Modal Editar Situação-->
			<div id="page-modal-horario" class="custon-modal">
				<div class="custon-modal-container" style="max-width: 750px;">
					<div class="card custon-modal-content">
						<div class="custon-modal-title mb-2" style="background-color: #466d96; color: #ffffff">
							<p id="tituloModal" class="h5"><!-- definido ao abrir modal--></p>
							<i id="modal-close-x" class="fab-icon-open icon-cross2 p-3" style="cursor: pointer"></i>
						</div>
						<div class="px-0">
							<div class="d-flex flex-row">
								<div class="col-lg-12">
									<div id="setHorario">
										<div class="form-group row">
											<div class="col-lg-5 mt-2">
												<div class="col-lg-12">
													<label>Início <span class="text-danger">*</span></label>
												</div>
												<div class="col-lg-12">
													<input id="horaAgendaInicio" type="time" class="form-control" value="" required>
												</div>
											</div>

											<div class="col-lg-5 mt-2">
												<div class="col-lg-12">
													<label>Fim <span class="text-danger">*</span></label>
												</div>
												<div class="col-lg-12">
													<input id="horaAgendaFim" type="time" class="form-control" value="" required>
												</div>
											</div>

											<div class="col-lg-2 mt-2">
												<div class="col-lg-12">
													<label>Intervalo(min)<span class="text-danger">*</span></label>
												</div>
												<div class="col-lg-12">
													<input id="horaIntervalo" class="form-control" type="number" name="number">
												</div>
											</div>
										</div>
										<div>
											<input type="hidden" id="idEvent" name="idEvent" value=""/>
										</div>
										<div class="text-right m-2 mt-3">
											<button id="definirHorario" class="btn btn-principal" role="button">Confirmar</button>
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
		<!-- /main content -->

	</div>
	<!-- /page content -->

	<?php include_once("alerta.php"); ?>

</body>

</html>
