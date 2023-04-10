<?php

include_once("sessao.php");

$_SESSION['PaginaAtual'] = 'Editar Orçamento';

include('global_assets/php/conexao.php');

//Se veio do orcamento.php
if (isset($_POST['inputOrcamentoId'])) {

	$iOrcamento = $_POST['inputOrcamentoId'];

	try {

		$sql = "SELECT OrcamId, OrcamNumero, OrcamTipo, OrcamData, OrcamCategoria, OrcamConteudo, OrcamFornecedor, 
					   ForneId, ForneNome, ForneContato, ForneEmail, ForneTelefone, ForneCelular, OrcamSolicitante, UsuarNome, UsuarEmail, UsuarTelefone
				FROM Orcamento
				JOIN Usuario on UsuarId = OrcamSolicitante
				LEFT JOIN Fornecedor on ForneId = OrcamFornecedor
				WHERE OrcamId = $iOrcamento ";
		$result = $conn->query($sql);
		$row = $result->fetch(PDO::FETCH_ASSOC);

		$sql = "SELECT SbCatId, SbCatNome
				FROM SubCategoria
				JOIN OrcamentoXSubCategoria on OrXSCSubCategoria = SbCatId
				WHERE SbCatEmpresa = " . $_SESSION['EmpreId'] . " and OrXSCOrcamento = $iOrcamento
				ORDER BY SbCatNome ASC";
		$result = $conn->query($sql);
		$rowBD = $result->fetchAll(PDO::FETCH_ASSOC);
		// Esta variável armazena em uma string os valores de Ids de subcategorias usadas pelo orcamento
		// para que seja transformadas em array no JS e comparada ao valor do select de subcategoria.
		$aSubCategoriasString = '';

		$tamanhoArray = count($rowBD);

		foreach ($rowBD as $key => $item) {
			$aSubCategorias[] = $item['SbCatId'];

			if (($tamanhoArray - 1) == $key) {
				$aSubCategoriasString .= $item['SbCatId'];
			} else {
				$aSubCategoriasString .= $item['SbCatId'] . ',';
			}
		}
	} catch (PDOException $e) {
		echo 'Error: ' . $e->getMessage();
	}

	$_SESSION['msg'] = array();
} else {  //Esse else foi criado para se caso o usuário der um REFRESH na página. Nesse caso não terá POST e campos não reconhecerão o $row da consulta acima (daí ele deve ser redirecionado) e se quiser continuar editando terá que clicar no ícone da Grid novamente

	irpara("orcamento.php");
}

