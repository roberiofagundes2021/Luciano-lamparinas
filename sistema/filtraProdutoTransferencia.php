<?php

include_once("sessao.php");

include('global_assets/php/conexao.php');


if (
	$_POST['tipoDeFiltro'] == 'produto'
	&& $_POST['idSubCategoria'] !== ''
	&& $_POST['idSubCategoria'] !== null
) {
	$sql = "SELECT DISTINCT CONVERT(varchar(10), produid) + '#'	+ CONVERT(varchar(10),ProduValorCusto) as ProduValue, 
				            ProduNome, ProduValorCusto, ProduCustoFinal, MvXPrValidade
			FROM Produto
			JOIN MovimentacaoXProduto ON MvXPrProduto = ProduId
			JOIN Movimentacao ON MovimId = MvXPrMovimentacao
			JOIN Categoria ON CategId = ProduCategoria
			JOIN SubCategoria ON SbCatId = ProduSubCategoria
			JOIN Situacao ON SituaId = MovimSituacao
			WHERE SituaChave = 'LIBERADOCONTABILIDADE' AND MovimTipo = 'E' AND ProduCategoria = " . $_POST['idCategoria'] . " AND  ProduSubCategoria = " . $_POST['idSubCategoria'] . "
			ORDER BY ProduNome ASC";
} else {
	$sql = "SELECT DISTINCT CONVERT(varchar(10), produid) + '#' + CONVERT(varchar(10),ProduValorCusto) as ProduValue, 
	                        ProduNome, ProduValorCusto, ProduCustoFinal, MvXPrValidade
			FROM Produto
			JOIN MovimentacaoXProduto ON MvXPrProduto = ProduId
			JOIN Movimentacao ON MovimId = MvXPrMovimentacao
			JOIN Categoria ON CategId = ProduCategoria
			JOIN Situacao ON SituaId = MovimSituacao
			WHERE SituaChave = 'LIBERADOCONTABILIDADE' AND MovimTipo = 'E' AND ProduCategoria = " . $_POST['idCategoria'] . "
			ORDER BY ProduNome ASC";
}


$result = $conn->query($sql);
$row = $result->fetchAll(PDO::FETCH_ASSOC);
$cont = count($row);


if ($cont) {
	foreach ($row as $value) {
		if ($value['MvXPrValidade'] == null ) {
			print('<option value="' . $value['ProduValue'] . '#null' .'">' . $value['ProduNome'] . '</option>');
		} else {
			print('<option value="' . $value['ProduValue'] . '#' . $value['MvXPrValidade'] .'">' . $value['ProduNome'] . '</option>');
		}
	}
} else {
	echo 'sem dados';
}
