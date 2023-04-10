<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputTipoAltaId'])){
	
	$iTipoAlta = $_POST['inputTipoAltaId'];
        	
	try{
		
		$sql = "DELETE FROM TipoAlta
				WHERE TpAltId = :id";
		$result = $conn->prepare($sql);
		$result->bindParam(':id', $iTipoAlta); 
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Tipo de alta excluída!!!";
		$_SESSION['msg']['tipo'] = "success";		
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao excluir tipo de alta!!! O registro a ser excluído está sendo usado em outro local.";
		$_SESSION['msg']['tipo'] = "error";			
		
		//echo 'Error: ' . $e->getMessage();die;
	}
}

irpara("atendimentoTipoAlta.php");

?>
