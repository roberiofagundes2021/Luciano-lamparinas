<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Editar Setor';

include('global_assets/php/conexao.php');

if(isset($_POST['inputSetorId'])){
	
	$iSetor = $_POST['inputSetorId'];
        	
	try{
		
		$sql = "SELECT SetorId, SetorNome, SetorUnidade
				FROM Setor
				WHERE SetorId = $iSetor ";
		$result = $conn->query("$sql");
		$row = $result->fetch(PDO::FETCH_ASSOC);
		
	} catch(PDOException $e) {
		echo 'Error: ' . $e->getMessage();
	}
	
	$_SESSION['msg'] = array();
} else {  //Esse else foi criado para se caso o usuário der um REFRESH na página. Nesse caso não terá POST e campos não reconhecerão o $row da consulta acima (daí ele deve ser redirecionado) e se quiser continuar editando terá que clicar no ícone da Grid novamente

	irpara("setor.php");
}

if(isset($_POST['inputNome'])){
	
	try{
		
		$sql = "UPDATE Setor SET SetorNome = :sNome, SetorUnidade = :iUnidade, SetorUsuarioAtualizador = :iUsuarioAtualizador
				WHERE SetorId = :iSetor";
		$result = $conn->prepare($sql);
				
		$result->execute(array(
						':sNome' => $_POST['inputNome'],
						':iUnidade' => $_POST['cmbUnidade'],
						':iUsuarioAtualizador' => $_SESSION['UsuarId'],
						':iSetor' => $_POST['inputSetorId']
						));

		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Setor alterado!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao alterar setor!!!";
		$_SESSION['msg']['tipo'] = "error";		
		
		echo 'Error: ' . $e->getMessage();
	}
	
	irpara("setor.php");
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Lamparinas | Setor</title>

  <?php include_once("head.php"); ?>

  <!-- Theme JS files -->
  <script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
  <script src="global_assets/js/demo_pages/form_layouts.js"></script>
  <script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>
  <!-- /theme JS files -->
  <!-- Validação -->
  <script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
  <script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
  <script src="global_assets/js/demo_pages/form_validation.js"></script>

  <script type="text/javascript">
  $(document).ready(function() {

    //Valida Registro Duplicado
    $('#enviar').on('click', function(e) {

      e.preventDefault();

      var inputNomeNovo = $('#inputNome').val();
      var inputNomeVelho = $('#inputSetorNome').val();
      var cmbUnidade = $('#cmbUnidade').val();

      //remove os espaços desnecessários antes e depois
      inputNomeNovo = inputNomeNovo.trim();

      //Esse ajax está sendo usado para verificar no banco se o registro já existe
      $.ajax({
        type: "POST",
        url: "setorValida.php",
        data: ('nomeNovo=' + inputNomeNovo + '&nomeVelho=' + inputNomeVelho + '&unidade=' + cmbUnidade),
        success: function(resposta) {

          if (resposta == 1) {
            alerta('Atenção', 'Esse registro já existe!', 'error');
            return false;
          }

          $("#formSetor").submit();
        }
      })

    })
  })
  </script>

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
        <div class="card">

          <form name="formSetor" id="formSetor" method="post" class="form-validate-jquery">
            <div class="card-header header-elements-inline">
              <h5 class="text-uppercase font-weight-bold">Editar Setor "<?php echo $row['SetorNome']; ?>"</h5>
            </div>

            <input type="hidden" id="inputSetorId" name="inputSetorId" value="<?php echo $row['SetorId']; ?>">
            <input type="hidden" id="inputSetorNome" name="inputSetorNome" value="<?php echo $row['SetorNome']; ?>">

            <div class="card-body">
              <div class="row">
                <div class="col-lg-6">
                  <div class="form-group">
                    <label for="inputNome">Nome do Setor <span class='text-danger'>*</span></label>
                    <input type="text" id="inputNome" name="inputNome" class="form-control" placeholder="Setor" value="<?php echo $row['SetorNome']; ?>" required autofocus>
                  </div>
                </div>
                <div class="col-lg-6">
                  <label for="cmbUnidade">Unidade <span class='text-danger'>*</span></label>
                  <select id="cmbUnidade" name="cmbUnidade" class="form-control form-control-select2" required>
                    <option value="">Selecione</option>
                    <?php 
											$sql = "SELECT UnidaId, UnidaNome
													FROM Unidade
													JOIN Situacao on SituaId = UnidaStatus
													WHERE SituaChave = 'ATIVO' and UnidaEmpresa = ".$_SESSION['EmpresaId']."
													ORDER BY UnidaNome ASC";
											$result = $conn->query($sql);
											$rowUnidade = $result->fetchAll(PDO::FETCH_ASSOC);
											
											foreach ($rowUnidade as $item){
												$seleciona = $item['UnidaId'] == $row['SetorUnidade'] ? "selected" : "";
												print('<option value="'.$item['UnidaId'].'" '. $seleciona .'>'.$item['UnidaNome'].'</option>');
											}
										
										?>
                  </select>
                </div>
              </div>

              <div class="row" style="margin-top: 10px;">
                <div class="col-lg-12">
                  <div class="form-group">
                    <button class="btn btn-lg btn-principal" id="enviar">Alterar</button>
                    <a href="setor.php" class="btn btn-basic" role="button">Cancelar</a>
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