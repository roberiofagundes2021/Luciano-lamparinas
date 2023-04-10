<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

$_SESSION['msg'] = array();

if(isset($_POST['inputVeicuId'])){
	
	$iVeiculo = $_POST['inputVeicuId'];
	$sStatus = $_POST['inputVeicuStatus'] == 'ATIVO' ? 'INATIVO' : 'ATIVO';
        	
	try{
		
		$sql = "SELECT SituaId
				FROM  Situacao
				WHERE SituaChave = '". $sStatus."'";
		$result = $conn->query($sql);
		$row = $result->fetch(PDO::FETCH_ASSOC);
		$iStatus = $row['SituaId'];

		$sql = "UPDATE Veiculo SET VeicuStatus = :iStatus
				WHERE VeicuId = :id";
		$result = $conn->prepare($sql);
		$result->bindParam(':iStatus', $iStatus); 
		$result->bindParam(':id', $iVeiculo);
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Situação do Veículo alterada!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao alterar situação do Veículo!!!";
		$_SESSION['msg']['tipo'] = "error";
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("veiculo.php");

?>
