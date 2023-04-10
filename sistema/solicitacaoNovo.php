<?php

include_once("sessao.php");

$_SESSION['PaginaAtual'] = 'Nova Solicitação';

include('global_assets/php/conexao.php');

// verifica se existe um valor minimo e maximo para paginação de itens
$sql = "WITH itens as (SELECT ProduId, ProduCodigo, ProduDetalhamento, ProduNome, ProduFoto, CategNome, 
		dbo.fnSaldoEstoque(" . $_SESSION['UnidadeId'] . ", ProduId, 'P', NULL) as Estoque, ROW_NUMBER() OVER(ORDER BY ProduNome) as rownum
		FROM Produto
		JOIN Categoria on CategId = ProduCategoria
		JOIN Situacao on SituaId = ProduStatus
		WHERE dbo.fnSaldoEstoque(" . $_SESSION['UnidadeId'] . ", ProduId, 'P', NULL) > 0 and ProduEmpresa = " . $_SESSION['EmpreId'] . " and SituaChave = 'ATIVO')
		SELECT ProduId, ProduCodigo, ProduDetalhamento, ProduNome, ProduFoto, CategNome, Estoque, rownum
		FROM itens WHERE rownum >= 0 and rownum <= 20 ORDER BY ProduNome ASC";
$result = $conn->query($sql);
$row = $result->fetchAll(PDO::FETCH_ASSOC);

//$count = count($row);

// pega a quantidade total de itens que vem do banco para fazer a paginação

$sqlCount = "SELECT COUNT(ProduId) as quantidade
		FROM Produto
		JOIN Categoria on CategId = ProduCategoria
		JOIN Situacao on SituaId = ProduStatus
	    WHERE dbo.fnSaldoEstoque(" . $_SESSION['UnidadeId'] . ", ProduId, 'P', NULL) > 0 and ProduEmpresa = " . $_SESSION['EmpreId'] . " and SituaChave = 'ATIVO'";
