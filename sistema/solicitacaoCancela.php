<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputSolicitacaoId'])){
	
	$iSolicitacao = $_POST['inputSolicitacaoId'];
	$sMotivo = $_POST['inputMotivo'];
	
	try{

		$conn->beginTransaction();

		// Selecionando o id da Bandeja 
		$sql = "SELECT BandeId
		FROM Bandeja
		WHERE BandeTabelaId =  ". $iSolicitacao ." and BandeTabela = 'Solicitacao' ";
		$result = $conn->query($sql);
		$Bandeja= $result->fetch(PDO::FETCH_ASSOC);

		/*----- DELETA BANDEJA X PERFIL -----*/
		$sql = "DELETE FROM BandejaXPerfil
				WHERE BnXPeBandeja = :id";
		$result = $conn->prepare($sql);
		$result->bindParam(':id', $Bandeja['BandeId']); 
		$result->execute();

		/*----- DELETA BANDEJA -----*/
		$sql = "DELETE FROM Bandeja
				WHERE BandeId = :id";
		$result = $conn->prepare($sql);
		$result->bindParam(':id', $Bandeja['BandeId']); 
		$result->execute();
		 
		// Selecionando o id da situacao 'CANCELADO'
		$sql = "SELECT SituaId
				FROM Situacao
				WHERE SituaChave = 'CANCELADO' ";
		$result = $conn->query($sql);
		$situacao = $result->fetch(PDO::FETCH_ASSOC);

		/*----- MUDA SITUACAO e MOTIVO -----*/
		$sql = "UPDATE Solicitacao SET SolicSituacao = :iSituacao, SolicMotivo = :sMotivo
				WHERE SolicId = :iSolicitacao";
		$result = $conn->prepare($sql);
		$result->bindParam(':iSituacao', $situacao['SituaId']); 
		$result->bindParam(':sMotivo', $sMotivo); 		
		$result->bindParam(':iSolicitacao', $iSolicitacao); 		
		$result->execute();

		$conn->commit();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Solicitação cancelada!!!";
		$_SESSION['msg']['tipo'] = "success";		
		
	} catch(PDOException $e) {
		
		$conn->rollback();

		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao cancelar solicitação!!!";
		$_SESSION['msg']['tipo'] = "error";			
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("solicitacao.php");

?>