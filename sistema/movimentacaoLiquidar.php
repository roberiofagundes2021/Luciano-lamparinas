<?php

include_once("sessao.php");

$_SESSION['PaginaAtual'] = 'Movimentação Liquidação';

include('global_assets/php/conexao.php');

// Caso o usuário dê um ENTER na URL, o sistema deve verificar e o POST existe, senão redireciona.
if (!isset($_POST['inputMovimentacaoId'])){
    irpara("movimentacao.php");
}

$inputMovimentacaoId = $_POST['inputMovimentacaoId'];
$UnidadeId = $_SESSION['UnidadeId'];

// pesquisa a movimentação
$sqlMovimentacao = "SELECT MovimId, MovimNumRecibo, MovimData, MovimValorTotal, MovimNotaFiscal,
                    MovimDataEmissao, MovimNumSerie, SituaChave, MvLiqData
                    FROM  Movimentacao
                    LEFT JOIN MovimentacaoLiquidacao ON MvLiqMovimentacao = MovimId
                    JOIN Situacao on SituaId = MovimSituacao
                    WHERE MovimUnidade = $UnidadeId and MovimId = $inputMovimentacaoId";
$resultMovimentacao = $conn->query($sqlMovimentacao);
$Movimentacao = $resultMovimentacao->fetch(PDO::FETCH_ASSOC);

// if(isset($Movimentacao['SituaChave']) && ){
//     // Se ja estiver liquidado ira buscar os dados da liquidação
//     $sqlLiquidacao = "SELECT MvLiqId, MvLiqMovimentacao, MvLiqData, MvLiqUsuario,
//                     MvLiqUnidade, MvLiqPlanoConta, UsuarNome
//                     FROM MovimentacaoLiquidacao
//                     JOIN Usuario ON UsuarId = MvLiqUsuario
//                     WHERE MvLiqMovimentacao = $inputMovimentacaoId AND MvLiqUnidade = $UnidadeId";
//     $resultLiquidacao = $conn->query($sqlLiquidacao);
//     $MoviLiqui = $resultLiquidacao->fetch(PDO::FETCH_ASSOC);

//     // Buacando centros de custos da movimentação
//     $sqlMvLiqXCnCus = "SELECT MvLiqXCnCusMovimentacaoLiquidacao, MvLiqXCnCusCentroCusto, CnCusCodigo,
//                     MvLiqXCnCusUsuarioAtualizador, MvLiqXCnCusValor,MvLiqXCnCusUnidade, CnCusNome, CnCusId
//                     FROM MovimentacaoLiquidacaoXCentroCusto
//                     JOIN CentroCusto ON CnCusId = MvLiqXCnCusCentroCusto
//                     WHERE MvLiqXCnCusMovimentacaoLiquidacao = $MoviLiqui[MvLiqId]
//                     AND MvLiqXCnCusUnidade = $UnidadeId";
//     $resultMvLiqXCnCus = $conn->query($sqlMvLiqXCnCus);
//     $MvLiqXCnCus = $resultMvLiqXCnCus->fetchAll(PDO::FETCH_ASSOC);
// }

// Se ja estiver liquidado ira buscar os dados da liquidação
$sqlLiquidacao = "SELECT MvLiqId, MvLiqMovimentacao, MvLiqData, MvLiqUsuario,
MvLiqUnidade, MvLiqPlanoConta, UsuarNome
FROM MovimentacaoLiquidacao
JOIN Usuario ON UsuarId = MvLiqUsuario
WHERE MvLiqMovimentacao = $inputMovimentacaoId AND MvLiqUnidade = $UnidadeId";
$resultLiquidacao = $conn->query($sqlLiquidacao);
$MoviLiqui = $resultLiquidacao->fetch(PDO::FETCH_ASSOC);

if(isset($MoviLiqui['MvLiqId'])){
    // Buacando centros de custos da movimentação
    $sqlMvLiqXCnCus = "SELECT MvLiqXCnCusMovimentacaoLiquidacao, MvLiqXCnCusCentroCusto, CnCusCodigo,
    MvLiqXCnCusUsuarioAtualizador, MvLiqXCnCusValor,MvLiqXCnCusUnidade, CnCusNome, CnCusNomePersonalizado, CnCusId
    FROM MovimentacaoLiquidacaoXCentroCusto
    JOIN CentroCusto ON CnCusId = MvLiqXCnCusCentroCusto
    WHERE MvLiqXCnCusMovimentacaoLiquidacao = $MoviLiqui[MvLiqId]
    AND MvLiqXCnCusUnidade = $UnidadeId";
    $resultMvLiqXCnCus = $conn->query($sqlMvLiqXCnCus);
    $MvLiqXCnCus = $resultMvLiqXCnCus->fetchAll(PDO::FETCH_ASSOC);
}

