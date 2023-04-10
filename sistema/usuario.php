<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Usuário';

include('global_assets/php/conexao.php');

if (isset($_POST['inputEmpresaId'])){
	$_SESSION['EmpresaId'] = $_POST['inputEmpresaId'];
	$_SESSION['EmpresaNome'] = $_POST['inputEmpresaNome'];
}

if (isset($_SESSION['EmpresaId'])){
	$EmpresaId = $_SESSION['EmpresaId'];
	$EmpresaNome = $_SESSION['EmpresaNome'];
} else {
	$EmpresaId = $_SESSION['EmpreId'];
	$EmpresaNome = $_SESSION['EmpreNomeFantasia'];
	$_SESSION['UC'] = 'Usuario';
}

$sql = "SELECT UsuarId, UsuarCpf, UsuarNome, UsuarLogin, UsuarCelular, EXUXPId, EXUXPStatus, PerfiNome, PerfiChave, 
               EmpreNomeFantasia, SituaNome, SituaChave, SituaCor, UsXUnOperadorCaixa
		FROM Usuario
		JOIN EmpresaXUsuarioXPerfil on EXUXPUsuario = UsuarId
		JOIN Empresa on EXUXPEmpresa = EmpreId
		JOIN Perfil on PerfiId = EXUXPPerfil
		LEFT JOIN UsuarioXUnidade on UsXUnEmpresaUsuarioPerfil = EXUXPId
		JOIN Situacao on SituaId =  EXUXPStatus
		Where EmpreId = ".$EmpresaId."
		ORDER BY UsuarNome ASC";
$result = $conn->query($sql);
$row = $result->fetchAll(PDO::FETCH_ASSOC);
$usuariosCadastrados = count($row);

$sql = "SELECT LicenLimiteUsuarios
		FROM Licenca
		JOIN Situacao on SituaId = LicenStatus
		WHERE LicenEmpresa = ".$EmpresaId." and SituaChave = 'ATIVO'
		ORDER BY LicenDtFim desc";
$result = $conn->query($sql);
$rowLimite = $result->fetch(PDO::FETCH_ASSOC);

$limiteUsuarios = $rowLimite['LicenLimiteUsuarios'];

$sqlUnidade = "SELECT UnidaId, UnidaNome
				FROM Unidade
				WHERE UnidaId = ". $_SESSION['UnidadeId'];