$resultCount = $conn->query($sqlCount);
$count = $resultCount->fetch(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head id="hea">
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Nova Solicitação</title>

	<?php include_once("head.php"); ?>
	
	<style>
		#Imagens {
		  height: 250px;

		  /* habilita o flex nos filhos diretos */
		  display: -ms-flex;
		  display: -webkit-flex;
		  display: flex;

		  /* centraliza na vertical */
		  -ms-align-items: center;
		  -webkit-align-items: center;
		  align-items: center;

		  /* centraliza na horizontal */
		  -ms-justify-content: center;
		  -webkit-justify-content: center;
		  justify-content: center;
		}	
	</style>

	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/tables/datatables/extensions/responsive.min.js"></script>
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
	<script src="global_assets/js/plugins/media/fancybox.min.js"></script>

	<script src="global_assets/js/demo_pages/form_layouts.js"></script>
	<script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>	
	<!-- btn group do modal-->
	<script src="global_assets/js/demo_pages/form_input_groups.js"></script>

	<!-- Adicionando Javascript -->
	<script type="text/javascript">
	// transforma as variaveis PHP em variaveis JavaScript atravez do jsonEncode
		<?php
			$js_array = json_encode($count['quantidade']);
			echo "var Maxitens = ".$js_array.";\n";
		?>
		$(document).ready(function() {

			//Aqui sou obrigado a instanciar novamente a utilização do fancybox
			$(".fancybox").fancybox({
				// options
			});


			(function selectSubcateg() {
				const cmbCategoria = $('#cmbCategoria')

				cmbCategoria.on('change', () => {
					const valCategoria = $('#cmbCategoria').val()
					// if adicionado para corrigir bug de ao retirar a seleção da categoria a
					// subcategoria ficava com valor "Filtrando..." e dava erro na requisição
					if(valCategoria){
						Filtrando()
						$.getJSON('filtraSubCategoria.php?idCategoria=' + valCategoria, function(dados) {

						var option = '<option value="">Selecione a SubCategoria</option>';

						if (dados.length) {

							$.each(dados, function(i, obj) {
								option += '<option value="' + obj.SbCatId + '">' + obj.SbCatNome + '</option>';
							});

							$('#cmbSubCategoria').html(option).show();
						} else {
							Reset();
						}
						});
					} else {
						Reset();
					}
				})

				function contaClicks(direc) {
					return
				}

				function editaCarrinhoBotoes() {
					$('.quant-edit').each((i, elem) => {
						$(elem).on('click', function() {
							$('[idProdu]').each((i, elemInp) => {

								if ($(elemInp).attr('idProdu') == $(elem).attr('id')) {
									let quantidade = $(elemInp).val()
									let quantidadeEstoque = $(elemInp).attr('quantiestoque')
									let id = $(elemInp).attr('idProdu')
									const url = 'solicitacaoAlteraCarrinho.php'

									if ($(elem).hasClass('bootstrap-touchspin-up')) {
										if (quantidade <= (quantidadeEstoque - 1)) {
											quantidade++
										}
									}
									if ($(elem).hasClass('bootstrap-touchspin-down')) {
										if (quantidade > 1) {
											quantidade--
										}
									}

									let dataPost = {
										inputQuantidadeProduto: quantidade,
										inputIdProduto: id
									}
									$.post(
										url,
										dataPost,
										function(data) {
											if (!data) {
												$(elemInp).val('')
											} else {
												$(elemInp).val(data)
											}
										}
									)
								}
							})
						})
					})
				}
				editaCarrinhoBotoes()

				function editarCarrinhoInput() {
					$('[quantiEstoque]').each((i, elem) => {
						let quantidadeInicial = $(elem).val()
						$(elem).on('keyup', () => {
							let quantidade = $(elem).val() == ''?$(elem).val() : parseInt($(elem).val())
							let quantidadeEstoque = parseInt($(elem).attr('quantiestoque'))
							let id = $(elem).attr('idProdu')
							const url = 'solicitacaoAlteraCarrinho.php'

							if (quantidade > 0 || quantidade == '') {
								if (quantidade > quantidadeEstoque) {
									$(elem).val(quantidadeEstoque)

									let dataPost = {
										inputQuantidadeProduto: quantidadeEstoque,
										inputIdProduto: id
									}

									$.post(
										url,
										dataPost,
										function(data) {
											if (!data) {
												$(elem).val('')
											} else {
												$(elem).val(data)
											}
										}
									)
								} else {
									if(quantidade == '' || quantidade > 0){
										console.log(quantidade)
										let dataPost = {
											inputQuantidadeProduto: quantidade,
											inputIdProduto: id
										}

										$.post(
											url,
											dataPost,
											function(data) {
												if (!data) {
													$(elem).val('')
												} else {
													$(elem).val(data)
												}
											}
										)
									}
								}
							} else {
								let dataPost = {
									inputQuantidadeProduto: 1,
									inputIdProduto: id
								}

								$.post(
									url,
									dataPost,
									function(data) {
										if (!data) {
											$(elem).val('')
										} else {
											$(elem).val(data)
										}
									}
								)
							}
						})
					})
				}
				editarCarrinhoInput()

				function verificarCarrinhoButtonConcluir() {
					let url = 'solicitacaoVerificarCarrinho.php'
					$.post(
						url,
						function(data) {
							let verifExistProduQuantMaiorZero = 0
							if (data) {
								let carrinho = JSON.parse(data)

								carrinho.forEach(item => {
									if (item.quantidade > 0) {
										verifExistProduQuantMaiorZero += 1
									}
								})
							}
							if (verifExistProduQuantMaiorZero >= 1) {
								$('#confirmar-solicitacao').removeAttr('disabled')
							}
						}
					)
				}

				function verificarCarrinho() {

					// Esta função verifica quais produtos já estão no array em $_SESSION['Carrinho'],
					// para que então eles fiquem com o botão desabilitado no carregamento da página, ou 
					// na chamada ajax no momento da pesquisa. Para isso, faz uma chamada para 
					// 'solicitacaoVerificarCarrinho.php', recebendo um JSON criado a partir de 
					// $_SESSION['Carrinho'], que é utilizado para saber quais produtos carregados
					// na pagina já estão no carrinho.

					let url = 'solicitacaoVerificarCarrinho.php'
					$.post(
						url,
						function(data) {
							// Convertendo a string JSON em um array de Objetos
							let verifExistProduQuantMaiorZero = 0
							if (data) {
								let carrinho = JSON.parse(data)
								var produtoServico = $('input[name="inputProdutoServico"]:checked').val();

								// Iterando sobre o array para ter acesso aos valores id de cada Objeto 
								carrinho.forEach(item => {
									if (item.quantidade > 0) {
										verifExistProduQuantMaiorZero += 1
									}
									$('.add-cart').each((i, elem) => {
										if ($(elem).attr('produId') == item.id && item.quantidade != 0) {
											// Desabilitando o botão e trocando o conteúdo.
											elem.setAttribute('disabled', '')
											var menssagem = produtoServico == 'P'? 'PRODUTO ADICIONADO':'SERVIÇO ADICIONADO'
											$(elem).html(menssagem)
										}
									})
								})
							}
							if (verifExistProduQuantMaiorZero >= 1) {
								$('#confirmar-solicitacao').removeAttr('disabled')
							} else {
								$('#confirmar-solicitacao').attr('disabled', '')
							}
						}
					)
				}
				verificarCarrinho()

				function excluirItemCarrinho() {
					$('[indexExcluir]').each((i, elem) => {
						$(elem).on('click', () => {
							$('[idProdu]').each((i, elemProdu) => {
								if ($(elem).attr('indexExcluir') == $(elemProdu).attr('idprodu')) {
									let elemParent = $(elemProdu).parent().parent().parent()

									let id = $(elemProdu).attr('idprodu')
									const url = 'solicitacaoAlteraCarrinho.php'
									elemParent.fadeOut(400)

									let dataPost = {
										inputQuantidadeProduto: 0,
										inputIdProduto: id
									}

									$.post(
										url,
										dataPost,
										function(data) {
											$('.add-cart').each((i, elemButton) => {
												if ($(elemButton).attr('produid') == $(elem).attr('indexExcluir')) {
													let icon = $('<i class="icon-cart-add mr-2"></i>')
													let text = ' Adicionar ao carrinho'
													$(elemButton).html(icon).append(text)
													$(elemButton).removeAttr('disabled')
												}
											})
											verificarCarrinho()
										}
									)
								}
							})
						})
					})
				}
				excluirItemCarrinho()


				function carrinho() {
					$('.add-cart').each((i, elem) => {
						$(elem).on('click', () => {
							let id = $(elem).attr('produId')
							var produtoServico = $('input[name="inputProdutoServico"]:checked').val();

							$.post(
								'solicitacaoNovoCarrinho.php', {
									inputId: id,
									type: produtoServico
								},
								function(data) {
									if (data) {
										elem.setAttribute('disabled', '')
										var menssagem = produtoServico == 'P'? 'PRODUTO ADICIONADO':'SERVIÇO ADICIONADO'
										$(elem).html(menssagem)
										$('.custon-modal-lista').append(data)
										//editaQuantidade()
										editaCarrinhoBotoes()
										excluirItemCarrinho()
										verificarCarrinho()
										editarCarrinhoInput()
									}
								}
							)
						})
					})
				}
				carrinho()

				function finalizarSolicitacao() {
					$('#confirmar-solicitacao').on('click', function() {
						let url = 'solicitacaoVerificarCarrinho.php'
						$.post(
							url,
							function(data) {
								if (data) {
									let carrinho = JSON.parse(data)
									if (carrinho.length > 0) {
										$('#solicitacao').submit()
									} else {
										console.log('não existem produtos para solicitação')
									}
								}
							}
						)
					})
				}
				finalizarSolicitacao()

				let resultadosConsulta = '';
				let inputsValues = {};

				$('#submitFiltro').on('click', (e) => {
					e.preventDefault()

					var produtoServico = $('input[name="inputProdutoServico"]:checked').val()
					let pesquisaProduto = $('#inputPesquisaProduto').val()
					let categoria = $('#cmbCategoria').val()
					let subCategoria = $('#cmbSubCategoria').val()
					//let marca = $('#cmbMarca').val()
					//let fabricante = $('#cmbFabricante').val()
					//let modelo = $('#cmbModelo').val()

					let url = "solicitacaoFiltraProdutos.php"

					inputsValues = {
						inputProdutoServico: produtoServico, 
						inputPesquisaProduto: pesquisaProduto,
						inputCategoria: categoria,
						inputSubCategoria: subCategoria,
						//inputMarca: marca,
						//inputFabricante: fabricante,
						//inputModelo: modelo,
						min: 0,
						max: 20,
					};

					$.post(
						url,
						inputsValues,
						(data) => {
							if (data) {
								$('#cards-produto').removeClass('justify-content-center px-2')
								$('#cards-produto').html(data)
								resultadosConsulta = data
								var count = parseInt($('#count').val())
								makePagitation(count)

								// Estas duas funções são chamadas a cada requisição Ajax realizada, onde 
								// novos elementos são carregados na tela, para que possam agir sobre eles,
								// como no carregamento da pagina.
								carrinho()
								verificarCarrinho()

								$(".fancybox").fancybox({
									// options
								});
							} else {
								semResultados()
							}
						}
					);
				})

				function semResultados() {
					const msg = $('<div class="card" style="width: 100%"><p class="text-center m-2">Sem resultados...</p></div>')
					const pagina0 = $('<li class="page-item active"><a href="#" class="page-link page-link-white">0</a></li>')
					const btnDireita = $('<li class="page-item"><a href="#" class="page-link page-link-white"><i class="icon-arrow-small-right"></i></a></li>')
					const btnEsquerda = $('<li class="page-item"><a href="#" class="page-link page-link-white"><i class="icon-arrow-small-left"></i></a></li>')

					$('#cards-produto').html(msg)
					$('#cards-produto').addClass('justify-content-center px-2').css('width', '100%')

					$('.pagination').html('')
					$('.pagination').append(btnDireita).append(pagina0).append(btnEsquerda)
				}

				// cria a numeração da paginação de acordo ao numero maximo de itens que vem do banco
				function makePagitation(MaxItens){
					// quantidade de itens por pagina
					var quantItens = 20
					var posicao = 1

					// calcula quantas paginas serão necessarias
					var count = MaxItens%quantItens? (Math.ceil(MaxItens/quantItens)+1):MaxItens/quantItens

					var HTMLpaginacao = "<li id='next' class='page-item'><a class='page-link page-link-white'><i class='icon-arrow-small-right'></i></a></li>"
					for(var x=1; x < count; x++) {
						HTMLpaginacao += "<li id='lista-"+x+"' class='page-item "+(x==posicao?'active':'')+"'><a class='page-link page-link-white'>"+x+"</a></li>"
					}
					HTMLpaginacao += "<li id='back' class='page-item'><a class='page-link page-link-white'><i class='icon-arrow-small-left'></i></a></li>"
					$('#pagination').html(HTMLpaginacao)

					$('.page-item').click(function(e){
					e.preventDefault()

					// pega o id que foi selecionado
					if($(this).attr('id') == 'next'){
						var id = posicao<count ? parseInt(posicao) : parseInt(posicao)+1
					} else if($(this).attr('id') == 'back'){
						var id = posicao==1 ? parseInt(posicao) : parseInt(posicao)-1
					} else {
						var id = parseInt($(this).attr('id').split('-')[1])
					}
					$('#lista-'+posicao).removeClass("active")
					posicao = id
					$('#lista-'+posicao).addClass("active")

					// adiciona o valor maximo de items
					var max = quantItens*id

					// a partir do valor maxio ja adicionado ele adiciona o valor minimo
					var min = ((parseInt(max)+1) - quantItens)

					var produtoServico = $('input[name="inputProdutoServico"]:checked').val();
					let pesquisaProduto = $('#inputPesquisaProduto').val()
					let categoria = $('#cmbCategoria').val()
					let subCategoria = $('#cmbSubCategoria').val()
					//let marca = $('#cmbMarca').val()
					//let fabricante = $('#cmbFabricante').val()
					//let modelo = $('#cmbModelo').val()

					let url = "solicitacaoFiltraProdutos.php";

					inputsValues = {
						inputProdutoServico: produtoServico, 
						inputPesquisaProduto: pesquisaProduto,
						inputCategoria: categoria,
						inputSubCategoria: subCategoria,
						//inputMarca: marca,
						//inputFabricante: fabricante,
						//inputModelo: modelo,
						min: min,
						max: max,
					};

					$.post(
						url,
						inputsValues,
						(data) => {
							if (data) {
								$('#cards-produto').removeClass('justify-content-center px-2')
								$('#cards-produto').html(data)
								resultadosConsulta = data

								// retorna o scroll da pagina para o topo
								$('html, body').animate({ scrollTop: 0 }, 0);

								// Estas duas funções são chamadas a cada requisição Ajax realizada, onde 
								// novos elementos são carregados na tela, para que possam agir sobre eles,
								// como no carregamento da pagina.
								carrinho()
								verificarCarrinho()

								$(".fancybox").fancybox({
									// options
								});
							} else {
								semResultados()
							}
						}
					);
				})
				}
				makePagitation(Maxitens)
			})()

			function Filtrando() {
				$('#cmbSubCategoria').empty().append('<option>Filtrando...</option>');
			}

			function Reset() {
				$('#cmbSubCategoria').empty().append('<option value="">Sem Subcategoria</option>');
			}

			(function modal() {
				$('#btn-modal').on('click', function() {
					$('#page-modal').addClass('custon-modal-show')
					$('body').css('overflow', 'hidden')

					$('#modal-close').on('click', function() {
						$('#page-modal').removeClass('custon-modal-show')
						$('body').css('overflow', 'scroll')
					})
				})
			})()
			/**/
		});
	</script>

