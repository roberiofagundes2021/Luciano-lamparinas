<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputSetorId'])){
	
	$iSetor = $_POST['inputSetorId'];
        	
	try{
		
		$sql = "DELETE FROM Setor
				WHERE SetorId = :id";
		$result = $conn->prepare("$sql");
		$result->bindParam(':id', $iSetor); 
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Setor excluído!!!";
		$_SESSION['msg']['tipo'] = "success";		
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Esse setor está sendo usado, portanto, não pode ser excluído!!!";
		$_SESSION['msg']['tipo'] = "error";			
		
		//echo 'Error: ' . $e->getMessage();
	}
}

irpara("setor.php");

?>
