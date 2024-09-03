<?php
  try
  {
    $conexao = new PDO('sqlite:bd_pesta.db');
    $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  } 
  catch (PDOException $e) 
  {
    die("Erro ao conectar a base de dados: " . $e->getMessage()); //Houve erros na conexão
  }
?>
<html lang="pt">
  <head>
    <title>Login</title>
    <style>
      .container 
      {
        max-width: 1024px;
        margin: 0 auto;
        text-align: center;
      }
      body 
      {
        text-align: center;
      }
      .content 
      {
        text-align: left;
        margin: 0 auto;
        max-width: 1024px;
      }
      .banner img 
      {
        width: 100%;
        height: auto;
      }
    </style>
  </head>
  <body>
    <div class="container">
      <div class="banner">
        <img src="https://www.dee.isep.ipp.pt/uploads/ISEP_DEP/BANNER-2022.png" alt="Banner ISEP">
      </div>
      <div class="content">
        <br>
        <?php
          session_start();
          if(isset($_POST['user'], $_POST['password']))  //Se o usuário está logado
          {
            if($_POST['user'] != "" && $_POST['password'] != "")  //Se não há campos vazios
            {
              $_SESSION['user'] = $_POST['user'];
              $_SESSION['user_aka'] = strtoupper(strstr($_POST['user'], "@", true));
              if($_SESSION['user_aka'] == "LBF" || $_SESSION['user_aka'] == "JES")  //ADMIN
              {
                $_SESSION['user'] = "admin";
                header('Location: sub_rel.php');
              }
              else if((strpos($_SESSION['user'], "@") == true) && (strlen($_SESSION['user']) == 15))  //Se for DOCENTE
              {
                $docente = $_SESSION['user_aka'];
                $query = $conexao->prepare("SELECT * FROM UC WHERE RUC = :dct");
                $query->bindParam(':dct', $docente);
                $query->execute();
                $resultado = $query->fetch(PDO::FETCH_ASSOC);
                if($resultado)  //Se a tabela tiver resultados
                {
                  $_SESSION['user'] = "ruc";
                  header('Location: sub_rel.php');              
                }
                else header('Location: error.php'); //É um DOCENTE não-ADMIN e não-RUC
              }
              else if((strpos($_SESSION['user'], "@") == true) && (strlen($_SESSION['user']) == 19)) header('Location: sub_rel.php');  //ALUNO 
              else header('Location: error.php');
            }
            else header('Location: error.php');
          }
        ?>
        <form action="" method="POST">
            <input type="text" name="user" value="" placeholder="E-mail do utilizador"/>
            <input type="text" name="password" value="" autocomplete="off" placeholder="Palavra-passe"/>
            <input type="submit" name="login" value="Login"/>
        </form>
        <?php
          $conexao = null;  //Fechar conexão com o banco de dados
        ?>
      </div>
    </div>
  </body>
</html>