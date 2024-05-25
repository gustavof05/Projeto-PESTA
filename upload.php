<?php
  try
  {
    $conexao = new PDO('sqlite:bd_pesta.db');
    $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  } 
  catch(PDOException $e) 
  {
    die("Erro ao conectar a base de dados: " . $e->getMessage()); //Houve erros na conexão
  }
  date_default_timezone_set("Europe/Lisbon");
  $datual = date('Y-m-d H:i:s');  //Data de hoje
  session_set_cookie_params(['httponly' => true]);  //Proteção contra roubos de sessão
  session_start();  //Inicio de sessão
  if(!isset($_SESSION['user']))  //Se o usuário não estiver logado
  {
    header('Location: login.php');
    exit();
  }
  function fazerUpload()  //Função de upload
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
            //Mover o arquivo enviado para o diretório relatorios/ano/uc/file_name
            $target_file = "uploads/" . $_GET['ano'] . "/" . $_GET['uc'] . "/" . $_SESSION['user_aka'] . "/" . basename($_FILES["file"]["name"]);
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
  if($_SESSION['user'] == "admin") $query = $conexao->prepare("SELECT * FROM UC JOIN AVALIACOES ON UC.id = AVALIACOES.EDICAO_UC WHERE UC.SIGLA = :sigla");  //ADMIN
  else if($_SESSION['user'] == "ruc") //RUC
  {
    $query = $conexao->prepare("SELECT * FROM UC JOIN AVALIACOES ON UC.id = AVALIACOES.EDICAO_UC WHERE UC.SIGLA = :sigla AND UC.RUC = :ruc");
    $query->bindValue(':ruc', $_SESSION['user_aka']);
  }
  else  //Aluno
  {
    $query = $conexao->prepare("SELECT * FROM UC JOIN AVALIACOES ON UC.id = AVALIACOES.EDICAO_UC WHERE UC.ANO = :ano AND UC.SIGLA = :sigla AND AVALIACOES.INICIO <= '$datual' AND AVALIACOES.FIM >= '$datual'");
    $query->bindValue(':ano', $_GET['ano']);
  }
  $query->bindValue(':sigla', $_GET['uc']);
  $query->execute();
?>
<html lang="pt">
  <head>
    <title>Upload de relat&oacute;rios</title>
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
        <br><br>
        <span style='display:block; text-align:center;'><h3><b>
        <?php
          echo $_POST['titulo'];
          exit(0);        
          if(count($query->fetchAll(PDO::FETCH_ASSOC)) > 0) fazerUpload();
          else echo "Não é permitido submeter nesta edição de UC";
        ?>
        </b></span>
        <br><br><br>
        <form action="sub_rel.php" method="POST">
          <div style="text-align:center"><input type="submit" value="Voltar atrás"/></div>
        </form>
        <?php
          $conexao = null;  //Fechar conexão com o banco de dados
        ?>
      </div>
    </div>
  </body>
</html>