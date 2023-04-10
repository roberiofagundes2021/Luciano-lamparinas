<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Observação Hospitalar';

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
$sql = "SELECT AtendId, AtendCliente, AtendNumRegistro, AtClaNome, AtendDataRegistro, AtModNome, ClienId,
		ClienCodigo, ClienNome, ClienSexo, ClienDtNascimento,ClienNomeMae, ClienCartaoSus, ClienCelular,
		ClResNome, AtClaChave
		FROM Atendimento
		JOIN Cliente ON ClienId = AtendCliente
		LEFT JOIN ClienteResponsavel on ClResCliente = AtendCliente
		JOIN AtendimentoModalidade ON AtModId = AtendModalidade
		LEFT JOIN AtendimentoClassificacao ON AtClaId = AtendClassificacao
		LEFT JOIN Situacao ON SituaId = AtendSituacao
	    WHERE AtendId = $iAtendimentoId and AtendUnidade = ".$_SESSION['UnidadeId']."
		ORDER BY AtendDataRegistro ASC";
$result = $conn->query($sql);
$row = $result->fetch(PDO::FETCH_ASSOC);

$iAtendimentoId = $row['AtendId'];
$iClienteId = $row['ClienId'];

$sql = "SELECT AtTGaId, AtTGaAtendimento, AtTGaDataRegistro, AtTGaServico, AtTGaProfissional, AtTGaHorario, 
               AtTGaValor, AtTGaDesconto, AtTGaDesconto, AtendCliente, AtendDataRegistro, SrVenNome, ProfiNome
		FROM AtendimentoTabelaGasto
		JOIN Atendimento ON AtendId = AtTGaAtendimento
		JOIN Cliente ON ClienId = AtendCliente
		JOIN ServicoVenda ON SrVenId = AtTGaServico
		JOIN Profissional ON ProfiId = AtTGaProfissional
		JOIN Situacao ON SituaId = AtendSituacao
	    WHERE AtendCliente = $iClienteId and AtTGaUnidade = ".$_SESSION['UnidadeId']."
		ORDER BY AtTGaDataRegistro ASC";
