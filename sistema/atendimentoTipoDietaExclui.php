<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputTipoDietaId'])){
	
	$iTipoDieta = $_POST['inputTipoDietaId'];
        	
	try{
		
		$sql = "DELETE FROM TipoDieta
				WHERE TpDieId = :id";
		$result = $conn->prepare($sql);
		$result->bindParam(':id', $iTipoDieta); 
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Tipo de dieta excluída!!!";
		$_SESSION['msg']['tipo'] = "success";		
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao excluir tipo de dieta!!! O registro a ser excluído está sendo usado em outro local.";
		$_SESSION['msg']['tipo'] = "error";			
		
		//echo 'Error: ' . $e->getMessage();die;
	}
}

irpara("atendimentoTipoDieta.php");

?>
