<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Conta/Banco';

include('global_assets/php/conexao.php');

//O ALTERAR é usado na importação de Produtos (eles não devem aparecer aqui)
$sql = "SELECT CnBanId, CnBanNome, CnBanBanco, CnBanAgencia, CnBanConta, BancoNome, SituaNome, SituaChave, SituaId, SituaCor
		FROM ContaBanco
        LEFT JOIN Banco on BancoId = CnBanBanco
		JOIN Situacao on SituaId = CnBanStatus
		WHERE CnBanUnidade = ".$_SESSION['UnidadeId']."
		ORDER BY CnBanNome ASC";
$result = $conn->query($sql);
$row = $result->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Contas</title>

	<?php include_once("head.php"); ?>
	
	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/tables/datatables/datatables.min.js"></script>
	<script src="global_assets/js/plugins/tables/datatables/extensions/responsive.min.js"></script>
	
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>	
	
	<script src="global_assets/js/demo_pages/datatables_responsive.js"></script>
	<script src="global_assets/js/demo_pages/datatables_sorting.js"></script>
	
	<!-- Não permite que o usuário retorne para o EDITAR -->
	<script src="global_assets/js/lamparinas/stop-back.js"></script>
	
	<!-- /theme JS files -->	
	
	<script type="text/javascript">

		$(document).ready(function (){	
			$('#tblCategoria').DataTable( {
				"order": [[ 0, "asc" ]],
			    autoWidth: false,
				responsive: true,
			    columnDefs: [
				{
					orderable: true,   //Conta/Banco
					width: "40%",
					targets: [0]
				},
				{ 
					orderable: true,   //Banco
					width: "27%",
					targets: [1]
				},
				{ 
					orderable: true,   //Agência
					width: "10%",
					targets: [2]
				},
				{ 
					orderable: true,   //Conta
					width: "13%",
					targets: [3]
				},
				{ 
					orderable: true,   //Situação
					width: "5%",
					targets: [4]
				},
				{ 
					orderable: true,   //Ações
					width: "5%",
					targets: [5]
				}
				
				],
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
		})
			
		//Essa função foi criada para não usar $_GET e ficar mostrando os ids via URL
		function atualizaCategoria(Permission, contaBancoId, contaBancoNome, contaBancoStatus, Tipo){
		
			if (Permission == 1){
				document.getElementById('inputContaBancoId').value = contaBancoId;
				document.getElementById('inputContaBancoNome').value = contaBancoNome;
				document.getElementById('inputContaBancoStatus').value = contaBancoStatus;
						
				if (Tipo == 'edita'){	
					document.formContaBanco.action = "contaBancoEdita.php";		
				} else if (Tipo == 'exclui'){
					confirmaExclusao(document.formContaBanco, "Tem certeza que deseja excluir essa Conta/Banco?", "contaBancoExclui.php");
				} else if (Tipo == 'mudaStatus'){
					document.formContaBanco.action = "contaBancoMudaSituacao.php";
				} 
				
				document.formContaBanco.submit();
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
								<h3 class="card-title">Relação de Contas</h3>
							</div>

							<div class="card-body">
								<div class="row">
									<div class="col-lg-9">
										<p class="font-size-lg">A relação abaixo faz referência às Contas da unidade <b><?php echo $_SESSION['UnidadeNome']; ?></b></p>
									</div>
									<div class="col-lg-3">	
										<div class="text-right"><a href="contaBancoNovo.php" class="btn btn-principal" role="button">Nova Conta</a></div>
									</div>
								</div>
							</div>					
							
							<!-- A table só filtra se colocar 6 colunas. Onde mudar isso? -->
							<table id="tblCategoria" class="table">
								<thead>
									<tr class="bg-slate">
										<th data-filter>Conta</th>
                                        <th data-filter>Banco</th>
                                        <th data-filter>Agência</th>
                                        <th data-filter>Conta Bancária</th>
										<th>Situação</th>
										<th class="text-center">Ações</th>
									</tr>
								</thead>
								<tbody>
								<?php
									foreach ($row as $item){
										
										$situacao = $item['SituaNome'];
										$situacaoClasse = 'badge badge-flat border-'.$item['SituaCor'].' text-'.$item['SituaCor'];
										
										print('
										<tr>
                                            <td>'.$item['CnBanNome'].'</td>
                                            <td>'.$item['BancoNome'].'</td>
                                            <td>'.$item['CnBanAgencia'].'</td>
                                            <td>'.$item['CnBanConta'].'</td>
											');
										
										print('<td><a href="#" onclick="atualizaCategoria(1,'.$item['CnBanId'].', \''.$item['CnBanNome'].'\',\''.$item['SituaChave'].'\', \'mudaStatus\');"><span class="badge '.$situacaoClasse.'">'.$situacao.'</span></a></td>');
										
										print('<td class="text-center">
												<div class="list-icons">
													<div class="list-icons list-icons-extended">
														<a href="#" onclick="atualizaCategoria('.$atualizar.','.$item['CnBanId'].', \''.$item['CnBanNome'].'\',\''.$item['SituaChave'].'\', \'edita\');" class="list-icons-item"><i class="icon-pencil7" data-popup="tooltip" data-placement="bottom" title="Editar"></i></a>
														<a href="#" onclick="atualizaCategoria('.$excluir.','.$item['CnBanId'].', \''.$item['CnBanNome'].'\',\''.$item['SituaChave'].'\', \'exclui\');" class="list-icons-item"><i class="icon-bin" data-popup="tooltip" data-placement="bottom" title="Exluir"></i></a>
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
				
				<form name="formContaBanco" method="post" action="contaBancoEdita.php">
					<input type="hidden" id="inputContaBancoId" name="inputContaBancoId" >
					<input type="hidden" id="inputContaBancoNome" name="inputContaBancoNome" >
					<input type="hidden" id="inputContaBancoStatus" name="inputContaBancoStatus" >
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
