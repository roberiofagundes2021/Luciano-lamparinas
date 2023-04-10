<?php

include_once("sessao.php");

include('global_assets/php/conexao.php');

$sql = "SELECT DISTINCT SbCatId, SbCatNome
						FROM SubCategoria
						JOIN Produto 
						  ON ProduSubCategoria = SbCatId
						JOIN Situacao 
						  ON SituaId = SbCatStatus
					 WHERE SbCatEmpresa = " . $_SESSION['EmpreId'] . " 
						 AND SbCatCategoria = '" . $_GET['idCategoria'] . "' 
						 AND SituaChave = 'ATIVO' ";


$result = $conn->query($sql);
$row = $result->fetchAll(PDO::FETCH_ASSOC);
$count = count($row);

//Verifica se já existe esse registro (se existir, retorna true )
if ($count) {
	echo json_encode($row);
} else {
	echo 0;
}
