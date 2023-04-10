<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputPrOrcId'])){
	
	$PrOrcId = $_POST['inputPrOrcId'];
	$pasta = "global_assets/images/produtos/";
        	
	try{
		
		$sql = "DELETE FROM produtoOrcamento
				WHERE PrOrcId = :id";
		$result = $conn->prepare("$sql");
		$result->bindParam(':id', $PrOrcId);
		$result->execute();	  
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Produto excluído!!!";
		$_SESSION['msg']['tipo'] = "success";		
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao excluir Produto!!!";
		$_SESSION['msg']['tipo'] = "error";			
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("produtoOrcamento.php");

?>
