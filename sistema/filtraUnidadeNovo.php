<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

$typeRequest = $_POST['tipoRequest'];
$usuaId = $_SESSION['UsuarId'];
$EmpresaId = isset($_SESSION['EmpresaId'])?$_SESSION['EmpresaId']:$_SESSION['EmpreId'];

try{
  // $conn->beginTransaction();
  
  if($typeRequest == "UNIDADE"){
    $sql = "INSERT INTO Unidade (UnidaNome, UnidaCNES, UnidaCnpj, UnidaTelefone, UnidaDiretorAdministrativo, UnidaDiretorTecnico, UnidaDiretorClinico, UnidaCep, UnidaEndereco, UnidaNumero, UnidaComplemento, UnidaBairro, 
                      UnidaCidade, UnidaEstado, UnidaStatus, UnidaUsuarioAtualizador, UnidaEmpresa)
            VALUES (:sNome, :sCNES, :sCnpj, :sTelefone, :sDiretorAdministrativo, :sDiretorTecnico, :sDiretorClinico, :sCep, :sEndereco, :sNumero, :sComplemento, :sBairro, 
                :sCidade, :sEstado, :bStatus, :iUsuarioAtualizador, :iEmpresa)";
		$result = $conn->prepare($sql);
				
		$result->execute(array(
      ':sNome' => $_POST['inputNome'],
      ':sCNES' => $_POST['inputCNES'] == '' ? null : $_POST['inputCNES'],
      ':sCnpj' => limpaCPF_CNPJ($_POST['inputCnpj']),
      ':sTelefone' => $_POST['inputTelefone'] == '(__) ____-____' ? null : $_POST['inputTelefone'],
      ':sDiretorAdministrativo' => $_POST['inputDiretorAdministrativo'],
      ':sDiretorTecnico' => $_POST['inputDiretorTecnico'],
      ':sDiretorClinico' => $_POST['inputDiretorClinico'],
      ':sCep' => $_POST['inputCep'],
      ':sEndereco' => $_POST['inputEndereco'],
      ':sNumero' => $_POST['inputNumero'],
      ':sComplemento' => $_POST['inputComplemento'],
      ':sBairro' => $_POST['inputBairro'],
      ':sCidade' => $_POST['inputCidade'],
      ':sEstado' => $_POST['cmbEstado'] == "" ? null : $_POST['cmbEstado'],
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iEmpresa' => $EmpresaId
    ));
    $unidadeIdNovo = $conn->lastInsertId();

    echo $unidadeIdNovo;
  }elseif($typeRequest == "PERFIS"){
    $unidadeIdNovo = $_POST['unidadeIdNovo'];

    $sqlPerfisPadrao = "SELECT PerfiId, PerfiNome, PerfiChave, PerfiStatus, SituaNome, SituaChave, SituaCor
      FROM Perfil
      JOIN Situacao on SituaId = PerfiStatus
      WHERE PerfiUnidade is null and PerfiPadrao = 1 and SituaChave = 'ATIVO'";
    $sqlPerfisPadrao = $conn->query($sqlPerfisPadrao);
    $sqlPerfisPadrao = $sqlPerfisPadrao->fetchAll(PDO::FETCH_ASSOC);

    $sqlPerfil = "INSERT INTO Perfil(PerfiNome,PerfiChave,PerfiStatus,PerfiUsuarioAtualizador,PerfiUnidade,PerfiPadrao) VALUES ";

    foreach($sqlPerfisPadrao as $perfPadrao){
      $sqlPerfil .= " ('$perfPadrao[PerfiNome]','$perfPadrao[PerfiChave]',$perfPadrao[PerfiStatus],$usuaId,$unidadeIdNovo,0),";
    }
    $sqlPerfil = substr($sqlPerfil, 0, -1);
    $conn->query($sqlPerfil);

    echo json_encode(true);
  }elseif($typeRequest == "PERFILPERMISSAOPADRAO"){
    $unidadeIdNovo = $_POST['unidadeIdNovo'];
    // esse select traz todos os perfis da unidade nova
    $sql = "SELECT PerfiId,PerfiNome,PerfiChave,PerfiStatus,PerfiUsuarioAtualizador,PerfiUnidade,PerfiPadrao
      FROM Perfil
      WHERE PerfiUnidade = $unidadeIdNovo";
    $result = $conn->query($sql);
    $rowPerfil = $result->fetchAll(PDO::FETCH_ASSOC);

    // esse select traz todos os perfis padrões LAMPARINAS com as respectivas PerfiChave
    $sql = "SELECT PaPerPerfil,PaPerMenu,MenuSetorPublico,MenuSetorPrivado,PaPerVisualizar,
      PaPerAtualizar,PaPerExcluir,PaPerInserir,PaPerSuperAdmin,PerfiChave
      FROM PadraoPermissao
      JOIN Perfil ON PerfiId = PaPerPerfil
      JOIN Menu ON MenuId = PaPerMenu";
    $result = $conn->query($sql);
    $rowPerfilPadrao = $result->fetchAll(PDO::FETCH_ASSOC);

    //Recupera o parâmetro pra saber se a empresa é pública ou privada
    $sqlParametro = "SELECT ParamEmpresaPublica 
      FROM Parametro
      WHERE ParamEmpresa = ".$_SESSION['EmpreId'];
    $resultParametro = $conn->query($sqlParametro);
    $parametro = $resultParametro->fetch(PDO::FETCH_ASSOC);

    $empresa = $parametro['ParamEmpresaPublica'] ? 'Publica' : 'Privada';
    $cont = 0;

    // inserir em PerfilXPermissao -------------------------------------------------------------------
      // $sqlPerfilXPermissao = "INSERT INTO PerfilXPermissao (PrXPePerfil,PrXPeMenu,PrXPeUnidade,PrXPeInserir,PrXPeVisualizar,
      // PrXPeAtualizar,PrXPeExcluir,PrXPeSuperAdmin)
      // VALUES ";

      // inserir em PadraoPerfilXPermissao -------------------------------------------------------------
      
      // $sqlPadraoPerfilXPermissao = "INSERT INTO PadraoPerfilXPermissao (PaPrXPePerfil, PaPrXPeMenu,PaPrXPeUnidade,PaPrXPeInserir,
      // PaPrXPeVisualizar,PaPrXPeAtualizar,PaPrXPeExcluir,PaPrXPeSuperAdmin)
      // VALUES ";

      // nesse laço para cada perfil ele procura um perfil padrão que contenha a mesma PerfiChave para
      // utilizar os campos: PaPerVisualizar, PaPerAtualizar, PaPerExcluir, PaPerSuperAdmin
      
      // foreach ($rowPerfil as $itemPerfil){
      //   foreach($rowPerfilPadrao as $rowPerPad){
      //     if($itemPerfil['PerfiChave'] == $rowPerPad['PerfiChave']){
      //       // nessa parte é verificado se a empresa é publica ou privada, assim verifica-se se cada menu
      //       // possui permissão de aparecer para esses tipos (publica/privada), caso publica, por exemplo, é atribuido o valor predefinido
      //       // no padrão caso o menu possa ser vispo por empresa publica, caso contrario ira setar como 0
      //       // permitindo que, caso necessario, o administrador altere essa condição apenas na empresa específica
      //       $empresaPermissao = $empresa=='Publica'?$rowPerPad['MenuSetorPublico']:$rowPerPad['MenuSetorPrivado'];
      //       $visualizar = $empresaPermissao?$rowPerPad['PaPerVisualizar']:0;

      //       $sqlPerfilXPermissao .= " ('$itemPerfil[PerfiId]', '$rowPerPad[PaPerMenu]', '$unidadeIdNovo', '$rowPerPad[PaPerInserir]',
      //       '$visualizar', '$rowPerPad[PaPerAtualizar]', '$rowPerPad[PaPerExcluir]', '$rowPerPad[PaPerSuperAdmin]'),";
      
      //       $sqlPadraoPerfilXPermissao .=  "('$itemPerfil[PerfiId]', '$rowPerPad[PaPerMenu]', '$unidadeIdNovo', '$rowPerPad[PaPerInserir]',
      //       '$visualizar', '$rowPerPad[PaPerAtualizar]', '$rowPerPad[PaPerExcluir]', '$rowPerPad[PaPerSuperAdmin]'),";

      //       $cont++;

      //       $new = $sqlPerfilXPermissao;
      //       if($old == $new){
      //         array_push($lista, $new);
      //       }
      //       $old = $new;

      //       if ($cont > 990){
      //         // Insere na base para não atingir o limite de 1000 linhas por INSERT
      //         $sqlPerfilXPermissao = substr($sqlPerfilXPermissao, 0, -1);
      //         $sqlPadraoPerfilXPermissao = substr($sqlPadraoPerfilXPermissao, 0, -1);
      //         $result = $conn->query($sqlPerfilXPermissao);
      //         $result = $conn->query($sqlPadraoPerfilXPermissao);

      //         // recria o inserir em PerfilXPermissao -------------------------------------------------------------------
      //         $sqlPerfilXPermissao = "INSERT INTO PerfilXPermissao (PrXPePerfil,PrXPeMenu,PrXPeUnidade,PrXPeInserir,PrXPeVisualizar,
      //         PrXPeAtualizar,PrXPeExcluir,PrXPeSuperAdmin) VALUES ";

      //         // recria o inserir em PadraoPerfilXPermissao -------------------------------------------------------------
      //         $sqlPadraoPerfilXPermissao = "INSERT INTO PadraoPerfilXPermissao (PaPrXPePerfil, PaPrXPeMenu,PaPrXPeUnidade,PaPrXPeInserir,
      //         PaPrXPeVisualizar,PaPrXPeAtualizar,PaPrXPeExcluir,PaPrXPeSuperAdmin) VALUES ";

      //         $cont = 0;
      //       }
      //     }
      //   }
      // }
      // if($cont > 0){
      //   $sqlPerfilXPermissao = substr($sqlPerfilXPermissao, 0, -1);
      //   $sqlPadraoPerfilXPermissao = substr($sqlPadraoPerfilXPermissao, 0, -1);
      //   $result = $conn->query($sqlPerfilXPermissao);
      //   $result = $conn->query($sqlPadraoPerfilXPermissao);
      // }
    //
    $sqlPerfilXPermissao = [];
    $sqlPadraoPerfilXPermissao = [];

    // está gerando uma lista de itens a ser inserido e depois verifica se existe itens duplicados
    // para não cadastrar duplicatos no banco

    foreach ($rowPerfil as $itemPerfil){
      foreach($rowPerfilPadrao as $rowPerPad){
        if($itemPerfil['PerfiChave'] == $rowPerPad['PerfiChave']){
          // nessa parte é verificado se a empresa é publica ou privada, assim verifica-se se cada menu
          // possui permissão de aparecer para esses tipos (publica/privada), caso publica, por exemplo, é atribuido o valor predefinido
          // no padrão caso o menu possa ser vispo por empresa publica, caso contrario ira setar como 0
          // permitindo que, caso necessario, o administrador altere essa condição apenas na empresa específica
          $empresaPermissao = $empresa=='Publica'?$rowPerPad['MenuSetorPublico']:$rowPerPad['MenuSetorPrivado'];
          $visualizar = $empresaPermissao?$rowPerPad['PaPerVisualizar']:0;
    
          array_push($sqlPadraoPerfilXPermissao,"('$itemPerfil[PerfiId]', '$rowPerPad[PaPerMenu]', '$unidadeIdNovo', '$rowPerPad[PaPerInserir]',
          '$visualizar', '$rowPerPad[PaPerAtualizar]', '$rowPerPad[PaPerExcluir]', '$rowPerPad[PaPerSuperAdmin]'),");
        }
      }
    }

    $sqlPadraoPerfilXPermissao = array_unique($sqlPadraoPerfilXPermissao);

    $sqlPerfilPadrao = "INSERT INTO PadraoPerfilXPermissao (PaPrXPePerfil, PaPrXPeMenu,PaPrXPeUnidade,PaPrXPeInserir,PaPrXPeVisualizar,PaPrXPeAtualizar,PaPrXPeExcluir,PaPrXPeSuperAdmin) VALUES ";

    $cont = 0;
    foreach($sqlPadraoPerfilXPermissao as $item){
      $sqlPerfilPadrao .= $item;
      $cont++;

      if ($cont > 990){
        // Insere na base para não atingir o limite de 1000 linhas por INSERT
        $sqlPerfilPadrao = substr($sqlPerfilPadrao, 0, -1);
        $result = $conn->query($sqlPerfilPadrao);

        // recria o inserir em PerfilXPermissao -------------------------------------------------------------------
        $sqlPerfilPadrao = "INSERT INTO PadraoPerfilXPermissao (PaPrXPePerfil, PaPrXPeMenu,PaPrXPeUnidade,PaPrXPeInserir,PaPrXPeVisualizar,PaPrXPeAtualizar,PaPrXPeExcluir,PaPrXPeSuperAdmin) VALUES ";

        $cont = 0;
      }
    }

    if($cont > 0){
      $sqlPerfilPadrao = substr($sqlPerfilPadrao, 0, -1);
      $result = $conn->query($sqlPerfilPadrao);
    }

    echo json_encode(true);
  }elseif($typeRequest == "PERFILPERMISSAO"){
    $unidadeIdNovo = $_POST['unidadeIdNovo'];
    // esse select traz todos os perfis da unidade nova
    $sql = "SELECT PerfiId,PerfiNome,PerfiChave,PerfiStatus,PerfiUsuarioAtualizador,PerfiUnidade,PerfiPadrao
      FROM Perfil
      WHERE PerfiUnidade = $unidadeIdNovo";
    $result = $conn->query($sql);
    $rowPerfil = $result->fetchAll(PDO::FETCH_ASSOC);

    // esse select traz todos os perfis padrões LAMPARINAS com as respectivas PerfiChave
    $sql = "SELECT PaPerPerfil,PaPerMenu,MenuSetorPublico,MenuSetorPrivado,PaPerVisualizar,
      PaPerAtualizar,PaPerExcluir,PaPerInserir,PaPerSuperAdmin,PerfiChave
      FROM PadraoPermissao
      JOIN Perfil ON PerfiId = PaPerPerfil
      JOIN Menu ON MenuId = PaPerMenu";
    $result = $conn->query($sql);
    $rowPerfilPadrao = $result->fetchAll(PDO::FETCH_ASSOC);

    //Recupera o parâmetro pra saber se a empresa é pública ou privada
    $sqlParametro = "SELECT ParamEmpresaPublica 
      FROM Parametro
      WHERE ParamEmpresa = ".$_SESSION['EmpreId'];
    $resultParametro = $conn->query($sqlParametro);
    $parametro = $resultParametro->fetch(PDO::FETCH_ASSOC);

    $empresa = $parametro['ParamEmpresaPublica'] ? 'Publica' : 'Privada';
    $cont = 0;

    // inserir em PerfilXPermissao -------------------------------------------------------------------
      // $sqlPerfilXPermissao = "INSERT INTO PerfilXPermissao (PrXPePerfil,PrXPeMenu,PrXPeUnidade,PrXPeInserir,PrXPeVisualizar,
      // PrXPeAtualizar,PrXPeExcluir,PrXPeSuperAdmin)
      // VALUES ";

      // inserir em PadraoPerfilXPermissao -------------------------------------------------------------
      
      // $sqlPadraoPerfilXPermissao = "INSERT INTO PadraoPerfilXPermissao (PaPrXPePerfil, PaPrXPeMenu,PaPrXPeUnidade,PaPrXPeInserir,
      // PaPrXPeVisualizar,PaPrXPeAtualizar,PaPrXPeExcluir,PaPrXPeSuperAdmin)
      // VALUES ";

      // nesse laço para cada perfil ele procura um perfil padrão que contenha a mesma PerfiChave para
      // utilizar os campos: PaPerVisualizar, PaPerAtualizar, PaPerExcluir, PaPerSuperAdmin
      
      // foreach ($rowPerfil as $itemPerfil){
      //   foreach($rowPerfilPadrao as $rowPerPad){
      //     if($itemPerfil['PerfiChave'] == $rowPerPad['PerfiChave']){
      //       // nessa parte é verificado se a empresa é publica ou privada, assim verifica-se se cada menu
      //       // possui permissão de aparecer para esses tipos (publica/privada), caso publica, por exemplo, é atribuido o valor predefinido
      //       // no padrão caso o menu possa ser vispo por empresa publica, caso contrario ira setar como 0
      //       // permitindo que, caso necessario, o administrador altere essa condição apenas na empresa específica
      //       $empresaPermissao = $empresa=='Publica'?$rowPerPad['MenuSetorPublico']:$rowPerPad['MenuSetorPrivado'];
      //       $visualizar = $empresaPermissao?$rowPerPad['PaPerVisualizar']:0;

      //       $sqlPerfilXPermissao .= " ('$itemPerfil[PerfiId]', '$rowPerPad[PaPerMenu]', '$unidadeIdNovo', '$rowPerPad[PaPerInserir]',
      //       '$visualizar', '$rowPerPad[PaPerAtualizar]', '$rowPerPad[PaPerExcluir]', '$rowPerPad[PaPerSuperAdmin]'),";
      
      //       $sqlPadraoPerfilXPermissao .=  "('$itemPerfil[PerfiId]', '$rowPerPad[PaPerMenu]', '$unidadeIdNovo', '$rowPerPad[PaPerInserir]',
      //       '$visualizar', '$rowPerPad[PaPerAtualizar]', '$rowPerPad[PaPerExcluir]', '$rowPerPad[PaPerSuperAdmin]'),";

      //       $cont++;

      //       $new = $sqlPerfilXPermissao;
      //       if($old == $new){
      //         array_push($lista, $new);
      //       }
      //       $old = $new;

      //       if ($cont > 990){
      //         // Insere na base para não atingir o limite de 1000 linhas por INSERT
      //         $sqlPerfilXPermissao = substr($sqlPerfilXPermissao, 0, -1);
      //         $sqlPadraoPerfilXPermissao = substr($sqlPadraoPerfilXPermissao, 0, -1);
      //         $result = $conn->query($sqlPerfilXPermissao);
      //         $result = $conn->query($sqlPadraoPerfilXPermissao);

      //         // recria o inserir em PerfilXPermissao -------------------------------------------------------------------
      //         $sqlPerfilXPermissao = "INSERT INTO PerfilXPermissao (PrXPePerfil,PrXPeMenu,PrXPeUnidade,PrXPeInserir,PrXPeVisualizar,
      //         PrXPeAtualizar,PrXPeExcluir,PrXPeSuperAdmin) VALUES ";

      //         // recria o inserir em PadraoPerfilXPermissao -------------------------------------------------------------
      //         $sqlPadraoPerfilXPermissao = "INSERT INTO PadraoPerfilXPermissao (PaPrXPePerfil, PaPrXPeMenu,PaPrXPeUnidade,PaPrXPeInserir,
      //         PaPrXPeVisualizar,PaPrXPeAtualizar,PaPrXPeExcluir,PaPrXPeSuperAdmin) VALUES ";

      //         $cont = 0;
      //       }
      //     }
      //   }
      // }
      // if($cont > 0){
      //   $sqlPerfilXPermissao = substr($sqlPerfilXPermissao, 0, -1);
      //   $sqlPadraoPerfilXPermissao = substr($sqlPadraoPerfilXPermissao, 0, -1);
      //   $result = $conn->query($sqlPerfilXPermissao);
      //   $result = $conn->query($sqlPadraoPerfilXPermissao);
      // }
    //
    $sqlPerfilXPermissao = [];
    $sqlPadraoPerfilXPermissao = [];

    // está gerando uma lista de itens a ser inserido e depois verifica se existe itens duplicados
    // para não cadastrar duplicatos no banco

    foreach ($rowPerfil as $itemPerfil){
      foreach($rowPerfilPadrao as $rowPerPad){
        if($itemPerfil['PerfiChave'] == $rowPerPad['PerfiChave']){
          // nessa parte é verificado se a empresa é publica ou privada, assim verifica-se se cada menu
          // possui permissão de aparecer para esses tipos (publica/privada), caso publica, por exemplo, é atribuido o valor predefinido
          // no padrão caso o menu possa ser vispo por empresa publica, caso contrario ira setar como 0
          // permitindo que, caso necessario, o administrador altere essa condição apenas na empresa específica
          $empresaPermissao = $empresa=='Publica'?$rowPerPad['MenuSetorPublico']:$rowPerPad['MenuSetorPrivado'];
          $visualizar = $empresaPermissao?$rowPerPad['PaPerVisualizar']:0;

          
          array_push($sqlPerfilXPermissao,"('$itemPerfil[PerfiId]', '$rowPerPad[PaPerMenu]', '$unidadeIdNovo', '$rowPerPad[PaPerInserir]',
          '$visualizar', '$rowPerPad[PaPerAtualizar]', '$rowPerPad[PaPerExcluir]', '$rowPerPad[PaPerSuperAdmin]'),");
        }
      }
    }

    $sqlPerfilXPermissao = array_unique($sqlPerfilXPermissao);

    $sqlPerfil = "INSERT INTO PerfilXPermissao (PrXPePerfil,PrXPeMenu,PrXPeUnidade,PrXPeInserir,PrXPeVisualizar,PrXPeAtualizar,PrXPeExcluir,PrXPeSuperAdmin) VALUES ";
    
    $cont = 0;
    foreach($sqlPerfilXPermissao as $item){
      $sqlPerfil .= $item;
      $cont++;

      if ($cont > 990){
        // Insere na base para não atingir o limite de 1000 linhas por INSERT
        $sqlPerfil = substr($sqlPerfil, 0, -1);
        $result = $conn->query($sqlPerfil);

        // recria o inserir em PerfilXPermissao -------------------------------------------------------------------
        $sqlPerfil = "INSERT INTO PerfilXPermissao (PrXPePerfil,PrXPeMenu,PrXPeUnidade,PrXPeInserir,PrXPeVisualizar,PrXPeAtualizar,PrXPeExcluir,PrXPeSuperAdmin) VALUES ";

        $cont = 0;
      }
    }
    
    if($cont > 0){
      $sqlPerfil = substr($sqlPerfil, 0, -1);
      $result = $conn->query($sqlPerfil);
    }
    echo json_encode(true);
  }elseif($typeRequest == "GRUPOCONTAS"){
    $unidadeIdNovo = $_POST['unidadeIdNovo'];

    // Alimentando GrupoConta com dados da tabela GrupoContaPadrao

    $sql = "SELECT ParamEmpresaPublica FROM Parametro WHERE ParamEmpresa = ".$EmpresaId;
    $result = $conn->query($sql);
    $rowParametro = $result->fetch(PDO::FETCH_ASSOC);

    $sql = "SELECT GrCoPId, GrCoPCodigo, GrCoPNomePublico, GrCoPNomePrivado, GrCoPStatus
    FROM GrupoContaPadrao";
    $result = $conn->query($sql);
    $rowGrupoConta = $result->fetchAll(PDO::FETCH_ASSOC);

    if(COUNT($rowGrupoConta)){
      $sql = "INSERT INTO GrupoConta(GrConCodigo,GrConNome,GrConStatus,
      GrConUsuarioAtualizador,GrConUnidade) VALUES ";
      $count = 0;

      foreach($rowGrupoConta as $GrupoConta){
        $codigo = $GrupoConta['GrCoPCodigo'];
        $nome = $rowParametro['ParamEmpresaPublica']==1?$GrupoConta['GrCoPNomePublico']:$GrupoConta['GrCoPNomePrivado'];
        $status = $GrupoConta['GrCoPStatus'];
        $usuario = $_SESSION['UsuarId'];

        $sql .= "($codigo, '$nome', $status, $usuario, $unidadeIdNovo),";
        $count++;

        if($count > 800){
          $sql = substr_replace($sql ,"", -1);
          $conn->query($sql);
          $sql = "INSERT INTO GrupoConta(GrConCodigo,GrConNome,GrConStatus,
          GrConUsuarioAtualizador,GrConUnidade) VALUES ";
          $count = 0;
        }
      }
      if($count<=800){
        $sql = substr_replace($sql ,"", -1);
        $conn->query($sql);
      }
    }
    echo json_encode(true);
  }elseif($typeRequest == "LOCALESTOQUE"){
    $unidadeIdNovo = $_POST['unidadeIdNovo'];

    /* Após criar a Unidade deve se cadastrar o Local de Estoque Padrão para essa Unidade nova criada */
    $sql = "INSERT INTO LocalEstoque (LcEstNome, LcEstChave, LcEstStatus, LcEstUsuarioAtualizador, LcEstUnidade)
          VALUES (:sNome, :sChave, :bStatus, :iUsuarioAtualizador, :iUnidade)";
    $result = $conn->prepare($sql);
        
    $result->execute(array(
      ':sNome' => 'GESTAO ANTERIOR',
      ':sChave' => 'GESTAOANTERIOR',
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));
    echo json_encode(true);
  }elseif($typeRequest == "FORMASPAGAMENTO"){
    $unidadeIdNovo = $_POST['unidadeIdNovo'];

    $sql = "INSERT INTO FormaPagamento (FrPagNome, FrPagChave, FrPagStatus, FrPagUsuarioAtualizador, FrPagUnidade)
        VALUES (:sNome, :sChave, :bStatus, :iUsuarioAtualizador, :iUnidade)";
    $result = $conn->prepare($sql);
        
    $result->execute(array(
      ':sNome' => 'Boleto Bancário',
      ':sChave' => 'BOLETO',
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));
    $result->execute(array(
      ':sNome' => 'Cartão de Crédito',
      ':sChave' => 'CARTAOCREDITO',
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));				
    $result->execute(array(
      ':sNome' => 'Cartão de Débito',
      ':sChave' => 'CARTAODEBITO',
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));
    $result->execute(array(
      ':sNome' => 'Cheque',
      ':sChave' => 'CHEQUE',
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));	
    $result->execute(array(
      ':sNome' => 'Dinheiro',
      ':sChave' => 'DINHEIRO',
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));	
    echo json_encode(true);
  }elseif($typeRequest == "CLASSIFICACAO"){
    $unidadeIdNovo = $_POST['unidadeIdNovo'];

    $sql = "INSERT INTO AtendimentoClassificacao (AtClaNome, AtClaModelo, AtClaChave, AtClaStatus, AtClaUsuarioAtualizador, AtClaUnidade) 
            VALUES ( :sNome, :sModelo, :sChave, :bStatus, :iUsuarioAtualizador, :iUnidade)";
    $result = $conn->prepare($sql);
        
    $result->execute(array(
      ':sNome' => 'Ambulatorial',
      ':sModelo' => 'A',
      ':sChave' => 'AMBULATORIAL',
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));
    $result->execute(array(
      ':sNome' => 'Eletivo',
      ':sModelo' => 'E',
      ':sChave' => 'ELETIVO',
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));				
    $result->execute(array(
      ':sNome' => 'Hospitalar',
      ':sModelo' => 'H',
      ':sChave' => 'HOSPITALAR',
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));
    $result->execute(array(
      ':sNome' => 'Odontológico',
      ':sModelo' => 'O',
      ':sChave' => 'ODONTOLOGICO',
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));
    echo json_encode(true);
  }elseif($typeRequest == "CLASSIFICACAORISCO"){
    $unidadeIdNovo = $_POST['unidadeIdNovo'];

    $sql = "INSERT INTO AtendimentoClassificacaoRisco (AtClRNome, AtClRTempo, AtClRCor, AtClRDeterminantes, AtClRStatus, AtClRUsuarioAtualizador, AtClRUnidade) 
            VALUES ( :sNome, :sTempo, :sCor, :sDeterminantes, :bStatus, :iUsuarioAtualizador, :iUnidade)";
    $result = $conn->prepare($sql);

    $result->execute(array(
      ':sNome' => 'EMERGENTE',
      ':sTempo' => 0,
      ':sCor' => '#fa0000',
      ':sDeterminantes' => 'Pacientes que necessitam de atendimento imediato, com risco iminente de morte.',
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));
    $result->execute(array(
      ':sNome' => 'MUITO URGENTE',
      ':sTempo' => 10,
      ':sCor' => '#ff630f',
      ':sDeterminantes' => 'Paciente grave e atendimento necessário em 10 minutos',
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));
    $result->execute(array(
      ':sNome' => 'URGENTE',
      ':sTempo' => 60,
      ':sCor' => '#fbff00',
      ':sDeterminantes' => 'Paciente com gravidade moderada. Deve ser atendido em até 60 minutos.',
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));
    $result->execute(array(
      ':sNome' => 'POUCO URGENTE',
      ':sTempo' => 120,
      ':sCor' => '#00ff1e',
      ':sDeterminantes' => 'Pouco-urgente, com atendimento em até 120 minutos.',
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));
    $result->execute(array(
      ':sNome' => 'NÃO URGENTE',
      ':sTempo' => 240,
      ':sCor' => '#0008ff',
      ':sDeterminantes' => 'Não-urgente, com atendimento de espera em até 240 minutos, pois não apresenta risco à saúde.',
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));
    echo json_encode(true);
  }elseif($typeRequest == "CENTROCUSTO"){
    $unidadeIdNovo = $_POST['unidadeIdNovo'];

    $sql = "INSERT INTO CentroCusto (CnCusCodigo, CnCusNome, CnCusDetalhamento, CnCusStatus, CnCusUsuarioAtualizador, CnCusUnidade)
            VALUES (:iCodigo, :sNome, :sDetalhamento, :iStatus, :iUsuarioAtualizador, :iUnidade)";
    $result = $conn->prepare($sql);

    $result->execute(array(
      ':iCodigo' => 01,
      ':sNome' =>'Atendimento Ambulatorial',
      ':sDetalhamento' =>'Centro de custo padrão do sistema',
      ':iStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));
    $result->execute(array(
      ':iCodigo' => 02,
      ':sNome' =>'Atendimento Eletivo',
      ':sDetalhamento' =>'Centro de custo padrão do sistema',
      ':iStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));
    $result->execute(array(
      ':iCodigo' => 03,
      ':sNome' =>'Atendimento Internação',
      ':sDetalhamento' =>'Centro de custo padrão do sistema',
      ':iStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));
    echo json_encode(true);
  }elseif($typeRequest == "MODALIDADE"){
    $unidadeIdNovo = $_POST['unidadeIdNovo'];

    $sql = "INSERT INTO AtendimentoModalidade (AtModNome, AtModChave, AtModTipoRecebimento, AtModSituacao, AtModUsuarioAtualizador, AtModUnidade)
            VALUES (:sNome, :sChave,:sRecebimento, :bStatus, :iUsuarioAtualizador, :iUnidade)";
    $result = $conn->prepare($sql);
          
    $result->execute(array(
      ':sNome' =>'Particular com desconto',
      ':sChave' =>'PARTICULARCOMDESCONTO',
      ':sRecebimento' =>'À Vista',
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));

    $result->execute(array(
      ':sNome' =>'Particular Sem Desconto',
      ':sChave' =>'PARTICULARSEMDESCONTO',
      ':sRecebimento' =>'À Vista',
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));

    $result->execute(array(
      ':sNome' =>'Convênio UNIMED',
      ':sChave' =>'CONVENIOUNIMED',
      ':sRecebimento' =>'À Prazo',
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));

    $result->execute(array(
      ':sNome' =>'Convênio CAMED',
      ':sChave' =>'CONVENIOCAMED',
      ':sRecebimento' =>'À Prazo',
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));

    $result->execute(array(
      ':sNome' =>'Convênio SUS',
      ':sChave' =>'CONVENIOSUS',
      ':sRecebimento' =>'À Prazo',
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));

    $result->execute(array(
      ':sNome' =>'Convênio Bradesco',
      ':sChave' =>'CONVENIOBRADESCO',
      ':sRecebimento' =>'À Prazo',
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));

    $result->execute(array(
      ':sNome' =>'Convênio Cassi',
      ':sChave' =>'CONVENIOCASSI',
      ':sRecebimento' =>'À Prazo',
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));

    $result->execute(array(
      ':sNome' =>'Convênio Sul América',
      ':sChave' =>'CONVENIOSULAMERICA',
      ':sRecebimento' =>'À Prazo',
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));
    
    echo json_encode(true);

  }elseif($typeRequest == "ESPECIALIDADELEITO"){
    $unidadeIdNovo = $_POST['unidadeIdNovo'];

    $sql = "INSERT INTO EspecialidadeLeito (EsLeiCodigo, EsLeiNome, EsLeiEspecialidadePai, EsLeiStatus, EsLeiUsuarioAtualizador, EsLeiUnidade)
					 VALUES (:iCodigo, :sNome, :iEspecialidadePai, :bStatus, :iUsuarioAtualizador, :iUnidade)";
		$result = $conn->prepare($sql);

    $result->execute(array(
      ':iCodigo' => 1,
      ':sNome' =>'CLÍNICO',
      ':iEspecialidadePai' => NULL,
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));
    $insertClinicoId = $conn->lastInsertId();

    $result->execute(array(
      ':iCodigo' => 2,
      ':sNome' =>'CIRÚRGICO',
      ':iEspecialidadePai' => NULL,
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));
    $insertCirurgicoId = $conn->lastInsertId();

    $result->execute(array(
      ':iCodigo' => 2.1,
      ':sNome' =>'ORTOPÉDICO',
      ':iEspecialidadePai' => $insertCirurgicoId,
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));
    $insertOrtopedicoId = $conn->lastInsertId();

    $result->execute(array(
      ':iCodigo' => 3,
      ':sNome' =>'OBSTÉTRICO',
      ':iEspecialidadePai' => NULL,
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));
    $insertObstetricoId = $conn->lastInsertId();

    $result->execute(array(
      ':iCodigo' => 3.1,
      ':sNome' =>'PRÉ-PARTO',
      ':iEspecialidadePai' => $insertObstetricoId,
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));
    $insertPrePartoId = $conn->lastInsertId();

    $result->execute(array(
      ':iCodigo' => 4,
      ':sNome' =>'OBSERVAÇÃO',
      ':iEspecialidadePai' => NULL,
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));
    $insertObservacaoId = $conn->lastInsertId();

    $result->execute(array(
      ':iCodigo' => 4.1,
      ':sNome' =>'APOIO PARA PROCEDIMENTOS DIAGNÓSTICOS E/OU TERAPÊUTICOS',
      ':iEspecialidadePai' => $insertObservacaoId,
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));
    $insertApoioId = $conn->lastInsertId();

    $result->execute(array(
      ':iCodigo' => 4.2,
      ':sNome' =>'URGÊNCIA E EMERGÊNCIA',
      ':iEspecialidadePai' => $insertObservacaoId,
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));
    $insertUrgenciaEmergenciaId = $conn->lastInsertId();

    $result->execute(array(
      ':iCodigo' => 5,
      ':sNome' =>'PEDIÁTRICO',
      ':iEspecialidadePai' => NULL,
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));
    $insertPediatricoId = $conn->lastInsertId();

    $result->execute(array(
      ':iCodigo' => 5.1,
      ':sNome' =>'NEONATAL',
      ':iEspecialidadePai' => $insertPediatricoId,
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));
    $insertNeoNatalId = $conn->lastInsertId();

    $result->execute(array(
      ':iCodigo' => 5.2,
      ':sNome' =>'BERÇARIO',
      ':iEspecialidadePai' => $insertPediatricoId,
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));
    $insertBercarioId = $conn->lastInsertId();

    $result->execute(array(
      ':iCodigo' => 6,
      ':sNome' =>'ISOLAMENTO',
      ':iEspecialidadePai' => NULL,
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));
    $insertIsolamentoId = $conn->lastInsertId();

    $result->execute(array(
      ':iCodigo' => 6.1,
      ':sNome' =>'ISOLAMENTO REVERSO',
      ':iEspecialidadePai' => $insertIsolamentoId,
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));
    $insertIsolamentoReversoId = $conn->lastInsertId();

    $result->execute(array(
      ':iCodigo' => 7,
      ':sNome' =>'UTI',
      ':iEspecialidadePai' => NULL,
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));
    $insertUtiId = $conn->lastInsertId();

    $result->execute(array(
      ':iCodigo' => 7.1,
      ':sNome' =>'UCI',
      ':iEspecialidadePai' => $insertUtiId,
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));
    $insertUciId = $conn->lastInsertId();


    $sql = "INSERT INTO EspecialidadeLeitoXClassificacao (ELXClEspecialidadeLeito, ELXClClassificacao, ELXClUnidade)
						VALUES (:iEspecialidadeLeito, :iClassificacao, :iUnidade)";
		$result = $conn->prepare($sql);
	
    $result->execute(array(
      ':iEspecialidadeLeito' => $insertClinicoId,
      ':iClassificacao' => 'H',
      ':iUnidade' => $unidadeIdNovo			
    ));

    $result->execute(array(
      ':iEspecialidadeLeito' => $insertCirurgicoId,
      ':iClassificacao' => 'H',
      ':iUnidade' => $unidadeIdNovo			
    ));

    $result->execute(array(
      ':iEspecialidadeLeito' => $insertOrtopedicoId,
      ':iClassificacao' => 'H',
      ':iUnidade' => $unidadeIdNovo			
    ));

    $result->execute(array(
      ':iEspecialidadeLeito' => $insertObstetricoId,
      ':iClassificacao' => 'H',
      ':iUnidade' => $unidadeIdNovo			
    ));

    $result->execute(array(
      ':iEspecialidadeLeito' => $insertPrePartoId,
      ':iClassificacao' => 'A',
      ':iUnidade' => $unidadeIdNovo			
    ));

    $result->execute(array(
      ':iEspecialidadeLeito' => $insertPrePartoId,
      ':iClassificacao' => 'H',
      ':iUnidade' => $unidadeIdNovo			
    ));

    $result->execute(array(
      ':iEspecialidadeLeito' => $insertObservacaoId,
      ':iClassificacao' => 'A',
      ':iUnidade' => $unidadeIdNovo			
    ));

    $result->execute(array(
      ':iEspecialidadeLeito' => $insertObservacaoId,
      ':iClassificacao' => 'H',
      ':iUnidade' => $unidadeIdNovo			
    ));

    $result->execute(array(
      ':iEspecialidadeLeito' => $insertApoioId,
      ':iClassificacao' => 'A',
      ':iUnidade' => $unidadeIdNovo			
    ));

    $result->execute(array(
      ':iEspecialidadeLeito' => $insertApoioId,
      ':iClassificacao' => 'H',
      ':iUnidade' => $unidadeIdNovo			
    ));

    $result->execute(array(
      ':iEspecialidadeLeito' => $insertUrgenciaEmergenciaId,
      ':iClassificacao' => 'A',
      ':iUnidade' => $unidadeIdNovo			
    ));

    $result->execute(array(
      ':iEspecialidadeLeito' => $insertUrgenciaEmergenciaId,
      ':iClassificacao' => 'H',
      ':iUnidade' => $unidadeIdNovo			
    ));

    $result->execute(array(
      ':iEspecialidadeLeito' => $insertPediatricoId,
      ':iClassificacao' => 'H',
      ':iUnidade' => $unidadeIdNovo			
    ));

    $result->execute(array(
      ':iEspecialidadeLeito' => $insertNeoNatalId,
      ':iClassificacao' => 'H',
      ':iUnidade' => $unidadeIdNovo			
    ));

    $result->execute(array(
      ':iEspecialidadeLeito' => $insertBercarioId,
      ':iClassificacao' => 'H',
      ':iUnidade' => $unidadeIdNovo			
    ));

    $result->execute(array(
      ':iEspecialidadeLeito' => $insertIsolamentoId,
      ':iClassificacao' => 'H',
      ':iUnidade' => $unidadeIdNovo			
    ));

    $result->execute(array(
      ':iEspecialidadeLeito' => $insertIsolamentoReversoId,
      ':iClassificacao' => 'H',
      ':iUnidade' => $unidadeIdNovo			
    ));

    $result->execute(array(
      ':iEspecialidadeLeito' => $insertUtiId,
      ':iClassificacao' => 'H',
      ':iUnidade' => $unidadeIdNovo			
    ));

    $result->execute(array(
      ':iEspecialidadeLeito' => $insertUciId,
      ':iClassificacao' => 'H',
      ':iUnidade' => $unidadeIdNovo			
    ));


    $sql = "INSERT INTO EspecialidadeLeitoXAgrupador (ELXAgEspecialidadeLeito, ELXAgLeitoAgrupador, ELXAgUnidade)
						VALUES (:iEspecialidadeLeito, :iLeitoAgrupador, :iUnidade)";
		$result = $conn->prepare($sql);
	
    $result->execute(array(
      ':iEspecialidadeLeito' => $insertClinicoId,
      ':iLeitoAgrupador' => 3,
      ':iUnidade' => $unidadeIdNovo			
    ));

    $result->execute(array(
      ':iEspecialidadeLeito' => $insertClinicoId,
      ':iLeitoAgrupador' => 4,
      ':iUnidade' => $unidadeIdNovo			
    ));

    $result->execute(array(
      ':iEspecialidadeLeito' => $insertCirurgicoId,
      ':iLeitoAgrupador' => 1,
      ':iUnidade' => $unidadeIdNovo			
    ));

    $result->execute(array(
      ':iEspecialidadeLeito' => $insertCirurgicoId,
      ':iLeitoAgrupador' => 2,
      ':iUnidade' => $unidadeIdNovo			
    ));

    $result->execute(array(
      ':iEspecialidadeLeito' => $insertOrtopedicoId,
      ':iLeitoAgrupador' => 1,
      ':iUnidade' => $unidadeIdNovo			
    ));

    $result->execute(array(
      ':iEspecialidadeLeito' => $insertOrtopedicoId,
      ':iLeitoAgrupador' => 2,
      ':iUnidade' => $unidadeIdNovo			
    ));

    $result->execute(array(
      ':iEspecialidadeLeito' => $insertObstetricoId,
      ':iLeitoAgrupador' => 6,
      ':iUnidade' => $unidadeIdNovo			
    ));

    $result->execute(array(
      ':iEspecialidadeLeito' => $insertPrePartoId,
      ':iLeitoAgrupador' => 6,
      ':iUnidade' => $unidadeIdNovo			
    ));

    $result->execute(array(
      ':iEspecialidadeLeito' => $insertObservacaoId,
      ':iLeitoAgrupador' => 7,
      ':iUnidade' => $unidadeIdNovo			
    ));

    $result->execute(array(
      ':iEspecialidadeLeito' => $insertApoioId,
      ':iLeitoAgrupador' => 7,
      ':iUnidade' => $unidadeIdNovo			
    ));

    $result->execute(array(
      ':iEspecialidadeLeito' => $insertUrgenciaEmergenciaId,
      ':iLeitoAgrupador' => 7,
      ':iUnidade' => $unidadeIdNovo			
    ));

    $result->execute(array(
      ':iEspecialidadeLeito' => $insertPediatricoId,
      ':iLeitoAgrupador' => 8,
      ':iUnidade' => $unidadeIdNovo			
    ));

    $result->execute(array(
      ':iEspecialidadeLeito' => $insertNeoNatalId,
      ':iLeitoAgrupador' => 8,
      ':iUnidade' => $unidadeIdNovo			
    ));

    $result->execute(array(
      ':iEspecialidadeLeito' => $insertBercarioId,
      ':iLeitoAgrupador' => 8,
      ':iUnidade' => $unidadeIdNovo			
    ));

    $result->execute(array(
      ':iEspecialidadeLeito' => $insertIsolamentoId,
      ':iLeitoAgrupador' => 5,
      ':iUnidade' => $unidadeIdNovo			
    ));

    $result->execute(array(
      ':iEspecialidadeLeito' => $insertIsolamentoReversoId,
      ':iLeitoAgrupador' => 5,
      ':iUnidade' => $unidadeIdNovo			
    ));

    $result->execute(array(
      ':iEspecialidadeLeito' => $insertUtiId,
      ':iLeitoAgrupador' => 9,
      ':iUnidade' => $unidadeIdNovo			
    ));

    $result->execute(array(
      ':iEspecialidadeLeito' => $insertUciId,
      ':iLeitoAgrupador' => 9,
      ':iUnidade' => $unidadeIdNovo			
    ));
    
    echo json_encode(true);
  }elseif($typeRequest == "ALTA"){
    $unidadeIdNovo = $_POST['unidadeIdNovo'];

    $sql = "SELECT TpAltId, TpAltChave, SituaChave
            FROM TipoAlta
            JOIN Situacao on SituaId = TpAltStatus
            WHERE SituaChave = 'ATIVO' ";
    $result = $conn->query($sql);
    $rowTipoAlta = $result->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rowTipoAlta as $item){

     if ($item['TpAltChave']=='PORALTAMEDICA'){
        $tipoAltaMedica = $item['TpAltId'];
      }
     if ($item['TpAltChave']=='PORPERMANENCIA'){
        $tipoPermanencia = $item['TpAltId'];
      }
      if ($item['TpAltChave']=='PORALTAADMINISTRATIVA'){
        $tipoAltaAdministrativa = $item['TpAltId'];
      }
      if ($item['TpAltChave']=='PORTRANSFERENCIA'){
        $tipoTransferencia = $item['TpAltId'];
      }
     if ($item['TpAltChave']=='PORPROCEDIMENTOSDEPARTO'){
        $tipoProcedimentoParto = $item['TpAltId'];
      }
      if ($item['TpAltChave']=='POROBITO'){
        $tipoObito = $item['TpAltId'];
      }

    }

    $sql = "INSERT INTO MotivoAlta (MtAltNome, MtAltTipoAlta, MtAltStatus, MtAltUsuarioAtualizador, MtAltUnidade)
					  VALUES (:sNome, :sTipoAlta, :bStatus, :iUsuarioAtualizador, :iUnidade)";
		$result = $conn->prepare($sql);
					
    $result->execute(array(
      ':sNome' => 'Curado',
      ':sTipoAlta' => $tipoAltaMedica,
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));
    $result->execute(array(
      ':sNome' => 'Melhorado',
      ':sTipoAlta' => $tipoAltaMedica,
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));
    $result->execute(array(
      ':sNome' => 'A Pedido',
      ':sTipoAlta' => $tipoAltaMedica,
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));
    $result->execute(array(
      ':sNome' => 'Com Previsão de Retorno',
      ':sTipoAlta' => $tipoAltaMedica,
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));
    $result->execute(array(
      ':sNome' => 'Por Evasão',
      ':sTipoAlta' => $tipoAltaMedica,
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));
    $result->execute(array(
      ':sNome' => 'Por outros Motivos',
      ':sTipoAlta' => $tipoAltaMedica,
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));

    $result->execute(array(
      ':sNome' => 'Por características próprias da doença',
      ':sTipoAlta' => $tipoPermanencia,
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));
    $result->execute(array(
      ':sNome' => 'Por impossibilidade sócio familiar',
      ':sTipoAlta' => $tipoPermanencia,
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));
    $result->execute(array(
      ':sNome' => 'Por processo de doação de órgãos, tecidos e células - doador vivo',
      ':sTipoAlta' => $tipoPermanencia,
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));
    $result->execute(array(
      ':sNome' => 'Por processo de doação de órgãos, tecidos e células - doador morto',
      ':sTipoAlta' => $tipoPermanencia,
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));
    $result->execute(array(
      ':sNome' => 'Por mudança de procedimento',
      ':sTipoAlta' => $tipoPermanencia,
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));
    $result->execute(array(
      ':sNome' => 'Por reoperação',
      ':sTipoAlta' => $tipoPermanencia,
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));
    $result->execute(array(
      ':sNome' => 'Outros motivos',
      ':sTipoAlta' => $tipoPermanencia,
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));

    $result->execute(array(
      ':sNome' => 'Por Administrativa',
      ':sTipoAlta' => $tipoAltaAdministrativa,
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));
    $result->execute(array(
      ':sNome' => 'Encerramento administrativo',
      ':sTipoAlta' => $tipoAltaAdministrativa,
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));
   
    $result->execute(array(
      ':sNome' => 'Transferido para outro estabelecimento',
      ':sTipoAlta' => $tipoTransferencia,
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));
    $result->execute(array(
      ':sNome' => 'Transferido para internação domiciliar',
      ':sTipoAlta' => $tipoTransferencia,
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));

    $result->execute(array(
      ':sNome' => 'Alta da mãe (puérpera) e do recém-nascido',
      ':sTipoAlta' => $tipoProcedimentoParto,
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));
    $result->execute(array(
      ':sNome' => 'Alta da mãe (puérpera) e permanência do recém-nascido',
      ':sTipoAlta' => $tipoProcedimentoParto,
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));
    $result->execute(array(
      ':sNome' => 'Alta da mãe (puérpera) e óbito do recém-nascido',
      ':sTipoAlta' => $tipoProcedimentoParto,
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));
    $result->execute(array(
      ':sNome' => 'Alta da mãe (puérpera) com óbito fetal',
      ':sTipoAlta' => $tipoProcedimentoParto,
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));
    $result->execute(array(
      ':sNome' => 'Óbito da gestante e do concepto',
      ':sTipoAlta' => $tipoProcedimentoParto,
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));
    $result->execute(array(
      ':sNome' => 'Óbito da mãe (puérpera) e alta do recém-nascido',
      ':sTipoAlta' => $tipoProcedimentoParto,
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));
    $result->execute(array(
      ':sNome' => 'Óbito da mãe (puérpera) e permanência do recém-nascido',
      ':sTipoAlta' => $tipoProcedimentoParto,
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));

    $result->execute(array(
      ':sNome' => 'Com declaração de óbito fornecida pelo medico assistente',
      ':sTipoAlta' => $tipoObito,
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));
    $result->execute(array(
      ':sNome' => 'Com declaração de óbito fornecida pelo Instituto Médico Legal – IML',
      ':sTipoAlta' => $tipoObito,
      ':bStatus' => 1,
      ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
      ':iUnidade' => $unidadeIdNovo
    ));

    echo json_encode([
      'titulo' => "Sucesso",
      'mensagem' => "Unidade incluída!!!",
      'status' => "success",
    ]);
  }
}catch(PDOException $e) {
  // $conn->rollback();
  $_SESSION['msg']['titulo'] = "Erro";
  $_SESSION['msg']['mensagem'] = "Erro ao incluir unidade!!!";
  $_SESSION['msg']['tipo'] = "error";

  echo json_encode([
    'type' => $typeRequest,
    'err' => $e,
    'sql' => $sql
  ]);die;
}
?>