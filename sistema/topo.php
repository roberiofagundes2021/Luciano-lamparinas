<script>
	// setTimeout(() => {
	// 	let url = 'usuariosOnlineAtualiza.php'
	// 	let data = {
	// 		timesTampUsuarioOnline: new Date().toLocaleTimeString(),
	// 		hora:  new Date().getHours(),
	// 		minuto:  new Date().getMinutes(),
	// 		segundos: new Date().getSeconds()
	// 	}
	// 	$.post({
	// 		url,
	// 		data,
	// 		success: (data) => {
	// 			$('#usuariosOnline').html(data)
	// 		}
	// 	})
	// }, 1000)
	// setInterval(() => {
	// 	let url = 'usuariosOnlineAtualiza.php'
	// 	let data = {
	// 		timesTampUsuarioOnline: new Date().toLocaleTimeString(),
	// 		hora:  new Date().getHours(),
	// 		minuto:  new Date().getMinutes(),
	// 		segundos: new Date().getSeconds()
	// 	}
	// 	$.post({
	// 		url,
	// 		data,
	// 		success: (data) => {
	// 			console.log(data)
	// 			$('#usuariosOnline').html(data)				
	// 		}
	// 	})
	// }, 2000)
</script>

<!-- Main navbar -->
<div class="navbar navbar-expand-md navbar-dark bg-black fixed-top">
	<div class="navbar-brand">
		<span style="color:#fff; font-size: 13px;"><?php echo $_SESSION['EmpreNomeFantasia'] ?> | </span> 
		<span style="color:#fff; font-size: 12px;">Unidade: <?php echo $_SESSION['UnidadeNome'] ?> <span>
		<!--<a href="index.html" class="d-inline-block">
				<img src="global_assets/images/logo_light.png" alt="">
			</a>-->
	</div>
	<div class="navbar-nav" style="display:none;">
		<ul class="navbar-nav">
			<li class="nav-item dropdown">
				<a href="#" class="navbar-nav-link dropdown-toggle caret-0" data-toggle="dropdown">
					<i class="icon-circle-down2"></i>
					<span class="d-md-none ml-2">Módulos</span>
					<!--<span class="badge badge-pill badge-mark bg-orange-400 border-orange-400 ml-auto ml-md-0"></span>-->
				</a>

				<div class="dropdown-menu dropdown-menu-content wmin-md-250">
					<div class="dropdown-content-body dropdown-scrollable">
						<ul class="media-list">
							<li class="media">
								<span style="margin-top: 3px; font-size: 15px;">Painel Principal</span>
							</li>
							<li class="media">
								<span style="margin-top: 3px; font-size: 15px;">Controle de Estoque</span>
							</li>
							<li class="media">
								<span style="margin-top: 3px; font-size: 15px;">Financeiro</span>
							</li>
							<li class="media">
								<span style="margin-top: 3px; font-size: 15px;">RH</span>
							</li>
						</ul>
					</div>
				</div>
			</li>
		</ul>
	</div>

	<div class="d-md-none">
		<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar-mobile">
			<i class="icon-tree5"></i>
		</button>
		<button class="navbar-toggler sidebar-mobile-main-toggle" type="button">
			<i class="icon-paragraph-justify3"></i>
		</button>
	</div>

	<div class="collapse navbar-collapse" id="navbar-mobile">
		<ul class="navbar-nav">
			<li class="nav-item">
				<a href="#" class="navbar-nav-link sidebar-control sidebar-main-toggle d-none d-md-block">
					<i class="icon-drag-left"></i>
				</a>
			</li>
		</ul>

		<span class="navbar-text ml-md-3">
			<span class="badge badge-mark border-orange-300 mr-2"></span>
			<?php echo saudacoes() . ", " . nomeSobrenome($_SESSION['UsuarNome'], 1) . "!"; ?>
		</span>


		<ul class="navbar-nav ml-md-auto">
			<?php
				$visibilidade = 'display:none;';
				if($_SESSION['PaginaAtual'] == 'Relação de Contas à Pagar' || $_SESSION['PaginaAtual'] == 'Novo Lançamento - Contas a Pagar' ||
				$_SESSION['PaginaAtual'] == 'Relação de Contas à Receber' || $_SESSION['PaginaAtual'] == 'Novo Lançamento - Contas a Receber' ||
				$_SESSION['PaginaAtual'] == 'Relação de Movimentações Financeiras' || $_SESSION['PaginaAtual'] == 'Financeiro / Movimentação do Financeiro / Novo Lançamento' ||
				$_SESSION['PaginaAtual'] == 'Movimentação do Caixa' || $_SESSION['PaginaAtual'] == 'Caixa - Recebimento Detalhamento') {
					
					$visibilidade = 'display:block;'; 
				
				}
			?>

			<li style="<?php echo $visibilidade; ?>">
				<a href="#" class="navbar-nav-link sidebar-control sidebar-right-toggle d-none d-md-block">
					<i class="icon-stats-growth"></i>
				</a>
			</li>

			<li class="nav-item dropdown" style="display:none;">
				<a href="#" class="navbar-nav-link dropdown-toggle caret-0" data-toggle="dropdown">
					<i class="icon-people"></i>
					<span class="d-md-none ml-2">Usuários</span>
				</a>

				<div class="dropdown-menu dropdown-menu-right dropdown-content wmin-md-300">
					<div class="dropdown-content-header pb-2" style="background-color: #F8F8F8">
						<span class="font-weight-semibold" style="font-size: 1rem;">Usuários online</span>

						<!-- <a href="#" class="text-default"><i class="icon-search4 font-size-base"></i></a> -->
					</div>
					<div class="dropdown-divider m-0"></div>
					<div class="dropdown-content-body dropdown-scrollable">
						<ul id="usuariosOnline" class="media-list">

						</ul>
					</div>

					<!-- <div class="dropdown-content-footer bg-light">
						<a href="#" class="text-grey mr-auto">Todos usuários</a>
						<a href="#" class="text-grey"><i class="icon-gear"></i></a>
					</div>
				</div> -->
			</li>

			<li class="nav-item dropdown" style="padding-right:10px;">
				<a href="#" class="navbar-nav-link dropdown-toggle caret-0" data-toggle="dropdown" aria-expanded="true">
					<i class="icon-bell2"></i>
					<span class="d-md-none ml-2">Alertas</span>
					<span class="badge badge-pill bg-warning-400 ml-auto ml-md-0">2</span>
				</a>

				<div class="dropdown-menu dropdown-menu-right dropdown-content wmin-md-350">
					<div class="dropdown-content-header">
						<span class="font-size-sm line-height-sm text-uppercase font-weight-semibold">Últimos alertas</span>
						<a href="#" class="text-default"><i class="icon-search4 font-size-base"></i></a>
					</div>

					<div class="dropdown-content-body dropdown-scrollable">
						<ul class="media-list">
							<li class="media">
								<div class="mr-3">
									<a href="#" class="btn bg-success-400 rounded-round btn-icon"><i class="icon-mention"></i></a>
								</div>

								<div class="media-body">
									<a href="#">Taylor Swift</a> mentioned you in a post "Angular JS. Tips and tricks"
									<div class="font-size-sm text-muted mt-1">4 minutes ago</div>
								</div>
							</li>

							<li class="media">
								<div class="mr-3">
									<a href="#" class="btn bg-pink-400 rounded-round btn-icon"><i class="icon-paperplane"></i></a>
								</div>

								<div class="media-body">
									Special offers have been sent to subscribed users by <a href="#">Donna Gordon</a>
									<div class="font-size-sm text-muted mt-1">36 minutes ago</div>
								</div>
							</li>

							<li class="media">
								<div class="mr-3">
									<a href="#" class="btn bg-blue rounded-round btn-icon"><i class="icon-plus3"></i></a>
								</div>

								<div class="media-body">
									<a href="#">Chris Arney</a> created a new <span class="font-weight-semibold">Design</span> branch in <span class="font-weight-semibold">Limitless</span> repository
									<div class="font-size-sm text-muted mt-1">2 hours ago</div>
								</div>
							</li>

							<li class="media">
								<div class="mr-3">
									<a href="#" class="btn bg-purple-300 rounded-round btn-icon"><i class="icon-truck"></i></a>
								</div>

								<div class="media-body">
									Shipping cost to the Netherlands has been reduced, database updated
									<div class="font-size-sm text-muted mt-1">Feb 8, 11:30</div>
								</div>
							</li>

							<li class="media">
								<div class="mr-3">
									<a href="#" class="btn bg-warning-400 rounded-round btn-icon"><i class="icon-comment"></i></a>
								</div>

								<div class="media-body">
									New review received on <a href="#">Server side integration</a> services
									<div class="font-size-sm text-muted mt-1">Feb 2, 10:20</div>
								</div>
							</li>

							<li class="media">
								<div class="mr-3">
									<a href="#" class="btn bg-teal-400 rounded-round btn-icon"><i class="icon-spinner11"></i></a>
								</div>

								<div class="media-body">
									<strong>January, 2018</strong> - 1320 new users, 3284 orders, $49,390 revenue
									<div class="font-size-sm text-muted mt-1">Feb 1, 05:46</div>
								</div>
							</li>
						</ul>
					</div>

					<div class="dropdown-content-footer bg-light">
						<a href="#" class="font-size-sm line-height-sm text-uppercase font-weight-semibold text-grey mr-auto">Todos alertas</a>
						<div>
							<a href="#" class="text-grey" data-popup="tooltip" title="Clear list"><i class="icon-checkmark3"></i></a>
							<a href="#" class="text-grey ml-2" data-popup="tooltip" title="Configurador"><i class="icon-gear"></i></a>
						</div>
					</div>
				</div>
			</li>

			<li class="nav-item" style="border-left: 1px solid #666;">
				<a href="sair.php" class="navbar-nav-link">
					<i class="icon-switch2"></i>
					<span class="ml-1">Sair</span>
				</a>
			</li>
		</ul>
	</div>
</div>
<!-- /main navbar -->