<?php 

include_once("sessao.php");

$_SESSION['PaginaAtual'] = 'Editar Classificação do Atendimento';

include('global_assets/php/conexao.php');


if(isset($_POST['inputAtendimentoClassificacaoId'])){

	$iAtendimentoClassificacao = $_POST['inputAtendimentoClassificacaoId'];
	
	try{
	
		$sql = "SELECT AtClaId, AtClaNome,  AtClaNomePersonalizado, AtClaModelo, AtClaChave, SituaChave
				FROM AtendimentoClassificacao
				JOIN Situacao on SituaId = AtClaStatus
				WHERE AtClaId = $iAtendimentoClassificacao ";
		$result = $conn->query($sql);
		$row = $result->fetch(PDO::FETCH_ASSOC);		

	} catch(PDOException $e) {
		echo 'Error: ' . $e->getMessage();
	}
	
	$_SESSION['msg'] = array();

} else {  //Esse else foi criado para se caso o usuário der um REFRESH na página. Nesse caso não terá POST e campos não reconhecerão o $row da consulta acima (daí ele deve ser redirecionado) e se quiser continuar editando terá que clicar no ícone da Grid novamente
	
	header("Location: atendimentoClassificacao.php");
	//irpara("atendimentoClassificacao.php");
}


if(isset($_POST['inputNomePersonalizado'])){
		
	try{

		$conn->beginTransaction();
		
		$sql = "UPDATE AtendimentoClassificacao SET AtClaNomePersonalizado = :sNomePersonalizado, AtClaModelo = :sModelo, 
		              AtClaUsuarioAtualizador = :iUsuarioAtualizador
				WHERE AtClaId = :iAtendimentoClassificacao ";

		$result = $conn->prepare($sql);
				
		$result->execute(array(
						':sNomePersonalizado' => $_POST['inputNomePersonalizado'],
						':sModelo' => isset($_POST['inputModelo']) ? $_POST['inputModelo'] : $row['AtClaModelo'],
						':iUsuarioAtualizador' => $_SESSION['UsuarId'],
						':iAtendimentoClassificacao' => $_POST['inputAtendimentoClassificacaoId']
						));				
		
		$conn->commit();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Classificação do Atendimento alterado!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {	
		
		$conn->rollback();
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao alterar classificação do atendimento!!!";
		$_SESSION['msg']['tipo'] = "error";	
		
		//$result->debugDumpParams();
		
		echo 'Error: ' . $e->getMessage();		
		exit;
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
	<title>Lamparinas | Classificação do Atendimento</title>

	<?php include_once("head.php"); ?>
	
	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>

	<script src="global_assets/js/demo_pages/form_layouts.js"></script>
	<script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>

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
		
			$('#alterar').on('click', function(e) {

				e.preventDefault();

				$("#formAtendimentoClassificacao").submit();

			})
		
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
					
					<form id="formAtendimentoClassificacao" name="formAtendimentoClassificacao" method="post" class="form-validate-jquery" action="atendimentoClassificacaoEdita.php">
						<div class="card-header header-elements-inline">
							<h5 class="text-uppercase font-weight-bold">Editar Classificação do Atendimento "<?php echo $row['AtClaNome']; ?>"</h5>
						</div>
						
						<input type="hidden" id="inputAtendimentoClassificacaoId" name="inputAtendimentoClassificacaoId" value="<?php echo $row['AtClaId']; ?>" >
						<input type="hidden" id="inputAtendimentoClassificacaoNome" name="inputAtendimentoClassificacaoNome" value="<?php echo $row['AtClaNome']; ?>">
						<input type="hidden" id="inputAtendimentoClassificacaoStatus" name="inputAtendimentoClassificacaoStatus" value="<?php echo $row['SituaChave']; ?>" >
						
						<div class="card-body">
							
							<div class="media">
								
                                <div class="media-body">

                                    <div class="row">

									<?php
										if ($row['AtClaChave'] != null)  {

											print('<div class="col-lg-6">
                                            <div class="form-group">
                                                <label for="inputNome">Título (Sugerido pelo sistema) <span class="text-danger">*</span></label>
                                                <input type="text" id="inputNome" name="inputNome" class="form-control" placeholder="Título Eletivo" value=' .$row['AtClaNome']. ' readOnly>
                                            </div>                                                                                                        
                                        </div> ');
										}
										?>
										<div class="col-lg-6">
											<div class="form-group">
												<?php
													if ($row['AtClaChave'] != null)  {

														print('<label for="inputNomePersonalizado">Título (Nome personalizado) </label> 
															   <input type="text" id="inputNomePersonalizado" name="inputNomePersonalizado" class="form-control" placeholder="Título " value='.$row['AtClaNomePersonalizado'].'>
														');
													}else{

														print('<label for="inputNomePersonalizado">Título <span class="text-danger">*</span></label> 
														       <input type="text" id="inputNomePersonalizado" name="inputNomePersonalizado" class="form-control" placeholder="Título " value='.$row['AtClaNomePersonalizado'].' required>
														');
													}
												?>
                                                
                                            </div>
                                        </div>
                                                                                                                            
                                    </div>

									<br>

                                    <div class="row" style="text-align:center;">
										<div class="col-lg-3">
											<div class="form-group">							
												<div class="form-check form-check-inline">
													<label class="form-check-label">
														<input type="radio" id="inputModelo" name="inputModelo" value="E" class="form-input-styled" <?php if ($row['AtClaModelo'] == 'E') echo "checked"; ?> <?php if ($row['AtClaChave'] != null) echo "disabled"; ?>>
														Eletivo
													</label>
												</div>
											</div>
										</div>
										<div class="col-lg-3">
											<div class="form-group">
												<div class="form-check form-check-inline">
													<label class="form-check-label">
														<input type="radio" id="inputModelo" name="inputModelo" value="A" class="form-input-styled" <?php if ($row['AtClaModelo'] == 'A') echo "checked"; ?> <?php if ($row['AtClaChave'] != null) echo "disabled"; ?>>
														Ambulatorial
													</label>
												</div>
											</div>
										</div>
										<div class="col-lg-3">
											<div class="form-group">		
												<div class="form-check form-check-inline">
													<label class="form-check-label">
														<input type="radio" id="inputModelo" name="inputModelo" value="H" class="form-input-styled" <?php if ($row['AtClaModelo'] == 'H') echo "checked"; ?> <?php if ($row['AtClaChave'] != null) echo "disabled"; ?>>
														Hospitalar
													</label>
												</div>									
											</div>			
										</div>
										<div class="col-lg-3">
											<div class="form-group">		
												<div class="form-check form-check-inline">
													<label class="form-check-label">
														<input type="radio" id="inputModelo" name="inputModelo" value="O" class="form-input-styled" <?php if ($row['AtClaModelo'] == 'O') echo "checked"; ?> <?php if ($row['AtClaChave'] != null) echo "disabled"; ?>>
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
									<?php
										if ($_POST['inputPermission']) {
											echo '<button id="alterar" class="btn btn-lg btn-principal" type="submit">Alterar</button>';
										}
									?>	
										<a href="atendimentoClassificacao.php" class="btn btn-basic" role="button">Cancelar</a>
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
