<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

if(isset($_POST['cnpjVelho'])){
	$sql = "SELECT EmpreId
			FROM Empresa
			WHERE EmpreCnpj = '". $_POST['cnpjNovo']."' and EmpreCnpj <> '". $_POST['cnpjVelho']."'";
} else{
	$sql = "SELECT EmpreId
			FROM Empresa
			WHERE EmpreCnpj = '". $_POST['cnpj']."'";
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
