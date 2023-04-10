<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputFabricanteId'])){
	
	$iFabricante = $_POST['inputFabricanteId'];
        	
	try{
		
		$sql = "DELETE FROM Fabricante
				WHERE FabriId = :id";
		$result = $conn->prepare("$sql");
		$result->bindParam(':id', $iFabricante); 
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Fabricante excluído!!!";
		$_SESSION['msg']['tipo'] = "success";		
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao excluir fabricante!!!";
		$_SESSION['msg']['tipo'] = "error";			
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("fabricante.php");

?>
