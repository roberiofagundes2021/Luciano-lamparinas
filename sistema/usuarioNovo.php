<?php

include_once("sessao.php");

include('global_assets/php/conexao.php');

$_SESSION['PaginaAtual'] = 'Novo Usuário';

if (isset($_SESSION['EmpresaId'])) {
	$EmpresaId = $_SESSION['EmpresaId'];
} else {
	$EmpresaId = $_SESSION['EmpreId'];
}

if (isset($_POST['inputCpf'])) {

	try {

		$conn->beginTransaction();
		
		$visibilidadeResumoFinanceiro = isset($_POST['inputVisualisaResumoFinanceiro']) ? true : false;

		$operadorCaixa = isset($_POST['inputOperadorCaixa']) ? true : false;

		//Se for um novo usuário que ainda não estava cadastrado em nenhuma empresa
		if ($_POST['inputId'] == 0) {


			//Passo1: inserir na tabela Usuario
			$sql = "INSERT INTO Usuario (UsuarCpf, UsuarNome, UsuarLogin, UsuarSenha, UsuarEmail, UsuarTelefone, UsuarCelular)
					VALUES (:sCpf, :sNome, :sLogin, :sSenha, :sEmail, :sTelefone, :sCelular)";
			$result = $conn->prepare($sql);

			$result->execute(array(
				':sCpf' => limpaCPF_CNPJ($_POST['inputCpf']),
				':sNome' => $_POST['inputNome'],
				':sLogin' => $_POST['inputLogin'],
				':sSenha' => md5($_POST['inputSenha']),
				':sEmail' => $_POST['inputEmail'],
				':sTelefone' => $_POST['inputTelefone'] == '(__) ____-____' ? null : $_POST['inputTelefone'],
				':sCelular' => $_POST['inputCelular'] == '(__) _____-____' ? null : $_POST['inputCelular']
			));
			$LAST_ID_USUARIO = $conn->lastInsertId();

			//Passo2: inserir na tabela EmpresaXUsuarioXPerfil
			$sql = "INSERT INTO EmpresaXUsuarioXPerfil (EXUXPEmpresa, EXUXPUsuario, EXUXPPerfil, EXUXPStatus, EXUXPUsuarioAtualizador)
					VALUES (:iEmpresa, :iUsuario, :iPerfil, :bStatus, :iUsuarioAtualizador)";
			$result = $conn->prepare($sql);

			$result->execute(array(
				':iEmpresa' => $EmpresaId,
				':iUsuario' => $LAST_ID_USUARIO,
				':iPerfil' => $_POST['cmbPerfil'],
				':bStatus' => 1,
				':iUsuarioAtualizador' => $_SESSION['UsuarId']
			));
			$LAST_ID_EXUXP = $conn->lastInsertId();

			if (!isset($_SESSION['EmpresaId'])){
				
				//Passo3: inserir na tabela UsuarioXUnidade (vinculando o usuário na Unidade, Setor e Local de Estoque)
				$sql = "INSERT INTO UsuarioXUnidade (UsXUnEmpresaUsuarioPerfil, UsXUnUnidade, UsXUnSetor, UsXUnLocalEstoque, UsXUnPermissaoPerfil, UsXUnResumoFinanceiro, UsXUnOperadorCaixa, UsXUnUsuarioAtualizador)
						VALUES (:iEmpresaUsarioPerfil, :iUnidade, :iSetor, :iLocalEstoque, :PermissaoPerfil, :bResumoFinanceiro, :bOperadorCaixa, :iUsuarioAtualizador)";
				$result = $conn->prepare($sql);

				$result->execute(array(
					':iEmpresaUsarioPerfil' => $LAST_ID_EXUXP,
					':iUnidade' => $_SESSION['UnidadeId'],
					':iSetor' => $_POST['cmbSetor'],
					':iLocalEstoque' => isset($_POST['cmbLocalEstoque']) && $_POST['cmbLocalEstoque'] != '' ? $_POST['cmbLocalEstoque'] : null,
					':PermissaoPerfil' => 1,
					':bResumoFinanceiro' => $visibilidadeResumoFinanceiro,
					':bOperadorCaixa' => $operadorCaixa,
					':iUsuarioAtualizador' => $_SESSION['UsuarId']
					));
			}

		} else {

			//Passo1: atualizar o dados do usuário na tabela Usuario
			$sql = "UPDATE Usuario SET UsuarNome = :sNome, usuarLogin = :sLogin, UsuarSenha = :sSenha, UsuarEmail = :sEmail, 
					UsuarTelefone = :sTelefone, UsuarCelular = :sCelular
					WHERE UsuarId = :iUsuario";
			$result = $conn->prepare($sql);

			$result->execute(array(
				':sNome' => $_POST['inputNome'],
				':sLogin' => $_POST['inputLogin'],
				':sSenha' => $_POST['inputSenha'],
				':sEmail' => $_POST['inputEmail'],
				':sTelefone' => $_POST['inputTelefone'] == '(__) ____-____' ? null : $_POST['inputTelefone'],
				':sCelular' => $_POST['inputCelular'] == '(__) _____-____' ? null : $_POST['inputCelular'],
				':iUsuario' => $_POST['inputId']
			));

			//Passo2: inserir na tabela EmpresaXUsuarioXPerfil
			$sql = "INSERT INTO EmpresaXUsuarioXPerfil (EXUXPEmpresa, EXUXPUsuario, EXUXPPerfil, EXUXPStatus, EXUXPUsuarioAtualizador)
					VALUES (:iEmpresa, :iUsuario, :iPerfil, :bStatus, :iUsuarioAtualizador)";
			$result = $conn->prepare($sql);
			$result->execute(array(
				':iEmpresa' => $EmpresaId,
				':iUsuario' => $_POST['inputId'],
				':iPerfil' => $_POST['cmbPerfil'] == '' ? null : $_POST['cmbPerfil'],
				':bStatus' => 1,
				':iUsuarioAtualizador' => $_SESSION['UsuarId']
			));
			$LAST_ID_EXUXP = $conn->lastInsertId();

			if (!isset($_SESSION['EmpresaId'])){			
				
				//Passo3: inserir na tabela UsuarioXUnidade (vinculando o usuário na Unidade, Setor e Local de Estoque)
				$sql = "INSERT INTO UsuarioXUnidade (UsXUnEmpresaUsuarioPerfil, UsXUnUnidade, UsXUnSetor, UsXUnLocalEstoque, UsXUnPermissaoPerfil, UsXUnResumoFinanceiro, UsXUnOperadorCaixa, UsXUnUsuarioAtualizador)
							VALUES (:iEmpresaUsarioPerfil, :iUnidade, :iSetor, :iLocalEstoque, :PermissaoPerfil, :bResumoFinanceiro, :bOperadorCaixa, :iUsuarioAtualizador )";
				$result = $conn->prepare($sql);

				$result->execute(array(
					':iEmpresaUsarioPerfil' => $LAST_ID_EXUXP,
					':iUnidade' => $_SESSION['UnidadeId'],
					':iSetor' => $_POST['cmbSetor'],
					':iLocalEstoque' => isset($_POST['cmbLocalEstoque']) && $_POST['cmbLocalEstoque'] != '' ? $_POST['cmbLocalEstoque'] : null,
					':PermissaoPerfil' => 1,
					':bResumoFinanceiro' => $visibilidadeResumoFinanceiro,
					':bOperadorCaixa' => $operadorCaixa,
					':iUsuarioAtualizador' => $_SESSION['UsuarId']
					));
			}
		}

		$conn->commit();

		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Usuário incluído!!!";
		$_SESSION['msg']['tipo'] = "success";
	} catch (PDOException $e) {

		$conn->rollback();

		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao incluir usuário!!!";
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

			//Garantindo que ninguém mude a empresa na tela de inclusão
			$('#cmbEmpresa').prop("disabled", true);

			//Ao mudar a categoria, filtra a subcategoria via ajax (retorno via JSON)
			$('#buscar').on('click', function(e) {

				var inputCpf = $('#inputCpf').val().replace(/[^\d]+/g, '');
				var inputId = $('#inputId').val();

				if (inputCpf.length < 11) {
					alerta('Atenção', 'O CPF precisa ser informado corretamente!', 'error');
					$('#inputCpf').focus();
					return false;
				}

				if (!validaCPF(inputCpf)) {
					alerta('Atenção', 'CPF inválido!', 'error');
					$('#inputCpf').focus();
					return false;
				}

				$.getJSON('usuarioValida.php?cpf=' + inputCpf, function(dados) {

					//Se o usuário está cadastrado e ele não está vinculado a essa empresa ainda
					if (typeof dados === 'object') {

						document.getElementById('demaisCampos').style.display = "block";

						$.each(dados, function(i, obj) {
							$('#inputId').val(obj.UsuarId);
							$('#inputNome').val(obj.UsuarNome);
							$('#inputLoginVelho').val(obj.UsuarLogin);
							$('#inputLogin').val(obj.UsuarLogin);
							$('#inputSenha').val(obj.UsuarSenha);
							$('#inputConfirmaSenha').val(obj.UsuarSenha);
							$('#inputEmail').val(obj.UsuarEmail);
							$('#inputTelefone').val(obj.UsuarTelefone);
							$('#inputCelular').val(obj.UsuarCelular);
						});

						$('#enviar').prop("disabled", false);

					} else {
						//se o usuário está cadastrado e já está vinculado a essa empresa
						if (dados) {
							document.getElementById('demaisCampos').style.display = "none";
							alerta('Atenção', 'O usuário com CPF ' + inputCpf + ' já está vinculado a essa empresa!', 'error');
							$('#enviar').prop("disabled", true);
							$('#inputCpf').val();
							$('#inputCpf').focus();
							return false;
						} else { // se o usuário não está cadastrado
							document.getElementById('demaisCampos').style.display = "block";
							$('#inputNome').val("");
							$('#cmbPerfil').val("");
							$('#inputLogin').val("");
							$('#inputSenha').val("");
							$('#inputConfirmaSenha').val("");
							$('#inputEmail').val("");
							$('#inputTelefone').val("");
							$('#inputCelular').val("");
							$('#cmbUnidade').val("");
							$('#cmbSetor').val("");
							$('#inputNome').focus();
							$('#enviar').prop("disabled", false);
							$('#inputId').val(0);
						}
					}
				});
			});

			$('#cmbPerfil').on('change', function(e) {
				let filhos = $('#cmbPerfil').children()
				let valorcmb = $('#cmbPerfil').val()
				filhos.each((i, elem) => {
					let perfil = $(elem).attr('chaveperfil')
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

			$('#inputCpf').on('change', function(e) {
				$('#buscar').trigger('click');
			});

			$('#inputCpf').keypress(function(e) {
				if (e.which == 13) {
					$('#buscar').trigger('click');
				}
			});

			//Valida Registro Duplicado
			$('#enviar').on('click', function(e) {

				e.preventDefault();

				var inputNome = $('#inputNome').val();
				var cmbPerfil = $('#cmbPerfil').val();
				var inputLoginVelho = $('#inputLoginVelho').val();
				var inputLoginNovo = $('#inputLogin').val();
				var inputEmail = $('#inputEmail').val();
				var inputSenha = $('#inputSenha').val();
				var inputConfirmaSenha = $('#inputConfirmaSenha').val();
				var cmbUnidade = $('#cmbUnidade').val();

				if (inputNome != '' && inputLoginNovo != '' && inputEmail != '' && cmbPerfil != '' && cmbUnidade != ''){
					if (inputSenha != inputConfirmaSenha) {
						alerta('Atenção', 'A confirmação de senha não confere!', 'error');
						$('#inputConfirmaSenha').focus();
						$("#formUsuario").submit();
						return false;
					}
				}

				$.getJSON('usuarioValida.php?loginVelho='+inputLoginVelho+'&loginNovo='+inputLoginNovo, function(dados) {
					if (dados){
						alerta('Atenção', 'Esse login já está em uso para outro usuário dessa empresa!', 'error');
						$('#inputLogin').focus();
						return false;
					}
				});	

				$('#cmbEmpresa').prop("disabled", false);				

				$("#formUsuario").submit();
				//document.formUsuario.submit();				
			});

			$('#cancelar').on('click', function(e) {

				$('#cmbEmpresa').prop("disabled", false);

				$(window.document.location).attr('href', "usuario.php");
			});			
		});

		function Filtrando() {
			$('#cmbSetor').empty().append('<option value="">Filtrando...</option>');
			$('#cmbLocalEstoque').empty().append('<option value="">Filtrando...</option>');
		}	
					
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

				<form name="formUsuario" id="formUsuario" method="post" class="form-validate-jquery">
					<div class="card-header header-elements-inline">
						<h5 class="text-uppercase font-weight-bold">Cadastrar Novo Usuário</h5>
					</div>

					<div class="card-body">
						<div class="row">
							<div class="col-lg-2" style="max-width:150px;">
								<label for="inputCpf">CPF<span class="text-danger"> *</span></label>
								<div class="form-group form-group-feedback form-group-feedback-right">
									<input type="text" id="inputCpf" name="inputCpf" class="form-control" placeholder="CPF" data-mask="999.999.999-99" required autofocus>
									<div class="form-control-feedback" id="buscar" style="cursor: pointer;">
										<i class="icon-search4"></i>
									</div>
									<input type="hidden" id="inputId" name="inputId" value="0">
									<input type="hidden" id="inputLoginVelho" name="inputVelho" value="">
								</div>
							</div>
						</div>

						<div id="demaisCampos" style="display:none;">
							<div class="row">
								<div class="col-lg-12">
									<div class="row">
										<div class="col-lg-4">
											<div class="form-group">
												<label for="inputNome">Nome<span class="text-danger"> *</span></label>
												<input type="text" id="inputNome" name="inputNome" class="form-control" placeholder="Nome" required>
											</div>
										</div>
										<div class="col-lg-4">
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
														print('<option value="' . $item['PerfiId'] . '" chavePerfil="' . $item['PerfiChave'] . '">' . $item['PerfiNome'] . '</option>');
													}
													?>
												</select>
											</div>
										</div>
										<?php if (!isset($_SESSION['EmpresaId'])){ ?>
										<div class="col-lg-3" style="margin-top: auto; margin-bottom: auto;">
											<div class="custom-control custom-checkbox">
												<input type="checkbox" class="custom-control-input" value="1" id="inputVisualisaResumoFinanceiro" name="inputVisualisaResumoFinanceiro">
												<label class="custom-control-label" for="inputVisualisaResumoFinanceiro">Resumo Financeiro Visível</label>
											</div>
										
											<div class="custom-control custom-checkbox">
												<input type="checkbox" class="custom-control-input" value="1" id="inputOperadorCaixa" name="inputOperadorCaixa">
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
												<input type="text" id="inputLogin" name="inputLogin" class="form-control" placeholder="Login" required>
											</div>
										</div>

										<div class="col-lg-4">
											<div class="form-group">
												<label for="inputSenha">Senha<span class="text-danger"> *</span></label>
												<input type="password" id="inputSenha" name="inputSenha" class="form-control" placeholder="Senha" required>
											</div>
										</div>

										<div class="col-lg-4">
											<div class="form-group">
												<label for="inputConfirmaSenha">Confirma Senha<span class="text-danger"> *</span></label>
												<input type="password" id="inputConfirmaSenha" name="inputConfirmaSenha" class="form-control" placeholder="Confirma Senha" required>
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
												<input type="email" id="inputEmail" name="inputEmail" class="form-control" placeholder="E-mail" required>
											</div>
										</div>
										<div class="col-lg-4">
											<div class="form-group">
												<label for="inputTelefone">Telefone</label>
												<input type="tel" id="inputTelefone" name="inputTelefone" class="form-control" placeholder="Telefone" data-mask="(99) 9999-9999">
											</div>
										</div>
										<div class="col-lg-4">
											<div class="form-group">
												<label for="inputCelular">Celular</label>
												<input type="tel" id="inputCelular" name="inputCelular" class="form-control" placeholder="Celular" data-mask="(99) 99999-9999">
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
														print('<option value="' . $item['SetorId'] . '">' . $item['SetorNome'] . '</option>');
													}
													?>
												</select>
											</div>
										</div>

										<div class="col-lg-4" id="LocalEstoque" style="display: none">
											<div class="form-group">
												<label for="cmbLocalEstoque">Local de Estoque<span class="text-danger"> *</span></label>
												<select name="cmbLocalEstoque" id="cmbLocalEstoque" class="form-control form-control-select2" required>
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
														print('<option value="' . $item['LcEstId'] . '">' . $item['LcEstNome'] . '</option>');
													}
													?>
												</select>
											</div>
										</div>

									</div>
								</div>
							</div>
							<?php } ?>
						</div>

						<div class="row" style="margin-top: 20px;">
							<div class="col-lg-12">
								<div class="form-group">
									<button class="btn btn-lg btn-principal" id="enviar" disabled>Incluir</button>
									<a href="usuario.php" class="btn btn-basic" role="button" id="cancelar">Cancelar</a>
								</div>
							</div>
						</div>

					</div>
					<!-- /card-body -->
				
				</form>	

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