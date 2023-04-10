<?php

include_once("sessao.php");

include('global_assets/php/conexao.php');

if (isset($_POST['inputContasAReceberId'])) {

	$id = $_POST['inputContasAReceberId'];

	try {
		$sql = "DELETE FROM ContasAReceberXCentroCusto
				WHERE CARXCContasAReceber = :id";
		$result = $conn->prepare($sql);
		$result->bindParam(':id', $id); 
		$result->execute();

		$sql = "DELETE FROM ContasAReceber
				WHERE CnAReId = :id";
		$result = $conn->prepare($sql);
		$result->bindParam(':id', $id);
		$result->execute();

		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Conta excluída!!!";
		$_SESSION['msg']['tipo'] = "success";
	} catch (PDOException $e) {

		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao excluir Conta!!!";
		$_SESSION['msg']['tipo'] = "error";
	}
}

irpara("contasAReceber.php");
