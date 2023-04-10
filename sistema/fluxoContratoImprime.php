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

//Pega os dados principais do contrato
$sql = "SELECT FlOpeNumContrato, FlOpeNumProcesso, FlOpeValor, FlOpeDataInicio, FlOpeDataFim, 
		FlOpeConteudoInicio, FlOpeConteudoFim, CategNome, ForneNome, ForneCelular, ForneEmail
		FROM FluxoOperacional
		JOIN Fornecedor on ForneId = FlOpeFornecedor
		JOIN Categoria on CategId = FlOpeCategoria
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

//Verifica se o contrato tem produtos
$sql = "SELECT ProduId
		FROM Produto
		JOIN FluxoOperacionalXProduto on FOXPrProduto = ProduId
		WHERE ProduEmpresa = " . $_SESSION['EmpreId'] . " and FOXPrFluxoOperacional = " . $iFluxoOperacional;	
$result = $conn->query($sql);
$rowProd = $result->fetchAll(PDO::FETCH_ASSOC);
$totalProdutos = count($rowProd);

//Verifica se o contrato tem serviços
$sql = "SELECT ServiId
		FROM Servico
		JOIN FluxoOperacionalXServico on FOXSrServico = ServiId
		WHERE ServiEmpresa = " . $_SESSION['EmpreId'] . " and FOXSrFluxoOperacional = " . $iFluxoOperacional;
$result = $conn->query($sql);
$rowServ = $result->fetchAll(PDO::FETCH_ASSOC);
$totalServicos = count($rowServ);

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
			<div style='margin-top:8px;'>Contrato: " . $row['FlOpeNumContrato'] . "</div>
		</div> 
	</div>

	<!-- <div style='text-align:center; margin-top: 20px;'><h1>Licitação - Contrato</h1></div> --->
	<div style='text-align:center; margin-top: 20px;'><h1>Contrato</h1></div>
    ";
    
    $html .= '
	<div>' . $row['FlOpeConteudoInicio'] . '</div>
	<br>';

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
					JOIN ProdutoXFabricante ON PrXFaProduto = FOXPrProduto and PrXFaFluxoOperacional = FOXPrFluxoOperacional
					JOIN FluxoOperacional on FlOpeId = PrXFaFluxoOperacional
					JOIN UnidadeMedida on UnMedId = ProduUnidadeMedida
					JOIN SubCategoria on SbCatId = ProduSubCategoria
					JOIN Marca on MarcaId = PrXFaMarca
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
		}else{
			foreach ($rowSubCategoria as $sbcat) {
				$totalProdutos = 0;
		
				$sql = "SELECT ProduId, ProduNome, FOXPrDetalhamento as Detalhamento, UnMedSigla, FOXPrQuantidade, FOXPrValorUnitario, MarcaNome
						FROM Produto
						JOIN FluxoOperacionalXProduto on FOXPrProduto = ProduId
						JOIN ProdutoXFabricante ON PrXFaProduto = FOXPrProduto and PrXFaFluxoOperacional = FOXPrFluxoOperacional
						JOIN FluxoOperacional on FlOpeId = PrXFaFluxoOperacional
						JOIN UnidadeMedida on UnMedId = ProduUnidadeMedida
						JOIN SubCategoria on SbCatId = ProduSubCategoria
						JOIN Marca on MarcaId = PrXFaMarca
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
		
			$sql = "SELECT ServiId, ServiNome, FOXSrDetalhamento as Detalhamento, FOXSrQuantidade, FOXSrValorUnitario
					FROM Servico
					JOIN FluxoOperacionalXServico on FOXSrServico = ServiId
					JOIN SubCategoria on SbCatId = ServiSubCategoria
					WHERE ServiEmpresa = " . $_SESSION['EmpreId'] . " and FOXSrFluxoOperacional = " . $iFluxoOperacional."
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
						<th style="text-align: left; width:50%">Serviço</th>
						<th style="text-align: center; width:12%">Quant.</th>
						<th style="text-align: center; width:15%">V. Unit.</th>
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
							<td style='text-align: left;'>" . $rowServico['ServiNome'] . ": " . $rowServico['Detalhamento'] . "</td>	
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
		}else{
			foreach ($rowSubCategoria as $sbcat) {
	
				$totalServicos = 0;
		
				$sql = "SELECT ServiId, ServiNome, FOXSrDetalhamento as Detalhamento, FOXSrQuantidade, FOXSrValorUnitario
						FROM Servico
						JOIN FluxoOperacionalXServico on FOXSrServico = ServiId
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
							<th style="text-align: left; width:50%">Serviço</th>
							<th style="text-align: center; width:12%">Quant.</th>
							<th style="text-align: center; width:15%">V. Unit.</th>
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
								<td style='text-align: left;'>" . $rowServico['ServiNome'] . ": " . $rowServico['Detalhamento'] . "</td>	
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
   
    $html .= '
	<br><br>
	<div>' . $row['FlOpeConteudoFim'] . '</div>
	<br>';

	$rodape = "<hr/>
    <div style='width:100%'>
		<div style='width:300px; float:left; display: inline;'>Sistema Lamparinas</div>
		<div style='width:105px; float:right; display: inline;'>Página {PAGENO} / {nbpg}</div> 
	</div>";

	//$mpdf->SetHTMLHeader($topo,'O',true); //o SetHTMLHeader deve vir antes do WriteHTML para que o cabeçalho apareça em todas as páginas
	$mpdf->SetHTMLFooter($rodape); 	//o SetHTMLFooter deve vir antes do WriteHTML para que o rodapé apareça em todas as páginas

} catch (\Mpdf\MpdfException $e) { // Note: safer fully qualified exception name used for catch

	// Process the exception, log, print etc.
	$html = $e->getMessage();
}

$mpdf->WriteHTML($html);

// Other code
$mpdf->Output();