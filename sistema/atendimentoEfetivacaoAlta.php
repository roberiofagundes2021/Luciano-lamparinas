<?php
include_once("sessao.php");
$_SESSION['PaginaAtual'] = 'Efetivação de Alta';
include('global_assets/php/conexao.php');


$iAtendimentoId = isset($_POST['iAtendimentoId']) ? $_POST['iAtendimentoId'] : 43;

if (isset($_SESSION['iAtendimentoId']) && $iAtendimentoId == null) {
    $iAtendimentoId = $_SESSION['iAtendimentoId'];
}
$_SESSION['iAtendimentoId'] = null;

if (!$iAtendimentoId) {
    irpara("atendimentoHospitalarListagem.php");
}

$iUnidade = $_SESSION['UnidadeId'];

$sql = "SELECT TOP(1) *
FROM EnfermagemEfetivacaoAlta
LEFT JOIN TipoAlta ON EnEfATipoAlta = TpAltId
LEFT JOIN MotivoAlta ON EnEfAMotivoAlta = MtAltId
WHERE EnEfAAtendimento = $iAtendimentoId
ORDER BY EnEfAId DESC";
$result = $conn->query($sql);
$rowEfetivacao = $result->fetch(PDO::FETCH_ASSOC);

//var_dump($rowEfetivacao);die;

$iAtendimentoEfetivacaoAlta = $rowEfetivacao?$rowEfetivacao['EnEfAId']:null;

$ClaChave = isset($_POST['ClaChave']) ? $_POST['ClaChave'] : '';
$ClaNome = isset($_POST['ClaNome']) ? $_POST['ClaNome'] : '';

//Essa consulta é para verificar  o profissional
$sql = "SELECT UsuarId, A.ProfiUsuario, A.ProfiId as ProfissionalId, A.ProfiNome as ProfissionalNome, PrConNome, B.ProfiCbo as ProfissaoCbo, ProfiNumConselho
		FROM Usuario
		JOIN Profissional A ON A.ProfiUsuario = UsuarId
		LEFT JOIN Profissao B ON B.ProfiId = A.ProfiProfissao
		LEFT JOIN ProfissionalConselho ON PrConId = ProfiConselho
		WHERE UsuarId =  " . $_SESSION['UsuarId'] . " ";
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

$iAtendimentoCliente = $row['AtendCliente'];
$iAtendimentoId = $row['AtendId'];

//Essa consulta é para preencher o sexo
if ($row['ClienSexo'] == 'F'){
    $sexo = 'Feminino';
} else{
    $sexo = 'Masculino';
}

$sql = "SELECT P.ProfiId,P.ProfiNome,PFS.ProfiCbo,PFS.ProfiNome as profissao
FROM Profissional P
JOIN Profissao PFS ON PFS.ProfiId = P.ProfiProfissao
WHERE P.ProfiUnidade = $_SESSION[UnidadeId]";
$result = $conn->query($sql);
$rowProfissionais = $result->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT *
FROM Estabelecimento
WHERE EstabUnidade = $_SESSION[UnidadeId]";
$result = $conn->query($sql);
$rowEstabelecimentos = $result->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT Cid10Id,Cid10Capitulo, Cid10Codigo, Cid10Descricao
FROM Cid10
JOIN Situacao on SituaId = Cid10Status
WHERE SituaChave = 'ATIVO'
ORDER BY Cid10Codigo ASC";
$result = $conn->query($sql);
$rowCid10 = $result->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT SrVenId,SrVenCodigo, SrVenNome
FROM ServicoVenda
WHERE SrVenUnidade = " . $_SESSION['UnidadeId'] . "
ORDER BY SrVenNome ASC";
$result = $conn->query($sql);
$rowProcedimentoDiagnostico = $result->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT TpAltId, TpAltNome, TpAltChave
FROM TipoAlta
JOIN Situacao on SituaId = TpAltStatus
WHERE SituaChave = 'ATIVO'";
$result = $conn->query($sql);
$rowTipoDeAlta = $result->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT MtAltId, MtAltNome
FROM MotivoAlta
JOIN Situacao on SituaId = MtAltStatus
WHERE SituaChave = 'ATIVO'";
$result = $conn->query($sql);
$rowMotivoAlta = $result->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT EsLeiId, EsLeiNome
FROM EspecialidadeLeito
JOIN Situacao on SituaId = EsLeiStatus
WHERE SituaChave = 'ATIVO'";
$result = $conn->query($sql);
$rowEspecialidadeLeito = $result->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT TOP(1) EnAnaId, EnAnaCid10, EnAnaProcedimento, EnAnaQueixaPrincipal, EnAnaHistoriaMolestiaAtual, 
EnAnaHistoriaPatologicaPregressa, EnAnaHistoriaFamiliar, EnAnaHipoteseSocioEconomica, EnAnaDigitacaoLivre,
Cid10Descricao, SrVenNome
FROM EnfermagemAnamnese
LEFT JOIN Cid10 ON EnAnaCid10 = Cid10Id
LEFT JOIN ServicoVenda ON EnAnaProcedimento = SrVenId
WHERE EnAnaAtendimento = $iAtendimentoId
ORDER BY EnAnaId DESC";
$result = $conn->query($sql);
$rowAnamnese= $result->fetch(PDO::FETCH_ASSOC);
$iAtendimentoAnamneseId = $rowAnamnese?$rowAnamnese['EnAnaId']:null;

//var_dump($rowAnamnese);die;

