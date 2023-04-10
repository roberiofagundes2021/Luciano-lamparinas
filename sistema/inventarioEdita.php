<?php

include_once("sessao.php");

$_SESSION['PaginaAtual'] = 'Editar Inventário';

include('global_assets/php/conexao.php');

//Se veio do inventario.php
if (isset($_POST['inputInventarioId'])) {

	$iInventario = $_POST['inputInventarioId'];

	try {

		$sql = "SELECT InvenId, InvenData, InvenNumero, InvenDataLimite, InvenClassificacao, InvenUnidade, InvenCategoria, 
					   UsuarNome, UsuarTelefone, UsuarEmail, InvenObservacao
				FROM Inventario
				JOIN Usuario on UsuarId = InvenSolicitante
				WHERE InvenId = $iInventario ";
		$result = $conn->query($sql);
		$row = $result->fetch(PDO::FETCH_ASSOC);

		//Locais do inventário
		$sql = "SELECT LcEstId, LcEstNome
				FROM LocalEstoque
				JOIN InventarioXLocalEstoque on InXLELocal = LcEstId
				JOIN Situacao on SituaId = LcEstStatus
				WHERE LcEstUnidade = " . $_SESSION['UnidadeId'] . " and InXLEInventario = " . $iInventario . " and SituaChave = 'ATIVO'
				ORDER BY LcEstNome ASC";
		$result = $conn->query("$sql");
		$rowBDLocal = $result->fetchAll(PDO::FETCH_ASSOC);
		foreach ($rowBDLocal as $item) {
			$aLocaisEstoque[] = $item['LcEstId'];
		}

		$sql = "SELECT SetorId, SetorNome
				 FROM Setor
				 JOIN InventarioXSetor on InXSeSetor = SetorId
				 JOIN Situacao on SituaId = SetorStatus
				 WHERE SetorUnidade = " . $_SESSION['UnidadeId'] . " and InXSeInventario = " . $iInventario . " and SituaChave = 'ATIVO'
				 ORDER BY SetorNome ASC";
		$result = $conn->query($sql);
		$rowBDSetor = $result->fetchAll(PDO::FETCH_ASSOC);
		foreach ($rowBDSetor as $item) {
			$aSetores[] = $item['SetorId'];
		}

		//Equipe do inventário
		$sql = "SELECT UsuarId, UsuarLogin
				FROM Usuario
				JOIN EmpresaXUsuarioXPerfil ON EXUXPUsuario = UsuarId
				JOIN UsuarioXUnidade on UsXUnEmpresaUsuarioPerfil = EXUXPId
				JOIN InventarioXEquipe on InXEqUsuario = UsuarId
				JOIN Situacao on SituaId = EXUXPStatus
				WHERE UsXUnUnidade = " . $_SESSION['UnidadeId'] . " and InXEqInventario =  " . $iInventario . " and SituaChave = 'ATIVO'
				ORDER BY UsuarLogin ASC";
		$result = $conn->query($sql);
		$rowBDEquipe = $result->fetchAll(PDO::FETCH_ASSOC);

		$aEquipes = '';

		foreach ($rowBDEquipe as $item) {

			$aEquipe[] = $item['UsuarId'];

			$aEquipes .= $item['UsuarId'] . ",";
		}
		$aEquipes = substr($aEquipes, 0, -1); // retira a última vírgula

	} catch (PDOException $e) {
		echo 'Error: ' . $e->getMessage();
	}

	$_SESSION['msg'] = array();
} else {  //Esse else foi criado para se caso o usuário der um REFRESH na página. Nesse caso não terá POST e campos não reconhecerão o $row da consulta acima (daí ele deve ser redirecionado) e se quiser continuar editando terá que clicar no ícone da Grid novamente

	irpara("inventario.php");
}

