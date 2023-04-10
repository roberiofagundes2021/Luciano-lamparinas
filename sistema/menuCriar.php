<?php 

include_once("sessao.php");

if(isset($_POST['id'])){
	$id = $_POST['id'];
	$isMenu = $_POST['isMenu'];

	$MenuId='';
	$MenuNome='';
	$MenuOrdem='';

	if($isMenu == 'menu'){
		$sql = "SELECT MenuId,MenuNome,MenuUrl,MenuIco,ModulNome,MenuModulo,MenuModulo,MenuPai,MenuLevel,MenuOrdem,MenuSubMenu,MenuSetorPublico,
		MenuSetorPrivado,MenuPosicao,MenuUsuarioAtualizador,MenuStatus, SituaId
		FROM Menu
		JOIN Situacao ON SituaId = MenuStatus
		JOIN Modulo ON ModulId = MenuModulo
		WHERE SituaChave = 'ATIVO' and MenuId = $id";
		$result = $conn->query($sql);
		$rowMenu = $result->fetch(PDO::FETCH_ASSOC);
	} else {
		$sql = "SELECT ModulId, ModulNome, ModulOrdem, SituaNome, SituaId
		FROM Modulo
		JOIN Situacao ON SituaId = ModulStatus
		WHERE ModulId = $id";
		$result = $conn->query($sql);
		$rowMenu = $result->fetch(PDO::FETCH_ASSOC);
	}
}
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
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
	<script src="global_assets/js/demo_pages/form_select2.js"></script>

	<script src="global_assets/js/demo_pages/form_layouts.js"></script>
	<script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>
	
	<script src="global_assets/js/plugins/forms/inputs/inputmask.js"></script>		
	<!-- /theme JS files -->	

	<!-- Validação -->
	<script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
	<script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
	<script src="global_assets/js/demo_pages/form_validation.js"></script>	

	<!-- Adicionando Javascript -->
    <script type="text/javascript" >
	
        $(document).ready(function() {
			if($('#isMenuOrModulo').val() == 'menu'){
				$('#tituloCadastro').html('Cadastrar Novo Menu')
				$('#toMenu').show()
				$('#toModulo').hide()
			}else if($('#isMenuOrModulo').val() == 'modulo'){
				$('#tituloCadastro').html('Cadastrar Novo Módulo')
				$('#toMenu').hide()
				$('#toModulo').show()
			}else{
				$('#tituloCadastro').html('Cadastrar Novo Menu')
				$('#toMenu').show()
				$('#toModulo').hide()
			}

        	$('#isMenu').on('change', function(e){
				$('#tituloCadastro').html('Cadastrar Novo Menu')
				$('#toMenu').show()
				$('#toModulo').hide()
			})
			$('#isModulo').on('change', function(e){
				$('#tituloCadastro').html('Cadastrar Novo Módulo')
				$('#toMenu').hide()
				$('#toModulo').show()
			})

			$('#btnAlterar').on('click', function(e){
				$('#btnAlterar').html("Alterando... <img src='global_assets/images/lamparinas/loader-transparente2.gif' style='width: 17px'>")
				$('#btnAlterar').attr('disabled', true)

				let msg = ''
				let idMenu = $('#idMenu').val()
				let tipo = 'ATUALIZAR'
				let inputNome = $('#inputNome').val()
				let inputNomeModulo = $('#inputNomeModulo').val()
				let url = $('#url').val()
				let icone = $('#icone').val()
				let cmbModulo = $('#cmbModulo').val()
				let menuPai = $('#cmbMenuPai').val()
				let nivel = $('#nivel').val()
				let ordem = $('#ordem').val()
				let ordemModulo = $('#ordemModulo').val()
				let subMenu = $('#subMenu').is(":checked")?1:0
				let publico = $('#publico').is(":checked")?1:0
				let privado = $('#privado').is(":checked")?1:0
				let situacao = $('#cmbSituacao').val()
				let situacaoModulo = $('#cmbSituacaoModulo').val();
				let posicao = $('#cmbPosicao').val()
				let isMenuOrModulo = $('#isMenuOrModulo').val()

				if(isMenuOrModulo=='menu'){
					switch(msg){
						case inputNome: msg = 'Informe nome do menu'; break;
						case url: msg = 'Informe a URL de destino'; break;
						case cmbModulo: msg = 'Informe o módulo que o menu irá pertencer'; break;
						case posicao: msg = 'Informe a posição do menu'; break;
						case situacao: msg = 'Informe a situação do menu'; break;
						default: msg = ''; break;
					}
				}else{
					switch(msg){
						case inputNomeModulo: msg = 'Informe nome do módulo'; break;
						case situacaoModulo: msg = 'Informe a situação do módulo'; break;
						default: msg = ''; break;
					}
				}

				if(msg){
					alerta('Informação obrigatória', msg, 'error')
					$('#btnAlterar').html("Alterar")
					$('#btnAlterar').attr('disabled', false)
					return
				}

				$.ajax({
					type: 'POST',
					url: 'menuFiltra.php',
					dataType: 'json',
					data:{
						'isMenu': isMenuOrModulo=='menu'?true:false,
						'tipo': tipo,
						'idMenu': idMenu,
						'inputNome': inputNome?inputNome:inputNomeModulo,
						'url': url,
						'icone': icone,
						'cmbModulo': cmbModulo,
						'menuPai': menuPai,
						'nivel': nivel,
						'ordem': ordem,
						'subMenu': subMenu,
						'publico': publico,
						'privado': privado,
						'posicao': posicao,
						'situacao': situacao?situacao:situacaoModulo
					},
					success: function(response) {
						alerta(response.titulo, response.menssagem, response.status);
						$('#btnAlterar').html("Alterar")
						$('#btnAlterar').attr('disabled', false)
						window.location.href = 'menuLista.php'
					},
					error: function(response) {
						alerta('Erro', 'Contate um administrador', 'error');
						$('#btnAlterar').html("Alterar")
						$('#btnAlterar').attr('disabled', false)
					}
				});
			})

			$('#btnInserir').on('click', function(e){
				$('#btnInserir').attr('onclick', '')
				$('#btnInserir').attr('disabled', true)

				let msg = ''
				let idMenu = $('#idMenu').val()
				let tipo = 'CRIAR'
				let inputNome = $('#inputNome').val()
				let inputNomeModulo = $('#inputNomeModulo').val()
				let url = $('#url').val()
				let icone = $('#icone').val()
				let cmbModulo = $('#cmbModulo').val()
				let menuPai = $('#cmbMenuPai').val()
				let nivel = $('#nivel').val()
				let ordem = $('#ordem').val()
				let ordemModulo = $('#ordemModulo').val()
				let subMenu = $('#subMenu').is(":checked")?1:0
				let publico = $('#publico').is(":checked")?1:0
				let privado = $('#privado').is(":checked")?1:0
				let situacao = $('#cmbSituacao').val()
				let situacaoModulo = $('#cmbSituacaoModulo').val();
				let posicao = $('#cmbPosicao').val()
				let isMenu = $('#isMenu').is(':checked')
				
				if(isMenu){
					switch(msg){
						case inputNome: msg = 'Informe nome do menu'; break;
						case url: msg = 'Informe a URL de destino'; break;
						case cmbModulo: msg = 'Informe o módulo que o menu irá pertencer'; break;
						case posicao: msg = 'Informe a posição do menu'; break;
						case situacao: msg = 'Informe a situação do menu'; break;
						default: msg = ''; break;
					}
				}else{
					switch(msg){
						case inputNomeModulo: msg = 'Informe nome do módulo'; break;
						case situacaoModulo: msg = 'Informe a situação do módulo'; break;
						default: msg = ''; break;
					}
				}

				if(msg){
					alerta('Informação obrigatória', msg, 'error')
					$('#btnInserir').html('Inserir')
					$('#btnInserir').attr('disabled', false)
					return
				}

				$.ajax({
					type: 'POST',
					url: 'menuFiltra.php',
					dataType: 'json',
					data:{
						'isMenu': isMenu,
						'idMenu': idMenu,
						'tipo': tipo,
						'inputNome': inputNome?inputNome:inputNomeModulo,
						'url': url,
						'icone': icone,
						'cmbModulo': cmbModulo,
						'menuPai': menuPai,
						'nivel': nivel,
						'ordem': ordem,
						'subMenu': subMenu,
						'publico': publico,
						'privado': privado,
						'posicao': posicao,
						'situacao': situacao?situacao:situacaoModulo
					},
					success: function(response) {
						alerta(response.titulo, response.menssagem, response.status);
						$('#btnInserir').html('Inserir')
						$('#btnInserir').attr('disabled', false)
						window.location.href = 'menuLista.php'
					},
					error: function(response) {
						alerta(response.titulo, response.menssagem, response.status);
						$('#btnInserir').html('Inserir')
						$('#btnInserir').attr('disabled', false)
					}
				});
			})
			
        }); // document.ready
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
				<div class="card">
					<form name="formMenu" id="formMenu" method="post" class="form-validate">
						<div class="card-header header-elements-inline">
							<h5 id="tituloCadastro" class="text-uppercase font-weight-bold">Cadastrar Novo Menu</h5>
						</div>
						
						<div class="card-body">
							<?php
								if(!isset($_POST['id'])){
									echo "<div class='col-lg-12 row mb-4'>
											<div class='col-lg-1'>
												<label for='isMenu'>Menu</label>
												<input id='isMenu' name='isMenu' type='radio' checked />
											</div>
											<div class='col-lg-1'>
												<label for='isModulo'>Modulo</label>
												<input id='isModulo' name='isMenu' type='radio' />
											</div>
										</div>";
								}
							?>
							<div id="toMenu" class="col-lg-12 row">
								<div class="col-lg-4">
									<label>Nome<span class="text-danger"> *</span></label>
								</div>
								<div class="col-lg-2">
									<label>URL<span class="text-danger"> *</span></label>
								</div>
								<div class="col-lg-2">
									<label>Ícone</label>
								</div>
								<div class="col-lg-2">
									<label>Módulo<span class="text-danger"> *</span></label>
								</div>
								<div class="col-lg-2">
									<label>Situação<span class="text-danger"> *</span></label>
								</div>

								<?php
									$sql = "SELECT SituaId,SituaNome
										FROM Situacao
										WHERE SituaChave in ('ATIVO','INATIVO')";
									$result = $conn->query($sql);
									$rowSituacoes = $result->fetchAll(PDO::FETCH_ASSOC);
									
									$sql = "SELECT ModulId,ModulNome
									FROM Modulo
									JOIN Situacao ON SituaId = ModulStatus
									WHERE SituaChave = 'ATIVO'";
									$result = $conn->query($sql);
									$rowModuloSelect = $result->fetchAll(PDO::FETCH_ASSOC);

									$sql = "SELECT MenuId, MenuNome, ModulNome
									FROM Menu
									JOIN Situacao ON SituaId = MenuStatus
									JOIN Modulo ON ModulId = MenuModulo
									WHERE SituaChave = 'ATIVO' and MenuSubMenu = 1";
									$result = $conn->query($sql);
									$rowMenuSelect = $result->fetchAll(PDO::FETCH_ASSOC);

									$optionsSituacao = "<option value=''>Selecione</option>";

									foreach($rowSituacoes as $item){
										$selected = isset($_POST['id']) && $isMenu == 'menu'?($item['SituaId'] == $rowMenu['SituaId']?'selected':''):'';
										$optionsSituacao .= "<option $selected value='$item[SituaId]'>$item[SituaNome]</option>";
									}

									$MenuId = isset($_POST['id']) && $isMenu == 'menu'?$rowMenu['MenuId']:'';
									$MenuNome = isset($_POST['id']) && $isMenu == 'menu'?$rowMenu['MenuNome']:'';
									$MenuUrl = isset($_POST['id']) && $isMenu == 'menu'?$rowMenu['MenuUrl']:'';
									$MenuIco = isset($_POST['id']) && $isMenu == 'menu'?$rowMenu['MenuIco']:'';
									$ModulNome = isset($_POST['id']) && $isMenu == 'menu'?$rowMenu['ModulNome']:'';
									$MenuModulo = isset($_POST['id']) && $isMenu == 'menu'?$rowMenu['MenuModulo']:'';
									$MenuPai = isset($_POST['id']) && $isMenu == 'menu'?$rowMenu['MenuPai']:'';
									$MenuLevel = isset($_POST['id']) && $isMenu == 'menu'?$rowMenu['MenuLevel']:1;
									$MenuOrdem = isset($_POST['id']) && $isMenu == 'menu'?$rowMenu['MenuOrdem']:1;
									$MenuSubMenu = isset($_POST['id']) && $isMenu == 'menu'?($rowMenu['MenuSubMenu']?'checked':''):'';
									$MenuSetorPublico = isset($_POST['id']) && $isMenu == 'menu'?($rowMenu['MenuSetorPublico']?'checked':''):'checked';
									$MenuSetorPrivado = isset($_POST['id']) && $isMenu == 'menu'?($rowMenu['MenuSetorPrivado']?'checked':''):'checked';
									$MenuPosicao = isset($_POST['id']) && $isMenu == 'menu'?$rowMenu['MenuPosicao']:'';
									$MenuUsuarioAtualizador = isset($_POST['id']) && $isMenu == 'menu'?$rowMenu['MenuUsuarioAtualizador']:'';
									$MenuStatus = isset($_POST['id']) && $isMenu == 'menu'?$rowMenu['MenuStatus']:1;

									$optionsModulo = "<option value=''>selecione</option>";
									$optionsMenu = "<option value=''>selecione</option>";
									foreach($rowModuloSelect as $modulo){
										$nome = $modulo['ModulNome'];
										$id = $modulo['ModulId'];
										$options = $MenuModulo?($MenuModulo == $id?"selected":""):"";

										$optionsModulo .= "<option $options value='$id'>$nome</option>";
									}

									foreach($rowMenuSelect as $menu){
										$nome = "$menu[MenuNome] ($menu[ModulNome])";
										$id = $menu['MenuId'];
										$options = $MenuPai?($MenuPai == $id?"selected":""):"";

										$optionsMenu .= "<option $options value='$id'>$nome</option>";
									}
									$optionsPosicao = "<option value=''>Selecione</option>";
									if(isset($_POST['id']) && $isMenu == 'menu'){
										$optionsPosicao .= $rowMenu['MenuPosicao'] == 'CONFIGURADOR'?"<option selected value='CONFIGURADOR'>CONFIGURADOR</option>":"<option value='CONFIGURADOR'>CONFIGURADOR</option>";
										$optionsPosicao .= $rowMenu['MenuPosicao'] == 'PRINCIPAL'?"<option selected value='PRINCIPAL'>PRINCIPAL</option>":"<option value='PRINCIPAL'>PRINCIPAL</option>";
										$optionsPosicao .= $rowMenu['MenuPosicao'] == 'APOIO'?"<option selected value='APOIO'>APOIO</option>":"<option value='APOIO'>APOIO</option>";
									} else {
										$optionsPosicao = "
										<option value=''>Selecione</option>
										<option value='CONFIGURADOR'>CONFIGURADOR</option>
										<option value='PRINCIPAL'>PRINCIPAL</option>
										<option value='APOIO'>APOIO</option>";
									}

									echo "
										<div class='col-lg-4'>
											<input type='text' id='inputNome' value='$MenuNome' name='inputNome' class='form-control' placeholder='Menu Nome' required autofocus>
										</div>
										<div class='col-lg-2'>
											<input type='text' id='url' value='$MenuUrl' name='url' class='form-control' placeholder='URL'>
										</div>	
										<div class='col-lg-2'>
											<input type='text' id='icone' value='$MenuIco' name='icone' class='form-control' placeholder='Ícone'>
										</div>	
										<div class='col-lg-2'>
											<select id='cmbModulo' name='cmbModulo' class='select-search' required>
												$optionsModulo
											</select>
										</div>
										<div class='col-lg-2'>
											<select id='cmbSituacao' name='cmbSituacao' class='form-control-select2' required>
												$optionsSituacao
											</select>
										</div>
										<div class='form-group col-lg-12 row mt-4'>
											<div class='col-lg-3'>
												<label for='inputNome'>Menu Pai</label>
											</div>
											<div class='col-lg-1'>
												<label for='inputNome'>Nível</label>
											</div>
											<div class='col-lg-1'>
												<label for='inputNome'>Ordem</label>
											</div>
											<div class='col-lg-1 text-center'>
												<label for='inputNome'>SubMenu?</label>
											</div>
											<div class='col-lg-2 text-center'>
												<label for='inputNome'>Setor Público</label>
											</div>
											<div class='col-lg-2 text-center'>
												<label for='inputNome'>Setor Privado</label>
											</div>
											<div class='col-lg-2'>
												<label for='inputNome'>Posição <span class='text-danger'> *</span></label>
											</div>

											<div class='col-lg-3'>
												<select id='cmbMenuPai' name='cmbMenuPai' class='select-search'>
													$optionsMenu
												</select>
											</div>
											<div class='col-lg-1'>
												<input type='number' id='nivel' value='$MenuLevel' name='nivel' value='1' class='form-control'>
											</div>
											<div class='col-lg-1'>
												<input type='number' id='ordem' value='$MenuOrdem' name='ordem' value='1' class='form-control'>
											</div>
											<div class='col-lg-1'>
												<input type='checkbox' id='subMenu' $MenuSubMenu name='subMenu' class='form-control'>
											</div>
											<div class='col-lg-2'>
												<input type='checkbox' id='publico' $MenuSetorPublico name='publico' class='form-control'>
											</div>
											<div class='col-lg-2'>
												<input type='checkbox' id='privado' $MenuSetorPrivado name='privado' class='form-control'>
											</div>
											<div class='col-lg-2'>
												<select id='cmbPosicao' name='cmbPosicao' class='form-control-select2' required>
													$optionsPosicao
												</select>
											</div>
										</div>";
								?>
							</div>
							<div id="toModulo" class="col-lg-12 row">
								<div class="col-lg-6">
									<label>Nome<span class="text-danger"> *</span></label>
								</div>
								<div class="col-lg-2">
									<label>Ordem<span class="text-danger"> *</span></label>
								</div>
								<div class="col-lg-4">
									<label>Situação</label>
								</div>
								<?php
									$sql = "SELECT SituaId,SituaNome
										FROM Situacao
										WHERE SituaChave in ('ATIVO','INATIVO')";
									$result = $conn->query($sql);
									$rowSituacoes = $result->fetchAll(PDO::FETCH_ASSOC);

									$optionsSituacao = "<option value=''>Selecione</option>";

									foreach($rowSituacoes as $item){
										$selected = isset($_POST['id']) && $isMenu == 'modulo'?($item['SituaId'] == $rowMenu['SituaId']?'selected':''):'';
										$optionsSituacao .= "<option $selected value='$item[SituaId]'>$item[SituaNome]</option>";
									}

									$MenuId = isset($_POST['id']) && $isMenu == 'modulo'?$rowMenu['ModulId']:$MenuId;
									$MenuNome = isset($_POST['id']) && $isMenu == 'modulo'?$rowMenu['ModulNome']:$MenuNome;
									$MenuOrdem = isset($_POST['id']) && $isMenu == 'modulo'?$rowMenu['ModulOrdem']:$MenuOrdem ;

									echo "<div class='col-lg-6'>
											<input type='text' id='inputNomeModulo' value='$MenuNome' name='inputNomeModulo' class='form-control' placeholder='Módulo Nome' required autofocus>
										</div>
										<div class='col-lg-2'>
											<input type='number' id='ordemModulo' value='$MenuOrdem' name='ordemModulo' value='1' class='form-control'>
										</div>
										<div class='col-lg-4'>
											<select id='cmbSituacaoModulo' name='cmbSituacaoModulo' class='form-control-select2' required>
												$optionsSituacao
											</select>
										</div>";
								?>
							</div>

							<?php
								if(isset($_POST['id'])){
									echo "<input type='hidden' id='idMenu' name='idMenu' value='$MenuId'>";
									echo "<input type='hidden' id='isMenuOrModulo' name='isMenuOrModulo' value='$isMenu'>";
								}
							?>
						</div>
					</form>
					<div class="row ml-3" style="margin-top: 40px;">
						<div class="col-lg-12">								
							<div class="form-group">
								<?php
									echo isset($_POST['id'])?'<button id="btnAlterar" class="btn btn-lg btn-principal" type="submit">Alterar</button>':'<button id="btnInserir" class="btn btn-lg btn-principal" type="submit">Inserir</button>';
								?>	
								<a href="menuLista.php" class="btn btn-basic" role="button">Cancelar</a>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php include_once("footer.php"); ?>
		</div>
	</div>
</body>
</html>
