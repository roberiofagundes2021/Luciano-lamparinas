<?php
include_once("sessao.php");
include('global_assets/php/conexao.php');

$argsRecebimento = [];
$argsPagamento = [];

//Aqui é para limpar a sessão caso o usuário filtre todos novamente
$_SESSION['MovCaixaPeriodoDe'] = '';
$_SESSION['MovCaixaAte'] = '';
$_SESSION['MovCaixaCliente'] = '';
$_SESSION['MovCaixaFormaPagamento'] = '';
$_SESSION['MovCaixaStatus'] = '';

if (!empty($_POST['inputPeriodoDe']) || !empty($_POST['inputAte'])) {
    empty($_POST['inputPeriodoDe']) ? $inputPeriodoDe = date('Y-m-d 01:00:00') : $inputPeriodoDe = $_POST['inputPeriodoDe'] . ' 00:00:00';
    empty($_POST['inputAte']) ? $inputAte = date('Y-m-d 23:59:59') : $inputAte = $_POST['inputAte'] . ' 23:59:59';

    $argsRecebimento[]  = "CxRecDataHora BETWEEN '" . $inputPeriodoDe . "' and '" . $inputAte . "' ";
    $argsPagamento[]  = "CxPagDataHora BETWEEN '" . $inputPeriodoDe . "' and '" . $inputAte . "' ";

    if (!empty($_POST['inputPeriodoDe'])) {
        $_SESSION['MovCaixaPeriodoDe'] = $_POST['inputPeriodoDe'];
    }

    if (!empty($_POST['inputAte'])) {
        $_SESSION['MovCaixaAte'] = $_POST['inputAte'];
    }
}

if (!empty($_POST['cmbClientes'])) {
    $argsRecebimento[]  = "AtendCliente = " . $_POST['cmbClientes'] . " ";
    $argsPagamento[]  = "CxPagId = 0 "; //Para não mostrar nenhum pagamento quando um cliente for filtrado 
    $_SESSION['MovCaixaCliente'] = $_POST['cmbClientes'];
}

if (!empty($_POST['inputFormaPagamento'])) {
    $argsRecebimento[]  = "CxRecFormaPagamento = " . $_POST['inputFormaPagamento'] . " ";
    $argsPagamento[]  = "CxPagFormaPagamento = " . $_POST['inputFormaPagamento'] . " ";
    $_SESSION['MovCaixaFormaPagamento'] = $_POST['inputFormaPagamento'];
}

if (!empty($_POST['cmbStatus'])) {
    $argsRecebimento[]  = "CxRecStatus = " . $_POST['cmbStatus'] . " ";
    $argsPagamento[]  = "CxPagStatus = " . $_POST['cmbStatus'] . " ";
    $_SESSION['MovCaixaStatus'] = $_POST['cmbStatus'];
}

$stringRecebimento = implode(" and ", $argsRecebimento);
$stringPagamento = implode(" and ", $argsPagamento);

if ($stringRecebimento != '') {
    $stringRecebimento .= ' and ';
}

if ($stringPagamento != '') {
    $stringPagamento .= ' and ';
}

$sql_movimentacao    = "SELECT CxRecId as ID, AtendNumRegistro, ClienNome as HISTORICO, CxRecDataHora as DATAHORA, CxRecAtendimento as ATENDIMENTO, FrPagNome, 
                                CxRecValor, CxRecValorTotal as TOTAL, SituaNome, SituaChave, 'Recebimento' as TIPO
                        FROM CaixaRecebimento
                        JOIN CaixaAbertura on CxAbeId = CxRecCaixaAbertura
                        JOIN FormaPagamento on FrPagId = CxRecFormaPagamento
                        JOIN Atendimento on AtendId = CxRecAtendimento
                        JOIN Cliente on ClienId = AtendCliente
                        JOIN Situacao on SituaId = CxRecStatus
                        WHERE ".$stringRecebimento." CxAbeOperador = $_SESSION[UsuarId] and CxRecUnidade = $_SESSION[UnidadeId]
                        UNION 
                        SELECT CxPagId as ID, '' as NUM_REGISTRO, CxPagJustificativaRetirada as HISTORICO, CxPagDataHora as DATAHORA, 0 as ATENDIMENTO, FrPagNome,
                                0 as Valor, CxPagValor as TOTAL, SituaNome, SituaChave, 'Pagamento' as TIPO
                        FROM CaixaPagamento
                        JOIN CaixaAbertura on CxAbeId = CxPagCaixaAbertura
                        JOIN FormaPagamento on FrPagId = CxPagFormaPagamento
                        JOIN Situacao on SituaId = CxPagStatus
                        WHERE ".$stringPagamento." CxAbeOperador = $_SESSION[UsuarId] and CxPagUnidade = $_SESSION[UnidadeId]";
