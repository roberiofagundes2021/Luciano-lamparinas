		<script language ="javascript">
		
			function mudarEmpresa(URL) {
				
				var empresa = $('#cmbEmpresa').val()
				empresa = empresa.split("#");
				
				var id = empresa[0];
				var nome = empresa[1];

				$('#inputEmpresaId').val(id)
				$('#inputEmpresaNome').val(nome)

				$('#formNewEmpresa').attr('action', URL)
				$('#formNewEmpresa').submit()
				
				// $.ajax({
				// 	type: "POST",
				// 	url: "menuLeftSecundarioAjax.php",
				// 	data: ('id='+id+'&nome='+nome),
				// 	success: function(resposta){
				
				// 		if(resposta){
				// 			location.reload();
				// 			return false;
				// 		}
				// 	}
				// })				
				
			}
							
		</script>
		
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
								<select id="cmbEmpresa" name="cmbEmpresa" class="form-control form-control-select2" onChange="mudarEmpresa('localEstoque.php'); ">
									<?php 
										$sql = "SELECT EmpreId, EmpreNomeFantasia
												FROM Empresa
												ORDER BY EmpreNomeFantasia ASC";
										$result = $conn->query($sql);
										$rowEmpresa = $result->fetchAll(PDO::FETCH_ASSOC);										
										
										foreach ($rowEmpresa as $item){
											$seleciona = $item['EmpreId'] == $_SESSION['EmpresaId'] ? "selected" : "";
											print('<option value="'.$item['EmpreId'].'#'.$item['EmpreNomeFantasia'].'" '. $seleciona .'>'.$item['EmpreNomeFantasia'].'</option>');
										}
									
									?>
								</select>
							</div>
						</form>
					</div>

					<form id="formNewEmpresa" name="formEmpresa" method="post">
						<input type="hidden" id="inputEmpresaId" name="inputEmpresaId" >
						<input type="hidden" id="inputEmpresaNome" name="inputEmpresaNome" >
					</form>
				</div>
				<!-- /sidebar Empresa -->

				<!-- Sub navigation -->
				<div class="card mb-2">

					<div class="card-body p-0">
						<ul class="nav nav-sidebar" data-nav-type="accordion">
							<li class="nav-item-header">GERENCIAR EMPRESA</li>
							<li class="nav-item">
								<a href="#" onclick="mudarEmpresa('licenca.php')" class="nav-link"><i class="icon-certificate"></i> Licença</a>
							</li>
							<li class="nav-item">
								<a href="#" onclick="mudarEmpresa('unidade.php')" class="nav-link"><i class="icon-home7"></i> Unidade</a>
							</li>
							<li class="nav-item">
								<a href="#" onclick="mudarEmpresa('setor.php')" class="nav-link"><i class="icon-cabinet"></i> Setor</a>
							</li>
							<li class="nav-item">
								<a href="#" onclick="mudarEmpresa('localEstoque.php')" class="nav-link"><i class="icon-box"></i> Local de Estoque</a>
							</li>
							<li class="nav-item">
								<a href="#" onclick="mudarEmpresa('usuario.php')" class="nav-link">
								
									<?php 
										$sql = "SELECT EXUXPUsuario
												FROM EmpresaXUsuarioXPerfil
												JOIN Situacao on SituaId =  EXUXPStatus
												WHERE EXUXPEmpresa = ".$_SESSION['EmpresaId']."
												";
										$result = $conn->query($sql);
										$rowUsuario = $result->fetchAll(PDO::FETCH_ASSOC);
										$countUsuario = count($rowUsuario);
										//var_dump($countUsuario);die;
										
										$sql = "SELECT isnull(LicenLimiteUsuarios,0) as Limite
												FROM Licenca
												JOIN Situacao on SituaId = LicenStatus
												WHERE LicenEmpresa = ".$_SESSION['EmpresaId']." and SituaChave = 'ATIVO'
												 Order By LicenDtInicio DESC
												";
										$result = $conn->query($sql);
										$rowLimite = $result->fetch(PDO::FETCH_ASSOC);

									?>								
								
									<i class="icon-user-plus"></i>Usuários
									<span class="badge bg-primary badge-pill ml-auto">
									<?php 
										echo $countUsuario;
										
										if (isset($rowLimite['Limite'])){
											echo "/".$rowLimite['Limite'];
										}
									?>
									</span>
								</a>
							</li>						
							<!--<li class="nav-item">
								<a href="menu.php" class="nav-link">
									<i class="icon-menu2"></i>
									Menu									
								</a>
							</li>-->
							<!--<li class="nav-item">
							
								<?php
								
									$situacao = $item['EmpreStatus'] ? 'Ativo' : 'Inativo';
									$situacaoClasse = $item['EmpreStatus'] ? 'badge-success' : 'badge-secondary';
									
									if ($_SESSION['EmpreId'] != $item['EmpreId']) {
										print('Ativo/Inativo: <a href="#" onclick="atualizaEmpresa('.$item['EmpreId'].', \''.$item['EmpreNomeFantasia'].'\','.$item['EmpreStatus'].', \'mudaStatus\');"><span class="badge '.$situacaoClasse.'">'.$situacao.'</span></a>');
									} else {
										print('Ativo/Inativo: <a href="#" data-popup="tooltip" data-trigger="focus" title="Essa empresa está sendo usada por você no momento"><span class="badge '.$situacaoClasse.'">'.$situacao.'</span></a>');
									}
								?>
							</li>-->
							<li class="nav-item">
								<a href="parametro.php" class="nav-link"><i class="icon-equalizer"></i> Parâmetro</a>
							</li>
							<li class="nav-item-divider"></li>
							<li class="nav-item">
								<a href="empresa.php" class="nav-link"><i class="icon-office"></i> Listar Empresas</a>
							</li>	
						</ul>
					</div>
				</div>
				<!-- /sub navigation -->

			</div>
			<!-- /sidebar content -->

		</div>
		<!-- /secondary sidebar -->
