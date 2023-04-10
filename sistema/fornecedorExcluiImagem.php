<?php
   
	include_once("sessao.php");    

	$pasta = "global_assets/images/fornecedores/";
                   
	if (file_exists($pasta.$_POST['foto'])){
		unlink($pasta.$_POST['foto']);
		echo 1;
	} else {
		echo 0;
	}    
    
?>
