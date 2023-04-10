<?php 

    include_once("sessao.php"); 

    $_SESSION['PaginaAtual'] = 'Admissão de Recém Nascido';

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
    FROM EnfermagemAdmissaoRN
    WHERE EnAdRAtendimento = $iAtendimentoId
    ORDER BY EnAdRId DESC";
    $result = $conn->query($sql);
    $rowAdmissao= $result->fetch(PDO::FETCH_ASSOC);

    $iAtendimentoAdmissaoRN = $rowAdmissao?$rowAdmissao['EnAdRId']:null;

    //var_dump($iAtendimentoAdmissaoRN);die;

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

    if(isset($_POST['RN'])){

        try {

            if ($iAtendimentoAdmissaoRN) {

                $sql = "UPDATE EnfermagemAdmissaoRN SET 
                    EnAdRDataInicio = :sEnAdRDataInicio,
                    EnAdRHoraInicio = :sEnAdRHoraInicio,
                    EnAdRPrevisaoAlta = :sEnAdRPrevisaoAlta,
                    EnAdRTipoInternacao = :sEnAdRTipoInternacao,
                    EnAdREspecialidadeLeito = :sEnAdREspecialidadeLeito,
                    EnAdRAla = :sEnAdRAla,
                    EnAdRQuarto = :sEnAdRQuarto,
                    EnAdRLeito = :sEnAdRLeito,
                    EnAdRProfissional = :sEnAdRProfissional,
                    EnAdRNomeMae = :sEnAdRNomeMae,
                    EnAdRDataNascimento = :sEnAdRDataNascimento,
                    EnAdRHoraNascimento = :sEnAdRHoraNascimento,
                    EnAdRSexo = :sEnAdRSexo,
                    EnAdRChoroPresente = :sEnAdRChoroPresente,
                    EnAdRApgar1min = :sEnAdRApgar1min,
                    EnAdRApgar5min = :sEnAdRApgar5min,
                    EnAdRAmamentacao1h = :sEnAdRAmamentacao1h,
                    EnAdRMotivoNaoAleitamento = :sEnAdRMotivoNaoAleitamento,
                    EnAdRFreqCardiaca = :sEnAdRFreqCardiaca,
                    EnAdRFreqRespiratoria = :sEnAdRFreqRespiratoria,
                    EnAdRTemperatura = :sEnAdRTemperatura,
                    EnAdRSPO = :sEnAdRSPO,
                    EnAdRHGT = :sEnAdRHGT,
                    EnAdRPeso = :sEnAdRPeso,
                    EnAdRIdadeGestacional = :sEnAdRIdadeGestacional,
                    EnAdRFatorRH = :sEnAdRFatorRH,
                    EnAdREstatura = :sEnAdREstatura,
                    EnAdRPerimetroCefalico = :sEnAdRPerimetroCefalico,
                    EnAdRPerimetroToraxico = :sEnAdRPerimetroToraxico,
                    EnAdRPerimetroAbdominal = :sEnAdRPerimetroAbdominal,
                    EnAdRAtividadeHipoativo = :sEnAdRAtividadeHipoativo,
                    EnAdRAtividadeSonolento = :sEnAdRAtividadeSonolento,
                    EnAdRAtividadeAtivo = :sEnAdRAtividadeAtivo,
                    EnAdRAtividadeChoroso = :sEnAdRAtividadeChoroso,
                    EnAdRAtividadeGemente = :sEnAdRAtividadeGemente,
                    EnAdRAtividadeDescricao = :sEnAdRAtividadeDescricao,
                    EnAdRColoracaoCorado = :sEnAdRColoracaoCorado,
                    EnAdRColoracaoHipoCorado = :sEnAdRColoracaoHipoCorado,
                    EnAdRColoracaoCianotico = :sEnAdRColoracaoCianotico,
                    EnAdRColoracaoIcterico = :sEnAdRColoracaoIcterico,
                    EnAdRColoracaoPletorico = :sEnAdRColoracaoPletorico,
                    EnAdRColoracaoDescricao = :sEnAdRColoracaoDescricao,
                    EnAdRHidratacao = :sEnAdRHidratacao,
                    EnAdRFontanela = :sEnAdRFontanela,
                    EnAdRPele = :sEnAdRPele,
                    EnAdRPeleDescricao = :sEnAdRPeleDescricao,
                    EnAdRReflexoSuccao = :sEnAdRReflexoSuccao,
                    EnAdRReflexoMoro = :sEnAdRReflexoMoro,
                    EnAdRReflexoPreensaoPalmar = :sEnAdRReflexoPreensaoPalmar,
                    EnAdRReflexoPressaoPlantar = :sEnAdRReflexoPressaoPlantar,
                    EnAdRCabecaEscoriacao = :sEnAdRCabecaEscoriacao,
                    EnAdRCabecaPIG = :sEnAdRCabecaPIG,
                    EnAdRCabecaGIG = :sEnAdRCabecaGIG,
                    EnAdRCabecaBossa = :sEnAdRCabecaBossa,
                    EnAdRCabecaCefalohematoma = :sEnAdRCabecaCefalohematoma,
                    EnAdRCabecaMascaraEquimotica = :sEnAdRCabecaMascaraEquimotica,
                    EnAdRAbdome = :sEnAdRAbdome,
                    EnAdRSuccaoSatisfatoria = :sEnAdRSuccaoSatisfatoria,
                    EnAdRPadraoRespiratorio = :sEnAdRPadraoRespiratorio,
                    EnAdRPadraoRespiratorioDescricao = :sEnAdRPadraoRespiratorioDescricao,
                    EnAdRGenturinarioIntegro = :sEnAdRGenturinarioIntegro,
                    EnAdRGenturinarioDiurese = :sEnAdRGenturinarioDiurese,
                    EnAdRGenturinarioAnusPervio = :sEnAdRGenturinarioAnusPervio,
                    EnAdRGenturinarioMeconio = :sEnAdRGenturinarioMeconio,
                    EnAdRGenturinarioOutro = :sEnAdRGenturinarioOutro,
                    EnAdRGenturinarioDescricao = :sEnAdRGenturinarioDescricao,
                    EnAdRCotoLimpoSeco = :sEnAdRCotoLimpoSeco,
                    EnAdRCotoGelatinoso = :sEnAdRCotoGelatinoso,
                    EnAdRCotoMumificado = :sEnAdRCotoMumificado,
                    EnAdRCotoUmido = :sEnAdRCotoUmido,
                    EnAdRCotoSujo = :sEnAdRCotoSujo,
                    EnAdRCotoFetido = :sEnAdRCotoFetido,
                    EnAdRCotoHiperemia = :sEnAdRCotoHiperemia,
                    EnAdRCotoDescricao = :sEnAdRCotoDescricao,
                    EnAdRCateter = :sEnAdRCateter,
                    EnAdRCateterDescricao = :sEnAdRCateterDescricao,
                    EnAdRSonda = :sEnAdRSonda,
                    EnAdRSondaDescricao = :sEnAdRSondaDescricao,
                    EnAdRDiagnosticoDorAguda = :sEnAdRDiagnosticoDorAguda,
                    EnAdRDiagnosticoDeficitAutoCuidado = :sEnAdRDiagnosticoDeficitAutoCuidado,
                    EnAdRDiagnosticoEliminacaoUrinaria = :sEnAdRDiagnosticoEliminacaoUrinaria,
                    EnAdRDiagnosticoNutricaoDesequilibrada = :sEnAdRDiagnosticoNutricaoDesequilibrada,
                    EnAdRDiagnosticoPadraoRespiratorio = :sEnAdRDiagnosticoPadraoRespiratorio,
                    EnAdRDiagnosticoPadraoSono = :sEnAdRDiagnosticoPadraoSono,
                    EnAdRDiagnosticoRiscoConstipacao = :sEnAdRDiagnosticoRiscoConstipacao,
                    EnAdRDiagnosticoRiscoGlicemia = :sEnAdRDiagnosticoRiscoGlicemia,
                    EnAdRDiagnosticoRiscoIctericia = :sEnAdRDiagnosticoRiscoIctericia,
                    EnAdRDiagnosticoRiscoInfeccao = :sEnAdRDiagnosticoRiscoInfeccao,
                    EnAdRDiagnosticoRiscoIntegridade = :sEnAdRDiagnosticoRiscoIntegridade,
                    EnAdRDiagnosticoRiscoSufocacao = :sEnAdRDiagnosticoRiscoSufocacao,
                    EnAdRDiagnosticoTermoRregulacao = :sEnAdRDiagnosticoTermoRregulacao,
                    EnAdRDiagnosticoOutro = :sEnAdRDiagnosticoOutro,
                    EnAdRAvaliacaoEnfermagem = :sEnAdRAvaliacaoEnfermagem,
                    EnAdRUnidade = :sEnAdRUnidade          
                    WHERE EnAdRId = :sAdmissaoId";

                $result->execute(array(

                    ':sEnAdRDataInicio' => date('Y-m-d'),
                    ':sEnAdRHoraInicio' => date('H:i'),
                    ':sEnAdRPrevisaoAlta' => '',
                    ':sEnAdRTipoInternacao' => $row['TpIntId'],
                    ':sEnAdREspecialidadeLeito' => $row['EsLeiId'],
                    ':sEnAdRAla' => $row['AlaId'],
                    ':sEnAdRQuarto' => $row['QuartId'],
                    ':sEnAdRLeito' => $row['LeitoId'],
                    ':sEnAdRProfissional' => $userId, /****/
                    ':sEnAdRNomeMae' => $_POST['RN'] == "" ? null : $_POST['RN'],
                    ':sEnAdRDataNascimento' => $_POST['dataNascimento'] == "" ? null : $_POST['dataNascimento'], 
                    ':sEnAdRHoraNascimento' => $_POST['horaNascimento'] == "" ? null : $_POST['horaNascimento'],
                    ':sEnAdRSexo' =>$_POST['sexo'] == "" ? null : $_POST['sexo'],
                    ':sEnAdRChoroPresente' => isset($_POST['choro'])?($_POST['choro']=="SIM"?1:0):'',
                    ':sEnAdRApgar1min' => $_POST['Apgar1'] == "" ? null : $_POST['Apgar1'],
                    ':sEnAdRApgar5min' => $_POST['Apgar5'] == "" ? null : $_POST['Apgar5'],
                    ':sEnAdRAmamentacao1h' => isset($_POST['amamentacao'])?($_POST['amamentacao']=="SIM"?1:0):'',                        
                    ':sEnAdRMotivoNaoAleitamento' => $_POST['motivoAleitamento'] == "" ? null : $_POST['motivoAleitamento'],
                    ':sEnAdRFreqCardiaca' => $_POST['FC'] == "" ? null : $_POST['FC'], 
                    ':sEnAdRFreqRespiratoria' => $_POST['FR'] == "" ? null : $_POST['FR'],   
                    ':sEnAdRTemperatura' => $_POST['Temperatura'] == "" ? null : $_POST['Temperatura'],
                    ':sEnAdRSPO' => $_POST['SPO'] == "" ? null : $_POST['SPO'],
                    ':sEnAdRHGT' => $_POST['HGT'] == "" ? null : $_POST['HGT'],
                    ':sEnAdRPeso' => $_POST['Peso'] == "" ? null : $_POST['Peso'],
                    ':sEnAdRIdadeGestacional' => $_POST['idadeGestacional'] == "" ? null : $_POST['idadeGestacional'], 
                    ':sEnAdRFatorRH' => $_POST['fatorRH'] == "" ? null : $_POST['fatorRH'], 
                    ':sEnAdREstatura' => $_POST['Estatura'] == "" ? null : $_POST['Estatura'],
                    ':sEnAdRPerimetroCefalico' => $_POST['PC'] == "" ? null : $_POST['PC'], 
                    ':sEnAdRPerimetroToraxico' => $_POST['PT'] == "" ? null : $_POST['PT'], 
                    ':sEnAdRPerimetroAbdominal' => $_POST['PA'] == "" ? null : $_POST['PA'],
                    ':sEnAdRAtividadeHipoativo' => isset($_POST['hipoativo'])?1:0,
                    ':sEnAdRAtividadeSonolento' => isset($_POST['sonolento'])?1:0,
                    ':sEnAdRAtividadeAtivo' => isset($_POST['ativo'])?1:0,
                    ':sEnAdRAtividadeChoroso' => isset($_POST['choroso'])?1:0,
                    ':sEnAdRAtividadeGemente' => isset($_POST['gemente'])?1:0,
                    ':sEnAdRAtividadeDescricao' => $_POST['textAtividade'] == "" ? null : $_POST['textAtividade'],
                    ':sEnAdRColoracaoCorado' => isset($_POST['corado'])?1:0,
                    ':sEnAdRColoracaoHipoCorado' => isset($_POST['hipocorado'])?1:0,
                    ':sEnAdRColoracaoCianotico' => isset($_POST['cianotico'])?1:0,
                    ':sEnAdRColoracaoIcterico' => isset($_POST['icterico'])?1:0,
                    ':sEnAdRColoracaoPletorico' => isset($_POST['pletorico'])?1:0,
                    ':sEnAdRColoracaoDescricao' => $_POST['textColoracao'] == "" ? null : $_POST['textColoracao'],
                    ':sEnAdRHidratacao' => isset($_POST['hidratacao'])?($_POST['hidratacao']=='S'?1:0):null,
                    ':sEnAdRFontanela' => $_POST['fontanela'] == "" ? null : $_POST['fontanela'],
                    ':sEnAdRPele' => isset($_POST['pele'])?$_POST['pele']:'',
                    ':sEnAdRPeleDescricao' => $_POST['textPele'] == "" ? null : $_POST['textPele'], 
                    ':sEnAdRReflexoSuccao' => isset($_POST['succaoR'])?1:0,
                    ':sEnAdRReflexoMoro' => isset($_POST['moro'])?1:0,
                    ':sEnAdRReflexoPreensaoPalmar' => isset($_POST['preensaoPalmar'])?1:0,
                    ':sEnAdRReflexoPressaoPlantar' => isset($_POST['pressaoPlantar'])?1:0,
                    ':sEnAdRCabecaEscoriacao' => isset($_POST['escoriacoes'])?$_POST['escoriacoes']:null,
                    ':sEnAdRCabecaPIG' => isset($_POST['pig'])?1:0,
                    ':sEnAdRCabecaGIG' => isset($_POST['gig'])?1:0,
                    ':sEnAdRCabecaBossa' => isset($_POST['bossa'])?1:0,
                    ':sEnAdRCabecaCefalohematoma' => isset($_POST['Cefalohematoma'])?1:0,
                    ':sEnAdRCabecaMascaraEquimotica' => isset($_POST['mascaraEquimotica'])?1:0,
                    ':sEnAdRAbdome' => $_POST['abdome'] == "" ? null : $_POST['abdome'], 
                    ':sEnAdRSuccaoSatisfatoria' => isset($_POST['succao'])?($_POST['succao']=="S"?1:0):null,
                    ':sEnAdRPadraoRespiratorio' => isset($_POST['padraoRespiratorio'])?$_POST['padraoRespiratorio']:'',
                    ':sEnAdRPadraoRespiratorioDescricao' => $_POST['textPadraoRespiratorio'] == "" ? null : $_POST['textPadraoRespiratorio'], 
                    ':sEnAdRGenturinarioIntegro' => isset($_POST['integro'])?1:0,
                    ':sEnAdRGenturinarioDiurese' => isset($_POST['diurese'])?1:0,
                    ':sEnAdRGenturinarioAnusPervio' => isset($_POST['anusPervio'])?1:0,
                    ':sEnAdRGenturinarioMeconio' => isset($_POST['Meconio'])?1:0,
                    ':sEnAdRGenturinarioOutro' => isset($_POST['outros'])?1:0,
                    ':sEnAdRGenturinarioDescricao' => $_POST['textGenturinario'] == "" ? null : $_POST['textGenturinario'],
                    ':sEnAdRCotoLimpoSeco' => isset($_POST['limpoSeco'])?1:0,
                    ':sEnAdRCotoGelatinoso' => isset($_POST['gelatinoso'])?1:0,
                    ':sEnAdRCotoMumificado' => isset($_POST['mumificado'])?1:0,
                    ':sEnAdRCotoUmido' => isset($_POST['umido'])?1:0,
                    ':sEnAdRCotoSujo' => isset($_POST['sujo'])?1:0,
                    ':sEnAdRCotoFetido' => isset($_POST['fetido'])?1:0,
                    ':sEnAdRCotoHiperemia' => isset($_POST['hiperemia'])?1:0,
                    ':sEnAdRCotoDescricao' => $_POST['textCotoUmbilical'] == "" ? null : $_POST['textCotoUmbilical'], 
                    ':sEnAdRCateter' => isset($_POST['cateter'])?($_POST['cateter']=="SIM"?1:0):'',
                    ':sEnAdRCateterDescricao' => $_POST['textCateter'] == "" ? null : $_POST['textCateter'],
                    ':sEnAdRSonda' => isset($_POST['sonda'])?($_POST['sonda']=="SIM"?1:0):'',
                    ':sEnAdRSondaDescricao' => $_POST['textSonda'] == "" ? null : $_POST['textSonda'], 
                    ':sEnAdRDiagnosticoDorAguda' => isset($_POST['dorAguda'])?1:0,
                    ':sEnAdRDiagnosticoDeficitAutoCuidado' => isset($_POST['deficitAutoCuidado'])?1:0,
                    ':sEnAdRDiagnosticoEliminacaoUrinaria' => isset($_POST['eliminacaoUrinaria'])?1:0,
                    ':sEnAdRDiagnosticoNutricaoDesequilibrada' => isset($_POST['nutricaoDesequilibrada'])?1:0,
                    ':sEnAdRDiagnosticoPadraoRespiratorio' => isset($_POST['padraoRespiratorio'])?1:0,
                    ':sEnAdRDiagnosticoPadraoSono' => isset($_POST['padraoSono'])?1:0,
                    ':sEnAdRDiagnosticoRiscoConstipacao' => isset($_POST['riscoConstipacao'])?1:0,
                    ':sEnAdRDiagnosticoRiscoGlicemia' => isset($_POST['riscoGlicemia'])?1:0,
                    ':sEnAdRDiagnosticoRiscoIctericia' => isset($_POST['riscoIctericia'])?1:0,
                    ':sEnAdRDiagnosticoRiscoInfeccao' => isset($_POST['riscoInfeccao'])?1:0,
                    ':sEnAdRDiagnosticoRiscoIntegridade' => isset($_POST['riscoIntegridade'])?1:0,
                    ':sEnAdRDiagnosticoRiscoSufocacao' => isset($_POST['riscoSufocacao'])?1:0,
                    ':sEnAdRDiagnosticoTermoRregulacao' => isset($_POST['termorregulacao'])?1:0,
                    ':sEnAdRDiagnosticoOutro' => isset($_POST['riscoOutros'])?1:0,
                    ':sEnAdRAvaliacaoEnfermagem' => $_POST['textAvaliacao'] == "" ? null : $_POST['textAvaliacao'],
                    ':sEnAdRUnidade' => $iUnidade,
                    ':sAdmissaoId' => $iAtendimentoAdmissaoRN
                ));


                $_SESSION['msg']['titulo'] = "Sucesso";
                $_SESSION['msg']['mensagem'] = "Admissão alterada com sucesso!!!";
                $_SESSION['msg']['tipo'] = "success";
                $_SESSION['iAtendimentoId'] = $iAtendimentoId;


            }else {

                $sql = "INSERT INTO EnfermagemAdmissaoRN(EnAdRAtendimento,EnAdRDataInicio,EnAdRHoraInicio,
                    EnAdRPrevisaoAlta,EnAdRTipoInternacao,EnAdREspecialidadeLeito,EnAdRAla,EnAdRQuarto,EnAdRLeito,
                    EnAdRProfissional,EnAdRNomeMae,EnAdRDataNascimento,EnAdRHoraNascimento,EnAdRSexo,EnAdRChoroPresente,
                    EnAdRApgar1min,EnAdRApgar5min,EnAdRAmamentacao1h,EnAdRMotivoNaoAleitamento,EnAdRFreqCardiaca,
                    EnAdRFreqRespiratoria,EnAdRTemperatura,EnAdRSPO,EnAdRHGT,EnAdRPeso,EnAdRIdadeGestacional,EnAdRFatorRH,
                    EnAdREstatura,EnAdRPerimetroCefalico,EnAdRPerimetroToraxico,EnAdRPerimetroAbdominal,
                    EnAdRAtividadeHipoativo,EnAdRAtividadeSonolento,EnAdRAtividadeAtivo,EnAdRAtividadeChoroso,
                    EnAdRAtividadeGemente,EnAdRAtividadeDescricao,EnAdRColoracaoCorado,EnAdRColoracaoHipoCorado,
                    EnAdRColoracaoCianotico,EnAdRColoracaoIcterico,EnAdRColoracaoPletorico,EnAdRColoracaoDescricao,
                    EnAdRHidratacao,EnAdRFontanela,EnAdRPele,EnAdRPeleDescricao,EnAdRReflexoSuccao,EnAdRReflexoMoro,
                    EnAdRReflexoPreensaoPalmar,EnAdRReflexoPressaoPlantar,EnAdRCabecaEscoriacao,EnAdRCabecaPIG,
                    EnAdRCabecaGIG,EnAdRCabecaBossa,EnAdRCabecaCefalohematoma,EnAdRCabecaMascaraEquimotica,EnAdRAbdome,
                    EnAdRSuccaoSatisfatoria,EnAdRPadraoRespiratorio,EnAdRPadraoRespiratorioDescricao,EnAdRGenturinarioIntegro,
                    EnAdRGenturinarioDiurese,EnAdRGenturinarioAnusPervio,EnAdRGenturinarioMeconio,EnAdRGenturinarioOutro,
                    EnAdRGenturinarioDescricao,EnAdRCotoLimpoSeco,EnAdRCotoGelatinoso,EnAdRCotoMumificado,EnAdRCotoUmido,
                    EnAdRCotoSujo,EnAdRCotoFetido,EnAdRCotoHiperemia,EnAdRCotoDescricao,EnAdRCateter,EnAdRCateterDescricao,
                    EnAdRSonda,EnAdRSondaDescricao,EnAdRDiagnosticoDorAguda,EnAdRDiagnosticoDeficitAutoCuidado,
                    EnAdRDiagnosticoEliminacaoUrinaria,EnAdRDiagnosticoNutricaoDesequilibrada,
                    EnAdRDiagnosticoPadraoRespiratorio,EnAdRDiagnosticoPadraoSono,EnAdRDiagnosticoRiscoConstipacao,
                    EnAdRDiagnosticoRiscoGlicemia,EnAdRDiagnosticoRiscoIctericia,EnAdRDiagnosticoRiscoInfeccao,
                    EnAdRDiagnosticoRiscoIntegridade,EnAdRDiagnosticoRiscoSufocacao,EnAdRDiagnosticoTermoRregulacao,
                    EnAdRDiagnosticoOutro,EnAdRAvaliacaoEnfermagem,EnAdRUnidade)
                VALUES(:EnAdRAtendimento,
                    :EnAdRDataInicio,
                    :EnAdRHoraInicio,
                    :EnAdRPrevisaoAlta,
                    :EnAdRTipoInternacao,
                    :EnAdREspecialidadeLeito,
                    :EnAdRAla,
                    :EnAdRQuarto,
                    :EnAdRLeito,
                    :EnAdRProfissional,
                    :EnAdRNomeMae,
                    :EnAdRDataNascimento,
                    :EnAdRHoraNascimento,
                    :EnAdRSexo,
                    :EnAdRChoroPresente,
                    :EnAdRApgar1min,
                    :EnAdRApgar5min,
                    :EnAdRAmamentacao1h,
                    :EnAdRMotivoNaoAleitamento,
                    :EnAdRFreqCardiaca,
                    :EnAdRFreqRespiratoria,
                    :EnAdRTemperatura,
                    :EnAdRSPO,
                    :EnAdRHGT,
                    :EnAdRPeso,
                    :EnAdRIdadeGestacional,
                    :EnAdRFatorRH,
                    :EnAdREstatura,
                    :EnAdRPerimetroCefalico,
                    :EnAdRPerimetroToraxico,
                    :EnAdRPerimetroAbdominal,
                    :EnAdRAtividadeHipoativo,
                    :EnAdRAtividadeSonolento,
                    :EnAdRAtividadeAtivo,
                    :EnAdRAtividadeChoroso,
                    :EnAdRAtividadeGemente,
                    :EnAdRAtividadeDescricao,
                    :EnAdRColoracaoCorado,
                    :EnAdRColoracaoHipoCorado,
                    :EnAdRColoracaoCianotico,
                    :EnAdRColoracaoIcterico,
                    :EnAdRColoracaoPletorico,
                    :EnAdRColoracaoDescricao,
                    :EnAdRHidratacao,
                    :EnAdRFontanela,
                    :EnAdRPele,
                    :EnAdRPeleDescricao,
                    :EnAdRReflexoSuccao,
                    :EnAdRReflexoMoro,
                    :EnAdRReflexoPreensaoPalmar,
                    :EnAdRReflexoPressaoPlantar,
                    :EnAdRCabecaEscoriacao,
                    :EnAdRCabecaPIG,
                    :EnAdRCabecaGIG,
                    :EnAdRCabecaBossa,
                    :EnAdRCabecaCefalohematoma,
                    :EnAdRCabecaMascaraEquimotica,
                    :EnAdRAbdome,
                    :EnAdRSuccaoSatisfatoria,
                    :EnAdRPadraoRespiratorio,
                    :EnAdRPadraoRespiratorioDescricao,
                    :EnAdRGenturinarioIntegro,
                    :EnAdRGenturinarioDiurese,
                    :EnAdRGenturinarioAnusPervio,
                    :EnAdRGenturinarioMeconio,
                    :EnAdRGenturinarioOutro,
                    :EnAdRGenturinarioDescricao,
                    :EnAdRCotoLimpoSeco,
                    :EnAdRCotoGelatinoso,
                    :EnAdRCotoMumificado,
                    :EnAdRCotoUmido,
                    :EnAdRCotoSujo,
                    :EnAdRCotoFetido,
                    :EnAdRCotoHiperemia,
                    :EnAdRCotoDescricao,
                    :EnAdRCateter,
                    :EnAdRCateterDescricao,
                    :EnAdRSonda,
                    :EnAdRSondaDescricao,
                    :EnAdRDiagnosticoDorAguda,
                    :EnAdRDiagnosticoDeficitAutoCuidado,
                    :EnAdRDiagnosticoEliminacaoUrinaria,
                    :EnAdRDiagnosticoNutricaoDesequilibrada,
                    :EnAdRDiagnosticoPadraoRespiratorio,
                    :EnAdRDiagnosticoPadraoSono,
                    :EnAdRDiagnosticoRiscoConstipacao,
                    :EnAdRDiagnosticoRiscoGlicemia,
                    :EnAdRDiagnosticoRiscoIctericia,
                    :EnAdRDiagnosticoRiscoInfeccao,
                    :EnAdRDiagnosticoRiscoIntegridade,
                    :EnAdRDiagnosticoRiscoSufocacao,
                    :EnAdRDiagnosticoTermoRregulacao,
                    :EnAdRDiagnosticoOutro,
                    :EnAdRAvaliacaoEnfermagem,
                    :EnAdRUnidade)";
                $result = $conn->prepare($sql);

                $result->execute(array(
                    ':EnAdRAtendimento' => $iAtendimentoId,
                    ':EnAdRDataInicio' => date('Y-m-d'),
                    ':EnAdRHoraInicio' => date('H:i'),
                    ':EnAdRPrevisaoAlta' => '',
                    ':EnAdRTipoInternacao' => $row['TpIntId'],
                    ':EnAdREspecialidadeLeito' => $row['EsLeiId'],
                    ':EnAdRAla' => $row['AlaId'],
                    ':EnAdRQuarto' => $row['QuartId'],
                    ':EnAdRLeito' => $row['LeitoId'],
                    ':EnAdRProfissional' => $userId, /****/

                    ':EnAdRNomeMae' => $_POST['RN'] == "" ? null : $_POST['RN'],

                    ':EnAdRDataNascimento' => $_POST['dataNascimento'] == "" ? null : $_POST['dataNascimento'], 
                    ':EnAdRHoraNascimento' => $_POST['horaNascimento'] == "" ? null : $_POST['horaNascimento'],
                    ':EnAdRSexo' =>$_POST['sexo'] == "" ? null : $_POST['sexo'],

                    ':EnAdRChoroPresente' => isset($_POST['choro'])?($_POST['choro']=="SIM"?1:0):'',

                    ':EnAdRApgar1min' => $_POST['Apgar1'] == "" ? null : $_POST['Apgar1'],
                    ':EnAdRApgar5min' => $_POST['Apgar5'] == "" ? null : $_POST['Apgar5'],

                    ':EnAdRAmamentacao1h' => isset($_POST['amamentacao'])?($_POST['amamentacao']=="SIM"?1:0):'',
                        
                    ':EnAdRMotivoNaoAleitamento' => $_POST['motivoAleitamento'] == "" ? null : $_POST['motivoAleitamento'],
                    ':EnAdRFreqCardiaca' => $_POST['FC'] == "" ? null : $_POST['FC'], 
                    ':EnAdRFreqRespiratoria' => $_POST['FR'] == "" ? null : $_POST['FR'],   

                    ':EnAdRTemperatura' => $_POST['Temperatura'] == "" ? null : $_POST['Temperatura'],
                    ':EnAdRSPO' => $_POST['SPO'] == "" ? null : $_POST['SPO'],
                    ':EnAdRHGT' => $_POST['HGT'] == "" ? null : $_POST['HGT'],
                    ':EnAdRPeso' => $_POST['Peso'] == "" ? null : $_POST['Peso'],
                    ':EnAdRIdadeGestacional' => $_POST['idadeGestacional'] == "" ? null : $_POST['idadeGestacional'], 
                    ':EnAdRFatorRH' => $_POST['fatorRH'] == "" ? null : $_POST['fatorRH'], 
                    ':EnAdREstatura' => $_POST['Estatura'] == "" ? null : $_POST['Estatura'],

                    ':EnAdRPerimetroCefalico' => $_POST['PC'] == "" ? null : $_POST['PC'], 
                    ':EnAdRPerimetroToraxico' => $_POST['PT'] == "" ? null : $_POST['PT'], 
                    ':EnAdRPerimetroAbdominal' => $_POST['PA'] == "" ? null : $_POST['PA'],

                    ':EnAdRAtividadeHipoativo' => isset($_POST['hipoativo'])?1:0,
                    ':EnAdRAtividadeSonolento' => isset($_POST['sonolento'])?1:0,
                    ':EnAdRAtividadeAtivo' => isset($_POST['ativo'])?1:0,
                    ':EnAdRAtividadeChoroso' => isset($_POST['choroso'])?1:0,
                    ':EnAdRAtividadeGemente' => isset($_POST['gemente'])?1:0,

                    ':EnAdRAtividadeDescricao' => $_POST['textAtividade'] == "" ? null : $_POST['textAtividade'],

                    ':EnAdRColoracaoCorado' => isset($_POST['corado'])?1:0,
                    ':EnAdRColoracaoHipoCorado' => isset($_POST['hipocorado'])?1:0,
                    ':EnAdRColoracaoCianotico' => isset($_POST['cianotico'])?1:0,
                    ':EnAdRColoracaoIcterico' => isset($_POST['icterico'])?1:0,
                    ':EnAdRColoracaoPletorico' => isset($_POST['pletorico'])?1:0,

                    ':EnAdRColoracaoDescricao' => $_POST['textColoracao'] == "" ? null : $_POST['textColoracao'],
                    ':EnAdRHidratacao' => isset($_POST['hidratacao'])?($_POST['hidratacao']=='S'?1:0):null,
                    ':EnAdRFontanela' => $_POST['fontanela'] == "" ? null : $_POST['fontanela'],

                    ':EnAdRPele' => isset($_POST['pele'])?$_POST['pele']:'',


                    ':EnAdRPeleDescricao' => $_POST['textPele'] == "" ? null : $_POST['textPele'], 


                    ':EnAdRReflexoSuccao' => isset($_POST['succaoR'])?1:0,
                    ':EnAdRReflexoMoro' => isset($_POST['moro'])?1:0,
                    ':EnAdRReflexoPreensaoPalmar' => isset($_POST['preensaoPalmar'])?1:0,
                    ':EnAdRReflexoPressaoPlantar' => isset($_POST['pressaoPlantar'])?1:0,
                    // ':EnAdRReflexoDescricao' => isset($_POST['textReflexos'])?$_POST['textReflexos']:'',
                    ':EnAdRCabecaEscoriacao' => isset($_POST['escoriacoes'])?$_POST['escoriacoes']:null,
                    ':EnAdRCabecaPIG' => isset($_POST['pig'])?1:0,
                    ':EnAdRCabecaGIG' => isset($_POST['gig'])?1:0,
                    ':EnAdRCabecaBossa' => isset($_POST['bossa'])?1:0,
                    ':EnAdRCabecaCefalohematoma' => isset($_POST['Cefalohematoma'])?1:0,
                    ':EnAdRCabecaMascaraEquimotica' => isset($_POST['mascaraEquimotica'])?1:0,


                    ':EnAdRAbdome' => $_POST['abdome'] == "" ? null : $_POST['abdome'], 

                    ':EnAdRSuccaoSatisfatoria' => isset($_POST['succao'])?($_POST['succao']=="S"?1:0):null,

                    ':EnAdRPadraoRespiratorio' => isset($_POST['padraoRespiratorio'])?$_POST['padraoRespiratorio']:'',

                    ':EnAdRPadraoRespiratorioDescricao' => $_POST['textPadraoRespiratorio'] == "" ? null : $_POST['textPadraoRespiratorio'], 

                    ':EnAdRGenturinarioIntegro' => isset($_POST['integro'])?1:0,
                    ':EnAdRGenturinarioDiurese' => isset($_POST['diurese'])?1:0,
                    ':EnAdRGenturinarioAnusPervio' => isset($_POST['anusPervio'])?1:0,
                    ':EnAdRGenturinarioMeconio' => isset($_POST['Meconio'])?1:0,
                    ':EnAdRGenturinarioOutro' => isset($_POST['outros'])?1:0,


                    ':EnAdRGenturinarioDescricao' => $_POST['textGenturinario'] == "" ? null : $_POST['textGenturinario'],


                    ':EnAdRCotoLimpoSeco' => isset($_POST['limpoSeco'])?1:0,
                    ':EnAdRCotoGelatinoso' => isset($_POST['gelatinoso'])?1:0,
                    ':EnAdRCotoMumificado' => isset($_POST['mumificado'])?1:0,
                    ':EnAdRCotoUmido' => isset($_POST['umido'])?1:0,
                    ':EnAdRCotoSujo' => isset($_POST['sujo'])?1:0,
                    ':EnAdRCotoFetido' => isset($_POST['fetido'])?1:0,
                    ':EnAdRCotoHiperemia' => isset($_POST['hiperemia'])?1:0,


                    ':EnAdRCotoDescricao' => $_POST['textCotoUmbilical'] == "" ? null : $_POST['textCotoUmbilical'], 


                    ':EnAdRCateter' => isset($_POST['cateter'])?($_POST['cateter']=="SIM"?1:0):'',


                    ':EnAdRCateterDescricao' => $_POST['textCateter'] == "" ? null : $_POST['textCateter'],


                    ':EnAdRSonda' => isset($_POST['sonda'])?($_POST['sonda']=="SIM"?1:0):'',


                    ':EnAdRSondaDescricao' => $_POST['textSonda'] == "" ? null : $_POST['textSonda'], 


                    ':EnAdRDiagnosticoDorAguda' => isset($_POST['dorAguda'])?1:0,
                    ':EnAdRDiagnosticoDeficitAutoCuidado' => isset($_POST['deficitAutoCuidado'])?1:0,
                    ':EnAdRDiagnosticoEliminacaoUrinaria' => isset($_POST['eliminacaoUrinaria'])?1:0,
                    ':EnAdRDiagnosticoNutricaoDesequilibrada' => isset($_POST['nutricaoDesequilibrada'])?1:0,
                    ':EnAdRDiagnosticoPadraoRespiratorio' => isset($_POST['padraoRespiratorio'])?1:0,
                    ':EnAdRDiagnosticoPadraoSono' => isset($_POST['padraoSono'])?1:0,
                    ':EnAdRDiagnosticoRiscoConstipacao' => isset($_POST['riscoConstipacao'])?1:0,
                    ':EnAdRDiagnosticoRiscoGlicemia' => isset($_POST['riscoGlicemia'])?1:0,
                    ':EnAdRDiagnosticoRiscoIctericia' => isset($_POST['riscoIctericia'])?1:0,
                    ':EnAdRDiagnosticoRiscoInfeccao' => isset($_POST['riscoInfeccao'])?1:0,
                    ':EnAdRDiagnosticoRiscoIntegridade' => isset($_POST['riscoIntegridade'])?1:0,
                    ':EnAdRDiagnosticoRiscoSufocacao' => isset($_POST['riscoSufocacao'])?1:0,
                    ':EnAdRDiagnosticoTermoRregulacao' => isset($_POST['termorregulacao'])?1:0,
                    ':EnAdRDiagnosticoOutro' => isset($_POST['riscoOutros'])?1:0,
                    ':EnAdRAvaliacaoEnfermagem' => $_POST['textAvaliacao'] == "" ? null : $_POST['textAvaliacao'],
                    ':EnAdRUnidade' => $iUnidade
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

        irpara("admissaoRecemNascido.php");
        
    }
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Admissão de Recém Nascido</title>

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
            $('#tblAcessoVenoso').DataTable({
				"order": [[ 0, "asc" ]],
			    autoWidth: false,
				responsive: true,
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

            $("#textMedicamentos").on('input', function(e){
                cantaCaracteres('textMedicamentos', 150, 'caracteresInputMedicamentos')
            })

            $('#addAcesso').on('click',function(e){
                e.preventDefault()
                $.ajax({
					type: 'POST',
					url: 'filtraAdmissaoCirurgicaPreOperatorio.php',
					dataType: 'json',
					data:{
						'tipoRequest': 'ACESSOVENOSO',
                        'data': $('#dataAcessoVenoso').val(),
                        'hora': $('#horaAcessoVenoso').val(),
                        'lado': $('#ladoAcessoVenoso').val(),
                        'calibre': $('#calibreAcessoVenoso').val(),
                        'responsavel': $('#responsavelAcessoVenoso').val(),

					},
					success: function(response) {
                        $('#dataAcessoVenoso').val('')
                        $('#horaAcessoVenoso').val('')
                        $('#ladoAcessoVenoso').val('')
                        $('#calibreAcessoVenoso').val('')
                        $('#responsavelAcessoVenoso').val('')

                        cheackList()
					}
				});
            })

            $('#modal-acesso-close-x').on('click', function(e){
                e.preventDefault()
                $('#tblAcessoVenosoViwer').addClass('d-none')
                $('#page-modal-acesso').fadeOut(200)
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
        textarea{
            height:40px;
        }
        .options{
            height:40px;
        }
        .text-float-border{
            position: absolute;
            top: 5px;
            left: 60px;
            background-color: #ffffff;
            padding-left: 10px;
            padding-right: 10px;
        }

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
						<form name="formAtendimentoAdmissaoPediatrica" id="formAtendimentoAdmissaoPediatrica" method="post">
							<?php
								echo "<input type='hidden' id='iAtendimentoId' name='iAtendimentoId' value='$iAtendimentoId' />";
							?>
							<div class="card">

                                <div class="col-md-12">
                                    <div class="row">

                                        <div class="col-md-6" style="text-align: left;">

                                            <div class="card-header header-elements-inline">
                                                <h3 class="card-title"><b>Admissão de RN</b></h3>
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
                                <?php //include ('atendimentoDadosSinaisVitais.php'); ?>
                            </div>

                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-lg-12">	
                                            <button type="button" id="pacientes-espera-btn" class="btn-grid btn btn-outline-secondary btn-lg itemLink" data-tipo='admissaoPreParto' >Admissão Pré Parto</button>
                                            <button type="button" id="pacientes-atendimento-btn" class="btn-grid btn btn-outline-secondary btn-lg active" >Admissão RN</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-header header-elements-inline">
                                    <h3 class="card-title font-weight-bold">Admissão RN</h3>
                                </div>
                            </div>

                            <div class="box-exameFisico" style="display: block;">
                                <div class="card">

                                    <div class="card-header header-elements-inline">
                                        <h3 class="card-title font-weight-bold ">Evolução Recém Nascido</h3>

                                        <div class="header-elements">
                                            <div class="list-icons">
                                                <a class="list-icons-item" data-action="collapse"></a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-lg-7">
                                                <div class="form-group">
                                                    <label for="RN">RN / Mãe</label>
                                                    <input id="RN" class="form-control" type="text" name="RN" value="<?php echo isset($iAtendimentoAdmissaoRN) ? $rowAdmissao['EnAdRNomeMae'] : ''; ?>">
                                                </div>
                                            </div>

                                            <div class="col-lg-5 row">
                                                <div class="col-lg-4">
                                                    <div class="form-group">
                                                        <label for="dataNascimento">Data de Nascimento</label>
                                                        <input id="dataNascimento" class="form-control" type="date" name="dataNascimento" value="<?php echo isset($iAtendimentoAdmissaoRN) ? $rowAdmissao['EnAdRDataNascimento'] : ''; ?>">
                                                    </div>
                                                </div>
                                                <div class="col-lg-4">
                                                    <div class="form-group">
                                                        <label for="horaNascimento">Hora de Nascimento</label>
                                                        <input id="horaNascimento" class="form-control" type="time" name="horaNascimento" value="<?php echo isset($iAtendimentoAdmissaoRN) ? $rowAdmissao['EnAdRHoraNascimento'] : ''; ?>" >
                                                    </div>                                                    
                                                </div>
                                                <div class="col-lg-4">
                                                    <div class="form-group">
                                                        <label for="sexo">Sexo</label>
                                                        <select id="sexo" name="sexo" class="select-search">
                                                            <option value=''>selecione</option>
                                                            <option value='M' <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRSexo'] == 'M' ? 'selected' : ''; ?> >Masculino</option>
                                                            <option value='F' <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRSexo'] == 'F' ? 'selected' : ''; ?> >Feminino</option>
                                                        </select>
                                                    </div>                                                    
                                                </div>                                                
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-2">
                                                <div class="form-group" id="box-choro-presente">
                                                    <label class="d-block ">Choro Presente</label>
                                                    <div class="form-check form-check-inline">
                                                        <label class="form-check-label">
                                                            <input type="radio" class="form-input-styled" name="choro" value="SIM" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRChoroPresente'] == '1' ? 'checked' : ''; ?> >
                                                            Sim
                                                        </label>
                                                    </div>

                                                    <div class="form-check form-check-inline">
                                                        <label class="form-check-label">
                                                            <input type="radio" class="form-input-styled" name="choro" value="NÃO" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRChoroPresente'] == '0' ? 'checked' : ''; ?> >
                                                            Não
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-lg-1">
                                                <div class="form-group">
                                                    <label for="Apgar1">Apgar 1 min</label>
                                                    <input id="Apgar1" class="form-control" type="number" name="Apgar1" value="<?php echo isset($iAtendimentoAdmissaoRN) ? $rowAdmissao['EnAdRApgar1min'] : ''; ?>" >
                                                </div>
                                            </div>

                                            <div class="col-lg-1">
                                                <div class="form-group">
                                                    <label for="Apgar5">Apgar 5 min</label>
                                                    <input id="Apgar5" class="form-control" type="number" name="Apgar5" value="<?php echo isset($iAtendimentoAdmissaoRN) ? $rowAdmissao['EnAdRApgar5min'] : ''; ?>" >
                                                </div>
                                            </div>

                                            <div class="col-lg-3">
                                                <div class="form-group" id="box-amamentacao">
                                                    <label class="d-block ">Amamentação na 1ª hora de vida</label>
                                                    <div class="form-check form-check-inline">
                                                        <label class="form-check-label">
                                                            <input type="radio" class="form-input-styled" name="amamentacao" value="SIM" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRAmamentacao1h'] == '1' ? 'checked' : ''; ?> >
                                                            Sim
                                                        </label>
                                                    </div>

                                                    <div class="form-check form-check-inline">
                                                        <label class="form-check-label">
                                                            <input type="radio" class="form-input-styled" name="amamentacao" value="NÃO" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRAmamentacao1h'] == '1' ? 'checked' : ''; ?> >
                                                            Não
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-lg-5">
                                                <div class="form-group">
                                                    <label for="motivoAleitamento">Motivo do não aleitamento</label>
                                                    <textarea id="motivoAleitamento" name="motivoAleitamento" onInput="contarCaracteres(this);" class="form-control" rows="4" cols="4" maxLength="30" placeholder="" ><?php echo isset($iAtendimentoAdmissaoRN) ? $rowAdmissao['EnAdRMotivoNaoAleitamento'] : ''; ?></textarea>            
                                                    <small class="text-muted form-text">Max. 30 caracteres<span class="caracteresmotivoAleitamento"></span></small>                              
                                                </div>
                                            </div>

                                        </div>

                                        <div class="row">
                                            <div class="card-header header-elements-inline" style="margin-left: -10px">
                                                <h3 class="card-title font-weight-bold">SSVV / Monitoramento</h3>
                                            </div>
                                        </div>

                                        <div class="row">                                                                          
                                            <!-- linha 3 -->
                                            <div class="col-lg-12 mb-3 row">
                                                <!-- titulos -->
                                                <div class="col-lg-2">
                                                    <label>FC (bpm)</label>
                                                </div>
                                                <div class="col-lg-2">
                                                    <label>FR (irpm)</label>
                                                </div>
                                                <div class="col-lg-2">
                                                    <label>Temperatura (C°)</label>
                                                </div>
                                                <div class="col-lg-2">
                                                    <label>SPO (%)</label>
                                                </div>
                                                <div class="col-lg-2">
                                                    <label>HGT (mg/dl)</label>
                                                </div>
                                                <div class="col-lg-2">
                                                    <label>Peso (Kg)</label>
                                                </div>
                                                
                                                <!-- campos -->
                                                <div class="col-lg-2">
                                                    <input id="FC" name="FC"  class="form-control" type="number" value="<?php echo isset($iAtendimentoAdmissaoRN) ? $rowAdmissao['EnAdRFreqCardiaca'] : ''; ?>" >
                                                </div>
                                                <div class="col-lg-2">
                                                    <input id="FR" name="FR" class="form-control" type="number" value="<?php echo isset($iAtendimentoAdmissaoRN) ? $rowAdmissao['EnAdRFreqRespiratoria'] : ''; ?>" >
                                                </div>
                                                <div class="col-lg-2">
                                                    <input id="Temperatura" name="Temperatura"  class="form-control" type="number" value="<?php echo isset($iAtendimentoAdmissaoRN) ? $rowAdmissao['EnAdRTemperatura'] : ''; ?>" >
                                                </div>
                                                <div class="col-lg-2">
                                                    <input id="SPO" name="SPO" class="form-control" type="number" value="<?php echo isset($iAtendimentoAdmissaoRN) ? $rowAdmissao['EnAdRSPO'] : ''; ?>" >
                                                </div>
                                                <div class="col-lg-2">
                                                    <input id="HGT" name="HGT"  class="form-control" type="number" value="<?php echo isset($iAtendimentoAdmissaoRN) ? $rowAdmissao['EnAdRHGT'] : ''; ?>" >
                                                </div>
                                                <div class="col-lg-2">
                                                    <input id="Peso" name="Peso" class="form-control" type="number" value="<?php echo isset($iAtendimentoAdmissaoRN) ? $rowAdmissao['EnAdRPeso'] : ''; ?>" >
                                                </div>
                                            </div>

                                            <!-- linha 4 -->
                                            <div class="col-lg-12 mb-3 row">
                                                <!-- titulos -->
                                                <div class="col-lg-2">
                                                    <label>Idade Gestacional</label>
                                                </div>
                                                <div class="col-lg-2">
                                                    <label>TS/Fator RH</label>
                                                </div>
                                                <div class="col-lg-2">
                                                    <label>Estatura (cm)</label>
                                                </div>
                                                <div class="col-lg-2">
                                                    <label>PC (cm)</label>
                                                </div>
                                                <div class="col-lg-2">
                                                    <label>PT (cm)</label>
                                                </div>
                                                <div class="col-lg-2">
                                                    <label>PA (cm)</label>
                                                </div>
                                                
                                                <!-- campos -->
                                                <div class="col-lg-2">
                                                    <input id="idadeGestacional" name="idadeGestacional"  class="form-control" type="text" value="<?php echo isset($iAtendimentoAdmissaoRN) ? $rowAdmissao['EnAdRIdadeGestacional'] : ''; ?>" >
                                                </div>
                                                <div class="col-lg-2">
                                                    <select id="fatorRH" name="fatorRH" class="select-search">
                                                        <option value=''>selecione</option>
                                                        <option value='A+' <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRFatorRH'] == 'A+' ? 'selected' : ''; ?> >A+</option>
                                                        <option value='B+' <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRFatorRH'] == 'B+' ? 'selected' : ''; ?> >B+</option>
                                                        <option value='AB+' <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRFatorRH'] == 'AB+' ? 'selected' : ''; ?> >AB+</option>
                                                        <option value='O+' <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRFatorRH'] == 'O+' ? 'selected' : ''; ?> >O+</option>
                                                        <option value='A-' <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRFatorRH'] == 'A-' ? 'selected' : ''; ?> >A-</option>
                                                        <option value='B-' <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRFatorRH'] == 'B-' ? 'selected' : ''; ?> >B-</option>
                                                    </select>
                                                </div>
                                                <div class="col-lg-2">
                                                    <input id="Estatura" name="Estatura"  class="form-control" type="number" value="<?php echo isset($iAtendimentoAdmissaoRN) ? $rowAdmissao['EnAdREstatura'] : '0'; ?>">
                                                </div>
                                                <div class="col-lg-2">
                                                    <input id="PC" name="PC" class="form-control" type="number" value="<?php echo isset($iAtendimentoAdmissaoRN) ? $rowAdmissao['EnAdRPerimetroCefalico'] : '0'; ?>">
                                                </div>
                                                <div class="col-lg-2">
                                                    <input id="PT" name="PT"  class="form-control" type="number" value="<?php echo isset($iAtendimentoAdmissaoRN) ? $rowAdmissao['EnAdRPerimetroToraxico'] : '0'; ?>">
                                                </div>
                                                <div class="col-lg-2">
                                                    <input id="PA" name="PA" class="form-control" type="number" value="<?php echo isset($iAtendimentoAdmissaoRN) ? $rowAdmissao['EnAdRAtividadeHipoativo'] : '0'; ?>">
                                                </div>
                                            </div>                                        
                                        </div>


                                        <div class="row">
                                            <div class="col-lg-6">

                                                <div class="card-header header-elements-inline" style="margin-left: -20px">
                                                    <h3 class="card-title font-weight-bold">Atividade</h3>
                                                </div>

                                                <div class="col-lg-12">
                                                    <div class="row">
                                                        <div class="form-check form-check-inline mr-4 ">
                                                            <label class="form-check-label">
                                                                <input type="checkbox" class="form-check-input" name="hipoativo" id="checkAtividadeHipoativo" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRAtividadeHipoativo'] == '1' ? 'checked' : ''; ?>  >
                                                                Hipoativo
                                                            </label>
                                                        </div>

                                                        <div class="form-check form-check-inline mr-4 " >
                                                            <label class="form-check-label">
                                                                <input type="checkbox" class="form-check-input" name="sonolento" id="checkAtividadeSonolento" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRAtividadeSonolento'] == '1' ? 'checked' : ''; ?> >
                                                                Sonolento
                                                            </label>
                                                        </div>

                                                        <div class="form-check form-check-inline mr-4 ">
                                                            <label class="form-check-label">
                                                                <input type="checkbox" class="form-check-input" name="ativo" id="checkAtividadeAtivo" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRAtividadeAtivo'] == '1' ? 'checked' : ''; ?> >
                                                                Ativo
                                                            </label>
                                                        </div>
                                                        <div class="form-check form-check-inline mr-4 ">
                                                            <label class="form-check-label">
                                                                <input type="checkbox" class="form-check-input" name="choroso" id="checkAtividadeChoroso" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRAtividadeChoroso'] == '1' ? 'checked' : ''; ?> >
                                                                Choroso
                                                            </label>
                                                        </div>
                                                        <div class="form-check form-check-inline mr-4 ">
                                                            <label class="form-check-label">
                                                                <input type="checkbox" class="form-check-input" name="gemente" id="checkAtividadeGemente" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRAtividadeGemente'] == '1' ? 'checked' : ''; ?> >
                                                                Gemente
                                                            </label>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <textarea id="textAtividade" name="textAtividade" class="form-control" onInput="contarCaracteres(this);" rows="4" cols="4" maxLength="30" placeholder="" ><?php echo isset($iAtendimentoAdmissaoRN) ? $rowAdmissao['EnAdRAtividadeDescricao'] : ''; ?></textarea>
                                                        <small class="text-muted form-text">Max. 30 caracteres<span class="caracterestextAtividade"></span></small> 
                                                       
                                                    </div>
                                                </div>

                                            </div>

                                            <div class="col-lg-6">

                                                <div class="card-header header-elements-inline" style="margin-left: -20px">
                                                    <h3 class="card-title font-weight-bold">Coloração</h3>
                                                </div>

                                                <div class="col-lg-12">
                                                    <div class="row">
                                                        <div class="form-check form-check-inline mr-4 ">
                                                            <label class="form-check-label">
                                                                <input type="checkbox" class="form-check-input" name="corado" id="checkColoracaoCorado" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRColoracaoCorado'] == '1' ? 'checked' : ''; ?>  >
                                                                Corado
                                                            </label>
                                                        </div>

                                                        <div class="form-check form-check-inline mr-4 ">
                                                            <label class="form-check-label">
                                                                <input type="checkbox" class="form-check-input" name="hipocorado" id="checkColoracaoHipocorado" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRColoracaoHipoCorado'] == '1' ? 'checked' : ''; ?> >
                                                                Hipocorado
                                                            </label>
                                                        </div>

                                                        <div class="form-check form-check-inline mr-4 ">
                                                            <label class="form-check-label">
                                                                <input type="checkbox" class="form-check-input" name="cianotico" id="checkColoracaoCianotico" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRColoracaoCianotico'] == '1' ? 'checked' : ''; ?> >
                                                                Cianotico
                                                            </label>
                                                        </div>
                                                        <div class="form-check form-check-inline mr-4 ">
                                                            <label class="form-check-label">
                                                                <input type="checkbox" class="form-check-input" name="icterico" id="checkColoracaoIcterico" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRColoracaoIcterico'] == '1' ? 'checked' : ''; ?> >
                                                                Ictério
                                                            </label>
                                                        </div>
                                                        <div class="form-check form-check-inline mr-4 ">
                                                            <label class="form-check-label">
                                                                <input type="checkbox" class="form-check-input" name="pletorico" id="checkColoracaoPletorico" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRColoracaoPletorico'] == '1' ? 'checked' : ''; ?> >
                                                                Pletórico
                                                            </label>
                                                        </div>                                                        
                                                    </div>

                                                    <div class="row">
                                                        <textarea id="textColoracao" name="textColoracao" class="form-control"  onInput="contarCaracteres(this);" rows="4" cols="4" maxLength="30" placeholder="" ><?php echo isset($iAtendimentoAdmissaoRN) ? $rowAdmissao['EnAdRColoracaoDescricao'] : ''; ?></textarea>
                                                        <small class="text-muted form-text">Max. 30 caracteres<span class="caracterestextColoracao"></span></small>    
                                                    </div>
                                                </div>  
                                                
                                            </div>
                                        </div>

                                        <div class="row col-lg-6">
                                            <div class="col-lg-4" style="margin-left: -10px;">
                                                <div class="card-header header-elements-inline" style="margin-left: -20px;">
                                                    <h3 class="card-title font-weight-bold">Hidratação</h3>
                                                </div>
                                                <select id="hidratacao" name="hidratacao" class="select">
                                                    <option value=''>selecione</option>
                                                    <option value='S' <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRHidratacao'] == '1' ? 'selected' : ''; ?> >SIM</option>
                                                    <option value='N' <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRHidratacao'] == '0' ? 'selected' : ''; ?> >NÃO</option>
                                                </select>
                                            </div>
                                            <div class="col-lg-8">
                                                <div class="card-header header-elements-inline" style="margin-left: -20px;">
                                                    <h3 class="card-title font-weight-bold">Fontanela</h3>
                                                </div> 
                                                <select id="fontanela" name="fontanela" class="select-search">
                                                    <option value=''>selecione</option>
                                                    <option value='NO' <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRFontanela'] == 'NO' ? 'selected' : ''; ?> >NOMOTENSA</option>
                                                    <option value='AB' <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRFontanela'] == 'AB' ? 'selected' : ''; ?> >ABAULADA</option>
                                                    <option value='DE' <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRFontanela'] == 'DE' ? 'selected' : ''; ?> >DEPRIMIDA</option>
                                                    <option value='CA' <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRFontanela'] == 'CA' ? 'selected' : ''; ?> >CAVALGADURA</option>
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
                                                                <input type="radio" class="form-input-styled" name="pele" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRPele'] == 'IN' ? 'checked' : ''; ?> >
                                                                Íntegra
                                                            </label>
                                                        </div>
                                                        <div class="form-check form-check-inline mr-4">
                                                            <label class="form-check-label">
                                                                <input type="radio" class="form-input-styled" name="pele" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRPele'] == 'DE' ? 'checked' : ''; ?> >
                                                                Descamativa
                                                            </label>
                                                        </div>
                                                        <div class="form-check form-check-inline mr-4">
                                                            <label class="form-check-label">
                                                                <input type="radio" class="form-input-styled" name="pele" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRPele'] == 'ER' ? 'checked' : ''; ?> >
                                                                Eritema Tóxico
                                                            </label>
                                                        </div>
                                                        <div class="form-check form-check-inline mr-4">
                                                            <label class="form-check-label">
                                                                <input type="radio" class="form-input-styled" name="pele" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRPele'] == 'EN' ? 'checked' : ''; ?> >
                                                                Enrugada
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <textarea id="textPele" name="textPele" class="form-control" onInput="contarCaracteres(this);" rows="4" cols="4" maxLength="30" placeholder="" ><?php echo isset($iAtendimentoAdmissaoRN) ? $rowAdmissao['EnAdRPeleDescricao'] : ''; ?></textarea>
                                                        <small class="text-muted form-text">Max. 30 caracteres<span class="caracterestextPele"></span></small>                              
                                                       
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-lg-6">

                                                <div class="card-header header-elements-inline" style="margin-left: -20px;">
                                                    <h3 class="card-title font-weight-bold" >Reflexos Normais</h3>
                                                </div>
                                                
                                                <div class="col-lg-12">
                                                    <div class="row">
                                                        <div class="form-check form-check-inline mr-4 ">
                                                            <label class="form-check-label">
                                                                <input type="checkbox" class="form-check-input" name="succaoR" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRReflexoSuccao'] == '1' ? 'checked' : ''; ?> >
                                                                Sucção
                                                            </label>
                                                        </div>

                                                        <div class="form-check form-check-inline mr-4 ">
                                                            <label class="form-check-label">
                                                                <input type="checkbox" class="form-check-input" name="moro" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRReflexoMoro'] == '1' ? 'checked' : ''; ?>  >
                                                                Moro
                                                            </label>
                                                        </div>

                                                        <div class="form-check form-check-inline mr-4 ">
                                                            <label class="form-check-label">
                                                                <input type="checkbox" class="form-check-input" name="preensaoPalmar" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRReflexoPreensaoPalmar'] == '1' ? 'checked' : ''; ?>  >
                                                                Preensão Palmar
                                                            </label>
                                                        </div>
                                                        <div class="form-check form-check-inline mr-4 ">
                                                            <label class="form-check-label">
                                                                <input type="checkbox" class="form-check-input" name="pressaoPlantar" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRReflexoPressaoPlantar'] == '1' ? 'checked' : ''; ?>  >
                                                                Pressão Plantar
                                                            </label>
                                                        </div>                                                        
                                                    </div>
                                                    <div class="row">
                                                        <textarea id="textReflexos" name="textReflexos" class="form-control" onInput="contarCaracteres(this);" rows="4" cols="4" maxLength="30" placeholder="" ></textarea>
                                                        <small class="text-muted form-text">Max. 30 caracteres<span class="caracterestextReflexos"></span></small>   
                                                    </div>
                                                </div>
                                                

                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-8">

                                                <div class="card-header header-elements-inline" style="margin-left: -20px;">
                                                    <h3 class="card-title font-weight-bold" >Cabeça</h3>
                                                </div>

                                                <div class="col-lg-12">
                                                    <div class="row">
                                                        <div class="form-check form-check-inline mr-4 ">
                                                            <label class="form-check-label">
                                                                <input type="checkbox" class="form-check-input" name="escoriacoes" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRCabecaEscoriacao'] == '1' ? 'checked' : ''; ?> >
                                                                Escoriações
                                                            </label>
                                                        </div>
                                                        <div class="form-check form-check-inline mr-4 ">
                                                            <label class="form-check-label">
                                                                <input type="checkbox" class="form-check-input" name="pig" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRCabecaPIG'] == '1' ? 'checked' : ''; ?> >
                                                                PIG
                                                            </label>
                                                        </div>
                                                        <div class="form-check form-check-inline mr-4 ">
                                                            <label class="form-check-label">
                                                                <input type="checkbox" class="form-check-input" name="gig" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRCabecaGIG'] == '1' ? 'checked' : ''; ?> >
                                                                GIG
                                                            </label>
                                                        </div>
                                                        <div class="form-check form-check-inline mr-4 ">
                                                            <label class="form-check-label">
                                                                <input type="checkbox" class="form-check-input" name="bossa" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRCabecaBossa'] == '1' ? 'checked' : ''; ?>  >
                                                                BOSSA
                                                            </label>
                                                        </div>  
                                                        <div class="form-check form-check-inline mr-4 ">
                                                            <label class="form-check-label">
                                                                <input type="checkbox" class="form-check-input" name="Cefalohematoma" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRCabecaCefalohematoma'] == '1' ? 'checked' : ''; ?>  >
                                                                Cefalohematoma
                                                            </label>
                                                        </div>
                                                        <div class="form-check form-check-inline mr-4 ">
                                                            <label class="form-check-label">
                                                                <input type="checkbox" class="form-check-input" name="mascaraEquimotica" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRCabecaMascaraEquimotica'] == '1' ? 'checked' : ''; ?>  >
                                                                Máscara Equimótica
                                                            </label>
                                                        </div>                                                      
                                                    </div>

                                                    <div class="row">
                                                        <textarea id="textCabeca" name="textCabeca" class="form-control" onInput="contarCaracteres(this);" rows="4" cols="4" maxLength="50" placeholder="" ></textarea>
                                                        <small class="text-muted form-text">Max. 50 caracteres<span class="caracterestextCabeca"></span></small>   
                                                    </div>
                                                </div>
                                                

                                            </div>

                                            <div class="col-lg-4">
                                                <div class="card-header header-elements-inline" style="margin-left: -10px;">
                                                    <h3 class="card-title font-weight-bold" >Abdome</h3>
                                                </div>

                                                <div class="col-lg-12">
                                                    <select id="abdome" name="abdome" class="select-search">
                                                        <option value=''>selecione</option>
                                                        <option value='IN' <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRAbdome'] == 'IN' ? 'selected' : ''; ?> >ÍNTEGRO</option>
                                                        <option value='FL' <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRAbdome'] == 'FL' ? 'selected' : ''; ?> >FLÁCIDO</option>
                                                        <option value='GL' <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRAbdome'] == 'GL' ? 'selected' : ''; ?> >GLOBOSO</option>
                                                        <option value='DI' <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRAbdome'] == 'DI' ? 'selected' : ''; ?> >DISTENTIDO</option>
                                                        <option value='TI' <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRAbdome'] == 'TI' ? 'selected' : ''; ?> >TIMPÂNICO</option>
                                                    </select>
                                                </div>

                                                <div class="card-header header-elements-inline" style="margin-left: -10px;">
                                                    <h3 class="card-title font-weight-bold" >Sucção Satisfatória</h3>
                                                </div>

                                                <div class="col-lg-12">
                                                    <select id="succao" name="succao" class="select-search">
                                                        <option value=''>selecione</option>
                                                        <option value='S' <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRSuccaoSatisfatoria'] == '1' ? 'selected' : ''; ?> >SIM</option>
                                                        <option value='N' <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRSuccaoSatisfatoria'] == '0' ? 'selected' : ''; ?> >NÃO</option>
                                                    </select> 
                                                </div>                                              
                                            </div>

                                        </div>


                                        <div class="row">
                                            <div class="col-lg-6">
                                                <div class="card-header header-elements-inline" style="margin-left: -20px;">
                                                    <h3 class="card-title font-weight-bold" >Padrão Respiratório</h3>
                                                </div>

                                                <div class="col-lg-12">
                                                    <div class="row" id="box-pele" >
                                                        <div class="form-check form-check-inline mr-4">
                                                            <label class="form-check-label">
                                                                <input type="radio" class="form-input-styled" name="padraoRespiratorio" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRPadraoRespiratorio'] == 'EU' ? 'checked' : ''; ?> >
                                                                Eupnéico
                                                            </label>
                                                        </div>
                                                        <div class="form-check form-check-inline mr-4">
                                                            <label class="form-check-label"> 
                                                                <input type="radio" class="form-input-styled" name="padraoRespiratorio" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRPadraoRespiratorio'] == 'TA' ? 'checked' : ''; ?> >
                                                                Taquipnéia
                                                            </label>
                                                        </div>
                                                        <div class="form-check form-check-inline mr-4">
                                                            <label class="form-check-label">
                                                                <input type="radio" class="form-input-styled" name="padraoRespiratorio" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRPadraoRespiratorio'] == 'BR' ? 'checked' : ''; ?> >
                                                                Bradipnéia
                                                            </label>
                                                        </div>
                                                        <div class="form-check form-check-inline mr-4">
                                                            <label class="form-check-label">
                                                                <input type="radio" class="form-input-styled" name="padraoRespiratorio" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRPadraoRespiratorio'] == 'ON' ? 'checked' : ''; ?> >
                                                                Obestrução Nasal
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <textarea id="textPadraoRespiratorio" name="textPadraoRespiratorio" onInput="contarCaracteres(this);" class="form-control" rows="4" cols="4" maxLength="30" placeholder="" ><?php echo isset($iAtendimentoAdmissaoRN) ? $rowAdmissao['EnAdRPadraoRespiratorioDescricao'] : ''; ?></textarea>
                                                        <small class="text-muted form-text">Max. 30 caracteres<span class="caracterestextPadraoRespiratorio"></span></small>   
                                                    </div>
                                                </div>                      
                                            </div>

                                            <div class="col-lg-6">
                                                <div class="card-header header-elements-inline" style="margin-left: -20px;">
                                                    <h3 class="card-title font-weight-bold" >Genturinário</h3>
                                                </div>

                                                <div class="col-lg-12">
                                                    <div class="row">
                                                        <div class="form-check form-check-inline mr-4 ">
                                                            <label class="form-check-label">
                                                                <input type="checkbox" class="form-check-input" name="integro" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRGenturinarioIntegro'] == '1' ? 'checked' : ''; ?>  >
                                                                Íntegro
                                                            </label>
                                                        </div>
                                                        <div class="form-check form-check-inline mr-4 ">
                                                            <label class="form-check-label">
                                                                <input type="checkbox" class="form-check-input" name="diurese" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRGenturinarioDiurese'] == '1' ? 'checked' : ''; ?>  >
                                                                Diurese
                                                            </label>
                                                        </div>
                                                        <div class="form-check form-check-inline mr-4 ">
                                                            <label class="form-check-label">
                                                                <input type="checkbox" class="form-check-input" name="anusPervio" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRGenturinarioAnusPervio'] == '1' ? 'checked' : ''; ?>   >
                                                                Ânus Pérvio
                                                            </label>
                                                        </div>
                                                        <div class="form-check form-check-inline mr-4 ">
                                                            <label class="form-check-label">
                                                                <input type="checkbox" class="form-check-input" name="Mec1io" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRGenturinarioMeconio'] == '1' ? 'checked' : ''; ?>   >
                                                                Mecônio
                                                            </label>
                                                        </div> 
                                                        <div class="form-check form-check-inline mr-4 ">
                                                            <label class="form-check-label">
                                                                <input type="checkbox" class="form-check-input" name="outros" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRGenturinarioOutro'] == '1' ? 'checked' : ''; ?>  >
                                                                Outros
                                                            </label>
                                                        </div>                                                       
                                                    </div>
                                                    <div class="row">
                                                        <textarea id="textGenturinario" name="textGenturinario" onInput="contarCaracteres(this);" class="form-control" rows="4" cols="4" maxLength="30" placeholder="" ><?php echo isset($iAtendimentoAdmissaoRN) ? $rowAdmissao['EnAdRGenturinarioDescricao'] : ''; ?></textarea>
                                                        <small class="text-muted form-text">Max. 30 caracteres<span class="caracterestextGenturinario"></span></small>   
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
                                                            <input type="checkbox" class="form-check-input" name="limpoSeco" id="checkCotoLimpoSeco"  <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRCotoLimpoSeco'] == '1' ? 'checked' : ''; ?> >
                                                            Limpo e Seco
                                                        </label>
                                                    </div>

                                                    <div class="form-check form-check-inline mr-4 ">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input" name="gelatinoso" id="checkCotoGelatinoso" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRCotoGelatinoso'] == '1' ? 'checked' : ''; ?> >
                                                            Gelatinoso
                                                        </label>
                                                    </div>

                                                    <div class="form-check form-check-inline mr-4 ">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input" name="mumificado" id="checkCotoMumificado" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRCotoMumificado'] == '1' ? 'checked' : ''; ?> >
                                                            Mumificado
                                                        </label>
                                                    </div>
                                                    <div class="form-check form-check-inline mr-4 ">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input" name="umido" id="checkCotoUmido" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRCotoUmido'] == '1' ? 'checked' : ''; ?> >
                                                            Úmido
                                                        </label>
                                                    </div>
                                                    <div class="form-check form-check-inline mr-4 ">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input" name="sujo" id="checkCotoSujo" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRCotoSujo'] == '1' ? 'checked' : ''; ?> >
                                                            Sujo
                                                        </label>
                                                    </div>
                                                    <div class="form-check form-check-inline mr-4 ">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input" name="fetido" id="checkCotoFetido" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRCotoFetido'] == '1' ? 'checked' : ''; ?> >
                                                            Fétido
                                                        </label>
                                                    </div>
                                                    <div class="form-check form-check-inline mr-4">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input" name="hiperemia" id="checkCotoFetido" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRCotoHiperemia'] == '1' ? 'checked' : ''; ?> >
                                                            Hiperemia
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <textarea id="textCotoUmbilical" name="textCotoUmbilical" onInput="contarCaracteres(this);" class="form-control" rows="4" cols="4" maxLength="30" placeholder="" ><?php echo isset($iAtendimentoAdmissaoRN) ? $rowAdmissao['EnAdRCotoDescricao'] : ''; ?></textarea>
                                                    <small class="text-muted form-text">Max. 30 caracteres<span class="caracterestextCotoUmbilical"></span></small>   
                                                </div>
                                            </div>
                                            
                                        </div>



                                        <div class="row col-lg-6">
                                            <div class="col-lg-6" style="margin-left: -10px;">
                                                <div class="card-header header-elements-inline" style="margin-left: -20px;">
                                                    <h3 class="card-title font-weight-bold">Cateter</h3>
                                                </div>

                                                <div class="form-group" >
                                                    <div class="form-check form-check-inline">
                                                        <label class="form-check-label">
                                                            <input type="radio" class="form-input-styled" name="cateter" class="cateter" value="SIM" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRCateter'] == '1' ? 'checked' : ''; ?> >
                                                            Sim
                                                        </label>
                                                    </div>

                                                    <div class="form-check form-check-inline">
                                                        <label class="form-check-label">
                                                            <input type="radio" class="form-input-styled" name="cateter" class="cateter" value="NÃO" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRCateter'] == '0' ? 'checked' : ''; ?> >
                                                            Não
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="col-lg-12">
                                                    <textarea id="textCateter" name="textCateter" class="form-control" onInput="contarCaracteres(this);" rows="4" cols="4" maxLength="30" placeholder="" ><?php echo isset($iAtendimentoAdmissaoRN) ? $rowAdmissao['EnAdRCateterDescricao'] : ''; ?></textarea>
                                                    <small class="text-muted form-text">Max. 30 caracteres<span class="caracterestextCateter"></span></small>
                                                </div>

                                            </div>

                                            <div class="col-lg-6">
                                                <div class="card-header header-elements-inline" style="margin-left: -20px;">
                                                    <h3 class="card-title font-weight-bold">Sonda</h3>
                                                </div>

                                                <div class="form-group" >
                                                    <div class="form-check form-check-inline">
                                                        <label class="form-check-label">
                                                            <input type="radio" class="form-input-styled" name="sonda" class="sonda" value="SIM" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRSonda'] == '1' ? 'checked' : ''; ?> >
                                                            Sim
                                                        </label>
                                                    </div>

                                                    <div class="form-check form-check-inline">
                                                        <label class="form-check-label">
                                                            <input type="radio" class="form-input-styled" name="sonda" class="sonda" value="NÃO" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRSonda'] == '0' ? 'checked' : ''; ?> >
                                                            Não
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="col-lg-12">
                                                    <textarea id="textSonda" name="textSonda" class="form-control" onInput="contarCaracteres(this);" rows="4" cols="4" maxLength="30" placeholder="" ><?php echo isset($iAtendimentoAdmissaoRN) ? $rowAdmissao['EnAdRSondaDescricao'] : ''; ?></textarea>
                                                    <small class="text-muted form-text">Max. 30 caracteres<span class="caracterestextSonda"></span></small>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- xxxxxxxxxxxxxxxxxxxxxxxxxxx -->

                                    </div>
                                </div>
                            </div>

                            <div class="card p-3">

                                <fieldset class=" col-lg-12 row fieldset-border " >
                                    <legend class="legend-border font-weight-bold">Diagnóstico de Enfermagem</legend>
                                    
                                    <div class="col-lg-4">
                                        <div class="form-group">                                        

                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <input type="checkbox" name="dorAguda" class="form-check-input" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRDiagnosticoDorAguda'] == '1' ? 'checked' : ''; ?> >
                                                    DOR AGUDA
                                                </label>
                                            </div>

                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <input type="checkbox" name="riscoIctericia" class="form-check-input" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRDiagnosticoRiscoIctericia'] == '1' ? 'checked' : ''; ?> >
                                                    RISCO DE ICTERICIA
                                                </label>
                                            </div>

                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <input type="checkbox" name="nutricaoDesequilibrada" class="form-check-input" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRDiagnosticoNutricaoDesequilibrada'] == '1' ? 'checked' : ''; ?> >
                                                    NUTRIÇÃO DESEQUILIBRADA QUE AS NECESSIDADES CORPORAIS
                                                </label>
                                            </div>

                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <input type="checkbox" name="riscoSufocacao" class="form-check-input" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRDiagnosticoRiscoSufocacao'] == '1' ? 'checked' : ''; ?> >
                                                    RISCO DE SUFOCAÇÃO
                                                </label>
                                            </div>

                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">

                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <input type="checkbox" name="riscoGlicemia" class="form-check-input" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRDiagnosticoRiscoGlicemia'] == '1' ? 'checked' : ''; ?> >
                                                    RISCO DE GLICEMIA
                                                </label>
                                            </div>

                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <input type="checkbox" name="eliminacaoUrinaria" class="form-check-input" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRDiagnosticoEliminacaoUrinaria'] == '1' ? 'checked' : ''; ?> >
                                                    ELIMINAÇÃO URINÁRIA
                                                </label>
                                            </div>

                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <input type="checkbox" name="riscoIntegridade" class="form-check-input" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRDiagnosticoRiscoIntegridade'] == '1' ? 'checked' : ''; ?> >
                                                    RISCO DE INTEGRIDADE
                                                </label>
                                            </div>

                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <input type="checkbox" name="padraoSono" class="form-check-input" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRDiagnosticoPadraoSono'] == '1' ? 'checked' : ''; ?> >
                                                    PADRÃO DE SONO
                                                </label>
                                            </div>

                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <input type="checkbox" name="riscoConstipacao" class="form-check-input" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRDiagnosticoRiscoConstipacao'] == '1' ? 'checked' : ''; ?> >
                                                    RISCO DE CONSTIPAÇÃO
                                                </label>
                                            </div>

                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">

                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <input type="checkbox" name="deficitAutoCuidado" class="form-check-input" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRDiagnosticoDeficitAutoCuidado'] == '1' ? 'checked' : ''; ?> >
                                                    DÉFICIT NO AUTO-CUIDADO
                                                </label>
                                            </div>

                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <input type="checkbox" name="riscoInfeccao" class="form-check-input" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRDiagnosticoRiscoInfeccao'] == '1' ? 'checked' : ''; ?> >
                                                    RISCO DE INFECÇÃO
                                                </label>
                                            </div>

                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <input type="checkbox" name="padraoRespiratorio" class="form-check-input" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRDiagnosticoPadraoRespiratorio'] == '1' ? 'checked' : ''; ?> >
                                                    PADRÃO RESPIRATÓRIO
                                                </label>
                                            </div>

                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <input type="checkbox" name="termorregulacao" class="form-check-input" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRDiagnosticoTermoRregulacao'] == '1' ? 'checked' : ''; ?> >
                                                    TERMORREGULAÇÃO
                                                </label>
                                            </div>

                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <input type="checkbox" name="termorregulacao" class="form-check-input" <?php if (isset($iAtendimentoAdmissaoRN )) echo $rowAdmissao['EnAdRDiagnosticoOutro'] == '1' ? 'checked' : ''; ?> >
                                                    OUTROS
                                                </label>
                                                <input name="riscoOutrosText" type="text" class="form-control" placeholder="Descrição Outros..." value=""/>

                                            </div>

                                        </div>
                                    </div>                                    
                                    
                                </fieldset> 

                                <div class="row">
                                    <div class="card-header header-elements-inline" style="margin-left: -10px;">
                                        <h3 class="card-title font-weight-bold">Avaliação de Enfermagem</h3>
                                    </div>

                                    <div class="col-lg-12">
                                        <textarea id="textAvaliacao" name="textAvaliacao" onInput="contarCaracteres(this);" class="form-control" rows="4" cols="4" maxLength="100" placeholder="" ><?php echo isset($iAtendimentoAdmissaoRN) ? $rowAdmissao['EnAdRAvaliacaoEnfermagem'] : ''; ?></textarea>
                                        <small class="text-muted form-text">Max. 100 caracteres<span class="caracterestextAvaliacao"></span></small>   
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