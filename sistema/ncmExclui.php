<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputNcmId'])){
	
	$iNcm = $_POST['inputNcmId'];
        	
	try{
		
		$sql = "DELETE FROM Ncm
				WHERE NcmId = :id";
		$result = $conn->prepare("$sql");
		$result->bindParam(':id', $iNcm); 
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "NCM excluído!!!";
		$_SESSION['msg']['tipo'] = "success";		
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao excluir NCM!!!";
		$_SESSION['msg']['tipo'] = "error";			
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("ncm.php");

?>
