<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputBancoId'])){
	
	$iBanco = $_POST['inputBancoId'];
        	
	try{
		
		$sql = "DELETE FROM Banco
				WHERE BancoId = :id";
		$result = $conn->prepare("$sql");
		$result->bindParam(':id', $iBanco); 
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Banco excluído!!!";
		$_SESSION['msg']['tipo'] = "success";		
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao excluir banco!!!";
		$_SESSION['msg']['tipo'] = "error";			
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("banco.php");

?>
