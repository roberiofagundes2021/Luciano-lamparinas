<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

if (isset($_POST['nomeVelho'])){
	$sql = "SELECT TpAprId
			 FROM TipoAprazamento
			 WHERE TpAprUnidade = ".$_SESSION['UnidadeId']." and TpAprNome = '". $_POST['nomeNovo']."' and TpAprNome <> '". $_POST['nomeVelho']."'";
} else{
	$sql = "SELECT TpAprId
			 FROM TipoAprazamento
			 WHERE TpAprUnidade = ".$_SESSION['UnidadeId']." and TpAprNome = '". $_POST['nome']."'";
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
