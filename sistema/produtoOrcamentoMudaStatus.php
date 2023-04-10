<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

$_SESSION['msg'] = array();

if(isset($_POST['inputPrOrcId'])){
	
	$inputPrOrcId = $_POST['inputPrOrcId'];
	$inputPrOrcStatus = $_POST['inputPrOrcStatus'] == 'ATIVO' ? 'INATIVO' : 'ATIVO';
        	
	try{

		$sql = "SELECT SituaId
				FROM Situacao
			    WHERE SituaChave = '$inputPrOrcStatus'  "; // ver essa sintaxe
		$result = $conn->query($sql);
		$row = $result->fetch(PDO::FETCH_ASSOC);
		$iStatus = $row['SituaId'];
		
		$sql = "UPDATE ProdutoOrcamento SET PrOrcSituacao = :bStatus
				WHERE PrOrcId = :id";
		$result = $conn->prepare("$sql");
		$result->bindParam(':bStatus', $iStatus);
		$result->bindParam(':id', $inputPrOrcId);
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Situação do produto alterada!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao alterar situação do produto!!!";
		$_SESSION['msg']['tipo'] = "error";
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("produtoOrcamento.php");

?>
