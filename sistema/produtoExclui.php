<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputProdutoId'])){
	
	$iProduto = $_POST['inputProdutoId'];
	$pasta = "global_assets/images/produtos/";
        	
	try{
		
		$sql = "SELECT ProduFoto
				FROM Produto
				Where ProduId = $iProduto";
		$result = $conn->query("$sql");
		$rowFoto = $result->fetch(PDO::FETCH_ASSOC);
		$sFoto = $rowFoto['ProduFoto'];
		
		$sql = "DELETE FROM Produto
				WHERE ProduId = :id";
		$result = $conn->prepare("$sql");
		$result->bindParam(':id', $iProduto);
		$result->execute();
		
		if (file_exists($pasta.$sFoto) and $sFoto <> ""){
			unlink($pasta.$sFoto);
		}		  
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Produto excluído!!!";
		$_SESSION['msg']['tipo'] = "success";		
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao excluir Produto!!!";
		$_SESSION['msg']['tipo'] = "error";			
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("produto.php");

?>
