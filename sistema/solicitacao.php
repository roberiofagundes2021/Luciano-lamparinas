<?php

include_once("sessao.php");

$_SESSION['PaginaAtual'] = 'Solicitação';

include('global_assets/php/conexao.php');

$sql = "SELECT SolicId, SolicNumero, SolicData, SolicObservacao, SolicSetor, SolicSolicitante, SolicSituacao, 
		SolicMotivo, UsuarNome, SetorNome, SituaChave, SituaNome, SituaCor, BandeMotivo
		FROM Solicitacao
		JOIN Usuario on UsuarId = SolicSolicitante
		JOIN Setor on SetorId = SolicSetor
		JOIN Situacao on SituaId = SolicSituacao
		LEFT JOIN Bandeja on BandeTabelaId = SolicId and BandeTabela = 'Solicitacao' and BandeUnidade = " . $_SESSION['UnidadeId'] . "
	    WHERE SolicUnidade = " . $_SESSION['UnidadeId'] . " and UsuarId = ".$_SESSION['UsuarId']."
		ORDER BY SolicData, SolicId DESC";
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
	<title>Lamparinas | Solicitação</title>

	<?php include_once("head.php"); ?>

	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/tables/datatables/datatables.min.js"></script>
	<script src="global_assets/js/plugins/tables/datatables/extensions/responsive.min.js"></script>

	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>

	<script src="global_assets/js/demo_pages/datatables_responsive.js"></script>
	<script src="global_assets/js/demo_pages/datatables_sorting.js"></script>

	<!-- Modal -->
	<script src="global_assets/js/plugins/notifications/bootbox.min.js"></script>

	<!-- Plugin para corrigir a ordenação por data. Caso a URL dê problema algum dia, salvei esses 2 arquivos na pasta global_assets/js/lamparinas -->
	<script type="text/javascript" language="javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.8.4/moment.min.js"></script>
	<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/plug-ins/1.10.10/sorting/datetime-moment.js"></script>	

	<script type="text/javascript">
		
		$(document).ready(function() {

			// Modal
			function modal() {
				$('.btn-modal').each((i, elem) => {
					$(elem).on('click', function() {
						$('#page-modal').addClass('custon-modal-show')
						$('body').css('overflow', 'hidden')

						$('#modal-close').on('click', function() {
							$('#page-modal').removeClass('custon-modal-show')
							$('body').css('overflow', 'scroll')
						})
					})
				})
			}
			modal()

            // Esta função invoca a função modal para que suas rotinas sejam carregadas quando a tabela entra no modo collapsed em telas menores
			function a() {
				setInterval(() => {
					if ($('#tblSolicitacao').hasClass('collapsed')) {
						modal()
						produtosMostrar()
						return
					}
				}, 100)
			}
			a()

			function produtosMostrar() {
				$('.btn-modal').each((i, elem) => {
					$(elem).on('click', () => {
						const id = $(elem).attr('id')
						const url = 'solicitacaoProdutos.php'
						$.post(
							url, {
								solicitacaoId: id
							},
							function(data) {
								$('.custon-modal-lista').html(data)
							}
						)
					})
				})
			}
			produtosMostrar()

			$.fn.dataTable.moment('DD/MM/YYYY'); //Para corrigir a ordenação por data

			/* Início: Tabela Personalizada */
			$('#tblSolicitacao').DataTable({
				"order": [[0, "desc"]],
				autoWidth: false,
				responsive: true,
				columnDefs: [{
						orderable: true, //Data
						width: "10%",
						targets: [0]
					},
					{
						orderable: true, //Numero
						width: "10%",
						targets: [1]
					},
					{
						orderable: true, //Setor
						width: "30%",
						targets: [2]
					},
					{
						orderable: true, //Solicitante
						width: "30%",
						targets: [3]
					},
					{
						orderable: true, //Situação
						width: "10%",
						targets: [4]
					},
					{
						orderable: false, //Ações
						width: "10%",
						targets: [5]
					}
				],
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
		function atualizaSolicitacao(SolicId, Tipo, Motivo, Numero) {

			document.getElementById('inputSolicitacaoId').value = SolicId;

			if (Tipo == 'motivo'){
	            bootbox.alert({
                    title: '<strong>Motivo da Não Liberação</strong>',
                    message: Motivo
                });
                return false;
			} else if (Tipo == 'motivoCancelamento'){
	            bootbox.alert({
                    title: '<strong>Motivo do Cancelamento da Solicitação</strong>',
                    message: Motivo
                });
                return false;
			} else if (Tipo == 'imprimir') {
				document.formSolicitacao.action = "solicitacaoImprime.php";
				document.formSolicitacao.setAttribute("target", "_blank");
			} else if (Tipo == 'cancelar') {

				bootbox.prompt({
					title: 'Informe o motivo do cancelamento da solicitação',
					inputType: 'textarea',
					buttons: {
						confirm: {
							label: 'Enviar',
							className: 'btn-principal'
						},
						cancel: {
							label: 'Cancelar',
							className: 'btn-link'
						}
					},
					callback: function(result) {

						if (result === null) {
							bootbox.alert({
								title: 'Cancelamento da solicitação abortado',
								message: 'A solicitação <b>' + Numero + '</b> não foi cancelada!'
							});
						} else {

							document.getElementById('inputMotivo').value = result;
							document.formSolicitacao.action = "solicitacaoCancela.php";
							document.formSolicitacao.setAttribute("target", "_self");
							document.formSolicitacao.submit();
						}
					}
				});
				return false;
			}

			document.formSolicitacao.submit();
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
								<h5 class="card-title">Relação de Solicitações de Material</h5>
							</div>

							<div class="card-body">
								<div class="row">
									<div class="col-lg-9">
										A relação abaixo faz referência às solicitações da unidade <b><?php echo $_SESSION['UnidadeNome']; ?></b>
									</div>
									<div class="col-lg-3">	
										<div class="text-right"><a href="solicitacaoNovo.php" class="btn btn-principal" role="button">Nova Solicitação</a></div>
									</div>
								</div>
							</div>

							<table class="table" id="tblSolicitacao">
								<thead>
									<tr class="bg-slate">
										<th>Data</th>
										<th>Número</th>
										<th>Setor</th>
										<th>Solicitante</th>
										<th>Situação</th>
										<th class="text-center">Ações</th>
									</tr>
								</thead>
								<tbody>
									<?php
									foreach ($row as $item) {

										$situacao = $item['SituaNome'];
										$situacaoClasse = 'badge badge-flat border-'.$item['SituaCor'].' text-'.$item['SituaCor'];

										print('
										<tr>
											<td>' . mostraData($item['SolicData']) . '</td>
											<td>' . $item['SolicNumero'] . '</td>
											<td>' . $item['SetorNome'] . '</td>
											<td>' . nomeSobrenome($item['UsuarNome'], 2) . '</td>
											');

										print('<td><div"><span class="' . $situacaoClasse . '">' . $situacao . '</span></div></td>');

										print('<td class="text-center">
													<div class="list-icons">
														<div class="list-icons list-icons-extended">
															<div class="dropdown">													
																<a href="#" class="list-icons-item" data-toggle="dropdown">
																	<i class="icon-menu9"></i>
																</a>

																<div class="dropdown-menu dropdown-menu-right">
																	<a id="' . $item['SolicId'] . '" class="btn-modal dropdown-item"><i class="icon-stackoverflow" title="Listar Itens"></i> Listar Itens</a>
																	<div class="dropdown-divider"></div>
																	' );

																	if ($item['SituaChave'] == 'AGUARDANDOLIBERACAO') {
                                    									print('<a href="#" onclick="atualizaSolicitacao('.$item['SolicId'].', \'cancelar\', \'\', \''.$item['SolicNumero'].'\')" class="dropdown-item" title="Cancelar Solicitação"><i class="icon-cancel-circle2"></i> Cancelar</a>');
																	}

																	if($item['SituaChave'] != 'CANCELADO'){
																		print('<a href="#" onclick="atualizaSolicitacao('.$item['SolicId'].', \'imprimir\', \'\', \''.$item['SolicNumero'].'\')" class="dropdown-item" title="Imprimir Solicitação"><i class="icon-printer2"></i> Imprimir</a>');
																	}	   

																	if (isset($item['BandeMotivo'])){
																		print('<a href="#" onclick="atualizaSolicitacao('.$item['SolicId'].', \'motivo\', \''.$item['BandeMotivo'].'\', \''.$item['SolicNumero'].'\')" class="dropdown-item" title="Motivo da Não liberação"><i class="icon-question4"></i> Motivo da Não Liberação</a>');
																	}

																	if (isset($item['SolicMotivo'])){
																		print('<a href="#" onclick="atualizaSolicitacao('.$item['SolicId'].', \'motivoCancelamento\', \''.$item['SolicMotivo'].'\', \''.$item['SolicNumero'].'\')" class="dropdown-item" title="Motivo da Não liberação"><i class="icon-question4"></i> Motivo Cancelamento</a>');
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

				<form name="formSolicitacao" method="post">
					<input type="hidden" id="inputSolicitacaoId" name="inputSolicitacaoId">
					<input type="hidden" id="inputSolicitacaoTipo" name="inputSolicitacaoTipo">
					<input type="hidden" id="inputMotivo" name="inputMotivo">
				</form>

			</div>
			<!-- /content area -->

			<?php include_once("footer.php"); ?>

		</div>
		<!-- /main content -->

	</div>
	<!-- /page content -->

	<!-- /modal -->
	<div id="page-modal" class="custon-modal">
		<div class="custon-modal-container">
			<div class="card custon-modal-content">
				<div class="custon-modal-title">
					<i class="fab-icon-open icon-cart p-3"></i>
					<p class="h3">Itens Selecionados</p>
					<i id="modal-close" class="fab-icon-open icon-cross2 p-3" style="cursor: pointer"></i>
				</div>
				<div class="custon-modal-lista d-flex flex-column">

				</div>
				<div class="card-footer mt-2 d-flex flex-column">
	
				</div>
			</div>
		</div>
	</div>

	<?php include_once("alerta.php"); ?>

</body>

</html>