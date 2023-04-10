<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

$_SESSION['msg'] = array();

if(isset($_POST['inputTipoCuidadoId'])){
	
	$iTipoCuidado = $_POST['inputTipoCuidadoId'];
	$sStatus = $_POST['inputTipoCuidadoStatus'] == 'ATIVO' ? 'INATIVO' : 'ATIVO';
        	
	try{

		$sql = "SELECT SituaId
				FROM Situacao
			    WHERE SituaChave = '". $sStatus."'";
		$result = $conn->query($sql);
		$row = $result->fetch(PDO::FETCH_ASSOC);
		$iStatus = $row['SituaId'];
		
		$sql = "UPDATE TipoCuidado SET TpCuiStatus = :iStatus
				WHERE TpCuiId = :id";
		$result = $conn->prepare($sql);
		$result->bindParam(':iStatus', $iStatus); 
		$result->bindParam(':id', $iTipoCuidado); 
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Situação do tipo de cuidado alterado!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao alterar situação do tipo de cuidado!!!";
		$_SESSION['msg']['tipo'] = "error";
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("atendimentoTipoCuidado.php");

?>
