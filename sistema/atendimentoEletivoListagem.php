<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Atendimento Eletivo';
$_SESSION['UltimaPagina'] = 'ELETIVO';

include('global_assets/php/conexao.php');

// as duas lista "$visaoAtendente" e "$visaoProfissional" representam os perfis
// que podem ver a tela de atendimento na visão do atendente ou profissional respectivamente

$iUnidade = $_SESSION['UnidadeId'];
$usuarioId = $_SESSION['UsuarId'];
$acesso = 'ATENDIMENTO';

// $sql = "SELECT ProfiId, ProfiUsuario
// FROM Profissional
// WHERE ProfiUsuario = $usuarioId and ProfiUnidade = $iUnidade";
// $result = $conn->query($sql);
// $row = $result->fetch(PDO::FETCH_ASSOC);

// $acesso = isset($row['ProfiId'])?'PROFISSIONAL':'PROFISSIONAL';

// as requisições são feitas ao carregar a página via AJAX no arquivo filtraAtendimento.php
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Atendimento Hospitalar</title>

	<?php include_once("head.php"); ?>
	<style>
		table td{
			padding: 1rem !important;
		}
		.dropdown-toggle::after{
			content:'';
		}
	</style>
	
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
	<script src="global_assets/js/demo_pages/components_dropdowns.js"></script>

	<script type="text/javascript" >
		// buscar todos os atendimento ao entrar na pagina
		
		$(document).ready(function() {
			getAtendimentos();
			
			$.fn.dataTable.moment('DD/MM/YYYY'); //Para corrigir a ordenação por data
			/* Início: Tabela Personalizada do Setor Publico */

			$('#AtendimentoTable').DataTable({
				"order": [],
			    autoWidth: false,
				responsive: true,
			    columnDefs: [{
					orderable: true,   //Data
					width: "10%",
					targets: [0]
				},
				{ 
					orderable: true,   //Espera
					width: "5%",
					targets: [1]
				},				
				{ 
					orderable: true,   //Nº Registro
					width: "5%",
					targets: [2]
				},
				{ 
					orderable: true,   //Paciente
					width: "20%",
					targets: [3]
				},
				{ 
					orderable: true,   //Profissional
					width: "10%",
					targets: [4]
				},
				{ 
					orderable: true,   //Modalidade
					width: "20%",
					targets: [5]
				},
				{ 
					orderable: true,   //Procedimento
					width: "5%",
					targets: [6]
				},
				{ 
					orderable: true,   //Situação
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
			})
			
			/* Início: Tabela Personalizada do Setor Publico */
			$('#AtendimentoTableEspera').DataTable({
				"order": [],
			    autoWidth: false,
				responsive: true,
			    columnDefs: [{
					orderable: true,   //Data
					width: "10%",
					targets: [0]
				},
				{ 
					orderable: true,   //Espera
					width: "5%",
					targets: [1]
				},				
				{ 
					orderable: true,   //Nº Registro
					width: "10%",
					targets: [2]
				},
				{ 
					orderable: true,   //Paciente
					width: "20%",
					targets: [3]
				},
				{ 
					orderable: true,   //Procedimento
					width: "10%",
					targets: [4]
				},
				{ 
					orderable: true,   //Risco
					width: "10%",
					targets: [5]
				},
				{ 
					orderable: true,   //Situação
					width: "5%",
					targets: [6]
				},
				{ 
					orderable: true,   //Ações
					width: "5%",
					targets: [7]
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

			$('#modal-close-x').on('click', ()=>{
				$('#iAtendimento').val('')
				$('#observacaoModal').html('').show()
				$('#page-modal-situacao').fadeOut(200);
			})

			/* Início: Tabela Personalizada do Setor Publico */
			$('#AtendimentoTableAtendido').DataTable({
				"order": [[ 0, "desc" ]],
			    autoWidth: false,
				responsive: true,
			    columnDefs: [{
					orderable: true,   //Data
					width: "12%",
					targets: [0]
				},
				{ 
					orderable: true,   //Espera
					width: "5%",
					targets: [1]
				},				
				{ 
					orderable: true,   //Nº Registro
					width: "10%",
					targets: [2]
				},
				{ 
					orderable: true,   //Paciente
					width: "24%",
					targets: [3]
				},
				{ 
					orderable: true,   //Procedimento
					width: "24%",
					targets: [4]
				},
				{ 
					orderable: true,   //Risco
					width: "13%",
					targets: [5]
				},
				{ 
					orderable: true,   //Situação
					width: "5%",
					targets: [6]
				},
				{ 
					orderable: true,   //Ações
					width: "5%",
					targets: [7]
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

			/* Início: Tabela Personalizada dos pacientes em atendimento */
			$('#AtendimentoTableEmAtendimento').DataTable({
				"order": [[ 0, "desc" ]],
			    autoWidth: false,
				responsive: true,
			    columnDefs: [{
					orderable: true,   //Data
					width: "10%",
					targets: [0]
				},
				{ 
					orderable: true,   //Espera
					width: "5%",
					targets: [1]
				},				
				{ 
					orderable: true,   //Nº Registro
					width: "10%",
					targets: [2]
				},
				{ 
					orderable: true,   //Paciente
					width: "20%",
					targets: [3]
				},
				{ 
					orderable: true,   //Procedimento
					width: "10%",
					targets: [4]
				},
				{ 
					orderable: true,   //Risco
					width: "10%",
					targets: [5]
				},
				{ 
					orderable: true,   //Situação
					width: "5%",
					targets: [6]
				},
				{ 
					orderable: true,   //Ações
					width: "5%",
					targets: [7]
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


			$('#cmbSituacao').on('change', ()=>{
				let cmbSituacao = $('#cmbSituacao').val()
				$('iSituacao').val(cmbSituacao)
			})

			$('#mudarSituacao').on('click', ()=>{
				$.ajax({
					type: 'POST',
					url: 'filtraAtendimento.php',
					dataType: 'json',
					data:{
						'tipoRequest': 'MUDARSITUACAO',
						'iAtendimento': $('#iAtendimento').val(),
						'iSituacao': $('#cmbSituacao').val(),
						'sObservacao': $('#observacaoModal').val()
					},
					success: function(response) {
						alerta(response.titulo, response.menssagem, response.status);
						$('#iAtendimento').val('')
						$('#observacaoModal').val('')
						$('#page-modal-situacao').fadeOut(200);
						getAtendimentos();
					},
					error: function(response) {
						alerta(response.titulo, response.menssagem, response.status);
						$('#iAtendimento').val('')
						$('#observacaoModal').val('')
						$('#page-modal-situacao').fadeOut(200);
						getAtendimentos();
					}
				});
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
			};

			_componentSelect2();
			
			/* Fim: Tabela Personalizada */
		});

		function setAttributs(){
			$('.atender').each(function(index, element){
				$(element).on('click', function(e){
					e.preventDefault()
					let iAtendimento = $(this).data('atendimento')
					let iAtendimentoEletivo = $(this).data('eletivo')
					let AtClaChave = $(this).data('clachave')
					let AtClaNome = $(this).data('clanome')
					let SituaChave = $(this).data('situachave')

					$('#iAtendimentoId').val(iAtendimento)
					$('#iAtendimentoEletivoId').val(iAtendimentoEletivo)
					$('#ClaChave').val(AtClaChave)
					$('#ClaNome').val(AtClaNome)
					$('#SituaChave').val(SituaChave)

					$('#dadosPost').attr('action', 'atendimentoEletivo.php')
					$('#dadosPost').attr('method', 'POST')
					$('#dadosPost').submit()
				})
			});

			$('.triagem').each(function(index, element){
				$(element).on('click', function(e){
					e.preventDefault()
					let iAtendimento = $(this).data('atendimento')
					let iAtendimentoEletivo = $(this).data('eletivo')
					let AtClaChave = $(this).data('clachave')
					let AtClaNome = $(this).data('clanome')
					let SituaChave = $(this).data('situachave')

					$('#iAtendimentoId').val(iAtendimento)
					$('#iAtendimentoEletivoId').val(iAtendimentoEletivo)
					$('#ClaChave').val(AtClaChave)
					$('#ClaNome').val(AtClaNome)
					$('#SituaChave').val(SituaChave)

					$('#dadosPost').attr('action', 'atendimentoTriagem.php')
					$('#dadosPost').attr('method', 'POST')
					$('#dadosPost').submit()
				})
			});

			$('.classificacao').each(function(index, element){
				$(element).on('click', function(e){
					e.preventDefault()
					let iAtendimento = $(this).data('atendimento')
					let iAtendimentoEletivo = $(this).data('eletivo')
					let AtClaChave = $(this).data('clachave')
					let AtClaNome = $(this).data('clanome')
					let SituaChave = $(this).data('situachave')

					$('#iAtendimentoId').val(iAtendimento)
					$('#iAtendimentoEletivoId').val(iAtendimentoEletivo)
					$('#ClaChave').val(AtClaChave)
					$('#ClaNome').val(AtClaNome)
					$('#SituaChave').val(SituaChave)

					$('#dadosPost').attr('action', 'atendimentoClassificacaoRisco.php')
					$('#dadosPost').attr('method', 'POST')
					$('#dadosPost').submit()
				})
			});

			$('.historico').each(function(index, element){
				$(element).on('click', function(e){
					e.preventDefault()
					let iAtendimento = $(this).data('atendimento')
					let iAtendimentoEletivo = $(this).data('eletivo')
					let AtClaChave = $(this).data('clachave')
					let AtClaNome = $(this).data('clanome')
					let SituaChave = $(this).data('situachave')
					
					$('#iAtendimentoId').val(iAtendimento)
					$('#iAtendimentoEletivoId').val(iAtendimentoEletivo)
					$('#ClaChave').val(AtClaChave)
					$('#ClaNome').val(AtClaNome)
					$('#SituaChave').val(SituaChave)

					$('#dadosPost').attr('action', 'atendimentoHistoricoPaciente.php')
					$('#dadosPost').attr('method', 'POST')
					$('#dadosPost').submit()
				})
			});

			$('.receituario').each(function(index, element){
				$(element).on('click', function(e){
					e.preventDefault()
					let iAtendimento = $(this).data('atendimento')
					let iAtendimentoEletivo = $(this).data('eletivo')
					let AtClaChave = $(this).data('clachave')
					let AtClaNome = $(this).data('clanome')
					let SituaChave = $(this).data('situachave')

					$('#iAtendimentoId').val(iAtendimento)
					$('#iAtendimentoEletivoId').val(iAtendimentoEletivo)
					$('#ClaChave').val(AtClaChave)
					$('#ClaNome').val(AtClaNome)
					$('#SituaChave').val(SituaChave)

					$('#dadosPost').attr('action', 'atendimentoReceituario.php')
					$('#dadosPost').attr('method', 'POST')
					$('#dadosPost').submit()
				})
			});

			// btn para editar ou excluir atendimento
			$('.atualizaAtendimento').each(function() {
				$(this).on('click',function(e){
					e.preventDefault()
					let atendimento = $(this).data('atendimento')

					$('#iAtendimentoId').val(atendimento)
					$('#dadosPost').attr('action', 'atendimentoNovo.php')
					$('#dadosPost').submit()
				})
			})

			$('.excluiAtendimento').each(function() {
				$(this).on('click',function(e){
					e.preventDefault()
					let atendimento = $(this).data('atendimento')
					$.ajax({
						type: 'POST',
						url: 'filtraAtendimento.php',
						dataType: 'json',
						data: {
							'tipoRequest': 'EXCLUI',
							'iAtendimento': atendimento
						},
						success: function(response) {
							if(response.status  == 'success'){
								alerta(response.titulo, response.menssagem, response.status)
								getAtendimentos()
							} else {
								alerta(response.titulo, response.menssagem, response.status)
							}
						}
					});
				})
			})
		}

		function alteraSituacao(situacao, element){
			$.ajax({
				type: 'POST',
				url: 'filtraAtendimento.php',
				dataType: 'json',
				data:{
					'tipoRequest': 'SITUACOES'
				},
				success: function(response) {
					// primeiro limpa os valores para adicionar novos evitando duplicação
					$('#cmbSituacao').empty()
					$('#iAtendimento').val('')
					$('#observacaoModal').val('')

					response.forEach(item => {
						let opt = item.SituaChave === situacao? `<option selected value="${item.id}">${item.nome}</option>`:`<option value="${item.id}">${item.nome}</option>`
						$('#cmbSituacao').append(opt)
					})
					$('#iAtendimento').val($(element).data('atendimento'))
					$('#observacaoModal').val($(element).data('observacao'))

					$('#page-modal-situacao').fadeIn(200);
				}
			});
		}
			
		//Essa função foi criada para não usar $_GET e ficar mostrando os ids via URL
		function getAtendimentos(){
			let acessoTipo = $('#sAcesso').val();
			$.ajax({
				type: 'POST',
				url: 'filtraAtendimento.php',
				dataType: 'json',
				data: {
					'tipoRequest': 'ATENDIMENTOSELETIVOS',
					'acesso': acessoTipo
				},
				success: function(response) {
					//|--Aqui é criado o DataTable caso seja a primeira vez q é executado e o clear é para evitar duplicação na tabela depois da primeira pesquisa

					let tableE = $('#AtendimentoTableEspera').DataTable().clear().draw()
					let tableA = $('#AtendimentoTableAtendido').DataTable().clear().draw()
					let tableEmAtendimento = $('#AtendimentoTableEmAtendimento').DataTable().clear().draw()
					let tableObservacao = $('#AtendimentoTableObservacao').DataTable().clear().draw()

					tableE = $('#AtendimentoTableEspera').DataTable()
					tableA = $('#AtendimentoTableAtendido').DataTable()					
					tableAtendidos = $('#AtendimentoTableEmAtendimento').DataTable()
					tableObservacao = $('#AtendimentoTableObservacao').DataTable()
					
					let rowNodeE
					let rowNodeA
					let rowNodeEmAtendimento
					let rowNodeObservacao

					console.log(response.dataEspera)

					response.dataEspera.forEach(item => {
						rowNodeE = tableE.row.add(item.data).draw().node()
						$(rowNodeE).attr('class', 'text-left')
						$(rowNodeE).find('td:eq(3)').attr('title', `Prontuário: ${item.identify.prontuario}`)
						$(rowNodeE).find('td:eq(5)').addClass('text-center')
						$(rowNodeE).find('td:eq(8)').attr('data-atendimento', `${item.identify.iAtendimento}`)
						$(rowNodeE).find('td:eq(8)').attr('data-observacao', `${item.identify.sObservacao}`)
					})					
					response.dataEmAtendimento.forEach(item => {
						rowNodeEmAtendimento = tableEmAtendimento.row.add(item.data).draw().node()
						$(rowNodeEmAtendimento).attr('class', 'text-left')
						$(rowNodeEmAtendimento).find('td:eq(3)').attr('title', `Prontuário: ${item.identify.prontuario}`)
						$(rowNodeEmAtendimento).find('td:eq(5)').addClass('text-center')
						$(rowNodeEmAtendimento).find('td:eq(8)').attr('data-atendimento', `${item.identify.iAtendimento}`)
						$(rowNodeEmAtendimento).find('td:eq(8)').attr('data-observacao', `${item.identify.sObservacao}`)
					})					
					response.dataAtendido.forEach(item => {
						rowNodeA = tableA.row.add(item.data).draw().node()
						$(rowNodeA).attr('class', 'text-left')
						$(rowNodeA).find('td:eq(3)').attr('title', `Prontuário: ${item.identify.prontuario}`)
						$(rowNodeA).find('td:eq(5)').attr('class', 'text-center')
						$(rowNodeA).find('td:eq(7)').attr('data-atendimento', `${item.identify.iAtendimento}`)
						$(rowNodeA).find('td:eq(8)').attr('data-atendimento', `${item.identify.iAtendimento}`)
						$(rowNodeA).find('td:eq(8)').attr('data-observacao', `${item.identify.sObservacao}`)
					})
					setAttributs()
				}
			});
		}

		function mudaRisco(idAtendimento, idRisco){
			$.ajax({
				type: 'POST',
				url: 'filtraAtendimento.php',
				dataType: 'json',
				data: {
					'tipoRequest': 'SETRISCO',
					'id': idAtendimento,
					'risco': idRisco
				},
				success: function(response) {
					getAtendimentos()
					alerta(response.titulo, response.menssagem, response.status);
				}
			});
		}

		function submeterAgendaMedica(){ 
			document.formAgendaMedica.submit();
		}

		$(function() {
			$('.btn-grid').click(function(){
				$('.btn-grid').removeClass('active');
				$(this).addClass('active');     
			});
		});

		function mudarGrid(grid){

			if (grid == 'espera') {
				document.getElementById("card-title").innerText = "Pacientes em Espera";
				document.getElementById("box-pacientes-espera").style.display = 'block';
				document.getElementById("box-pacientes-atendidos").style.display = 'none';
				document.getElementById("box-pacientes-atendimento").style.display = 'none';

			} else if (grid == 'atendidos') {
				document.getElementById("card-title").innerText = "Pacientes Atendidos";
				document.getElementById("box-pacientes-atendidos").style.display = 'block';
				document.getElementById("box-pacientes-espera").style.display = 'none';
				document.getElementById("box-pacientes-atendimento").style.display = 'none';

			} else if (grid == 'atendimento') {
				document.getElementById("card-title").innerText = "Pacientes em Atendimento";
				document.getElementById("box-pacientes-atendimento").style.display = 'block';
				document.getElementById("box-pacientes-espera").style.display = 'none';
				document.getElementById("box-pacientes-atendidos").style.display = 'none';				

			}
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
				<form id='dadosPost' method="POST">
					<input type='hidden' id='iAtendimentoId' name='iAtendimentoId' value='' />
					<input type='hidden' id='iAtendimentoEletivoId' name='iAtendimentoEletivoId' value='' />
					<input type='hidden' id='ClaChave' name='ClaChave' value='' />
					<input type='hidden' id='ClaNome' name='ClaNome' value='' />
					<input type='hidden' id='SituaChave' name='SituaChave' value='' />
				</form>	
				<div class="row">
					<div class="col-lg-12">
						<!-- Basic responsive configuration -->
						
						<div class="card">

							<div class="card-header header-elements-inline">
								<h3 class="card-title">ATENDIMENTO MÉDICO</h3>
							</div>

							<div class="card-body">
								<div class="row">
									<div class="col-lg-9">
										<p class="font-size-lg">A relação abaixo faz referência aos atendimentos médicos da unidade <b><?php echo $_SESSION['UnidadeNome']; ?></b></p>
									</div>

									<div class="col-lg-12">	
										<button type="button" id="pacientes-espera-btn" class="btn-grid btn btn-outline-secondary btn-lg active" onclick="mudarGrid('espera')" >Pacientes em Espera</button>
										<button type="button" id="pacientes-atendimento-btn" class="btn-grid btn btn-outline-secondary btn-lg" onclick="mudarGrid('atendimento')" >Pacientes em Atendimento</button>
										<button type="button" id="pacientes-atendidos-btn" class="btn-grid btn btn-outline-secondary btn-lg " onclick="mudarGrid('atendidos')" >Pacientes Atendidos</button>
									</div>

									<div class="col-lg-3">
										
									</div>
								</div>
							</div>

							<div class="card-body pb-1">
								<div class="row">
									<div class="col-md-9">
										<h3 class="card-title" id="card-title">Pacientes em Espera</h3>
									</div>
								</div>
							</div>

							<!-- Pacientes Espera -->
							<div id="box-pacientes-espera" style="display: block;">
								<table class="table" id="AtendimentoTableEspera">
									<thead>
										<tr class="bg-slate text-left">
											<th>Data / Hora</th>
											<th>Espera</th>
											<th>Nº Registro</th>		
											<th>Paciente</th>
											<th>Procedimento</th>
											<th>Classificação<br>de Risco</th>
											<th>Situação</th>
											<th>Ações</th>
										</tr>
									</thead>
									<tbody id="dataAtendimentos">

									</tbody>
								</table>
							</div>

							<!-- Pacientes Em Atendimento -->
							<div id="box-pacientes-atendimento" style="display: none;">
								<div class="card-body" style="padding: 0px"></div>
								<table class="table" id="AtendimentoTableEmAtendimento">
									<thead>
										<tr class="bg-slate text-left">
											<th>Data / Hora</th>
											<th>Espera</th>
											<th>Nº Registro</th>		
											<th>Paciente</th>
											<th>Procedimento</th>
											<th>Classificação<br>de Risco</th>
											<th>Situação</th>
											<th>Ações</th>
										</tr>
									</thead>
									<tbody id="dataAtendimentos">

									</tbody>
								</table>
							</div>

							<!-- Pacientes Atendidos -->
							<div id="box-pacientes-atendidos" style="display: none;">

								<div class="card-body" style="padding: 0px"></div>
								<table class="table" id="AtendimentoTableAtendido">
									<thead>
										<tr class="bg-slate text-left">
											<th>Data / Hora</th>
											<th>Espera</th>
											<th>Nº Registro</th>		
											<th>Paciente</th>
											<th>Procedimento</th>
											<th>Classif. de Risco</th>
											<th>Situação</th>
											<th>Ações</th>
										</tr>
									</thead>
									<tbody id="dataAtendimentos">

									</tbody>
								</table>
							</div>

						</div>						
						
					</div>
				</div>

				<!--Modal Editar Situação-->
                <div id="page-modal-situacao" class="custon-modal">
                    <div class="custon-modal-container" style="max-width: 300px;">
                        <div class="card custon-modal-content">
                            <div class="custon-modal-title mb-2" style="background-color: #466d96; color: #ffffff">
                                <p class="h5">Situação do Atendimento</p>
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
								<div class="text-right m-2">
									<button id="mudarSituacao" class="btn btn-principal" role="button">Confirmar</button>
								</div>
							</div>
						</div>
					</div>
				</div>
				<input id='iAtendimento' name='iAtendimento' type='hidden' value=''/>
				<?php
					echo "<input id='sAcesso' name='sAcesso' type='hidden' value='$acesso'/>"
				?>

				<!-- Agenda Médica -->
				<form name="formAgendaMedica" id="formAgendaMedica" method="POST" action="agendaMedica.php">
					<input id="inputOrigem" name="inputOrigem" type="hidden" value="atendimentoAmbulatorialListagem.php" />
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
