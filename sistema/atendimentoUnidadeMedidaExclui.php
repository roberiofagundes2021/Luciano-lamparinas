<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputAtendimentoUnidadeMedidaId'])){
	
	$iAtendimentoUnidadeMedida = $_POST['inputAtendimentoUnidadeMedidaId'];
        	
	try{
		
		$sql = "DELETE FROM AtendimentoUnidadeMedida
				WHERE AtUMeId = :id";
		$result = $conn->prepare("$sql");
		$result->bindParam(':id', $iAtendimentoUnidadeMedida); 
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

irpara("atendimentoUnidademedida.php");

?>
