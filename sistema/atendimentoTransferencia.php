<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Transferência';

include('global_assets/php/conexao.php');

$iAtendimentoId = isset($_POST['iAtendimentoId'])?$_POST['iAtendimentoId']:null;

if (isset($_SESSION['iAtendimentoId']) && !$iAtendimentoId) {
	$iAtendimentoId = $_SESSION['iAtendimentoId'];
}else if($iAtendimentoId){
	$_SESSION['iAtendimentoId'] = $iAtendimentoId;
}
// $_SESSION['iAtendimentoId'] = null;

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

$iUnidade = $_SESSION['UnidadeId'];

// essas variáveis são utilizadas para colocar o nome da classificação do atendimento no menu secundario

$ClaChave = isset($_POST['ClaChave'])?$_POST['ClaChave']:'';
$ClaNome = isset($_POST['ClaNome'])?$_POST['ClaNome']:'';


//Essa consulta é para verificar  o profissional
$sql = "SELECT UsuarId, A.ProfiUsuario, A.ProfiId as ProfissionalId, A.ProfiNome as ProfissionalNome, PrConNome, B.ProfiCbo as ProfissaoCbo
		FROM Usuario
		JOIN Profissional A ON A.ProfiUsuario = UsuarId
		LEFT JOIN Profissao B ON B.ProfiId = A.ProfiProfissao
		LEFT JOIN ProfissionalConselho ON PrConId = ProfiConselho
		WHERE UsuarId =  $_SESSION[UsuarId]";
$result = $conn->query($sql);
$rowUser = $result->fetch(PDO::FETCH_ASSOC);
$userId = $rowUser['ProfissionalId'];


//Essa consulta é para verificar qual é o atendimento e cliente 
$sql = "SELECT AtendId, AtendCliente, AtendNumRegistro, AtModNome, ClienId, ClienCodigo, ClienNome, ClienSexo, ClienDtNascimento,
               ClienNomeMae, ClienCartaoSus, ClienCelular, ClienStatus, ClienUsuarioAtualizador, ClienUnidade, ClResNome
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

$sql = "SELECT AtClaChave
	FROM Atendimento
	JOIN AtendimentoClassificacao ON AtClaId = AtendClassificacao
	WHERE AtendId = $iAtendimentoId and AtClaUnidade = $iUnidade";
$result = $conn->query($sql);
$rowAtendimentoClassificacao = $result->fetch(PDO::FETCH_ASSOC);

// if($rowAtendimentoClassificacao['AtClaChave'] == 'ELETIVO'){
// 	$sql = "SELECT TOP(1) AtEleDataInicio as dataInicio, AtEleHoraInicio as horaFim, AtEleDataFim as dataFim, AtEleHoraFim as horaFim
// 	FROM AtendimentoEletivo
// 	WHERE AtEleAtendimento = $iAtendimentoId
// 	ORDER BY AtEleId DESC";
// }elseif($rowAtendimentoClassificacao['AtClaChave'] == 'AMBULATORIAL'){
// 	$sql = "SELECT TOP(1) AtAmbDataInicio as dataInicio, AtAmbHoraInicio as horaFim, AtAmbDataFim as dataFim, AtAmbHoraFim as horaFim
// 	FROM AtendimentoAmbulatorial
// 	WHERE AtAmbAtendimento = $iAtendimentoId
// 	ORDER BY AtAmbId DESC";
// }elseif($rowAtendimentoClassificacao['AtClaChave'] == 'HOSPITALAR'){
// 	$sql = "SELECT TOP(1) AtIEnDataInicio as dataInicio, AtIEnHoraInicio as horaInicio, AtIEnDataFim as dataFim, AtIEnHoraFim as horaFim
// 	FROM AtendimentoInternacaoEntrada
// 	WHERE AtIEnAtendimento = $iAtendimentoId
// 	ORDER BY AtIEnId DESC";
// }
// $result = $conn->query($sql);
// $rowAtendimento = $result->fetchAll(PDO::FETCH_ASSOC);	

// //Essa consulta é para preencher o campo Receituário ao editar

// $_SESSION['msg'] = array();

// if($rowAtendimento){
// 	// Formatar Hora/Data
// 	$DataInicio = strtotime($rowAtendimento['dataInicio']);
// 	$DataAtendimentoInicio = date("d/m/Y", $DataInicio);

