<?php
	// essas variáveis são utilizadas para colocar o nome da classificação do atendimento no menu secundario

	$sql = "SELECT AtClaNome, AtClaChave, AtendCliente, ClienCodigo, ClienNome, SituaChave,AtendDesfechoChave
	FROM Atendimento
	JOIN AtendimentoClassificacao ON AtClaId = AtendClassificacao
	JOIN Cliente ON ClienId = AtendCliente
	JOIN Situacao ON SituaId = AtendSituacao
	WHERE AtendId = $iAtendimentoId";
	$result = $conn->query($sql);
	$rowClassificacao = $result->fetch(PDO::FETCH_ASSOC);

	$ClaChave = $rowClassificacao['AtClaChave'];
	$ClaNome = $rowClassificacao['AtClaNome'] == 'Internação' ? "HOSPITALAR" : $rowClassificacao['AtClaNome'];
	$prontuario = $rowClassificacao['ClienCodigo'];
	$Cliente = $rowClassificacao['ClienNome'];

	//Situação do Atendimento na Sessão
	if (isset($_POST['SituaChave'])){
		$_SESSION['SituaChave'] = isset($_POST['SituaChave'])?$_POST['SituaChave']:'';
	} 
	
	$SituaChave = $_SESSION['SituaChave'];
	$desfechoChave = $rowClassificacao['AtendDesfechoChave'];
?>

