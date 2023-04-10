<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Especialidade do Leito'; 

include('global_assets/php/conexao.php');


$sql = "SELECT EsLeiId, EsLeiCodigo, EsLeiNome, EsLeiStatus, SituaNome, SituaCor, SituaChave, 
		dbo.fnClassificacaoAtendimento(EsLeiId, EsLeiUnidade, 'EspecialidadeLeito') as Classificacao
		FROM EspecialidadeLeito
		JOIN Situacao on SituaId = EsLeiStatus
	    WHERE EsLeiUnidade = ". $_SESSION['UnidadeId'] ."
		ORDER BY EsLeiNome ASC";
$result = $conn->query($sql);
$row = $result->fetchAll(PDO::FETCH_ASSOC);
//$count = count($row);

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Especialidade do leito</title>

	<?php include_once("head.php"); ?>
	
	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/tables/datatables/datatables.min.js"></script>
	<script src="global_assets/js/plugins/tables/datatables/extensions/responsive.min.js"></script>

	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>

	<script src="global_assets/js/demo_pages/datatables_responsive.js"></script>
	<script src="global_assets/js/demo_pages/datatables_sorting.js"></script>
	<!-- /theme JS files -->	
	
	<script type="text/javascript">

		$(document).ready(function (){
			
			/* Início: Tabela Personalizada */
			$('#tblEspecialidadeLeito').DataTable( {
				"order": [[ 0, "asc" ]],
			    autoWidth: false,
				responsive: true,
			    columnDefs: [
				{
					orderable: true,   //Código
					width: "10%",
					targets: [0]
				},
				{
					orderable: true,   //Especialidade do leito
					width: "35%",
					targets: [1]
				},
				{
					orderable: true,   //Classificação
					width: "35%",
					targets: [2]
				},
				{ 
					orderable: true,   //Situação
					width: "10%",
					targets: [3]
				},
				{ 
					orderable: false,   //Ações
					width: "10%",
					targets: [4]
				}],
				dom: '<"datatable-header"fl><"datatable-scroll-wrap"t><"datatable-footer"ip>',
				language: {
					search: '<span>Filtro:</span> _INPUT_',
					searchPlaceholder: 'filtra qualquer coluna...',
					lengthMenu: '<span>Mostrar:</span> _MENU_',
					paginate: { 'first': 'Primeira', 'last': 'Última', 'next': $('html').attr('dir') == 'rtl' ? '&larr;' : '&rarr;', 'previous': $('html').attr('dir') == 'rtl' ? '&rarr;' : '&larr;' }
				}
			});
			
			// Select2 for length menu styling
			var _componentSelect2 = function() {
				if (!$().select2) {
					console.warn('Warning - select2.min.js is not loaded.');
					return;
				}

				// Initialize
				$('.dataTables_length select').select2({
					minimumResultsForSearch: Infinity,
					dropdownAutoWidth: true,
					width: 'auto'
				});
			};	

			_componentSelect2();
			
			/* Fim: Tabela Personalizada */				
		});
			
		//Essa função foi criada para não usar $_GET e ficar mostrando os ids via URL
		function atualizaEspecialidadeLeito(Permission, EsLeiId, EsLeiNome, EsLeiStatus, Tipo){
		
		if (Permission == 1){
			document.getElementById('inputEspecialidadeLeitoId').value = EsLeiId;
			document.getElementById('inputEspecialidadeLeitoNome').value = EsLeiNome;
			document.getElementById('inputEspecialidadeLeitoStatus').value = EsLeiStatus;
					
			if (Tipo == 'edita'){	
				document.formEspecialidadeLeito.action = "atendimentoEspecialidadeLeitoEdita.php";		
			} else if (Tipo == 'exclui'){
				confirmaExclusao(document.formEspecialidadeLeito, "Tem certeza que deseja excluir essa Especialidade do leito?", "atendimentoEspecialidadeLeitoExclui.php");
			} else if (Tipo == 'mudaStatus'){
				document.formEspecialidadeLeito.action = "atendimentoEspecialidadeLeitoMudaSituacao.php";
			} 
			
			document.formEspecialidadeLeito.submit();
		} else{
			alerta('Permissão Negada!','');
		}
	}		
			
	</script>

