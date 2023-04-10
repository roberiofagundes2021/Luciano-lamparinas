<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputFrequenciaId'])){
	
	$iFrequencia = $_POST['inputFrequenciaId'];
        	
	try{
		
		$sql = "DELETE FROM Frequencia
				WHERE FrequId = :id";
		$result = $conn->prepare($sql);
		$result->bindParam(':id', $iFrequencia); 
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Frequência excluída!!!";
		$_SESSION['msg']['tipo'] = "success";		
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao excluir frequência!!! O registro a ser excluído está sendo usado em outro local.";
		$_SESSION['msg']['tipo'] = "error";			
		
		//echo 'Error: ' . $e->getMessage();die;
	}
}

irpara("atendimentoFrequencia.php");

?>
