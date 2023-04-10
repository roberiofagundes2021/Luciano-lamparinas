<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

if ($_POST['tipo'] == 'F'){

	if(isset($_POST['nomeVelho'])){
		$sql = "SELECT ForneId
				FROM Fornecedor
				WHERE ForneEmpresa = ".$_SESSION['EmpreId']." and ForneNome = '". $_POST['nomeNovo']."' and ForneNome <> '". $_POST['nomeVelho']."' and ForneCpf = '". limpaCPF_CNPJ($_POST['documento'])."'";
	} else{
		$sql = "SELECT ForneId
				FROM Fornecedor
				WHERE ForneEmpresa = ".$_SESSION['EmpreId']." and ForneNome = '". $_POST['nome']."' and ForneCpf = '". limpaCPF_CNPJ($_POST['documento'])."'";
	}
} else{

	if(isset($_POST['nomeVelho'])){
		$sql = "SELECT ForneId
				FROM Fornecedor
				WHERE ForneEmpresa = ".$_SESSION['EmpreId']." and ForneNome = '". $_POST['nomeNovo']."' and ForneNome <> '". $_POST['nomeVelho']."' and ForneCnpj = '". limpaCPF_CNPJ($_POST['documento'])."'";
	} else{
		$sql = "SELECT ForneId
				FROM Fornecedor
				WHERE ForneEmpresa = ".$_SESSION['EmpreId']." and ForneNome = '". $_POST['nome']."' and ForneCnpj = '". limpaCPF_CNPJ($_POST['documento'])."'";
	}
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
