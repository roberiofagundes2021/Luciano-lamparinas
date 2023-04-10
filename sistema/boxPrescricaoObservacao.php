<!-- Dieta -->
<div class="card card-collapsed">
    
    <div class="card-header header-elements-inline">
        <h3 class="card-title">DIETA</h3>
        <div class="header-elements">
            <div class="list-icons">
                <a class="list-icons-item" data-action="collapse"></a>
            </div>
        </div>
    </div>

    <div class="card-body">

        <form id="formDieta" name="formDieta" method="post" class="form-validate-jquery">

            <input type="hidden" name="idDieta" id="idDieta">

            <div class="col-lg-12 mb-3 row">
                <!-- titulos -->
                <div class="col-lg-3">
                    <label>Data Inicial <span class="text-danger">*</span></label>
                </div>
                <div class="col-lg-3">
                    <label>Data Final <span class="text-danger">*</span></label>
                </div>
                <div class="col-lg-6">
                    <label>Tipo de Dieta <span class="text-danger">*</span></label>
                </div>
                <!-- campos -->										
                <div class="col-lg-3">
                    <input type="date" class="form-control" name="dataInicialDieta" id="dataInicialDieta" min="<?php echo date('Y-m-d') ?>">
                </div>
                <div class="col-lg-3">
                    <input type="date" class="form-control" name="dataFinalDieta" id="dataFinalDieta" min="<?php echo date('Y-m-d') ?>">
                </div>
                <div class="col-lg-6">
                    <select id="selTipoDeDieta" name="selTipoDeDieta" class="select-search" onChange="setDescricaoDieta()" >
                        <option value=''>Selecione</option>
                    </select>											
                </div>
            </div>

            <div class="col-lg-12 mb-3 row">
                <!-- titulos -->
                <div class="col-lg-3">
                    <label>Via <span class="text-danger">*</span></label>
                </div>
                <div class="col-lg-3">
                    <label>Frequência <span class="text-danger">*</span></label>
                </div>
                <div class="col-lg-3">
                    <label>Tipo de Aprazamento <span class="text-danger">*</span></label>
                </div>
                <div class="col-lg-3">											
                </div>
                <!-- campos -->										
                <div class="col-lg-3">
                    <select id="selViaDieta" name="selViaDieta" class="select-search" >
                        <option value=''>Selecione</option>
                    </select>
                </div>
                <div class="col-lg-3">											
                    <input type="text" id="freqDieta" name="freqDieta" class="form-control" onChange="setDescricaoDieta()">
                </div>
                <div class="col-lg-3">
                    <select id="selTipoAprazamentoDieta" name="selTipoAprazamentoDieta" class="select-search" onChange="setDescricaoDieta()">
                        <option value=''>Selecione</option>
                    </select>
                </div>
                <div class="col-lg-3">
                    <div class="form-check form-check-inline" style="margin-top: 10px;">
                        <label class="form-check-label">
                            <input type="checkbox" class="form-check-input" id="checkBombaInfusaoDieta" name="checkBombaInfusaoDieta" onclick="setDescricaoDieta()" >
                            Bomba de Infusão
                        </label>
                    </div>																						
                </div>
            </div>

            
            <div class="row" style="margin-top: 20px">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label for="justificativa">Descrição da Dieta <span class="text-danger">*</span></label>
                        <textarea rows="5" cols="5" maxLength="350" id="descricaoDieta" name="descricaoDieta" onInput="contarCaracteres(this);" class="form-control" placeholder="Digite aqui o texto da Posologia." ></textarea>
                        <small class="text-muted form-text">Max. 350 caracteres <span class="caracteresdescricaoDieta"></span></small>
                    </div>
                </div>
            </div>

        </form>


        <div class="row">
            <div class="col-lg-12">
                <div class="form-group" style="padding-top:15px;">
                    <?php 
                        if (isset($_SESSION['SituaChave']) && $_SESSION['SituaChave'] != "ATENDIDO") {
                            echo "<button class='btn btn-lg btn-success' id='adicionarDieta'  >Adicionar</button>";
                        }
                    ?>
                    <button class="btn btn-lg btn-success" id="salvarEdDieta" style="display: none;"  >Salvar Alterações</button>
                </div>
            </div>
        </div> 

    </div>

    <div class="row">
        <div class="col-lg-12">
            <table class="table" id="tblDieta">
                <thead>
                    <tr class="bg-slate">
                        <th class="text-left">Item</th>
                        <th class="text-left">Data Inicial</th>
                        <th class="text-left">Tipo da Dieta</th>
                        <th class="text-left">Descrição da Dieta</th>
                        <th class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody id="dataDieta">
                </tbody>
            </table>
        </div>		
    </div>

