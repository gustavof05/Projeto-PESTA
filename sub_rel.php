<html lang="pt">
  <head>
    <title>Submiss&atilde;o de relat&oacute;rios</title>
  </head>
  <body>
    <?php
      
      session_set_cookie_params(['httponly' => true]);  //Proteção contra roubos de sessão
      session_start();  //Inicio de sessão

      if (!isset($_SESSION['user']))  //Se o usuário não estiver logado
      {
        header('Location: login.php');  //Redirecionar para a página de login
        exit();
      }

      if ($_SESSION['user'] == "admin") 
      {
        echo "Bem-vindo, professor <b>" . strtoupper($_SESSION['user_aka']) . "</b>!</br>";
        //Selecionar UC
        echo "<br><th rowspan='' colspan='' class=''>Listar Unidades Curriculares:</th>
        <th rowspan='' colspan='' class='' style='width:230px;text-align:center;'><select name='FiltroDisciplinas' size='' style='width:100%;'>
          <option value='0' selected>Todas as Unidades Curriculares</option>
          <option value='1' >Laborat&oacute;rio de Sistemas (LABSI)</option>
          <option value='2' >Projeto / Est&aacute;gio (PESTA)</option>
          </select>
        </th>";
        //Exibir botões para criar, editar/adicionar ou excluir relatórios da UC selecionada
    ?>
    <form action="" enctype="multipart/form-data" method="POST">
				<input type="file" name="file"/>
        <input type="submit" name="enviar" value="Submeter"/>
    </form>
    <?php
        if(isset($_POST["enviar"]))
        {
          $arq = $_FILES["file"];
          $narq = explode(".", $arq["name"]);
          if($narq[sizeof($narq)-1] != "pdf") die("Não é possível dar upload do arquivo");
          else
          {
            move_uploaded_file($arq["tmp_name"], "relatorios/" . $arq["name"]);
            echo "Upload realizado";
          }
        }
      }
      else
      {
        echo "Bem-vindo, aluno nº" . $_SESSION['user_aka'] . "!</br>";
        //Exibir botões para visualizar UCs e respetivos campos de submissão (com prazos visíveis)
        echo "<br><th rowspan='' colspan='' class=''>Listar Unidades Curriculares:</th>
        <th rowspan='' colspan='' class='' style='width:230px;text-align:center;'><select name='FiltroDisciplinas' size='' style='width:100%;'>
          <option value='0' selected>Todas as Unidades Curriculares</option>
          <option value='1' >Laborat&oacute;rio de Sistemas (LABSI)</option>
          <option value='2' >Projeto / Est&aacute;gio (PESTA)</option>
          </select>
        </th>";
    ?>
    <form action="" enctype="multipart/form-data" method="POST">
				<input type="file" name="file"/>
        <input type="submit" name="enviar" value="Submeter"/>
    </form>
    <?php
        if(isset($_POST["enviar"]))
        {
          $arq = $_FILES["file"];
          $narq = explode(".", $arq["name"]);
          if($narq[sizeof($narq)-1] != "pdf") die("Não é possível dar upload do arquivo");
          else
          {
            move_uploaded_file($arq["tmp_name"], "relatorios/" . $arq["name"]);
            echo "Upload realizado";
          }
        }
      }
    ?> 
  </body>
</html>
