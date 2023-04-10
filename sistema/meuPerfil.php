<?php

include_once("sessao.php");

include('global_assets/php/conexao.php');

$_SESSION['PaginaAtual'] = 'Perfil';

if (isset($_SESSION['EmpresaId'])) {
	$EmpresaId = $_SESSION['EmpresaId'];
} else {
	$EmpresaId = $_SESSION['EmpreId'];
}   

if (isset($_SESSION['UsuarId'])) {

	$iUsuario = $_SESSION['UsuarId'];

	$sql = "SELECT UsuarId, UsuarCpf, UsuarNome, UsuarLogin, UsuarSenha, UsuarEmail, PerfiNome,
			UsuarTelefone, UsuarCelular, EXUXPId, EXUXPPerfil, PerfiChave, UsXUnResumoFinanceiro, UsXUnOperadorCaixa
			FROM Usuario
			JOIN EmpresaXUsuarioXPerfil on EXUXPUsuario = UsuarId
			JOIN Perfil on PerfiId = EXUXPPerfil
			LEFT JOIN UsuarioXUnidade on UsXUnEmpresaUsuarioPerfil = EXUXPId 
			WHERE UsuarId = $iUsuario and EXUXPEmpresa = $EmpresaId ";
	$result = $conn->query($sql);
	$row = $result->fetch(PDO::FETCH_ASSOC);

	$_SESSION['msg'] = array();
}

