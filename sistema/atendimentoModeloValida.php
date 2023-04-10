<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

if (isset($_POST['nomeVelho'])){
	$sql = "SELECT AtModId
			 FROM AtendimentoModelo
			 WHERE AtModUnidade = ".$_SESSION['UnidadeId']." and AtModDescricao = '".$_POST['nomeNovo']."' and AtModDescricao <> '".$_POST['nomeVelho']."' and AtModTipoModelo = '". $_POST['cmbModelo']."'";
} else{
	$sql = "SELECT AtModId
	FROM AtendimentoModelo
	WHERE AtModUnidade = ".$_SESSION['UnidadeId']." and AtModDescricao = '".$_POST['nomeNovo']."' and AtModTipoModelo = '". $_POST['cmbModelo']."'";
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