<script language ="javascript">

	$(document).ready(function() {
		$('.itemLink').each(function() {
			$(this).click(function(e) {
				e.preventDefault()
				let tipo = $(this).data('tipo')
				let URL = ''

				switch(tipo){
					case 'atendimentoEletivo': URL = 'atendimentoEletivo.php'; $('#dadosPost').attr('target', '_self'); break;
					case 'atestadoMedico': URL = 'atendimentoAtestadoMedico.php'; $('#dadosPost').attr('target', '_self'); break;
					case 'historicoPaciente': URL = 'atendimentoHistoricoPaciente.php'; $('#dadosPost').attr('target', '_self'); break;
					case 'encaminhamentoMedico': URL = 'atendimentoEncaminhamentoMedico.php'; $('#dadosPost').attr('target', '_self'); break;
					case 'receituarioMedico': URL = 'atendimentoReceituario.php'; $('#dadosPost').attr('target', '_self'); break;
					case 'observacaoHospitalarEntrada': URL = 'atendimentoObservacaoHospitalar.php'; $('#dadosPost').append(`<input type='hidden' name='screen' value='activeEntrada' />`); $('#dadosPost').attr('target', '_self'); break;
					case 'observacaoHospitalarPrescricaoMedica': URL = 'atendimentoObservacaoHospitalar.php'; $('#dadosPost').append(`<input type='hidden' name='screen' value='activePrescricao' />`); $('#dadosPost').attr('target', '_self'); break;
					case 'observacaoHospitalarEvolucaoMedica': URL = 'atendimentoObservacaoHospitalar.php'; $('#dadosPost').append(`<input type='hidden' name='screen' value='activeEvolucaoMedica' />`); $('#dadosPost').attr('target', '_self'); break;
					case 'solicitacaoExame': URL = 'atendimentoSolicitacaoExame.php'; $('#dadosPost').attr('target', '_self'); break;
					case 'solicitacaoProcedimento': URL = 'atendimentoSolicitacaoProcedimento.php'; $('#dadosPost').attr('target', '_self'); break;
					case 'triagem': URL = 'atendimentoTriagem.php'; $('#dadosPost').attr('target', '_self'); break;
					case 'atendimentoAmbulatorial': URL = 'atendimentoAmbulatorial.php'; $('#dadosPost').attr('target', '_self'); break;
					case 'exportacaoProntuario': URL = 'atendimentoProntuarioExportacao.php'; $('#dadosPost').attr('target', '_blank'); break;
					case 'tabelaGastos': URL = 'atendimentoTabelaGastos.php'; $('#dadosPost').attr('target', '_self'); break;
					case 'documento': URL = 'atendimentoDocumentos.php'; $('#dadosPost').attr('target', '_self'); break;
					case 'admissaoEnfermagem': URL = 'atendimentoAdmissaoEnfermagem.php'; $('#dadosPost').attr('target', '_self'); break;
					case 'evolucaoEnfermagem': URL = 'atendimentoEvolucaoEnfermagem.php'; $('#dadosPost').attr('target', '_self'); break;
					case 'efetivacaoAlta': URL = 'atendimentoEfetivacaoAlta.php'; $('#dadosPost').attr('target', '_self'); break;
					case 'anotacaoTecnicoEnfermagem': URL = 'atendimentoAnotacaoTecnicoEnfermagem.php'; $('#dadosPost').attr('target', '_self'); break;
					case 'anotacaoTecnicoEnfermagemRN': URL = 'atendimentoAnotacaoTecnicoEnfermagemRN.php'; $('#dadosPost').attr('target', '_self'); break;
					case 'admissaoPediatrica': URL = 'atendimentoAdmissaoPediatrica.php'; $('#dadosPost').attr('target', '_self'); break;
					case 'admissaoEnfermagemMultidisciplinar': URL = 'atendimentoAdmissaoEnfermagemMultidisciplinar.php'; $('#dadosPost').attr('target', '_self'); break;
					case 'admissaoCirurgica': URL = 'admissaoCirurgicaPreOperatorio.php'; $('#dadosPost').attr('target', '_self'); break;
					case 'anotacaoTransOperatorio': URL = 'anotacaoTransOperatorio.php'; $('#dadosPost').attr('target', '_self'); break;
					case 'admissaoRN': URL = 'admissaoRecemNascido.php'; $('#dadosPost').attr('target', '_self'); break;
					case 'admissaoPreParto': URL = 'atendimentoAdmissaoPreParto.php'; $('#dadosPost').attr('target', '_self'); break;
					case 'provisaoAlta': URL = 'atendimentoProvisaoAlta.php'; $('#dadosPost').attr('target', '_self'); break;
					case 'checklistCirurgiaSegura': URL = 'atendimentoChecklistCirurgiaSegura.php'; $('#dadosPost').attr('target', '_self'); break;
					case 'internacaoHospitalarEntrada': URL = 'atendimentoInternacaoHospitalarEntrada.php'; $('#dadosPost').attr('target', '_self'); break;
					case 'internacaoHospitalarPrescricaoMedica': URL = 'atendimentoInternacaoHospitalarPrescricaoEvolucao.php'; $('#dadosPost').attr('target', '_self'); break;
					case 'internacaoHospitalarEvolucaoMedica': URL = 'atendimentoInternacaoHospitalarEvolucaoMedica.php'; $('#dadosPost').attr('target', '_self'); break;
					case 'internacaoHospitalarSolicitacaoInterconsulta': URL = 'atendimentoInternacaoHospitalarSolicitaoInterconsulta.php'; $('#dadosPost').attr('target', '_self'); break;
					default: URL = ''; console.log(tipo); return; break;
				}
				$('#dadosPost').attr('action', URL)
				$('#dadosPost').attr('method', 'POST')
				$('#dadosPost').submit()
			})
		})

		$('#finalizarAtendimento').on('click', function(e){
			e.preventDefault()
			$('#dadosPost').attr('action', 'atendimentoFinalizar.php')
			$('#dadosPost').attr('method', 'POST')
			$('#dadosPost').submit()
		})
	})

</script>

