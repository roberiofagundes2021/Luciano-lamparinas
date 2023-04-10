<?php
include_once("sessao.php");
include('global_assets/php/conexao.php');

$data = $_POST['inputData'];

//pega o saldo inicial realizado
$sql_saldoInicial    = "select dbo.fnFluxoCaixaSaldoInicialRealizado(".$_SESSION['UnidadeId'].",'".$data."') as SaldoInicial";
$resultSaldoInicial  = $conn->query($sql_saldoInicial);
$rowSaldoInicial     = $resultSaldoInicial->fetch(PDO::FETCH_ASSOC);

print(json_encode(mostraValor($rowSaldoInicial['SaldoInicial'])));
?>