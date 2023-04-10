<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Gastos Adicionais';

include('global_assets/php/conexao.php');

$_SESSION['gastosAdicionaisProdutos'] = [];

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Observação Entrada</title>

	<?php include_once("head.php"); ?>
	
	<link href="global_assets/css/icons/icomoon/styles.css" rel="stylesheet" type="text/css">
	<link href="global_assets/css/lamparinas/components.min.css" rel="stylesheet" type="text/css">

	<script src="global_assets/js/main/bootstrap.bundle.min.js"></script>
	<script src="global_assets/js/plugins/loaders/blockui.min.js"></script>
	<script src="global_assets/js/plugins/ui/ripple.min.js"></script>

	<script src="global_assets/js/plugins/forms/wizards/steps.min.js"></script>
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
	<script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>
	<script src="global_assets/js/plugins/forms/inputs/inputmask.js"></script>
	<script src="global_assets/js/demo_pages/form_select2.js"></script>
	<script src="global_assets/js/demo_pages/form_layouts.js"></script>	
    <script src="global_assets/js/plugins/tables/datatables/datatables.min.js"></script>
    <script src="global_assets/js/plugins/tables/datatables/extensions/responsive.min.js"></script>
    <script src="global_assets/js/demo_pages/datatables_responsive.js"></script>
    <script src="global_assets/js/demo_pages/datatables_sorting.js"></script>
	<script src="global_assets/js/plugins/editors/summernote/summernote.min.js"></script>
	
	<script src="global_assets/js/plugins/ui/moment/moment.min.js"></script>
	<script src="global_assets/js/plugins/pickers/daterangepicker.js"></script>
	<script src="global_assets/js/plugins/pickers/anytime.min.js"></script>
	<script src="global_assets/js/plugins/pickers/pickadate/picker.js"></script>
	<script src="global_assets/js/plugins/pickers/pickadate/picker.date.js"></script>
	<script src="global_assets/js/plugins/pickers/pickadate/picker.time.js"></script>
	<script src="global_assets/js/plugins/pickers/pickadate/legacy.js"></script>
	<script src="global_assets/js/plugins/notifications/jgrowl.min.js"></script>

	<!-- Plugin para corrigir a ordenação por data. Caso a URL dê problema algum dia, salvei esses 2 arquivos na pasta global_assets/js/lamparinas -->
	<script type="text/javascript" language="javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.8.4/moment.min.js"></script>
	<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/plug-ins/1.10.10/sorting/datetime-moment.js"></script>	

	<!-- Modal -->
	<script src="global_assets/js/plugins/notifications/bootbox.min.js"></script>
    
    <!-- Validação -->
	<script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
	<script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
	<script src="global_assets/js/demo_pages/form_validation.js"></script>
	
	<script type="text/javascript">
		$(document).ready(function() {

			getProdutos()
			getAtendimentos()
			getCmbs()			

            /* Início: Tabela Personalizada */
			$('#tblTabelaGastosAdicionais').DataTable( {
				"order": [[ 0, "asc" ]],
			    autoWidth: false,
				responsive: true,
			    columnDefs: [
				{ 
					orderable: true,   //item
					width: "5%", //15
					targets: [0]
				},
				{ 
					orderable: true,   //data-hora
					width: "15%", //20
					targets: [1]
				},
				{ 
					orderable: true,   //grupo
					width: "10%", //15
					targets: [2]
				},				
				{ 
					orderable: true,   //subgrupo
					width: "10%", //15
					targets: [3]
				},
				{ 
					orderable: true,   //procedimento
					width: "30%", //15
					targets: [4]
				},
				{ 
					orderable: true,   //Valor
					width: "10%",
					targets: [5]
				},
				{ 
					orderable: false,   //Ações
					width: "10%",
					targets: [6]
				}],
				dom: '<"datatable-header"fl><"datatable-scroll-wrap"t><"datatable-footer"ip>',
				language: {
					search: '<span>Filtro:</span> _INPUT_',
					searchPlaceholder: 'filtra qualquer coluna...',
					lengthMenu: '<span>Mostrar:</span> _MENU_',
					paginate: { 'first': 'Primeira', 'last': 'Última', 'next': $('html').attr('dir') == 'rtl' ? '&larr;' : '&rarr;', 'previous': $('html').attr('dir') == 'rtl' ? '&rarr;' : '&larr;' }
				}
                
			});

			/* Início: Tabela Personalizada */
			$('#tblTabelaProdutosSelecionados').DataTable( {
				"order": [[ 0, "asc" ]],
			    autoWidth: false,
				responsive: true,
				searching: false,
				ordering: false, 
				paging: false,
			    columnDefs: [
				{ 
					orderable: true,   
					width: "15%", 
					targets: [0]
				},
				{ 
					orderable: true,   
					width: "25%", 
					targets: [1]
				},
				{ 
					orderable: true,  
					width: "10%", 
					targets: [2]
				},				
				{ 
					orderable: true,   
					width: "10%", 
					targets: [3]
				},
				{ 
					orderable: true,   
					width: "10%", 
					targets: [4]
				},
				{ 
					orderable: true,  
					width: "10%",
					targets: [5]
				}],
				dom: '<"datatable-header"fl><"datatable-scroll-wrap"t><"datatable-footer">',
				language: {
					search: '<span>Filtro:</span> _INPUT_',
					searchPlaceholder: 'filtra qualquer coluna...',
					lengthMenu: '<span>Mostrar:</span> _MENU_',
					paginate: { 'first': 'Primeira', 'last': 'Última', 'next': $('html').attr('dir') == 'rtl' ? '&larr;' : '&rarr;', 'previous': $('html').attr('dir') == 'rtl' ? '&rarr;' : '&larr;' }
				}
                
			});

			divTotalP = `<div class="row" style="padding-right: 14%;">
                            <div class="col-lg-9">
								<button class="btn btn-lg btn-success fecharConta" id="">Concluir</button>
								<a href="atendimento.php" class="btn btn-basic" role="button">Voltar</a>
							</div>
							<div id="tabelaValoresProdutos" class="col-lg-3 text-right" >	
								<div style='font-weight: bold;'>Desconto: </div> <br> 
								<div style='font-weight: bold;'>TOTAL A PAGAR: </div>
							</div>
						</div> <br>`
			$('.footerProduto').append(divTotalP);

			$('#paciente').on('change', ()=>{
				let idPaciente = $('#paciente').val();
				setPaciente(idPaciente);	
				$('#box-selecionar-paciente').hide();
			})

			$('#inserirProduto').on('click',function(e){
				$('#formProduto').submit()
			})

			$('#formProduto').submit(function(e){
				e.preventDefault()
				let menssageError = ''

				let produtos  = $('#produto').val()

				switch(menssageError){
					case produtos: menssageError = 'informe o Produto'; $('#produto').focus();break;					
					default: menssageError = ''; break;
				}

				if(menssageError){
					alerta('Campo Obrigatório!', menssageError, 'error')
					return
				}

				$.ajax({
					type: 'POST',
					url: 'filtraAtendimentoGastosAdicionais.php',
					dataType: 'json',
					data:{
						'tipoRequest': 'ADICIONARPRODUTO',
						'servico': produtos,
					},
					success: function(response) {
						if(response.status == 'success'){
							checkProdutos()
							alerta(response.titulo, response.menssagem, response.status)
						} else {
							alerta(response.titulo, response.menssagem, response.status);
						}
					},
					error: function(response) {
						alerta(response.titulo, response.menssagem, response.status);
					}
				});
			})

			$('#setDescontoProduto').on('click', function(e){
				$.ajax({
					type: 'POST',
					url: 'filtraAtendimentoGastosAdicionais.php',
					dataType: 'json',
					data:{
						'tipoRequest': 'SETDESCONTOPRODUTO',
						'id':$('#itemIdp').val(),
						'desconto':$('#inputDescontop').val(),
					},
					success: async function(response) {
						checkProdutos()
						$('#pageModalDescontosProduto').fadeOut(200)
						alerta(response.titulo, response.menssagem, response.status)
					}
				});
			})


			$('#inputDescontop').on('keyup', function(e){
				let novoValor = parseFloat($('#itemModalValuep').val()) - ($('#inputDescontop').val().replaceAll('.', '').replace(',', '.')?parseFloat($('#inputDescontop').val().replaceAll('.', '').replace(',', '.')):0)
				$('#inputModalValorFp').val(`R$ ${float2moeda(novoValor)}`)
			})

			$('.fecharConta').on('click', function(e){
				e.preventDefault();
				let menssageError = ''

				let idAtendimento = $('#atendimentoInf').val()
				let idPaciente = $('#pacienteInf').val()

				switch(menssageError){
					case idPaciente: menssageError = 'informe o Paciente, ou selecione um Atendimento'; $('#paciente').focus();break;					
					default: menssageError = ''; break;
				}

				if(menssageError){
					alerta('Campo Obrigatório!', menssageError, 'error')
					return
				}

				$.ajax({
					type: 'POST',
					url: 'filtraAtendimentoGastosAdicionais.php',
					dataType: 'json',
					data:{
						'tipoRequest': 'FECHARCONTA',
						'idAtendimento' : idAtendimento,
						'idPaciente' : idPaciente
					},
					success: function(response) {
						if(response.status == 'success'){

							cancelarVinculacao()							
							getProdutos()
							getAtendimentos()
							getCmbs()
							checkProdutos()

							$('#produto').val('').change()
							
							alerta(response.titulo, response.menssagem, response.status)
						} else {
							alerta(response.titulo, response.menssagem, response.status)
						}
					}
				});
			})

			$('#addPaciente').on('click', function(e) {
				e.preventDefault();
				$('#page-modal-paciente').fadeIn(200)
			})

			$('#modalPaciente-close-x').on('click', () => {
				$('#iAtendimento').val('')
				$('#page-modal-paciente').fadeOut(200)
			})

			$('#voltarPacienteModal').on('click', () => {
				$('#iAtendimento').val('')
				$('#page-modal-paciente').fadeOut(200)
			})


			

			$('#salvarPacienteModal').on('click', function(e) {
				e.preventDefault()

				let menssageError = ''
				switch (menssageError) {
					case $('#nomeNew').val():
						menssageError = 'Informe o nome!!';
						$('#nomeNew').focus();
						break;
					case $('#cpfNew').val():
						menssageError = 'Informe o CPF!!';
						$('#cpfNew').focus();
						break;
					default:
						menssageError = '';
						break;
				}

				if (menssageError) {
					alerta('Campo Obrigatório!', menssageError, 'error')
					return
				}

				var cpfSoNumeros = $('#cpfNew').val().replace(/[^\d]+/g, '');
				if(!validaCPF(cpfSoNumeros)){
					alerta('CPF Inválido!', 'Digite um CPF válido!!', 'error')
					return
				}

				let dataPreenchida = $("#nascimentoNew").val();
				if(!validaDataNascimento(dataPreenchida)){
					$('#nascimentoNew').val('');
					alerta('Atenção', 'Data de nascimento não pode ser futura!', 'error');
					$('#nascimentoNew').focus();
					return
				}

				$.ajax({
					type: 'POST',
					url: 'filtraAtendimento.php',
					dataType: 'json',
					data: {
						'tipoRequest': 'SALVARPACIENTE',
						'prontuario': $('#prontuarioNew').val(),
						'nome': $('#nomeNew').val(),
						'nomeSocial': $('#nomeSocialNew').val(),
						'cpf': cpfSoNumeros,
						'cns': $('#cnsNew').val(),
						'rg': $('#rgNew').val(),
						'emissor': $('#emissorNew').val(),
						'uf': $('#ufNew').val(),
						'sexo': $('#sexoNew').val(),
						'nascimento': $('#nascimentoNew').val(),
						'nomePai': $('#nomePaiNew').val(),
						'nomeMae': $('#nomeMaeNew').val(),
						'racaCor': $('#racaCorNew').val(),
						'naturalidade': $('#naturalidadeNew').val(),
						'profissao': $('#profissaoNew').val(),
						'estadoCivil': $('#estadoCivilNew').val(),
						'cep': $('#cepNew').val(),
						'endereco': $('#enderecoNew').val(),
						'numero': $('#numeroNew').val(),
						'complemento': $('#complementoNew').val(),
						'bairro': $('#bairroNew').val(),
						'cidade': $('#cidadeNew').val(),
						'estado': $('#estadoNew').val(),
						'contato': $('#contatoNew').val(),
						'telefone': $('#telefoneNew').val(),
						'celular': $('#celularNew').val(),
						'email': $('#emailNew').val(),
						'observacao': $('#observacaoNew').val()
					},
					success: async function(response) {
						if (response.status == 'success') {
							alerta(response.titulo, response.menssagem, response.status)
							await getCmbs({
								'pacienteID': response.id
							})

							setPaciente(response.id)

							$('#page-modal-paciente').fadeOut(200)
						} else {
							alerta(response.titulo, response.menssagem, response.status)
						}
					}
				});
			})

		}); //document.ready


		function validaDataNascimento(dataASerValidada){			
			//Adicionado um espaço para forçar o fuso horário de brasília		
			let dataObj = new Date(dataASerValidada+" ");
			let hoje = new Date();
			if((hoje-dataObj)<0){
				return false;				
			}
			else{
				return true;
			}
		}

		//check produtos
		function checkProdutos(){			
			$.ajax({
				type: 'POST',
				url: 'filtraAtendimentoGastosAdicionais.php',
				dataType: 'json',
				data:{
					'tipoRequest': 'CHECKPRODUTO',
				},
				success: async function(response) {

					statusProdutos = response.array.length?true:false;
					if(statusProdutos){

						$('#dataProduto').html('');

						let HTML = ''
						let i = 1;
						response.array.forEach(item => {
							if(item.status != 'rem'){
								let exc = `<a class='list-icons-item removeItemP' style='color: black; cursor:pointer' data-id="${item.id}" data-valor="${item.valor}"><i class='icon-bin' title='Excluir Produto'></i></a>`;
								let popup = `<a class='list-icons-item openPopUpProduto' style="color:${(item.desconto?'#50b900':'#000')}; cursor:pointer" data-value="${float2moeda(item.valor)}" data-titulo="${item.servico}" data-id="${item.id}"><i class='icon-cash' title='Desconto'></i></a>`;
								
								let acoes = `<div class='list-icons'>
											${popup}
											${exc}
										</div>`;
								HTML += `
								<tr class='servicoItem'>
									<td class="text-left">${item.servico}</td>
									<td class="text-left">${item.detalhamento}</td>
									<td class="text-left">${item.marcaNome}</td>
									<td class="text-left"> </td>
									<td class="text-right">R$ ${float2moeda(item.valor)}</td>
									<td class="text-center">${acoes}</td>
								</tr>`
							}
							i++;
						})
				
						$('#tabelaValoresProdutos').html(`
							<div style='font-weight: bold;'>Desconto: R$${float2moeda(response.desconto)}</div> <br> 
							<div style='font-weight: bold;'>TOTAL A PAGAR: R$${float2moeda(response.valorTotal)}</div>
						`)

						$('#dataProduto').html(HTML).show();
						$('#tblTabelaProdutosSelecionados').show();

						$('.openPopUpProduto').each(function(index,element){
							$(element).on('click', function(e){
								e.preventDefault()
								$('#itemIdp').val($(this).data('id'))
								$('#inputModalValorBp').val(`R$ ${$(this).data('value')}`)
								$('#itemModalValuep').val($(this).data('value'))
								let Val1 = parseFloat($(this).data('value'))

								$('#tituloModalp').html(`Descontos do produto <strong>${$(this).data('titulo')}</strong>`)
								$.ajax({
									type: 'POST',
									url: 'filtraAtendimentoGastosAdicionais.php',
									dataType: 'json',
									data:{
										'tipoRequest': 'GETDESCONTOPRODUTO',
										'id': $(this).data('id')
									},
									success: async function(response) {
										if(response.status == 'success'){
											if (response.desconto != 0) {
												$('#inputDescontop').val(response.desconto)
											}
											let Val2 = parseFloat(response.desconto)
											console.log(Val1)
											console.log(Val2)
											$('#inputModalValorFp').val(`R$ ${float2moeda(Val1 - Val2)}`)
											$('#pageModalDescontosProduto').fadeIn(200)
										}else{
											alerta(response.titulo, response.menssagem, response.status)
										}
									}
								});
							})
						})

						$('.removeItemP').each(function(index,element){
							$(element).on('click', function(e){
								$.ajax({
									type: 'POST',
									url: 'filtraAtendimentoGastosAdicionais.php',
									dataType: 'json',
									data:{
										'tipoRequest': 'EXCLUIPRODUTO',
										'id': $(this).data('id'),
									},
									success: function(response) {
										alerta(response.titulo, response.menssagem, response.status)
										checkProdutos()
									}
								});
							})
						})
					}else{
						$('#dataProduto').hide();

						$('#tabelaValoresProdutos').html(`
							<div style='font-weight: bold;'>Desconto:</div> <br> 
							<div style='font-weight: bold;'>TOTAL A PAGAR:</div>
						`)
					}
				}
			});
		}

		function setPaciente(idPaciente) {

			$.ajax({ 
				type: 'POST',
				url: 'filtraAtendimento.php',
				dataType: 'json',
				data: {
					'tipoRequest': 'PACIENTE',
					'iPaciente': idPaciente
				},
				success: function(response) {
					if (response.status == 'success') {
											
						document.getElementById("paciente-informado").innerText =  "" + response.nome + " - Prontuário: " + response.prontuario;
						$('#pacienteInf').val(idPaciente);	
									
					} else {
						alerta(response.titulo, response.menssagem, response.status)
					}
				}
			});
			
		}
		
		function getCmbs(obj){

			$.ajax({
				type: 'POST',
				url: 'filtraAtendimento.php',
				dataType: 'json',
				data: {
					'tipoRequest': 'PACIENTES'
				},
				success: function(response) {
					$('#paciente').empty();
					$('#paciente').append(`<option value=''>Selecione</option>`)
					let opt = ''
					response.forEach(item => {
						let id = obj && obj.pacienteID? obj.pacienteID:null
						opt = id == item.id?`<option selected value="${item.id}">${item.nome}</option>`:`<option value="${item.id}">${item.nome}</option>`
						$('#paciente').append(opt)
					})
				}
			});

		}

		function getAtendimentos(){
			$.ajax({
				type: 'POST',
				url: 'filtraAtendimentoGastosAdicionais.php',
				dataType: 'json',
				data: {
					'tipoRequest': 'ATENDIMENTOS'
				},
				success: async function(response) {
					
					$('#tblTabelaGastosAdicionais').DataTable().clear().draw()

					tableAtendimento = $('#tblTabelaGastosAdicionais').DataTable()
					let rowNodeAtendimento

					await response.dataAtendimento.forEach(item => {
						rowNodeAtendimento = tableAtendimento.row.add(item.data).draw().node()
					})
					
				}
			});
		}

		function getProdutos() {
			$.ajax({
				type: 'POST',
				url: 'filtraAtendimentoGastosAdicionais.php',
				dataType: 'json',
				data: {
					'tipoRequest': 'PRODUTOS',
				},
				success: function(response) {

					$('#produto').empty();
					$('#produto').append(`<option value=''>Selecione</option>`)
					let opt = ''
					response.forEach(item => {
						opt = `<option value="${item.id}">${item.descricao}</option>`
						$('#produto').append(opt)
					})
				}
			});
		}

		function cancelarVinculacao() {

			$('.btnSelecionar').css('display', 'block');
			$('.btnSelecionado').css('display', 'none');

			$('#atendimentoInf').val('');
			$('#pacienteInf').val('');
			$('#selPacienteBtn').show();
			$('#cancVinculacaoBtn').hide();
			document.getElementById("atendimento-informado").innerText = "Nenhum vínculo informado";
			document.getElementById("paciente-informado").innerText = "Nenhum paciente informado";

		}

		function openSelPaciente(){
			$('#box-selecionar-paciente').show();
		}

		function selecionarAtendimento(atendServ, atendId, numRegistro, idPaciente){

			$('.btnSelecionar').css('display', 'block');
			$('.btnSelecionado').css('display', 'none');
			$('#atendSelecionar-' + atendServ).css('display', 'none');
			$('#atendSelecionado-' + atendServ).css('display', 'block');

			document.getElementById("atendimento-informado").innerText = "Sim. Atendimento: " + numRegistro;
			$('#atendimentoInf').val(atendId);
			$('#cancVinculacaoBtn').show();
			$('#selPacienteBtn').hide();
			$('#box-selecionar-paciente').hide();
			setPaciente(idPaciente);

		}

	</script>

	<style>
		.valorTotalEDesconto {
            font-size: 1.5rem;
            border: 1px solid #ccc;
            float: left;
            min-width: 100px;
        }
	</style>

