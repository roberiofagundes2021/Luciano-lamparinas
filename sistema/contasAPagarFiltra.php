<?php

include_once("sessao.php");
include('global_assets/php/conexao.php');

$cont = 0;

$args = [];

//Aqui é para limpar a sessão caso o usuário filtre todos novamente
$_SESSION['ContPagPeriodoDe'] = '';
$_SESSION['ContPagAte'] = '';
$_SESSION['ContPagFornecedor'] = '';
$_SESSION['ContPagPlanoContas'] = '';
$_SESSION['ContPagStatus'] = '';

if (!empty($_POST['inputPeriodoDe']) || !empty($_POST['inputAte'])) {
    empty($_POST['inputPeriodoDe']) ? $inputPeriodoDe = '1900-01-01' : $inputPeriodoDe = $_POST['inputPeriodoDe'];
    empty($_POST['inputAte']) ? $inputAte = '2100-01-01' : $inputAte = $_POST['inputAte'];

    if($_POST['statusTipo'] == 'APAGAR' || $_POST['statusTipo'] == 'ESTORNADO'){
        $args[]  = "CnAPaDtVencimento BETWEEN '" . $inputPeriodoDe . "' and '" . $inputAte . "' ";
    } else {
        $args[]  = "CnAPaDtPagamento BETWEEN '" . $inputPeriodoDe . "' and '" . $inputAte . "' ";                
    }

    if(!empty($_POST['inputPeriodoDe'])){
        $_SESSION['ContPagPeriodoDe'] = $_POST['inputPeriodoDe'];
    }

    if(!empty($_POST['inputAte'])){
        $_SESSION['ContPagAte'] = $_POST['inputAte'];
    }
}

if (!empty($_POST['cmbFornecedor'])) {
    $args[]  = "CnAPaFornecedor = " . $_POST['cmbFornecedor'] . " ";
    $_SESSION['ContPagFornecedor'] = $_POST['cmbFornecedor'];
}

if (!empty($_POST['cmbPlanoContas'])) {
    $args[]  = "CnAPaPlanoContas = " . $_POST['cmbPlanoContas'] . " ";
    $_SESSION['ContPagPlanoContas'] = $_POST['cmbPlanoContas'];
}

if (!empty($_POST['cmbStatus'])) {
    if($_POST['cmbStatus'] != 'Estornado') {
        $args[]  = "CnAPaStatus = " . $_POST['cmbStatus'] . " and CnAPaJustificativaEstorno is null ";
    }else {
        $args[]  = "CnApaStatus = 11 and CnAPAJustificativaEstorno is not null ";
    }
    $_SESSION['ContPagStatus'] = $_POST['cmbStatus'];
}

if (count($args) >= 1) {

    $string = implode(" and ", $args);

    if ($string != '') {
        $string .= ' and ';
    }

    $sql = "SELECT  CnAPaId, CnAPaDescricao, CnAPaStatus, CnAPaValorAPagar, CnAPaDtVencimento, CnAPaDtPagamento, CnAPaJustificativaEstorno, 
                    CnAPaNotaFiscal, CnAPaAgrupamento, ForneNome 
            FROM ContasAPagar
            LEFT JOIN Fornecedor on ForneId = CnAPaFornecedor
            JOIN Situacao on SituaId = CnApaStatus
            WHERE " . $string . " CnAPaAgrupamento is null and CnAPaUnidade = " . $_SESSION['UnidadeId'] . "";
    $result = $conn->query($sql);
    $rowData = $result->fetchAll(PDO::FETCH_ASSOC);

    count($rowData) >= 1 ? $ExisteConsulta = 1 : $ExisteConsulta = 0;

    $sql = "SELECT CnAgrDescricaoAgrupamento, SituaNome, SUM(CnAPaValorPago) as VALORAGRUPAMENTO, CnAPaAgrupamento, CnAgrDtPagamento
            FROM ContasAPagar
            JOIN ContasAgrupadas on CnAgrId = CnAPaAgrupamento
            JOIN Situacao on SituaId = CnAPaStatus
            WHERE " . $string . " CnAPaUnidade = " . $_SESSION['UnidadeId'] . "
            GROUP BY CnAPaAgrupamento, SituaNome, CnAgrDtPagamento, CnAgrDescricaoAgrupamento";
    $resultAgrupamento = $conn->query($sql);
    $rowAgrupamento = $resultAgrupamento->fetchAll(PDO::FETCH_ASSOC);

    count($rowAgrupamento) >= 1 ? $ExisteAgrupamento = 1 : $ExisteAgrupamento = 0;
}