// 	$DataFim = strtotime($rowAtendimento['dataFim']);
// 	$DataAtendimentoFim = date("d/m/Y", $DataFim);

// 	$Inicio = strtotime($rowAtendimento['horaInicio']);
// 	$HoraInicio = date("H:i", $Inicio);

// 	$Fim = strtotime($rowAtendimento['horaFim']);
// 	$HoraFim = date("H:i", $Fim);
// }

//Se estiver gravando (inclusão ou edição)
if (isset($_POST['inputTipoTransferencia']) && isset($_POST['inputCid'])){
	try{
		//Inserção
		$sql = "INSERT INTO AtendimentoTransferencia(AtTraAtendimento,AtTraDataInicio,AtTraHoraInicio,AtTraDataFim,
				AtTraHoraFim,AtTraProfissional,AtTraEstabelecimento,AtTraDataAlta,AtTraAtendimentoModelo,AtTraCid10,
				AtTraRelatorioTransferencia,AtTraUnidade)
				VALUES (:iAtendimento, :DataInicio, :sHoraInicio, :sDataFim, :sHoraFim, :iProfissional,
				:iEstabelecimento, :sDataAlta, :iAtendimentoModelo, :iCid10, :sRelatorioTransferencia, :iUnidade)";
		$result = $conn->prepare($sql);
				
		$result->execute(array(
			':iAtendimento' => $iAtendimentoId,
			':DataInicio' => date('Y-m-d'),
			':sHoraInicio' => date('H:i'),
			':sDataFim' => date('Y-m-d'),
			':sHoraFim' => date('H:i'),
			':iProfissional' => $userId,
			':iEstabelecimento' => $_POST['inputLocalDestino'],
			':sDataAlta' => $_POST['inputDataAlta'],
			':iAtendimentoModelo' => $_POST['inputTipoTransferencia'],
			':iCid10' => $_POST['inputCid'],
			':sRelatorioTransferencia' => $_POST['relatorio'],
			':iUnidade' => $iUnidade
		));

		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Transferência incluída com sucesso!!!";
		$_SESSION['msg']['tipo'] = "success";
		switch($rowAtendimentoClassificacao['AtClaChave']){
			case "ELETIVO":irpara("atendimentoEletivoListagem.php");break;
			case "AMBULATORIAL":irpara("atendimentoAmbulatorialListagem.php");break;
			case "HOSPITALAR":irpara("atendimentoHospitalarListagem.php");break;
			default: irpara("atendimentoEletivoListagem.php");break;
		}
					
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao cadastrar Transferência!!!";
		$_SESSION['msg']['tipo'] = "error";	
		
		echo 'Error: ' . $e->getMessage();
	}
	$_SESSION['iAtendimentoId'] = $iAtendimentoId;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Receituário</title>

	<?php include_once("head.php"); ?>
	
	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/tables/datatables/datatables.min.js"></script>
	<script src="global_assets/js/plugins/tables/datatables/extensions/responsive.min.js"></script>
	
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
	<script src="global_assets/js/demo_pages/form_select2.js"></script>

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
			$('#enviar').on('click', function(e){
				e.preventDefault()
				$( "#formAtendimentoTransferencia" ).submit()
			})
			$('#voltar').on('click', function(e){
				e.preventDefault()
				$('#dadosPost').attr('action','atendimentoFinalizar.php')
				$('#dadosPost').submit()
			})
			$('#inputTipoTransferencia').on('change', function(e){
				$.ajax({
					type: 'POST',
					url: 'filtraAtendimentoTransferencia.php',
					dataType: 'json',
					data: {
						'tipoRequest': 'MODELO',
						'iAtendimentoModelo': $(this).val()
					},
					success: function(response){
						$('#relatorio').val(response)
					},
					error: function(response){}
				});
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
						<form id='dadosPost' method="POST">
							<?php
								echo "<input type='hidden' id='iAtendimentoId' name='iAtendimentoId' value='$iAtendimentoId' />";
							?>
						</form>
						<!-- Basic responsive configuration -->
						<form id="formAtendimentoTransferencia" name="formAtendimentoTransferencia" method="post" class="form-validate-jquery">
							<?php
								$tipo = isset($_POST['tipo'])?$_POST['tipo']:null;
								// echo "<input type='hidden' id='inputDataFimReceituario' name='inputDataFimReceituario' value='".($rowReceituario?$rowReceituario['AtRecDataFim']:'')."'/>";
								// echo "<input type='hidden' id='inputHoraFimReceituario' name='inputHoraFimReceituario' value='".($rowReceituario?$rowReceituario['AtRecHoraFim']:'')."'/>";
								echo "<input type='hidden' id='iAtendimentoId' name='iAtendimentoId' value='$iAtendimentoId' />";
								// echo "<input type='hidden' id='iReceituario' name='iReceituario' value='".($iAtendimentoReceituarioId?$iAtendimentoReceituarioId:'')."' />";
							?>

							<div class="card">
								<div class="card-header header-elements-inline">
									<h3 class="card-title"><b>RECEITUÁRIO</b></h3>
								</div>
							</div>

							<div> <?php include ('atendimentoDadosPaciente.php'); ?> </div>

							<div class="card">
								<div class="card-body">
									<div class="row m-0 mb-3 col-lg-12">
										<div class="col-lg-6">
											<label>Local de Destino <span class="text-danger">*</span></label>
											<select id="inputLocalDestino" name="inputLocalDestino" class="select-search" required>
												<option value=''>Selecione</option>
												<?php
													$sql = "SELECT EstabId,EstabNome
													FROM Estabelecimento
													WHERE EstabUnidade = $iUnidade";
													$result = $conn->query($sql);
													$rowDestino = $result->fetchAll(PDO::FETCH_ASSOC);

													$opts = "";

													foreach($rowDestino as $destino){
														$opts .= "<option value='$destino[EstabId]'>$destino[EstabNome]</option>";
													}
													echo $opts;
												?>
											</select>
										</div>
										<div class="col-lg-6">
											<label>Data da Alta <span class="text-danger">*</span></label>
											<div class="input-group">
												<!-- <input type="date" id="inputDataAlta" name="inputDataAlta" class="form-control" placeholder="Data da alta" value="<?php echo date('Y-m-d'); ?>" required> -->
												<input type="date" id="inputDataAlta" name="inputDataAlta" class="form-control" placeholder="Data da alta" value="" required>
											</div>
										</div>
									</div>

									<div class="row  m-0 mb-3 col-lg-12">
										<div class="col-lg-6">
											<label>Tipo de Transferência <span class="text-danger">*</span></label>
											<select id="inputTipoTransferencia" name="inputTipoTransferencia" class="select-search" required>
												<option value=''>Selecione</option>
												<?php
													$sql = "SELECT AtModId,AtModDescricao
													FROM AtendimentoModelo
													JOIN AtendimentoTipoModelo ON AtTMoId = AtModTipoModelo
													WHERE AtTMoChave = 'ATESTADOMEDICO' and AtModUnidade = $iUnidade";
													$result = $conn->query($sql);
													$rowTipo = $result->fetchAll(PDO::FETCH_ASSOC);

													$opts = "";

													foreach($rowTipo as $tipo){
														$opts .= "<option value='$tipo[AtModId]'>$tipo[AtModDescricao]</option>";
													}
													echo $opts;
												?>
											</select>
										</div>
										<div class="col-lg-6">
											<label>CID-10</label>
											<select id="inputCid" name="inputCid" class="select-search" required>
												<option value=''>Selecione</option>
												<?php
													$sql = "SELECT Cid10Id,Cid10Capitulo,Cid10Codigo,Cid10Descricao
													FROM Cid10";
													$result = $conn->query($sql);
													$rowCid = $result->fetchAll(PDO::FETCH_ASSOC);

													$opts = "";
													foreach($rowCid as $cid){
														$opts .= "<option value='$cid[Cid10Id]'>$cid[Cid10Capitulo]-$cid[Cid10Codigo]-$cid[Cid10Descricao]</option>";
													}
													echo $opts;
												?>
											</select>
										</div>
									</div>

									<div class="row  m-0 mb-3 col-lg-12">
										<div class="col-lg-12">
											<div class="form-group">
												<textarea rows="5" cols="5" id="relatorio" name="relatorio" class="form-control" placeholder="Corpo do Relatório (informe aqui o texto que você queira que apareça no relatório de transferência)"></textarea>
											</div>
										</div>
									</div>
									<div class="row m-0 ">
										<div class="col-lg-12">
											<div class="form-group" style="padding-top:25px;">
												<button id="enviar" class="btn btn-lg btn-principal">Salvar</button>
												<a id='voltar' href="#" class="btn btn-basic">Voltar</a>
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
