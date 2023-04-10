<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Receituário';

include('global_assets/php/conexao.php');

$iAtendimentoId = isset($_POST['iAtendimentoId'])?$_POST['iAtendimentoId']:null;

$SituaChave = $_SESSION['SituaChave'];

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

if(isset($_POST['iReceituario'])){
	$iAtendimentoReceituarioId = $_POST['iReceituario'];
}else{
	$sql = "SELECT TOP(1) AtRecId
	FROM AtendimentoReceituario
	WHERE AtRecAtendimento = $iAtendimentoId AND AtRecDataFim IS NULL";
	$result = $conn->query($sql);
	$rowReceituario= $result->fetch(PDO::FETCH_ASSOC);
	$iAtendimentoReceituarioId = $rowReceituario?$rowReceituario['AtRecId']:null;
}
$sql = "SELECT TOP(1) AtRecId
	FROM AtendimentoReceituario
	WHERE AtRecAtendimento = $iAtendimentoId AND AtRecDataFim IS NULL";
$result = $conn->query($sql);
$rowReceituario= $result->fetch(PDO::FETCH_ASSOC);
$iAtendimentoReceituarioId = $rowReceituario?$rowReceituario['AtRecId']:null;

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
//Se estiver gravando (inclusão ou edição)
if (isset($_POST['txtareaConteudo']) && isset($_POST['receituario']) && $_POST['receituario']){
	try{
		//Edição
		if ($iAtendimentoReceituarioId){
			$sql = "UPDATE AtendimentoReceituario SET AtRecAtendimento = :sAtendimento, AtRecDataInicio = :dDataInicio, AtRecDataFim = :dDataFim, AtRecHoraInicio = :sHoraInicio,
					AtRecTipoReceituario = :TipoReceituario, AtRecHoraFim  = :sHoraFim, AtRecProfissional = :sProfissional, AtRecReceituario = :sReceituario, AtRecUnidade = :iUnidade
					WHERE AtRecId = :iAtendimentoReceituario";
			$result = $conn->prepare($sql);

			$result->execute(array(
				':sAtendimento' => $iAtendimentoId,
				':dDataInicio' => gravaData($_POST['inputDataInicio']),
				':dDataFim' => $_POST['inputDataFimReceituario']?$_POST['inputDataFimReceituario']:NULL,
				':TipoReceituario' => $_POST['receituario'],
				':sHoraInicio' => $_POST['inputInicio'],
				':sHoraFim' => $_POST['inputHoraFimReceituario']?$_POST['inputHoraFimReceituario']:NULL,
				':sProfissional' => $userId,
				':sReceituario' => $_POST['txtareaConteudo'],
				':iUnidade' => $_SESSION['UnidadeId'],
				':iAtendimentoReceituario' => $iAtendimentoReceituarioId
				));

			$_SESSION['msg']['titulo'] = "Sucesso";
			$_SESSION['msg']['mensagem'] = "Receituário alterada!!!";
			$_SESSION['msg']['tipo'] = "success";

		} else { //inclusão
			$sql = "INSERT INTO AtendimentoReceituario (AtRecAtendimento, AtRecDataInicio, AtRecTipoReceituario, AtRecDataFim, AtRecHoraInicio, AtRecHoraFim, AtRecProfissional, AtRecReceituario, AtRecUnidade)
						VALUES (:sAtendimento, :dDataInicio, :TipoReceituario, :dDataFim, :sHoraInicio, :sHoraFim, :sProfissional,:sReceituario, :iUnidade)";
			$result = $conn->prepare($sql);
					
			$result->execute(array(
				':sAtendimento' => $iAtendimentoId,
				':dDataInicio' => gravaData($_POST['inputDataInicio']),
				':dDataFim' => $_POST['inputDataFimReceituario']?$_POST['inputDataFimReceituario']:NULL,
				':TipoReceituario' => $_POST['receituario'],
				':sHoraInicio' => $_POST['inputInicio'],
				':sHoraFim' => $_POST['inputHoraFimReceituario']?$_POST['inputHoraFimReceituario']:NULL,
				':sProfissional' => $userId,
				':sReceituario' => $_POST['txtareaConteudo'],
				':iUnidade' => $_SESSION['UnidadeId'],
			));

			$_SESSION['msg']['mensagem'] = "Receituário incluída!!!";

		}

		if(isset($_POST['inputDataFimReceituario']) && $_POST['inputDataFimReceituario']){
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
		}
	
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['tipo'] = "success";
					
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro reportado com a Receituário!!!";
		$_SESSION['msg']['tipo'] = "error";	
		
		echo 'Error: ' . $e->getMessage();
	}
	$_SESSION['iAtendimentoId'] = $iAtendimentoId;
	irpara("atendimentoReceituario.php");
}

