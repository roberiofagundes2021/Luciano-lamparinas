<?php 

// OBS.: Alterar linha de serviço(colocar em uma nova linha: Data, Hora e Botão)
// Alterar insert no banco, vai inserir, para cada serviço, um novo atendimento alterando apenas dados do seviço.

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Agendamento';

include('global_assets/php/conexao.php');

// a requisição é feita ao carregar a página via AJAX no arquivo filtraAgendamentos.php

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Agendamento</title>

	<?php include_once("head.php"); ?>
	<style>
		table td{
			padding: 1rem !important;
		}
	</style>

	<!-- Theme JS files -->
	<script src="global_assets/js/demo_pages/form_select2.js"></script>
	<script src="global_assets/js/demo_pages/picker_date.js"></script>
	<script src="global_assets/js/demo_pages/form_layouts.js"></script>
	<script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>
    <script src="global_assets/js/plugins/tables/datatables/datatables.min.js"></script>
    <script src="global_assets/js/plugins/tables/datatables/extensions/responsive.min.js"></script>
    <script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
    <script src="global_assets/js/demo_pages/datatables_responsive.js"></script>
    <script src="global_assets/js/demo_pages/datatables_sorting.js"></script>
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
		// buscar todos os agendamentos ao entrar na pagina
		getAgendamentos();
			
		$(document).ready(function() {
			
			$.fn.dataTable.moment('DD/MM/YYYY'); //Para corrigir a ordenação por data
			
			/* Início: Tabela Personalizada do Setor Publico */
			$('#AgendamentoTable').DataTable({
				"order": [[ 0, "desc" ]],
			    autoWidth: false,
				responsive: true,
			    columnDefs: [{
					orderable: true,   //Data - Hora
					width: "10%",
					targets: [0]
				},
				{ 
					orderable: true,   //Paciente
					width: "15%",
					targets: [1]
				},
				{ 
					orderable: true,   //Idade
					width: "5%",
					targets: [2]
				},				
				{ 
					orderable: true,   //Profissional
					width: "15%",
					targets: [3]
				},
				{ 
					orderable: true,   //Procedimento
					width: "15%",
					targets: [4]
				},
				{ 
					orderable: true,   //Modalidade
					width: "10%",
					targets: [5]
				},
				{
					orderable: true,   //Contato
					width: "10%",
					targets: [6]
				},
				{
					orderable: true,   //Situacao
					width: "5%",
					targets: [7]
				},
				{ 
					orderable: true,   //Ações
					width: "5%",
					targets: [8]
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
			});
			
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
			};	

			_componentSelect2();
			
			/* Fim: Tabela Personalizada */

			$('#modal-close-x').on('click', ()=>{
				$('#iAgendamentos').val('')
				$('#observacaoModal').html('').show()
				$('#page-modal-situacao').fadeOut(200);
			})
			$('#modal-auditoria-close-x').on('click', ()=>{
				$('#dataAuditoria').html('')
				$('#page-modal-auditoria').fadeOut(200);
			})

			$('#cmbSituacao').on('change', ()=>{
				let cmbSituacao = $('#cmbSituacao').val();
				$('iSituacao').val(cmbSituacao)
			})

			$('#mudarSituacao').on('click', ()=>{
				$.ajax({
					type: 'POST',
					url: 'filtraAgendamento.php',
					dataType: 'json',
					data:{
						'tipoRequest': 'MUDARSITUACAO',
						'iAgendamento': $('#iAgendamento').val(),
						'iSituacao': $('#cmbSituacao').val(),
						'sObservacao': $('#observacaoModal').val()
					},
					success: function(response) {
						alerta(response.titulo, response.menssagem, response.tipo);
						$('#iAgendamento').val('')
						$('#observacaoModal').val('')
						$('#page-modal-situacao').fadeOut(200);
						getAgendamentos();
					},
					error: function(response) {
						alerta(response.titulo, response.menssagem, response.tipo);
						$('#iAgendamento').val('')
						$('#observacaoModal').val('')
						$('#page-modal-situacao').fadeOut(200);
						getAgendamentos();
					}
				});
			})
			
		});

		function auditoria(element){
			$.ajax({
				type: 'POST',
				url: 'filtraAgendamento.php',
				dataType: 'json',
				data: {
					'tipoRequest': 'AUDITORIA',
					'tipo':$(element).data('tipo'),
					'id':$(element).data('id'),
				},
				success: async function(response) {
					$('#dataAuditoria').empty()
					if(response.status == 'success'){
						let data = new Date(response.auditoria.dataRegistro).toLocaleString("pt-BR", {timeZone: "America/Bahia"})
						let dataRegistro = new Date(response.auditoria.dtHrRegistro).toLocaleString("pt-BR", {timeZone: "America/Bahia"})
						let tds = `
						<tr>
							<td>${response.auditoria.UsuarNome}</td>
							<td>${data.split(' ')[0]} ${response.auditoria.horaRegistro.split('.')[0]}</td>
							<td>${response.auditoria.ClienNome}</td>
							<td>${dataRegistro.split(' ')[0]}</td>
						</tr>
						`;
						$('#dataAuditoria').html(tds)
						$('#page-modal-auditoria').fadeIn();
					}
				}
			});
		}
			
		//Essa função foi criada para não usar $_GET e ficar mostrando os ids via URL
		function getAgendamentos(){
			$.ajax({
				type: 'POST',
				url: 'filtraAgendamento.php',
				dataType: 'json',
				data:{
					'tipoRequest': 'AGENDAMENTOS'
				},
				success: function(response) {
					//|--Aqui é criado o DataTable caso seja a primeira vez q é executado e o clear é para evitar duplicação na tabela depois da primeira pesquisa

					let table = $('#AgendamentoTable').DataTable().clear().draw()

					table = $('#AgendamentoTable').DataTable()
					let rowNode

					response.forEach(item => {
						rowNode = table.row.add(item.data).draw().node()
						$(rowNode).attr('class', 'text-left')
						$(rowNode).find('td:eq(7)').attr('onclick', `alteraSituacao('${item.identify.situacao}', this)`)
						$(rowNode).find('td:eq(7)').attr('data-agendamento', `${item.identify.iAgendamento}`)
						$(rowNode).find('td:eq(7)').attr('data-observacao', `${item.identify.sObservacao}`)
						
						$(rowNode).find('td:eq(1)').attr('title', item.identify.prontuario)
						$(rowNode).find('td:eq(3)').attr('title', item.identify.cbo)
					})
				}
			});
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
					// primeiro limpa os valores para adicionar novos evitando duplicação
					$('#cmbSituacao').empty()
					$('#iAgendamento').val('')
					$('#observacaoModal').val('')

					response.forEach(item => {
						let opt = item.SituaChave === situacao? `<option selected value="${item.id}">${item.nome}</option>`:`<option value="${item.id}">${item.nome}</option>`
						$('#cmbSituacao').append(opt)
					})
					$('#iAgendamento').val($(element).data('agendamento'))
					$('#observacaoModal').val($(element).data('observacao'))

					$('#page-modal-situacao').fadeIn(200);
				}
			});
		}
		function atualizaAgendamento(tipo, iAgendamento){
			if(tipo == 'EDITA'){
				$('#iAgendamento').val(iAgendamento)
				$('#formEdita').submit()
			} else if(tipo == 'EXCLUI'){
				// (url, texto, tipoRequest, id, acaoSuccess)
				confirmaExclusaoAjax('filtraAgendamento.php', 'Excluir agendamento?', 'EXCLUI', iAgendamento, getAgendamentos)
			}
		}

		function submeterAgendaMedica(){ 
			document.formAgendaMedica.submit();
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
								<h3 class="card-title">Relação de Agendamentos</h3>
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
									<div class="col-lg-8">
										<p class="font-size-lg">A relação abaixo faz referência aos agendamentos da unidade <b><?php echo $_SESSION['UnidadeNome']; ?></b></p>
									</div>
									<div class="col-lg-4 text-right">
										<div class="text-right">
											<a href="#" onclick="submeterAgendaMedica()" class="btn" role="button">Agenda médica</a>
											<?php 
												echo $inserir?"<a href='agendamentoNovo.php' class='btn btn-principal' role='button'>Novo Agendamento</a>":"";
											?>
											<a href="#collapse-imprimir-relacao" class="btn bg-slate-700 btn-icon" role="button" data-toggle="collapse" data-placement="bottom" data-container="body">
												<i class="icon-printer2"></i>																						
											</a>
										</div>
									</div>	
								</div>
							</div>
						</div>
						<div class="card">
							<div class="card-header header-elements-inline">
								<h3 class="card-title">Agendamentos</h3>
								<div class="header-elements">
									<div class="list-icons">
										<a class="list-icons-item" data-action="collapse"></a>
										<!-- <a href="perfil.php" class="list-icons-item" data-action="reload"></a> -->
										<!--<a class="list-icons-item" data-action="remove"></a>-->
									</div>
								</div>
							</div>

							<table class="table" id="AgendamentoTable">
								<thead>
									<tr class="bg-slate text-left">
										<th>Data / Hora</th>
										<th>Paciente</th>
										<th>Idade</th>
										<th>Profissional</th>
										<th>Procedimento</th>			
										<th>Modalidade</th>
										<th>Contato</th>
										<th>Situação</th>
										<th class="text-center">Ações</th>
									</tr>
								</thead>
								<tbody id="dataAgendamentos">

								</tbody>
							</table>
						</div>
						<!-- /basic responsive configuration -->

					</div>
				</div>

				<!--Modal Editar Situação-->
                <div id="page-modal-situacao" class="custon-modal">
                    <div class="custon-modal-container" style="max-width: 300px;">
                        <div class="card custon-modal-content">
                            <div class="custon-modal-title mb-2" style="background-color: #466d96; color: #ffffff">
                                <p class="h5">Situação do Agendamento</p>
                                <i id="modal-close-x" class="fab-icon-open icon-cross2 p-3" style="cursor: pointer"></i>
                            </div>
							<div class="px-0">
								<div class="d-flex flex-row">
									<div class="col-lg-12">
										<form id="editaSituacao" name="alterarSituacao" method="POST" class="form-validate-jquery">
											<div class="form-group">
												<div class="col-lg-12 mt-2">
													<div class="col-lg-12">
														<label for="cmbSituacao">Situação <span class="text-danger">*</span></label>
													</div>
													<div class="col-lg-12">
														<select id="cmbSituacao" name="cmbSituacao" class="form-control form-control-select2" required>
															<!--  -->
														</select>
													</div>
												</div>
												<div class="col-lg-12 mt-4">
													<!-- titulos -->
													<div class="col-lg-12">
														<label>Observação <span class="text-danger">*</span></label>
													</div>

													<!-- campos -->
													<div class="col-lg-12">
														<textarea id="observacaoModal" name="observacaoModal" class="form-control" placeholder="Observações"></textarea>
													</div>
												</div>
											</div>
										</form>
									</div>
								</div>
								<div class="text-right m-2"><button id="mudarSituacao" class="btn btn-principal" role="button">Confirmar</button></div>
							</div>
						</div>
					</div>
				</div>
				<!--Modal Auditoria-->
                <div id="page-modal-auditoria" class="custon-modal">
                    <div class="custon-modal-container" style="max-width: 800px;">
                        <div class="card custon-modal-content">
                            <div class="custon-modal-title mb-2" style="background-color: #466d96; color: #ffffff">
                                <p class="h5">Auditoria</p>
                                <i id="modal-auditoria-close-x" class="fab-icon-open icon-cross2 p-3" style="cursor: pointer"></i>
                            </div>
							<div class="px-0">
								<div class="d-flex flex-row">
									<div class="col-lg-12">
										<table class="table mb-4" id="auditoriaTable">
											<thead>
												<tr>
													<th style="width: 35%;">Atendente</th>
													<th style="width: 30%;">Data/Hora</th>
													<th style="width: 35%;">Paciente</th>
													<th style="width: 20%">Atualização</th>
												</tr>
											</thead>
											<tbody id="dataAuditoria">

											</tbody>
										</table>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- /content area -->

				<!-- inf block -->
				<form id="formEdita" method="POST" action="agendamentoEdita.php">
					<input id="iAgendamento" name="iAgendamento" type="hidden" value="" />
				</form>

				<!-- Agenda Médica --> 
				<form name="formAgendaMedica" id="formAgendaMedica" method="POST" action="agendaMedica.php">
					<input id="inputOrigem" name="inputOrigem" type="hidden" value="agendamento.php" />
				</form>
			</div>			
			<?php include_once("footer.php"); ?>

		</div>
		<!-- /main content -->

	</div>
	<!-- /page content -->

	<?php include_once("alerta.php"); ?>

</body>

</html>