if (isset($_POST['inputInicio'])) {

    //var_dump($_POST);die;


    $sql = "SELECT TpAltId
    FROM TipoAlta
    WHERE TpAltChave = '$_POST[cmbTipoDeAlta]'";
    $result = $conn->query($sql);
    $rowidTipoDeAlta = $result->fetch(PDO::FETCH_ASSOC);

    $idTipoAlta = $rowidTipoDeAlta['TpAltId'];

    //var_dump($idTipoAlta);die;


    try {
        $nome_final = '';

        if (isset($_FILES['copiaDeclaracaoObito']) && $_FILES['copiaDeclaracaoObito'] != '') {

            $_UP['pasta'] = 'global_assets/anexos/declaracaoObito/';

            // Renomeia o arquivo? (Se true, o arquivo será salvo como .csv e um nome único)
            $_UP['renomeia'] = false;

            // Primeiro verifica se deve trocar o nome do arquivo
            if ($_UP['renomeia'] == true) {
            
                // Cria um nome baseado no UNIX TIMESTAMP atual e com extensão .csv
                $nome_final = date('d-m-Y')."-".date('H-i-s')."-".$_FILES['copiaDeclaracaoObito']['name'];
            
            } else {
            
                // Mantém o nome original do arquivo
                $nome_final = $_FILES['copiaDeclaracaoObito']['name'];
            }

            move_uploaded_file($_FILES['copiaDeclaracaoObito']['tmp_name'], $_UP['pasta'] . $nome_final);          

        }
        
        if ($iAtendimentoEfetivacaoAlta) {

            $sql = "UPDATE EnfermagemEfetivacaoAlta SET                 
                EnEfAAtendimento = :EnEfAAtendimento ,
                EnEfADataInicio =  :EnEfADataInicio,
                EnEfAHoraInicio =  :EnEfAHoraInicio,
                EnEfADataFim = :EnEfADataFim ,
                EnEfAHoraFim = :EnEfAHoraFim ,
                EnEfAPrevisaoAlta = :EnEfAPrevisaoAlta ,
                EnEfATipoInternacao = :EnEfATipoInternacao ,
                EnEfAEspecialidadeLeito = :EnEfAEspecialidadeLeito ,
                EnEfAAla = :EnEfAAla ,
                EnEfAQuarto = :EnEfAQuarto ,
                EnEfALeito = :EnEfALeito ,
                EnEfAProfissional = :EnEfAProfissional ,
                EnEfAPas = :EnEfAPas ,
                EnEfAPad = :EnEfAPad ,
                EnEfAFreqCardiaca = :EnEfAFreqCardiaca ,
                EnEfAFreqRespiratoria = :EnEfAFreqRespiratoria ,
                EnEfATemperatura = :EnEfATemperatura ,
                EnEfASPO = :EnEfASPO ,
                EnEfAHGT = :EnEfAHGT ,
                EnEfAPeso = :EnEfAPeso ,
                EnEfADataHoraAlta = :EnEfADataHoraAlta ,
                EnEfATipoAlta = :EnEfATipoAlta ,
                EnEfAMotivoAlta = :EnEfAMotivoAlta,
                EnEfARetornoAlta = :EnEfARetornoAlta,
                EnEfASumarioAltaMedica = :EnEfASumarioAltaMedica,
                EnEfACondicaoPaciente = :EnEfACondicaoPaciente ,
                EnEfAOutros = :EnEfAOutros,
                EnEfATipoTransporte = :EnEfATipoTransporte ,
                EnEfAProfissionalResponsavel = :EnEfAProfissionalResponsavel ,
                EnEfAClinica = :EnEfAClinica,
                EnEfATransferencia = :EnEfATransferencia ,
                EnEfALocalTransferencia = :EnEfALocalTransferencia ,
                EnEfAAcompanhante = :EnEfAAcompanhante ,
                EnEfAAcompanhanteNome = :EnEfAAcompanhanteNome ,
                EnEfAJustificativaAlta = :EnEfAJustificativaAlta ,
                EnEfAProcedimentoMedicacao = :EnEfAProcedimentoMedicacao ,
                EnEfACid10 = :EnEfACid10 ,
                EnEfAProcedimentoRealizado = :EnEfAProcedimentoRealizado ,
                EnEfANumObito = :EnEfANumObito ,
                EnEfAArquivoDeclaracaoObito = :EnEfAArquivoDeclaracaoObito ,
                EnEfACausaObitoA = :EnEfACausaObitoA ,
                EnEfADataHoraObitoA = :EnEfADataHoraObitoA ,
                EnEfACidA = :EnEfACidA ,
                EnEfACausaObitoB = :EnEfACausaObitoB ,
                EnEfADataHoraObitoB = :EnEfADataHoraObitoB ,
                EnEfACidB = :EnEfACidB ,
                EnEfACausaObitoC = :EnEfACausaObitoC ,
                EnEfADataHoraObitoC = :EnEfADataHoraObitoC ,
                EnEfACidC = :EnEfACidC ,
                EnEfACausaObitoD = :EnEfACausaObitoD ,
                EnEfADataHoraObitoD = :EnEfADataHoraObitoD ,
                EnEfACidD = :EnEfACidD ,
                EnEfAUnidade = :EnEfAUnidade                
                WHERE EnEfAId = :iAtendimentoEfetivacaoAlta";

            $result = $conn->prepare($sql);

            $result->execute(array(
                ':EnEfAAtendimento' => $iAtendimentoId,
                ':EnEfADataInicio' => date('m/d/Y'),
                ':EnEfAHoraInicio' => date('H:i'),
                ':EnEfADataFim' => date('m/d/Y'),
                ':EnEfAHoraFim' => date('H:i'),
                ':EnEfAPrevisaoAlta' => $_POST['inputPrevisaoAlta'] == "" ? null : $_POST['inputPrevisaoAlta'],
                ':EnEfATipoInternacao' => $_POST['inputTipoInternacao'] == "" ? null : $_POST['inputTipoInternacao'],
                ':EnEfAEspecialidadeLeito' => $_POST['inputEspLeito'] == "" ? null : $_POST['inputEspLeito'],
                ':EnEfAAla' => $_POST['inputAla'] == "" ? null : $_POST['inputAla'],
                ':EnEfAQuarto' => $_POST['inputQuarto'] == "" ? null : $_POST['inputQuarto'],
                ':EnEfALeito' => $_POST['inputLeito'] == "" ? null : $_POST['inputLeito'],
                ':EnEfAProfissional' => $userId,
                ':EnEfAPas' => $_POST['inputSistolica'] == "" ? null : $_POST['inputSistolica'],
                ':EnEfAPad' => $_POST['inputDiatolica'] == "" ? null : $_POST['inputDiatolica'],
                ':EnEfAFreqCardiaca' => $_POST['inputCardiaca'] == "" ? null : $_POST['inputCardiaca'],
                ':EnEfAFreqRespiratoria' => $_POST['inputRespiratoria'] == "" ? null : $_POST['inputRespiratoria'],
                ':EnEfATemperatura' => $_POST['inputTemperatura'] == "" ? null : $_POST['inputTemperatura'],
                ':EnEfASPO' => $_POST['inputSPO'] == "" ? null : $_POST['inputSPO'],
                ':EnEfAHGT' => $_POST['inputHGT'] == "" ? null : $_POST['inputHGT'],
                ':EnEfAPeso' => $_POST['inputPeso'] == "" ? null : $_POST['inputPeso'],
                ':EnEfADataHoraAlta' => $_POST['inputDataHora'] == "" ? null :  str_replace('T', ' ', $_POST['inputDataHora'] ),
                ':EnEfATipoAlta' => $idTipoAlta,
                ':EnEfAMotivoAlta' => $_POST['cmbMotivoAlta'] == "" ? null : $_POST['cmbMotivoAlta'],
                ':EnEfARetornoAlta' => $_POST['inputDataRetornoAlta'] == "" ? null : $_POST['inputDataRetornoAlta'],
                ':EnEfASumarioAltaMedica' => $_POST['inputSumarioAltaMedica'] == "" ? null : $_POST['inputSumarioAltaMedica'],
                ':EnEfACondicaoPaciente' => $_POST['cmbCondicoesPaciente'] == "" ? null : $_POST['cmbCondicoesPaciente'],
                ':EnEfAOutros' => $_POST['inputCondicoesPacienteOutros'] == "" ? null : $_POST['inputCondicoesPacienteOutros'],
                ':EnEfATipoTransporte' => $_POST['cmbTipoTransporte'] == "" ? null : $_POST['cmbTipoTransporte'],
                ':EnEfAProfissionalResponsavel' => $_POST['cmbDadosProfResponsavel'] == "" ? null : $_POST['cmbDadosProfResponsavel'],
                ':EnEfAClinica' => $_POST['cmbClinica'] == "" ? null : $_POST['cmbClinica'],
                ':EnEfATransferencia' => $_POST['cmbTransferencia'] == "" ? null : $_POST['cmbTransferencia'],
                ':EnEfALocalTransferencia' => $_POST['cmbLocalTransferencia'] == "" ? null : $_POST['cmbLocalTransferencia'],
                ':EnEfAAcompanhante' => $_POST['cmbAcompanhante'] == "" ? null : $_POST['cmbAcompanhante'],
                ':EnEfAAcompanhanteNome' => $_POST['inputNomeAcompanhante'] == "" ? null : $_POST['inputNomeAcompanhante'],
                ':EnEfAJustificativaAlta' => $_POST['inputJustificativaAlta'] == "" ? null : $_POST['inputJustificativaAlta'],
                ':EnEfAProcedimentoMedicacao' => $_POST['inputProcMedAdministrada'] == "" ? null : $_POST['inputProcMedAdministrada'],
                ':EnEfACid10' => $_POST['cmbCId10'] == "" ? null : $_POST['cmbCId10'],
                ':EnEfAProcedimentoRealizado' => $_POST['cmbProcedimentoDiagnostico'] == "" ? null : $_POST['cmbProcedimentoDiagnostico'],
                ':EnEfANumObito' => $_POST['declaracaoNObito'] == "" ? null : $_POST['declaracaoNObito'],
                ':EnEfAArquivoDeclaracaoObito' => $nome_final == "" ? null : $nome_final,
                ':EnEfACausaObitoA' => $_POST['inputCausaObitoA'] == "" ? null : $_POST['inputCausaObitoA'],
                ':EnEfADataHoraObitoA' => $_POST['inputDataHoraObitoA'] == "" ? null : str_replace('T', ' ', $_POST['inputDataHoraObitoA'] ),
                ':EnEfACidA' => $_POST['inputCidObitoA'] == "" ? null : $_POST['inputCidObitoA'],
                ':EnEfACausaObitoB' => $_POST['inputCausaObitoB'] == "" ? null : $_POST['inputCausaObitoB'],
                ':EnEfADataHoraObitoB' => $_POST['inputDataHoraObitoB'] == "" ? null : str_replace('T', ' ', $_POST['inputDataHoraObitoB'] ),
                ':EnEfACidB' => $_POST['inputCidObitoB'] == "" ? null : $_POST['inputCidObitoB'],
                ':EnEfACausaObitoC' => $_POST['inputCausaObitoC'] == "" ? null : $_POST['inputCausaObitoC'],
                ':EnEfADataHoraObitoC' => $_POST['inputDataHoraObitoC'] == "" ? null : str_replace('T', ' ', $_POST['inputDataHoraObitoC'] ),
                ':EnEfACidC' => $_POST['inputCidObitoC'] == "" ? null : $_POST['inputCidObitoC'],
                ':EnEfACausaObitoD' => $_POST['inputCausaObitoD'] == "" ? null : $_POST['inputCausaObitoD'],
                ':EnEfADataHoraObitoD' => $_POST['inputDataHoraObitoD'] == "" ? null : str_replace('T', ' ', $_POST['inputDataHoraObitoD'] ),
                ':EnEfACidD' => $_POST['inputCidObitoD'] == "" ? null : $_POST['inputCidObitoD'],
                ':EnEfAUnidade' => $iUnidade,
                ':iAtendimentoEfetivacaoAlta' => $iAtendimentoEfetivacaoAlta
            ));

            $sql = "UPDATE EnfermagemEfetivacaoAltaOrientacao SET EnEAOEditavel = 0
			WHERE EnEAOEfetivacaoAlta =  '$iAtendimentoEfetivacaoAlta'";
		    $conn->query($sql);

            $_SESSION['msg']['titulo'] = "Sucesso";
            $_SESSION['msg']['mensagem'] = "Efetivação de Alta alterada!!!";
            $_SESSION['msg']['tipo'] = "success";

        } else {

            $sql = "INSERT INTO EnfermagemEfetivacaoAlta 
                (EnEfAAtendimento,
                EnEfADataInicio,
                EnEfAHoraInicio,
                EnEfADataFim,
                EnEfAHoraFim,
                EnEfAPrevisaoAlta,
                EnEfATipoInternacao,
                EnEfAEspecialidadeLeito,
                EnEfAAla,
                EnEfAQuarto,
                EnEfALeito,
                EnEfAProfissional,
                EnEfAPas,
                EnEfAPad,
                EnEfAFreqCardiaca,
                EnEfAFreqRespiratoria,
                EnEfATemperatura,
                EnEfASPO,
                EnEfAHGT,
                EnEfAPeso,
                EnEfADataHoraAlta,
                EnEfATipoAlta,
                EnEfAMotivoAlta,
                EnEfARetornoAlta,
                EnEfASumarioAltaMedica,
                EnEfACondicaoPaciente,
                EnEfAOutros,
                EnEfATipoTransporte,
                EnEfAProfissionalResponsavel,
                EnEfAClinica,
                EnEfATransferencia,
                EnEfALocalTransferencia,
                EnEfAAcompanhante,
                EnEfAAcompanhanteNome,
                EnEfAJustificativaAlta,
                EnEfAProcedimentoMedicacao,
                EnEfACid10,
                EnEfAProcedimentoRealizado,
                EnEfANumObito,
                EnEfAArquivoDeclaracaoObito,
                EnEfACausaObitoA,
                EnEfADataHoraObitoA,
                EnEfACidA,
                EnEfACausaObitoB,
                EnEfADataHoraObitoB,
                EnEfACidB,
                EnEfACausaObitoC,
                EnEfADataHoraObitoC,
                EnEfACidC,
                EnEfACausaObitoD,
                EnEfADataHoraObitoD,
                EnEfACidD,
                EnEfAUnidade
                )
                
			VALUES (

                :EnEfAAtendimento,
                :EnEfADataInicio,
                :EnEfAHoraInicio,
                :EnEfADataFim,
                :EnEfAHoraFim,
                :EnEfAPrevisaoAlta,
                :EnEfATipoInternacao,
                :EnEfAEspecialidadeLeito,
                :EnEfAAla,
                :EnEfAQuarto,
                :EnEfALeito,
                :EnEfAProfissional,
                :EnEfAPas,
                :EnEfAPad,
                :EnEfAFreqCardiaca,
                :EnEfAFreqRespiratoria,
                :EnEfATemperatura,
                :EnEfASPO,
                :EnEfAHGT,
                :EnEfAPeso,
                :EnEfADataHoraAlta,
                :EnEfATipoAlta,
                :EnEfAMotivoAlta ,
                :EnEfARetornoAlta ,
                :EnEfASumarioAltaMedica ,
                :EnEfACondicaoPaciente,
                :EnEfAOutros ,
                :EnEfATipoTransporte,
                :EnEfAProfissionalResponsavel,
                :EnEfAClinica,
                :EnEfATransferencia,
                :EnEfALocalTransferencia,
                :EnEfAAcompanhante,
                :EnEfAAcompanhanteNome,
                :EnEfAJustificativaAlta,
                :EnEfAProcedimentoMedicacao,
                :EnEfACid10,
                :EnEfAProcedimentoRealizado,
                :EnEfANumObito,
                :EnEfAArquivoDeclaracaoObito,
                :EnEfACausaObitoA,
                :EnEfADataHoraObitoA,
                :EnEfACidA,
                :EnEfACausaObitoB,
                :EnEfADataHoraObitoB,
                :EnEfACidB,
                :EnEfACausaObitoC,
                :EnEfADataHoraObitoC,
                :EnEfACidC,
                :EnEfACausaObitoD,
                :EnEfADataHoraObitoD,
                :EnEfACidD,
                :EnEfAUnidade               
                
            )";
            $result = $conn->prepare($sql);

            $result->execute(array(

                ':EnEfAAtendimento' => $iAtendimentoId,
                ':EnEfADataInicio' => date('m/d/Y'),
                ':EnEfAHoraInicio' => date('H:i'),
                ':EnEfADataFim' => date('m/d/Y'),
                ':EnEfAHoraFim' => date('H:i'),
                ':EnEfAPrevisaoAlta' => $_POST['inputPrevisaoAlta'] == "" ? null : $_POST['inputPrevisaoAlta'],
                ':EnEfATipoInternacao' => $_POST['inputTipoInternacao'] == "" ? null : $_POST['inputTipoInternacao'],
                ':EnEfAEspecialidadeLeito' => $_POST['inputEspLeito'] == "" ? null : $_POST['inputEspLeito'],
                ':EnEfAAla' => $_POST['inputAla'] == "" ? null : $_POST['inputAla'],
                ':EnEfAQuarto' => $_POST['inputQuarto'] == "" ? null : $_POST['inputQuarto'],
                ':EnEfALeito' => $_POST['inputLeito'] == "" ? null : $_POST['inputLeito'],
                ':EnEfAProfissional' => $userId,
                ':EnEfAPas' => $_POST['inputSistolica'] == "" ? null : $_POST['inputSistolica'],
                ':EnEfAPad' => $_POST['inputDiatolica'] == "" ? null : $_POST['inputDiatolica'],
                ':EnEfAFreqCardiaca' => $_POST['inputCardiaca'] == "" ? null : $_POST['inputCardiaca'],
                ':EnEfAFreqRespiratoria' => $_POST['inputRespiratoria'] == "" ? null : $_POST['inputRespiratoria'],
                ':EnEfATemperatura' => $_POST['inputTemperatura'] == "" ? null : $_POST['inputTemperatura'],
                ':EnEfASPO' => $_POST['inputSPO'] == "" ? null : $_POST['inputSPO'],
                ':EnEfAHGT' => $_POST['inputHGT'] == "" ? null : $_POST['inputHGT'],
                ':EnEfAPeso' => $_POST['inputPeso'] == "" ? null : $_POST['inputPeso'],
                ':EnEfADataHoraAlta' => $_POST['inputDataHora'] == "" ? null :  str_replace('T', ' ', $_POST['inputDataHora'] ),
                ':EnEfATipoAlta' => $idTipoAlta,
                ':EnEfAMotivoAlta' => $_POST['cmbMotivoAlta'] == "" ? null : $_POST['cmbMotivoAlta'],
                ':EnEfARetornoAlta' => $_POST['inputDataRetornoAlta'] == "" ? null : $_POST['inputDataRetornoAlta'],
                ':EnEfASumarioAltaMedica' => $_POST['inputSumarioAltaMedica'] == "" ? null : $_POST['inputSumarioAltaMedica'],
                ':EnEfACondicaoPaciente' => $_POST['cmbCondicoesPaciente'] == "" ? null : $_POST['cmbCondicoesPaciente'],
                ':EnEfAOutros' => $_POST['inputCondicoesPacienteOutros'] == "" ? null : $_POST['inputCondicoesPacienteOutros'],
                ':EnEfATipoTransporte' => $_POST['cmbTipoTransporte'] == "" ? null : $_POST['cmbTipoTransporte'],
                ':EnEfAProfissionalResponsavel' => $_POST['cmbDadosProfResponsavel'] == "" ? null : $_POST['cmbDadosProfResponsavel'],
                ':EnEfAClinica' => $_POST['cmbClinica'] == "" ? null : $_POST['cmbClinica'],
                ':EnEfATransferencia' => $_POST['cmbTransferencia'] == "" ? null : $_POST['cmbTransferencia'],
                ':EnEfALocalTransferencia' => $_POST['cmbLocalTransferencia'] == "" ? null : $_POST['cmbLocalTransferencia'],
                ':EnEfAAcompanhante' => $_POST['cmbAcompanhante'] == "" ? null : $_POST['cmbAcompanhante'],
                ':EnEfAAcompanhanteNome' => $_POST['inputNomeAcompanhante'] == "" ? null : $_POST['inputNomeAcompanhante'],
                ':EnEfAJustificativaAlta' => $_POST['inputJustificativaAlta'] == "" ? null : $_POST['inputJustificativaAlta'],
                ':EnEfAProcedimentoMedicacao' => $_POST['inputProcMedAdministrada'] == "" ? null : $_POST['inputProcMedAdministrada'],
                ':EnEfACid10' => $_POST['cmbCId10'] == "" ? null : $_POST['cmbCId10'],
                ':EnEfAProcedimentoRealizado' => $_POST['cmbProcedimentoDiagnostico'] == "" ? null : $_POST['cmbProcedimentoDiagnostico'],
                ':EnEfANumObito' => $_POST['declaracaoNObito'] == "" ? null : $_POST['declaracaoNObito'],
                ':EnEfAArquivoDeclaracaoObito' => $nome_final == "" ? null : $nome_final,
                ':EnEfACausaObitoA' => $_POST['inputCausaObitoA'] == "" ? null : $_POST['inputCausaObitoA'],
                ':EnEfADataHoraObitoA' => $_POST['inputDataHoraObitoA'] == "" ? null : str_replace('T', ' ', $_POST['inputDataHoraObitoA'] ),
                ':EnEfACidA' => $_POST['inputCidObitoA'] == "" ? null : $_POST['inputCidObitoA'],
                ':EnEfACausaObitoB' => $_POST['inputCausaObitoB'] == "" ? null : $_POST['inputCausaObitoB'],
                ':EnEfADataHoraObitoB' => $_POST['inputDataHoraObitoB'] == "" ? null : str_replace('T', ' ', $_POST['inputDataHoraObitoB'] ),
                ':EnEfACidB' => $_POST['inputCidObitoB'] == "" ? null : $_POST['inputCidObitoB'],
                ':EnEfACausaObitoC' => $_POST['inputCausaObitoC'] == "" ? null : $_POST['inputCausaObitoC'],
                ':EnEfADataHoraObitoC' => $_POST['inputDataHoraObitoC'] == "" ? null : str_replace('T', ' ', $_POST['inputDataHoraObitoC'] ),
                ':EnEfACidC' => $_POST['inputCidObitoC'] == "" ? null : $_POST['inputCidObitoC'],
                ':EnEfACausaObitoD' => $_POST['inputCausaObitoD'] == "" ? null : $_POST['inputCausaObitoD'],
                ':EnEfADataHoraObitoD' => $_POST['inputDataHoraObitoD'] == "" ? null : str_replace('T', ' ', $_POST['inputDataHoraObitoD'] ),
                ':EnEfACidD' => $_POST['inputCidObitoD'] == "" ? null : $_POST['inputCidObitoD'],
                ':EnEfAUnidade' => $iUnidade

            ));

            $_SESSION['msg']['titulo'] = "Sucesso";
            $_SESSION['msg']['mensagem'] = "Efetivação de Alta inserida com sucesso!!!";
            $_SESSION['msg']['tipo'] = "success";

        }
    } catch (PDOException $e) {

        $_SESSION['msg']['titulo'] = "Erro";
        $_SESSION['msg']['mensagem'] = "Erro reportado com a Efetivação da Alta!!!";
        $_SESSION['msg']['tipo'] = "error";

        echo 'Error: ' . $e->getMessage();
    }

    $_SESSION['iAtendimentoId'] = $iAtendimentoId;
    irpara("atendimentoEfetivacaoAlta.php");
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Lamparinas | Efetivação de Alta</title>

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

            getOrientacoesAlta();  
            getEvolucoes()        

            $('#tblOrientacaoAlta').DataTable( {
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
					width: "60%", 
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

            $('#tblTermoConsentimento').DataTable( {
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
					width: "60%", 
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
				}],
				dom: '<"datatable-header"fl><"datatable-scroll-wrap"t><"datatable-footer">',
				language: {
					search: '<span>Filtro:</span> _INPUT_',
					searchPlaceholder: 'filtra qualquer coluna...',
					lengthMenu: '<span>Mostrar:</span> _MENU_',
					paginate: { 'first': 'Primeira', 'last': 'Última', 'next': $('html').attr('dir') == 'rtl' ? '&larr;' : '&rarr;', 'previous': $('html').attr('dir') == 'rtl' ? '&rarr;' : '&larr;' }
				}
                
			});

            $('.salvarEfetivacaoAlta').on('click', function(e) {
                e.preventDefault();

                $("#formAtendimentoEfetivacaoAlta").submit();
            })


            $('#cmbTipoDeAlta').on('change', function(e) {
                e.preventDefault();

                let valorSelecionado = $("#cmbTipoDeAlta").val();
                
                $.ajax({
                    type: 'POST',
                    url: 'filtraAtendimento.php',
                    dataType: 'json',
                    data: {
                        'tipoRequest': 'GETMOTIVOALTA',
                        'valorSelecionado' : valorSelecionado,				
				
                    },
                    success: function(response) {                        
                        
                        response.forEach(item => {
                            $('#cmbMotivoAlta').empty();
                            $('#cmbMotivoAlta').append(`<option value=''>Selecione</option>`)
                    
                            response.forEach(item => {
                                let opt = `<option value="${item.id}">${item.nomeMotivo}</option>`
                                $('#cmbMotivoAlta').append(opt)
                            })                       
                        })
                        $('#cmbMotivoAlta').focus();
                    }
                }); 

                if (valorSelecionado === "") {

                    $(".box-obito").css('display', 'none');
				    $(".box-alta").css('display', 'none');

                } else if (valorSelecionado === "POROBITO") {

                    $(".box-obito").css('display', 'block');
				    $(".box-alta").css('display', 'none');
                    
                } else {

                    if (valorSelecionado === "PORTRANSFERENCIA") {

                        $('.titulo-box-alta').text("ALTA POR TRANSFERÊNCIA");
                        $(".box-transferencia").css('display', 'block');
                        
                    } else {

                        $(".box-transferencia").css('display', 'none');

                        if (valorSelecionado === "PORALTAMEDICA") {

                            $('.titulo-box-alta').text("ALTA MÉDICA");  

                        } else if (valorSelecionado === "PORPERMANENCIA") {

                            $('.titulo-box-alta').text("ALTA POR PERMANÊNCIA");

                        } else if (valorSelecionado === "PORALTAADMINISTRATIVA") {

                            $('.titulo-box-alta').text("ALTA ADMINISTRATIVA");

                        } else if (valorSelecionado === "PORPROCEDIMENTOSDEPARTO") {

                            $('.titulo-box-alta').text("ALTA POR PORCEDIMENTOS DE PARTO");

                        }
                        
                    }     

                    $(".box-alta").css('display', 'block');
			    	$(".box-obito").css('display', 'none');

                }              
            })

            $('.adicionarOrientacao').on('click', function (e) {

                e.preventDefault();

                let tipoOrientacao = $('#cmbTipoOrientacaoAlta').val()
                let profissionalOrientacao = $('#cmbProfissionalOrientacao').val()
                let orientacaoAlta = $('#inputOrientacaoAlta').val()

                $.ajax({
                    type: 'POST',
                    url: 'filtraAtendimento.php',
                    dataType: 'json',

                    data: {
                        'tipoRequest': 'ADICIONARORIENTACAOALTA',
                        'ideEfetivacao' : <?php echo isset($iAtendimentoEfetivacaoAlta) ? $iAtendimentoEfetivacaoAlta : 'null'; ?>,				
                        'tipoOrientacao' : tipoOrientacao,				
                        'profissionalOrientacao' : profissionalOrientacao,				
                        'orientacaoAlta' : orientacaoAlta				
                    },
                    success: function(response) {
                        if(response.status == 'success'){
                            alerta(response.titulo, response.menssagem, response.status)
                            zerarOrientacaoAlta()
                            getOrientacoesAlta()                        
                        }else{
                            alerta(response.titulo, response.menssagem, response.status)
                        }
                    }
                }); 
            });

            $('.editarOrientacao').on('click', function (e) {

                e.preventDefault();

                let idOrientacaoAlta = $('#idOrientacaoAlta').val()
                let tipoOrientacao = $('#cmbTipoOrientacaoAlta').val()
                let profissionalOrientacao = $('#cmbProfissionalOrientacao').val()
                let orientacaoAlta = $('#inputOrientacaoAlta').val()

                $.ajax({
                    type: 'POST',
                    url: 'filtraAtendimento.php',
                    dataType: 'json',

                    data: {
                        'tipoRequest': 'EDITARORIENTACAOALTA',
                        'idOrientacaoAlta' : idOrientacaoAlta,				
                        'tipoOrientacao' : tipoOrientacao,				
                        'profissionalOrientacao' : profissionalOrientacao,				
                        'orientacaoAlta' : orientacaoAlta				
                    },
                    success: function(response) {
                        if(response.status == 'success'){
                            alerta(response.titulo, response.menssagem, response.status)
                            zerarOrientacaoAlta()
                            getOrientacoesAlta()                           
                        }else{
                            alerta(response.titulo, response.menssagem, response.status)
                        }
                    }
                }); 
            });

            $('#cmbCondicoesPaciente').on('change', function(e) {
                e.preventDefault();

                let cmbCondicoesPaciente = $("#cmbCondicoesPaciente").val();

                if (cmbCondicoesPaciente === "OU") {
                    $(".box-inputCondicoesPacienteOutros").css('display', 'block');
                } else {
                    $(".box-inputCondicoesPacienteOutros").css('display', 'none');
                }              
            })

            $('#diagnosticoDeEntradaBTN').on('click',function(e){
                e.preventDefault()
                
                $('#page-modal-diagnosticoDeEntrada').fadeIn(200)

            })
            $('#evolucaoMedicaBTN').on('click',function(e){
                e.preventDefault()
                
                $('#page-modal-evolucaoMedica').fadeIn(200)
            })


            $('#modal-diagnosticoDeEntrada-x').on('click', function(e){
                e.preventDefault()
                $('#page-modal-diagnosticoDeEntrada').fadeOut(200)
            })
            $('#modal-evolucaoMedica-x').on('click', function(e){
                e.preventDefault()
                $('#page-modal-evolucaoMedica').fadeOut(200)
            })

        }); //document.ready

        function ext(path) {
			var final = path.substr(path.lastIndexOf('/')+1);
			var separador = final.lastIndexOf('.');
			return separador <= 0 ? '' : final.substr(separador + 1);
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

                        let anexos = `<a class='list-icons-item mr-2 ' style='color: black; cursor:pointer'  onclick='anexosEvolucao(\"${item.id}\")' class='list-icons-item' ><i class='icon-attachment' title='Anexos da Evolução'></i></a>`;
                        let acoes = ``;

                      
                        acoes = `<div class='list-icons'>
                            ${anexos}                                
                        </div>`;
                   
                        
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

        function anexosEvolucao(idEvolucao) {
            $('#idEvolucaoAnexo').val(idEvolucao);
            $('#inputClienteEvolucao').val('<?php echo $row['ClienNome']; ?>');
            $('#inputAtendimento').val('<?php echo $row['AtendNumRegistro']; ?>');
            document.formAnexo.action = "evolucaoAnexo.php";
            document.formAnexo.submit();
        }

        function getOrientacoesAlta() {

            $.ajax({
                type: 'POST',
                url: 'filtraAtendimento.php',
                dataType: 'json',
                data:{
                    'tipoRequest': 'GETORIENTACOESALTA',
                    'ideEfetivacao' : <?php echo isset($iAtendimentoEfetivacaoAlta) ? $iAtendimentoEfetivacaoAlta : 'null'; ?>
                },
                success: function(response) {
                                        
                    $('#dataOrientacao').html('');
                    let HTML = ''

                    response.forEach(item => {

                        let copiar = `<a class='list-icons-item mr-2 ' style='color: black; cursor:pointer' onclick='copiarOrientacaoAlta(\"${item.id}\")'><i class='icon-files-empty' title='Copiar Orientação'></i></a>`; 
                        let editar = `<a class='list-icons-item mr-2 ' style='color: black; cursor:pointer'  onclick='editarOrientacaoAlta(\"${item.id}\")' class='list-icons-item' ><i class='icon-pencil7' title='Editar Orientação'></i></a>`;
                        let exc = `<a style='color: black; cursor:pointer' onclick='excluirOrientacaoAlta(\"${item.id}\")' class='list-icons-item'><i class='icon-bin' title='Excluir Orientação'></i></a>`;
                        let acoes = ``;

                        if (item.editavel == 1) {                           
                            
                            acoes = `<div class='list-icons'>
                                ${copiar}
                                ${editar}
                                ${exc}
                            </div>`;
                            
                        } else {                           
                            
                            acoes = `<div class='list-icons'>
                                ${copiar}
                            </div>`;
                                                    
                        }

                        HTML += `
                        <tr class='orientacaoItem'>
                            <td class="text-left">${item.item}</td>
                            <td class="text-left">${item.dataHora}</td>
                            <td class="text-left">${item.orientacao}</td>
                            <td class="text-center">${acoes}</td>
                        </tr>`

                    })
                    
                    $('#dataOrientacao').html(HTML).show();
                           
                }
            });	
            
        }

        function editarOrientacaoAlta(id) {

            $.ajax({
                type: 'POST',
                url: 'filtraAtendimento.php',
                dataType: 'json',
                data:{
                    'tipoRequest': 'GETORIENTACAOALTA',
                    'id' : id
                },
                success: function(response) {
                    
                    $('#idOrientacaoAlta').val(response.EnEAOId)
                    $('#cmbTipoOrientacaoAlta').val(response.EnEAOTipoOrientacao).change()
                    $('#cmbProfissionalOrientacao').val(response.EnEAOProfissional).change()
                    $('#inputOrientacaoAlta').val(response.EnEAOOrientacao)
                    $(".adicionarOrientacao").css('display', 'none');
                    $(".editarOrientacao").css('display', 'block');		
                }
            });

        }

        function copiarOrientacaoAlta(id) {
            zerarOrientacaoAlta()
            $.ajax({
                type: 'POST',
                url: 'filtraAtendimento.php',
                dataType: 'json',
                data:{
                    'tipoRequest': 'GETORIENTACAOALTA',
                    'id' : id
                },
                success: function(response) {
                    
                    $('#cmbTipoOrientacaoAlta').val(response.EnEAOTipoOrientacao).change()
                    $('#cmbProfissionalOrientacao').val(response.EnEAOProfissional).change()
                    $('#inputOrientacaoAlta').val(response.EnEAOOrientacao)	
                }
            });
        }

        function excluirOrientacaoAlta(id) {
            confirmaExclusaoAjax('filtraAtendimento.php', 'Excluir Orientação?', 'DELETEORIENTACAOALTA', id, getOrientacoesAlta)
        }
        
        function zerarOrientacaoAlta() {
            $('#idOrientacaoAlta').val('')
            $('#cmbTipoOrientacaoAlta').val('').change()
            $('#cmbProfissionalOrientacao').val('').change()
            $('#inputOrientacaoAlta').val('')
            $(".adicionarOrientacao").css('display', 'block');
            $(".editarOrientacao").css('display', 'none');
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
                        <form name="formAtendimentoEfetivacaoAlta" id="formAtendimentoEfetivacaoAlta" enctype="multipart/form-data" method="post">
                            <?php
                            echo "<input type='hidden' id='iAtendimentoId' name='iAtendimentoId' value='$iAtendimentoId' />";
                            ?>
                            <div class="card">

                                <div class="col-md-12">
                                    <div class="row">

                                        <div class="col-md-6" style="text-align: left;">

                                            <div class="card-header header-elements-inline">
                                                <h3 class="card-title"><b>EFETIVAÇÃO DE ALTA HOSPITALAR</b></h3>
                                            </div>

                                        </div>

                                        <div class="col-md-6" style="text-align: right;">

                                            <div class="form-group" style="margin:20px;">
                                                <?php 
                                                    if (isset($SituaChave) && $SituaChave != "ATENDIDO") {
                                                        echo "<button class='btn btn-lg btn-success mr-1 salvarEfetivacaoAlta'>Salvar</button>";
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
                                <?php include('atendimentoDadosPacienteHospitalar.php'); ?>
                                <?php include('atendimentoDadosSinaisVitais.php'); ?>
                            </div>

                            <div class="card">

                                <div class="card-header header-elements-inline">
                                    <h3 class="card-title font-weight-bold ">RELATÓRIO DE ALTA</h3>  
                                </div>

                                <div class="card-body">

                                    <div class="col-lg-12 mb-2 row">                                                                                                                
                                                                               
                                        <div class="col-lg-2">
                                            <label>Data/ Hora</label>
                                            <input type="datetime-local" class="form-control" name="inputDataHora" id="inputDataHora" value="<?php echo isset($iAtendimentoEfetivacaoAlta) ? $rowEfetivacao['EnEfADataHoraAlta'] : ''; ?>" >
                                        </div>

                                        <div class="col-lg-4">
                                            <label>Tipo de Alta</label>
                                            <select id="cmbTipoDeAlta" name="cmbTipoDeAlta" class="select-search" >
                                                <option value="" >Selecione</option>
                                                <?php                                                   
                                                    foreach ($rowTipoDeAlta as $item){

                                                        $seleciona = $item['TpAltId'] == $rowEfetivacao['EnEfATipoAlta'] ? "selected" : "";    

                                                        print('<option value="'.$item['TpAltChave'].'" '. $seleciona .'>' . $item['TpAltNome'] .'</option>');
                                                    }
                                                ?>

                                            </select>											
                                        </div>

                                        <div class="col-lg-4">
                                            <label>Motivo da Alta</label>
                                            <select id="cmbMotivoAlta" name="cmbMotivoAlta" class="select-search" >
                                                <option value="" >Selecione</option>
                                                <?php                                                   
                                                    foreach ($rowMotivoAlta as $item){

                                                        $seleciona = $item['MtAltId'] == $rowEfetivacao['EnEfAMotivoAlta'] ? "selected" : "";

                                                        print('<option value="'.$item['MtAltId'].'" '. $seleciona .'>' . $item['MtAltNome'] .'</option>');
                                                    }
                                                ?>
                                                                                              
                                            </select>											
                                        </div>

                                        <div class="col-lg-2">
                                            <label>Retorno da Alta Data</label>
                                            <input type="date" class="form-control" name="inputDataRetornoAlta" id="inputDataRetornoAlta" value="<?php echo isset($iAtendimentoEfetivacaoAlta) ? $rowEfetivacao['EnEfARetornoAlta'] : ''; ?>" >
                                        </div>

                                    </div>
                                    
                                </div>                                 

                                <div class="box-alta" style="display: <?php echo isset($iAtendimentoEfetivacaoAlta) ? (( $rowEfetivacao['TpAltChave'] == 'PORALTAMEDICA' || $rowEfetivacao['TpAltChave'] == 'PORPERMANENCIA' || $rowEfetivacao['TpAltChave'] == 'PORALTAADMINISTRATIVA' || $rowEfetivacao['TpAltChave'] == 'PORTRANSFERENCIA' || $rowEfetivacao['TpAltChave'] == 'PORPROCEDIMENTOSDEPARTO' ) ? 'block' : 'none') : 'none'; ?>; ">

                                    <!-- titulo box -->
                                    <div class="card-header header-elements-inline">
                                        <h3 class="card-title font-weight-bold titulo-box-alta">
                                            <?php 
                                                if (isset($iAtendimentoEfetivacaoAlta) && $rowEfetivacao['TpAltChave'] == 'PORALTAMEDICA') {
                                                    echo 'ALTA MÉDICA';
                                                } elseif (isset($iAtendimentoEfetivacaoAlta) && $rowEfetivacao['TpAltChave'] == 'PORPERMANENCIA') {
                                                    echo 'ALTA POR PERMANÊNCIA';
                                                } elseif (isset($iAtendimentoEfetivacaoAlta) && $rowEfetivacao['TpAltChave'] == 'PORALTAADMINISTRATIVA') {
                                                    echo 'ALTA ADMINISTRATIVA';
                                                } elseif (isset($iAtendimentoEfetivacaoAlta) && $rowEfetivacao['TpAltChave'] == 'PORTRANSFERENCIA') {
                                                    echo 'ALTA POR TRANSFERÊNCIA';
                                                } elseif (isset($iAtendimentoEfetivacaoAlta) && $rowEfetivacao['TpAltChave'] == 'PORPROCEDIMENTOSDEPARTO') {
                                                    echo 'ALTA POR PORCEDIMENTOS DE PARTO';
                                                }                                                                            
                                            ?>
                                        </h3>  
                                    </div>

                                    <div class="card-body row"> 

                                        <div class="col-lg-12 ">

                                            <div class="col-lg-12 row" style="justify-content: space-between;">
                                                <label for="inputSumarioAltaMedica">Sumário de Alta Médica</label>
                                                <label f><a href='' class='' id="diagnosticoDeEntradaBTN" role='button'>Diagnóstico de Entrada</a> | <a href='' class='' id="evolucaoMedicaBTN" role='button'>Evolução Médica</a></label>                                            
                                            </div>

                                            <textarea rows="3"  maxLength="2000" onInput="contarCaracteres(this);"  id="inputSumarioAltaMedica" name="inputSumarioAltaMedica" class="form-control" placeholder="" ><?php  if (isset($iAtendimentoEfetivacaoAlta )) echo $rowEfetivacao['EnEfASumarioAltaMedica']; ?></textarea>
                                            <small class="text-muted form-text">Max. 2000 caracteres<span class="caracteresinputSumarioAltaMedica"></span></small>      
                                            
                                        </div>

                                        <div class="col-lg-12 row mt-2">                                     
                                            <div class="col-lg-3">
                                                <div class="form-group">
                                                    <label for="cmbCondicoesPaciente">Condições do Paciente</label>   
                                                    <select id="cmbCondicoesPaciente" name="cmbCondicoesPaciente" class="select-search" >
                                                        <option value="">Selecione</option>
                                                        <option value="DE" <?php echo isset($iAtendimentoEfetivacaoAlta) ? ($rowEfetivacao['EnEfACondicaoPaciente'] == 'DE' ? 'selected' : '' ) : ''; ?> >Deambulado</option>
                                                        <option value="CR" <?php echo isset($iAtendimentoEfetivacaoAlta) ? ($rowEfetivacao['EnEfACondicaoPaciente'] == 'CR' ? 'selected' : '' ) : ''; ?> >Em cadeira de rodas</option>
                                                        <option value="OU" <?php echo isset($iAtendimentoEfetivacaoAlta) ? ($rowEfetivacao['EnEfACondicaoPaciente'] == 'OU' ? 'selected' : '' ) : ''; ?> >Outros</option>
                                                    
                                                    </select>                                            
                                                </div>
                                            </div>                                            

                                            <div class="col-lg-5 ">
                                                <div class="form-group">
                                                    <label for="cmbDadosProfResponsavel">Dados do Profissional Responsável</label>
                                                    <select id="cmbDadosProfResponsavel" name="cmbDadosProfResponsavel" class="select-search" >
                                                        <option value="">Selecione</option>
                                                        <?php
                                                            foreach($rowProfissionais as $item){
                                                                if (isset($iAtendimentoEfetivacaoAlta) && $rowEfetivacao['EnEfAProfissionalResponsavel'] == $item['ProfiId'] ) {
                                                                    echo "<option value='$item[ProfiId]' selected>$item[ProfiNome] - $item[profissao] - $item[ProfiCbo]</option>";
                                                                } else {
                                                                    echo "<option value='$item[ProfiId]'>$item[ProfiNome] - $item[profissao] - $item[ProfiCbo]</option>";
                                                                }                                                          
                                                            }
                                                        ?>
                                                    </select>                                               
                                                </div>                                                
                                            </div>

                                            <div class="col-lg-4">
                                                <div class="form-group">
                                                    <label for="cmbClinica">Clínica</label>    
                                                    <select id="cmbClinica" name="cmbClinica" class="select-search" >
                                                        <option value="">Selecione</option>
                                                        <?php
                                                            foreach($rowEspecialidadeLeito as $item){
                                                                if (isset($iAtendimentoEfetivacaoAlta) && $rowEfetivacao['EnEfAClinica'] == $item['EsLeiId'] ) {
                                                                    echo "<option value='$item[EsLeiId]' selected> $item[EsLeiNome] </option>";
                                                                } else {
                                                                    echo "<option value='$item[EsLeiId]'> $item[EsLeiNome] </option>";
                                                                }                                                          
                                                            }
                                                        ?>
                                                    </select>                                           
                                                </div>
                                            </div>                                        
                                        </div>

                                        <div class="col-lg-6 box-inputCondicoesPacienteOutros " style="display: <?php echo isset($iAtendimentoEfetivacaoAlta) ? (( $rowEfetivacao['EnEfACondicaoPaciente'] == 'OU') ? 'block' : 'none') : 'none'; ?>; ">

                                            <div class="form-group">
                                                <input type="text" class="form-control" name="inputCondicoesPacienteOutros" id="inputCondicoesPacienteOutros" value="<?php echo isset($iAtendimentoEfetivacaoAlta) ? $rowEfetivacao['EnEfAOutros'] : ''; ?>" maxLength="100" placeholder="Descrição Condições do Paciente (Outros)." >                                                                                      
                                            </div>
                                    
                                        </div>
                                        
                                        <div class="col-lg-12 row mt-2">

                                            <div class="col-lg-5">
                                                <div class="form-group">
                                                    <label for="cmbTipoTransporte">Tipo de Transporte</label>    
                                                    <select id="cmbTipoTransporte" name="cmbTipoTransporte" class="select-search" >
                                                        <option value="">Selecione</option>
                                                        <option value="AM" <?php echo isset($iAtendimentoEfetivacaoAlta) ? ($rowEfetivacao['EnEfATipoTransporte'] == 'AM' ? 'selected' : '' ) : ''; ?> >Ambulância</option>
                                                        <option value="CP" <?php echo isset($iAtendimentoEfetivacaoAlta) ? ($rowEfetivacao['EnEfATipoTransporte'] == 'CP' ? 'selected' : '' ) : ''; ?> >Carro Próprio</option>
                                                        <option value="OU" <?php echo isset($iAtendimentoEfetivacaoAlta) ? ($rowEfetivacao['EnEfATipoTransporte'] == 'OU' ? 'selected' : '' ) : ''; ?> >Outros</option>            
                                                    </select>                                           
                                                </div>
                                            </div>

                                            <div class="col-lg-2">
                                                <div class="form-group">
                                                    <label for="cmbAcompanhante">Acompanhante</label>
                                                    <select id="cmbAcompanhante" name="cmbAcompanhante" class="select-search" >
                                                        <option value="">Selecione</option>
                                                        <option value="1" <?php echo isset($iAtendimentoEfetivacaoAlta) ? ($rowEfetivacao['EnEfAAcompanhante'] == '1' ? 'selected' : '' ) : ''; ?> >Sim</option>
                                                        <option value="0" <?php echo isset($iAtendimentoEfetivacaoAlta) ? ($rowEfetivacao['EnEfAAcompanhante'] == '0' ? 'selected' : '' ) : ''; ?> >Não</option>
                                                    </select>                                               
                                                </div>
                                            </div>

                                            <div class="col-lg-5">
                                                <div class="form-group">
                                                    <label for="inputNomeAcompanhante">Nome do Acompanhante</label>
                                                    <input type="text" class="form-control" name="inputNomeAcompanhante" id="inputNomeAcompanhante" value="<?php echo isset($iAtendimentoEfetivacaoAlta) ? $rowEfetivacao['EnEfAAcompanhanteNome'] : ''; ?>" >                                                                                      
                                                </div>
                                            </div>

                                        </div>

                                        <div class="col-lg-12 mt-2 box-transferencia" style="display: <?php echo isset($iAtendimentoEfetivacaoAlta) ? (( $rowEfetivacao['TpAltChave'] == 'PORTRANSFERENCIA') ? 'block' : 'none') : 'none'; ?>; ">
                                            
                                            <div class="row">
                                            
                                                <div class="col-lg-2">
                                                    <div class="form-group">
                                                        <label for="cmbTransferencia">Transferência</label>
                                                        <select id="cmbTransferencia" name="cmbTransferencia" class="select-search" >
                                                            <option value="">Selecione</option>
                                                            <option value="I" <?php echo isset($iAtendimentoEfetivacaoAlta) ? ($rowEfetivacao['EnEfATransferencia'] == 'I' ? 'selected' : '' ) : ''; ?> >Interna</option>
                                                            <option value="E" <?php echo isset($iAtendimentoEfetivacaoAlta) ? ($rowEfetivacao['EnEfATransferencia'] == 'E' ? 'selected' : '' ) : ''; ?> >Externa</option>
                                                        </select>                                               
                                                    </div>
                                                </div>
                                                <div class="col-lg-3">
                                                    <div class="form-group">
                                                        <label for="cmbLocalTransferencia">Local da Transferência</label>
                                                        <select id="cmbLocalTransferencia" name="cmbLocalTransferencia" class="select-search" >
                                                            <option value="">Selecione</option>
                                                            <?php
                                                                foreach($rowEstabelecimentos as $item){
                                                                    if (isset($iAtendimentoEfetivacaoAlta) && $rowEfetivacao['EnEfALocalTransferencia'] == $item['EstabId'] ) {
                                                                        echo "<option value='$item[EstabId]' selected> $item[EstabNome] </option>";
                                                                    } else {
                                                                        echo "<option value='$item[EstabId]'> $item[EstabNome] </option>";
                                                                    }                                                          
                                                                }
                                                            ?>
                                                        </select>                                               
                                                    </div>
                                                </div>

                                            </div>

                                        </div>                                   
                                        
                                        <div class="col-lg-6">                                              
                                            <label for="inputJustificativaAlta">Justificativa da Alta</label>
                                            <textarea rows="3"  maxLength="500" onInput="contarCaracteres(this);"  id="inputJustificativaAlta" name="inputJustificativaAlta" class="form-control" placeholder="" ><?php  if (isset($iAtendimentoEfetivacaoAlta )) echo $rowEfetivacao['EnEfAJustificativaAlta']; ?></textarea>
                                            <small class="text-muted form-text">Max. 500 caracteres<span class="caracteresinputJustificativaAlta"></span></small>                                                                                                  
                                        </div>
                                        <div class="col-lg-6">                                               
                                                <label for="inputProcMedAdministrada">Procedimentos e Medicação Administrada (digitação livre)</label>
                                                <textarea rows="3"  maxLength="500" onInput="contarCaracteres(this);"  id="inputProcMedAdministrada" name="inputProcMedAdministrada" class="form-control" placeholder="" ><?php if (isset($iAtendimentoEfetivacaoAlta )) echo $rowEfetivacao['EnEfAProcedimentoMedicacao']; ?></textarea>
                                                <small class="text-muted form-text">Max. 500 caracteres<span class="caracteresinputProcMedAdministrada"></span></small>                                                                                                                                     
                                        </div>

                                    </div>

                                    <div class="card-header header-elements-inline">
                                        <h3 class="card-title font-weight-bold ">Diagnóstico Principal</h3>  
                                    </div>

                                    <div class="card-body mb-2 row">
                                        
                                        <div class="col-lg-6">
                                            <label>CID <span class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-lg-6">
                                            <label>Procedimento <span class="text-danger">*</span></label>
                                        </div>                                        
                                                                                
                                        <div class="col-lg-6">
                                            <select id="cmbCId10" name="cmbCId10" class="select-search" >
                                                <option value="">Selecione</option>
                                                <?php                                               
                                                    foreach ($rowCid10 as $item) {
                                                        $seleciona = $item['Cid10Id'] == $rowEfetivacao['EnEfACid10'] ? "selected" : "";
                                                        print('<option value="' . $item['Cid10Id'] . '" ' . $seleciona . '>' . $item['Cid10Codigo'] . ' - ' . $item['Cid10Descricao'] . ' ' . '</option>');
                                                    }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="col-lg-6">
                                            <select id="cmbProcedimentoDiagnostico" name="cmbProcedimentoDiagnostico" class="select-search" >
                                                <option value="">Selecione</option>
                                                <?php                                               
                                                    foreach ($rowProcedimentoDiagnostico as $item) {
                                                        $seleciona = $item['SrVenId'] == $rowEfetivacao['EnEfAProcedimentoRealizado'] ? "selected" : "";
                                                        print('<option value="' . $item['SrVenId'] . '" ' . $seleciona . '>' . $item['SrVenCodigo'] . ' - ' . $item['SrVenNome'] . '</option>');
                                                    }
                                                ?>
                                            </select>											
                                        </div>

                                    </div>                             

                                </div>

                                <div class="box-obito" style="display: <?php echo isset($iAtendimentoEfetivacaoAlta) ? (( $rowEfetivacao['TpAltChave'] == 'POROBITO') ? 'block' : 'none') : 'none'; ?>; " >

                                    <div class="card-header header-elements-inline">
                                        <h3 class="card-title font-weight-bold ">Óbito</h3>  
                                    </div>

                                    <div class="card-body">

                                        <div class="col-lg-12 mb-3 row">
                                            
                                            <div class="col-lg-3">
                                                <label>Declaração de Óbito Nº</label>
                                            </div>
                                            <div class="col-lg-4">
                                                <label>Dados do Profissional Responsável</label>
                                            </div>
                                            <div class="col-lg-5">
                                                <label>Cópia da Declaração de Óbito</label>
                                            </div>
                                            
                                                                                    
                                            <div class="col-lg-3">
                                                <input type="text" class="form-control" name="declaracaoNObito" id="declaracaoNObito" value="<?php echo isset($iAtendimentoEfetivacaoAlta) ? $rowEfetivacao['EnEfANumObito'] : ''; ?>" >
                                            </div>

                                            <div class="col-lg-4">
                                                <select id="dadosProfissionalRespObito" name="dadosProfissionalRespObito" class="select-search" >
                                                    <option value="">Selecione</option>
                                                    <?php
                                                        foreach($rowProfissionais as $item){
                                                            if (isset($iAtendimentoEfetivacaoAlta) && $rowEfetivacao['EnEfAProfissionalResponsavel'] == $item['ProfiId'] ) {
                                                                echo "<option value='$item[ProfiId]' selected>$item[ProfiNome] - $item[profissao] - $item[ProfiCbo]</option>";
                                                            } else {
                                                                echo "<option value='$item[ProfiId]'>$item[ProfiNome] - $item[profissao] - $item[ProfiCbo]</option>";
                                                            }                                                          
                                                        }
                                                    ?>                                                    
                                                </select>											
                                            </div>

                                            <div class="col-lg-5">
                                                <input type="file" class="form-control" name="copiaDeclaracaoObito" id="copiaDeclaracaoObito" >
                                                <?php if (isset($iAtendimentoEfetivacaoAlta) && $rowEfetivacao['EnEfAArquivoDeclaracaoObito'] != '' ) { ?>

                                                    <small class=" form-text">
                                                        <a href="global_assets/anexos/declaracaoObito/<?php echo $rowEfetivacao['EnEfAArquivoDeclaracaoObito']; ?>" target="_blank">
                                                            <?php echo $rowEfetivacao['EnEfAArquivoDeclaracaoObito'] ?>
                                                        </a>
                                                    </small>

                                                <?php } ?>
                                            </div>

                                        </div>

                                        <div class="col-lg-12  row">                                            
                                            <div class="col-lg-3">
                                                <label>Causas do Óbito</label>
                                            </div>
                                        </div>

                                        <div class="col-lg-8 row mb-3">
                                            <div class="col-lg-6">
                                                <input type="text" class="form-control" name="inputCausaObitoA" id="inputCausaObitoA" placeholder="A: Digitação Livre" value="<?php echo isset($iAtendimentoEfetivacaoAlta) ? $rowEfetivacao['EnEfACausaObitoA'] : ''; ?>" >
                                            </div>
                                            <div class="col-lg-3">
                                                <input type="datetime-local" class="form-control" name="inputDataHoraObitoA" id="inputDataHoraObitoA" value="<?php echo isset($iAtendimentoEfetivacaoAlta) ? $rowEfetivacao['EnEfADataHoraObitoA'] : ''; ?>" >
                                            </div>
                                            <div class="col-lg-3">
                                                <select name="inputCidObitoA" id="inputCidObitoA" class=" form-control select-search">
                                                    <option value="">Selecione</option>
                                                    <?php
                                                        foreach ($rowCid10 as $item) {
                                                            $seleciona = $item['Cid10Id'] == $rowEfetivacao['EnEfACidA'] ? "selected" : "";
                                                            print('<option value="' . $item['Cid10Id'] . '" ' . $seleciona . '>' . $item['Cid10Codigo'] . '</option>');
                                                        }
                                                    ?>
                                                </select>                                                 
                                            </div>
                                        </div>

                                        <div class="col-lg-8 row mb-3">
                                            <div class="col-lg-6">
                                                <input type="text" class="form-control" name="inputCausaObitoB" id="inputCausaObitoB" placeholder="B: Digitação Livre" value="<?php echo isset($iAtendimentoEfetivacaoAlta) ? $rowEfetivacao['EnEfACausaObitoB'] : ''; ?>"  >
                                            </div>
                                            <div class="col-lg-3">
                                                <input type="datetime-local" class="form-control" name="inputDataHoraObitoB" id="inputDataHoraObitoB" value="<?php echo isset($iAtendimentoEfetivacaoAlta) ? $rowEfetivacao['EnEfADataHoraObitoB'] : ''; ?>" >
                                            </div>
                                            <div class="col-lg-3">
                                                <select name="inputCidObitoB" id="inputCidObitoB" class=" form-control select-search">
                                                    <option value="">Selecione</option>
                                                    <?php
                                                        foreach ($rowCid10 as $item) {
                                                            $seleciona = $item['Cid10Id'] == $rowEfetivacao['EnEfACidB'] ? "selected" : "";
                                                            print('<option value="' . $item['Cid10Id'] . '" ' . $seleciona . '>' . $item['Cid10Codigo'] . '</option>');
                                                        }
                                                    ?>
                                                </select>   
                                            </div>
                                        </div>

                                        <div class="col-lg-8 row mb-3">
                                            <div class="col-lg-6">
                                                <input type="text" class="form-control" name="inputCausaObitoC" id="inputCausaObitoC" placeholder="C: Digitação Livre" value="<?php echo isset($iAtendimentoEfetivacaoAlta) ? $rowEfetivacao['EnEfACausaObitoC'] : ''; ?>" >
                                            </div>
                                            <div class="col-lg-3">
                                                <input type="datetime-local" class="form-control" name="inputDataHoraObitoC" id="inputDataHoraObitoC" value="<?php echo isset($iAtendimentoEfetivacaoAlta) ? $rowEfetivacao['EnEfADataHoraObitoC'] : ''; ?>" >
                                            </div>
                                            <div class="col-lg-3">
                                                <select name="inputCidObitoC" id="inputCidObitoC" class=" form-control select-search">
                                                    <option value="">Selecione</option>
                                                    <?php
                                                        foreach ($rowCid10 as $item) {
                                                            $seleciona = $item['Cid10Id'] == $rowEfetivacao['EnEfACidC'] ? "selected" : "";
                                                            print('<option value="' . $item['Cid10Id'] . '" ' . $seleciona . '>' . $item['Cid10Codigo'] . '</option>');
                                                        }
                                                    ?>
                                                </select>   
                                            </div>
                                        </div>

                                        <div class="col-lg-8 row mb-3">
                                            <div class="col-lg-6">
                                                <input type="text" class="form-control" name="inputCausaObitoD" id="inputCausaObitoD" placeholder="D: Digitação Livre" value="<?php echo isset($iAtendimentoEfetivacaoAlta) ? $rowEfetivacao['EnEfACausaObitoD'] : ''; ?>" >
                                            </div>
                                            <div class="col-lg-3">
                                                <input type="datetime-local" class="form-control" name="inputDataHoraObitoD" id="inputDataHoraObitoD" value="<?php echo isset($iAtendimentoEfetivacaoAlta) ? $rowEfetivacao['EnEfADataHoraObitoD'] : ''; ?>" >
                                            </div>
                                            <div class="col-lg-3">
                                                <select name="inputCidObitoD" id="inputCidObitoD" class=" form-control select-search">
                                                    <option value="">Selecione</option>
                                                    <?php
                                                        foreach ($rowCid10 as $item) {
                                                            $seleciona = $item['Cid10Id'] == $rowEfetivacao['EnEfACidD'] ? "selected" : "";
                                                            print('<option value="' . $item['Cid10Id'] . '" ' . $seleciona . '>' . $item['Cid10Codigo'] . '</option>');
                                                        }
                                                    ?>
                                                </select>   
                                            </div>
                                        </div>
                                        
                                    </div>  

                                </div>
                
                            </div>

                            <div class="card">
                                <div class=" card-body row">
                                    <div class="col-lg-12">
                                        <div class="form-group" style="margin-bottom:0px;">
                                            <?php 
                                                if (isset($SituaChave) && $SituaChave != "ATENDIDO") {
                                                    echo "<button class='btn btn-lg btn-success mr-1 salvarEfetivacaoAlta'>Salvar</button>";
                                                }
                                            ?>
                                            <button type="button" class="btn btn-lg btn-secondary mr-1">Imprimir</button>
                                            <a href='atendimentoHospitalarListagem.php' class='btn btn-basic' role='button'>Voltar</a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php if ( isset($iAtendimentoEfetivacaoAlta) && ( $rowEfetivacao['TpAltChave'] != 'POROBITO' ) ) { ?>

                                <div class="card">

                                    <div class="card-header header-elements-inline">
                                        <h3 class="card-title font-weight-bold ">Orientações da Alta</h3>  
                                    </div>

                                    <div class="card-body row"> 

                                        <input type="hidden" name="idOrientacaoAlta" id="idOrientacaoAlta">

                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label for="cmbTipoOrientacaoAlta">Tipo de Orientações da Alta</label>   
                                                <select id="cmbTipoOrientacaoAlta" name="cmbTipoOrientacaoAlta" class="select-search" >
                                                    <option value="">Selecione</option>
                                                    <option value="EN">Enfermagem</option>
                                                    <option value="SS">Serviço Social</option>
                                                    <option value="PS">Psicologia</option>
                                                    <option value="NU">Nutrição</option>
                                                    <option value="FI">Fisioterapia</option>
                                                    <option value="OU">Outros</option>                                            
                                                </select>                                            
                                            </div>
                                        </div>

                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <label for="cmbProfissionalOrientacao">Profissional</label> 
                                                <select id="cmbProfissionalOrientacao" name="cmbProfissionalOrientacao" class="select-search" >
                                                    <option value="">Selecione</option>
                                                    <?php
                                                        foreach($rowProfissionais as $item){                                                        
                                                            echo "<option value='$item[ProfiId]'>$item[ProfiNome] - $item[profissao] - $item[ProfiCbo]</option>";                                                                                                                 
                                                        }
                                                    ?>                                             
                                                </select>                                      
                                            </div>
                                        </div>

                                        <div class="col-lg-4 ">
                                            <div class="form-group">
                                                <label for="inputOrientacaoAlta">Orientações da Alta</label>
                                                <input type="text" class="form-control" name="inputOrientacaoAlta" id="inputOrientacaoAlta" maxLength="100">                                              
                                            </div>
                                            
                                        </div>

                                        <div class="col-lg-1" style="margin-top: 15px;">
                                            <a class="btn btn-lg btn-principal adicionarOrientacao">
                                                <i class='icon-plus3' title='Adicionar'></i>
                                            </a>
                                            <a class="btn btn-lg btn-success editarOrientacao" style="display: none;">
                                                <i class='icon-plus3' title='Salvar Alterações'></i>
                                            </a>
                                        </div>       

                                    </div>

                                    <div class="row">
                                        <div class="col-lg-12">
                                            <table class="table" id="tblOrientacaoAlta">
                                                <thead>
                                                    <tr class="bg-slate">
                                                        <th class="text-left">Item</th>
                                                        <th class="text-left">Data/ Hora</th>
                                                        <th class="text-left">Descrição</th>
                                                        <th class="text-center">Ações</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="dataOrientacao">
                                                </tbody>
                                            </table>
                                        </div>		
                                    </div>			

                                </div>

                            <?php } ?>
                          

                            
                            <!--Modal-->
                            <div id="page-modal-diagnosticoDeEntrada" class="custon-modal">
                                <div class="custon-modal-container" style="max-width: 1000px">
                                    <div class="card custon-modal-content">
                                        <div class="custon-modal-title mb-2" style="background-color: #466d96; color: #ffffff">
                                            <p class="h5">Diagnóstico de Entrada</p>
                                            <i id="modal-diagnosticoDeEntrada-x" class="fab-icon-open icon-cross2 p-3" style="cursor: pointer"></i>
                                        </div>
                                        <div class="px-0" style="overflow-y: scroll;">
                                            <div class="d-flex flex-row">
                                                <div class="col-lg-12">
                                                    
                                                    <div class="card-header header-elements-inline">
                                                        <h3 class="card-title font-weight-bold ">DIAGNÓSTICO PRINCIPAL</h3>  
                                                    </div>

                                                    <div class="card-body">

                                                        <div class="col-lg-12 mb-2 row">
                                                            <!-- titulos -->
                                                            <div class="col-lg-6">
                                                                <label>CID</label>
                                                            </div>
                                                            <div class="col-lg-6">
                                                                <label>Procedimento</label>
                                                            </div>
                                                            
                                                            <!-- campos -->										
                                                            <div class="col-lg-6">
                                                                <input type="text" class="form-control" value="<?php if (isset($iAtendimentoAnamneseId )) echo $rowAnamnese['Cid10Descricao']; ?>" readonly>
                                                            </div>
                                                            <div class="col-lg-6">
                                                                <input type="text" class="form-control" value="<?php if (isset($iAtendimentoAnamneseId )) echo $rowAnamnese['SrVenNome']; ?>" readonly>									
                                                            </div>

                                                        </div>
                                                        
                                                    </div>

                                                    <div class="card-header header-elements-inline">
                                                        <h3 class="card-title font-weight-bold ">Anamnese</h3>  
                                                    </div>

                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col-lg-9">
                                                                <div class="row">
                                                                    <div class="col-lg-12">
                                                                        <div class="form-group"> 
                                                                            <a href="#collapse1-link" class="font-weight-semibold collapsed" data-toggle="collapse" aria-expanded="false"><h5> 1. Queixa Principal (QP)</h5></a>   
                                                                            <div class="collapse" id="collapse1-link" >
                                                                                <div class="mt-3">
                                                                                    <textarea rows="4" cols="4" maxLength="500" onInput="contarCaracteres(this);"  id="summernote1" name="txtareaConteudo1" class="form-control" placeholder="" ><?php if (isset($iAtendimentoAnamneseId )) echo $rowAnamnese['EnAnaQueixaPrincipal']; ?></textarea>
                                                                                    <small class="text-muted form-text">Max. 500 caracteres<span class="caracteressummernote1"></span></small>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-lg-12">
                                                                        <div class="form-group">
                                                                            <a href="#collapse2-link" class="font-weight-semibold collapsed" data-toggle="collapse" aria-expanded="false"><h5> 1.1. História da Moléstia Atual (HMA)</h5></a>   
                                                                            <div class="collapse" id="collapse2-link" >
                                                                                <div class="mt-3">
                                                                                    <textarea rows="4" cols="4" maxLength="500" onInput="contarCaracteres(this);" id="summernote2" name="txtareaConteudo2" class="form-control" placeholder="" ><?php if (isset($iAtendimentoAnamneseId )) echo $rowAnamnese['EnAnaHistoriaMolestiaAtual']; ?></textarea>
                                                                                    <small class="text-muted form-text">Max. 500 caracteres<span class="caracteressummernote2"></span></small>
                                                                                
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-lg-12">
                                                                        <div class="form-group">
                                                                            <a href="#collapse3-link" class="font-weight-semibold collapsed" data-toggle="collapse" aria-expanded="false"><h5> 1.2. História Patológica Pregressa</h5></a>   
                                                                            <div class="collapse" id="collapse3-link" >
                                                                                <div class="mt-3">
                                                                                    <textarea rows="4" cols="4" maxLength="500" onInput="contarCaracteres(this);" id="summernote3" name="txtareaConteudo3" class="form-control" placeholder="" ><?php if (isset($iAtendimentoAnamneseId )) echo $rowAnamnese['EnAnaHistoriaPatologicaPregressa']; ?></textarea>
                                                                                    <small class="text-muted form-text">Max. 500 caracteres<span class="caracteressummernote3"></span></small>
                                                                                
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-lg-12">
                                                                        <div class="form-group">
                                                                            <a href="#collapse4-link" class="font-weight-semibold collapsed" data-toggle="collapse" aria-expanded="false"><h5> 1.3. História Familiar</h5></a>   
                                                                            <div class="collapse" id="collapse4-link" >
                                                                                <div class="mt-3">
                                                                                    <textarea rows="4" cols="4" maxLength="500" onInput="contarCaracteres(this);" id="summernote4" name="txtareaConteudo4" class="form-control" placeholder="" ><?php if (isset($iAtendimentoAnamneseId )) echo $rowAnamnese['EnAnaHistoriaFamiliar']; ?></textarea>
                                                                                    <small class="text-muted form-text">Max. 500 caracteres<span class="caracteressummernote4"></span></small>
                                                                                
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-lg-12">
                                                                        <div class="form-group">
                                                                            <a href="#collapse5-link" class="font-weight-semibold collapsed" data-toggle="collapse" aria-expanded="false"><h5> 1.4. História Sócioeconômica</h5></a>   
                                                                            <div class="collapse" id="collapse5-link" >
                                                                                <div class="mt-3">
                                                                                    <textarea rows="4" cols="4" maxLength="500" onInput="contarCaracteres(this);" id="summernote5" name="txtareaConteudo5" class="form-control" placeholder="" ><?php if (isset($iAtendimentoAnamneseId )) echo $rowAnamnese['EnAnaHipoteseSocioEconomica']; ?></textarea>
                                                                                    <small class="text-muted form-text">Max. 500 caracteres<span class="caracteressummernote5"></span></small>
                                                                                
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-lg-12">
                                                                        <div class="form-group">
                                                                            <a href="#collapse6-link" class="font-weight-semibold collapsed" data-toggle="collapse" aria-expanded="false"><h5> 2. Anamnese (Digitação Livre)</h5></a>   
                                                                            <div class="collapse" id="collapse6-link" >
                                                                                <div class="mt-3">
                                                                                    <textarea rows="5" cols="5" maxLength="1000" onInput="contarCaracteres(this);" id="summernote6" name="txtareaConteudo6" class="form-control" placeholder="" ><?php if (isset($iAtendimentoAnamneseId )) echo $rowAnamnese['EnAnaDigitacaoLivre']; ?></textarea>
                                                                                    <small class="text-muted form-text">Max. 1000 caracteres<span class="caracteressummernote6"></span></small>
                                                                                
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>                                                
                                                            </div>                                            
                                                        </div>                                         
                                                    </div>                                                                                                   
                                                                                                                                                        
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!--Modal-->
                            <div id="page-modal-evolucaoMedica" class="custon-modal">
                                <div class="custon-modal-container" style="max-width: 1000px">
                                    <div class="card custon-modal-content">
                                        <div class="custon-modal-title mb-2" style="background-color: #466d96; color: #ffffff">
                                            <p class="h5">Evolução Médica</p>
                                            <i id="modal-evolucaoMedica-x" class="fab-icon-open icon-cross2 p-3" style="cursor: pointer"></i>
                                        </div>
                                        <div class="px-0" style="overflow-y: scroll;">
                                            <div class="d-flex flex-row">
                                                <div class="col-lg-12">
                                                                                                        
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <table class="table" id="tblEvolucao">
                                                                <thead>
                                                                    <tr class="bg-slate">
                                                                        <th class="text-left">Item</th>
                                                                        <th class="text-left">Data/ Hora</th>
                                                                        <th class="text-left">Justificativa de Lançamento Retroativo</th>
                                                                        <th class="text-left">Evolução Diária</th>
                                                                        <th class="text-left">Profissional/ CBO</th>
                                                                        <th class="text-center">Ações</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody id="dataEvolucao">
                                                                </tbody>
                                                            </table>
                                                        </div>		
                                                    </div>

                                                    <form method="post" name="formAnexo" target="print_popup">
                                                        <input type="hidden" id="idEvolucaoAnexo" name="idEvolucaoAnexo">
                                                        <input type="hidden" id="inputClienteEvolucao" name="inputClienteEvolucao">
                                                        <input type="hidden" id="inputAtendimento" name="inputAtendimento">
                                                    </form> 
                                                    
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