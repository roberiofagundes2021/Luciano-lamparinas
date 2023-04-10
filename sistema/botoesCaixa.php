<div class="card">      
    <div class="card-body">
        <div class="row">
            <div class="col-7">
                <div class="row">
                    <div class="col-3">
                        <button id="btnPdv" class="btn btn-outline bg-slate-600 text-slate-600 border-slate <?php echo $_SESSION['PaginaAtual'] =='Caixa PDV' ? "active" : "" ?> legitRipple" style="width: 100%;">PDV</button>
                    </div>

                    <div class="col-3">
                        <a href="caixaMovimentacao.php" type="button" class="btn btn-outline bg-slate-600 text-slate-600 border-slate <?php echo $_SESSION['PaginaAtual'] =='Movimentação do Caixa' ? "active" : "" ?> legitRipple" style="width: 100%;">
                            <span style="font-size: 11px;">Movimentação</span>
                        </a>   
                    </div>

                    <div class="col-3">
                        <button id="btnRetirada" type="button" data-toggle="modal" data-target="#modal_small_Retirada_Caixa" class="btn btn-outline bg-slate-600 text-slate-600 border-slate legitRipple caixaEmOperacao" style="width: 100%; display: none;">
                            <span style="font-size: 11px;">Retirada</span>
                        </button>
                    </div>
                    
                    <div class="col-3">
                        <button id="btnFechamento" type="button" class="btn btn-outline bg-slate-600 text-slate-600 border-slate legitRipple caixaEmOperacao <?php echo $_SESSION['PaginaAtual'] =='Caixa Fechamento' ? "active" : "" ?>" style="width: 100%; display: none;">
                            <span style="font-size: 11px;">Fechamento</span>
                        </button>   
                    </div>    
                </div>
            </div>

            <div class="col-5 m-auto">
                <h4 class="text-right m-auto"><?php echo "Operador: " . nomeSobrenome($_SESSION['UsuarNome'], 1); ?></h4>
            </div>
        </div>
    </div>
</div>