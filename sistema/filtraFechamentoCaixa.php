<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Ordem de Compra';

include('global_assets/php/conexao.php');

/*
esse arquivo é único para fechamento de caixa, onde deve passar como parametro o campo "tipoRequest"
que irá indicar qual ação será executada.
*/

$tipoRequest = $_POST['tipoRequest'];

try{
	$iUnidade = $_SESSION['UnidadeId'];
	$usuarioId = $_SESSION['UsuarId'];

	if($tipoRequest == 'CAIXA'){
		$inicio = isset($_POST['inicio'])?$_POST['inicio']:'';
		$fim = isset($_POST['fim'])?$_POST['fim']:'';
		$operador = isset($_POST['operador'])?$_POST['operador']:'';
		$caixa = isset($_POST['caixa'])?$_POST['caixa']:'';

		$sql = "SELECT CxAbeId, CxAbeDataHoraFechamento, CxAbeSaldoFinal, CaixaNome, UsuarNome
		FROM CaixaAbertura
		JOIN Caixa ON CaixaId = CxAbeCaixa
		JOIN Usuario ON UsuarId = CxAbeOperador
		WHERE CxAbeUnidade = $iUnidade and CxAbeDataHoraFechamento >= '$inicio 00:00:00'";

		if($fim){
			$sql .= " and CxAbeDataHoraFechamento <= '$fim 23:59:59'";
		}
		if($operador){
			$operadores = '(';
			foreach($operador as $item){
				$operadores .= $item.',';
			}
			$operadores = substr($operadores, 0, -1);
			$operadores .= ')';
			
			$sql .= " and CxAbeOperador in $operadores";
		}
		if($caixa){
			$caixas = '(';
			foreach($caixa as $item){
				$caixas .= $item.',';
			}
			$caixas = substr($caixas, 0, -1);
			$caixas .= ')';
			$sql .= " and CxAbeCaixa in $caixas";
		}
		
		$result = $conn->query($sql);
		$row = $result->fetchAll(PDO::FETCH_ASSOC);
		
		$array = [];
		foreach($row as $item){
			$date = date_create($item['CxAbeDataHoraFechamento']);
			$print = "<a style='color: black' href='#' data-caixa='$item[CxAbeId]' class='list-icons-item btnImprimir'><i class='icon-printer2' title='Imprimir'></i></a>";
			$acoes = "<div class='list-icons'>
						$print
					</div>";
			array_push($array, [
				'fechamento' => date_format($date, 'd/m/Y'),
				'saldo' => $item['CxAbeSaldoFinal'],
				'caixa' => $item['CaixaNome'],
				'operador' => $item['UsuarNome'],
				'acao' => $acoes
			]);
		}
		echo json_encode($array);
	}
}catch(PDOException $e) {
	$msg = '';
	switch($tipoRequest){
		case 'CAIXA': $msg = 'Erro ao carregar Fechamento de caixa';break;
		default: $msg = "Erro ao executar ação COD.: $tipoRequest";break;
	}

	echo json_encode([
		'titulo' => 'Fechamento de Caixa',
		'tipo' => 'error',
		'menssagem' => $msg,
		'sql' => $sql,
		'error' => $e->getMessage()
	]);
}
