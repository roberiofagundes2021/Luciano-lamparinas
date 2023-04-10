<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Novo Processo';

$iUnidade = $_SESSION['UnidadeId'];

include('global_assets/php/conexao.php');

$sql = "SELECT UnidaId,UnidaNome
	FROM Unidade
	WHERE UnidaId = $iUnidade";
$result = $conn->query($sql);
$unidadeConsulta = $result->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Novo Processo</title>

	<?php include_once("head.php"); ?>

	<script src="global_assets/js/plugins/loaders/blockui.min.js"></script>
	<script src="global_assets/js/plugins/ui/ripple.min.js"></script>
	<script src="global_assets/js/plugins/ui/moment/moment.min.js"></script>
	<script src="global_assets/js/plugins/pickers/daterangepicker.js"></script>
	<script src="global_assets/js/plugins/pickers/anytime.min.js"></script>
	<script src="global_assets/js/plugins/pickers/pickadate/picker.js"></script>
	<script src="global_assets/js/plugins/pickers/pickadate/picker.date.js"></script>
	<script src="global_assets/js/plugins/pickers/pickadate/picker.time.js"></script>
	<script src="global_assets/js/plugins/pickers/pickadate/legacy.js"></script>
	<script src="global_assets/js/plugins/notifications/jgrowl.min.js"></script>

	<!-- Theme JS files -->
	<script src="global_assets/js/demo_pages/form_select2.js"></script>
	<script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>
    <script src="global_assets/js/plugins/tables/datatables/datatables.min.js"></script>
    <script src="global_assets/js/plugins/tables/datatables/extensions/responsive.min.js"></script>
    <script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
	<script src="global_assets/js/plugins/editors/summernote/summernote.min.js"></script>

	<!-- Plugin para corrigir a ordenação por data. Caso a URL dê problema algum dia, salvei esses 2 arquivos na pasta global_assets/js/lamparinas -->
	<script type="text/javascript" language="javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.8.4/moment.min.js"></script>
	<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/plug-ins/1.10.10/sorting/datetime-moment.js"></script>	

	<!-- Modal -->
	<script src="global_assets/js/plugins/notifications/bootbox.min.js"></script>
    
    <!-- Validação -->
	<script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
	<script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
	<script src="global_assets/js/demo_pages/form_validation.js"></script>
	
	<script type="text/javascript" >
		$(document).ready(function(){
			$('#formProcesso').submit(function(e){
				e.preventDefault()
				$.ajax({
					type: 'POST',
					url: 'filtraProcessoLicitatorio.php',
					dataType: 'json',
					data: {
						'tipoRequest':'SALVARPROCESSO',
						'numero': $('#numero').val(),
						'data': $('#data').val(),
						'especie': $('#especie').val(),
						'urgente': $('#urgente').val(),
						'tipo': $('#tipo').val(),
						'categoria': $('#categoria').val(),
						'status': $('#status').val(),
						'situacao': $('#situacao').val(),
						'descricao': $('#descricao').val(),
						'participantes': $('#participantes').val(),
						'responsavel': $('#responsavel').val(),
						'telefone': $('#telefone').val(),
						'email': $('#email').val()
					},
					success: function(response) {
						alerta(response.titulo,response.menssagem,response.status)
						if(response.status == 'success'){
							window.location.href = 'processoLicitatorio.php'
						}
					}
				})
			})
			getCmb()
		});
		function getCmb(){
			$('#tipo').empty()
			$('#categoria').empty()
			$('#status').empty()
			$('#situacao').empty()
			$('#participantes').empty()
			
			$('#tipo').append(`<option selected value=''>Carregando...</option>`)
			$('#categoria').append(`<option selected value=''>Carregando...</option>`)
			$('#status').append(`<option selected value=''>Carregando...</option>`)
			$('#situacao').append(`<option selected value=''>Carregando...</option>`)
			$('#participantes').append(`<option selected value=''>Carregando...</option>`)
			$.ajax({
				type: 'POST',
				url: 'filtraProcessoLicitatorio.php',
				dataType: 'json',
				data: {
					'tipoRequest':'GETCMB'
				},
				success: function(response) {
					$('#tipo').empty()
					$('#categoria').empty()
					$('#status').empty()
					$('#situacao').empty()
					$('#participantes').empty()

					$('#tipo').append(`<option selected value=''>Selecione</option>`)
					$('#categoria').append(`<option selected value=''>Selecione</option>`)
					$('#status').append(`<option selected value=''>Selecione</option>`)
					$('#situacao').append(`<option selected value=''>Selecione</option>`)

					response.tipo.forEach(function(item){
						$('#tipo').append(`<option value='${item.id}'>${item.nome}</option>`)
					})
					response.categoria.forEach(function(item){
						$('#categoria').append(`<option value='${item.id}'>${item.nome}</option>`)
					})
					response.status.forEach(function(item){
						$('#status').append(`<option value='${item.id}'>${item.nome}</option>`)
					})
					response.situacao.forEach(function(item){
						$('#situacao').append(`<option value='${item.id}'>${item.nome}</option>`)
					})
					response.participantes.forEach(function(item){
						$('#participantes').append(`<option value='${item.id}'>${item.nome}</option>`)
					})
				}
			})
		}
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
				<div class="row">
					<div class="col-lg-12">
						<div class="card">
							<!-- dados do agendamento -->
							<div id="agendamento" class="formDados card-body" style="display: block; margin-top:-10px;" >
								<div class="card-header header-elements-inline" style="margin-left:-10px;">
									<h5 class='text-uppercase font-weight-bold'>CADASTRAR NOVO PROCESSO</h5>
								</div>

								<input type="hidden" id="tipoRequest" name="tipoRequest" value="NOVO" />

								<form name="formProcesso" id="formProcesso" method="post" class="form-validate-jquery">
									<!-- linha 1 -->
									<div class="col-lg-12 mb-4 row form-group">
										<!-- titulos -->
										<div class="col-lg-3">
											<label>Nº Processo</label>
										</div>
										<div class="col-lg-2">
											<label>Data de Autuação</label>
										</div>
										<div class="col-lg-2">
											<label>Espécie <span class="text-danger">*</span></label>
										</div>
										<div class="col-lg-2">
											<label>Urgente <span class="text-danger">*</span></label>
										</div>
										<div class="col-lg-3">
											<label>Tipo de Processo <span class="text-danger">*</span></label>
										</div>

										<!-- campos -->
										<div class="col-lg-3">
											<input id="numero" type="text" class="form-control">
										</div>
										<div class="col-lg-2">
											<input id="data" name="data" type="date" class="form-control">
										</div>
										<div class="col-lg-2">
											<select id="especie" class="select-search" required>
												<option value=''>Selecione</option>
												<option value='E'>ELETRÔNICO</option>
												<option value='F'>FÍSICO</option>
											</select>
										</div>
										<div class="col-lg-2">
											<select id="urgente" class="select-search" required>
												<option value=''>Selecione</option>
												<option value='S'>SIM</option>
												<option value='N'>NÃO</option>
											</select>
										</div>
										<div class="col-lg-3">
											<select id="tipo" class="select-search" required>
												<option value=''>Selecione</option>
											</select>
										</div>
									</div>

									<!-- linha 2 -->
									<div class="col-lg-12 mb-4 row form-group">
										<!-- titulos -->
										<div class="col-lg-4">
											<label>Categoria <span class="text-danger">*</span></label>
										</div>
										<div class="col-lg-4">
											<label>Status <span class="text-danger">*</span></label>
										</div>
										<div class="col-lg-4">
											<label>Situação</label>
										</div>

										<!-- campos -->
										<div class="col-lg-4">
											<select id="categoria" class="select-search" required>
												<option value=''>Selecione</option>
											</select>
										</div>
										<div class="col-lg-4">
											<select id="status" class="select-search" required>
												<option value=''>Selecione</option>
											</select>
										</div>
										<div class="col-lg-4">
											<select id="situacao" class="select-search">
												<option value=''>Selecione</option>
											</select>
										</div>
									</div>

									<!-- linha 3 -->
									<div class="col-lg-12">
										<div class="col-lg-12">
											<label>Descrição <span class="text-danger">*</span></label>
										</div>
										<div class="col-lg-12">
											<textarea id="descricao" class="form-control" placeholder="Descrição" required></textarea>
										</div>
									</div>

									<!-- linha 4 -->
									<div class="col-lg-12 my-3 text-black-50">
										<h5 class="mb-0 font-weight-semibold">Dados do interessado</h5>
									</div>

									<!-- linha 5 -->
									<div class="col-lg-12 mb-4 row form-group">
										<!-- titulos -->
										<div class="col-lg-5">
											<label>Unidade Gestora</label>
										</div>
										<div class="col-lg-7">
											<label>Unidades Participantes</label>
										</div>

										<!-- campos -->
										<div class="col-lg-5">
											<?php echo "<input type='text' class='form-control' readonly value='$unidadeConsulta[UnidaNome]'>"; ?>
										</div>
										<div class="col-lg-7">
											<select id="participantes" class="form-control select" multiple="multiple" data-fouc>
												<option value=''>Selecione</option>
											</select>
										</div>
									</div>

									<!-- linha 6 -->
									<div class="col-lg-12 mb-4 row form-group">
										<!-- titulos -->
										<div class="col-lg-4">
											<label>Nome do Responsável</label>
										</div>
										<div class="col-lg-4">
											<label>Telefone</label>
										</div>
										<div class="col-lg-4">
											<label>E-mail</label>
										</div>

										<!-- campos -->
										<div class="col-lg-4">
											<input id="responsavel" type="text" class="form-control">
										</div>
										<div class="col-lg-4">
											<input id="telefone" type="text" class="form-control">
										</div>
										<div class="col-lg-4">
											<input id="email" type="email" class="form-control">
										</div>
									</div>

									<!-- botões -->
									<div class="col-lg-12 mt-4 mb-2 row">
										<button class="btn btn-lg btn-principal" id="salvarProcesso">Salvar</button>
										<a href="processoLicitatorio.php" class="btn btn-lg" id="cancelar">Cancelar</a>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php include_once("footer.php"); ?>
		</div>
	</div>
	<?php include_once("alerta.php"); ?>
</body>

</html>
