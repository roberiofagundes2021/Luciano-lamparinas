<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Cliente Anexo';

include('global_assets/php/conexao.php');

if (isset($_POST['inputClienteId'])){
	$_SESSION['idCliente'] = $_POST['inputClienteId'];
	$_SESSION['nomeCliente'] = $_POST['inputClienteNome']; 
}

$sql = "SELECT ClAneId, ClAneData, ClAneNome, ClAneArquivo
        FROM ClienteAnexo
        WHERE ClAneUnidade = ". $_SESSION['UnidadeId'] ." and ClAneCliente = ". $_SESSION['idCliente'] ."
        ORDER BY ClAneNome ASC";
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
	<title>Lamparinas | Cliente Anexo</title>

	<?php include_once("head.php"); ?>
	
	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/tables/datatables/datatables.min.js"></script>
	<script src="global_assets/js/plugins/tables/datatables/extensions/responsive.min.js"></script>
	
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
	<script src="global_assets/js/demo_pages/form_select2.js"></script>
	
	<script src="global_assets/js/demo_pages/form_layouts.js"></script>
	<script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>	

	<script src="global_assets/js/demo_pages/datatables_responsive.js"></script>
	<script src="global_assets/js/demo_pages/datatables_sorting.js"></script>
	<script src="global_assets/js/lamparinas/stop-back.js"></script>
	
	<!-- /theme JS files -->	
	
	<script type="text/javascript">
		
		$(document).ready(function() {
			
			/* Início: Tabela Personalizada */
			$('#tblClienteAnexo').DataTable( {
				"order": [[ 0, "desc" ]],
			    autoWidth: false,
				responsive: true,
			    columnDefs: [{ 
					orderable: true,   //Data
					width: "14%",
					targets: [0]
				},
				{ 
					orderable: true,   //Nome
					width: "38%",
					targets: [1]
				},				
				{ 
					orderable: true,   //Arquivo
					width: "38%",
					targets: [2]
				},
				{ 
					orderable: false,  //Ações
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
		function atualizaClienteAnexo(ClAneId, ClAneData, ClAneNome, ClAneArquivo, Tipo){

				document.getElementById('inputClienteAnexoId').value = ClAneId;
				document.getElementById('inputClienteAnexoNome').value = ClAneNome;
				document.getElementById('inputClienteAnexoData').value = ClAneData;
				document.getElementById('inputClienteAnexoArquivo').value = ClAneArquivo;	

				if (Tipo == 'edita'){	
					document.formClienteAnexo.action = "clienteAnexoEdita.php";		
				} else if (Tipo == 'exclui'){
					confirmaExclusao(document.formClienteAnexo, "Tem certeza que deseja excluir esse Anexo", "clienteAnexoExclui.php");
			}
			
			document.formClienteAnexo.submit();
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
								<h3 class="card-title">Relação de Anexos</h3>
							</div>

							<div class="card-body">
								<div class="row">
									<div class="col-lg-9">
										A relação abaixo faz referência aos Anexos do cliente <span style="color: #FF0000; font-weight: bold;"> <?php echo $_SESSION['nomeCliente']; ?> </span> da unidade <b><?php echo $_SESSION['UnidadeNome']; ?></b>
									</div>
									<div class="col-lg-3">
										<div class="text-right" style="margin-top: -10px;">
											<a href="cliente.php" role="button"><< Relação de Cliente</a>&nbsp;&nbsp;&nbsp;
											<a href="clienteAnexoNovo.php" class="btn btn-principal" role="button">Novo Anexo</a>
										</div>
									</div>
								</div>
							</div>
							
							<table class="table" id="tblClienteAnexo">
								<thead>
									<tr class="bg-slate">
                                        <th>Data</th>
										<th>Descrição</th>
										<th>Arquivo</th>										
										<th class="text-center">Ações</th>
									</tr>
								</thead>
								<tbody>
								<?php

									foreach ($row as $item){
										
										print('
										<tr>
										    <td>'.mostraData($item['ClAneData']).'</td>
                                            <td>'.$item['ClAneNome'].'</td>
											<td><a href="global_assets/anexos/cliente/'.$item['ClAneArquivo'].'" target="_blank">'.$item['ClAneArquivo'].'</a></td>
											');
																										
										print('<td class="text-center">
												<div class="list-icons">
													<div class="list-icons list-icons-extended">
															<a href="#" onclick="atualizaClienteAnexo('.$item['ClAneId'].', \''.$item['ClAneData'].'\',\''.$item['ClAneNome'].'\', \''.$item['ClAneArquivo'].'\', \'edita\');" class="list-icons-item"><i class="icon-pencil7" data-popup="tooltip" data-placement="bottom" title="Editar"></i></a>
															<a href="#" onclick="atualizaClienteAnexo('.$item['ClAneId'].', \''.$item['ClAneData'].'\',\''.$item['ClAneNome'].'\', \''.$item['ClAneArquivo'].'\', \'exclui\');" class="list-icons-item"><i class="icon-bin" data-popup="tooltip" data-placement="bottom" title="Exluir"></i></a>														
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
				
				<form name="formClienteAnexo" method="post">
					<input type="hidden" id="inputClienteAnexoId" name="inputClienteAnexoId">
					<input type="hidden" id="inputClienteAnexoData" name="inputClienteAnexoData">
					<input type="hidden" id="inputClienteAnexoNome" name="inputClienteAnexoNome">
					<input type="hidden" id="inputClienteAnexoArquivo" name="inputClienteAnexoArquivo">
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
