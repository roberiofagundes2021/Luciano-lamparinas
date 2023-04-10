<?php
	include_once("sessao.php"); 
	include('global_assets/php/conexao.php');

	$_SESSION['PaginaAtual'] = 'Movimentacão Anexo';

	// var_dump($_SESSION['inputTRIdDotacao']);
	// die;

	try{	
		
		$_UP['pasta'] = 'global_assets/anexos/movimentacao/';

		// Renomeia o arquivo? (Se true, o arquivo será salvo como .csv e um nome único)
		$_UP['renomeia'] = false;

		// Primeiro verifica se deve trocar o nome do arquivo
		if ($_UP['renomeia'] == true) {
		
			// Cria um nome baseado no UNIX TIMESTAMP atual e com extensão .csv
			//$nome_final = time().".".$extensao;
			$nome_final = date('d-m-Y')."-".date('H-i-s')."-".$_FILES['inputArquivo']['name'];
		
		} else {
		
			// Mantém o nome original do arquivo
			$nome_final = $_FILES['inputArquivo']['name'];
		}
		
		//echo $_FILES['inputArquivo']['tmp_name']." <br>";
		//echo $_UP['pasta'] . $nome_final." <br>";
		
		// Depois verifica se é possível mover o arquivo para a pasta escolhida
		if (move_uploaded_file($_FILES['inputArquivo']['tmp_name'], $_UP['pasta'] . $nome_final)) {

			$conn->beginTransaction();
		
			/* Cria registro inserindo arquivo na tabela */
			$sql = "INSERT INTO  MovimentacaoAnexo
					(MvAneData, MvAneNome, MvAneArquivo, MvAneMovimentacao, MvAneUsuarioAtualizador, MvAneUnidade)
				   
				    VALUES (:iData, :sNome, :iArquivo, :iMovimentacao, :iUsuarioAtualizador, :iUnidade)";
			$result = $conn->prepare($sql);
			
			$result->execute(array(
					':iData' 				=> gravaData($_POST['inputData']),
					':sNome' 				=> $_POST['inputNome'],
					':iArquivo' 			=> $nome_final,
					':iMovimentacao' 		=> $_SESSION['MovimentacaoIdAnexo'],
					':iUsuarioAtualizador' 	=> $_SESSION['UsuarId'],
					':iUnidade' 			=> $_SESSION['UnidadeId'],
			));


			$conn->commit();
			$_SESSION['msg']['titulo'] 		= "Sucesso";
			$_SESSION['msg']['mensagem'] 	= "Anexo incluído!!!";
			$_SESSION['msg']['tipo'] 		= "success";
		}

	} catch(PDOException $e) {
		
		$conn->rollback();
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao incluir Anexo!!!";
		$_SESSION['msg']['tipo'] = "error";	
		
		echo 'Error: ' . $e->getMessage().$e->getLine();exit;
	}

	irpara('movimentacaoAnexo.php');
?>