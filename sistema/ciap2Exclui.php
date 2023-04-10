<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputCiap2Id'])){
	
	$iCiap2 = $_POST['inputCiap2Id'];
        	
	try{
		
		$sql = "DELETE FROM Ciap2
				WHERE Ciap2Id = :id";
		$result = $conn->prepare("$sql");
		$result->bindParam(':id', $iCiap2); 
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Ciap-2 excluída!!!";
		$_SESSION['msg']['tipo'] = "success";		
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao excluir ciap-2!!!";
		$_SESSION['msg']['tipo'] = "error";			
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("Ciap2.php");

?>
