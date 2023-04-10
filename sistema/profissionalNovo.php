<?php
include_once("sessao.php");
$_SESSION['PaginaAtual'] = 'Novo Profissional';
include('global_assets/php/conexao.php');
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Profissional</title>

	<?php include_once("head.php"); ?>
	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
	<script src="global_assets/js/demo_pages/form_select2.js"></script>
	<script src="global_assets/js/plugins/forms/selects/bootstrap_multiselect.js"></script>
	<script src="global_assets/js/demo_pages/form_multiselect.js"></script>

	<script src="global_assets/js/demo_pages/form_layouts.js"></script>
	<script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>

	<script src="global_assets/js/plugins/forms/inputs/inputmask.js"></script>
	<!-- /theme JS files -->

	<!-- Validação -->
	<script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
	<script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
	<script src="global_assets/js/demo_pages/form_validation.js"></script>

	<!-- Adicionando Javascript -->
	<script type="text/javascript">

		$(document).ready(function () {
			selecionaPessoa('PF');
			//$("#cmbEstado").addClass("form-control-select2");

			function limpa_formulário_cep() {
				// Limpa valores do formulário de cep.
				$("#inputEndereco").val("");
				$("#inputBairro").val("");
				$("#inputCidade").val("");
				$("#cmbEstado").val("");
				$("#inputNumero").val("");
				$("#inputComplemento").val("");
			}

			//Quando o campo cep perde o foco.
			$("#inputCep").blur(function () {

				//$("#cmbEstado").removeClass("form-control-select2");

				//Nova variável "cep" somente com dígitos.
				var cep = $(this).val().replace(/\D/g, '');

				//Verifica se campo cep possui valor informado.
				if (cep != "") {

					//Expressão regular para validar o CEP.
					var validacep = /^[0-9]{8}$/;

					//Valida o formato do CEP.
					if (validacep.test(cep)) {

						//Preenche os campos com "..." enquanto consulta webservice.
						$("#inputEndereco").val("...");
						$("#inputBairro").val("...");
						$("#inputCidade").val("...");
						$("#cmbEstado").val("...");

						//Consulta o webservice viacep.com.br/
						$.getJSON("https://viacep.com.br/ws/" + cep + "/json/?callback=?", function (dados) {

							if (!("erro" in dados)) {

								//Atualiza os campos com os valores da consulta.
								$("#inputEndereco").val(dados.logradouro);
								$("#inputBairro").val(dados.bairro);
								$("#inputCidade").val(dados.localidade);
								$("#cmbEstado").val(dados.uf);
								//$("#cmbEstado").find('option[value="MA"]').attr('selected','selected');
								//$('#cmbEstado :selected').text();
								//$("#cmbEstado").find('option:selected').text();
								//document.getElementById("cmbEstado").options[5].selected = true;
							} //end if.
							else {
								//CEP pesquisado não foi encontrado.
								limpa_formulário_cep();
								alerta("Erro", "CEP não encontrado.", "erro");
							}
						});
					} //end if.
					else {
						//cep é inválido.
						$("#inputCep").val("");
						limpa_formulário_cep();
						alerta("Erro", "Formato de CEP inválido.", "erro");
					}
				} //end if.
				else {
					//cep sem valor, limpa formulário.
					limpa_formulário_cep();
				}
			});
			//Valida Registro Duplicado
			$('#enviar').on('click', function (e) {
				e.preventDefault();

				// subistitui qualquer espaço em branco no campo "CEP" antes de enviar para o banco
				var cep = $("#inputCep").val()
				cep = cep.replace(' ', '')
				$("#inputCep").val(cep)

				var inputTipo = $('input[name="inputTipo"]:checked').val();
				var inputNome = "";
				var inputNomePF = $('#inputNomePF').val();
				var inputNomePJ = $('#inputNomePJ').val();
				var inputCpf = $('#inputCpf').val().replace(/[^\d]+/g, '');
				var inputCnpj = $('#inputCnpj').val().replace(/[^\d]+/g, '');
				var cmbUsuario = $('#cmbUsuario').val();


				if (inputTipo == 'F') {
					inputNome = inputNomePF;

					if (inputCpf.trim() == '') {
						$('#inputCpf').val('');
					} else {
						if (inputNome != '' && !validaCPF(inputCpf)) {
							$('#inputCpf').val('');
							alerta('Atenção', 'CPF inválido!', 'error');
							$('#inputCpf').focus();
							return false;
						}
					}
				} else {
					inputNome = inputNomePJ;

					if (inputCnpj.trim() == '') {
						$('#inputCnpj').val('');
					} else {
						if (inputNome != '' && !validarCNPJ(inputCnpj)) {
							$('#inputCnpj').val('');
							alerta('Atenção', 'CNPJ inválido!', 'error');
							$('#inputCnpj').focus();
							return false;
						}
					}
				}

				//remove os espaços desnecessários antes e depois
				inputNome = inputNome.trim();

				//Esse ajax está sendo usado para verificar se o usuário já é vinculado a um profissional 
				$.ajax({
					type: "POST",
					url: "profissionalUsuarioValida.php",
					data: ('nomeNovo=' + cmbUsuario),
					success: function (resposta) {

						if (resposta == 1) {
							alerta('Atenção', 'Esse usuário já está vinculado a outro profissional!', 'error');
							return false;
						} else {
							//Esse ajax está sendo usado para verificar no banco se o registro já existe
							$.ajax({
								type: "POST",
								url: "profissionalValida.php",
								data: { tipo: inputTipo, nome: inputNome, cpf: inputCpf, cnpj: inputCnpj },
								success: function (resposta) {

									if (resposta == 1) {
										alerta('Atenção', 'Esse funcionário já possui cadastro no sistema!', 'error');
										return false;
									}
									$('#formProfissional').submit();
								}
							}); //ajax
						}
					},
					error: function (response) {
						alerta('Erro', 'Erro ao salvar o profissional.', 'error');
						return false;
					}
				})

			}); // enviar

			$('#cmbUsuario').on('change', function () {

				let iUsuario = $(this).val();

				if (iUsuario) {

					$.ajax({
						type: 'POST',
						url: 'filtraProfissional.php',
						dataType: 'json',
						data: {
							'tipoRequest': 'BUSCARDADOSUSUARIO',
							'iUsuario': iUsuario
						},
						success: function (response) {

							if (response.status == 'success') {

								$('#inputNomePF').val(response.data.UsuarNome);
								$('#inputCpf').val((response.data.UsuarCpf).replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, "$1.$2.$3-$4"));
								$('#inputNomeContato').val(response.data.UsuarNome);
								$('#inputTelefone').val((response.data.UsuarTelefone).replace(/^(\d{2})(\d)/g, "($1) $2").replace(/(\d)(\d{4})$/, "$1-$2"));
								$('#inputCelular').val((response.data.UsuarCelular).replace(/^(\d{2})(\d)/g, "($1) $2").replace(/(\d)(\d{4})$/, "$1-$2"));
								$('#inputEmail').val(response.data.UsuarEmail);

							}

						}
					})

				}

			})

		}); //document.ready

		function selecionaPessoa(tipo) {
			var camposPFObrigatorios = [
				'inputNomePF',
				'inputCpf',
				'inputRg',
				'inputEmissor',
				'cmbUf',
				'cmbSexo',
				'inputDtNascimento',
				'cmbProfissaoPF',
				'cmbConselho',
				'inputNumConselho',
				'cmbEspecialidade',
				'cmbProfissaoPJ'
			]
			var camposPJObrigatorios = [
				'inputNomePJ',
				'inputCnpj'
			]
			if (tipo == 'PF') {
				camposPFObrigatorios.forEach(element => $("#" + element).attr('required', true));
				camposPJObrigatorios.forEach(element => $("#" + element).attr('required', false));

				document.getElementById('dadosPF').style.display = "block";
				document.getElementById('dadosPJ').style.display = "none";

			} else {
				document.getElementById('dadosPF').style.display = "none";
				document.getElementById('dadosPJ').style.display = "block";

				camposPJObrigatorios.forEach(element => $("#" + element).attr('required', true));
				camposPFObrigatorios.forEach(element => $("#" + element).attr('required', false));
			}
		}

		function validaDataNascimento(dataASerValidada) {
			//Adicionado um espaço para forçar o fuso horário de brasília		
			let dataObj = new Date(dataASerValidada + " ");
			let hoje = new Date();
			if ((hoje - dataObj) < 0) {
				return false;
			}
			else {
				return true;
			}
		}

		function formataCampoDataNascimento() {
			let dataPreenchida = $('#inputDtNascimento').val();
			if (!validaDataNascimento(dataPreenchida)) {
				let labelErro = $('#inputDtNascimento-error')
				labelErro.removeClass('validation-valid-label');
				labelErro[0].innerHTML = "Data não pode ser futura";
				$('#inputDtNascimento').val("");
			}
		}
	</script>

