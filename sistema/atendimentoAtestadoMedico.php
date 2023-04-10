<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Atestado Médico';

include('global_assets/php/conexao.php'); 

$iAtendimentoId = isset($_POST['iAtendimentoId'])?$_POST['iAtendimentoId']:null;

if (isset($_SESSION['iAtendimentoId']) && $iAtendimentoId == null) {
	$iAtendimentoId = $_SESSION['iAtendimentoId'];
}
$_SESSION['iAtendimentoId'] = null;

if(!$iAtendimentoId){
	$uTipoAtendimento = $_SESSION['UltimaPagina'];

	if ($uTipoAtendimento == "ELETIVO") {
		irpara("atendimentoEletivoListagem.php");
	} elseif ($uTipoAtendimento == "AMBULATORIAL") {
		irpara("atendimentoAmbulatorialListagem.php");
	} elseif ($uTipoAtendimento == "HOSPITALAR") {
		irpara("atendimentoHospitalarListagem.php");
	}	
}

$sql = "SELECT TOP(1) AtAMeId
FROM AtendimentoAtestadoMedico
WHERE AtAMeAtendimento = $iAtendimentoId
ORDER BY AtAMeId DESC";
$result = $conn->query($sql);
$rowAtestadoMedico= $result->fetch(PDO::FETCH_ASSOC);

$iAtendimentoAtestadoMedicoId = $rowAtestadoMedico?$rowAtestadoMedico['AtAMeId']:null;

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
$sql = "SELECT AtendId, AtendCliente, AtendNumRegistro, AtModNome, ClienId, ClienCodigo, ClienNome, ClienSexo, ClienDtNascimento,
               ClienNomeMae, ClienCartaoSus, ClienCelular, ClienStatus, ClienUsuarioAtualizador, ClienUnidade, ClResNome, SituaChave
		FROM Atendimento
		JOIN Cliente ON ClienId = AtendCliente
		LEFT JOIN ClienteResponsavel on ClResCliente = AtendCliente
		LEFT JOIN AtendimentoModalidade ON AtModId = AtendModalidade
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
if(isset($iAtendimentoAtestadoMedicoId ) && $iAtendimentoAtestadoMedicoId ){

	//Essa consulta é para preencher o campo Atestado Médico ao editar
	$sql = "SELECT AtAMeAtestadoMedico, AtAMeHoraFim, AtAMeHoraInicio, AtAMeDataInicio, AtAMeDataFim, AtAMeCid10
			FROM AtendimentoAtestadoMedico
			WHERE AtAMeId = " . $iAtendimentoAtestadoMedicoId ;
	$result = $conn->query($sql);
	$rowAtestadoMedico = $result->fetch(PDO::FETCH_ASSOC);
		
	$_SESSION['msg'] = array();

	// Formatar Hora/Data

	$DataInicio = strtotime($rowAtestadoMedico['AtAMeDataInicio']);
	$DataAtendimentoInicio = date("d/m/Y", $DataInicio);

	$DataFim = strtotime($rowAtestadoMedico['AtAMeDataFim']);
	$DataAtendimentoFim = date("d/m/Y", $DataFim);

	$Inicio = strtotime($rowAtestadoMedico['AtAMeHoraInicio']);
	$HoraInicio = date("H:i", $Inicio);

	$Fim = strtotime($rowAtestadoMedico['AtAMeHoraFim']);
	$HoraFim = date("H:i", $Fim);

} 



