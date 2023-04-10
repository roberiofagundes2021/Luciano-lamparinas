<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Encaminhamento Médico';

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

$sql = "SELECT TOP(1) AtEMeId
FROM AtendimentoEncaminhamentoMedico
WHERE AtEMeAtendimento = $iAtendimentoId
ORDER BY AtEMeId DESC";
$result = $conn->query($sql);
$rowEncaminhamentoMedico= $result->fetch(PDO::FETCH_ASSOC);

$iAtendimentoEncaminhamentoMedicoId = $rowEncaminhamentoMedico?$rowEncaminhamentoMedico['AtEMeId']:null;

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
if(isset($iAtendimentoEncaminhamentoMedicoId ) && $iAtendimentoEncaminhamentoMedicoId ){

	//Essa consulta é para preencher o campo Encaminhamento Médico ao editar
	$sql = "SELECT AtEMeEncaminhamentoMedico, AtEMeHoraFim, AtEMeHoraInicio, AtEMeDataInicio, AtEMeDataFim, AtEMeCid10
			FROM AtendimentoEncaminhamentoMedico
			WHERE AtEMeId = " . $iAtendimentoEncaminhamentoMedicoId ;
	$result = $conn->query($sql);
	$rowEncaminhamentoMedico = $result->fetch(PDO::FETCH_ASSOC);
		
	$_SESSION['msg'] = array();

	// Formatar Hora/Data

	$DataInicio = strtotime($rowEncaminhamentoMedico['AtEMeDataInicio']);
	$DataAtendimentoInicio = date("d/m/Y", $DataInicio);

	$DataFim = strtotime($rowEncaminhamentoMedico['AtEMeDataFim']);
	$DataAtendimentoFim = date("d/m/Y", $DataFim);

	$Inicio = strtotime($rowEncaminhamentoMedico['AtEMeHoraInicio']);
	$HoraInicio = date("H:i", $Inicio);

	$Fim = strtotime($rowEncaminhamentoMedico['AtEMeHoraFim']);
	$HoraFim = date("H:i", $Fim);

} 

//Se estiver gravando (inclusão ou edição)
// if (isset($_POST['txtareaConteudo']) ){

// 	try{
	

// 		//Edição
// 		if ($iAtendimentoEncaminhamentoMedicoId){
		
// 			$sql = "UPDATE AtendimentoEncaminhamentoMedico SET AtEMeAtendimento = :sAtendimento, AtEMeDataInicio = :dDataInicio, AtEMeDataFim = :dDataFim, AtEMeHoraInicio = :sHoraInicio,
// 						   AtEMeHoraFim  = :sHoraFim, AtEMeProfissional = :sProfissional, AtEMeCid10 = :iCid10, AtEMeEncaminhamentoMedico = :sEncaminhamentoMedico, AtEMeUnidade = :iUnidade
// 					WHERE AtEMeId = :iAtendimentoEncaminhamentoMedico";
// 			$result = $conn->prepare($sql);
					
// 			$result->execute(array(
// 				':sAtendimento' => $iAtendimentoId,
// 				':dDataInicio' => gravaData($_POST['inputDataInicio']),
// 				':dDataFim' => date('m/d/Y'),
// 				':sHoraInicio' => $_POST['inputInicio'],
// 				':sHoraFim' => date('H:i'),
// 				':sProfissional' => $userId,
// 				':iCid10' => $_POST['cmbCid10'],
// 				':sEncaminhamentoMedico' => $_POST['txtareaConteudo'],
// 				':iUnidade' => $_SESSION['UnidadeId'],
// 				':iAtendimentoEncaminhamentoMedico' => $iAtendimentoEncaminhamentoMedicoId 
// 				));

// 			$_SESSION['msg']['mensagem'] = "Encaminhamento Médico alterado!!!";
			

// 		} else { //inclusão

