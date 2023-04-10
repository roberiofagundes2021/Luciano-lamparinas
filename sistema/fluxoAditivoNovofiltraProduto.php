<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

// cmbSubCategoria
// inputIdCategoria

if(isset($_POST['cmbSubCategoria']) && isset($_POST['inputIdCategoria']) && isset($_POST['iFluxoOperacional'])){

	$iCategorias = $_POST['cmbSubCategoria'];
	$iSubCategoria = $_POST['inputIdCategoria'];
	$iFluxoOperacional = $_POST['iFluxoOperacional'];

	$subCategoriaList = "";

	foreach($iCategorias as $subCat){
		$subCategoriaList .= "$subCat,";
	}
	$subCategoriaList  = substr($subCategoriaList, 0, -1);

	$sql = "SELECT DISTINCT ProduId, ProduNome, FOXPrDetalhamento, UnMedSigla, MarcaNome, ModelNome, FabriNome
			FROM Produto
			JOIN FluxoOperacionalXProduto on FOXPrProduto = ProduId
			JOIN UnidadeMedida on UnMedId = ProduUnidadeMedida
			LEFT JOIN ProdutoXFabricante on PrXFaProduto = ProduId and PrXFaFluxoOperacional = $iFluxoOperacional
			LEFT JOIN Marca on MarcaId = PrXFaMarca
			LEFT JOIN Modelo on ModelId = PrXFaModelo
			LEFT JOIN Fabricante on FabriId = PrXFaFabricante
			WHERE ProduEmpresa = " . $_SESSION['EmpreId'] . " and ProduCategoria = $iSubCategoria and 
			ProduSubCategoria in ($subCategoriaList) AND FOXPrFluxoOperacional = $iFluxoOperacional";

	$result = $conn->query($sql);
	$row = $result->fetchAll(PDO::FETCH_ASSOC);

	$output = '';
	$fTotalGeral = 0;
	foreach ($row as $cont=>$item){
		$cont++;
		
		$id = $item['ProduId'];
		
		$iQuantidade = isset($item['AdXPrQuantidade']) ? $item['AdXPrQuantidade'] : '';
		$fValorUnitario = isset($item['AdXPrValorUnitario']) ? mostraValor($item['AdXPrValorUnitario']) : '';
		$fValorTotal = (isset($item['AdXPrQuantidade']) and isset($item['AdXPrValorUnitario'])) ? mostraValor($item['AdXPrQuantidade'] * $item['AdXPrValorUnitario']) : '';

		$fTotalGeral += (isset($item['AdXPrQuantidade']) and isset($item['AdXPrValorUnitario'])) ? $item['AdXPrQuantidade'] * $item['AdXPrValorUnitario'] : 0;
		
		$detalhamento = $item['FOXPrDetalhamento']?$item['FOXPrDetalhamento']:$item['ProduNome'];

		$output .= "<div class='row' style='margin-top: 8px;' >
		<div class='col-lg-7'>
			<div class='row'>
				<div class='col-lg-1'>
					<input type='text' id='inputItem$cont' name='inputItem$cont' class='form-control-border-off' value='$cont' readOnly>
					<input type='hidden' id='inputIdProduto$cont' name='inputIdProduto$cont' value='$item[ProduId]' class='idProduto'>
				</div>
				<div class='col-lg-5'>
					<input type='text' id='inputProduto$cont' name='inputProduto$cont' class='form-control-border-off' data-popup='tooltip' title='$detalhamento' value='$item[ProduNome]' readOnly>
				</div>
				<div class='col-lg-2'>
					<input type='text' id='inputMarca$cont' name='inputMarca$cont' class='form-control-border-off' data-popup='tooltip' title='$item[MarcaNome]' value='$item[MarcaNome]' readOnly>
				</div>
				<div class='col-lg-2'>
					<input type='text' id='inputModelo$cont' name='inputModelo$cont' class='form-control-border-off' data-popup='tooltip' title='$item[ModelNome]' value='$item[ModelNome]' readOnly>
				</div>
				<div class='col-lg-2'>
					<input type='text' id='inputFabricante$cont' name='inputFabricante$cont' class='form-control-border-off' data-popup='tooltip' title='$item[FabriNome]' value='$item[FabriNome]' readOnly>
				</div>
			</div>
		</div>								
		<div class='col-lg-1'>
			<input type='text' id='inputUnidade$cont' name='inputUnidade$cont' class='form-control-border-off' value='$item[UnMedSigla]' readOnly>
		</div>
		<div class='col-lg-1'>
			<input type='text' id='inputQuantidade$cont' name='inputQuantidade$cont' class='form-control-border Quantidade pula' onChange='calculaValorTotal($cont)' onkeypress='return onlynumber();' value='$iQuantidade'>
		</div>	
		<div class='col-lg-1'>
			<input type='text' id='inputValorUnitario$cont' name='inputValorUnitario$cont' class='form-control-border ValorUnitario pula' onChange='calculaValorTotal($cont)' onKeyUp='moeda(this)' maxLength='12' value='$fValorUnitario'>
		</div>	
		<div class='col-lg-2'>
			<input type='text' id='inputValorTotal$cont' name='inputValorTotal$cont' class='form-control-border-off text-right' value='$fValorTotal' readOnly>
		</div>											
	</div>";	
	}

	$output .= "<div class='row' style='margin-top: 8px;'>
					<div class='col-lg-7'>
						<div class='row'>
							<div class='col-lg-1'>
								
							</div>
							<div class='col-lg-8'>
								
							</div>
							<div class='col-lg-3'>
								
							</div>
						</div>
					</div>								
					<div class='col-lg-1'>
						
					</div>
					<div class='col-lg-1'>
						
					</div>	
					<div class='col-lg-1' style='padding-top: 5px; text-align: right;'>
						<h3><b>Total:</b></h3>
					</div>	
					<div class='col-lg-2'>
						<input type='text' id='inputTotalGeralProduto' name='inputTotalGeralProduto' class='form-control-border-off' value='".mostraValor($fTotalGeral)."' readOnly>
					</div>											
				</div>";

	$output .= "<input type='hidden' id='totalRegistros' name='totalRegistros' value='$cont' >";

	echo $output;
} else {
	echo null;
}

?>