// pesquisa os centros de custos da unidade
$sqlCentroCusto = "SELECT CnCusId, CnCusCodigo, CnCusNome, CnCusNomePersonalizado CnCusDetalhamento, CnCusStatus, SituaChave
                   FROM  CentroCusto JOIN Situacao on SituaId = CnCusStatus
                   WHERE CnCusUnidade = ".$_SESSION['UnidadeId']." and SituaChave = 'ATIVO'";
$resultCentroCusto = $conn->query($sqlCentroCusto);
$CentroCustos = $resultCentroCusto->fetchAll(PDO::FETCH_ASSOC);

// pesquisa o Planos de Contas da unidade
$sqlPlanoConta = "SELECT PlConId, PlConCodigo, PlConNome, SituaChave
                  FROM  PlanoConta JOIN Situacao on SituaId = PlConStatus
                  WHERE PlConUnidade = ".$_SESSION['UnidadeId']." and SituaChave = 'ATIVO'
                  AND PlConNatureza = 'D'";
$sqlPlanoConta .= isset($MoviLiqui['MvLiqId'])?" and PlConId = $MoviLiqui[MvLiqPlanoConta]":'';
$resultPlanoConta = $conn->query($sqlPlanoConta);
$PlanoConta = $resultPlanoConta->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT MvAneArquivo
        FROM MovimentacaoAnexo
        WHERE MvAneUnidade = ". $_SESSION['UnidadeId'] ." AND MvAneMovimentacao = ".$_POST['inputMovimentacaoId'];
