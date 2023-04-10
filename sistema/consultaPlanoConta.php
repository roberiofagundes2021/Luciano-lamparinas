<?php
include_once("sessao.php");
include('global_assets/php/conexao.php');

$filtroPlanoConta = implode(', ',$_POST['inputFiltroPlanoConta']);
$planoConta1 = $_POST['inputPlanoConta1'];
$data1 = $_POST['inputDataInicial1'];
$dataFinal1 = $_POST['inputDataFinal1'];
$data2 = $_POST['inputDataInicial2'];
$dataFinal2 = $_POST['inputDataFinal2'];
$data3 = $_POST['inputDataInicial3'];
$dataFinal3 = $_POST['inputDataFinal3'];
$data4 = $_POST['inputDataInicial4'];
$dataFinal4 = $_POST['inputDataFinal4'];

$sql = "SELECT PlConId, PlConCodigo, PlConNome, PlConNatureza,
               dbo.fnPlanoContasPrevisto(".$_SESSION['UnidadeId'].", PlConCodigo, '".$data1."', '".$dataFinal1."', PlConNatureza)  as Previsto,
               dbo.fnPlanoContasRealizado(".$_SESSION['UnidadeId'].", PlConCodigo, '".$data1."', '".$dataFinal1."', PlConNatureza)  as Realizado,
               dbo.fnPlanoContasPrevisto(".$_SESSION['UnidadeId'].", PlConCodigo, '".$data2."', '".$dataFinal2."', PlConNatureza)  as Previsto2,
               dbo.fnPlanoContasRealizado(".$_SESSION['UnidadeId'].", PlConCodigo, '".$data2."', '".$dataFinal2."', PlConNatureza)  as Realizado2,
               dbo.fnPlanoContasPrevisto(".$_SESSION['UnidadeId'].", PlConCodigo, '".$data3."', '".$dataFinal3."', PlConNatureza)  as Previsto3,
               dbo.fnPlanoContasRealizado(".$_SESSION['UnidadeId'].", PlConCodigo, '".$data3."', '".$dataFinal3."', PlConNatureza)  as Realizado3,
               dbo.fnPlanoContasPrevisto(".$_SESSION['UnidadeId'].", PlConCodigo, '".$data4."', '".$dataFinal4."', PlConNatureza)  as Previsto4,
               dbo.fnPlanoContasRealizado(".$_SESSION['UnidadeId'].", PlConCodigo, '".$data4."', '".$dataFinal4."', PlConNatureza)  as Realizado4
        FROM PlanoConta
        JOIN Situacao on SituaId = PlConStatus
        WHERE PlConUnidade = " . $_SESSION['UnidadeId'] . " and PlConPlanoContaPai = $planoConta1 and PlConId in (".$filtroPlanoConta.")
        ORDER BY PlConCodigo ASC";
$result = $conn->query($sql);
$rowPlanoContas = $result->fetchAll(PDO::FETCH_ASSOC);

$array[0] = $rowPlanoContas;
$array[1] = $data2; 
$array[2] = $data3;
$array[3] = $data4;

print(json_encode($array));
?>