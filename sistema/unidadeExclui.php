<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputUnidadeId'])){        	
	try{
		//$conn->beginTransaction();

		$iUnidade = $_POST['inputUnidadeId'];
/*
		$sql = "DELETE FROM PadraoPerfilXPermissao WHERE PaPrXPeUnidade = $iUnidade";
		$result = $conn->query($sql);
		$result = $result->fetch(PDO::FETCH_ASSOC);

		$sql = "DELETE FROM PerfilXPermissao WHERE PrXPeUnidade = $iUnidade";
		$result = $conn->query($sql);
		$result = $result->fetch(PDO::FETCH_ASSOC);

		// $sql = "DELETE FROM Perfil WHERE PerfiUnidade = $iUnidade";
		// $result = $conn->query($sql);
		// $result = $result->fetch(PDO::FETCH_ASSOC);

		$sql = "DELETE FROM LocalEstoque WHERE LcEstUnidade = $iUnidade";
		$result = $conn->query($sql);
		$result = $result->fetch(PDO::FETCH_ASSOC);

		$sql = "DELETE FROM FormaPagamento WHERE FrPagUnidade = $iUnidade";
		$result = $conn->query($sql);
		$result = $result->fetch(PDO::FETCH_ASSOC);

		$sql = "DELETE FROM GrupoConta WHERE GrConUnidade = $iUnidade";
		$result = $conn->query($sql);
		$result = $result->fetch(PDO::FETCH_ASSOC);

		$sql = "DELETE FROM AtendimentoClassificacao WHERE AtClaId = $iUnidade";
		$result = $conn->query($sql);
		$result = $result->fetch(PDO::FETCH_ASSOC);

		$sql = "DELETE FROM AtendimentoClassificacaoRisco WHERE AtClRUnidade = $iUnidade";
		$result = $conn->query($sql);
		$result = $result->fetch(PDO::FETCH_ASSOC);

		$sql = "DELETE FROM CentroCusto WHERE CnCusUnidade = $iUnidade";
		$result = $conn->query($sql);
		$result = $result->fetch(PDO::FETCH_ASSOC);

		$sql = "DELETE FROM AtendimentoModalidade WHERE AtModUnidade = $iUnidade";
		$result = $conn->query($sql);
		$result = $result->fetch(PDO::FETCH_ASSOC);

		$sql = "DELETE FROM UsuarioXUnidade WHERE UsXUnUnidade = $iUnidade";
		$result = $conn->query($sql);
		$result = $result->fetch(PDO::FETCH_ASSOC);

		$sql = "DELETE FROM Setor WHERE SetorUnidade = $iUnidade";
		$result = $conn->query($sql);
		$result = $result->fetch(PDO::FETCH_ASSOC);
*/		

		/* Não precisa mais excluir as tabelas que são geradas pelo sistema quando se cria uma 
		nova unidade, pois todas essas tabelas estão excluindo em cascata. Poranto, todas as 
		tabelas (geradas pelo sistema) que contenham a unidade que está sendo excluída está 
		excluindo em cascata */
		$sql = "DELETE FROM Unidade WHERE UnidaId = :id";
		$result = $conn->prepare($sql);
		$result->bindParam(':id', $iUnidade); 
		$result->execute();		

		//$conn->commit();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Unidade excluída!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {
		//$conn->rollback();
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Não é possível excluir essa unidade, pois existem registros ligados a ela!!!";
		$_SESSION['msg']['tipo'] = "error";
		
		var_dump($e);die;
	}
}

irpara("unidade.php");

?>
