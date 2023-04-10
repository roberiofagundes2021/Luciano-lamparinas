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
                    WHERE SituaChave = 'PAGO'";
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
    
            $sql = "UPDATE ContasAPagar SET CnAPaContaBanco = :iContaBanco, CnAPaFormaPagamento = :iFormaPagamento, CnAPaDescricao = :sDescricao, CnAPaDtPagamento = :dateDtPagamento, 
                                            CnAPaValorPago = :fValorPago, CnAPaAgrupamento = :iAgrupamento, CnAPaStatus = :iStatus, CnAPaUsuarioAtualizador = :iUsuarioAtualizador, CnAPaUnidade = :iUnidade
		    		WHERE CnAPaId = ".$dados[$i]['id']."";
		    $result = $conn->prepare($sql);
		    		
		    $result->execute(array(
                                ':iContaBanco' => $_POST['contaBanco'],
                                ':iFormaPagamento' => $_POST['formaPagamento'],
                                ':sDescricao' => $dados[$i]['descricao'],
                                ':dateDtPagamento' => $_POST['dataPagamento'],
                                ':fValorPago' => $valor,
                                ':iAgrupamento' => $idContaGrupo,
                                ':iStatus' => $situacao['SituaId'],
                                ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
                                ':iUnidade' => $_SESSION['UnidadeId']
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