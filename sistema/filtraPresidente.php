<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

$sql = "SELECT UsuarId, UsuarLogin
		FROM Usuario
		JOIN EmpresaXUsuarioXPerfil ON EXUXPUsuario = UsuarId
		JOIN UsuarioXUnidade on UsXUnEmpresaUsuarioPerfil = EXUXPId
		JOIN Situacao on SituaId = EXUXPStatus
		WHERE UsXUnUnidade = ". $_SESSION['UnidadeId'] ." and EXUXPUsuario in (". $_GET['aEquipe'].") and SituaChave = 'ATIVO'";

$result = $conn->query($sql);
$row = $result->fetchAll(PDO::FETCH_ASSOC);
$count = count($row);

//Verifica se ja existe esse registro (se existir, retorna true )
if($count){
	echo json_encode($row);
} else{
	echo 0;
}

?>
