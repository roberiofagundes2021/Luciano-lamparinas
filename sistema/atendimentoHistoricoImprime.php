<?php

include_once("sessao.php");

include('global_assets/php/conexao.php');

use Mpdf\Mpdf;

require_once 'global_assets/php/vendor/autoload.php';

if (!isset($_POST['AtendimentoId'])){
  irpara("atendimentoHistoricoPaciente.php");
}

$iAtendimento = $_POST['AtendimentoId'];

$sql = "SELECT AtendId, AtendNumRegistro, UnidaNome, AtModNome, AtRecReceituario, AtSExJustificativa, AtAmbDataInicio, AtAmbHoraInicio, AtAmbHoraFim, AtAmbQueixaPrincipal, 
                AtAmbHistoriaMolestiaAtual, AtAmbHistoriaPatologicaPregressa, AtAmbExameFisico, AtAmbHipoteseDiagnostica, AtAmbDigitacaoLivre, 
                A.ProfiNome as ProfissionalNome, B.ProfiNome as ProfissaoNome, ProfiProfissao, AtEleDataInicio, AtEleHoraInicio, AtEleHoraFim, AtClaChave,
                ClienId, ClienCodigo, ClienNome, ClienSexo, ClienDtNascimento, ClienNomeMae, ClienCartaoSus, ClienCelular, ClResNome
        FROM Atendimento
        LEFT JOIN AtendimentoEletivo ON AtEleAtendimento = AtendId
        LEFT JOIN AtendimentoAmbulatorial ON AtAmbAtendimento = AtendId
        LEFT JOIN AtendimentoModalidade ON AtModId = AtendModalidade
        LEFT JOIN AtendimentoReceituario ON AtRecAtendimento = AtendId
        LEFT JOIN AtendimentoSolicitacaoExame ON AtSExAtendimento = AtendId
        LEFT JOIN AtendimentoClassificacao ON AtClaId = AtendClassificacao
        LEFT JOIN Profissional A ON A.ProfiId = AtSExProfissional
        LEFT JOIN Profissao B ON B.ProfiId = A.ProfiProfissao
        LEFT JOIN ClienteResponsavel on ClResCliente = AtendCliente
        JOIN Unidade ON UnidaId = AtendUnidade
        JOIN Cliente ON ClienId = AtendCliente
        WHERE AtendId = '". $iAtendimento."' and AtendUnidade = ".$_SESSION['UnidadeId']; 
$result = $conn->query($sql);
$row = $result->fetch(PDO::FETCH_ASSOC);
$count = count($row);

//Essa consulta é para preencher o sexo
if ($row['ClienSexo'] == 'F'){
    $sexo = 'Feminino';
} else{
    $sexo = 'Masculino';
}

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

$mpdf->SetDisplayMode('fullpage','two'); //'fullpage': Ajustar uma página inteira na tela, 'fullwidth': Ajustar a largura da página na tela, 'real': Exibir em tamanho real, 'default': Configuração padrão do usuário no Adobe Reader, 'none'

/*$mpdf = new Mpdf([
    'mode' => 'utf-8',
    //'format' => [190, 236], 
    'format' => 'A4-P', //A4-L
    'default_font_size' => 9,
    'default_font' => 'dejavusans',
    'orientation' => 'P' //P->Portrait (retrato)    L->Landscape (paisagem)
]);*/


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
    
    <div style='position: relative; width:100%; border-bottom: 1px solid #000;'>
        <div style='width:450px; float:left; display: inline;'>
            <img src='global_assets/images/empresas/".$_SESSION['EmpreFoto']."' style='width:60px; height:60px; float:left; margin-right: 10px; margin-top:-10px;' alt='Logo Empresa' />
            <span style='font-weight:bold;line-height:200px;'>" . $_SESSION['EmpreNomeFantasia'] . "</span><br>
            <div style='position: absolute; font-size:12px; margin-top: 8px; margin-left:4px;'>Unidade: ".$_SESSION['UnidadeNome']."</div>
        </div>
        <div style='width:150px; float:right; display: inline; text-align:right;'>
            <div>Data: ".date('d/m/Y')."</div>
            <div style='margin-top:8px;'>Hora: ". date('H:i:s') ."</div>
        </div> 
    </div>	 

    <div style='text-align:center; margin-top: 10px;'><h1>Dados do Paciente</h1></div>
";


