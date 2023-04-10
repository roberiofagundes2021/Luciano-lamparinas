<?php
  include('global_assets/php/conexao.php');
  $unidade = $_SESSION['UnidadeId'];
  $perfil = $_SESSION['PerfiChave'];
  $userId = $_SESSION['UsuarId'];

  $sqlUser = "SELECT UsXUnPermissaoPerfil as UsuarPermissaoPerfil
  FROM Usuario
  JOIN EmpresaXUsuarioXPerfil ON EXUXPUsuario = UsuarId
  JOIN UsuarioXUnidade ON UsXUnEmpresaUsuarioPerfil = EXUXPId
  Where UsuarId = '$userId' and UsXUnUnidade = $unidade";

  $resultUserId = $conn->query($sqlUser);
  $usuaXPerm = $resultUserId->fetch(PDO::FETCH_ASSOC);

  $userPermission = (isset($usuaXPerm['UsuarPermissaoPerfil'])?$usuaXPerm['UsuarPermissaoPerfil']:0);

  $sqlPerfil = "SELECT PerfiId 
                FROM Perfil
                WHERE PerfiChave = '" . $perfil . "' and PerfiUnidade = " . $_SESSION['UnidadeId'];

  $resultPerfilId = $conn->query($sqlPerfil);
  $perfilId = $resultPerfilId->fetchAll(PDO::FETCH_ASSOC);
  $perfilId = $perfilId[0]['PerfiId'];

  //Recupera todos os módulos do sistema
  $sqlModulo = "SELECT ModulId, ModulOrdem, ModulNome, ModulStatus, SituaChave, SituaCor
                FROM Modulo 
                JOIN Situacao on ModulStatus = SituaId 
                ORDER BY ModulOrdem ASC";

  $resultModulo = $conn->query($sqlModulo);
  $modulo = $resultModulo->fetchAll(PDO::FETCH_ASSOC);

  //Recupera todos os menus do sistema caso esteja usando permissao personalizada

  if($usuaXPerm['UsuarPermissaoPerfil'] == 0){
    $sqlMenu = "SELECT MenuId, MenuNome, MenuUrl, MenuIco, MenuSubMenu, MenuModulo, MenuSetorPublico, MenuPosicao,
                MenuPai, MenuLevel, MenuOrdem, MenuStatus, SituaChave, MenuSetorPrivado,
                UsXPeVisualizar, UsXPeAtualizar, UsXPeExcluir, UsXPeInserir, UsXPeUnidade
                FROM Menu
                JOIN Situacao on MenuStatus = SituaId
                JOIN UsuarioXPermissao on UsXPeUsuario = '$userId'
                WHERE UsXPeUnidade = '$unidade' and UsXPeMenu = MenuId
                ORDER BY MenuOrdem ASC";
  }else{
    $sqlMenu = "SELECT MenuId, MenuNome, MenuUrl, MenuIco, MenuSubMenu, MenuModulo, MenuSetorPublico, MenuPosicao,
                MenuPai, MenuLevel, MenuOrdem, MenuStatus, SituaChave, PrXPeId, PrXPePerfil, MenuSetorPrivado,
                PrXPeMenu, PrXPeVisualizar, PrXPeAtualizar,  PrXPeExcluir, PrXPeInserir, PrXPeUnidade
                FROM Menu
                JOIN Situacao on MenuStatus = SituaId
                JOIN PerfilXPermissao on MenuId = PrXPeMenu
                WHERE PrXPePerfil = $perfilId and PrXPeUnidade  = $unidade
                ORDER BY MenuOrdem ASC";
  }
  $resultMenu = $conn->query($sqlMenu);
  $menu = $resultMenu->fetchAll(PDO::FETCH_ASSOC);
  $arrayPermissao = [];
  // primeiramente faz a varredura das visibilidade dos subMenu para setar a visibilidade do menuPai
  foreach($menu as $menuPai){
    // adiciona as paginas e suas permissões em um array
    if(strtoupper($menuPai['SituaChave']) == "ATIVO"){
      array_push($arrayPermissao, Array(
        'url'=>$menuPai['MenuUrl'],
        'posicao'=>$menuPai['MenuPosicao'],
        'visualizar'=>(isset($menuPai['UsXPeVisualizar'])?$menuPai['UsXPeVisualizar']:$menuPai['PrXPeVisualizar']),
        'inserir'=>(isset($menuPai['UsXPeInserir'])?$menuPai['UsXPeInserir']:$menuPai['PrXPeInserir']),
        'atualizar'=>(isset($menuPai['PrXPeAtualizar'])?$menuPai['PrXPeAtualizar']:$menuPai['UsXPeAtualizar']),
        'excluir'=>(isset($menuPai['PrXPeExcluir'])?$menuPai['PrXPeExcluir']:$menuPai['UsXPeExcluir']),
      ));
    }
    $position = array_search($menuPai, $menu);
    $menuContente = 0;

    // verifica em cada menuPai se existe algum submenu com visibilidade true, se sim ele e o menuPai será visivel
    foreach($menu as $subMenu){
      if ($menuPai['MenuId'] == $subMenu['MenuPai']){
        $visualizar = (isset($subMenu['UsXPeVisualizar'])?$subMenu['UsXPeVisualizar']:$subMenu['PrXPeVisualizar']);

        // altera a o valor do visualizar modulo para 1 caso tenha algo para exibir ou 0 se não houver
        if($visualizar == 1 && $subMenu['MenuPosicao']=='PRINCIPAL'){
          $menuContente = 1;
        }
        // seta a visibilidade do menuPai em 0 ou 1 de acordo com a visibilidae dos subMenus
        if(isset($menuPai['UsXPeVisualizar'])){$menu[$position]['UsXPeVisualizar'] = $menuContente;}
        else{$menu[$position]['PrXPeVisualizar'] = $menuContente;}
      }
    }
  }
  // adiciona o array em uma session para ser acessado em outras páginas
  $_SESSION['Permissoes'] = $arrayPermissao;

  // Faz uma varredura para identificar quais modulos irão aparecer de
  // acordo com a visibilidade dos menus já atualizadas
  foreach($modulo as $mod){
    $menuCont = 0;
    if($mod['SituaChave'] == strtoupper("ativo")){
      // percorre os menus para verificar se existe algum menu pertencente ao modulo que tenha visibilidade true
      foreach($menu as $men){
        if($men["SituaChave"] == strtoupper("ativo") && $men["MenuModulo"] == $mod["ModulId"] && $men['MenuPai']==0){
          $visualizar = (isset($men['UsXPeVisualizar'])?$men['UsXPeVisualizar']:$men['PrXPeVisualizar']);
          if($visualizar == 1 && $men['MenuPosicao'] == 'PRINCIPAL'){
            $menuCont = 1;
          }
        }
      }
      // seta o valor conteudo no modulo em 0 ou 1 de acordo com a visibilidade dos menus 
      $positionMenu = array_search($mod, $modulo);
      $modulo[$positionMenu]['conteudo'] = $menuCont;
    }
  }

  //Recupera o parâmetro pra saber se a empresa é pública ou privada
  // $sqlParametro = "SELECT ParamEmpresaPublica 
  //                  FROM Parametro
  //                  WHERE ParamEmpresa = ".$_SESSION['EmpreId'];
  // $resultParametro = $conn->query($sqlParametro);
  // $parametro = $resultParametro->fetch(PDO::FETCH_ASSOC);	
  
  // $empresa = $parametro['ParamEmpresaPublica'] ? 'Publica' : 'Privada';