// 			$sql = "INSERT INTO AtendimentoEncaminhamentoMedico (AtEMeAtendimento, AtEMeDataInicio, AtEMeDataFim, AtEMeHoraInicio, AtEMeHoraFim, AtEMeProfissional, AtEMeCid10, AtEMeEncaminhamentoMedico, AtEMeUnidade)
// 						VALUES (:sAtendimento, :dDataInicio, :dDataFim, :sHoraInicio, :sHoraFim, :sProfissional, :iCid10, :sEncaminhamentoMedico, :iUnidade)";
// 			$result = $conn->prepare($sql);
					
// 			$result->execute(array(
// 				':sAtendimento' => $iAtendimentoId,
// 				':dDataInicio' => gravaData($_POST['inputDataInicio']),
// 				':dDataFim' => gravaData($_POST['inputDataFim']),
// 				':sHoraInicio' => $_POST['inputInicio'],
// 				':sHoraFim' => date('H:i'),
// 				':sProfissional' => $userId,
// 				':iCid10' => $_POST['cmbCid10'],
// 				':sEncaminhamentoMedico' => $_POST['txtareaConteudo'],
// 				':iUnidade' => $_SESSION['UnidadeId'],
// 			));

// 			$_SESSION['msg']['mensagem'] = "Encaminhamento Médico incluído!!!";

// 		}
	
// 		$_SESSION['msg']['titulo'] = "Sucesso";
// 		$_SESSION['msg']['tipo'] = "success";
					
// 	} catch(PDOException $e) {
		
// 		$_SESSION['msg']['titulo'] = "Erro";
// 		$_SESSION['msg']['mensagem'] = "Erro reportado com o Encaminhamento Médico!!!";
// 		$_SESSION['msg']['tipo'] = "error";	
		
// 		echo 'Error: ' . $e->getMessage();
// 	}

