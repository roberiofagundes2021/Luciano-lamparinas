<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'TR Orçamento Duplica';

include('global_assets/php/conexao.php');

if (isset($_POST['inputOrcamentoId'])){

	try {

		$conn->beginTransaction();

		$sql = "SELECT TrXOrId, TrXOrTermoReferencia, TrXOrCategoria, TrXOrConteudo, TrXOrStatus
				FROM TRXOrcamento
				WHERE TrXOrUnidade = ". $_SESSION['UnidadeId'] ." and TrXOrId = ".$_POST['inputOrcamentoId']."";
		$result = $conn->query($sql);
		$rowOrcamento = $result->fetch(PDO::FETCH_ASSOC);
		//$count = count($rowOrcamento);
		
		$sql = "SELECT max(TrXOrNumero) as Numero
				FROM TRXOrcamento
				WHERE TrXOrUnidade = " . $_SESSION['UnidadeId'] . " and TrXOrTermoReferencia = ".$_SESSION['TRId'];
		$result = $conn->query($sql);
		$rowNumero = $result->fetch(PDO::FETCH_ASSOC);		
		
		$sNumero = (int)$rowNumero['Numero'] + 1;
		$sNumero = str_pad($sNumero,6,"0",STR_PAD_LEFT);
			
		$sql = "INSERT INTO TRXOrcamento (TrXOrTermoReferencia, TrXOrNumero, TrXOrData, TrXOrCategoria, TrXOrConteudo, TrXOrFornecedor,
						TrXOrSolicitante, TrXOrStatus, TrXOrUsuarioAtualizador, TrXOrUnidade)
				VALUES (:sTR, :sNumero, :dData, :iCategoria, :sConteudo, :iFornecedor, :iSolicitante, 
						:bStatus, :iUsuarioAtualizador, :iUnidade)";
		$result = $conn->prepare($sql);
		
		$result->execute(array(
						':sTR' => $rowOrcamento['TrXOrTermoReferencia'],
						':sNumero' => $sNumero,
						':dData' => gravaData(date('d/m/Y')),
						':iCategoria' => $rowOrcamento['TrXOrCategoria'] == '' ? null : $rowOrcamento['TrXOrCategoria'],
						':sConteudo' => $rowOrcamento['TrXOrConteudo'],
						':iFornecedor' => null,
						':iSolicitante' => $_SESSION['UsuarId'],
						':bStatus' => $rowOrcamento['TrXOrStatus'],
						':iUsuarioAtualizador' => $_SESSION['UsuarId'],
						':iUnidade' => $_SESSION['UnidadeId']
						));						

		$insertId = $conn->lastInsertId();

		$sql = "SELECT *
				FROM TRXOrcamentoXProduto
				WHERE TXOXPUnidade = ". $_SESSION['UnidadeId'] ." and TXOXPOrcamento = ".$_POST['inputOrcamentoId']."";
		$result = $conn->query($sql);
		$rowProduto = $result->fetchAll(PDO::FETCH_ASSOC);

		foreach ($rowProduto as $item){ 
		
			$sql = "INSERT INTO TRXOrcamentoXProduto (TXOXPOrcamento, TXOXPProduto, TXOXPDetalhamento, TXOXPQuantidade, TXOXPValorUnitario, 
					TXOXPUsuarioAtualizador, TXOXPUnidade)
					VALUES (:iOrcamento, :iProduto, :sDetalhamento, :iQuantidade, :fValorUnitario, :iUsuarioAtualizador, :iUnidade)";
			$result = $conn->prepare($sql);
			
			$result->execute(array(
							':iOrcamento'		 	=> $insertId,
							':iProduto' 			=> $item['TXOXPProduto'],
							':sDetalhamento' 	    => $item['TXOXPDetalhamento'],
							':iQuantidade' 			=> $item['TXOXPQuantidade'],
							':fValorUnitario' 		=> null,
							':iUsuarioAtualizador' 	=> $_SESSION['UsuarId'],
							':iUnidade' 			=> $_SESSION['UnidadeId']
							));
		}

		// Select Subcategoria
		$sql = "SELECT SbCatId, SbCatNome
				FROM SubCategoria
				JOIN TRXOrcamentoXSubcategoria on TXOXSCSubcategoria = SbCatId
				WHERE SbCatEmpresa = ". $_SESSION['EmpreId'] ." and TXOXSCOrcamento = ".$rowOrcamento['TrXOrId']."";
		$result = $conn->query($sql);
		$rowSBC = $result->fetchAll(PDO::FETCH_ASSOC);

		$sql = "INSERT INTO TRXOrcamentoXSubcategoria (TXOXSCOrcamento, TXOXSCSubcategoria, TXOXSCUnidade)
				VALUES (:iOrcamento, :iSubCategoria, :iUnidade)";
		$result = $conn->prepare($sql);

		foreach ($rowSBC as $subcategoria){

			$result->execute(array(
				':iOrcamento' => $insertId,
				':iSubCategoria' => $subcategoria['SbCatId'],
				':iUnidade' => $_SESSION['UnidadeId']
			));
		}

		$sql = "INSERT INTO AuditTR ( AdiTRTermoReferencia, AdiTRDataHora, AdiTRUsuario, AdiTRTela, AdiTRDetalhamento)
				VALUES (:iTRTermoReferencia, :iTRDataHora, :iTRUsuario, :iTRTela, :iTRDetalhamento)";
		$result = $conn->prepare($sql);
				
		$result->execute(array(
			':iTRTermoReferencia' 	=> $rowOrcamento['TrXOrTermoReferencia'] ,
			':iTRDataHora' 			=> date("Y-m-d H:i:s"),
			':iTRUsuario' 			=> $_SESSION['UsuarId'],
			':iTRTela' 				=>'ORÇAMENTO',
			':iTRDetalhamento' 		=>'ORÇAMENTO DUPLICADO DE Nº '.$sNumero. ''
		));

		$conn->commit();

		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Orçamento duplicado!!!";
		$_SESSION['msg']['tipo'] = "success";	

	} catch(PDOException $e) {

		$conn->rollback();

		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao duplicar orçamento!!!";
		$_SESSION['msg']['tipo'] = "error";	

		//echo 'Error2: ' . $e->getMessage();die;
	}

} else {

	$_SESSION['msg']['titulo'] = "Erro";
	$_SESSION['msg']['mensagem'] = "Erro ao duplicar orçamento!!!";
	$_SESSION['msg']['tipo'] = "error";		
}	

irpara("trOrcamento.php");
	
?>