?>
<head>
  <style>
		.recuar{
			padding-left: 36px !important;
		}
	</style>
</head>
<!-- Main sidebar -->
<div class="sidebar sidebar-dark sidebar-main sidebar-expand-md">

  <!-- Sidebar mobile toggler -->
  <div class="sidebar-mobile-toggler text-center">
    <a href="#" class="sidebar-mobile-main-toggle">
      <i class="icon-arrow-left8"></i>
    </a>
    <span class="font-weight-semibold">Navigation</span>
    <a href="#" class="sidebar-mobile-expand">
      <i class="icon-screen-full"></i>
      <i class="icon-screen-normal"></i>
    </a>
  </div>
  <!-- /sidebar mobile toggler -->


  <!-- Sidebar content -->
  <div class="sidebar-content">

    <!-- User menu -->
    <div class="sidebar-user-material">
      <div class="sidebar-user-material-body">
        <div class="card-body text-center">
          <a href="index.php">
            <!-- src="global_assets/images/placeholders/placeholder.jpg" class="rounded-circle shadow-1 -->
            <img src="global_assets/images/lamparinas/logo-lamparinas_200x200.jpg" class="img-fluid shadow-5 mb-3" width="100" height="100" alt="" style="padding-top:8px;visibility:hidden">
          </a>
          <h6 class="mb-0 text-white text-shadow-dark"><?php //echo nomeSobrenome($_SESSION['UsuarNome'],2); ?></h6>
          <span class="font-size-sm text-white text-shadow-dark"><?php //echo $_SESSION['UnidadeNome']; ?></span>
        </div>

        <div class="sidebar-user-material-footer" style="margin-top:40px;">
          <a href="#user-nav" class="d-flex justify-content-between align-items-center text-shadow-dark dropdown-toggle" data-toggle="collapse"><span>Minha Conta</span></a>
        </div>
      </div>

      <div class="collapse" id="user-nav">
        <ul class="nav nav-sidebar">
          <li class="nav-item">
            <a href="meuPerfil.php" class="nav-link">
              <i class="icon-user-plus"></i>
              <span>Meu Perfil</span>
            </a>
          </li>
          <!--<li class="nav-item">
								<a href="#" class="nav-link">
									<i class="icon-coins"></i>
									<span>Minha bandeja</span>
								</a>
							</li>-->
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="icon-comment-discussion"></i>
              <span>Minha bandeja</span>
              <span class="badge bg-teal-400 badge-pill align-self-center ml-auto">5</span>
            </a>
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="icon-cog5"></i>
              <span>Configurar Conta</span>
            </a>
          </li>
          <li class="nav-item">
            <a href="sair.php" class="nav-link">
              <i class="icon-switch2"></i>
              <span>Sair</span>
            </a>
          </li>
        </ul>
      </div>
    </div>
    <!-- /user menu -->


    <!-- Main navigation -->
    <div class="card card-sidebar-mobile">
      <ul class="nav nav-sidebar" data-nav-type="accordion">
      <?php
          if($_SESSION['PerfiChave'] == "CONTABILIDADE"){
            echo '<li class="nav-item-header">
              <div class="text-uppercase font-size-xs line-height-xs">Contábil e Fiscal</div>
            </li>';
          }
          foreach($modulo as $mod){
            if($mod['SituaChave'] == strtoupper("ativo")  && $mod['conteudo'] == 1){
              if($_SESSION['PerfiChave'] != "CONTABILIDADE"){
                echo "<li class='nav-item-header'>
                  <div class='text-uppercase font-size-xs line-height-xs'>$mod[ModulNome]</div>
                </li>";
              }
              // loop para colocar os menus dentro de cada módulo
              foreach($menu as $men){
                if ($men["MenuModulo"] == $mod["ModulId"] && $men["MenuPai"]==0 && $men['SituaChave'] == strtoupper("ativo") && $men['MenuPosicao']=='PRINCIPAL'){  
                  $visualizar = (isset($men['UsXPeVisualizar'])?$men['UsXPeVisualizar']:$men['PrXPeVisualizar']);
                  
                  //Empresa pública e o menu visível para o Setor Público ou Empresa Privada e o menu visível para o Setor Privado
                  if($visualizar == 1){
                    $class = basename($_SERVER['PHP_SELF']) == $men['MenuUrl']?" class='nav-link active'":" class='nav-link'";
                    echo (($men['MenuSubMenu'] == 1) ? '<li class="nav-item nav-item-submenu">':'<li class="nav-item">').
                    "<a href='$men[MenuUrl]' $class >
                    <i class='$men[MenuIco]'></i>
                      <span>$men[MenuNome]</span>
                    </a>";

                    // caso o menu seja um submenu vai colocar tambem os itens desse submenu
                    if($men['MenuSubMenu'] == 1) {
                      echo '<ul class="nav nav-group-sub" data-submenu-title="Text editors">';
  
                      foreach($menu as $men_f){
                        $visualizar_f = (isset($men_f['UsXPeVisualizar'])?$men_f['UsXPeVisualizar']:$men_f['PrXPeVisualizar']);
                    
                        if($men_f['MenuPai'] == $men['MenuId'] && $visualizar_f == 1 && $men_f['MenuPosicao'] == 'PRINCIPAL'){
                          // mostra todos os submenus e caso a rota destino(MenuUrl) seja "estoqueMinimoImprime.php"
                          // ele abrirá em uma nova aba

                          if($men_f['MenuSubMenu'] == 1) {
                            $classF = basename($_SERVER['PHP_SELF']) == $men_f['MenuUrl']?" class='nav-link active recuar'":" class='nav-link recuar'";
                            echo (($men_f['MenuSubMenu'] == 1) ? '<li class="nav-item nav-item-submenu">':'<li class="nav-item">').
                            "<a href='$men_f[MenuUrl]' $classF >
                            <i class='$men_f[MenuIco]'></i>
                              <span>$men_f[MenuNome]</span>
                            </a>";
                            
                            echo '<ul class="nav nav-group-sub" data-submenu-title="Text editors">';
        
                            foreach($menu as $men_ff){
                              $visualizar_f = (isset($men_ff['UsXPeVisualizar'])?$men_ff['UsXPeVisualizar']:$men_ff['PrXPeVisualizar']);
                          
                              if($men_ff['MenuPai'] == $men_f['MenuId'] && $visualizar_f == 1 && $men_ff['MenuPosicao'] == 'PRINCIPAL'){
                                // mostra todos os submenus e caso a rota destino(MenuUrl) seja "estoqueMinimoImprime.php"
                                // ele abrirá em uma nova aba
                                echo  "<li class='nav-item'><a href='$men_ff[MenuUrl]' class='nav-link'"
                                .($men_ff['MenuUrl'] == 'estoqueMinimoImprime.php' ? " target='_blank' >" : ">")."$men_ff[MenuNome]</a></li>";
                              } 
                            }
                            echo '</ul>';
                          }else{
                            echo  "<li class='nav-item'><a href='$men_f[MenuUrl]' class='nav-link'"
                            .($men_f['MenuUrl'] == 'estoqueMinimoImprime.php' ? " target='_blank' >" : ">")."$men_f[MenuNome]</a></li>";
                          }
                        } 
                      }
                      echo '</ul>';
                    }
                    echo '</li>';
                  }
                }
              }
            }
          }
          ?>
      </ul>
    </div>
    <!-- /Main navigation -->
  </div>
</div>