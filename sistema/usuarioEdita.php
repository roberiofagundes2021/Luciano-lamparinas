<?php

include_once("sessao.php");

include('global_assets/php/conexao.php');

$_SESSION['PaginaAtual'] = 'Editar Usuário';

if (isset($_SESSION['EmpresaId'])) {
	$EmpresaId = $_SESSION['EmpresaId'];
} else {
	$EmpresaId = $_SESSION['EmpreId'];
}

if (isset($_POST['inputUsuarioId'])) {

	$iUsuario = $_POST['inputUsuarioId'];

	$sql = "SELECT UsuarId, UsuarCpf, UsuarNome, UsuarLogin, UsuarSenha, UsuarEmail, 
			UsuarTelefone, UsuarCelular, EXUXPId, EXUXPPerfil, PerfiChave, UsXUnResumoFinanceiro, UsXUnOperadorCaixa
			FROM Usuario
			JOIN EmpresaXUsuarioXPerfil on EXUXPUsuario = UsuarId
			JOIN Perfil on PerfiId = EXUXPPerfil
			LEFT JOIN UsuarioXUnidade on UsXUnEmpresaUsuarioPerfil = EXUXPId
			WHERE UsuarId = $iUsuario and EXUXPEmpresa = $EmpresaId ";
	$result = $conn->query($sql);
	$row = $result->fetch(PDO::FETCH_ASSOC);

	if (!isset($_SESSION['EmpresaId'])) {
		$sql = "SELECT UsXUnSetor, UsXUnLocalEstoque 
				FROM UsuarioXUnidade
				WHERE UsXUnEmpresaUsuarioPerfil = ".$row['EXUXPId']." and UsXUnUnidade = " . $_SESSION['UnidadeId'];
		$result = $conn->query($sql);
		$rowSetorLocal = $result->fetch(PDO::FETCH_ASSOC);
	}

	$_SESSION['msg'] = array();
}

