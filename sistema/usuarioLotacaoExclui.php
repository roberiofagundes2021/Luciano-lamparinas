<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputEmpresaUsuarioPerfil'])){
	
	$iEmpresaUsuarioPerfil = $_POST['inputEmpresaUsuarioPerfil'];
	$iUnidade = $_POST['inputUnidade'];
        	
	try{
		
		$sql = "DELETE FROM UsuarioXUnidade
				WHERE UsXUnEmpresaUsuarioPerfil = :iEmpresaUsuarioPerfil and UsXUnUnidade = :iUnidade";
		$result = $conn->prepare($sql);
		$result->bindParam(':iEmpresaUsuarioPerfil', $iEmpresaUsuarioPerfil); 
		$result->bindParam(':iUnidade', $iUnidade); 
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Lotação excluída!!!";
		$_SESSION['msg']['tipo'] = "success";		
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao excluir a Lotação!!!";
		$_SESSION['msg']['tipo'] = "error";			
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("usuarioLotacao.php");

?>
