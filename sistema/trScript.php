<?php

include_once("sessao.php");

$_SESSION['PaginaAtual'] = 'Novo Termo de Referência';

include('global_assets/php/conexao.php');

/* Esse arquivo foi criado apenas para percorrer todas as empresas e suas respectivas unidades
   que tenham TR para popular corretamente o Detalhamento dos Produtos e Serviços.
   ATENÇÃO: esse arquivo pode ser excluído quando quiser, não fa parte do sistema. */

try {

	$conn->beginTransaction();

	/* Detalhamento para todos os TRs já cadastrados */
	$sql = "SELECT EmpreId, EmpreNomeFantasia
			FROM Empresa
			Where EmpreId not in (8, 10, 11)";
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

			$sql = "SELECT TrRefId
					FROM TermoReferencia
					WHERE TrRefUnidade = " . $itemUnidade['UnidaId'];
			$result = $conn->query($sql);
			$rowTR = $result->fetchAll(PDO::FETCH_ASSOC);		

			foreach ($rowTR as $itemTR){
			
				$iTR = $itemTR['TrRefId'];

				/*
				$sql = "SELECT TRXPrProduto
						FROM TermoReferenciaXProduto
						WHERE TRXPrTermoReferencia = " . $iTR;
				$result = $conn->query($sql);
				$rowProduto = $result->fetchAll(PDO::FETCH_ASSOC);
				$countProduto = count($rowProduto);

				if ($countProduto){
					
					if ($parametroProduto == 'ProdutoOrcamento'){
					
						foreach ($rowProduto as $itemProduto){
							
							$sql = "SELECT PrOrcDetalhamento
									FROM ProdutoOrcamento
									WHERE PrOrcId = " . $itemProduto['TRXPrProduto'];
							$result = $conn->query($sql);
							$rowProdutoDet = $result->fetch(PDO::FETCH_ASSOC);
	
							$sql = "UPDATE TermoReferenciaXProduto SET TRXPrDetalhamento = :sDetalhamento
									WHERE TRXPrProduto = :iProduto";
							$result = $conn->prepare($sql);
					
							$result->execute(array(
								':sDetalhamento' => $rowProdutoDet['PrOrcDetalhamento'],
								':iProduto' => $itemProduto['TRXPrProduto']
							));	
						}					
					} else {
						foreach ($rowProduto as $itemProduto){
							
							$sql = "SELECT ProduDetalhamento
									FROM Produto
									WHERE ProduId = " . $itemProduto['TRXPrProduto'];
							$result = $conn->query($sql);
							$rowProdutoDet = $result->fetch(PDO::FETCH_ASSOC);

							$sql = "UPDATE TermoReferenciaXProduto SET TRXPrDetalhamento = :sDetalhamento
									WHERE TRXPrProduto = :iProduto";
							$result = $conn->prepare($sql);
					
							$result->execute(array(
								':sDetalhamento' => $rowProdutoDet['ProduDetalhamento'],
								':iProduto' => $itemProduto['TRXPrProduto']
							));
						}
					}	
				}

				$sql = "SELECT TRXSrServico
						FROM TermoReferenciaXServico
						WHERE TRXSrTermoReferencia = " . $iTR;
				$result = $conn->query($sql);
				$rowServico = $result->fetchAll(PDO::FETCH_ASSOC);
				$countServico = count($rowServico);

				if ($countServico){				

					if ($parametroServico == 'ServicoOrcamento'){
						
						foreach ($rowServico as $itemServico){
							
							$sql = "SELECT SrOrcDetalhamento
									FROM ServicoOrcamento
									WHERE SrOrcId = " . $itemServico['TRXSrServico'];
							$result = $conn->query($sql);
							$rowServicoDet = $result->fetch(PDO::FETCH_ASSOC);
							
							$sql = "UPDATE TermoReferenciaXServico SET TRXSrDetalhamento = :sDetalhamento
									WHERE TRXSrServico = :iServico";
							$result = $conn->prepare($sql);
					
							$result->execute(array(
								':sDetalhamento' => $rowServicoDet['SrOrcDetalhamento'],
								':iServico' => $itemServico['TRXSrServico']
							));
						}
					
					} else {
						foreach ($rowServico as $itemServico){
							
							$sql = "SELECT ServiDetalhamento
									FROM Servico
									WHERE ServiId = " . $itemServico['TRXSrServico'];
							$result = $conn->query($sql);
							$rowServicoDet = $result->fetch(PDO::FETCH_ASSOC);

							$sql = "UPDATE TermoReferenciaXServico SET TRXSrDetalhamento = :sDetalhamento
									WHERE TRXSrServico = :iServico";
							$result = $conn->prepare($sql);
						
							$result->execute(array(
								':sDetalhamento' => $rowServicoDet['ServiDetalhamento'],
								':iServico' => $itemServico['TRXSrServico']
							));
						}
					}
				}
				*/


				$sql = "SELECT TrXOrId
						FROM TRXOrcamento
						WHERE TrXOrTermoReferencia = " . $iTR;
				$result = $conn->query($sql);
				$rowOrcamento = $result->fetchAll(PDO::FETCH_ASSOC);
				$countOrcamento = count($rowOrcamento);

				if ($countOrcamento){
					
					foreach ($rowOrcamento as $itemOrcamento){
						
						$sql = "SELECT TXOXPProduto
								FROM TRXOrcamentoXProduto
								WHERE TXOXPOrcamento = " . $itemOrcamento['TrXOrId'];
						$result = $conn->query($sql);
						$rowOrcamentoProduto = $result->fetchAll(PDO::FETCH_ASSOC);
						$countOrcamentoProduto = count($rowOrcamentoProduto);

						if ($countOrcamentoProduto){

							foreach ($rowOrcamentoProduto as $itemOrcamentoProduto){
							
								$sql = "SELECT TRXPrDetalhamento
										FROM TermoReferenciaXProduto
										WHERE TRXPrProduto = " . $itemOrcamentoProduto['TXOXPProduto'];
								$result = $conn->query($sql);
								$rowOrcamentoProdutoDet = $result->fetch(PDO::FETCH_ASSOC);
		
								$sql = "UPDATE TRXOrcamentoXProduto SET TXOXPDetalhamento = :sDetalhamento
										WHERE TXOXPProduto = :iProduto";
								$result = $conn->prepare($sql);
						
								$result->execute(array(
									':sDetalhamento' => $rowOrcamentoProdutoDet['TRXPrDetalhamento'],
									':iProduto' => $itemOrcamentoProduto['TXOXPProduto']
								));	
							}
						}

						$sql = "SELECT TXOXSServico
								FROM TRXOrcamentoXServico
								WHERE TXOXSOrcamento = " . $itemOrcamento['TrXOrId'];
						$result = $conn->query($sql);
						$rowOrcamentoServico = $result->fetchAll(PDO::FETCH_ASSOC);
						$countOrcamentoServico = count($rowOrcamentoServico);

						if ($countOrcamentoServico){

							foreach ($rowOrcamentoServico as $itemOrcamentoServico){
							
								$sql = "SELECT TRXSrDetalhamento
										FROM TermoReferenciaXServico
										WHERE TRXSrServico = " . $itemOrcamentoServico['TXOXSServico'];
								$result = $conn->query($sql);
								$rowOrcamentoServicoDet = $result->fetch(PDO::FETCH_ASSOC);

								$sql = "UPDATE TRXOrcamentoXServico SET TXOXSDetalhamento = :sDetalhamento
										WHERE TXOXSServico = :iServico";
								$result = $conn->prepare($sql);
						
								$result->execute(array(
									':sDetalhamento' => $rowOrcamentoServicoDet['TRXSrDetalhamento'],
									':iServico' => $itemOrcamentoServico['TXOXSServico']
								));	
							}
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
}

?>
