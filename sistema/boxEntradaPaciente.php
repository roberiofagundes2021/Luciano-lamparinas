<?php 

$sql = "SELECT TOP(1) *
FROM AtendimentoObservacaoEntrada
WHERE AtOEnAtendimento = $iAtendimentoId";
$result = $conn->query($sql);
$rowEntrada= $result->fetch(PDO::FETCH_ASSOC);

$idEntrada = $rowEntrada?$rowEntrada['AtOEnId']:null;


?>

<div class="card">

    <div class="card-header header-elements-inline">
        <h3 class="card-title">HIPÓTESE DIAGNÓSTICA</h3>
    </div>

    <div class="card-body">

        <form id="formDieta" name="formDieta" method="post" class="form-validate-jquery">									
                                        
            <div class="col-lg-12 row" style="margin-top: 10px">
                <div class="col-lg-4">
                    <div class="form-group">
                        <label for="justificativa">HISTÓRIA MOLESTIA ATUAL <span class="text-danger">*</span></label>
                        <textarea rows="5" cols="5" maxLength="500" id="historiaEntrada" name="historiaEntrada"  class="form-control" placeholder="..." ><?php if (isset($idEntrada )) echo $rowEntrada['AtOEnHistoriaMolestiaAtual']; ?></textarea>
                        <small class="text-muted form-text">Max. 500 caracteres - <span class="caracteresjustificativa"></span></small>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="form-group">
                        <label for="justificativa">EXAME FÍSICO <span class="text-danger">*</span></label>
                        <textarea rows="5" cols="5" maxLength="500" id="exameFisico" name="exameFisico"  class="form-control" placeholder="..." ><?php if (isset($idEntrada )) echo $rowEntrada['AtOEnExameFisico']; ?></textarea>
                        <small class="text-muted form-text">Max. 500 caracteres - <span class="caracteresjustificativa"></span></small>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="form-group">
                        <label for="justificativa">HIPÓTESE DIAGNÓSTICA <span class="text-danger">*</span></label>
                        <textarea rows="5" cols="5" maxLength="500" id="hipoteseDiagnostica" name="hipoteseDiagnostica"  class="form-control" placeholder="..." ><?php if (isset($idEntrada )) echo $rowEntrada['AtOEnHipoteseDiagnostica']; ?></textarea>
                        <small class="text-muted form-text">Max. 500 caracteres - <span class="caracteresjustificativa"></span></small>
                    </div>
                </div>
            </div>

            <div class="col-lg-12 row" style="margin-top: 10px">

                <div class="col-lg-4">
                    <div class="form-group">
                        <label for="justificativa">ANAMNESE (DIGITAÇÃO LIVRE)</label>
                        <textarea rows="5" cols="5" maxLength="1000" id="anamnese" name="anamnese"  class="form-control" placeholder="..." ><?php if (isset($idEntrada )) echo $rowEntrada['AtOEnAnamnese']; ?></textarea>
                        <small class="text-muted form-text">Max. 1000 caracteres - <span class="caracteresjustificativa"></span></small>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="form-group">
                        
                        <label for="justificativa">CARÁTER DA INTERNAÇÃO </label>

                        <select id="caraterInternacao" name="caraterInteraacao" class="select-search" >
                            <option value="">selecione</option>
                            <?php
                                $sql = "SELECT CrIntId,CrIntNome
                                FROM CaraterInternacao
                                JOIN Situacao ON SituaId = CrIntStatus
                                WHERE CrIntUnidade = $iUnidade and SituaChave = 'ATIVO'";
                                $result = $conn->query($sql);
                                $result = $result->fetchAll(PDO::FETCH_ASSOC);

                                foreach($result as $item){

                                    if ( isset( $idEntrada ) && $rowEntrada['AtOEnCaraterInternacao'] == $item['CrIntId'] ) {
                                        echo "<option value='$item[CrIntId]' selected>$item[CrIntNome]</option>";                                        
                                    } else {
                                        echo "<option value='$item[CrIntId]'>$item[CrIntNome]</option>";
                                    }
                                    

                                }
                            ?>
                        </select>
                        
                    </div>
                </div>

            </div>

        </form>

    </div>

    <div class="card-header header-elements-inline">
        <h3 class="card-title">DIAGNÓSTICO PRINCIPAL</h3>
    </div>

    <div class="card-body">

        <form id="formDieta" name="formDieta" method="post" class="form-validate-jquery">
            <div class="col-lg-12 mb-3 row">
                <!-- titulos -->
                <div class="col-lg-6">
                    <label>CID-10 <span class="text-danger">*</span></label>
                </div>
            
                <div class="col-lg-6">
                    <label>Procedimento <span class="text-danger">*</span></label>
                </div>
                <!-- campos -->										
                <div class="col-lg-6">
                    <select id="cid10" name="cid10" class="select-search">
                        <option value=''>Selecione</option>
                    </select>											
                </div>
                <div class="col-lg-6">
                    <select id="servico" name="servico" class="select-search">
                        <option value=''>Selecione</option>
                    </select>											
                </div>
            </div>                    
        </form>

    </div>

    <div class="card-body">

        <div class="col-md-12">
            <div class="row">                                    
                <div class="col-md-10" style="text-align: left;">
                    <button type="button" class="btn btn-lg btn-success mr-1" id="salvarObservacaoEntrada">Salvar</button>
                </div>            
            </div>
        </div> 

    </div>

</div>
