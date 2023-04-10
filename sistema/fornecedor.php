<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Fornecedor';

include('global_assets/php/conexao.php');

$sql = "SELECT ForneId, ForneNome, ForneCpf, ForneCnpj, ForneTelefone, ForneCelular, ForneStatus, CategNome, SituaNome, SituaCor, SituaChave
		FROM Fornecedor
		JOIN Categoria on CategId = ForneCategoria
		JOIN Situacao on SituaId = ForneStatus
	    WHERE ForneEmpresa = ". $_SESSION['EmpreId'] ." 
		ORDER BY ForneNome ASC";
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
	<title>Lamparinas | Fornecedor</title>

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
			$('#tblFornecedor').DataTable( {
				"order": [[ 0, "asc" ]],
			    autoWidth: false,
				responsive: true,
			    columnDefs: [{ 
					orderable: true,   //Nome
					width: "40%",
					targets: [0]
				},
				{ 
					orderable: true,   //CPF/CNPJ
					width: "20%",
					targets: [1]
				},				
				{ 
					orderable: true,   //Telefone
					width: "10%",
					targets: [2]
				},
				{ 
					orderable: true,   //Categoria
					width: "20%",
					targets: [3]
				},
				{ 
					orderable: true,   //Situação
					width: "5%",
					targets: [4]
				},
				{ 
					orderable: false,  //Ações
					width: "5%",
					targets: [5]
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
		function atualizaFornecedor(Permission, ForneId, ForneNome, ForneStatus, Tipo){
			
				if (Tipo == 'imprime'){
					// alerta('Esse Termo de Referência já está finalizado e não pode ser excluído!','');

					document.getElementById('inputFornecedorCategoria').value = document.getElementById('cmbCategoria').value;
					
					document.formFornecedor.action = "fornecedorImprime.php";
					document.formFornecedor.setAttribute("target", "_blank");
				} else {
					document.getElementById('inputPermission').value = Permission;
					document.getElementById('inputFornecedorId').value = ForneId;
					document.getElementById('inputFornecedorNome').value = ForneNome;
					document.getElementById('inputFornecedorStatus').value = ForneStatus;
					console.log(ForneId, ForneNome, ForneStatus, Tipo)
							
					if (Tipo == 'edita'){	
						document.formFornecedor.action = "fornecedorEdita.php";		
					} else if (Tipo == 'mudaStatus'){
						document.formFornecedor.action = "fornecedorMudaSituacao.php";
					} else if (Tipo == 'anexo'){
						document.formFornecedor.action = "fornecedorAnexo.php";
					} else if (Tipo == 'socio'){
						document.formFornecedor.action = "fornecedorSocio.php";
					}else if (Tipo == 'exclui'){
						if(Permission){
							confirmaExclusao(document.formFornecedor, "Tem certeza que deseja excluir esse fornecedor?", "fornecedorExclui.php");
						}else{
							alerta('Permissão Negada!','');
							return false;
						}
					}
				}
				
				document.formFornecedor.submit();
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
								<h3 class="card-title">Relação de Fornecedores</h3>
							</div>

							<div class="card-body">
								<div class="row">
									<div class="col-lg-9">
										<p class="font-size-lg">A relação abaixo faz referência aos fornecedores da unidade <b><?php echo $_SESSION['UnidadeNome']; ?></b></p>
									</div>
									<div class="col-lg-3">
										<div class="text-right">
											<div class="dropdown p-0" style="float:right; margin-left: 5px;">										
												<a href="#collapse-imprimir-relacao" class="dropdown-toggle btn bg-slate-700 btn-icon" role="button" data-toggle="collapse" data-placement="bottom" data-container="body">
													<i class="icon-printer2"></i>																						
												</a>
											</div>
											<div>
												<?php 
													echo $inserir?"<a href='fornecedorNovo.php' class='btn btn-principal' role='button'>Novo Fornecedor</a>":"";
												?>
											</div>
										</div>

										<div class="collapse" id="collapse-imprimir-relacao" style="margin-top: 5px;">
											<div class="row">
												<div class="col-lg-12">
													<div class="form-group">												
														<select id="cmbCategoria" name="cmbCategoria" class="form-control form-control-select2">
															<option value="#">Filtrar por: Categoria (todas)</option>
															<?php 
																$sql = "SELECT CategId, CategNome
																		FROM Categoria
																		JOIN Situacao on SituaId = CategStatus		  
																		WHERE CategEmpresa = ".$_SESSION['EmpreId']." and SituaChave = 'ATIVO'
																		ORDER BY CategNome ASC";
																$result = $conn->query($sql);
																$rowCategoria = $result->fetchAll(PDO::FETCH_ASSOC);
																
																foreach ($rowCategoria as $item){															
																	print('<option value="'.$item['CategId'].'">'.$item['CategNome'].'</option>');
																}													
															?>
														</select>
													</div>
												
													<a href="#" onclick="atualizaFornecedor(1,0, '','', 'imprime');" class="form-control btn bg-slate-700 btn-icon" role="button" data-placement="bottom" data-container="body">
														<i class="icon-printer2"> Gerar PDF ou Imprimir</i>
													</a>
												</div>
											</div>
										</div>
									</div>	
								</div>
							</div>
							
							<table class="table" id="tblFornecedor">
								<thead>
									<tr class="bg-slate">
										<th>Nome</th>
										<th>CPF/CNPJ</th>
										<th>Telefone</th>										
										<th>Categoria</th>
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
										$documento = $item['ForneCnpj'] == NULL ? $item['ForneCpf'] : $item['ForneCnpj'];
										$telefone = $item['ForneCelular'] == NULL ? $item['ForneTelefone'] : $item['ForneCelular'];
										
										print('
										<tr>
											<td>'.$item['ForneNome'].'</td>
											<td>'.formatarCPF_Cnpj($documento).'</td>
											<td>'.$telefone.'</td>
											<td>'.$item['CategNome'].'</td>
											');
										
										print('<td><a href="#" onclick="atualizaFornecedor(1,'.$item['ForneId'].', \''.$item['ForneNome'].'\','.$situacaoChave.', \'mudaStatus\');"><span class="badge '.$situacaoClasse.'">'.$situacao.'</span></a></td>');
										
										print('<td class="text-center">
												<div class="list-icons">
													<div class="list-icons list-icons-extended">'.
														'<a href="#" onclick="atualizaFornecedor('.$atualizar.','.$item['ForneId'].', \''.$item['ForneNome'].'\','.$item['ForneStatus'].', \'edita\');" class="list-icons-item"><i class="icon-pencil7" data-popup="tooltip" data-placement="bottom" title="Editar"></i></a>
														<a href="#" onclick="atualizaFornecedor('.$excluir.','.$item['ForneId'].', \''.$item['ForneNome'].'\','.$item['ForneStatus'].', \'exclui\');" class="list-icons-item"><i class="icon-bin" data-popup="tooltip" data-placement="bottom" title="Exluir"></i></a>

														<div class="dropdown">													
															<a href="#" class="list-icons-item" data-toggle="dropdown">
																<i class="icon-menu9"></i>
															</a>
														
															<div class="dropdown-menu dropdown-menu-right">
															    <a href="#" onclick="atualizaFornecedor(1,'.$item['ForneId'].', \''.$item['ForneNome'].'\','.$item['ForneStatus'].', \'anexo\');" class="dropdown-item"><i class="icon-attachment" title="Anexos"></i> Anexos</a>');

																if ($item['ForneCpf'] == null)  {

																	print('<a href="#" onclick="atualizaFornecedor(1,'.$item['ForneId'].', \''.$item['ForneNome'].'\','.$item['ForneStatus'].', \'socio\');" class="dropdown-item"><i class="icon-users4" title="Sócios"></i> Sócios</a> ');
																}
		
															print('
																
															</div>
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
				
				<form name="formFornecedor" method="post">
					<input type="hidden" id="inputPermission" name="inputPermission" >
					<input type="hidden" id="inputFornecedorId" name="inputFornecedorId" >
					<input type="hidden" id="inputFornecedorNome" name="inputFornecedorNome" >
					<input type="hidden" id="inputFornecedorStatus" name="inputFornecedorStatus" >
					<input type="hidden" id="inputFornecedorCategoria" name="inputFornecedorCategoria" >
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
