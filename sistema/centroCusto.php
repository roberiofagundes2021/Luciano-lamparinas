<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Centro de Custo';

include('global_assets/php/conexao.php');

$sql = "SELECT CnCusId, CnCusCodigo, CnCusNome, CnCusDetalhamento, CnCusStatus, SituaNome, SituaCor, SituaChave
		FROM CentroCusto
		JOIN Situacao on SituaId = CnCusStatus
	    WHERE CnCusUnidade = ". $_SESSION['UnidadeId'] ."
		ORDER BY CnCusNome ASC";
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
	<title>Lamparinas | Centro de Custo</title>

	<?php include_once("head.php"); ?>
	
	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/tables/datatables/datatables.min.js"></script>
	<script src="global_assets/js/plugins/tables/datatables/extensions/responsive.min.js"></script>
	
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
	
	<script src="global_assets/js/demo_pages/datatables_responsive.js"></script>
	<script src="global_assets/js/demo_pages/datatables_sorting.js"></script>	
	<!-- /theme JS files -->	
	
	<script type="text/javascript">

		$(document).ready(function (){	
			$('#tblCentroCusto').DataTable( {
				"order": [[ 0, "asc" ]],
			    autoWidth: false,
				responsive: true,
			    columnDefs: [
				{
					orderable: true,   //Código
					width: "10%",
					targets: [0]
				},
				{
					orderable: true,   //Centro de Custo
					width: "25%",
					targets: [1]
				},
				{
					orderable: true,   //Detalhamento
					width: "45%",
					targets: [2]
				},
				{ 
					orderable: true,   //Situação
					width: "10%",
					targets: [3]
				},
				{ 
					orderable: false,   //Ações
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
		function atualizaCentroCusto(Permission, CnCusId, CnCusNome, CnCusStatus, Tipo){
		
			document.getElementById('inputCentroCustoId').value = CnCusId;
			document.getElementById('inputCentroCustoNome').value = CnCusNome;
			document.getElementById('inputCentroCustoStatus').value = CnCusStatus;
					
			if (Permission == 1){
				if (Tipo == 'edita'){	
					document.formCentroCusto.action = "centroCustoEdita.php";		
				} else if (Tipo == 'exclui'){
					confirmaExclusao(document.formCentroCusto, "Tem certeza que deseja excluir esse Centro de Custo?", "centroCustoExclui.php");
				} else if (Tipo == 'mudaStatus'){
					document.formCentroCusto.action = "centroCustoMudaSituacao.php";
				} 
			
				document.formCentroCusto.submit();
			} else{
				alerta('Permissão Negada!','');
			}
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
								<h3 class="card-title">Relação de Centros de Custo</h3>
								<div class="header-elements">
									<div class="list-icons">
										<a class="list-icons-item" data-action="collapse"></a>
										<a href="categoria.php" class="list-icons-item" data-action="reload"></a>
										<!--<a class="list-icons-item" data-action="remove"></a>-->
									</div>
								</div>
							</div>

							<div class="card-body">
								<div class="row">
									<div class="col-lg-9">
										<p class="font-size-lg">A relação abaixo faz referência aos Centros de Custo da unidade <b><?php echo $_SESSION['UnidadeNome']; ?></b></p>
										</div>
									<div class="col-lg-3">	
										<div class="text-right"><a href="centroCustoNovo.php" class="btn btn-principal" role="button">Novo Centro de Custo</a></div>
									</div>
								</div>
							</div>					
							
							<!-- A table só filtra se colocar 6 colunas. Onde mudar isso? -->
							<table id="tblCentroCusto" class="table">
								<thead>
									<tr class="bg-slate">
										<th data-filter>Código</th>
										<th data-filter>Centro de Custo</th>
										<th data-filter>Detalhamento</th>
										<th>Situação</th>
										<th class="text-center">Ações</th>
									</tr>
								</thead>
								<tbody>
								<?php
									foreach ($row as $item){
										
										$situacao = $item['SituaNome'];
										$situacaoClasse = 'badge badge-flat border-'.$item['SituaCor'].' text-'.$item['SituaCor'];
										$situacaoChave ='\''.$item['SituaChave'].'\'';
										
										print('
										<tr>
											<td>'.$item['CnCusCodigo'].'</td>
											<td>'.$item['CnCusNome'].'</td>
											<td>'.$item['CnCusDetalhamento'].'</td>
											');
											
										if ($item['CnCusNome'] != 'Atendimento Eletivo' && $item['CnCusNome'] != 'Atendimento Ambulatorial' && $item['CnCusNome'] != 'Atendimento Internação') {
											print('<td><a href="#" onclick="atualizaCentroCusto(1,'.$item['CnCusId'].', \''.$item['CnCusNome'].'\','.$situacaoChave .', \'mudaStatus\');"><span class="badge '.$situacaoClasse.'">'.$situacao.'</span></a></td>');
										}else{
											print('<td><a <span class="badge '.$situacaoClasse.'">'.$situacao.'</span></a></td>');
										}

										print('<td class="text-center">
												<div class="list-icons">
													<div class="list-icons list-icons-extended">
														<a href="#" onclick="atualizaCentroCusto('.$atualizar.','.$item['CnCusId'].', \''.$item['CnCusNome'].'\','.$item['CnCusStatus'].', \'edita\');" class="list-icons-item"><i class="icon-pencil7" data-popup="tooltip" data-placement="bottom" title="Editar"></i></a>');
														if ($item['CnCusNome'] != 'Atendimento Eletivo' && $item['CnCusNome'] != 'Atendimento Ambulatorial' && $item['CnCusNome'] != 'Atendimento Internação') {
															print('<a href="#" onclick="atualizaCentroCusto('.$excluir.','.$item['CnCusId'].', \''.$item['CnCusNome'].'\','.$item['CnCusStatus'].', \'exclui\');" class="list-icons-item"><i class="icon-bin" data-popup="tooltip" data-placement="bottom" title="Exluir"></i></a>');
														}
													print('</div>
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
				
				<form name="formCentroCusto" method="post" action="centroCustoEdita.php">
					<input type="hidden" id="inputCentroCustoId" name="inputCentroCustoId" >
					<input type="hidden" id="inputCentroCustoNome" name="inputCentroCustoNome" >
					<input type="hidden" id="inputCentroCustoStatus" name="inputCentroCustoStatus" >
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
