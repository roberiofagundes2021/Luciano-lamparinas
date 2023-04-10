<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Atendimento Ambulatorial';

include('global_assets/php/conexao.php');

$iAtendimentoId = isset($_POST['iAtendimentoId'])?$_POST['iAtendimentoId']:null;

if (isset($_SESSION['iAtendimentoId']) && $iAtendimentoId == null) {
	$iAtendimentoId = $_SESSION['iAtendimentoId'];
}
$_SESSION['iAtendimentoId'] = $iAtendimentoId;

if(!$iAtendimentoId){
	irpara("atendimentoAmbulatorialListagem.php");
}

$sql = "SELECT TOP(1) AtAmbId
FROM AtendimentoAmbulatorial
WHERE AtAmbAtendimento = $iAtendimentoId
ORDER BY AtAmbId DESC";
$result = $conn->query($sql);
$rowAmbulatorial= $result->fetch(PDO::FETCH_ASSOC);

$iAtendimentoAmbulatorialId = $rowAmbulatorial?$rowAmbulatorial['AtAmbId']:null;

// essas variáveis são utilizadas para colocar o nome da classificação do atendimento no menu secundario

$ClaChave = isset($_POST['ClaChave'])?$_POST['ClaChave']:'';
$ClaNome = isset($_POST['ClaNome'])?$_POST['ClaNome']:'';


//Essa consulta é para verificar  o profissional
$sql = "SELECT UsuarId, A.ProfiUsuario, A.ProfiId as ProfissionalId, A.ProfiNome as ProfissionalNome, PrConNome, B.ProfiCbo as ProfissaoCbo
		FROM Usuario
		JOIN Profissional A ON A.ProfiUsuario = UsuarId
		LEFT JOIN Profissao B ON B.ProfiId = A.ProfiProfissao
		LEFT JOIN ProfissionalConselho ON PrConId = ProfiConselho
		WHERE UsuarId =  ". $_SESSION['UsuarId'] . " ";
$result = $conn->query($sql);
$rowUser = $result->fetch(PDO::FETCH_ASSOC);
$userId = $rowUser['ProfissionalId'];


//Essa consulta é para verificar qual é o atendimento e cliente 
$sql = "SELECT AtendId, AtendCliente, AtendNumRegistro, AtModNome, AtendClassificacaoRisco, ClienId, ClienCodigo, ClienNome, ClienSexo, ClienDtNascimento,
               ClienNomeMae, ClienCartaoSus, ClienCelular, ClienStatus, ClienUsuarioAtualizador, ClienUnidade, ClResNome, AtTriPeso,
			   AtTriAltura, AtTriImc, AtTriPressaoSistolica, AtTriPressaoDiatolica, AtTriFreqCardiaca, AtTriTempAXI, AtClRCor, SituaChave
		FROM Atendimento
		JOIN Cliente ON ClienId = AtendCliente
		LEFT JOIN ClienteResponsavel on ClResCliente = AtendCliente
		LEFT JOIN AtendimentoModalidade ON AtModId = AtendModalidade
		LEFT JOIN AtendimentoTriagem ON AtTriAtendimento = AtendId
		LEFT JOIN AtendimentoClassificacaoRisco ON AtClRId = AtendClassificacaoRisco
		JOIN Situacao ON SituaId = AtendSituacao
	    WHERE  AtendId = $iAtendimentoId 
		ORDER BY AtendNumRegistro ASC";
$result = $conn->query($sql);
$row = $result->fetch(PDO::FETCH_ASSOC);

$iAtendimentoCliente = $row['AtendCliente'] ;
$iAtendimentoId = $row['AtendId'];

//Essa consulta é para preencher o sexo
if ($row['ClienSexo'] == 'F'){
    $sexo = 'Feminino';
} else{
    $sexo = 'Masculino';
}

