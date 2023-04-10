<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Ordem de Compra';

include('global_assets/php/conexao.php');

$sql = "SELECT DISTINCT OrComId, OrComFluxoOperacional, OrComTipo, OrComNumero, OrComLote, OrComDtEmissao, OrComCategoria, ForneNome, 
		CategNome, OrComNumProcesso, OrComSituacao, SituaNome, SituaChave, SituaCor, FlOpeNumContrato, BandeMotivo, SbCatNome, dbo.fnValorTotalOrdemCompra(OrComUnidade, OrComId) as ValorTotalOrdemCompra,
		(SELECT COUNT(FOXPrProduto) FROM FluxoOperacionalXProduto WHERE FOXPrFluxoOperacional = OrComFluxoOperacional) as produtoCount,
		(SELECT COUNT(FOXSrServico) FROM FluxoOperacionalXServico WHERE FOXSrFluxoOperacional = OrComFluxoOperacional) as servicoCount,
		(SELECT COUNT(OCXPrProduto) FROM OrdemCompraXProduto WHERE OCXPrOrdemCompra = OrComId and OCXPrQuantidade <> '' and OCXPrQuantidade <> 0 and OCXPrValorUnitario <> 0.00) as produtoQuant,
		(SELECT COUNT(OCXSrServico) FROM OrdemCompraXServico WHERE OCXSrOrdemCompra = OrComId and OCXSrQuantidade <> '' and OCXSrQuantidade <> 0 and OCXSrValorUnitario <> 0.00) as servicoQuant
		FROM OrdemCompra
		JOIN Fornecedor on ForneId = OrComFornecedor
		JOIN Categoria on CategId = OrComCategoria
		LEFT JOIN SubCategoria on SbCatId = OrComSubCategoria
		JOIN Situacao on SituaId = OrComSituacao
		JOIN FluxoOperacional on FlOpeId = OrComFluxoOperacional
		LEFT JOIN Bandeja on BandeTabelaId = OrComId and BandeTabela = 'OrdemCompra' and BandeUnidade = ".$_SESSION['UnidadeId'].
		" WHERE OrComUnidade = ".$_SESSION['UnidadeId']." ORDER BY OrComDtEmissao DESC";
