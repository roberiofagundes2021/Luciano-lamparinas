<?php

include_once("sessao.php");

$_SESSION['PaginaAtual'] = 'Nova Movimentação';

include('global_assets/php/conexao.php');

if (isset($_POST['inputData'])) {

	try {

		if ($_POST['cmbMotivo'] !== '') {
			$aMotivo = explode("#", $_POST['cmbMotivo']);
			$iMotivo = $aMotivo[0];
		} else {
			$iMotivo = null;
		}

		$origemArray = null;
		$idOrigem = null;
		$tipoOrigem = null;

		if ($_POST['cmbEstoqueOrigemLocalSetor'] !== '') {
			$origemArray = explode('#', $_POST['cmbEstoqueOrigemLocalSetor']);

			if (count($origemArray) > 2) {
				$idOrigem = $origemArray[0];
				$tipoOrigem = $origemArray[2];
			}
		}

		$destinoArray = null;
		$idDestino = null;
		$tipoDestino = null;

		if ($_POST['cmbDestinoLocalEstoqueSetor'] != '#') {
			$destinoArray = explode('#', $_POST['cmbDestinoLocalEstoqueSetor']);

			if (count($destinoArray) > 2) {
				$idDestino = $destinoArray[0];
				$tipoDestino = $destinoArray[2];
			}
		}

    $conn->beginTransaction();

    $newMovi = '1/'.(date("Y"));

		// vai retornar um valor contendo somente a segunda parte da string ex: "1/2021" => "2021"
		$sqlMovi = "SELECT MAX(SUBSTRING(MovimNumRecibo, 3, 6)) as MovimNumRecibo
        FROM Movimentacao
        WHERE MovimUnidade = '$_SESSION[UnidadeId]'";
		$resultMovi = $conn->query($sqlMovi);
		$rowMovi = $resultMovi->fetch(PDO::FETCH_ASSOC);

		// Se ultimo valor cadastrado no banco for de um ano diferente do ano atual,
		// a contagem será reiniciada
		if ($rowMovi['MovimNumRecibo'] == date("Y")) {
			// vai buscar o ultimo valor completo no banco
			$sqlMovi = "SELECT MAX(MovimNumRecibo) as MovimNumRecibo
					FROM Movimentacao
					WHERE MovimUnidade = '$_SESSION[UnidadeId]' and MovimNumRecibo LIKE '%".date("Y")."%'";
			$resultMovi = $conn->query($sqlMovi);
			$rowMovi = $resultMovi->fetch(PDO::FETCH_ASSOC);
	
			$newMovi = explode('/', $rowMovi['MovimNumRecibo']);
			$newMovi = (intval($newMovi[0])+1).'/'.(date("Y"));
		}

		$sql = "INSERT INTO Movimentacao (MovimTipo,
                                      MovimNumRecibo,
																			MovimData, 
																			MovimFinalidade,
																			MovimOrigemLocal,
																			MovimOrigemSetor,
																			MovimDestinoLocal,
																			MovimDestinoSetor,
																			MovimDestinoManual,
																			MovimObservacao,
																			MovimOrdemCompra,
																			MovimNotaFiscal,
																			MovimDataEmissao,
																			MovimNumSerie,
																			MovimValorTotal,
																			MovimChaveAcesso,
																			MovimFornecedor,
																			MovimMotivo,
																			MovimSituacao, 
																			MovimUnidade, 
																			MovimUsuarioAtualizador)
								VALUES (:sTipo, 
												:dMovi,
												:dData,
												:iFinalidade,
												:iOrigemLocal, 
												:iOrigemSetor, 
												:iDestinoLocal, 
												:iDestinoSetor, 
												:sDestinoManual, 
												:sObservacao,
												:iOrdemCompra, 
												:sNotaFiscal, 
												:dDataEmissao, 
												:sNumSerie, 
												:fValorTotal, 
												:sChaveAcesso,
												:iFornecedor,
												:iMotivo,
												:iSituacao, 
												:iUnidade,
												:iUsuarioAtualizador)";
		$result = $conn->prepare($sql);

		$result->execute(array(
			':sTipo' => 'T',  // Transferência
			':dMovi' => $newMovi,  // Número incremental
			':dData' => gravaData($_POST['inputData']),
			':iFinalidade' => null,
			':iOrigemLocal' => $tipoOrigem == 'Local' ? $idOrigem : $tipoOrigem == 'OrigemLocalTransferencia' ? $idOrigem : null,
			':iOrigemSetor' => $tipoOrigem == 'Setor' ? $idOrigem : null,
			':iDestinoLocal' => $tipoDestino == 'Local' ? $idDestino : $tipoDestino == 'DestinoLocal' ? $idDestino : null,
			':iDestinoSetor' => $tipoDestino == 'Setor' ? $idDestino : $tipoDestino == 'DestinoSetor' ? $idDestino : null,
			':sDestinoManual' => $_POST['inputDestinoManual'] == '' ? null : $_POST['inputDestinoManual'],
			':sObservacao' => $_POST['txtareaObservacao'],
			':iOrdemCompra' => null,
			':sNotaFiscal' => null,
			':dDataEmissao' => null,
			':sNumSerie' => null,
			':fValorTotal' => $_POST['inputTotal'] == '' ? null : gravaValor($_POST['inputTotal']),
			':sChaveAcesso' => null,
			':iFornecedor' => null,
			':iMotivo' => intval($iMotivo),
			':iSituacao' => $_POST['cmbSituacao'] == '' ? null : $_POST['cmbSituacao'],
			':iUnidade' => $_SESSION['UnidadeId'],
			':iUsuarioAtualizador' => $_SESSION['UsuarId']
		));



		$insertId = $conn->lastInsertId();

		try {

			$numItems = intval($_POST['inputNumItens']);

			for ($i = 1; $i <=  $numItems; $i++) {

				$campoSoma = $i;
				$campo = 'campo' . $i;

				//Aqui tenho que fazer esse IF, por causa das exclusões da Grid
				if (isset($_POST[$campo])) {
					
					$registro = explode('#', $_POST[$campo]);
					$quantItens = intval($registro[3]);

					if ((int) $registro[3] > 0) { //Quantidade > 0
						$sql = "INSERT INTO MovimentacaoXProduto 
															 (MvXPrMovimentacao,
																MvXPrProduto,
																MvXPrQuantidade,
																MvXPrValorUnitario, /*NOT NULL*/
																MvXPrLote, /*NOT NULL*/
																MvXPrValidade, /*NOT NULL*/
																MvXPrClassificacao, /*NOT NULL*/
																MvXPrUsuarioAtualizador,
																MvXPrPatrimonio, /*NOT NULL*/
																MvXPrUnidade)
											VALUES  (:iMovimentacao,
															 :iProduto,
															 :iQuantidade,
															 :dValorUnitario, /*NOT NULL*/
															 :vLote, /*NOT NULL*/
															 :dValidade, /*NOT NULL*/
															 :iClassificacao, /*NOT NULL*/
															 :iUsuarioAtualizador,
															 :iPatrimonio, /*NOT NULL*/
															 :iUnidade)";

						$result = $conn->prepare($sql);

						// var_dump($sql);
						// var_dump($registro);
						// die;

						$result->execute(array(
							':iMovimentacao' => intval($insertId),
							':iProduto' => intval($registro[1]),
							':iQuantidade' => (int) $registro[3],
							':dValorUnitario' => isset($registro[2]) ? (float) $registro[2] : null,
							':vLote' => $registro[5],
							':dValidade' => $registro[6] == 'null' ? null : $registro[6],
							':iClassificacao' => null,
							':iUsuarioAtualizador' => intval($_SESSION['UsuarId']),
							':iPatrimonio' => $_POST['cmbPatrimonio'] = '' ? null : intval($_POST['cmbPatrimonio']),
							':iUnidade' => intval($_SESSION['UnidadeId'])
						));
					}
				}
			}
		} catch (PDOException $e) {
			$conn->rollback();
			echo 'Error1: ' . $e->getMessage();
			exit;
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

    /* Início: Tabela Personalizada */
    $('#tblTransferencia1').DataTable({
      "order": [
        [0, "asc"]
      ],
      autoWidth: false,
      responsive: true,
      columnDefs: [{
          orderable: true, //Item
          width: "5%",
          targets: [0]
        },
        {
          orderable: true, //Produto
          width: "30%",
          targets: [1]
        },
        {
          orderable: true, //Nª do patrimonio
          width: "10%",
          targets: [2]
        },
        {
          orderable: true, //Unidade Medida
          width: "10%",
          targets: [3]
        },
        {
          orderable: true, //Quantidade
          width: "10%",
          targets: [4]
        },
        {
          orderable: true, //Valor Unitário
          width: "10%",
          targets: [5]
        },
        {
          orderable: true, //Valor Total
          width: "10%",
          targets: [6]
        },
        {
          orderable: false, //Validade
          whidth: "10",
          targets: [7]
        },
        {
          orderable: false, //Ações
          width: "5%",
          targets: [8]
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


    $('#cmbEstoqueOrigemLocalSetor').on('change', function(e) {
      limpaSubCategProd();
      FiltraCategoriaOrigem('#Categoria');
      filtraPatrimonioProdutoOrigem();
    });


    $('#cmbPatrimonio').on('change', function(e) {
      FiltraCategoriaPatrimonio({
        tipo: '#CategoriaPatrimonio',
        valor: e.target.value
      });
      // $('#cmbProduto').change();
    });

    $('#cmbSubCategoria').on('change', function(e) {
      const idCategoria = $('#cmbCategoria').val()
      const idSubCategoria = $('#cmbSubCategoria').val()
      const tipoDeFiltro = 'produto';

      FiltraCategoriaProduto({
        tipoDeFiltro: 'produto',
        idCategoria: idCategoria,
        idSubCategoria: idSubCategoria
      });
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


    function limpaSubCategProd() {
      $('#inputQuantidade').val("");
      $('#inputValorUnitario').val("");
      $('#inputLote').val("");

      const optionSubCategoria = '<option value="" "selected">Selecione a Subcategoria</option>';
      const optionProduto = '<option value="" "selected">Selecione o Produto</option>';

      $('#cmbSubCategoria').html('');
      $('#cmbSubCategoria').append(optionSubCategoria)

      $('#cmbProduto').html('');
      $('#cmbProduto').append(optionProduto)
    }


    function filtraPatrimonioProdutoOrigem() {
      const cmbOrigem = $('#cmbEstoqueOrigemLocalSetor').val().split('#')
      const selectPatrimonio = document.querySelector('#cmbPatrimonio');
      let tipoDeFiltro = 'Patrimonio'

      $('#cmbPatrimonio').html('<option value="" "selected">Filtrando...</option>');
      try {
        $.ajax({
          type: "POST",
          url: "filtraPorOrigemTransferencia.php",
          data: {
            origem: cmbOrigem[0],
            tipoDeFiltro: tipoDeFiltro
          },
          success: function(resposta) {
            var option = '<option value="" "selected">Selecione o Patrimônio</option>';
            if (resposta !== '') {
              $('#cmbPatrimonio').html('');
              $('#cmbPatrimonio').append(option)
              $('#cmbPatrimonio').append(resposta)
            } else {
              $('#cmbPatrimonio').html('<option value="" "selected">Sem Patrimônios</option>');
            }
          }
        })
      } catch (err) {
        console.log('Erro ao filtrar patrimonios: ' + err);
      }
    }


    function FiltraCategoriaOrigem(props) {
      const cmbOrigemLocalSetor = $('#cmbEstoqueOrigemLocalSetor').val()
      let tipoDeFiltro = props;

      FiltraCategoria();

      $('#cmbCategoria').html('<option value="" "selected">Filtrando...</option>');

      try {
        $.ajax({
          type: "POST",
          url: "filtraPorOrigemTransferencia.php",
          data: {
            origem: cmbOrigemLocalSetor,
            tipoDeFiltro: tipoDeFiltro
          },
          success: function(resposta) {
            var option = '<option value="" "selected">Selecione a Categoria</option>';
            if (resposta !== 'sem dados') {
              $('#cmbCategoria').html('');
              $('#cmbCategoria').append(option)
              $('#cmbCategoria').append(resposta)

            } else {
              $('#cmbCategoria').html('<option value="" "selected">Sem categorias</option>');
            }
          }
        })
      } catch (err) {
        console.log('Erro ao carregar dados da Categoria: ' + err);
      }
    }



    function FiltraCategoriaPatrimonio(props) {
      const cmbOrigemLocalSetor = $('#cmbEstoqueOrigemLocalSetor').val()

      FiltraCategoria();

      $('#cmbCategoria').html('<option value="" "selected">Filtrando...</option>');

      if (props.valor === "#") {
        $('#cmbCategoria').html('<option value="" "selected">Selecione a Categoria</option>');
        $('#cmbSubCategoria').html('<option value="" "selected">Selecione a Subcategoria</option>');
        $('#cmbProduto').html('<option value="" "selected">Selecione o Produto</option>');
        $('#cmbCategoria').prop('disabled', '');
        $('#cmbSubCategoria').prop('disabled', '');
        $('#cmbProduto').prop('disabled', '');
        // $('#formMovimentacao').submit();
        var inputTipo = $('input[name="inputTipo"]:checked').val();
        var cmbProduto = $('#cmbProduto').val();
        var inputValorUnitario = $('#inputValorUnitario').val();
        $('#inputValorUnitario').val('');
        $('#inputLote').val('');
        $('#inputQuantidade').val('');

      } else {
        try {
          $.ajax({
            type: "POST",
            url: "filtraPorOrigemTransferencia.php",
            data: {
              origem: cmbOrigemLocalSetor,
              tipoDeFiltro: props.tipo,
              valor: props.valor,
              campo: 'categoria'
            },
            success: function(respostaCategoria) {
              var option = '<option value="" >Selecione a Categoria</option>';
              if (respostaCategoria !== 'sem dados') {
                $('#cmbCategoria').html('');
                $('#cmbCategoria').append(option)
                $('#cmbCategoria').append(respostaCategoria);
                // $('#cmbCategoria').prop('disabled', 'disabled');

              } else {
                $('#cmbCategoria').html('<option value="" "selected">Sem Categorias</option>');
                // $('#cmbCategoria').prop('disabled', 'disabled');
              }
            }
          })
        } catch (err) {
          console.log('Erro ao carregar dados da Categoria: ' + err);
        }

        try {
          $.ajax({
            type: "POST",
            url: "filtraPorOrigemTransferencia.php",
            data: {
              origem: cmbOrigemLocalSetor,
              tipoDeFiltro: props.tipo,
              valor: props.valor,
              campo: 'subcategoria'
            },
            success: function(respostaSubCategoria) {
              var option = '<option value="" >Selecione a SubCategoria</option>';
              if (respostaSubCategoria !== 'sem dados') {
                $('#cmbSubCategoria').html('');
                $('#cmbSubCategoria').append(option)
                $('#cmbSubCategoria').append(respostaSubCategoria);
                // $('#cmbSubCategoria').prop('disabled', 'disabled');
              } else {
                $('#cmbSubCategoria').html('<option value="" "selected">Sem Subcategorias</option>');
                // $('#cmbSubCategoria').prop('disabled', 'disabled');
              }
            }
          })
        } catch (err) {
          console.log('Erro ao carregar dados da Categoria: ' + err);
        }

        try {
          $.ajax({
            type: "POST",
            url: "filtraPorOrigemTransferencia.php",
            data: {
              origem: cmbOrigemLocalSetor,
              tipoDeFiltro: props.tipo,
              valor: props.valor,
              campo: 'produto'
            },
            success: function(respostaProduto) {
              var option = '<option value="" >Selecione o produto</option>';
              if (respostaProduto !== 'sem dados') {
                $('#cmbProduto').html('');
                $('#cmbProduto').append(option)
                $('#cmbProduto').append(respostaProduto);
                // $('#cmbProduto').prop('disabled', 'disabled');

                var inputTipo = $('input[name="inputTipo"]:checked').val();
                var cmbProduto = $('#cmbProduto').val();
                var inputValorUnitario = $('#inputValorUnitario').val();

                var Produto = cmbProduto.split("#");
                var valor = Produto[1].replace(".", ",");

                if (valor != 'null' && valor) {
                  $('#inputValorUnitario').val(valor);
                } else {
                  $('#inputValorUnitario').val('0,00');
                }
                $('#inputQuantidade').focus();
              } else {
                $('#cmbProduto').html('<option value="" "selected">Sem Produtos</option>');
                // $('#cmbProduto').prop('disabled', 'disabled');
              }
            }
          })
        } catch (err) {
          console.log('Erro ao carregar dados da Categoria: ' + err);
        }
      }
    }



    //Ao mudar a categoria, filtra a subcategoria e produto via ajax (retorno via JSON)
    $('#cmbCategoria').on('change', function(e) {
      const idCategoria = $('#cmbCategoria').val()
      const idSubCategoria = $('#cmbSubCategoria').val()
      const tipoDeFiltro = 'produto';

      Filtrando();

      const inputTipo = document.querySelector('input[name="inputTipo"]:checked').value;
      const cmbCategoria = document.querySelector('#cmbCategoria').value;
      const cmbSubCategoria = document.querySelector('#cmbSubCategoria').value;


      $.getJSON('filtraSubCategoria.php?idCategoria=' + cmbCategoria, function(dados) {

        let option = '<option value="">Selecione a SubCategoria</option>';

        if (dados.length) {

          $.each(dados, function(i, obj) {
            option += '<option value="' + obj.SbCatId + '">' + obj.SbCatNome + '</option>';
          });

          $('#cmbSubCategoria').html(option).show();

        } else {
          ResetSubCategoria();
        }
      }).fail(function(m) {
        console.log(m);
      });

      FiltraCategoriaProduto({
        tipoDeFiltro: 'produto',
        idCategoria: idCategoria,
        idSubCategoria: idSubCategoria
      });
    });



    //Impede que o input quantidade receba letras
    $('#inputQuantidade').on('keydown', () => {
      let valor = $('#inputQuantidade').val()

      if (valor == '´' || valor == '~' || valor == '`' || valor == ';') {
        $('#inputQuantidade').val('')
      }
      if (event.keyCode != '8' && event.keyCode != '48' && event.keyCode != '49' && event.keyCode != '50' && event.keyCode != '51' && event.keyCode != '52' && event.keyCode != '53' && event.keyCode != '54' && event.keyCode != '55' && event.keyCode != '56' && event.keyCode != '57' && event.keyCode != '96' && event.keyCode != '97' && event.keyCode != '98' && event.keyCode != '99' && event.keyCode != '100' && event.keyCode != '101' && event.keyCode != '102' && event.keyCode != '103' && event.keyCode != '104' && event.keyCode != '105' && event.keyCode != '106' && event.keyCode != '107') {
        return false
      }

      if (event.keyCode == '222' && event.keyCode != '219' && event.keyCode != '191') {
        return false
      }
    })




    //Ao mudar a SubCategoria, filtra o produto via ajax (retorno via JSON)
    function FiltraCategoriaProduto(props) {

      $('#cmbProduto').html('<option value="" "selected">Filtrando...</option>');

      try {
        $.ajax({
          type: "POST",
          url: "filtraProdutoTransferencia.php",
          data: {
            tipoDeFiltro: props.tipoDeFiltro,
            idCategoria: props.idCategoria,
            idSubCategoria: props.idSubCategoria
          },
          success: function(respostaProduto) {
            var option = '<option value="" selected>Selecione o produto</option>';
            if (respostaProduto !== 'sem dados') {
              $('#cmbProduto').html('');
              $('#cmbProduto').append(option)
              $('#cmbProduto').append(respostaProduto);
            } else {
              $('#cmbProduto').html('<option value="" "selected">Sem Produtos</option>');
            }
          }
        })
      } catch (err) {
        console.log('Erro ao carregar dados do Produto: ' + err);
      }
    };


    //Ao mudar o Produto, trazer o Valor Unitário do cadastro (retorno via JSON)
    $('#cmbProduto').on('change', function(e) {

      let inputTipo = $('input[name="inputTipo"]:checked').val();
      let cmbProduto = $('#cmbProduto').val();
      let inputValorUnitario = $('#inputValorUnitario').val();

      if (cmbProduto !== null && cmbProduto !== "") {
        let Produto = cmbProduto.split("#");
        let valor = Produto[1].replace(".", ",");

        if (valor != 'null' && valor) {
          $('#inputValorUnitario').val(valor);
        } else {
          $('#inputValorUnitario').val('0,00');
        }
        $('#inputQuantidade').focus();
      }
    });



    $("input[type=radio][name=inputTipo]").click(function() {
      console.log('INPUT TIPO E PRODUTO NA LISTA, LINHA 806')

      var inputTipo = $('input[name="inputTipo"]:checked').val();
      var inputNumItens = $('#inputNumItens').val();

      if (inputNumItens > 0) {
        alerta('Atenção', 'O tipo não pode ser alterado quando se tem produto(s) na lista! Exclua-o(s) primeiro ou cancele e recomece o cadastro da movimentação.', 'error');
        return false;
      }

      // $('#cmbCategoria').val("");
      // $('#inputValorUnitario').val("");
      // $('#inputLote').val("");
      // $('#inputValidade').val("");
      $('#inputQuantidade').val("");
    });



    $('#btnAdicionar').click(function() {
      let cmbProduto = $('#cmbProduto').val();
      let cmbPatrimonio = $('#cmbPatrimonio').val();
      let cmbCategoria = $('#cmbCategoria').val();
      let cmbClassificacao = $('#cmbClassificacao').val();
      let cmbOrigem = $('#cmbEstoqueOrigemLocalSetor').val();
      let cmbDestino = $('#cmbDestinoLocalEstoqueSetor').val()? $('#cmbDestinoLocalEstoqueSetor').val():$('#inputDestinoManual').val();
      let Produto = cmbProduto.split("#");
      let inputTipo = $('input[name="inputTipo"]:checked').val();
      let inputNumItens = $('#inputNumItens').val();
      let inputQuantidade = $('#inputQuantidade').val();
      let inputValorUnitario = $('#inputValorUnitario').val();
      let inputLote = $('#inputLote').val();
      let inputValidade = $('#inputValidade').val();
      let inputIdProdutos = $('#inputIdProdutos').val(); //esse aqui guarda todos os IDs de produtos que estão na grid para serem movimentados
      let resNumItens = parseInt(inputNumItens) + 1;
      let inputTotal = $('#inputTotal').val();

      let total = parseInt(inputQuantidade) * parseFloat(inputValorUnitario.replace(',', '.'));

      total = total + parseFloat(inputTotal);
      let totalFormatado = "R$ " + float2moeda(total).toString();

      //Esse ajax está sendo usado para verificar no banco se o registro já existe
      let origem = $('#cmbEstoqueOrigemLocalSetor').val();
      origem = origem.split('#');
      origem[0] = parseInt(origem[0]);

      //remove os espaços desnecessários antes e depois
      inputQuantidade = inputQuantidade.trim();

      //Verifica se o campo só possui espaços em branco
      if (cmbOrigem == '' || cmbOrigem == null) {
        alerta('Atenção', 'Informe a origem antes de adicionar!', 'error');
        $('html, body').animate({
          scrollTop: $("#cmbEstoqueOrigemLocalSetor")[0].scrollHeight
        }, 1500);
        $('#cmbEstoqueOrigemLocalSetor').focus();
        return false;
      } else if (cmbDestino == '' || cmbDestino == null) {
        alerta('Atenção', 'Informe o destino antes de adicionar!', 'error');
        $('html, body').animate({
          scrollTop: $("#cmbDestinoLocalEstoqueSetor").scrollHeight
        }, 1500);
        $('#cmbDestinoLocalEstoqueSetor').focus();
        return false;
      } else if ((cmbCategoria == '' || cmbCategoria == null) && (cmbPatrimonio == '' || cmbPatrimonio == null)) {
        alerta('Atenção', 'Informe a categoria ou o patrimonio antes de adicionar!', 'error');
        $('html, body').animate({
          scrollTop: $("#cmbCategoria").scrollHeight
        }, 1500);
        $('#cmbCategoria').focus();
        return false;
      } else if (cmbProduto == '') {
        alerta('Atenção', 'Informe o produto antes de adicionar!', 'error');
        $('html, body').animate({
          scrollTop: $("#cmbProduto").scrollHeight
        }, 1500);
        $('#cmbProduto').focus();
        return false;
      } else if (inputQuantidade == '') {
        alerta('Atenção', 'Informe a quantidade antes de adicionar!', 'error');
        $('html, body').animate({
          scrollTop: $("#inputQuantidade").scrollHeight
        }, 1500);
        $('#inputQuantidade').focus();
        return false;
      } else if (inputValorUnitario == '') {
        alerta('Atenção', 'Nenhum produto foi selecionado!', 'error');
        $('html, body').animate({
          scrollTop: $("#cmbProduto").scrollHeight
        }, 1500);
        $('#cmbProduto').focus();
        return false;
      }

      //Verifica se o campo já está no array
      if (inputIdProdutos.includes(Produto[0])) {
        alerta('Atenção', 'Esse produto já foi adicionado!', 'error');
        $('#cmbProduto').focus();
        return false;
      }

      $.ajax({
        type: "POST",
        url: "movimentacaoAddProdutoTransferencia.php",
        data: {
          tipo: inputTipo,
          numItens: resNumItens,
          idProduto: Produto[0],
          origem: origem[0],
          quantidade: inputQuantidade,
          classific: cmbClassificacao,
          patrimonioId: cmbPatrimonio
        },
        success: function(resposta) {
          let inputTipo = $('input[name="inputTipo"]:checked').val();

          $("#tblTransferencia").append(resposta);

          //Adiciona mais um item nessa contagem
          $('#inputNumItens').val(resNumItens);
          $('#inputQuantidade').val('');
          $('#inputValorUnitario').val('');
          $('#inputTotal').val(total);
          $('#total').text(totalFormatado);
          $('#inputLote').val('');

          $('#inputProdutos').append('<input type="hidden" class="inputProdutoServicoClasse" id="campo' + resNumItens + '" name="campo' + resNumItens + '" value="' + 'P#' + Produto[0] + '#' + inputValorUnitario + '#' + inputQuantidade + '#' + 'SaldoValNull' + '#' + inputLote + '#' + Produto[2] + '#' + cmbClassificacao + '">');

          inputIdProdutos = inputIdProdutos + ', ' + parseInt(Produto[0]);

          $('#inputIdProdutos').val(inputIdProdutos);

          $('input[name="inputTipo"]').each((i, elem) => {
            if ($(elem) !== $('input[name="inputTipo"]:checked')) {
              $(elem).attr('disabled', '')
            }
          })

          return false;
        }
      })

    }); //click


    $(document).on('click', '.btn_remove', function() {

      var inputTotal = $('#inputTotal').val();
      var button_id = $(this).attr("id");
      var Produto = button_id.split("#");
      var inputIdProdutos = $('#inputIdProdutos').val(); //array com o Id dos produtos adicionados
      var inputNumItens = $('#inputNumItens').val();

      var item = inputIdProdutos.split(",");

      var i;
      var arr = [];

      for (i = 0; i < item.length; i++) {
        arr.push(item[i]);
      }

      var index = arr.indexOf(Produto[0]);

      arr.splice(index, 1);

      $('#inputIdProdutos').val(arr);

      $("#row" + Produto[0] + "").remove(); //remove a linha da tabela
      $("#campo" + Produto[0] + "").remove(); //remove o campo hidden

      //Agora falta calcular o valor total novamente
      inputTotal = parseFloat(inputTotal) - parseFloat(Produto[1]);
      var totalFormatado = "R$ " + float2moeda(inputTotal).toString();

      $('#inputTotal').val(inputTotal);
      $('#total').text(totalFormatado);


      // var resNumItens = parseInt(inputNumItens) - 1;
      // $('#inputNumItens').val(resNumItens);
      // console.log($('#inputNumItens').val())

    })

    //Valida Registro Duplicado
    $('#enviar').on('click', function(e) {
      var inputTipo = $('input[name="inputTipo"]:checked').val();
      var inputTotal = $('#inputTotal').val();
      var cmbMotivo = $('#cmbMotivo').val();
      var cmbEstoqueOrigemLocalSetor = $('#cmbEstoqueOrigemLocalSetor').val();
      var cmbDestinoLocalEstoqueSetor = $('#cmbDestinoLocalEstoqueSetor').val()? $('#cmbDestinoLocalEstoqueSetor').val():$('#inputDestinoManual').val();
      var inputDestinoManual = $('#inputDestinoManual').val();

      var Motivo = cmbMotivo.split("#");
      var chave = Motivo[1];

      //remove os espaços desnecessários antes e depois
      inputDestinoManual = inputDestinoManual.trim();


      //Verifica se a combo Motivo foi informada
      if (cmbMotivo == '' || cmbMotivo == null) {
        console.log(cmbMotivo)
        alerta('Atenção', 'Informe o Motivo!', 'error');
        $('html, body').animate({
          scrollTop: $("#cmbMotivo")[0].scrollHeight
        }, 1500);
        $('#cmbMotivo').focus();
        $('#btnAdicionar').click();
        return false;
      }

      //Verifica se a combo Estoque de Origem foi informada
      if (cmbEstoqueOrigemLocalSetor == '' || cmbEstoqueOrigemLocalSetor == null) {
        event.preventDefault();
        alerta('Atenção', 'Informe o Estoque de Origem!', 'error');
        $('html, body').animate({
          scrollTop: $("#cmbEstoqueOrigemLocalSetor")[0].scrollHeight
        }, 1500);
        $('#cmbEstoqueOrigemLocalSetor').focus();
        $('#btnAdicionar').click();
        // $("#formMovimentacao").submit();
        return false;
      }

      if (cmbDestinoLocalEstoqueSetor == '' || cmbDestinoLocalEstoqueSetor == null) {
        event.preventDefault();
        alerta('Atenção', 'Informe o Local de Destino!', 'error');
        $('html, body').animate({
          scrollTop: $("#cmbDestinoLocalEstoqueSetor")[0].scrollHeight
        }, 1500);
        $('#cmbDestinoLocalEstoqueSetor').focus();
        $('#btnAdicionar').click();
        // $("#formMovimentacao").submit();
        return false;
      }

      if (chave == 'DOACAO' || chave == 'DESCARTE' || chave == 'DEVOLUCAO' || chave == 'CONSIGNACAO') {

        //Verifica se o input Destino foi informado
        if (inputDestinoManual == '' || inputDestinoManual == null) {
          event.preventDefault();
          alerta('Atenção', 'Informe o Destino!', 'error');
          $('html, body').animate({
            scrollTop: $("#inputDestinoManual")[0].scrollHeight
          }, 1500);
          $('#inputDestinoManual').focus();
          $('#btnAdicionar').click();
          // $("#formMovimentacao").submit();
          return false;
        }
      }

      //Verifica se tem algum produto na Grid
      if (inputTotal == '' || inputTotal == 0) {
        alerta('Atenção', 'Informe algum produto!', 'error');
        $('#cmbCategoria').focus();
        $('#btnAdicionar').click();
        return false;
      }

      //desabilita as combos "Fornecedor" e "Situacao" na hora de gravar, senão o POST não o encontra
      $('#cmbSituacao').prop('disabled', false);

      //desabilita o botão Incluir evitando duplo clique, ou seja, evitando inserções duplicadas
      $('#enviar').prop("disabled", true);

      $("#formMovimentacao").submit();
    });



    //Mostra o "Filtrando..." na combo SubCategoria e Produto ao mesmo tempo
    function Filtrando() {
      $('#cmbSubCategoria').empty().append('<option>Filtrando...</option>');
    }

    //Mostra o "Filtrando..." na combo Produto
    function FiltraCategoria() {
      $('#cmbCategoria').empty().append('<option>Filtrando...</option>');
    }

    //Mostra o "Filtrando..." na combo Produto
    function FiltraProduto() {
      $('#cmbProduto').empty().append('<option>Filtrando...</option>');
    }

    function FiltraOrdensCompra() {
      $('#cmbOrdemCompra').empty().append('<option>Filtrando...</option>');
    }

    function ResetCategoria() {
      $('#cmbCategoria').empty().append('<option>Sem Categoria</option>');
    }

    function ResetSubCategoria() {
      $('#cmbSubCategoria').empty().append('<option>Sem Subcategoria</option>');
    }

    function ResetProduto() {
      $('#cmbProduto').empty().append('<option>Sem produto</option>');
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
    if (tipo == 'E') {
      window.location.href = "movimentacaoNovoEntrada.php";
    } else if (tipo == 'S') {
      window.location.href = "movimentacaoNovoSaida.php";
    } else
      window.location.href = "movimentacaoNovoTransferencia.php";
  };


  function selecionaMotivo(motivo) {
    var Motivo = motivo.split("#");
    var chave = Motivo[1];

    if (chave == 'DOACAO' || chave == 'DESCARTE' || chave == 'DEVOLUCAO' || chave == 'CONSIGNACAO') {
      document.getElementById('DestinoManual').style.display = "block";
      document.getElementById('DestinoLocalEstoqueSetor').style.display = "none";
    } else {
      document.getElementById('DestinoManual').style.display = "none";
      document.getElementById('DestinoLocalEstoqueSetor').style.display = "block";
      document.getElementById('DestinoManual').value = '';
    }
  }


  function verifcMumero(elem) {
    if (typeof $(elem).val() == 'string') {
      return false
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
                      <input type="radio" name="inputTipo" value="E" class="form-input-styled" onclick="selecionaTipo('E')" data-fouc>
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
                      <input type="radio" name="inputTipo" value="T" class="form-input-styled" checked data-fouc>
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

          <form name="formMovimentacao" id="formMovimentacao" method="post" class="form-validate-jquery" action="movimentacaoNovoTransferencia.php">
            <div class="card-body">

              <div class="row">
                <div class="col-lg-12">
                  <h5 class="mb-0 font-weight-semibold">Dados da Transferência</h5>
                  <br>
                  <div class="row">
                    <div class="col-lg-2">
                      <div class="form-group">
                        <label for="inputData">Data<span style="color: red">*</span></label>
                        <input type="text" id="inputData" name="inputData" class="form-control" value="<?php echo date('d/m/Y'); ?>" readOnly>
                      </div>
                    </div>

                    <div class="col-lg-2" id="motivo">
                      <div class="form-group">
                        <label for="cmbMotivo">Motivo<span style="color: red">*</span></label>
                        <select id="cmbMotivo" name="cmbMotivo" class="form-control form-control-select2" onChange="selecionaMotivo(this.value)">
                          <option value="">Selecione</option>
                          <?php
													$sql = "SELECT MotivId, MotivNome, MotivChave
																	FROM Motivo
																	JOIN Situacao on SituaId = MotivStatus
																	WHERE SituaChave = 'ATIVO'
																	ORDER BY MotivNome ASC";
													$result = $conn->query($sql);
													$rowMotivo = $result->fetchAll(PDO::FETCH_ASSOC);

													foreach ($rowMotivo as $item) {
														print('<option value="' . $item['MotivId'] . '#' . $item['MotivChave'] . '">' . $item['MotivNome'] . '</option>');
													}

													?>
                        </select>
                      </div>
                    </div>

                    <div class="col-lg-4" id="EstoqueOrigemLocalSetor">
                      <div class="form-group">
                        <label for="cmbEstoqueOrigemLocalSetor">Origem<span style="color: red">*</span></label>
                        <select id="cmbEstoqueOrigemLocalSetor" name="cmbEstoqueOrigemLocalSetor" class="form-control form-control-select2">
                          <option value="">Selecione</option>
                          <?php
													$sql = "SELECT LcEstId as Id, LcEstNome as Nome, 'Local' as Referencia 
																		FROM LocalEstoque
																		JOIN Situacao on SituaId = LcEstStatus
																		WHERE LcEstUnidade = " . $_SESSION['UnidadeId'] . " and SituaChave = 'ATIVO'
																		UNION
																		SELECT SetorId as Id, SetorNome as Nome, 'Setor' as Referencia 
																		FROM Setor
																		JOIN Situacao on SituaId = SetorStatus
																		WHERE SetorUnidade = " . $_SESSION['UnidadeId'] . " and SituaChave = 'ATIVO'
																		Order By Nome";

													$result = $conn->query($sql);
													$row = $result->fetchAll(PDO::FETCH_ASSOC);

													foreach ($row as $item) {
														print('<option value="' . $item['Id'] . '#' . $item['Nome'] . '#' . $item['Referencia'] . '">' . $item['Nome'] . '</option>');
													}

													?>
                        </select>
                      </div>
                    </div>


                    <div class="col-lg-4" id="DestinoLocalEstoqueSetor">
                      <div class="form-group">
                        <label for="cmbDestinoLocalEstoqueSetor">Destino<span style="color: red">*</span></label>
                        <select id="cmbDestinoLocalEstoqueSetor" name="cmbDestinoLocalEstoqueSetor" class="form-control form-control-select2">
                          <option value="">Selecione</option>
                          <?php
													$sql = "SELECT LcEstId as Id, LcEstNome as Nome, 'Local' as Referencia 
																		FROM LocalEstoque
																		JOIN Situacao on SituaId = LcEstStatus
																		WHERE LcEstUnidade = " . $_SESSION['UnidadeId'] . " and SituaChave = 'ATIVO'
																		UNION
																		SELECT SetorId as Id, SetorNome as Nome, 'Setor' as Referencia 
																		FROM Setor
																		JOIN Situacao on SituaId = SetorStatus
																		WHERE SetorUnidade = " . $_SESSION['UnidadeId'] . " and SituaChave = 'ATIVO'
																		Order By Nome";
													$result = $conn->query($sql);
													$row = $result->fetchAll(PDO::FETCH_ASSOC);

													foreach ($row as $item) {
														print('<option value="' . $item['Id'] . '#' . $item['Nome'] . '#' . $item['Referencia'] . '">' . $item['Nome'] . '</option>');
													}
													?>
                        </select>
                      </div>
                    </div>

                    <div class="col-lg-4" id="DestinoManual" style="display:none">
                      <div class="form-group">
                        <label for="inputDestinoManual">Destino<span style="color: red">*</span></label>
                        <input type="text" id="inputDestinoManual" name="inputDestinoManual" class="form-control">
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



              <div class="row" id="dadosProduto">
                <div class="col-lg-12">
                  <div class="row" id="Patrimonio">
                    <div class="col-lg-4">
                      <div class="form-group">
                        <label for="cmbPatrimonio">Patrimônio</label>
                        <select id="cmbPatrimonio" name="cmbPatrimonio" class="form-control form-control-select2">
                          <option value="">Informe a origem</option>
                        </select>
                      </div>
                    </div>
                  </div>
                  <br>

                  <h5 class="mb-0 font-weight-semibold" id="tituloProdutoServico">Dados dos Produtos</h5>
                  <br>

                  <div class="row">
                    <div class="col-lg-4">
                      <div class="form-group">
                        <label for="cmbCategoria">Categoria</label>
                        <select id="cmbCategoria" name="cmbCategoria" class="form-control form-control-select2">
                          <option value="">Selecione</option>

                        </select>
                      </div>
                    </div>

                    <div class="col-lg-4">
                      <div class="form-group">
                        <label for="cmbSubCategoria">SubCategoria</label>
                        <select id="cmbSubCategoria" name="cmbSubCategoria" class="form-control form-control-select2">
                          <option value="">Selecione</option>
                        </select>
                      </div>
                    </div>

                    <div class="col-lg-4">
                      <div class="form-group">
                        <label for="cmbProduto">Produto</label>
                        <select id="cmbProduto" name="cmbProduto" class="form-control form-control-select2">
                          <option value="">Selecione</option>
                        </select>
                      </div>
                    </div>
                  </div>



                  <div class="row">

                    <div class="col-lg-2">
                      <div class="form-group">
                        <label for="inputQuantidade">Quantidade</label>
                        <input type="text" maxlength="10" id="inputQuantidade" name="inputQuantidade" class="form-control" onKeyUp="onlynumber(this)">
                      </div>
                    </div>

                    <div class="col-lg-2">
                      <div class="form-group">
                        <label for="inputValorUnitario">Valor Unitário</label>
                        <input type="text" id="inputValorUnitario" name="inputValorUnitario" class="form-control" readOnly>
                      </div>
                    </div>

                    <div class="col-lg-2" id="formLote">
                      <div class="form-group">
                        <label for="inputLote">Lote</label>
                        <input type="text" maxlength="50" id="inputLote" name="inputLote" class="form-control">
                      </div>
                    </div>



                    <div class="col-lg-2">
                      <div class="form-group" style='text-align:left'>
                        <button type="button" id="btnAdicionar" class="btn btn-lg btn-principal" style="margin-top:20px;">Adicionar</button>
                        <!--<button id="adicionar" type="button">Teste</button>-->
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <br>

              <div id="inputProdutos">
                <input type="hidden" id="inputNumItens" name="inputNumItens" value="0">
                <input type="hidden" id="itemEditadoquantidade" name="itemEditadoquantidade" value="0">
                <input type="hidden" id="inputIdProdutos" name="inputIdProdutos" value="0">
                <input type="hidden" id="inputProdutosRemovidos" name="inputProdutosRemovidos" value="0">
                <input type="hidden" id="inputTotal" name="inputTotal" value="0">
              </div>

              <div class="row">
                <div class="col-lg-12">
                  <?php
									print('<table class="table" id="tblTransferencia">');
									?>
                  <thead>
                    <?php

										print('
												<tr class="bg-slate"  >
													<th>Item</th>
													<th>Produto</th>
													<th>Nº do Patrimônio</th>
													<th style="text-align:center">Unidade Medida</th>
													<th id="quantEditaEntradaSaida" style="text-align:center">Quantidade</th>
													<th style="text-align:right">Valor Unitário</th>
													<th style="text-align:right">Valor Total</th>
													<th id="classificacaoSaida">Validade</th>
													<th class="text-center">Ações</th>
												</tr>
											');

										?>
                  </thead>
                  <tbody>
                    <?php
										print('<tr style="display:none;">
													<td>&nbsp;</td>
													<td>&nbsp;</td>
													<td>&nbsp;</td>
													<td>&nbsp;</td>
													<td>&nbsp;</td>
													<td>&nbsp;</td>
													<td>&nbsp;</td>
													<td>&nbsp;</td>
													<td>&nbsp;</td>
												</tr>
											');
										?>
                  </tbody>

                  <tfoot>
                    <tr>
                      <th id="totalTitulo" colspan="6" style="text-align:right; font-size: 16px; font-weight:bold;">Total (R$): </th>
                      <?php

											print('
													<th colspan="1">
														<div id="total" style="text-align:right; font-size: 15px; font-weight:bold;">R$ 0,00</div>
													</th>
												');
											print('
													<th colspan="2">
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
                        <label for="inputSituacao">Situação</label>
                        <!--<option value="#">Selecione</option>-->
                        <?php

												if ($_SESSION['PerfiChave'] == 'CONTROLADORIA' || $_SESSION['PerfiChave'] == 'SUPER') {
													$sql = "SELECT SituaId, SituaNome, SituaChave
																FROM Situacao
																WHERE SituaStatus = '1'
																ORDER BY SituaNome ASC";
													$result = $conn->query($sql);
													$row = $result->fetchAll(PDO::FETCH_ASSOC);

													print('<select id="cmbSituacao" name="cmbSituacao" class="form-control form-control-select2">');
													print('<option value="#">Selecione</option>');

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
													print('<select id="cmbSituacao" name="cmbSituacao" class="form-control form-control-select2" disabled>');
													print('<option value="#">Selecione</option>');
													print('<option value="9" selected>Aguardando Liberação</option>');
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
                    <a href="movimentacaoNovo.php" class="btn btn-basic" role="button">Cancelar</a>
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