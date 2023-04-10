<?php

	print('
	<tr class="table-active table-border-double">
		<td colspan="3">Ações Finalizadas</td>
		<td class="text-right">
			<span class="badge bg-brown badge-pill">'.$totalFinalizado.'</span>
		</td>
	</tr>');
	
	foreach ($rowFinalizado as $item){ 

		print('								
		<tr>
			<td class="text-center">
				<i class="icon-thumbs-up2 text-brown-400"></i>
			</td>
			<td>
				<div class="d-flex align-items-center">
					<div class="mr-3">
						<a href="#">
							<img src="global_assets/images/placeholders/placeholder.jpg" class="rounded-circle" width="32" height="32" alt="Placeholder" />
						</a>
					</div>
					<div>
						<a href="#" class="text-default font-weight-semibold">'.nomeSobrenome($item['UsuarNome'], 2).'</a>
						<div class="text-muted font-size-sm"><span class="badge badge-mark border-brown mr-1"></span> '.$item['SituaNome'].'</div>
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