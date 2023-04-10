<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Atendimento Ambulatorial';
$_SESSION['UltimaPagina'] = 'AMBULATORIAL';


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
	<title>Lamparinas | Atendimento Ambulatorial</title>

	<?php include_once("head.php"); ?>
	<style>
		table td{
			padding: 1rem !important;
		}
		.dropdown-toggle::after{
			content:'';
		}

		.cardLeitos i {
			font-size: 60px;
			margin: 20px;
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

	<script type="text/javascript" >
		// buscar todos os atendimento ao entrar na pagina
		
		$(document).ready(function() {
			getAtendimentos();
			
			$.fn.dataTable.moment('DD/MM/YYYY'); //Para corrigir a ordenação por data
			/* Início: Tabela Personalizada do Setor Publico */

			$('#AtendimentoTable').DataTable({
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
					width: "15%",
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
					width: "15%",
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
					width: "15%",
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


			/* Início: Tabela Personalizada dos atendimentos em observacao */
			$('#AtendimentoTableObservacao').DataTable({
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
					width: "15%",
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
		})

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

					$('#dadosPost').attr('action', 'atendimentoAmbulatorial.php')
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
					'tipoRequest': 'ATENDIMENTOSAMBULATORIAIS',
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

					response.dataEspera.forEach(item => {
						rowNodeE = tableE.row.add(item.data).draw().node()
						$(rowNodeE).attr('class', 'text-left')
						$(rowNodeE).find('td:eq(3)').attr('title', `Prontuário: ${item.identify.prontuario}`)
						$(rowNodeE).find('td:eq(8)').attr('data-atendimento', `${item.identify.iAtendimento}`)
						$(rowNodeE).find('td:eq(8)').attr('data-observacao', `${item.identify.sObservacao}`)
					})
					response.dataEmAtendimento.forEach(item => {
						rowNodeEmAtendimento = tableEmAtendimento.row.add(item.data).draw().node()
						$(rowNodeEmAtendimento).attr('class', 'text-left')
						$(rowNodeEmAtendimento).find('td:eq(3)').attr('title', `Prontuário: ${item.identify.prontuario}`)
						$(rowNodeEmAtendimento).find('td:eq(8)').attr('data-atendimento', `${item.identify.iAtendimento}`)
						$(rowNodeEmAtendimento).find('td:eq(8)').attr('data-observacao', `${item.identify.sObservacao}`)
					})
					$('#contadorEmObservacao').text(response.contadorEmObservacao)
					response.dataObservacao.forEach(item => {
						rowNodeObservacao = tableObservacao.row.add(item.data).draw().node()
						$(rowNodeObservacao).attr('class', 'text-left')
						$(rowNodeObservacao).find('td:eq(3)').attr('title', `Prontuário: ${item.identify.prontuario}`)
						$(rowNodeObservacao).find('td:eq(8)').attr('data-atendimento', `${item.identify.iAtendimento}`)
						$(rowNodeObservacao).find('td:eq(8)').attr('data-observacao', `${item.identify.sObservacao}`)
					})
					response.dataAtendido.forEach(item => {
						rowNodeA = tableA.row.add(item.data).draw().node()
						$(rowNodeA).attr('class', 'text-left')
						$(rowNodeA).find('td:eq(3)').attr('title', `Prontuário: ${item.identify.prontuario}`)
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
		})
		$(function() {
			$('.btn-grid2').click(function(){
				$('.btn-grid2').removeClass('active');
				$(this).addClass('active');     
			});
		})

		function mudarGrid(grid){

			if (grid == 'espera') {
				document.getElementById("box-pacientes-espera").style.display = 'block';
				document.getElementById("box-pacientes-atendidos").style.display = 'none';
				document.getElementById("box-pacientes-observacao").style.display = 'none';
				document.getElementById("box-pacientes-atendimento").style.display = 'none';

			} else if (grid == 'atendidos') {
				document.getElementById("box-pacientes-atendidos").style.display = 'block';
				document.getElementById("box-pacientes-espera").style.display = 'none';
				document.getElementById("box-pacientes-observacao").style.display = 'none';
				document.getElementById("box-pacientes-atendimento").style.display = 'none';

			} else if (grid == 'observacao') {
				document.getElementById("box-pacientes-observacao").style.display = 'block';
				document.getElementById("box-pacientes-espera").style.display = 'none';
				document.getElementById("box-pacientes-atendidos").style.display = 'none';
				document.getElementById("box-pacientes-atendimento").style.display = 'none';

			}  else if (grid == 'atendimento') {
				document.getElementById("box-pacientes-atendimento").style.display = 'block';
				document.getElementById("box-pacientes-espera").style.display = 'none';
				document.getElementById("box-pacientes-atendidos").style.display = 'none';				
				document.getElementById("box-pacientes-observacao").style.display = 'none';

			}
		}

		function mudarGridEspecialidade(especialidade){
			$('.box-especialidade').css('display','none');
			document.getElementById(especialidade).style.display = 'block';
		}
		function entrar(id){
			$('#iAtendimentoId').val(id)
			$('#dadosPost').attr('action', 'atendimentoAmbulatorial.php');
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
					<input type='hidden' id='iAtendimentoEletivoId' name='iAtendimentoEletivoId' value='' />
					<input type='hidden' id='ClaChave' name='ClaChave' value='' />
					<input type='hidden' id='ClaNome' name='ClaNome' value='' />
					<input type='hidden' id='SituaChave' name='SituaChave' value='' />
				</form>
				<div class="row">
					<div class="col-lg-12">
							
						<div class="card">

							<div class="card-header header-elements-inline">
								<h3 class="card-title">ATENDIMENTO AMBULATORIAL</h3>
							</div>

							<div class="card-body">
								<div class="row">									
									<div class="col-lg-12" >
										<p class="font-size-lg">A relação abaixo faz referência aos atendimentos ambulatoriais da unidade <b><?php echo $_SESSION['UnidadeNome']; ?></b></p>
									</div>

									<div class="col-lg-12">	
										<button type="button" id="pacientes-espera-btn" class="btn-grid btn btn-outline-secondary btn-lg active" onclick="mudarGrid('espera')" >Pacientes em Espera</button>
										<button type="button" id="pacientes-atendimento-btn" class="btn-grid btn btn-outline-secondary btn-lg" onclick="mudarGrid('atendimento')" >Pacientes em Atendimento</button>
										
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-grid btn-lg btn-outline-secondary " onclick="mudarGrid('observacao')">
												<input type="radio" autocomplete="off"  > Pacientes em Observação 
											</label>
											<label id="contadorEmObservacao" class="btn btn-lg btn-success" style="padding-left: 3px; padding-right: 3px"> - </label>
										</div>
										
										<button type="button" id="pacientes-atendidos-btn" class="btn-grid btn btn-outline-secondary btn-lg " onclick="mudarGrid('atendidos')" >Pacientes Atendidos</button>
									</div>
								</div>
							</div>

							

							<!-- Pacientes Espera -->
							<div id="box-pacientes-espera" style="display: block;">

								<div class="card-body" style="padding: 0px"></div>
								
								<div class="card-body pb-1">
									<div class="row">
										<div class="col-md-9">
											<h3 class="card-title" id="card-title">Pacientes em Espera</h3>
										</div>
									</div>	
								</div>
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
								<div class="card-body pb-1">
									<div class="row">
										<div class="col-md-9">
											<h3 class="card-title" id="card-title">Pacientes em Atendimento</h3>
										</div>
									</div>	
								</div>

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

							<!-- Pacientes Em Observacao -->
							<div id="box-pacientes-observacao" style="display: none;">

								<hr /> 
								
								<div class="card-body pb-1">

									<div class="" style="">
										<div class="row">
											<div class="col-md-9">
												<h3 class="card-title" id="card-title">Pacientes em Observação</h3>
											</div>
										</div>	
									</div>

									<div class="" style="float: right; margin-top: -40px">
										<div >
											<div class="" style="float: left">
												Leitos Ocupados:	
											</div>
											<div class="form-group text-white text-bold leitosOcupados" style="float: left; margin-left: 5px; margin-Top: -5px; height: 30px; width: 30px; background-color:blue; border-radius: 50px; display: flex;justify-content: center;align-items: center;" >
												10
											</div>

										</div>
										<div>
											<div class="" style="float: left;">
												Leitos Livres:
											</div>
											<div class="form-group text-white text-bold leitosLivres " style="float: right; margin-left: 5px; margin-Top: -5px; height: 30px; width: 30px; background-color:green; border-radius: 50px; display: flex;justify-content: center;align-items: center;" >
												10
											</div>
										</div>
									</div>
									
									<div class="col-lg-12 mb-2 pl-0 ml-0">	
										<?php
											$sql = "SELECT *
											FROM EspecialidadeLeito
											JOIN Situacao on SituaId = EsLeiStatus
											LEFT JOIN EspecialidadeLeitoXClassificacao ON EsLeiId = ELXClEspecialidadeLeito
											WHERE SituaChave = 'ATIVO' AND EsLeiUnidade = $iUnidade
											AND ELXClClassificacao = 'A'
											ORDER BY EsLeiNome ASC" ;
											$result = $conn->query($sql);

											foreach ($result as $key => $item) {
												$active = $key == 0 ? 'active' : '';
												echo "<button type='button' id='pacientes-espera-btn' class=' m-1 btn-grid2 btn btn-outline-secondary btn-lg " . $active . " ' onclick='mudarGridEspecialidade(`boxEspecialidade". $item['EsLeiId'] . "`)'  >" . $item['EsLeiNome'] . "</button>";
											}
										?>
									</div>

								</div>

								<?php

									$leitosOcupados = 0;
									$leitosTotais = 0;

									$sql = "SELECT *
									FROM EspecialidadeLeito
									JOIN Situacao on SituaId = EsLeiStatus
									LEFT JOIN EspecialidadeLeitoXClassificacao ON EsLeiId = ELXClEspecialidadeLeito
									WHERE SituaChave = 'ATIVO' AND EsLeiUnidade = $iUnidade
									AND ELXClClassificacao = 'A'
									ORDER BY EsLeiNome ASC" ;
									$result = $conn->query($sql);
									
									foreach ($result as $key => $item) {
							
										$sql = "SELECT DISTINCT QuartId, QuartNome
										FROM Quarto
										left JOIN VincularLeito ON VnLeiQuarto = QuartId
										left JOIN Situacao on SituaId = QuartStatus
										LEFT JOIN EspecialidadeLeito ON EsLeiId = VnLeiEspecialidadeLeito
										LEFT JOIN EspecialidadeLeitoXClassificacao ON EsLeiId = ELXClEspecialidadeLeito
										WHERE  SituaChave = 'ATIVO'	AND QuartUnidade = $iUnidade
										AND ELXClClassificacao = 'A'
										AND EsLeiId = $item[EsLeiId]
										ORDER BY QuartNome ASC" ;
										$resultQuarto = $conn->query($sql);

										$display = $key == 0 ? 'block' : 'none';

										echo "<div class='box-especialidade' id='boxEspecialidade" . $item['EsLeiId'] . "' style='display: " . $display . ";'>";
											
											foreach($resultQuarto as $itemQuarto){	
								
												$sql = "SELECT * FROM Leito 
												LEFT JOIN AtendimentoXLEito ON LeitoId = AtXLeLeito
												LEFT JOIN Atendimento ON AtendId = AtXLeAtendimento
												LEFT JOIN Cliente ON ClienId = AtendCliente
												LEFT JOIN Situacao ON SituaId = AtendSituacao
												where LeitoQuarto = $itemQuarto[QuartId]";
												$resultLeitos = $conn->query($sql);
												
												echo "<div class='card-header header-elements-inline ' style='margin-bottom: -30px' >
													<h3 class='card-title' >" . $itemQuarto['QuartNome'] . "</h3>
													<hr />
												</div >";

												echo "<hr style='border-color:#aaa;box-sizing:border-box;width:97%;'/>";

												echo "<div class='row' >";

													foreach ($resultLeitos as $key => $item) {

														$leitosTotais += 1;
														
														if ($item['SituaChave'] == 'EMOBSERVACAO') {

															$leitosOcupados += 1;

															$data = explode(" ", $item['AtXLeDataHoraInicio'])[0];

															echo " <div class='col-lg-3 col-md-6 col-sm-12' >
																<div class='card-body'>							
																	<div class='card cardLeitos text-center ' style='width: 18rem; '>											
																		<div class='card-header' style='color: white; background-color: #466D96; padding: 5px'>
																			<h3 class=' m-0'> " . $item['LeitoNome'] . "</h3>
																			<p class='m-0'>Previsão de alta: --/--/----</p>
																		</div>

																		<div class='card-body'>
																			<div class='m-3'><img src='global_assets/images/lamparinas/leito-ocupado.png' alt='Leito ocupado' width='80' height='80'></div>
																			<h4 class='card-title'>" . $item['ClienNome'] . "</h4>
																			<p class='card-text mb-1'>Nrº do Registro: " . $item['AtendNumRegistro'] . "</p>
																			<p class='card-text'>Data da Internação: " . mostraData($data) . "</p>
																			<button onclick='entrar(" . $item['AtendId'] . ")' type='button' class='btn btn-principal btn-sm'>Entrar</button>
																		</div>
																	</div>
																</div>
															</div> ";

														} else {

															echo "<div class='col-lg-3 col-md-6 col-sm-12' >
																<div class='card-body'>							
																	<div class='card cardLeitos text-center ' style='width: 18rem; '>											
																		<div class='card-header' style='color: white; background-color: #466D96; padding: 5px'>
																			<h3 class=' m-0'> " . $item['LeitoNome'] . "</h3>
																			<p class='m-0'>Previsão de alta: --/--/----</p>
																		</div>

																		<div class='card-body'>
																			<div class='m-3'><img src='global_assets/images/lamparinas/leito-vazio.png' alt='Leito vazio' width='80' height='80'></div>
																			<h4 class='card-title'>LEITO VAZIO</h4>
																			<p class='card-text mb-1'>Nrº do Registro: ----</p>
																			<p class='card-text'>Data da Internação: --/--/----</p>
																			<button type='button' class='btn btn-sm' disabled >Entrar</button>
																		</div>
																	</div>
																</div>
															</div> ";
														}

													}

												echo "</div>";												
												
											}

										echo "</div>";
									}

									echo "<script>
										$('.leitosOcupados').html(" . $leitosOcupados . ");
										$('.leitosLivres').html(" . ( $leitosTotais - $leitosOcupados) . ");
									</script>";

								?>

							</div>

							<!-- Pacientes Atendidos -->
							<div id="box-pacientes-atendidos" style="display: none;">

								<div class="card-body" style="padding: 0px"></div>

								<div class="card-body pb-1">
									<div class="row">
										<div class="col-md-9">
											<h3 class="card-title" id="card-title">Pacientes Atendidos</h3>
										</div>
									</div>	
								</div>

								<table class="table" id="AtendimentoTableAtendido">
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
