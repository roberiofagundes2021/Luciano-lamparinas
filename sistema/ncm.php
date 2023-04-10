<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'NCM';

include('global_assets/php/conexao.php');

$sql = "SELECT NcmId, NcmCodigo, NcmNome, NcmStatus, SituaNome, SituaChave, SituaCor
		FROM Ncm
		JOIN Situacao on SituaId = NcmStatus
		ORDER BY NcmCodigo ASC";
$result = $conn->query($sql);
$row = $result->fetchAll(PDO::FETCH_ASSOC);
$count = count($row);
//	var_dump($count);die;
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | NCM</title>

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
			$('#tblNCM').DataTable( {
				"order": [[ 0, "asc" ]],
			    autoWidth: false,
				responsive: true,
			    columnDefs: [{ 
					orderable: true,  //Codigo
					width: "15%",
					targets: [0]
				},	
				{
					orderable: true,   //Nome do NCM
					width: "55%",
					targets: [1]
				},	
				{
					orderable: true,   //Situacao
					width: "15%",
					targets: [2]
				},	
				{
					orderable: false,   //Acoes
					width: "15%",
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
		
		function atualizaNCM(NcmId, NcmNome, NcmStatus, Tipo){

			document.getElementById('inputNcmId').value = NcmId;
			document.getElementById('inputNcmNome').value = NcmNome;
			document.getElementById('inputNcmStatus').value = NcmStatus;
					
			if (Tipo == 'edita'){	
				document.formNCM.action = "ncmEdita.php";		
			} else if (Tipo == 'exclui'){
				confirmaExclusao(document.formNCM, "Tem certeza que deseja excluir esse NCM?", "ncmExclui.php");
			} else if (Tipo == 'mudaStatus'){
				document.formNCM.action = "ncmMudaSituacao.php";
			} 
			
			document.formNCM.submit();
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
								<h3 class="card-title">Relação de NCMs</h3>
								<div class="header-elements">
									<div class="list-icons">
										<a class="list-icons-item" data-action="collapse"></a>
										<a href="ncm.php" class="list-icons-item" data-action="reload"></a>
										<!--<a class="list-icons-item" data-action="remove"></a>-->
									</div>
								</div>
							</div>

							<div class="card-body">
								<p class="font-size-lg">Segue abaixo a relação de NCMs (Nomeclatura Comum Mercosul) disponíveis para os usuários do sistema.</p>
								<div class="text-right"><a href="ncmNovo.php" class="btn btn-principal" role="button">Novo NCM</a></div>
							</div>							

							<table id="tblNCM" class="table">
								<thead>
									<tr class="bg-slate">
										<th>Código NCM</th>
										<th>NCM</th>
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
											<td>'.$item['NcmCodigo'].'</td>
											<td>'.$item['NcmNome'].'</td>
											');
										
										print('<td><a href="#" onclick="atualizaNCM('.$item['NcmId'].', \''.$item['NcmNome'].'\',\''.$item['SituaChave'].'\', \'mudaStatus\');"><span class="badge '.$situacaoClasse.'">'.$situacao.'</span></a></td>');
																				
										print('<td class="text-center">
												<div class="list-icons">
													<div class="list-icons list-icons-extended">
														<a href="#" onclick="atualizaNCM('.$item['NcmId'].', \''.$item['NcmNome'].'\',\''.$item['SituaChave'].'\', \'edita\');" class="list-icons-item"><i class="icon-pencil7"></i></a>
														<a href="#" onclick="atualizaNCM('.$item['NcmId'].', \''.$item['NcmNome'].'\',\''.$item['SituaChave'].'\', \'exclui\');" class="list-icons-item"><i class="icon-bin"></i></a>
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
				
				<form name="formNCM" method="post" action="ncmEdita.php">
					<input type="hidden" id="inputNcmId" name="inputNcmId" >
					<input type="hidden" id="inputNcmNome" name="inputNcmNome" >
					<input type="hidden" id="inputNcmStatus" name="inputNcmStatus" >
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
