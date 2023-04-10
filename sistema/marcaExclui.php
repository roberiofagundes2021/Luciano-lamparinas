<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputMarcaId'])){
	
	$iMarca = $_POST['inputMarcaId'];
        	
	try{
		
		$sql = "DELETE FROM Marca
				WHERE MarcaId = :id";
		$result = $conn->prepare("$sql");
		$result->bindParam(':id', $iMarca); 
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Marca excluída!!!";
		$_SESSION['msg']['tipo'] = "success";		
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao excluir marca!!!";
		$_SESSION['msg']['tipo'] = "error";			
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("marca.php");

?>
