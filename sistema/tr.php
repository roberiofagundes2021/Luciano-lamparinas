<?php

include_once("sessao.php");

//$inicio1 = microtime(true);

$_SESSION['PaginaAtual'] = 'Termo de Referência';

include('global_assets/php/conexao.php');

$unidade = $_SESSION['UnidadeId'];
$perfil = $_SESSION['PerfiChave'];
$userId = $_SESSION['UsuarId'];
$url = basename($_SERVER['PHP_SELF']);

$sqlPerfil = "SELECT PerfiId 
			  FROM Perfil
			  WHERE PerfiChave = '". $perfil . "' and PerfiUnidade = " . $_SESSION['UnidadeId'];
$resultPerfilId = $conn->query($sqlPerfil);
$perfilId = $resultPerfilId->fetchAll(PDO::FETCH_ASSOC);
$perfilId = $perfilId[0]['PerfiId'];

$sqlUsuarioEquipe = "SELECT TRXEqTermoReferencia, TRXEqUsuario, TRXEqPresidente, TRXEqUnidade
					 FROM TRXEquipe
					 WHERE TRXEqUsuario = $userId and TRXEqUnidade = $unidade";
$resultUsuarioEquipe = $conn->query($sqlUsuarioEquipe);
$UsuarioEquipe = $resultUsuarioEquipe->fetchAll(PDO::FETCH_ASSOC);

$sqlUser = "SELECT UsXUnPermissaoPerfil as UsuarPermissaoPerfil
FROM Usuario
JOIN EmpresaXUsuarioXPerfil ON EXUXPUsuario = UsuarId
JOIN UsuarioXUnidade ON UsXUnEmpresaUsuarioPerfil = EXUXPId
Where UsuarId = '$userId' and UsXUnUnidade = $unidade";
$resultUserId = $conn->query($sqlUser);
$usuaXPerm = $resultUserId->fetch(PDO::FETCH_ASSOC);

if($usuaXPerm['UsuarPermissaoPerfil'] == 0){
	$sqlMenu = "SELECT MenuId, MenuNome, MenuUrl, MenuIco, MenuSubMenu, MenuModulo, MenuSetorPublico, MenuPosicao,
							MenuPai, MenuLevel, MenuOrdem, MenuStatus, SituaChave, MenuSetorPrivado,
							UsXPeVisualizar, UsXPeAtualizar, UsXPeExcluir, UsXPeUnidade
							FROM Menu
							JOIN Situacao on MenuStatus = SituaId
							JOIN UsuarioXPermissao on UsXPeUsuario = '$userId' and UsXPeUnidade = '$unidade' and UsXPeMenu = MenuId
							WHERE UPPER(MenuPosicao) = 'APOIO' and MenuUrl = '$url'";
} else {
	$sqlMenu = "SELECT MenuId, MenuNome, MenuUrl, MenuIco, MenuSubMenu, MenuModulo, MenuSetorPublico, MenuPosicao,
							MenuPai, MenuLevel, MenuOrdem, MenuStatus, SituaChave, PrXPeId, PrXPePerfil, MenuSetorPrivado,
							PrXPeMenu, PrXPeVisualizar, PrXPeAtualizar,  PrXPeExcluir, PrXPeUnidade
							FROM Menu
							JOIN Situacao on MenuStatus = SituaId
							JOIN PerfilXPermissao on MenuId = PrXPeMenu and PrXPePerfil = '$perfilId' and PrXPeUnidade  = '$unidade'
							WHERE UPPER(MenuPosicao) = 'APOIO' and MenuUrl = '$url'";
}
$resultMenu = $conn->query($sqlMenu);
$menuPermissao = $resultMenu->fetch(PDO::FETCH_ASSOC);
$permissao = isset($menuPermissao['UsXPeVisualizar'])?$menuPermissao['UsXPeVisualizar']:$menuPermissao['PrXPeVisualizar'];

