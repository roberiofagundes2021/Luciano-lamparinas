<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

use Mpdf\Mpdf;

require_once 'global_assets/php/vendor/autoload.php';

if(isset($_POST['inputAditivoId'])){
	$iAditivo = $_POST['inputAditivoId'];
} else {
	$iAditivo = $_POST['inputFluxoOperacionalId'];
}

/*$sql = "SELECT *
		FROM Aditivo
		JOIN Fornecedor on ForneId = FlOpeFornecedor
		JOIN Categoria on CategId = FlOpeCategoria
		JOIN SubCategoria on SbCatId = FlOpeSubCategoria
		WHERE FlOpeUnidade = ". $_SESSION['UnidadeId'] ." and FlOpeId = ".$iFluxoOperacional;

$result = $conn->query($sql);
$row = $result->fetch(PDO::FETCH_ASSOC);*/

$sql = "SELECT AditiId, AditiNumero, AditiDtCelebracao, AditiDtInicio, AditiDtFim, AditiValor, 
AditiConteudoInicio, AditiConteudoFim, FlOpeId, FlOpeNumContrato, FlOpeNumProcesso, FlOpeValor, 
FlOpeDataInicio, FlOpeDataFim, CategNome, SbCatNome,
		ForneNome, ForneCelular, ForneEmail
		FROM Aditivo
		JOIN FluxoOperacional on FlOpeId = AditiFluxoOperacional
		JOIN Fornecedor on ForneId = FlOpeFornecedor
		JOIN Categoria on CategId = FlOpeCategoria
		LEFT JOIN SubCategoria on SbCatId = FlOpeSubCategoria
		WHERE AditiUnidade = ". $_SESSION['UnidadeId'] ." and AditiId = ".$iAditivo;
