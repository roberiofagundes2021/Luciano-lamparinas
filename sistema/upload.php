<?php
   
	include_once("sessao.php");
   

    $pasta = "";
    switch($_POST['tela']){
        case 'produto':
            $pasta = "global_assets/images/produtos/";
            break;
        case 'empresa':
            $pasta = "global_assets/images/empresas/";
            break;
        case 'fornecedor':
            $pasta = "global_assets/images/fornecedores/";
            break;
    }
    
    // formatos de imagem permitidos
    $permitidos = array(".jpg", ".jpeg", ".gif", ".png", ".bmp");   
         
    if(isset($_FILES)){
		
		//print_r($_FILES['imagem']);die;
		
        $nome_imagem    = $_FILES['imagem']['name'];
        $tamanho_imagem = $_FILES['imagem']['size'];
  
        // pega a extensão do arquivo
        $ext = strtolower(strrchr($nome_imagem,"."));
         
        //  verifica se a extensão está entre as extensões permitidas
        if(in_array($ext,$permitidos)){
             
            // converte o tamanho para KB
            $tamanho = round($tamanho_imagem / 1024);
            $tamanhoMaximo = 1024 * 1024 * 3; //3MB
             
            if($tamanho < $tamanhoMaximo){ //se imagem for até 3MB envia
                $nome_atual = md5(uniqid(time())).$ext; //nome que dará a imagem

                $tmp = $_FILES['imagem']['tmp_name']; //caminho temporário da imagem
                
                $destino = $pasta.$nome_atual;

				//ATENCAO: tive que dá permissão no diretório "sudo chmod 777 -R /var/www/html/lamparinas/global_assets/images/produtos" pra funcionar.
				//Tem que testar no servidor, pra ver se teremos que fazer isso também no SERVIDOR da AZURE
                 
                // se enviar a foto, insere o nome da foto no banco de dados
                if(move_uploaded_file($tmp, $destino)){
                    
                    //verifica se já foi adicionado alguma foto, se sim, exclui ela fisicamente, já que nao será mais usada, eliminando lixo.
                    if (isset($_SESSION['fotoAtual']) and $_SESSION['fotoAtual'] != ''){
						if (file_exists($pasta.$_SESSION['fotoAtual'])){
							unlink($pasta.$_SESSION['fotoAtual']);
						}
					}                    
                    
                    print('<a href="'.$pasta.$nome_atual.'" class="fancybox">
							 <img src="'.$pasta.$nome_atual.'" style="max-width: 230px; max-height:250px; border:2px solid #ccc;">
						   </a>
						   <input type="hidden" id="inputFoto" name="inputFoto" value="'.$nome_atual.'" >
						   '); //imprime a foto na tela
                    
                    $_SESSION['fotoAtual'] = $nome_atual; //guarda o nome da foto pra gravar no banco					
                }else{
                    echo "Falha ao enviar";
					
					unset($_SESSION['fotoAtual']);
                }
            }else{
                echo "A imagem deve ser de no máximo 3MB";
            }
        }else{
            echo "Formatos aceitos: .jpg, .jpeg, .gif, .png, .bmp";
        }
    }else{
        echo "Selecione uma imagem";
        exit;
    }
    
?>
