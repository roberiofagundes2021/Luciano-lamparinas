<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

$centros = $_POST['centroCustos']?$_POST['centroCustos']:null;
$conta = $_POST['conta'];
$tipo = $_POST['tipo'];

$array = '(';

for($x=0; $x < COUNT($centros); $x++){
    $array .= "".$centros[$x];
    if ($x < (COUNT($centros)-1)) {
        $array .= ',';
    }
}

$array .= ')';

if($tipo == 'DESPESA') {
    $sqlCentroCusto =  "SELECT CAPXCValor, CnCusId, CnCusCodigo, CnCusNome, CnCusNomePersonalizado, CnCusDetalhamento, CnCusStatus, SituaChave
                        FROM  ContasAPagarXCentroCusto 
                        JOIN CentroCusto on CnCusId = CAPXCCentroCusto
                        JOIN Situacao on SituaId = CnCusStatus
                        WHERE CAPXCUnidade = ".$_SESSION['UnidadeId']." and 
                        SituaChave = 'ATIVO' and CAPXCContasAPagar = $conta and CAPXCCentroCusto in ".$array."
                        ORDER BY CnCusNome ASC";
    
    $resultCentroCusto = $conn->query($sqlCentroCusto);
    $CentroCustos = $resultCentroCusto->fetchAll(PDO::FETCH_ASSOC);
    
    $count = COUNT($CentroCustos);
}else {
    $sqlCentroCusto =  "SELECT CARXCValor, CnCusId, CnCusCodigo, CnCusNome, CnCusNomePersonalizado, CnCusDetalhamento, CnCusStatus, SituaChave
                        FROM  ContasAReceberXCentroCusto 
                        JOIN CentroCusto on CnCusId = CARXCCentroCusto
                        JOIN Situacao on SituaId = CnCusStatus
                        WHERE CARXCUnidade = ".$_SESSION['UnidadeId']." and 
                        SituaChave = 'ATIVO' and CARXCContasAReceber = $conta and CARXCCentroCusto in ".$array."
                        ORDER BY CnCusNome ASC";
        
    $resultCentroCusto = $conn->query($sqlCentroCusto);
    $CentroCustos = $resultCentroCusto->fetchAll(PDO::FETCH_ASSOC);
    
    $count = COUNT($CentroCustos);
}

//Verifica se já existe esse registro (se existir, retorna true )
if($count){
    echo (json_encode($CentroCustos));
} else{
    echo (0);
}

?>