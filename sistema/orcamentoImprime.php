<?php

include_once("sessao.php");

include('global_assets/php/conexao.php');

use Mpdf\Mpdf;

require_once 'global_assets/php/vendor/autoload.php';

$iOrcamento = $_POST['inputOrcamentoId'];
$sNumero = $_POST['inputOrcamentoNumero'];

$sql ="SELECT OrcamId, OrcamNumero, OrcamTipo, OrcamData, OrcamConteudo, OrcamCategoria, OrcamSolicitante,
			  ForneNome, ForneCelular, ForneEmail, CategNome, OrcamStatus, 
			  dbo.fnSubCategoriasOrcamento(OrcamUnidade, OrcamId) as SubCategorias
		FROM Orcamento
		LEFT JOIN Fornecedor on ForneId = OrcamFornecedor
		JOIN Categoria on CategId = OrcamCategoria
		WHERE OrcamUnidade = " . $_SESSION['UnidadeId'] . " and OrcamId = " . $iOrcamento;
$result = $conn->query($sql);
$row = $result->fetch(PDO::FETCH_ASSOC);

$sql = "SELECT SbCatId, SbCatNome
		FROM OrcamentoXSubcategoria
		JOIN SubCategoria ON SbCatId = OrXSCSubcategoria
		WHERE OrXSCUnidade = '$_SESSION[UnidadeId]'	AND OrXSCOrcamento = '$iOrcamento' ORDER BY SbCatNome ASC";
