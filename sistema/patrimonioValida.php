<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

	$sql = "SELECT PatriId, PatriNumero
			FROM Patrimonio
			WHERE PatriNumero = '". $_POST['numero']."' AND PatriUnidade = " . $_SESSION['UnidadeId'];

$result = $conn->query($sql);
$row = $result->fetchAll(PDO::FETCH_ASSOC);
$count = count($row);

//Verifica se já existe esse registro (se existir, retorna true )
if($count){
	echo 1;
} else{
	echo 0;
}

?>