$result = $conn->query($sql);
$row = $result->fetchAll(PDO::FETCH_ASSOC);
//$count = count($row);

	$sql = "SELECT ParamEmpresaPublica
			FROM Parametro
			WHERE ParamEmpresa = ". $_SESSION['EmpreId'];
	$result = $conn->query($sql);
	$rowParametros = $result->fetch(PDO::FETCH_ASSOC);

	$empresaPublica = ($rowParametros['ParamEmpresaPublica'] == 1) ? true : false;

	if ($rowParametros['ParamEmpresaPublica']){
		$ordemCompra = "CONTRATO";
		$lote= "Nº Ata/Lote";
		$contrato = "Contrato";

	} else {
		$ordemCompra = " ";
		$lote = "Nº Lote";
		$contrato = "Nº Fluxo";
	}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Ordem de Compra</title>

	<?php include_once("head.php"); ?>
	<style>
		table td{
			padding: 1rem !important;
		}
	</style>
	
	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/tables/datatables/datatables.min.js"></script>
	<script src="global_assets/js/plugins/tables/datatables/extensions/responsive.min.js"></script>
	
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>

	<script src="global_assets/js/demo_pages/datatables_responsive.js"></script>
	<script src="global_assets/js/demo_pages/datatables_sorting.js"></script>

	<!-- Plugin para corrigir a ordenação por data. Caso a URL dê problema algum dia, salvei esses 2 arquivos na pasta global_assets/js/lamparinas -->
	<script type="text/javascript" language="javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.8.4/moment.min.js"></script>
	<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/plug-ins/1.10.10/sorting/datetime-moment.js"></script>	

	<!-- Modal -->
	<script src="global_assets/js/plugins/notifications/bootbox.min.js"></script>		
	
	<script type="text/javascript" >
			
		$(document).ready(function() {
			
			$.fn.dataTable.moment('DD/MM/YYYY'); //Para corrigir a ordenação por data
			
			/* Início: Tabela Personalizada do Setor Publico */
			$('#tblOrdemCompraPublica').DataTable( {
				"order": [[ 0, "desc" ]],
			    autoWidth: false,
				responsive: true,
			    columnDefs: [{ 
					orderable: true,   //Data
					width: "10%",
					targets: [0]
				},
				{ 
					orderable: true,   //Numero Ordem Compra
					width: "5%",
					targets: [1]
				},
				{ 
					orderable: true,   //Contrato
					width: "5%",
					targets: [2]
				},				
				{ 
					orderable: true,   //Lote
					width: "10%",
					targets: [3]
				},
				{ 
					orderable: true,   //Tipo
					width: "9%",
					targets: [4]
				},
				{ 
					orderable: true,   //Fornecedor
					width: "14%",
					targets: [5]
				},
				{ 
					orderable: true,   //Processo
					width: "10%",
					targets: [6]
				},
				{
					orderable: true,   //SubCategoria
					width: "19%",
					targets: [7]
				},
				{
					orderable: true,   //Valor Total
					width: "10%",
					targets: [8]
				},
				{ 
					orderable: true,   //Situação
					width: "3%",
					targets: [9]
				},
				{ 
					orderable: false,  //Ações
					width: "5%",
					targets: [10]
				}],
				dom: '<"datatable-header"fl><"datatable-scroll-wrap"t><"datatable-footer"ip>',
				language: {
					search: '<span>Filtro:</span> _INPUT_',
					searchPlaceholder: 'filtra qualquer coluna...',
					lengthMenu: '<span>Mostrar:</span> _MENU_',
					paginate: { 'first': 'Primeira', 'last': 'Última', 'next': $('html').attr('dir') == 'rtl' ? '&larr;' : '&rarr;', 'previous': $('html').attr('dir') == 'rtl' ? '&rarr;' : '&larr;' }
				}
			});

			/* Início: Tabela Personalizada do Setor Privado */
			$('#tblOrdemCompraPrivada').DataTable( {
				"order": [[ 0, "desc" ]],
			    autoWidth: false,
				responsive: true,
			    columnDefs: [{ 
					orderable: true,   //Data
					width: "10%",
					targets: [0]
				},
				{ 
					orderable: true,   //Numero Ordem Compra
					width: "5%",
					targets: [1]
				},
				{ 
					orderable: true,   //Fluxo
					width: "5%",
					targets: [2]
				},				
				{ 
					orderable: true,   //Lote
					width: "10%",
					targets: [3]
				},
				{ 
					orderable: true,   //Tipo
					width: "9%",
					targets: [4]
				},
				{ 
					orderable: true,   //Fornecedor
					width: "19%",
					targets: [5]
				},
				{
					orderable: true,   //SubCategoria
					width: "24%",
					targets: [6]
				},
				{
					orderable: true,   //Valor Total
					width: "10%",
					targets: [7]
				},
				{ 
					orderable: true,   //Situação
					width: "3%",
					targets: [8]
				},
				{ 
					orderable: false,  //Ações
					width: "5%",
					targets: [9]
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
		function atualizaOrdemCompra(Permission, OrComFlOpeId, OrComId, OrComNumero, OrComCategoria, CategNome, OrComSituacao, OrComSituacaoChave, OrComTipo, Tipo, Motivo){

			document.getElementById('inputPermission').value = Permission;
			document.getElementById('inputOrdemCompraFlOpeId').value = OrComFlOpeId;
			document.getElementById('inputOrdemCompraId').value = OrComId;
			document.getElementById('inputOrdemCompraNumero').value = OrComNumero;
			document.getElementById('inputOrdemCompraCategoria').value = OrComCategoria;
			document.getElementById('inputOrdemCompraNomeCategoria').value = CategNome;
			document.getElementById('inputOrdemCompraStatus').value = OrComSituacao;
			document.getElementById('inputOrdemCompraTipo').value = OrComTipo;
		
			if (Tipo == 'motivo'){
				bootbox.alert({
							title: '<strong>Motivo da Não Liberação</strong>',
							message: Motivo
					});

				return false;
			} else if (Tipo == 'imprimir'){
				if (OrComSituacaoChave == 'AGUARDANDOLIBERACAOCENTRO'){			
					alerta('Atenção','Enquanto o status estiver AGUARDANDO LIBERAÇÃO a impressão não poderá ser realizada!','error');
					return false;
				} else if (OrComSituacaoChave == 'PENDENTE'){			
					alerta('Atenção','Enquanto o status estiver PENDENTE de preenchimento a impressão não poderá ser realizada!','error');
					return false;
				} else if (OrComSituacaoChave == 'NAOLIBERADOCENTRO'){			
					alerta('Atenção','A ordem de compra/contrato não foi liberada, portanto, a impressão não poderá ser realizada!','error');
					return false;
				} else {
					document.formOrdemCompra.action = "ordemcompraImprime.php";
					document.formOrdemCompra.setAttribute("target", "_blank");
				}
			} else {
				if (Tipo == 'edita'){	
					document.formOrdemCompra.action = "ordemcompraEdita.php";		
				} else if (Tipo == 'exclui'){

					if(Permission){
						//Esse ajax verifica se a Ordem de Compra está sendo usada. Se sim, não deixa excluir.
						$.ajax({
							type: "POST",
							url: "ordemcompraEmUso.php",
							data: ('iOrdemCompra='+OrComId),
							success: function(resposta){
								
								if (resposta == 1){								
									alerta('Atenção','Há movimentações usando essa Ordem de Compra, portanto, não pode ser excluída!','error');
									return false;
								} else{
									confirmaExclusao(document.formOrdemCompra, "Tem certeza que deseja excluir essa ordem de compra?", "ordemcompraExclui.php");
								} 

								document.getElementById('inputOrdemCompraId').value = resposta;
							}
						});

						if (document.getElementById('inputOrdemCompraId').value){
							return false;
						}
					
					} else{
							alerta('Permissão Negada!','');
							return false;
						}


				} else if (Tipo == 'mudaStatus'){
					document.formOrdemCompra.action = "ordemcompraMudaSituacao.php";
				} else if (Tipo == 'produto'){
					document.formOrdemCompra.action = "ordemcompraProduto.php";
				} else if (Tipo == 'servico'){
					document.formOrdemCompra.action = "ordemcompraServico.php";
				}else if (Tipo == 'aprovacao'){
					confirmaExclusao(document.formOrdemCompra, "Essa ação enviará toda a Ordem de Compra (com seus produtos e serviços) para aprovação do Centro Administrativo. Tem certeza que deseja enviar?", "ordemcompraEnviar.php");
				} else if (Tipo == 'duplica'){
					document.formOrdemCompra.action = "ordemcompraDuplica.php";
				} else if (Tipo == 'aprovacaoContabilidade') {
					document.formOrdemCompra.action = "ordemcompraAprovacaoContabilidade.php";
				} else if (Tipo == 'empenho') {
					document.formOrdemCompra.action = "ordemCompraEmpenho.php";
				}

				document.formOrdemCompra.setAttribute("target", "_self");
			}
			
			document.formOrdemCompra.submit();
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
								<h5 class="card-title">Relação de Ordens de Compra</h5>
								<div class="header-elements">
									<div class="list-icons">
										<a class="list-icons-item" data-action="collapse"></a>
										<a href="perfil.php" class="list-icons-item" data-action="reload"></a>
										<!--<a class="list-icons-item" data-action="remove"></a>-->
									</div>
								</div>
							</div>					

							<div class="card-body">
								<div class="row">
									<div class="col-lg-9">
										 relação abaixo faz referência às ordens de compra da unidade <b><?php echo $_SESSION['UnidadeNome']; ?></b>
									</div>
									<div class="col-lg-3">
										<div class="text-right"><a href="ordemcompraNovo.php" class="btn btn-principal" role="button">Nova Ordem de Compra</a></div>
									</div>
								</div>
							</div>

							<table class="table" id="<?php if ($ordemCompra == 'CONTRATO') { echo "tblOrdemCompraPublica"; } else { echo "tblOrdemCompraPrivada"; } ?>">
								<thead>
									<tr class="bg-slate">
										<th>Data</th>
										<th>Número</th>
										<th><?php echo $contrato; ?></th>
										<th><?php echo $lote; ?></th>
										<th>Tipo</th>
										<th>Fornecedor</th>
										<?php
											if ($ordemCompra == 'CONTRATO'){	
												print('<th>Processo</th>');
											}
										?>			
										<th>SubCategoria</th>
										<th>Valor Total</th>
										<th>Situação</th>
										<th class="text-center">Ações</th>
									</tr>
								</thead>
								<tbody>
								<?php
									foreach ($row as $item){										
										$situacao = $item['SituaNome'];
										$situacaoClasse = 'badge badge-flat border-'.$item['SituaCor'].' text-'.$item['SituaCor'];
										
										$tipo = $item['OrComTipo'] == 'C' ? 'Carta Contrato' : 'Ordem de Compra';
										
										print('
										<tr>
											<td>'.mostraData($item['OrComDtEmissao']).'</td>
											<td>'.$item['OrComNumero'].'</td>
											<td>'.$item['FlOpeNumContrato'].'</td>
											<td>'.$item['OrComLote'].'</td>
											<td>'.$tipo.'</td>
											<td>'.$item['ForneNome'].'</td>
										');
										
										if ($ordemCompra == 'CONTRATO'){	
											print('<td>'.$item['OrComNumProcesso'].'</td>');
										}
										
										print('
											<td>'.$item['SbCatNome'].'</td>
											<td class="text-right" style="padding-right:40px !important;">'.mostraValor($item['ValorTotalOrdemCompra']).'</td>											
											');
										
										print('<td><span class="'.$situacaoClasse.'">'.$situacao.'</span></td>');

										print('<td class="text-center">
												<div class="list-icons">
													<div class="list-icons list-icons-extended">
														<a href="#" onclick="atualizaOrdemCompra('.$atualizar.','.$item['OrComFluxoOperacional'].','.$item['OrComId'].', \''.$item['OrComNumero'].'\', \''.$item['OrComCategoria'].'\', \''.$item['CategNome'].'\','.$item['OrComSituacao'].',\''.$item['SituaChave'].'\', \''.$item['OrComTipo'].'\', \'edita\', \'\');" class="list-icons-item"><i class="icon-pencil7" title="Editar Ordem de Compra"></i></a>
														<a href="#" onclick="atualizaOrdemCompra('.$excluir.','.$item['OrComFluxoOperacional'].','.$item['OrComId'].', \''.$item['OrComNumero'].'\', \''.$item['OrComCategoria'].'\', \''.$item['CategNome'].'\','.$item['OrComSituacao'].',\''.$item['SituaChave'].'\', \''.$item['OrComTipo'].'\', \'exclui\', \'\');" class="list-icons-item"><i class="icon-bin" title="Excluir Ordem de Compra"></i></a>
														<div class="dropdown">
															<a href="#" class="list-icons-item" data-toggle="dropdown"> 
																<i class="icon-menu9"></i>
															</a>

															<div class="dropdown-menu dropdown-menu-right">'.
																($item['produtoCount']>0?'<a href="#" onclick="atualizaOrdemCompra(1,'.$item['OrComFluxoOperacional'].','.$item['OrComId'].', \''.$item['OrComNumero'].'\', \''.$item['OrComCategoria'].'\', \''.$item['CategNome'].'\','.$item['OrComSituacao'].',\''.$item['SituaChave'].'\', \''.$item['OrComTipo'].'\', \'produto\', \'\');" class="dropdown-item"><i class="icon-stackoverflow" title="Listar Produtos"></i> Listar Produtos</a>':'').
																($item['servicoCount']>0?'<a href="#" onclick="atualizaOrdemCompra(1,'.$item['OrComFluxoOperacional'].','.$item['OrComId'].', \''.$item['OrComNumero'].'\', \''.$item['OrComCategoria'].'\', \''.$item['CategNome'].'\','.$item['OrComSituacao'].',\''.$item['SituaChave'].'\', \''.$item['OrComTipo'].'\', \'servico\', \'\');" class="dropdown-item"><i class="icon-stackoverflow" title="Listar Serviços"></i> Listar Serviços</a>':'').
																((($item['produtoQuant']>0 || $item['servicoQuant']>0) && $_SESSION['PerfiChave'] != 'CONTABILIDADE')?'<a href="#" onclick="atualizaOrdemCompra(1,'.$item['OrComFluxoOperacional'].','.$item['OrComId'].', \''.$item['OrComNumero'].'\', \''.$item['OrComCategoria'].'\', \''.$item['CategNome'].'\','.$item['OrComSituacao'].',\''.$item['SituaChave'].'\', \''.$item['OrComTipo'].'\', \'aprovacao\', \'\');" class="dropdown-item"><i class="icon-list2" title="Enviar para Aprovação"></i> Enviar para Aprovação</a>':'').''
															.'<div class="dropdown-divider"></div>');

																if ($item['SituaChave'] == 'LIBERADOCENTRO' && $empresaPublica){
																	print('<a href="#" onclick="atualizaOrdemCompra(1,'.$item['OrComFluxoOperacional'].','.$item['OrComId'].', \''.$item['OrComNumero'].'\', \''.$item['OrComCategoria'].'\', \''.$item['CategNome'].'\','.$item['OrComSituacao'].',\''.$item['SituaChave'].'\', \''.$item['OrComTipo'].'\', \'aprovacaoContabilidade\', \'\')" class="dropdown-item" title="Contabilidade"><i class="icon-list2"></i> Enviar para Contabilidade</a>');
																
																}
								  
																  if ($item['SituaChave'] == 'AGUARDANDOLIBERACAOCONTABILIDADE' || $item['SituaChave'] == 'LIBERADOCONTABILIDADE'){
																	print('<a href="#" onclick="atualizaOrdemCompra(1,'.$item['OrComFluxoOperacional'].','.$item['OrComId'].', \''.$item['OrComNumero'].'\', \''.$item['OrComCategoria'].'\', \''.$item['CategNome'].'\','.$item['OrComSituacao'].',\''.$item['SituaChave'].'\', \''.$item['OrComTipo'].'\', \'empenho\', \'\')" class="dropdown-item" title="Empenhar"><i class="icon-coin-dollar"></i> Empenhos</a>');
																}
																print('	<a href="#" onclick="atualizaOrdemCompra(1,'.$item['OrComFluxoOperacional'].','.$item['OrComId'].', \''.$item['OrComNumero'].'\', \''.$item['OrComCategoria'].'\', \''.$item['CategNome'].'\','.$item['OrComSituacao'].',\''.$item['SituaChave'].'\', \''.$item['OrComTipo'].'\', \'imprimir\', \'\')" class="dropdown-item" title="Imprimir"><i class="icon-printer2"></i> Imprimir</a>');

										if (isset($item['BandeMotivo'])){

											print('
																<div class="dropdown-divider"></div>
																<a href="#" onclick="atualizaOrdemCompra(1,'.$item['OrComFluxoOperacional'].','.$item['OrComId'].', \''.$item['OrComNumero'].'\', \''.$item['OrComCategoria'].'\', \''.$item['CategNome'].'\','.$item['OrComSituacao'].',\''.$item['SituaChave'].'\', \''.$item['OrComTipo'].'\', \'motivo\', \''.$item['BandeMotivo'].'\')" class="dropdown-item" title="Motivo da Não liberação"><i class="icon-question4"></i> Motivo</a>');
										}
										print('				</div>
														</div>
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
				
				<form name="formOrdemCompra" method="post">
				<input type="hidden" id="inputPermission" name="inputPermission" >
					<input type="hidden" id="inputOrdemCompraFlOpeId" name="inputOrdemCompraFlOpeId" >
					<input type="hidden" id="inputOrdemCompraId" name="inputOrdemCompraId" >
					<input type="hidden" id="inputOrdemCompraNumero" name="inputOrdemCompraNumero" >
					<input type="hidden" id="inputOrdemCompraCategoria" name="inputOrdemCompraCategoria" >
					<input type="hidden" id="inputOrdemCompraNomeCategoria" name="inputOrdemCompraNomeCategoria" >
					<input type="hidden" id="inputOrdemCompraStatus" name="inputOrdemCompraStatus" >
					<input type="hidden" id="inputOrdemCompraTipo" name="inputOrdemCompraTipo" >

					<input type="hidden" id="inputExclui" name="inputExclui" value="0" >
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
