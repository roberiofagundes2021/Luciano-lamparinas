<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

// cmbSubCategorias
// inputIdCategoria

if(isset($_POST['cmbSubCategorias']) && isset($_POST['inputIdCategoria']) && isset($_POST['iFluxoOperacional'])){

	$iCategorias = $_POST['cmbSubCategorias'];
	$iSubCategoria = $_POST['inputIdCategoria'];
    $iFluxoOperacional = $_POST['iFluxoOperacional'];

	$subCategoriaList = "";

	foreach($iCategorias as $subCat){
		$subCategoriaList .= "$subCat,";
	}
	$subCategoriaList  = substr($subCategoriaList, 0, -1);

	$sql = "SELECT DISTINCT ServiId, ServiNome, FOXSrDetalhamento, MarcaNome, ModelNome, FabriNome
			FROM Servico
			JOIN FluxoOperacionalXServico on FOXSrServico = ServiId
			LEFT JOIN ServicoXFabricante ON SrXFaServico = ServiId and SrXFaFluxoOperacional = FOXSrFluxoOperacional
			LEFT JOIN Marca on MarcaId = SrXFaMarca
			LEFT JOIN Modelo on ModelId = SrXFaModelo
			LEFT JOIN Fabricante on FabriId = SrXFaFabricante
			WHERE ServiEmpresa = " . $_SESSION['EmpreId'] . " AND ServiCategoria = $iSubCategoria AND 
			ServiSubCategoria IN ($subCategoriaList) AND FOXSrFluxoOperacional = $iFluxoOperacional";

	$result = $conn->query($sql);
	$row = $result->fetchAll(PDO::FETCH_ASSOC);

	$output = '';
	$fTotalGeral = 0;

	foreach ($row as $cont=>$item){
		
		$cont++;
		
		$id = $item['ServiId'];
		
		$iQuantidade = isset($item['FOXSrQuantidade']) ? $item['FOXSrQuantidade'] : '';
		$fValorUnitario = isset($item['FOXSrValorUnitario']) ? mostraValor($item['FOXSrValorUnitario']) : '';
		$fValorTotal = (isset($item['FOXSrQuantidade']) and isset($item['FOXSrValorUnitario'])) ? mostraValor($item['FOXSrQuantidade'] * $item['FOXSrValorUnitario']) : '';

		$fTotalGeral += (isset($item['FOXSrQuantidade']) and isset($item['FOXSrValorUnitario'])) ? $item['FOXSrQuantidade'] * $item['FOXSrValorUnitario'] : 0;
		
		$output .= "
		<div class='row' style='margin-top: 8px;' >
			<div class='col-lg-8'>
				<div class='row'>
					<div class='col-lg-1'>
						<input type='text' id='inputItem$cont' name='inputItem$cont' class='form-control-border-off' value='$cont' readOnly>
						<input type='hidden' id='inputIdServico$cont' name='inputIdServico$cont' value='$item[ServiId]' class='idServico'>
					</div>
					<div class='col-lg-5'>
						<input type='text' id='inputServico$cont' name='inputServico$cont' class='form-control-border-off' data-popup='tooltip' title='$item[FOXSrDetalhamento]' value='$item[ServiNome]' readOnly>
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
				<input type='text' id='inputQuantidadeServico$cont' name='inputQuantidadeServico$cont' class='form-control-border Quantidade pula' onChange='calculaValorTotalServico($cont)' onkeypress='return onlynumber();' value='$iQuantidade'>
			</div>	
			<div class='col-lg-1'>
				<input type='text' id='inputValorUnitarioServico$cont' name='inputValorUnitarioServico$cont' class='form-control-border ValorUnitario pula text-right' onChange='calculaValorTotalServico($cont)' onKeyUp='moeda(this)' maxLength='12' value='$fValorUnitario'>
			</div>	
			<div class='col-lg-2'>
				<input type='text' id='inputValorTotalServico$cont' name='inputValorTotalServico$cont' class='form-control-border-off text-right' value='$fValorTotal' readOnly>
			</div>												
		</div>";	
	}

	$output .= "
	<div class='row' style='margin-top: 8px;'>
		<div class='col-lg-8'>
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
			<div class='col-lg-1' style='padding-top: 5px; text-align: right;'>
				<h5><b>Total:</b></h5>
			</div>	
			<div class='col-lg-2'>
				<input type='text' id='inputTotalGeralServico' name='inputTotalGeralServico' class='form-control-border-off text-right' value='".mostraValor($fTotalGeral)."' readOnly>
			</div>											
	</div>";

	$output .= "<input type='hidden' id='totalRegistrosServicos' name='totalRegistrosServicos' value='$cont'>";

	echo $output;
} else {
	echo null;
}

?>
