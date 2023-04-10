<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

$tipoRequest = isset($_POST['tipoRequest'])?$_POST['tipoRequest']:'';

try{
	$iEmpresa = $_SESSION['EmpreId'];
	$iUnidade = $_SESSION['UnidadeId'];
	$usuarioId = $_SESSION['UsuarId'];

	if($tipoRequest == 'PROCESSOS'){
		$sqlUser = "SELECT ProceId,ProceNumero,ProceDataAutuacao,ProceEspecie,ProceUrgente,
			 S1.SituaNome as stat,S2.SituaNome as situacao,S2.SituaCor as cor,TpProNome,CategNome
			FROM Processo
			JOIN Categoria ON CategId = ProceCategoria
			JOIN TipoProcesso ON TpProId = ProceTipoProcesso
			JOIN Situacao S1 ON S1.SituaId = ProceStatus
			JOIN Situacao S2 ON S2.SituaId = ProceSituacao
			WHERE ProceEmpresa = $iEmpresa AND ProceUnidadeGestora = $iUnidade";
		$processos = $conn->query($sqlUser);
		$processos = $processos->fetchAll(PDO::FETCH_ASSOC);

		$array = [];
		foreach($processos as $prcs){
			$att = "<a style='color: black; cursor:pointer' onclick='editarProcesso($prcs[ProceId])' class='list-icons-item'><i class='icon-pencil7' title='Editar Processo'></i></a>";
			$exc = "<a style='color: black; cursor:pointer' onclick='excluiProcesso($prcs[ProceId])' class='list-icons-item mr-2'><i class='icon-bin' title='Excluir Processo'></i></a>";
			
			$acoes = "
				<div class='list-icons'>
					$att
					$exc
					<div class='dropdown'>													
						<a href='#' class='list-icons-item' data-toggle='dropdown'>
							<i class='icon-menu9'></i>
						</a>

						<div class='dropdown-menu dropdown-menu-right'>
							<a href='#' onclick='' class='dropdown-item'><i class='icon-stackoverflow' title='Teste de btn'></i> BTN1</a>									
							<div class='dropdown-divider'></div>
							<a href='#' onclick='' class='dropdown-item'><i class='icon-stackoverflow' title='Teste de btn'></i> BTN2</a>									
							<a href='#' onclick='' class='dropdown-item'><i class='icon-stackoverflow' title='Teste de btn'></i> BTN3</a>									
						</div>
					</div>
				</div>";

			array_push($array,[
				'data'=>[
					$prcs['ProceNumero'],
					date_format(date_create($prcs['ProceDataAutuacao']),'d/m/Y'),
					$prcs['ProceEspecie']=='F'?'Físico':'Eletrônico',
					$prcs['TpProNome'],
					$prcs['CategNome'],
					$prcs['stat'],
					$prcs['situacao'],
					$acoes
				],
				'identify'=>[
					'cor'=>$prcs['cor']
				]
			]);
		}
		
		echo json_encode($array);
	}elseif($tipoRequest == 'GETCMB'){
		$sql = "SELECT SituaId,SituaNome
			FROM Situacao
			WHERE SituaChave in ('EMANDAMENTO','IMPUGNADA','FRACASSADA','SUSPENSA')";
		$status = $conn->query($sql);
		$status = $status->fetchAll(PDO::FETCH_ASSOC);

		$sql = "SELECT SituaId,SituaNome
			FROM Situacao";
		$situacao = $conn->query($sql);
		$situacao = $situacao->fetchAll(PDO::FETCH_ASSOC);

		$sql = "SELECT TpProId,TpProNome
			FROM TipoProcesso";
		$tipo = $conn->query($sql);
		$tipo = $tipo->fetchAll(PDO::FETCH_ASSOC);

		$sql = "SELECT CategId,CategCodigo,CategNome
			FROM Categoria
			WHERE (CategNome like '%Material Médico Hospitalar%' OR CategNome like '%Material de Papelaria%')
			AND CategEmpresa = $iEmpresa";
		$categoria = $conn->query($sql);
		$categoria = $categoria->fetchAll(PDO::FETCH_ASSOC);

		$sql = "SELECT UnidaId,UnidaNome
			FROM Unidade
			WHERE UnidaEmpresa = $iEmpresa AND UnidaId != $iUnidade";
		$participantes = $conn->query($sql);
		$participantes = $participantes->fetchAll(PDO::FETCH_ASSOC);

		$array = [
			'status'=>[],
			'situacao'=>[],
			'tipo'=>[],
			'categoria'=>[],
			'participantes'=>[],
		];

		foreach($status as $item){
			array_push($array['status'],[
				'id'=>$item['SituaId'],
				'nome'=>$item['SituaNome'],
			]);
		}
		foreach($situacao as $item){
			array_push($array['situacao'],[
				'id'=>$item['SituaId'],
				'nome'=>$item['SituaNome'],
			]);
		}
		foreach($tipo as $item){
			array_push($array['tipo'],[
				'id'=>$item['TpProId'],
				'nome'=>$item['TpProNome'],
			]);
		}
		foreach($categoria as $item){
			array_push($array['categoria'],[
				'id'=>$item['CategId'],
				'cod'=>$item['CategCodigo'],
				'nome'=>$item['CategNome'],
			]);
		}
		foreach($participantes as $item){
			array_push($array['participantes'],[
				'id'=>$item['UnidaId'],
				'nome'=>$item['UnidaNome'],
			]);
		}

		echo json_encode($array);
	}elseif($tipoRequest == 'SALVARPROCESSO'){
		$numero = isset($_POST['numero'])?$_POST['numero']:'';
		$data = isset($_POST['data'])?$_POST['data']:'';
		$especie = isset($_POST['especie'])?$_POST['especie']:'';
		$urgente = isset($_POST['urgente'])?($_POST['urgente']=='S'?1:0):'';
		$tipo = isset($_POST['tipo'])?$_POST['tipo']:'';
		$categoria = isset($_POST['categoria'])?$_POST['categoria']:'';
		$status = isset($_POST['status'])?$_POST['status']:'';
		$situacao = isset($_POST['situacao'])?$_POST['situacao']:'';
		$descricao = isset($_POST['descricao'])?$_POST['descricao']:'';
		$participantes = isset($_POST['participantes'])?$_POST['participantes']:'';
		$responsavel = isset($_POST['responsavel'])?$_POST['responsavel']:'';
		$telefone = isset($_POST['telefone'])?$_POST['telefone']:'';
		$email = isset($_POST['email'])?$_POST['email']:'';

		$sql = "INSERT INTO Processo(ProceNumero,ProceDataAutuacao,ProceEspecie,ProceUrgente,ProceTipoProcesso,
		ProceCategoria,ProceStatus,ProceSituacao,ProceDescricao,ProceUnidadeGestora,ProceResponsavelNome,
		ProceResponsavelTelefone,ProceResponsavelEmail,ProceUsuarioAtualizador,ProceEmpresa)
		VALUES('$numero','$data','$especie','$urgente','$tipo',$categoria,$status,$situacao,'$descricao',
		$iUnidade,'$responsavel','$telefone','$email',$usuarioId,$iEmpresa)";
		$conn->query($sql);

		echo json_encode([
			'titulo' => 'Processo Licitatório',
			'status' => 'success',
			'menssagem' => 'Processo inserido com sucesso!!!',
		]);
	}elseif($tipoRequest == 'ATTPROCESSO'){
		$iProcesso = $_POST['iProcesso'];
		$numero = isset($_POST['numero'])?$_POST['numero']:'';
		$data = isset($_POST['data'])?$_POST['data']:'';
		$especie = isset($_POST['especie'])?$_POST['especie']:'';
		$urgente = isset($_POST['urgente'])?($_POST['urgente']=='S'?1:0):'';
		$tipo = isset($_POST['tipo'])?$_POST['tipo']:'';
		$categoria = isset($_POST['categoria'])?$_POST['categoria']:'';
		$status = isset($_POST['status'])?$_POST['status']:'';
		$situacao = isset($_POST['situacao'])?$_POST['situacao']:'';
		$descricao = isset($_POST['descricao'])?$_POST['descricao']:'';
		$participantes = isset($_POST['participantes'])?$_POST['participantes']:'';
		$responsavel = isset($_POST['responsavel'])?$_POST['responsavel']:'';
		$telefone = isset($_POST['telefone'])?$_POST['telefone']:'';
		$email = isset($_POST['email'])?$_POST['email']:'';

		$sql = "UPDATE Processo SET
			ProceNumero='$numero',
			ProceDataAutuacao='$data',
			ProceEspecie='$especie',
			ProceUrgente=$urgente,
			ProceTipoProcesso=$tipo,
			ProceCategoria=$categoria,
			ProceStatus=$status,
			ProceSituacao=$situacao,
			ProceDescricao='$descricao',
			ProceResponsavelNome='$responsavel',
			ProceResponsavelTelefone='$telefone',
			ProceResponsavelEmail='$email',
			ProceUsuarioAtualizador=$usuarioId
			WHERE ProceId = $iProcesso";
		$conn->query($sql);

		echo json_encode([
			'titulo' => 'Processo Licitatório',
			'status' => 'success',
			'menssagem' => 'Processo atualizado com sucesso!!!',
		]);
	}elseif($tipoRequest == 'DELPROCESSO'){
		$iProcesso = $_POST['id'];
		$sql = "DELETE FROM Processo WHERE ProceId = $iProcesso";
		$conn->query($sql);

		echo json_encode([
			'titulo' => 'Processo Licitatório',
			'status' => 'success',
			'menssagem' => 'Processo excluído com sucesso!!!',
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
		'titulo' => 'Processo Licitatório',
		'status' => 'error',
		'menssagem' => $msg,
		'error' => $e->getMessage()
	]);
}