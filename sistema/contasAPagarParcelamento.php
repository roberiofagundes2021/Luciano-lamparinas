<?php

include_once("sessao.php");
include('global_assets/php/conexao.php');

if(isset($_POST['numParcelas'])){

    $sql = "SELECT * 
            FROM ContasAPagar
            LEFT JOIN Fornecedor on ForneId = CnAPaFornecedor
            WHERE CnAPaId = ".$_POST['idConta']." and CnAPaUnidade = " . $_SESSION['UnidadeId'] . "
    ";
    $result = $conn->query($sql);
    $conta = $result->fetch(PDO::FETCH_ASSOC);

    $sql = "SELECT SituaId
	    FROM Situacao
	    WHERE SituaChave = 'APAGAR'";
    $result = $conn->query($sql);
    $situacao = $result->fetch(PDO::FETCH_ASSOC);

    $numParcelas = intVal($_POST['numParcelas']);
    $parcelas = json_decode($_POST['parcelas'], true);

    $numeroLinhasNaGrid = $_POST['elementosNaGride'];
            
    for($i = 0; $i <= $numParcelas - 1; $i++){
    
        $sql = "INSERT INTO ContasAPagar ( CnAPaPlanoContas, CnAPaFornecedor, CnAPaContaBanco, CnAPaFormaPagamento, CnAPaNumDocumento,
                                      CnAPaNotaFiscal, CnAPaDtEmissao, CnAPaOrdemCompra, CnAPaDescricao, CnAPaDtVencimento, CnAPaValorAPagar,
                                      CnAPaDtPagamento, CnAPaValorPago, CnAPaObservacao, CnAPaStatus, CnAPaUsuarioAtualizador, CnAPaUnidade)
                VALUES ( :iPlanoContas, :iFornecedor, :iContaBanco, :iFormaPagamento,:sNumDocumento, :sNotaFiscal, :dateDtEmissao, :iOrdemCompra,
                        :sDescricao, :dateDtVencimento, :fValorAPagar, :dateDtPagamento, :fValorPago, :sObservacao, :iStatus, :iUsuarioAtualizador, :iUnidade)";
        $result = $conn->prepare($sql);

        $result->execute(array(
            
                ':iPlanoContas' => $conta['CnAPaPlanoContas'],
                ':iFornecedor' => $conta['CnAPaFornecedor'],
                ':iContaBanco' => $conta['CnAPaContaBanco'],
                ':iFormaPagamento' => $conta['CnAPaFormaPagamento'],
                ':sNumDocumento' => $conta['CnAPaNumDocumento'],
                ':sNotaFiscal' => $conta['CnAPaNotaFiscal'],
                ':dateDtEmissao' => $conta['CnAPaDtEmissao'],
                ':iOrdemCompra' => $conta['CnAPaOrdemCompra'],
                ':sDescricao' => $parcelas[$i]['descricao'],
                ':dateDtVencimento' => $conta['CnAPaDtVencimento'],
                ':fValorAPagar' => floatval(str_replace(',', '.', $parcelas[$i]['valor'])),
                ':dateDtPagamento' => $parcelas[$i]['vencimento'],
                ':fValorPago' => null,
                ':sObservacao' => $conta['CnAPaObservacao'],
                ':iStatus' => $situacao['SituaId'],
                ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
                ':iUnidade' => $_SESSION['UnidadeId']
        ));

        $sql = "DELETE FROM ContasAPagar
				WHERE CnAPaId = :id";
		$result = $conn->prepare($sql);
		$result->bindParam(':id', $_POST['idConta']);
		$result->execute();    

    /*    $parcelaId = $conn->lastInsertId();

        $status = 'À Pagar';

        $numeroLinhasNaGrid++;

        print("
        
        <tr>
            <td class='even'>
                <input type='checkbox' id='check".$numeroLinhasNaGrid."'>
                <input type='hidden' value='".$parcelaId."'>
            </td>
            <td class='even'><p class='m-0'>" . mostraData($parcelas[$i]['vencimento']) . "</p><input type='hidden' value='".$parcelas[$i]['vencimento']."'></td>
            <td class='even'><a href='contasAPagarNovoLancamento.php?lancamentoId=".$parcelaId."'>" . $parcelas[$i]['descricao'] . "</a></td>
            <td class='even'>" . $conta['ForneNome'] . "</td>
            <td class='even' style='text-align: center'>" . $conta['CnAPaNumDocumento'] . "</td>
            <td class='even' style='text-align: center'>" . $parcelas[$i]['valor'] . "</td>
            <td class='even' style='text-align: center'>" .$status. "</td>
            <td class='even d-flex flex-row justify-content-around align-content-center' style='text-align: center'>
            <div class='list-icons'>
                <div class='list-icons list-icons-extended'>
                    <a href='#' class='list-icons-item editarLancamento'  data-popup='tooltip' data-placement='bottom' title='Excluir Produto'><i class='icon-pencil7'></i></a>
                    <a href='#' idContaExcluir='".$parcelaId."' class='list-icons-item excluirConta'  data-popup='tooltip' data-placement='bottom' title='Excluir Conta'><i class='icon-bin'></i></a>
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
        ");


        // print('<input type="hidden" id="elementosGrid" value="'.count($rowData).'">');

        // $cont++;     
        // $status = $item['CnAPaStatus'] == 11 ? 'À Pagar' : 'Pago';
        // print("
        
        // <tr>
        //     <td class='even'>
        //         <input type='checkbox' id='check".$cont."'>
        //         <input type='hidden' value='".$item['CnAPaId']."'>
        //     </td>
        //     <td class='even'><p class='m-0'>" . mostraData($item['CnAPaDtVencimento']) . "</p><input type='hidden' value='".$item['CnAPaDtVencimento']."'></td>
        //     <td class='even'><a href='contasAPagarNovoLancamento.php?lancamentoId=".$item['CnAPaId']."'>" . $item['CnAPaDescricao'] . "</a></td>
        //     <td class='even'>" . $item['ForneNome'] . "</td>
        //     <td class='even' style='text-align: center'>" . $item['CnAPaNumDocumento'] . "</td>
        //     <td class='even' style='text-align: center'>" . mostraValor($item['CnAPaValorAPagar']) . "</td>
        //     <td class='even' style='text-align: center'>" .$status. "</td>
        //     <td class='even d-flex flex-row justify-content-around align-content-center' style='text-align: center'>
        //     <div class='list-icons'>
        //         <div class='list-icons list-icons-extended'>
        //             <a href='#' class='list-icons-item editarLancamento'  data-popup='tooltip' data-placement='bottom' title='Editar Conta'><i class='icon-pencil7'></i></a>
        //             <a href='#' idContaExcluir='".$item['CnAPaId']."' class='list-icons-item excluirConta'  data-popup='tooltip' data-placement='bottom' title='Excluir Conta'><i class='icon-bin'></i></a>
		// 	        <div class='dropdown'>													
		// 	        	<a href='#' class='list-icons-item' data-toggle='dropdown'>
		// 	        		<i class='icon-menu9'></i>
			    
		// 	        	<div class='dropdown-menu dropdown-menu-right'>
        //                     <a href='#' class='dropdown-item btnParcelar'  data-popup='tooltip' data-placement='bottom' title='Parcelar'><i class='icon-file-text2'></i> Parcelar</a>
        //                     <a href='#' class='dropdown-item'  data-popup='tooltip' data-placement='bottom' title='Excluir Produto'><i class='icon-file-empty'></i></a>
		// 	        	</div>
		// 	        </div>
		// 	    </div>
               
        //         </div>
        //     </td>
        // </tr>
        // ");*/
    }
}

?>