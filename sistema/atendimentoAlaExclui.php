<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputAlaId'])){
	
	$iAla = $_POST['inputAlaId'];
        	
	try{
		
		$sql = "DELETE FROM Ala
				WHERE AlaId = :id";
		$result = $conn->prepare("$sql");
		$result->bindParam(':id', $iAla); 
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Ala excluída!!!";
		$_SESSION['msg']['tipo'] = "success";		
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao excluir ala!!! O registro está sendo usado em outro local.";
		$_SESSION['msg']['tipo'] = "error";			
		
		//echo 'Error: ' . $e->getMessage();
	}
}

irpara("atendimentoAla.php");

?>
