<?php

include_once("sessao.php");

include('global_assets/php/conexao.php');

if (isset($_POST['produtos']) and $_POST['produtos'] != '') {
	$produtos = $_POST['produtos'];
	$numProdutos = count($produtos);

	$lista = "";

	for ($i = 0; $i < $numProdutos; $i++) {
		$lista .= $produtos[$i] . ",";
	}

	//retira a última vírgula
	$lista = substr($lista, 0, -1);
} else {
	$lista = 0;
}

$iTR = $_POST['idTr'];

$sql = "SELECT TRXPrProduto
		FROM TermoReferenciaXProduto
		JOIN ProdutoOrcamento on PrOrcId = TRXPrProduto
		WHERE TRXPrUnidade = " . $_SESSION['UnidadeId'] . " and TRXPrTermoReferencia = " . $iTR . " and TRXPrTabela = 'ProdutoOrcamento'";
$result = $conn->query($sql);
$rowProdutosOrcamento = $result->fetchAll(PDO::FETCH_ASSOC);


$sql = "SELECT TRXPrProduto
		FROM TermoReferenciaXProduto
		JOIN Produto on ProduId = TRXPrProduto
		WHERE ProduEmpresa = " . $_SESSION['EmpreId'] . " and TRXPrTermoReferencia = " . $iTR . " and TRXPrTabela = 'Produto'";
$result = $conn->query($sql);
$rowProdutos = $result->fetchAll(PDO::FETCH_ASSOC);
$countProdutosTr2 = count($rowProdutos);


//echo $produto;

if (count($rowProdutosOrcamento) >= 1) {

	$sql = "SELECT PrOrcId, PrOrcNome, PrOrcDetalhamento, PrOrcUnidadeMedida, UnMedNome
			FROM ProdutoOrcamento
			JOIN UnidadeMedida on UnMedId = PrOrcUnidadeMedida
			WHERE PrOrcEmpresa = " . $_SESSION['EmpreId'] . " and PrOrcId in (" . $lista . ")
			Order By PrOrcSubCategoria ASC
			";
	//echo $sql;

	$result = $conn->query($sql);
	$rowDupli = $result->fetchAll(PDO::FETCH_ASSOC);
	$row = array_unique($rowDupli, SORT_REGULAR);
	//$count = count($row);
	//echo json_encode($sql);

	$output = '';

	$cont = 0;

	foreach ($row as $item) {

		$cont++;

		$id = $item['PrOrcId'];

		$quantidade = isset($_POST['produtoQuant'][$id]) ? $_POST['produtoQuant'][$id] : '';

		$output .= ' <div class="row" style="margin-top: 8px;">
					<div class="col-lg-9">
						<div class="row">
							<div class="col-lg-1">
								<input type="text" id="inputItem' . $cont . '" name="inputItem' . $cont . '" class="form-control-border-off" value="' . $cont . '" readOnly>
								<input type="hidden" id="inputIdProduto' . $cont . '" name="inputIdProduto' . $cont . '" value="' . $item['PrOrcId'] . '" class="idProduto">
							</div>
							<div class="col-lg-11">
								<input type="text" id="inputProduto' . $cont . '" name="inputProduto' . $cont . '" class="form-control-border-off" data-popup="tooltip" title="' . $item['PrOrcDetalhamento'] . '" value="' . $item['PrOrcNome'] . '" readOnly>
								<input type="hidden" id="inputDetalhamento' . $cont . '" name="inputDetalhamento' . $cont . '" value="' . $item['PrOrcDetalhamento'] . '">
							</div>
						</div>
					</div>								
					<div class="col-lg-1">
						<input type="text" id="inputUnidade' . $cont . '" name="inputUnidade' . $cont . '" class="form-control-border-off" value="' . $item['UnMedNome'] . '" readOnly>
					</div>
					<div class="col-lg-2">
						<input type="text" id="inputQuantidade' . $cont . '" name="inputQuantidade' . $cont . '" class="form-control-border Quantidade pula" onkeypress="pula(event)" value="' . $quantidade . '">
					</div>	
				</div>';

			$output .= '<input type="hidden" id="inputTabelaProduto' . $cont . '" name="inputTabelaProduto' . $cont . '" value="' . 'ProdutoOrcamento ' . '">';
	}

	$output .= '<input type="hidden" id="totalRegistros" name="totalRegistros" value="' . $cont . '" >';

	echo $output;
} else {

	$sql = "SELECT ProduId, ProduNome, ProduDetalhamento, ProduUnidadeMedida, UnMedNome
			FROM Produto
			JOIN Categoria on CategId = ProduCategoria
			JOIN UnidadeMedida on UnMedId = ProduUnidadeMedida
			WHERE ProduEmpresa = ".$_SESSION['EmpreId']." and ProduId in (" . $lista . ")
			";
	//echo $sql;

	$result = $conn->query($sql);
	$row = $result->fetchAll(PDO::FETCH_ASSOC);
	//$count = count($row);
	//echo json_encode($sql);

	$output = '';

	$cont = 0;

	foreach ($row as $item) {

		$cont++;

		$id = $item['ProduId'];

		$quantidade = isset($_POST['produtoQuant'][$id]) ? $_POST['produtoQuant'][$id] : '';

		$output .= ' <div class="row" style="margin-top: 8px;">
					<div class="col-lg-9">
						<div class="row">
							<div class="col-lg-1">
								<input type="text" id="inputItem' . $cont . '" name="inputItem' . $cont . '" class="form-control-border-off" value="' . $cont . '" readOnly>
								<input type="hidden" id="inputIdProduto' . $cont . '" name="inputIdProduto' . $cont . '" value="' . $item['ProduId'] . '" class="idProduto">
							</div>
							<div class="col-lg-11">
								<input type="text" id="inputProduto' . $cont . '" name="inputProduto' . $cont . '" class="form-control-border-off" data-popup="tooltip" title="' . $item['ProduDetalhamento'] . '" value="' . $item['ProduNome'] . '" readOnly>
								<input type="hidden" id="inputDetalhamento' . $cont . '" name="inputDetalhamento' . $cont . '" value="' . $item['ProduDetalhamento'] . '">
							</div>
						</div>
					</div>								
					<div class="col-lg-1">
						<input type="text" id="inputUnidade' . $cont . '" name="inputUnidade' . $cont . '" class="form-control-border-off" value="' . $item['UnMedNome'] . '" readOnly>
					</div>
					<div class="col-lg-2">
						<input type="text" id="inputQuantidade' . $cont . '" name="inputQuantidade' . $cont . '" class="form-control-border Quantidade pula" onkeypress="pula(event)" value="' . $quantidade . '">
					</div>	
				</div>';

			$output .= '<input type="hidden" id="inputTabelaProduto' . $cont . '" name="inputTabelaProduto' . $cont . '" value="' . 'Produto ' . '">';
	}

	$output .= '<input type="hidden" id="totalRegistros" name="totalRegistros" value="' . $cont . '" >';

	echo $output;
}