$result = $conn->query($sql);
$rowTGasto = $result->fetchAll(PDO::FETCH_ASSOC);


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
	<title>Lamparinas | Observação Hospitalar</title>

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
			getMedicamentosSolucoes()
			getDietas()
			getCuidados()

			getCmbs()			

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
					width: "20%", 
					targets: [3]
				},		
				{ 
					orderable: true,  
					width: "10%", 
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

			/* Início: Tabela Personalizada */
			$('#tblMedicamentosSolucoes').DataTable( {
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
					width: "15%", 
					targets: [2]
				},				
				{ 
					orderable: true,   
					width: "10%", 
					targets: [3]
				},
				{ 
					orderable: true,   
					width: "30%", 
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

			/* Início: Tabela Personalizada */
			$('#tblDieta').DataTable( {
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
					width: "15%", 
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
				}],
				dom: '<"datatable-header"fl><"datatable-scroll-wrap"t><"datatable-footer">',
				language: {
					search: '<span>Filtro:</span> _INPUT_',
					searchPlaceholder: 'filtra qualquer coluna...',
					lengthMenu: '<span>Mostrar:</span> _MENU_',
					paginate: { 'first': 'Primeira', 'last': 'Última', 'next': $('html').attr('dir') == 'rtl' ? '&larr;' : '&rarr;', 'previous': $('html').attr('dir') == 'rtl' ? '&rarr;' : '&larr;' }
				}
                
			});

			/* Início: Tabela Personalizada */
			$('#tblCuidados').DataTable( {
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
					width: "15%", 
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

				let inputSistolica = $('#inputSistolica').val()
				let inputDiatolica = $('#inputDiatolica').val()
				let inputCardiaca = $('#inputCardiaca').val()
				let inputRespiratoria = $('#inputRespiratoria').val()
				let inputTemperatura = $('#inputTemperatura').val()
				let inputSPO = $('#inputSPO').val()
				let inputHGT = $('#inputHGT').val()
				let peso = $('#inputPeso').val()
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
						'evolucaoDiaria' : evolucaoDiaria,
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
						'inputUsoMedicamentoDescricao' : inputUsoMedicamentoDescricao,
						'peso' : peso						
					},
					success: function(response) {
						if(response.status == 'success'){
							alerta(response.titulo, response.menssagem, response.status)
							getEvolucaoDiaria()
							zerarEvolucao()
						}else{
							alerta(response.titulo, response.menssagem, response.status)
						}
					}
				});

				
			})

			$('#adicionarMedicamento').on('click', function (e) {
				e.preventDefault();

				let msg = ''

				let medicamentoEstoqueMedicamentos = $('#medicamentoEstoqueMedicamentos').val()
				let medicamentoDlMedicamentos = $('#medicamentoDlMedicamentos').val()
				let selViaMedicamentos = $('#selViaMedicamentos').val()
				let doseMedicamentos = $('#doseMedicamentos').val()
				let selUnidadeMedicamentos = $('#selUnidadeMedicamentos').val()
				let frequenciaMedicamentos = $('#frequenciaMedicamentos').val()
				let selTipoAprazamentoMedicamentos = $('#selTipoAprazamentoMedicamentos').val()
				let dataInicioMedicamentos = $('#dataInicioMedicamentos').val()
				let checkBombaInfusaoMedicamentos = $('#checkBombaInfusaoMedicamentos').is(':checked');
				let checkInicioAdmMedicamentos = $('#checkInicioAdmMedicamentos').is(':checked');
				let horaInicioAdmMedicamentos = $('#horaInicioAdmMedicamentos').val()
				let complementoMedicamentos = $('#complementoMedicamentos').val()
				let descricaoPosologiaMedicamentos = $('#descricaoPosologiaMedicamentos').val()
				let validadeInicioMedicamentos = $('#validadeInicioMedicamentos').val()
				let validadeFimMedicamentos = $('#validadeFimMedicamentos').val()

				checkBombaInfusaoMedicamentos = checkBombaInfusaoMedicamentos == true ? 1 : 0;
				checkInicioAdmMedicamentos = checkInicioAdmMedicamentos == true ? 1 : 0;

				if (medicamentoEstoqueMedicamentos == '' && medicamentoDlMedicamentos == '') {
					msg = 'Informe um medicamento no campo livre ou pesquise um medicamento do estoque!';
					alerta('Campo Obrigatório!', msg, 'error')
					$('#medicamentoDlMedicamentos').focus();return					
				}

				if (checkInicioAdmMedicamentos == 1 && horaInicioAdmMedicamentos == '') {

					msg = 'informe a hora do Início ADM, ou desmarque a opção!';
					alerta('Campo Obrigatório!', msg, 'error')
					$('#horaInicioAdmMedicamentos').focus();return	
					
				}

				switch(msg){
					case selViaMedicamentos: msg = 'Informe a via de administração do medicamento!';$('#selViaMedicamentos').focus();break
					case doseMedicamentos: msg = 'Informe a dose do medicamento';$('#doseMedicamentos').focus();break
					case selUnidadeMedicamentos: msg = 'Informe a unidade do medicamento!';$('#selUnidadeMedicamentos').focus();break
					case frequenciaMedicamentos: msg = 'Informe a frequência de administração do medicamento!';$('#frequenciaMedicamentos').focus();break
					case selTipoAprazamentoMedicamentos: msg = 'Informe o tipo de aprazamento do medicamento!';$('#selTipoAprazamentoMedicamentos').focus();break
					case descricaoPosologiaMedicamentos: msg = 'Informe a posologia da administração do medicamento!';$('#descricaoPosologiaMedicamentos').focus();break
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
						'tipoRequest': 'ADICIONARMEDICAMENTO',
						'iAtendimentoId' : <?php echo $iAtendimentoId; ?>,
						'profissional' : <?php echo $userId; ?> ,
						'tipo' : 'M',
						'medicamentoEstoqueMedicamentos' : medicamentoEstoqueMedicamentos,
						'medicamentoDlMedicamentos' : medicamentoDlMedicamentos,
						'selViaMedicamentos' : selViaMedicamentos,
						'doseMedicamentos' : doseMedicamentos,
						'selUnidadeMedicamentos' : selUnidadeMedicamentos,
						'frequenciaMedicamentos' : frequenciaMedicamentos,
						'selTipoAprazamentoMedicamentos' : selTipoAprazamentoMedicamentos,
						'dataInicioMedicamentos' : dataInicioMedicamentos,
						'checkBombaInfusaoMedicamentos' : checkBombaInfusaoMedicamentos,
						'checkInicioAdmMedicamentos' : checkInicioAdmMedicamentos,
						'horaInicioAdmMedicamentos' : horaInicioAdmMedicamentos,
						'complementoMedicamentos' : complementoMedicamentos,
						'descricaoPosologiaMedicamentos' : descricaoPosologiaMedicamentos,		
						'validadeInicioMedicamentos' : validadeInicioMedicamentos,
						'validadeFimMedicamentos' : validadeFimMedicamentos
					},
					success: function(response) {
						if(response.status == 'success'){
							alerta(response.titulo, response.menssagem, response.status)
							zerarMedicamento()
							getMedicamentosSolucoes()
						}else{
							alerta(response.titulo, response.menssagem, response.status)
						}
					}
				});

				
			})
			
			$('#adicionarSolucao').on('click', function (e) {
				e.preventDefault();

				let msg = ''

				let medicamentoEstoqueSolucoes = $('#medicamentoEstoqueSolucoes').val()
				let medicamentoDlSolucoes = $('#medicamentoDlSolucoes').val()
				let selViaSolucoes = $('#selViaSolucoes').val()
				let doseSolucoes = $('#doseSolucoes').val()
				let selUnidadeSolucoes = $('#selUnidadeSolucoes').val()
				let frequenciaSolucoes = $('#frequenciaSolucoes').val()
				let selTipoAprazamentoSolucoes = $('#selTipoAprazamentoSolucoes').val()
				let dataInicioSolucoes = $('#dataInicioSolucoes').val()
				let diluenteSolucoes = $('#diluenteSolucoes').val()
				let volumeSolucoes = $('#volumeSolucoes').val()
				let correrEmSolucoes = $('#correrEmSolucoes').val()
				let selUnTempoSolucoes = $('#selUnTempoSolucoes').val()
				let velocidadeInfusaoSolucoes = $('#velocidadeInfusaoSolucoes').val()
				let checkBombaInfusaoSolucoes = $('#checkBombaInfusaoSolucoes').is(':checked');
				let checkInicioAdmSolucoes = $('#checkInicioAdmSolucoes').is(':checked');
				let horaInicioAdmSolucoes = $('#horaInicioAdmSolucoes').val()
				let complementoSolucoes = $('#complementoSolucoes').val()
				let descricaoPosologiaSolucoes = $('#descricaoPosologiaSolucoes').val()
				let validadeInicioSolucoes = $('#validadeInicioSolucoes').val()
				let validadeFimSolucoes = $('#validadeFimSolucoes').val()

				checkBombaInfusaoSolucoes = checkBombaInfusaoSolucoes == true ? 1 : 0;
				checkInicioAdmSolucoes = checkInicioAdmSolucoes == true ? 1 : 0;


				if (medicamentoEstoqueSolucoes == '' && medicamentoDlSolucoes == '') {
					msg = 'Informe um medicamento no campo livre ou pesquise um medicamento do estoque!';
					alerta('Campo Obrigatório!', msg, 'error')
					$('#medicamentoDlSolucoes').focus();return					
				}

				if (checkInicioAdmSolucoes == 1 && horaInicioAdmSolucoes == '') {

					msg = 'informe a hora do Início ADM, ou desmarque a opção!';
					alerta('Campo Obrigatório!', msg, 'error')
					$('#horaInicioAdmSolucoes').focus();return	

				}

				switch(msg){
					case selViaSolucoes: msg = 'Informe a via de administração do medicamento!';$('#selViaSolucoes').focus();break
					case doseSolucoes: msg = 'Informe a dose do medicamento';$('#doseSolucoes').focus();break
					case selUnidadeSolucoes: msg = 'Informe a unidade do medicamento!';$('#selUnidadeSolucoes').focus();break
					case frequenciaSolucoes: msg = 'Informe a frequência de administração do medicamento!';$('#frequenciaSolucoes').focus();break
					case selTipoAprazamentoSolucoes: msg = 'Informe o tipo de aprazamento do medicamento!';$('#selTipoAprazamentoSolucoes').focus();break
					case descricaoPosologiaSolucoes: msg = 'Informe a posologia da administração do medicamento!';$('#descricaoPosologiaSolucoes').focus();break
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
						'tipoRequest': 'ADICIONARSOLUCAO',
						'iAtendimentoId' : <?php echo $iAtendimentoId; ?>,
						'profissional' : <?php echo $userId; ?> ,
						'tipo' : 'S',
						'medicamentoEstoqueSolucoes' : medicamentoEstoqueSolucoes,
						'medicamentoDlSolucoes' : medicamentoDlSolucoes,
						'selViaSolucoes' : selViaSolucoes,
						'doseSolucoes' : doseSolucoes,
						'selUnidadeSolucoes' : selUnidadeSolucoes,
						'frequenciaSolucoes' : frequenciaSolucoes,
						'selTipoAprazamentoSolucoes' : selTipoAprazamentoSolucoes,
						'dataInicioSolucoes' : dataInicioSolucoes,
						'diluenteSolucoes' : diluenteSolucoes,
						'volumeSolucoes' : volumeSolucoes,
						'correrEmSolucoes' : correrEmSolucoes,
						'selUnTempoSolucoes' : selUnTempoSolucoes,
						'velocidadeInfusaoSolucoes' : velocidadeInfusaoSolucoes,
						'checkBombaInfusaoSolucoes' : checkBombaInfusaoSolucoes,
						'checkInicioAdmSolucoes' : checkInicioAdmSolucoes,
						'horaInicioAdmSolucoes' : horaInicioAdmSolucoes,
						'complementoSolucoes' : complementoSolucoes,
						'descricaoPosologiaSolucoes' : descricaoPosologiaSolucoes,
						'validadeInicioSolucoes' : validadeInicioSolucoes,
						'validadeFimSolucoes' : validadeFimSolucoes
						
					},
					success: function(response) {
						if(response.status == 'success'){
							alerta(response.titulo, response.menssagem, response.status)
							zerarSolucao()
							getMedicamentosSolucoes()
						}else{
							alerta(response.titulo, response.menssagem, response.status)
						}
					}
				});
				
			})
			
			$('#adicionarDieta').on('click', function (e) {
				e.preventDefault();

				let msg = ''
				let dataInicialDieta = $('#dataInicialDieta').val()
				let dataFinalDieta = $('#dataFinalDieta').val()
				let selTipoDeDieta = $('#selTipoDeDieta').val()
				let selViaDieta = $('#selViaDieta').val()
				let freqDieta = $('#freqDieta').val()
				let selTipoAprazamentoDieta = $('#selTipoAprazamentoDieta').val()
				let checkBombaInfusaoDieta = $('#checkBombaInfusaoDieta').val()
				let descricaoDieta = $('#descricaoDieta').val()
				
				if (document.getElementById('checkBombaInfusaoDieta').checked) {
					checkBombaInfusaoDieta = 1;
				}else {
					checkBombaInfusaoDieta = 0;
				}

				switch(msg){
					case dataInicialDieta: msg = 'Informe a data inicial da dieta!';$('#dataInicialDieta').focus();break
					case selTipoDeDieta: msg = 'Informe o tipo de dieta!';$('#selTipoDeDieta').focus();break
					case freqDieta: msg = 'Informe a frequência da administração da dieta!';$('#freqDieta').focus();break
					case selTipoAprazamentoDieta: msg = 'Informe o tipo de aprazamentos da dieta!';$('#selTipoAprazamentoDieta').focus();break
					case descricaoDieta: msg = 'Informe a descrição da dieta!';$('#descricaoDieta').focus();break
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
						'tipoRequest': 'ADICIONARDIETA',
						'iAtendimentoId' : <?php echo $iAtendimentoId; ?>,
						'profissional' : <?php echo $userId; ?> ,
						'dataInicialDieta' : dataInicialDieta,
						'dataFinalDieta' : dataFinalDieta,
						'selTipoDeDieta' : selTipoDeDieta,
						'selViaDieta' : selViaDieta,
						'freqDieta' : freqDieta,
						'selTipoAprazamentoDieta' : selTipoAprazamentoDieta,
						'checkBombaInfusaoDieta' : checkBombaInfusaoDieta,
						'descricaoDieta' : descricaoDieta						
					},
					success: function(response) {
						if(response.status == 'success'){
							alerta(response.titulo, response.menssagem, response.status)
							zerarDieta()
							getDietas()
						}else{
							alerta(response.titulo, response.menssagem, response.status)
						}
					}
				});
				
			})

			$('#adicionarCuidado').on('click', function (e) {
				e.preventDefault();


				let msg = ''
				let snCuidado = 0
				let dataInicialCuidados = $('#dataInicialCuidados').val()
				let dataFinalCuidados = $('#dataFinalCuidados').val()
				let selTipoDeCuidado = $('#selTipoDeCuidado').val()
				let frequenciaCuidados = $('#frequenciaCuidados').val()
				let selTipoAprazamentoCuidados = $('#selTipoAprazamentoCuidados').val()
				let complementoCuidados = $('#complementoCuidados').val()
				let descricaoCuidados = $('#descricaoCuidados').val()
					
				if ($('#snCuidados').prop("checked") == true) {
					snCuidado = 1
				}
				
				switch(msg){
					case dataInicialCuidados: msg = 'Informe a data inicial dos cuidados!';$('#dataInicialCuidados').focus();break
					case selTipoDeCuidado: msg = 'Informe o tipo de cuidado!';$('#selTipoDeCuidado').focus();break
					case frequenciaCuidados: msg = 'Informe a frequência de aplicação dos cuidados!';$('#frequenciaCuidados').focus();break
					case selTipoAprazamentoCuidados: msg = 'Informe o tipo de aprazamento dos cuidados!';$('#selTipoAprazamentoCuidados').focus();break
					case descricaoCuidados: msg = 'Informe a descrição dos cuidados!';$('#descricaoCuidados').focus();break
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
						'tipoRequest': 'ADICIONARCUIDADO',
						'iAtendimentoId' : <?php echo $iAtendimentoId; ?>,
						'profissional' : <?php echo $userId; ?> ,
						'dataInicialCuidados' : dataInicialCuidados,
						'dataFinalCuidados' : dataFinalCuidados,
						'selTipoDeCuidado' : selTipoDeCuidado,
						'frequenciaCuidados' : frequenciaCuidados,
						'selTipoAprazamentoCuidados' : selTipoAprazamentoCuidados,
						'snCuidado' : snCuidado,
						'complementoCuidados' : complementoCuidados,
						'descricaoCuidados' : descricaoCuidados,						
					},
					success: function(response) {
						if(response.status == 'success'){
							alerta(response.titulo, response.menssagem, response.status)
							zerarCuidado()
							getCuidados()
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

				let inputSistolica = $('#inputSistolica').val()
				let inputDiatolica = $('#inputDiatolica').val()
				let inputCardiaca = $('#inputCardiaca').val()
				let inputRespiratoria = $('#inputRespiratoria').val()
				let inputTemperatura = $('#inputTemperatura').val()
				let inputSPO = $('#inputSPO').val()
				let inputHGT = $('#inputHGT').val()
				let peso = $('#inputPeso').val()                
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
						'evolucaoDiaria' : evolucaoDiaria,
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
						'inputUsoMedicamentoDescricao' : inputUsoMedicamentoDescricao,
						'peso' : peso					
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

			$('#salvarEdMedicamento').on('click', function (e) {
				e.preventDefault();

				let msg = ''

				let idMedicamentos = $('#idMedicamentos').val()
				let medicamentoEstoqueMedicamentos = $('#medicamentoEstoqueMedicamentos').val()
				let medicamentoDlMedicamentos = $('#medicamentoDlMedicamentos').val()
				let selViaMedicamentos = $('#selViaMedicamentos').val()
				let doseMedicamentos = $('#doseMedicamentos').val()
				let selUnidadeMedicamentos = $('#selUnidadeMedicamentos').val()
				let frequenciaMedicamentos = $('#frequenciaMedicamentos').val()
				let selTipoAprazamentoMedicamentos = $('#selTipoAprazamentoMedicamentos').val()
				let dataInicioMedicamentos = $('#dataInicioMedicamentos').val()
				let checkBombaInfusaoMedicamentos = $('#checkBombaInfusaoMedicamentos').is(':checked');
				let checkInicioAdmMedicamentos = $('#checkInicioAdmMedicamentos').is(':checked');
				let horaInicioAdmMedicamentos = $('#horaInicioAdmMedicamentos').val()
				let complementoMedicamentos = $('#complementoMedicamentos').val()
				let descricaoPosologiaMedicamentos = $('#descricaoPosologiaMedicamentos').val()
				let validadeInicioMedicamentos = $('#validadeInicioMedicamentos').val()
				let validadeFimMedicamentos = $('#validadeFimMedicamentos').val()

				checkBombaInfusaoMedicamentos = checkBombaInfusaoMedicamentos == true ? 1 : 0;
				checkInicioAdmMedicamentos = checkInicioAdmMedicamentos == true ? 1 : 0;

				if (medicamentoEstoqueMedicamentos == '' && medicamentoDlMedicamentos == '') {
					msg = 'Informe um medicamento no campo livre ou pesquise um medicamento do estoque!';
					alerta('Campo Obrigatório!', msg, 'error')
					$('#medicamentoDlMedicamentos').focus();return					
				}

				if (checkInicioAdmMedicamentos == 1 && horaInicioAdmMedicamentos == '') {

					msg = 'informe a hora do Início ADM, ou desmarque a opção!';
					alerta('Campo Obrigatório!', msg, 'error')
					$('#horaInicioAdmMedicamentos').focus();return	
					
				}

				switch(msg){
					case selViaMedicamentos: msg = 'Informe a via de administração do medicamento!';$('#selViaMedicamentos').focus();break
					case doseMedicamentos: msg = 'Informe a dose do medicamento';$('#doseMedicamentos').focus();break
					case selUnidadeMedicamentos: msg = 'Informe a unidade do medicamento!';$('#selUnidadeMedicamentos').focus();break
					case frequenciaMedicamentos: msg = 'Informe a frequência de administração do medicamento!';$('#frequenciaMedicamentos').focus();break
					case selTipoAprazamentoMedicamentos: msg = 'Informe o tipo de aprazamento do medicamento!';$('#selTipoAprazamentoMedicamentos').focus();break
					case descricaoPosologiaMedicamentos: msg = 'Informe a posologia da administração do medicamento!';$('#descricaoPosologiaMedicamentos').focus();break
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
						'tipoRequest': 'EDITARMEDICAMENTO',
						'idMedicamentos' : idMedicamentos,
						'iAtendimentoId' : <?php echo $iAtendimentoId; ?>,
						'profissional' : <?php echo $userId; ?> ,
						'tipo' : 'M',
						'medicamentoEstoqueMedicamentos' : medicamentoEstoqueMedicamentos,
						'medicamentoDlMedicamentos' : medicamentoDlMedicamentos,
						'selViaMedicamentos' : selViaMedicamentos,
						'doseMedicamentos' : doseMedicamentos,
						'selUnidadeMedicamentos' : selUnidadeMedicamentos,
						'frequenciaMedicamentos' : frequenciaMedicamentos,
						'selTipoAprazamentoMedicamentos' : selTipoAprazamentoMedicamentos,
						'dataInicioMedicamentos' : dataInicioMedicamentos,
						'checkBombaInfusaoMedicamentos' : checkBombaInfusaoMedicamentos,
						'checkInicioAdmMedicamentos' : checkInicioAdmMedicamentos,
						'horaInicioAdmMedicamentos' : horaInicioAdmMedicamentos,
						'complementoMedicamentos' : complementoMedicamentos,
						'descricaoPosologiaMedicamentos' : descricaoPosologiaMedicamentos,	
						'validadeInicioMedicamentos' : validadeInicioMedicamentos,
						'validadeFimMedicamentos' : validadeFimMedicamentos	
					},
					success: function(response) {
						if(response.status == 'success'){
							alerta(response.titulo, response.menssagem, response.status)
							$("#adicionarMedicamento").css('display', 'block');
							$("#salvarEdMedicamento").css('display', 'none');
							zerarMedicamento()
							getMedicamentosSolucoes()
						}else{
							alerta(response.titulo, response.menssagem, response.status)
						}
					}
				});

			})

			$('#salvarEdSolucao').on('click', function (e) {

				e.preventDefault();

				let msg = ''

				let idSolucoes = $('#idSolucoes').val()
				let medicamentoEstoqueSolucoes = $('#medicamentoEstoqueSolucoes').val()
				let medicamentoDlSolucoes = $('#medicamentoDlSolucoes').val()
				let selViaSolucoes = $('#selViaSolucoes').val()
				let doseSolucoes = $('#doseSolucoes').val()
				let selUnidadeSolucoes = $('#selUnidadeSolucoes').val()
				let frequenciaSolucoes = $('#frequenciaSolucoes').val()
				let selTipoAprazamentoSolucoes = $('#selTipoAprazamentoSolucoes').val()
				let dataInicioSolucoes = $('#dataInicioSolucoes').val()
				let diluenteSolucoes = $('#diluenteSolucoes').val()
				let volumeSolucoes = $('#volumeSolucoes').val()
				let correrEmSolucoes = $('#correrEmSolucoes').val()
				let selUnTempoSolucoes = $('#selUnTempoSolucoes').val()
				let velocidadeInfusaoSolucoes = $('#velocidadeInfusaoSolucoes').val()
				let checkBombaInfusaoSolucoes = $('#checkBombaInfusaoSolucoes').is(':checked');
				let checkInicioAdmSolucoes = $('#checkInicioAdmSolucoes').is(':checked');
				let horaInicioAdmSolucoes = $('#horaInicioAdmSolucoes').val()
				let complementoSolucoes = $('#complementoSolucoes').val()
				let descricaoPosologiaSolucoes = $('#descricaoPosologiaSolucoes').val()
				let validadeInicioSolucoes = $('#validadeInicioSolucoes').val()
				let validadeFimSolucoes = $('#validadeFimSolucoes').val()

				checkBombaInfusaoSolucoes = checkBombaInfusaoSolucoes == true ? 1 : 0;
				checkInicioAdmSolucoes = checkInicioAdmSolucoes == true ? 1 : 0;


				if (medicamentoEstoqueSolucoes == '' && medicamentoDlSolucoes == '') {
					msg = 'Informe um medicamento no campo livre ou pesquise um medicamento do estoque!';
					alerta('Campo Obrigatório!', msg, 'error')
					$('#medicamentoDlSolucoes').focus();return					
				}

				if (checkInicioAdmSolucoes == 1 && horaInicioAdmSolucoes == '') {

					msg = 'informe a hora do Início ADM, ou desmarque a opção!';
					alerta('Campo Obrigatório!', msg, 'error')
					$('#horaInicioAdmSolucoes').focus();return	

				}

				switch(msg){
					case selViaSolucoes: msg = 'Informe a via de administração do medicamento!';$('#selViaSolucoes').focus();break
					case doseSolucoes: msg = 'Informe a dose do medicamento';$('#doseSolucoes').focus();break
					case selUnidadeSolucoes: msg = 'Informe a unidade do medicamento!';$('#selUnidadeSolucoes').focus();break
					case frequenciaSolucoes: msg = 'Informe a frequência de administração do medicamento!';$('#frequenciaSolucoes').focus();break
					case selTipoAprazamentoSolucoes: msg = 'Informe o tipo de aprazamento do medicamento!';$('#selTipoAprazamentoSolucoes').focus();break
					case descricaoPosologiaSolucoes: msg = 'Informe a posologia da administração do medicamento!';$('#descricaoPosologiaSolucoes').focus();break
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
						'tipoRequest': 'EDITARSOLUCAO',
						'idSolucoes' : idSolucoes,
						'iAtendimentoId' : <?php echo $iAtendimentoId; ?>,
						'profissional' : <?php echo $userId; ?> ,
						'tipo' : 'S',
						'medicamentoEstoqueSolucoes' : medicamentoEstoqueSolucoes,
						'medicamentoDlSolucoes' : medicamentoDlSolucoes,
						'selViaSolucoes' : selViaSolucoes,
						'doseSolucoes' : doseSolucoes,
						'selUnidadeSolucoes' : selUnidadeSolucoes,
						'frequenciaSolucoes' : frequenciaSolucoes,
						'selTipoAprazamentoSolucoes' : selTipoAprazamentoSolucoes,
						'dataInicioSolucoes' : dataInicioSolucoes,
						'diluenteSolucoes' : diluenteSolucoes,
						'volumeSolucoes' : volumeSolucoes,
						'correrEmSolucoes' : correrEmSolucoes,
						'selUnTempoSolucoes' : selUnTempoSolucoes,
						'velocidadeInfusaoSolucoes' : velocidadeInfusaoSolucoes,
						'checkBombaInfusaoSolucoes' : checkBombaInfusaoSolucoes,
						'checkInicioAdmSolucoes' : checkInicioAdmSolucoes,
						'horaInicioAdmSolucoes' : horaInicioAdmSolucoes,
						'complementoSolucoes' : complementoSolucoes,
						'descricaoPosologiaSolucoes' : descricaoPosologiaSolucoes,
						'validadeInicioSolucoes' : validadeInicioSolucoes,
						'validadeFimSolucoes' : validadeFimSolucoes
						
					},
					success: function(response) {
						if(response.status == 'success'){
							alerta(response.titulo, response.menssagem, response.status)
							$("#adicionarSolucao").css('display', 'block');
							$("#salvarEdSolucao").css('display', 'none');
							zerarSolucao()
							getMedicamentosSolucoes()
						}else{
							alerta(response.titulo, response.menssagem, response.status)
						}
					}
				});

			})
			
			$('#salvarEdDieta').on('click', function (e) {

				e.preventDefault();

				let msg = ''

				let idDieta = $('#idDieta').val()
				let dataInicialDieta = $('#dataInicialDieta').val()
				let dataFinalDieta = $('#dataFinalDieta').val()
				let selTipoDeDieta = $('#selTipoDeDieta').val()
				let selViaDieta = $('#selViaDieta').val()
				let freqDieta = $('#freqDieta').val()
				let selTipoAprazamentoDieta = $('#selTipoAprazamentoDieta').val()
				let checkBombaInfusaoDieta = $('#checkBombaInfusaoDieta').val()
				let descricaoDieta = $('#descricaoDieta').val()
				
				if (document.getElementById('checkBombaInfusaoDieta').checked) {
					checkBombaInfusaoDieta = 1;
				}else {
					checkBombaInfusaoDieta = 0;
				}

				switch(msg){
					case dataInicialDieta: msg = 'Informe a data inicial da dieta!';$('#dataInicialDieta').focus();break
					case selTipoDeDieta: msg = 'Informe o tipo de dieta!';$('#selTipoDeDieta').focus();break
					case freqDieta: msg = 'Informe a frequência da administração da dieta!';$('#freqDieta').focus();break
					case selTipoAprazamentoDieta: msg = 'Informe o tipo de aprazamentos da dieta!';$('#selTipoAprazamentoDieta').focus();break
					case descricaoDieta: msg = 'Informe a descrição da dieta!';$('#descricaoDieta').focus();break
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
						'tipoRequest': 'EDITARDIETA',
						'idDieta' : idDieta,
						'iAtendimentoId' : <?php echo $iAtendimentoId; ?>,
						'profissional' : <?php echo $userId; ?> ,
						'dataInicialDieta' : dataInicialDieta,
						'dataFinalDieta' : dataFinalDieta,
						'selTipoDeDieta' : selTipoDeDieta,
						'selViaDieta' : selViaDieta,
						'freqDieta' : freqDieta,
						'selTipoAprazamentoDieta' : selTipoAprazamentoDieta,
						'checkBombaInfusaoDieta' : checkBombaInfusaoDieta,
						'descricaoDieta' : descricaoDieta						
					},
					success: function(response) {
						if(response.status == 'success'){
							alerta(response.titulo, response.menssagem, response.status)
							$("#adicionarDieta").css('display', 'block');
							$("#salvarEdDieta").css('display', 'none');
							zerarDieta()
							getDietas()
						}else{
							alerta(response.titulo, response.menssagem, response.status)
						}
					}
				});

			})
			
			$('#salvarEdCuidado').on('click', function (e) {

				e.preventDefault();

				let msg = ''
				let idCuidado = $('#idCuidado').val()
				let snCuidado = 0
				let dataInicialCuidados = $('#dataInicialCuidados').val()
				let dataFinalCuidados = $('#dataFinalCuidados').val()
				let selTipoDeCuidado = $('#selTipoDeCuidado').val()
				let frequenciaCuidados = $('#frequenciaCuidados').val()
				let selTipoAprazamentoCuidados = $('#selTipoAprazamentoCuidados').val()
				let complementoCuidados = $('#complementoCuidados').val()
				let descricaoCuidados = $('#descricaoCuidados').val()
					
				if ($('#snCuidados').prop("checked") == true) {
					snCuidado = 1
				}
				
				switch(msg){
					case dataInicialCuidados: msg = 'Informe a data inicial dos cuidados!';$('#dataInicialCuidados').focus();break
					case selTipoDeCuidado: msg = 'Informe o tipo de cuidado!';$('#selTipoDeCuidado').focus();break
					case frequenciaCuidados: msg = 'Informe a frequência de aplicação dos cuidados!';$('#frequenciaCuidados').focus();break
					case selTipoAprazamentoCuidados: msg = 'Informe o tipo de aprazamento dos cuidados!';$('#selTipoAprazamentoCuidados').focus();break
					case descricaoCuidados: msg = 'Informe a descrição dos cuidados!';$('#descricaoCuidados').focus();break
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
						'tipoRequest': 'EDITARCUIDADO',
						'idCuidado' : idCuidado,
						'iAtendimentoId' : <?php echo $iAtendimentoId; ?>,
						'profissional' : <?php echo $userId; ?> ,
						'dataInicialCuidados' : dataInicialCuidados,
						'dataFinalCuidados' : dataFinalCuidados,
						'selTipoDeCuidado' : selTipoDeCuidado,
						'frequenciaCuidados' : frequenciaCuidados,
						'selTipoAprazamentoCuidados' : selTipoAprazamentoCuidados,
						'snCuidado' : snCuidado,
						'complementoCuidados' : complementoCuidados,
						'descricaoCuidados' : descricaoCuidados,						
					},
					success: function(response) {
						if(response.status == 'success'){
							alerta(response.titulo, response.menssagem, response.status)
							$("#adicionarCuidado").css('display', 'block');
							$("#salvarEdCuidado").css('display', 'none');
							zerarCuidado()
							getCuidados()
						}else{
							alerta(response.titulo, response.menssagem, response.status)
						}						
					}
				});

			})

			$('#salvarEvolucaoPrescricao').on('click', function (e) {

				e.preventDefault();

				$.ajax({
					type: 'POST',
					url: 'filtraAtendimentoObservacaoHospitalar.php',
					dataType: 'json',
					data: {
						'tipoRequest': 'SALVAREVOLUCAOPRESCRICAO',
						'iAtendimentoId' : <?php echo $iAtendimentoId; ?>,					
					},
					success: function(response) {
						if(response.status == 'success'){
							alerta(response.titulo, response.menssagem, response.status)
							getEvolucaoDiaria()
							getMedicamentosSolucoes()
							getDietas()
							getCuidados()
						}else{
							alerta(response.titulo, response.menssagem, response.status)
						}						
					}
				});

				
			})

			$("#snMedicamentos").change(function() {
				if ($(this).prop("checked") == true) {
					$('#complementoMedicamentos').attr("disabled", false);	
					$('#complementoMedicamentos').focus();
				} else{
					$('#complementoMedicamentos').attr("disabled", true);
				}
			});

			$("#snSolucoes").change(function() {
				if ($(this).prop("checked") == true) {
					$('#complementoSolucoes').attr("disabled", false);	
					$('#complementoSolucoes').focus();
				} else{
					$('#complementoSolucoes').attr("disabled", true);
				}
			});

			$("#snCuidados").change(function() {
				if ($(this).prop("checked") == true) {
					$('#complementoCuidados').attr("disabled", false);	
					$('#complementoCuidados').focus();
				} else{
					$('#complementoCuidados').attr("disabled", true);
				}
			});

			$('#selTipoDeDieta').on('change', function(e){
				
				let selTipoDeDieta = $('#selTipoDeDieta').val()

				$.ajax({
					type: 'POST',
					url: 'filtraAtendimentoObservacaoHospitalar.php',
					dataType: 'json',
					data:{
						'tipoRequest': 'FILTRAVIADIETA',
						'tipoDieta' : selTipoDeDieta
					},
					success: function(response) {

						if (response.length !== 0) {
							$('#selViaDieta').empty();
							$('#selViaDieta').append(`<option value=''>Selecione</option>`)
							response.forEach(item => {

								let opt = `<option value="${item.id}">${item.nome}</option>`
								$('#selViaDieta').append(opt)
							})			
						}				
					}
				});

			})

			$('#salvarObservacaoEntrada').on('click', function (e) {
				e.preventDefault();

				let msg = ''
				let historiaEntrada = $('#historiaEntrada').val()
				let exameFisico = $('#exameFisico').val()
				let hipoteseDiagnostica = $('#hipoteseDiagnostica').val()
				let anamnese = $('#anamnese').val()
				let caraterInternacao = $('#caraterInternacao').val()
				let cid10 = $('#cid10').val()				
				let servico = $('#servico').val()

				switch(msg){
					case historiaEntrada: msg = 'Informe o texto da História de entrada!';$('#historiaEntrada').focus();break
					case historiaEntrada: msg = 'Informe o texto da História de Moléstia Atual!';$('#historiaEntrada').focus();break
					case exameFisico: msg = 'Informe o texto do Exame Físico!';$('#exameFisico').focus();break
					case hipoteseDiagnostica: msg = 'Informe o texto da Hipótese Diagnóstica!';$('#hipoteseDiagnostica').focus();break
					case cid10: msg = 'Informe o CID-10!';$('#cid10').focus();break
					case servico: msg = 'Informe o Procedimento!';$('#servico').focus();break
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
						'tipoRequest': 'SALVAROBSERVACAOENTRADA',
						'iAtendimentoId' : <?php echo $iAtendimentoId; ?>,
						'profissional' : <?php echo $userId; ?> ,
						'historiaEntrada' : historiaEntrada,
						'exameFisico' : exameFisico,				
						'hipoteseDiagnostica' : hipoteseDiagnostica,				
						'anamnese' : anamnese,				
						'caraterInternacao' : caraterInternacao,				
						'cid10' : cid10,						
						'servico' : servico						
					},
					success: function(response) {
						if(response.status == 'success'){							
							alerta(response.titulo, response.menssagem, response.status)
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

						let situaChave = $("#atendimentoSituaChave").val();
						let copiar = `<a class='list-icons-item mr-2 ' style='color: black; cursor:pointer' onclick='copiarEvolucao (\"${item.evolucaoCompl}\")'><i class='icon-files-empty' title='Copiar Evolução'></i></a>`;
						let editar = `<a class='list-icons-item mr-2 ' style='color: black; cursor:pointer'  onclick='editarEvolucao(\"${item.id}\")' class='list-icons-item' ><i class='icon-pencil7' title='Editar Evolução'></i></a>`;
						let exc = `<a style='color: black; cursor:pointer' onclick='excluirEvolucao(\"${item.id}\")' class='list-icons-item'><i class='icon-bin' title='Excluir Evolução'></i></a>`;
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
							<td class="text-left" title="${item.evolucaoCompl}">${item.evolucao}</td>
							<td class="text-left">${item.profissionalCbo}</td>
							<td class="text-center">${acoes}</td>
						</tr>`

					})
					$('#dataEvolucaoDiaria').html(HTML).show();
				}
			});	

		}

		function getMedicamentosSolucoes() {

			$.ajax({
				type: 'POST',
				url: 'filtraAtendimentoObservacaoHospitalar.php',
				dataType: 'json',
				data:{
					'tipoRequest': 'MEDICAMENTOSSOLUCOES',
					'iAtendimentoId' : <?php echo $iAtendimentoId; ?>
				},
				success: function(response) {

					$('#dataMedicamentosSolucoes').html('');
					let HTML = ''
					
					response.forEach(item => {

						let situaChave = $("#atendimentoSituaChave").val();
						let editar = `<a class='list-icons-item mr-2 ' style='color: black; cursor:pointer'  onclick='editarMedicamento(\"${item.id}\", \"${item.tipo}\")' class='list-icons-item' ><i class='icon-pencil7' title='Editar Medicamento'></i></a>`;
						let exc = `<a style='color: black; cursor:pointer' onclick='excluirMedicamento(\"${item.id}\", \"${item.tipo}\")' class='list-icons-item'><i class='icon-bin' title='Excluir Medicamento'></i></a>`;
						let acoes = ``;
						
						if (item.editavel == 1) {							
							if (situaChave != 'ATENDIDO'){
								acoes = `<div class='list-icons'>
										${editar}
										${exc}
									</div>`;
							} else{
								acoes = `<div class='list-icons'>
                                        
								</div>`;
							}			
						}
						
						HTML += `
						<tr class='medicamentoItem'>
							<td class="text-left">${item.item}</td>
							<td class="text-left">${item.dataIniTratamento}</td>
							<td class="text-left">${item.dadosMedicamento}</td>
							<td class="text-left">${item.via}</td>
							<td class="text-left" title="${item.posologia}">${item.posologia}</td>
							<td class="text-center">${acoes}</td>
						</tr>`

					})
					$('#dataMedicamentosSolucoes').html(HTML).show();
				}
			});	
			
		}

		function getDietas() {

			$.ajax({
				type: 'POST',
				url: 'filtraAtendimentoObservacaoHospitalar.php',
				dataType: 'json',
				data:{
					'tipoRequest': 'DIETA',
					'iAtendimentoId' : <?php echo $iAtendimentoId; ?>
				},
				success: function(response) {

					$('#dataDieta').html('');
					let HTML = ''
					
					response.forEach(item => {

						let situaChave = $("#atendimentoSituaChave").val();
						let editar = `<a class='list-icons-item mr-2 ' style='color: black; cursor:pointer'  onclick='editarDieta(\"${item.id}\")' class='list-icons-item' ><i class='icon-pencil7' title='Editar Dieta'></i></a>`;
						let exc = `<a style='color: black; cursor:pointer' onclick='excluirDieta(\"${item.id}\")' class='list-icons-item'><i class='icon-bin' title='Excluir Dieta'></i></a>`;
						let acoes = ``;
						
						if (item.editavel == 1) {

							if (situaChave != 'ATENDIDO'){
								acoes = `<div class='list-icons'>
										${editar}
										${exc}
									</div>`;
							} else{
								acoes = `<div class='list-icons'>
                                        
								</div>`;
							}							
							
						}
						
						HTML += `
						<tr class='dietaItem'>
							<td class="text-left">${item.item}</td>
							<td class="text-left">${item.dataIniTratamento}</td>
							<td class="text-left">${item.tipoDieta}</td>
							<td class="text-left">${item.descricaoDieta}</td>
							<td class="text-center">${acoes}</td>
						</tr>`

					})
					$('#dataDieta').html(HTML).show();
				}
			});

		}

		function getCuidados() {

			$.ajax({
				type: 'POST',
				url: 'filtraAtendimentoObservacaoHospitalar.php',
				dataType: 'json',
				data:{
					'tipoRequest': 'CUIDADOS',
					'iAtendimentoId' : <?php echo $iAtendimentoId; ?>
				},
				success: function(response) {

					$('#dataCuidados').html('');
					let HTML = ''
					
					response.forEach(item => {

						let situaChave = $("#atendimentoSituaChave").val();
						let editar = `<a class='list-icons-item mr-2 ' style='color: black; cursor:pointer'  onclick='editarCuidado(\"${item.id}\")' class='list-icons-item' ><i class='icon-pencil7' title='Editar Cuidado'></i></a>`;
						let exc = `<a style='color: black; cursor:pointer' onclick='excluirCuidado(\"${item.id}\")' class='list-icons-item'><i class='icon-bin' title='Excluir Cuidado'></i></a>`;
						let acoes = ``;
						
						if (item.editavel == 1) {
													
							if (situaChave != 'ATENDIDO'){
								acoes = `<div class='list-icons'>
										${editar}
										${exc}
									</div>`;
							} else{
								acoes = `<div class='list-icons'>
                                        
								</div>`;
							}
						}

							
						
						HTML += `
						<tr class='cuidadoItem'>
							<td class="text-left">${item.item}</td>
							<td class="text-left">${item.dataHora}</td>
							<td class="text-left">${item.tipoCuidado}</td>
							<td class="text-left" title="${item.descricaoCuidado}">${item.descricaoCuidado}</td>
							<td class="text-center">${acoes}</td>
						</tr>`

					})
					$('#dataCuidados').html(HTML).show();
				}
			});
			
		}

		function getCmbs(){

			// vai preencher cid-10
			$.ajax({
				type: 'POST',
				url: 'filtraAtendimento.php',
				dataType: 'json',
				data:{
					'tipoRequest': 'CID10'
				},
				success: function(response) {
					$('#cid10').empty();
					$('#cid10').append(`<option value=''>Selecione</option>`)
				
					response.forEach(item => {
						let opt = `<option value="${item.id}">${item.codigo} - ${item.descricao}</option>`
						$('#cid10').append(opt)
					})
					
				}
			});

			// vai preencher Procedimento
			$.ajax({
				type: 'POST',
				url: 'filtraAtendimento.php',
				dataType: 'json',
				data:{
					'tipoRequest': 'SERVICOS'
				},
				success: function(response) {
					$('#servico').empty();
					$('#servico').append(`<option value=''>Selecione</option>`)
				
					response.forEach(item => {
						let opt = `<option value="${item.id}">${item.codigo} - ${item.nome}</option>`
						$('#servico').append(opt)
					})
					
				}
			});

			// vai preencher VIA
			$.ajax({
				type: 'POST',
				url: 'filtraAtendimentoObservacaoHospitalar.php',
				dataType: 'json',
				data:{
					'tipoRequest': 'VIA'
				},
				success: function(response) {
					$('#selViaSolucoes').empty();
					$('#selViaMedicamentos').empty();
					$('#selViaDieta').empty();
					$('#selViaSolucoes').append(`<option value=''>Selecione</option>`)
					$('#selViaMedicamentos').append(`<option value=''>Selecione</option>`)
					$('#selViaDieta').append(`<option value=''>Selecione</option>`)
					response.forEach(item => {

						let opt = `<option value="${item.id}">${item.nome}</option>`						
						//let opt = '<option value="' + item.id + '" ' + (item.nome == "Sonda (VS)" ? "selected" : "")  + '>' + item.nome + '</option>'

						$('#selViaSolucoes').append(opt)
						$('#selViaMedicamentos').append(opt)
						$('#selViaDieta').append(opt)
						$('#selViaMedicamentos').append(opt)
					})
				}
			});

			// vai preencher UNIDADE MEDIDA
			$.ajax({
				type: 'POST',
				url: 'filtraAtendimentoObservacaoHospitalar.php',
				dataType: 'json',
				data:{
					'tipoRequest': 'UNIDADEMEDIDA'
				},
				success: function(response) {
					$('#selUnidadeSolucoes').empty();
					$('#selUnidadeMedicamentos').empty();
					$('#selUnidadeSolucoes').append(`<option value=''>Selecione</option>`)
					$('#selUnidadeMedicamentos').append(`<option value=''>Selecione</option>`)
					response.forEach(item => {

						let opt = `<option value="${item.id}">${item.nome}</option>`						
						$('#selUnidadeSolucoes').append(opt)
						$('#selUnidadeMedicamentos').append(opt)
					})
				}
			});

			// vai preencher TIPOAPRAZAMENTO
			$.ajax({
				type: 'POST',
				url: 'filtraAtendimentoObservacaoHospitalar.php',
				dataType: 'json',
				data:{
					'tipoRequest': 'TIPOAPRAZAMENTO'
				},
				success: function(response) {
					$('#selTipoAprazamentoSolucoes').empty();
					$('#selTipoAprazamentoMedicamentos').empty();
					$('#selTipoAprazamentoDieta').empty();
					$('#selTipoAprazamentoCuidados').empty();
					$('#selTipoAprazamentoSolucoes').append(`<option value=''>Selecione</option>`)
					$('#selTipoAprazamentoMedicamentos').append(`<option value=''>Selecione</option>`)
					$('#selTipoAprazamentoDieta').append(`<option value=''>Selecione</option>`)
					$('#selTipoAprazamentoCuidados').append(`<option value=''>Selecione</option>`)
					response.forEach(item => {

						let opt = `<option value="${item.id}">${item.nome}</option>`
						$('#selTipoAprazamentoSolucoes').append(opt)
						$('#selTipoAprazamentoMedicamentos').append(opt)
						$('#selTipoAprazamentoDieta').append(opt)
						$('#selTipoAprazamentoCuidados').append(opt)
					})
				}
			});

			// vai preencher UNIDADETEMPO
			$.ajax({
				type: 'POST',
				url: 'filtraAtendimentoObservacaoHospitalar.php',
				dataType: 'json',
				data:{
					'tipoRequest': 'UNIDADETEMPO'
				},
				success: function(response) {
					$('#selUnTempoSolucoes').empty();
					$('#selUnTempoSolucoes').append(`<option value=''>Selecione</option>`)
					response.forEach(item => {

						let opt = `<option value="${item.id}">${item.nome}</option>`
						$('#selUnTempoSolucoes').append(opt)
					})
				}
			});

			// vai preencher TIPODIETA
			$.ajax({
				type: 'POST',
				url: 'filtraAtendimentoObservacaoHospitalar.php',
				dataType: 'json',
				data:{
					'tipoRequest': 'TIPODIETA'
				},
				success: function(response) {
					$('#selTipoDeDieta').empty();
					$('#selTipoDeDieta').append(`<option value=''>Selecione</option>`)
					response.forEach(item => {

						let opt = `<option value="${item.id}">${item.nome}</option>`
						$('#selTipoDeDieta').append(opt)
					})
				}
			});

			// vai preencher TIPOCUIDADO
			$.ajax({
				type: 'POST',
				url: 'filtraAtendimentoObservacaoHospitalar.php',
				dataType: 'json',
				data:{
					'tipoRequest': 'TIPOCUIDADO'
				},
				success: function(response) {
					$('#selTipoDeCuidado').empty();
					$('#selTipoDeCuidado').append(`<option value=''>Selecione</option>`)
					response.forEach(item => {

						let opt = `<option value="${item.id}">${item.nome}</option>`
						$('#selTipoDeCuidado').append(opt)
					})
				}
			});

		}

		$(function() {
			$('.btn-grid').click(function(){
				$('.btn-grid').removeClass('active');
				$(this).addClass('active');     
			});
		});

		function mudarGrid(grid){
			if (grid == 'entradaPaciente') {
				$(".box-entradaPaciente").css('display', 'block');
				$(".box-evolucao").css('display', 'none');
				$(".box-prescricao").css('display', 'none');				
			} else if (grid == 'evolucao') {	
				$(".box-entradaPaciente").css('display', 'none');			
				$(".box-evolucao").css('display', 'block');
				$(".box-prescricao").css('display', 'none');
			} else if (grid == 'prescricao') {
				$(".box-entradaPaciente").css('display', 'none');
				$(".box-prescricao").css('display', 'block');
				$(".box-evolucao").css('display', 'none');
			}
		}

		function pesquisarMedicamento(tipo) {

			$.ajax({
				type: 'POST',
				url: 'filtraAtendimento.php',
				dataType: 'json',
				data:{
					'tipoRequest': 'SETTIPOBUSCAMEDICAMENTO',
					'tipoBusca': tipo,
				},
				success: function(response) {
					if (response.status == 'success') {						
						if (window.location.host == 'localhost' || window.location.host == '127.0.0.1' ) {							
							window.open('/lamparinas/sistema/atendimentoProdutos.php',	'Janela','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,width=1000,height=450,left=25,top=25'); 							
						} else {
							window.open('/sistema/atendimentoProdutos.php',	'Janela','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,width=1000,height=450,left=25,top=25'); 							
						}
					}
				}
			});

		}

		function setDescricaoPosologiaMed() {
			let newText = ''
			let arrayCampos = [
				$('#nomeMedicamentoEstoqueMedicamentos').val(),
				$( "#selViaMedicamentos" ).val()?$( "#selViaMedicamentos option:selected" ).text():'',
				$('#doseMedicamentos').val(),
				$('#medicamentoDlMedicamentos').val(),
				$('#selUnidadeMedicamentos').val()?$( "#selUnidadeMedicamentos option:selected" ).text():'',
				$('#frequenciaMedicamentos').val(),
				$( "#selTipoAprazamentoMedicamentos" ).val()?$('#selTipoAprazamentoMedicamentos option:selected').text():'',
				$('#checkBombaInfusaoMedicamentos').is(':checked')?'Bomba de Infusão':'',
				$('#complementoMedicamentos').val()
			]
			arrayCampos.forEach(function(item){
				newText += item?` ${item} -`:''
			})
			newText = newText.slice(0, -1)
			
			$('#descricaoPosologiaMedicamentos').val(newText)
			contarCaracteres($('#descricaoPosologiaMedicamentos')[0])
			
		}

		function setDescricaoPosologiaSol() {

			let nomeSolucao = $('#nomeMedicamentoEstoqueSolucoes').val()
			let solucaoDl = $('#medicamentoDlSolucoes').val()			
			let doseSol = $('#doseSolucoes').val()
			let unidadeSol = $('#selUnidadeSolucoes option:selected').text()
			let frequenciaSol = $('#frequenciaSolucoes').val()
			let tipoAprazemtentoSol = $('#selTipoAprazamentoSolucoes option:selected').text()
			let diluenteSol = $('#nomeDiluenteSolucoes').val()
			let volumeSol = $('#volumeSolucoes').val()
			let correrEmSol = $('#correrEmSolucoes').val()
			let unidadeTempoSol = $('#selUnTempoSolucoes option:selected').text()
			let velocidadeInfSol = $('#velocidadeInfusaoSolucoes').val()
			let bombaInfusaoSol = ''
			let complementoSol = $('#complementoSolucoes').val()

			if (document.getElementById('checkBombaInfusaoSolucoes').checked) {
				bombaInfusaoSol = 'Bomba de Infusão';
			}

			$('#descricaoPosologiaSolucoes').val(

				(nomeSolucao != '' ? nomeSolucao : '') + 
				(solucaoDl != '' ? ' - ' + solucaoDl : '') + 
				(doseSol != '' ? ' - ' + doseSol : '') + 
				(unidadeSol != 'Selecione' ? ' - ' + unidadeSol : '') +
				(frequenciaSol != '' ? ' - ' + frequenciaSol : '') +
				(tipoAprazemtentoSol != 'Selecione' ? ' - ' + tipoAprazemtentoSol : '') +	
				(diluenteSol != '' ? ' - ' + diluenteSol : '') +
				(volumeSol != '' ? ' - ' + volumeSol : '') +
				(correrEmSol != '' ? ' - ' + correrEmSol : '') +
				(unidadeTempoSol != 'Selecione' ? ' - ' + unidadeTempoSol : '') +	
				(velocidadeInfSol != '' ? ' - ' + velocidadeInfSol : '') +						
				(bombaInfusaoSol != '' ? ' - ' + bombaInfusaoSol : '') +			
				(complementoSol != '' ? ' - ' + complementoSol : '')
				
			)
			contarCaracteres($('#descricaoPosologiaSolucoes')[0])
			
		}

		function setDescricaoDieta() {

			let tipoDieta = $('#selTipoDeDieta option:selected').text();
			let frequenciaDieta = $('#freqDieta').val();
			let aprazamentoDieta = $('#selTipoAprazamentoDieta option:selected').text();		
			let bombaInfusaoDieta = '';

			if (document.getElementById('checkBombaInfusaoDieta').checked) {
				bombaInfusaoDieta = 'Bomba de Infusão';
			}

			$('#descricaoDieta').val(
				(tipoDieta != 'Selecione' ? tipoDieta : '') +  
				(frequenciaDieta != '' ? ' - ' + frequenciaDieta : '') + 
				(aprazamentoDieta != 'Selecione' ?  ' - ' + aprazamentoDieta : '') +
				(bombaInfusaoDieta != '' ? ' - ' + bombaInfusaoDieta : '')
			)
			contarCaracteres($('#descricaoDieta')[0])
		}

		function setDescricaoCuidados() {

			let tipoCuidado = $('#selTipoDeCuidado option:selected').text();
			let frequenciaCuidado = $('#frequenciaCuidados').val();
			let aprazamentoCuidado = $('#selTipoAprazamentoCuidados option:selected').text();
			let complementoCuidado = $('#complementoCuidados').val();	

			$('#descricaoCuidados').val(
				(tipoCuidado != 'Selecione' ? tipoCuidado : '') + 
				(frequenciaCuidado != '' ? ' - ' + frequenciaCuidado : '') + 
				(aprazamentoCuidado != 'Selecione' ?  ' - ' + aprazamentoCuidado : '') +
				(complementoCuidado != '' ? ' - ' + complementoCuidado : '')
			)
			contarCaracteres($('#descricaoCuidados')[0])
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

		function editarMedicamento(id, tipo) {

			$.ajax({
				type: 'POST',
				url: 'filtraAtendimentoObservacaoHospitalar.php',
				dataType: 'json',
				data:{
					'tipoRequest': 'GETMEDICAMENTO',
					'id' : id
				},
				success: function(response) {

					if (tipo == 'M') {

						$('#idMedicamentos').val(response[0].AtPMeId)
						$('#nomeMedicamentoEstoqueMedicamentos').val(response[0].ProduNome)
						$('#medicamentoEstoqueMedicamentos').val(response[0].AtPMeProdutoEmEstoque)
						$('#medicamentoDlMedicamentos').val(response[0].AtPMeProdutoLivre)
						$('#selViaMedicamentos').val(response[0].AtPMeVia).change()
						$('#doseMedicamentos').val(response[0].AtPMeDose)
						$('#selUnidadeMedicamentos').val(response[0].AtPMeUnidadeMedida).change()
						$('#frequenciaMedicamentos').val(response[0].AtPMeFrequencia)
						$('#selTipoAprazamentoMedicamentos').val(response[0].AtPMeTipoAprazamento).change()
						$('#dataInicioMedicamentos').val(response[0].AtPMeDtInicioTratamento)
						$('#horaInicioAdmMedicamentos').val((response[0].AtPMeHoraInicioAdm).split('.')[0])
						$('#complementoMedicamentos').val(response[0].AtPMeComplemento)
						$('#descricaoPosologiaMedicamentos').val(response[0].AtPMePosologia)
						$('#checkBombaInfusaoMedicamentos').prop('checked', response[0].AtPMeBombaInfusao == 1 ? true : false);
						$('#checkInicioAdmMedicamentos').prop('checked', response[0].AtPMeInicioAdm == 1 ? true : false);
						$('#validadeInicioMedicamentos').val(response[0].AtPMeValidadeInicio)
						$('#validadeFimMedicamentos').val(response[0].AtPMeValidadeFim)

						$("#adicionarMedicamento").css('display', 'none');
						$("#salvarEdMedicamento").css('display', 'block');
						
						$('#nomeMedicamentoEstoqueMedicamentos').focus()
								
					} else if (tipo == 'S') {

						$('#idSolucoes').val(response[0].AtPMeId)
						$('#nomeMedicamentoEstoqueSolucoes').val(response[0].ProduNome)
						$('#medicamentoEstoqueSolucoes').val(response[0].AtPMeProdutoEmEstoque)
						$('#medicamentoDlSolucoes').val(response[0].AtPMeProdutoLivre)
						$('#selViaSolucoes').val(response[0].AtPMeVia).change()
						$('#doseSolucoes').val(response[0].AtPMeDose)
						$('#selUnidadeSolucoes').val(response[0].AtPMeUnidadeMedida).change()
						$('#frequenciaSolucoes').val(response[0].AtPMeFrequencia)
						$('#selTipoAprazamentoSolucoes').val(response[0].AtPMeTipoAprazamento).change()
						$('#dataInicioSolucoes').val(response[0].AtPMeDtInicioTratamento)
						$('#nomeDiluenteSolucoes').val(response[1].NomeDiluente)
						$('#diluenteSolucoes').val(response[0].AtPMeDiluente)
						$('#volumeSolucoes').val(response[0].AtPMeVolume)
						$('#correrEmSolucoes').val(response[0].AtPMeCorrerEm)
						$('#selUnTempoSolucoes').val(response[0].AtPMeUnidadeTempo).change()
						$('#velocidadeInfusaoSolucoes').val(response[0].AtPMeVelocidadeInfusao)
						$('#horaInicioAdmSolucoes').val((response[0].AtPMeHoraInicioAdm).split('.')[0])
						$('#complementoSolucoes').val(response[0].AtPMeComplemento)
						$('#descricaoPosologiaSolucoes').val(response[0].AtPMePosologia)						
						$('#checkBombaInfusaoSolucoes').prop('checked', response[0].AtPMeBombaInfusao == 1 ? true : false);
						$('#checkInicioAdmSolucoes').prop('checked', response[0].AtPMeInicioAdm == 1 ? true : false);
						$('#validadeInicioSolucoes').val(response[0].AtPMeValidadeInicio)
						$('#validadeFimSolucoes').val(response[0].AtPMeValidadeFim)
						
						$("#adicionarSolucao").css('display', 'none');
						$("#salvarEdSolucao").css('display', 'block');
					
						$('#nomeMedicamentoEstoqueSolucoes').focus()
						
					}			
					
				}
			});
			
		}

		function editarDieta(id) {

			$.ajax({
				type: 'POST',
				url: 'filtraAtendimentoObservacaoHospitalar.php',
				dataType: 'json',
				data:{
					'tipoRequest': 'GETDIETA',
					'id' : id
				},
				success: function(response) {

					$('#idDieta').val(response.AtPDiId)
					$('#dataInicialDieta').val(response.AtPDiDataInicioDieta)
					$('#dataFinalDieta').val(response.AtPDiDataFimDieta)
					$('#selTipoDeDieta').val(response.AtPDiTipoDieta).change()
					$('#selViaDieta').val(response.AtPDiVia).change()
					$('#freqDieta').val(response.AtPDiFrequencia)
					$('#selTipoAprazamentoDieta').val(response.AtPDiTipoAprazamento).change()
					$('#descricaoDieta').val(response.AtPDiDescricaoDieta)				
					$('#checkBombaInfusaoDieta').prop('checked', response.AtPDiBombaInfusao == 1 ? true : false)		
					$("#adicionarDieta").css('display', 'none');
					$("#salvarEdDieta").css('display', 'block');
					
				}
			});
			
		}

		function editarCuidado(id) {

			$.ajax({
				type: 'POST',
				url: 'filtraAtendimentoObservacaoHospitalar.php',
				dataType: 'json',
				data:{
					'tipoRequest': 'GETCUIDADO',
					'id' : id
				},
				success: function(response) {

					$('#idCuidado').val(response.AtPCuId)					
					$('#dataInicialCuidados').val(response.AtPCuDataInicioCuidado)
					$('#dataFinalCuidados').val(response.AtPCuDataFimCuidado)
					$('#selTipoDeCuidado').val(response.AtPCuTipoCuidado).change()
					$('#frequenciaCuidados').val(response.AtPCuFrequencia)
					$('#selTipoAprazamentoCuidados').val(response.AtPCuTipoAprazamento).change()
					$('#complementoCuidados').val(response.AtPCuComplemento)
					$('#descricaoCuidados').val(response.AtPCuDescricaoCuidado)

					if (response.AtPCuSn == 1) {
						$('#snCuidados').prop('checked', true)	
						$('#complementoCuidados').prop("disabled", false); 					
					}

					$("#adicionarCuidado").css('display', 'none');
					$("#salvarEdCuidado").css('display', 'block');
					
				}
			});
			
		}

		function excluirEvolucao(id) {
			confirmaExclusaoAjax('filtraAtendimentoObservacaoHospitalar.php', 'Excluir Evolução?', 'DELETEEVOLUCAO', id, getEvolucaoDiaria)
		}

		function excluirMedicamento(id, tipo) {
			confirmaExclusaoAjax('filtraAtendimentoObservacaoHospitalar.php', 'Excluir Prescrição de Medicamento?', 'DELETEMEDICAMENTO', id, getMedicamentosSolucoes)	
		}

		function excluirDieta(id) {
			confirmaExclusaoAjax('filtraAtendimentoObservacaoHospitalar.php', 'Excluir Prescrição de Dieta?', 'DELETEDIETA', id, getDietas)	
		}

		function excluirCuidado(id) {
			confirmaExclusaoAjax('filtraAtendimentoObservacaoHospitalar.php', 'Excluir Prescrição de Cuidado?', 'DELETECUIDADO', id, getCuidados)	
		}

		function zerarEvolucao() {

			$('#idEvolucao').val("")
			$('#evolucaoDiaria').val("")
		}

		function zerarMedicamento() {

			$('#idMedicamentos').val("")
			$('#nomeMedicamentoEstoqueMedicamentos').val("")
			$('#medicamentoEstoqueMedicamentos').val("")
			$('#medicamentoDlMedicamentos').val("")
			$('#selViaMedicamentos').val("").change()
			$('#doseMedicamentos').val("")
			$('#selUnidadeMedicamentos').val("").change()
			$('#frequenciaMedicamentos').val("")
			$('#selTipoAprazamentoMedicamentos').val("").change()
			$('#dataInicioMedicamentos').val("")
			$('#horaInicioAdmMedicamentos').val("").change()
			$('#complementoMedicamentos').val("")
			$('#descricaoPosologiaMedicamentos').val("")
			$('#checkBombaInfusaoMedicamentos').prop('checked', false)
			$('#checkInicioAdmMedicamentos').prop('checked', false)
			$('#snMedicamentos').prop('checked', false)	
			$('#complementoMedicamentos').prop("disabled", true);
			$('#validadeInicioMedicamentos').val("")
			$('#validadeFimMedicamentos').val("")
		}

		function zerarSolucao() {

			$('#idSolucoes').val("")
			$('#nomeMedicamentoEstoqueSolucoes').val("")
			$('#medicamentoEstoqueSolucoes').val("")
			$('#medicamentoDlSolucoes').val("")
			$('#selViaSolucoes').val("").change()
			$('#doseSolucoes').val("")
			$('#selUnidadeSolucoes').val("").change()
			$('#frequenciaSolucoes').val("")
			$('#selTipoAprazamentoSolucoes').val("").change()
			$('#dataInicioSolucoes').val("")
			$('#nomeDiluenteSolucoes').val("")
			$('#diluenteSolucoes').val("")
			$('#volumeSolucoes').val("")
			$('#correrEmSolucoes').val("")
			$('#selUnTempoSolucoes').val("").change()
			$('#velocidadeInfusaoSolucoes').val("")
			$('#horaInicioAdmSolucoes').val("")
			$('#complementoSolucoes').val("")
			$('#descricaoPosologiaSolucoes').val("")						
			$('#checkBombaInfusaoSolucoes').prop('checked', false);
			$('#checkInicioAdmSolucoes').prop('checked', false);
			$('#snSolucoes').prop('checked', false)	
			$('#complementoSolucoes').prop("disabled", true);
			$('#validadeInicioSolucoes').val("")
			$('#validadeFimSolucoes').val("")
			
		}

		function zerarDieta() {

			$('#idDieta').val("")
			$('#dataInicialDieta').val("")
			$('#dataFinalDieta').val("")
			$('#selTipoDeDieta').val("").change()
			$('#selViaDieta').val("").change()
			$('#freqDieta').val("")
			$('#selTipoAprazamentoDieta').val("").change()
			$('#descricaoDieta').val("")				
			$('#checkBombaInfusaoDieta').prop('checked', false)
			
		}

		function zerarCuidado() {

			$('#idCuidado').val("")					
			$('#dataInicialCuidados').val("")
			$('#dataFinalCuidados').val("")
			$('#selTipoDeCuidado').val("").change()
			$('#frequenciaCuidados').val("")
			$('#selTipoAprazamentoCuidados').val("").change()
			$('#complementoCuidados').val("")
			$('#descricaoCuidados').val("")			
			$('#snCuidados').prop('checked', false)	
			$('#complementoCuidados').prop("disabled", true); 				
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
							echo "<input type='hidden' id='atendimentoSituaChave' value='".$_SESSION['SituaChave']."' />";
						?>
						<div class="card">
							<div class="card-header header-elements-inline">
								<h3 class="card-title"><b>OBSERVAÇÃO HOSPITALAR</b></h3>
							</div>
						</div>

						<div> <?php include ('atendimentoDadosPaciente.php'); ?> </div>

						<div class="box-evolucao" style="display : <?php echo isset($_POST['screen']) ? ($_POST['screen'] == 'activeEvolucaoMedica' ? 'block' : 'none' ) : 'none'; ?>;" >  
							<?php include ('atendimentoDadosSinaisVitais.php'); ?> 
						</div>

						<div class="card">
							<div class="card-header header-elements-inline">
								<div class="col-lg-11">	
									<button type="button" id="entradaPaciente-btn" class="btn-grid btn btn-lg btn-outline-secondary btn-lg mr-2 <?php echo isset($_POST['screen']) ? ($_POST['screen'] == 'activeEntrada' ? 'active' : '' ) : ''; ?> " onclick="mudarGrid('entradaPaciente')" style="margin-left: -10px;" >Entrada do Paciente</button>
									<button type="button" id="prescricao-btn" class="btn-grid btn btn-lg btn-outline-secondary btn-lg mr-2 <?php echo isset($_POST['screen']) ? ($_POST['screen'] == 'activePrescricao' ? 'active' : '' ) : ''; ?> " onclick="mudarGrid('prescricao')"  >Prescrição Médica</button>
									<button type="button" id="evolucao-btn" class="btn-grid btn btn-lg btn-outline-secondary btn-lg mr-2 <?php echo isset($_POST['screen']) ? ($_POST['screen'] == 'activeEvolucaoMedica' ? 'active' : '' ) : ''; ?> " onclick="mudarGrid('evolucao')" >Evolução Médica</button>
									<button type="button" id="evolucao-btn" class="btn-grid btn btn-lg btn-outline-secondary btn-lg itemLink " data-tipo='evolucaoEnfermagem' >Evolução de Enfermagem</button>
								</div>
							</div>								
						</div>

						<div class="box-entradaPaciente" style="display: <?php echo isset($_POST['screen']) ? ($_POST['screen'] == 'activeEntrada' ? 'block' : 'none' ) : 'none'; ?>;">
							<?php include_once("boxEntradaPaciente.php"); ?>
						</div>
						
						<div class="box-prescricao" style="display: <?php echo isset($_POST['screen']) ? ($_POST['screen'] == 'activePrescricao' ? 'block' : 'none' ) : 'none'; ?>;">
							<?php include_once("boxPrescricaoObservacao.php"); ?>					
						</div>
						<div class="box-evolucao" style="display : <?php echo isset($_POST['screen']) ? ($_POST['screen'] == 'activeEvolucaoMedica' ? 'block' : 'none' ) : 'none'; ?>;">
							<?php include_once("boxEvolucaoObservacao.php"); ?>
						</div>   

						<div class="card " style="padding: 15px">
                            <div class="col-md-12">
                                <div class="row">                                    
                                    <div class="col-md-10" style="text-align: left;">
										<?php 
											if (isset($_SESSION['SituaChave']) && $_SESSION['SituaChave'] != "ATENDIDO") {
												echo " <button type='button' class='btn btn-lg btn-success mr-1' id='salvarEvolucaoPrescricao'>Salvar</button>";
											}
										?>
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