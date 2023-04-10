<?php

include_once("sessao.php");

include('global_assets/php/conexao.php');

$sql = " SELECT dbo.fnValorTotalOrdemCompra(" . $_SESSION['UnidadeId'] . ",  " . $_POST['ordemCompra'] . ") as valorTotalOrdemCompra";
$result = $conn->query($sql);
$totalOrdemCompra = $result->fetch(PDO::FETCH_ASSOC);

print($totalOrdemCompra['valorTotalOrdemCompra']);

?>