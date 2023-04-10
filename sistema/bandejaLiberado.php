<?php

	print('
	<tr class="table-active table-border-double">
		<td colspan="3">Ações Liberadas</td>
		<td class="text-right">
			<span class="badge bg-success badge-pill">'.$totalLiberado.'</span>
		</td>
	</tr>');
	
	foreach ($rowLiberado as $item){ 

		print('
		<tr>
			<td class="text-center">
				<i class="icon-checkmark3 text-success"></i>
			</td>
			<td>
				<div class="d-flex align-items-center">
					<div class="mr-3">
						<a href="#" class="btn bg-success-400 rounded-round btn-icon btn-sm">
							<span class="letter-icon"></span>
						</a>
					</div>
					<div>
						<a href="#" class="text-default font-weight-semibold letter-icon-title">'.nomeSobrenome($item['UsuarNome'], 2).'</a>
						<div class="text-muted font-size-sm"><span class="badge badge-mark border-success mr-1"></span> '.$item['SituaNome'].'</div>
					</div>
				</div>
			</td>
			<td>
				<a href="#" class="text-default">
					<div>[#'.$item['BandeTabelaId'].'] '.$item['BandeIdentificacao'].'</div>
					<span class="text-muted">Ação: '.$item['BandeDescricao'].'</span>
				</a>
			</td>
			<td class="text-center">
				<div class="list-icons">
					<div class="list-icons list-icons-extendedt">
						<a href="#" onclick="atualizaBandeja('.$item['BandeId'].',\''.$item['BandeTabela'].'\','.$item['BandeTabelaId'].', \''.$item['MovimTipo'].'\', \'imprimir\');" class="list-icons-item"><i class="icon-printer2"></i> Visualizar</a>
					</div>
				</div>
			</td>
		</tr>
		');
	}

?>