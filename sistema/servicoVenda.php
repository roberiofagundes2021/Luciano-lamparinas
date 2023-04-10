<?php 

include_once("sessao.php"); 
include('global_assets/php/conexao.php');

$_SESSION['PaginaAtual'] = 'Serviço';

$sql = "SELECT SrVenId, SrVenNome, SrVenStatus, PlConNome, SituaNome, SituaChave, SituaCor
		FROM ServicoVenda
		JOIN PlanoConta ON PlConId = SrVenPlanoConta
		JOIN Situacao ON SituaId = SrVenStatus
	    WHERE SrVenUnidade = ". $_SESSION['UnidadeId'] ."
		ORDER BY SrVenNome ASC";
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
	<title>Lamparinas | Serviço</title>

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
			$('#tblServico').DataTable( {
				"order": [[ 0, "asc" ]],
			    autoWidth: false,
				responsive: true,
			    columnDefs: [{ 
					orderable: true,   //Serviço
					width: "45%",
					targets: [0]
				},				
				{ 
					orderable: true,   //Plano de Conta
					width: "45%",
					targets: [1]
				},
				{ 
					orderable: true,   //Situação
					width: "5%",
					targets: [2]
				},
				{ 
					orderable: false,  //Ações
					width: "5%",
					targets: [3]
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
			function atualizaServico(Permission,ServicoId, ServicoNome, ServicoStatus, Tipo){

				document.getElementById('inputPermission').value = Permission;
				document.getElementById('inputServicoId').value = ServicoId;
				document.getElementById('inputServicoNome').value = ServicoNome;
				document.getElementById('inputServicoStatus').value = ServicoStatus;

				

				//Esse ajax está sendo usado para verificar no banco se o registro já existe

					if (Tipo == 'edita'){	
						document.formServico.action = "servicoVendaEdita.php";
					} else if (Tipo == 'mudaStatus'){
						document.formServico.action = "servicoVendaMudaSituacao.php";
					}	else if (Tipo == 'exclui'){
						if(Permission){
							confirmaExclusao(document.formServico, "Tem certeza que deseja excluir esse serviço?", "servicoVendaExclui.php");
						}	else{
							alerta('Permissão Negada!','');
							return false;
						}
					}  
				
				
				document.formServico.submit();
				
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
								<h3 class="card-title">Relação de Serviços</h3>
							</div>

							<div class="card-body">
								<div class="row">
									<div class="col-lg-9">
									<p class="font-size-lg">A relação abaixo faz referência aos serviços da unidade <b><?php echo $_SESSION['UnidadeNome']; ?></b></p>
									</div>
                                    <div class="col-lg-3">
										<div class="text-right">
											<a href="servicoVendaNovo.php" class="btn btn-principal" role="button">Novo Serviço</a>
										</div>
									</div>
								</div>
							</div>
							
							<table class="table" id="tblServico">
								<thead>
									<tr class="bg-slate">
										<th>Servico</th>
										<th>Plano de Conta</th>
										<th>Situação</th>
										<th class="text-center">Ações</th>
									</tr>
								</thead>
								<tbody>
								<?php
									foreach ($row as $item){

										$situacao = $item['SituaNome'];
										$situacaoClasse = 'badge badge-flat border-'.$item['SituaCor'].' text-'.$item['SituaCor'];
										
										print('
										<tr>
											<td>'.$item['SrVenNome'].'</td>
											<td>'.$item['PlConNome'].'</td>
											');
										
										print('<td><a href="#" onclick="atualizaServico(1,'.$item['SrVenId'].', \''.$item['SrVenNome'].'\',\''.$item['SituaChave'].'\', \'mudaStatus\');" data-popup="tooltip" data-placement="bottom" title="Mudar Situação"><span class="badge '.$situacaoClasse.'">'.$situacao.'</span></a></td>');
										
										print('<td class="text-center">
												<div class="list-icons">
													<div class="list-icons list-icons-extended">
														<a href="#" onclick="atualizaServico( 1 ,'.$item['SrVenId'].', \''.$item['SrVenNome'].'\',\''.$item['SituaChave'].'\', \'edita\');" class="list-icons-item" data-popup="tooltip" data-placement="bottom" title="Editar Serviço"><i class="icon-pencil7"></i></a>
														<a href="#" onclick="atualizaServico( 1 ,'.$item['SrVenId'].', \''.$item['SrVenNome'].'\',\''.$item['SituaChave'].'\', \'exclui\');" class="list-icons-item" data-popup="tooltip" data-placement="bottom" title="Excluir Serviço"><i class="icon-bin"></i></a>
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
				
				<form name="formServico" method="post">
					<input type="hidden" id="inputPermission" name="inputPermission" >
					<input type="hidden" id="inputServicoId" name="inputServicoId" >
					<input type="hidden" id="inputServicoNome" name="inputServicoNome" >
					<input type="hidden" id="inputServicoStatus" name="inputServicoStatus" >
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
