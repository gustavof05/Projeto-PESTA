<?php
  $conexao = new SQLite3('bd_pesta.db');
  if(!$conexao) die("Erro ao conectar a base de dados."); //Houve erros na conexão
  date_default_timezone_set("Europe/Lisbon");

  if(isset($_POST['env'])) //Se o formulário for enviado
  {
    if(isset($_POST['id'], $_POST['epoca'], $_POST['inicio'], $_POST['fim'])) //Verificar se todos os campos foram preenchidos
    {
      //Recuperar os dados do formulário
      $id = $_POST['id'];
      $epoca = $_POST['epoca'];
      $inicio = $_POST['inicio'];
      $fim = $_POST['fim'];
      $di = $_POST['di'];
      $df = $_POST['df'];
      $inicio = $inicio . " " . $di;
      $fim = $fim . " " . $df;
      //Preparar a inserção da nova edição de UC
      $stmt = $conexao->prepare("INSERT INTO SUBMISSOES (EDICAO_UC, EPOCA, INICIO, FIM) VALUES (:id, :ep, :ini, :fm)");
      $stmt->bindParam(':id', $id);
      $stmt->bindParam(':ep', $epoca);
      $stmt->bindParam(':ini', $inicio);
      $stmt->bindParam(':fm', $fim);
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
      $stmt = $conexao->prepare("DELETE FROM SUBMISSOES WHERE ID = :id");
      $stmt->bindParam(':id', $did);
      $stmt->execute(); //Executar a submissão dos dados na BD
      header('Location: ' . $_SERVER['PHP_SELF']);
      exit();
    }
  }
  $resultado = $conexao->query("SELECT * FROM SUBMISSOES"); //Consultar os dados da BD
?>
<html lang="pt">
  <head>
    <title>Cria&ccedil;&atilde;o de submiss&otilde;es</title>
  </head>
  <body>
    <?php
      session_set_cookie_params(['httponly' => true]);  //Proteção contra roubos de sessão
    ?>
    <b><u>Tabela Atual:</u></b>
    <table border="1">
      <tr>
        <th>ID</th>
        <th>EDICAO_UC</th>
        <th>EPOCA</th>
        <th>INICIO</th>
        <th>FIM</th>
        <th style='color: red'>ELIMINAR LINHA</th>
      </tr>
      <?php
        while($row = $resultado->fetchArray(SQLITE3_ASSOC)) //Mostrar cada linha da tabela
        {
          echo "<tr>";
          echo "<td>" . $row['ID'] . "</td>";
          echo "<td>" . $row['EDICAO_UC'] . "</td>";
          echo "<td>" . $row['EPOCA'] . "</td>";
          echo "<td>" . $row['INICIO'] . "</td>";
          echo "<td>" . $row['FIM'] . "</td>";
          // Botão de exclusão
          echo "<td style='text-align:center'><form action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "' method='POST'>";
          echo "<input type='hidden' name='did' value='" . $row['ID'] . "'>";
          echo "<input type='submit' name='delete' value='Excluir'>";
          echo "</form></td>";
          echo "</tr>";
        }
      ?>
    </table>
    <br><br>
    <b><u>Criação de um campo de submissão de relatórios:</u></b>
    <form action="" method="POST">
        ID da UC (tabela disponível em baixo): <input type="text" name="id" value="" autocomplete="off" placeholder="ID da UC" required>
        <br>
        Época de submissão: <input type="text" name="epoca" value="" autocomplete="off" placeholder="Época de submissão" required>
        <br>
        Data de início: <input type="date" id="ini" name="inicio" required>
        <input type="time" step="1" name="di" required>
        <br>
        Data de fim: <input type="date" id="fm" name="fim" required>
        <input type="time" step="1" name="df" required>
        <br>
        <input type="submit" name="env" value="Enviar"/>
    </form>
    <?php
      $resultado->reset();  //Limpar os resultados anteriores
      $resultado = $conexao->query("SELECT * FROM UC"); //Consultar os dados da BD
    ?>
    <b><u>Tabela das edições das Unidades Curriculares:</u></b>
    <table border="1">
      <tr>
        <th>ID</th>
        <th>Sigla UC</th>
        <th>Ano</th>
        <th>RUC</th>
      </tr>
      <?php
        while($row = $resultado->fetchArray(SQLITE3_ASSOC)) //Mostrar cada linha da tabela
        {
          echo "<tr>";
          echo "<td>" . $row['id'] . "</td>";
          echo "<td>" . $row['SIGLA'] . "</td>";
          echo "<td>" . $row['ANO'] . "</td>";
          echo "<td>" . $row['RUC'] . "</td>";
          echo "</tr>";
        }
      ?>
    </table>
    <br><br><br>
    <b><u>NOTAS IMPORTANTES:</u></b>
    <ul><li>No âmbito da criação de uma submissão, <u>a UC deve ter uma edição disponível</u>. Se a edição ainda não tiver sido criada, é necessário criá-la na página anterior, no botão 'Criar/Excluir edição UC'.</li>
    <br><li>As épocas de submissão disponíveis são <u>'Época Normal'</u>, <u>'Época de Recurso'</u> e <u>'Época Especial'</u>.</li>
    <?php  
      $conexao->close();  //Fechar conexão com o banco de dados
    ?>
  </body>
</html>