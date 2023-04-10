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

	if($tipoRequest == 'AGENDAMENTOS'){		
		$sql = "SELECT AgendId,AgendDataRegistro,AgendData,AgendHorario,AtModNome,
		AgendClienteResponsavel,AgendAtendimentoLocal,AgendServico,
		AgendObservacao,ClienNome, ClienCelular,ClienTelefone,ClienEmail,SituaNome,SituaChave,ClienDtNascimento,ClienCodigo,
		SituaCor,Profissional.ProfiNome as ProfissionalNome,AtLocNome, SrVenNome, ProfiCbo, Profissao.ProfiNome as ProfissaoNome
		FROM Agendamento
		JOIN AtendimentoModalidade ON AtModId = AgendModalidade
		JOIN Situacao ON SituaId = AgendSituacao
		JOIN Cliente ON ClienId = AgendCliente
		JOIN Profissional ON Profissional.ProfiId = AgendProfissional
		JOIN Profissao ON Profissional.ProfiProfissao = Profissao.ProfiId
		JOIN AtendimentoLocal ON AtLocId = AgendAtendimentoLocal
		JOIN ServicoVenda ON SrVenId = AgendServico
		WHERE AgendUnidade = $iUnidade";
		$result = $conn->query($sql);
		$row = $result->fetchAll(PDO::FETCH_ASSOC);
		
		$array = [];
		foreach($row as $item){
			$att = "<a style='color: black' href='#' onclick='atualizaAgendamento(\"EDITA\", $item[AgendId])' class='list-icons-item'><i class='icon-pencil7' title='Editar Atendimento'></i></a>";
			$exc = "<a style='color: black' href='#' onclick='atualizaAgendamento(\"EXCLUI\", $item[AgendId])' class='list-icons-item'><i class='icon-bin' title='Excluir Atendimento'></i></a>";
			$aud = "<a style='color: black' href='#'  data-tipo='AGENDAMENTO' onclick='auditoria(this)' class='list-icons-item' data-id='$item[AgendId]'><i class='icon-eye4' title='Auditoria'></i></a>";
			$acoes = "<div class='list-icons'>
						${att}
						${exc}
						${aud}
					</div>";
		
			$contato = $item['ClienCelular']?$item['ClienCelular']:($item['ClienTelefone']?$item['ClienTelefone']:'não informado');
			array_push($array, [
				'data' => [
					mostraData($item['AgendData']) . " - " . mostraHora($item['AgendHorario']),					
					$item['ClienNome'],
					calculaIdadeSimples($item['ClienDtNascimento']),
					$item['ProfissionalNome'],
					$item['SrVenNome'],
					$item['AtModNome'],
					$contato,
					"<span style='cursor:pointer' class='badge badge-flat border-$item[SituaCor] text-$item[SituaCor]'>$item[SituaNome]</span>",
					$acoes
				],
				'identify' => [
					'situacao' => $item['SituaChave'],
					'iAgendamento' => $item['AgendId'],
					'sObservacao' => $item['AgendObservacao'],
					'prontuario' => 'Prontuário - '.($item['ClienCodigo']?$item['ClienCodigo']:'NaN'),
					'cbo' => 'CBO - '.($item['ProfiCbo']?$item['ProfiCbo']:'NaN'),
				]
			]);
		}
		//var_dump(json_encode($array));die;
		echo json_encode($array);
	} elseif ($tipoRequest == 'SITUACOES'){
		$sql = "SELECT SituaId,SituaNome,SituaChave,SituaStatus,SituaUsuarioAtualizador,SituaCor
		FROM Situacao
		WHERE SituaChave in ('AGENDADO','CONFIRMADO','CANCELADO')";
		$result = $conn->query($sql);
		$row = $result->fetchAll(PDO::FETCH_ASSOC);

		$array = [];
		foreach($row as $item){
			array_push($array,[
				'id' => $item['SituaId'],
				'nome' => $item['SituaNome'],
				'SituaChave' => $item['SituaChave'],
			]);
		}
	
		echo json_encode($array);
	} elseif ($tipoRequest == 'MUDARSITUACAO'){
		$iAgendamento = $_POST['iAgendamento'];
		$iSituacao = $_POST['iSituacao'];
		$sObservacao = $_POST['sObservacao'];
	
		$sql = "UPDATE Agendamento SET AgendSituacao = $iSituacao, AgendObservacao = '$sObservacao'
		WHERE AgendId = $iAgendamento and AgendUnidade = $iUnidade";
		$result = $conn->query($sql);

		echo json_encode([
			'titulo' => 'Alterar Situação',
			'tipo' => 'success',
			'menssagem' => 'Situação alterada com sucesso!!!'
		]);
	} elseif ($tipoRequest == 'PACIENTES'){
	
		$sql = "SELECT ClienId,ClienCodigo,ClienNome
		,ClienCpf,ClienRg,ClienOrgaoEmissor,ClienUf,ClienSexo,
		ClienDtNascimento,ClienNomePai,ClienNomeMae,ClienCartaoSus,ClienProfissao,ClienCep,ClienEndereco,
		ClienNumero,ClienComplemento,ClienBairro,ClienCidade,ClienEstado,ClienContato,ClienTelefone,
		ClienCelular,ClienEmail,ClienObservacao,ClienStatus,ClienUsuarioAtualizador,ClienUnidade
		FROM Cliente WHERE ClienUnidade = $iUnidade";
		$result = $conn->query($sql);

		$array = [];
		foreach($result as $item){
			array_push($array,[
				'id' => $item['ClienId'],
				'nome' => $item['ClienNome'],
			]);
		}

		echo json_encode($array);
	} elseif ($tipoRequest == 'ADDPACIENTENOVO'){

		$nomePaciente = $_POST['nomePaciente'];
		$telefone = $_POST['telefone'];
		$celular = $_POST['celular'];
		$email = $_POST['email'];
		$observacao = $_POST['observacao'];
		$sCodigo = null;

		try{		
			$sql = "SELECT COUNT(isnull(clienCodigo,0)) as Codigo
					FROM Cliente
					Where ClienUnidade = $iUnidade";
			//echo $sql;die;
			$result = $conn->query("$sql");
			$rowCodigo = $result->fetch(PDO::FETCH_ASSOC);	
			
			$sCodigo = (int)$rowCodigo['Codigo'] + 1;
			$sCodigo = str_pad($sCodigo,6,"0",STR_PAD_LEFT);
		} catch(PDOException $e) {	
			echo 'Error1: ' . $e->getMessage();die;
		}
	
		// insere o novo usuário no banco
		$sql = "INSERT INTO  Cliente(clienCodigo,ClienNome,ClienTelefone,ClienCelular,ClienEmail,ClienObservacao,
		ClienStatus,ClienUnidade,ClienUsuarioAtualizador)
		VALUES ('$sCodigo','$nomePaciente','$telefone','$celular','$email','$observacao',1,$iUnidade,$usuarioId)";
		$conn->query($sql);

		$lestIdCliente = $conn->lastInsertId();

		// busca todos os usuários com o novo inserido para adicionalo ja selecionado no select
		$sql = "SELECT ClienId,ClienNome FROM Cliente";
		$result = $conn->query($sql);

		$array = [];
		foreach($result as $item){
			array_push($array,[
				'id' => $item['ClienId'],
				'nome' => $item['ClienNome'],
				'isSelected' => $item['ClienId'] == $lestIdCliente? 'selected':''
			]);
		}
		echo json_encode([
			'titulo' => 'Incluir paciente',
			'status' => 'success',
			'menssagem' => 'Paciente inserido com sucesso!!!',
			'array' => $array,
		]);
	} elseif ($tipoRequest == 'MODALIDADES'){
	
		$sql = "SELECT AtModId,AtModNome,AtModChave,AtModSituacao,AtModUsuarioAtualizador
		FROM AtendimentoModalidade
		WHERE AtModUnidade = $iUnidade";
		$result = $conn->query($sql);

		$array = [];
		foreach($result as $item){
			array_push($array,[
				'id' => $item['AtModId'],
				'nome' => $item['AtModNome'],
			]);
		}

		echo json_encode($array);
	} elseif ($tipoRequest == 'SERVICOS'){
		// $sql = "SELECT SrVenId,SrVenNome,SrVenPlanoConta,SrVenDetalhamento,SrVenValorCusto,
		// SrVenOutrasDespesas,SrVenCustoFinal,SrVenMargemLucro,SrVenValorVenda,SrVenStatus,
		// SrVenUsuarioAtualizador,SrVenUnidade
		// FROM Servico WHERE SrVenUnidade = $iUnidade";

		$sql = "SELECT SrVenId,SrVenNome,SrVenCodigo
		FROM ServicoVenda WHERE SrVenUnidade = $iUnidade";
		$result = $conn->query($sql);

		$array = [];
		foreach($result as $item){
			array_push($array,[
				'id' => $item['SrVenId'],
				'nome' => $item['SrVenNome'],
				'codigo' => $item['SrVenCodigo']
			]);
		}

		echo json_encode($array);
	} elseif ($tipoRequest == 'CLASSIFICACAO'){
		$sql = "SELECT AtClaId,AtClaNome,AtClaNomePersonalizado,AtClaChave,AtClaModelo,AtClaStatus,
		AtClaUsuarioAtualizador,AtClaUnidade
		FROM AtendimentoClassificacao WHERE AtClaUnidade = $iUnidade";
		$result = $conn->query($sql);

		$array = [];
		foreach($result as $item){
			array_push($array,[
				'id' => $item['AtClaId'],
				'nome' => $item['AtClaNome'],
			]);
		}

		echo json_encode($array);
	} elseif ($tipoRequest == 'MEDICOS'){
		$servico = $_POST['servico'];

		$sql = "SELECT ProfiId,ProfiNome
		FROM ProfissionalXServicoVenda
		JOIN Profissional ON ProfiId = PrXSVProfissional
		WHERE PrXSVServicoVenda = $servico and ProfiUnidade = $iUnidade";
		$result = $conn->query($sql);

		$array = [];
		foreach($result as $item){
			array_push($array,[
				'id' => $item['ProfiId'],
				'nome' => $item['ProfiNome'],
			]);
		}

		echo json_encode($array);
	} elseif ($tipoRequest == 'LOCALATENDIMENTO'){

		$iMedico = $_POST['iMedico'];
		$hoje = date('Y-m-d');

		$sql= "SELECT DISTINCT AtLocId,AtLocNome,AtLocStatus,AtLocUsuarioAtualizador,AtLocUnidade
		FROM AtendimentoLocal 
		JOIN ProfissionalAgenda ON PrAgeAtendimentoLocal = AtLocId
		WHERE PrAgeProfissional = $iMedico 
		AND PrAgeData >= '$hoje'
		AND AtLocUnidade = $iUnidade";
		$result = $conn->query($sql);

		$array = [];
		foreach($result as $item){
			array_push($array,[
				'id' => $item['AtLocId'],
				'nome' => $item['AtLocNome'],
			]);
		}

		echo json_encode($array);
	} elseif ($tipoRequest == 'ADDAGENDAMENTO'){
		$data = $_POST['data'];
		$paciente = $_POST['paciente'];
		$modalidade = $_POST['modalidade'];
		$observacao = $_POST['observacao'];
		$cmbSituacao = $_POST['cmbSituacao'];
		$servicos = $_SESSION['SERVICOS'];

		// caso não vá atualizar,deve procurar no banco se existe um agendamento para o dia,
		// horário e profissional selecionado caso contrário, irá procurar no banco se existe um
		// agendamento para o dia,horário e profissional diferente do atendimento atual

		foreach($servicos as $item){
			$iMedico = $item['iMedico'];
			$data = $item['data'];
			$hora = $item['hora'];
			$isUpdate = isset($_POST['isUpdate'])?$_POST['isUpdate']:false;

			$sql = "SELECT AgendId FROM Agendamento
			WHERE AgendData = '$data' and AgendHorario = '$hora' and AgendProfissional = '$iMedico'";
			$sql .= $isUpdate?" and AgendId != $isUpdate":"";
			$result = $conn->query($sql);
			$row = $result->fetchAll(PDO::FETCH_ASSOC);

			if(COUNT($row)){
				echo json_encode([
					'titulo' => 'Conflito de serviço',
					'status' => 'error',
					'menssagem' => 'Um dos serviços já possui cadastro com o mesmo Profissional, data e horário',
				]);
				exit;
			}
		}

		$sql = "SELECT AgXSeId FROM AgendamentoXServico 
		WHERE AgXSeData = '$data' and AgXSeHorario = '$hora' and AgXSeProfissional = '$iMedico'";
		$result = $conn->query($sql);
		$row = $result->fetchAll(PDO::FETCH_ASSOC);
		if(COUNT($row)){
			echo json_encode([
				'titulo' => 'Conflito de serviço',
				'status' => 'error',
				'menssagem' => 'Um dos serviços já possui cadastro com o mesmo Profissional, data e horário',
			]);
			exit;
		}

		if($isUpdate){
			$sql = "DELETE FROM Agendamento WHERE AgendId = $isUpdate and AgendUnidade = $iUnidade";
			$conn->query($sql);
		}

		$tipoRequest = 'ATTAGENDAMENTO';
		$sql = "INSERT INTO Agendamento(AgendDataRegistro,AgendCliente,AgendModalidade,
		AgendServico,AgendProfissional,AgendData,AgendHorario,AgendAtendimentoLocal,
		AgendObservacao,AgendSituacao,AgendUnidade,AgendUsuarioAtualizador)
		VALUES ";

		foreach($servicos as $servico){
			$iServico = $servico['iServico'];
			$iMedico = $servico['iMedico'];
			$dataR = $servico['data'];
			$horaR = $servico['hora'];
			$iLocal = $servico['iLocal'];

			$sql .= "('$data','$paciente','$modalidade','$iServico','$iMedico','$dataR','$horaR',
			'$iLocal','$observacao','$cmbSituacao','$iUnidade','$usuarioId'),";
		}
		$sql  = substr($sql, 0, -1);
		$conn->query($sql);

		// vai limpar todos os dados da sessão utilizadas em agendamento após o cadastro
		$_SESSION['SERVICOS'] = [];

		echo json_encode([
			'titulo' => 'Incluir agendamento',
			'status' => 'success',
			'menssagem' => 'Agendamento inserido com sucesso!!!',
		]);
	} elseif ($tipoRequest == 'EDITAR'){
		$iAgendamento = isset($_POST['iAgendamento'])?$_POST['iAgendamento']:'';

		$data = isset($_POST['data'])?$_POST['data']:'';
		$data = explode('/', $data)[0];


		$paciente = isset($_POST['paciente'])?$_POST['paciente']:'';
		$modalidade = isset($_POST['modalidade'])?$_POST['modalidade']:'';

		$responsavel = isset($_POST['responsavel'])?$_POST['responsavel']:'';
		$cmbSituacao = isset($_POST['cmbSituacao'])?$_POST['cmbSituacao']:'';
		$observacao = isset($_POST['observacao'])?$_POST['observacao']:'';
	
		$sql = "UPDATE Agendamento SET AgendDataRegistro = '$data',AgendCliente = $paciente,
		AgendModalidade = $modalidade,AgendClienteResponsavel = $responsavel,
		AgendObservacao = '$observacao',AgendSituacao = $cmbSituacao,AgendUsuarioAtualizador = $usuarioId
		WHERE AgendId = $iAgendamento and AgendUnidade = $iUnidade";

		$result = $conn->query($sql);

		echo json_encode([
			'titulo' => 'Editar agendamento',
			'tipo' => 'success',
			'menssagem' => 'Agendamento atualizado com sucesso!!!',
		]);
		irpara('agendamento.php');
	} elseif ($tipoRequest == 'EXCLUI'){
		$iAgendamento = $_POST['id'];
	
		$sql = "DELETE FROM AgendamentoXServico WHERE AgXSeAgendamento = $iAgendamento
		and AgXSeUnidade = $iUnidade";
		$conn->query($sql);

		$sql = "DELETE FROM Agendamento WHERE AgendId = $iAgendamento and AgendUnidade = $iUnidade";
		$conn->query($sql);

		echo json_encode([
			'titulo' => 'Excluir agendamento',
			'status' => 'success',
			'menssagem' => 'Agendamento excluido com sucesso!!!',
		]);
	} elseif ($tipoRequest == 'ADDSERVICO'){
		if(!isset($_SESSION['SERVICOS'])){
			$_SESSION['SERVICOS'] = [];
		}
		$arrayServico = $_SESSION['SERVICOS'];

		$iServico = $_POST['servico'];
		$iMedico = $_POST['medico'];
		$sData = explode('/',$_POST['data']);
		$sData = $sData[2].'-'.$sData[1].'-'.$sData[0];
		$sHora = $_POST['hora'];
		$iLocal = $_POST['local'];

		// $sqlServico = "SELECT SrVenId,SrVenNome,SrVenDetalhamento,SrVenValorCusto,SrVenUnidade
		// FROM ServicoVenda WHERE SrVenId = $iServico and SrVenUnidade = $iUnidade";
		$sql = "SELECT SrVenId,SrVenNome,SrVenDetalhamento,SVXMoValorVenda,SrVenUnidade
		FROM ServicoVenda 
		LEFT JOIN ServicoVendaXModalidade ON SrVenId = SVXMoServicoVenda
		WHERE SrVenId = $iServico and SrVenUnidade = $iUnidade";
		$resultServico = $conn->query($sql);
		$resultServico = $resultServico->fetch(PDO::FETCH_ASSOC);

		// $sqlMedico = "SELECT ProfiId,ProfiNome,ProfiCpf,ProfiSexo,ProfiEndereco,ProfiCelular,ProfiTelefone
		// FROM Profissional WHERE ProfiId = $iMedico and ProfiUnidade = $iUnidade";
		$sql = "SELECT ProfiId,ProfiNome,ProfiCpf,ProfiSexo,ProfiEndereco,ProfiCelular,ProfiTelefone
		FROM Profissional WHERE ProfiId = $iMedico and ProfiUnidade = $iUnidade";
		$resultMedico = $conn->query($sql);
		$resultMedico = $resultMedico->fetch(PDO::FETCH_ASSOC);

		// $sqlLocal = "SELECT AtLocId,AtLocNome
		// FROM AtendimentoLocal WHERE AtLocId = $iLocal and AtLocUnidade = $iUnidade";
		$sql = "SELECT AtLocId,AtLocNome
		FROM AtendimentoLocal WHERE AtLocId = $iLocal and AtLocUnidade = $iUnidade";
		$resultLocal = $conn->query($sql);
		$resultLocal = $resultLocal->fetch(PDO::FETCH_ASSOC);

		$valorTotal = 0;

		foreach($arrayServico as $item){
			if($item['iMedico'] == $iMedico && $item['data'] == $sData && $item['hora'] == $sHora){
				echo json_encode([
					'status' => 'error',
					'titulo' => 'Duplicação de registro',
					'menssagem' => 'Já foi adicionado registro com o mesmo Médico, Data e Hora',
				]);
				exit;
			}
		}

		array_push($arrayServico, [
			'id' => "$resultServico[SrVenId]#$resultMedico[ProfiId]#$resultLocal[AtLocId]",
			'iServico' => $resultServico['SrVenId'],
			'iMedico' => $resultMedico['ProfiId'],
			'iLocal' => $resultLocal['AtLocId'],

			'servico' => $resultServico['SrVenNome'],
			'medico' => $resultMedico['ProfiNome'],
			'local' => $resultLocal['AtLocNome'],
			'sData' => mostraData($sData),
			'data' => $sData,
			'hora' => $sHora,
			'valor' => $resultServico['SVXMoValorVenda'],
		]);

		// esse loop serve para ja calcular o valor total da venda
		foreach($arrayServico as $item){
			$valorTotal += $item['valor'];
		}

		$_SESSION['SERVICOS'] = $arrayServico;
		echo json_encode([
			'array' => $arrayServico,
			'valorTotal' => $valorTotal,
			'status' => 'success',
			'titulo' => 'Serviço',
			'menssagem' => 'Serviço adicionado!!!',
		]);
	} elseif ($tipoRequest == 'CHECKSERVICO'){
		$arrayServico = isset($_SESSION['SERVICOS'])?$_SESSION['SERVICOS']:[];

		if(isset($_POST['iAgendamento'])){
			$iAgendamento = $_POST['iAgendamento'];
			
			$sql = "SELECT AgendId,ProfiId,AtLocId,AgendProfissional,AgendAtendimentoLocal,AgendDataRegistro,
			AgendData,AgendHorario,AtModNome,
			AgendClienteResponsavel,AgendAtendimentoLocal,AgendServico,
			AgendObservacao,ClienNome, ClienCelular,ClienTelefone,ClienEmail,SituaNome,SituaChave,
			SituaCor,ProfiNome,AtLocNome,SrVenNome,SVXMoValorVenda,SrVenId
			FROM Agendamento
			JOIN AtendimentoModalidade ON AtModId = AgendModalidade
			JOIN Situacao ON SituaId = AgendSituacao
			JOIN Cliente ON ClienId = AgendCliente
			JOIN Profissional ON ProfiId = AgendProfissional
			JOIN AtendimentoLocal ON AtLocId = AgendAtendimentoLocal
			JOIN ServicoVenda ON SrVenId = AgendServico
			LEFT JOIN ServicoVendaXModalidade ON SrVenId = SVXMoServicoVenda
			WHERE AgendId = $iAgendamento and AgendUnidade = $iUnidade";
			$result = $conn->query($sql);
			$rowAgendamento = $result->fetchAll(PDO::FETCH_ASSOC);
			
			foreach($rowAgendamento as $item){
				array_push($arrayServico, [
					'id' => "$item[AgendServico]#$item[ProfiId]#$item[AtLocId]",
					'iServico' => $item['SrVenId'],
					'iMedico' => $item['ProfiId'],
					'iLocal' => $item['AtLocId'],
			
					'servico' => $item['SrVenNome'],
					'medico' => $item['ProfiNome'],
					'local' => $item['AtLocNome'],
					'sData' => mostraData($item['AgendData']),
					'data' => $item['AgendData'],
					'hora' => mostraHora($item['AgendHorario']),
					'valor' => $item['SVXMoValorVenda'],
				]);
			}
		}
		$valorTotal = 0;

		foreach($arrayServico as $item){
			$valorTotal += $item['valor'];
		}
		$_SESSION['SERVICOS'] = $arrayServico;
		
		echo json_encode([
			'array' => $arrayServico,
			'valorTotal' => $valorTotal
		]);
	} elseif ($tipoRequest == 'EXCLUISERVICO'){
		$id = $_POST['id'];

		// idSeervico # idMedico # idLocal
		$id = explode('#',$id);

		if(!isset($_SESSION['SERVICOS'])){
			$_SESSION['SERVICOS'] = [];
		}
		$arrayServico = $_SESSION['SERVICOS'];

		foreach($arrayServico as $key => $item){
			$iServico = $item['iServico'];
			$iMedico = $item['iMedico'];
			$iLocal = $item['iLocal'];

			if($iServico == $id[0] && $iMedico == $id[1] && $iLocal == $id[2]){
				array_splice($arrayServico, $key, 1);
				$_SESSION['SERVICOS'] = $arrayServico;
				echo json_encode([
					'status' => 'success',
					'titulo' => 'Excluir serviço',
					'menssagem' => 'Serviço Excluído!!!',
				]);
				break;
			}
		}
	} elseif ($tipoRequest == 'SETDATAPROFISSIONAL'){
		$iMedico = $_POST['iMedico'];
		$localAtend = $_POST['localAtend'];
		$hoje = date('Y-m-d');
		
		$sql = "SELECT PrAgeData, PrAgeHoraInicio, PrAgeHoraFim
		FROM ProfissionalAgenda WHERE PrAgeProfissional = $iMedico and PrAgeUnidade = $iUnidade
		AND PrAgeAtendimentoLocal = $localAtend
		AND PrAgeData >= '$hoje'
		ORDER BY PrAgeData ASC";
		$result = $conn->query($sql);
		$row = $result->fetchAll(PDO::FETCH_ASSOC);

		$arrayData = [true];
		foreach($row as $item){
			$data = explode('-', $item['PrAgeData']);
			
			array_push($arrayData,
			[
				intval($data[0]),
				intval($data[1])-1,
				intval($data[2])
			]);
		}

		echo json_encode([
			'arrayData' => $arrayData,
			'status' => 'success',
			'titulo' => 'Data',
			'menssagem' => 'Data do profissional selecionado!!!',
		]);
	} elseif ($tipoRequest == 'SETHORAPROFISSIONAL'){
		$iMedico = $_POST['iMedico'];
		$data = $_POST['data'];
		$data = explode('/', $data); // dd/mm/yyyy
		$data = $data[2].'-'.$data[1].'-'.$data[0]; // yyyy-mm-dd

		$sql = "SELECT PrAgeData, PrAgeHoraInicio, PrAgeHoraFim, PrAgeIntervalo
		FROM ProfissionalAgenda
		WHERE PrAgeData = '$data' and PrAgeProfissional = $iMedico and PrAgeUnidade = $iUnidade";
		$result = $conn->query($sql);
		$row = $result->fetchAll(PDO::FETCH_ASSOC);

		$arrayHora = [true,];
		$intervalo = 30;
		foreach($row as $item){
			$horaI = explode(':', $item['PrAgeHoraInicio']);
			$horaF = explode(':', $item['PrAgeHoraFim']);
			$intervalo = intval($item['PrAgeIntervalo']);
			
			array_push($arrayHora,
			[
				'from' => [intval($horaI[0]), intval($horaI[1])],
				'to' => [intval($horaF[0]), intval($horaF[1])],
			]);
		}

		$sql = "SELECT AgendHorario 
		FROM Agendamento
		WHERE AgendData = '$data' AND AgendProfissional = $iMedico AND AgendUnidade = $iUnidade";
		$result = $conn->query($sql);
		$row2 = $result->fetchAll(PDO::FETCH_ASSOC);

		$horariosIndisp = [];
		foreach ($row2 as $item ) {		
			$horaA = explode(':', $item['AgendHorario']);
			array_push($horariosIndisp, [ intval($horaA[0]), intval($horaA[1]) ] );		
		}

		echo json_encode([
			'horariosIndisp' => $horariosIndisp,
			'arrayHora' => $arrayHora,
			'intervalo'=> $intervalo,
			'status' => 'success',
			'titulo' => 'Data',
			'menssagem' => 'Hora do profissional selecionado!!!',
		]);
	} elseif ($tipoRequest == 'AUDITORIA'){
		$tipo = $_POST['tipo'];
		$id = $_POST['id'];

		$sql = "SELECT UsuarNome,ProfiNome, ClienNome, AgendData as dataRegistro, AgendHorario as horaRegistro, AgendDataRegistro as dtHrRegistro
			FROM Agendamento
			JOIN Cliente ON ClienId = AgendCliente
			JOIN Profissional ON ProfiId = AgendProfissional
			JOIN Usuario ON UsuarId = AgendUsuarioAtualizador
			WHERE AgendUnidade = $iUnidade and AgendId = $id";
		$result = $conn->query($sql);
		$row = $result->fetch(PDO::FETCH_ASSOC);

		echo json_encode([
			'auditoria' => $row,
			'status' => 'success',
			'titulo' => 'Data',
			'menssagem' => 'Hora do profissional selecionado!!!',
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
