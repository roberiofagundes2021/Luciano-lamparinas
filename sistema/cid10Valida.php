<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

if(isset($_POST['codigoVelho'])){
	$sql = ("SELECT Cid10Id
			 FROM Cid10
			 WHERE Cid10Codigo = '". $_POST['codigoNovo']."' and Cid10Codigo <> '". $_POST['codigoVelho']."'");
} else{
	$sql = ("SELECT Cid10Id
			 FROM Cid10
			 WHERE Cid10Codigo = '". $_POST['codigo']."'");
}
$result = $conn->query("$sql");
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
