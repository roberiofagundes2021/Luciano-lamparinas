<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

$typeRequest = $_POST['tipoRequest'];
$usuaId = $_SESSION['UsuarId'];
$iUnidade = $_SESSION['UnidadeId'];
$EmpresaId = isset($_SESSION['EmpresaId'])?$_SESSION['EmpresaId']:$_SESSION['EmpreId'];

if(!isset($_SESSION['admissaoCirurgica'])){
  $_SESSION['admissaoCirurgica'] = [
    'acesso'=>[],
    'concentimento'=>[],
    'exames'=>[],
  ];
}

try{  

  if($typeRequest == "ADDACESSOVENOSO"){

    
    $admissaoCirurgicaPreOperatorio = $_POST['iAtendimentoCirurgicoPreOperatorio'] == '' ? $_POST['idTemporaria'] : $_POST['iAtendimentoCirurgicoPreOperatorio'];

    $dataHoraAcessoVenoso = $_POST['dataHoraAcessoVenoso'] == "" ? null : str_replace('T', ' ', $_POST['dataHoraAcessoVenoso']);
    $localPuncaoAcessoVenoso = $_POST['localPuncaoAcessoVenoso'] == "" ? null : $_POST['localPuncaoAcessoVenoso'];
    $calibreAcessoVenoso = $_POST['calibreAcessoVenoso'] == "" ? null : $_POST['calibreAcessoVenoso'];
    $responsavelAcessoVenoso = $_POST['responsavelAcessoVenoso'] == "" ? null : $_POST['responsavelAcessoVenoso'];

		$sql = "INSERT INTO  EnfermagemAdmissaoCirurgicaPreOperatorioAcessoVenoso( EnACAAdmissaoCirurgicaPreOperatorio, EnACADataHora, EnACALocalPuncao, 
        EnACATipoCalibre, EnACAResponsavelTecnico, EnACAUnidade )
        VALUES ( '$admissaoCirurgicaPreOperatorio', '$dataHoraAcessoVenoso', '$localPuncaoAcessoVenoso', '$calibreAcessoVenoso', '$responsavelAcessoVenoso', '$iUnidade')";
    $conn->query($sql);

    echo json_encode([
      'status' => 'success',
      'titulo' => 'Acesso Venoso',
      'menssagem' => 'Acesso Venoso inserido com sucesso!!!'
    ]);	

  }elseif ($typeRequest == "ADDTERMOCONSENTIMENTO") {

    try {
			$nome_final = '';

			if ( isset($_FILES['arquivoTermoConsentimento']) ) {

				$_UP['pasta'] = 'global_assets/anexos/termoConsentimentoAdCirPreOperatorio/';
				// Renomeia o arquivo? (Se true, o arquivo será salvo como .csv e um nome único)
				$_UP['renomeia'] = false;
				// Primeiro verifica se deve trocar o nome do arquivo
				if ($_UP['renomeia'] == true) {			
					// Cria um nome baseado no UNIX TIMESTAMP atual e com extensão .csv
					$nome_final = date('d-m-Y')."-".date('H-i-s')."-".$_FILES['arquivoTermoConsentimento']['name'];			
				} else {			
					// Mantém o nome original do arquivo
					$nome_final = $_FILES['arquivoTermoConsentimento']['name'];
				}
				move_uploaded_file( $_FILES['arquivoTermoConsentimento']['tmp_name'], $_UP['pasta'] . $nome_final);
			}

      $admissaoCirurgicaPreOperatorio = $_POST['iAtendimentoCirurgicoPreOperatorio'] == 'null' ? $_POST['idTemporaria'] : $_POST['iAtendimentoCirurgicoPreOperatorio'];
			$dataHoraConsentimento = $_POST['dataHoraConsentimento'] == "" ? null : str_replace('T', ' ', $_POST['dataHoraConsentimento'] );
			$descricaoConsentimento = $_POST['descricaoConsentimento'] == "" ? null : $_POST['descricaoConsentimento'];
			$arquivoTermoConsentimento = $nome_final;

			$sql = "INSERT INTO  EnfermagemAdmissaoCirurgicaPreOperatorioTermoConsentimento( EnACTAdmissaoCirurgicaPreOperatorio, EnACTDataHora, EnACTDescricao, EnACTArquivo, EnACTUnidade )
				VALUES ( '$admissaoCirurgicaPreOperatorio', '$dataHoraConsentimento', '$descricaoConsentimento', '$arquivoTermoConsentimento', '$iUnidade')";
			$conn->query($sql);

			echo json_encode([
				'status' => 'success',
				'titulo' => 'Termo de Consentimento',
				'menssagem' => 'Termo inserido com sucesso!!!'
			]);	

		} catch (\Throwable $th) {

			echo json_encode([
					'status' => 'error',
					'titulo' => 'Erro',
					'menssagem' => 'Erro ao adicionar Termo de Consentimento!!!' 
			]);

		}

    
  }elseif ($typeRequest == 'ADDEXAMESCOMPLEMENTARES') {

    //var_dump($_FILES);die;

    try {
			$nome_final = '';

			if ( isset($_FILES['arquivoExame']) ) {

				$_UP['pasta'] = 'global_assets/anexos/examesComplementaresImagensAdCirPreOperatorio/';
				// Renomeia o arquivo? (Se true, o arquivo será salvo como .csv e um nome único)
				$_UP['renomeia'] = false;
				// Primeiro verifica se deve trocar o nome do arquivo
				if ($_UP['renomeia'] == true) {			
					// Cria um nome baseado no UNIX TIMESTAMP atual e com extensão .csv
					$nome_final = date('d-m-Y')."-".date('H-i-s')."-".$_FILES['arquivoExame']['name'];			
				} else {			
					// Mantém o nome original do arquivo
					$nome_final = $_FILES['arquivoExame']['name'];
				}
				move_uploaded_file( $_FILES['arquivoExame']['tmp_name'], $_UP['pasta'] . $nome_final);
			}

      $admissaoCirurgicaPreOperatorio = $_POST['iAtendimentoCirurgicoPreOperatorio'] == 'null' ? $_POST['idTemporaria'] : $_POST['iAtendimentoCirurgicoPreOperatorio'];
			$dataHoraExame = $_POST['dataHoraExame'] == "" ? null : str_replace('T', ' ', $_POST['dataHoraExame'] );
			$descricaoExame = $_POST['descricaoExame'] == "" ? null : $_POST['descricaoExame'];
			$arquivoExame = $nome_final;

			$sql = "INSERT INTO  EnfermagemAdmissaoCirurgicaPreOperatorioExameComplementar( EnACEAdmissaoCirurgicaPreOperatorio, EnACEDataHora, EnACEDescricao, EnACEArquivo, EnACEUnidade )
				VALUES ( '$admissaoCirurgicaPreOperatorio', '$dataHoraExame', '$descricaoExame', '$arquivoExame', '$iUnidade')";
			$conn->query($sql);

			echo json_encode([
				'status' => 'success',
				'titulo' => 'Exames Complementares de Imagens',
				'menssagem' => 'Exame inserido com sucesso!!!'
			]);	

		} catch (\Throwable $th) {

			echo json_encode([
					'status' => 'error',
					'titulo' => 'Erro',
					'menssagem' => 'Erro ao adicionar Termo de Consentimento!!!' 
			]);
		}
    
  }elseif ($typeRequest == 'GETACESSOSVENOSOS') {

    $iAtendimentoCirurgicoPreOperatorio = $_POST['iAtendimentoCirurgicoPreOperatorio'] == "" ? null : $_POST['iAtendimentoCirurgicoPreOperatorio'];
	
		$sql = "SELECT * FROM EnfermagemAdmissaoCirurgicaPreOperatorioAcessoVenoso
				WHERE EnACAAdmissaoCirurgicaPreOperatorio = $iAtendimentoCirurgicoPreOperatorio";

		$result = $conn->query($sql);
		$acessosVenosos = $result->fetchAll(PDO::FETCH_ASSOC);

		$array = [];

		foreach($acessosVenosos as $key => $item){

			$dataHora = explode(" ", $item['EnACADataHora']);
			array_push($array,[
				'item' => ($key + 1),
				'id'=>$item['EnACAId'],
				'dataHora'=> mostraData($dataHora[0]) . ' ' . mostraHora($dataHora[1]),
        'localPuncao' => $item['EnACALocalPuncao'],
        'tipoCalibre' => $item['EnACATipoCalibre'],
        'responsavelTecnico' => $item['EnACAResponsavelTecnico']
			]);
		}		
		echo json_encode($array);

  }elseif ($typeRequest == 'GETTERMOSCONSENTIMENTO') {

    $iAtendimentoCirurgicoPreOperatorio = $_POST['iAtendimentoCirurgicoPreOperatorio'] == "" ? null : $_POST['iAtendimentoCirurgicoPreOperatorio'];
	
		$sql = "SELECT * FROM EnfermagemAdmissaoCirurgicaPreOperatorioTermoConsentimento
				WHERE EnACTAdmissaoCirurgicaPreOperatorio = $iAtendimentoCirurgicoPreOperatorio";

		$result = $conn->query($sql);
		$termos = $result->fetchAll(PDO::FETCH_ASSOC);

		$array = [];

		foreach($termos as $key => $item){

			$dataHora = explode(" ", $item['EnACTDataHora']);
			array_push($array,[
				'item' => ($key + 1),
				'id'=>$item['EnACTId'],
				'dataHora'=> mostraData($dataHora[0]) . ' ' . mostraHora($dataHora[1]),
				'descricao' => $item['EnACTDescricao'],
				'arquivo' => $item['EnACTArquivo']
			]);
		}
		echo json_encode($array);
    
  }elseif ($typeRequest == 'GETEXAMESCOMPLEMENTARES') {

    $iAtendimentoCirurgicoPreOperatorio = $_POST['iAtendimentoCirurgicoPreOperatorio'] == "" ? null : $_POST['iAtendimentoCirurgicoPreOperatorio'];
	
		$sql = "SELECT * FROM EnfermagemAdmissaoCirurgicaPreOperatorioExameComplementar
				WHERE EnACEAdmissaoCirurgicaPreOperatorio = $iAtendimentoCirurgicoPreOperatorio";

		$result = $conn->query($sql);
		$exames = $result->fetchAll(PDO::FETCH_ASSOC);

		$array = [];

		foreach($exames as $key => $item){

			$dataHora = explode(" ", $item['EnACEDataHora']);
			array_push($array,[
				'item' => ($key + 1),
				'id'=>$item['EnACEId'],
				'dataHora'=> mostraData($dataHora[0]) . ' ' . mostraHora($dataHora[1]),
				'descricao' => $item['EnACEDescricao'],
				'arquivo' => $item['EnACEArquivo']
			]);
		}
		echo json_encode($array);
    
  } elseif ($typeRequest == "DELETEACESSOVENOSO") {
    
    $id = $_POST['id'];
	
		$sql = "DELETE FROM EnfermagemAdmissaoCirurgicaPreOperatorioAcessoVenoso
		WHERE EnACAId = $id";
		$conn->query($sql);

		echo json_encode([
			'status' => 'success',
			'titulo' => 'Acesso Venoso',
			'menssagem' => 'Acesso excluído!!!',
		]);

  } elseif ($typeRequest == "DELETETERMO") {
    
    $id = $_POST['id'];
	
		$sql = "DELETE FROM EnfermagemAdmissaoCirurgicaPreOperatorioTermoConsentimento
		WHERE EnACTId = $id";
		$conn->query($sql);

		echo json_encode([
			'status' => 'success',
			'titulo' => 'Termo de Consentimento',
			'menssagem' => 'Termo excluído!!!',
		]);

  } elseif ($typeRequest == "DELETEEXAME") {
    
    $id = $_POST['id'];
	
		$sql = "DELETE FROM EnfermagemAdmissaoCirurgicaPreOperatorioExameComplementar
		WHERE EnACEId = $id";
		$conn->query($sql);

		echo json_encode([
			'status' => 'success',
			'titulo' => 'Exames Complementares',
			'menssagem' => 'Exame excluído!!!',
		]);

  }

}catch(PDOException $e) {
  $_SESSION['msg']['titulo'] = "Erro";
  $_SESSION['msg']['mensagem'] = "Erro ao salvar Admissão!!!";
  $_SESSION['msg']['tipo'] = "error";

  echo json_encode([
    'type' => $typeRequest,
    'err' => $e,
    'sql' => $sql
  ]);
}
?>