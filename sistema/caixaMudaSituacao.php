<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

$_SESSION['msg'] = array();

if(isset($_POST['inputCaixaId'])){
	
	$iCaixa = $_POST['inputCaixaId'];
	$bStatus = $_POST['inputCaixaStatus']  == 'ATIVO' ? 'INATIVO' : 'ATIVO';
        	
	try{

		$sql = "SELECT SituaId
				FROM Situacao
				WHERE SituaChave = '". $bStatus ."'
				";
		$result = $conn->query($sql);
		$row = $result->fetch(PDO::FETCH_ASSOC);
		$iStatus = $row['SituaId'];
		
		$sql = "UPDATE Caixa SET CaixaStatus = :bStatus
				WHERE CaixaId = :id";
		$result = $conn->prepare($sql);
		$result->bindParam(':bStatus', $iStatus); 
		$result->bindParam(':id', $iCaixa); 
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Situação do Caixa alterada!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao alterar situação do Caixa!!!";
		$_SESSION['msg']['tipo'] = "error";
		
		echo 'Error: ' . $e->getMessage(); exit;
	}
}

irpara("caixa.php");

?>
