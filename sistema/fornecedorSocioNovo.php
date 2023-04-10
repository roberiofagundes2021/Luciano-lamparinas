<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Novo Sócio';

include('global_assets/php/conexao.php');

if(isset($_POST['inputNome'])){

	try{	
		 
		$sql = "INSERT INTO FornecedorXSocio (FrXSoNome, FrXSoCpf, FrXSoRg, FrXSoCelular, FrXSoEmail, FrXSoFornecedor, FrXSoEmpresa)
				VALUES (:sNome, :sCpf, :sRg, :sCelular, :sEmail, :iFornecedor, :iEmpresa)";
		$result = $conn->prepare($sql);

		$result->execute(array(
						':sNome' => $_POST['inputNome'],
						':sCpf' => limpaCPF_CNPJ($_POST['inputCpf']), 
						':sRg' => $_POST['inputRg'] == '' ? null : $_POST['inputRg'],
						':sCelular' => $_POST['inputCelular'] == '(__) _____-____' ? null : $_POST['inputCelular'],
						':sEmail' => $_POST['inputEmail'] == '' ? null : $_POST['inputEmail'], 
						':iFornecedor' => $_SESSION['idFornecedor'],
						':iEmpresa' => $_SESSION['EmpreId']
						));
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Sócio incluído!!!";
		$_SESSION['msg']['tipo'] = "success";
		

	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao incluir Sócio!!!";
		$_SESSION['msg']['tipo'] = "error";	
		echo 'Error: ' . $e->getMessage();die;
	}
	
	irpara("fornecedorSocio.php");
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Sócio</title>

	<?php include_once("head.php"); ?>
	
	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
	<script src="global_assets/js/plugins/forms/inputs/inputmask.js"></script>
	<script src="global_assets/js/demo_pages/form_layouts.js"></script>
	<script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>
	<!-- /theme JS files -->

	<!-- Validação -->
	<script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
	<script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
	<script src="global_assets/js/demo_pages/form_validation.js"></script>
	

	<script type="text/javascript" >

        $(document).ready(function() {
			
			//Valida Registro Duplicado
			$('#enviar').on('click', function(e){
				
				e.preventDefault();
					
                $( "#formFornecedorSocio" ).submit();
				
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
					
					<form name="formFornecedorSocio" id="formFornecedorSocio" method="post" enctype="multipart/form-data" class="form-validate-jquery">
						<div class="card-header header-elements-inline">
							<h5 class="text-uppercase font-weight-bold">Cadastrar Novo Sócio</h5>
						</div>
					
						<div class="card-body">								
							<div class="row">
								<div class="col-lg-3">
									<div class="form-group">
										<label for="inputNome">Nome<span class="text-danger"> *</span></label>
										<input type="text" id="inputNome" name="inputNome" class="form-control" placeholder="Nome" required autofocus>
									</div>
								</div>
								<div class="col-lg-2">
									<div class="form-group">
										<label for="inputCpf">CPF</label>
										<input type="text" id="inputCpf" name="inputCpf" class="form-control" data-mask="999.999.999-99">
									</div>
								</div>
								<div class="col-lg-2">
									<div class="form-group">
										<label for="inputRg">RG</span></label>
										<input type="text" id="inputRg" name="inputRg" class="form-control" placeholder="RG" >
									</div>
								</div>
								<div class="col-lg-2">
											<div class="form-group">
												<label for="inputCelular">Celular</label>
												<input type="tel" id="inputCelular" name="inputCelular" class="form-control" placeholder="Celular" data-mask="(99) 99999-9999">
											</div>
										</div>
								<div class="col-lg-3">
									<div class="form-group">
										<label for="inputEmail">E-Mail</span></label>
										<input type="text" id="inputEmail" name="inputEmail" class="form-control" placeholder="E-Mail" >
									</div>
								</div>
							</div>	
																	
							<div class="row" style="margin-top: 30px;">
								<div class="col-lg-12">								
									<div class="form-group">
										<button class="btn btn-lg btn-principal" id="enviar">Incluir</button>
										<a href="fornecedorSocio.php" class="btn btn-basic" role="button">Cancelar</a>
									</div>
								</div>
							</div>
						</div>	<!-- /card-body -->
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
