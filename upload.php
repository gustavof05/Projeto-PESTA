<html lang="pt">
  <head>
    <title>Upload de relat&oacute;rios</title>
  </head>
  <body>
    <br><br><br>
    <span style='display:block; text-align:center;'><h3><b>
    <?php
      if(isset($_FILES["file"]))  //Verificar se um arquivo foi enviado
      {
        if($_FILES["file"]["error"] == UPLOAD_ERR_OK) //Verificar se não há erros durante o envio
        {
          $extensoes = array("zip", "rar"); //Extensões permitidas
          $file_extension = pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION);  //Obtém a extensão do arquivo          if(in_array($file_type,  $allowed_file_types)) 
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
          else echo "Apenas arquivos ZIP e RAR são permitidos";
        } 
        else echo "Ocorreu um erro durante o envio do arquivo";
      }
      else echo "Nenhum arquivo foi enviado";
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