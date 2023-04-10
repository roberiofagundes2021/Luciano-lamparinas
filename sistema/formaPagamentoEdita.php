<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Editar Forma de Pagamento';

include('global_assets/php/conexao.php');

if(isset($_POST['inputFormaPagamentoId'])){
	
	$iFormaPagamento = $_POST['inputFormaPagamentoId'];
		
	$sql = "SELECT FrPagId, FrPagNome
			FROM FormaPagamento
			WHERE FrPagId = $iFormaPagamento ";
	$result = $conn->query($sql);
	$row = $result->fetch(PDO::FETCH_ASSOC);
	
	$_SESSION['msg'] = array();
} else {  //Esse else foi criado para se caso o usuário der um REFRESH na página. Nesse caso não terá POST e campos não reconhecerão o $row da consulta acima (daí ele deve ser redirecionado) e se quiser continuar editando terá que clicar no ícone da Grid novamente

	irpara("formaPagamento.php");
}

if(isset($_POST['inputNome'])){
	
	try{
		
		$sql = "UPDATE FormaPagamento SET FrPagNome = :sNome, FrPagUsuarioAtualizador = :iUsuarioAtualizador
				WHERE FrPagId = :iFormaPagamento";
		$result = $conn->prepare($sql);
				
		$result->execute(array(
						':sNome' => $_POST['inputNome'],
						':iUsuarioAtualizador' => $_SESSION['UsuarId'],
						':iFormaPagamento' => $_POST['inputFormaPagamentoId']
						));

		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Forma de Pagamento alterada!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao alterar Forma de Pagamento!!!";
		$_SESSION['msg']['tipo'] = "error";		
		
		echo 'Error: ' . $e->getMessage();
	}
	
	irpara("formaPagamento.php");
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Forma de Pagamento</title>

	<?php include_once("head.php"); ?>

	<!-- Validação -->
	<script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
	<script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
	<script src="global_assets/js/demo_pages/form_validation.js"></script>
	
	<script type="text/javascript" >

        $(document).ready(function() {
			
			//Valida Registro Duplicado
			$('#enviar').on('click', function(e){

				e.preventDefault();
				
				var inputNomeNovo  = $('#inputNome').val();
				var inputNomeVelho = $('#inputFormaPagamentoNome').val();
				
				//remove os espaços desnecessários antes e depois
				inputNomeNovo = inputNomeNovo.trim();
				
				//Esse ajax está sendo usado para verificar no banco se o registro já existe
				
				$.ajax({
					type: "POST",
				    url: "formaPagamentoValida.php",
					data: ('nomeNovo='+inputNomeNovo+'&nomeVelho='+inputNomeVelho),
					success: function(resposta){
						
						if(resposta == 1){
							alerta('Atenção','Esse registro já existe!','error');
							return false;								
						}
						
						  $( "#formFormaPagamento" ).submit();
					}
				})
				
			})
		})
	
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
					
					<form name="formFormaPagamento" id="formFormaPagamento" method="post" class="form-validate-jquery">
						<div class="card-header header-elements-inline">
							<h5 class="text-uppercase font-weight-bold">Editar Forma de Pagamento "<?php echo $row['FrPagNome']; ?>"</h5>
						</div>
						<input type="hidden" id="inputFormaPagamentoId" name="inputFormaPagamentoId" value="<?php echo $row['FrPagId']; ?>" >
						<input type="hidden" id="inputFormaPagamentoNome" name="inputFormaPagamentoNome" value="<?php echo $row['FrPagNome']; ?>" >
						<div class="card-body">								
							<div class="row">
								<div class="col-lg-12">
									<div class="form-group">
										<label for="inputNome">Forma de Pagamento<span class="text-danger"> *</span></label>
										<input type="text" id="inputNome" name="inputNome" class="form-control" placeholder="Forma de Pagamento" value="<?php echo $row['FrPagNome']; ?>" required autofocus>
									</div>
								</div>
							</div>
								
							<div class="row" style="margin-top: 10px;">
								<div class="col-lg-12">								
									<div class="form-group">
										<button class="btn btn-lg btn-principal" id="enviar">Alterar</button>
										<a href="formaPagamento.php" class="btn btn-basic" role="button">Cancelar</a>
									</div>
								</div>
							</div>
						</form>								

					</div>
					<!-- /card-body -->
					
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
