<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputLocalEstoqueId'])){
	
	$iLocalEstoque = $_POST['inputLocalEstoqueId'];
        	
	try{
		
		$sql = "DELETE FROM LocalEstoque
				WHERE LcEstId = :id";
		$result = $conn->prepare("$sql");
		$result->bindParam(':id', $iLocalEstoque); 
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Local do Estoque excluído!!!";
		$_SESSION['msg']['tipo'] = "success";		
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao excluir local do estoque!!!";
		$_SESSION['msg']['tipo'] = "error";			
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("localEstoque.php");

?>
