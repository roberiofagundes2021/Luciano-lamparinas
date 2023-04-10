<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

$sDestino = $_POST['inputOrigem'];

if(isset($_POST['inputFluxoOperacionalId'])){
	
	$iFluxoOperacional = $_POST['inputFluxoOperacionalId'];
        	
	try{
		$conn->beginTransaction();	
		
		$sql = "DELETE FROM FluxoOperacionalXProduto
				WHERE FOXPrFluxoOperacional = :iFluxoOperacional and FOXPrUnidade = :iUnidade";
		$result = $conn->prepare($sql);
		$result->bindParam(':iFluxoOperacional', $iFluxoOperacional);
		$result->bindParam(':iUnidade', $_SESSION['UnidadeId']); 
		$result->execute();

		$sql = "DELETE FROM FluxoOperacionalXSubCategoria
				WHERE FOXSCFluxo = :iFluxo and FOXSCUnidade = :iUnidade";
		$result = $conn->prepare($sql);
		$result->bindParam(':iFluxo', $iFluxoOperacional);
		$result->bindParam(':iUnidade', $_SESSION['UnidadeId']); 
		$result->execute();
		
		
		$sql = "DELETE FROM FluxoOperacional
				WHERE FlOpeId = :id";
		$result = $conn->prepare($sql);
		$result->bindParam(':id', $iFluxoOperacional); 
		$result->execute();
		
		$conn->commit();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Fluxo Operacional excluído!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {
			
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao excluir Fluxo Operacional!!!";
		$_SESSION['msg']['tipo'] = "error";			
		
		$conn->rollback();
		
		//echo 'Error: ' . $e->getMessage();
	}
}

irpara($sDestino);

?>
