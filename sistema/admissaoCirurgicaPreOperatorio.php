<?php 

    include_once("sessao.php"); 

    $_SESSION['PaginaAtual'] = 'Admissão Cirúrgica Pré-Operatório';

    include('global_assets/php/conexao.php');

    $iAtendimentoId = isset($_POST['iAtendimentoId'])?$_POST['iAtendimentoId']:null;

    if (isset($_SESSION['iAtendimentoId']) && $iAtendimentoId == null) {
        $iAtendimentoId = $_SESSION['iAtendimentoId'];
    }
    $_SESSION['iAtendimentoId'] = null;

    if(!$iAtendimentoId){
        irpara("atendimentoHospitalarListagem.php");	
    }

    $iUnidade = $_SESSION['UnidadeId'];

    //exame físico
    $sql = "SELECT TOP(1) *
    FROM EnfermagemAdmissaoCirurgicaPreOperatorio
    WHERE EnAdCAtendimento = $iAtendimentoId
    ORDER BY EnAdCId DESC";
    $result = $conn->query($sql);
    $rowAdmissao= $result->fetch(PDO::FETCH_ASSOC);

    $iAtendimentoCirurgicoPreOperatorio = $rowAdmissao?$rowAdmissao['EnAdCId']:null;

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
                TpIntNome, TpIntId, EsLeiNome, EsLeiId, AlaNome, AlaId, QuartNome, QuartId, LeitoNome, LeitoId
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

