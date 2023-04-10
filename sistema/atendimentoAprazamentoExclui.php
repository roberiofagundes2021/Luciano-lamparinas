<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputAprazamentoId'])){
	
	$iAprazamento = $_POST['inputAprazamentoId'];
        	
	try{
		
		$sql = "DELETE FROM TipoAprazamento
				WHERE TpAprId = :id";
		$result = $conn->prepare($sql);
		$result->bindParam(':id', $iAprazamento); 
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Tipo de aprazamento excluído!!!";
		$_SESSION['msg']['tipo'] = "success";		
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao excluir grupo!!! O registro a ser excluído está sendo usado em outro local.";
		$_SESSION['msg']['tipo'] = "error";			
		
		//echo 'Error: ' . $e->getMessage();die;
	}
}

irpara("atendimentoAprazamento.php");

?>
