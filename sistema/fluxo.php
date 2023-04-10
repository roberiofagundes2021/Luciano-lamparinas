<?php 

include_once("sessao.php"); 

//$inicio = microtime(true);

$_SESSION['PaginaAtual'] = 'Fluxo Operacional';

include('global_assets/php/conexao.php'); 

$sql = "SELECT DISTINCT FlOpeId, ForneRazaoSocial, FlOpeCategoria, FlOpeDataInicio, FlOpeDataFim, 
		FlOpeNumContrato, FlOpeValor, FlOpeStatus, CategNome, SituaChave, 
		SituaNome, SituaCor, dbo.fnSubCategoriasFluxo(FlOpeEmpresa, FlOpeId) as SubCategorias, 
		dbo.fnFluxoFechado(FlOpeId, FlOpeUnidade) as FluxoFechado, BandeMotivo,
		dbo.fnFimContrato(FlOpeId) as FimContrato
		FROM FluxoOperacional
		JOIN Categoria on CategId = FlOpeCategoria
		JOIN Fornecedor on ForneId = FlOpeFornecedor
		JOIN Situacao on SituaId = FlOpeStatus
		LEFT JOIN Bandeja on BandeTabelaId = FlOpeId and BandeTabela = 'FluxoOperacional' and 
							 BandeUnidade = ".$_SESSION['UnidadeId']." and SituaChave = 'NAOLIBERADO'
	    WHERE FlOpeUnidade = ". $_SESSION['UnidadeId'] ."
		ORDER BY FlOpeDataInicio DESC, FlOpeCategoria ASC";
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
	<title>Lamparinas | Fluxo Operacional</title>

	<?php include_once("head.php"); ?>

	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/tables/datatables/datatables.min.js"></script>
	<script src="global_assets/js/plugins/tables/datatables/extensions/responsive.min.js"></script>
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>

	<script src="global_assets/js/demo_pages/datatables_responsive.js"></script>
	<script src="global_assets/js/demo_pages/datatables_sorting.js"></script>
	<!-- /theme JS files -->

	<!-- Plugin para corrigir a ordenação por data. Caso a URL dê problema algum dia, salvei esses 2 arquivos na pasta global_assets/js/lamparinas -->
	<script type="text/javascript" language="javascript"
		src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.8.4/moment.min.js"></script>
	<script type="text/javascript" language="javascript"
		src="https://cdn.datatables.net/plug-ins/1.10.10/sorting/datetime-moment.js"></script>

	<!-- Modal -->
	<script src="global_assets/js/plugins/notifications/bootbox.min.js"></script>

	<script type="text/javascript">
		$(document).ready(function () {

			$.fn.dataTable.moment('DD/MM/YYYY'); //Para corrigir a ordenação por data

			/* Início: Tabela Personalizada */
			$('#tblFluxo').DataTable({
				"order": [
					[0, "desc"]
				],
				autoWidth: false,
				responsive: true,
				columnDefs: [{
					orderable: false,
					width: 50,
					targets: [7]
				}],
				dom: '<"datatable-header"fl><"datatable-scroll-wrap"t><"datatable-footer"ip>',
				language: {
					search: '<span>Filtro:</span> _INPUT_',
					searchPlaceholder: 'filtra qualquer coluna...',
					lengthMenu: '<span>Mostrar:</span> _MENU_',
					paginate: {
						'first': 'Primeira',
						'last': 'Última',
						'next': $('html').attr('dir') == 'rtl' ? '&larr;' : '&rarr;',
						'previous': $('html').attr('dir') == 'rtl' ? '&rarr;' : '&larr;'
					}
				}
			});

			// Select2 for length menu styling
			var _componentSelect2 = function () {
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
		function atualizaFluxoOperacional(Permission, linkAditivo, FlOpeId, FlOpeCategoria, FlOpeStatus, Tipo, Motivo) {
			
			document.getElementById('inputPermission').value = Permission;
			document.getElementById('inputFluxoOperacionalId').value = FlOpeId;
			document.getElementById('inputFluxoOperacionalCategoria').value = FlOpeCategoria;
			document.getElementById('inputFluxoOperacionalStatus').value = FlOpeStatus;

			if (Tipo == 'edita') {
				document.formFluxoOperacional.action = "fluxoEdita.php";
			} else if (Tipo == 'exclui') {
				if(Permission){
					confirmaExclusao(document.formFluxoOperacional, "Tem certeza que deseja excluir esse fluxo?",
					"fluxoExclui.php");
				} else{
					alerta('Permissão Negada!','');
					return false;
				}
			} else if (Tipo == 'mudaStatus') {
				document.formFluxoOperacional.action = "fluxoMudaSituacao.php";
			} else if (Tipo == 'produto') {
				document.formFluxoOperacional.action = "fluxoProduto.php";
			} else if (Tipo == 'servico') {
				document.formFluxoOperacional.action = "fluxoServico.php";
			} else if (Tipo == 'realizado') {
				document.formFluxoOperacional.action = "fluxoRealizado.php";
			} else if (Tipo == 'aditivo') {
				if (linkAditivo != '') {

					alerta('Atenção', 'Este fluxo operacional ainda está pendente. Não é possível fazer aditivos',
						'error');
					return false;
				}
				document.formFluxoOperacional.action = "fluxoAditivo.php";
			} else if (Tipo == 'imprimir') {
				console.log('teste')
				document.formFluxoOperacional.action = "fluxoImprime.php";
				document.formFluxoOperacional.setAttribute("target", "_blank");
			} else if (Tipo == 'contrato') {
				console.log('teste')
				document.formFluxoOperacional.action = "fluxoContratoImprime.php";
				document.formFluxoOperacional.setAttribute("target", "_blank");
			} else if (Tipo == 'motivo') {
				bootbox.alert({
					title: '<strong>Motivo da Não Liberação</strong>',
					message: Motivo
				});
				return false;
			} else if (Tipo == 'enviarAprovacao') {
				confirmaExclusao(document.formFluxoOperacional, "Essa ação enviará o Fluxo Operacional para aprovação da Controladoria. Tem certeza que deseja enviar?", "fluxoEnviar.php");
				return false;	
			}

			document.formFluxoOperacional.submit();
		}
	</script>

</head>

<body class="navbar-top sidebar-xs">

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
								<h3 class="card-title">Relação dos Fluxos Operacionais</h3>
								<div class="header-elements">
								</div>
							</div>

							<div class="card-body">
								<div class="row">
									<div class="col-lg-9">
										<p class="font-size-lg">A relação abaixo faz referência aos fluxos operacionais da
										unidade <b><?php echo $_SESSION['UnidadeNome']; ?></b></p>
									</div>		
									<div class="col-lg-3">
										<div class="text-right"><a href="fluxoNovo.php" class="btn btn-principal"
										role="button">Novo Fluxo Operacional</a></div>
									</div>
								</div>
							</div>

							<table class="table" id="tblFluxo">
								<thead>
									<tr class="bg-slate">
										<th width="10%">Início</th>
										<th width="10%">Fim</th>
										<th width="12%">Nº Fluxo</th>
										<th width="17%">Fornecedor</th>
										<th width="17%">Categoria</th>
										<th width="14%">SubCategoria</th>
										<th width="10%">Situação</th>
										<th width="10%" class="text-center">Ações</th>
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
											<td>'.mostraData($item['FlOpeDataInicio']).'</td>
											<td>'.mostraData($item['FimContrato']).'</td>
											<td>'.$item['FlOpeNumContrato'].'</td>
											<td>'.$item['ForneRazaoSocial'].'</td>
											<td>'.$item['CategNome'].'</td>
											<td>'. $item['SubCategorias'].'</td>
											<td><span class="'.$situacaoClasse.'">'.$situacao.'</span>
											');
											$disabled = $item['SituaChave'] == 'PENDENTE' ? "disabled" : '';
										
										print('<td class="text-center">
												<div class="list-icons">
													<div class="list-icons list-icons-extended">
														<a href="#" onclick="atualizaFluxoOperacional('.$atualizar.',\''.$disabled.'\','.$item['FlOpeId'].', \''.$item['FlOpeCategoria'].'\', \''.$item['SituaChave'].'\', \'edita\', \'\');" class="list-icons-item"><i class="icon-pencil7" data-popup="tooltip" data-placement="bottom" title="Editar"></i></a>
														<a href="#" onclick="atualizaFluxoOperacional('.$excluir.',\''.$disabled.'\','.$item['FlOpeId'].', \''.$item['FlOpeCategoria'].'\', \''.$item['SituaChave'].'\', \'exclui\', \'\');" class="list-icons-item"><i class="icon-bin" data-popup="tooltip" data-placement="bottom" title="Exluir"></i></a>
														
														<div class="dropdown">													
															<a href="#" class="list-icons-item" data-toggle="dropdown">
																<i class="icon-menu9"></i>
															</a>
															
															<div class="dropdown-menu dropdown-menu-right">
																<a href="#" onclick="atualizaFluxoOperacional(1,\''.$disabled.'\','.$item['FlOpeId'].', \''.$item['FlOpeCategoria'].'\', \''.$item['SituaChave'].'\', \'produto\', \'\');" class="dropdown-item"><i class="icon-stackoverflow" title="Listar Produtos"></i> Listar Produtos</a>
																<a href="#" onclick="atualizaFluxoOperacional(1,\''.$disabled.'\','.$item['FlOpeId'].', \''.$item['FlOpeCategoria'].'\', \''.$item['SituaChave'].'\', \'servico\', \'\');" class="dropdown-item"><i class="icon-stackoverflow" title="Listar Serviços"></i> Listar Serviços</a>');
																
																if ($item['FluxoFechado'] && $item['SituaChave'] != 'LIBERADO'){												
																	print('<a href="#" onclick="atualizaFluxoOperacional(1,\''.$disabled.'\','.$item['FlOpeId'].', \''.$item['FlOpeCategoria'].'\', \''.$item['SituaChave'].'\', \'enviarAprovacao\', \'\')" class="dropdown-item" title="Enviar para Aprovação"><i class="icon-printer2"></i> Enviar para Aprovação</a>');
																}																					

																print('
																<a href="#" onclick="atualizaFluxoOperacional(1,\''.$disabled.'\','.$item['FlOpeId'].', \''.$item['FlOpeCategoria'].'\', \''.$item['SituaChave'].'\', \'aditivo\', \'\');" class="dropdown-item"><i class="icon-add-to-list" title="Gerenciar Aditivos"></i> Aditivos</a>
																<div class="dropdown-divider"></div>

																<a href="#" onclick="atualizaFluxoOperacional(1,\''.$disabled.'\','.$item['FlOpeId'].', \''.$item['FlOpeCategoria'].'\', \''.$item['SituaChave'].'\', \'imprimir\', \'\')" class="dropdown-item" title="Imprimir Fluxo<"><i class="icon-printer2"></i> Imprimir Fluxo</a>
																<a href="#" onclick="atualizaFluxoOperacional(1,\''.$disabled.'\','.$item['FlOpeId'].', \''.$item['FlOpeCategoria'].'\', \''.$item['SituaChave'].'\', \'contrato\', \'\')" class="dropdown-item" title="Imprimir Contrato"><i class="icon-printer2"></i> Imprimir Contrato</a>');
																
																if ($item['SituaChave'] != 'PENDENTE'){
																	print('<a href="#" onclick="atualizaFluxoOperacional(1,\''.$disabled.'\','.$item['FlOpeId'].', \''.$item['FlOpeCategoria'].'\', \''.$item['SituaChave'].'\', \'realizado\', \'\');" class="dropdown-item"><i class="icon-statistics" data-popup="tooltip" data-placement="bottom" title="Fluxo Realizado"></i> Fluxo Realizado</a>');
																}
																
																if (isset($item['BandeMotivo']) && $item['BandeMotivo'] != null){

																	print('
																	<div class="dropdown-divider"></div>
																	<a href="#" onclick="atualizaFluxoOperacional(1,\''.$disabled.'\','.$item['FlOpeId'].', \''.$item['FlOpeCategoria'].'\', \''.$item['SituaChave'].'\', \'motivo\', \''.$item['BandeMotivo'].'\');" class="dropdown-item"><i class="icon-question4" data-popup="tooltip" data-placement="bottom" title="Motivo da Não liberação"></i> Motivo</a>');
																}

										print('				</div>
														</div>
													</div>
												</div>
											</td>
										</tr>');

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

				<form name="formFluxoOperacional" method="post">
					<input type="hidden" id="inputPermission" name="inputPermission" >
					<input type="hidden" id="inputFluxoOperacionalId" name="inputFluxoOperacionalId">
					<input type="hidden" id="inputFluxoOperacionalCategoria" name="inputFluxoOperacionalCategoria">
					<input type="hidden" id="inputFluxoOperacionalStatus" name="inputFluxoOperacionalStatus">
					<input type="hidden" id="inputOrigem" name="inputOrigem" value="fluxo.php">
				</form>

			</div>
			<!-- /content area -->

			<?php include_once("footer.php"); ?>

		</div>
		<!-- /main content -->

	</div>
	<!-- /page content -->

	<?php include_once("alerta.php"); ?>
	
	<?php //$total = microtime(true) - $inicio;
	 //echo '<span style="background-color:yellow">Tempo de execução do script: ' . round($total, 2).' segundos</span>'; ?>
</body>

</html>