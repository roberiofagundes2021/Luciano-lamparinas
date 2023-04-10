<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputContaBancoId'])){
	
	$iContaBanco = $_POST['inputContaBancoId'];
        	
	try{
		
		$sql = "DELETE FROM ContaBanco
				WHERE CnBanId = :id";
		$result = $conn->prepare($sql);
		$result->bindParam(':id', $iContaBanco); 
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Conta/Banco excluída!!!";
		$_SESSION['msg']['tipo'] = "success";		
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao excluir Conta/Banco!!!";
		$_SESSION['msg']['tipo'] = "error";			
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("contaBanco.php");

?>
