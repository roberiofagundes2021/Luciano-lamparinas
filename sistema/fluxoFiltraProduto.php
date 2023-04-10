<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

if (isset($_POST['produtos']) and $_POST['produtos'] != ''){
	$produtos = $_POST['produtos'];
	$numProdutos = count($produtos);
	
	$lista = "";
	
	for ($i=0; $i < $numProdutos; $i++){
		$lista .= $produtos[$i] . ",";
	}
	
	//retira a última vírgula
	$lista = substr($lista, 0, -1);
} else{
	$lista = 0;
}
$iUnidade = $_SESSION['UnidadeId'];
$iEmpresa = $_SESSION['EmpreId'];
$iFluxoOperacional = $_POST['iFluxoOperacional'];
$Origem = $_POST['Origem'];


$sql = "SELECT FlOpeStatus, SituaChave
		FROM FluxoOperacional
		JOIN Situacao on SituaId = FlOpeStatus
		WHERE FlOpeUnidade = $iUnidade and FlOpeId = $iFluxoOperacional";
$result = $conn->query($sql);
$row = $result->fetch(PDO::FETCH_ASSOC);

$sql = "SELECT ProduId, ProduNome, UnMedSigla, FOXPrDetalhamento as Detalhamento,
	FOXPrQuantidade, FOXPrValorUnitario
	FROM Produto
	JOIN Categoria on CategId = ProduCategoria
	JOIN FluxoOperacionalXProduto on FOXPrProduto = ProduId
	LEFT JOIN UnidadeMedida on UnMedId = ProduUnidadeMedida
	WHERE ProduEmpresa = $iEmpresa and FOXPrFluxoOperacional = $iFluxoOperacional";
if (isset($_POST['idSubCategoria']) && $_POST['idSubCategoria'] != '#' and $_POST['idSubCategoria'] != ''){
	$sql .= " and ProduSubCategoria = '". $_POST['idSubCategoria']."' and ProduId in (".$lista.")";
} else {
	$sql .= " and ProduCategoria = '". $_POST['idCategoria']."' and ProduId in (".$lista.")";
}

$result = $conn->query($sql);
$rowResult = $result->fetchAll(PDO::FETCH_ASSOC);
$count = count($rowResult);
//echo json_encode($sql);

if($Origem == 'fluxo.php'){
	if (!$count){
		$sql = "SELECT ProduId, ProduNome, UnMedSigla, FOXPrDetalhamento as Detalhamento
		FROM Produto
		JOIN Categoria on CategId = ProduCategoria
		LEFT JOIN FluxoOperacionalXProduto on FOXPrProduto = ProduId and FOXPrFluxoOperacional = $iFluxoOperacional
		LEFT JOIN UnidadeMedida on UnMedId = ProduUnidadeMedida
		WHERE ProduEmpresa = $iEmpresa";
		if (isset($_POST['idSubCategoria']) && $_POST['idSubCategoria'] != '#' and $_POST['idSubCategoria'] != ''){
			$sql .= " and ProduSubCategoria = '". $_POST['idSubCategoria']."' and ProduId in (".$lista.")";
		} else {
			$sql .= " and ProduCategoria = '". $_POST['idCategoria']."' and ProduId in (".$lista.")";
		}
		$result = $conn->query($sql);
		$rowResult = $result->fetchAll(PDO::FETCH_ASSOC);
	}
}

$output = '';

$cont = 0;
$fTotalGeral = 0;

// pega marca modelo fabricante para adicionar nos selects
$sql = "SELECT MarcaId, MarcaNome
FROM Marca
JOIN Situacao on SituaId = MarcaStatus
WHERE MarcaEmpresa = $iEmpresa and SituaChave = 'ATIVO'
ORDER BY MarcaNome ASC";
$resultMarca = $conn->query($sql);
$rowMarca = $resultMarca->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT ModelId, ModelNome
FROM Modelo
JOIN Situacao on SituaId = ModelStatus
WHERE ModelEmpresa = ". $_SESSION['EmpreId'] ." and SituaChave = 'ATIVO'
ORDER BY ModelNome ASC";
$resultModelo = $conn->query($sql);
$rowModelo = $resultModelo->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT FabriId, FabriNome
FROM Fabricante
JOIN Situacao on SituaId = FabriStatus
WHERE FabriEmpresa = ". $_SESSION['EmpreId'] ." and SituaChave = 'ATIVO'
ORDER BY FabriNome ASC";
$resultFabricante = $conn->query($sql);
$rowFabricante = $resultFabricante->fetchAll(PDO::FETCH_ASSOC);

$HTML_MARCA = '';
$HTML_MODELO = '';
$HTML_FABRICANTE = '';

