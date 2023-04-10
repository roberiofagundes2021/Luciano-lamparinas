<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Evolução de Enfermagem';

include('global_assets/php/conexao.php');

$iAtendimentoId = isset($_POST['iAtendimentoId'])?$_POST['iAtendimentoId']:null;

if (isset($_SESSION['iAtendimentoId']) && $iAtendimentoId == null) {
	$iAtendimentoId = $_SESSION['iAtendimentoId'];
}
$_SESSION['iAtendimentoId'] = null;

if(!$iAtendimentoId){
	irpara("atendimentoHospitalarListagem.php");	
}

// essas variáveis são utilizadas para colocar o nome da classificação do atendimento no menu secundario

$ClaChave = isset($_POST['ClaChave'])?$_POST['ClaChave']:'';
$ClaNome = isset($_POST['ClaNome'])?$_POST['ClaNome']:'';


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

$iAtendimentoCliente = $row['AtendCliente'] ;
$iAtendimentoId = $row['AtendId'];

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
	<title>Lamparinas | Evolução Enfermagem</title>

	<?php include_once("head.php"); ?>

    <!-- Theme JS files -->
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
	<script src="global_assets/js/demo_pages/form_select2.js"></script>
	<script src="global_assets/js/plugins/forms/selects/bootstrap_multiselect.js"></script>
	<script src="global_assets/js/demo_pages/form_multiselect.js"></script>

	<script src="global_assets/js/demo_pages/form_layouts.js"></script>
	<script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>

	<script src="global_assets/js/plugins/forms/inputs/inputmask.js"></script>

    <script src="global_assets/js/plugins/tables/datatables/datatables.min.js"></script>
    <script src="global_assets/js/plugins/tables/datatables/extensions/responsive.min.js"></script>
    <script src="global_assets/js/demo_pages/datatables_responsive.js"></script>
    <script src="global_assets/js/demo_pages/datatables_sorting.js"></script>
	<!-- /theme JS files -->	

	<!-- Validação -->
	<script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
	<script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
	<script src="global_assets/js/demo_pages/form_validation.js"></script>
	
	<script type="text/javascript">

		$(document).ready(function() {	

            getEvolucoes()

            /* Início: Tabela Personalizada */
			$('#tblEvolucao').DataTable( {
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
					width: "25%", 
					targets: [2]
				},				
				{ 
					orderable: true,  
					width: "25%", 
					targets: [3]
				},				
				{ 
					orderable: true,  
					width: "15%", 
					targets: [4]
				},				
				{ 
					orderable: true,  
					width: "10%", 
					targets: [5]
				}],
				dom: '<"datatable-header"fl><"datatable-scroll-wrap"t><"datatable-footer">',
				language: {
					search: '<span>Filtro:</span> _INPUT_',
					searchPlaceholder: 'filtra qualquer coluna...',
					lengthMenu: '<span>Mostrar:</span> _MENU_',
					paginate: { 'first': 'Primeira', 'last': 'Última', 'next': $('html').attr('dir') == 'rtl' ? '&larr;' : '&rarr;', 'previous': $('html').attr('dir') == 'rtl' ? '&rarr;' : '&larr;' }
				}
                
			});

            $('#incluirEvolucaoEnfermagem').on('click', function (e) {

                e.preventDefault();

                let msg = ''
                let justificativaEvolucao = $('#justificativaEvolucao').val()
                let evolucaoEnfermagem = $('#evolucaoEnfermagem').val()

                let inputPrevisaoAlta = $('#inputPrevisaoAlta').val()
                let inputTipoInternacao = $('#inputTipoInternacao').val()
                let inputEspLeito = $('#inputEspLeito').val()
                let inputAla = $('#inputAla').val()
                let inputQuarto = $('#inputQuarto').val()
                let inputLeito = $('#inputLeito').val()

                let inputSistolica = $('#inputSistolica').val()
                let inputDiatolica = $('#inputDiatolica').val()
                let inputCardiaca = $('#inputCardiaca').val()
                let inputRespiratoria = $('#inputRespiratoria').val()
                let inputTemperatura = $('#inputTemperatura').val()
                let inputSPO = $('#inputSPO').val()
                let inputHGT = $('#inputHGT').val()
                let inputPeso = $('#inputPeso').val()

                let inputAlergia = $('#inputAlergia').val()
                let inputDiabetes = $('#inputDiabetes').val()
                let inputHipertensao = $('#inputHipertensao').val()
                let inputNeoplasia = $('#inputNeoplasia').val()
                let inputUsoMedicamento = $('#inputUsoMedicamento').val()
                let inputAlergiaDescricao = $('#inputAlergiaDescricao').val()
                let inputDiabetesDescricao = $('#inputDiabetesDescricao').val()
                let inputHipertensaoDescricao = $('#inputHipertensaoDescricao').val()
                let inputNeoplasiaDescricao = $('#inputNeoplasiaDescricao').val()
                let inputUsoMedicamentoDescricao = $('#inputUsoMedicamentoDescricao').val()


                switch(msg){
                    case evolucaoEnfermagem: msg = 'Informe o texto da Evolucao!';$('#evolucaoEnfermagem').focus();break
                }
                if(msg){
                    alerta('Campo Obrigatório!', msg, 'error')
                    return
                }

                $.ajax({
                    type: 'POST',
                    url: 'filtraAtendimento.php',
                    dataType: 'json',

                    data: {
                        'tipoRequest': 'INCLUIREVOLUCAOENFERMAGEM',
                        'tipo' : 'INSERT',
                        'iAtendimentoId' : <?php echo $iAtendimentoId; ?>,
                        'justificativaEvolucao' : justificativaEvolucao,				
                        'evolucaoEnfermagem' : evolucaoEnfermagem,	

                        'inputPrevisaoAlta' : inputPrevisaoAlta,
                        'inputTipoInternacao' : inputTipoInternacao,
                        'inputEspLeito' : inputEspLeito,
                        'inputAla' : inputAla,
                        'inputQuarto' : inputQuarto,
                        'inputLeito' : inputLeito,	

                        'inputSistolica' : inputSistolica,		
                        'inputDiatolica' : inputDiatolica,		
                        'inputCardiaca' : inputCardiaca,		
                        'inputRespiratoria' : inputRespiratoria,		
                        'inputTemperatura' : inputTemperatura,		
                        'inputSPO' : inputSPO,		
                        'inputHGT' : inputHGT,
                        'inputPeso' : inputPeso,
                        'inputAlergia' : inputAlergia,
						'inputDiabetes' : inputDiabetes,
						'inputHipertensao' : inputHipertensao,
						'inputNeoplasia' : inputNeoplasia,
						'inputUsoMedicamento' : inputUsoMedicamento,
						'inputAlergiaDescricao' : inputAlergiaDescricao,
						'inputDiabetesDescricao' : inputDiabetesDescricao,
						'inputHipertensaoDescricao' : inputHipertensaoDescricao,
						'inputNeoplasiaDescricao' : inputNeoplasiaDescricao,
						'inputUsoMedicamentoDescricao' : inputUsoMedicamentoDescricao
                    },
                    success: function(response) {
                        if(response.status == 'success'){
                            alerta(response.titulo, response.menssagem, response.status)
                            getEvolucoes()
                            zerarEvolucao()
                        }else{
                            alerta(response.titulo, response.menssagem, response.status)
                        }
                    }
                });

            })

            $('#salvarEdEvolucao').on('click', function (e) {

                e.preventDefault();

                let msg = ''
                let idEvolucao = $('#idEvolucao').val()
                let justificativaEvolucao = $('#justificativaEvolucao').val()
                let evolucaoEnfermagem = $('#evolucaoEnfermagem').val()

                let inputPrevisaoAlta = $('#inputPrevisaoAlta').val()
                let inputTipoInternacao = $('#inputTipoInternacao').val()
                let inputEspLeito = $('#inputEspLeito').val()
                let inputAla = $('#inputAla').val()
                let inputQuarto = $('#inputQuarto').val()
                let inputLeito = $('#inputLeito').val()

                let inputSistolica = $('#inputSistolica').val()
                let inputDiatolica = $('#inputDiatolica').val()
                let inputCardiaca = $('#inputCardiaca').val()
                let inputRespiratoria = $('#inputRespiratoria').val()
                let inputTemperatura = $('#inputTemperatura').val()
                let inputSPO = $('#inputSPO').val()
                let inputHGT = $('#inputHGT').val()

                let inputAlergia = $('#inputAlergia').val()
                let inputDiabetes = $('#inputDiabetes').val()
                let inputHipertensao = $('#inputHipertensao').val()
                let inputNeoplasia = $('#inputNeoplasia').val()
                let inputUsoMedicamento = $('#inputUsoMedicamento').val()
                let inputAlergiaDescricao = $('#inputAlergiaDescricao').val()
                let inputDiabetesDescricao = $('#inputDiabetesDescricao').val()
                let inputHipertensaoDescricao = $('#inputHipertensaoDescricao').val()
                let inputNeoplasiaDescricao = $('#inputNeoplasiaDescricao').val()
                let inputUsoMedicamentoDescricao = $('#inputUsoMedicamentoDescricao').val()

                switch(msg){
                    case evolucaoEnfermagem: msg = 'Informe o texto da Evolução!';$('#evolucaoEnfermagem').focus();break
                }
                if(msg){
                    alerta('Campo Obrigatório!', msg, 'error')
                    return
                }

                $.ajax({
                    type: 'POST',
                    url: 'filtraAtendimento.php',
                    dataType: 'json',

                    data: {
                        'tipoRequest': 'INCLUIREVOLUCAOENFERMAGEM',
                        'tipo' : 'UPDATE',
                        'idEvolucao' : idEvolucao,
                        'justificativaEvolucao' : justificativaEvolucao,			
                        'evolucaoEnfermagem' : evolucaoEnfermagem,	
                        
                        'inputPrevisaoAlta' : inputPrevisaoAlta,
                        'inputTipoInternacao' : inputTipoInternacao,
                        'inputEspLeito' : inputEspLeito,
                        'inputAla' : inputAla,
                        'inputQuarto' : inputQuarto,
                        'inputLeito' : inputLeito,

                        'inputSistolica' : inputSistolica,		
                        'inputDiatolica' : inputDiatolica,		
                        'inputCardiaca' : inputCardiaca,		
                        'inputRespiratoria' : inputRespiratoria,		
                        'inputTemperatura' : inputTemperatura,		
                        'inputSPO' : inputSPO,		
                        'inputHGT' : inputHGT,
                        'inputAlergia' : inputAlergia,
						'inputDiabetes' : inputDiabetes,
						'inputHipertensao' : inputHipertensao,
						'inputNeoplasia' : inputNeoplasia,
						'inputUsoMedicamento' : inputUsoMedicamento,
						'inputAlergiaDescricao' : inputAlergiaDescricao,
						'inputDiabetesDescricao' : inputDiabetesDescricao,
						'inputHipertensaoDescricao' : inputHipertensaoDescricao,
						'inputNeoplasiaDescricao' : inputNeoplasiaDescricao,
						'inputUsoMedicamentoDescricao' : inputUsoMedicamentoDescricao					
                    },
                    success: function(response) {
                        if(response.status == 'success'){
                            alerta(response.titulo, response.menssagem, response.status)
                            $("#incluirEvolucaoEnfermagem").css('display', 'block');
                            $("#salvarEdEvolucao").css('display', 'none');
                            zerarEvolucao()
                            getEvolucoes()

                        }else{
                            alerta(response.titulo, response.menssagem, response.status)
                        }
                    }
                });

            })

            $('.salvarEvolucaoEnfermagem').on('click', function(e){

                e.preventDefault();

                $.ajax({
                    type: 'POST',
                    url: 'filtraAtendimento.php',
                    dataType: 'json',
                    data: {
                        'tipoRequest': 'SALVAREVOLUCAOENFERMAGEM',
                        'iAtendimentoId' : <?php echo $iAtendimentoId; ?>,					
                    },
                    success: function(response) {
                        if(response.status == 'success'){
                            alerta(response.titulo, response.menssagem, response.status)
                            getEvolucoes()
                        }else{
                            alerta(response.titulo, response.menssagem, response.status)
                        }						
                    }
                });

            })
     
			$('#enviar').on('click', function(e){
				
				e.preventDefault();
		
				$( "#formAtendimentoAmbulatorial" ).submit();
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
        


        function getEvolucoes() {

            $.ajax({
                type: 'POST',
                url: 'filtraAtendimento.php',
                dataType: 'json',
                data:{
                    'tipoRequest': 'GETEVOLUCOESENFERMAGEM',
                    'id' : <?php echo $iAtendimentoId; ?>
                },
                success: function(response) {

                    $('#dataEvolucao').html('');
                    let HTML = ''
                    
                    response.forEach(item => {

                        let situaChave = $("#atendimentoSituaChave").val();
                        let copiar = `<a class='list-icons-item mr-2 ' style='color: black; cursor:pointer' onclick='copiarEvolucao(\"${item.justificativaCompleta}\", \"${item.evolucaoCompleta}\" )'><i class='icon-files-empty' title='Copiar Evolução'></i></a>`; 
                        let anexos = `<a class='list-icons-item mr-2 ' style='color: black; cursor:pointer'  onclick='anexosEvolucao(\"${item.id}\")' class='list-icons-item' ><i class='icon-attachment' title='Anexos da Evolução'></i></a>`;
                        let editar = `<a class='list-icons-item mr-2 ' style='color: black; cursor:pointer'  onclick='editarEvolucao(\"${item.id}\")' class='list-icons-item' ><i class='icon-pencil7' title='Editar Evolução'></i></a>`;
                        let exc = `<a style='color: black; cursor:pointer' onclick='excluirEvolucao(\"${item.id}\")' class='list-icons-item'><i class='icon-bin' title='Excluir Evolução'></i></a>`;
                        let acoes = ``;

                        if (item.editavel == 1) {

                            if (situaChave != 'ATENDIDO'){
								acoes = `<div class='list-icons'>
                                    ${copiar}
                                    ${anexos}
                                    ${editar}
                                    ${exc}
								</div>`;
							} else{
								acoes = `<div class='list-icons'>
                                    ${anexos}
                                        
								</div>`;
							}

                        } else {

                            if (situaChave != 'ATENDIDO'){
								acoes = `<div class='list-icons'>
                                    ${copiar}
                                    ${anexos}
								</div>`;
							} else{
								acoes = `<div class='list-icons'>
                                    ${anexos}
                                        
								</div>`;
							}
                            	
                        }
                        
                        HTML += `
                        <tr class='evolucaoItem'>
                            <td class="text-left">${item.item}</td>
                            <td class="text-left">${item.dataHora}</td>
                            <td class="text-left" title="${item.justificativaCompleta}">${item.justificativa}</td>
                            <td class="text-left" title="${item.evolucaoCompleta}">${item.evolucao}</td>
                            <td class="text-left" >${item.profissionalCbo}</td>
                            <td class="text-center">${acoes}</td>
                        </tr>`

                    })
                    $('#dataEvolucao').html(HTML).show();
                }
            });	

        }

        function editarEvolucao(id) {

            $.ajax({
                type: 'POST',
                url: 'filtraAtendimento.php',
                dataType: 'json',
                data:{
                    'tipoRequest': 'GETEVOLUCAOENFERMAGEM',
                    'id' : id
                },
                success: function(response) {
                    
                    $('#idEvolucao').val(response.EnEvoId)
                    $('#justificativaEvolucao').val(response.EnEvoJustificativaLancRetroativo)
                    $('#evolucaoEnfermagem').val(response.EnEvoEvolucao)

                    $('#inputSistolica').val(response.EnEvoPas)
                    $('#inputDiatolica').val(response.EnEvoPad)
                    $('#inputCardiaca').val(response.EnEvoFreqCardiaca)
                    $('#inputRespiratoria').val(response.EnEvoFreqRespiratoria)
                    $('#inputTemperatura').val(response.EnEvoTemperatura)
                    $('#inputSPO').val(response.EnEvoSPO)
                    $('#inputHGT').val(response.EnEvoHGT)

                    $("#incluirEvolucaoEnfermagem").css('display', 'none');
                    $("#salvarEdEvolucao").css('display', 'block');
                    $('#evolucaoEnfermagem').focus()		
                }
            });

        }

        function copiarEvolucao(justificativa,anotacao) {
            $('#justificativaEvolucao').val(justificativa)
			$('#evolucaoEnfermagem').val(anotacao)   
        }

        function excluirEvolucao(id) {
            confirmaExclusaoAjax('filtraAtendimento.php', 'Excluir Evolução?', 'DELETEEVOLUCAOENFERMAGEM', id, getEvolucoes)
        }

        function zerarEvolucao() {
            $('#justificativaEvolucao').val('')
            $('#evolucaoEnfermagem').val('')
        }

        function anexosEvolucao(idEvolucao) {
            $('#idEvolucaoAnexo').val(idEvolucao);
            $('#inputClienteEvolucao').val('<?php echo $row['ClienNome']; ?>');
            $('#inputAtendimento').val('<?php echo $row['AtendNumRegistro']; ?>');
            document.formAnexo.action = "evolucaoAnexo.php";
            document.formAnexo.submit();
        }

        function mudarTela(next){

            let HTML = '';

            if ( next === 'entradaPaciente' ) {
                HTML += `<input type='hidden' id='screen' name='screen' value='activeEntrada' />`; 
            } else if( next === 'prescricao' ) {
                HTML += `<input type='hidden' id='screen' name='screen' value='activePrescricao' />`;
            } else if( next === 'evolucaoMedica' ) {
                HTML += `<input type='hidden' id='screen' name='screen' value='activeEvolucaoMedica' />`;
            }
    
            $('#dadosPost').attr('action', 'atendimentoObservacaoHospitalar.php')
            $('#dadosPost').append(HTML)
            $('#dadosPost').attr('method', 'POST')
            $('#dadosPost').submit()
        }

	</script>

    <style>
	</style>

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
                                echo "<input type='hidden' id='atendimentoSituaChave' value='".$_SESSION['SituaChave']."' />";
                            ?>
							<div class="card">

                            <div class="col-md-12">
                                <div class="row">

                                    <div class="col-md-6" style="text-align: left;">
                                        <div class="card-header header-elements-inline">
                                            <h3 class="card-title"><b>EVOLUÇÃO DE ENFERMAGEM</b></h3>
                                        </div>            
                                    </div>

                                    <div class="col-md-6" style="text-align: right;">
                                        <div class="form-group" style="margin:20px;" >
                                            <?php 
                                                if (isset($_SESSION['SituaChave']) && $_SESSION['SituaChave'] != "ATENDIDO") {
                                                    echo "<button class='btn btn-lg btn-success mr-1 salvarEvolucaoEnfermagem' >Salvar</button>";
                                                }
                                            ?>
                                            <button type="button" class="btn btn-lg btn-secondary mr-1">Imprimir</button>
                                            <a href='atendimentoHospitalarListagem.php' class='btn btn-basic' role='button'>Voltar</a>
                                        </div>
                                    </div>

                                </div>
                            </div>
	
							</div>

							<div> 
                                <?php include ('atendimentoDadosPacienteHospitalar.php'); ?>
                                <?php include ('atendimentoDadosSinaisVitais.php'); ?>
                            </div>

                            <?php if ($_SESSION['UltimaPagina'] != 'HOSPITALAR') { ?>                          
                                <div class="card">
                                    <div class="card-header header-elements-inline">
                                        <div class="col-lg-11">	
                                        <button type="button" id="entradaPaciente-btn" class="btn-grid btn btn-lg btn-outline-secondary btn-lg mr-2" onclick="mudarTela('entradaPaciente')" style="margin-left: -10px;" >Entrada do Paciente</button>
                                            <button type="button" id="prescricao-btn" class="btn-grid btn btn-lg btn-outline-secondary btn-lg mr-2 " onclick="mudarTela('prescricao')"  >Prescrição Médica</button>
                                            <button type="button" id="evolucao-btn" class="btn-grid btn btn-lg btn-outline-secondary btn-lg mr-2 " onclick="mudarTela('evolucaoMedica')" >Evolução Médica</button>
                                            <button type="button" id="evolucao-btn" class="btn-grid btn btn-lg btn-outline-secondary btn-lg active " >Evolução de Enfermagem</button>
                                        </div>
                                    </div>								
                                </div>
                            <?php } else { ?>
                                <div class="card">
                                    <div class="card-header header-elements-inline">
                                        <div class="col-lg-11">	
                                            <button type="button" id="entradaPaciente-btn" class="btn-grid btn btn-md btn-outline-secondary mr-2 itemLink " data-tipo='internacaoHospitalarEntrada' style="margin-left: -10px;" >Entrada do Paciente</button>
                                            <button type="button" id="prescricao-btn" class="btn-grid btn btn-md btn-outline-secondary mr-2 itemLink " data-tipo='internacaoHospitalarPrescricaoMedica' >Prescrição Médica</button>
                                            <button type="button" id="prescricao-btn" class="btn-grid btn btn-md btn-outline-secondary mr-2 itemLink " data-tipo='internacaoHospitalarEvolucaoMedica' >Evolução Médica</button>
                                            <button type="button" id="prescricao-btn" class="btn-grid btn btn-md btn-outline-secondary mr-2 itemLink " data-tipo='internacaoHospitalarSolicitacaoInterconsulta' >Solicitação Interconsulta</button>
                                            <button type="button" id="prescricao-btn" class="btn-grid btn btn-md btn-outline-secondary mr-2 active " >Evolução Enfermagem</button>
                                        </div>
                                    </div>							
                                </div>
                            <?php } ?>
                        
                            <div class="card">

                                <div class="card-header header-elements-inline">
                                    <h3 class="card-title font-weight-bold">Evolução de Enfermagem</h3>
                                </div>

                                <div class="card-body">
                                    <?php 
                                        if (isset($_SESSION['SituaChave']) && $_SESSION['SituaChave'] != "ATENDIDO") {

                                            echo " <form id='formEvolucaoEnfermagem' name='formEvolucaoEnfermagem' method='post' class='form-validate-jquery'>
                                                <input type='hidden' name='idEvolucao' id='idEvolucao'>";
                                        
                                                echo " <div class='col-lg-12 mb-2 row' style='margin-left: -20px;'>
                                                    <!-- titulos -->
                                                    <div class='col-lg-2'>
                                                        <label>Data/Hora <span class='text-danger'>*</span></label>
                                                    </div>
                                                    <div class='col-lg-10'>
                                                        <label>Justificativa de Lançamento Retroativo</label>
                                                    </div>
                                                    
                                                    <!-- campos -->										
                                                    <div class='col-lg-2'>
                                                        <input type='datatime-local' class='form-control' name='dataHoraEvolucaoEnfermagem' id='dataHoraEvolucaoEnfermagem' value='";echo date('d/m/Y H:i'); echo "' readonly>	
                                                    </div>
                                                    <div class='col-lg-10'>
                                                        <input type='text' class='form-control' name='justificativaEvolucao' id='justificativaEvolucao' value=''>	
                                                    
                                                    </div>
                                                    
                                                </div>";
                                                
                                                echo "<div class='row'>
                                                    <div class='col-lg-12'>
                                                        <div class='form-group'>
                                                            <label for='evolucaoEnfermagem'>Evolução de Enfermagem <span class='text-danger'>*</span></label>
                                                            <textarea rows='5' cols='5' maxLength='500' id='evolucaoEnfermagem' name='evolucaoEnfermagem'  class='form-control' onInput='contarCaracteres(this);' placeholder='Corpo da evolução (informe aqui o texto que você queira que apareça na evolução)' ></textarea>
                                                            <small class='text-muted form-text'>Max. 500 caracteres <span class='caracteresevolucaoEnfermagem'></span></small>
                                                        </div>
                                                    </div>
                                                </div>"; 
                                            echo "</form>";

                                            echo "<div class='row'>
                                                <div class='col-lg-12'>
                                                    <div class='form-group row' style='padding-top:15px;'>
                                                        <button class='btn btn-lg btn-success mr-1' id='incluirEvolucaoEnfermagem' style='display: block;'  >Adicionar</button>
                                                        <button class='btn btn-lg btn-success mr-1' id='salvarEdEvolucao' style='display: none;'>Salvar Alterações</button>
                                                        <a href='atendimentoHospitalarListagem.php' class='btn btn-basic' role='button'>Voltar</a>
                                                        <!--<button class='btn btn-lg btn-success' type='button' onClick='abrirJanela()' style='display: block;'  >Testar Botao</button>-->
                                                    </div>
                                                </div>
                                            </div> ";
                                        }
                                    ?>	 
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <table class="table" id="tblEvolucao">
                                            <thead>
                                                <tr class="bg-slate">
                                                    <th class="text-left">Item</th>
                                                    <th class="text-left">Data/ Hora</th>
                                                    <th class="text-left">Justificativa de Lançamento Retroativo</th>
                                                    <th class="text-left">Evolução Diária</th>
                                                    <th class="text-left">Profissional - CBO</th>
                                                    <th class="text-center">Ações</th>
                                                </tr>
                                            </thead>
                                            <tbody id="dataEvolucao">
                                            </tbody>
                                        </table>
                                    </div>		
                                </div>							

                            </div>

                            <form method="post" name="formAnexo" target="print_popup">
                                <input type="hidden" id="idEvolucaoAnexo" name="idEvolucaoAnexo">
                                <input type="hidden" id="inputClienteEvolucao" name="inputClienteEvolucao">
                                <input type="hidden" id="inputAtendimento" name="inputAtendimento">
                            </form>                                            

                            <div class="card">

                                <div class=" card-body row">
                                    <div class="col-lg-12">
                                        <div class="form-group" style="margin-bottom:0px;">
                                            <?php 
                                                if (isset($_SESSION['SituaChave']) && $_SESSION['SituaChave'] != "ATENDIDO") {
                                                    echo "<button class='btn btn-lg btn-success mr-1 salvarEvolucaoEnfermagem' >Salvar</button>";
                                                }
                                            ?>
                                            <button type="button" class="btn btn-lg btn-secondary mr-1">Imprimir</button>
                                            <a href='atendimentoHospitalarListagem.php' class='btn btn-basic' role='button'>Voltar</a>
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