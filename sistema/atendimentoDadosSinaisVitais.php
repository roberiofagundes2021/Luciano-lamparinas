<?php

$sql = "SELECT TOP(1) AtTriId
FROM AtendimentoTriagem
WHERE AtTriAtendimento = $iAtendimentoId
ORDER BY AtTriId DESC";
$result = $conn->query($sql);
$rowTriagem= $result->fetch(PDO::FETCH_ASSOC);

$iAtendimentoTriagemId = $rowTriagem?$rowTriagem['AtTriId']:null;

if(isset($iAtendimentoTriagemId ) && $iAtendimentoTriagemId ){
	//Essa consulta é para preencher o campo Triagem ao editar
	$sql = "SELECT *
			FROM AtendimentoTriagem
			WHERE AtTriId = " . $iAtendimentoTriagemId ;
	$result = $conn->query($sql);
	$rowTriagem = $result->fetch(PDO::FETCH_ASSOC);
	$_SESSION['msg'] = array();
}

?>

<script>

    window.onload = function(){
        //Ao carregar a página é verificado se é Sim ou Não para aparecer a descrição ou esconder
        var tipo = $('input[name="inputAlergia"]:checked').val();
        var tipo1 = $('input[name="inputDiabetes"]:checked').val();
        var tipo2 = $('input[name="inputHipertensao"]:checked').val();
        var tipo3 = $('input[name="inputNeoplasia"]:checked').val();
        var tipo4 = $('input[name="inputUsoMedicamento"]:checked').val();

        selecionaAlergiaDescricao(tipo);
        selecionaDiabeteDescricao(tipo1);        
        selecionaHipertensaoDescricao(tipo2);        
        selecionaNeoplasiaDescricao(tipo3);        
        selecionaUsoMedicamentoDescricao(tipo4);
    }

    function getSinaisVitais() {

        $.ajax({
            type: 'POST',
            url: 'filtraAtendimento.php',
            dataType: 'json',
            data:{
                'tipoRequest': 'GETSINAISVITAIS',
                'id' : <?php echo isset($iAtendimentoTriagemId) ? $iAtendimentoTriagemId : 'null'; ?>
            },
            success: function(response) {    

                $('#inputSistolica').val(response.AtTriPressaoSistolica)
                $('#inputDiatolica').val(response.AtTriPressaoDiatolica)
                $('#inputCardiaca').val(response.AtTriFreqCardiaca)
                $('#inputRespiratoria').val(response.AtTriFreqRespiratoria)
                $('#inputTemperatura').val(response.AtTriTempAXI)
                $('#inputSPO').val(response.AtTriSPO)
                $('#inputHGT').val(response.AtTriHGT)

            }
        });
        
    }

    function selecionaAlergiaDescricao(tipo) {
        if (tipo == 1){
            document.getElementById('dadosAlergia').style.display = "block";	
        } else {			
            document.getElementById('dadosAlergia').style.display = "none";		
        }
    }

    function selecionaDiabeteDescricao(tipo1) {
        if (tipo1 == 1){	
            document.getElementById('dadosDiabete').style.display = "block";
        } else {						
            document.getElementById('dadosDiabete').style.display = "none";
        }
    }
        
    function selecionaHipertensaoDescricao(tipo2) {
        if (tipo2 == 1){	
            document.getElementById('dadosHipertencao').style.display = "block";
        } else {						
            document.getElementById('dadosHipertencao').style.display = "none";
        }
    }

    function selecionaNeoplasiaDescricao(tipo3) {
        if (tipo3 == 1){	
            document.getElementById('dadosNeoplasia').style.display = "block";
        } else {						
            document.getElementById('dadosNeoplasia').style.display = "none";
        }
    }

    function selecionaUsoMedicamentoDescricao(tipo4) {
        if (tipo4 == 1){	
            document.getElementById('dadosMedicamento').style.display = "block";
        } else {						
            document.getElementById('dadosMedicamento').style.display = "none";
        }
    }

</script>