foreach ($rowResult as $item){

	// vai buscar na tabela ProdutoXFabricante os dados caso esse fluxo ja tenha sido liberado

	$sqlPrXFa = "SELECT PrXFaId, PrXFaMarca, PrXFaModelo, PrXFaFabricante
	FROM ProdutoXFabricante
	WHERE PrXFaProduto = $item[ProduId] and PrXFaFluxoOperacional = $iFluxoOperacional and PrXFaUnidade = $iUnidade";
	$resultPrXFa = $conn->query($sqlPrXFa);
	$resultPrXFa = $resultPrXFa->fetch(PDO::FETCH_ASSOC);

	foreach($rowMarca as $marca){
		$seleciona = "";
		if(isset($resultPrXFa['PrXFaMarca'])){
			$seleciona = ($resultPrXFa['PrXFaMarca'] == $marca['MarcaId']) ? "selected " : "";
		}
		$HTML_MARCA .= '<option value="'.$marca['MarcaId'].'" '.$seleciona.'>'.$marca['MarcaNome'].'</option>';
	}

	foreach($rowModelo as $modelo){
		$seleciona = "";
		if(isset($resultPrXFa['PrXFaModelo'])){
			$seleciona = ($resultPrXFa['PrXFaModelo'] == $modelo['ModelId']) ? "selected " : "";
		}
		$HTML_MODELO .= '<option value="'.$modelo['ModelId'].'" '.$seleciona.'>'.$modelo['ModelNome'].'</option>';
	}

	foreach($rowFabricante as $fabricante){
		$seleciona = "";
		if(isset($resultPrXFa['PrXFaFabricante'])){
			$seleciona = ($resultPrXFa['PrXFaFabricante'] == $fabricante['FabriId']) ? "selected " : "";
		}
		$HTML_FABRICANTE .= '<option value="'.$fabricante['FabriId'].'" '.$seleciona.'>'.$fabricante['FabriNome'].'</option>';
	}
	
	$cont++;
	
	$iQuantidade = isset($item['FOXPrQuantidade']) ? $item['FOXPrQuantidade'] : '';
	$fValorUnitario = isset($item['FOXPrValorUnitario']) ? mostraValor($item['FOXPrValorUnitario']) : '';											
	$fValorTotal = (isset($item['FOXPrQuantidade']) and isset($item['FOXPrValorUnitario'])) ? mostraValor($item['FOXPrQuantidade'] * $item['FOXPrValorUnitario']) : '';
	
	$fTotalGeral += (isset($item['FOXPrQuantidade']) and isset($item['FOXPrValorUnitario'])) ? $item['FOXPrQuantidade'] * $item['FOXPrValorUnitario'] : 0;
	
	$output .= '<div class="row" style="margin-top: 8px;">
					<div class="col-lg-8">
						<div class="row">
							<div class="col-lg-1">
								<input type="text" id="inputItem'.$cont.'" name="inputItem'.$cont.'" class="form-control-border-off" value="'.$cont.'" readOnly>
								<input type="hidden" id="inputIdProduto'.$cont.'" name="inputIdProduto'.$cont.'" value="'.$item['ProduId'].'" class="idProduto">
							</div>
							<div class="col-lg-2">
								<input type="text" id="inputProduto'.$cont.'" name="inputProduto'.$cont.'" class="form-control-border-off" data-popup="tooltip" title="'. substr($item['Detalhamento'],0,380).'..." value="'.$item['ProduNome'].'" readOnly>
								<input type="hidden" id="inputDetalhamento' . $cont . '" name="inputDetalhamento' . $cont . '" value="' . $item['Detalhamento'] . '">
							</div>
							<div class="col-lg-3">
								<select id="inputMarca'.$cont.'" name="inputMarca'.$cont.'"'.($row['SituaChave'] == 'LIBERADO'?' disabled ':'').'class="form-control select-search">
									<option value="">Selecione</option>
									'.$HTML_MARCA.'
									</select>
							</div>
							<div class="col-lg-3">
								<select id="inputModelo'.$cont.'" name="inputModelo'.$cont.'" '.($row['SituaChave'] == 'LIBERADO'?' disabled ':'').'class="form-control select-search">
									<option value="">Selecione</option>
									'.$HTML_MODELO.'
									</select>
							</div>
							<div class="col-lg-3">
								<select id="inputFabricante'.$cont.'" name="inputFabricante'.$cont.'" '.($row['SituaChave'] == 'LIBERADO'?' disabled ':'').'class="form-control select-search">
									<option value="">Selecione</option>
									'.$HTML_FABRICANTE.'
									</select>
							</div>
						</div>
					</div>								
					<div class="col-lg-1">
						<input type="text" id="inputUnidade'.$cont.'" name="inputUnidade'.$cont.'" class="form-control-border-off" value="'.$item['UnMedSigla'].'" readOnly>
					</div>
					<div class="col-lg-1">
						<input type="text" id="inputQuantidade'.$cont.'" name="inputQuantidade'.$cont.'" class="form-control-border Quantidade pula" onChange="calculaValorTotal()" value="'.$iQuantidade.'">
					</div>	
					<div class="col-lg-1">
						<input type="text" id="inputValorUnitario'.$cont.'" name="inputValorUnitario'.$cont.'" class="form-control-border ValorUnitario text-right pula" onChange="calculaValorTotal()" onKeyUp="moeda(this)" maxLength="12" value="'.$fValorUnitario.'">
					</div>	
					<div class="col-lg-1">
						<input type="text" id="inputValorTotal'.$cont.'" name="inputValorTotal'.$cont.'" class="form-control-border-off text-right" value="'.$fValorTotal.'" readOnly>
					</div>											
				</div>';
				
}

$output .= '<div class="row" style="margin-top: 8px;">
				<div class="col-lg-7">
					<div class="row">
						<div class="col-lg-1">
							
						</div>
						<div class="col-lg-8">
							
						</div>
						<div class="col-lg-3">
							
						</div>
					</div>
				</div>								
				<div class="col-lg-1">
					
				</div>
				<div class="col-lg-1">
					
				</div>	
				<div class="col-lg-1" style="padding-top: 5px; text-align: right;">
					<h5><b>Total:</b></h5>
				</div>	
				<div class="col-lg-2">
					<input type="text" id="inputTotalGeral" name="inputTotalGeral" class="form-control-border-off text-right" value="'.mostraValor($fTotalGeral).'" readOnly>
				</div>											
			</div>';


$output .= '<input type="hidden" id="totalRegistros" name="totalRegistros" value="'.$cont.'" >';

echo $output;

?>
