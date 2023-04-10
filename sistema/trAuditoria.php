<?php 

include_once("sessao.php"); 
include('global_assets/php/conexao.php');

$_SESSION['PaginaAtual'] = 'Auditoria';

include('global_assets/php/conexao.php');

if (isset($_POST['inputTRId'])){
	$sql = "SELECT AdiTRId, AdiTRTermoReferencia, AdiTRDataHora, AdiTRUsuario, AdiTRTela, AdiTRDetalhamento,UsuarNome
			FROM AuditTR
			JOIN Usuario ON UsuarId = AdiTRUsuario
			WHERE AdiTRTermoReferencia = ".$_POST['inputTRId']."
			ORDER BY AdiTRDataHora ASC";
	$result = $conn->query($sql);
	$row = $result->fetchAll(PDO::FETCH_ASSOC);
} else{
	irpara("tr.php");
}


?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Auditoria</title>

	<?php include_once("head.php"); ?>
	
	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/tables/datatables/datatables.min.js"></script>
	<script src="global_assets/js/plugins/tables/datatables/extensions/responsive.min.js"></script>
	
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
	
	<script src="global_assets/js/demo_pages/datatables_responsive.js"></script>
	<script src="global_assets/js/demo_pages/datatables_sorting.js"></script>

	<!-- Não permite que o usuário retorne para o EDITAR -->
	<script src="global_assets/js/lamparinas/stop-back.js"></script>

	<script type="text/javascript">

		$(document).ready(function (){	
			$('#tblAuditotia').DataTable( {
				"order": [[ 0, "desc" ]],
			    autoWidth: false,
				responsive: true,
			    columnDefs: [
				{
					orderable: true,   //DATA/HORA
					width: "17%",
					targets: [0]
				},	
				{
					orderable: true,   //USUÁRIO
					width: "13%",
					targets: [1]
				},
				{ 
					orderable: true,   //TELA
					width: "30%",
					targets: [2]
				},
				{ 
					orderable: true,   //LOG
					width: "40%",
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
								<h3 class="card-title">Relação da Auditoria</h3>
							</div>

							<div class="card-body">
								<div class="row">
									<div class="col-lg-12">
										<p class="font-size-lg">A relação abaixo faz referência as alterações realizadas no Termo de Referência<span style="color: #FF0000; font-weight: bold;"> Nº <?php echo $_POST['inputTRNumero']; ?></p>
										<div class="text-right"><a href="tr.php" role="button"><< Termo de Referência</a>&nbsp;&nbsp;&nbsp;</div>
									</div>	
								</div>		
							</div>
							
							<table class="table" id="tblAuditotia">
								<thead>
									<tr class="bg-slate">
										<th>Data/Hora</th>
										<th>Usuário</th>
										<th>Tela</th>
										<th>Detalhamento</th>
									</tr>
								</thead>
								<tbody>
                                    <?php
                                        foreach ($row as $item){											
                                            
                                            print('
                                            <tr>
                                                <td>'.mostraDataHora($item['AdiTRDataHora']).'</td>
                                                <td>'.nomeSobrenome($item['UsuarNome'], 2).'</td>
                                                <td>'.$item['AdiTRTela'].'</td>
                                                <td>'.$item['AdiTRDetalhamento'].'</td>
                                            ');
                                            
                                        }
                                    ?>
								</tbody>
							</table>
						</div>
					</div>
				</div>				
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
