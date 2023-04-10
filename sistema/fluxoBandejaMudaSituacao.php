<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

$_SESSION['msg'] = array();

if(isset($_POST['inputFluxoId'])){
	
	$iFluxo = $_POST['inputFluxoId'];
        	
	try{

		$conn->beginTransaction();

		$sql = "SELECT SituaId
				FROM Situacao	
				WHERE SituaChave = '".$_POST['inputFluxoStatus']."'";
		$result = $conn->query($sql);
		$row = $result->fetch(PDO::FETCH_ASSOC);

		if ($_POST['inputFluxoStatus'] == 'NAOLIBERADO'){
			$motivo = $_POST['inputMotivo'];
			$msg = "Fluxo operacional não liberado!";
		} else{
			$motivo = NULL;
			$msg = "Fluxo operacional liberado!";
		}
		
		$sql = "UPDATE FluxoOperacional SET FlOpeStatus = :bStatus, FlOpeUsuarioAtualizador = :iUsuario
				WHERE FlOpeId = :iFluxo";
		$result = $conn->prepare($sql);
		$result->bindParam(':bStatus', $row['SituaId']);
		$result->bindParam(':iUsuario', $_SESSION['UsuarId']);
		$result->bindParam(':iFluxo', $iFluxo);
		$result->execute();
		
		$sql = "UPDATE Bandeja SET BandeStatus = :bStatus, BandeMotivo = :sMotivo, BandeUsuarioAtualizador = :iUsuario
				WHERE BandeId = :iBandeja";
		$result = $conn->prepare($sql);
		$result->bindParam(':bStatus', $row['SituaId']);
		$result->bindParam(':sMotivo', $motivo);
		$result->bindParam(':iUsuario', $_SESSION['UsuarId']);
		$result->bindParam(':iBandeja', $_POST['inputBandejaId']);
		$result->execute();

		$sql = "SELECT ParamValorAtualizadoFluxo
				FROM Parametro
				WHERE ParamEmpresa = " . $_SESSION['EmpreId'];
		$result = $conn->query($sql);
		$rowParametro = $result->fetch(PDO::FETCH_ASSOC);

		$fCustoFinal = 0;
		$fPrecoVenda = 0;		

		//Se o parâmetro diz que o Valor do Produto/Serviço será atualizado a partir do Fluxo, tais valores devem ser atualizados		
		if ($rowParametro['ParamValorAtualizadoFluxo'] && $_POST['inputFluxoStatus'] == 'LIBERADO'){
			
			$sql = "SELECT FOXPrProduto, FOXPrValorUnitario 
					FROM FluxoOperacionalXProduto
					WHERE FOXPrFluxoOperacional = ".$iFluxo;
			$result = $conn->query($sql);
			$rowProduto = $result->fetchAll(PDO::FETCH_ASSOC);  

			foreach ($rowProduto as $item){

				$sql = "SELECT ProduOutrasDespesas, ProduMargemLucro 
						FROM Produto
						WHERE ProduId = ".$item['FOXPrProduto'];
				$result = $conn->query($sql);
				$rowAtualizaProduto = $result->fetch(PDO::FETCH_ASSOC);
				
				$fCustoFinal = $item['FOXPrValorUnitario'] + $rowAtualizaProduto['ProduOutrasDespesas'];
				$fPrecoVenda = $fCustoFinal + ($rowAtualizaProduto['ProduMargemLucro'] * $fCustoFinal) / 100; 				

				$sql = "UPDATE Produto SET ProduValorCusto = :fCusto, ProduCustoFinal = :fCustoFinal, 
						ProduValorVenda = :fVenda, ProduUsuarioAtualizador = :iUsuario
						WHERE ProduId = :iProduto";
				$result = $conn->prepare($sql);
				$result->bindParam(':fCusto', $item['FOXPrValorUnitario']);
				$result->bindParam(':fCustoFinal', $fCustoFinal);
				$result->bindParam(':fVenda', $fPrecoVenda);				
				$result->bindParam(':iUsuario', $_SESSION['UsuarId']);
				$result->bindParam(':iProduto', $item['FOXPrProduto']);
				$result->execute();
			}

			$sql = "SELECT FOXSrServico, FOXSrValorUnitario 
					FROM FluxoOperacionalXServico
					WHERE FOXSrFluxoOperacional = ".$iFluxo;
			$result = $conn->query($sql);
			$rowServico = $result->fetchAll(PDO::FETCH_ASSOC);  

			$fCustoFinal = 0;
			$fPrecoVenda = 0;			

			foreach ($rowServico as $item){

				$sql = "SELECT ServiOutrasDespesas, ServiMargemLucro 
						FROM Servico
						WHERE ServiId = ".$item['FOXSrServico'];
				$result = $conn->query($sql);
				$rowAtualizaServico = $result->fetch(PDO::FETCH_ASSOC);
				
				$fCustoFinal = $item['FOXSrValorUnitario'] + $rowAtualizaServico['ServiOutrasDespesas'];
				$fPrecoVenda = $fCustoFinal + ($rowAtualizaServico['ServiMargemLucro'] * $fCustoFinal) / 100; 	

				$sql = "UPDATE Servico SET ServiValorCusto = :fCusto, ServiCustoFinal = :fCustoFinal, 
						ServiValorVenda = :fVenda, ServiUsuarioAtualizador = :iUsuario
						WHERE ServiId = :iServico";
				$result = $conn->prepare($sql);
				$result->bindParam(':fCusto', $item['FOXSrValorUnitario']);
				$result->bindParam(':fCustoFinal', $fCustoFinal);
				$result->bindParam(':fVenda', $fPrecoVenda);				
				$result->bindParam(':iUsuario', $_SESSION['UsuarId']);
				$result->bindParam(':iServico', $item['FOXSrServico']);
				$result->execute();
			}			
		}

		$conn->commit();

		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] =  $msg;
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {

		$conn->rollback();
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro na liberação do fluxo operacional!!!";
		$_SESSION['msg']['tipo'] = "error";
		
		echo 'Error: ' . $e->getMessage();exit;
	}
}

irpara("index.php");

?>
