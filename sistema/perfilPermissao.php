<?php 

include_once("sessao.php");

$_SESSION['PaginaAtual'] = 'Permissões';

$unidade = $_SESSION['UnidadeId'];
if(!isset($_POST['inputPerfilId'])){
	header("location:javascript://history.go(-1)");
}
$PerfilId = $_POST['inputPerfilId'];

// ao recarregar fica sumindo o valor atribuido à $perfilId;

$sqlModuloPxP = "SELECT ModulId, ModulOrdem, ModulNome, ModulStatus, SituaChave, SituaCor
				 FROM Modulo
				 JOIN Situacao on ModulStatus = SituaId 
				 ORDER BY ModulOrdem ASC";
$resultModuloPxP = $conn->query($sqlModuloPxP);
$moduloPxP = $resultModuloPxP->fetchAll(PDO::FETCH_ASSOC);

//Recupera o parâmetro para saber se a empresa é pública ou privada
$sqlParametro = "SELECT ParamEmpresaPublica 
				 FROM Parametro
				 WHERE ParamEmpresa = ".$_SESSION['EmpreId'];
$resultParametro = $conn->query($sqlParametro);
$parametro = $resultParametro->fetch(PDO::FETCH_ASSOC);	
$empresa = $parametro['ParamEmpresaPublica'] ? 'Publica' : 'Privada';

$sqlMenuPxP = "SELECT MenuId, MenuNome, MenuUrl, MenuIco, MenuSubMenu, MenuModulo,
				MenuPai, MenuLevel, MenuOrdem, MenuStatus, SituaChave, 
				PrXPeId, PrXPePerfil, PrXPeMenu, PrXPeVisualizar, PrXPeAtualizar,  PrXPeExcluir, PrXPeInserir, PrXPeSuperAdmin,
				PrXPeUnidade
				FROM Menu
				JOIN Situacao on MenuStatus = SituaId
				JOIN PerfilXPermissao on MenuId = PrXPeMenu
				WHERE PrXPePerfil = $PerfilId and PrXPeUnidade = $unidade
				ORDER BY MenuOrdem ASC";

// if($empresa == 'Publica'){
// 	$sqlMenuPxP .=	" WHERE MenuSetorPublico = 1 ";
// } else {
// 	$sqlMenuPxP .=	" WHERE MenuSetorPrivado = 1 ";
// }
// $sqlMenuPxP .=	" ORDER BY MenuOrdem asc";

$resultMenuPxP = $conn->query($sqlMenuPxP);
$menuPxP = $resultMenuPxP->fetchAll(PDO::FETCH_ASSOC);
// var_dump($sqlMenuPxP);
// exit;

