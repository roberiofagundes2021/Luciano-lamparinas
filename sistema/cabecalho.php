			<?php

				//Recupera todos os menus do sistema caso esteja usando permissao personalizada
				$userId = $_SESSION['UsuarId'];
				$unidade = $_SESSION['UnidadeId'];

				$sqlUser = "SELECT UsXUnPermissaoPerfil as UsuarPermissaoPerfil
				FROM Usuario
				JOIN EmpresaXUsuarioXPerfil ON EXUXPUsuario = UsuarId
				JOIN UsuarioXUnidade ON UsXUnEmpresaUsuarioPerfil = EXUXPId
				Where UsuarId = '$userId' and UsXUnUnidade = $unidade";
			
				$resultUserId = $conn->query($sqlUser);
				$usuaXPerm = $resultUserId->fetch(PDO::FETCH_ASSOC);

				$sqlEmpresa = "SELECT EmpreId, EmpreNomeFantasia
				FROM Empresa
				WHERE EmpreId = ".$_SESSION['EmpreId'];
			
				$resultEmpresa = $conn->query($sqlEmpresa);
				$empresa = $resultEmpresa->fetch(PDO::FETCH_ASSOC);

				// Verifica se o usuário esta utilizando permissao personalizada ou do perfil
				if($usuaXPerm['UsuarPermissaoPerfil'] == 0){
					$SuperAdmin = $_SESSION['PerfiChave'] != "SUPER" ? ' and UsXPeSuperAdmin = 0' : '';
					$sqlConfig = "SELECT MenuId, MenuNome, MenuUrl, MenuIco, MenuSubMenu, MenuModulo, MenuSetorPublico, MenuPosicao,
							MenuPai, MenuLevel, MenuOrdem, MenuStatus, SituaChave, MenuSetorPrivado,
							UsXPeVisualizar, UsXPeAtualizar, UsXPeExcluir, UsXPeUnidade, UsXPeSuperAdmin as SuperAdmin
							FROM Menu
							JOIN Situacao on MenuStatus = SituaId
							JOIN UsuarioXPermissao on UsXPeUsuario = '$userId' and UsXPeUnidade = '$unidade' and UsXPeMenu = MenuId
							WHERE UPPER(MenuPosicao) = 'CONFIGURADOR' ".$SuperAdmin;
				} else {
					$SuperAdmin = $_SESSION['PerfiChave'] != "SUPER" ? ' and PrXPeSuperAdmin = 0' : '';
					$sqlConfig = "SELECT MenuId, MenuNome, MenuUrl, MenuIco, MenuSubMenu, MenuModulo, MenuSetorPublico, MenuPosicao,
						MenuPai, MenuLevel, MenuOrdem, MenuStatus, SituaChave, PrXPeId, PrXPePerfil, MenuSetorPrivado
						PrXPeMenu, PrXPeVisualizar, PrXPeAtualizar,  PrXPeExcluir, PrXPeUnidade, PrXPeSuperAdmin as SuperAdmin
						FROM Menu
						JOIN Situacao on MenuStatus = SituaId
						JOIN PerfilXPermissao on MenuId = PrXPeMenu and PrXPePerfil = '$perfilId' and PrXPeUnidade  = '$unidade'
						WHERE UPPER(MenuPosicao) = 'CONFIGURADOR' ".$SuperAdmin;
				}
				$sqlConfig .= ' order by MenuOrdem asc';
				$resultConfig = $conn->query($sqlConfig);
				$config = $resultConfig->fetchAll(PDO::FETCH_ASSOC);

			?>

			<!-- Page header -->
			<script>
				// function goPage(content){
				// 	var conteudo = content.split('#')
				// 	document.getElementById('formActionConfig').action = conteudo[0]
				// 	document.getElementById('inputEmpresaIdConfig').value = conteudo[1]
				// 	document.getElementById('inputEmpresaNomeConfig').value = conteudo[2]

				// 	document.getElementById('formActionConfig').submit()

				// }
			</script>
			<div class="page-header page-header-light">
				<div class="page-header-content header-elements-md-inline" style="display:none;">
					<div class="page-title d-flex">
						<h4><i class="icon-new-tab2 mr-2"></i> <span class="font-weight-semibold"><?php echo $_SESSION['PaginaAtual']; ?></h4>
						<a href="faq.php" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
					</div>

					<div class="header-elements d-none">
						<div class="d-flex justify-content-center">
							<a href="#" class="btn btn-link btn-float font-size-sm font-weight-semibold text-default">
								<i class="icon-bars-alt text-pink-300"></i>
								<span>Statistics</span>
							</a>
							<a href="#" class="btn btn-link btn-float font-size-sm font-weight-semibold text-default">
								<i class="icon-calculator text-pink-300"></i>
								<span>Invoices</span>
							</a>
							<a href="#" class="btn btn-link btn-float font-size-sm font-weight-semibold text-default">
								<i class="icon-calendar5 text-pink-300"></i>
								<span>Schedule</span>
							</a>
						</div>
					</div>
				</div>

				<div class="breadcrumb-line breadcrumb-line-light header-elements-md-inline" style="margin-top:4px;">
					<div class="d-flex">
						<div class="breadcrumb">
							<a href="index.php" class="breadcrumb-item"><i class="icon-home2 mr-2"></i> Home</a>
							<!--<a href="general_faq.html" class="breadcrumb-item">General pages</a>-->
							<span class="breadcrumb-item active"><?php echo $_SESSION['PaginaAtual']; ?></span>
						</div>

						<a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
					</div>

					<div class="header-elements d-none">
						<div class="breadcrumb justify-content-center">
							<a href="faq.php" class="breadcrumb-elements-item">
								<i class="icon-comment-discussion mr-2"></i>
								Suporte
							</a>

							<?php
							if ($_SESSION['PerfiChave'] == "SUPER" or $_SESSION['PerfiChave'] == "ADMINISTRADOR") {
								print('
										<div class="breadcrumb-elements-item dropdown p-0">
											<a href="#" class="breadcrumb-elements-item dropdown-toggle" data-toggle="dropdown">
												<i class="icon-gear mr-2"></i>
												Configurador
											</a>
										<div class="dropdown-menu dropdown-menu-right">');
								foreach($config as $conf){
									echo('<a href="'.$conf['MenuUrl'].'" class="dropdown-item"><i class="'.$conf['MenuIco'].'"></i>'.$conf['MenuNome'].'</a>');
								}
								print('</div>
								</div>');
							}
							?>
						</div>
					</div>
				</div>
				<!-- <form id="formActionConfig" name="formActionConfig" method="POST">
					<input type="hidden" id="inputEmpresaIdConfig" name="inputEmpresaId" >
					<input type="hidden" id="inputEmpresaNomeConfig" name="inputEmpresaNome" >
				</form> -->
			</div>
			<!-- /page header -->