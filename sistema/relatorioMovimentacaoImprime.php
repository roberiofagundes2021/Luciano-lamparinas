<?php

include_once("sessao.php");

include('global_assets/php/conexao.php');

use Mpdf\Mpdf;

require_once 'global_assets/php/vendor/autoload.php';

$sTipoProdutoServico = $_POST['TipoProdutoServico'];
$dDataInicio = $_POST['inputDataDe_imp'];
$dDataFim = $_POST['inputDataAte_imp'];
$iTipo = isset($_POST['cmbTipo_imp']) ? $_POST['cmbTipo_imp'] : 0;
$iFornecedor = isset($_POST['cmbFornecedor_imp']) ? $_POST['cmbFornecedor_imp'] : 0;
$iCategoria = isset($_POST['cmbCategoria_imp']) ? $_POST['cmbCategoria_imp'] : 0;
$iSubCategoria = isset($_POST['cmbSubCategoria_imp']) ? $_POST['cmbSubCategoria_imp'] : 0;
$sCodigo = isset($_POST['cmbCodigo_imp']) ? $_POST['cmbCodigo_imp'] : 0;
$iProduto = isset($_POST['cmbProduto_imp']) ? $_POST['cmbProduto_imp'] : 0;
$iOrigem = isset($_POST['cmbOrigem_imp']) ? $_POST['cmbOrigem_imp'] : 0;
$iDestino = isset($_POST['cmbDestino_imp']) ? $_POST['cmbDestino_imp'] : 0;
$iClassificacao = isset($_POST['cmbClassificacao_imp']) ? $_POST['cmbClassificacao_imp'] : 0;
$iServico = isset($_POST['cmbServico_imp']) ? $_POST['cmbServico_imp'] : 0;

if ($iCategoria != '' and $iCategoria != 0) {

	$sqlNome = "SELECT CategNome
				FROM Categoria
				WHERE CategId = ".$iCategoria;
	$result = $conn->query($sqlNome);
	$rowNome = $result->fetch(PDO::FETCH_ASSOC);		
	$sCategoria = $rowNome['CategNome']; 
}	

if ($iSubCategoria != '' and $iSubCategoria != 0) {
	$sqlNome = "SELECT SbCatNome
				FROM SubCategoria
				WHERE SbCatId = ".$iSubCategoria;
	$result = $conn->query($sqlNome);
	$rowNome = $result->fetch(PDO::FETCH_ASSOC);		
	$sSubCategoria = $rowNome['SbCatNome']; 
}

if ($iProduto != '' and $iProduto != 0) {
	$sqlNome = "SELECT ProduNome
				FROM Produto
				WHERE ProduId = ".$iProduto;
	$result = $conn->query($sqlNome);
	$rowNome = $result->fetch(PDO::FETCH_ASSOC);		
	$sProduto = $rowNome['ProduNome']; 
}	

if ($iServico != '' and $iServico != 0) {
	$sqlNome = "SELECT ServiNome
				FROM Servico 
				WHERE ServiId = ".$iServico;
	$result = $conn->query($sqlNome);
	$rowNome = $result->fetch(PDO::FETCH_ASSOC);		
	$sServico = $rowNome['ServiNome']; 
}	

if ($iFornecedor != '' and $iFornecedor != 0) {
	$sqlNome = "SELECT ForneNome
				FROM Fornecedor
  				WHERE ForneId = ".$iFornecedor;
	$result = $conn->query($sqlNome);
	$rowNome = $result->fetch(PDO::FETCH_ASSOC);		
	$sFornecedor = $rowNome['ForneNome']; 
}

if ($iOrigem != '' and $iOrigem != 0) {

	$aOrigem = explode("#", $iOrigem);

	$sOrigem = $aOrigem[1]; 
}

if ($iDestino != '') {
	
	$aDestino = explode("#", $iDestino);

	$sDestino = $aDestino[1]; 
}	

if ($iClassificacao != '' and $iClassificacao != 0) {

	$sqlNome = "SELECT ClassNome
				FROM Classificacao
				WHERE ClassId = ".$iClassificacao;
	$result = $conn->query($sqlNome);
	$rowNome = $result->fetch(PDO::FETCH_ASSOC);		
	$sClassificacao = $rowNome['ClassNome']; 
}	

