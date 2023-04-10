<?php

include_once("sessao.php");

$_SESSION['PaginaAtual'] = 'Novo Fluxo Operacional';

include('global_assets/php/conexao.php');

/* Esse arquivo foi criado apenas para percorrer todas as empresas e suas respectivas unidades
   que tenham Fluxo para popular corretamente o Detalhamento dos Produtos e Serviços.
   ATENÇÃO: esse arquivo pode ser excluído quando quiser, não fa parte do sistema. */

try {

	$conn->beginTransaction();

	/* Detalhamento para todos os Fluxos já cadastrados */
	$sql = "SELECT EmpreId, EmpreNomeFantasia
			FROM Empresa
			Where EmpreId in (8, 10, 11, 17)";
	$result = $conn->query($sql);
	$rowEmpresa = $result->fetchAll(PDO::FETCH_ASSOC);

	foreach ($rowEmpresa as $itemEmpresa){

		$sql = "SELECT ParamProdutoOrcamento, ParamServicoOrcamento
				FROM Parametro
				WHERE ParamEmpresa = " . $itemEmpresa['EmpreId'];
		$result = $conn->query($sql);
		$rowParametro = $result->fetch(PDO::FETCH_ASSOC);

		isset($rowParametro['ParamProdutoOrcamento']) && $rowParametro['ParamProdutoOrcamento'] == 1 ? $parametroProduto = 'ProdutoOrcamento' : $parametroProduto = 'Produto';
		isset($rowParametro['ParamServicoOrcamento']) && $rowParametro['ParamServicoOrcamento'] == 1 ? $parametroServico = 'ServicoOrcamento' : $parametroServico = 'Servico';

		$sql = "SELECT UnidaId, UnidaNome
				FROM Unidade
				WHERE UnidaEmpresa = ".$itemEmpresa['EmpreId'];
		$result = $conn->query($sql);
		$rowUnidade = $result->fetchAll(PDO::FETCH_ASSOC);

		foreach ($rowUnidade as $itemUnidade){

			$sql = "SELECT FlOpeId, FlOpeTermoReferencia
					FROM FluxoOperacional
					WHERE FlOpeUnidade = " . $itemUnidade['UnidaId'];
			$result = $conn->query($sql);
			$rowFluxo = $result->fetchAll(PDO::FETCH_ASSOC);		

			foreach ($rowFluxo as $itemFluxo){
			
				$iFluxo = $itemFluxo['FlOpeId'];

				if ($parametroProduto == 'ProdutoOrcamento'){
					$sql = "SELECT PrOrcId as Produto
							FROM FluxoOperacionalXProduto
							JOIN ProdutoOrcamento on PrOrcProduto = FOXPrProduto
							WHERE FOXPrFluxoOperacional = " . $iFluxo;
				} else {
					$sql = "SELECT FOXPrProduto as Produto
							FROM FluxoOperacionalXProduto
							WHERE FOXPrFluxoOperacional = " . $iFluxo;
				}

				$result = $conn->query($sql);
				$rowProduto = $result->fetchAll(PDO::FETCH_ASSOC);
				$countProduto = count($rowProduto);

				if ($countProduto){					
					
					foreach ($rowProduto as $itemProduto){
						
						if (isset($itemFluxo['FlOpeTermoReferencia']) && $itemFluxo['FlOpeTermoReferencia'] != 'NULL'){
							$sql = "SELECT TRXPrDetalhamento
									FROM TermoReferenciaXProduto
									JOIN TermoReferencia on TrRefId = TRXPrTermoReferencia
									WHERE TRXPrTermoReferencia = ".$itemFluxo['FlOpeTermoReferencia']." and TRXPrProduto = " . $itemProduto['Produto'];
							$result = $conn->query($sql);
							$rowProdutoDet = $result->fetch(PDO::FETCH_ASSOC);

							$sql = "UPDATE FluxoOperacionalXProduto SET FOXPrDetalhamento = :sDetalhamento
									WHERE FOXPrFluxoOperacional = :iFluxo and FOXPrProduto = :iProduto";
							$result = $conn->prepare($sql);
					
							$result->execute(array(
								':sDetalhamento' => $rowProdutoDet['TRXPrDetalhamento'],
								':iFluxo' => $iFluxo,
								':iProduto' => $itemProduto['Produto']
							));
						}
					}						
				}
				
				if ($parametroServico == 'ServicoOrcamento'){
					$sql = "SELECT SrOrcId as Servico
							FROM FluxoOperacionalXServico
							JOIN ServicoOrcamento on SrOrcServico = FOXSrServico
							WHERE FOXSrFluxoOperacional = " . $iFluxo;
				} else {
					$sql = "SELECT FOXSrServico as Servico
							FROM FluxoOperacionalXServico
							WHERE FOXSrFluxoOperacional = " . $iFluxo;
				}
				$result = $conn->query($sql);
				$rowServico = $result->fetchAll(PDO::FETCH_ASSOC);
				$countServico = count($rowServico);

				if ($countServico){				

					foreach ($rowServico as $itemServico){
						
						if (isset($itemFluxo['FlOpeTermoReferencia']) && $itemFluxo['FlOpeTermoReferencia'] != 'NULL'){
							$sql = "SELECT TRXSrDetalhamento
									FROM TermoReferenciaXServico
									JOIN TermoReferencia on TrRefId = TRXSrTermoReferencia
									WHERE TRXSrTermoReferencia = ".$itemFluxo['FlOpeTermoReferencia']." and TRXSrServico = " . $itemServico['Servico'];
							$result = $conn->query($sql);
							$rowServicoDet = $result->fetch(PDO::FETCH_ASSOC);

							$sql = "UPDATE FluxoOperacionalXServico SET FOXSrDetalhamento = :sDetalhamento
									WHERE FOXSrFluxoOperacional = :iFluxo and FOXSrServico = :iServico";
							$result = $conn->prepare($sql);
					
							$result->execute(array(
								':sDetalhamento' => $rowServicoDet['TRXSrDetalhamento'],
								':iFluxo' => $iFluxo,
								':iServico' => $itemServico['Servico']
							));
						}
					}
				}			
			}

			echo "Unidade: ". $itemUnidade['UnidaNome']. " concluída com sucesso!";
			echo "<br>";
		}

		echo "<b>Empresa: ". $itemEmpresa['EmpreNomeFantasia']. " concluído com sucesso!</b>";
		echo "<br><hr /><br>";
	}

	$conn->commit();

	echo "FINALIZADO COM SUCESSO!!!";

} catch (PDOException $e) {

	$conn->rollback();
	echo 'Error1: ' . $e->getMessage();
	echo '<br>';
	echo $sql;die;
}

?>