$result = $conn->query($sql);
$rowNotaFiscal = $result->fetch(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Lamparinas | Movimentação Liquidar</title>

    <?php include_once("head.php"); ?>

    <!-- Validação -->
	<script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
	<script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
	<script src="global_assets/js/demo_pages/form_validation.js"></script>

    <!-- Theme JS files -->
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
	<script src="global_assets/js/demo_pages/form_select2.js"></script>

	<script src="global_assets/js/demo_pages/form_layouts.js"></script>
	<script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>
	
	<script src="global_assets/js/plugins/editors/summernote/summernote.min.js"></script>

    <script src="global_assets/js/plugins/tables/datatables/extensions/responsive.min.js"></script>
	<!-- /theme JS files -->

    <?php
        echo '<script>
                var valorTotal = '.json_encode($Movimentacao['MovimValorTotal']).';
                var MovimNotaFiscal = '.json_encode($Movimentacao['MovimNotaFiscal']).';
            </script>'
    ?>

    <script type="text/javascript">
        $(document).ready(function () {

            // função que valida a data inserida, deve ser igual ou maior que a data atual
            $('#inputPeriodoDe').on('focusout',  function(e){
                if ($('#inputPeriodoDe').val()) {
                    var DataAtual = new Date().toLocaleDateString("pt-BR", {timeZone: "America/Bahia"})
    
                    var dia = $('#inputPeriodoDe').val().split('-')[2]
                    var mes = $('#inputPeriodoDe').val().split('-')[1]
                    var ano = $('#inputPeriodoDe').val().split('-')[0]
    
                    var diaAtual = DataAtual.split('/')[0]
                    var mesAtual = DataAtual.split('/')[1]
                    var anoAtual = DataAtual.split('/')[2]
    
                    var succes = true
    
                    // verifica se a data gerada é menor que a data atual
                    if (ano < anoAtual){
                        succes = false
                    }
                    if (mes < mesAtual && ano === anoAtual){
                        succes = false
                    }
                    if (dia < diaAtual && mes === mesAtual && ano === anoAtual){
                        succes = false
                    }
                    if (!succes) {
                        alerta('Atenção','Data de vencimento deve ser igual ou maior que a data atual!','error');
                        $('#inputPeriodoDe').val(null)
                    }
                }
            })
            
            $('#submitForm').on('click', function(e){
                e.preventDefault();
                var response = calculaValorTotal()
                if(response.status){
                    $('#formCentroCustos').submit()
                    // var menssagem = 'Movimentação Nº '+MovimNotaFiscal+' foi liquidada com sucesso !'
                    // alerta('Atenção', menssagem, 'success')

                } else {
                    var menssagem = 'Os valores dos centros de custos devem bater com o total da Nota Fiscal (R$ '+parseFloat(response.val).toFixed(2).replace('.', ',')+') !'
                    alerta('Atenção', menssagem, 'error')
                }
            })

            // $('#relacaoCentroCusto').hide()
            
            $('#cmbCentroCusto').on('change', function(){
                var centros = $('#cmbCentroCusto').val();
                var HTML = ''
                var HTML_TOTAL = ''
                
                if (centros.length){
                    $.ajax({
                        method: "POST",
                        url: "filtraCentroCusto.php",
                        data: { centroCustos: centros },
                        dataType:"json",
                        success: function(response){
                            if (response.length){
                                $('#relacaoCentroCusto').show()
                                for(var x=0; x<response.length; x++){
                                    var centro = response[x]
                                    var centroCustoDescr = centro.CnCusNomePersonalizado !== null ? centro.CnCusNomePersonalizado : centro.CnCusNome;

                                    $('#totalRegistros').val(response.length)

                                    HTML = HTML + `
                                    <div class="row" style="margin-top: 8px;">
                                        <div class="col-lg-9">
                                            <div class="row">
                                                <div class="col-lg-1" style="min-width: 50x">
                                                    <input type="text" id="inputItem-`+x+`" name="inputItem1" class="form-control-border-off" value="`+(x+1)+`" readOnly>
                                                    <input type="hidden" id="inputIdCentro-`+x+`" name="inputIdCentro-`+x+`" value="`+centro.CnCusId+`">
                                                </div>
                                                <div class="col-lg-2">
                                                    <input type="text" id="inputCentroCodigo-`+x+`" name="inputCentroCodigo-`+x+`" class="form-control-border-off" data-popup="tooltip" value="`+centro.CnCusCodigo+`" readOnly>
                                                </div>
                                                <div class="col-lg-9">
                                                    <input type="text" id="inputCentroNome-`+x+`" name="inputCentroNome-`+x+`" class="form-control-border-off" data-popup="tooltip" value="`+centroCustoDescr+`" readOnly>
                                                </div>
                                            </div>
                                        </div>
        
                                        <div class="col-lg-2">
                                            <input type="" class="form-control-border Valor text-right pula" id="inputCentroValor-${x}" name="inputCentroValor-${x}" onChange="calculaValorTotal(${x})" onkeypress="pula(event)" value="" required>
                                        </div>
        
                                        <div class="col-sm-1 btn" style="text-align:center;" onClick="reset('inputCentroValor-${x}', 0)">
                                            <i class="icon-reset" title="Resetar"></i>
                                        </div>
                                    </div>`;
                                }
                                HTML_TOTAL = `
                                    <div class="col-lg-7">
                                        <div class="row">
                                            <div class="col-lg-1"></div>
                                            <div class="col-lg-11"></div>
                                        </div>
                                    </div>
                                    <div class="col-lg-2" style="padding-top: 5px; text-align: right;">
                                        <h5><b>Total:</b></h5>
                                    </div>
                                    <div class="col-lg-2">
                                        <input type="text" id="inputTotalGeral" name="inputTotalGeral" class="form-control-border-off text-right" value="R$ 0" readOnly>
                                    </div>
                                    <div class="col-lg-1 btn" style="text-align:center;" onClick="reset('all', 0)">
                                        <i class="icon-reset" title="Resetar Todos"></i>
                                    </div>
                                `
                            }
                            $('#centroCustoContent').html(HTML).show();
                            $('#centroCustoContentTotal').html(HTML_TOTAL).show();
                            $('#inputTotalGeral').val('R$ ' + 0);
                        }})
                }else{
                    $('#centroCustoContent').html(HTML).show();
                    $('#centroCustoContentTotal').html(HTML_TOTAL).show();
                    $('#inputTotalGeral').val('R$ ' + 0);
                    $('#relacaoCentroCusto').hide()
                }
            })
        });

        function pula(e){
			/*
			* verifica se o evento é Keycode (para IE e outros browsers)
			* se não for pega o evento Which (Firefox)
			*/
			var tecla = (e.keyCode?e.keyCode:e.which);

			/* verifica se a tecla pressionada foi o ENTER */
			if(tecla == 13){
				/* guarda o seletor do campo que foi pressionado Enter */
				var array_campo = document.getElementsByClassName('pula');

				/* pega o indice do elemento*/
				var id = e.path[0].id.split('-')
				id = 'inputCentroValor-' + (parseInt(id[1])+1)

				/*soma mais um ao indice e verifica se não é null
				*se não for é porque existe outro elemento
				*/

				if(document.getElementById(id)){
					document.getElementById(id).focus()
				} else {
					document.getElementById(e.path[0].id).blur()
                }
			} else {
				return e;
			}

			/* impede o sumbit caso esteja dentro de um form */
			e.preventDefault(e);
			return false;
		}

        function reset(id, val, totalRegistros){
            if (id === 'all'){
                var total = totalRegistros?totalRegistros:parseFloat($('#totalRegistros').val())
                for(var x=0; x<total; x++){
                    $('#inputCentroValor-'+x).val(float2moeda(0))
                }
            } else {
                $('#'+id).val(float2moeda(val))
            }
            calculaValorTotal()
        }

        function calculaValorTotal(id, totalRegistros){
            var totalNotaFiscal = parseFloat(valorTotal)
            var ValTotal = 0
            var total = totalRegistros?totalRegistros:parseFloat($('#totalRegistros').val())
            var cont = 0
            var valor = 0
            if(id !== undefined){
                valor = parseFloat($('#inputCentroValor-'+id).val().replaceAll('.', '').replace(',', '.'))
                $('#inputCentroValor-'+id).val(float2moeda(valor))
            }

            console.log(totalNotaFiscal)
            console.log(ValTotal)
            console.log(total)
            console.log(valor)

            for(var x=0; x<total; x++){
                ValTotal += parseFloat($(`#inputCentroValor-${x}`).val()) ? parseFloat($(`#inputCentroValor-${x}`).val().replaceAll('.', '').replace(',', '.')) : 0
            }
            console.log(ValTotal)

            if (id !== undefined){
                if(ValTotal > totalNotaFiscal){
                    cont = ValTotal - totalNotaFiscal
                    ValTotal = ValTotal - cont
                    $('#inputCentroValor-'+id).val(float2moeda(valor - cont))
                }
            }
            var newValue = float2moeda(ValTotal) //parseFloat(ValTotal).toFixed(2).replace('.', ',')
            $('#inputTotalGeral').val(`R$ ${newValue}`)
            // retorna o status para quando for submeter o sistema verificar
            // se o valor está batendo com o total
            if (ValTotal !== totalNotaFiscal && total > 0){
                var obj = {
                    status: false,
                    val: totalNotaFiscal
                }
                return obj
            } else {
                var obj = {
                    status: true,
                    val: totalNotaFiscal
                }
                return obj
            }
        }
    </script>

</head>

<body class="navbar-top sidebar-right-visible sidebar-xs">

    <?php include_once("topo.php"); ?>

    <!-- Page content -->
    <div class="page-content">

        <?php include_once("menu-left.php"); ?>

        <!-- Main content -->
        <div class="content-wrapper">

            <?php include_once("cabecalho.php"); ?>

            <!-- Content area -->
            <div class="content">

                <!-- Info blocks -->
                <div class="row">
                    <div class="col-lg-12">
                        <form id="formCentroCustos" name="formCentroCustos" method="POST" action="movimentacaoLiquidarContabilidade.php" class="form-validate-jquery">
                            <!-- Basic responsive configuration -->
                            <div class="card">
                                <div class="card-header header-elements-inline">
                                    <h3 class="card-title">Liquidar movimentação</h3>
                                </div>

                                <div class="card-body">
                                        <div class="row">
                                            <?php
                                            echo '<div class="col-lg-2">
                                                    <div class="form-group">
                                                        <label for="inputNumeroNota">Nº Nota Fiscal</label>
                                                        <div class="input-group">
                                                            <input readOnly type="text" id="inputNumeroNota" name="inputNumeroNota" class="form-control" value="'.$Movimentacao['MovimNotaFiscal'].'">
                                                            <span class="input-group-prepend" style="cursor: pointer;">';
                                                                
                                                                if (isset($rowNotaFiscal['MvAneArquivo']) && $rowNotaFiscal['MvAneArquivo']){
                                                                    echo '<a href="global_assets/anexos/movimentacao/'.$rowNotaFiscal['MvAneArquivo'].'" target="_blank" title="Abrir Nota Fiscal">
                                                                            <span class="input-group-text" style="color: red;"><i class="icon-file-pdf"></i></span>
                                                                          </a>';
                                                                } else{
                                                                    echo '<a href="#" title="Nota fiscal não anexada">
                                                                            <span class="input-group-text" style="color: red;"><i class="icon-file-pdf"></i></span>
                                                                          </a>';
                                                                }                                                               

                                            echo            '</span>
                                                        </div>
                                                    </div>
                                                </div>';

                                                echo '<div class="col-lg-2">
                                                        <div class="form-group">
                                                            <label for="inputValorNota">Total (R$) Nota Fiscal</label>
                                                            <div class="input-group">
                                                                <input readOnly type="text" id="inputValorNota" name="inputValorNota" class="form-control" value="R$ '.mostraValor($Movimentacao['MovimValorTotal']).'">
                                                            </div>
                                                        </div>
                                                    </div>'
                                            ?>
                                            <div class="col-lg-2">
                                                <div class="form-group">
                                                    <label for="inputPeriodoDe">Data de vencimento <span class='text-danger'>*</span></label>
                                                    <div class="input-group">
                                                        <?php
                                                            $disabled = $Movimentacao['SituaChave'] == 'LIBERADOCONTABILIDADE'? 'disabled':'';
                                                            echo "<input type='date' id='inputPeriodoDe' name='inputPeriodoDe' $disabled class='form-control'  value='".(isset($MoviLiqui['MvLiqData'])?$MoviLiqui['MvLiqData']:'')."' required>";
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label for="cmbPlanoContaId">Plano de contas <span class='text-danger'>*</span></label>
                                                    <?php
                                                        $disabled = $Movimentacao['SituaChave'] == 'LIBERADOCONTABILIDADE'? 'disabled':'';
                                                        $selected = isset($MoviLiqui['MvLiqPlanoConta'])? 'selected':'';
                                                        $selectPlanCont = "<select id='cmbPlanoContaId' $disabled name='cmbPlanoContaId' class='form-control form-control-select2' required autofocus>
                                                                        <option value=''>Selecione</option>";
                                                        foreach($PlanoConta as $Plano){
                                                            $selectPlanCont .= "<option $selected value='".$Plano['PlConId']."'>".$Plano['PlConCodigo'] . ' - '.$Plano['PlConNome']."</option>";
                                                        }
                                                        $selectPlanCont .= "</select>";
                                                        echo $selectPlanCont;
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="form-group" style="border-bottom:1px solid #ddd;">
                                                    <label for="cmbCentroCusto">Centro de custos <span class='text-danger'>*</span></label>
                                                    <?php
                                                        $CenCust = isset($MoviLiqui['MvLiqPlanoConta'])? $MvLiqXCnCus:$CentroCustos;
                                                        $disabled = $Movimentacao['SituaChave'] == 'LIBERADOCONTABILIDADE'? 'disabled':'';

                                                        $selectCencust = "<select id='cmbCentroCusto' $disabled name='cmbCentroCusto[]' class='form-control select' multiple='multiple' required autofocus data-fouc>";
                                                        foreach($CenCust as $CentroCusto){
                                                            $cnCusDescricao = $CentroCusto["CnCusNomePersonalizado"] === NULL ? $CentroCusto["CnCusNome"] : $CentroCusto["CnCusNomePersonalizado"];
                                                            $selectCencust .= "<option ".(isset($MoviLiqui['MvLiqPlanoConta'])?' selected ':'')."value='".$CentroCusto['CnCusId']."'>".$cnCusDescricao."</option>";
                                                        }
                                                        $selectCencust .= "</select>";
                                                        echo $selectCencust;
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                    <div class="row" style="width:100%;">
                                        
                                        <?php
                                            if($Movimentacao['SituaChave'] != 'LIBERADOCONTABILIDADE' && ($_SESSION['PerfiChave'] == 'CONTABILIDADE' || $_SESSION['PerfiChave'] == 'SUPER')){
                                                echo "<div><button id='submitForm' class='btn btn-principal'>Liquidar</button></div>";
                                            }
                                        ?>

                                        <div>
                                            <a href="movimentacao.php" class="btn btn-basic" role="button">Cancelar</a>
                                        </div>
                                        <?php
                                            if(isset($MoviLiqui['MvLiqId'])){
                                                echo "<div style='text-align: right; position:absolute; right:10px; margin-top:10px'>
                                                        <p style='color: red; margin-right: 20px'><i class='icon-info3'></i>
                                                        Liquidado pelo usuário $MoviLiqui[UsuarNome] na data ".mostraData($Movimentacao['MvLiqData']).
                                                        "</p>
                                                    </div>";
                                            }
                                        ?>  
                                    </div>
                                </div>
                            </div>
                            <!-- /basic responsive configuration -->

                            <div id="relacaoCentroCusto" class="card" <?php echo isset($MoviLiqui['MvLiqId'])?'style="display:block;"':'style="display:none;"' ?> >
                                <div class="card-header header-elements-inline">
                                    <h5 class="card-title">Relação de Centro de Custo</h5>
                                    <div class="header-elements">
                                        <div class="list-icons">
                                            <a class="list-icons-item" data-action="collapse"></a>
                                            <!-- <a class="list-icons-item" data-action="reload"></a>
                                            <a class="list-icons-item" data-action="remove"></a> -->
                                        </div>
                                    </div>
                                </div>

                                <div class="card-body">
                                    <p class="mb-3">Abaixo estão listados todos os centros de custos selecionados. Para atualizar os valores, basta preencher a coluna <code>Valor</code> e depois clicar em <b>LIQUIDAR</b>.</p>

                                    <div class="row" style="margin-bottom: -20px;">
                                        <?php
                                            echo !isset($MoviLiqui['MvLiqId'])?"<div class='col-lg-9'>
                                                    <div class='row'>
                                                        <div class='col-lg-1'>
                                                            <label for='inputCodigo'><strong>Item</strong></label>
                                                        </div>
                                                        <div class='col-lg-2'>
                                                            <div class='col-sm-2' style='text-align:center;'>
                                                                <label for=''><strong>Código</strong></label>
                                                            </div>
                                                        </div>
                                                        <div class='col-lg-9'>
                                                            <label for='inputProduto'><strong>Centro de Custo</strong></label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class='col-lg-2'>
                                                    <div class='form-group'>
                                                        <label for='inputValor'><strong>Valor</strong></label>
                                                    </div>
                                                </div>"
                                                :
                                                "<div class='col-lg-10'>
                                                    <div class='row'>
                                                        <div class='col-lg-1'>
                                                            <label for='inputCodigo'><strong>Item</strong></label>
                                                        </div>
                                                        <div class='col-lg-2'>
                                                            <div class='col-sm-2' style='text-align:center;'>
                                                                <label for=''><strong>Código</strong></label>
                                                            </div>
                                                        </div>
                                                        <div class='col-lg-9'>
                                                            <label for='inputProduto'><strong>Centro de Custo</strong></label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class='col-lg-2'>
                                                    <div class='form-group'>
                                                        <label for='inputValor'><strong>Valor</strong></label>
                                                    </div>
                                                </div>"
                                        ?>
                                        <?php
                                            if(!isset($MoviLiqui['MvLiqId'])){
                                                echo "<div class='col-lg-1'>
                                                        <div class='col-sm-12' style='text-align:center;'>
                                                            <label for=''><strong>Resetar</strong></label>
                                                        </div>
                                                    </div>";
                                            }
                                        ?>
                                    </div>
                                    <div id="centroCustoContent">
                                        <!-- caso ja esteja liquidado será mostrado os centros de custos -->
                                        <?php
                                            if(isset($MoviLiqui['MvLiqId'])){
                                                $HTMLCenCust = '';
                                                $HTMLCenCustValueTotal = 0;
                                                foreach($MvLiqXCnCus as $key => $CnCus){
                                                    $cnCusDescricao = $CnCus["CnCusNomePersonalizado"] === NULL ? $CnCus["CnCusNome"] : $CnCus["CnCusNomePersonalizado"];
                                                    $valor = $CnCus['MvLiqXCnCusValor'];
                                                    $valor = str_replace('.', ',', $valor);
                                                    $HTMLCenCust .= "
                                                    <div class='row' style='margin-top: 8px;'>
                                                        <div class='col-lg-9'>
                                                            <div class='row'>
                                                                <div class='col-lg-1' style='min-width: 50x'>
                                                                    <input type='text' id='inputItem-$key' name='inputItem1' class='form-control-border-off' value='".($key+1)."' readOnly>
                                                                    <input type='hidden' id='inputIdCentro-$key' name='inputIdCentro-$key' value='$CnCus[CnCusCodigo]'>
                                                                </div>
                                                                <div class='col-lg-2'>
                                                                    <input type='text' id='inputCentroCodigo-$key' name='inputCentroCodigo-$key' class='form-control-border-off' data-popup='tooltip' value='$CnCus[CnCusCodigo]' readOnly>
                                                                </div>
                                                                <div class='col-lg-9'>
                                                                    <input type='text' id='inputCentroNome-$key' name='inputCentroNome-$key' class='form-control-border-off' data-popup='tooltip' value='$cnCusDescricao' readOnly>
                                                                </div>
                                                            </div>
                                                        </div>
                        
                                                        <div class='col-lg-2'>
                                                            <input type='' id='inputCentroValor-$key' name='inputCentroValor-$key' value='$valor' onChange='calculaValorTotal($key,  ".COUNT($MvLiqXCnCus).")' onkeypress='pula(event)' ".
                                                            ($Movimentacao['SituaChave'] == 'LIBERADOCONTABILIDADE'?" class='form-control-border-off Valor text-right' readOnly":" class='form-control-border Valor text-right'")."/>
                                                        </div>

                                                        <div class='col-sm-1 btn' style='text-align:center;' ".($Movimentacao['SituaChave'] == 'LIBERADOCONTABILIDADE'?"":"onClick='reset(`inputCentroValor-$key`, 0)'").">
                                                            <i class='icon-reset' title='Resetar'></i>
                                                        </div>
                                                    </div>";
                                                    $HTMLCenCustValueTotal += $valor;
                                                }
                                                echo $HTMLCenCust;
                                            }
                                        ?>
                                        <!-- aqui será adicionado o HTML listando centros de custos selecionados -->
                                    </div>

                                    <div id="centroCustoContentTotal" class="row" style="margin-top: 8px;">
                                        <!-- aqui será adicionado o HTML mostrando o valor total dos centros de custos selecionados -->
                                        <?php 
                                            if(isset($MoviLiqui['MvLiqId'])){
                                                $HTMLCenCustTotal = "
                                                    <div class='col-lg-7'>
                                                        <div class='row'>
                                                            <div class='col-lg-1'></div>
                                                            <div class='col-lg-11'></div>
                                                        </div>
                                                    </div>
                                                    <div class='col-lg-2' style='padding-top: 5px; text-align: right;'>
                                                        <h5><b>Total:</b></h5>
                                                    </div>
                                                    <div class='col-lg-2'>
                                                        <input type='text' id='inputTotalGeral' name='inputTotalGeral' class='form-control-border-off text-right' value='R$ $HTMLCenCustValueTotal' readOnly>
                                                    </div>
                                                    <div class='col-lg-1 btn' style='text-align:center;' ".($Movimentacao['SituaChave'] == 'LIBERADOCONTABILIDADE'?"":" onClick='reset(`all`, 0, ".COUNT($MvLiqXCnCus).")' ").">
                                                        <i class='icon-reset' title='Resetar Todos'></i>
                                                    </div>";
                                                    echo $HTMLCenCustTotal;
                                            }
                                        ?>
                                    </div>

                                    <?php
                                        echo "<input type='hidden' id='totalRegistros' name='totalRegistros' value='".($MvLiqXCnCus?COUNT($MvLiqXCnCus):0)."'>";
                                        echo '<input id="inputMovimentacaoId" type="hidden" name="inputMovimentacaoId" value="'.$_POST['inputMovimentacaoId'].'"></input>';
                                    ?>
                                </div>
                            </div>
                        </form>
                        <form id="formImprime" method="POST" target="_blank">
                            <?php
                                echo '<input id="inputMovimentacaoId" type="hidden" name="inputMovimentacaoId" value="'.$_POST['inputMovimentacaoId'].'"></input>';
                            ?>
                        </form>
                    </div>
                </div>
            </div>
            <!-- /content area -->

            <?php include_once("footer.php"); ?>

        </div>
    </div>
    <!-- /page content -->

    <?php include_once("alerta.php"); ?>

</body>

</html>