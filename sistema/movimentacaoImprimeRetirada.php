<?php

include_once("sessao.php");

include('global_assets/php/conexao.php');

use Mpdf\Mpdf;

require_once 'global_assets/php/vendor/autoload.php';
require_once 'global_assets/php/funcoesgerais.php';

if (isset($_POST['inputMovimentacaoId'])) {

    $iMovimentacao = $_POST['inputMovimentacaoId'];

    $sql = "SELECT MovimTipo, MovimData, MovimNumRecibo, MovimObservacao, ParamValorObsImpreRetirada, MotivNome
            FROM Movimentacao
            JOIN Unidade on UnidaId = MovimUnidade
            JOIN Parametro on ParamEmpresa = UnidaEmpresa
            LEFT JOIN Motivo on MotivId = MovimMotivo
            WHERE MovimUnidade = " . $_SESSION['UnidadeId'] . " and MovimId = ". $iMovimentacao;
    $result = $conn->query($sql);
    $row = $result->fetch(PDO::FETCH_ASSOC);

    // Após concluido a tela de Movimentação tem que avaliar se precisa desse Distinct,
    // porque não deve ser criado vários registros na tabela MovimentacaoXProduto pra esse caso
    // de produtos não patrimoniados.
    $sql = "SELECT Distinct MvXPrProduto as ProduServi, MvXPrQuantidade as Quantidade, MvXPrLote, isnull(cast(cast(MvXPrValidade as date)as varchar),'') as Validade, ClassNome, ClassChave, ProduNome as Nome, 
            ProduCodigo as Codigo, ProduUnidadeMedida, CategNome as Categoria, UnMedSigla, ModelNome as NomeModelo, MarcaNome as Marca, Tipo = 'P'
	        FROM Movimentacao
	        JOIN MovimentacaoXProduto on MvXPrMovimentacao = MovimId
	        JOIN Produto on ProduId = MvXPrProduto
	        JOIN Categoria on CategId = ProduCategoria
	        LEFT JOIN Classificacao on ClassId = MvXPrClassificacao
	        LEFT JOIN UnidadeMedida on UnMedId = ProduUnidadeMedida
            LEFT JOIN OrdemCompra on OrComId = MovimOrdemCompra
            LEFT JOIN FluxoOperacional on FlOpeId = OrComFluxoOperacional
            LEFT JOIN ProdutoXFabricante on PrXFaFluxoOperacional = FlOpeId
            LEFT JOIN Marca on MarcaId = PrXFaMarca
	        LEFT JOIN Modelo on ModelId = PrXFaModelo
	        WHERE MovimUnidade = " . $_SESSION['UnidadeId'] . " and MovimId = " . $iMovimentacao. " and ClassChave <> 'PERMANENTE' 
            UNION
            SELECT Distinct MvXSrServico as ProduServi, MvXSrQuantidade as Quantidade, '' , '' , '', '' , ServiNome as Nome, ServiCodigo as Codigo,
            '' , CategNome as Categoria, '', ModelNome as NomeModelo, MarcaNome as Marca, Tipo = 'S'
            FROM Movimentacao
            JOIN MovimentacaoXServico on MvXSrMovimentacao = MovimId
            JOIN Servico on ServiId = MvXSrServico
            JOIN Categoria on CategId = ServiCategoria
            LEFT JOIN OrdemCompra on OrComId = MovimOrdemCompra
            LEFT JOIN FluxoOperacional on FlOpeId = OrComFluxoOperacional
            LEFT JOIN ServicoXFabricante on SrXFaFluxoOperacional = FlOpeId
            LEFT JOIN Marca on MarcaId = SrXFaMarca
	        LEFT JOIN Modelo on ModelId = SrXFaModelo
            WHERE MovimUnidade = " . $_SESSION['UnidadeId'] . " and MovimId = " . $iMovimentacao. "
            ";
            $result = $conn->query($sql);
            $rowMvPrNaoPatrimoniado = $result->fetchAll(PDO::FETCH_ASSOC);

    $sql = "SELECT MvXPrProduto, MvXPrQuantidade, MvXPrLote, isnull(cast(cast(MvXPrValidade as date)as varchar),'') as Validade, ClassNome, ClassChave, ProduNome, ProduMarca, 
            ProduModelo, ProduCodigo, ProduUnidadeMedida, ProduModelo, CategNome, UnMedSigla, ModelNome, MarcaNome, PatriNumero
	        FROM Movimentacao
	        JOIN MovimentacaoXProduto on MvXPrMovimentacao = MovimId
	        JOIN Produto on ProduId = MvXPrProduto
	        JOIN Categoria on CategId = ProduCategoria
	        LEFT JOIN Classificacao on ClassId = MvXPrClassificacao
	        JOIN UnidadeMedida on UnMedId = ProduUnidadeMedida
	        LEFT JOIN Modelo on ModelId = ProduModelo
            LEFT JOIN Marca on MarcaId = ProduMarca
            --JOIN MovimentacaoXProdutoXPatrimonio on MXPXPMovimentacaoXProduto = MvXPrId
            JOIN Patrimonio on PatriId = MvXPrPatrimonio
	        WHERE MovimUnidade = " . $_SESSION['UnidadeId'] . " and MovimId = " . $iMovimentacao. " and ClassChave = 'PERMANENTE' ";
    $result = $conn->query($sql);
    $rowMvPrPatrimoniado = $result->fetchAll(PDO::FETCH_ASSOC);    

    if ($row['MovimTipo'] == 'S'){
        
        $sql = "SELECT LcEstNome as Origem, SetorNome as Destino
                FROM Movimentacao
                JOIN LocalEstoque on LcEstId = MovimOrigemLocal
                JOIN Setor on SetorId = MovimDestinoSetor
                WHERE MovimUnidade = " . $_SESSION['UnidadeId'] . " and MovimId = " . $iMovimentacao;

    } else if ($row['MovimTipo'] == 'T'){
        
        $sql = "SELECT CASE
                         WHEN MovimOrigemLocal IS NULL THEN StO.SetorNome
                         ELSE LcO.LcEstNome
                       END AS Origem, 
                       CASE
                         WHEN MovimDestinoLocal IS NULL THEN isnull(StD.SetorNome, MovimDestinoManual)
                         ELSE LcD.LcEstNome
                       END AS Destino, 
                       MotivChave
                FROM Movimentacao
                LEFT JOIN LocalEstoque LcO on LcO.LcEstId = MovimOrigemLocal
                LEFT JOIN LocalEstoque LcD on LcD.LcEstId = MovimDestinoLocal                
                LEFT JOIN Setor StO on StO.SetorId = MovimOrigemSetor
                LEFT JOIN Setor StD on StD.SetorId = MovimDestinoSetor
                JOIN Motivo on MotivId = MovimMotivo
                WHERE MovimUnidade = " . $_SESSION['UnidadeId'] . " and MovimId = " . $iMovimentacao;
    }
    $result = $conn->query($sql);
    $rowMv = $result->fetch(PDO::FETCH_ASSOC);

    $Origem = $rowMv['Origem'];
    $Destino = $rowMv['Destino'];
    //$Motivo = $rowMv['MotivChave']; 
}

