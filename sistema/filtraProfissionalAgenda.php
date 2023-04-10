<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Agenda';

include('global_assets/php/conexao.php');

$tipoRequest = $_POST['tipoRequest'];

if(!isset($_SESSION['agendaProfissional'])){
	$_SESSION['agendaProfissional'] = [];
}

try{
	$iUnidade = $_SESSION['UnidadeId'];
	$usuarioId = $_SESSION['UsuarId'];

	if($tipoRequest == 'AGENDA'){
		$iProfissional = $_POST['iProfissional'];

		$sql = "SELECT PrAgeId,PrAgeProfissional,PrAgeData,PrAgeHoraInicio,PrAgeHoraFim,PrAgeAtendimentoLocal,
			PrAgeUsuarioAtualizador,PrAgeUnidade,AtLocNome,AtLocCor,PrAgeIntervalo
			FROM ProfissionalAgenda
			JOIN AtendimentoLocal ON AtLocId = PrAgeAtendimentoLocal
			WHERE PrAgeProfissional = $iProfissional";
		$result = $conn->query($sql);
		$rowAgenda = $result->fetchAll(PDO::FETCH_ASSOC);

		$array = [];
		foreach($rowAgenda as $item){
			$horaInicio = explode('.',$item['PrAgeHoraInicio'])[0];
			$horaFim = explode('.',$item['PrAgeHoraFim'])[0];

			$dataI = date_create($item['PrAgeData']);
			$dataI = date_format($dataI,'Y/m/d');

			$inicio = "$dataI $horaInicio";
			$fim = "$dataI $horaFim";

			array_push($array,[
				'id'=> $item['PrAgeId'],
				'url'=> '',
				'intervalo'=> $item['PrAgeIntervalo'],
				'tipInsert'=> '',
				'localId' => $item['PrAgeAtendimentoLocal'],
				'title'=> $item['AtLocNome'],
				'start'=> $inicio,
				'end'=> $fim,
				'color'=> $item['AtLocCor']?$item['AtLocCor']:'#546E7A'
			]);
		}
		$_SESSION['agendaProfissional'] = $array;
	
		echo json_encode($array);
	} else if($tipoRequest == 'CHECKAGENDA'){
		$arrayAgenda = [];

		foreach($_SESSION['agendaProfissional'] as $item){
			if($item['tipInsert'] != 'REMOVE'){
				array_push($arrayAgenda, $item);
			}
		}
		echo json_encode($arrayAgenda);
	} else if($tipoRequest == 'LOCAIS'){
		$sql = "SELECT AtLocId,AtLocNome,AtLocCor
			FROM AtendimentoLocal
			WHERE AtLocUnidade = $iUnidade";
		$result = $conn->query($sql);
		$rowLocal = $result->fetchAll(PDO::FETCH_ASSOC);

		$array = [];
		foreach($rowLocal as $item){
			array_push($array,[
				'idLocal'=> $item['AtLocId'],
				'nome'=> $item['AtLocNome'],
				'cor'=> $item['AtLocCor']?$item['AtLocCor']:'#546E7A'
			]);
		}
		echo json_encode($array);
	} else if($tipoRequest == 'SETAGENDA'){
		$arrayAgenda = $_SESSION['agendaProfissional'];
		$id = $_POST['id'];
		$intervalo = $_POST['horaIntervalo'];
		$localId = isset($_POST['localId'])?$_POST['localId']:'N/A';
		$title = isset($_POST['title'])?$_POST['title']:'N/A';
		$cor = isset($_POST['cor'])?$_POST['cor']:'#546E7A';

		$horaI = '';
		$data = '';
		$horaF = '';
		if(isset($_POST['dataI']) && $_POST['dataI']){
			$horaI = $_POST['dataI']; // "dd/mm/yyyy hh:MM:ss"
			$arrayDataHora = explode(' ',$horaI); // ["dd/mm/yyyy", "hh:MM:ss"]
			$horaI = $arrayDataHora[1];

			$data = explode('/',$arrayDataHora[0]);
			$data = $data[2].'/'.$data[1].'/'.$data[0]; // "yyyy/mm/dd"
	
			if(isset($_POST['dataF']) && $_POST['dataF']){
				$horaF = $_POST['dataF'];
				$horaF = explode(' ',$horaF)[1];
			}
		}

		foreach($arrayAgenda as $key=>$item){
			if($item['id'] == $id){
				$type = $arrayAgenda[$key]['tipInsert'];

				$arrayAgenda[$key]['title'] = $title;
				$arrayAgenda[$key]['start'] = ($data.' '.$horaI);
				$arrayAgenda[$key]['end'] = ($data.' '.$horaF);
				$arrayAgenda[$key]['intervalo'] = $intervalo;
				$arrayAgenda[$key]['tipInsert'] = $type=='NEW'?$type:'ATT';
				$_SESSION['agendaProfissional'] = $arrayAgenda;
				echo json_encode($arrayAgenda);
				exit;
			}
		}

		array_push($arrayAgenda, [
			'id' => $id,
			'localId' => $localId,
			'start' => ($data.' '.$horaI),
			'end' => ($data.' '.$horaF),
			'intervalo' => $intervalo,
			'tipInsert'=> 'NEW',
			'title'=> $title,
			'color'=> $cor,
			'url'=> ''
		]);
		$_SESSION['agendaProfissional'] = $arrayAgenda;
		echo json_encode($arrayAgenda);
	} else if($tipoRequest == 'SETHORAAGENDA'){
		$arrayAgenda = $_SESSION['agendaProfissional'];

		$id = $_POST['id'];
		$horaAgendaInicio = $_POST['horaAgendaInicio'];
		$horaAgendaFim = $_POST['horaAgendaFim'];
		$intervalo = $_POST['horaIntervalo'];

		foreach($arrayAgenda as $key=>$item){
			if($item['id'] == $id){
				$type = $arrayAgenda[$key]['tipInsert'];
				$dataI = explode(' ',$item['start'])[0];
				$dataF = explode(' ',$item['start'])[0];
				$arrayAgenda[$key]['start'] = $dataI.' '.$horaAgendaInicio;
				$arrayAgenda[$key]['end'] = $dataF.' '.$horaAgendaFim;
				$arrayAgenda[$key]['intervalo'] = $intervalo;
				$arrayAgenda[$key]['tipInsert'] = $type=='NEW'?$type:'ATT';
			}
		}
		
		$_SESSION['agendaProfissional'] = $arrayAgenda;
		echo json_encode([
			'titulo' => 'Horário',
			'status' => 'success',
			'menssagem' => 'Horário definido!!',
			'arrayAgenda' => $arrayAgenda
		]);
	} else if($tipoRequest == 'SALVAAGENDA'){
		$arrayAgenda = $_SESSION['agendaProfissional'];
		$iProfissional = $_POST['iProfissional'];
		$msg = '';

		foreach($arrayAgenda as $item){
			// caso seja um item que será atualizado au inserido...
			// ou seja tipInsert != '' && tipInsert != 'REMOVE' (tipInsert == 'NEW' ou tipInsert == 'ATT')
			if($item['tipInsert'] && $item['tipInsert'] != 'REMOVE'){
				$start = explode(' ',$item['start']); //"2022/08/20 09:00:00" => [0]="2022/08/20" [1]="09:00:00"
				$end = explode(' ',$item['end']); //"2022/08/20 09:00:00" => [0]="2022/08/20" [1]="09:00:00"
				$intervalo = $item['intervalo']>0?$item['intervalo']:30;

				$data = explode('/',$start[0]); // [0]="2022" [1]="08" [2]="20"
				$data = $data[0].'-'.$data[1].'-'.$data[2]; // "2022-08-20"

				$sql = "SELECT PrAgeProfissional,PrAgeIntervalo,PrAgeData,PrAgeHoraInicio,
				PrAgeHoraFim,PrAgeAtendimentoLocal,PrAgeUsuarioAtualizador,PrAgeUnidade
				FROM ProfissionalAgenda
				WHERE PrAgeProfissional = $iProfissional AND PrAgeData = '$data'
				AND (('$start[1]' >= PrAgeHoraInicio AND '$start[1]' <= PrAgeHoraFim
				OR '$end[1]' >= PrAgeHoraInicio AND '$end[1]' <= PrAgeHoraFim)OR
				(PrAgeHoraInicio >= '$start[1]' AND PrAgeHoraFim <= '$start[1]'
				OR PrAgeHoraInicio >= '$end[1]' AND PrAgeHoraFim <= '$end[1]'))";
				$results = $conn->query($sql);
				$results = $results->fetchAll(PDO::FETCH_ASSOC);

				switch($item){
					case !isset($item['tipInsert']):$msg = 'Informe o início e fim do agendamento!!';break;
					case !$item['start']:$msg = 'Data de início não informada!!';break;
					case !explode(' ',$item['start'])[1]:$msg = 'Hora de início não informada!!';break;
					case !$item['end']:$msg = 'Data de fim não informada!!';break;
					case !explode(' ',$item['end'])[1]:$msg = 'Hora de fim não informada!!';break;
					case (!$item['id'] || $item['id'] == 'N/A'):$msg = 'Card de data sem alteração!!';break;
					default: $msg = '';break;
				}
				if(COUNT($results)){
					$msg = "Existe um registro que entra em conflito com a data selecionada: $data";
				}
				if($msg){
					echo json_encode([
						'status'=> 'ERRO',
						'titulo'=> 'Dados incompletos!!',
						'menssagem' => $msg
					]);
					exit;
				}
			}
		}

		$arraySql = [];
		$arraySqlUpdate = [];

		foreach($arrayAgenda as $item){
			$start = explode(' ',$item['start']); //"22-08-2022 09:00:00"
			$end = explode(' ',$item['end']); //"22-08-2022 09:00:00"
			$intervalo = $item['intervalo']>0?$item['intervalo']:30;

			$data = explode('/',$start[0]); // [0]="2022" [1]="08" [2]="20"
			$data = $data[0].'-'.$data[1].'-'.$data[2]; // "2022-08-20"

			if($item['tipInsert'] == 'NEW'){
				$sql = "INSERT INTO ProfissionalAgenda(PrAgeProfissional,PrAgeIntervalo,PrAgeData,PrAgeHoraInicio,
				PrAgeHoraFim,PrAgeAtendimentoLocal,PrAgeUsuarioAtualizador,PrAgeUnidade) VALUES
				($iProfissional,'$intervalo','$data', '$start[1]','$end[1]',$item[localId],$usuarioId,$iUnidade)";

				array_push($arraySql, $sql);
			} elseif($item['tipInsert'] == 'ATT'){
				$sql = "SELECT SituaId  FROM Situacao WHERE (SituaNome = 'Reagendar' OR SituaChave = 'REAGENDAR') AND SituaStatus = 1";
				$result = $conn->query($sql);
				$idSituaReag = $result->fetch(PDO::FETCH_ASSOC);			
				
				$sql = "SELECT PrAgeData, PrAgeAtendimentoLocal FROM ProfissionalAgenda WHERE PrAgeId = $item[id]";
				$result = $conn->query($sql);
				$oldDataELocal = $result->fetch(PDO::FETCH_ASSOC);
				
				$sql = "$idSituaReag[SituaId];$oldDataELocal[PrAgeData];$data;$oldDataELocal[PrAgeAtendimentoLocal]" . "<>" . "UPDATE ProfissionalAgenda SET
				PrAgeData='$data',
				PrAgeIntervalo='$intervalo',
				PrAgeHoraInicio='$start[1]',
				PrAgeHoraFim='$end[1]',
				PrAgeAtendimentoLocal='$item[localId]',
				PrAgeUsuarioAtualizador=$usuarioId
				WHERE PrAgeId = $item[id]";

				array_push($arraySqlUpdate, $sql);
			} elseif($item['tipInsert'] == 'REMOVE'){
				$sql = "DELETE FROM ProfissionalAgenda WHERE PrAgeId = $item[id]";
				array_push($arraySql, $sql);
			}
		}
		if(COUNT($arraySql)){
			foreach($arraySql as $sql){
				$conn->query($sql);
			}
		}
		if (COUNT($arraySqlUpdate)) {

			foreach ($arraySqlUpdate as $array ) {

				$reagendar = explode(';', explode('<>',$array)[0])[0];
				$oldData = explode(';', explode('<>',$array)[0])[1];
				$newData = str_replace('/', '-', explode(';', explode('<>',$array)[0])[2]);
				$local = explode(';', explode('<>',$array)[0])[3];

				if($newData != $oldData){
					$sql= "UPDATE Agendamento 
							SET AgendSituacao = '$reagendar'
							WHERE AgendData = '$oldData' 
							AND AgendProfissional = '$iProfissional'
							AND AgendAtendimento IS NULL";	
					$conn->query($sql);		
				}
				
				$conn->query(explode('<>',$array)[1]);
			}
		}
		echo json_encode([
			'status'=> 'success',
			'titulo'=> 'Agenda salva!!',
			'menssagem' => 'Agenda do profissional salva com sucesso!!',
			'sql' =>$arraySql
			// 'sql' =>$arrayAgenda
		]);
	} else if($tipoRequest == 'REMOVEAGENDA'){
		$arrayAgenda = $_SESSION['agendaProfissional'];
		$id = $_POST['id'];

		foreach($arrayAgenda as $key=>$item){
			if($item['id'] == $id){
				$arrayAgenda[$key]['tipInsert'] = 'REMOVE';
			}
		}

		$_SESSION['agendaProfissional'] = $arrayAgenda;
		
		echo json_encode([
			'status'=> 'success',
			'titulo'=> 'Item removido!!',
			'menssagem' => 'Item removido da agenda com sucesso!!',
			'array' =>$_SESSION['agendaProfissional']
		]);
	}
}catch(PDOException $e) {
	$msg = '';
	switch($tipoRequest){
		case 'SALVAAGENDA': $msg = 'Erro ao salvar agenda!!';break;
		case 'AGENDA': $msg = 'Erro ao carregar agenda!!';break;
		default: $msg = 'Erro ao executar ação!!';break;
	}
	echo json_encode([
		'titulo' => 'Agenda',
		'status' => 'error',
		'menssagem' => $msg,
		'error' => $e->getMessage(),
		'sql' => $sql
	]);
}
?>

