<?php

include_once("sessao.php");

include('global_assets/php/conexao.php');

$_SESSION['msg'] = array();

if (isset($_POST['inputProdutoId'])) {
    $sql = "SELECT ProduNome, ProduDetalhamento, ProduCategoria, ProduSubCategoria, ProduUnidadeMedida, ProduStatus
            FROM Produto
            WHERE ProduId = " . $_POST['inputProdutoId'] . " and ProduEmpresa = " . $_SESSION['EmpreId'];
    $result = $conn->query($sql);
    $Produto = $result->fetch(PDO::FETCH_ASSOC);

    if ($Produto) {
        try {
            $sql = "INSERT INTO ProdutoOrcamento (PrOrcNome, PrOrcProduto, PrOrcDetalhamento, PrOrcCategoria, PrOrcSubCategoria, PrOrcUnidadeMedida, PrOrcSituacao, PrOrcUsuarioAtualizador, PrOrcEmpresa) 
				VALUES (:sNome, :iProduto, :sDetalhamento, :iCategoria, :iSubCategoria, :iUnidadeMedida, :iSituacao, :iUsuarioAtualizador, :iEmpresa)";
            $result = $conn->prepare($sql);

            $result->execute(array(
                ':sNome' => $Produto['ProduNome'],
                ':iProduto' => $_POST['inputProdutoId'],
                ':sDetalhamento' => $Produto['ProduDetalhamento'],
                ':iCategoria' => $Produto['ProduCategoria'],
                ':iSubCategoria' => $Produto['ProduSubCategoria'],
                ':iUnidadeMedida' => $Produto['ProduUnidadeMedida'],
                ':iSituacao' => $Produto['ProduStatus'],
                ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
                ':iEmpresa' => $_SESSION['EmpreId']
            ));

            $_SESSION['msg']['titulo'] = "Sucesso";
            $_SESSION['msg']['mensagem'] = "Produto exportado!!!";
            $_SESSION['msg']['tipo'] = "success";
        } catch (PDOException $e) {

            $_SESSION['msg']['titulo'] = "Erro";
            $_SESSION['msg']['mensagem'] = "Erro ao exportar produto!!!";
            $_SESSION['msg']['tipo'] = "error";

            echo 'Error2: ' . $e->getMessage();
            die;
        }

        irpara("produto.php");
    }
}
