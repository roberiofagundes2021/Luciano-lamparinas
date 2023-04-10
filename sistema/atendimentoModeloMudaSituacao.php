<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

$_SESSION['msg'] = array();

if(isset($_POST['inputModeloId'])){
	
	$iModelo = $_POST['inputModeloId'];
	$sStatus = $_POST['inputModeloStatus'] == 'ATIVO' ? 'INATIVO' : 'ATIVO';
        	
	try{

		$sql = "SELECT SituaId
				FROM Situacao
			    WHERE SituaChave = '". $sStatus."'";
		$result = $conn->query($sql);
		$row = $result->fetch(PDO::FETCH_ASSOC);
		$iStatus = $row['SituaId'];
		
		$sql = "UPDATE AtendimentoModelo SET AtModStatus = :iStatus
				WHERE AtModId = :id";
		$result = $conn->prepare($sql);
		$result->bindParam(':iStatus', $iStatus); 
		$result->bindParam(':id', $iModelo); 
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Situação do modelo alterado!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao alterar situação do modelo!!!";
		$_SESSION['msg']['tipo'] = "error";
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("atendimentoModelo.php");

?>