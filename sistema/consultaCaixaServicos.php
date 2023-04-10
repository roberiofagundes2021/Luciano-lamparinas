<?php
include_once("sessao.php");
include('global_assets/php/conexao.php');

$atendimentoId = $_POST['inputAtendimentoId'];

$sql_atendimento = "SELECT SrVenNome, ProfiNome, SVXMoValorVenda, AtendDesconto
    FROM Atendimento
    JOIN ServicoVenda ON SrVenId = AtendServico
    LEFT JOIN ServicoVendaXModalidade ON SrVenId = SVXMoServicoVenda
    JOIN Profissional ON ProfiId = AtendProfissional
    WHERE AtendId = $atendimentoId AND AtendUnidade = $_SESSION[UnidadeId]";
$resultAtendimento  = $conn->query($sql_atendimento);
$rowSaldoInicial = $resultAtendimento->fetchAll(PDO::FETCH_ASSOC);

$arrayData = [];
foreach ($rowSaldoInicial as $item) {
    $procedimento = $item["SrVenNome"];
    $medico = $item["ProfiNome"];
    $valorTotal = mostraValor($item["SVXMoValorVenda"]);
    $desconto = mostraValor($item["AtendDesconto"]);

    $array = [
        'data'=>[
            isset($procedimento) ? $procedimento : null,
            isset($medico) ? $medico : null,
            isset($valorTotal) ? $valorTotal : null,
            isset($desconto) ? $desconto : null
        ],
        'identify'=>[
            
        ]
    ];

    array_push($arrayData,$array);
}

print(json_encode($arrayData));
?>