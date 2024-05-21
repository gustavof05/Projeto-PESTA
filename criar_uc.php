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
  session_set_cookie_params(['httponly' => true]);  //Proteção contra roubos de sessão
  session_start();  //Inicio de sessão
  if(!isset($_SESSION['user']))  //Se o usuário não estiver logado
  {
    header('Location: login.php');  //Redirecionar para a página de login
    exit();
  }
  if($_SESSION['user'] == "admin") //Esta página só funciona para ADMIN'S
  {
//------------------------------------ENVIAR DADOS------------------------------------\\
    if(isset($_POST['env'])) //Se o formulário for enviado
    {
      if(isset($_POST['sig'], $_POST['ano'], $_POST['ruc']))
      {
        //Recuperar os dados do formulário
        $sigla = strtoupper($_POST['sig']);
        $ano = $_POST['ano'];
        $ruc = strtoupper($_POST['ruc']);
        //Preparar a inserção da nova edição de UC
        $query = $conexao->prepare("INSERT INTO UC (SIGLA, ANO, RUC) VALUES (:sigla, :ano, :ruc)");
        $query->bindParam(':sigla', $sigla);
        $query->bindParam(':ano', $ano);
        $query->bindParam(':ruc', $ruc);
        $query->execute(); //Executar a submissão dos dados na BD
        header('Location: ' . $_SERVER['PHP_SELF']); //Redirecionar para esta página após a inserção dos dados
        exit();
      }
    }
//-----------------------------------ATUALIZAR DADOS-----------------------------------\\
    if(isset($_POST['edit']))  //Se o botão de edição for clicado
    {
      if(isset($_POST['eid'], $_POST['esigla']))
      {
        header("Location: $_SERVER[PHP_SELF]?edit_id=" . $_POST['eid'] . "&edit_sigla=" . $_POST['esigla'] . "");  //Redireciona para a mesma página, mas com o ID da linha a ser editada
        exit();
      }
    }
    if (isset($_POST['update'])) //Se o botão de atualização for clicado
    {
      if (isset($_POST['id'], $_POST['new_ano']))
      {
        //Preparar a atualização do ano da UC
        $query = $conexao->prepare("UPDATE UC SET ANO = :ano WHERE id = :id");
        $query->bindValue(':ano', $_POST['new_ano']);
        $query->bindValue(':id', $_POST['id']);
        $query->execute(); //Executar a submissão dos dados na BD
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
      }
    }
//-----------------------------------ELIMINAR DADOS-----------------------------------\\
    if(isset($_POST['delete'])) //Se o botão de exclusão for clicado
    {
      if(isset($_POST['did']))  //Verificar se o ID da linha a ser excluída foi passado via POST
      {
        //Recuperar o ID da linha a ser excluída
        $did = $_POST['did'];
        //Preparar a exclusão da nova edição de UC
        $query = $conexao->prepare("DELETE FROM UC WHERE id = :id");
        $query->bindParam(':id', $did);
        $query->execute(); //Executar a submissão dos dados na BD
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
      }
    }
    $resultado = $conexao->query("SELECT * FROM UC"); //Consultar os dados da BD
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
        <br><br>
        <b><u>Tabela Atual:</u></b>
        <table border="1">
          <tr>
            <th>ID</th>
            <th>SIGLA DA UC</th>
            <th>ANO</th>
            <th>RUC</th>
            <th style='color: red'>ELIMINAR LINHA</th>
          </tr>
          <?php
            while($row = $resultado->fetch(PDO::FETCH_ASSOC)) //Mostrar cada linha da tabela
            {
              echo "<tr>";
              echo "<td style='text-align:center'>" . $row['id'] . "</td>";
              echo "<td style='text-align:center'>" . $row['SIGLA'] . "</td>";
              //Edição
              echo "<td style='text-align:center'>";
              if(isset($_GET['edit_id']) && $_GET['edit_id'] == $row['id']) //Formulário de edição
              {
                echo "<form action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "' method='POST'>";
                echo "<input type='hidden' name='id' value='" . $row['id'] . "'>";
                echo "Novo Ano (Letivo): <input type='text' name='new_ano' value='" . $row['ANO'] . "' required>";
                echo "<input type='submit' name='update' value='Atualizar'>";
                echo "<input type='button' value='Cancelar' onclick='window.location.href=\"" . $_SERVER["PHP_SELF"] . "\"'>";
                echo "</form>";
              }
              else //Botão de edição
              {
                echo "<form action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "' method='POST'>";
                echo "<input type='hidden' name='eid' value='" . $row['id'] . "'>";
                echo "<input type='hidden' name='esigla' value='" . $row['SIGLA'] . "'>";
                echo $row['ANO'] . " ";
                echo "<input type='submit' name='edit' value='Editar'>";
                echo "</form>";
              }
              echo "</td>";
              echo "<td style='text-align:center'>" . $row['RUC'] . "</td>";
              //Botão de exclusão
              echo "<td style='text-align:center'><form action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "' method='POST'>";
              echo "<input type='hidden' style='middle' name='did' value='" . $row['id'] . "'>";
              echo "<input type='submit' name='delete' value='Excluir'>";
              echo "</form></td>";
              echo "</tr>";
            }
          ?>
        </table>
        <br>
        <br><b><u>Criação de uma edição de uma Unidade Curricular:</u></b>
        <form action="" method="POST">
          Sigla da Unidade Curricular: <input type="text" name="sig" value="" autocomplete="off" placeholder="Sigla da UC" required>
          <br>
          Ano: <input type="text" name="ano" value="" autocomplete="off" placeholder="Ano" required>
          <br>
          Responsável da Unidade Curricular: <input type="text" name="ruc" value="" autocomplete="off" placeholder="RUC" required>
          <br>
          <input type="submit" name="env" value="Enviar"/>
        </form>
        <br><br>
        <b><u>NOTA IMPORTANTE:</u></b>
        <ul><li>O 'ANO' corresponde ao Ano Letivo em que se inicia a Unidade Curricular. <b>Exemplo: <u>Ano Letivo:20XX/20YY</u> --> <u>ANO:20XX</u></b></li>
        <li>Antes de eliminar uma Unidade Curricular, <u>elimine as avaliações associadas à mesma</u>.</li></ul>
        <?php  
    }
    else header('Location: error.php');
    $conexao = null;  //Fechar conexão com o banco de dados
        ?>
      </div>
    </div>
  </body>
</html>