$arrayData = [];
if ($ExisteConsulta == 1) {
    $cont = 0;
    //print('<input type="hidden" id="elementosGrid" value="'.count($rowData).'">');

    foreach ($rowData as $item) {
        $cont++;     
        $status = $item['CnAPaStatus'] == 11 ? 'À Pagar' : 'Pago';
        $data = $_POST['statusTipo'] == 'APAGAR' || $_POST['statusTipo'] == 'ESTORNADO' ? mostraData($item['CnAPaDtVencimento']) : mostraData($item['CnAPaDtPagamento']);

        $visibilidade = ($status == 'Pago') ? 'none' : 'block';

        $estornamento =  (!isset($item['CnAPaJustificativaEstorno']) || $status == 'Pago') ? 'none' : 'block';
        $justificativaEstornamento = (isset($item['CnAPaJustificativaEstorno'])) ? $item['CnAPaJustificativaEstorno'] : '';
        $consultaAgrupamento = (isset($item['CnAPaAgrupamento'])) ? 'consultaPagamento#'.$item['CnAPaAgrupamento'] : '';

        $checkbox = '<input type="checkbox" id="check'.$cont.'" style="display: '.$visibilidade.';"> <input type="hidden" value="'.$item["CnAPaId"].'">';
        
        $vencimento = '<p class="m-0">' . $data . '</p><input type="hidden" value="'.$data.'">';

        $descricao = '<a href="#" onclick="atualizaContasAPagar('.$_POST['permissionAtualiza'].','.$item["CnAPaId"].', \'edita\')">' . $item["CnAPaDescricao"] . '</a>';
        
        $favorecido = $item["ForneNome"];

        $numDoc = $item["CnAPaNotaFiscal"];

        $valorTotal = mostraValor($item["CnAPaValorAPagar"]);

        $status = $status;

        if( $item["CnAPaAgrupamento"] == '') {
            $acaoConta = ($status == 'Pago') ? '<a href="#" data-toggle="modal" data-target="#modal_mini-estornar" onclick="atualizaContasAPagar('.$_POST['permissionAtualiza'].','.$item["CnAPaId"].', \'estornar\');"  class="list-icons-item"  data-popup="tooltip" data-placement="bottom" title="Estornar Conta"><i class="icon-undo2"></i></a>' : 
            '<a href="#" onclick="atualizaContasAPagar('.$_POST['permissionExclui'].','.$item["CnAPaId"].', \'exclui\');"  class="list-icons-item"  data-popup="tooltip" data-placement="bottom" title="Excluir Conta"><i class="icon-bin"></i></a>';
        }else {
            $acaoConta = ($status == 'Pago') ? '<a href="#" data-toggle="modal" data-target="#modal_mini-estornar" onclick="atualizaContasAPagar('.$_POST['permissionAtualiza'].','.$item["CnAPaId"].', \'estornar\');"  class="list-icons-item"  data-popup="tooltip" data-placement="bottom" title="Estornar Conta"><i class="icon-undo2"></i></a>' : 
                                                '<a href="#" onclick="atualizaContasAPagar('.$_POST['permissionExclui'].','.$item["CnAPaId"].', \'exclui\');"  class="list-icons-item"  data-popup="tooltip" data-placement="bottom" title="Excluir Conta"><i class="icon-bin"></i></a>';
        }


        $acoes = $_POST['permissionAtualiza'] || $_POST['permissionExclui']?'
            <div class="list-icons">
                <div class="list-icons list-icons-extended">'.
                    ($_POST['permissionAtualiza']?'<a href="#" onclick="atualizaContasAPagar('.$_POST['permissionAtualiza'].','.$item["CnAPaId"].', \'edita\');" class="list-icons-item"  data-popup="tooltip" data-placement="bottom" title="Editar Conta"><i class="icon-pencil7"></i></a>':'')
                    .($_POST['permissionExclui']?$acaoConta:'').
                    '<a href="#" class="list-icons-item" data-toggle="modal" data-target="#modal_mini-justificativa-estorno" onclick="estornoJustificativa(\''.$justificativaEstornamento.'\');"  data-popup="tooltip" data-placement="bottom" title="Motivo do estorno" style="display: '.$estornamento.';"><i class="icon-info3"></i></a>
                    <!-- Retirado ícone de parcelar
                    <div class="dropdown" style="display: '.$visibilidade.';">													
                        <a href="#" class="list-icons-item" data-toggle="dropdown">
                            <i class="icon-menu9"></i>
                
                        <div class="dropdown-menu dropdown-menu-right">
                            <a href="#" class="dropdown-item btnParcelar"  data-popup="tooltip" data-placement="bottom" title="Parcelar"><i class="icon-file-text2"></i> Parcelar</a>
                        </div>
                    </div>-->
                </div>
            </div>':'';

        $array = [
            'data'=>[
                isset($checkbox) ? $checkbox : null, 
                isset($vencimento) ? $vencimento : null,
                isset($descricao) ? $descricao : null, 
                isset($favorecido) ? $favorecido : null, 
                isset($numDoc) ? $numDoc : null, 
                isset($valorTotal) ? $valorTotal : null, 
                isset($status) ? $status : null, 
                isset($acoes) ? $acoes : null
            ],
            'identify'=>[
                
            ]
        ];

        array_push($arrayData,$array);
    }   
}

