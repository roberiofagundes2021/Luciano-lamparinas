<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputAditivoId'])){
	
	$iAditivo = $_POST['inputAditivoId'];
	$iFluxoOperacional = $_SESSION['FluxoId'];
        	
	try{
		$conn->beginTransaction();	
		
		$sql = "DELETE FROM AditivoXProduto
				WHERE AdXPrAditivo = :iAditivo and AdXPrUnidade = :iUnidade";
		$result = $conn->prepare($sql);
		$result->bindParam(':iAditivo', $iAditivo);
		$result->bindParam(':iUnidade', $_SESSION['UnidadeId']); 
        $result->execute();
        
        $sql = "DELETE FROM AditivoXServico
				WHERE AdXSrAditivo = :iAditivo and AdXSrUnidade = :iUnidade";
		$result = $conn->prepare($sql);
		$result->bindParam(':iAditivo', $iAditivo);
		$result->bindParam(':iUnidade', $_SESSION['UnidadeId']); 
		$result->execute();
		
		
		$sql = "DELETE FROM Aditivo
				WHERE AditiId = :id";
		$result = $conn->prepare($sql);
		$result->bindParam(':id', $iAditivo); 
		$result->execute();

		//// Mudando status do fluxo, após excluir o aditivo.
		$sql = "SELECT SituaId
				FROM Situacao
				WHERE SituaChave = 'LIBERADO' ";
		$result = $conn->query($sql);
		$rowStatus = $result->fetch(PDO::FETCH_ASSOC);
		$bStatus = $rowStatus['SituaId'];

		$sql = "UPDATE FluxoOperacional SET FlOpeStatus = :bStatus
	            WHERE FlOpeId = :id";
		$result = $conn->prepare($sql);
		$result->bindParam(':bStatus', $bStatus);
		$result->bindParam(':id', $iFluxoOperacional);
		$result->execute();

		
		// Selecionando o id da Bandeja 
		$sql = "SELECT BandeId
		FROM Bandeja
		WHERE BandeTabelaId =  ". $iAditivo ." ";
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


		
		$conn->commit();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Aditivo excluído!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {
			
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao excluir Aditivol!!!";
		$_SESSION['msg']['tipo'] = "error";			
		
		$conn->rollback();
		
		//echo 'Error: ' . $e->getMessage();
	}
}

irpara("fluxoAditivo.php");

?>