if (isset($_POST['inputNome'])) {

	try {

		$conn->beginTransaction();

		$visibilidadeResumoFinanceiro = isset($_POST['inputVisualisaResumoFinanceiro']) ? true : false;

		$sql = "SELECT EXUXPId, UsuarSenha
				FROM Usuario
				JOIN EmpresaXUsuarioXPerfil on EXUXPUsuario = UsuarId
				JOIN Perfil on PerfiId = EXUXPPerfil
				WHERE UsuarId = $iUsuario and EXUXPEmpresa = $EmpresaId ";
		$result = $conn->query($sql);
		$row = $result->fetch(PDO::FETCH_ASSOC);

		$senha = '';
		$row['UsuarSenha'] == $_POST['inputSenha'] ? $senha = $_POST['inputSenha'] : $senha = md5($_POST['inputSenha']);

		$sql = "UPDATE Usuario SET UsuarNome = :sNome, usuarLogin = :sLogin, UsuarSenha = :sSenha, UsuarEmail = :sEmail, 
								   UsuarTelefone = :sTelefone, UsuarCelular = :sCelular
				WHERE UsuarId = :iUsuario";
		$result = $conn->prepare($sql);

		$result->execute(array(
			':sNome' => $_POST['inputNome'],
			':sLogin' => $_POST['inputLogin'],
			':sSenha' => $senha,
			':sEmail' => $_POST['inputEmail'],
			':sTelefone' => $_POST['inputTelefone'] == '(__) ____-____' ? null : $_POST['inputTelefone'],
			':sCelular' => $_POST['inputCelular'] == '(__) _____-____' ? null : $_POST['inputCelular'],
			':iUsuario' => $_SESSION['UsuarId']
		));	

		$sql = "UPDATE UsuarioXUnidade SET  UsXUnResumoFinanceiro = :bResumoFinanceiro, UsXUnUsuarioAtualizador = :iUsuarioAtualizador
				WHERE UsXUnEmpresaUsuarioPerfil = :iEmpresaUsarioPerfil and UsXUnUnidade = :iUnidade";
		$result = $conn->prepare($sql);


		$result->execute(array(	
			':bResumoFinanceiro' => $visibilidadeResumoFinanceiro,
			':iUsuarioAtualizador' => $_SESSION['UsuarId'],
			':iEmpresaUsarioPerfil' => $row['EXUXPId'],
			':iUnidade' => $_SESSION['UnidadeId']
		));

		$conn->commit();

		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Perfil alterado!!!";
		$_SESSION['msg']['tipo'] = "success";
	} catch (PDOException $e) {

		$conn->rollback();

		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao alterar perfil!!!";
		$_SESSION['msg']['tipo'] = "error";

		echo 'Error: ' . $e->getMessage();
		die;
	}

	irpara("meuPerfil.php");
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Perfil</title>

	<?php include_once("head.php"); ?>

	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>

	<script src="global_assets/js/demo_pages/form_layouts.js"></script>
	<script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>

	<script src="global_assets/js/plugins/forms/inputs/inputmask.js"></script>

	<!-- Validação -->
	<script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
	<script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
	<script src="global_assets/js/demo_pages/form_validation.js"></script>	

	<!-- Adicionando Javascript -->
	<script type="text/javascript">
		
		$(document).ready(function() {

			$('#enviar').on('click', function(e) {

				e.preventDefault();

				var inputNome = $('#inputNome').val();
				var inputLogin = $('#inputLogin').val();
				var inputEmail = $('#inputEmail').val();
				var inputSenha = $('#inputSenha').val();
				var inputConfirmaSenha = $('#inputConfirmaSenha').val();
								

				if (inputNome != '' && inputLogin != '' && inputEmail != ''){
					if (inputSenha != inputConfirmaSenha) {
						alerta('Atenção', 'A confirmação de senha não confere!', 'error');
						$('#inputConfirmaSenha').focus();
						return false;
					}
				}

				$("#formPerfil").submit();
			})

			
			

			$('#cancelar').on('click', function(e) {

				$(window.document.location).attr('href', "meuPerfil.php");
			});

		});
	</script>

</head>

<?php

if (isset($_SESSION['EmpresaId'])) {
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

	if (isset($_SESSION['EmpresaId'])) {
		include_once("menuLeftSecundario.php");
	}

	?>

	<!-- Main content -->
	<div class="content-wrapper">

		<?php include_once("cabecalho.php"); ?>

		<!-- Content area -->
		<div class="content">

			<!-- Info blocks -->
			<div class="card">

				<form name="formPerfil" id="formPerfil" method="post" class="form-validate-jquery" action="meuPerfil.php">
					<div class="card-header header-elements-inline">
						<h5 class="text-uppercase font-weight-bold">Editar Perfil "<?php echo $row['UsuarNome']; ?>"</h5>
					</div>

					<input type="hidden" id="inputUsuarioId" name="inputUsuarioId" value="<?php echo $row['UsuarId']; ?>">

					<div class="card-body">
						<div class="row">
							<div class="col-lg-12">
								<div class="row">
									<div class="col-lg-4">
										<div class="form-group">
											<label for="inputNome">Nome<span class="text-danger"> *</span></label>
											<input type="text" id="inputNome" name="inputNome" class="form-control" placeholder="Nome" value="<?php echo $row['UsuarNome']; ?>" required readOnly>
										</div>
									</div>
									<div class="col-lg-3">
										<div class="form-group">
											<label for="cmbPerfil">Perfil<span class="text-danger"> *</span></label>
											<input type="text" id="cmbPerfil" name="cmbPerfil" class="form-control" value="<?php echo $row['PerfiNome']; ?>" readOnly>
										</div>
									</div>
									<div class="col-lg-3" style="margin-top: auto; margin-bottom: auto;">
										<div class="custom-control custom-checkbox">
											<input type="checkbox" class="custom-control-input" value="1" id="inputVisualisaResumoFinanceiro" name="inputVisualisaResumoFinanceiro" <?php echo isset($row['UsXUnResumoFinanceiro']) && $row['UsXUnResumoFinanceiro'] ? 'checked' : ''; ?>>
											<label class="custom-control-label" for="inputVisualisaResumoFinanceiro">Resumo Financeiro Visível</label>
										</div>
									</div>

									<div class="col-lg-2" style="margin-top: auto; margin-bottom: auto;">
										<div class="custom-control custom-checkbox">
											<input type="checkbox" class="custom-control-input" value="1" id="inputOperadorCaixa" name="inputOperadorCaixa" <?php echo isset($row['UsXUnOperadorCaixa']) && $row['UsXUnOperadorCaixa'] ? 'checked' : ''; ?> disabled>
											<label class="custom-control-label" for="inputOperadorCaixa">Operado de Caixa</label>
										</div>
									</div>
								</div>
							</div>
						</div>

						<h5 class="mb-0 font-weight-semibold">Login</h5>
						<br>
						<div class="row">
							<div class="col-lg-12">
								<div class="row">
									<div class="col-lg-4">
										<div class="form-group">
											<label for="inputLogin">Login<span class="text-danger"> *</span></label>
											<input type="text" id="inputLogin" name="inputLogin" class="form-control" placeholder="Login" value="<?php echo $row['UsuarLogin']; ?>" required>
										</div>
									</div>

									<div class="col-lg-4">
										<div class="form-group">
											<label for="inputSenha">Senha<span class="text-danger"> *</span></label>
											<input type="password" id="inputSenha" name="inputSenha" class="form-control" placeholder="Senha" value="<?php echo $row['UsuarSenha']; ?>" required>
										</div>
									</div>

									<div class="col-lg-4">
										<div class="form-group">
											<label for="inputConfirmaSenha">Confirma Senha<span class="text-danger"> *</span></label>
											<input type="password" id="inputConfirmaSenha" name="inputConfirmaSenha" class="form-control" placeholder="Confirma Senha" value="<?php echo $row['UsuarSenha']; ?>" required>
										</div>
									</div>
								</div>
							</div>
						</div>

						<h5 class="mb-0 font-weight-semibold">Contato</h5>
						<br>
						<div class="row">
							<div class="col-lg-12">
								<div class="row">
									<div class="col-lg-4">
										<div class="form-group">
											<label for="inputEmail">E-mail<span class="text-danger"> *</span></label>
											<input type="email" id="inputEmail" name="inputEmail" class="form-control" placeholder="E-mail" value="<?php echo $row['UsuarEmail']; ?>" required>
										</div>
									</div>
									<div class="col-lg-4">
										<div class="form-group">
											<label for="inputTelefone">Telefone</label>
											<input type="text" id="inputTelefone" name="inputTelefone" class="form-control" placeholder="Telefone" data-mask="(99) 9999-9999" value="<?php echo $row['UsuarTelefone']; ?>">
										</div>
									</div>
									<div class="col-lg-4">
										<div class="form-group">
											<label for="inputCelular">Celular</label>
											<input type="text" id="inputCelular" name="inputCelular" class="form-control" placeholder="Celular" data-mask="(99) 99999-9999" value="<?php echo $row['UsuarCelular']; ?>">
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="row" style="margin-top: 20px;">
							<div class="col-lg-12">
								<div class="form-group">
									<?php	
                                        print('<button class="btn btn-lg btn-principal" id="enviar">Alterar</button>');
									?>
									<a href="index.php" class="btn btn-basic" role="button" id="cancelar">Cancelar</a>
								</div>
							</div>
						</div>
				</form>

			</div>
			<!-- /card-body -->

		</div>
		<!-- /info blocks -->

	</div>
	<!-- /content area -->

	<?php include_once("footer.php"); ?>

</div>
<!-- /main content -->

</div>
<!-- /page content -->

</body>

</html>