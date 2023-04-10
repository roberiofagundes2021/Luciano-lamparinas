<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

$_SESSION['msg'] = array();

if(isset($_POST['inputCentroCustoId'])){
	
	$iCentroCusto = $_POST['inputCentroCustoId'];
	$bStatus = $_POST['inputCentroCustoStatus'] == 'ATIVO' ? 'INATIVO' : 'ATIVO';
        	
	try{

		$sql = "SELECT SituaId
				FROM Situacao
				WHERE SituaChave = '". $bStatus ."'
				";
		$result = $conn->query($sql);
		$row = $result->fetch(PDO::FETCH_ASSOC);
		$iStatus = $row['SituaId'];
		
		$sql = "UPDATE CentroCusto SET CnCusStatus = :bStatus
				WHERE CnCusId = :id";
		$result = $conn->prepare($sql);
		$result->bindParam(':bStatus', $iStatus); 
		$result->bindParam(':id', $iCentroCusto); 
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Situação do Centro de Custo alterada!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao alterar situação do Centro de Custo!!!";
		$_SESSION['msg']['tipo'] = "error";
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("centroCusto.php");

?>
