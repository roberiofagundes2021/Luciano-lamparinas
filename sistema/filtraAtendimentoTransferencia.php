<?php 

include_once("sessao.php");

$_SESSION['PaginaAtual'] = 'Ordem de Compra';

include('global_assets/php/conexao.php');

$tipoRequest = $_POST['tipoRequest'];

// OBS.: Adicionar condicionais para trazer dados da unidade específica

/*
esse arquivo é único para atendimento, onde deve passar como parametro o campo "tipoRequest"
que irá indicar qual ação será executada
*/

try{
	$iUnidade = $_SESSION['UnidadeId'];
	$usuarioId = $_SESSION['UsuarId'];

	// feito consultas para buscar de acordo com a classificação do atendimento
	// (ATENDIMENTOSAMBULATORIAIS, ATENDIMENTOSHOSPITALARES, ATENDIMENTOSELETIVOS)
	if($tipoRequest == 'MODELO'){
		$sql = "SELECT AtModConteudo
				FROM AtendimentoModelo
				WHERE AtModId = $_POST[iAtendimentoModelo] and AtModUnidade = $iUnidade";
		$result = $conn->query($sql);
		$rowTipo = $result->fetch(PDO::FETCH_ASSOC);
	
		echo json_encode($rowTipo['AtModConteudo']);
	}

}catch(PDOException $e) {
	$msg = '';
	switch($tipoRequest){
		case 'MUDARSITUACAO': $msg = 'Erro ao atualizar situação do atendimentos!!';break;
		default: $msg = 'Erro ao executar ação!!';break;
	}
	echo json_encode([
		'titulo' => 'Transferência',
		'status' => 'error',
		'menssagem' => $msg,
		'error' => $e->getMessage(),
		'sql' => $sql
	]);
}
