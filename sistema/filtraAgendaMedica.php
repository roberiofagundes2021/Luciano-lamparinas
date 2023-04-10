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
	$tipoRequest = $_POST['tipoRequest'];

	if($tipoRequest == 'PROFISSIONAIS'){
		$sql = "SELECT ProfiId,ProfiNome
				FROM Profissional
				JOIN Situacao on SituaId = ProfiStatus
				WHERE ProfiUnidade = $iUnidade and SituaChave = 'ATIVO' ";
		$result = $conn->query($sql);
		$row = $result->fetchAll(PDO::FETCH_ASSOC);
		
		$array = [];
		foreach($row as $item){
			array_push($array, [
				'id' => $item['ProfiId'],
				'nome' => $item['ProfiNome']
			]);
		}
	
		echo json_encode([
			'data' => $array,
			'status' => 'success',
			'titulo' => 'Profissionais',
			'menssagem' => 'Profissionais encontrados!!!',
		]);
	}else if($tipoRequest == 'AGENDA'){
		$iProfissional = $_POST['iProfissional'];

		// PrAgeId,PrAgeProfissional,PrAgeData,PrAgeHoraInicio,PrAgeHoraFim,PrAgeIntervalo,
		// PrAgeAtendimentoLocal,PrAgeUsuarioAtualizador,PrAgeUnidade

		$sql = "SELECT PrAgeId,PrAgeData
		FROM ProfissionalAgenda WHERE PrAgeProfissional = $iProfissional";
		$result = $conn->query($sql);
		$row = $result->fetchAll(PDO::FETCH_ASSOC);
		
		$array = [];
		foreach($row as $item){
			$data = explode('-',$item['PrAgeData']);

			array_push($array, [
				'id' => $item['PrAgeId'],
				'data' => $data
			]);
		}
	
		echo json_encode([
			'data' => $array,
			'status' => 'success',
			'titulo' => 'Agenda',
			'menssagem' => 'Agenda encontrada!!!',
		]);
	}else if($tipoRequest == 'AGENDAPROFISSIONAL'){
		$iProfissional = $_POST['iProfissional'];
		$data = $_POST['data'];

		// formatando data dd/mm/yyyy => yyyy-mm-dd
		$data = explode('/',$data);
		$data = $data[2].'-'.$data[1].'-'.$data[0];

		// PrAgeId,PrAgeProfissional,PrAgeData,PrAgeHoraInicio,PrAgeHoraFim,PrAgeIntervalo,
		// PrAgeAtendimentoLocal,PrAgeUsuarioAtualizador,PrAgeUnidade

		$sql = "SELECT PrAgeHoraInicio,PrAgeHoraFim
		FROM ProfissionalAgenda
		WHERE PrAgeProfissional = $iProfissional and PrAgeData = '$data'";
		$result = $conn->query($sql);
		$rowHorarios = $result->fetchAll(PDO::FETCH_ASSOC);

		// montar um array com os horários dentro do intervalo: inicio e fim
		$arrayHora = [];
		foreach($rowHorarios as $itemAgenda){
			$hI = intval(explode('.',$itemAgenda['PrAgeHoraInicio'])[0]);
			$hF = intval(explode('.',$itemAgenda['PrAgeHoraFim'])[0]);
			$loop = true;

			while($loop){
				if($hI < $hF){
					array_push($arrayHora,$hI);
					$hI++;
				} else {
					array_push($arrayHora,$hF);
					$loop = false;
				}
			}
		}
		$arrayAgenda = [];

		foreach($arrayHora as $horario){
			$sql = "SELECT AtXSeId,AtXSeData,AtXSeHorario,AtLocNome,ClienNome,SrVenNome,SituaNome
					FROM AtendimentoXServico
					LEFT JOIN Atendimento ON AtendId = AtXSeAtendimento
					LEFT JOIN Cliente ON ClienId = AtendCliente
					LEFT JOIN AtendimentoLocal ON AtLocId = AtXSeAtendimentoLocal
					LEFT JOIN ServicoVenda ON SrVenId = AtXSeServico
					JOIN Situacao on SituaId = AtendSituacao
					WHERE AtXSeProfissional = $iProfissional and AtXSeData = '$data' and AtXSeUnidade = $iUnidade
					and AtXSeHorario like '%$horario%'
					ORDER BY AtXSeHorario";
			$result = $conn->query($sql);
			$row = $result->fetchAll(PDO::FETCH_ASSOC);

			if(COUNT($row)){
				foreach($row as $item){
					array_push($arrayAgenda, [
						$item['AtXSeData'],
						$item['AtXSeHorario'],
						$item['AtLocNome']?$item['AtLocNome']:'',
						$item['ClienNome']?$item['ClienNome']:'',
						$item['SrVenNome']?$item['SrVenNome']:'',
						$item['SituaNome']?$item['SituaNome']:'',
						'',
					]);
				}
			} else {
				$horario = str_pad($horario,2,0,STR_PAD_LEFT);
				$horario = str_pad($horario,5,":00",STR_PAD_RIGHT);
				array_push($arrayAgenda, [
					$data,
					$horario,
					'',
					'',
					'',
					'',
					'',
				]);
			}
		}
	
		echo json_encode([
			'data' => $arrayAgenda,
			'status' => 'success',
			'titulo' => 'Agenda',
			'menssagem' => 'Agenda encontrada!!!',
		]);
	}else if($tipoRequest == 'DADOSPROFISSIONAL'){

		$iProfissional = $_POST['iProfissional'];

		if($iProfissional){
			$sql = "SELECT ProfiId, ProfiNome, ProfiNumConselho, ProfiCelular, PrConNome, EspecNome
					FROM Profissional
					LEFT JOIN ProfissionalConselho ON PrConId = ProfiConselho
					LEFT JOIN Especialidade ON EspecId = ProfiEspecialidade
					WHERE ProfiId = ".$iProfissional ;
			$result = $conn->query($sql);
			$row = $result->fetch(PDO::FETCH_ASSOC);
			$count = count($row);
			
		

			if($count){

				print('
					<div class="form-group" style="border: 1px solid #ccc; background-color:#F1F1F1; margin-right: 10px ">
						<div class="row" style="margin-top: 10px;">
							<div class="col-lg-2">	
								<p style="margin-right:10px; margin-left: 10px"><b> Dr. '.$row['ProfiNome'].'</b> </p>
							</div>
							<div class="col-lg-2">	
								<p style="margin-right:10px; margin-left: 10px"><b>'.$row['PrConNome'].': '.$row['ProfiNumConselho'].'</b> </p>
							</div>
							<div class="col-lg-2">		
								<p style="margin-right:10px; margin-left: 10px"><b>Celular: '.$row['ProfiCelular'].'</b> </p>
							</div>
						</div>
						<div class="row">
							<div class="col-lg-12">	
								<p style="margin-right:10px; margin-left: 10px">Especialidades: '.$row['EspecNome'].' </p>
							</div>
						</div>
					</div>
				');
					
			} else{
				echo 0;
			}
		}	
	}
}catch(PDOException $e) {
	$msg = '';
	switch($tipoRequest){
		case 'AGENDAMENTOS': $msg = 'Erro ao carregar agendamentos';break;
		default: $msg = "Erro ao executar ação COD.: $tipoRequest";break;
	}

	echo json_encode([
		'titulo' => 'Agenda médica',
		'tipo' => 'error',
		'menssagem' => $msg,
		'sql' => $sql,
		'error' => $e->getMessage()
	]);
}
