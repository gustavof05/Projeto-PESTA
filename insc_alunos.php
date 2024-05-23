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
  date_default_timezone_set("Europe/Lisbon");
  $datual = date('Y-m-d H:i:s');  //Data de hoje
  //Ano letivo (próximas 2 linhas)
  $alatual = date('Y');
  if(($datual > date('Y-01-01')) && ($datual < date('Y-09-20'))) $alatual = date('Y', strtotime('-1 year'));
  $queryano = $conexao->query("SELECT MIN(ANO) AS amin FROM UC");
  $resultadoano = $queryano->fetch(PDO::FETCH_ASSOC);
  $ano_minimo = $resultadoano['amin'];
  $anoSelecionado = isset($_POST['AnoLetivo']) ? $_POST['AnoLetivo'] : $alatual;
  session_set_cookie_params(['httponly' => true]);  //Proteção contra roubos de sessão
  session_start();  //Inicio de sessão
  if(!isset($_SESSION['user']))  //Se o usuário não estiver logado
  {
    header('Location: login.php');  //Redirecionar para a página de login
    exit();
  }
  if($_SESSION['user'] == "admin" || $_SESSION['user'] == "ruc") //Esta página só funciona para ADMIN'S e RUC'S
  {
//------------------------------------ENVIAR DADOS------------------------------------\\
    if(isset($_POST['env'])) //Se o formulário for enviado
    {
      if(isset($_POST['educ'], $_POST['nal']))
      {
        //Preparar a inserção da nova edição de UC
        $stmt = $conexao->prepare("INSERT INTO INSCREVER_ALUNOS (ED_UC, N_ALUNO) VALUES (:educ, :nal)");
        $stmt->bindValue(':educ', $_POST['educ']);
        $stmt->bindValue(':nal', $_POST['nal']);
        $stmt->execute(); //Executar a submissão dos dados na BD
        header('Location: ' . $_SERVER['PHP_SELF']); //Redireciona para a mesma página após a inserção dos dados
        exit();
      }
    }
//-----------------------------------ELIMINAR DADOS-----------------------------------\\
    if(isset($_POST['delete'])) //Se o botão de exclusão for clicado
    {
      if(isset($_POST['did']))
      {
        //Preparar a exclusão da nova edição de UC
        $stmt = $conexao->prepare("DELETE FROM INSCREVER_ALUNOS WHERE Id = :id");
        $stmt->bindValue(':id', $_POST['did']);
        $stmt->execute(); //Executar a submissão dos dados na BD
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
      }
    }
    if($_SESSION['user'] == "ruc")  //"Tabela do RUC"
    {
      $resultado = $conexao->prepare("SELECT * FROM INSCREVER_ALUNOS JOIN UC ON INSCREVER_ALUNOS.ED_UC = UC.id WHERE UC.RUC = :ruc");
      $resultado->bindValue(':ruc', $_SESSION['user_aka']);
      $resultado->execute();
    }
    else $resultado = $conexao->query("SELECT * FROM INSCREVER_ALUNOS JOIN UC ON INSCREVER_ALUNOS.ED_UC = UC.id"); //"Tabela do ADMIN"
?>
<html lang="pt">
  <head>
    <title>Cria&ccedil;&atilde;o de submiss&otilde;es</title>
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
    <script>
        function anoletivo() 
        {
          document.getElementById('formAnoLetivo').submit();  //Enviar o formulário ao alterar o ano letivo
        }
    </script>
  </head>
  <body>
    <div class="container">
      <div class="banner">
        <img src="https://www.dee.isep.ipp.pt/uploads/ISEP_DEP/BANNER-2022.png" alt="Banner ISEP">
      </div>
      <div class="content">
        <br>
        <form action="sub_rel.php" method="POST">
          <div style="text-align:left"><input type="submit" name="login" value="Voltar atrás"/></div>
        </form>
        <br>
        <form method="POST" action="" id="formAnoLetivo">
          <select id='AnoLetivo' name='AnoLetivo' onchange='anoletivo()'>
            <?php
              for($ano = $alatual; $ano >= $ano_minimo; $ano--)
              {
                $selected = ($anoSelecionado == $ano) ? "selected" : "";
                echo "<option value='$ano' $selected>$ano/" . $ano+1 . "</option>";
              }
            ?>
          </select>
        <br>
        <b><u>Tabela Atual:</u></b>
        <table border="1">
          <tr>
            <th>ID</th>
            <th>EDIÇÃO UC</th>
            <th>Nº ALUNO</th>
            <th style='color: red'>ELIMINAR LINHA</th>
          </tr>
          <?php
            while($row = $resultado->fetch(PDO::FETCH_ASSOC)) //Mostrar cada linha da tabela
            {
              echo "<tr>";
              echo "<td style='text-align:center'>" . $row['Id'] . "</td>";
              echo "<td style='text-align:center'>" . $row['SIGLA'] . " | " . $row['ANO'] . "</td>";
              echo "<td style='text-align:center'>" . $row['N_ALUNO'] . "</td>";
              //Botão de exclusão
              echo "<td style='text-align:center'>";
              echo "<form action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "' method='POST'>";
              echo "<input type='hidden' name='did' value='" . $row['Id'] . "'>";
              echo "<input type='submit' name='delete' value='Excluir'>";
              echo "</form>";
              echo "</td>";
              echo "</tr>";
            }
          ?>
        </table>
        <br><br>
        <b><u>Criação de um campo de submissão de relatórios:</u></b>
        <form action="" method="POST">
          <?php
            echo "Unidade Curricular: <select name='educ' required>";
            echo "<option value='' selected disabled>Ver as UC's disponíveis</option>";
            if($_SESSION['user'] == "ruc")  //Se for RUC
            {
              $queryuccreate = $conexao->prepare("SELECT id, SIGLA, ANO FROM UC WHERE RUC = :sigla");
              $queryuccreate->bindValue(':sigla', $_SESSION['user_aka']);
              $queryuccreate->execute();
            }
            else $queryuccreate = $conexao->query("SELECT id, SIGLA, ANO FROM UC"); //Se for ADMIN
            $ucids = array();
            $ucsiglas = array();
            $ucanos = array();
            while($row = $queryuccreate->fetch(PDO::FETCH_ASSOC)) 
            {
              $ucids[] = $row['id'];
              $ucsiglas[] = $row['SIGLA'];
              $ucanos[] = $row['ANO'];
            }
            for($i = 0; $i < count($ucsiglas); $i++) 
            {
              $uc_id = $ucids[$i];
              $uc_sigla = $ucsiglas[$i];
              $uc_ano = $ucanos[$i];
              echo "<option value='$uc_id'>$uc_sigla | $uc_ano</option>";
            }
            echo "</select><br>";
          ?>
          Número do Aluno: <input type="text" name="nal" value="" autocomplete="off" placeholder="Exemplo: 1230001" required>
          <br>
          <input type="submit" name="env" value="Enviar"/>
        </form>
        <br><br><br>
        <!--b><u>NOTA IMPORTANTE:</u></b>
        <ul><li>No âmbito da criação de uma submissão, <u>a Unidade Curricular deve ter uma edição disponível</u>. Se a edição ainda não tiver sido criada, é necessário criá-la na página anterior, no botão 'Criar/Excluir edição UC' ou pedir a um ADMINISTRADOR.</li-->
        <?php  
  }
  else header("Location: error.php");
  $conexao = null;  //Fechar conexão com o banco de dados
        ?>
      </div>
    </div>
  </body>
</html>