try {
	$mpdf = new mPDF([
	             'mode' => 'utf-8',    // mode - default ''
	             'format' => 'A4-P',    // format - A4, for example, default ''
	             'default_font_size' => 9,     // font size - default 0
	             'default_font' => '',    // default font family
	             'margin-left' => 15,    // margin_left
	             'margin-right' => 15,    // margin right
	             'margin-top' => 158,     // margin top    -- aumentei aqui para que não ficasse em cima do header
	             'margin-bottom' => 60,    // margin bottom
	             'margin-header' => 6,     // margin header
	             'margin-bottom' => 0,     // margin footer
	             'orientation' => 'P']);  // L - landscape, P - portrait	

    // Evita erro ao recarregar pagina do relatório
    if (!isset($rowMv)) {
        return;
    }

    $titulo = $row['MovimTipo'] == 'S' ? 'RECIBO DE RETIRADA' : ($row['MovimTipo'] == 'T'?'RECIBO DE TRANSFERÊNCIA':'');

    $html = "
    <style>
        th{
            text-align: center; 
            border: #bbb solid 1px; 
            background-color: #f8f8f8; 
            padding: 8px;
        }

        td{
            padding: 8px;               
            border: #bbb solid 1px;
        }
    </style>


    <div style='position: relative; width:100%; border-bottom: 1px solid #666;'>
        <div style='float:left; width: 400px; display: inline-block;'>
            <img src='global_assets/images/empresas/".$_SESSION['EmpreFoto']."' style='width:60px; height:60px; float:left; margin-right: 10px; margin-top:-10px;' />     
            <span style='font-weight:bold;line-height:200px;'>" . $_SESSION['EmpreNomeFantasia'] . "</span><br>
            <div style='position: absolute; font-size:12px; margin-top: 8px; margin-left:4px;'>Unidade: ".$_SESSION['UnidadeNome']."</div>
            </div>
        <div style='width:250px; float:right; display: inline; text-align:right;'>
            <div>".date('d/m/Y')."</div>
            <div style='margin-top:8px; font-weight:bold;'>Recibo de Retirada</div>
        </div> 
    </div>

    <div style='text-align:center; margin-top: 20px;'><h1>$titulo</h1></div>
    ";

    //"Bens não patrimoniados" tem quantidade e não tem patrimônio, já os "Bens patrimoniados" não tem quantidade e tem patrimônio
    $html .= "<br>
    <table style='width:100%;'>
        <tr>                                
            <td style='width:25%'>Data: " . mostraData($row['MovimData']) . "</td>
            <td style='width:25%; text-align: center; background-color: #d8d8d8;'>Nº: $row[MovimNumRecibo]</td>
            <td style='width:50%'>Motivo: $row[MotivNome]</td>
        </tr>
        <tr>
            <td colspan='2' style='width:50%'>Origem:<br>$Origem</td>
            <td colspan='2' style='width:50%'>Destino:<br>$Destino</td>
        </tr>";

    if ($row['ParamValorObsImpreRetirada'] == 1) {

        $html .= ' 
            <tr>
                <td colspan="4">Observação: '.$row['MovimObservacao'].'</td>
                </tr>
            ';
    }

    $html .= '</table>
    <br>';    

    //Listando os Bens Não Patrimoniados
    include("movimentacaoImprimeRetiradaNaoPatrimoniado.php");

    //Listando os Bens Patrimoniados
    include("movimentacaoImprimeRetiradaPatrimoniado.php");

    $html .= '<div style="margin-top: 8px ;">
                <div style="margin-right: 2px ;float: left; width: 49%; border: 1px solid #c9d0d4">
                    <div style="float: left; margin-left: 29px; margin-top: 29px; margin-bottom: -20px; width: 260px; height: 100px;">
                        <div style="width= 100%; height: 25px; border-bottom: 1px solid rgb(187, 186, 186);">
                    
                        </div>
                        <div style="">
                            <p style="font-size: 0.9rem; margin: 7px 0 0 0; text-align: center;">Solicitante</p>
                            <p style="color: rgb(156, 154, 154); font-size: 0.8rem; margin: 7px 0 0 0; text-align: center;">Assinatura (funcionário)</p>
                        </div>
                    </div>
                </div>
                <div style="margin-left: 2px ;float: left; width: 50%; border: 1px solid #c9d0d4">
                    <div style="float: left; margin-left: 29px; margin-top: 29px; margin-bottom: -20px; width: 260px; height: 100px;">
                        <div style="width= 100%; height: 25px; border-bottom: 1px solid rgb(187, 186, 186);">
                            
                        </div>
                        <div style="">
                            <p style="font-size: 0.9rem; margin: 7px 0 0 0; text-align: center;">Solicitado</p>
                            <p style="color: rgb(156, 154, 154); font-size: 0.8rem; margin: 7px 0 0 0; text-align: center;">Assinatura (resp. pelo setor)</p>
                        </div>
                    </div>
                </div>
            </div>';

        $rodape = "<hr/>
        <div style='width:100%'>
        <div style='width:300px; float:left; display: inline;'>Sistema Lamparinas</div>
        <div style='width:105px; float:right; display: inline;'>Página {PAGENO} / {nbpg}</div> 
        </div>";			

        //$mpdf->SetHTMLHeader($topo);
        //$stylesheet = file_get_contents('global_assets/css/lamparinas/bootstrap-3.3.7/dist/css/bootstrap.min.css');
        //$stylesheet = file_get_contents('global_assets/css/lamparinas/impressao.css');         
        //$mpdf->WriteHTML($stylesheet, 1); // CSS Script goes here.
        $mpdf->SetHTMLFooter($rodape);
        $mpdf->WriteHTML($html);            
        // $mpdf->SetHTMLHeader($topo,'O',true);	    

        $mpdf->Output();

} catch (\Mpdf\MpdfException $e) { // Note: safer fully qualified exception name used for catch

    // Process the exception, log, print etc.
    $html = $e->getMessage();
	
    $mpdf->WriteHTML($html);
    
    // Other code
    $mpdf->Output();	
}
