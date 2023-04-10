<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Unidade';

include('global_assets/php/conexao.php');

if (isset($_POST['inputEmpresaId'])){
	$_SESSION['EmpresaId'] = $_POST['inputEmpresaId'];
	$_SESSION['EmpresaNome'] = $_POST['inputEmpresaNome'];
}

if (!isset($_SESSION['EmpresaId'])) {
	irpara("empresa.php");
}

$sql = "SELECT UnidaId, UnidaNome, UnidaBairro, UnidaCidade, UnidaEstado, UnidaStatus, SituaNome, SituaChave, SituaCor
		FROM Unidade
		JOIN Situacao on SituaId = UnidaStatus
	    WHERE UnidaEmpresa = ". $_SESSION['EmpresaId'] ."
		ORDER BY UnidaNome ASC";
$result = $conn->query($sql);
$row = $result->fetchAll(PDO::FETCH_ASSOC);
$count = count($row);
//var_dump($count);die;

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Unidade</title>

	<?php include_once("head.php"); ?>
	
	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/tables/datatables/datatables.min.js"></script>
	<script src="global_assets/js/plugins/tables/datatables/extensions/responsive.min.js"></script>
	
	<script src="global_assets/js/demo_pages/datatables_responsive.js"></script>
	<script src="global_assets/js/demo_pages/datatables_sorting.js"></script>
	
	<script src="global_assets/js/plugins/notifications/jgrowl.min.js"></script>
	<script src="global_assets/js/plugins/notifications/noty.min.js"></script>
	<script src="global_assets/js/demo_pages/extra_jgrowl_noty.js"></script>
	<script src="global_assets/js/demo_pages/components_popups.js"></script>
	
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
	
	<script src="global_assets/js/demo_pages/form_layouts.js"></script>
	<script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>		
	
	<!-- /theme JS files -->	
	
	<script language ="javascript">

		$(document).ready(function (){	
		
			$('#tblUnidade').DataTable( {
				"order": [[ 0, "asc" ]],
			    autoWidth: false,
				responsive: true,
			    columnDefs: [
				{
					orderable: true,   //Unidade
					width: "30%",
					targets: [0]
				},
				{ 
					orderable: true,   //Bairro
					width: "20%",
					targets: [1]
				},
				{ 
					orderable: true,   //Cidade
					width: "20%",
					targets: [2]
				},
				{ 
					orderable: true,   //Estado
					width: "10%",
					targets: [3]
				},
				{ 
					orderable: true,   //Situacao
					width: "10%",
					targets: [4]
				},
				{ 
					orderable: true,   //Ações
					width: "10%",
					targets: [5]
				}],
				dom: '<"datatable-header"fl><"datatable-scroll-wrap"t><"datatable-footer"ip>',
				language: {
					search: '<span>Filtro:</span> _INPUT_',
					searchPlaceholder: 'filtra qualquer coluna...',
					lengthMenu: '<span>Mostrar:</span> _MENU_',
					paginate: { 'first': 'Primeira', 'last': 'Última', 'next': $('html').attr('dir') == 'rtl' ? '&larr;' : '&rarr;', 'previous': $('html').attr('dir') == 'rtl' ? '&rarr;' : '&larr;' }
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
			
		//Essa função foi criada para não usar $_GET e ficar mostrando os ids via URL
		function atualizaUnidade(UnidaId, UnidaNome, UnidaStatus, Tipo){
		
			document.getElementById('inputUnidadeId').value = UnidaId;
			document.getElementById('inputUnidadeNome').value = UnidaNome;
			document.getElementById('inputUnidadeStatus').value = UnidaStatus;
					
			if (Tipo == 'edita'){	
				document.formUnidade.action = "unidadeEdita.php";		
			} else if (Tipo == 'exclui'){
				confirmaExclusao(document.formUnidade, "Tem certeza que deseja excluir essa unidade?", "unidadeExclui.php");
			} else if (Tipo == 'mudaStatus'){
				document.formUnidade.action = "unidadeMudaSituacao.php";
			} else if (Tipo == 'imprime'){
				document.formUnidade.action = "unidadeImprime.php";
				document.formUnidade.setAttribute("target", "_blank");
			}
			
			document.formUnidade.submit();
		}		
			
	</script>

</head>

<body class="navbar-top sidebar-xs">

	<?php include_once("topo.php"); ?>	

	<!-- Page content -->
	<div class="page-content">
		
		<?php include_once("menu-left.php"); ?>
		
		<?php include_once("menuLeftSecundario.php"); ?>		

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
								<h3 class="card-title">Relação de Unidades</h3>
							</div>

							<div class="card-body">
								<div class="row">
									<div class="col-lg-9">
										<p class="font-size-lg">A relação abaixo faz referência às unidades da empresa <b><?php echo $_SESSION['EmpresaNome']; ?></b></p>
										</div>
									<div class="col-lg-3">
										<div class="text-right"><a href="unidadeNovo.php" class="btn btn-principal" role="button">Nova Unidade</a></div>
									</div>
								</div>
							</div>					
							
							<!-- A table só filtra se colocar 6 colunas. Onde mudar isso? -->
							<table id="tblUnidade" class="table">
								<thead>
									<tr class="bg-slate">
										<th width="30%">Unidade</th>
										<th width="20%">Bairro</th>
										<th width="20%">Cidade</th>
										<th width="10%">Estado</th>
										<th width="10%">Situação</th>
										<th width="10%" class="text-center">Ações</th>
									</tr>
								</thead>
								<tbody>
								<?php
									foreach ($row as $item){
										
										$situacao = $item['SituaNome'];
										$situacaoClasse = 'badge badge-flat border-'.$item['SituaCor'].' text-'.$item['SituaCor'];
										
										print('
										<tr>
											<td>'.$item['UnidaNome'].'</td>
											<td>'.$item['UnidaBairro'].'</td>
											<td>'.$item['UnidaCidade'].'</td>
											<td>'.$item['UnidaEstado'].'</td>
											');
										
										print('<td><a href="#" onclick="atualizaUnidade('.$item['UnidaId'].', \''.$item['UnidaNome'].'\', \''.$item['SituaChave'].'\', \'mudaStatus\');"><span class="badge '.$situacaoClasse.'">'.$situacao.'</span></a></td>');
										
										print('<td class="text-center">
												<div class="list-icons">
													<div class="list-icons list-icons-extended">
														<a href="#" onclick="atualizaUnidade('.$item['UnidaId'].', \''.$item['UnidaNome'].'\', \''.$item['SituaChave'].'\', \'edita\');" class="list-icons-item"><i class="icon-pencil7" data-popup="tooltip" data-placement="bottom" title="Editar"></i></a>
														<a href="#" onclick="atualizaUnidade('.$item['UnidaId'].', \''.$item['UnidaNome'].'\', \''.$item['SituaChave'].'\', \'exclui\');" class="list-icons-item"><i class="icon-bin" data-popup="tooltip" data-placement="bottom" title="Exluir"></i></a>
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
				
				<form name="formUnidade" method="post" action="cEdita.php">
					<input type="hidden" id="inputUnidadeId" name="inputUnidadeId" >
					<input type="hidden" id="inputUnidadeNome" name="inputUnidadeNome" >
					<input type="hidden" id="inputUnidadeStatus" name="inputUnidadeStatus" >
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
