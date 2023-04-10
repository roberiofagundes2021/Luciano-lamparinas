<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Nova Empresa';

include('global_assets/php/conexao.php');

if(isset($_POST['inputCnpj'])){

	try{
		
		$sql = "INSERT INTO Empresa (EmpreCnpj, EmpreRazaoSocial, EmpreNomeFantasia, EmpreCep, EmpreEndereco, EmpreNumero, EmpreComplemento, 
									 EmpreBairro, EmpreCidade, EmpreEstado, EmpreContato, EmpreTelefone, EmpreCelular, EmpreEmail, 
									 EmpreSite, EmpreObservacao, EmpreStatus, EmpreUsuarioAtualizador, EmpreFoto)
				VALUES (:sCnpj, :sRazaoSocial, :sNomeFantasia,  :sCep, :sEndereco, :sNumero, :sComplemento, :sBairro, :sCidade, 
						:sEstado, :sContato, :sTelefone, :sCelular, :sEmail, :sSite, :sObservacao, :bStatus, :iUsuarioAtualizador, :sFoto)";
		$result = $conn->prepare($sql);
		
		$conn->beginTransaction();
				
		$result->execute(array(
						':sCnpj' => limpaCPF_CNPJ($_POST['inputCnpj']),
						':sRazaoSocial' => $_POST['inputRazaoSocial'],
						':sNomeFantasia' => $_POST['inputNomeFantasia'],
						':sCep' => limpaCEP($_POST['inputCep']),
						':sEndereco' => $_POST['inputEndereco'],
						':sNumero' => $_POST['inputNumero'],
						':sComplemento' => $_POST['inputComplemento'],
						':sBairro' => $_POST['inputBairro'],
						':sCidade' => $_POST['inputCidade'],
						':sEstado' => $_POST['cmbEstado'],
						':sContato' => $_POST['inputNomeContato'],
						':sTelefone' => $_POST['inputTelefone'],
						':sCelular' => $_POST['inputCelular'],
						':sEmail' => $_POST['inputEmail'],
						':sSite' => $_POST['inputSite'],
						':sObservacao' => $_POST['txtareaObservacao'],						
						':bStatus' => 1,
						':iUsuarioAtualizador' => $_SESSION['UsuarId'],
						':sFoto' => isset($_POST['inputFoto']) ? $_POST['inputFoto'] : null
						));
						
		$insertId = $conn->lastInsertId();
		
		$sql = "INSERT INTO Parametro 
					(ParamEmpresaPublica, ParamValorAtualizadoFluxo, ParamValorAtualizadoOrdemCompra, ParamValorObsImpreRetirada, ParamProdutoOrcamento, ParamServicoOrcamento, ParamPrecoGridProduto, ParamUsuarioAtualizador, ParamEmpresa)
				VALUES 
					(:iEmpresaPublica, :iValorAtualizadoFluxo, :iValorAtualizadoOrdemCompra, :iValorObsImpreRetirada, :iProdutoOrcamento, :iServicoOrcamento, :PrecoGridProduto, :iUsuarioAtualizador, :iEmpresa)";
		$result = $conn->prepare($sql);

		$result->execute(array(
						':iEmpresaPublica' => 1,
						':iValorAtualizadoFluxo' => 1,
						':iValorAtualizadoOrdemCompra' => 0,
						':iValorObsImpreRetirada' => 0,
						':iProdutoOrcamento' => 0,
						':iServicoOrcamento' => 0,
						':PrecoGridProduto' => 'precoCustoFinal',
						':iUsuarioAtualizador' => $_SESSION['UsuarId'],
						':iEmpresa' => $insertId
						));
		
		$conn->commit();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Empresa incluída!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {
		
		$conn->rollback();
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao incluir empresa!!!";
		$_SESSION['msg']['tipo'] = "error";	
		
		echo 'Error: ' . $e->getMessage();
	}
	
	irpara("empresa.php");
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Empresa</title>

	<?php include_once("head.php"); ?>
	
	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
	
	<script src="global_assets/js/plugins/forms/inputs/inputmask.js"></script>	
	
	<script src="global_assets/js/plugins/notifications/pnotify.min.js"></script>
	<script src="global_assets/js/demo_pages/extra_pnotify.js"></script>

	<script src="global_assets/js/plugins/media/fancybox.min.js"></script>
	
	<!-- /theme JS files -->	

	<!-- Validação -->
	<script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
	<script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
	<script src="global_assets/js/demo_pages/form_validation.js"></script>	
	
	<!-- Adicionando Javascript -->
    <script type="text/javascript" >

        $(document).ready(function() {

			//Aqui sou obrigado a instanciar novamente a utilização do fancybox
			$(".fancybox").fancybox({
				// options
			});				

            function limpa_formulário_cep() {
                // Limpa valores do formulário de cep.
                $("#inputEndereco").val("");
                $("#inputBairro").val("");
                $("#inputCidade").val("");
                $("#cmbEstado").val("");                
            }
            
            //Quando o campo cep perde o foco.
            $("#inputCep").blur(function() {

                //Nova variável "cep" somente com dígitos.
                var cep = $(this).val().replace(/\D/g, '');

                //Verifica se campo cep possui valor informado.
                if (cep != "") {

                    //Expressão regular para validar o CEP.
                    var validacep = /^[0-9]{8}$/;

                    //Valida o formato do CEP.
                    if(validacep.test(cep)) {

                        //Preenche os campos com "..." enquanto consulta webservice.
                        $("#inputEndereco").val("...");
                        $("#inputBairro").val("...");
                        $("#inputCidade").val("...");
                        $("#cmbEstado").val("...");                        

                        //Consulta o webservice viacep.com.br/
                        $.getJSON("https://viacep.com.br/ws/"+ cep +"/json/?callback=?", function(dados) {

                            if (!("erro" in dados)) {

                                //Atualiza os campos com os valores da consulta.
                                $("#inputEndereco").val(dados.logradouro);
                                $("#inputBairro").val(dados.bairro);
                                $("#inputCidade").val(dados.localidade);
                                $("#cmbEstado").removeClass("form-control-select2");
                                $("#cmbEstado").val(dados.uf);
								$("#cmbEstado").addClass("form-control-select2");
								//$("#cmbEstado").find('option[value="MA"]').attr('selected','selected');
								//$('#cmbEstado :selected').text();
								//$("#cmbEstado").find('option:selected').text();
								//document.getElementById("cmbEstado").options[5].selected = true;
                            } //end if.
                            else {
                                //CEP pesquisado não foi encontrado.
                                limpa_formulário_cep();
                                alerta("Erro","CEP não encontrado.", "erro");
                            }
                        });
                    } //end if.
                    else {
                        //cep é inválido.
                        limpa_formulário_cep();
                        alerta("Erro","Formato de CEP inválido.","erro");
                    }
                } //end if.
                else {
                    //cep sem valor, limpa formulário.
                    limpa_formulário_cep();
                }
            }); //end blur

			//Ao clicar no botão Adicionar Foto aciona o click do file que está hidden
			$('#addFoto').on('click', function(e){	
				e.preventDefault(); // Isso aqui não deixa o formulário "formProduto" ser submetido ao clicar no INcluir Foto, ou seja, ao executar o método ajax
			
				$('#imagem').trigger("click");
			});			
			
			// #imagem é o id do input, ao alterar o conteudo do input execurará a função abaixo
			$('#imagem').on('change',function(){

				$('#visualizar').html('<img src="global_assets/images/lamparinas/ajax-loader.gif" alt="Enviando..." />');
								
				// Get form
				var form = $('#formFoto')[0];
				var formData = new FormData(form);
				//var inputFoto = $('#inputFoto').val();
				//alert($('#imagem')[0].files[0]);
				
				formData.append('file', $('#imagem')[0].files[0] );
				formData.append('tela', 'empresa' );
				
				$.ajax({
					type: "POST",
					enctype: 'multipart/form-data',
					url: "upload.php",
					processData: false,  // impedir que o jQuery tranforma a "data" em querystring					
					contentType: false,  // desabilitar o cabeçalho "Content-Type"
					cache: false, // desabilitar o "cache"
					data: formData,//{imagem: inputImagem},
					success: function(resposta){
						//console.log(resposta);
						
						$('#visualizar').html(resposta);
						$('#addFoto').text("Alterar Foto...");
						
						//Aqui sou obrigado a instanciar novamente a utilização do fancybox
						$(".fancybox").fancybox({
							// options
						});	
						
						return false;						
					}
				}); //ajax
				
				//$('#formFoto').submit();
				
				// Efetua o Upload sem dar refresh na pagina
				$('#formFoto').ajaxForm({
					target:'#visualizar' // o callback será no elemento com o id #visualizar
				}).submit();
			});			
       
			//Valida Registro Duplicado
			$('#enviar').on('click', function(e){
				
				e.preventDefault();

				// subistitui qualquer espaço em branco no campo "CEP" antes de enviar para o banco
				var cep = $("#inputCep").val()
				cep = cep.replace(' ','')
				$("#inputCep").val(cep)
				
				//pega só os números do campo CNPJ
				var inputCnpj = $('#inputCnpj').val().replace(/[^\d]+/g,'');
				var inputRazaoSocial = $('#inputRazaoSocial').val();
				var inputNomeFantasia = $('#inputNomeFantasia').val();

				if (inputCnpj.trim() == ''){
					$('#inputCnpj').val('');
					$("#formEmpresa").submit();
				} else {

					if (!validarCNPJ(inputCnpj)){
						$('#inputCnpj').val('');
						alerta('Atenção','CNPJ inválido!','error');
						$('#inputCnpj').focus();
						return false;
					}

					if (inputRazaoSocial != '' && inputNomeFantasia != ''){
																																			
						//Esse ajax está sendo usado para verificar no banco se o registro já existe
						$.ajax({
							type: "POST",
							url: "empresaValida.php",
							data: ('cnpj='+inputCnpj),
							success: function(resposta){
								
								if(resposta == 1){
									alerta('Atenção','Essa empresa já foi cadastrada no sistema!','error');
									return;
								} 

								$("#formEmpresa").submit();
							}
						})
					}
				}
			})

			//Valida Registro Duplicado
			$('#cancelar').on('click', function(e){
				
				e.preventDefault();
				
				var inputFoto = $('#inputFoto').val();
				
				//Esse ajax está sendo usado para excluir a imagem que nao será mais usada
				$.ajax({
					type: "POST",
					url: "empresaExcluiImagem.php",
					data: ('foto='+inputFoto),
					success: function(resposta){
						
					}
				})				
				
				$(window.document.location).attr('href',"empresa.php");
				
			}); // cancelar		

        });	

		function validarCNPJ(cnpj) {
 
			cnpj = cnpj.replace(/[^\d]+/g,'');

			if(cnpj == '') return false;
			
			if (cnpj.length != 14)
				return false;

			// Elimina CNPJs invalidos conhecidos
			if (cnpj == "00000000000000" || 
				cnpj == "11111111111111" || 
				cnpj == "22222222222222" || 
				cnpj == "33333333333333" || 
				cnpj == "44444444444444" || 
				cnpj == "55555555555555" || 
				cnpj == "66666666666666" || 
				cnpj == "77777777777777" || 
				cnpj == "88888888888888" || 
				cnpj == "99999999999999")
				return false;
				
			// Valida DVs
			tamanho = cnpj.length - 2
			numeros = cnpj.substring(0,tamanho);
			digitos = cnpj.substring(tamanho);
			soma = 0;
			pos = tamanho - 7;
			for (i = tamanho; i >= 1; i--) {
			soma += numeros.charAt(tamanho - i) * pos--;
			if (pos < 2)
					pos = 9;
			}
			resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
			if (resultado != digitos.charAt(0))
				return false;
				
			tamanho = tamanho + 1;
			numeros = cnpj.substring(0,tamanho);
			soma = 0;
			pos = tamanho - 7;
			for (i = tamanho; i >= 1; i--) {
			soma += numeros.charAt(tamanho - i) * pos--;
			if (pos < 2)
					pos = 9;
			}
			resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
			if (resultado != digitos.charAt(1))
				return false;
					
			return true;
			
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
					
					<form name="formEmpresa" id="formEmpresa" method="post" class="form-validate-jquery" >
						<div class="card-header header-elements-inline">
							<h5 class="text-uppercase font-weight-bold">Cadastrar Nova Empresa</h5>
						</div>
						
						<div class="card-body">	
							<div class="media">
								<div class="media-body">

									<div class="row">
										<div class="col-lg-3">
											<div class="form-group">
												<label for="inputCnpj">CNPJ<span class="text-danger"> *</span></label>
												<input type="text" id="inputCnpj" name="inputCnpj" class="form-control" placeholder="CNPJ" data-mask="99.999.999/9999-99" required >
											</div>
										</div>
									</div>
										
									<div class="row">				
										<div class="col-lg-12">
											<div class="row">
												<div class="col-lg-6">
													<div class="form-group">
														<label for="inputRazaoSocial">Razão Social<span class="text-danger"> *</span></label>
														<input type="text" id="inputRazaoSocial" name="inputRazaoSocial" class="form-control" placeholder="Razão Social" required >
													</div>
												</div>			
												<div class="col-lg-6">
													<div class="form-group">
														<label for="inputNomeFantasia">Nome Fantasia<span class="text-danger"> *</span></label>
														<input type="text" id="inputNomeFantasia" name="inputNomeFantasia" class="form-control" placeholder="Nome Fantasia" required >
													</div>
												</div>
											</div>
										</div>
									</div>
									
								</div> <!-- media-body -->

								<div style="text-align:center;">
									<div id="visualizar">										
										<img class="ml-3" src="global_assets/images/lamparinas/sem_foto.gif" alt="Sem Foto" style="max-height:200px; border:2px solid #ccc;">
									</div>
									<br>
									<button id="addFoto" class="ml-3 btn btn-lg btn-principal" style="width:90%">Adicionar Foto...</button>	
								</div>
								
							</div> <!-- media -->

							<div class="row">
								<div class="col-lg-12">									
									<h5 class="mb-0 font-weight-semibold">Endereço</h5>
									<br>
									<div class="row">
										<div class="col-lg-1">
											<div class="form-group">
												<label for="inputCep">CEP</label>
												<input type="text" id="inputCep" name="inputCep" class="form-control" placeholder="CEP" data-mask="99999-999">
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
												<select id="cmbEstado" name="cmbEstado" class="form-control form-control-select2">
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

							<div class="row">
								<div class="col-lg-12">									
									<h5 class="mb-0 font-weight-semibold">Contato</h5>
									<br>
									<div class="row">								
										<div class="col-lg-3">
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
										
										<div class="col-lg-2">
											<div class="form-group">
												<label for="inputEmail">E-mail</label>
												<input type="email" id="inputEmail" name="inputEmail" class="form-control" placeholder="E-mail">
											</div>
										</div>
										
										<div class="col-lg-3">
											<div class="form-group">
												<label for="inputSite">Site</label>
												<input type="text" id="inputSite" name="inputSite" class="form-control" placeholder="URL">
											</div>
										</div>										
									</div>
									
									<div class="row">
										<div class="col-lg-12">
											<div class="form-group">
												<label for="txtareaObservacao">Observação</label>
												<textarea rows="5" cols="5" class="form-control" id="txtareaObservacao" name="txtareaObservacao" placeholder="Observação"></textarea>
											</div>
										</div>
									</div>										
								</div>
							</div>
							
							<div class="row" style="margin-top: 10px;">
								<div class="col-lg-12">								
									<div class="form-group">
										<button class="btn btn-lg btn-principal" id="enviar">Incluir</button>
										<a href="empresa.php" class="btn btn-basic" role="button">Cancelar</a>
									</div>
								</div>
							</div>						

						</div>
						<!-- /card-body -->
					</form>	

					<form id="formFoto" method="post" enctype="multipart/form-data" action="upload.php">
						<input type="file" id="imagem" name="imagem" style="display:none;" />
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
