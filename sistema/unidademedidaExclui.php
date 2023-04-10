<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputUnidadeMedidaId'])){
	
	$iUnidadeMedida = $_POST['inputUnidadeMedidaId'];
        	
	try{
		
		$sql = "DELETE FROM UnidadeMedida
				WHERE UnMedId = :id";
		$result = $conn->prepare("$sql");
		$result->bindParam(':id', $iUnidadeMedida); 
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Unidade de Medida excluída!!!";
		$_SESSION['msg']['tipo'] = "success";		
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao excluir unidade de medida!!!";
		$_SESSION['msg']['tipo'] = "error";			
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("unidademedida.php");

?>
