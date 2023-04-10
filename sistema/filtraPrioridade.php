<?php

include_once("sessao.php");

include('global_assets/php/conexao.php');

if (isset($_POST['inputPrioridade'])) {
    $sql = "SELECT PriorId, PriorNome
			FROM Prioridade
            JOIN Situacao on SituaId = PriorStatus
			WHERE SituaChave = 'ATIVO'
            ORDER BY PriorNome ASC";

    $result = $conn->query($sql);
    $row = $result->fetchAll(PDO::FETCH_ASSOC);
    $count = count($row);

    //Verifica se já existe esse registro (se existir, retorna true)
    if ($count) {
        foreach ($row as $item) {
            if ($item['PriorId'] == $_POST['inputPrioridade']) {
                print("<option value='" . $item['PriorId'] . "' selected>" . $item['PriorNome']."</option>");
            } else {
                print("<option value='" . $item['PriorId'] . "'>" . $item['PriorNome'] . "</option>");
            }
        }
    } else {
        echo 0;
    }
}