</div>

<!-- medicamentos -->
<div class=" card card-collapsed " >

    <div class="card-header header-elements-inline">
        <h3 class="card-title">MEDICAMENTOS</h3>
        <div class="header-elements">
            <div class="list-icons">
                <a class="list-icons-item" data-action="collapse"></a>
            </div>
        </div>
    </div>

    <div class="card-body">

        <form id="formMedicamentos" name="formMedicamentos" method="post" class="form-validate-jquery">
        
        <input type="hidden" name="idMedicamentos" id="idMedicamentos">

            <div class="col-lg-12 mb-4 row">
                <!-- titulos -->
                <div class="col-lg-6">
                    <label>Medicamentos (Em Estoque)</label>
                </div>
                <div class="col-lg-6">
                    <label>Medicamentos (Digitação Livre)</label>
                </div>
                <!-- campos -->										
                <div class="col-lg-5">
                    <input type="text" class="form-control" name="nomeMedicamentoEstoqueMedicamentos" id="nomeMedicamentoEstoqueMedicamentos" onChange="setDescricaoPosologiaMed()" readonly>											
                    <input type="hidden" class="form-control" name="medicamentoEstoqueMedicamentos" id="medicamentoEstoqueMedicamentos">
                </div>
                <div class="col-lg-1" style="margin-top: -5px;">
                    <button class="btn btn-lg btn-principal" onClick="pesquisarMedicamento('MEDICAMENTO'); return false;">
                        <i class='icon-search4' title='Pesquisar Medicamento'></i>
                    </button>
                </div>
                <div class="col-lg-6">
                    <input type="text" class="form-control" name="medicamentoDlMedicamentos" id="medicamentoDlMedicamentos" onChange="setDescricaoPosologiaMed()">										
                </div>
            </div>

            <div class="col-lg-3 mb-4 row">
                <!-- titulos -->
                <div class="col-lg-12">
                    <label>Via<span class="text-danger">*</span></label>
                </div>
                <!-- campos -->										
                <div class="col-lg-11">
                    <select id="selViaMedicamentos" name="selViaMedicamentos" class="select-search" onChange="setDescricaoPosologiaMed()">
                        <option value=''>Selecione</option>
                    </select>
                </div>
                <div class="col-lg-1" style="margin-top: -5px;">
                    <a class="btn btn-lg btn-principal abrirCalculadora">
                        <i class='icon-calculator' title='Calculadora'></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-12 mb-4 row">
                <!-- titulos -->
                <div class="col-lg-2">
                    <label>Dose <span class="text-danger">*</span></label>
                </div>
                <div class="col-lg-2">
                    <label>Unidade <span class="text-danger">*</span></label>
                </div>
                <div class="col-lg-2">
                    <label>Frequência <span class="text-danger">*</span></label>
                </div>
                <div class="col-lg-4">
                    <label>Tipo de Aprazamento <span class="text-danger">*</span></label>
                </div>
                <div class="col-lg-2">
                    <label>Data Início Tratamento</label>
                </div>
                <!-- campos -->										
                <div class="col-lg-2">
                    <input type="text" class="form-control" name="doseMedicamentos" id="doseMedicamentos" onChange="setDescricaoPosologiaMed()">
                </div>
                <div class="col-lg-2">
                    <select id="selUnidadeMedicamentos" name="selUnidadeMedicamentos" class="select-search"  onChange="setDescricaoPosologiaMed()">
                        <option value=''>Selecione</option>
                    </select>											
                </div>
                <div class="col-lg-2">
                    <input type="text" class="form-control" name="frequenciaMedicamentos" id="frequenciaMedicamentos" onChange="setDescricaoPosologiaMed()">											
                </div>
                <div class="col-lg-4">
                    <select id="selTipoAprazamentoMedicamentos" name="selTipoAprazamentoMedicamentos" class="select-search" onChange="setDescricaoPosologiaMed()" >
                        <option value=''>Selecione</option>
                    </select>											
                </div>
                <div class="col-lg-2">
                    <input type="date" class="form-control" name="dataInicioMedicamentos" id="dataInicioMedicamentos" min="<?php echo date('Y-m-d') ?>">									
                </div>
            </div>
        
            <div class="col-lg-12 mb-4 row">
                <!-- titulos -->
                <div class="col-lg-2">
                </div>
                <div class="col-lg-2">
                </div>
                <div class="col-lg-2">
                    <label>Hora Início Adm</label>
                </div>
                <div class="col-lg-1">
                </div>
                <div class="col-lg-5">
                    <label>Complemento</label>
                </div>

                <!-- campos -->										
                <div class="col-lg-2">													
                    <div class="form-check form-check-inline" style="margin-top: 10px;">
                        <label class="form-check-label">
                            <input type="checkbox" class="form-check-input" id="checkBombaInfusaoMedicamentos" name="checkBombaInfusaoMedicamentos" onChange="setDescricaoPosologiaMed()" >
                            Bomba de Infusão
                        </label>
                    </div>																		
                </div>
                <div class="col-lg-2">
                    <div class="form-check form-check-inline" style="margin-top: 10px;">
                        <label class="form-check-label">
                            <input type="checkbox" class="form-check-input" id="checkInicioAdmMedicamentos" name="checkInicioAdmMedicamentos" >
                            Início Adm
                        </label>
                    </div>																								
                </div>
                <div class="col-lg-2">
                    <input type="time" class="form-control" name="horaInicioAdmMedicamentos" id="horaInicioAdmMedicamentos">										
                </div>
                <div class="col-lg-1">	
                    <div class="form-check form-check-inline form-check-right" style="margin-top: 10px;">
                        <label class="form-check-label">
                            SN
                            <input type="checkbox" class="form-check-input" id="snMedicamentos" >
                        </label>
                    </div>
                </div>
                <div class="col-lg-5">
                    <input type="text" class="form-control" name="complementoMedicamentos" id="complementoMedicamentos" onChange="setDescricaoPosologiaMed()" disabled>										
                </div>
            </div>				
            
            <div class="row" style="margin-top: 20px">
                <div class="col-lg-7">
                    <div class="form-group">
                        <label for="justificativa">Descrição da Posologia <span class="text-danger">*</span></label>
                        <textarea rows="5" cols="5" maxLength="350" id="descricaoPosologiaMedicamentos" name="descricaoPosologiaMedicamentos" onInput="contarCaracteres(this);" onChange="contarCaracteres(this);" class="form-control" placeholder="Digite aqui o texto da Posologia." ></textarea>
                        <small class="text-muted form-text">Max. 350 caracteres <span class="caracteresdescricaoPosologiaMedicamentos"></span></small>
                    </div>
                </div>

                <div class=" row col-lg-5">

                    <div class="col-lg-6">
                        <label>Validade de Prescrição:</label>
                        <input type="datetime-local" class="form-control" name="validadeInicioMedicamentos" id="validadeInicioMedicamentos" min="<?php echo date('Y-m-d') ?>">
                    </div>

                    <div class="col-lg-6">
                        <label>&nbsp;</label>
                        <input type="datetime-local" class="form-control" name="validadeFimMedicamentos" id="validadeFimMedicamentos" min="<?php echo date('Y-m-d') ?>">
                    </div>

                </div>
            </div>
            
        </form>

        <div class="row">
            <div class="col-lg-12">
                <div class="form-group" style="padding-top:15px;">
                    
                    <?php 
                        if (isset($_SESSION['SituaChave']) && $_SESSION['SituaChave'] != "ATENDIDO") {
                            echo "<button class='btn btn-lg btn-success' id='adicionarMedicamento'  >Adicionar</button>";
                        }
                    ?>
                    <button class="btn btn-lg btn-success" id="salvarEdMedicamento" style="display: none;"  >Salvar Alterações</button>
                </div>
            </div>
        </div> 

    </div>

