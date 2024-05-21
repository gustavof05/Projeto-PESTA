<?php
  date_default_timezone_set("Europe/Lisbon");
  $datual = date('Y-m-d H:i:s');  //Data de hoje
  //Ano letivo (próximas 2 linhas)
  $alatual = date('Y');
  if(($datual > date('Y-01-01')) && ($datual < date('Y-09-20'))) $alatual = date('Y', strtotime('-1 year'));
  session_set_cookie_params(['httponly' => true]);  //Proteção contra roubos de sessão
  session_start();  //Inicio de sessão
  if(!isset($_SESSION['user']))  //Se o usuário não estiver logado
  {
    header('Location: login.php');  //Redirecionar para a página de login
    exit();
  }
?>
<html lang="pt">
  <head>
    <title>Upload de relat&oacute;rios</title>
  </head>
  <body>
    <br><br><br>
    <span style='display:block; text-align:center;'><h3><b>
    <?php
      if($_SESSION['user'] == "admin" || $_SESSION['user'] == "ruc") 
      {
        if(isset($_FILES["file"]))  //Verificar se um arquivo foi enviado
        {
          if($_FILES["file"]["error"] == UPLOAD_ERR_OK) //Verificar se não há erros durante o envio
          {
            $extensoes = array("zip"); //Extensões permitidas
            $file_extension = pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION);  //Obtém a extensão do arquivo
            if(in_array(strtolower($file_extension), $extensoes))
            {
              $max_file_size = 512 * 1024 * 1024; //512MB em bytes
              if($_FILES["file"]["size"] > $max_file_size) echo "O tamanho do arquivo excede o limite permitido (512MB)";
              else
              {
                //Mover o arquivo enviado para o diretório relatorios/file_name
                $target_file = "uploads/" . basename($_FILES["file"]["name"]);
                if(move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) echo "Upload realizado com sucesso";
                else echo "Ocorreu um erro ao tentar fazer o upload do arquivo";
              }
            }
            else echo "Apenas arquivos ZIP são permitidos";
          } 
          else echo "Ocorreu um erro durante o envio do arquivo";
        }
        else echo "Nenhum arquivo foi enviado";
      }
      else
      {
        if($alatual == $row["FIM"])
        {
          if(isset($_FILES["file"]))  //Verificar se um arquivo foi enviado
        {
          if($_FILES["file"]["error"] == UPLOAD_ERR_OK) //Verificar se não há erros durante o envio
          {
            $extensoes = array("zip"); //Extensões permitidas
            $file_extension = pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION);  //Obtém a extensão do arquivo
            if(in_array(strtolower($file_extension), $extensoes))
            {
              $max_file_size = 512 * 1024 * 1024; //512MB em bytes
              if($_FILES["file"]["size"] > $max_file_size) echo "O tamanho do arquivo excede o limite permitido (512MB)";
              else
              {
                //Mover o arquivo enviado para o diretório relatorios/file_name
                $target_file = "uploads/" . basename($_FILES["file"]["name"]);
                if(move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) echo "Upload realizado com sucesso";
                else echo "Ocorreu um erro ao tentar fazer o upload do arquivo";
              }
            }
            else echo "Apenas arquivos ZIP são permitidos";
          } 
          else echo "Ocorreu um erro durante o envio do arquivo";
        }
        else echo "Nenhum arquivo foi enviado";
        }
      }
    ?>
    </b></span>
    <br>
    <br>
    <br>
    <form action="sub_rel.php" method="POST">
      <div style="text-align:center"><input type="submit" name="login" value="Voltar atrás"/></div>
    </form>
  </body>
</html>