if($ExisteAgrupamento == 1) {
    foreach ($rowAgrupamento as $item) {
        $status = $item['SituaNome'];
        $data = mostraData($item['CnAgrDtPagamento']);

        $justificativaEstornamento = '';
        $consultaAgrupamento = (isset($item['CnAPaAgrupamento'])) ? 'consultaPagamento#'.$item['CnAPaAgrupamento'] : '';

        $checkbox = '';
        
        $vencimento = '<p class="m-0">' . $data . '</p><input type="hidden" value="'.$data.'">';

        $descricao = '<a href="#"  data-toggle="modal" data-target="#modal_consultaPagamentoAgrupado" onclick="atualizaContasAPagar('.$_POST['permissionAtualiza'].', 0, \''.$consultaAgrupamento.'\');" data-popup="tooltip" data-placement="bottom">' . $item["CnAgrDescricaoAgrupamento"] . '</a>';
        
        $favorecido = '';

        $numDoc = '';

        $valorTotal = mostraValor($item["VALORAGRUPAMENTO"]);

        $status = $status;

        $acoes = '
            <div class="list-icons">
                <div class="list-icons list-icons-extended">
                    <a href="#" data-toggle="modal" data-target="#modal_consultaPagamentoAgrupado" onclick="atualizaContasAPagar('.$_POST['permissionAtualiza'].', 0, \''.$consultaAgrupamento.'\');" class="list-icons-item"  data-popup="tooltip" data-placement="bottom" title="Pagamento agrupado"><i class="icon-stack3"></i></a>
                </div>
            </div>';

        $array = [
            'data'=>[
                isset($checkbox) ? $checkbox : null, 
                isset($vencimento) ? $vencimento : null,
                isset($descricao) ? $descricao : null, 
                isset($favorecido) ? $favorecido : null, 
                isset($numDoc) ? $numDoc : null, 
                isset($valorTotal) ? $valorTotal : null, 
                isset($status) ? $status : null, 
                isset($acoes) ? $acoes : null
            ],
            'identify'=>[
                
            ]
        ];

        array_push($arrayData,$array);
    }
}

if ($ExisteConsulta == 1 || $ExisteAgrupamento == 1) {
    print(json_encode($arrayData));
}