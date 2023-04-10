<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Tabela de Gastos';

include('global_assets/php/conexao.php');

if(!isset($_SESSION['atendimentoTabelaProdutos'])){
	$_SESSION['atendimentoTabelaProdutos'] = [];
}
if(!isset($_SESSION['atendimentoTabelaServicos'])){
	$_SESSION['atendimentoTabelaServicos'] = [];
}


$tipoRequest = $_POST['tipoRequest'];
$iUnidade = $_SESSION['UnidadeId'];
$usuarioId = $_SESSION['UsuarId'];
$iEmpresa = $_SESSION['EmpreId'];

if($tipoRequest == 'PROCEDIMENTOS'){
	
	$idSubGrupo = $_POST['idSubGrupo'];

	$sql = "SELECT SrVenId, SrVenNome
			FROM ServicoVenda
			JOIN Situacao on SituaId = SrVenStatus
			WHERE SrVenSubGrupo = $idSubGrupo
			AND SituaChave = 'ATIVO' and SrVenUnidade = $iUnidade
			ORDER BY SrVenNome ASC";
	$result = $conn->query($sql);
	$rows = $result->fetchAll(PDO::FETCH_ASSOC);
	
	$array = [];
	foreach($rows as $item){
		array_push($array, [
			'id' => $item['SrVenId'],
			'nome' => $item['SrVenNome']
		]);
	}

	echo json_encode($array);
} elseif($tipoRequest == 'PRODUTOS'){		
	
	$sql = "SELECT ProduId, ProduNome
			FROM Produto
			
			WHERE ProduStatus = 1
			AND ProduEmpresa = $iEmpresa
			AND ProduValorVenda IS NOT NULL
			ORDER BY ProduNome ASC";
	$result = $conn->query($sql);
	$rows = $result->fetchAll(PDO::FETCH_ASSOC);
	
	$array = [];
	foreach($rows as $item){
		array_push($array, [
			'id' => $item['ProduId'],
			'nome' => $item['ProduNome']
		]);
	}

	echo json_encode($array);
} elseif ($tipoRequest == 'PROFISSIONAL'){
	$sql = "SELECT ProfiId, ProfiNome
			FROM Profissional
			JOIN Situacao on SituaId = ProfiStatus
			WHERE SituaChave = 'ATIVO' and ProfiUnidade = $iUnidade
			ORDER BY ProfiNome ASC";
	$result = $conn->query($sql);
	$rows = $result->fetchAll(PDO::FETCH_ASSOC);

	$array = [];
	foreach($rows as $item){
		array_push($array,[
			'id' => $item['ProfiId'],
			'nome' => $item['ProfiNome']
		]);
	}

	echo json_encode($array);
} elseif($tipoRequest == 'ADICIONARSERVICO'){
	$atendimentoTabelaServicos = $_SESSION['atendimentoTabelaServicos'];

	$iServico = $_POST['servico'];
	$iMedico = $_POST['medicos'];
	$iGrupo = $_POST['grupo'];
	$iSubGrupo = $_POST['subgrupo'];

	$sData = date("Y-m-d");
	$sHora = date("H:i:s");

	$sql = "SELECT AtGruId, AtGruNome, AtGruUnidade
	FROM AtendimentoGrupo WHERE AtGruId = $iGrupo and AtGruUnidade = $iUnidade";
	$resultGrupo = $conn->query($sql);
	$resultGrupo = $resultGrupo->fetch(PDO::FETCH_ASSOC);

	$sql = "SELECT AtSubId, AtSubNome, AtSubUnidade
	FROM AtendimentoSubGrupo WHERE AtSubId = $iSubGrupo and AtSubUnidade = $iUnidade";
	$resultSubGrupo = $conn->query($sql);
	$resultSubGrupo = $resultSubGrupo->fetch(PDO::FETCH_ASSOC);
	
	$sql = "SELECT SrVenId,SrVenNome,SrVenDetalhamento,SVXMoValorVenda,SrVenUnidade, SrVenCodigo
	FROM ServicoVenda
	LEFT JOIN ServicoVendaXModalidade ON SVXMoServicoVenda = SrVenId
	WHERE SrVenId = $iServico and SrVenUnidade = $iUnidade";
	$resultServico = $conn->query($sql);
	$resultServico = $resultServico->fetch(PDO::FETCH_ASSOC);

	
	$sql = "SELECT ProfiId,ProfiNome,ProfiCpf,ProfiSexo,ProfiEndereco,ProfiCelular,ProfiTelefone
	FROM Profissional WHERE ProfiId = $iMedico and ProfiUnidade = $iUnidade";
	$resultMedico = $conn->query($sql);
	$resultMedico = $resultMedico->fetch(PDO::FETCH_ASSOC);
	$keyHora = str_replace(':', '', $sHora);

	array_push($atendimentoTabelaServicos, [
		'id' => "$resultServico[SrVenId]$resultMedico[ProfiId]$keyHora", // #$resultLocal[AtLocId]",
		'iServico' => $resultServico['SrVenId'],
		'iMedico' => $resultMedico['ProfiId'],
		'iGrupo' => $resultGrupo['AtGruId'],
		'iSubGrupo' => $resultSubGrupo['AtSubId'],
		'codigo' => $resultServico['SrVenCodigo'],
		'status' => 'new',
		'grupo' => $resultGrupo['AtGruNome'],
		'subgrupo' => $resultSubGrupo['AtSubNome'],
		'servico' => $resultServico['SrVenNome'],
		'medico' => $resultMedico['ProfiNome'],
		'sData' => mostraData($sData),
		'data' => $sData,
		'hora' => mostraHora($sHora),
		'valor' => $resultServico['SVXMoValorVenda'],
		'desconto' => 0
	]);
	$_SESSION['atendimentoTabelaServicos'] = $atendimentoTabelaServicos;

	echo json_encode([
		'status' => 'success',
		'titulo' => 'Serviço',
		'menssagem' => 'Serviço adicionado!!!',
	]);
}  elseif($tipoRequest == 'ADICIONARPRODUTO'){
	$atendimentoTabelaProdutos = $_SESSION['atendimentoTabelaProdutos'];

	$iServico = $_POST['servico'];
	$iMedico = $_POST['medicos'];

	$sData = date("Y-m-d");
	$sHora = date("H:i:s");
	
	$sql = "SELECT ProduId,ProduNome,ProduDetalhamento,ProduValorVenda,ProduEmpresa, ProduCodigo, ProduCodigoCompleto
	FROM Produto WHERE ProduId = $iServico and ProduEmpresa = $iEmpresa";
	$resultServico = $conn->query($sql);
	$resultServico = $resultServico->fetch(PDO::FETCH_ASSOC);

	$sql = "SELECT ProfiId,ProfiNome,ProfiCpf,ProfiSexo,ProfiEndereco,ProfiCelular,ProfiTelefone
	FROM Profissional WHERE ProfiId = $iMedico and ProfiUnidade = $iUnidade";
	$resultMedico = $conn->query($sql);
	$resultMedico = $resultMedico->fetch(PDO::FETCH_ASSOC);
	$keyHora = str_replace(':', '', $sHora);

	array_push($atendimentoTabelaProdutos, [
		'id' => "$resultServico[ProduId]$resultMedico[ProfiId]$keyHora", // #$resultLocal[AtLocId]",
		'iProduto' => $resultServico['ProduId'],
		'iMedico' => $resultMedico['ProfiId'],
		'codigoCompleto' => $resultServico['ProduCodigoCompleto'],
		
		'status' => 'new',
		'servico' => $resultServico['ProduNome'],
		'medico' => $resultMedico['ProfiNome'],
		'sData' => mostraData($sData),
		'data' => $sData,
		'hora' => mostraHora($sHora),
		'valor' => $resultServico['ProduValorVenda'],
		'desconto' => 0
	]);
	$_SESSION['atendimentoTabelaProdutos'] = $atendimentoTabelaProdutos;

	echo json_encode([
		'status' => 'success',
		'titulo' => 'Serviço',
		'menssagem' => 'Serviço adicionado!!!',
	]);
} elseif ($tipoRequest == 'CHECKSERVICO'){
	$atendimentoSessao = $_SESSION['atendimentoTabelaServicos'];

	if(isset($_POST['iAtendimento']) && $_POST['iAtendimento']){
		$iAtendimento = $_POST['iAtendimento'];

		$sql = "SELECT AtTGaId,AtTGaAtendimento,AtTGaServico,AtTGaProfissional,AtTGaDataRegistro,AtTGaHorario
			,AtTGaDataAtendimento,AtTGaValor,AtTGaDesconto, AtTGaGrupo, AtTGaSubGrupo,
			AtGruNome, AtGruId, AtSubNome, AtSubId,
			Profissional.ProfiId,AtModNome,ClienNome, ClienCelular,ClienTelefone,ClienEmail,SituaNome,SituaChave,
			SituaCor,ProfiNome,SrVenNome,SVXMoValorVenda,SrVenId, SrVenCodigo
			FROM AtendimentoTabelaGastoProcedimento
			JOIN Atendimento ON AtendId = AtTGaAtendimento
			JOIN AtendimentoModalidade ON AtModId = AtendModalidade
			JOIN Situacao ON SituaId = AtendSituacao
			JOIN Cliente ON ClienId = AtendCliente
			JOIN Profissional ON ProfiId = AtTGaProfissional
			JOIN ServicoVenda ON SrVenId = AtTGaServico
			JOIN AtendimentoGrupo ON AtGruId = AtTGaGrupo
			JOIN AtendimentoSubGrupo ON AtSubId = AtTGaSubGrupo
			LEFT JOIN ServicoVendaXModalidade ON SVXMoServicoVenda = SrVenId
			WHERE AtTGaUnidade = $iUnidade and AtTGaAtendimento = $iAtendimento";
		$result = $conn->query($sql);
		$rowAtendimento = $result->fetchAll(PDO::FETCH_ASSOC);

		// esse loop duplo serve para evitar duplicações e evitar que os itens incluídos localmente ao editar
		// não desapareçam
		foreach($rowAtendimento as $item){
			$hasItem = false;
			foreach($atendimentoSessao as $item2){
				if($item2['id'] == $item['AtTGaId']){
					$hasItem = true;
				}
			}
			if(!$hasItem){
				array_push($atendimentoSessao, [
					'id' => $item['AtTGaId'],
					'iServico' => $item['SrVenId'],
					'iMedico' => $item['ProfiId'],
					'iGrupo' => $item['AtTGaGrupo'],
					'iSubGrupo' => $item['AtTGaSubGrupo'],
					'codigo' => $item['SrVenCodigo'],
					'status' => 'att',
					'grupo' => $item['AtGruNome'],
					'subgrupo' => $item['AtSubNome'],
					'servico' => $item['SrVenNome'],
					'medico' => $item['ProfiNome'],
					'sData' => mostraData($item['AtTGaDataAtendimento']),
					'data' => $item['AtTGaDataAtendimento'],
					'hora' => mostraHora($item['AtTGaHorario']),
					'valor' => $item['AtTGaValor'],
					'desconto' => $item['AtTGaDesconto']?$item['AtTGaDesconto']:0
				]);
			}
		}
	}
	$valorTotal = 0;
	$desconto = 0;

	foreach($atendimentoSessao as $item){
		if ($item['status'] != 'rem') {
			$valor = $item['valor'] - $item['desconto'];
			$valorTotal += $valor;
			$desconto += $item['desconto'];
		}
	}
	$_SESSION['atendimentoTabelaServicos'] = $atendimentoSessao;
	
	echo json_encode([
		'array' => $atendimentoSessao,
		'valorTotal' => $valorTotal,
		'desconto' => $desconto
	]);
} elseif ($tipoRequest == 'CHECKPRODUTO'){

	$atendimentoSessao = $_SESSION['atendimentoTabelaProdutos'];

	if(isset($_POST['iAtendimento']) && $_POST['iAtendimento']){
		$iAtendimento = $_POST['iAtendimento'];

		$sql = "SELECT AtTGaId,AtTGaAtendimento,AtTGaProfissional,AtTGaDataRegistro,AtTGaHorario
			,AtTGaDataAtendimento,AtTGaValor,AtTGaDesconto,
			Profissional.ProfiId,AtModNome,ClienNome, ClienCelular,ClienTelefone,ClienEmail,SituaNome,SituaChave,
			SituaCor,ProfiNome,ProduNome,ProduValorVenda,ProduId, ProduCodigo, ProduCodigoCompleto
			FROM AtendimentoTabelaGastoProduto
			JOIN Atendimento ON AtendId = AtTGaAtendimento
			JOIN AtendimentoModalidade ON AtModId = AtendModalidade
			JOIN Situacao ON SituaId = AtendSituacao
			JOIN Cliente ON ClienId = AtendCliente
			JOIN Profissional ON ProfiId = AtTGaProfissional
			JOIN Produto ON ProduId = AtTGaProduto
			WHERE AtTGaUnidade = $iUnidade and AtTGaAtendimento = $iAtendimento";
		$result = $conn->query($sql);
		$rowAtendimento = $result->fetchAll(PDO::FETCH_ASSOC);

		// esse loop duplo serve para evitar duplicações e evitar que os itens incluídos localmente ao editar
		// não desapareçam
		foreach($rowAtendimento as $item){
			$hasItem = false;
			foreach($atendimentoSessao as $item2){
				if($item2['id'] == $item['AtTGaId']){
					$hasItem = true;
				}
			}
			if(!$hasItem){
				array_push($atendimentoSessao, [
					'id' => $item['AtTGaId'],
					'iServico' => $item['ProduId'],
					'iMedico' => $item['ProfiId'],

					'status' => 'att',
					'codigoCompleto' => $item['ProduCodigoCompleto'],
					'servico' => $item['ProduNome'],
					'medico' => $item['ProfiNome'],
					'sData' => mostraData($item['AtTGaDataAtendimento']),
					'data' => $item['AtTGaDataAtendimento'],
					'hora' => mostraHora($item['AtTGaHorario']),
					'valor' => $item['AtTGaValor'],
					'desconto' => $item['AtTGaDesconto']?$item['AtTGaDesconto']:0
				]);
			}
		}
	}
	$valorTotal = 0;
	$desconto = 0;

	foreach($atendimentoSessao as $item){
		if ($item['status'] != 'rem') {
			$valor = $item['valor'] - $item['desconto'];
			$valorTotal += $valor;
			$desconto += $item['desconto'];
		}
	}
	$_SESSION['atendimentoTabelaProdutos'] = $atendimentoSessao;
	echo json_encode([
		'array' => $atendimentoSessao,
		'valorTotal' => $valorTotal,
		'desconto' => $desconto
	]);


} elseif($tipoRequest == 'FECHARCONTA'){
	$atendimentoSessao = $_SESSION['atendimentoTabelaServicos'];
	$atendimentoSessaoProduto = $_SESSION['atendimentoTabelaProdutos'];

	if(!COUNT($atendimentoSessao)){
		echo json_encode([
			'titulo' => 'Atendimento',
			'status' => 'error',
			'menssagem' => 'Atendimento deve ter ao menos 1(um) procedimento!!'
		]);
		exit;
	}

	$id = isset($_POST['atendimento'])?$_POST['atendimento']:'';
	$cliente = isset($_POST['cliente'])?$_POST['cliente']:'';

	if($id && $cliente){
		$sql = "INSERT INTO AtendimentoTabelaGastoProcedimento(AtTGaAtendimento,AtTGaDataRegistro,AtTGaServico,
			AtTGaProfissional,AtTGaDataAtendimento,AtTGaHorario,AtTGaValor,AtTGaDesconto,
			AtTGaUsuarioAtualizador,AtTGaUnidade)
			VALUES ";
		$dataAtual = date("Y-m-d");

		$arraySql = [];
		$arraySqlProd = [];

		foreach($atendimentoSessao as $item){
			
			if($item['status'] == 'new'){
				
				$sql = "INSERT INTO AtendimentoTabelaGastoProcedimento(AtTGaAtendimento,AtTGaDataRegistro,AtTGaServico,
				AtTGaProfissional,AtTGaDataAtendimento,AtTGaHorario,AtTGaValor,AtTGaDesconto,
				AtTGaUsuarioAtualizador,AtTGaUnidade, AtTGaGrupo ,AtTGaSubGrupo)
				VALUES ('$id', '$dataAtual','$item[iServico]','$item[iMedico]','$item[data]','$item[hora]',
				$item[valor],'$item[desconto]','$usuarioId','$iUnidade', '$item[iGrupo]', '$item[iSubGrupo]')";
			} elseif($item['status'] == 'att'){
				$sql = "UPDATE AtendimentoTabelaGastoProcedimento SET
				AtTGaServico = '$item[iServico]',
				AtTGaProfissional = '$item[iMedico]',
				AtTGaDataAtendimento = '$item[data]',
				AtTGaHorario = '$item[hora]',
				AtTGaValor = '$item[valor]',
				AtTGaDesconto = '$item[desconto]',
				AtTGaUsuarioAtualizador = '$usuarioId',
				AtTGaGrupo = '$item[iGrupo]' ,
				AtTGaSubGrupo = '$item[iSubGrupo]'
				WHERE AtTGaId = '$item[id]'";
			} elseif($item['status'] == 'rem'){
				$sql = "DELETE FROM AtendimentoTabelaGastoProcedimento
				WHERE AtTGaId = '$item[id]'";
			}
			array_push($arraySql,$sql);
		}
		foreach($arraySql as $sql){
			$conn->query($sql);
		}

		foreach($atendimentoSessaoProduto as $item){
			
			if($item['status'] == 'new'){
				$sql = "INSERT INTO AtendimentoTabelaGastoProduto(AtTGaAtendimento,AtTGaDataRegistro,AtTGaProduto,
				AtTGaProfissional,AtTGaDataAtendimento,AtTGaHorario,AtTGaValor,AtTGaDesconto,
				AtTGaUsuarioAtualizador,AtTGaUnidade)
				VALUES ('$id', '$dataAtual','$item[iProduto]','$item[iMedico]','$item[data]','$item[hora]',
				$item[valor],'$item[desconto]','$usuarioId','$iUnidade')";
			} elseif($item['status'] == 'att'){
				$sql = "UPDATE AtendimentoTabelaGastoProduto SET
				AtTGaProduto = '$item[iServico]',
				AtTGaProfissional = '$item[iMedico]',
				AtTGaDataAtendimento = '$item[data]',
				AtTGaHorario = '$item[hora]',
				AtTGaValor = '$item[valor]',
				AtTGaDesconto = '$item[desconto]',
				AtTGaUsuarioAtualizador = '$usuarioId'
				WHERE AtTGaId = '$item[id]'";
			} elseif($item['status'] == 'rem'){
				$sql = "DELETE FROM AtendimentoTabelaGastoProduto
				WHERE AtTGaId = '$item[id]'";
			}
			array_push($arraySqlProd,$sql);
		}

		

		foreach($arraySqlProd as $sql){
			$conn->query($sql);
		}

		echo json_encode([
			'titulo' => 'Fechamento de conta',
			'status' => 'success',
			'menssagem' => 'Conta fechada!!'
		]);
	}else{
		echo json_encode([
			'titulo' => 'Fechamento de conta',
			'status' => 'error',
			'menssagem' => 'Erro ao fechar conta!!'
		]);
	}
} elseif($tipoRequest == 'SETDESCONTO'){
	$atendimentoSessao = $_SESSION['atendimentoTabelaServicos'];

	$id = $_POST['id'];
	$desconto = $_POST['desconto'];

	foreach($atendimentoSessao as $key => $item){
		if($item['id'] == $id){
			$atendimentoSessao[$key]['desconto'] = floatval($desconto);
		}
	}

	$_SESSION['atendimentoTabelaServicos'] = $atendimentoSessao;

	echo json_encode([
		'status' => 'success',
		'titulo' => 'Desconto',
		'menssagem' => 'Desconto adicionado!!!',
	]);
} elseif($tipoRequest == 'SETDESCONTOPRODUTO'){
	$atendimentoSessao = $_SESSION['atendimentoTabelaProdutos'];

	$id = $_POST['id'];
	$desconto = $_POST['desconto'];
	
	foreach($atendimentoSessao as $key => $item){
		if($item['id'] == $id){		
			$atendimentoSessao[$key]['desconto'] = floatval($desconto);	
		}
	}

	$_SESSION['atendimentoTabelaProdutos'] = $atendimentoSessao;

	echo json_encode([
		'status' => 'success',
		'titulo' => 'Desconto',
		'menssagem' => 'Desconto adicionado!!!',
	]);
} elseif($tipoRequest == 'GETDESCONTO'){
	$atendimentoSessao = $_SESSION['atendimentoTabelaServicos'];

	$id = $_POST['id'];
	$desconto = null;

	foreach($atendimentoSessao as $item){
		if($item['id'] == $id){
			$desconto = $item['desconto'];
		}
	}

	echo json_encode([
		'desconto' => $desconto,
		'status' => 'success',
		'titulo' => 'Desconto',
		'menssagem' => '',
	]);
} elseif($tipoRequest == 'GETDESCONTOPRODUTO'){
	$atendimentoSessao = $_SESSION['atendimentoTabelaProdutos'];

	$id = $_POST['id'];
	$desconto = null;

	foreach($atendimentoSessao as $item){
		if($item['id'] == $id){
			$desconto = $item['desconto'];
		}
	}

	echo json_encode([
		'desconto' => $desconto,
		'status' => 'success',
		'titulo' => 'Desconto',
		'menssagem' => '',
	]);
} elseif ($tipoRequest == 'EXCLUISERVICO'){
	$id = $_POST['id']; // "SrVenId#ProfiId#AtLocId"
	$atendimentoSessao = $_SESSION['atendimentoTabelaServicos'];

	foreach($atendimentoSessao as $key => $item){

		if($item['id'] == $id){
			$atendimentoSessao[$key]['status'] = 'rem';
			$_SESSION['atendimentoTabelaServicos'] = $atendimentoSessao;
			echo json_encode([
				'status' => 'success',
				'titulo' => 'Excluir gasto',
				'menssagem' => 'Gasto Excluído!!!',
			]);
			break;
		}
	}

} elseif ($tipoRequest == 'EXCLUIPRODUTO'){
	$id = $_POST['id']; // "ProduId#ProfiId"

	$atendimentoSessaoProduto = $_SESSION['atendimentoTabelaProdutos'];

	foreach($atendimentoSessaoProduto as $key => $item){


		if($item['id'] == $id){
			$atendimentoSessaoProduto[$key]['status'] = 'rem';
			$_SESSION['atendimentoTabelaProdutos'] = $atendimentoSessaoProduto;
			echo json_encode([
				'status' => 'success',
				'titulo' => 'Excluir Produto',
				'menssagem' => 'Produto Excluído!!!',
			]);
			break;
		}
	}

} elseif ($tipoRequest == 'GRUPOS') {

	$sql = "SELECT AtGruId, AtGruNome
		FROM AtendimentoGrupo
		WHERE AtGruStatus = 1
		and AtGruUnidade = $iUnidade
		ORDER BY AtGruNome ASC";
	$result = $conn->query($sql);
	$rows = $result->fetchAll(PDO::FETCH_ASSOC);

	$array = [];
	foreach($rows as $item){
		array_push($array,[
			'id' => $item['AtGruId'],
			'nome' => $item['AtGruNome']
		]);
	}

	echo json_encode($array);

} elseif ($tipoRequest == 'SUBGRUPOS') {

	$idGrupo = $_POST['idGrupo'];

	$sql = "SELECT AtSubId, AtSubNome
		FROM AtendimentoSubGrupo
		WHERE AtSubGrupo = $idGrupo 
		AND AtSubStatus = 1
		AND AtSubUnidade = $iUnidade
		ORDER BY AtSubNome ASC";
	$result = $conn->query($sql);
	$rows = $result->fetchAll(PDO::FETCH_ASSOC);

	$array = [];
	foreach($rows as $item){
		array_push($array,[
			'id' => $item['AtSubId'],
			'nome' => $item['AtSubNome']
		]);
	}

	echo json_encode($array);

}