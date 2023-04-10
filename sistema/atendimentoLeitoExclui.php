<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputLeitoId'])){
	
	$iLeito = $_POST['inputLeitoId'];
        	
	try{
		
		$sql = "DELETE FROM Leito
				WHERE LeitoId = :id";
		$result = $conn->prepare($sql);
		$result->bindParam(':id', $iLeito); 
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Leito excluído!!!";
		$_SESSION['msg']['tipo'] = "success";		
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao excluir Leito!!! O registro está sendo usado em outro local.";
		$_SESSION['msg']['tipo'] = "error";			
		
		//echo 'Error: ' . $e->getMessage();die;
	}
}

irpara("atendimentoLeito.php");

?>
