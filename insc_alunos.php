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
        $edicaoSelecionada = $_POST['EdicaoUC']; //Pegar o valor da edição selecionada
        header('Location: ' . $_SERVER['PHP_SELF'] . '?EdicaoUC=' . $edicaoSelecionada); //Redirecionar para esta página após a inserção dos dados
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
    //Edição UC
    $edicaoSelecionada = isset($_POST['EdicaoUC']) ? $_POST['EdicaoUC'] : (isset($_GET['EdicaoUC']) ? $_GET['EdicaoUC'] : '');  //Para manter na mesma página após submeter o formulário
    $edicoesUC = array();
    if($_SESSION['alsel']) 
    {
      $stmt = ("SELECT id, SIGLA, ANO FROM UC WHERE ANO = :ano");
      if($_SESSION['user'] == "ruc") $stmt .= " AND RUC = :ruc";
      $resultadouc = $conexao->prepare($stmt);
      $resultadouc->bindValue(':ano', $_SESSION['alsel']);
      if($_SESSION['user'] == "ruc") $resultadouc->bindValue(':ruc', $_SESSION['user_aka']);
      $resultadouc->execute();
      $edicoesUC = $resultadouc->fetchAll(PDO::FETCH_ASSOC);
    }
    //Selecionar os dados para a tabela de acordo com o ano letivo e a edição UC selecionados
    $query = "SELECT * FROM INSCREVER_ALUNOS JOIN UC ON INSCREVER_ALUNOS.ED_UC = UC.id WHERE UC.ANO = :ano";
    if($_SESSION['user'] == "ruc") $query .= " AND UC.RUC = :ruc";
    if($edicaoSelecionada) $query .= " AND INSCREVER_ALUNOS.ED_UC = :edicao";
    $resultado = $conexao->prepare($query);
    $resultado->bindValue(':ano', $_SESSION['alsel']);
    if($_SESSION['user'] == "ruc") $resultado->bindValue(':ruc', $_SESSION['user_aka']);
    if($edicaoSelecionada) $resultado->bindValue(':edicao', $edicaoSelecionada);
    $resultado->execute();
    //Query de "criação" de alunos
    $queryacreate = "SELECT id, SIGLA FROM UC WHERE ANO = :asel";
    if($_SESSION['user'] == "ruc") $queryacreate .= " AND RUC = :ruc";
    $resultadoacreate = $conexao->prepare($queryacreate);
    $resultadoacreate->bindValue(':asel', $_SESSION['alsel']);
    if($_SESSION['user'] == "ruc") $resultadoacreate->bindValue(':ruc', $_SESSION['user_aka']);        
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
        function edicaoUC()
        {
          document.getElementById('formEdicaoUC').submit();  // Enviar o formulário ao alterar a edição UC
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
        <?php
          echo "<b><u>Ano Selecionado = " . $_SESSION['alsel'] . "</u></b> (Mudar na página anterior)";
        ?>  
        <br><br>
        <form method="POST" action="" id="formEdicaoUC">
          <select id='EdicaoUC' name='EdicaoUC' onchange='edicaoUC()'>
            <option value='' selected disabled>Selecione a edição UC</option>
              <?php
                foreach ($edicoesUC as $edicao) 
                {
                  $selected = ($edicaoSelecionada == $edicao['id']) ? "selected" : "";
                  echo "<option value='" . $edicao['id'] . "' $selected>" . $edicao['SIGLA'] . "</option>";
                }
              ?>
           </select>
        </form>
        <b><u>Tabela Atual:</u></b>
        <table border="1">
          <tr>
            <th>EDIÇÃO UC</th>
            <th>Nº ALUNO</th>
            <th style='color: red'>ELIMINAR LINHA</th>
          </tr>
          <?php
            while($row = $resultado->fetch(PDO::FETCH_ASSOC)) //Mostrar cada linha da tabela
            {
              echo "<tr>";
              echo "<td style='text-align:center'>" . $row['SIGLA'] . "</td>";
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
          <input type="hidden" name="EdicaoUC" value="<?php echo $edicaoSelecionada; ?>">
          <?php
            echo "Unidade Curricular: <select name='educ' required>";
            echo "<option value='' selected disabled>Ver as UC's disponíveis</option>";
            $resultadoacreate->execute();
            $ucids = array();
            $ucsiglas = array();
            while($row = $resultadoacreate->fetch(PDO::FETCH_ASSOC)) 
            {
              $ucids[] = $row['id'];
              $ucsiglas[] = $row['SIGLA'];
            }
            for($i = 0; $i < count($ucsiglas); $i++) 
            {
              $uc_id = $ucids[$i];
              $uc_sigla = $ucsiglas[$i];
              echo "<option value='$uc_id'>$uc_sigla</option>";
            }
            echo "</select><br>";
          ?>
          Número do Aluno: <input type="text" name="nal" value="" autocomplete="off" placeholder="Exemplo: 1230001" required>
          <br>
          <input type="submit" name="env" value="Inscrever"/>
        </form>
        <br><br><br>
        <?php  
  }
  else header("Location: error.php");
  $conexao = null;  //Fechar conexão com o banco de dados
        ?>
      </div>
    </div>
  </body>
</html>