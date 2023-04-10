<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputFornecedorId'])){
	
	$iFornecedor = $_POST['inputFornecedorId'];
        	
	try{
		
		$sql = "DELETE FROM Fornecedor
				WHERE ForneId = :id";
		$result = $conn->prepare($sql);
		$result->bindParam(':id', $iFornecedor);
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Fornecedor excluído!!!";
		$_SESSION['msg']['tipo'] = "success";		
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao excluir Fornecedor!!!";
		$_SESSION['msg']['tipo'] = "error";			
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("fornecedor.php");

?>
