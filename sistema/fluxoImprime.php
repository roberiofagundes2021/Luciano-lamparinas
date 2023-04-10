<?php

include_once("sessao.php");

include('global_assets/php/conexao.php');

use Mpdf\Mpdf;

require_once 'global_assets/php/vendor/autoload.php';

if (isset($_POST['inputFluxoId'])) {
	$iFluxoOperacional = $_POST['inputFluxoId'];
} else {
	$iFluxoOperacional = $_POST['inputFluxoOperacionalId'];
}

//Pega os dados principais do fluxo
$sql = "SELECT FlOpeNumContrato, FlOpeNumProcesso, FlOpeValor, FlOpeDataInicio, FlOpeDataFim, CategNome, 
		dbo.fnSubCategoriasFluxo(FlOpeUnidade, FlOpeId) as SubCategorias, ForneNome, ForneRazaoSocial, ForneCelular, 
		ForneEmail, FlOpeTermoReferencia, TrRefTabelaProduto, TrRefTabelaServico
		FROM FluxoOperacional
		JOIN Fornecedor on ForneId = FlOpeFornecedor
		JOIN Categoria on CategId = FlOpeCategoria
		LEFT JOIN TermoReferencia on TrRefId = FlOpeTermoReferencia
		WHERE FlOpeUnidade = " . $_SESSION['UnidadeId'] . " and FlOpeId = " . $iFluxoOperacional;
$result = $conn->query($sql);
$row = $result->fetch(PDO::FETCH_ASSOC);

// Lista as SubCategorias
$sql = "SELECT SbCatId, SbCatNome
		FROM FluxoOperacionalXSubCategoria
		JOIN SubCategoria ON SbCatId = FOXSCSubcategoria
		WHERE FOXSCUnidade = " . $_SESSION['UnidadeId'] . "	AND FOXSCFluxo = " . $iFluxoOperacional."
		ORDER BY SbCatNome ASC";
$result = $conn->query($sql);
$rowSubCategoria = $result->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT AditiId, AditiNumero, AditiDtCelebracao, AditiDtInicio, AditiDtFim, AditiValor, FlOpeId, 
		FlOpeNumContrato, FlOpeNumProcesso, FlOpeValor, FlOpeDataInicio, FlOpeDataFim
		FROM Aditivo
		JOIN FluxoOperacional on FlOpeId = AditiFluxoOperacional
		WHERE AditiUnidade = " . $_SESSION['UnidadeId'] . " and AditiFluxoOperacional = " . $iFluxoOperacional;
$result = $conn->query($sql);
$rowAditivos = $result->fetchAll(PDO::FETCH_ASSOC);

//Verifica se o fluxo tem produtos
$sql = "SELECT ProduId
		FROM Produto
		JOIN FluxoOperacionalXProduto on FOXPrProduto = ProduId
		WHERE ProduEmpresa = " . $_SESSION['EmpreId'] . " and FOXPrFluxoOperacional = " . $iFluxoOperacional;	
$result = $conn->query($sql);
$rowProd = $result->fetchAll(PDO::FETCH_ASSOC);
$totalProdutos = count($rowProd);

 //Verifica se o fluxo tem serviços
$sql = "SELECT ServiId
		FROM Servico
		JOIN FluxoOperacionalXServico on FOXSrServico = ServiId
		WHERE ServiEmpresa = " . $_SESSION['EmpreId'] . " and FOXSrFluxoOperacional = " . $iFluxoOperacional;
$result = $conn->query($sql);
$rowServ = $result->fetchAll(PDO::FETCH_ASSOC);
$totalServicos = count($rowServ);

//Se empresa pública usar o termo CONTRATO, se empresa privado FLUXO OPERACIONAL
$sql = "SELECT ParamEmpresaPublica
		FROM Parametro
		WHERE ParamEmpresa = ". $_SESSION['EmpreId'];
$result = $conn->query($sql);
$rowParametro = $result->fetch(PDO::FETCH_ASSOC);

if ($rowParametro['ParamEmpresaPublica']){
	$fluxo = "CONTRATO";
} else {
	$fluxo = "FLUXO OPERACIONAL";
}

