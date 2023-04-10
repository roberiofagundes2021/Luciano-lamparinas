<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputQuartoId'])){
	
	$iQuartoId = $_POST['inputQuartoId'];
        	
	try{
		
		$sql = "DELETE FROM Quarto
				WHERE QuartId = :id";
		$result = $conn->prepare($sql);
		$result->bindParam(':id', $iQuartoId); 
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Relação de Quarto excluído!!!";
		$_SESSION['msg']['tipo'] = "success";		
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao excluir Relação de Quarto!!! O registro está sendo usado em outro local.";
		$_SESSION['msg']['tipo'] = "error";			
		
		//echo 'Error: ' . $e->getMessage();die;
	}
}

irpara("atendimentoRelacaoQuartos.php");

?>
