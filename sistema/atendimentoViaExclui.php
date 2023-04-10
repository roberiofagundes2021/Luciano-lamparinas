<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputViaId'])){
	
	$iVia = $_POST['inputViaId'];
        	
	try{
		
		$sql = "DELETE FROM Via
				WHERE ViaId = :id";
		$result = $conn->prepare($sql);
		$result->bindParam(':id', $iVia); 
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Via excluída!!!";
		$_SESSION['msg']['tipo'] = "success";		
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao excluir via!!! O registro a ser excluído está sendo usado em outro local.";
		$_SESSION['msg']['tipo'] = "error";			
		
		//echo 'Error: ' . $e->getMessage();die;
	}
}

irpara("atendimentoVia.php");

?>
