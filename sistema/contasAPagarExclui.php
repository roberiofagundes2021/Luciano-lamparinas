<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputContasAPagarId'])){
	$conn->beginTransaction();
	
    $id = $_POST['inputContasAPagarId'];
	$iUnidade = $_SESSION['UnidadeId'];
	$iEmpresa = $_SESSION['EmpreId'];

    try{
		$sql = "SELECT CnAPaId, CnAPaMovimentacao
			FROM ContasAPagar
			WHERE CnAPaId = $id and CnAPaUnidade = $iUnidade";
		$result = $conn->query($sql);
		$rowIdMovimentacao = $result->fetch(PDO::FETCH_ASSOC);

		if($rowIdMovimentacao['CnAPaMovimentacao']){
			$iMovimentacao = $rowIdMovimentacao['CnAPaMovimentacao'];

			// consulta para saber se a empresa é pública ou privada
			$sql = "SELECT ParamEmpresaPublica
				FROM Parametro
				WHERE ParamEmpresa = $iEmpresa";
			$result = $conn->query($sql);
			$rowParametro = $result->fetch(PDO::FETCH_ASSOC);

			$situaStatusNovo = $rowParametro['ParamEmpresaPublica'] == 1?'AGUARDANDOLIBERACAOCONTABILIDADE':'AGUARDANDOLIBERACAOCENTRO';
			$situaStatusAntigo = $rowParametro['ParamEmpresaPublica'] == 1?'LIBERADOCONTABILIDADE':'LIBERADOCENTRO';

			// se for pública, a situação será "AGUARDANDOLIBERACAOCONTABILIDADE"
			// se for privada será "AGUARDANDOLIBERACAOCENTRO"
			$sql = "SELECT SituaId, SituaNome, SituaChave
				FROM Situacao
				WHERE SituaChave = '$situaStatusNovo'";
			$result = $conn->query($sql);
			$situa = $result->fetch(PDO::FETCH_ASSOC);

			$sql = "SELECT BandeId
				FROM Bandeja
				JOIN Situacao on SituaId = BandeStatus
				WHERE BandeTabelaId = $iMovimentacao and SituaChave = '$situaStatusAntigo'";
			$result = $conn->query($sql);
			$situaBandeja = $result->fetch(PDO::FETCH_ASSOC);

			// aqui altera o status para que seja mostrado novamente na bandeja
			if(isset($situaBandeja['BandeId'])){
				$sql = "UPDATE Bandeja
					SET BandeStatus = $situa[SituaId]
					WHERE BandeId = $situaBandeja[BandeId]";
				$result = $conn->query($sql);
			}

			// aqui altera o status para que seja mostrado em movimentação
			$sql = "UPDATE Movimentacao set MovimSituacao = $situa[SituaId]
			WHERE MovimId = $iMovimentacao";
			$result = $conn->query($sql);
		}
		
		$sql = "DELETE FROM ContasAPagarXCentroCusto
				WHERE CAPXCContasAPagar = :id";
		$result = $conn->prepare($sql);
		$result->bindParam(':id', $id); 
		$result->execute();
		
		$sql = "DELETE FROM ContasAPagar
				WHERE CnAPaId = :id";
		$result = $conn->prepare($sql);
		$result->bindParam(':id', $id); 
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Conta excluída!!!";
		$_SESSION['msg']['tipo'] = "success";

		$conn->commit();
		
	} catch(PDOException $e) {
		var_dump($e);
		$conn->rollback();
		exit;
			
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao excluir Conta!!!";
		$_SESSION['msg']['tipo'] = "error";			
	}
}

irpara("contasAPagar.php");

?>