<div class="card card-collapsed">
    <div class="card-header header-elements-inline">
        <h3 class="card-title font-weight-bold">Sinais Vitais</h3>

        <div class="header-elements">
            <div class="list-icons">
                <a class="list-icons-item" data-action="collapse"></a>
            </div>
        </div>
    </div>

    <div class="card-body">

        <div class="row" >
            <div class="col-lg-2">
                <div class="form-group">
                    <label for="inputPressaoArterial">PAS <span class="">(mmHg)</span>&nbsp;&nbsp;&nbsp; PAD<span class="">(mmHg)</span></label>
                    <div class="input-group">
                        <input type="number" id="inputSistolica" name="inputSistolica" class="form-control" placeholder="" value="<?php if (isset($iAtendimentoTriagemId )) echo $rowTriagem['AtTriPressaoSistolica']; ?>">
                        <span class="input-group-prepend">
                            <span class="input-group-text">X</span>	
                        </span>
                        <input type="number" id="inputDiatolica" name="inputDiatolica" class="form-control" placeholder="" value="<?php if (isset($iAtendimentoTriagemId )) echo $rowTriagem['AtTriPressaoDiatolica']; ?>">
                    </div>
                </div>
            </div>
            
            <div class="col-lg-2" style="margin-right: 10px;">
                <div class="form-group">
                    <label for="inputCardiaca">FC <span class="">(bpm)</span></label>
                    <div class="input-group">
                        <input type="number" id="inputCardiaca" name="inputCardiaca" class="form-control" placeholder="" value="<?php if (isset($iAtendimentoTriagemId )) echo $rowTriagem['AtTriFreqCardiaca']; ?>">
                    </div>
                </div>
            </div>
            <div class="col-lg-2" style="margin-right: 20px;">
                <div class="form-group">
                    <label for="inputRespiratoria">FR <span class="">(rpm)</span></label>
                    <div class="input-group">												
                        <input type="number" onKeyUp="" id="inputRespiratoria" name="inputRespiratoria" class="form-control" placeholder="" value="<?php if (isset($iAtendimentoTriagemId )) echo $rowTriagem['AtTriFreqRespiratoria']; ?>">
                    </div>
                </div>
            </div>
            
            <div class="col-lg-2" style="margin-right: 10px;">
                <div class="form-group">
                    <label for="inputTemperatura">Temperatura <span class="">(ºC)</span></label>
                    <div class="input-group">
                    <input type="number" id="inputTemperatura" name="inputTemperatura" class="form-control" placeholder="" value="<?php if (isset($iAtendimentoTriagemId )) echo $rowTriagem['AtTriTempAXI']; ?>">
                        <span class="input-group-prepend">
                            <span class="input-group-text"><img src="global_assets/images/lamparinas/thermometro.png" width="32" style="margin-top: -13px;" alt="Termometro" /></span>
                        </span>
                        
                    </div>
                </div>
            </div>
            <div class="col-lg-1" style="margin-right: 20px;">
                <div class="form-group">
                    <label for="inputSPO">SPO<sub>2</sub> <span class="">(%)</span></label>
                    <div class="input-group">
                        <input type="number" onKeyUp="" id="inputSPO" name="inputSPO" class="form-control" placeholder="" value="<?php if (isset($iAtendimentoTriagemId )) echo $rowTriagem['AtTriSPO']; ?>">
                    </div>
                </div>
            </div>
            <div class="col-lg-1">
                <div class="form-group">
                <label for="inputHGT">HGT <span class="">(mg/dl)</span></label>
                    <input type="number" id="inputHGT" name="inputHGT" class="form-control" placeholder="" value="<?php if (isset($iAtendimentoTriagemId )) echo $rowTriagem['AtTriHGT']; ?>">
                </div>
            </div>
            <div class="col-lg-1">
                <div class="form-group">
                <label for="inputPeso">Peso <span class="">(Kg)</span></label>
                    <input type="number" id="inputPeso" name="inputPeso" class="form-control" onKeyUp="moeda(this);" placeholder="" value="<?php if (isset($iAtendimentoTriagemId )) echo $rowTriagem['AtTriPeso']; ?>">
                </div>
            </div>
        </div>

    </div>

    <div class="card-body" style='border-top: 0px;'>         

        <div class="row" style='justify-content: space-between;'>
            <div class="col-lg-2"  style="margin-right: 20px;">
                <label for="inputAlergia">Alergia</label>
                <div class="form-group">							
                    <div class="form-check form-check-inline">
                        <label class="form-check-label">
                            <input type="radio" id="inputAlergia" name="inputAlergia" value="1" class="form-input-styled" data-fouc onclick="selecionaAlergiaDescricao('1')" <?php if (isset($iAtendimentoTriagemId )) { if ($rowTriagem['AtTriAlergia'] == 1) echo "checked"; }?>>
                            Sim
                        </label>                     
                    </div>                                              
                    
                    <div class="form-check form-check-inline">
                        <label class="form-check-label">
                            <input type="radio" id="inputAlergia" name="inputAlergia" value="0" class="form-input-styled" data-fouc onclick="selecionaAlergiaDescricao('0')" <?php if (isset($iAtendimentoTriagemId )) { if ($rowTriagem['AtTriAlergia'] == 0) echo "checked"; }else{ echo "checked"; }?>>
                            Não
                        </label>
                    </div>										
                </div>									
            </div>
            <div class="col-lg-2"  style="margin-right: 20px;">
                <label for="inputDiabetes">Diabetes</label>
                <div class="form-group">							
                    <div class="form-check form-check-inline">
                        <label class="form-check-label">
                            <input type="radio" id="inputDiabetes" name="inputDiabetes" value="1" class="form-input-styled" data-fouc onclick="selecionaDiabeteDescricao('1')" <?php if (isset($iAtendimentoTriagemId )) { if ($rowTriagem['AtTriDiabetes'] == 1) echo "checked"; }?>>
                            Sim
                        </label>
                    </div>
                    <div class="form-check form-check-inline">
                        <label class="form-check-label">
                            <input type="radio" id="inputDiabetes" name="inputDiabetes" value="0" class="form-input-styled" data-fouc onclick="selecionaDiabeteDescricao('0')" <?php if (isset($iAtendimentoTriagemId )) { if ($rowTriagem['AtTriDiabetes'] == 0) echo "checked"; }else{ echo "checked"; }?>>
                            Não
                        </label>
                    </div>										
                </div>									
            </div>
            <div class="col-lg-2"  style="margin-right: 20px;">
                <label for="inputHipertensao">Hipertensão</label>
                <div class="form-group">							
                    <div class="form-check form-check-inline">
                        <label class="form-check-label">
                            <input type="radio" id="inputHipertensao" name="inputHipertensao" value="1" class="form-input-styled" data-fouc onclick="selecionaHipertensaoDescricao('1')" <?php if (isset($iAtendimentoTriagemId )) { if ($rowTriagem['AtTriHipertensao'] == 1) echo "checked"; }?>>
                            Sim
                        </label>
                    </div>
                    <div class="form-check form-check-inline">
                        <label class="form-check-label">
                            <input type="radio" id="inputHipertensao" name="inputHipertensao" value="0" class="form-input-styled" data-fouc  onclick="selecionaHipertensaoDescricao('0')" <?php if (isset($iAtendimentoTriagemId )) { if ($rowTriagem['AtTriHipertensao'] == 0) echo "checked"; }else{ echo "checked"; }?>>
                            Não
                        </label>
                    </div>										
                </div>									
            </div>
            <div class="col-lg-2"  style="margin-right: 20px;">
                <label for="inputNeoplasia">Neoplasia</label>
                <div class="form-group">							
                    <div class="form-check form-check-inline">
                        <label class="form-check-label">
                            <input type="radio" id="inputNeoplasia" name="inputNeoplasia" value="1" class="form-input-styled" data-fouc onclick="selecionaNeoplasiaDescricao('1')" <?php if (isset($iAtendimentoTriagemId )) { if ($rowTriagem['AtTriNeoplasia'] == 1) echo "checked"; }?>>
                            Sim
                        </label>
                    </div>
                    <div class="form-check form-check-inline">
                        <label class="form-check-label">
                            <input type="radio" id="inputNeoplasia" name="inputNeoplasia" value="0" class="form-input-styled" data-fouc onclick="selecionaNeoplasiaDescricao('0')" <?php if (isset($iAtendimentoTriagemId )) { if ($rowTriagem['AtTriNeoplasia'] == 0) echo "checked"; }else{ echo "checked"; }?>>
                            Não
                        </label>
                    </div>										
                </div>									
            </div>
            <div class="col-lg-2">
                <label for="inputUsoMedicamento">Uso de medicamento</label>
                <div class="form-group">							
                    <div class="form-check form-check-inline">
                        <label class="form-check-label">
                            <input type="radio" id="inputUsoMedicamento" name="inputUsoMedicamento" value="1" class="form-input-styled" data-fouc data-fouc onclick="selecionaUsoMedicamentoDescricao('1')" <?php if (isset($iAtendimentoTriagemId )) { if ($rowTriagem['AtTriUsoMedicamento'] == 1) echo "checked"; }?>>
                            Sim
                        </label>
                    </div>
                    <div class="form-check form-check-inline">
                        <label class="form-check-label">
                            <input type="radio" id="inputUsoMedicamento" name="inputUsoMedicamento" value="0" class="form-input-styled" data-fouc onclick="selecionaUsoMedicamentoDescricao('0')" <?php if (isset($iAtendimentoTriagemId )) { if ($rowTriagem['AtTriUsoMedicamento'] == 0) echo "checked"; }else{ echo "checked"; }?>>
                            Não
                        </label>
                    </div>										
                </div>									
            </div>
        </div>	
        <br>
        <div class="row" style='justify-content: space-between;'>
            <div class="col-lg-2"  style="margin-right: 20px;">
                <div id="dadosAlergia" <?php if (!$iAtendimentoTriagemId) print('style="display:none"'); ?>>
                    <div class="form-group">
                        <label for="inputAlergiaDescricao">Descrição (Alergia) </label>
                        <textarea rows="4" id="inputAlergiaDescricao" name="inputAlergiaDescricao" onInput="contarCaracteres(this)" maxLength="150" class="form-control" placeholder="Descrição da Alergia" ><?php if (isset($iAtendimentoTriagemId )) echo $rowTriagem['AtTriAlergiaDescricao']; ?></textarea>
                        <small class="text-muted form-text">
                            Máx. 150 caracteres<br>
                            <span class="caracteresinputAlergiaDescricao"></span>
                        </small>
                    </div>
                </div> 
            </div>
            <div class="col-lg-2"  style="margin-right: 20px;">
                <div id="dadosDiabete" <?php if (!$iAtendimentoTriagemId) print('style="display:none"'); ?>>
                    <div class="form-group">
                        <label for="inputDiabetesDescricao">Descrição (Diabetes) </label>
                        <textarea rows="4" id="inputDiabetesDescricao" name="inputDiabetesDescricao" onInput="contarCaracteres(this)" maxLength="150" class="form-control" placeholder="Descrição da Diabetes" ><?php if (isset($iAtendimentoTriagemId )) echo $rowTriagem['AtTriDiabetesDescricao']; ?></textarea>
                        <small class="text-muted form-text">
                            Máx. 150 caracteres<br>
                            <span class="caracteresinputDiabetesDescricao"></span>
                        </small>
                    </div>
                </div> 
            </div>
            <div class="col-lg-2"  style="margin-right: 20px;">
                <div id="dadosHipertencao" <?php if (!$iAtendimentoTriagemId) print('style="display:none"'); ?>>
                    <div class="form-group">
                        <label for="inputHipertensaoDescricao">Descrição (Hipertensão) </label>
                        <textarea rows="4" id="inputHipertensaoDescricao" name="inputHipertensaoDescricao" onInput="contarCaracteres(this)" maxLength="150" class="form-control" placeholder="Descrição da Hipertensão" ><?php if (isset($iAtendimentoTriagemId )) echo $rowTriagem['AtTriHipertensaoDescricao']; ?></textarea>
                        <small class="text-muted form-text">
                            Máx. 150 caracteres<br>
                            <span class="caracteresinputHipertensaoDescricao"></span>
                        </small>
                    </div>
                </div>
            </div>
            <div class="col-lg-2"  style="margin-right: 20px;">
                <div id="dadosNeoplasia"<?php if (!$iAtendimentoTriagemId) print('style="display:none"'); ?>>
                    <div class="form-group">
                        <label for="inputNeoplasiaDescricao">Descrição (Neoplasia) </label>
                        <textarea rows="4" id="inputNeoplasiaDescricao" name="inputNeoplasiaDescricao" onInput="contarCaracteres(this)" maxLength="150" class="form-control" placeholder="Descrição da Neoplasia" ><?php if (isset($iAtendimentoTriagemId )) echo $rowTriagem['AtTriNeoplasiaDescricao']; ?></textarea>
                        <small class="text-muted form-text">
                            Máx. 150 caracteres<br>
                            <span class="caracteresinputNeoplasiaDescricao"></span>
                        </small>
                    </div>
                </div>
            </div>
            <div class="col-lg-2">
                <div id="dadosMedicamento" <?php if (!$iAtendimentoTriagemId) print('style="display:none"'); ?>>
                    <div class="form-group">
                        <label for="inputUsoMedicamentoDescricao">Descrição (Uso Medicamentos) </label>
                        <textarea rows="4" id="inputUsoMedicamentoDescricao" name="inputUsoMedicamentoDescricao" onInput="contarCaracteres(this)" maxLength="150" class="form-control" placeholder="Descrição da Medicamento" ><?php if (isset($iAtendimentoTriagemId )) echo $rowTriagem['AtTriUsoMedicamentoDescricao']; ?></textarea>
                        <small class="text-muted form-text">
                            Máx. 150 caracteres<br>
                            <span class="caracteresinputUsoMedicamentoDescricao"></span>
                        </small>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>