<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

if(isset($_POST['historicoId'])){

	$sql = "SELECT AtendId, AtendNumRegistro, UnidaNome, AtModNome, AtRecReceituario, AtSExJustificativa, AtAmbDataInicio, AtAmbHoraInicio, AtAmbHoraFim, 
				   AtAmbQueixaPrincipal, AtAmbHistoriaMolestiaAtual, AtAmbHistoriaPatologicaPregressa, AtAmbExameFisico, AtAmbHipoteseDiagnostica, AtAmbDigitacaoLivre, 
				   A.ProfiNome as ProfissionalNome, B.ProfiNome as ProfissaoNome, ProfiProfissao, AtEleDataInicio, AtEleHoraInicio, 
				   AtEleHoraFim, AtClaNome, AtClaChave
			FROM Atendimento
			LEFT JOIN AtendimentoEletivo ON AtEleAtendimento = AtendId
			LEFT JOIN AtendimentoAmbulatorial ON AtAmbAtendimento = AtendId
			LEFT JOIN AtendimentoModalidade ON AtModId = AtendModalidade
			LEFT JOIN AtendimentoReceituario ON AtRecAtendimento = AtendId
			LEFT JOIN AtendimentoSolicitacaoExame ON AtSExAtendimento = AtendId
			LEFT JOIN AtendimentoClassificacao ON AtClaId = AtendClassificacao
			LEFT JOIN Profissional A ON A.ProfiId = AtSExProfissional
			LEFT JOIN Profissao B ON B.ProfiId = A.ProfiProfissao
			JOIN Unidade ON UnidaId = AtendUnidade
			JOIN Cliente ON ClienId = AtendCliente
			WHERE AtendId = '". $_POST['historicoId']."' and AtendUnidade = ".$_SESSION['UnidadeId']; 
	$result = $conn->query($sql);
	$row = $result->fetch(PDO::FETCH_ASSOC);
	$count = count($row);
	

	if($count){

		if($row['AtClaChave'] == "AMBULATORIAL"){
			print('
				<p style="margin-right:10px; margin-left: 10px"><b>ENTRADA:</b> '.mostraData($row['AtAmbDataInicio']).' - '.mostraHora($row['AtAmbHoraInicio']).'</p>
				<p style="margin-right:10px; margin-left: 10px"><b>SAÍDA:</b> '.mostraData($row['AtAmbDataInicio']).' - '.mostraHora($row['AtAmbHoraFim']).'</p>
				<hr style="margin-right:10px; margin-left: 10px">
				<p style="margin-right:10px; margin-left: 10px"><b>Modalidade:</b> '.$row['AtModNome'].'</p>
				<p style="margin-right:10px; margin-left: 10px"><b>Guia:</b> '.$row['AtendNumRegistro'].'</p>
				<p style="margin-right:10px; margin-left: 10px"><b>Unidade de Atendimento:</b> '.$row['UnidaNome'].'</p>
				<p style="margin-right:10px; margin-left: 10px"><b>Médico Solicitante:</b><b> '.$row['ProfissionalNome'].' ('.$row['ProfissaoNome'].')</b></p>
				<hr style="margin-right:10px; margin-left: 10px">
				<p style="margin-right:10px; margin-left: 10px"><b>Queixa Principal (QP):</b> '.$row['AtAmbQueixaPrincipal'].'</p>
				<p style="margin-right:10px; margin-left: 10px"><b>História da Moléstia Atual (HMA):</b> '.$row['AtAmbHistoriaMolestiaAtual'].'</p>
				<p style="margin-right:10px; margin-left: 10px"><b>Patologica Pregressa:</b> '.$row['AtAmbHistoriaPatologicaPregressa'].'</p>
				<p style="margin-right:10px; margin-left: 10px"><b>Exame Físico:</b> '.$row['AtAmbExameFisico'].'</p>
				<p style="margin-right:10px; margin-left: 10px"><b>Hipotese Diagnostica:</b> '.$row['AtAmbHipoteseDiagnostica'].'</p>
				<p style="margin-right:10px; margin-left: 10px"><b>Digitacao Livre:</b> '.$row['AtAmbDigitacaoLivre'].'</p>
				<p style="margin-right:10px; margin-left: 10px"><b>Receituário:</b> '.$row['AtRecReceituario'].'</p>
				<p style="margin-right:10px; margin-left: 10px"><b>Solicitação de Procedimento:</b> '.$row['AtSExJustificativa'].'</p>
			');
		}  else if  ($row['AtClaChave'] == "ELETIVO"){

			print('
				<p style="margin-right:10px; margin-left: 10px"><b>ENTRADA:</b> '.mostraData($row['AtEleDataInicio']).' - '.mostraHora($row['AtEleHoraInicio']).'</p>
				<p style="margin-right:10px; margin-left: 10px"><b>SAÍDA:</b> '.mostraData($row['AtEleDataInicio']).' - '.mostraHora($row['AtEleHoraFim']).'</p>
				<hr style="margin-right:10px; margin-left: 10px">
				<p style="margin-right:10px; margin-left: 10px"><b>Modalidade:</b> '.$row['AtModNome'].'</p>
				<p style="margin-right:10px; margin-left: 10px"><b>Guia:</b> '.$row['AtendNumRegistro'].'</p>
				<p style="margin-right:10px; margin-left: 10px"><b>Unidade de Atendimento:</b> '.$row['UnidaNome'].'</p>
				<p style="margin-right:10px; margin-left: 10px"><b>Médico Solicitante:</b><b> '.$row['ProfissionalNome'].' ('.$row['ProfissaoNome'].')</b></p>
				<hr style="margin-right:10px; margin-left: 10px">
				<p style="margin-right:10px; margin-left: 10px"><b>Receituário:</b> '.$row['AtRecReceituario'].'</p>
				<p style="margin-right:10px; margin-left: 10px"><b>Solicitação de Procedimento:</b> '.$row['AtSExJustificativa'].'</p>			
			');

		}  else {

			
		}

		print('<form name="formAtendimentoHistorico" id="formAtendimentoHistorico" method="post" action="atendimentoHistoricoImprime.php">
					<input type="hidden" name="AtendimentoId" id="AtendimentoId" value="'.$row['AtendId'].'" />
	  		   </form>
		');
		
	} else{
		echo 0;
	}
} else {
	echo 0;
}

?>
