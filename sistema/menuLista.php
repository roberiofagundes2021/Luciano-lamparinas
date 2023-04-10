<?php 

include_once("sessao.php");

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Menus</title>

	<?php include_once("head.php"); ?>
	
	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
	<script src="global_assets/js/demo_pages/form_select2.js"></script>

	<script src="global_assets/js/demo_pages/form_layouts.js"></script>
	<script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>
	
	<script src="global_assets/js/plugins/forms/inputs/inputmask.js"></script>		
	<!-- /theme JS files -->	

	<!-- Validação -->
	<script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
	<script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
	<script src="global_assets/js/demo_pages/form_validation.js"></script>	

	<script src="global_assets/js/plugins/tables/datatables/datatables.min.js"></script>
	<script src="global_assets/js/plugins/tables/datatables/extensions/responsive.min.js"></script>

	<script src="global_assets/js/demo_pages/datatables_responsive.js"></script>
	<script src="global_assets/js/demo_pages/datatables_sorting.js"></script>

	<!-- Adicionando Javascript -->
    <script type="text/javascript" >

        $(document).ready(function() {
        	$('#tblMenu').DataTable({
				"order": [
					[0, "asc"]
				],
				autoWidth: false,
				responsive: true,
				columnDefs: [{
						orderable: true, //Nome
						width: "10%",
						targets: [0]
					},
					{
						orderable: false, //Url
						width: "20%",
						targets: [1]
					},
					{
						orderable: false, //Icone
						width: "10%",
						targets: [2]
					},
					{
						orderable: false, //Modulo
						width: "20%",
						targets: [3]
					},
					{
						orderable: false, //Nível
						width: "5%",
						targets: [4]
					},
					{
						orderable: false, //Ordem
						width: "5%",
						targets: [5]
					},
					{
						orderable: false, //SubMenu
						width: "5%",
						targets: [6]
					},
					{
						orderable: false, //Publico
						width: "5%",
						targets: [7]
					},
					{
						orderable: false, //Privado
						width: "5%",
						targets: [8]
					},
					{
						orderable: false, //Posição
						width: "10%",
						targets: [9]
					},
					{
						orderable: false, //Ações
						width: "5%",
						targets: [10]
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
			$('#tblModulo').DataTable({
				"order": [
					[0, "asc"]
				],
				autoWidth: false,
				responsive: true,
				columnDefs: [{
						orderable: true, //Nome
						width: "50%",
						targets: [0]
					},
					{
						orderable: false, //Posicao
						width: "10%",
						targets: [1]
					},
					{
						orderable: true, //Situacao
						width: "30%",
						targets: [2]
					},
					{
						orderable: false, //Acoes
						width: "10%",
						targets: [3]
					},
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

			$('#body').addClass('sidebar-xs')
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
			getAllMenus()
        }); // document.ready

		function atualizaMenu(id, tipo){
			$('#id').val(id)
			$('#isMenu').val(tipo)
			$('#formMenu').submit()
		}
		function excluirMenu(id, tipo){
			$.ajax({
				type: 'POST',
				url: 'menuFiltra.php',
				dataType: 'json',
				data:{
					'tipo': 'EXCLUIR',
					'idMenu': id,
					'isMenu': tipo
				},
				success: function(response) {
					alerta(response.titulo, response.menssagem, response.status);
					getAllMenus()
				},
				error: function(response) {
					alerta(response.titulo, response.menssagem, response.status);
				}
			});
		}

		function getAllMenus(){
			// #tblMenu
			$.ajax({
				type: 'POST',
				url: 'menuFiltra.php',
				dataType: 'json',
				data:{
					'tipo': 'ALL'
				},
				success: function(response) {
					$('#tblMenu').DataTable().clear().draw()
					tableMen = $('#tblMenu').DataTable()
					let rowNodeMen

					response.menus.forEach(item => {
						rowNodeMen = tableMen.row.add(item.data).draw().node()
						// $(rowNode).attr('class', 'text-center')
						// $(rowNode).find('td:eq(7)').attr('data-agendamento', `${item.identify.iAgendamento}`)
					})

					$('#tblModulo').DataTable().clear().draw()
					tableMod = $('#tblModulo').DataTable()
					let rowNodeMod
					response.modulos.forEach(item => {
						rowNodeMod = tableMod.row.add(item.data).draw().node()
						// $(rowNode).attr('class', 'text-center')
						// $(rowNode).find('td:eq(7)').attr('data-agendamento', `${item.identify.iAgendamento}`)
					})
				}
			});
		}
    </script>
</head>

<body id="body" class="navbar-top">

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
				<div class="card">
					<div class="card-header header-elements-inline">
						<h3 class="card-title">Relação de Menus</h3>
					</div>
					<div class="card-body">
						<div class="row">
							<div class="col-lg-9">
									A relação abaixo faz referência à todos os menus</b>
							</div>
							<div class="col-lg-3">
								<div class="text-right"><a href="menuCriar.php" class="btn btn-principal" role="button">Novo Menu</a></div>
							</div>
						</div>
					</div>
					
					<div class="card-body">
						<table class="table" id="tblMenu">
							<thead>
								<tr class="bg-slate">
									<th>Nome</th>
									<th>Url</th>
									<th>Icone</th>
									<th>Modulo</th>
									<th>Nível</th>
									<th>Ordem</th>
									<th>SubMenu</th>
									<th>Setor Publico</th>
									<th>Setor Privado</th>
									<th>Posição</th>
									<th class="text-center">Ações</th>
								</tr>
							</thead>
							<tbody>
							</tbody>
						</table>
					</div>
					<div class="card-body">
						<table class="table" id="tblModulo">
							<thead>
								<tr class="bg-slate">
									<th>Nome</th>
									<th>Ordem</th>
									<th>Situação</th>
									<th class="text-center">Ações</th>
								</tr>
							</thead>
							<tbody>
							</tbody>
						</table>
					</div>
					<form name="formMenu" id="formMenu" method="post" action="menuCriar.php">
						<input id='id' type='hidden' name='id' value=''>
						<input id='isMenu' type='hidden' name='isMenu' value=''>
					</form>
				</div>
			</div>
			<?php include_once("footer.php"); ?>
		</div>
	</div>
</body>
</html>