</head>

<body class="navbar-top sidebar-xs">

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

					<form name="formProfissional" action="profissionalNovoFinalizaTransacao.php" id="formProfissional"
						method="post" class="form-validate-jquery">
						<div class="card-header header-elements-inline">
							<h5 class="text-uppercase font-weight-bold">Cadastrar Novo Profissional</h5>
						</div>

						<div class="card-body">
							<div class="row">
								<div class="col-lg-4">
									<div class="form-group">
										<div class="form-check form-check-inline">
											<label class="form-check-label">
												<input type="radio" id="inputTipo" name="inputTipo" value="F"
													class="form-input-styled" data-fouc onclick="selecionaPessoa('PF')"
													checked>
												Pessoa Física
											</label>
										</div>
										<div class="form-check form-check-inline">
											<label class="form-check-label">
												<input type="radio" id="inputTipo" name="inputTipo" value="J"
													class="form-input-styled" data-fouc onclick="selecionaPessoa('PJ')">
												Pessoa Jurídica
											</label>
										</div>
									</div>
								</div>
								<div class="col-lg-4 ">
								</div>
								<div class="col-lg-2 float-end">
									<div class="form-group ">
										<label for="cmbUsuario">Usuário <span class="text-danger">*</span></label>
										<select id="cmbUsuario" name="cmbUsuario" class="form-control select-search"
											required>
											<option value="">Selecione o usuário referente ao profissional</option>
											<?php
											$sql = "SELECT UsuarId, UsuarNome
														FROM Usuario
														JOIN EmpresaXUsuarioXPerfil ON EXUXPUsuario = UsuarId
														JOIN UsuarioXUnidade on UsXUnEmpresaUsuarioPerfil = EXUXPId
														JOIN Situacao on SituaId = EXUXPStatus
														WHERE UsXUnUnidade = " . $_SESSION['UnidadeId'] . " AND SituaChave = 'ATIVO'
														ORDER BY UsuarNome ASC";
											$result = $conn->query($sql);
											$rowUsuario = $result->fetchAll(PDO::FETCH_ASSOC);

											foreach ($rowUsuario as $item) {
												print('<option value="' . $item['UsuarId'] . '">' . $item['UsuarNome'] . '</option>');
											}

											?>
										</select>
									</div>
								</div>
							</div>

							<h5 class="mb-0 font-weight-semibold">Dados Pessoais</h5>
							<br>
							<div class="row">
								<div class="col-lg-12">
									<div id="dadosPF">
										<div class="row">
											<div class="col-lg-3">
												<div class="form-group">
													<label for="inputCodigoPF">Código<span class="text-danger">
															*</span></label>
													<input type="text" id="inputNomePF" name="inputNomePF"
														class="form-control" placeholder="Código" required autofocus>
												</div>
											</div>

											<div class="col-lg-3">
												<div class="form-group">
													<label for="inputNomePF">Nome<span class="text-danger">
															*</span></label>
													<input type="text" id="inputNomePF" name="inputNomePF"
														class="form-control" placeholder="Nome Completo" required
														autofocus>
												</div>
											</div>

											<div class="col-lg-3">
												<div class="form-group">
													<label for="inputCpf">CPF<span class="text-danger"> *</span></label>
													<input type="text" id="inputCpf" name="inputCpf"
														class="form-control" data-mask="999.999.999-99" required>
												</div>
											</div>
											<div class="col-lg-3">
												<div class="form-group">
													<label for="inputCns">CNS</label>
													<input type="text" id="inputCns" name="inputCns"
														class="form-control" placeholder="CNS">
												</div>
											</div>
										</div>

										<div class="row">
											<div class="col-lg-2">
												<div class="form-group">
													<label for="inputRg">RG<span class="text-danger"> *</span></label>
													<input type="text" id="inputRg" name="inputRg" class="form-control"
														placeholder="RG">
												</div>
											</div>

											<div class="col-lg-2">
												<div class="form-group">
													<label for="inputEmissor">Emissor<span class="text-danger">
															*</span></label>
													<input type="text" id="inputEmissor" name="inputEmissor"
														class="form-control" placeholder="Órgão Emissor">
												</div>
											</div>

											<div class="col-lg-1">
												<div class="form-group">
													<label for="cmbUf">UF<span class="text-danger"> *</span></label>
													<select id="cmbUf" name="cmbUf"
														class="form-control form-control-select2">
														<option value="">Selecione um estado</option>
														<option value="AC">Acre</option>
														<option value="AL">Alagoas</option>
														<option value="AP">Amapá</option>
														<option value="AM">Amazonas</option>
														<option value="BA">Bahia</option>
														<option value="CE">Ceará</option>
														<option value="DF">Distrito Federal</option>
														<option value="ES">Espírito Santo</option>
														<option value="GO">Goiás</option>
														<option value="MA">Maranhão</option>
														<option value="MT">Mato Grosso</option>
														<option value="MS">Mato Grosso do Sul</option>
														<option value="MG">Minas Gerais</option>
														<option value="PA">Pará</option>
														<option value="PB">Paraíba</option>
														<option value="PR">Paraná</option>
														<option value="PE">Pernambuco</option>
														<option value="PI">Piauí</option>
														<option value="RJ">Rio de Janeiro</option>
														<option value="RN">Rio Grande do Norte</option>
														<option value="RS">Rio Grande do Sul</option>
														<option value="RO">Rondônia</option>
														<option value="RR">Roraima</option>
														<option value="SC">Santa Catarina</option>
														<option value="SP">São Paulo</option>
														<option value="SE">Sergipe</option>
														<option value="TO">Tocantins</option>
														<option value="ES">Estrangeiro</option>
													</select>
												</div>
											</div>

											<div class="col-lg-2">
												<div class="form-group">
													<label for="inputDtRg">Data Emissão RG<span class="text-danger">
															*</span></label>
													<input type="date" id="inputDtRg" name="inputDtRg"
														class="form-control" placeholder="Órgão Emissor">
												</div>
											</div>

											<div class="col-lg-2">
												<div class="form-group">
													<label for="cmbSexo">Sexo<span class="text-danger"> *</span></label>
													<select id="cmbSexo" name="cmbSexo"
														class="form-control form-control-select2">
														<option value="">Selecione o sexo</option>
														<option value="F">Feminino</option>
														<option value="M">Masculino</option>
													</select>
												</div>
											</div>

											<div class="col-lg-3">
												<div class="form-group">
													<label for="inputDtNascimento">Data Nascimento<span
															class="text-danger"> *</span></label>
													<input type="date" id="inputDtNascimento" name="inputDtNascimento"
														class="form-control" placeholder="Data Nascimento"
														onblur="formataCampoDataNascimento()">
												</div>
											</div>

											<div class="col-lg-2">
												<div class="form-group">
													<label for="cmbSexo">Raça/Cor<span class="text-danger">
															*</span></label>
													<select id="cmbSexo" name="cmbSexo"
														class="form-control form-control-select2">
														<option value="">Selecione o sexo</option>
														<option value="F">Branco</option>
														<option value="M">Preto</option>
														<option value="M">Pardo</option>
														<option value="M">Amarelo</option>
														<option value="M">Indígena</option>
													</select>
												</div>
											</div>

											<div class="col-lg-5">
												<div class="form-group">
													<label for="inputPai">Nome Pai<span class="text-danger">
														</span></label>
													<input type="text" id="inputPai" name="inputPai"
														class="form-control" placeholder="Nome Pai">
												</div>
											</div>

											<div class="col-lg-5">
												<div class="form-group">
													<label for="inputEmissor">Nome Mãe<span class="text-danger">
														</span></label>
													<input type="text" id="inputMae" name="inputMae"
														class="form-control" placeholder="Nome Mãe">
												</div>
											</div>

											<div class="col-lg-5">
												<div class="form-group">
													<label for="inputNaturalidade">Naturalidade<span
															class="text-danger"> </span></label>
													<input type="text" id="inputNaturalidade" name="inputNaturalidade"
														class="form-control" placeholder="Naturalidade">
												</div>
											</div>

											<div class="col-lg-3">
												<div class="form-group">
													<label for="cmbEstadoCivil">Estado Civil<span class="text-danger">
															*</span></label>
													<select id="cmbEstadoCivil" name="cmbEstadoCivil"
														class="form-control form-control-select2">
														<option value="">Selecione o sexo</option>
														<option value="">solteiro</option>
														<option value="">casado</option>
														<option value="">separado</option>
														<option value="">divorciado</option>
														<option value="">viúvo</option>
													</select>
												</div>
											</div>

											<div class="col-lg-4">
												<div class="form-group">
													<label for="inputEscolaridade">Escolaridade<span
															class="text-danger"> </span></label>
													<input type="text" id="inputEscolaridade" name="inputEscolaridade"
														class="form-control" placeholder="Escolaridade">
												</div>
											</div>

											<div class="col-lg-4">
												<div class="form-group">
													<label for="inputEleitor">Titulo de Eleitor<span
															class="text-danger"> </span></label>
													<input type="text" id="inputEleitor" name="inputEleitor"
														class="form-control" placeholder="Titulo de Eleitor">
												</div>
											</div>

											<div class="col-lg-4">
												<div class="form-group">
													<label for="inputZona">Zona<span class="text-danger">
														</span></label>
													<input type="text" id="inputZona" name="inputZona"
														class="form-control" placeholder="Zona">
												</div>
											</div>

											<div class="col-lg-4">
												<div class="form-group">
													<label for="inputZona">Seção<span class="text-danger">
														</span></label>
													<input type="text" id="inputSecao" name="inputSecao"
														class="form-control" placeholder="Seção">
												</div>
											</div>

										</div>
										<br>
										<div class="row">
											<div class="col-lg-12">
												<h5 class="mb-0 font-weight-semibold">Dados Profissionais</h5>
												<br>
												<div class="row">

													<div class="col-lg-2">
														<label for="cmbProfissaoPF">Ocupação CBO<span
																class="text-danger">
																*</span></label>
														<select id="cmbProfissaoPF" name="cmbProfissaoPF"
															class="form-control select-search" required>
															<option value="">Seleciona uma profissão</option>
															<?php
															$sql = "SELECT ProfiId, ProfiNome
																	FROM Profissao
																	JOIN Situacao on SituaId = ProfiStatus
																	WHERE SituaChave = 'ATIVO'
																	ORDER BY ProfiNome ASC";
															$result = $conn->query($sql);
															$row = $result->fetchAll(PDO::FETCH_ASSOC);

															foreach ($row as $item) {
																print('<option value="' . $item['ProfiId'] . '">' . $item['ProfiNome'] . '</option>');
															}
															?>
														</select>
													</div>

													<div class="col-lg-1">
														<label for="cmbConselho">Conselho<span class="text-danger">
																*</span></label>
														<select id="cmbConselho" name="cmbConselho"
															class="form-control select-search">
															<option value="">Selecione </option>
															<?php
															$sql = "SELECT PrConId, PrConNome
																	FROM ProfissionalConselho
																	JOIN Situacao on SituaId = PrConStatus
																	WHERE SituaChave = 'ATIVO'
																	ORDER BY PrConNome ASC";
															$result = $conn->query($sql);
															$row = $result->fetchAll(PDO::FETCH_ASSOC);

															foreach ($row as $item) {
																print('<option value="' . $item['PrConId'] . '">' . $item['PrConNome'] . '</option>');
															}
															?>

														</select>
													</div>

													<div class="col-lg-2">
														<div class="form-group">
															<label for="inputNumConselho">Nº do Conselho/UF<span
																	class="text-danger"> *</span></label>
															<input type="text" id="inputNumConselho"
																name="inputNumConselho" class="form-control"
																placeholder="CRM/Outros">
														</div>
													</div>

													<div class="col-lg-2">
														<div class="form-group">
															<label for="inputCnesPF">CNES<span class="text-danger">
																	*</span></label>
															<input type="text" id="inputCnesPF" name="inputCnesPF"
																class="form-control" placeholder="CNES" maxlength="8">
														</div>
													</div>
													<div class="col-lg-2">
														<div class="form-group">
															<label for="inputCtps">CTPS</label>
															<input type="text" id="inputCtps" name="inputCtps"
																class="form-control" placeholder="CTPS">
														</div>
													</div>

													<div class="col-lg-3">
														<label for="cmbEspecialidade">Especialidades<span
																class="text-danger"> *</span></label>
														<select id="cmbEspecialidade" name="cmbEspecialidade[]"
															class="form-control multiselect-filtering"
															multiple="multiple">

															<?php
															$sql = "SELECT EspecId, EspecNome
                                                                    FROM Especialidade
                                                                    JOIN Situacao on SituaId = EspecStatus
                                                                    WHERE EspecUnidade = " . $_SESSION['UnidadeId'] . " and SituaChave = 'ATIVO'
                                                                    ORDER BY EspecNome ASC";
															$result = $conn->query($sql);
															$row = $result->fetchAll(PDO::FETCH_ASSOC);

															foreach ($row as $item) {
																print('<option value="' . $item['EspecId'] . '">' . $item['EspecNome'] . '</option>');
															}
															?>
														</select>
													</div>
												</div>
											</div>
										</div>
									</div>

									<!-- Fim dadosPF -->

									<div id="dadosPJ" style="display:none">

										<div class="row">
											<div class="col-lg-2">
												<div class="form-group">
													<label for="inputCodigo">Código<span class="text-danger">
															*</span></label>
													<input type="text" id="inputNomePJ" name="inputNomePJ"
														class="form-control" placeholder="Nome Fantasia">
												</div>
											</div>
											<div class="col-lg-6">
												<div class="form-group">
													<label for="inputNomePJ">Nome Fantasia<span class="text-danger">
															*</span></label>
													<input type="text" id="inputNomePJ" name="inputNomePJ"
														class="form-control" placeholder="Nome Fantasia">
												</div>
											</div>

											<div class="col-lg-4">
												<div class="form-group">
													<label for="inputCnpj">CNPJ<span class="text-danger">
															*</span></label>
													<input type="text" id="inputCnpj" name="inputCnpj"
														class="form-control" data-mask="99.999.999/9999-99">
												</div>
											</div>
										</div>


										<div class="row">
											<div class="col-lg-4">
												<div class="form-group">
													<label for="inputRazaoSocial">Razão Social</label>
													<input type="text" id="inputRazaoSocial" name="inputRazaoSocial"
														class="form-control" placeholder="Razão Social">
												</div>
											</div>

											<div class="col-lg-2">
												<div class="form-group">
													<label for="inputInscricaoMunicipal">Inscrição Municipal</label>
													<input type="text" id="inputInscricaoMunicipal"
														name="inputInscricaoMunicipal" class="form-control"
														placeholder="Inscrição Municipal">
												</div>
											</div>

											<div class="col-lg-2">
												<div class="form-group">
													<label for="inputInscricaoEstadual">Inscrição Estadual</label>
													<input type="text" id="inputInscricaoEstadual"
														name="inputInscricaoEstadual" class="form-control"
														placeholder="Inscrição Estadual">
												</div>
											</div>
											<div class="col-lg-2">
												<div class="form-group">
													<label for="inputCnesPJ">CNES</label>
													<input type="text" id="inputCnesPJ" name="inputCnesPJ"
														class="form-control" placeholder="CNES" maxlength="8">
												</div>
											</div>
											<div class="col-lg-2">
												<label for="cmbProfissaoPJ">Profissão<span class="text-danger">
														*</span></label>
												<select id="cmbProfissaoPJ" name="cmbProfissaoPJ"
													class="form-control select-search" required>
													<option value="">Seleciona uma profissão</option>
													<?php
													$sql = "SELECT ProfiId, ProfiNome
															FROM Profissao
															JOIN Situacao on SituaId = ProfiStatus
															WHERE SituaChave = 'ATIVO'
															ORDER BY ProfiNome ASC";
													$result = $conn->query($sql);
													$row = $result->fetchAll(PDO::FETCH_ASSOC);

													foreach ($row as $item) {
														print('<option value="' . $item['ProfiId'] . '">' . $item['ProfiNome'] . '</option>');
													}
													?>
												</select>
											</div>
										</div>
									</div> <!-- Fim dadosPJ -->
								</div>
							</div>
							<br>

							<div class="row">
								<div class="col-lg-12">
									<h5 class="mb-0 font-weight-semibold">Endereço</h5>
									<br>
									<div class="row">
										<div class="col-lg-3">
											<div class="form-group">
												<label for="inputCep">CEP</label>
												<input type="text" id="inputCep" name="inputCep" class="form-control"
													placeholder="CEP" maxLength="8">
											</div>
										</div>

										<div class="col-lg-3">
											<div class="form-group">
												<label for="inputCep">Logradouro</label>
												<input type="text" id="inputLogradouro" name="inputLogradouro"
													class="form-control" placeholder="CEP" maxLength="8">
											</div>
										</div>

										<div class="col-lg-3">
											<div class="form-group">
												<label for="inputNumero">Nº</label>
												<input type="text" id="inputNumero" name="inputNumero"
													class="form-control" placeholder="Número">
											</div>
										</div>

										<div class="col-lg-3">
											<div class="form-group">
												<label for="inputComplemento">Complemento</label>
												<input type="text" id="inputComplemento" name="inputComplemento"
													class="form-control" placeholder="complemento">
											</div>
										</div>

										<div class="col-lg-2">
											<div class="form-group">
												<label for="inputBairro">Bairro</label>
												<input type="text" id="inputBairro" name="inputBairro"
													class="form-control" placeholder="Bairro">
											</div>
										</div>

										<div class="col-lg-4">
											<div class="form-group">
												<label for="inputCidade">Cidade</label>
												<input type="text" id="inputCidade" name="inputCidade"
													class="form-control" placeholder="Cidade">
											</div>
										</div>

										<div class="col-lg-2">
											<div class="form-group">
												<label for="cmbEstado">Estado</label>
												<select id="cmbEstado" name="cmbEstado" class="form-control">
													<!-- retirei isso da class: form-control-select2 para que funcionasse a seleção do texto do estado, além do valor -->
													<option value="#">Selecione um estado</option>
													<option value="AC">Acre</option>
													<option value="AL">Alagoas</option>
													<option value="AP">Amapá</option>
													<option value="AM">Amazonas</option>
													<option value="BA">Bahia</option>
													<option value="CE">Ceará</option>
													<option value="DF">Distrito Federal</option>
													<option value="ES">Espírito Santo</option>
													<option value="GO">Goiás</option>
													<option value="MA">Maranhão</option>
													<option value="MT">Mato Grosso</option>
													<option value="MS">Mato Grosso do Sul</option>
													<option value="MG">Minas Gerais</option>
													<option value="PA">Pará</option>
													<option value="PB">Paraíba</option>
													<option value="PR">Paraná</option>
													<option value="PE">Pernambuco</option>
													<option value="PI">Piauí</option>
													<option value="RJ">Rio de Janeiro</option>
													<option value="RN">Rio Grande do Norte</option>
													<option value="RS">Rio Grande do Sul</option>
													<option value="RO">Rondônia</option>
													<option value="RR">Roraima</option>
													<option value="SC">Santa Catarina</option>
													<option value="SP">São Paulo</option>
													<option value="SE">Sergipe</option>
													<option value="TO">Tocantins</option>
													<option value="ES">Estrangeiro</option>
												</select>
											</div>
										</div>

										<div class="col-lg-4">
											<div class="form-group">
												<label for="cmbEstadoCivil">Tipo Logradouro<span class="text-danger">
														*</span></label>
												<select id="cmbEstadoCivil" name="cmbEstadoCivil"
													class="form-control form-control-select2">
													<option value="">Selecione</option>
												</select>
											</div>
										</div>
									</div>
								</div>
								<br>

								<div class="row">
									<div class="col-lg-12">
										<h5 class="mb-0 font-weight-semibold">Contato</h5>
										<br>
										<div class="row">
											<div class="col-lg-4">
												<div class="form-group">
													<label for="inputNomeContato">Nome</label>
													<input type="text" id="inputNomeContato" name="inputNomeContato"
														class="form-control" placeholder="Contato">
												</div>
											</div>

											<div class="col-lg-2">
												<div class="form-group">
													<label for="inputTelefone">Telefone</label>
													<input type="tel" id="inputTelefone" name="inputTelefone"
														class="form-control" placeholder="Res. ?Com."
														data-mask="(99) 9999-9999">
												</div>
											</div>

											<div class="col-lg-2">
												<div class="form-group">
													<label for="inputCelular">Celular<span class="text-danger">
															*</span></label>
													<input type="tel" id="inputCelular" name="inputCelular"
														class="form-control" placeholder="Celular"
														data-mask="(99) 99999-9999" required>
												</div>
											</div>

											<div class="col-lg-4">
												<div class="form-group">
													<label for="inputEmail">E-mail</label>
													<input type="email" id="inputEmail" name="inputEmail"
														class="form-control" placeholder="E-mail">
												</div>
											</div>
										</div>
									</div>
								</div>
								<br>

								<div class="row">
									<div class="col-lg-12">
										<h5 class="mb-0 font-weight-semibold">Dados Bancários</h5>
										<br>
										<div class="row">
											<div class="col-lg-4">
												<label for="cmbBanco">Banco</label>
												<select id="cmbBanco" name="cmbBanco"
													class="form-control select-search">
													<option value="">Selecione</option>
													<?php
													$sql = "SELECT CnBanId, CnBanNome
                                                            FROM ContaBanco
                                                            JOIN Situacao on SituaId =  CnBanStatus
                                                            WHERE SituaChave = 'ATIVO'
                                                            ORDER BY CnBanNome ASC";
													$result = $conn->query($sql);
													$row = $result->fetchAll(PDO::FETCH_ASSOC);

													foreach ($row as $item) {
														print('<option value="' . $item['CnBanId'] . '">' . $item['CnBanNome'] . '</option>');
													}

													?>
												</select>
											</div>

											<div class="col-lg-2">
												<div class="form-group">
													<label for="inputAgencia">Agência</label>
													<input type="text" id="inputAgencia" name="inputAgencia"
														class="form-control" placeholder="Agência + digito">
												</div>
											</div>

											<div class="col-lg-2">
												<div class="form-group">
													<label for="inputConta">Conta</label>
													<input type="text" id="inputConta" name="inputConta"
														class="form-control" placeholder="Conta + digito">
												</div>
											</div>

											<div class="col-lg-4">
												<div class="form-group">
													<label for="inputInformacaoAdicional">Informação Adicional</label>
													<input type="text" id="inputInformacaoAdicional"
														name="inputInformacaoAdicional" class="form-control"
														placeholder="">
												</div>
											</div>

											<div class="col-lg-12">
										<div class="form-group">
											<label for="txtObservacao">Observação</label>
											<textarea rows="5" cols="5" class="form-control" id="txtareaObservacao"
												name="txtareaObservacao" placeholder="Observação"></textarea>
										</div>
									</div>
										</div>
									</div>
								</div>
								<br>

								
							</div>
							<div class="row" style="margin-top: 40px;">
								<div class="col-lg-12">
									<div class="form-group">
										<button class="btn btn-lg btn-principal" id="enviar">Incluir</button>
										<a href="profissional.php" class="btn btn-basic" role="button">Cancelar</a>
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