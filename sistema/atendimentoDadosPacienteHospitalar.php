<div class="card card-collapsed">
    <div class="card-header header-elements-inline">
        <h4 class="card-title font-weight-bold">
            <label>PRONTUÁRIO ELETRÔNICO: <?php echo $row['ClienCodigo'] != '' ? $row['ClienCodigo'] : 'XXXXXX'; ?></label>
            <label> - 
                <?php 
                    $encoding = mb_internal_encoding(); // ou UTF-8, ISO-8859-1
                    echo mb_strtoupper($row['ClienNome'], $encoding); 
                ?>
            </label>
        </h4>
        <div class="header-elements">
            <div class="list-icons">
                <a class="list-icons-item" data-action="collapse"></a>
            </div>
        </div>
    </div>
    <div class="card-body" style="padding: 1.25rem 1.25rem 0 1.25rem">
        <div class="row">            
            <div class="col-lg-2">
                <div class="form-group">
                    <label>Nº do Registro: <?php echo $row['AtendNumRegistro']; ?></label>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="form-group">
                    <label>Modalidade: <?php echo $row['AtModNome'] ; ?></label>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="form-group">
                    <label>CNS: <?php echo $row['ClienCartaoSus']; ?></label>
                </div>
            </div>
            <div class="col-lg-2">
                <div class="form-group">
                    <label>Telefone: <?php echo $row['ClienCelular']; ?></label>
                </div>
            </div>
            <div class="col-lg-2">
                <div class="form-group">
                    <label>Sexo: <?php echo $sexo ; ?></label>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-2">
                <div class="form-group">
                    <label>Nascimento: <?php echo mostraData($row['ClienDtNascimento']); ?></label>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="form-group">
                    <label>Idade: <?php echo calculaIdade($row['ClienDtNascimento']); ?></label>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="form-group">
                    <label>Mãe: <?php echo $row['ClienNomeMae'] ; ?></label>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="form-group">
                    <label>Responsável: <?php echo $row['ClResNome']; ?></label>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">

        <input type="hidden" id="inputDataInicio" name="inputDataInicio" class="form-control" value="<?php if (isset($iAtendimentoEletivoId )){ echo $DataAtendimentoInicio;} else { echo date('d/m/Y'); } ?>" > 
        <input type="hidden" id="inputInicio" name="inputInicio" class="form-control"  value="<?php if (isset($iAtendimentoEletivoId )){ echo $HoraInicio;} else { echo date('H:i'); } ?>" >
        <input type="hidden" id="inputDataFim" name="inputDataFim" class="form-control" value="<?php if (isset($iAtendimentoEletivoId )){ echo $DataAtendimentoFim;} else { echo date('d/m/Y'); } ?>" > 
        <input type="hidden" id="inputFim" name="inputFim" class="form-control" value="<?php if (isset($iAtendimentoEletivoId )) echo $HoraFim; ?>" >
        <input type="hidden" id="inputConselho" name="inputConselho" class="form-control"  value="<?php echo $rowUser['PrConNome']; ?>" >
        <input type="hidden" id="inputProfissional" name="inputProfissional" class="form-control"  value="<?php echo $rowUser['ProfissionalNome']; ?>" >
        <input type="hidden" id="inputCbo" name="inputCbo" class="form-control"  value="<?php echo $rowUser['ProfissaoCbo']; ?>" >
        <input type="hidden" id="inputPrevisaoAlta" name="inputPrevisaoAlta" class="form-control"  value="" >
        <input type="hidden" id="inputTipoInternacao" name="inputTipoInternacao" class="form-control"  value="<?php echo $row['TpIntId']; ?>" >
        <input type="hidden" id="inputEspLeito" name="inputEspLeito" class="form-control"  value="<?php echo $row['EsLeiId']; ?>" >
        <input type="hidden" id="inputAla" name="inputAla" class="form-control"  value="<?php echo $row['AlaId']; ?>" >
        <input type="hidden" id="inputQuarto" name="inputQuarto" class="form-control"  value="<?php echo $row['QuartId']; ?>" >
        <input type="hidden" id="inputLeito" name="inputLeito" class="form-control"  value="<?php echo $row['LeitoId']; ?>" >

        <div class="row">
            <div class="col-lg-2">
                <div class="form-group">
                    <label>Data do Evento: <?php if (isset($iAtendimentoEletivoId )){ echo $DataAtendimentoInicio;} else { echo date('d/m/Y'); } ?></label>
                </div>
            </div>
            <div class="col-lg-2">
                <div class="form-group">
                    <label>Início do Evento: <?php if (isset($iAtendimentoEletivoId )){ echo $HoraInicio;} else { echo date('H:i'); } ?></label>
                </div>
            </div>
            <div class="col-lg-2">
                <div class="form-group">
                    <label>Data: <?php echo date('d/m/Y'); ?></label>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="form-group">
                    <label>Término do Evento: <?php if (isset($iAtendimentoEletivoId )) echo $HoraFim; ?></label>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="form-group">
                    <label>Previsão de Alta: <?php echo ''; ?></label>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-3">
                <div class="form-group">
                    <label>Tipo de internação: <?php echo $row['TpIntNome']; ?></label>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="form-group">
                    <label>Especialidade do leito: <?php echo $row['EsLeiNome']; ?></label>
                </div>
            </div>
            <div class="col-lg-2">
                <div class="form-group">
                    <label>Ala: <?php echo $row['AlaNome']; ?></label>
                </div>
            </div>
            <div class="col-lg-2">
                <div class="form-group">
                    <label>Quarto Nº: <?php echo $row['QuartNome']; ?></label>
                </div>
            </div>
            <div class="col-lg-2">
                <div class="form-group">
                    <label>Leito Nº: <?php echo $row['LeitoNome']; ?></label>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="form-group">
                    <label>Nº Conselho: <?php echo $rowUser['ProfiNumConselho'] .  ' - ' . $rowUser['ProfissionalNome'] . ' - CBO: ' . $rowUser['ProfissaoCbo']; ?> </label>
                </div>
            </div>
        </div>

    </div>
</div>