<?php 

include_once("sessao.php"); 
include('global_assets/php/conexao.php');

$_SESSION['PaginaAtual'] = 'Classificação de Risco';

$sql = "SELECT AtClRId, AtClRNome, AtClRTempo, AtClRCor, AtClRDeterminantes, AtClRStatus, SituaNome, SituaChave, SituaCor
		FROM AtendimentoClassificacaoRisco
		JOIN Situacao ON SituaId = AtClRStatus
	    WHERE AtClRUnidade = ". $_SESSION['UnidadeId'] ."
		ORDER BY AtClRNome ASC";
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
	<title>Lamparinas | Classificação de Risco</title>

	<?php include_once("head.php"); ?>
	
	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/tables/datatables/datatables.min.js"></script>
	<script src="global_assets/js/plugins/tables/datatables/extensions/responsive.min.js"></script>
	
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>

	<script src="global_assets/js/demo_pages/datatables_responsive.js"></script>
	<script src="global_assets/js/demo_pages/datatables_sorting.js"></script>

	<!-- Não permite que o usuário retorne para o EDITAR -->
	<script src="global_assets/js/lamparinas/stop-back.js"></script>	
	
	<!-- /theme JS files -->	
	
	<script type="text/javascript">
	
		$(document).ready(function() {		
			
			/* Início: Tabela Personalizada */
			$('#tblClassificacaoRisco').DataTable( {
				"order": [[ 0, "asc" ]],
			    autoWidth: false,
				responsive: true,
			    columnDefs: [{ 
					orderable: true,   //Protocolo
					width: "80%",
					targets: [0]
				},
				{ 
					orderable: false,  //Ações
					width: "20%",
					targets: [1]
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
			function atualizaClassificacaoRisco(Permission,ClassificacaoRiscoId, ClassificacaoRiscoNome, ClassificacaoRiscoStatus, Tipo){

				document.getElementById('inputPermission').value = Permission;
				document.getElementById('inputClassificacaoRiscoId').value = ClassificacaoRiscoId;
				document.getElementById('inputClassificacaoRiscoNome').value = ClassificacaoRiscoNome;
				document.getElementById('inputClassificacaoRiscoStatus').value = ClassificacaoRiscoStatus;


				//Esse ajax está sendo usado para verificar no banco se o registro já existe

					if (Tipo == 'edita'){	
						document.formClassificacaoRisco.action = "classificacaoRiscoEdita.php";
					}
				
				
				document.formClassificacaoRisco.submit();
				
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
								<h3 class="card-title">Relação de Classificação de Risco</h3>
							</div>

							<div class="card-body">
								<div class="row">
									<div class="col-lg-9">
									<p class="font-size-lg">A relação abaixo faz referência as classificações de risco da unidade <b><?php echo $_SESSION['UnidadeNome']; ?></b></p>
									</div>
								</div>
							</div>
							
							<table class="table" id="tblClassificacaoRisco">
								<thead>
									<tr class="bg-slate">
										<th>Nome</th>
										<th class="text-center">Ações</th>
									</tr>
								</thead>
								<tbody>
								<?php
									foreach ($row as $item){
										
										print('
										<tr>
											<td>'.$item['AtClRNome'].'</td>
											');
										
										print('<td class="text-center">
												<div class="list-icons">
													<div class="list-icons list-icons-extended">
														<a href="#" onclick="atualizaClassificacaoRisco( 1 ,'.$item['AtClRId'].', \''.$item['AtClRNome'].'\',\''.$item['SituaChave'].'\', \'edita\');" class="list-icons-item" data-popup="tooltip" data-placement="bottom" title="Editar Classificação de Risco"><i class="icon-pencil7"></i></a>
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
				
				<form name="formClassificacaoRisco" method="post">
					<input type="hidden" id="inputPermission" name="inputPermission" >
					<input type="hidden" id="inputClassificacaoRiscoId" name="inputClassificacaoRiscoId" >
					<input type="hidden" id="inputClassificacaoRiscoNome" name="inputClassificacaoRiscoNome" >
					<input type="hidden" id="inputClassificacaoRiscoStatus" name="inputClassificacaoRiscoStatus" >
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
