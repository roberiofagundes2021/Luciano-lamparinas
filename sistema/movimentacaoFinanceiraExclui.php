<?php

include_once("sessao.php");

include('global_assets/php/conexao.php');

if (isset($_POST['inputMovimentacaoFinanceiraId'])) {

	if ($_POST['tipoMov'] === 'R') {
		try {
			$sql = "DELETE FROM ContasAReceber
										WHERE CnAReId = :id";
			$result = $conn->prepare($sql);
			$result->bindParam(':id', $_POST['inputMovimentacaoFinanceiraId']);
			$result->execute();

			$_SESSION['msg']['titulo'] = "Sucesso";
			$_SESSION['msg']['mensagem'] = "Conta excluída!!!";
			$_SESSION['msg']['tipo'] = "success";
		} catch (PDOException $e) {

			$_SESSION['msg']['titulo'] = "Erro";
			$_SESSION['msg']['mensagem'] = "Erro ao excluir Conta!!!";
			$_SESSION['msg']['tipo'] = "error";
		}

	} else if ($_POST['tipoMov'] === 'P'){
		try {
			$sql = "DELETE FROM ContasAPagar
										WHERE CnAPaId = :id";
			$result = $conn->prepare($sql);
			$result->bindParam(':id', $_POST['inputMovimentacaoFinanceiraId']);
			$result->execute();

			$_SESSION['msg']['titulo'] = "Sucesso";
			$_SESSION['msg']['mensagem'] = "Conta excluída !!!";
			$_SESSION['msg']['tipo'] = "success";
		} catch (PDOException $e) {

			$_SESSION['msg']['titulo'] = "Erro";
			$_SESSION['msg']['mensagem'] = "Erro ao excluir Conta!!!";
			$_SESSION['msg']['tipo'] = "error";
		}

	} else if ($_POST['tipoMov'] === 'T'){
		try {
			$conn->beginTransaction();

			/*----- DELETA MOVIMENTAÇÃO - ContasTransferencia -----*/
			$sql = "DELETE FROM ContasTransferencia
										WHERE CnTraId = :id";
			$result = $conn->prepare($sql);
			$result->bindParam(':id', $_POST['inputMovimentacaoFinanceiraId']);
			$result->execute();

			/*----- DELETA MOVIMENTAÇÃO - ContasAReceber -----*/
			$sql = "DELETE FROM ContasAReceber
										WHERE CnAReTransferencia = :id";
			$result = $conn->prepare($sql);
			$result->bindParam(':id',$_POST['inputMovimentacaoFinanceiraId']);
			$result->execute();

			/*----- DELETA MOVIMENTAÇÃO - ContasAPagar -----*/
			$sql = "DELETE FROM ContasAPagar
										WHERE CnAPaTransferencia = :id";
			$result = $conn->prepare($sql);
			$result->bindParam(':id', $_POST['inputMovimentacaoFinanceiraId']);
			$result->execute();
	
			$conn->commit();

			$_SESSION['msg']['titulo'] = "Sucesso";
			$_SESSION['msg']['mensagem'] = "Conta excluída!!!";
			$_SESSION['msg']['tipo'] = "success";

		} catch (PDOException $e) {
			$_SESSION['msg']['titulo'] = "Erro";
			$_SESSION['msg']['mensagem'] = "Erro ao excluir Conta!!!";
			$_SESSION['msg']['tipo'] = "error";
		}
	}
}

irpara("movimentacaoFinanceira.php");