$html .= '
    <table style="width:100%; border-collapse: collapse;"> 
        <tr>
            <td style="width:25%; font-size:12px;">Prontuário Eletrônico:<br>'.$row['ClienCodigo'].'</td>	
            <td style="width:25%; font-size:12px;">Nº do Registro:<br>'.$row['AtendNumRegistro'].'</td>
            <td style="width:25%; font-size:12px;">Modalidade:<br>'.$row['AtModNome'].'</td>
            <td style="width:25%; font-size:12px;">CNS:<br>'.$row['ClienCartaoSus'].'</td>
        </tr>
    </table>
    <table style="width:100%; border-collapse: collapse;"> 
        <tr>
            <td style="width:50%; font-size:14px; background-color:#F1F1F1;">Paciente:<br>'.$row['ClienNome'].'</td>	
            <td style="width:25%; font-size:12px;">Sexo:<br>'.$sexo.'</td>
            <td style="width:25%; font-size:12px;">Telefone:<br>'.$row['ClienCelular'].'</td>
        </tr>
    </table>
    <table style="width:100%; border-collapse: collapse;">
        <tr >
            <td style="width:25%; font-size:12px;">Data Nascimento:<br>'.mostraData($row['ClienDtNascimento']).'</td>
            <td style="width:25%; font-size:12px;">Idade:<br>'.calculaIdade($row['ClienDtNascimento']).'</td>	
            <td style="width:25%; font-size:12px;">Mãe:<br>'.$row['ClienNomeMae'].'</td>
            <td style="width:25%; font-size:12px;">Responsável:<br>'.$row['ClResNome'].'</td>
        </tr>
    </table>
