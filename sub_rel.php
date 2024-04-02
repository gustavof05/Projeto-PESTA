<html>
  <head>
    <title>Submiss&atilde;o de relat&oacute;rios</title>
  </head>
  <body>
    <?php
      /*
      session_set_cookie_params([httponly => true]);  //Proteçãocontra roubos de sessão
      session_start();  //Inicio de sessão

      if (!isset($_SESSION['user']))  // Verificar se o usuário está logado
      {
        header('Location: login.php');  // Redirecionar para a página de login
        exit();
      }
      
      if($_SESSION['user'] == abc@isep.ipp.pt || $_SESSION['user'] == def@isep.ipp.pt || $_SESSION['user'] == ghi@isep.ipp.pt)$perms = docente;
      else $perms = aluno;

      if ($perms === "docente") 
      {
        echo "Bem-vindo, professor!";
        // Exibir botões para abrir, editar ou excluir relatórios, por exemplo
      }
      else if ($perms === "aluno") 
      {*/
        echo "Bem-vindo, aluno nº "/* . $_SESSION['user_num'] . */"!</br>";
        echo "<th rowspan='' colspan='' class='' style='text-align:left;width:161.2px;'>Listar Unidades Curriculares:</th>
        <th rowspan='' colspan='' class='' style='width:230px;text-align:center;'><select name='FiltroDisciplinas' size='' style='width:100%;'>
          <option value='0' selected>Todas as Unidades Curriculares</option>
          <option value='1' >Laborat&oacute;rio de Sistemas (LABSI)</option>
          <option value='2' >Projeto / Est&aacute;gio (PESTA)</option>
          </select>
        </th>";
        if(isset($_POST["enviar"]))
        {
          $arq = $_FILES["file"];
          $narq = explode(".", $arq["name"]);
          if($narq[sizeof($narq)-1] != "pdf") die("Não é possível dar upload do arquivo");
          else
          {
            echo "Upload realizado";
            move_uploaded_file($arq["tmp_name"], "relatorios/" . $arq["name"]);
          }
        }/* Exibir botões para visualizar relatórios, por exemplo
      }*/
    ?>
    <form action="" enctype="multipart/form-data" method="POST">
				<input type="file" name="file"/>
        <input type="submit" name="enviar" value="Submeter"/>
    </form>
  </body>
</html>
