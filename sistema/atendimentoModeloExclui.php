<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputModeloId'])){
	
	$iModelo = $_POST['inputModeloId'];
        	
	try{
		
		$sql = "DELETE FROM AtendimentoModelo
				WHERE AtModId = :id";
		$result = $conn->prepare($sql);
		$result->bindParam(':id', $iModelo); 
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Modelo excluído!!!";
		$_SESSION['msg']['tipo'] = "success";		
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao excluir grupo!!! O registro a ser excluído está sendo usado em outro local.";
		$_SESSION['msg']['tipo'] = "error";			
		
		//echo 'Error: ' . $e->getMessage();die;
	}
}

irpara("atendimentoModelo.php");

?>
