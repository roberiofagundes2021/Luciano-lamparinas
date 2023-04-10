<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Nova Local do Estoque';

include('global_assets/php/conexao.php');

if(isset($_POST['inputNome'])){

	try{

		if (isset($_SESSION['EmpresaId'])){
			$iUnidade = $_POST['cmbUnidade'];
		} else{
			$iUnidade = $_SESSION['UnidadeId'];
		}
		
		$sql = "INSERT INTO LocalEstoque (LcEstNome, LcEstChave, LcEstCep, LcEstEndereco, LcEstNumero, LcEstComplemento, LcEstBairro, LcEstCidade, 
		                    LcEstEstado, LcEstUnidade, LcEstStatus, LcEstUsuarioAtualizador)
				VALUES (:sNome, :sChave, :sCep, :sEndereco, :sNumero, :sComplemento, :sBairro, 
						:sCidade, :sEstado, :iUnidade, :bStatus, :iUsuarioAtualizador)";
		$result = $conn->prepare($sql);
				
		$result->execute(array(
						':sNome' => $_POST['inputNome'],
						':sChave' => formatarChave($_POST['inputNome']),
						':sCep' => $_POST['inputCep'],
						':sEndereco' => $_POST['inputEndereco'],
						':sNumero' => $_POST['inputNumero'],
						':sComplemento' => $_POST['inputComplemento'],
						':sBairro' => $_POST['inputBairro'],
						':sCidade' => $_POST['inputCidade'],
						':sEstado' => $_POST['cmbEstado'],
						':iUnidade' => $iUnidade,
						':bStatus' => 1,
						':iUsuarioAtualizador' => $_SESSION['UsuarId']
						));
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Local do Estoque incluído!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao incluir local do estoque!!!";
		$_SESSION['msg']['tipo'] = "error";	
		
		echo 'Error: ' . $e->getMessage();
	}
	
	irpara("localEstoque.php");
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Local do Estoque</title>

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

	<script type="text/javascript" >

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
			
			//Quando o campo cep perde o foco.
			$("#inputCep").blur(function() {

				$("#cmbEstado").removeClass("form-control-select2");
				
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
								$("#cmbEstado").val(dados.uf);                                
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
						$("#inputCep").val("");
						limpa_formulário_cep();
						alerta("Erro","Formato de CEP inválido.","erro");
					}
				} //end if.
				else {
					//cep sem valor, limpa formulário.
					limpa_formulário_cep();
				}             
				
            });
            




			
			//Valida Registro Duplicado
			$('#enviar').on('click', function(e){
				
				e.preventDefault();

				// subistitui qualquer espaço em branco no campo "CEP" antes de enviar para o banco
				var cep = $("#inputCep").val()
				cep = cep.replace(' ','')
				$("#inputCep").val(cep)
				
				var inputNome  = $('#inputNome').val();
				var cmbUnidade  = $('#cmbUnidade').val();
				
				//remove os espaços desnecessários antes e depois
				inputNome = inputNome.trim();

				if (inputNome == '' || cmbUnidade == ''){
					$( "#formLocalEstoque" ).submit();
					return false;
				}
				
				//Esse ajax está sendo usado para verificar no banco se o registro já existe
				$.ajax({
					type: "POST",
					url: "localEstoqueValida.php",
					data: ('nome='+inputNome+'&unidade='+cmbUnidade),
					success: function(resposta){
						
						if(resposta == 1){
							alerta('Atenção','Esse registro já existe!','error');
							return false;
						}
						
						$( "#formLocalEstoque" ).submit();
					}
				})
			})
		})
	</script>

</head>

<body class="navbar-top <?php if (isset($_SESSION['EmpresaId'])) echo "sidebar-xs"; ?>">

	<?php include_once("topo.php"); ?>	

	<!-- Page content -->
	<div class="page-content">
		
		<?php include_once("menu-left.php"); ?>

		<?php 
			if (isset($_SESSION['EmpresaId'])){ 
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
					
					<form name="formLocalEstoque" id="formLocalEstoque" method="post" class="form-validate-jquery">
						<div class="card-header header-elements-inline">
							<h5 class="text-uppercase font-weight-bold">Cadastrar Novo Local do Estoque</h5>
						</div>
						
						<div class="card-body">								
							<div class="row">
								<?php 
									if (isset($_SESSION['EmpresaId'])){ 
										print('<div class="col-lg-6">');
									} else{
										print('<div class="col-lg-12">');  
									}
								?>
									<div class="form-group">
										<label for="inputNome">Local do Estoque<span class="text-danger"> *</span></label>
										<input type="text" id="inputNome" name="inputNome" class="form-control" placeholder="Local do Estoque" required autofocus>
									</div>
								</div>	

								<?php 
							
									if (isset($_SESSION['EmpresaId'])){
										
										print('
										<div class="col-lg-6">
											<div class="form-group">
												<label for="cmbUnidade">Unidade<span class="text-danger"> *</span></label>
												<select name="cmbUnidade" id="cmbUnidade" class="form-control form-control-select2" required>
													<option value="">Informe uma unidade</option>');
													
													$sql = "SELECT UnidaId, UnidaNome
															FROM Unidade
															JOIN Situacao on SituaId = UnidaStatus															     
															WHERE UnidaEmpresa = " . $_SESSION['EmpresaId'] . " and SituaChave = 'ATIVO'
															ORDER BY UnidaNome ASC";
													$result = $conn->query($sql);
													$rowUnidade = $result->fetchAll(PDO::FETCH_ASSOC);

													foreach ($rowUnidade as $item) {
														print('<option value="' . $item['UnidaId'] . '">' . $item['UnidaNome'] . '</option>');
													}

										print('												
												</select>
											</div>
										</div>
										');
									} else{
										print('<input type="hidden" id="cmbUnidade" value="0" >');
									}
								?>

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
												<select id="cmbEstado" name="cmbEstado" class="form-control"> <!-- retirei isso da class: form-control-select2 para que funcionasse a seleção do texto do estado, além do valor -->
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
							
							<div class="row" style="margin-top: 10px;">
								<div class="col-lg-12">								
									<div class="form-group">
										<button class="btn btn-lg btn-principal" id="enviar">Incluir</button>
										<a href="localEstoque.php" class="btn btn-basic" role="button">Cancelar</a>
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