if (isset($_POST['inputTipo'])) {

	try {

		$iOrcamento = $_POST['inputOrcamentoId'];

		$sql = "UPDATE Orcamento SET OrcamTipo = :sTipo, OrcamCategoria = :iCategoria, OrcamConteudo = :sConteudo,
									 OrcamFornecedor = :iFornecedor, OrcamUsuarioAtualizador = :iUsuarioAtualizador
				WHERE OrcamId = :iOrcamento";
		$result = $conn->prepare($sql);

		$conn->beginTransaction();

		$aFornecedor = explode("#", $_POST['cmbFornecedor']);
		$iFornecedor = $aFornecedor[0];

		$result->execute(array(
			':sTipo' => $_POST['inputTipo'],
			':iCategoria' => $_POST['cmbCategoria'] == '#' ? null : $_POST['cmbCategoria'],
			//':iSubCategoria' => $_POST['cmbSubCategoria'] == '#' ? null : $_POST['cmbSubCategoria'],
			':sConteudo' => $_POST['txtareaConteudo'],
			':iFornecedor' => $iFornecedor,
			':iUsuarioAtualizador' => $_SESSION['UsuarId'],
			':iOrcamento' => $iOrcamento
		));


		$sql = "DELETE FROM OrcamentoXSubCategoria
				WHERE OrXSCOrcamento = :iOrcamento and OrXSCUnidade = :iUnidade";
		$result = $conn->prepare($sql);

		$result->execute(array(
			':iOrcamento' => $_POST['inputOrcamentoId'],
			':iUnidade' => $_SESSION['UnidadeId']
		));


		if (isset($_POST['cmbSubCategoria'])) {

			try {
				$sql = "INSERT INTO OrcamentoXSubCategoria 
							(OrXSCOrcamento, OrXSCSubCategoria, OrXSCUnidade)
						VALUES 
							(:iOrcamento, :iSubCategoria, :iUnidade)";
				$result = $conn->prepare($sql);

				foreach ($_POST['cmbSubCategoria'] as $key => $value) {

					$result->execute(array(
						':iOrcamento' => $_POST['inputOrcamentoId'],
						':iSubCategoria' => $value,
						':iUnidade' => $_SESSION['UnidadeId']
					));
				}
			} catch (PDOException $e) {
				$conn->rollback();
				echo 'Error: ' . $e->getMessage();
				exit;
			}
		}

		if (isset($_POST['inputOrcamentoProdutoExclui']) and $_POST['inputOrcamentoProdutoExclui']) {

			$sql = "DELETE FROM OrcamentoXProduto
					WHERE OrXPrOrcamento = :iOrcamento and OrXPrUnidade = :iUnidade";
			$result = $conn->prepare($sql);

			$result->execute(array(
				':iOrcamento' => $iOrcamento,
				':iUnidade' => $_SESSION['UnidadeId']
			));
		}

		if (isset($_POST['inputOrcamentoServicoExclui']) and $_POST['inputOrcamentoServicoExclui']) {

			$sql = "DELETE FROM OrcamentoXServico
					WHERE OrXSrOrcamento = :iOrcamento and OrXSrUnidade = :iUnidade";
			$result = $conn->prepare($sql);

			$result->execute(array(
				':iOrcamento' => $iOrcamento,
				':iUnidade' => $_SESSION['UnidadeId']
			));
		}

		$conn->commit();

		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Orçamento alterado!!!";
		$_SESSION['msg']['tipo'] = "success";
	} catch (PDOException $e) {

		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao alterar orçamento!!!";
		$_SESSION['msg']['tipo'] = "error";

		$conn->rollback();

		echo 'Error: ' . $e->getMessage();
		exit;
	}

	irpara("orcamento.php");
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Orçamento</title>

	<?php include_once("head.php"); ?>

	<!-- JS file path -->
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
	<script src="global_assets/js/demo_pages/form_select2.js"></script>

	<script src="global_assets/js/demo_pages/form_layouts.js"></script>
	<script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>

	<script src="global_assets/js/plugins/editors/summernote/summernote.min.js"></script>

	<!-- Validação -->
	<script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
	<script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
	<script src="global_assets/js/demo_pages/form_validation.js"></script>

	<!-- Adicionando Javascript -->
	<script type="text/javascript">
		$(document).ready(function() {

			// Ao usar a validação de formulário junto com a seleção multipla de subcategorias ocorreu um erro, onde o aviso de capo obrigatório
			// crebava o layout do form-group da subcategoria, deixando a borda do elemento abaixo de se, o que causa um efeito estranho no visual.
			// O código a baixo corrige isso.
			/**/
			$('.select2-selection').css('border-bottom', '1px solid #ddd')
			$('.form-group').each((i, item) => {
				$(item).css('border-bottom', '0px')
			})
			/**/

			$('#summernote').summernote();

			//Ao informar o fornecedor, trazer os demais dados dele (contato, e-mail, telefone)
			$('#cmbFornecedor').on('change', function(e) {

				var Fornecedor = $('#cmbFornecedor').val();
				var Forne = Fornecedor.split('#');

				$('#inputContato').val(Forne[1]);
				$('#inputEmailFornecedor').val(Forne[2]);

				if (Forne[3] != "" && Forne[3] != "(__) ____-____") {
					$('#inputTelefoneFornecedor').val(Forne[3]);
				} else {
					$('#inputTelefoneFornecedor').val(Forne[4]);
				}
			});

			//Ao mudar a categoria, filtra a subcategoria e produto via ajax (retorno via JSON)
			$('#cmbCategoria').on('change', function(e) {

				Filtrando();

				var cmbCategoria = $('#cmbCategoria').val();

				$.getJSON('filtraSubCategoria.php?idCategoria=' + cmbCategoria, function(dados) {

					var option = '<option value="#">Selecione a SubCategoria</option>';

					if (dados.length) {

						$.each(dados, function(i, obj) {
							option += '<option value="' + obj.SbCatId + '">' + obj.SbCatNome + '</option>';
						});

						$('#cmbSubCategoria').html(option).show();
					} else {
						ResetSubCategoria();
					}
				});

				$.getJSON('filtraFornecedor.php?idCategoria=' + cmbCategoria, function(dados) {

					var option = '<option value="">Selecione o Fornecedor</option>';

					if (dados.length) {

						$.each(dados, function(i, obj) {
							option += '<option value="' + obj.ForneId + '#' + obj.ForneContato + '#' + obj.ForneEmail + '#' + obj.ForneTelefone + '#' + obj.ForneCelular + '">' + obj.ForneNome + '</option>';
						});

						$('#cmbFornecedor').html(option).show();
					} else {
						ResetFornecedor();
					}
				});

			});

			// Limpa os campos de fornecedor quando uma nova categoria é selecionada
			$('#cmbCategoria').on('change', function() {
				let inputContato = $('#inputContato')
				let inputEmailFornecedor = $('#inputEmailFornecedor')
				let inputTelefoneFornecedor = $('#inputTelefoneFornecedor')

				if (inputContato.val() || inputEmailFornecedor.val() || inputTelefoneFornecedor.val()) {
					inputContato.val('')
					inputEmailFornecedor.val('')
					inputTelefoneFornecedor.val('')
				}
			})

			$("#enviar").on('click', function(e) {

				e.preventDefault();

				//Antes
				var inputCategoria = $('#inputOrcamentoCategoria').val();
				var inputSubCategoria = $('#inputOrcamentoSubCategoria').val();
				if (inputSubCategoria == '' || inputSubCategoria == null) {
					inputSubCategoria = '#';
				}

				//Depois
				var cmbCategoria = $('#cmbCategoria').val();
				var cmbSubCategoria = $('#cmbSubCategoria').val().join(); // retorna uma string a partir do valor do select que é um array
				var cmbFornecedor = $('#cmbFornecedor').val();

				//Tem produto cadastrado para esse orçamento na tabela OrcamentoXProduto?
				var inputProduto = $('#inputOrcamentoProduto').val();
				var inputServico = $('#inputOrcamentoServico').val();

				//Exclui os produtos desse Orçamento?
				var inputExclui = $('#inputOrcamentoProdutoExclui').val();
				var inputExclui = $('#inputOrcamentoServicoExclui').val();

				//Aqui verifica primeiro se tem produtos preenchidos, porque do contrário deixa mudar

				if ((inputProduto > 0 || inputServico > 0)) {

					//Verifica se o a categoria ou subcategoria foi alterada

					if ((inputSubCategoria != cmbSubCategoria) || ($('#tipoAnteriorProdutoServico').val() != $('input[name="inputTipo"]:checked').val())) {
						console.log(inputSubCategoria)
						console.log(cmbSubCategoria)

						if (cmbCategoria == '' || cmbCategoria == '#') {
							alerta('Atenção', 'Informe a categoria!', 'error');
							$('#cmbCategoria').focus();
							return false;
						}

						if (cmbFornecedor == '' || cmbFornecedor == '#') {
							alerta('Atenção', 'Informe o Fornecedor!', 'error');
							$('#cmbFornecedor').focus();
							return false;
						}

						inputExclui = 1;
						$('#inputOrcamentoProdutoExclui').val(inputExclui);
						$('#inputOrcamentoServicoExclui').val(inputExclui);

						confirmaExclusao(document.formOrcamento, "Tem certeza que deseja alterar o orçamento? Existem produtos com quantidades ou valores lançados!", "orcamentoEdita.php");

					} else {
						inputExclui = 0;
						$('#inputOrcamentoProdutoExclui').val(inputExclui);
						$('#inputOrcamentoServicoExclui').val(inputExclui);
					}
				}

				if ($('#tipoAnteriorProdutoServico').val() != $('input[name="inputTipo"]:checked').val()) {
					
					inputExclui = 1;
					$('#inputOrcamentoProdutoExclui').val(inputExclui);
					$('#inputOrcamentoServicoExclui').val(inputExclui);

				} 
				

				$("#formOrcamento").submit();

			}); // enviar			
		}); //document.ready

		//Mostra o "Filtrando..." na combo SubCategoria
		function Filtrando() {
			$('#cmbSubCategoria').empty().append('<option value="">Filtrando...</option>');
		}

		function ResetSubCategoria() {
			$('#cmbSubCategoria').empty().append('<option value="">Sem Subcategoria</option>');
		}

		function ResetFornecedor() {
			$('#cmbFornecedor').empty().append('<option value="">Sem Fornecedor</option>');
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

					<form name="formOrcamento" id="formOrcamento" method="post" class="form-validate-jquery">
						<div class="card-header header-elements-inline">
							<h5 class="text-uppercase font-weight-bold">Editar Orçamento Nº "<?php echo $_POST['inputOrcamentoNumero']; ?>"</h5>
						</div>

						<input type="hidden" id="inputOrcamentoId" name="inputOrcamentoId" value="<?php echo $row['OrcamId']; ?>">
						<input type="hidden" id="inputOrcamentoNumero" name="inputOrcamentoNumero" value="<?php echo $row['OrcamNumero']; ?>">
						<input type="hidden" id="inputOrcamentoCategoria" name="inputOrcamentoCategoria" value="<?php echo $row['OrcamCategoria']; ?>">
						<input type="hidden" id="inputOrcamentoSubCategoria" name="inputOrcamentoSubCategoria" value="<?php echo $aSubCategoriasString; ?>">
						<input type="hidden" id="inputOrcamentoProdutoExclui" name="inputOrcamentoProdutoExclui" value="0">
						<input type="hidden" id="inputOrcamentoServicoExclui" name="inputOrcamentoServicoExclui" value="0">
						<input type="hidden" id="tipoAnteriorProdutoServico" name="tipoAnteriorProdutoServico" value="<?php echo $row['OrcamTipo'] ?>">

						<?php

						$sql = "SELECT OrXPrOrcamento
									FROM OrcamentoXProduto
									WHERE OrXPrOrcamento = " . $iOrcamento . " and OrXPrUnidade = " . $_SESSION['UnidadeId'];
						$result = $conn->query($sql);
						$rowProduto = $result->fetchAll(PDO::FETCH_ASSOC);
						$countProduto = count($rowProduto);

						print('<input type="hidden" id="inputOrcamentoProduto" name="inputOrcamentoProduto" value="' . $countProduto . '" >');
						?>

						<?php

						$sql = "SELECT OrXSrOrcamento
			                        FROM OrcamentoXServico
			                        WHERE OrXSrOrcamento = " . $iOrcamento . " and OrXSrUnidade = " . $_SESSION['UnidadeId'];
						$result = $conn->query($sql);
						$rowServico = $result->fetchAll(PDO::FETCH_ASSOC);
						$countServico = count($rowServico);

						print('<input type="hidden" id="inputOrcamentoServico" name="inputOrcamentoServico" value="' . $countServico . '" >');
						?>

						<div class="card-body">

							<div class="row">
								<div class="col-lg-12">
									<div class="row">

										<div class="col-lg-3">
											<div class="form-group">
												<div class="form-check form-check-inline">
													<label class="form-check-label">
														<input type="radio" id="inputTipo" value="P" name="inputTipo" class="form-input-styled" data-fouc <?php if ($row['OrcamTipo'] == 'P') echo "checked"; ?>>
														Produto
													</label>
												</div>
												<div class="form-check form-check-inline">
													<label class="form-check-label">
														<input type="radio" id="inputTipo" value="S" name="inputTipo" class="form-input-styled" data-fouc <?php if ($row['OrcamTipo'] == 'S') echo "checked"; ?>>
														Serviço
													</label>
												</div>
											</div>
										</div>

										<div class="col-lg-1">
											<div class="form-group">
												<label for="inputData">Data</label>
												<input type="text" id="inputData" name="inputData" class="form-control" value="<?php echo mostraData($row['OrcamData']); ?>" readOnly>
											</div>
										</div>

										<div class="col-lg-3">
											<div class="form-group">
												<label for="cmbCategoria">Categoria <span class="text-danger">*</span></label>
												<select id="cmbCategoria" name="cmbCategoria" class="form-control form-control-select2" required>
													<option value="">Selecione</option>
													<?php
													$sql = "SELECT CategId, CategNome
																FROM Categoria
																JOIN Situacao on SituaId = CategStatus											     
																WHERE CategEmpresa = ".$_SESSION['EmpreId']." and SituaChave = 'ATIVO'
															    ORDER BY CategNome ASC";
													$result = $conn->query($sql);
													$rowCategoria = $result->fetchAll(PDO::FETCH_ASSOC);

													foreach ($rowCategoria as $item) {
														$seleciona = $item['CategId'] == $row['OrcamCategoria'] ? "selected" : "";
														print('<option value="' . $item['CategId'] . '" ' . $seleciona . '>' . $item['CategNome'] . '</option>');
													}

													?>
												</select>
											</div>
										</div>

										<div class="col-lg-5">
											<div class="form-group" style="border-bottom:1px solid #ddd;">
												<label for="cmbSubCategoria">SubCategoria</label>
												<select id="cmbSubCategoria" name="cmbSubCategoria[]" class="form-control select" multiple="multiple" data-fouc>
													<!--<option value="#">Selecione uma subcategoria</option>-->
													<?php
													if (isset($row['OrcamCategoria'])) {
														$sql = "SELECT SbCatId, SbCatNome
																	FROM SubCategoria
																	JOIN Situacao on SituaId = SbCatStatus
																	WHERE SbCatEmpresa = " . $_SESSION['EmpreId'] . " and SbCatCategoria = " . $row['OrcamCategoria'] . " and SituaChave = 'ATIVO'
																	ORDER BY SbCatNome ASC";
														$result = $conn->query($sql);
														$rowSubCategoria = $result->fetchAll(PDO::FETCH_ASSOC);
														$count = count($rowSubCategoria);

														if ($count) {
															foreach ($rowSubCategoria as $item) {
																$seleciona = in_array($item['SbCatId'], $aSubCategorias) ? "selected" : "";
																print('<option value="' . $item['SbCatId'] . '" ' . $seleciona . '>' . $item['SbCatNome'] . '</option>');
															}
														}
													}
													?>
												</select>
											</div>
										</div>

									</div>
								</div>
							</div>

							<div class="row">
								<div class="col-lg-12">
									<div class="form-group">
										<label for="txtareaConteudo">Conteúdo personalizado</label>
										<textarea rows="5" cols="5" class="form-control" id="summernote" name="txtareaConteudo" placeholder="Corpo do orçamento (informe aqui o texto que você queira que apareça no orçamento)"><?php echo $row['OrcamConteudo']; ?></textarea>
									</div>
								</div>
							</div>
							<br>

							<div class="row">
								<div class="col-lg-12">
									<h5 class="mb-0 font-weight-semibold">Dados do Fornecedor</h5>
									<br>
									<div class="row">
										<div class="col-lg-4">
											<div class="form-group">
												<label for="cmbFornecedor">Fornecedor <span class="text-danger">*</span></label>
												<select id="cmbFornecedor" name="cmbFornecedor" class="form-control form-control-select2" required>
													<option value="">Selecione</option>
													<?php
													$sql = "SELECT ForneId, ForneNome, ForneContato, ForneEmail, ForneTelefone, ForneCelular
																FROM Fornecedor
																JOIN Situacao on SituaId = ForneStatus							     
																WHERE ForneEmpresa = " . $_SESSION['EmpreId'] . " and SituaChave = 'ATIVO' and ForneCategoria = " . $row['OrcamCategoria'] . "
															    ORDER BY ForneNome ASC";
													$result = $conn->query($sql);
													$fornecedores = $result->fetchAll(PDO::FETCH_ASSOC);

													foreach ($fornecedores as $fornecedor) {
														if (isset($row['OrcamFornecedor'])) {
															if ($fornecedor['ForneId'] == $row['OrcamFornecedor']) {
																print('<option selected value="' . $fornecedor['ForneId'] . '#' . $fornecedor['ForneContato'] . '#' . $fornecedor['ForneEmail'] . '#' . $fornecedor['ForneTelefone'] . '#' . $fornecedor['ForneCelular'] . '" selected>' . $fornecedor['ForneNome'] . '</option>');
															} else {
																print('<option value="' . $fornecedor['ForneId'] . '#' . $fornecedor['ForneContato'] . '#' . $fornecedor['ForneEmail'] . '#' . $fornecedor['ForneTelefone'] . '#' . $fornecedor['ForneCelular'] . '">' . $fornecedor['ForneNome'] . '</option>');
															}
														} else {
															print('<option value="' . $fornecedor['ForneId'] . '#' . $fornecedor['ForneContato'] . '#' . $fornecedor['ForneEmail'] . '#' . $fornecedor['ForneTelefone'] . '#' . $fornecedor['ForneCelular'] . '" >' . $fornecedor['ForneNome'] . '</option>');
														}
													};
													?>
												</select>
											</div>
										</div>

										<div class="col-lg-3">
											<div class="form-group">
												<label for="inputContato">Contato</label>
												<input type="text" id="inputContato" name="inputContato" class="form-control" value="<?php echo $row['ForneContato']; ?>" readOnly>
											</div>
										</div>

										<div class="col-lg-3">
											<div class="form-group">
												<label for="inputEmailFornecedor">E-mail</label>
												<input type="text" id="inputEmailFornecedor" name="inputEmailFornecedor" class="form-control" value="<?php echo $row['ForneEmail']; ?>" readOnly>
											</div>
										</div>

										<div class="col-lg-2">
											<div class="form-group">
												<label for="inputTelefoneFornecedor">Telefone</label>
												<input type="text" id="inputTelefoneFornecedor" name="inputTelefoneFornecedor" class="form-control" value="<?php echo $row['ForneTelefone']; ?>" readOnly>
											</div>
										</div>
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
												<label for="inputNomeSolicitante">Solicitante <span class="text-danger">*</span></label>
												<input type="text" id="inputNomeSolicitante" name="inputNomeSolicitante" class="form-control" value="<?php echo $row['UsuarNome']; ?>" readOnly required>
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
								</div>
							</div>

							<div class="row" style="margin-top: 10px;">
								<div class="col-lg-12">
									<div class="form-group">
									<?php
										if ($_POST['inputPermission']) {
										echo '<button class="btn btn-lg btn-principal"  id="enviar" type="submit">Alterar</button>';
									}
									?>	
										<a href="orcamento.php" class="btn btn-basic" role="button">Cancelar</a>
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