//Se estiver gravando (inclusão ou edição)
if (isset($_POST['txtareaConteudo']) ){

	try{
	

		//Edição
		if ($iAtendimentoAtestadoMedicoId){
		
			$sql = "UPDATE AtendimentoAtestadoMedico SET AtAMeAtendimento = :sAtendimento, AtAMeDataInicio = :dDataInicio, AtAMeDataFim = :dDataFim, AtAMeHoraInicio = :sHoraInicio,
						   AtAMeHoraFim  = :sHoraFim, AtAMeProfissional = :sProfissional, AtAMeCid10 = :iCid10, AtAMeAtestadoMedico = :sAtestadoMedico, AtAMeUnidade = :iUnidade
					WHERE AtAMeId = :iAtendimentoAtestadoMedico";
			$result = $conn->prepare($sql);
					
			$result->execute(array(
				':sAtendimento' => $iAtendimentoId,
				':dDataInicio' => gravaData($_POST['inputDataInicio']),
				':dDataFim' => date('m/d/Y'),
				':sHoraInicio' => $_POST['inputInicio'],
				':sHoraFim' => date('H:i'),
				':sProfissional' => $userId,
				':iCid10' => $_POST['cmbCid10'],
				':sAtestadoMedico' => $_POST['txtareaConteudo'],
				':iUnidade' => $_SESSION['UnidadeId'],
				':iAtendimentoAtestadoMedico' => $iAtendimentoAtestadoMedicoId 
				));

			$_SESSION['msg']['mensagem'] = "Atestado Médico alterado!!!";
			

		} else { //inclusão

			$sql = "INSERT INTO AtendimentoAtestadoMedico (AtAMeAtendimento, AtAMeDataInicio, AtAMeDataFim, AtAMeHoraInicio, AtAMeHoraFim, AtAMeProfissional, AtAMeCid10, AtAMeAtestadoMedico, AtAMeUnidade)
						VALUES (:sAtendimento, :dDataInicio, :dDataFim, :sHoraInicio, :sHoraFim, :sProfissional, :iCid10, :sAtestadoMedico, :iUnidade)";
			$result = $conn->prepare($sql);
					
			$result->execute(array(
				':sAtendimento' => $iAtendimentoId,
				':dDataInicio' => gravaData($_POST['inputDataInicio']),
				':dDataFim' => gravaData($_POST['inputDataFim']),
				':sHoraInicio' => $_POST['inputInicio'],
				':sHoraFim' => date('H:i'),
				':sProfissional' => $userId,
				':iCid10' => $_POST['cmbCid10'],
				':sAtestadoMedico' => $_POST['txtareaConteudo'],
				':iUnidade' => $_SESSION['UnidadeId'],
			));

			$_SESSION['msg']['mensagem'] = "Atestado Médico incluído!!!";

		}
	
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['tipo'] = "success";
					
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro reportado com o Atestado Médico!!!";
		$_SESSION['msg']['tipo'] = "error";	
		
		echo 'Error: ' . $e->getMessage();
	}
	$_SESSION['iAtendimentoId'] = $iAtendimentoId;
	irpara("atendimentoAtestadoMedico.php");
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Atestado Médico</title>

	<?php include_once("head.php"); ?>
	
	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/tables/datatables/datatables.min.js"></script>
	<script src="global_assets/js/plugins/tables/datatables/extensions/responsive.min.js"></script>
	
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>

	<script src="global_assets/js/plugins/editors/summernote/summernote.min.js"></script>

	<script src="global_assets/js/demo_pages/datatables_responsive.js"></script>
	<script src="global_assets/js/demo_pages/datatables_sorting.js"></script>

	<!-- Não permite que o usuário retorne para o EDITAR -->
	<script src="global_assets/js/lamparinas/stop-back.js"></script>

	<!-- Validação -->
	<script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
	<script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
	<script src="global_assets/js/demo_pages/form_validation.js"></script>	
	
	<script type="text/javascript">

		$(document).ready(function() {	

			$('#summernote').summernote();
			
        
		$('#enviar').on('click', function(e){
			
			e.preventDefault();
	

			$( "#formAtendimentoAtestadoMedico" ).submit()	
			
		})
	}); //document.ready	
	
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

						<form name="formAtendimentoAtestadoMedico" id="formAtendimentoAtestadoMedico" method="post" class="form-validate-jquery">
							<?php
								echo "<input type='hidden' id='iAtendimentoId' name='iAtendimentoId' value='$iAtendimentoId' />";
							?>
							<div class="card">
								<div class="card-header header-elements-inline">
									<h3 class="card-title"><b>ATESTADO MÉDICO</b></h3>
								</div>
							</div>

							<div> <?php include ('atendimentoDadosPaciente.php'); ?> </div>

							<div class="card">

								<div class="card-body">

									<div class="col-lg-12">
										<div class="form-group">
											<label for="cmbCid10">CID-10<span class="text-danger">*</span></label>
											<select id="cmbCid10" name="cmbCid10" class="form-control select-search" required>
												<option value="">Selecione</option>
												<?php 
													$sql = "SELECT Cid10Id,Cid10Capitulo, Cid10Codigo, Cid10Descricao
															FROM Cid10
															JOIN Situacao on SituaId = Cid10Status
															WHERE SituaChave = 'ATIVO'
															ORDER BY Cid10Codigo ASC";
													$result = $conn->query($sql);
													$row = $result->fetchAll(PDO::FETCH_ASSOC);

													foreach ($row as $item){
														$seleciona = $item['Cid10Id'] == $rowAtestadoMedico['AtAMeCid10'] ? "selected" : "";
														print('<option value="'.$item['Cid10Id'].'" '. $seleciona .'>'.$item['Cid10Capitulo'] . ' - '.$item['Cid10Codigo'] . ' - ' . $item['Cid10Descricao'] . ' ' .'</option>');
													}
												
												?>
											</select>
										</div>
									</div>
									<br>
									<div class="row">
										<div class="col-lg-12">
											<div class="form-group">
												<label for="inputNome">Atestado Médico </label>
												<textarea rows="5" cols="5"  id="summernote" name="txtareaConteudo" class="form-control" placeholder="Corpo do anamnese (informe aqui o texto que você queira que apareça no anamnese)" > <?php if (isset($iAtendimentoAtestadoMedicoId )) echo $rowAtestadoMedico['AtAMeAtestadoMedico']; ?> </textarea>
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
