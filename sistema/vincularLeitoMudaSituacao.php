<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

$_SESSION['msg'] = array();

if(isset($_POST['inputVincularLeitoId'])){
	
	$iVincularLeito = $_POST['inputVincularLeitoId'];
	$sStatus = $_POST['inputVincularLeitoStatus'] == 'ATIVO' ? 'INATIVO' : 'ATIVO';
        	
	try{
		
		$sql = "SELECT SituaId
				FROM  Situacao
				WHERE SituaChave = '". $sStatus."'";
		$result = $conn->query($sql);
		$row = $result->fetch(PDO::FETCH_ASSOC);
		$iStatus = $row['SituaId'];

		$sql = "UPDATE VincularLeito SET VnLeiStatus = :iStatus
				WHERE VnLeiId = :id";
		$result = $conn->prepare($sql);
		$result->bindParam(':iStatus', $iStatus); 
		$result->bindParam(':id', $iVincularLeito);
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Situação da vinculação do leito alterada!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao alterar situação da vinculação do leito!!!";
		$_SESSION['msg']['tipo'] = "error";
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("vincularLeito.php");

?>
