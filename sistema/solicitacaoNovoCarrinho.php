<?php

use Mpdf\Utils\Arrays;

include_once("sessao.php");
include('global_assets/php/conexao.php');


if (!empty($_POST['inputId'])) {
    if (session_status() !== 'PHP_SESSION_ACTIVE') {

        $verifcExist = false;
        $verifcZero = false;
        $chaveProdutoZero = 0;

        // Esse trecho de código é executado se o array $_SESSION possuir o indice 
        // Carrinho.
        if (isset($_SESSION['Carrinho'])) {
            $produtos = $_SESSION['Carrinho']; // O array temporário $produtos recebe Carrinho
            $valorInicial = 1;
            // Neste foreach é verificado se o id do produto que etá sendo adicionado já está 
            // no array produtos.
            foreach ($produtos as $key => $item) {
                if ($_POST['inputId'] == $item['id']) {
                    $verifcExist = true;
                    // Caso o produto já exista, mas seu valor for 0, por ter sido excluido, ele recebe valor 1, e volta ao carrinho
                    if ($item['quantidade'] == 0) {
                        $chaveProdutoZero = $key;
                        $verifcZero = true;
                    }
                }
            }

            // Caso $verifc seja falso, o id que está vindo na req POST é novo,
            // então é adicionado ao array $produtos.
            if ($verifcExist == false) {
                array_push($produtos, ['quantidade' => 1, 'id' => $_POST['inputId'], 'type'=>$_POST['type']]);

                //Carregar o no item na tela de modal do carrinho da pagina de solicitação
                if($_POST['type'] == 'P'){
                    $sql = "SELECT ProduId as Id, ProduCodigo as Codigo, ProduNome as Nome, ProduFoto, CategNome,
                    dbo.fnSaldoEstoque(" . $_SESSION['UnidadeId'] . ", ProduId, 'P', NULL) as Estoque
                    FROM Produto
                    JOIN Categoria on CategId = ProduCategoria
                    JOIN Situacao on SituaId = ProduStatus
                    WHERE ProduId = " . $_POST['inputId'] . " and ProduEmpresa = " . $_SESSION['EmpreId'] . " and SituaChave = 'ATIVO'";
                } else {
                    $sql = "SELECT ServiId as Id, ServiCodigo as Codigo, ServiNome as Nome, CategNome
                    CategNome, dbo.fnSaldoEstoque(".$_SESSION['UnidadeId'].", ServiId, 'S', NULL) as Estoque
                    FROM Servico
                    JOIN Categoria on CategId = ServiCategoria
                    JOIN Situacao on SituaId = ServiStatus
                    WHERE ServiId = ".$_POST['inputId']." and ServiEmpresa = " . $_SESSION['EmpreId'] . " and SituaChave = 'ATIVO' ";
                }
                $result = $conn->query($sql);
                $row = $result->fetch(PDO::FETCH_ASSOC);

                print('
                    <div class="custon-modal-produto">
                        <div class="custon-modal-produTitle d-flex flex-column col-12 col-sm-5 col-lg-8">
                            <p>' . $row['Nome'] . '</p>
                            <p>' . $row['CategNome'] . '</p>
                        </div>
                        <div class="modal-controles col-12 col-sm-7 col-lg-4 row justify-content-md-center align-items-center mx-0">
                            <div class="input-group bootstrap-touchspin col-9 col-sm-9">
                                <span class="input-group-prepend">
                                    <button id="' . $row['Id'] . '" class="btn btn-light bootstrap-touchspin-down quant-edit" type="button">–</button>
                                </span>
                                <span class="input-group-prepend bootstrap-touchspin-prefix d-none">
                                    <span class="input-group-text"></span>
                                </span>
                                <input quantiEstoque="'.$row['Estoque'].'" idProdu="' . $row['Id'] . '" style="text-align: center" type="text" value="' . 1 . '" class="form-control touchspin-set-value" style="display: block;">
                                <span class="input-group-append bootstrap-touchspin-postfix d-none">
                                    <span class="input-group-text"></span>
                                </span>
                                <span class="input-group-append">
                                    <button id="' . $row['Id'] . '" class="btn btn-light bootstrap-touchspin-up quant-edit" type="button">+</button>
                                </span>
                            </div>
                            <div class="col-3 col-sm-3 row m-0">
                                <button class="btn" indexExcluir=' . $row['Id'] . '><i class="fab-icon-open icon-bin2 excluir-item"></i></button>
                            </div>
                        </div>
                    </div>
                
                     ');
                $_SESSION['Carrinho'] = $produtos;

            } else if ($verifcZero = true) {

                $_SESSION['Carrinho'][$chaveProdutoZero]['quantidade'] = 1;

                //Carregar o no item na tela de modal do carrinho da pagina de solicitação
                if($_POST['type'] == 'P'){
                    $sql = "SELECT ProduId as Id, ProduCodigo as Codigo, ProduNome as Nome, ProduFoto, CategNome,
                    dbo.fnSaldoEstoque(" . $_SESSION['UnidadeId'] . ", ProduId, 'P', NULL) as Estoque
                    FROM Produto
                    JOIN Categoria on CategId = ProduCategoria
                    JOIN Situacao on SituaId = ProduStatus
                    WHERE ProduId = " . $_POST['inputId'] . " and ProduEmpresa = " . $_SESSION['EmpreId'] . " and SituaChave = 'ATIVO'";
                } else {
                    $sql = "SELECT ServiId as Id, ServiCodigo as Codigo, ServiNome as Nome, CategNome
                    CategNome, dbo.fnSaldoEstoque(".$_SESSION['UnidadeId'].", ServiId, 'S', NULL) as Estoque
                    FROM Servico
                    JOIN Categoria on CategId = ServiCategoria
                    JOIN Situacao on SituaId = ServiStatus
                    WHERE ServiId = ".$_POST['inputId']."and ServiEmpresa = " . $_SESSION['EmpreId'] . " and SituaChave = 'ATIVO' ";
                }
                $result = $conn->query($sql);
                $row = $result->fetch(PDO::FETCH_ASSOC);

                print('
							
                    <div class="custon-modal-produto">
                        <div class="custon-modal-produTitle d-flex flex-column col-12 col-sm-5 col-lg-8">
                            <p>' . $row['Nome'] . '</p>
                            <p>' . $row['CategNome'] . '</p>
                        </div>
                        <div class="modal-controles col-12 col-sm-7 col-lg-4 row justify-content-md-center align-items-center mx-0">
                            <div class="input-group bootstrap-touchspin col-9 col-sm-9">
                                <span class="input-group-prepend">
                                    <button id="' . $row['Id'] . '" class="btn btn-light bootstrap-touchspin-down quant-edit" type="button">–</button>
                                </span>
                                <span class="input-group-prepend bootstrap-touchspin-prefix d-none">
                                    <span class="input-group-text"></span>
                                </span>
                                <input quantiEstoque="'.$row['Estoque'].'" idProdu="' . $row['Id'] . '" style="text-align: center" type="text" value="' . 1 . '" class="form-control touchspin-set-value" style="display: block;">
                                <span class="input-group-append bootstrap-touchspin-postfix d-none">
                                    <span class="input-group-text"></span>
                                </span>
                                <span class="input-group-append">
                                    <button id="' . $row['Id'] . '" class="btn btn-light bootstrap-touchspin-up quant-edit" type="button">+</button>
                                </span>
                            </div>
                            <div class="col-3 col-sm-3 row m-0">
                                <button class="btn" indexExcluir=' . $row['Id'] . '><i class="fab-icon-open icon-bin2 excluir-item"></i></button>
                            </div>
                        </div>
                    </div>
                
                     ');
            }

            // $_SESSION['Carrinho'] recebe o array atualizado com a nova posição.
        } else {

            // Se o indice Carrinho não existir em $_SESSION, é inicializado com o primeiro 
            // valor vindo no POST.
            $_SESSION['Carrinho'] = [['quantidade' => 1, 'id' => $_POST['inputId'], 'type' => $_POST['type']]];


            //Carregar o no item na tela de modal do carrinho da pagina de solicitação
            if($_POST['type'] == 'P'){
                $sql = "SELECT ProduId as Id, ProduCodigo as Codigo, ProduNome as Nome, ProduFoto, CategNome,
                dbo.fnSaldoEstoque(" . $_SESSION['UnidadeId'] . ", ProduId, 'P', NULL) as Estoque
                FROM Produto
                JOIN Categoria on CategId = ProduCategoria
                JOIN Situacao on SituaId = ProduStatus
                WHERE ProduId = " . $_POST['inputId'] . " and ProduEmpresa = " . $_SESSION['EmpreId'] . " and SituaChave = 'ATIVO'";
            } else {
                $sql = "SELECT ServiId as Id, ServiCodigo as Codigo, ServiNome as Nome, CategNome
                CategNome, dbo.fnSaldoEstoque(".$_SESSION['UnidadeId'].", ServiId, 'S', NULL) as Estoque
                FROM Servico
                JOIN Categoria on CategId = ServiCategoria
                JOIN Situacao on SituaId = ServiStatus
                WHERE ServiId = ".$_POST['inputId']."and ServiEmpresa = " . $_SESSION['EmpreId'] . " and SituaChave = 'ATIVO' ";
            }
            $result = $conn->query($sql);
            $row = $result->fetch(PDO::FETCH_ASSOC);

            print('	
                <div class="custon-modal-produto">
                    <div class="custon-modal-produTitle d-flex flex-column col-12 col-sm-5 col-lg-8">
                        <p>' . $row['Nome'] . '</p>
                        <p>' . $row['CategNome'] . '</p>
                    </div>
                    <div class="modal-controles col-12 col-sm-7 col-lg-4 row justify-content-md-center align-items-center mx-0">
                        <div class="input-group bootstrap-touchspin col-9 col-sm-9">
                            <span class="input-group-prepend">
                                <button id="' . $row['Id'] . '" class="btn btn-light bootstrap-touchspin-down quant-edit" type="button">–</button>
                            </span>
                            <span class="input-group-prepend bootstrap-touchspin-prefix d-none">
                                <span class="input-group-text"></span>
                            </span>
                            <input quantiEstoque="'.$row['Estoque'].'" idProdu="' . $row['Id'] . '" style="text-align: center" type="text" value="' . 1 . '" class="form-control touchspin-set-value" style="display: block;">
                            <span class="input-group-append bootstrap-touchspin-postfix d-none">
                                <span class="input-group-text"></span>
                            </span>
                            <span class="input-group-append">
                                <button id="' . $row['Id'] . '" class="btn btn-light bootstrap-touchspin-up quant-edit" type="button">+</button>
                            </span>
                        </div>
                        <div class="col-3 col-sm-3 row m-0">
                            <button class="btn" indexExcluir=' . $row['Id'] . '><i class="fab-icon-open icon-bin2 excluir-item"></i></button>
                        </div>
                    </div>
                </div>
            ');
        }

        //unset($_SESSION['Carrinho']);
    }
}
