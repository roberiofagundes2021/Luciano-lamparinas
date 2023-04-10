<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputAtendimentoClassificacaoId'])){
	
	$iUnidade = $_SESSION['UnidadeId'];
	$iAtendimentoClassificacao = $_POST['inputAtendimentoClassificacaoId'];

	// essa lista é de "AtClaChave" que são criadas por padram em cada unidade nova
	// e, portanto, não devem ser excluídas
	$arrayNativos= [
		'AMBULATORIAL',
		'ELETIVO',
		'HOSPITALAR',
		'ODONTOLOGICO'
	];
        	
	try{
		$sql = "SELECT AtClaChave FROM AtendimentoClassificacao
				WHERE AtClaId = $iAtendimentoClassificacao and AtClaUnidade = $iUnidade";
		$result = $conn->query($sql);
		$row = $result->fetch(PDO::FETCH_ASSOC);

		if(!in_array($row['AtClaChave'], $arrayNativos)){
			$sql = "DELETE FROM AtendimentoClassificacao
					WHERE AtClaId = :id";
			$result = $conn->prepare($sql);
			$result->bindParam(':id', $iAtendimentoClassificacao);
			$result->execute();
			
			$_SESSION['msg']['titulo'] = "Sucesso";
			$_SESSION['msg']['mensagem'] = "Classificação do atendimento excluída!!!";
			$_SESSION['msg']['tipo'] = "success";
		} else {
			$_SESSION['msg']['titulo'] = "Erro";
			$_SESSION['msg']['mensagem'] = "Classificação do atendimento não pode ser excluída por se tratar de uma classificação nativa do sistema!!!";
			$_SESSION['msg']['tipo'] = "error";
		}
		
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao excluir classificação do atendimento!!!";
		$_SESSION['msg']['tipo'] = "error";			
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("atendimentoClassificacao.php");

?>
