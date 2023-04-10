<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputVeicuId'])){
	
	$iVeiculo = $_POST['inputVeicuId'];
        	
	try{
		
		$sql = "DELETE FROM Veiculo
				WHERE VeicuId = :id";
		$result = $conn->prepare("$sql");
		$result->bindParam(':id', $iVeiculo); 
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Veículo excluído!!!";
		$_SESSION['msg']['tipo'] = "success";		
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao excluir veículo!!!";
		$_SESSION['msg']['tipo'] = "error";			
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("veiculo.php");

?>
