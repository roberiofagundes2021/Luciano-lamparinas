<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputSrOrcId'])){
	
	$SrOrcId = $_POST['inputSrOrcId'];
        	
	try{
		
		$sql = "DELETE FROM servicoOrcamento
				WHERE SrOrcId = :id";
		$result = $conn->prepare("$sql");
		$result->bindParam(':id', $SrOrcId);
		$result->execute();	  
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Serviço excluído!!!";
		$_SESSION['msg']['tipo'] = "success";		
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao excluir serviço!!!";
		$_SESSION['msg']['tipo'] = "error";			
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("servicoOrcamento.php");

?>
