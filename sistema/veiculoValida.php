<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

if(isset($_POST['nomeVelho'])){
	$sql = "SELECT VeicuId
			FROM Veiculo
			WHERE VeicuUnidade = ".$_SESSION['UnidadeId']." and VeicuPlaca = '". $_POST['nomeNovo']."' and VeicuPlaca <> '". $_POST['nomeVelho']."'";
} else {
	$sql = "SELECT VeicuId
			FROM Veiculo
			WHERE VeicuUnidade = ".$_SESSION['UnidadeId']." and VeicuPlaca = '". $_POST['nomeNovo']."'";
} 

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
