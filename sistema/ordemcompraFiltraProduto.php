<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');
$iOrdemCompra = isset($_POST['iOrdemCompra'])?$_POST['iOrdemCompra']:'';
$iFluxoOp = isset($_POST['iFluxoOp'])?$_POST['iFluxoOp']:'';

$sqlProd = "SELECT *
			FROM OrdemCompra
			JOIN Fornecedor on ForneId = OrComFornecedor
			JOIN Categoria on CategId = OrComCategoria
			LEFT JOIN SubCategoria on SbCatId = OrComSubCategoria
			WHERE OrComUnidade = ". $_SESSION['UnidadeId'] ." and OrComId = ".$iOrdemCompra;
$resultProd = $conn->query($sqlProd);
$rowProd = $resultProd->fetch(PDO::FETCH_ASSOC);

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

$sql = "SELECT ProduId, ProduNome, OCXPrDetalhamento as Detalhamento, UnMedSigla, OCXPrQuantidade, FOXPrValorUnitario,
				dbo.fnSaldoOrdemCompra($_SESSION[UnidadeId], '$iFluxoOp', ProduId, 'P') as SaldoOrdemCompra
				FROM Produto
				JOIN Situacao on SituaId = ProduStatus
				JOIN OrdemCompraXProduto on OCXPrProduto = ProduId and OCXPrOrdemCompra = '$iOrdemCompra'
				JOIN UnidadeMedida on UnMedId = ProduUnidadeMedida
				JOIN FluxoOperacionalXProduto on FOXPrProduto = ProduId and FOXPrFluxoOperacional = '$iFluxoOp'
				WHERE ProduEmpresa = ".$_SESSION['EmpreId']." and ProduId in (".$lista.")";
if (isset($rowProd['OrComSubCategoria']) and $rowProd['OrComSubCategoria'] != '' and $rowProd['OrComSubCategoria'] != null){
	$sql .= " and ProduSubCategoria = ".$rowProd['OrComSubCategoria'];
}

$sql = $sql." ORDER BY ProduNome";
$result = $conn->query($sql);
$row = $result->fetchAll(PDO::FETCH_ASSOC);

$count = count($row);

if(!$count>0){
	$sql = "SELECT ProduId, ProduNome, FOXPrDetalhamento as Detalhamento, UnMedSigla, FOXPrValorUnitario,
					dbo.fnSaldoOrdemCompra($_SESSION[UnidadeId], '$iFluxoOp', ProduId, 'P') as SaldoOrdemCompra
					FROM Produto
					JOIN Categoria on CategId = ProduCategoria
					JOIN UnidadeMedida on UnMedId = ProduUnidadeMedida
					JOIN FluxoOperacionalXProduto on FOXPrProduto = ProduId and FOXPrFluxoOperacional = '$iFluxoOp'
					WHERE ProduEmpresa = ".$_SESSION['EmpreId']." and ProduId in (".$lista.")";
	if (isset($rowProd['OrComSubCategoria']) and $rowProd['OrComSubCategoria'] != '' and $rowProd['OrComSubCategoria'] != null){
		$sql .= " and ProduSubCategoria = ".$rowProd['OrComSubCategoria'];
	}
	$sql = $sql." ORDER BY ProduNome";
	$result = $conn->query($sql);
	$row = $result->fetchAll(PDO::FETCH_ASSOC);
}

$output = '';

$cont = 0;
$fTotalGeral = 0;

foreach ($row as $item){
	
	$cont++;
	
	$id = $item['ProduId'];
	$saldo = isset($item['SaldoOrdemCompra']) ? $item['SaldoOrdemCompra'] : 0;
	$quantidade = isset($item['OCXPrQuantidade']) ? $item['OCXPrQuantidade'] : 0;
	$valorUnitario = isset($item['FOXPrValorUnitario']) ? mostraValor($item['FOXPrValorUnitario']) : 0;

	$ValorTotal = mostraValor(intval($quantidade)*intval($valorUnitario));
	
	$fTotalGeral += intval($ValorTotal);
	
	$output .= ' <div class="row" style="margin-top: 8px;">
									<div class="col-lg-5">
										<div class="row">
											<div class="col-lg-2" style="max-width:60px">
												<input type="text" id="inputItem'.$cont.'" name="inputItem'.$cont.'" class="form-control-border-off" value="'.$cont.'" readOnly>
												<input type="hidden" id="inputIdProduto'.$cont.'" name="inputIdProduto'.$cont.'" value="'.$item['ProduId'].'" class="idProduto">
											</div>
											<div class="col-lg-10" style="width:100%">
												<input type="text" id="inputProduto'.$cont.'" name="inputProduto'.$cont.'" class="form-control-border-off" data-popup="tooltip" title="'.$item['Detalhamento'].'" value="'.$item['ProduNome'].'" readOnly>
											</div>
										</div>
									</div>								
									<div class="col-lg-1">
										<input type="text" id="inputUnidade'.$cont.'" name="inputUnidade'.$cont.'" class="form-control-border-off" value="'.$item['UnMedSigla'].'" readOnly>
									</div>
									<div class="col-lg-1">
										<input type="text" id="inputSaldo'.$cont.'" readOnly name="Saldo'.$cont.'" class="form-control-border-off text-right" value="'.$saldo.'">
									</div>
									<div class="col-lg-1">
										<input type="text" id="inputQuantidade'.$cont.'"'.($saldo > 0?'':'readOnly').' name="inputQuantidade'.$cont.'" onkeypress="validaQuantInputModal('.$saldo.',this)" class="form-control-border Quantidade text-right pula" onChange="calculaValorTotal('.$cont.')" onkeypress="return onlynumber();" value="'.$quantidade.'">
									</div>		
									<div class="col-lg-1">
										<input readOnly type="text" id="inputValorUnitario'.$cont.'" name="inputValorUnitario'.$cont.'" class="form-control-border-off ValorUnitario text-right" onChange="calculaValorTotal('.$cont.')" onKeyUp="moeda(this)" maxLength="12" value="'.$valorUnitario.'">
									</div>	
									<div class="col-lg-2">
										<input type="text" id="inputValorTotal'.$cont.'" name="inputValorTotal'.$cont.'" class="form-control-border-off text-right" value="'.$ValorTotal.'" readOnly>
									</div>
									<div class="col-sm-1 btn" style="text-align:center;" onClick="reset('.$cont.')">
										<i class="icon-reset" title="Resetar"></i>
									</div>											
								</div>';	
}

$output .= ' <div class="row" style="margin-top: 8px;">
				<div class="col-lg-6">
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
					<h3><b>Total:</b></h3>
				</div>	
				<div class="col-lg-2">
					<input type="text" id="inputTotalGeral" name="inputTotalGeral" class="form-control-border-off text-right" value="'.mostraValor($fTotalGeral).'" readOnly>
				</div>
			</div>';

$output .= '<input type="hidden" id="totalRegistros" name="totalRegistros" value="'.$cont.'" >';

echo $output;

?>
