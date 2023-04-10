<?php
include_once("sessao.php");
$_SESSION['PaginaAtual'] = 'Provisão de Alta';
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
FROM EnfermagemProvisaoAlta
WHERE EnPrAAtendimento = $iAtendimentoId
ORDER BY EnPrAId DESC";
$result = $conn->query($sql);
$rowProvisao = $result->fetch(PDO::FETCH_ASSOC);

$iAtendimentoProvisaoAlta = $rowProvisao?$rowProvisao['EnPrAId']:null;

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

if (isset($_POST['inputInicio'])) {

    try {       
        
        if ($iAtendimentoProvisaoAlta) {

            $sql = "UPDATE EnfermagemProvisaoAlta SET   

                EnPrAAtendimento = :EnPrAAtendimento ,
                EnPrADataInicio = :EnPrADataInicio ,
                EnPrAHoraInicio = :EnPrAHoraInicio ,
                EnPrADataFim = :EnPrADataFim ,
                EnPrAHoraFim = :EnPrAHoraFim ,
                EnPrAPrevisaoAlta = :EnPrAPrevisaoAlta ,
                EnPrATipoInternacao = :EnPrATipoInternacao ,
                EnPrAEspecialidadeLeito = :EnPrAEspecialidadeLeito ,
                EnPrAAla = :EnPrAAla ,
                EnPrAQuarto = :EnPrAQuarto ,
                EnPrALeito = :EnPrALeito ,
                EnPrAProfissional = :EnPrAProfissional ,
                EnPrAPas = :EnPrAPas ,
                EnPrAPad = :EnPrAPad ,
                EnPrAFreqCardiaca = :EnPrAFreqCardiaca ,
                EnPrAFreqRespiratoria = :EnPrAFreqRespiratoria ,
                EnPrATemperatura = :EnPrATemperatura ,
                EnPrASPO = :EnPrASPO ,
                EnPrAHGT = :EnPrAHGT ,
                EnPrAPeso = :EnPrAPeso ,
                EnPrADataHoraProvisionamento = :EnPrADataHoraProvisionamento ,
                EnPrAProfissionalResponsavel = :EnPrAProfissionalResponsavel ,
                EnPrAJustificativa = :EnPrAJustificativa ,
                EnPrACid10 = :EnPrACid10 ,
                EnPrAProcedimento = :EnPrAProcedimento ,
                EnPrAObservacao = :EnPrAObservacao ,
                EnPrAUnidade = :EnPrAUnidade           
                WHERE EnPrAId = :iAtendimentoProvisaoAlta";

            $result = $conn->prepare($sql);

            $result->execute(array(

                ':EnPrAAtendimento' => $iAtendimentoId,
                ':EnPrADataInicio' => date('m/d/Y'),
                ':EnPrAHoraInicio' => date('H:i'),
                ':EnPrADataFim' => date('m/d/Y'),
                ':EnPrAHoraFim' => date('H:i'),
                ':EnPrAPrevisaoAlta' => $_POST['inputPrevisaoAlta'] == "" ? null : $_POST['inputPrevisaoAlta'],
                ':EnPrATipoInternacao' => $_POST['inputTipoInternacao'] == "" ? null : $_POST['inputTipoInternacao'],
                ':EnPrAEspecialidadeLeito' => $_POST['inputEspLeito'] == "" ? null : $_POST['inputEspLeito'],
                ':EnPrAAla' => $_POST['inputAla'] == "" ? null : $_POST['inputAla'],
                ':EnPrAQuarto' => $_POST['inputQuarto'] == "" ? null : $_POST['inputQuarto'],
                ':EnPrALeito' => $_POST['inputLeito'] == "" ? null : $_POST['inputLeito'],
                ':EnPrAProfissional' => $userId,
                ':EnPrAPas' => $_POST['inputSistolica'] == "" ? null : $_POST['inputSistolica'],
                ':EnPrAPad' => $_POST['inputDiatolica'] == "" ? null : $_POST['inputDiatolica'], 
                ':EnPrAFreqCardiaca' => $_POST['inputCardiaca'] == "" ? null : $_POST['inputCardiaca'],
                ':EnPrAFreqRespiratoria' => $_POST['inputRespiratoria'] == "" ? null : $_POST['inputRespiratoria'],
                ':EnPrATemperatura' => $_POST['inputTemperatura'] == "" ? null : $_POST['inputTemperatura'],
                ':EnPrASPO' => $_POST['inputSPO'] == "" ? null : $_POST['inputSPO'],
                ':EnPrAHGT' => $_POST['inputHGT'] == "" ? null : $_POST['inputHGT'],
                ':EnPrAPeso' => $_POST['inputPeso'] == "" ? null : $_POST['inputPeso'],
                ':EnPrADataHoraProvisionamento' => $_POST['inputDataProvisonamentoAlta'] == "" ? null : str_replace('T', ' ', $_POST['inputDataProvisonamentoAlta'] ),
                ':EnPrAProfissionalResponsavel' => $_POST['cmbDadosProfResponsavel'] == "" ? null : $_POST['cmbDadosProfResponsavel'],
                ':EnPrAJustificativa' => $_POST['inputJustificativa'] == "" ? null : $_POST['inputJustificativa'],
                ':EnPrACid10' => $_POST['cmbCId10'] == "" ? null : $_POST['cmbCId10'],
                ':EnPrAProcedimento' => $_POST['cmbProcedimentoDiagnostico'] == "" ? null : $_POST['cmbProcedimentoDiagnostico'],
                ':EnPrAObservacao' => $_POST['inputObservacao'] == "" ? null : $_POST['inputObservacao'],            
                ':EnPrAUnidade' => $iUnidade,
                ':iAtendimentoProvisaoAlta' => $iAtendimentoProvisaoAlta
            ));

            $_SESSION['msg']['titulo'] = "Sucesso";
            $_SESSION['msg']['mensagem'] = "Provisão de Alta alterada com sucesso!!!";
            $_SESSION['msg']['tipo'] = "success";

        } else {

            $sql = "INSERT INTO EnfermagemProvisaoAlta (

                EnPrAAtendimento ,
                EnPrADataInicio ,
                EnPrAHoraInicio ,
                EnPrADataFim ,
                EnPrAHoraFim ,
                EnPrAPrevisaoAlta ,
                EnPrATipoInternacao ,
                EnPrAEspecialidadeLeito ,
                EnPrAAla ,
                EnPrAQuarto ,
                EnPrALeito ,
                EnPrAProfissional ,
                EnPrAPas ,
                EnPrAPad ,
                EnPrAFreqCardiaca ,
                EnPrAFreqRespiratoria ,
                EnPrATemperatura ,
                EnPrASPO ,
                EnPrAHGT ,
                EnPrAPeso ,
                EnPrADataHoraProvisionamento ,
                EnPrAProfissionalResponsavel ,
                EnPrAJustificativa ,
                EnPrACid10 ,
                EnPrAProcedimento ,
                EnPrAObservacao ,
                EnPrAUnidade

            ) VALUES (
                
                :EnPrAAtendimento ,
                :EnPrADataInicio ,
                :EnPrAHoraInicio ,
                :EnPrADataFim ,
                :EnPrAHoraFim ,
                :EnPrAPrevisaoAlta ,
                :EnPrATipoInternacao ,
                :EnPrAEspecialidadeLeito ,
                :EnPrAAla ,
                :EnPrAQuarto ,
                :EnPrALeito ,
                :EnPrAProfissional ,
                :EnPrAPas ,
                :EnPrAPad ,
                :EnPrAFreqCardiaca ,
                :EnPrAFreqRespiratoria ,
                :EnPrATemperatura ,
                :EnPrASPO ,
                :EnPrAHGT ,
                :EnPrAPeso ,
                :EnPrADataHoraProvisionamento ,
                :EnPrAProfissionalResponsavel ,
                :EnPrAJustificativa ,
                :EnPrACid10 ,
                :EnPrAProcedimento ,
                :EnPrAObservacao ,
                :EnPrAUnidade          
                
            )";
            $result = $conn->prepare($sql);

            $result->execute(array(
                ':EnPrAAtendimento' => $iAtendimentoId,
                ':EnPrADataInicio' => date('m/d/Y'),
                ':EnPrAHoraInicio' => date('H:i'),
                ':EnPrADataFim' => date('m/d/Y'),
                ':EnPrAHoraFim' => date('H:i'),
                ':EnPrAPrevisaoAlta' => $_POST['inputPrevisaoAlta'] == "" ? null : $_POST['inputPrevisaoAlta'],
                ':EnPrATipoInternacao' => $_POST['inputTipoInternacao'] == "" ? null : $_POST['inputTipoInternacao'],
                ':EnPrAEspecialidadeLeito' => $_POST['inputEspLeito'] == "" ? null : $_POST['inputEspLeito'],
                ':EnPrAAla' => $_POST['inputAla'] == "" ? null : $_POST['inputAla'],
                ':EnPrAQuarto' => $_POST['inputQuarto'] == "" ? null : $_POST['inputQuarto'],
                ':EnPrALeito' => $_POST['inputLeito'] == "" ? null : $_POST['inputLeito'],
                ':EnPrAProfissional' => $userId,
                ':EnPrAPas' => $_POST['inputSistolica'] == "" ? null : $_POST['inputSistolica'],
                ':EnPrAPad' => $_POST['inputDiatolica'] == "" ? null : $_POST['inputDiatolica'], 
                ':EnPrAFreqCardiaca' => $_POST['inputCardiaca'] == "" ? null : $_POST['inputCardiaca'],
                ':EnPrAFreqRespiratoria' => $_POST['inputRespiratoria'] == "" ? null : $_POST['inputRespiratoria'],
                ':EnPrATemperatura' => $_POST['inputTemperatura'] == "" ? null : $_POST['inputTemperatura'],
                ':EnPrASPO' => $_POST['inputSPO'] == "" ? null : $_POST['inputSPO'],
                ':EnPrAHGT' => $_POST['inputHGT'] == "" ? null : $_POST['inputHGT'],
                ':EnPrAPeso' => $_POST['inputPeso'] == "" ? null : $_POST['inputPeso'],
                ':EnPrADataHoraProvisionamento' => $_POST['inputDataProvisonamentoAlta'] == "" ? null : str_replace('T', ' ', $_POST['inputDataProvisonamentoAlta'] ),
                ':EnPrAProfissionalResponsavel' => $_POST['cmbDadosProfResponsavel'] == "" ? null : $_POST['cmbDadosProfResponsavel'],
                ':EnPrAJustificativa' => $_POST['inputJustificativa'] == "" ? null : $_POST['inputJustificativa'],
                ':EnPrACid10' => $_POST['cmbCId10'] == "" ? null : $_POST['cmbCId10'],
                ':EnPrAProcedimento' => $_POST['cmbProcedimentoDiagnostico'] == "" ? null : $_POST['cmbProcedimentoDiagnostico'],
                ':EnPrAObservacao' => $_POST['inputObservacao'] == "" ? null : $_POST['inputObservacao'],            
                ':EnPrAUnidade' => $iUnidade
            ));

            $_SESSION['msg']['titulo'] = "Sucesso";
            $_SESSION['msg']['mensagem'] = "Provisão de Alta inserida com sucesso!!!";
            $_SESSION['msg']['tipo'] = "success";

        }
    } catch (PDOException $e) {

        $_SESSION['msg']['titulo'] = "Erro";
        $_SESSION['msg']['mensagem'] = "Erro reportado com a Provisão de Alta!!!";
        $_SESSION['msg']['tipo'] = "error";

        echo 'Error: ' . $e->getMessage();
    }

    $_SESSION['iAtendimentoId'] = $iAtendimentoId;
    irpara("atendimentoProvisaoAlta.php");
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Lamparinas | Provisão de Alta</title>

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
      
            $('.salvarProvisaoAlta').on('click', function(e) {
                e.preventDefault();

                $("#formAtendimentoProvisaoAlta").submit();
            })          

        }); //document.ready

        function ext(path) {
			var final = path.substr(path.lastIndexOf('/')+1);
			var separador = final.lastIndexOf('.');
			return separador <= 0 ? '' : final.substr(separador + 1);
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
                        <form name="formAtendimentoProvisaoAlta" id="formAtendimentoProvisaoAlta" enctype="multipart/form-data" method="post">
                            <?php
                            echo "<input type='hidden' id='iAtendimentoId' name='iAtendimentoId' value='$iAtendimentoId' />";
                            ?>
                            <div class="card">

                                <div class="col-md-12">
                                    <div class="row">

                                        <div class="col-md-6" style="text-align: left;">

                                            <div class="card-header header-elements-inline">
                                                <h3 class="card-title"><b>PROVISÃO DE ALTA DO PACIENTE</b></h3>
                                            </div>

                                        </div>

                                        <div class="col-md-6" style="text-align: right;">

                                            <div class="form-group" style="margin:20px;">
                                                <?php 
                                                    if (isset($SituaChave) && $SituaChave != "ATENDIDO") {
                                                        echo "<button class='btn btn-lg btn-success mr-1 salvarProvisaoAlta'>Salvar</button>";
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


                                <!-- titulo box -->
                                <div class="card-header header-elements-inline">
                                    <h3 class="card-title font-weight-bold titulo-box-alta">
                                        Provisão de Alta
                                    </h3>  
                                </div>

                                <div class="card-body row">                                     

                                    <div class="col-lg-12 row "> 

                                        <div class="col-lg-4">
                                            <div class="col-lg-8 mb-2">
                                                <label>Provisionamento de Alta</label>
                                                <input type="datetime-local" class="form-control" name="inputDataProvisonamentoAlta" id="inputDataProvisonamentoAlta" value="<?php echo isset($iAtendimentoProvisaoAlta) ? $rowProvisao['EnPrADataHoraProvisionamento'] : ''; ?>" >
                                            </div>                                                                                      

                                            <div class="col-lg-12 ">
                                                <div class="form-group">
                                                    <label for="cmbDadosProfResponsavel">Dados do Profissional Responsável</label>
                                                    <select id="cmbDadosProfResponsavel" name="cmbDadosProfResponsavel" class="select-search" >
                                                        <option value="">Selecione</option>
                                                        <?php
                                                            foreach($rowProfissionais as $item){
                                                                if (isset($iAtendimentoProvisaoAlta) && $rowProvisao['EnPrAProfissionalResponsavel'] == $item['ProfiId'] ) {
                                                                    echo "<option value='$item[ProfiId]' selected>$item[ProfiNome] - $item[profissao] - $item[ProfiCbo]</option>";
                                                                } else {
                                                                    echo "<option value='$item[ProfiId]'>$item[ProfiNome] - $item[profissao] - $item[ProfiCbo]</option>";
                                                                }                                                          
                                                            }
                                                        ?>
                                                    </select>                                               
                                                </div>                                                
                                            </div>                                                
                                        </div>
                                        
                                        <div class="col-lg-8">                                                                                         
                                            <label for="inputJustificativa">Justificativa</label>
                                            <textarea rows="5"  maxLength="150" onInput="contarCaracteres(this);"  id="inputJustificativa" name="inputJustificativa" class="form-control" placeholder="" ><?php  if (isset($iAtendimentoProvisaoAlta )) echo $rowProvisao['EnPrAJustificativa']; ?></textarea>
                                            <small class="text-muted form-text">Max. 150 caracteres<span class="caracteresinputJustificativa"></span></small>                                                                                                                                           
                                        </div>                                           
                                                                                                                        
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
                                                    $seleciona = $item['Cid10Id'] == $rowProvisao['EnPrACid10'] ? "selected" : "";
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
                                                    $seleciona = $item['SrVenId'] == $rowProvisao['EnPrAProcedimento'] ? "selected" : "";
                                                    print('<option value="' . $item['SrVenId'] . '" ' . $seleciona . '>' . $item['SrVenCodigo'] . ' - ' . $item['SrVenNome'] . '</option>');
                                                }
                                            ?>
                                        </select>											
                                    </div>

                                </div>     
                                
                                <div class="card-header header-elements-inline">
                                    <h3 class="card-title font-weight-bold ">Observações:</h3>  
                                </div>

                                <div class="card-body mb-2 row">                                    
                                    <div class="col-lg-12">                                   
                                        <textarea rows="4"  maxLength="800" onInput="contarCaracteres(this);"  id="inputObservacao" name="inputObservacao" class="form-control" placeholder="" ><?php  if (isset($iAtendimentoProvisaoAlta )) echo $rowProvisao['EnPrAObservacao']; ?></textarea>
                                        <small class="text-muted form-text">Max. 800 caracteres<span class="caracteresinputObservacao"></span></small>  
                                    </div>                                  
                                </div>                                     
                              
                            </div>

                            <div class="card">
                                <div class=" card-body row">
                                    <div class="col-lg-12">
                                        <div class="form-group" style="margin-bottom:0px;">
                                            <?php 
                                                if (isset($SituaChave) && $SituaChave != "ATENDIDO") {
                                                    echo "<button class='btn btn-lg btn-success mr-1 salvarProvisaoAlta'>Salvar</button>";
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