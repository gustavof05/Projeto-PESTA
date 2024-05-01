<?php
  $conexao = new SQLite3('bd_pesta.db');
  if(!$conexao) die("Erro ao conectar a base de dados."); //Houve erros na conexão
?>
<html lang="pt">
  <head>
    <title>Submiss&atilde;o de relat&oacute;rios</title>
  </head>
  <body>
    <?php
      session_start();
      if(isset($_POST['user'], $_POST['password']))  //Se o usuário está logado
      {
        if($_POST['user'] != "" && $_POST['password'] != "")  //Se não há campos vazios
        {
          $_SESSION['user'] = $_POST['user'];
          $_SESSION['user_aka'] = strtoupper(strstr($_POST['user'], "@", true));
          if($_SESSION['user_aka'] == "LBF" || $_SESSION['user_aka'] == "JES" || $_SESSION['user_aka'] == "JBM")  //ADMIN
          {
            $_SESSION['user'] = "admin";
            $docente = "ADMIN";
            header('Location: sub_rel.php');
          }
          else if((strpos($_SESSION['user'], "@") == true) && (strlen($_SESSION['user']) == 15))  //Se for DOCENTE
          {
            $docente = $_SESSION['user_aka'];
            $resultado = $conexao->query("SELECT * FROM UC WHERE RUC = '$docente'");
            if($resultado)  //Se a tabela tiver resultados
            {
              if ($resultado->fetchArray(SQLITE3_ASSOC))  //E for RUC
              {
                $_SESSION['user'] = "ruc";
                $resultado->reset();  //Limpar os resultados anteriores
                header('Location: sub_rel.php');
              }
              else 
              {
                $resultado->reset();  //Limpar os resultados anteriores
                header('Location: error.php'); //É um DOCENTE não RUC nem ADMIN             ????????
              }
            }
          }
          else if((strpos($_SESSION['user'], "@") == true) && (strlen($_SESSION['user']) == 19)) header('Location: sub_rel.php');  //ALUNO 
          else header('Location: error.php');
        }
        else header('Location: error.php');
      }
    ?>
    <form action="" method="POST">
				<input type="text" name="user" value="" placeholder="Email de estudante"/>
        <input type="text" name="password" value="" autocomplete="off" placeholder="Palavra-passe"/>
        <input type="submit" name="login" value="Login"/>
    </form>
    <?php
      $conexao->close();  //Fechar conexão com o banco de dados
    ?>
  </body>
</html>