if ($sTipoProdutoServico == 'P') {

	$sql = "SELECT MovimData, MovimTipo, 
			CASE 
				WHEN MovimOrigemLocal IS NULL THEN SetorO.SetorNome
			ELSE LocalO.LcEstNome 
			END as Origem,
			CASE 
				WHEN MovimDestinoLocal IS NULL THEN ISNULL(SetorD.SetorNome, MovimDestinoManual)
			ELSE LocalD.LcEstNome
			END as Destino, 
			MovimNotaFiscal, MvXPrQuantidade, MvXPrLote,
	        MvXPrValidade, MvXPrValorUnitario, ProduNome, ForneNome, ClassNome
			FROM Movimentacao
			JOIN MovimentacaoXProduto on MvXPrMovimentacao = MovimId
			JOIN Produto on ProduId = MvXPrProduto
			LEFT JOIN Fornecedor on ForneId = MovimFornecedor
			LEFT JOIN LocalEstoque LocalO on LocalO.LcEstId = MovimOrigemLocal 
			LEFT JOIN LocalEstoque LocalD on LocalD.LcEstId = MovimDestinoLocal 
			LEFT JOIN Setor SetorO on SetorO.SetorId = MovimOrigemSetor 
			LEFT JOIN Setor SetorD on SetorD.SetorId = MovimDestinoSetor 
			LEFT JOIN Classificacao on ClassId = MvXPrClassificacao
			JOIN Situacao on SituaId = MovimSituacao
			Where MovimUnidade = " . $_SESSION['UnidadeId'] . " and MovimData between '" . $dDataInicio . "' and '" . $dDataFim . "' 
			and SituaChave in ('LIBERADO', 'LIBERADOCENTRO', 'AGUARDANDOLIBERACAOCONTABILIDADE', 'LIBERADOCONTABILIDADE') ";

	if ($iTipo != 0) {
		$sql .= " and MovimTipo = $iTipo ";
	}
	
	if ($iCategoria != '' and $iCategoria != 0) {
		$sql .= " and ProduCategoria = $iCategoria ";
	}

	if ($iSubCategoria != '' and $iSubCategoria != 0) {
		$sql .= " and ProduSubCategoria = $iSubCategoria ";
	}

	if ($iProduto != '' and $iProduto != 0) {
		$sql .= " and ProduId = $iProduto ";
	}

	if ($iFornecedor != '' and $iFornecedor != 0) {
		$sql .= " and MovimFornecedor = $iFornecedor ";
	}

	if ($iOrigem != '' and $iOrigem != 0) {

		$aOrigem = explode("#", $iOrigem);

		if ($aOrigem[2] == 'Local'){
			$sql .= " and MovimOrigemLocal = ".$aOrigem[0];
		} else{
			$sql .= " and MovimOrigemSetor = ".$aOrigem[0];
		}
	}

	if ($iDestino != '') {
		
		$aDestino = explode("#", $iDestino);

		if ($aDestino[2] == 'Local'){
			$sql .= " and MovimDestinoLocal = ".$aDestino[0];
		} else if ($aDestino[2] == 'Setor') {
			$sql .= " and MovimDestinoSetor = ".$aDestino[0];
		} else {
			$sql .= " and MovimDestinoManual = '".$aDestino[1]."'";
		}
	}

	if ($iClassificacao != '' and $iClassificacao != 0) {
		$sql .= " and MvXPrClassificacao = $iClassificacao ";
	}

} else {
	$sql = "SELECT MovimData, MovimTipo, 
			CASE 
				WHEN MovimOrigemLocal IS NULL THEN SetorO.SetorNome
			ELSE LocalO.LcEstNome 
			END as Origem,
			CASE 
				WHEN MovimDestinoLocal IS NULL THEN ISNULL(SetorD.SetorNome, MovimDestinoManual)
			ELSE LocalD.LcEstNome
			END as Destino, 
			MovimNotaFiscal, MvXSrQuantidade, MvXSrValorUnitario, ServiNome, ForneNome
		    FROM Movimentacao
		    JOIN MovimentacaoXServico on MvXSrMovimentacao = MovimId
		    JOIN Servico on ServiId = MvXSrServico
		    LEFT JOIN Fornecedor on ForneId = MovimFornecedor
			LEFT JOIN LocalEstoque LocalO on LocalO.LcEstId = MovimOrigemLocal 
			LEFT JOIN LocalEstoque LocalD on LocalD.LcEstId = MovimDestinoLocal 
			LEFT JOIN Setor SetorO on SetorO.SetorId = MovimOrigemSetor 
			LEFT JOIN Setor SetorD on SetorD.SetorId = MovimDestinoSetor
			JOIN Situacao on SituaId = MovimSituacao
		    Where MovimUnidade = " . $_SESSION['UnidadeId'] . " and MovimData between '" . $dDataInicio . "' and '" . $dDataFim . "' 
		    and SituaChave in ('LIBERADO', 'LIBERADOCENTRO', 'AGUARDANDOLIBERACAOCONTABILIDADE', 'LIBERADOCONTABILIDADE') ";

	if ($iCategoria != '' and $iCategoria != 0) {
		$sql .= " and ServiCategoria = $iCategoria ";
	}

	if ($iSubCategoria != '' and $iSubCategoria != 0) {
		$sql .= " and ServiSubCategoria = $iSubCategoria ";
	}

	if ($iServico != '' and $iServico != 0) {
		$sql .= " and ServiId = $iServico ";
	}

	if ($iFornecedor != '' and $iFornecedor != 0) {
		$sql .= " and MovimFornecedor = $iFornecedor ";
	}

	if ($iOrigem != '' and $iOrigem != 0) {

		$aOrigem = explode("#", $iOrigem);

		if ($aOrigem[2] == 'Local'){
			$sql .= " and MovimOrigemLocal = ".$aOrigem[0];
		} else{
			$sql .= " and MovimOrigemSetor = ".$aOrigem[0];
		}
	}

	if ($iDestino != '') {
		
		$aDestino = explode("#", $iDestino);

		if ($aDestino[2] == 'Local'){
			$sql .= " and MovimDestinoLocal = ".$aDestino[0];
		} else if ($aDestino[2] == 'Setor') {
			$sql .= " and MovimDestinoSetor = ".$aDestino[0];
		} else {
			$sql .= " and MovimDestinoManual = '".$aDestino[1]."'";
		}
	}

	
}

