<?php

include_once("sessao.php");

include('global_assets/php/conexao.php');
require_once 'global_assets/php/vendor/autoload.php';

use Mpdf\Mpdf;


if (isset($_POST['inputTRId'])){
	$iTR = $_POST['inputTRId'];
} else if (isset($_POST['inputTermoReferenciaId'])) {
	$iTR = $_POST['inputTermoReferenciaId'];
} else{
	print('<script>
				window.close();
		   </script> ');
}

try {

	$sql = "SELECT TrRefNumero, TrRefTipo, TrRefConteudoInicio, TrRefConteudoFim, TrRefTabelaProduto, TrRefTabelaServico, CategNome
			FROM TermoReferencia
			JOIN Categoria ON CategId = TrRefCategoria
		 	WHERE TrRefUnidade = " . $_SESSION['UnidadeId'] . " 
		   	AND TrRefId = " . $iTR;
	$result = $conn->query($sql);
	$row = $result->fetch(PDO::FETCH_ASSOC);

	$sql = "SELECT *
		  	FROM TRXSubcategoria
			JOIN SubCategoria ON SbCatId = TRXSCSubcategoria
		 	WHERE TRXSCUnidade = " . $_SESSION['UnidadeId'] . " 
		 	AND TRXSCTermoReferencia = " . $iTR."
			ORDER BY SbCatNome ASC";
	$result = $conn->query($sql);
	$rowSubCategoria = $result->fetchAll(PDO::FETCH_ASSOC);

	$totalProdutos = 0;
	$totalServicos = 0;
	$totalGeralProdutos = 0;
	$totalGeralServicos = 0;
	$totalGeral = 0;

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
					'orientation' => 'P']);  // L - landscape, P - portrait

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
			<div style='width:300px; float:left; display: inline;'>
				<img src='global_assets/images/empresas/".$_SESSION['EmpreFoto']."' style='width:60px; height:60px; float:left; margin-right: 10px; margin-top:-10px;' />		
				<span style='font-weight:bold;line-height:200px;'>" . $_SESSION['EmpreNomeFantasia']. "</span><br>
				<div style='position: absolute; font-size:12px; margin-top: 8px; margin-left:4px;'>Unidade: ".$_SESSION['UnidadeNome']."</div>
			</div>
			<div style='width:220px; float:right; display: inline; text-align:right;'>
				<div>".date('d/m/Y')."</div>
				<div style='margin-top:8px;'>Termo de Referência: " . formatarNumero($row['TrRefNumero']) . "</div>
			</div> 
			</div>

			<div style='text-align:center; margin-top: 20px;'><h1>TERMO DE REFERÊNCIA</h1></div>
	";

	$html .= '
	<div>' . $row['TrRefConteudoInicio'] . '</div>
	<br>';
	
	//Verifica se o TR é de Produto ou Produto/Serviço
	if ($row['TrRefTipo'] == 'P' || $row['TrRefTipo'] == 'PS'){

		$html .= "<div style='text-align:center; margin-top: 20px;'><h2>PRODUTOS</h2></div>";

		$html .= '
		<div style="font-weight: bold; position:relative; margin-top: 5px; background-color:#ddd; padding: 8px; border: 1px solid #ccc;">
			Categoria: <span style="font-weight:normal;">' . $row['CategNome'] . '</span> 
		</div>
		<br>
		';

		$cont = 1;

		foreach ($rowSubCategoria as $sbcat) {
			
			$totalProdutos = 0;

			//Se foi utilizado ProdutoOrcamento
			if ($row['TrRefTabelaProduto'] == 'ProdutoOrcamento'){
				$sql = "SELECT PrOrcId as Id, PrOrcNome as Nome, PrOrcCategoria as Categoria, PrOrcSubCategoria as SubCategoria,
						TRXPrDetalhamento, UnMedSigla, TRXPrQuantidade, TRXPrValorUnitario
						FROM ProdutoOrcamento
						JOIN TermoReferenciaXProduto on TRXPrProduto = PrOrcId
						JOIN UnidadeMedida on UnMedId = PrOrcUnidadeMedida
						JOIN SubCategoria on SbCatId = PrOrcSubCategoria
						WHERE PrOrcEmpresa = " . $_SESSION['EmpreId'] . " and TRXPrTermoReferencia = " . $iTR." and PrOrcSubCategoria = ".$sbcat['SbCatId']."
						ORDER BY SbCatNome, PrOrcNome ASC";
			} else {
				$sql = "SELECT ProduId as Id, ProduNome as Nome, ProduCategoria as Categoria, ProduSubCategoria as SubCategoria, 
						TRXPrDetalhamento, UnMedSigla, TRXPrQuantidade, TRXPrValorUnitario
						FROM Produto
						JOIN TermoReferenciaXProduto on TRXPrProduto = ProduId
						JOIN UnidadeMedida on UnMedId = ProduUnidadeMedida
						JOIN SubCategoria on SbCatId = ProduSubCategoria
						WHERE ProduEmpresa = ".$_SESSION['EmpreId']." and TRXPrTermoReferencia = " . $iTR." and ProduSubCategoria = ".$sbcat['SbCatId']."
						ORDER BY SbCatNome, ProduNome ASC";
			}
			$result = $conn->query($sql);
			$rowProdutos = $result->fetchAll(PDO::FETCH_ASSOC);
			
			if ($rowProdutos){

				$html .= '
				<div style="font-weight: bold; position:relative; margin-top: 15px; background-color:#eee; padding: 8px; border: 1px solid #ccc;">
					SubCategoria: <span style="font-weight:normal;">' . $sbcat['SbCatNome'] . '</span>
				</div>
				<br> ';					

				$html .= '	
				<table style="width:100%; border-collapse: collapse;">
					<tr>
						<th style="text-align: center; width:8%">Item</th>
						<th style="text-align: left; width:65%">Produto</th>
						<th style="text-align: center; width:12%">Unidade</th>        	
						<th style="text-align: center; width:15%">Quantidade</th>
					</tr>
				';			

				foreach ($rowProdutos as $itemProduto) {

					if ($sbcat['TRXSCSubcategoria'] == $itemProduto['SubCategoria']) {

						$html .= "
							<tr>
								<td style='text-align: center;'>" . $cont . "</td>
								<td style='text-align: left;'>" . $itemProduto['Nome'] . ": " . $itemProduto['TRXPrDetalhamento'] . "</td>
								<td style='text-align: center;'>" . $itemProduto['UnMedSigla'] . "</td>							
								<td style='text-align: center;'>" . $itemProduto['TRXPrQuantidade'] . "</td>
							</tr>
						";

						$cont++;
						$totalProdutos += $itemProduto['TRXPrQuantidade'];						
					}
				}

				$totalGeralProdutos += $totalProdutos;
			
				$html .= "<br>";
				
				$html .= "  <tr>
								<td colspan='3' height='50' valign='middle'>
									<strong>Total Produtos</strong>
								</td>
								<td style='text-align: center' colspan='1'>
									".$totalProdutos."
								</td>
							</tr>";
				$html .= "</table>"; 
			}
		} 
	}

	//Verifica se o TR é de Serviço ou Produto/Serviço
	if ($row['TrRefTipo'] == 'S' || $row['TrRefTipo'] == 'PS'){

		$html .= "<div style='text-align:center; margin-top: 20px;'><h2>SERVIÇOS</h2></div>";

		$html .= '
		<div style="font-weight: bold; position:relative; margin-top: 5px; background-color:#ddd;  padding: 8px;  border: 1px solid #ccc;">
			Categoria: <span style="font-weight:normal;">' . $row['CategNome'] . '</span> 
		</div>
		<br>
		';

		$cont = 1;

		foreach ($rowSubCategoria as $sbcat) {
			
			$totalServicos = 0;

			//Se foi utilizado ServicoOrcamento
			if ($row['TrRefTabelaServico'] == 'ServicoOrcamento'){
				$sql = "SELECT SrOrcId as Id, SrOrcNome as Nome, SrOrcCategoria as Categoria, SrOrcSubCategoria as SubCategoria,
						TRXSrDetalhamento, TRXSrQuantidade
						FROM ServicoOrcamento
						JOIN TermoReferenciaXServico on TRXSrServico = SrOrcId
						JOIN SubCategoria on SbCatId = SrOrcSubCategoria
						WHERE SrOrcEmpresa = " . $_SESSION['EmpreId'] . " and TRXSrTermoReferencia = " . $iTR . " and SrOrcSubCategoria = ".$sbcat['SbCatId']."
						ORDER BY SbCatNome, SrOrcNome ASC";
			} else {
				$sql = "SELECT ServiId as Id, ServiNome as Nome, ServiCategoria as Categoria, ServiSubCategoria as SubCategoria, 
						TRXSrDetalhamento, TRXSrQuantidade
						FROM Servico
						JOIN TermoReferenciaXServico on TRXSrServico = ServiId
						JOIN SubCategoria on SbCatId = ServicoSubCategoria
						WHERE ServiEmpresa = " . $_SESSION['EmpreId'] . " and TRXSrTermoReferencia = " . $iTR . " and ServiSubCategoria = ".$sbcat['SbCatId']."
						ORDER BY SbCatNome, ServicoNome ASC";
			}

			$result = $conn->query($sql);
			$rowServicos = $result->fetchAll(PDO::FETCH_ASSOC);
			
			if ($rowServicos){

				$html .= '
				<div style="font-weight: bold; position:relative; margin-top: 15px; background-color:#eee; padding: 8px; border: 1px solid #ccc;">
					SubCategoria: <span style="font-weight:normal;">' . $sbcat['SbCatNome'] . '</span>
				</div>
				<br> ';				
				
				$html .= '	
				<table style="width:100%; border-collapse: collapse;">
					<tr>
						<th style="text-align: center; width:8%">Item</th>
						<th style="text-align: left; width:77%">Serviço</th>
						<th style="text-align: center; width:15%">Quantidade</th>
					</tr>
				';			

				foreach ($rowServicos as $itemServico) {

					if ($sbcat['TRXSCSubcategoria'] == $itemServico['SubCategoria']) {

						$html .= "
							<tr>
								<td style='text-align: center;'>" . $cont . "</td>
								<td style='text-align: left;'>" . $itemServico['Nome'] . ": " . $itemServico['TRXSrDetalhamento'] . "</td>
								<td style='text-align: center;'>" . $itemServico['TRXSrQuantidade'] . "</td>
							</tr>
						";

						$cont++;
						$totalServicos += $itemServico['TRXSrQuantidade'];
					}
				}
			
				$totalGeralServicos += $totalServicos;

				$html .= "<br>";
				
				$html .= "  <tr>
								<td colspan='2' height='50' valign='middle'>
									<strong>Total Serviços</strong>
								</td>
								<td style='text-align: center' colspan='1'>
									".$totalServicos."
								</td>
							</tr>";
				$html .= "</table>";
			} 
		} 
	}

	$totalGeral = $totalGeralProdutos + $totalGeralServicos;
	//echo $totalGeral;die;

	if($totalGeral){
		$html .= "<table style='width:100%; border-collapse: collapse; margin-top: 20px;'>
					<tr>
						<td colspan='3' height='50' valign='middle' style='width:85%'>
							<strong>TOTAL DE ITENS GERAL</strong>
						</td>
						<td style='text-align: center; width:15%'>
							".$totalGeral."
						</td>
					</tr>
				</table>
		";	
	}

	$html .= '
	<br><br>
	<div>' . $row['TrRefConteudoFim'] . '</div>
	<br>';

	$rodape = "<hr/>
    <div style='width:100%'>
		<div style='width:300px; float:left; display: inline;'>Sistema Lamparinas</div>
		<div style='width:105px; float:right; display: inline;'>Página {PAGENO} / {nbpg}</div> 
	</div>";

	//$mpdf->SetHTMLHeader($topo, '0');
	$mpdf->SetHTMLFooter($rodape);
	$mpdf->WriteHTML($html);	

	// Other code
	$mpdf->Output();
} catch (\Mpdf\MpdfException $e) { // Note: safer fully qualified exception name used for catch

	// Process the exception, log, print etc.
	echo $e->getMessage();
}
