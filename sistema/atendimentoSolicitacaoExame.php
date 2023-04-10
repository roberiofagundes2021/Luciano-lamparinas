<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Solicitacao de Exame';

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

$sql = "SELECT TOP(1) AtSExId
FROM AtendimentoSolicitacaoExame
WHERE AtSExAtendimento = $iAtendimentoId
ORDER BY AtSExId DESC";
$result = $conn->query($sql);
$rowSolicitacaoExame= $result->fetch(PDO::FETCH_ASSOC);

$iAtendimentoSolicitacaoExameId = $rowSolicitacaoExame?$rowSolicitacaoExame['AtSExId']:null;

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
               ClienNomeMae, ClienCartaoSus, ClienCelular, ClienStatus, ClienUsuarioAtualizador, ClienUnidade, ClResNome,SituaChave
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
if(isset($iAtendimentoSolicitacaoExameId ) && $iAtendimentoSolicitacaoExameId ){

	//Essa consulta é para preencher o campo Receituário ao editar
	$sql = "SELECT *
			FROM AtendimentoSolicitacaoExame
			WHERE AtSExId = " . $iAtendimentoSolicitacaoExameId ;
	$result = $conn->query($sql);
	$rowSolicitacaoExame = $result->fetch(PDO::FETCH_ASSOC);
		
	$_SESSION['msg'] = array();

	// Formatar Hora/Data

	$DataInicio = strtotime($rowSolicitacaoExame['AtSExDataInicio']);
	$DataAtendimentoInicio = date("d/m/Y", $DataInicio);

	$DataFim = strtotime($rowSolicitacaoExame['AtSExDataFim']);
	$DataAtendimentoFim = date("d/m/Y", $DataFim);

	$Inicio = strtotime($rowSolicitacaoExame['AtSExHoraInicio']);
	$HoraInicio = date("H:i", $Inicio);

	$Fim = strtotime($rowSolicitacaoExame['AtSExHoraFim']);
	$HoraFim = date("H:i", $Fim);

} 

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Sol. Exame</title>

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
	
	<script type="text/javascript">

		$(document).ready(function() {

			getCmbs()
			checkExames()
			
			$('#tblSolicitacaoExame').DataTable( {
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
					width: "10%", //15
					targets: [2]
				},				
				{ 
					orderable: true,   //subgrupo
					width: "15%", //15
					targets: [3]
				},
				{ 
					orderable: true,   //codigo
					width: "10%", //15
					targets: [4]
				},
				{ 
					orderable: true,   //exame
					width: "30%",
					targets: [5]
				},
				{ 
					orderable: false,   //Ações
					width: "10%",
					targets: [6]
				}],
				dom: '<"datatable-header"fl><"datatable-scroll-wrap"t><"datatable-footer">',
				language: {
					search: '<span>Filtro:</span> _INPUT_',
					searchPlaceholder: 'filtra qualquer coluna...',
					lengthMenu: '<span>Mostrar:</span> _MENU_',
					paginate: { 'first': 'Primeira', 'last': 'Última', 'next': $('html').attr('dir') == 'rtl' ? '&larr;' : '&rarr;', 'previous': $('html').attr('dir') == 'rtl' ? '&rarr;' : '&larr;' }
				}
                
			});
	

			$('#adicionarExame').on('click', function(e) {
				e.preventDefault();

				let menssageError = '';

				let grupo = $('#grupo').val();
				let subgrupo = $('#subgrupo').val();
				let exame = $('#exame').val();
				let justificativa = $('#justificativa').val();
				let urgente = $('#urgente').val();
				let profissional = <?php echo $userId; ?>

				let iAtendimentoId = $('#iAtendimentoId').val();
				
				switch (menssageError) {
					
					case grupo:
						menssageError = 'Informe o Grupo';
						$('#cmbProcRealizado').focus();
						break;
					case subgrupo:
						menssageError = 'Informe o SubGrupo e Exame';
						$('#cmbProcRealizado').focus();
						break;
					case exame:
						menssageError = 'Informe o Exame';
						$('#cmbProcRealizado').focus();
						break;
					case justificativa:
						menssageError = 'Informe a Justificativa';
						$('#cmbProcRealizado').focus();
						break;
					default:
						menssageError = '';
						break;
				}

				if (menssageError) {
					alerta('Campo Obrigatório!', menssageError, 'error')
					return
				}

				//chamar requisicao
				$.ajax({
					type: 'POST',
					url: 'filtraAtendimentoSolicitacaoExame.php',
					dataType: 'json',
					data: {
						'tipoRequest': 'ADICIONAREXAME',
						'grupo' : grupo,
						'subgrupo' : subgrupo,
						'exame' : exame,
						'justificativa' : justificativa,
						'urgente' : urgente,
						'profissional' : profissional,
						'iAtendimentoId' : iAtendimentoId
					},
					success: function(response) {
						if (response.status == 'success') {
							checkExames()
							alerta(response.titulo, response.menssagem, response.status);
							$('#grupo').val('').change();
							$('#subgrupo').val('').change();
							$('#exame').val('').change();
							$('#justificativa').val('');

						} else {
							alerta(response.titulo, response.menssagem, response.status);
						}
					},
					error: function(response) {
						alerta(response.titulo, response.menssagem, response.status);
					}
				});

			});

			$(".caracteresjustificativa").text((500 - $("#justificativa").val().length) + ' restantes'); //restantes em motivo da consulta

		}); //document.ready

		async function filtrarSubGrupo() {

			let idGrupo = $('#grupo').val();				

			await $.ajax({
				type: 'POST',
				url: 'filtraAtendimentoSolicitacaoExame.php',
				dataType: 'json',
				data:{
					'tipoRequest': 'SUBGRUPOS',
					'idGrupo' : idGrupo						 
				},
				success: function(response) {
					$('#subgrupo').empty();
					$('#subgrupo').append(`<option value=''>Selecione</option>`)

					if (response.length !== 0) {
						Array.from(response).forEach(item => {
							let opt = `<option value="${item.id}">${item.nome}</option>`
							$('#subgrupo').append(opt)	
						})							
					}	
					$('#subgrupo').focus();			
				}	
			})
			
		}

		async function filtrarProcedimento() {

			let idSubGrupo = $('#subgrupo').val();	

			await $.ajax({
				type: 'POST',
				url: 'filtraAtendimentoSolicitacaoExame.php',
				dataType: 'json',
				data:{
					'tipoRequest': 'PROCEDIMENTOS',
					'idSubGrupo' : idSubGrupo						 
				},
				success: function(response) {
					$('#exame').empty();
					$('#exame').append(`<option value=''>Selecione</option>`)

					if (response.length !== 0) {
						Array.from(response).forEach(item => {
							let opt = `<option value="${item.id}">${item.nome}</option>`
							$('#exame').append(opt)	
						})							
					}	
					$('#exame').focus();			
				}	
			})
			
		}	

		function contarCaracteres(params) {

			var limite = params.maxLength;
			var informativo = " restantes.";
			var caracteresDigitados = params.value.length;
			var caracteresRestantes = limite - caracteresDigitados;

			if (caracteresRestantes <= 0) {
				var texto = $(`textarea[name=${params.id}]`).val();
				$(`textarea[name=${params.id}]`).val(texto.substr(0, limite));
				$(".caracteres" + params.id).text("0 " + informativo);
			} else {
				$(".caracteres" + params.id).text(caracteresRestantes + " " + informativo);
			}
		}

		function checkExames() {

			let iAtendimentoId = $('#iAtendimentoId').val();

			$.ajax({
			type: 'POST',
				url: 'filtraAtendimentoSolicitacaoExame.php',
				dataType: 'json',
				data: {
					'tipoRequest': 'CHECKEXAMES',
					'iAtendimentoId': iAtendimentoId
				},
				success: async function(response) {
					let table = $('#tblSolicitacaoExame').DataTable().clear().draw()

					table = $('#tblSolicitacaoExame').DataTable()
					let rowNode	

					response.forEach(item => {
						rowNode = table.row.add(item.data).draw()
					})

				}
			});

		}

		async function copiarExame(params) {

			$('#grupo').val(params.AtSExGrupo).change();
			
			await filtrarSubGrupo();

			$('#subgrupo').val(params.AtSExSubGrupo).change();

			await filtrarProcedimento();

			$('#exame').val(params.AtSExExame).change();
			
			$('#justificativa').val(params.AtSExJustificativa);
			
		}

		function excluirExame(id){
			confirmaExclusaoAjax('filtraAtendimentoSolicitacaoExame.php', 'Excluir Solicitação de Exame?', 'EXCLUIREXAME', id, checkExames);
		}

		function getCmbs(){
			// vai preencher GRUPOS
			$.ajax({
				type: 'POST',
				url: 'filtraAtendimentoSolicitacaoExame.php',
				dataType: 'json',
				data:{
					'tipoRequest': 'GRUPOS'
				},
				success: function(response) {
					$('#grupo').empty();
					$('#grupo').append(`<option value=''>Selecione</option>`)
				
					response.forEach(item => {
						let opt = `<option value="${item.id}">${item.nome}</option>`
						$('#grupo').append(opt)
					})
					
				}
			});
		
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
						<form name="formAtendimentoSolicitacaoExame" id="formAtendimentoSolicitacaoExame" method="post" class="form-validate-jquery">
							<?php
								echo "<input type='hidden' id='iAtendimentoId' name='iAtendimentoId' value='$iAtendimentoId' />";
							?>
							<div class="card">
								<div class="card-header header-elements-inline">
									<h3 class="card-title"><b>SOLICITAÇÃO DE EXAMES</b></h3>
								</div>
							</div>

							<div> <?php include ('atendimentoDadosPaciente.php'); ?> </div>

							<div class="card">

								<div class="card-body">	
									<div class="row"  style="margin-top:25px;">									
										<form id="formSolicitacaoExames" name="formTabelaformSolicitacaoExames" method="post" class="form-validate-jquery">
											<div class="col-lg-12 row">
												<!-- titulos -->
												<div class="col-lg-3">
													<label>Grupo <span class="text-danger">*</span></label>
												</div>
												<div class="col-lg-4">
													<label>SubGrupo <span class="text-danger">*</span></label>
												</div>
												<div class="col-lg-5">
													<label>Exame <span class="text-danger">*</span></label>
												</div>
												
												<!-- campos -->										
												<div class="col-lg-3">
													<select id="grupo" name="grupo" class="select-search" onChange="filtrarSubGrupo()" required>
														<option value=''>Selecione</option>
													</select>
												</div>
												<div class="col-lg-4">
													<select id="subgrupo" name="subgrupo" class="select-search" onChange="filtrarProcedimento()" required>
														<option value=''>Selecione</option>
													</select>											
												</div>
												<div class="col-lg-5">
													<select id="exame" name="exame" class="select-search" required>
														<option value=''>Selecione</option>
													</select>
												</div>											
										
											</div>
										</form>
									</div>

									<div class="row" style="margin-top: 20px">
										<div class="col-lg-12">
											<div class="form-group">
												<label for="justificativa">Justificativa <span class="text-danger">*</span></label>
												<textarea rows="5" cols="5" maxLength="500" id="justificativa" name="justificativa" onInput="contarCaracteres(this);" class="form-control" placeholder="Corpo da solicitação de exame (informe aqui o texto que você queira que apareça na solicitacao de exame)" ></textarea>
												<small class="text-muted form-text">Max. 500 caracteres - <span class="caracteresjustificativa"></span></small>
											</div>
										</div>
									</div>

									<div class="row">
										<div class="col-lg-12">
											<div class="form-group form-inline">
												<label class="d-block font-weight-semibold" style=" margin-right: 20px" >Urgente:</label>
												<div class="form-check form-check-inline">
													<label class="form-check-label">
														<input type="radio" class="form-check-input" id="urgente" name="urgente" value="1" checked>
														Sim
													</label>
												</div>

												<div class="form-check form-check-inline">
													<label class="form-check-label">
														<input type="radio" class="form-check-input" id="urgente" name="urgente" value="0">
														Não
													</label>
												</div>
											</div>
										</div>
									</div>

									<div class="row">
										<div class="col-lg-12">
											<div class="form-group" style="padding-top:15px;">
												<button class="btn btn-lg btn-success" id="adicionarExame" data-tipo="ADICIONAREXAME" >Adicionar</button>
											</div>
										</div>
									</div> 

									<div class="card-header header-elements-inline" style="margin-left: -20px">
										<h4 class="card-title font-weight-bold">Exames Solicitados</h4>
									</div>

									<div class="row">
										<div class="col-lg-12">
											<table class="table" id="tblSolicitacaoExame">
												<thead>
													<tr class="bg-slate">
														<th class="text-left">Item</th>
														<th class="text-left">Data/Hora</th>
														<th class="text-left">Grupo</th>
														<th class="text-left">SubGrupo</th>
														<th class="text-left">Código</th>
														<th class="text-left">Exame</th>
														<th class="text-left">Ações</th>
													</tr>
												</thead>
												<tbody id="dataServico">
												</tbody>
											</table>
										</div>		
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
