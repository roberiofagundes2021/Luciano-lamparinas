<?php

include_once("sessao.php");

include('global_assets/php/conexao.php');

$bProduto = $_POST['bProduto'];
$bServico = $_POST['bServico'];
$iCategoria = $_POST['iCategoria'];
$iSubCategoria = $_POST['iSubCategoria'];
$parametroProduto = $_POST['parametroProduto'];
$parametroServico = $_POST['parametroServico'];
$Produto = '';
$Servico = '';

if ($bProduto) {
	if ($parametroProduto) {
		$sql = "SELECT PrOrcId
				FROM ProdutoOrcamento
				JOIN Situacao on SituaId = PrOrcSituacao
				WHERE PrOrcCategoria = " . $iCategoria . " and SituaChave = 'ATIVO' ";
		if ($iSubCatgoria) {
			$sql .= " and PrOrcSubCategoria in (" . $iSubCategoria . ")";
		}

		$Produto = 'produto orçamento';
	} else {
		$sql = "SELECT ProduId
				FROM Produto
				JOIN Situacao on SituaId = ProduStatus
				WHERE ProduCategoria = " . $iCategoria . " and SituaChave = 'ATIVO' ";
		if ($iSubCatgoria) {
			$sql .= " and ProduSubCategoria in (" . $iSubCategoria . ")";
		}

		$Produto = 'produto';
	}
	$result = $conn->query($sql);
	$row = $result->fetchAll(PDO::FETCH_ASSOC);
	$countProduto = count($row);

	if (!$countProduto) {
		echo "Não possui nenhum " . $Produto . " cadastrado para a Categoria e SubCategoria informado.";
	} else {
		echo 0;
	}
}

if ($bServico) {
	if ($parametroServico) {
		$sql = "SELECT SrOrcId
				FROM ServicoOrcamento
				JOIN Situacao on SituaId = SrOrcSituacao
				WHERE SrOrcCategoria = " . $iCategoria . " and SituaChave = 'ATIVO' ";
		if ($iSubCatgoria) {
			$sql .= " and SrOrcSubCategoria in (" . $iSubCategoria . ")";
		}

		$Servico = 'serviço orçamento';
	} else {
		$sql = "SELECT ServiId
				FROM Servico
				JOIN Situacao on SituaId = ServiStatus
				WHERE ServiCategoria = " . $iCategoria . " and SituaChave = 'ATIVO' ";
		if ($iSubCatgoria) {
			$sql .= " and ServiSubCategoria in (" . $iSubCategoria . ")";
		}

		$Servico = 'serviço';
	}
	$result = $conn->query($sql);
	$row = $result->fetchAll(PDO::FETCH_ASSOC);
	$countServico = count($row);

	if (!$countServico) {
		echo "Não possui nenhum " . $Servico . " cadastrado para a Categoria e SubCategoria informado.";
	} else {
		echo 0;
	}
}
