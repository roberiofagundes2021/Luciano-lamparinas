<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputCid10Id'])){
	
	$iCid10 = $_POST['inputCid10Id'];
        	
	try{
		
		$sql = "DELETE FROM Cid10
				WHERE Cid10Id = :id";
		$result = $conn->prepare("$sql");
		$result->bindParam(':id', $iCid10); 
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Cid10 excluída!!!";
		$_SESSION['msg']['tipo'] = "success";		
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao excluir cid10!!!";
		$_SESSION['msg']['tipo'] = "error";			
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("cid10.php");

?>
