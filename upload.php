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
  if($_SESSION['user'] == "admin" || $_SESSION['user'] == "ruc") $aluno = $_POST['aluno'];
  else $aluno = $_SESSION['user_aka'];
  function fazerUpload()  //Função de upload
  {
    global $conexao, $aluno;
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
            $target_dir = "uploads/" . $_GET['ano'] . "/" . $_GET['uc'] . "/" . $_POST['EPOCA'] . "/" . $aluno;
            $target_file = $target_dir . "/" . basename($_FILES["file"]["name"]);
            // Verificar e criar o diretório, se necessário
            if(!file_exists($target_dir))
            {
              if(!mkdir($target_dir, 0777, true))
              {
                echo "Falha ao criar diretórios...";
                return;
              }
            }
            if(move_uploaded_file($_FILES["file"]["tmp_name"], $target_file))
            {
              echo "Upload realizado com sucesso";
              $queryup = $conexao->prepare("INSERT INTO RELATORIOS_SUBMETIDOS (EDICAO_AVALIACOES, TITULO, ALUNO) VALUES (:edav, :tit, :aluno)");
              $queryup->bindValue(':edav', $_POST['EDAV']);
              $queryup->bindValue(':tit', $_POST['titulo']);
              $queryup->bindValue(':aluno', $aluno);
              $queryup->execute();
            }
            else echo "Ocorreu um erro ao tentar fazer o upload do arquivo";
          }
        }
        else echo "Apenas arquivos ZIP são permitidos";
      } 
      else echo "Ocorreu um erro durante o envio do arquivo";
    }
    else echo "Nenhum arquivo foi enviado";
  }
  //Query de verificação da submissão
  $query = "SELECT * FROM UC JOIN AVALIACOES ON UC.id = AVALIACOES.EDICAO_UC WHERE UC.SIGLA = :sigla";
  if($_SESSION['user'] == "ruc") $query .= " AND UC.RUC = :ruc";
  else if($_SESSION['user'] != "admin" && $_SESSION['user'] != "ruc") $query .= " AND UC.ANO = :ano AND AVALIACOES.INICIO <= '$datual' AND AVALIACOES.FIM >= '$datual'";
  $resultado = $conexao->prepare($query);
  $resultado->bindValue(':sigla', $_GET['uc']);
  if($_SESSION['user'] == "ruc") $resultado->bindValue(':ruc', $_SESSION['user_aka']);
  else if($_SESSION['user'] != "admin" && $_SESSION['user'] != "ruc") $resultado->bindValue(':ano', $_GET['ano']);
  $resultado->execute();
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
          if(count($resultado->fetchAll(PDO::FETCH_ASSOC)) > 0) 
          {
            echo "Título: " . $_POST['titulo'] . "<br><br><br>"; 
            fazerUpload();
          }
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