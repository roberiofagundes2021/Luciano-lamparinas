<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

$sql = "SELECT MovimOrdemCompra
		FROM Movimentacao
		WHERE MovimOrdemCompra = " . $_POST['iOrdemCompra'] . " and MovimUnidade = " . $_SESSION['UnidadeId'];
$result = $conn->query($sql);
$row = $result->fetchAll(PDO::FETCH_ASSOC);
$count = count($row);

//Verifica se a Ordem de Compra está em uso
if($count){
	echo 1;
} else{
	echo 0;
}

?>
