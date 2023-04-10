<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

if(isset($_POST['nome'])){
	$UnidadeId = $_SESSION['UnidadeId'];
	$nome = $_POST['nome'];
	$sql = "SELECT PlConId
		    FROM PlanoConta
		    WHERE PlConUnidade = $UnidadeId and PlConNome = '$nome'".
			(isset($_POST['planoContasId'])?' and PlConId <> '.$_POST['planoContasId']:'');
}
$result = $conn->query($sql);
$row = $result->fetchAll(PDO::FETCH_ASSOC);

$count = count($row);

//Verifica se já existe esse registro (se existir, retorna true )
if($count){
	echo 1;
} else {
	echo 0;
}