</div>

<!-- Soluções -->
<div class="card card-collapsed">

    <div class="card-header header-elements-inline">
        <h3 class="card-title">SOLUÇÕES</h3>
        <div class="header-elements">
            <div class="list-icons">
                <a class="list-icons-item" data-action="collapse"></a>
            </div>
        </div>
    </div>

    <div class="card-body">

        <form id="formSolucoes" name="formSolucoes" method="post" class="form-validate-jquery">

            <input type="hidden" name="idSolucoes" id="idSolucoes">

            <div class="col-lg-12 mb-4 row">
                <!-- titulos -->
                <div class="col-lg-6">
                    <label>Medicamentos (Em Estoque) </label>
                </div>
                <div class="col-lg-6">
                    <label>Medicamentos (Digitação Livre) </label>
                </div>
                <!-- campos -->										
                <div class="col-lg-5">
                    <input type="text" name="nomeMedicamentoEstoqueSolucoes" id="nomeMedicamentoEstoqueSolucoes" class="form-control" onChange="setDescricaoPosologiaSol()" readonly>
                    <input type="hidden" class="form-control" name="medicamentoEstoqueSolucoes" id="medicamentoEstoqueSolucoes" onChange="setDescricaoPosologiaSol()">
                </div>
                <div class="col-lg-1" style="margin-top: -5px;">
                    <button class="btn btn-lg btn-principal" onClick="pesquisarMedicamento('SOLUCAO'); return false;">
                        <i class='icon-search4' title='Pesquisar Medicamento'></i>
                    </button>
                </div>
                <div class="col-lg-6">
                    <input type="text" class="form-control" name="medicamentoDlSolucoes" id="medicamentoDlSolucoes" onChange="setDescricaoPosologiaSol()">											
                </div>
                </div>

            <div class="col-lg-3 mb-3 row">
                <!-- titulos -->
                <div class="col-lg-12">
                    <label>Via<span class="text-danger">*</span></label>
                </div>
                <!-- campos -->										
                <div class="col-lg-11">
                    <select id="selViaSolucoes" name="selViaSolucoes" class="select-search" >
                        <option value=''>Selecione</option>
                    </select>
                </div>
                <div class="col-lg-1" style="margin-top: -5px;">
                    <a class="btn btn-lg btn-principal abrirCalculadora">
                        <i class='icon-calculator' title='Calculadora'></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-12 mb-3 row">
                <!-- titulos -->
                <div class="col-lg-2">
                    <label>Dose <span class="text-danger">*</span></label>
                </div>
                <div class="col-lg-2">
                    <label>Unidade <span class="text-danger">*</span></label>
                </div>
                <div class="col-lg-2">
                    <label>Frequência <span class="text-danger">*</span></label>
                </div>
                <div class="col-lg-4">
                    <label>Tipo de Aprazamento <span class="text-danger">*</span></label>
                </div>
                <div class="col-lg-2">
                    <label>Data Início Tratamento</label>
                </div>
                <!-- campos -->										
                <div class="col-lg-2">
                    <input type="text" class="form-control" name="doseSolucoes" id="doseSolucoes" onChange="setDescricaoPosologiaSol()">
                </div>
                <div class="col-lg-2">
                    <select id="selUnidadeSolucoes" name="selUnidadeSolucoes" class="select-search" onChange="setDescricaoPosologiaSol()">
                        <option value=''>Selecione</option>
                    </select>											
                </div>
                <div class="col-lg-2">
                    <input type="text" class="form-control" name="frequenciaSolucoes" id="frequenciaSolucoes" onChange="setDescricaoPosologiaSol()">											
                </div>
                <div class="col-lg-4">
                    <select id="selTipoAprazamentoSolucoes" name="selTipoAprazamentoSolucoes" class="select-search"onChange="setDescricaoPosologiaSol()" >
                        <option value=''>Selecione</option>
                    </select>											
                </div>
                <div class="col-lg-2">
                    <input type="date" class="form-control" name="dataInicioSolucoes" id="dataInicioSolucoes" min="<?php echo date('Y-m-d') ?>">										
                </div>
            </div>

            <div class="col-lg-12 mb-3 row">
                <!-- titulos -->
                <div class="col-lg-4">
                    <label>Diluente (Em Estoque)</label>
                </div>
                <div class="col-lg-2">
                    <label>Volume (ml)</label>
                </div>
                <div class="col-lg-2">
                    <label>Correr em</label>
                </div>
                <div class="col-lg-2">
                    <label>Unidade de Tempo</label>
                </div>
                <div class="col-lg-2">
                    <label>Velocidade de Infusão</label>
                </div>
                <!-- campos -->										
                <div class="col-lg-3">
                    <input type="text" name="nomeDiluenteSolucoes" id="nomeDiluenteSolucoes" class="form-control" onChange="setDescricaoPosologiaSol()" readonly>
                    <input type="hidden" class="form-control" name="diluenteSolucoes" id="diluenteSolucoes" onChange="setDescricaoPosologiaSol()">	
                </div>
                <div class="col-lg-1" style="margin-top: -5px;">
                    <button class="btn btn-lg btn-principal" onClick="pesquisarMedicamento('SOLUCAODILUENTE'); return false;">
                        <i class='icon-search4' title='Pesquisar Medicamento'></i>
                    </button>
                </div>
                <div class="col-lg-2">
                    <input type="text" class="form-control" name="volumeSolucoes" id="volumeSolucoes" onChange="setDescricaoPosologiaSol()">										
                </div>
                <div class="col-lg-2">
                    <input type="text" class="form-control" name="correrEmSolucoes" id="correrEmSolucoes" onChange="setDescricaoPosologiaSol()">											
                </div>
                <div class="col-lg-2">
                    <select id="selUnTempoSolucoes" name="selUnTempoSolucoes" class="select-search" onChange="setDescricaoPosologiaSol()" >
                        <option value=''>Selecione</option>
                        <option value='Hora(s)'>Hora(s)</option>
                        <option value='Minuto(s)'>Minuto(s)</option>
                        <option value='Dia(s)'>Dia(s)</option>
                        <option value='Semana(s)'>Semana(s)</option>
                        <option value='Agora'>Agora</option>
                        <option value='A critério médico'>A critério médico</option>
                    </select>											
                </div>
                <div class="col-lg-2">
                    <input type="text" class="form-control" name="velocidadeInfusaoSolucoes" id="velocidadeInfusaoSolucoes" onChange="setDescricaoPosologiaSol()">											
                </div>
            </div>
        
            <div class="col-lg-12 mb-3 row">
                <!-- titulos -->
                <div class="col-lg-2">
                </div>
                <div class="col-lg-2">
                </div>
                <div class="col-lg-2">
                    <label>Hora Início Adm</label>
                </div>
                <div class="col-lg-1">
                </div>
                <div class="col-lg-5">
                    <label>Complemento</label>
                </div>

                <!-- campos -->										
                <div class="col-lg-2">	
                    <div class="form-check form-check-inline" style="margin-top: 10px;">
                        <label class="form-check-label">
                            <input type="checkbox" class="form-check-input" id="checkBombaInfusaoSolucoes" name="checkBombaInfusaoSolucoes" onChange="setDescricaoPosologiaSol()">
                            Bomba de Infusão
                        </label>
                    </div>											
                </div>
                <div class="col-lg-2">	
                    <div class="form-check form-check-inline" style="margin-top: 10px;">
                        <label class="form-check-label">
                            <input type="checkbox" class="form-check-input" id="checkInicioAdmSolucoes" name="checkInicioAdmSolucoes">
                            Início Adm
                        </label>
                    </div>																					
                </div>
                <div class="col-lg-2">
                    <input type="time" class="form-control" name="horaInicioAdmSolucoes" id="horaInicioAdmSolucoes">											
                </div>
                <div class="col-lg-1">
                    <div class="form-check form-check-inline form-check-right" style="margin-top: 10px;">
                        <label class="form-check-label">
                            SN
                            <input type="checkbox" class="form-check-input" id="snSolucoes" >
                        </label>
                    </div>							
                </div>
                <div class="col-lg-5">
                    <input type="text" class="form-control" name="complementoSolucoes" id="complementoSolucoes" onChange="setDescricaoPosologiaSol()" disabled>										
                </div>
            </div>				
            
            <div class="row" style="margin-top: 20px">
                <div class="col-lg-7">
                    <div class="form-group">
                        <label for="justificativa">Descrição da Posologia <span class="text-danger">*</span></label>
                        <textarea rows="5" cols="5" maxLength="350" id="descricaoPosologiaSolucoes" name="descricaoPosologiaSolucoes" onInput="contarCaracteres(this);" class="form-control" placeholder="Digite aqui o texto da Posologia." ></textarea>
                        <small class="text-muted form-text">Max. 350 caracteres <span class="caracteresdescricaoPosologiaSolucoes"></span></small>
                    </div>
                </div>

                <div class=" row col-lg-5">

                    <div class="col-lg-6">
                        <label>Validade de Prescrição:</label>
                        <input type="datetime-local" class="form-control" name="validadeInicioSolucoes" id="validadeInicioSolucoes" min="<?php echo date('Y-m-d') ?>">
                    </div>

                    <div class="col-lg-6">
                        <label>&nbsp;</label>
                        <input type="datetime-local" class="form-control" name="validadeFimSolucoes" id="validadeFimSolucoes" min="<?php echo date('Y-m-d') ?>">
                    </div>

                </div>

            </div>
        </form>

        <div class="row">
            <div class="col-lg-12">
                <div class="form-group" style="padding-top:15px;">
                    <?php 
                        if (isset($_SESSION['SituaChave']) && $_SESSION['SituaChave'] != "ATENDIDO") {
                            echo "<button class='btn btn-lg btn-success' id='adicionarSolucao'  >Adicionar</button>";
                        }
                    ?>
                        <button class="btn btn-lg btn-success" id="salvarEdSolucao" style="display: none;"  >Salvar Alterações</button>
                </div>
            </div>
        </div> 

    </div>

    

