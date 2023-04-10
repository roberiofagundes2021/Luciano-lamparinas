<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Profissionais';

include('global_assets/php/conexao.php');

$sql = "SELECT ProfiId, ProfiNome,ProfiTipo, ProfiCpf, ProfiCnpj, ProfiTelefone, ProfiCelular, ProfiStatus, 
		ProfiEmail, SituaNome, SituaCor, SituaChave, UsuarLogin
        FROM Profissional
        JOIN Situacao on SituaId = ProfiStatus
		LEFT JOIN Usuario on UsuarId = ProfiUsuario
        WHERE ProfiUnidade = ". $_SESSION['UnidadeId'] ." 
        ORDER BY ProfiNome ASC";
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
	<title>Lamparinas | Profissionais</title>

	<?php include_once("head.php"); ?>
	
	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/tables/datatables/datatables.min.js"></script>
	<script src="global_assets/js/plugins/tables/datatables/extensions/responsive.min.js"></script>
	
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
	<script src="global_assets/js/demo_pages/form_select2.js"></script>
	
	<script src="global_assets/js/demo_pages/form_layouts.js"></script>
	<script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>	

	<script src="global_assets/js/demo_pages/datatables_responsive.js"></script>
	<script src="global_assets/js/demo_pages/datatables_sorting.js"></script>
	<script src="global_assets/js/lamparinas/stop-back.js"></script>
	
	<!-- /theme JS files -->	
	
	<script type="text/javascript">
		
		$(document).ready(function() {
			
			/* Início: Tabela Personalizada */
			$('#tblProfissional').DataTable( {
				"order": [[ 0, "asc" ]],
			    autoWidth: false,
				responsive: true,
			    columnDefs: [{ 
					orderable: true,   //Nome
					width: "30%",
					targets: [0]
				},
				{ 
					orderable: true,   //CPF/CNPJ
					width: "15%",
					targets: [1]
				},				
				{ 
					orderable: true,   //Telefone
					width: "15%",
					targets: [2]
				},
				{ 
					orderable: true,   //E-mail
					width: "20%",
					targets: [3]
				},
				{ 
					orderable: true,   //Usuario
					width: "10%",
					targets: [4]
				},
				{ 
					orderable: true,   //Situação
					width: "5%",
					targets: [5]
				},
				{ 
					orderable: false,  //Ações
					width: "5%",
					targets: [6]
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
		function atualizaProfissional(Permission, ProfiId, ProfiNome, ProfiStatus, Tipo){

			if (Tipo == 'imprime'){			
				document.formProfissional.action = "profissionalImprime.php";
				document.formProfissional.setAttribute("target", "_blank");
			} else {
				document.getElementById('inputPermission').value = Permission;
				document.getElementById('inputProfissionalId').value = ProfiId;
				document.getElementById('inputProfissionalNome').value = ProfiNome;
				document.getElementById('inputProfissionalStatus').value = ProfiStatus;
				console.log(ProfiId, ProfiNome, ProfiStatus, Tipo)
						
				if (Tipo == 'edita'){	
					document.formProfissional.action = "profissionalEdita.php";		
				} else if (Tipo == 'mudaStatus'){
					document.formProfissional.action = "profissionalMudaSituacao.php";
				} else if (Tipo == 'anexo'){
					document.formProfissional.action = "profissionalAnexo.php";
				}else if (Tipo == 'exclui'){
					if(Permission){
						confirmaExclusao(document.formProfissional, "Tem certeza que deseja excluir esse profissional", "profissionalExclui.php");
					}else{
						alerta('Permissão Negada!','');
						return false;
					}
				} else if(Tipo == 'agenda'){
					document.formProfissional.action = "profissionalAgenda.php";
				} else if(Tipo == 'servico'){
					document.formProfissional.action = "profissionalServico.php";
				}
			}
			document.formProfissional.submit();
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
								<h3 class="card-title">Relação de Profissionais</h3>
							</div>

							<div class="card-body">
								<div class="row">
									<div class="col-lg-9">
										<p class="font-size-lg">A relação abaixo faz referência aos profissionais da unidade <b><?php echo $_SESSION['UnidadeNome']; ?></b></p>
									</div>
									<div class="col-lg-3">
										<div class="text-right">
											<div class="p-0" style="float:right; margin-left: 5px;">										
												<a href="#" onclick="atualizaProfissional(1, 0, '','', 'imprime');" class="form-control btn bg-slate-700 btn-icon" role="button" data-placement="bottom" data-container="body">
													<i class="icon-printer2"></i>
												</a>
											</div>
											<div>
												<a href="profissionalNovo.php" class="btn btn-principal" role="button">Novo Profissional</a>
											</div>
										</div>

									</div>	
								</div>
							</div>
							
							<table class="table" id="tblProfissional">
								<thead>
									<tr class="bg-slate">
										<th>Nome</th>
										<th>CPF/CNPJ</th>
										<th>Celular</th>										
										<th>E-mail</th>
										<th>Usuário</th>
										<th>Situação</th>
										<th class="text-center">Ações</th>
									</tr>
								</thead>
								<tbody>
								<?php

									foreach ($row as $item){
										
										$situacao = $item['SituaChave'] == 'ATIVO' ? 'Ativo' : 'Inativo';
										$situacaoClasse = 'badge badge-flat border-'.$item['SituaCor'].' text-'.$item['SituaCor'];
										$situacaoChave ='\''.$item['SituaChave'].'\'';
										$documento = $item['ProfiTipo'] == 'F' || $item['ProfiTipo'] == 'f' ? $item['ProfiCpf'] : $item['ProfiCnpj'];
										$telefone = $item['ProfiCelular'] == NULL ? $item['ProfiTelefone'] : $item['ProfiCelular'];
										
										print('
										<tr>
											<td>'.$item['ProfiNome'].'</td>
											<td>'.formatarCPF_Cnpj($documento).'</td>
											<td>'.$telefone.'</td>
											<td>'.$item['ProfiEmail'].'</td>
											<td>'.$item['UsuarLogin'].'</td>
											');
										
										print('<td><a href="#" onclick="atualizaProfissional(1,'.$item['ProfiId'].', \''.$item['ProfiNome'].'\','.$situacaoChave.', \'mudaStatus\');"><span class="badge '.$situacaoClasse.'">'.$situacao.'</span></a></td>');
										
										print('<td class="text-center">
												<div class="list-icons">
													<div class="list-icons list-icons-extended">
															<a href="#" onclick="atualizaProfissional('.$atualizar.','.$item['ProfiId'].', \''.$item['ProfiNome'].'\','.$item['ProfiStatus'].', \'edita\');" class="list-icons-item"><i class="icon-pencil7" data-popup="tooltip" data-placement="bottom" title="Editar"></i></a>
															<a href="#" onclick="atualizaProfissional('.$excluir.','.$item['ProfiId'].', \''.$item['ProfiNome'].'\','.$item['ProfiStatus'].', \'exclui\');" class="list-icons-item"><i class="icon-bin" data-popup="tooltip" data-placement="bottom" title="Exluir"></i></a>														
														<div class="dropdown">													
															<a href="#" class="list-icons-item" data-toggle="dropdown">
																<i class="icon-menu9"></i>
															</a>
														
															<div class="dropdown-menu dropdown-menu-right">
															    <a href="#" onclick="atualizaProfissional(1,'.$item['ProfiId'].', \''.$item['ProfiNome'].'\','.$item['ProfiStatus'].', \'anexo\');" class="dropdown-item"><i class="icon-attachment" title="Anexos"></i> Anexos</a>
															<div class="dropdown-divider"></div>
																<a href="#" onclick="atualizaProfissional(1,'.$item['ProfiId'].', \''.$item['ProfiNome'].'\','.$item['ProfiStatus'].', \'historico\');" class="dropdown-item" title="Histórico"><i class="icon-stack"></i> Histórico</a>
																<a href="#" onclick="atualizaProfissional(1,'.$item['ProfiId'].', \''.$item['ProfiNome'].'\','.$item['ProfiStatus'].', \'agenda\');" class="dropdown-item" title="Agenda"><i class="icon-calendar2"></i> Agenda</a>
																<a href="#" onclick="atualizaProfissional(1,'.$item['ProfiId'].', \''.$item['ProfiNome'].'\','.$item['ProfiStatus'].', \'servico\');" class="dropdown-item" title="Serviço"><i class="icon-diff"></i> Serviço</a>
														</div>
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
				
				<form name="formProfissional" method="post">
					<input type="hidden" id="inputPermission" name="inputPermission" >
					<input type="hidden" id="inputProfissionalId" name="inputProfissionalId" >
					<input type="hidden" id="inputProfissionalNome" name="inputProfissionalNome" >
					<input type="hidden" id="inputProfissionalStatus" name="inputProfissionalStatus" >
					<input type="hidden" id="inputProfissionalEmail" name="inputProfissionalEmail" >
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
