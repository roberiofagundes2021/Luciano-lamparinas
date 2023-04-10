<?php

include_once("sessao.php");
$_SESSION['PaginaAtual'] = 'Editar Serviço';
include('global_assets/php/conexao.php');

//Esse if foi criado para se caso o usuário der um REFRESH na página. Nesse caso não terá POST e campos não reconhecerão o $row da consulta acima (daí ele deve ser redirecionado) e se quiser continuar editando terá que clicar no ícone da Grid novamente
if (!isset($_POST['inputServicoId'])) {
	echo 1;
	//header("Location: servicoVenda.php");
} else {
	$iUnidade = $_SESSION['UnidadeId'];
	$sqlDadosFormulario = "SELECT SrVenId,SrVenCodigo,SrVenNome,SrVenDetalhamento,SrVenTipoServico,SrVenGrupo,SrVenSubGrupo,SrVenPlanoConta,s.SituaChave
	FROM ServicoVenda sv
	JOIN Situacao s ON s.SituaStatus = sv.SrVenStatus 
	WHERE SrVenId = " . $_POST['inputServicoId'] . "and SrVenUnidade = " . $iUnidade . ";";
	$resultDadosFormulario = $conn->query($sqlDadosFormulario);
	$resultDadosFormulario = $resultDadosFormulario->fetch(PDO::FETCH_ASSOC);

	$sqlTiposServico = "SELECT TpSerId,TpSerCodigo,TpSerNome
	FROM TipoServico ts
	JOIN Situacao s ON SituaId = ts.TpSerStatus
	WHERE s.SituaChave = 'ATIVO' AND ts.TpSerUnidade = " . $_SESSION['UnidadeId'] . " ORDER BY ts.TpSerNome asc;";
	$tiposServico = $conn->query($sqlTiposServico);
	$tiposServico = $tiposServico->fetchAll(PDO::FETCH_ASSOC);

	$sqlGrupos = "SELECT AtGruId,AtGruNome
	FROM AtendimentoGrupo a
	JOIN Situacao s ON SituaId = a.AtGruStatus
	WHERE s.SituaChave = 'ATIVO' AND a.AtGruUnidade = " . $_SESSION['UnidadeId'] . " ORDER BY a.AtGruNome asc;";
	$grupos = $conn->query($sqlGrupos);
	$grupos = $grupos->fetchAll(PDO::FETCH_ASSOC);

	$sqlSubgrupos = ("SELECT AtSubId, AtSubNome, AtSubGrupo
		FROM AtendimentoSubGrupo ag
		JOIN Situacao s ON SituaId = ag.AtSubStatus  
		WHERE s.SituaChave = 'ATIVO' AND ag.AtSubUnidade  = $iUnidade
		ORDER BY ag.AtSubNome  asc;"
	);
	$subGrupos = $conn->query($sqlSubgrupos);
	$subGrupos = $subGrupos->fetchAll(PDO::FETCH_ASSOC);
	$arraySubgrupos = [];
	foreach ($subGrupos as $item) {
		array_push($arraySubgrupos, [
			'AtSubId' => $item['AtSubId'],
			'AtSubNome' => $item['AtSubNome'],
			'AtGrupoId' => $item['AtSubGrupo']
		]);
	}

	$sqlModalidades = "SELECT AtModId,AtModNome
	FROM AtendimentoModalidade am 
	JOIN Situacao s on SituaId = am.AtModSituacao
	WHERE s.SituaChave = 'ATIVO' AND am.AtModUnidade = " . $_SESSION['UnidadeId'] . " ORDER BY am.AtModNome asc;";
	$modalidades = $conn->query($sqlModalidades);
	$modalidades = $modalidades->fetchAll(PDO::FETCH_ASSOC);

	$queryServicos = "SELECT SVXMoId,SVXMoModalidade,am.AtModNome ,SVXMoValorCusto,SVXMoOutrasDespesas,SVXMoCustoFinal,SVXMoMargemLucro,SVXMoValorVenda
	FROM ServicoVendaXModalidade svm
	JOIN AtendimentoModalidade am on svm.SVXMoModalidade = am.AtModId 
	JOIN Situacao s ON s.SituaId = svm.SVXMoStatus 
	WHERE s.SituaChave = 'ATIVO' and SVXMoServicoVenda = " . $_POST['inputServicoId'] . "
	AND SVXMoUnidade = " . $_SESSION['UnidadeId'] . ";";
	$Servicos = $conn->query($queryServicos);
	$Servicos = $Servicos->fetchAll(PDO::FETCH_ASSOC);

	$queryPlanoConta = "SELECT PlConId, PlConNome
	FROM PlanoConta
	JOIN Situacao on SituaId = PlConStatus
	WHERE PlConUnidade = " . $_SESSION['UnidadeId'] . " and SituaChave = 'ATIVO'
	ORDER BY PlConNome ASC";
	$rowPlanoConta = $conn->query($queryPlanoConta);
	$rowPlanoConta = $rowPlanoConta->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Serviço</title>

	<?php include_once("head.php"); ?>

	<!-- Validação -->
	<script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
	<script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
	<script src="global_assets/js/demo_pages/form_validation.js"></script>

	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
	<script src="global_assets/js/demo_pages/form_layouts.js"></script>
	<script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>
	<script src="global_assets/js/plugins/tables/datatables/datatables.min.js"></script>
	<script src="global_assets/js/plugins/tables/datatables/extensions/responsive.min.js"></script>

	<!-- Adicionando Javascript -->
	<script type="text/javascript">
		$(document).ready(function() {
			servicosFormatados = [];
			//controla se está editando ou criando uma linha nova na tabela de serviços
			linhaAtual = null;
			contadorLinhasCriadas = 0;

			let tipoServico = $("#inputServicoTipo").val();
			$(`#tipoServico option[value=${tipoServico}]`).prop('selected', 'selected').change();

			let grupo = $("#inputGrupo").val();
			$(`#grupo option[value=${grupo}]`).prop('selected', 'selected').change();

			let subgrupo = $("#inputSubgrupo").val();
			atualizaSubGrupos();
			$(`#subGrupo option[value=${subgrupo}]`).prop('selected', 'selected').change();

			let planoDeConta = $("#inputPlanoDeConta").val();
			$(`#cmbPlanoConta option[value=${planoDeConta}]`).prop('selected', 'selected').change();

			//Tabela customizada de preços dos serviços
			$('#precoServicosTable').DataTable({
				"order": [
					[0, "desc"]
				],
				autoWidth: false,
				responsive: true,
				columnDefs: [{
						orderable: true, //Modalidade do Serviço
						width: "70%",
						targets: [0]
					},
					{
						orderable: true, //Valor de venda
						width: "20%",
						targets: [1]
					},
					{
						orderable: true, //Situacao
						width: "5%",
						targets: [2]
					},
					{
						orderable: true, //Ações
						width: "5%",
						targets: [3]
					}
				],
				dom: '<"datatable-header"fl><"datatable-scroll-wrap"t><"datatable-footer"ip>',
				language: {
					search: '<span>Filtro:</span> _INPUT_',
					searchPlaceholder: 'filtra qualquer coluna...',
					lengthMenu: '<span>Mostrar:</span> _MENU_',
					paginate: {
						'first': 'Primeira',
						'last': 'Última',
						'next': $('html').attr('dir') == 'rtl' ? '&larr;' : '&rarr;',
						'previous': $('html').attr('dir') == 'rtl' ? '&rarr;' : '&larr;'
					}
				}
			});

			//|--Aqui é criado o DataTable caso seja a primeira vez q é executado e o clear é para evitar duplicação na tabela depois da primeira pesquisa
			let table = $('#precoServicosTable').DataTable().clear().draw()
			table = $('#precoServicosTable').DataTable()
			let rowNode

			var servicos = '<?php echo json_encode($Servicos); ?>';
			servicos = JSON.parse(servicos);

			servicos.forEach(item => {
				let situacaoHtml = `
				<span style='cursor:pointer' class='badge badge-flat border-secondary text-secondary'>
    				Ativo
				</span>
				`;
				let BotaoEditaHtml = `
				<div class='list-icons'><a style='color: black' href='#' class='list-icons-item'>
					<i onclick='editaLinha(event,${item.SVXMoId})' class='icon-pencil7' title='Editar modalidade'></i>
				</div>
				`
				linha = {}
				linha.data = [item.AtModNome, item.SVXMoValorVenda, situacaoHtml, BotaoEditaHtml]
				linha.identify = {
					"situação": "Ativo",
					"idBanco": item.SVXMoId,
					"idTabela": "x"
				}
				linha.rawData = item;
				servicosFormatados.push(linha)
			})

			servicosFormatados.forEach(item => {
				rowNode = table.row.add(item.data).draw();
				item.identify.idTabela = rowNode[0];
			})

		});

		function editaLinha(e, idBanco) {
			e.preventDefault();
			var scrollDiv = document.getElementById("rowTabela").offsetTop;
			window.scrollTo({
				top: scrollDiv,
				behavior: 'smooth'
			});
			let indexDaLinha = servicosFormatados.findIndex(item => item.identify.idBanco == idBanco);
			let dadosDaLinha = servicosFormatados[indexDaLinha].rawData;
			$('#inputValorCusto').val(float2moeda(dadosDaLinha.SVXMoValorCusto));
			$('#inputOutrasDespesas').val(float2moeda(dadosDaLinha.SVXMoOutrasDespesas));
			$('#inputCustoFinal').val(float2moeda(dadosDaLinha.SVXMoCustoFinal));
			$('#inputMargemLucro').val(float2moeda(dadosDaLinha.SVXMoMargemLucro));
			$('#inputValorVenda').val(float2moeda(dadosDaLinha.SVXMoValorVenda));
			$(`#modalidades option[value=${dadosDaLinha.SVXMoModalidade}]`).prop('selected', 'selected').change();
			linhaAtual = {
				"idBanco": idBanco,
				"idTabela": servicosFormatados[indexDaLinha].idTabela,
				"indexDaLinha": indexDaLinha
			};
		}

		function AtualizarOuIncluir(e) {
			e.preventDefault();
			let mensagemErro = '';
			let modalidades = $('#modalidades').val();
			let inputValorCusto = $('#inputValorCusto').val();
			let inputMargemLucro = $('#inputMargemLucro').val();
			let inputValorVenda = $('#inputValorVenda').val();

			switch (mensagemErro) {
				case modalidades:
					mensagemErro = 'informe a modalidade';
					$('#modalidades').focus();
					break;
				case inputValorCusto:
					mensagemErro = 'informe o valor do custo';
					$('#inputValorCusto').focus();
					break;
				case inputMargemLucro:
					mensagemErro = 'informe a margem de lucro ou o valor de venda';
					$('#inputMargemLucro').focus();
					break;
				case inputValorVenda:
					mensagemErro = 'informe a margem de lucro ou o valor de venda';
					$('#inputValorVenda').focus();
					break;
				default:
					mensagemErro = '';
					break;
			}

			if (mensagemErro) {
				alerta('Campo Obrigatório!', mensagemErro, 'error')
				return
			}
			let table = $('#precoServicosTable').DataTable();
			let idProvisorio = 990000 + contadorLinhasCriadas;
			let situacaoHtml = `
				<span style='cursor:pointer' class='badge badge-flat border-secondary text-secondary'>
    				Ativo
				</span>
				`;
			let BotaoEditaHtml = `
				<div class='list-icons'><a style='color: black' href='#' class='list-icons-item'>
					<i onclick='editaLinha(event,${linhaAtual==null?idProvisorio:linhaAtual.idBanco})' class='icon-pencil7' title='Editar modalidade'></i>
				</div>
				`
			linha = {}
			linha.data = [$('#modalidades option:selected').prop('label'), $('#inputValorVenda').val(), situacaoHtml, BotaoEditaHtml]
			linha.identify = {
				"situação": "Ativo",
				"idBanco": linhaAtual == null ? idProvisorio : linhaAtual.idBanco,
				"idTabela": "x"
			}
			linha.rawData = {
				SVXMoId: linhaAtual == null ? "X" : linhaAtual.idBanco.toString(),
				SVXMoModalidade: $('#modalidades option:selected').val(),
				AtModNome: $('#modalidades option:selected').prop('label'),
				SVXMoCustoFinal: $('#inputCustoFinal').val().replaceAll('.', '').replace(',', '.'),
				SVXMoMargemLucro: $('#inputMargemLucro').val().replaceAll('.', '').replace(',', '.'),
				SVXMoOutrasDespesas: $('#inputOutrasDespesas').val().replaceAll('.', '').replace(',', '.'),
				SVXMoValorCusto: $('#inputValorCusto').val().replaceAll('.', '').replace(',', '.'),
				SVXMoValorVenda: $('#inputValorVenda').val().replaceAll('.', '').replace(',', '.'),
			};
			if (linhaAtual == null) {
				rowNode = table.row.add(linha.data).draw();
				linha.identify.idTabela = rowNode[0];
				servicosFormatados.push(linha);
				contadorLinhasCriadas++;
			} else {
				table.row(linhaAtual.idTabela).data(linha.data).draw();
				servicosFormatados[linhaAtual.indexDaLinha] = linha;
			}
			linhaAtual = null;
			$('#modalidades').val("");
			$('#inputValorCusto').val("");
			$('#inputOutrasDespesas').val("");
			$('#inputCustoFinal').val("");
			$('#inputMargemLucro').val("");
			$('#inputValorVenda').val("");
		}

		function atualizaSubGrupos() {
			if ($('#grupo').val() == "") {
				$('#subGrupo').empty();
				let opt = '<option value="">Selecione primeiro um grupo</option>';
				$('#subGrupo').append(opt);
			} else {
				$('#subGrupo').empty();
				let grupo = $('#grupo').val();
				let possiveisSubgrupos = [];
				$("#possiveisSubgrupos option").each(function() {
					let val = $(this).val();
					possiveisSubgrupos.push(JSON.parse(val));
				});
				let subGrupos = possiveisSubgrupos.filter(subGrupo => subGrupo.AtGrupoId == grupo);
				subGrupos.forEach(item => {
					let opt = `<option value="${item.AtSubId}">${item.AtSubNome}</option>`;
					$('#subGrupo').append(opt);
				})
			}
		}

		function limpaEspacosEmBranco() {
			var inputNome = $('#inputNome').val();
			inputNome = inputNome.trim();
			if (inputNome.length == 0) {
				$('#inputNome').val('');
			}
		};

		function retornaPrecos() {

			var camposDigitados = [
				"inputValorCusto",
				"inputOutrasDespesas",
				"inputCustoFinal"
			]

			var camposCalculados = [
				"inputMargemLucro",
				"inputValorVenda"
			]

			var inputValorCusto
			inputOutrasDespesas
			inputCustoFinal
			inputMargemLucro
			inputValorVenda;

			camposDigitados.forEach(campo => {
				eval(`${campo} = $('#${campo}').val().replaceAll('.', '').replace(',', '.').trim();`);
				eval(`${campo} = (${campo}==null||${campo}=='')?0.00:parseFloat(${campo});`);
			});

			camposCalculados.forEach(campo => {
				eval(`${campo} = $('#${campo}').val().replaceAll('.', '').replace(',', '.').trim();`);
				eval(`${campo} = (${campo}==null||${campo}=='')?"":parseFloat(${campo});`);
			});

			return ({
				"inputValorCusto": inputValorCusto,
				"inputOutrasDespesas": inputOutrasDespesas,
				"inputCustoFinal": inputCustoFinal,
				"inputMargemLucro": inputMargemLucro,
				"inputValorVenda": inputValorVenda
			})
		}

		function AtualizaCustoFinal() {
			var {
				inputValorCusto,
				inputOutrasDespesas
			} = retornaPrecos();
			var custoFinalAtualizado;
			custoFinalAtualizado = inputValorCusto + inputOutrasDespesas;
			custoFinalAtualizado = float2moeda(custoFinalAtualizado).toString();
			$('#inputCustoFinal').val(custoFinalAtualizado);
			AtualizaValorDeVenda();
		}

		function AtualizaMargemDeLucro() {
			var {
				inputMargemLucro,
				inputValorVenda,
				inputCustoFinal
			} = retornaPrecos();
			var margemLucroAtualizado = inputMargemLucro;
			if (inputValorVenda != "") {
				var lucro = inputValorVenda - inputCustoFinal;
				margemLucroAtualizado = lucro / inputCustoFinal * 100;
				margemLucroAtualizado = margemLucroAtualizado.toFixed(2);

			}
			$('#inputMargemLucro').val(margemLucroAtualizado);
		}

		function AtualizaValorDeVenda() {
			var {
				inputMargemLucro,
				inputValorVenda,
				inputCustoFinal
			} = retornaPrecos();
			var valorVendaAtualizado = inputValorVenda;
			if (inputMargemLucro != "") {
				lucro = inputCustoFinal * (inputMargemLucro / 100);
				valorVendaAtualizado = inputCustoFinal + lucro;
				valorVendaAtualizado = float2moeda(valorVendaAtualizado).toString();
			}
			$('#inputValorVenda').val(valorVendaAtualizado);
		}

		function submeterFormulario() {
			let unidade = <?php echo $_SESSION['UnidadeId']; ?>;
			camposObrigatorios = [{
					"idCampo": "inputNome",
					"apelidoCampo": "Nome"
				}, {
					"idCampo": "tipoServico",
					"apelidoCampo": "Tipo de serviço"
				},
				{
					"idCampo": "grupo",
					"apelidoCampo": "Grupo"
				},
				{
					"idCampo": "subGrupo",
					"apelidoCampo": "Subgrupo"
				},
				{
					"idCampo": "cmbPlanoConta",
					"apelidoCampo": "Plano de conta"
				},
				{
					"idCampo": "inputCodigo",
					"apelidoCampo": "Código"
				}
			];
			dadosValidos = true;
			camposObrigatorios.forEach(campo => {
				if ($(`#${campo.idCampo}`).val() == "" ^ $(`#${campo.idCampo}`).val() == null) {
					if (dadosValidos) {
						$(`#${campo.idCampo}`).focus();
						alerta('Atenção', `Campo ${campo.apelidoCampo} é obrigatório!`, 'error');
						dadosValidos = false;
					}
				}
			})
			if (servicosFormatados.length <= 0) {
				if (dadosValidos) {
					alerta('Atenção', `Insira pelo menos uma modalidade`, 'error');
					$(`#modalidades`).focus();
					var scrollDiv = document.getElementById("rowTabela").offsetTop;
					window.scrollTo({
						top: scrollDiv,
						behavior: 'smooth'
					});
					dadosValidos = false;
				}
			}
			if (dadosValidos) {
				$.ajax({
					type: 'POST',
					url: 'servicoVendaSalva.php',
					dataType: 'json',
					data: {
						"SrVenId": $("#inputServicoId").val(),
						"SrVenCodigo": $("#inputCodigo").val(),
						"SrVenNome": $("#inputNome").val(),
						"SrVenTipoServico": $('#tipoServico option:selected').val(),
						"SrVenDetalhamento": $("#txtDetalhamento").val(),
						"SrVenGrupo": $('#grupo option:selected').val(),
						"SrVenSubGrupo": $('#subGrupo option:selected').val(),
						"SrVenPlanoConta": $('#cmbPlanoConta option:selected').val(),
						"SrVenUnidade": unidade.toString(),
						"modalidades": servicosFormatados
					},
					success: function(response) {
						alerta(response.titulo, response.mensagem, response.status);
						window.location.href = './servicoVenda.php';
					},
					error: function(response) {
						alerta(response.titulo, response.mensagem, response.status);
						window.location.href = './servicoVenda.php';
					}
				});
			}
		};
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

					<form id="formServico" name="formServico" class="form-validate-jquery">
						<div class="card-header header-elements-inline">
							<h5 class="text-uppercase font-weight-bold">Editar Serviço <?php echo $resultDadosFormulario['SrVenNome']; ?></h5>
						</div>

						<input type="hidden" id="inputServicoId" name="inputServicoId" value="<?php echo $resultDadosFormulario['SrVenId']; ?>">
						<input type="hidden" id="inputServicoStatus" name="inputServicoStatus" value="<?php echo $resultDadosFormulario['SituaChave']; ?>">
						<input type="hidden" id="inputServicoTipo" name="inputServicoTipo" value="<?php echo $resultDadosFormulario['SrVenTipoServico']; ?>">
						<input type="hidden" id="inputGrupo" name="inputGrupo" value="<?php echo $resultDadosFormulario['SrVenGrupo']; ?>">
						<input type="hidden" id="inputSubgrupo" name="inputSubgrupo" value="<?php echo $resultDadosFormulario['SrVenSubGrupo']; ?>">
						<select style="display:none" id="possiveisSubgrupos" name="Subgrupos">
							<?php foreach ($arraySubgrupos as $item) {
								print("<option>" . json_encode($item) . "</option>");
							} ?>"
						</select>
						<input type="hidden" id="inputPlanoDeConta" name="inputPlanoDeConta" value="<?php echo $resultDadosFormulario['SrVenPlanoConta']; ?>">

						<div class="card-body">

							<div class="media">

								<div class="media-body">

									<div class="row">
										<div class="col-lg-2">
											<div class="form-group">
												<label for="inputCodigo">Código</label>
												<input type="text" id="inputCodigo" name="inputCodigo" class="form-control" placeholder="Código" maxlength="13" value="<?php echo $resultDadosFormulario['SrVenCodigo']; ?>">
											</div>
										</div>

										<div class="col-lg-6">
											<div class="form-group">
												<label for="inputNome">Nome <span class="text-danger">*</span></label>
												<input type="text" id="inputNome" onblur='limpaEspacosEmBranco()' name="inputNome" class="form-control" placeholder="Nome" maxlength="254" value="<?php echo $resultDadosFormulario['SrVenNome']; ?>" required>
											</div>
										</div>

										<div class="col-lg-4">
											<div class="form-group">
												<label for="tipoServico">Tipos de Serviços <span class="text-danger">*</span></label>
												<select id="tipoServico" name="tipoServico" class="form-control form-control-select2" required>
													<option value="">Selecione</option>
													<?php
													foreach ($tiposServico as $item) {
														print('<option value="' . $item['TpSerId'] . '">' . $item['TpSerCodigo'] . " - " . $item['TpSerNome'] . '</option>');
													}
													?>
												</select>
											</div>
										</div>
									</div>

									<div class="row">
										<div class="col-lg-12">
											<div class="form-group">
												<label for="txtDetalhamento">Detalhamento</label>
												<textarea rows="5" cols="5" class="form-control" id="txtDetalhamento" name="txtDetalhamento" placeholder="Detalhamento do serviço" maxlength="1999"><?php echo $resultDadosFormulario['SrVenDetalhamento']; ?></textarea>
											</div>
										</div>
									</div>

									<div class="row">
										<div class="col-lg-4">
											<div class="form-group">
												<label for="grupo">Grupo<span class="text-danger">*</span></label>
												<select onchange='atualizaSubGrupos()' id="grupo" name="grupo" class="form-control form-control-select2" required>
													<option value="">Selecione</option>
													<?php
													foreach ($grupos as $item) {
														print('<option value="' . $item['AtGruId'] . '">' . $item['AtGruNome'] . '</option>');
													}
													?>
												</select>
											</div>
										</div>

										<div class="col-lg-4">
											<div class="form-group">
												<label for="subGrupo">Subgrupo<span class="text-danger">*</span></label>
												<select id="subGrupo" name="subGrupo" class="form-control form-control-select2" required>
													<option value="">Selecione primeiro um grupo</option>
												</select>
											</div>
										</div>
										<div class="col-lg-4">
											<div class="form-group">
												<label for="cmbPlanoConta">Plano de Conta <span class="text-danger">*</span></label>
												<select id="cmbPlanoConta" name="cmbPlanoConta" class="form-control form-control-select2" required>
													<option value="">Selecione</option>
													<?php
													foreach ($rowPlanoConta as $item) {
														$seleciona = $item['PlConId'] == $row['SrVenPlanoConta'] ? "selected" : "";
														print('<option value="' . $item['PlConId'] . '" ' . $seleciona . '>' . $item['PlConNome'] . '</option>');
													}
													?>
												</select>
											</div>
										</div>
									</div>

									<div class="row">
										<div class="col-lg-12">
											<h5 class="mb-0 font-weight-semibold">Preço do Serviço</h5>
											<br>
										</div>
									</div>

									<div class="row" id="rowTabela">
										<div class="col-lg-2">
											<div class="form-group">
												<label for="modalidades">Modalidades <span class="text-danger">*</span></label>
												<select id="modalidades" name="modalidades" class="form-control form-control-select2">
													<option value="">Selecione</option>
													<?php
													foreach ($modalidades as $item) {
														print('<option value="' . $item['AtModId'] . '">' . $item['AtModNome'] . '</option>');
													}
													?>
												</select>
											</div>
										</div>

										<div class="col-lg-2">
											<div class="form-group">
												<label for="inputValorCusto">Valor de Custo</label>
												<input type="text" id="inputValorCusto" onchange='AtualizaCustoFinal()' name="inputValorCusto" class="form-control" placeholder="Valor de Custo" value="" onKeyUp="moeda(this)" maxLength="12">
											</div>
										</div>

										<div class="col-lg-2">
											<div class="form-group">
												<label for="inputOutrasDespesas">Outras Despesas</label>
												<input type="text" id="inputOutrasDespesas" onchange='AtualizaCustoFinal()' name="inputOutrasDespesas" class="form-control" placeholder="Outras Despesas" value="" onKeyUp="moeda(this)" maxLength="12">
											</div>
										</div>

										<div class="col-lg-2">
											<div class="form-group">
												<label for="inputCustoFinal">Custo Final</label>
												<input type="text" id="inputCustoFinal" name="inputCustoFinal" class="form-control" placeholder="Custo Final" value="" readOnly>
											</div>
										</div>

										<div class="col-lg-2">
											<div class="form-group">
												<label for="inputMargemLucro">Margem de Lucro (%)</label>
												<input type="text" id="inputMargemLucro" onchange='AtualizaValorDeVenda()' name="inputMargemLucro" class="form-control" placeholder="Margem Lucro" value="" onKeyUp="moeda(this)" maxLength="6">
											</div>
										</div>

										<div class="col-lg-2">
											<div class="form-group">
												<label for="inputValorVenda">Valor de Venda</label>
												<input type="text" id="inputValorVenda" onchange='AtualizaMargemDeLucro()' name="inputValorVenda" class="form-control" placeholder="Valor de Venda" value="" onKeyUp="moeda(this)" maxLength="12">
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-lg-12">
											<button id="incluir" onclick="AtualizarOuIncluir(event)" class="btn btn-lg btn-principal">Incluir/Atualizar</button>
											<br>
											<br>
										</div>
									</div>

									<div class="row">
										<div class="col-lg-12">
											<table class="table" id="precoServicosTable">
												<thead>
													<tr class="bg-slate text-left">
														<th>Modalidade do Serviço</th>
														<th>Valor de venda</th>
														<th>Situação</th>
														<th>Ações</th>
													</tr>
												</thead>
												<tbody id="precoServicosBody">
												</tbody>
											</table>
										</div>
									</div>

									<div class="row">
										<div class="col-lg-12">
											<div class="form-group">
												<?php
												if ($_POST['inputPermission']) {
													echo '<button type="button" id="alterar" onclick="submeterFormulario()" class="btn btn-lg ">Alterar</button>';
												}
												?>
												<a href="servicoVenda.php" class="btn btn-basic" role="button">Cancelar</a>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>

			<?php include_once("footer.php"); ?>

		</div>
	</div>
</body>

</html>