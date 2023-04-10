<?php
include_once("sessao.php");
include('global_assets/php/conexao.php');

$sql_saldoInicial    = "SELECT CxAbeId, CxAbeSaldoInicial
                        FROM CaixaAbertura
                        JOIN Caixa on CaixaId = CxAbeCaixa
                        WHERE CxAbeOperador = ".$_SESSION['UsuarId']." ORDER BY CxAbeId DESC";
$resultSaldoInicial  = $conn->query($sql_saldoInicial);

if($rowSaldoInicial = $resultSaldoInicial->fetch(PDO::FETCH_ASSOC)) {
    $aberturaCaixaId = $rowSaldoInicial['CxAbeId'];
    $resposta[0] = $rowSaldoInicial;
    //Para mostrar o saldo do dia da Abertura do Caixa
    $dataHoraInicio = date('Y-m-d 00:00:00');
    $dataHoraFinal = date('Y-m-d 23:59:59');

    //Adicionar o caixa Pagamento
    $sql_movimentacao    = "SELECT SUM(CxRecValorTotal) as SaldoRecebido
                        FROM CaixaRecebimento
                        JOIN CaixaAbertura on CxAbeId = CxRecCaixaAbertura
                        JOIN Situacao on SituaId = CxRecStatus
                        WHERE CxRecDataHora BETWEEN '".$dataHoraInicio."' and '".$dataHoraFinal."' and 
                        CxAbeOperador = $_SESSION[UsuarId] and CxRecCaixaAbertura = ".$aberturaCaixaId." and CxRecUnidade = $_SESSION[UnidadeId]";
    $resultMovimentacao  = $conn->query($sql_movimentacao);

    if($rowMovimentacao = $resultMovimentacao->fetch(PDO::FETCH_ASSOC)) {
        $resposta[1] = $rowMovimentacao;
    }else {
        $resposta[1] = 'consultaVazia';
    }

    $sql_movimentacao    = "SELECT SUM(CxPagValor) as SaldoPago
                        FROM CaixaPagamento
                        JOIN CaixaAbertura on CxAbeId = CxPagCaixaAbertura
                        JOIN Situacao on SituaId = CxPagStatus
                        WHERE CxPagDataHora BETWEEN '".$dataHoraInicio."' and '".$dataHoraFinal."' and 
                        CxAbeOperador = $_SESSION[UsuarId] and CxPagCaixaAbertura = ".$aberturaCaixaId." and CxPagUnidade = $_SESSION[UnidadeId]";
    $resultMovimentacao  = $conn->query($sql_movimentacao);

    if($rowMovimentacao = $resultMovimentacao->fetch(PDO::FETCH_ASSOC)) {
        $resposta[2] = $rowMovimentacao;
    }else {
        $resposta[2] = 'consultaVazia';
    }
}else {
    $resposta = 'consultaVazia';
}

print(json_encode($resposta));
?>