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
  session_set_cookie_params(['httponly' => true]);  //Proteção contra roubos de sessão
  session_start();  //Inicio de sessão
  if(!isset($_SESSION['user'])) //Se o usuário não estiver logado
  {
    header('Location: login.php');  //Redirecionar para a página de login
    exit();
  }
  if($_SESSION['user'] == "admin" || $_SESSION['user'] == "ruc")  //Esta página só funciona para ADMIN'S e RUC'S
  {
    //Formulário de atualização da visibilidade
    if(isset($_POST["id_relatorio"])) //Verificar se os dados foram recebidos
    {
      foreach($_POST["id_relatorio"] as $id_relatorio)  //Loop sobre os relatórios enviados pelo formulário
      {  
        $visualizado = isset($_POST["visualizado"][$id_relatorio]) ? 1 : 0;  //Verificar se o relatório foi marcado como visualizado      
        $titulo = $_POST["titulo"][$id_relatorio];
        //Atualizar o status de visualização na base de dados
        $query = $conexao->prepare("UPDATE RELATORIOS_SUBMETIDOS SET VISUALIZADO = :visualizado, TITULO = :titulo WHERE iD = :id_relatorio");
        $query->bindValue(':visualizado', $visualizado, PDO::PARAM_INT);
        $query->bindValue(':titulo', $titulo, PDO::PARAM_STR);
        $query->bindValue(':id_relatorio', $id_relatorio, PDO::PARAM_INT);
        $query->execute();
      }
      $edicaoSelecionada = $_POST['EdicaoUC']; //Pegar o valor da edição selecionada
      header('Location: ' . $_SERVER['PHP_SELF'] . '?EdicaoUC=' . $edicaoSelecionada); //Redirecionar para esta página após a inserção dos dados
      exit();
    }
    //Edição UC
    $edicaoSelecionada = isset($_POST['EdicaoUC']) ? $_POST['EdicaoUC'] : (isset($_GET['EdicaoUC']) ? $_GET['EdicaoUC'] : '');  //Para manter na mesma página após submeter o formulário
    $edicoesUC = array();
    if($_SESSION['alsel']) 
    {
      $stmt = ("SELECT id, SIGLA FROM UC WHERE ANO = :ano");
      if($_SESSION['user'] == "ruc") $stmt .= " AND RUC = :ruc";
      $resultadouc = $conexao->prepare($stmt);
      $resultadouc->bindValue(':ano', $_SESSION['alsel']);
      if($_SESSION['user'] == "ruc") $resultadouc->bindValue(':ruc', $_SESSION['user_aka']);
      $resultadouc->execute();
      $edicoesUC = $resultadouc->fetchAll(PDO::FETCH_ASSOC);
    }
    //Selecionar os dados para a tabela de acordo com o ano letivo e a edição UC selecionados
    $queryrel = "SELECT * FROM RELATORIOS_SUBMETIDOS JOIN AVALIACOES ON RELATORIOS_SUBMETIDOS.EDICAO_AVALIACOES = AVALIACOES.ID JOIN UC ON AVALIACOES.EDICAO_UC = UC.id WHERE UC.ANO = :ano";
    if($_SESSION['user'] == "ruc") $queryrel .= " AND UC.RUC = :ruc";
    if($edicaoSelecionada) $queryrel .= " AND AVALIACOES.EDICAO_UC = :edicao";
    $relatorios = $conexao->prepare($queryrel);
    $relatorios->bindValue(':ano', $_SESSION['alsel']);
    if($_SESSION['user'] == "ruc") $relatorios->bindValue(':ruc', $_SESSION['user_aka']);
    if($edicaoSelecionada) $relatorios->bindValue(':edicao', $edicaoSelecionada);
    $relatorios->execute();
?>
<html lang="pt">
  <head>
    <title>Visibilidade de relatórios</title>
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
      function enableEdit(id) 
      {
        document.getElementById('titulo_display_' + id).style.display = 'none';
        document.getElementById('titulo_edit_' + id).style.display = 'inline';
        document.getElementById('edit_button_' + id).style.display = 'none';
        document.getElementById('cancel_button_' + id).style.display = 'inline';
        document.getElementById('titulo_edit_' + id).value = document.getElementById('titulo_display_' + id).innerHTML; //Buscar o valor da variável 'atual' para poder editar
      }
      function cancelEdit(id) 
      {
        document.getElementById('titulo_display_' + id).style.display = 'inline';
        document.getElementById('titulo_edit_' + id).style.display = 'none';
        document.getElementById('edit_button_' + id).style.display = 'inline';
        document.getElementById('cancel_button_' + id).style.display = 'none';
        document.getElementById('titulo_edit_' + id).value = document.getElementById('titulo_display_' + id).innerHTML; //Buscar o valor da variável 'atual', ou seja, cancelar a atualização de título
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
        <form method="POST">
        <input type="hidden" name="EdicaoUC" value="<?php echo $edicaoSelecionada; ?>">
          <table border="1">
            <tr>
              <th>TÍTULO</th>
              <th>ALUNO</th>
              <th>VISIBILIDADE</th>
            </tr>
            <?php
              while ($row = $relatorios->fetch(PDO::FETCH_ASSOC)) 
              {
                $visualizado = $row['VISUALIZADO'] ? 'checked' : '';
                $visibilidade = $row['VISUALIZADO'] ? 'Público' : 'Privado';
                $id = $row['iD'];
                echo "<tr>";
                echo "<td style='text-align:center'>";
                echo "<span id='titulo_display_" . $row['iD'] . "'>" . $row['TITULO'] . "</span>";
                echo "<input type='text' name='titulo[" . $row['iD'] . "]' id='titulo_edit_" . $row['iD'] . "' value='" . $row['TITULO'] . "' style='display:none;'>";
                echo "<button type='button' style='margin-left: 10px' id='edit_button_$id' onclick='enableEdit($id)'>Editar Título</button>";
                echo "<button type='button' id='cancel_button_$id' onclick='cancelEdit($id)' style='display:none;'>Cancelar</button>";
                echo "<input type='hidden' name='id_relatorio[$id]' value='$id'>";
                echo "</td>";
                echo "<td style='text-align:center'>". $row['ALUNO'] . "</td>";
                echo "<td style='text-align:center'>";
                echo "<input type='checkbox' name='visualizado[" . $row['iD'] . "]' id='visualizado_" . $row['iD'] . "]' $visualizado> ($visibilidade)";
                echo "</td>";
                echo "</tr>";
              }
            ?>
          </table>
          <br>
          <button type="submit">Salvar</button>
          <br><br><br><br><br><br><br>
            <b><u>NOTA IMPORTANTE:</u></b>
              <ul><li>Quando a 'checkbox' está ativada, o relatório <u>está público</u>. No caso contrário, <u>está privado</u>.</li>
        </form>
      </div>
    </div>
  </body>
</html>
<?php
  } 
  else header("Location: error.php");
  $conexao = null;  // Fechar conexão com a BD
?>