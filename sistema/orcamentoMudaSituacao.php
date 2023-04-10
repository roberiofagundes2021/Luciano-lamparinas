<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

$_SESSION['msg'] = array();

if(isset($_POST['inputOrcamentoId'])){
	
	$iOrcamento = $_POST['inputOrcamentoId'];
	$sStatus = $_POST['inputOrcamentoStatus'] == 'ATIVO' ? 'INATIVO' : 'ATIVO';
        	
	try{

		$sql = "SELECT SituaId
				FROM Situacao
			    WHERE SituaChave = '". $sStatus."'";
		$result = $conn->query($sql);
		$row = $result->fetch(PDO::FETCH_ASSOC);
		$iStatus = $row['SituaId'];
     
		$sql = "UPDATE Orcamento SET OrcamStatus = ".$iStatus."
				WHERE OrcamId = ".$iOrcamento."";
		$result = $conn->prepare($sql);
		// $result->bindParam(':iStatus', $iStatus); 
		// $result->bindParam(':id', $iOrcamento); 
        $result->execute();
        print($sql);
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Situação do orçamento alterada!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao alterar situação do orçamento!!!";
		$_SESSION['msg']['tipo'] = "error";
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("orcamento.php");

?>
