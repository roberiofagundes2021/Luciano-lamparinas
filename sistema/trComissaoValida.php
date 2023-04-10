<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

if(isset($_POST['usuarioVelho'])){
	$sql = "
		SELECT TRXEqUsuario
			FROM TRXEquipe
		 WHERE TRXEqUnidade = ".$_SESSION['UnidadeId']." 
		 	 AND TRXEqUsuario = ". $_POST['usuario']." 
			 AND TRXEqUsuario <> ". $_POST['usuarioVelho']." 
			 AND TRXEqTermoReferencia = ".$_POST['TRId']."";
} else{
	$sql = "
		SELECT TRXEqUsuario
			FROM TRXEquipe
		 WHERE TRXEqUnidade = ".$_SESSION['UnidadeId']." 
		   AND TRXEqUsuario = ". $_POST['usuario']." 
			 AND TRXEqTermoReferencia =".$_POST['TRId']."";
}
$result = $conn->query($sql);
$row 		= $result->fetchAll(PDO::FETCH_ASSOC);
$count 	= count($row);

//Verifica se já existe esse registro (se existir, retorna true )
if($count){
	echo 1;
} else{
	echo 0;
}

?>
