<?php

include_once("sessao.php");

$_SESSION['PaginaAtual'] = 'Nova Movimentação';

include('global_assets/php/conexao.php');

if (isset($_POST['inputData'])) {
	try {
		$sql = "INSERT INTO Movimentacao (MovimTipo, MovimMotivo, MovimData, MovimFinalidade, MovimOrigemLocal, MovimOrigemSetor, MovimDestinoLocal, MovimDestinoSetor, MovimDestinoManual, 
										  MovimObservacao, MovimFornecedor, MovimOrdemCompra, MovimNotaFiscal, MovimDataEmissao, MovimNumSerie, MovimValorTotal, 
										  MovimChaveAcesso, MovimSituacao, MovimUsuarioAtualizador, MovimUnidade)
				VALUES (:sTipo, :iMotivo, :dData, :iFinalidade, :iOrigemLocal, :iOrigemSetor, :iDestinoLocal, :iDestinoSetor, :sDestinoManual, 
						:sObservacao, :iFornecedor, :iOrdemCompra, :sNotaFiscal, :dDataEmissao, :sNumSerie, :fValorTotal, 
						:sChaveAcesso, :iSituacao, :iUsuarioAtualizador, :iUnidade)";
		$result = $conn->prepare($sql);

		$conn->beginTransaction();

		$result->execute(array(
			':sTipo' => 'E',
			':iMotivo' => null,
			':dData' => gravaData($_POST['inputData']),
			':iFinalidade' => null,

			':iOrigemLocal' => null,
			':iOrigemSetor' => null,

			':iDestinoLocal' => $_POST['cmbDestinoLocal'],
			':iDestinoSetor' => null,
			':sDestinoManual' => null,

			':sObservacao' => $_POST['txtareaObservacao'],
			':iFornecedor' => $_POST['cmbFornecedor'] == '' ? null : $_POST['cmbFornecedor'],
			':iOrdemCompra' => $_POST['cmbOrdemCompra'] == '' ? null : $_POST['cmbOrdemCompra'],
			':sNotaFiscal' => $_POST['inputNotaFiscal'] == '' ? null : $_POST['inputNotaFiscal'],
			':dDataEmissao' => $_POST['inputDataEmissao'] == '' ? null : $_POST['inputDataEmissao'],
			':sNumSerie' => $_POST['inputNumSerie'] == '' ? null : $_POST['inputNumSerie'],
			':fValorTotal' => $_POST['inputValorTotal'] == '' ? null : gravaValor($_POST['inputValorTotal']),
			':sChaveAcesso' => $_POST['inputChaveAcesso'] == '' ? null : $_POST['inputChaveAcesso'],
			':iSituacao' => $_POST['cmbSituacao'] == '' ? null : $_POST['cmbSituacao'],
			':iUsuarioAtualizador' => $_SESSION['UsuarId'],
			':iUnidade' => $_SESSION['UnidadeId']
		));

		$insertId = $conn->lastInsertId();

		$numItems = intval($_POST['inputNumItens']);

		// essa etapa vai buscar o Saldo Remanescente da ordem de compra
		$sqlMovimentacao = "SELECT OrComId, OrComSaldoRemanescente, OrComFluxoOperacional
		FROM Movimentacao
		JOIN OrdemCompra on OrComId = MovimOrdemCompra
		WHERE MovimId = $insertId";
		$resultMovimentacao = $conn->query($sqlMovimentacao);
		$rowMovimentacao = $resultMovimentacao->fetch(PDO::FETCH_ASSOC);

		$sReferencia = 'P';

		/* Caso exista Saldo Remanescente deve ser verificado a existencia de um aditivo caso exista
		deve ser utilisado o "AditiNumero" do PENULTIMO aditivo*/
		if($rowMovimentacao['OrComSaldoRemanescente'] == 1){
			$sqlAdt = "SELECT (MAX(AditiNumero)-1) as AditiNumero FROM Aditivo
			WHERE AditiValor is not null and AditiUnidade = ".$_SESSION['UnidadeId']." and AditiFluxoOperacional = ".$rowMovimentacao['OrComFluxoOperacional'];
			$resultAdt = $conn->query($sqlAdt);
			$rowAdt = $resultAdt->fetch(PDO::FETCH_ASSOC);

			/* Caso encontre o aditivo sera setado na variavel $sReferencia o numero do aditivo ex.: "A1", "A2"
			caso contrario sera setado em $sReferencia o valor "P" de principal*/
			if($rowAdt['AditiNumero'] != null){
				$sReferencia = 'A'.$rowAdt['AditiNumero'];
			}
		} else {
			/* Caso NÃO exista Saldo Remanescente deve ser verificado a existencia de um aditivo caso exista
			deve ser utilisado o "AditiNumero" do ULTIMO aditivo*/
			$sqlAdt = "SELECT MAX(AditiNumero) as AditiNumero FROM Aditivo
			WHERE AditiValor is not null and AditiUnidade = ".$_SESSION['UnidadeId']." and AditiFluxoOperacional = ".$rowMovimentacao['OrComFluxoOperacional'];
			$resultAdt = $conn->query($sqlAdt);
			$rowAdt = $resultAdt->fetch(PDO::FETCH_ASSOC);

			/* Caso encontre o aditivo sera setado na variavel $sReferencia o numero do aditivo ex.: "A1", "A2"
			caso contrario sera setado em $sReferencia o valor "P" de principal*/
			if($rowAdt['AditiNumero'] != null){
				$sReferencia = 'A'.$rowAdt['AditiNumero'];
			}
		}

		for ($i = 1; $i <=  $numItems; $i++) {

			$campoSoma = $i;

			$campo = 'campo' . $i;

			//Aqui tenho que fazer esse IF, por causa das exclusões da Grid

			if (isset($_POST[$campo])) {
				//var_dump($campo);
				$registro = explode('#', $_POST[$campo]);
				// var_dump($registro);
				// exit();

				if ($registro[0] == 'P') {

					$quantItens = intval($registro[3]);  //quantidade informada no modal

					if ((int) $registro[3] > 0) {
						$sql = "INSERT INTO MovimentacaoXProduto
								(MvXPrMovimentacao, MvXPrProduto, MvXPrDetalhamento, MvXPrQuantidade, MvXPrValorUnitario, MvXPrLote,
								MvXPrValidade, MvXPrClassificacao, MvXPrUsuarioAtualizador, MvXPrUnidade, MvXPrPatrimonio,
								MvXPrAnoFabricacao, MvXPrNumSerie, MvXPrReferencia)
								VALUES
								(:iMovimentacao, :iProduto, :sDetalhamento, :iQuantidade, :fValorUnitario, :sLote, :dValidade, :iClassificacao,
								:iUsuarioAtualizador, :iUnidade, :iPatrimonio, :iFabricacao, :iNumSerie, :sReferencia)";
						$result = $conn->prepare($sql);

						$result->execute(array(
							':iMovimentacao' => $insertId,
							':iProduto' => $registro[1],
							':sDetalhamento' => $registro[9],
							':iQuantidade' => (int) $registro[3],
							':fValorUnitario' => isset($registro[2]) ? (float) $registro[2] : null,
							':sLote' => $registro[5],
							':dValidade' => $registro[6] != '0' ? $registro[6] : gravaData('12/09/2333'),
							':iNumSerie' => isset($registro[7])? $registro[7] : '',
							':iFabricacao' => $registro[8] != '0' ? $registro[8] : (gravaData('12/09/2333') ? $registro[8] : null),
							':iClassificacao' => isset($registro[9]) ? (int) $registro[9] : null,
							':iUsuarioAtualizador' => $_SESSION['UsuarId'],
							':iUnidade' => $_SESSION['UnidadeId'],
							':iPatrimonio' => null,
							':sReferencia' => $sReferencia
						));
					}
				} else {
					$sql = "INSERT INTO MovimentacaoXServico
							(MvXSrMovimentacao, MvXSrServico, MvXSrDetalhamento, MvXSrQuantidade, MvXSrValorUnitario, MvXSrUsuarioAtualizador, MvXSrUnidade, MvXSrReferencia)
							VALUES
							(:iMovimentacao, :iServico, :sDetalhamento,  :iQuantidade, :fValorUnitario, :iUsuarioAtualizador, :iUnidade, :sReferencia)";
					$result = $conn->prepare($sql);

					$result->execute(array(
						':iMovimentacao' => $insertId,
						':iServico' => $registro[1],
						':sDetalhamento' => $registro[9],
						':iQuantidade' => (int) $registro[3],
						':fValorUnitario' => isset($registro[2]) ? (float) $registro[2] : null,
						':iUsuarioAtualizador' => $_SESSION['UsuarId'],
						':iUnidade' => $_SESSION['UnidadeId'],
						':sReferencia' => $sReferencia
					));
				}
			}
		}

		if (isset($_POST['cmbSituacao'])) {

			$sql = "SELECT SituaId, SituaNome, SituaChave
					FROM Situacao
					WHERE SituaId = " . $_POST['cmbSituacao'] . "
					";
			$result = $conn->query($sql);
			$rowSituacao = $result->fetch(PDO::FETCH_ASSOC);

			$destinoChave = '';

			if ($rowSituacao['SituaChave'] == 'AGUARDANDOLIBERACAOCENTRO') $destinoChave = 'CENTROADMINISTRATIVO';
			if ($rowSituacao['SituaChave'] == 'PENDENTE') $destinoChave = 'ALMOXARIFADO';

			if ($rowSituacao['SituaChave'] != 'LIBERADO') {
				$sql = "SELECT PerfiId
				        FROM Perfil
				        WHERE PerfiChave = '" . $destinoChave . "' and PerfiUnidade = " . $_SESSION['UnidadeId'];
				$result = $conn->query($sql);
				$rowPerfil = $result->fetch(PDO::FETCH_ASSOC);


				/* Insere na Bandeja para Aprovação do perfil ADMINISTRADOR ou CONTROLADORIA */
				$sIdentificacao = 'Movimentação';

				$sql = "INSERT INTO Bandeja (BandeIdentificacao, BandeData, BandeDescricao, BandeURL, BandeSolicitante, 
							BandeSolicitanteSetor, BandeTabela, BandeTabelaId, BandeStatus, BandeUsuarioAtualizador, BandeUnidade)
					VALUES (:sIdentificacao, :dData, :sDescricao, :sURL, :iSolicitante, :iSolicitanteSetor, :sTabela, 
							:iTabelaId, :iStatus, :iUsuarioAtualizador, :iUnidade)";
				$result = $conn->prepare($sql);

				$result->execute(array(
					':sIdentificacao' => $sIdentificacao,
					':dData' => date("Y-m-d"),
					':sDescricao' => 'Liberar Movimentacao',
					':sURL' => '',
					':iSolicitante' => $_SESSION['UsuarId'],
					':iSolicitanteSetor' => null,
					':sTabela' => 'Movimentacao',
					':iTabelaId' => $insertId,
					':iStatus' => $rowSituacao['SituaId'],
					':iUsuarioAtualizador' => $_SESSION['UsuarId'],
					':iUnidade' => $_SESSION['UnidadeId']
				));

				$insertIdBande = $conn->lastInsertId();

				$sql = "INSERT INTO BandejaXPerfil (BnXPeBandeja, BnXPePerfil, BnXPeUnidade)
						VALUES (:iBandeja, :iPerfil, :iUnidade)";
				$result = $conn->prepare($sql);

				$result->execute(array(
					':iBandeja' => $insertIdBande,
					':iPerfil' => $rowPerfil['PerfiId'],
					':iUnidade' => $_SESSION['UnidadeId']
				));

				/* Fim Insere Bandeja */
			}
		}

		$conn->commit();

		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Movimentação realizada!!!";
		$_SESSION['msg']['tipo'] = "success";
	} catch (PDOException $e) {

		$conn->rollback();

		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao realizar movimentação!!!";
		$_SESSION['msg']['tipo'] = "error";

		echo 'Error: ' . $e->getMessage();
		exit;
	}

	irpara("movimentacao.php");
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Movimentação</title>

	<?php include_once("head.php"); ?>

	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/tables/datatables/datatables.min.js"></script>
	<script src="global_assets/js/plugins/tables/datatables/extensions/responsive.min.js"></script>

	<script src="global_assets/js/demo_pages/datatables_responsive.js"></script>
	<script src="global_assets/js/demo_pages/datatables_sorting.js"></script>

	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
	<script src="global_assets/js/demo_pages/form_select2.js"></script>

	<script src="global_assets/js/demo_pages/form_layouts.js"></script>
	<script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>

	<!-- Validação -->
	<script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
	<script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
	<script src="global_assets/js/demo_pages/form_validation.js"></script>

	<script src="global_assets/js/lamparinas/jquery.maskMoney.js"></script> <!-- http://www.fabiobmed.com.br/criando-mascaras-para-moedas-com-jquery/ -->
	<!-- /theme JS files -->

	<!-- Adicionando Javascript -->
	<script type="text/javascript">

		$(document).ready(function() {

			function verificaTotalNotaFiscal() {
				let valorTotalNotaFiscal = $('#inputValorTotal').val().replaceAll('.', '').replace(',', '.')
				let valorTotalNotaFiscalGrid = $('#total').attr('valor')

				if (parseFloat(valorTotalNotaFiscalGrid) != parseFloat(valorTotalNotaFiscal)) {
					alerta('Atenção', 'O valor total da Nota Fiscal informado não corresponde ao total da entrada.', 'error');
					$('#inputValorTotal').focus();
					return false;
				}

				return true;
			}

			function calcSaldoOrdemCompra() {
				let valorTotal = $('#total').attr('valor')
				let valorSaldoOrdemCompra = $("#totalSaldo").attr('valorTotalInicial')
				let calcSaldoAtual = (parseFloat(valorSaldoOrdemCompra) - parseFloat(valorTotal))

				if (calcSaldoAtual < 0) {
					alerta('Atenção', 'O valor total da Ordem de Compra foi ultrapassado.', 'error');
					$('#totalSaldo').html('R$ ' + float2moeda(calcSaldoAtual)).attr('valor', calcSaldoAtual)
					$('#inputValorTotal').focus();
					return false;
				} 
				
				$('#totalSaldo').html('R$ ' + float2moeda(calcSaldoAtual)).attr('valor', calcSaldoAtual)

				return true;
			}

			//Ao mudar o fornecedor, filtra a categoria, subcategoria e produto via ajax (retorno via JSON)
			$('#cmbFornecedor').on('change', function(e) {

				var inputFornecedor = $('#inputFornecedor').val();
				var cmbFornecedor = $('#cmbFornecedor').val();

				$('#inputFornecedor').val(cmbFornecedor);

				FiltraOrdensCompra();

				$.get('filtraOrdemCompra.php?idFornecedor=' + cmbFornecedor, function(dados) {

					var option = '<option value="">Selecione</option>';
					if (dados) {
						$('#cmbOrdemCompra').html(option).show();
						$('#cmbOrdemCompra').append(dados).show();

					} else {
						$('#cmbOrdemCompra').html(option).show();
					}
				});

			});

			$('#cmbOrdemCompra').on('change', function() {
				
				var ordemCompra = '';

				//Buscao o ID da OrdemCompra que está no atributo idOrdemCompra do option do select (pra usar logo abaixo)
				$('#cmbOrdemCompra').children().each((i, elem) => {
					if ($(elem).val() == $('#cmbOrdemCompra').val()) {
						ordemCompra = $(elem).attr('idOrdemCompra');
					}
				});

				if (ordemCompra) {

					produtosOrdemCompra(ordemCompra)
				}

			})

			//Valida Registro Duplicado
			$('#enviar').on('click', function(e) {
				console.log('entrou no click');

				// close modal
				$('#page-modal').fadeOut(200);
				$('body').css('overflow', 'scroll');

				var inputTotal = $('#inputTotal').val();
				var cmbOrdemCompra = $('#cmbOrdemCompra').val();
				var cmbFornecedor = $('#cmbFornecedor').val();
				var cmbDestinoLocal = $('#cmbDestinoLocal').val();
				var inputValorTotal = $('#inputValorTotal').val();
				var inputNotaFiscal = $('#inputNotaFiscal').val();
				var itemEditado = $('#itemEditadoquantidade').val();
				var validadeNaoInformada = $('#validadeNaoInformada').val();

				//Verifica se a combo Estoque de Destino foi informada
				if (cmbDestinoLocal == '') {
					alerta('Atenção', 'Informe o Estoque de Destino!', 'error');
					$('#cmbDestinoLocal').focus();
					return false;
				}

				if (cmbFornecedor == '') {
					alerta('Atenção', 'Informe o Fornecedor!', 'error');
					$('#cmbFornecedor').focus();	
					return false;
				}

				if (cmbOrdemCompra == '') {
					alerta('Atenção', 'Informe a Ordem Compra / Carta Contrato!', 'error');
					$('#cmbOrdemCompra').focus();
					return false;
				}

				if (inputNotaFiscal == '') {
					alerta('Atenção', 'Informe Nº Nota Fiscal', 'error');
					$('#inputNotaFiscal').focus();	
					return false;
				}

				if (inputValorTotal == '') {
					alerta('Atenção', 'Informe o Valor Total da Nota Fiscal!', 'error');
					$('#inputValorTotal').focus();
					return false;
				}

				// Verifica se pelo menos um produto ou serviço foi editado, na entrada.
				if (itemEditado == 0) {
					alerta('Atenção', 'Informe a quantidade de itens que deseja dar entrada no sistema!', 'error');
					return false;
				}

				if (validadeNaoInformada == '' && $('#tipo').val() != 'S') {
					alerta('Atenção', 'Tem itens sem validade!', 'error');
					return false;
				}				

				if (inputValorTotal == '') {
					alerta('Atenção', 'Informe o valor Total da nota fiscal!', 'error');
					return false;
				}

				if (!verificaTotalNotaFiscal()){
					return false;
				}

				//Verifica se tem algum produto na Grid
				if (inputTotal == '' || inputTotal == 0) {
					alerta('Atenção', 'Informe algum produto!', 'error');
					$("#formMovimentacao").submit();
					return false;
				}

				if (cmbSituacao == '') {
					alerta('Atenção', 'Informe a Situacao!', 'error');
					$('#cmbSituacao').focus();
					$("#formMovimentacao").submit();
					return false;
				}

				if (!calcSaldoOrdemCompra()){
					return false;
				}

				//desabilita as combos "Fornecedor" e "Situacao" na hora de gravar, senão o POST não o encontra
				//$('#cmbFornecedor').prop('disabled', false);
				$('#cmbSituacao').prop('disabled', false);

				//desabilita o botão Incluir evitando duplo clique, ou seja, evitando inserções duplicadas
				$('#enviar').prop("disabled", true);

				$("#formMovimentacao").submit();
			});

			function FiltraOrdensCompra() {
				$('#cmbOrdemCompra').empty().append('<option value="">Filtrando...</option>');
			}

			function formatDate(data, formato) {
				if (formato == 'pt-BR') {
					return (data.substr(0, 10).split('-').reverse().join('/'));
				} else {
					return (data.substr(0, 10).split('/').reverse().join('-'));
				}
			}

			function produtosOrdemCompra(ordemCompra) {
				let inputLote = $('#inputLote').val();

				$.ajax({
					type: "POST",
					url: "movimentacaoAddProdutoOrdemCompra.php",
					data: {
						ordemCompra: ordemCompra,
						lote: inputLote
					},
					success: function(resposta) {
						$("#tabelaProdutoServico").html(resposta);

						let total = $('#total').html();

						$("#inputTotal").val(total);
						modalAcoes()
						mudarValores()

						let todasLinhas = $('.trGrid')
						$('#inputNumItens').val(todasLinhas.length)
					}

				});

				$.ajax({
					type: "POST",
					url: "movimentacaoSaldoOrdemCompra.php",
					data: {
						ordemCompra: ordemCompra,
					},
					success: function(resposta) {
						$("#inputTotalOrdemCompraCartaContrato").val(float2moeda(resposta)).attr('disabled', '').attr('valor', resposta);
					}
				})
			}

			function modalAcoes() {

				$('.btn-acoes').each((i, elem) => {
					$(elem).on('click', function() {
						let linha = $(elem).parent().parent()
						let todasLinhas = $(elem).parent().parent().parent()
						let saldoinicialModal = $(elem).parent().next().attr('saldoInicial') // selecionando o valor do input hidden

						if ($(elem).attr('idRow') == linha.attr('id')) {
							let tds = linha.children();
							let tipoProdutoServico = $(tds[9]).attr('tipo');
							$('#tipo').val(tipoProdutoServico)

							let valores = [];

							let Marca = ''
							let Modelo = ''
							let Fabri = ''

							let linhaTabela = ''

							let idTd = $(tds[0]).text()
							let valorCampo = $(`#campo${idTd}`).val()
							valorCampo = valorCampo.split('#')

							console.log(valorCampo)
							// value='$item[tipo] # $item[id] # $item[valorCusto] # 0 # 0 # 0 # 0 # $item[detalhamento]'

							let quantidade = parseInt(valorCampo[3]) < parseInt(valorCampo[4])? valorCampo[3] : valorCampo[4]
							let lote = valorCampo[5] && valorCampo[5] != '0'? valorCampo[5]: ''
							let validade = valorCampo[6] && valorCampo[6] != '0'? valorCampo[6]: ''
							let numSerie = valorCampo[7] && valorCampo[7] != '0'? valorCampo[7]: ''
							let fabricacao = valorCampo[8] && valorCampo[8] != '0'? valorCampo[8]: ''

							tds.each((i, elem) => {
								let id = $(elem).attr('id')
								valores[i] = $(elem).html();
								Marca = id === 'MarcaNome' && $(elem).val() ? $(elem).val():Marca
								Modelo = id === 'ModelNome' && $(elem).val() ? $(elem).val():Modelo
								Fabri = id === 'FabriNome' && $(elem).val() ? $(elem).val():Fabri
							})

							if (tipoProdutoServico != 'P') {
								HTML = `
								<div class="row col-lg-12" style="padding:0px; margin:0px;">
									<div class="col-lg-4">
										<label for="inputServicoNome">Serviço</label>
										<input type="text" id="inputServicoNome" name="inputServicoNome" class="form-control" value="${valores[1]}" disabled>
									</div>
									<div class="col-lg-2">
										<label for="inputMarcaNome">Marca</label>
										<input type="text" id="inputMarcaNome" name="inputMarcaNome" class="form-control" value="${Marca}" disabled>
									</div>
									<div class="col-lg-2">
										<label for="inputModeloNome">Modelo</label>
										<input type="text" id="inputModeloNome" name="inputModeloNome" class="form-control" value="${Modelo}" disabled>
									</div>
									<div class="col-lg-2">
										<label for="inputFabriNome">Fabricante</label>
										<input type="text" id="inputFabriNome" name="inputFabriNome" class="form-control" value="${Fabri}" disabled>
									</div>
									<div class="col-lg-1">
										<label for="saldo">Saldo</label>
										<input id='saldo' class="form-control" style="text-align: center"  value="${saldoinicialModal}" disabled>
									</div>
									<div class="col-lg-1">
										<label for="quantidade">Quantidade</label>
										<input id='quantidade' quantMax='${valores[4]}' type="text" class="form-control" value="${quantidade}" onkeypress="return onlynumber(event)" style="text-align: center" autofocus>
									</div>
									<div class="d-none">
										<input id='idModal' type="hidden" value="${idTd}">
									</div>
								</div>`;
							} else {
								HTML = `
								<div class="row col-lg-12" style="padding:0px; margin:0px;">
									<div class="col-lg-6">
										<label for="inputProdutoNome">Produto</label>
										<input type="text" id="inputProdutoNome" name="inputProdutoNome" class="form-control" value="${valores[1]}" disabled>
									</div>
									<div class="col-lg-2">
										<label for="inputMarcaNome">Marca</label>
										<input type="text" id="inputMarcaNome" name="inputMarcaNome" class="form-control" value="${Marca}" disabled>
									</div>
									<div class="col-lg-2">
										<label for="inputModeloNome">Modelo</label>
										<input type="text" id="inputModeloNome" name="inputModeloNome" class="form-control" value="${Modelo}" disabled>
									</div>
									<div class="col-lg-2">
										<label for="inputFabriNome">Fabricante</label>
										<input type="text" id="inputFabriNome" name="inputFabriNome" class="form-control" value="${Fabri}" disabled>
									</div>
								</div>
								
								<div class="row col-lg-12" style="padding:0px; margin:0px;">
									<div class="col-lg-1">
										<label for="saldo">Saldo</label>
										<input id='saldo' type="text" class="form-control" value="${saldoinicialModal}" style="text-align: center" disabled>
									</div>
									<div class="col-lg-1">
										<label for="quantidade">Quantidade</label>
										<input id='quantidade' quantMax='${valores[4]}' type="text" class="form-control" value="${quantidade}" onkeypress="return onlynumber(event)" style="text-align: center" autofocus>
									</div>
									<div class="col-lg-2">
										<label for="lote">Lote</label>
										<input id='lote' type="text" class="form-control" value="${lote}" style="text-align: center">
									</div>
									<div class="col-lg-4">
										<label for="numSerie">Nº Série/Chassi</label>
										<input id='numSerie' type="text" class="form-control" value="${numSerie}" style="text-align: center">
									</div>
									<div class="col-lg-2">
										<label for="fabricacao">Fabricação</label>
										<input id='fabricacao' type="date" class="form-control" value="${fabricacao}" style="text-align: center">
									</div>
									<div class="col-lg-2">
										<label for="validade">Validade</label>
										<input id='validade' type="date" class="form-control" value="${validade}" style="text-align: center">
									</div>
									<div class="d-none">
										<input id='idModal' type="hidden" value="${idTd}">
									</div>
								</div>`;
							}

							$('#thead-modal').html(HTML);

							// Esta função não permite que o valor digitado pelo usuário seja maior que o valor de saldo.
							function validaQuantInputModal(quantMax) {
								$('#quantidade').on('keyup', function() {
									if (parseInt($('#quantidade').val()) > parseInt(quantMax)) {
										$('#quantidade').val(quantMax)
									}
								})
							}

							validaQuantInputModal($('#saldo').val())

							$('#quantidade').focus()
						}
						$('#page-modal').fadeIn(200);
					})
				})

				$('#modal-close').on('click', function() {
					$('#page-modal').fadeOut(200);
					$('body').css('overflow', 'scroll');
				})
			}

			function mudarValores() {
				$('#salvar').on('click', () => {

					var id = $('#idModal').val()
					var saldo = $('#saldo').val()
					var lote = $('#lote').val()
					var quantidade = $('#quantidade').val()
					var numSerie = $('#numSerie').val()
					var fabricacao = $('#fabricacao').val()
					var validade = $('#validade').val()

					let inputHiddenProdutoServico = $(`#campo${id}`)
					let arrayValInput = inputHiddenProdutoServico.val().split('#')

					// adicionando  novos dados no array
					arrayValInput[3] = quantidade
					arrayValInput[4] = saldo
					arrayValInput[5] = lote
					arrayValInput[6] = validade
					arrayValInput[7] = numSerie
					arrayValInput[8] = fabricacao

					// ['S', '58', '100.00', '0', '0', '0', '0', 'Detalhamento do servico']

					var virgula = eval('/' + ',' + '/g') // buscando na string as ocorrências da ','
					var stringVallnput = arrayValInput.toString().replace(virgula, '#') // transformando novamente em string, e trocando as virgulas por #.
					
					inputHiddenProdutoServico.val(stringVallnput)// colocando a nova string com os valores no input do produto/servico.

					let quantInicial = inputHiddenProdutoServico.attr('quantInicial')
					let saldoInicial = inputHiddenProdutoServico.attr('saldoInicial')

					let novosValores = recalcValores(quantInicial, quantidade, saldoInicial, arrayValInput[2])

					var tds = $(`#row${id}`).find('td')

					$(tds[3]).html(novosValores.quantAtualizada)
					$(tds[4]).html(novosValores.novoSaldo)
					$(tds[6]).html("R$ " + novosValores.valorTotal)
					$(tds[6]).attr('valorTotalSomaGeral', novosValores.somaTotalValorGeral)
					$(tds[7]).html(validade?formatDate(validade, 'pt-BR'):'')

					$('#inputNumItens').val()
					stringVallnput = ''

					// O input itemEditadoquantidade recebe como valor a ultima quantidade editada, para garantir que pelo menos uma quantidade de produtos ou serviços foi editada 
					$('#itemEditadoquantidade').val(quantidade)
					$('#validadeNaoInformada').val(validade)

					$('#page-modal').fadeOut(200);
					$('body').css('overflow', 'scroll');

					recalcValorTotal()
					calcSaldoOrdemCompra()
				})
			}

			function recalcValores(quantInicial, novaQuantidade, saldoInicial, valorUni) {

				let valorTotal = 0
				let novoSaldo = 0
				let quantAtualizada = 0

				novoSaldo = saldoInicial - novaQuantidade;

				//quantAtualizada = parseInt(novaQuantidade) + parseInt(quantInicial)

				return {
					quantAtualizada: novaQuantidade,
					valorTotal: float2moeda(novaQuantidade * valorUni),
					somaTotalValorGeral: novaQuantidade * valorUni,
					novoSaldo: novoSaldo
				};
			}

			function recalcValorTotal() {
				let novoTotalGeral = 0
				let velhoTotalGeral = $('#total').attr('valorTotalGeral')
				$('.trGrid').each((i, elem) => {
					$(elem).children().each((i, elem) => {
						if ($(elem).hasClass('valorTotal')) {

							if ($(elem).attr('valorTotalSomaGeral')) {
								novoTotalGeral += parseFloat($(elem).attr('valorTotalSomaGeral'))

								//$('#total').attr('valorTotalGeral', `${novoTotalGeral}`)
							}
						}
					})
				})

				$('#total').html(`R$ ${float2moeda(novoTotalGeral)}`).attr('valor', novoTotalGeral)
			}

			function inputsModal() {
				$('#tbody-modal')
			}
		}); //document.ready
		
			Array.prototype.remove = function(start, end) {
				this.splice(start, end);
				return this;
			}

			Array.prototype.insert = function(pos, item) {
				this.splice(pos, 0, item);
				return this;
			}

			function selecionaTipo(tipo) {

				// $('#divConteudo').css({
				// 	"background-color": "#eeeded",
				// 	"box-shadow": "none"
				// });
				// $('#divConteudo').html('<div style="text-align:center;"><img src="global_assets/images/lamparinas/loader-transparente.gif" width="200" /></div>');
				
				// setTimeout(() => {
				// }, 3000);
				if (tipo == 'E') {
					location.href = 'movimentacaoNovoEntrada.php';
				} else if (tipo == 'S') {
					location.href = 'movimentacaoNovoSaida.php';
				} else {
					location.href = 'movimentacaoNovoTransferencia.php';
				}
			}

			function float2moeda(num) {

				x = 0;

				if (num < 0) {
					num = Math.abs(num);
					x = 1;
				}
				if (isNaN(num)) num = "0";
				cents = Math.floor((num * 100 + 0.5) % 100);

				num = Math.floor((num * 100 + 0.5) / 100).toString();

				if (cents < 10) cents = "0" + cents;
				for (var i = 0; i < Math.floor((num.length - (1 + i)) / 3); i++)
					num = num.substring(0, num.length - (4 * i + 3)) + '.' +
					num.substring(num.length - (4 * i + 3));
				ret = num + ',' + cents;
				if (x == 1) ret = ' - ' + ret;

				return ret;

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
				<div class="card">

					<div class="card-header header-elements-inline">
						<h5 class="text-uppercase font-weight-bold">Cadastrar Nova Movimentação</h5>
					</div>

					<div class="card-body">
						<div class="row">
							<div class="col-lg-4">
								<div class="form-group">
									<div class="form-check form-check-inline">
										<label class="form-check-label">
											<input type="radio" name="inputTipo" value="E" class="form-input-styled" checked data-fouc>
											Entrada
										</label>
									</div>
									<div class="form-check form-check-inline">
										<label class="form-check-label">
											<input type="radio" name="inputTipo" value="S" class="form-input-styled" onclick="selecionaTipo('S')" data-fouc>
											Saída
										</label>
									</div>
									<div class="form-check form-check-inline">
										<label class="form-check-label">
											<input type="radio" name="inputTipo" value="T" class="form-input-styled" onclick="selecionaTipo('T')" data-fouc>
											Transferência
										</label>
									</div>
								</div>
							</div>
						</div>
					</div>
					<!-- /card-body -->

				</div>
				<!-- /info blocks -->

				<!-- Info blocks -->
				<div class="card" id="divConteudo">

					<form name="formMovimentacao" id="formMovimentacao" method="post" class="form-validate-jquery" action="movimentacaoNovoEntrada.php">
						<div class="card-body">
							<input type="hidden" name="tipo" id="tipo" value="" />

							<div class="row">
								<div class="col-lg-12">
									<h5 class="mb-0 font-weight-semibold">Dados da Entrada</h5>
									<br>
									<div class="row">
										<div class="col-lg-2">
											<div class="form-group">
												<label for="inputData">Data<span style="color: red">*</span></label>
												<input type="text" id="inputData" name="inputData" class="form-control" value="<?php echo date('d/m/Y'); ?>" readOnly required>
											</div>
										</div>

										<div class="col-lg-4" id="DestinoLocal">
											<div class="form-group">
												<label for="cmbDestinoLocal">Destino<span style="color: red">*</span></label>
												<select id="cmbDestinoLocal" name="cmbDestinoLocal" class="form-control form-control-select2" required>
													<option value="">Selecione</option>
													<?php
													$sql = "SELECT LcEstId, LcEstNome
															FROM LocalEstoque
															JOIN Situacao on SituaId = LcEstStatus
															WHERE LcEstUnidade = " . $_SESSION['UnidadeId'] . " and SituaChave = 'ATIVO'
															ORDER BY LcEstNome ASC";
													$result = $conn->query($sql);
													$row = $result->fetchAll(PDO::FETCH_ASSOC);

													foreach ($row as $item) {
														print('<option value="' . $item['LcEstId'] . '">' . $item['LcEstNome'] . '</option>');
													}
													?>
												</select>
											</div>
										</div>
									</div>
								</div>
							</div>

							<div class="row">
								<div class="col-lg-12">
									<div class="form-group">
										<label for="txtareaObservacao">Observação</label>
										<textarea rows="5" cols="5" class="form-control" id="txtareaObservacao" name="txtareaObservacao" placeholder="Observação" maxlength="4000"></textarea>
									</div>
								</div>
							</div>
							<br>

							<div class="row" id="dadosNF">
								<div class="col-lg-12">
									<h5 class="mb-0 font-weight-semibold">Dados da Nota Fiscal</h5>
									<br>
									<div class="row">
										<div class="col-lg-6">
											<div class="form-group">
												<label for="cmbFornecedor">Fornecedor<span style="color: red">*</span></label>
												<select id="cmbFornecedor" name="cmbFornecedor" class="form-control form-control-select2" required>
													<option value="">Selecione</option>
													<?php
													$sql = "SELECT ForneId, ForneNome
															FROM Fornecedor
															JOIN Situacao on SituaId = ForneStatus
															WHERE ForneEmpresa = " . $_SESSION['EmpreId'] . " and SituaChave = 'ATIVO'
															ORDER BY ForneNome ASC";
													$result = $conn->query($sql);
													$rowFornecedor = $result->fetchAll(PDO::FETCH_ASSOC);

													foreach ($rowFornecedor as $item) {
														print('<option value="' . $item['ForneId'] . '">' . $item['ForneNome'] . '</option>');
													}

													?>
												</select>
											</div>
										</div>

										<input type="hidden" id="inputFornecedor" name="inputFornecedor" value="#">

										<div class="col-lg-3">
											<div class="form-group">
												<label for="cmbOrdemCompra">Nº Ordem Compra / Carta Contrato<span style="color: red">*</span></label>
												<select id="cmbOrdemCompra" name="cmbOrdemCompra" class="form-control form-control-select2" required>
													<option value="">Selecione</option>
												</select>
											</div>
										</div>

										<div class="col-lg-3">
											<div class="form-group">
												<label for="inputTotalOrdemCompraCartaContrato">Total (R$) Ordem de Compra/Carta Contrato</label>
												<input type="text" id="inputTotalOrdemCompraCartaContrato" name="inputTotalOrdemCompraCartaContrato" class="form-control" onKeyUp="moeda(this)" maxLength="16">
											</div>
										</div>
									</div> <!-- row -->

									<div class="row">
										<div class="col-lg-3">
											<div class="form-group">
												<label for="inputNotaFiscal">Nº Nota Fiscal<span style="color: red">*</span></label>
												<input type="text" id="inputNotaFiscal" name="inputNotaFiscal" class="form-control" placeholder="Nº NF" maxlength="50" required>
											</div>
										</div>

										<div class="col-lg-2">
											<div class="form-group">
												<label for="inputDataEmissao">Data Emissão</label>
												<input type="date" id="inputDataEmissao" name="inputDataEmissao" class="form-control" placeholder="Data NF">
											</div>
										</div>

										<div class="col-lg-3">
											<div class="form-group">
												<label for="inputNumSerie">Nº Série Nota Fiscal</label>
												<input type="text" id="inputNumSerie" name="inputNumSerie" class="form-control" maxLength="30" placeholder="Nº Série">
											</div>
										</div>

										<div class="col-lg-2">
											<div class="form-group">
												<label for="inputValorTotal">Total (R$) Nota Fiscal<span style="color: red">*</span></label>
												<input type="text" id="inputValorTotal" name="inputValorTotal" class="form-control" onKeyUp="moeda(this)" maxLength="16" required>
											</div>
										</div>

										<div class="col-lg-2">
											<div class="form-group">
												<label for="inputChaveAcesso">Chave de Acesso</label>
												<input type="text" id="inputChaveAcesso" name="inputChaveAcesso" class="form-control" placeholder="Chave de Acesso NF" maxlength="100">
											</div>
										</div>

									</div> <!-- row -->
								</div> <!-- col-lg-12 -->
							</div> <!-- row -->
							<br>

							<div id="inputProdutos">
								<input type="hidden" id="inputNumItens" name="inputNumItens" value="0">
								<input type="hidden" id="itemEditadoquantidade" name="itemEditadoquantidade" value="0">
								<input type="hidden" id="validadeNaoInformada" name="validadeNaoInformada" value="0">
								<input type="hidden" id="inputIdProdutos" name="inputIdProdutos" value="0">
								<input type="hidden" id="inputProdutosRemovidos" name="inputProdutosRemovidos" value="0">
								<input type="hidden" id="inputTotal" name="inputTotal" value="0">
							</div>

							<div class="row">
								<div class="col-lg-12">
									<table class="table" id="tabelaProdutoServico">
										<thead>
											<?php
											print('
													<tr class="bg-slate" id="trEntrada">
														<th>Item</th>
														<th>Produto/Serviço</th>
														<th style="text-align:center">Unidade Medida</th>
														<th style="text-align:center">Quantidade</th>
														<th style="text-align:center">Saldo Recebido</th>
														<th style="text-align:right">Valor Unitário</th>
														<th style="text-align:right">Valor Total</th>
														<th style="text-align:center">Validade</th>
														<th class="text-center">Ações</th>
													</tr>
												');
											?>
										</thead>
										<tbody>
										</tbody>
										<tfoot>
											<tr>
												<th id="totalTitulo" colspan="6" style="text-align:right; font-size: 16px; font-weight:bold;">Total (R$) Nota Fiscal: </th>
												<?php
												print('
															<th colspan="1">
																<div id="total" style="text-align:right; font-size: 15px; font-weight:bold;">R$ 0,00</div>
															</th>
													');
												print('<th colspan="3">

															</th>
													');
												?>
											</tr>
										</tfoot>
									</table>
								</div>
							</div>
							<br>
							<br>

							<div class="row">
								<div class="col-lg-12">
									<div class="row">
										<div class="col-lg-3">
											<div class="form-group">
												<?php

												$sql = "SELECT SituaId, SituaNome, SituaChave
															FROM Situacao
															WHERE SituaStatus = '1'
															ORDER BY SituaNome ASC";
												$result = $conn->query($sql);
												$row = $result->fetchAll(PDO::FETCH_ASSOC);

												if ($_SESSION['PerfiChave'] == 'CENTROADMINISTRATIVO' || $_SESSION['PerfiChave'] == 'ADMINISTRADOR') {

													print('<label for="inputSituacao">Situação</label>
													<select id="cmbSituacao" name="cmbSituacao" class="form-control form-control-select2" required value="0">');
													print('<option value="">Selecione</option>');

													foreach ($row as $item) {
														if ($item['SituaChave'] == 'AGUARDANDOLIBERACAOCENTRO' || $item['SituaChave'] == 'PENDENTE' || $item['SituaChave'] == 'LIBERADO') {
															if ($item['SituaChave'] == 'AGUARDANDOLIBERACAOCENTRO') {
																print('<option value="' . $item['SituaId'] . '" selected>' . $item['SituaNome'] . '</option>');
															} else {
																print('<option value="' . $item['SituaId'] . '">' . $item['SituaNome'] . '</option>');
															}
														}
													}
												} else {
													foreach ($row as $item) {
														if ($item['SituaChave'] == 'AGUARDANDOLIBERACAOCENTRO') {
															print('<input name="cmbSituacao" value="' . $item['SituaId'] . '" type="hidden" />');
														}
													}
												}
												?>
												</select>

											</div>
										</div>
									</div>
								</div>
							</div>

							<div class="row" style="margin-top: 10px;">
								<div class="col-lg-12">
									<div class="form-group">
										<button class="btn btn-lg btn-principal" id="enviar">Incluir</button>
										<a href="movimentacao.php" class="btn btn-basic" role="button">Cancelar</a>
									</div>
								</div>
							</div>
						</div>
						<!-- /card-body -->
					</form>


				</div>
				<!-- /info blocks -->

				<div id="page-modal" class="custon-modal">
					<div class="custon-modal-container" style="width:90%">
						<div class="card custon-modal-content" style="width:100%">
							<div class="custon-modal-title">
								<i class=""></i>
								<p class="h3">Itens Recebidos</p>
								<i class=""></i>
							</div>
							<div class="card-footer mt-2 d-flex flex-column" style="width:100%">
								<div class="" style="width:100%" id="thead-modal">
									
								</div>
								<div class="row" style="margin-top: 10px;">
									<div class="col-lg-12">
										<div class="form-group">
											<button class="btn btn-lg btn-principal" id="salvar">Salvar</button>
											<a id="modal-close" class="btn btn-basic" role="button">Cancelar</a>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

			</div>
			<!-- /content area -->

			<?php include_once("footer.php"); ?>

		</div>
		<!-- /main content -->

	</div>
	<!-- /page content -->

</body>

</html>