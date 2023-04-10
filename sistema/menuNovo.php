<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Novo Menu';

include('global_assets/php/conexao.php');

if(isset($_POST['inputNome'])){

	try{
		
		$sql = "INSERT INTO Menu (MenuNome, MenuUrl, MenuIco, MenuHome, MenuModulo, MenuPai, MenuLevel, MenuOrdem, MenuStatus, MenuUsuarioAtualizador, MenuEmpresa)
				VALUES (:sNome, :sUrl, :sIco, :bHome, :iModulo, :iPai, :iLevel, :iOrdem, :bStatus, :iUsuarioAtualizador, :iEmpresa)";
		$result = $conn->prepare($sql);
		
		$aPai = explode('#',  $_POST['cmbPai']);
		//var_dump($aPai);die;
		$pai = intval ($aPai[0]);
		$level = intval ($aPai[1]);
		
		$result->execute(array(
						':sNome' => $_POST['inputNome'],
						':sUrl' => $_POST['inputUrl'],
						':sIco' => $_POST['cmbIco'],
						':bHome' =>$_POST['inputHome'] == "on" ? 1 : 0,
						':iModulo' => $_POST['cmbModulo'],
						':iPai' => $pai,
						':iLevel' => $level,
						':iOrdem' => $_POST['cmbOrdem'],
						':bStatus' => 1,
						':iUsuarioAtualizador' => $_SESSION['UsuarId'],
						':iEmpresa' => $_SESSION['EmpresaId'],
						));
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Menu incluído!!!";
		$_SESSION['msg']['tipo'] = "success";	
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao incluir menu!!!";
		$_SESSION['msg']['tipo'] = "error";	
		
		echo 'Error: ' . $e->getMessage();die;
	}
	
	irpara("menu.php");
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Menu</title>

	<?php include_once("head.php"); ?>
	
	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
	
	<script src="global_assets/js/demo_pages/form_layouts.js"></script>
	<script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>
	
	<!-- /theme JS files -->


</head>

<body class="navbar-top sidebar-xs">

	<?php include_once("topo.php"); ?>	

	<!-- Page content -->
	<div class="page-content">
		
		<?php include_once("menu-left.php"); ?>
		
		<?php include_once("menuLeftSecundario.php"); ?>

		<!-- Main content -->
		<div class="content-wrapper">

			<?php include_once("cabecalho.php"); ?>	

			<!-- Content area -->
			<div class="content">
				
				<!-- Info blocks -->
				<div class="card">
					
					<form name="formMenu" method="post" class="form-validate" action="menuNovo.php">
						<div class="card-header header-elements-inline">
							<h5 class="text-uppercase font-weight-bold">Cadastrar Novo Menu</h5>
						</div>
						
						<div class="card-body">								
							<div class="row">
								<div class="col-lg-5">
									<div class="form-group">
										<label for="inputNome">Nome</label>
										<input type="text" id="inputNome" name="inputNome" class="form-control" placeholder="Nome" required>
									</div>
								</div>
								
								<div class="col-lg-5">
									<div class="form-group">
										<label for="inputUrl">URL</label>
										<input type="text" id="inputUrl" name="inputUrl" class="form-control" placeholder="URL" required>
									</div>
								</div>
								
								<div class="col-lg-2">
									<div class="form-group">
										<label for="cmbIco">Ícone</label>
										<select id="cmbIco" name="cmbIco" class="form-control select-icons">
											<option value="wordpress2" data-icon="icon-wordpress2">icon-wordpress2</option>
											<option value="icon-home2" data-icon="icon-home2">icon-home2</option>
										</select>
									</div>
								</div>
							</div>
							
							<div class="row">
								<div class="col-lg-4">
									<label for="cmbModulo">Módulo</label>
									<select id="cmbModulo" name="cmbModulo" class="form-control form-control-select2">
										<option value="#">Selecione</option>
										<?php 
											$sql = ("SELECT ModulId, ModulNome
													 FROM Modulo
													 WHERE ModulStatus = 1
													 ORDER BY ModulNome ASC");
											$result = $conn->query("$sql");
											$row = $result->fetchAll(PDO::FETCH_ASSOC);
											
											foreach ($row as $item){
												print('<option value="'.$item['ModulId'].'">'.$item['ModulNome'].'</option>');
											}
										
										?>
									</select>
								</div>
							
								<div class="col-lg-4">
									<label for="cmbPai">Menu Pai</label>
									<select id="cmbPai" name="cmbPai" class="form-control form-control-select2">
										<option value="#">Selecione</option>
										<?php 
											$sql = ("SELECT MenuId, MenuNome, MenuLevel
													 FROM Menu
													 WHERE MenuStatus = 1 and MenuEmpresa = ".$_SESSION['EmpresaId']."
													 ORDER BY MenuNome ASC");
											$result = $conn->query("$sql");
											$row = $result->fetchAll(PDO::FETCH_ASSOC);
											
											print('<option value="0#0">Principal</option>');
											
											foreach ($row as $item){
												print('<option value="'.$item['MenuId'].'#'.$item['MenuLevel'].'">'.$item['MenuNome'].'</option>');
											}
										
										?>
									</select>
								</div>
								
								<div class="col-lg-4">
									<label for="cmbOrdem">Menu Ordem (Depois de)</label>
									<select id="cmbOrdem" name="cmbOrdem" class="form-control form-control-select2">
										<option value="#">Selecione</option>
										<?php 
											$sql = ("SELECT MenuId, MenuNome, MenuLevel, MenuOrdem
													 FROM Menu
													 WHERE MenuStatus = 1 and MenuEmpresa = ".$_SESSION['EmpresaId']."
													 ORDER BY MenuNome ASC");
											$result = $conn->query("$sql");
											$row = $result->fetchAll(PDO::FETCH_ASSOC);
											
											print('<option value="0"> 0 - Principal</option>');
											
											$ordem = 1;
											foreach ($row as $item){
												print('<option value="'.$item['MenuId'].'">'.$ordem . ' - ' .$item['MenuNome'].'</option>');
												$ordem++;
											}
										
										?>
									</select>
								</div>
							</div>
							
							<div class="row" style="margin-top:30px;">
								<div class="col-lg-4">
									<label for="inputHome">Página Inicial</label>
									<div class="form-group">							
										<div class="form-check form-check-inline">
											<label class="form-check-label">
												<input type="radio" id="inputHome" name="inputHome" class="form-input-styled" data-fouc>
												Sim
											</label>
										</div>
										<div class="form-check form-check-inline">
											<label class="form-check-label">
												<input type="radio" id="inputHome" name="inputHome" class="form-input-styled" checked data-fouc>
												Não
											</label>
										</div>										
									</div>									
								</div>
							</div>							

							<div class="row" style="margin-top: 30px;">
								<div class="col-lg-12">								
									<div class="form-group">
										<button class="btn btn-lg btn-principal" type="submit">Incluir</button>
										<a href="menu.php" class="btn btn-basic" role="button">Cancelar</a>
									</div>
								</div>
							</div>

						</div>
						<!-- /card-body -->
					</form>								
					
				</div>
				<!-- /info blocks -->

			</div>
			<!-- /content area -->			
			
			<?php include_once("footer.php"); ?>

		</div>
		<!-- /main content -->

	</div>
	<!-- /page content -->

</body>
</html>
