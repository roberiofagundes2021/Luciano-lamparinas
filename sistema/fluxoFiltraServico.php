<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

$iUnidade = $_SESSION['UnidadeId'];
$iEmpresa = $_SESSION['EmpreId'];
$iFluxoOperacional = $_POST['iFluxoOperacional'];
$Origem = $_POST['Origem'];

$sql = "SELECT FlOpeId, FlOpeTermoReferencia, SituaChave, TrRefTabelaServico,
		dbo.fnSubCategoriasFluxo(FlOpeUnidade, FlOpeId) as SubCategorias,
		dbo.fnFluxoFechado(FlOpeId, FlOpeUnidade) as FluxoFechado
FROM FluxoOperacional
JOIN Fornecedor on ForneId = FlOpeFornecedor
JOIN Categoria on CategId = FlOpeCategoria
LEFT JOIN FluxoOperacionalXSubCategoria on FOXSCFluxo = FlOpeId
JOIN Situacao on SituaId = FlOpeStatus
LEFT JOIN TermoReferencia on TrRefId = FlOpeTermoReferencia
WHERE FlOpeUnidade = " . $_SESSION['UnidadeId'] . " and FlOpeId = " . $iFluxoOperacional;
$result = $conn->query($sql);
$row = $result->fetch(PDO::FETCH_ASSOC);

if (isset($_POST['servicos']) and $_POST['servicos'] != ''){
	$servicos = $_POST['servicos'];
	$numServicos = count($servicos);
	
	$lista = "";
	
	for ($i=0; $i < $numServicos; $i++){
		$lista .= $servicos[$i] . ",";
	}
	
	//retira a última vírgula
	$lista = substr($lista, 0, -1);
} else{
	$lista = 0;
}

$sql = "SELECT ServiId, ServiNome, FOXSrDetalhamento as Detalhamento, FOXSrQuantidade, FOXSrValorUnitario
FROM Servico
JOIN FluxoOperacionalXServico on FOXSrServico = ServiId
LEFT JOIN SubCategoria on SbCatId = ServiSubCategoria
WHERE ServiEmpresa =  $iEmpresa and FOXSrFluxoOperacional = $iFluxoOperacional";

if (isset($_POST['idSubCategoria']) && $_POST['idSubCategoria'] != '#' and $_POST['idSubCategoria'] != ''){
	$sql .= " and ServiSubCategoria = '". $_POST['idSubCategoria']."' and ServiId in (".$lista.")";
} else {
	$sql .= " and ServiCategoria = '". $_POST['idCategoria']."' and ServiId in (".$lista.")";
}
$sql .= ' ORDER BY SbCatNome, ServiNome ASC';
$result = $conn->query($sql);
$rowResult = $result->fetchAll(PDO::FETCH_ASSOC);
$count = count($rowResult);

if ($Origem == 'fluxo.php'){
	if (!$count) {
		$sql = "SELECT ServiId, ServiNome, FOXSrDetalhamento as Detalhamento
				FROM Servico
				JOIN Situacao on SituaId = ServiStatus
				LEFT JOIN FluxoOperacionalXServico on FOXSrServico = ServiId and FOXSrFluxoOperacional = $iFluxoOperacional
				LEFT JOIN SubCategoria on SbCatId = ServiSubCategoria
				WHERE ServiEmpresa = $iEmpresa";

		if (isset($_POST['idSubCategoria']) && $_POST['idSubCategoria'] != '#' and $_POST['idSubCategoria'] != ''){
			$sql .= " and ServiSubCategoria = '". $_POST['idSubCategoria']."' and ServiId in (".$lista.")";
		} else {
			$sql .= " and ServiCategoria = '". $_POST['idCategoria']."' and ServiId in (".$lista.")";
		}
		$result = $conn->query($sql);
		$rowResult = $result->fetchAll(PDO::FETCH_ASSOC);
	}
}

$output = '';

$cont = 0;
$fTotalGeral = 0;

