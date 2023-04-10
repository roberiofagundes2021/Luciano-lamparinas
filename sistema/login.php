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
$_SESSION['Permissoes'] = Array();
$MultEmpresas = false;
$usuaId = 0;

$usuarioId = isset($_POST['usuarioId'])?$_POST['usuarioId']:false;

// Se a pessoa preencheu o login
if(isset($_POST['usuario'])){
	$newUser = false;

	$psUsuario = $_POST['usuario'];
	$psSenha = md5($_POST['senha']);

	if(isset($_SESSION['UsuarLogin'])){
		if($_SESSION['UsuarLogin'] != $_POST['usuario']){
			$newUser = true; // verifica se a pessoa alterou o login para refaze-lo com outro usuário
		}
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
	
	$sPerfilChave = isset($row['PerfiChave'])?$row['PerfiChave']:false;
	if ($row == 0){
		$erro[] = "O usuário não está cadastrado.";
	} else if ($row['SituaChave'] == 'INATIVO'){
		$erro[] = "Esse usuário está desativado.";
	} else if (strcmp($row['UsuarSenha'], ($psSenha)) != 0){  //"strcmp": compara 2 strings (se for 0 significa que são iguais)
		$erro[] = "<strong>Senha</strong> incorreta.";
	} else {
		$sql = "SELECT UsuarId, UsuarLogin, UsuarNome, EmpreId, EmpreNomeFantasia, PerfiChave, EmpreFoto
				FROM Usuario
				JOIN EmpresaXUsuarioXPerfil on EXUXPUsuario = UsuarId
				JOIN Situacao on SituaId = EXUXPStatus
				JOIN Perfil on PerfiId = EXUXPPerfil
				JOIN Empresa on EmpreId = EXUXPEmpresa
				WHERE UsuarLogin = '$usuario_escape' and SituaChave = 'ATIVO' and 
				EmpreId in (Select LicenEmpresa from Licenca JOIN Situacao on SituaId = LicenStatus where LicenDtFim is null or LicenDtFim > GETDATE() and SituaChave = 'ATIVO')";
		$result = $conn->query($sql);
		$row = $result->fetchAll(PDO::FETCH_ASSOC);  //Pega o número de registros associados a essa consulta
		$count = count($row);

		$usuaId = $row[0]['UsuarId'];

		if ($count == 0){
			$erro[] = "A licença da sua empresa expirou. Procure o Gestor do Contrato do sistema \"Lamparinas\" na sua empresa.";
		} else if ($count > 1 and !isset($_POST['empresa'])){
			$erro[] = "Você está vinculado em mais de uma empresa. Informe qual deseja acessar.";
		} else {
			if(!$newUser) {
				$sql = "SELECT UsuarId, UsuarNome, UsuarLogin, UnidaId, UnidaNome, UsXUnResumoFinanceiro, EmpreId, EmpreNomeFantasia, EmpreFoto,
						PerfiChave
						FROM UsuarioXUnidade
						JOIN EmpresaXUsuarioXPerfil on EXUXPId = UsXUnEmpresaUsuarioPerfil
						JOIN Empresa on EmpreId = EXUXPEmpresa
						JOIN Unidade on UnidaId = UsXUnUnidade
						JOIN Usuario on UsuarId = EXUXPUsuario
						JOIN Perfil on PerfiId = EXUXPPerfil
						WHERE EXUXPUsuario = ".$row[0]['UsuarId'];
				$sql .= isset($_POST['empresa'])?" and EXUXPEmpresa = $_POST[empresa]":"";
				$sql .= isset($_POST['unidade'])?" and UnidaId = $_POST[unidade]":"";
				$result = $conn->query($sql);
				$rowUnidade = $result->fetch(PDO::FETCH_ASSOC);  //Pega o número de registros associados a essa consulta
				
				if(!$rowUnidade){
					$erro[] = "O usuário não possui Lotação. Por favor contate um administrador!";
				}else{
					//Pra esse caso aqui só vai vim um registro mesmo, daí precisa do [0] sem fazer o foreach
					$_SESSION['UsuarId'] = $rowUnidade['UsuarId'];
					$_SESSION['UsuarLogin'] = $rowUnidade['UsuarLogin'];
					$_SESSION['UsuarNome'] = $rowUnidade['UsuarNome'];
					$_SESSION['EmpreId'] = $rowUnidade['EmpreId'];
					$_SESSION['EmpreNomeFantasia'] = $rowUnidade['EmpreNomeFantasia'];
					$_SESSION['EmpreFoto'] = $rowUnidade['EmpreFoto'];
					$_SESSION['UnidadeId'] = $rowUnidade['UnidaId'];
					$_SESSION['UnidadeNome'] = $rowUnidade['UnidaNome'];
					$_SESSION['PerfiChave'] = $rowUnidade['PerfiChave'];
					$_SESSION['ResumoFinanceiro'] = $rowUnidade['UsXUnResumoFinanceiro'];
	
					unset($_SESSION['UsuarSenha']);
					
					irpara("index.php");
				}
			}
		}
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

	<!-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"> -->
  	<!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script> -->
  	<!-- <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script> -->

	<script type="text/javascript">

		$(document).ready(function() {
			$('.divEmpresa').hide();
			$('.divUnidade').hide();

			if($('#usuarioId').val()){
				$.ajax({
					type: "POST",
					url: "loginFiltraUnidade.php",
					dataType: "json",
					data: {
						usuario: $('#usuarioId').val()
					},
					success: function(resposta){
						if(resposta.length){
							var HTML = '';
							var count = resposta.length;
							for(var i = 0; i < count; i++){
								HTML += HTML?'<option value="'+resposta[i].EmpreId+'">'+resposta[i].EmpreNomeFantasia+'</option>':'<option selectted value="'+resposta[i].EmpreId+'">'+resposta[i].EmpreNomeFantasia+'</option>';
							}
							$('#empresaSelect').html(HTML);
							$('.divEmpresa').show();

							$.ajax({
								type: "POST",
								url: "loginFiltraUnidade.php",
								dataType: "json",
								data: {
									empresa: resposta[0].EmpreId,
									usuario: $('#usuarioId').val()
								},
								success: function(resposta){
									if(resposta.length){
										var HTML = '';
										var count = resposta.length;
										for(var i = 0; i < count; i++){
											HTML += HTML?'<option value="'+resposta[i].UnidaId+'">'+resposta[i].UnidaNome+'</option>':'<option selectted value="'+resposta[i].UnidaId+'">'+resposta[i].UnidaNome+'</option>';
										}
										$('#unidadeSelect').html(HTML);
										$('.divUnidade').show();
									}
								}
							})
						}
					}
				})
			}

			$('#empresaSelect').on('change', function(){
				$.ajax({
					type: "POST",
					url: "loginFiltraUnidade.php",
					dataType: "json",
					data: {
						empresa: $('#empresaSelect').val(),
						usuario: $('#usuarioId').val()
					},
					success: function(resposta){
						if(resposta.length){
							var HTML = '';
							var count = resposta.length;
							for(var i = 0; i < count; i++){
								HTML += HTML?'<option value="'+resposta[i].UnidaId+'">'+resposta[i].UnidaNome+'</option>':'<option selectted value="'+resposta[i].UnidaId+'">'+resposta[i].UnidaNome+'</option>';
							}
							$('#unidadeSelect').html(HTML);
							$('.divUnidade').show();
						}
					}
				})
			});	
		
			// Não permitir aspas no campo usuário.
			$('#usuario').on('input', function (e) {
				this.value = this.value.replace(/['"]/g, "");
			}); 
		})

	</script>
</head>

<body>

	<!-- Page content -->
	<div class="page-content">

		<div class="col-lg-12">
			
			<div class="row" style="height:100%">
				
				<div class="col-lg-8 login-cover login-banner" style="min-height: 100%">
				</div>
				<div class="col-lg-4 col-xs-12">

					<div class="content d-flex justify-content-center align-items-center" style="display: table-cell; vertical-align: middle; height: 100%">
						
						<!-- Login form -->
						<form name="formLogin" method="post" class="login-form form-validate" action="login.php">
							<div>
								<div>
									<div class="text-center" style="margin-bottom: 60px;">
										<!--<i class="icon-reading icon-2x text-slate-300 border-slate-300 border-3 rounded-round p-3 mb-3 mt-1"></i>-->
										<img src="global_assets/images/lamparinas/logo-lamparinas.png" style="max-width:300px;" />
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

										print ("<input type='hidden' name='usuarioId' id='usuarioId' value='$usuaId' />");

										print("
										<div class='form-group divEmpresa'>
											<select id='empresaSelect' name='empresa' class='form-control select' data-fouc>
											</select>
										</div>");
										
										print('
										<div class="form-group divUnidade">
											<select id="unidadeSelect" name="unidade" class="form-control select" data-fouc>
											</select>
										</div>');
									?>							
									
									<div class="form-group form-group-feedback form-group-feedback-left">
										<input value="<?php if(isset($_SESSION['UsuarLogin'])) echo $_SESSION['UsuarLogin']; ?>" name="usuario" id="usuario" type="text" class="form-control" placeholder="Usuário..." required <?php if(!isset($_SESSION['UsuarLogin'])) echo "autofocus"; ?>>
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

									<div class="form-group" style="margin-top: 30px;">
										<button type="submit" class="btn btn-primary btn-lg btn-block" style="padding: 10px;">Acessar Sistema</button>
									</div>

									<div class="form-group text-center mt-5">
										<a href="esqueceu-sua-senha.php">Esqueceu sua senha?</a>
									</div>							
								</div>
							</div>
							<div class="form-group text-center text-muted content-divider" style="margin-bottom: 85px; display:none;">
								<span class="px-2"><a href="http://www.lamparinas.com.br">Ir para o site</a></spam>
							</div>
						</form>
						<!-- /login form -->

					</div>

				</div>
			</div>
		</div>
	
	</div>
	<!-- /page content -->

</body>
</html>
