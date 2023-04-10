<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

if ($_POST['tipo'] == 'F'){

	if(isset($_POST['nomeVelho'])){
		$sql = "SELECT ProfiId
				FROM Profissional
				WHERE ProfiUnidade = ".$_SESSION['UnidadeId']." and ProfiNome = '". $_POST['nomeNovo']."' and ProfiNome <> '". $_POST['nomeVelho']."' and ProfiCpf = '". limpaCPF_CNPJ($_POST['cpf'])."'";
	} else{
		$sql = "SELECT ProfiId
				FROM Profissional
				WHERE ProfiUnidade = ".$_SESSION['UnidadeId']." and ProfiNome = '". $_POST['nome']."' and ProfiCpf = '". limpaCPF_CNPJ($_POST['cpf'])."'";
	}
} else{

	if(isset($_POST['nomeVelho'])){
		$sql = "SELECT ProfiId
				FROM Profissional
				WHERE ProfiUnidade = ".$_SESSION['UnidadeId']." and ProfiNome = '". $_POST['nomeNovo']."' and  ProfiNome <> '". $_POST['nomeVelho']."' and ProfiCnpj = '". limpaCPF_CNPJ($_POST['cnpj'])."'";
	} else{
		$sql = "SELECT ProfiId
				FROM Profissional
				WHERE ProfiUnidade = ".$_SESSION['UnidadeId']." and ProfiNome = '". $_POST['nome']."' and ProfiCnpj = '". limpaCPF_CNPJ($_POST['cnpj'])."'";
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
