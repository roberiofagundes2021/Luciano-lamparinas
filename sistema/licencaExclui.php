<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputLicencaId'])){
	
	$iLicenca = $_POST['inputLicencaId'];
        	
	try{
		
		$sql = "DELETE FROM Licenca
				WHERE LicenId = :id";
		$result = $conn->prepare("$sql");
		$result->bindParam(':id', $iLicenca);
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Licença excluída!!!";
		$_SESSION['msg']['tipo'] = "success";		
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao excluir Licença!!!";
		$_SESSION['msg']['tipo'] = "error";			
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("licenca.php");

?>
