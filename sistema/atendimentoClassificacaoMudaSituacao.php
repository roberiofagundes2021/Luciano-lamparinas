<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

$_SESSION['msg'] = array();

if(isset($_POST['inputAtendimentoClassificacaoId'])){
	
	$iAtendimentoClassificacao = $_POST['inputAtendimentoClassificacaoId'];
	$sStatus = $_POST['inputAtendimentoClassificacaoStatus'] == 'ATIVO' ? 'INATIVO' : 'ATIVO';
        	
	try{

		$sql = "SELECT SituaId
				FROM Situacao
			    WHERE SituaChave = '". $sStatus."'";
		$result = $conn->query($sql);
		$row = $result->fetch(PDO::FETCH_ASSOC);
		$iStatus = $row['SituaId'];		
		
		$sql = "UPDATE AtendimentoClassificacao SET AtClaStatus = :iStatus
				WHERE AtClaId = :id";
		$result = $conn->prepare("$sql");
		$result->bindParam(':iStatus', $iStatus);
		$result->bindParam(':id', $iAtendimentoClassificacao);
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Situação da classificação do atendimento alterada!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao alterar situação da classificação do atendimento!!!";
		$_SESSION['msg']['tipo'] = "error";
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("atendimentoClassificacao.php");

?>
