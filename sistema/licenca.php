<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Licença';

include('global_assets/php/conexao.php');

if (isset($_POST['inputEmpresaId'])){
	$_SESSION['EmpresaId'] = $_POST['inputEmpresaId'];
	$_SESSION['EmpresaNome'] = $_POST['inputEmpresaNome'];
}

if (!isset($_SESSION['EmpresaId'])) {
	irpara("empresa.php");
}

$sql = "SELECT LicenId, LicenDtInicio, LicenDtFim, LicenLimiteUsuarios, LicenStatus, SituaNome, SituaChave, SituaCor
		FROM Licenca
		JOIN Situacao on SituaId = LicenStatus
		WHERE LicenEmpresa = ".$_SESSION['EmpresaId']."
		ORDER BY LicenDtInicio DESC"; 		
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
	<title>Lamparinas | Licenças</title>

	<?php include_once("head.php"); ?>
	
	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/tables/datatables/datatables.min.js"></script>
	<script src="global_assets/js/plugins/tables/datatables/extensions/responsive.min.js"></script>

	<script src="global_assets/js/demo_pages/datatables_responsive.js"></script>
	<script src="global_assets/js/demo_pages/datatables_sorting.js"></script>	
	
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
	
	<script src="global_assets/js/demo_pages/form_layouts.js"></script>
	<script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>

	<!-- Plugin para corrigir a ordenação por data. Caso a URL dê problema algum dia, salvei esses 2 arquivos na pasta global_assets/js/lamparinas -->
	<script type="text/javascript" language="javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.8.4/moment.min.js"></script>
	<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/plug-ins/1.10.10/sorting/datetime-moment.js"></script>
	
	<script type="text/javascript">
		
		$(document).ready(function (){	

			$.fn.dataTable.moment('DD/MM/YYYY'); //Para corrigir a ordenação por data

			$('#tblLicenca').DataTable( {
				"order": [[ 0, "desc" ]],
			    autoWidth: false,
				responsive: true,
			    columnDefs: [
				{
					orderable: true,   //Data Início
					width: "30%",
					targets: [0]
				},
				{
					orderable: true,   //Data Fim
					width: "30%",
					targets: [1]
				},
				{
					orderable: true,   //Limite Usuários
					width: "20%",
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

		function atualizaLicenca(LicenId, LicenStatus, Tipo){

			document.getElementById('inputLicencaId').value = LicenId;
			document.getElementById('inputLicencaStatus').value = LicenStatus;
				
			if (Tipo == 'edita'){	
				document.formLicenca.action = "licencaEdita.php";		
			} else if (Tipo == 'exclui'){
				confirmaExclusao(document.formLicenca, "Tem certeza que deseja excluir essa licença?", "licencaExclui.php");
			} else if (Tipo == 'mudaStatus'){
				document.formLicenca.action = "licencaMudaSituacao.php";
			}
			
			document.formLicenca.submit();
		}
			
	</script>

</head>

<body class="navbar-top sidebar-xs">

	<?php include_once("topo.php"); ?>	

	<!-- Page content -->
	<div class="page-content">
		
		<?php include_once("menu-left.php"); ?>

		<?php include_once("menuLeftSecundario.php"); ?>
		
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
								<h5 class="card-title">Relação das Licenças</h5>
							</div>

							<div class="card-body">
								<div class="row">
									<div class="col-lg-9">
										As licenças abaixo são da empresa <b><?php echo $_SESSION['EmpresaNome']; ?></b>.
									</div>	
									<div class="col-lg-3">
										<div class="text-right"><a href="licencaNovo.php" class="btn btn-principal" role="button">Nova Licença</a></div>
									</div>
								</div>
							</div>							

							<table id="tblLicenca" class="table">
								<thead>
									<tr class="bg-slate">
										<th>Data Início</th>
										<th>Data Fim</th>
										<th>Limite de Usuários</th>
										<th>Situação</th>
										<th class="text-center">Ações</th>
									</tr>
								</thead>
								<tbody>
								<?php

									$cont = 1;

									foreach ($row as $item){
										
										$situacao = $item['SituaNome'];
										$situacaoClasse = 'badge badge-flat border-'.$item['SituaCor'].' text-'.$item['SituaCor'];
										
										print('
										<tr>
											<td>'.mostraData($item['LicenDtInicio']).'</td>
											<td>'.mostraData($item['LicenDtFim']).'</td>
											<td>'.$item['LicenLimiteUsuarios'].'</td>');										
										
										if ($cont == 1){
											print('<td><a href="#" onclick="atualizaLicenca('.$item['LicenId'].',\''.$item['SituaChave'].'\', \'mudaStatus\')"><span class="badge '.$situacaoClasse.'">'.$situacao.'</span></a></td>');

											print('<td class="text-center">
														<div class="list-icons">
															<div class="list-icons list-icons-extended">
																<a href="#" onclick="atualizaLicenca('.$item['LicenId'].',\''.$item['SituaChave'].'\', \'edita\')" class="list-icons-item"><i class="icon-pencil7"></i></a>
																<a href="#" onclick="atualizaLicenca('.$item['LicenId'].',\''.$item['SituaChave'].'\', \'exclui\')" class="list-icons-item"><i class="icon-bin"></i></a>														
															</div>
														</div>
													</td>');
										} else{
											print('<td><span class="badge '.$situacaoClasse.'">'.$situacao.'</span></td>');
											print('<td></td>');
										}

										print('</tr>');

										$cont++;
									}
								?>

								</tbody>
							</table>
						</div>
						<!-- /basic responsive configuration -->

					</div>
				</div>				
				
				<!-- /info blocks -->
				
				<form name="formLicenca" method="post" action="licencaEdita.php">
					<input type="hidden" id="inputLicencaId" name="inputLicencaId" >
					<input type="hidden" id="inputLicencaStatus" name="inputLicencaStatus" >
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
