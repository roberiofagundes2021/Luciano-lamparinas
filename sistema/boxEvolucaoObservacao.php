<!-- históa de entrada -->
<div class="card">

    <div class="card-header header-elements-inline">
        <h3 class="card-title">EVOLUÇÃO DIÁRIA</h3>
    </div>

    <div class="card-body">

        <form id="formEvolucaoDiaria" name="formEvolucaoDiaria" method="post" class="form-validate-jquery">
            <input type="hidden" name="idEvolucao" id="idEvolucao">
            
            <div class="row mb-2">
                <div class="col-lg-3 mb-2 row">				
                    <div class="col-lg-12">
                        <label>Data/Hora <span class="text-danger">*</span></label>
                        <input type="datatime-local" class="form-control" name="dataHoraEvolucaoDiaria" id="dataHoraEvolucaoDiaria" value="<?php echo date('d/m/Y H:i');?>" readonly>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label for="evolucaoDiaria">Evolução Diária <span class="text-danger">*</span></label>
                        <textarea rows="5" cols="5" maxLength="500" id="evolucaoDiaria" name="evolucaoDiaria"  class="form-control" onInput="contarCaracteres(this);" placeholder="Corpo da evolução (informe aqui o texto que você queira que apareça na evolução diária)" ></textarea>
                        <small class="text-muted form-text">Max. 500 caracteres <span class="caracteresevolucaoDiaria"></span></small>
                    </div>
                </div>
            </div>
        </form>

        <div class="row">
            <div class="col-lg-12">
                <div class="form-group" style="padding-top:15px;">
                    <?php 
                        if (isset($_SESSION['SituaChave']) && $_SESSION['SituaChave'] != "ATENDIDO") {
                            echo " <button class='btn btn-lg btn-success' id='incluirEvolucaoDiaria' style='display: block;'  >Incluir</button>
                            <button class='btn btn-lg btn-success' id='salvarEdEvolucao'style='display: none;'>Salvar Alterações</button>";
                        }
                    ?>
                </div>
            </div>
        </div> 
    </div>

    <div class="row">
        <div class="col-lg-12">
            <table class="table" id="tblEvolucaoDiaria">
                <thead>
                    <tr class="bg-slate">
                        <th class="text-left">Item</th>
                        <th class="text-left">Data/ Hora</th>
                        <th class="text-left">Evolução Diária</th>
                        <th class="text-left">Profissional - CBO</th>
                        <th class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody id="dataEvolucaoDiaria">
                </tbody>
            </table>
        </div>		
    </div>							

</div>
