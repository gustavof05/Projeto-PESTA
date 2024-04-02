<html>
  <head>
    <title>Submiss&atilde;o de relat&oacute;rios</title>
  </head>
  <body>
    <?php
      if(isset(($_POST['user'] == "abc@isep.ipp.pt" || $_POST['user'] == "def@isep.ipp.pt" || $_POST['user'] == "ghi@isep.ipp.pt")) && isset($_POST['password']))  // Verificar se o usuário está logado
      {
        $_SESSION['user'] = $_POST['user'];
        header('Location: sub_rel.php');
      }
      else 

      
    ?>
    <form action="" method="POST">
				<input type="text" name="user" placeholder="Email de estudante"/>
        <input type="text" name="password" placeholder="Palavra-passe"/>
        <input type="submit" name="login" value="Login"/>
    </form>
  </body>
</html>
