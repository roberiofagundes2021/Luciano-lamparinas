<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Editar Local do Estoque';

include('global_assets/php/conexao.php');

if(isset($_POST['inputLocalEstoqueId'])){
	
	$iLocalEstoque = $_POST['inputLocalEstoqueId'];
        			
	$sql = "SELECT LcEstId, LcEstNome, LcEstUnidade, LcEstCep, LcEstEndereco, LcEstNumero, 
				   LcEstComplemento, LcEstBairro, LcEstCidade, LcEstEstado
			FROM LocalEstoque
			WHERE LcEstId = $iLocalEstoque ";
	$result = $conn->query($sql);
	$row = $result->fetch(PDO::FETCH_ASSOC);
			
	$_SESSION['msg'] = array();
} else {  //Esse else foi criado para se caso o usuário der um REFRESH na página. Nesse caso não terá POST e campos não reconhecerão o $row da consulta acima (daí ele deve ser redirecionado) e se quiser continuar editando terá que clicar no ícone da Grid novamente

	irpara("localEstoque.php");
}

if(isset($_POST['inputNome'])){
	
	try{

		if (isset($_SESSION['EmpresaId'])){

			$sql = "UPDATE LocalEstoque SET LcEstNome = :sNome, LcEstChave = :sChave, LcEstCep = :sCep, LcEstEndereco = :sEndereco, 
											LcEstNumero = :sNumero, LcEstComplemento = :sComplemento, LcEstBairro = :sBairro, 
											LcEstCidade = :sCidade, LcEstEstado = :sEstado, LcEstUnidade = :iUnidade, LcEstUsuarioAtualizador = :iUsuarioAtualizador
					WHERE LcEstId = :iLocalEstoque";
			$result = $conn->prepare($sql);
					
			$result->execute(array(
							':sNome' => $_POST['inputNome'],
							':sChave' => formatarChave($_POST['inputNome']),
							':sCep' => trim($_POST['inputCep']) == "" ? null : $_POST['inputCep'],
							':sEndereco' => $_POST['inputEndereco'],
							':sNumero' => $_POST['inputNumero'],
							':sComplemento' => $_POST['inputComplemento'],
							':sBairro' => $_POST['inputBairro'],
							':sCidade' => $_POST['inputCidade'],
							':sEstado' => $_POST['cmbEstado'],
							':iUnidade' => $_POST['cmbUnidade'],
							':iUsuarioAtualizador' => $_SESSION['UsuarId'],
							':iLocalEstoque' => $_POST['inputLocalEstoqueId']
							));

		} else {
			$sql = "UPDATE LocalEstoque SET LcEstNome = :sNome, LcEstChave = :sChave, LcEstCep = :sCep, LcEstEndereco = :sEndereco, 
											LcEstNumero = :sNumero, LcEstComplemento = :sComplemento, LcEstBairro = :sBairro, 
											LcEstCidade = :sCidade, LcEstEstado = :sEstado, LcEstUsuarioAtualizador = :iUsuarioAtualizador
					WHERE LcEstId = :iLocalEstoque";
			$result = $conn->prepare($sql);
					
			$result->execute(array(
							':sNome' => $_POST['inputNome'],
							':sChave' => formatarChave($_POST['inputNome']),
							':sCep' => trim($_POST['inputCep']) == "" ? null : $_POST['inputCep'],
							':sEndereco' => $_POST['inputEndereco'],
							':sNumero' => $_POST['inputNumero'],
							':sComplemento' => $_POST['inputComplemento'],
							':sBairro' => $_POST['inputBairro'],
							':sCidade' => $_POST['inputCidade'],
							':sEstado' => $_POST['cmbEstado'],
							':iUsuarioAtualizador' => $_SESSION['UsuarId'],
							':iLocalEstoque' => $_POST['inputLocalEstoqueId']
							));
		}
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Local do Estoque alterado!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao alterar local do estoque!!!";
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
									$("#inputCep").val("");
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
				// subistitui qualquer espaço em branco no campo "CEP" antes de enviar para o banco
				var cep = $("#inputCep").val()
				cep = cep.replace(' ','')
				$("#inputCep").val(cep)
				// console.log($("#inputCep").val())
				
				e.preventDefault();
				
				var inputNomeNovo  = $('#inputNome').val();
				var inputNomeVelho = $('#inputLocalEstoqueNome').val();
				var cmbUnidade   = $('#cmbUnidade').val();
				
				//remove os espaços desnecessários antes e depois
				inputNomeNovo = inputNomeNovo.trim();

				if (inputNomeNovo == '' || cmbUnidade == ''){
					$( "#formLocalEstoque" ).submit();
					return false;
				}				
				
				//Esse ajax está sendo usado para verificar no banco se o registro já existe
				$.ajax({
					type: "POST",
					url: "localEstoqueValida.php",
					data: ('nomeNovo='+inputNomeNovo+'&nomeVelho='+inputNomeVelho+'&unidade='+cmbUnidade),
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
							<h5 class="text-uppercase font-weight-bold">Editar Local do Estoque "<?php echo $row['LcEstNome']; ?>"</h5>
						</div>
						
						<input type="hidden" id="inputLocalEstoqueId" name="inputLocalEstoqueId" value="<?php echo $row['LcEstId']; ?>" >
						<input type="hidden" id="inputLocalEstoqueNome" name="inputLocalEstoqueNome" value="<?php echo $row['LcEstNome']; ?>" >
						
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
										<input type="text" id="inputNome" name="inputNome" class="form-control" placeholder="Local do Estoque" value="<?php echo $row['LcEstNome']; ?>" required autofocus>
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
														$seleciona = $item['UnidaId'] == $row['LcEstUnidade'] ? "selected" : "";
														print('<option value="'. $item['UnidaId'].'" '. $seleciona .'>' . $item['UnidaNome'] . '</option>');
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
												<input type="text" id="inputCep" name="inputCep" class="form-control" placeholder="CEP" value="<?php echo $row['LcEstCep']; ?>" maxLength="8">
											</div>
										</div>
										
										<div class="col-lg-5">
											<div class="form-group">
												<label for="inputEndereco">Endereço</label>
												<input type="text" id="inputEndereco" name="inputEndereco" class="form-control" placeholder="Endereço" value="<?php echo $row['LcEstEndereco']; ?>">
											</div>
										</div>

										<div class="col-lg-1">
											<div class="form-group">
												<label for="inputNumero">Nº</label>
												<input type="text" id="inputNumero" name="inputNumero" class="form-control" placeholder="Número" value="<?php echo $row['LcEstNumero']; ?>">
											</div>
										</div>

										<div class="col-lg-5">
											<div class="form-group">
												<label for="inputComplemento">Complemento</label>
												<input type="text" id="inputComplemento" name="inputComplemento" class="form-control" placeholder="complemento" value="<?php echo $row['LcEstComplemento']; ?>">
											</div>
										</div>
									</div>
									
									<div class="row">
										<div class="col-lg-4">
											<div class="form-group">
												<label for="inputBairro">Bairro</label>
												<input type="text" id="inputBairro" name="inputBairro" class="form-control" placeholder="Bairro" value="<?php echo $row['LcEstBairro']; ?>">
											</div>
										</div>

										<div class="col-lg-5">
											<div class="form-group">
												<label for="inputCidade">Cidade</label>
												<input type="text" id="inputCidade" name="inputCidade" class="form-control" placeholder="Cidade" value="<?php echo $row['LcEstCidade']; ?>">
											</div>
										</div>

										<div class="col-lg-3">
											<div class="form-group">
												<label for="cmbEstado">Estado</label>
												<select id="cmbEstado" name="cmbEstado" class="form-control">
													<option value="#">Selecione um estado</option>
													<option value="AC" <?php if ($row['LcEstEstado'] == 'AC') echo "selected"; ?> >Acre</option>
													<option value="AL" <?php if ($row['LcEstEstado'] == 'AL') echo "selected"; ?> >Alagoas</option>
													<option value="AP" <?php if ($row['LcEstEstado'] == 'AP') echo "selected"; ?> >Amapá</option>
													<option value="AM" <?php if ($row['LcEstEstado'] == 'AM') echo "selected"; ?> >Amazonas</option>
													<option value="BA" <?php if ($row['LcEstEstado'] == 'BA') echo "selected"; ?> >Bahia</option>
													<option value="CE" <?php if ($row['LcEstEstado'] == 'CE') echo "selected"; ?> >Ceará</option>
													<option value="DF" <?php if ($row['LcEstEstado'] == 'DF') echo "selected"; ?> >Distrito Federal</option>
													<option value="ES" <?php if ($row['LcEstEstado'] == 'ES') echo "selected"; ?> >Espírito Santo</option>
													<option value="GO" <?php if ($row['LcEstEstado'] == 'GO') echo "selected"; ?> >Goiás</option>
													<option value="MA" <?php if ($row['LcEstEstado'] == 'MA') echo "selected"; ?> >Maranhão</option>
													<option value="MT" <?php if ($row['LcEstEstado'] == 'MT') echo "selected"; ?> >Mato Grosso</option>
													<option value="MS" <?php if ($row['LcEstEstado'] == 'MS') echo "selected"; ?> >Mato Grosso do Sul</option>
													<option value="MG" <?php if ($row['LcEstEstado'] == 'MG') echo "selected"; ?> >Minas Gerais</option>
													<option value="PA" <?php if ($row['LcEstEstado'] == 'PA') echo "selected"; ?> >Pará</option>
													<option value="PB" <?php if ($row['LcEstEstado'] == 'PB') echo "selected"; ?> >Paraíba</option>
													<option value="PR" <?php if ($row['LcEstEstado'] == 'PR') echo "selected"; ?> >Paraná</option>
													<option value="PE" <?php if ($row['LcEstEstado'] == 'PE') echo "selected"; ?> >Pernambuco</option>
													<option value="PI" <?php if ($row['LcEstEstado'] == 'PI') echo "selected"; ?> >Piauí</option>
													<option value="RJ" <?php if ($row['LcEstEstado'] == 'RJ') echo "selected"; ?> >Rio de Janeiro</option>
													<option value="RN" <?php if ($row['LcEstEstado'] == 'RN') echo "selected"; ?> >Rio Grande do Norte</option>
													<option value="RS" <?php if ($row['LcEstEstado'] == 'RS') echo "selected"; ?> >Rio Grande do Sul</option>
													<option value="RO" <?php if ($row['LcEstEstado'] == 'RO') echo "selected"; ?> >Rondônia</option>
													<option value="RR" <?php if ($row['LcEstEstado'] == 'RR') echo "selected"; ?> >Roraima</option>
													<option value="SC" <?php if ($row['LcEstEstado'] == 'SC') echo "selected"; ?> >Santa Catarina</option>
													<option value="SP" <?php if ($row['LcEstEstado'] == 'SP') echo "selected"; ?> >São Paulo</option>
													<option value="SE" <?php if ($row['LcEstEstado'] == 'SE') echo "selected"; ?> >Sergipe</option>
													<option value="TO" <?php if ($row['LcEstEstado'] == 'TO') echo "selected"; ?> >Tocantins</option>
													<option value="ES" <?php if ($row['LcEstEstado'] == 'ES') echo "selected"; ?> >Estrangeiro</option>
												</select>
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
											echo '<button class="btn btn-lg btn-principal" id="enviar">Alterar</button>';
										}
									?>	
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
