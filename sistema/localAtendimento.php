<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Local de Atendimento';

include('global_assets/php/conexao.php');

$sql = "SELECT AtLocId, AtLocNome, AtLocCNES, AtLocStatus, SituaNome, SituaCor, SituaChave
		FROM AtendimentoLocal
		JOIN Situacao on SituaId = AtLocStatus
	    WHERE AtLocUnidade = ". $_SESSION['UnidadeId'] ."
		ORDER BY AtLocNome ASC";
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
	<title>Lamparinas | Local de Atendimento</title>

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
			
			/* Início: Tabela Personalizada */
			$('#tblAtendimentoLocal').DataTable( {
				"order": [[ 0, "asc" ]],
			    autoWidth: false,
				responsive: true,
			    columnDefs: [
				{
					orderable: true,   //Local de Atendimento
					width: "50%",
					targets: [0]
				},	
				{
					orderable: true,   //CNES
					width: "30%",
					targets: [1]
				},
				{ 
					orderable: true,   //Situação
					width: "10%",
					targets: [2]
				},
				{ 
					orderable: false,   //Ações
					width: "10%",
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
		function atualizaAtendimentoLocal(Permission, AtendimentoLocalId,AtendimentoLocalNome, AtendimentoLocalStatus, Tipo){
		
			if (Permission == 1){
				document.getElementById('inputAtendimentoLocalId').value = AtendimentoLocalId;
				document.getElementById('inputAtendimentoLocalNome').value = AtendimentoLocalNome;
				document.getElementById('inputAtendimentoLocalStatus').value = AtendimentoLocalStatus;
						
				if (Tipo == 'edita'){	
					document.formAtendimentoLocal.action = "localAtendimentoEdita.php";		
				} else if (Tipo == 'exclui'){
					confirmaExclusao(document.formAtendimentoLocal, "Tem certeza que deseja excluir esse local de atendimento?", "localAtendimentoExclui.php");
				} else if (Tipo == 'mudaStatus'){
					document.formAtendimentoLocal.action = "localAtendimentoMudaSituacao.php";
				}
				
				document.formAtendimentoLocal.submit();
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
								<h3 class="card-title">Relação dos locais de atendimento</h3>
								<div class="header-elements">
									<div class="list-icons">
										<a class="list-icons-item" data-action="collapse"></a>
										<a href="subcategoria.php" class="list-icons-item" data-action="reload"></a>
										<!--<a class="list-icons-item" data-action="remove"></a>-->
									</div>
								</div>
							</div>

							<div class="card-body">
								<div class="row">
									<div class="col-lg-9">
										<p class="font-size-lg">A relação abaixo faz referência aos locais de atendimento da unidade <b><?php echo $_SESSION['UnidadeNome']; ?></b></p>
									</div>
									<div class="col-lg-3">	
										<div class="text-right"><a href="localAtendimentoNovo.php" class="btn btn-principal" role="button">Novo local de atendimento</a></div>
									</div>
								</div>
							</div>
							
							<table id="tblAtendimentoLocal" class="table">
								<thead>
									<tr class="bg-slate">
										<th>Local de Atendimento</th>
										<th>CNES</th>
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
											<td>'.$item['AtLocNome'].'</td>
											<td>'.$item['AtLocCNES'].'</td>
											');
										
										print('<td><a href="#" onclick="atualizaAtendimentoLocal(1,'.$item['AtLocId'].', \''.$item['AtLocNome'].'\','.$situacaoChave.', \'mudaStatus\');"><span class="badge '.$situacaoClasse.'">'.$situacao.'</span></a></td>');
										
										print('<td class="text-center">
												<div class="list-icons">
													<div class="list-icons list-icons-extended">
														<a href="#" onclick="atualizaAtendimentoLocal(1,'.$item['AtLocId'].', \''.$item['AtLocNome'].'\','.$item['AtLocStatus'].', \'edita\');" class="list-icons-item"><i class="icon-pencil7" data-popup="tooltip" data-placement="bottom" title="Editar"></i></a>
														<a href="#" onclick="atualizaAtendimentoLocal(1,'.$item['AtLocId'].', \''.$item['AtLocNome'].'\','.$item['AtLocStatus'].', \'exclui\');" class="list-icons-item"><i class="icon-bin" data-popup="tooltip" data-placement="bottom" title="Exluir"></i></a>														
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
				
				<form name="formAtendimentoLocal" method="post">
					<input type="hidden" id="inputAtendimentoLocalId" name="inputAtendimentoLocalId" >
					<input type="hidden" id="inputAtendimentoLocalNome" name="inputAtendimentoLocalNome" >
					<input type="hidden" id="inputAtendimentoLocalStatus" name="inputAtendimentoLocalStatus" >
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
