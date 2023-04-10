<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Empresa';

include('global_assets/php/conexao.php');

$sql = ("SELECT EmpreId, EmpreCnpj, EmpreRazaoSocial, EmpreNomeFantasia, EmpreStatus, dbo.fnLicencaVencimento(EmpreId) as Licenca
		 FROM Empresa
		 LEFT JOIN Licenca on LicenEmpresa = EmpreId
		 ORDER BY EmpreNomeFantasia ASC");
$result = $conn->query("$sql");
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
	
	<script src="global_assets/js/plugins/notifications/jgrowl.min.js"></script>
	<script src="global_assets/js/plugins/notifications/noty.min.js"></script>
	<script src="global_assets/js/demo_pages/extra_jgrowl_noty.js"></script>
	<script src="global_assets/js/demo_pages/components_popups.js"></script>
	
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
	
	<script src="global_assets/js/demo_pages/form_layouts.js"></script>
	<script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>	
	<!-- /theme JS files -->	
	
	<script>
				
		function atualizaEmpresa(EmpresaId, EmpresaNome, EmpresaStatus, Tipo){

			document.getElementById('inputEmpresaId').value = EmpresaId;
			document.getElementById('inputEmpresaNome').value = EmpresaNome;
			document.getElementById('inputEmpresaStatus').value = EmpresaStatus;
					
			if (Tipo == 'edita'){	
				document.formEmpresa.action = "empresaEdita.php";		
			} else if (Tipo == 'exclui'){
				confirmaExclusao(document.formEmpresa, "Tem certeza que deseja excluir essa empresa?", "empresaExclui.php");
			} else if (Tipo == 'mudaStatus'){
				document.formEmpresa.action = "empresaMudaSituacao.php";
			} else if (Tipo == 'usuario') {
				document.formEmpresa.action = "empresaUsuario.php";
			} else if (Tipo == 'licenca'){
				document.formEmpresa.action = "licenca.php";
			}
			
			document.formEmpresa.submit();
		}
			
	</script>

</head>

<body class="navbar-top sidebar-xs">

	<?php include_once("topo.php"); ?>	

	<!-- Page content -->
	<div class="page-content">
		
		<?php include_once("menu-left.php"); ?>

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

				<!-- Sidebar Empresa -->
				<div class="card"  style="padding-top:10px;">
					<div class="card-header bg-transparent header-elements-inline">
						<span class="text-uppercase font-size-sm font-weight-semibold">Empresa</span>
					</div>

					<div class="card-body">
						<form action="#">
							<div class="form-group-feedback form-group-feedback-right">
								<select id="cmbEmpresa" name="cmbEmpresa" class="form-control form-control-select2">
									<?php 
										$sql = ("SELECT EmpreId, EmpreNomeFantasia
												 FROM Empresa
												 WHERE EmpreStatus = 1
												 ORDER BY EmpreNomeFantasia ASC");
										$result = $conn->query("$sql");
										$row = $result->fetchAll(PDO::FETCH_ASSOC);
										
										foreach ($row as $item){
											print('<option value="'.$item['EmpreId'].'">'.$item['EmpreNomeFantasia'].'</option>');
										}
									
									?>
								</select>
							</div>
						</form>
					</div>
				</div>
				<!-- /sidebar Empresa -->


				<!-- Sub navigation -->
				<div class="card mb-2">
					<div class="card-header bg-transparent header-elements-inline">
						<span class="text-uppercase font-size-sm font-weight-semibold">Navigation</span>
						<div class="header-elements">
							<div class="list-icons">
		                		<a class="list-icons-item" data-action="collapse"></a>
	                		</div>
                		</div>
					</div>

					<div class="card-body p-0">
						<ul class="nav nav-sidebar" data-nav-type="accordion">
							<li class="nav-item-header">Empreory title</li>
							<li class="nav-item">
								<a href="#" class="nav-link"><i class="icon-googleplus5"></i> Gerenciar Licença</a>
							</li>
							<li class="nav-item">
								<a href="#" class="nav-link"><i class="icon-portfolio"></i> Adicionar Usuários</a>
							</li>
							<li class="nav-item">
								<a href="#" class="nav-link">
									<i class="icon-user-plus"></i>
									Gerenciar Menu
									<span class="badge bg-primary badge-pill ml-auto">2</span>
								</a>
							</li>
							<li class="nav-item-divider"></li>
							<li class="nav-item nav-item-submenu">
								<a href="#" class="nav-link"><i class="icon-cog3"></i> Menu levels</a>
								<ul class="nav nav-group-sub">
									<li class="nav-item"><a href="#" class="nav-link">Second level</a></li>
									<li class="nav-item nav-item-submenu">
										<a href="#" class="nav-link">Second level with child</a>
										<ul class="nav nav-group-sub">
											<li class="nav-item"><a href="#" class="nav-link">Third level</a></li>
											<li class="nav-item nav-item-submenu">
												<a href="#" class="nav-link">Third level with child</a>
												<ul class="nav nav-group-sub">
													<li class="nav-item"><a href="#" class="nav-link">Fourth level</a></li>
													<li class="nav-item"><a href="#" class="nav-link">Fourth level</a></li>
												</ul>
											</li>
											<li class="nav-item"><a href="#" class="nav-link">Third level</a></li>
										</ul>
									</li>
									<li class="nav-item"><a href="#" class="nav-link">Second level</a></li>
								</ul>
							</li>
						</ul>
					</div>
				</div>
				<!-- /sub navigation -->

			</div>
			<!-- /sidebar content -->

		</div>
		<!-- /secondary sidebar -->		
		
		
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
								<p class="font-size-lg">As empresas cadastradas abaixo estarão aptas a utilizar o sistema, desde que ativas e com licença vigente.</p>
								<div class="text-right"><a href="empresaNovo.php" class="btn btn-principal" role="button">Nova Empresa</a></div>
							</div>							

							<table class="table datatable-responsive">
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

										$situacao = $item['EmpreStatus'] ? 'Ativo' : 'Inativo';
										$situacaoClasse = $item['EmpreStatus'] ? 'badge-success' : 'badge-secondary';
										
										print('
										<tr>
											<td>'.$item['EmpreNomeFantasia'].'</td>
											<td>'.$item['EmpreRazaoSocial'].'</td>
											<td>'.formatarCPF_Cnpj($item['EmpreCnpj']).'</td>');
										
										if ($_SESSION['EmpreId'] != $item['EmpreId']) {
											print('<td><a href="#" onclick="atualizaEmpresa('.$item['EmpreId'].', \''.$item['EmpreNomeFantasia'].'\','.$item['EmpreStatus'].', \'mudaStatus\');"><span class="badge '.$situacaoClasse.'">'.$situacao.'</span></a></td>');
										} else {
											print('<td><a href="#" data-popup="tooltip" data-trigger="focus" title="Essa empresa está sendo usada por você no momento"><span class="badge '.$situacaoClasse.'">'.$situacao.'</span></a></td>');
										}
										
										print('<td><span>'.$item['Licenca'].'</span></td>');
										
										print('<td class="text-center">
												<div class="list-icons">
													<div class="list-icons list-icons-extended">
														<a href="#" onclick="atualizaEmpresa('.$item['EmpreId'].', \''.$item['EmpreNomeFantasia'].'\','.$item['EmpreStatus'].', \'edita\');" class="list-icons-item"><i class="icon-pencil7"></i></a>
														<a href="#" onclick="atualizaEmpresa('.$item['EmpreId'].', \''.$item['EmpreNomeFantasia'].'\','.$item['EmpreStatus'].', \'exclui\');" class="list-icons-item"><i class="icon-bin"></i></a>
														<div class="dropdown">													
															<a href="#" class="list-icons-item" data-toggle="dropdown">
																<i class="icon-menu9"></i>
															</a>

															<div class="dropdown-menu dropdown-menu-right">
																<a href="#" onclick="atualizaEmpresa('.$item['EmpreId'].', \''.$item['EmpreNomeFantasia'].'\','.$item['EmpreStatus'].', \'usuario\');" class="dropdown-item"><i class="icon-user-plus"></i> Adicionar usuários</a>
																<a href="#" onclick="atualizaEmpresa('.$item['EmpreId'].', \''.$item['EmpreNomeFantasia'].'\','.$item['EmpreStatus'].', \'licenca\');" class="dropdown-item"><i class="icon-certificate"></i> Gerenciar Licença</a>
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
				
				<form name="formEmpresa" method="post" action="empresaEdita.php">
					<input type="hidden" id="inputEmpresaId" name="inputEmpresaId" >
					<input type="hidden" id="inputEmpresaNome" name="inputEmpresaNome" >
					<input type="hidden" id="inputEmpresaStatus" name="inputEmpresaStatus" >
				</form>

			</div>
			<!-- /content area -->

			<?php //include_once("footer.php"); ?>

		</div>
		<!-- /main content -->
		
	</div>
	<!-- /page content -->
	
	<?php include_once("alerta.php"); ?>

</body>
</html>
