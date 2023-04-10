<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputPerfilId'])){
	
	$iPerfil = $_POST['inputPerfilId'];
	$iUnidade = $_SESSION['UnidadeId'];
        	
	try{

		$conn->beginTransaction();

		$sql = "DELETE FROM PerfilXPermissao
				WHERE PrXPePerfil = $iPerfil and PrXPeUnidade = $iUnidade";
		$conn->query($sql);

		$sql = "DELETE FROM PadraoPerfilXPermissao
				WHERE PaPrXPePerfil = $iPerfil and PaPrXPeUnidade = $iUnidade";
		$conn->query($sql);

		$sql = "DELETE FROM Perfil
				WHERE PerfiId = $iPerfil and PerfiUnidade = $iUnidade";
		$conn->query($sql);

		$conn->commit();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Perfil excluído!!!";
		$_SESSION['msg']['tipo'] = "success";		
		
	} catch(PDOException $e) {

		$conn->rollback();
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao excluir Perfil!!!";
		$_SESSION['msg']['tipo'] = "error";			
		
		echo 'Error: ' . $e->getMessage();
	}
}
irpara("perfil.php");

?>
