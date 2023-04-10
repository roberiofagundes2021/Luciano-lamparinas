<?php

include_once("sessao.php");

$_SESSION['PaginaAtual'] = 'Novo Fluxo Operacional';

include('global_assets/php/conexao.php');

/* Esse arquivo foi criado apenas para percorrer todas as empresas e suas respectivas unidades
   que tenham Ordem de Compra para popular corretamente o Detalhamento dos Produtos e Serviços.
   ATENÇÃO: esse arquivo pode ser excluído quando quiser, não fa parte do sistema. */

try {

	$conn->beginTransaction();

	/* Detalhamento para todos as Ordem de Compra já cadastradas */
	$sql = "SELECT EmpreId, EmpreNomeFantasia
			FROM Empresa
			Where EmpreId in (8, 10, 11, 17)";
	$result = $conn->query($sql);
	$rowEmpresa = $result->fetchAll(PDO::FETCH_ASSOC);

	foreach ($rowEmpresa as $itemEmpresa){

		$sql = "SELECT UnidaId, UnidaNome
				FROM Unidade
				WHERE UnidaEmpresa = ".$itemEmpresa['EmpreId'];
		$result = $conn->query($sql);
		$rowUnidade = $result->fetchAll(PDO::FETCH_ASSOC);

		foreach ($rowUnidade as $itemUnidade){

			$sql = "SELECT OrComId, OrComFluxoOperacional
					FROM OrdemCompra
					WHERE OrComUnidade = " . $itemUnidade['UnidaId'];
			$result = $conn->query($sql);
			$rowOrdemCompra = $result->fetchAll(PDO::FETCH_ASSOC);		

			foreach ($rowOrdemCompra as $itemOrdemCompra){
			
				$iOrdemCompra = $itemOrdemCompra['OrComId'];

				$sql = "SELECT OCXPrProduto
						FROM OrdemCompraXProduto
						WHERE OCXPrOrdemCompra = " . $iOrdemCompra;
				$result = $conn->query($sql);
				$rowProduto = $result->fetchAll(PDO::FETCH_ASSOC);
				$countProduto = count($rowProduto);

				if ($countProduto){
					
					foreach ($rowProduto as $itemProduto){
						
						if (isset($itemOrdemCompra['OrComFluxoOperacional']) && $itemOrdemCompra['OrComFluxoOperacional'] != 'NULL'){
							$sql = "SELECT FOXPrDetalhamento
									FROM FluxoOperacionalXProduto
									JOIN FluxoOperacional on FlOpeId = FOXPrFluxoOperacional
									WHERE FOXPrFluxoOperacional = ".$itemOrdemCompra['OrComFluxoOperacional']." and FOXPrProduto = " . $itemProduto['OCXPrProduto'];
							$result = $conn->query($sql);
							$rowProdutoDet = $result->fetch(PDO::FETCH_ASSOC);

							$sql = "UPDATE OrdemCompraXProduto SET OCXPrDetalhamento = :sDetalhamento
									WHERE OCXPrOrdemCompra = :iOrdemCompra and OCXPrProduto = :iProduto";
							$result = $conn->prepare($sql);
					
							$result->execute(array(
								':sDetalhamento' => $rowProdutoDet['FOXPrDetalhamento'],
								':iOrdemCompra' => $iOrdemCompra,
								':iProduto' => $itemProduto['OCXPrProduto']
							));
						}
					}						
				}

				$sql = "SELECT OCXSrServico
						FROM OrdemCompraXServico
						WHERE OCXSrOrdemCompra = " . $iOrdemCompra;
				$result = $conn->query($sql);
				$rowServico = $result->fetchAll(PDO::FETCH_ASSOC);
				$countServico = count($rowServico);

				if ($countServico){				

					foreach ($rowServico as $itemServico){
						
						if (isset($itemOrdemCompra['OrComFluxoOperacional']) && $itemOrdemCompra['OrComFluxoOperacional'] != 'NULL'){
							$sql = "SELECT FOXSrDetalhamento
									FROM FluxoOperacionalXServico
									JOIN FluxoOperacional on FlOpeId = FOXSrFluxoOperacional
									WHERE FOXSrFluxoOperacional = ".$itemOrdemCompra['OrComFluxoOperacional']." and FOXSrServico = " . $itemServico['OCXSrServico'];
							$result = $conn->query($sql);
							$rowServicoDet = $result->fetch(PDO::FETCH_ASSOC);

                            $sql = "UPDATE OrdemCompraXServico SET OCXSrDetalhamento = :sDetalhamento
                                    WHERE OCXSrOrdemCompra = :iOrdemCompra and OCXSrServico = :iServico";
                            $result = $conn->prepare($sql);
                    
                            $result->execute(array(
                                ':sDetalhamento' => $rowServicoDet['FOXSrDetalhamento'],
                                ':iOrdemCompra' => $iOrdemCompra,
                                ':iServico' => $itemServico['OCXSrServico']
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

} catch (PDOException $e) {

	$conn->rollback();
	echo 'Error1: ' . $e->getMessage();
	echo '<br>';
	echo $sql;die;
}

?>
