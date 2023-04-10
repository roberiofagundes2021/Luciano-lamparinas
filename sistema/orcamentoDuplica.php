<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Orçamento Duplica';

include('global_assets/php/conexao.php');

if (isset($_POST['inputOrcamentoId'])){

	$sql = "SELECT OrcamId, OrcamTipo, OrcamCategoria, OrcamConteudo, OrcamFornecedor, OrcamStatus
			FROM Orcamento
			WHERE OrcamUnidade = ". $_SESSION['UnidadeId'] ." and OrcamId = ".$_POST['inputOrcamentoId']."";
	$result = $conn->query($sql);
	$rowOrcamento = $result->fetch(PDO::FETCH_ASSOC);
	//$count = count($rowOrcamento);

	
	$sql = "SELECT COUNT(isnull(OrcamNumero,0)) as Numero
			FROM Orcamento
			Where OrcamUnidade = ".$_SESSION['UnidadeId'];
	$result = $conn->query($sql);
	$rowNumero = $result->fetch(PDO::FETCH_ASSOC);		
	
	$sNumero = (int)$rowNumero['Numero'] + 1;
	$sNumero = str_pad($sNumero,6,"0",STR_PAD_LEFT);
		
	$sql = "INSERT INTO Orcamento (OrcamNumero, OrcamTipo, OrcamData, OrcamCategoria, OrcamConteudo,
								   OrcamSolicitante, OrcamStatus, OrcamUsuarioAtualizador, OrcamUnidade)
			VALUES (:sNumero, :sTipo, :dData, :iCategoria, :sConteudo, :iSolicitante, 
					:bStatus, :iUsuarioAtualizador, :iUnidade)";
	$result = $conn->prepare($sql);
	
	$result->execute(array(
					':sNumero' => $sNumero,
					':sTipo' => $rowOrcamento['OrcamTipo'],
					':dData' => gravaData(date('d/m/Y')),
					':iCategoria' => $rowOrcamento['OrcamCategoria'] == '' ? null : $rowOrcamento['OrcamCategoria'],
					':sConteudo' => $rowOrcamento['OrcamConteudo'],
					':iSolicitante' => $_SESSION['UsuarId'],
					':bStatus' => $rowOrcamento['OrcamStatus'],
					':iUsuarioAtualizador' => $_SESSION['UsuarId'],
					':iUnidade' => $_SESSION['UnidadeId']
					));						

	$insertId = $conn->lastInsertId();

	$sql = "SELECT *
			FROM OrcamentoXProduto
			WHERE OrXPrUnidade = ". $_SESSION['UnidadeId'] ." and OrXPrOrcamento = ".$_POST['inputOrcamentoId']."";
	$result = $conn->query($sql);
	$rowProduto = $result->fetchAll(PDO::FETCH_ASSOC);

	foreach ($rowProduto as $item){
		try {
		$sql = "INSERT INTO OrcamentoXProduto (OrXPrOrcamento, OrXPrProduto, OrXPrQuantidade, OrXPrValorUnitario, OrXPrUsuarioAtualizador, OrXPrUnidade)
				VALUES (:iOrcamento, :iProduto, :iQuantidade, :fValorUnitario, :iUsuarioAtualizador, :iUnidade)";
		$result = $conn->prepare($sql);
		
		$result->execute(array(
						':iOrcamento' => $insertId,
						':iProduto' => $item['OrXPrProduto'],
						':iQuantidade' => $item['OrXPrQuantidade'],
						':fValorUnitario' => null,
						':iUsuarioAtualizador' => $_SESSION['UsuarId'],
						':iUnidade' => $_SESSION['UnidadeId']
						));
		} catch(PDOException $e) {
			echo 'Error2: ' . $e->getMessage();die;
		}
	}

	// Select Subcategoria
	$sql = "SELECT SbCatId, SbCatNome
			FROM SubCategoria
			JOIN OrcamentoXSubCategoria on OrXSCSubCategoria = SbCatId
			WHERE SbCatEmpresa = ". $_SESSION['EmpreId'] ." and OrXSCOrcamento = ".$rowOrcamento['OrcamId']."";
	$result = $conn->query($sql);
	$rowSBC = $result->fetchAll(PDO::FETCH_ASSOC);

	$sql = "INSERT INTO OrcamentoXSubCategoria 
							(OrXSCOrcamento, OrXSCSubCategoria, OrXSCUnidade)
						VALUES 
							(:iOrcamento, :iSubCategoria, :iUnidade)";
				$result = $conn->prepare($sql);

				foreach ($rowSBC as $subcategoria){

					$result->execute(array(
									':iOrcamento' => $insertId,
									':iSubCategoria' => $subcategoria['SbCatId'],
									':iUnidade' => $_SESSION['UnidadeId']
									));
				}

	
	$_SESSION['msg']['titulo'] = "Sucesso";
	$_SESSION['msg']['mensagem'] = "Orçamento duplicado!!!";
	$_SESSION['msg']['tipo'] = "success";	

} else {
	$_SESSION['msg']['titulo'] = "Erro";
	$_SESSION['msg']['mensagem'] = "Erro ao duplicar orçamento!!!";
	$_SESSION['msg']['tipo'] = "error";		
}	

irpara("orcamento.php");
	
?>
