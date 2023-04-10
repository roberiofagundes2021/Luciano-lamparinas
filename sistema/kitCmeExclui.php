<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputKitCmeId'])){
	
	$iKitCme = $_POST['inputKitCmeId'];
        	
	try{
		
		$sql = "DELETE FROM KitCme
				WHERE KtCmeId = :id";
		$result = $conn->prepare($sql);
		$result->bindParam(':id', $iKitCme); 
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Kit CME excluído!!!";
		$_SESSION['msg']['tipo'] = "success";		
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao excluir Kit CME !!! O registro a ser excluído está sendo usado em outro local.";
		$_SESSION['msg']['tipo'] = "error";			
		
		//echo 'Error: ' . $e->getMessage();die;
	}
}

irpara("kitCme.php");

?>
