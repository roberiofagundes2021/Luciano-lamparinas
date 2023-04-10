<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

$_SESSION['msg'] = array();

if(isset($_POST['inputViaId'])){
	
	$iVia = $_POST['inputViaId'];
	$sStatus = $_POST['inputViaStatus'] == 'ATIVO' ? 'INATIVO' : 'ATIVO';
        	
	try{

		$sql = "SELECT SituaId
				FROM Situacao
			    WHERE SituaChave = '". $sStatus."'";
		$result = $conn->query($sql);
		$row = $result->fetch(PDO::FETCH_ASSOC);
		$iStatus = $row['SituaId'];
		
		$sql = "UPDATE Via SET ViaStatus = :iStatus
				WHERE ViaId = :id";
		$result = $conn->prepare($sql);
		$result->bindParam(':iStatus', $iStatus); 
		$result->bindParam(':id', $iVia); 
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Situação da Via alterada!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao alterar situação da Via!!!";
		$_SESSION['msg']['tipo'] = "error";
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("atendimentoVia.php");

?>