$result = $conn->query($sql);
$rowSubCategoria = $result->fetchAll(PDO::FETCH_ASSOC);

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

	$mpdf->SetDisplayMode('fullpage','two'); //'fullpage': Ajustar uma página inteira na tela, 'fullwidth': Ajustar a largura da página na tela, 'real': Exibir em tamanho real, 'default': Configuração padrão do usuário no Adobe Reader, 'none'

	/*$mpdf = new Mpdf([
		'mode' => 'utf-8',
		//'format' => [190, 236], 
		'format' => 'A4-P', //A4-L
		'default_font_size' => 9,
		'default_font' => 'dejavusans',
		'orientation' => 'P' //P->Portrait (retrato)    L->Landscape (paisagem)
	]);*/


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
				<span style='font-weight:bold;line-height:200px;'>" . $_SESSION['EmpreNomeFantasia'] . "</span><br>
				<div style='position: absolute; font-size:12px; margin-top: 8px; margin-left:4px;'>Unidade: ".$_SESSION['UnidadeNome']."</div>
			</div>
			<div style='width:150px; float:right; display: inline; text-align:right;'>
				<div>".date('d/m/Y')."</div>
				<div style='margin-top:8px;'>Orçamento: " . formatarNumero($sNumero) . "</div>
			</div> 
		</div>	 

		<div style='text-align:center; margin-top: 20px;'><h1>ORÇAMENTO</h1></div>
	";

	if ($row['OrcamTipo'] == 'S') {
		$tipo = "Serviço";
	} else {
		$tipo = "Produto";
	}

	if ($tipo == "Produto") {
		$html .= '<div style="font-weight: bold; position:relative; margin-top: 10px; background-color:#ccc; padding: 5px;">
								Fornecedor: <span style="font-weight:normal;">' . $row['ForneNome'] . '</span> <span style="color:#aaa;"></span><br>Telefone: <span style="font-weight:normal;">' . $row['ForneCelular'] . '</span> <span style="color:#aaa;">&nbsp;&nbsp;|&nbsp;&nbsp;</span> E-mail: <span style="font-weight:normal;">' . $row['ForneEmail'] . '</span>
							</div>
							<div style="font-weight: bold; position:relative; margin-top: 5px; background-color:#eee; padding: 5px;">
								Categoria: <span style="font-weight:normal;">' . $row['CategNome'] . '</span>
							</div>
							<br>
							<div>' . $row['OrcamConteudo'] . '</div><br>';

		foreach ($rowSubCategoria as $subCatObj){
			$html .= '<div style="font-weight: bold; position:relative; margin-top: 5px; background-color:#eee; padding: 5px;">
									SubCategoria: <span style="font-weight:normal;">'.$subCatObj['SbCatNome'].'</span>
								</div>';
			$html .= '<table style="width:100%; border-collapse: collapse; margin-top: 20px;">
									<tr>
										<th style="text-align: center; width:8%">Item</th>
										<th style="text-align: left; width:46%">' . $tipo . '</th>
										<th style="text-align: center; width:11%">Unidade</th>
										<th style="text-align: center; width:12%">Quant.</th>				            
										<th style="text-align: center; width:11%">V. Unit.</th>
										<th style="text-align: center; width:12%">V. Total</th>
									</tr>';

			$sql = "SELECT ProduId, ProduNome, ProduDetalhamento, UnMedSigla, OrXPrQuantidade, OrXPrValorUnitario
					FROM Produto
					JOIN OrcamentoXProduto on OrXPrProduto = ProduId
					JOIN UnidadeMedida on UnMedId = ProduUnidadeMedida
					WHERE ProduEmpresa = '$_SESSION[EmpreId]' and OrXPrOrcamento = '$iOrcamento'
					and ProduSubCategoria = '$subCatObj[SbCatId]'";

			// $sql = "SELECT ProduId, ProduNome, ProduDetalhamento, UnMedSigla, OrXPrQuantidade, OrXPrValorUnitario
			// FROM Produto
			// JOIN OrcamentoXProduto on OrXPrProduto = ProduId
			// JOIN UnidadeMedida on UnMedId = ProduUnidadeMedida
			// WHERE ProduEmpresa = '$_SESSION[EmpreId]' and OrXPrOrcamento = '$iOrcamento'";

			$result = $conn->query($sql);
			$rowProdutos = $result->fetchAll(PDO::FETCH_ASSOC);
			
			$cont = 1;
			$cont2 = 0;
			$totalGeral = 0;
			
			foreach ($rowProdutos as $itemProduto) {
				if (($itemProduto['OrXPrValorUnitario'] != '' and $itemProduto['OrXPrValorUnitario'] != null) && ($itemProduto['OrXPrQuantidade'] != '' and $itemProduto['OrXPrQuantidade'] != null)) {
					$valorUnitario = mostraValor($itemProduto['OrXPrValorUnitario']);
					$valorTotal = mostraValor($itemProduto['OrXPrQuantidade'] * $itemProduto['OrXPrValorUnitario']);
					$totalGeral = ($itemProduto['OrXPrQuantidade'] * $itemProduto['OrXPrValorUnitario']) + $totalGeral;
					
					$cont2++;
				} else {
					$valorUnitario = "";
					$valorTotal = "";
				}

				$html .= "<tr>
										<td style='text-align: center;'>" . $cont . "</td>
										<td style='text-align: left;'>" . $itemProduto['ProduNome'] . ": " . $itemProduto['ProduDetalhamento'] . "</td>
										<td style='text-align: center;'>" . $itemProduto['UnMedSigla'] . "</td>					
										<td style='text-align: center;'>" . $itemProduto['OrXPrQuantidade'] . "</td>
										<td style='text-align: right;'>" . $valorUnitario . "</td>
										<td style='text-align: right; '>" . $valorTotal . "</td>
									</tr>";
				$cont++;
			}
			$html .= "<tr>
									<td colspan='5' height='40' valign='middle'>
										<strong>Total Geral</strong>
									</td>
									<td style='text-align: right;'>
											" . mostraValor($totalGeral) . "
									</td>
								</tr>";
			$html .= "</table>";
			$html .= "<br>";
		}
	} else {
		$html .= '<div style="font-weight: bold; position:relative; margin-top: 10px; background-color:#ccc; padding: 5px;">
								Fornecedor: <span style="font-weight:normal;">' . $row['ForneNome'] . '</span> <span style="color:#aaa;"></span><br>Telefone: <span style="font-weight:normal;">' . $row['ForneCelular'] . '</span> <span style="color:#aaa;">&nbsp;&nbsp;|&nbsp;&nbsp;</span> E-mail: <span style="font-weight:normal;">' . $row['ForneEmail'] . '</span>
							</div>
							<div style="font-weight: bold; position:relative; margin-top: 5px; background-color:#eee; padding: 5px;">
								Categoria: <span style="font-weight:normal;">' . $row['CategNome'] . '</span>
							</div><br>
							<div>' . $row['OrcamConteudo'] . '</div><br>';

		foreach ($rowSubCategoria as $subCatObj){
			$html .= '<div style="font-weight: bold; position:relative; margin-top: 5px; background-color:#eee; padding: 5px;">
									SubCategoria: <span style="font-weight:normal;">'.$subCatObj['SbCatNome'].'</span>
								</div>';
			$html .= '<table style="width:100%; border-collapse: collapse; margin-top: 20px;">
									<tr>
										<th style="text-align: center; width:8%">Item</th>
										<th style="text-align: left; width:50%">'.$tipo.'</th>
										<th style="text-align: center; width:12%">Quant.</th>
										<th style="text-align: center; width:15%">V. Unit.</th>
										<th style="text-align: center; width:15%">V. Total</th>
									</tr>';
			$sql = "SELECT ServiId, ServiNome, ServiDetalhamento, OrXSrQuantidade, OrXSrValorUnitario
					FROM Servico
					JOIN OrcamentoXServico on OrXSrServico = ServiId
					WHERE ServiEmpresa = " . $_SESSION['EmpreId'] . " and OrXSrOrcamento = " . $iOrcamento;

			$result = $conn->query($sql);
			$rowServicos = $result->fetchAll(PDO::FETCH_ASSOC);

			$cont = 1;
			$cont2 = 0;
			$totalGeral = 0;

			foreach ($rowServicos as $itemServico) {

				if (($itemServico['OrXSrValorUnitario'] != '' and $itemServico['OrXSrValorUnitario'] != null and $itemServico['OrXSrValorUnitario'] != 0) && ($itemServico['OrXSrQuantidade'] != '' and $itemServico['OrXSrQuantidade'] != null and $itemServico['OrXSrQuantidade'] != 0)) {
					$valorUnitario = mostraValor($itemServico['OrXSrValorUnitario']);
					$valorTotal = mostraValor($itemServico['OrXSrQuantidade'] * $itemServico['OrXSrValorUnitario']);
					$totalGeral = ($itemServico['OrXSrQuantidade'] * $itemServico['OrXSrValorUnitario']) + $totalGeral;

					$cont2++;
				} else {
					$valorUnitario = "";
					$valorTotal = "";
				}

				$html .= "
					<tr>
						<td style='text-align: center'>" . $cont . "</td>
						<td style='text-align: left'>" . $itemServico['ServiNome'] . ": " . $itemServico['ServiDetalhamento'] . "</td>
						<td style='text-align: center'>" . $itemServico['OrXSrQuantidade'] . "</td>
						<td style='text-align: right'>" . $valorUnitario . "</td>
						<td style='text-align: right'>" . $valorTotal . "</td>
					</tr>
				";

				$cont++;
			}

			if ($cont2 == count($rowServicos)) {
				$html .= "  
						<tr>
									<td colspan='4' height='40' valign='middle'>
											<strong>Total Geral</strong>
										</td>
							<td style='text-align: right;'>
									" . mostraValor($totalGeral) . "
							</td>
					</tr>";
			} else {
				$html .= "  
						<tr>
									<td colspan='4' height='40' valign='middle'>
											<strong>Total Geral</strong>
										</td>
							<td style='text-align: right;'>
								
							</td>
					</tr>";
			}
			$html .= "</table>";
			$html .= "<br>";
		}
	}

	$sql = "SELECT UsuarId, UsuarNome, UsuarEmail, UsuarTelefone
			FROM Usuario
			Where UsuarId = " . $row['OrcamSolicitante'] . "
			ORDER BY UsuarNome ASC";
	$result = $conn->query($sql);
	$rowUsuario = $result->fetch(PDO::FETCH_ASSOC);

	$html .= '			
		<br><br>
		<div style="width: 100%; margin-top: 100px;">
			<div style="position: relative; float: left; text-align: center;">
				Solicitante: ' . $rowUsuario['UsuarNome'] . '<br>
				<div style="margin-top:3px;">';
	
	if ($rowUsuario['UsuarTelefone'] != ''){
		$html .= 'Telefone: ' . $rowUsuario['UsuarTelefone'] . ' <br>';
	}
	
	$html .= '		E-mail: ' . $rowUsuario['UsuarEmail'] . '
				</div>
			</div>
		</div>
	';

	//$html .= "</div>";

	$topo = "
	<div style='position: relative; width:100%; border-bottom: 1px solid #000;'>
		<div style='width:300px; float:left; display: inline;'>
			<img src='global_assets/images/empresas/".$_SESSION['EmpreFoto']."' style='width:60px; height:60px; float:left; margin-right: 10px; margin-top:-10px;' />		
			<span style='font-weight:bold;line-height:200px;'>" . $_SESSION['EmpreNomeFantasia'] . "</span><br>
			<div style='position: absolute; font-size:12px; margin-top: 8px; margin-left:4px;'>Unidade: ".$_SESSION['UnidadeNome']."</div>
		</div>
		<div style='width:150px; float:right; display: inline; text-align:right;'>
			<div>{DATE j/m/Y}</div>
			<div style='margin-top:8px;'>Orçamento: " . formatarNumero($sNumero) . "</div>
		</div> 
	 </div>
	";

	$rodape = "<hr/>
    <div style='width:100%'>
		<div style='width:300px; float:left; display: inline;'>Sistema Lamparinas</div>
		<div style='width:105px; float:right; display: inline;'>Página {PAGENO} / {nbpg}</div> 
	</div>";
	
	//ATENÇÃO: Tive que colocar o cabeçalho dentro do HTML, para o cabeçalho não sobrescrever o conteúdo HTML a partir da segunda página. Em compensação o cabeçalho só aparece na primeira página. Foi a única forma que encontrei. Tentei de tudo...

	//$mpdf->SetHTMLHeader($topo);	//o SetHTMLHeader deve vir antes do WriteHTML para que o cabeçalho apareça em todas as páginas
	$mpdf->SetHTMLFooter($rodape); 	//o SetHTMLFooter deve vir antes do WriteHTML para que o rodapé apareça em todas as páginas
	$mpdf->WriteHTML($html);

	// Other code
	$mpdf->Output();

} catch (\Mpdf\MpdfException $e) { // Note: safer fully qualified exception name used for catch

	// Process the exception, log, print etc.
	echo 'ERRO: '.$e->getMessage();
}
