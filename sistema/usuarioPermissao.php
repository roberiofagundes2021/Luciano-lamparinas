<?php 

include_once("sessao.php");

$_SESSION['PaginaAtual'] = 'Permissões';

$unidade = $_SESSION['UnidadeId'];
if(!isset($_POST['inputUsuarioId']) || !isset($_POST['inputUsuarioPerfil'])){
	header("location:javascript://history.go(-1)");
}
$user = $_POST['inputUsuarioId'];
$perfilId = $_POST['inputUsuarioPerfil'];

$sqlUserPerfil = "SELECT PerfiId FROM Perfil WHERE PerfiChave = '$perfilId' and PerfiUnidade = $unidade";
$resultUserPerfil = $conn->query($sqlUserPerfil);
$perfilId = $resultUserPerfil->fetch(PDO::FETCH_ASSOC);

$userPxP = "SELECT UsXUnPermissaoPerfil as UsuarPermissaoPerfil
FROM Usuario
JOIN EmpresaXUsuarioXPerfil ON EXUXPUsuario = UsuarId
JOIN UsuarioXUnidade ON UsXUnEmpresaUsuarioPerfil = EXUXPId
Where UsuarId = '$user' and UsXUnUnidade = $unidade";

$resultUserUxP = $conn->query($userPxP);
$UxPxP = $resultUserUxP->fetch(PDO::FETCH_ASSOC);

// ao recarregar fica sumindo o valor atribuido à $perfilId;

$sqlModuloUxP = "SELECT ModulId, ModulOrdem, ModulNome, ModulStatus, SituaChave, SituaCor
				 FROM Modulo 
				 JOIN Situacao on ModulStatus = SituaId 
				 ORDER BY ModulOrdem ASC";
$resultModuloUxP = $conn->query($sqlModuloUxP);
$moduloUxP = $resultModuloUxP->fetchAll(PDO::FETCH_ASSOC);

//Recupera o parâmetro pra saber se a empresa é pública ou privada
$sqlParametro = "SELECT ParamEmpresaPublica 
				 FROM Parametro
				 WHERE ParamEmpresa = ".$_SESSION['EmpreId'];
$resultParametro = $conn->query($sqlParametro);
$parametro = $resultParametro->fetch(PDO::FETCH_ASSOC);	
$empresa = $parametro['ParamEmpresaPublica'] ? 'Publica' : 'Privada';


$sqlMenuUxP = "SELECT MenuId, MenuNome, MenuUrl, MenuIco, MenuSubMenu, MenuModulo,
				MenuPai, MenuLevel, MenuOrdem, MenuStatus, SituaChave,
				UsXPeVisualizar, UsXPeAtualizar, UsXPeExcluir, UsXPeInserir, UsXPeSuperAdmin, UsXPeUnidade
				FROM Menu
				JOIN Situacao on MenuStatus = SituaId
				JOIN UsuarioXPermissao on MenuId = UsXPeMenu  
				WHERE UsXPeUnidade = ".$unidade." and UsXPeUsuario = ".$user;

if($empresa == 'Publica'){
	$sqlMenuUxP .=	" and MenuSetorPublico = 1 ";
} else {
	$sqlMenuUxP .=	" and MenuSetorPrivado = 1 ";
}
$sqlMenuUxP .= " ORDER BY MenuOrdem asc";

$resultMenuUxP = $conn->query($sqlMenuUxP);
$menuUxP = $resultMenuUxP->fetchAll(PDO::FETCH_ASSOC);

