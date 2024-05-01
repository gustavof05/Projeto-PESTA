<?php
  $conexao = new SQLite3('bd_pesta.db');
  if(!$conexao) die("Erro ao conectar a base de dados."); //Houve erros na conexão
  date_default_timezone_set("Europe/Lisbon");
  include('login.php');
//------------------------------------ENVIAR DADOS------------------------------------\\
  if(isset($_POST['env'])) //Se o formulário for enviado
  {
    if(isset($_POST['id'], $_POST['epoca'], $_POST['inicio'], $_POST['fim'])) //Verificar se todos os campos foram preenchidos
    {
      //Recuperar dados do formulário
      $id = $_POST['id'];
      $epoca = $_POST['epoca'];
      $inicio = $_POST['inicio'];
      $fim = $_POST['fim'];
      $di = $_POST['di'];
      $df = $_POST['df'];
      $inicio = $inicio . " " . $di;
      $fim = $fim . " " . $df;
      
      // Verificar se o RUC associado a esta UC corresponde ao RUC do usuário atual
      // Por exemplo, se o RUC do usuário estiver armazenado em $_SESSION['RUC']
      $stmt_ruc = $conexao->prepare("SELECT RUC FROM UC WHERE id = :id");
      $stmt_ruc->bindParam(':id', $id);
      $result_ruc = $stmt_ruc->execute()->fetchArray(SQLITE3_ASSOC);
      $ruc_uc = $result_ruc['RUC'];
      
      if ($docente == "ADMIN") {
        // O RUC corresponde ao RUC do usuário, então proceda com a inserção na base de dados
        $stmt = $conexao->prepare("INSERT INTO AVALIACOES (EDICAO_UC, EPOCA, INICIO, FIM) VALUES (:id, :ep, :ini, :fm)");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':ep', $epoca);
        $stmt->bindParam(':ini', $inicio);
        $stmt->bindParam(':fm', $fim);
        $stmt->execute(); //Executar a submissão dos dados na BD
        header('Location: ' . $_SERVER['PHP_SELF']); //Redireciona para a mesma página após a inserção dos dados
        exit();
      } else {
        // RUC não corresponde ao RUC do usuário, exiba uma mensagem de erro ou redirecione para uma página de acesso negado
        echo "Você não tem permissão para realizar esta ação.";
        exit();
      }
    }
//-----------------------------------ATUALIZAR DADOS-----------------------------------\\
  }
  if(isset($_POST['edit']))  //Se o botão de edição for clicado
  {
    if(isset($_POST['eid']))  //Verificar se o ID da linha a ser editada foi passado via POST
    {
      $eid = $_POST['eid']; //ID da linha a ser editada
      header("Location: $_SERVER[PHP_SELF]?edit_id=$eid");  //Redireciona para a mesma página, mas com o ID da linha sendo editada
      exit();
    }
  }
  if(isset($_POST['update'])) //Se o botão de atualização for clicado
  {
    // Verifique se todos os campos necessários estão definidos
    if(isset($_POST['id'], $_POST['new_inicio'], $_POST['new_fim'])) 
    {
      //Recuperar os dados do formulário de atualização
      $id = $_POST['id'];
      $new_inicio = $_POST['new_inicio'];
      $new_fim = $_POST['new_fim'];
      $new_di = $_POST['new_di'];
      $new_df = $_POST['new_df'];
      $new_inicio = $new_inicio . " " . $new_di;
      $new_fim = $new_fim . " " . $new_df;
      
      // Verificar se o RUC associado a esta UC corresponde ao RUC do usuário atual
      $stmt_ruc = $conexao->prepare("SELECT RUC FROM AVALIACOES WHERE ID = :id");
      $stmt_ruc->bindParam(':id', $id);
      $result_ruc = $stmt_ruc->execute()->fetchArray(SQLITE3_ASSOC);
      $ruc_avaliacao = $result_ruc['RUC'];
      
      if ($ruc_avaliacao === $docente) {
        // O RUC corresponde ao RUC do usuário, então proceda com a atualização na base de dados
        $stmt = $conexao->prepare("UPDATE AVALIACOES SET INICIO = :ini, FIM = :fm WHERE ID = :id");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':ini', $new_inicio);
        $stmt->bindParam(':fm', $new_fim);
        $stmt->execute(); //Executar a atualização dos dados na BD
        header('Location: ' . $_SERVER['PHP_SELF']); //Redireciona para a mesma página após a atualização dos dados
        exit();
      } else {
        // RUC não corresponde ao RUC do usuário, exiba uma mensagem de erro ou redirecione para uma página de acesso negado
        echo "Você não tem permissão para realizar esta ação.";
        exit();
      }
    }
