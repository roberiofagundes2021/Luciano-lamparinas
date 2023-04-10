<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Leito';

include('global_assets/php/conexao.php');

$iAtendimentoId = isset($_POST['iAtendimentoId'])?$_POST['iAtendimentoId']:null;
$iUnidade = $_SESSION['UnidadeId'];

if (isset($_SESSION['iAtendimentoId']) && $iAtendimentoId == null) {
	$iAtendimentoId = $_SESSION['iAtendimentoId'];
}
$_SESSION['iAtendimentoId'] = null;

if(!$iAtendimentoId){	
	irpara("atendimentoEletivoListagem.php");
}

$sql = "SELECT TOP(1) AtEleId
FROM AtendimentoEletivo
WHERE AtEleAtendimento = $iAtendimentoId
ORDER BY AtEleId DESC";
$result = $conn->query($sql);
$rowEletivo = $result->fetch(PDO::FETCH_ASSOC);

$iAtendimentoEletivoId = $rowEletivo?$rowEletivo['AtEleId']:null;

//Essa consulta é para verificar qual é o atendimento e cliente 
$sql = "SELECT AtendId, AtendCliente, AtendNumRegistro, AtModNome, AtendClassificacaoRisco, ClienId, ClienCodigo, ClienNome, ClienSexo, ClienDtNascimento,
               ClienNomeMae, ClienCartaoSus, ClienCelular, ClienStatus, ClienUsuarioAtualizador, ClienUnidade, ClResNome, AtTriPeso,
			   AtTriAltura, AtTriImc, AtTriPressaoSistolica, AtTriPressaoDiatolica, AtTriFreqCardiaca, AtTriTempAXI, AtClRCor
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
}else{
    $sexo = 'Masculino';
}

