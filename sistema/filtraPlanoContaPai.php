<?php
include_once("sessao.php");
include('global_assets/php/conexao.php');

$codigo = '%' . $_POST['inputCodigo'] ;//. '%';

$sql = "SELECT PlConId, PlConCodigo, PlConNome
        FROM PlanoConta
        JOIN Situacao on SituaId = PlConStatus
        WHERE PlConUnidade = " . $_SESSION['UnidadeId'] . " and PlConTipo = 'S' and PlConCodigo like '$codigo'
        ORDER BY PlConCodigo ASC";
$result = $conn->query($sql);
$rowPlanoContas = $result->fetchAll(PDO::FETCH_ASSOC);

print(json_encode($rowPlanoContas));
?>