</head>

<body class="navbar-top sidebar-xs">

	<?php include_once("topo.php"); ?>

	<!-- Page content -->
	<div class="page-content">

		<?php include_once("menu-left.php"); ?>

		<!-- Main content -->
		<div class="content-wrapper">

			<?php include_once("cabecalho.php"); ?>

			<!-- Content area -->
			<div class="content">
				<!-- Inner container -->
				<div class="d-flex align-items-start flex-column flex-md-row">

					<!-- Left content -->
					<div class="w-100 order-2 order-md-1">

						<div id="produtos" class="row">
							<!--Search Filter-->
							<div id="filter" class="col-12">
								<div class="card pb-3 pr-2">
									<div class="card-header header-elements-inline">
										<h5 class="card-title">Nova Solicitação de Materiais/Serviços</h5>
										<div class="header-elements">
											<a href="solicitacao.php"><< Solicitações</a>
										</div>
									</div>
									<div class="card-bod pt-1 pl-3 pr-2">
										<form class="col-12" id="pesquisa" action="">
											<div class="row">
												<div class="col-lg-2 pt-3">
													<div class="form-group">
														<div class="form-check form-check-inline">
															<label class="form-check-label">
																<input type="radio" name="inputProdutoServico" value="P" class="form-input-styled" checked data-fouc>
																Produto
															</label>
														</div>
														<div class="form-check form-check-inline">
															<label class="form-check-label">
																<input type="radio" name="inputProdutoServico" value="S" class="form-input-styled" data-fouc>
																Serviço
															</label>
														</div>
													</div>
												</div>
												<div class="col-lg-3">
													<div class="form-group">
														<label for="inputPesquisaProduto">Pesquisa</label>
														<div class="form-group form-group-feedback form-group-feedback-right">
															<input id="inputPesquisaProduto" type="search" class="form-control" placeholder="Buscar">
															<div class="form-control-feedback">
																<i class="icon-search4 font-size-base text-muted"></i>
															</div>
														</div>
													</div>
												</div>
												<div class="col-lg-3">
													<div class="form-group">
														<label for="cmbCategoria">Categoria</label>
														<select id="cmbCategoria" name="cmbCategoria" class="form-control form-control-select2">
															<option value="">Selecionar</option>
															<?php
															$sql = "SELECT CategId, CategNome
																	FROM Categoria		
																	JOIN Situacao on SituaId = CategStatus											     
																	WHERE CategEmpresa = ".$_SESSION['EmpreId']." and SituaChave = 'ATIVO'
																	ORDER BY CategNome ASC";
															$result = $conn->query($sql);
															$rowCateg = $result->fetchAll(PDO::FETCH_ASSOC);

															foreach ($rowCateg as $item) {
																print('<option value="' . $item['CategId'] . '">' . $item['CategNome'] . '</option>');
															}
															?>
														</select>
													</div>
												</div>
												<div class="col-lg-2">
													<div class="form-group">
														<label for="cmbSubCategoria">SubCategoria</label>
														<select id="cmbSubCategoria" name="cmbSubCategoria" class="form-control form-control-select2">
															<option value="">Selecionar</option>
														</select>
													</div>
												</div>
												<div class="col-lg-2">
													<div class="form-group pt-4">
														<button id="submitFiltro" class="btn btn-principal form-control">Consultar</button>
													</div>
												</div>
											</div>
											<!--
											<div class="row">
												<div class="col-lg-4">
													<div class="form-group">
														<label for="cmbMarca">Marca</label>
														<select id="cmbMarca" name="cmbMarca" class="form-control form-control-select2">
															<option value="">Selecionar</option>
															<?php
															/*
															$sql = "SELECT MarcaId, MarcaNome
																	FROM Marca
																	JOIN Situacao on SituaId = MarcaStatus											     
																	WHERE MarcaEmpresa = " . $_SESSION['EmpreId'] . " and SituaChave = 'ATIVO'
																	ORDER BY MarcaNome ASC";
															$result = $conn->query($sql);
															$rowMarca = $result->fetchAll(PDO::FETCH_ASSOC);

															foreach ($rowMarca as $item) {
																print('<option value="' . $item['MarcaId'] . '">' . $item['MarcaNome'] . '</option>');
															}
															*/
															?>
														</select>
													</div>
												</div>
												<div class="col-lg-3">
													<div class="form-group">
														<label for="cmbModelo">Modelo</label>
														<select id="cmbModelo" name="cmbModelo" class="form-control form-control-select2">
															<option value="">Selecionar</option>
															<?php
															/*
															$sql = "SELECT ModelId, ModelNome
																	FROM Modelo														     
																	JOIN Situacao on SituaId = ModelStatus											     
																	WHERE ModelEmpresa = " . $_SESSION['EmpreId'] . " and SituaChave = 'ATIVO'
																	ORDER BY ModelNome ASC";
															$result = $conn->query($sql);
															$rowModel = $result->fetchAll(PDO::FETCH_ASSOC);

															foreach ($rowModel as $item) {
																print('<option value="' . $item['ModelId'] . '">' . $item['ModelNome'] . '</option>');
															}
															*/
															?>
														</select>
													</div>
												</div>
												<div class="col-lg-3">
													<div class="form-group">
														<label for="cmbFabricante">Fabricante</label>
														<select id="cmbFabricante" name="cmbFabricante" class="form-control form-control-select2">
															<option value="">Selecionar</option>
															<?php
															/*
															$sql = "SELECT FabriId, FabriNome
																	FROM Fabricante														     
																	JOIN Situacao on SituaId = FabriStatus											     
																	WHERE FabriEmpresa = " . $_SESSION['EmpreId'] . " and SituaChave = 'ATIVO'
																	ORDER BY FabriNome ASC";
															$result = $conn->query($sql);
															$rowFabri = $result->fetchAll(PDO::FETCH_ASSOC);
															// var_dump($rowFabri);

															foreach ($rowFabri as $item) {
																print('<option value="' . $item['FabriId'] . '">' . $item['FabriNome'] . '</option>');
															}
															*/
															?>
														</select>
													</div>
												</div>
											</div>-->
										</form>
									</div>
								</div>
							</div>

							<!--Buttom Modal-->
							<ul id="btn-modal" class="fab-menu fab-menu-fixed fab-menu-bottom-right" data-fab-toggle="click">
								<li>
									<a class="fab-menu-btn btn bg-blue btn-float rounded-round btn-icon">
										<i class="fab-icon-open icon-cart"></i>
										<i class="fab-icon-close icon-cart"></i>
									</a>
								</li>
							</ul>

							<div id="cards-produto" class="col-12 row m-0 px-0">
								<?php

								$sFoto = '';

								foreach ($row as $item) {

									if ($item['ProduFoto'] != null) {

										//Depois verifica se o arquivo físico ainda existe no servidor
										if (file_exists("global_assets/images/produtos/" . $item['ProduFoto'])) {
											$sFoto = "global_assets/images/produtos/" . $item['ProduFoto'];
										} else {
											$sFoto = "global_assets/images/lamparinas/sem_foto.gif";
										}
									} else {
										$sFoto = "global_assets/images/lamparinas/sem_foto.gif";
									}

									// Este trecho de código formata o titulo limitando o seu tamanho.
									$titulo = strlen($item['ProduNome']);
									$tituloArray = str_split(utf8_decode($item['ProduNome']));

									for ($i = 0; $i <= $titulo - 1; $i++) {
										if ($i > 70) {
											unset($tituloArray[$i]);
										}
									}

									$novaString = implode("", $tituloArray);
									$novoTitulo = utf8_encode($novaString);

									if ($titulo > 70) {
										$novoTitulo .= "...";
									}
									//

									if ($item['Estoque'] > 0) {
										print('
		                                    <div class="col-xl-3 col-lg-4 col-sm-6">
			                                    <div class="card">
				                                    <div class="card-body">
					                                    <div class="card-img-actions" id="Imagens">
						                                    <a href="' . $sFoto . '" class="fancybox">
							                                    <img src="' . $sFoto . '" class="card-img"  alt="" style="max-height:250px;">
							                                    <span class="card-img-actions-overlay card-img">
								                                    <i class="icon-plus3 icon-2x"></i>
							                                    </span>
						                                    </a>
					                                    </div>
				                                    </div>

				                                    <div class="card-body bg-light text-center">
					                                    <div class="mb-2">
					                                    	<h6 class="font-weight-semibold mb-0" data-popup="tooltip" title="' . $item['ProduDetalhamento'] . '" style="height: 46.1667px; overflow: hidden">
						                                    	<a href="#" class="text-default">' . $novoTitulo . '</a>
						                                    </h6>

						                                    <a href="#" class="text-muted">' . $item['CategNome'] . '</a>
					                                    </div>
					                                    <div class="text-muted mb-3">' . $item['Estoque'] . ' em estoque</div>

					                                    <button produId=' . $item['ProduId'] . ' type="button" class="btn btn-produtos bg-teal-400 add-cart"><i class="icon-cart-add mr-2"></i> Adicionar ao carrinho</button>
				                                    </div>
			                                    </div>
	                                	    </div>							
                                    	');
									} else {
										print('
		                                    <div class="col-xl-3 col-lg-4 col-sm-6">
			                                    <div class="card">
				                                    <div class="card-body">
					                                    <div class="card-img-actions" id="Imagens">
						                                    <a href="' . $sFoto . '" class="fancybox">
							                                    <img src="' . $sFoto . '" class="card-img"  alt="" style="max-height:250px;">
							                                    <span class="card-img-actions-overlay card-img">
								                                    <i class="icon-plus3 icon-2x"></i>
							                                    </span>
						                                    </a>
					                                    </div>
				                                    </div>

				                                    <div class="card-body bg-light text-center">
					                                    <div class="mb-2">
					                                    	<h6 class="font-weight-semibold mb-0" data-popup="tooltip" title="' . $item['ProduDetalhamento'] . '" style="height: 46.1667px; overflow: hidden">
						                                    	<a href="#" class="text-default">' . $novoTitulo . '</a>
						                                    </h6>

						                                    <a href="#" class="text-muted">' . $item['CategNome'] . '</a>
					                                    </div>
					                                    <div class="text-muted mb-3">' . $item['Estoque'] . ' em estoque</div>

					                                    <button produId=' . $item['ProduId'] . ' type="button" class="btn btn-produtos bg-teal-400 add-cart" disabled><i class="icon-cart-add mr-2"></i> Adicionar ao carrinho</button>
				                                    </div>
			                                    </div>
	                                	    </div>							
                                    	');
									}
								}
								?>
							</div>
							<form id="addItemCart" name="addItemCart" action="" method="POST">
								<input id="inputProdutoId" type="hidden" name="inputProdutoId">
							</form>
						</div>
						<!-- /grid -->


						<!-- Pagination -->
						<div class="d-flex justify-content-center pt-3 mb-3">
							<ul id="pagination" class="pagination shadow-1">
								
							</ul>
						</div>
						<form id="formPaginacao" method="POST" action="solicitacaoNovo.php">
							<input id="min" type="hidden" name="min" value="" />
							<input id="max" type="hidden" name="max" value="" />
							<input id="tipoPagina" type="hidden" name="tipoPagina" value="" />
							<input id="posicao" type="hidden" name="posicao" value="" />
						</form>
						<!-- /pagination -->

					</div>
					<!-- /left content -->

				</div>
				<!-- /inner container -->
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
					<?php
					if (isset($_SESSION['Carrinho'])) {

						foreach ($_SESSION['Carrinho'] as $item) {
							if ($item['quantidade'] > 0) {
								$sql = "SELECT ProduId, ProduCodigo, ProduNome, ProduFoto, CategNome, dbo.fnSaldoEstoque(" . $_SESSION['UnidadeId'] . ", ProduId, 'P', NULL) as Estoque
		                            FROM Produto
		                            JOIN Categoria on CategId = ProduCategoria
									JOIN Situacao on SituaId = ProduStatus
	                                WHERE ProduId = " . $item['id'] . " and ProduEmpresa = " . $_SESSION['EmpreId'] . " and SituaChave = 'ATIVO'
		                            ";
								$result = $conn->query($sql);
								$row = $result->fetch(PDO::FETCH_ASSOC);

								print('
							
							        <div class="custon-modal-produto">
							            <div class="custon-modal-produTitle d-flex flex-column col-12 col-sm-5 col-lg-8">
							            	<p>' . $row['ProduNome'] . '</p>
							            	<p>' . $row['CategNome'] . '</p>
							            </div>
							            <div class="modal-controles col-12 col-sm-7 col-lg-4 row justify-content-md-center align-items-center mx-0">
							            	<div class="input-group bootstrap-touchspin col-9 col-sm-9">
							            		<span class="input-group-prepend">
							            			<button id="' . $row['ProduId'] . '" class="btn btn-light bootstrap-touchspin-down quant-edit" type="button">–</button>
							            		</span>
							            		<span class="input-group-prepend bootstrap-touchspin-prefix d-none">
							            			<span class="input-group-text"></span>
							            		</span>
							            		<input quantiEstoque="' . $row['Estoque'] . '" idProdu="' . $row['ProduId'] . '" style="text-align: center" type="text" value="' . $item['quantidade'] . '" class="form-control touchspin-set-value" style="display: block;">
							            		<span class="input-group-append bootstrap-touchspin-postfix d-none">
							            			<span class="input-group-text"></span>
							            		</span>
							            		<span class="input-group-append">
							            			<button id="' . $row['ProduId'] . '" class="btn btn-light bootstrap-touchspin-up quant-edit" type="button">+</button>
							            		</span>
							            	</div>
							            	<div class="col-3 col-sm-3 row m-0">
							            	    <button class="btn" indexExcluir=' . $row['ProduId'] . '><i class="fab-icon-open icon-bin2 excluir-item"></i></button>
							            	</div>
							            </div>
						            </div>
							
							     ');
							}
						}
					}
					?>
				</div>
				<div class="card-footer mt-2 d-flex flex-column">
					<form id="solicitacao" method="POST" action="solicitacaoNovoConcluir.php">
						<!--<input id="inputObservacao" type="hidden" name="inputObservacao">-->
						<div class="row">
							<div class="col-lg-12">
								<div class="form-group">
									<label for="txtObservacao">Observação</label>
									<textarea rows="3" cols="5" class="form-control textarea-modal" id="txtObservacao" name="txtObservacao" placeholder="Observações sobre a solicitação..."></textarea>
								</div>
							</div>
						</div>
					</form>
					<button id="confirmar-solicitacao" class="btn btn-principal" disabled>Confirmar</button>
				</div>
			</div>
		</div>
	</div>

</body>

</html>