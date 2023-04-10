<?php

include_once("sessao.php");

include('global_assets/php/conexao.php');

use Mpdf\Mpdf;

require_once 'global_assets/php/vendor/autoload.php';

if (isset($_POST['inputOrcamentoId'])) {
	$iOrcamento = $_POST['inputOrcamentoId'];
} else {
	print('<script>
				window.close();
		   </script> ');
}

try {

	$sql = "SELECT TrXOrNumero, TrRefData, TrXOrConteudo, TrXOrTabelaProduto, TrXOrTabelaServico,
				   TrXOrSolicitante, TrRefId, TrRefTabelaProduto, TrRefTabelaServico, TrRefNumero, 
				   TrRefTipo, ForneNome, ForneRazaoSocial, CategNome 
			FROM TRXOrcamento
			JOIN TermoReferencia ON TrRefId = TrXOrTermoReferencia
			LEFT JOIN Fornecedor ON ForneId = TrXOrFornecedor
			JOIN Categoria ON CategId = TrXOrCategoria
			LEFT JOIN SubCategoria ON SbCatId = TrXOrSubCategoria
			WHERE TrXOrUnidade = " . $_SESSION['UnidadeId'] . " 
			AND TrXOrId = " . $iOrcamento;

	$result = $conn->query($sql);
	$row = $result->fetch(PDO::FETCH_ASSOC);

	$sql = "SELECT SbCatNome, TXOXSCSubcategoria
		  	FROM TRXOrcamentoXSubCategoria
			JOIN SubCategoria ON SbCatId = TXOXSCSubcategoria
		 	WHERE TXOXSCUnidade = " . $_SESSION['UnidadeId'] . " 
		   	AND TXOXSCOrcamento = " . $iOrcamento."
			ORDER BY SbCatNome ASC";
	$result = $conn->query($sql);
	$rowSubCategoria = $result->fetchAll(PDO::FETCH_ASSOC);

	//Selects para identificar se o orçamento já possue produtos.
	$tabelaOrigemProduto = $row['TrRefTabelaProduto'];
	$campoPrefix =  $row['TrRefTabelaProduto'] == 'Produto' ? 'Produ' : 'PrOrc';

	$sql = "SELECT DISTINCT COUNT(TXOXPProduto) as CONT
    		FROM TRXOrcamentoXProduto
    		JOIN " . $tabelaOrigemProduto . " on " . $campoPrefix . "Id = TXOXPProduto
			WHERE " . $campoPrefix . "Unidade = " . $_SESSION['UnidadeId'] . " and TXOXPOrcamento = " . $iOrcamento . "";
	$result = $conn->query($sql);
	$rowProdutoUtilizado = $result->fetch(PDO::FETCH_ASSOC);

	// Selects para identificar se o orçamento já possue serviços.
	$tabelaOrigemServico = $row['TrRefTabelaServico'];
	$campoPrefix =  $row['TrRefTabelaServico'] == 'Servico' ? 'Servi' : 'SrOrc';

	$sql = "SELECT COUNT(TXOXSServico) as CONT
    		FROM TRXOrcamentoXServico
    		JOIN " . $tabelaOrigemServico . " on " . $campoPrefix . "Id = TXOXSServico
    		WHERE " . $campoPrefix . "Unidade = " . $_SESSION['UnidadeId'] . " and TXOXSOrcamento = " . $iOrcamento . " ";
	$result = $conn->query($sql);
	$rowServicoUtilizado = $result->fetch(PDO::FETCH_ASSOC);

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
			<div style='width:300px; float:left; display: inline;'>
				<img src='global_assets/images/empresas/".$_SESSION['EmpreFoto']."' style='width:60px; height:60px; float:left; margin-right: 10px; margin-top:-10px;' />		
				<span style='font-weight:bold;line-height:200px;'>" . $_SESSION['EmpreNomeFantasia'] . "</span><br>
				<div style='position: absolute; font-size:12px; margin-top: 8px; margin-left:4px;'>Unidade: " . $_SESSION['UnidadeNome'] . "</div>
			</div>
			<div style='width:220px; float:right; display: inline; text-align:right;'>
				<div>" . date('d/m/Y') . "</div>
				<div style='margin-top:8px;'>Orçamento: " . formatarNumero($row['TrXOrNumero']) . "</div>
			</div> 
			</div>

			<div style='text-align:center; margin-top: 20px;'><h1>ORÇAMENTO</h1></div>
    ";

	$html .= "<div style='text-align:center;'><h2>TERMO DE REFERÊNCIA</h2></div>";

	$html .= '
    <table style="width:100%; border-collapse: collapse;">
        <tr style="background-color:#F1F1F1;">
            <td style="width:25%; font-size:12px;">Nº TR: ' . $row['TrRefNumero'] . '</td>
            <td style="width:15%; font-size:12px;">Data: ' . mostraData($row['TrRefData']) . '</td>
        </tr>
    </table>
	<br>';

	if ($row['ForneNome'] <> ""){
		$html .= "<div style='text-align:center;'><h2>FORNECEDOR</h2></div>";
		$html .= '<div style="text-align:center; margin-top: -20px"><p style="font-size:18px;">' . $row['ForneRazaoSocial'] . '</p></div>';	
	}

	$html .= '
	<div>' . $row['TrXOrConteudo'] . '</div>
	<br>';

	if ($rowProdutoUtilizado['CONT'] > 0) {

		$tituloProduto = "<div style='text-align:center;'><h2>PRODUTOS</h2></div>";

		$cabecalhoProduto = '
	                                	<div style="font-weight: bold; position:relative; margin-top: 5px; background-color:#ddd; padding: 8px; border: 1px solid #ccc;">
	                                	Categoria: <span style="font-weight:normal;">' . $row['CategNome'] . '</span> 
	                                </div>
	                            <br>
						';
		
		$html .= $row['TrRefTipo'] == 'P' || $row['TrRefTipo'] == 'PS' ? $tituloProduto.' '.$cabecalhoProduto : '';
		$exibirProduto = $row['TrRefTipo'] == 'P' || $row['TrRefTipo'] == 'PS' ? true : false;

		$cont = 1;

		foreach ($rowSubCategoria as $sbcat) {

			$totalProdutos = 0;

            $tabelaOrigemProduto = $row['TrRefTabelaProduto'];
			$campoPrefix =  $row['TrRefTabelaProduto'] == 'Produto' ? 'Produ' : 'PrOrc';

			$sql = "SELECT ".$campoPrefix."Id as Id, ".$campoPrefix."Nome as Nome, ".$campoPrefix."Categoria as Categoria, ".$campoPrefix."SubCategoria as SubCategoria,
			        ".$campoPrefix."Detalhamento as Detalhamento, UnMedSigla, TXOXPQuantidade, TXOXPValorUnitario
					FROM ".$tabelaOrigemProduto."
					JOIN TRXOrcamentoXProduto on TXOXPProduto = ".$campoPrefix."Id
					JOIN UnidadeMedida on UnMedId = ".$campoPrefix."UnidadeMedida
					JOIN SubCategoria on SbCatId = ".$campoPrefix."SubCategoria
                    WHERE ".$campoPrefix."Unidade = " . $_SESSION['UnidadeId'] . " and TXOXPOrcamento = " . $iOrcamento."
					AND ".$campoPrefix."SubCategoria = " . $sbcat['TXOXSCSubcategoria']."
					ORDER BY SbCatNome, ".$campoPrefix."Nome ASC";
			$result = $conn->query($sql);
			$rowProdutos = $result->fetchAll(PDO::FETCH_ASSOC);
			$count = count($rowProdutos);

			if (isset($rowProdutos) && $count && $exibirProduto) {

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
                        <th style="text-align: center; width:15%">V. Unitário</th>
                        <th style="text-align: center; width:15%">V. Total</th>
					</tr>
				';

				foreach ($rowProdutos as $itemProduto) {

					if ($sbcat['TXOXSCSubcategoria'] == $itemProduto['SubCategoria']) {

						if ($itemProduto['TXOXPValorUnitario'] != '' and $itemProduto['TXOXPValorUnitario'] != null) {
							$valorUnitario = mostraValor($itemProduto['TXOXPValorUnitario']);
							$valorTotal = mostraValor($itemProduto['TXOXPQuantidade'] * $itemProduto['TXOXPValorUnitario']);
						} else {
							$valorUnitario = '';
							$valorTotal = '';
						}

						$html .= "
                            
                            <tr>
					            <td style='text-align: center;'>" . $cont . "</td>
					            <td style='text-align: left;'>" . $itemProduto['Nome'] . ", " . $itemProduto['Detalhamento'] . "</td>
					            <td style='text-align: center;'>" . $itemProduto['UnMedSigla'] . "</td>					
					            <td style='text-align: center;'>" . $itemProduto['TXOXPQuantidade'] . "</td>
					            <td style='text-align: right;'>" . $valorUnitario . "</td>
					            <td style='text-align: right;'>" . $valorTotal . "</td>
				            </tr>
                		";

						$cont++;
						$totalProdutos += $itemProduto['TXOXPQuantidade'] * $itemProduto['TXOXPValorUnitario'];
					}
				}

				$totalGeralProdutos += $totalProdutos;

				$html .= "<br>";

				$html .= "  <tr>
                				<td colspan='5' height='50' valign='middle'>
                					<strong>Total Produtos</strong>
                				</td>
                				<td style='text-align: right' colspan='1'>
                					" . mostraValor($totalProdutos) . "
                				</td>
                			</tr>";
				$html .= "</table>";
			}
		}
	} else {

		$tituloProduto = "<div style='text-align:center;'><h2>PRODUTOS</h2></div>";

		$cabecalhoProduto = '
	                                	<div style="font-weight: bold; position:relative; margin-top: 5px; background-color:#ddd; padding: 8px; border: 1px solid #ccc;">
	                                	Categoria: <span style="font-weight:normal;">' . $row['CategNome'] . '</span> 
	                                </div>
	                            <br>
						';
		
		$html .= $row['TrRefTipo'] == 'P' || $row['TrRefTipo'] == 'PS' ? $tituloProduto.' '.$cabecalhoProduto : '';
		$exibirProduto = $row['TrRefTipo'] == 'P' || $row['TrRefTipo'] == 'PS' ? true : false;

		$cont = 1;

		foreach ($rowSubCategoria as $sbcat) {

			$totalProdutos = 0;

			$tabelaOrigemProduto = $row['TrRefTabelaProduto'];
			$campoPrefix =  $row['TrRefTabelaProduto'] == 'Produto' ? 'Produ' : 'PrOrc';

			$sql = "SELECT " . $campoPrefix . "Id as Id, " . $campoPrefix . "Nome as Nome, " . $campoPrefix . "Categoria as Categoria, " . $campoPrefix . "SubCategoria as SubCategoria,
		            " . $campoPrefix . "Detalhamento as Detalhamento, UnMedSigla, TRXPrQuantidade
		    		FROM " . $tabelaOrigemProduto . "
		    		JOIN TermoReferenciaXProduto on TRXPrProduto = " . $campoPrefix . "Id
                    JOIN TermoReferencia on TrRefId = TRXPrTermoReferencia
                    JOIN TRXSubcategoria on TRXSCTermoReferencia = TRXPrTermoReferencia
		    		JOIN UnidadeMedida on UnMedId = " . $campoPrefix . "UnidadeMedida
					JOIN SubCategoria on SbCatId = ".$campoPrefix."SubCategoria
                    WHERE " . $campoPrefix . "Unidade = " . $_SESSION['UnidadeId'] . " and TRXPrTermoReferencia = " . $row['TrRefId']."
					ORDER BY SbCatNome, ".$campoPrefix."Nome ASC";
			$result = $conn->query($sql);
			$rowProdutos = $result->fetchAll(PDO::FETCH_ASSOC);

			if (isset($rowProdutos) && $exibirProduto) {

				$html .= '
				<div style="font-weight: bold; position:relative; margin-top: 15px; background-color:#eee; padding: 8px; border: 1px solid #ccc;">
					SubCategoria: <span style="font-weight:normal;">' . $sbcat['SbCatNome'] . '</span>
				</div>
				<br> ';

				$html .= '	
				<table style="width:100%; border-collapse: collapse;">
					<tr>
						<th style="text-align: center; width:10%">Item</th>
						<th style="text-align: left; width:65%">Produto</th>
						<th style="text-align: center; width:10%">Unidade</th>        	
						<th style="text-align: center; width:15%">Quantidade</th>
						<th style="text-align: center; width:15%">V. Unitário</th>
                        <th style="text-align: center; width:15%">V. Total</th>
					</tr>
				';

				foreach ($rowProdutos as $itemProduto) {

					if ($sbcat['TXOXSCSubcategoria'] == $itemProduto['SubCategoria']) {

						$html .= "
                            
                            <tr>
					            <td style='text-align: center;'>" . $cont . "</td>
					            <td style='text-align: left;'>" . $itemProduto['Nome'] . ", " . $itemProduto['Detalhamento'] . "</td>
					            <td style='text-align: center;'>" . $itemProduto['UnMedSigla'] . "</td>					
					            <td style='text-align: center;'>" . $itemProduto['TRXPrQuantidade'] . "</td>
								<td style='text-align: center;'></td>
								<td style='text-align: center;'></td>
					            
				            </tr>
                		";

						$cont++;
						// $totalProdutos += $itemProduto['TXOXPQuantidade'] * $itemProduto['TXOXPValorUnitario'];
					}
				}

				$totalGeralProdutos += $totalProdutos;

				$html .= "<br>";

				$html .= "  <tr>
								<td colspan='5' height='50' valign='middle'>
									<strong>Total Produtos</strong>
								</td>
								<td style='text-align: right' colspan='1'></td>
							</tr>";
				$html .= "</table>";
			}
		}
	}

	if ($rowServicoUtilizado['CONT'] > 0) {

		$tituloServico = "<div style='text-align:center; margin-top: 20px;'><h2>SERVIÇOS</h2></div>";

		$cabecalhoServico = '
    	                        <div style="font-weight: bold; position:relative; margin-top: 5px; background-color:#ddd;  padding: 8px;  border: 1px solid #ccc;">
    	                        	Categoria: <span style="font-weight:normal;">' . $row['CategNome'] . '</span> 
    	                        </div>
    	                    <br>
						';
		
		$html .= $row['TrRefTipo'] == 'S' || $row['TrRefTipo'] == 'PS' ? $tituloServico.' '.$cabecalhoServico : '';
		$cont = 1;

		foreach ($rowSubCategoria as $sbcat) {

			$totalServicos = 0;

			$tabelaOrigemServico = $row['TrRefTabelaServico'];
			$campoPrefix =  $row['TrRefTabelaServico'] == 'Servico' ? 'Servi' : 'SrOrc';

			$sql = "SELECT ".$campoPrefix."Id as Id, ".$campoPrefix."Nome as Nome, ".$campoPrefix."Categoria as Categoria, 
					" . $campoPrefix . "Detalhamento as Detalhamento,
					".$campoPrefix."SubCategoria as SubCategoria, TXOXSQuantidade, TXOXSValorUnitario
				  	FROM ".$tabelaOrigemServico."
					JOIN TRXOrcamentoXServico ON TXOXSServico = ".$campoPrefix."Id
					JOIN SubCategoria on SbCatId = ".$campoPrefix."SubCategoria
				 	WHERE ".$campoPrefix."Unidade = " . $_SESSION['UnidadeId'] . " 
				   	AND TXOXSOrcamento = " . $iOrcamento . " 
					AND ".$campoPrefix."SubCategoria = " . $sbcat['TXOXSCSubcategoria']."
					ORDER BY SbCatNome, ".$campoPrefix."Nome ASC";

			$result = $conn->query($sql);
			$rowServicos = $result->fetchAll(PDO::FETCH_ASSOC);
			$count = count($rowServicos);

			if (isset($rowServicos) and $count) {

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
                        <th style="text-align: center; width:15%">V. Unitário</th>
                        <th style="text-align: center; width:15%">V. Total</th>
    				</tr>
				';

				foreach ($rowServicos as $itemServico) {

					if ($sbcat['TXOXSCSubcategoria'] == $itemServico['SubCategoria']) {

						if ($itemServico['TXOXSValorUnitario'] != '' and $itemServico['TXOXSValorUnitario'] != null) {
							$valorUnitario = mostraValor($itemServico['TXOXSValorUnitario']);
							$valorTotal = mostraValor($itemServico['TXOXSQuantidade'] * $itemServico['TXOXSValorUnitario']);
						} else {
							$valorUnitario = '';
							$valorTotal = '';
						}

						$html .= "
    						<tr>
    							<td style='text-align: center;'>" . $cont . "</td>
    							<td style='text-align: left;'>" . $itemServico['Nome'] . ", " . $itemServico['Detalhamento'] . "</td>
                                <td style='text-align: center;'>" . $itemServico['TXOXSQuantidade'] . "</td>
                                <td style='text-align: right;'>" . $valorUnitario . "</td>
					            <td style='text-align: right;'>" . $valorTotal . "</td>
    						</tr>
    					";

						$cont++;
						$totalServicos += $itemServico['TXOXSQuantidade'] * $itemServico['TXOXSValorUnitario'];
					}
				}

				$totalGeralServicos += $totalServicos;

				$html .= "<br>";

				$html .= "  <tr>
    							<td colspan='4' height='50' valign='middle'>
    								<strong>Total Serviços</strong>
    							</td>
    							<td style='text-align: right' colspan='1'>
    								" . mostraValor($totalServicos) . "
    							</td>
    						</tr>";
				$html .= "</table>";
			}
		}
	} else {

		$tituloServico = "<div style='text-align:center; margin-top: 20px;'><h2>SERVIÇOS</h2></div>";

		$cabecalhoServico = '
    	                        <div style="font-weight: bold; position:relative; margin-top: 5px; background-color:#ddd;  padding: 8px;  border: 1px solid #ccc;">
    	                        	Categoria: <span style="font-weight:normal;">' . $row['CategNome'] . '</span> 
    	                        </div>
    	                    <br>
						';
		
		$html .= $row['TrRefTipo'] == 'S' || $row['TrRefTipo'] == 'PS' ? $tituloServico.' '.$cabecalhoServico : '';
		$cont = 1;

		foreach ($rowSubCategoria as $sbcat) {

			$totalServicos = 0;

			$tabelaOrigemServico = $row['TrRefTabelaServico'];
			$campoPrefix =  $row['TrRefTabelaServico'] == 'Servico' ? 'Servi' : 'SrOrc';

			$sql = "SELECT DISTINCT ".$campoPrefix ."Id as Id, 
							 ".$campoPrefix ."Nome as Nome, 
							 ". $campoPrefix . "Detalhamento as Detalhamento,
							 ".$campoPrefix ."Categoria as Categoria, 
							 ".$campoPrefix ."SubCategoria as SubCategoria, 
							 TRXSrQuantidade
					FROM ".$tabelaOrigemServico."
					JOIN TermoReferenciaXServico 
						ON TRXSrServico = ".$campoPrefix ."Id
				 WHERE ".$campoPrefix ."Unidade = " . $_SESSION['UnidadeId'] . " 
				 	 AND TRXSrTermoReferencia = " . $row['TrRefId'] . "
			";
		
			$result = $conn->query($sql);
			$rowServicos = $result->fetchAll(PDO::FETCH_ASSOC);
			$count = count($rowServicos);

			if (isset($rowServicos) and $count) {

				$html .= '
    			<div style="font-weight: bold; position:relative; margin-top: 15px; background-color:#eee; padding: 8px; border: 1px solid #ccc;">
    				SubCategoria: <span style="font-weight:normal;">' . $sbcat['SbCatNome'] . '</span>
    			</div>
    			<br> ';

				$html .= '	
    			<table style="width:100%; border-collapse: collapse;">
				<tr>
				<th style="text-align: center; width:8%">Item</th>
				<th style="text-align: left; width:65%">Serviço</th>     	
				<th style="text-align: center; width:15%">Quantidade</th>
				<th style="text-align: center; width:15%">V. Unitário</th>
				<th style="text-align: center; width:15%">V. Total</th>
				</tr>
    			';

					// var_dump($row['TrRefTabelaServico']);
					// die;


				foreach ($rowServicos as $itemServico) {


					if ($sbcat['TXOXSCSubcategoria'] == $itemServico['SubCategoria']) {

						$html .= "
						<tr>
						    <td style='text-align: center;'>" . $cont . "</td>
						    <td style='text-align: left;'>" . $itemServico['Nome'] . ", " . $itemServico['Detalhamento'] . "</td>
						    <td style='text-align: center;'>" . $itemServico['TRXSrQuantidade'] . "</td>
						    <td style='text-align: right;'></td>
						    <td style='text-align: right;'></td>
					    </tr>
    					";

						$cont++;
						//$totalServicos += $itemServico['TRXSrQuantidade'] * $itemServico['TXOXSValorUnitario'];
					}
				}

				//$totalGeralServicos += $totalServicos;

				$html .= "<br>";

				$html .= "  <tr>
    							<td colspan='4' height='50' valign='middle'>
    								<strong>Total Serviços</strong>
    							</td>
    							<td style='text-align: center' colspan='1'></td>
    						</tr>";
				$html .= "</table>";
			}
		}
	}

	$totalGeral = $totalGeralProdutos + $totalGeralServicos;
	//echo $totalGeral;die;

	if ($totalGeral) {
		$html .= "<table style='width:100%; border-collapse: collapse; margin-top: 20px;'>
					<tr>
						<td colspan='3' height='50' valign='middle' style='width:85%'>
							<strong>TOTAL GERAL DE ITENS</strong>
						</td>
						<td style='text-align: right; width:15%'>
							" . mostraValor($totalGeral) . "
						</td>
					</tr>
				</table>
		";
	}

	$sql = "SELECT UsuarId, UsuarNome, UsuarEmail, UsuarTelefone
			FROM Usuario
			Where UsuarId = " . $row['TrXOrSolicitante'] . "
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
