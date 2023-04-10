<?php

include_once("sessao.php");
include('global_assets/php/conexao.php');

function queryPesquisa()
{
    $cont = 0;

    include('global_assets/php/conexao.php');

    $args = [];

    empty($_POST['inputDataDe']) ? $inputDataDe = '' : $inputDataDe = " FlOpeDataInicio > '".$_POST['inputDataDe']."'";
    $args[]  = $inputDataDe;

    empty($_POST['inputDataAte']) ? $inputDataAte = '' : $inputDataAte = " dbo.fnFimContrato(FlOpeId) < '". $_POST['inputDataAte']."'";
    $args[]  = $inputDataAte;

    if (!empty($_POST['cmbUnidade']) && $_POST['cmbUnidade'] != "") {
        $args[]  = "FlOpeUnidade = " . $_POST['cmbUnidade'] . " ";
    } else{
        $args[]  = "FlOpeUnidade = " . $_SESSION['UnidadeId'] . " ";
    }

    if (!empty($_POST['cmbEmpresaContratada']) && $_POST['cmbEmpresaContratada'] != "") {
        $args[]  = "FlOpeFornecedor = " . $_POST['cmbEmpresaContratada'] . " ";
    }

    if (!empty($_POST['cmbCategoria']) && $_POST['cmbCategoria'] != "") {
        $args[]  = "FlOpeCategoria = " . $_POST['cmbCategoria'] . " ";
    }

    // if(!empty($_POST['cmbClassificacao'])){
    //     $args[]  = "FlOpe = ".$_POST['inputSubCategoria']." ";
    // }

    if (!empty($_POST['cmbModalidade']) && $_POST['cmbModalidade'] != "") {
        $args[]  = "FlOpeModalidadeLicitacao = " . $_POST['cmbModalidade'] . " ";
    }

    if (!empty($_POST['cmbPrioridade']) && $_POST['cmbPrioridade'] != "") {
        $args[]  = "FlOpePrioridade = " . $_POST['cmbPrioridade'] . " ";
    }

    if (!empty($_POST['cmbStatus']) && $_POST['cmbStatus'] != "") {
        $args[]  = "FlOpeStatus = " . $_POST['cmbStatus'] . " ";
    }

    if (count($args) >= 1) {
        try {

            $string = implode(" and ", $args);

            // if ($string != ''){
            //     $string .= ' and ';
            // }

            $sql = "SELECT FlOpeId,  FlOpeDataInicio,  FlOpeDataFim,  FlOpeObservacao, FlOpePrioridade, UnidaNome, ForneRazaoSocial, CategNome, MdLicNome, PriorNome, SituaNome
                    FROM FluxoOperacional
                    JOIN Unidade on UnidaId = FlOpeUnidade
                    JOIN Fornecedor on ForneId = FlOpeFornecedor
                    JOIN Categoria  on CategId = FlOpeCategoria
                    LEFT JOIN ModalidadeLicitacao on MdLicId = FlOpeModalidadeLicitacao
                    LEFT JOIN Prioridade on PriorId = FlOpePrioridade 
                    JOIN Situacao on SituaId = FlOpeStatus
                    WHERE " . $string . "
                    ";
            $result = $conn->query($sql);
            $rowData = $result->fetchAll(PDO::FETCH_ASSOC);

            count($rowData) >= 1 ? $cont = 1 : $cont = 0;

        } catch (PDOException $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }
    if ($cont == 1) {
        $cont = 0;
        foreach ($rowData as $item) {

            $cont++;

            $sql = "SELECT MAX(AditiId) AditiId
                        FROM FluxoOperacional
                        JOIN Aditivo on AditiFluxoOperacional = FlOpeId
                        WHERE FlOpeId = " . $item['FlOpeId'] . "
            ";
            $result = $conn->query($sql);
            $rowUltimoAditivo = $result->fetch(PDO::FETCH_ASSOC);


            if ($rowUltimoAditivo['AditiId']) {

                $sql = "SELECT AditiDtInicio, AditiDtFim
                            FROM Aditivo
                            WHERE AditiId = " . $rowUltimoAditivo['AditiId'] . "
                ";
                $result = $conn->query($sql);
                $rowDataUltimoAditivo = $result->fetch(PDO::FETCH_ASSOC);

                print("
                    
                    <tr idFluxoOperacional=" . $item['FlOpeId'] . " editado='0'>
                       <td class='even'>" . $cont . "</td>
                       <td class='odd'>" . $item['CategNome'] . "</td>
                       <td class='odd'>" . $item['ForneRazaoSocial'] . "</td>
                       <td class='even'>" . $item['UnidaNome'] . "</td>
                       <td class='odd'>" . $item['SituaNome'] . "</td>
                       <td class='even'>" . $item['MdLicNome'] . "</td>
                       <td class='odd'>" . mostraData($rowDataUltimoAditivo['AditiDtInicio']) . "</td>
                       <td class='even'>" . mostraData($rowDataUltimoAditivo['AditiDtFim']) . "</td>
                       <td class='odd'>" . $item['PriorNome'] . "</td>
                       <td  class='odd' style='text-align: center'>
                             <i idinput='campo3' idrow='row3' class='icon-pencil7 btn-acoes' style='cursor: pointer'></i>
                       </td>
                       <td style='display: none'>
                            <input type='text' value='" . $item['FlOpePrioridade'] . "'>
                       </td>
                       <td style='display: none'>
                            <input type='text' value='" . $item['FlOpeObservacao'] . "'>
                       </td>
                    </tr>
                 ");
            } else {

                print("
                    
                    <tr idFluxoOperacional=" . $item['FlOpeId'] . " editado='0'>
                       <td class='even'>" . $cont . "</td>
                       <td class='odd'>" . $item['CategNome'] . "</td>
                       <td class='odd'>" . $item['ForneRazaoSocial'] . "</td>
                       <td class='even'>" . $item['UnidaNome'] . "</td>
                       <td class='odd'>" . $item['SituaNome'] . "</td>
                       <td class='even'>" . $item['MdLicNome'] . "</td>
                       <td class='odd'>" . mostraData($item['FlOpeDataInicio']) . "</td>
                       <td class='even'>" . mostraData($item['FlOpeDataFim']) . "</td>
                       <td class='odd'>" . $item['PriorNome'] . "</td>
                       <td  class='odd' style='text-align: center'>
                             <i idinput='campo3' idrow='row3' class='icon-pencil7 btn-acoes' style='cursor: pointer'></i>
                       </td>
                       <td style='display: none'>
                            <input type='text' value='" . $item['FlOpePrioridade'] . "'>
                       </td>
                       <td style='display: none'>
                            <input type='text' value='" . $item['FlOpeObservacao'] . "'>
                       </td>
                    </tr>
                 ");
            }
        }
    }
}

queryPesquisa();
