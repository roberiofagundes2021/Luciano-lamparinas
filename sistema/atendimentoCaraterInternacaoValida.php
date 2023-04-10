<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

if(isset($_POST['nomeVelho'])){
	$sql = "SELECT CrIntId
			FROM CaraterInternacao
			WHERE CrIntUnidade = ".$_SESSION['UnidadeId']." and CrIntNome = '". $_POST['nomeNovo']."' and CrIntNome <> '". $_POST['nomeVelho']."'";
} else{
	$sql = "SELECT CrIntId
			FROM CaraterInternacao
			WHERE CrIntUnidade = ".$_SESSION['UnidadeId']." and CrIntNome = '". $_POST['nome']."'";
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