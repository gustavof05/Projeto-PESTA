<html>
  <head>
    <title>Portal do DEE&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&copy;DEE.Net 2012-2024</title>
  </head>
  <body>
    <?php
      session_start();  // Verificar se o usuário está logado
      /*if (!isset($_SESSION['usuario_id'])) 
      {
        header('Location: login.php');  // Redirecionar para a página de login
        exit();
      }*/
      
      $perms = obterpermsDoBancoDeDados($_SESSION["usuario_id"]); // Verificar as permissões do usuário
      if ($perms === "docente") 
      {
        // Exibir opções adicionais para professores
        echo "Bem-vindo, professor!";
        // Exibir botões para abrir, editar ou excluir relatórios, por exemplo
      } 
      else if ($perms === "aluno") 
      {
        // Exibir conteúdo para alunos
        echo "Bem-vindo, aluno!";
        if(isset($_POST["enviar"]))
        {
          $arq = $_FILES["file"];
          $narq = explode(".", $arq["name"]);
          if($narq[sizeof($narq)-1] != "jpg") die("Não é possível dar upload do arquivo");
          else
          {
            echo "Upload realizado";
            move_uploaded_file($arq["tmp_name"], "upload/" . $arq["name"]);
          }
        }// Exibir botões para visualizar relatórios, por exemplo
      } 
      else 
      {
        // Exibir mensagem de erro ou redirecionar para página de permissões insuficientes
        echo "Permissões insuficientes para acessar esta página!";
        // Ou redirecionar para outra página
        // header('Location: perms_insuficiente.php');
        // exit();
      }

      // Função para obter as permissões do usuário do banco de dados
      function obterpermsDoBancoDeDados($usuario_id) 
      {
        // Aqui você deve implementar a lógica para consultar o banco de dados e obter as permissões do usuário
        // Retorne a permissão do usuário (por exemplo, 'professor', 'aluno', etc.)
      }
    ?>
    <form action="" enctype="multipart/form-data" method="POST">
				<input type="file" name="file"/>
        <input type="submit" name="enviar" value="Submeter"/>
    </form>
  </body>
</html>
