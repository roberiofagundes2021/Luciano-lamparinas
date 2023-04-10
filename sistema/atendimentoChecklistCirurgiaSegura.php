<?php 

    include_once("sessao.php"); 

    $_SESSION['PaginaAtual'] = 'Checklist Cirurgia Segura';

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
    FROM EnfermagemChecklistCirurgiaSegura
    WHERE EnCCSAtendimento = $iAtendimentoId
    ORDER BY EnCCSId DESC";
    $result = $conn->query($sql);
    $rowCheckList = $result->fetch(PDO::FETCH_ASSOC);

    $iAtendimentoCheckList = $rowCheckList?$rowCheckList['EnCCSId']:null;
    
    //var_dump($iAtendimentoCheckList);die;

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

    $sql = "SELECT P.ProfiId,P.ProfiNome,PFS.ProfiCbo,PFS.ProfiNome as profissao
    FROM Profissional P
    JOIN Profissao PFS ON PFS.ProfiId = P.ProfiProfissao
    WHERE P.ProfiUnidade = $_SESSION[UnidadeId]";
    $result = $conn->query($sql);
    $rowProfissionais = $result->fetchAll(PDO::FETCH_ASSOC);

    if(isset($_POST['inputInicio'])){

        if ($iAtendimentoCheckList) {

            $sql = "UPDATE EnfermagemChecklistCirurgiaSegura SET
                EnCCSAtendimento = :EnCCSAtendimento , 
                EnCCSDataInicio = :EnCCSDataInicio , 
                EnCCSHoraInicio = :EnCCSHoraInicio , 
                EnCCSDataFim = :EnCCSDataFim , 
                EnCCSHoraFim = :EnCCSHoraFim , 
                EnCCSPrevisaoAlta = :EnCCSPrevisaoAlta , 
                EnCCSTipoInternacao = :EnCCSTipoInternacao , 
                EnCCSEspecialidadeLeito = :EnCCSEspecialidadeLeito , 
                EnCCSAla = :EnCCSAla , 
                EnCCSQuarto = :EnCCSQuarto , 
                EnCCSLeito = :EnCCSLeito , 
                EnCCSProfissional = :EnCCSProfissional , 
                EnCCSPas = :EnCCSPas , 
                EnCCSPad = :EnCCSPad , 
                EnCCSFreqCardiaca = :EnCCSFreqCardiaca , 
                EnCCSFreqRespiratoria = :EnCCSFreqRespiratoria , 
                EnCCSTemperatura = :EnCCSTemperatura , 
                EnCCSSPO = :EnCCSSPO , 
                EnCCSHGT = :EnCCSHGT , 
                EnCCSPeso = :EnCCSPeso , 
                EnCCSConfirmacaoPaciente = :EnCCSConfirmacaoPaciente , 
                EnCCSVerificacaoSegurancaoAnestesica = :EnCCSVerificacaoSegurancaoAnestesica , 
                EnCCSVerificacaoSegurancaoAnestesicaDescricao = :EnCCSVerificacaoSegurancaoAnestesicaDescricao , 
                EnCCSViaAereaDificil = :EnCCSViaAereaDificil , 
                EnCCSViaAereaDificilDescricao = :EnCCSViaAereaDificilDescricao , 
                EnCCSAcessoVenosoAdequado = :EnCCSAcessoVenosoAdequado , 
                EnCCSAcessoVenosoAdequadoDescricao = :EnCCSAcessoVenosoAdequadoDescricao , 
                EnCCSAlergia = :EnCCSAlergia , 
                EnCCSAlergiaDescricao = :EnCCSAlergiaDescricao , 
                EnCCSProfilaxia = :EnCCSProfilaxia , 
                EnCCSProfilaxiaDescricao = :EnCCSProfilaxiaDescricao , 
                EnCCSRiscoPerdaSanguinia = :EnCCSRiscoPerdaSanguinia , 
                EnCCSRiscoPerdaSanguiniaDescricao = :EnCCSRiscoPerdaSanguiniaDescricao , 
                EnCCSMontagemSala = :EnCCSMontagemSala , 
                EnCCSMontagemSalaDescricao = :EnCCSMontagemSalaDescricao , 
                EnCCSConfirmacaoChecklistEquipe = :EnCCSConfirmacaoChecklistEquipe , 
                EnCCSPlacaEletrocauterioPosicionada = :EnCCSPlacaEletrocauterioPosicionada , 
                EnCCSPlacaEletrocauterioPosicionadaDescricao = :EnCCSPlacaEletrocauterioPosicionadaDescricao , 
                EnCCSExameImagemDisponivel = :EnCCSExameImagemDisponivel , 
                EnCCSExameImagemDisponivelDescricao = :EnCCSExameImagemDisponivelDescricao , 
                EnCCSRevisaoMedicaPontoCritico = :EnCCSRevisaoMedicaPontoCritico , 
                EnCCSRevisaoMedicaPontoCriticoDescricao = :EnCCSRevisaoMedicaPontoCriticoDescricao , 
                EnCCSContagemCompressa = :EnCCSContagemCompressa , 
                EnCCSContagemCompressaDescricao = :EnCCSContagemCompressaDescricao , 
                EnCCSPecaAnatomica = :EnCCSPecaAnatomica , 
                EnCCSPecaAnatomicaDescricao = :EnCCSPecaAnatomicaDescricao , 
                EnCCSRegistroCompleto = :EnCCSRegistroCompleto , 
                EnCCSRegistroCompletoDescricao = :EnCCSRegistroCompletoDescricao , 
                EnCCSRecomendacaoPosAnestesico = :EnCCSRecomendacaoPosAnestesico , 
                EnCCSRecomendacaoPosAnestesicoDescricao = :EnCCSRecomendacaoPosAnestesicoDescricao , 
                EnCCSFixacaoEtiqueta = :EnCCSFixacaoEtiqueta , 
                EnCCSFixacaoEtiquetaDescricao = :EnCCSFixacaoEtiquetaDescricao , 
                EnCCSObservacaoPreOperatorio = :EnCCSObservacaoPreOperatorio , 
                EnCCSRecomendacaoCirurgiao = :EnCCSRecomendacaoCirurgiao , 
                EnCCSRecomendacaoAnestesista = :EnCCSRecomendacaoAnestesista , 
                EnCCSRecomendacaoEnfermagem = :EnCCSRecomendacaoEnfermagem , 
                EnCCSUnidade = :EnCCSUnidade
                WHERE EnCCSId = :iAtendimentoCheckList";
                
            $result = $conn->prepare($sql);
                    
            $result->execute(array(
                ':EnCCSAtendimento' => $iAtendimentoId,
                ':EnCCSDataInicio' => date('Y-m-d'),
                ':EnCCSHoraInicio' => date('H:i'),
                ':EnCCSDataFim' => date('Y-m-d'),
                ':EnCCSHoraFim' => date('H:i'),           
                ':EnCCSPrevisaoAlta' => '',
                ':EnCCSTipoInternacao' => $row['TpIntId'],
                ':EnCCSEspecialidadeLeito' => $row['EsLeiId'],
                ':EnCCSAla' => $row['AlaId'],
                ':EnCCSQuarto' => $row['QuartId'],
                ':EnCCSLeito' => $row['LeitoId'],
                ':EnCCSProfissional' => $userId,
                ':EnCCSPas' => $_POST['inputSistolica'] == "" ? null : $_POST['inputSistolica'],
                ':EnCCSPad' => $_POST['inputDiatolica'] == "" ? null : $_POST['inputDiatolica'],
                ':EnCCSFreqCardiaca' => $_POST['inputCardiaca'] == "" ? null : $_POST['inputCardiaca'],
                ':EnCCSFreqRespiratoria' => $_POST['inputRespiratoria'] == "" ? null : $_POST['inputRespiratoria'],
                ':EnCCSTemperatura' => $_POST['inputTemperatura'] == "" ? null : $_POST['inputTemperatura'],
                ':EnCCSSPO' => $_POST['inputSPO'] == "" ? null : $_POST['inputSPO'],
                ':EnCCSHGT' => $_POST['inputHGT'] == "" ? null : $_POST['inputHGT'],
                ':EnCCSPeso' => $_POST['inputPeso'] == "" ? null : $_POST['inputPeso'],
                ':EnCCSConfirmacaoPaciente' => isset($_POST['cmbConfirmacaoPaciente'])?$_POST['cmbConfirmacaoPaciente']:null, 
                ':EnCCSVerificacaoSegurancaoAnestesica' => isset($_POST['verificacaoSegurancaAnestesica'])?$_POST['verificacaoSegurancaAnestesica']:null , 
                ':EnCCSVerificacaoSegurancaoAnestesicaDescricao' => isset($_POST['verificacaoSegurancaAnestesicaDescricao'])?$_POST['verificacaoSegurancaAnestesicaDescricao']:null , 
                ':EnCCSViaAereaDificil' => isset($_POST['viaAereaDificil'])?$_POST['viaAereaDificil']:null , 
                ':EnCCSViaAereaDificilDescricao' => isset($_POST['viaAereaDificilDescricao'])?$_POST['viaAereaDificilDescricao']:null , 
                ':EnCCSAcessoVenosoAdequado' => isset($_POST['acessoVenosoAdequado'])?$_POST['acessoVenosoAdequado']:null , 
                ':EnCCSAcessoVenosoAdequadoDescricao' => isset($_POST['acessoVenosoAdequadoDescricao'])?$_POST['acessoVenosoAdequadoDescricao']:null , 
                ':EnCCSAlergia' => isset($_POST['alergiasCS'])?$_POST['alergiasCS']:null , 
                ':EnCCSAlergiaDescricao' => isset($_POST['alergiasCSDescricao'])?$_POST['alergiasCSDescricao']:null , 
                ':EnCCSProfilaxia' => isset($_POST['profilaxia'])?$_POST['profilaxia']:null , 
                ':EnCCSProfilaxiaDescricao' => isset($_POST['profilaxiaDescricao'])?$_POST['profilaxiaDescricao']:null , 
                ':EnCCSRiscoPerdaSanguinia' => isset($_POST['riscoPerdaSanguinia'])?$_POST['riscoPerdaSanguinia']:null , 
                ':EnCCSRiscoPerdaSanguiniaDescricao' => isset($_POST['riscoPerdaSanguiniaDescricao'])?$_POST['riscoPerdaSanguiniaDescricao']:null , 
                ':EnCCSMontagemSala' => isset($_POST['montagemSala'])?$_POST['montagemSala']:null , 
                ':EnCCSMontagemSalaDescricao' => isset($_POST['montagemSalaDescricao'])?$_POST['montagemSalaDescricao']:null , 
                ':EnCCSConfirmacaoChecklistEquipe' => isset($_POST['cmbConfirmacaoChecklistEquipe'])?$_POST['cmbConfirmacaoChecklistEquipe']:null , 
                ':EnCCSPlacaEletrocauterioPosicionada' => isset($_POST['placaEletrocauterioPosicionada'])?$_POST['placaEletrocauterioPosicionada']:null , 
                ':EnCCSPlacaEletrocauterioPosicionadaDescricao' => isset($_POST['placaEletrocauterioPosicionadaDescricao'])?$_POST['placaEletrocauterioPosicionadaDescricao']:null , 
                ':EnCCSExameImagemDisponivel' => isset($_POST['exameImagemDisponivel'])?$_POST['exameImagemDisponivel']:null , 
                ':EnCCSExameImagemDisponivelDescricao' => isset($_POST['exameImagemDisponivelDescricao'])?$_POST['exameImagemDisponivelDescricao']:null , 
                ':EnCCSRevisaoMedicaPontoCritico' => isset($_POST['revisaoMedicaPontoCritico'])?$_POST['revisaoMedicaPontoCritico']:null , 
                ':EnCCSRevisaoMedicaPontoCriticoDescricao' => isset($_POST['revisaoMedicaPontoCriticoDescricao'])?$_POST['revisaoMedicaPontoCriticoDescricao']:null , 
                ':EnCCSContagemCompressa' => isset($_POST['contagemCompressa'])?$_POST['contagemCompressa']:null , 
                ':EnCCSContagemCompressaDescricao' => isset($_POST['contagemCompressaDescricao'])?$_POST['contagemCompressaDescricao']:null , 
                ':EnCCSPecaAnatomica' => isset($_POST['pecaAnatomica'])?$_POST['pecaAnatomica']:null , 
                ':EnCCSPecaAnatomicaDescricao' => isset($_POST['pecaAnatomicaDescricao'])?$_POST['pecaAnatomicaDescricao']:null , 
                ':EnCCSRegistroCompleto' => isset($_POST['registroCompleto'])?$_POST['registroCompleto']:null , 
                ':EnCCSRegistroCompletoDescricao' => isset($_POST['registroCompletoDescricao'])?$_POST['registroCompletoDescricao']:null , 
                ':EnCCSRecomendacaoPosAnestesico' => isset($_POST['recomendacaoPosAnestesico'])?$_POST['recomendacaoPosAnestesico']:null , 
                ':EnCCSRecomendacaoPosAnestesicoDescricao' => isset($_POST['recomendacaoPosAnestesicoDescricao'])?$_POST['recomendacaoPosAnestesicoDescricao']:null , 
                ':EnCCSFixacaoEtiqueta' => isset($_POST['fixacaoEtiqueta'])?$_POST['fixacaoEtiqueta']:null , 
                ':EnCCSFixacaoEtiquetaDescricao' => isset($_POST['fixacaoEtiquetaDescricao'])?$_POST['fixacaoEtiquetaDescricao']:null , 
                ':EnCCSObservacaoPreOperatorio' => isset($_POST['inputObservacaoPreOperatorio'])?$_POST['inputObservacaoPreOperatorio']:null , 
                ':EnCCSRecomendacaoCirurgiao' => isset($_POST['inputRecomendacaoCirurgiao'])?$_POST['inputRecomendacaoCirurgiao']:null , 
                ':EnCCSRecomendacaoAnestesista' => isset($_POST['inputRecomendacaoAnestesista'])?$_POST['inputRecomendacaoAnestesista']:null , 
                ':EnCCSRecomendacaoEnfermagem' => isset($_POST['inputRecomendacaoEnfermagem'])?$_POST['inputRecomendacaoEnfermagem']:null , 
                ':EnCCSUnidade' => $_SESSION['UnidadeId'],
                ':iAtendimentoCheckList' => $iAtendimentoCheckList 
            ));

            //lógica para tabela 'EnfermagemChecklistCirurgiaXEquipe'
            if (isset($_POST['cmbApresentacaoEquipe'])) {

                //se estiver editando, deleta todos os registros com o id do CheckList e insere dnv os que estão selecionados
                if ($iAtendimentoCheckList) {
                    $sql = "DELETE FROM EnfermagemChecklistCirurgiaXEquipe WHERE  ECXEqChecklistCirurgiaSegura = '$iAtendimentoCheckList'";
                    $conn->query($sql);
                }

                foreach ($_POST['cmbApresentacaoEquipe'] as $idProfissional) {

                    $sql = "SELECT (COUNT(ECXEqId) + 1) as CONTADOR FROM EnfermagemChecklistCirurgiaXEquipe";
                    $result = $conn->query($sql);
		            $count = $result->fetch(PDO::FETCH_ASSOC);

                    $sql = "INSERT INTO EnfermagemChecklistCirurgiaXEquipe 
                        ( ECXEqId , ECXEqChecklistCirurgiaSegura , ECXEqProfissional , ECXEqUnidade ) 
                    VALUES 
                        ( :ECXEqId , :ECXEqChecklistCirurgiaSegura , :ECXEqProfissional , :ECXEqUnidade )";
                    $result = $conn->prepare($sql);

                    $result->execute(array(
                        ':ECXEqId' => $count['CONTADOR'],
                        ':ECXEqChecklistCirurgiaSegura' =>  $iAtendimentoCheckList,
                        ':ECXEqProfissional' => $idProfissional,
                        ':ECXEqUnidade' => $_SESSION['UnidadeId']
                    ));
                }
            } else {

                //se estiver editando, deleta todos os registros com o id do CheckList e insere dnv os que estão selecionados
                if ($iAtendimentoCheckList) {
                    $sql = "DELETE FROM EnfermagemChecklistCirurgiaXEquipe WHERE  ECXEqChecklistCirurgiaSegura = '$iAtendimentoCheckList'";
                    $conn->query($sql);
                }

            }

                $_SESSION['iAtendimentoId'] = $iAtendimentoId;
                $_SESSION['msg']['titulo'] = "Sucesso";
                $_SESSION['msg']['mensagem'] = "Checklist alterada com sucesso!!!";
                $_SESSION['msg']['tipo'] = "success";

        } else {

            $sql = "INSERT INTO EnfermagemChecklistCirurgiaSegura (
                EnCCSAtendimento , 
                EnCCSDataInicio , 
                EnCCSHoraInicio , 
                EnCCSDataFim , 
                EnCCSHoraFim , 
                EnCCSPrevisaoAlta , 
                EnCCSTipoInternacao , 
                EnCCSEspecialidadeLeito , 
                EnCCSAla , 
                EnCCSQuarto , 
                EnCCSLeito , 
                EnCCSProfissional , 
                EnCCSPas , 
                EnCCSPad , 
                EnCCSFreqCardiaca , 
                EnCCSFreqRespiratoria , 
                EnCCSTemperatura , 
                EnCCSSPO , 
                EnCCSHGT , 
                EnCCSPeso , 
                EnCCSConfirmacaoPaciente , 
                EnCCSVerificacaoSegurancaoAnestesica , 
                EnCCSVerificacaoSegurancaoAnestesicaDescricao , 
                EnCCSViaAereaDificil , 
                EnCCSViaAereaDificilDescricao , 
                EnCCSAcessoVenosoAdequado , 
                EnCCSAcessoVenosoAdequadoDescricao , 
                EnCCSAlergia , 
                EnCCSAlergiaDescricao , 
                EnCCSProfilaxia , 
                EnCCSProfilaxiaDescricao , 
                EnCCSRiscoPerdaSanguinia , 
                EnCCSRiscoPerdaSanguiniaDescricao , 
                EnCCSMontagemSala , 
                EnCCSMontagemSalaDescricao , 
                EnCCSConfirmacaoChecklistEquipe , 
                EnCCSPlacaEletrocauterioPosicionada , 
                EnCCSPlacaEletrocauterioPosicionadaDescricao , 
                EnCCSExameImagemDisponivel , 
                EnCCSExameImagemDisponivelDescricao , 
                EnCCSRevisaoMedicaPontoCritico , 
                EnCCSRevisaoMedicaPontoCriticoDescricao , 
                EnCCSContagemCompressa , 
                EnCCSContagemCompressaDescricao , 
                EnCCSPecaAnatomica , 
                EnCCSPecaAnatomicaDescricao , 
                EnCCSRegistroCompleto , 
                EnCCSRegistroCompletoDescricao , 
                EnCCSRecomendacaoPosAnestesico , 
                EnCCSRecomendacaoPosAnestesicoDescricao , 
                EnCCSFixacaoEtiqueta , 
                EnCCSFixacaoEtiquetaDescricao , 
                EnCCSObservacaoPreOperatorio , 
                EnCCSRecomendacaoCirurgiao , 
                EnCCSRecomendacaoAnestesista , 
                EnCCSRecomendacaoEnfermagem , 
                EnCCSUnidade
            ) VALUES (
                :EnCCSAtendimento , 
                :EnCCSDataInicio , 
                :EnCCSHoraInicio , 
                :EnCCSDataFim , 
                :EnCCSHoraFim , 
                :EnCCSPrevisaoAlta , 
                :EnCCSTipoInternacao , 
                :EnCCSEspecialidadeLeito , 
                :EnCCSAla , 
                :EnCCSQuarto , 
                :EnCCSLeito , 
                :EnCCSProfissional , 
                :EnCCSPas , 
                :EnCCSPad , 
                :EnCCSFreqCardiaca , 
                :EnCCSFreqRespiratoria , 
                :EnCCSTemperatura , 
                :EnCCSSPO , 
                :EnCCSHGT , 
                :EnCCSPeso , 
                :EnCCSConfirmacaoPaciente , 
                :EnCCSVerificacaoSegurancaoAnestesica , 
                :EnCCSVerificacaoSegurancaoAnestesicaDescricao , 
                :EnCCSViaAereaDificil , 
                :EnCCSViaAereaDificilDescricao , 
                :EnCCSAcessoVenosoAdequado , 
                :EnCCSAcessoVenosoAdequadoDescricao , 
                :EnCCSAlergia , 
                :EnCCSAlergiaDescricao , 
                :EnCCSProfilaxia , 
                :EnCCSProfilaxiaDescricao , 
                :EnCCSRiscoPerdaSanguinia , 
                :EnCCSRiscoPerdaSanguiniaDescricao , 
                :EnCCSMontagemSala , 
                :EnCCSMontagemSalaDescricao , 
                :EnCCSConfirmacaoChecklistEquipe , 
                :EnCCSPlacaEletrocauterioPosicionada , 
                :EnCCSPlacaEletrocauterioPosicionadaDescricao , 
                :EnCCSExameImagemDisponivel , 
                :EnCCSExameImagemDisponivelDescricao , 
                :EnCCSRevisaoMedicaPontoCritico , 
                :EnCCSRevisaoMedicaPontoCriticoDescricao , 
                :EnCCSContagemCompressa , 
                :EnCCSContagemCompressaDescricao , 
                :EnCCSPecaAnatomica , 
                :EnCCSPecaAnatomicaDescricao , 
                :EnCCSRegistroCompleto , 
                :EnCCSRegistroCompletoDescricao , 
                :EnCCSRecomendacaoPosAnestesico , 
                :EnCCSRecomendacaoPosAnestesicoDescricao , 
                :EnCCSFixacaoEtiqueta , 
                :EnCCSFixacaoEtiquetaDescricao , 
                :EnCCSObservacaoPreOperatorio , 
                :EnCCSRecomendacaoCirurgiao , 
                :EnCCSRecomendacaoAnestesista , 
                :EnCCSRecomendacaoEnfermagem , 
                :EnCCSUnidade
                ) ";
            $result = $conn->prepare($sql);

            $result->execute(array(
                ':EnCCSAtendimento' => $iAtendimentoId,
                ':EnCCSDataInicio' => date('Y-m-d'),
                ':EnCCSHoraInicio' => date('H:i'),
                ':EnCCSDataFim' => date('Y-m-d'),
                ':EnCCSHoraFim' => date('H:i'),
                
                ':EnCCSPrevisaoAlta' => '',
                ':EnCCSTipoInternacao' => $row['TpIntId'],
                ':EnCCSEspecialidadeLeito' => $row['EsLeiId'],
                ':EnCCSAla' => $row['AlaId'],
                ':EnCCSQuarto' => $row['QuartId'],
                ':EnCCSLeito' => $row['LeitoId'],
                ':EnCCSProfissional' => $userId,
                ':EnCCSPas' => $_POST['inputSistolica'] == "" ? null : $_POST['inputSistolica'],
                ':EnCCSPad' => $_POST['inputDiatolica'] == "" ? null : $_POST['inputDiatolica'],
                ':EnCCSFreqCardiaca' => $_POST['inputCardiaca'] == "" ? null : $_POST['inputCardiaca'],
                ':EnCCSFreqRespiratoria' => $_POST['inputRespiratoria'] == "" ? null : $_POST['inputRespiratoria'],
                ':EnCCSTemperatura' => $_POST['inputTemperatura'] == "" ? null : $_POST['inputTemperatura'],
                ':EnCCSSPO' => $_POST['inputSPO'] == "" ? null : $_POST['inputSPO'],
                ':EnCCSHGT' => $_POST['inputHGT'] == "" ? null : $_POST['inputHGT'],
                ':EnCCSPeso' => $_POST['inputPeso'] == "" ? null : $_POST['inputPeso'],

                ':EnCCSConfirmacaoPaciente' => isset($_POST['cmbConfirmacaoPaciente'])?$_POST['cmbConfirmacaoPaciente']:null, 
                ':EnCCSVerificacaoSegurancaoAnestesica' => isset($_POST['verificacaoSegurancaAnestesica'])?$_POST['verificacaoSegurancaAnestesica']:null , 
                ':EnCCSVerificacaoSegurancaoAnestesicaDescricao' => isset($_POST['verificacaoSegurancaAnestesicaDescricao'])?$_POST['verificacaoSegurancaAnestesicaDescricao']:null , 
                ':EnCCSViaAereaDificil' => isset($_POST['viaAereaDificil'])?$_POST['viaAereaDificil']:null , 
                ':EnCCSViaAereaDificilDescricao' => isset($_POST['viaAereaDificilDescricao'])?$_POST['viaAereaDificilDescricao']:null , 
                ':EnCCSAcessoVenosoAdequado' => isset($_POST['acessoVenosoAdequado'])?$_POST['acessoVenosoAdequado']:null , 
                ':EnCCSAcessoVenosoAdequadoDescricao' => isset($_POST['acessoVenosoAdequadoDescricao'])?$_POST['acessoVenosoAdequadoDescricao']:null , 
                ':EnCCSAlergia' => isset($_POST['alergiasCS'])?$_POST['alergiasCS']:null , 
                ':EnCCSAlergiaDescricao' => isset($_POST['alergiasCSDescricao'])?$_POST['alergiasCSDescricao']:null , 
                ':EnCCSProfilaxia' => isset($_POST['profilaxia'])?$_POST['profilaxia']:null , 
                ':EnCCSProfilaxiaDescricao' => isset($_POST['profilaxiaDescricao'])?$_POST['profilaxiaDescricao']:null , 
                ':EnCCSRiscoPerdaSanguinia' => isset($_POST['riscoPerdaSanguinia'])?$_POST['riscoPerdaSanguinia']:null , 
                ':EnCCSRiscoPerdaSanguiniaDescricao' => isset($_POST['riscoPerdaSanguiniaDescricao'])?$_POST['riscoPerdaSanguiniaDescricao']:null , 
                ':EnCCSMontagemSala' => isset($_POST['montagemSala'])?$_POST['montagemSala']:null , 
                ':EnCCSMontagemSalaDescricao' => isset($_POST['montagemSalaDescricao'])?$_POST['montagemSalaDescricao']:null , 
                ':EnCCSConfirmacaoChecklistEquipe' => isset($_POST['cmbConfirmacaoChecklistEquipe'])?$_POST['cmbConfirmacaoChecklistEquipe']:null , 
                ':EnCCSPlacaEletrocauterioPosicionada' => isset($_POST['placaEletrocauterioPosicionada'])?$_POST['placaEletrocauterioPosicionada']:null , 
                ':EnCCSPlacaEletrocauterioPosicionadaDescricao' => isset($_POST['placaEletrocauterioPosicionadaDescricao'])?$_POST['placaEletrocauterioPosicionadaDescricao']:null , 
                ':EnCCSExameImagemDisponivel' => isset($_POST['exameImagemDisponivel'])?$_POST['exameImagemDisponivel']:null , 
                ':EnCCSExameImagemDisponivelDescricao' => isset($_POST['exameImagemDisponivelDescricao'])?$_POST['exameImagemDisponivelDescricao']:null , 
                ':EnCCSRevisaoMedicaPontoCritico' => isset($_POST['revisaoMedicaPontoCritico'])?$_POST['revisaoMedicaPontoCritico']:null , 
                ':EnCCSRevisaoMedicaPontoCriticoDescricao' => isset($_POST['revisaoMedicaPontoCriticoDescricao'])?$_POST['revisaoMedicaPontoCriticoDescricao']:null , 
                ':EnCCSContagemCompressa' => isset($_POST['contagemCompressa'])?$_POST['contagemCompressa']:null , 
                ':EnCCSContagemCompressaDescricao' => isset($_POST['contagemCompressaDescricao'])?$_POST['contagemCompressaDescricao']:null , 
                ':EnCCSPecaAnatomica' => isset($_POST['pecaAnatomica'])?$_POST['pecaAnatomica']:null , 
                ':EnCCSPecaAnatomicaDescricao' => isset($_POST['pecaAnatomicaDescricao'])?$_POST['pecaAnatomicaDescricao']:null , 
                ':EnCCSRegistroCompleto' => isset($_POST['registroCompleto'])?$_POST['registroCompleto']:null , 
                ':EnCCSRegistroCompletoDescricao' => isset($_POST['registroCompletoDescricao'])?$_POST['registroCompletoDescricao']:null , 
                ':EnCCSRecomendacaoPosAnestesico' => isset($_POST['recomendacaoPosAnestesico'])?$_POST['recomendacaoPosAnestesico']:null , 
                ':EnCCSRecomendacaoPosAnestesicoDescricao' => isset($_POST['recomendacaoPosAnestesicoDescricao'])?$_POST['recomendacaoPosAnestesicoDescricao']:null , 
                ':EnCCSFixacaoEtiqueta' => isset($_POST['fixacaoEtiqueta'])?$_POST['fixacaoEtiqueta']:null , 
                ':EnCCSFixacaoEtiquetaDescricao' => isset($_POST['fixacaoEtiquetaDescricao'])?$_POST['fixacaoEtiquetaDescricao']:null , 
                ':EnCCSObservacaoPreOperatorio' => isset($_POST['inputObservacaoPreOperatorio'])?$_POST['inputObservacaoPreOperatorio']:null , 
                ':EnCCSRecomendacaoCirurgiao' => isset($_POST['inputRecomendacaoCirurgiao'])?$_POST['inputRecomendacaoCirurgiao']:null , 
                ':EnCCSRecomendacaoAnestesista' => isset($_POST['inputRecomendacaoAnestesista'])?$_POST['inputRecomendacaoAnestesista']:null , 
                ':EnCCSRecomendacaoEnfermagem' => isset($_POST['inputRecomendacaoEnfermagem'])?$_POST['inputRecomendacaoEnfermagem']:null , 
                ':EnCCSUnidade' => $_SESSION['UnidadeId']

            ));

            $idLastChecklistCirurgiaSegura = $conn->lastInsertId();


            //lógica para tabela 'EnfermagemChecklistCirurgiaXEquipe'
            if (isset($_POST['cmbApresentacaoEquipe']) ) {

                //se estiver editando, deleta todos os registros com o id do CheckList e insere dnv os que estão selecionados
                if ($iAtendimentoCheckList) {
                    $sql = "DELETE FROM EnfermagemChecklistCirurgiaXEquipe WHERE  ECXEqChecklistCirurgiaSegura = '$iAtendimentoCheckList'";
                    $conn->query($sql);
                }

                foreach ($_POST['cmbApresentacaoEquipe'] as $idProfissional) {

                    $sql = "SELECT (COUNT(ECXEqId) + 1) as CONTADOR FROM EnfermagemChecklistCirurgiaXEquipe";
                    $result = $conn->query($sql);
		            $count = $result->fetch(PDO::FETCH_ASSOC);

                    $sql = "INSERT INTO EnfermagemChecklistCirurgiaXEquipe 
                        ( ECXEqId , ECXEqChecklistCirurgiaSegura , ECXEqProfissional , ECXEqUnidade ) 
                    VALUES 
                        ( :ECXEqId , :ECXEqChecklistCirurgiaSegura , :ECXEqProfissional , :ECXEqUnidade )";
                    $result = $conn->prepare($sql);

                    $result->execute(array(
                        ':ECXEqId' => $count['CONTADOR'],
                        ':ECXEqChecklistCirurgiaSegura' =>  $idLastChecklistCirurgiaSegura,
                        ':ECXEqProfissional' => $idProfissional,
                        ':ECXEqUnidade' => $_SESSION['UnidadeId']
                    ));
                }
            }
            
            $_SESSION['iAtendimentoId'] = $iAtendimentoId;
            $_SESSION['msg']['titulo'] = "Sucesso";
            $_SESSION['msg']['mensagem'] = "Checklist inserida com sucesso!!!";
            $_SESSION['msg']['tipo'] = "success";	

        }

        $_SESSION['iAtendimentoId'] = $iAtendimentoId;
        
    }
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Checklist Cirurgia Segura</title>

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

            $('.salvarChecklist').on('click', function(e){
				e.preventDefault();	
				$( "#formAtendimentoChecklist" ).submit();
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
                    checkList()
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

        function selecionaverificacaoSegurancaAnestesica(tipo) {
            if (tipo == 1){
                document.getElementById('verificacaoSegurancaAnestesicaDescricaoViwer').style.display = "block";	
            } else {			
                document.getElementById('verificacaoSegurancaAnestesicaDescricaoViwer').style.display = "none";		
            }
        }
        function selecionaviaAereaDificil(tipo) {
            if (tipo == 'SIM'){
                document.getElementById('viaAereaDificilDescricaoViwer').style.display = "block";	
            } else {			
                document.getElementById('viaAereaDificilDescricaoViwer').style.display = "none";		
            }
        }
        function selecionaacessoVenosoAdequado(tipo) {
            if (tipo == 1){
                document.getElementById('acessoVenosoAdequadoDescricaoViwer').style.display = "block";	
            } else {			
                document.getElementById('acessoVenosoAdequadoDescricaoViwer').style.display = "none";		
            }
        }
        function selecionaalergiasCS(tipo) {
            if (tipo == 'SIM'){
                document.getElementById('alergiasCSDescricaoViwer').style.display = "block";	
            } else {			
                document.getElementById('alergiasCSDescricaoViwer').style.display = "none";		
            }
        }
        function selecionaprofilaxia(tipo) {
            if (tipo == 1){
                document.getElementById('profilaxiaDescricaoViwer').style.display = "block";	
            } else {			
                document.getElementById('profilaxiaDescricaoViwer').style.display = "none";		
            }
        }
        function selecionariscoPerdaSanguinia(tipo) {
            if (tipo == 1){
                document.getElementById('riscoPerdaSanguiniaDescricaoViwer').style.display = "block";	
            } else {			
                document.getElementById('riscoPerdaSanguiniaDescricaoViwer').style.display = "none";		
            }
        }
        function selecionamontagemSala(tipo) {
            if (tipo == 1){
                document.getElementById('montagemSalaDescricaoViwer').style.display = "block";	
            } else {			
                document.getElementById('montagemSalaDescricaoViwer').style.display = "none";		
            }
        }
        function selecionaexameImagemDisponivel(tipo) {
            if (tipo == 'SIM'){
                document.getElementById('exameImagemDisponivelDescricaoViwer').style.display = "block";	
            } else {			
                document.getElementById('exameImagemDisponivelDescricaoViwer').style.display = "none";		
            }
        }
        function selecionarevisaoMedicaPontoCritico(tipo) {
            if (tipo == 'SIM'){
                document.getElementById('revisaoMedicaPontoCriticoDescricaoViwer').style.display = "block";	
            } else {			
                document.getElementById('revisaoMedicaPontoCriticoDescricaoViwer').style.display = "none";		
            }
        }
        function selecionaplacaEletrocauterioPosicionada(tipo) {
            if (tipo == 'SIM'){
                document.getElementById('placaEletrocauterioPosicionadaDescricaoViwer').style.display = "block";	
            } else {			
                document.getElementById('placaEletrocauterioPosicionadaDescricaoViwer').style.display = "none";		
            }
        }
        function selecionacontagemCompressa(tipo) {
            if (tipo == 'SIM'){
                document.getElementById('contagemCompressaDescricaoViwer').style.display = "block";	
            } else {			
                document.getElementById('contagemCompressaDescricaoViwer').style.display = "none";		
            }
        }
        function selecionapecaAnatomica(tipo) {
            if (tipo == 1){
                document.getElementById('pecaAnatomicaDescricaoViwer').style.display = "block";	
            } else {			
                document.getElementById('pecaAnatomicaDescricaoViwer').style.display = "none";		
            }
        }
        function selecionaregistroCompleto(tipo) {
            if (tipo == 1){
                document.getElementById('registroCompletoDescricaoViwer').style.display = "block";	
            } else {			
                document.getElementById('registroCompletoDescricaoViwer').style.display = "none";		
            }
        }
        function selecionarecomendacaoPosAnestesico(tipo) {
            if (tipo == 1){
                document.getElementById('recomendacaoPosAnestesicoDescricaoViwer').style.display = "block";	
            } else {			
                document.getElementById('recomendacaoPosAnestesicoDescricaoViwer').style.display = "none";		
            }
        }
        function selecionafixacaoEtiqueta(tipo) {
            if (tipo == 1){
                document.getElementById('fixacaoEtiquetaDescricaoViwer').style.display = "block";	
            } else {			
                document.getElementById('fixacaoEtiquetaDescricaoViwer').style.display = "none";		
            }
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
						<form name="formAtendimentoChecklist" id="formAtendimentoChecklist" method="post">
							<?php
								echo "<input type='hidden' id='iAtendimentoId' name='iAtendimentoId' value='$iAtendimentoId' />";
							?>
							<div class="card">

                            <div class="col-md-12">
                                <div class="row">

                                    <div class="col-md-6" style="text-align: left;">

                                        <div class="card-header header-elements-inline">
                                            <h3 class="card-title font-weight-bold">CHECKLIST CIRURGIA SEGURA</h3>
                                        </div>
            
                                    </div>

                                    <div class="col-md-6" style="text-align: right;">

                                        <div class="form-group" style="margin:20px;" >
                                            <button class="btn btn-lg btn-success mr-1 salvarChecklist" >Salvar</button>
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
                                    <h3 class="card-title font-weight-bold">Checks - Indução Anestésica</h3>

                                    <div class="header-elements">
                                        <div class="list-icons">
                                            <a class="list-icons-item" data-action="collapse"></a>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-body row">  

                                    <div class="col-lg-12 mb-3 row">

                                        <div class="col-lg-4">                                            
                                            <div class="form-group">
                                                <label for="cmbConfirmacaoPaciente">Confirmação sobre o Paciente</label>
                                                <select id="cmbConfirmacaoPaciente" name="cmbConfirmacaoPaciente" class="form-control-select2" >
                                                    <option value="">Selecione</option>
                                                    <option value='1' <?php if (isset($iAtendimentoCheckList )) echo $rowCheckList['EnCCSConfirmacaoPaciente'] == '1' ? 'selected' : ''; ?> >SIM</option>
                                                    <option value='0' <?php if (isset($iAtendimentoCheckList )) echo $rowCheckList['EnCCSConfirmacaoPaciente'] == '0' ? 'selected' : ''; ?> >NÃO</option>
                                                </select>
                                            </div>                                           
                                        </div>

                                        <div class="col-lg-4">
                                            <label>
                                                Verificação de segurança anestésica                                            
                                            </label>
                                            <div class="col-lg-12 row options">
                                                <div class="col-lg-3 form-check form-check-inline">
                                                    <label class="form-check-label">
                                                        <input type="radio" name="verificacaoSegurancaAnestesica" class="verificacaoSegurancaAnestesica form-input-styled" placeholder="" value="1" onclick="selecionaverificacaoSegurancaAnestesica('1')" <?php if (isset($iAtendimentoCheckList )) echo $rowCheckList['EnCCSVerificacaoSegurancaoAnestesica'] == '1' ? 'checked' : ''; ?> >
                                                        SIM
                                                    </label>
                                                </div>
                                                <div class="col-lg-3 form-check form-check-inline">
                                                    <label class="form-check-label">
                                                        <input type="radio" name="verificacaoSegurancaAnestesica" class="verificacaoSegurancaAnestesica form-input-styled" placeholder="" value="0" onclick="selecionaverificacaoSegurancaAnestesica('0')" <?php if (isset($iAtendimentoCheckList )) echo $rowCheckList['EnCCSVerificacaoSegurancaoAnestesica'] == '0' ? 'checked' : ''; ?> >
                                                        NÃO
                                                    </label>
                                                </div>
                                            </div>
                                            <div id="verificacaoSegurancaAnestesicaDescricaoViwer" style=" display: <?php if (isset($iAtendimentoCheckList)) { echo $rowCheckList['EnCCSVerificacaoSegurancaoAnestesica'] == '1' ? 'block' : 'none'; } else { echo 'none'; } ?>" >
                                                <textarea id="verificacaoSegurancaAnestesicaDescricao" name="verificacaoSegurancaAnestesicaDescricao" onInput="contarCaracteres(this);" class="form-control" rows="4" cols="4" maxLength="150" placeholder="" ><?php echo isset($iAtendimentoCheckList) ? $rowCheckList['EnCCSVerificacaoSegurancaoAnestesicaDescricao'] : ''; ?></textarea>
                                                <small class="text-muted form-text">
                                                    Máx. 150 caracteres 
                                                    <span class="caracteresverificacaoSegurancaAnestesicaDescricao"></span>
                                                </small>
                                            </div>
                                        </div>

                                        <div class="col-lg-4">
                                            <label>
                                                Via aérea difícil/Risco de aspiração
                                            </label>
                                            <div class="col-lg-12 row options">
                                                <div class="col-lg-3 form-check form-check-inline" style="margin-right: 2px;">
                                                    <label class="form-check-label">
                                                        <input type="radio" name="viaAereaDificil" class="viaAereaDificil form-input-styled" placeholder="" value="SIM" onclick="selecionaviaAereaDificil('SIM')" <?php if (isset($iAtendimentoCheckList )) echo $rowCheckList['EnCCSViaAereaDificil'] == 'SIM' ? 'checked' : ''; ?> >
                                                        SIM
                                                    </label>
                                                </div>
                                                <div class="col-lg-3 form-check form-check-inline" style="margin-right: 2px;">
                                                    <label class="form-check-label">
                                                        <input type="radio" name="viaAereaDificil" class="viaAereaDificil form-input-styled" placeholder="" value="NAO" onclick="selecionaviaAereaDificil('NAO')" <?php if (isset($iAtendimentoCheckList )) echo $rowCheckList['EnCCSViaAereaDificil'] == 'NAO' ? 'checked' : ''; ?> >
                                                        NÃO
                                                    </label>
                                                </div>
                                                <div class="col-lg-6 row form-check form-check-inline" style="margin-right: 2px;">
                                                    <label class="form-check-label">
                                                        <input type="radio" name="viaAereaDificil" class="viaAereaDificil form-input-styled" placeholder="" value="NSA" onclick="selecionaviaAereaDificil('NSA')" <?php if (isset($iAtendimentoCheckList )) echo $rowCheckList['EnCCSViaAereaDificil'] == 'NSA' ? 'checked' : ''; ?> >
                                                        NÃO SE APLICA
                                                    </label>
                                                </div>
                                            </div>
                                            <div id="viaAereaDificilDescricaoViwer" style=" display: <?php if (isset($iAtendimentoCheckList)) { echo $rowCheckList['EnCCSViaAereaDificil'] == 'SIM' ? 'block' : 'none'; } else { echo 'none'; } ?>" >
                                                <textarea id="viaAereaDificilDescricao" name="viaAereaDificilDescricao" onInput="contarCaracteres(this);" class="form-control" rows="4" cols="4" maxLength="150" placeholder="" ><?php echo isset($iAtendimentoCheckList) ? $rowCheckList['EnCCSViaAereaDificilDescricao'] : ''; ?></textarea>
                                                <small class="text-muted form-text">
                                                    Máx. 150 caracteres 
                                                    <span class="caracteresviaAereaDificilDescricao"></span>
                                                </small>
                                            </div>
                                        </div>
                                        
                                    </div>

                                    <div class="col-lg-12 mb-3 row">

                                        <div class="col-lg-4">                                            
                                            <label>
                                                Acesso venoso adequado e pérvio
                                            </label>
                                            <div class="col-lg-12 row options">
                                                <div class="col-lg-3 form-check form-check-inline" style="margin-right: 2px;">
                                                    <label class="form-check-label">
                                                        <input type="radio" name="acessoVenosoAdequado" class="acessoVenosoAdequado form-input-styled" placeholder="" value="1" onclick="selecionaacessoVenosoAdequado('1')" <?php if (isset($iAtendimentoCheckList )) echo $rowCheckList['EnCCSAcessoVenosoAdequado'] == '1' ? 'checked' : ''; ?> >
                                                        SIM
                                                    </label>
                                                </div>
                                                <div class="col-lg-3 form-check form-check-inline" style="margin-right: 2px;">
                                                    <label class="form-check-label">
                                                        <input type="radio" name="acessoVenosoAdequado" class="acessoVenosoAdequado form-input-styled" placeholder="" value="0" onclick="selecionaacessoVenosoAdequado('0')" <?php if (isset($iAtendimentoCheckList )) echo $rowCheckList['EnCCSAcessoVenosoAdequado'] == '0' ? 'checked' : ''; ?> >
                                                        NÃO
                                                    </label>
                                                </div>
                                            </div>
                                            <div id="acessoVenosoAdequadoDescricaoViwer" style=" display: <?php if (isset($iAtendimentoCheckList)) { echo $rowCheckList['EnCCSAcessoVenosoAdequado'] == '1' ? 'block' : 'none'; } else { echo 'none'; } ?>" >
                                                <textarea id="acessoVenosoAdequadoDescricao" name="acessoVenosoAdequadoDescricao" onInput="contarCaracteres(this);" class="form-control" rows="4" cols="4" maxLength="150" placeholder="" ><?php echo isset($iAtendimentoCheckList) ? $rowCheckList['EnCCSAcessoVenosoAdequadoDescricao'] : ''; ?></textarea>
                                                <small class="text-muted form-text">
                                                    Máx. 150 caracteres 
                                                    <span class="caracteresacessoVenosoAdequadoDescricao"></span>
                                                </small>
                                            </div>                                            
                                        </div>

                                        <div class="col-lg-4">
                                            <label>
                                                Alergias                                                
                                            </label>
                                            <div class="col-lg-12 row options">
                                                <div class="col-lg-3 form-check form-check-inline" style="margin-right: 2px;">
                                                    <label class="form-check-label">
                                                        <input type="radio" name="alergiasCS" class="alergiasCS form-input-styled" placeholder="" value="SIM" onclick="selecionaalergiasCS('SIM')" <?php if (isset($iAtendimentoCheckList )) echo $rowCheckList['EnCCSAlergia'] == 'SIM' ? 'checked' : ''; ?> >
                                                        SIM
                                                    </label>
                                                </div>
                                                <div class="col-lg-3 form-check form-check-inline" style="margin-right: 2px;">
                                                    <label class="form-check-label">
                                                        <input type="radio" name="alergiasCS" class="alergiasCS form-input-styled" placeholder="" value="NAO" onclick="selecionaalergiasCS('NAO')"  <?php if (isset($iAtendimentoCheckList )) echo $rowCheckList['EnCCSAlergia'] == 'NAO' ? 'checked' : ''; ?> >
                                                        NÃO
                                                    </label>
                                                </div>
                                                <div class="col-lg-6 row form-check form-check-inline" style="margin-right: 2px;">
                                                    <label class="form-check-label">
                                                        <input type="radio" name="alergiasCS" class="alergiasCS form-input-styled" placeholder="" value="NIN" onclick="selecionaalergiasCS('NIN')"  <?php if (isset($iAtendimentoCheckList )) echo $rowCheckList['EnCCSAlergia'] == 'NIN' ? 'checked' : ''; ?> >
                                                        NÃO INFORMADO
                                                    </label>
                                                </div>
                                            </div>
                                            <div id="alergiasCSDescricaoViwer" style=" display: <?php if (isset($iAtendimentoCheckList)) { echo $rowCheckList['EnCCSAlergia'] == 'SIM' ? 'block' : 'none'; } else { echo 'none'; } ?>" >
                                                <textarea id="alergiasCSDescricao" name="alergiasCSDescricao" onInput="contarCaracteres(this);" class="form-control" rows="4" cols="4" maxLength="150" placeholder="" ><?php echo isset($iAtendimentoCheckList) ? $rowCheckList['EnCCSAlergiaDescricao'] : ''; ?></textarea>
                                                <small class="text-muted form-text">
                                                    Máx. 150 caracteres
                                                    <span class="caracteresalergiasCSDescricao"></span>
                                                </small>
                                            </div>
                                        </div>

                                        <div class="col-lg-4">
                                            <label>
                                                Profilaxia antimicrobiana (< 60min)
                                            </label>
                                            <div class="col-lg-12 row options">
                                                <div class="col-lg-3 form-check form-check-inline">
                                                    <label class="form-check-label">
                                                        <input type="radio" name="profilaxia" class="profilaxia form-input-styled" placeholder="" value="1" onclick="selecionaprofilaxia('1')" <?php if (isset($iAtendimentoCheckList )) echo $rowCheckList['EnCCSProfilaxia'] == '1' ? 'checked' : ''; ?> >
                                                        SIM
                                                    </label>
                                                </div>
                                                <div class="col-lg-3 form-check form-check-inline">
                                                    <label class="form-check-label">
                                                        <input type="radio" name="profilaxia" class="profilaxia form-input-styled" placeholder="" value="0" onclick="selecionaprofilaxia('0')" <?php if (isset($iAtendimentoCheckList )) echo $rowCheckList['EnCCSProfilaxia'] == '0' ? 'checked' : ''; ?> >
                                                        NÃO
                                                    </label>
                                                </div>
                                            </div>
                                            <div id="profilaxiaDescricaoViwer" style=" display: <?php if (isset($iAtendimentoCheckList)) { echo $rowCheckList['EnCCSProfilaxia'] == '1' ? 'block' : 'none'; } else { echo 'none'; } ?>" >
                                                <textarea id="profilaxiaDescricao" name="profilaxiaDescricao" onInput="contarCaracteres(this);" class="form-control" rows="4" cols="4" maxLength="150" placeholder="" ><?php echo isset($iAtendimentoCheckList) ? $rowCheckList['EnCCSProfilaxiaDescricao'] : ''; ?></textarea>
                                                <small class="text-muted form-text">
                                                    Máx. 150 caracteres
                                                    <span class="caracteresprofilaxiaDescricao"></span>
                                                </small>
                                            </div>
                                        </div>
                                        
                                    </div>

                                    <div class="col-lg-12 mb-3 row">

                                        <div class="col-lg-4">                                            
                                            <label>
                                                Risco de perda sanguínea <br>
                                                (>500mL - Adulto / 7mL/Kg em Crianças)
                                            </label>
                                            <div class="col-lg-12 row options">
                                                <div class="col-lg-3 form-check form-check-inline">
                                                    <label class="form-check-label">
                                                        <input type="radio" name="riscoPerdaSanguinia" class="riscoPerdaSanguinia form-input-styled" placeholder="" value="1" onclick="selecionariscoPerdaSanguinia('1')" <?php if (isset($iAtendimentoCheckList )) echo $rowCheckList['EnCCSRiscoPerdaSanguinia'] == '1' ? 'checked' : ''; ?> >
                                                        SIM
                                                    </label>
                                                </div>
                                                <div class="col-lg-3 form-check form-check-inline">
                                                    <label class="form-check-label">
                                                        <input type="radio" name="riscoPerdaSanguinia" class="riscoPerdaSanguinia form-input-styled" placeholder="" value="0" onclick="selecionariscoPerdaSanguinia('0')" <?php if (isset($iAtendimentoCheckList )) echo $rowCheckList['EnCCSRiscoPerdaSanguinia'] == '0' ? 'checked' : ''; ?> >
                                                        NÃO
                                                    </label>
                                                </div>
                                            </div>                                           
                                            <div id="riscoPerdaSanguiniaDescricaoViwer" style=" display: <?php if (isset($iAtendimentoCheckList)) { echo $rowCheckList['EnCCSRiscoPerdaSanguinia'] == '1' ? 'block' : 'none'; } else { echo 'none'; } ?>" >
                                                <textarea id="riscoPerdaSanguiniaDescricao" name="riscoPerdaSanguiniaDescricao" onInput="contarCaracteres(this);" class="form-control" rows="4" cols="4" maxLength="150" placeholder="" ><?php echo isset($iAtendimentoCheckList) ? $rowCheckList['EnCCSRiscoPerdaSanguiniaDescricao'] : ''; ?></textarea>
                                                <small class="text-muted form-text">
                                                    Máx. 150 caracteres
                                                    <span class="caracteresriscoPerdaSanguiniaDescricao"></span>
                                                </small>
                                            </div>                                            
                                        </div>

                                        <div class="col-lg-4">
                                            <label>
                                                Montagem da sala de acordo com<br>
                                                o procedimento e riscos levantados                                                
                                            </label>
                                            <div class="col-lg-12 row options">
                                                <div class="col-lg-3 form-check form-check-inline">
                                                    <label class="form-check-label">
                                                        <input type="radio" name="montagemSala" class="montagemSala form-input-styled" placeholder="" value="1" onclick="selecionamontagemSala('1')" <?php if (isset($iAtendimentoCheckList )) echo $rowCheckList['EnCCSMontagemSala'] == '1' ? 'checked' : ''; ?> >
                                                        SIM
                                                    </label>
                                                </div>
                                                <div class="col-lg-3 form-check form-check-inline">
                                                    <label class="form-check-label">
                                                        <input type="radio" name="montagemSala" class="montagemSala form-input-styled" placeholder="" value="0" onclick="selecionamontagemSala('0')" <?php if (isset($iAtendimentoCheckList )) echo $rowCheckList['EnCCSMontagemSala'] == '0' ? 'checked' : ''; ?> >
                                                        NÃO
                                                    </label>
                                                </div>
                                            </div>
                                            <div id="montagemSalaDescricaoViwer" style=" display: <?php if (isset($iAtendimentoCheckList)) { echo $rowCheckList['EnCCSMontagemSala'] == '1' ? 'block' : 'none'; } else { echo 'none'; } ?>" >
                                                <textarea id="montagemSalaDescricao" name="montagemSalaDescricao" onInput="contarCaracteres(this);" class="form-control" rows="4" cols="4" maxLength="150" placeholder="" ><?php echo isset($iAtendimentoCheckList) ? $rowCheckList['EnCCSMontagemSalaDescricao'] : ''; ?></textarea>
                                                <small class="text-muted form-text">
                                                    Máx. 150 caracteres
                                                    <span class="caracteresmontagemSalaDescricao"></span>
                                                </small>
                                            </div>
                                        </div>

                                        
                                    </div>
                                    
                                
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-header header-elements-inline">
                                    <h3 class="card-title font-weight-bold">Checks - Equipe Cirúrgica</h3>

                                    <div class="header-elements">
                                        <div class="list-icons">
                                            <a class="list-icons-item" data-action="collapse"></a>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-body row"> 

                                    <div class="col-lg-12 row">

                                        <div class="col-lg-8 row">

                                            <div class="col-lg-12 mb-3 row">

                                                <div class="col-lg-6 ">
                                            
                                                    <div class="form-group">
                                                        <label for="cmbApresentacaoEquipe">Apresentação da Equipe (Nome e Função)</label>
                                                        <select id="cmbApresentacaoEquipe" name="cmbApresentacaoEquipe[]" class="multiselect-filtering" multiple="multiple" >
                                                            <?php          
                                                                
                                                                $array = [];                                                    
                                                            
                                                                if ($iAtendimentoCheckList) {

                                                                    $sql = "SELECT ECXEqProfissional FROM EnfermagemChecklistCirurgiaXEquipe WHERE ECXEqChecklistCirurgiaSegura = '$iAtendimentoCheckList'";
                                                                    $result = $conn->query($sql);
                                                                    $idProfissionalChecklist = $result->fetchAll(PDO::FETCH_ASSOC); 
                                                                   
                                                                    foreach ($idProfissionalChecklist as $item) {
                                                                        array_push($array, $item['ECXEqProfissional']);
                                                                    }
                                                                }

                                                                

                                                                foreach($rowProfissionais as $item){

                                                                    if ( isset($iAtendimentoCheckList) && in_array($item['ProfiId'], $array) ) {                                                                        
                                                                        echo "<option value='$item[ProfiId]' selected>$item[ProfiNome] - $item[profissao] - $item[ProfiCbo]</option>";
                                                                    } else {
                                                                        echo "<option value='$item[ProfiId]'>$item[ProfiNome] - $item[profissao] - $item[ProfiCbo]</option>";
                                                                    }      
                                                                }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-lg-6 ">
                                                    <div class="form-group">
                                                        <label for="cmbConfirmacaoChecklistEquipe">Confirmação do checklist pela Equipe</label>
                                                        <select id="cmbConfirmacaoChecklistEquipe" name="cmbConfirmacaoChecklistEquipe" class="form-control-select2" >
                                                            <option value="">Selecione</option>
                                                            <option value='1' <?php if (isset($iAtendimentoCheckList )) echo $rowCheckList['EnCCSConfirmacaoChecklistEquipe'] == '1' ? 'selected' : ''; ?> >SIM</option>
                                                            <option value='0' <?php if (isset($iAtendimentoCheckList )) echo $rowCheckList['EnCCSConfirmacaoChecklistEquipe'] == '0' ? 'selected' : ''; ?> >NÃO</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                
                                            </div>
                                            
                                            <div class="col-lg-12 mb-3 row">

                                                <div class="col-lg-6">

                                                    <label>Exames de imagem disponíveis </label>
                                                    <div class="col-lg-12 row options">
                                                        <div class="col-lg-3 form-check form-check-inline" style="margin-right: 2px;">
                                                            <label class="form-check-label">
                                                                <input type="radio" name="exameImagemDisponivel" class="exameImagemDisponivel form-input-styled" placeholder="" value="SIM" onclick="selecionaexameImagemDisponivel('SIM')" <?php if (isset($iAtendimentoCheckList )) echo $rowCheckList['EnCCSExameImagemDisponivel'] == 'SIM' ? 'checked' : ''; ?> >
                                                                SIM
                                                            </label>
                                                        </div>
                                                        <div class="col-lg-3 form-check form-check-inline" style="margin-right: 2px;">
                                                            <label class="form-check-label">
                                                                <input type="radio" name="exameImagemDisponivel" class="exameImagemDisponivel form-input-styled" placeholder="" value="NAO" onclick="selecionaexameImagemDisponivel('NAO')" <?php if (isset($iAtendimentoCheckList )) echo $rowCheckList['EnCCSExameImagemDisponivel'] == 'NAO' ? 'checked' : ''; ?> >
                                                                NÃO
                                                            </label>
                                                        </div>
                                                        <div class="col-lg-6 row form-check form-check-inline" style="margin-right: 2px;">
                                                            <label class="form-check-label">
                                                                <input type="radio" name="exameImagemDisponivel" class="exameImagemDisponivel form-input-styled" placeholder="" value="NSA" onclick="selecionaexameImagemDisponivel('NSA')" <?php if (isset($iAtendimentoCheckList )) echo $rowCheckList['EnCCSExameImagemDisponivel'] == 'NSA' ? 'checked' : ''; ?> >
                                                                NÃO SE APLICA
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div id="exameImagemDisponivelDescricaoViwer" style=" display: <?php if (isset($iAtendimentoCheckList)) { echo $rowCheckList['EnCCSExameImagemDisponivel'] == 'SIM' ? 'block' : 'none'; } else { echo 'none'; } ?>" >
                                                        <textarea id="exameImagemDisponivelDescricao" name="exameImagemDisponivelDescricao" onInput="contarCaracteres(this);" class="form-control" rows="4" cols="4" maxLength="150" placeholder="" ><?php echo isset($iAtendimentoCheckList) ? $rowCheckList['EnCCSExameImagemDisponivelDescricao'] : ''; ?></textarea>
                                                        <small class="text-muted form-text">
                                                            Máx. 150 caracteres
                                                            <span class="caracteresexameImagemDisponivelDescricao"></span>
                                                        </small>
                                                    </div> 

                                                </div>

                                                <div class="col-lg-6">

                                                    <label>
                                                        Revisão médica dos pontos críticos do  <br>
                                                        procedimento cirúrgico e anestésico
                                                    </label>
                                                    <div class="col-lg-12 row options">
                                                        <div class="col-lg-3 form-check form-check-inline" style="margin-right: 2px;">
                                                            <label class="form-check-label">
                                                                <input type="radio" name="revisaoMedicaPontoCritico" class="revisaoMedicaPontoCritico form-input-styled" placeholder="" value="SIM" onclick="selecionarevisaoMedicaPontoCritico('SIM')" <?php if (isset($iAtendimentoCheckList )) echo $rowCheckList['EnCCSRevisaoMedicaPontoCritico'] == 'SIM' ? 'checked' : ''; ?> >
                                                                SIM
                                                            </label>
                                                        </div>
                                                        <div class="col-lg-3 form-check form-check-inline" style="margin-right: 2px;">
                                                            <label class="form-check-label">
                                                                <input type="radio" name="revisaoMedicaPontoCritico" class="revisaoMedicaPontoCritico form-input-styled" placeholder="" value="NAO" onclick="selecionarevisaoMedicaPontoCritico('NAO')" <?php if (isset($iAtendimentoCheckList )) echo $rowCheckList['EnCCSRevisaoMedicaPontoCritico'] == 'NAO' ? 'checked' : ''; ?> >
                                                                NÃO
                                                            </label>
                                                        </div>
                                                        <div class="col-lg-6 row form-check form-check-inline" style="margin-right: 2px;">
                                                            <label class="form-check-label">
                                                                <input type="radio" name="revisaoMedicaPontoCritico" class="revisaoMedicaPontoCritico form-input-styled" placeholder="" value="NSA" onclick="selecionarevisaoMedicaPontoCritico('NSA')" <?php if (isset($iAtendimentoCheckList )) echo $rowCheckList['EnCCSRevisaoMedicaPontoCritico'] == 'NSA' ? 'checked' : ''; ?> >
                                                                NÃO SE APLICA
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div id="revisaoMedicaPontoCriticoDescricaoViwer" style=" display: <?php if (isset($iAtendimentoCheckList)) { echo $rowCheckList['EnCCSRevisaoMedicaPontoCritico'] == 'SIM' ? 'block' : 'none'; } else { echo 'none'; } ?>" >
                                                        <textarea id="revisaoMedicaPontoCriticoDescricao" name="revisaoMedicaPontoCriticoDescricao" onInput="contarCaracteres(this);" class="form-control" rows="4" cols="4" maxLength="150" placeholder="" ><?php echo isset($iAtendimentoCheckList) ? $rowCheckList['EnCCSRevisaoMedicaPontoCriticoDescricao'] : ''; ?></textarea>
                                                        <small class="text-muted form-text">
                                                            Máx. 150 caracteres
                                                            <span class="caracteresrevisaoMedicaPontoCriticoDescricao"></span>
                                                        </small>
                                                    </div>    

                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-4 m-0">

                                            <label>Placa de eletrocautério posicionada</label>
                                            <div class="col-lg-12 row options">
                                                <div class="col-lg-3 form-check form-check-inline" style="margin-right: 2px;">
                                                    <label class="form-check-label">
                                                        <input type="radio" name="placaEletrocauterioPosicionada" class="placaEletrocauterioPosicionada form-input-styled" placeholder="" value="SIM" onclick="selecionaplacaEletrocauterioPosicionada('SIM')" <?php if (isset($iAtendimentoCheckList )) echo $rowCheckList['EnCCSPlacaEletrocauterioPosicionada'] == 'SIM' ? 'checked' : ''; ?> >
                                                        SIM
                                                    </label>
                                                </div>
                                                <div class="col-lg-3 form-check form-check-inline" style="margin-right: 2px;">
                                                    <label class="form-check-label">
                                                        <input type="radio" name="placaEletrocauterioPosicionada" class="placaEletrocauterioPosicionada form-input-styled" placeholder="" value="NAO" onclick="selecionaplacaEletrocauterioPosicionada('NAO')" <?php if (isset($iAtendimentoCheckList )) echo $rowCheckList['EnCCSPlacaEletrocauterioPosicionada'] == 'NAO' ? 'checked' : ''; ?> >
                                                        NÃO
                                                    </label>
                                                </div>
                                                <div class="col-lg-6 row form-check form-check-inline" style="margin-right: 2px;">
                                                    <label class="form-check-label">
                                                        <input type="radio" name="placaEletrocauterioPosicionada" class="placaEletrocauterioPosicionada form-input-styled" placeholder="" value="NSA" onclick="selecionaplacaEletrocauterioPosicionada('NSA')" <?php if (isset($iAtendimentoCheckList )) echo $rowCheckList['EnCCSPlacaEletrocauterioPosicionada'] == 'NSA' ? 'checked' : ''; ?> >
                                                        NÃO SE APLICA
                                                    </label>
                                                </div>
                                            </div>
                                            <div id="placaEletrocauterioPosicionadaDescricaoViwer" style=" display: <?php if (isset($iAtendimentoCheckList)) { echo $rowCheckList['EnCCSPlacaEletrocauterioPosicionada'] == 'SIM' ? 'block' : 'none'; } else { echo 'none'; } ?>" >
                                                <textarea id="placaEletrocauterioPosicionadaDescricao" name="placaEletrocauterioPosicionadaDescricao" onInput="contarCaracteres(this);" class="form-control" rows="4" cols="4" maxLength="150" placeholder="" ><?php echo isset($iAtendimentoCheckList) ? $rowCheckList['EnCCSPlacaEletrocauterioPosicionadaDescricao'] : ''; ?></textarea>
                                                <small class="text-muted form-text">
                                                    Máx. 150 caracteres
                                                    <span class="caracteresplacaEletrocauterioPosicionadaDescricao"></span>
                                                </small>
                                            </div> 

                                        </div>
                                    </div>                                
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-header header-elements-inline">
                                    <h3 class="card-title font-weight-bold">Checks - Pós Cirúrgico</h3>

                                    <div class="header-elements">
                                        <div class="list-icons">
                                            <a class="list-icons-item" data-action="collapse"></a>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-body row">  
                                    
                                    <div class="col-lg-12 mb-3 row">

                                        <div class="col-lg-4">                                            
                                            <label>
                                                Contagem de Compressas, <br>
                                                Agulhas e Instrumentais
                                            </label>
                                            <div class="col-lg-12 row options">
                                                <div class="col-lg-3 form-check form-check-inline" style="margin-right: 2px;">
                                                    <label class="form-check-label">
                                                        <input type="radio" name="contagemCompressa" class="contagemCompressa form-input-styled" placeholder="" value="SIM" onclick="selecionacontagemCompressa('SIM')" <?php if (isset($iAtendimentoCheckList )) echo $rowCheckList['EnCCSContagemCompressa'] == 'SIM' ? 'checked' : ''; ?> >
                                                        SIM
                                                    </label>
                                                </div>
                                                <div class="col-lg-3 form-check form-check-inline" style="margin-right: 2px;">
                                                    <label class="form-check-label">
                                                        <input type="radio" name="contagemCompressa" class="contagemCompressa form-input-styled" placeholder="" value="NAO" onclick="selecionacontagemCompressa('NAO')" <?php if (isset($iAtendimentoCheckList )) echo $rowCheckList['EnCCSContagemCompressa'] == 'NAO' ? 'checked' : ''; ?> >
                                                        NÃO
                                                    </label>
                                                </div>
                                                <div class="col-lg-6 row form-check form-check-inline" style="margin-right: 2px;">
                                                    <label class="form-check-label">
                                                        <input type="radio" name="contagemCompressa" class="contagemCompressa form-input-styled" placeholder="" value="NSA" onclick="selecionacontagemCompressa('NSA')" <?php if (isset($iAtendimentoCheckList )) echo $rowCheckList['EnCCSContagemCompressa'] == 'NSA' ? 'checked' : ''; ?> >
                                                        NÃO SE APLICA
                                                    </label>
                                                </div>
                                            </div>
                                            <div id="contagemCompressaDescricaoViwer" style=" display: <?php if (isset($iAtendimentoCheckList)) { echo $rowCheckList['EnCCSContagemCompressa'] == 'SIM' ? 'block' : 'none'; } else { echo 'none'; } ?>" >
                                                <textarea id="contagemCompressaDescricao" name="contagemCompressaDescricao" onInput="contarCaracteres(this);" class="form-control" rows="4" cols="4" maxLength="150" placeholder="" ><?php echo isset($iAtendimentoCheckList) ? $rowCheckList['EnCCSContagemCompressaDescricao'] : ''; ?></textarea>
                                                <small class="text-muted form-text">
                                                    Máx. 150 caracteres
                                                    <span class="caracterescontagemCompressaDescricao"></span>
                                                </small>
                                            </div>                                            
                                        </div>

                                        <div class="col-lg-4">
                                            <label>
                                                Peças anatômicas/culturas identificadas<br>
                                                adequadamente e com requisição preenchida                                                
                                            </label>
                                            <div class="col-lg-12 row options">
                                                <div class="col-lg-3 form-check form-check-inline">
                                                    <label class="form-check-label">
                                                        <input type="radio" name="pecaAnatomica" class="pecaAnatomica form-input-styled" placeholder="" value="1" onclick="selecionapecaAnatomica('1')" <?php if (isset($iAtendimentoCheckList )) echo $rowCheckList['EnCCSPecaAnatomica'] == '1' ? 'checked' : ''; ?> >
                                                        SIM
                                                    </label>
                                                </div>
                                                <div class="col-lg-3 form-check form-check-inline">
                                                    <label class="form-check-label">
                                                        <input type="radio" name="pecaAnatomica" class="pecaAnatomica form-input-styled" placeholder="" value="0" onclick="selecionapecaAnatomica('0')" <?php if (isset($iAtendimentoCheckList )) echo $rowCheckList['EnCCSPecaAnatomica'] == '0' ? 'checked' : ''; ?> >
                                                        NÃO
                                                    </label>
                                                </div>
                                            </div>
                                            <div id="pecaAnatomicaDescricaoViwer" style=" display: <?php if (isset($iAtendimentoCheckList)) { echo $rowCheckList['EnCCSPecaAnatomica'] == '1' ? 'block' : 'none'; } else { echo 'none'; } ?>" >
                                                <textarea id="pecaAnatomicaDescricao" name="pecaAnatomicaDescricao" onInput="contarCaracteres(this);" class="form-control" rows="4" cols="4" maxLength="150" placeholder="" ><?php echo isset($iAtendimentoCheckList) ? $rowCheckList['EnCCSPecaAnatomicaDescricao'] : ''; ?></textarea>
                                                <small class="text-muted form-text">
                                                    Máx. 150 caracteres
                                                    <span class="caracterespecaAnatomicaDescricao"></span>
                                                </small>
                                            </div>
                                        </div>

                                        <div class="col-lg-4">
                                            <label>
                                                Registro completo do procedimento <br>
                                                intra-operatório
                                            </label>
                                            <div class="col-lg-12 row options">
                                                <div class="col-lg-3 form-check form-check-inline">
                                                    <label class="form-check-label">
                                                        <input type="radio" name="registroCompleto" class="registroCompleto form-input-styled" placeholder="" value="1" onclick="selecionaregistroCompleto('1')" <?php if (isset($iAtendimentoCheckList )) echo $rowCheckList['EnCCSRegistroCompleto'] == '1' ? 'checked' : ''; ?> >
                                                        SIM
                                                    </label>
                                                </div>
                                                <div class="col-lg-3 form-check form-check-inline">
                                                    <label class="form-check-label">
                                                        <input type="radio" name="registroCompleto" class="registroCompleto form-input-styled" placeholder="" value="0" onclick="selecionaregistroCompleto('0')" <?php if (isset($iAtendimentoCheckList )) echo $rowCheckList['EnCCSRegistroCompleto'] == '0' ? 'checked' : ''; ?> >
                                                        NÃO
                                                    </label>
                                                </div>
                                            </div>
                                            <div id="registroCompletoDescricaoViwer" style=" display: <?php if (isset($iAtendimentoCheckList)) { echo $rowCheckList['EnCCSRegistroCompleto'] == '1' ? 'block' : 'none'; } else { echo 'none'; } ?>" >
                                                <textarea id="registroCompletoDescricao" name="registroCompletoDescricao" onInput="contarCaracteres(this);" class="form-control" rows="4" cols="4" maxLength="150" placeholder="" ><?php echo isset($iAtendimentoCheckList) ? $rowCheckList['EnCCSRegistroCompletoDescricao'] : ''; ?></textarea>
                                                <small class="text-muted form-text">
                                                    Máx. 150 caracteres
                                                    <span class="caracteresregistroCompletoDescricao"></span>
                                                </small>
                                            </div>
                                        </div>
                                        
                                    </div>


                                    <div class="col-lg-12 mb-3 row">

                                        <div class="col-lg-4">                                            
                                            <label>
                                                Recomendações e orientações pós- <br>
                                                anestésico e pós cirúrgico do Paciente
                                            </label>
                                            <div class="col-lg-12 row options">
                                                <div class="col-lg-3 form-check form-check-inline">
                                                    <label class="form-check-label">
                                                        <input type="radio" name="recomendacaoPosAnestesico" class="recomendacaoPosAnestesico form-input-styled" placeholder="" value="1" onclick="selecionarecomendacaoPosAnestesico('1')" <?php if (isset($iAtendimentoCheckList )) echo $rowCheckList['EnCCSRecomendacaoPosAnestesico'] == '1' ? 'checked' : ''; ?> >
                                                        SIM
                                                    </label>
                                                </div>
                                                <div class="col-lg-3 form-check form-check-inline">
                                                    <label class="form-check-label">
                                                        <input type="radio" name="recomendacaoPosAnestesico" class="recomendacaoPosAnestesico form-input-styled" placeholder="" value="0" onclick="selecionarecomendacaoPosAnestesico('0')" <?php if (isset($iAtendimentoCheckList )) echo $rowCheckList['EnCCSRecomendacaoPosAnestesico'] == '0' ? 'checked' : ''; ?> >
                                                        NÃO
                                                    </label>
                                                </div>
                                            </div>
                                            <div id="recomendacaoPosAnestesicoDescricaoViwer" style=" display: <?php if (isset($iAtendimentoCheckList)) { echo $rowCheckList['EnCCSRecomendacaoPosAnestesico'] == '1' ? 'block' : 'none'; } else { echo 'none'; } ?>" >
                                                <textarea id="recomendacaoPosAnestesicoDescricao" name="recomendacaoPosAnestesicoDescricao" onInput="contarCaracteres(this);" class="form-control" rows="4" cols="4" maxLength="150" placeholder="" ><?php echo isset($iAtendimentoCheckList) ? $rowCheckList['EnCCSRecomendacaoPosAnestesicoDescricao'] : ''; ?></textarea>
                                                <small class="text-muted form-text">
                                                    Máx. 150 caracteres
                                                    <span class="caracteresrecomendacaoPosAnestesicoDescricao"></span>
                                                </small>
                                            </div>                                            
                                        </div>

                                        <div class="col-lg-4">
                                            <label>
                                                Fixação das etiquetas de <br>
                                                esterilização no prontuário
                                            </label>
                                            <div class="col-lg-12 row options">
                                                <div class="col-lg-3 form-check form-check-inline">
                                                    <label class="form-check-label">
                                                        <input type="radio" name="fixacaoEtiqueta" class="fixacaoEtiqueta form-input-styled" placeholder="" value="1" onclick="selecionafixacaoEtiqueta('1')" <?php if (isset($iAtendimentoCheckList )) echo $rowCheckList['EnCCSFixacaoEtiqueta'] == '1' ? 'checked' : ''; ?> >
                                                        SIM
                                                    </label>
                                                </div>
                                                <div class="col-lg-3 form-check form-check-inline">
                                                    <label class="form-check-label">
                                                        <input type="radio" name="fixacaoEtiqueta" class="fixacaoEtiqueta form-input-styled" placeholder="" value="0" onclick="selecionafixacaoEtiqueta('0')" <?php if (isset($iAtendimentoCheckList )) echo $rowCheckList['EnCCSFixacaoEtiqueta'] == '0' ? 'checked' : ''; ?> >
                                                        NÃO
                                                    </label>
                                                </div>
                                            </div>
                                            <div id="fixacaoEtiquetaDescricaoViwer" style=" display: <?php if (isset($iAtendimentoCheckList)) { echo $rowCheckList['EnCCSFixacaoEtiqueta'] == '1' ? 'block' : 'none'; } else { echo 'none'; } ?>" >
                                                <textarea id="fixacaoEtiquetaDescricao" name="fixacaoEtiquetaDescricao" onInput="contarCaracteres(this);" class="form-control" rows="4" cols="4" maxLength="150" placeholder="" ><?php echo isset($iAtendimentoCheckList) ? $rowCheckList['EnCCSFixacaoEtiquetaDescricao'] : ''; ?></textarea>
                                                <small class="text-muted form-text">
                                                    Máx. 150 caracteres
                                                    <span class="caracteresfixacaoEtiquetaDescricao"></span>
                                                </small>
                                            </div>
                                        </div>

                                    </div>
                                    
                                                                       
                                    <div class="col-lg-12 mb-3">
                                        <label for="inputObservacaoPreOperatorio">Observação: Pré-Operatório e Trans-Operatório</label>                                   
                                        <textarea rows="4"  maxLength="1000" onInput="contarCaracteres(this);"  id="inputObservacaoPreOperatorio" name="inputObservacaoPreOperatorio" class="form-control" placeholder="" ><?php  if (isset($iAtendimentoCheckList )) echo $rowCheckList['EnCCSObservacaoPreOperatorio']; ?></textarea>
                                        <small class="text-muted form-text">Max. 1000 caracteres<span class="caracteresinputObservacaoPreOperatorio"></span></small>  
                                    </div> 

                                    <div class="col-lg-12 mb-3">
                                        <label for="inputRecomendacaoCirurgiao">Recomendações Terapêutico: Pós-Operatório (Cirurgião)</label>                                   
                                        <textarea rows="4"  maxLength="1000" onInput="contarCaracteres(this);"  id="inputRecomendacaoCirurgiao" name="inputRecomendacaoCirurgiao" class="form-control" placeholder="" ><?php  if (isset($iAtendimentoCheckList )) echo $rowCheckList['EnCCSRecomendacaoCirurgiao']; ?></textarea>
                                        <small class="text-muted form-text">Max. 1000 caracteres<span class="caracteresinputRecomendacaoCirurgiao"></span></small>  
                                    </div> 

                                    <div class="col-lg-12 mb-3">
                                        <label for="inputRecomendacaoAnestesista">Recomendações Terapêutico: Pós-Operatório (Anestesista)</label>                                   
                                        <textarea rows="4"  maxLength="1000" onInput="contarCaracteres(this);"  id="inputRecomendacaoAnestesista" name="inputRecomendacaoAnestesista" class="form-control" placeholder="" ><?php  if (isset($iAtendimentoCheckList )) echo $rowCheckList['EnCCSRecomendacaoAnestesista']; ?></textarea>
                                        <small class="text-muted form-text">Max. 1000 caracteres<span class="caracteresinputRecomendacaoAnestesista"></span></small>  
                                    </div>  

                                    <div class="col-lg-12 mb-3">
                                        <label for="inputRecomendacaoEnfermagem">Recomendações Terapêutico: Pós-Operatório (Enfermagem)</label>                                   
                                        <textarea rows="4"  maxLength="1000" onInput="contarCaracteres(this);"  id="inputRecomendacaoEnfermagem" name="inputRecomendacaoEnfermagem" class="form-control" placeholder="" ><?php  if (isset($iAtendimentoCheckList )) echo $rowCheckList['EnCCSRecomendacaoEnfermagem']; ?></textarea>
                                        <small class="text-muted form-text">Max. 1000 caracteres<span class="caracteresinputRecomendacaoEnfermagem"></span></small>  
                                    </div>                                  
                                    
                                </div>
                            </div>
                            



                            <div class="card">
                                <div class=" card-body row">
                                    <div class="col-lg-12">
                                        <div class="form-group" style="margin-bottom:0px;">
                                            <button class="btn btn-lg btn-success mr-1 salvarChecklist" >Salvar</button>
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