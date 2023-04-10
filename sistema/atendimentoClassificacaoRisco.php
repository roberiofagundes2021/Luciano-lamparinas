<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Atestado Médico';

include('global_assets/php/conexao.php'); 

	$iAtendimentoId = isset($_POST['iAtendimentoId'])?$_POST['iAtendimentoId']:null;

	if(!$iAtendimentoId){
		irpara("atendimentoEletivoListagem.php");
	}

	$sql = "SELECT AtendId, AtendClassificacaoRisco, AtClRId, AtClRNome, AtClRTempo, AtClRCor, AtClRDeterminantes
			FROM Atendimento
			LEFT JOIN AtendimentoClassificacaoRisco ON AtClRId = AtendClassificacaoRisco
			WHERE AtendUnidade = ". $_SESSION['UnidadeId'] ." AND AtendId = ". $iAtendimentoId ." 
			ORDER BY AtClRNome ASC";
	$result = $conn->query($sql);
	$row = $result->fetch(PDO::FETCH_ASSOC);

	$borderColor = "";
	
	switch($row['AtClRCor']){
		case '#ff630f':$borderColor='2px solid #cd5819';break;
		case '#fa0000':$borderColor='2px solid #c10000';break;
		case '#fbff00':$borderColor='2px solid #adb003';break;
		case '#00ff1e':$borderColor='2px solid #2db93e';break;
		case '#0008ff':$borderColor='2px solid #010579';break;
		default: $borderColor = '2px solid #FFFF';break;
	}	

	if(isset($_POST['cmbClassificacaoRisco'])){
		try{
			$aClassificacaoRisco = explode("$", $_POST['cmbClassificacaoRisco']);
			$iClassificacaoRisco = $aClassificacaoRisco[0];
				
			$sql = "UPDATE Atendimento SET AtendClassificacaoRisco = :sClassificacaoRisco, AtendUsuarioAtualizador = :iUsuarioAtualizador
					WHERE AtendId = :iAtendimento";
			$result = $conn->prepare($sql);
					
			$result->execute(array(
							':sClassificacaoRisco' => $iClassificacaoRisco,
							':iUsuarioAtualizador' => $_SESSION['UsuarId'],
							':iAtendimento' => $iAtendimentoId
							));

			$_SESSION['msg']['titulo'] = "Sucesso";
			$_SESSION['msg']['mensagem'] = "Classificacao de Risco incluído!!!";
			$_SESSION['msg']['tipo'] = "success";
			
		} catch(PDOException $e) {
			
			$_SESSION['msg']['titulo'] = "Erro";
			$_SESSION['msg']['mensagem'] = "Erro ao incluir Classificacao de Risco!!!";
			$_SESSION['msg']['tipo'] = "error";		
			
			echo 'Error: ' . $e->getMessage();
		}

		irpara("atendimentoEletivoListagem.php");
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
	
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
	<script src="global_assets/js/demo_pages/form_select2.js"></script>

	<!-- Validação -->
	<script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
	<script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
	<script src="global_assets/js/demo_pages/form_validation.js"></script>	
	
	<script type="text/javascript">

		$(document).ready(function() {

			//Ao informar a ClassificacaoRisco , trazer os demais dados dele (tempo, cor e determinantes)
			$('#cmbClassificacaoRisco').on('change', function(e) { 

				var ClassificacaoRisco = $('#cmbClassificacaoRisco').val();
				var Classif = ClassificacaoRisco.split('$');

				const cor = Classif[2];
				switch (cor) {
					case '#ff630f':borderColor='2px solid #cd5819';break;
					case '#fa0000':borderColor='2px solid #c10000';break;
					case '#fbff00':borderColor='2px solid #adb003';break;
					case '#00ff1e':borderColor='2px solid #2db93e';break;
					case '#0008ff':borderColor='2px solid #010579';break;
					default: borderColor = '2px solid #FFFF';break;
				}

				$('#inputTempo').val(Classif[1]);
				$('#inputCor').css({
					"background-color": Classif[2],
					"border": borderColor
				});
				$('#txtDeterminantes').val(Classif[3]);
			});

			$('#enviar').on('click', function(e){
				
				e.preventDefault();

				$( "#formClassificacaoRisco" ).submit()
			})
			
		}); //document.ready
	</script>

</head>

<body class="navbar-top">

	<?php include_once("topo.php"); ?>	

	<!-- Page content -->
	<div class="page-content">
		
		<?php
			include_once("menu-left.php");
		?>

		<!-- Main content -->
		<div class="content-wrapper">

			<?php include_once("cabecalho.php"); ?>	

			<!-- Content area -->
			<div class="content">

				<!-- Info blocks -->
				<div class="card">
					
					<form id="formClassificacaoRisco" method="post" action="atendimentoClassificacaoRisco.php" class="form-validate-jquery">
						<div><input type="hidden" id="iAtendimentoId" name="iAtendimentoId" value="<?php echo $iAtendimentoId ?>"/></div>
		
						<div class="card-header header-elements-inline"> <h5 class="text-uppercase font-weight-bold"> Classificação de Risco</h5></div>
						
						<div class="card-body">								
							
							<div class="media">								
								
								<div class="media-body"> 
									<div class="row">	
										<div class="col-lg-8">
											<label for="cmbClassificacaoRisco">Classificação de Risco<span class="text-danger"> *</span></label>
											<select id="cmbClassificacaoRisco" name="cmbClassificacaoRisco" class="form-control select-search" required>
												<option value="">Selecione</option>
												<?php
													$sql = "SELECT AtClRId, AtClRNome,AtClRTempo, AtClRCor, AtClRDeterminantes
															FROM AtendimentoClassificacaoRisco
															JOIN Situacao ON SituaId = AtClRStatus
															WHERE AtClRUnidade = " . $_SESSION['UnidadeId'] . " AND SituaChave = 'ATIVO'
														    ORDER BY AtClRNome ASC";
													$result = $conn->query($sql);
													$rowClassificacao = $result->fetchAll(PDO::FETCH_ASSOC);
													
													foreach ($rowClassificacao as $Classificacao) {
														if (isset($row['AtendClassificacaoRisco'])) {
															if ($Classificacao['AtClRId'] == $row['AtendClassificacaoRisco']) {
																print('<option selected value="' . $Classificacao['AtClRId'] . '$' . $Classificacao['AtClRTempo'] . '$' . $Classificacao['AtClRCor'] . '$' . $Classificacao['AtClRDeterminantes'] . '" selected>' . $Classificacao['AtClRNome'] . '</option>');
															} else {
																print('<option value="' . $Classificacao['AtClRId'] . '$' . $Classificacao['AtClRTempo'] . '$' . $Classificacao['AtClRCor'] . '$' . $Classificacao['AtClRDeterminantes'] . '">' . $Classificacao['AtClRNome'] . '</option>');
															}
														} else {
															print('<option value="' . $Classificacao['AtClRId'] . '$' . $Classificacao['AtClRTempo'] . '$' . $Classificacao['AtClRCor'] . '$' . $Classificacao['AtClRDeterminantes'] . '" >' . $Classificacao['AtClRNome'] . '</option>');
														}
													};

												?>
											</select>
										</div>
										
										<div class="col-lg-2">
											<div class="form-group">
												<label for="inputTempo">Tempo (min)</label>
												<input type="number" id="inputTempo" name="inputTempo" class="form-control" placeholder="Tempo" value="<?php if (isset($row)) echo $row['AtClRTempo']; ?>" readonly>
											</div>
										</div>
										<div class="col-lg-2">									
											<div class="col-lg-2">
												<div class="form-group" id="inputCor" name="inputCor" style="margin-left: 10px; margin-Top: 5px; height: 55px; width: 55px <?php if (isset($row)) echo ";background-color:".$row['AtClRCor']."; border:".$borderColor; ?>;  border-radius: 50px;" >
                                            </div>
                                        </div>
										</div>
									</div>
									
									<div class="row">
										<div class="col-lg-12">
											<div class="form-group">
												<label for="txtDeterminantes">Determinantes Gerais</label>
												<textarea rows="5" cols="5" class="form-control" id="txtDeterminantes" name="txtDeterminantes" placeholder="Determinantes" readonly><?php if (isset($row)) echo $row['AtClRDeterminantes']; ?></textarea>
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
										<a href="atendimentoEletivoListagem.php" class="btn btn-lg" role="button">Cancelar</a>
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

	<?php include_once("alerta.php"); ?>

</body>

</html>
