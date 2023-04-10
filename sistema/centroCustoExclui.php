<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputCentroCustoId'])){
	
	$iCentroCusto = $_POST['inputCentroCustoId'];
        	
	try{
		
		$sql = "DELETE FROM CentroCusto
				WHERE CnCusId = :id";
		$result = $conn->prepare($sql);
		$result->bindParam(':id', $iCentroCusto); 
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Centro de Custo excluído!!!";
		$_SESSION['msg']['tipo'] = "success";		
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao excluir Centro de Custo!!!";
		$_SESSION['msg']['tipo'] = "error";			
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("centroCusto.php");

?>
