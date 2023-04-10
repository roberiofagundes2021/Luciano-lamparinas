<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

$_SESSION['msg'] = array();

if(isset($_POST['inputLocalEstoqueId'])){
	
	$iLocalEstoque = $_POST['inputLocalEstoqueId'];
	$sStatus = $_POST['inputLocalEstoqueStatus'] == 'ATIVO' ? 'INATIVO' : 'ATIVO';
        	
	try{
		$sql = "SELECT SituaId
				FROM Situacao
			    WHERE SituaChave = '". $sStatus."'";
		$result = $conn->query($sql);
		$row = $result->fetch(PDO::FETCH_ASSOC);
		$iStatus = $row['SituaId'];
		
		$sql = "UPDATE LocalEstoque SET LcEstStatus = :bStatus
				WHERE LcEstId = :id";
		$result = $conn->prepare($sql);
		$result->bindParam(':bStatus', $iStatus); 
		$result->bindParam(':id', $iLocalEstoque); 
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Situação do local do estoque alterada!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao alterar situação do local do estoque!!!";
		$_SESSION['msg']['tipo'] = "error";
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("localEstoque.php");

?>
