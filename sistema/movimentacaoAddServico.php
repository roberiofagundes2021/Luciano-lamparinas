<?php

include_once("sessao.php");

include('global_assets/php/conexao.php');

$sql = "SELECT ServiId, ServiNome, ServiValorCusto, ServiCustoFinal, ServiDetalhamento, 
		dbo.fnSaldoEstoque(". $_SESSION['UnidadeId'] . ", ServiId, 'S', NULL) as Estoque
		FROM Servico
		WHERE ServiEmpresa = " . $_SESSION['EmpreId'] . " and ServiId = " . $_POST['idServico'];
$result = $conn->query($sql);
$row = $result->fetch(PDO::FETCH_ASSOC);
$count = count($row);
//Verifica se já existe esse registro (se existir, retorna true )
if ($count) {
	if ($row['Estoque'] >= 1) {
		$title = $row['ServiDetalhamento'];
		if ($_POST['tipo'] == 'E') {
			$valorCusto = formataMoeda($row['ServiValorCusto']);
			$valorTotal = formataMoeda($_POST['quantidade'] * $row['ServiValorCusto']);

			$total = $_POST['quantidade'] * $row['ServiValorCusto'];
		} else {
			$valorCusto = formataMoeda($row['ServiCustoFinal']);
			$valorTotal = formataMoeda($_POST['quantidade'] * $row['ServiCustoFinal']);

			$total = $_POST['quantidade'] * $row['ServiCustoFinal'];
		}

		$teste = [
			'status' => '',
			'data' => [
				$_POST['numItens'],
				$row['ServiNome'],
				'',
				$_POST['quantidade'],
				$valorCusto,
				$valorTotal,
				'',
				"<span name='remove' id='" .$_POST['idServico'] . "#$total#S' class='btn btn_remove'>X</span>"
			],
			'identify' => [
				'row' . $_POST['idServico'], //ID
				$row['ServiId'],            //ProdId
				'S',                        //Tipo
				'',                         //lote
				'',                         //validade
				$title                      //detalhamento
			]
		];
		echo json_encode($teste);
	} else {
		$teste = [
			'status' => 'SEMESTOQUE'
		];
		echo json_encode($teste);
	}
} else {
	echo 0;
}
