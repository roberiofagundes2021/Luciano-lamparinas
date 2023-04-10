<?php

	if (isset($_POST['inputTRId'])){
		$insertId = $_POST['inputTRId'];
	}

	if ($possuiSubCategoria){

		foreach ($_POST['cmbSubCategoria'] as $value) {					
			
			if ($parametroProduto = 'ProdutoOrcamento') {
			
				$sql = "SELECT PrOrcId as idProduto, PrOrcDetalhamento as Detalhamento
						FROM ProdutoOrcamento
						JOIN Situacao on SituaId = PrOrcSituacao
						WHERE PrOrcSubcategoria = " . $value . " and SituaChave = 'ATIVO'";
			} else{
			
				$sql = "SELECT ProduId as idProduto, ProduDetalhamento as Detalhamento
						FROM Produto
						JOIN Situacao on SituaId = ProduStatus
						WHERE ProduSubCategoria = " . $value . " and SituaChave = 'ATIVO'";
			}
			
			$result = $conn->query($sql);
			$produtos = $result->fetchAll(PDO::FETCH_ASSOC);

			foreach ($produtos as $produto) {

				$sql = "INSERT INTO TermoReferenciaXProduto (TRXPrTermoReferencia, TRXPrProduto, TRXPrDetalhamento, TRXPrQuantidade, TRXPrValorUnitario, TRXPrTabela, TRXPrUsuarioAtualizador, TRXPrUnidade)
						VALUES (:iTR, :iProduto, :sDetalhamento, :iQuantidade, :fValorUnitario, :sTabela, :iUsuarioAtualizador, :iUnidade)";
				$result = $conn->prepare($sql);

				if ($produto) {

					$result->execute(array(
						':iTR' => $insertId,
						':iProduto' => $produto['idProduto'],
						':sDetalhamento' => $produto['Detalhamento'],
						':iQuantidade' => null,
						':fValorUnitario' => null,
						':sTabela' => $parametroProduto,
						':iUsuarioAtualizador' => $_SESSION['UsuarId'],
						':iUnidade' => $_SESSION['UnidadeId'],
					));
				}
			}
		}
		
	} else {
		
		$value = $_POST['cmbCategoria'];
			
		if ($parametroProduto = 'ProdutoOrcamento') {
		
			$sql = "SELECT PrOrcId as idProduto, PrOrcDetalhamento as Detalhamento
					FROM ProdutoOrcamento
					JOIN Situacao on SituaId = PrOrcSituacao
					WHERE PrOrcCategoria = " . $value . " and SituaChave = 'ATIVO'";
		} else {
		
			$sql = "SELECT ProduId as idProduto, ProduDetalhamento as Detalhamento
					FROM Produto
					JOIN Situacao on SituaId = ProduStatus
					WHERE ProduCategoria = " . $value . " and SituaChave = 'ATIVO'";
		}
		
		$result = $conn->query($sql);
		$produtos = $result->fetchAll(PDO::FETCH_ASSOC);
	
		foreach ($produtos as $produto) {

			$sql = "INSERT INTO TermoReferenciaXProduto (TRXPrTermoReferencia, TRXPrProduto, TRXPrDetalhamento, TRXPrQuantidade, TRXPrValorUnitario, TRXPrTabela, TRXPrUsuarioAtualizador, TRXPrUnidade)
					VALUES (:iTR, :iProduto, :sDetalhamento, :iQuantidade, :fValorUnitario, :sTabela, :iUsuarioAtualizador, :iUnidade)";
			$result = $conn->prepare($sql);

			if ($produto) {

				$result->execute(array(
					':iTR' => $insertId,
					':iProduto' => $produto['idProduto'],
					':sDetalhamento' => $produto['Detalhamento'],
					':iQuantidade' => null,
					':fValorUnitario' => null,
					':sTabela' => $parametroProduto,
					':iUsuarioAtualizador' => $_SESSION['UsuarId'],
					':iUnidade' => $_SESSION['UnidadeId'],
				));
			}
		}
	}
?>