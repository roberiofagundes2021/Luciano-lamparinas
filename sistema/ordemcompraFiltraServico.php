<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

$iOrdemCompra = isset($_POST['iOrdemCompra'])?$_POST['iOrdemCompra']:'';
$iFluxo = isset($_POST['iFluxoOp'])?$_POST['iFluxoOp']:'';

$sqlServ = "SELECT *
			FROM OrdemCompra
			JOIN Fornecedor on ForneId = OrComFornecedor
			JOIN Categoria on CategId = OrComCategoria
			LEFT JOIN SubCategoria on SbCatId = OrComSubCategoria
			WHERE OrComUnidade = ". $_SESSION['UnidadeId'] ." and OrComId = ".$iOrdemCompra;
$resultServ = $conn->query($sqlServ);
$rowServ = $resultServ->fetch(PDO::FETCH_ASSOC);

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

$sql = "SELECT ServiId, ServiNome, ServiDetalhamento, FOXSrValorUnitario, OCXSrQuantidade,
				dbo.fnSaldoOrdemCompra($_SESSION[UnidadeId], '$iFluxo', ServiId, 'S') as SaldoOrdemCompra
				FROM Servico
				JOIN Categoria on CategId = ServiCategoria
				JOIN OrdemCompraXServico on OCXSrServico = ServiId and OCXSrOrdemCompra = '$iOrdemCompra'
				JOIN FluxoOperacionalXServico on FOXSrServico = ServiId and FOXSrFluxoOperacional = '$iFluxo'
				WHERE ServiEmpresa = $_SESSION[EmpreId] and ServiId in (".$lista.")";
if (isset($rowServ['OrComSubCategoria']) and $rowServ['OrComSubCategoria'] != '' and $rowServ['OrComSubCategoria'] != null){
	$sql .= " and ServiSubCategoria = ".$rowServ['OrComSubCategoria'];
}
$result = $conn->query($sql);
$row = $result->fetchAll(PDO::FETCH_ASSOC);
$count = count($row);

if(!$count>0){
	$sql = "SELECT ServiId, ServiNome, ServiDetalhamento, FOXSrValorUnitario,
					dbo.fnSaldoOrdemCompra($_SESSION[UnidadeId], '$iFluxo', ServiId, 'S') as SaldoOrdemCompra
					FROM Servico
					JOIN Categoria on CategId = ServiCategoria
					JOIN FluxoOperacionalXServico on FOXSrServico = ServiId and FOXSrFluxoOperacional = '$iFluxo'
					WHERE ServiEmpresa = $_SESSION[EmpreId] and ServiId in (".$lista.")";
	if (isset($rowServ['OrComSubCategoria']) and $rowServ['OrComSubCategoria'] != '' and $rowServ['OrComSubCategoria'] != null){
		$sql .= " and ServiSubCategoria = ".$rowServ['OrComSubCategoria'];
	}
	$result = $conn->query($sql);
	$row = $result->fetchAll(PDO::FETCH_ASSOC);
	$count = count($row);
}

$output = '';

$cont = 0;
$fTotalGeral = 0;

foreach ($row as $item){
	
	$cont++;
	
	$id = $item['ServiId'];

	$saldo = isset($item['SaldoOrdemCompra']) ? $item['SaldoOrdemCompra'] : 0;
	$quantidade = isset($item['OCXSrQuantidade']) ? $item['OCXSrQuantidade'] : 0;
	$valorUnitario = isset($item['FOXSrValorUnitario']) ? mostraValor($item['FOXSrValorUnitario']) : 0;											
	$valorTotal = mostraValor(intval($quantidade)*gravaValor($valorUnitario));
	
	$fTotalGeral += gravaValor($valorTotal);
	
	$output .= ' <div class="row" style="margin-top: 8px;">
									<div class="col-lg-6">
										<div class="row">
											<div class="col-lg-2" style="max-width:60px">
												<input type="text" id="inputItem'.$cont.'" name="inputItem'.$cont.'" class="form-control-border-off" value="'.$cont.'" readOnly>
												<input type="hidden" id="inputIdServico'.$cont.'" name="inputIdServico'.$cont.'" value="'.$item['ServiId'].'" class="idServico">
											</div>
											<div class="col-lg-10" style="width:100%">
												<input type="text" id="inputServico'.$cont.'" name="inputServico'.$cont.'" class="form-control-border-off" data-popup="tooltip" title="'.$item['ServiDetalhamento'].'" value="'.$item['ServiNome'].'" readOnly>
											</div>
										</div>
									</div>
									<div class="col-lg-1">
										<input type="text" id="inputSaldo'.$cont.'" readOnly name="Saldo'.$cont.'" class="form-control-border-off text-right" value="'.$saldo.'">
									</div>
									<div class="col-lg-1">
										<input type="text" id="inputQuantidade'.$cont.'" '.($saldo > 0?'':'readOnly ').
										'name="inputQuantidade'.$cont.'" class="form-control-border Quantidade text-right pula" onChange="calculaValorTotal('.$cont.')" onkeypress="return onlynumber(), validaQuantInputModal('.$saldo.',this)" value="'.$quantidade.'">
									</div>	
									<div class="col-lg-1">
										<input readOnly type="text" id="inputValorUnitario'.$cont.'" name="inputValorUnitario'.$cont.'" class="form-control-border-off ValorUnitario text-right" onChange="calculaValorTotal('.$cont.')" onKeyUp="moeda(this)" maxLength="12" value="'.$valorUnitario.'">
									</div>	
									<div class="col-lg-2">
										<input type="text" id="inputValorTotal'.$cont.'" name="inputValorTotal'.$cont.'" class="form-control-border-off text-right" value="'.$valorTotal.'" readOnly>
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
