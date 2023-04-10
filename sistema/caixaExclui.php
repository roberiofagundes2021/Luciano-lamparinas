<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputCaixaId'])){
	
	$iCaixa = $_POST['inputCaixaId'];
        	
	try{
		
		$sql = "DELETE FROM Caixa
				WHERE CaixaId = :id";
		$result = $conn->prepare($sql);
		$result->bindParam(':id', $iCaixa); 
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Caixa excluído!!!";
		$_SESSION['msg']['tipo'] = "success";		
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro Caixa!!!";
		$_SESSION['msg']['tipo'] = "error";			
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("caixa.php");

?>
