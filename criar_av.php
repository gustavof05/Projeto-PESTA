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
  if($_SESSION['user'] == "admin" || $_SESSION['user'] == "ruc") //Esta página só funciona para ADMIN'S e RUC'S
  {
//------------------------------------ENVIAR DADOS------------------------------------\\
    if(isset($_POST['env'])) //Se o formulário for enviado
    {
      if(isset($_POST['id'], $_POST['epoca'], $_POST['inicio'], $_POST['fim']))
      {
        //Recuperar dados do formulário
        $inicio = $_POST['inicio'];
        $fim = $_POST['fim'];
        $di = $_POST['di'];
        $df = $_POST['df'];
        $inicio = $inicio . " " . $di;
        $fim = $fim . " " . $df;
        //Preparar a inserção da nova edição de UC
        $stmt = $conexao->prepare("INSERT INTO AVALIACOES (EDICAO_UC, EPOCA, INICIO, FIM) VALUES (:id, :ep, :ini, :fm)");
        $stmt->bindValue(':id', $_POST['id']);
        $stmt->bindValue(':ep', $_POST['epoca']);
        $stmt->bindParam(':ini', $inicio);
        $stmt->bindParam(':fm', $fim);
        $stmt->execute(); //Executar a submissão dos dados na BD
        header('Location: ' . $_SERVER['PHP_SELF']); //Redireciona para a mesma página após a inserção dos dados
        exit();
      }
    }
//-----------------------------------ATUALIZAR DADOS-----------------------------------\\
    if(isset($_POST['editi']))  //Se o botão de edição da data inicial for clicado
    {
      if(isset($_POST['eid'], $_POST['edi']))
      {
        header("Location: $_SERVER[PHP_SELF]?edit_id=" . $_POST['eid'] . "&edit_data=" . $_POST['edi'] . "");  //Redireciona para a mesma página, mas com o ID da linha a ser editada
        exit();
      }
    }
    if(isset($_POST['editf']))  //Se o botão de edição da data final for clicado
    {
      if(isset($_POST['eid'], $_POST['edf']))
      {
        header("Location: $_SERVER[PHP_SELF]?edit_id=" . $_POST['eid'] . "&edit_data=" . $_POST['edf'] . "");  //Redireciona para a mesma página, mas com o ID da linha a ser editada
        exit();
      }
    }
    if(isset($_POST['updatei'])) //Se o botão de atualização da data inicial for clicado
    {
      if(isset($_POST['id'], $_POST['new_inicio'])) 
      {
        $new_inicio = $_POST['new_inicio'];
        $new_di = $_POST['new_di'];
        $new_inicio = $new_inicio . " " . $new_di;
        //Preparar a atualização das datas de início e de fim
        $stmt = $conexao->prepare("UPDATE AVALIACOES SET INICIO = :ini WHERE ID = :id");
        $stmt->bindValue(':id', $_POST['id']);
        $stmt->bindParam(':ini', $new_inicio);
        $stmt->execute();
        header('Location: ' . $_SERVER['PHP_SELF']); //Redireciona para a mesma página após a atualização dos dados
        exit();
      }
    }
    if(isset($_POST['updatef'])) //Se o botão de atualização da data final for clicado
    {
      if(isset($_POST['id'], $_POST['new_fim'])) 
      {
        $new_fim = $_POST['new_fim'];
        $new_df = $_POST['new_df'];
        $new_fim = $new_fim . " " . $new_df;
        //Preparar a atualização das datas de início e de fim
        $stmt = $conexao->prepare("UPDATE AVALIACOES SET FIM = :fm WHERE ID = :id");
        $stmt->bindValue(':id', $_POST['id']);
        $stmt->bindParam(':fm', $new_fim);
        $stmt->execute();
        header('Location: ' . $_SERVER['PHP_SELF']); //Redireciona para a mesma página após a atualização dos dados
        exit();
      }
    }
//-----------------------------------ELIMINAR DADOS-----------------------------------\\
    if(isset($_POST['delete'])) //Se o botão de exclusão for clicado
    {
      if(isset($_POST['did']))
      {
        //Preparar a exclusão da nova edição de UC
        $stmt = $conexao->prepare("DELETE FROM AVALIACOES WHERE ID = :id");
        $stmt->bindValue(':id', $_POST['did']);
        $stmt->execute(); //Executar a submissão dos dados na BD
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
      }
    }
    if($_SESSION['user'] == "ruc")  //"Tabela do RUC"
    {
      $resultado = $conexao->prepare("SELECT AVALIACOES.ID, AVALIACOES.EDICAO_UC, UC.SIGLA, UC.ANO, AVALIACOES.EPOCA, AVALIACOES.INICIO, AVALIACOES.FIM FROM AVALIACOES JOIN UC ON AVALIACOES.EDICAO_UC = UC.id WHERE UC.RUC = :ruc");
      $resultado->bindValue(':ruc', $_SESSION['user_aka']);
      $resultado->execute();
    }
    else $resultado = $conexao->query("SELECT AVALIACOES.ID, AVALIACOES.EDICAO_UC, UC.SIGLA, UC.ANO, AVALIACOES.EPOCA, AVALIACOES.INICIO, AVALIACOES.FIM FROM AVALIACOES JOIN UC ON AVALIACOES.EDICAO_UC = UC.id"); //"Tabela do ADMIN"
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
        <th>EDIÇÃO UC</th>
        <th>ÉPOCA</th>
        <th>INICIO</th>
        <th>FIM</th>
        <th style='color: red'>ELIMINAR LINHA</th>
      </tr>
      <?php
        while($row = $resultado->fetch(PDO::FETCH_ASSOC)) //Mostrar cada linha da tabela
        {
          echo "<tr>";
          echo "<td style='text-align:center'>" . $row['ID'] . "</td>";
          echo "<td style='text-align:center'>" . $row['SIGLA'] . " | " . $row['ANO'] . "</td>";
          echo "<td style='text-align:center'>" . $row['EPOCA'] . "</td>";
          echo "<td style='text-align:center'>";
          //Edição - Data Inicial
          if(isset($_GET['edit_id'], $_GET['edit_data']) && $_GET['edit_id'] == $row['ID'] && $_GET['edit_data'] == "datainicial") //Formulário de edição
          {
            echo "<form action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "' method='POST'>";
            echo "<input type='hidden' name='id' value='" . $row['ID'] . "'>";
            echo "Nova Data de Início: <input type='date' name='new_inicio' required>";
            echo "<input type='time' step='1' name='new_di' required><br>";
            echo "<input type='submit' name='updatei' value='Atualizar'>";
            echo "<input type='button' value='Cancelar' onclick='window.location.href=\"" . $_SERVER["PHP_SELF"] . "\"'>";
            echo "</form>";
          }
          else  //Botão de edição
          {
            echo "<form action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "' method='POST'>";
            echo "<input type='hidden' name='eid' value='" . $row['ID'] . "'>";
            echo "<input type='hidden' name='edi' value='datainicial'>";
            echo $row['INICIO'] . " ";
            echo "<input type='submit' name='editi' value='Editar'>";
            echo "</form>";
          }
          echo "</td>";
          //Edição - Data Final
          echo "<td style='text-align:center'>";
          if(isset($_GET['edit_id'], $_GET['edit_data']) && $_GET['edit_id'] == $row['ID'] && $_GET['edit_data'] == "datafinal") //Formulário de edição
          {
            echo "<form action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "' method='POST'>";
            echo "<input type='hidden' name='id' value='" . $row['ID'] . "'>";
            echo "Nova Data de Início: <input type='date' name='new_fim' required>";
            echo "<input type='time' step='1' name='new_df' required><br>";
            echo "<input type='submit' name='updatef' value='Atualizar'>";
            echo "<input type='button' value='Cancelar' onclick='window.location.href=\"" . $_SERVER["PHP_SELF"] . "\"'>";
            echo "</form>";
          }
          else  //Botão de edição
          {
            echo "<form action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "' method='POST'>";
            echo "<input type='hidden' name='eid' value='" . $row['ID'] . "'>";
            echo "<input type='hidden' name='edf' value='datafinal'>";
            echo $row['FIM'] . " ";
            echo "<input type='submit' name='editf' value='Editar'>";
            echo "</form>";
          }
          echo "</td>";
          //Botão de exclusão
          echo "<td style='text-align:center'>";
          echo "<form action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "' method='POST'>";
          echo "<input type='hidden' name='did' value='" . $row['ID'] . "'>";
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
        echo "Unidade Curricular: <select name='id' required>";
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
          echo "<option value='$uc_id'>$uc_sigla $uc_ano</option>";
        }
        echo "</select><br>";
      ?>
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
    <br><br><br>
    <b><u>NOTA IMPORTANTE:</u></b>
    <ul><li>No âmbito da criação de uma submissão, <u>a Unidade Curricular deve ter uma edição disponível</u>. Se a edição ainda não tiver sido criada, é necessário criá-la na página anterior, no botão 'Criar/Excluir edição UC' ou pedir a um ADMINISTRADOR.</li>
    <?php  
  }
  else header("Location: error.php");
  $conexao = null;  //Fechar conexão com o banco de dados
    ?>
  </body>
</html>