//Se estiver editando
if(isset($iAtendimentoReceituarioId) && $iAtendimentoReceituarioId){

	//Essa consulta é para preencher o campo Receituário ao editar
	$sql = "SELECT AtRecReceituario, AtRecHoraFim, AtRecHoraInicio, AtRecDataInicio, AtRecDataFim, AtRecTipoReceituario
	FROM AtendimentoReceituario
	WHERE AtRecId = $iAtendimentoReceituarioId";
	$result = $conn->query($sql);
	$rowReceituario = $result->fetch(PDO::FETCH_ASSOC);

	$_SESSION['msg'] = array();

	if($rowReceituario){
		// Formatar Hora/Data
		$DataInicio = strtotime($rowReceituario['AtRecDataInicio']);
		$DataAtendimentoInicio = date("d/m/Y", $DataInicio);

		$DataFim = strtotime($rowReceituario['AtRecDataFim']);
		$DataAtendimentoFim = date("d/m/Y", $DataFim);

		$Inicio = strtotime($rowReceituario['AtRecHoraInicio']);
		$HoraInicio = date("H:i", $Inicio);

		$Fim = strtotime($rowReceituario['AtRecHoraFim']);
		$HoraFim = date("H:i", $Fim);
	}
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
				$('#tipo').val('ATT')
				$( "#formAtendimentoReceituario" ).submit()
			})

			$('#finalizarBTN').on('click', function(e){
				e.preventDefault()
				$('#tipo').val('NEW')
				let data = new Date().toLocaleString("pt-BR", {timeZone: "America/Bahia"})
				let hora = ''

				hora = data.split(' ')[1]
				data = data.split(' ')[0] // dd/mm/yyyy

				data = data.split('/')
				data = data[2]+'-'+data[1]+'-'+data[0] // yyyy-mm-dd

				$('#inputHoraFimReceituario').val(hora)
				$('#inputDataFimReceituario').val(data)
				$( "#formAtendimentoReceituario" ).submit()
			})

			$('.selectItem').each(function(){
				$(this).on('click', function(element){
					element.preventDefault()
					$.ajax({
						type: 'POST',
						url: 'filtraAtendimentoReceituario.php',
						dataType: 'json',
						data: {
							'tipoRequest': 'GETRECEITUARIO',
							'iReceituario': $(this).data('id')
						},
						success: function(response) {
							$('#summernote').val(response.receituario)
							$('#receituario').val(response.tipoReceituario)

							$('#receituario').children("option").each(function(index, item){
								if($(item).val() == response.tipoReceituario){
									$(item).change()
								}
							})
						}
					})
				})
			})
			$('.imprimirReceituario').each(function(){
				$(this).on('click', function(element){
					element.preventDefault()
				})
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
						<form name="formAtendimentoReceituario" id="formAtendimentoReceituario" method="post" class="form-validate-jquery">
							<?php
								$tipo = isset($_POST['tipo'])?$_POST['tipo']:null;
								echo "<input type='hidden' id='inputDataFimReceituario' name='inputDataFimReceituario' value='".($rowReceituario?$rowReceituario['AtRecDataFim']:'')."'/>";
								echo "<input type='hidden' id='inputHoraFimReceituario' name='inputHoraFimReceituario' value='".($rowReceituario?$rowReceituario['AtRecHoraFim']:'')."'/>";
								echo "<input type='hidden' id='iAtendimentoId' name='iAtendimentoId' value='$iAtendimentoId' />";
								echo "<input type='hidden' id='iReceituario' name='iReceituario' value='".($iAtendimentoReceituarioId?$iAtendimentoReceituarioId:'')."' />";
							?>

							<div class="card">
								<div class="card-header header-elements-inline">
									<h3 class="card-title"><b>RECEITUÁRIO</b></h3>
								</div>
							</div>

							<div> <?php include ('atendimentoDadosPaciente.php'); ?> </div>

							<div class="card">

								<div class="card-body">
									<div class="row mb-3">
										<div class="col-lg-3">
											<label>Tipo de Receituário <span class="text-danger">*</span></label>
											<select id="receituario" name="receituario" class="select-search" required>
												<option value=''>Selecione</option>
												<?php
													$opts = '';
													if($rowReceituario && $rowReceituario['AtRecTipoReceituario']){
														$tipo = $rowReceituario['AtRecTipoReceituario'];

														$opts .= $tipo == 'S'?"<option selected value='S'>Simples</option>":"<option value='S'>Simples</option>";
														$opts .= $tipo == 'E'?"<option selected value='E'>Especial</option>":"<option value='E'>Especial</option>";
													}
													echo $opts?$opts:"<option value='S'>Simples</option>
														<option value='E'>Especial</option>";
												?>
											</select>
										</div>
									</div>

									<div class="row">
										<div class="col-lg-12">
											<div class="form-group">
												<textarea rows="5" cols="5"  id="summernote" name="txtareaConteudo" class="form-control" placeholder="Corpo do receituário (informe aqui o texto que você queira que apareça no receituário)"><?php if (isset($iAtendimentoReceituarioId )) echo $rowReceituario['AtRecReceituario']; ?></textarea>
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-lg-12">
											<div class="form-group" style="padding-top:25px;">
												<?php
													echo "<button class='btn btn-lg btn-success mr-1' id='enviar'>Adicionar Receita</button>";
												?>
												<?php
													if($rowReceituario && !$rowReceituario['AtRecDataFim']){
														echo "<a id='imprimirBTN' href='#' class='btn btn-basic' role='button'>Imprimir</a>";
														if (isset($SituaChave) && $SituaChave != "ATENDIDO") {
															echo "<a id='finalizarBTN' href='#' class='btn btn-basic' role='button'>Finalizar Atendimento</a>";
														}	
													}

													if (isset($ClaChave) && $ClaChave == "ELETIVO") {
														echo "<a href='atendimentoEletivoListagem.php' class='btn btn-basic' role='button'>Voltar</a>";
													} elseif (isset($ClaChave) && $ClaChave == "AMBULATORIAL") {
														echo "<a href='atendimentoAmbulatorialListagem.php' class='btn btn-basic' role='button'>Voltar</a>";
													} elseif (isset($ClaChave) && $ClaChave == "HOSPITALAR") {
														echo "<a href='atendimentoHospitalarListagem.php' class='btn btn-basic' role='button'>Voltar</a>";
													}
												?>
											</div>
										</div>
									</div>

									<div>
										<div id="box-agendamentos" style="display: block;">
											<table class="table" id="receituarioTable">
												<thead>
													<tr class="bg-slate text-left">
														<th>Item</th>
														<th>Data Registro</th>
														<th>Tipo de Receituário</th>
														<th>Profissional</th>
														<th>CBO</th>
														<th>Ações</th>
													</tr>
												</thead>
												<tbody>
													<?php
														$sql = "SELECT  AtRecId,AtRecDataFim,AtRecDataInicio,AtRecTipoReceituario, ProfiNome, ProfiCodigo,AtRecReceituario
																FROM AtendimentoReceituario
																JOIN Profissional ON ProfiId = AtRecProfissional
																WHERE AtRecAtendimento = $iAtendimentoId";
														$result = $conn->query($sql);
														$rowReceituarioHistorico = $result->fetchAll(PDO::FETCH_ASSOC);

														foreach($rowReceituarioHistorico as $key => $item){
															$acoes = "
															<a href='#' data-id='$item[AtRecId]' class='list-icons-item selectItem' title='Copiar Receita'><i class='icon-files-empty'></i></a>
															<a href='#' data-id='$item[AtRecId]' class='list-icons-item imprimirReceituario' title='Imprimir'><i class='icon-printer2'></i></a>";

															$tipoReceituario = $item['AtRecTipoReceituario']=='S'?'Simples':'Especial';
															$data = mostraData($item['AtRecDataInicio']);
															echo "<tr>
																	<td>".($key+1)."</td>
																	<td>$data</td>
																	<td>$tipoReceituario</td>
																	<td>$item[ProfiNome]</td>
																	<td>$item[ProfiCodigo]</td>
																	<td>$acoes</td>
																</tr>";
														}
													?>
												</tbody>
											</table>
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
