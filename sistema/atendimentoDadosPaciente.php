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
        <div class="row">
            <div class="col-lg-2">
                <div class="form-group">
                    <label for="inputDataInicio">Data Início</label>
                    <input type="text" id="inputDataInicio" name="inputDataInicio" class="form-control" value="<?php if (isset($iAtendimentoEletivoId )){ echo $DataAtendimentoInicio;} else { echo date('d/m/Y'); } ?>" readOnly> 
                </div>
            </div>
            <div class="col-lg-2">
                <div class="form-group">
                    <label for="inputInicio">Início do Atendimento</label>
                    <input type="text" id="inputInicio" name="inputInicio" class="form-control"  value="<?php if (isset($iAtendimentoEletivoId )){ echo $HoraInicio;} else { echo date('H:i'); } ?>" readOnly>
                </div>
            </div>
            <div class="col-lg-2">
                <div class="form-group">
                    <label for="inputDataFim">Data Fim</label>
                    <input type="text" id="inputDataFim" name="inputDataFim" class="form-control" value="<?php if (isset($iAtendimentoEletivoId )){ echo $DataAtendimentoFim;} else { echo date('d/m/Y'); } ?>" readOnly> 
                </div>
            </div>
            <div class="col-lg-2">
                <div class="form-group">
                    <label for="inputFim">Témino do Atendimento</label>
                    <input type="text" id="inputFim" name="inputFim" class="form-control" value="<?php if (isset($iAtendimentoEletivoId )) echo $HoraFim; ?>" readOnly>
                </div>
            </div>
            <div class="col-lg-1">
                <div class="form-group">
                    <label for="inputConselho"> Conselho </label>
                    <input type="text" id="inputConselho" name="inputConselho" class="form-control"  value="<?php echo $rowUser['PrConNome']; ?>" readOnly>
                </div>
            </div>
            <div class="col-lg-2">
                <div class="form-group">
                    <label for="inputProfissional">Profissional</label>
                    <input type="text" id="inputProfissional" name="inputProfissional" class="form-control"  value="<?php echo $rowUser['ProfissionalNome']; ?>" readOnly>
                </div>
            </div>
            <div class="col-lg-1">
                <div class="form-group">
                    <label for="inputCbo"> CBO </label>
                    <input type="text" id="inputCbo" name="inputCbo" class="form-control"  value="<?php echo $rowUser['ProfissaoCbo']; ?>" readOnly>
                </div>
            </div>
        </div>
    </div>
</div>