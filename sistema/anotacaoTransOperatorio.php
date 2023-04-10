<?php 

    include_once("sessao.php"); 

    $_SESSION['PaginaAtual'] = 'Anotação Trans-Operatória';

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

    //anotação trans-operatória
    $sql = "SELECT TOP(1) *
    FROM EnfermagemTransOperatoria
    WHERE EnTrOAtendimento = $iAtendimentoId
    ORDER BY EnTrOId DESC";
    $result = $conn->query($sql);
    $rowAnotacao = $result->fetch(PDO::FETCH_ASSOC);

    $iAtendimentoAnotacao = $rowAnotacao?$rowAnotacao['EnTrOId']:null;

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

    $sql = "SELECT P.ProfiId,P.ProfiNome,PFS.ProfiCbo,PFS.ProfiNome as profissao
    FROM Profissional P
    JOIN Profissao PFS ON PFS.ProfiId = P.ProfiProfissao
    WHERE P.ProfiUnidade = $_SESSION[UnidadeId]";
    $result = $conn->query($sql);
    $rowProfissionais = $result->fetchAll(PDO::FETCH_ASSOC);

    $sql = "SELECT KtCmeId,KtCmeNome
    FROM KitCme
    WHERE KtCmeUnidade = $_SESSION[UnidadeId]";
    $result = $conn->query($sql);
    $rowKitCME = $result->fetchAll(PDO::FETCH_ASSOC);

    if(isset($_POST['entradaHora'])){

        if ($iAtendimentoAnotacao) {

            $sql = "UPDATE EnfermagemTransOperatoria SET
            EnTrOAtendimento = :EnTrOAtendimento,
            EnTrODataInicio = :EnTrODataInicio,
            EnTrOHoraInicio = :EnTrOHoraInicio,
            EnTrOPrevisaoAlta = :EnTrOPrevisaoAlta,
            EnTrOTipoInternacao = :EnTrOTipoInternacao,
            EnTrOEspecialidadeLeito = :EnTrOEspecialidadeLeito,
            EnTrOAla  = :EnTrOAla,
            EnTrOQuarto   = :EnTrOQuarto,
            EnTrOLeito = :EnTrOLeito,
            EnTrOProfissional = :EnTrOProfissional,
            EnTrOPas = :EnTrOPas,
            EnTrOPad = :EnTrOPad,
            EnTrOFreqCardiaca = :EnTrOFreqCardiaca,
            EnTrOFreqRespiratoria = :EnTrOFreqRespiratoria,
            EnTrOTemperatura = :EnTrOTemperatura,
            EnTrOSPO = :EnTrOSPO,
            EnTrOHGT = :EnTrOHGT,
            EnTrOPeso = :EnTrOPeso,
            EnTrOHoraEntrada = :EnTrOHoraEntrada,
            EnTrOHoraSaida = :EnTrOHoraSaida,
            EnTrOSala = :EnTrOSala,
            EnTrOProfiCirculante = :EnTrOProfiCirculante,
            EnTrOInicioAnestesia = :EnTrOInicioAnestesia,
            EnTrOTerminoAnestesia = :EnTrOTerminoAnestesia,
            EnTrOTipoAnestesia = :EnTrOTipoAnestesia,
            EnTrOProfiAnestesista = :EnTrOProfiAnestesista,
            EnTrOProfiInstrumentador = :EnTrOProfiInstrumentador,
            EnTrOInicioCirurgia = :EnTrOInicioCirurgia,
            EnTrOTerminoCirurgia = :EnTrOTerminoCirurgia,
            EnTrOProfiCirurgiao = :EnTrOProfiCirurgiao,
            EnTrOProfiCirurgiaoAssistente = :EnTrOProfiCirurgiaoAssistente,
            EnTrODescPosicaoOperatoria = :EnTrODescPosicaoOperatoria,
            EnTrOServAdicionalBancoSangue = :EnTrOServAdicionalBancoSangue,
            EnTrOServAdicionalRadiologia = :EnTrOServAdicionalRadiologia,
            EnTrOServAdicionalLaboratorio = :EnTrOServAdicionalLaboratorio,
            EnTrOServAdicionalAnatPatologica = :EnTrOServAdicionalAnatPatologica,
            EnTrOServAdicionalUsoContraste = :EnTrOServAdicionalUsoContraste,
            EnTrOServAdicionalOutros = :EnTrOServAdicionalOutros,
            EnTrOEncaminhamentoPosCirurgia = :EnTrOEncaminhamentoPosCirurgia,
            EnTrOEmUsoCateterEpidural = :EnTrOEmUsoCateterEpidural,
            EnTrOEmUsoDrenoTubular = :EnTrOEmUsoDrenoTubular,
            EnTrOEmUsoEntubacaoTraqueal = :EnTrOEmUsoEntubacaoTraqueal,
            EnTrOEmUsoIntracath = :EnTrOEmUsoIntracath,
            EnTrOEmUsoKehr = :EnTrOEmUsoKehr,
            EnTrOEmUsoPecaCirurgica = :EnTrOEmUsoPecaCirurgica,
            EnTrOEmUsoPenrose = :EnTrOEmUsoPenrose,
            EnTrOEmUsoProntuario = :EnTrOEmUsoProntuario,
            EnTrOEmUsoPuncaoPeriferica = :EnTrOEmUsoPuncaoPeriferica,
            EnTrOEmUsoRadiografia = :EnTrOEmUsoRadiografia,
            EnTrOEmUsoSistemaSuccao = :EnTrOEmUsoSistemaSuccao,
            EnTrOEmUsoSondaGastrica = :EnTrOEmUsoSondaGastrica,
            EnTrOEmUsoSondaVesical = :EnTrOEmUsoSondaVesical,
            EnTrOProfiEnfermeiro = :EnTrOProfiEnfermeiro,
            EnTrOProfiTecnico = :EnTrOProfiTecnico,
            EnTrOProfiEnfermeiroCCO = :EnTrOProfiEnfermeiroCCO,
            EnTrOProfiTecnicoCCO = :EnTrOProfiTecnicoCCO,
            EnTrOProfiTecnicoRPA = :EnTrOProfiTecnicoRPA,
            EnTrOKitCME = :EnTrOKitCME,
            EnTrOMedicacaoAdministrada = :EnTrOMedicacaoAdministrada,
            EnTrOObservacao = :EnTrOObservacao
            WHERE EnTrOId = :iAtendimentoAnotacao";
                
            $result = $conn->prepare($sql);
                    
            $result->execute(array(
                ':EnTrOAtendimento' => $iAtendimentoId,
                ':EnTrODataInicio' => date('Y-m-d'),
                ':EnTrOHoraInicio' => date('H:i'),
                ':EnTrOPrevisaoAlta' => '',
                ':EnTrOTipoInternacao' => $row['TpIntId'],
                ':EnTrOEspecialidadeLeito' => $row['EsLeiId'],
                ':EnTrOAla' => $row['AlaId'],
                ':EnTrOQuarto' => $row['QuartId'],
                ':EnTrOLeito' => $row['LeitoId'],
                ':EnTrOProfissional' => $userId,
                ':EnTrOPas' => $_POST['inputSistolica'] == "" ? null : $_POST['inputSistolica'],
                ':EnTrOPad' => $_POST['inputDiatolica'] == "" ? null : $_POST['inputDiatolica'],
                ':EnTrOFreqCardiaca' => $_POST['inputCardiaca'] == "" ? null : $_POST['inputCardiaca'],
                ':EnTrOFreqRespiratoria' => $_POST['inputRespiratoria'] == "" ? null : $_POST['inputRespiratoria'],
                ':EnTrOTemperatura' => $_POST['inputTemperatura'] == "" ? null : $_POST['inputTemperatura'],
                ':EnTrOSPO' => $_POST['inputSPO'] == "" ? null : $_POST['inputSPO'],
                ':EnTrOHGT' => $_POST['inputHGT'] == "" ? null : $_POST['inputHGT'],
                ':EnTrOPeso' => $_POST['inputPeso'] == "" ? null : $_POST['inputPeso'],
                ':EnTrOHoraEntrada' => isset($_POST['entradaHora'])?$_POST['entradaHora']:null,
                ':EnTrOHoraSaida' => isset($_POST['saidaHora'])?$_POST['saidaHora']:null,
                ':EnTrOSala' => isset($_POST['sala'])?$_POST['sala']:null,
                ':EnTrOProfiCirculante' => isset($_POST['profissional'])?$_POST['profissional']:null,
                ':EnTrOInicioAnestesia' => isset($_POST['inicioAnestesia'])?$_POST['inicioAnestesia']:null,
                ':EnTrOTerminoAnestesia' => isset($_POST['terminoAnestesia'])?$_POST['terminoAnestesia']:null,
                ':EnTrOTipoAnestesia' => isset($_POST['tipoAnestesia'])?$_POST['tipoAnestesia']:null,
                ':EnTrOProfiAnestesista' => isset($_POST['profissionalAnestesista'])?$_POST['profissionalAnestesista']:null,
                ':EnTrOProfiInstrumentador' => isset($_POST['profissionalInstrumentador'])?$_POST['profissionalInstrumentador']:null,
                ':EnTrOInicioCirurgia' => isset($_POST['inicioCirurgia'])?$_POST['inicioCirurgia']:null,
                ':EnTrOTerminoCirurgia' => isset($_POST['terminoCirurgia'])?$_POST['terminoCirurgia']:null,
                ':EnTrOProfiCirurgiao' => isset($_POST['profissionalCirurgiao'])?$_POST['profissionalCirurgiao']:null,
                ':EnTrOProfiCirurgiaoAssistente' => isset($_POST['profissionalAssistente'])?$_POST['profissionalAssistente']:null,
                ':EnTrODescPosicaoOperatoria' => isset($_POST['descricaoPosicao'])?$_POST['descricaoPosicao']:null,
                ':EnTrOEncaminhamentoPosCirurgia' => isset($_POST['encaminhamento'])?$_POST['encaminhamento']:null,
                ':EnTrOProfiEnfermeiro' => isset($_POST['profissionalEnfermeiro'])?$_POST['profissionalEnfermeiro']:null,
                ':EnTrOProfiTecnico' => isset($_POST['profissionalTecnico'])?$_POST['profissionalTecnico']:null,
                ':EnTrOProfiEnfermeiroCCO' => isset($_POST['profissionalEnfermeiroCCO'])?$_POST['profissionalEnfermeiroCCO']:null,
                ':EnTrOProfiTecnicoCCO' => isset($_POST['profissionalTecnicoCCO'])?$_POST['profissionalTecnicoCCO']:null,
                ':EnTrOProfiTecnicoRPA' => isset($_POST['profissionalTecnicoRPA'])?$_POST['profissionalTecnicoRPA']:null,
                ':EnTrOKitCME' => isset($_POST['kitCME'])?$_POST['kitCME']:null,
                ':EnTrOMedicacaoAdministrada' => isset($_POST['textMedicacao'])?$_POST['textMedicacao']:null,
                ':EnTrOObservacao' => isset($_POST['textObservacao'])?$_POST['textObservacao']:null,

                // servicosAdicionais[]
                ':EnTrOServAdicionalBancoSangue' => isset($_POST['servicosAdicionais'])?(in_array('BS',$_POST['servicosAdicionais'])?1:0):null,
                ':EnTrOServAdicionalRadiologia' => isset($_POST['servicosAdicionais'])?(in_array('RA',$_POST['servicosAdicionais'])?1:0):null,
                ':EnTrOServAdicionalLaboratorio' => isset($_POST['servicosAdicionais'])?(in_array('LA',$_POST['servicosAdicionais'])?1:0):null,
                ':EnTrOServAdicionalAnatPatologica' => isset($_POST['servicosAdicionais'])?(in_array('AN',$_POST['servicosAdicionais'])?1:0):null,
                ':EnTrOServAdicionalUsoContraste' => isset($_POST['servicosAdicionais'])?(in_array('UC',$_POST['servicosAdicionais'])?1:0):null,
                ':EnTrOServAdicionalOutros' => isset($_POST['servicosAdicionais'])?(in_array('OU',$_POST['servicosAdicionais'])?1:0):null,

                // usoPosCirurgia[]
                ':EnTrOEmUsoCateterEpidural' => isset($_POST['usoPosCirurgia'])?(in_array('CE',$_POST['usoPosCirurgia'])?1:0):null,
                ':EnTrOEmUsoDrenoTubular' => isset($_POST['usoPosCirurgia'])?(in_array('DT',$_POST['usoPosCirurgia'])?1:0):null,
                ':EnTrOEmUsoEntubacaoTraqueal' => isset($_POST['usoPosCirurgia'])?(in_array('ET',$_POST['usoPosCirurgia'])?1:0):null,
                ':EnTrOEmUsoIntracath' => isset($_POST['usoPosCirurgia'])?(in_array('IN',$_POST['usoPosCirurgia'])?1:0):null,
                ':EnTrOEmUsoKehr' => isset($_POST['usoPosCirurgia'])?(in_array('KE',$_POST['usoPosCirurgia'])?1:0):null,
                ':EnTrOEmUsoPecaCirurgica' => isset($_POST['usoPosCirurgia'])?(in_array('PC',$_POST['usoPosCirurgia'])?1:0):null,
                ':EnTrOEmUsoPenrose' => isset($_POST['usoPosCirurgia'])?(in_array('PE',$_POST['usoPosCirurgia'])?1:0):null,
                ':EnTrOEmUsoProntuario' => isset($_POST['usoPosCirurgia'])?(in_array('PR',$_POST['usoPosCirurgia'])?1:0):null,
                ':EnTrOEmUsoPuncaoPeriferica' => isset($_POST['usoPosCirurgia'])?(in_array('PP',$_POST['usoPosCirurgia'])?1:0):null,
                ':EnTrOEmUsoRadiografia' => isset($_POST['usoPosCirurgia'])?(in_array('RA',$_POST['usoPosCirurgia'])?1:0):null,
                ':EnTrOEmUsoSistemaSuccao' => isset($_POST['usoPosCirurgia'])?(in_array('SS',$_POST['usoPosCirurgia'])?1:0):null,
                ':EnTrOEmUsoSondaGastrica' => isset($_POST['usoPosCirurgia'])?(in_array('SG',$_POST['usoPosCirurgia'])?1:0):null,
                ':EnTrOEmUsoSondaVesical' => isset($_POST['usoPosCirurgia'])?(in_array('SV',$_POST['usoPosCirurgia'])?1:0):null,

                ':iAtendimentoAnotacao' => $iAtendimentoAnotacao 
                ));

                $_SESSION['iAtendimentoId'] = $iAtendimentoId;
                $_SESSION['msg']['titulo'] = "Sucesso";
                $_SESSION['msg']['mensagem'] = "Anotação alterada com sucesso!!!";
                $_SESSION['msg']['tipo'] = "success";

        } else {

            $sql = "INSERT INTO EnfermagemTransOperatoria(EnTrOAtendimento,EnTrODataInicio,EnTrOHoraInicio,
                EnTrOPrevisaoAlta,EnTrOTipoInternacao,EnTrOEspecialidadeLeito,EnTrOAla,EnTrOQuarto,EnTrOLeito,
                EnTrOProfissional,EnTrOPas,EnTrOPad,EnTrOFreqCardiaca,EnTrOFreqRespiratoria,EnTrOTemperatura,
                EnTrOSPO,EnTrOHGT,EnTrOPeso,EnTrOHoraEntrada,EnTrOHoraSaida,EnTrOSala,EnTrOProfiCirculante,
                EnTrOInicioAnestesia,EnTrOTerminoAnestesia,EnTrOTipoAnestesia,EnTrOProfiAnestesista,
                EnTrOProfiInstrumentador,EnTrOInicioCirurgia,EnTrOTerminoCirurgia,EnTrOProfiCirurgiao,
                EnTrOProfiCirurgiaoAssistente,EnTrODescPosicaoOperatoria,EnTrOServAdicionalBancoSangue,
                EnTrOServAdicionalRadiologia,EnTrOServAdicionalLaboratorio,EnTrOServAdicionalAnatPatologica,
                EnTrOServAdicionalUsoContraste,EnTrOServAdicionalOutros,EnTrOEncaminhamentoPosCirurgia,
                EnTrOEmUsoCateterEpidural,EnTrOEmUsoDrenoTubular,EnTrOEmUsoEntubacaoTraqueal,EnTrOEmUsoIntracath,
                EnTrOEmUsoKehr,EnTrOEmUsoPecaCirurgica,EnTrOEmUsoPenrose,EnTrOEmUsoProntuario,
                EnTrOEmUsoPuncaoPeriferica,EnTrOEmUsoRadiografia,EnTrOEmUsoSistemaSuccao,EnTrOEmUsoSondaGastrica,
                EnTrOEmUsoSondaVesical,EnTrOProfiEnfermeiro,EnTrOProfiTecnico,EnTrOProfiEnfermeiroCCO,
                EnTrOProfiTecnicoCCO,EnTrOProfiTecnicoRPA,EnTrOKitCME,EnTrOMedicacaoAdministrada,EnTrOObservacao)
            VALUES(
                :EnTrOAtendimento,
                :EnTrODataInicio,
                :EnTrOHoraInicio,
                :EnTrOPrevisaoAlta,
                :EnTrOTipoInternacao,
                :EnTrOEspecialidadeLeito,
                :EnTrOAla,
                :EnTrOQuarto,
                :EnTrOLeito,
                :EnTrOProfissional,
                :EnTrOPas,
                :EnTrOPad,
                :EnTrOFreqCardiaca,
                :EnTrOFreqRespiratoria,
                :EnTrOTemperatura,
                :EnTrOSPO,
                :EnTrOHGT,
                :EnTrOPeso,
                :EnTrOHoraEntrada,
                :EnTrOHoraSaida,
                :EnTrOSala,
                :EnTrOProfiCirculante,
                :EnTrOInicioAnestesia,
                :EnTrOTerminoAnestesia,
                :EnTrOTipoAnestesia,
                :EnTrOProfiAnestesista,
                :EnTrOProfiInstrumentador,
                :EnTrOInicioCirurgia,
                :EnTrOTerminoCirurgia,
                :EnTrOProfiCirurgiao,
                :EnTrOProfiCirurgiaoAssistente,
                :EnTrODescPosicaoOperatoria,
                :EnTrOServAdicionalBancoSangue,
                :EnTrOServAdicionalRadiologia,
                :EnTrOServAdicionalLaboratorio,
                :EnTrOServAdicionalAnatPatologica,
                :EnTrOServAdicionalUsoContraste,
                :EnTrOServAdicionalOutros,
                :EnTrOEncaminhamentoPosCirurgia,
                :EnTrOEmUsoCateterEpidural,
                :EnTrOEmUsoDrenoTubular,
                :EnTrOEmUsoEntubacaoTraqueal,
                :EnTrOEmUsoIntracath,
                :EnTrOEmUsoKehr,
                :EnTrOEmUsoPecaCirurgica,
                :EnTrOEmUsoPenrose,
                :EnTrOEmUsoProntuario,
                :EnTrOEmUsoPuncaoPeriferica,
                :EnTrOEmUsoRadiografia,
                :EnTrOEmUsoSistemaSuccao,
                :EnTrOEmUsoSondaGastrica,
                :EnTrOEmUsoSondaVesical,
                :EnTrOProfiEnfermeiro,
                :EnTrOProfiTecnico,
                :EnTrOProfiEnfermeiroCCO,
                :EnTrOProfiTecnicoCCO,
                :EnTrOProfiTecnicoRPA,
                :EnTrOKitCME,
                :EnTrOMedicacaoAdministrada,
                :EnTrOObservacao)";
            $result = $conn->prepare($sql);

            $result->execute(array(
                ':EnTrOAtendimento' => $iAtendimentoId,
                ':EnTrODataInicio' => date('Y-m-d'),
                ':EnTrOHoraInicio' => date('H:i'),
                ':EnTrOPrevisaoAlta' => '',
                ':EnTrOTipoInternacao' => $row['TpIntId'],
                ':EnTrOEspecialidadeLeito' => $row['EsLeiId'],
                ':EnTrOAla' => $row['AlaId'],
                ':EnTrOQuarto' => $row['QuartId'],
                ':EnTrOLeito' => $row['LeitoId'],
                ':EnTrOProfissional' => $userId,
                ':EnTrOPas' => $_POST['inputSistolica'] == "" ? null : $_POST['inputSistolica'],
                ':EnTrOPad' => $_POST['inputDiatolica'] == "" ? null : $_POST['inputDiatolica'],
                ':EnTrOFreqCardiaca' => $_POST['inputCardiaca'] == "" ? null : $_POST['inputCardiaca'],
                ':EnTrOFreqRespiratoria' => $_POST['inputRespiratoria'] == "" ? null : $_POST['inputRespiratoria'],
                ':EnTrOTemperatura' => $_POST['inputTemperatura'] == "" ? null : $_POST['inputTemperatura'],
                ':EnTrOSPO' => $_POST['inputSPO'] == "" ? null : $_POST['inputSPO'],
                ':EnTrOHGT' => $_POST['inputHGT'] == "" ? null : $_POST['inputHGT'],
                ':EnTrOPeso' => $_POST['inputPeso'] == "" ? null : $_POST['inputPeso'],
                ':EnTrOHoraEntrada' => isset($_POST['entradaHora'])?$_POST['entradaHora']:null,
                ':EnTrOHoraSaida' => isset($_POST['saidaHora'])?$_POST['saidaHora']:null,
                ':EnTrOSala' => isset($_POST['sala'])?$_POST['sala']:null,
                ':EnTrOProfiCirculante' => isset($_POST['profissional'])?$_POST['profissional']:null,
                ':EnTrOInicioAnestesia' => isset($_POST['inicioAnestesia'])?$_POST['inicioAnestesia']:null,
                ':EnTrOTerminoAnestesia' => isset($_POST['terminoAnestesia'])?$_POST['terminoAnestesia']:null,
                ':EnTrOTipoAnestesia' => isset($_POST['tipoAnestesia'])?$_POST['tipoAnestesia']:null,
                ':EnTrOProfiAnestesista' => isset($_POST['profissionalAnestesista'])?$_POST['profissionalAnestesista']:null,
                ':EnTrOProfiInstrumentador' => isset($_POST['profissionalInstrumentador'])?$_POST['profissionalInstrumentador']:null,
                ':EnTrOInicioCirurgia' => isset($_POST['inicioCirurgia'])?$_POST['inicioCirurgia']:null,
                ':EnTrOTerminoCirurgia' => isset($_POST['terminoCirurgia'])?$_POST['terminoCirurgia']:null,
                ':EnTrOProfiCirurgiao' => isset($_POST['profissionalCirurgiao'])?$_POST['profissionalCirurgiao']:null,
                ':EnTrOProfiCirurgiaoAssistente' => isset($_POST['profissionalAssistente'])?$_POST['profissionalAssistente']:null,
                ':EnTrODescPosicaoOperatoria' => isset($_POST['descricaoPosicao'])?$_POST['descricaoPosicao']:null,
                ':EnTrOEncaminhamentoPosCirurgia' => isset($_POST['encaminhamento'])?$_POST['encaminhamento']:null,
                ':EnTrOProfiEnfermeiro' => isset($_POST['profissionalEnfermeiro'])?$_POST['profissionalEnfermeiro']:null,
                ':EnTrOProfiTecnico' => isset($_POST['profissionalTecnico'])?$_POST['profissionalTecnico']:null,
                ':EnTrOProfiEnfermeiroCCO' => isset($_POST['profissionalEnfermeiroCCO'])?$_POST['profissionalEnfermeiroCCO']:null,
                ':EnTrOProfiTecnicoCCO' => isset($_POST['profissionalTecnicoCCO'])?$_POST['profissionalTecnicoCCO']:null,
                ':EnTrOProfiTecnicoRPA' => isset($_POST['profissionalTecnicoRPA'])?$_POST['profissionalTecnicoRPA']:null,
                ':EnTrOKitCME' => isset($_POST['kitCME'])?$_POST['kitCME']:null,
                ':EnTrOMedicacaoAdministrada' => isset($_POST['textMedicacao'])?$_POST['textMedicacao']:null,
                ':EnTrOObservacao' => isset($_POST['textObservacao'])?$_POST['textObservacao']:null,

                // servicosAdicionais[]
                ':EnTrOServAdicionalBancoSangue' => isset($_POST['servicosAdicionais'])?(in_array('BS',$_POST['servicosAdicionais'])?1:0):null,
                ':EnTrOServAdicionalRadiologia' => isset($_POST['servicosAdicionais'])?(in_array('RA',$_POST['servicosAdicionais'])?1:0):null,
                ':EnTrOServAdicionalLaboratorio' => isset($_POST['servicosAdicionais'])?(in_array('LA',$_POST['servicosAdicionais'])?1:0):null,
                ':EnTrOServAdicionalAnatPatologica' => isset($_POST['servicosAdicionais'])?(in_array('AN',$_POST['servicosAdicionais'])?1:0):null,
                ':EnTrOServAdicionalUsoContraste' => isset($_POST['servicosAdicionais'])?(in_array('UC',$_POST['servicosAdicionais'])?1:0):null,
                ':EnTrOServAdicionalOutros' => isset($_POST['servicosAdicionais'])?(in_array('OU',$_POST['servicosAdicionais'])?1:0):null,

                // usoPosCirurgia[]
                ':EnTrOEmUsoCateterEpidural' => isset($_POST['usoPosCirurgia'])?(in_array('CE',$_POST['usoPosCirurgia'])?1:0):null,
                ':EnTrOEmUsoDrenoTubular' => isset($_POST['usoPosCirurgia'])?(in_array('DT',$_POST['usoPosCirurgia'])?1:0):null,
                ':EnTrOEmUsoEntubacaoTraqueal' => isset($_POST['usoPosCirurgia'])?(in_array('ET',$_POST['usoPosCirurgia'])?1:0):null,
                ':EnTrOEmUsoIntracath' => isset($_POST['usoPosCirurgia'])?(in_array('IN',$_POST['usoPosCirurgia'])?1:0):null,
                ':EnTrOEmUsoKehr' => isset($_POST['usoPosCirurgia'])?(in_array('KE',$_POST['usoPosCirurgia'])?1:0):null,
                ':EnTrOEmUsoPecaCirurgica' => isset($_POST['usoPosCirurgia'])?(in_array('PC',$_POST['usoPosCirurgia'])?1:0):null,
                ':EnTrOEmUsoPenrose' => isset($_POST['usoPosCirurgia'])?(in_array('PE',$_POST['usoPosCirurgia'])?1:0):null,
                ':EnTrOEmUsoProntuario' => isset($_POST['usoPosCirurgia'])?(in_array('PR',$_POST['usoPosCirurgia'])?1:0):null,
                ':EnTrOEmUsoPuncaoPeriferica' => isset($_POST['usoPosCirurgia'])?(in_array('PP',$_POST['usoPosCirurgia'])?1:0):null,
                ':EnTrOEmUsoRadiografia' => isset($_POST['usoPosCirurgia'])?(in_array('RA',$_POST['usoPosCirurgia'])?1:0):null,
                ':EnTrOEmUsoSistemaSuccao' => isset($_POST['usoPosCirurgia'])?(in_array('SS',$_POST['usoPosCirurgia'])?1:0):null,
                ':EnTrOEmUsoSondaGastrica' => isset($_POST['usoPosCirurgia'])?(in_array('SG',$_POST['usoPosCirurgia'])?1:0):null,
                ':EnTrOEmUsoSondaVesical' => isset($_POST['usoPosCirurgia'])?(in_array('SV',$_POST['usoPosCirurgia'])?1:0):null
            ));
            
            $_SESSION['iAtendimentoId'] = $iAtendimentoId;
            $_SESSION['msg']['titulo'] = "Sucesso";
            $_SESSION['msg']['mensagem'] = "Anotação inserida com sucesso!!!";
            $_SESSION['msg']['tipo'] = "success";	

        }

        irpara("anotacaoTransOperatorio.php");
        
        
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

		$(document).ready(function() {
            // a função "cantaCaracteres" está no arquivo "custom.js"
            // "function cantaCaracteres(htmlTextId, numMaxCaracteres, htmlIdMostraRestantes)"

            $('#textMedicacao').on('input', function(e){
                cantaCaracteres('textMedicacao',150,'caracteresInputMedicacao')
            })
            $('#textObservacao').on('input', function(e){
                cantaCaracteres('textObservacao',800,'caracteresInputObservacao')
            })
		}); //document.ready

        function exclui(element){
            $.ajax({
                type: 'POST',
                url: 'filtraAdmissaoCirurgicaPreOperatorio.php',
                dataType: 'json',
                data:{
                    'tipoRequest': 'EXCLUIR',
                    'id': $(element).data('id'),
                    'tipo': $(element).data('tipo')
                },
                success: function(response) {
                    cheackList()
                }
            });
        }
        function cheackList(){
            $.ajax({
                type: 'POST',
                url: 'filtraAdmissaoCirurgicaPreOperatorio.php',
                dataType: 'json',
                data:{
                    'tipoRequest': 'CHECKLIST'
                },
                success: function(response) {
                    // Acesso venoso listagem
                        $('#tblAcessoVenoso').DataTable().clear().draw()
                        let tableAcesso = $('#tblAcessoVenoso').DataTable()
                        let rowNodeAcesso
                        let rowsAcesso = [];
                        if(response.acesso.length){
                            $('#tblAcessoVenosoViwer').removeClass('d-none')
                            response.acesso.forEach((item, index) => {
                                rowsAcesso.push([
                                    index+1,
                                    item.dataHora,
                                    item.lado == 'DI'?'Direito':'Esquerdo',
                                    item.calibre,
                                    item.responsavel,
                                    item.acoes
                                ])
                            })
                            rowsAcesso.forEach((item, index) => {
                                rowNodeAcesso = tableAcesso.row.add(item).draw().node()
                                // $(rowNodeAcesso).attr('class', 'text-left')
                                // $(rowNodeAcesso).find('td:eq(3)').attr('title', `Prontuário: ${item.identify.prontuario}`)
                            })
                        }else{
                            $('#tblAcessoVenosoViwer').addClass('d-none')
                        }
                    //
                    // Concentimento listagem
                        $('#tblConcentimento').DataTable().clear().draw()
                        let tableConcentimento = $('#tblConcentimento').DataTable()
                        let rowNodeConcentimento
                        let rowsConcentimento = [];

                        if(response.concentimento.length){
                            $('#tblConcentimentoViwer').removeClass('d-none')
                            response.concentimento.forEach((item, index) => {
                                rowsConcentimento.push([
                                    index+1,
                                    item.dataHora,
                                    item.descricao,
                                    item.acoes
                                ])
                            })
                            rowsConcentimento.forEach((item, index) => {
                                rowNodeConcentimento = tableConcentimento.row.add(item).draw().node()
                                // $(rowNodeConcentimento).attr('class', 'text-left')
                                // $(rowNodeConcentimento).find('td:eq(3)').attr('title', `Prontuário: ${item.identify.prontuario}`)
                            })
                        }else{
                            $('#tblConcentimentoViwer').addClass('d-none')
                        }
                    //
                    // Exames listagem
                        $('#tblExame').DataTable().clear().draw()
                        let tableExame = $('#tblExame').DataTable()
                        let rowNodeExame
                        let rowsExame = [];

                        if(response.exames.length){
                            $('#tblExameViwer').removeClass('d-none')
                            response.exames.forEach((item, index) => {
                                rowsExame.push([
                                    index+1,
                                    item.dataHora,
                                    item.descricao,
                                    item.acoes
                                ])
                            })
                            rowsExame.forEach((item, index) => {
                                rowNodeExame = tableExame.row.add(item).draw().node()
                                // $(rowNodeConcentimento).attr('class', 'text-left')
                                // $(rowNodeConcentimento).find('td:eq(3)').attr('title', `Prontuário: ${item.identify.prontuario}`)
                            })
                        }else{
                            $('#tblExameViwer').addClass('d-none')
                        }
                    //
                }
            });
        }

        // $.ajax({
        //     type: 'POST',
        //     url: 'filtraAdmissaoCirurgicaPreOperatorio.php',
        //     dataType: 'json',
        //     data:{
        //         'tipoRequest': 'ACESSOVENOSO',
        //     },
        //     success: function(response) {}
        // });

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
						<form name="formAtendimentoAdmissaoPediatrica" id="formAtendimentoAdmissaoPediatrica" method="post">
							<?php
								echo "<input type='hidden' id='iAtendimentoId' name='iAtendimentoId' value='$iAtendimentoId' />";
							?>
							<div class="card">

                            <div class="col-md-12">
                                <div class="row">

                                    <div class="col-md-6" style="text-align: left;">

                                        <div class="card-header header-elements-inline">
                                            <h3 class="card-title font-weight-bold">REGISTRO TRANSOPERATÓRIO</h3>
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
                                        <h3 class="card-title font-weight-bold">Entrada Sala Operatória</h3>

                                        <div class="header-elements">
                                            <div class="list-icons">
                                                <a class="list-icons-item" data-action="collapse"></a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card-body">
                                      
                                        <!-- linha 1 -->
                                        <div class="col-lg-12 mb-4 row">
                                            <!-- titulos -->
                                            <div class="col-lg-3">
                                                <label>Entrada Hora</label>
                                            </div>
                                            <div class="col-lg-3">
                                                <label>Sala</label>
                                            </div>
                                            <div class="col-lg-3">
                                                <label>Saída Hora</label>
                                            </div>
                                            <div class="col-lg-3">
                                                <label>Profissional Circulante</label>
                                            </div>
                                            
                                            <!-- campos -->                                            
                                            <div class="col-lg-3">
                                                <input id="entradaHora" class="form-control" type="time" name="entradaHora" value="<?php echo isset($iAtendimentoAnotacao) ? explode(".", $rowAnotacao['EnTrOHoraEntrada'])[0] : ''; ?>" >
                                            </div>
                                            <div class="col-lg-3">
                                                <input id="sala" class="form-control" type="text" name="sala" value="<?php echo isset($iAtendimentoAnotacao) ? $rowAnotacao['EnTrOSala'] : ''; ?>" >
                                            </div>
                                            <div class="col-lg-3">
                                                <input id="saidaHora" class="form-control" type="time" name="saidaHora"value="<?php echo isset($iAtendimentoAnotacao) ? explode(".", $rowAnotacao['EnTrOHoraSaida'])[0] : ''; ?>" >
                                            </div>
                                            <div class="col-lg-3">
                                                <select id="profissional" name="profissional" class="select-search" >
                                                    <option value=''>selecione</option>
                                                    <?php
                                                        foreach($rowProfissionais as $item){
                                                            if (isset($iAtendimentoAnotacao) && $rowAnotacao['EnTrOProfiCirculante'] == $item['ProfiId'] ) {
                                                                echo "<option value='$item[ProfiId]' selected>$item[ProfiNome] - $item[profissao] - $item[ProfiCbo]</option>";
                                                            } else {
                                                                echo "<option value='$item[ProfiId]'>$item[ProfiNome] - $item[profissao] - $item[ProfiCbo]</option>";
                                                            }                                                          
                                                        }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- linha 2 -->
                                        <div class="col-lg-12 mb-4 row">
                                            <!-- titulos -->
                                            <div class="col-lg-2">
                                                <label>Início da anestesia</label>
                                            </div>
                                            <div class="col-lg-2">
                                                <label>Término da anestesia</label>
                                            </div>
                                            <div class="col-lg-2">
                                                <label>Tipo de anestesia</label>
                                            </div>
                                            <div class="col-lg-3">
                                                <label>Profissional Anestesista</label>
                                            </div>
                                            <div class="col-lg-3">
                                                <label>Profissional Instrumentador</label>
                                            </div>
                                            
                                            <!-- campos -->

                                            <div class="col-lg-2">
                                                <input id="inicioAnestesia" class="form-control" type="time" name="inicioAnestesia" value="<?php echo isset($iAtendimentoAnotacao) ? explode(".", $rowAnotacao['EnTrOInicioAnestesia'])[0] : ''; ?>">
                                            </div>
                                            <div class="col-lg-2">
                                                <input id="terminoAnestesia" class="form-control" type="time" name="terminoAnestesia" value="<?php echo isset($iAtendimentoAnotacao) ? explode(".", $rowAnotacao['EnTrOTerminoAnestesia'])[0] : ''; ?>">
                                            </div>
                                            <div class="col-lg-2">
                                                <select id="tipoAnestesia" name="tipoAnestesia" class="select-search">
                                                    <option value=''>selecione</option>
                                                    <option value='LO' <?php if (isset($iAtendimentoAnotacao )) echo $rowAnotacao['EnTrOTipoAnestesia'] == 'LO' ? 'selected' : ''; ?> >LOCAL</option>
                                                    <option value='PL' <?php if (isset($iAtendimentoAnotacao )) echo $rowAnotacao['EnTrOTipoAnestesia'] == 'PL' ? 'selected' : ''; ?> >PLEXULAR</option>
                                                    <option value='GV' <?php if (isset($iAtendimentoAnotacao )) echo $rowAnotacao['EnTrOTipoAnestesia'] == 'GV' ? 'selected' : ''; ?> >GERAL (VM)</option>
                                                    <option value='GS' <?php if (isset($iAtendimentoAnotacao )) echo $rowAnotacao['EnTrOTipoAnestesia'] == 'GS' ? 'selected' : ''; ?> >GERAL (SEDAÇÃO)</option>
                                                    <option value='BE' <?php if (isset($iAtendimentoAnotacao )) echo $rowAnotacao['EnTrOTipoAnestesia'] == 'BE' ? 'selected' : ''; ?> >BLOQUEIOS ESPINHAIS</option>
                                                </select>
                                            </div>
                                            <div class="col-lg-3">
                                                <select id="profissionalAnestesista" name="profissionalAnestesista" class="select-search">
                                                    <option value=''>selecione</option>
                                                    <?php
                                                        foreach($rowProfissionais as $item){
                                                            if (isset($iAtendimentoAnotacao) && $rowAnotacao['EnTrOProfiAnestesista'] == $item['ProfiId'] ) {
                                                                echo "<option value='$item[ProfiId]' selected>$item[ProfiNome] - $item[profissao] - $item[ProfiCbo]</option>";
                                                            } else {
                                                                echo "<option value='$item[ProfiId]'>$item[ProfiNome] - $item[profissao] - $item[ProfiCbo]</option>";
                                                            }               
                                                        }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="col-lg-3">
                                                <select id="profissionalInstrumentador" name="profissionalInstrumentador" class="select-search">
                                                    <option value=''>selecione</option>
                                                    <?php
                                                        foreach($rowProfissionais as $item){
                                                            if (isset($iAtendimentoAnotacao) && $rowAnotacao['EnTrOProfiInstrumentador'] == $item['ProfiId'] ) {
                                                                echo "<option value='$item[ProfiId]' selected>$item[ProfiNome] - $item[profissao] - $item[ProfiCbo]</option>";
                                                            } else {
                                                                echo "<option value='$item[ProfiId]'>$item[ProfiNome] - $item[profissao] - $item[ProfiCbo]</option>";
                                                            }
                                                        }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- linha 3 -->
                                        <div class="col-lg-12 mb-4 row">
                                            <!-- titulos -->
                                            <div class="col-lg-2">
                                                <label>Início da cirurgia</label>
                                            </div>
                                            <div class="col-lg-2">
                                                <label>Término da cirurgia</label>
                                            </div>
                                            <div class="col-lg-4">
                                                <label>Profissional Cirurgião</label>
                                            </div>
                                            <div class="col-lg-4">
                                                <label>Cirurgião Assistente</label>
                                            </div>
                                            
                                            <!-- campos -->

                                            <div class="col-lg-2">
                                                <input id="inicioCirurgia" class="form-control" type="time" name="inicioCirurgia" value="<?php echo isset($iAtendimentoAnotacao) ? explode(".", $rowAnotacao['EnTrOInicioCirurgia'])[0] : ''; ?>" >
                                            </div>
                                            <div class="col-lg-2">
                                                <input id="terminoCirurgia" class="form-control" type="time" name="terminoCirurgia" value="<?php echo isset($iAtendimentoAnotacao) ? explode(".", $rowAnotacao['EnTrOTerminoCirurgia'])[0] : ''; ?>" >
                                            </div>
                                            <div class="col-lg-4">
                                                <select id="profissionalCirurgiao" name="profissionalCirurgiao" class="select-search">
                                                    <option value=''>selecione</option>
                                                    <?php
                                                        foreach($rowProfissionais as $item){
                                                            if (isset($iAtendimentoAnotacao) && $rowAnotacao['EnTrOProfiCirurgiao'] == $item['ProfiId'] ) {
                                                                echo "<option value='$item[ProfiId]' selected>$item[ProfiNome] - $item[profissao] - $item[ProfiCbo]</option>";
                                                            } else {
                                                                echo "<option value='$item[ProfiId]'>$item[ProfiNome] - $item[profissao] - $item[ProfiCbo]</option>";
                                                            }
                                                        }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="col-lg-4">
                                                <select id="profissionalAssistente" name="profissionalAssistente" class="select-search">
                                                    <option value=''>selecione</option>
                                                    <?php
                                                        foreach($rowProfissionais as $item){
                                                            if (isset($iAtendimentoAnotacao) && $rowAnotacao['EnTrOProfiCirurgiaoAssistente'] == $item['ProfiId'] ) {
                                                                echo "<option value='$item[ProfiId]' selected>$item[ProfiNome] - $item[profissao] - $item[ProfiCbo]</option>";
                                                            } else {
                                                                echo "<option value='$item[ProfiId]'>$item[ProfiNome] - $item[profissao] - $item[ProfiCbo]</option>";
                                                            }
                                                        }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- linha 4 -->
                                        <div class="col-lg-12 mb-4 row">
                                            <!-- titulos -->
                                            <div class="col-lg-3">
                                                <label>Descrição da posição operatória</label>
                                            </div>
                                            <div class="col-lg-3">
                                                <label>Serviços adicionais</label>
                                            </div>
                                            <div class="col-lg-3">
                                                <label>Encaminhamento pós cirurgia </label>
                                            </div>
                                            <div class="col-lg-3">
                                                <label>Em uso pós cirurgia</label>
                                            </div>
                                            
                                            <!-- campos -->

                                            <div class="col-lg-3">
                                                <select id="descricaoPosicao" name="descricaoPosicao" class="select-search">
                                                    <option value=''>selecione</option>
                                                    <option value='VE' <?php if (isset($iAtendimentoAnotacao )) echo $rowAnotacao['EnTrODescPosicaoOperatoria'] == 'VE' ? 'selected' : ''; ?> >VENTRAL</option>
                                                    <option value='DO' <?php if (isset($iAtendimentoAnotacao )) echo $rowAnotacao['EnTrODescPosicaoOperatoria'] == 'DO' ? 'selected' : ''; ?> >DORSAL</option>
                                                    <option value='LA' <?php if (isset($iAtendimentoAnotacao )) echo $rowAnotacao['EnTrODescPosicaoOperatoria'] == 'LA' ? 'selected' : ''; ?> >LATERAL</option>
                                                    <option value='GI' <?php if (isset($iAtendimentoAnotacao )) echo $rowAnotacao['EnTrODescPosicaoOperatoria'] == 'GI' ? 'selected' : ''; ?> >GINECOLÓGICA</option>
                                                    <option value='SG' <?php if (isset($iAtendimentoAnotacao )) echo $rowAnotacao['EnTrODescPosicaoOperatoria'] == 'SG' ? 'selected' : ''; ?> >SEMI-GINECOLÓGICA</option>
                                                    <option value='TR' <?php if (isset($iAtendimentoAnotacao )) echo $rowAnotacao['EnTrODescPosicaoOperatoria'] == 'TR' ? 'selected' : ''; ?> >TRENDELENBURG</option>
                                                </select>
                                            </div>
                                            <div class="col-lg-3">
                                                <select id="servicosAdicionais" name="servicosAdicionais[]" class="form-control multiselect-filtering" multiple="multiple">
                                                    <option value='BS' <?php if (isset($iAtendimentoAnotacao )) echo $rowAnotacao['EnTrOServAdicionalBancoSangue'] == 1 ? 'selected' : ''; ?> >BANCO DE SANGUE</option>
                                                    <option value='RA' <?php if (isset($iAtendimentoAnotacao )) echo $rowAnotacao['EnTrOServAdicionalRadiologia'] == 1 ? 'selected' : ''; ?> >RADIOLOGIA</option>
                                                    <option value='LA' <?php if (isset($iAtendimentoAnotacao )) echo $rowAnotacao['EnTrOServAdicionalLaboratorio'] == 1 ? 'selected' : ''; ?> >LABORATÓRIO</option>
                                                    <option value='AN' <?php if (isset($iAtendimentoAnotacao )) echo $rowAnotacao['EnTrOServAdicionalAnatPatologica'] == 1 ? 'selected' : ''; ?> >ANATOMIA PATOLÓGICA</option>
                                                    <option value='UC' <?php if (isset($iAtendimentoAnotacao )) echo $rowAnotacao['EnTrOServAdicionalUsoContraste'] == 1 ? 'selected' : ''; ?> >USO DE CONTRASTE</option>
                                                    <option value='OU' <?php if (isset($iAtendimentoAnotacao )) echo $rowAnotacao['EnTrOServAdicionalOutros'] == 1 ? 'selected' : ''; ?> >OUTROS</option>
                                                </select>
                                            </div>
                                            <div class="col-lg-3">
                                                <select id="encaminhamento" name="encaminhamento" class="select-search">
                                                    <option value=''>selecione</option>
                                                    <option value='SO' <?php if (isset($iAtendimentoAnotacao )) echo $rowAnotacao['EnTrODescPosicaoOperatoria'] == 'SO' ? 'selected' : ''; ?> >SALA PÓS-OPERATÓRIA</option>
                                                    <option value='LE' <?php if (isset($iAtendimentoAnotacao )) echo $rowAnotacao['EnTrODescPosicaoOperatoria'] == 'LE' ? 'selected' : ''; ?> >LEITO</option>
                                                    <option value='CT' <?php if (isset($iAtendimentoAnotacao )) echo $rowAnotacao['EnTrODescPosicaoOperatoria'] == 'CT' ? 'selected' : ''; ?> >CTI</option>
                                                    <option value='UT' <?php if (isset($iAtendimentoAnotacao )) echo $rowAnotacao['EnTrODescPosicaoOperatoria'] == 'UT' ? 'selected' : ''; ?> >UTI</option>
                                                    <option value='SU' <?php if (isset($iAtendimentoAnotacao )) echo $rowAnotacao['EnTrODescPosicaoOperatoria'] == 'SU' ? 'selected' : ''; ?> >SEMI UTI</option>
                                                    <option value='OB' <?php if (isset($iAtendimentoAnotacao )) echo $rowAnotacao['EnTrODescPosicaoOperatoria'] == 'OB' ? 'selected' : ''; ?> >ÓBITO</option>
                                                </select>
                                            </div>
                                            <div class="col-lg-3">
                                                <select id="usoPosCirurgia" name="usoPosCirurgia[]" class="form-control multiselect-filtering" multiple="multiple">
                                                    <option value='CE' <?php if (isset($iAtendimentoAnotacao )) echo $rowAnotacao['EnTrOEmUsoCateterEpidural'] == 1 ? 'selected' : ''; ?> >Cateter Epidural</option>
                                                    <option value='DT' <?php if (isset($iAtendimentoAnotacao )) echo $rowAnotacao['EnTrOEmUsoDrenoTubular'] == 1 ? 'selected' : ''; ?> >Drenos Tubulares</option>
                                                    <option value='ET' <?php if (isset($iAtendimentoAnotacao )) echo $rowAnotacao['EnTrOEmUsoEntubacaoTraqueal'] == 1 ? 'selected' : ''; ?> >Entubação Traqueal</option>
                                                    <option value='IN' <?php if (isset($iAtendimentoAnotacao )) echo $rowAnotacao['EnTrOEmUsoIntracath'] == 1 ? 'selected' : ''; ?> >Intracath</option>
                                                    <option value='KE' <?php if (isset($iAtendimentoAnotacao )) echo $rowAnotacao['EnTrOEmUsoKehr'] == 1 ? 'selected' : ''; ?> >Kehr</option>
                                                    <option value='PC' <?php if (isset($iAtendimentoAnotacao )) echo $rowAnotacao['EnTrOEmUsoPecaCirurgica'] == 1 ? 'selected' : ''; ?> >Peças Cirurgicas</option>
                                                    <option value='PE' <?php if (isset($iAtendimentoAnotacao )) echo $rowAnotacao['EnTrOEmUsoPenrose'] == 1 ? 'selected' : ''; ?> >Penrose</option>
                                                    <option value='PR' <?php if (isset($iAtendimentoAnotacao )) echo $rowAnotacao['EnTrOEmUsoProntuario'] == 1 ? 'selected' : ''; ?> >Prontuário</option>
                                                    <option value='PP' <?php if (isset($iAtendimentoAnotacao )) echo $rowAnotacao['EnTrOEmUsoPuncaoPeriferica'] == 1 ? 'selected' : ''; ?> >Punção Periférica</option>
                                                    <option value='RA' <?php if (isset($iAtendimentoAnotacao )) echo $rowAnotacao['EnTrOEmUsoRadiografia'] == 1 ? 'selected' : ''; ?> >Radiografias</option>
                                                    <option value='SS' <?php if (isset($iAtendimentoAnotacao )) echo $rowAnotacao['EnTrOEmUsoSistemaSuccao'] == 1 ? 'selected' : ''; ?> >Sistema de Sucção</option>
                                                    <option value='SG' <?php if (isset($iAtendimentoAnotacao )) echo $rowAnotacao['EnTrOEmUsoSondaGastrica'] == 1 ? 'selected' : ''; ?> >Sonda Gastrica</option>
                                                    <option value='SV' <?php if (isset($iAtendimentoAnotacao )) echo $rowAnotacao['EnTrOEmUsoSondaVesical'] == 1 ? 'selected' : ''; ?> >Sonda Vesical</option>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- linha 5 -->
                                        <div class="col-lg-12 mb-4 row">
                                            <!-- titulos -->
                                            <div class="col-lg-3">
                                                <label>Profissional Enfermeiro</label>
                                            </div>
                                            <div class="col-lg-3">
                                                <label>Profissional Técnico</label>
                                            </div>
                                            <div class="col-lg-3">
                                                <label>Profissional Enfermeiro CCO</label>
                                            </div>
                                            <div class="col-lg-3">
                                                <label>Profissional Técnico CCO</label>
                                            </div>
                                            
                                            <!-- campos -->

                                            <div class="col-lg-3">
                                                <select id="profissionalEnfermeiro" name="profissionalEnfermeiro" class="select-search">
                                                    <option value=''>selecione</option>
                                                    <?php
                                                        foreach($rowProfissionais as $item){
                                                            if (isset($iAtendimentoAnotacao) && $rowAnotacao['EnTrOProfiEnfermeiro'] == $item['ProfiId'] ) {
                                                                echo "<option value='$item[ProfiId]' selected>$item[ProfiNome] - $item[profissao] - $item[ProfiCbo]</option>";
                                                            } else {
                                                                echo "<option value='$item[ProfiId]'>$item[ProfiNome] - $item[profissao] - $item[ProfiCbo]</option>";
                                                            }
                                                        }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="col-lg-3">
                                                <select id="profissionalTecnico" name="profissionalTecnico" class="select-search">
                                                    <option value=''>selecione</option>
                                                    <?php
                                                        foreach($rowProfissionais as $item){
                                                            if (isset($iAtendimentoAnotacao) && $rowAnotacao['EnTrOProfiTecnico'] == $item['ProfiId'] ) {
                                                                echo "<option value='$item[ProfiId]' selected>$item[ProfiNome] - $item[profissao] - $item[ProfiCbo]</option>";
                                                            } else {
                                                                echo "<option value='$item[ProfiId]'>$item[ProfiNome] - $item[profissao] - $item[ProfiCbo]</option>";
                                                            }
                                                        }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="col-lg-3">
                                                <select id="profissionalEnfermeiroCCO" name="profissionalEnfermeiroCCO" class="select-search">
                                                    <option value=''>selecione</option>
                                                    <?php
                                                        foreach($rowProfissionais as $item){
                                                            if (isset($iAtendimentoAnotacao) && $rowAnotacao['EnTrOProfiEnfermeiroCCO'] == $item['ProfiId'] ) {
                                                                echo "<option value='$item[ProfiId]' selected>$item[ProfiNome] - $item[profissao] - $item[ProfiCbo]</option>";
                                                            } else {
                                                                echo "<option value='$item[ProfiId]'>$item[ProfiNome] - $item[profissao] - $item[ProfiCbo]</option>";
                                                            }
                                                        }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="col-lg-3">
                                                <select id="profissionalTecnicoCCO" name="profissionalTecnicoCCO" class="select-search">
                                                    <option value=''>selecione</option>
                                                    <?php
                                                        foreach($rowProfissionais as $item){
                                                            if (isset($iAtendimentoAnotacao) && $rowAnotacao['EnTrOProfiTecnicoCCO'] == $item['ProfiId'] ) {
                                                                echo "<option value='$item[ProfiId]' selected>$item[ProfiNome] - $item[profissao] - $item[ProfiCbo]</option>";
                                                            } else {
                                                                echo "<option value='$item[ProfiId]'>$item[ProfiNome] - $item[profissao] - $item[ProfiCbo]</option>";
                                                            }
                                                        }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- linha 6 -->
                                        <div class="col-lg-12 mb-4 row">
                                            <!-- titulos -->
                                            <div class="col-lg-3">
                                                <label>Profissional Técnico RPA</label>
                                            </div>
                                            <div class="col-lg-3">
                                                <label>Kit CME</label>
                                            </div>
                                            <div class="col-lg-6">
                                                <!--  -->
                                            </div>
                                            
                                            <!-- campos -->

                                            <div class="col-lg-3">
                                                <select id="profissionalTecnicoRPA" name="profissionalTecnicoRPA" class="select-search">
                                                    <option value=''>selecione</option>
                                                    <?php
                                                        foreach($rowProfissionais as $item){
                                                            if (isset($iAtendimentoAnotacao) && $rowAnotacao['EnTrOProfiTecnicoRPA'] == $item['ProfiId'] ) {
                                                                echo "<option value='$item[ProfiId]' selected>$item[ProfiNome] - $item[profissao] - $item[ProfiCbo]</option>";
                                                            } else {
                                                                echo "<option value='$item[ProfiId]'>$item[ProfiNome] - $item[profissao] - $item[ProfiCbo]</option>";
                                                            }
                                                        }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="col-lg-3">
                                                <select id="kitCME" name="kitCME" class="select-search">
                                                    <option value=''>selecione</option>
                                                    <?php
                                                        foreach($rowKitCME as $item){
                                                            if (isset($iAtendimentoAnotacao) && $rowAnotacao['EnTrOKitCME'] == $item['KtCmeId']) {
                                                                echo "<option value='$item[KtCmeId]' selected>$item[KtCmeNome]</option>";
                                                            } else {
                                                                echo "<option value='$item[KtCmeId]'>$item[KtCmeNome]</option>";
                                                            }
                                                            
                                                        }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="col-lg-6">
                                                <!--  -->
                                            </div>
                                        </div>

                                        <!-- linha 7 -->
                                        <div class="col-lg-12 mb-4 row">
                                            <!-- titulos -->
                                            <div class="col-lg-12">
                                                <label>Medicação Administrada (digitação livre)</label>
                                            </div>

                                            <!-- campos -->
                                            <div class="col-lg-12">
                                                <textarea id="textMedicacao" name="textMedicacao" class="form-control" rows="4" cols="4" maxLength="150" placeholder="" ><?php echo isset($iAtendimentoAnotacao) ? $rowAnotacao['EnTrOMedicacaoAdministrada'] : ''; ?></textarea>
                                                <small class="text-muted form-text">
                                                    Máx. 150 caracteres<br>
                                                    <span id="caracteresInputMedicacao"></span>
                                                </small>
                                            </div>
                                        </div>

                                        <!-- linha 8 -->
                                        <div class="col-lg-12 mb-3 row">
                                            <!-- titulos -->

                                            <div class="card-header header-elements-inline" style="margin-left: -20px">
                                                <h3 class="card-title font-weight-bold">Observações</h3>
                                            </div>

                                            <!-- campos -->
                                            <div class="col-lg-12">
                                                <textarea id="textObservacao" name="textObservacao" class="form-control" rows="4" cols="4" maxLength="800" placeholder="" ><?php echo isset($iAtendimentoAnotacao) ? $rowAnotacao['EnTrOObservacao'] : ''; ?></textarea>
                                                <small class="text-muted form-text">
                                                    Máx. 800 caracteres<br>
                                                    <span id="caracteresInputObservacao"></span>
                                                </small>
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