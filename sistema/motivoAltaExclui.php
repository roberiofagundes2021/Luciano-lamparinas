<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputMotivoAltaId'])){
	
	$iMotivoAlta = $_POST['inputMotivoAltaId'];
        	
	try{
		
		$sql = "DELETE FROM MotivoAlta
				WHERE MtAltId = :id";
		$result = $conn->prepare($sql);
		$result->bindParam(':id', $iMotivoAlta); 
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Motivo da alta excluída!!!";
		$_SESSION['msg']['tipo'] = "success";		
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao excluir motivo da alta!!! O registro a ser excluído está sendo usado em outro local.";
		$_SESSION['msg']['tipo'] = "error";			
		
		//echo 'Error: ' . $e->getMessage();die;
	}
}

irpara("motivoAlta.php");

?>
