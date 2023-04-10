<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

$tipoConta = $_POST['tipoConta'];
$agrupamentoId = $_POST['agrupamentoId'];

if($tipoConta == 'P') {
    $sqlAgrupamento = "SELECT CnAPaId, CnAPaDescricao, CnAPaNotaFiscal, CnAPaDtPagamento, CnAPaFormaPagamento, CnAPaContaBanco, CnAPaValorPago,
                    CnAgrDescricaoAgrupamento, CnAgrValorTotal
                    FROM  ContasAPagar 
                    JOIN ContasAgrupadas on CnAgrId = CnAPaAgrupamento
                    WHERE CnAPaAgrupamento = $agrupamentoId
                    ORDER BY CnAPaDescricao ASC";
    
    $resultPagamentoAgrupado = $conn->query($sqlAgrupamento);
    $pagamentoAgrupado = $resultPagamentoAgrupado->fetchAll(PDO::FETCH_ASSOC);
}else {
    $sqlAgrupamento = "SELECT CnAReId, CnAReDescricao, CnAReNumDocumento, CnAReDtRecebimento, CnAReFormaPagamento, CnAReContaBanco, CnAReValorRecebido,
                    CnAgrDescricaoAgrupamento, CnAgrValorTotal
                    FROM  ContasAReceber 
                    JOIN ContasAgrupadas on CnAgrId = CnAReAgrupamento
                    WHERE CnAReAgrupamento = $agrupamentoId
                    ORDER BY CnAReDescricao ASC";
    
    $resultPagamentoAgrupado = $conn->query($sqlAgrupamento);
    $pagamentoAgrupado = $resultPagamentoAgrupado->fetchAll(PDO::FETCH_ASSOC);
}

$count = COUNT($pagamentoAgrupado);
//Verifica se já existe esse registro (se existir, retorna true )
if($count){
	echo (json_encode($pagamentoAgrupado));
} else{
	echo (0);
}

?>
