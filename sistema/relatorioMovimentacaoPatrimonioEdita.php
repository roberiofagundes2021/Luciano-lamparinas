<?php

include_once("sessao.php");
include('global_assets/php/conexao.php');

try {
    $conn->beginTransaction();

    if (isset($_POST['inputId'])) {
        $iPatrimonio = $_POST['inputId'];
        $sql = " UPDATE Patrimonio SET PatriNumSerie = :sNumeroSerie, PatriEstadoConservacao = :iEstadoConservacao WHERE PatriId = " . $iPatrimonio . "";
        $result = $conn->prepare($sql);
        $result->execute(array(
            ':sNumeroSerie' => isset($_POST['inputNumeroSerie']) ? $_POST['inputNumeroSerie'] : null,
            ':iEstadoConservacao' => isset($_POST['cmbEstadoConservacao']) ? $_POST['cmbEstadoConservacao'] : null,
        ));

        if(isset($_POST['inputProdutoXFabricante']) && $_POST['inputProdutoXFabricante'] != '') {
            $sql = "UPDATE ProdutoXFabricante SET PrXFaMarca = :iMarca, PrXFaModelo = :iModelo, PrXFaFabricante = :iFabricante
                    WHERE PrXFaId = :iId";
            $resultProdutoXFabricante = $conn->prepare($sql);
                    
            $resultProdutoXFabricante->execute(array(
                            ':iMarca' 	   	        => isset($_POST['cmbPatriMarca']) ? $_POST['cmbPatriMarca'] : 0,
                            ':iModelo' 	   	        => isset($_POST['cmbPatriModelo']) ? $_POST['cmbPatriModelo'] : 0,
                            ':iFabricante' 			=> isset($_POST['cmbPatriFabricante']) ? $_POST['cmbPatriFabricante'] : 0,
                            ':iId' 	                => $_POST['inputProdutoXFabricante']
                            ));
        }else {
            $sql = "INSERT INTO ProdutoXFabricante (PrXFaProduto, PrXFaPatrimonio, PrXFaMarca, PrXFaModelo, PrXFaFabricante, PrXFaUnidade)
                    VALUES (:iProduto, :iPatrimonio, :iMarca, :iModelo, :iFabricante, :iUnidade)";
            $resultProdutoXFabricante = $conn->prepare($sql);
            
            $resultProdutoXFabricante->execute(array(
                            ':iProduto' 			=> isset($_POST['inputProduto']) ? $_POST['inputProduto'] : 0,
                            ':iPatrimonio' 	        => $iPatrimonio,
                            ':iMarca' 	   	        => isset($_POST['cmbPatriMarca']) ? $_POST['cmbPatriMarca'] : 0,
                            ':iModelo' 	   	        => isset($_POST['cmbPatriModelo']) ? $_POST['cmbPatriModelo'] : 0,
                            ':iFabricante' 			=> isset($_POST['cmbPatriFabricante']) ? $_POST['cmbPatriFabricante'] : 0,
                            ':iUnidade' 			=> $_SESSION['UnidadeId']
                            ));
        }
    }
    $conn->commit();

    print('sucesso');

} catch (PDOException $e) {
    $conn->rollback();

    $_SESSION['msg']['titulo'] = "Erro";
    $_SESSION['msg']['mensagem'] = "Erro ao alterar movimentação patrimônio!!!";
    $_SESSION['msg']['tipo'] = "error";

    //$result->debugDumpParams();

    echo 'Error: ' . $e->getMessage();
    exit;
}
