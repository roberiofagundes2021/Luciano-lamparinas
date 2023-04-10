<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputClienteId'])){
	
	$iCliente = $_POST['inputClienteId'];
        	
	try{
		
		$sql = "DELETE FROM Cliente
				WHERE ClienId = :id";
		$result = $conn->prepare("$sql");
		$result->bindParam(':id', $iCliente);
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Cliente excluído!!!";
		$_SESSION['msg']['tipo'] = "success";		
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao excluir Cliente!!!";
		$_SESSION['msg']['tipo'] = "error";			
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("cliente.php");

?>
