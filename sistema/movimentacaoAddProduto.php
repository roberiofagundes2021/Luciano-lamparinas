<?php

include_once("sessao.php");

include('global_assets/php/conexao.php');

$sql = "SELECT ProduId, ProduNome, ProduValorCusto, ProduCustoFinal, UnMedSigla, ProduDetalhamento, MarcaNome,
		dbo.fnSaldoEstoque(".$_SESSION['UnidadeId'].", ProduId, 'P', '" . $_POST['origem'] . "') as Estoque,
		dbo.fnValidadeProduto(".$_SESSION['UnidadeId'].", ProduId) as Validade
		FROM Produto
		JOIN UnidadeMedida on UnMedId = ProduUnidadeMedida
		LEFT JOIN Marca on MarcaId = ProduMarca
		WHERE ProduEmpresa = " . $_SESSION['EmpreId'] . " and ProduId = " . $_POST['idProduto'];
$result = $conn->query($sql);
$row = $result->fetch(PDO::FETCH_ASSOC);
$count = count($row);


//Verifica se já existe esse registro (se existir, retorna true )
if ($count) {

	if ($row['Estoque'] >= 1) {

		$valorCusto = formataMoeda($row['ProduCustoFinal']);
		$valorTotal = formataMoeda($_POST['quantidade'] * $row['ProduCustoFinal']);

		$total = $_POST['quantidade'] * $row['ProduCustoFinal'];

		$marca = $row['MarcaNome'] != "" ?  " MARCA: " . $row['MarcaNome'] : "";
		$validade = $row['Validade'] != "" ? " - Validade: " . mostraData($row['Validade']) : "";
		$lote = $_POST['Lote'] != "" ? " - Lote: " . $_POST['Lote'] : "";
		$title = $row['ProduDetalhamento'];

		$sql = "SELECT ClassId, ClassNome
				FROM Classificacao
				JOIN Situacao on SituaId = ClassStatus
				WHERE SituaChave = 'ATIVO'
				ORDER BY ClassNome ASC";
		$result = $conn->query($sql);
		$rowClassificacao = $result->fetchAll(PDO::FETCH_ASSOC);

		$classificacao = '<select id="' . $_POST['idProduto'] . '" name="cmbClassificacao' . $_POST['idProduto'] . '" class="form-control form-control-select2 selectClassific2">
						<option value="#">Selecione</option>';
		foreach ($rowClassificacao as $item) {
			if ($_POST['classific'] == $item['ClassId']) {
				$classificacao .= '<option value="' . $item['ClassId'] . '" selected>' . $item['ClassNome'] . '</option>';
			} else {
				$classificacao .= '<option value="' . $item['ClassId'] . '">' . $item['ClassNome'] . '</option>';
			}
		}
		$classificacao .= '</select>';

		$ProdutoData = [
			'status' => '',
			'data' => [
				$_POST['numItens'],
				$row['ProduNome'],
				$row['UnMedSigla'],
				$_POST['quantidade'],
				$valorCusto,
				$valorTotal,
				$classificacao,
				"<span name='remove' id='" . $_POST['idProduto'] . "#$total#P' class='btn btn_remove'>X</span>"
			],
			'identify' => [
				'row' . $_POST['idProduto'],//ID
				$row['ProduId'],            //ProdId
				'P',                        //Tipo
				$lote,                      //lote
				$validade,                 	//validade
				$title						//Detalhamento
			]
		];
		echo json_encode($ProdutoData);
	} else {
		$ProdutoData = [
			'status' => 'SEMESTOQUE'
		];
		echo json_encode($ProdutoData);
	}
} else {
	echo 0;
}
