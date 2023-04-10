<?php

include_once("sessao.php");
include('global_assets/php/conexao.php');

// var_dump($_POST);

if(isset($_POST['valores'])){
    $dados = $_POST['valores'];
    $idsCont = count($_POST['valores']);

    $retorno = '';
    $strin = '';
    try{
        $conn->beginTransaction();

        $sql1 = "SELECT SituaId
                    FROM Situacao
                    WHERE SituaChave = 'RECEBIDO'";
        $result = $conn->query($sql1);
        $situacao = $result->fetch(PDO::FETCH_ASSOC);

        $valorTotal = str_replace('.', '', $_POST['valorTotal']);
        $valorTotal = str_replace(',', '.', $valorTotal);

        $sql = "INSERT INTO ContasAgrupadas ( CnAgrDtPagamento, CnAgrValorTotal, CnAgrFormaPagamento, CnAgrContaBanco, CnAgrDescricaoAgrupamento)
                VALUES ( :dateDtPagamento, :fValorTotal, :iFormaPagamento, :iContaBanco, :sDescricaoAgrupamento)";
        $result = $conn->prepare($sql);
                
        $result->execute(array(
                        ':dateDtPagamento' => $_POST['dataPagamento'],
                        ':fValorTotal' => $valorTotal,
                        ':iFormaPagamento' => $_POST['formaPagamento'],
                        ':iContaBanco' => $_POST['contaBanco'],
                        ':sDescricaoAgrupamento' => $_POST['descricaoGrupo']
                        ));

        $idContaGrupo = $conn->lastInsertId();

        for($i = 0; $i < $idsCont; $i++){
            $valor = str_replace('.', '', $dados[$i]['valor']);
            $valor = str_replace(',', '.', $valor);

            $sql = "UPDATE ContasAReceber SET CnAReContaBanco = :iContaBanco, CnAReFormaPagamento = :iFormaPagamento, CnAReDescricao = :sDescricao, CnAReDtRecebimento = :dateDtRecebimento, 
                            CnAReValorRecebido = :fValorRecebido, CnAReAgrupamento = :iAgrupamento, CnAReStatus = :iStatus, CnAReUsuarioAtualizador = :iUsuarioAtualizador, CnAReUnidade = :iUnidade
            WHERE CnAReId = ".$dados[$i]['id']."";

                $result = $conn->prepare($sql);
                $result->execute(array(
                ':iContaBanco'          => isset($_POST['contaBanco']) ? intval($_POST['contaBanco']) : null,
                ':iFormaPagamento'      => isset($_POST['formaPagamento']) ? $_POST['formaPagamento'] : null,
                ':sDescricao'           => $dados[$i]['descricao'],
                ':dateDtRecebimento'    => $_POST['dataPagamento'],
                ':fValorRecebido'       => $valor,
                ':iAgrupamento'         => $idContaGrupo,
                ':iStatus'              => intval($situacao['SituaId']),
                ':iUsuarioAtualizador'  => intval($_SESSION['UsuarId']),
                ':iUnidade'             => intval($_SESSION['UnidadeId'])
                ));
            
            if($i == 0){
                $retorno .= $strin.''.$dados[$i]['id'];
            } else {
                $retorno .= $strin.'/'.$dados[$i]['id'];
            }
        }

        $conn->commit();
        
        echo $retorno;

    } catch(PDOException $e) {	
        $conn->rollback();

        echo 'Erro.';

    }
}


?>