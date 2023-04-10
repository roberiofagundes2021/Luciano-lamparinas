<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

$centros = $_POST['centroCustos']?$_POST['centroCustos']:null;

$array = '(';

for($x=0; $x < COUNT($centros); $x++){
    $array .= "".$centros[$x];
    if ($x < (COUNT($centros)-1)) {
        $array .= ',';
    }
}

$array .= ')';

$sqlCentroCusto = "SELECT CnCusId, CnCusCodigo, CnCusNome, CnCusNomePersonalizado, CnCusDetalhamento, CnCusStatus, SituaChave
                FROM  CentroCusto JOIN Situacao on SituaId = CnCusStatus
                WHERE CnCusUnidade = ".$_SESSION['UnidadeId']." and 
                SituaChave = 'ATIVO' and CnCusId in ".$array."
                ORDER BY CnCusNome ASC";

$resultCentroCusto = $conn->query($sqlCentroCusto);
$CentroCustos = $resultCentroCusto->fetchAll(PDO::FETCH_ASSOC);

$count = COUNT($CentroCustos);
//Verifica se já existe esse registro (se existir, retorna true )
if($count){
	echo (json_encode($CentroCustos));
} else{
	echo (0);
}

?>
