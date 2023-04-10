<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputTipoFiscalId'])){
	
	$iTipoFiscal = $_POST['inputTipoFiscalId'];
        	
	try{
		
		$sql = "DELETE FROM TipoFiscal
				WHERE TpFisId = :id";
		$result = $conn->prepare("$sql");
		$result->bindParam(':id', $iTipoFiscal); 
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Tipo Fiscal excluído!!!";
		$_SESSION['msg']['tipo'] = "success";		
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao excluir o Tipo Fiscal!!!";
		$_SESSION['msg']['tipo'] = "error";			
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("tipoFiscal.php");

?>
