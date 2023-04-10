<?php

    // Caso seja uma movimentação de saída
    if ($row['MovimTipo'] == 'S') {

        $numPaginas = count($rowMvPrNaoPatrimoniado) / 4;
        $cont = 0;
        $produtos = array_chunk($rowMvPrNaoPatrimoniado, 4);

        foreach ($produtos as $produtos3) {
            
            $cont += 1;

            //Isso aqui para os casos de uma retirada de mais de 4 bens permanentes de uma só vez (isso é para quebrar a página)
            if ($cont > 1){
                $html .= '<br><br><br><br><br><br><br><br><br><br><br><br><br><br>';
            }

            $html .= '                      
           
            <table style="width:100%; border: none;"> 
                <tr>
                    <td style="background-color: #d8d8d8; text-align: center; font-weight: bold; width:100%; ">BENS NÃO PATRIMONIADOS</td>
                </tr>
            </table> <br> ';            

            foreach ($produtos3 as $value) {

                $html .= '  
                    <table style="width:100%; border: none;"> 
                        <tr>
                            <td style="text-align: center; background-color: #eee;">Código: '.$value['Codigo'].'</td>
                         </tr>
                    </table>
                    <table style="width:100%; border: none;"> 
                        <tr>';
                        
                        if($value['Tipo'] == 'S'){
                            $html .= '<td style="text-align: left; width:60%">Serviço:<br>'.$value['Nome'].'</td>';                    
                        } else{   
                            $html .= '<td style="text-align: left; width:60%">Produto:<br>'.$value['Nome'].'</td>';
                            ;    
                        }
                            $html .= '<td style="text-align: left; width:40%">Categoria:<br>'.$value['Categoria'].'</td>
                        </tr>
                    </table>
	                <table style="width:100%; border: none;">
                        <tr>';
                $html .= ' <td style="text-align: left; width:50%">Marca:<br>'. $value['Marca'] .'</td>';
                $html .= ' <td style="text-align: left; width:30%">Modelo:<br>'.$value['NomeModel'].'</td>';

                            if($value['Tipo'] == 'S'){
                                $html .= '<td style="text-align: left; width:20%">Quantidade:<br>'.$value['Quantidade'].'</td> ';                    
                            } else{
            
                               
                                $html .= '<td style="text-align: left; width:20%">Unidade:<br>'.$value['UnMedSigla'].'</td>';
                                ;    
                            }

                                                   
             $html .= ' </tr>
                    </table>'; 
                if($value['Tipo'] == 'P'){

                    $html .= ' <table style="width:100%;border: none;">
                            <tr>
                        ';

                    $html .= '  <td style="text-align: left; width:30%">Classificação:<br>'.$value['ClassNome'].'</td>';

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

                    $html .= '
                    <td style="text-align: left; width:20%">Quantidade:<br>'.$value['Quantidade'].'</td>                                
                                </tr>          
                    </table>  <br> ';
                } else{ 
                    
                   $html .= '<br> ';
                }
            }

        }
        

        //*************************************** Caso seja uma movimentação de Transferência ***********************************\\
    } else if ($row['MovimTipo'] == 'T') {

        $numPaginas = count($rowMvPrNaoPatrimoniado) / 3;
        $cont = 0;
        $produtos = array_chunk($rowMvPrNaoPatrimoniado, 3);

        foreach ($produtos as $produtos3) {
            $cont += 1;

            //Isso aqui para os casos de uma retirada de mais de 4 bens permanentes de uma só vez (isso é para quebrar a página)
            if ($cont > 1){
                $html .= '<br><br><br><br><br><br><br><br><br><br><br><br><br><br>';
            }

            $html .= '                      

            <table style="width:100%; border: none;"> 
                <tr>
                    <td style="background-color: #d8d8d8; text-align: center; font-weight: bold; width:100%; ">BENS NÃO PATRIMONIADOS</td>
                </tr>
            </table> <br> ';       

            foreach ($produtos3 as $value) {


                $html .= '  
                    <table style="width:100%; border: none;"> 
                        <tr>
                            <td style="text-align: center; background-color: #eee;">Código: '.$value['Codigo'].'</td>
                         </tr>
                    </table>
                    <table style="width:100%; border: none;"> 
                        <tr>
                            <td style="text-align: left; width:60%">Produto:<br>'.$value['Nome'].'</td>
                            <td style="text-align: left; width:40%">Categoria:<br>'.$value['Categoria'].'</td>
                        </tr>
                    </table>
	                <table style="width:100%; border: none;">
                        <tr>
                            <td style="text-align: left; width:50%">Marca:<br>'. $value['Marca'] .'</td>
                            <td style="text-align: left; width:30%">Modelo:<br>'.$value['NomeModel'].'</td>
                            <td style="text-align: left; width:20%">Unidade:<br>'.$value['UnMedSigla'].'</td>                                    
                        </tr>
                    </table>

                    <table style="width:100%;border: none;">
                        <tr>
                     ';

                $html .= '  <td style="text-align: left; width:30%">Classificação:<br>'.$value['ClassNome'].'</td>';

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

                $html .= '
                <td style="text-align: left; width:20%">Quantidade:<br>'.$value['Quantidade'].'</td>                                
                            </tr>          
                </table>  <br> ';
            }
        }
    }

    ?>