$sql .= " Order By MovimData DESC";

//echo $sql;die;

$result = $conn->query($sql);
$row = $result->fetchAll(PDO::FETCH_ASSOC);

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
			<div>&nbsp;</div>
			<div style='margin-top:8px;'>Intervalo: ".mostraData($dDataInicio)." a ".mostraData($dDataFim)."</div>
		</div> 
	</div>

	<div style='text-align:center; margin-top: 20px;'><h1>RELATÓRIO DE MOVIMENTAÇÃO</h1></div>
	";

	$sTipo = $iTipo == 'E' ? 'Entrada' : ($iTipo == 'S' ? 'Saída' : ($iTipo == 'T' ? 'Transferência' : 'Todos'));
	$sFornecedor = $iFornecedor ? $sFornecedor : 'Todos';
	$sCategoria = $iCategoria ? $sCategoria : 'Todos';
	$sSubCategoria = $iSubCategoria ? $sSubCategoria : 'Todos';
	$sCodigo = $sCodigo ? $sCodigo : 'Todos';
	$sProduto = $iProduto ? $sProduto : 'Todos';
	$sServico = $iServico ? $sServico : 'Todos';
	$sOrigem = $iOrigem ? $sOrigem : 'Todos';
	$sDestino = $iDestino ? $sDestino : 'Todos';
	$sClassificacao = $iClassificacao ? $sClassificacao : 'Todos';
	

	$htmlEntrada = '';
	$htmlSaida = '';
	$htmlTransferencia = '';
	$contEntrada = 0;
	$contSaida = 0;
	$contTransferencia = 0;

	$html .= '<br>
			  <br>
			  ';

	if ($iTipo == 'E'){
		$html .= '		
		<table style="width:100%; border-collapse: collapse; font-size:10px;">
		<tr><td colspan="5" style="text-align:center;"><h2>RELAÇÃO DAS ENTRADAS</h2></td></tr>
		<tr><td style="border:none;"></td></tr>
		';
	} else if ($iTipo == 'S'){
		$html .= '
		<table style="width:100%; border-collapse: collapse; font-size:10px;">
		<tr><td colspan="5" style="text-align:center;"><h2>RELAÇÃO DAS SAÍDAS</h2></td></tr>
		<tr><td style="border:none;"></td></tr>
		';
	} else if ($iTipo == 'T'){
		$html .= '
		<table style="width:100%; border-collapse: collapse; font-size:10px;">
		<tr><td colspan="5" style="text-align:center;"><h2>RELAÇÃO DAS TRANSFERÊNCIAS</h2></td></tr>
		<tr><td style="border:none;"></td></tr>
		';	
	} else {

		$htmlEntrada .= '		
		<table style="width:100%; border-collapse: collapse; font-size:10px;">
		<tr><td colspan="5" style="text-align:center;"><h2>RELAÇÃO DAS ENTRADAS</h2></td></tr>
		<tr><td style="border:none;"></td></tr>
		';
		$htmlSaida .= '
		<table style="width:100%; border-collapse: collapse; font-size:10px;">
		<tr><td colspan="5" style="text-align:center;"><h2>RELAÇÃO DAS SAÍDAS</h2></td></tr>
		<tr><td style="border:none;"></td></tr>
		';
		$htmlTransferencia .= '
		<table style="width:100%; border-collapse: collapse; font-size:10px;">
		<tr><td colspan="5" style="text-align:center;"><h2>RELAÇÃO DAS TRANSFERÊNCIAS</h2></td></tr>
		<tr><td style="border:none;"></td></tr>
		';	
	}

	foreach ($row as $item) {
		
		if ($sTipoProdutoServico == 'P') {
			
			$dValidade = $item['MvXPrValidade'] != '1900-01-01' ? mostraData($item['MvXPrValidade']) : '';

			if ($iTipo == 'E'){

				$html .= "
				<tr>
					<td colspan='1' style='background-color:#EEEEEE;'>Data: " . mostraData($item['MovimData']) . "</td>
					<td colspan='4' style='background-color:#EEEEEE;'>Produto: " . $item['ProduNome'] . "</td>
				</tr>
				<tr>
					<td colspan='3'>Fornecedor: " . $item['ForneNome'] . "</td>
					<td colspan='2'>Destino: " . $item['Destino'] . "</td>											
				</tr>
				<tr>	
					<td style='width:25%'>Nota Fiscal: " . $item['MovimNotaFiscal'] . "</td>
					<td style='width:25%'>Quantidade: " . $item['MvXPrQuantidade'] . "</td>
					<td style='width:25%'>Lote: " . $item['MvXPrLote'] . "</td>
					<td style='width:25%'>Validade: " . $dValidade . "</td>
					<td style='width:25%'>Valor: " . mostraValor($item['MvXPrValorUnitario']) . "</td>
				</tr>
				<tr><td style='border:none;'></td></tr>
				";

			} else if ($iTipo == 'S'){ 

				$html .= "
				<tr>
					<td colspan='1' style='background-color:#EEEEEE;'>Data: " . mostraData($item['MovimData']) . "</td>
					<td colspan='4' style='background-color:#EEEEEE;'>Produto: " . $item['ProduNome'] . "</td>
				</tr>
				<tr>
					<td colspan='3'>Origem: " . $item['Origem'] . "</td>
					<td colspan='2'>Destino: " . $item['Destino'] . "</td>											
				</tr>
				<tr>	
					<td style='width:25%'>Classificação: " . $item['ClassNome'] . "</td>
					<td style='width:25%'>Quantidade: " . $item['MvXPrQuantidade'] . "</td>
					<td style='width:25%'>Lote: " . $item['MvXPrLote'] . "</td>
					<td style='width:25%'>Validade: " . $dValidade . "</td>
					<td style='width:25%'>Valor: " . mostraValor($item['MvXPrValorUnitario']) . "</td>
				</tr>
				<tr><td style='border:none;'></td></tr>
				";
			} else if ($iTipo == 'T'){

				$html .= "
				<tr>
					<td colspan='1' style='background-color:#EEEEEE;'>Data: " . mostraData($item['MovimData']) . "</td>
					<td colspan='4' style='background-color:#EEEEEE;'>Produto: " . $item['ProduNome'] . "</td>
				</tr>
				<tr>
					<td colspan='3'>Origem: " . $item['Origem'] . "</td>
					<td colspan='2'>Destino: " . $item['Destino'] . "</td>											
				</tr>
				<tr>	
					<td style='width:25%'>Classificação: " . $item['ClassNome'] . "</td>
					<td style='width:25%'>Quantidade: " . $item['MvXPrQuantidade'] . "</td>
					<td style='width:25%'>Lote: " . $item['MvXPrLote'] . "</td>
					<td style='width:25%'>Validade: " . $dValidade . "</td>
					<td style='width:25%'>Valor: " . mostraValor($item['MvXPrValorUnitario']) . "</td>
				</tr>
				<tr><td style='border:none;'></td></tr>
				";
			} else{

				if ($item['MovimTipo'] == 'E'){

					$htmlEntrada .= "
					<tr>
						<td colspan='1' style='background-color:#EEEEEE;'>Data: " . mostraData($item['MovimData']) . "</td>
						<td colspan='4' style='background-color:#EEEEEE;'>Produto: " . $item['ProduNome'] . "</td>
					</tr>
					<tr>
						<td colspan='3'>Fornecedor: " . $item['ForneNome'] . "</td>
						<td colspan='2'>Destino: " . $item['Destino'] . "</td>											
					</tr>
					<tr>	
						<td style='width:25%'>Nota Fiscal: " . $item['MovimNotaFiscal'] . "</td>
						<td style='width:25%'>Quantidade: " . $item['MvXPrQuantidade'] . "</td>
						<td style='width:25%'>Lote: " . $item['MvXPrLote'] . "</td>
						<td style='width:25%'>Validade: " . $dValidade . "</td>
						<td style='width:25%'>Valor: " . mostraValor($item['MvXPrValorUnitario']) . "</td>
					</tr>
					<tr><td style='border:none;'></td></tr>
					";

					$contEntrada++;
	
				} else if ($item['MovimTipo'] == 'S'){ 
	
					$htmlSaida .= "
					<tr>
						<td colspan='1' style='background-color:#EEEEEE;'>Data: " . mostraData($item['MovimData']) . "</td>
						<td colspan='4' style='background-color:#EEEEEE;'>Produto: " . $item['ProduNome'] . "</td>
					</tr>
					<tr>
						<td colspan='3'>Origem: " . $item['Origem'] . "</td>
						<td colspan='2'>Destino: " . $item['Destino'] . "</td>											
					</tr>
					<tr>	
						<td style='width:25%'>Classificação: " . $item['ClassNome'] . "</td>
						<td style='width:25%'>Quantidade: " . $item['MvXPrQuantidade'] . "</td>
						<td style='width:25%'>Lote: " . $item['MvXPrLote'] . "</td>
						<td style='width:25%'>Validade: " . $dValidade . "</td>
						<td style='width:25%'>Valor: " . mostraValor($item['MvXPrValorUnitario']) . "</td>
					</tr>
					<tr><td style='border:none;'></td></tr>
					";

					$contSaida++;

				} else if ($item['MovimTipo'] == 'T'){
	
					$htmlTransferencia .= "
					<tr>
						<td colspan='1' style='background-color:#EEEEEE;'>Data: " . mostraData($item['MovimData']) . "</td>
						<td colspan='4' style='background-color:#EEEEEE;'>Produto: " . $item['ProduNome'] . "</td>
					</tr>
					<tr>
						<td colspan='3'>Origem: " . $item['Origem'] . "</td>
						<td colspan='2'>Destino: " . $item['Destino'] . "</td>											
					</tr>
					<tr>	
						<td style='width:25%'>Classificação: " . $item['ClassNome'] . "</td>
						<td style='width:25%'>Quantidade: " . $item['MvXPrQuantidade'] . "</td>
						<td style='width:25%'>Lote: " . $item['MvXPrLote'] . "</td>
						<td style='width:25%'>Validade: " . $dValidade . "</td>
						<td style='width:25%'>Valor: " . mostraValor($item['MvXPrValorUnitario']) . "</td>
					</tr>
					<tr><td style='border:none;'></td></tr>
					";

					$contTransferencia++;
				} 	

			}

		} else { //Serviço

			if ($iTipo == 'E'){

				$html .= "
				<tr>
					<td colspan='1' style='background-color:#EEEEEE;'>Data: " . mostraData($item['MovimData']) . "</td>
					<td colspan='4' style='background-color:#EEEEEE;'>Serviço: " . $item['ServiNome'] . "</td>
				</tr>
				<tr>
					<td colspan='3'>Fornecedor: " . $item['ForneNome'] . "</td>
					<td colspan='2'>Destino: " . $item['Destino'] . "</td>											
				</tr>
				<tr>	
					<td colspan='2'>Nota Fiscal: " . $item['MovimNotaFiscal'] . "</td>
					<td colspan='1'>Quantidade: " . $item['MvXSrQuantidade'] . "</td>
					<td colspan='2'>Valor: " . mostraValor($item['MvXSrValorUnitario']) . "</td>
				</tr>
				<tr><td style='border:none;'></td></tr>
				";

			} else if ($iTipo == 'S'){ 

				$html .= "
				<tr>
					<td colspan='1' style='background-color:#EEEEEE;'>Data: " . mostraData($item['MovimData']) . "</td>
					<td colspan='4' style='background-color:#EEEEEE;'>Serviço: " . $item['ServiNome'] . "</td>
				</tr>
				<tr>
					<td colspan='3'>Origem: " . $item['Origem'] . "</td>
					<td colspan='2'>Destino: " . $item['Destino'] . "</td>											
				</tr>
				<tr>	
					<td colspan='3'>Quantidade: " . $item['MvXPrQuantidade'] . "</td>
					<td colspan='2'>Valor: " . mostraValor($item['MvXPrValorUnitario']) . "</td>
				</tr>
				<tr><td style='border:none;'></td></tr>
				";

			}  else{

				if ($item['MovimTipo'] == 'E'){

					$htmlEntrada .= "
					<tr>
						<td colspan='1' style='background-color:#EEEEEE;'>Data: " . mostraData($item['MovimData']) . "</td>
						<td colspan='4' style='background-color:#EEEEEE;'>Serviço: " . $item['ServiNome'] . "</td>
					</tr>
					<tr>
						<td colspan='3'>Fornecedor: " . $item['ForneNome'] . "</td>
						<td colspan='2'>Destino: " . $item['Destino'] . "</td>											
					</tr>
					<tr>	
						<td colspan='2'>Nota Fiscal: " . $item['MovimNotaFiscal'] . "</td>
						<td colspan='1'>Quantidade: " . $item['MvXSrQuantidade'] . "</td>
						<td colspan='2'>Valor: " . mostraValor($item['MvXSrValorUnitario']) . "</td>
					</tr>
					<tr><td style='border:none;'></td></tr>
					";

					$contEntrada++;
	
				} else if ($item['MovimTipo'] == 'S'){ 
	
					$htmlSaida .= "
					<tr>
						<td colspan='1' style='background-color:#EEEEEE;'>Data: " . mostraData($item['MovimData']) . "</td>
						<td colspan='4' style='background-color:#EEEEEE;'>Serviço: " . $item['ServiNome'] . "</td>
					</tr>
					<tr>
						<td colspan='3'>Origem: " . $item['Origem'] . "</td>
						<td colspan='2'>Destino: " . $item['Destino'] . "</td>											
					</tr>
					<tr>	
						<td colspan='3'>Quantidade: " . $item['MvXSrQuantidade'] . "</td>
						<td colspan='2'>Valor: " . mostraValor($item['MvXSrValorUnitario']) . "</td>
					</tr>
					<tr><td style='border:none;'></td></tr>
					";

					$contSaida++;

				}
			}

		} 
	}

	if ($htmlEntrada != ''){
		
		if ($contEntrada){
			$htmlEntrada .= "</table><br>";
			$html .= $htmlEntrada;
		}
		if ($contSaida){
			$htmlSaida .= "</table><br>";
			$html .= $htmlSaida;
		}
		if ($contTransferencia){
			$htmlTransferencia .= "</table><br>";
			$html .= $htmlTransferencia;
		}		

	} else{
		$html .= "</table>";
	}

	$html .= '<hr/>
			  <br>
			  Observação: Esse relatório foi gerado a partir dos seguintes critérios: ';

	if ($sTipoProdutoServico == 'P'){
		$html .= 'Tipo ('.$sTipo.'), Fornecedor ('.$sFornecedor.'), Categoria ('.$sCategoria.'), SubCategoria ('.$sSubCategoria.'), Código ('.$sCodigo.'), Produto ('.$sProduto.'), Origem ('.$sOrigem.'), Destino ('.$sDestino.'), Classificação ('.$sClassificacao.') <br><br>';	
	} else{
		$html .= 'Tipo ('.$sTipo.'), Fornecedor ('.$sFornecedor.'), Categoria ('.$sCategoria.'), SubCategoria ('.$sSubCategoria.'), Código ('.$sCodigo.'), Serviço ('.$sServico.'), Origem ('.$sOrigem.'), Destino ('.$sDestino.')<br><br>';	
	}
	
	$rodape = "<hr/>
    <div style='width:100%'>
		<div style='width:300px; float:left; display: inline;'>Sistema Lamparinas</div>
		<div style='width:105px; float:right; display: inline;'>Página {PAGENO} / {nbpg}</div> 
	</div>";

	//$mpdf->SetHTMLHeader($topo, 'O', true);
	$mpdf->WriteHTML($html);
	$mpdf->SetHTMLFooter($rodape);

	// Other code
	$mpdf->Output();
} catch (\Mpdf\MpdfException $e) { // Note: safer fully qualified exception name used for catch

	// Process the exception, log, print etc.
	echo $e->getMessage();
}