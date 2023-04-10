<?php

include_once("sessao.php");
$_SESSION['PaginaAtual'] = 'Salvar Serviço';
include('global_assets/php/conexao.php');

try {
    $iUnidade = $_SESSION['UnidadeId'];
    $usuarioId = $_SESSION['UsuarId'];
    $tmpSrvId = "X";

    $dataArray = array(
        ':sSrVenCodigo' => $_POST['SrVenCodigo'],
        ':sSrVenNome' => $_POST['SrVenNome'],
        ':iSrVenTipoServico' => $_POST['SrVenTipoServico'],
        ':sSrVenDetalhamento' => $_POST['SrVenDetalhamento'],
        ':iSrVenGrupo' => $_POST['SrVenGrupo'],
        ':iSrVenSubGrupo' => $_POST['SrVenSubGrupo'],
        ':iSrVenPlanoConta' => $_POST['SrVenPlanoConta'],
        ':iSrVenStatus' => 1,
        ':iSrVenUsuarioAtualizador' => $usuarioId,
        ':iSrVenUnidade' => $iUnidade
    );

    if (isset($_POST['SrVenId']) and $_POST['SrVenId']!="") {
        $sql = ("UPDATE
                ServicoVenda
            SET
                SrVenCodigo = :sSrVenCodigo,
                SrVenNome = :sSrVenNome,
                SrVenTipoServico = :iSrVenTipoServico,
                SrVenDetalhamento = :sSrVenDetalhamento,
                SrVenGrupo = :iSrVenGrupo,
                SrVenSubGrupo = :iSrVenSubGrupo,
                SrVenPlanoConta = :iSrVenPlanoConta,
                SrVenStatus = :iSrVenStatus,
                SrVenUsuarioAtualizador = :iSrVenUsuarioAtualizador,
                SrVenUnidade = :iSrVenUnidade
            WHERE
                SrVenId = ". $_POST['SrVenId'] .";"
        );
        $mensagem = "Serviço editado com sucesso!";
    } else {
        $sql = "INSERT INTO ServicoVenda (
        SrVenCodigo,SrVenNome,SrVenTipoServico,
        SrVenDetalhamento,SrVenGrupo,SrVenSubGrupo,SrVenPlanoConta,
        SrVenStatus,SrVenUsuarioAtualizador,SrVenUnidade)
    	VALUES (
            :sSrVenCodigo, :sSrVenNome, :iSrVenTipoServico,
            :sSrVenDetalhamento, :iSrVenGrupo, :iSrVenSubGrupo,
            :iSrVenPlanoConta, :iSrVenStatus, :iSrVenUsuarioAtualizador,
            :iSrVenUnidade
        )";        
        $sqlUltimoRegistroInserido = "SELECT MAX(SrVenId) from ServicoVenda where SrVenStatus = 1 and SrVenUnidade = " . $iUnidade .";";
        $result = $conn->query($sqlUltimoRegistroInserido);
        $row = $result->fetch(PDO::FETCH_ASSOC);
        $tmpSrvId = $row[""];
        $mensagem = "Serviço criado com sucesso!";
    }
    $result = $conn->prepare($sql);
    $result->execute($dataArray);
    foreach ($_POST['modalidades'] as &$modalidade) {
        $sql = "";
        $modalidadeArray = array(
            ':iSVXMoServicoVenda' => $_POST['SrVenId'],
            ':iSVXMoModalidade' => $modalidade['rawData']['SVXMoModalidade'],
            ':fSVXMoValorCusto' => $modalidade['rawData']['SVXMoValorCusto'],
            ':fSVXMoOutrasDespesas' => $modalidade['rawData']['SVXMoOutrasDespesas'],
            ':fSVXMoCustoFinal' => $modalidade['rawData']['SVXMoCustoFinal'],
            ':fSVXMoMargemLucro' => $modalidade['rawData']['SVXMoMargemLucro'],
            ':fSVXMoValorVenda' => $modalidade['rawData']['SVXMoValorVenda'],
            ':iSVXMoStatus' => "1",
            ':iSVXMoUnidade' => $_SESSION['UnidadeId']
        );
        if(!$_POST['SrVenId']){
            $modalidadeArray[':iSVXMoServicoVenda'] = $tmpSrvId;
        }
        if(
            ($modalidade['rawData']['SVXMoId']) == 'X')
        {
            $sql = "INSERT INTO ServicoVendaXModalidade
                (
                    SVXMoServicoVenda,
                    SVXMoModalidade,
                    SVXMoValorCusto,
                    SVXMoOutrasDespesas,
                    SVXMoCustoFinal,
                    SVXMoMargemLucro,
                    SVXMoValorVenda,
                    SVXMoStatus,
                    SVXMoUnidade
                )
            VALUES(
                :iSVXMoServicoVenda,
                :iSVXMoModalidade,
                :fSVXMoValorCusto,
                :fSVXMoOutrasDespesas,
                :fSVXMoCustoFinal,
                :fSVXMoMargemLucro,
                :fSVXMoValorVenda,
                :iSVXMoStatus,
                :iSVXMoUnidade
            );
            ";    
              
        }
        else{
            $sql = "UPDATE ServicoVendaXModalidade
            SET 
                SVXMoServicoVenda = :iSVXMoServicoVenda,
                SVXMoModalidade = :iSVXMoModalidade,
                SVXMoValorCusto = :fSVXMoValorCusto,
                SVXMoOutrasDespesas = :fSVXMoOutrasDespesas,
                SVXMoCustoFinal = :fSVXMoCustoFinal,
                SVXMoMargemLucro = :fSVXMoMargemLucro,
                SVXMoValorVenda = :fSVXMoValorVenda,
                SVXMoStatus = :iSVXMoStatus,
                SVXMoUnidade = :iSVXMoUnidade
            WHERE SVXMoId = " .$modalidade['rawData']['SVXMoId'].";";            
        }
        $result = $conn->prepare($sql);
        $result->execute($modalidadeArray); 
       }
    echo json_encode([
        'status' => 'success',
        'titulo' => 'Serviço',
        'mensagem' => $mensagem,
    ]);
} catch (PDOException $e) {
    $mensagem = "Erro ao salvar serviço.";
    echo json_encode([
        'titulo' => 'Serviço Venda',
        'status' => 'error',
        //'mensagem' => $mensagem
        'mensagem' => $e->getMessage()
        //'mensagem' =>  $sql
    ]);
}