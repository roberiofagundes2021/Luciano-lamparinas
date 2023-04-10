<?php
include_once("sessao.php");
$_SESSION['PaginaAtual'] = 'Editar Cliente';
include('global_assets/php/conexao.php');

//Se veio do Cliente.php
if (isset($_POST['inputClienteId'])) {
	$iCliente = $_POST['inputClienteId'];
	$sql = "SELECT *
			FROM Cliente
			WHERE ClienId = $iCliente ";
	$result = $conn->query($sql);
	$row = $result->fetch(PDO::FETCH_ASSOC);
}
//Esse else foi criado para se caso o usuário der um REFRESH na página. Nesse caso não terá POST e campos não reconhecerão o $row da consulta acima (daí ele deve ser redirecionado) e se quiser continuar editando terá que clicar no ícone da Grid novamente
else {
	irpara("cliente.php");
}
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
	<!-- Validação -->

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
								$("#cmbEstado").find('option:selected').text();
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

				var inputNomeNovo = "";
				var inputNomeNovoPF = $('#inputNomePF').val();
				var inputNomeVelho = $('#inputClienteNome').val();
				var inputCpf = $('#inputCpf').val().replace(/[^\d]+/g, '');
				inputNomeNovo = inputNomeNovoPF;

				if (inputCpf.trim() == '') {
					$('#inputCpf').val('');
				} else {
					if (!validaCPF(inputCpf)) {
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
				inputNomeNovo = inputNomeNovo.trim();

				//Esse ajax está sendo usado para verificar no banco se o registro já existe
				$.ajax({
					type: "POST",
					url: "clienteValida.php",
					data: {
						nomeNovo: inputNomeNovo,
						nomeVelho: inputNomeVelho,
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
					<form action="clienteEditaFinalizaTransacao.php" name="formCliente" id="formCliente" method="post" class="form-validate-jquery">
					<input type="hidden" id="inputClienteId" name="inputClienteId" class="form-control"  value="<?php echo $_POST['inputClienteId']; ?>" readonly>
						<div class="card-header header-elements-inline">
							<h5 class="text-uppercase font-weight-bold">Editar Cliente "<?php echo $row['ClienNome']; ?>"</h5>
						</div>
						<div class="card-body">
							<h5 class="mb-0 font-weight-semibold">Dados Pessoais</h5>
							<br>
							<div class="row">
								<div class="col-lg-12">
									<div id="dadosPF">
										<div class="row">
											<div class="col-lg-2">
												<div class="form-group">
													<label for="inputCodigo">Prontuário</label>
													<input type="text" id="inputCodigo" name="inputCodigo" class="form-control" placeholder="Prontuário Eletrônico" value="<?php echo $row['ClienCodigo']; ?>" readOnly>
												</div>
											</div>
											<div class="col-lg-4">
												<div class="form-group">
													<label for="inputNomePF">Nome<span class="text-danger"> *</span></label>
													<input type="text" id="inputNomePF" name="inputNomePF" class="form-control" placeholder="Nome Completo" value="<?php echo $row['ClienNome']; ?>" required autofocus>
												</div>
											</div>

											<div class="col-lg-2">
												<div class="form-group">
												<label for="inputNomePF">Nome Social</label>
													<input type="text" id="inputNomeSocial" name="inputNomeSocial" class="form-control" placeholder="Nome Social" value="<?php echo $row['ClienNomeSocial']; ?>">
												</div>
											</div>

											<div class="col-lg-2" id="CPF">
												<div class="form-group">
													<label for="inputCpf">CPF<span class="text-danger"> *</span></label>
													<input required type="text" id="inputCpf" name="inputCpf" class="form-control" placeholder="CPF" data-mask="999.999.999-99" value="<?php echo formatarCPF_Cnpj($row['ClienCpf']); ?>">
												</div>
											</div>

											<div class="col-lg-2">
												<div class="form-group">
													<label for="inputCartaoSus">CNS</label>
													<input type="text" id="inputCartaoSus" name="inputCartaoSus" class="form-control" placeholder="Cartão do SUS" value="<?php echo $row['ClienCartaoSus']; ?>">
												</div>
											</div>
										</div>

										<div class="row">
											<div class="col-lg-2">
												<div class="form-group">
													<label for="inputRg">RG</label>
													<input type="text" id="inputRg" name="inputRg" class="form-control" placeholder="RG" value="<?php echo $row['ClienRg']; ?>">
												</div>
											</div>

											<div class="col-lg-2">
												<div class="form-group">
													<label for="inputEmissor">Emissor</label>
													<input type="text" id="inputEmissor" name="inputEmissor" class="form-control" placeholder="Órgão Emissor" value="<?php echo $row['ClienOrgaoEmissor']; ?>">
												</div>
											</div>

											<div class="col-lg-3">
												<div class="form-group">
													<label for="cmbUf">UF</label>
													<select id="cmbUf" name="cmbUf" class="form-control form-control-select2">
														<option value="#">Selecione um estado</option>
														<option value="AC" <?php if ($row['ClienUf'] == 'AC') echo "selected"; ?>>Acre</option>
														<option value="AL" <?php if ($row['ClienUf'] == 'AL') echo "selected"; ?>>Alagoas</option>
														<option value="AP" <?php if ($row['ClienUf'] == 'AP') echo "selected"; ?>>Amapá</option>
														<option value="AM" <?php if ($row['ClienUf'] == 'AM') echo "selected"; ?>>Amazonas</option>
														<option value="BA" <?php if ($row['ClienUf'] == 'BA') echo "selected"; ?>>Bahia</option>
														<option value="CE" <?php if ($row['ClienUf'] == 'CE') echo "selected"; ?>>Ceará</option>
														<option value="DF" <?php if ($row['ClienUf'] == 'DF') echo "selected"; ?>>Distrito Federal</option>
														<option value="ES" <?php if ($row['ClienUf'] == 'ES') echo "selected"; ?>>Espírito Santo</option>
														<option value="GO" <?php if ($row['ClienUf'] == 'GO') echo "selected"; ?>>Goiás</option>
														<option value="MA" <?php if ($row['ClienUf'] == 'MA') echo "selected"; ?>>Maranhão</option>
														<option value="MT" <?php if ($row['ClienUf'] == 'MT') echo "selected"; ?>>Mato Grosso</option>
														<option value="MS" <?php if ($row['ClienUf'] == 'MS') echo "selected"; ?>>Mato Grosso do Sul</option>
														<option value="MG" <?php if ($row['ClienUf'] == 'MG') echo "selected"; ?>>Minas Gerais</option>
														<option value="PA" <?php if ($row['ClienUf'] == 'PA') echo "selected"; ?>>Pará</option>
														<option value="PB" <?php if ($row['ClienUf'] == 'PB') echo "selected"; ?>>Paraíba</option>
														<option value="PR" <?php if ($row['ClienUf'] == 'PR') echo "selected"; ?>>Paraná</option>
														<option value="PE" <?php if ($row['ClienUf'] == 'PE') echo "selected"; ?>>Pernambuco</option>
														<option value="PI" <?php if ($row['ClienUf'] == 'PI') echo "selected"; ?>>Piauí</option>
														<option value="RJ" <?php if ($row['ClienUf'] == 'RJ') echo "selected"; ?>>Rio de Janeiro</option>
														<option value="RN" <?php if ($row['ClienUf'] == 'RN') echo "selected"; ?>>Rio Grande do Norte</option>
														<option value="RS" <?php if ($row['ClienUf'] == 'RS') echo "selected"; ?>>Rio Grande do Sul</option>
														<option value="RO" <?php if ($row['ClienUf'] == 'RO') echo "selected"; ?>>Rondônia</option>
														<option value="RR" <?php if ($row['ClienUf'] == 'RR') echo "selected"; ?>>Roraima</option>
														<option value="SC" <?php if ($row['ClienUf'] == 'SC') echo "selected"; ?>>Santa Catarina</option>
														<option value="SP" <?php if ($row['ClienUf'] == 'SP') echo "selected"; ?>>São Paulo</option>
														<option value="SE" <?php if ($row['ClienUf'] == 'SE') echo "selected"; ?>>Sergipe</option>
														<option value="TO" <?php if ($row['ClienUf'] == 'TO') echo "selected"; ?>>Tocantins</option>
														<option value="ES" <?php if ($row['ClienUf'] == 'ES') echo "selected"; ?>>Estrangeiro</option>
													</select>
												</div>
											</div>

											<div class="col-lg-2">
												<div class="form-group">
													<label for="cmbSexo">Sexo</label>
													<select id="cmbSexo" name="cmbSexo" class="form-control form-control-select2">
														<option value="#">Selecione o sexo</option>
														<option value="F" <?php if ($row['ClienSexo'] == 'F') echo "selected"; ?>>Feminino</option>
														<option value="M" <?php if ($row['ClienSexo'] == 'M') echo "selected"; ?>>Masculino</option>
													</select>
												</div>
											</div>

											<div class="col-lg-3">
												<div class="form-group">
													<label for="inputDtNascimento">Data Nascimento</label>
													<input type="date" id="inputDtNascimento" name="inputDtNascimento" class="form-control" onfocusout="formataCampoDataNascimento()" placeholder="Data Nascimento" value="<?php echo $row['ClienDtNascimento']; ?>">
												</div>
											</div>
										</div>

										<div class="row">
											<div class="col-lg-4">
												<div class="form-group">
													<label for="inputNomePai">Nome do Pai</label>
													<input type="text" id="inputNomePai" name="inputNomePai" class="form-control" placeholder="Nome do Pai" value="<?php echo $row['ClienNomePai']; ?>">
												</div>
											</div>

											<div class="col-lg-4">
												<div class="form-group">
													<label for="inputNomeMae">Nome da Mãe</label>
													<input type="text" id="inputNomeMae" name="inputNomeMae" class="form-control" placeholder="Nome da Mãe" value="<?php echo $row['ClienNomeMae']; ?>">
												</div>
											</div>

											<div class="col-lg-4">
											<div class="form-group">
													<label for="cmbRacaCor">Raça/Cor</label>
													<select id="cmbRacaCor" name="cmbRacaCor" class="form-control form-control-select2">
														<option value="#">Selecione a Raça/Cor</option>
														<option value="Branca" <?php if ($row['ClienRacaCor'] == 'Branca') echo "selected"; ?> >Branca</option>
														<option value="Preta" <?php if ($row['ClienRacaCor'] == 'Preta') echo "selected"; ?> >Preta</option>
														<option value="Parda" <?php if ($row['ClienRacaCor'] == 'Parda') echo "selected"; ?> >Parda</option>
														<option value="Amarela" <?php if ($row['ClienRacaCor'] == 'Amarela') echo "selected"; ?> >Amarela</option>
														<option value="Indigena" <?php if ($row['ClienRacaCor'] == 'Indigena') echo "selected"; ?> >Indígena</option>
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
														<option value="ST" <?php if ($row['ClienEstadoCivil'] == 'ST') echo "selected"; ?> >Solteiro</option>
														<option value="CS" <?php if ($row['ClienEstadoCivil'] == 'CS') echo "selected"; ?> >Casado</option>
														<option value="SP" <?php if ($row['ClienEstadoCivil'] == 'SP') echo "selected"; ?> >Separado</option>
														<option value="DV" <?php if ($row['ClienEstadoCivil'] == 'DV') echo "selected"; ?> >Divorciado</option>
														<option value="VI" <?php if ($row['ClienEstadoCivil'] == 'VI') echo "selected"; ?> >Viúvo</option>
													</select>
												</div>
											</div>	
											<div class="col-lg-4">
											    <div class="form-group">
													<label for="inputNaturalidade">Naturalidade</label>
												    <input type="text" id="inputNaturalidade" name="inputNaturalidade" class="form-control" placeholder="Naturalidade" value="<?php echo $row['ClienNaturalidade']; ?>">
											    </div>
										    </div>
											<div class="col-lg-4">
												<label for="inputProfissao">Profissão</label>
												<input type="text" id="inputProfissao" name="inputProfissao" class="form-control" placeholder="Profissão" value="<?php echo $row['ClienProfissao']; ?>">											
											</div>
										</div>
									</div> <!-- Fim dadosPF -->


									<br>

									<div class="row">
										<div class="col-lg-12">
											<h5 class="mb-0 font-weight-semibold">Endereço</h5>
											<br>
											<div class="row">
												<div class="col-lg-1">
													<div class="form-group">
														<label for="inputCep">CEP</label>
														<input type="text" id="inputCep" name="inputCep" class="form-control" placeholder="CEP" value="<?php echo $row['ClienCep']; ?>" maxLength="8">
													</div>
												</div>

												<div class="col-lg-5">
													<div class="form-group">
														<label for="inputEndereco">Endereço</label>
														<input type="text" id="inputEndereco" name="inputEndereco" class="form-control" placeholder="Endereço" value="<?php echo $row['ClienEndereco']; ?>">
													</div>
												</div>

												<div class="col-lg-1">
													<div class="form-group">
														<label for="inputNumero">Nº</label>
														<input type="text" id="inputNumero" name="inputNumero" class="form-control" placeholder="Número" value="<?php echo $row['ClienNumero']; ?>">
													</div>
												</div>

												<div class="col-lg-5">
													<div class="form-group">
														<label for="inputComplemento">Complemento</label>
														<input type="text" id="inputComplemento" name="inputComplemento" class="form-control" placeholder="complemento" value="<?php echo $row['ClienComplemento']; ?>">
													</div>
												</div>
											</div>

											<div class="row">
												<div class="col-lg-4">
													<div class="form-group">
														<label for="inputBairro">Bairro</label>
														<input type="text" id="inputBairro" name="inputBairro" class="form-control" placeholder="Bairro" value="<?php echo $row['ClienBairro']; ?>">
													</div>
												</div>

												<div class="col-lg-5">
													<div class="form-group">
														<label for="inputCidade">Cidade</label>
														<input type="text" id="inputCidade" name="inputCidade" class="form-control" placeholder="Cidade" value="<?php echo $row['ClienCidade']; ?>">
													</div>
												</div>

												<div class="col-lg-3">
													<div class="form-group">
														<label for="cmbEstado">Estado</label>
														<select id="cmbEstado" name="cmbEstado" class="form-control">
															<option value="#">Selecione um estado</option>
															<option value="AC" <?php if ($row['ClienEstado'] == 'AC') echo "selected"; ?>>Acre</option>
															<option value="AL" <?php if ($row['ClienEstado'] == 'AL') echo "selected"; ?>>Alagoas</option>
															<option value="AP" <?php if ($row['ClienEstado'] == 'AP') echo "selected"; ?>>Amapá</option>
															<option value="AM" <?php if ($row['ClienEstado'] == 'AM') echo "selected"; ?>>Amazonas</option>
															<option value="BA" <?php if ($row['ClienEstado'] == 'BA') echo "selected"; ?>>Bahia</option>
															<option value="CE" <?php if ($row['ClienEstado'] == 'CE') echo "selected"; ?>>Ceará</option>
															<option value="DF" <?php if ($row['ClienEstado'] == 'DF') echo "selected"; ?>>Distrito Federal</option>
															<option value="ES" <?php if ($row['ClienEstado'] == 'ES') echo "selected"; ?>>Espírito Santo</option>
															<option value="GO" <?php if ($row['ClienEstado'] == 'GO') echo "selected"; ?>>Goiás</option>
															<option value="MA" <?php if ($row['ClienEstado'] == 'MA') echo "selected"; ?>>Maranhão</option>
															<option value="MT" <?php if ($row['ClienEstado'] == 'MT') echo "selected"; ?>>Mato Grosso</option>
															<option value="MS" <?php if ($row['ClienEstado'] == 'MS') echo "selected"; ?>>Mato Grosso do Sul</option>
															<option value="MG" <?php if ($row['ClienEstado'] == 'MG') echo "selected"; ?>>Minas Gerais</option>
															<option value="PA" <?php if ($row['ClienEstado'] == 'PA') echo "selected"; ?>>Pará</option>
															<option value="PB" <?php if ($row['ClienEstado'] == 'PB') echo "selected"; ?>>Paraíba</option>
															<option value="PR" <?php if ($row['ClienEstado'] == 'PR') echo "selected"; ?>>Paraná</option>
															<option value="PE" <?php if ($row['ClienEstado'] == 'PE') echo "selected"; ?>>Pernambuco</option>
															<option value="PI" <?php if ($row['ClienEstado'] == 'PI') echo "selected"; ?>>Piauí</option>
															<option value="RJ" <?php if ($row['ClienEstado'] == 'RJ') echo "selected"; ?>>Rio de Janeiro</option>
															<option value="RN" <?php if ($row['ClienEstado'] == 'RN') echo "selected"; ?>>Rio Grande do Norte</option>
															<option value="RS" <?php if ($row['ClienEstado'] == 'RS') echo "selected"; ?>>Rio Grande do Sul</option>
															<option value="RO" <?php if ($row['ClienEstado'] == 'RO') echo "selected"; ?>>Rondônia</option>
															<option value="RR" <?php if ($row['ClienEstado'] == 'RR') echo "selected"; ?>>Roraima</option>
															<option value="SC" <?php if ($row['ClienEstado'] == 'SC') echo "selected"; ?>>Santa Catarina</option>
															<option value="SP" <?php if ($row['ClienEstado'] == 'SP') echo "selected"; ?>>São Paulo</option>
															<option value="SE" <?php if ($row['ClienEstado'] == 'SE') echo "selected"; ?>>Sergipe</option>
															<option value="TO" <?php if ($row['ClienEstado'] == 'TO') echo "selected"; ?>>Tocantins</option>
															<option value="ES" <?php if ($row['ClienEstado'] == 'ES') echo "selected"; ?>>Estrangeiro</option>
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
														<input type="text" id="inputNomeContato" name="inputNomeContato" class="form-control" placeholder="Contato" value="<?php echo $row['ClienContato']; ?>">
													</div>
												</div>

												<div class="col-lg-2">
													<div class="form-group">
														<label for="inputTelefone">Telefone</label>
														<input type="tel" id="inputTelefone" name="inputTelefone" class="form-control" placeholder="Telefone" data-mask="(99) 9999-9999" value="<?php echo $row['ClienTelefone']; ?>">
													</div>
												</div>

												<div class="col-lg-2">
													<div class="form-group">
														<label for="inputCelular">Celular</label>
														<input type="tel" id="inputCelular" name="inputCelular" class="form-control" placeholder="Celular" data-mask="(99) 99999-9999" value="<?php echo $row['ClienCelular']; ?>">
													</div>
												</div>

												<div class="col-lg-4">
													<div class="form-group">
														<label for="inputEmail">E-mail</label>
														<input type="email" id="inputEmail" name="inputEmail" class="form-control" placeholder="E-mail" value="<?php echo $row['ClienEmail']; ?>">
													</div>
												</div>
											</div>

											<div class="row">
												<div class="col-lg-12">
													<div class="form-group">
														<label for="txtObservacao">Observação</label>
														<textarea rows="5" cols="5" class="form-control" id="txtareaObservacao" name="txtareaObservacao" placeholder="Observação"><?php echo $row['ClienObservacao']; ?></textarea>
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
													echo '<button class="btn btn-lg btn-principal" id="enviar">Incluir</button>';
												}
												?>
												<a href="cliente.php" class="btn btn-basic" role="button">Cancelar</a>
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


	</div>
	<!-- /main content -->

	</div>
	<!-- /page content -->
	
	<?php include_once("footer.php"); ?>

</body>
</html>