<?php 

include_once("sessao.php");

$_SESSION['PaginaAtual'] = 'Editar Classificação de Risco';

include('global_assets/php/conexao.php');


if(isset($_POST['inputClassificacaoRiscoId'])){

	$iClassificacaoRisco = $_POST['inputClassificacaoRiscoId'];
	
	try{
	
		$sql = "SELECT AtClRId, AtClRNome, AtClRNomePersonalizado, AtClRTempo, AtClRCor, AtClRDeterminantes, SituaChave
				FROM AtendimentoClassificacaoRisco
				JOIN Situacao on SituaId = AtClRStatus
				WHERE AtClRId = $iClassificacaoRisco ";
		$result = $conn->query($sql);
		$row = $result->fetch(PDO::FETCH_ASSOC);		

	} catch(PDOException $e) {
		echo 'Error: ' . $e->getMessage();
	}
	
	$_SESSION['msg'] = array();

} else {  //Esse else foi criado para se caso o usuário der um REFRESH na página. Nesse caso não terá POST e campos não reconhecerão o $row da consulta acima (daí ele deve ser redirecionado) e se quiser continuar editando terá que clicar no ícone da Grid novamente
	
	header("Location: classificacaoRisco.php");
	//irpara("classificacaoRisco.php");
}


if(isset($_POST['inputNome'])){
		
	try{

		$conn->beginTransaction();
		
		$sql = "UPDATE AtendimentoClassificacaoRisco SET AtClRNome = :sNome, AtClRNomePersonalizado = :sNomePersonalizado, AtClRTempo = :sTempo, 
		               AtClRDeterminantes = :sDeterminantes, AtClRUsuarioAtualizador = :iUsuarioAtualizador
				WHERE AtClRId = :iClassificacaoRisco ";

		$result = $conn->prepare($sql);
				
		$result->execute(array(
						':sNome' => $_POST['inputNome'],
						':sNomePersonalizado' => $_POST['inputNomePersonalizado'],
						':sTempo' => $_POST['inputTempo'],
						':sDeterminantes' => $_POST['txtDeterminantes'],
						':iUsuarioAtualizador' => $_SESSION['UsuarId'],
						':iClassificacaoRisco' => $_POST['inputClassificacaoRiscoId']
						));				
		
		$conn->commit();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Classificação de Risco alterado!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {	
		
		$conn->rollback();
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao alterar classificação de risco!!!";
		$_SESSION['msg']['tipo'] = "error";	
		
		//$result->debugDumpParams();
		
		echo 'Error: ' . $e->getMessage();		
		exit;
	}
	
	irpara("classificacaoRisco.php");
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Classificação de Risco</title>

	<?php include_once("head.php"); ?>
	
	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>

	<script src="global_assets/js/demo_pages/form_layouts.js"></script>
	<script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>

	<script src="global_assets/js/plugins/editors/summernote/summernote.min.js"></script>

	<!-- Validação -->
	<script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
	<script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
	<script src="global_assets/js/demo_pages/form_validation.js"></script>
	<!-- /theme JS files -->	

	<!-- Adicionando Javascript -->
    <script type="text/javascript" >

		$(document).ready(function() {

			$('#summernote').summernote();

			$('#enviar').on('click', function(e){
				e.preventDefault();
				$( "formClassificacaoRisco" ).submit();
			})
		}); //document.ready

		
		

        $(document).ready(function() {	
		
		
			$('#alterar').on('click', function(e) {

				e.preventDefault();

				$("#formClassificacaoRisco").submit();

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
					
					<form id="formClassificacaoRisco" name="formClassificacaoRisco" method="post" class="form-validate-jquery" action="classificacaoRiscoEdita.php">
						<div class="card-header header-elements-inline">
							<h5 class="text-uppercase font-weight-bold">Editar Classificação de Risco "<?php echo $row['AtClRNome']; ?>"</h5>
						</div>
						
						<input type="hidden" id="inputClassificacaoRiscoId" name="inputClassificacaoRiscoId" value="<?php echo $row['AtClRId']; ?>" >
						<input type="hidden" id="inputProtocoloNome" name="inputProtocoloNome" value="<?php echo $row['AtClRNome']; ?>">
						<input type="hidden" id="inputClassificacaoRiscoStatus" name="inputClassificacaoRiscoStatus" value="<?php echo $row['SituaChave']; ?>" >
						
						<div class="card-body">
							
							<div class="media">
								
                                <div class="media-body">

                                    <div class="row">

                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <label for="inputNome">Nome <span class="text-danger">*</span></label>
                                                <input type="text" id="inputNome" name="inputNome" class="form-control" placeholder="Nome" value="<?php echo $row['AtClRNome']; ?>" readonly>
                                            </div>
                                        </div>
										<div class="col-lg-4">
											<div class="form-group">
												<label for="inputNomePersonalizado">Classificação de Risco (nome personalizado)</label>
												<input type="text" id="inputNomePersonalizado" name="inputNomePersonalizado" class="form-control" placeholder="Título personalizado" value="<?php echo $row['AtClRNomePersonalizado']; ?>">
											</div>
										</div>
										<div class="col-lg-2">
                                            <div class="form-group">
                                                <label for="inputTempo">Tempo (min)<span class="text-danger">*</span></label>
                                                <input type="number" id="inputTempo" name="inputTempo" class="form-control" placeholder="Tempo" value="<?php echo $row['AtClRTempo']; ?>" required>
                                            </div>
                                        </div>
										<div class="col-lg-2">
											<label for="inputCor">Cor<span class="text-danger">*</span></label>
											<div class="form-group" style="margin-left: 10px; margin-Top: 5px; height: 40px; width: 40px; background-color: <?php echo $row['AtClRCor']; ?>; border-radius: 50px;" >
                                            </div>
                                        </div>

										
										
                                        
                                       
                                                                                                                                            
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="form-group">
                                                <label for="txtDeterminantes">Determinantes Gerais</label>
                                                <textarea rows="5" cols="5" class="form-control" id="summernote" name="txtDeterminantes" placeholder="Determinantes"><?php echo $row['AtClRDeterminantes']; ?></textarea>
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
										<a href="classificacaoRisco.php" class="btn btn-basic" role="button">Cancelar</a>
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