if($permissao == 1){
	$sql = "SELECT DISTINCT TrRefId, TrRefNumero, TrRefData,	TrRefCategoria,	TrRefTipo, CategNome, 
					TrRefStatus, TrRefLiberaParcial, SituaId, SituaCor, SituaChave, SituaNome, 
					dbo.fnSubCategoriasTR(TrRefEmpresa, TrRefId) as SubCategorias, BandeMotivo, TRXEqUsuario
					FROM TermoReferencia
					JOIN Categoria ON CategId = TrRefCategoria
					JOIN Situacao  ON SituaId = TrRefStatus
					LEFT JOIN Bandeja ON BandeTabelaId = TrRefId AND BandeTabela = 'TermoReferencia' AND BandeUnidade = $_SESSION[UnidadeId]
					LEFT JOIN TRXEquipe ON TRXEqTermoReferencia = TrRefId AND TRXEqPresidente = 1
					WHERE TrRefEmpresa = $_SESSION[EmpreId] ORDER BY TrRefData DESC";
	$result = $conn->query($sql);
	$row = $result->fetchAll(PDO::FETCH_ASSOC);
}else{
	if(COUNT($UsuarioEquipe)>0){
		$sql = "SELECT DISTINCT TrRefId, TrRefNumero, TrRefData,	TrRefCategoria,	TrRefTipo, CategNome, 
						TrRefStatus, TrRefLiberaParcial, SituaId, SituaCor, SituaChave, SituaNome, 
						dbo.fnSubCategoriasTR(TrRefUnidade, TrRefId) as SubCategorias, BandeMotivo, TRXEqUsuario
						FROM TermoReferencia
						JOIN Categoria ON CategId = TrRefCategoria
						JOIN Situacao  ON SituaId = TrRefStatus
						LEFT JOIN Bandeja ON BandeTabelaId = TrRefId AND BandeTabela = 'TermoReferencia' AND BandeUnidade = $_SESSION[UnidadeId]
						LEFT JOIN TRXEquipe ON TRXEqTermoReferencia = TrRefId AND TRXEqPresidente = 1
						WHERE TrRefUnidade = $_SESSION[UnidadeId] AND (";
		for($x=0; $x<COUNT($UsuarioEquipe); $x++){
			$sql .= "TrRefId = ".$UsuarioEquipe[$x]['TRXEqTermoReferencia'];
			$sql .= $x<(COUNT($UsuarioEquipe)-1)?" OR ":"";
		}
		$sql .= ") ORDER BY TrRefData DESC";
		
		$result = $conn->query($sql);
		$row = $result->fetchAll(PDO::FETCH_ASSOC);
	}
}

//$count = count($row);

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Termo de Referência</title>

	<?php include_once("head.php"); ?>

	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/tables/datatables/datatables.min.js"></script>
	<script src="global_assets/js/plugins/tables/datatables/extensions/responsive.min.js"></script>

	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>

	<script src="global_assets/js/demo_pages/datatables_responsive.js"></script>
	<script src="global_assets/js/demo_pages/datatables_sorting.js"></script>
	
	<!-- Não permite que o usuário retorne para o EDITAR -->
	<script src="global_assets/js/lamparinas/stop-back.js"></script>

	<!-- Plugin para corrigir a ordenação por data. Caso a URL dê problema algum dia, salvei esses 2 arquivos na pasta global_assets/js/lamparinas -->
	<script type="text/javascript" language="javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.8.4/moment.min.js"></script>
	<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/plug-ins/1.10.10/sorting/datetime-moment.js"></script>

	<script src="global_assets/js/plugins/notifications/bootbox.min.js"></script>		

	<script type="text/javascript">
	
		$(document).ready(function() {

			$.fn.dataTable.moment('DD/MM/YYYY'); //Para corrigir a ordenação por data			

			/* Início: Tabela Personalizada */
			$('#tblTR').DataTable({
				"order": [
					[0, "desc"], [1, "desc"]
				],
				autoWidth: false,
				responsive: true,
				columnDefs: [{
						orderable: true, //Data
						width: "10%",
						targets: [0]
					},
					{
						orderable: true, //Nº TR
						width: "15%",
						targets: [1]
					},
					{
						orderable: true, //Categoria
						width: "30%",
						targets: [2]
					},
					{
						orderable: true, //SubCategoria
						width: "30%",
						targets: [3]
					},
					{
						orderable: true, //Situação
						width: "5%",
						targets: [4]
					},
					{
						orderable: false, //Ações
						width: "10%",
						targets: [5]
					}
				],
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
		function atualizaTR(Permission, TRId, TRNumero, TRCategoria, CategNome, TRStatus, Tipo, liberaParcial, Motivo) {
			document.getElementById('inputPermission').value = Permission;
			document.getElementById('inputTRId').value = TRId;
			document.getElementById('inputTRNumero').value = TRNumero;
			document.getElementById('inputTRCategoria').value = TRCategoria;
			document.getElementById('inputTRNomeCategoria').value = CategNome;
			document.getElementById('inputTRStatus').value = TRStatus;

			if (Tipo == 'motivo' && Motivo !== '' && Motivo !== null)  {				
				bootbox.alert({
					title: '<strong>Motivo da Não Liberação</strong>',
						message: Motivo
				});	
				return false;

			} else if (Tipo == 'imprimirTr') {
				document.formTR.action = "trImprime.php";
				document.formTR.setAttribute("target", "_blank");
				document.formTR.submit();

			} else if (Tipo == 'imprimirComissao') {
				document.formTR.action = "trImprimeComissao.php";
				document.formTR.setAttribute("target", "_blank");
				document.formTR.submit();

			} else {
				if (Tipo == 'edita') {
					document.formTR.action = "trEdita.php";
					document.formTR.submit();

				} else if (Tipo == 'exclui') {
                        if(Permission){ 
                            if (TRStatus === "FASEINTERNAFINALIZADA") {
                                alerta('Esse Termo de Referência já está finalizado e não pode ser excluído!','');
                            } else {	
                                confirmaExclusao(document.formTR, "Tem certeza que deseja excluir essa TR?", "trExclui.php");
                                document.formTR.submit();
                            }
                        }	else{
							alerta('Permissão Negada!','');
							return false;
						}

				} else if (Tipo == 'auditoria') {
					document.formTR.action = "trAuditoria.php";
					document.formTR.submit();

				} else if (Tipo == 'listarProdutos') {
					document.formTR.action = "trProduto.php";
					document.formTR.submit();

				} else if (Tipo == 'listarServicos') {
					document.formTR.action = "trServico.php";
					document.formTR.submit();

				} else if (Tipo == 'aprovacaoAdministrativo') {
					confirmaExclusao(document.formTR, "Essa ação enviará todo o Termo de Referência (com seus produtos e/ou serviços) para aprovação do Centro Administrativo. Tem certeza que deseja enviar?", "trAprovacaoAdministrativo.php");

				} else if (Tipo == 'orcamento') {
					
					//Esse ajax está sendo usado para verificar no banco se há algum produto sem informar a quantidade. Caso tenha não deixar ir para o orçamento.
					$.ajax({
						type: "POST",
						url: "trValidaQuantidade.php",
						data: {iTr: TRId},
						success: function(resposta){

							if (resposta == 1){
								alerta('Atenção','Enquanto todas as quantidades não forem informadas não é possível gerar um orçamento!','error');
								return false;
							} else{
								document.formTR.action = "trOrcamento.php";
								document.formTR.submit();
							}
						}
					})
					
				} else if (Tipo == 'comissaoProcessoLicitatorio') {
					document.formTR.action = "trComissao.php";
					document.formTR.submit();

				} else if (Tipo == 'dotacaoOrcamentaria') {
					document.formTR.action = "trDotacao.php";
					document.formTR.submit();

				} else if (Tipo == 'aprovacaoContabilidade') {
					document.formTR.action = "trAprovacaoContabilidade.php";
					document.formTR.submit();
					
				} else if (Tipo == 'aprovacaoComissao') {
					document.formTR.action = "trAprovacaoComissao.php";
					document.formTR.submit();
				} else if (Tipo == 'gerarContrato'){

					//Esse ajax está sendo usado para verificar se já foi gerado contrato para todas as SubCategorias
					$.ajax({

						type: "POST",
						url: "trContratoValida.php",
						data: ('IdTR='+TRId),
						success: function(resposta){
							
							if(resposta == 1){
								alerta('Atenção','Mais nenhum contrato pode ser gerado para o TR ' + TRNumero + '. Todas as SubCategorias já possuem contrato!','error');
								return false;
							}
		
							document.formTR.action = "contratoNovo.php";
							document.formTR.submit();
						}
					});

				} else if (Tipo == 'finalizarTR') {
						
					bootbox.confirm("Tem certeza que deseja finalizar o TR?", function(result){ 
						
						if (result) {
							document.formTR.action = "trMudaSituacao.php";
							document.formTR.submit();
						}
					});
						
				}
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
								<h5 class="card-title">Relação de Termos de Referência</h5>
							</div>

							<div class="card-body">
								<div class="row">
									<div class="col-lg-9">
										A relação abaixo faz referência aos orçamentos da unidade <b><?php echo $_SESSION['UnidadeNome']; ?></b>
									</div>
										<?php
											// verifica se o perfil possui permissão de inserir caso possua ira aparecer esse camo
											if($inserir){ 
												print('	
												<div class="col-lg-3">
													<div class="text-right"><a href="trNovo.php" class="btn btn-principal" role="button">Novo TR</a></div>
												</div>
											');
											}
										?>
								</div>
							</div>

							<table class="table" id="tblTR">
								<thead>
									<tr class="bg-slate">
										<th>Data</th>
										<th>Nº do TR</th>
										<th>Categoria</th>
										<th>SubCategoria</th>
										<th>Situação</th>
										<th class="text-center">Ações</th>
									</tr>
								</thead>
								<tbody>
									<?php
										if(ISSET($row)){
											foreach ($row as $item) {
												
												$situacao = $item['SituaNome'];
												$situacaoClasse = 'badge badge-flat border-'.$item['SituaCor'].' text-'.$item['SituaCor'];
												$situacaoChave ='\''.$item['SituaChave'].'\'';

												//$telefone = isset($item['ForneTelefone']) ? $item['ForneTelefone'] : $item['ForneCelular'];

												print('
														<tr>
															<td>' . mostraData($item['TrRefData']) . '</td>
															<td>' . $item['TrRefNumero'] . '</td>
															<td>' . $item['CategNome'] . '</td>
															<td>' . $item['SubCategorias'] . '</td>
												');

												// print('<td><a href="#" onclick="atualizaTR(' . $item['TrRefId'] . ', \'' . $item['TrRefNumero'] . '\', \'' . $item['TrRefCategoria'] . '\', \'' . $item['CategNome'] . '\',' . $item['TrRefStatus'] . ', \'mudaStatus\');"><span class="badge ' . $situacaoClasse . '">' . $situacao . '</span></a></td>');
												print('<td><span class="badge ' . $situacaoClasse . '">' . $situacao . '</span></td>');

												print('<td class="text-center">
														<div class="list-icons">
															<div class="list-icons list-icons-extended">
															
																<a href="#" onclick="atualizaTR('.$atualizar.',' . $item['TrRefId'] . ', \'' . $item['TrRefNumero'] . '\', \'' . $item['TrRefCategoria'] . '\', \'' . $item['CategNome'] . '\',' . $item['TrRefStatus'] . ', \'edita\');" class="list-icons-item"><i class="icon-pencil7" title="Editar TR"></i></a>
															      
																<a href="#" onclick="atualizaTR('.$excluir.',' . $item['TrRefId'] . ', \'' . $item['TrRefNumero'] . '\', \'' . $item['TrRefCategoria'] . '\', \'' . $item['CategNome'] . '\', \'' . $item['SituaChave'] . '\', \'exclui\', \'' . $item['TrRefLiberaParcial'] . '\');" class="list-icons-item"><i class="icon-bin" title="Excluir TR"></i></a>

																<a href="#" onclick="atualizaTR(1,' . $item['TrRefId'] . ', \'' . $item['TrRefNumero'] . '\', \'' . $item['TrRefCategoria'] . '\', \'' . $item['CategNome'] . '\',' . $item['TrRefStatus'] . ', \'auditoria\');" class="list-icons-item"><i class="icon-eye4" title="Auditoria"></i></a>

																<div class="dropdown">													
																	<a href="#" class="list-icons-item" data-toggle="dropdown">
																		<i class="icon-menu9"></i>
																	</a>

																	<div class="dropdown-menu dropdown-menu-right">');
																	
																	if ($item['TrRefTipo'] == 'P' || $item['TrRefTipo'] == 'PS') {
																		print('<a href="#" onclick="atualizaTR(1,' . $item['TrRefId'] . ', \'' . $item['TrRefNumero'] . '\', \'' . $item['TrRefCategoria'] . '\', \'' . $item['CategNome'] . '\',' . $item['TrRefStatus'] . ', \'listarProdutos\');" class="dropdown-item"><i class="icon-stackoverflow" title="Listar Produtos"></i> Listar Produtos</a>');
																	}
																	
																	if ($item['TrRefTipo'] == 'S' || $item['TrRefTipo'] == 'PS') {
																		print('<a href="#" onclick="atualizaTR(1,' . $item['TrRefId'] . ', \'' . $item['TrRefNumero'] . '\', \'' . $item['TrRefCategoria'] . '\', \'' . $item['CategNome'] . '\',' . $item['TrRefStatus'] . ', \'listarServicos\');" class="dropdown-item"><i class="icon-stackoverflow" title="Listar Serviços"></i> Listar Serviços</a>');
																	}

																	if ($item['SituaChave'] != 'FASEINTERNAFINALIZADA'){
																		if($_SESSION['PerfiChave']==strtoupper('ADMINISTRADOR') || $_SESSION['PerfiChave']==strtoupper('CENTROADMINISTRATIVO') || $_SESSION['PerfiChave']==strtoupper('ADMINISTRATIVO')){
																			print('<a href="#" onclick="atualizaTR(1,' . $item['TrRefId'] . ', \'' . $item['TrRefNumero'] . '\', \'' . $item['TrRefCategoria'] . '\', \'' . $item['CategNome'] . '\',' . $item['TrRefStatus'] . ', \'aprovacaoAdministrativo\');" class="dropdown-item"><i class="icon-list2" title="Aprovação - Centro Administrativo"></i> Enviar para aprovação</a>');
																		}
																	}

																	if (isset($item['BandeMotivo']) && $item['SituaChave'] == 'NAOLIBERADOCENTRO'){
																		print('
																			<div class="dropdown-divider"></div>

																			<a href="#" onclick="atualizaTR(1,'.$item['TrRefId'].', \''.$item['TrRefNumero'].'\', \''.$item['TrRefCategoria'].'\', \''.$item['CategNome'].'\','.$item['TrRefStatus'].', \'motivo\', \'0\', \''.$item['BandeMotivo'].'\')" class="dropdown-item" title="Motivo da Não liberação"><i class="icon-question4"></i> Motivo</a>
																		');
																	}

																	if(isset($item['TrRefLiberaParcial']) && $item['TrRefLiberaParcial'] == true) {
																		
																		print('<a href="#" onclick="atualizaTR(1,' . $item['TrRefId'] . ', \'' . $item['TrRefNumero'] . '\', \'' . $item['TrRefCategoria'] . '\', \'' . $item['CategNome'] . '\',' . $item['TrRefStatus'] . ', \'orcamento\');" class="dropdown-item"><i class="icon-coin-dollar" title="Orçamentos"></i> Orçamentos</a>');
																		
																		if ($item['SituaChave'] != 'FASEINTERNAFINALIZADA'){
																			if($_SESSION['PerfiChave']==strtoupper('ADMINISTRADOR') || $_SESSION['PerfiChave']==strtoupper('CENTROADMINISTRATIVO') || $_SESSION['PerfiChave']==strtoupper('ADMINISTRATIVO')){
																				print('<a href="#" onclick="atualizaTR(1,' . $item['TrRefId'] . ', \'' . $item['TrRefNumero'] . '\', \'' . $item['TrRefCategoria'] . '\', \'' . $item['CategNome'] . '\',' . $item['TrRefStatus'] . ', \'aprovacaoContabilidade\');" class="dropdown-item"><i class="icon-list2" title="Aprovação - Contabilidade"></i> Enviar para contabilidade</a>');
																			}
																		}

																		print('<a href="#" onclick="atualizaTR(1,' . $item['TrRefId'] . ', \'' . $item['TrRefNumero'] . '\', \'' . $item['TrRefCategoria'] . '\', \'' . $item['CategNome'] . '\',' . $item['TrRefStatus'] . ', \'dotacaoOrcamentaria\');" class="dropdown-item"><i class="icon-coin-dollar" title="Orçamentos"></i> Dotação Orçamentária</a>																
																				<a href="#" onclick="atualizaTR(1,' . $item['TrRefId'] . ', \'' . $item['TrRefNumero'] . '\', \'' . $item['TrRefCategoria'] . '\', \'' . $item['CategNome'] . '\',' . $item['TrRefStatus'] . ', \'comissaoProcessoLicitatorio\');" class="dropdown-item"><i class="icon-stack2" title=" Comissão do Processo Licitatório "></i>  Comissão do Processo Licitatório </a>');

																		if ($item['SituaChave'] != 'FASEINTERNAFINALIZADA'){
																			if($_SESSION['PerfiChave']==strtoupper('ADMINISTRADOR') || $_SESSION['PerfiChave']==strtoupper('CENTROADMINISTRATIVO') || $_SESSION['PerfiChave']==strtoupper('ADMINISTRATIVO')){
																				print('<a href="#" onclick="atualizaTR(1,' . $item['TrRefId'] . ', \'' . $item['TrRefNumero'] . '\', \'' . $item['TrRefCategoria'] . '\', \'' . $item['CategNome'] . '\',' . $item['TrRefStatus'] . ', \'aprovacaoComissao\');" class="dropdown-item"><i class="icon-list2" title="Aprovação"></i> Enviar para comissão</a>');
																			}
																		}

																		//Se o TR estiver AGUARDANDOFINALIZACAO e o Presidente da Comissão for o usuário Logado mostra a opção para ele "Finalizar TR"
																		if ($item['SituaChave'] == 'AGUARDANDOFINALIZACAO' && $item['TRXEqUsuario'] == $_SESSION['UsuarId']){
																			print('<a href="#" onclick="atualizaTR(1,' . $item['TrRefId'] . ', \'' . $item['TrRefNumero'] . '\', \'' . $item['TrRefCategoria'] . '\', \'' . $item['CategNome'] . '\',' . $item['TrRefStatus'] . ', \'finalizarTR\');" class="dropdown-item"><i class="icon-checkmark3 text-success"></i> Finalizar TR</a>');
																		}

																		print('
																		
																		<div class="dropdown-divider"></div>

																		<a href="#" onclick="atualizaTR(1,' . $item['TrRefId'] . ', \'' . $item['TrRefNumero'] . '\', \'' . $item['TrRefCategoria'] . '\', \'' . $item['CategNome'] . '\',' . $item['TrRefStatus'] . ', \'imprimirTr\');" class="dropdown-item" title="Imprimir TR"><i class="icon-printer2"></i> Imprimir TR</a>
																		');

																		if ($item['SituaChave'] == 'FASEINTERNAFINALIZADA'){
																			print('
																				<div class="dropdown-divider"></div>
																				
																				<a href="#" onclick="atualizaTR(1,'.$item['TrRefId'].', \''.$item['TrRefNumero'].'\', \''.$item['TrRefCategoria'].'\', \''.$item['CategNome'].'\','.$item['TrRefStatus'].', \'gerarContrato\')" class="dropdown-item" title="Gerar Contrato"><i class="icon-file-text"></i> Gerar Contrato</a>
																			');
																		}

																	}

												print('
																	</div>
																</div>
															</div>
														</div>
														</td>
													</tr>
												');
											}
										}
									?>

								</tbody>
							</table>
						</div>
						<!-- /basic responsive configuration -->

					</div>
				</div>

				<!-- /info blocks -->

				<form name="formTR" method="post">
					<input type="hidden" id="inputPermission" name="inputPermission" >
					<input type="hidden" id="inputTRId" name="inputTRId">
					<input type="hidden" id="inputTRNumero" name="inputTRNumero">
					<input type="hidden" id="inputTRCategoria" name="inputTRCategoria">
					<input type="hidden" id="inputTRNomeCategoria" name="inputTRNomeCategoria">
					<input type="hidden" id="inputTRStatus" name="inputTRStatus">
					<input type="hidden" id="inputTermoReferenciaStatus" name="inputTermoReferenciaStatus" value="FASEINTERNAFINALIZADA"> <!-- esse aqui é por causa do FinalizarTR -->
				</form>
				
			</div>
			<!-- /content area -->

			<?php include_once("footer.php"); ?>

		</div>
		<!-- /main content -->

	</div>
	<!-- /page content -->

	<?php include_once("alerta.php"); ?>
	
	<?php /* $total1 = microtime(true) - $inicio1;
		 echo '<span style="background-color:yellow; padding: 10px; font-size:24px;">Tempo de execução do script: ' . round($total1, 2).' segundos</span>'; */ ?>
</body>

</html>
