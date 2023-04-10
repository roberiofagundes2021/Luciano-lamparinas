<?php

$sql = "SELECT isNull(dbo.fnDebitosDia(".$_SESSION['UnidadeId'].", 0, convert(date, getdate())), 0.00) as Debito,
               isNull(dbo.fnCreditosDia(".$_SESSION['UnidadeId'].", 0, convert(date, getdate())), 0.00) as Credito";
$result = $conn->query($sql);
$rowResumo = $result->fetch(PDO::FETCH_ASSOC);

$fCredito = mostraValor($rowResumo['Credito']);
$fDebito = mostraValor($rowResumo['Debito']);

$fSaldo = mostraValor($rowResumo['Credito'] - $rowResumo['Debito']);

?>

<script type="text/javascript">
    $(document).ready(function () {
        function buscar(date, conta) {
            $.ajax ({
                    type: 'POST',
                    dataType: 'html',
                    url: 'resumoFinanceiroFiltra.php',
                    beforeSend: function () {
                        //$("#dados").html('<img src="global_assets/images/lamparinas/loader.gif" style="width: 120px">');
                    },
                    data: {
                        date: date,
                        conta: conta
                    },
                    success: function(msg) {
                        $("#dados").html(msg);
                    }
            });
        }

        //--|Aqui é para os dados n se perderem caso o usuário continue no mesmo caso de uso
        $("#cmbCnBnResumoFinanceiro").val($("#cmbCnBnResumoFinanceiro").val()).change()

        buscar($("#inputDtVencResumoFinanceiro").val(), $("#cmbCnBnResumoFinanceiro").val())
        //--|

        $("#inputDtVencResumoFinanceiro").change(function() {
            buscar($("#inputDtVencResumoFinanceiro").val(), $("#cmbCnBnResumoFinanceiro").val())
        });

        $("#cmbCnBnResumoFinanceiro").change(function() {
            buscar($("#inputDtVencResumoFinanceiro").val(), $("#cmbCnBnResumoFinanceiro").val())
        });
        
    });
    
</script>

<!-- Main sidebar -->
<div class="sidebar sidebar-light sidebar-right sidebar-expand-md">

    <!-- Sidebar content -->
    <div class="sidebar-content">

        <!-- Resumo do Financeiro -->
        <div class="card">
            
            <div style="padding: 30px 10px 15px 10px;background: #aaa;text-align: center;">
                <h2 style="color:#333">Resumo do Financeiro</h2>
            </div>

            <div style="padding: 10px 10px 0px 10px;background: #EEEDED;text-align: center;border-top: 1px solid #ccc;border-bottom: 1px solid #ccc;">
                <input type="date" id="inputDtVencResumoFinanceiro" onchange="selecionaData();" name="inputDtVencResumoFinanceiro" class="form-control" value="<?php echo date('Y-m-d'); ?>" style="font-size: 21px; text-align: center;">
            </div>

            <div style="padding: 10px 10px 0 10px; background: #ccc;">
                <div class="form-group" style="font-size:16px;">
                    <select id="cmbCnBnResumoFinanceiro" name="cmbCnBnResumoFinanceiro" class="form-control form-control-select2">
                        <option value="0">Todos</option>
                        <?php

                            $sql = "SELECT CnBanId, CnBanNome
                                    FROM ContaBanco
                                    JOIN Situacao on SituaId = CnBanStatus
                                    WHERE CnBanUnidade = " . $_SESSION['UnidadeId'] . " and SituaChave = 'ATIVO'
                                    ORDER BY CnBanNome ASC";
                            $result = $conn->query($sql);
                            $rowContaBanco = $result->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($rowContaBanco as $item) {
                                print('<option value="'.$item['CnBanId'].'">' . $item['CnBanNome'] . '</option>');
                            }
                        ?>
                    </select>
                    <h3 class="form-text text-right" style="color: #666;">Conta</h3>
                </div>
                
                <div id="dados">
                    <div class="form-group">
                        <input id="inputCredito" name="inputCredito" class="form-control" value="<?php echo $fCredito; ?>" style="font-size: 30px; text-align: right;" readonly>
                        <h3 class="form-text text-right" style="color: #666;">Crédito</h3>
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
                        <input id="inputCredito" name="inputCredito" class="form-control" value="<?php echo $fCredito; ?>" style="font-size: 30px; text-align: right;" readonly>
                        <h3 class="form-text text-right" style="color: #666;">Saldo Atual</h3>
                    </div>
                </div>
            </div>
        </div>
        <!-- /main navigation -->

        <div style="background: #ccc; height:10px; margin-top: 0px;"></div>

        <div class="card">

            <div style="padding: 10px 10px 5px 10px;background: #aaa;text-align: center;border-top: 1px solid #ccc;border-bottom: 1px solid #ccc;">
                <?php echo "<h2>Acesso Rápido</h2>"; ?>
            </div>        
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <button type="button" class="btn btn-outline bg-slate-600 text-slate-600 border-slate legitRipple" style="width: 100%;">
                            <span>PDV</span>
                        </button>

                        <button type="button" class="btn btn-outline bg-slate-600 text-slate-600 border-slate legitRipple" style="width: 100%; margin-top: 10px;">
                            <span>Transferência</span>
                        </button>

                        <button type="button" class="btn btn-outline bg-slate-600 text-slate-600 border-slate legitRipple" style="width: 100%; margin-top: 10px;">
                            <span>Movimentação</span>
                        </button>                        
                    </div>                    
                </div>
            </div>
        </div>

    </div>
    <!-- /sidebar content -->
			
</div>
<!-- /main sidebar -->
