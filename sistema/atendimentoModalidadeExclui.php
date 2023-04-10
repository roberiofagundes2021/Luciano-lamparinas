<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputModalidadeId'])){
	
	$iModalidade = $_POST['inputModalidadeId'];
        	
	try{
		
		$sql = "DELETE FROM AtendimentoModalidade
				WHERE AtModId = :id";
		$result = $conn->prepare($sql);
		$result->bindParam(':id', $iModalidade); 
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Modalidade excluída!!!";
		$_SESSION['msg']['tipo'] = "success";		
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao excluir modalidade!!! O registro a ser excluído está sendo usado em outro local.";
		$_SESSION['msg']['tipo'] = "error";			
		
		//echo 'Error: ' . $e->getMessage();die;
	}
}

irpara("atendimentoModalidade.php");

?>
