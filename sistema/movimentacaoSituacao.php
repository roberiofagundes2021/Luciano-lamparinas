<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

$sql = "SELECT SituaId, SituaNome, SituaChave
		FROM Situacao
		WHERE SituaStatus = 1
		ORDER BY SituaNome ASC";

$result = $conn->query($sql);
$row = $result->fetchAll(PDO::FETCH_ASSOC);
$count = count($row);

//Verifica se já existe esse registro (se existir, retorna true )
if($count){
	echo json_encode($row);
} else{
	echo 0;
}

?>
