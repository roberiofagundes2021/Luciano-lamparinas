<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Editar Conta';

include('global_assets/php/conexao.php');


$sql = "SELECT CnBanId, CnBanNome, CnBanBanco, CnBanAgencia, CnBanConta
	    FROM ContaBanco
	    WHERE CnBanUnidade = ".$_SESSION['UnidadeId']." and CnBanId = ".$_POST['inputContaBancoId']."";
$result = $conn->query($sql);
$row = $result->fetch(PDO::FETCH_ASSOC);

if(isset($_POST['inputNome'])){

	try{

        $sql = "SELECT SituaId
		        FROM Situacao
		        WHERE SituaChave = 'ATIVO'";
        $result = $conn->query($sql);
        $situacao = $result->fetch(PDO::FETCH_ASSOC);
		
		$sql = "UPDATE ContaBanco SET CnBanNome = :sNome, CnBanBanco = :iBanco, CnBanAgencia = :sAgencia, CnBanConta = :sConta
				WHERE CnBanId = ".$_POST['inputContaBancoId']."";
		$result = $conn->prepare($sql);
				
		$result->execute(array(
						':sNome' => $_POST['inputNome'],
                        ':iBanco' => $_POST['cmbBanco'],
                        ':sAgencia' => $_POST['inputAgencia'],
                        ':sConta' => $_POST['inputConta']
						));
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Conta alterada!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao alterar Conta!!!";
		$_SESSION['msg']['tipo'] = "error";	
		
		echo 'Error: ' . $e->getMessage();die;
	}
	
	irpara("contaBanco.php");
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Lamparinas | Conta/Banco</title>

    <?php include_once("head.php"); ?>

    <!-- Theme JS files -->
    <script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
    <script src="global_assets/js/demo_pages/form_select2.js"></script>

    <!--<script src="http://malsup.github.com/jquery.form.js"></script>-->
    <script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
    <script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
    <script src="global_assets/js/demo_pages/form_validation.js"></script>
    <!-- /theme JS files -->

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

                    <form name="formSubCategoria" id="formSubCategoria" method="post" class="form-validate-jquery">
                        <input type="hidden" name="inputContaBancoId" value="<?php echo $row['CnBanId']?>">
                        <div class="card-header header-elements-inline">
                            <h5 class="text-uppercase font-weight-bold">Cadastrar Nova Conta</h5>
                        </div>
                        <div class="card-body">

                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label for="inputNome">Nome<span class="text-danger"> *</span></label>
                                        <input type="text" id="inputNome" name="inputNome" value="<?php echo $row['CnBanNome']?>" class="form-control"
                                            placeholder="Conta/Banco" required autofocus>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-4">
                                    <label for="cmbBanco">Banco</label>
                                    <select id="cmbBanco" name="cmbBanco" class="form-control select-search">
                                        <option value="">Selecione</option>
                                        <?php 
											$sql = "SELECT BancoId, BancoNome
													FROM Banco
                                                    JOIN Situacao on SituaId = BancoStatus
													WHERE SituaChave = 'ATIVO'
													ORDER BY BancoNome ASC";
											$result = $conn->query($sql);
											$rowBanco = $result->fetchAll(PDO::FETCH_ASSOC);
											
											foreach ($rowBanco as $item){
                                                if($item['BancoId'] == $row['CnBanBanco']){
                                                    print('<option value="'.$item['BancoId'].'" selected>'.$item['BancoNome'].'</option>');
                                                } else {
                                                    print('<option value="'.$item['BancoId'].'">'.$item['BancoNome'].'</option>');
                                                }
											}
										
										?>
                                    </select>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="inputAgencia">Agência</label>
                                        <input type="text" id="inputAgencia" name="inputAgencia" value="<?php echo $row['CnBanAgencia']?>" class="form-control"
                                            placeholder="Agência" autofocus>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="inputConta">Conta Bancária</label>
                                        <input type="text" id="inputConta" name="inputConta" value="<?php echo $row['CnBanConta']?>" class="form-control"
                                            placeholder="Conta Bancária" autofocus>
                                    </div>
                                </div>
                            </div>

                            <div class="row" style="margin-top: 10px;">
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <button class="btn btn-lg btn-principal" id="enviar">Incluir</button>
                                        <a href="contaBanco.php" class="btn btn-basic" role="button">Cancelar</a>
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