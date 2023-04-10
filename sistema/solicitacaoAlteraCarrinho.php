<?php

include_once("sessao.php");
include('global_assets/php/conexao.php');


if (isset($_SESSION['Carrinho']) && !empty($_POST['inputIdProduto'])) {
    $quantProdu = $_POST['inputQuantidadeProduto']?$_POST['inputQuantidadeProduto']:'';
    $idProdu =  $_POST['inputIdProduto'];
    $produtos = $_SESSION['Carrinho'];
    $newProdutos = [];

    foreach ($produtos as $key => $value) {
        // essa parte verifica o array e retira o item que o usuário escolheu
        // OBS.: Antes setava a quantidade como "0", agora exclui do array
        if($quantProdu != 0 || $quantProdu == ''){
            if ($value['id'] == $idProdu) {
                $produtos[$key]['quantidade'] = $quantProdu;
                print($produtos[$key]['quantidade']);
            }
            array_push($newProdutos, $produtos[$key]);
        } else {
            if ($value['id'] != $idProdu) {
                array_push($newProdutos, $value);
            }
        }
    }
    $_SESSION['Carrinho'] = $newProdutos;
} else {
    print('erro');
}
