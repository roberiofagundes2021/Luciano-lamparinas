<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

$sql = "SELECT COUNT(MvAneMovimentacao) Qtde
        FROM   MovimentacaoAnexo	
        WHERE  MvAneUnidade = ". $_SESSION['UnidadeId'] ." AND MvAneMovimentacao = ".$_POST['IMovim']." 
        ";
        
$result = $conn->query($sql);
$row = $result->fetch(PDO::FETCH_ASSOC);
$count = $row['Qtde'];

//Verifica se já existe esse registro (se existir, retorna true )
if($count){
        echo 1;
} else{
	echo 0;
}

?>
