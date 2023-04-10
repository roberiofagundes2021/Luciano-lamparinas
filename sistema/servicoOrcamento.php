<?php 

include_once("sessao.php"); 
include('global_assets/php/conexao.php');

$_SESSION['PaginaAtual'] = 'Serviço para Termo de Referência';

include('global_assets/php/conexao.php');

$sql = "SELECT SrOrcNome, CategNome, SbCatNome, SrOrcSituacao, SrOrcId, SituaNome, SituaCor, SituaChave
		FROM ServicoOrcamento
		JOIN Categoria on CategId = SrOrcCategoria
		LEFT JOIN SubCategoria on SbCatId = SrOrcSubcategoria
		JOIN Situacao on SituaId = SrOrcSituacao
	    WHERE SrOrcEmpresa = ". $_SESSION['EmpreId'] ."
		ORDER BY SrOrcNome ASC";
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
	<title>Lamparinas | Serviços para Termo de Referência</title>

	<?php include_once("head.php"); ?>
	
	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/tables/datatables/datatables.min.js"></script>
	<script src="global_assets/js/plugins/tables/datatables/extensions/responsive.min.js"></script>
	
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
	
	<script src="global_assets/js/demo_pages/datatables_responsive.js"></script>
	<script src="global_assets/js/demo_pages/datatables_sorting.js"></script>

	<!-- Não permite que o usuário retorne para o EDITAR -->
	<script src="global_assets/js/lamparinas/stop-back.js"></script>	
			
	</script>

	<script type="text/javascript">

		$(document).ready(function (){	
			$('#tblServicoOrcamento').DataTable( {
				"order": [[ 0, "asc" ]],
			    autoWidth: false,
				responsive: true,
			    columnDefs: [
				{
					orderable: true,   //Servico
					width: "30%",
					targets: [0]
				},	
				{
					orderable: true,   //Categoria
					width: "25%",
					targets: [1]
				},
				{ 
					orderable: true,   //SubCategoria
					width: "25%",
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
			
		function atualizaOrcamento(Permission, SrOrcId, SrOrcNome, SrOrcStatus, Tipo){
            
			
			document.getElementById('inputSrOrcId').value = SrOrcId;
			document.getElementById('inputSrOrcNome').value = SrOrcNome;
			document.getElementById('inputSrOrcStatus').value = SrOrcStatus;
			document.getElementById('inputPermission').value = Permission;

			if (Tipo == 'edita'){	
				document.formSrOrc.action = "servicoOrcamentoEdita.php";		
			} else if (Tipo == 'mudaStatus'){
				document.formSrOrc.action = "servicoOrcamentoMudaStatus.php";
			} else if (Tipo == 'exclui'){
				if(Permission){
					confirmaExclusao(document.formSrOrc, "Tem certeza que deseja excluir esse serviço?", "servicoOrcamentoExclui.php");
				} else{
					alerta('Permissão Negada!','');
					return false;
				}
			}

			document.formSrOrc.submit();		
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
								<h3 class="card-title">Relação de Serviços para Termo de Referência</h3>
								<div class="header-elements">
									<div class="list-icons">
										<a class="list-icons-item" data-action="collapse"></a>
										<a href="modelo.php" class="list-icons-item" data-action="reload"></a>
										<!--<a class="list-icons-item" data-action="remove"></a>-->
									</div>
								</div>
							</div>

							<div class="card-body">
							<div class="row">
									<div class="col-lg-9">
										<p class="font-size-lg">A relação abaixo faz referência aos serviços para Termo de Referência da unidade <b><?php echo $_SESSION['UnidadeNome']; ?></b></p>
									</div>			
									<div class="col-lg-3">
										<div class="text-right"><a href="servicoOrcamentoNovo.php" class="btn btn-principal" role="button">Novo Serviço</a></div>
									</div>
								</div>
							</div>
							
							<table class="table" id="tblServicoOrcamento">
								<thead>
									<tr class="bg-slate">
										<th>Serviço</th>
										<th>Categoria</th>
										<th>Subcategoria</th>
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
											<td>'.$item['SrOrcNome'].'</td>
											<td>'.$item['CategNome'].'</td>
											<td>'.$item['SbCatNome'].'</td>
											');
										
										print('<td><a href="#" onclick="atualizaOrcamento(1,'.$item['SrOrcId'].', \''.htmlentities(addslashes($item['SrOrcNome']), ENT_QUOTES).'\',\''.$item['SituaChave'].'\', \'mudaStatus\');"><span class="badge '.$situacaoClasse.'">'.$situacao.'</span></a></td>');
										
										print('<td class="text-center">
												<div class="list-icons">
													<div class="list-icons list-icons-extended">
														<a href="#" onclick="atualizaOrcamento('.$atualizar.','.$item['SrOrcId'].', \''.htmlentities(addslashes($item['SrOrcNome']), ENT_QUOTES).'\','.$item['SrOrcSituacao'].', \'edita\');" class="list-icons-item"><i class="icon-pencil7" data-popup="tooltip" data-placement="bottom" title="Editar Serviço"></i></a>
														<a href="#" onclick="atualizaOrcamento('.$excluir.','.$item['SrOrcId'].', \''.htmlentities(addslashes($item['SrOrcNome']), ENT_QUOTES).'\','.$item['SrOrcSituacao'].', \'exclui\');" class="list-icons-item"><i class="icon-bin" data-popup="tooltip" data-placement="bottom" title="Exluir Serviço"></i></a>							
													</div>
												</div>
											</td>
										</tr>');
									}
								?>
								</tbody>
							</table>
						</div>
					</div>
				</div>				
				<form name="formSrOrc" method="post">
					<input type="hidden" id="inputPermission" name="inputPermission" >
					<input type="hidden" id="inputSrOrcId" name="inputSrOrcId" >
					<input type="hidden" id="inputSrOrcNome" name="inputSrOrcNome" >
					<input type="hidden" id="inputSrOrcStatus" name="inputSrOrcStatus" >
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
