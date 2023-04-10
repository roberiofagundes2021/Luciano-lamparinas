<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Nova Classificação do Atendimento';

include('global_assets/php/conexao.php');

if(isset($_POST['inputNomePersonalizado'])){
	
	try{
		
		$sql = "INSERT INTO AtendimentoClassificacao (AtClaNome, AtClaNomePersonalizado, AtClaModelo, 
						    AtClaStatus, AtClaUsuarioAtualizador, AtClaUnidade) 
				VALUES ( :sNome, :sNomePersonalizado, :sModelo, :bStatus, :iUsuarioAtualizador, :iUnidade)";
		$result = $conn->prepare($sql);

		$result->execute(array(
						':sNome' => $_POST['inputNomePersonalizado'],
						':sNomePersonalizado' => $_POST['inputNomePersonalizado'],
						':sModelo' => $_POST['inputModelo'],
						':bStatus' => 1,
						':iUsuarioAtualizador' => $_SESSION['UsuarId'],
						':iUnidade' => $_SESSION['UnidadeId']
						));
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Classificação do Atendimento incluída!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {		
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao incluir classificação do atendimento !!!";
		$_SESSION['msg']['tipo'] = "error";	
		
		echo 'Error2: ' . $e->getMessage();die;
		
	}
	
	irpara("atendimentoClassificacao.php");
} 

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Classificação do Atendimento </title>

	<?php include_once("head.php"); ?>
	
	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>

	<script src="global_assets/js/demo_pages/form_layouts.js"></script>
	<script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>
	
	<!--<script src="http://malsup.github.com/jquery.form.js"></script>-->

	<script src="global_assets/js/plugins/media/fancybox.min.js"></script>

	<!-- Validação -->
	<script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
	<script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
	<script src="global_assets/js/demo_pages/form_validation.js"></script>
	<!-- /theme JS files -->

	<!-- Adicionando Javascript -->
    <script type="text/javascript" >

        $(document).ready(function() {	 	
			
			//Aqui sou obrigado a instanciar a utilização do fancybox
			$(".fancybox").fancybox({
				// options
			});	
			
			$('#enviar').on('click', function(e){
				
				e.preventDefault();	
				
				$("#formAtendimentoClassificacao").submit();
				
			}); 
		});	
		
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
					
					<form id="formAtendimentoClassificacao" name="formAtendimentoClassificacao" method="post" class="form-validate-jquery">
						<div class="card-header header-elements-inline">
							<h5 class="text-uppercase font-weight-bold">Cadastrar Nova Classificação do Atendimento</h5>
						</div>
						
						<div class="card-body">								
							
							<div class="media">								
								
								<div class="media-body">
									<div class="row">	
										<div class="col-lg-6">
											<div class="form-group">
												<label for="inputNomePersonalizado">Título<span class="text-danger">*</span></label>
												<input type="text" id="inputNomePersonalizado" name="inputNomePersonalizado" class="form-control" placeholder="Título" required>
											</div>
										</div>
										
									</div>

									<br>
									
									<div class="row" style="text-align:center">
										<div class="col-lg-3">
											<div class="form-group">								
												<div class="form-check form-check-inline">
													<label class="form-check-label" >
														<input type="radio" id="inputModelo" name="inputModelo" value="E" class="form-input-styled" checked>
														Eletivo
													</label>
												</div>
											</div>	
										</div>	
										<div class="col-lg-3">
											<div class="form-group">			
												<div class="form-check form-check-inline">
													<label class="form-check-label">
														<input type="radio" id="inputModelo" name="inputModelo" value="A" class="form-input-styled" >
														Ambulatorial
													</label>
												</div>
											</div>
										</div>
										<div class="col-lg-3">
											<div class="form-group">			
												<div class="form-check form-check-inline">
													<label class="form-check-label">
														<input type="radio" id="inputModelo" name="inputModelo" value="H" class="form-input-styled" >
														Hospitalar
													</label> 
												</div>									
											</div>			
										</div>
										<div class="col-lg-3">
											<div class="form-group">			
												<div class="form-check form-check-inline">
													<label class="form-check-label">
														<input type="radio" id="inputModelo" name="inputModelo" value="O" class="form-input-styled" >
														Odontológico
													</label> 
												</div>									
											</div>			
										</div>
										
										<div class="col-lg-3" style="text-align:center;">
											<div>										
												<a href="global_assets/images/atendimentoClassificacao/logo-lamparinas.jpg" class="fancybox">
													<img class="ml-3" src="global_assets/images/atendimentoClassificacao/logo-lamparinas.jpg" style="max-height:250px; border:2px solid #ccc;" alt="Logo Lamparinas">
												</a>
											</div>
										</div>
										<div class="col-lg-3" style="text-align:center;">
											<div>	
												<a href="global_assets/images/atendimentoClassificacao/logo-lamparinas.jpg" class="fancybox">									
													<img class="ml-3" src="global_assets/images/atendimentoClassificacao/logo-lamparinas.jpg" style="max-height:250px; border:2px solid #ccc;" alt="Logo Lamparinas">
												</a>
											</div>
										</div>
										<div class="col-lg-3" style="text-align:center;">
											<div>		
												<a href="global_assets/images/atendimentoClassificacao/logo-lamparinas.jpg" class="fancybox">								
													<img class="ml-3" src="global_assets/images/atendimentoClassificacao/logo-lamparinas.jpg" style="max-height:250px; border:2px solid #ccc;" alt="Logo Lamparinas">
												</a>
											</div>		
										</div>
										<div class="col-lg-3" style="text-align:center;">
											<div>		
												<a href="global_assets/images/atendimentoClassificacao/logo-lamparinas.jpg" class="fancybox">								
													<img class="ml-3" src="global_assets/images/atendimentoClassificacao/logo-lamparinas.jpg" style="max-height:250px; border:2px solid #ccc;" alt="Logo Lamparinas">
												</a>
											</div>		
										</div>
									</div>
																		
								</div> <!-- media-body -->
								
							</div> <!-- media -->

							<br>
							<div class="row" style="margin-top: 40px;">
								<div class="col-lg-12">								
									<div class="form-group">
										<button class="btn btn-lg btn-principal" id="enviar">Incluir</button>
										<a href="atendimentoClassificacao.php" class="btn btn-lg" role="button">Cancelar</a>
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
