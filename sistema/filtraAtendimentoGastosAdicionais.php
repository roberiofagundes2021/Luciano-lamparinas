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
	$iEmpresa = $_SESSION['EmpreId'];
	if(!isset($_SESSION['atendimento'])){
		$_SESSION['atendimento'] = [
			'paciente' => '',
			'responsavel' => '',
			'atendimentoServicos' => []
		];
	}
	
 	 // feito consultas para buscar a profissão do profissional do atendimento
	$sql = "SELECT Profissao.ProfiNome as ProfissaoNome
			FROM Profissional
			JOIN Profissao ON Profissao.ProfiId = Profissional.ProfiProfissao
			WHERE ProfiUsuario = $usuarioId";
	$result = $conn->query($sql);
	$rowProfissao = $result->fetch(PDO::FETCH_ASSOC);

	// feito consultas para buscar de acordo com a classificação do atendimento
	// (ATENDIMENTOSAMBULATORIAIS, ATENDIMENTOSHOSPITALARES, ATENDIMENTOSELETIVOS)
	if($tipoRequest == 'ATENDIMENTOS'){

		$array = [];
		$hoje = date('Y-m-d');

		$sql = "SELECT AtXSeId, AtendId,AtendNumRegistro,AtendDataRegistro,ClienNome,ClienCodigo,AtModNome,AtendClassificacao,AtClaChave, 
			AtendObservacao,AtendJustificativa,AtendSituacao,AtXSeData,AtXSeHorario,AtXSeAtendimentoLocal,AtXSeValor,AtXSeDesconto, ClienDtNascimento, ClienId, 
			ClienCelular,ClienTelefone,ClienEmail,SituaNome,SituaChave,SituaCor,Profissional.ProfiNome as ProfissionalNome,SrVenNome,
			Profissao.ProfiNome as ProfissaoNome, ProfiCbo,AtClRNome,AtClRNomePersonalizado,AtClRTempo,AtClRCor,AtClRDeterminantes
			FROM AtendimentoXServico
			LEFT JOIN Atendimento ON AtendId = AtXSeAtendimento
			LEFT JOIN AtendimentoModalidade ON AtModId = AtendModalidade
			LEFT JOIN Situacao ON SituaId = AtendSituacao
			LEFT JOIN Cliente ON ClienId = AtendCliente
			LEFT JOIN Profissional ON Profissional.ProfiId = AtXSeProfissional
			LEFT JOIN Profissao ON Profissional.ProfiProfissao = Profissao.ProfiId
			LEFT JOIN ServicoVenda ON SrVenId = AtXSeServico
			LEFT JOIN AtendimentoLocal ON AtLocId = AtXSeAtendimentoLocal
			LEFT JOIN AtendimentoClassificacaoRisco ON AtendClassificacaoRisco = AtClRId
			LEFT JOIN AtendimentoClassificacao ON AtClaId = AtendClassificacao
			WHERE AtendUnidade = $iUnidade
			AND SituaNome = 'ATENDIDO'";
		$result = $conn->query($sql);
		$rowAtendimento = $result->fetchAll(PDO::FETCH_ASSOC);
		
		$dataAtendimento = [];

		foreach($rowAtendimento as $item){
			$id = $item['AtendId'];
			$atendServico = $item['AtXSeId'];
			$att = "<div>
						<button id='atendSelecionar-$atendServico' style='cursor:pointer' class=' btnSelecionar btn btn-sm border-blue text-blue' onClick='selecionarAtendimento(\"$item[AtXSeId]\", \"$item[AtendId]\",\"$item[AtendNumRegistro]\", \"$item[ClienId]\")'>SELECIONAR</button>
						<button id='atendSelecionado-$atendServico'  style='display: none; cursor:pointer' class=' btnSelecionado btn btn-sm border-green text-green' onClick='selecionarAtendimento(\"$item[AtXSeId]\", \"$item[AtendId]\",\"$item[AtendNumRegistro]\", \"$item[ClienId]\")' disabled>SELECIONADO</button>
					</div>";
			$acoes = "<div class='list-icons'>
						$att						
					</div>";
		
			$contato = $item['ClienCelular']?$item['ClienCelular']:($item['ClienTelefone']?$item['ClienTelefone']:'não informado');
			
			$dataEspera = date('Y-m-d');
			$difference = diferencaEmHoras($dataEspera, $item['AtXSeData']);
			
			array_push($dataAtendimento, [
				'data' => [
					mostraData($item['AtXSeData']) . " - " . mostraHora($item['AtXSeHorario']), // Data - Hora
					$item['AtendNumRegistro'],  // Nº Registro
					'prontuario',
					$item['ClienNome'],  // Paciente
					$item['ProfissionalNome'],  // Profissional
					$item['AtModNome'],  // Modalidade
					$item['SrVenNome'],  // Procedimento
					"<span style='cursor:pointer' class='badge badge-flat border-$item[SituaCor] text-$item[SituaCor]'>$item[SituaNome]</span>",  // Situação
					$acoes,  // Ações
				],
			]);
		}
		$array  = [
			'dataAtendimento' => $dataAtendimento,
			'titulo' => 'Atendimentos',
			'status' => 'success',
			'menssagem' => ''
		];	

		echo json_encode($array);


    }elseif ($tipoRequest == 'PRODUTOS') {
		$sql = "SELECT ProduId, ProduCodigo, ProduNome
			FROM Produto		
			WHERE ProduEmpresa = $iEmpresa";

		$result = $conn->query($sql);
		$rowProdutos = $result->fetchAll(PDO::FETCH_ASSOC);

		$array = [];
				
		foreach ($rowProdutos as $key => $item ) {
			
			array_push($array,[
				'id' => $item['ProduId'],
				'produCodigo'=>$item['ProduCodigo'],
				'descricao'=> $item['ProduNome'],
			]);

		}

		echo json_encode($array);
	}elseif ($tipoRequest == 'ADICIONARPRODUTO') {


		$gastosAdicionaisProdutos = $_SESSION['gastosAdicionaisProdutos'];

		$iServico = $_POST['servico'];

		$sData = date("Y-m-d");
		$sHora = date("H:i:s");
		
		$sql = "SELECT ProduId,ProduNome,ProduDetalhamento,ProduValorVenda,ProduEmpresa, ProduCodigo, MarcaId, MarcaNome
		FROM Produto
		LEFT JOIN Marca ON ProduMarca = MarcaId 
		WHERE ProduId = $iServico and ProduEmpresa = $iEmpresa";
		$resultServico = $conn->query($sql);
		$resultServico = $resultServico->fetch(PDO::FETCH_ASSOC);

		array_push($gastosAdicionaisProdutos, [

			'id' => "$resultServico[ProduId]$sHora", // #$resultLocal[AtLocId]",
			'iProduto' => $resultServico['ProduId'],
			'codigo' => $resultServico['ProduCodigo'],
			'detalhamento' => $resultServico['ProduDetalhamento'],
			'marcaNome' => $resultServico['MarcaNome'],
			'marcaId' => $resultServico['MarcaId'],

			
			'status' => 'new',
			'servico' => $resultServico['ProduNome'],
			'sData' => mostraData($sData),
			'data' => $sData,
			'hora' => mostraHora($sHora),
			'valor' => $resultServico['ProduValorVenda'],
			'desconto' => 0
		]);
		$_SESSION['gastosAdicionaisProdutos'] = $gastosAdicionaisProdutos;

		echo json_encode([
			'status' => 'success',
			'titulo' => 'Produto',
			'menssagem' => 'Produto adicionado!!!',
		]);
		# code...
	}  elseif ($tipoRequest == 'CHECKPRODUTO'){ 

		$valorTotal = 0;
		$desconto = 0;

		$atendimentoSessao = $_SESSION['gastosAdicionaisProdutos'];

		foreach($atendimentoSessao as $item){
			if ($item['status'] != 'rem') {
				$valor = $item['valor'] - $item['desconto'];
				$valorTotal += $valor;
				$desconto += $item['desconto'];
			}
		}

		$_SESSION['gastosAdicionaisProdutos'] = $atendimentoSessao;

		echo json_encode([
			'array' => $atendimentoSessao,
			'valorTotal' => $valorTotal,
			'desconto' => $desconto
		]);

	}  elseif($tipoRequest == 'GETDESCONTOPRODUTO'){

		$atendimentoSessao = $_SESSION['gastosAdicionaisProdutos'];
	
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
	} elseif ($tipoRequest == 'EXCLUIPRODUTO'){
		$id = $_POST['id']; 
	
		$atendimentoSessaoProduto = $_SESSION['gastosAdicionaisProdutos'];
	
		foreach($atendimentoSessaoProduto as $key => $item){
	
	
			if($item['id'] == $id){
				$atendimentoSessaoProduto[$key]['status'] = 'rem';
				$_SESSION['gastosAdicionaisProdutos'] = $atendimentoSessaoProduto;
				echo json_encode([
					'status' => 'success',
					'titulo' => 'Excluir Produto',
					'menssagem' => 'Produto Excluído!!!',
				]);
				break;
			}
		}
	
	} elseif($tipoRequest == 'SETDESCONTOPRODUTO'){
		$atendimentoSessao = $_SESSION['gastosAdicionaisProdutos'];
	
		$id = $_POST['id'];
		$desconto = $_POST['desconto'];
		
		foreach($atendimentoSessao as $key => $item){
			if($item['id'] == $id){		
				$atendimentoSessao[$key]['desconto'] = floatval($desconto);	
			}
		}
	
		$_SESSION['gastosAdicionaisProdutos'] = $atendimentoSessao;
	
		echo json_encode([
			'status' => 'success',
			'titulo' => 'Desconto',
			'menssagem' => 'Desconto adicionado!!!',
		]);
	} elseif($tipoRequest == 'FECHARCONTA'){

		$atendimentoSessao = $_SESSION['gastosAdicionaisProdutos'];
	
		if(!COUNT($atendimentoSessao)){
			echo json_encode([
				'titulo' => 'Gasto',
				'status' => 'error',
				'menssagem' => 'Gasto deve ter ao menos 1(um) produto!!'
			]);
			exit;
		}

		$idAtendimento = isset($_POST['idAtendimento'])?$_POST['idAtendimento']:'';
		$idPaciente = isset($_POST['idPaciente'])?$_POST['idPaciente']:'';
	
		if($idPaciente){

			$dataHora = date('Y-m-d H:i:s');

			if ($idAtendimento) {
				$sql = "INSERT INTO AtendimentoGastoAdicional(AtGAdAtendimento,AtGAdPaciente,AtGAdDataHora,AtGAdUsuarioAtualizador,AtGAdUnidade)
				VALUES ('$idAtendimento','$idPaciente','$dataHora','$usuarioId','$iUnidade')";
				
			} else {
				$sql = "INSERT INTO AtendimentoGastoAdicional(AtGAdPaciente,AtGAdDataHora,AtGAdUsuarioAtualizador,AtGAdUnidade)
				VALUES ('$idPaciente','$dataHora','$usuarioId','$iUnidade')";
			}

			
			$conn->query($sql);
			$lastGastoInsert = $conn->lastInsertId();

			$arraySqlProd = [];
	
			foreach($atendimentoSessao as $item){
				
				if($item['status'] == 'new'){
					$sql = "INSERT INTO AtendimentoGastoAdicionalProduto(AGAPrGastoAdicional, AGAPrProduto, AGAPrDetalhamento, AGAPrMarca, AGAPrValor, AGAPrDesconto, AGAPrUnidade)
					VALUES ('$lastGastoInsert','$item[iProduto]','$item[detalhamento]','$item[marcaId]', '$item[valor]','$item[desconto]','$iUnidade')";

				} elseif($item['status'] == 'rem'){
					$sql = "DELETE FROM AtendimentoGastoAdicionalProduto
					WHERE AGAPrId = '$item[id]'";
				}
				array_push($arraySqlProd,$sql);
			}
	
			foreach($arraySqlProd as $sql){
				$conn->query($sql);
			}

			$_SESSION['gastosAdicionaisProdutos'] = [];
	
			echo json_encode([
				'titulo' => 'Gasto Adicional',
				'status' => 'success',
				'menssagem' => 'Gasto Adicional Inserido!!'
			]);
		}else{
			echo json_encode([
				'titulo' => 'Gasto Adicional',
				'status' => 'error',
				'menssagem' => 'Erro ao fechar conta!!'
			]);
		}
	}

}catch(PDOException $e) {
	$msg = '';
	switch($tipoRequest){
		case 'MUDARSITUACAO': $msg = 'Erro ao atualizar situação do atendimentos!!';break;
		case 'EXCLUI': $msg = 'Erro ao excluir atendimento!!';break;
		case 'SALVARPACIENTE': $msg = 'Erro ao salvar paciente!!';break;
		case 'RESPONSAVEL': $msg = 'Erro ao buscar responsável!!';break;
		case 'SALVARRESPONSAVEL': $msg = 'Erro ao salvar responsável!!';break;
		default: $msg = 'Erro ao executar ação!!';break;
	}
	echo json_encode([
		'titulo' => 'Atendimento',
		'status' => 'error',
		'menssagem' => $msg,
		'error' => $e->getMessage(),
		'sql' => $sql
	]);
}
