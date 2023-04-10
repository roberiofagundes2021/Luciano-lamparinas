<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Novo Veículo';

include('global_assets/php/conexao.php');

if(isset($_POST['inputNome'])){

	try{
		$sql = "INSERT INTO Veiculo (VeicuNome, VeicuPlaca, VeicuRenavam, VeicuChassi, VeicuUnidade, VeicuSetor, VeicuStatus, VeicuUsuarioAtualizador)
				    VALUES (:sNome, :sPlaca, :sRenavam, :sChassi, :iUnidade, :iSetor, :bStatus, :iUsuarioAtualizador)";
		$result = $conn->prepare($sql);
				
		$result->execute(array(
            ':sNome' => $_POST['inputNome'],
            ':sPlaca' => $_POST['inputPlaca'],
            ':sRenavam' => $_POST['inputRenavam'],
            ':sChassi' => $_POST['inputChassi'],
            ':iUnidade' => $_SESSION['UnidadeId'],
            ':iSetor' => $_POST['cmbSetor'],
            ':bStatus' => 1,
            ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
            ));
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Veículo incluído!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao incluir Veículo!!!";
		$_SESSION['msg']['tipo'] = "error";	
		
		echo 'Error: ' . $e->getMessage();die;
	}
	
	irpara("veiculo.php");
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Lamparinas | Veículo</title>

  <?php include_once("head.php"); ?>

  <!-- Theme JS files -->
  <script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>

  <script src="global_assets/js/demo_pages/form_layouts.js"></script>
  <script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>

  <script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
  <script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
  <script src="global_assets/js/demo_pages/form_validation.js"></script>
  <!-- /theme JS files -->

  <script type="text/javascript">

    $(document).ready(function() {

      
			//Limpa o campo Nome quando for digitado só espaços em branco
			$("#inputNome").on('blur', function(e){
				
				var inputNome = $('#inputNome').val();

				inputNome = inputNome.trim();
				
				if (inputNome.length == 0){
					$('#inputNome').val('');
					//$("#formVeiculo").submit(); //Isso aqui é para submeter o formulário, validando os campos obrigatórios novamente
				}	
			});

    //Valida Registro Duplicado
    $('#enviar').on('click', function(e) {

      e.preventDefault();

      var inputNome = $('#inputNome').val(); 
      var cmbSetor = $('#cmbSetor').val(); 
      var inputPlaca = $('#inputPlaca').val();
     
      if (inputNome.trim() == "" || cmbSetor == ""){
        $("#formVeiculo").submit();
      }
     
      inputPlaca = inputPlaca.trim();
      
      //Esse ajax está sendo usado para verificar no banco se o registro já existe
      $.ajax({
        type: "POST",
        url: "veiculoValida.php",
        data: ('nomeNovo=' + inputPlaca),
        success: function(resposta) {

          if (resposta == 1) {
            alerta('Atenção', 'Essa placa já existe!', 'error');
            return false;
          }

          $("#formVeiculo").submit();
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

          <form name="formVeiculo" id="formVeiculo" method="post" class="form-validate-jquery" action="veiculoNovo.php">
            <div class="card-header header-elements-inline">
              <h5 class="text-uppercase font-weight-bold">Cadastrar Novo Veículo</h5>
            </div>

            <div class="card-body">
              <div class="row">
                <div class="col-lg-4">
                  <div class="form-group">
                    <label for="inputNome">Nome do Veículo <span class='text-danger'>*</span></label>
                    <input type="text" id="inputNome" name="inputNome" class="form-control" placeholder="Veículo" required autofocus>
                  </div>
                </div>

                <div class="col-lg-2">
                  <label for="inputPlaca">Placa do Veículo </label>
                  <input type="text" id="inputPlaca" name="inputPlaca" class="form-control" placeholder="Placa">
                </div>
                <div class="col-lg-3">
                  <label for="inputRenavam">Renavam do Veículo </label>
                  <input type="text" id="inputRenavam" name="inputRenavam" class="form-control" placeholder="Renavam">
                </div>
                <div class="col-lg-3">
                  <label for="inputChassi">Chassi do Veículo </label>
                  <input type="text" id="inputChassi" name="inputChassi" class="form-control" placeholder="Chassi">
                </div>
              </div>
              
              <h5 class="mb-0 font-weight-semibold">Lotação</h5>
              <br>
          
              <div class="row">
                <div class="col-lg-4">
                  <div class="form-group">
                    <label for="cmbSetor">Setor<span class="text-danger"> *</span></label>
                    <select name="cmbSetor" id="cmbSetor" class="form-control form-control-select2" required>
                      <option value="">Informe um setor</option>
                      <?php
													$sql = "SELECT SetorId, SetorNome
															FROM Setor
															JOIN Situacao on SituaId = SetorStatus															     
															WHERE SituaChave = 'ATIVO' and SetorUnidade = ".$_SESSION['UnidadeId']."
															ORDER BY SetorNome ASC";
													$result = $conn->query($sql);
													$rowSetor = $result->fetchAll(PDO::FETCH_ASSOC);

													foreach ($rowSetor as $item) {
														print('<option value="' . $item['SetorId'] . '">' . $item['SetorNome'] . '</option>');
													}
													?>
                    </select>
                  </div>
                </div>									
              </div>
                
              <div class="row" style="margin-top: 10px;">
                <div class="col-lg-12">
                  <div class="form-group">
                    <button class="btn btn-lg btn-principal" id="enviar">Incluir</button>
                    <a href="veiculo.php" class="btn btn-basic" role="button">Cancelar</a>
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