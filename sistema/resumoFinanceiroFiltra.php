<?php 
include_once("sessao.php");
include('global_assets/php/conexao.php');

$data = $_POST['date'];

$conta = $_POST['conta'];

//pega o saldo inicial realizado
$sql_saldoInicial    = "select dbo.fnSaldoInicial(".$_SESSION['UnidadeId'].",'".$data."', ".$conta.") as SaldoInicial";
$resultSaldoInicial  = $conn->query($sql_saldoInicial);
$rowSaldoInicial     = $resultSaldoInicial->fetch(PDO::FETCH_ASSOC);
$saldoAnterior = $rowSaldoInicial['SaldoInicial'];

$sql = "SELECT isNull(dbo.fnDebitosDia(".$_SESSION['UnidadeId'].", ".$conta.", '".$data."'), 0.00) as Debito,
               isNull(dbo.fnCreditosDia(".$_SESSION['UnidadeId'].", ".$conta.", '".$data."'), 0.00) as Credito";
$result = $conn->query($sql);
$rowResumo = $result->fetch(PDO::FETCH_ASSOC);

$fCredito = mostraValor($rowResumo['Credito']);
$fDebito = mostraValor($rowResumo['Debito']);

$fSaldo = mostraValor($rowResumo['Credito'] - $rowResumo['Debito']);

$saldoAtual = $saldoAnterior + ($rowResumo['Credito'] - $rowResumo['Debito']);
?>
<div class="form-group">
    <input id="inputSaldoAnterior" name="inputSaldoAnterior" class="form-control" value="<?php echo mostraValor($saldoAnterior); ?>" style="font-size: 30px; text-align: right;" readonly>
    <h3 class="form-text text-right" style="color: #666;">Saldo Anterior</h3>
</div>

<div class="form-group">
    <input id="inputCredito" name="inputCredito" class="form-control" value="<?php echo $fCredito; ?>" style="font-size: 30px; text-align: right;" readonly>
    <h3 class="form-text text-right" style="color: #666;">Crédito</h3>
</div> 

<div class="form-group">
    <input id="inputDebito" name="inputDebito" class="form-control" value="<?php echo $fDebito; ?>" style="font-size: 30px; text-align: right;" readonly>
    <h3 class="form-text text-right" style="color: #666;">Débito</h3>
</div>                

<div class="form-group">
    <input id="inputSaldo" name="inputSaldo" class="form-control" value="<?php echo $fSaldo; ?>" style="font-size: 30px; text-align: right;" readonly>
    <h3 class="form-text text-right" style="color: #666;"><b>Saldo</b> (Crédito - Débito)</h3>
</div>

<div class="form-group">
    <input id="inputSaldoAtual" name="inputSaldoAtual" class="form-control" value="<?php echo mostraValor($saldoAtual); ?>" style="font-size: 30px; text-align: right;" readonly>
    <h3 class="form-text text-right" style="color: #666;">Saldo Atual</h3>
</div>