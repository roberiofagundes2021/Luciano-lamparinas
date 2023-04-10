<?php

include_once("sessao.php");

include('global_assets/php/conexao.php');

//Verifica se já existe saldo 
$sql = "SELECT isnull(SUM(dbo.fnItensRestantesContrato(" . $_POST['IdFlOpe'] . "," .$_SESSION['UnidadeId']. ",FOXPrProduto,'P')), 0) as ItensRestantesContrato 
        FROM FluxoOperacionalXProduto
        JOIN FluxoOperacional on FlOpeId = FOXPrFluxoOperacional
        WHERE FOXPrUnidade = " . $_SESSION['UnidadeId'] . " and FOXPrFluxoOperacional = " . $_POST['IdFlOpe'];
$result = $conn->query($sql);
$rowProduto = $result->fetch(PDO::FETCH_ASSOC);

$sql = "SELECT isnull(SUM(dbo.fnItensRestantesContrato(" . $_POST['IdFlOpe'] . ",".$_SESSION['UnidadeId']. ",FOXSrServico,'S')), 0) as ItensRestantesContrato
        FROM FluxoOperacionalXServico
        JOIN FluxoOperacional on FlOpeId = FOXSrFluxoOperacional
        WHERE FOXSrUnidade = " . $_SESSION['UnidadeId'] . " and FOXSrFluxoOperacional = " . $_POST['IdFlOpe'];
$result = $conn->query($sql);
$rowServico = $result->fetch(PDO::FETCH_ASSOC);

$rowSoma = $rowProduto['ItensRestantesContrato'] + $rowServico['ItensRestantesContrato'];

//Verifica se ainda existe saldo (se existir, retorna true)
if ($rowSoma > 0) {
    echo 1;
} else{
    echo 0;
}