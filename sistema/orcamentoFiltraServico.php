<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

if (isset($_POST['servico']) and $_POST['servico'] != ''){
	$servicos = $_POST['servico'];
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

//echo $servico; 

if (isset($_POST['idSubCategoria']) && $_POST['idSubCategoria'] != '#' and $_POST['idSubCategoria'] != ''){

	$sql = "SELECT ServiId, ServiNome, ServiDetalhamento
			FROM Servico
			JOIN Categoria on CategId = ServiCategoria
			WHERE ServiEmpresa = ".$_SESSION['EmpreId']." and ServiSubCategoria = '". $_POST['idSubCategoria']."' and ServiId in (".$lista.")
			";
} else {
	$sql = "SELECT ServiId, ServiNome, ServiDetalhamento
			FROM Servico
			JOIN Categoria on CategId = ServiCategoria
			WHERE ServiEmpresa = ".$_SESSION['EmpreId']." and ServiCategoria = '". $_POST['idCategoria']."' and ServiId in (".$lista.")
			";
}

//echo $sql;

$result = $conn->query($sql);
$row = $result->fetchAll(PDO::FETCH_ASSOC);
//$count = count($row);
//echo json_encode($sql);

$output = '';

$cont = 0;
$fTotalGeral = 0;

foreach ($row as $item){
	
	$cont++;
	
	$id = $item['ServiId'];	
	$quantidade = isset($_POST['servicoQuant'][$id]) ? $_POST['servicoQuant'][$id] : '';
	$valorUnitario = isset($_POST['servicoValor'][$id]) ? $_POST['servicoValor'][$id] : '';
	$valorTotal = (isset($_POST['servicoQuant'][$id]) && isset($_POST['servicoValor'][$id])) ? mostraValor((float)$quantidade * (float)$valorUnitario) : '';
	
	$fTotalGeral += (isset($_POST['servicoQuant'][$id]) and isset($_POST['servicoValor'][$id])) ? (float)$quantidade * (float)$valorUnitario : 0;	
	
	$output .= ' <div class="row" style="margin-top: 8px;">
					<div class="col-lg-12">
						<div class="row">
							<div class="col-lg-1">
								<input type="text" id="inputItem'.$cont.'" name="inputItem'.$cont.'" class="form-control-border-off" value="'.$cont.'" readOnly>
								<input type="hidden" id="inputIdServico'.$cont.'" name="inputIdServico'.$cont.'" value="'.$item['ServiId'].'" class="idServico">
							</div>
							<div class="col-lg-8">
								<input type="text" id="inputServico'.$cont.'" name="inputServico'.$cont.'" class="form-control-border-off" data-popup="tooltip" title="'.$item['ServiDetalhamento'].'" value="'.$item['ServiNome'].'" readOnly>
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
						</div>
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
