<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputDgNanId'])){
	
	$iNanda = $_POST['inputDgNanId'];
        	
	try{
		
		$sql = "DELETE FROM DiagnosticoNanda
				WHERE DgNanId = :id";
		$result = $conn->prepare("$sql");
		$result->bindParam(':id', $iNanda); 
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Diagnóstico de enfermagem (NANDA) excluída!!!";
		$_SESSION['msg']['tipo'] = "success";		
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao excluir diagnóstico de enfermagem (NANDA)!!!";
		$_SESSION['msg']['tipo'] = "error";			
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("diagnosticoNanda.php");

?>
