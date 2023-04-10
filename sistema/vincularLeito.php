<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Vincular Leito';

include('global_assets/php/conexao.php');

$sql = "SELECT VnLeiId, VnLeiTipoAcomodacao, VnLeiTipoInternacao, VnLeiEspecialidadeLeito, VnLeiAla,
				VnLeiQuarto, VnLeiObservacao, VnLeiStatus, VnLeiUsuarioAtualizador, TpAcoNome, TpIntNome, 
				EsLeiNome, QuartNome, AlaNome, SituaNome, SituaChave, SituaCor, dbo.fnLeitos(VnLeiUnidade, VnLeiId) as Leitos
		FROM VincularLeito
		JOIN Situacao on SituaId = VnLeiStatus
		LEFT JOIN TipoAcomodacao on TpAcoId = VnLeiTipoAcomodacao
		LEFT JOIN TipoInternacao on TpIntId = VnLeiTipoInternacao
		LEFT JOIN EspecialidadeLeito on EsLeiId = VnLeiEspecialidadeLeito
		LEFT JOIN Quarto on QuartId = VnLeiQuarto
		LEFT JOIN Ala on AlaId= VnLeiAla
		WHERE VnLeiUnidade = ". $_SESSION['UnidadeId'] ."
		ORDER BY VnLeiTipoAcomodacao ASC";
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
	<title>Lamparinas | Vincular Leito</title>

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
			$('#tblVincularLeito').DataTable( {
				"order": [[ 0, "asc" ]],
			    autoWidth: false,
				responsive: true,
			    columnDefs: [
				{
					orderable: true,   // Ala
					width: "12%",
					targets: [0]
				},
				{
					orderable: true,   // Tipo da Acomodação
					width: "15%",
					targets: [1]
				},	
				{
					orderable: true,   // Tipo da Internação
					width: "15%",
					targets: [2]
				},	
				{
					orderable: true,   // Especialidade do Leito
					width: "18%",
					targets: [3]
				},
				{
					orderable: true,   // Nº Quarto
					width: "15%",
					targets: [4]
				},
				{
					orderable: true,   // Leito
					width: "15%",
					targets: [5]
				},

				{ 
					orderable: true,   //Situação
					width: "5%",
					targets: [6]
				},
				{ 
					orderable: false,   //Ações
					width: "5%",
					targets: [7]
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
		function atualizaVincularLeito(Permission, VnLeiId, VnLeiTipoAcomodacao, VnLeiStatus, Tipo){
		
			if (Permission == 1){
				document.getElementById('inputVincularLeitoId').value = VnLeiId;
				document.getElementById('inputVincularLeitoTipoAcomodacao').value = VnLeiTipoAcomodacao;
				document.getElementById('inputVincularLeitoStatus').value = VnLeiStatus;
						
				if (Tipo == 'edita'){	
					document.formVincularLeito.action = "vincularLeitoEdita.php";		
				} else if (Tipo == 'exclui'){
					confirmaExclusao(document.formVincularLeito, "Tem certeza que deseja excluir essa vinculação de leito?", "vincularLeitoExclui.php");
				} else if (Tipo == 'mudaStatus'){
					document.formVincularLeito.action = "vincularLeitoMudaSituacao.php";
				}
				
				document.formVincularLeito.submit();
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
								<h3 class="card-title">Relação de Leitos Vinculados</h3>
							</div>

							<div class="card-body">
								<div class="row">
									<div class="col-lg-9">
										<p class="font-size-lg">A relação abaixo faz referência aos leitos vinculados da unidade <b><?php echo $_SESSION['UnidadeNome']; ?></b></p>
									</div>	
									<div class="col-lg-3">	
										<div class="text-right"><a href="vincularLeitoNovo.php" class="btn btn-principal" role="button">Vincular Novo Leito</a></div>
									</div>
								</div>
							</div>
							
							<table id="tblVincularLeito" class="table">
								<thead>
									<tr class="bg-slate">
										<th>Ala</th>
										<th>Tipo da Acomodação</th>
										<th>Tipo da Internação</th>
										<th>Especialidade do Leito</th>
										<th>Nº Quarto</th>
										<th>Leito</th>
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
										$leitos = strlen($item['Leitos']) > 40 ? substr($item['Leitos'], 0, 40)."..." : $item['Leitos'];

										print('
										<tr>
											<td>'.$item['AlaNome'].'</td>
											<td>'.$item['TpAcoNome'].'</td>
											<td>'.$item['TpIntNome'].'</td>
											<td>'.$item['EsLeiNome'].'</td>
											<td>'.$item['QuartNome'].'</td>
											<td data-popup="tooltip" title="'.$item['Leitos'].'">'.$leitos.'</td>
											');
										
										print('<td><a href="#" onclick="atualizaVincularLeito(1,'.$item['VnLeiId'].', \''.$item['VnLeiTipoAcomodacao'].'\','.$situacaoChave.', \'mudaStatus\');"><span class="badge '.$situacaoClasse.'">'.$situacao.'</span></a></td>');
										
										print('<td class="text-center">
												<div class="list-icons">
													<div class="list-icons list-icons-extended">
														<a href="#" onclick="atualizaVincularLeito('.$atualizar.','.$item['VnLeiId'].', \''.$item['VnLeiTipoAcomodacao'].'\','.$item['VnLeiStatus'].', \'edita\');" class="list-icons-item"><i class="icon-pencil7" data-popup="tooltip" data-placement="bottom" title="Editar"></i></a>
														<a href="#" onclick="atualizaVincularLeito('.$excluir.','.$item['VnLeiId'].', \''.$item['VnLeiTipoAcomodacao'].'\','.$item['VnLeiStatus'].', \'exclui\');" class="list-icons-item"><i class="icon-bin" data-popup="tooltip" data-placement="bottom" title="Exluir"></i></a>														
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
				
				<form name="formVincularLeito" method="post">
					<input type="hidden" id="inputVincularLeitoId" name="inputVincularLeitoId" >
					<input type="hidden" id="inputVincularLeitoTipoAcomodacao" name="inputVincularLeitoTipoAcomodacao" >
					<input type="hidden" id="inputVincularLeitoStatus" name="inputVincularLeitoStatus" >
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
