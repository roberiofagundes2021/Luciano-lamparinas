<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

$_SESSION['msg'] = array();

if(isset($_POST['inputFabricanteId'])){
	
	$iFabricante = $_POST['inputFabricanteId'];
	$sStatus = $_POST['inputFabricanteStatus']  == 'ATIVO' ? 'INATIVO' : 'ATIVO';
        	
	try{
		$sql = "SELECT SituaId
				FROM Situacao
			    WHERE SituaChave = '". $sStatus."'";
		$result = $conn->query($sql);
		$row = $result->fetch(PDO::FETCH_ASSOC);
		$iStatus = $row['SituaId'];
		
		$sql = "UPDATE Fabricante SET FabriStatus = :bStatus
				WHERE FabriId = :id";
		$result = $conn->prepare("$sql");
		$result->bindParam(':bStatus', $iStatus); 
		$result->bindParam(':id', $iFabricante); 
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Situação do fabricante alterada!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao alterar situação do fabricante!!!";
		$_SESSION['msg']['tipo'] = "error";
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("fabricante.php");

?>
