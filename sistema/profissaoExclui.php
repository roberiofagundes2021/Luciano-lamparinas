<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputProfissaoId'])){
	
	$iProfissao = $_POST['inputProfissaoId'];
        	
	try{
		
		$sql = "DELETE FROM Profissao
				WHERE ProfiId = :id";
		$result = $conn->prepare("$sql");
		$result->bindParam(':id', $iProfissao); 
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Profissão excluída!!!";
		$_SESSION['msg']['tipo'] = "success";		
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao excluir profissão!!!";
		$_SESSION['msg']['tipo'] = "error";			
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("profissao.php");

?>
