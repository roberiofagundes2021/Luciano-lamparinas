<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Anotação Técnico de Enfermagem';

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
	<title>Lamparinas | Anotação Técnico de Enfermagem</title>

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

            getAnotacoes()

            /* Início: Tabela Personalizada */
			$('#tblAnotacao').DataTable( {
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
					width: "30%", 
					targets: [3]
				},				
				{ 
					orderable: true,  
					width: "10%", 
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


            $('#incluirAnotacao').on('click', function (e) {

                e.preventDefault();

				let msg = ''
				let justificativaAnotacao = $('#justificativaAnotacao').val()
				let peso = $('#peso').val()
				let anotacao = $('#anotacao').val()

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
					case anotacao: msg = 'Informe o texto da Anotação!';$('#anotacao').focus();break
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
						'tipoRequest': 'INCLUIRANOTACAOTECENFERMAGEM',
                        'tipo' : 'INSERT',
						'iAtendimentoId' : <?php echo $iAtendimentoId; ?>,
						'profissional' : <?php echo $userId; ?> ,
						'inputPrevisaoAlta' : inputPrevisaoAlta,
                        'inputTipoInternacao' : inputTipoInternacao,
                        'inputEspLeito' : inputEspLeito,
                        'inputAla' : inputAla,
                        'inputQuarto' : inputQuarto,
                        'inputLeito' : inputLeito,
						'justificativaAnotacao' : justificativaAnotacao,		
						'peso' : peso,		
						'anotacao' : anotacao,		
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
							getAnotacoes()
                            getSinaisVitais()
							zerarAnotacao()
						}else{
							alerta(response.titulo, response.menssagem, response.status)
						}
					}
				});
                
            })

            $('#salvarEdAnotacao').on('click', function (e) {

                e.preventDefault();

                let msg = ''
                let idAnotacao = $('#idAnotacao').val()
                let justificativaAnotacao = $('#justificativaAnotacao').val()
				let peso = $('#peso').val()
				let anotacao = $('#anotacao').val()

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
                    case anotacao: msg = 'Informe o texto da Evolução!';$('#anotacao').focus();break
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
                        'tipoRequest': 'INCLUIRANOTACAOTECENFERMAGEM',
                        'tipo' : 'UPDATE',
                        'idAnotacao' : idAnotacao,
                        'justificativaAnotacao' : justificativaAnotacao,
						'inputPrevisaoAlta' : inputPrevisaoAlta,
                        'inputTipoInternacao' : inputTipoInternacao,
                        'inputEspLeito' : inputEspLeito,
                        'inputAla' : inputAla,
                        'inputQuarto' : inputQuarto,
                        'inputLeito' : inputLeito,		
						'peso' : peso,		
						'anotacao' : anotacao,		
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
                            $("#incluirAnotacao").css('display', 'block');
                            $("#salvarEdAnotacao").css('display', 'none');
                            zerarAnotacao()
                            getAnotacoes()
                            getSinaisVitais()

                        }else{
                            alerta(response.titulo, response.menssagem, response.status)
                        }
                    }
                });

            })

			$('.salvarAnotacaoTecEnfermagem').on('click', function(e){

                e.preventDefault();

                $.ajax({
					type: 'POST',
					url: 'filtraAtendimento.php',
					dataType: 'json',
					data: {
						'tipoRequest': 'SALVARANOTACAOTECENFERMAGEM',
						'iAtendimentoId' : <?php echo $iAtendimentoId; ?>,					
					},
					success: function(response) {
						if(response.status == 'success'){
							alerta(response.titulo, response.menssagem, response.status)
							getAnotacoes()
                            getSinaisVitais()
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
				$(".caracteres" + params.id).text("0 " + informativo);
			} else {
				$(".caracteres" + params.id).text(caracteresRestantes + " " + informativo);
			}
		}

        function getAnotacoes() {

            $.ajax({
                type: 'POST',
                url: 'filtraAtendimento.php',
                dataType: 'json',
                data:{
                    'tipoRequest': 'GETANOTACOESTECENFERMAGEM',
                    'id' : <?php echo $iAtendimentoId; ?>
                },
                success: function(response) {

                    $('#dataAnotacao').html('');
                    let HTML = ''
                    
                    response.forEach(item => {
						
						let situaChave = $("#atendimentoSituaChave").val();
                        let copiar = `<a class='list-icons-item mr-2 ' style='color: black; cursor:pointer' onclick='copiarAnotacao(\"${item.justificativaCompleta}\",\"${item.peso}\", \"${item.anotacaoCompleta}\" )'><i class='icon-files-empty' title='Copiar Anotacao'></i></a>`;
                        let editar = `<a class='list-icons-item mr-2 ' style='color: black; cursor:pointer'  onclick='editarAnotacao(\"${item.id}\")' class='list-icons-item' ><i class='icon-pencil7' title='Editar Anotacao'></i></a>`;
                        let exc = `<a style='color: black; cursor:pointer' onclick='excluirAnotacao(\"${item.id}\")' class='list-icons-item'><i class='icon-bin' title='Excluir Anotacao'></i></a>`;
                        let acoes = ``;

                        if (item.editavel == 1) {

							if (situaChave != 'ATENDIDO'){
								acoes = `<div class='list-icons'>
									${copiar}
                                    ${editar}
                                    ${exc}
								</div>`;
							} else{
								acoes = `<div class='list-icons'>
                                        
								</div>`;
							}

                        } else {

                           if (situaChave != 'ATENDIDO'){
								acoes = `<div class='list-icons'>
									${copiar}
								</div>`;
							} else{
								acoes = `<div class='list-icons'>
                                        
								</div>`;
							}
									
                        }
                        
                        HTML += `
                        <tr class='evolucaoItem'>
                            <td class="text-left">${item.item}</td>
                            <td class="text-left">${item.dataHora}</td>
                            <td class="text-left" title="${item.justificativaCompleta}">${item.justificativa}</td>
                            <td class="text-left" title="${item.anotacaoCompleta}">${item.anotacao}</td>
                            <td class="text-left">${item.peso} Kg</td>
                            <td class="text-center">${acoes}</td>
                        </tr>`

                    })
                    $('#dataAnotacao').html(HTML).show();
                }
            });	

        }

        function editarAnotacao(id) {

            $.ajax({
                type: 'POST',
                url: 'filtraAtendimento.php',
                dataType: 'json',
                data:{
                    'tipoRequest': 'GETANOTACAOTECENFERMAGEM',
                    'id' : id
                },
                success: function(response) {
                    
                    $('#idAnotacao').val(response.EnAnTId)

                    $('#justificativaAnotacao').val(response.EnAnTJustificativaLancRetroativo)
                    $('#peso').val(response.EnAnTPeso.replace(".", ","))
                    $('#anotacao').val(response.EnAnTAnotacao)

                    $('#inputSistolica').val(response.EnAnTPas)
                    $('#inputDiatolica').val(response.EnAnTPad)
                    $('#inputCardiaca').val(response.EnAnTFreqCardiaca)
                    $('#inputRespiratoria').val(response.EnAnTFreqRespiratoria)
                    $('#inputTemperatura').val(response.EnAnTTemperatura)
                    $('#inputSPO').val(response.EnAnTSPO)
                    $('#inputHGT').val(response.EnAnTHGT)

                    $("#incluirAnotacao").css('display', 'none');
                    $("#salvarEdAnotacao").css('display', 'block');

                    $('#anotacao').focus()		
                }
            });

        }

        function copiarAnotacao(justificativa, peso, anotacao) {
            $('#justificativaAnotacao').val(justificativa)
			$('#peso').val(peso.replace(".", ","))
			$('#anotacao').val(anotacao)   
        }

        function excluirAnotacao(id) {
			confirmaExclusaoAjax('filtraAtendimento.php', 'Excluir Anotação?', 'DELETEANOTACAOTECENFERMAGEM', id, getAnotacoes)
		}

        function zerarAnotacao() {

            $('#justificativaAnotacao').val('')
			$('#peso').val('')
			$('#anotacao').val('')

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
                                            <h3 class="card-title"><b>ANOTAÇÃO TÉCNICO DE ENFERMAGEM</b></h3>
                                        </div>            
                                    </div>

                                    <div class="col-md-6" style="text-align: right;">
                                        <div class="form-group" style="margin:20px;" >
											<?php 
                                                if (isset($_SESSION['SituaChave']) && $_SESSION['SituaChave'] != "ATENDIDO") {
                                                    echo "<button class='btn btn-lg btn-success mr-1 salvarAnotacaoTecEnfermagem' >Salvar</button>";
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
                        
                            <div class="card">

                                <div class="card-header header-elements-inline">
                                    <h3 class="card-title font-weight-bold">Anotação Técnico de Enfermagem</h3>
                                </div>

                                <div class="card-body">
									<?php 
                                        if (isset($_SESSION['SituaChave']) && $_SESSION['SituaChave'] != "ATENDIDO") {
                                            echo "<form id='formAnotacao' name='formAnotacao' method='post' class='form-validate-jquery'>
												<input type='hidden' name='idAnotacao' id='idAnotacao'>";
									
												echo "<div class='col-lg-12 mb-2 row' style='margin-left: -20px;'>
													<!-- titulos -->
													<div class='col-lg-2'>
														<label>Data/Hora <span class='text-danger'>*</span></label>
													</div>
													<div class='col-lg-9'>
														<label>Justificativa de Lançamento Retroativo</label>
													</div>
													<div class='col-lg-1'>
														<label>Peso(KG)</label>
													</div>
													
													<!-- campos -->										
													<div class='col-lg-2'>
														<input type='datatime-local' class='form-control' name='dataHoraAnotacao' id='dataHoraAnotacao' value='"; echo date('d/m/Y H:i');echo"' readonly>	
													</div>
													<div class='col-lg-9'>
														<input type='text' class='form-control' name='justificativaAnotacao' id='justificativaAnotacao' value=''>	
													
													</div>
													<div class='col-lg-1'>
														<input type='text' onKeyUp='moeda(this); ' class='form-control' name='peso' id='peso' value=''>	
													</div>
												
												</div>";
											
												echo "<div class='row'>
													<div class='col-lg-12'>
														<div class='form-group'>
															<label for='anotacao'>Anotação <span class='text-danger'>*</span></label>
															<textarea rows='5' cols='5' maxLength='500' id='anotacao' name='anotacao'  class='form-control' onInput='contarCaracteres(this);' placeholder='Corpo da anotação (informe aqui o texto que você queira que apareça na anotação)' ></textarea>
															<small class='text-muted form-text'>Max. 500 caracteres <span class='caracteresanotacao'></span></small>
														</div>
													</div>
												</div>";
											echo "</form>";

											echo "<div class='row'>
												<div class='col-lg-12'>
													<div class='form-group' style='padding-top:15px;'>
														<button class='btn btn-lg btn-success' id='incluirAnotacao' style='display: block;'  >Adicionar</button>	
														<button class='btn btn-lg btn-success' id='salvarEdAnotacao' style='display: none;'>Salvar Alterações</button>
													</div>
												</div>
											</div>";
                                        }
                                    ?>	 

                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <table class="table" id="tblAnotacao">
                                            <thead>
                                                <tr class="bg-slate">
                                                    <th class="text-left">Item</th>
                                                    <th class="text-left">Data/ Hora</th>
                                                    <th class="text-left">Justificativa de Lançamento Retroativo</th>
                                                    <th class="text-left">Anotação</th>
                                                    <th class="text-left">Peso</th>
                                                    <th class="text-center">Ações</th>
                                                </tr>
                                            </thead>
                                            <tbody id="dataAnotacao">
                                            </tbody>
                                        </table>
                                    </div>		
                                </div>							

                            </div>
                                			
                            <div class="card">

                                <div class=" card-body row">
                                    <div class="col-lg-12">
                                        <div class="form-group" style="margin-bottom:0px;">
											<?php 
												if (isset($_SESSION['SituaChave']) && $_SESSION['SituaChave'] != "ATENDIDO") {
													echo "<button class='btn btn-lg btn-success mr-1 salvarAnotacaoTecEnfermagem' >Salvar</button>";
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