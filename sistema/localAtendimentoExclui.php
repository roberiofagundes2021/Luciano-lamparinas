<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputAtendimentoLocalId'])){
	
	$iAtendimentoLocal = $_POST['inputAtendimentoLocalId'];
        	
	try{
		
		$sql = "DELETE FROM AtendimentoLocal
				WHERE AtLocId = :id";
		$result = $conn->prepare("$sql");
		$result->bindParam(':id', $iAtendimentoLocal); 
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Local de Atendimento excluído!!!";
		$_SESSION['msg']['tipo'] = "success";		
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao excluir local de atendimento!!!";
		$_SESSION['msg']['tipo'] = "error";			
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("localAtendimento.php");

?>
