<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Ordem de Compra';

include('global_assets/php/conexao.php');

$tipoRequest = $_POST['tipoRequest'];

/*
esse arquivo é único para atendimento, onde deve passar como parametro o campo "tipoRequest"
que irá indicar qual ação será executada
*/

try{
	$iUnidade = $_SESSION['UnidadeId'];
	$usuarioId = $_SESSION['UsuarId'];

	if($tipoRequest == 'ATENDIMENTOS'){		
		$sql = "SELECT AtendId, ClienNome, AtendNumRegistro
				FROM Atendimento
				JOIN Cliente on ClienId = AtendCliente
				JOIN Situacao on SituaId = AtendSituacao
				JOIN AtendimentoModalidade on AtModId = AtendModalidade
				LEFT JOIN CaixaRecebimento on CxRecAtendimento = AtendId
				WHERE AtendUnidade = ".$_SESSION['UnidadeId']." and AtendId not in (SELECT CxRecAtendimento FROM CaixaRecebimento)
				AND SituaChave = 'LIBERADO'
				AND AtModTipoRecebimento = 'À Vista'                                                      
				ORDER BY ClienNome";
		$result = $conn->query($sql);
		$rowFornecedor = $result->fetchAll(PDO::FETCH_ASSOC);
		
		$array = [];
		foreach($rowFornecedor as $item){
			array_push($array, [
				'id'=>$item['AtendId'],
				'registro'=>$item['AtendNumRegistro'],
				'nome'=>$item['ClienNome'],
			]);
		}
		echo json_encode($array);
	} elseif ($tipoRequest == 'FINALIZARATENDIMENTO') {

		$nomeCaixa = $_POST['inputCaixaNome'];
		$aberturaCaixaId = $_POST['inputCaixaId'];
		$dataHora = date("Y-m-d H:i:s");
		$atendimentoId = $_POST['inputAtendimentoId'];
		$valor = gravaValor($_POST['inputValorTotal']);
		$desconto = gravaValor($_POST['inputDesconto']);
		$valorFinal = gravaValor($_POST['inputValorFinal']);
		$formaPagamentoId = $_POST['inputFormaPagamento'];
		$numeroCheque = $_POST['inputNumeroCheque'] != '' ? $_POST['inputNumeroCheque'] : null;
		$valorCheque = $_POST['inputValorCheque'] != '' ? gravaValor($_POST['inputValorCheque']) : null; 
		$dataEmissaoCheque = $_POST['inputDataEmissaoCheque'] != '' ?  $_POST['inputDataEmissaoCheque'] : null;
		$dataVencimentoCheque = $_POST['inputDataVencimentoCheque'] != '' ?  $_POST['inputDataVencimentoCheque'] : null;
		$bancoCheque = $_POST['inputBancoCheque'] != '' ?  $_POST['inputBancoCheque'] : null;
		$agenciaCheque = $_POST['inputAgenciaCheque'] != '' ?  $_POST['inputAgenciaCheque'] : null;
		$contaCheque = $_POST['inputContaCheque'] != '' ?  $_POST['inputContaCheque'] : null;
		$nomeCheque = $_POST['inputNomeCheque'] != '' ?  $_POST['inputNomeCheque'] : null;
		$cpfCheque = $_POST['inputCpfCheque'] != '' ?  $_POST['inputCpfCheque'] : null;
		$parcelas = $_POST['inputParcelas'] != '' ?  $_POST['inputParcelas'] : null;

		$sql_saldoInicial    = "SELECT CxRecId 
								FROM CaixaRecebimento
								WHERE CxRecAtendimento = " . $atendimentoId . "";
		$resultSaldoInicial  = $conn->query($sql_saldoInicial);

		if($rowSaldoInicial = $resultSaldoInicial->fetch(PDO::FETCH_ASSOC)) {

			echo json_encode([
				'titulo' => 'Erro',
				'status' => 'error',
				'menssagem' => 'Esse atendimento já foi registrado em outro caixa!!!'
			]);

		}else {
			if($parcelas == 1) {
				$sql = "SELECT SituaId
						FROM Situacao
						WHERE SituaChave = 'RECEBIDO'";
				$result = $conn->query($sql);
				$row = $result->fetch(PDO::FETCH_ASSOC);
				$iStatus = $row['SituaId'];		

				try{
					$conn->beginTransaction();

					$sql = "INSERT INTO CaixaRecebimento (CxRecCaixaAbertura, CxRecDataHora, CxRecAtendimento, CxRecValor, CxRecDesconto, CxRecValorTotal, 
														CxRecFormaPagamento, CxRecNumCheque, CxRecValorCheque, CxRecDtEmissaoCheque, CxRecDtVencimentoCheque, 
														CxRecBancoCheque, CxRecAgenciaCheque, CxRecContaCheque, CxRecNomeCheque, CxRecCpfCheque, CxRecStatus, CxRecUnidade)
							VALUES (:iAberturaCaixa, :sDataHora, :iAtendimento, :fValor, :fDesconto, :fValorTotal, :iFormaPagamento, :sNumCheque, 
							:fValorCheque, :sDtEmissaoCheque, :sDtVencimentoCheque, :iBancoCheque, :sAgenciaCheque, :sContaCheque, :sNomeCheque, :sCpfCheque, :bStatus, :iUnidade)";
					$result = $conn->prepare($sql);
							
					$result->execute(array(
						':iAberturaCaixa' => $aberturaCaixaId,
						':sDataHora' => $dataHora,
						':iAtendimento' => $atendimentoId,
						':fValor' => $valor,
						':fDesconto' => $desconto,
						':fValorTotal' => $valorFinal,
						':iFormaPagamento' => $formaPagamentoId,
						':sNumCheque' => $numeroCheque,
						':fValorCheque' => $valorCheque,
						':sDtEmissaoCheque' => $dataEmissaoCheque,
						':sDtVencimentoCheque' => $dataVencimentoCheque,
						':iBancoCheque' => $bancoCheque,
						':sAgenciaCheque' => $agenciaCheque,
						':sContaCheque' => $contaCheque,
						':sNomeCheque' => $nomeCheque,
						':sCpfCheque' => $cpfCheque,
						':bStatus' => $iStatus,
						':iUnidade' => $_SESSION['UnidadeId'],
					));

					//Consulta o saldo de recebimento atual que está na abertura do caixa
					$sql = "SELECT CxAbeTotalRecebido
							FROM CaixaAbertura
							WHERE CxAbeId = $aberturaCaixaId";
					$result = $conn->query($sql);
					$row = $result->fetch(PDO::FETCH_ASSOC);
					$valorFinal = $row['CxAbeTotalRecebido'] + $valorFinal;	

					$sql = "UPDATE CaixaAbertura SET CxAbeTotalRecebido = :fValorRecebido
							WHERE CxAbeId = :iCaixaAberturaId";
					$result = $conn->prepare($sql);
							
					$result->execute(array(
						':fValorRecebido' => $valorFinal,
						':iCaixaAberturaId' => $aberturaCaixaId 
					));
			
					$sql = "SELECT SituaId FROM Situacao WHERE SituaChave = 'EMESPERA'";
					$result = $conn->query($sql);
					$row = $result->fetch(PDO::FETCH_ASSOC);
					$newStatusAtend = $row['SituaId'];	
					
					$sql = "UPDATE Atendimento SET AtendSituacao = :sNewStatusAtend
							WHERE AtendId = :iAtendimento";
					$result = $conn->prepare($sql);
					$result->execute(array(
						':sNewStatusAtend' => $newStatusAtend,
						':iAtendimento' => $atendimentoId
					));

					$conn->commit();

					echo json_encode([
						'titulo' => 'Sucesso',
						'status' => 'success',
						'menssagem' => 'Atendimento incluído ao caixa!!!',
					]);
								
				} catch(PDOException $e) {

					$conn->rollback();

					echo json_encode([
						'titulo' => 'Erro',
						'tipo' => 'error',
						'menssagem' => 'Erro aos cadastrar atendimento no caixa!!!',
						'error' => $e->getMessage()
					]);
				}
			}else {
				echo json_encode([
					'titulo' => 'Erro',
					'status' => 'error',
					'menssagem' => 'Pagamento parcelado ainda não está disponível!!!'
				]);
			}
			
		}
				
	}
}catch(PDOException $e) {
	$msg = '';
	switch($tipoRequest){
		case 'ATENDIMENTOS': $msg = 'Erro ao carregar atendimentos do caixa';break;
		default: $msg = "Erro ao executar ação COD.: $tipoRequest";break;
	}

	echo json_encode([
		'titulo' => 'Caixa PDV',
		'tipo' => 'error',
		'menssagem' => $msg,
		'sql' => $sql,
		'error' => $e->getMessage()
	]);
}
