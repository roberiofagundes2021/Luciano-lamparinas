<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

$sql = "SELECT UsXUnEmpresaUsuarioPerfil
		FROM UsuarioXUnidade
		WHERE UsXUnEmpresaUsuarioPerfil = ".$_SESSION['EmpresaUsuarioPerfil']." and UsXUnUnidade = '". $_POST['unidade']."'";
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
