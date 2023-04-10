<?php

if(!isset($_SESSION)){
    session_start();
}

date_default_timezone_set("Brazil/East");

require_once("global_assets/php/funcoesgerais.php");
include('global_assets/php/conexao.php');

$erro = array();
$empresas = array();
$piEmpresa = 0;
$piUnidade = 0;

// Se a pessoa preencheu o login
if(isset($_POST['usuario'])){

	$psUsuario = $_POST['usuario'];
	$psSenha = md5($_POST['senha']);

	if (isset($_POST['empresa'])){
		$piEmpresa = $_POST['empresa'];
		$_SESSION['EmpreId'] = $piEmpresa;
	}

	if (isset($_POST['unidade'])){
		$piUnidade = $_POST['unidade'];
		$_SESSION['UnidadeId'] = $piUnidade;
	}	

	$_SESSION['UsuarLogin'] = $_POST['usuario'];
	$_SESSION['UsuarSenha'] = $_POST['senha'];

	$usuario_escape = addslashes($psUsuario);
	$senha_escape = addslashes($psSenha);

	$sql = "SELECT Top 1 UsuarId, UsuarLogin, UsuarNome, UsuarSenha, EXUXPStatus, PerfiChave, SituaChave
			FROM Usuario
			JOIN EmpresaXUsuarioXPerfil on EXUXPUsuario = UsuarId
			JOIN Situacao on SituaId = EXUXPStatus	
			JOIN Perfil on PerfiId = EXUXPPerfil			 
			WHERE UsuarLogin = '$usuario_escape' ";
	$result = $conn->query($sql);
	$row = $result->fetch();
	//echo $sql;die;
	//	$_SESSION['UsuarLogado'] = 0;
	
	$sPerfilChave = $row['PerfiChave'];
	
	if ($row == 0){	
		$erro[] = "O usuário não está cadastrado.";
	} else if ($row['SituaChave'] == 'INATIVO'){
		$erro[] = "Esse usuário está desativado.";
	} else if (strcmp($row['UsuarSenha'], ($psSenha)) != 0){  //"strcmp": compara 2 strings (se for 0 significa que são iguais)
		$erro[] = "<strong>Senha</strong> incorreta.";
	} else {	
		
	/*	//Se Super Usuário e cadastro ativo, pode acessar qualquer empresa
		if ($row['PerfiChave'] == 'SUPER' and $row['EXUXPStatus'] == 1){
			
			//Se não foi selecionado nenhuma empresa ainda
			if ($piEmpresa == 0){
				$erro[] = "Você está vinculado em mais de uma empresa. Informe qual deseja acessar.";
				
				$_SESSION['EmpreId'] = 99999999;  // Se preferirem deixar pre-selecionado já uma empresa basta trocar o 9999999 por $row[0]['EmpreId']
				
				$sql = ("SELECT EmpreId, EmpreNomeFantasia
						 FROM Empresa
					     WHERE EmpreStatus = 1");				
				$result = $conn->query("$sql");
				while ($linhas = $result->fetch()){
					$_SESSION['Empresa'][$linhas['EmpreId']] = $linhas['EmpreNomeFantasia'];
				}				
			} else {
				$sql = ("SELECT UsuarId, UsuarLogin, UsuarNome, EmpreId, EmpreNomeFantasia, PerfiChave
						 FROM Usuario
						 JOIN EmpresaXUsuarioXPerfil EUP on EXUXPUsuario = UsuarId
						 JOIN Perfil on PerfiId = EXUXPPerfil
						 JOIN Empresa on EmpreId = EXUXPEmpresa
						 WHERE UsuarLogin = '$usuario_escape' and EXUXPStatus = 1 and EmpreId = $piEmpresa
						 ");
				$result = $conn->query("$sql");
				$row = $result->fetch();
				
				$_SESSION['UsuarId'] = $row['UsuarId'];
				$_SESSION['UsuarLogin'] = $row['UsuarLogin'];
				$_SESSION['UsuarNome'] = $row['UsuarNome'];
				$_SESSION['PerfiChave'] = $row['PerfiChave'];
				
				$_SESSION['EmpreId'] = $row['EmpreId'];
				$_SESSION['EmpreNomeFantasia'] = $row['EmpreNomeFantasia'];
				$_SESSION['UsuarLogado'] = 1;
				
				irpara("index.php");
	
			}
		} else { */
		
			$sql = "SELECT UsuarId, UsuarLogin, UsuarNome, EmpreId, EmpreNomeFantasia, UnidaId, UnidaNome, PerfiChave, EmpreFoto
					FROM Usuario
					JOIN EmpresaXUsuarioXPerfil on EXUXPUsuario = UsuarId
					JOIN Situacao on SituaId = EXUXPStatus
					JOIN Perfil on PerfiId = EXUXPPerfil
					JOIN Empresa on EmpreId = EXUXPEmpresa
					JOIN UsuarioXUnidade on UsXUnEmpresaUsuarioPerfil = EXUXPId
					JOIN Unidade on UnidaId = UsXUnUnidade
					WHERE UsuarLogin = '$usuario_escape' and SituaChave = 'ATIVO' and 
					      EmpreId in (Select LicenEmpresa from Licenca JOIN Situacao on SituaId = LicenStatus where LicenDtFim is null or LicenDtFim > GETDATE() and SituaChave = 'ATIVO')
					";
			$result = $conn->query($sql);
			$row = $result->fetchAll(PDO::FETCH_ASSOC);  //Pega o número de registros associados a essa consulta
			$count = count($row);		
			//echo $sql;die;

			if ($count == 0){
				$erro[] = "A licença da sua empresa expirou. Procure o Gestor do Contrato do sistema \"Lamparinas\" na sua empresa.";			
			} else if ($count > 1 and $piEmpresa == 0) {				
				
				$empresa = 0;
				$unidade = 0;

				$_SESSION['Empresa'] = [];
				$_SESSION['Unidade'] = [];

				$result = $conn->query($sql);
				while ($linhas = $result->fetch()){
					
					if (isset($_SESSION['Empresa']) && !array_key_exists($linhas['EmpreId'], $_SESSION['Empresa'])){
						$_SESSION['Empresa'][$linhas['EmpreId']] = $linhas['EmpreNomeFantasia'];

						$empresa++;
					}
					
					if (isset($_SESSION['Unidade']) && !array_key_exists($linhas['UnidaId'], $_SESSION['Unidade'])){
						$_SESSION['Unidade'][$linhas['UnidaId']] = $linhas['UnidaNome'];

						$unidade++;
					}
				}

				if ($empresa > 1 && $unidade > 1) {
					$_SESSION['EmpreId'] = 99999999;  // Se preferirem deixar pre-selecionado já uma empresa basta trocar o 9999999 por $row[0]['EmpreId']
					$_SESSION['UnidaId'] = 99999999;  // Se preferirem deixar pre-selecionado já uma empresa basta trocar o 9999999 por $row[0]['EmpreId']
					$erro[] = "Você está vinculado em mais de uma empresa. Informe qual deseja acessar.";
				} else {
					if ($empresa > 1) {
						$_SESSION['EmpreId'] = 99999999;  // Se preferirem deixar pre-selecionado já uma empresa basta trocar o 9999999 por $row[0]['EmpreId']
						$erro[] = "Você está vinculado em mais de uma empresa. Informe qual deseja acessar.";
					}
					
					if ($unidade > 1) {
						$_SESSION['UnidaId'] = 99999999;  // Se preferirem deixar pre-selecionado já uma empresa basta trocar o 9999999 por $row[0]['EmpreId']
						$erro[] = "Você está vinculado em mais de uma unidade. Informe qual deseja acessar.";
					}	
				}

			} else if ($piEmpresa) {
/*
				$sql = "SELECT UnidaId, UnidaNome
						FROM UsuarioXUnidade
						JOIN EmpresaXUsuarioXPerfil on EXUXPId = UsXUnEmpresaUsuarioPerfil
						JOIN Unidade on UnidaId = UsXUnUnidade
						WHERE EXUXPUsuario = ".$row[0]['UsuarId']." and EXUXPEmpresa = ".$piEmpresa."
						";
				$result = $conn->query($sql);
				$rowUnidade = $result->fetchAll(PDO::FETCH_ASSOC);  //Pega o número de registros associados a essa consulta
				$countUnidade = count($rowUnidade);					
				
				if ($countUnidade == 0){
					$erro[] = "O usuário está cadastrado em uma empresa, porém não está vinculado a nenhuma unidade. Favor acionar o responsável pelo cadastro na sua empresa.";			
				} else if ($countUnidade == 1) {
*/					
					$sql = "SELECT UsuarId, UsuarLogin, UsuarNome, EmpreId, EmpreNomeFantasia, PerfiChave, EmpreFoto
							FROM Usuario
							JOIN EmpresaXUsuarioXPerfil EUP on EXUXPUsuario = UsuarId
							JOIN Situacao on SituaId = EXUXPStatus
							JOIN Perfil on PerfiId = EXUXPPerfil
							JOIN Empresa on EmpreId = EXUXPEmpresa
							WHERE UsuarLogin = '$usuario_escape' and SituaChave = 'ATIVO' and EmpreId = $piEmpresa and 
							EmpreId in (Select LicenEmpresa from Licenca JOIN Situacao on SituaId = LicenStatus where LicenDtFim is null or LicenDtFim > GETDATE() and SituaChave = 'ATIVO')
							";
					$result = $conn->query($sql);
					$row = $result->fetch();
					
					if ($row > 0){
						$_SESSION['UsuarId'] = $row['UsuarId'];
						$_SESSION['UsuarLogin'] = $row['UsuarLogin'];
						$_SESSION['UsuarNome'] = $row['UsuarNome'];
						$_SESSION['EmpreId'] = $row['EmpreId'];
						$_SESSION['EmpreNomeFantasia'] = $row['EmpreNomeFantasia'];
						$_SESSION['EmpreFoto'] = $row['EmpreFoto'];
						$_SESSION['UnidadeId'] = $rowUnidade[0]['UnidaId'];
						$_SESSION['UnidadeNome'] = $rowUnidade[0]['UnidaNome'];
						$_SESSION['PerfiChave'] = $row['PerfiChave'];					
						//$_SESSION['UsuarLogado'] = 1;
						
						unset($_SESSION['UsuarSenha']);

						irpara("index.php");
					} /*else {
						$erro[] = "Login inválido. Verifique se o usuário informado faz parte dessa empresa.";
					}
				} else {
					$erro[] = "Você está vinculado em mais de uma unidade dessa empresa. Informe qual deseja acessar.";
				
					$_SESSION['UnidadeId'] = 99999999;  // Se preferirem deixar pre-selecionado já uma empresa basta trocar o 9999999 por $row[0]['EmpreId']
					
					$result = $conn->query($sql);
					while ($linhas = $result->fetch()){
						$_SESSION['Unidade'][$linhas['UnidaId']] = $linhas['UnidaNome'];
					}
				}
*/				
			} else {

				if ($piUnidade){

					$sql = "SELECT UnidaId, UnidaNome
							FROM Unidade
							WHERE UnidaId = ".$piUnidade;
					$result = $conn->query($sql);
					$rowUnidade = $result->fetch(PDO::FETCH_ASSOC);

					//Pra esse caso aqui só vai vim um registro mesmo, daí precisa do [0] sem fazer o foreach
					$_SESSION['UsuarId'] = $row[0]['UsuarId'];
					$_SESSION['UsuarLogin'] = $row[0]['UsuarLogin'];
					$_SESSION['UsuarNome'] = $row[0]['UsuarNome'];
					$_SESSION['EmpreId'] = $row[0]['EmpreId'];
					$_SESSION['EmpreNomeFantasia'] = $row[0]['EmpreNomeFantasia'];
					$_SESSION['EmpreFoto'] = $row[0]['EmpreFoto'];
					$_SESSION['UnidadeId'] = $rowUnidade['UnidaId'];;
					$_SESSION['UnidadeNome'] = $rowUnidade['UnidaNome'];
					$_SESSION['PerfiChave'] = $row[0]['PerfiChave'];
					//$_SESSION['UsuarLogado'] = 1;

					unset($_SESSION['UsuarSenha']);

					irpara("index.php");					

				} else {
					$sql = "SELECT UnidaId, UnidaNome
							FROM UsuarioXUnidade
							JOIN EmpresaXUsuarioXPerfil on EXUXPId = UsXUnEmpresaUsuarioPerfil
							JOIN Unidade on UnidaId = UsXUnUnidade
							WHERE EXUXPUsuario = ".$row[0]['UsuarId']." and EXUXPEmpresa = ".$row[0]['EmpreId']."
							";
					$result = $conn->query($sql);
					$rowUnidade = $result->fetchAll(PDO::FETCH_ASSOC);  //Pega o número de registros associados a essa consulta
					$countUnidade = count($rowUnidade);	
					//echo "Entrou aqui: ".$sql;die;
					
					if ($countUnidade == 0){
						$erro[] = "O usuário está cadastrado em uma empresa, porém não está vinculado a nenhuma unidade. Favor acionar o responsável pelo cadastro na sua empresa.";			
					} else if ($countUnidade == 1) {				
		
						//Pra esse caso aqui só vai vim um registro mesmo, daí precisa do [0] sem fazer o foreach
						$_SESSION['UsuarId'] = $row[0]['UsuarId'];
						$_SESSION['UsuarLogin'] = $row[0]['UsuarLogin'];
						$_SESSION['UsuarNome'] = $row[0]['UsuarNome'];
						$_SESSION['EmpreId'] = $row[0]['EmpreId'];
						$_SESSION['EmpreNomeFantasia'] = $row[0]['EmpreNomeFantasia'];
						$_SESSION['EmpreFoto'] = $row[0]['EmpreFoto'];
						$_SESSION['UnidadeId'] = $rowUnidade[0]['UnidaId'];;
						$_SESSION['UnidadeNome'] = $rowUnidade[0]['UnidaNome'];
						$_SESSION['PerfiChave'] = $row[0]['PerfiChave'];
						//$_SESSION['UsuarLogado'] = 1;

						unset($_SESSION['UsuarSenha']);
						
						irpara("index.php");

					} else {
						$erro[] = "Você está vinculado em mais de uma unidade dessa empresa. Informe qual deseja acessar.";
					
						$_SESSION['UnidadeId'] = 99999999;  // Se preferirem deixar pre-selecionado já uma empresa basta trocar o 9999999 por $row[0]['EmpreId']
						
						$result = $conn->query($sql);
						while ($linhas = $result->fetch()){
							$_SESSION['Unidade'][$linhas['UnidaId']] = $linhas['UnidaNome'];
						}
					}
				}				
			}
	//	}
	} 
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Login</title>

	<?php include_once("head.php"); ?>

	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
	<script src="global_assets/js/demo_pages/form_select2.js"></script>
</head>

<body>

	<!-- Page content -->
	<div class="page-content login-cover">

		<!-- Main content -->
		<div class="content-wrapper">

			<!-- Content area -->
			<div class="content d-flex justify-content-center align-items-center">

				<!-- Login form -->
				<form name="formLogin" method="post" class="login-form form-validate" action="login.php">
					<div class="card mb-0">
						<div class="card-body">
							<div class="text-center mb-3">
								<!--<i class="icon-reading icon-2x text-slate-300 border-slate-300 border-3 rounded-round p-3 mb-3 mt-1"></i>-->
								<img src="global_assets/images/lamparinas/logo-lamparinas_200x200.jpg" />
								<h5 class="mb-0">Acesse sua conta</h5>
								<span class="d-block text-muted">Informe as credenciais abaixo</span>
							</div>

							<?php 
								if(isset($erro)) {
									if(count($erro) > 0){ 
							?>
										<div class="alert alert-danger">
											<?php foreach($erro as $msg) echo "$msg <br>"; ?>
										</div>
							<?php 
									}
								}
								
								if (isset($_SESSION['EmpreId'])){	
									
									print('
									
									<div class="form-group">
										<select name="empresa" class="form-control select" data-fouc>
											<option value="0">Selecione uma empresa</option>');
											
											foreach($_SESSION['Empresa'] as $indice => $valor){
												if ($_SESSION['EmpreId'] == $indice){
													echo '<option value="'.$indice.'" selected>'.$valor.'</option>';
												} else {
													echo '<option value="'.$indice.'">'.$valor.'</option>';
												}
											}
											
											print('
										</select>
									</div>
									
									');
								}
								
								if (isset($_SESSION['UnidaId'])){	
									
									print('
									
									<div class="form-group">
										<select name="unidade" class="form-control select" data-fouc>
											<option value="0">Selecione uma unidade</option>');
											
											foreach($_SESSION['Unidade'] as $indice => $valor){
												if ($_SESSION['UnidaId'] == $indice){
													echo '<option value="'.$indice.'" selected>'.$valor.'</option>';
												} else {
													echo '<option value="'.$indice.'">'.$valor.'</option>';
												}
											}
											
											print('
										</select>
									</div>
									
									');
								}			
								
								if (isset($_SESSION['EmpreId']) || isset($_SESSION['UnidaId'])){
									print('<div style="margin-top: 20px; margin-bottom: 20px;"><a href="loginReset.php">Resetar Formulário</a></div>');
								}	
                            ?>							
							
							<div class="form-group form-group-feedback form-group-feedback-left">
								<input value="<?php if(isset($_SESSION['UsuarLogin'])) echo $_SESSION['UsuarLogin']; ?>" name="usuario" type="text" class="form-control" placeholder="Usuário..." required <?php if(!isset($_SESSION['UsuarLogin'])) echo "autofocus"; ?>>
								<div class="form-control-feedback">
									<i class="icon-user text-muted"></i>
								</div>
							</div>

							<div class="form-group form-group-feedback form-group-feedback-left">
								<input value="<?php if(isset($_SESSION['UsuarSenha'])) echo $_SESSION['UsuarSenha']; ?>" name="senha" id="senha" type="password" class="form-control" placeholder="Senha..." onKeyPress="if (event.keyCode == 13){document.forms[0].submit();}" required  <?php if(isset($_SESSION['UsuarLogin'])) echo "autofocus"; ?>>
								<div class="form-control-feedback">
									<i class="icon-lock2 text-muted"></i>
								</div>
							</div>

							<div class="form-group">
								<button type="submit" class="btn btn-primary btn-block">Entrar <i class="icon-circle-right2 ml-2"></i></button>
							</div>

							<div class="form-group text-center">
								<a href="esqueceu-sua-senha.php">Esqueceu sua senha?</a>
							</div>							
						</div>
					</div>
					<div class="form-group text-center text-muted content-divider" style="margin-top: 15px;">
						<span class="px-2"><a href="http://www.lamparinas.com.br">Ir para o site</a></spam>
					</div>
				</form>
				<!-- /login form -->

			</div>
			<!-- /content area -->			

			<?php //include_once("footer.php"); ?>

		</div>
		<!-- /main content -->

	</div>
	<!-- /page content -->

</body>
</html>
