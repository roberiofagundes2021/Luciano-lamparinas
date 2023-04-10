<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

if(isset($_POST['numeroVelho'])){
	$sql = ("SELECT InvenId
			 FROM Inventario
			 WHERE InvenEmpresa = ".$_SESSION['EmpreId']." and InvenNumero = '". $_POST['numeroNovo']."' and InvenNome <> '". $_POST['numeroVelho']."'");
} else{
	$sql = ("SELECT InvenId
			 FROM Inventario
			 WHERE InvenEmpresa = ".$_SESSION['EmpreId']." and InvenNumero = '". $_POST['numero']."'");
}
$result = $conn->query("$sql");
$row = $result->fetchAll(PDO::FETCH_ASSOC);
$count = count($row);

//Verifica se já existe esse registro (se existir, retorna true )
if($count){
	echo 1;
} else{
	echo 0;
}

?>