if (isset($_POST['inputCpf'])) {

	try {

		$conn->beginTransaction();

		$sql = "SELECT EXUXPId, UsuarSenha
				FROM Usuario
				JOIN EmpresaXUsuarioXPerfil on EXUXPUsuario = UsuarId
				JOIN Perfil on PerfiId = EXUXPPerfil
				WHERE UsuarId = $iUsuario and EXUXPEmpresa = $EmpresaId ";
		$result = $conn->query($sql);
		$row = $result->fetch(PDO::FETCH_ASSOC);

		$senha = '';
		$row['UsuarSenha'] == $_POST['inputSenha'] ? $senha = $_POST['inputSenha'] : $senha = md5($_POST['inputSenha']);

		$visibilidadeResumoFinanceiro = isset($_POST['inputVisualisaResumoFinanceiro']) ? true : false;

		$operadorCaixa = isset($_POST['inputOperadorCaixa']) ? true : false;

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
			':iUsuario' => $_POST['inputUsuarioId']
		));

		$sql = "UPDATE EmpresaXUsuarioXPerfil SET EXUXPPerfil = :iPerfil, EXUXPUsuarioAtualizador = :iUsuarioAtualizador
				WHERE EXUXPUsuario = :iUsuario and EXUXPEmpresa = :iEmpresa";
		$result = $conn->prepare($sql);

		$result->execute(array(
			':iPerfil' => $_POST['cmbPerfil'] == '' ? null : $_POST['cmbPerfil'],
			':iUsuarioAtualizador' => $_SESSION['UsuarId'],
			':iUsuario' => $_POST['inputUsuarioId'],
			':iEmpresa' => $EmpresaId
		));

		if (!isset($_SESSION['EmpresaId'])){
				
			$sql = "SELECT PerfiChave
					FROM Perfil
					WHERE PerfiId = ".$_POST['cmbPerfil'];
			$result = $conn->query($sql);
			$rowPerfilChave = $result->fetch(PDO::FETCH_ASSOC);

			$sql = "SELECT COUNT(UsXUnUnidade) as Unidade
					FROM UsuarioXUnidade
					WHERE UsXUnEmpresaUsuarioPerfil = ".$row['EXUXPId']." and UsXUnUnidade = ".$_SESSION['UnidadeId'];
			$result = $conn->query($sql);
			$rowUsuarioXUnidade = $result->fetch(PDO::FETCH_ASSOC);			

			//Verifica se esse usuário já foi vinculado a unidade do usuário logado que está editando o registro ($rowUsuarioXUnidade['Unidade']==0 não está)
			//Se ainda não tiver sido vinculado nesse momento deve-se vincular, do contrário apenas atualiza
			if ($rowUsuarioXUnidade['Unidade'] == 0){
				
				//Passo3: inserir na tabela UsuarioXUnidade (vinculando o usuário na Unidade, Setor e Local de Estoque)
				$sql = "INSERT INTO UsuarioXUnidade (UsXUnEmpresaUsuarioPerfil, UsXUnUnidade, UsXUnSetor, UsXUnLocalEstoque, UsXUnPermissaoPerfil, UsXUnResumoFinanceiro, UsXUnOperadorCaixa, UsXUnUsuarioAtualizador)
						VALUES (:iEmpresaUsarioPerfil, :iUnidade, :iSetor, :iLocalEstoque, :PermissaoPerfil, :bResumoFinanceiro, :bOperadorCaixa, :iUsuarioAtualizador )";
				$result = $conn->prepare($sql);

				if ($rowPerfilChave['PerfiChave'] == 'ALMOXARIFADO'){
					$localEstoque = $_POST['cmbLocalEstoque'];
				} else {
					$localEstoque = null;
				}

				$result->execute(array(
					':iEmpresaUsarioPerfil' => $row['EXUXPId'],
					':iUnidade' => $_SESSION['UnidadeId'],
					':iSetor' => $_POST['cmbSetor'],
					':iLocalEstoque' => $localEstoque,
					':PermissaoPerfil' => 1,
					':bResumoFinanceiro' => $visibilidadeResumoFinanceiro,
					':bOperadorCaixa' => $operadorCaixa,
					':iUsuarioAtualizador' => $_SESSION['UsuarId'],
					));
			} else {

				//Passo3: inserir na tabela UsuarioXUnidade (vinculando o usuário na Unidade, Setor e Local de Estoque)
				$sql = "UPDATE UsuarioXUnidade SET UsXUnSetor = :iSetor, UsXUnLocalEstoque = :iLocalEstoque, UsXUnResumoFinanceiro = :bResumoFinanceiro, UsXUnOperadorCaixa = :bOperadorCaixa, UsXUnUsuarioAtualizador = :iUsuarioAtualizador
						WHERE UsXUnEmpresaUsuarioPerfil = :iEmpresaUsarioPerfil and UsXUnUnidade = :iUnidade";
				$result = $conn->prepare($sql);

				if ($rowPerfilChave['PerfiChave'] == 'ALMOXARIFADO'){
					$localEstoque = $_POST['cmbLocalEstoque'];
				} else {
					$localEstoque = null;
				}

				$result->execute(array(
					':iSetor' => $_POST['cmbSetor'],
					':iLocalEstoque' => $localEstoque,
					':bResumoFinanceiro' => $visibilidadeResumoFinanceiro,
					':bOperadorCaixa' => $operadorCaixa,
					':iUsuarioAtualizador' => $_SESSION['UsuarId'],
					':iEmpresaUsarioPerfil' => $row['EXUXPId'],
					':iUnidade' => $_SESSION['UnidadeId']
					));
			}
		}		

		$conn->commit();

		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Usuário alterado!!!";
		$_SESSION['msg']['tipo'] = "success";
	} catch (PDOException $e) {

		$conn->rollback();

		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao alterar usuário!!!";
		$_SESSION['msg']['tipo'] = "error";

		echo 'Error: ' . $e->getMessage();
		die;
	}

	irpara("usuario.php");
}

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
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
    <script src="global_assets/js/demo_pages/form_select2.js"></script>

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

			//Garantindo que ninguém mude a empresa na tela de edição
			//$('#cmbEmpresa').prop("disabled", true);

			$('#cmbPerfil').on('change', function(e) {
				let filhos = $('#cmbPerfil').children()
				let valorcmb = $('#cmbPerfil').val()
				filhos.each((i, elem) => {
					let perfil = $(elem).attr('chavePerfil')
					let valOption = $(elem).attr('value')

					if (valOption == valorcmb){

						if (perfil == 'ALMOXARIFADO') {
							
							$('#LocalEstoque').fadeIn('300');
							document.getElementById('cmbLocalEstoque').setAttribute('required', 'required');
						} else {
							document.getElementById('cmbLocalEstoque').removeAttribute('required', 'required');
							$('#LocalEstoque').fadeOut('300');							
						}
					}
				})
			})

			//Tela de usuário acessada pelo Configurador/Usuário
			$('#enviar').on('click', function(e) {

				e.preventDefault();

				var inputNome = $('#inputNome').val();
				var cmbPerfil = $('#cmbPerfil').val();
				var inputLogin = $('#inputLogin').val();
				var inputEmail = $('#inputEmail').val();
				var inputSenha = $('#inputSenha').val();
				var inputConfirmaSenha = $('#inputConfirmaSenha').val();
				var cmbSetor = $('#cmbSetor').val();				

				if (inputNome != '' && inputLogin != '' && inputEmail != '' && cmbPerfil != '' && cmbSetor != ''){
					if (inputSenha != inputConfirmaSenha) {
						alerta('Atenção', 'A confirmação de senha não confere!', 'error');
						$('#inputConfirmaSenha').focus();
						$("#formUsuario").submit();
						return false;
					}
				}

				$("#formUsuario").submit();
			})

			//Tele de usuário acessada pelo Configurador/Empresa/Usuário
			$('#enviarEmpresa').on('click', function(e) {

				e.preventDefault();

				var inputNome = $('#inputNome').val();
				var cmbPerfil = $('#cmbPerfil').val();
				var inputLogin = $('#inputLogin').val();
				var inputEmail = $('#inputEmail').val();
				var inputSenha = $('#inputSenha').val();
				var inputConfirmaSenha = $('#inputConfirmaSenha').val();			

				if (inputNome != '' && inputLogin != '' && inputEmail != '' && cmbPerfil != ''){
					if (inputSenha != inputConfirmaSenha) {
						alerta('Atenção', 'A confirmação de senha não confere!', 'error');
						$('#inputConfirmaSenha').focus();
						$("#formUsuario").submit();
						return false;
					}
				}

				//$('#cmbEmpresa').prop("disabled", false);

				$("#formUsuario").submit();
			})

			$('#cancelar').on('click', function(e) {

				//$('#cmbEmpresa').prop("disabled", false);

				$(window.document.location).attr('href', "usuario.php");
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

				<form name="formUsuario" id="formUsuario" method="post" class="form-validate-jquery" action="usuarioEdita.php">
					<div class="card-header header-elements-inline">
						<h5 class="text-uppercase font-weight-bold">Editar Usuário "<?php echo $row['UsuarNome']; ?>"</h5>
					</div>

					<input type="hidden" id="inputUsuarioId" name="inputUsuarioId" value="<?php echo $row['UsuarId']; ?>">

					<div class="card-body">
						<div class="row">
							<div class="col-lg-12">
								<div class="row">
									<div class="col-lg-2">
										<div class="form-group">
											<label for="inputCpf">CPF<span class="text-danger"> *</span></label>
											<input type="text" id="inputCpf" name="inputCpf" class="form-control" placeholder="CPF" value="<?php echo formatarCPF_Cnpj($row['UsuarCpf']); ?>" required readOnly>
										</div>
									</div>
									<div class="col-lg-4">
										<div class="form-group">
											<label for="inputNome">Nome<span class="text-danger"> *</span></label>
											<input type="text" id="inputNome" name="inputNome" class="form-control" placeholder="Nome" value="<?php echo $row['UsuarNome']; ?>" required>
										</div>
									</div>
									<div class="col-lg-3">
										<div class="form-group">
											<label for="cmbPerfil">Perfil<span class="text-danger"> *</span></label>
											<select id="cmbPerfil" name="cmbPerfil" class="form-control select-search" required>
												<option value="">Informe um perfil</option>
												<?php
												$sql = "SELECT PerfiId, PerfiNome, PerfiChave
														FROM Perfil
														JOIN Situacao on SituaId = PerfiStatus															     
														WHERE SituaChave = 'ATIVO' and PerfiUnidade = ".$_SESSION['UnidadeId']."
														ORDER BY PerfiNome ASC";
												$result = $conn->query($sql);
												$rowPerfil = $result->fetchAll(PDO::FETCH_ASSOC);

												foreach ($rowPerfil as $item) {
													$seleciona = $item['PerfiId'] == $row['EXUXPPerfil'] ? "selected" : "";
													print('<option value="' . $item['PerfiId'] . '" '. $seleciona .' chavePerfil="' . $item['PerfiChave'] . '">' . $item['PerfiNome'] . '</option>');
												}
												?>
											</select>
										</div>
									</div>
									<?php if (!isset($_SESSION['EmpresaId'])){ ?>
									<div class="col-lg-3" style="margin-top: auto; margin-bottom: auto;">
										<div class="custom-control custom-checkbox">
											<input type="checkbox" class="custom-control-input" value="1" id="inputVisualisaResumoFinanceiro" name="inputVisualisaResumoFinanceiro" <?php echo isset($row['UsXUnResumoFinanceiro']) && $row['UsXUnResumoFinanceiro'] ? 'checked' : ''; ?>>
											<label class="custom-control-label" for="inputVisualisaResumoFinanceiro">Resumo Financeiro Visível</label>
										</div>

										<div class="custom-control custom-checkbox">
											<input type="checkbox" class="custom-control-input" value="1" id="inputOperadorCaixa" name="inputOperadorCaixa" <?php echo isset($row['UsXUnOperadorCaixa']) && $row['UsXUnOperadorCaixa'] ? 'checked' : ''; ?>>
											<label class="custom-control-label" for="inputOperadorCaixa">Operador de Caixa</label>
										</div>
									</div>
									<?php } ?>
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

						<?php if (!isset($_SESSION['EmpresaId'])){ ?>
						<h5 class="mb-0 font-weight-semibold">Lotação</h5>
						<br>
						<div class="row">
							<div class="col-lg-12">
								<div class="row">
									<div class="col-lg-4">
										<div class="form-group">
											<label for="cmbSetor">Setor<span class="text-danger"> *</span></label>
											<select name="cmbSetor" id="cmbSetor" class="form-control select-search" required>
												<option value="">Informe um setor</option>
												<?php
												$sql = "SELECT SetorId, SetorNome
														FROM Setor
														JOIN Situacao on SituaId = SetorStatus															     
														WHERE SituaChave = 'ATIVO' and SetorUnidade = ".$_SESSION['UnidadeId']."
														ORDER BY SetorNome ASC";
												$result = $conn->query($sql);
												$rowSetor = $result->fetchAll(PDO::FETCH_ASSOC);

												foreach ($rowSetor as $item) {
													$seleciona = $item['SetorId'] == $rowSetorLocal['UsXUnSetor'] ? "selected" : "";
													print('<option value="' . $item['SetorId'] . '" '. $seleciona .'>' . $item['SetorNome'] . '</option>');
												}
												?>
											</select>
										</div>
									</div>

									<div class="col-lg-4" id="LocalEstoque" <?php if ($row['PerfiChave'] != 'ALMOXARIFADO') { print('style="display: none"'); } ?>>
										<div class="form-group">
											<label for="cmbLocalEstoque">Local de Estoque<span class="text-danger"> *</span></label>
											<select name="cmbLocalEstoque" id="cmbLocalEstoque" class="form-control form-control-select2" <?php if ($row['PerfiChave'] == 'ALMOXARIFADO') { print('required'); } ?>>
												<option value="">Informe um local de estoque</option>
												<?php
												$sql = "SELECT LcEstId, LcEstNome
														FROM LocalEstoque
														JOIN Situacao on SituaId = LcEstStatus
														WHERE SituaChave = 'ATIVO' and LcEstUnidade = ".$_SESSION['UnidadeId']."
														ORDER BY LcEstNome ASC";
												$result = $conn->query($sql);
												$rowLocal = $result->fetchAll(PDO::FETCH_ASSOC);

												foreach ($rowLocal as $item) {
													$seleciona = $item['LcEstId'] == $rowSetorLocal['UsXUnLocalEstoque'] ? "selected" : "";
													print('<option value="' . $item['LcEstId'] . '" '. $seleciona .'>' . $item['LcEstNome'] . '</option>');
												}
												?>
											</select>
										</div>
									</div>

								</div>
							</div>
						</div>
						<?php } ?>
						
						<div class="row" style="margin-top: 20px;">
							<div class="col-lg-12">
								<div class="form-group">
									<?php
										if ($_POST['inputPermission']) {	
											if (isset($_SESSION['EmpresaId'])){
												print('<button class="btn btn-lg btn-principal" id="enviarEmpresa">Alterar</button>');
											} else{
												print('<button class="btn btn-lg btn-principal" id="enviar">Alterar</button>');
											}										
										}
									?>
									<a href="usuario.php" class="btn btn-basic" role="button" id="cancelar">Cancelar</a>
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