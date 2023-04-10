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

//echo $produto; 

if (isset($_POST['idSubCategoria']) && $_POST['idSubCategoria'] != '#' and $_POST['idSubCategoria'] != ''){

	$sql = "SELECT ProduId, ProduNome, ProduDetalhamento, UnMedSigla
			FROM Produto
			JOIN Categoria on CategId = ProduCategoria
			JOIN UnidadeMedida on UnMedId = ProduUnidadeMedida
			WHERE ProduEmpresa = ".$_SESSION['EmpreId']." and ProduSubCategoria = '". $_POST['idSubCategoria']."' and ProduId in (".$lista.")
			";
} else {
	$sql = "SELECT ProduId, ProduNome, ProduDetalhamento, UnMedSigla
			FROM Produto
			JOIN Categoria on CategId = ProduCategoria
			JOIN UnidadeMedida on UnMedId = ProduUnidadeMedida
			WHERE ProduEmpresa = ".$_SESSION['EmpreId']." and ProduCategoria = '". $_POST['idCategoria']."' and ProduId in (".$lista.")
			";
}

$result = $conn->query($sql);
$row = $result->fetchAll(PDO::FETCH_ASSOC);
//$count = count($row);
//echo json_encode($sql);

$output = '';

$cont = 0;
$fTotalGeral = 0;

foreach ($row as $item){
	
	$cont++;
	
	$id = $item['ProduId'];
	
	$quantidade = isset($_POST['produtoQuant'][$id]) ? $_POST['produtoQuant'][$id] : '';
	$valorUnitario = isset($_POST['produtoValor'][$id]) ? $_POST['produtoValor'][$id] : '';
	$valorTotal = (isset($_POST['produtoQuant'][$id]) && isset($_POST['produtoValor'][$id])) ? mostraValor((float)$quantidade * (float)$valorUnitario) : '';
	
	$fTotalGeral += (isset($_POST['produtoQuant'][$id]) and isset($_POST['produtoValor'][$id])) ? (float)$quantidade * (float)$valorUnitario : 0;	
	
	$output .= ' <div class="row" style="margin-top: 8px;">
					<div class="col-lg-8">
						<div class="row">
							<div class="col-lg-1">
								<input type="text" id="inputItem'.$cont.'" name="inputItem'.$cont.'" class="form-control-border-off" value="'.$cont.'" readOnly>
								<input type="hidden" id="inputIdProduto'.$cont.'" name="inputIdProduto'.$cont.'" value="'.$item['ProduId'].'" class="idProduto">
							</div>
							<div class="col-lg-11">
								<input type="text" id="inputProduto'.$cont.'" name="inputProduto'.$cont.'" class="form-control-border-off" data-popup="tooltip" title="'.$item['ProduDetalhamento'].'" value="'.$item['ProduNome'].'" readOnly>
							</div>
						</div>
					</div>								
					<div class="col-lg-1">
						<input type="text" id="inputUnidade'.$cont.'" name="inputUnidade'.$cont.'" class="form-control-border-off" value="'.$item['UnMedSigla'].'" readOnly>
					</div>
					<div class="col-lg-1">
						<input type="text" id="inputQuantidade'.$cont.'" name="inputQuantidade'.$cont.'" class="form-control-border Quantidade" onChange="calculaValorTotal('.$cont.')" onkeypress="return onlynumber();" value="'.$quantidade.'">
					</div>	
					<div class="col-lg-1">
						<input type="text" id="inputValorUnitario'.$cont.'" name="inputValorUnitario'.$cont.'" class="form-control-border ValorUnitario" onChange="calculaValorTotal('.$cont.')" onKeyUp="moeda(this)" maxLength="12" value="'.$valorUnitario.'">
					</div>	
					<div class="col-lg-1">
						<input type="text" id="inputValorTotal'.$cont.'" name="inputValorTotal'.$cont.'" class="form-control-border-off" value="'.$valorTotal.'" readOnly>
					</div>
				</div>';	
}

$output .= ' <div class="row" style="margin-top: 8px;">
				<div class="col-lg-8">
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
				<div class="col-lg-1">
					<input type="text" id="inputTotalGeral" name="inputTotalGeral" class="form-control-border-off" value="'.mostraValor($fTotalGeral).'" readOnly>
				</div>											
			</div>';

$output .= '<input type="hidden" id="totalRegistros" name="totalRegistros" value="'.$cont.'" >';

echo $output;

?>
