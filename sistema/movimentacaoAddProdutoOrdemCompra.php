<?php

include_once("sessao.php");

include('global_assets/php/conexao.php');

$ordemCompra = $_POST['ordemCompra'];
$iUnidade = $_SESSION['UnidadeId'];
$iEmpresa = $_SESSION['EmpreId'];

$sql = "SELECT OCXPrQuantidade as quantidade, ProduId as id, ProduNome as nome, OCXPrDetalhamento as detalhamento,
        OCXPrValorUnitario as valorCusto, ProduCustoFinal as custoFinal, UnMedSigla, MarcaNome, tipo = 'P',
        MarcaNome, ModelNome, FabriNome
		FROM OrdemCompraXProduto
        JOIN Produto on ProduId = OCXPrProduto
        JOIN OrdemCompra on OrComId = OCXPrOrdemCompra
        LEFT JOIN ProdutoXFabricante on PrXFaProduto = ProduId and PrXFaFluxoOperacional = OrComFluxoOperacional and PrXFaUnidade = OrComUnidade
 		LEFT JOIN UnidadeMedida on UnMedId = ProduUnidadeMedida
        LEFT JOIN Marca on MarcaId = PrXFaMarca
        LEFT JOIN Modelo on ModelId = PrXFaModelo
        LEFT JOIN Fabricante on FabriId = PrXFaFabricante
        WHERE OCXPrUnidade = $iUnidade and OCXPrOrdemCompra = $ordemCompra
        UNION
        SELECT OCXSrQuantidade as quantidade, ServiId as id, ServiNome as nome, OCXSrDetalhamento as detalhamento,
        OCXSrValorUnitario as valorCusto, ServiCustoFinal as custoFinal, '', MarcaNome, tipo = 'S',
        MarcaNome, ModelNome, FabriNome
		FROM OrdemCompraXServico
        JOIN Servico on ServiId = OCXSrServico
        JOIN OrdemCompra on OrComId = OCXSrOrdemCompra
        LEFT JOIN ServicoXFabricante on SrXFaServico = ServiId and SrXFaFluxoOperacional = OrComFluxoOperacional and SrXFaUnidade = OrComUnidade
        LEFT JOIN Marca on MarcaId = SrXFaMarca
        LEFT JOIN Modelo on ModelId = SrXFaModelo
        LEFT JOIN Fabricante on FabriId = SrXFaFabricante
        WHERE ServiEmpresa = $iEmpresa and OCXSrOrdemCompra = $ordemCompra
        ORDER BY nome ASC";
$result = $conn->query($sql);
$row = $result->fetchAll(PDO::FETCH_ASSOC);
$count = count($row);

$output = '';
$totalGeral = 0;

$sql = "SELECT MovimId
		FROM Movimentacao
		JOIN Situacao on SituaId = MovimSituacao
	    WHERE MovimOrdemCompra = " . $_POST['ordemCompra'] . " and MovimTipo = 'E' and 
        MovimUnidade = " . $_SESSION['UnidadeId'] . " and SituaChave in ('LIBERADOCENTRO', 'LIBERADOCONTABILIDADE', 'AGUARDANDOLIBERACAOCENTRO', 'AGUARDANDOLIBERACAOCONTABILIDADE') ";
$result = $conn->query($sql);
$movimentAprovada = $result->fetchAll(PDO::FETCH_ASSOC);
$countMovimentAprovada = count($movimentAprovada);

$sql = " SELECT dbo.fnValorTotalOrdemCompra(" . $_SESSION['UnidadeId'] . ",  " . $_POST['ordemCompra'] . ") as valorTotalOrdemCompra";
$result = $conn->query($sql);
$totalOrdemCompra = $result->fetch(PDO::FETCH_ASSOC);