// 	// irpara("atendimentoEncaminhamentoMedico.php");
// }

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Encaminhamento Médico</title>

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

	<script src="global_assets/js/plugins/editors/summernote/summernote.min.js"></script>
	
	<script type="text/javascript">

		$(document).ready(function() {	
			$('#summernote').summernote();
			getCmbs()
			checkEncaminhamentos()

			$('#encaminhamentoTable').DataTable( {
				"order": [[ 0, "asc" ]],
			    autoWidth: false,
				responsive: true,
			    columnDefs: [
				{ 
					orderable: true,   //item
					width: "5%", //15
					targets: [0]
				},
				{ 
					orderable: true,   //data-hora
					width: "15%", //20
					targets: [1]
				},
				{ 
					orderable: true,   //grupo
					width: "30%", //15
					targets: [2]
				},				
				{ 
					orderable: true,   //subgrupo
					width: "30%", //15
					targets: [3]
				},
				{ 
					orderable: true,   //procedimento
					width: "5%", //15
					targets: [4]
				}],
				dom: '<"datatable-header"fl><"datatable-scroll-wrap"t><"datatable-footer">',
				language: {
					search: '<span>Filtro:</span> _INPUT_',
					searchPlaceholder: 'filtra qualquer coluna...',
					lengthMenu: '<span>Mostrar:</span> _MENU_',
					paginate: { 'first': 'Primeira', 'last': 'Última', 'next': $('html').attr('dir') == 'rtl' ? '&larr;' : '&rarr;', 'previous': $('html').attr('dir') == 'rtl' ? '&rarr;' : '&larr;' }
				}
                
			});
        
			$('#enviar').on('click', function(e){
				e.preventDefault();
				$( "#formAtendimentoEncaminhamentoMedico" ).submit()
			})

			$('#enviar').on('click', function(e){
				e.preventDefault()
				// vai preencher cmbProfissionais
				let menssagem = ''

				switch(menssagem){
					case $('#profissional').val(): menssagem='Informe o profissional!';$('#profissional').focus();break;
					case $('#especialidade').val(): menssagem='Informe a especialidade!';$('#especialidade').focus();break;
					case $('#modelo').val(): menssagem='Informe o tipo de encaminhamento!';$('#modelo').focus();break;
					case $('#summernote').val(): menssagem='Informe o encaminhamento médico!';$('#summernote').focus();break;
					default: menssagem='';break;
				}
				if(menssagem){
					alerta('Campo obrigatório!!', menssagem, 'error')
					return
				}
				$.ajax({
					type: 'POST',
					url: 'filtraAtendimento.php',
					dataType: 'json',
					data:{
						'tipoRequest': 'SALVARENCAMINHAMENTO',
						'id': '<?php echo $iAtendimentoId ?>',
						'dataI': '<?php if (isset($iAtendimentoEletivoId )){ echo $DataAtendimentoInicio;} else { echo date('d/m/Y'); } ?>',
						'horaI': '<?php if (isset($iAtendimentoEletivoId )){ echo $HoraInicio;} else { echo date('H:i'); } ?>',
						'dataF': '<?php if (isset($iAtendimentoEletivoId )){ echo $DataAtendimentoFim;} else { echo date('d/m/Y'); } ?>',
						'horaF': '<?php if (isset($iAtendimentoEletivoId )) echo $HoraFim; ?>',
						'profissional': '<?php echo $userId ?>',
						'profissionalDestino': $('#profissional').val(),
						'especialidade': $('#especialidade').val(),
						'modelo': $('#modelo').val(),
						'cid': $('#cid').val(),
						'encaminhamentoMedico': $('#summernote').val(),
					},
					success: function(response) {
						alerta(response.titulo, response.menssagem, response.status);
						$('#profissional').val('').change();
						$('#especialidade').val('').change();
						$('#modelo').val('').change();
						$('#cid').val('').change();
						$('#summernote').summernote('code', '');
						getCmbs()
						checkEncaminhamentos()
					}
				});
			})

			$('#formAtendimentoEncaminhamentoMedico').submit(function(e){
				e.preventDefault()
			})
			$('#summernote').on('summernote.change', function(){
				cantaCaracteres("summernote", 2000, "caracteresInputEncaminhamentoMedico")
			})
			$('#modelo').on('change', function(){
				// vai preencher MODELOS
				$.ajax({
					type: 'POST',
					url: 'filtraAtendimento.php',
					dataType: 'json',
					data:{
						'tipoRequest': 'CONTEUDOMODELO',
						'id': $(this).val()
					},
					success: function(response) {
						$('#summernote').val('')
						$('#summernote').summernote('code', response.conteudo)
						cantaCaracteres("summernote", 2000, "caracteresInputEncaminhamentoMedico")
					}
				})
			})
		})//document.ready

		async function filtrarEspecialidades() {
			// vai preencher especialidade
			await $.ajax({
				type: 'POST',
				url: 'filtraAtendimento.php',
				dataType: 'json',
				data:{
					'tipoRequest': 'ESPECIALIDADES',
					'id': $('#profissional').val()
				},
				success: function(response) {
					$('#especialidade').empty();
					$('#especialidade').append(`<option value=''>Selecione</option>`)
					response.forEach(item => {
						let opt = `<option value="${item.id}">${item.nome}</option>`
						$('#especialidade').append(opt)
					})
				}
			});			
		}

		function getCmbs(){
			// limpa o campo text
			$('#summernote').val('')

			// vai preencher MEDICOS
			$.ajax({
				type: 'POST',
				url: 'filtraAtendimento.php',
				dataType: 'json',
				data:{
					'tipoRequest': 'MEDICOS'
				},
				success: function(response) {
					$('#profissional').empty();
					$('#profissional').append(`<option value=''>Selecione</option>`)
					response.forEach(item => {
						let opt = `<option value="${item.id}">${item.nome}</option>`
						$('#profissional').append(opt)
					})
				}
			});
			// vai preencher MODELOS
			$.ajax({
				type: 'POST',
				url: 'filtraAtendimento.php',
				dataType: 'json',
				data:{
					'tipoRequest': 'MODELOS',
					'chave': 'EMCAMINHAMENTO'
				},
				success: function(response) {
					$('#modelo').empty();
					$('#modelo').append(`<option value=''>Selecione</option>`)
					response.forEach(item => {
						let opt = `<option value="${item.id}">${item.nome}</option>`
						$('#modelo').append(opt)
					})
				}
			});
			// vai preencher CID10
			$.ajax({
				type: 'POST',
				url: 'filtraAtendimento.php',
				dataType: 'json',
				data:{
					'tipoRequest': 'CID10'
				},
				success: function(response) {
					$('#cid').empty();
					$('#cid').append(`<option value=''>Selecione</option>`)
					response.forEach(item => {
						let opt = `<option value="${item.id}">${item.capitulo} - ${item.codigo} - ${item.descricao}</option>`
						$('#cid').append(opt)
					})
				}
			});
		}
		function checkEncaminhamentos(){
			// vai preencher MEDICOS
			$.ajax({
				type: 'POST',
				url: 'filtraAtendimento.php',
				dataType: 'json',
				data:{
					'tipoRequest': 'ENCAMINHAMENTOS',
					'id': <?php echo $iAtendimentoId?>,
					'situaChave' : $("#atendimentoSituaChave").val()
				},
				success: function(response) {

					let tableE = $('#encaminhamentoTable').DataTable().clear().draw()
					let rowNodeE

					response.forEach(item => {
						rowNodeE = tableE.row.add(item).draw().node()
					})

					/*if(response.length){						
						$('#dataEncaminhamento').html('')
						let HTML = ''
						response.forEach((item,index) => {

							let situaChave = $("#atendimentoSituaChave").val();
							let exc = `<a style='color: black; cursor:pointer' onclick='excluiServico(\"${item.id}\")' class='list-icons-item'><i class='icon-bin' title='Excluir Encaminhamento'></i></a>`;
							let copiar = `<a class='list-icons-item mr-2 ' style='color: black; cursor:pointer' onclick='copiarEncaminhamento( \"${item.idProfissionalDestino}\", \"${item.idEspecialidade}\", \"${item.idModelo}\", \"${item.idCid10}\", \"${item.encaminhamentoMedico}\"  )'><i class='icon-files-empty' title='Copiar Encaminhamento'></i></a>`; 
							let print = `<a style='color: black; cursor:pointer' onclick='imprimirServico(\"${item.id}\")' class='list-icons-item'><i class='icon-printer2' title='Imprimir Encaminhamento'></i></a>`;
							let acoes = '';			

							if (situaChave != 'ATENDIDO'){
								acoes = `<div class='list-icons'>
                                        ${print}
										${copiar}
										${exc}
									</div>`;
							} else{
								acoes = `<div class='list-icons'>
										${print}
										${copiar}
									</div>`;
							}
							HTML += `
							<tr class='servicoItem'>profissional
								<td class="text-left">${index+1}</td>
								<td class="text-left">${item.data} ${item.hora}</td>
								<td class="text-left">${item.profissional}</td>
								<td class="text-left">${item.especialidade}</td>
								<td class="text-center">${acoes}</td>
							</tr>`
						})
						$('#dataEncaminhamento').html(HTML)
					}else{
						$('#dataEncaminhamento').html('')
					}*/
				}
			});
		}

		async function copiarEncaminhamento(idProfissionalDestino, idEspecialidade, idModelo, idCid10, encaminhamentoMedico) {
			
			await $('#profissional').val(idProfissionalDestino).change();
			await filtrarEspecialidades();
			$('#especialidade').val(idEspecialidade).change();
			$('#modelo').val(idModelo).change();
			$('#cid').val(idCid10).change();
			$('#summernote').summernote('code', encaminhamentoMedico);
		}

		function excluiServico(id){
			confirmaExclusaoAjax('filtraAtendimento.php', 'Excluir Solicitação de Procedimento?', 'EXCLUIRENCAMINHAMENTO', id, checkEncaminhamentos);
			getCmbs()
		}

		function imprimirServico(id){
			console.log(id)
		}
		function cantaCaracteres(htmlId, numCaracteres, htmlIdMostra){
			if($(`#${htmlId}`).val().length >= numCaracteres){
				$(`#${htmlId}`).val($(`#${htmlId}`).val().substring(0, numCaracteres));
			}
			let numCaracteresRestantes = numCaracteres - $(`#${htmlId}`).val().length
			$(`#${htmlIdMostra}`).html(numCaracteresRestantes!=numCaracteres? numCaracteresRestantes+' restantes':'')
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

						<form name="formAtendimentoEncaminhamentoMedico" id="formAtendimentoEncaminhamentoMedico" method="post" class="form-validate-jquery">
							<?php
								echo "<input type='hidden' id='iAtendimentoId' name='iAtendimentoId' value='$iAtendimentoId' />";
								echo "<input type='hidden' id='atendimentoSituaChave' value='".$_SESSION['SituaChave']."' />";
							?>
							<div class="card">
								<div class="card-header header-elements-inline">
									<h3 class="card-title"><b>ENCAMINHAMENTO MÉDICO</b></h3>
								</div>
							</div>

							<div> <?php include ('atendimentoDadosPaciente.php'); ?> </div>

							<div class="card">

								<div class="card-body">
									<div class="col-lg-12 row">
										<div class="col-lg-6">
											<label>Profissional <span class="text-danger">*</span></label>
										</div>
										<div class="col-lg-6">
											<label>Especialidade <span class="text-danger">*</span></label>
										</div>

										<div class="col-lg-6 input-group">
											<select id="profissional" name="profissional" class="form-control select-search" onChange="filtrarEspecialidades()">
												<option value="">Selecione</option>
											</select>
										</div>
										<div class="col-lg-6 input-group">
											<select id="especialidade" name="especialidade" class="form-control select-search">
												<option value="">Selecione</option>
											</select>
										</div>
									</div>

									<br/>

									<div class="col-lg-12 row">
										<div class="col-lg-6">
											<label>Tipo de encaminhamento <span class="text-danger">*</span></label>
										</div>
										<div class="col-lg-6">
											<label>CID-10</label>
										</div>

										<div class="col-lg-6 input-group">
											<select id="modelo" name="modelo" class="form-control select-search">
												<option value="">Selecione</option>
											</select>
										</div>
										<div class="col-lg-6 input-group">
											<select id="cid" name="cid" class="form-control select-search">
												<option value="">Selecione</option>
											</select>
										</div>
									</div>

									<br/>

									<div class="col-lg-12">
										<div class="form-group">
											<label>Encaminhamento Médico <span class="text-danger">*</span></label>
											<textarea rows="5" cols="5"  id="summernote" name="txtareaConteudo" class="form-control"></textarea>
											<small class="text-muted form-text">
												Máx. 2000 caracteres<br>
												<span id="caracteresInputEncaminhamentoMedico"></span>
											</small>
										</div>
									</div>	
									
									<div class="col-lg-12 mb-3">
										<button class="btn btn-lg btn-principal" id="enviar">Incluir</button>
									</div>

									<div class="col-lg-12 mt-2">
										<div class="col-lg-12 card-header p-0">
											<h5 class="card-title"><b>Encaminhamentos</b></h5>
										</div>
										<table class="table" id="encaminhamentoTable">
											<thead>
												<tr class="bg-slate">
													<th>item</th>
													<th>Data/Hora</th>
													<th>Profissional</th>
													<th>Especialidade</th>
													<!-- <th>Local</th> -->
													<th class="text-center">Ações</th>
												</tr>
											</thead>
											<tbody id="dataEncaminhamento">
												
											</tbody>
										</table>
									</div>

									<div class="row">
										<div class="col-lg-12">
											<div class="form-group" style="padding-top:25px;">	
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
