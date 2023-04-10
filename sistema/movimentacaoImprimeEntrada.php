<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

use Mpdf\Mpdf;

require_once 'global_assets/php/vendor/autoload.php';

if (isset($_POST['inputMovimentacaoId'])){
	$iMovimentacao = $_POST['inputMovimentacaoId'];
} else{
	print('<script>
				window.close();
		   </script> ');
}

$sql = "SELECT ForneNome, ForneCelular, ForneEmail, MovimTipo, MovimData, MovimNotaFiscal, MovimObservacao, UsuarNome, OrComNumero,
		dbo.fnValorTotalOrdemCompra(" . $_SESSION['UnidadeId'] . ", MovimOrdemCompra) as TotalOrdemCompra, SetorNome		
        FROM Movimentacao
		JOIN Fornecedor on ForneId = MovimFornecedor
		JOIN OrdemCompra on OrComId = MovimOrdemCompra
		JOIN Usuario on UsuarId = MovimUsuarioAtualizador
		JOIN EmpresaXUsuarioXPerfil on EXUXPUsuario = UsuarId
		JOIN UsuarioXUnidade on UsXUnEmpresaUsuarioPerfil = EXUXPId
		JOIN Setor on SetorId = UsXUnSetor
		WHERE MovimUnidade = ". $_SESSION['UnidadeId'] ." and MovimId = ".$iMovimentacao." and MovimTipo = 'E'";
$result = $conn->query($sql);
$row = $result->fetch(PDO::FETCH_ASSOC);

$sql = "SELECT MvLiqMovimentacao, MvLiqData, MvLiqUsuario,UsuarNome
		FROM MovimentacaoLiquidacao
		JOIN Usuario on UsuarId = MvLiqUsuario
		WHERE MvLiqUnidade = ". $_SESSION['UnidadeId'] ." and MvLiqMovimentacao = ".$iMovimentacao."
		ORDER BY MvLiqUsuario ASC";
$result = $conn->query($sql);
$rowLiquida = $result->fetch(PDO::FETCH_ASSOC);	

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
		<div style='width:470px; float:left; display: inline;'>
			<img src='global_assets/images/empresas/".$_SESSION['EmpreFoto']."' style='width:60px; height:60px; float:left; margin-right: 10px; margin-top:-10px;' />		
			<span style='font-weight:bold;line-height:200px;'>".$_SESSION['EmpreNomeFantasia']."</span><br>
			<div style='position: absolute; font-size:12px; margin-top: 8px; margin-left:4px;'>Unidade: ".$_SESSION['UnidadeNome']."</div>
		</div>
		<div style='width:130px; float:right; display: inline; text-align:right;'>
			<div>".date('d/m/Y')."</div>
			<div style='margin-top:8px;'>Nota Fiscal: ".$row['MovimNotaFiscal']."</div>
		</div> 
	</div>

	<div style='text-align:center; margin-top: 20px;'><h1>ENTRADA DE PRODUTOS/SERVIÇOS</h1></div>
	";
	
    $sql = "SELECT DISTINCT ProduId, ProduNome, MvXPrDetalhamento, UnMedSigla, MvXPrQuantidade, MvXPrValorUnitario,
			MarcaNome as Marca, MvXPrLote, MvXPrValidade
            FROM Produto
            JOIN MovimentacaoXProduto on MvXPrProduto = ProduId
            JOIN UnidadeMedida on UnMedId = ProduUnidadeMedida
            JOIN Movimentacao on MovimId = MvXPrMovimentacao
            JOIN OrdemCompra on OrComId = MovimOrdemCompra
            JOIN FluxoOperacional on FlOpeId = OrComFluxoOperacional
            JOIN ProdutoXFabricante on PrXFaFluxoOperacional = FlOpeId
            LEFT JOIN Marca on MarcaId = PrXFaMarca
            WHERE ProduEmpresa = ".$_SESSION['EmpreId']." and MvXPrMovimentacao = ".$iMovimentacao." and MvXPrQuantidade <> 0";
    $result = $conn->query($sql);
    $rowProdutos = $result->fetchAll(PDO::FETCH_ASSOC);
    $totalProdutos = count($rowProdutos);

    $sql = "SELECT DISTINCT ServiId, ServiNome, MvXSrDetalhamento, MvXSrQuantidade, MvXSrValorUnitario,
			MarcaNome as Marca
            FROM Servico
            JOIN MovimentacaoXServico on MvXSrServico = ServiId
			JOIN Movimentacao on MovimId = MvXSrMovimentacao
            JOIN OrdemCompra on OrComId = MovimOrdemCompra
            JOIN FluxoOperacional on FlOpeId = OrComFluxoOperacional
            JOIN ServicoXFabricante on SrXFaFluxoOperacional = FlOpeId
			LEFT JOIN Marca on MarcaId = SrXFaMarca
            WHERE ServiEmpresa = ".$_SESSION['EmpreId']." and MvXSrMovimentacao = ".$iMovimentacao." and MvXSrQuantidade <> 0";
    $result = $conn->query($sql);
    $rowServicos = $result->fetchAll(PDO::FETCH_ASSOC);
    $totalServicos = count($rowServicos);
		
	$html .= '
	<table style="width:100%; border-collapse: collapse;">
		<tr style="background-color:#F1F1F1;">
			<td style="width:40%; font-size:12px;">Nº Ordem Compra / Carta Contrato:<br>'. $row['OrComNumero'].'</td>
			<td style="width:20%; font-size:12px;">Valor:<br>'. mostraValor($row['TotalOrdemCompra']).'</td>
			<td style="width:20%; font-size:12px;">Nº Nota Fiscal:<br>'. $row['MovimNotaFiscal'].'</td>
			<td style="width:20%; font-size:12px;">Data:<br>'. mostraData($row['MovimData']).'</td>
		</tr>
	</table>
	<table style="width:100%; border-collapse: collapse;">
		<tr>
			<td style="width:40%; font-size:12px;">Data da Liquidação:<br>'. mostraData($rowLiquida['MvLiqData']).'</td>
			<td style="width:60%; font-size:12px;">Liquidado por:<br>'. $rowLiquida['UsuarNome'].'</td>
		</tr>
	</table>
	<table style="width:100%; border-collapse: collapse;">
		<tr>
			<td style="width:40%; font-size:12px;">Fornecedor:<br>'. $row['ForneNome'].'</td>
			<td style="width:20%; font-size:12px;">Telefone:<br>'. $row['ForneCelular'].'</td>
			<td style="width:40%; font-size:12px;">E-mail:<br>'. $row['ForneEmail'].'</td>
		</tr>
	</table>
	<table style="width:100%; border-collapse: collapse;">
		<tr>
			<td style="width:100%; font-size:12px;">Observação: '.$row['MovimObservacao'].'</td>
		</tr>
	</table>
	
	
	
	';

	$totalGeralProdutos = 0;
		
	if ($totalProdutos > 0){

		$html .= "<div style='text-align:center; margin-top: 20px;'><h2>PRODUTOS</h2></div>";

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
		//var_dump($rowProdutos);exit;
		foreach ($rowProdutos as $itemProduto){

			if ($itemProduto['MvXPrQuantidade'] != '' and $itemProduto['MvXPrQuantidade'] != null and $itemProduto['MvXPrQuantidade'] != 0){
				
				$valorUnitario = $itemProduto['MvXPrValorUnitario'];                    
				$valorTotal = $itemProduto['MvXPrQuantidade'] * $itemProduto['MvXPrValorUnitario'];
				
				$html .= "
					<tr>
						<td style='text-align: center;'>".$cont."</td>
						<td style='text-align: left;'>".$itemProduto['ProduNome'].": ".$itemProduto['MvXPrDetalhamento']
						.($itemProduto['Marca']!=''?"<br>Marca: ".$itemProduto['Marca']:'')
						.($itemProduto['MvXPrLote']!=''?"<br>Lote: ".$itemProduto['MvXPrLote']:'')
						.($itemProduto['MvXPrValidade']!=''?"<br>Validade: ".mostraData($itemProduto['MvXPrValidade']):'').
						"</td>
						<td style='text-align: center;'>".$itemProduto['UnMedSigla']."</td>					
						<td style='text-align: center;'>".$itemProduto['MvXPrQuantidade']."</td>
						<td style='text-align: right;'>".mostraValor($valorUnitario)."</td>
						<td style='text-align: right;'>".mostraValor($valorTotal)."</td>
					</tr>
				";
				
				$cont++;
				$totalGeralProdutos += $valorTotal;
			}
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

		$html .= "<div style='text-align:center; margin-top: 20px;'><h2>SERVIÇOS</h2></div>";

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
		
		foreach ($rowServicos as $itemServico){
			
			if ($itemServico['MvXSrQuantidade'] != '' and $itemServico['MvXSrQuantidade'] != null and $itemServico['MvXSrQuantidade'] != 0){
				
				$valorUnitario = $itemServico['MvXSrValorUnitario'];
				$valorTotal = $itemServico['MvXSrQuantidade'] * $itemServico['MvXSrValorUnitario'];

				$html .= "
					<tr>
						<td style='text-align: center;'>".$cont."</td>
						<td style='text-align: left;'>".$itemServico['ServiNome'].": ".$itemServico['MvXSrDetalhamento']
						.($itemServico['Marca']!=''?"<br>Marca: ".$itemServico['Marca']:'').
						"</td>
						<td style='text-align: center;'>".$itemServico['MvXSrQuantidade']."</td>
						<td style='text-align: right;'>".mostraValor($valorUnitario)."</td>
						<td style='text-align: right;'>".mostraValor($valorTotal)."</td>
					</tr>
				";
				
				$cont++;
				$totalGeralServicos += $valorTotal;
			}
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
		<div style="width: 100%; margin-top: 100px;">
			<div style="position: relative; float: left; text-align: center;">
				<div style="position: relative; width: 250px; border-top: 1px solid #333; padding-top:10px; float: left; text-align: center; margin-left: 220px;">'.$row['UsuarNome'].'</div>
				'.$row['SetorNome'].'<br>
			</div>
		</div>
	';	
	
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
