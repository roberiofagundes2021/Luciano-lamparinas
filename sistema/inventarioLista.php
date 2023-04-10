<?php

include_once("sessao.php");

include('global_assets/php/conexao.php');

use Mpdf\Mpdf;

require_once 'global_assets/php/vendor/autoload.php';

$iInventario = $_POST['inputInventarioId'];
$sNumero = $_POST['inputInventarioNumero'];

$sql = "SELECT InvenNumero, InvenCategoria, InXLELocal, LcEstNome, InvenUnidade
		FROM Inventario
		JOIN InventarioXLocalEstoque on InXLEInventario = InvenId
		JOIN LocalEstoque on LcEstId = InXLELocal
		Where InvenId = " . $iInventario . "
		";
$result = $conn->query($sql);
$rowLocalEstoque = $result->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT InvenNumero, InvenClassificacao, InvenCategoria, InvenObservacao, InvenUnidade, CategNome, InXSeSetor, SetorNome
		 FROM Inventario
		 JOIN InventarioXSetor on InXSeInventario = InvenId
		 JOIN Setor on SetorId = InXSeSetor
		 LEFT JOIN Categoria on CategId = InvenCategoria
		 Where InvenId = " . $iInventario . "
		";
$result = $conn->query($sql);
$rowSetor = $result->fetchAll(PDO::FETCH_ASSOC);

