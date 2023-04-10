<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

$_SESSION['msg'] = array();

if(isset($_POST['inputTipoDietaId'])){
	
	$iTipoDieta = $_POST['inputTipoDietaId'];
	$sStatus = $_POST['inputTipoDietaStatus'] == 'ATIVO' ? 'INATIVO' : 'ATIVO';
        	
	try{

		$sql = "SELECT SituaId
				FROM Situacao
			    WHERE SituaChave = '". $sStatus."'";
		$result = $conn->query($sql);
		$row = $result->fetch(PDO::FETCH_ASSOC);
		$iStatus = $row['SituaId'];
		
		$sql = "UPDATE TipoDieta SET TpDieStatus = :iStatus
				WHERE TpDieId = :id";
		$result = $conn->prepare($sql);
		$result->bindParam(':iStatus', $iStatus); 
		$result->bindParam(':id', $iTipoDieta); 
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Situação do tipo de dieta alterada!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao alterar situação do tipo de dieta!!!";
		$_SESSION['msg']['tipo'] = "error";
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("atendimentoTipoDieta.php");

?>
