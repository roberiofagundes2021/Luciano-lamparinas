<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

$_SESSION['msg'] = array();

if(isset($_POST['inputOrdemCompraId'])){
	
	$iOrdemCompra = $_POST['inputOrdemCompraId'];
	
	try{

		$conn->beginTransaction();

		$sql = "SELECT SituaId
				FROM Situacao	
				WHERE SituaChave = '".$_POST['inputOrdemCompraStatus']."'";
		$result = $conn->query($sql);
		$row = $result->fetch(PDO::FETCH_ASSOC);        	

		if ($_POST['inputOrdemCompraStatus'] == 'NAOLIBERADOCENTRO'){
			$motivo = $_POST['inputMotivo'];
			$msg = "Ordem de Compra não liberada!";
		} else{
			$motivo = NULL;
			$msg = "Ordem de Compra liberada!";
		}
		
		$sql = "UPDATE OrdemCompra SET OrComSituacao = :bStatus, OrComUsuarioAtualizador = :iUsuario
				WHERE OrComId = :iOrdemCompra";
		$result = $conn->prepare($sql);
		$result->bindParam(':bStatus', $row['SituaId']);
		$result->bindParam(':iUsuario', $_SESSION['UsuarId']);
		$result->bindParam(':iOrdemCompra', $iOrdemCompra);
		$result->execute();
		
		$sql = "UPDATE Bandeja SET BandeStatus = :bStatus, BandeMotivo = :sMotivo, BandeUsuarioAtualizador = :iUsuario
				WHERE BandeId = :iBandeja";
		$result = $conn->prepare($sql);
		$result->bindParam(':bStatus', $row['SituaId']);
		$result->bindParam(':sMotivo', $motivo);
		$result->bindParam(':iUsuario', $_SESSION['UsuarId']);
		$result->bindParam(':iBandeja', $_POST['inputBandejaId']);
		$result->execute();

		$sql = "SELECT ParamValorAtualizadoOrdemCompra
				FROM Parametro
				WHERE ParamEmpresa = " . $_SESSION['EmpreId'];
		$result = $conn->query($sql);
		$rowParametro = $result->fetch(PDO::FETCH_ASSOC);

		$fCustoFinal = 0;
		$fPrecoVenda = 0;

		//Se o parâmetro diz que o Valor do Produto/Serviço será atualizado a partir da Ordem de Compra, tais valores devem ser atualizados		
		if ($rowParametro['ParamValorAtualizadoOrdemCompra'] && $_POST['inputOrdemCompraStatus'] == 'LIBERADO'){
			
			$sql = "SELECT OCXPrProduto, OCXPrValorUnitario 
					FROM OrdemCompraXProduto
					WHERE OCXPrOrdemCompra = ".$iOrdemCompra;
			$result = $conn->query($sql);
			$rowProduto = $result->fetchAll(PDO::FETCH_ASSOC); 

			foreach ($rowProduto as $item){
				
				$sql = "SELECT ProduOutrasDespesas, ProduMargemLucro 
						FROM Produto
						WHERE ProduId = ".$item['OCXPrProduto'];
				$result = $conn->query($sql);
				$rowAtualizaProduto = $result->fetch(PDO::FETCH_ASSOC);
				
				$fCustoFinal = $item['OCXPrValorUnitario'] + $rowAtualizaProduto['ProduOutrasDespesas'];
				$fPrecoVenda = $fCustoFinal + ($rowAtualizaProduto['ProduMargemLucro'] * $fCustoFinal) / 100; 

				$sql = "UPDATE Produto SET ProduValorCusto = :fCusto, ProduCustoFinal = :fCustoFinal, 
						ProduValorVenda = :fVenda, ProduUsuarioAtualizador = :iUsuario
						WHERE ProduId = :iProduto";
				$result = $conn->prepare($sql);
				$result->bindParam(':fCusto', $item['OCXPrValorUnitario']);
				$result->bindParam(':fCustoFinal', $fCustoFinal);
				$result->bindParam(':fVenda', $fPrecoVenda);
				$result->bindParam(':iUsuario', $_SESSION['UsuarId']);
				$result->bindParam(':iProduto', $item['OCXPrProduto']);
				$result->execute();
			}

			$sql = "SELECT OCXSrServico, OCXSrValorUnitario 
					FROM OrdemCompraXServico
					WHERE OCXSrOrdemCompra = ".$iOrdemCompra;
			$result = $conn->query($sql);
			$rowServico = $result->fetchAll(PDO::FETCH_ASSOC);			

			$fCustoFinal = 0;
			$fPrecoVenda = 0;			

			foreach ($rowServico as $item){
				
				$sql = "SELECT ServiOutrasDespesas, ServiMargemLucro 
						FROM Servico
						WHERE ServiId = ".$item['OCXSrServico'];
				$result = $conn->query($sql);
				$rowAtualizaServico = $result->fetch(PDO::FETCH_ASSOC);
				
				$fCustoFinal = $item['OCXSrValorUnitario'] + $rowAtualizaServico['ServiOutrasDespesas'];
				$fPrecoVenda = $fCustoFinal + ($rowAtualizaServico['ServiMargemLucro'] * $fCustoFinal) / 100; 				

				$sql = "UPDATE Servico SET ServiValorCusto = :fCusto, ServiCustoFinal = :fCustoFinal, 
						ServiValorVenda = :fVenda, ServiUsuarioAtualizador = :iUsuario
						WHERE ServiId = :iServico";
				$result = $conn->prepare($sql);
				$result->bindParam(':fCusto', $item['OCXSrValorUnitario']);
				$result->bindParam(':fCustoFinal', $fCustoFinal);
				$result->bindParam(':fVenda', $fPrecoVenda);				
				$result->bindParam(':iUsuario', $_SESSION['UsuarId']);
				$result->bindParam(':iServico', $item['OCXSrServico']);
				$result->execute();
			}			
		}

		$conn->commit();

		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = $msg;
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {

		$conn->rollback();
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro na liberação da ordem compra!!!";
		$_SESSION['msg']['tipo'] = "error";
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("index.php");

?>
