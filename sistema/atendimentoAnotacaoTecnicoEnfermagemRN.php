<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Anotação Técnico de Enfermagem RN';

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
               TpIntNome, TpIntId, EsLeiNome, EsLeiId, AlaNome, AlaId, QuartNome, QuartId, LeitoNome, LeitoId, SituaChave
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
	<title>Lamparinas | Anotação Técnico de Enfermagem RN</title>

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
					width: "15%", 
					targets: [1]
				},
				{ 
					orderable: true,
					width: "10%", 
					targets: [2]
				},				
				{ 
					orderable: true,  
					width: "10%", 
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
				},				
				{ 
					orderable: true,  
					width: "10%", 
					targets: [6]
				},				
				{ 
					orderable: true,  
					width: "30%", 
					targets: [7]
				},				
				{ 
					orderable: true,  
					width: "10%", 
					targets: [8]
				}
            
            ],
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

                let inputDataInicio = $('#inputDataInicio').val()
                let inputInicio = $('#inputInicio').val()

                let inputPrevisaoAlta = $('#inputPrevisaoAlta').val()
                let inputTipoInternacao = $('#inputTipoInternacao').val()
                let inputEspLeito = $('#inputEspLeito').val()
                let inputAla = $('#inputAla').val()
                let inputQuarto = $('#inputQuarto').val()
                let inputLeito = $('#inputLeito').val()

                let inputNomeMae = $('#inputNomeMae').val()
                let inputDtNascimento = $('#inputDtNascimento').val()
                let inputHrNascimento = $('#inputHrNascimento').val()
                let cmbSexo = $('#cmbSexo').val() == '' ? 0 : $('#cmbSexo').val()
                let cmbChoroPresente = $('#cmbChoroPresente').val() == '' ? null : $('#cmbChoroPresente').val();
                let cmbSuccao = $('#cmbSuccao').val() == '' ? null : $('#cmbSuccao').val()
               
                var inputAmamentacao =  (typeof $('input[name="inputAmamentacao"]:checked').val() == 'undefined') ? null : $('input[name="inputAmamentacao"]:checked').val();
                let inputAmamentacaoDescricao = $('#inputAmamentacaoDescricao').val()

                let inputCardiacaM = $('#inputCardiacaM').val()
                let inputRespiratoriaM = $('#inputRespiratoriaM').val()
                let inputTemperaturaM = $('#inputTemperaturaM').val()
                let inputSPOM = $('#inputSPOM').val()
                let inputHGTM = $('#inputHGTM').val()
                let inputPesoM = $('#inputPesoM').val()

                let checkAtividadeHipoativo = $('#checkAtividadeHipoativo').is(':checked') ? 1 : 0;
                let checkAtividadeSonolento = $('#checkAtividadeSonolento').is(':checked') ? 1 : 0;
                let checkAtividadeAtivo = $('#checkAtividadeAtivo').is(':checked') ? 1 : 0;
                let checkAtividadeChoroso = $('#checkAtividadeChoroso').is(':checked') ? 1 : 0;
                let checkAtividadeGemente = $('#checkAtividadeGemente').is(':checked') ? 1 : 0;
                let inputAtividadeDescricao = $('#inputAtividadeDescricao').val()

                let checkColoracaoCorado = $('#checkColoracaoCorado').is(':checked') ? 1 : 0;
                let checkColoracaoHipocorado = $('#checkColoracaoHipocorado').is(':checked') ? 1 : 0;
                let checkColoracaoCianotico = $('#checkColoracaoCianotico').is(':checked') ? 1 : 0;
                let checkColoracaoIcterico = $('#checkColoracaoIcterico').is(':checked') ? 1 : 0;
                let checkColoracaoPletorico = $('#checkColoracaoPletorico').is(':checked') ? 1 : 0;
                let inputColoracaoDescricao = $('#inputColoracaoDescricao').val()

                let cmbHidratacao = $('#cmbHidratacao').val() == '' ? null : $('#cmbHidratacao').val()
                let cmbAbdome = $('#cmbAbdome').val() == '' ? null : $('#cmbAbdome').val()

                var inputPele = (typeof $('input[name="inputPele"]:checked').val() == 'undefined') ? null : $('input[name="inputPele"]:checked').val()
                let inputPeleDescricao = $('#inputPeleDescricao').val()

                var inputPadraoRespiratorio = (typeof $('input[name="inputPadraoRespiratorio"]:checked').val() == 'undefined') ? null : $('input[name="inputPadraoRespiratorio"]:checked').val()
                let inputPadraoRespDescricao = $('#inputPadraoRespDescricao').val()

                let checkCotoLimpoSeco = $('#checkCotoLimpoSeco').is(':checked') ? 1 : 0;
                let checkCotoGelatinoso = $('#checkCotoGelatinoso').is(':checked') ? 1 : 0;
                let checkCotoMumificado = $('#checkCotoMumificado').is(':checked') ? 1 : 0;
                let checkCotoUmido = $('#checkCotoUmido').is(':checked') ? 1 : 0;
                let checkCotoSujo = $('#checkCotoSujo').is(':checked') ? 1 : 0;
                let checkCotoFetido = $('#checkCotoFetido').is(':checked') ? 1 : 0;
                let inputCotoDescricao = $('#inputCotoDescricao').val();

                let inputAnotacoesDescricao = $('#inputAnotacoesDescricao').val();

                $.ajax({
					type: 'POST',
					url: 'filtraAtendimento.php',
					dataType: 'json',

					data: {
						'tipoRequest': 'INCLUIRANOTACAOTECENFERMAGEMRN',
                        'tipo' : 'INSERT',
						'iAtendimentoId' : <?php echo $iAtendimentoId; ?>,
                        'profissional' : <?php echo $userId; ?> ,

                        'inputDataInicio' : inputDataInicio,
                        'inputInicio' : inputInicio,
                        'inputPrevisaoAlta' : inputPrevisaoAlta,
                        'inputTipoInternacao' : inputTipoInternacao,
                        'inputEspLeito' : inputEspLeito,
                        'inputAla' : inputAla,
                        'inputQuarto' : inputQuarto,
                        'inputLeito' : inputLeito,

                        'inputNomeMae' : inputNomeMae,
                        'inputDtNascimento' : inputDtNascimento,
                        'inputHrNascimento' : inputHrNascimento,
                        'cmbSexo' : cmbSexo,
                        'cmbChoroPresente' : cmbChoroPresente,
                        'cmbSuccao' : cmbSuccao,
                        
                        'inputAmamentacao' : inputAmamentacao,
                        'inputAmamentacaoDescricao' : inputAmamentacaoDescricao,

                        'inputCardiacaM' : inputCardiacaM,
                        'inputRespiratoriaM' : inputRespiratoriaM,
                        'inputTemperaturaM' : inputTemperaturaM,
                        'inputSPOM' : inputSPOM,
                        'inputHGTM' : inputHGTM,
                        'inputPesoM' : inputPesoM,

                        'checkAtividadeHipoativo' : checkAtividadeHipoativo,
                        'checkAtividadeSonolento' : checkAtividadeSonolento,
                        'checkAtividadeAtivo' : checkAtividadeAtivo,
                        'checkAtividadeChoroso' : checkAtividadeChoroso,
                        'checkAtividadeGemente' : checkAtividadeGemente,
                        'inputAtividadeDescricao' : inputAtividadeDescricao,

                        'checkColoracaoCorado' : checkColoracaoCorado,
                        'checkColoracaoHipocorado' : checkColoracaoHipocorado,
                        'checkColoracaoCianotico' : checkColoracaoCianotico,
                        'checkColoracaoIcterico' : checkColoracaoIcterico,
                        'checkColoracaoPletorico' : checkColoracaoPletorico,
                        'inputColoracaoDescricao' : inputColoracaoDescricao,

                        'cmbHidratacao' : cmbHidratacao,
                        'cmbAbdome' : cmbAbdome,

                        'inputPele' : inputPele,
                        'inputPeleDescricao' : inputPeleDescricao,

                        'inputPadraoRespiratorio' : inputPadraoRespiratorio,
                        'inputPadraoRespDescricao' : inputPadraoRespDescricao,

                        'checkCotoLimpoSeco' : checkCotoLimpoSeco,
                        'checkCotoGelatinoso' : checkCotoGelatinoso,
                        'checkCotoMumificado' : checkCotoMumificado,
                        'checkCotoUmido' : checkCotoUmido,
                        'checkCotoSujo' : checkCotoSujo,
                        'checkCotoFetido' : checkCotoFetido,
                        'inputCotoDescricao' : inputCotoDescricao,

                        'inputAnotacoesDescricao' : inputAnotacoesDescricao  

					},
					success: function(response) {
						if(response.status == 'success'){
							alerta(response.titulo, response.menssagem, response.status)
							getAnotacoes()
							zerarAnotacao()
						}else{
							alerta(response.titulo, response.menssagem, response.status)
						}
					}
				});
                
            })

            $('#salvarEdAnotacao').on('click', function (e) {

                e.preventDefault();

                let idAnotacao = $('#idAnotacao').val()

                let inputDataInicio = $('#inputDataInicio').val()
                let inputInicio = $('#inputInicio').val()

                let inputPrevisaoAlta = $('#inputPrevisaoAlta').val()
                let inputTipoInternacao = $('#inputTipoInternacao').val()
                let inputEspLeito = $('#inputEspLeito').val()
                let inputAla = $('#inputAla').val()
                let inputQuarto = $('#inputQuarto').val()
                let inputLeito = $('#inputLeito').val()

                let inputNomeMae = $('#inputNomeMae').val()
                let inputDtNascimento = $('#inputDtNascimento').val()
                let inputHrNascimento = $('#inputHrNascimento').val()
                let cmbSexo = $('#cmbSexo').val() == '' ? 0 : $('#cmbSexo').val()
                let cmbChoroPresente = $('#cmbChoroPresente').val() == '' ? null : $('#cmbChoroPresente').val();
                let cmbSuccao = $('#cmbSuccao').val() == '' ? null : $('#cmbSuccao').val()
               
                var inputAmamentacao =  (typeof $('input[name="inputAmamentacao"]:checked').val() == 'undefined') ? null : $('input[name="inputAmamentacao"]:checked').val();
                let inputAmamentacaoDescricao = $('#inputAmamentacaoDescricao').val()

                let inputCardiacaM = $('#inputCardiacaM').val()
                let inputRespiratoriaM = $('#inputRespiratoriaM').val()
                let inputTemperaturaM = $('#inputTemperaturaM').val()
                let inputSPOM = $('#inputSPOM').val()
                let inputHGTM = $('#inputHGTM').val()
                let inputPesoM = $('#inputPesoM').val()

                let checkAtividadeHipoativo = $('#checkAtividadeHipoativo').is(':checked') ? 1 : 0;
                let checkAtividadeSonolento = $('#checkAtividadeSonolento').is(':checked') ? 1 : 0;
                let checkAtividadeAtivo = $('#checkAtividadeAtivo').is(':checked') ? 1 : 0;
                let checkAtividadeChoroso = $('#checkAtividadeChoroso').is(':checked') ? 1 : 0;
                let checkAtividadeGemente = $('#checkAtividadeGemente').is(':checked') ? 1 : 0;
                let inputAtividadeDescricao = $('#inputAtividadeDescricao').val()

                let checkColoracaoCorado = $('#checkColoracaoCorado').is(':checked') ? 1 : 0;
                let checkColoracaoHipocorado = $('#checkColoracaoHipocorado').is(':checked') ? 1 : 0;
                let checkColoracaoCianotico = $('#checkColoracaoCianotico').is(':checked') ? 1 : 0;
                let checkColoracaoIcterico = $('#checkColoracaoIcterico').is(':checked') ? 1 : 0;
                let checkColoracaoPletorico = $('#checkColoracaoPletorico').is(':checked') ? 1 : 0;
                let inputColoracaoDescricao = $('#inputColoracaoDescricao').val()

                let cmbHidratacao = $('#cmbHidratacao').val() == '' ? null : $('#cmbHidratacao').val()
                let cmbAbdome = $('#cmbAbdome').val() == '' ? null : $('#cmbAbdome').val()

                var inputPele = (typeof $('input[name="inputPele"]:checked').val() == 'undefined') ? null : $('input[name="inputPele"]:checked').val()
                let inputPeleDescricao = $('#inputPeleDescricao').val()

                var inputPadraoRespiratorio = (typeof $('input[name="inputPadraoRespiratorio"]:checked').val() == 'undefined') ? null : $('input[name="inputPadraoRespiratorio"]:checked').val()
                let inputPadraoRespDescricao = $('#inputPadraoRespDescricao').val()

                let checkCotoLimpoSeco = $('#checkCotoLimpoSeco').is(':checked') ? 1 : 0;
                let checkCotoGelatinoso = $('#checkCotoGelatinoso').is(':checked') ? 1 : 0;
                let checkCotoMumificado = $('#checkCotoMumificado').is(':checked') ? 1 : 0;
                let checkCotoUmido = $('#checkCotoUmido').is(':checked') ? 1 : 0;
                let checkCotoSujo = $('#checkCotoSujo').is(':checked') ? 1 : 0;
                let checkCotoFetido = $('#checkCotoFetido').is(':checked') ? 1 : 0;
                let inputCotoDescricao = $('#inputCotoDescricao').val();
                let inputAnotacoesDescricao = $('#inputAnotacoesDescricao').val();

                $.ajax({
                    type: 'POST',
                    url: 'filtraAtendimento.php',
                    dataType: 'json',

                    data: {
                        'tipoRequest': 'INCLUIRANOTACAOTECENFERMAGEMRN',
                        'tipo' : 'UPDATE',
                        'idAnotacao' : idAnotacao,

                        'iAtendimentoId' : <?php echo $iAtendimentoId; ?>,
                        'profissional' : <?php echo $userId; ?> ,

                        'inputDataInicio' : inputDataInicio,
                        'inputInicio' : inputInicio,
                        'inputPrevisaoAlta' : inputPrevisaoAlta,
                        'inputTipoInternacao' : inputTipoInternacao,
                        'inputEspLeito' : inputEspLeito,
                        'inputAla' : inputAla,
                        'inputQuarto' : inputQuarto,
                        'inputLeito' : inputLeito,

                        'inputNomeMae' : inputNomeMae,
                        'inputDtNascimento' : inputDtNascimento,
                        'inputHrNascimento' : inputHrNascimento,
                        'cmbSexo' : cmbSexo,
                        'cmbChoroPresente' : cmbChoroPresente,
                        'cmbSuccao' : cmbSuccao,
                        
                        'inputAmamentacao' : inputAmamentacao,
                        'inputAmamentacaoDescricao' : inputAmamentacaoDescricao,

                        'inputCardiacaM' : inputCardiacaM,
                        'inputRespiratoriaM' : inputRespiratoriaM,
                        'inputTemperaturaM' : inputTemperaturaM,
                        'inputSPOM' : inputSPOM,
                        'inputHGTM' : inputHGTM,
                        'inputPesoM' : inputPesoM,

                        'checkAtividadeHipoativo' : checkAtividadeHipoativo,
                        'checkAtividadeSonolento' : checkAtividadeSonolento,
                        'checkAtividadeAtivo' : checkAtividadeAtivo,
                        'checkAtividadeChoroso' : checkAtividadeChoroso,
                        'checkAtividadeGemente' : checkAtividadeGemente,
                        'inputAtividadeDescricao' : inputAtividadeDescricao,

                        'checkColoracaoCorado' : checkColoracaoCorado,
                        'checkColoracaoHipocorado' : checkColoracaoHipocorado,
                        'checkColoracaoCianotico' : checkColoracaoCianotico,
                        'checkColoracaoIcterico' : checkColoracaoIcterico,
                        'checkColoracaoPletorico' : checkColoracaoPletorico,
                        'inputColoracaoDescricao' : inputColoracaoDescricao,

                        'cmbHidratacao' : cmbHidratacao,
                        'cmbAbdome' : cmbAbdome,

                        'inputPele' : inputPele,
                        'inputPeleDescricao' : inputPeleDescricao,

                        'inputPadraoRespiratorio' : inputPadraoRespiratorio,
                        'inputPadraoRespDescricao' : inputPadraoRespDescricao,

                        'checkCotoLimpoSeco' : checkCotoLimpoSeco,
                        'checkCotoGelatinoso' : checkCotoGelatinoso,
                        'checkCotoMumificado' : checkCotoMumificado,
                        'checkCotoUmido' : checkCotoUmido,
                        'checkCotoSujo' : checkCotoSujo,
                        'checkCotoFetido' : checkCotoFetido,
                        'inputCotoDescricao' : inputCotoDescricao,

                        'inputAnotacoesDescricao' : inputAnotacoesDescricao  			
                    },
                    success: function(response) {
                        if(response.status == 'success'){
                            alerta(response.titulo, response.menssagem, response.status)
                            $("#incluirAnotacao").css('display', 'block');
                            $("#salvarEdAnotacao").css('display', 'none');
                            zerarAnotacao()
                            getAnotacoes()

                        }else{
                            alerta(response.titulo, response.menssagem, response.status)
                        }
                    }
                });

            })

			$('.salvarAnotacaoTecEnfermagemRN').on('click', function(e){

                e.preventDefault();

                $.ajax({
					type: 'POST',
					url: 'filtraAtendimento.php',
					dataType: 'json',
					data: {
						'tipoRequest': 'SALVARANOTACAOTECENFERMAGEMRN',
						'iAtendimentoId' : <?php echo $iAtendimentoId; ?>,					
					},
					success: function(response) {
						if(response.status == 'success'){
							alerta(response.titulo, response.menssagem, response.status)
							getAnotacoes()
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
				$(".caracteres" + params.id).text(" - " + caracteresRestantes + " " + informativo);
			}
		}

        function getAnotacoes() {

            $.ajax({
                type: 'POST',
                url: 'filtraAtendimento.php',
                dataType: 'json',
                data:{
                    'tipoRequest': 'GETANOTACOESTECENFERMAGEMRN',
                    'id' : <?php echo $iAtendimentoId; ?>
                },
                success: function(response) {

                    $('#dataAnotacao').html('');
                    let HTML = ''
                    
                    response.forEach(item => {

                        let situaChave = $("#atendimentoSituaChave").val();
                        let copiar = `<a class='list-icons-item mr-2 ' style='color: black; cursor:pointer' onclick='copiarAnotacao(\"${item.id}\" )'><i class='icon-files-empty' title='Copiar Anotacao'></i></a>`;
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
                            <td class="text-left">${item.fc}</td>
                            <td class="text-left">${item.fr}</td>
                            <td class="text-left">${item.temperatura}</td>
                            <td class="text-left">${item.spo}</td>
                            <td class="text-left">${item.peso}</td>
                            <td class="text-left">${item.anotacao}</td>
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
                    'tipoRequest': 'GETANOTACAOTECENFERMAGEMRN',
                    'id' : id
                },
                success: function(response) {

                    let boxAmamentacao = '';
                    let boxPele = '';
                    let boxPadraoRespiratorio = '';

                    $('#inputNomeMae').val(response.EnAnTNomeMae)
                    $('#inputDtNascimento').val(response.EnAnTDataNascimento)
                    //$('#inputHrNascimento').val(response.EnAnTHoraNascimento)
                    $('#cmbSexo').val(response.EnAnTSexo).change()
                    $('#cmbChoroPresente').val(response.EnAnTChoroPresente).change()
                    $('#cmbSuccao').val(response.EnAnTSuccao).change()
                
                    boxAmamentacao += `
                        <label class="d-block ">Amamentação</label>
                        <div class="form-check form-check-inline">
                            <label class="form-check-label">
                                <input type="radio" class="form-check-input" name="inputAmamentacao" ${ response.EnAnTAmamentacao == 1 ? 'checked' : ''} value="1">
                                Sim
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <label class="form-check-label">
                                <input type="radio" class="form-check-input" name="inputAmamentacao" ${ response.EnAnTAmamentacao == 0 ? 'checked' : ''} value="0">
                                Não
                            </label>
                        </div>`;
                    
                    $('#inputAmamentacaoDescricao').val(response.EnAnTAmamentacaoDescricao)

                    $('#inputCardiacaM').val(response.EnAnTFreqCardiaca)
                    $('#inputRespiratoriaM').val(response.EnAnTFreqRespiratoria)
                    $('#inputTemperaturaM').val(response.EnAnTTemperatura)
                    $('#inputSPOM').val(response.EnAnTSPO)
                    $('#inputHGTM').val(response.EnAnTHGT)
                    $('#inputPesoM').val(response.EnAnTPeso == null ? response.EnAnTPeso : response.EnAnTPeso.replace(".", ","))

                    if(response.EnAnTAtividadeHipoativo == 1) $('#checkAtividadeHipoativo').prop("checked", true);
                    if(response.EnAnTAtividadeSonolento == 1) $('#checkAtividadeSonolento').prop("checked", true);
                    if(response.EnAnTAtividadeAtivo == 1) $('#checkAtividadeAtivo').prop("checked", true);
                    if(response.EnAnTAtividadeChoroso == 1) $('#checkAtividadeChoroso').prop("checked", true);
                    if(response.EnAnTAtividadeGemente == 1) $('#checkAtividadeGemente').prop("checked", true);

                    $('#inputAtividadeDescricao').val(response.EnAnTAtividadeDescricao)

                    if(response.EnAnTColoracaoCorado == 1) $('#checkColoracaoCorado').prop("checked", true);
                    if(response.EnAnTColoracaoHipoCorado == 1) $('#checkColoracaoHipocorado').prop("checked", true);
                    if(response.EnAnTColoracaoCianotico == 1) $('#checkColoracaoCianotico').prop("checked", true);
                    if(response.EnAnTColoracaoIcterico == 1) $('#checkColoracaoIcterico').prop("checked", true);
                    if(response.EnAnTColoracaoPletorico == 1) $('#checkColoracaoPletorico').prop("checked", true);

                    $('#inputColoracaoDescricao').val(response.EnAnTColoracaoDescricao)

                    $('#cmbHidratacao').val(response.EnAnTHidratacao).change()
                    $('#cmbAbdome').val(response.EnAnTAbdome).change()

                    boxPele = `
                        <div class="form-check form-check-inline mr-4">
                            <label class="form-check-label">
                                <input type="radio" class="form-check-input" name="inputPele" ${ response.EnAnTPele == 'IN' ? 'checked' : ''} value="IN">
                                Íntegra
                            </label>
                        </div>
                        <div class="form-check form-check-inline mr-4">
                            <label class="form-check-label">
                                <input type="radio" class="form-check-input" name="inputPele" ${ response.EnAnTPele == 'DE' ? 'checked' : ''} value="DE">
                                Descamativa
                            </label>
                        </div>
                        <div class="form-check form-check-inline mr-4">
                            <label class="form-check-label">
                                <input type="radio" class="form-check-input" name="inputPele" ${ response.EnAnTPele == 'ET' ? 'checked' : ''} value="ET">
                                Eritema Tóxico
                            </label>
                        </div>
                        <div class="form-check form-check-inline mr-4">
                            <label class="form-check-label">
                                <input type="radio" class="form-check-input" name="inputPele" ${ response.EnAnTPele == 'EN' ? 'checked' : ''} value="EN">
                                Enrugada
                            </label>
                        </div>`;

                    $('#inputPeleDescricao').val(response.EnAnTPeleDescricao)

                    boxPadraoRespiratorio = `
                        <div class="form-check form-check-inline mr-4">
                            <label class="form-check-label">
                                <input type="radio" class="form-check-input" name="inputPadraoRespiratorio" ${ response.EnAnTPadraoRespiratorio == 'EU' ? 'checked' : ''} value="EU">
                                Eupinéico
                            </label>
                        </div>

                        <div class="form-check form-check-inline mr-4">
                            <label class="form-check-label">
                                <input type="radio" class="form-check-input" name="inputPadraoRespiratorio" ${ response.EnAnTPadraoRespiratorio == 'TA' ? 'checked' : ''} value="TA">
                                Taquipnéia
                            </label>
                        </div>

                        <div class="form-check form-check-inline mr-4">
                            <label class="form-check-label">
                                <input type="radio" class="form-check-input" name="inputPadraoRespiratorio" ${ response.EnAnTPadraoRespiratorio == 'BR' ? 'checked' : ''} value="BR">
                                Bradipnéia
                            </label>
                        </div>

                        <div class="form-check form-check-inline mr-4">
                            <label class="form-check-label">
                                <input type="radio" class="form-check-input" name="inputPadraoRespiratorio" ${ response.EnAnTPadraoRespiratorio == 'ON' ? 'checked' : ''} value="ON">
                                Obestrução Nasal
                            </label>
                        </div>`;

                    $('#inputPadraoRespDescricao').val(response.EnAnTPadraoRespiratorioDescricao)


                    if(response.EnAnTCotoLimpoSeco == 1) $('#checkCotoLimpoSeco').prop("checked", true);
                    if(response.EnAnTCotoGelatinoso == 1) $('#checkCotoGelatinoso').prop("checked", true);
                    if(response.EnAnTCotoMumificado == 1) $('#checkCotoMumificado').prop("checked", true);
                    if(response.EnAnTCotoUmido == 1) $('#checkCotoUmido').prop("checked", true);
                    if(response.EnAnTCotoSujo == 1) $('#checkCotoSujo').prop("checked", true);
                    if(response.EnAnTCotoFetido == 1) $('#checkCotoFetido').prop("checked", true);

                    $('#inputCotoDescricao').val(response.EnAnTCotoDescricao);
                    $('#inputAnotacoesDescricao').val(response.EnAnTAnotacaoEnfermagem);
                
                    $('#box-amamentacao').html('');
                    $('#box-amamentacao').html(boxAmamentacao).show();

                    $('#box-pele').html('');
                    $('#box-pele').html(boxPele).show();

                    $('#box-padraoRespiratorio').html('');
                    $('#box-padraoRespiratorio').html(boxPadraoRespiratorio).show();

                    $('#idAnotacao').val(response.EnAnTId)
                    
                    $("#incluirAnotacao").css('display', 'none');
                    $("#salvarEdAnotacao").css('display', 'block');
                    	
                }
            });

        }

        function copiarAnotacao(id) {

            $.ajax({
                type: 'POST',
                url: 'filtraAtendimento.php',
                dataType: 'json',
                data:{
                    'tipoRequest': 'GETANOTACAOTECENFERMAGEMRN',
                    'id' : id
                },
                success: function(response) {

                    let boxAmamentacao = '';
                    let boxPele = '';
                    let boxPadraoRespiratorio = '';

                    $('#inputNomeMae').val(response.EnAnTNomeMae)
                    $('#inputDtNascimento').val(response.EnAnTDataNascimento)
                    //$('#inputHrNascimento').val(response.EnAnTHoraNascimento)
                    $('#cmbSexo').val(response.EnAnTSexo).change()
                    $('#cmbChoroPresente').val(response.EnAnTChoroPresente).change()
                    $('#cmbSuccao').val(response.EnAnTSuccao).change()
                
                    boxAmamentacao += `
                        <label class="d-block ">Amamentação</label>
                        <div class="form-check form-check-inline">
                            <label class="form-check-label">
                                <input type="radio" class="form-check-input" name="inputAmamentacao" ${ response.EnAnTAmamentacao == 1 ? 'checked' : ''} value="1">
                                Sim
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <label class="form-check-label">
                                <input type="radio" class="form-check-input" name="inputAmamentacao" ${ response.EnAnTAmamentacao == 0 ? 'checked' : ''} value="0">
                                Não
                            </label>
                        </div>`;
                    
                    $('#inputAmamentacaoDescricao').val(response.EnAnTAmamentacaoDescricao)

                    $('#inputCardiacaM').val(response.EnAnTFreqCardiaca)
                    $('#inputRespiratoriaM').val(response.EnAnTFreqRespiratoria)
                    $('#inputTemperaturaM').val(response.EnAnTTemperatura)
                    $('#inputSPOM').val(response.EnAnTSPO)
                    $('#inputHGTM').val(response.EnAnTHGT)
                    $('#inputPesoM').val(response.EnAnTPeso == null ? response.EnAnTPeso : response.EnAnTPeso.replace(".", ","))

                    if(response.EnAnTAtividadeHipoativo == 1) $('#checkAtividadeHipoativo').prop("checked", true);
                    if(response.EnAnTAtividadeSonolento == 1) $('#checkAtividadeSonolento').prop("checked", true);
                    if(response.EnAnTAtividadeAtivo == 1) $('#checkAtividadeAtivo').prop("checked", true);
                    if(response.EnAnTAtividadeChoroso == 1) $('#checkAtividadeChoroso').prop("checked", true);
                    if(response.EnAnTAtividadeGemente == 1) $('#checkAtividadeGemente').prop("checked", true);

                    $('#inputAtividadeDescricao').val(response.EnAnTAtividadeDescricao)

                    if(response.EnAnTColoracaoCorado == 1) $('#checkColoracaoCorado').prop("checked", true);
                    if(response.EnAnTColoracaoHipoCorado == 1) $('#checkColoracaoHipocorado').prop("checked", true);
                    if(response.EnAnTColoracaoCianotico == 1) $('#checkColoracaoCianotico').prop("checked", true);
                    if(response.EnAnTColoracaoIcterico == 1) $('#checkColoracaoIcterico').prop("checked", true);
                    if(response.EnAnTColoracaoPletorico == 1) $('#checkColoracaoPletorico').prop("checked", true);

                    $('#inputColoracaoDescricao').val(response.EnAnTColoracaoDescricao)

                    $('#cmbHidratacao').val(response.EnAnTHidratacao).change()
                    $('#cmbAbdome').val(response.EnAnTAbdome).change()

                    boxPele = `
                        <div class="form-check form-check-inline mr-4">
                            <label class="form-check-label">
                                <input type="radio" class="form-check-input" name="inputPele" ${ response.EnAnTPele == 'IN' ? 'checked' : ''} value="IN">
                                Íntegra
                            </label>
                        </div>
                        <div class="form-check form-check-inline mr-4">
                            <label class="form-check-label">
                                <input type="radio" class="form-check-input" name="inputPele" ${ response.EnAnTPele == 'DE' ? 'checked' : ''} value="DE">
                                Descamativa
                            </label>
                        </div>
                        <div class="form-check form-check-inline mr-4">
                            <label class="form-check-label">
                                <input type="radio" class="form-check-input" name="inputPele" ${ response.EnAnTPele == 'ET' ? 'checked' : ''} value="ET">
                                Eritema Tóxico
                            </label>
                        </div>
                        <div class="form-check form-check-inline mr-4">
                            <label class="form-check-label">
                                <input type="radio" class="form-check-input" name="inputPele" ${ response.EnAnTPele == 'EN' ? 'checked' : ''} value="EN">
                                Enrugada
                            </label>
                        </div>`;

                    $('#inputPeleDescricao').val(response.EnAnTPeleDescricao)

                    boxPadraoRespiratorio = `
                        <div class="form-check form-check-inline mr-4">
                            <label class="form-check-label">
                                <input type="radio" class="form-check-input" name="inputPadraoRespiratorio" ${ response.EnAnTPadraoRespiratorio == 'EU' ? 'checked' : ''} value="EU">
                                Eupinéico
                            </label>
                        </div>

                        <div class="form-check form-check-inline mr-4">
                            <label class="form-check-label">
                                <input type="radio" class="form-check-input" name="inputPadraoRespiratorio" ${ response.EnAnTPadraoRespiratorio == 'TA' ? 'checked' : ''} value="TA">
                                Taquipnéia
                            </label>
                        </div>

                        <div class="form-check form-check-inline mr-4">
                            <label class="form-check-label">
                                <input type="radio" class="form-check-input" name="inputPadraoRespiratorio" ${ response.EnAnTPadraoRespiratorio == 'BR' ? 'checked' : ''} value="BR">
                                Bradipnéia
                            </label>
                        </div>

                        <div class="form-check form-check-inline mr-4">
                            <label class="form-check-label">
                                <input type="radio" class="form-check-input" name="inputPadraoRespiratorio" ${ response.EnAnTPadraoRespiratorio == 'ON' ? 'checked' : ''} value="ON">
                                Obestrução Nasal
                            </label>
                        </div>`;

                    $('#inputPadraoRespDescricao').val(response.EnAnTPadraoRespiratorioDescricao)

                    if(response.EnAnTCotoLimpoSeco == 1) $('#checkCotoLimpoSeco').prop("checked", true);
                    if(response.EnAnTCotoGelatinoso == 1) $('#checkCotoGelatinoso').prop("checked", true);
                    if(response.EnAnTCotoMumificado == 1) $('#checkCotoMumificado').prop("checked", true);
                    if(response.EnAnTCotoUmido == 1) $('#checkCotoUmido').prop("checked", true);
                    if(response.EnAnTCotoSujo == 1) $('#checkCotoSujo').prop("checked", true);
                    if(response.EnAnTCotoFetido == 1) $('#checkCotoFetido').prop("checked", true);

                    $('#inputCotoDescricao').val(response.EnAnTCotoDescricao);
                    $('#inputAnotacoesDescricao').val(response.EnAnTAnotacaoEnfermagem);
                
                    $('#box-amamentacao').html('');
                    $('#box-amamentacao').html(boxAmamentacao).show();

                    $('#box-pele').html('');
                    $('#box-pele').html(boxPele).show();

                    $('#box-padraoRespiratorio').html('');
                    $('#box-padraoRespiratorio').html(boxPadraoRespiratorio).show();
                    
                    $("#incluirAnotacao").css('display', 'block');
                    $("#salvarEdAnotacao").css('display', 'none');
                    	
                }
            });   
        }

        function excluirAnotacao(id) {
			confirmaExclusaoAjax('filtraAtendimento.php', 'Excluir Anotação?', 'DELETEANOTACAOTECENFERMAGEMRN', id, getAnotacoes)
		}

        function zerarAnotacao() {

            let boxAmamentacao = '';
            let boxPele = '';
            let boxPadraoRespiratorio = '';

            $('#inputNomeMae').val('')
            $('#inputDtNascimento').val('')
            $('#inputHrNascimento').val('')
            $('#cmbSexo').val('').change()
            $('#cmbChoroPresente').val('').change()
            $('#cmbSuccao').val('').change()
        
            boxAmamentacao += `
                <label class="d-block ">Amamentação</label>
                <div class="form-check form-check-inline">
                    <label class="form-check-label">
                        <input type="radio" class="form-check-input" name="inputAmamentacao" value="1">
                        Sim
                    </label>
                </div>
                <div class="form-check form-check-inline">
                    <label class="form-check-label">
                        <input type="radio" class="form-check-input" name="inputAmamentacao" value="0">
                        Não
                    </label>
                </div>`;
            
            $('#inputAmamentacaoDescricao').val('')

            $('#inputCardiacaM').val('')
            $('#inputRespiratoriaM').val('')
            $('#inputTemperaturaM').val('')
            $('#inputSPOM').val('')
            $('#inputHGTM').val('')
            $('#inputPesoM').val('')

            $('#checkAtividadeHipoativo').prop("checked", false);
            $('#checkAtividadeSonolento').prop("checked", false);
            $('#checkAtividadeAtivo').prop("checked", false);
            $('#checkAtividadeChoroso').prop("checked", false);
            $('#checkAtividadeGemente').prop("checked", false);

            $('#inputAtividadeDescricao').val('')

            $('#checkColoracaoCorado').prop("checked", false);
            $('#checkColoracaoHipocorado').prop("checked", false);
            $('#checkColoracaoCianotico').prop("checked", false);
            $('#checkColoracaoIcterico').prop("checked", false);
            $('#checkColoracaoPletorico').prop("checked", false);

            $('#inputColoracaoDescricao').val('')

            $('#cmbHidratacao').val('').change()
            $('#cmbAbdome').val('').change()

            boxPele = `
                <div class="form-check form-check-inline mr-4">
                    <label class="form-check-label">
                        <input type="radio" class="form-check-input" name="inputPele" value="IN">
                        Íntegra
                    </label>
                </div>
                <div class="form-check form-check-inline mr-4">
                    <label class="form-check-label">
                        <input type="radio" class="form-check-input" name="inputPele" value="DE">
                        Descamativa
                    </label>
                </div>
                <div class="form-check form-check-inline mr-4">
                    <label class="form-check-label">
                        <input type="radio" class="form-check-input" name="inputPele" value="ET">
                        Eritema Tóxico
                    </label>
                </div>
                <div class="form-check form-check-inline mr-4">
                    <label class="form-check-label">
                        <input type="radio" class="form-check-input" name="inputPele" value="EN">
                        Enrugada
                    </label>
                </div>`;

            $('#inputPeleDescricao').val('')

            boxPadraoRespiratorio = `
                <div class="form-check form-check-inline mr-4">
                    <label class="form-check-label">
                        <input type="radio" class="form-check-input" name="inputPadraoRespiratorio" value="EU">
                        Eupinéico
                    </label>
                </div>

                <div class="form-check form-check-inline mr-4">
                    <label class="form-check-label">
                        <input type="radio" class="form-check-input" name="inputPadraoRespiratorio" value="TA">
                        Taquipnéia
                    </label>
                </div>

                <div class="form-check form-check-inline mr-4">
                    <label class="form-check-label">
                        <input type="radio" class="form-check-input" name="inputPadraoRespiratorio" value="BR">
                        Bradipnéia
                    </label>
                </div>

                <div class="form-check form-check-inline mr-4">
                    <label class="form-check-label">
                        <input type="radio" class="form-check-input" name="inputPadraoRespiratorio" value="ON">
                        Obestrução Nasal
                    </label>
                </div>`;

            $('#inputPadraoRespDescricao').val('')


            $('#checkCotoLimpoSeco').prop("checked", false);
            $('#checkCotoGelatinoso').prop("checked", false);
            $('#checkCotoMumificado').prop("checked", false);
            $('#checkCotoUmido').prop("checked", false);
            $('#checkCotoSujo').prop("checked", false);
            $('#checkCotoFetido').prop("checked", false);

            $('#inputCotoDescricao').val('');
            $('#inputAnotacoesDescricao').val('');
        
            $('#box-amamentacao').html('');
            $('#box-amamentacao').html(boxAmamentacao).show();

            $('#box-pele').html('');
            $('#box-pele').html(boxPele).show();

            $('#box-padraoRespiratorio').html('');
            $('#box-padraoRespiratorio').html(boxPadraoRespiratorio).show();

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
                            <input type="hidden" name="idAnotacao" id="idAnotacao">
							<?php
								echo "<input type='hidden' id='iAtendimentoId' name='iAtendimentoId' value='$iAtendimentoId' />";
                                echo "<input type='hidden' id='atendimentoSituaChave' value='".$_SESSION['SituaChave']."' />";
                            ?>
							<div class="card">

                            <div class="col-md-12">
                                <div class="row">

                                    <div class="col-md-6" style="text-align: left;">
                                        <div class="card-header header-elements-inline">
                                            <h3 class="card-title"><b>ANOTAÇÃO TÉCNICO DE ENFERMAGEM RN</b></h3>
                                        </div>            
                                    </div>

                                    <div class="col-md-6" style="text-align: right;">
                                        <div class="form-group" style="margin:20px;" >
                                            <?php 
                                                if (isset($_SESSION['SituaChave']) && $_SESSION['SituaChave'] != "ATENDIDO") {
                                                    echo "<button class='btn btn-lg btn-success mr-1 salvarAnotacaoTecEnfermagemRN' >Salvar</button>";
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
                                <?php //include ('atendimentoDadosSinaisVitais.php'); ?>
                            </div>
                        
                            <div class="card">

                                <div class="card-header header-elements-inline">
                                    <h3 class="card-title font-weight-bold">Evolução Recém Nascido</h3>
                                </div>

                                <div class="card-body" style="">

                                    <div class="row"> 
                                        <div class="col-lg-7">
                                            <div class="form-group">
                                                <label for="inputNomeMae">RN / Mãe</label>                                               
                                                <input type="text" onKeyUp="" id="inputNomeMae" name="inputNomeMae" class="form-control" placeholder="" value="">                                                
                                            </div>
                                        </div>

                                        <div class="col-lg-5 row">
                                            <div class="col-lg-4">
                                                <div class="form-group">
                                                    <label for="inputDtNascimento">Data de Nascimento</label>
                                                    <input type="date" onKeyUp="" id="inputDtNascimento" name="inputDtNascimento" class="form-control" placeholder="" value="">     
                                                </div>
                                            </div>

                                            <div class="col-lg-4">
                                                <div class="form-group">
                                                    <label for="inputHrNascimento">Hora de Nascimento</label>
                                                    <input type="time" id="inputHrNascimento" name="inputHrNascimento" class="form-control" placeholder="" value="">
                                                </div>
                                            </div>

                                            <div class="col-lg-4">
                                                <div class="form-group">
                                                    <label for="cmbSexo">Sexo</label>
                                                    <select id="cmbSexo" name="cmbSexo" class="form-control-select2" >
                                                        <option value="">Selecione</option>
                                                        <option value='M'>MASCULINO</option>
                                                        <option value='F'>FEMININO</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">                                       
                                        <div class="col-lg-4 row">

                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label for="cmbChoroPresente">Choro Presente</label>
                                                    <select id="cmbChoroPresente" name="cmbChoroPresente" class="form-control-select2" >
                                                        <option value="">Selecione</option>
                                                        <option value='1'>SIM</option>
                                                        <option value='0'>NÃO</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label for="cmbSuccao">Sucção</label>
                                                    <select id="cmbSuccao" name="cmbSuccao" class="form-control-select2" >
                                                        <option value="">Selecione</option>
                                                        <option value='1'>SIM</option>
                                                        <option value='0'>NÃO</option>
                                                    </select>
                                                </div>
                                            </div>
                                            
                                        </div>

                                        <div class="col-lg-8 row ml-4">

                                            <div class="col-lg-3">
                                                <div class="form-group" id="box-amamentacao">
                                                    <label class="d-block ">Amamentação</label>
                                                    <div class="form-check form-check-inline">
                                                        <label class="form-check-label">
                                                            <input type="radio" class="form-check-input" name="inputAmamentacao" value="1">
                                                            Sim
                                                        </label>
                                                    </div>

                                                    <div class="form-check form-check-inline">
                                                        <label class="form-check-label">
                                                            <input type="radio" class="form-check-input" name="inputAmamentacao" value="0">
                                                            Não
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-lg-9 mr-1" style="margin-left: -10px" >
                                                <input type="text" id="inputAmamentacaoDescricao" name="inputAmamentacaoDescricao" maxLength="60" onInput="contarCaracteres(this);" class="form-control" placeholder="" value="<?php if (isset($iAtendimentoExameFisicoId )) echo $rowExameFisico['EnExFOlfatoAlteracao']; ?>">
                                                <small class="text-muted form-text">Max. de 60 caracteres<span class="caracteresinputAmamentacaoDescricao"></span></small>
                                            </div>

                                    
                                        </div>
                                    </div>
                                </div>

                                <hr/>

                                <div class="card-body">
                                    <div class="row">
                                        <div class="card-header header-elements-inline" style="margin-left: -10px">
                                            <h3 class="card-title font-weight-bold">SSVV / Monitoramento</h3>
                                        </div>
                                    </div>
                                    <div class="row" style="justify-content: space-between;" >                                                        
                                        <div class="col-lg-2" style="margin-right: 10px;">
                                            <div class="form-group">
                                                <label for="inputCardiacaM">FC <span class="">(bpm)</span></label>
                                                <div class="input-group">
                                                    <input type="number" id="inputCardiacaM" name="inputCardiacaM" class="form-control" placeholder="" value="">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-2" style="margin-right: 20px;">
                                            <div class="form-group">
                                                <label for="inputRespiratoriaM">FR <span class="">(rpm)</span></label>
                                                <div class="input-group">												
                                                    <input type="number" onKeyUp="" id="inputRespiratoriaM" name="inputRespiratoriaM" class="form-control" placeholder="" value="">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-lg-2" style="margin-right: 10px;">
                                            <div class="form-group">
                                                <label for="inputTemperaturaM">Temperatura <span class="">(ºC)</span></label>
                                                <div class="input-group">
                                                <input type="number" id="inputTemperaturaM" name="inputTemperaturaM" class="form-control" placeholder="" value="">
                                                    <span class="input-group-prepend">
                                                        <span class="input-group-text"><img src="global_assets/images/lamparinas/thermometro.png" width="32" style="margin-top: -13px;" alt="termometro" /></span>
                                                    </span>
                                                    
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-1" style="margin-right: 20px;">
                                            <div class="form-group">
                                                <label for="inputSPOM">SPO<sub>2</sub> <span class="">(%)</span></label>
                                                <div class="input-group">
                                                    <input type="number" onKeyUp="" id="inputSPOM" name="inputSPOM" class="form-control" placeholder="" value="">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-1" style="margin-right: 20px;">
                                            <div class="form-group">
                                            <label for="inputHGTM">HGT <span class="">(mg/dl)</span></label>
                                                <input type="number" id="inputHGTM" name="inputHGTM" class="form-control" placeholder="" value="">
                                            </div>
                                        </div>
                                        <div class="col-lg-1">
                                            <div class="form-group">
                                            <label for="inputPesoM">Peso <span class="">(Kg)</span></label>
                                                <input type="text" id="inputPesoM" name="inputPesoM" class="form-control" onKeyUp="moeda(this);" placeholder="" value="">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="card-header header-elements-inline" style="margin-left: -20px;">
                                                <h3 class="card-title font-weight-bold" >Atividade</h3>
                                            </div>   
                                            
                                            <div class="col-lg-12">
                                                <div class="row" >
                                                    <div class="form-check form-check-inline mr-4 ">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input" id="checkAtividadeHipoativo" value="HI" >
                                                            Hipoativo
                                                        </label>
                                                    </div>

                                                    <div class="form-check form-check-inline mr-4 " >
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input" id="checkAtividadeSonolento" value="SO">
                                                            Sonolento
                                                        </label>
                                                    </div>

                                                    <div class="form-check form-check-inline mr-4 ">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input" id="checkAtividadeAtivo" value="AT">
                                                            Ativo
                                                        </label>
                                                    </div>
                                                    <div class="form-check form-check-inline mr-4 ">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input" id="checkAtividadeChoroso" value="CH">
                                                            Choroso
                                                        </label>
                                                    </div>
                                                    <div class="form-check form-check-inline mr-4 ">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input" id="checkAtividadeGemente" value="GE">
                                                            Gemente
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <input type="text" id="inputAtividadeDescricao" name="inputAtividadeDescricao" maxLength="60" onInput="contarCaracteres(this);" class="form-control" placeholder="" value="<?php if (isset($iAtendimentoExameFisicoId )) echo $rowExameFisico['EnExFOlfatoAlteracao']; ?>">
                                                    <small class="text-muted form-text">Max. de 60 caracteres<span class="caracteresinputAtividadeDescricao"></span></small>
                                                </div>
                                            </div>
                                            
                                        </div>

                                        <div class="col-lg-6">
                                            <div class="card-header header-elements-inline" style="margin-left: -20px;">
                                                <h3 class="card-title font-weight-bold">Coloração</h3>
                                            </div> 

                                            <div class="col-lg-12">
                                                <div class="row" style="">
                                                    <div class="form-check form-check-inline mr-4 ">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input" id="checkColoracaoCorado" value="HI" >
                                                            Corado
                                                        </label>
                                                    </div>

                                                    <div class="form-check form-check-inline mr-4 ">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input" id="checkColoracaoHipocorado" value="SO">
                                                            Hipocorado
                                                        </label>
                                                    </div>

                                                    <div class="form-check form-check-inline mr-4 ">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input" id="checkColoracaoCianotico" value="AT">
                                                            Cianotico
                                                        </label>
                                                    </div>
                                                    <div class="form-check form-check-inline mr-4 ">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input" id="checkColoracaoIcterico" value="CH">
                                                            Ictério
                                                        </label>
                                                    </div>
                                                    <div class="form-check form-check-inline mr-4 ">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input" id="checkColoracaoPletorico" value="GE">
                                                            Pletórico
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <input type="text" id="inputColoracaoDescricao" name="inputColoracaoDescricao" maxLength="60" onInput="contarCaracteres(this);" class="form-control" placeholder="" value="<?php if (isset($iAtendimentoExameFisicoId )) echo $rowExameFisico['EnExFOlfatoAlteracao']; ?>">
                                                    <small class="text-muted form-text">Max. de 60 caracteres<span class="caracteresinputColoracaoDescricao"></span></small>
                                                </div>
                                            </div>

                                        </div>
                                    </div>

                                    <div class="row col-lg-6">
                                        <div class="col-lg-4" style="margin-left: -10px;">
                                            <div class="card-header header-elements-inline" style="margin-left: -20px;">
                                                <h3 class="card-title font-weight-bold">Hidratação</h3>
                                            </div> 
                                            <select id="cmbHidratacao" name="cmbHidratacao" class="form-control-select2" >
                                                <option value="">Selecione</option>
                                                <option value='1'>SIM</option>
                                                <option value='0'>NÃO</option>
                                            </select>                                    
                                        </div>
                                        <div class="col-lg-8">
                                            <div class="card-header header-elements-inline" style="margin-left: -20px;">
                                                <h3 class="card-title font-weight-bold">Abdome</h3>
                                            </div> 
                                            <select id="cmbAbdome" name="cmbAbdome" class="form-control-select2"  >
                                                <option value="">Selecione</option>
                                                <option value='IN' <?php if (isset($iAtendimentoExameFisicoId )) echo $rowExameFisico['EnExFRegulacaoAlergia'] == 'IN' ? 'selected' : ''; ?> >ÍNTEGRO</option>
                                                <option value='FL' <?php if (isset($iAtendimentoExameFisicoId )) echo $rowExameFisico['EnExFRegulacaoAlergia'] == 'FL' ? 'selected' : ''; ?> >FLÁCIDO</option>
                                                <option value='GL' <?php if (isset($iAtendimentoExameFisicoId )) echo $rowExameFisico['EnExFRegulacaoAlergia'] == 'GL' ? 'selected' : ''; ?> >GLOBOSO</option>
                                                <option value='DI' <?php if (isset($iAtendimentoExameFisicoId )) echo $rowExameFisico['EnExFRegulacaoAlergia'] == 'DI' ? 'selected' : ''; ?> >DISTENDIDO</option>
                                                <option value='TI' <?php if (isset($iAtendimentoExameFisicoId )) echo $rowExameFisico['EnExFRegulacaoAlergia'] == 'TI' ? 'selected' : ''; ?> >TIMPÂNICO</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row">

                                        <div class="col-lg-6">
                                            <div class="card-header header-elements-inline" style="margin-left: -20px;">
                                                <h3 class="card-title font-weight-bold" >Pele</h3>
                                            </div>  
                                            
                                            <div class="col-lg-12">
                                                <div class="row" id="box-pele" >
                                                    <div class="form-check form-check-inline mr-4">
                                                        <label class="form-check-label">
                                                            <input type="radio" class="form-check-input" name="inputPele" value="IN">
                                                            Íntegra
                                                        </label>
                                                    </div>
                                                    <div class="form-check form-check-inline mr-4">
                                                        <label class="form-check-label">
                                                            <input type="radio" class="form-check-input" name="inputPele" value="DE">
                                                            Descamativa
                                                        </label>
                                                    </div>
                                                    <div class="form-check form-check-inline mr-4">
                                                        <label class="form-check-label">
                                                            <input type="radio" class="form-check-input" name="inputPele" value="ET">
                                                            Eritema Tóxico
                                                        </label>
                                                    </div>
                                                    <div class="form-check form-check-inline mr-4">
                                                        <label class="form-check-label">
                                                            <input type="radio" class="form-check-input" name="inputPele" value="EN">
                                                            Enrugada
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <input type="text" id="inputPeleDescricao" name="inputPeleDescricao" maxLength="60" onInput="contarCaracteres(this);" class="form-control" placeholder="" value="<?php if (isset($iAtendimentoExameFisicoId )) echo $rowExameFisico['EnExFOlfatoAlteracao']; ?>">
                                                    <small class="text-muted form-text">Max. de 60 caracteres<span class="caracteresinputPeleDescricao"></span></small>
                                                </div>
                                            </div>
                                            
                                        </div>

                                        <div class="col-lg-6">
                                            <div class="card-header header-elements-inline" style="margin-left: -20px;">
                                                <h3 class="card-title font-weight-bold">Padrão Respiratório</h3>
                                            </div> 

                                            <div class="col-lg-12">
                                                <div class="row" id="box-padraoRespiratorio" >

                                                    <div class="form-check form-check-inline mr-4">
                                                        <label class="form-check-label">
                                                            <input type="radio" class="form-check-input" name="inputPadraoRespiratorio" value="EU">
                                                            Eupinéico
                                                        </label>
                                                    </div>

                                                    <div class="form-check form-check-inline mr-4">
                                                        <label class="form-check-label">
                                                            <input type="radio" class="form-check-input" name="inputPadraoRespiratorio" value="TA">
                                                            Taquipnéia
                                                        </label>
                                                    </div>

                                                    <div class="form-check form-check-inline mr-4">
                                                        <label class="form-check-label">
                                                            <input type="radio" class="form-check-input" name="inputPadraoRespiratorio" value="BR">
                                                            Bradipnéia
                                                        </label>
                                                    </div>

                                                    <div class="form-check form-check-inline mr-4">
                                                        <label class="form-check-label">
                                                            <input type="radio" class="form-check-input" name="inputPadraoRespiratorio" value="ON">
                                                            Obestrução Nasal
                                                        </label>
                                                    </div>

                                                </div>
                                                <div class="row">
                                                    <input type="text" id="inputPadraoRespDescricao" name="inputPadraoRespDescricao" maxLength="60" onInput="contarCaracteres(this);" class="form-control" placeholder="" value="<?php if (isset($iAtendimentoExameFisicoId )) echo $rowExameFisico['EnExFOlfatoAlteracao']; ?>">
                                                    <small class="text-muted form-text">Max. de 60 caracteres<span class="caracteresinputPadraoRespDescricao"></span></small>
                                                </div>
                                            </div>

                                        </div>

                                    </div>

                                    <div class="row col-lg-12">                                       
                                        
                                        <div class="card-header header-elements-inline" style="margin-left: -20px;">
                                            <h3 class="card-title font-weight-bold" >Coto Umbilical</h3>
                                        </div>    
                                        
                                        <div class="col-lg-12">
                                            <div class="row" style="justify-content: space-between;" >
                                                <div class="form-check form-check-inline mr-4 ">
                                                    <label class="form-check-label">
                                                        <input type="checkbox" class="form-check-input" id="checkCotoLimpoSeco" value="LS" >
                                                        Limpo e Seco
                                                    </label>
                                                </div>

                                                <div class="form-check form-check-inline mr-4 ">
                                                    <label class="form-check-label">
                                                        <input type="checkbox" class="form-check-input" id="checkCotoGelatinoso" value="GE">
                                                        Gelatinoso
                                                    </label>
                                                </div>

                                                <div class="form-check form-check-inline mr-4 ">
                                                    <label class="form-check-label">
                                                        <input type="checkbox" class="form-check-input" id="checkCotoMumificado" value="MU">
                                                        Mumificado
                                                    </label>
                                                </div>
                                                <div class="form-check form-check-inline mr-4 ">
                                                    <label class="form-check-label">
                                                        <input type="checkbox" class="form-check-input" id="checkCotoUmido" value="UM">
                                                        Úmido
                                                    </label>
                                                </div>
                                                <div class="form-check form-check-inline mr-4 ">
                                                    <label class="form-check-label">
                                                        <input type="checkbox" class="form-check-input" id="checkCotoSujo" value="SU">
                                                        Sujo
                                                    </label>
                                                </div>
                                                <div class="form-check form-check-inline mr-4 ">
                                                    <label class="form-check-label">
                                                        <input type="checkbox" class="form-check-input" id="checkCotoFetido" value="FE">
                                                        Fétido
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <input type="text" id="inputCotoDescricao" name="inputCotoDescricao" maxLength="60" onInput="contarCaracteres(this);" class="form-control" placeholder="" value="<?php if (isset($iAtendimentoExameFisicoId )) echo $rowExameFisico['EnExFOlfatoAlteracao']; ?>">
                                                <small class="text-muted form-text">Max. de 60 caracteres<span class="caracteresinputCotoDescricao"></span></small>
                                            </div>
                                        </div>
                                        
                                    </div>

                                    <div class="row col-lg-12">
                                        
                                        <div class="card-header header-elements-inline" style="margin-left: -20px;">
                                            <h3 class="card-title font-weight-bold" >Anotações de Enfermagem</h3>
                                        </div>    
                                        
                                        <div class="col-lg-12">
                                            <div class="row">                                                    
                                                <textarea rows="3"  maxLength="150" onInput="contarCaracteres(this);"  id="inputAnotacoesDescricao" name="inputAnotacoesDescricao" class="form-control" placeholder="Corpo da anotação (informe aqui o texto que você queira que apareça na anotação de enfermagem)" ><?php if (isset($iAtendimentoAnamneseId )) echo $rowAnamnese['EnAnaQueixaPrincipal']; ?></textarea>
                                                <small class="text-muted form-text">Max. 150 caracteres<span class="caracteresinputAnotacoesDescricao"></span></small>                                                                                               
                                            </div>
                                        </div>
                                        
                                    </div>


                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="form-group" style="padding-top:15px;">
                                                <?php 
                                                    if (isset($_SESSION['SituaChave']) && $_SESSION['SituaChave'] != "ATENDIDO") {
                                                        echo "<button class='btn btn-lg btn-success' id='incluirAnotacao' style='display: block;'  >Adicionar</button>";
                                                    }
                                                ?>
                                                <button class="btn btn-lg btn-success" id="salvarEdAnotacao" style="display: none;">Salvar Alterações</button>
                                            </div>
                                        </div>
                                    </div>  
                                    
                                </div>
                                

                                <div class="row">
                                    <div class="col-lg-12">
                                        <table class="table" id="tblAnotacao">
                                            <thead>
                                                <tr class="bg-slate">
                                                    <th class="text-left">Item</th>
                                                    <th class="text-left">Data/ Hora</th>
                                                    <th class="text-left">FC (bpm)</th>
                                                    <th class="text-left">FR (irpm)</th>
                                                    <th class="text-left">Temperatura (ºC)</th>
                                                    <th class="text-left">SPO (%)</th>
                                                    <th class="text-left">Peso (Kg)</th>
                                                    <th class="text-left">Anotação</th>
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
                                                    echo " <button class='btn btn-lg btn-success mr-1 salvarAnotacaoTecEnfermagemRN' >Salvar</button>";
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