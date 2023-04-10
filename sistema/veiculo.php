<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Veiculo';

include('global_assets/php/conexao.php');

$sql = "SELECT VeicuId, VeicuNome, VeicuPlaca, SetorNome, VeicuStatus, SituaNome, SituaChave, SituaCor
		FROM Veiculo
		JOIN Setor ON SetorId = VeicuSetor
		JOIN Situacao on SituaId = VeicuStatus
		WHERE VeicuUnidade = ". $_SESSION['UnidadeId'] ."
		ORDER BY VeicuNome ASC";
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
	<title>Lamparinas | Veículo</title>

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
			$('#tblVeiculo').DataTable( {
				"order": [[ 0, "asc" ]],
			    autoWidth: false,
				responsive: true,
			    columnDefs: [
				{ 
					orderable: true,   //Nome
					width: "20%",
					targets: [0]
				},
				{ 
					orderable: true,   //Placa
					width: "20%",
					targets: [1]
				},
				{ 
					orderable: true,   //Setor
					width: "20%",
					targets: [2]
				},				
				{ 
					orderable: true,   //Situação
					width: "10%",
					targets: [3]
				},
				{ 
					orderable: false,  //Ações
					width: "10%",
					targets: [4]
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
		function atualizaVeiculo(Permission, VeicuId, VeicuNome,  VeicuPlaca, VeicuStatus, Tipo){
		
			document.getElementById('inputPermission').value = Permission;
			document.getElementById('inputVeicuId').value = VeicuId;
			document.getElementById('inputVeicuNome').value = VeicuNome;
			document.getElementById('inputVeicuPlaca').value = VeicuPlaca;
			document.getElementById('inputVeicuStatus').value = VeicuStatus;
			
					
			if (Tipo == 'edita'){	
				document.formVeiculo.action = "veiculoEdita.php";		
			} else if (Tipo == 'exclui'){
				if(Permission){
					confirmaExclusao(document.formVeiculo, "Tem certeza que deseja excluir esse veículo?", "veiculoExclui.php");
				} else{
					alerta('Permissão Negada!','');
					return false;
				}
		} else if (Tipo == 'mudaStatus'){
				document.formVeiculo.action = "veiculoMudaSituacao.php";
			} 			
			document.formVeiculo.submit();
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
								<h3 class="card-title">Relação de Veículos</h3>
							</div>

							<div class="card-body">
								<div class="row">
									<div class="col-lg-9">
									<p class="font-size-lg">A relação abaixo faz referência aos veículos da unidade <b><?php echo $_SESSION['UnidadeNome']; ?></b></p>
									</div>	
										<div class="col-lg-3">	
										<div class="text-right"><a href="veiculoNovo.php" class="btn btn-principal" role="button">Novo Veículo</a></div>
									</div>
								</div>
							</div>
							
							<table class="table" id="tblVeiculo">
								<thead>
									<tr class="bg-slate">
										<th >Veiculo</th>
										<th >Placa</th>
										<th >Setor</th>
										<th >Situação</th>
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
											<td>'.$item['VeicuNome'].'</td>
											<td>'.$item['VeicuPlaca'].'</td>
											<td>'.$item['SetorNome'].'</td>
											');
										
										print('<td><a href="#" onclick="atualizaVeiculo(1,'.$item['VeicuId'].', \''.$item['VeicuNome'].'\', \''.$item['VeicuPlaca'].'\',\''.$item['SituaChave'].'\', \'mudaStatus\');"><span class="badge '.$situacaoClasse.'">'.$situacao.'</span></a></td>');
										
										print('<td class="text-center">
												<div class="list-icons">
													<div class="list-icons list-icons-extended">
														<a href="#" onclick="atualizaVeiculo('.$atualizar.','.$item['VeicuId'].', \''.$item['VeicuNome'].'\', \''.$item['VeicuPlaca'].'\',\''.$item['SituaChave'].'\', \'edita\');" class="list-icons-item"><i class="icon-pencil7" data-popup="tooltip" data-placement="bottom" title="Editar"></i></a>
														<a href="#" onclick="atualizaVeiculo('.$excluir.','.$item['VeicuId'].', \''.$item['VeicuNome'].'\', \''.$item['VeicuPlaca'].'\',\''.$item['SituaChave'].'\', \'exclui\');" class="list-icons-item"><i class="icon-bin" data-popup="tooltip" data-placement="bottom" title="Exluir"></i></a>							
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
				
				<form name="formVeiculo" method="post">
					<input type="hidden" id="inputPermission" name="inputPermission" >
					<input type="hidden" id="inputVeicuId" name="inputVeicuId" >
					<input type="hidden" id="inputVeicuNome" name="inputVeicuNome" >
					<input type="hidden" id="inputVeicuPlaca" name="inputVeicuPlaca" >
					<input type="hidden" id="inputVeicuStatus" name="inputVeicuStatus" >
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
