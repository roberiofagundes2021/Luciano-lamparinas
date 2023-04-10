<?php 

include_once("sessao.php");

if(!$_SESSION['PerfiChave'] == "SUPER"){
	header("location:javascript://history.go(-1)");
}

$_SESSION['PaginaAtual'] = 'Empresa';

include('global_assets/php/conexao.php');

$sql = "SELECT EmpreId, EmpreCnpj, EmpreRazaoSocial, EmpreNomeFantasia, dbo.fnLicencaVencimento(EmpreId) as Licenca, SituaNome, SituaChave, SituaCor
		FROM Empresa
		JOIN Situacao on SituaId = EmpreStatus
		ORDER BY EmpreNomeFantasia ASC";
$result = $conn->query($sql);
$row = $result->fetchAll(PDO::FETCH_ASSOC);
$count = count($row);
//	var_dump($count);die;
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Empresa</title>

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
			$('#tblEmpresa').DataTable( {
				"order": [[ 0, "asc" ]],
			    autoWidth: false,
				responsive: true,
			    columnDefs: [
				{
					orderable: true,   //Nome Fantasia
					width: "27%",
					targets: [0]
				},
				{ 
					orderable: true,   //Razao Social
					width: "27%",
					targets: [1]
				},
				{ 
					orderable: true,   //CNPJ
					width: "14%",
					targets: [2]
				},
				{ 
					orderable: true,   //Situacao
					width: "10%",
					targets: [3]
				},
				{ 
					orderable: true,   //Fim Licenca
					width: "12%",
					targets: [4]
				},
				{ 
					orderable: true,   //Ações
					width: "10%",
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
				
				
		function atualizaEmpresa(Permission, EmpresaId, EmpresaNome, EmpresaStatus, Tipo){

			document.getElementById('inputPermission').value = Permission;
			document.getElementById('inputEmpresaId').value = EmpresaId;
			document.getElementById('inputEmpresaNome').value = EmpresaNome;
			document.getElementById('inputEmpresaStatus').value = EmpresaStatus;
			/*		
			//Aqui é só para preecher a $_SESSION['EmpresaId'] e $_SESSION['EmpresaNome'] para levar para licenca, unidade, setor, usuario
			$.ajax({
				type: "POST",
				url: "menuLeftSecundarioAjax.php",
				data: ('id='+EmpresaId+'&nome='+EmpresaNome)
			});*/
					
			if (Tipo == 'edita'){	
				document.formEmpresa.action = "empresaEdita.php";		
			} else if (Tipo == 'exclui'){
				if(Permission){
					confirmaExclusao(document.formEmpresa, "Tem certeza que deseja excluir essa empresa?", "empresaExclui.php");
				}	else{
					alerta('Permissão Negada!','');
					return false;
				}
			} else if (Tipo == 'mudaStatus'){
				document.formEmpresa.action = "empresaMudaSituacao.php";
			} else if (Tipo == 'licenca'){
				document.formEmpresa.action = "licenca.php";
			} else if (Tipo == 'unidade'){
				document.formEmpresa.action = "unidade.php";
			} else if (Tipo == 'localestoque'){
				document.formEmpresa.action = "localEstoque.php";
			} else if (Tipo == 'setor'){
				document.formEmpresa.action = "setor.php";
			} else if (Tipo == 'usuario') {
				document.formEmpresa.action = "usuario.php";
			} else if (Tipo == 'veiculo'){
				document.formEmpresa.action = "veiculo.php";
			} else if (Tipo == 'menu'){
				document.formEmpresa.action = "menu.php";
			} else if (Tipo == 'parametro'){
				document.formEmpresa.action = "parametro.php";
			}	
			
			document.formEmpresa.submit();
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
							<div class="card-header bg-white header-elements-inline">
								<h3 class="card-title">Relação de Empresas</h3>
								<div class="header-elements">
									<div class="list-icons">
										<a class="list-icons-item" data-action="collapse"></a>
										<a href="empresa.php" class="list-icons-item" data-action="reload"></a>
										<!--<a class="list-icons-item" data-action="remove"></a>-->
									</div>
								</div>
							</div>

							<div class="card-body">
								<div class="row">
									<div class="col-lg-9">
										<p class="font-size-lg">As empresas cadastradas abaixo estarão aptas a utilizar o sistema, desde que ativas e com licença vigente.</p>
									</div>
										<div class="col-lg-3">
											<div class="text-right"><a href="empresaNovo.php" class="btn btn-principal" role="button">Nova Empresa</a>
											</div>		
									 	</div>			 
								</div>
							</div>							

							<table id="tblEmpresa" class="table">
								<thead>
									<tr class="bg-slate">
										<th>Nome Fantasia</th>
										<th>Razão Social</th>
										<th>CNPJ</th>
										<th>Situação</th>
										<th>Fim Licença</th>
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
											<td>'.$item['EmpreNomeFantasia'].'</td>
											<td>'.$item['EmpreRazaoSocial'].'</td>
											<td>'.formatarCPF_Cnpj($item['EmpreCnpj']).'</td>');
										
										if ($_SESSION['EmpreId'] != $item['EmpreId']) {
											print('<td><a href="#" onclick="atualizaEmpresa(1,'.$item['EmpreId'].', \''.$item['EmpreNomeFantasia'].'\',\''.$item['SituaChave'].'\', \'mudaStatus\');"><span class="badge '.$situacaoClasse.'">'.$situacao.'</span></a></td>');
										} else {
											print('<td><a href="#" data-popup="tooltip" data-trigger="focus" title="Essa empresa está sendo usada por você no momento"><span class="badge '.$situacaoClasse.'">'.$situacao.'</span></a></td>');
										}
										
										print('<td><span>'.$item['Licenca'].'</span></td>');
										
										print('<td class="text-center">
												<div class="list-icons">
													<div class="list-icons list-icons-extended">
														<a href="#" onclick="atualizaEmpresa('.$atualizar.','.$item['EmpreId'].', \''.$item['EmpreNomeFantasia'].'\',\''.$item['SituaChave'].'\', \'edita\');" class="list-icons-item"><i class="icon-pencil7"></i></a>
														<a href="#" onclick="atualizaEmpresa('.$excluir.','.$item['EmpreId'].', \''.$item['EmpreNomeFantasia'].'\',\''.$item['SituaChave'].'\', \'exclui\');" class="list-icons-item"><i class="icon-bin"></i></a>
														<div class="dropdown">													
															<a href="#" class="list-icons-item" data-toggle="dropdown">
																<i class="icon-menu9"></i>
															</a>

															<div class="dropdown-menu dropdown-menu-right">
																<a href="#" onclick="atualizaEmpresa(1,'.$item['EmpreId'].', \''.$item['EmpreNomeFantasia'].'\',\''.$item['SituaChave'].'\', \'licenca\');" class="dropdown-item"><i class="icon-certificate"></i> Licença</a>
																<a href="#" onclick="atualizaEmpresa(1,'.$item['EmpreId'].', \''.$item['EmpreNomeFantasia'].'\',\''.$item['SituaChave'].'\', \'unidade\');" class="dropdown-item"><i class="icon-home7"></i> Unidade</a>
																<a href="#" onclick="atualizaEmpresa(1,'.$item['EmpreId'].', \''.$item['EmpreNomeFantasia'].'\',\''.$item['SituaChave'].'\', \'setor\');" class="dropdown-item"><i class="icon-store"></i> Setor</a>
																<a href="#" onclick="atualizaEmpresa(1,'.$item['EmpreId'].', \''.$item['EmpreNomeFantasia'].'\',\''.$item['SituaChave'].'\', \'localestoque\');" class="dropdown-item"><i class="icon-box"></i> Local de Estoque</a>																
																<a href="#" onclick="atualizaEmpresa(1,'.$item['EmpreId'].', \''.$item['EmpreNomeFantasia'].'\',\''.$item['SituaChave'].'\', \'usuario\');" class="dropdown-item"><i class="icon-user-plus"></i> Usuários</a>
																<!--<a href="#" onclick="atualizaEmpresa(1,'.$item['EmpreId'].', \''.$item['EmpreNomeFantasia'].'\',\''.$item['SituaChave'].'\', \'menu\');" class="dropdown-item"><i class="icon-menu2"></i> Menu</a>-->
																<a href="#" onclick="atualizaEmpresa(1,'.$item['EmpreId'].', \''.$item['EmpreNomeFantasia'].'\',\''.$item['SituaChave'].'\', \'parametro\');" class="dropdown-item"><i class="icon-equalizer"></i> Parâmetro</a>
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
				
				<form name="formEmpresa" method="post">
					<input type="hidden" id="inputPermission" name="inputPermission" >
					<input type="hidden" id="inputEmpresaId" name="inputEmpresaId" >
					<input type="hidden" id="inputEmpresaNome" name="inputEmpresaNome" >
					<input type="hidden" id="inputEmpresaStatus" name="inputEmpresaStatus" >
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