//Se estiver editando
if(isset($iAtendimentoAmbulatorialId ) && $iAtendimentoAmbulatorialId ){

	//Essa consulta é para preencher o campo do Atendimento Ambulatorial ao editar
	$sql = "SELECT *
			FROM AtendimentoAmbulatorial
			WHERE AtAmbId = " . $iAtendimentoAmbulatorialId ;
	$result = $conn->query($sql);
	$rowAmbulatorial = $result->fetch(PDO::FETCH_ASSOC);
		
	$_SESSION['msg'] = array();

	// Formatar Hora/Data

	$DataInicio = strtotime($rowAmbulatorial['AtAmbDataInicio']);
	$DataAtendimentoInicio = date("d/m/Y", $DataInicio);

	$DataFim = strtotime($rowAmbulatorial['AtAmbDataFim']);
	$DataAtendimentoFim = date("d/m/Y", $DataFim);

	$Inicio = strtotime($rowAmbulatorial['AtAmbHoraInicio']);
	$HoraInicio = date("H:i", $Inicio);

	$Fim = strtotime($rowAmbulatorial['AtAmbHoraFim']);
	$HoraFim = date("H:i", $Fim);

} 

//Se estiver gravando (inclusão ou edição)
if (isset($_POST['inputInicio']) ){
	try{
		//Edição
		if ($iAtendimentoAmbulatorialId){
		
			$sql = "UPDATE AtendimentoAmbulatorial SET AtAmbAtendimento = :sAtendimento, AtAmbDataInicio = :dDataInicio, AtAmbDataFim = :dDataFim, AtAmbHoraInicio = :sHoraInicio,
						   AtAmbHoraFim  = :sHoraFim, AtAmbProfissional = :sProfissional, AtAmbQueixaPrincipal = :sQueixaPrincipal,
						   AtAmbHistoriaMolestiaAtual = :sHistoriaMolestiaAtual, AtAmbHistoriaPatologicaPregressa = :sHistoriaPatologicaPregressa,
						   AtAmbExameFisico = :sExameFisico, AtAmbHipoteseDiagnostica = :sHipoteseDiagnostica, AtAmbDigitacaoLivre = :sDigitacaoLivre, AtAmbUnidade = :iUnidade
					WHERE AtAmbId = :iAtendimentoAmbulatorial";
			$result = $conn->prepare($sql);
					
			$result->execute(array(
				':sAtendimento' => $iAtendimentoId,
				':dDataInicio' => gravaData($_POST['inputDataInicio']),
				':dDataFim' => date('m/d/Y'),
				':sHoraInicio' => $_POST['inputInicio'],
				':sHoraFim' => date('H:i'),
				':sProfissional' => $userId,				
				':sQueixaPrincipal' => $_POST['txtareaConteudo1'],
				':sHistoriaMolestiaAtual' => $_POST['txtareaConteudo2'],
				':sHistoriaPatologicaPregressa' => $_POST['txtareaConteudo3'],
				':sExameFisico' => $_POST['txtareaConteudo4'],
				':sHipoteseDiagnostica' => $_POST['txtareaConteudo5'],
				':sDigitacaoLivre' => $_POST['txtareaConteudo6'],				
				':iUnidade' => $_SESSION['UnidadeId'],
				':iAtendimentoAmbulatorial' => $iAtendimentoAmbulatorialId 
				));

			$_SESSION['msg']['mensagem'] = "Atendimento Ambulatorial alterado!!!";
			

		} else { //inclusão

			$sql = "INSERT INTO AtendimentoAmbulatorial (AtAmbAtendimento, AtAmbDataInicio, AtAmbDataFim, AtAmbHoraInicio, AtAmbHoraFim, AtAmbProfissional, AtAmbQueixaPrincipal, AtAmbHistoriaMolestiaAtual,
			 											AtAmbHistoriaPatologicaPregressa, AtAmbExameFisico, AtAmbHipoteseDiagnostica, AtAmbDigitacaoLivre, AtAmbUnidade)
						VALUES (:sAtendimento, :dDataInicio, :dDataFim, :sHoraInicio, :sHoraFim, :sProfissional,:sQueixaPrincipal,:sHistoriaMolestiaAtual, :sHistoriaPatologicaPregressa, 
						        :sExameFisico, :sHipoteseDiagnostica, :sDigitacaoLivre, :iUnidade)";
			$result = $conn->prepare($sql);
					
			$result->execute(array(
				':sAtendimento' => $iAtendimentoId,
				':dDataInicio' => gravaData($_POST['inputDataInicio']),
				':dDataFim' => gravaData($_POST['inputDataFim']),
				':sHoraInicio' => $_POST['inputInicio'],
				':sHoraFim' => date('H:i'),
				':sProfissional' => $userId,
				':sQueixaPrincipal' => $_POST['txtareaConteudo1'],
				':sHistoriaMolestiaAtual' => $_POST['txtareaConteudo2'],
				':sHistoriaPatologicaPregressa' => $_POST['txtareaConteudo3'],
				':sExameFisico' => $_POST['txtareaConteudo4'],
				':sHipoteseDiagnostica' => $_POST['txtareaConteudo5'],
				':sDigitacaoLivre' => $_POST['txtareaConteudo6'],
				':iUnidade' => $_SESSION['UnidadeId'],
			));

			$_SESSION['msg']['mensagem'] = "Atendimento Ambulatorial incluído!!!";

		}
	
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['tipo'] = "success";
					
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro reportado com o Atendimento Ambulatorial!!!";
		$_SESSION['msg']['tipo'] = "error";	
		
		echo 'Error: ' . $e->getMessage();
	}

	irpara("atendimentoAmbulatorial.php");
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Atendimento Ambulatorial</title>

	<?php include_once("head.php"); ?>
	
	<script type="text/javascript">

		$(document).ready(function() {	
	     
			$('#enviar').on('click', function(e){
				
				e.preventDefault();
		
				$( "#formAtendimentoAmbulatorial" ).submit();
			})

			$(".caracteressummernote1").text((500 - $("#summernote1").val().length) + ' restantes'); //restantes no input1
			$(".caracteressummernote2").text((500 - $("#summernote2").val().length) + ' restantes'); //restantes no input2
			$(".caracteressummernote3").text((500 - $("#summernote3").val().length) + ' restantes'); //restantes no input3
			$(".caracteressummernote4").text((500 - $("#summernote4").val().length) + ' restantes'); //restantes no input4
			$(".caracteressummernote5").text((500 - $("#summernote5").val().length) + ' restantes'); //restantes no input5
			$(".caracteressummernote6").text((1000 - $("#summernote6").val().length) + ' restantes'); //restantes no input6
		}); //document.ready

		function contarCaracteres(params) {

			var limite = params.maxLength;
			var informativo = " restantes.";
			var caracteresDigitados = params.value.length;
			var caracteresRestantes = limite - caracteresDigitados;

			if (caracteresRestantes <= 0) {
				var texto = $(`textarea[id=${params.id}]`).val();
				$(`textarea[id=${params.id}]`).val(texto.substr(0, limite));
				$(".caracteres" + params.id).text("0 " + informativo);
			} else {
				$(".caracteres" + params.id).text(caracteresRestantes + " " + informativo);
			}
		}

	</script>

