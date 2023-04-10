<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

$tipoRequest = $_POST['tipoRequest'];

try {

    $iUnidade = $_SESSION['UnidadeId'];
	$usuarioId = $_SESSION['UsuarId'];

    if ($tipoRequest == 'ADICIONAREXAME') {

        $grupo = $_POST['grupo'];
        $subgrupo = $_POST['subgrupo'];
        $exame = $_POST['exame'];
        $justificativa = $_POST['justificativa'];
        $urgente = $_POST['urgente'];
        $profissional = $_POST['profissional'];
        $iAtendimentoId = $_POST['iAtendimentoId'];

        $DataAtendimentoInicio = date("Y-m-d");
        $HoraInicio = date("H:i:s");        

        $sql = "INSERT INTO AtendimentoSolicitacaoExame
        (AtSExGrupo, AtSExSubGrupo, AtSExExame, AtSExJustificativa, AtSExUrgente, AtSExProfissional, AtSExAtendimento, AtSExUnidade, AtSExDataInicio, AtSExHoraInicio)
        VALUES ('$grupo', '$subgrupo', '$exame',  '$justificativa', '$urgente', '$profissional', '$iAtendimentoId', '$iUnidade', '$DataAtendimentoInicio', '$HoraInicio')";

        $conn->query($sql);

        echo json_encode([
			'status' => 'success',
			'titulo' => 'Exame',
			'menssagem' => 'Exame Adicionado!!!',
		]);      


    } elseif ($tipoRequest == 'CHECKEXAMES') {
       
        $iAtendimentoId = $_POST['iAtendimentoId'];

        $sql = "SELECT * 
                FROM AtendimentoSolicitacaoExame
                JOIN AtendimentoGrupo ON AtSExGrupo = AtGruId
                JOIN AtendimentoSubGrupo ON AtSExSubGrupo = AtSubId
                JOIN ServicoVenda ON AtSExExame = SrVenId    
                WHERE AtSExAtendimento = $iAtendimentoId and AtSExUnidade = $iUnidade
                ORDER BY AtSExDataInicio DESC, AtSExHoraInicio DESC";
        $result = $conn->query($sql);
        $rowExames = $result->fetchAll(PDO::FETCH_ASSOC);

        $array = [];
        $i = 1;
        foreach ($rowExames as $item) {
            $print = "<a style='color: blue;' href='#' onclick='imprimirSolExame($item[AtSExId])' class='list-icons-item mr-2'><i class='icon-printer2' title='Imprimir Solicitação'></i></a>";
            $copiar = "<a style='color: black'  onclick='copiarExame(" . json_encode($item) . ")' class='list-icons-item mr-2'><i class='icon-files-empty' title='Copiar Sol. Exame'></i></a>";
            $exc = "<a style='color: black'  onclick='excluirExame($item[AtSExId])' class='list-icons-item'><i class='icon-bin' title='Excluir Exame'></i></a>";

                    if (isset($_SESSION['SituaChave']) && $_SESSION['SituaChave'] != "ATENDIDO") {
                        $acoes = "<div class='list-icons'>
                                    ${print}
                                    ${copiar}
                                    ${exc}
                                </div>";
                    } else{
                        $acoes = "<div class='list-icons'>
                                    ${print}
                                    ${copiar}
                                </div>";
                    }
            
            array_push($array, [
                'data' => [
                    $i,
                    mostraData($item['AtSExDataInicio']) . " " . mostraHora($item['AtSExHoraInicio']),
                    $item['AtGruNome'],
                    $item['AtSubNome'],
                    $item['SrVenCodigo'],
                    $item['SrVenNome'],
                    $acoes
                ]
            ]);
            $i++;
        }

        echo json_encode($array);

        
    } elseif ($tipoRequest == 'EXCLUIREXAME') {

        $idExame = $_POST['id'];

        $sql = "DELETE FROM AtendimentoSolicitacaoExame
                WHERE AtSExId = $idExame
                AND AtSExUnidade = $iUnidade";
                        
        $conn->query($sql);

        echo json_encode([
            'status' => 'success',
            'titulo' => 'Excluir Exame',
            'menssagem' => 'Exame Excluído!!!'
        ]);
        
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
    
    } elseif($tipoRequest == 'PROCEDIMENTOS'){
        
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
    }

} catch (\Throwable $e) {

    $msg = '';

	switch($tipoRequest){
		case 'ADICIONAREXAME': $msg = 'Erro ao adicionar exame!!';break;
        case 'CHECKEXAMES': $msg = 'Erro ao checar exame!!';break;
		case 'EXCLUIREXAME': $msg = 'Erro ao excluir exame!!';break;
		case 'GRUPOS': $msg = 'Erro ao buscar grupos!!';break;
		case 'SUBGRUPOS': $msg = 'Erro ao buscar subgrupos!!';break;
		case 'PROCEDIMENTOS': $msg = 'Erro ao buscar exames!!';break;
		default: $msg = 'Erro ao executar ação!!';break;
	}
	echo json_encode([
		'titulo' => 'Solicitação de Exame',
		'status' => 'error',
		'menssagem' => $msg,
		'error' => $e->getMessage(),
		'sql' => $sql
	]);
}

