<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

if(isset($_POST['nomeVelho'])){
	$sql = "SELECT CnCusId
			 FROM CentroCusto
			 WHERE CnCusUnidade = ".$_SESSION['UnidadeId']." and CnCusNome = '". $_POST['nomeNovo']."' and CnCusNome <> '". $_POST['nomeVelho']."'";
} else{
	$sql = "SELECT CnCusId
			FROM CentroCusto
			WHERE CnCusUnidade = ".$_SESSION['UnidadeId']." and CnCusNome = '". $_POST['nome']."'";
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
