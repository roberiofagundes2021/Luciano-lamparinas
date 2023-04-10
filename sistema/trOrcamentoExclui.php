<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

	$iOrcamento = $_POST['inputOrcamentoId'];

	$sql = "SELECT  TrXOrNumero
			FROM TRXOrcamento
			JOIN TermoReferencia on TrRefId = TrXOrTermoReferencia
			WHERE TrXOrId = $iOrcamento ";
	$result = $conn->query($sql);
	$row = $result->fetch(PDO::FETCH_ASSOC);

if(isset($_POST['inputOrcamentoId'])){
		
	try{
		$conn->beginTransaction();	
		
		$sql = "DELETE FROM TRXOrcamentoXProduto
				WHERE TXOXPOrcamento = :iOrcamento and TXOXPUnidade = :iUnidade";
		$result = $conn->prepare($sql);
		$result->bindParam(':iOrcamento', $iOrcamento);
		$result->bindParam(':iUnidade', $_SESSION['UnidadeId']); 
		$result->execute();
		
		$sql = "DELETE FROM TRXOrcamentoXSubcategoria
				WHERE TXOXSCOrcamento = :id";
		$result = $conn->prepare($sql);
		$result->bindParam(':id', $iOrcamento); 
		$result->execute();		
		
		$sql = "DELETE FROM TRXOrcamento
				WHERE TrXOrId = :id";
		$result = $conn->prepare($sql);
		$result->bindParam(':id', $iOrcamento); 
		$result->execute();

		$sql = "INSERT INTO AuditTR ( AdiTRTermoReferencia, AdiTRDataHora, AdiTRUsuario, AdiTRTela, AdiTRDetalhamento)
				VALUES (:iTRTermoReferencia, :iTRDataHora, :iTRUsuario, :iTRTela, :iTRDetalhamento)";
		$result = $conn->prepare($sql);
				
		$result->execute(array(
			':iTRTermoReferencia' => $_SESSION['TRId'],
			':iTRDataHora' => date("Y-m-d H:i:s"),
			':iTRUsuario' => $_SESSION['UsuarId'],
			':iTRTela' =>'ORÇAMENTO',
			':iTRDetalhamento' =>' EXCLUSÃO DO ORÇAMENTO DE Nº '. $row['TrXOrNumero']. ' '
		));

		
		$conn->commit();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Orcamento excluído!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {
			
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao excluir orcamento!!!";
		$_SESSION['msg']['tipo'] = "error";			
		
		$conn->rollback();
		
		//echo 'Error: ' . $e->getMessage();
	}
}

irpara("trOrcamento.php");

?>
