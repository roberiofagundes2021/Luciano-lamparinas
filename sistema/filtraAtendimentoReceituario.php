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

	if($tipoRequest == 'GETRECEITUARIO'){		
		$sql = "SELECT AtRecReceituario, AtRecHoraFim, AtRecHoraInicio, AtRecDataInicio, AtRecDataFim, AtRecTipoReceituario
		FROM AtendimentoReceituario
		WHERE AtRecId = $_POST[iReceituario]";
		$result = $conn->query($sql);
		$rowReceituario = $result->fetch(PDO::FETCH_ASSOC);
		echo json_encode([
			'receituario'=>$rowReceituario['AtRecReceituario'],
			'tipoReceituario'=>$rowReceituario['AtRecTipoReceituario']
		]);
	}
}catch(PDOException $e) {
	$msg = '';
	switch($tipoRequest){
		case 'AGENDAMENTOS': $msg = 'Erro ao carregar agendamentos';break;
		case 'MUDARSITUACAO': $msg = 'Erro ao atualizar situação do agendamento';break;
		case 'ADDAGENDAMENTO': $msg = 'Erro ao incluir novo agendamento';break;
		case 'ATTAGENDAMENTO': $msg = 'Erro ao atualizar agendamento';break;
		case 'ADDSERVICO': $msg = 'Erro ao incluir novo serviço';break;
		case 'EDITAR': $msg = 'Erro ao atualizar agendamento';break;
		case 'EXCLUI': $msg = 'Erro ao excluir agendamento';break;
		case 'EXCLUISERVICO': $msg = 'Erro ao excluir serviço';break;
		case 'ADDPACIENTENOVO': $msg = 'Erro ao inserir novo paciente';break;
		default: $msg = "Erro ao executar ação COD.: $tipoRequest";break;
	}

	echo json_encode([
		'titulo' => 'Agendamento',
		'tipo' => 'error',
		'menssagem' => $msg,
		'sql' => $sql,
		'error' => $e->getMessage()
	]);
}