try {
	$mpdf = new Mpdf([
		'mode' => 'utf-8',
		//'format' => [190, 236], 
		'format' => 'A4-L',
		'default_font_size' => 10,
		'default_font' => 'dejavusans',
		'orientation' => 'L'
	]);

	$html = "
	<div style='position: relative; width:100%; border-bottom: 1px solid #000;'>
		<div style='width:300px; float:left; display: inline;'>
			<img src='global_assets/images/empresas/".$_SESSION['EmpreFoto']."' style='width:60px; height:60px; float:left; margin-right: 10px; margin-top:-10px;' />		
			<span style='font-weight:bold;line-height:200px;'>" . $_SESSION['EmpreNomeFantasia'] . "</span><br>
			<div style='position: absolute; font-size:12px; margin-top: 8px; margin-left:4px;'>Unidade: " . $_SESSION['UnidadeNome'] . "</div>
		</div>
		<div style='width:150px; float:right; display: inline; text-align:right;'>
			<div>".date('d/m/Y')."</div>
			<div style='margin-top:8px;'>Inventário: " . formatarNumero($sNumero) . "</div>
		</div> 
	 </div>
	";

	foreach ($rowLocalEstoque as $item) {

		$iCategoria = $item['InvenCategoria'];
		$iLocal = $item['InXLELocal'];

		$sql = "SELECT Distinct ProduCodigo, ProduNome, UnMedSigla, CategNome, ProduCustoFinal,
				dbo.fnSaldoEstoque(" . $item['InvenUnidade'] . ", ProduId, 'P', MovimDestinoLocal) as Saldo, 
				LcEstNome
				FROM Produto
				LEFT JOIN Categoria on CategId = ProduCategoria
				JOIN UnidadeMedida on UnMedId = ProduUnidadeMedida
				JOIN MovimentacaoXProduto on MvXPrProduto = ProduId
				JOIN Movimentacao on MovimId = MvXPrMovimentacao
				LEFT JOIN LocalEstoque on LcEstId = MovimDestinoLocal
				LEFT JOIN Setor on SetorId = MovimDestinoSetor
				JOIN Situacao on SituaId = MovimSituacao
				WHERE MovimUnidade = " . $item['InvenUnidade'] . " and ProduStatus = 1 and
					  MovimDestinoLocal = (" . $iLocal . ") and SituaChave in ('LIBERADO', 'LIBERADOCONTABILIDADE')
				 ";
		if ($iCategoria){
			$sql .= " and ProduCategoria = " . $iCategoria;
		}
		$result = $conn->query($sql);
		$rowProdutos = $result->fetchAll(PDO::FETCH_ASSOC);
		$count = count($rowProdutos);

		if ($count){
			$html .= '
			<br>
			<div style="font-weight: bold; position:relative; margin-top: 50px; background-color:#ccc; padding: 5px;">Local: ' . $item['LcEstNome'] . '</div>
			<br>
			<table style="width:100%; border-collapse: collapse;">
				<tr>
					<th style="text-align: left; border-bottom: 1px solid #333; padding-top: 7px; padding-bottom: 7px; width:10%">Patrimônio</th>
					<th style="text-align: left; border-bottom: 1px solid #333; padding-top: 7px; padding-bottom: 7px; width:30%">Produto</th>
					<th style="text-align: left; border-bottom: 1px solid #333; padding-top: 7px; padding-bottom: 7px; width:8%">Unidade</th>
					<th style="text-align: left; border-bottom: 1px solid #333; padding-top: 7px; padding-bottom: 7px; width:28%">Categoria</th>
					<th style="text-align: left; border-bottom: 1px solid #333; padding-top: 7px; padding-bottom: 7px; width:12%">1ª Contagem</th>
					<th style="text-align: left; border-bottom: 1px solid #333; padding-top: 7px; padding-bottom: 7px; width:1%"></th>
					<th style="text-align: left; border-bottom: 1px solid #333; padding-top: 7px; padding-bottom: 7px; width:11%">2ª Contagem</th>
				</tr>
			';
	
			foreach ($rowProdutos as $itemProduto) {
	
				$html .= "
					<tr>
						<td style='padding-top: 8px;'></td>
						<td style='padding-top: 8px;'>" . $itemProduto['ProduNome'] . "</td>
						<td style='padding-top: 8px;'>" . $itemProduto['UnMedSigla'] . "</td>
						<td style='padding-top: 8px;'>" . $itemProduto['CategNome'] . "</td>
						<td style='padding-top: 8px; border-bottom: 1px solid #666;'></td>
						<td></td>
						<td style='padding-top: 8px; border-bottom: 1px solid #666;'></td>
					</tr>
				";
			}
	
			$html .= "</table>";	
		}
	}

	foreach ($rowSetor as $item) {

		$iCategoria = $item['InvenCategoria'];
		$iClassificacao = $item['InvenClassificacao'];
		$iSetor = $item['InXSeSetor'];

		$sql = "SELECT ProduCodigo, ProduNome, UnMedSigla, CategNome, ProduCustoFinal, PatriNumero, 
				dbo.fnSaldoEstoque(" . $item['InvenUnidade'] . ", ProduId, 'P', MovimDestinoLocal) as Saldo, 
				LcEstNome
				FROM Produto
				LEFT JOIN Categoria on CategId = ProduCategoria
				JOIN UnidadeMedida on UnMedId = ProduUnidadeMedida
				JOIN MovimentacaoXProduto on MvXPrProduto = ProduId
				LEFT JOIN Patrimonio on PatriId = MvXPrPatrimonio
				JOIN Movimentacao on MovimId = MvXPrMovimentacao
				LEFT JOIN LocalEstoque on LcEstId = MovimDestinoLocal
				LEFT JOIN Setor on SetorId = MovimDestinoSetor
				JOIN Situacao on SituaId = MovimSituacao
				WHERE MovimUnidade = " . $item['InvenUnidade'] . " and ProduStatus = 1 and
					  MovimDestinoSetor = $iSetor and SituaChave = 'LIBERADO'
				 ";
		if ($iCategoria){
			$sql .= " and ProduCategoria = " . $iCategoria; 
		}
		
		if ($iClassificacao){
			$sql .= " and MvXPrClassificacao = " . $iClassificacao; 
		}

		$result = $conn->query($sql);
		$rowProdutos = $result->fetchAll(PDO::FETCH_ASSOC);
		$count = count($rowProdutos);

		if ($count){
			$html .= '
			<br>
			<div style="font-weight: bold; position:relative; margin-top: 50px; background-color:#ccc; padding: 5px;">Setor: ' . $item['SetorNome'] . '</div>
			<br>
			<table style="width:100%; border-collapse: collapse;">
				<tr>
					<th style="text-align: left; border-bottom: 1px solid #333; padding-top: 7px; padding-bottom: 7px; width:10%">Patrimônio</th>
					<th style="text-align: left; border-bottom: 1px solid #333; padding-top: 7px; padding-bottom: 7px; width:30%">Produto</th>
					<th style="text-align: left; border-bottom: 1px solid #333; padding-top: 7px; padding-bottom: 7px; width:8%">Unidade</th>
					<th style="text-align: left; border-bottom: 1px solid #333; padding-top: 7px; padding-bottom: 7px; width:28%">Categoria</th>
					<th style="text-align: left; border-bottom: 1px solid #333; padding-top: 7px; padding-bottom: 7px; width:12%">1ª Contagem</th>
					<th style="text-align: left; border-bottom: 1px solid #333; padding-top: 7px; padding-bottom: 7px; width:1%"></th>
					<th style="text-align: left; border-bottom: 1px solid #333; padding-top: 7px; padding-bottom: 7px; width:11%">2ª Contagem</th>
				</tr>
			';
	
			foreach ($rowProdutos as $itemProduto) {
	
				$html .= "
					<tr>
						<td style='padding-top: 8px;'>" . formatarNumero($itemProduto['PatriNumero']) . "</td>
						<td style='padding-top: 8px;'>" . $itemProduto['ProduNome'] . "</td>
						<td style='padding-top: 8px;'>" . $itemProduto['UnMedSigla'] . "</td>
						<td style='padding-top: 8px;'>" . $itemProduto['CategNome'] . "</td>
						<td style='padding-top: 8px; border-bottom: 1px solid #666;'></td>
						<td></td>
						<td style='padding-top: 8px; border-bottom: 1px solid #666;'></td>
					</tr>
				";
			}
	
			$html .= "</table>";	
		}
	}

	$html .= '			
		<br><br>
		<div style="width: 100%; margin-top: 200px;">
			<div style="position: relative; width: 250px; border-top: 1px solid #333; padding-top:10px; float: left; text-align: center;">Responsável</div>
			<div style="position: relative; width: 250px; border-top: 1px solid #333; padding-top:10px; float: left; text-align: center; margin-left: 100px;">Membro 1</div>
			<div style="position: relative; width: 250px; border-top: 1px solid #333; padding-top:10px; float: left; text-align: center; margin-left: 100px;">Membro 2</div>
		</div>
	';

	$rodape = "<hr/>
    <div style='width:100%'>
		<div style='width:300px; float:left; display: inline;'>Sistema Lamparinas</div>
		<div style='width:105px; float:right; display: inline;'>Página {PAGENO} / {nbpg}</div> 
	</div>";

	//$mpdf->SetHTMLHeader($topo, 'O', true);
    $mpdf->SetHTMLFooter($rodape); 	//o SetHTMLFooter deve vir antes do WriteHTML para que o rodapé apareça em todas as páginas
    $mpdf->WriteHTML($html);

	// Other code
	$mpdf->Output();
} catch (\Mpdf\MpdfException $e) { // Note: safer fully qualified exception name used for catch

	// Process the exception, log, print etc.
	echo $e->getMessage();
}