$resultMovimentacao  = $conn->query($sql_movimentacao);
$rowMovimentacao = $resultMovimentacao->fetchAll(PDO::FETCH_ASSOC);

$arrayData = [];
foreach ($rowMovimentacao as $item) {
    $numeroRegistro = $item["AtendNumRegistro"];
    $dataHora = mostraDataHora($item["DATAHORA"]);
    $historico = '<a href="#" onclick="atualizaMovimentacaoCaixa('.$item["ID"].','.$item["ATENDIMENTO"].', \''.$item["TIPO"].'\', \'detalhamento\');">'.$item["HISTORICO"].'</a>';
    $tipo = $item["TIPO"];
    $formaPagamento = $item["FrPagNome"];
    $status = $item["SituaNome"];
    
    if($tipo == 'Recebimento') {
        $valorFinal = mostraValor($item["TOTAL"]);
    }else {
        //Transformando os valores em números negativos.
        $valorFinal = mostraValor($item["TOTAL"] * -1); 
    }

    $iconeVizivel = $item["SituaChave"] == 'ESTORNADO' ? '<a href="#" data-toggle="modal" data-target="#modal_mini-estornar" onclick="atualizaMovimentacaoCaixa('.$item["ID"].','.$item["ATENDIMENTO"].', \''.$item["TIPO"].'\', \'estornar\');"  class="list-icons-item"  data-popup="tooltip" data-placement="bottom" title="Estornar"><i class="icon-info3"></i></a>' :
                                                         '<a href="#" data-toggle="modal" data-target="#modal_mini-estornar" onclick="atualizaMovimentacaoCaixa('.$item["ID"].','.$item["ATENDIMENTO"].', \''.$item["TIPO"].'\', \'detalhesEstornamento\');"  class="list-icons-item"  data-popup="tooltip" data-placement="bottom" title="Estornar"><i class="icon-undo2"></i></a>';

    $acoes = '
            <div class="list-icons">
                <div class="list-icons list-icons-extended">
                    <a href="#" onclick="atualizaMovimentacaoCaixa('.$item["ID"].','.$item["ATENDIMENTO"].', \''.$item["TIPO"].'\', \'detalhamento\');" class="list-icons-item"  data-popup="tooltip" data-placement="bottom" title="Detalhamento"><i class="icon-file-text2"></i></a>
                    '.$iconeVizivel.'
                    <a href="#" data-toggle="modal" data-target="#modal_mini-estornar" onclick="atualizaMovimentacaoCaixa('.$item["ID"].','.$item["ATENDIMENTO"].', \''.$item["TIPO"].'\', \'imprimir\');"  class="list-icons-item"  data-popup="tooltip" data-placement="bottom" title="Imprimir"><i class="icon-printer2"></i></a>
                </div>
            </div>';

    $array = [  
        'data'=>[
            isset($numeroRegistro) ? $numeroRegistro : null,
            isset($dataHora) ? $dataHora : null,
            isset($historico) ? $historico : null,
            isset($tipo) ? $tipo : null,
            isset($formaPagamento) ? $formaPagamento : null,
            isset($valorFinal) ? $valorFinal : null,
            isset($status) ? $status : null,
            isset($acoes) ? $acoes : null
        ],
        'identify'=>[
            
        ]
    ];

    array_push($arrayData,$array);
}

print(json_encode($arrayData));
?>