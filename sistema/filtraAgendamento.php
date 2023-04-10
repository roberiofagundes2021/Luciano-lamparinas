<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

$tipoRequest = isset($_POST['tipoRequest'])?$_POST['tipoRequest']:'';

try{
	$iUnidade = $_SESSION['UnidadeId'];
	$usuarioId = $_SESSION['UsuarId'];

	if($tipoRequest == 'AGENDAMENTOS'){
		$sql = "SELECT AgendId,AgendDataRegistro,AgendData,AgendHoraInicio,AgendHoraFim,AtModNome,AgendModalidade,
		AgendClienteResponsavel,AgendAtendimentoLocal,AtLocId,AgendServico,AgendObservacao,
		C.ClienNome as ClienNome,C.ClienId as ClienId,C.ClienCelular as ClienCelular,C.ClienTelefone as ClienTelefone,C.ClienEmail as ClienEmail,C.ClienDtNascimento as ClienDtNascimento,C.ClienCodigo as ClienCodigo,
		CR.ClienNome as RespoNome,CR.ClienId as RespoId,CR.ClienCelular as RespoCelular,CR.ClienTelefone as RespoTelefone,CR.ClienEmail as RespoEmail,CR.ClienDtNascimento as RespoDtNascimento,CR.ClienCodigo as RespoCodigo,
		AgendSituacao,SituaNome,SituaChave,SituaCor,Profissional.ProfiNome as ProfissionalNome,
		AtLocNome, SrVenNome, ProfiCbo, Profissao.ProfiNome as ProfissaoNome
		FROM Agendamento
		JOIN AtendimentoModalidade ON AtModId = AgendModalidade
		JOIN Situacao ON SituaId = AgendSituacao
		JOIN Cliente C ON C.ClienId = AgendCliente
		LEFT JOIN Cliente CR ON CR.ClienId = AgendClienteResponsavel
		JOIN Profissional ON Profissional.ProfiId = AgendProfissional
		JOIN Profissao ON Profissional.ProfiProfissao = Profissao.ProfiId
		JOIN AtendimentoLocal ON AtLocId = AgendAtendimentoLocal
		JOIN ServicoVenda ON SrVenId = AgendServico
		WHERE AgendUnidade = $iUnidade";

		if(isset($_POST['status']) && $_POST['status']){
			$sql .= " AND SituaId = $_POST[status]";
		}
		if(isset($_POST['inicio']) && $_POST['inicio']){
			$sql .= " AND AgendData >= '$_POST[inicio]'";
		}
		if(isset($_POST['fim']) && $_POST['fim']){
			$sql .= " AND AgendData <= '$_POST[fim]'";
		}

		$prof = "(null)";

		if(isset($_POST['profissionais'])){
			$prof = "(";
			foreach($_POST['profissionais'] as $key => $item){
				$prof .= "$item,";
			}
			$prof = substr($prof, 0, -1);
			$prof .= ")";
		}
		$sql .= " AND Profissional.ProfiId in $prof";

		$result = $conn->query($sql);
		$row = $result->fetchAll(PDO::FETCH_ASSOC);

		$array = [];

		foreach($row as $item){
			array_push($array,[
				'id' => $item['AgendId'],
				'status' => 'ATT',
				'registro' => $item['AgendDataRegistro'],
				'data' => $item['AgendData'],
				'horaInicio' => $item['AgendHoraInicio'],
				'horaFim' => $item['AgendHoraFim']?$item['AgendHoraFim']:'',
				'servico' => $item['AgendServico']?$item['AgendServico']:'',
				'observacao' => $item['AgendObservacao'],

				'servico' => [
					'id' => $item['AgendServico'],
					'nome' => $item['SrVenNome'],
				],
				'cliente' => [
					'id' => $item['ClienId'],
					'nome' => $item['ClienNome'],
					'celular' => $item['ClienCelular'],
					'telefone' => $item['ClienTelefone'],
					'email' => $item['ClienEmail'],
					'nascimento' => $item['ClienDtNascimento'],
					'codigo' => $item['ClienCodigo']
				],
				'responsavel' => isset($item['RespoId']) && $item['RespoId']?[
					'id' => $item['RespoId'],
					'nome' => $item['RespoNome'],
					'celular' => $item['RespoCelular'],
					'telefone' => $item['RespoTelefone'],
					'email' => $item['RespoEmail'],
					'nascimento' => $item['RespoDtNascimento'],
					'codigo' => $item['RespoCodigo']
				]:null,
				'local' => [
					'id' => $item['AtLocId'],
					'nome' => $item['AgendAtendimentoLocal']
				],
				'modalidade' => [
					'id' => $item['AgendModalidade'],
					'nome' => $item['AtModNome']
				],
				'situacao' => [
					'id' => $item['AgendSituacao'],
					'nome' => $item['SituaNome'],
					'chave' => $item['SituaChave'],
					'cor' => $item['SituaCor']
				]
			]);
		}
		
		echo json_encode($array);
	} elseif($tipoRequest == 'GETAGENDAMENTO'){
		$id = $_POST['id'];
		$type = $_POST['type'];

		if($type == 'AGENDAMENTO'){
			$sql = "SELECT AgendId,AgendDataRegistro,AgendData,AgendProfissional,AgendHoraInicio,AgendHoraFim,
				AgendModalidade,AgendClienteResponsavel,AgendAtendimentoLocal,AgendServico,AgendCliente,
				AgendObservacao,AgendSituacao
				FROM Agendamento
				WHERE AgendId = $id AND AgendUnidade = $iUnidade";
			$result = $conn->query($sql);
			$rowAgendamento = $result->fetch(PDO::FETCH_ASSOC);
	
			$horaI = explode(':',$rowAgendamento['AgendHoraInicio']);
			$horaF = $rowAgendamento['AgendHoraFim']?explode(':',$rowAgendamento['AgendHoraFim']):'';
			
			echo json_encode([
				'id' => $rowAgendamento['AgendId'],
				'registro' => $rowAgendamento['AgendDataRegistro'],
				'data' => $rowAgendamento['AgendData'],
				'horaInicio' => "$horaI[0]:$horaI[1]",
				'horaFim' => $horaF?"$horaF[0]:$horaF[1]":'',
				'servico' => $rowAgendamento['AgendServico'],
				'observacao' => $rowAgendamento['AgendObservacao'],
				'status' => 'ATT',
	
				'servico' => $rowAgendamento['AgendServico'],
				'cliente' => $rowAgendamento['AgendCliente'],
				'profissional' => $rowAgendamento['AgendProfissional'],
				'responsavel' => isset($rowAgendamento['RespoId']) && $rowAgendamento['RespoId']?$rowAgendamento['RespoId']:null,
				'local' => $rowAgendamento['AgendAtendimentoLocal'],
				'modalidade' => $rowAgendamento['AgendModalidade'],
				'situacao' => $rowAgendamento['AgendSituacao']
			]);
		}elseif($type == 'BLOQUEIO'){
			$sql = "SELECT AgBloId,AgBloDataHoraInicio,AgBloDataHoraFim,AgBloRecorrenteSegunda,AgBloRecorrenteTerca,
				AgBloRecorrenteQuarta,AgBloRecorrenteQuinta,AgBloRecorrenteSexta,AgBloRecorrenteSabado,
				AgBloRecorrenteDomingo,AgBloRecorrenteQuantidade,AgBloRecorrenteDataFinal,
				AgBloRecorrenteRepeticao,AgBloDescricao,AgBloJustificativa,AgBloProfissional
				FROM AgendamentoBloqueio
				WHERE AgBloId = $id AND AgBloUnidade = $iUnidade";
			$resultBloqueio = $conn->query($sql);
			$resultBloqueio = $resultBloqueio->fetch(PDO::FETCH_ASSOC);
			
			echo json_encode([
				'id' => $resultBloqueio['AgBloId'],
				'descricao' => $resultBloqueio['AgBloDescricao'],
				'justificativa' => $resultBloqueio['AgBloJustificativa'],
				'profissional' => $resultBloqueio['AgBloProfissional'],
				'dataI' => explode(' ',$resultBloqueio['AgBloDataHoraInicio'])[0],
				'dataF' => explode(' ',$resultBloqueio['AgBloDataHoraFim'])[0],
				'horaI' => explode(' ',$resultBloqueio['AgBloDataHoraInicio'])[1],
				'horaF' => explode(' ',$resultBloqueio['AgBloDataHoraFim'])[1],
				'segunda' => $resultBloqueio['AgBloRecorrenteSegunda'],
				'terca' => $resultBloqueio['AgBloRecorrenteTerca'],
				'quarta' => $resultBloqueio['AgBloRecorrenteQuarta'],
				'quinta' => $resultBloqueio['AgBloRecorrenteQuinta'],
				'sexta' => $resultBloqueio['AgBloRecorrenteSexta'],
				'sabado' => $resultBloqueio['AgBloRecorrenteSabado'],
				'domingo' => $resultBloqueio['AgBloRecorrenteDomingo'],
				'quantidade' => $resultBloqueio['AgBloRecorrenteQuantidade'],
				'dataFinal' => $resultBloqueio['AgBloRecorrenteDataFinal'],
				'repeticao' => $resultBloqueio['AgBloRecorrenteRepeticao'],
			]);
		}
	} elseif($tipoRequest == 'PACIENTES'){
	
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
	} elseif($tipoRequest == 'MODALIDADES'){
	
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
	} elseif($tipoRequest == 'SERVICOS'){
		// $sql = "SELECT SrVenId,SrVenNome,SrVenPlanoConta,SrVenDetalhamento,SrVenValorCusto,
		// SrVenOutrasDespesas,SrVenCustoFinal,SrVenMargemLucro,SrVenValorVenda,SrVenStatus,
		// SrVenUsuarioAtualizador,SrVenUnidade
		// FROM Servico WHERE SrVenUnidade = $iUnidade";
		$modalidade = isset($_POST['modalidade'])?$_POST['modalidade']:false;
		$array = [];

		if($modalidade){
			$sql = "SELECT SrVenId,SrVenNome,SrVenCodigo,SVXMoValorVenda
			FROM ServicoVenda
			JOIN ServicoVendaXModalidade ON SVXMoServicoVenda = SrVenId
			WHERE SrVenUnidade = $iUnidade and SVXMoModalidade = $modalidade";
			$result = $conn->query($sql);
			$result = $result->fetchAll(PDO::FETCH_ASSOC);

			foreach($result as $item){
				array_push($array,[
					'id' => $item['SrVenId'],
					'nome' => $item['SrVenNome'],
					'valor' => $item['SVXMoValorVenda'],
					'codigo' => $item['SrVenCodigo']
				]);
			}
		}

		echo json_encode($array);
	} elseif($tipoRequest == 'MEDICOS'){
		$servico = isset($_POST['servico'])?$_POST['servico']:null;

		$sql = "SELECT DISTINCT ProfiId,ProfiNome
		FROM ProfissionalXServicoVenda
		JOIN Profissional ON ProfiId = PrXSVProfissional
		WHERE ProfiUnidade = $iUnidade";

		if($servico){
			$sql .= "AND PrXSVServicoVenda = $servico";
		}

		$result = $conn->query($sql);
		$result = $result->fetchAll(PDO::FETCH_ASSOC);

		$array = [];
		foreach($result as $item){
			$arrayDatasRecorrente = [];
			$arrayDatasIntervalo = [];

			$sql = "SELECT DISTINCT AgBloId,AgBloDataHoraInicio,AgBloDataHoraFim,AgBloRecorrenteSegunda,AgBloRecorrenteTerca,
				AgBloRecorrenteQuarta,AgBloRecorrenteQuinta,AgBloRecorrenteSexta,AgBloRecorrenteSabado,
				AgBloRecorrenteDomingo,AgBloRecorrenteQuantidade,AgBloRecorrenteDataFinal,AgBloRecorrenteRepeticao
				FROM AgendamentoBloqueio
				WHERE AgBloProfissional = $item[ProfiId] AND AgBloUnidade = $iUnidade";
			$resultBloqueio = $conn->query($sql);
			$resultBloqueio = $resultBloqueio->fetchAll(PDO::FETCH_ASSOC);

			foreach($resultBloqueio as $bloqueio){
				$arrayDays = [];

				// vai montar um array com os dias da semana que estão marcados para bloqueio
				if($bloqueio['AgBloRecorrenteSegunda']){
					array_push($arrayDays,'Mon'); //Segunda
				}if($bloqueio['AgBloRecorrenteTerca']){
					array_push($arrayDays,'Tue'); //Terça
				}if($bloqueio['AgBloRecorrenteQuarta']){
					array_push($arrayDays,'Wed'); //Quarta
				}if($bloqueio['AgBloRecorrenteQuinta']){
					array_push($arrayDays,'Thu'); //Quinta
				}if($bloqueio['AgBloRecorrenteSexta']){
					array_push($arrayDays,'Fri'); //Sexta
				}if($bloqueio['AgBloRecorrenteSabado']){
					array_push($arrayDays,'Sat'); //Sábado
				}if($bloqueio['AgBloRecorrenteDomingo']){
					array_push($arrayDays,'Sun'); //Domingo
				}

				$dt = explode(' ',$bloqueio['AgBloDataHoraInicio'])[0];
				$hr = explode(' ',$bloqueio['AgBloDataHoraInicio'])[1];
				$quantidade = $bloqueio['AgBloRecorrenteQuantidade']?intval($bloqueio['AgBloRecorrenteQuantidade']):0;

				// se o bloqueio for recorrente...
				if($quantidade){
					$data = date_create($dt); // pegando a data de início como objeto date...
					$repeticao = $bloqueio['AgBloRecorrenteRepeticao']; // 1S/2S/3S...

					// define as datas de acordo à repetição
					if($repeticao[1]=="S"){// se for semanal "S"...
						for($x=0; $x < $quantidade; $x++){
							$dateLoop = date_create(date_format($data,"Y-m-d"));
							$notfound = true;
							$loopWhile = true;

							while($loopWhile){
								if(in_array(date_format($dateLoop,"D"),$arrayDays)){
									array_push($arrayDatasRecorrente, [
										'id' => $bloqueio['AgBloId'],
										'data' => date_format($dateLoop,"Y-m-d")
									]);
									$notfound = false;
								}
								$loopWhile = date_format($dateLoop,"D") == 'Sat'?false:true;
								if($loopWhile){
									$dateLoop = $dateLoop->modify("+1 day");
								}
							}

							$x = $notfound?$x-1:$x;

							$d = intval($repeticao[0]);
							for($y=0; $y < $d; $y++){
								$loopWhile = true;
								while($loopWhile){
									$data = $data->modify("+1 day");
									$loopWhile = date_format($data,"D") == 'Sun'?false:true;
								}
							}
						}
					}
				}else{
					$dtF = explode(' ',$bloqueio['AgBloDataHoraFim'])[0];
					$hrF = explode(' ',$bloqueio['AgBloDataHoraFim'])[1];

					array_push($arrayDatasIntervalo, [
						'id' => $bloqueio['AgBloId'],
						'dataI' => $dt,
						'dataF' => $dtF,
						'horaI' => $hr,
						'horaF' => $hrF,
					]);
				}
			}
			
			array_push($array,[
				'id' => $item['ProfiId'],
				'nome' => $item['ProfiNome'],
				'datasRecorrente' => $arrayDatasRecorrente,
				'datasIntervalo' => $arrayDatasIntervalo,
			]);
		}

		echo json_encode($array);
	} elseif($tipoRequest == 'LOCALATENDIMENTO'){
		// $iMedico = $_POST['iMedico'];
		$hoje = date('Y-m-d');

		// $sql= "SELECT DISTINCT AtLocId,AtLocNome,AtLocStatus,AtLocUsuarioAtualizador,AtLocUnidade
		// FROM AtendimentoLocal
		// JOIN ProfissionalAgenda ON PrAgeAtendimentoLocal = AtLocId
		// WHERE PrAgeProfissional = $iMedico AND PrAgeData >= '$hoje' AND AtLocUnidade = $iUnidade";
		// $result = $conn->query($sql);

		$sql= "SELECT DISTINCT AtLocId,AtLocNome,AtLocStatus,AtLocUsuarioAtualizador,AtLocUnidade
		FROM AtendimentoLocal
		WHERE AtLocUnidade = $iUnidade";
		$result = $conn->query($sql);

		$array = [];
		foreach($result as $item){
			array_push($array,[
				'id' => $item['AtLocId'],
				'nome' => $item['AtLocNome'],
			]);
		}

		echo json_encode($array);
	} elseif($tipoRequest == 'ADDAGENDAMENTO'){
		$registro = date('Y-m-d');
		$data = $_POST['data'];
		$horaI = $_POST['horaI'];
		$horaF = $_POST['horaF'];
		$paciente = $_POST['paciente'];
		$modalidade = $_POST['modalidade'];
		$servico = $_POST['servico'];
		$profissional = $_POST['profissional'];
		$local = $_POST['local'];
		$observacao = $_POST['observacao'];
		$cmbSituacao = $_POST['situacao'];
		$isUpdate = $_POST['idAgendamento'];

		// $sql = "SELECT SrVenId,SrVenNome,SrVenCodigo,SVXMoValorVenda
		// 	FROM ServicoVenda
		// 	JOIN ServicoVendaXModalidade ON SVXMoServicoVenda = SrVenId
		// 	WHERE SVXMoModalidade = $modalidade AND SVXMoServicoVenda = $servico
		// 	AND SrVenUnidade = $iUnidade";
		// $result = $conn->query($sql);
		// $result = $result->fetch(PDO::FETCH_ASSOC);

		if($isUpdate){
			if($data < date('Y-m-d') || ($data == date('Y-m-d') && $hora < date('H:i'))){
				$sql = "UPDATE Agendamento SET
					AgendSituacao = $cmbSituacao,
					AgendUsuarioAtualizador = $usuarioId
					WHERE AgendId = $isUpdate";
			}else{
				$sql = "UPDATE Agendamento SET 
					AgendCliente = $paciente,
					AgendModalidade = $modalidade,
					AgendServico = $servico, 
					AgendProfissional = $profissional,
					AgendData = '$data',
					AgendHoraInicio = '$horaI',
					AgendHoraFim = '$horaF',
					AgendAtendimentoLocal = $local,
					AgendObservacao = '$observacao',
					AgendSituacao = $cmbSituacao,
					AgendUnidade = $iUnidade,
					AgendUsuarioAtualizador = $usuarioId,
					$result[SVXMoValorVenda]
					WHERE AgendId = $isUpdate";
			}
			$conn->query($sql);

			echo json_encode([
				'titulo' => 'Atualizar agendamento',
				'status' => 'success',
				'menssagem' => 'Agendamento atualizado com sucesso!!!',
			]);
		}else{
			if($data < date('Y-m-d') || ($data == date('Y-m-d') && $hora < date('H:i'))){
				echo json_encode([
					'titulo' => 'Incluir agendamento',
					'status' => 'error',
					'menssagem' => 'Agendamento com data retroativa!!!'
				]);
			}

			foreach($data as $dt){
				$sql = "INSERT INTO Agendamento(AgendDataRegistro,AgendCliente,AgendModalidade,
					AgendServico,AgendProfissional,AgendData,AgendHoraInicio,AgendHoraFim,AgendAtendimentoLocal,
					AgendObservacao,AgendSituacao,AgendUnidade,AgendUsuarioAtualizador)
					VALUES ('$registro','$paciente','$modalidade','$servico','$profissional','$dt','$horaI','$horaF',
					'$local','$observacao','$cmbSituacao','$iUnidade','$usuarioId')";
				$result = $conn->query($sql);
			}


			echo json_encode([
				'titulo' => 'Incluir agendamento',
				'status' => 'success',
				'menssagem' => 'Agendamento inserido com sucesso!!!',
				'sql' => $result
			]);
		}
	} elseif($tipoRequest == 'ATTAGENDAMENTO'){
		$cmbSituacao = $_POST['situacao'];

		if($isUpdate){
			$sql = "UPDATE Agendamento SET
				AgendSituacao = $cmbSituacao,
				AgendUsuarioAtualizador = $usuarioId
				WHERE AgendId = $isUpdate";
			$conn->query($sql);

			echo json_encode([
				'titulo' => 'Atualizar agendamento',
				'status' => 'success',
				'menssagem' => 'Agendamento atualizado com sucesso!!!',
			]);
		}else{
			$sql = "INSERT INTO Agendamento(AgendDataRegistro,AgendCliente,AgendModalidade,
			AgendServico,AgendProfissional,AgendData,AgendHoraInicio,AgendHoraFim,AgendAtendimentoLocal,
			AgendObservacao,AgendSituacao,AgendUnidade,AgendUsuarioAtualizador)
			VALUES ('$registro','$paciente','$modalidade','$servico','$profissional','$data','$horaI','$horaF',
			'$local','$observacao','$cmbSituacao','$iUnidade','$usuarioId')";
			$result = $conn->query($sql);

			echo json_encode([
				'titulo' => 'Incluir agendamento',
				'status' => 'success',
				'menssagem' => 'Agendamento inserido com sucesso!!!',
				'sql' => $result
			]);
		}
	} elseif($tipoRequest == 'ADDPACIENTENOVO'){
		$tipoRequest = isset($_POST['tipoRequest'])?$_POST['tipoRequest']:null;
		$prontuario = isset($_POST['prontuario'])?$_POST['prontuario']:null;
		$nome = isset($_POST['nome'])?$_POST['nome']:null;
		$nomeSocial = isset($_POST['nomeSocial'])?$_POST['nomeSocial']:null;
		$cpf = isset($_POST['cpf'])?$_POST['cpf']:null;
		$cns = isset($_POST['cns'])?$_POST['cns']:null;
		$rg = isset($_POST['rg'])?$_POST['rg']:null;
		$emissor = isset($_POST['emissor'])?$_POST['emissor']:null;
		$uf = isset($_POST['uf'])?$_POST['uf']:null;
		$sexo = isset($_POST['sexo'])?$_POST['sexo']:null;
		$nascimento = isset($_POST['nascimento'])?$_POST['nascimento']:null;
		$nomePai = isset($_POST['nomePai'])?$_POST['nomePai']:null;
		$nomeMae = isset($_POST['nomeMae'])?$_POST['nomeMae']:null;
		$racaCor = isset($_POST['racaCor'])?$_POST['racaCor']:null;
		$naturalidade = isset($_POST['naturalidade'])?$_POST['naturalidade']:null;
		$profissao = isset($_POST['profissao'])?$_POST['profissao']:null;
		$estadoCivil = isset($_POST['estadoCivil'])?$_POST['estadoCivil']:null;
		$cep = isset($_POST['cep'])?$_POST['cep']:null;
		$endereco = isset($_POST['endereco'])?$_POST['endereco']:null;
		$numero = isset($_POST['numero'])?$_POST['numero']:null;
		$complemento = isset($_POST['complemento'])?$_POST['complemento']:null;
		$bairro = isset($_POST['bairro'])?$_POST['bairro']:null;
		$cidade = isset($_POST['cidade'])?$_POST['cidade']:null;
		$estado = isset($_POST['estado'])?$_POST['estado']:null;
		$contato = isset($_POST['contato'])?$_POST['contato']:$_POST['nome'];
		$telefone = isset($_POST['telefone'])?$_POST['telefone']:null;
		$celular = isset($_POST['celular'])?$_POST['celular']:null;
		$email = isset($_POST['email'])?$_POST['email']:null;
		$observacao = isset($_POST['observacao'])?$_POST['observacao']:null;
		$sCodigo = null;

		$sql = "SELECT ClienId
				FROM Cliente
				Where ClienCpf = $cpf";
		$result = $conn->query("$sql");
		$rowCPF = $result->fetchAll(PDO::FETCH_ASSOC);

		if(COUNT($rowCPF)){
			echo json_encode([
				'titulo' => 'Incluir paciente',
				'status' => 'error',
				'menssagem' => 'CPF já cadastrado!!!'
			]);
		}

		$sql = "SELECT SituaId
				FROM Situacao
				Where SituaChave = 'ATIVO'";
		$result = $conn->query("$sql");
		$rowSituacao = $result->fetch(PDO::FETCH_ASSOC);	

		$sql = "SELECT COUNT(isnull(clienCodigo,0)) as Codigo
				FROM Cliente
				Where ClienUnidade = $iUnidade";
		$result = $conn->query("$sql");
		$rowCodigo = $result->fetch(PDO::FETCH_ASSOC);	
		
		$sCodigo = (int)$rowCodigo['Codigo'] + 1;
		$sCodigo = str_pad($sCodigo,6,"0",STR_PAD_LEFT);
	
		// insere o novo usuário no banco
		$sql = "INSERT INTO  Cliente(ClienCodigo,ClienNome,ClienNomeSocial,ClienCpf,ClienRg,
		ClienOrgaoEmissor,ClienUf,ClienSexo,ClienDtNascimento,ClienNomePai,ClienNomeMae,ClienEstadoCivil,
		ClienNaturalidade,ClienRacaCor,ClienCartaoSus,ClienProfissao,ClienCep,
		ClienEndereco,ClienNumero,ClienComplemento,ClienBairro,ClienCidade,ClienEstado,ClienContato,
		ClienTelefone,ClienCelular,ClienEmail,ClienObservacao,ClienStatus,
		ClienUsuarioAtualizador,ClienUnidade)
		VALUES ('$sCodigo','$nomePaciente','$nomeSocial','$cpf','$rg','$emissor','$uf','$sexo','$nascimento','$nomePai','$nomeMae',
		'$estadoCivil','$naturalidade','$racaCor','$cns','$profissao','$cep','$endereco','$numero','$complemento',
		'$bairro','$cidade','$estado','$contato','$telefone','$celular','$email','$observacao',$rowSituacao[SituaId],$usuarioId,$iUnidade)";
		$conn->query($sql);

		$lestIdCliente = $conn->lastInsertId();
		echo json_encode([
			'titulo' => 'Incluir paciente',
			'status' => 'success',
			'menssagem' => 'Paciente inserido com sucesso!!!',
			'id' => $lestIdCliente,
		]);
	} elseif($tipoRequest == 'SITUACAO'){
		$sql = "SELECT SituaId, SituaNome, SituaChave
			FROM Situacao
			WHERE SituaChave in ('AGENDADO','CONFIRMADO','FILAESPERA','ATENDIDO','CANCELADO')";
		$result = $conn->query($sql);
		$situacoes = $result->fetchAll(PDO::FETCH_ASSOC);

		$array = [];
		foreach($situacoes as $item){
			array_push($array, [
				'id' => $item['SituaId'],
				'nome' => $item['SituaNome'],
				'chave' => $item['SituaChave'],
			]);
		}
		echo json_encode($array);
	} elseif($tipoRequest == 'UPDATEDATA'){
		$id = $_POST['id'];
		$data = $_POST['data'];
		$horaI = $_POST['horaI'];
		$horaF = isset($_POST['horaF'])?$_POST['horaF']:'';

		$sql = "UPDATE Agendamento SET
			AgendData = '$data',
			AgendHoraInicio = '$horaI',
			AgendHoraFim = '$horaF'
			WHERE AgendId = $id";
		$conn->query($sql);

		echo json_encode($data);
	} elseif($tipoRequest == 'ADDEVENTO'){
		$dataHoraInicio = '';
		$dataHoraFim = '';

		$type = $_POST['type'];
		$recorrente = $_POST['recorrente'];
		$medicoConfig = $_POST['medicoConfig'];
		$bloqueio = $_POST['bloqueio'];
		$justificativa = $_POST['justificativa'];
		$inputDataInicioBloqueio = $_POST['inputDataInicioBloqueio'];
		$inputHoraInicioBloqueio = $_POST['inputHoraInicioBloqueio'];
		$inputDataFimBloqueio = $_POST['inputDataFimBloqueio'];
		$inputHoraFimBloqueio = $_POST['inputHoraFimBloqueio'];

		$segunda = $recorrente?$_POST['segunda']:0;
		$terca = $recorrente?$_POST['terca']:0;
		$quarta = $recorrente?$_POST['quarta']:0;
		$quinta = $recorrente?$_POST['quinta']:0;
		$sexta = $recorrente?$_POST['sexta']:0;
		$sabado = $recorrente?$_POST['sabado']:0;
		$domingo = $recorrente?$_POST['domingo']:0;
		$repeticao = $recorrente?$_POST['repeticao']:'';

		$quantidadeRecorrencia = $recorrente && $_POST['quantidadeRecorrencia']?($_POST['quantidadeRecorrencia']>99?99:$_POST['quantidadeRecorrencia']):0;
		$dataRecorrencia = $recorrente?$_POST['dataRecorrencia']:'';
		$dataHoraInicio = $inputDataInicioBloqueio.' '.$inputHoraInicioBloqueio;

		if(!$recorrente){
			$dataHoraFim = $inputDataFimBloqueio.' '.$inputHoraFimBloqueio;
		}

		if($type == 'NEW'){
			$sql = "INSERT INTO AgendamentoBloqueio(AgBloProfissional,AgBloDescricao,AgBloJustificativa,
			AgBloDataHoraInicio,AgBloDataHoraFim,AgBloRecorrenteRepeticao,AgBloRecorrenteSegunda,
			AgBloRecorrenteTerca,AgBloRecorrenteQuarta,AgBloRecorrenteQuinta,AgBloRecorrenteSexta,
			AgBloRecorrenteSabado,AgBloRecorrenteDomingo,AgBloRecorrenteQuantidade,AgBloRecorrenteDataFinal,
			AgBloUnidade)
			VALUES($medicoConfig,'$bloqueio','$justificativa','$dataHoraInicio','$dataHoraFim',
			'$repeticao',$segunda,$terca,$quarta,$quinta,$sexta,$sabado,$domingo,$quantidadeRecorrencia,
			'$dataRecorrencia',$iUnidade)";
			$conn->query($sql);
	
			echo json_encode([
				'titulo' => 'Evento',
				'status' => 'success',
				'menssagem' => 'Evento agendado com sucesso!!!'
			]);
		}else{
			$sql = "UPDATE AgendamentoBloqueio SET
				AgBloProfissional = $medicoConfig,
				AgBloDescricao = '$bloqueio',
				AgBloJustificativa = '$justificativa',
				AgBloDataHoraInicio = '$dataHoraInicio',
				AgBloDataHoraFim = '$dataHoraFim',
				AgBloRecorrenteRepeticao = '$repeticao',
				AgBloRecorrenteSegunda = $segunda,
				AgBloRecorrenteTerca = $terca,
				AgBloRecorrenteQuarta = $quarta,
				AgBloRecorrenteQuinta = $quinta,
				AgBloRecorrenteSexta = $sexta,
				AgBloRecorrenteSabado = $sabado,
				AgBloRecorrenteDomingo = $domingo,
				AgBloRecorrenteQuantidade = $quantidadeRecorrencia,
				AgBloRecorrenteDataFinal = '$dataRecorrencia'
				WHERE AgBloId = $type";
			$conn->query($sql);
			echo json_encode([
				'titulo' => 'Evento',
				'status' => 'success',
				'menssagem' => 'Evento atualizado com sucesso!!!'
			]);
		}

	} elseif($tipoRequest == 'ADDCONFIGUNIDADE'){
		$horaAbertura = $_POST['inputHoraAberturaUnidade'];
		$horaFechamento = $_POST['inputHoraFechamentoUnidade'];
		$horaInicio = $_POST['inputHoraInicioUnidade'];
		$horaFim = $_POST['inputHoraFimUnidade'];
		$intervalo = $_POST['inputHoraIntervaloUnidade'];
		$observacao = $_POST['observacaoUnidade'];

		$segunda = $_POST['segunda'];
		$terca = $_POST['terca'];
		$quarta = $_POST['quarta'];
		$quinta = $_POST['quinta'];
		$sexta = $_POST['sexta'];
		$sabado = $_POST['sabado'];
		$domingo = $_POST['domingo'];

		$sql = "SELECT AgFUnId,AgFUnSegunda,AgFUnTerca,AgFUnQuarta,AgFUnQuinta,AgFUnSexta,AgFUnSabado,AgFUnDomingo,AgFUnObservacao,
		AgFUnHorarioAbertura,AgFUnHorarioFechamento,AgFUnHorarioAlmocoInicio,AgFUnHorarioAlmocoFim,AgFUnIntervaloAgenda
		FROM AgendamentoFuncionamentoUnidade WHERE AgFUnUnidade = $iUnidade";
		$result = $conn->query($sql);
		$rowConfig = $result->fetch(PDO::FETCH_ASSOC);

		
		if($rowConfig){
			$sql = "UPDATE AgendamentoFuncionamentoUnidade SET
			AgFUnSegunda= $segunda,
			AgFUnTerca = $terca,
			AgFUnQuarta = $quarta,
			AgFUnQuinta = $quinta,
			AgFUnSexta = $sexta,
			AgFUnSabado = $sabado,
			AgFUnDomingo = $domingo,
			AgFUnObservacao = '$observacao',
			AgFUnHorarioAbertura = '$horaAbertura',
			AgFUnHorarioFechamento = '$horaFechamento',
			AgFUnHorarioAlmocoInicio = '$horaInicio',
			AgFUnHorarioAlmocoFim = '$horaFim',
			AgFUnIntervaloAgenda = $intervalo
			WHERE AgFUnId = $rowConfig[AgFUnId] AND AgFUnUnidade = $iUnidade";
			$conn->query($sql);
		}else{
			$sql = "INSERT INTO AgendamentoFuncionamentoUnidade(AgFUnSegunda,AgFUnTerca,AgFUnQuarta,AgFUnQuinta,
			AgFUnSexta,AgFUnSabado,AgFUnDomingo,AgFUnObservacao,AgFUnHorarioAbertura,AgFUnHorarioFechamento,
			AgFUnHorarioAlmocoInicio,AgFUnHorarioAlmocoFim,AgFUnIntervaloAgenda,AgFUnUnidade)
			VALUES($segunda,$terca,$quarta,$quinta,$sexta,$sabado,$domingo,'$observacao','$horaAbertura','$horaFechamento',
			'$horaInicio','$horaFim',$intervalo,$iUnidade)";
			$conn->query($sql);
		}

		echo json_encode([
			'titulo' => 'Configuração de Unidade',
			'status' => 'success',
			'menssagem' => 'Configuração de Unidade salva com sucesso!!!'
		]);
	} elseif($tipoRequest == 'EXCLUI'){
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
	} elseif($tipoRequest == 'GETCONFIG'){
		$obj = [];

		$sql = "SELECT AgFUnId,AgFUnSegunda,AgFUnTerca,AgFUnQuarta,AgFUnQuinta,AgFUnSexta,AgFUnSabado,AgFUnDomingo,AgFUnObservacao,
		AgFUnHorarioAbertura,AgFUnHorarioFechamento,AgFUnHorarioAlmocoInicio,AgFUnHorarioAlmocoFim,AgFUnIntervaloAgenda
		FROM AgendamentoFuncionamentoUnidade WHERE AgFUnUnidade = $iUnidade";
		$result = $conn->query($sql);
		$rowConfig = $result->fetch(PDO::FETCH_ASSOC);

		if($rowConfig){
			$obj = [
				'id' => $rowConfig['AgFUnId'],
				'segunda' => $rowConfig['AgFUnSegunda'],
				'terca' => $rowConfig['AgFUnTerca'],
				'quarta' => $rowConfig['AgFUnQuarta'],
				'quinta' => $rowConfig['AgFUnQuinta'],
				'sexta' => $rowConfig['AgFUnSexta'],
				'sabado' => $rowConfig['AgFUnSabado'],
				'domingo' => $rowConfig['AgFUnDomingo'],
				'observacao' => $rowConfig['AgFUnObservacao'],
				'abertura' => explode('.',$rowConfig['AgFUnHorarioAbertura'])[0],
				'fechamento' => explode('.',$rowConfig['AgFUnHorarioFechamento'])[0],
				'almocoInicio' => explode('.',$rowConfig['AgFUnHorarioAlmocoInicio'])[0],
				'almocoFim' => explode('.',$rowConfig['AgFUnHorarioAlmocoFim'])[0],
				'intervalo' => $rowConfig['AgFUnIntervaloAgenda']
			];
		}

		echo json_encode($obj);
	} elseif($tipoRequest == 'CHECKAGENDAUNIDADE'){
		$data = $_POST['data'];
		$horaI = isset($_POST['horaI'])?$_POST['horaI']:false;
		$horaF = isset($_POST['horaF'])?$_POST['horaF']:false;

		$diaAtual = date_create($data);
		$diaAtual = date_format($diaAtual,"D");

		switch($diaAtual){
			case 'Mon': $diaAtual = 'AgFUnSegunda';break;
			case 'Tue': $diaAtual = 'AgFUnTerca';break;
			case 'Wed': $diaAtual = 'AgFUnQuarta';break;
			case 'Thu': $diaAtual = 'AgFUnQuinta';break;
			case 'Fri': $diaAtual = 'AgFUnSexta';break;
			case 'Sat': $diaAtual = 'AgFUnSabado';break;
			case 'Sun': $diaAtual = 'AgFUnDomingo';break;
			default: $diaAtual = '';break;
		}

		$sql = "SELECT AgFUnSegunda,AgFUnTerca,AgFUnQuarta,AgFUnQuinta,AgFUnSexta,AgFUnSabado,AgFUnDomingo,
		AgFUnHorarioAbertura,AgFUnHorarioFechamento,AgFUnHorarioAlmocoInicio,AgFUnHorarioAlmocoFim
		FROM AgendamentoFuncionamentoUnidade WHERE AgFUnUnidade = $iUnidade";
		$result = $conn->query($sql);
		$config = $result->fetch(PDO::FETCH_ASSOC);

		if(($config[$diaAtual])){
			if($horaI && $horaF){
				$abertura = explode('.',$config['AgFUnHorarioAbertura'])[0];
				$fechamento = explode('.',$config['AgFUnHorarioFechamento'])[0];
				$almoçoI = explode('.',$config['AgFUnHorarioAlmocoInicio'])[0];
				$almoçoF = explode('.',$config['AgFUnHorarioAlmocoFim'])[0];
	
				if($horaI >= $abertura && $horaI < $fechamento && $horaI >= $abertura && $horaI < $fechamento){
					if($horaI >= $almoçoI && $horaI < $almoçoF && $horaF >= $almoçoI && $horaF < $almoçoF){
						echo json_encode([
							'titulo' => 'Agendamento',
							'tipo' => 'error',
							'menssagem' => 'O horário selecionado está reservado para almoço na unidade!!',
						]);
					}else{
						echo json_encode([
							'titulo' => 'Agendamento',
							'tipo' => 'success',
							'menssagem' => 'data e hora válida',
						]);
					}
				}else{
					echo json_encode([
						'titulo' => 'Agendamento',
						'tipo' => 'error',
						'menssagem' => 'O horário não condiz com o de funcionamento da unidade!!',
					]);
				}
			}else{
				echo json_encode([
					'titulo' => 'Agendamento',
					'tipo' => 'success',
					'menssagem' => 'data válida',
				]);
			}
		}else{
			echo json_encode([
				'titulo' => 'Agendamento',
				'tipo' => 'error',
				'menssagem' => 'A data selecionado não condiz com o de funcionamento da unidade',
			]);
		}
		
	} elseif($tipoRequest == 'GETBLOQUEIOS'){
		$sql = "SELECT AgBloId,AgBloDataHoraInicio,AgBloDataHoraFim,ProfiNome,AgBloDescricao,AgBloRecorrenteQuantidade,
			AgBloRecorrenteDataFinal,AgBloRecorrenteRepeticao
			FROM AgendamentoBloqueio
			JOIN Profissional ON ProfiId = AgBloProfissional
			WHERE AgBloUnidade = $iUnidade";
		$resultBloqueio = $conn->query($sql);
		$resultBloqueio = $resultBloqueio->fetchAll(PDO::FETCH_ASSOC);

		$array = [];
		foreach($resultBloqueio as $bloqueio){
			if($bloqueio['AgBloRecorrenteQuantidade'] && $bloqueio['AgBloRecorrenteQuantidade']>0){
				$DI = explode('.',$bloqueio['AgBloDataHoraInicio'])[0]; // "yyyy-mm-dd hh:ii:ss.000000" => "yyyy-mm-dd hh:ii:ss"
				$DF = explode('-',$bloqueio['AgBloRecorrenteDataFinal']); // "yyyy-mm-dd" => ['yyyy','mm','dd']
	
				$HI = '';
	
				$DI = explode(' ',$DI)[0]; // "yyyy-mm-dd" => ['yyyy-mm-dd', 'hh:ii:ss']
				$DI = explode('-',$DI); // "yyyy-mm-dd" => ['yyyy','mm','dd']
				$DI = "$DI[2]/$DI[1]/$DI[0]"; // ['yyyy','mm','dd'] => dd/mm/yyyy
	
				$HF = '';

				$DF = "$DF[2]/$DF[1]/$DF[0]"; // ['yyyy','mm','dd'] => dd/mm/yyyy

				$recorrente = "SIM";

				if($bloqueio['AgBloRecorrenteRepeticao'][1]=="S"){
					$repeticao = $bloqueio['AgBloRecorrenteRepeticao'][0]==0?'Semanal':"{$bloqueio['AgBloRecorrenteRepeticao'][0]} Semanas";
				}else{
					$repeticao = $bloqueio['AgBloRecorrenteRepeticao'][0]==0?'Mensal':"{$bloqueio['AgBloRecorrenteRepeticao'][0]} Meses";
				}
				$repeticao = "$repeticao ($bloqueio[AgBloRecorrenteQuantidade] X)";
			}else{
				$DI = explode('.',$bloqueio['AgBloDataHoraInicio'])[0]; // yyyy-mm-dd hh:ii:ss.000000 => yyyy-mm-dd hh:ii:ss
				$DF = explode('.',$bloqueio['AgBloDataHoraFim'])[0]; // yyyy-mm-dd hh:ii:ss.000000 => yyyy-mm-dd hh:ii:ss
	
				$HI = explode(' ',$DI)[1]; // "yyyy-mm-dd hh:ii:ss" => ['yyyy-mm-dd', 'hh:ii:ss']
				$HI = explode(':',$HI); // "hh:ii:ss" => ['hh','ii','ss']
				$HI = "$HI[0]:$HI[1]"; // ['hh','ii','ss'] => "hh:ii"
	
				$DI = explode(' ',$DI)[0]; // "yyyy-mm-dd hh:ii:ss" => yyyy-mm-dd'
				$DI = explode('-',$DI); // 'yyyy-mm-dd' => ['yyyy','mm','dd']
				$DI = "$DI[2]/$DI[1]/$DI[0]"; // ['yyyy','mm','dd'] => dd/mm/yyyy
				
				$HF = explode(' ',$DF)[1]; // "yyyy-mm-dd hh:ii:ss" => ['yyyy-mm-dd', 'hh:ii:ss']
				$HF = explode(':',$HF); // "hh:ii:ss" => ['hh','ii','ss']
				$HF = "$HF[0]:$HF[1]"; // ['hh','ii','ss'] => "hh:ii"
				
				$DF = explode(' ',$DF)[0]; // yyyy-mm-dd hh:ii:ss => ['yyyy-mm-dd', 'hh:ii:ss']
				$DF = explode('-',$DF); // yyyy-mm-dd => ['yyyy','mm','dd']
				$DF = "$DF[2]/$DF[1]/$DF[0]"; // ['yyyy','mm','dd'] => dd/mm/yyyy

				$recorrente = "NÃO";
				$repeticao = '';
			}
			$acoes = "<i onclick='deletBloqueio($bloqueio[AgBloId])' style='cursor:pointer' class='icon-bin' title='Excluir Bloqueio'></i>";

			array_push($array,[
				"$DI $HI",
				"$DF $HF",
				$bloqueio['AgBloDescricao'],
				$bloqueio['ProfiNome'],
				$recorrente,
				$repeticao,
				$acoes
			]);
		}

		echo json_encode($array);
	} elseif($tipoRequest == 'DELBLOQUEIO'){
		$id = $_POST['id'];
		$sql = "DELETE FROM AgendamentoBloqueio WHERE AgBloId = $id";
		$conn->query($sql);

		echo json_encode([
			'titulo' => 'Excluir Bloqueio',
			'status' => 'success',
			'menssagem' => 'Bloqueio excluido com sucesso!!!'
		]);
	} elseif($tipoRequest == 'GETRECORRENCIA'){
		$data = $_POST['data'];
		$horaI = $_POST['horaI'];
		$horaF = $_POST['horaF'];
		if(!intval($_POST['recorrente'])){
			$dateBase = date_create($data);
			echo json_encode([
				'status' => 'success',
				'datas' => [$dateBase->format('Y-m-d')]
			]);
			exit;
		}

		$repeticao = $_POST['repeticaoAgendamento'];
		$quantidade = $_POST['quantidadeRecorrenciaAgendamento'];
		$segunda = $_POST['segunda'];
		$terca = $_POST['terca'];
		$quarta = $_POST['quarta'];
		$quinta = $_POST['quinta'];
		$sexta = $_POST['sexta'];
		$sabado = $_POST['sabado'];
		$domingo = $_POST['domingo'];
		$quantidade = $quantidade<=0?1:$quantidade;
		$profissional = $_POST['profissional'];
		$dataFim = isset($_POST['dataFim'])?date_create($_POST['dataFim']):false;

		$datasSelect = [];
		{
			if($segunda){
				array_push($datasSelect,'Mon'); //Segunda
			}if($terca){
				array_push($datasSelect,'Tue'); //Terça
			}if($quarta){
				array_push($datasSelect,'Wed'); //Quarta
			}if($quinta){
				array_push($datasSelect,'Thu'); //Quinta
			}if($sexta){
				array_push($datasSelect,'Fri'); //Sexta
			}if($sabado){
				array_push($datasSelect,'Sat'); //Sábado
			}if($domingo){
				array_push($datasSelect,'Sun'); //Domingo
			}
		}

		if(!COUNT($datasSelect)){
			echo json_encode([
				'titulo' => 'Campo obrigatório',
				'status' => '',
				'menssagem' => "Informe pelo menos 1 dia da semana!!"
			]);
			exit;
		}

		// vai servir para verificar se a data é válida
		$sql = "SELECT AgFUnId,AgFUnSegunda,AgFUnTerca,AgFUnQuarta,AgFUnQuinta,AgFUnSexta,AgFUnSabado,AgFUnDomingo,AgFUnObservacao,
		AgFUnHorarioAbertura,AgFUnHorarioFechamento,AgFUnHorarioAlmocoInicio,AgFUnHorarioAlmocoFim,AgFUnIntervaloAgenda
		FROM AgendamentoFuncionamentoUnidade WHERE AgFUnUnidade = $iUnidade";
		$result = $conn->query($sql);
		$rowConfig = $result->fetch(PDO::FETCH_ASSOC);

		$arrayDays = [];
		{
			if(!$rowConfig['AgFUnSegunda']){
				array_push($arrayDays,'Mon'); //Segunda
			}if(!$rowConfig['AgFUnTerca']){
				array_push($arrayDays,'Tue'); //Terça
			}if(!$rowConfig['AgFUnQuarta']){
				array_push($arrayDays,'Wed'); //Quarta
			}if(!$rowConfig['AgFUnQuinta']){
				array_push($arrayDays,'Thu'); //Quinta
			}if(!$rowConfig['AgFUnSexta']){
				array_push($arrayDays,'Fri'); //Sexta
			}if(!$rowConfig['AgFUnSabado']){
				array_push($arrayDays,'Sat'); //Sábado
			}if(!$rowConfig['AgFUnDomingo']){
				array_push($arrayDays,'Sun'); //Domingo
			}
		}

		// loop para verificar disponibilidade das datas de acordo funcionamento da unidade
		$dateBase = date_create($data);
		$datasRecorrentes = [];
		$countWeek = 0;

		if($dataFim){
			while($dateBase->format('Y-m-d') <= $dataFim->format('Y-m-d')){
				$dateLoop = $dateBase;
				$dayCount = false;
				$loopWhile = true;
	
				if($dateLoop->format('Y-m-d') <= $dataFim){
					while($loopWhile){
						// se o dia em questão estiver sido marcado como dia para agendar...
						if(in_array($dateLoop->format('D'), $datasSelect)){
							$dateLoop = $dateLoop->format('Y-m-d');
		
							$sql = "SELECT AgendId FROM Agendamento WHERE AgendData = '$dateLoop' and
								AgendHoraInicio <= '$horaI' and AgendHoraFim >= '$horaF' and
								AgendProfissional = $profissional";
							$result = $conn->query($sql);
							$result = $result->fetchAll(PDO::FETCH_ASSOC);
							
							if(COUNT($result)){
								$dateLoop = date_create($dateLoop);
								$dateLoop = $dateLoop->format('d/m/Y');
								echo json_encode([
									'titulo' => 'erro ao incluir agendamento',
									'status' => 'error',
									'menssagem' => "A data e horário ($dateLoop das $horaI às $horaF) já está reservada!!"
								]);
								exit;
							}
							array_push($datasRecorrentes, $dateLoop);
							$dayCount = true;
							$dateLoop = date_create($dateLoop);
						}
						$loopWhile = $dateLoop->format('D') == 'Sat'?false:true;
						if($loopWhile){
							$dateLoop = $dateLoop->modify("+1 day");
						}
					}
				}
				$countWeek = $dayCount? $countWeek+1:$countWeek;
				if($repeticao[1] == 'S'){
					$d = intval($repeticao[0]);
					for($y=0; $y < $d; $y++){
						$loopWhile = true;
						while($loopWhile){
							$dateBase = $dateBase->modify("+1 day");
							$loopWhile = $dateBase->format('D') == 'Sun'?false:true;
						}
					}
				}
	
				// evitar dias em que a unidade não funciona
				while(in_array($dateBase->format('D'), $arrayDays)){
					$dateBase = $dateBase->modify("+1 day");
				}
			}
		}else{
			for($x=0; $x < intval($quantidade); $x++){
				$dayCount = false;
				$dateLoop = $dateBase;
				$loopWhile = true;
	
				while($loopWhile){
					// se o dia em questão estiver sido marcado como dia para agendar...
					if(in_array($dateLoop->format('D'), $datasSelect) && !in_array($dateLoop->format('D'), $arrayDays)){
						$dateLoop = $dateLoop->format('Y-m-d');
	
						$sql = "SELECT AgendId FROM Agendamento WHERE AgendData = '$dateLoop' and
							AgendHoraInicio <= '$horaI' and AgendHoraFim >= '$horaF' and
							AgendProfissional = $profissional";
						$result = $conn->query($sql);
						$result = $result->fetchAll(PDO::FETCH_ASSOC);
						
						if(COUNT($result)){
							$dateLoop = date_create($dateLoop);
							$dateLoop = $dateLoop->format('d/m/Y');
							echo json_encode([
								'titulo' => 'erro ao incluir agendamento',
								'status' => 'error',
								'menssagem' => "A data e horário ($dateLoop das $horaI às $horaF) já está reservada!!"
							]);
							exit;
						}
						array_push($datasRecorrentes, $dateLoop);
						$dayCount = true;
						$dateLoop = date_create($dateLoop);
					}
					$loopWhile = $dateLoop->format('D') == 'Sat'?false:true;
					if($loopWhile){
						$dateLoop = $dateLoop->modify("+1 day");
					}
				}
	
				$x = $dayCount?$x:$x-1; //caso não encontre um dia para marcar ele decrementa o loop
				if($repeticao[1] == 'S'){
					$d = intval($repeticao[0]);
					for($y=0; $y < $d; $y++){
						$loopWhile = true;
						while($loopWhile){
							$dateBase = $dateBase->modify("+1 day");
							$loopWhile = $dateBase->format('D') == 'Sun'?false:true;
						}
					}
				}
	
				// evitar dias em que a unidade não funciona
				while(in_array($dateBase->format('D'), $arrayDays)){
					$dateBase = $dateBase->modify("+1 day");
				}
			}
		}

		echo json_encode([
			'status' => 'success',
			'datas' => $datasRecorrentes,
			'counter' =>$countWeek
		]);
	}
}catch(PDOException $e) {
	$msg = '';
	switch($tipoRequest){
		case '': $msg = 'Informe o tipo de requisição';break;
		case 'AGENDAMENTOS': $msg = 'Erro ao carregar agendamentos';break;
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
