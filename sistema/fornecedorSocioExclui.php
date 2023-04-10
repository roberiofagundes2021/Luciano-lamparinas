<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputFornecedorXSocioId'])){
	
	$iFornecedorXSocio = $_POST['inputFornecedorXSocioId'];
        	
	try{
		
		$sql = "DELETE FROM FornecedorXSocio
				WHERE FrXSoId  = :id";
		$result = $conn->prepare($sql);
		$result->bindParam(':id', $iFornecedorXSocio);
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Sócio excluído!!!";
		$_SESSION['msg']['tipo'] = "success";		
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao excluir Sócio!!!";
		$_SESSION['msg']['tipo'] = "error";			
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("fornecedorSocio.php");

?>
