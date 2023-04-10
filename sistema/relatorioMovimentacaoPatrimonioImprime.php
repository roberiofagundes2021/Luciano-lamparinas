<?php

include_once("sessao.php");

include('global_assets/php/conexao.php');

use Mpdf\Mpdf;

require_once 'global_assets/php/vendor/autoload.php';

// Aplicado filtro aos resultados
/***************************************************************/
$args = [];

/////////////////////////////////////////////

if (!empty($_POST['inputDataDe_imp']) || !empty($_POST['inputDataAte_imp'])) {
	empty($_POST['inputDataDe_imp']) ? $inputDataDe = '1900-01-01' : $inputDataDe = $_POST['inputDataDe_imp'];
	empty($_POST['inputDataAte_imp']) ? $inputDataAte = '2100-01-01' : $inputDataAte = $_POST['inputDataAte_imp'];

	$args[]  = "MovimData BETWEEN '".$inputDataDe."' and '".$inputDataAte."' ";
}

if(!empty($_POST['inputLocalEstoque_imp'])){
	$args[]  = "MovimDestinoLocal = ".$_POST['inputLocalEstoque_imp']." ";
}

if(!empty($_POST['inputSetor_imp'])){
	$args[]  = "MovimDestinoSetor = ".$_POST['inputSetor_imp']." ";
}

if(!empty($_POST['inputCategoria_imp'])){
	$args[]  = "ProduCategoria = ".$_POST['inputCategoria_imp']." ";
}

if(!empty($_POST['inputSubCategoria_imp'])){
	$args[]  = "ProduSubCategoria = ".$_POST['inputSubCategoria_imp']." ";
}

if(!empty($_POST['inputProduto_imp'])){
	$args[]  = "ProduNome LIKE '%".$_POST['inputProduto_imp']."%' ";
}

if (count($args) >= 1) {
	try {

		$string = implode(" and ", $args);

		$string != '' ? $string .= ' and ' : $string;

		$sql = "SELECT PatriNumero ,MvXPrId, MovimId, MovimData, MovimNotaFiscal, MvXPrValidade, 
				MvXPrValorUnitario, MvXPrValidade, ProduNome,
				CASE 
					WHEN MovimOrigemLocal IS NULL THEN SetorO.SetorNome
					ELSE LocalO.LcEstNome 
						END as Origem,
					CASE 
					WHEN MovimDestinoLocal IS NULL THEN ISNULL(SetorD.SetorNome, MovimDestinoManual)
					ELSE LocalD.LcEstNome
						END as Destino
				FROM Patrimonio
				JOIN MovimentacaoXProduto on MvXPrPatrimonio = PatriId
				JOIN Movimentacao on MovimId = MvXPrMovimentacao
				JOIN Produto on ProduId = MvXPrProduto
				LEFT JOIN LocalEstoque LocalO on LocalO.LcEstId = MovimOrigemLocal 
				LEFT JOIN LocalEstoque LocalD on LocalD.LcEstId = MovimDestinoLocal 
				LEFT JOIN Setor SetorO on SetorO.SetorId = MovimOrigemSetor 
				LEFT JOIN Setor SetorD on SetorD.SetorId = MovimDestinoSetor 
				WHERE " . $string . " MovimUnidade = " . $_SESSION['UnidadeId'] . "
				";
		$result = $conn->query($sql);
		$rowData = $result->fetchAll(PDO::FETCH_ASSOC);

		count($rowData) >= 1 ? $cont = 1 : $cont = 0;
	} catch (PDOException $e) {
		echo 'Error: ' . $e->getMessage();
	}
}
/***************************************************************/

if (isset($_POST['inputLocalEstoque_imp']) && $_POST['inputLocalEstoque_imp'] != '') {
	try {
		$sql = "SELECT LcEstNome
		        FROM LocalEstoque
		        WHERE LcEstId = " . $_POST['inputLocalEstoque_imp'] . " and LcEstUnidade = " . $_SESSION['UnidadeId'] . "
				";
		echo $sql;		
		$result = $conn->query($sql);
		$LocalEstoque = $result->fetch(PDO::FETCH_ASSOC);
	} catch (PDOException $e) {
		echo 'Error1: ' . $e->getMessage();die;
	}
}

