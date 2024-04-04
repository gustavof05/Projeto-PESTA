<html lang="pt">
  <head>
    <title>Submiss&atilde;o de relat&oacute;rios</title>
  </head>
  <body>
    <?php
      session_start();
      if(isset($_POST['user'], $_POST['password']))  // Verificar se o usuário está logado
      {
        if($_POST['user'] != "" && $_POST['password'] != "")
        {
          $_SESSION['user'] = $_POST['user'];
          $_SESSION['user_aka'] = strstr($_POST['user'], "@", true);
          if((strpos($_SESSION['user'], "@") == true) && (strlen($_SESSION['user']) == 19)) header('Location: sub_rel.php');
          else if((strpos($_SESSION['user'], "@") == true) && (strlen($_SESSION['user']) == 15))
          {
            $_SESSION['user'] = "admin";
            header('Location: sub_rel.php');
          }
        }
        else header('Location: error.php');
      }
    ?>
    <form action="" method="POST">
				<input type="text" name="user" value="" placeholder="Email de estudante"/>
        <input type="text" name="password" value="" autocomplete="off" placeholder="Palavra-passe"/>
        <input type="submit" name="login" value="Login"/>
    </form>
  </body>
</html>
