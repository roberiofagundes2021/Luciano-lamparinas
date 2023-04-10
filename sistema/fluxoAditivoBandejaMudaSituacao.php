<?php

include_once("sessao.php");

include('global_assets/php/conexao.php');

$_SESSION['msg'] = array();

if (isset($_POST['inputAditivoId'])) {

	$iAditivo = $_POST['inputAditivoId'];

	$sql = "SELECT AditiFluxoOperacional, AditiStatusFluxo
            FROM Aditivo
            WHERE AditiId = " . $iAditivo;
	$result = $conn->query($sql);
	$rowFluxo = $result->fetch(PDO::FETCH_ASSOC);

	$iFluxo = $rowFluxo['AditiFluxoOperacional'];

	try {

		$conn->beginTransaction();

		$sql = "SELECT SituaId
				FROM Situacao	
				WHERE SituaChave = '" . $_POST['inputAditivoStatus'] . "'";
		$result = $conn->query($sql);
		$row = $result->fetch(PDO::FETCH_ASSOC);

		if ($_POST['inputAditivoStatus'] == 'NAOLIBERADO') {
			$motivo = $_POST['inputMotivo'];
			$msg = "Fluxo operacional não liberado!";
		} else {
			$motivo = NULL;
			$msg = "Fluxo operacional liberado!";
		}

// Se o aditivo não é liberado, o fluxo volta ao status armazenado em AditiStatusFluxo
		if ($_POST['inputAditivoStatus'] != 'NAOLIBERADO') {

			$sql = "UPDATE FluxoOperacional SET FlOpeStatus = :bStatus, FlOpeUsuarioAtualizador = :iUsuario
			WHERE FlOpeId = :iFluxo";
			$result = $conn->prepare($sql);
			$result->bindParam(':bStatus', $row['SituaId']);
			$result->bindParam(':iUsuario', $_SESSION['UsuarId']);
			$result->bindParam(':iFluxo', $iFluxo);
			$result->execute();
		} else {

			$sql = "SELECT SituaId
					FROM Situacao	
					WHERE SituaChave = 'INATIVO' ";
		    $result = $conn->query($sql);
		    $rowSitua = $result->fetch(PDO::FETCH_ASSOC);

			$sql = "UPDATE FluxoOperacional SET FlOpeStatus = :bStatus, FlOpeUsuarioAtualizador = :iUsuario
			WHERE FlOpeId = :iFluxo";
			$result = $conn->prepare($sql);
			$result->bindParam(':bStatus', $rowFluxo['AditiStatusFluxo']);
			$result->bindParam(':iUsuario', $_SESSION['UsuarId']);
			$result->bindParam(':iFluxo', $iFluxo);
			$result->execute();

			$sql = "UPDATE Aditivo SET AditiStatus = :bStatus, AditiUsuarioAtualizador = :iUsuario
					WHERE AditiId = :iAditivo";
			$result = $conn->prepare($sql);
			$result->bindParam(':bStatus', $rowSitua['SituaId']);
			$result->bindParam(':iUsuario', $_SESSION['UsuarId']);
			$result->bindParam(':iAditivo', $iAditivo);
			$result->execute();
		}


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
		if ($rowParametro['ParamValorAtualizadoFluxo'] && $_POST['inputAditivoStatus'] == 'LIBERADO') {

			$sql = "SELECT AdXPrProduto, AdXPrValorUnitario 
					FROM AditivoXProduto
					WHERE AdXPrAditivo = " . $iAditivo;
			$result = $conn->query($sql);
			$rowProduto = $result->fetchAll(PDO::FETCH_ASSOC);

			foreach ($rowProduto as $item) {

				$sql = "SELECT ProduOutrasDespesas, ProduMargemLucro 
						FROM Produto
						WHERE ProduId = " . $item['AdXPrProduto'];
				$result = $conn->query($sql);
				$rowAtualizaProduto = $result->fetch(PDO::FETCH_ASSOC);

				$fCustoFinal = $item['AdXPrValorUnitario'] + $rowAtualizaProduto['ProduOutrasDespesas'];
				$fPrecoVenda = $fCustoFinal + ($rowAtualizaProduto['ProduMargemLucro'] * $fCustoFinal) / 100;

				$sql = "UPDATE Produto SET ProduValorCusto = :fCusto, ProduCustoFinal = :fCustoFinal, 
						ProduValorVenda = :fVenda, ProduUsuarioAtualizador = :iUsuario
						WHERE ProduId = :iProduto";
				$result = $conn->prepare($sql);
				$result->bindParam(':fCusto', $item['AdXPrValorUnitario']);
				$result->bindParam(':fCustoFinal', $fCustoFinal);
				$result->bindParam(':fVenda', $fPrecoVenda);
				$result->bindParam(':iUsuario', $_SESSION['UsuarId']);
				$result->bindParam(':iProduto', $item['AdXPrProduto']);
				$result->execute();
			}

			$sql = "SELECT AdXSrServico, AdXSrValorUnitario 
					FROM AditivoXServico
					WHERE AdXSrAditivo = " . $iAditivo;
			$result = $conn->query($sql);
			$rowServico = $result->fetchAll(PDO::FETCH_ASSOC);

			$fCustoFinal = 0;
			$fPrecoVenda = 0;

			foreach ($rowServico as $item) {

				$sql = "SELECT ServiOutrasDespesas, ServiMargemLucro 
						FROM Servico
						WHERE ServiId = " . $item['AdXSrServico'];
				$result = $conn->query($sql);
				$rowAtualizaServico = $result->fetch(PDO::FETCH_ASSOC);

				$fCustoFinal = $item['AdXSrValorUnitario'] + $rowAtualizaServico['ServiOutrasDespesas'];
				$fPrecoVenda = $fCustoFinal + ($rowAtualizaServico['ServiMargemLucro'] * $fCustoFinal) / 100;

				$sql = "UPDATE Servico SET ServiValorCusto = :fCusto, ServiCustoFinal = :fCustoFinal, 
						ServiValorVenda = :fVenda, ServiUsuarioAtualizador = :iUsuario
						WHERE ServiId = :iServico";
				$result = $conn->prepare($sql);
				$result->bindParam(':fCusto', $item['AdXSrValorUnitario']);
				$result->bindParam(':fCustoFinal', $fCustoFinal);
				$result->bindParam(':fVenda', $fPrecoVenda);
				$result->bindParam(':iUsuario', $_SESSION['UsuarId']);
				$result->bindParam(':iServico', $item['AdXSrServico']);
				$result->execute();
			}
		}

		$conn->commit();

		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = $msg;
		$_SESSION['msg']['tipo'] = "success";
	} catch (PDOException $e) {

		$conn->rollback();

		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro na liberação do fluxo operacional!!!";
		$_SESSION['msg']['tipo'] = "error";

		echo 'Error: ' . $e->getMessage();
		exit;
	}
}

irpara("index.php");