</div>

<!-- tabela medicametos -->
<div class="card" >

    <div class="row">
        <div class="col-lg-12">
            <table class="table" id="tblMedicamentosSolucoes">
                <thead>
                    <tr class="bg-slate">
                        <th class="text-left">Item</th>
                        <th class="text-left">Data Início Tratamento</th>
                        <th class="text-left">Medicamento</th>
                        <th class="text-left">Via</th>
                        <th class="text-left">Posologia</th>
                        <th class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody id="dataMedicamentosSolucoes">
                </tbody>
            </table>
        </div>		
    </div>

</div>

<!-- Cuidados/Procedimentos -->
<div class="card card-collapsed ">
    
    <div class="card-header header-elements-inline">
        <h3 class="card-title">CUIDADOS/PROCEDIMENTOS</h3>
        <div class="header-elements">
            <div class="list-icons">
                <a class="list-icons-item" data-action="collapse"></a>
            </div>
        </div>
    </div>

    <div class="card-body">

        <form id="formCuidados" name="formCuidados" method="post" class="form-validate-jquery">

        <input type="hidden" name="idCuidado" id="idCuidado">
            
            <div class="col-lg-12 mb-3 row">
                <!-- titulos -->
                <div class="col-lg-3">
                    <label>Data Inicial <span class="text-danger">*</span></label>
                </div>
                <div class="col-lg-3">
                    <label>Data Final</label>
                </div>
                <div class="col-lg-6">
                    <label>Tipo do Cuidado/Procedimento <span class="text-danger">*</span></label>
                </div>
                <!-- campos -->										
                <div class="col-lg-3">
                    <input type="date" class="form-control" name="dataInicialCuidados" id="dataInicialCuidados" min="<?php echo date('Y-m-d') ?>">
                </div>
                <div class="col-lg-3">
                    <input type="date" class="form-control" name="dataFinalCuidados" id="dataFinalCuidados" min="<?php echo date('Y-m-d') ?>">
                </div>
                <div class="col-lg-6">
                    <select id="selTipoDeCuidado" name="selTipoDeCuidado" class="select-search" onChange="setDescricaoCuidados()">
                        <option value=''>Selecione</option>
                    </select>											
                </div>
            </div>

            <div class="col-lg-12 mb-3 row">
                <!-- titulos -->
                <div class="col-lg-3">
                    <label>Frequência <span class="text-danger">*</span></label>
                </div>
                <div class="col-lg-3">
                    <label>Tipo de Aprazamento <span class="text-danger">*</span></label>
                </div>
                <div class="col-lg-1">											
                </div>
                <div class="col-lg-5">
                    <label>Complemento</label>
                </div>

                <!-- campos -->										
                <div class="col-lg-3">
                    <input type="text" class="form-control" name="frequenciaCuidados" id="frequenciaCuidados" onChange="setDescricaoCuidados()">
                </div>
                <div class="col-lg-3">
                    <select id="selTipoAprazamentoCuidados" name="selTipoAprazamentoCuidados" class="select-search" onChange="setDescricaoCuidados()" >
                        <option value=''>Selecione</option>
                    </select>
                </div>
                <div class="col-lg-1">
                    <div class="form-check form-check-inline form-check-right" style="margin-top: 10px;">
                        <label class="form-check-label">
                            SN
                            <input type="checkbox" class="form-check-input" id="snCuidados">
                        </label>
                    </div>																						
                </div>
                <div class="col-lg-5">
                    <input type="text" class="form-control" name="complementoCuidados" id="complementoCuidados" onChange="setDescricaoCuidados()" disabled>
                </div>
            </div>
            
            <div class="row" style="margin-top: 20px">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label for="justificativa">Descrição dos Cuidados/Procedimentos <span class="text-danger">*</span></label>
                        <textarea rows="5" cols="5" maxLength="350" id="descricaoCuidados" name="descricaoCuidados" onInput="contarCaracteres(this);" onChange="contarCaracteres(this);" class="form-control" placeholder="Digite aqui o texto da Posologia." ></textarea>
                        <small class="text-muted form-text">Max. 350 caracteres <span class="caracteresdescricaoCuidados"></span></small>
                    </div>
                </div>
            </div>									
            
        </form>

        <div class="row">
            <div class="col-lg-12">
                <div class="form-group" style="padding-top:10px;">
                    <?php 
                        if (isset($_SESSION['SituaChave']) && $_SESSION['SituaChave'] != "ATENDIDO") {
                            echo "<button class='btn btn-lg btn-success' id='adicionarCuidado'  >Adicionar</button>";
                        }
                    ?>
                    <button class="btn btn-lg btn-success" id="salvarEdCuidado" style="display: none;" >Salvar Alterações</button>
                </div>
            </div>
        </div> 

    </div>

    <div class="row">
        <div class="col-lg-12">
            <table class="table" id="tblCuidados">
                <thead>
                    <tr class="bg-slate">
                        <th class="text-left">Item</th>
                        <th class="text-left">Data/ Hora</th>
                        <th class="text-left">Tipo de Cuidado</th>
                        <th class="text-left">Descrição do cuidado</th>
                        <th class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody id="dataCuidados">
                </tbody>
            </table>
        </div>		
    </div>

</div>
