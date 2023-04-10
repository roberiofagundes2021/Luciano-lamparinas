<?php

include_once("sessao.php");
include('global_assets/php/conexao.php');

function queryPesquisa()
{

    $cont = 0;

    include('global_assets/php/conexao.php');

    $args = [];
    if (!empty($_POST['inputPesquisaProduto'])) {
        $args[]  = $_POST['inputProdutoServico'] == 'P'? "ProduNome LIKE '%" . $_POST['inputPesquisaProduto'] . "%'" : "ServiNome LIKE '%" . $_POST['inputPesquisaProduto'] . "%'";
    }

    if (!empty($_POST['inputCategoria'])) {
        $args[]  = $_POST['inputProdutoServico'] == 'P'? "ProduCategoria = " . $_POST['inputCategoria'] . " " : "ServiCategoria = " . $_POST['inputCategoria'] . " ";
    }

    if (!empty($_POST['inputSubCategoria'])) {
        $args[]  = $_POST['inputProdutoServico'] == 'P'? "ProduSubCategoria = " . $_POST['inputSubCategoria'] . " " : "ServiSubCategoria = " . $_POST['inputSubCategoria'] . " ";
    }

    /*
    if (!empty($_POST['inputMarca'])) {
        $args[]  = $_POST['inputProdutoServico'] == 'P'? "PrXFaMarca = " . $_POST['inputMarca'] . " " : "SrXFaMarca = " . $_POST['inputMarca'] . " ";
    }

    if (!empty($_POST['inputFabricante'])) {
        $args[]  = $_POST['inputProdutoServico'] == 'P'? "PrXFaFabricante = " . $_POST['inputFabricante'] . " " : "SrXFaFabricante = " . $_POST['inputFabricante'] . " ";
    }

    if (!empty($_POST['inputModelo'])) {
        $args[]  = $_POST['inputProdutoServico'] == 'P'? "PrXFaModelo = " . $_POST['inputModelo'] . " " : "SrXFaModelo = " . $_POST['inputModelo'] . " ";
    }
    */

    if (count($args) >= 1) {
        try {

            $string = implode(" and ", $args);

            if ($string != '') {
                $string .= ' and ';
            }

            if ($_POST['inputProdutoServico'] == 'S'){
                $sql = "WITH itens as (SELECT  ServiId as Id, ServiCodigo as Codigo, ServiNome as Nome, ServiDetalhamento as Detalhamento, 
                CategNome, dbo.fnSaldoEstoque(".$_SESSION['UnidadeId'].", ServiId, 'S', NULL) as Estoque, ROW_NUMBER() OVER(ORDER BY ServiId) as rownum
                FROM Servico
                -- JOIN ServicoXFabricante on SrXFaServico = ServiId and SrXFaFluxoOperacional is not null
                JOIN Categoria on CategId = ServiCategoria
                JOIN Situacao on SituaId = ServiStatus
                WHERE dbo.fnSaldoEstoque(".$_SESSION['UnidadeId'].", ServiId, 'S', NULL) > 0 and $string ServiEmpresa = " . $_SESSION['EmpreId'] . " and SituaChave = 'ATIVO')
                SELECT Id, Codigo, Nome, Detalhamento, CategNome, Estoque, rownum
                FROM itens WHERE rownum >= ".$_POST['min']." and rownum <= ".$_POST['max']." ORDER BY Nome ASC";

                $sqlCount = "SELECT ServiId
                FROM Servico
                -- JOIN ServicoXFabricante on SrXFaServico = ServiId and SrXFaFluxoOperacional is not null
                JOIN Categoria on CategId = ServiCategoria
                JOIN Situacao on SituaId = ServiStatus
                WHERE dbo.fnSaldoEstoque(".$_SESSION['UnidadeId'].", ServiId, 'S', NULL) > 0 and $string ServiEmpresa = " . $_SESSION['EmpreId'] . " and SituaChave = 'ATIVO'";
            } else {
                $sql = "WITH itens as (SELECT ProduId as Id, ProduCodigo as Codigo, ProduNome as Nome, ProduDetalhamento as Detalhamento, 
                ProduFoto, CategNome, dbo.fnSaldoEstoque(" . $_SESSION['UnidadeId'] . ", ProduId, 'P', NULL) as Estoque, ROW_NUMBER() OVER(ORDER BY ProduId) as rownum
                FROM Produto
                -- JOIN ProdutoXFabricante on PrXFaProduto = ProduId and PrXFaFluxoOperacional is not null
                JOIN Categoria on CategId = ProduCategoria
                JOIN Situacao on SituaId = ProduStatus
                WHERE dbo.fnSaldoEstoque(" . $_SESSION['UnidadeId'] . ", ProduId, 'P', NULL) > 0 and $string ProduEmpresa = " . $_SESSION['EmpreId'] . " and SituaChave = 'ATIVO')
                SELECT Id, Codigo, Nome, Detalhamento, CategNome, Estoque, ProduFoto, rownum
                FROM itens WHERE rownum >= ".$_POST['min']." and rownum <= ".$_POST['max']." ORDER BY Nome ASC";

                $sqlCount = "SELECT ProduId
                FROM Produto
                -- JOIN ProdutoXFabricante on PrXFaProduto = ProduId and PrXFaFluxoOperacional is not null
                JOIN Categoria on CategId = ProduCategoria
                JOIN Situacao on SituaId = ProduStatus
                WHERE dbo.fnSaldoEstoque(" . $_SESSION['UnidadeId'] . ", ProduId, 'P', NULL) > 0  and $string ProduEmpresa = " . $_SESSION['EmpreId'] . " and SituaChave = 'ATIVO'";
            }
            $result = $conn->query($sql);
            $rowData = $result->fetchAll(PDO::FETCH_ASSOC);

            $resultCount = $conn->query($sqlCount);
            $count = COUNT($resultCount->fetchAll(PDO::FETCH_ASSOC));

            count($rowData) >= 1 ? $cont = 1 : $cont = 0;
        } catch (PDOException $e) {
            echo 'Error: ' . $e->getMessage();
        }
    } else {
        try {
            if ($_POST['inputProdutoServico'] == 'S'){
                $sql = "WITH itens as (SELECT  ServiId as Id, ServiCodigo as Codigo, ServiNome as Nome, ServiDetalhamento as Detalhamento, 
                CategNome, dbo.fnSaldoEstoque(".$_SESSION['UnidadeId'].", ServiId, 'S', NULL) as Estoque, ROW_NUMBER() OVER(ORDER BY ServiNome) as rownum
                FROM Servico
                -- JOIN ServicoXFabricante on SrXFaServico = ServiId and SrXFaFluxoOperacional is not null
                JOIN Categoria on CategId = ServiCategoria
                JOIN Situacao on SituaId = ServiStatus
                WHERE dbo.fnSaldoEstoque(".$_SESSION['UnidadeId'].", ServiId, 'S', NULL) > 0 and ServiEmpresa = " . $_SESSION['EmpreId'] . " and SituaChave = 'ATIVO')
                SELECT Id, Codigo, Nome, Detalhamento, CategNome, Estoque, rownum
                FROM itens WHERE rownum >= ".$_POST['min']." and rownum <= ".$_POST['max']." ORDER BY Nome ASC";

                $sqlCount = "SELECT ServiId
                FROM Servico
                -- JOIN ServicoXFabricante on SrXFaServico = ServiId and SrXFaFluxoOperacional is not null
                JOIN Situacao on SituaId = ServiStatus
                WHERE dbo.fnSaldoEstoque(".$_SESSION['UnidadeId'].", ServiId, 'S', NULL) > 0 and ServiEmpresa = " . $_SESSION['EmpreId'] . " and SituaChave = 'ATIVO'";
            } else {
                $sql = "WITH itens as (SELECT ProduId as Id, ProduCodigo as Codigo, ProduNome as Nome, ProduDetalhamento as Detalhamento, 
                ProduFoto, CategNome, dbo.fnSaldoEstoque(" . $_SESSION['UnidadeId'] . ", ProduId, 'P', NULL) as Estoque, ROW_NUMBER() OVER(ORDER BY ProduNome) as rownum
                FROM Produto
                -- JOIN ProdutoXFabricante on PrXFaProduto = ProduId and PrXFaFluxoOperacional is not null
                JOIN Categoria on CategId = ProduCategoria
                JOIN Situacao on SituaId = ProduStatus
                WHERE dbo.fnSaldoEstoque(" . $_SESSION['UnidadeId'] . ", ProduId, 'P', NULL) > 0 and ProduEmpresa = " . $_SESSION['EmpreId'] . " and SituaChave = 'ATIVO')
                SELECT Id, Codigo, Nome, Detalhamento, CategNome, Estoque, ProduFoto, rownum
                FROM itens WHERE rownum >= ".$_POST['min']." and rownum <= ".$_POST['max']." ORDER BY Nome ASC";
                
                $sqlCount = "SELECT ProduId
                FROM Produto
                -- JOIN ProdutoXFabricante on PrXFaProduto = ProduId and PrXFaFluxoOperacional is not null
                JOIN Situacao on SituaId = ProduStatus
                WHERE dbo.fnSaldoEstoque(" . $_SESSION['UnidadeId'] . ", ProduId, 'P', NULL) > 0 and ProduEmpresa = " . $_SESSION['EmpreId'] . " and SituaChave = 'ATIVO'";
            }
            $result = $conn->query($sql);
            $rowData = $result->fetchAll(PDO::FETCH_ASSOC);

            $resultCount = $conn->query($sqlCount);
            $count = COUNT($resultCount->fetchAll(PDO::FETCH_ASSOC));

            $cont = 1;
        } catch (PDOException $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    if ($cont == 1) {
        print("<input type='hidden' id='count' value='".$count."'/>");
        $cont = 0;
        foreach ($rowData as $item) {
            $cont++;

            $sFoto = "global_assets/images/lamparinas/sem_foto.gif";

            if ($_POST['inputProdutoServico'] == 'P'){

                if ($item['ProduFoto'] != null) {

                    //Depois verifica se o arquivo físico ainda existe no servidor
                    if (file_exists("global_assets/images/produtos/" . $item['ProduFoto'])) {
                        $sFoto = "global_assets/images/produtos/" . $item['ProduFoto'];
                    } else {
                        $sFoto = "global_assets/images/lamparinas/sem_foto.gif";
                    }
                }        
            }

            if ($item['Estoque'] > 0) {
                print('
                    <div class="col-xl-3 col-sm-3">
                        <div class="card">');
                
                if ($_POST['inputProdutoServico'] == 'P'){
                    print('        
                    <div class="card-body">
                        <div class="card-img-actions" id="Imagens">
                            <a href="' . $sFoto . '" class="fancybox">
                                <img src="' . $sFoto . '" class="card-img"  alt="" style="max-height:250px;">
                                <span class="card-img-actions-overlay card-img">
                                    <i class="icon-plus3 icon-2x"></i>
                                </span>
                            </a>
                        </div>
                    </div>');
                }

                print('
                            <div class="card-body bg-light text-center">
                                <div class="mb-2">
                                    <h6 class="font-weight-semibold mb-0" data-popup="tooltip" title="' . $item['Detalhamento'] . '" style="height: 46.1667px; overflow: hidden">
                                        <a href="#" class="text-default">' . $item['Nome'] . '</a>
                                    </h6>

                                    <a href="#" class="text-muted">' . $item['CategNome'] . '</a>
                                </div>
                                <div class="text-muted mb-3">' . $item['Estoque'] . ' em estoque</div>

                                <button produId=' . $item['Id'] . ' type="button" class="btn btn-produtos bg-teal-400 add-cart"><i class="icon-cart-add mr-2"></i> Adicionar ao carrinho</button>
                            </div>
                        </div>
                    </div>							
                ');
            } else {
                print('
                    <div class="col-xl-3 col-sm-3">
                        <div class="card">');
                
                if ($_POST['inputProdutoServico'] == 'P'){
                    print('                        
                    <div class="card-body">
                        <div class="card-img-actions" id="Imagens">
                            <a href="' . $sFoto . '" class="fancybox">
                                <img src="' . $sFoto . '" class="card-img"  alt="" style="max-height:250px;">
                                <span class="card-img-actions-overlay card-img">
                                    <i class="icon-plus3 icon-2x"></i>
                                </span>
                            </a>
                        </div>
                    </div>');
                }

                print('
                            <div class="card-body bg-light text-center">
                                <div class="mb-2">
                                    <h6 class="font-weight-semibold mb-0" data-popup="tooltip" title="' . $item['Detalhamento'] . '" style="height: 46.1667px; overflow: hidden">
                                        <a href="#" class="text-default">' . $item['Nome'] . '</a>
                                    </h6>

                                    <a href="#" class="text-muted">' . $item['CategNome'] . '</a>
                                </div>
                                <div class="text-muted mb-3">' . $item['Estoque'] . ' em estoque</div>

                                <button produId=' . $item['Id'] . ' type="button" class="btn btn-produtos bg-teal-400 add-cart" disabled><i class="icon-cart-add mr-2"></i> Adicionar ao carrinho</button>
                            </div>
                        </div>
                    </div>							
                ');
            }
        }
    }
}

queryPesquisa();
