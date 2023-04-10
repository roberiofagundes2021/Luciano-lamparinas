<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

$tipoRequest = $_POST['tipoRequest'];

try {

    $iUnidade = $_SESSION['UnidadeId'];
	$usuarioId = $_SESSION['UsuarId'];

    if ($tipoRequest == 'ADICIONARPROCEDIMENTO') {

        $grupo = $_POST['grupo'];
        $subgrupo = $_POST['subgrupo'];
        $procedimento = $_POST['procedimento'];
        $cid10 = $_POST['cid10'];
        $justificativa = $_POST['justificativa'];
        $urgente = $_POST['urgente'];
        $profissional = $_POST['profissional'];
        $iAtendimentoId = $_POST['iAtendimentoId'];

        $DataAtendimentoInicio = date("Y-m-d");
        $HoraInicio = date("H:i:s");        

        $sql = "INSERT INTO AtendimentoSolicitacaoProcedimento
        (AtSPrGrupo, AtSPrSubGrupo, AtSPrProcedimento, AtSPrCid10, AtSPrJustificativa, AtSPrUrgente, AtSPrProfissional, AtSPrAtendimento, AtSPrUnidade, AtSPrDataInicio, AtSPrHoraInicio)
        VALUES ('$grupo', '$subgrupo', '$procedimento', $cid10,  '$justificativa', '$urgente', '$profissional', '$iAtendimentoId', '$iUnidade', '$DataAtendimentoInicio', '$HoraInicio')";

        $conn->query($sql);

        echo json_encode([
			'status' => 'success',
			'titulo' => 'Procedimento',
			'menssagem' => 'Procedimento adicionado!!!',
		]);      


    } elseif ($tipoRequest == 'CHECKPROCEDIMENTOS') {
       
        $iAtendimentoId = $_POST['iAtendimentoId'];

        $sql = "SELECT *
                FROM AtendimentoSolicitacaoProcedimento
                JOIN AtendimentoGrupo ON AtSPrGrupo = AtGruId
                JOIN AtendimentoSubGrupo ON AtSPrSubGrupo = AtSubId
                JOIN ServicoVenda ON AtSPrProcedimento = SrVenId    
				JOIN Cid10 ON AtSPrCid10 = Cid10Id
                WHERE AtSPrAtendimento = $iAtendimentoId and AtSPrUnidade = $iUnidade
                ORDER BY AtSPrDataInicio DESC, AtSPrHoraInicio DESC ";
        $result = $conn->query($sql);
        $rowProcedimentos = $result->fetchAll(PDO::FETCH_ASSOC);

        $array = [];
        $dataProcedimentos = [];
        $i = 1;
        foreach ($rowProcedimentos as $item) {
            $print = "<a style='color: blue;' href='#' onclick='imprimirSolProcedimento($item[AtSPrId])' class='list-icons-item mr-2'><i class='icon-printer2' title='Imprimir Solicitação'></i></a>";
            $copiar = "<a style='color: black'  onclick='copiarProcedimento(" . json_encode($item) . ")' class='list-icons-item mr-2'><i class='icon-files-empty' title='Copiar Sol. Procedimento'></i></a>";
            $exc = "<a style='color: black' onclick='excluirProcedimento($item[AtSPrId])' class='list-icons-item'><i class='icon-bin' title='Excluir Procedimento'></i></a>";
           
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
            
            array_push($dataProcedimentos, [
                'data' => [
                    $i,
                    mostraData($item['AtSPrDataInicio']) . " " . mostraHora($item['AtSPrHoraInicio']),
                    $item['AtGruNome'],
                    $item['AtSubNome'],
                    $item['SrVenCodigo'],
                    $item['SrVenNome'],
					$item['Cid10Codigo'],
                    $acoes
                ],
                'identify' => [
                    'descricaoCid10' => $item['Cid10Descricao']
                ]
            ],
        );
            $i++;
        }

        $array = [
            'dataProcedimentos' => $dataProcedimentos
        ];

        echo json_encode($array);
        
    } elseif ($tipoRequest == 'EXCLUIRPROCEDIMENTO') {

        $idProcedimento = $_POST['id'];

        $sql = "DELETE FROM AtendimentoSolicitacaoProcedimento
                WHERE AtSPrId = $idProcedimento
        		AND AtSPrUnidade = $iUnidade";		
                        
        $result = $conn->query($sql);

        echo json_encode([
            'status' => 'success',
            'titulo' => 'Excluir Procedimento',
            'menssagem' => 'Procedimento Excluído!!!'
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
    
    } elseif ($tipoRequest == 'CID10') {
    
        $sql = "SELECT Cid10Id, Cid10Codigo, Cid10Descricao
            FROM Cid10
            WHERE Cid10Status = 1
            ORDER BY Cid10Codigo ASC";
        $result = $conn->query($sql);
        $rows = $result->fetchAll(PDO::FETCH_ASSOC);
    
        $array = [];
        foreach($rows as $item){
            array_push($array,[
                'id' => $item['Cid10Id'],
                'codigo' => $item['Cid10Codigo'],
				'descricao' => $item['Cid10Descricao']
            ]);
        }
    
        echo json_encode($array);
		
	}
	 elseif($tipoRequest == 'PROCEDIMENTOS'){

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

		case 'ADICIONARPROCEDIMENTO': $msg = 'Erro ao adicionar procedimento!!';break;
		case 'CHECKPROCEDIMENTOS': $msg = 'Erro ao checar procedimento!!';break;
		case 'EXCLUIRPROCEDIMENTO': $msg = 'Erro ao excluir procedimento!!';break;
		case 'GRUPOS': $msg = 'Erro ao buscar grupos!!';break;
		case 'SUBGRUPOS': $msg = 'Erro ao buscar subgrupos!!';break;
		case 'CID10': $msg = 'Erro ao buscar cid-10!!';break;
		case 'PROCEDIMENTOS': $msg = 'Erro ao buscar procedimentos!!';break;

		default: $msg = 'Erro ao executar ação!!';break;
	}
	echo json_encode([
		'titulo' => 'Solicitação de Procedimento',
		'status' => 'error',
		'menssagem' => $msg,
		'error' => $e->getMessage(),
		'sql' => $sql
	]);
}