//-----------------------------------ELIMINAR DADOS-----------------------------------\\
  }
  if(isset($_POST['delete'])) //Se o botão de exclusão for clicado
  {
    if(isset($_POST['did']))  //Verificar se o ID da linha a ser excluída foi passado via POST
    {
      //Recuperar o ID da linha a ser excluída
      $did = $_POST['did'];
      
      // Verificar se o RUC associado a esta avaliação corresponde ao RUC do usuário atual
      $stmt_ruc = $conexao->prepare("SELECT RUC FROM AVALIACOES WHERE ID = :id");
      $stmt_ruc->bindParam(':id', $did);
      $result_ruc = $stmt_ruc->execute()->fetchArray(SQLITE3_ASSOC);
      $ruc_avaliacao = $result_ruc['RUC'];
      
      if ($ruc_avaliacao === $docente) {
        // O RUC corresponde ao RUC do usuário, então proceda com a exclusão na base de dados
        $stmt = $conexao->prepare("DELETE FROM AVALIACOES WHERE ID = :id");
        $stmt->bindParam(':id', $did);
        $stmt->execute(); //Executar a exclusão dos dados na BD
        header('Location: ' . $_SERVER['PHP_SELF']); //Redireciona para a mesma página após a exclusão dos dados
        exit();
      } else {
        // RUC não corresponde ao RUC do usuário, exiba uma mensagem de erro ou redirecione para uma página de acesso negado
        echo "Você não tem permissão para realizar esta ação.";
        exit();
      }
    }
  }
  $resultado = $conexao->query("SELECT * FROM AVALIACOES"); //Consultar os dados da BD
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
        <th>EDICAO_UC</th>
        <th>EPOCA</th>
        <th>INICIO</th>
        <th>FIM</th>
        <th style='color: orange'>EDITAR LINHA</th>
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
          //Botão de edição
          echo "<td style='text-align:center'>";
          if(isset($_GET['edit_id']) && $_GET['edit_id'] == $row['ID']) 
          {
            echo "<form action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "' method='POST'>";
            echo "<input type='hidden' name='id' value='" . $row['ID'] . "'>";
            echo "Nova Data de Início: <input type='date' name='new_inicio' value='" . $row['INICIO'] . "' required>";
            echo "<input type='time' step='1' name='new_di' required><br>";
            echo "Nova Data de Fim: <input type='date' name='new_fim' value='" . $row['FIM'] . "' required>";
            echo "<input type='time' step='1' name='new_df' required><br>";
            echo "<input type='submit' name='update' value='Atualizar'>";
            echo "<input type='button' value='Cancelar' onclick='window.location.href=\"" . $_SERVER["PHP_SELF"] . "\"'>";
            echo "</form>";
          }
          else 
          {
            echo "<form action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "' method='POST'>";
            echo "<input type='hidden' name='eid' value='" . $row['ID'] . "'>";
            echo "<input type='submit' name='edit' value='Editar'>";
            echo "</form>";
          }
          echo "</td>";
          //Botão de exclusão
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
        Época de submissão: <select id='FiltroEpocas' name='epoca' size='' required>
          <option value='' selected disabled>Ver todas as épocas disponíveis</option>
          <option value='Época Normal'>Época Normal</option>
          <option value='Época de Recurso'>Época de Recurso</option>
          <option value='Época Especial'>Época Especial</option>
        </select>
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
    <b><u>NOTA IMPORTANTE:</u></b>
    <ul><li>No âmbito da criação de uma submissão, <u>a UC deve ter uma edição disponível</u>. Se a edição ainda não tiver sido criada, é necessário criá-la na página anterior, no botão 'Criar/Excluir edição UC'.</li>
    <?php  
      $conexao->close();  //Fechar conexão com o banco de dados
    ?>
  </body>
</html>
