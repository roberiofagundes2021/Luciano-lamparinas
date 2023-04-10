<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputInventarioId'])){
	
	$iInventario = $_POST['inputInventarioId'];
        	
	try{
		
		$sql = "DELETE FROM Inventario
				WHERE InvenId = :id";
		$result = $conn->prepare("$sql");
		$result->bindParam(':id', $iInventario);
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Inventário excluído!!!";
		$_SESSION['msg']['tipo'] = "success";		
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao excluir Inventário!!!";
		$_SESSION['msg']['tipo'] = "error";			
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("inventario.php");

?>