$resultUnidade = $conn->query($sqlUnidade);
$unidadeUser = $resultUnidade->fetch(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Usuário</title>

	<?php include_once("head.php"); ?>

	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/tables/datatables/datatables.min.js"></script>
	<script src="global_assets/js/plugins/tables/datatables/extensions/responsive.min.js"></script>

	<script src="global_assets/js/demo_pages/datatables_responsive.js"></script>
	<script src="global_assets/js/demo_pages/datatables_sorting.js"></script>
	
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
	
	<script src="global_assets/js/demo_pages/form_layouts.js"></script>
	<script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>		
	<!-- /theme JS files -->
	
	<script type="text/javascript">
		
		$(document).ready(function() {
			
			/* Início: Tabela Personalizada */
			$('#tblUsuario').DataTable( {
				"order": [[ 0, "asc" ]],
			    autoWidth: false,
				responsive: true,
				columnDefs: [
				{ 			
					orderable: true,		//Nome
					width: "20%",
					targets: [0]
				},
				{ 
					orderable: true,		//Login
					width: "20%",
					targets: [1]
				},
				{ 
					orderable: true,		//CPF
					width: "20%",
					targets: [2]
				},
				{ 
					orderable: true,		//Perfil
					width: "15%",
					targets: [3]
				},
				{ 
					orderable: true,		//Operador de Caixa
					width: "15%",
					targets: [4]
				},
				{ 
					orderable: true,		//Situação
					width: "5%",
					targets: [5]
				},
				{ 
					orderable: false,		//Ações
					width: "5%",
					targets: [6] 
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
		
		function atualizaUsuario(Permission, UsuarioId, UsuarioNome, UsuarioStatus, UsuarioPerfil, EmpresaUsuarioPerfil, Tipo){

			document.getElementById('inputPermission').value = Permission;
			document.getElementById('inputUsuarioId').value = UsuarioId;
			document.getElementById('inputUsuarioNome').value = UsuarioNome;
			document.getElementById('inputUsuarioStatus').value = UsuarioStatus;
			document.getElementById('inputUsuarioPerfil').value = UsuarioPerfil;
			document.getElementById('inputEmpresaUsuarioPerfil').value = EmpresaUsuarioPerfil;
			
			if (Tipo == 'novo'){

				var limiteUsuarios = $('#inputLimiteUsuarios').val();
				var usuariosCadastrados = $('#inputUsuariosCadastrados').val();

				if (parseInt(usuariosCadastrados) >= parseInt(limiteUsuarios)){
					alerta('Atenção', 'O limite de usuários para essa empresa foi atingido! Para adicionar mais usuários, favor contactar a empresa Lamparinas', 'error');
					return false;
				} else{
					document.formUsuario.action = "usuarioNovo.php";
				}	

      } else if (Tipo == 'lotacao'){
			document.formUsuario.action = "usuarioLotacao.php";		
			} else if (Tipo == 'edita'){
				document.formUsuario.action = "usuarioEdita.php";
			} else if (Tipo == 'exclui'){
				if(Permission){
					confirmaExclusao(document.formUsuario, "Tem certeza que deseja excluir esse usuário?", "usuarioExclui.php");
				}	else{
					alerta('Permissão Negada!','');
					return false;
				}
			} else if (Tipo == 'mudaStatus'){
				document.formUsuario.action = "usuarioMudaSituacao.php";
			} else if (Tipo == 'permissao'){
				document.formUsuario.action = "usuarioPermissao.php";
			}
			
			document.formUsuario.submit();
		}
			
	</script>	

</head>

	<?php
		
		if (isset($_SESSION['EmpresaId'])){		
			print('<body class="navbar-top sidebar-xs">');
		} else {
			print('<body class="navbar-top">');
		}

		include_once("topo.php");
	?>	

	<!-- Page content -->
	<div class="page-content">
		
		<?php 
		
			include_once("menu-left.php"); 
		
			if (isset($_SESSION['EmpresaId'])){
				include_once("menuLeftSecundario.php");
			}
		?>		

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
								<h3 class="card-title">Relação de Usuários</h3>
								<div class="header-elements">
								</div>
							</div>

							<div class="card-body">
								<div class="row">
									<div class="col-lg-9">
										<?php 
											echo (isset($_POST['inputEmpresaNome'])?'<p class="font-size-lg">Os usuários cadastrados abaixo pertencem a empresa <b>'.$_POST['inputEmpresaNome'].'</b></p>':
											'<p class="font-size-lg">Os usuários cadastrados abaixo pertencem a unidade <b>'.$unidadeUser['UnidaNome'].'</b></p>');
										?>
									</div>
									<div class="col-lg-3">	
										<div class="text-right"><a href="#" onclick="atualizaUsuario(1,0, '', '', '', '', 'novo')" class="btn btn-principal" role="button">Novo usuário</a></div>
									</div>
								</div>
							</div>							

							<table class="table" id="tblUsuario">
								<thead>
									<tr class="bg-slate">
										<th>Nome</th>
										<th>Login</th>
										<th>CPF</th>
										<th>Perfil</th>
										<?php 
											if (!isset($_SESSION['EmpresaId'])){
												print('<th>Operador de Caixa</th>');
										 	} else {
												print('<th>Celular</th>');
											}
										?>
										<th>Situação</th>										
										<th class="text-center">Ações</th>
									</tr>
								</thead>
								<tbody>
								<?php
									foreach ($row as $item){
										
										$situacao = $item['SituaNome'];
										$situacaoClasse = 'badge badge-flat border-'.$item['SituaCor'].' text-'.$item['SituaCor'];
										$operadorCaixa = $item['UsXUnOperadorCaixa'] == 1 ? 'SIM' :  'NÃO';
										
										
										print('
										<tr>
											<td>'.$item['UsuarNome'].'</td>
											<td>'.$item['UsuarLogin'].'</td>
											<td>'.formatarCPF_Cnpj($item['UsuarCpf']).'</td>
											<td>'.$item['PerfiNome'].'</td>');
											if (!isset($_SESSION['EmpresaId'])){ 
												print('<td class="text-center">'. $operadorCaixa .'</td>');
											} else{
												print('<td>'.$item['UsuarCelular'].'</td>');
											}
										if ($_SESSION['UsuarId'] != $item['UsuarId']) {
											print('<td><a href="#" onclick="atualizaUsuario(1,'.$item['UsuarId'].', \''.$item['UsuarNome'].'\', \''.$item['SituaChave'].'\', \''.$item['PerfiChave'].'\', '.$item['EXUXPId'].', \'mudaStatus\')"><span class="badge '.$situacaoClasse.'">'.$situacao.'</span></a></td>');
										} else {
											print('<td><a href="#" data-popup="tooltip" data-trigger="focus" title="Seu usuário não pode ser desativado por você."><span class="badge '.$situacaoClasse.'">'.$situacao.'</span></a></td>');
										}
										
										//<td><span class="badge '.$situacaoClasse.'">'.$situacao.'</span></td>											
											
										print('	<td class="text-center">
												<div class="list-icons">
													<div class="list-icons list-icons-extended">');

										if (isset($_SESSION['EmpresaId'])){
											print('		<a href="#" onclick="atualizaUsuario(1,'.$item['UsuarId'].', \''.addslashes($item['UsuarNome']).'\', '.$item['EXUXPStatus'].', \''.$item['PerfiChave'].'\', '.$item['EXUXPId'].', \'lotacao\')" class="list-icons-item"><i class="icon-users4" data-popup="tooltip" data-placement="bottom" title="Lotação"></i></a>');
										}										
										
										print('	<a href="#" onclick="atualizaUsuario('.$atualizar.','.$item['UsuarId'].', \''.addslashes($item['UsuarNome']).'\', '.$item['EXUXPStatus'].', \''.$item['PerfiChave'].'\', '.$item['EXUXPId'].', \'edita\')" class="list-icons-item"><i class="icon-pencil7" data-popup="tooltip" data-placement="bottom" title="Editar"></i></a>
														<a href="#" onclick="atualizaUsuario('.$excluir.','.$item['UsuarId'].', \''.addslashes($item['UsuarNome']).'\', '.$item['EXUXPStatus'].', \''.$item['PerfiChave'].'\', '.$item['EXUXPId'].', \'exclui\')" class="list-icons-item"><i class="icon-bin" data-popup="tooltip" data-placement="bottom" title="Exluir"></i></a>'.
														(!isset($_SESSION['EmpresaId'])?'<a href="#" onclick="atualizaUsuario(1,'.$item['UsuarId'].', \''.addslashes($item['UsuarNome']).'\', '.$item['EXUXPStatus'].', \''.$item['PerfiChave'].'\', '.$item['EXUXPId'].', \'permissao\')" class="list-icons-item"><i class="icon-lock2" data-popup="tooltip" data-placement="bottom" title="permissao"></i></a>':'')
													.'</div>
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
				
				<form name="formUsuario" method="post" action="usuarioEdita.php">
					<input type="hidden" id="inputPermission" name="inputPermission" >
					<input type="hidden" id="inputUsuarioId" name="inputUsuarioId" >
					<input type="hidden" id="inputUsuarioNome" name="inputUsuarioNome" >
					<input type="hidden" id="inputUsuarioStatus" name="inputUsuarioStatus" >
					<input type="hidden" id="inputUsuarioPerfil" name="inputUsuarioPerfil" >
					<input type="hidden" id="inputEmpresaUsuarioPerfil" name="inputEmpresaUsuarioPerfil" >
					<input type="hidden" id="inputLimiteUsuarios" value="<?php echo $limiteUsuarios; ?>" >
					<input type="hidden" id="inputUsuariosCadastrados" value="<?php echo $usuariosCadastrados; ?>" >
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
