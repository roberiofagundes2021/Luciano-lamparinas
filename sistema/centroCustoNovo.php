<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Novo Centro de Custo';

include('global_assets/php/conexao.php');

if(isset($_POST['inputNome'])){

	try{
		
		$sql = "INSERT INTO CentroCusto (CnCusCodigo, CnCusNome, CnCusDetalhamento, CnCusStatus, CnCusUsuarioAtualizador, CnCusUnidade)
				VALUES (:iCodigo, :sNome, :sDetalhamento, :iStatus, :iUsuarioAtualizador, :iUnidade)";
		$result = $conn->prepare($sql);
				
		$result->execute(array(
						':iCodigo' => $_POST['inputCodigo'],
						':sNome' => $_POST['inputNome'],
						':sDetalhamento' => $_POST['txtDetalhamento'],
						':iStatus' => 1,
						':iUsuarioAtualizador' => $_SESSION['UsuarId'],
						':iUnidade' => $_SESSION['UnidadeId'],
						));
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Centro de Custo incluído!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao incluir Centro de Custo!!!";
		$_SESSION['msg']['tipo'] = "error";	
		
		echo 'Error: ' . $e->getMessage(); die;
	}
	
	irpara("centroCusto.php");
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Centro de Custo</title>

	<?php include_once("head.php"); ?>

	<!-- Validação -->
	<script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
	<script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
	<script src="global_assets/js/demo_pages/form_validation.js"></script>
	
	<script type="text/javascript" >

        $(document).ready(function() {
			
			//Valida Registro Duplicado
			$('#enviar').on('click', function(e){

				e.preventDefault();
				
				var inputNome = $('#inputNome').val();
				
				//remove os espaços desnecessários antes e depois
				inputNome = inputNome.trim();
				
				//Se o usuário preencheu com espaços em branco ou não preencheu nada
				if (inputNome == ''){
					$('#inputNome').val('');
					$('#formCentroCusto').submit();
				} else{
					//Esse ajax está sendo usado para verificar no banco se o registro já existe
					$.ajax({
						type: "POST",
						url: "centroCustoValida.php",
						data: ('nome='+inputNome),
						success: function(resposta){
							
							if(resposta == 1){
								alerta('Atenção','Esse registro já existe!','error');
								return false;
							}
							
							$('#formCentroCusto').submit();
						}
					})
				}				
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
					
					<form id="formCentroCusto" name="formCentroCusto" method="post" class="form-validate-jquery">
						<div class="card-header header-elements-inline">
							<h5 class="text-uppercase font-weight-bold">Cadastrar Novo Centro de Custo</h5>
						</div>
						
						<div class="card-body">								
							<div class="row">
								<div class="col-lg-2">
									<div class="form-group">
										<label for="inputCodigo">Código<span class="text-danger"> *</span></label>
										<input type="text" id="inputCodigo" name="inputCodigo" class="form-control" placeholder="Código" required autofocus>
									</div>
								</div>

								<div class="col-lg-10">
									<div class="form-group">
										<label for="inputNome">Título<span class="text-danger"> *</span></label>
										<input type="text" id="inputNome" name="inputNome" class="form-control" placeholder="Centro de Custo" required>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-lg-12">
									<label for="txtDetalhamento">Detalhamento</label>
									<textarea id="txtDetalhamento" name="txtDetalhamento" class="form-control" placeholder="Detalhamento do Centro de Custo" rows="7" cols="5" ></textarea>
								</div>
							</div>							
															
							<div class="row" style="margin-top: 30px;">
								<div class="col-lg-12">								
									<div class="form-group">
										<button class="btn btn-lg btn-principal" id="enviar">Incluir</button>
										<a href="centroCusto.php" class="btn btn-basic" role="button">Cancelar</a>
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
