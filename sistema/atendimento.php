<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Atendimento';

include('global_assets/php/conexao.php');

// as duas lista "$visaoAtendente" e "$visaoProfissional" representam os perfis
// que podem ver a tela de atendimento na visão do atendente ou profissional respectivamente

$iUnidade = $_SESSION['UnidadeId'];
$usuarioId = $_SESSION['UsuarId'];
$acesso = 'ATENDIMENTO';

// as requisições são feitas ao carregar a página via AJAX no arquivo filtraAtendimento.php
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Atendimento</title>

	<?php include_once("head.php"); ?>
	<style>
		table td{
			padding: 1rem !important;
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

	<script type="text/javascript" >
		// buscar todos os atendimento ao entrar na pagina
		
		$(document).ready(function() {
			getAtendimentos();
			
			$.fn.dataTable.moment('DD/MM/YYYY'); //Para corrigir a ordenação por data
			/* Início: Tabela Personalizada do Setor Publico */
			$('#AgendamentoTable').DataTable({
				"order": [[ 0, "desc" ]],
			    autoWidth: false,
				responsive: true,
			    columnDefs: [{
					orderable: true,   //Data - hora
					width: "10%",
					targets: [0]
				},
				{ 
					orderable: true,   //Espera
					width: "5%",
					targets: [1]
				},
				{ 
					orderable: true,   //Paciente
					width: "15%",
					targets: [2]
				},
				{ 
					orderable: true,   //idade
					width: "10%",
					targets: [3]
				},
				{ 
					orderable: true,   //Profissional
					width: "15%",
					targets: [4]
				},
				{ 
					orderable: true,   //modalidade
					width: "5%",
					targets: [5]
				},
				{ 
					orderable: true,   // Procedimento
					width: "5%",
					targets: [6]
				},
				{ 
					orderable: true,   // Situacao
					width: "5%",
					targets: [7]
				},
				{ 
					orderable: true,   // acoes
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

			$('#AtendimentoTable').DataTable({
				"order": [[ 0, "desc" ]],
			    autoWidth: false,
				responsive: true,
			    columnDefs: [{
					orderable: true,   //Data - hora
					width: "15%",
					targets: [0]
				},
				{ 
					orderable: true,   // Espera
					width: "5%",
					targets: [1]
				},
				{ 
					orderable: true,   // Nº Registro
					width: "10%",
					targets: [2]
				},
				{ 
					orderable: true,   // Paciente
					width: "15%",
					targets: [3]
				},
				{ 
					orderable: true,   // Idade
					width: "10%",
					targets: [4]
				},
				{ 
					orderable: true,   //Profissional
					width: "10%",
					targets: [5]
				},
				{ 
					orderable: true,   //Modalidade
					width: "5%",
					targets: [6]
				},
				{ 
					orderable: true,   //Procedimento
					width: "5%",
					targets: [7]
				},
				{ 
					orderable: true,   // Situação
					width: "5%",
					targets: [8]
				},
				{ 
					orderable: true,   //Ações
					width: "5%",
					targets: [9]
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

			$('#SolicitacoesMedicasTable').DataTable({
				"order": [[ 0, "desc" ]],
			    autoWidth: false,
				responsive: true,
			    columnDefs: [{
					orderable: true,   //Data - hora
					width: "10%",
					targets: [0]
				},
				{ 
					orderable: true,   // Nº Registro
					width: "10%",
					targets: [1]
				},
				{ 
					orderable: true,   // Paciente
					width: "15%",
					targets: [2]
				},
				{ 
					orderable: true,   // Idade
					width: "10%",
					targets: [3]
				},
				{ 
					orderable: true,   //Profissional
					width: "15%",
					targets: [4]
				},
				{ 
					orderable: true,   //conduta
					width: "15%",
					targets: [5]
				},
				{ 
					orderable: true,   // Situação
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
			
			/* Início: Tabela Personalizada do Setor Publico */
			$('#AtendimentoTableEspera').DataTable({
				"order": [[ 0, "desc" ]],
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
					orderable: true,   //Espera
					width: "5%",
					targets: [2]
				},				
				{ 
					orderable: true,   //Nº Registro
					width: "5%",
					targets: [3]
				},
				{ 
					orderable: true,   //Prontuário
					width: "5%",
					targets: [4]
				},
				{ 
					orderable: true,   //Paciente
					width: "20%",
					targets: [5]
				},
				{ 
					orderable: true,   //Procedimento
					width: "10%",
					targets: [6]
				},
				{ 
					orderable: true,   //Risco
					width: "20%",
					targets: [7]
				},
				{ 
					orderable: true,   //Situação
					width: "5%",
					targets: [8]
				},
				{ 
					orderable: true,   //Ações
					width: "5%",
					targets: [9]
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
				$('#justificativaModal').html('').show()
				$('#page-modal-situacao').fadeOut(200);
			})
			$('#modal-auditoria-close-x').on('click', ()=>{
				$('#dataAuditoria').html('')
				$('#page-modal-auditoria').fadeOut(200);
			})

			/* Início: Tabela Personalizada do Setor Publico */
			$('#AtendimentoTableAtendido').DataTable({
				"order": [[ 0, "desc" ]],
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
					orderable: true,   //Espera
					width: "5%",
					targets: [2]
				},				
				{ 
					orderable: true,   //Nº Registro
					width: "5%",
					targets: [3]
				},
				{ 
					orderable: true,   //Prontuário
					width: "5%",
					targets: [4]
				},
				{ 
					orderable: true,   //Paciente
					width: "20%",
					targets: [5]
				},
				{ 
					orderable: true,   //Procedimento
					width: "10%",
					targets: [6]
				},
				{ 
					orderable: true,   //Risco
					width: "20%",
					targets: [7]
				},
				{ 
					orderable: true,   //Situação
					width: "5%",
					targets: [8]
				},
				{ 
					orderable: true,   //Ações
					width: "5%",
					targets: [9]
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
				if(!$('#justificativaModal').val()){
					alerta('Justificativa!', 'Informe a justificativa!!', 'error')
					return
				}
				$.ajax({
					type: 'POST',
					url: 'filtraAtendimento.php',
					dataType: 'json',
					data:{
						'tipoRequest': 'MUDARSITUACAO',
						'tipo': $('#AtendimentoAgendamento').val(),
						'iAtendimento': $('#iAtendimento').val(),
						'iSituacao': $('#cmbSituacao').val(),
						'sJustificativa': $('#justificativaModal').val()
					},
					success: function(response) {
						alerta(response.titulo, response.menssagem, response.status);
						$('#iAtendimento').val('')
						$('#justificativaModal').val('')
						$('#page-modal-situacao').fadeOut(200);
						getAtendimentos();
					},
					error: function(response) {
						alerta(response.titulo, response.menssagem, response.status);
						$('#iAtendimento').val('')
						$('#justificativaModal').val('')
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

			$(function() {
				$('.btn-grid').click(function(){
					$('.btn-grid').removeClass('active');
					$(this).addClass('active');     
				});
			});

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
		}

		function newAtendimento(element){
			$('#AtendimentoAgendamento').val($(element).data('tipo'))
			$('#iAtendimentoId').val($(element).data('id'))
			$('#dadosPost').attr('action','atendimentoEdita.php')
			$('#dadosPost').submit()
		}

		function auditoria(element){
			$.ajax({
				type: 'POST',
				url: 'filtraAtendimento.php',
				dataType: 'json',
				data: {
					'tipoRequest': 'AUDITORIA',
					'tipo':$(element).data('tipo'),
					'id':$(element).data('id'),
				},
				success: async function(response) {
					$('#dataAuditoria').empty()
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
			});
		}

		function atualizaAtendimento(element){
			let Id
			if($(element).data('tipo')=='ATENDIMENTO'){
				Id = $(element).data('atendimento')
				$('#dadosPost').attr('action','atendimentoEdita.php')
			}else{
				Id = $(element).data('agendamento')
				$('#dadosPost').attr('action','agendamentoEdita.php')
			}
			$('#iAtendimentoId').val(Id)
			$('#AtendimentoAgendamento').val($(element).data('tipo'))

			$('#dadosPost').submit()
		}

		function excluiAtendimento(element){
			let url = $(element).data('tipo')=='ATENDIMENTO'?'filtraAtendimento.php':'filtraAgendamento.php'
			let text = $(element).data('tipo')=='ATENDIMENTO'?'Excluir atendimento?':'Excluir agendamento?'
			let id = $(element).data('tipo')=='ATENDIMENTO'?$(element).data('atendimento'):$(element).data('agendamento')
			// (url, texto, tipoRequest, id)
			confirmaExclusaoAjax(url, text, 'EXCLUI', id, getAtendimentos)
		}

		function alteraSituacao(situacao, element){
			$.ajax({
				type: 'POST',
				url: 'filtraAtendimento.php',
				dataType: 'json',
				data:{
					'tipoRequest': 'SITUACOES',
					'tipo': $(element).data('tipo')
				},
				success: function(response) {
					// primeiro limpa os valores para adicionar novos evitando duplicação
					$('#cmbSituacao').empty()
					$('#iAtendimento').val('')
					$('#justificativaModal').val('')

					$('#tituloModalSituacao').html('')
					let titulo = $(element).data('tipo') == 'ATENDIMENTO'?'Situação do Atendimento':'Situação do Agendamento'
					$('#tituloModalSituacao').html(titulo)

					response.forEach(item => {
						let opt = item.SituaChave === situacao? `<option selected value="${item.id}">${item.nome}</option>`:`<option value="${item.id}">${item.nome}</option>`
						$('#cmbSituacao').append(opt)
					})
					$('#iAtendimento').val($(element).data('id'))
					$('#justificativaModal').val($(element).data('observacao'))
					$('#AtendimentoAgendamento').val($(element).data('tipo'))

					$('#page-modal-situacao').fadeIn(200)
				}
			});
		}
			
		//Essa função foi criada para não usar $_GET e ficar mostrando os ids via URL
		function getAtendimentos(){
			$.ajax({
				type: 'POST',
				url: 'filtraAtendimento.php',
				dataType: 'json',
				data: {
					'tipoRequest': 'ATENDIMENTOS'
				},
				success: async function(response) {
					//|--Aqui é criado o DataTable caso seja a primeira vez q é executado e o clear é para evitar duplicação na tabela depois da primeira pesquisa

					$('#AgendamentoTable').DataTable().clear().draw()

					tableAgendamento = $('#AgendamentoTable').DataTable()
					let rowNodeAgendamento

					await response.dataAgendamento.forEach(item => {
						rowNodeAgendamento = tableAgendamento.row.add(item.data).draw().node()
						$(rowNodeAgendamento).attr('class', 'text-left')

						// esse trecho serve para o link no nome do paciente
						$(rowNodeAgendamento).find('td:eq(2)').addClass('text-primary')
						$(rowNodeAgendamento).find('td:eq(2)').attr('style', 'cursor: pointer;')
						$(rowNodeAgendamento).find('td:eq(2)').attr('title', `Transformar o agendamento em atendimento \n${item.identify.prontuario}`)
						$(rowNodeAgendamento).find('td:eq(2)').attr('data-id', `${item.identify.id}`)
						$(rowNodeAgendamento).find('td:eq(2)').attr('data-tipo', 'AGENDAMENTO')
						$(rowNodeAgendamento).find('td:eq(2)').attr('onclick', 'newAtendimento(this)')
						// <end>
						$(rowNodeAgendamento).find('td:eq(4)').attr('title', `${item.identify.cbo}`)

						// esse trecho serve para o atributos no campo situação de cada linha
						$(rowNodeAgendamento).find('td:eq(7)').attr('data-id', `${item.identify.id}`)
						$(rowNodeAgendamento).find('td:eq(7)').attr('data-observacao', `${item.identify.sJustificativa}`)
						$(rowNodeAgendamento).find('td:eq(7)').attr('data-tipo', 'AGENDAMENTO')
						$(rowNodeAgendamento).find('td:eq(7)').attr('onclick', `alteraSituacao('${item.identify.situacao}', this)`)
						// <end>
					})

					$('#AtendimentoTable').DataTable().clear().draw()

					tableAtendimento = $('#AtendimentoTable').DataTable()
					let rowNodeAtendimento

					await response.dataAtendimento.forEach(item => {
						rowNodeAtendimento = tableAtendimento.row.add(item.data).draw().node()

						// $(rowNodeAtendimento).attr('style',`border-left: 3px solid ${item.identify.classCor};`)

						if(item.identify.class){
							$(rowNodeAtendimento).find('td:eq(0)').attr('style',`border-left: 10px solid ${item.identify.classCor};`)
							$(rowNodeAtendimento).find('td:eq(0)').attr('title',`${item.identify.class}\n${item.identify.classDeterminante}`)
						}

						$(rowNodeAtendimento).find('td:eq(3)').attr('title', item.identify.prontuario)
						$(rowNodeAtendimento).find('td:eq(5)').attr('title', item.identify.cbo)

						// esse trecho serve para o atributos no campo situação de cada linha
						$(rowNodeAtendimento).attr('class', 'text-left')
						$(rowNodeAtendimento).find('td:eq(8)').attr('data-id', `${item.identify.iAtendimento}`)
						$(rowNodeAtendimento).find('td:eq(8)').attr('data-observacao', `${item.identify.sJustificativa}`)
						$(rowNodeAtendimento).find('td:eq(8)').attr('data-tipo', 'ATENDIMENTO')

						// essa opção de alterar situação só vai estar disponível caso o status seja "Em espera" ou "Liberado"
						if(item.identify.situacao == "EMESPERA" || item.identify.situacao == "LIBERADO"){
							$(rowNodeAtendimento).find('td:eq(8)').attr('onclick', `alteraSituacao('${item.identify.situacao}', this)`)
						}
						// <end>
					})

					$('#contadorSolicitacoes').text(response.contadorSolicitacoes)
					$('#SolicitacoesMedicasTable').DataTable().clear().draw()

					tableSolicitacao = $('#SolicitacoesMedicasTable').DataTable()
					let rowNodeSolicitacao

					await response.dataSolicitacao.forEach(item => {
						rowNodeSolicitacao = tableSolicitacao.row.add(item.data).draw().node()

						$(rowNodeSolicitacao).find('td:eq(2)').attr('title', item.identify.prontuario)
						$(rowNodeSolicitacao).find('td:eq(4)').attr('title', item.identify.cbo)

						// esse trecho serve para o atributos no campo situação de cada linha
						$(rowNodeSolicitacao).attr('class', 'text-left')
						$(rowNodeSolicitacao).find('td:eq(6)').attr('data-id', `${item.identify.id}`)
						$(rowNodeSolicitacao).find('td:eq(6)').attr('data-tipo', 'SOLICITACAO')

						$(rowNodeSolicitacao).find('td:eq(7)').attr('class', 'text-center')

						// <end>
					})

					setAttributs()
				}
			});
		}

		function submeterAgendaMedica(){ 
			document.formAgendaMedica.submit();
		}

		function mudarGrid(grid){

			if (grid == 'atendimentos') {
				document.getElementById("box-atendimentos").style.display = 'block';
				document.getElementById("box-agendamentos").style.display = 'none';
				document.getElementById("box-solicitacoes-medicas").style.display = 'none';
			}
			if (grid == 'agendamentos') {
				document.getElementById("box-agendamentos").style.display = 'block';
				document.getElementById("box-atendimentos").style.display = 'none';	
				document.getElementById("box-solicitacoes-medicas").style.display = 'none';

			}
			if (grid == 'solicitacoes-medicas') {
				document.getElementById("box-solicitacoes-medicas").style.display = 'block';
				document.getElementById("box-agendamentos").style.display = 'none';
				document.getElementById("box-atendimentos").style.display = 'none';			
			}

		}

		function adimissaoLeito(id){
			$('#iAtendimentoId').val(id)
			$('#dadosPost').attr('action', 'atendimentoAdmissaoLeito.php')
			$('#dadosPost').submit()
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
					<input type='hidden' id='AtendimentoAgendamento' name='AtendimentoAgendamento' value='' />
					<input type='hidden' id='iAtendimentoEletivoId' name='iAtendimentoEletivoId' value='' />
					<input type='hidden' id='ClaChave' name='ClaChave' value='' />
					<input type='hidden' id='ClaNome' name='ClaNome' value='' />
				</form>
				<!-- Visão Atendente -->		
				<div class="row">
					<div class="col-lg-12">
						<!-- Basic responsive configuration -->
						<div class="card">
							<div class="card-body">
								<div class="row">
									<div class="col-lg-7">
										<p class="font-size-lg">A relação abaixo faz referência aos atendimentos da unidade <b><?php echo $_SESSION['UnidadeNome']; ?></b></p>
									</div>
									<div class="col-lg-5 text-right">
										<div class="text-right">
											<?php 
												echo $inserir?"<a href='atendimentoNovo.php' class='btn btn-principal' role='button'>Novo Atendimento</a>":"";
											?>
											<a href="gastosAdicionais.php" class="btn btn-primary" role="button">Gastos Adicionais</a>
											
											<a href="#collapse-imprimir-relacao" class="btn bg-slate-700 btn-icon" role="button" data-toggle="collapse" data-placement="bottom" data-container="body">
												<i class="icon-printer2"></i>																						
											</a>
										</div>
									</div>
									
									<div class="col-lg-12">	
										<button type="button" id="pacientes-espera-btn" class="btn-grid btn btn-outline-secondary btn-lg active" onclick="mudarGrid('agendamentos')" >Agendamentos do Dia</button>
										<button type="button" id="pacientes-atendidos-btn" class="btn-grid btn btn-outline-secondary btn-lg " onclick="mudarGrid('atendimentos')" >Atendimentos</button>

										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-grid btn-lg btn-outline-secondary " onclick="mudarGrid('solicitacoes-medicas')">
												<input type="radio" autocomplete="off"  > Solicitações Médicas 
											</label>
											<label id="contadorSolicitacoes" class="btn btn-lg btn-success" style="padding-left: 3px; padding-right: 3px"> - </label>
										</div>
										
									</div>

								</div>
							</div>							

							<!-- Agendamentos -->
							<div id="box-agendamentos" style="display: block;">
								<table class="table" id="AgendamentoTable">
									<thead>
										<tr class="bg-slate text-left">
											<th>Data / Hora</th>
											<th>Espera</th>
											<th>Paciente</th>
											<th>Idade</th>
											<th>Profissional</th>
											<th>Modalidade</th>
											<th>Procedimento</th>
											<th>Situação</th>
											<th class="text-center">Ações</th>
										</tr>
									</thead>
									<tbody>

									</tbody>
								</table>
							</div>

							<!-- Atendimentos -->
							<div id="box-atendimentos" style="display: none;">

								<div class="card-body" style="padding: 0px"></div>
								<table class="table" id="AtendimentoTable">
									<thead style="border-left: 10px solid #466d96">
										<tr class="bg-slate text-left">
											<th>Data / Hora</th>
											<th>Espera</th>
											<th>Nº Registro</th>
											<th>Paciente</th>
											<th>Idade</th>
											<th>Profissional</th>
											<th>Modalidade</th>
											<th>Procedimento</th>
											<th>Situação</th>
											<th class="text-center">Ações</th>
										</tr>
									</thead>
									<tbody>

									</tbody>
								</table>
							</div>

							<!-- Solicitacoes Medicas -->
							<div id="box-solicitacoes-medicas" style="display: none;">

								<div class="card-body" style="padding: 0px"></div>
								<table class="table" id="SolicitacoesMedicasTable">
									<thead style="border-left: 10px solid #466d96">
										<tr class="bg-slate text-left">
											<th>Data / Hora</th>											
											<th>Nº Registro</th>
											<th>Paciente</th>
											<th>Idade</th>
											<th>Profissional</th>
											<th>Conduta</th>
											<th>Situação</th>
											<th class="text-center">Ações</th>
										</tr>
									</thead>
									<tbody>

									</tbody>
								</table>
							</div>

						<!-- FIM DIV CARD -->
						</div>														
					</div>
				</div>

				<!--Modal Editar Situação-->
                <div id="page-modal-situacao" class="custon-modal">
                    <div class="custon-modal-container" style="max-width: 300px;">
                        <div class="card custon-modal-content">
                            <div class="custon-modal-title mb-2" style="background-color: #466d96; color: #ffffff">
                                <p class="h5" id="tituloModalSituacao">Situação do Atendimento</p>
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
														<label>Justificativa <span class="text-danger">*</span></label>
													</div>

													<!-- campos -->
													<div class="col-lg-12">
														<textarea id="justificativaModal" name="justificativaModal" class="form-control" placeholder="Justificativa"></textarea>
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
				<input id='iAtendimento' name='iAtendimento' type='hidden' value=''/>
				<!-- Agenda Médica -->
				<form name="formAgendaMedica" id="formAgendaMedica" method="POST" action="agendaMedica.php">
					<input id="inputOrigem" name="inputOrigem" type="hidden" value="atendimento.php" />
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