</head>

<body class="navbar-top sidebar-xs">

	<?php include_once("topo.php"); ?>	

	<!-- Page content -->
	<div class="page-content">
		
		<?php
			include_once("menu-left.php");
			include_once("menuLeftSecundarioVenda.php");
		?>

		<!-- Main content -->
		<div class="content-wrapper">

			<?php include_once("cabecalho.php"); ?>	

			<!-- Content area -->
			<div class="content">

				<!-- Info blocks -->		
				<div class="row">
					
					<div class="col-lg-12">
						<form id='dadosPost'>
							<?php
								echo "<input type='hidden' id='iAtendimentoId' name='iAtendimentoId' value='$iAtendimentoId' />";
							?>
						</form>
						<!-- Basic responsive configuration -->
						<form name="formAtendimentoAmbulatorial" id="formAtendimentoAmbulatorial" method="post">
							<?php
								echo "<input type='hidden' id='iAtendimentoId' name='iAtendimentoId' value='$iAtendimentoId' />";
							?>
							<div class="card">
								<div class="card-header header-elements-inline">
									<h3 class="card-title"><b>ATENDIMENTO AMBULATORIAL</b></h3>
								</div>
							</div>

							<div> <?php include ('atendimentoDadosPaciente.php'); ?> </div>

							<div class="card">

								<div class="card-body">
									<div class="row">
										<div class="col-lg-9">
											<div class="row">
												<div class="col-lg-12">
													<div class="form-group"> 
														<a href="#collapse1-link" class="font-weight-semibold collapsed" data-toggle="collapse" aria-expanded="false"><h5> 1. Queixa Principal (QP)</h5></a>   
														<div class="collapse" id="collapse1-link" style="">
															<div class="mt-3">
																<textarea rows="4" cols="4" maxLength="500" onInput="contarCaracteres(this);"  id="summernote1" name="txtareaConteudo1" class="form-control" placeholder="Corpo do ambulatorial (informe aqui o texto que você queira que apareça na queixa principal)" ><?php if (isset($iAtendimentoAmbulatorialId )) echo $rowAmbulatorial['AtAmbQueixaPrincipal']; ?></textarea>
																<small class="text-muted form-text">Max. 500 caracteres - <span class="caracteressummernote1"></span></small>
															</div>
														</div>
													</div>
												</div>
											</div>
											<div class="row">
												<div class="col-lg-12">
													<div class="form-group">
														<a href="#collapse2-link" class="font-weight-semibold collapsed" data-toggle="collapse" aria-expanded="false"><h5> 1.1. História da Moléstia Atual (HMA)</h5></a>   
														<div class="collapse" id="collapse2-link" style="">
															<div class="mt-3">
																<textarea rows="4" cols="4" maxLength="500" onInput="contarCaracteres(this);" id="summernote2" name="txtareaConteudo2" class="form-control" placeholder="Corpo do ambulatorial (informe aqui o texto que você queira que apareça na história da moléstia atual)" ><?php if (isset($iAtendimentoAmbulatorialId )) echo $rowAmbulatorial['AtAmbHistoriaMolestiaAtual']; ?></textarea>
																<small class="text-muted form-text">Max. 500 caracteres - <span class="caracteressummernote2"></span></small>
															
															</div>
														</div>
													</div>
												</div>
											</div>
											<div class="row">
												<div class="col-lg-12">
													<div class="form-group">
														<a href="#collapse3-link" class="font-weight-semibold collapsed" data-toggle="collapse" aria-expanded="false"><h5> 1.2. História Patológica Pregressa</h5></a>   
														<div class="collapse" id="collapse3-link" style="">
															<div class="mt-3">
																<textarea rows="4" cols="4" maxLength="500" onInput="contarCaracteres(this);" id="summernote3" name="txtareaConteudo3" class="form-control" placeholder="Corpo do ambulatorial (informe aqui o texto que você queira que apareça na história patológica pregressa)" ><?php if (isset($iAtendimentoAmbulatorialId )) echo $rowAmbulatorial['AtAmbHistoriaPatologicaPregressa']; ?></textarea>
																<small class="text-muted form-text">Max. 500 caracteres - <span class="caracteressummernote3"></span></small>
															
															</div>
														</div>
													</div>
												</div>
											</div>
											<div class="row">
												<div class="col-lg-12">
													<div class="form-group">
														<a href="#collapse4-link" class="font-weight-semibold collapsed" data-toggle="collapse" aria-expanded="false"><h5> 1.3. Exame Físico</h5></a>   
														<div class="collapse" id="collapse4-link" style="">
															<div class="mt-3">
																<textarea rows="4" cols="4" maxLength="500" onInput="contarCaracteres(this);" id="summernote4" name="txtareaConteudo4" class="form-control" placeholder="Corpo do ambulatorial (informe aqui o texto que você queira que apareça no exame físico)" ><?php if (isset($iAtendimentoAmbulatorialId )) echo $rowAmbulatorial['AtAmbExameFisico']; ?></textarea>
																<small class="text-muted form-text">Max. 500 caracteres - <span class="caracteressummernote4"></span></small>
															
															</div>
														</div>
													</div>
												</div>
											</div>
											<div class="row">
												<div class="col-lg-12">
													<div class="form-group">
														<a href="#collapse5-link" class="font-weight-semibold collapsed" data-toggle="collapse" aria-expanded="false"><h5> 1.4. Hipótese Diagnóstica</h5></a>   
														<div class="collapse" id="collapse5-link" style="">
															<div class="mt-3">
																<textarea rows="4" cols="4" maxLength="500" onInput="contarCaracteres(this);" id="summernote5" name="txtareaConteudo5" class="form-control" placeholder="Corpo do ambulatorial (informe aqui o texto que você queira que apareça na hipotese diagnóstica)" ><?php if (isset($iAtendimentoAmbulatorialId )) echo $rowAmbulatorial['AtAmbHipoteseDiagnostica']; ?></textarea>
																<small class="text-muted form-text">Max. 500 caracteres - <span class="caracteressummernote5"></span></small>
															
															</div>
														</div>
													</div>
												</div>
											</div>
											<div class="row">
												<div class="col-lg-12">
													<div class="form-group">
														<a href="#collapse6-link" class="font-weight-semibold collapsed" data-toggle="collapse" aria-expanded="false"><h5> 2. Anamnese (Digitação Livre)</h5></a>   
														<div class="collapse" id="collapse6-link" style="">
															<div class="mt-3">
																<textarea rows="5" cols="5" maxLength="1000" onInput="contarCaracteres(this);" id="summernote6" name="txtareaConteudo6" class="form-control" placeholder="Corpo do ambulatorial (informe aqui o texto que você queira que apareça na anamnese)" ><?php if (isset($iAtendimentoAmbulatorialId )) echo $rowAmbulatorial['AtAmbDigitacaoLivre']; ?></textarea>
																<small class="text-muted form-text">Max. 1000 caracteres - <span class="caracteressummernote6"></span></small>
															
															</div>
														</div>
													</div>
												</div>
											</div>
											
										</div>
										<div class="col-lg-3">
											<div class="card-body" style="margin-Top: -20px;">
												<div class="form-group" style="margin-left: -15px;">
													<h5 class="text-uppercase font-weight-bold">Risco/Vulnerabilidade</h5>
												</div>
												<div class="form-group" style="margin-left: -15px; margin-Top: -10px; height: 40px; width: 40px; background-color: <?php echo $row['AtClRCor']; ?>; border-radius: 50px;" >

												</div>
											</div>	
											<div class="card-body" style="margin-Top: -25px;">
												<div class="form-group" style="margin-left: -15px;">
													<h5 class="text-uppercase font-weight-bold">Medições</h5>
												</div>
												
												<div class="form-group" style="margin-left: -15px; margin-Top: -10px;" >
													<label>Peso: <?php echo $row['AtTriPeso']; ?> Kg</label>
													<br>
													<label>Altura: <?php echo $row['AtTriAltura']; ?> cm</label>
													<br>
													<label>IMC: <?php echo $row['AtTriImc']; ?> Kg/m2</label>
													<br>
													<label>Pressão Arterial: <?php echo $row['AtTriPressaoSistolica']; ?>/<?php echo $row['AtTriPressaoDiatolica']; ?> mmHg</label>
													<br>
													<label>Frequência Cardíaca: <?php echo $row['AtTriFreqCardiaca']; ?> bpm</label>
													<br>
													<label>Temperatura AXI: <?php echo $row['AtTriTempAXI']; ?> ºC</label>
												</div>
											</div>	
										</div>
									</div>
									<div class="row">
										<div class="col-lg-12">
											<div class="form-group" style="padding-top:25px;">
												<?php 
													if (isset($_SESSION['SituaChave']) && $_SESSION['SituaChave'] != "ATENDIDO") {
														echo "<button class='btn btn-lg btn-success mr-1' id='enviar'>Salvar</button>";
														}
												?>
												<button type="button" class="btn btn-lg btn-secondary mr-1">Imprimir</button>

												<?php 
													if (isset($ClaChave) && $ClaChave == "ELETIVO") {
													echo "<a href='atendimentoEletivoListagem.php' class='btn btn-basic' role='button'>Cancelar</a>";
													} elseif (isset($ClaChave) && $ClaChave == "AMBULATORIAL") {
													echo "<a href='atendimentoAmbulatorialListagem.php' class='btn btn-basic' role='button'>Cancelar</a>";
													} elseif (isset($ClaChave) && $ClaChave == "HOSPITALAR") {
													echo "<a href='atendimentoHospitalarListagem.php' class='btn btn-basic' role='button'>Cancelar</a>";
													}					
												?>
											</div>
										</div>
									</div>    
									
								</div>
							</div>
						</form>	

							<!-- /basic responsive configuration -->
					</div>
					
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