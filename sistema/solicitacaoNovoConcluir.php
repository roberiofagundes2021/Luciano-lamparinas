<?php

include_once("sessao.php");
include('global_assets/php/conexao.php');

function gerarNumeracao()
{
	include('global_assets/php/conexao.php');

	$sql = "SELECT MAX(SolicId)
	        FROM Solicitacao
			WHERE SolicUnidade = " . $_SESSION['UnidadeId'] . "
		   ";
	$result = $conn->query($sql);
	$row = $result->fetch(PDO::FETCH_ASSOC);

	if ($row[""]) {
		$sql = "SELECT SolicNumero
	            FROM Solicitacao
			    WHERE SolicId = " . $row[""] . " and SolicUnidade = " . $_SESSION['UnidadeId'] . "
		   ";
		$result = $conn->query($sql);
		$rowSolic = $result->fetch(PDO::FETCH_ASSOC);

		if (count($rowSolic) >= 1) {
			$temp = explode('/', $rowSolic['SolicNumero']);

			$int = intval($temp[0]);

			if ($int <= 9) {
				return '0' . ++$int . '/' . date('Y') . '';
			} else {
				return '' . ++$int . '/' . date('Y') . '';
			}
		} 
	} else {
		return '01/' . date('Y') . '';
	}
}

$newArrayItens = [];

if(isset($_SESSION['Carrinho'])){
	foreach($_SESSION['Carrinho'] as $key => $value) {
		if ($value['quantidade'] && $value['quantidade'] > 0) {
			array_Push($newArrayItens, $value);
		}
	}
}

if (COUNT($newArrayItens)) {

	try {

		$conn->beginTransaction();

		// Selecionando dados do setor do usuário
		$sql = "SELECT SetorId, SetorNome
                FROM EmpresaXUsuarioXPerfil 
				JOIN UsuarioXUnidade on UsXUnEmpresaUsuarioPerfil = EXUXPId
                JOIN Setor on SetorId = UsXUnSetor
		        WHERE EXUXPUsuario = " . $_SESSION['UsuarId'] . " and UsXUnUnidade =  " . $_SESSION['UnidadeId'] . "
		       ";
		$result = $conn->query($sql);
		$Setor = $result->fetch(PDO::FETCH_ASSOC);

		$sql = "SELECT SituaId
		        FROM Situacao
		        WHERE SituaChave = 'AGUARDANDOLIBERACAO'
		       ";
		$result = $conn->query($sql);
		$Situacao = $result->fetch(PDO::FETCH_ASSOC);

		$sql = "SELECT PerfiId
				FROM Perfil
				Where PerfiChave = 'ALMOXARIFADO' and PerfiUnidade = " . $_SESSION['UnidadeId'];
		$result = $conn->query($sql);
		$rowPerfil = $result->fetch(PDO::FETCH_ASSOC);

		$soliObservacao = '';
		if (isset($_POST['txtObservacao'])) {
			$soliObservacao = $_POST['txtObservacao'];
		}

		$sql = "INSERT INTO Solicitacao (SolicNumero, SolicData, SolicObservacao, SolicSetor, SolicSolicitante, 
				SolicSituacao, SolicUsuarioAtualizador, SolicUnidade)
				VALUES (:iNumero, :iData, :iObservacao, :iSetor, :iSolicitante, :iSituacao, :iUsuarioAtualizador, :iUnidade)";
		$result = $conn->prepare($sql);
		// var_dump($Perfil['PerfiId']);
		// var_dump($_SESSION['UsuarId']);
		// var_dump($situaId);
		//  var_dump($_SESSION['EmpreId']);
		$result->execute(array(
			':iNumero' => gerarNumeracao(),
			':iData' => date('Y-m-d'),
			':iObservacao' => $soliObservacao,
			':iSetor' => $Setor['SetorId'],
			':iSolicitante' => $_SESSION['UsuarId'],
			':iSituacao' => $Situacao['SituaId'],
			':iUsuarioAtualizador' => $_SESSION['UsuarId'],
			':iUnidade' => $_SESSION['UnidadeId']
		));

		$SolicitacaoId = $conn->lastInsertId();

		foreach ($newArrayItens as $key => $value) {
			if($value['quantidade'] != '' && intval($value['quantidade']) > 0){
				if($value['type']=='P'){
					$sql = "INSERT INTO SolicitacaoXProduto
						(SlXPrSolicitacao, SlXPrProduto, SlXPrQuantidade, SlXPrUsuarioAtualizador, SlXPrUnidade)
						VALUES ($SolicitacaoId, $value[id], $value[quantidade], $_SESSION[UsuarId], $_SESSION[UnidadeId])";
					$conn->query($sql);
				}else{
					$sql = "INSERT INTO SolicitacaoXServico
						(SlXSrSolicitacao, SlXSrServico, SlXSrQuantidade, SlXSrUsuarioAtualizador, SlXSrUnidade)
						VALUES ($SolicitacaoId, $value[id], $value[quantidade], $_SESSION[UsuarId], $_SESSION[UnidadeId])";
					$conn->query($sql);
				}
			}
		}

		$sIdentificacao = 'Solicitação de materiais (' . $Setor['SetorNome'] . ')';

		$sql = "INSERT INTO Bandeja (BandeIdentificacao, BandeData, BandeDescricao, BandeURL, BandeSolicitante, 
				BandeSolicitanteSetor, BandeTabela, BandeTabelaId, BandeStatus, BandeUsuarioAtualizador, BandeUnidade, BandePerfil)
				VALUES (:sIdentificacao, :dData, :sDescricao, :sURL, :iSolicitante, :iSolicitanteSetor, :sTabela, 
				:iTabelaId, :iStatus, :iUsuarioAtualizador, :iUnidade, :sPerfil)";
		$result = $conn->prepare($sql);

		$result->execute(array(
			':sIdentificacao' => $sIdentificacao,
			':dData' => date("Y-m-d"),
			':sDescricao' => 'Liberar Solicitação',
			':sURL' => '',
			':iSolicitante' => $_SESSION['UsuarId'],
			':iSolicitanteSetor' => $Setor['SetorId'],
			':sTabela' => 'Solicitacao',
			':iTabelaId' => $SolicitacaoId,
			':iStatus' => $Situacao['SituaId'],
			':iUsuarioAtualizador' => $_SESSION['UsuarId'],
			':iUnidade' => $_SESSION['UnidadeId'],
			':sPerfil' => 'ALMOXARIFADO',
		));

		$BandejaId = $conn->lastInsertId();


		$sql = "INSERT INTO BandejaXPerfil (BnXPeBandeja, BnXPePerfil, BnXPeUnidade)
							VALUES (:iBandeja, :iPerfil, :iUnidade)";
		$result = $conn->prepare($sql);

		$result->execute(array(
			':iBandeja' => $BandejaId,
			':iPerfil' => $rowPerfil['PerfiId'],
			':iUnidade' => $_SESSION['UnidadeId']
		));

		$conn->commit();

		unset($_SESSION['Carrinho']);

		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Solicitação realizada!!!";
		$_SESSION['msg']['tipo'] = "success";
	} catch (PDOException $e) {

		$conn->rollback();

		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao concluir solicitação!!!";
		$_SESSION['msg']['tipo'] = "error";

		echo 'Error: ' . $e->getMessage();
		die;
	}

	irpara("solicitacao.php");
}
