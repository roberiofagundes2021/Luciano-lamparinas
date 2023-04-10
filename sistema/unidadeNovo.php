<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Nova Unidade';

include('global_assets/php/conexao.php');
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Lamparinas | Unidade</title>

  <?php include_once("head.php"); ?>

  <!-- Theme JS files -->
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
	<script src="global_assets/js/demo_pages/form_select2.js"></script>

	<script src="global_assets/js/demo_pages/form_layouts.js"></script>
	<script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>
	
	<script src="global_assets/js/plugins/forms/inputs/inputmask.js"></script>	

  <script src="global_assets/js/plugins/tables/datatables/datatables.min.js"></script>
  <script src="global_assets/js/plugins/tables/datatables/extensions/responsive.min.js"></script>
	<!-- /theme JS files -->	

  <!-- Validação -->
  <script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
  <script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
  <script src="global_assets/js/demo_pages/form_validation.js"></script>

  <!-- Passo a passo -->
  <script src="global_assets/js/plugins/forms/wizards/steps.min.js"></script>
  <script src="global_assets/js/demo_pages/form_wizard.js"></script>

  <script type="text/javascript">
 
    $(document).ready(function() {
      var iUnidadeNovo
      var erros = []
      

      $('#cnesTable').DataTable({
				"order": [[ 0, "desc" ],[ 1, "asc" ]],
        autoWidth: false,
				responsive: true,
        columnDefs: [
          {
            orderable: true,   //Estabelecimento
            width: "80%",
            targets: [0]
          },
          {
            orderable: true,   //CNES
            width: "10%",
            targets: [1]
          },
          {
            orderable: true,   //Tipo
            width: "10%",
            targets: [2]
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
			})

      function limpa_formulário_cep() {
        // Limpa valores do formulário de cep.
        $("#inputEndereco").val("");
        $("#inputBairro").val("");
        $("#inputCidade").val("");
        $("#cmbEstado").val("");
      }

      //Quando o campo cep perde o foco.
      $("#inputCep").blur(function() {

        $("#cmbEstado").removeClass("form-control-select2");

        //Nova variável "cep" somente com dígitos.
        var cep = $(this).val().replace(/\D/g, '');

        //Verifica se campo cep possui valor informado.
        if (cep != "") {

          //Expressão regular para validar o CEP.
          var validacep = /^[0-9]{8}$/;

          //Valida o formato do CEP.
          if (validacep.test(cep)) {

            //Preenche os campos com "..." enquanto consulta webservice.
            $("#inputEndereco").val("...");
            $("#inputBairro").val("...");
            $("#inputCidade").val("...");
            $("#cmbEstado").val("...");

            //Consulta o webservice viacep.com.br/
            $.getJSON("https://viacep.com.br/ws/" + cep + "/json/?callback=?", function(dados) {

              if (!("erro" in dados)) {

                //Atualiza os campos com os valores da consulta.
                $("#inputEndereco").val(dados.logradouro);
                $("#inputBairro").val(dados.bairro);
                $("#inputCidade").val(dados.localidade);
                $("#cmbEstado").val(dados.uf);
                //$("#cmbEstado").find('option[value="MA"]').attr('selected','selected');
                //$('#cmbEstado :selected').text();
                //$("#cmbEstado").find('option:selected').text();
                //document.getElementById("cmbEstado").options[5].selected = true;
              } //end if.
              else {
                //CEP pesquisado não foi encontrado.
                limpa_formulário_cep();
                alerta("Erro", "CEP não encontrado.", "erro");
              }
            });
          } //end if.
          else {
            //cep é inválido.
            limpa_formulário_cep();
            alerta("Erro", "Formato de CEP inválido.", "erro");
          }
        } //end if.
        else {
          //cep sem valor, limpa formulário.
          limpa_formulário_cep();
        }
      });

      //Valida Registro Duplicado
      $('#enviar').on('click', async function(e) {
        e.preventDefault();
        // essa lista representa os tipos de requisição que serão feitos para o arquivo "filtraUnidadeNovo.php"
        // caso queira inserir mais alguma cois basta coloca-lo aqui e programar a ação na tela "filtraUnidadeNovo.php"
        let itensRequest = {
          'PERFIS': 'Perfis',
          'PERFILPERMISSAOPADRAO': 'Permissões padrões',
          'PERFILPERMISSAO': 'Permissões',
          'GRUPOCONTAS': 'Grupo Contas',
          'LOCALESTOQUE': 'Locais de estoque',
          'FORMASPAGAMENTO': 'Formas de pagamento',
          'CLASSIFICACAO': 'Classificação',
          'CLASSIFICACAORISCO': 'Classificação de risco',
          'CENTROCUSTO': 'Centro de custos',
          'MODALIDADE': 'Modalidades',
          'ALTA': 'Motivo da Alta',
          'ESPECIALIDADELEITO': 'Especialidade do Leito',
        }
        let increment = 100 / Object.keys(itensRequest).length
        let porcentagem = 0

        // subistitui qualquer espaço em branco no campo "CEP" antes de enviar para o banco
        var cep = $("#inputCep").val()
        cep = cep.replace(' ','')
        $("#inputCep").val(cep)

        let inputNome = $('#inputNome').val();
        let inputCnpj = $('#inputCnpj').val().replace(/[^\d]+/g,'');
        let inputCep = $('#inputCep').val();
        let inputEndereco = $('#inputEndereco').val();
        let inputNumero = $('#inputNumero').val();
        let inputBairro = $('#inputBairro').val();
        let inputCidade = $('#inputCidade').val();
        let cmbEstado = $('#cmbEstado').val();

        if (inputCnpj.trim() == ''){
						$('#inputCnpj').val('');
        } else {
          if (!validarCNPJ(inputCnpj)){
            $('#inputCnpj').val('');
            alerta('Atenção','CNPJ inválido!','error');
            $('#inputCnpj').focus();
            return false;
          }
        }		

        //remove os espaços desnecessários antes e depois
        inputNome = inputNome.trim();

        //Verifica se o campo só possui espaços em branco
        if (!inputNome || !inputCep || !inputEndereco || !inputNumero || !inputBairro || !inputCidade || !cmbEstado) {
          $("#formUnidade").submit();
          $('#inputNome').focus();
          return false;
        }

        //Esse ajax está sendo usado para verificar no banco se o registro já existe
        $.ajax({
          type: "POST",
          url: "unidadeValida.php",
          data: ('nome=' + inputNome),
          success: async function(resposta) {
            if (resposta == 1) {
              alerta('Atenção', 'Esse registro já existe!', 'error');
              return false;
            }

            $('#cardData').addClass('d-none')
            $('#cardLoading').removeClass('d-none')

            //  cria UNIDADE

            await $.ajax({
              type: "POST",
              url: "filtraUnidadeNovo.php",
              data: {
                'tipoRequest': 'UNIDADE',
                'inputNome': $('#inputNome').val(),
                'inputCNES': $('#inputCNES').val(),
                'inputCnpj': $('#inputCnpj').val(),
                'inputTelefone': $('#inputTelefone').val(),
                'inputDiretorAdministrativo': $('#inputDiretorAdministrativo').val(),
                'inputDiretorTecnico': $('#inputDiretorTecnico').val(),
                'inputDiretorClinico': $('#inputDiretorClinico').val(),
                'inputCep': $('#inputCep').val(),
                'inputEndereco': $('#inputEndereco').val(),
                'inputNumero': $('#inputNumero').val(),
                'inputComplemento': $('#inputComplemento').val(),
                'inputBairro': $('#inputBairro').val(),
                'inputCidade': $('#inputCidade').val(),
                'cmbEstado': $('#cmbEstado').val()
              },
              success: async function(response) {
                porcentagem += increment
                iUnidadeNovo = parseInt(response)

                $('#textProgress').html('Incluindo Perfis')
                
                for(key in itensRequest){
                  $('#infoCard').append(`
                  <div class='row col-lg-12 text-center mt-3' style="background-color: #f8f8f8; border: 1px solid #eee; padding: 10px;">
                    <span id='textProgress' class="ml-2">Incluindo ${itensRequest[key]}</span>
                    <div id="imgLoading-${key}" style="margin-left: 4px; margin-top: -4px;">
                      <img src='global_assets/images/lamparinas/infinity.gif' style='width: 30px; height: 30px;'>
                    </div>
                  </div>`)
                  $('#progressBar').attr('style',`width: ${porcentagem}%;`)

                  await $.ajax({
                    type: "POST",
                    url: "filtraUnidadeNovo.php",
                    dataType: 'json',
                    data: {
                      'tipoRequest': key,
                      'unidadeIdNovo': iUnidadeNovo
                    },
                    success: function(response) {
                      $(`#imgLoading-${key}`).html('<i class="icon-checkmark3 text-green" style="font-size:22px;"></i>')
                      porcentagem += increment
                    },
                    error: function(response){
                      $(`#imgLoading-${key}`).html('<i class="icon-x text-danger" style="font-size:22px;"></i>')
                      porcentagem += increment
                      erros.push(key)
                    }
                  })
                }

                if(erros.length){
                  // colocar uma menssagem falando que houve erro ao cadastrar alguns itens e apresentar
                  // botão de nova tentativa
                }else{
                  window.location.href='unidade.php'
                }
              }
            })
          }
        })
      })

      // btn search do input CNES
      $('#cnesSearch').on('click', function(e){
        e.preventDefault()
        getTipoUnidadeCnes()
        $('#page-modal-cnes').fadeIn(200)
      })

      // btn close do modal cnes
      $('#modal-cnes-close-x').on('click', function(e){
        e.preventDefault()
        $('#page-modal-cnes').fadeOut(200)
      })

      // btn de consulta do modal cnes
      $('#concultarCnes').on('click',function(e){
        e.preventDefault()

        let msg = ''

        switch(msg){
          case $('#cnesNum').val()||$('#cnesTipo').val():msg = 'informe o numero CNES ou tipo de Estabelecimento';break;
          default: msg = '';break;
        }
        
        if(msg){
          alerta('Campo Obrigatório!', msg, 'error')
					return
        }

        let centroCirurgico = 'estabelecimento_possui_centro_cirurgico=1'
        let centroObstetrico = 'estabelecimento_possui_centro_obstetrico=1'
        let tipoUnidade = $('#cnesTipo').val() ? `&codigo_tipo_unidade=${$('#cnesTipo').val()}` : ''
        let CNES = $('#cnesNum').val() ? `&codigo_tipo_unidade=${$('#cnesTipo').val()}` : ''

        // let URL = `https://apidadosabertos.saude.gov.br/cnes/estabelecimentos?${centroCirurgico}&${centroObstetrico}${tipoUnidade}${tipoUnidade}`
        let URL = `https://cnes.datasus.gov.br/services/estabelecimentos?gestao=M&natureza=1&municipio=291170`

        $.ajax({
					type: 'GET',
					url: URL,
					dataType: 'json',
					// data: {
					// },
					success: function(response) {
            console.log(response)
					}
				});



        // let tableAgenda = $('#AgendaTable').DataTable().clear().draw()
        // tableAgenda = $('#AgendaTable').DataTable()

        // let rowTableAgenda

        // response.data.forEach(item => {
        //   rowTableAgenda = tableAgenda.row.add(item).draw().node()
        //   $(rowTableAgenda).attr('class', 'text-center')
        //   $(rowTableAgenda).find('td:eq(6)').attr('data-atendimento', `${item.identify.iAtendimento}`)
        // })
      })
    })

    function validaEFormataCnpj(){
			let cnpj = $('#inputCnpj').val();
			let resultado = validarCNPJ(cnpj);
			if (!resultado){
				let labelErro = $('#inputCnpj-error')
				labelErro.removeClass('validation-valid-label');
				labelErro[0].innerHTML = "CNPJ Inválido";	
				$('#inputCnpj').val("");
			}
			
		}

    function getTipoUnidadeCnes(){
      $.ajax({
					type: 'GET',
					url: 'https://apidadosabertos.saude.gov.br/cnes/tipounidades',
					dataType: 'json',
					// data: {
					// },
					success: function(response) {
            $('#cnesTipo').html('<option value="">selecione</option>')
						response.tipos_unidade.foreach(item => {
              $('#cnesTipo').append(`<option value="${item.codigo_tipo_unidade}">${item.descricao_tipo_unidade}</option>`)
            })
					}
				});
    }
  </script>

  <style>

      .passos {
          position: relative;
          display: block;
          width: 100%;
      }

      .passos>ul {
          display: table;
          width: 100%;
          table-layout: fixed;
          margin: 0;
          padding: 0;
          list-style: none;
      }

      .passos>ul>li {
          display: table-cell;
          width: auto;
          vertical-align: top;
          text-align: center;
          position: relative;
      }

      .passos>ul>li a {
          position: relative;
          padding-top: 3rem;
          margin-top: 1.25rem;
          margin-bottom: 1.25rem;
          display: block;
          outline: 0;
          color: #999;
      }
      
      .passos>ul>li:after, .passos>ul>li:before {
          content: '';
          display: block;
          position: absolute;
          top: 2.375rem;
          width: 50%;
          height: 2px;
          background-color: #00bcd4;
          z-index: 9;
      }   
      
      .passos>ul>li:before {
          left: 0;
      }

      .passos>ul>li:after {
          right: 0;
      }      

      /* Remove os traços antes e depois dos números */
      .passos>ul>li:first-child:before,.passos>ul>li:last-child:after{
          content:none
      }

      .passos>ul>li.current:after, .passos>ul>li.current~li:after, .passos>ul>li.current~li:before {
          background-color: #eee;
      }

      .passos>ul>li.current>a {
          color: #333;
          cursor: default;
      }      

      .passos>ul>li.current .number1{
          font-size: 0;
          border-color: #00bcd4;
          background-color: #fff;
          color: #00bcd4;
      }

      .passos>ul>li.current .number:after {
          content: '\e913';
          font-family: icomoon;
          display: inline-block;
          font-size: 1rem;
          line-height: 2.125rem;
          -webkit-font-smoothing: antialiased;
          -moz-osx-font-smoothing: grayscale;
          transition: all ease-in-out .15s;
      }  
      
      @media screen and (prefers-reduced-motion:reduce){
          .passos>ul>li.current .number1:after{
              transition:none
          }
      }  
      
      .passos>ul>li.disabled a{
          cursor:default
      }
      .passos>ul>li.done a,.passos>ul>li.done a:focus,.passos>ul>li.done a:hover{
          color:#999
      }
      .passos>ul>li.done .number1{
          font-size:0;
          background-color:#00bcd4;
          border-color:#00bcd4;
          color:#fff
      } 
      
      .passos>ul>li.done .number1:after{
          content:'\ed6f';
          font-family:icomoon;
          display:inline-block;
          font-size:1rem;
          line-height:2.125rem;
          -webkit-font-smoothing:antialiased;
          -moz-osx-font-smoothing:grayscale;
          transition:all ease-in-out .15s
      }
      @media screen and (prefers-reduced-motion:reduce){
          .passos>ul>li.done .number1:after{
              transition:none
          }
      }
      .passos>ul>li.error .number1{
          border-color:#f44336;
          color:#f44336
      }
      .card>.card-header:not([class*=bg-])>.passos>ul{
          border-top:1px solid rgba(0,0,0,.125)
      }
      @media (max-width:991.98px){
          .passos>ul{
              margin-bottom:1.25rem
          }
          .passos>ul>li{
              display:block;
              float:left;
              width:50%
          }
          .passos>ul>li>a{
              margin-bottom:0
          }
          .passos>ul>li:first-child:before,.passos>ul>li:last-child:after{
              content:''
          }
          .passos>ul>li:last-child:after{
              background-color:#00bcd4
          }
      }
      @media (max-width:767.98px){
          .passos>ul>li{
              width:100%
          }
          .passos>ul>li.current:after{
              background-color:#00bcd4
          }
      }      

      .passos .number1 {
          background-color: #fff;
          color: #ccc;
          display: inline-block;
          position: absolute;
          top: 0;
          left: 50%;
          margin-left: -1.1875rem;
          border: 2px solid #eee;
          font-size: .875rem;
          z-index: 10;
          line-height: 2.125rem;
          text-align: center;
          width: 2.375rem;
          height: 2.375rem;
          border-radius: 50%;
      }

      .number1:after {
          content: '\e913';
          font-family: icomoon;
          display: inline-block;
          font-size: 1rem;
          line-height: 2.125rem;
          -webkit-font-smoothing: antialiased;
          -moz-osx-font-smoothing: grayscale;
          transition: all ease-in-out .15s;
      }

      .passos .current-info {
          display: none;
      }

  </style>  

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
        <div id="cardData" class="card">

          <div class="card-header bg-white header-elements-inline">
						<h6 class="card-title text-uppercase font-weight-bold">Cadastrar Nova Unidade</h6>
					</div>

          <form name="formUnidade" id="formUnidade" method="post" class="form-validate-jquery wizard-form ">
            <div class="card-body">
              <div class="row">
                <div class="col-lg-5">
                  <div class="form-group">
                    <label for="inputNome">Nome da Unidade <span class='text-danger'>*</span></label>
                    <input type="text" id="inputNome" name="inputNome" class="form-control" placeholder="Unidade" required autofocus>
                  </div>
                </div>
                <div class="col-lg-3" id="CNPJ">
                  <div class="form-group">				
                    <label for="inputCnpj">CNPJ <span class="text-danger"> *</span></label>
                    <input type="text" id="inputCnpj" name="inputCnpj" class="form-control" placeholder="CNPJ" data-mask="99.999.999/9999-99" onblur="validaEFormataCnpj()"required>
                  </div>	
                </div>	
                <div class="col-lg-4">
                  <label for="inputCNES">CNES </label>
                  <div class="form-group form-group-feedback form-group-feedback-right">
                    <input type="text" id="inputCNES" name="inputCNES" class="form-control" placeholder="CNES" readonly>
                    <div id="cnesSearch" class="form-control-feedback form-control-feedback-lg">
                      <i class="icon-search4"></i>
                    </div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-lg-4">
                  <div class="form-group">
                    <label for="inputDiretorAdministrativo">Diretor Administrativo </label>
                    <input type="text" id="inputDiretorAdministrativo" name="inputDiretorAdministrativo" class="form-control" placeholder="Diretor Administrativo">
                  </div>
                </div>
                <div class="col-lg-4">
                  <div class="form-group">
                    <label for="inputDiretorTecnico">Diretor Técnico</label>
                    <input type="text" id="inputDiretorTecnico" name="inputDiretorTecnico" class="form-control" placeholder="Diretor Técnico">
                  </div>
                </div>	
                <div class="col-lg-4">
                  <div class="form-group">
                    <label for="inputDiretorClinico">Diretor Clínico</label>
                    <input type="text" id="inputDiretorClinico" name="inputDiretorClinico" class="form-control" placeholder="Diretor Clínico">
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-lg-12">
                  <h5 class="mb-0 font-weight-semibold">Endereço</h5>
                  <br>
                  <div class="row">
                    <div class="col-lg-1">
                      <div class="form-group">
                        <label for="inputCep">CEP <span class='text-danger'>*</span></label>
                        <input type="text" id="inputCep" name="inputCep" class="form-control" placeholder="CEP" maxLength="8" required>
                      </div>
                    </div>

                    <div class="col-lg-5">
                      <div class="form-group">
                        <label for="inputEndereco">Endereço <span class='text-danger'>*</span></label>
                        <input type="text" id="inputEndereco" name="inputEndereco" class="form-control" placeholder="Endereço" required>
                      </div>
                    </div>

                    <div class="col-lg-1">
                      <div class="form-group">
                        <label for="inputNumero">Nº <span class='text-danger'>*</span></label>
                        <input type="text" id="inputNumero" name="inputNumero" class="form-control" placeholder="Número" required>
                      </div>
                    </div>

                    <div class="col-lg-3">
                      <div class="form-group">
                        <label for="inputComplemento">Complemento</label>
                        <input type="text" id="inputComplemento" name="inputComplemento" class="form-control" placeholder="complemento">
                      </div>
                    </div>
                    <div class="col-lg-2">
                      <div class="form-group">
                        <label for="inputTelefone">Telefone</span></label>
                        <input type="tel" id="inputTelefone" name="inputTelefone" class="form-control" placeholder="Telefone" data-mask="(99) 9999-9999">
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-lg-4">
                      <div class="form-group">
                        <label for="inputBairro">Bairro <span class='text-danger'>*</span></label>
                        <input type="text" id="inputBairro" name="inputBairro" class="form-control" placeholder="Bairro" required>
                      </div>
                    </div>

                    <div class="col-lg-5">
                      <div class="form-group">
                        <label for="inputCidade">Cidade <span class='text-danger'>*</span></label>
                        <input type="text" id="inputCidade" name="inputCidade" class="form-control" placeholder="Cidade" required>
                      </div>
                    </div>

                    <div class="col-lg-3">
                      <div class="form-group">
                        <label for="cmbEstado">Estado <span class='text-danger'>*</span></label>
                        <select id="cmbEstado" name="cmbEstado" class="form-control" required>
                          <!-- retirei isso da class: form-control-select2 para que funcionasse a seleção do texto do estado, além do valor -->
                          <option value="">Selecione um estado</option>
                          <option value="AC">Acre</option>
                          <option value="AL">Alagoas</option>
                          <option value="AP">Amapá</option>
                          <option value="AM">Amazonas</option>
                          <option value="BA">Bahia</option>
                          <option value="CE">Ceará</option>
                          <option value="DF">Distrito Federal</option>
                          <option value="ES">Espírito Santo</option>
                          <option value="GO">Goiás</option>
                          <option value="MA">Maranhão</option>
                          <option value="MT">Mato Grosso</option>
                          <option value="MS">Mato Grosso do Sul</option>
                          <option value="MG">Minas Gerais</option>
                          <option value="PA">Pará</option>
                          <option value="PB">Paraíba</option>
                          <option value="PR">Paraná</option>
                          <option value="PE">Pernambuco</option>
                          <option value="PI">Piauí</option>
                          <option value="RJ">Rio de Janeiro</option>
                          <option value="RN">Rio Grande do Norte</option>
                          <option value="RS">Rio Grande do Sul</option>
                          <option value="RO">Rondônia</option>
                          <option value="RR">Roraima</option>
                          <option value="SC">Santa Catarina</option>
                          <option value="SP">São Paulo</option>
                          <option value="SE">Sergipe</option>
                          <option value="TO">Tocantins</option>
                          <option value="SS">Estrangeiro</option>
                        </select>
                      </div>
                    </div>
                  </div> <!-- row -->
                </div> <!-- col-lg-12 -->
              </div>

              <div class="row" style="margin-top: 10px;">
                <div class="col-lg-12">
                  <div class="form-group row">
                    <button class="btn btn-lg btn-principal" id="enviar">
                      Incluir
                    </button>
                    <a href="unidade.php" class="btn btn-basic" role="button">Cancelar</a>
                  </div>
                </div>
              </div>
            </div>
          </form>
        </div>
        
        <div id="cardLoading" class="card d-none" style="padding: 30px;">

          <h1>Criando Unidade</h1>
          <h4>Favor aguardar até que todos os passos sejam finalizados!!</h4>

          <div class="progress">
            <div id="progressBar" class="progress-bar" role="progressbar" style="width: 0%" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
          </div>
          <div id="infoCard" class="row m-0 mb-4 col-lg-12 align-content-center">
            <!-- <div class="col-lg-10 text-center">
              <span></span>
              <img id="gifLoading" src="global_assets/images/lamparinas/triangulos.gif" style="width: 80px; height: 40px;">
            </div> -->
          </div>
        </div>
        <!-- /info blocks -->
      </div>

      <!--Modal Auditoria-->
      <div id="page-modal-cnes" class="custon-modal">
          <div class="custon-modal-container" style="max-width: 800px;">
            <div class="card custon-modal-content">
              <div class="custon-modal-title mb-2" style="background-color: #466d96; color: #ffffff">
                  <p class="h5">Buscar CNES</p>
                  <i id="modal-cnes-close-x" class="fab-icon-open icon-cross2 p-3" style="cursor: pointer"></i>
              </div>

              <div class="px-1 m-2">
                <div class="col-lg-12 row">
                  <div class="col-lg-6">
                    <label>Nome do Estabelecimento</label>
                  </div>
                  <div class="col-lg-6">
                    <label>Tipo de Estabelecimento</label>
                  </div>

                  <div class="col-lg-6">
                    <input id="cnesNome" name="cnesNome" type="text" class="form-control" placeholder="nome do estabelecimento">
                  </div>
                  <div class="col-lg-6">
                    <select id="cnesTipo" name="cnesTipo" class="select-search">
                      <option value="">selecione</option>
                    </select>
                  </div>
                </div>

                <div class="col-lg-12 row mt-3">
                  <div class="col-lg-7">
                    <label>CNES</label>
                  </div>
                  <div class="col-lg-2">
                    <label>Centro Cirúrgico</label>
                  </div>
                  <div class="col-lg-2">
                    <label>Centro Obstétrico</label>
                  </div>
                  <div class="col-lg-1">
                    <label></label>
                  </div>

                  <div class="col-lg-7">
                    <input id="cnesNum" name="cnesNum" type="text" class="form-control" placeholder="número CNES">
                  </div>
                  <div class="col-lg-2">
                    <input id="cnesCentroCirurgico" name="cnesCentroCirurgico" type="checkbox" class="form-control">
                  </div>
                  <div class="col-lg-2">
                    <input id="cnesCentroObstetrico" name="cnesCentroObstetrico" type="checkbox" class="form-control">
                  </div>
                  <div class="col-lg-1">
                    <button id="concultarCnes" class="btn btn-principal">Buscar</button>
                  </div>
                </div>

                <div class="card mt-4">
                  <table class="table" id="cnesTable">
                    <thead>
                      <tr class="bg-slate text-left">
                        <th>Estabelecimento</th>
                        <th>CNES</th>
                        <th>Tipo</th>
                      </tr>
                    </thead>
                    <tbody>

                    </tbody>
                  </table>
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