if (isset($_POST['inputData'])) {

	try {

		$sql = "UPDATE Inventario SET InvenDataLimite = :dDataLimite, InvenClassificacao = :iClassificacao, InvenUnidade = :iUnidade, 
									  InvenCategoria = :iCategoria, InvenObservacao = :sObservacao, 
									  InvenUsuarioAtualizador = :iUsuarioAtualizador
				WHERE InvenId = :iInventario";
		$result = $conn->prepare($sql);

		$conn->beginTransaction();

		$result->execute(array(
			':dDataLimite' => gravaData($_POST['inputDataLimite']),
			':iClassificacao' => $_POST['cmbClassificacao'] == '#' ? null : $_POST['cmbClassificacao'],
			':iUnidade' => $_POST['cmbUnidade'] == '#' ? null : $_POST['cmbUnidade'],
			':iCategoria' => $_POST['cmbCategoria'] == '#' ? null : $_POST['cmbCategoria'],
			':sObservacao' => $_POST['txtObservacao'],
			':iUsuarioAtualizador' => $_SESSION['UsuarId'],
			':iInventario'	=> $_POST['inputInventarioId']
		));

		$sql = "DELETE FROM InventarioXLocalEstoque
				WHERE InXLEInventario = :iInventario";
		$result = $conn->prepare($sql);

		$result->execute(array(':iInventario' => $_POST['inputInventarioId']));

		if ($_POST['cmbLocalEstoque']) {

			$sql = "INSERT INTO InventarioXLocalEstoque 
						(InXLEInventario, InXLELocal)
					VALUES 
						(:iInventario, :iLocal)";
			$result = $conn->prepare($sql);

			foreach ($_POST['cmbLocalEstoque'] as $key => $value) {

				$result->execute(array(
					':iInventario' => $_POST['inputInventarioId'],
					':iLocal' => $value
				));
			}
		}

		$sql = "DELETE FROM InventarioXSetor
				WHERE InXSeInventario = :iInventario";
		$result = $conn->prepare($sql);

		$result->execute(array(':iInventario' => $_POST['inputInventarioId']));

		
		if (isset($_POST["cmbSetor"])) {
			$sql = "INSERT INTO InventarioXSetor 
						(InXSeInventario, InXSeSetor)
					VALUES 
						(:iInventario, :iSetor)";
			$result = $conn->prepare($sql);

			foreach ($_POST['cmbSetor'] as $key => $value) {

				$result->execute(array(
					':iInventario' => $_POST['inputInventarioId'],
					':iSetor' => $value
				));
			}
		}

		$sql = "DELETE FROM InventarioXEquipe
				WHERE InXEqInventario = :iInventario";
		$result = $conn->prepare($sql);

		$result->execute(array(':iInventario' => $_POST['inputInventarioId']));

		if ($_POST['cmbEquipe']) {

			$sql = "INSERT INTO InventarioXEquipe
						(InXEqInventario, InXEqUsuario, InXEqPresidente)
					VALUES 
						(:iInventario, :iUsuario, :bPresidente)";
			$result = $conn->prepare($sql);

			foreach ($_POST['cmbEquipe'] as $key => $value) {

				$result->execute(array(
					':iInventario' => $_POST['inputInventarioId'],
					':iUsuario' => $value,
					':bPresidente' => $value == $_POST['cmbPresidente'] ? 1 : 0
				));
			}
		}

		$conn->commit();

		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Inventário alterado!!!";
		$_SESSION['msg']['tipo'] = "success";
	} catch (PDOException $e) {

		$conn->rollback();

		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao alterar inventário!!!";
		$_SESSION['msg']['tipo'] = "error";

		echo 'Error: ' . $e->getMessage();
		exit;
	}

	irpara("inventario.php");
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Inventario</title>

	<?php include_once("head.php"); ?>

	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
	<script src="global_assets/js/demo_pages/form_select2.js"></script>

	<script src="global_assets/js/demo_pages/form_layouts.js"></script>
	<script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>
	<script src="global_assets/js/plugins/forms/selects/bootstrap_multiselect.js"></script>
	<script src="global_assets/js/demo_pages/form_multiselect.js"></script>

	<!-- Validação -->
	<script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
	<script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
	<script src="global_assets/js/demo_pages/form_validation.js"></script>
	<!-- /theme JS files -->

	<!-- Adicionando Javascript -->
	<script type="text/javascript">
		$(document).ready(function() {

			//Valida Registro Duplicado
			$("#enviar").on('click', function(e) {

				e.preventDefault();

				var cmbUnidade = $('#cmbUnidade').val();
				var cmbLocalEstoque = $('#cmbLocalEstoque').val();
				var cmbEquipe = $('#cmbEquipe').val();

				//Verifica se o campo só possui espaços em branco

				$("#formInventario").submit();

			}); // enviar

			//Ao mudar a categoria, filtra a subcategoria via ajax (retorno via JSON)
			$('#cmbUnidade').on('change', function(e) {

				FiltraLocalEstoque();

				var cmbUnidade = $('#cmbUnidade').val();

				if (cmbUnidade == '#') {
					ResetLocalEstoque();
				} else {

					$.getJSON('filtraLocalEstoque.php?idUnidade=' + cmbUnidade, function(dados) {

						var option = '';

						if (dados.length) {

							$.each(dados, function(i, obj) {
								option += '<option value="' + obj.LcEstId + '">' + obj.LcEstNome + '</option>';
							});

							$('#cmbLocalEstoque').html(option).show();
						} else {
							ResetLocalEstoque();
						}
					});
				}
			});

			//Ao mudar a Equipe, filtra o possível presidente via ajax (retorno via JSON)
			$('#cmbEquipe').on('change', function(e) {

				var cmbEquipe = $('#cmbEquipe').val();

				//Esse IF é para quando se exclui todos que estavam selecionados entrar no ELSE e limpar a combo do Presidente
				if (cmbEquipe != '') {

					$.getJSON('filtraPresidente.php?aEquipe=' + cmbEquipe, function(dados) {

						var option = '';

						if (dados.length) {

							$.each(dados, function(i, obj) {
								option += '<option value="' + obj.UsuarId + '">' + obj.UsuarLogin + '</option>';
							});

							$('#cmbPresidente').html(option).show();
						} else {
							ResetPresidente();
						}
					});
				} else {
					ResetPresidente();
				}
			});

		}); //document.ready

		function FiltraLocalEstoque() {
			$('#cmbLocalEstoque').empty().append('<option>Filtrando...</option>');
		}

		function ResetLocalEstoque() {
			$('#cmbLocalEstoque').empty().append('<option>Sem Local do Estoque</option>');
		}

		//Mostra o "Filtrando..." na combo Presidente da Comissão
		function FiltraPresidente() {
			$('#cmbPresidente').empty().append('<option>Filtrando...</option>');
		}

		function ResetPresidente() {
			$('#cmbPresidente').empty().append('<option value="#">Nenhum</option>');
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
				<div class="card">

					<form name="formInventario" id="formInventario" method="post" class="form-validate-jquery">
						<div class="card-header header-elements-inline">
							<h5 class="text-uppercase font-weight-bold">Editar Inventário Nº <?php echo formatarNumero($row['InvenNumero']); ?></h5>
						</div>

						<input type="hidden" id="inputInventarioId" name="inputInventarioId" value="<?php echo $row['InvenId']; ?>">
						<input type="hidden" id="inputInventarioNumero" name="inputInventarioNumero" value="<?php echo $row['InvenNumero']; ?>">

						<div class="card-body">

							<div class="row">
								<div class="col-lg-2">
									<div class="form-group">
										<label for="inputData">Data de Emissão</label>
										<input type="text" id="inputData" name="inputData" class="form-control" placeholder="Data de Emissão" value="<?php echo mostraData($row['InvenData']); ?>" readOnly>
									</div>
								</div>

								<div class="col-lg-2">
									<div class="form-group">
										<label for="inputNumero">Número</label>
										<input type="text" id="inputNumero" name="inputNumero" class="form-control" placeholder="Número" value="<?php echo formatarNumero($row['InvenNumero']); ?>" readOnly>
									</div>
								</div>

								<div class="col-lg-2">
									<div class="form-group">
										<label for="inputDataLimite">Data Limite</label>
										<input type="text" id="inputDataLimite" name="inputDataLimite" class="form-control" placeholder="Data Limite" value="<?php echo mostraData($row['InvenDataLimite']); ?>">
									</div>
								</div>

								<div class="col-lg-2" id="classificacao">
									<div class="form-group">
										<label for="cmbClassificacao">Classificação/Bens</label>
										<select id="cmbClassificacao" name="cmbClassificacao" class="form-control form-control-select2">
											<option value="#">Selecione</option>
											<?php
											$sql = "SELECT ClassId, ClassNome
													FROM Classificacao
													JOIN Situacao on SituaId = ClassStatus
													WHERE SituaChave = 'ATIVO'
													ORDER BY ClassNome ASC";
											$result = $conn->query($sql);
											$rowClassificacao = $result->fetchAll(PDO::FETCH_ASSOC);

											foreach ($rowClassificacao as $item) {
												$seleciona = $item['ClassId'] == $row['InvenClassificacao'] ? "selected" : "";
												print('<option value="' . $item['ClassId'] . '" ' . $seleciona . '>' . $item['ClassNome'] . '</option>');
											}

											?>
										</select>
									</div>
								</div>

								<div class="col-lg-4">
									<label for="cmbCategoria">Categoria</label>
									<select id="cmbCategoria" name="cmbCategoria" class="form-control form-control-select2">
										<option value="#">Selecione</option>
										<?php
										$sql = "SELECT CategId, CategNome
												FROM Categoria
												JOIN Situacao on SituaId = CategStatus
												WHERE CategEmpresa = ".$_SESSION['EmpreId']." and SituaChave = 'ATIVO'
												ORDER BY CategNome ASC";
										$result = $conn->query($sql);
										$rowCategoria = $result->fetchAll(PDO::FETCH_ASSOC);

										foreach ($rowCategoria as $item) {
											$seleciona = $item['CategId'] == $row['InvenCategoria'] ? "selected" : "";
											print('<option value="' . $item['CategId'] . '" ' . $seleciona . '>' . $item['CategNome'] . '</option>');
										}
										?>
									</select>
								</div>
							</div>

							<div class="row">
								<div class="col-lg-4">
									<label for="cmbUnidade">Unidade<span class="text-danger"> *</span></label>
									<select id="cmbUnidade" name="cmbUnidade" class="form-control form-control-select2" required>
										<?php
										$sql = "SELECT UsXUnUnidade, UnidaNome
												FROM EmpresaXUsuarioXPerfil
												JOIN UsuarioXUnidade on UsXUnEmpresaUsuarioPerfil = EXUXPId
												JOIN Unidade on UnidaId = UsXUnUnidade
												WHERE EXUXPUsuario = " . $_SESSION['UsuarId'] . " and UsXUnUnidade = " . $_SESSION['UnidadeId'] . "
											    ";
										$result = $conn->query($sql);
										$usuarioUnidade = $result->fetch(PDO::FETCH_ASSOC);

										print('<option value="' . $usuarioUnidade['UsXUnUnidade'] . '" selected>' . $usuarioUnidade['UnidaNome'] . '</option>');

										?>
									</select>
								</div>
								<div class="col-lg-4">
									<div class="form-group" style="border-bottom:1px solid #ddd;">
										<label for="cmbLocalEstoque">Locais do Estoque<span class="text-danger"> *</span></label>
										<select id="cmbLocalEstoque" name="cmbLocalEstoque[]" class="form-control multiselect" multiple="multiple" data-fouc required>
											<?php
											$sql = "SELECT LcEstId, LcEstNome
													FROM LocalEstoque
													JOIN Situacao on SituaId = LcEstStatus
													WHERE LcEstUnidade = " . $row['InvenUnidade'] . " and SituaChave = 'ATIVO'
													ORDER BY LcEstNome ASC";
											$result = $conn->query($sql);
											$rowLocal = $result->fetchAll(PDO::FETCH_ASSOC);
											$count = count($rowLocal);

											if ($count) {
												foreach ($rowLocal as $item) {
													$seleciona = in_array($item['LcEstId'], $aLocaisEstoque) ? "selected" : "";
													print('<option value="' . $item['LcEstId'] . '" ' . $seleciona . '>' . $item['LcEstNome'] . '</option>');
												}
											}
											?>
										</select>
									</div>
								</div>
								<div class="col-lg-4">
									<div class="form-group" style="border-bottom:1px solid #ddd;">
										<label for="cmbSetor">Setor<span class="text-danger"> *</span></label>
										<select id="cmbSetor" name="cmbSetor[]" class="form-control multiselect" multiple="multiple" required>
											<option value="">Todos</option>
											<?php
											$sql = "SELECT SetorId, SetorNome
													FROM Setor
													JOIN Situacao on SituaId = SetorStatus
													WHERE SetorUnidade = " . $_SESSION['UnidadeId'] . " and SituaChave = 'ATIVO'
													ORDER BY SetorNome ASC";
											$result = $conn->query($sql);
											$rowSetor = $result->fetchAll(PDO::FETCH_ASSOC);
											$count = count($rowSetor);

											if ($count) {
												foreach ($rowSetor as $item) {
													$seleciona = in_array($item['SetorId'], $aSetores) ? "selected" : "";
													print('<option value="' . $item['SetorId'] . '" '.$seleciona.'>' . $item['SetorNome'] . '</option>');
												}
											}

											?>
										</select>
									</div>
								</div>
							</div>
							<br>
<?php 
//foreach ($rowSetor as $key => $item) {
	//$seleciona = array_key_exists($item['SetorId'], $aSetores) ? "selected" : "";
	//$seleciona = array_key_exists($item['SetorId'], $aSetores) ? "selected" : "";
//	if(in_array                                                ($item['SetorId'], $aSetores)){
//		printf('existe');
//	}
//} 
?>
<?php ///var_dump($aSetores); ?>
							<h5 class="mb-0 font-weight-semibold">Comissão de Inventário</h5>
							<br>
							<div class="row">
								<div class="col-lg-9">
									<div class="form-group" style="border-bottom:1px solid #ddd;">
										<label for="cmbEquipe">Equipe Responsável<span class="text-danger"> *</span></label>
										<select id="cmbEquipe" name="cmbEquipe[]" class="form-control select" multiple="multiple" data-fouc required>
											<?php
											$sql = "SELECT UsuarId, UsuarLogin
													FROM Usuario
													JOIN EmpresaXUsuarioXPerfil ON EXUXPUsuario = UsuarId
													JOIN UsuarioXUnidade on UsXUnEmpresaUsuarioPerfil = EXUXPId
													JOIN Situacao on SituaId = EXUXPStatus
													WHERE UsXUnUnidade = " . $_SESSION['UnidadeId'] . " and SituaChave = 'ATIVO'
													ORDER BY UsuarLogin ASC";
											$result = $conn->query($sql);
											$rowEquipe = $result->fetchAll(PDO::FETCH_ASSOC);

											foreach ($rowEquipe as $item) {
												$seleciona = in_array($item['UsuarId'], $aEquipe) ? "selected" : "";
												print('<option value="' . $item['UsuarId'] . '" ' . $seleciona . '>' . $item['UsuarLogin'] . '</option>');
											}

											?>
										</select>
									</div>
								</div>

								<div class="col-lg-3">
									<div class="form-group" style="border-bottom:1px solid #ddd;">
										<label for="cmbPresidente">Presidente da Comissão</label>
										<select id="cmbPresidente" name="cmbPresidente" class="form-control form-control-select2">
											<?php
											$sql = "SELECT UsuarId, UsuarLogin
													FROM Usuario
													JOIN EmpresaXUsuarioXPerfil ON EXUXPUsuario = UsuarId
													JOIN UsuarioXUnidade on UsXUnEmpresaUsuarioPerfil = EXUXPId
													JOIN Situacao on SituaId = EXUXPStatus
													WHERE UsXUnUnidade = " . $_SESSION['UnidadeId'] . " and EXUXPUsuario in (" . $aEquipes . ") and SituaChave = 'ATIVO'";
											$result = $conn->query($sql);
											$rowPresidente = $result->fetchAll(PDO::FETCH_ASSOC);

											foreach ($rowPresidente as $item) {
												$seleciona = in_array($item['UsuarId'], $aEquipe) ? "selected" : "";
												print('<option value="' . $item['UsuarId'] . '" ' . $seleciona . '>' . $item['UsuarLogin'] . '</option>');
											}
											?>
										</select>
									</div>
								</div>
							</div>
							<br>

							<div class="row">
								<div class="col-lg-12">
									<h5 class="mb-0 font-weight-semibold">Dados do Solicitante</h5>
									<br>
									<div class="row">
										<div class="col-lg-6">
											<div class="form-group">
												<label for="inputNomeSolicitante">Solicitante</label>
												<input type="text" id="inputNomeSolicitante" name="inputNomeSolicitante" class="form-control" value="<?php echo $row['UsuarNome']; ?>" readOnly>
											</div>
										</div>

										<div class="col-lg-3">
											<div class="form-group">
												<label for="inputEmailSolicitante">E-mail</label>
												<input type="text" id="inputEmailSolicitante" name="inputEmailSolicitante" class="form-control" value="<?php echo $row['UsuarEmail']; ?>" readOnly>
											</div>
										</div>

										<div class="col-lg-3">
											<div class="form-group">
												<label for="inputTelefoneSolicitante">Telefone</label>
												<input type="text" id="inputTelefoneSolicitante" name="inputTelefoneSolicitante" class="form-control" value="<?php echo $row['UsuarTelefone']; ?>" readOnly>
											</div>
										</div>
									</div>

									<div class="row">
										<div class="col-lg-12">
											<div class="form-group">
												<label for="txtObservacao">Observação</label>
												<textarea rows="5" cols="5" class="form-control" id="txtObservacao" name="txtObservacao" placeholder="Observação"><?php echo $row['InvenObservacao']; ?></textarea>
											</div>
										</div>
									</div>
								</div>
							</div>

							<div class="row" style="margin-top: 40px;">
								<div class="col-lg-12">
									<div class="form-group">
										<?php
											if ($_POST['inputPermission']) {
												echo '<button class="btn btn-lg btn-principal" id="enviar">Alterar</button>';
											}
										?>	
										<a href="inventario.php" class="btn btn-basic" role="button">Cancelar</a>
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