//Se estiver inserindo
if(isset($_POST['caraterInternaacao']) && isset($_POST['iLeitoId'])){
	$sql = "SELECT VnLeiEspecialidadeLeito
      FROM Leito
	  JOIN VincularLeitoXLeito ON VLXLeLeito = LeitoId
      JOIN VincularLeito ON VnLeiId = VLXLeVinculaLeito
      WHERE LeitoUnidade = $iUnidade and LeitoId = $_POST[iLeitoId]";
	$result = $conn->query($sql);
	$result = $result->fetch(PDO::FETCH_ASSOC);

	$inicio = date('Y-m-d H:i');

	$sql = "INSERT INTO AtendimentoXLeito(AtXLeAtendimento,AtXLeCaraterInternacao,AtXLeLeito,
		AtXLeEspecialidadeLeito,AtXLeDataHoraInicio,AtXLeUsuarioAtualizador,AtXLeUnidade)
		VALUES($iAtendimentoId,$_POST[caraterInternaacao],$_POST[iLeitoId],$result[VnLeiEspecialidadeLeito],'$inicio',$_SESSION[UsuarId],$iUnidade)";
	$result = $conn->query($sql);

	irpara('atendimento.php');
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Leito</title>

	<?php include_once("head.php"); ?>

	<script src="global_assets/js/plugins/loaders/blockui.min.js"></script>
	<script src="global_assets/js/plugins/ui/ripple.min.js"></script>
	<script src="global_assets/js/plugins/ui/moment/moment.min.js"></script>
	<script src="global_assets/js/plugins/pickers/daterangepicker.js"></script>
	<script src="global_assets/js/plugins/pickers/anytime.min.js"></script>
	<script src="global_assets/js/plugins/pickers/pickadate/picker.js"></script>
	<script src="global_assets/js/plugins/pickers/pickadate/picker.date.js"></script>
	<script src="global_assets/js/plugins/pickers/pickadate/picker.time.js"></script>
	<script src="global_assets/js/plugins/pickers/pickadate/legacy.js"></script>
	<script src="global_assets/js/plugins/notifications/jgrowl.min.js"></script>

	<!-- Theme JS files -->
	<script src="global_assets/js/demo_pages/form_select2.js"></script>
	<script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>
    <script src="global_assets/js/plugins/tables/datatables/datatables.min.js"></script>
    <script src="global_assets/js/plugins/tables/datatables/extensions/responsive.min.js"></script>
    <script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
	<script src="global_assets/js/plugins/editors/summernote/summernote.min.js"></script>

	<!-- Plugin para corrigir a ordenação por data. Caso a URL dê problema algum dia, salvei esses 2 arquivos na pasta global_assets/js/lamparinas -->
	<script type="text/javascript" language="javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.8.4/moment.min.js"></script>
	<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/plug-ins/1.10.10/sorting/datetime-moment.js"></script>	

	<!-- Modal -->
	<script src="global_assets/js/plugins/notifications/bootbox.min.js"></script>
    
    <!-- Validação -->
	<script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
	<script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
	<script src="global_assets/js/demo_pages/form_validation.js"></script>

	<script type="text/javascript">

		$(document).ready(function() {
			getLeitos()
			getFiltros()
			$('#salvarLeito').on('click', function(e){
				e.preventDefault()
				$("#formAtendimentoEletivo").submit()
			})

			$("#formAtendimentoEletivo").submit(function(){
				let leitoSelected = false
				$('.checkLeito').each(function(e){
					leitoSelected = $(this).is(':checked')?true:leitoSelected
				})
				if(!leitoSelected){
					alerta('Atenção', 'Selecione um leito na listagem abaixo!!', 'error')
					e.preventDefault()
				}
			});

			$('#filtrar').on('click', function(e){
				e.preventDefault()
				getLeitos()
			})

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

		function getFiltros(){
			// ACOMODACAO
			$.ajax({
				type: 'POST',
				url: 'filtraAtendimentoLeito.php',
				dataType: 'json',
				data: {
					'tipoRequest': 'ACOMODACAO'
				},
				success: function(response) {
					$('#acomodacao').html(`<option value=''>selecione</option>`)
					response.forEach(item => {
						$('#acomodacao').append(`<option value='${item.TpAcoId}'>${item.TpAcoNome}</option>`)
					})
				}
			})

			// INTERNACAO
			$.ajax({
				type: 'POST',
				url: 'filtraAtendimentoLeito.php',
				dataType: 'json',
				data: {
					'tipoRequest': 'INTERNACAO'
				},
				success: function(response) {
					$('#internacao').html(`<option value=''>selecione</option>`)
					response.forEach(item => {
						$('#internacao').append(`<option value='${item.TpIntId}'>${item.TpIntNome}</option>`)
					})
				}
			})

			// ESPECIALIDADE
			$.ajax({
				type: 'POST',
				url: 'filtraAtendimentoLeito.php',
				dataType: 'json',
				data: {
					'tipoRequest': 'ESPECIALIDADE'
				},
				success: function(response) {
					$('#especialidade').html(`<option value=''>selecione</option>`)
					response.forEach(item => {
						$('#especialidade').append(`<option value='${item.EsLeiId}'>${item.EsLeiNome}</option>`)
					})
				}
			})

			// ALA
			$.ajax({
				type: 'POST',
				url: 'filtraAtendimentoLeito.php',
				dataType: 'json',
				data: {
					'tipoRequest': 'ALA'
				},
				success: function(response) {
					$('#ala').html(`<option value=''>selecione</option>`)
					response.forEach(item => {
						$('#ala').append(`<option value='${item.AlaId}'>${item.AlaNome}</option>`)
					})
				}
			})
		}

		function getLeitos(){
			$.ajax({
				type: 'POST',
				url: 'filtraAtendimentoLeito.php',
				dataType: 'json',
				data: {
					'tipoRequest': 'LEITOS',
					'especialidade': $('#especialidade').val()?$('#especialidade').val():null,
					'ala': $('#ala').val()?$('#ala').val():null,
				},
				success: async function(response) {
					$('#dadosLeitos').html('')
					let total = response.leitos.length
					let ocupados = 0
					let livres = 0
					
					await response.quartos.forEach(function(quarto){

						$('#dadosLeitos').append(`<div class='col-lg-12 my-3'>
							<h4 class='font-weight-bold'>${quarto.QuartNome}</h4>
							<div class='dropdown-divider'></div>
						</div>`)
						response.leitos.forEach(function(item){
							if(item.QuartId == quarto.QuartId){
								let opt = !item.AtXLeId?`<div id="${item.LeitoId}" class='cardLeitos text-center col-lg-4 mx-1 my-1'>
									<div class='headEnable'>
										<h4 class='m-0 font-weight-bold' id='tituloCardLeito'>${item.LeitoNome}</h4>
										<p id='subTituloCardLeito'>${item.EsLeiNome}</p>
									</div>
			
									<img src="global_assets/images/lamparinas/leito-vazio.png" alt="Leito vazio" width="100" height="100">
			
									<div class='m-1'>
										<p id='tipoLeito'>${item.TpIntNome}</p>
										<input name="leitoSelect" class='checkLeito' data-pai='${item.LeitoId}' type='radio'/>
									</div>
								</div>`
								:
								`<div id="${item.LeitoId}" class='cardLeitos text-center col-lg-4 mx-1 my-1'>
									<div class='headDisable'>
										<h4 class='m-0 font-weight-bold' id='tituloCardLeito'>${item.LeitoNome}</h4>
										<p id='subTituloCardLeito'>Previsão de Alta: ${'---'}</p>
									</div>
			
									<img src="global_assets/images/lamparinas/leito-ocupado.png" alt="Leito ocupado" width="100" height="100">
			
									<div class='m-1'>
										<p id='tipoLeito'>${item.TpIntNome}</p>
									</div>
								</div>`
								ocupados = item.AtXLeId?ocupados+1:ocupados
								livres = !item.AtXLeId?livres+1:livres
								$('#dadosLeitos').append(opt)
							}		
						})
					})

					$('#leitosTotal').html(total)
					$('#leitosOcupados').html(ocupados)
					$('#leitosLivres').html(livres)

					$('.checkLeito').each(function(e){
						$(this).on('change', function(e){
							e.preventDefault()

							let idPai = $(this).data('pai')
							let idPaiOld = $('#iLeitoId').val()

							$(`#${idPai}`).toggleClass('on')
							
							if(idPaiOld){
								$(`#${idPaiOld}`).toggleClass('on')
							}
							$('#iLeitoId').val(idPai)
						})
					})
				}
			});
		}
	</script>

	<style>
		.cardLeitos{
			padding: 0px;
			max-width: 195px;
			max-height: 220px;
			background-color:#FFFF;
			border:1px solid rgba(0,0,0,0.2);
		}

		.headDisable{
			padding:1px;
			background-color:#375b82;
			color:#FFFF;
		}

		.headEnable{
			padding:1px;
			background-color:#00cb00;
			color:#FFFF;
		}

		.cardLeitos.on{
			background-color:#ebebeb;
		}
		.cardLeitos.off{
			background-color:#FFFF;
		}

		.cardLeitos i {
			font-size: 60px;
			margin: 10px;
		}
		.cardLeitosDisable i {
			font-size: 60px;
			margin: 10px;
		}
		.roundCard{
			border-radius: 100px;
			width:35px;
			height: 35px;
			color:white;
			padding-top: 5px;
		}
	</style>

</head>

<body class="navbar-top sidebar-xs">

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
				<div class="row">
					
					<div class="col-lg-12">
						<form id='dadosPost'>
							<?php
								echo "<input type='hidden' id='iAtendimentoId' name='iAtendimentoId' value='$iAtendimentoId' />";
							?>
						</form>
						<!-- Basic responsive configuration -->
						<form name="formAtendimentoEletivo" id="formAtendimentoEletivo" method="POST" class="form-validate-jquery">
							<?php
								echo "<input type='hidden' id='iAtendimentoId' name='iAtendimentoId' value='$iAtendimentoId' />";
								echo "<input type='hidden' id='iLeitoId' name='iLeitoId' value='' />";
							?>
							<div class="card">
								<div class="card-header header-elements-inline">
									<h3 class="card-title"><b>Leito Internação Hospitalar</b></h3>
								</div>
							</div>

							<div> <?php include ('atendimentoDadosPaciente.php'); ?> </div>

							<div class="card">
								<div class="card-header header-elements-inline">
									<h5 class="card-title"><b>Filtros</b></h5>
								</div>

								<div class="mt-3 col-lg-12">
									<div class="row col-lg-12 text-left mb-2">
										<div class="col-lg-3">Acomodação</div>
										<div class="col-lg-3">Internação</div>
										<div class="col-lg-3">Especialidade</div>
										<div class="col-lg-2">Ala</div>
										<div class="col-lg-1"> </div>
									</div>

									<div class="row col-lg-12 mb-3">
										<div class="col-lg-3">
											<select id="acomodacao" name="acomodacao" class="select-search">
												<option value="">selecione</option>
											</select>
										</div>
										<div class="col-lg-3">
											<select id="internacao" name="internacao" class="select-search">
												<option value="">selecione</option>
											</select>
										</div>
										<div class="col-lg-3">
											<select id="especialidade" name="especialidade" class="select-search">
												<option value="">selecione</option>
											</select>
										</div>
										<div class="col-lg-2">
											<select id="ala" name="ala" class="select-search">
												<option value="">selecione</option>
											</select>
										</div>
										<div class="col-lg-1">
											<button class="btn btn-principal" id="filtrar">Filtrar</button>
										</div>
									</div>
								</div>
							</div>

							<div class="card p-2">
								<div class="card-header header-elements-inline">
									<h5 class="card-title"><b>Dados do Leito</b></h5>
								</div>

								<div class="col-lg-12 row p-0 m-0">
									<div class="col-lg-3">
										<div class="col-lg-10">
											Caráter da Internação <span class="text-danger">*</span>
										</div>
										<div class="col-lg-10 form-group">
											<select id="caraterInternaacao" name="caraterInternaacao" class="select-search" required>
												<option value="">selecione</option>
												<?php
													$sql = "SELECT CrIntId,CrIntNome
													FROM CaraterInternacao
													JOIN Situacao ON SituaId = CrIntStatus
													WHERE CrIntUnidade = $iUnidade and SituaChave = 'ATIVO'";
													$result = $conn->query($sql);
													$result = $result->fetchAll(PDO::FETCH_ASSOC);

													foreach($result as $item){
														echo "<option value='$item[CrIntId]'>$item[CrIntNome]</option>";
													}
												?>
											</select>
										</div>
										<div class="col-lg-9">
										</div>
									</div>
									<div class="col-lg-7"></div>
									<div class="col-lg-2">
										<div class="col-lg-12 row mb-1">
											<div class="col-lg-10">
												Total de Leitos:
											</div>
											<div class="col-lg-2">
												<div style="background-color:#a9a9a9; border: 1px solid #5a5a5a;" id='leitosTotal' class='roundCard text-center'>0</div>
											</div>
										</div>
										<div class="col-lg-12 row mb-1">
											<div class="col-lg-10">
												Leitos Ocupados:
											</div>
											<div class="col-lg-2">
												<div style="background-color:#375b82; border: 1px solid #00008b;" id='leitosOcupados' class='roundCard text-center'>0</div>
											</div>
										</div>
										<div class="col-lg-12 row mb-1">
											<div class="col-lg-10">
												Leitos Livres:
											</div>
											<div class="col-lg-2">
												<div style="background-color:#00cb00; border:1px solid #176017;" id='leitosLivres' class='roundCard text-center'>0</div>
											</div>
										</div>
									</div>
								</div>

								<div id="dadosLeitos" class="col-lg-12 row m-0 p-0 justify-content-right">
									
								</div>
								<div class="col-lg-12 ml-2 my-4 row text-right">
									<button class="btn btn-lg btn-principal" id="salvarLeito">Salvar</button>
									<a href="atendimento.php" class="btn btn-link legitRipple">Cancelar</a>
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
