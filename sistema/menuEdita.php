<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Editar Menu';

include('global_assets/php/conexao.php');
		
if(isset($_POST['inputMenuId'])){
	
	$iMenu = $_POST['inputMenuId'];
        	
	try{
		
		$sql = "SELECT MenuId, MenuNome, MenuUrl, MenuIco, MenuHome, MenuModulo, MenuPai, MenuLevel, MenuOrdem
				FROM Menu
				WHERE MenuId = $iMenu ";
		$result = $conn->query("$sql");
		$row = $result->fetch(PDO::FETCH_ASSOC);
		
	} catch(PDOException $e) {
		echo 'Error: ' . $e->getMessage();
	}
	
	$_SESSION['msg'] = array();
} else {
	irpara("menu.php");
}

if(isset($_POST['inputNome'])){
	
	try{
				
		$sql = "UPDATE Menu SET MenuNome = :sNome, MenuUrl = :sUrl, MenuIco = :sIco, MenuHome = :bHome,  
				MenuModulo = :iModulo, MenuPai = :iPai, MenuLevel = :iLevel, MenuOrdem = :iOrdem, 
				MenuUsuarioAtualizador = :iUsuarioAtualizador
				WHERE MenuId = :iMenu";
		$result = $conn->prepare($sql);
		
		$aPai = explode('#',  $_POST['cmbPai']);
		$pai = intval ($aPai[0]);
		$level = intval ($aPai[1]);		

		$result->execute(array(
						':sNome' => $_POST['inputNome'],
						':sUrl' => $_POST['inputUrl'],
						':sIco' => $_POST['cmbIco'],
						':bHome' => $_POST['inputHomeSim'],
						':iModulo' => $_POST['cmbModulo'],
						':iPai' => $pai,
						':iLevel' => $level,
						':iOrdem' => $_POST['cmbOrdem'],
						':iUsuarioAtualizador' => $_SESSION['UsuarId'],
						':iMenu'	=> $_POST['inputMenuId']
						));
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Menu alterado!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao alterar menu!!!";
		$_SESSION['msg']['tipo'] = "error";		
		
		echo 'Error: ' . $e->getMessage();exit;
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
	
	<!-- Adicionando Javascript -->
    <script type="text/javascript" >
        
        function selecionaHome(tipo) {
			if (tipo == 'SIM'){
				document.getElementById('inputHomeSim').value = 1;
			} else {
				document.getElementById('inputHomeSim').value = 0;				
			}
		}

    </script>
	
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
					
					<form name="formMenu" id="formMenu" method="post" class="form-validate" action="menuEdita.php">
						<div class="card-header header-elements-inline">
							<h5 class="text-uppercase font-weight-bold">Editar Menu</h5>
						</div>
						
						<input type="hidden" id="inputMenuId" name="inputMenuId" value="<?php echo $row['MenuId']; ?>" >
						<input type="hidden" id="inputHomeSim" name="inputHomeSim" value="<?php echo $row['MenuHome']; ?>" >
						
						<div class="card-body">								
							<div class="row">
								<div class="col-lg-5">
									<div class="form-group">
										<label for="inputNome">Nome</label>
										<input type="text" id="inputNome" name="inputNome" class="form-control" placeholder="Nome" value="<?php echo $row['MenuNome']; ?>" required>
									</div>
								</div>
								
								<div class="col-lg-5">
									<div class="form-group">
										<label for="inputUrl">URL</label>
										<input type="text" id="inputUrl" name="inputUrl" class="form-control" placeholder="URL" value="<?php echo $row['MenuUrl']; ?>" required>
									</div>
								</div>
								
								<div class="col-lg-2">
									<div class="form-group">
										<label for="cmbIco">Ícone</label>
										<select id="cmbIco" name="cmbIco" class="form-control select-icons">
											<option value="#">Selecione um ícone</option>
											<option value="wordpress2" data-icon="icon-wordpress2" <?php if ($row['MenuIco'] == 'wordpress2') echo "selected"; ?>>icon-wordpress2</option>
											<option value="icon-home2" data-icon="icon-home2" <?php if ($row['MenuIco'] == 'icon-home2') echo "selected"; ?>>icon-home2</option>
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
											$rowModulo = $result->fetchAll(PDO::FETCH_ASSOC);
											
											foreach ($rowModulo as $item){
												$seleciona = $item['ModulId'] == $row['MenuModulo'] ? "selected" : "";
												print('<option value="'.$item['ModulId'].'" '. $seleciona .'>'.$item['ModulNome'].'</option>');
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
											$rowPai = $result->fetchAll(PDO::FETCH_ASSOC);
											
											print('<option value="0#0">Principal</option>');
											
											foreach ($rowPai as $item){
												$seleciona = $item['MenuId'] == $row['MenuId'] ? "selected" : "";
												print('<option value="'.$item['MenuId'].'#'.$item['MenuLevel'].'" '. $seleciona .'>'.$item['MenuNome'].'</option>');
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
											$rowOrdem = $result->fetchAll(PDO::FETCH_ASSOC);
											
											print('<option value="0"> 0 - Principal</option>');
											
											$ordem = 1;
											foreach ($rowOrdem as $item){
												$seleciona = $item['MenuOrdem'] == $row['MenuOrdem'] ? "selected" : "";
												print('<option value="'.$item['MenuId'].'" '. $seleciona .'>'.$ordem . ' - ' .$item['MenuNome'].'</option>');
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
												<input type="radio" id="inputHome" name="inputHome" class="form-input-styled" data-fouc <?php if ($row['MenuHome'] == 1) echo "checked"; ?> onclick="selecionaHome('SIM')">
												Sim
											</label>
										</div>
										<div class="form-check form-check-inline">
											<label class="form-check-label">
												<input type="radio" id="inputHome" name="inputHome" class="form-input-styled" data-fouc <?php if ($row['MenuHome'] == 0) echo "checked"; ?> onclick="selecionaHome('NAO')">
												Não
											</label>
										</div>										
									</div>									
								</div>
							</div>							

							<div class="row" style="margin-top: 30px;">
								<div class="col-lg-12">								
									<div class="form-group">
										<button class="btn btn-lg btn-principal" type="submit">Alterar</button>
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
