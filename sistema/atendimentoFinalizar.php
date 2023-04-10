<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Receituário';

include('global_assets/php/conexao.php');

$iAtendimentoId = isset($_POST['iAtendimentoId'])?$_POST['iAtendimentoId']:null;
$iUnidade = $_SESSION['UnidadeId'];
$usuarioId = $_SESSION['UsuarId'];

if (isset($_SESSION['iAtendimentoId']) && !$iAtendimentoId) {
	$iAtendimentoId = $_SESSION['iAtendimentoId'];
}else if($iAtendimentoId){
	$_SESSION['iAtendimentoId'] = $iAtendimentoId;
}

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

if(isset($_POST['conduta'])){	
	// essa parte vai pegar os dados do atendimento para criar outro atendimento baseado no antigo
	$creatNew = false;
	switch($_POST['conduta']){
		case "AMBULATORIAL":$creatNew = true;break;
		case "HOSPITALAR":$creatNew = true;break;
		default: $creatNew = false;
	}

	if(!$creatNew){
		// essa parde vai setar a situação do atendimento antigo como "ATENDIDO"

		$sql = "SELECT SituaId FROM Situacao WHERE SituaChave = 'ATENDIDO'";
		$result = $conn->query($sql);
		$situacao = $result->fetch(PDO::FETCH_ASSOC);

		$sql = "UPDATE Atendimento SET AtendSituacao = '$situacao[SituaId]' WHERE AtendId = '$iAtendimentoId'";
		$conn->query($sql);

		switch($_SESSION['UltimaPagina']){
			case "ELETIVO":irpara("atendimentoEletivoListagem.php");break;
			case "AMBULATORIAL":irpara("atendimentoAmbulatorialListagem.php");break;
			case "HOSPITALAR":irpara("atendimentoHospitalarListagem.php");break;
			default: irpara("atendimentoEletivoListagem.php");break;
		}
	} else{
		// busca a classificação
		$sql = "SELECT AtClaId FROM AtendimentoClassificacao WHERE AtClaChave = '$_POST[conduta]'
		AND AtClaUnidade = $iUnidade";
		$result = $conn->query($sql);
		$classificacao = $result->fetch(PDO::FETCH_ASSOC);
	
		// busca o Atendimento antigo
		$sql = "SELECT AtendAgendamento, AtendNumRegistro, AtendDataRegistro, AtendCliente, AtendModalidade,
		AtendResponsavel, AtendClassificacao, AtendClassificacaoRisco, AtendObservacao, AtendJustificativa,
		AtendSituacao, AtendUsuarioAtualizador, AtendUnidade
		FROM Atendimento WHERE AtendId = $iAtendimentoId AND AtendUnidade = $iUnidade";
		$result = $conn->query($sql);
		$atendimento = $result->fetch(PDO::FETCH_ASSOC);

		$mes = date('m');

		$sql = "SELECT AtendNumRegistro FROM Atendimento WHERE AtendNumRegistro LIKE '%A$mes-%'
				ORDER BY AtendId DESC";
		$result = $conn->query($sql);
		$rowCodigo = $result->fetchAll(PDO::FETCH_ASSOC);

		$intaValCodigo = COUNT($rowCodigo)?intval(explode('-',$rowCodigo[0]['AtendNumRegistro'])[1])+1:1;

		$numRegistro = "A$mes-$intaValCodigo";
	
		// trata os dados para serem inseridos no banco
		$AtendAgendamento = $atendimento['AtendAgendamento'];
		$AtendNumRegistro = $numRegistro;
		$AtendDataRegistro = date('Y-m-d');
		$AtendCliente = $atendimento['AtendCliente'];
		$AtendModalidade = $atendimento['AtendModalidade'];
		$AtendResponsavel = $atendimento['AtendResponsavel'];
		$AtendClassificacao = $classificacao['AtClaId'];
		$AtendClassificacaoRisco = $atendimento['AtendClassificacaoRisco'];
		$AtendObservacao = $atendimento['AtendObservacao'];
		$AtendJustificativa = $atendimento['AtendJustificativa'];
		$AtendSituacao = $atendimento['AtendSituacao'];
		$AtendUsuarioAtualizador = $usuarioId;
		$AtendUnidade = $iUnidade;
	
		// insere no banco os novos dados
		$sql = "INSERT INTO Atendimento(AtendAgendamento, AtendNumRegistro, AtendDataRegistro, AtendCliente, AtendModalidade,
				AtendResponsavel, AtendClassificacao, AtendClassificacaoRisco, AtendObservacao, AtendJustificativa,
				AtendSituacao, AtendUsuarioAtualizador, AtendUnidade)
				VALUES('$AtendAgendamento','$AtendNumRegistro','$AtendDataRegistro','$AtendCliente','$AtendModalidade',
				'$AtendResponsavel','$AtendClassificacao','$AtendClassificacaoRisco','$AtendObservacao',
				'$AtendJustificativa','$AtendSituacao','$AtendUsuarioAtualizador','$AtendUnidade')";
		$conn->query($sql);

		// $_SESSION['iAtendimentoId'] = $conn->lastInsertId();

		// essa parde vai setar a situação do atendimento antigo como "ATENDIDO"
		$sql = "SELECT SituaId FROM Situacao WHERE SituaChave = 'ATENDIDO'";
		$result = $conn->query($sql);
		$situacao = $result->fetch(PDO::FETCH_ASSOC);

		$sql = "UPDATE Atendimento SET AtendSituacao = $situacao[SituaId],
		AtendDesfechoChave = '$_POST[conduta]'
		WHERE AtendId = '$iAtendimentoId'";
		$conn->query($sql);

		switch($_POST['conduta']){
			case "AMBULATORIAL":irpara("atendimentoObservacaoEntrada.php");break;
			case "HOSPITALAR":irpara("atendimentoInternacaoEntrada.php");break;
			default: irpara("atendimentoAmbulatorialListagem.php");break;
		}
	}
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Finalizar atendimento</title>

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
			$('#encerrar').on('click', function(e){
				e.preventDefault()
				let URL
				switch($('#conduta').val()){
					case 'COMRECEITA': URL = 'atendimentoReceituario.php';break;
					case 'SEMRECEITA': URL = 'atendimentoFinalizar.php';break;
					case 'LIBERADO': URL = 'atendimentoFinalizar.php';break;
					case 'AMBULATORIAL': URL = 'atendimentoFinalizar.php';break;
					case 'HOSPITALAR': URL = 'atendimentoFinalizar.php';break;
					case 'TRANSFERENCIA': URL = 'atendimentoTransferencia.php';break;
					default: URL = 'atendimentoFinalizar.php';
				}
				$( "#formAtendimentoFinalizar" ).attr('action', URL)
				$( "#formAtendimentoFinalizar" ).submit()
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
						<form id='dadosPost' method="post">
							<?php
								echo "<input type='hidden' id='iAtendimentoId' name='iAtendimentoId' value='$iAtendimentoId' />";
							?>
						</form>
						<!-- Basic responsive configuration -->
						<form name="formAtendimentoFinalizar" id="formAtendimentoFinalizar" method="post" class="form-validate-jquery">
							<div class="card">
								<div class="card-header header-elements-inline">
									<h3 class="card-title"><b>Finalizar Atendimento</b></h3>
								</div>
							</div>

							<div> <?php include ('atendimentoDadosPaciente.php'); ?> </div>

							<div class="card">

								<div class="card-body">

									<div class="col-lg-12 row mb-3">
										<div class="col-lg-12">
											<label>Conduta <span class="text-danger">*</span></label>
										</div>
										<div class="col-lg-6">
											<select id="conduta" name="conduta" class="select-search" required>
												<option value=''>Selecione</option>
												<option value='COMRECEITA'>Residência com Receita</option>
												<option value='SEMRECEITA'>Residência sem Receita</option>
												<option value='LIBERADO'>Liberado após exames/procedimentos solicitados</option>
												
												<option value='AMBULATORIAL'>Observação Hospitalar</option>
												<option value='HOSPITALAR'>Internação Hospitalar</option>
												<option value='TRANSFERENCIA'>Transferência</option>
											</select>
										</div>
									</div>
									<div class="row">
										<div class="col-lg-12">
											<div class="form-group" style="padding-top:25px;">
												<button class="btn btn-lg btn-principal" id="encerrar">ENCERRAR</button>
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