try {
	$mpdf = new mPDF([
		'mode' => 'utf-8',    // mode - default ''
		'format' => 'A4-P',    // format - A4, for example, default ''
		'default_font_size' => 9,     // font size - default 0
		'default_font' => '',    // default font family
		'margin-left' => 15,    // margin_left
		'margin-right' => 15,    // margin right
		'margin-top' => 158,     // margin top    -- aumentei aqui para que não ficasse em cima do header
		'margin-bottom' => 60,    // margin bottom
		'margin-header' => 6,     // margin header
		'margin-bottom' => 0,     // margin footer
		'orientation' => 'P'
	]);  // L - landscape, P - portrait	

	$html = "

	<style>
		th{
		    text-align: center; 
		    border: #bbb solid 1px; 
		    background-color: #f8f8f8; 
		    padding: 8px;
		}

		td{
			padding: 8px;				
			border: #bbb solid 1px;
		}
	</style>

	<div style='position: relative; width:100%; border-bottom: 1px solid #000;'>
		<div style='width:400px; float:left; display: inline;'>
			<img src='global_assets/images/empresas/".$_SESSION['EmpreFoto']."' style='width:60px; height:60px; float:left; margin-right: 10px; margin-top:-10px;' />		
			<span style='font-weight:bold;line-height:200px;'>" . $_SESSION['EmpreNomeFantasia'] . "</span><br>
			<div style='position: absolute; font-size:12px; margin-top: 8px; margin-left:4px;'>Unidade: " . $_SESSION['UnidadeNome'] . "</div>
		</div>
		<div style='width:250px; float:right; display: inline; text-align:right;'>
			<div>" . date('d/m/Y') . "</div>
			<div style='margin-top:8px;'>".$fluxo.": " . $row['FlOpeNumContrato'] . "</div>
		</div> 
	</div>

	<div style='text-align:center; margin-top: 20px;'><h1>FLUXO OPERACIONAL</h1></div>
	";

	$html .= '
	<h2 style="margin-top: 20px; text-align: center;">RESUMO</h2>
'	;	

	//Dados do Fluxo
	$html .= '
    <table style="width:100%; border-collapse: collapse;">
        <tr style="background-color:#F1F1F1;">
            <td style="width:20%; font-size:14px;">Início:<br>' . mostraData($row['FlOpeDataInicio']) . '</td>
            <td style="width:20%; font-size:14px;">Fim:<br>' . mostraData($row['FlOpeDataFim']) . '</td> ';
			if ($fluxo == 'CONTRATO'){
				$html .= ' <td style="width:20%; font-size:14px;">Nº Ata Registro:<br>' . $row['FlOpeNumContrato'] . '</td>
						   <td style="width:20%; font-size:14px;">Nº Processo:<br>' . $row['FlOpeNumProcesso'] . '</td> ';
			}else{		
				$html .= ' <td style="width:40%; font-size:14px;">Nº FLUXO OPERACIONAL:<br>' . $row['FlOpeNumContrato'] . '</td>';
			}				
				$html .= '<td style="width:20%; font-size:14px; border-left: none; text-align:right;">Valor:<br>' . mostraValor($row['FlOpeValor']) . '</td>
		</tr>
	</table>
	<table style="width:100%; border-collapse: collapse;">
        <tr>
            <td style="width:40%; font-size:14px;">Categoria:<br>' . $row['CategNome'] . '</td>
            <td style="width:60%; font-size:14px;">Sub Categoria(s):<br>' . $row['SubCategorias'] . '</td>
        </tr>
	</table>
	<table style="width:100%; border-collapse: collapse;">
        <tr>
            <td style="width:40%; font-size:14px;">Fornecedor:<br>' . $row['ForneRazaoSocial'] . '</td>
            <td style="width:40%; font-size:14px;">E-mail:<br>' . $row['ForneEmail'] . '</td>
            <td style="width:20%; font-size:14px;">Telefone:<br>' . $row['ForneCelular'] . '</td>
        </tr>
    </table>
	<br>';

	$totalGeralFluxo = $row['FlOpeValor'];

/*	$totalGeralProdutos = 0;

	if ($rowProdutos){
		foreach ($rowProdutos as $rowProduto) {

			if ($rowProduto['FOXPrValorUnitario'] != '' and $rowProduto['FOXPrValorUnitario'] != null) {
				$valorUnitario = $rowProduto['FOXPrValorUnitario'];
				$valorTotal = $rowProduto['FOXPrQuantidade'] * $rowProduto['FOXPrValorUnitario'];
			} else {
				$valorUnitario = 0;
				$valorTotal = 0;
			}
	
			$totalGeralProdutos += $valorTotal;
		}	
	}

	$totalGeralServicos = 0;

	if ($rowServicos){
		foreach ($rowServicos as $rowServico) {

			if ($rowServico['FOXSrValorUnitario'] != '' and $rowServico['FOXSrValorUnitario'] != null) {
				$valorUnitario = $rowServico['FOXSrValorUnitario'];
				$valorTotal = $rowServico['FOXSrQuantidade'] * $rowServico['FOXSrValorUnitario'];
			} else {
				$valorUnitario = 0;
				$valorTotal = 0;
			}
	
			$totalGeralServicos += $valorTotal;
		}	
	}
*/
	$totalGeralAditivos = 0;

	//Dados dos Aditivos
	if ($rowAditivos){

		foreach ($rowAditivos as $aditivo){
			$html .= '
			<table style="width:100%; border-collapse: collapse;">
				<tr>
					<td style="width:17%; font-size:10px;">Nº Aditivo: ' . $aditivo['AditiNumero'] . '</td>
					<td style="width:23%; font-size:10px;">Celebração: ' . mostraData($aditivo['AditiDtCelebracao']) . '</td>
					<td style="width:18%; font-size:10px;">Início: ' . mostraData($aditivo['AditiDtInicio']) . '</td>
					<td style="width:22%; font-size:10px;">Fim: ' . mostraData($aditivo['AditiDtFim']) . '</td>
					<td style="width:7%; font-size:10px; border-right: none;">Valor:</td>
					<td style="width:13%; font-size:10px; border-left: none; text-align:right;">'. mostraValor($aditivo['AditiValor']) . '</td>	
				</tr>
			</table>
			<br>';

			$totalGeralAditivos += $aditivo['AditiValor'];
		}
	}

	$totalGeral = $totalGeralFluxo + $totalGeralAditivos;

	$html .= "<table style='width:100%; border-collapse: collapse; margin-top: 20px;'>
	 			<tr>
                	<td colspan='5' height='50' valign='middle' style='width:80%'>
	                    <strong>TOTAL GERAL (".ucfirst(strtolower($fluxo))." + Aditivos)</strong>
                    </td>
				    <td style='text-align: right; width:20%'>
				        " . mostraValor($totalGeral) . "
				    </td>
			    </tr>
			  </table>
	";

	$totalGeralProdutos = 0;

	$html .= '
	<h2 style="margin-top: 50px; text-align: center;">DETALHAMENTO DO FLUXO</h2>
	';

	$totalGeralProdutos = 0;

	if ($totalProdutos > 0){
		$html .= "<div style='text-align:center; margin-top: 20px;'><h2>PRODUTOS</h2></div>";
			
		$html .= '
		<div style="font-weight: bold; position:relative; margin-top: 5px; background-color:#ddd; padding: 8px; border: 1px solid #ccc;">
			Categoria: <span style="font-weight:normal;">' . $row['CategNome'] . '</span> 
		</div>
		<br>
		';

		if(!COUNT($rowSubCategoria)){
			$totalProdutos = 0;
		
			$sql = "SELECT ProduId, ProduNome, FOXPrDetalhamento as Detalhamento, UnMedSigla, FOXPrQuantidade, FOXPrValorUnitario, MarcaNome
					FROM Produto
					JOIN FluxoOperacionalXProduto on FOXPrProduto = ProduId
					LEFT JOIN ProdutoXFabricante ON PrXFaProduto = FOXPrProduto and PrXFaFluxoOperacional = FOXPrFluxoOperacional
					LEFT JOIN FluxoOperacional on FlOpeId = PrXFaFluxoOperacional
					LEFT JOIN Marca on MarcaId = PrXFaMarca
					JOIN UnidadeMedida on UnMedId = ProduUnidadeMedida
					JOIN SubCategoria on SbCatId = ProduSubCategoria
					WHERE FOXPrFluxoOperacional = $iFluxoOperacional and ProduEmpresa = " . $_SESSION['EmpreId'] . " ORDER BY SbCatNome, ProduNome ASC";	
			$result = $conn->query($sql);
			$rowProdutos = $result->fetchAll(PDO::FETCH_ASSOC);
			$countProdutos = count($rowProdutos);
	
			if ($countProdutos > 0) {
	
				$html .= '
						<div style="font-weight: bold; position:relative; margin-top: 15px; background-color:#eee; padding: 8px; border: 1px solid #ccc;">
							SubCategoria: <span style="font-weight:normal;">' . $sbcat['SbCatNome'] . '</span>
						</div>';	
	
				$html .= '
				<table style="width:100%; border-collapse: collapse; margin-top: 20px;">
					<tr>
						<th style="text-align: center; width:8%">Item</th>
						<th style="text-align: left; width:40%">Produto</th>
						<th style="text-align: center; width:10%">Unidade</th>				
						<th style="text-align: center; width:12%">Quant.</th>
						<th style="text-align: center; width:15%">V. Unit.</th>
						<th style="text-align: center; width:15%">V. Total</th>
					</tr>
				';
	
				$cont = 1;
	
				foreach ($rowProdutos as $rowProduto) {
	
					if ($rowProduto['FOXPrValorUnitario'] != '' and $rowProduto['FOXPrValorUnitario'] != null) {
						$valorUnitario = $rowProduto['FOXPrValorUnitario'];
						$valorTotal = $rowProduto['FOXPrQuantidade'] * $rowProduto['FOXPrValorUnitario'];
					} else {
						$valorUnitario = 0;
						$valorTotal = 0;
					}
					
					$html .= "
						<tr>
							<td style='text-align: center;'>" . $cont . "</td>
							<td style='text-align: left;'>" . $rowProduto['ProduNome'] . ": " . $rowProduto['Detalhamento'] . "<br>Marca: ".$rowProduto['MarcaNome']."</td>
							<td style='text-align: center;'>" . $rowProduto['UnMedSigla'] . "</td>					
							<td style='text-align: center;'>" . $rowProduto['FOXPrQuantidade'] . "</td>	
							<td style='text-align: right;'>" . mostraValor($valorUnitario) . "</td>
							<td style='text-align: right;'>" . mostraValor($valorTotal) . "</td>	
						</tr>
					";
	
					$cont++;
					$totalProdutos += $valorTotal;
				}

				$totalGeralProdutos += $totalProdutos;

				$html .= "  <tr>
								<td colspan='5' height='50' valign='middle'>
									<strong>Total Produtos</strong>
								</td>
								<td style='text-align: right' colspan='2'>
									" . mostraValor($totalProdutos) . "
								</td>
							</tr>";
				$html .= "</table>";	

				$html .= "<br>";					
			}
		} else {
			foreach ($rowSubCategoria as $sbcat) {
	
				$totalProdutos = 0;
		
				$sql = "SELECT ProduId, ProduNome, FOXPrDetalhamento as Detalhamento, UnMedSigla, FOXPrQuantidade, FOXPrValorUnitario,
						MarcaNome, ModelNome, FabriNome
						FROM Produto
						JOIN FluxoOperacionalXProduto on FOXPrProduto = ProduId
						LEFT JOIN ProdutoXFabricante ON PrXFaProduto = FOXPrProduto and PrXFaFluxoOperacional = FOXPrFluxoOperacional
						LEFT JOIN FluxoOperacional on FlOpeId = PrXFaFluxoOperacional
						LEFT JOIN Marca on MarcaId = PrXFaMarca
						LEFT JOIN Modelo on ModelId = PrXFaModelo
						LEFT JOIN Fabricante on FabriId = PrXFaFabricante
						JOIN UnidadeMedida on UnMedId = ProduUnidadeMedida
						JOIN SubCategoria on SbCatId = ProduSubCategoria
						WHERE ProduEmpresa = " . $_SESSION['EmpreId'] . " and FOXPrFluxoOperacional = " . $iFluxoOperacional."
						and SbCatId = ".$sbcat['SbCatId']."
						ORDER BY SbCatNome, ProduNome ASC";	
				$result = $conn->query($sql);
				$rowProdutos = $result->fetchAll(PDO::FETCH_ASSOC);
				$countProdutos = count($rowProdutos);
		
				if ($countProdutos > 0) {
		
					$html .= '
							<div style="font-weight: bold; position:relative; margin-top: 15px; background-color:#eee; padding: 8px; border: 1px solid #ccc;">
								SubCategoria: <span style="font-weight:normal;">' . $sbcat['SbCatNome'] . '</span>
							</div>';	
		
					$html .= '
					<table style="width:100%; border-collapse: collapse; margin-top: 20px;">
						<tr>
							<th style="text-align: center; width:8%">Item</th>
							<th style="text-align: left; width:40%">Produto</th>
							<th style="text-align: center; width:10%">Unidade</th>				
							<th style="text-align: center; width:12%">Quant.</th>
							<th style="text-align: center; width:15%">V. Unit.</th>
							<th style="text-align: center; width:15%">V. Total</th>
						</tr>
					';
		
					$cont = 1;
		
					foreach ($rowProdutos as $rowProduto) {
		
						if ($rowProduto['FOXPrValorUnitario'] != '' and $rowProduto['FOXPrValorUnitario'] != null) {
							$valorUnitario = $rowProduto['FOXPrValorUnitario'];
							$valorTotal = $rowProduto['FOXPrQuantidade'] * $rowProduto['FOXPrValorUnitario'];
						} else {
							$valorUnitario = 0;
							$valorTotal = 0;
						}

						$MarcaModeloFabricante = '';

						$MarcaModeloFabricante .= $rowProduto['MarcaNome']?'<br>MARCA: '.$rowProduto['MarcaNome']:'';
						$MarcaModeloFabricante .= $rowProduto['ModelNome']?'<br>MODELO: '.$rowProduto['ModelNome']:'';
						$MarcaModeloFabricante .= $rowProduto['FabriNome']?'<br>FABRICANTE: '.$rowProduto['FabriNome']:'';

						$detalhamento = $rowProduto['Detalhamento']?' : '.$rowProduto['Detalhamento']:'';
						
						$html .= "
							<tr>
								<td style='text-align: center;'>" . $cont . "</td>
								<td style='text-align: left;'>" . $rowProduto['ProduNome'] . "$detalhamento $MarcaModeloFabricante</td>
								<td style='text-align: center;'>" . $rowProduto['UnMedSigla'] . "</td>					
								<td style='text-align: center;'>" . $rowProduto['FOXPrQuantidade'] . "</td>	
								<td style='text-align: right;'>" . mostraValor($valorUnitario) . "</td>
								<td style='text-align: right;'>" . mostraValor($valorTotal) . "</td>	
							</tr>
						";
		
						$cont++;
						$totalProdutos += $valorTotal;
					}
	
					$totalGeralProdutos += $totalProdutos;
	
					$html .= "  <tr>
									<td colspan='5' height='50' valign='middle'>
										<strong>Total Produtos</strong>
									</td>
									<td style='text-align: right' colspan='2'>
										" . mostraValor($totalProdutos) . "
									</td>
								</tr>";
					$html .= "</table>";	
	
					$html .= "<br>";					
				}
			}
		}
	
	}

	$totalGeralServicos = 0;

	if ($totalServicos > 0){
		
		$html .= "<div style='text-align:center; margin-top: 20px;'><h2>SERVIÇOS</h2></div>";
			
		$html .= '
		<div style="font-weight: bold; position:relative; margin-top: 5px; background-color:#ddd;  padding: 8px;  border: 1px solid #ccc;">
			Categoria: <span style="font-weight:normal;">' . $row['CategNome'] . '</span> 
		</div>
		<br>
		';

		if(!COUNT($rowSubCategoria)){
			$totalServicos = 0;
		
			$sql = "SELECT ServiId, ServiNome, FOXSrDetalhamento as Detalhamento, FOXSrQuantidade, FOXSrValorUnitario,
					MarcaNome, ModelNome, FabriNome
					FROM Servico
					JOIN FluxoOperacionalXServico on FOXSrServico = ServiId
					LEFT JOIN ServicoXFabricante ON SrXFaServico = FOXSrServico and SrXFaFluxoOperacional = FOXSrFluxoOperacional
					LEFT JOIN FluxoOperacional on FlOpeId = SrXFaFluxoOperacional
					LEFT JOIN Marca on MarcaId = SrXFaMarca
					LEFT JOIN Modelo on ModelId = SrXFaModelo
					LEFT JOIN Fabricante on FabriId = SrXFaFabricante
					JOIN SubCategoria on SbCatId = ServiSubCategoria
					WHERE FOXSrFluxoOperacional = $iFluxoOperacional and ServiEmpresa = " . $_SESSION['EmpreId'] . " ORDER BY SbCatNome, ServiNome ASC";
			$result = $conn->query($sql);
			$rowServicos = $result->fetchAll(PDO::FETCH_ASSOC);
			$countServicos = count($rowServicos);
			
	
			if ($countServicos > 0) {
	
				$html .= '
						<div style="font-weight: bold; position:relative; margin-top: 15px; background-color:#eee; padding: 8px; border: 1px solid #ccc;">
							SubCategoria: <span style="font-weight:normal;">' . $sbcat['SbCatNome'] . '</span>
						</div>';	
	
				$html .= '
				<table style="width:100%; border-collapse: collapse; margin-top: 20px;">
					<tr>
						<th style="text-align: center; width:8%">Item</th>
						<th style="text-align: left; width:53%">Serviço</th>
						<th style="text-align: center; width:12%">Quant.</th>
						<th style="text-align: center; width:12%">V. Unit.</th>
						<th style="text-align: center; width:15%">V. Total</th>
					</tr>
				';
	
				$cont = 1;
	
				foreach ($rowServicos as $rowServico) {
	
					if ($rowServico['FOXSrValorUnitario'] != '' and $rowServico['FOXSrValorUnitario'] != null) {
						$valorUnitario = $rowServico['FOXSrValorUnitario'];
						$valorTotal = $rowServico['FOXSrQuantidade'] * $rowServico['FOXSrValorUnitario'];
					} else {
						$valorUnitario = 0;
						$valorTotal = 0;
					}

					$MarcaModeloFabricante = '';

					$MarcaModeloFabricante .= $rowServico['MarcaNome']?'<br>MARCA: '.$rowServico['MarcaNome']:'';
					$MarcaModeloFabricante .= $rowServico['ModelNome']?'<br>MODELO: '.$rowServico['ModelNome']:'';
					$MarcaModeloFabricante .= $rowServico['FabriNome']?'<br>FABRICANTE: '.$rowServico['FabriNome']:'';

					$detalhamento = $rowServico['Detalhamento']?' : '.$rowServico['Detalhamento']:'';
					
					$html .= "
						<tr>
							<td style='text-align: center;'>" . $cont . "</td>
							<td style='text-align: left;'>" . $rowServico['ServiNome'] . "$detalhamento $MarcaModeloFabricante</td>	
							<td style='text-align: center;'>" . $rowServico['FOXSrQuantidade'] . "</td>	
							<td style='text-align: right;'>" . mostraValor($valorUnitario) . "</td>
							<td style='text-align: right;'>" . mostraValor($valorTotal) . "</td>		
						</tr>
					";
	
					$cont++;
					$totalServicos += $valorTotal;
				}		
				
				$totalGeralServicos += $totalServicos;

				$html .= "  <tr>
								<td colspan='4' height='50' valign='middle'>
									<strong>Total Serviços</strong>
								</td>
								<td style='text-align: right' colspan='2'>
									" . mostraValor($totalServicos) . "
								</td>
							</tr>";
				$html .= "</table>";

				$html .= "<br>";				
			}
		}else {
			foreach ($rowSubCategoria as $sbcat) {
	
				$totalServicos = 0;
		
				$sql = "SELECT ServiId, ServiNome, FOXSrDetalhamento as Detalhamento, FOXSrQuantidade, FOXSrValorUnitario,MarcaNome
						FROM Servico
						JOIN FluxoOperacionalXServico on FOXSrServico = ServiId
						LEFT JOIN ServicoXFabricante ON SrXFaServico = FOXSrServico and SrXFaFluxoOperacional = FOXSrFluxoOperacional
						LEFT JOIN FluxoOperacional on FlOpeId = SrXFaFluxoOperacional
						LEFT JOIN Marca on MarcaId = SrXFaMarca
						JOIN SubCategoria on SbCatId = ServiSubCategoria
						WHERE ServiEmpresa = " . $_SESSION['EmpreId'] . " and FOXSrFluxoOperacional = " . $iFluxoOperacional."
						and SbCatId = ".$sbcat['SbCatId']."
						ORDER BY SbCatNome, ServiNome ASC";
				$result = $conn->query($sql);
				$rowServicos = $result->fetchAll(PDO::FETCH_ASSOC);
				$countServicos = count($rowServicos);
				
		
				if ($countServicos > 0) {
		
					$html .= '
							<div style="font-weight: bold; position:relative; margin-top: 15px; background-color:#eee; padding: 8px; border: 1px solid #ccc;">
								SubCategoria: <span style="font-weight:normal;">' . $sbcat['SbCatNome'] . '</span>
							</div>';	
		
					$html .= '
					<table style="width:100%; border-collapse: collapse; margin-top: 20px;">
						<tr>
							<th style="text-align: center; width:8%">Item</th>
							<th style="text-align: left; width:53%">Serviço</th>
							<th style="text-align: center; width:12%">Quant.</th>
							<th style="text-align: center; width:12%">V. Unit.</th>
							<th style="text-align: center; width:15%">V. Total</th>
						</tr>
					';
		
					$cont = 1;
		
					foreach ($rowServicos as $rowServico) {
		
						if ($rowServico['FOXSrValorUnitario'] != '' and $rowServico['FOXSrValorUnitario'] != null) {
							$valorUnitario = $rowServico['FOXSrValorUnitario'];
							$valorTotal = $rowServico['FOXSrQuantidade'] * $rowServico['FOXSrValorUnitario'];
						} else {
							$valorUnitario = 0;
							$valorTotal = 0;
						}
						
						$html .= "
							<tr>
								<td style='text-align: center;'>" . $cont . "</td>
								<td style='text-align: left;'>" . $rowServico['ServiNome'] . ": " . $rowServico['Detalhamento'] . "<br>Marca: ".$rowServico['MarcaNome']."</td>	
								<td style='text-align: center;'>" . $rowServico['FOXSrQuantidade'] . "</td>	
								<td style='text-align: right;'>" . mostraValor($valorUnitario) . "</td>
								<td style='text-align: right;'>" . mostraValor($valorTotal) . "</td>		
							</tr>
						";
		
						$cont++;
						$totalServicos += $valorTotal;
					}		
					
					$totalGeralServicos += $totalServicos;
	
					$html .= "  <tr>
									<td colspan='4' height='50' valign='middle'>
										<strong>Total Serviços</strong>
									</td>
									<td style='text-align: right' colspan='2'>
										" . mostraValor($totalServicos) . "
									</td>
								</tr>";
					$html .= "</table>";
	
					$html .= "<br>";				
				}
			}
		}
	}

	$totalGeral = $totalGeralProdutos + $totalGeralServicos;

	$html .= "<table style='width:100%; border-collapse: collapse; margin-top: 20px;'>
	 			<tr>
                	<td colspan='5' height='50' valign='middle' style='width:85%'>
	                    <strong>TOTAL DE ITENS GERAIS</strong>
                    </td>
				    <td style='text-align: right; width:15%'>
				        " . mostraValor($totalGeral) . "
				    </td>
			    </tr>
			  </table>
	";

	// Exibindo os produtos e serviços dos Aditivos do Fluxo Operacional
	foreach ($rowAditivos as $aditivo) {
		//////////////////////////////////
		$html .= '
	        <h2 style="margin-top: 50px; text-align: center;">DETALHAMENTO DO ADITIVO</h2>
	    ';

		$html .= '
                <table style="width:100%; border-collapse: collapse;">
                    <tr style="background-color:#F1F1F1;">
                        <td style="width:20%; font-size:10px;">Nº Aditivo: ' . $aditivo['AditiNumero'] . '</td>
                        <td style="width:30%; font-size:10px;">Data de Celebração: ' . mostraData($aditivo['AditiDtCelebracao']) . '</td>
                        <td style="width:18%; font-size:10px;">Início: ' . mostraData($aditivo['AditiDtInicio']) . '</td>
						<td style="width:17%; font-size:10px;">Fim: ' . mostraData($aditivo['AditiDtFim']) . '</td>
						<td style="width:7%; font-size:10px;border-right:none;">Valor:</td>
						<td style="width:8%; font-size:10px;border-left: none; text-align:right;">' . mostraValor($aditivo['AditiValor']) . '</td>						
                    </tr>
                </table>
	            <br>';

		/* A marca não deveria ser LEFT JOIN e sim JOIN, já que é um campo obrigatório. Foi feito assim por causa dos cadastro antigos que não tem marca. 
		Com isso fomos obrigados a usar o DISTINCT */
		if ($row['FlOpeTermoReferencia'] && $row['TrRefTabelaProduto'] != null && $row['TrRefTabelaProduto'] == 'ProdutoOrcamento'){
			$sql = "SELECT ProduId, ProduNome, PrOrcDetalhamento as Detalhamento, UnMedSigla, AdXPrQuantidade, AdXPrValorUnitario, MarcaNome, SbCatNome
					FROM Produto
					JOIN AditivoXProduto on AdXPrProduto = ProduId
					JOIN UnidadeMedida on UnMedId = ProduUnidadeMedida
					JOIN SubCategoria on SbCatId = ProduSubCategoria
					JOIN ProdutoOrcamento on PrOrcProduto = ProduId
					JOIN FluxoOperacionalXProduto on FOXPrProduto = ProduId
				    JOIN ProdutoXFabricante ON PrXFaProduto = FOXPrProduto and PrXFaFluxoOperacional = FOXPrFluxoOperacional
				    JOIN FluxoOperacional on FlOpeId = PrXFaFluxoOperacional
				    JOIN Marca on MarcaId = PrXFaMarca
					WHERE ProduEmpresa = " . $_SESSION['EmpreId'] . " and FOXPrFluxoOperacional = " . $iFluxoOperacional." and AdXPrAditivo = " . $aditivo['AditiId']."
					ORDER BY SbCatNome ASC";
		} else {
			$sql = "SELECT ProduId, ProduNome, FOXPrDetalhamento as Detalhamento, UnMedSigla, AdXPrQuantidade, AdXPrValorUnitario, MarcaNome, SbCatNome
					FROM Produto
					JOIN AditivoXProduto on AdXPrProduto = ProduId
					JOIN UnidadeMedida on UnMedId = ProduUnidadeMedida
					JOIN SubCategoria on SbCatId = ProduSubCategoria
					JOIN FluxoOperacionalXProduto on FOXPrProduto = ProduId
					JOIN ProdutoXFabricante ON PrXFaProduto = FOXPrProduto and PrXFaFluxoOperacional = FOXPrFluxoOperacional
					JOIN FluxoOperacional on FlOpeId = PrXFaFluxoOperacional
					JOIN Marca on MarcaId = PrXFaMarca
					WHERE ProduEmpresa = " . $_SESSION['EmpreId'] . " and FOXPrFluxoOperacional = " . $iFluxoOperacional." and AdXPrAditivo = " . $aditivo['AditiId']."
					ORDER BY SbCatNome ASC";
		}
		$result = $conn->query($sql);
		$rowProdutos = $result->fetchAll(PDO::FETCH_ASSOC);
		$totalProdutos = count($rowProdutos);

		/* A marca não deveria ser LEFT JOIN e sim JOIN, já que é um campo obrigatório. Foi feito assim por causa dos cadastro antigos que não tem marca. 
		Com isso fomos obrigados a usar o DISTINCT */
		if ($row['FlOpeTermoReferencia'] && $row['TrRefTabelaServico'] != null && $row['TrRefTabelaServico'] == 'ServicoOrcamento'){		
			$sql = "SELECT ServiId, ServiNome, SrOrcDetalhamento as Detalhamento, AdXSrQuantidade, AdXSrValorUnitario, MarcaNome, SbCatNome
					FROM Servico
					JOIN AditivoXServico on AdXSrServico = ServiId
					JOIN SubCategoria on SbCatId = ServiSubCategoria
					JOIN ServicoOrcamento on SrOrcServico = ServiId
					JOIN FluxoOperacionalXServico on FOXSrServico = ServiId
					JOIN ServicoXFabricante ON SrXFaServico = FOXSrServico and SrXFaFluxoOperacional = FOXSrFluxoOperacional
				    JOIN FluxoOperacional on FlOpeId = SrXFaFluxoOperacional
					JOIN Marca on MarcaId = SrXFaMarca
					WHERE ServiEmpresa = " . $_SESSION['EmpreId'] . " and FOXSrFluxoOperacional = " . $iFluxoOperacional." and AdXSrAditivo = " . $aditivo['AditiId']."
					ORDER BY SbCatNome ASC";
		} else {
			$sql = "SELECT ServiId, ServiNome, AdXSrDetalhamento as Detalhamento, AdXSrQuantidade, AdXSrValorUnitario, MarcaNome, SbCatNome
					FROM Servico
					JOIN AditivoXServico on AdXSrServico = ServiId
					JOIN SubCategoria on SbCatId = ServiSubCategoria
					JOIN FluxoOperacionalXServico on FOXSrServico = ServiId
					JOIN ServicoXFabricante ON SrXFaServico = FOXSrServico and SrXFaFluxoOperacional = FOXSrFluxoOperacional
				    JOIN FluxoOperacional on FlOpeId = SrXFaFluxoOperacional
					JOIN Marca on MarcaId = SrXFaMarca
					WHERE ServiEmpresa = " . $_SESSION['EmpreId'] . " and FOXSrFluxoOperacional = " . $iFluxoOperacional." and AdXSrAditivo = " . $aditivo['AditiId']."
					ORDER BY SbCatNome ASC";
		}
		$result = $conn->query($sql);
		$rowServicos = $result->fetchAll(PDO::FETCH_ASSOC);
		$totalServicos = count($rowServicos);

		$totalGeralProdutos = 0;

		if ($totalProdutos > 0) {

			$html .= "<div style='margin-top: -10px; text-align:center;'><h3>Produtos do Aditivo N° ".$aditivo['AditiNumero']."</h3></div>";

			$html .= '
			<table style="width:100%; border-collapse: collapse;">
				<tr>
					<th style="text-align: center; width:8%">Item</th>
					<th style="text-align: left; width:43%">Produto</th>
					<th style="text-align: center; width:10%">Unidade</th>				
					<th style="text-align: center; width:12%">Quant.</th>
					<th style="text-align: center; width:12%">V. Unit.</th>
					<th style="text-align: center; width:15%">V. Total</th>
				</tr>
			';

			$cont = 1;

			foreach ($rowProdutos as $rowProduto) {

				if ($rowProduto['AdXPrValorUnitario'] != '' and $rowProduto['AdXPrValorUnitario'] != null) {
					$valorUnitario = $rowProduto['AdXPrValorUnitario'];
					$valorTotal = $rowProduto['AdXPrQuantidade'] * $rowProduto['AdXPrValorUnitario'];
				} else {
					$valorUnitario = 0;
					$valorTotal = 0;
				}

				if ($totalProdutos == ($cont)) {
					$html .= "
				<tr>
					<td style='text-align: center;'>" . $cont . "</td>
					<td style='text-align: left;'>" . $rowProduto['ProduNome'] . ": " . $rowProduto['Detalhamento'].
					"<br>Marca: ".$rowProduto['MarcaNome']."</td>
					<td style='text-align: center;'>" . $rowProduto['UnMedSigla'] . "</td>					
					<td style='text-align: center;'>" . $rowProduto['AdXPrQuantidade'] . "</td>
					<td style='text-align: right;'>" . mostraValor($valorUnitario) . "</td>
					<td style='text-align: right;'>" . mostraValor($valorTotal) . "</td>
				</tr>
			";
				} else if ($rowProduto['AdXPrQuantidade'] > 0) {
					$html .= "
				<tr>
					<td style='text-align: center;'>" . $cont . "</td>
					<td style='text-align: left;'>" . $rowProduto['ProduNome'] . ": " . $rowProduto['Detalhamento'].
					"<br>Marca: ".$rowProduto['MarcaNome']."</td>
					<td style='text-align: center;'>" . $rowProduto['UnMedSigla'] . "</td>					
					<td style='text-align: center;'>" . $rowProduto['AdXPrQuantidade'] . "</td>
					<td style='text-align: right;'>" . mostraValor($valorUnitario) . "</td>
					<td style='text-align: right'>" . mostraValor($valorTotal) . "</td>
				</tr>
			";
				}

				$cont++;
				$totalGeralProdutos += $valorTotal;
			}

			$html .= "<br>";

			$html .= "  <tr>
	                	<td colspan='5' height='50' valign='middle'>
		                    <strong>Total Produtos</strong>
	                    </td>
					    <td style='text-align: right' colspan='2'>
					        " . mostraValor($totalGeralProdutos) . "
					    </td>
				    </tr>";
			$html .= "</table>";
		}

		$totalGeralServicos = 0;

		if ($totalServicos > 0) {

			$html .= "<div style='margin-top: 10px; margin-bottom: -20px; text-align: center;'><h4>Serviços Aditivo N°".$aditivo['AditiNumero']."</h4></div>";

			$html .= '
			<table style="width:100%; border-collapse: collapse; margin-top: 20px;">
				<tr>
					<th style="text-align: center; width:8%">Item</th>
					<th style="text-align: left; width:53%">Serviço</th>
					<th style="text-align: center; width:12%">Quant.</th>
					<th style="text-align: center; width:12%">V. Unit.</th>
					<th style="text-align: center; width:15%">V. Total</th>
				</tr>
			';

			$cont = 1;

			foreach ($rowServicos as $rowServico) {

				if ($rowServico['AdXSrValorUnitario'] != '' and $rowServico['AdXSrValorUnitario'] != null) {
					$valorUnitario = $rowServico['AdXSrValorUnitario'];
					$valorTotal = $rowServico['AdXSrQuantidade'] * $rowServico['AdXSrValorUnitario'];
				} else {
					$valorUnitario = 0;
					$valorTotal = 0;
				}

				if ($totalServicos == $cont) {
					if ($rowServico['AdXSrQuantidade'] != NULL && $rowServico['AdXSrQuantidade'] > 0) {
						$html .= "
							<tr>
								<td style='text-align: center;'>" . $cont . "</td>
								<td style='text-align: left;'>" . $rowServico['ServiNome'] . ": " . $rowServico['Detalhamento'].
								"<br>Marca: ".$rowServico['MarcaNome']."</td>
								<td style='text-align: center;'>" . $rowServico['AdXSrQuantidade'] . "</td>
								<td style='text-align: right;'>" . mostraValor($valorUnitario) . "</td>
								<td style='text-align: right;'>" . mostraValor($valorTotal) . "</td>
							</tr>
						";
					}
				} else if ($rowServico['AdXSrQuantidade'] != NULL && $rowServico['AdXSrQuantidade'] > 0) {
					$html .= "
						<tr>
							<td style='text-align: center;'>" . $cont . "</td>
							<td style='text-align: left;'>" . $rowServico['ServiNome'] . ": " . $rowServico['Detalhamento'].
							"<br>Marca: ".$rowServico['MarcaNome']."</td>
							<td style='text-align: center;'>" . $rowServico['AdXSrQuantidade'] . "</td>
							<td style='text-align: right;'>" . mostraValor($valorUnitario) . "</td>
							<td style='text-align: right'>" . mostraValor($valorTotal) . "</td>
						</tr>
					";
				}

				$cont++;
				$totalGeralServicos += $valorTotal;
			}

			$html .= "<br>";

			$html .= "  <tr>
	                	<td colspan='4' height='50' valign='middle'>
		                    <strong>Total Serviços</strong>
	                    </td>
					    <td style='text-align: right' colspan='2'>
					        " . mostraValor($totalGeralServicos) . "
					    </td>
				    </tr>";
			$html .= "</table>";
		}

		$totalGeral = $totalGeralProdutos + $totalGeralServicos;

		$html .= "<table style='width:100%; border-collapse: collapse; margin-top: 20px;'>
	 			<tr>
                	<td colspan='5' height='50' valign='middle' style='width:85%'>
	                    <strong>TOTAL DO ADITIVO</strong>
                    </td>
				    <td style='text-align: right; width:15%'>
				        " . mostraValor($totalGeral) . "
				    </td>
			    </tr>
			  </table>
	";
		/////////////////////////////////
	}

	$rodape = "<hr/>
    <div style='width:100%'>
		<div style='width:300px; float:left; display: inline;'>Sistema Lamparinas</div>
		<div style='width:105px; float:right; display: inline;'>Página {PAGENO} / {nbpg}</div> 
	</div>";

	//$mpdf->SetHTMLHeader($topo,'O',true); //o SetHTMLHeader deve vir antes do WriteHTML para que o cabeçalho apareça em todas as páginas
	$mpdf->SetHTMLFooter($rodape); 	//o SetHTMLFooter deve vir antes do WriteHTML para que o rodapé apareça em todas as páginas
	$mpdf->WriteHTML($html);

	// Other code
	$mpdf->Output(); // Para debugar o mpdf basta colocar ['debug' => true] dentro desse Output().
} catch (\Mpdf\MpdfException $e) { // Note: safer fully qualified exception name used for catch

	// Process the exception, log, print etc.
	$html = $e->getMessage();

	$mpdf->WriteHTML($html);

	// Other code
	$mpdf->Output(['debug' => true]);
}
