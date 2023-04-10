<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputServicoId'])){
	
	$iServico = $_POST['inputServicoId'];
        	
	try{
		
		$sql = "DELETE FROM Servico
				WHERE ServiId = :id";
		$result = $conn->prepare($sql);
		$result->bindParam(':id', $iServico);
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Serviço excluído!!!";
		$_SESSION['msg']['tipo'] = "success";		
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao excluir Serviço!!!";
		$_SESSION['msg']['tipo'] = "error";			
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("servico.php");

?>
