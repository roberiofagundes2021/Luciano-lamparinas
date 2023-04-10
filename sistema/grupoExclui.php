<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputGrupoContaId'])){
	
	$iGrupoConta = $_POST['inputGrupoContaId'];
        	
	try{
		
		$sql = "DELETE FROM GrupoConta
				WHERE GrConId = :id";
		$result = $conn->prepare("$sql");
		$result->bindParam(':id', $iGrupoConta); 
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Grupo Conta excluída!!!";
		$_SESSION['msg']['tipo'] = "success";		
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao excluir grupo conta!!!";
		$_SESSION['msg']['tipo'] = "error";			
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("grupo.php");

?>
