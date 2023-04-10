<?php

include_once("sessao.php");
include('global_assets/php/conexao.php');

try {
    if (isset($_POST['inputId'])) {
        $sql = " UPDATE FluxoOperacional SET FlOpePrioridade = :iPrioridade, FlOpeObservacao = :sObservacao WHERE FlOpeId = " . $_POST['inputId'] . "";
        $result = $conn->prepare($sql);
        $result->execute(array(
            ':iPrioridade' => isset($_POST['cmbPrioridade']) ? $_POST['cmbPrioridade'] : null,
            ':sObservacao' => isset($_POST['observacao']) ? $_POST['observacao'] : null,
        ));
    }

    print('sucesso');

} catch (PDOException $e) {
    $_SESSION['msg']['titulo'] = "Erro";
    $_SESSION['msg']['mensagem'] = "Erro ao alterar Fluxo Operacional!!!";
    $_SESSION['msg']['tipo'] = "error";

    //$result->debugDumpParams();

    echo 'Error: ' . $e->getMessage();
    exit;
}