if (isset($_POST['inputSetor_imp']) && $_POST['inputSetor_imp'] != '') {
	try {
		$sql = "SELECT SetorNome
		        FROM Setor
		        WHERE SetorId = " . $_POST['inputSetor_imp'] . " and SetorUnidade = " . $_SESSION['UnidadeId'] . "
	            ";
		$result = $conn->query($sql);
		$Setor = $result->fetch(PDO::FETCH_ASSOC);
	} catch (PDOException $e) {
		echo 'Error: ' . $e->getMessage();
	}
}

if (isset($_POST['inputCategoria_imp']) && $_POST['inputCategoria_imp'] != '') {
	try {
		$sql = "SELECT CategNome
		        FROM Categoria
		        WHERE CategId = " . $_POST['inputCategoria_imp'] . " and CategEmpresa = ".$_SESSION['EmpreId']." 
	            ";
		$result = $conn->query($sql);
		$Categoria = $result->fetch(PDO::FETCH_ASSOC);
	} catch (PDOException $e) {
		echo 'Error: ' . $e->getMessage();
	}
}


if (isset($_POST['inputSubCategoria_imp']) && $_POST['inputSubCategoria_imp'] != '') {
	try {
		$sql = "SELECT SbCatNome
		        FROM SubCategoria
		        WHERE SbCatId = " . $_POST['inputSubCategoria_imp'] . " and SbCatEmpresa = " . $_SESSION['EmpreId'] . "
	            ";
		$result = $conn->query($sql);
		$SubCategoria = $result->fetch(PDO::FETCH_ASSOC);
	} catch (PDOException $e) {
		echo 'Error: ' . $e->getMessage();
	}
}