';
if($row['AtClaChave'] == "AMBULATORIAL"){
    $html .= '
        <div style="text-align:center; margin-top: 10px;"><h1>Histórico do Paciente</h1></div>
        <div style=" border: #aaa solid 1px; text-align: center; font-weight: bold; position:relative; margin-top: 5px; background-color:#eee; padding: 10px;">
            DATA DO ATENDIMENTO AMBULATORIAL
            <br><br>
            <span style=" text-align: center; color:#aaa;">&nbsp;&nbsp;|&nbsp;&nbsp;</span>Entrada: <span style="font-weight:normal;">' .mostraData($row['AtAmbDataInicio']).' - '.mostraHora($row['AtAmbHoraInicio']).'</span><span style="color:#aaa;">&nbsp;&nbsp;|&nbsp;&nbsp;</span><span style="color:#aaa;">&nbsp;&nbsp;|&nbsp;&nbsp;</span>Saída: <span style="font-weight:normal;">' .mostraData($row['AtAmbDataInicio']).' - '.mostraHora($row['AtAmbHoraFim']).'</span><span style="color:#aaa;">&nbsp;&nbsp;|&nbsp;&nbsp;</span>
        </div>
        <div style=" border: #aaa solid 1px; font-weight: bold; position:relative;  background-color:#eee; padding: 10px;">
            Unidade de Atendimento: <span style="font-weight:normal;">'. $row['UnidaNome'] .'</span> <span style="color:#aaa;"></span><br>Médico Solicitante: <span style="font-weight:normal;">'.$row['ProfissionalNome'].' ('.$row['ProfissaoNome'].')</span> <span style="color:#aaa;">&nbsp;&nbsp;|&nbsp;&nbsp;</span> Modalidade: <span style="font-weight:normal;">'. $row['AtModNome'] .'</span><span style="color:#aaa;">&nbsp;&nbsp;|&nbsp;&nbsp;</span> Guia: <span style="font-weight:normal;">'.$row['AtendNumRegistro'].'</span>
        </div>
        <br>
    ';

    $html .= '
        <div style="text-align:center;font-weight: bold; position:relative; margin-top: 10px; background-color:#ccc; padding: 5px;">
            HISTÓRICO DO ATENDIMENTO
        </div>
        <table style="width:100%; border-collapse: collapse;"> 
            <tr>
                <td style="width:20%; font-weight: bold;">Queixa Principal (QP)</td>	
                <td style="width:80%; font-size:12px;">'.$row['AtAmbQueixaPrincipal'].'</td>
            </tr>
        </table>
        <table style="width:100%; border-collapse: collapse;"> 
            <tr>
                <td style="width:20%; font-weight: bold;">História da Moléstia Atual (HMA)</td>
                <td style="width:80%; font-size:12px;">'.$row['AtAmbHistoriaMolestiaAtual'].'</td>	
            </tr>
        </table>
        <table style="width:100%; border-collapse: collapse;">
            <tr>
                <td style="width:20%; font-weight: bold;">Patologica Pregressa</td>
                <td style="width:80%; font-size:12px;">'.$row['AtAmbHistoriaPatologicaPregressa'].'</td>	
            </tr>
        </table>
        <table style="width:100%; border-collapse: collapse;"> 
            <tr>
                <td style="width:20%; font-weight: bold;">Exame Fisico</td>	
                <td style="width:80%; font-size:12px;">'.$row['AtAmbExameFisico'].'</td>
            </tr>
        </table>
        <table style="width:100%; border-collapse: collapse;"> 
            <tr>
                <td style="width:20%; font-weight: bold;">Hipotese Diagnostica</td>
                <td style="width:80%; font-size:12px;">'.$row['AtAmbHipoteseDiagnostica'].'</td>	
            </tr>
        </table>
        <table style="width:100%; border-collapse: collapse;"> 
            <tr>
                <td style="width:20%; font-weight: bold;">Digitacao Livre</td>	
                <td style="width:80%; font-size:12px;">'.$row['AtAmbDigitacaoLivre'].'</td>
            </tr>
        </table>
        <br>
        <table style="width:100%; border-collapse: collapse;"> 
            <tr>
                <td style="width:20%; font-weight: bold;">Receituário</td>
                <td style="width:80%; font-size:12px;">'.$row['AtRecReceituario'].'</td>	
            </tr>
        </table>
        <table style="width:100%; border-collapse: collapse;">
            <tr>
                <td style="width:20%; font-weight: bold;">Solicitação de Procedimento</td>
                <td style="width:80%; font-size:12px;">'.$row['AtSExJustificativa'].'</td>	
            
            </tr>
        </table>
    ';
}  else if  ($row['AtClaChave'] == "ELETIVO"){

    $html .= '
        <div style="text-align:center; margin-top: 10px;"><h1>Histórico do Paciente</h1></div>
    
        <div style=" border: #aaa solid 1px; text-align: center; font-weight: bold; position:relative; margin-top: 5px; background-color:#eee; padding: 10px;">
        DATA DO ATENDIMENTO ELETIVO
        <br><br>
        <span style=" text-align: center; color:#aaa;">&nbsp;&nbsp;|&nbsp;&nbsp;</span>Entrada: <span style="font-weight:normal;">' .mostraData($row['AtEleDataInicio']).' - '.mostraHora($row['AtEleHoraInicio']).'</span><span style="color:#aaa;">&nbsp;&nbsp;|&nbsp;&nbsp;</span><span style="color:#aaa;">&nbsp;&nbsp;|&nbsp;&nbsp;</span>Saída: <span style="font-weight:normal;">' .mostraData($row['AtEleDataInicio']).' - '.mostraHora($row['AtEleHoraFim']).'</span><span style="color:#aaa;">&nbsp;&nbsp;|&nbsp;&nbsp;</span>
        </div>
        <div style="  border: #aaa solid 1px; font-weight: bold; position:relative; background-color:#eee; padding: 10px;">
        Unidade de Atendimento: <span style="font-weight:normal;">'. $row['UnidaNome'] .'</span> <span style="color:#aaa;"></span><br>Médico Solicitante: <span style="font-weight:normal;">'.$row['ProfissionalNome'].' ('.$row['ProfissaoNome'].')</span> <span style="color:#aaa;">&nbsp;&nbsp;|&nbsp;&nbsp;</span> Modalidade: <span style="font-weight:normal;">'. $row['AtModNome'] .'</span><span style="color:#aaa;">&nbsp;&nbsp;|&nbsp;&nbsp;</span> Guia: <span style="font-weight:normal;">'.$row['AtendNumRegistro'].'</span>
        </div>
        <br>
    ';

    $html .= '
        <div style="text-align:center;font-weight: bold; position:relative; margin-top: 10px; background-color:#ccc; padding: 5px;">
            HISTÓRICO DO ATENDIMENTO
        </div>

        <table style="width:100%; border-collapse: collapse;"> 
            <tr>
                <td style="width:20%; font-weight: bold;">Receituário</td>
                <td style="width:80%; font-size:12px;">'.$row['AtRecReceituario'].'</td>	
            </tr>
        </table>
        <table style="width:100%; border-collapse: collapse;">
            <tr >
                <td style="width:20%; font-weight: bold;">Solicitação de Procedimento</td>
                <td style="width:80%; font-size:12px;">'.$row['AtSExJustificativa'].'</td>	
            
            </tr>
        </table>
    ';

}  else {

            
}

$rodape = "<hr/>
<div style='width:100%'>
    <div style='width:300px; float:left; display: inline;'>Sistema Lamparinas</div>
    <div style='width:105px; float:right; display: inline;'>Página {PAGENO} / {nbpg}</div> 
</div>";

//ATENÇÃO: Tive que colocar o cabeçalho dentro do HTML, para o cabeçalho não sobrescrever o conteúdo HTML a partir da segunda página. Em compensação o cabeçalho só aparece na primeira página. Foi a única forma que encontrei. Tentei de tudo...

//$mpdf->SetHTMLHeader($topo);	//o SetHTMLHeader deve vir antes do WriteHTML para que o cabeçalho apareça em todas as páginas
$mpdf->SetHTMLFooter($rodape); 	//o SetHTMLFooter deve vir antes do WriteHTML para que o rodapé apareça em todas as páginas
$mpdf->WriteHTML($html);

// Other code
$mpdf->Output();
