<?php
include_once("sessao.php");
include('global_assets/php/conexao.php');

//Para pegar a última consulta
$sql_saldoInicial    = "SELECT CxAbeId, CaixaNome, CxAbeCaixa, CxAbeDataHoraAbertura, CxAbeDataHoraFechamento, SituaChave
                        FROM CaixaAbertura
                        JOIN Caixa on CaixaId = CxAbeCaixa
                        JOIN Situacao on SituaId = CxAbeStatus
                        WHERE CxAbeOperador = ".$_SESSION['UsuarId']." AND CxAbeUnidade = $_SESSION[UnidadeId]
                        ORDER BY CxAbeId DESC";
$resultSaldoInicial  = $conn->query($sql_saldoInicial);


if($rowSaldoInicial = $resultSaldoInicial->fetch(PDO::FETCH_ASSOC)) {
    $resposta = $rowSaldoInicial;
}else {
    $resposta = 'abrirCaixa';
}

print(json_encode($resposta));

//codigo para BACKUP Caso precise

/*function consultaSituacaoCaixa() {
                    let urlConsultaAberturaCaixa = "consultaAberturaCaixa.php";

                    $.ajax({
                        type: "POST",
                        url: urlConsultaAberturaCaixa,
                        dataType: "json",
                        success: function(resposta) {
                            //Essa condicional acontece quando n há registros no banco
                            if(resposta == 'abrirCaixa') {
                                $(".caixaEmOperacao").hide();
                            }else {
                                //Essa situação é quando há registros, porém o caixa está fechado
                                if(resposta.SituaChave == 'FECHADO') {
                                    $(".caixaEmOperacao").hide();
                                }else {
                                    $(".caixaEmOperacao").show();
                                }
                                
                                $("#inputAberturaCaixaId").val(resposta.CxAbeId);
                                $("#inputCaixaId").val(resposta.CxAbeCaixa);
                                $("#inputSaldoInicial").val(float2moeda(resposta.CxAbeSaldoFinal));
                            }
                        }
                    })
                }*/
?>