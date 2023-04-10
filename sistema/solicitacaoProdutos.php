<?php
include_once("sessao.php");

include('global_assets/php/conexao.php');

if (isset($_POST['solicitacaoId'])) {

    $sqlProduto = "SELECT SolicId, SlXPrQuantidade as Quantidade, ProduId as Id, ProduCodigo as Codigo,
    ProduNome as Nome, ProduFoto, CategNome, dbo.fnSaldoEstoque(SolicUnidade, ProduId, 'P', NULL) as Estoque
            FROM Solicitacao
            JOIN SolicitacaoXProduto on SlXPrSolicitacao = SolicId
            JOIN Produto on ProduId = SlXPrProduto
            JOIN Categoria on CategId = ProduCategoria
            JOIN Situacao on SituaId = ProduStatus
            WHERE SolicId = " . $_POST['solicitacaoId'] . " and SolicUnidade = " . $_SESSION['UnidadeId'] . "
            ";
    $resultProduto = $conn->query($sqlProduto);
    $rowProdutos = $resultProduto->fetchAll(PDO::FETCH_ASSOC);

    $sqlServico = "SELECT SolicId, SlXSrQuantidade as Quantidade, ServiId as Id, ServiCodigo as Codigo,
    ServiNome as Nome, CategNome, dbo.fnSaldoEstoque(SolicUnidade, ServiId, 'S', NULL) as Estoque
            FROM Solicitacao
            JOIN SolicitacaoXServico on SlXSrSolicitacao = SolicId
            JOIN Servico on ServiId = SlXSrServico
            JOIN Categoria on CategId = ServiCategoria
            JOIN Situacao on SituaId = ServiStatus
            WHERE SolicId = " . $_POST['solicitacaoId'] . " and SolicUnidade = " . $_SESSION['UnidadeId'] . "
            ";
    $resultServico = $conn->query($sqlServico);
    $rowServicos = $resultServico->fetchAll(PDO::FETCH_ASSOC);

    $row = array_merge($rowServicos, $rowProdutos);

    foreach ($row as $item) {
        print('
            <div class="custon-modal-produto">
                <div class="custon-modal-produTitle d-flex flex-column col-12 col-sm-5 col-lg-7">
                    <p>' . $item['Nome'] . '</p>
                    <p>' . $item['CategNome'] . '</p>
                </div>
                <div class="modal-controles col-12 col-sm-7 col-lg-5 row justify-content-md-center align-items-center mx-0">
                    <p class="col-12 col-sm-5 text-center">Quantidade:</p>
                    <span class="col-12 col-sm-6" style="text-align: center" type="text" class="form-control touchspin-set-value" style="display: block;">'.$item['Quantidade'].'</span>
                </div>
            </div>
        ');
    }
}
