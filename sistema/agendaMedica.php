<?php 

// OBS.: Alterar linha de serviço(colocar em uma nova linha: Data, Hora e Botão)
// Alterar insert no banco, vai inserir, para cada serviço, um novo atendimento alterando apenas dados do seviço.

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Agenda medica';

include('global_assets/php/conexao.php');

if (isset($_POST['inputOrigem'])){
	$urlOrigem = $_POST['inputOrigem'];
} else {
	irpara("agendamento.php");
}

// a requisição é feita ao carregar a página via AJAX no arquivo filtraAgendamentos.php
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Agenda médica</title>

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

	<!-- Core JS files -->
	
	<script type="text/javascript" >			
		$(document).ready(function() {
			getProfissionais()
			getPikerDate()

			$.fn.dataTable.moment('DD/MM/YYYY'); //Para corrigir a ordenação por data
			
			/* Início: Tabela Personalizada do Setor Publico */
			$('#AgendaTable').DataTable({
				"order": [[ 0, "desc" ],[ 1, "asc" ]],
			    autoWidth: false,
				responsive: true,
			    columnDefs: [{
					orderable: true,   //Data
					width: "10%",
					targets: [0]
				},
				{ 
					orderable: true,   //Horário
					width: "10%",
					targets: [1]
				},
				{ 
					orderable: true,   //Local
					width: "20%",
					targets: [2]
				},				
				{ 
					orderable: true,   //Paciente
					width: "20%",
					targets: [3]
				},
				{ 
					orderable: false,   //Serviço
					width: "30%",
					targets: [4]
				},
				{ 
					orderable: true,   //Situação
					width: "10%",
					targets: [5]
				},
				{ 
					orderable: false,   //Ações
					width: "10%",
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
			// Select2 for length menu styling
			var _componentSelect2 = function() {
				if (!$().select2) {
					console.warn('Warning - select2.min.js is not loaded.');
					return;
				}

				// Initialize
				$('.dataTables_length select').select2({
					minimumResultsForSearch: Infinity,
					dropdownAutoWidth: true,
					width: 'auto'
				});
			}
			_componentSelect2()
			/* Fim: Tabela Personalizada */

			$('#medicoSelect').on('change', function(e){
				$.ajax({
					type: 'POST',
					url: 'filtraAgendaMedica.php',
					dataType: 'json',
					data:{
						'tipoRequest': 'AGENDA',
						'iProfissional': $(this).val()
					},
					success: function(response) {
						if(response.status == 'success'){
							let array = [true]
							response.data.forEach(item => {
								array.push([
									parseInt(item.data[0]),
									parseInt(item.data[1])-1,
									parseInt(item.data[2])
								])
							})
							getPikerDate(array)
						}else{
							alerta(response.titulo, response.menssagem, response.status)
						}
					}
				});

				//Esse ajax está sendo usado para verificar no banco se o registro já existe
				$.ajax({
					type: "POST",
					url: "filtraAgendaMedica.php",
					data:{
						'tipoRequest': 'DADOSPROFISSIONAL',
						'iProfissional': $(this).val()
					},
					success: function(resposta) {

						if(resposta){
							$('#dadosProfissional').html(resposta);
						}					
					}
				})
			})
		});

		function getProfissionais(){
			$.ajax({
				type: 'POST',
				url: 'filtraAgendaMedica.php',
				dataType: 'json',
				data:{
					'tipoRequest': 'PROFISSIONAIS'
				},
				success: function(response) {
					if(response.status == 'success'){
						$('#medicoSelect').html("<option value=''>selecione</option>")
						response.data.forEach(item => {
							$('#medicoSelect').append(`<option value='${item.id}'>${item.nome}</option>`)
						})
					}else{
						alerta(response.titulo, response.menssagem, response.status)
					}
				}
			});
		}

		function getPikerDate(arrayDate){
			$('#dataAgenda').html('')
			$('#dataAgenda').html('<input type="text" class="form-control pickadate" placeholder="">')

			let array = arrayDate?arrayDate:[]
			// Events
			$('.pickadate').pickadate({
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
				formatSubmit: 'yyyy/mm/dd',
				disable: array,
				// disable: [
				// 	true,
				// 	[2022,8,4],
				// 	[2022,8,5],
				// 	[2022,8,6]
				// ],
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
							'font-weight-bold border bg-gray-agenda'))
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
							'font-weight-bold border bg-gray-agenda'))
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
					let data = new Date(context.select).toLocaleString("pt-BR", {timeZone: "America/Bahia"})
					data = data.split(' ')[0]

					$.ajax({
						type: 'POST',
						url: 'filtraAgendaMedica.php',
						dataType: 'json',
						data:{
							'tipoRequest': 'AGENDAPROFISSIONAL',
							'iProfissional': $('#medicoSelect').val(),
							'data': data // dd/mm/yyyy
						},
						success: function(response) {
							console.log(response)
							if(response.status == 'success'){
								let tableAgenda = $('#AgendaTable').DataTable().clear().draw()
								tableAgenda = $('#AgendaTable').DataTable()

								let rowTableAgenda

								response.data.forEach(item => {
									rowTableAgenda = tableAgenda.row.add(item).draw().node()
									$(rowTableAgenda).attr('class', 'text-center')
									// $(rowTableAgenda).find('td:eq(6)').attr('data-atendimento', `${item.identify.iAtendimento}`)
								})
							}else{
								alerta(response.titulo, response.menssagem, response.status)
							}
						}
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

				<!-- Info blocks -->		
				<div class="row">
					<div class="col-lg-12">
						<!-- Basic responsive configuration -->
						<div class="card">
							<div class="card-header header-elements-inline">
								<h3 class="card-title">Relação da agenda médica</h3>
								<div class="header-elements">
									<div class="list-icons">
										<!-- <a class="list-icons-item" data-action="collapse"></a>
										<a href="perfil.php" class="list-icons-item" data-action="reload"></a> -->
										<!--<a class="list-icons-item" data-action="remove"></a>-->
									</div>
								</div>
							</div>					

							<div class="card-body">
								<div class="row">
									<div class="col-lg-12">
										<p class="font-size-lg">A relação abaixo faz referência à agenda dos profissionais da unidade <b><?php echo $_SESSION['UnidadeNome']; ?></b></p>
									</div>
									<!-- <div class="col-lg-4 text-right">
										<div class="text-right">
											<a href="#" class="btn" role="button">Agenda médica</a>
											<a href="agendamentoNovo.php" class="btn btn-principal" role="button">Novo Agendamento</a>
											<a href="#collapse-imprimir-relacao" class="btn bg-slate-700 btn-icon" role="button" data-toggle="collapse" data-placement="bottom" data-container="body">
												<i class="icon-printer2"></i>																						
											</a>
										</div>
									</div> -->
								</div>
							</div>
						</div>
						<div class="card pl-2">
							<div class="col-lg-12 my-4 row">
								<div class="col-lg-3">
									<label for="medicoSelect">Profissional</label>
									<select id="medicoSelect" name="medicoSelect" class="select-search">
										<option value="">selecione</option>
									</select>
								</div>
								<div class="col-lg-3">
									<label>Data</label>
									<div id="dataAgenda" class=" input-group">
										<input type="text" class="form-control pickadate" placeholder="">
									</div>
								</div>
							</div>

							<div class="text-right" style=" margin-top: -40px; margin-right: 15px; "><a href="<?php echo $urlOrigem; ?>" role="button"><< VOLTAR</a>&nbsp;&nbsp;&nbsp;</div>

							<div id="dadosProfissional" style="padding: 10px; "></div>							

						</div>
						<div class="card">
							<div class="card-header header-elements-inline">
								<h3 class="card-title">Relação da Agenda</h3>
								<div class="header-elements">
									<div class="list-icons">
										<a class="list-icons-item" data-action="collapse"></a>
										<!-- <a href="perfil.php" class="list-icons-item" data-action="reload"></a> -->
										<!--<a class="list-icons-item" data-action="remove"></a>-->
									</div>
								</div>
							</div>

							<table class="table" id="AgendaTable">
								<thead>
									<tr class="bg-slate text-center">
										<th>Data</th>
										<th>Horário</th>
										<th>Local</th>
										<th>Paciente</th>
										<th>Serviço</th>
										<th>Situação</th>
										<th class="text-center">Ações</th>
									</tr>
								</thead>
								<tbody>

								</tbody>
							</table>
						</div>
						<!-- /basic responsive configuration -->

					</div>
				</div>
				<!-- /content area -->
			</div>			
			<?php include_once("footer.php"); ?>

		</div>
		<!-- /main content -->

	</div>
	<!-- /page content -->

	<?php include_once("alerta.php"); ?>

</body>

</html>
