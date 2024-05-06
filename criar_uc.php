<?php
  $conexao = new SQLite3('bd_pesta.db');
  if(!$conexao) die("Erro ao conectar a base de dados."); //Houve erros na conexão
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
    if(isset($_POST['env'])) //Se o formulário for enviado
    {
      if(isset($_POST['sig'], $_POST['ano'], $_POST['ruc'])) //Verificar se todos os campos foram preenchidos
      {
        //Recuperar os dados do formulário
        $sigla = strtoupper($_POST['sig']);
        $ano = $_POST['ano'];
        $ruc = strtoupper($_POST['ruc']);
        //Preparar a inserção da nova edição de UC
        $stmt = $conexao->prepare("INSERT INTO UC (SIGLA, ANO, RUC) VALUES (:sigla, :ano, :ruc)");
        $stmt->bindParam(':sigla', $sigla);
        $stmt->bindParam(':ano', $ano);
        $stmt->bindParam(':ruc', $ruc);
        $stmt->execute(); //Executar a submissão dos dados na BD
        header('Location: ' . $_SERVER['PHP_SELF']); //Redirecionar para esta página após a inserção dos dados
        exit();
      }
    }
    if(isset($_POST['delete'])) //Se o botão de exclusão for clicado
    {
      if(isset($_POST['did']))  //Verificar se o ID da linha a ser excluída foi passado via POST
      {
        //Recuperar o ID da linha a ser excluída
        $did = $_POST['did'];
        //Preparar a exclusão da nova edição de UC
        $stmt = $conexao->prepare("DELETE FROM UC WHERE id = :id");
        $stmt->bindParam(':id', $did);
        $stmt->execute(); //Executar a submissão dos dados na BD
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
      }
    }
    $resultado = $conexao->query("SELECT * FROM UC"); //Consultar os dados da BD
  ?>
  <html lang="pt">
    <head>
      <title>Cria&ccedil;&atilde;o de submiss&otilde;es</title>
    </head>
    <body>
      <br>
      <form action="sub_rel.php" method="POST">
        <div style="text-align:left"><input type="submit" name="login" value="Voltar atrás"/></div>
      </form>
      <br>
      <br>
      <b><u>Tabela Atual:</u></b>
      <table border="1">
        <tr>
          <th>ID</th>
          <th>Sigla UC</th>
          <th>Ano</th>
          <th>RUC</th>
          <th style='color: red'>ELIMINAR LINHA</th>
        </tr>
        <?php
          while($row = $resultado->fetchArray(SQLITE3_ASSOC)) //Mostrar cada linha da tabela
          {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['SIGLA'] . "</td>";
            echo "<td>" . $row['ANO'] . "</td>";
            echo "<td>" . $row['RUC'] . "</td>";
            // Botão de exclusão
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
      <b><u>NOTAS IMPORTANTES:</u></b>
      <ul><li>O 'Ano' corresponde ao ano em que se inicia a Unidade Curricular.</li>
      <?php  
        $conexao->close();  //Fechar conexão com o banco de dados
  }
  else header('Location: error.php');
      ?>
  </body>
</html>