<?php

include_once("sessao.php");

//$inicio1 = microtime(true);

$_SESSION['PaginaAtual'] = 'Processos Licitatórios';

include('global_assets/php/conexao.php');

$empresa = $_SESSION['EmpreId'];
$unidade = $_SESSION['UnidadeId'];
$perfil = $_SESSION['PerfiChave'];
$userId = $_SESSION['UsuarId'];
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Processos Licitatório</title>

	<?php include_once("head.php"); ?>

	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/tables/datatables/datatables.min.js"></script>
	<script src="global_assets/js/plugins/tables/datatables/extensions/responsive.min.js"></script>

	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>

	<script src="global_assets/js/demo_pages/datatables_responsive.js"></script>
	<script src="global_assets/js/demo_pages/datatables_sorting.js"></script>
	
	<!-- Não permite que o usuário retorne para o EDITAR -->
	<script src="global_assets/js/lamparinas/stop-back.js"></script>

	<!-- Plugin para corrigir a ordenação por data. Caso a URL dê problema algum dia, salvei esses 2 arquivos na pasta global_assets/js/lamparinas -->
	<script type="text/javascript" language="javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.8.4/moment.min.js"></script>
	<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/plug-ins/1.10.10/sorting/datetime-moment.js"></script>

	<script src="global_assets/js/plugins/notifications/bootbox.min.js"></script>		

	<script type="text/javascript">
	
		$(document).ready(function() {

			$.fn.dataTable.moment('DD/MM/YYYY'); //Para corrigir a ordenação por data			

			/* Início: Tabela Personalizada */
			$('#tblProcessos').DataTable({
				"order": [
					[0, "desc"], [1, "desc"]
				],
				autoWidth: false,
				responsive: true,
				columnDefs: [
					{
						orderable: true, //Nº TR
						width: "15%",
						targets: [0]
					},
					{
						orderable: true, //Data
						width: "10%",
						targets: [1]
					},
					{
						orderable: true, //Especie
						width: "15%",
						targets: [2]
					},
					{
						orderable: true, //Tipo
						width: "20%",
						targets: [3]
					},
					{
						orderable: true, //Categoria
						width: "20%",
						targets: [4]
					},
					{
						orderable: true, //Status
						width: "10%",
						targets: [5]
					},
					{
						orderable: true, //Situação
						width: "5%",
						targets: [6]
					},
					{
						orderable: false, //Ações
						width: "5%",
						targets: [7]
					}
				],
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

			getProcessos()
		});
		function getProcessos(){
			$.ajax({
				type: 'POST',
				url: 'filtraProcessoLicitatorio.php',
				dataType: 'json',
				data: {
					'tipoRequest':'PROCESSOS'
				},
				success: function(response) {
					let table = $('#tblProcessos').DataTable().clear().draw()

					table = $('#tblProcessos').DataTable()
					let rowNode

					response.forEach(item => {
						rowNode = table.row.add(item.data).draw().node()
						$(rowNode).find('td:eq(6)').attr('class', `badge badge-flat border-${item.identify.cor} text-${item.identify.cor}`)
						$(rowNode).find('td:eq(7)').attr('class', 'text-center')
					})
				}
			})
		}
		function editarProcesso(id){
			$('#iProcesso').val(id)
			$('#formEdita').submit()
		}
		function excluiProcesso(id){
			confirmaExclusaoAjax('filtraProcessoLicitatorio.php','Deseja excluir o processo em questão?','DELPROCESSO',id,getProcessos)
		}
	</script>

</head>

<body class="navbar-top">

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
								<h5 class="card-title">Relação dos Processos Licitatórios</h5>
							</div>

							<form id="formEdita" method="POST" action="processoEdita.php">
								<input id='iProcesso' name='iProcesso' type='hidden' value='' />
							</form>

							<div class="card-body">
								<div class="row">
									<div class="col-lg-9">
										A relação abaixo faz referência aos processos de licitatórios da Empresa <b><?php echo $_SESSION['UnidadeNome']; ?></b>
									</div>
									<div class="text-right"><a href="processoNovo.php" class="btn btn-principal" role="button">Novo Processo Licitatório</a></div>
								</div>
							</div>

							<table class="table" id="tblProcessos">
								<thead>
									<tr class="bg-slate">
										<th>Nº Processo</th>
										<th>Data</th>
										<th>Espécie</th>
										<th>Tipo</th>
										<th>Categoria</th>
										<th>Status</th>
										<th class="text-center">Situação</th>
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

				<!-- /info blocks -->

				<form name="formTR" method="post">
					<input type="hidden" id="inputPermission" name="inputPermission" >
					<input type="hidden" id="inputTRId" name="inputTRId">
					<input type="hidden" id="inputTRNumero" name="inputTRNumero">
					<input type="hidden" id="inputTRCategoria" name="inputTRCategoria">
					<input type="hidden" id="inputTRNomeCategoria" name="inputTRNomeCategoria">
					<input type="hidden" id="inputTRStatus" name="inputTRStatus">
					<input type="hidden" id="inputTermoReferenciaStatus" name="inputTermoReferenciaStatus" value="FASEINTERNAFINALIZADA"> <!-- esse aqui é por causa do FinalizarTR -->
				</form>
				
			</div>
			<!-- /content area -->

			<?php include_once("footer.php"); ?>

		</div>
		<!-- /main content -->

	</div>
	<!-- /page content -->

	<?php include_once("alerta.php"); ?>
	
	<?php /* $total1 = microtime(true) - $inicio1;
		 echo '<span style="background-color:yellow; padding: 10px; font-size:24px;">Tempo de execução do script: ' . round($total1, 2).' segundos</span>'; */ ?>
</body>

</html>
