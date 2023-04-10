<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputFinalisticoId'])){
	
	$iFinalistico = $_POST['inputFinalisticoId'];
        	
	try{
		
		$sql = "DELETE FROM Finalistico
				WHERE FinalId = :id";
		$result = $conn->prepare($sql);
		$result->bindParam(':id', $iFinalistico); 
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Finalístico excluído!!!";
		$_SESSION['msg']['tipo'] = "success";		
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao excluir finalístico!!! O registro a ser excluído está sendo usado em outro local.";
		$_SESSION['msg']['tipo'] = "error";			
		
		//echo 'Error: ' . $e->getMessage();die;
	}
}

irpara("finalistico.php");

?>
