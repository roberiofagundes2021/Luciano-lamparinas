<?php

include_once("sessao.php");

include('global_assets/php/conexao.php');



if (isset($_POST['origem'])) {
	$post_string = implode("", $_POST);
	$post_string = explode('#', $post_string);
}

// alerta($_POST['tipoDeFiltro']);
// alerta($post_string[0]);

if ($_POST['tipoDeFiltro'] == '#Categoria') {

	if ($post_string[2] == 'Local') {
		$sql = "SELECT DISTINCT CategId, CategNome
				FROM MovimentacaoXProduto
				JOIN Movimentacao ON MovimId = MvXPrMovimentacao
				JOIN Produto ON ProduId = MvXPrProduto
				JOIN Categoria ON CategId = ProduCategoria
				JOIN Situacao  ON SituaId = MovimSituacao
				WHERE MvXPrUnidade = " . $_SESSION['UnidadeId'] . " AND MovimDestinoLocal = " . $post_string[0] . " AND SituaChave in ('LIBERADO', 'LIBERADOCONTABILIDADE')
				ORDER BY CategNome ASC";

		$result = $conn->query($sql);
		$row = $result->fetchAll(PDO::FETCH_ASSOC);
		$cont = count($row);

		if ($cont) {
			foreach ($row as $value) {
				// print_r($value);
				print('<option value="' . $value['CategId'] . '">' . $value['CategNome'] . '</option>');
			}
		} else {
			echo 'sem dados';
		}
	} else if ($post_string[2] == 'Setor') {
		$sql = "SELECT DISTINCT CategId, CategNome
				FROM MovimentacaoXProduto
				JOIN Movimentacao ON MovimId = MvXPrMovimentacao
				JOIN Produto ON ProduId = MvXPrProduto
				JOIN Categoria ON CategId = ProduCategoria
				JOIN Situacao  ON SituaId = MovimSituacao
				WHERE MvXPrUnidade = " . $_SESSION['UnidadeId'] . " AND MovimDestinoSetor = " . $post_string[0] . " AND SituaChave = 'LIBERADO'
				ORDER BY CategNome ASC";

		$result = $conn->query($sql);
		$row = $result->fetchAll(PDO::FETCH_ASSOC);
		$cont = count($row);

		if ($cont) {
			foreach ($row as $value) {
				// print_r($value);
				print('<option value="' . $value['CategId'] . '">' . $value['CategNome'] . '</option>');
			}
		} else {
			echo 'sem dados';
		}
	}
} else if ($_POST['tipoDeFiltro'] == '#CategoriaPatrimonio') {

	$sql = "SELECT DISTINCT CategId, CategNome, SbCatId, SbCatNome, CONVERT(varchar(10), produid) + '#' 
	                        + CONVERT(varchar(10),ProduValorCusto) as ProduValue,produNome,MvXPrValidade
			FROM PRODUTO
			JOIN PATRIMONIO ON PatriProduto = ProduId
			JOIN categoria ON categid = produCategoria
			JOIN SubCategoria ON SbCatId = ProduSubCategoria
			JOIN MovimentacaoXProduto ON MvXPrPatrimonio = PatriId
			JOIN movimentacao ON MovimId = MvXPrMovimentacao
			WHERE PatriId = " . $_POST['valor'] . "";

	$result = $conn->query($sql);
	$row = $result->fetchAll(PDO::FETCH_ASSOC);
	$cont = count($row);


	if ($_POST['campo'] == 'categoria') {
		if ($cont) {
			foreach ($row as $value) {
				print('<option value="' . $value['CategId'] . '" selected>' . $value['CategNome'] . '</option>');
			}
		} else {
			echo 'sem dados';
		}
	}

	if ($_POST['campo'] == 'subcategoria') {
		if ($cont) {
			foreach ($row as $value) {
				print('<option value="' . $value['SbCatId'] . '" selected>' . $value['SbCatNome'] . '</option>');
			}
		} else {
			echo 'sem dados';
		}
	}

	if ($_POST['campo'] == 'produto') {
		if ($cont) {
			foreach ($row as $value) {
				if ($value['MvXPrValidade'] == null ){
					print('<option value="' . $value['ProduValue'] . '#null' .'" selected>' . $value['produNome'] . '</option>');
				} else {
					print('<option value="' . $value['ProduValue'] . '#' . $value['MvXPrValidade'] . '" selected>' . $value['produNome'] . '</option>');
				}
			}
		} else {
			echo 'sem dados';
		}
	}
} else if ($_POST['tipoDeFiltro'] === 'Patrimonio') {

	$sql = "SELECT PatriId, PatriNumero
			FROM Patrimonio
			JOIN MovimentacaoXProduto ON MvXPrPatrimonio = PatriId
			JOIN Movimentacao ON MovimId = MvXPrMovimentacao
			JOIN Situacao  ON SituaId = MovimSituacao
			WHERE MvXPrUnidade = " . $_SESSION['UnidadeId'] . " AND MovimDestinoSetor 	= " . $_POST['origem'] . " AND SituaChave = 'LIBERADO'";
	$result = $conn->query($sql);
	$rowPatrimonios = $result->fetchAll(PDO::FETCH_ASSOC);
	$cont = count($rowPatrimonios);

	if ($cont >= 1) {
		foreach ($rowPatrimonios as $value) {
			print('<option value="' . $value['PatriId'] . '">' . $value['PatriNumero'] . '</option>');
		}
	}
} else {
	alerta('CategoriaPatrimonioPHP');
}