</head>

<body class="navbar-top sidebar-xs">

	<?php include_once("topo.php"); ?>	

	<!-- Page content -->
	<div class="page-content">
		
		<?php
			include_once("menu-left.php");
			//include_once("menuLeftSecundarioVenda.php");
		?>

		<!-- Main content -->
		<div class="content-wrapper">

			<?php include_once("cabecalho.php"); ?>	

			<!-- Content area -->
			<div class="content">

				<!-- Info blocks -->		
				<div class="row">
					
					<div class="col-lg-12">
						<form id='dadosPost'>
							<?php
								//echo "<input type='hidden' id='iAtendimentoId' name='iAtendimentoId' value='$iAtendimentoId' />";
							?>
						</form>
						<!-- Basic responsive configuration -->
						
						<?php
							//echo "<input type='hidden' id='iAtendimentoId' name='iAtendimentoId' value='$iAtendimentoId' />";
						?>
						<div class="card">
							<div class="card-header header-elements-inline">
								<h3 class="card-title"><b>Gastos Adicionais</b></h3>
							</div>
						</div>

						<div class="box-entradaPaciente" style="display: block;">
							<div class="card">

								<div class="card-header header-elements-inline">
									<h3 class="card-title text-bold">Selecionar Atendimento</h3>
								</div>

                                <div class="card-body">

                                    <div class="row">
                                        <div class="col-lg-12">
                                            <table class="table" id="tblTabelaGastosAdicionais">
                                                <thead>
                                                    <tr class="bg-slate">
                                                        <th class="text-left">Data/hora</th>
                                                        <th class="text-left">Nº Registro</th>
                                                        <th class="text-left">Prontuário</th>
                                                        <th class="text-left">Paciente</th>
                                                        <th class="text-left">Profissional</th>
                                                        <th class="text-right">Modalidade</th>
                                                        <th class="text-right">Procedimento</th>
                                                        <th class="text-right">Situação</th>
                                                        <th class="text-center">Ações</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="dataServico">
                                                </tbody>
                                            </table>
                                        </div>		
                                    </div>			
                                    
                                </div>
                               				
								<div class="card-header header-elements-inline">
									<h3 class="card-title">Gasto vinculado a algum atendimento? <span id="cancVinculacaoBtn" style="cursor: pointer; color: blue; display: none;" onCLick="cancelarVinculacao()">Cancelar Vinculação</span></h3>
								</div>

								<input type="hidden" id="atendimentoInf" name="atendimentoInf">
								<div class="card-body" style='margin: auto -10px -25px;'>
                                    <div class="col-lg-12" >
                                        <div  class="alert alert-dark" role="alert" >
                                            <h5 id="atendimento-informado">Nenhum vínculo informado</h3>
                                        </div>
                                    </div>

								</div>

                                
								<div class="card-header header-elements-inline">
									<h3 class="card-title">Gasto relacionado a qual paciente? <span id="selPacienteBtn" style="cursor: pointer; color: blue" onCLick="openSelPaciente()">Selecionar Paciente</span></h3>
								</div>

								<div id="box-selecionar-paciente" class="card-body" style="display: none;">

									<div class="row" style="margin-top: -10px">	
										
										<div class="col-lg-4">Paciente</div>
										<div class="col-lg-4"><a style="cursor: pointer; color: blue" id="addPaciente" >Cadastrar Novo Paciente</a></div>

										<div class="col-lg-6">
											<select id="paciente" name="paciente" class="select-search" >
												<option value="">Selecione</option>
											</select>
										</div>
									</div>

								</div>	

								<input type="hidden" name="pacienteInf" id="pacienteInf">								
								<div class="card-body" style='margin: auto -10px -25px; border-top: 0px;'>
                                    <div class="col-lg-12" >
                                        <div id="paciente-informado" class="alert alert-dark" role="alert" >
                                            <h5 id="paciente-informado">Nenhum paciente informado</h5>			
                                        </div>
                                    </div>

								</div>


                                <div class="card-header header-elements-inline">
									<h3 class="card-title">Produtos Selecionados</h3>
								</div>
								
								<div class="card-body" >

									<form id="formProduto" name="formProduto" method="post" class="form-validate-jquery">
										<div class="row" style="margin-top: -10px">												
											<div class="col-lg-6">
												<select id="produto" name="produto" class="select-search" >
													<option value="">Selecione</option>
												</select>
											</div>
											<div class="col-lg-1" style="margin-top: -5px;">
												<a id="inserirProduto" class="btn btn-lg btn-primary">+</a>
											</div>
										</div>
									</form>

								</div>

								<div class="card-body" style="border-top: 0px;">

                                    <div class="row">
                                        <div class="col-lg-12">
                                            <table class="table" id="tblTabelaProdutosSelecionados">
                                                <thead>
                                                    <tr class="bg-slate">
                                                        <th class="text-left">Produto</th>
                                                        <th class="text-left">Detalhamento</th>
                                                        <th class="text-left">Marca</th>
                                                        <th class="text-left">Validade</th>
                                                        <th class="text-right">Valor</th>
                                                        <th class="text-center">Ações</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="dataProduto">
                                                </tbody>
                                            </table>
											<div class="footerProduto" style="margin-left: 25px"></div>
                                        </div>		
                                    </div>			
                                    
                                </div>

							</div>
						</div>                  
							
					</div>
				</div>
				<!-- /info blocks -->
				<!--Modal Desconto Produto-->
				<div id="pageModalDescontosProduto" class="custon-modal">
								<div class="custon-modal-container" style="max-width: 500px;">
									<div class="card custon-modal-content">
										<div class="custon-modal-title mb-2" style="background-color: #466d96; color: #ffffff">
											<p id='tituloModalp' class="h5">Desconto</p>
											<i id="modal-close-x-p" class="fab-icon-open icon-cross2 p-3" style="cursor: pointer"></i>
										</div>
										<div class="px-0">
											<div class="d-flex flex-row">
												<div class="col-lg-12">
													<form id="editaSituacao" name="alterarSituacao" method="POST" class="form-validate-jquery">
														<div class="form-group">
																														
															<div class="p-3">
																<div class="row d-flex flex-row justify-content-between">
																	<div class=" col-lg-12 d-flex justify-content-center">
																		<div class=" col-lg-8 form-group text-center ">												

																			<div class="row">
																				<div class="col-12">
																					<h2 class="text-left pr-3">Desconto:</h2>
																					<div class="text-right pl-10" >
																						<input id="inputDescontop" maxLength="12" onKeyUp="moeda(this)" class="form-control valorTotalEDesconto text-center" style="color: green" type="text" name="inputDescontoP">
																					</div>                                                
																				</div>
																			</div>   
																			<div class="row">
																				<div class="col-12">
																					<h2 class="text-left pr-3">Valor:</h2>
																					<div class="text-right pl-10">
																						<input id="inputModalValorBp" maxLength="12" class="form-control valorTotalEDesconto text-center" type="text" readonly>
																					</div>                                                
																				</div>
																			</div>  
																			<div class="row">
																				<div class="col-12">
																					<h2 class="text-left pr-3">Valor Final:</h2>
																					<div class="text-right pl-10">
																						<input id="inputModalValorFp" maxLength="12" class="form-control valorTotalEDesconto text-center" type="text" readonly>
																					</div>                                                
																				</div>
																			</div>    

																			<input id="itemIdp" name="itemId" type="hidden" value=''>
																			<input id="itemModalValuep" name="itemId" type="hidden" value=''>
																		</div>
																	</div>
																</div>
															</div>
														</div>
													</form>
												</div>
											</div>
											<div class="text-right m-2">
												<button id="setDescontoProduto" class="btn btn-principal" role="button">Confirmar</button>
											</div>
										</div>
									</div>
								</div>
							</div>
				</div>

				<!--Modal-->
				<div id="page-modal-paciente" class="custon-modal">
					<div class="custon-modal-container" style="max-width: 800px; height: 95%;">
						<div class="card custon-modal-content" style="height: 95%;">
							<div class="custon-modal-title mb-2" style="background-color: #466d96; color: #ffffff">
								<p class="h5">Novo paciente</p>
								<i id="modalPaciente-close-x" class="fab-icon-open icon-cross2 p-3" style="cursor: pointer"></i>
							</div>
							<div class="px-0" style="overflow-y: scroll;">
								<div class="d-flex flex-row">
									<div class="col-lg-12">
										<form id="novoPaciente" name="alterarSituacao" method="POST" class="form-validate-jquery">
											<div class="form-group">

												<div class="card-header header-elements-inline" style="margin-left: -10px;">
													<h5 class="text-uppercase font-weight-bold">Dados Pessoais do paciente</h5>
												</div>

												<div class="col-lg-12 mb-4 row">
													<!-- titulos -->
													<div class="col-lg-6">
														<label>Nome <span class="text-danger">*</span></label>
													</div>
													<div class="col-lg-6">
														<label>Nome Social</label>
													</div>

													<!-- campos -->
													<div class="col-lg-6">
														<input id="nomeNew" name="nomeNew" type="text" class="form-control" placeholder="Nome completo">
													</div>
													<div class="col-lg-6">
														<input id="nomeSocialNew" name="nomeSocialNew" type="text" class="form-control" placeholder="Nome Social">
													</div>
												</div>

												<div class="col-lg-12 mb-4 row">
													<!-- titulos -->
													<div class="col-lg-4">
														<label>CPF <span class="text-danger">*</span></label>
													</div>
													<div class="col-lg-4">
														<label>CNS</label>
													</div>
													<div class="col-lg-4">
														<label>RG</label>
													</div>

													<!-- campos -->
													<div class="col-lg-4">
														<input id="cpfNew" name="cpfNew" type="text" class="form-control" placeholder="CPF" data-mask="999.999.999-99" required>
													</div>
													<div class="col-lg-4">
														<input id="cnsNew" name="cnsNew" type="text" class="form-control" placeholder="Cartão do SUS">
													</div>
													<div class="col-lg-4">
														<input id="rgNew" name="rgNew" type="text" class="form-control" placeholder="RG" data-mask="99.999.999-99">
													</div>
												</div>

												<div class="col-lg-12 mb-4 row">
													<!-- titulos -->
													<div class="col-lg-3">
														<label>Emissor</label>
													</div>
													<div class="col-lg-2">
														<label>UF</label>
													</div>
													<div class="col-lg-3">
														<label>Sexo</label>
													</div>
													<div class="col-lg-4">
														<label>Data de Nascimento</label>
													</div>

													<!-- campos -->
													<div class="col-lg-3">
														<input id="emissorNew" name="emissorNew" type="text" class="form-control" placeholder="Orgão Emissor">
													</div>
													<div class="col-lg-2">
														<select id="ufNew" name="ufNew" class="form-control form-control-select2" placeholder="UF">
															<option value="">Selecione</option>
															<option value="AC">AC</option>
															<option value="AL">AL</option>
															<option value="AP">AP</option>
															<option value="AM">AM</option>
															<option value="BA">BA</option>
															<option value="CE">CE</option>
															<option value="DF">DF</option>
															<option value="ES">ES</option>
															<option value="GO">GO</option>
															<option value="MA">MA</option>
															<option value="MT">MT</option>
															<option value="MS">MS</option>
															<option value="MG">MG</option>
															<option value="PA">PA</option>
															<option value="PB">PB</option>
															<option value="PR">PR</option>
															<option value="PE">PE</option>
															<option value="PI">PI</option>
															<option value="RJ">RJ</option>
															<option value="RN">RN</option>
															<option value="RS">RS</option>
															<option value="RO">RO</option>
															<option value="RR">RR</option>
															<option value="SC">SC</option>
															<option value="SP">SP</option>
															<option value="SE">SE</option>
															<option value="TO">TO</option>
														</select>
													</div>
													<div class="col-lg-3">
														<select id="sexoNew" name="sexoNew" class="form-control form-control-select2">
															<option value="" selected>selecionar</option>
															<option value="M">Masculino</option>
															<option value="F">Feminino</option>
														</select>
													</div>
													<div class="col-lg-4">
														<input id="nascimentoNew" name="nascimentoNew" type="date" class="form-control" placeholder="dd/mm/aaaa">
													</div>
												</div>

												<div class="col-lg-12 mb-4 row">
													<!-- titulos -->
													<div class="col-lg-6">
														<label>Nome do Pai</label>
													</div>
													<div class="col-lg-6">
														<label>Nome da Mãe</label>
													</div>

													<!-- campos -->
													<div class="col-lg-6">
														<input id="nomePaiNew" name="nomePaiNew" type="text" class="form-control" placeholder="Nome do Pai">
													</div>
													<div class="col-lg-6">
														<input id="nomeMaeNew" name="nomeMaeNew" type="text" class="form-control" placeholder="Nome da Mãe">
													</div>
												</div>

												<div class="col-lg-12 mb-4 row">
													<!-- titulos -->
													<div class="col-lg-3">
														<label>Raça/Cor</label>
													</div>
													<div class="col-lg-3">
														<label>Estado Civil</label>
													</div>
													<div class="col-lg-3">
														<label>Naturalidade</label>
													</div>
													<div class="col-lg-3">
														<label>Profissão</label>
													</div>

													<!-- campos -->
													<div class="col-lg-3">
														<select id="racaCorNew" name="racaCorNew" class="form-control form-control-select2">
															<option value="#">Selecione</option>
															<option value="Branca">Branca</option>
															<option value="Preta">Preta</option>
															<option value="Parda">Parda</option>
															<option value="Amarela">Amarela</option>
															<option value="Indígena">Indígena</option>
														</select>
													</div>
													<div class="col-lg-3">
														<select id="estadoCivilNew" name="estadoCivilNew" class="form-control form-control-select2">
															<option value="#">Selecione</option>
															<option value="ST">Solteiro</option>
															<option value="CS">Casado</option>
															<option value="SP">Separado</option>
															<option value="DV">Divorciado</option>
															<option value="VI">Viúvo</option>
														</select>
													</div>
													<div class="col-lg-3">
														<input id="naturalidadeNew" name="naturalidadeNew" type="text" class="form-control" placeholder="Naturalidade">
													</div>
													<div class="col-lg-3">
														<input id="profissaoNew" name="profissaoNew" type="text" class="form-control" placeholder="Profissão" required>
													</div>
												</div>

												<div class="col-lg-12 my-3 text-black-50">
													<h5 class="mb-0 font-weight-semibold">Endereço do Paciente</h5>
												</div>

												<div class="col-lg-12 mb-4 row">
													<!-- titulos -->
													<div class="col-lg-3">
														<label>CEP</label>
													</div>
													<div class="col-lg-4">
														<label>Endereço</label>
													</div>
													<div class="col-lg-2">
														<label>Nº</label>
													</div>
													<div class="col-lg-3">
														<label>Complemento</label>
													</div>

													<!-- campos -->
													<div class="col-lg-3">
														<input id="cepNew" name="cepNew" type="text" class="form-control" placeholder="CEP">
													</div>
													<div class="col-lg-4">
														<input id="enderecoNew" name="enderecoNew" type="text" class="form-control" placeholder="EX.: Rua, Av">
													</div>
													<div class="col-lg-2">
														<input id="numeroNew" name="numeroNew" type="text" class="form-control" placeholder="Número">
													</div>
													<div class="col-lg-3">
														<input id="complementoNew" name="complementoNew" type="text" class="form-control" placeholder="Complemento">
													</div>
												</div>

												<div class="col-lg-12 mb-4 row">
													<!-- titulos -->
													<div class="col-lg-4">
														<label>Bairro</label>
													</div>
													<div class="col-lg-4">
														<label>Cidade</label>
													</div>
													<div class="col-lg-4">
														<label>Estado</label>
													</div>

													<!-- campos -->
													<div class="col-lg-4">
														<input id="bairroNew" name="bairroNew" type="text" class="form-control" placeholder="Bairro">
													</div>
													<div class="col-lg-4">
														<input id="cidadeNew" name="cidadeNew" type="text" class="form-control" placeholder="Cidade">
													</div>
													<div class="col-lg-4">
														<select id="estadoNew" name="estadoNew" class="form-control form-control-select2" placeholder="Estado">
															<option value="#">Selecione um estado</option>
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
															<option value="ES">Estrangeiro</option>	
														</select>
													</div>
												</div>

												<div class="col-lg-12 my-3 text-black-50">
													<h5 class="mb-0 font-weight-semibold">Contato</h5>
												</div>

												<div class="col-lg-12 mb-4 row">
													<!-- titulos -->
													<div class="col-lg-4">
														<label>Nome</label>
													</div>
													<div class="col-lg-2">
														<label>Telefone</label>
													</div>
													<div class="col-lg-2">
														<label>Celular</label>
													</div>
													<div class="col-lg-4">
														<label>E-mail</label>
													</div>

													<!-- campos -->
													<div class="col-lg-4">
														<input id="contatoNew" name="contatoNew" type="text" class="form-control" placeholder="Contato">
													</div>
													<div class="col-lg-2">
														<input id="telefoneNew" name="telefoneNew" type="text" class="form-control" placeholder="Telefone" data-mask="(99) 9999-9999">
													</div>
													<div class="col-lg-2">
														<input id="celularNew" name="celularNew" type="text" class="form-control" placeholder="Celular" data-mask="(99) 99999-9999">
													</div>
													<div class="col-lg-4">
														<input id="emailNew" name="emailNew" type="text" class="form-control" placeholder="E-mail">
													</div>
												</div>

												<div class="col-lg-12 mb-4 row">
													<!-- titulos -->
													<div class="col-lg-12">
														<label>Observação</label>
													</div>

													<!-- campos -->
													<div class="col-lg-12">
														<textarea id="observacaoNew" name="observacaoNew" class="form-control" placeholder="Observações"></textarea>
													</div>
												</div>
											</div>
										</form>
									</div>
								</div>
								<div class="text-left m-2">
									<button id="salvarPacienteModal" class="btn btn-success" role="button">Confirmar</button>
									<button id="voltarPacienteModal" class="btn btn-link" role="button">Voltar</button>
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
	<?php include_once("alerta.php"); ?>

</body>

</html>