$sql = " SELECT dbo.fnSaldoEntradaOrdemCompra(" . $_SESSION['UnidadeId'] . ",  " . $_POST['ordemCompra'] . ") as saldoOrdemCompra";
$result = $conn->query($sql);
$saldoOrdemCompra = $result->fetch(PDO::FETCH_ASSOC);

// Vefirica se já teve alguma entrada para essa Ordem de Compra
if ($countMovimentAprovada) {
 
    if ($count) {
        $numItens = 0;

        $output .= ' <table class="table" id="tabelaProdutoServico">';

        $output .= '<thead>
                    <tr class="bg-slate">
                        <th style="text-align: center">Item</th>
                        <th>Produto/Serviço</th>
                        <th style="text-align: center">Unidade Medida</th>
                        <th style="text-align: center">Quantidade</th>
                        <th style="text-align: center">Saldo</th>
                        <th style="text-align: right; width: 10%">Valor Unitário</th>
                        <th style="text-align: right; width: 13%">Valor Total</th>
                        <th style="text-align: center; width: 10%">Validade</th>
                        <th style="text-align: center">Ações</th>
                    </tr>
                </thead>
        ';
        $output .= '<tbody>';

        foreach ($row as $item) {

            if ($item['tipo'] == 'P') {
                $sql = "SELECT  dbo.fnQuantidadeEntrada(OrComUnidade, OrComId, " . $item['id'] . ", 'P') as Quantidade, dbo.fnSaldoEntrada(OrComUnidade, OrComId, " . $item['id'] . ", 'P') as Saldo
                        FROM OrdemCompra
                        Where OrComUnidade = " . $_SESSION['UnidadeId'] . " and OrComId = '" . $_POST['ordemCompra'] . "'
                   ";
                $result = $conn->query($sql);
                $saldo = $result->fetch(PDO::FETCH_ASSOC);
            } else {

                $sql = "SELECT  dbo.fnQuantidadeEntrada(OrComUnidade, OrComId, " . $item['id'] . ", 'S') as Quantidade, dbo.fnSaldoEntrada(OrComUnidade, OrComId, " . $item['id'] . ", 'S') as Saldo
                        FROM OrdemCompra
                        Where OrComUnidade = " . $_SESSION['UnidadeId'] . " and OrComId = '" . $_POST['ordemCompra'] . "'
                 ";
                $result = $conn->query($sql);
                $saldo = $result->fetch(PDO::FETCH_ASSOC);
            }

            $numItens++;

            $valorCusto = formataMoeda($item['valorCusto']);
            $valorTotal = formataMoeda($item['quantidade'] * $item['valorCusto']);

            $marca = '';
            $marca .= $item['MarcaNome'] ?  ' MARCA: '. $item['MarcaNome'] : '';
            $marca .= $item['ModelNome'] ?  ' MODELO: '. $item['ModelNome'] : '';
            $marca .= $item['FabriNome'] ?  ' FABRICANTE: '. $item['FabriNome'] : '';

            $output .=  "<tr class='trGrid' id='row$numItens'>
						 <td title='$item[detalhamento]$marca' data-popup='tooltip' style='text-align: center'>$numItens</td>
						 <td title='$item[detalhamento]$marca' data-popup='tooltip'>$item[nome]</td>
						 <td title='$item[detalhamento]$marca' data-popup='tooltip' style='text-align: center'>$item[UnMedSigla]</td>
                         <td title='$item[detalhamento]$marca' data-popup='tooltip' style='text-align: center'>0</td>
                         <td title='$item[detalhamento]$marca' data-popup='tooltip' style='text-align: center'>$saldo[Saldo]</td>
						 <td title='$item[detalhamento]$marca' data-popup='tooltip' style='text-align: right'>$valorCusto</td>
                         <td title='$item[detalhamento]$marca' data-popup='tooltip' class='valorTotal' style='text-align: right'>R$ 0, 00</td>
                         <td title='$item[detalhamento]$marca' data-popup='tooltip' style='text-align: center'></td>
                         <td style='text-align: center'><i idInput='campo$numItens' idRow='row$numItens' class='icon-file-check btn-acoes' style='cursor: pointer'></i></td>
                         <input type='hidden' tipo='$item[tipo]' id='campo$numItens' idLinha='row$numItens' quantInicial='$saldo[Quantidade]' saldoInicial='$saldo[Saldo]'  name='campo$numItens' value='$item[tipo]#$item[id]#$item[valorCusto]#0#0#0#0#0#0#$item[detalhamento]'>
                         <input id='MarcaNome' type='hidden' value='$item[MarcaNome]' />
                         <input id='ModelNome' type='hidden' value='$item[ModelNome]' />
                         <input id='FabriNome' type='hidden' value='$item[FabriNome]' />
					<tr>";
            // $output .= "<input type='hidden' tipo='".$item['tipo']."' id='campo".$numItens."' name='campo".$numItens."' value='".$item['id']."#".$item['valorCusto']."'>";
        }

        $output .= '</tbody>';

        $totalGeral = formataMoeda($totalGeral);

        $output .= '
            <tfoot>
                <tr>
                    <th colspan="6" style="text-align:right; font-size: 16px; font-weight:bold;">
                         <div>Total (R$) Nota Fiscal:</div>
                         <div>Saldo (R$) Ordem de Compra/Carta Contrato:</div>
                    </th>
                    <th colspan="1">
                        <div>
                            <div id="total" valorTotalGeral="" style="text-align:right; font-size: 15px; font-weight:bold;">R$ 0, 00</div>
                        </div>
                        <div>
                            <div id="totalSaldo" style="text-align:right; font-size: 15px; font-weight:bold;" valorTotalInicial="'.$saldoOrdemCompra['saldoOrdemCompra'].'" valor="' . $saldoOrdemCompra['saldoOrdemCompra'] . '">' . formataMoeda($saldoOrdemCompra['saldoOrdemCompra']) . '</div>
                         </div>
                    </th>
                    <th colspan="2">
                    </th>
                </tr>
            </tfoot>
    ';

        $output .= '</table>';

        echo $output;
    } else {
        echo 0;
    }
     

} else {

    if ($count) {
        $numItens = 0;

        $output .= ' <table class="table" id="tabelaProdutoServico">';

        $output .= '<thead>
                    <tr class="bg-slate">
                        <th style="text-align: center">Item</th>
                        <th>Produto/Serviço</th>
                        <th style="text-align: center">Unidade Medida</th>
                        <th style="text-align: center">Quant. Recebida</th>
                        <th style="text-align: center">Saldo</th>
                        <th style="text-align: right; width: 10%">Valor Unitário</th>
                        <th style="text-align: right; width: 13%">Valor Total</th>
                        <th style="text-align: center; width: 10%">Validade</th>
                        <th style="text-align: center">Ações</th>
                    </tr>
                </thead>
        ';
        $output .= '<tbody>';

        foreach ($row as $item) {

            if ($item['tipo'] == 'P') {
                $sql = "SELECT  dbo.fnQuantidadeEntrada(OrComUnidade, OrComId, " . $item['id'] . ", 'P') as Quantidade, dbo.fnSaldoEntrada(OrComUnidade, OrComId, " . $item['id'] . ", 'P') as Saldo
                        FROM OrdemCompra
                        Where OrComUnidade = " . $_SESSION['UnidadeId'] . " and OrComId = '" . $_POST['ordemCompra'] . "'
                   ";
                $result = $conn->query($sql);
                $saldo = $result->fetch(PDO::FETCH_ASSOC);
            } else {

                $sql = "SELECT  dbo.fnQuantidadeEntrada(OrComUnidade, OrComId, " . $item['id'] . ", 'S') as Quantidade, dbo.fnSaldoEntrada(OrComUnidade, OrComId, " . $item['id'] . ", 'S') as Saldo
                        FROM OrdemCompra
                        Where OrComUnidade = " . $_SESSION['UnidadeId'] . " and OrComId = '" . $_POST['ordemCompra'] . "'
                 ";
                $result = $conn->query($sql);
                $saldo = $result->fetch(PDO::FETCH_ASSOC);
            }

            $numItens++;

            $valorCusto = formataMoeda($item['valorCusto']);
            $valorTotal = formataMoeda($item['quantidade'] * $item['valorCusto']);

            $marca = '';
            $marca .= $item['MarcaNome'] ?  "\nMARCA: $item[MarcaNome];" : "";
            $marca .= $item['ModelNome'] ?  "\nMODELO: $item[ModelNome];" : "";
            $marca .= $item['FabriNome'] ?  "\nFABRICANTE: $item[FabriNome];" : "";

            $totalGeral += $item['quantidade'] * $item['valorCusto'];
            // var_dump($saldo);

            $output .=  "<tr class='trGrid' id='row$numItens'>
						 <td title='$item[detalhamento]$marca' data-popup='tooltip' style='text-align: center'>$numItens</td>
						 <td title='$item[detalhamento]$marca' data-popup='tooltip'>$item[nome]</td>
                         <td title='$item[detalhamento]$marca' data-popup='tooltip' style='text-align: center'>$item[UnMedSigla]</td>
                         <td title='$item[detalhamento]$marca' data-popup='tooltip' style='text-align: center'></td>
                         <td title='$item[detalhamento]$marca' data-popup='tooltip' style='text-align: center'>$saldo[Saldo]</td>
						 <td title='$item[detalhamento]$marca' data-popup='tooltip' style='text-align: right'>$valorCusto</td>
                         <td title='$item[detalhamento]$marca' data-popup='tooltip' class='valorTotal' style='text-align: right'>R$ 0, 00</td>
                         <td title='$item[detalhamento]$marca' data-popup='tooltip' style='text-align: center'></td>
                         <td style='text-align: center'><i idInput='campo$numItens' idRow='row$numItens' class='icon-file-check btn-acoes' style='cursor: pointer'></i></td>
                         <input type='hidden' tipo='$item[tipo]' id='campo$numItens' idLinha='row$numItens' quantInicial='$saldo[Quantidade]' saldoInicial='$saldo[Saldo]'  name='campo$numItens' value='$item[tipo]#$item[id]#$item[valorCusto]#0#0#0#0#0#0#$item[detalhamento]'>
                         <input id='MarcaNome' type='hidden' value='$item[MarcaNome]' />
                         <input id='ModelNome' type='hidden' value='$item[ModelNome]' />
                         <input id='FabriNome' type='hidden' value='$item[FabriNome]' />
					<tr>";
        }

        $output .= '</tbody>';

        $totalGeral = formataMoeda($totalGeral);

        $output .= '
            <tfoot>
                <tr>
                    <th colspan="6" style="text-align:right; font-size: 16px; font-weight:bold;">
                         <div>Total (R$) Nota Fiscal:</div>
                         <div>Saldo (R$) Ordem de Compra/Carta Contrato:</div>
                    </th>
                    <th colspan="1">
                        <div>
                            <div id="total" valorTotalGeral="" style="text-align:right; font-size: 15px; font-weight:bold;">R$ 0, 00</div>
                        </div>
                        <div>
                            <div id="totalSaldo" style="text-align:right; font-size: 15px; font-weight:bold;" valorTotalInicial="'.$totalOrdemCompra['valorTotalOrdemCompra'].'" valor="' . $totalOrdemCompra['valorTotalOrdemCompra'] . '">' . formataMoeda($totalOrdemCompra['valorTotalOrdemCompra']) . '</div>
                         </div>
                    </th>
                    <th colspan="2">
                    </th>
                </tr>
            </tfoot>
    ';

        $output .= '</table>';

        echo $output;
    } else {
        echo 0;
    }
}
