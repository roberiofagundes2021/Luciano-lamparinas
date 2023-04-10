<?php

include_once("sessao.php");
include('global_assets/php/conexao.php');

// Neste arquivo é retornado um JSON com os registros do array $_SESSION['Carrinho']
// para que seja verificado no front-end os produtos que já foram adicionados ao carrinho.
if(!empty($_SESSION['Carrinho'])){
    $json = json_encode($_SESSION['Carrinho']);

    print($json);
    //print(''.$_SESSION['Carrinho'].'');
}


?>