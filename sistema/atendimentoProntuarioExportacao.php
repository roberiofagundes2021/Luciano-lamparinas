<?php

include_once("sessao.php");

include('global_assets/php/conexao.php');

use Mpdf\Mpdf;

require_once 'global_assets/php/vendor/autoload.php';

$iAtendimento = isset($_POST['iAtendimentoId'])?$_POST['iAtendimentoId']:null;

$sql = "SELECT AtendId, AtendNumRegistro, UnidaNome, AtModNome, ClienId, ClienCodigo, ClienNome,
               ClienSexo, ClienDtNascimento, ClienNomeMae, ClienCartaoSus, ClienCelular, ClResNome
            FROM Atendimento
            LEFT JOIN AtendimentoModalidade ON AtModId = AtendModalidade
            LEFT JOIN ClienteResponsavel on ClResCliente = AtendCliente
            JOIN Unidade ON UnidaId = AtendUnidade
            JOIN Cliente ON ClienId = AtendCliente
			WHERE AtendId = '". $iAtendimento."' and AtendUnidade = ".$_SESSION['UnidadeId']; 
	$result = $conn->query($sql);
	$rowPaciente = $result->fetch(PDO::FETCH_ASSOC);

    $iPaciente = $rowPaciente['ClienId'];

     //Essa consulta é para preencher o sexo
    if ($rowPaciente['ClienSexo'] == 'F'){
        $sexo = 'Feminino';
    } else{
        $sexo = 'Masculino';
    }

    $sql = "SELECT AtendId, AtendNumRegistro, AtendDataRegistro, UnidaNome, AtModNome, AtRecReceituario, AtSExJustificativa, AtAmbDataInicio, AtAmbHoraInicio, AtAmbHoraFim, 
                   AtAmbQueixaPrincipal, AtAmbHistoriaMolestiaAtual,AtAmbExameFisico, AtAmbHistoriaPatologicaPregressa, AtAmbHipoteseDiagnostica, AtAmbDigitacaoLivre, 
                   AtAmbOutrasObservacoes, A.ProfiNome as ProfissionalNome, B.ProfiNome as ProfissaoNome, ProfiProfissao, AtEleDataInicio, AtEleHoraInicio, 
                   AtEleHoraFim, AtClaChave, ClienId, ClienCodigo, ClienNome, ClienSexo, ClienDtNascimento, ClienNomeMae, ClienCartaoSus, 
                   ClienCelular, ClResNome
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
			WHERE AtendCliente = '". $iPaciente."' and AtendUnidade = ".$_SESSION['UnidadeId']."
            ORDER BY AtendDataRegistro, AtendId DESC"; 
	$result = $conn->query($sql);
	$row = $result->fetchAll(PDO::FETCH_ASSOC);
	$count = count($row);

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
                <td style="width:25%; font-size:12px;">Prontuário Eletrônico:<br>'.$rowPaciente['ClienCodigo'].'</td>	
                <td style="width:25%; font-size:12px;">Nº do Registro:<br>'.$rowPaciente['AtendNumRegistro'].'</td>
                <td style="width:25%; font-size:12px;">Modalidade:<br>'.$rowPaciente['AtModNome'].'</td>
                <td style="width:25%; font-size:12px;">CNS:<br>'.$rowPaciente['ClienCartaoSus'].'</td>
            </tr>
        </table>
        <table style="width:100%; border-collapse: collapse;"> 
            <tr>
                <td style="width:50%; font-size:14px; background-color:#F1F1F1;">Paciente:<br>'.$rowPaciente['ClienNome'].'</td>	
                <td style="width:25%; font-size:12px;">Sexo:<br>'.$sexo.'</td>
                <td style="width:25%; font-size:12px;">Telefone:<br>'.$rowPaciente['ClienCelular'].'</td>
            </tr>
        </table>
        <table style="width:100%; border-collapse: collapse;">
            <tr >
                <td style="width:25%; font-size:12px;">Data Nascimento:<br>'.mostraData($rowPaciente['ClienDtNascimento']).'</td>
                <td style="width:25%; font-size:12px;">Idade:<br>'.calculaIdade($rowPaciente['ClienDtNascimento']).'</td>	
                <td style="width:25%; font-size:12px;">Mãe:<br>'.$rowPaciente['ClienNomeMae'].'</td>
                <td style="width:25%; font-size:12px;">Responsável:<br>'.$rowPaciente['ClResNome'].'</td>
            </tr>
        </table>


        <div style="text-align:center; margin-top: 10px;"><h1>Histórico do Paciente</h1></div>

    ';
    
    foreach ($row as $item){
        if($item['AtClaChave'] == "AMBULATORIAL"){
            $html .= '
                
                <div style=" border: #aaa solid 1px; text-align: center; font-weight: bold; position:relative; margin-top: 5px; background-color:#eee; padding: 10px;">
                    DATA DO ATENDIMENTO AMBULATORIAL
                    <br><br>
                    <span style=" text-align: center; color:#aaa;">&nbsp;&nbsp;|&nbsp;&nbsp;</span>Entrada: <span style="font-weight:normal;">' .mostraData($item['AtAmbDataInicio']).' - '.mostraHora($item['AtAmbHoraInicio']).'</span><span style="color:#aaa;">&nbsp;&nbsp;|&nbsp;&nbsp;</span><span style="color:#aaa;">&nbsp;&nbsp;|&nbsp;&nbsp;</span>Saída: <span style="font-weight:normal;">' .mostraData($item['AtAmbDataInicio']).' - '.mostraHora($item['AtAmbHoraFim']).'</span><span style="color:#aaa;">&nbsp;&nbsp;|&nbsp;&nbsp;</span>
                </div>
                <div style=" border: #aaa solid 1px; font-weight: bold; position:relative;  background-color:#eee; padding: 10px;">
                    Unidade de Atendimento: <span style="font-weight:normal;">'. $item['UnidaNome'] .'</span> <span style="color:#aaa;"></span><br>Médico Solicitante: <span style="font-weight:normal;">'.$item['ProfissionalNome'].' ('.$item['ProfissaoNome'].')</span> <span style="color:#aaa;">&nbsp;&nbsp;|&nbsp;&nbsp;</span> Modalidade: <span style="font-weight:normal;">'. $item['AtModNome'] .'</span><span style="color:#aaa;">&nbsp;&nbsp;|&nbsp;&nbsp;</span> Guia: <span style="font-weight:normal;">'.$item['AtendNumRegistro'].'</span>
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
                        <td style="width:80%; font-size:12px;">'.$item['AtAmbQueixaPrincipal'].'</td>
                    </tr>
                </table>
                <table style="width:100%; border-collapse: collapse;"> 
                    <tr>
                        <td style="width:20%; font-weight: bold;">História da Moléstia Atual (HMA)</td>
                        <td style="width:80%; font-size:12px;">'.$item['AtAmbHistoriaMolestiaAtual'].'</td>	
                    </tr>
                </table>
                <table style="width:100%; border-collapse: collapse;"> 
                    <tr>
                        <td style="width:20%; font-weight: bold;">Patologica Pregressa</td>	
                        <td style="width:80%; font-size:12px;">'.$item['AtAmbHistoriaPatologicaPregressa'].'</td>
                    </tr>
                </table>
                <table style="width:100%; border-collapse: collapse;">
                    <tr >
                        <td style="width:20%; font-weight: bold;">Exame Físico</td>
                        <td style="width:80%; font-size:12px;">'.$item['AtAmbExameFisico'].'</td>	
                    </tr>
                </table>
                <table style="width:100%; border-collapse: collapse;"> 
                    <tr>
                        <td style="width:20%; font-weight: bold;">Hipotese Diagnostica</td>
                        <td style="width:80%; font-size:12px;">'.$item['AtAmbHipoteseDiagnostica'].'</td>	
                    </tr>
                </table>
                <table style="width:100%; border-collapse: collapse;"> 
                    <tr>
                        <td style="width:20%; font-weight: bold;">Digitacao Livre</td>	
                        <td style="width:80%; font-size:12px;">'.$item['AtAmbDigitacaoLivre'].'</td>
                    </tr>
                </table>
                <table style="width:100%; border-collapse: collapse;"> 
                    <tr>
                        <td style="width:20%; font-weight: bold;">Receituário</td>
                        <td style="width:80%; font-size:12px;">'.$item['AtRecReceituario'].'</td>	
                    </tr>
                </table>
                <table style="width:100%; border-collapse: collapse;">
                    <tr >
                        <td style="width:20%; font-weight: bold;">Solicitação de Procedimento</td>
                        <td style="width:80%; font-size:12px;">'.$item['AtSExJustificativa'].'</td>	
                    
                    </tr>
                </table>

                <br><br>
            ';
            
        }  
    
        if  ($item['AtClaChave'] == "ELETIVO"){

            $html .= '
            
                <div style=" border: #aaa solid 1px; text-align: center; font-weight: bold; position:relative; margin-top: 5px; background-color:#eee; padding: 10px;">
                DATA DO ATENDIMENTO ELETIVO
                <br><br>
                <span style=" text-align: center; color:#aaa;">&nbsp;&nbsp;|&nbsp;&nbsp;</span>Entrada: <span style="font-weight:normal;">' .mostraData($item['AtEleDataInicio']).' - '.mostraHora($item['AtEleHoraInicio']).'</span><span style="color:#aaa;">&nbsp;&nbsp;|&nbsp;&nbsp;</span><span style="color:#aaa;">&nbsp;&nbsp;|&nbsp;&nbsp;</span>Saída: <span style="font-weight:normal;">' .mostraData($item['AtEleDataInicio']).' - '.mostraHora($item['AtEleHoraFim']).'</span><span style="color:#aaa;">&nbsp;&nbsp;|&nbsp;&nbsp;</span>
                </div>
                <div style="  border: #aaa solid 1px; font-weight: bold; position:relative; background-color:#eee; padding: 10px;">
                Unidade de Atendimento: <span style="font-weight:normal;">'. $item['UnidaNome'] .'</span> <span style="color:#aaa;"></span><br>Médico Solicitante: <span style="font-weight:normal;">'.$item['ProfissionalNome'].' ('.$item['ProfissaoNome'].')</span> <span style="color:#aaa;">&nbsp;&nbsp;|&nbsp;&nbsp;</span> Modalidade: <span style="font-weight:normal;">'. $item['AtModNome'] .'</span><span style="color:#aaa;">&nbsp;&nbsp;|&nbsp;&nbsp;</span> Guia: <span style="font-weight:normal;">'.$item['AtendNumRegistro'].'</span>
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
                        <td style="width:80%; font-size:12px;">'.$item['AtRecReceituario'].'</td>	
                    </tr>
                </table>
                <table style="width:100%; border-collapse: collapse;">
                    <tr >
                        <td style="width:20%; font-weight: bold;">Solicitação de Procedimento</td>
                        <td style="width:80%; font-size:12px;">'.$item['AtSExJustificativa'].'</td>	
                    
                    </tr>
                </table>

                <br><br>
            ';
        
        } 

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

} catch (\Mpdf\MpdfException $e) { // Note: safer fully qualified exception name used for catch

	// Process the exception, log, print etc.
	echo 'ERRO: '.$e->getMessage();
}
