<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Internação Entrada';

include('global_assets/php/conexao.php');

$iAtendimentoId = isset($_POST['iAtendimentoId'])?$_POST['iAtendimentoId']:null;

if (isset($_SESSION['iAtendimentoId']) && $iAtendimentoId == null) {
	$iAtendimentoId = $_SESSION['iAtendimentoId'];
}
$_SESSION['iAtendimentoId'] = null;
$iUnidade = $_SESSION['UnidadeId'];

$uTipoAtendimento = $_SESSION['UltimaPagina'];
if(!$iAtendimentoId){

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

$_SESSION['atendimentoTabelaServicos'] = [];
$_SESSION['atendimentoTabelaProdutos'] = [];

//Essa consulta é para verificar  o profissional
$sql = "SELECT UsuarId, A.ProfiUsuario, A.ProfiId as ProfissionalId, A.ProfiNome as ProfissionalNome, PrConNome, B.ProfiCbo as ProfissaoCbo, ProfiNumConselho
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
			   AtTriAltura, AtTriImc, AtTriPressaoSistolica, AtTriPressaoDiatolica, AtTriFreqCardiaca, AtTriTempAXI, AtClRCor,
               TpIntNome, TpIntId, EsLeiNome, EsLeiId, AlaNome, AlaId, QuartNome, QuartId, LeitoNome, LeitoId,SituaChave
		FROM Atendimento
		JOIN Cliente ON ClienId = AtendCliente
		LEFT JOIN ClienteResponsavel on ClResCliente = AtendCliente
		LEFT JOIN AtendimentoModalidade ON AtModId = AtendModalidade
		LEFT JOIN AtendimentoTriagem ON AtTriAtendimento = AtendId
		LEFT JOIN AtendimentoClassificacaoRisco ON AtClRId = AtendClassificacaoRisco
        LEFT JOIN AtendimentoXLeito ON AtXLeAtendimento = AtendId
        LEFT JOIN EspecialidadeLeito ON AtXLeEspecialidadeLeito = EsLeiId
        LEFT JOIN Leito ON AtXLeLeito = LeitoId
        LEFT JOIN VincularLeitoXLeito ON VLXLeLeito = LeitoId
        LEFT JOIN VincularLeito ON VnLeiId = VLXLeVinculaLeito
        LEFT JOIN Quarto ON QuartId = VnLeiQuarto
        LEFT JOIN TipoInternacao ON TpIntId = VnLeiTipoInternacao
        LEFT JOIN Ala ON AlaId = VnLeiAla
		JOIN Situacao ON SituaId = AtendSituacao
	    WHERE  AtendId = $iAtendimentoId 
		ORDER BY AtendNumRegistro ASC";
$result = $conn->query($sql);
$row = $result->fetch(PDO::FETCH_ASSOC);

$iAtendimentoId = $row['AtendId'];
$iClienteId = $row['ClienId'];

$iAtendimentoHistoricoId = $row['AtendId'];

//Essa consulta é para preencher o sexo
if ($row['ClienSexo'] == 'F'){
    $sexo = 'Feminino';
} else{
    $sexo = 'Masculino';
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Internação Entrada</title>

	<?php include_once("head.php"); ?>
	
	<link href="global_assets/css/icons/icomoon/styles.css" rel="stylesheet" type="text/css">
	<link href="global_assets/css/lamparinas/components.min.css" rel="stylesheet" type="text/css">

	<script src="global_assets/js/main/bootstrap.bundle.min.js"></script>
	<script src="global_assets/js/plugins/loaders/blockui.min.js"></script>
	<script src="global_assets/js/plugins/ui/ripple.min.js"></script>

	<script src="global_assets/js/plugins/forms/wizards/steps.min.js"></script>
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
	<script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>
	<script src="global_assets/js/plugins/forms/inputs/inputmask.js"></script>
	<script src="global_assets/js/demo_pages/form_select2.js"></script>
	<script src="global_assets/js/demo_pages/form_layouts.js"></script>	
    <script src="global_assets/js/plugins/tables/datatables/datatables.min.js"></script>
    <script src="global_assets/js/plugins/tables/datatables/extensions/responsive.min.js"></script>
    <script src="global_assets/js/demo_pages/datatables_responsive.js"></script>
    <script src="global_assets/js/demo_pages/datatables_sorting.js"></script>
	<script src="global_assets/js/plugins/editors/summernote/summernote.min.js"></script>
	
	<script src="global_assets/js/plugins/ui/moment/moment.min.js"></script>
	<script src="global_assets/js/plugins/pickers/daterangepicker.js"></script>
	<script src="global_assets/js/plugins/pickers/anytime.min.js"></script>
	<script src="global_assets/js/plugins/pickers/pickadate/picker.js"></script>
	<script src="global_assets/js/plugins/pickers/pickadate/picker.date.js"></script>
	<script src="global_assets/js/plugins/pickers/pickadate/picker.time.js"></script>
	<script src="global_assets/js/plugins/pickers/pickadate/legacy.js"></script>
	<script src="global_assets/js/plugins/notifications/jgrowl.min.js"></script>

	<!-- Plugin para corrigir a ordenação por data. Caso a URL dê problema algum dia, salvei esses 2 arquivos na pasta global_assets/js/lamparinas -->
	<script type="text/javascript" language="javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.8.4/moment.min.js"></script>
	<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/plug-ins/1.10.10/sorting/datetime-moment.js"></script>	

	<!-- Modal -->
	<script src="global_assets/js/plugins/notifications/bootbox.min.js"></script>
    
    <!-- Validação -->
	<script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
	<script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
	<script src="global_assets/js/demo_pages/form_validation.js"></script>

	<?php
		// essa parte do código transforma uma variáve php em Js para ser utilizado 
		echo '<script>
				var atendimento = '.json_encode($row).';
			</script>';
	?>
	
	<script type="text/javascript">
		$(document).ready(function() {

			getEvolucaoDiaria()		

            /* Início: Tabela Personalizada */
			$('#tblEvolucaoDiaria').DataTable( {
				"order": [[ 0, "asc" ]],
			    autoWidth: false,
				responsive: true,
				searching: false,
				ordering: false, 
				paging: false,
			    columnDefs: [
				{ 
					orderable: true, 
					width: "5%", 
					targets: [0]
				},
				{ 
					orderable: true,   
					width: "10%", 
					targets: [1]
				},
				{ 
					orderable: true,
					width: "30%", 
					targets: [2]
				},				
				{ 
					orderable: true,  
					width: "10%", 
					targets: [3]
				}],
				dom: '<"datatable-header"fl><"datatable-scroll-wrap"t><"datatable-footer">',
				language: {
					search: '<span>Filtro:</span> _INPUT_',
					searchPlaceholder: 'filtra qualquer coluna...',
					lengthMenu: '<span>Mostrar:</span> _MENU_',
					paginate: { 'first': 'Primeira', 'last': 'Última', 'next': $('html').attr('dir') == 'rtl' ? '&larr;' : '&rarr;', 'previous': $('html').attr('dir') == 'rtl' ? '&rarr;' : '&larr;' }
				}
                
			});

			$('#incluirEvolucaoDiaria').on('click', function (e) {
				e.preventDefault();

				let msg = ''
				let evolucaoDiaria = $('#evolucaoDiaria').val()

				switch(msg){
					case evolucaoDiaria: msg = 'Informe o texto da Evolução!';$('#evolucaoDiaria').focus();break
				}
				if(msg){
					alerta('Campo Obrigatório!', msg, 'error')
					return
				}

				$.ajax({
					type: 'POST',
					url: 'filtraAtendimentoObservacaoHospitalar.php',
					dataType: 'json',

					data: {
						'tipoRequest': 'INCLUIREVOLUCAODIARIA',
						'iAtendimentoId' : <?php echo $iAtendimentoId; ?>,
						'evolucaoDiaria' : evolucaoDiaria						
					},
					success: function(response) {
						if(response.status == 'success'){
							getEvolucaoDiaria()
							alerta(response.titulo, response.menssagem, response.status)
						}else{
							alerta(response.titulo, response.menssagem, response.status)
						}
					}
				});

			})
	
			$('#salvarEdEvolucao').on('click', function (e) {

				let msg = ''
				let idEvolucao = $('#idEvolucao').val()
				let evolucaoDiaria = $('#evolucaoDiaria').val()

				switch(msg){
					case evolucaoDiaria: msg = 'Informe o texto da Evolução!';$('#evolucaoDiaria').focus();break
				}
				if(msg){
					alerta('Campo Obrigatório!', msg, 'error')
					return
				}

				$.ajax({
					type: 'POST',
					url: 'filtraAtendimentoObservacaoHospitalar.php',
					dataType: 'json',

					data: {
						'tipoRequest': 'EDITAREVOLUCAO',
						'idEvolucao' : idEvolucao,
						'evolucaoDiaria' : evolucaoDiaria						
					},
					success: function(response) {
						if(response.status == 'success'){
							alerta(response.titulo, response.menssagem, response.status)
							$("#incluirEvolucaoDiaria").css('display', 'block');
							$("#salvarEdEvolucao").css('display', 'none');
							zerarEvolucao()
							getEvolucaoDiaria()

						}else{
							alerta(response.titulo, response.menssagem, response.status)
						}
					}
				});

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
				$(".caracteres" + params.id).text("- 0 " + informativo);
			} else {
				$(".caracteres" + params.id).text( '- ' + caracteresRestantes + " " + informativo);
			}
		}

		function copiarEvolucao(evolucao) {
			$('#evolucaoDiaria').val(evolucao);
		}

		function getEvolucaoDiaria() {

			$.ajax({
				type: 'POST',
				url: 'filtraAtendimentoObservacaoHospitalar.php',
				dataType: 'json',
				data:{
					'tipoRequest': 'EVOLUCAODIARIA',
					'id' : <?php echo $iAtendimentoId; ?>
				},
				success: function(response) {

					$('#dataEvolucaoDiaria').html('');
					let HTML = ''
					
					response.forEach(item => {

						let copiar = `<a class='list-icons-item mr-2 ' style='color: black; cursor:pointer' onclick='copiarEvolucao (\"${item.evolucaoCompl}\")'><i class='icon-clipboard2' title='Copiar Evolução'></i></a>`;
						let editar = `<a class='list-icons-item mr-2 ' style='color: black; cursor:pointer'  onclick='editarEvolucao(\"${item.id}\")' class='list-icons-item' ><i class='icon-pencil7' title='Editar Evolução'></i></a>`;
						let exc = `<a style='color: black; cursor:pointer' onclick='excluirEvolucao(\"${item.id}\")' class='list-icons-item'><i class='icon-bin' title='Excluir Evolução'></i></a>`;

						let acoes = `<div class='list-icons'>
									${copiar}
									${editar}
									${exc}
								</div>`;

						HTML += `
						<tr class='evolucaoItem'>
							<td class="text-left">${item.item}</td>
							<td class="text-left">${item.dataHora}</td>
							<td class="text-left" title="${item.evolucaoCompl}">${item.evolucao}</td>
							<td class="text-center">${acoes}</td>
						</tr>`

					})
					$('#dataEvolucaoDiaria').html(HTML).show();
				}
			});	

		}

		function editarEvolucao(id) {

			$.ajax({
				type: 'POST',
				url: 'filtraAtendimentoObservacaoHospitalar.php',
				dataType: 'json',
				data:{
					'tipoRequest': 'GETEVOLUCAO',
					'id' : id
				},
				success: function(response) {
					
					$('#idEvolucao').val(response.AtEDiId)
					$('#evolucaoDiaria').val(response.AtEDiEvolucaoDiaria)

					$("#incluirEvolucaoDiaria").css('display', 'none');
					$("#salvarEdEvolucao").css('display', 'block');

					$('#evolucaoDiaria').focus()				
				}
			});
			
		}

		function excluirEvolucao(id) {
			confirmaExclusaoAjax('filtraAtendimentoObservacaoHospitalar.php', 'Excluir Evolução?', 'DELETEEVOLUCAO', id, getEvolucaoDiaria)
		}

		function zerarEvolucao() {

			$('#idEvolucao').val("")
			$('#evolucaoDiaria').val("")
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
						
						<?php
							echo "<input type='hidden' id='iAtendimentoId' name='iAtendimentoId' value='$iAtendimentoId' />";
						?>
						<div class="card">
							<div class="card-header header-elements-inline">
								<h3 class="card-title"><b>INTERNAÇÃO HOSPITALAR</b></h3>
							</div>
						</div>

						<div> <?php include ('atendimentoDadosPacienteHospitalar.php'); 
							include ('atendimentoDadosSinaisVitais.php');?> 
						</div>

						<div class="card">
							<div class="card-header header-elements-inline">
								<div class="col-lg-11">	
                                    <button type="button" class=" btn btn-md btn-outline-secondary mr-2 itemLink" data-tipo='internacaoHospitalarEntrada' style="margin-left: -10px;" >Entrada do Paciente</button>
									<button type="button" class=" btn btn-md btn-outline-secondary mr-2 itemLink" data-tipo='internacaoHospitalarPrescricaoMedica' >Prescrição Médica</button>
									<button type="button" class=" btn btn-md btn-outline-secondary mr-2 active " >Evolução Médica</button>
									<button type="button" class=" btn btn-md btn-outline-secondary mr-2 itemLink" data-tipo='internacaoHospitalarSolicitacaoInterconsulta' >Solicitação Interconsulta</button>
									<button type="button" class=" btn btn-md btn-outline-secondary mr-2 itemLink" data-tipo='evolucaoEnfermagem' >Evolução Enfermagem</button>
								</div>
							</div>							
						</div>

						<div class="box-evolucao" >
							<?php include_once("boxEvolucaoObservacao.php"); ?>
						</div>  

                        <div class="card " style="padding: 15px">
                            <div class="col-md-12">
                                <div class="row">                                    
                                    <div class="col-md-10" style="text-align: left;">
                                        <button type="button" class="btn btn-lg btn-secondary mr-1">Imprimir</button>
                                    </div>
                                    
                                </div>
                            </div> 
                        </div>
							
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
