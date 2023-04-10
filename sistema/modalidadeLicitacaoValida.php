<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

if(isset($_POST['nomeVelho'])){
	$sql = "SELECT MdLicId
			FROM ModalidadeLicitacao
			WHERE MdLicNome = '". $_POST['nomeNovo']."' and MdLicNome <> '". $_POST['nomeVelho']."'";
} else{
	$sql = "SELECT MdLicId
			FROM ModalidadeLicitacao
			WHERE MdLicNome = '". $_POST['nome']."'";
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