$sqlSituacao = "SELECT SituaId, SituaNome, SituaChave, SituaStatus, SituaCor FROM situacao";
$resultSituacao = $conn->query($sqlSituacao);
$situacao = $resultSituacao->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Permissões</title>

	<?php include_once("head.php"); ?>
	
	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/tables/datatables/datatables.min.js"></script>
	<script src="global_assets/js/plugins/tables/datatables/extensions/responsive.min.js"></script>
	
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>

	<script src="global_assets/js/demo_pages/datatables_responsive.js"></script>
	<script src="global_assets/js/demo_pages/datatables_sorting.js"></script>
		
	<!-- Não permite que o usuário retorne para o EDITAR -->
	<script src="global_assets/js/lamparinas/stop-back.js"></script>
	
	<!-- Validação -->
	<script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
	<script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
	<script src="global_assets/js/demo_pages/form_validation.js"></script>
	<script type="text/javascript">
		$(document).ready(function (){	
			$('#tblPermissao').DataTable( {
				"order": [[ 0, "asc" ]],
			    autoWidth: false,
				responsive: true,
			    columnDefs: [
				{
					orderable: true,   //permissao
					width: "50%",
					targets: [0]
				},
				{ 
					orderable: true,   //visualizar
					width: "10%",
					targets: [1]
				},
				{ 
					orderable: false,   //atualizar
					width: "10%",
					targets: [2]
				},
				{ 
					orderable: false,   //excluir
					width: "10%",
					targets: [3]
				},
				{ 
					orderable: false,   //inserir
					width: "10%",
					targets: [4]
				},
				{ 
					orderable: false,   //resetar
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
		function needSave(){
			document.getElementById("btnSave").style.display="block"
		}
		function save(){
			document.getElementById('form_id').action = "perfilPermissaoPersist.php";
			document.getElementById("form_id").submit();
		}

		function resetPermissao(unidade, PerfilId, modulo) {
			$('#unidade').val(unidade)
			$('#PerfilId').val(PerfilId)
			$('#modulo').val(modulo)

			confirmaExclusao(document.getElementById('form_reset'),
			modulo === 'all'?"Tem certeza que deseja resetar todos os módulos?":"Tem certeza que deseja resetar esse módulo?", "perfilPermissaoReset.php");

			// $('#form_reset').attr('action', 'perfilPermissaoReset.php')
			// $('#form_reset').submit()
		}

	</script>
	
</head>

<style>
.btn-group-fab {
	display: none;
	position: fixed;
	width: 100px;
	height: auto;
	right: 20px; bottom: 20px;
	z-index: 1;
}
.separate{
	height:10px;
	border-bottom:solid 1px #dddddd;
	margin-top:10px;
	margin-bottom:10px;
}
.full-width{
	width: 100%;
}
td{
	border-top:solid 1px #dddddd;
	border-bottom:solid 1px #dddddd;
}
</style>

<body class="navbar-top">

	<?php include_once("topo.php"); ?>	

	<!-- Page content -->
	<div class="page-content">
		
		<?php include_once("menu-left.php"); ?>
		
		<div class="content-wrapper">

			<?php include_once("cabecalho.php"); ?>	

			<button id="btnSave" type="submit" onClick="save()" class="btn-group-fab btn btn-lg btn-principal">salvar</button>
			
			<div class="content">

				<!-- Info blocks -->		
				<div class="row">
					<div class="col-lg-12">
						<div class="card">							
							<div class="card-header">
								<div class="header-elements-inline">
									<h3 class="card-title">Relação de Permissões (<?php echo $_POST['inputPerfilNome']; ?>)</h3>
									<div class="header-elements">
										<div>
											<a href="perfil.php" role="button"><< Relação de Perfis</a> 
											| 
											<?php echo '<a href="#" role="button" title="Resetar Todos" onClick="resetPermissao('.$unidade.','.$PerfilId.', `all`)">Resetar todos</a>'; ?>
										</div>
									</div>
								</div>								
							</div>
						</div>
						<form id="form_reset" method="POST">
							<input type="hidden" value="" name="unidade" id="unidade" />
							<input type="hidden" value="" name="PerfilId" id="PerfilId" />
							<input type="hidden" value="" name="modulo" id="modulo" />
						</form>
						<form id="form_id" method="POST">
							<?php
								echo '<input name="unidade" type="hidden" value="'.$unidade.'" />';
								echo '<input name="PerfilId" type="hidden" value="'.$PerfilId.'" />';
								$minimizar = '';

								foreach($moduloPxP as $mod){

									if($mod['SituaChave'] == strtoupper("ativo")){

										$minimizar = $mod['ModulNome'] != 'Controle de Estoque' ? 'card-collapsed' : '';
										$modulo = $mod['ModulId'];

										print('<div class="card '.$minimizar.'">
												<div class="card-header header-elements-inline">
													<h3 class="card-title">'.$mod['ModulNome'].'</h3>
													<div class="header-elements">
														<div class="list-icons">
															<i style="cursor: pointer;" onClick="resetPermissao('.$unidade.','.$PerfilId.','.$modulo.')" class="icon-reset" title="Resetar"></i>
															<a class="list-icons-item" data-action="collapse"></a>
														</div>
													</div>
												</div>
												<div class="card-body">
												');
																	
							?>

							<script type="text/javascript">
								
								$(document).ready(function (){	

									$('#tblPermissao<?php echo $mod['ModulId']; ?>').DataTable( {
										"order": [[ 0, "asc" ]],
										autoWidth: false,
										responsive: true,
										bPaginate: false,
										columnDefs: [
										{
											orderable: true,   //permissao
											width: "70%",
											targets: [0]
										},
										{ 
											orderable: false,   //visualizar
											width: "10%",
											targets: [1]
										},
										{ 
											orderable: false,   //inserir
											width: "10%",
											targets: [2]
										},
										{ 
											orderable: false,   //atualizar
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
							</script>

							<table id="tblPermissao<?php echo $mod['ModulId']; ?>" class="table pt-3">
								<thead>
									<tr class="bg-slate">
										<th>Permissão</th>
										<th style="text-align: center">Visualizar</th>
										<th style="text-align: center">Inserir</th>
										<th style="text-align: center">Atualizar</th>
										<th style="text-align: center">Excluir</th>
									</tr>
								</thead>
								<div class="separate"></div>
									<tbody>
										<?php

											foreach($menuPxP as $men){
												$superADmin = false;
												if ($men["PrXPeSuperAdmin"] == 0 || $_SESSION['PerfiChave'] == 'SUPER'){
													$superADmin = true;
												}
												if ($men["MenuModulo"] == $mod["ModulId"] && $men['MenuSubMenu'] == 0 && $men['MenuPai'] == 0 && $men['SituaChave'] == strtoupper("ativo") && $superADmin){
													echo '<input name="'.$men['PrXPeId'].'-PrXPeId" value='.$men['PrXPeId'].' type="hidden">';
													echo '<tr>
														<td><h5>'.$men['MenuNome'].'</h5></td>
														<td class="text-center">
															';
																if(isset($men['UsXPeVisualizar'])){
																	echo '<input name="'.$men['PrXPeId'].'-view'.'" onclick="needSave()" value="view" type="checkbox"'.
																	($men['UsXPeVisualizar'] == 1?'checked/>':'/>');
																}else{
																	echo '<input name="'.$men['PrXPeId'].'-view'.'" onclick="needSave()" value="view" type="checkbox"'.
																	($men['PrXPeVisualizar'] == 1?'checked/>':'/>');
																}
															echo '
														</td>
														<td class="text-center">';
															if(isset($men['UsXPeInserir'])){
																echo '<input name="'.$men['PrXPeId'].'-insert'.'" onclick="needSave()" value="insert" type="checkbox"'.
																($men['UsXPeInserir'] == 1?'checked/>':'/>');
															}else{
																echo '<input name="'.$men['PrXPeId'].'-insert'.'" onclick="needSave()" value="insert" type="checkbox"'.
																($men['PrXPeInserir'] == 1?'checked/>':'/>');
															}
															echo '
														</td>
														<td class="text-center">';
															if(isset($men['UsXPeAtualizar'])){
																echo '<input name="'.$men['PrXPeId'].'-edit'.'" onclick="needSave()" value="edit" type="checkbox"'.
																($men['UsXPeAtualizar'] == 1?'checked/>':'/>');
															}else{
																echo '<input name="'.$men['PrXPeId'].'-edit'.'" onclick="needSave()" value="edit" type="checkbox"'.
																($men['PrXPeAtualizar'] == 1?'checked/>':'/>');
															}
															echo'
														</td>
														<td class="text-center">';
															if(isset($men['UsXPeExcluir'])){
																echo '<input name="'.$men['PrXPeId'].'-delet'.'" onclick="needSave()" value="delet" type="checkbox"'.
																($men['UsXPeExcluir'] == 1?'checked/>':'/>');
															}else{
																echo '<input name="'.$men['PrXPeId'].'-delet'.'" onclick="needSave()" value="delet" type="checkbox"'.
																($men['PrXPeExcluir'] == 1?'checked/>':'/>');
															}
															echo '
														</td>
													</tr>';
												}
												if($men['MenuSubMenu'] == 1  && $men["MenuModulo"] == $mod["ModulId"]){
													foreach($menuPxP as $men_f){
														if ($men_f["MenuPai"] == $men["MenuId"] && $men_f['SituaChave'] == strtoupper("ativo")){
															echo '<input name="MenuId" value='.$men_f['MenuId'].' type="hidden">';
															echo '<input name="'.$men_f['PrXPeId'].'-PrXPeId" value='.$men_f['PrXPeId'].' type="hidden">';
															echo '<tr>
																<td><h5>('.$men['MenuNome'].') - '.$men_f['MenuNome'].'</h5></td>
																<td class="text-center">';
																		if(isset($men_f['UsXPeVisualizar'])){
																			echo '<input name="'.$men_f['PrXPeId'].'-view'.'" onclick="needSave()" value="view" type="checkbox"'.
																			($men_f['UsXPeVisualizar'] == 1?'checked/>':'/>');
																		}else{
																			echo '<input name="'.$men_f['PrXPeId'].'-view'.'" onclick="needSave()" value="view" type="checkbox"'.
																			($men_f['PrXPeVisualizar'] == 1?'checked/>':'/>');
																		}
																	echo '
																</td>
																<td class="text-center">';
																	if(isset($men_f['UsXPeInserir'])){
																		echo '<input name="'.$men_f['PrXPeId'].'-insert'.'" onclick="needSave()" value="insert" type="checkbox"'.
																		($men_f['UsXPeInserir'] == 1?'checked/>':'/>');
																	}else{
																		echo '<input name="'.$men_f['PrXPeId'].'-insert'.'" onclick="needSave()" value="insert" type="checkbox"'.
																		($men_f['PrXPeInserir'] == 1?'checked/>':'/>');
																	}
																	echo '
																</td>
																<td class="text-center">';
																	if(isset($men_f['UsXPeAtualizar'])){
																		echo '<input name="'.$men_f['PrXPeId'].'-edit'.'" onclick="needSave()" value="edit" type="checkbox"'.
																		($men_f['UsXPeAtualizar'] == 1?'checked/>':'/>');
																	}else{
																		echo '<input name="'.$men_f['PrXPeId'].'-edit'.'" onclick="needSave()" value="edit" type="checkbox"'.
																		($men_f['PrXPeAtualizar'] == 1?'checked/>':'/>');
																	}
																	echo'
																</td>
																<td class="text-center">';
																	if(isset($men_f['UsXPeExcluir'])){
																		echo '<input name="'.$men_f['PrXPeId'].'-delet'.'" onclick="needSave()" value="delet" type="checkbox"'.
																		($men_f['UsXPeExcluir'] == 1?'checked/>':'/>');
																	}else{
																		echo '<input name="'.$men_f['PrXPeId'].'-delet'.'" onclick="needSave()" value="delet" type="checkbox"'.
																		($men_f['PrXPeExcluir'] == 1?'checked/>':'/>');
																	}
																	echo '
																</td>
															</tr>';
														}
													}
												}
											}
												//} //*
											//} //*
										?>									
									</tbody>
							</table>
							</div> <!-- card-body -->
							</div> <!-- -->
							<?php  }
								}
							?>
						</form>	
				</div>
				<!-- /info blocks -->

			</div>
			
		</div>
		<!-- /main content -->

		<?php include_once("footer.php"); ?>

	</div>
	<!-- /page content -->

	<?php include_once("alerta.php"); ?>

</body>

</html>
