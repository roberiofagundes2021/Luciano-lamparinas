<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Evolução Anexo';

include('global_assets/php/conexao.php');

if (isset($_POST['idEvolucaoAnexo'])){

	$_SESSION['idEvolucaoAnexo'] = $_POST['idEvolucaoAnexo'];
	$_SESSION['nomeCliente'] = $_POST['inputClienteEvolucao']; 
	$_SESSION['atendNumRegistro'] = $_POST['inputAtendimento']; 

}

$sql = "SELECT EnEAnId, EnEAnData, EnEAnNome, EnEAnArquivo
        FROM EnfermagemEvolucaoAnexo
        WHERE EnEAnUnidade = ". $_SESSION['UnidadeId'] ." and EnEAnEnfermagemEvolucao = ". $_SESSION['idEvolucaoAnexo'] ."
        ORDER BY EnEAnNome ASC";

$result = $conn->query($sql);
$row = $result->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Evolução de Enfermagem Anexo</title>

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
			$('#tblEvolucaoAnexo').DataTable( {
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
		function atualizaEvolucaoAnexo(EnEAnId, EnEAnData, EnEAnNome, EnEAnArquivo, Tipo){

				document.getElementById('inputEvolucaoAnexoId').value = EnEAnId;
				document.getElementById('inputEvolucaoAnexoNome').value = EnEAnNome;
				document.getElementById('inputEvolucaoAnexoData').value = EnEAnData;
				document.getElementById('inputEvolucaoAnexoArquivo').value = EnEAnArquivo;	

				if (Tipo == 'edita'){	
					document.formEvolucaoAnexo.action = "evolucaoAnexoEdita.php";		
				} else if (Tipo == 'exclui'){
					confirmaExclusao(document.formEvolucaoAnexo, "Tem certeza que deseja excluir esse Anexo", "evolucaoAnexoExclui.php");
			}
			
			document.formEvolucaoAnexo.submit();
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
								<h3 class="card-title">Relação de Anexos da Evolução de Enfermagem</h3>
							</div>

							<div class="card-body">
								<div class="row">
									<div class="col-lg-8">
										A relação abaixo faz referência aos Anexos da Evolução de Enfermagem do cliente <span style="color: #FF0000; font-weight: bold;"> <?php echo $_SESSION['nomeCliente']; ?> </span>, do atendimento de Número: <?php echo $_SESSION['atendNumRegistro']; ?> da unidade <b><?php echo $_SESSION['UnidadeNome']; ?></b>
									</div>
									
									<div class= "col-lg-4 text-right">
										<a href="evolucaoAnexoNovo.php" class="btn btn-principal" role="button">Novo Anexo</a>

										<a href="#" onClick="window.open('', '_self', ''); window.close();" class="btn btn-primary" role="button">Voltar</a>
                                    
                                    </div>
									</div>
								</div>
							<table class="table" id="tblEvolucaoAnexo">
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
										    <td>'.mostraData($item['EnEAnData']).'</td>
                                            <td>'.$item['EnEAnNome'].'</td>
											<td><a href="global_assets/anexos/evolucaoEnfermagem/'.$item['EnEAnArquivo'].'" target="_blank">'.$item['EnEAnArquivo'].'</a></td>
											');
																										
										print('<td class="text-center">
												<div class="list-icons">
													<div class="list-icons list-icons-extended">
															<a href="#" onclick="atualizaEvolucaoAnexo('.$item['EnEAnId'].', \''.$item['EnEAnData'].'\',\''.$item['EnEAnNome'].'\', \''.$item['EnEAnArquivo'].'\', \'edita\');" class="list-icons-item"><i class="icon-pencil7" data-popup="tooltip" data-placement="bottom" title="Editar"></i></a>
															<a href="#" onclick="atualizaEvolucaoAnexo('.$item['EnEAnId'].', \''.$item['EnEAnData'].'\',\''.$item['EnEAnNome'].'\', \''.$item['EnEAnArquivo'].'\', \'exclui\');" class="list-icons-item"><i class="icon-bin" data-popup="tooltip" data-placement="bottom" title="Exluir"></i></a>														
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
				
				<form name="formEvolucaoAnexo" method="post">
					<input type="hidden" id="inputEvolucaoAnexoId" name="inputEvolucaoAnexoId">
					<input type="hidden" id="inputEvolucaoAnexoData" name="inputEvolucaoAnexoData">
					<input type="hidden" id="inputEvolucaoAnexoNome" name="inputEvolucaoAnexoNome">
					<input type="hidden" id="inputEvolucaoAnexoArquivo" name="inputEvolucaoAnexoArquivo">
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
