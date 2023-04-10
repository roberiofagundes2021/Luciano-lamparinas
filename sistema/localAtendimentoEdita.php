<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Editar Local de Atendimento';

include('global_assets/php/conexao.php');

if(isset($_POST['inputAtendimentoLocalId'])){
	
	$iLocalAtendimento = $_POST['inputAtendimentoLocalId'];
		
	$sql = "SELECT *
			FROM AtendimentoLocal
			WHERE AtLocId = $iLocalAtendimento ";
	$result = $conn->query($sql);
	$row = $result->fetch(PDO::FETCH_ASSOC);
	
	$_SESSION['msg'] = array();
} else {  //Esse else foi criado para se caso o usuário der um REFRESH na página. Nesse caso não terá POST e campos não reconhecerão o $row da consulta acima (daí ele deve ser redirecionado) e se quiser continuar editando terá que clicar no ícone da Grid novamente

	//irpara("localAtendimento.php");
}

if(isset($_POST['inputNome'])){
	
	try{
		
		$sql = "UPDATE AtendimentoLocal SET AtLocNome = :sNome, AtLocCNES= :sCNES, AtLocCor = :sCOR, AtLocCep = :sCep,
		AtLocEndereco = :sEndereco, AtLocNumero = :sNumero, AtLocComplemento = :sComplemento, AtLocBairro = :sBairro, 
		AtLocCidade = :sCidade, AtLocEstado = :sEstado, AtLocUsuarioAtualizador = :iUsuarioAtualizador
		WHERE AtLocId = :iAtendimentoLocal";
		$result = $conn->prepare($sql);
				
		$result->execute(array(
						':sNome' => $_POST['inputNome'],
						':sCNES' => $_POST['inputCNES'],
						':sCOR' => $_POST['inputCor'],
						':sCep' => trim($_POST['inputCep']) == "" ? null : $_POST['inputCep'],
						':sEndereco' => $_POST['inputEndereco'],
						':sNumero' => $_POST['inputNumero'],
						':sComplemento' => $_POST['inputComplemento'],
						':sBairro' => $_POST['inputBairro'],
						':sCidade' => $_POST['inputCidade'],
						':sEstado' => $_POST['cmbEstado'],
						':iUsuarioAtualizador' => $_SESSION['UsuarId'],
						':iAtendimentoLocal' => $_POST['inputAtendimentoLocalId']
						));

		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Local de Atendimento alterado!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao alterar local de atendimento!!!";
		$_SESSION['msg']['tipo'] = "error";		
		
		echo 'Error: ' . $e->getMessage();
	}
	
	irpara("localAtendimento.php");
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Local Atendimento</title>

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
                                $("#cmbEstado").val(dados.uf);
								$("#cmbEstado").find('option:selected').text();
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
            }); //cep
            

			
			//Valida Registro Duplicado
			$('#enviar').on('click', function(e){
				
				e.preventDefault();

				// subistitui qualquer espaço em branco no campo "CEP" antes de enviar para o banco
				var cep = $("#inputCep").val()
				cep = cep.replace(' ','')
				$("#inputCep").val(cep)
				
				var inputNomeNovo = $('#inputNome').val();
				var inputNomeVelho = $('#inputAtendimentoLocalNome').val();
				
				//remove os espaços desnecessários antes e depois
				inputNome = inputNomeNovo.trim();
				
				//Esse ajax está sendo usado para verificar no banco se o registro já existe
				$.ajax({
					type: "POST",
						url: "localAtendimentoValida.php",
						data: ('nomeNovo='+inputNome+'&nomeVelho='+inputNomeVelho),
						success: function(resposta){
						if(resposta == 1){
							alerta('Atenção','Esse registro já existe!','error');
							return false;
						}
						
						$( "#formAtendimentoLocal" ).submit();
					}
				})
			})
		})
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
					
					<form name="formAtendimentoLocal" id="formAtendimentoLocal" method="post" class="form-validate-jquery">
						<div class="card-header header-elements-inline">
							<h5 class="text-uppercase font-weight-bold">Editar Local de Atendimento "<?php echo $row['AtLocNome']; ?>"</h5>
						</div>
						
						<input type="hidden" id="inputAtendimentoLocalId" name="inputAtendimentoLocalId" value="<?php echo $row['AtLocId']; ?>">
						<input type="hidden" id="inputAtendimentoLocalNome" name="inputAtendimentoLocalNome" value="<?php echo $row['AtLocNome']; ?>">
						
						<div class="card-body">								
							<div class="row">
								<div class="col-lg-5">
									<div class="form-group">
										<label for="inputNome">Nome do Local de Atendimento<span class="text-danger"> *</span></label>
										<input type="text" id="inputNome" name="inputNome" class="form-control" placeholder="Local de Atendimento" value="<?php echo $row['AtLocNome']; ?>" required autofocus>
									</div>
								</div>
								<div class="col-lg-5">
									<div class="form-group">
										<label for="inputCNES">CNES</label>
										<input type="text" id="inputCNES" name="inputCNES" class="form-control" placeholder="CNES" value="<?php echo $row['AtLocCNES']; ?>" >
									</div>
								</div>
								<div class="col-lg-2">
									<div class="form-group">
										<label for="inputCor">Cor <span class="text-danger">*</span></label>
										<input type="color" id="inputCor" name="inputCor" class="container" placeholder="Cor" value="<?php echo $row['AtLocCor']; ?>" required>
									</div>
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
												<input type="text" id="inputCep" name="inputCep" class="form-control" placeholder="CEP" value="<?php echo $row['AtLocCep']; ?>" maxLength="8">
											</div>
										</div>
										
										<div class="col-lg-5">
											<div class="form-group">
												<label for="inputEndereco">Endereço</label>
												<input type="text" id="inputEndereco" name="inputEndereco" class="form-control" placeholder="Endereço" value="<?php echo $row['AtLocEndereco']; ?>">
											</div>
										</div>

										<div class="col-lg-1">
											<div class="form-group">
												<label for="inputNumero">Nº</label>
												<input type="text" id="inputNumero" name="inputNumero" class="form-control" placeholder="Número" value="<?php echo $row['AtLocNumero']; ?>">
											</div>
										</div>

										<div class="col-lg-5">
											<div class="form-group">
												<label for="inputComplemento">Complemento</label>
												<input type="text" id="inputComplemento" name="inputComplemento" class="form-control" placeholder="complemento" value="<?php echo $row['AtLocComplemento']; ?>">
											</div>
										</div>
									</div>
									
									<div class="row">
										<div class="col-lg-4">
											<div class="form-group">
												<label for="inputBairro">Bairro</label>
												<input type="text" id="inputBairro" name="inputBairro" class="form-control" placeholder="Bairro" value="<?php echo $row['AtLocBairro']; ?>">
											</div>
										</div>

										<div class="col-lg-5">
											<div class="form-group">
												<label for="inputCidade">Cidade</label>
												<input type="text" id="inputCidade" name="inputCidade" class="form-control" placeholder="Cidade" value="<?php echo $row['AtLocCidade']; ?>">
											</div>
										</div>

										<div class="col-lg-3">
											<div class="form-group">
												<label for="cmbEstado">Estado</label>
												<select id="cmbEstado" name="cmbEstado" class="form-control">
													<option value="#">Selecione um estado</option>
													<option value="AC" <?php if ($row['AtLocEstado'] == 'AC') echo "selected"; ?> >Acre</option>
													<option value="AL" <?php if ($row['AtLocEstado'] == 'AL') echo "selected"; ?> >Alagoas</option>
													<option value="AP" <?php if ($row['AtLocEstado'] == 'AP') echo "selected"; ?> >Amapá</option>
													<option value="AM" <?php if ($row['AtLocEstado'] == 'AM') echo "selected"; ?> >Amazonas</option>
													<option value="BA" <?php if ($row['AtLocEstado'] == 'BA') echo "selected"; ?> >Bahia</option>
													<option value="CE" <?php if ($row['AtLocEstado'] == 'CE') echo "selected"; ?> >Ceará</option>
													<option value="DF" <?php if ($row['AtLocEstado'] == 'DF') echo "selected"; ?> >Distrito Federal</option>
													<option value="ES" <?php if ($row['AtLocEstado'] == 'ES') echo "selected"; ?> >Espírito Santo</option>
													<option value="GO" <?php if ($row['AtLocEstado'] == 'GO') echo "selected"; ?> >Goiás</option>
													<option value="MA" <?php if ($row['AtLocEstado'] == 'MA') echo "selected"; ?> >Maranhão</option>
													<option value="MT" <?php if ($row['AtLocEstado'] == 'MT') echo "selected"; ?> >Mato Grosso</option>
													<option value="MS" <?php if ($row['AtLocEstado'] == 'MS') echo "selected"; ?> >Mato Grosso do Sul</option>
													<option value="MG" <?php if ($row['AtLocEstado'] == 'MG') echo "selected"; ?> >Minas Gerais</option>
													<option value="PA" <?php if ($row['AtLocEstado'] == 'PA') echo "selected"; ?> >Pará</option>
													<option value="PB" <?php if ($row['AtLocEstado'] == 'PB') echo "selected"; ?> >Paraíba</option>
													<option value="PR" <?php if ($row['AtLocEstado'] == 'PR') echo "selected"; ?> >Paraná</option>
													<option value="PE" <?php if ($row['AtLocEstado'] == 'PE') echo "selected"; ?> >Pernambuco</option>
													<option value="PI" <?php if ($row['AtLocEstado'] == 'PI') echo "selected"; ?> >Piauí</option>
													<option value="RJ" <?php if ($row['AtLocEstado'] == 'RJ') echo "selected"; ?> >Rio de Janeiro</option>
													<option value="RN" <?php if ($row['AtLocEstado'] == 'RN') echo "selected"; ?> >Rio Grande do Norte</option>
													<option value="RS" <?php if ($row['AtLocEstado'] == 'RS') echo "selected"; ?> >Rio Grande do Sul</option>
													<option value="RO" <?php if ($row['AtLocEstado'] == 'RO') echo "selected"; ?> >Rondônia</option>
													<option value="RR" <?php if ($row['AtLocEstado'] == 'RR') echo "selected"; ?> >Roraima</option>
													<option value="SC" <?php if ($row['AtLocEstado'] == 'SC') echo "selected"; ?> >Santa Catarina</option>
													<option value="SP" <?php if ($row['AtLocEstado'] == 'SP') echo "selected"; ?> >São Paulo</option>
													<option value="SE" <?php if ($row['AtLocEstado'] == 'SE') echo "selected"; ?> >Sergipe</option>
													<option value="TO" <?php if ($row['AtLocEstado'] == 'TO') echo "selected"; ?> >Tocantins</option>
													<option value="ES" <?php if ($row['AtLocEstado'] == 'ES') echo "selected"; ?> >Estrangeiro</option>
												</select>
											</div>
										</div>
									</div>
								</div>
							</div>
								
							<div class="row" style="margin-top: 10px;">
								<div class="col-lg-12">								
									<div class="form-group">
										<button class="btn btn-lg btn-principal" id="enviar">Alterar</button>
										<a href="localAtendimento.php" class="btn btn-basic">Cancelar</a>
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
