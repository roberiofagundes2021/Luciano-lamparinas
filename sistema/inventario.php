<?php

include_once("sessao.php");

$_SESSION['PaginaAtual'] = 'Inventário';

include('global_assets/php/conexao.php');

$sql = "SELECT InvenId, InvenData, InvenNumero, SituaNome, SituaChave, SituaCor, UnidaNome, CategNome
		FROM Inventario
		JOIN Situacao on SituaId = InvenSituacao
		LEFT JOIN Unidade on UnidaId = InvenUnidade
		LEFT JOIN Categoria on CategId = InvenCategoria
		WHERE InvenEmpresa = " . $_SESSION['EmpreId'] . "
		ORDER BY InvenNumero DESC";
$result = $conn->query($sql);
$row = $result->fetchAll(PDO::FETCH_ASSOC);
//$count = count($row);

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Inventários</title>

	<?php include_once("head.php"); ?>

	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/tables/datatables/datatables.min.js"></script>
	<script src="global_assets/js/plugins/tables/datatables/extensions/responsive.min.js"></script>

	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>

	<script src="global_assets/js/demo_pages/datatables_responsive.js"></script>
	<script src="global_assets/js/demo_pages/datatables_sorting.js"></script>

	<!-- /theme JS files -->

	<script type="text/javascript">
		$(document).ready(function() {

			/* Início: Tabela Personalizada */
			$('#tblInventario').DataTable({
				"order": [
					[1, "desc"]
				],
				autoWidth: false,
				responsive: true,
				/*  columnDefs: [{ 
					orderable: true,
					width: 150,
					targets: [ 3 ]
				}], */
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
		});

		function atualizaInventario(Permission, InvenId, InvenNumero, Situacao, Tipo) {

			document.getElementById('inputPermission').value = Permission;
			document.getElementById('inputInventarioId').value = InvenId;
			document.getElementById('inputInventarioNumero').value = InvenNumero;
			document.getElementById('inputInventarioStatus').value = Situacao;

			if (Tipo == 'edita') {
				document.formInventario.action = "inventarioEdita.php";
			} else if (Tipo == 'exclui') {
				if(Permission){
					confirmaExclusao(document.formInventario, "Tem certeza que deseja excluir esse inventário?", "inventarioExclui.php");
				} else{
					alerta('Permissão Negada!','');
					return false;
				}
			} else if (Tipo == 'mudaStatus') {

				if (Situacao == 'PENDENTE') {
					confirmaExclusao(document.formInventario, "Tem certeza que deseja finalizar esse inventário? O processo é irreversível.", "inventarioMudaSituacao.php");
				} else {
					alerta('Notificação', 'Uma vez finalizado não pode mais alterar a situação do inventário.', 'info');
					return false;
				}

			} else if (Tipo == 'imprimir-lista') {
				document.formInventario.action = "inventarioLista.php";
				document.formInventario.setAttribute("target", "_blank");
			} else if (Tipo == 'imprimir-inventario') {

				if (Situacao == 'FINALIZADO') {
					document.formInventario.action = "inventarioRelatorio.php";
					document.formInventario.setAttribute("target", "_blank");
				} else {
					confirmaExclusao(document.formInventario, "Esse relatório só pode ser visualizado após finalização do inventário. Deseja finalizá-lo? Lembrando que esse processo é irreversível.", "inventarioMudaSituacao.php");
				}
			}

			document.formInventario.submit();
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
								<h5 class="card-title">Relação dos Inventários</h5>
								<div class="header-elements">
									<div class="list-icons">
										<!--<a class="list-icons-item" data-action="collapse"></a>-->
										<!--<a href="empresa.php" class="list-icons-item" data-action="reload"></a>-->
										<!--<a class="list-icons-item" data-action="remove"></a>-->
									</div>
								</div>
							</div>

							<div class="card-body">
								<div class="row">
									<div class="col-lg-9">
										A relação abaixo faz referência aos inventários da unidade <b><?php echo $_SESSION['UnidadeNome']; ?></b>.
									</div>
									<div class="col-lg-3">
										<div class="text-right"><a href="inventarioNovo.php" class="btn btn-principal" role="button">Novo Inventário</a></div>
									</div>
								</div>
							</div>

							<table class="table" id="tblInventario">
								<thead>
									<tr class="bg-slate">
										<th width="10%">Data</th>
										<th width="13%">Nº Inventário</th>
										<th width="29%">Unidade</th>
										<th width="28%">Categoria</th>
										<th width="10%">Situação</th>
										<th width="10%" class="text-center">Ações</th>
									</tr>
								</thead>
								<tbody>
									<?php
									foreach ($row as $item) {

										$situacao = $item['SituaNome'];
										$situacaoClasse = 'badge badge-flat border-' . $item['SituaCor'] . ' text-' . $item['SituaCor'];

										print('
										<tr>
											<td>' . mostraData($item['InvenData']) . '</td>
											<td>' . formatarNumero($item['InvenNumero']) . '</td>
											<td>' . $item['UnidaNome'] . '</td>
											<td>' . $item['CategNome'] . '</td>');

										print('<td><a href="#" onclick="atualizaInventario(1,' . $item['InvenId'] . ', ' . $item['InvenNumero'] . ', \'' . $item['SituaChave'] . '\', \'mudaStatus\');"><span class="badge ' . $situacaoClasse . '">' . $situacao . '</span></a></td>');

										print('<td class="text-center">
												<div class="list-icons">
													<div class="list-icons list-icons-extended">
														<a href="#" onclick="atualizaInventario('.$atualizar.',' . $item['InvenId'] . ', ' . $item['InvenNumero'] . ', \'' . $item['SituaChave'] . '\', \'edita\')" class="list-icons-item"><i class="icon-pencil7"></i></a>
														<a href="#" onclick="atualizaInventario('.$excluir.',' . $item['InvenId'] . ', ' . $item['InvenNumero'] . ', \'' . $item['SituaChave'] . '\',  \'exclui\')" class="list-icons-item"><i class="icon-bin"></i></a>
														<div class="dropdown">													
															<a href="#" class="list-icons-item" data-toggle="dropdown">
																<i class="icon-menu9"></i>
															</a>

															<div class="dropdown-menu dropdown-menu-right">
																<a href="#" onclick="atualizaInventario(1,' . $item['InvenId'] . ', ' . $item['InvenNumero'] . ', \'' . $item['SituaChave'] . '\', \'imprimir-lista\')"  class="dropdown-item" title="Imprimir Lista"><i class="icon-printer"></i> Imprimir Lista</a>
																<a href="#" onclick="atualizaInventario(1,' . $item['InvenId'] . ', ' . $item['InvenNumero'] . ', \'' . $item['SituaChave'] . '\', \'imprimir-inventario\')"  class="dropdown-item" title="Imprimir Inventário"><i class="icon-printer2"></i> Imprimir Inventário</a>
															</div>
														</div>
													</div>
												</div>
											</td>
										</tr>');
									}
									?>

								</tbody>
							</table>
						</div>
						<!-- /basic responsive configuration -->

					</div>
				</div>

				<!-- /info blocks -->

				<form name="formInventario" method="post" action="inventarioEdita.php">
					<input type="hidden" id="inputPermission" name="inputPermission" >
					<input type="hidden" id="inputInventarioId" name="inputInventarioId">
					<input type="hidden" id="inputInventarioNumero" name="inputInventarioNumero">
					<input type="hidden" id="inputInventarioStatus" name="inputInventarioStatus">
				</form>

			</div>
			<!-- /content area -->

			<?php include_once("footer.php"); ?>

		</div>
		<!-- /main content -->

	</div>
	<!-- /page content -->

	<?php include_once("alerta.php"); ?>

</body>

</html>