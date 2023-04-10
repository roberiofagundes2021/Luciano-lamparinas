<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputSubCategoriaId'])){
	
	$iSubCategoria = $_POST['inputSubCategoriaId'];
        	
	try{
		
		$sql = "DELETE FROM SubCategoria
				WHERE SbCatId = :id";
		$result = $conn->prepare("$sql");
		$result->bindParam(':id', $iSubCategoria); 
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Sub Categoria excluída!!!";
		$_SESSION['msg']['tipo'] = "success";		
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao excluir sub categoria!!!";
		$_SESSION['msg']['tipo'] = "error";			
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("subcategoria.php");

?>
