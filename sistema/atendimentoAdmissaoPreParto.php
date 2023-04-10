<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Admissão de Pré Parto';

include('global_assets/php/conexao.php');

$iAtendimentoId = isset($_POST['iAtendimentoId'])?$_POST['iAtendimentoId']:null;

if (isset($_SESSION['iAtendimentoId']) && $iAtendimentoId == null) {
	$iAtendimentoId = $_SESSION['iAtendimentoId'];
}
$_SESSION['iAtendimentoId'] = null;

if(!$iAtendimentoId){
	irpara("atendimentoHospitalarListagem.php");	
}

//admissao
$sql = "SELECT TOP(1) EnAdPId
FROM EnfermagemAdmissaoPreParto
WHERE EnAdPAtendimento = $iAtendimentoId
ORDER BY EnAdPId DESC";
$result = $conn->query($sql);
$rowAdmissao= $result->fetch(PDO::FETCH_ASSOC);


$iAtendimentoAdmissaoId = $rowAdmissao?$rowAdmissao['EnAdPId']:null;

$iAtendimentoAdmissaoPreParto = $rowAdmissao?$rowAdmissao['EnAdPId']:null;
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
$SituaChave = $row['SituaChave'];

//Essa consulta é para preencher o sexo
if ($row['ClienSexo'] == 'F'){
    $sexo = 'Feminino';
} else{
    $sexo = 'Masculino';
}


//se estive editando
if(isset($iAtendimentoAdmissaoPreParto ) && $iAtendimentoAdmissaoPreParto ){

	$sql = "SELECT *
			FROM EnfermagemAdmissaoPreParto
			WHERE EnAdPId = " . $iAtendimentoAdmissaoPreParto ;
	$result = $conn->query($sql);
	$rowAdmissao = $result->fetch(PDO::FETCH_ASSOC);
		
	$_SESSION['msg'] = array();

} 

