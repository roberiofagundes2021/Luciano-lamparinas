<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputPlanoContasId'])){
	
	$iPlanoContas = $_POST['inputPlanoContasId'];
        	
	try{
		
		$sql = "DELETE FROM PlanoConta
				WHERE PlConId = :id";
		$result = $conn->prepare($sql);
		$result->bindParam(':id', $iPlanoContas); 
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Plano de Contas excluído!!!";
		$_SESSION['msg']['tipo'] = "success";		
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro Plano de Contas!!!";
		$_SESSION['msg']['tipo'] = "error";			
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("planoContas.php");

?>
