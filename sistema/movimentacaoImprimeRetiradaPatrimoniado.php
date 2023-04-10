<?php

    // Caso seja uma movimentação de saída
    if ($row['MovimTipo'] == 'S') {

        $numPaginas = count($rowMvPrPatrimoniado) / 4;
        $cont = 0;
        $produtos = array_chunk($rowMvPrPatrimoniado, 4);

        foreach ($produtos as $produtos3) {            
            
            $cont += 1;
            
            //Isso aqui para os casos de uma retirada de mais de 4 bens permanentes de uma só vez (isso é para quebrar a página)
            if ($cont > 1){
                $html .= '<br><br><br><br><br><br><br><br><br><br><br><br><br><br>';
            }

            $html .= '  
           
            <table style="width:100%; border: none;">
                <tr>
                    <td  style="background-color: #d8d8d8; text-align: center; font-weight: bold; width:100%; ">BENS PATRIMONIADOS</td>
                </tr>
            </table> <br> ';

            foreach ($produtos3 as $value) {

                $html .= '   
                
                <table style="width:100%; border: none;">
                        <tr>
                            <td style="text-align: center; background-color: #eee;">Patrimônio: '.$value['PatriNumero'].'</td>
                        </tr>
                </table>
                    
                <table style="width:100%; border: none;"> 
                    <tr>
                        <td style="text-align: left; width:60%">Produto:<br>'.$value['ProduNome'].'</td>
                        <td style="text-align: left; width:40%">Código:<br>'.$value['ProduCodigo'].'</td>
                    </tr>
                </table>
                <table style="width:100%; border: none;">
                    <tr>
                        <td style="text-align: left; width:50%">Marca:<br>'. $value['MarcaNome'] .'</td>
                        <td style="text-align: left; width:30%">Modelo:<br>'.$value['ModelNome'].'</td>
                        <td style="text-align: left; width:20%">Unidade:<br>'.$value['UnMedSigla'].'</td>                                    
                    </tr>
                </table>

                <table style="width:100%;border: none;">
                    <tr>
                        ';

                        $html .= '  <td style="text-align: left; width:30%">Categoria:<br>'.$value['CategNome'].'</td>';

                        if($value['Validade'] == ''){
                            $html .= '  <td style="text-align: left; width:50%">Lote:<br>'.$value['MvXPrLote'].'</td>';                    
                        } else{
                            if ($value['Validade'] == '1900-01-01'){
                                $validade = 'Não informado';
                            } else{
                                $validade = mostraData($value['Validade']);
                            }

                            $html .= '  <td style="text-align: left; width:20%">Lote:<br>'.$value['MvXPrLote'].'</td>';
                            $html .= '  <td style="text-align: left; width:30%">Validade:<br>'.$validade.'</td>';    
                        }

                        $html .= '  <td style="text-align: left; width:20%">Quantidade:<br>1</td>';
                    $html .= ' </tr>
                </table> <br>';
            }

        }

        //*************************************** Caso seja uma movimentação de Transferência ***********************************\\
    } else if ($row['MovimTipo'] == 'T') {

        $numPaginas = count($rowMvPrPatrimoniado) / 3;
        $cont = 0;
        $produtos = array_chunk($rowMvPrPatrimoniado, 3);

        foreach ($produtos as $produtos3) {
            $cont += 1;

           //Isso aqui para os casos de uma retirada de mais de 4 bens permanentes de uma só vez (isso é para quebrar a página)
           if ($cont > 1){
                $html .= '<br><br><br><br><br><br><br><br><br><br><br><br><br><br>';
            }

            $html .= '  
            
            <table style="width:100%; border: none;">
                <tr>
                    <td  style="background-color: #d8d8d8; text-align: center; font-weight: bold; width:100%; ">BENS PATRIMONIADOS</td>
                </tr>
            </table> <br> ';

            foreach ($produtos3 as $value) {


                $html .= '

                <table style="width:100%; border: none;">
                    <tr>
                        <td style="text-align: center; background-color: #eee;">Patrimônio: '.$value['PatriNumero'].'</td>
                    </tr>
                </table>

                <table style="width:100%; border: none;"> 
                    <tr>
                        <td style="text-align: left; width:60%">Produto:<br>'.$value['ProduNome'].'</td>
                        <td style="text-align: left; width:40%">Código:<br>'.$value['ProduCodigo'].'</td>
                    </tr>
                </table>
                <table style="width:100%; border: none;">
                    <tr>
                        <td style="text-align: left; width:50%">Marca:<br>'. $value['MarcaNome'] .'</td>
                        <td style="text-align: left; width:30%">Modelo:<br>'.$value['ModelNome'].'</td>
                        <td style="text-align: left; width:20%">Unidade:<br>'.$value['UnMedSigla'].'</td>                                    
                    </tr>
                </table>
                <table style="width:100%;border: none;">
                    <tr>
                        ';

                        $html .= '  <td style="text-align: left; width:30%">Categoria:<br>'.$value['CategNome'].'</td>';

                        if($value['Validade'] == ''){
                            $html .= '  <td style="text-align: left; width:50%">Lote:<br>'.$value['MvXPrLote'].'</td>';                    
                        } else{
                            if ($value['Validade'] == '1900-01-01'){
                                $validade = 'Não informado';
                            } else{
                                $validade = mostraData($value['Validade']);
                            }
                            
                            $html .= '  <td style="text-align: left; width:20%">Lote:<br>'.$value['MvXPrLote'].'</td>';
                            $html .= '  <td style="text-align: left; width:30%">Validade:<br>'.$validade.'</td>';    
                        }

                        $html .= '  <td style="text-align: left; width:20%">Quantidade:<br>1</td>';
                    $html .= ' </tr>
                </table> <br>';      
            }
        }
    }

    ?>