foreach ($rowResult as $item){
	
	$cont++;

	$iQuantidade = isset($item['FOXSrQuantidade']) ? $item['FOXSrQuantidade'] : '';
	$fValorUnitario = isset($item['FOXSrValorUnitario']) ? mostraValor($item['FOXSrValorUnitario']) : '';
	$fValorTotal = (isset($item['FOXSrQuantidade']) and isset($item['FOXSrValorUnitario'])) ? mostraValor($item['FOXSrQuantidade'] * $item['FOXSrValorUnitario']) : '';

	$fTotalGeral += (isset($item['FOXSrQuantidade']) and isset($item['FOXSrValorUnitario'])) ? $item['FOXSrQuantidade'] * $item['FOXSrValorUnitario'] : 0;

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

	// vai buscar na tabela ServicoXFabricante os dados caso esse fluxo ja tenha sido liberado

	$sqlSrXFa = "SELECT SrXFaId, SrXFaMarca, SrXFaModelo, SrXFaFabricante
			FROM ServicoXFabricante
			WHERE SrXFaServico = $item[ServiId] and SrXFaFluxoOperacional = $iFluxoOperacional and  SrXFaUnidade = $iUnidade";
	$resultSrXFa = $conn->query($sqlSrXFa);
	$resultSrXFa = $resultSrXFa->fetch(PDO::FETCH_ASSOC);

	foreach($rowMarca as $marca){
		$seleciona = "";
		if(isset($resultSrXFa['SrXFaMarca'])){
			$seleciona = ($resultSrXFa['SrXFaMarca'] == $marca['MarcaId']) ? "selected " : "";
		}
		$HTML_MARCA .= '<option value="'.$marca['MarcaId'].'" '.$seleciona.'>'.$marca['MarcaNome'].'</option>';
	}

	foreach($rowModelo as $modelo){
		$seleciona = "";
		if(isset($resultSrXFa['SrXFaModelo'])){
			$seleciona = ($resultSrXFa['SrXFaModelo'] == $modelo['ModelId']) ? "selected " : "";
		}
		$HTML_MODELO .= '<option value="'.$modelo['ModelId'].'" '.$seleciona.'>'.$modelo['ModelNome'].'</option>';
	}

	foreach($rowFabricante as $fabricante){
		$seleciona = "";
		if(isset($resultSrXFa['SrXFaFabricante'])){
			$seleciona = ($resultSrXFa['SrXFaFabricante'] == $fabricante['FabriId']) ? "selected " : "";
		}
		$HTML_FABRICANTE .= '<option value="'.$fabricante['FabriId'].'" '.$seleciona.'>'.$fabricante['FabriNome'].'</option>';
	}
	
	$output .= '<div class="row" style="margin-top: 8px;">
				<div class="col-lg-9">
					<div class="row">
						<div class="col-lg-1">
							<input type="text" id="inputItem' . $cont . '" name="inputItem' . $cont . '" class="form-control-border-off" value="' . $cont . '" readOnly>
							<input type="hidden" id="inputIdServico' . $cont . '" name="inputIdServico' . $cont . '" value="' . $item['ServiId'] . '" class="idServico">
						</div>
						<div class="col-lg-5">
							<input type="text" id="inputServico' . $cont . '" name="inputServico' . $cont . '" class="form-control-border-off" data-popup="tooltip" title="'. substr($item['Detalhamento'],0,380).'..." value="' . $item['ServiNome'] . '" readOnly>
							<input type="hidden" id="inputDetalhamento' . $cont . '" name="inputDetalhamento' . $cont . '" value="' . $item['Detalhamento'] . '">
						</div>
						<div class="col-lg-2">
							<select required id="inputMarca'.$cont.'" name="inputMarca'.$cont.'"'.($row['SituaChave'] == 'LIBERADO'?' disabled ':'').'class="form-control select-search">
								<option value="">Selecione</option>
								'.$HTML_MARCA.'
							</select>
						</div>
						<div class="col-lg-2">
							<select required id="inputModelo'.$cont.'" name="inputModelo'.$cont.'"'.($row['SituaChave'] == 'LIBERADO'?' disabled ':'').'class="form-control select-search">
								<option value="">Selecione</option>
								'.$HTML_MODELO.'
							</select>
						</div>
						<div class="col-lg-2">
							<select required id="inputFabricante'.$cont.'" name="inputFabricante'.$cont.'"'.($row['SituaChave'] == 'LIBERADO'?' disabled ':'').'class="form-control select-search">
								<option value="">Selecione</option>
								'.$HTML_FABRICANTE.'
							</select>
						</div>
					</div>
				</div>';

	$output .= '<div class="col-lg-1">
					<input type="text" id="inputQuantidade' . $cont . '" name="inputQuantidade' . $cont . '" class="form-control-border Quantidade text-right pula" onChange="calculaValorTotal()" value="' . $iQuantidade . '">
				</div>	
				<div class="col-lg-1">
					<input type="text" id="inputValorUnitario' . $cont . '" name="inputValorUnitario' . $cont . '" class="form-control-border ValorUnitario text-right pula" onChange="calculaValorTotal()" onKeyUp="moeda(this)" maxLength="12" value="' . $fValorUnitario . '">
				</div>	
				<div class="col-lg-1">
					<input type="text" id="inputValorTotal' . $cont . '" name="inputValorTotal' . $cont . '" class="form-control-border-off text-right" value="' . $fValorTotal . '" readOnly>
				</div>											
			</div>';
				
}

$output .= ' <div class="row" style="margin-top: 8px;">
				<div class="col-lg-9">
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
				<div class="col-lg-1" style="padding-top: 5px; text-align: right;">
					<h3><b>Total:</b></h3>
				</div>	
				<div class="col-lg-1">
					<input type="text" id="inputTotalGeral" name="inputTotalGeral" class="form-control-border-off" value="'.mostraValor($fTotalGeral).'" readOnly>
				</div>											
			</div>';


$output .= '<input type="hidden" id="totalRegistros" name="totalRegistros" value="'.$cont.'" >';

echo $output;

?>
