<?php

include_once("sessao.php");

include('global_assets/php/conexao.php');

//print_r($_POST);

if (isset($_POST['patrimonioId']) && $_POST['patrimonioId'] !== '') {
	$sql = "SELECT  ProduId, 
									ProduNome, 
									ProduValorCusto, 
									ProduCustoFinal, 
									UnMedSigla, 
									ProduDetalhamento, 
									dbo.fnSaldoEstoque(".$_SESSION['UnidadeId'].", ProduId, 'P', '" . $_POST['origem'] . "') as Estoque,
									PatriNumero,
									MvXPrValidade
						FROM  Produto
						JOIN  UnidadeMedida 
						  ON  UnMedId = ProduUnidadeMedida
						JOIN  Patrimonio
							ON  PatriProduto = ProduId
						JOIN  MovimentacaoXProduto
						  ON  MvXPrProduto = ProduId
					 WHERE  ProduEmpresa = " . $_SESSION['EmpreId'] . " 
						 AND  ProduId = " . $_POST['idProduto'] . "
						 AND  PatriId = " . $_POST['patrimonioId'] . "
				";

	$result = $conn->query($sql);
	$row = $result->fetch(PDO::FETCH_ASSOC);
	$count = count($row);

	//Verifica se já existe esse registro (se existir, retorna true )
	if ($count) {
		$valorCusto = formataMoeda($row['ProduCustoFinal']);
		$valorTotal = formataMoeda($_POST['quantidade'] * $row['ProduCustoFinal']);
		$total = $_POST['quantidade'] * $row['ProduCustoFinal'];
	}

	$output = 	'
											<tr id="row' . $_POST['numItens'] . '" class="trGrid">
												<td>' . $_POST['numItens'] . '</td>
												<td data-popup="tooltip" title="' . $row['ProduDetalhamento'] . '">' . $row['ProduNome'] . '</td>
												<td style="text-align: center">' . $row['PatriNumero'] . '</td>
												<td style="text-align: center">' . $row['UnMedSigla'] . '</td>
												<td style="text-align: center">' . $_POST['quantidade'] . '</td>
												<td style="text-align: right">' . $valorCusto . '</td>
												<td style="text-align: right">' . $valorTotal . '</td>
										';
	if ($row['MvXPrValidade'] != null)
		$output .= '
												<td style="text-align: right">' . mostraData($row['MvXPrValidade']) . '</td>
										';
	else
		$output .= '
												<td style="text-align: right"></td>
										';

	$output .= 	"
												<td><span name=remove id='" . $_POST['numItens'] . "#" . $total . "' class='btn btn_remove'>X</span></td>
											</tr>
										";

} else {
	$sql = "SELECT ProduId, 
  					   ProduNome, 
							 ProduValorCusto, 
							 ProduCustoFinal, 
							 UnMedSigla, 
							 ProduDetalhamento, 
							 dbo.fnSaldoEstoque(".$_SESSION['UnidadeId'].", ProduId, 'P', '" . $_POST['origem'] . "') as Estoque,
							 MvXPrValidade
					FROM Produto
					JOIN UnidadeMedida 
					  ON UnMedId = ProduUnidadeMedida
					JOIN MovimentacaoXProduto
	  			  ON MvXPrProduto = ProduId
				 WHERE ProduEmpresa = " . $_SESSION['EmpreId'] . " 
					 AND ProduId = " . $_POST['idProduto'] . "
				";

	$result = $conn->query($sql);
	$row = $result->fetch(PDO::FETCH_ASSOC);
	$count = count($row);

	//Verifica se já existe esse registro (se existir, retorna true )
	if ($count) {
		$valorCusto = formataMoeda($row['ProduCustoFinal']);
		$valorTotal = formataMoeda($_POST['quantidade'] * $row['ProduCustoFinal']);
		$total = $_POST['quantidade'] * $row['ProduCustoFinal'];
	}

	$output = 	'
											<tr id="row' . $_POST['numItens'] . '" class="trGrid">
												<td>' . $_POST['numItens'] . '</td>
												<td data-popup="tooltip" title="' . $row['ProduDetalhamento'] . '">' . $row['ProduNome'] . '</td>
												<td style="text-align: center"> </td>
												<td style="text-align: center">' . $row['UnMedSigla'] . '</td>
												<td style="text-align: center">' . $_POST['quantidade'] . '</td>
												<td style="text-align: right">' . $valorCusto . '</td>
												<td style="text-align: right">' . $valorTotal . '</td>
										';
	if ($row['MvXPrValidade'] != null)
		$output .= '
												<td style="text-align: right">' . mostraData($row['MvXPrValidade']) . '</td>
										';
	else
		$output .= '
												<td style="text-align: right"></td>
										';

	$output .= 	"
												<td><span name=remove id='" . $_POST['numItens'] . "#" . $total . "' class='btn btn_remove'>X</span></td>
											</tr>
										";
}

echo $output;