if (isset($_POST['inputInicio'])) {

    try {
 
        if ($iAtendimentoAdmissaoPreParto) {

            $sql = "UPDATE EnfermagemAdmissaoPreParto SET 
                EnAdPAtendimento = :sAtendimento,
                EnAdPDataInicio = :sDataInicio,
                EnAdPHoraInicio = :sHoraInicio,
                EnAdPDataFim = :sDataFim,
                EnAdPHoraFim = :sHoraFim,
                EnAdPProfissional = :sProfissional,

                EnAdPPrevisaoAlta = :sPrevisaoAlta,
                EnAdPTipoInternacao = :sTipoInternacao,
                EnAdPEspecialidadeLeito = :sEspecialidadeLeito,
                EnAdPAla = :sAla,
                EnAdPQuarto = :sQuarto,
                EnAdPLeito = :sLeito,


                EnAdPPas = :sPas,
                EnAdPPad = :sPad,
                EnAdPFreqCardiaca = :sFreqCardiaca,
                EnAdPFreqRespiratoria = :sFreqRespiratoria,
                EnAdPTemperatura = :sTemperatura,
                EnAdPSPO = :sSPO,
                EnAdPHGT = :sHGT,
                EnAdPPeso = :sPeso,

                EnAdPMotivoInternacao = :sMotivoInternacao ,
                EnAdPIndicacaoCesariana = :sIndicacaoCesariana ,
                EnAdPIG = :sIG ,
                EnAdPDUM = :sDUM ,
                EnAdPIGDUM = :sIGDUM ,
                EnAdPDPP = :sDPP ,
                EnAdPGestacao = :sGestacao ,
                EnAdPAborto = :sAborto ,
                EnAdPPartoNatural = :sPartoNatural ,
                EnAdPPartoCesariana = :sPartoCesariana ,
                EnAdPNumConsultaPreNatal = :sNumConsultaPreNatal ,
                EnAdPMesInicioPreNatal = :sMesInicioPreNatal ,
                EnAdPFatorRH = :sFatorRH ,
                EnAdPHIV = :sHIV ,
                EnAdPVDRL = :sVDRL ,
                EnAdPHBsAg = :sHBsAg ,
                EnAdPVacinaPNI = :sVacinaPNI ,
                EnAdPComorbidade = :sComorbidade ,
                EnAdPComorbidadeDescricao = :sComorbidadeDescricao ,
                EnAdPIntercorrenciaGestacao = :sIntercorrenciaGestacao ,
                EnAdPIntercorrenciaGestacaoDescricao = :sIntercorrenciaGestacaoDescricao ,
                EnAdPAlergia = :sAlergia ,
                EnAdPAlergiaDescricao = :sAlergiaDescricao ,
                EnAdPUsoMedicamento = :sUsoMedicamento ,
                EnAdPUsoMedicamentoDescricao = :sUsoMedicamentoDescricao ,
                EnAdPMovimentoFetal = :sMovimentoFetal ,
                EnAdPContracao = :sContracao ,
                EnAdPFrequencia = :sFrequencia ,
                EnAdPBCFMIN = :sBCFMIN ,
                EnAdPBolsaRota = :sBolsaRota ,
                EnAdPBolsaRotaData = :sBolsaRotaData ,
                EnAdPBolsaRotaHora = :sBolsaRotaHora ,
                EnAdPEliminacaoFisiologica = :sEliminacaoFisiologica ,
                EnAdPEdemaMMII = :sEdemaMMII ,
                EnAdPClassificacaoEdema = :sClassificacaoEdema ,
                EnAdPAcompanhante = :sAcompanhante ,
                EnAdPAcompanhanteNome = :sAcompanhanteNome ,
                EnAdPObservacao = :sObservacao ,
                EnAdPUnidade = :sUnidade

                WHERE EnAdPId = :iAtendimentoAdmissao";
                
            $result = $conn->prepare($sql);
                    
            $result->execute(array(
                ':sAtendimento' => $iAtendimentoId,
                ':sDataInicio' => date('m/d/Y'),
                ':sDataFim' => date('m/d/Y'),
                ':sHoraInicio' => date('H:i'),
                ':sHoraFim' => date('H:i'),
                
                ':sPrevisaoAlta' => $_POST['inputPrevisaoAlta'] == "" ? null : $_POST['inputPrevisaoAlta'], 
                ':sTipoInternacao' => $_POST['inputTipoInternacao'] == "" ? null : $_POST['inputTipoInternacao'], 
                ':sEspecialidadeLeito' => $_POST['inputEspLeito'] == "" ? null : $_POST['inputEspLeito'],
                ':sAla' => $_POST['inputAla'] == "" ? null : $_POST['inputAla'], 
                ':sQuarto' => $_POST['inputQuarto'] == "" ? null : $_POST['inputQuarto'], 
                ':sLeito' => $_POST['inputLeito'] == "" ? null : $_POST['inputLeito'], 
                ':sProfissional' => $userId,

                ':sPas' => $_POST['inputSistolica'] == "" ? null : $_POST['inputSistolica'],
                ':sPad' => $_POST['inputDiatolica'] == "" ? null : $_POST['inputDiatolica'],
                ':sFreqCardiaca' => $_POST['inputCardiaca'] == "" ? null : $_POST['inputCardiaca'],
                ':sFreqRespiratoria' => $_POST['inputRespiratoria'] == "" ? null : $_POST['inputRespiratoria'],
                ':sTemperatura' => $_POST['inputTemperatura'] == "" ? null : $_POST['inputTemperatura'],
                ':sSPO' => $_POST['inputSPO'] == "" ? null : $_POST['inputSPO'],
                ':sHGT' => $_POST['inputHGT'] == "" ? null : $_POST['inputHGT'],
                ':sPeso' => $_POST['inputPeso'] == "" ? null : $_POST['inputPeso'],


                ':sMotivoInternacao' => $_POST['inputMotivoInternacao'] == "" ? null : $_POST['inputMotivoInternacao'],
                ':sIndicacaoCesariana' => $_POST['inputIndicacaoCesariana'] == "" ? null : $_POST['inputIndicacaoCesariana'] ,
                ':sIG' => $_POST['inputIG'] == "" ? null : $_POST['inputIG'] ,
                ':sDUM' => $_POST['inputDUM'] == "" ? null : $_POST['inputDUM'] ,
                ':sIGDUM' => $_POST['inputIGDUM'] == "" ? null : $_POST['inputIGDUM'] ,
                ':sDPP' => $_POST['inputDPP'] == "" ? null : $_POST['inputDPP'] ,
                ':sGestacao' => $_POST['inputG'] == "" ? null : $_POST['inputG'] ,
                ':sAborto' => $_POST['inputA'] == "" ? null : $_POST['inputA'] ,
                ':sPartoNatural' => $_POST['inputPN'] == "" ? null : $_POST['inputPN'] ,
                ':sPartoCesariana' => $_POST['inputPC'] == "" ? null : $_POST['inputPC'] ,
                ':sNumConsultaPreNatal' => $_POST['inputNumConsultaPreNatal'] == "" ? null : $_POST['inputNumConsultaPreNatal'] ,
                ':sMesInicioPreNatal' => $_POST['inputMesInicioPreNatal'] == "" ? null : $_POST['inputMesInicioPreNatal'] ,
                ':sFatorRH' => $_POST['cmbFatorRH'] == "" ? null : $_POST['cmbFatorRH'] ,
                ':sHIV' => $_POST['cmbHIV'] == "" ? null : $_POST['cmbHIV'] ,
                ':sVDRL' => $_POST['cmbVDRL'] == "" ? null : $_POST['cmbVDRL'] ,
                ':sHBsAg' => $_POST['cmbHBsAg'] == "" ? null : $_POST['cmbHBsAg'] ,
                ':sVacinaPNI' => $_POST['cmbVacinaPNI'] == "" ? null : $_POST['cmbVacinaPNI'] ,
                ':sComorbidade' => $_POST['cmbComorbidade'] == "" ? null : $_POST['cmbComorbidade'] ,
                ':sComorbidadeDescricao' => $_POST['inputComorbidadeDescricao'] == "" ? null : $_POST['inputComorbidadeDescricao'] ,
                ':sIntercorrenciaGestacao' => $_POST['inputIntercorrencias'] == "" ? null : $_POST['inputIntercorrencias'] ,
                ':sIntercorrenciaGestacaoDescricao' => $_POST['inputIntercorrenciasDescricao'] == "" ? null : $_POST['inputIntercorrenciasDescricao'] ,
                ':sAlergia' => $_POST['inputAlergia'] == "" ? null : $_POST['inputAlergia'] ,
                ':sAlergiaDescricao' => $_POST['inputAlergiaDescricao'] == "" ? null : $_POST['inputAlergiaDescricao'] ,
                ':sUsoMedicamento' => $_POST['inputUsoMedicamentos'] == "" ? null : $_POST['inputUsoMedicamentos'] ,
                ':sUsoMedicamentoDescricao' => $_POST['inputUsoMedDescricao'] == "" ? null : $_POST['inputUsoMedDescricao'] ,
                ':sMovimentoFetal' => $_POST['cmbMovimentosFetais'] == "" ? null : $_POST['cmbMovimentosFetais'] ,
                ':sContracao' => $_POST['cmbContracao'] == "" ? null : $_POST['cmbContracao'] ,
                ':sFrequencia' => $_POST['inputFrequencia'] == "" ? null : $_POST['inputFrequencia'] ,
                ':sBCFMIN' => $_POST['inputBCFMIN'] == "" ? null : $_POST['inputBCFMIN'] ,
                ':sBolsaRota' => $_POST['cmbBolsaRota'] == "" ? null : $_POST['cmbBolsaRota'] ,
                ':sBolsaRotaData' => $_POST['inputBolsaRotaData'] == "" ? null : $_POST['inputBolsaRotaData'] ,
                ':sBolsaRotaHora' => $_POST['inputBolsaRotaHora'] == "" ? null : $_POST['inputBolsaRotaHora'] ,
                ':sEliminacaoFisiologica' => $_POST['cmbEliminacoesFis'] == "" ? null : $_POST['cmbEliminacoesFis'] ,
                ':sEdemaMMII' => $_POST['cmbEdemaMMII'] == "" ? null : $_POST['cmbEdemaMMII'] ,
                ':sClassificacaoEdema' => $_POST['inputClassificacaoEdema'] == "" ? null : $_POST['inputClassificacaoEdema'] ,
                ':sAcompanhante' => $_POST['cmbAcompanhante'] == "" ? null : $_POST['cmbAcompanhante'] ,
                ':sAcompanhanteNome' => $_POST['inputNomeAcompanhante'] == "" ? null : $_POST['inputNomeAcompanhante'] ,
                ':sObservacao' => $_POST['inputObservacoes'] == "" ? null : $_POST['inputObservacoes'] ,
                ':sUnidade' => $_SESSION['UnidadeId'],

                ':iAtendimentoAdmissao' => $iAtendimentoAdmissaoId 
                ));


                //salvar evolucao de parto e n deixar editavel
                $sql = "UPDATE EnfermagemAdmissaoPrePartoEvolucao SET EnAPEEditavel = 0
                WHERE EnAPEAdmissaoPreParto = '$iAtendimentoAdmissaoPreParto'";

                $conn->query($sql);
    
                $_SESSION['msg']['titulo'] = "Sucesso";
                $_SESSION['msg']['mensagem'] = "Admissão alterada com sucesso!!!";
                $_SESSION['msg']['tipo'] = "success";
                $_SESSION['iAtendimentoId'] = $iAtendimentoId;
    
        } else {

            $sql = "INSERT INTO EnfermagemAdmissaoPreParto 
                (EnAdPAtendimento ,EnAdPDataInicio ,EnAdPHoraInicio ,EnAdPDataFim ,EnAdPHoraFim ,EnAdPPrevisaoAlta ,EnAdPTipoInternacao ,
                EnAdPEspecialidadeLeito ,EnAdPAla ,EnAdPQuarto ,EnAdPLeito ,EnAdPProfissional ,EnAdPPas ,EnAdPPad ,EnAdPFreqCardiaca ,
                EnAdPFreqRespiratoria ,EnAdPTemperatura ,EnAdPSPO ,EnAdPHGT ,EnAdPPeso ,EnAdPMotivoInternacao ,EnAdPIndicacaoCesariana ,
                EnAdPIG ,EnAdPDUM ,EnAdPIGDUM ,EnAdPDPP ,EnAdPGestacao ,EnAdPAborto ,EnAdPPartoNatural ,EnAdPPartoCesariana ,EnAdPNumConsultaPreNatal ,
                EnAdPMesInicioPreNatal ,EnAdPFatorRH ,EnAdPHIV ,EnAdPVDRL ,EnAdPHBsAg ,EnAdPVacinaPNI ,EnAdPComorbidade ,EnAdPComorbidadeDescricao ,
                EnAdPIntercorrenciaGestacao ,EnAdPIntercorrenciaGestacaoDescricao ,EnAdPAlergia ,EnAdPAlergiaDescricao ,EnAdPUsoMedicamento ,
                EnAdPUsoMedicamentoDescricao ,EnAdPMovimentoFetal ,EnAdPContracao ,EnAdPFrequencia ,EnAdPBCFMIN ,EnAdPBolsaRota ,EnAdPBolsaRotaData ,
                EnAdPBolsaRotaHora ,EnAdPEliminacaoFisiologica ,EnAdPEdemaMMII ,EnAdPClassificacaoEdema ,EnAdPAcompanhante ,EnAdPAcompanhanteNome ,
                EnAdPObservacao ,EnAdPUnidade)
			VALUES 
                (:sAtendimento ,:sDataInicio ,:sDataFim ,:sHoraInicio ,:sHoraFim ,:sPrevisaoAlta ,:sTipoInternacao ,:sEspecialidadeLeito ,:sAla ,
                :sQuarto ,:sLeito ,:sProfissional ,:sPas ,:sPad ,:sFreqCardiaca ,:sFreqRespiratoria ,:sTemperatura ,:sSPO ,:sHGT ,:sPeso ,
                :sMotivoInternacao ,:sIndicacaoCesariana ,:sIG ,:sDUM ,:sIGDUM ,:sDPP ,:sGestacao ,:sAborto ,:sPartoNatural ,:sPartoCesariana ,
                :sNumConsultaPreNatal ,:sMesInicioPreNatal ,:sFatorRH ,:sHIV ,:sVDRL ,:sHBsAg ,:sVacinaPNI ,:sComorbidade ,:sComorbidadeDescricao ,
                :sIntercorrenciaGestacao ,:sIntercorrenciaGestacaoDescricao ,:sAlergia ,:sAlergiaDescricao ,:sUsoMedicamento ,
                :sUsoMedicamentoDescricao ,:sMovimentoFetal ,:sContracao ,:sFrequencia ,:sBCFMIN ,:sBolsaRota ,:sBolsaRotaData ,:sBolsaRotaHora ,
                :sEliminacaoFisiologica ,:sEdemaMMII ,:sClassificacaoEdema ,:sAcompanhante ,:sAcompanhanteNome ,:sObservacao ,:sUnidade)";
			$result = $conn->prepare($sql);

            $result->execute(array(
                ':sAtendimento' => $iAtendimentoId,
                ':sDataInicio' => date('m/d/Y'),
                ':sDataFim' => date('m/d/Y'),
                ':sHoraInicio' => date('H:i'),
                ':sHoraFim' => date('H:i'),
                ':sPrevisaoAlta' => $_POST['inputPrevisaoAlta'] == "" ? null : $_POST['inputPrevisaoAlta'], 
                ':sTipoInternacao' => $_POST['inputTipoInternacao'] == "" ? null : $_POST['inputTipoInternacao'], 
                ':sEspecialidadeLeito' => $_POST['inputEspLeito'] == "" ? null : $_POST['inputEspLeito'],
                ':sAla' => $_POST['inputAla'] == "" ? null : $_POST['inputAla'], 
                ':sQuarto' => $_POST['inputQuarto'] == "" ? null : $_POST['inputQuarto'], 
                ':sLeito' => $_POST['inputLeito'] == "" ? null : $_POST['inputLeito'], 
                ':sProfissional' => $userId,
                ':sPas' => $_POST['inputSistolica'] == "" ? null : $_POST['inputSistolica'],
                ':sPad' => $_POST['inputDiatolica'] == "" ? null : $_POST['inputDiatolica'],
                ':sFreqCardiaca' => $_POST['inputCardiaca'] == "" ? null : $_POST['inputCardiaca'],
                ':sFreqRespiratoria' => $_POST['inputRespiratoria'] == "" ? null : $_POST['inputRespiratoria'],
                ':sTemperatura' => $_POST['inputTemperatura'] == "" ? null : $_POST['inputTemperatura'],
                ':sSPO' => $_POST['inputSPO'] == "" ? null : $_POST['inputSPO'],
                ':sHGT' => $_POST['inputHGT'] == "" ? null : $_POST['inputHGT'],
                ':sPeso' => $_POST['inputPeso'] == "" ? null : $_POST['inputPeso'],
                ':sMotivoInternacao' => $_POST['inputMotivoInternacao'] == "" ? null : $_POST['inputMotivoInternacao'],
                ':sIndicacaoCesariana' => $_POST['inputIndicacaoCesariana'] == "" ? null : $_POST['inputIndicacaoCesariana'] ,
                ':sIG' => $_POST['inputIG'] == "" ? null : $_POST['inputIG'] ,
                ':sDUM' => $_POST['inputDUM'] == "" ? null : $_POST['inputDUM'] ,
                ':sIGDUM' => $_POST['inputIGDUM'] == "" ? null : $_POST['inputIGDUM'] ,
                ':sDPP' => $_POST['inputDPP'] == "" ? null : $_POST['inputDPP'] ,
                ':sGestacao' => $_POST['inputG'] == "" ? null : $_POST['inputG'] ,
                ':sAborto' => $_POST['inputA'] == "" ? null : $_POST['inputA'] ,
                ':sPartoNatural' => $_POST['inputPN'] == "" ? null : $_POST['inputPN'] ,
                ':sPartoCesariana' => $_POST['inputPC'] == "" ? null : $_POST['inputPC'] ,
                ':sNumConsultaPreNatal' => $_POST['inputNumConsultaPreNatal'] == "" ? null : $_POST['inputNumConsultaPreNatal'] ,
                ':sMesInicioPreNatal' => $_POST['inputMesInicioPreNatal'] == "" ? null : $_POST['inputMesInicioPreNatal'] ,
                ':sFatorRH' => $_POST['cmbFatorRH'] == "" ? null : $_POST['cmbFatorRH'] ,
                ':sHIV' => $_POST['cmbHIV'] == "" ? null : $_POST['cmbHIV'] ,
                ':sVDRL' => $_POST['cmbVDRL'] == "" ? null : $_POST['cmbVDRL'] ,
                ':sHBsAg' => $_POST['cmbHBsAg'] == "" ? null : $_POST['cmbHBsAg'] ,
                ':sVacinaPNI' => $_POST['cmbVacinaPNI'] == "" ? null : $_POST['cmbVacinaPNI'] ,
                ':sComorbidade' => $_POST['cmbComorbidade'] == "" ? null : $_POST['cmbComorbidade'] ,
                ':sComorbidadeDescricao' => $_POST['inputComorbidadeDescricao'] == "" ? null : $_POST['inputComorbidadeDescricao'] ,
                ':sIntercorrenciaGestacao' => $_POST['inputIntercorrencias'] == "" ? null : $_POST['inputIntercorrencias'] ,
                ':sIntercorrenciaGestacaoDescricao' => $_POST['inputIntercorrenciasDescricao'] == "" ? null : $_POST['inputIntercorrenciasDescricao'] ,
                ':sAlergia' => $_POST['inputAlergia'] == "" ? null : $_POST['inputAlergia'] ,
                ':sAlergiaDescricao' => $_POST['inputAlergiaDescricao'] == "" ? null : $_POST['inputAlergiaDescricao'] ,
                ':sUsoMedicamento' => $_POST['inputUsoMedicamentos'] == "" ? null : $_POST['inputUsoMedicamentos'] ,
                ':sUsoMedicamentoDescricao' => $_POST['inputUsoMedDescricao'] == "" ? null : $_POST['inputUsoMedDescricao'] ,
                ':sMovimentoFetal' => $_POST['cmbMovimentosFetais'] == "" ? null : $_POST['cmbMovimentosFetais'] ,
                ':sContracao' => $_POST['cmbContracao'] == "" ? null : $_POST['cmbContracao'] ,
                ':sFrequencia' => $_POST['inputFrequencia'] == "" ? null : $_POST['inputFrequencia'] ,
                ':sBCFMIN' => $_POST['inputBCFMIN'] == "" ? null : $_POST['inputBCFMIN'] ,
                ':sBolsaRota' => $_POST['cmbBolsaRota'] == "" ? null : $_POST['cmbBolsaRota'] ,
                ':sBolsaRotaData' => $_POST['inputBolsaRotaData'] == "" ? null : $_POST['inputBolsaRotaData'] ,
                ':sBolsaRotaHora' => $_POST['inputBolsaRotaHora'] == "" ? null : $_POST['inputBolsaRotaHora'] ,
                ':sEliminacaoFisiologica' => $_POST['cmbEliminacoesFis'] == "" ? null : $_POST['cmbEliminacoesFis'] ,
                ':sEdemaMMII' => $_POST['cmbEdemaMMII'] == "" ? null : $_POST['cmbEdemaMMII'] ,
                ':sClassificacaoEdema' => $_POST['inputClassificacaoEdema'] == "" ? null : $_POST['inputClassificacaoEdema'] ,
                ':sAcompanhante' => $_POST['cmbAcompanhante'] == "" ? null : $_POST['cmbAcompanhante'] ,
                ':sAcompanhanteNome' => $_POST['inputNomeAcompanhante'] == "" ? null : $_POST['inputNomeAcompanhante'] ,
                ':sObservacao' => $_POST['inputObservacoes'] == "" ? null : $_POST['inputObservacoes'] ,
                ':sUnidade' => $_SESSION['UnidadeId']
            ));

            $_SESSION['msg']['titulo'] = "Sucesso";
            $_SESSION['msg']['mensagem'] = "Admissão inserida com sucesso!!!";
            $_SESSION['msg']['tipo'] = "success";
            $_SESSION['iAtendimentoId'] = $iAtendimentoId;
           
        }
        
    } catch (PDOException $e) {
        $_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro reportado com a Admissao!!!";
		$_SESSION['msg']['tipo'] = "error";		
		echo 'Error: ' . $e->getMessage();      
    }

    
    irpara("atendimentoAdmissaoPreParto.php");
    
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Admissão Pediátrica</title>

	<?php include_once("head.php"); ?>

    <!-- Theme JS files -->
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
	<script src="global_assets/js/demo_pages/form_select2.js"></script>
	<script src="global_assets/js/plugins/forms/selects/bootstrap_multiselect.js"></script>
	<script src="global_assets/js/demo_pages/form_multiselect.js"></script>

	<script src="global_assets/js/demo_pages/form_layouts.js"></script>
	<script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>

	<script src="global_assets/js/plugins/forms/inputs/inputmask.js"></script>
	<!-- /theme JS files -->	

	<!-- Validação -->
	<script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
	<script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
	<script src="global_assets/js/demo_pages/form_validation.js"></script>
	
	<script type="text/javascript">

		$(document).ready(function() {	

            getAdmissoesEvolucoes(); 
	     
			$('.salvarAdmissao').on('click', function(e){
				e.preventDefault();
				$( "#formAtendimentoAdmissaoPreParto" ).submit();
			})

            $('#inserirEvolucaoAdmissaoPreParto').on('click', function(e){
                e.preventDefault();
                let cmbRealizadoToque = $('#cmbRealizadoToque').val()
                let inputDilatacao = $('#inputDilatacao').val()
                let inputApagamento = $('#inputApagamento').val()
                let cmbApresentacao = $('#cmbApresentacao').val()
                let cmbPlanoLee = $('#cmbPlanoLee').val()
                let inputLiquido = $('#inputLiquido').val()
                let cmbMeconio = $('#cmbMeconio').val()

                $.ajax({
                    type: 'POST',
                    url: 'filtraAtendimento.php',
                    dataType: 'json',

                    data: {
                        'tipoRequest': 'INCLUIREVOLUCAOADMISSAOPREPARTO',
                        'tipo' : 'INSERT',
                        'idAdmissao' : <?php echo isset($iAtendimentoAdmissaoId) ? $iAtendimentoAdmissaoId: 0 ; ?>,				
                        'cmbRealizadoToque' : cmbRealizadoToque,				
                        'inputDilatacao' : inputDilatacao,				
                        'inputApagamento' : inputApagamento,				
                        'cmbApresentacao' : cmbApresentacao,				
                        'cmbPlanoLee' : cmbPlanoLee,				
                        'inputLiquido' : inputLiquido,				
                        'cmbMeconio' : cmbMeconio		
                    },
                    success: function(response) {
                        if(response.status == 'success'){
                            alerta(response.titulo, response.menssagem, response.status)
                            zerarAdmissaoEvolucao()
                            getAdmissoesEvolucoes()

                        }else{
                            alerta(response.titulo, response.menssagem, response.status)
                        }
                    }
                });

            })

            $('#editarEvolucaoAdmissaoPreParto').on('click', function(e){
                e.preventDefault();
                let idEvolucao = $('#idEvolucao').val()
                let cmbRealizadoToque = $('#cmbRealizadoToque').val()
                let inputDilatacao = $('#inputDilatacao').val()
                let inputApagamento = $('#inputApagamento').val()
                let cmbApresentacao = $('#cmbApresentacao').val()
                let cmbPlanoLee = $('#cmbPlanoLee').val()
                let inputLiquido = $('#inputLiquido').val()
                let cmbMeconio = $('#cmbMeconio').val()

                $.ajax({
                    type: 'POST',
                    url: 'filtraAtendimento.php',
                    dataType: 'json',

                    data: {
                        'tipoRequest': 'INCLUIREVOLUCAOADMISSAOPREPARTO',
                        'tipo' : 'UPDATE',
                        'idEvolucao' : idEvolucao,
                        'idAdmissao' : <?php echo isset($iAtendimentoAdmissaoId) ? $iAtendimentoAdmissaoId: 0 ; ?>,				
                        'cmbRealizadoToque' : cmbRealizadoToque,				
                        'inputDilatacao' : inputDilatacao,				
                        'inputApagamento' : inputApagamento,				
                        'cmbApresentacao' : cmbApresentacao,				
                        'cmbPlanoLee' : cmbPlanoLee,				
                        'inputLiquido' : inputLiquido,				
                        'cmbMeconio' : cmbMeconio		
                    },
                    success: function(response) {
                        if(response.status == 'success'){
                            alerta(response.titulo, response.menssagem, response.status)
                            zerarAdmissaoEvolucao()
                            getAdmissoesEvolucoes()

                        }else{
                            alerta(response.titulo, response.menssagem, response.status)
                        }
                    }
                });

            })

			$(".caracteresinputComorbidadeDescricao").text(' - ' + (150 - $("#inputComorbidadeDescricao").val().length) + ' restantes');
			$(".caracteresinputIntercorrenciasDescricao").text(' - ' + (150 - $("#inputIntercorrenciasDescricao").val().length) + ' restantes');
			$(".caracteresinputAlergiaDescricao").text(' - ' + (150 - $("#inputAlergiaDescricao").val().length) + ' restantes');
			$(".caracteresinputUsoMedDescricao").text(' - ' + (150 - $("#inputUsoMedDescricao").val().length) + ' restantes');
			$(".caracteresinputObservacoes").text(' - ' + (100 - $("#inputObservacoes").val().length) + ' restantes');

		}); //document.ready

        function getAdmissoesEvolucoes() {

            $.ajax({
                type: 'POST',
                url: 'filtraAtendimento.php',
                dataType: 'json',
                data:{
                    'tipoRequest': 'GETADMISSOESPREPARTO',
                    'idAdmissao' : <?php echo isset($iAtendimentoAdmissaoId) ? $iAtendimentoAdmissaoId: 0 ;?>,
                },
                success: function(response) {

                    $('#dataAdmissao').html('');
                    let HTML = ''
                    
                    response.forEach(item => {

                        let situaChave = $("#atendimentoSituaChave").val();
                        let copiar = `<a class='list-icons-item mr-2 ' style='color: black; cursor:pointer' onclick='copiarAdmissaoEvolucao(\"${item.id}\")'><i class='icon-files-empty' title='Copiar Evolução'></i></a>`; 
                        let editar = `<a class='list-icons-item mr-2 ' style='color: black; cursor:pointer'  onclick='editarAdmissaoEvolucao(\"${item.id}\")' class='list-icons-item' ><i class='icon-pencil7' title='Editar Evolução'></i></a>`;
                        let exc = `<a style='color: black; cursor:pointer' onclick='excluirAdmissaoEvolucao(\"${item.id}\")' class='list-icons-item'><i class='icon-bin' title='Excluir Evolução'></i></a>`;
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
                            <td class="text-left">${item.dilatacao}</td>
                            <td class="text-left">${item.apagamento}</td>
                            <td class="text-left">${item.planoLee}</td>
                            <td class="text-left">${item.liquido}</td>
                            <td class="text-left">${item.meconio}</td>
                            <td class="text-center">${acoes}</td>
                        </tr>`

                    })
                    $('#dataAdmissao').html(HTML).show();
                }
            });	
            
        }

        function editarAdmissaoEvolucao(id) {

            $.ajax({
                type: 'POST',
                url: 'filtraAtendimento.php',
                dataType: 'json',
                data:{
                    'tipoRequest': 'GETADMISSAOEVOLUCAO',
                    'id' : id
                },
                success: function(response) {
                    
                    $('#idEvolucao').val(response.EnAPEId)
                    $('#cmbRealizadoToque').val(response.EnAPERealizadoToque).change()
                    $('#inputDilatacao').val(response.EnAPEDilatacao)
                    $('#inputApagamento').val(response.EnAPEApagamento)
                    $('#cmbApresentacao').val(response.EnAPEApresentacao).change()
                    $('#cmbPlanoLee').val(response.EnAPEPlano).change()
                    $('#inputLiquido').val(response.EnAPELiquido)
                    $('#cmbMeconio').val(response.EnAPEMeconio).change()

                    $("#inserirEvolucaoAdmissaoPreParto").css('display', 'none');
                    $("#editarEvolucaoAdmissaoPreParto").css('display', 'block');		
                }
            });

        }

        function copiarAdmissaoEvolucao(id) {

            zerarAdmissaoEvolucao()

            $.ajax({
                type: 'POST',
                url: 'filtraAtendimento.php',
                dataType: 'json',
                data:{
                    'tipoRequest': 'GETADMISSAOEVOLUCAO',
                    'id' : id
                },
                success: function(response) {
                    
                    $('#idAdmissaoEvolucao').val(response.EnAPEId)
                    $('#cmbRealizadoToque').val(response.EnAPERealizadoToque).change()
                    $('#inputDilatacao').val(response.EnAPEDilatacao)
                    $('#inputApagamento').val(response.EnAPEApagamento)
                    $('#cmbApresentacao').val(response.EnAPEApresentacao).change()
                    $('#cmbPlanoLee').val(response.EnAPEPlano).change()
                    $('#inputLiquido').val(response.EnAPELiquido)
                    $('#cmbMeconio').val(response.EnAPEMeconio).change()	
                }
            });
  
        }

        function excluirAdmissaoEvolucao(id) {
            confirmaExclusaoAjax('filtraAtendimento.php', 'Excluir Evolução?', 'DELETEADMISSAOPREPARTO', id, getAdmissoesEvolucoes)
        }

        function zerarAdmissaoEvolucao() {
            $('#idEvolucao').val('')
            $('#cmbRealizadoToque').val('').change()
            $('#inputDilatacao').val('')
            $('#inputApagamento').val('')
            $('#cmbApresentacao').val('').change()
            $('#cmbPlanoLee').val('').change()
            $('#inputLiquido').val('')
            $('#cmbMeconio').val('').change()

            $("#inserirEvolucaoAdmissaoPreParto").css('display', 'block');
            $("#editarEvolucaoAdmissaoPreParto").css('display', 'none');
        }

        $(function() {
			$('.btn-grid').click(function(){
				$('.btn-grid').removeClass('active');
				$(this).addClass('active');     
			});
		});

        function mudarGrid(grid){
			if (grid == 'pre-parto') {				
				$(".box-pre-parto").css('display', 'block');
				$(".box-evolucao-parto").css('display', 'none');
			} else if (grid == 'evolucao-parto') {
				$(".box-evolucao-parto").css('display', 'block');
				$(".box-pre-parto").css('display', 'none');
			}
		}

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

        function selecionaAlergiaDescricaoAd(tipo1) {
            if (tipo1 == 1){	
                document.getElementById('dadosAlergiad').style.display = "block";
            } else {						
                document.getElementById('dadosAlergiad').style.display = "none";
            }
        }

        function selecionaInputIntercorrenciasDescricao(tipo) {
            if (tipo == 1){
                document.getElementById('dadosIntercorrencias').style.display = "block";	
            } else {			
                document.getElementById('dadosIntercorrencias').style.display = "none";		
            }
        }
     
        function selecionaUsoMedicamentosDescricao(tipo2) {
            if (tipo2 == 1){	
                document.getElementById('dadosUsoMedDescricao').style.display = "block";
            } else {						
                document.getElementById('dadosUsoMedDescricao').style.display = "none";
            }
        }

	</script>

    <style>

        .fieldset-border {
            border: 1px groove #ddd !important;
            padding: 0 1.4em 1.4em 1.4em !important;
            margin: 0 0 1.5em 0 !important;
        }

        .fieldset-border .legend-border {
            font-size: 1.2em !important;
            text-align: left !important;
            width: auto;
            padding: 0 10px;
            border-bottom: none;
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
						<form name="formAtendimentoAdmissaoPreParto" id="formAtendimentoAdmissaoPreParto" method="post">
							<?php
								echo "<input type='hidden' id='iAtendimentoId' name='iAtendimentoId' value='$iAtendimentoId' />";
                                echo "<input type='hidden' id='atendimentoSituaChave' value='".$_SESSION['SituaChave']."' />";
                            ?>
							<div class="card">

                            <div class="col-md-12">
                                <div class="row">

                                    <div class="col-md-6" style="text-align: left;">

                                        <div class="card-header header-elements-inline">
                                            <h3 class="card-title"><b>ADMISSÃO PRÉ PARTO</b></h3>
                                        </div>
            
                                    </div>

                                    <div class="col-md-6" style="text-align: right;">
                                        <div class="form-group" style="margin:20px;" >
                                            <?php 
                                                if (isset($SituaChave) && $SituaChave != "ATENDIDO") {
                                                    echo "<button class='btn btn-lg btn-success mr-1 salvarAdmissao' >Salvar</button>";
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
                                    <div class="col-lg-11">	
                                        <button type="button" class="btn-grid btn btn-lg btn-outline-secondary btn-lg active mr-2 " onclick="mudarGrid('pre-parto')" style="margin-left: -10px;" >Admissao Pré Parto</button>
                                        
                                        <?php if ($iAtendimentoAdmissaoId) { ?>
                                            <button type="button" class="btn-grid btn btn-lg btn-outline-secondary btn-lg mr-2 " onclick="mudarGrid('evolucao-parto')" >Evolução do Parto</button>
                                        <?php } ?>
                                        
                                        <button type="button" class="btn-grid btn btn-lg btn-outline-secondary btn-lg itemLink" data-tipo='admissaoRN' >Admissão RN</button>
                                    </div>
                                </div>                                
                                
                            </div>

                            <div class="box-pre-parto" style="display: block;">


                                <div class="card">

                                    <div class="card-header header-elements-inline">
                                        <h3 class="card-title font-weight-bold">Admissão Pré Parto</h3>
                                    </div>

                                    <div class="card-body">

                                        <div class="row mb-3"> 
                                            <div class="col-lg-5">
                                                <div class="form-group">
                                                    <label for="inputMotivoInternacao">Motivo da internação</label>                                               
                                                    <input type="text" onKeyUp="" maxLength="80" id="inputMotivoInternacao" name="inputMotivoInternacao" class="form-control" placeholder="" value="<?php if (isset($iAtendimentoAdmissaoId )) echo $rowAdmissao['EnAdPMotivoInternacao']; ?>">                                                
                                                </div>
                                            </div>

                                            <div class="col-lg-7 ">
                                            <div class="form-group">
                                                    <label for="inputIndicacaoCesariana">Indicação para Cesariana</label>                                               
                                                    <input type="text" onKeyUp="" maxLength="80" id="inputIndicacaoCesariana" name="inputIndicacaoCesariana" class="form-control" placeholder="" value="<?php if (isset($iAtendimentoAdmissaoId )) echo $rowAdmissao['EnAdPIndicacaoCesariana']; ?>">                                                
                                                </div>
                                                
                                            </div>
                                        </div>

                                        <div class="row mb-3">                                       
                                            <div class="col-lg-6 row">

                                                <div class="col-lg-3">
                                                    <div class="form-group">
                                                        <label for="inputIG">IG (1ª USG)</label>
                                                        <input type="text" onKeyUp="" maxLength="30" id="inputIG" name="inputIG" class="form-control" placeholder="" value="<?php if (isset($iAtendimentoAdmissaoId )) echo $rowAdmissao['EnAdPIG']; ?>">                                                

                                                    </div>
                                                </div>

                                                <div class="col-lg-3">
                                                    <div class="form-group">
                                                        <label for="inputDUM">DUM</label>
                                                        <input type="date" onKeyUp="" id="inputDUM" name="inputDUM" class="form-control" placeholder="" value="<?php if (isset($iAtendimentoAdmissaoId )) echo $rowAdmissao['EnAdPDUM']; ?>">                                                

                                                    </div>
                                                </div>

                                                <div class="col-lg-3">
                                                    <div class="form-group">
                                                        <label for="inputIGDUM">IG-DUM</label>
                                                        <input type="text" onKeyUp="" maxLength="30" id="inputIGDUM" name="inputIGDUM" class="form-control" placeholder="" value="<?php if (isset($iAtendimentoAdmissaoId )) echo $rowAdmissao['EnAdPIGDUM']; ?>">                                                

                                                    </div>
                                                </div>

                                                <div class="col-lg-3">
                                                    <div class="form-group">
                                                        <label for="inputDPP">DPP</label>
                                                        <input type="date" onKeyUp="" id="inputDPP" name="inputDPP" class="form-control" placeholder="" value="<?php if (isset($iAtendimentoAdmissaoId )) echo $rowAdmissao['EnAdPDPP']; ?>">                                                

                                                    </div>
                                                </div>
                                                
                                            </div>

                                            <div class="col-lg-6 row">

                                                <div class="col-lg-2">
                                                    <div class="form-group">
                                                        <label for="inputG">G</label>
                                                        <input type="number" onKeyUp="" id="inputG" name="inputG" class="form-control" placeholder="" value="<?php if (isset($iAtendimentoAdmissaoId )) echo $rowAdmissao['EnAdPGestacao']; ?>">                                                

                                                    </div>
                                                </div>

                                                <div class="col-lg-2">
                                                    <div class="form-group">
                                                        <label for="inputA">A</label>
                                                        <input type="number" onKeyUp="" id="inputA" name="inputA" class="form-control" placeholder="" value="<?php if (isset($iAtendimentoAdmissaoId )) echo $rowAdmissao['EnAdPAborto']; ?>">                                                

                                                    </div>
                                                </div>

                                                <div class="col-lg-2">
                                                    <div class="form-group">
                                                        <label for="inputPN">PN</label>
                                                        <input type="number" onKeyUp="" id="inputPN" name="inputPN" class="form-control" placeholder="" value="<?php if (isset($iAtendimentoAdmissaoId )) echo $rowAdmissao['EnAdPPartoNatural']; ?>">                                                

                                                    </div>
                                                </div>

                                                <div class="col-lg-2">
                                                    <div class="form-group">
                                                        <label for="inputPC">PC</label>
                                                        <input type="number" onKeyUp="" id="inputPC" name="inputPC" class="form-control" placeholder="" value="<?php if (isset($iAtendimentoAdmissaoId )) echo $rowAdmissao['EnAdPPartoCesariana']; ?>">                                                

                                                    </div>
                                                </div>

                                                <div class="col-lg-4">
                                                    <div class="form-group">
                                                        <label for="cmbVacinaPNI">Vacinas no PNI</label>
                                                        <select id="cmbVacinaPNI" name="cmbVacinaPNI" class="form-control-select2" >
                                                            <option value="">Selecione</option>
                                                            <option value='1' <?php if (isset($iAtendimentoAdmissaoId )) echo $rowAdmissao['EnAdPVacinaPNI'] == 1 ? 'selected' : ''; ?> >SIM</option>
                                                            <option value='0' <?php if (isset($iAtendimentoAdmissaoId )) echo $rowAdmissao['EnAdPVacinaPNI'] == 0 ? 'selected' : ''; ?> >NÃO</option>
                                                        </select>
                                                    </div>
                                                </div>

                                        
                                            </div>
                                        </div>

                                        <div class="row mb-3">                                       
                                            <div class="col-lg-12 row">

                                                <div class="col-lg-2">
                                                    <div class="form-group">
                                                        <label for="inputNumConsultaPreNatal">Nº de Consultas de pré-natal</label>
                                                        <input type="number" onKeyUp="" id="inputNumConsultaPreNatal" name="inputNumConsultaPreNatal" class="form-control" placeholder="" value="<?php if (isset($iAtendimentoAdmissaoId )) echo $rowAdmissao['EnAdPNumConsultaPreNatal']; ?>">                                                
                                                    </div>
                                                </div>

                                                <div class="col-lg-2">
                                                    <div class="form-group">
                                                        <label for="inputMesInicioPreNatal">Mês/Ano início do pré-natal</label>
                                                        <input type="month" onKeyUp="" id="inputMesInicioPreNatal" name="inputMesInicioPreNatal" class="form-control" placeholder="" value="<?php if (isset($iAtendimentoAdmissaoId )) echo $rowAdmissao['EnAdPMesInicioPreNatal']; ?>">                                                

                                                    </div>
                                                </div>

                                                <div class="col-lg-2">
                                                    <div class="form-group">
                                                        <label for="cmbFatorRH">TS/Fator RH</label>
                                                        <select id="cmbFatorRH" name="cmbFatorRH" class="select-search" >
                                                            <option value="">Selecione</option>                                                            
                                                            <option value='A+' <?php if (isset($iAtendimentoAdmissaoId )) echo $rowAdmissao['EnAdPFatorRH'] == 'A+' ? 'selected' : ''; ?> >A+</option>
                                                            <option value='A-' <?php if (isset($iAtendimentoAdmissaoId )) echo $rowAdmissao['EnAdPFatorRH'] == 'A-' ? 'selected' : ''; ?> >A-</option>
                                                            <option value='B+' <?php if (isset($iAtendimentoAdmissaoId )) echo $rowAdmissao['EnAdPFatorRH'] == 'B+' ? 'selected' : ''; ?> >B+</option>
                                                            <option value='B-' <?php if (isset($iAtendimentoAdmissaoId )) echo $rowAdmissao['EnAdPFatorRH'] == 'B-' ? 'selected' : ''; ?> >B-</option>
                                                            <option value='AB+' <?php if (isset($iAtendimentoAdmissaoId )) echo $rowAdmissao['EnAdPFatorRH'] == 'AB+' ? 'selected' : ''; ?> >AB+</option>
                                                            <option value='AB-' <?php if (isset($iAtendimentoAdmissaoId )) echo $rowAdmissao['EnAdPFatorRH'] == 'AB-' ? 'selected' : ''; ?> >AB-</option>
                                                            <option value='O+' <?php if (isset($iAtendimentoAdmissaoId )) echo $rowAdmissao['EnAdPFatorRH'] == 'O+' ? 'selected' : ''; ?> >O+</option>
                                                            <option value='O-' <?php if (isset($iAtendimentoAdmissaoId )) echo $rowAdmissao['EnAdPFatorRH'] == 'O-' ? 'selected' : ''; ?> >O-</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-lg-2">
                                                    <div class="form-group">
                                                        <label for="cmbHIV">HIV</label>
                                                        <select id="cmbHIV" name="cmbHIV" class="form-control-select2" >
                                                            <option value="">Selecione</option>
                                                            <option value='NR' <?php if (isset($iAtendimentoAdmissaoId )) echo $rowAdmissao['EnAdPHIV'] == 'NR' ? 'selected' : ''; ?> >NÃO REAGENTE</option>
                                                            <option value='RE' <?php if (isset($iAtendimentoAdmissaoId )) echo $rowAdmissao['EnAdPHIV'] == 'RE' ? 'selected' : ''; ?> >REAGENTE</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                                                
                                                <div class="col-lg-2">
                                                    <div class="form-group">
                                                        <label for="cmbVDRL">VDRL</label>
                                                        <select id="cmbVDRL" name="cmbVDRL" class="form-control-select2" >
                                                            <option value="">Selecione</option>
                                                            <option value='NR' <?php if (isset($iAtendimentoAdmissaoId )) echo $rowAdmissao['EnAdPVDRL'] == 'NR' ? 'selected' : ''; ?> >NÃO REAGENTE</option>
                                                            <option value='RE'<?php if (isset($iAtendimentoAdmissaoId )) echo $rowAdmissao['EnAdPVDRL'] == 'RE' ? 'selected' : ''; ?> >REAGENTE</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-lg-2">
                                                    <div class="form-group">
                                                        <label for="cmbHBsAg">HBsAg</label>
                                                        <select id="cmbHBsAg" name="cmbHBsAg" class="form-control-select2" >
                                                            <option value="">Selecione</option>
                                                            <option value='NR' <?php if (isset($iAtendimentoAdmissaoId )) echo $rowAdmissao['EnAdPHBsAg'] == 'NR' ? 'selected' : ''; ?> >NÃO REAGENTE</option>
                                                            <option value='RE' <?php if (isset($iAtendimentoAdmissaoId )) echo $rowAdmissao['EnAdPHBsAg'] == 'RE' ? 'selected' : ''; ?> >REAGENTE</option>
                                                        </select>
                                                    </div>
                                                </div>    
                                        
                                            </div>
                                        </div>

                                        <div class="row mb-3">

                                            <div class="col-lg-4">

                                                <div class="col-lg-8">
                                                    <div class="row form-group">
                                                        <label for="cmbComorbidade">Comorbidades</label>
                                                        <select id="cmbComorbidade" name="cmbComorbidade" class="select-search" >
                                                            <option value="">Selecione</option>
                                                            <option value='IT' <?php if (isset($iAtendimentoAdmissaoId )) echo $rowAdmissao['EnAdPComorbidade'] == 'IT' ? 'selected' : ''; ?> >ITU</option>
                                                            <option value='DM' <?php if (isset($iAtendimentoAdmissaoId )) echo $rowAdmissao['EnAdPComorbidade'] == 'DM' ? 'selected' : ''; ?> >DMG</option>
                                                            <option value='HA' <?php if (isset($iAtendimentoAdmissaoId )) echo $rowAdmissao['EnAdPComorbidade'] == 'HA' ? 'selected' : ''; ?> >HAS</option>
                                                            <option value='OB' <?php if (isset($iAtendimentoAdmissaoId )) echo $rowAdmissao['EnAdPComorbidade'] == 'OB' ? 'selected' : ''; ?> >OBESIDADE</option>
                                                            <option value='SI' <?php if (isset($iAtendimentoAdmissaoId )) echo $rowAdmissao['EnAdPComorbidade'] == 'SI' ? 'selected' : ''; ?> >SÍFILIS</option>
                                                            <option value='OU' <?php if (isset($iAtendimentoAdmissaoId )) echo $rowAdmissao['EnAdPComorbidade'] == 'OU' ? 'selected' : ''; ?> >OUTROS</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-lg-11">
                                                    <div class="row">       
                                                        <label for="inputComorbidadeDescricao">Qual Comorbidades?</label>
                                                        <textarea rows="3"  maxLength="150" onInput="contarCaracteres(this);"  id="inputComorbidadeDescricao" name="inputComorbidadeDescricao" class="form-control" placeholder="" ><?php if (isset($iAtendimentoAdmissaoId )) echo $rowAdmissao['EnAdPComorbidadeDescricao']; ?></textarea>
                                                        <small class="text-muted form-text">Max. 150 caracteres<span class="caracteresinputComorbidadeDescricao"></span></small>                                                                                               
                                                    </div>
                                                </div>
                                                
                                            </div>

                                            <div class="col-lg-8">                                       

                                                <div class="row" style='justify-content: space-between;'>
                                                    <div class="col-lg-4" >
                                                        <label for="inputIntercorrencias">Intercorrências na Gestação</label>
                                                        <div class="form-group">							
                                                            <div class="form-check form-check-inline">
                                                                <label class="form-check-label">
                                                                    <input type="radio" id="inputIntercorrencias" name="inputIntercorrencias" value="1" class="form-input-styled" data-fouc onclick="selecionaInputIntercorrenciasDescricao('1')" <?php if (isset($iAtendimentoAdmissaoId )) { if ($rowAdmissao['EnAdPIntercorrenciaGestacao'] == 1) echo "checked"; }?>>
                                                                    Sim
                                                                </label>                     
                                                            </div>                                              
                                                            
                                                            <div class="form-check form-check-inline">
                                                                <label class="form-check-label">
                                                                    <input type="radio" id="inputIntercorrencias" name="inputIntercorrencias" value="0" class="form-input-styled" data-fouc onclick="selecionaInputIntercorrenciasDescricao('0')" <?php if (isset($iAtendimentoAdmissaoId )) { if ($rowAdmissao['EnAdPIntercorrenciaGestacao'] == 0) echo "checked"; }else{ echo "checked"; }?>>
                                                                    Não
                                                                </label>
                                                            </div>										
                                                        </div>									
                                                    </div>
                                                    <div class="col-lg-4" >
                                                        <label for="inputAlergia">Alergia</label>
                                                        <div class="form-group">							
                                                            <div class="form-check form-check-inline">
                                                                <label class="form-check-label">
                                                                    <input type="radio" id="inputAlergia" name="inputAlergia" value="1" class="form-input-styled" data-fouc onclick="selecionaAlergiaDescricaoAd('1')" <?php if (isset($iAtendimentoAdmissaoId )) { if ($rowAdmissao['EnAdPAlergia'] == 1) echo "checked"; }?>>
                                                                    Sim
                                                                </label>
                                                            </div>
                                                            <div class="form-check form-check-inline">
                                                                <label class="form-check-label">
                                                                    <input type="radio" id="inputAlergia" name="inputAlergia" value="0" class="form-input-styled" data-fouc onclick="selecionaAlergiaDescricaoAd('0')" <?php if (isset($iAtendimentoAdmissaoId )) { if ($rowAdmissao['EnAdPAlergia'] == 0) echo "checked"; }else{ echo "checked"; }?>>
                                                                    Não
                                                                </label>
                                                            </div>										
                                                        </div>									
                                                    </div>
                                                    <div class="col-lg-4" >
                                                        <label for="inputUsoMedicamentos">Uso de Medicamentos</label>
                                                        <div class="form-group">							
                                                            <div class="form-check form-check-inline">
                                                                <label class="form-check-label">
                                                                    <input type="radio" id="inputUsoMedicamentos" name="inputUsoMedicamentos" value="1" class="form-input-styled" data-fouc onclick="selecionaUsoMedicamentosDescricao('1')" <?php if (isset($iAtendimentoAdmissaoId )) { if ($rowAdmissao['EnAdPUsoMedicamento'] == 1) echo "checked"; }?>>
                                                                    Sim
                                                                </label>
                                                            </div>
                                                            <div class="form-check form-check-inline">
                                                                <label class="form-check-label">
                                                                    <input type="radio" id="inputUsoMedicamentos" name="inputUsoMedicamentos" value="0" class="form-input-styled" data-fouc  onclick="selecionaUsoMedicamentosDescricao('0')" <?php if (isset($iAtendimentoAdmissaoId )) { if ($rowAdmissao['EnAdPUsoMedicamento'] == 0) echo "checked"; }else{ echo "checked"; }?>>
                                                                    Não
                                                                </label>
                                                            </div>										
                                                        </div>									
                                                    </div>                                                    
                                            
                                                </div>	
                                                <br>
                                                <div class="row" style='justify-content: space-between;'>
                                                    <div class="col-lg-4"  >
                                                        <div id="dadosIntercorrencias" <?php if (!$iAtendimentoAdmissaoId) print('style="display:none"'); ?>>
                                                            <div class="form-group">
                                                                <textarea rows="4" id="inputIntercorrenciasDescricao" name="inputIntercorrenciasDescricao" onInput="contarCaracteres(this)" maxLength="150" class="form-control" placeholder="Descrição das Intercorrências" ><?php if (isset($iAtendimentoAdmissaoId )) echo $rowAdmissao['EnAdPIntercorrenciaGestacaoDescricao']; ?></textarea>
                                                                <small class="text-muted form-text">
                                                                    Máx. 150 caracteres<br>
                                                                    <span class="caracteresinputIntercorrenciasDescricao"></span>
                                                                </small>
                                                            </div>
                                                        </div> 
                                                    </div>
                                                    <div class="col-lg-4"  >
                                                        <div id="dadosAlergiad" <?php if (!$iAtendimentoAdmissaoId) print('style="display:none"'); ?>>
                                                            <div class="form-group">
                                                                <textarea rows="4" id="inputAlergiaDescricao" name="inputAlergiaDescricao" onInput="contarCaracteres(this)" maxLength="150" class="form-control" placeholder="Descrição da Alergia" ><?php if (isset($iAtendimentoAdmissaoId )) echo $rowAdmissao['EnAdPAlergiaDescricao']; ?></textarea>
                                                                <small class="text-muted form-text">
                                                                    Máx. 150 caracteres<br>
                                                                    <span class="caracteresinputAlergiaDescricao"></span>
                                                                </small>
                                                            </div>
                                                        </div> 
                                                    </div>
                                                    <div class="col-lg-4"  >
                                                        <div id="dadosUsoMedDescricao" <?php if (!$iAtendimentoAdmissaoId) print('style="display:none"'); ?>>
                                                            <div class="form-group">
                                                                <textarea rows="4" id="inputUsoMedDescricao" name="inputUsoMedDescricao" onInput="contarCaracteres(this)" maxLength="150" class="form-control" placeholder="Descrição do Medicamento" ><?php if (isset($iAtendimentoAdmissaoId )) echo $rowAdmissao['EnAdPUsoMedicamentoDescricao']; ?></textarea>
                                                                <small class="text-muted form-text">
                                                                    Máx. 150 caracteres<br>
                                                                    <span class="caracteresinputUsoMedDescricao"></span>
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                
                                                </div>
                                            </div>
                                            
                                        </div>

                                        <div class="row mb-3">

                                            <div class="col-lg-12 row">

                                                <div class="col-lg-2">
                                                    <div class="form-group">
                                                        <label for="cmbMovimentosFetais">Movimentos Fetais</label>
                                                        <select id="cmbMovimentosFetais" name="cmbMovimentosFetais" class="form-control-select2" >
                                                            <option value="">Selecione</option>
                                                            <option value='1' <?php if (isset($iAtendimentoAdmissaoId )) echo $rowAdmissao['EnAdPMovimentoFetal'] == 1 ? 'selected' : ''; ?> >SIM</option>
                                                            <option value='0' <?php if (isset($iAtendimentoAdmissaoId )) echo $rowAdmissao['EnAdPMovimentoFetal'] == 0 ? 'selected' : ''; ?> >NÃO</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-lg-2">
                                                    <div class="form-group">
                                                        <label for="cmbContracao">Contração</label>
                                                        <select id="cmbContracao" name="cmbContracao" class="form-control-select2" >
                                                            <option value="">Selecione</option>
                                                            <option value='1' <?php if (isset($iAtendimentoAdmissaoId )) echo $rowAdmissao['EnAdPContracao'] == 1 ? 'selected' : ''; ?> >SIM</option>
                                                            <option value='0' <?php if (isset($iAtendimentoAdmissaoId )) echo $rowAdmissao['EnAdPContracao'] == 0 ? 'selected' : ''; ?> >NÃO</option>
                                                        </select>

                                                    </div>
                                                </div>

                                                <div class="col-lg-2">
                                                    <div class="form-group">
                                                        <label for="inputFrequencia">Frequência</label>
                                                        <input type="text" onKeyUp="" maxLength="30" id="inputFrequencia" name="inputFrequencia" class="form-control" placeholder="" value="<?php if (isset($iAtendimentoAdmissaoId )) echo $rowAdmissao['EnAdPFrequencia']; ?>">                                                

                                                    </div>
                                                </div>

                                                <div class="col-lg-1">
                                                    <div class="form-group">
                                                        <label for="inputBCFMIN">BCF/MIN</label>
                                                        <input type="number" onKeyUp="" id="inputBCFMIN" name="inputBCFMIN" class="form-control" placeholder="" value="<?php if (isset($iAtendimentoAdmissaoId )) echo $rowAdmissao['EnAdPBCFMIN']; ?>">                                                

                                                    </div>
                                                </div>
                                                                                
                                                <div class="col-lg-2">
                                                    <div class="form-group">
                                                        <label for="cmbBolsaRota">Bolsa Rota</label>
                                                        <select id="cmbBolsaRota" name="cmbBolsaRota" class="form-control-select2" >
                                                            <option value="">Selecione</option>
                                                            <option value='1' <?php if (isset($iAtendimentoAdmissaoId )) echo $rowAdmissao['EnAdPBolsaRota'] == 1 ? 'selected' : ''; ?> >SIM</option>
                                                            <option value='0' <?php if (isset($iAtendimentoAdmissaoId )) echo $rowAdmissao['EnAdPBolsaRota'] == 0 ? 'selected' : ''; ?> >NÃO</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-lg-2">
                                                    <div class="form-group">
                                                        <label for="inputBolsaRotaData">Data</label>
                                                        <input type="date" onKeyUp="" id="inputBolsaRotaData" name="inputBolsaRotaData" class="form-control" placeholder="" value="<?php if (isset($iAtendimentoAdmissaoId )) echo $rowAdmissao['EnAdPBolsaRotaData']; ?>">                                                
                                                    </div>
                                                </div>

                                                <div class="col-lg-1">
                                                    <div class="form-group">
                                                        <label for="inputBolsaRotaHora">Hora</label>
                                                        <input type="time" onKeyUp="" id="inputBolsaRotaHora" name="inputBolsaRotaHora" class="form-control" placeholder="" value="<?php if (isset($iAtendimentoAdmissaoId )) echo $rowAdmissao['EnAdPBolsaRotaHora']; ?>">                                                
                                                    </div>
                                                </div>                          
                                                                                   
                                            </div>

                                        </div>                                   
                                        
                                        <div class="row mb-3">

                                            <div class="col-lg-12 row">

                                                <div class="col-lg-3">
                                                    <div class="form-group">
                                                        <label for="cmbEliminacoesFis">Eliminações Fisiológicas</label>
                                                        <select id="cmbEliminacoesFis" name="cmbEliminacoesFis" class="form-control-select2" >
                                                            <option value="">Selecione</option>
                                                            <option value='1' <?php if (isset($iAtendimentoAdmissaoId )) echo $rowAdmissao['EnAdPEliminacaoFisiologica'] == 1 ? 'selected' : ''; ?> >SIM</option>
                                                            <option value='0' <?php if (isset($iAtendimentoAdmissaoId )) echo $rowAdmissao['EnAdPEliminacaoFisiologica'] == 0 ? 'selected' : ''; ?> >NÃO</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-lg-3">
                                                    <div class="form-group">
                                                        <label for="cmbEdemaMMII">Edemas de MMII</label>
                                                        <select id="cmbEdemaMMII" name="cmbEdemaMMII" class="form-control-select2" >
                                                            <option value="">Selecione</option>
                                                            <option value='1' <?php if (isset($iAtendimentoAdmissaoId )) echo $rowAdmissao['EnAdPEdemaMMII'] == 1 ? 'selected' : ''; ?> >SIM</option>
                                                            <option value='0' <?php if (isset($iAtendimentoAdmissaoId )) echo $rowAdmissao['EnAdPEdemaMMII'] == 0 ? 'selected' : ''; ?> >NÃO</option>
                                                        </select>

                                                    </div>
                                                </div>

                                                <div class="col-lg-2">
                                                    <div class="form-group">
                                                        <label for="inputClassificacaoEdema">Classificação de Edema</label>
                                                        <input type="text" onKeyUp="" maxLength="60" id="inputClassificacaoEdema" name="inputClassificacaoEdema" class="form-control" placeholder="" value="<?php if (isset($iAtendimentoAdmissaoId )) echo $rowAdmissao['EnAdPClassificacaoEdema']; ?>">                                                

                                                    </div>
                                                </div>                                                                        
                                                                                   
                                            </div>

                                        </div>

                                        <div class="row mb-3">

                                            <div class="col-lg-12 row">

                                                <div class="col-lg-2">
                                                    <div class="form-group">
                                                        <label for="cmbAcompanhante">Acompanhante</label>
                                                        <select id="cmbAcompanhante" name="cmbAcompanhante" class="form-control-select2" >
                                                            <option value="">Selecione</option>
                                                            <option value='1' <?php if (isset($iAtendimentoAdmissaoId )) echo $rowAdmissao['EnAdPAcompanhante'] == 1 ? 'selected' : ''; ?> >SIM</option>
                                                            <option value='0' <?php if (isset($iAtendimentoAdmissaoId )) echo $rowAdmissao['EnAdPAcompanhante'] == 0 ? 'selected' : ''; ?> >NÃO</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                

                                                <div class="col-lg-10">
                                                    <div class="form-group">
                                                        <label for="inputNomeAcompanhante">Nome</label>
                                                        <input type="text" onKeyUp="" maxLength="30" id="inputNomeAcompanhante" name="inputNomeAcompanhante" class="form-control" placeholder="" value="<?php if (isset($iAtendimentoAdmissaoId )) echo $rowAdmissao['EnAdPAcompanhanteNome']; ?>">                                                

                                                    </div>
                                                </div>                                                                        
                                                                                   
                                            </div>

                                        </div>

                                        <div class="row col-lg-12">
                                            
                                            <div class="card-header header-elements-inline" style="margin-left: -20px;">
                                                <h3 class="card-title font-weight-bold" >Observações</h3>
                                            </div>    
                                            
                                            <div class="col-lg-12">
                                                <div class="row">                                                    
                                                    <textarea rows="3"  maxLength="100" onInput="contarCaracteres(this);"  id="inputObservacoes" name="inputObservacoes" class="form-control" placeholder="Corpo da observação (informe aqui o texto que você queira que apareça na observação da admissão)" ><?php if (isset($iAtendimentoAdmissaoId )) echo $rowAdmissao['EnAdPObservacao']; ?></textarea>
                                                    <small class="text-muted form-text">Max. 100 caracteres<span class="caracteresinputObservacoes"></span></small>                                                                                               
                                                </div>
                                            </div>
                                            
                                        </div>  

                                    </div>                                  

                                </div>
                                			
                            </div>

                             <!-- EVOLUCAO DE PARTO -->
                             <?php if ($iAtendimentoAdmissaoId) { ?>

                                <div class="card box-evolucao-parto" style="display: none;">

                                    <div class="card-header header-elements-inline">
                                        <h3 class="card-title font-weight-bold">Evolucao do Parto</h3>
                                    </div>

                                    <input type="hidden" name="idEvolucao" id="idEvolucao">

                                    <div class="card-body">
                                        <div class="row mb-3">
                                            <div class="col-lg-12 row">
                                                <div class="col-lg-2">
                                                    <div class="form-group">
                                                        <label for="cmbRealizadoToque">Realizado Toque?</label>
                                                        <select id="cmbRealizadoToque" name="cmbRealizadoToque" class="form-control-select2" >
                                                            <option value="">Selecione</option>
                                                            <option value='1'>SIM</option>
                                                            <option value='0'>NÃO</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-lg-1">
                                                    <div class="form-group">
                                                        <label for="inputDilatacao">Dilatação(cm)</label>
                                                        <input type="number" onKeyUp="" id="inputDilatacao" name="inputDilatacao" class="form-control" placeholder="" value="">                                                                                                   

                                                    </div>
                                                </div>

                                                <div class="col-lg-1 ">
                                                    <div class="form-group">
                                                        <label for="inputApagamento">Apagamento(%)</label>
                                                        <input type="number" onKeyUp="" id="inputApagamento" name="inputApagamento" class="form-control" placeholder="" value="">                                                

                                                    </div>
                                                </div>

                                                <div class="col-lg-2">
                                                    <div class="form-group">
                                                        <label for="cmbApresentacao">Apresentação</label>
                                                        <select id="cmbApresentacao" name="cmbApresentacao" class="select-search" >
                                                            <option value="">Selecione</option>
                                                            <option value='CE'>CEFÁLICA</option>
                                                            <option value='PE'>PÉLVICA</option>
                                                            <option value='TR'>TRANSVERSO</option>
                                                        </select>

                                                    </div>
                                                </div>
                                                                                
                                                <div class="col-lg-2">
                                                    <div class="form-group">
                                                        <label for="cmbPlanoLee">Plano de Lee</label>
                                                        <select id="cmbPlanoLee" name="cmbPlanoLee" class="select-search" >
                                                            <option value="">Selecione</option>
                                                            <option value='-5cm'> - 5cm</option>
                                                            <option value='-4cm'> - 4cm</option>
                                                            <option value='-3cm'> - 3cm</option>
                                                            <option value='-2cm'> - 2cm</option>
                                                            <option value='-1cm'> - 1cm</option>
                                                            <option value='+1cm'> + 1cm</option>   
                                                            <option value='+2cm'> + 2cm</option>
                                                            <option value='+3cm'> + 3cm</option>
                                                            <option value='+4cm'> + 4cm</option>
                                                            <option value='+5cm'> + 5cm</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-lg-2">
                                                    <div class="form-group">
                                                        <label for="inputLiquido">Líquido</label>
                                                        <input type="text" onKeyUp="" maxLength="30" id="inputLiquido" name="inputLiquido" class="form-control" placeholder="" value="">                                                
                                                    </div>
                                                </div>

                                                <div class="col-lg-2">
                                                    <div class="form-group">
                                                        <label for="cmbMeconio">Mecônio</label>
                                                        <select id="cmbMeconio" name="cmbMeconio" class="form-control-select2" >
                                                            <option value="">Selecione</option>
                                                            <option value='1'>SIM</option>
                                                            <option value='0'>NÃO</option>
                                                        </select>
                                                    </div>
                                                </div>   
                                                
                                                <div class='col-lg-1 mt-2' >
                                                    <a id="inserirEvolucaoAdmissaoPreParto" class='btn btn-lg btn-principal'>Adicionar</a>
                                                    <a id="editarEvolucaoAdmissaoPreParto" class='btn btn-lg btn-principal' style="display: none;">Salvar</a>
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
                                                        <th class="text-left">Dilatação</th>
                                                        <th class="text-left">Apagamento</th>
                                                        <th class="text-left">Plano de Lee</th>
                                                        <th class="text-left">Líquido</th>
                                                        <th class="text-left">Mecônio</th>
                                                        <th class="text-center">Ações</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="dataAdmissao">
                                                </tbody>
                                            </table>
                                        </div>		
                                    </div>			


                                </div>

                            <?php } ?>

                            <div class="card">
                                <div class=" card-body row">
                                    <div class="col-lg-12">
                                        <div class="form-group" style="margin-bottom:0px;">
                                            <?php 
                                                if (isset($SituaChave) && $SituaChave != "ATENDIDO") {
                                                    echo "<button class='btn btn-lg btn-success mr-1 salvarAdmissao' >Salvar</button>";
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