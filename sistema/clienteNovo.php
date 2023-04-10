<?php
include_once("sessao.php");
$_SESSION['PaginaAtual'] = 'Novo Cliente';
include('global_assets/php/conexao.php');
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Cliente</title>

	<?php include_once("head.php"); ?>

	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
	<script src="global_assets/js/demo_pages/form_select2.js"></script>
	<script src="global_assets/js/demo_pages/form_layouts.js"></script>
	<script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>
	<script src="global_assets/js/plugins/forms/inputs/inputmask.js"></script>
	<!-- /theme JS files -->

	<!-- Validação -->
	<script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
	<script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
	<script src="global_assets/js/demo_pages/form_validation.js"></script>
	<!-- /Validação -->

	<!-- Adicionando Javascript -->

	<script type="text/javascript">
		function validaDataNascimento(dataASerValidada){			
			//Adicionado um espaço para forçar o fuso horário de brasília		
			let dataObj = new Date(dataASerValidada+" ");
			let hoje = new Date();
			if((hoje-dataObj)<0){
				return false;				
			}
			else{
				return true;
			}
		}

		function formataCampoDataNascimento(){
			let dataPreenchida = $('#inputDtNascimento').val();
			if (!validaDataNascimento(dataPreenchida)){
				let labelErro = $('#inputDtNascimento-error')
				labelErro.removeClass('validation-valid-label');
				labelErro[0].innerHTML = "Data não pode ser futura";	
				$('#inputDtNascimento').val("");			
			}
		}
	</script>

	<script type="text/javascript">
		$(document).ready(function() {
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

			//Esta função será executada quando o campo cep perder o foco.
			$("#inputCep").blur(function() {
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
						$.getJSON("https://viacep.com.br/ws/" + cep + "/json/?callback=?", function(dados) {

							if (!("erro" in dados)) {

								//Atualiza os campos com os valores da consulta.
								$("#inputEndereco").val(dados.logradouro);
								$("#inputBairro").val(dados.bairro);
								$("#inputCidade").val(dados.localidade);
								$("#cmbEstado").val(dados.uf);
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
			}); //cep

			//Valida Registro Duplicado
			$("#enviar").on('click', function(e) {
				e.preventDefault();

				// subistitui qualquer espaço em branco no campo "CEP" antes de enviar para o banco
				var cep = $("#inputCep").val()
				cep = cep.replace(' ', '')
				$("#inputCep").val(cep)

				var inputNome = "";
				var inputNomePF = $('#inputNomePF').val();
				var inputCpf = $('#inputCpf').val().replace(/[^\d]+/g, '');

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

				let dataPreenchida = $("#inputDtNascimento").val();
				if(!validaDataNascimento(dataPreenchida)){
					$('#inputDtNascimento').val('');
					alerta('Atenção', 'Data de nascimento não pode ser futura!', 'error');
					$('#inputDtNascimento').focus();
					return false;
				}

				//remove os espaços desnecessários antes e depois
				inputNome = inputNome.trim();

				//Esse ajax está sendo usado para verificar no banco se o registro já existe
				$.ajax({
					type: "POST",
					url: "clienteValida.php",
					data: {
						nome: inputNome,
						cpf: inputCpf
					},
					success: function(resposta) {
						if (resposta >= 1) {
							alerta('Atenção', 'Esse registro já existe!', 'error');
							return false;
						}
						$('#formCliente').submit();
					}
				}); //ajax

			}); // enviar

		}); //document.ready

		
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
					<form action="clienteNovoFinalizaTransacao.php" name="formCliente" id="formCliente" method="post" class="form-validate-jquery">
						<div class="card-header header-elements-inline">
							<h5 class="text-uppercase font-weight-bold">Cadastrar Novo Cliente</h5>
						</div>
						<div class="card-body">
							<h5 class="mb-0 font-weight-semibold">Dados Pessoais</h5>
							<br>
							<div class="row">
								<div class="col-lg-12">
									<div id="dadosPF">
										<div class="row">
											<div class="col-lg-4">
												<div class="form-group">
													<label for="inputNomePF">Nome<span class="text-danger"> *</span></label>
													<input type="text" id="inputNomePF" name="inputNomePF" class="form-control" placeholder="Nome Completo" required autofocus>
												</div>
											</div>

											<div class="col-lg-4">
												<div class="form-group">
													<label for="inputNomeSocial">Nome Social</label>
													<input type="text" id="inputNomeSocial" name="inputNomeSocial" class="form-control" placeholder="Nome Social">
												</div>
											</div>

											<div class="col-lg-2">
												<div class="form-group">
													<label for="inputCpf">CPF<span class="text-danger"> *</span></label>
													<input type="text" id="inputCpf" name="inputCpf" class="form-control" data-mask="999.999.999-99" required>
												</div>
											</div>

											<div class="col-lg-2">
												<div class="form-group">
													<label for="inputCartaoSus">CNS</label>
													<input type="text" id="inputCartaoSus" name="inputCartaoSus" class="form-control" placeholder="Cartão do SUS">
												</div>
											</div>
										</div>

										<div class="row">
											<div class="col-lg-2">
												<div class="form-group">
													<label for="inputRg">RG</label>
													<input type="text" id="inputRg" name="inputRg" class="form-control" placeholder="RG">
												</div>
											</div>

											<div class="col-lg-2">
												<div class="form-group">
													<label for="inputEmissor">Emissor</label>
													<input type="text" id="inputEmissor" name="inputEmissor" class="form-control" placeholder="Órgão Emissor">
												</div>
											</div>

											<div class="col-lg-3">
												<div class="form-group">
													<label for="cmbUf">UF</label>
													<select id="cmbUf" name="cmbUf" class="form-control form-control-select2">
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

											<div class="col-lg-2">
												<div class="form-group">
													<label for="cmbSexo">Sexo</label>
													<select id="cmbSexo" name="cmbSexo" class="form-control form-control-select2">
														<option value="#">Selecione o sexo</option>
														<option value="F">Feminino</option>
														<option value="M">Masculino</option>
													</select>
												</div>
											</div>

											<div class="col-lg-3">
												<div class="form-group">
													<label for="inputDtNascimento">Data Nascimento</label>
													<input type="date" id="inputDtNascimento" name="inputDtNascimento" class="form-control" placeholder="Data Nascimento" onfocusout="formataCampoDataNascimento()">
												</div>
											</div>
										</div>

										<div class="row">
											<div class="col-lg-4">
												<div class="form-group">
													<label for="inputNomePai">Nome do Pai</label>
													<input type="text" id="inputNomePai" name="inputNomePai" class="form-control" placeholder="Nome do Pai">
												</div>
											</div>

											<div class="col-lg-4">
												<div class="form-group">
													<label for="inputNomeMae">Nome da Mãe</label>
													<input type="text" id="inputNomeMae" name="inputNomeMae" class="form-control" placeholder="Nome da Mãe">
												</div>
											</div>
											
											<div class="col-lg-4">
												<div class="form-group">
													<label for="cmbRacaCor">Raça/Cor</label>
													<select id="cmbRacaCor" name="cmbRacaCor" class="form-control form-control-select2">
														<option value="#">Selecione a Raça/Cor</option>
														<option value="Branca">Branca</option>
														<option value="Preta">Preta</option>
														<option value="Parda">Parda</option>
														<option value="Amarela">Amarela</option>
														<option value="Indigena">Indígena</option>
													</select>
												</div>
											</div>
										</div>

										<div class="row">
											<div class="col-lg-4">
												<div class="form-group">
													<label for="cmbEstadoCivil">Estado Civil</label>
													<select id="cmbEstadoCivil" name="cmbEstadoCivil" class="form-control form-control-select2">
														<option value="#">Selecione um estado civil</option>
														<option value="ST">Solteiro</option>
														<option value="CS">Casado</option>
														<option value="SP">Separado</option>
														<option value="DV">Divorciado</option>
														<option value="VI">Viúvo</option>
													</select>
												</div>
											</div>	
											<div class="col-lg-4">
												<div class="form-group">
													<label for="inputNaturalidade">Naturalidade</label>
													<input type="text" id="inputNaturalidade" name="inputNaturalidade" class="form-control" placeholder="Naturalidade">
												</div>
											</div>
											<div class="col-lg-4">
												<div class="form-group">
													<label for="inputProfissao">Profissão</label>
												<input type="text" id="inputProfissao" name="inputProfissao" class="form-control" placeholder="Profissão" >											

												
												</div>
											</div>
										</div>
									</div> <!-- Fim dadosPF -->
								</div>
							</div>
							<br>

							<div class="row">
								<div class="col-lg-12">
									<h5 class="mb-0 font-weight-semibold">Endereço</h5>
									<br>
									<div class="row">
										<div class="col-lg-1">
											<div class="form-group">
												<label for="inputCep">CEP</label>
												<input type="text" id="inputCep" name="inputCep" class="form-control" placeholder="CEP" maxLength="8">
											</div>
										</div>

										<div class="col-lg-5">
											<div class="form-group">
												<label for="inputEndereco">Endereço</label>
												<input type="text" id="inputEndereco" name="inputEndereco" class="form-control" placeholder="Endereço">
											</div>
										</div>

										<div class="col-lg-1">
											<div class="form-group">
												<label for="inputNumero">Nº</label>
												<input type="text" id="inputNumero" name="inputNumero" class="form-control" placeholder="Número">
											</div>
										</div>

										<div class="col-lg-5">
											<div class="form-group">
												<label for="inputComplemento">Complemento</label>
												<input type="text" id="inputComplemento" name="inputComplemento" class="form-control" placeholder="complemento">
											</div>
										</div>
									</div>

									<div class="row">
										<div class="col-lg-4">
											<div class="form-group">
												<label for="inputBairro">Bairro</label>
												<input type="text" id="inputBairro" name="inputBairro" class="form-control" placeholder="Bairro">
											</div>
										</div>

										<div class="col-lg-5">
											<div class="form-group">
												<label for="inputCidade">Cidade</label>
												<input type="text" id="inputCidade" name="inputCidade" class="form-control" placeholder="Cidade">
											</div>
										</div>

										<div class="col-lg-3">
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
												<input type="text" id="inputNomeContato" name="inputNomeContato" class="form-control" placeholder="Contato">
											</div>
										</div>

										<div class="col-lg-2">
											<div class="form-group">
												<label for="inputTelefone">Telefone</label>
												<input type="tel" id="inputTelefone" name="inputTelefone" class="form-control" placeholder="Telefone" data-mask="(99) 9999-9999">
											</div>
										</div>

										<div class="col-lg-2">
											<div class="form-group">
												<label for="inputCelular">Celular</label>
												<input type="tel" id="inputCelular" name="inputCelular" class="form-control" placeholder="Celular" data-mask="(99) 99999-9999">
											</div>
										</div>

										<div class="col-lg-4">
											<div class="form-group">
												<label for="inputEmail">E-mail</label>
												<input type="email" id="inputEmail" name="inputEmail" class="form-control" placeholder="E-mail">
											</div>
										</div>
									</div>

									<div class="row">
										<div class="col-lg-12">
											<div class="form-group">
												<label for="txtObservacao">Observação</label>
												<textarea rows="5" cols="5" class="form-control" id="txtareaObservacao" name="txtareaObservacao" placeholder="Observação"></textarea>
											</div>
										</div>
									</div>
								</div>
							</div>
							<br>

							<div class="row" style="margin-top: 40px;">
								<div class="col-lg-12">
									<div class="form-group">
										<button class="btn btn-lg btn-principal" id="enviar">Incluir</button>
										<a href="cliente.php" class="btn btn-basic" role="button">Cancelar</a>
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