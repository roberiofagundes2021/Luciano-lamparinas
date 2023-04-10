<?php
include_once("sessao.php");
include('global_assets/php/conexao.php');

$filtroCentroCusto = implode(', ',$_POST['inputFiltroCentroCusto']);
$planoConta = $_POST['inputPlanoConta1'];
$data1 = $_POST['inputDataInicial1'];
$dataFinal1 = $_POST['inputDataFinal1'];
$data2 = $_POST['inputDataInicial2'];
$dataFinal2 = $_POST['inputDataFinal2'];
$data3 = $_POST['inputDataInicial3'];
$dataFinal3 = $_POST['inputDataFinal3'];
$data4 = $_POST['inputDataInicial4'];
$dataFinal4 = $_POST['inputDataFinal4'];

$sql = "SELECT DISTINCT CnCusId, CnCusNome, CnCusNomePersonalizado,
        dbo.fnCentroCustoPrevisto($_SESSION[UnidadeId], CnCusId, $planoConta, '$data1', '$dataFinal1', 'S')  as Previsto,
        dbo.fnCentroCustoRealizado($_SESSION[UnidadeId], CnCusId, $planoConta, '$data1', '$dataFinal1', 'S')  as Realizado,
        dbo.fnCentroCustoPrevisto($_SESSION[UnidadeId], CnCusId, $planoConta, '$data2', '$dataFinal2', 'S')  as Previsto2,
        dbo.fnCentroCustoRealizado($_SESSION[UnidadeId], CnCusId, $planoConta, '$data2', '$dataFinal2', 'S')  as Realizado2,
        dbo.fnCentroCustoPrevisto($_SESSION[UnidadeId], CnCusId, $planoConta, '$data3', '$dataFinal3', 'S')  as Previsto3,
        dbo.fnCentroCustoRealizado($_SESSION[UnidadeId], CnCusId, $planoConta, '$data3', '$dataFinal3', 'S')  as Realizado3,
        dbo.fnCentroCustoPrevisto($_SESSION[UnidadeId], CnCusId, $planoConta, '$data4', '$dataFinal4', 'S')  as Previsto4,
        dbo.fnCentroCustoRealizado($_SESSION[UnidadeId], CnCusId, $planoConta, '$data4', '$dataFinal4', 'S')  as Realizado4
        FROM CentroCusto
        JOIN ContasAPagarXCentroCusto ON CAPXCCentroCusto = CnCusId
        JOIN ContasAPagar ON CnAPaId = CAPXCContasAPagar
        WHERE CnAPaPlanoContas = $planoConta AND CAPXCUnidade = $_SESSION[UnidadeId] and CnCusId in ($filtroCentroCusto)
        ORDER BY CnCusNome ASC";
$result = $conn->query($sql);
$rowCentroCusto = $result->fetchAll(PDO::FETCH_ASSOC);

$array[0] = $rowCentroCusto;
$array[1] = $data2; 
$array[2] = $data3; 
$array[3] = $data4;

print(json_encode($array));
?>