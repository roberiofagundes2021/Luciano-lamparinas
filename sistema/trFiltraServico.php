<?php

include_once("sessao.php");

include('global_assets/php/conexao.php');

if (isset($_POST['servicos']) and $_POST['servicos'] != '') {
	$servicos = $_POST['servicos'];
	$numServicos = count($servicos);

	$lista = "";

	for ($i = 0; $i < $numServicos; $i++) {
		$lista .= $servicos[$i] . ",";
	}

	//retira a última vírgula
	$lista = substr($lista, 0, -1);
} else {
	$lista = 0;
}

$iTR = $_POST['idTr'];

$sql = "SELECT TRXSrServico
		FROM TermoReferenciaXServico
		JOIN ServicoOrcamento on SrOrcId = TRXSrServico
		WHERE TRXSrUnidade = " . $_SESSION['UnidadeId'] . " and TRXSrTermoReferencia = " . $iTR . " and TRXSrTabela = 'ServicoOrcamento'";
$result = $conn->query($sql);
$rowServicosOrcamento = $result->fetchAll(PDO::FETCH_ASSOC);


$sql = "SELECT TRXSrServico
		FROM TermoReferenciaXServico
		JOIN Servico on ServiId = TRXSrServico
		WHERE TRXSrUnidade = " . $_SESSION['UnidadeId'] . " and TRXSrTermoReferencia = " . $iTR . " and TRXSrTabela = 'Servico'";
$result = $conn->query($sql);
$rowServicos = $result->fetchAll(PDO::FETCH_ASSOC);
$countServicosTr2 = count($rowServicos);

//echo $servico;

if (count($rowServicosOrcamento) >= 1) {

	$sql = "SELECT SrOrcId, SrOrcNome, SrOrcDetalhamento
			FROM ServicoOrcamento
			WHERE SrOrcEmpresa = " . $_SESSION['EmpreId'] . " and SrOrcId in (" . $lista . ")
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

		$id = $item['SrOrcId'];

		$quantidade = isset($_POST['servicoQuant'][$id]) ? $_POST['servicoQuant'][$id] : '';

		$output .= ' <div class="row" style="margin-top: 8px;">
					<div class="col-lg-10">
						<div class="row">
							<div class="col-lg-1">
								<input type="text" id="inputItem' . $cont . '" name="inputItem' . $cont . '" class="form-control-border-off" value="' . $cont . '" readOnly>
								<input type="hidden" id="inputIdServico' . $cont . '" name="inputIdServico' . $cont . '" value="' . $item['SrOrcId'] . '" class="idServico">
							</div>
							<div class="col-lg-11">
								<input type="text" id="inputServico' . $cont . '" name="inputServico' . $cont . '" class="form-control-border-off" data-popup="tooltip" title="' . $item['SrOrcDetalhamento'] . '" value="' . $item['SrOrcNome'] . '" readOnly>
								<input type="hidden" id="inputDetalhamento' . $cont . '" name="inputDetalhamento' . $cont . '" value="' . $item['SrOrcDetalhamento'] . '">
							</div>
						</div>
					</div>								
					<div class="col-lg-2">
						<input type="text" id="inputQuantidade' . $cont . '" name="inputQuantidade' . $cont . '" class="form-control-border Quantidade pula" onkeypress="pula(event)" value="' . $quantidade . '">
					</div>	
				</div>';

		// if ($item['TRXSrTabela'] != null) {
		// 	$output .= '<input type="hidden" id="inputTabelaServico' . $cont . '" name="inputTabelaServico' . $cont . '" value="' . $item['TRXSrTabela'] . '">';
		// } else {
			$output .= '<input type="hidden" id="inputTabelaServico' . $cont . '" name="inputTabelaServico' . $cont . '" value="' . 'ServicoOrcamento ' . '">';
		// }
	}

	$output .= '<input type="hidden" id="totalRegistros" name="totalRegistros" value="' . $cont . '" >';

	echo $output;
} else {
	$sql = "SELECT ServiId, ServiNome, ServiDetalhamento
			FROM Servico
			WHERE ServiEmpresa = " . $_SESSION['EmpreId'] . " and ServiId in (" . $lista . ")
			";

	//echo $sql;die;

	$result = $conn->query($sql);
	$row = $result->fetchAll(PDO::FETCH_ASSOC);
	//$count = count($row);
	//echo json_encode($sql);

	$output = '';

	$cont = 0;

	foreach ($row as $item) {

		$cont++;

		$id = $item['ServiId'];

		$quantidade = isset($_POST['servicoQuant'][$id]) ? $_POST['servicoQuant'][$id] : '';

		$output .= ' <div class="row" style="margin-top: 8px;">
					<div class="col-lg-10">
						<div class="row">
							<div class="col-lg-1">
								<input type="text" id="inputItem' . $cont . '" name="inputItem' . $cont . '" class="form-control-border-off" value="' . $cont . '" readOnly>
								<input type="hidden" id="inputIdServico' . $cont . '" name="inputIdServico' . $cont . '" value="' . $item['ServiId'] . '" class="idServico">
							</div>
							<div class="col-lg-11">
								<input type="text" id="inputServico' . $cont . '" name="inputServico' . $cont . '" class="form-control-border-off" data-popup="tooltip" title="' . $item['ServiDetalhamento'] . '" value="' . $item['ServiNome'] . '" readOnly>
								<input type="hidden" id="inputDetalhamento' . $cont . '" name="inputDetalhamento' . $cont . '" value="' . $item['ServiDetalhamento'] . '">
							</div>
						</div>
					</div>								
					<div class="col-lg-2">
						<input type="text" id="inputQuantidade' . $cont . '" name="inputQuantidade' . $cont . '" class="form-control-border Quantidade pula" onkeypress="pula(event)" value="' . $quantidade . '">
					</div>	
				</div>';

		// if ($item['TRXSrTabela'] != null) {
		// 	$output .= '<input type="hidden" id="inputTabelaServico' . $cont . '" name="inputTabelaServico' . $cont . '" value="' . $item['TRXSrTabela'] . '">';
		// } else {
			$output .= '<input type="hidden" id="inputTabelaServico' . $cont . '" name="inputTabelaServico' . $cont . '" value="' . 'Servico ' . '">';
		// }
	}

	$output .= '<input type="hidden" id="totalRegistros" name="totalRegistros" value="' . $cont . '" >';

	echo $output;
}
