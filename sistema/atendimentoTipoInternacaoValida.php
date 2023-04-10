<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

if (isset($_POST['nomeVelho'])){
	$sql = "SELECT TpIntId
			 FROM TipoInternacao
			 WHERE TpIntUnidade = ".$_SESSION['UnidadeId']." and TpIntNome = '". $_POST['nomeNovo']."' and TpIntNome <> '". $_POST['nomeVelho']."'";
} else{
	$sql = "SELECT TpIntId
			 FROM TipoInternacao
			 WHERE TpIntUnidade = ".$_SESSION['UnidadeId']." and TpIntNome = '". $_POST['nomeNovo']."'";
}
$result = $conn->query($sql);
$row = $result->fetchAll(PDO::FETCH_ASSOC);
$count = count($row);

//Verifica se já existe esse registro (se existir, retorna true )
if($count){
	echo 1;
} else{

	if ($_POST['estadoAtual'] == 'EDITA'){
		echo "EDITA";
	} else{
		echo 0;
	}
}

?>
