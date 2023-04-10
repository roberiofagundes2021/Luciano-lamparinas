<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputTipoCuidadoId'])){
	
	$iTipoCuidado = $_POST['inputTipoCuidadoId'];
        	
	try{
		
		$sql = "DELETE FROM TipoCuidado
				WHERE TpCuiId = :id";
		$result = $conn->prepare($sql);
		$result->bindParam(':id', $iTipoCuidado); 
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Tipo de cuidado excluído!!!";
		$_SESSION['msg']['tipo'] = "success";		
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao excluir tipo de cuidado!!! O registro a ser excluído está sendo usado em outro local.";
		$_SESSION['msg']['tipo'] = "error";			
		
		//echo 'Error: ' . $e->getMessage();die;
	}
}

irpara("atendimentoTipoCuidado.php");

?>