if (isset($_POST['resultados'])) {
	try {
		$mpdf = new Mpdf([
			'mode' => 'utf-8',
			//'format' => [190, 236], 
			'format' => 'A4-L', //A4-L
			'default_font_size' => 9,
			'default_font' => 'dejavusans',
			'orientation' => 'L' //P->Portrait (retrato)    L->Landscape (paisagem)
		]);

		$topo = "
			<div style='position: relative; width:100%; border-bottom: 1px solid #000;'>
				<div style='float:left; width: 400px; display: inline-block; margin-bottom: 10px;'>
					<img src='global_assets/images/empresas/".$_SESSION['EmpreFoto']."' style='width:60px; height:60px; float:left; margin-right: 10px; margin-top:-10px;' />		
					<span style='font-weight:bold;line-height:200px;'>" . $_SESSION['EmpreNomeFantasia'] . "</span><br>
					<div style='position: absolute; font-size:9px; margin-top: 8px; margin-left:4px;'>Unidade: " . $_SESSION['UnidadeNome'] . "</div>
				</div>
				<div style='width:350px; float:right; display: inline; text-align:right;'>
					<div style='font-size: 0.8rem'>Data {DATE j/m/Y}</div>
					<div style='margin-top:8px;'></div>
					<div style='margin-top:8px; font-weight:bold;'>Relatório Movimentação do Patrimônio</div>
				</div>
			</div>
	    ";

		$html = '
			<style>
				th{
					text-align: center; 
					border: #bbb solid 1px; 
					background-color: #f8f8f8; 
					padding: 8px;
					font-size: 0.6rem;
				}

				td{
					padding: 8px;				
					border: #bbb solid 1px;
					font-size: 0.6rem;
				}
			</style>
		';

		$html .= '<br>';
		$html .= '<br>';

		if (!empty($_POST['inputDataDe_imp']) || !empty($_POST['inputDataAte_imp'])) {
			if (!empty($_POST['inputDataDe_imp']) && !empty($_POST['inputDataAte_imp'])) {
				$html .= '
            		<div style="font-weight: bold; font-size: 0.8rem; position:relative; margin-top: 10px; padding: 5px;">
			            Período: ' . mostraData($_POST['inputDataDe_imp']) . ' à ' . mostraData($_POST['inputDataAte_imp']) . ' 
		            </div>
		        ';
			} else if (!empty($_POST['inputDataDe_imp'])) {
				$html .= '
            		<div style="font-weight: bold; font-size: 0.8rem; position:relative; margin-top: 20px; padding: 5px;">
			            Período: ' . mostraData($_POST['inputDataDe_imp']) . ' à ' . date('d/m/Y') . ' 
		            </div>
		        ';
			} else {
				$html .= '
            		<div style="font-weight: bold; font-size: 0.8rem; position:relative; margin-top: 20px; padding: 5px;">
			            Período: Até ' . date('d/m/Y') . ' 
		            </div>
		        ';
			}
		}

		if (!empty($_POST['inputCategoria_imp'])) {
			$html .= '
					<div style="font-weight: bold; font-size: 0.8rem; padding: 5px;">
			            Categoria: ' . $Categoria['CategNome'] . '
		            </div>
		        ';
		}
		if (!empty($_POST['inputSubCategoria_imp'])) {
			$html .= '
            		<div style="font-weight: bold; font-size: 0.8rem; position:relative; padding: 5px;">
			            SubCategoria: ' . $SubCategoria['SbCatNome'] . ' 
		            </div>
		        ';
		}


		$html .= '
		<br>
		<br>
		<table style="width:100%; border-collapse: collapse; margin-top: -24px">
			<tr>
				<th style="text-align: left; width:4%;">Item</th>
				<th style="text-align: left; width:17%;">Descrição</th>
				<th style="text-align: center; width:10%;">Patrimônio</th>
				<th style="text-align: center; width:12%;">Nota Fiscal</th>
				<th style="text-align: center; width:11%;">Aquisição (R$)</th>
				<th style="text-align: center; width:12%;">Depreciação (R$)</th>
				<th style="text-align: center; width:10%;">Validade</th>
				<th style="text-align: left; width:12%;">Origem</th>
				<th style="text-align: left; width:12%;">Destino</th>
			</tr>
		';

		$html .= "<tbody>";

		$cont = 0;
		foreach ($rowData as $produto) {
			$cont += 1;
			$html .= "
			<tr>
				<td style='text-align: center'>" . $cont . "</td>
				<td style='text-align: left'>" . $produto['ProduNome'] . "</td>
				<td style='text-align: center'>".$produto['PatriNumero']."</td>
				<td style='text-align: center'>" . $produto['MovimNotaFiscal'] . "</td>
				<td style='text-align: right'>" . mostraValor($produto['MvXPrValorUnitario']) . "</td>
				<td style='text-align: right'></td>
				<td style='text-align: center'>" . mostraData($produto['MvXPrValidade']) . "</td>
				<td style='text-align: left'>" . $produto['Origem'] . "</td>
				<td style='text-align: left'>" . $produto['Destino'] . "</td>
			</tr>
		 ";
		}
		$html .= "</tbody>";

		$html .= "</table>";

		$rodape = "<hr/>
		<div style='width:100%'>
			<div style='width:300px; float:left; display: inline;'>Sistema Lamparinas</div>
			<div style='width:105px; float:right; display: inline;'>Página {PAGENO} / {nbpg}</div> 
		</div>";

		$mpdf->SetHTMLHeader($topo, 'O', true);
		$mpdf->WriteHTML($html);
		$mpdf->SetHTMLFooter($rodape);

		// Other code
		$mpdf->Output();
	} catch (\Mpdf\MpdfException $e) { // Note: safer fully qualified exception name used for catch

		// Process the exception, log, print etc.
		echo $e->getMessage();
	}
}