<!-- Secondary sidebar -->
<div class="sidebar sidebar-light sidebar-secondary sidebar-expand-md">
	<!-- Sidebar mobile toggler -->
	<div class="sidebar-mobile-toggler text-center">
		<a href="#" class="sidebar-mobile-secondary-toggle">
			<i class="icon-arrow-left8"></i>
		</a>
		<span class="font-weight-semibold">Secondary sidebar</span>
		<a href="#" class="sidebar-mobile-expand">
			<i class="icon-screen-full"></i>
			<i class="icon-screen-normal"></i>
		</a>
	</div>
	<!-- /sidebar mobile toggler -->

	<!-- Sidebar content -->
	<div class="sidebar-content">
		<!-- Sub navigation -->
		<div class="card mb-2">
			<div class="card-body p-0">
				<?php if($ClaChave == 'AMBULATORIAL'){?>
					<ul class="nav nav-sidebar" data-nav-type="accordion">
						<li style="padding: 20px 0px 0px 20px;"><h2 style="font-weight: 500"><?php echo "".strtoupper($ClaNome); ?></b></li>

						<li class="nav-item-divider"></li>

						<li class="nav-item-header"><?php echo strtoupper($Cliente). "<br>Prontuário: " .$prontuario ; ?></li>

						<li class="nav-item-divider"></li>

						<?php if($SituaChave == 'EMOBSERVACAO'){?>
							<li class="nav-item nav-item-submenu">
								<a href="#" class="nav-link legitRipple">Ato de Enfermagem</a>
								<ul class="nav nav-group-sub">
									<li class="nav-item">
										<a href="#" class="nav-link itemLink" data-tipo='admissaoEnfermagem'><i class="icon-certificate"></i> Admissão</a>
									</li>
									<li class="nav-item">
										<a href="#" class="nav-link itemLink" data-tipo='evolucaoEnfermagem'><i class="icon-certificate"></i> Prescrição e Evolução</a>
									</li>
									<li class="nav-item">
										<a href="#" class="nav-link itemLink" data-tipo='anotacaoTecnicoEnfermagem'><i class="icon-certificate"></i> Anotações</a>
									</li>								
									<li class="nav-item">
										<a href="#" class="nav-link itemLink" data-tipo='formularios'><i class="icon-certificate"></i> Formulários</a>
									</li>
									<li class="nav-item">
										<a href="#" class="nav-link itemLink" data-tipo='efetivacaoAlta'><i class="icon-certificate"></i> Efetivação de Alta</a>
									</li>
									<li class="nav-item">
										<a href="#" class="nav-link itemLink" data-tipo='relatorioAta'><i class="icon-certificate"></i> Relatório de Alta</a>
									</li>
									<li class="nav-item">
										<a href="#" class="nav-link itemLink" data-tipo='admissaoCirurgica'><i class="icon-certificate"></i> Admissão Cirúrgica Pré-Operatório</a>
									</li>
									<li class="nav-item">
										<a href="#" class="nav-link itemLink" data-tipo='anotacaoTransOperatorio'><i class="icon-certificate"></i> Anotação Trans-Operatória</a>
									</li>
									<li class="nav-item">
										<a href="#" class="nav-link itemLink" data-tipo='admissaoRN'><i class="icon-certificate"></i> Admissão de RN</a>
									</li>
									<li class="nav-item">
										<a href="#" class="nav-link itemLink" data-tipo='provisaoAlta'><i class="icon-certificate"></i> Provisão de Alta</a>
									</li>
								</ul>
							</li>
						<?php }?>

						<li class="nav-item">
							<a href="#" class="nav-link itemLink" data-tipo='atendimentoAmbulatorial'><i class="icon-certificate"></i> Atendimento Ambulatorial</a>
						</li>
						<li class="nav-item">
							<a href="#" class="nav-link itemLink" data-tipo='historicoPaciente'><i class="icon-equalizer"></i> Histórico do Paciente</a>
						</li>						
						<li class="nav-item">
							<a href="#" class="nav-link itemLink" data-tipo='triagem'><i class="icon-home7"></i> Triagem</a>
						</li>
						<li class="nav-item">
							<a href="#" class="nav-link itemLink" data-tipo='solicitacaoExame'><i class="icon-copy"></i> Solicitação de Exames</a>
						</li>
						<li class="nav-item">
							<a href="#" class="nav-link itemLink" data-tipo='solicitacaoProcedimento'><i class="icon-copy"></i> Solicitação de Procedimentos</a>
						</li>	
						<li class="nav-item">
							<a href="#" class="nav-link itemLink" data-tipo='tabelaGastos'><i class="icon-table2"></i> Tabela de Gastos</a>
						</li>											
						<li class="nav-item">
							<a href="#" class="nav-link itemLink" data-tipo='documento'><i class="icon-file-text"></i> Documentos</a>
						</li>
						<li class="nav-item">
							<a href="#" class="nav-link itemLink" data-tipo='encaminhamentoMedico'><i class="icon-folder-plus4"></i> Encaminhamento Médico</a>
						</li>
						
						<?php if($SituaChave == 'ATENDIDO'){?>
							<!-- Esse item de menu só deve aparecer em paciente atendidos e que o desfecho foi com receita -->	
							<li class="nav-item">
								<a href="#" class="nav-link itemLink" data-tipo='receituarioMedico'><i class="icon-folder-plus4"></i> Receituário</a>
							</li>
						<?php }?>

						<?php if($desfechoChave == 'TRANSFERENCIA'){?>
							<!-- Esse item de menu só deve aparecer em paciente atendidos e que o desfecho foi com transferência -->
							<li class="nav-item">
								<a href="#" class="nav-link itemLink" data-tipo='receituarioMedico'><i class="icon-folder-plus4"></i> Transferência</a>
							</li>
						<?php }?>

						<li class="nav-item">
							<a href="#" class="nav-link itemLink" data-tipo='exportacaoProntuario'><i class="icon-drawer-out"></i> Exportação do Prontuário</a>
						</li>	


						<!-- Esses menus de Observação só devem aparecer quando vier do card Observação Hospitalar --> 
						<li class="nav-item nav-item-submenu">
							<a href="#" class="nav-link legitRipple">Observação Hospitalar</a>
							<ul class="nav nav-group-sub">
								<li class="nav-item">
									<a href="#" class="nav-link itemLink" data-tipo='observacaoHospitalarEntrada'><i class="icon-file-eye"></i> Entrada</a>
								</li>

								<li class="nav-item">
									<a href="#" class="nav-link itemLink" data-tipo='observacaoHospitalarPrescricaoMedica'><i class="icon-file-text2"></i> Prescrição Médica</a>
								</li>	

								<li class="nav-item">
									<a href="#" class="nav-link itemLink" data-tipo='observacaoHospitalarEvolucaoMedica'><i class="icon-file-text2"></i> Evolução Médica</a>
								</li>

								<li class="nav-item">
									<a href="#" class="nav-link itemLink" data-tipo='evolucaoEnfermagem'><i class="icon-file-text2"></i> Evolução de Enfermagem</a>
								</li>
							</ul>	
						</li>

						<li class="nav-item-divider"></li>

						<li class="nav-item pt-3">
							<div class="col-lg-12">

								<?php if($SituaChave != 'ATENDIDO'){?>

									<button class="btn w-100 btn-lg btn-principal" id="finalizarAtendimento">Finalizar atendimento</button>

								<?php }?>
								
							</div>
						</li>
					</ul>
				<?php }elseif($ClaChave == 'ELETIVO'){ ?>
					<ul class="nav nav-sidebar" data-nav-type="accordion">
						<li style="padding: 20px 0px 0px 20px;"><h2 style="font-weight: 500"><?php echo "".strtoupper($ClaNome); ?></b></li>

						<li class="nav-item-divider"></li>

						<li class="nav-item-header"><?php echo strtoupper($Cliente). "<br>Prontuário: " .$prontuario ; ?></li>

						<li class="nav-item-divider"></li>
						
						<li class="nav-item">
							<a href="#" class="nav-link itemLink" data-tipo='atendimentoEletivo'><i class="icon-certificate"></i> Atendimento Eletivo</a>
						</li>
						<li class="nav-item">
							<a href="#" class="nav-link itemLink" data-tipo='historicoPaciente'><i class="icon-equalizer"></i> Histórico do Paciente</a>
						</li>
						<li class="nav-item">
							<a href="#" class="nav-link itemLink" data-tipo='triagem'><i class="icon-home7"></i> Triagem</a>
						</li>
						<li class="nav-item">
							<a href="#" class="nav-link itemLink" data-tipo='solicitacaoExame'><i class="icon-office"></i> Solicitação de Exames</a>
						</li>
						<li class="nav-item">
							<a href="#" class="nav-link itemLink" data-tipo='solicitacaoProcedimento'><i class="icon-office"></i> Solicitação de Procedimentos</a>
						</li>	
						<li class="nav-item">
							<a href="#" class="nav-link itemLink" data-tipo='tabelaGastos'><i class="icon-office"></i> Tabela de Gastos</a>
						</li>
						<li class="nav-item">
							<a href="#" class="nav-link itemLink" data-tipo='documento'><i class="icon-file-text"></i> Documentos</a>
						</li>
						<li class="nav-item">
							<a href="#" class="nav-link itemLink" data-tipo='encaminhamentoMedico'><i class="icon-folder-plus4"></i> Encaminhamento Médico</a>
						</li>

						<?php if($SituaChave == 'ATENDIDO'){?>
							<!-- Esse item de menu só deve aparecer em paciente atendidos e que o desfecho foi com receita -->	
							<li class="nav-item">
								<a href="#" class="nav-link itemLink" data-tipo='receituarioMedico'><i class="icon-folder-plus4"></i> Receituário</a>
							</li>
						<?php }?>

						<?php if($SituaChave == 'ATENDIDO' && $desfechoChave == 'TRANSFERENCIA'){?>
							<!-- Esse item de menu só deve aparecer em paciente atendidos e que o desfecho foi com transferência -->
							<li class="nav-item">
								<a href="#" class="nav-link itemLink" data-tipo='receituarioMedico'><i class="icon-folder-plus4"></i> Transferência</a>
							</li>
						<?php }?>

						<li class="nav-item">
							<a href="#" class="nav-link itemLink" data-tipo='exportacaoProntuario'><i class="icon-office"></i> Exportação do Prontuário</a>
						</li>

						<!--<li class="nav-item">
							<a href="#" class="nav-link itemLink" data-tipo='prescricaoMedica'><i class="icon-office"></i> Prescrição Médica</a>
						</li>-->

						<li class="nav-item-divider"></li>

						<li class="nav-item pt-3">
							<div class="col-lg-12">

								<?php if($SituaChave != 'ATENDIDO'){?>

									<button class="btn w-100 btn-lg btn-principal" id="finalizarAtendimento">Finalizar atendimento</button>

								<?php }?>
									
							</div>
						</li>
					</ul>
				<?php }elseif($ClaChave == 'HOSPITALAR'){ ?>
					<ul class="nav nav-sidebar" data-nav-type="accordion">
						<li style="padding: 20px 0px 0px 20px;"><h2 style="font-weight: 500"><?php echo "".strtoupper($ClaNome); ?></b></li>

						<li class="nav-item-divider"></li>

						<li class="nav-item-header"><?php echo strtoupper($Cliente). "<br>Prontuário: " .$prontuario ; ?></li>

						<li class="nav-item-divider"></li>

						<li class="nav-item nav-item-submenu">
							
							<a href="#" class="nav-link legitRipple">Internação Hospitalar</a>
							
							<ul class="nav nav-group-sub">

								<li class="nav-item">
									<a href="#" class="nav-link itemLink" data-tipo='internacaoHospitalarEntrada'><i class="icon-certificate"></i> Entrada do Paciente</a>
								</li>
								<li class="nav-item">
									<a href="#" class="nav-link itemLink" data-tipo='internacaoHospitalarPrescricaoMedica'><i class="icon-certificate"></i> Prescrição Médica</a>
								</li>
								<li class="nav-item">
									<a href="#" class="nav-link itemLink" data-tipo='internacaoHospitalarEvolucaoMedica'><i class="icon-certificate"></i> Evolução Médica</a>
								</li>
								<li class="nav-item">
									<a href="#" class="nav-link itemLink" data-tipo='evolucaoEnfermagem'><i class="icon-certificate"></i> Evolução de Enfermagem</a>
								</li>
								<li class="nav-item">
									<a href="#" class="nav-link itemLink" data-tipo='transferenciaInterna'><i class="icon-certificate"></i> Transferência Interna</a>
								</li>
								<li class="nav-item">
									<a href="#" class="nav-link itemLink" data-tipo='aih'><i class="icon-certificate"></i> AIH</a>
								</li>
								<li class="nav-item">
									<a href="#" class="nav-link itemLink" data-tipo='tiss'><i class="icon-certificate"></i> TISS</a>
								</li>

							</ul>

						</li>

						<li class="nav-item nav-item-submenu">
							<a href="#" class="nav-link legitRipple">Ato Médico</a>
							<ul class="nav nav-group-sub">
								<li class="nav-item">
									<a href="#" class="nav-link itemLink" data-tipo='historicoPaciente'><i class="icon-certificate"></i> Histórico do Paciente</a>
								</li>				
								<li class="nav-item">
									<a href="#" class="nav-link itemLink" data-tipo='solicitacaoExame'><i class="icon-certificate"></i> Solicitação de Exames</a>
								</li>
								<li class="nav-item">
									<a href="#" class="nav-link itemLink" data-tipo='solicitacaoProcedimento'><i class="icon-certificate"></i> Solicitação de Procedimentos</a>
								</li>
								<li class="nav-item">
									<a href="#" class="nav-link itemLink" data-tipo='solicitacaoLaboratorio'><i class="icon-certificate"></i> Solicitação de Laboratório</a>
								</li>
								<li class="nav-item">
									<a href="#" class="nav-link itemLink" data-tipo='receituarioMedico'><i class="icon-certificate"></i> Receituário</a>
								</li>
								<li class="nav-item">
									<a href="#" class="nav-link itemLink" data-tipo='encaminhamentoMedico'><i class="icon-certificate"></i> Encaminhamento Médico</a>
								</li>	
								
							</ul>
						</li>

						<li class="nav-item nav-item-submenu">
							<a href="#" class="nav-link legitRipple">Alta Hospitalar</a>
							<ul class="nav nav-group-sub">
								<li class="nav-item">
									<a href="#" class="nav-link itemLink" data-tipo='provisaoAlta'><i class="icon-certificate"></i> Provisão de Alta</a>
								</li>
								<li class="nav-item">
									<a href="#" class="nav-link itemLink" data-tipo='efetivacaoAlta'><i class="icon-certificate"></i> Efetivação de Alta</a>
								</li>									
							</ul>
						</li>

						<li class="nav-item nav-item-submenu">
							<a href="#" class="nav-link legitRipple">Ato Enfermagem</a>
							<ul class="nav nav-group-sub">
								<!--<li class="nav-item">
									<a href="#" class="nav-link itemLink" data-tipo='admissaoEnfermagem'><i class="icon-certificate"></i> Admissão</a>
								</li>-->
								<li class="nav-item">
									<a href="#" class="nav-link itemLink" data-tipo='admissaoEnfermagem'><i class="icon-certificate"></i> Prescrição e Evolução</a>
								</li>
								<li class="nav-item">
									<a href="#" class="nav-link itemLink" data-tipo='anotacaoTecnicoEnfermagem'><i class="icon-certificate"></i> Anotação Técnico de Enfermagem</a>
								</li>					
								<li class="nav-item">
									<a href="#" class="nav-link itemLink" data-tipo='admissaoPreParto'><i class="icon-certificate"></i> Admissão Pré Parto</a>
								</li>				
								<li class="nav-item">
									<a href="#" class="nav-link itemLink" data-tipo='anotacaoTecnicoEnfermagemRN'><i class="icon-certificate"></i> Anotação Técnico de Enfermagem RN</a>
								</li>
								<li class="nav-item">
									<a href="#" class="nav-link itemLink" data-tipo='anotacaoTransOperatorio'><i class="icon-certificate"></i> Anotação Trans-Operatório</a>
								</li>
								<li class="nav-item">
									<a href="#" class="nav-link itemLink" data-tipo='admissaoCirurgica'><i class="icon-certificate"></i> Admissão Cirúrgica Pré-Operatório</a>
								</li>
								<li class="nav-item">
									<a href="#" class="nav-link itemLink" data-tipo='checklistCirurgiaSegura'><i class="icon-certificate"></i> Checklist Cirurgia Segura</a>
								</li>
								<li class="nav-item">
									<a href="#" class="nav-link itemLink" data-tipo='relatoriosEnfermagem'><i class="icon-certificate"></i> Relatórios</a>
								</li>

								<!--
								<li class="nav-item">
									<a href="#" class="nav-link itemLink" data-tipo='admissaoPediatrica'><i class="icon-certificate"></i> Admissão Pediátrica</a>
								</li>													
								<li class="nav-item">
									<a href="#" class="nav-link itemLink" data-tipo='formularios'><i class="icon-certificate"></i> Formulários</a>
								</li>
								<li class="nav-item">
									<a href="#" class="nav-link itemLink" data-tipo='relatorioAta'><i class="icon-certificate"></i> Relatório de Alta</a>
								</li>							
								<li class="nav-item">
									<a href="#" class="nav-link itemLink" data-tipo='admissaoRN'><i class="icon-certificate"></i> Admissão de RN</a>
								</li>
								-->				
								
							</ul>
						</li>

						<li class="nav-item nav-item-submenu">
							<a href="#" class="nav-link legitRipple">Ato Multiprofissional</a>
							<ul class="nav nav-group-sub">
								<li class="nav-item">
									<a href="#" class="nav-link itemLink" data-tipo='admissaoEnfermagemMultidisciplinar'><i class="icon-certificate"></i> Admissão</a>
								</li>
								<li class="nav-item">
									<a href="#" class="nav-link itemLink" data-tipo='relatoriosMultiprofissional'><i class="icon-certificate"></i> Relatórios</a>
								</li>
							</ul>
						</li>

						<li class="nav-item">
							<a href="#" class="nav-link itemLink" data-tipo='triagem'><i class="icon-certificate"></i> Triagem</a>
						</li>						

						<li class="nav-item">
							<a href="#" class="nav-link itemLink" data-tipo='gestaoLeitos'><i class="icon-certificate"></i> Gestão de Leitos</a>
						</li>

						<li class="nav-item">
							<a href="#" class="nav-link itemLink" data-tipo='tabelaGastos'><i class="icon-certificate"></i> Tabela de Gastos</a>
						</li>

						<li class="nav-item">
							<a href="#" class="nav-link itemLink" data-tipo='documentacao'><i class="icon-certificate"></i> Documentação</a>
						</li>

						<li class="nav-item">
							<a href="#" class="nav-link itemLink" data-tipo='formulario'><i class="icon-certificate"></i> Formulário</a>
						</li>

						<li class="nav-item">
							<a href="#" class="nav-link itemLink" data-tipo='exportacaoProntuario'><i class="icon-drawer-out"></i> Exportação do Prontuário</a>
						</li>

						<li class="nav-item-divider"></li>

						<li class="nav-item pt-3">
							<div class="col-lg-12">

								<?php if($SituaChave != 'ATENDIDO'){?>

									<button class="btn w-100 btn-lg btn-principal" id="finalizarAtendimento">Finalizar atendimento</button>

								<?php }?>
								
							</div>
						</li>
					</ul>
				<?php }else{irpara("atendimento.php");} ?>
			</div>
		</div>
		<!-- /sub navigation -->

	</div>
	<!-- /sidebar content -->
</div>
<!-- /secondary sidebar -->