$result = $conn->query($sql);
$row = $result->fetch(PDO::FETCH_ASSOC);

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
			<span style='font-weight:bold;line-height:200px;'>".$_SESSION['EmpreNomeFantasia']."</span><br>
			<div style='position: absolute; font-size:12px; margin-top: 8px; margin-left:4px;'>Unidade: ".$_SESSION['UnidadeNome']."</div>
		</div>
		<div style='width:250px; float:right; display: inline; text-align:right;'>
			<div>".date('d/m/Y')."</div>
			<div style='margin-top:8px;'>".$fluxo.": ".$row['FlOpeNumContrato']."</div>
		</div> 
	</div>";
	
	$html .= "<div style='text-align:center; margin-top: 20px;'><h1>".$fluxo."/ADITIVO</h1></div>";

	$html .= "<h3>".$fluxo."</h3>";
	
	$html .= '
    <table style="width:100%; border-collapse: collapse;">
        <tr style="background-color:#F1F1F1;">
            <td style="width:25%; font-size:12px;">Nº Ata Registro:<br>'. $row['FlOpeNumContrato'].'</td>
            <td style="width:25%; font-size:12px;">Nº Processo:<br>'. $row['FlOpeNumProcesso'].'</td>
            <td style="width:20%; font-size:12px;">Valor:<br>'. mostraValor($row['FlOpeValor']).'</td>
            <td style="width:15%; font-size:12px;">Início:<br>'. mostraData($row['FlOpeDataInicio']).'</td>
            <td style="width:15%; font-size:12px;">Fim:<br>'. mostraData($row['FlOpeDataFim']).'</td>
        </tr>
	</table>
	<table style="width:100%; border-collapse: collapse;">
        <tr>
            <td style="font-size:12px; width: 50%">Categoria:<br>'.$row['CategNome'].'</td>
            <td style="font-size:12px; width: 50%">Sub Categoria:<br>'.$row['SbCatNome'].'</td>
        </tr>
	</table>
	<table style="width:100%; border-collapse: collapse;">
        <tr>
            <td style="width:45%; font-size:12px;">Fornecedor:<br>'.$row['ForneNome'].'</td>
            <td style="width:25%; font-size:12px;">Telefone:<br>'.$row['ForneCelular'].'</td>
            <td style="width:30%; font-size:12px;">E-mail:<br>'.$row['ForneEmail'].'</td>
        </tr>
    </table>
	<br>';

	$html .= '
	    <h3>ADITIVO</h3>
	';
	
	$html .= '
    <table style="width:100%; border-collapse: collapse;">
        <tr style="background-color:#F1F1F1;">
            <td style="width:20%; font-size:10px;">Nº Aditivo: '. $row['AditiNumero'].'</td>
            <td style="width:30%; font-size:10px;">Data de Celebração: '. mostraData($row['AditiDtCelebracao']).'</td>
            <td style="width:20%; font-size:10px;">Valor: '. mostraValor($row['AditiValor']).'</td>
            <td style="width:15%; font-size:10px;">Início: '. mostraData($row['AditiDtInicio']).'</td>
            <td style="width:15%; font-size:10px;">Fim: '. mostraData($row['AditiDtFim']).'</td>
        </tr>
    </table>
	<br>';
	
	$sql = "SELECT ProduId, ProduNome, AdXPrDetalhamento, UnMedSigla, AdXPrQuantidade, AdXPrValorUnitario,
			MarcaNome, ModelNome, FabriNome
			FROM Produto
			JOIN AditivoXProduto on AdXPrProduto = ProduId
			JOIN UnidadeMedida on UnMedId = ProduUnidadeMedida
			LEFT JOIN ProdutoXFabricante on PrXFaProduto = ProduId and PrXFaFluxoOperacional = $row[FlOpeId]
			LEFT JOIN Marca on MarcaId = PrXFaMarca
			LEFT JOIN Modelo on ModelId = PrXFaModelo
			LEFT JOIN Fabricante on FabriId = PrXFaFabricante
			WHERE ProduEmpresa = ".$_SESSION['EmpreId']." and AdXPrAditivo = ".$_POST['inputAditivoId']." and AdXPrQuantidade > ' 0 ' ";

	$result = $conn->query($sql);
	$rowProdutos = $result->fetchAll(PDO::FETCH_ASSOC);
	$totalProdutos = count($rowProdutos);

	$sql = "SELECT ServiId, ServiNome, AdXSrDetalhamento, AdXSrQuantidade, AdXSrValorUnitario,
			MarcaNome, ModelNome, FabriNome
			FROM Servico
			JOIN AditivoXServico on AdXSrServico = ServiId
			LEFT JOIN ServicoXFabricante on SrXFaServico = ServiId and SrXFaFluxoOperacional = $row[FlOpeId]
			LEFT JOIN Marca on MarcaId = SrXFaMarca
			LEFT JOIN Modelo on ModelId = SrXFaModelo
			LEFT JOIN Fabricante on FabriId = SrXFaFabricante
			WHERE ServiEmpresa = ".$_SESSION['EmpreId']." and AdXSrAditivo = ".$_POST['inputAditivoId']." and AdXSrQuantidade > ' 0 ' ";
	$result = $conn->query($sql);
	$rowServicos = $result->fetchAll(PDO::FETCH_ASSOC);
	$totalServicos = count($rowServicos);

	$html .= '
	<div>' . $row['AditiConteudoInicio'] . '</div>
	<br>';

	$totalGeralProdutos = 0;
	
	if($totalProdutos > 0){

		$html .= "<div style='text-align:center; margin-top: 20px;'><h2>PRODUTOS ADITIVADOS</h2></div>";

		$html .= '
		<table style="width:100%; border-collapse: collapse;">
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
		
		foreach ($rowProdutos as $rowProduto){
			
			if ($rowProduto['AdXPrValorUnitario'] != '' and $rowProduto['AdXPrValorUnitario'] != null){
				$valorUnitario = $rowProduto['AdXPrValorUnitario'];
				$valorTotal = $rowProduto['AdXPrQuantidade'] * $rowProduto['AdXPrValorUnitario'];
			} else {
				$valorUnitario = 0;
				$valorTotal = 0;
			}

			$MarcaModeloFabricante = '';

			$MarcaModeloFabricante .= $rowProduto['MarcaNome']?'<br>MARCA: '.$rowProduto['MarcaNome']:'';
			$MarcaModeloFabricante .= $rowProduto['ModelNome']?'<br>MODELO: '.$rowProduto['ModelNome']:'';
			$MarcaModeloFabricante .= $rowProduto['FabriNome']?'<br>FABRICANTE: '.$rowProduto['FabriNome']:'';

			$detalhamento = $rowProduto['FOXPrDetalhamento']?' : '.$rowProduto['FOXPrDetalhamento']:'';

			
				$html .= "
				<tr>
					<td style='text-align: center;'>".$cont."</td>
					<td style='text-align: left;'>".$rowProduto['ProduNome']."$detalhamento $MarcaModeloFabricante</td>
					<td style='text-align: center;'>".$rowProduto['UnMedSigla']."</td>
					<td style='text-align: center;'>".$rowProduto['AdXPrQuantidade']."</td>
					<td style='text-align: right;'>".mostraValor($valorUnitario)."</td>
					<td style='text-align: right;'>".mostraValor($valorTotal)."</td>
				</tr>
			";
			
				
			
			
			$cont++;
			$totalGeralProdutos += $valorTotal;
		}

		$html .= "<br>";
		
		$html .= "  <tr>
	                	<td colspan='5' height='50' valign='middle'>
		                    <strong>Total Produtos</strong>
	                    </td>
					    <td style='text-align: right' colspan='2'>
					        ".mostraValor($totalGeralProdutos)."
					    </td>
				    </tr>";
		$html .= "</table>";
	}

	$totalGeralServicos = 0;
	
	if($totalServicos > 0){

		$html .= "<div style='text-align:center; margin-top: 20px;'><h2>SERVIÇOS ADITIVADOS</h2></div>";

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
		
		foreach ($rowServicos as $rowServico){
			
			if ($rowServico['AdXSrValorUnitario'] != '' and $rowServico['AdXSrValorUnitario'] != null){
				$valorUnitario = $rowServico['AdXSrValorUnitario'];
				$valorTotal = $rowServico['AdXSrQuantidade'] * $rowServico['AdXSrValorUnitario'];
			} else {
				$valorUnitario = "";
				$valorTotal = "";
			}

			$MarcaModeloFabricante = '';

			$MarcaModeloFabricante .= $rowServico['MarcaNome']?'<br>MARCA: '.$rowServico['MarcaNome']:'';
			$MarcaModeloFabricante .= $rowServico['ModelNome']?'<br>MODELO: '.$rowServico['ModelNome']:'';
			$MarcaModeloFabricante .= $rowServico['FabriNome']?'<br>FABRICANTE: '.$rowServico['FabriNome']:'';

			$detalhamento = $rowServico['AdXSrDetalhamento']?' : '.$rowServico['AdXSrDetalhamento']:'';

			
				$html .= "
				<tr>
					<td style='text-align: center;'>".$cont."</td>
					<td style='text-align: left;'>".$rowServico['ServiNome']."$detalhamento $MarcaModeloFabricante</td>	
					<td style='text-align: center;'>".$rowServico['AdXSrQuantidade']."</td>
					<td style='text-align: right;'>".mostraValor($valorUnitario)."</td>
					<td style='text-align: right;'>".mostraValor($valorTotal)."</td>
				</tr>
			";
			
			$cont++;
			$totalGeralServicos += $valorTotal;
		}

		$html .= "<br>";
		
		$html .= "  <tr>
	                	<td colspan='4' height='50' valign='middle'>
		                    <strong>Total Serviços</strong>
	                    </td>
					    <td style='text-align: right' colspan='2'>
					        ".mostraValor($totalGeralServicos)."
					    </td>
				    </tr>";
		$html .= "</table>";
	}

	$totalGeral = $totalGeralProdutos + $totalGeralServicos;

	$html .= "<table style='width:100%; border-collapse: collapse; margin-top: 20px;'>
	 			<tr>
                	<td colspan='5' height='50' valign='middle' style='width:85%'>
	                    <strong>TOTAL GERAL</strong>
                    </td>
				    <td style='text-align: right; width:15%'>
				        ".mostraValor($totalGeral)."
				    </td>
			    </tr>
			  </table>
	";

	$html .= '
	<br><br>
	<div>' . $row['AditiConteudoFim'] . '</div>
	<br>';
			
    $rodape = "<hr/>
    <div style='width:100%'>
		<div style='width:300px; float:left; display: inline;'>Sistema Lamparinas</div>
		<div style='width:105px; float:right; display: inline;'>Página {PAGENO} / {nbpg}</div> 
	</div>";
    
    //$mpdf->SetHTMLHeader($topo,'O',true); //o SetHTMLHeader deve vir antes do WriteHTML para que o cabeçalho apareça em todas as páginas
    $mpdf->SetHTMLFooter($rodape); 	//o SetHTMLFooter deve vir antes do WriteHTML para que o rodapé apareça em todas as páginas
    $mpdf->WriteHTML($html);
    
    // Other code
    $mpdf->Output();
    
} catch (\Mpdf\MpdfException $e) { // Note: safer fully qualified exception name used for catch
    
    // Process the exception, log, print etc.
    $html = $e->getMessage();
	
    $mpdf->WriteHTML($html);
    
    // Other code
    $mpdf->Output();	
}
