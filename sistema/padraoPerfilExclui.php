<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

$_SESSION['msg'] = array();

if(isset($_POST['inputPerfilId'])){
	
	$iPerfil = $_POST['inputPerfilId'];
        	
	try{

		$sql = "DELETE FROM PadraoPermissao WHERE PaPerPerfil = $iPerfil";
		$conn->query($sql);

		$sql = "DELETE FROM Perfil
				WHERE PerfiId = $iPerfil and PerfiUnidade is null and PerfiPadrao = 1";
		$conn->query($sql);

		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Padrão de perfil deletado!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {
		$conn->rollBack();

		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao deletar padrão de perfil!!!";
		$_SESSION['msg']['tipo'] = "error";
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("padraoPerfil.php");

?>
