<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

$_SESSION['msg'] = array();

if(isset($_POST['inputFormaPagamentoId'])){
	
	$iFormaPagamento = $_POST['inputFormaPagamentoId'];
	$bStatus = $_POST['inputFormaPagamentoStatus'] == 'ATIVO' ? 'INATIVO' : 'ATIVO';
        	
	try{

		$sql = "SELECT SituaId
				FROM Situacao
				WHERE SituaChave = '". $bStatus ."'
				";
		$result = $conn->query($sql);
		$row = $result->fetch(PDO::FETCH_ASSOC);
		$iStatus = $row['SituaId'];
		
		$sql = "UPDATE formaPagamento SET FrPagStatus = :bStatus
				WHERE FrPagId = :id";
		$result = $conn->prepare($sql);
		$result->bindParam(':bStatus', $iStatus); 
		$result->bindParam(':id', $iFormaPagamento); 
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Situação da Forma de Pagamento alterada!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao alterar situação da Forma de Pagamento!!!";
		$_SESSION['msg']['tipo'] = "error";
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("formaPagamento.php");

?>
