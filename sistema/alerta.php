<?php
	
	//verifica se existe a variável de sessão e se ela não não está vazia (lembrando que ela é um array)
	if (isset($_SESSION['msg']) and !empty($_SESSION['msg'])){
		
		print("
			<script>
				
				var titulo = '".$_SESSION['msg']['titulo']."';
				var msg = '".$_SESSION['msg']['mensagem']."';
				var tipo = '".$_SESSION['msg']['tipo']."';							

				if (msg) {
												
					$(function(){
						alerta(titulo, msg, tipo);
					});
				}
		
			 </script>  					
		");	
		
		$_SESSION['msg'] = array();
	}
	
?>
