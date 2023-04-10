<?php

include_once("sessao.php");

include('global_assets/php/conexao.php');

$sql = "SELECT ParamProdutoOrcamento, ParamServicoOrcamento
        FROM Parametro
        WHERE ParamEmpresa = " . $_SESSION['EmpreId'] . "
       ";
$result = $conn->query($sql);
$rowParametro = $result->fetch(PDO::FETCH_ASSOC);

if (isset($_POST['tipoTr'])) {

    if ($_POST['tipoTr'] == 'PS') {

        $tabela1 = '';
        $tabela2 = '';
        $prefixo1 = '';
        $prefixo2 = '';
        $categoria = '';
        $categoriaId = '';
        $situacaoOuStatus1 = '';
        $situacaoOuStatus2 = '';
        $subcategoriaString1 = '';
        $subcategoriaString2 = '';

        $rowParametro['ParamProdutoOrcamento'] == 0 ? ($tabela1 = 'Produto' and $prefixo1 = 'Produ' and  $situacaoOuStatus1 = 'Status') : ($tabela1 = 'ProdutoOrcamento' and $prefixo1 = 'PrOrc' and  $situacaoOuStatus1 = 'Situacao');
        $rowParametro['ParamServicoOrcamento'] == 0 ? ($tabela2 = 'Servico' and $prefixo2 = 'Servi' and  $situacaoOuStatus2 = 'Status') : ($tabela2 = 'ServicoOrcamento' and $prefixo2 = 'SrOrc' and  $situacaoOuStatus2 = 'Situacao');

        isset($_POST['cmbCategoriaId']) ? $categoriaId = $_POST['cmbCategoriaId'] : $categoriaId = '';

        if (isset($_POST['cmbSubCategoriaArray'])) {
            $subcategoriaString1 = " and " . $prefixo1 . "SubCategoria IN (" . implode(",", $_POST['cmbSubCategoriaArray']) . ")";
            $subcategoriaString2 = " and " . $prefixo2 . "SubCategoria IN (" . implode(",", $_POST['cmbSubCategoriaArray']) . ")";
        } else {
            $subcategoriaString = "";
        }
        //var_dump($subcategoriaString);

        $sql = "SELECT " . $prefixo1 . "Nome as nome
            FROM " . $tabela1 . "
            JOIN Situacao on SituaId = " . $prefixo1 . "".$situacaoOuStatus1."
            WHERE " . $prefixo1 . "Unidade = " . $_SESSION['UnidadeId'] . " and " . $prefixo1 . "Categoria = " . $categoriaId . "" . $subcategoriaString1 . " and SituaChave = 'ATIVO'
            UNION
            SELECT " . $prefixo2 . "Nome as nome
            FROM " . $tabela2 . "
            JOIN Situacao on SituaId = " . $prefixo2 . "".$situacaoOuStatus2."
            WHERE " . $prefixo2 . "Unidade = " . $_SESSION['UnidadeId'] . " and " . $prefixo2 . "Categoria = " . $categoriaId . "" . $subcategoriaString2 . "  and SituaChave = 'ATIVO'
           ";
        $result = $conn->query($sql);
        $row = $result->fetchAll(PDO::FETCH_ASSOC);
        //print($sql);

        if (count($row) >= 1) {
            echo 'existem produtos';
        } else {
            echo 'sem produtos';
        }
    } else {

        $tabela = '';
        $prefixo = '';
        $situacaoOuStatus = '';
        $tipoSelect = '';
        $categoriaId = '';
        $subcategoriaString = '';

        if($_POST['tipoTr'] == 'P'){
            $rowParametro['ParamProdutoOrcamento'] == 0 ? ($tabela = 'Produto' and $prefixo = 'Produ' and  $situacaoOuStatus = 'Status') : ($tabela = 'ProdutoOrcamento' and $prefixo = 'PrOrc' and  $situacaoOuStatus = 'Situacao');
        } else {
            $rowParametro['ParamServicoOrcamento'] == 0 ? ($tabela = 'Servico' and $prefixo = 'Servi' and  $situacaoOuStatus = 'Status') : ($tabela = 'ServicoOrcamento' and $prefixo = 'SrOrc' and  $situacaoOuStatus = 'Situacao');
        }


        isset($_POST['cmbCategoriaId']) ? $categoriaId = $_POST['cmbCategoriaId'] : $categoriaId = '';

        if (isset($_POST['cmbSubCategoriaArray'])) {
            $subcategoriaString = " and " . $prefixo . "SubCategoria IN (" . implode(",", $_POST['cmbSubCategoriaArray']) . ")";
        } else {
            $subcategoriaString = "";
        }

        $sql = "SELECT *
            FROM " . $tabela . "
            JOIN Situacao on SituaId = " . $prefixo . "".$situacaoOuStatus."
            WHERE " . $prefixo . "Unidade = " . $_SESSION['UnidadeId'] . " and " . $prefixo . "Categoria = " . $categoriaId . "" . $subcategoriaString . "  and SituaChave = 'ATIVO'
           ";
        $result = $conn->query($sql);
        $row = $result->fetchAll(PDO::FETCH_ASSOC);
        //print($sql);

        if (count($row) >= 1) {
            echo 'existem produtos';
        } else {
            echo 'sem produtos';
        }
    }
}
?>
