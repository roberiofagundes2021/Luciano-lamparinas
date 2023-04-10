<?php

include_once("sessao.php");
include('global_assets/php/conexao.php');

if (isset($_POST['numParcelas'])) {

    $sql = "SELECT * 
            FROM ContasAReceber
            LEFT JOIN Cliente ON ClienId = CnAReCliente
            WHERE CnAReId = ".$_POST['idConta']." and CnAReUnidade = " . $_SESSION['UnidadeId'];
    $result = $conn->query($sql);
    $conta = $result->fetch(PDO::FETCH_ASSOC);

    $sql = "SELECT SituaId
	        FROM Situacao
            WHERE SituaChave = 'ARECEBER'";
    $result = $conn->query($sql);
    $situacao = $result->fetch(PDO::FETCH_ASSOC);

    $numParcelas = intVal($_POST['numParcelas']);
    $parcelas = json_decode($_POST['parcelas'], true);

    $numeroLinhasNaGrid = $_POST['elementosNaGride'];

    for ($i = 0; $i <= $numParcelas - 1; $i++) {

        $sql = "INSERT INTO ContasAReceber ( CnARePlanoContas, 
                                             CnAReCliente, 
                                             CnAReContaBanco, 
                                             CnAReFormaPagamento, 
                                             CnAReNumDocumento,
                                             CnAReDtEmissao, 
                                             CnAReDescricao, 
                                             CnAReDtVencimento, 
                                             CnAReValorAReceber,
                                             CnAReDtRecebimento, 
                                             CnAReValorRecebido, 
                                             CnAReObservacao, 
                                             CnAReStatus, 
                                             CnAReUsuarioAtualizador, 
                                             CnAReUnidade)
                VALUES ( :iPlanoContas, 
                         :iCliente, 
                         :iContaBanco, 
                         :iFormaRecebimento,
                         :sNumDocumento, 
                         :dateDtEmissao, 
                         :sDescricao, 
                         :dateDtVencimento, 
                         :fValorAReceber, 
                         :dateDtRecebimento, 
                         :fValorRecebido, 
                         :sObservacao, 
                         :iStatus, 
                         :iUsuarioAtualizador, 
                         :iUnidade)";

        $result = $conn->prepare($sql);
        $result->execute(array(

            ':iPlanoContas'         => $conta['CnARePlanoContas'],
            ':iCliente'             => $conta['CnAReCliente'],
            ':iContaBanco'          => $conta['CnAReContaBanco'],
            ':iFormaRecebimento'    => $conta['CnAReFormaPagamento'],
            ':sNumDocumento'        => $conta['CnAReNumDocumento'],
            ':dateDtEmissao'        => $conta['CnAReDtEmissao'],
            ':sDescricao'           => $parcelas[$i]['descricao'],
            ':dateDtVencimento'     => $conta['CnAReDtVencimento'],
            ':fValorAReceber'       => floatval(str_replace(',', '.', $parcelas[$i]['valor'])),
            ':dateDtRecebimento'    => $parcelas[$i]['vencimento'],
            ':fValorRecebido'       => null,
            ':sObservacao'          => $conta['CnAReObservacao'],
            ':iStatus'              => $situacao['SituaId'],
            ':iUsuarioAtualizador'  => $_SESSION['UsuarId'],
            ':iUnidade'             => $_SESSION['UnidadeId']
        ));

		$sql = "DELETE FROM ContasAReceber
				WHERE CnAReId = :id";
		$result = $conn->prepare($sql);
		$result->bindParam(':id', $_POST['idConta']);
		$result->execute();        

/*
        $parcelaId = $conn->lastInsertId();
        $status = 'À Receber';
        $numeroLinhasNaGrid++;

        print("
        
        <tr>
            <td class='even'>
                <input type='checkbox' id='check" . $numeroLinhasNaGrid . "'>
                <input type='hidden' value='" . $parcelaId . "'>
            </td>
            <td class='even'><p class='m-0'>" . mostraData($parcelas[$i]['vencimento']) . "</p><input type='hidden' value='" . $parcelas[$i]['vencimento'] . "'></td>
            <td class='even'><a href='contasAPagarNovoLancamento.php?lancamentoId=" . $parcelaId . "'>" . $parcelas[$i]['descricao'] . "</a></td>
            <td class='even'>" . $conta['ClienNome'] . "</td>
            <td class='even' style='text-align: center'>" . $conta['CnAReNumDocumento'] . "</td>
            <td class='even' style='text-align: right'>" . $parcelas[$i]['valor'] . "</td>
            <td class='even' style='text-align: center'>" . $status . "</td>
            <td class='even d-flex flex-row justify-content-around align-content-center' style='text-align: center'>
            <div class='list-icons'>
                <div class='list-icons list-icons-extended'>
                    <a href='#' class='list-icons-item editarLancamento'  data-popup='tooltip' data-placement='bottom' title='Excluir Produto'><i class='icon-pencil7'></i></a>
                    <a href='#' idContaExcluir='" . $parcelaId . "' class='list-icons-item excluirConta'  data-popup='tooltip' data-placement='bottom' title='Excluir Conta'><i class='icon-bin'></i></a>
                                    <div class='dropdown'>													
                                            <a href='#' class='list-icons-item' data-toggle='dropdown'>
                                                    <i class='icon-menu9'></i>
                                
                                            <div class='dropdown-menu dropdown-menu-right'>
                            <a href='#' class='dropdown-item btnParcelar'  data-popup='tooltip' data-placement='bottom' title='Parcelar'><i class='icon-file-text2'></i> Parcelar</a>
                            <a href='#' class='dropdown-item'  data-popup='tooltip' data-placement='bottom' title='Excluir Produto'><i class='icon-file-empty'></i></a>
                                            </div>
                                    </div>
                                </div>
                </div>
            </td>
        </tr>
        ");*/
    }
}
