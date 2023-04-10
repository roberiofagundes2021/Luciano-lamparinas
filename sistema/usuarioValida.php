<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

if (isset($_SESSION['EmpresaId'])){
	$EmpresaId = $_SESSION['EmpresaId'];
} else {	
	$EmpresaId = $_SESSION['EmpreId'];
}

if(isset($_GET['cpf'])){

	$sql = "SELECT UsuarId, UsuarNome, UsuarLogin, UsuarSenha, UsuarEmail, UsuarTelefone, UsuarCelular, 
				   EXUXPEmpresa
			FROM Usuario
			JOIN EmpresaXUsuarioXPerfil on EXUXPUsuario = UsuarId
			JOIN UsuarioXUnidade on UsXUnEmpresaUsuarioPerfil = EXUXPId
			WHERE UsuarCpf = '". $_GET['cpf']."'";
			 
	$result = $conn->query($sql);
	$row = $result->fetchAll(PDO::FETCH_ASSOC);
	$count = count($row);
	
	if ($count){
		
		$empresas = array();
		
		foreach ($row as $item){
			$empresas[] = $item['EXUXPEmpresa'];
		}
		
		if (in_array($EmpresaId, $empresas)){
			echo 1; //"existeNessaEmpresa";
		} else {			
			echo json_encode($row);
		}
	} else{
		echo 0;
	}			 
} else {
	
	if ($_GET['loginNovo'] != $_GET['loginVelho']){

		$sql = "SELECT UsuarId
				FROM Usuario
				WHERE UsuarEmpresa = ".$EmpresaId." and UsuarLogin = '". $_GET['loginNovo']."'";
		$result = $conn->query($sql);
		$row = $result->fetchAll(PDO::FETCH_ASSOC);
		$count = count($row);

		if ($count){
			echo 1;
		}
	} else {
		echo 0;
	}
		
}

?>
