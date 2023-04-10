<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputTRId'])){
	
	$iTR = $_POST['inputTRId'];
	$iUnidade = $_SESSION['UnidadeId'];
        	
	try{
		$conn->beginTransaction();	

		/* Aqui não estou usando o Foreign Key on Cascade. Portanto, preciso excluir primeiro o TRXOrcamentoXProduto, 
		TRXOrcamentoXServico e TRXOrcamentoXSubCategoria */

		$sql = "SELECT TrXOrId
				FROM TRXOrcamento
			 	WHERE TrXOrTermoReferencia = $iTR AND TrXOrUnidade = $iUnidade ";
		$result = $conn->query($sql);
		$rowOrcamentosTR = $result->fetchAll(PDO::FETCH_ASSOC);
		
		foreach ($rowOrcamentosTR as $item){
		   
			$iOrcamento = $item['TrXOrId'];
		   
			$sql = "DELETE FROM TRXOrcamentoXSubCategoria
				 	WHERE TXOXSCOrcamento = :iOrcamento AND TXOXSCUnidade = :iUnidade ";
			$result = $conn->prepare($sql);
			$result->bindParam(':iOrcamento', $iOrcamento);
			$result->bindParam(':iUnidade', $iUnidade); 
			$result->execute();
			
			$sql = "DELETE FROM TRXOrcamentoXProduto
				 	WHERE TXOXPOrcamento = :iOrcamento AND TXOXPUnidade = :iUnidade	";
			$result = $conn->prepare($sql);
			$result->bindParam(':iOrcamento', $iOrcamento);
			$result->bindParam(':iUnidade', $iUnidade); 
			$result->execute();	

			$sql = "DELETE FROM TRXOrcamentoXServico
				 	WHERE TXOXSOrcamento = :iOrcamento AND TXOXSUnidade = :iUnidade ";
			$result = $conn->prepare($sql);
			$result->bindParam(':iOrcamento', $iOrcamento);
			$result->bindParam(':iUnidade', $iUnidade); 
			$result->execute();				
		}
		   
		$sql = "DELETE FROM TRXOrcamento
			 	WHERE TrXOrTermoReferencia = :iTR AND TrXOrUnidade = :iUnidade ";
		$result = $conn->prepare($sql);
		$result->bindParam(':iTR', $iTR);
		$result->bindParam(':iUnidade', $iUnidade); 
		$result->execute();
		
		$sql = "DELETE FROM TermoReferenciaXProduto
			 	WHERE TRXPrTermoReferencia = :iTR AND TRXPrUnidade = :iUnidade ";
		$result = $conn->prepare($sql);
		$result->bindParam(':iTR', $iTR);
		$result->bindParam(':iUnidade', $iUnidade); 
		$result->execute();
		
		$sql = "DELETE FROM TermoReferenciaXServico
			 	WHERE TRXSrTermoReferencia = :iTR AND TRXSrUnidade = :iUnidade ";
		$result = $conn->prepare($sql);
		$result->bindParam(':iTR', $iTR);
		$result->bindParam(':iUnidade', $iUnidade); 
		$result->execute();	
	
		$sql = "DELETE FROM TRXEquipe
				WHERE TRXEqTermoReferencia = :id ";
		$result = $conn->prepare($sql);
		$result->bindParam(':id', $iTR);
		$result->execute();

		$sql = "DELETE FROM TRXSubcategoria
				WHERE TRXSCTermoReferencia = :id ";
		$result = $conn->prepare($sql);
		$result->bindParam(':id', $iTR); 
		$result->execute();
		
		$sql = "DELETE FROM TermoReferencia
			 	WHERE TrRefId = :id	";
		$result = $conn->prepare($sql);
		$result->bindParam(':id', $iTR); 
		$result->execute();

		$sql = "DELETE FROM AuditTR
			 	WHERE AdiTRTermoReferencia = :id	";
		$result = $conn->prepare($sql);
		$result->bindParam(':id', $iTR); 
		$result->execute();

		$conn->commit();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Termo de Referência excluído!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {
			
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao excluir termo de referência!!!";
		$_SESSION['msg']['tipo'] = "error";			
		
		$conn->rollback();
		
		echo 'Error: ' . $e->getMessage().$e->getLine();
		die;
	}
}

irpara("tr.php");

?>
