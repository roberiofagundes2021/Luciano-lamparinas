<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

$_SESSION['msg'] = array();

if(isset($_POST['inputCid10Id'])){
	
	$iCid10 = $_POST['inputCid10Id'];
	$sStatus = $_POST['inputCid10Status'] == 'ATIVO' ? 'INATIVO' : 'ATIVO';
        	
	try{

		$sql = "SELECT SituaId
				FROM Situacao
			    WHERE SituaChave = '". $sStatus."'";
		$result = $conn->query($sql);
		$row = $result->fetch(PDO::FETCH_ASSOC);
		$iStatus = $row['SituaId'];
		
		$sql = "UPDATE Cid10 SET Cid10Status = :iStatus
				WHERE Cid10Id = :id";
		$result = $conn->prepare("$sql");
		$result->bindParam(':iStatus', $iStatus); 
		$result->bindParam(':id', $iCid10); 
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Situação da cid-10 alterada!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao alterar situação da cid-10!!!";
		$_SESSION['msg']['tipo'] = "error";
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("cid10.php");

?>
