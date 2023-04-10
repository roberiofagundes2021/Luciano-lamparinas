<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

if(isset($_POST['nomeVelho']) && $_POST['nomeVelho'] != ''){
	$sql = "SELECT SbCatId
			FROM SubCategoria
			WHERE SbCatEmpresa = ".$_SESSION['EmpreId']." and SbCatNome = '". $_POST['nomeNovo']."' and SbCatCategoria = ". $_POST['categoriaNovo']." and 
			SbCatId not in (select SbCatId from SubCategoria where SbCatEmpresa = ".$_SESSION['EmpreId']." and SbCatNome = '". $_POST['nomeVelho']."' and 
			SbCatCategoria = ". $_POST['categoriaVelho'].")";
} else{
	$sql = "SELECT SbCatId
			FROM SubCategoria
			WHERE SbCatEmpresa = ".$_SESSION['EmpreId']." and SbCatNome = '". $_POST['nomeNovo']."' and SbCatCategoria = ". $_POST['categoriaNovo'];
}

$result = $conn->query($sql);
$row = $result->fetchAll(PDO::FETCH_ASSOC);
$count = count($row);

//Verifica se já existe esse registro (se existir, retorna true)

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