if(!isset($menuUxP[0]['UsXPeVisualizar'])){
	$sqlMenuUxP = "SELECT MenuId, MenuNome, MenuUrl, MenuIco, MenuSubMenu, MenuModulo,
					MenuPai, MenuLevel, MenuOrdem, MenuStatus, SituaChave, 
					PrXPeId, PrXPePerfil, PrXPeMenu, PrXPeVisualizar, PrXPeAtualizar,  PrXPeExcluir, PrXPeInserir,
					PrXPeSuperAdmin, PrXPeUnidade
					FROM Menu
					JOIN Situacao on MenuStatus = SituaId
					JOIN PerfilXPermissao on MenuId = PrXPeMenu 
					WHERE PrXPePerfil = '$perfilId[PerfiId]' and PrXPeUnidade = ".$unidade;

	if($empresa == 'Publica'){
		$sqlMenuUxP .=	" and MenuSetorPublico = 1 ";
	} else {
		$sqlMenuUxP .=	" and MenuSetorPrivado = 1 ";
	}
	$sqlMenuUxP .= " ORDER BY MenuOrdem asc";
	
	$resultMenuUxP = $conn->query($sqlMenuUxP);
	$menuUxP = $resultMenuUxP->fetchAll(PDO::FETCH_ASSOC);
}

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
	
	<script src="global_assets/js/demo_pages/datatables_responsive.js"></script>
	<script src="global_assets/js/demo_pages/datatables_sorting.js"></script>

	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>

	<script src="global_assets/js/demo_pages/form_layouts.js"></script>
	<script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>
	
	<script src="global_assets/js/plugins/forms/styling/switch.min.js"></script>

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
					width: "70%",
					targets: [0]
				},
				{ 
					orderable: true,   //visualizar
					width: "10%",
					targets: [1]
				},
				{ 
					orderable: false,   //inserir
					width: "10%",
					targets: [4]
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
		function needSaveCheck(){
			document.getElementById("btnSave").style.display="block"
		}
		function needSave(){
			document.getElementById("permissaoCheck").checked=false
			document.getElementById("btnSave").style.display="block"
		}

		function save(){
			document.getElementById('form_id').action = "usuarioPermissaoPersist.php";
			document.getElementById("form_id").submit();
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
		height:5px;
		border-bottom:solid 1px #dddddd;
		margin-top:5px;
		margin-bottom:5px;
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
						<form id="form_id" method="POST">
							<input name="usuarioId" value="<?php echo $user; ?>" type="hidden"/>
							<input name="UnidadeId" value="<?php echo $unidade; ?> " type="hidden"/>
							
							<!-- Basic responsive configuration -->
							<div class="card">							
								<div class="card-header">
									<div class="header-elements-inline">
										<h3 class="card-title">Relação de Permissões de usuários (<?php echo $_POST['inputUsuarioNome']; ?>)</h3>
										<div class="header-elements">
											<div><a href="usuario.php" role="button"><< Relação de Usuários</a></div>
										</div>
									</div>
									<div class="row mx-1 mt-3" style="height:20px;">
										<label>
											<p class="mr-2 pt-2" style="font-size:15px;">Utilizar as permissoes do Perfil</p>
										</label>
										
										<div class="p-1">
											<?php echo '
											<label class="form-check-label d-flex align-items-center">
												<input id="permissaoCheck" onchange="needSaveCheck()" type="checkbox" name="permissao_usuario" data-on-text="Sim" data-off-text="Não" class="form-input-switch"'
												.($UxPxP['UsuarPermissaoPerfil']==0? ' />':' checked/>').
											'</label>';
											?>
										</div>
									</div>
									<br>									
								</div>
							</div>

							<?php

								$minimizar = '';

								foreach($moduloUxP as $mod){

									if($mod['SituaChave'] == strtoupper("ativo")){

										$minimizar = $mod['ModulNome'] != 'Controle de Estoque' ? 'card-collapsed' : '';

										print('<div class="card '.$minimizar.'">
												<div class="card-header header-elements-inline">
													<h3 class="card-title">'.$mod['ModulNome'].'</h3>
													<div class="header-elements">
														<div class="list-icons">
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
											orderable: false,   //atualizar
											width: "10%",
											targets: [2]
										},
										{ 
											orderable: false,   //excluir
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

										foreach($menuUxP as $men){
											$superAdmin = false;
											$permission = isset($men["PrXPeSuperAdmin"])?$men["PrXPeSuperAdmin"]:$men["UsXPeSuperAdmin"];
											if ($permission == 0 || $_SESSION['PerfiChave'] == 'SUPER'){
												$superAdmin = true;
											}
											echo "<input name='$men[MenuId]-MenuId' value='$men[MenuId]' type='hidden'/>";
											echo "<input name='$men[MenuId]-SuperAdmin' value='$permission' type='hidden' />";
											if ($men["MenuModulo"] == $mod["ModulId"] && $men['MenuSubMenu'] == 0 && $men['MenuPai'] == 0 && $men['SituaChave'] == strtoupper("ativo") && $superAdmin){
												echo '<tr>
													<td><h5>'.$men['MenuNome'].'</h5></td>
													<td class="text-center">
														';
															if(isset($men['UsXPeVisualizar'])){
																echo '<input name="'.$men['MenuId'].'-view'.'" onclick="needSave()" value="view" type="checkbox"'.
																($men['UsXPeVisualizar'] == 1?'checked/>':'/>');
															}else{
																echo '<input name="'.$men['MenuId'].'-view'.'" onclick="needSave()" value="view" type="checkbox"'.
																($men['PrXPeVisualizar'] == 1?'checked/>':'/>');
															}
														echo '
													</td>
													<td class="text-center">';
														if(isset($men['UsXPeInserir'])){
															echo '<input name="'.$men['MenuId'].'-insert'.'" onclick="needSave()" value="insert" type="checkbox"'.
															($men['UsXPeInserir'] == 1?'checked/>':'/>');
														}else{
															echo '<input name="'.$men['MenuId'].'-insert'.'" onclick="needSave()" value="insert" type="checkbox"'.
															($men['PrXPeInserir'] == 1?'checked/>':'/>');
														}
														echo '
													</td>
													<td class="text-center">';
														if(isset($men['UsXPeAtualizar'])){
															echo '<input name="'.$men['MenuId'].'-edit'.'" onclick="needSave()" value="edit" type="checkbox"'.
															($men['UsXPeAtualizar'] == 1?'checked/>':'/>');
														}else{
															echo '<input name="'.$men['MenuId'].'-edit'.'" onclick="needSave()" value="edit" type="checkbox"'.
															($men['PrXPeAtualizar'] == 1?'checked/>':'/>');
														}
														echo'
													</td>
													<td class="text-center">';
														if(isset($men['UsXPeExcluir'])){
															echo '<input name="'.$men['MenuId'].'-delet'.'" onclick="needSave()" value="delet" type="checkbox"'.
															($men['UsXPeExcluir'] == 1?'checked/>':'/>');
														}else{
															echo '<input name="'.$men['MenuId'].'-delet'.'" onclick="needSave()" value="delet" type="checkbox"'.
															($men['PrXPeExcluir'] == 1?'checked/>':'/>');
														}
														echo '
													</td>
												</tr>';
											}
											if($men['MenuSubMenu'] == 1  && $men["MenuModulo"] == $mod["ModulId"]){
												foreach($menuUxP as $men_f){
													if ($men_f["MenuPai"] == $men["MenuId"] && $men_f['SituaChave'] == strtoupper("ativo")){
														echo '<tr>
															<td><h5>('.$men['MenuNome'].') - '.$men_f['MenuNome'].'</h5></td>
															<td class="text-center">';
																	if(isset($men_f['UsXPeVisualizar'])){
																		echo '<input name="'.$men_f['MenuId'].'-view'.'" onclick="needSave()" value="view" type="checkbox"'.
																		($men_f['UsXPeVisualizar'] == 1?'checked/>':'/>');
																	}else{
																		echo '<input name="'.$men_f['MenuId'].'-view'.'" onclick="needSave()" value="view" type="checkbox"'.
																		($men_f['PrXPeVisualizar'] == 1?'checked/>':'/>');
																	}
																echo '
															</td>
															<td class="text-center">';
																if(isset($men_f['UsXPeInserir'])){
																	echo '<input name="'.$men_f['MenuId'].'-insert'.'" onclick="needSave()" value="insert" type="checkbox"'.
																	($men_f['UsXPeInserir'] == 1?'checked/>':'/>');
																}else{
																	echo '<input name="'.$men_f['MenuId'].'-insert'.'" onclick="needSave()" value="insert" type="checkbox"'.
																	($men_f['PrXPeInserir'] == 1?'checked/>':'/>');
																}
																echo '
															</td>
															<td class="text-center">';
																if(isset($men_f['UsXPeAtualizar'])){
																	echo '<input name="'.$men_f['MenuId'].'-edit'.'" onclick="needSave()" value="edit" type="checkbox"'.
																	($men_f['UsXPeAtualizar'] == 1?'checked/>':'/>');
																}else{
																	echo '<input name="'.$men_f['MenuId'].'-edit'.'" onclick="needSave()" value="edit" type="checkbox"'.
																	($men_f['PrXPeAtualizar'] == 1?'checked/>':'/>');
																}
																echo'
															</td>
															<td class="text-center">';
																if(isset($men_f['UsXPeExcluir'])){
																	echo '<input name="'.$men_f['MenuId'].'-delet'.'" onclick="needSave()" value="delet" type="checkbox"'.
																	($men_f['UsXPeExcluir'] == 1?'checked/>':'/>');
																}else{
																	echo '<input name="'.$men_f['MenuId'].'-delet'.'" onclick="needSave()" value="delet" type="checkbox"'.
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
