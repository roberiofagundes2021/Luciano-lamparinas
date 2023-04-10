<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Plano de Contas';

include('global_assets/php/conexao.php');

$sql = "SELECT PlConId, PlConCodigo, PlConNome, PlConTipo, PlConNatureza, PlConGrupo, PlConDetalhamento, PlConPlanoContaPai, PlConStatus, SituaNome, SituaCor, SituaChave, GrConNome
		FROM PlanoConta
		LEFT JOIN GrupoConta on GrConId = PlConGrupo
		JOIN Situacao on SituaId = PlConStatus
	    WHERE PlConUnidade = ". $_SESSION['UnidadeId'] ."
		ORDER BY PlConNome ASC";
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
	<title>Lamparinas | Plano de Contas</title>

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
			$('#tblPlanoContas').DataTable( {
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
					orderable: true,   //Título
					width: "25%",
					targets: [1]
				},	
				{
					orderable: true,   //Tipo
					width: "10%",
					targets: [2]
				},
				{
					orderable: true,   //Natureza
					width: "10%",
					targets: [3]
				},
				{
					orderable: true,   //Grupo de Contas
					width: "25%",
					targets: [4]
				},

				{ 
					orderable: true,   //Situação
					width: "10%",
					targets: [5]
				},
				{ 
					orderable: false,   //Ações
					width: "10%",
					targets: [6]
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
		function atualizaPlanoContas(Permission, PlConId, PlConNome, PlConStatus, Tipo){
		
			if (Permission == 1){
				document.getElementById('inputPlanoContasId').value = PlConId;
				document.getElementById('inputPlanoContasNome').value = PlConNome;
				document.getElementById('inputPlanoContasStatus').value = PlConStatus;
						
				if (Tipo == 'edita'){	
					document.formPlanoContas.action = "planoContasEdita.php";		
				} else if (Tipo == 'exclui'){
					confirmaExclusao(document.formPlanoContas, "Tem certeza que deseja excluir esse Plano de Contas?", "planoContasExclui.php");
				} else if (Tipo == 'mudaStatus'){
					document.formPlanoContas.action = "planoContasMudaSituacao.php";
				}
				
				document.formPlanoContas.submit();
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
								<h3 class="card-title">Relação de Planos de Contas</h3>
							</div>

							<div class="card-body">
								<div class="row">
									<div class="col-lg-9">
										<p class="font-size-lg">A relação abaixo faz referência às Planos de Contas da unidade <b><?php echo $_SESSION['UnidadeNome']; ?></b></p>
									</div>	
									<div class="col-lg-3">	
										<div class="text-right"><a href="planoContasNovo.php" class="btn btn-principal" role="button">Novo Plano de Contas</a></div>
									</div>
								</div>
							</div>
							
							<table id="tblPlanoContas" class="table">
								<thead>
									<tr class="bg-slate">
										<th>Código</th>
										<th>Título</th>
										<th>Tipo</th>
										<th>Natureza</th>
										<th>Grupo de Conta</th>
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
										
										$tipo = $item['PlConTipo'] == 'A' ? 'Analítico' : 'Sintético';
										$Natureza = $item['PlConNatureza'] == 'D' ? 'Despesa' : 'Receita';

										print('
										<tr>
											<td>'.$item['PlConCodigo'].'</td>
											<td>'.$item['PlConNome'].'</td>
											<td>'.$tipo.'</td>
											<td>'.$Natureza.'</td>
											<td>'.$item['GrConNome'].'</td>
											');
										
										print('<td><a href="#" onclick="atualizaPlanoContas(1,'.$item['PlConId'].', \''.$item['PlConNome'].'\','.$situacaoChave.', \'mudaStatus\');"><span class="badge '.$situacaoClasse.'">'.$situacao.'</span></a></td>');
										
										print('<td class="text-center">
												<div class="list-icons">
													<div class="list-icons list-icons-extended">
														<a href="#" onclick="atualizaPlanoContas('.$atualizar.','.$item['PlConId'].', \''.$item['PlConNome'].'\','.$item['PlConStatus'].', \'edita\');" class="list-icons-item"><i class="icon-pencil7" data-popup="tooltip" data-placement="bottom" title="Editar"></i></a>
														<a href="#" onclick="atualizaPlanoContas('.$excluir.','.$item['PlConId'].', \''.$item['PlConNome'].'\','.$item['PlConStatus'].', \'exclui\');" class="list-icons-item"><i class="icon-bin" data-popup="tooltip" data-placement="bottom" title="Exluir"></i></a>														
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
				
				<form name="formPlanoContas" method="post">
					<input type="hidden" id="inputPlanoContasId" name="inputPlanoContasId" >
					<input type="hidden" id="inputPlanoContasNome" name="inputPlanoContasNome" >
					<input type="hidden" id="inputPlanoContasStatus" name="inputPlanoContasStatus" >
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
