<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

$tipoRequest = $_POST['tipoRequest'];

try {

    $iUnidade = $_SESSION['UnidadeId'];
	$usuarioId = $_SESSION['UsuarId'];

    if ($tipoRequest == 'ADICIONARPROCEDIMENTO') {

        $sql= "SELECT MAX(AtTriId + 1) FROM AtendimentoTriagem
        WHERE AtTriUnidade = $iUnidade";

        $resultID = $conn->query($sql);
        $resultID = $resultID->fetch(PDO::FETCH_ASSOC);

        $procedimento = $_POST['procedimento'];
        $triagemId = $_POST['triagemId'];

        if ($triagemId == '') {
            $triagemId = $resultID;
        };

        $procTriagem = [
            'triagemId' => $triagemId,
            'servicoVenda' => $procedimento,
            'unidade' => $iUnidade
        ];

        $sql = "INSERT INTO AtendimentoTriagemXServicoVenda(AtTXSTriagem, AtTXSServicoVenda, AtTXSUnidade)
        VALUES ('$procTriagem[triagemId]','$procTriagem[servicoVenda]','$procTriagem[unidade]')";

        $conn->query($sql);

        echo json_encode([
			'status' => 'success',
			'titulo' => 'Procedimento',
			'menssagem' => 'Procedimento adicionado!!!',
		]);      
      
    }elseif ($tipoRequest == 'CHECKSERVICO') {
        
        $triagemId = $_POST['triagemId']; 

        $sql = "SELECT AtTXSTriagem, AtTXSServicoVenda, SrVenCodigo, SrVenNome
				FROM AtendimentoTriagemXServicoVenda
				JOIN ServicoVenda ON AtTXSServicoVenda = SrVenId
				WHERE AtTXSTriagem = $triagemId and AtTXSUnidade = $iUnidade";
        $result = $conn->query($sql);
        $rowProcedimentos = $result->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
			'array' => $rowProcedimentos
		]);
        
    }elseif ($tipoRequest == 'EXCLUIPROCEDIMENTO') {

        $oldId = $_POST['idTriagem']; 

        $idServico = $_POST['idServico']; 

        $sql = "DELETE FROM AtendimentoTriagemXServicoVenda
	            WHERE AtTXSTriagem = $oldId 
                AND AtTXSServicoVenda = $idServico
                AND AtTXSUnidade = $iUnidade";
                
        $result = $conn->query($sql);

        echo json_encode([
            'status' => 'success',
            'titulo' => 'Excluir Procedimento',
            'menssagem' => 'Procedimento Excluído!!!'
        ]);
        
    }elseif ($tipoRequest == 'ADDPROCEDIMENTOSPADRAO') {

        $triagemId = $_POST['triagemId'];

        $sql = "SELECT SrVenId
                FROM ServicoVenda
                WHERE (SrVenNome = 'Sinais Vitais'
                OR SrVenNome =  'Antropometria'
                OR SrVenNome = 'Glicemia')
                AND SrVenUnidade = $iUnidade";

        $result = $conn->query($sql);
        $rowProcedimentos = $result->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rowProcedimentos as $item) {

            $iten = $item['SrVenId']; 

            $sql = "SELECT AtTXSServicoVenda FROM AtendimentoTriagemXServicoVenda WHERE AtTXSServicoVenda = $iten";
            $result2 = $conn->query($sql);
            $rowId = $result2->fetchAll(PDO::FETCH_ASSOC);

            if (!COUNT($rowId)) {

                $sql = "INSERT INTO AtendimentoTriagemXServicoVenda(AtTXSTriagem, AtTXSServicoVenda, AtTXSUnidade)
                    VALUES ($triagemId, $iten, $iUnidade)";
                $conn->query($sql);

            }
            
        }     

    }



} catch (\Throwable $e) {

    $msg = '';

	switch($tipoRequest){
		case 'ADICIONARPROCEDIMENTO': $msg = 'Erro ao adicionar procedimento!!';break;
		case 'CHECKSERVICO': $msg = 'Erro ao buscar procedimentos!!';break;
		case 'EXCLUIPROCEDIMENTO': $msg = 'Erro ao excluir procedimento!!';break;
		case 'ADDPROCEDIMENTOSPADRAO': $msg = 'Erro ao adicionar procedimentos padrao!!';break;
		default: $msg = 'Erro ao executar ação!!';break;
	}
	echo json_encode([
		'titulo' => 'Triagem',
		'status' => 'error',
		'menssagem' => $msg,
		'error' => $e->getMessage(),
		'sql' => $sql
	]);
}