if(isset($_POST['inputInicio'])){

    try {

        if ($iAtendimentoCirurgicoPreOperatorio) { 

            $sql = "UPDATE EnfermagemAdmissaoCirurgicaPreOperatorio SET
                EnAdCDataInicio = :EnAdCDataInicio ,
                EnAdCHoraInicio = :EnAdCHoraInicio ,
                EnAdCDataFim = :EnAdCDataFim ,
                EnAdCHoraFim = :EnAdCHoraFim ,
                EnAdCPrevisaoAlta = :EnAdCPrevisaoAlta ,
                EnAdCTipoInternacao = :EnAdCTipoInternacao ,
                EnAdCEspecialidadeLeito = :EnAdCEspecialidadeLeito ,
                EnAdCAla = :EnAdCAla ,
                EnAdCQuarto = :EnAdCQuarto ,
                EnAdCLeito = :EnAdCLeito ,
                EnAdCProfissional = :EnAdCProfissional ,
                EnAdCPas = :EnAdCPas ,
                EnAdCPad = :EnAdCPad ,
                EnAdCFreqCardiaca = :EnAdCFreqCardiaca ,
                EnAdCFreqRespiratoria = :EnAdCFreqRespiratoria ,
                EnAdCTemperatura = :EnAdCTemperatura ,
                EnAdCSPO = :EnAdCSPO ,
                EnAdCHGT = :EnAdCHGT ,
                EnAdCPeso = :EnAdCPeso ,
                EnAdCAlergia = :EnAdCAlergia ,
                EnAdCAlergiaDescricao = :EnAdCAlergiaDescricao ,
                EnAdCUsoMedicamento = :EnAdCUsoMedicamento ,
                EnAdCUsoMedicamentoDescricao = :EnAdCUsoMedicamentoDescricao ,
                EnAdCHistCirurgiaAnterior = :EnAdCHistCirurgiaAnterior ,
                EnAdCHistCirurgiaAnteriorDescricao = :EnAdCHistCirurgiaAnteriorDescricao ,
                EnAdCJejumMinimo = :EnAdCJejumMinimo ,
                EnAdCJejumMinimoDescricao = :EnAdCJejumMinimoDescricao ,
                EnAdCUsoProtese = :EnAdCUsoProtese ,
                EnAdCUsoProteseDescricao = :EnAdCUsoProteseDescricao ,
                EnAdCRemocaoJoia = :EnAdCRemocaoJoia ,
                EnAdCRemocaoJoiaDescricao = :EnAdCRemocaoJoiaDescricao ,
                EnAdCDoencaPrevia = :EnAdCDoencaPrevia ,
                EnAdCDoencaPreviaDescricao = :EnAdCDoencaPreviaDescricao ,
                EnAdCAreaOperatoria = :EnAdCAreaOperatoria ,
                EnAdCAreaOperatoriaDescricao = :EnAdCAreaOperatoriaDescricao ,
                EnAdCLocalCirurgia = :EnAdCLocalCirurgia ,
                EnAdCLado = :EnAdCLado ,
                EnAdCEsvaziamentoVesical = :EnAdCEsvaziamentoVesical ,
                EnAdCEsvaziamentoVesicalDescricao = :EnAdCEsvaziamentoVesicalDescricao ,
                EnAdCUnidade = :EnAdCUnidade
            WHERE EnAdCId = :sAtendimentoCirurgicoPreOp";

            $result->execute(array(
                ":EnAdCDataInicio" => date('Y-m-d') ,
                ":EnAdCHoraInicio" => date('H:i') ,
                ":EnAdCDataFim" => date('Y-m-d') ,
                ":EnAdCHoraFim" => date('H:i') ,
                ":EnAdCPrevisaoAlta" => $_POST['inputPrevisaoAlta'] == "" ? null : $_POST['inputPrevisaoAlta'], 
                ":EnAdCTipoInternacao" => $_POST['inputTipoInternacao'] == "" ? null : $_POST['inputTipoInternacao'], 
                ":EnAdCEspecialidadeLeito" => $_POST['inputEspLeito'] == "" ? null : $_POST['inputEspLeito'],
                ":EnAdCAla" => $_POST['inputAla'] == "" ? null : $_POST['inputAla'], 
                ":EnAdCQuarto" => $_POST['inputQuarto'] == "" ? null : $_POST['inputQuarto'], 
                ":EnAdCLeito" => $_POST['inputLeito'] == "" ? null : $_POST['inputLeito'], 
                ":EnAdCProfissional" => $userId,
                ":EnAdCPas" => $_POST['inputSistolica'] == "" ? null : $_POST['inputSistolica'],
                ":EnAdCPad" => $_POST['inputDiatolica'] == "" ? null : $_POST['inputDiatolica'],
                ":EnAdCFreqCardiaca" =>  $_POST['inputCardiaca'] == "" ? null : $_POST['inputCardiaca'],
                ":EnAdCFreqRespiratoria" =>  $_POST['inputRespiratoria'] == "" ? null : $_POST['inputRespiratoria'],
                ":EnAdCTemperatura" =>  $_POST['inputTemperatura'] == "" ? null : $_POST['inputTemperatura'],
                ":EnAdCSPO" => $_POST['inputSPO'] == "" ? null : $_POST['inputSPO'],
                ":EnAdCHGT" =>  $_POST['inputHGT'] == "" ? null : $_POST['inputHGT'],
                ":EnAdCPeso" => $_POST['inputPeso'] == "" ? null : $_POST['inputPeso'],
                ":EnAdCAlergia" => isset($_POST['alergias']) ? $_POST['alergias'] == "" ? null : $_POST['alergias'] : null,
                ":EnAdCAlergiaDescricao" => $_POST['textAlergias'] == "" ? null : $_POST['textAlergias'],
                ":EnAdCUsoMedicamento" => isset($_POST['medicamentos']) ? $_POST['medicamentos'] == "" ? null : $_POST['medicamentos'] : null,
                ":EnAdCUsoMedicamentoDescricao" => $_POST['textMedicamentos'] == "" ? null : $_POST['textMedicamentos'],
                ":EnAdCHistCirurgiaAnterior" => isset($_POST['cirurgiaAnterior']) ? $_POST['cirurgiaAnterior'] == "" ? null : $_POST['cirurgiaAnterior'] : null ,
                ":EnAdCHistCirurgiaAnteriorDescricao" =>  $_POST['textCirurgiaAnterior'] == "" ? null : $_POST['textCirurgiaAnterior'] ,                
                ":EnAdCJejumMinimo" => isset($_POST['jejum']) ? $_POST['jejum'] == "" ? null : $_POST['jejum'] : null ,
                ":EnAdCJejumMinimoDescricao" =>  $_POST['textJejum'] == "" ? null : $_POST['textJejum'] ,                
                ":EnAdCUsoProtese" => isset($_POST['proteses']) ? $_POST['proteses'] == "" ? null : $_POST['proteses'] : null ,
                ":EnAdCUsoProteseDescricao" =>  $_POST['textProteses'] == "" ? null : $_POST['textProteses'] ,                
                ":EnAdCRemocaoJoia" => isset($_POST['acessorios']) ? $_POST['acessorios'] == "" ? null : $_POST['acessorios'] : null,
                ":EnAdCRemocaoJoiaDescricao" =>  $_POST['textAcessorios'] == "" ? null : $_POST['textAcessorios'] ,                
                ":EnAdCDoencaPrevia" =>  $_POST['doencas'] == "" ? null : $_POST['doencas'] ,
                ":EnAdCDoencaPreviaDescricao" =>  $_POST['textDoencas'] == "" ? null : $_POST['textDoencas'] ,                
                ":EnAdCAreaOperatoria" => isset($_POST['tricotomia']) ? $_POST['tricotomia'] == "" ? null : $_POST['tricotomia'] : null ,
                ":EnAdCAreaOperatoriaDescricao" =>  $_POST['textTricotomia'] == "" ? null : $_POST['textTricotomia'] ,                
                ":EnAdCLocalCirurgia" =>  $_POST['local'] == "" ? null : $_POST['local'] ,
                ":EnAdCLado" =>  $_POST['lado'] == "" ? null : $_POST['lado'] , 
                ":EnAdCEsvaziamentoVesical" => isset($_POST['esvaziamento']) ? $_POST['esvaziamento'] == "" ? null : $_POST['esvaziamento'] : null,
                ":EnAdCEsvaziamentoVesicalDescricao" =>  $_POST['textEsvaziamento'] == "" ? null : $_POST['textEsvaziamento'] ,                
                ":EnAdCUnidade" =>  $iUnidade,
                ":sAtendimentoCirurgicoPreOp" => $iAtendimentoCirurgicoPreOperatorio
            ));

            $_SESSION['msg']['titulo'] = "Sucesso";
            $_SESSION['msg']['mensagem'] = "Admissão alterada com sucesso!!!";
            $_SESSION['msg']['tipo'] = "success";

        }else {
            
            $sql = "INSERT INTO EnfermagemAdmissaoCirurgicaPreOperatorio( 
                EnAdCAtendimento,
                EnAdCDataInicio,
                EnAdCHoraInicio,
                EnAdCDataFim,
                EnAdCHoraFim,
                EnAdCPrevisaoAlta,
                EnAdCTipoInternacao,
                EnAdCEspecialidadeLeito,
                EnAdCAla,
                EnAdCQuarto,
                EnAdCLeito,
                EnAdCProfissional,
                EnAdCPas,
                EnAdCPad,
                EnAdCFreqCardiaca,
                EnAdCFreqRespiratoria,
                EnAdCTemperatura,
                EnAdCSPO,
                EnAdCHGT,
                EnAdCPeso,
                EnAdCAlergia,
                EnAdCAlergiaDescricao,
                EnAdCUsoMedicamento,
                EnAdCUsoMedicamentoDescricao,
                EnAdCHistCirurgiaAnterior,
                EnAdCHistCirurgiaAnteriorDescricao,
                EnAdCJejumMinimo,
                EnAdCJejumMinimoDescricao,
                EnAdCUsoProtese,
                EnAdCUsoProteseDescricao,
                EnAdCRemocaoJoia,
                EnAdCRemocaoJoiaDescricao,
                EnAdCDoencaPrevia,
                EnAdCDoencaPreviaDescricao,
                EnAdCAreaOperatoria,
                EnAdCAreaOperatoriaDescricao,
                EnAdCLocalCirurgia,
                EnAdCLado,
                EnAdCEsvaziamentoVesical,
                EnAdCEsvaziamentoVesicalDescricao,
                EnAdCUnidade
            ) VALUES( 
                :EnAdCAtendimento,
                :EnAdCDataInicio,
                :EnAdCHoraInicio,
                :EnAdCDataFim,
                :EnAdCHoraFim,
                :EnAdCPrevisaoAlta,
                :EnAdCTipoInternacao,
                :EnAdCEspecialidadeLeito,
                :EnAdCAla,
                :EnAdCQuarto,
                :EnAdCLeito,
                :EnAdCProfissional,
                :EnAdCPas,
                :EnAdCPad,
                :EnAdCFreqCardiaca,
                :EnAdCFreqRespiratoria,
                :EnAdCTemperatura,
                :EnAdCSPO,
                :EnAdCHGT,
                :EnAdCPeso,
                :EnAdCAlergia,
                :EnAdCAlergiaDescricao,
                :EnAdCUsoMedicamento,
                :EnAdCUsoMedicamentoDescricao,
                :EnAdCHistCirurgiaAnterior,
                :EnAdCHistCirurgiaAnteriorDescricao,
                :EnAdCJejumMinimo,
                :EnAdCJejumMinimoDescricao,
                :EnAdCUsoProtese,
                :EnAdCUsoProteseDescricao,
                :EnAdCRemocaoJoia,
                :EnAdCRemocaoJoiaDescricao,
                :EnAdCDoencaPrevia,
                :EnAdCDoencaPreviaDescricao,
                :EnAdCAreaOperatoria,
                :EnAdCAreaOperatoriaDescricao,
                :EnAdCLocalCirurgia,
                :EnAdCLado,
                :EnAdCEsvaziamentoVesical,
                :EnAdCEsvaziamentoVesicalDescricao,
                :EnAdCUnidade
            )";
            $result = $conn->prepare($sql);
        
            $result->execute(array(
                ":EnAdCAtendimento" => $iAtendimentoId ,
                ":EnAdCDataInicio" => date('Y-m-d') ,
                ":EnAdCHoraInicio" => date('H:i') ,
                ":EnAdCDataFim" => date('Y-m-d') ,
                ":EnAdCHoraFim" => date('H:i') ,
                ":EnAdCPrevisaoAlta" => $_POST['inputPrevisaoAlta'] == "" ? null : $_POST['inputPrevisaoAlta'], 
                ":EnAdCTipoInternacao" => $_POST['inputTipoInternacao'] == "" ? null : $_POST['inputTipoInternacao'], 
                ":EnAdCEspecialidadeLeito" => $_POST['inputEspLeito'] == "" ? null : $_POST['inputEspLeito'],
                ":EnAdCAla" => $_POST['inputAla'] == "" ? null : $_POST['inputAla'], 
                ":EnAdCQuarto" => $_POST['inputQuarto'] == "" ? null : $_POST['inputQuarto'], 
                ":EnAdCLeito" => $_POST['inputLeito'] == "" ? null : $_POST['inputLeito'], 
                ":EnAdCProfissional" => $userId,
                ":EnAdCPas" => $_POST['inputSistolica'] == "" ? null : $_POST['inputSistolica'],
                ":EnAdCPad" => $_POST['inputDiatolica'] == "" ? null : $_POST['inputDiatolica'],
                ":EnAdCFreqCardiaca" =>  $_POST['inputCardiaca'] == "" ? null : $_POST['inputCardiaca'],
                ":EnAdCFreqRespiratoria" =>  $_POST['inputRespiratoria'] == "" ? null : $_POST['inputRespiratoria'],
                ":EnAdCTemperatura" =>  $_POST['inputTemperatura'] == "" ? null : $_POST['inputTemperatura'],
                ":EnAdCSPO" => $_POST['inputSPO'] == "" ? null : $_POST['inputSPO'],
                ":EnAdCHGT" =>  $_POST['inputHGT'] == "" ? null : $_POST['inputHGT'],
                ":EnAdCPeso" => $_POST['inputPeso'] == "" ? null : $_POST['inputPeso'],
                ":EnAdCAlergia" => isset($_POST['alergias']) ? $_POST['alergias'] == "" ? null : $_POST['alergias'] : null,
                ":EnAdCAlergiaDescricao" => $_POST['textAlergias'] == "" ? null : $_POST['textAlergias'],
                ":EnAdCUsoMedicamento" => isset($_POST['medicamentos']) ? $_POST['medicamentos'] == "" ? null : $_POST['medicamentos'] : null,
                ":EnAdCUsoMedicamentoDescricao" => $_POST['textMedicamentos'] == "" ? null : $_POST['textMedicamentos'],
                ":EnAdCHistCirurgiaAnterior" => isset($_POST['cirurgiaAnterior']) ? $_POST['cirurgiaAnterior'] == "" ? null : $_POST['cirurgiaAnterior'] : null ,
                ":EnAdCHistCirurgiaAnteriorDescricao" =>  $_POST['textCirurgiaAnterior'] == "" ? null : $_POST['textCirurgiaAnterior'] ,                
                ":EnAdCJejumMinimo" => isset($_POST['jejum']) ? $_POST['jejum'] == "" ? null : $_POST['jejum'] : null ,
                ":EnAdCJejumMinimoDescricao" =>  $_POST['textJejum'] == "" ? null : $_POST['textJejum'] ,                
                ":EnAdCUsoProtese" => isset($_POST['proteses']) ? $_POST['proteses'] == "" ? null : $_POST['proteses'] : null ,
                ":EnAdCUsoProteseDescricao" =>  $_POST['textProteses'] == "" ? null : $_POST['textProteses'] ,                
                ":EnAdCRemocaoJoia" => isset($_POST['acessorios']) ? $_POST['acessorios'] == "" ? null : $_POST['acessorios'] : null,
                ":EnAdCRemocaoJoiaDescricao" =>  $_POST['textAcessorios'] == "" ? null : $_POST['textAcessorios'] ,                
                ":EnAdCDoencaPrevia" =>  $_POST['doencas'] == "" ? null : $_POST['doencas'] ,
                ":EnAdCDoencaPreviaDescricao" =>  $_POST['textDoencas'] == "" ? null : $_POST['textDoencas'] ,                
                ":EnAdCAreaOperatoria" => isset($_POST['tricotomia']) ? $_POST['tricotomia'] == "" ? null : $_POST['tricotomia'] : null ,
                ":EnAdCAreaOperatoriaDescricao" =>  $_POST['textTricotomia'] == "" ? null : $_POST['textTricotomia'] ,                
                ":EnAdCLocalCirurgia" =>  $_POST['local'] == "" ? null : $_POST['local'] ,
                ":EnAdCLado" =>  $_POST['lado'] == "" ? null : $_POST['lado'] , 
                ":EnAdCEsvaziamentoVesical" => isset($_POST['esvaziamento']) ? $_POST['esvaziamento'] == "" ? null : $_POST['esvaziamento'] : null,
                ":EnAdCEsvaziamentoVesicalDescricao" =>  $_POST['textEsvaziamento'] == "" ? null : $_POST['textEsvaziamento'] ,                
                ":EnAdCUnidade" =>  $iUnidade
            ));

            $_SESSION['msg']['titulo'] = "Sucesso";
            $_SESSION['msg']['mensagem'] = "Admissão inserida com sucesso!!!";
            $_SESSION['msg']['tipo'] = "success";

        }

        
    } catch (PDOException $e) {

        $_SESSION['msg']['titulo'] = "Erro";
        $_SESSION['msg']['mensagem'] = "Erro reportado com a Admissão Cirurgica Pre Operatoria!!!";
        $_SESSION['msg']['tipo'] = "error";

        echo 'Error: ' . $e->getMessage();
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Admissão Cirúrgica Pré-Operatório</title>

	<?php include_once("head.php"); ?>

    <!-- Theme JS files -->
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
	<script src="global_assets/js/demo_pages/form_select2.js"></script>
	<script src="global_assets/js/plugins/forms/selects/bootstrap_multiselect.js"></script>
	<script src="global_assets/js/demo_pages/form_multiselect.js"></script>

	<script src="global_assets/js/demo_pages/form_layouts.js"></script>
	<script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>
    <script src="global_assets/js/plugins/editors/summernote/summernote.min.js"></script>

	<script src="global_assets/js/plugins/forms/inputs/inputmask.js"></script>

    <script src="global_assets/js/demo_pages/datatables_responsive.js"></script>
	<script src="global_assets/js/demo_pages/datatables_sorting.js"></script>
    <script src="global_assets/js/plugins/tables/datatables/datatables.min.js"></script>
	<script src="global_assets/js/plugins/tables/datatables/extensions/responsive.min.js"></script>
	<!-- /theme JS files -->	

	<!-- Validação -->
	<script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
	<script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
	<script src="global_assets/js/demo_pages/form_validation.js"></script>
	
	<script type="text/javascript">

        let idAtendimentoCirurgicoPreOperatorio = <?php echo isset($iAtendimentoCirurgicoPreOperatorio) ? $iAtendimentoCirurgicoPreOperatorio : 'null'; ?>

		$(document).ready(function() {

            console.log(idAtendimentoCirurgicoPreOperatorio);

            getAcessosVenosos();
            getTermosConsentimento();
            getExamesComplementares();

            $('#tblAcessoVenoso').DataTable({
				"order": [[ 0, "asc" ]],
			    autoWidth: false,
				responsive: true,
                searching: false,
				ordering: false, 
				paging: false,
			    columnDefs: [{ 
					orderable: false,   //item
					width: "1%",
					targets: [0]
				},
				{ 
					orderable: true,   //Data
					width: "25%",
					targets: [1]
				},
                { 
					orderable: true,   //Local
					width: "25%",
					targets: [2]
				},				
				{ 
					orderable: true,   //Tipo
					width: "24%",
					targets: [3]
				},
				{ 
					orderable: false,  //Responsável
					width: "20%",
					targets: [4]
				},
                { 
					orderable: false,  //ações
					width: "5%",
					targets: [4]
				}],
				dom: '<"datatable-header"fl><"datatable-scroll-wrap"t><"datatable-footer"ip>',
				language: {
					search: '<span>Filtro:</span> _INPUT_',
					searchPlaceholder: 'filtra qualquer coluna...',
					lengthMenu: '<span>Mostrar:</span> _MENU_',
					paginate: { 'first': 'Primeira', 'last': 'Última', 'next': $('html').attr('dir') == 'rtl' ? '&larr;' : '&rarr;', 'previous': $('html').attr('dir') == 'rtl' ? '&rarr;' : '&larr;' }
				}
			});
            $('#tblConcentimento').DataTable({
				"order": [[ 0, "asc" ]],
			    autoWidth: false,
				responsive: true,
                searching: false,
				ordering: false, 
				paging: false,
			    columnDefs: [{ 
					orderable: false,   //item
					width: "1%",
					targets: [0]
				},
				{ 
					orderable: true,   //Data
					width: "35%",
					targets: [1]
				},
                { 
					orderable: true,   //descrição
					width: "60%",
					targets: [2]
				},
                { 
					orderable: false,  //ações
					width: "4%",
					targets: [3]
				}],
				dom: '<"datatable-header"fl><"datatable-scroll-wrap"t><"datatable-footer"ip>',
				language: {
					search: '<span>Filtro:</span> _INPUT_',
					searchPlaceholder: 'filtra qualquer coluna...',
					lengthMenu: '<span>Mostrar:</span> _MENU_',
					paginate: { 'first': 'Primeira', 'last': 'Última', 'next': $('html').attr('dir') == 'rtl' ? '&larr;' : '&rarr;', 'previous': $('html').attr('dir') == 'rtl' ? '&rarr;' : '&larr;' }
				}
			});
            $('#tblExame').DataTable({
				"order": [[ 0, "asc" ]],
			    autoWidth: false,
				responsive: true,
                searching: false,
				ordering: false, 
				paging: false,
			    columnDefs: [{ 
					orderable: false,   //item
					width: "1%",
					targets: [0]
				},
				{ 
					orderable: true,   //Data
					width: "35%",
					targets: [1]
				},
                { 
					orderable: true,   //descrição
					width: "60%",
					targets: [2]
				},
                { 
					orderable: false,  //ações
					width: "4%",
					targets: [3]
				}],
				dom: '<"datatable-header"fl><"datatable-scroll-wrap"t><"datatable-footer"ip>',
				language: {
					search: '<span>Filtro:</span> _INPUT_',
					searchPlaceholder: 'filtra qualquer coluna...',
					lengthMenu: '<span>Mostrar:</span> _MENU_',
					paginate: { 'first': 'Primeira', 'last': 'Última', 'next': $('html').attr('dir') == 'rtl' ? '&larr;' : '&rarr;', 'previous': $('html').attr('dir') == 'rtl' ? '&rarr;' : '&larr;' }
				}
			});
            // a função "cantaCaracteres" está no arquivo "custom.js"
            // "function cantaCaracteres(htmlTextId, numMaxCaracteres, htmlIdMostraRestantes)"

            $(".alergias").each(function(){
                $(this).on('click',function(e){
                    if($(this).val() == '1'){
                        $('#textAlergiasViwer').removeClass('d-none')
                    }else{
                        $('#textAlergias').val('');
                        $('#textAlergiasViwer').addClass('d-none')
                        cantaCaracteres('textAlergias', 150, 'caracteresInputAlergias')
                    }
                })
            })
            $(".medicamentos").each(function(){
                $(this).on('click',function(e){
                    if($(this).val() == '1'){
                        $('#textMedicamentosViwer').removeClass('d-none');
                    }else{
                        $('#textMedicamentos').val('');
                        $('#textMedicamentosViwer').addClass('d-none');
                        cantaCaracteres('textMedicamentos', 150, 'caracteresInputMedicamentos')
                    }
                })
            })
            $(".cirurgiaAnterior").each(function(){
                $(this).on('click',function(e){
                    if($(this).val() == '1'){
                        $('#textCirurgiaAnteriorViwer').removeClass('d-none');
                    }else{
                        $('#textCirurgiaAnterior').val('');
                        $('#textCirurgiaAnteriorViwer').addClass('d-none');
                        cantaCaracteres('textCirurgiaAnterior', 150, 'caracteresInputCirurgiaAnterior')
                    }
                })
            })
            $(".jejum").each(function(){
                $(this).on('click',function(e){
                    if($(this).val() == '1'){
                        $('#textJejumViwer').removeClass('d-none');
                    }else{
                        $('#textJejum').val('');
                        $('#textJejumViwer').addClass('d-none');
                        cantaCaracteres('textJejum', 150, 'caracteresInputJejum')
                    }
                })
            })
            $(".proteses").each(function(){
                $(this).on('click',function(e){
                    if($(this).val() == '1'){
                        $('#textProtesesViwer').removeClass('d-none')
                    }else{
                        $('#textProteses').val('');
                        $('#textProtesesViwer').addClass('d-none')
                        cantaCaracteres('textProteses', 150, 'caracteresInputProteses')
                    }
                })
            })
            $(".acessorios").each(function(){
                $(this).on('click',function(e){
                    if($(this).val() == '1'){
                        $('#textAcessoriosViwer').removeClass('d-none');
                    }else{
                        $('#textAcessorios').val('');
                        $('#textAcessoriosViwer').addClass('d-none');
                        cantaCaracteres('textAcessorios', 150, 'caracteresInputAcessorios')
                    }
                })
            })
            $('#doencas').on('change',function(e){
                if($(this).val()){
                    $('#textDoencasViwer').removeClass('d-none');
                }else{
                    $('#textDoencas').val('');
                    $('#textDoencasViwer').addClass('d-none');
                    cantaCaracteres('textDoencas', 150, 'caracteresInputDoencas')
                }
            })
            $(".tricotomia").each(function(){
                $(this).on('click',function(e){
                    if($(this).val() == '1'){
                        $('#textTricotomiaViwer').removeClass('d-none');
                    }else{
                        $('#textTricotomia').val('');
                        $('#textTricotomiaViwer').addClass('d-none');
                        cantaCaracteres('textTricotomia', 150, 'caracteresInputTricotomia')
                    }
                })
            })
            $(".esvaziamento").each(function(){
                $(this).on('click',function(e){
                    if($(this).val() == '1'){
                        $('#textEsvaziamentoViwer').removeClass('d-none');
                    }else{
                        $('#textEsvaziamento').val('');
                        $('#textEsvaziamentoViwer').addClass('d-none');
                        cantaCaracteres('textEsvaziamento', 150, 'caracteresInputEsvaziamento')
                    }
                })
            })

            $("#textMedicamentos").on('input', function(e){
                cantaCaracteres('textMedicamentos', 150, 'caracteresInputMedicamentos')
            })
            $("#textAlergias").on('input', function(e){
                cantaCaracteres('textAlergias', 150, 'caracteresInputAlergias')
            })
            $("#textCirurgiaAnterior").on('input', function(e){
                cantaCaracteres('textCirurgiaAnterior', 150, 'caracteresInputCirurgiaAnterior')
            })
            $("#textJejum").on('input', function(e){
                cantaCaracteres('textJejum', 150, 'caracteresInputJejum')
            })
            $("#textProteses").on('input', function(e){
                cantaCaracteres('textProteses', 150, 'caracteresInputProteses')
            })
            $("#textAcessorios").on('input', function(e){
                cantaCaracteres('textAcessorios', 150, 'caracteresInputAcessorios')
            })
            $("#textDoencas").on('input', function(e){
                cantaCaracteres('textDoencas', 150, 'caracteresInputDoencas')
            })
            $("#textTricotomia").on('input', function(e){
                cantaCaracteres('textTricotomia', 150, 'caracteresInputTricotomia')
            })
            $("#textEsvaziamento").on('input', function(e){
                cantaCaracteres('textEsvaziamento', 150, 'caracteresInputEsvaziamento')
            })

            $('#venosoBTN').on('click',function(e){
                e.preventDefault()
                if (idAtendimentoCirurgicoPreOperatorio == null) {
                    alerta('Erro', 'Salve a Admissão antes de inserir um Acesso Venoso', 'error')
                    return
                }
                $('#page-modal-acesso').fadeIn(200)
            })

            $('#termoBTN').on('click',function(e){
                e.preventDefault()
                if (idAtendimentoCirurgicoPreOperatorio == null) {
                    alerta('Erro', 'Salve a Admissão antes de inserir um Termo de Consentimento', 'error')
                    return
                }
                $('#page-modal-concentimento').fadeIn(200)
            })

            $('#examesBTN').on('click',function(e){
                e.preventDefault()
                if (idAtendimentoCirurgicoPreOperatorio == null) {
                    alerta('Erro', 'Salve a Admissão antes de inserir um Exame Complementar', 'error')
                    return
                }
                $('#page-modal-exames').fadeIn(200)
            })

            $('#addAcesso').on('click',function(e){
                e.preventDefault()
                let menssageError = ''

                let dataHoraAcessoVenoso = $('#dataHoraAcessoVenoso').val()
                let localPuncaoAcessoVenoso = $('#localPuncaoAcessoVenoso').val()
                let calibreAcessoVenoso = $('#calibreAcessoVenoso').val()
                let responsavelAcessoVenoso = $('#responsavelAcessoVenoso').val()

                switch(menssageError){
                    case dataHoraAcessoVenoso: menssageError = 'informe a data e hora do Acesso Venoso'; $('#dataHoraAcessoVenoso').focus();break;
					case localPuncaoAcessoVenoso: menssageError = 'informe o local de punção do Acesso Venoso'; $('#localPuncaoAcessoVenoso').focus();break;
                    case responsavelAcessoVenoso : menssageError = 'infomrme o responsãvel do Acesso Venoso'; $('#responsavelAcessoVenoso').focus();break;	
					default: menssageError = ''; break;
				}

                if(menssageError){
					alerta('Campo Obrigatório!', menssageError, 'error')
					return
				}

                $('#addAcesso').prop('disabled', true);

                $.ajax({
					type: 'POST',
					url: 'filtraAdmissaoCirurgicaPreOperatorio.php',
					dataType: 'json',
					data:{
						'tipoRequest': 'ADDACESSOVENOSO',
                        'iAtendimentoCirurgicoPreOperatorio' : <?php echo isset($iAtendimentoCirurgicoPreOperatorio) ? $iAtendimentoCirurgicoPreOperatorio : 'null'; ?>,
                        'idTemporaria' : <?php echo $iAtendimentoId; ?> ,
                        'dataHoraAcessoVenoso' : dataHoraAcessoVenoso,
                        'localPuncaoAcessoVenoso' : localPuncaoAcessoVenoso,
                        'calibreAcessoVenoso' : calibreAcessoVenoso,
                        'responsavelAcessoVenoso' : responsavelAcessoVenoso
					},
					success: function(response) {

                        if(response.status == 'success'){

                            alerta(response.titulo, response.menssagem, response.status)
                            getAcessosVenosos();

                            $('#addAcesso').prop('disabled', true);  

                            $('#dataHoraAcessoVenoso').val('')
                            $('#localPuncaoAcessoVenoso').val('')
                            $('#calibreAcessoVenoso').val('')
                            $('#responsavelAcessoVenoso').val('')  

                        }else{
                            alerta(response.titulo, response.menssagem, response.status)
                            $('#addAcesso').prop('disabled', true);
                        }


                        //cheackList()
					}
				});
            })
            $('#addConsentimento').on('click',function(e){

                e.preventDefault()
                let menssageError = ''

                let dataHoraConsentimento = $('#dataHoraConsentimento').val()
                let descricaoConsentimento = $('#descricaoConsentimento').val()
                let arquivoTermoConsentimento = $('#arquivoTermoConsentimento').val()
                let fileTC = $('#arquivoTermoConsentimento').prop('files')[0]

                switch(menssageError){
                    case dataHoraConsentimento: menssageError = 'informe a data e hora do Termo de Consentimento'; $('#dataHoraConsentimento').focus();break;
					case descricaoConsentimento: menssageError = 'informe a descrição do Termo de Consentimento'; $('#descricaoConsentimento').focus();break;
                    case arquivoTermoConsentimento : menssageError = 'infomrme o arquivo do Termo de Consentimento'; $('#arquivoTermoConsentimento').focus();break;	
					default: menssageError = ''; break;
				}

                if(menssageError){
					alerta('Campo Obrigatório!', menssageError, 'error')
					return
				}

                //Verifica se a extensão é  diferente de PDF, DOC, DOCX, ODT, JPG, JPEG, PNG!
				if (ext(arquivoTermoConsentimento) != 'pdf' && ext(arquivoTermoConsentimento) != 'doc' && ext(arquivoTermoConsentimento) != 'docx' && ext(arquivoTermoConsentimento) != 'odt' && ext(arquivoTermoConsentimento) != 'jpg' && ext(arquivoTermoConsentimento) != 'jpeg' && ext(arquivoTermoConsentimento) != 'png'){
					alerta('Atenção','Por favor, envie arquivos com a seguinte extensão: PDF, DOC, DOCX, ODT, JPG, JPEG, PNG!','error');
					$('#arquivoTermoConsentimento').focus();
					return ;	
				}

                let tamanho =  1024 * 1024 * 32; //32MB
				//Verifica o tamanho do arquivo
				if (fileTC.size > tamanho){
					alerta('Atenção','O arquivo enviado é muito grande, envie arquivos de até 32MB.','error');
					$('#arquivoTermoConsentimento').focus();
					return ;
				}

                let form_data = new FormData();
                
                form_data.append('tipoRequest', 'ADDTERMOCONSENTIMENTO');
                form_data.append('arquivoTermoConsentimento', $('#arquivoTermoConsentimento').prop('files')[0]);                  
                form_data.append('dataHoraConsentimento', $("#dataHoraConsentimento").val());
                form_data.append('descricaoConsentimento', $("#descricaoConsentimento").val());
                form_data.append('iAtendimentoCirurgicoPreOperatorio', <?php echo isset($iAtendimentoCirurgicoPreOperatorio) ? $iAtendimentoCirurgicoPreOperatorio : 'null'; ?>);
                form_data.append('idTemporaria', <?php echo $iAtendimentoId; ?>);

                $('#addConsentimento').prop('disabled', true);

                $.ajax({
					type: 'POST',
					url: 'filtraAdmissaoCirurgicaPreOperatorio.php',
					dataType: 'json',
                    cache: false,
                    contentType: false,
                    processData: false,
					data: form_data ,
					success: function(response) {

                        if(response.status == 'success'){

                            alerta(response.titulo, response.menssagem, response.status)
                            getTermosConsentimento();
                            $('#addConsentimento').prop('disabled', false); 

                        }else{
                            alerta(response.titulo, response.menssagem, response.status)
                            $('#addConsentimento').prop('disabled', false);
                        }


                        //cheackList()
					}
				});
            })


            $('#addExame').on('click',function(e){

                e.preventDefault()
                let menssageError = ''

                let dataHoraExame = $('#dataHoraExame').val()
                let descricaoExame = $('#descricaoExame').val()
                let arquivoExame = $('#arquivoExame').val()
                let fileExame = $('#arquivoExame').prop('files')[0]

                switch(menssageError){
                    case dataHoraExame: menssageError = 'informe a data e hora do Termo de Consentimento'; $('#dataHoraExame').focus();break;
					case descricaoExame: menssageError = 'informe a descrição do Termo de Consentimento'; $('#descricaoExame').focus();break;
                    case arquivoExame : menssageError = 'infomrme o arquivo do Termo de Consentimento'; $('#arquivoExame').focus();break;	
					default: menssageError = ''; break;
				}

                if(menssageError){
					alerta('Campo Obrigatório!', menssageError, 'error')
					return
				}

                //Verifica se a extensão é  diferente de PDF, DOC, DOCX, ODT, JPG, JPEG, PNG!
				if (ext(arquivoExame) != 'pdf' && ext(arquivoExame) != 'doc' && ext(arquivoExame) != 'docx' && ext(arquivoExame) != 'odt' && ext(arquivoExame) != 'jpg' && ext(arquivoExame) != 'jpeg' && ext(arquivoExame) != 'png'){
					alerta('Atenção','Por favor, envie arquivos com a seguinte extensão: PDF, DOC, DOCX, ODT, JPG, JPEG, PNG!','error');
					$('#arquivoExame').focus();
					return ;	
				}

                let tamanho =  1024 * 1024 * 32; //32MB
				//Verifica o tamanho do arquivo
				if (fileExame.size > tamanho){
					alerta('Atenção','O arquivo enviado é muito grande, envie arquivos de até 32MB.','error');
					$('#arquivoExame').focus();
					return ;
				}

                let form_data = new FormData();
                
                form_data.append('tipoRequest', 'ADDEXAMESCOMPLEMENTARES');
                form_data.append('arquivoExame', $('#arquivoExame').prop('files')[0]);                  
                form_data.append('dataHoraExame', $("#dataHoraExame").val());
                form_data.append('descricaoExame', $("#descricaoExame").val());
                form_data.append('iAtendimentoCirurgicoPreOperatorio', <?php echo isset($iAtendimentoCirurgicoPreOperatorio) ? $iAtendimentoCirurgicoPreOperatorio : 'null'; ?>);
                form_data.append('idTemporaria', <?php echo $iAtendimentoId; ?>);

                $('#addExame').prop('disabled', true);

                $.ajax({
					type: 'POST',
					url: 'filtraAdmissaoCirurgicaPreOperatorio.php',
					dataType: 'json',
                    cache: false,
                    contentType: false,
                    processData: false,
					data: form_data ,
					success: function(response) {

                        if(response.status == 'success'){

                            alerta(response.titulo, response.menssagem, response.status) 
                            getExamesComplementares();
                            $('#addExame').prop('disabled', false);


                        }else{
                            alerta(response.titulo, response.menssagem, response.status)
                            $('#addExame').prop('disabled', false);

                        }

					}
				});
            })

            $('#salvarAdmissao').on('click', function(e){
                e.preventDefault()
                $("#formAdmissaoCirurgicaPreOperatorio").submit();
            })

            $('#modal-acesso-close-x').on('click', function(e){
                e.preventDefault()
                //$('#tblAcessoVenosoViwer').addClass('d-none')
                $('#page-modal-acesso').fadeOut(200)
            })
            $('#modal-concentimento-close-x').on('click', function(e){
                e.preventDefault()
                //$('#tblConcentimentoViwer').addClass('d-none')
                $('#page-modal-concentimento').fadeOut(200)
            })
            $('#modal-exames-close-x').on('click', function(e){
                e.preventDefault()
                //$('#tblExameViwer').addClass('d-none')
                $('#page-modal-exames').fadeOut(200)
            })
            
		}); //document.ready

        function ext(path) {
			var final = path.substr(path.lastIndexOf('/')+1);
			var separador = final.lastIndexOf('.');
			return separador <= 0 ? '' : final.substr(separador + 1);
		}

        function getAcessosVenosos(){

            $.ajax({
                type: 'POST',
                url: 'filtraAdmissaoCirurgicaPreOperatorio.php',
                dataType: 'json',
                data:{
                    'tipoRequest': 'GETACESSOSVENOSOS',
                    'iAtendimentoCirurgicoPreOperatorio' : <?php echo isset($iAtendimentoCirurgicoPreOperatorio) ? $iAtendimentoCirurgicoPreOperatorio : 'null'; ?>,
                },
                success: function(response) {

                    $('#dataAcessoVenoso').html('');
                    let HTML = ''
                    
                    response.forEach(item => {

                        /*let copiar = `<a class='list-icons-item mr-2 ' style='color: black; cursor:pointer' onclick='copiarTermoConsentimento(\"${item.id}\")'><i class='icon-files-empty' title='Copiar Termo'></i></a>`;
                        let visualizarArquivo = `<a class='list-icons-item mr-2 ' style='color: black; cursor:pointer' href="global_assets/anexos/termoConsentimento/${item.arquivo}" target="_blank" > <i class='icon-file-eye' title='Visualizar Termo'></i> </a>`;*/
                        let exc = `<a style='color: black; cursor:pointer' onclick='excluirAcessoVenoso(\"${item.id}\")' class='list-icons-item'><i class='icon-bin' title='Excluir Acesso Venoso'></i></a>`;
                        let acoes = ``;                                              
                   
                        acoes = `<div class='list-icons'>
                            ${exc}
                        </div>`;
                                            
                        HTML += `
                        <tr class='orientacaoItem'>
                            <td class="text-left">${item.item}</td> 
                            <td class="text-left">${item.dataHora}</td>
                            <td class="text-left">${item.localPuncao}</td>
                            <td class="text-left">${item.tipoCalibre}</td>                            
                            <td class="text-left">${item.responsavelTecnico}</td>
                            <td class="text-center">${acoes}</td>
                        </tr>`

                    })
                    $('#dataAcessoVenoso').html(HTML).show();
                }
            });

        }

        function getTermosConsentimento(){

            $.ajax({
                type: 'POST',
                url: 'filtraAdmissaoCirurgicaPreOperatorio.php',
                dataType: 'json',
                data:{
                    'tipoRequest': 'GETTERMOSCONSENTIMENTO',
                    'iAtendimentoCirurgicoPreOperatorio' : <?php echo isset($iAtendimentoCirurgicoPreOperatorio) ? $iAtendimentoCirurgicoPreOperatorio : 'null'; ?>,
                },
                success: function(response) {

                    $('#dataTermoConsentimento').html('');
                    let HTML = ''
                    
                    response.forEach(item => {

                        /*let copiar = `<a class='list-icons-item mr-2 ' style='color: black; cursor:pointer' onclick='copiarTermoConsentimento(\"${item.id}\")'><i class='icon-files-empty' title='Copiar Termo'></i></a>`;*/
                        let visualizarArquivo = `<a class='list-icons-item mr-2 ' style='color: black; cursor:pointer' href="global_assets/anexos/termoConsentimentoAdCirPreOperatorio/${item.arquivo}" target="_blank" > <i class='icon-file-eye' title='Visualizar Termo'></i> </a>`;
                        let exc = `<a style='color: black; cursor:pointer' onclick='excluirTermo(\"${item.id}\")' class='list-icons-item'><i class='icon-bin' title='Excluir Termo'></i></a>`;
                        let acoes = ``;                                              
                   
                        acoes = `<div class='list-icons'>
                            ${visualizarArquivo}
                            ${exc}
                        </div>`;
                                            
                        HTML += `
                        <tr class='orientacaoItem'>
                            <td class="text-left">${item.item}</td> 
                            <td class="text-left">${item.dataHora}</td>                          
                            <td class="text-left">${item.descricao}</td>
                            <td class="text-center">${acoes}</td>
                        </tr>`

                    })
                    $('#dataTermoConsentimento').html(HTML).show();
                }
            });

        }

        function getExamesComplementares(){

            $.ajax({
                type: 'POST',
                url: 'filtraAdmissaoCirurgicaPreOperatorio.php',
                dataType: 'json',
                data:{
                    'tipoRequest': 'GETEXAMESCOMPLEMENTARES',
                    'iAtendimentoCirurgicoPreOperatorio' : <?php echo isset($iAtendimentoCirurgicoPreOperatorio) ? $iAtendimentoCirurgicoPreOperatorio : 'null'; ?>,
                },
                success: function(response) {

                    $('#dataExame').html('');
                    let HTML = ''
                    
                    response.forEach(item => {

                        /*let copiar = `<a class='list-icons-item mr-2 ' style='color: black; cursor:pointer' onclick='copiarTermoConsentimento(\"${item.id}\")'><i class='icon-files-empty' title='Copiar Termo'></i></a>`;*/
                        let visualizarArquivo = `<a class='list-icons-item mr-2 ' style='color: black; cursor:pointer' href="global_assets/anexos/examesComplementaresImagensAdCirPreOperatorio/${item.arquivo}" target="_blank" > <i class='icon-file-eye' title='Visualizar Exame'></i> </a>`;
                        let exc = `<a style='color: black; cursor:pointer' onclick='excluirExame(\"${item.id}\")' class='list-icons-item'><i class='icon-bin' title='Excluir Exame'></i></a>`;
                        let acoes = ``;                                              
                   
                        acoes = `<div class='list-icons'>
                            ${visualizarArquivo}
                            ${exc}
                        </div>`;
                                            
                        HTML += `
                        <tr class='orientacaoItem'>
                            <td class="text-left">${item.item}</td> 
                            <td class="text-left">${item.dataHora}</td>                          
                            <td class="text-left">${item.descricao}</td>
                            <td class="text-center">${acoes}</td>
                        </tr>`

                    })
                    $('#dataExame').html(HTML).show();
                }
            });

        }

        function excluirAcessoVenoso(id){
            confirmaExclusaoAjax('filtraAdmissaoCirurgicaPreOperatorio.php', 'Excluir Acesso Venoso?', 'DELETEACESSOVENOSO', id, getAcessosVenosos)
        }
        function excluirTermo(id){
            confirmaExclusaoAjax('filtraAdmissaoCirurgicaPreOperatorio.php', 'Excluir Termo de Consentimento?', 'DELETETERMO', id, getTermosConsentimento)           
        }
        function excluirExame(id){
            confirmaExclusaoAjax('filtraAdmissaoCirurgicaPreOperatorio.php', 'Excluir Exame?', 'DELETEEXAME', id, getExamesComplementares)          
        }        

	</script>

    <style>
        textarea{
            height:80px;
        }
        .options{
            height:40px;
        }
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
						<form name="formAdmissaoCirurgicaPreOperatorio" id="formAdmissaoCirurgicaPreOperatorio" method="post">
							<?php
								echo "<input type='hidden' id='iAtendimentoId' name='iAtendimentoId' value='$iAtendimentoId' />";
							?>
							<div class="card">

                            <div class="col-md-12">
                                <div class="row">

                                    <div class="col-md-6" style="text-align: left;">

                                        <div class="card-header header-elements-inline">
                                            <h3 class="card-title"><b>Admissão Cirúrgica Pré-Operatório</b></h3>
                                        </div>
            
                                    </div>

                                    <div class="col-md-6" style="text-align: right;">

                                        <div class="form-group" style="margin:20px;" >
                                            <button class="btn btn-lg btn-success mr-1 salvarAdmissao" >Salvar</button>
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

                            <div class="box-exameFisico" style="display: block;">
                                <div class="card">
                                    <div class="card-header header-elements-inline">
                                        <h3 class="card-title">Histórico Pré-Operatório</h3>

                                        <div class="header-elements">
                                            <div class="list-icons">
                                                <a class="list-icons-item" data-action="collapse"></a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card-body">
                                      
                                        <!-- linha 1 -->
                                        <div class="col-lg-12 mb-3 row">
                                                                              
                                            <div class="col-lg-3">
                                                <label>Alergias</label>
                                                <div class="col-lg-12 row options">
                                                    <div class="col-lg-3 form-check form-check-inline">
                                                        <label class="form-check-label">
                                                            <input type="radio" name="alergias" class="alergias form-input-styled" placeholder="" value="1" <?php if (isset($iAtendimentoCirurgicoPreOperatorio )) echo $rowAdmissao['EnAdCAlergia'] == '1' ? 'checked' : ''; ?> >
                                                            SIM
                                                        </label>
                                                    </div>
                                                    <div class="col-lg-3 form-check form-check-inline">
                                                        <label class="form-check-label">
                                                            <input type="radio" name="alergias" class="alergias form-input-styled" placeholder="" value="0" <?php if (isset($iAtendimentoCirurgicoPreOperatorio )) echo $rowAdmissao['EnAdCAlergia'] == '0' ? 'checked' : ''; ?> >
                                                            NÃO
                                                        </label>
                                                    </div>
                                                </div>
                                                <div id="textAlergiasViwer" class="<?php if (isset($iAtendimentoCirurgicoPreOperatorio )) echo $rowAdmissao['EnAdCAlergia'] == '1' ? '' : 'd-none'; ?>">
                                                    <textarea id="textAlergias" name="textAlergias" class="form-control" rows="4" cols="4" maxLength="150" placeholder="" ><?php echo isset($iAtendimentoCirurgicoPreOperatorio) ? $rowAdmissao['EnAdCAlergiaDescricao'] : ''; ?></textarea>
                                                    <small class="text-muted form-text">
                                                        Máx. 150 caracteres<br>
                                                        <span id="caracteresInputAlergias"></span>
                                                    </small>
                                                </div>
                                            </div>

                                            <div class="col-lg-3">
                                                <label>Uso de medicamentos</label>
                                                <div class="col-lg-12 row options">
                                                    <div class="col-lg-3 form-check form-check-inline">
                                                        <label class="form-check-label">
                                                            <input type="radio" name="medicamentos" class="medicamentos form-input-styled" placeholder="" value="1" <?php if (isset($iAtendimentoCirurgicoPreOperatorio )) echo $rowAdmissao['EnAdCUsoMedicamento'] == '1' ? 'checked' : ''; ?> >
                                                            SIM
                                                        </label>
                                                    </div>
                                                    <div class="col-lg-3 form-check form-check-inline">
                                                        <label class="form-check-label">
                                                            <input type="radio" name="medicamentos" class="medicamentos form-input-styled" placeholder="" value="0" <?php if (isset($iAtendimentoCirurgicoPreOperatorio )) echo $rowAdmissao['EnAdCUsoMedicamento'] == '0' ? 'checked' : ''; ?> >
                                                            NÃO
                                                        </label>
                                                    </div>
                                                </div>
                                                <div id="textMedicamentosViwer" class="<?php if (isset($iAtendimentoCirurgicoPreOperatorio )) echo $rowAdmissao['EnAdCUsoMedicamento'] == '1' ? '' : 'd-none'; ?>">
                                                    <textarea id="textMedicamentos" name="textMedicamentos" class="form-control" rows="4" cols="4" maxLength="150" placeholder="" ><?php echo isset($iAtendimentoCirurgicoPreOperatorio) ? $rowAdmissao['EnAdCUsoMedicamentoDescricao'] : ''; ?></textarea>
                                                    <small class="text-muted form-text">
                                                        Máx. 150 caracteres<br>
                                                        <span id="caracteresInputMedicamentos"></span>
                                                    </small>
                                                </div>
                                            </div>

                                            <div class="col-lg-3">
                                                <label>História de Cirurgia Anterior</label>
                                                <div class="col-lg-12 row options">
                                                    <div class="col-lg-3 form-check form-check-inline">
                                                        <label class="form-check-label">
                                                            <input type="radio" name="cirurgiaAnterior" class="cirurgiaAnterior form-input-styled" placeholder="" value="1" <?php if (isset($iAtendimentoCirurgicoPreOperatorio )) echo $rowAdmissao['EnAdCHistCirurgiaAnterior'] == '1' ? 'checked' : ''; ?> >
                                                            SIM
                                                        </label>
                                                    </div>
                                                    <div class="col-lg-3 form-check form-check-inline">
                                                        <label class="form-check-label">
                                                            <input type="radio" name="cirurgiaAnterior" class="cirurgiaAnterior form-input-styled" placeholder="" value="0" <?php if (isset($iAtendimentoCirurgicoPreOperatorio )) echo $rowAdmissao['EnAdCHistCirurgiaAnterior'] == '0' ? 'checked' : ''; ?> >
                                                            NÃO
                                                        </label>
                                                    </div>
                                                </div>
                                                <div id="textCirurgiaAnteriorViwer" class="<?php if (isset($iAtendimentoCirurgicoPreOperatorio )) echo $rowAdmissao['EnAdCHistCirurgiaAnterior'] == '1' ? '' : 'd-none'; ?>">
                                                    <textarea id="textCirurgiaAnterior" name="textCirurgiaAnterior" class="form-control" rows="4" cols="4" maxLength="150" placeholder="" ><?php echo isset($iAtendimentoCirurgicoPreOperatorio) ? $rowAdmissao['EnAdCHistCirurgiaAnteriorDescricao'] : ''; ?></textarea>
                                                    <small class="text-muted form-text">
                                                        Máx. 150 caracteres<br>
                                                        <span id="caracteresInputCirurgiaAnterior"></span>
                                                    </small>
                                                </div>
                                            </div>

                                            <div class="col-lg-3">
                                                <label>Jejum Mínimo (8 horas)</label>
                                                <div class="col-lg-12 row options">
                                                    <div class="col-lg-3 form-check form-check-inline">
                                                        <label class="form-check-label">
                                                            <input type="radio" name="jejum" class="jejum form-input-styled" placeholder="" value="1" <?php if (isset($iAtendimentoCirurgicoPreOperatorio )) echo $rowAdmissao['EnAdCJejumMinimo'] == '1' ? 'checked' : ''; ?> >
                                                            SIM
                                                        </label>
                                                    </div>
                                                    <div class="col-lg-3 form-check form-check-inline">
                                                        <label class="form-check-label">
                                                            <input type="radio" name="jejum" class="jejum form-input-styled" placeholder="" value="0" <?php if (isset($iAtendimentoCirurgicoPreOperatorio )) echo $rowAdmissao['EnAdCJejumMinimo'] == '0' ? 'checked' : ''; ?> >
                                                            NÃO
                                                        </label>
                                                    </div>
                                                </div>
                                                <div id="textJejumViwer" class="<?php if (isset($iAtendimentoCirurgicoPreOperatorio )) echo $rowAdmissao['EnAdCJejumMinimo'] == '1' ? '' : 'd-none'; ?>">
                                                    <textarea id="textJejum" name="textJejum" class="form-control" rows="4" cols="4" maxLength="150" placeholder="" ><?php echo isset($iAtendimentoCirurgicoPreOperatorio) ? $rowAdmissao['EnAdCJejumMinimoDescricao'] : ''; ?></textarea>
                                                    <small class="text-muted form-text">
                                                        Máx. 150 caracteres<br>
                                                        <span id="caracteresInputJejum"></span>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- linha 2 -->
                                        <div class="col-lg-12 mb-3 row">
                                                                                       
                                            <div class="col-lg-3">
                                                <label>Uso de Próteses</label>
                                                <div class="col-lg-12 row options">
                                                    <div class="col-lg-3 form-check form-check-inline">
                                                        <label class="form-check-label">
                                                            <input type="radio" name="proteses" class="proteses form-input-styled" placeholder="" value="1" <?php if (isset($iAtendimentoCirurgicoPreOperatorio )) echo $rowAdmissao['EnAdCUsoProtese'] == '1' ? 'checked' : ''; ?> >
                                                            SIM
                                                        </label>
                                                    </div>
                                                    <div class="col-lg-3 form-check form-check-inline">
                                                        <label class="form-check-label">
                                                            <input type="radio" name="proteses" class="proteses form-input-styled" placeholder="" value="0" <?php if (isset($iAtendimentoCirurgicoPreOperatorio )) echo $rowAdmissao['EnAdCUsoProtese'] == '0' ? 'checked' : ''; ?> >
                                                            NÃO
                                                        </label>
                                                    </div>
                                                </div>
                                                <div id="textProtesesViwer" class="<?php if (isset($iAtendimentoCirurgicoPreOperatorio )) echo $rowAdmissao['EnAdCUsoProtese'] == '1' ? '' : 'd-none'; ?>">
                                                    <textarea id="textProteses" name="textProteses" class="form-control" rows="4" cols="4" maxLength="150" placeholder="" ><?php echo isset($iAtendimentoCirurgicoPreOperatorio) ? $rowAdmissao['EnAdCUsoProteseDescricao'] : ''; ?></textarea>
                                                    <small class="text-muted form-text">
                                                        Máx. 150 caracteres<br>
                                                        <span id="caracteresInputProteses"></span>
                                                    </small>
                                                </div>
                                            </div>

                                            <div class="col-lg-3">
                                                <label>Remoção de Jóias e Acessórios</label>
                                                <div class="col-lg-12 row options">
                                                    <div class="col-lg-3 form-check form-check-inline">
                                                        <label class="form-check-label">
                                                            <input type="radio" name="acessorios" class="acessorios form-input-styled" placeholder="" value="1" <?php if (isset($iAtendimentoCirurgicoPreOperatorio )) echo $rowAdmissao['EnAdCRemocaoJoia'] == '1' ? 'checked' : ''; ?> >
                                                            SIM
                                                        </label>
                                                    </div>
                                                    <div class="col-lg-3 form-check form-check-inline">
                                                        <label class="form-check-label">
                                                            <input type="radio" name="acessorios" class="acessorios form-input-styled" placeholder="" value="0" <?php if (isset($iAtendimentoCirurgicoPreOperatorio )) echo $rowAdmissao['EnAdCRemocaoJoia'] == '0' ? 'checked' : ''; ?> >
                                                            NÃO
                                                        </label>
                                                    </div>
                                                </div>
                                                <div id="textAcessoriosViwer" class="<?php if (isset($iAtendimentoCirurgicoPreOperatorio )) echo $rowAdmissao['EnAdCRemocaoJoia'] == '1' ? '' : 'd-none'; ?>">
                                                    <textarea id="textAcessorios" name="textAcessorios" class="form-control" rows="4" cols="4" maxLength="150" placeholder="" ><?php echo isset($iAtendimentoCirurgicoPreOperatorio) ? $rowAdmissao['EnAdCRemocaoJoiaDescricao'] : ''; ?></textarea>
                                                    <small class="text-muted form-text">
                                                        Máx. 150 caracteres<br>
                                                        <span id="caracteresInputAcessorios"></span>
                                                    </small>
                                                </div>
                                            </div>

                                            <div class="col-lg-3">
                                                <label>Doenças Prévias</label>
                                                <div class="col-lg-12 row options">
                                                    <select id="doencas" name="doencas" class="select-search">
                                                        <option value=''>selecione</option>
                                                        <option value='RC' <?php if (isset($iAtendimentoCirurgicoPreOperatorio )) echo $rowAdmissao['EnAdCDoencaPrevia'] == 'RC' ? 'selected' : ''; ?> >Doenças respiratórias crônicas</option>
                                                        <option value='CC' <?php if (isset($iAtendimentoCirurgicoPreOperatorio )) echo $rowAdmissao['EnAdCDoencaPrevia'] == 'CC' ? 'selected' : ''; ?> >Doenças cardíacas crônicas</option>
                                                        <option value='DI' <?php if (isset($iAtendimentoCirurgicoPreOperatorio )) echo $rowAdmissao['EnAdCDoencaPrevia'] == 'DI' ? 'selected' : ''; ?> >Diabetes</option>
                                                        <option value='DC' <?php if (isset($iAtendimentoCirurgicoPreOperatorio )) echo $rowAdmissao['EnAdCDoencaPrevia'] == 'DC' ? 'selected' : ''; ?> >Portador de doenças cromossônicas</option>
                                                        <option value='DF' <?php if (isset($iAtendimentoCirurgicoPreOperatorio )) echo $rowAdmissao['EnAdCDoencaPrevia'] == 'DF' ? 'selected' : ''; ?> >Portador de doenças de fragilidade imunológicas</option>
                                                        <option value='IM' <?php if (isset($iAtendimentoCirurgicoPreOperatorio )) echo $rowAdmissao['EnAdCDoencaPrevia'] == 'IM' ? 'selected' : ''; ?> >Imunossupressão</option>
                                                        <option value='DR' <?php if (isset($iAtendimentoCirurgicoPreOperatorio )) echo $rowAdmissao['EnAdCDoencaPrevia'] == 'DR' ? 'selected' : ''; ?> >Doenças renais crônicas em estágio avançado (3, 4 e 5)</option>
                                                        <option value='HA' <?php if (isset($iAtendimentoCirurgicoPreOperatorio )) echo $rowAdmissao['EnAdCDoencaPrevia'] == 'HA' ? 'selected' : ''; ?> >HAS</option>
                                                        <option value='OU' <?php if (isset($iAtendimentoCirurgicoPreOperatorio )) echo $rowAdmissao['EnAdCDoencaPrevia'] == 'OU' ? 'selected' : ''; ?> >Outros</option>
                                                    </select>
                                                </div>
                                                <div id="textDoencasViwer" class="<?php if (isset($iAtendimentoCirurgicoPreOperatorio )) echo $rowAdmissao['EnAdCDoencaPrevia'] == 'OU' ? '' : 'd-none'; ?> ">
                                                    <textarea id="textDoencas" name="textDoencas" class="form-control" rows="4" cols="4" maxLength="150" placeholder="" ><?php echo isset($iAtendimentoCirurgicoPreOperatorio) ? $rowAdmissao['EnAdCDoencaPreviaDescricao'] : ''; ?></textarea>
                                                    <small class="text-muted form-text">
                                                        Máx. 150 caracteres<br>
                                                        <span id="caracteresInputDoencas"></span>
                                                    </small>
                                                </div>
                                            </div>

                                            <div class="col-lg-3">
                                                <label>Área Operatória Tricotomia</label>
                                                <div class="col-lg-12 row options">
                                                    <div class="col-lg-3 form-check form-check-inline">
                                                        <label class="form-check-label">
                                                            <input type="radio" name="tricotomia" class="tricotomia form-input-styled" placeholder="" value="1" <?php if (isset($iAtendimentoCirurgicoPreOperatorio )) echo $rowAdmissao['EnAdCAreaOperatoria'] == '1' ? 'checked' : ''; ?> >
                                                            SIM
                                                        </label>
                                                    </div>
                                                    <div class="col-lg-3 form-check form-check-inline">
                                                        <label class="form-check-label">
                                                            <input type="radio" name="tricotomia" class="tricotomia form-input-styled" placeholder="" value="0" <?php if (isset($iAtendimentoCirurgicoPreOperatorio )) echo $rowAdmissao['EnAdCAreaOperatoria'] == '0' ? 'checked' : ''; ?> >
                                                            NÃO
                                                        </label>
                                                    </div>
                                                </div>
                                                <div id="textTricotomiaViwer" class="<?php if (isset($iAtendimentoCirurgicoPreOperatorio )) echo $rowAdmissao['EnAdCAreaOperatoria'] == '1' ? '' : 'd-none'; ?>">
                                                    <textarea id="textTricotomia" name="textTricotomia" class="form-control" rows="4" cols="4" maxLength="150" placeholder="" ><?php echo isset($iAtendimentoCirurgicoPreOperatorio) ? $rowAdmissao['EnAdCAreaOperatoriaDescricao'] : ''; ?></textarea>
                                                    <small class="text-muted form-text">
                                                        Máx. 150 caracteres<br>
                                                        <span id="caracteresInputTricotomia"></span>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- linha 3 -->
                                        <div class="col-lg-12 mb-3 row">
                                            <!-- titulos -->
                                            <div class="col-lg-3">
                                                <label>Local da Cirurgia</label>
                                            </div>
                                            <div class="col-lg-3">
                                                <label>Lado</label>
                                            </div>
                                            <div class="col-lg-3">
                                                <label>Acesso Venoso</label>
                                            </div>
                                            <div class="col-lg-3">
                                                <label>Esvaziamento Vesical</label>
                                            </div>
                                            
                                            <!-- campos -->                                            
                                            <div class="col-lg-3">
                                                <input type="text" name="local" id="local" class="form-control" maxLength="100" value="<?php echo isset($iAtendimentoCirurgicoPreOperatorio) ? $rowAdmissao['EnAdCLocalCirurgia'] : ''; ?>">
                                            </div>

                                            <div class="col-lg-3">
                                                <input type="text" name="lado" id="lado" class="form-control" maxLength="60" value="<?php echo isset($iAtendimentoCirurgicoPreOperatorio) ? $rowAdmissao['EnAdCLado'] : ''; ?>">
                                            </div>

                                            <div class="col-lg-3">
                                                <button id="venosoBTN" class="btn btn-lg btn-principal p-0 m-0" style="width:40px; height:35px;">
                                                    <i class='icon-search4' title='Pesquisar'></i>
                                                </button>
                                            </div>

                                            <div class="col-lg-3">
                                                <div class="col-lg-12 row options">
                                                    <div class="col-lg-3 form-check form-check-inline">
                                                        <label class="form-check-label">
                                                            <input type="radio" name="esvaziamento" class="esvaziamento form-input-styled" placeholder="" value="1" <?php if (isset($iAtendimentoCirurgicoPreOperatorio )) echo $rowAdmissao['EnAdCEsvaziamentoVesical'] == '1' ? 'checked' : ''; ?> >
                                                            SIM
                                                        </label>
                                                    </div>
                                                    <div class="col-lg-3 form-check form-check-inline">
                                                        <label class="form-check-label">
                                                            <input type="radio" name="esvaziamento" class="esvaziamento form-input-styled" placeholder="" value="0" <?php if (isset($iAtendimentoCirurgicoPreOperatorio )) echo $rowAdmissao['EnAdCEsvaziamentoVesical'] == '0' ? 'checked' : ''; ?> >
                                                            NÃO
                                                        </label>
                                                    </div>
                                                </div>
                                                <div id="textEsvaziamentoViwer" class="<?php if (isset($iAtendimentoCirurgicoPreOperatorio )) echo $rowAdmissao['EnAdCEsvaziamentoVesical'] == '1' ? '' : 'd-none'; ?> position-absolute" style="width:100%;">
                                                    <textarea id="textEsvaziamento" name="textEsvaziamento" class="form-control" rows="4" cols="4" maxLength="150" placeholder="" ><?php echo isset($iAtendimentoCirurgicoPreOperatorio) ? $rowAdmissao['EnAdCEsvaziamentoVesicalDescricao'] : ''; ?></textarea>
                                                    <small class="text-muted form-text">
                                                        Máx. 150 caracteres<br>
                                                        <span id="caracteresInputEsvaziamento"></span>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- linha 4 -->
                                        <div class="col-6 mb-3 row m-0 p-0 py-2">
                                            <!-- titulos -->
                                            <div class="col-lg-6">
                                                <label>Termo de Consentimento para Cirurgia</label>
                                            </div>
                                            <div class="col-lg-6">
                                                <label>Exames Complementares de Imagens</label>
                                            </div>

                                            <!-- campos -->
                                            <div class="col-lg-6 row mr-2">
                                                <div class="col-lg-10">
                                                    <input type="text" class="form-control" id="termo" readonly placeholder='Anexar aquirvo...'>
                                                </div>
                                                <div class="col-lg-2 p-0 m-0">
                                                    <button id='termoBTN' class="btn btn-lg btn-principal p-0 m-0" style="width:40px; height:35px;">
                                                        <i class='icon-attachment' title='Pesquisar'></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="col-lg-6 row">
                                                <div class="col-lg-10">
                                                    <input type="text" class="form-control" id="exames" readonly placeholder='Anexar aquirvo...'>
                                                </div>
                                                <div class="col-lg-2 p-0 m-0">
                                                    <button id='examesBTN' class="btn btn-lg btn-principal p-0 m-0" style="width:40px; height:35px;">
                                                        <i class='icon-attachment' title='Pesquisar'></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>

                            </div>
                            <div class="card">
                                <div class=" card-body row">
                                    <div class="col-lg-12">
                                        <div class="form-group" style="margin-bottom:0px;">
                                            <button class="btn btn-lg btn-success mr-1 salvarAdmissao" >Salvar</button>
                                            <button type="button" class="btn btn-lg btn-secondary mr-1">Imprimir</button>
                                            <a href='atendimentoHospitalarListagem.php' class='btn btn-basic' role='button'>Voltar</a>
                                        </div>
                                    </div>
                                </div>  
                            </div>

							<!--Modal-->
                            <div id="page-modal-acesso" class="custon-modal">
                                <div class="custon-modal-container" style="max-width: 1000px">
                                    <div class="card custon-modal-content">
                                        <div class="custon-modal-title mb-2" style="background-color: #466d96; color: #ffffff">
                                            <p class="h5">Acesso Venoso</p>
                                            <i id="modal-acesso-close-x" class="fab-icon-open icon-cross2 p-3" style="cursor: pointer"></i>
                                        </div>
                                        <div class="px-0" style="overflow-y: scroll;">
                                            <div class="d-flex flex-row">
                                                <div class="col-lg-12">
                                                    <form id="novoAcessoVenoso" name="novoAcessoVenoso" method="POST" class="form-validate-jquery">
                                                        <!-- linha 1 -->
                                                        <div class="col-lg-12 m-0 p-0 mb-3 row">
                                                            <!-- titulos -->
                                                            <div class="col-lg-3">
                                                                <label>Data / Hora</label>
                                                            </div>
                                                            <div class="col-lg-3">
                                                                <label>Local de punção</label>
                                                            </div>
                                                            <div class="col-lg-2">
                                                                <label>Tipo/Calibre</label>
                                                            </div>
                                                            <div class="col-lg-3">
                                                                <label>Responsável Técnico</label>
                                                            </div>
                                                            <div class="col-lg-1">
                                                            </div>
                                                            
                                                            <!-- campos -->
                                                            <div class="col-lg-3">
                                                                <input id="dataHoraAcessoVenoso" class="form-control" type="datetime-local" name="dataHoraAcessoVenoso">
                                                            </div>

                                                            <div class="col-lg-3">
                                                                <input id="localPuncaoAcessoVenoso" class="form-control" type="text" name="localPuncaoAcessoVenoso">
                                                            </div>

                                                            <div class="col-lg-2">
                                                                <input id="calibreAcessoVenoso" class="form-control" type="text" name="calibreAcessoVenoso">
                                                            </div>

                                                            <div class="col-lg-3">
                                                                <input id="responsavelAcessoVenoso" class="form-control" type="text" name="responsavelAcessoVenoso">
                                                            </div>
                                                            <div class="col-lg-1">
                                                                <button id="addAcesso" class="btn btn-lg btn-principal p-0 m-0" style="width:50px; height:35px;">
                                                                    <i class="fab-icon-open icon-add-to-list p-0" style="cursor: pointer; color: black"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </form>

                                                    
                                                    <table class="table" id="tblAcessoVenoso">
                                                        <thead>
                                                            <tr class="bg-slate">
                                                                <th>Item</th>
                                                                <th>Data/Hora</th>
                                                                <th>Local</th>
                                                                <th>Tipo</th>										
                                                                <th>Responsável</th>
                                                                <th class="text-center">Ações</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="dataAcessoVenoso">

                                                        </tbody>
                                                    </table>
                                                    
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="page-modal-concentimento" class="custon-modal">
                                <div class="custon-modal-container" style="max-width: 1000px">
                                    <div class="card custon-modal-content">
                                        <div class="custon-modal-title mb-2" style="background-color: #466d96; color: #ffffff">
                                            <p class="h5">Termo de Consentimento para Cirurgia</p>
                                            <i id="modal-concentimento-close-x" class="fab-icon-open icon-cross2 p-3" style="cursor: pointer"></i>
                                        </div>
                                        <div class="px-0" style="overflow-y: scroll;">
                                            <div class="d-flex flex-row">
                                                <div class="col-lg-12">
                                                    <form id="novoConsentimento" name="novoConsentimento" method="POST" class="form-validate-jquery">
                                                        <!-- linha 1 -->
                                                        <div class="col-lg-12 mt-2 m-0 p-0 mb-3 row">
                                                            <!-- titulos -->
                                                            <div class="col-lg-3">
                                                                <label>Data/Hora</label>
                                                            </div>
                                                            <div class="col-lg-5">
                                                                <label>Descrição</label>
                                                            </div>
                                                            <div class="col-lg-3">
                                                                <label>Arquivo</label>
                                                            </div>
                                                            <div class="col-lg-1">
                                                                <!-- btn -->
                                                            </div>
                                                            
                                                            <!-- campos -->
                                                            <div class="col-lg-3">
                                                                <input id="dataHoraConsentimento" class="form-control" type="datetime-local" name="dataHoraConsentimento">
                                                            </div>
                                                            <div class="col-lg-5">
                                                                <input id="descricaoConsentimento" class="form-control" type="text" name="descricaoConsentimento">
                                                            </div>
                                                            <div class="col-lg-3">
                                                                <input id="arquivoTermoConsentimento" class="form-control" type="file" name="arquivoTermoConsentimento">
                                                            </div>

                                                            <div class="col-lg-1">
                                                                <button id="addConsentimento" class="btn btn-lg btn-principal p-0 m-0" style="width:50px; height:35px;">
                                                                    <i class="fab-icon-open icon-add-to-list p-0" style="cursor: pointer; color: black"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </form>

                                                    
                                                    <table class="table" id="tblConcentimento">
                                                        <thead>
                                                            <tr class="bg-slate">
                                                                <th>Item</th>
                                                                <th>Data/Hora</th>
                                                                <th>Descrição</th>
                                                                <th class="text-center">Ações</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="dataTermoConsentimento">

                                                        </tbody>
                                                    </table>
                                                    
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="page-modal-exames" class="custon-modal">
                                <div class="custon-modal-container" style="max-width: 1000px">
                                    <div class="card custon-modal-content">
                                        <div class="custon-modal-title mb-2" style="background-color: #466d96; color: #ffffff">
                                            <p class="h5">Exames Complementares de Imagens</p>
                                            <i id="modal-exames-close-x" class="fab-icon-open icon-cross2 p-3" style="cursor: pointer"></i>
                                        </div>
                                        <div class="px-0" style="overflow-y: scroll;">
                                            <div class="d-flex flex-row">
                                                <div class="col-lg-12">
                                                    <form id="novoExamesComplementares" name="novoExamesComplementares" method="POST" class="form-validate-jquery">
                                                        <!-- linha 1 -->
                                                        <div class="col-lg-12 mt-2 p-0 mb-3 row">
                                                            <!-- titulos -->
                                                            <div class="col-lg-3">
                                                                <label>Data/Hora</label>
                                                            </div>
                                                            <div class="col-lg-5">
                                                                <label>Descrição</label>
                                                            </div>
                                                            <div class="col-lg-3">
                                                                <label>Arquivo</label>
                                                            </div>
                                                            <div class="col-lg-1">
                                                                <!-- btn -->
                                                            </div>
                                                            
                                                            <!-- campos -->
                                                            <div class="col-lg-3">
                                                                <input id="dataHoraExame" class="form-control" type="datetime-local" name="dataHoraExame">
                                                            </div>

                                                            <div class="col-lg-5">
                                                                <input id="descricaoExame" class="form-control" type="text" name="descricaoExame">
                                                            </div>

                                                            <div class="col-lg-3">
                                                                <input id="arquivoExame" class="form-control" type="file" name="arquivoExame">
                                                            </div>

                                                            <div class="col-lg-1">
                                                                <button id="addExame" class="btn btn-lg btn-principal p-0 m-0" style="width:50px; height:35px;">
                                                                    <i class="fab-icon-open icon-add-to-list p-0" style="cursor: pointer; color: black"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </form>

                                                    
                                                    <table class="table" id="tblExame">
                                                        <thead>
                                                            <tr class="bg-slate">
                                                                <th>Item</th>
                                                                <th>Data/Hora</th>
                                                                <th>Descrição</th>
                                                                <th class="text-center">Ações</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="dataExame">

                                                        </tbody>
                                                    </table>
                                                    
                                                </div>
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