<?php
// print_r($rowPendente);

	print('
	<tr class="table-active table-border-double">
		<td colspan="3">Ações Aguardando Liberação/Finalização</td>
		<td class="text-right">
			<span class="badge bg-blue badge-pill">'.$totalPendente.'</span>
		</td>
	</tr>');
								
	foreach ($rowPendente as $item){ 
		
		if ($item['Intervalo'] > 1){
			$dias = 'dias';
		} else{
			$dias = 'dia';
		}

		print('
		<tr>
			<td class="text-center">
				<h6 class="mb-0" data-popup="tooltip" data-placement="bottom" data-container="body" title="'.mostradata($item['BandeData']).'">'.$item['Intervalo'].'</h6>
				<div class="font-size-sm text-muted line-height-1">'.$dias.'</div>
			</td>
			<td>
				<div class="d-flex align-items-center">
					<div class="mr-3">
						<a href="#" class="btn bg-teal-400 rounded-round btn-icon btn-sm">
							<span class="letter-icon"></span>
						</a>
					</div>
					<div>
						<span class="text-default font-weight-semibold letter-icon-title">'.nomeSobrenome($item['UsuarNome'], 2).'</span>
						<div class="text-muted font-size-sm"><span class="badge badge-mark border-blue mr-1"></span> '.$item['SituaNome'].'</div>
					</div>
				</div>
			</td>
		');
		
		if ($item['BandeTabela'] == 'Solicitacao'){
			if ($item['SetorAtual'] == $item['SetorQuandoSolicitou']){
				print('
					<td>
						<a href="#" class="text-default">
							<div class="font-weight-semibold">[#'.$item['BandeTabelaId'].'] '.$item['BandeIdentificacao'].'</div>
							<span class="text-muted">Ação: '.$item['BandeDescricao'].'</span>
						</a>
					</td>
				');
			} else{
				print('
					<td>
						<a href="#" class="text-default">
							<div class="font-weight-semibold">[#'.$item['BandeTabelaId'].'] '.$item['BandeIdentificacao'].' <span style="color:red;">Obs.: O solicitante mudou de setor, portanto, não é permitido liberar a solicitação.</span></div>
							<span class="text-muted">Ação: '.$item['BandeDescricao'].'</span>
						</a>
					</td>
				');
			}
		} else{
			print('
			<td>
				<a href="#" class="text-default">
					<div class="font-weight-semibold">[#'.$item['BandeTabelaId'].'] '.$item['BandeIdentificacao'].'</div>
					<span class="text-muted">Ação: '.$item['BandeDescricao'].'</span>
				</a>
			</td>
			');			
		}

		print('
			<td class="text-center">
				<div class="list-icons">
					<div class="list-icons-item dropdown">
						<a href="#" class="list-icons-item dropdown-toggle caret-0" data-toggle="dropdown"><i class="icon-menu7"></i></a>
						<div class="dropdown-menu dropdown-menu-right">
							<a href="#" onclick="atualizaBandeja('.$item['BandeId'].',\''.$item['BandeTabela'].'\','.$item['BandeTabelaId'].', \''.$item['MovimTipo'].'\', \'imprimir\');" class="dropdown-item"><i class="icon-printer2"></i> Visualizar</a>
							
							<div class="dropdown-divider"></div>
		');

		//Aqui verifica se o BandeUsuario está preenchido e se é TR, se sim, significa que é um presidente de Comissão
		if ($item['BandeUsuario'] == $_SESSION['UsuarId'] && $item['BandeTabela'] == 'TermoReferencia'){

			print('<a href="tr.php" class="dropdown-item"><i class="icon-circle-right2"></i> Acessar TR</a>');
			
			if ($item['SituaChaveTR'] == 'AGUARDANDOFINALIZACAO'){
				print('<a href="#" onclick="atualizaBandeja('.$item['BandeId'].',\''.$item['BandeTabela'].'\','.$item['BandeTabelaId'].', \''.$item['MovimTipo'].'\', \'finalizarTR\', '.$item['BandeUsuario'].');" class="dropdown-item"><i class="icon-checkmark3 text-success"></i> Finalizar TR</a>');
			}			

			print('
								</div>
							</div>
						</div>
					</td>
				</tr>
			'); 			

		} else if (isset($item['BandePerfil']) && $item['BandePerfil'] !== null && $item['BandePerfil'] !== '' && $item['BandePerfil'] === 'CENTROADMINISTRATIVO'){
			print('
				<a href="#" onclick="atualizaBandeja('.$item['BandeId'].',\''.$item['BandeTabela'].'\','.$item['BandeTabelaId'].', \''.$item['MovimTipo'].'\', \'liberarCentroAdministrativo\');" class="dropdown-item"><i class="icon-checkmark3 text-success"></i> Liberar</a>
								
				<a href="#" onclick="atualizaBandeja('.$item['BandeId'].',\''.$item['BandeTabela'].'\','.$item['BandeTabelaId'].', \''.$item['MovimTipo'].'\', \'naoliberar\');" class="dropdown-item" id="motivo"><i class="icon-cross2 text-danger"></i> Não Liberar</a>

							</div>
						</div>
					</div>
				</td>
			</tr>
			'); 
		} else if(isset($item['BandePerfil']) && $item['BandePerfil'] !== null && $item['BandePerfil'] !== '' && $item['BandePerfil'] === 'CONTABILIDADE'){
		

			if (isset($item['OrComTipo']) == 'O' || $item['OrComTipo'] == 'C' ) {
				print('
					<a href="#" onclick="atualizaBandeja('.$item['BandeId'].',\''.$item['BandeTabela'].'\','.$item['BandeTabelaId'].', \''.$item['MovimTipo'].'\', \'empenharContabilidade\');" class="dropdown-item"><i class="icon-checkmark3 text-success"></i> Empenhar </a>

									</div>
								</div>
							</div>
						</td>
					</tr>
				'); 
			} else if(isset($item['MovimTipo']) == 'E' ) {
				print('
					<a href="#" onclick="atualizaBandeja('.$item['BandeId'].',\''.$item['BandeTabela'].'\','.$item['BandeTabelaId'].', \''.$item['MovimTipo'].'\', \'liquidarContabilidade\');" class="dropdown-item"><i class="icon-checkmark3 text-success"></i> Liquidar </a>

									</div>
								</div>
							</div>
						</td>
					</tr>
				'); 
			} else {
				print('
					<a href="#" onclick="atualizaBandeja('.$item['BandeId'].',\''.$item['BandeTabela'].'\','.$item['BandeTabelaId'].', \''.$item['MovimTipo'].'\', \'liberarContabilidade\');" class="dropdown-item"><i class="icon-checkmark3 text-success"></i> Liberar</a>

									</div>
								</div>
							</div>
						</td>
					</tr>
				'); 
            }
		} else {
			print('
				<a href="#" onclick="atualizaBandeja('.$item['BandeId'].',\''.$item['BandeTabela'].'\','.$item['BandeTabelaId'].', \''.$item['MovimTipo'].'\', \'liberar\');" class="dropdown-item"><i class="icon-checkmark3 text-success"></i> Liberar</a>
				<a href="#" onclick="atualizaBandeja('.$item['BandeId'].',\''.$item['BandeTabela'].'\','.$item['BandeTabelaId'].', \''.$item['MovimTipo'].'\', \'naoliberar\');" class="dropdown-item" id="motivo"><i class="icon-cross2 text-danger"></i> Não Liberar</a>
								</div>
							</div>
						</div>
					</td>
				</tr>
			');
		}
	}

?>