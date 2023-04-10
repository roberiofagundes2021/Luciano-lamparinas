<?php
include_once("sessao.php");
include('global_assets/php/conexao.php');

$caixaId = $_POST['inputCaixaId'];

//Para pegar a última consulta
$sql_saldoInicial    = "SELECT CxAbeId, CaixaNome, CxAbeSaldoInicial, CxAbeDataHoraAbertura, CxAbeTotalRecebido, CxAbeTotalPago, 
                               CxAbeDataHoraFechamento, CxAbeSaldoFinal 
                        FROM CaixaAbertura
                        JOIN Caixa on CaixaId = CxAbeCaixa
                        WHERE CxAbeCaixa = ".$caixaId." ORDER BY CxAbeId DESC";
$resultSaldoInicial  = $conn->query($sql_saldoInicial);


if($rowSaldoInicial = $resultSaldoInicial->fetch(PDO::FETCH_ASSOC)) {
    $resposta = $rowSaldoInicial;
}else {
    $resposta = 'abrirCaixa';
}

print(json_encode($resposta));
?>