</head>

<body class="navbar-top">

	<?php include_once("topo.php"); ?>	

	<!-- Page content -->
	<div class="page-content">
		
		<?php include_once("menu-left.php"); ?>

		<!-- Main content -->
		<div class="content-wrapper">

			<?php include_once("cabecalho.php"); ?>	

			<!-- Content area -->
			<div class="content">

				<!-- Info blocks -->		
				<div class="row">
					<div class="col-lg-12">
						<!-- Basic responsive configuration -->
						<div class="card">
							<div class="card-header header-elements-inline">
								<h3 class="card-title">Relação da Especialidade do Leito</h3>
							</div>

							<div class="card-body">
								<div class="row">
									<div class="col-lg-9">
										<p class="font-size-lg">A relação abaixo faz referência as especialidades do leito da unidade <b><?php echo $_SESSION['UnidadeNome']; ?></b></p>
									</div>	
									<div class="col-lg-3">	
										<div class="text-right"><a href="atendimentoEspecialidadeLeitoNovo.php" class="btn btn-principal" role="button">Especialidade do Leito Novo</a></div>
									</div>
								</div>
							</div>
							
							<table id="tblEspecialidadeLeito" class="table">
								<thead>
									<tr class="bg-slate">
										<th data-filter>Código</th>
										<th data-filter>Especialidade do Leito</th>
										<th data-filter>Classificação</th>
										<th>Situação</th>
										<th class="text-center">Ações</th>
									</tr>
								</thead>
								<tbody>
								<?php
									foreach ($row as $item){
										
										$situacao = $item['SituaNome'];
										$situacaoClasse = 'badge badge-flat border-'.$item['SituaCor'].' text-'.$item['SituaCor'];
										$situacaoChave ='\''.$item['SituaChave'].'\'';
										$Classificacao = $item['Classificacao'];

										print('
										<tr>
											<td>'.$item['EsLeiCodigo'].'</td>
											<td>'.$item['EsLeiNome'].'</td>
											<td>'.$Classificacao.'</td>
											');
										
                                        print('<td><a href="#" onclick="atualizaEspecialidadeLeito(1,'.$item['EsLeiId'].', \''.$item['EsLeiNome'].'\','.$situacaoChave.', \'mudaStatus\');"><span class="badge '.$situacaoClasse.'">'.$situacao.'</span></a></td>');
										
										print('<td class="text-center">
												<div class="list-icons">
													<div class="list-icons list-icons-extended">
													<a href="#" onclick="atualizaEspecialidadeLeito(1,'.$item['EsLeiId'].', \''.$item['EsLeiNome'].'\', '.$item['EsLeiStatus'].', \'edita\');" class="list-icons-item"><i class="icon-pencil7" data-popup="tooltip" data-placement="bottom" title="Editar" ></i></a>
													<a href="#" onclick="atualizaEspecialidadeLeito(1,'.$item['EsLeiId'].', \''.$item['EsLeiNome'].'\', '.$item['EsLeiStatus'].', \'exclui\');" class="list-icons-item"><i class="icon-bin" data-popup="tooltip" data-placement="bottom" title="Exluir"></i></a>														
													</div>
												</div>
											</td>
										</tr>');
									}
								?>

								</tbody>
							</table>
						</div>
						<!-- /basic responsive configuration -->

					</div>
				</div>				
				
				<!-- /info blocks -->
				
				<form name="formEspecialidadeLeito" method="post">
					<input type="hidden" id="inputEspecialidadeLeitoId" name="inputEspecialidadeLeitoId" >
					<input type="hidden" id="inputEspecialidadeLeitoNome" name="inputEspecialidadeLeitoNome" >
					<input type="hidden" id="inputEspecialidadeLeitoStatus" name="inputEspecialidadeLeitoStatus" >
				</form>

			</div>
			<!-- /content area -->
			
			<?php include_once("footer.php"); ?>

		</div>
		<!-- /main content -->

	</div>
	<!-- /page content -->

	<?php include_once("alerta.php"); ?>

</body>

</html>
