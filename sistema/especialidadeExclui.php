<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputEspecialidadeId'])){
	
	$iEspecialidade = $_POST['inputEspecialidadeId'];
        	
	try{
		
		$sql = "DELETE FROM Especialidade
				WHERE EspecId = :id";
		$result = $conn->prepare("$sql");
		$result->bindParam(':id', $iEspecialidade); 
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Especialidade excluído!!!";
		$_SESSION['msg']['tipo'] = "success";		
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao excluir especialidade!!!";
		$_SESSION['msg']['tipo'] = "error";			
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("especialidade.php");

?>
