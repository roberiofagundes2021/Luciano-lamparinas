<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

if (isset($_POST['nomeVelho'])){
	$sql = "SELECT EsLeiId
			 FROM EspecialidadeLeito
			 WHERE EsLeiUnidade = ".$_SESSION['UnidadeId']." and EsLeiNome = '". $_POST['nomeNovo']."' and EsLeiNome <> '". $_POST['nomeVelho']."'";
} else{
	$sql = "SELECT EsLeiId
			 FROM EspecialidadeLeito
			 WHERE EsLeiUnidade = ".$_SESSION['UnidadeId']." and EsLeiNome = '". $_POST['nomeNovo']."'";
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
