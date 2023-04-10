<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

if(isset($_POST['nomeVelho'])){
	$sql = "SELECT PerfiId
			FROM Perfil
			WHERE PerfiNome = '". $_POST['nomeNovo']."' and PerfiNome <> '". $_POST['nomeVelho']."' and PerfiUnidade = " . $_SESSION['UnidadeId'];
} else{
	$sql = "SELECT PerfiId
			FROM Perfil
			WHERE PerfiNome = '". $_POST['nome']."' and PerfiUnidade = " . $_SESSION['UnidadeId'];
}
$result = $conn->query($sql);
$row = $result->fetchAll(PDO::FETCH_ASSOC);
$count = count($row);

//Verifica se já existe esse registro (se existir, retorna true )
if($count){
	echo 1;
}  else{

	if ($_POST['estadoAtual'] == 'EDITA'){
		echo "EDITA";
	} else{
		echo 0;
	}
}

?>
