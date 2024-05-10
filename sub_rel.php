<?php
  $conexao = new SQLite3('bd_pesta.db');
  if(!$conexao) die("Erro ao conectar a base de dados."); //Houve erros na conexão
  date_default_timezone_set("Europe/Lisbon");
  $datual = date('Y-m-d H:i:s');  //Data de hoje
?>
<html lang="pt">
  <head>
    <title>Submiss&atilde;o de relat&oacute;rios</title>
    <script>
        function mostrar() 
        {
          //Barra de seleção
          var todos = document.getElementById("FiltroDisciplinas").value;
          document.getElementById("todos").style.display = "none";  //Não se vê
          if(todos == "0") document.getElementById("todos").style.display = "block";  //Dá para ver
          //UC's selecionadas
          var uc = document.getElementsByClassName("campos-submissao");
          for (var i = 0; i < uc.length; i++) uc[i].style.display = "none";
          var campoSubmissao = document.getElementById(todos);
          if(campoSubmissao) campoSubmissao.style.display = "block";
        }
    </script>
  </head>
  <body>
    <?php
      session_set_cookie_params(['httponly' => true]);  //Proteção contra roubos de sessão
      session_start();  //Inicio de sessão

      if(!isset($_SESSION['user']))  //Se o usuário não estiver logado
      {
        header('Location: login.php');  //Redirecionar para a página de login
        exit();
      }
//------------------------------------SEPARADOR ADMIN------------------------------------\\
      if ($_SESSION['user'] == "admin") 
      {
        echo "<b><h2>Bem-vindo, professor " . $_SESSION['user_aka'] . "!"
    ?>
    <form action="login.php" style="display: inline; float: right;" method="POST">
      <input type="submit" name="logout" value="Logout"/>
    </form>
    </b></h2><br>
    <form action="criar_uc.php" method="POST">
      <input type="submit" name="cuc" value="Criar/Excluir edição UC"/>
    </form>
    <br>
    <form action="criar_av.php" style="display: inline;" method="POST">
      <input type="submit" name="ca" value="Criar/Editar/Excluir campo de submissão"/>
    </form>
    <!--Selecionar UC-->
    <br><br><b><h3>Listar Unidades Curriculares:</b>
    <select id='FiltroDisciplinas' name='FiltroDisciplinas' size='' style='width:100%;' onchange='mostrar()'>
      <option value='0' selected>Ver todas as Unidades Curriculares</option>
      <?php
        $queryuc = $conexao->prepare("SELECT id, SIGLA FROM UC");
        $resultadouc = $queryuc->execute();
        while($row = $resultadouc->fetchArray(SQLITE3_ASSOC)) echo "<option value='" . $row['id'] . "'>" . $row['SIGLA'] . "</option>";
        echo "</select></h3>";
        echo "<div id='todos' style='display:block; text-align:center;'><h3><b>Escolha, em cima, uma Unidade Curricular para ver os campos de submissão disponíveis.</b></div>";
        $resultadouc->reset();
        while($row = $resultadouc->fetchArray(SQLITE3_ASSOC))
        {
          echo "<div id='" . $row['id'] . "' class='campos-submissao' style='display:none;'><h3><b>Campos de submissão para " . $row['SIGLA'] . ":</b></h3>";
          //Verificar se existe uma edição aberta dentro do prazo
          $query = $conexao->prepare("SELECT * FROM AVALIACOES WHERE EDICAO_UC = :id");
          $query->bindValue(':id', $row['id']);
          $resultado = $query->execute();
          if($resultado)
          {
            if ($resultado->fetchArray() != false) 
            {
              echo "Existem campos de submissão disponíveis neste momento:";
              $resultado->reset();
              echo "<br><br>";
              while ($row = $resultado->fetchArray(SQLITE3_ASSOC))
              {
                $dinicial = strtotime($row["INICIO"]); //Converte a data do formato string para timestamp
                $dfinal = strtotime($row["FIM"]); //Converte a data do formato string para timestamp
                $hoje = strtotime($datual); //Converte a data atual para timestamp
                $cor = ($hoje >= $dinicial && $hoje <= $dfinal) ? "green" : "red"; //Verifica se o prazo está ultrapassado
                echo "<b>Época:</b> <span style='color:blue;'>". $row["EPOCA"]. "</span> - <b>Prazo: <span style='color: $cor; text-decoration: underline;'>" . $row["FIM"] . "</span></b><br>";
        ?>
    <form action="" enctype="multipart/form-data" method="POST">
      <input type="file" name="file"/>
      <input type="submit" name="enviar" value="Submeter"/>
    </form>
    <?php
                if(isset($_POST["enviar"]))
                {
                  $arq = $_FILES["file"];
                  $narq = explode(".", $arq["name"]);
                  if($narq[sizeof($narq)-1] != "pdf") die("Não é possível dar upload do arquivo");
                  else
                  {
                    move_uploaded_file($arq["tmp_name"], "relatorios/" . $arq["name"]);
                    echo "Upload realizado";
                  }
                }
                echo "<br>";
              }
            }
            else echo "<br><b>Não há nenhuma edição aberta dentro do prazo para submissão de relatórios.</b>";
          }
          else echo "<br><b>Erro na consulta da base de dados.</b>";
          echo "</div>";
        }
      }
//------------------------------------SEPARADOR RUC------------------------------------\\
      else if ($_SESSION['user'] == "ruc") 
      {
        echo "<b><h2>Bem-vindo, professor " . $_SESSION['user_aka'] . "!"
    ?>
    <form action="login.php" style="display: inline; float: right;" method="POST">
      <input type="submit" name="logout" value="Logout"/>
    </form>
    <?php
        echo "</b></h2><br>";
    ?>
    <form action="criar_av.php" style="display: inline;" method="POST">
      <input type="submit" name="ca" value="Criar/Editar/Excluir campo de submissão"/>
    </form>
    <!--Selecionar UC-->
    <br><br><b><h3>Listar Unidades Curriculares:</b>
    <select id='FiltroDisciplinas' name='FiltroDisciplinas' size='' style='width:100%;' onchange='mostrar()'>
      <option value='0' selected>Ver todas as Unidades Curriculares</option>
      <?php
        $queryucruc = $conexao->prepare("SELECT id, SIGLA FROM UC WHERE RUC = :ruc_sigla");
        $queryucruc->bindValue(':ruc_sigla', $_SESSION['user_aka']);
        $resultadoucruc = $queryucruc->execute();
        while($row = $resultadoucruc->fetchArray(SQLITE3_ASSOC)) echo "<option value='" . $row['id'] . "'>" . $row['SIGLA'] . "</option>";
        echo "</select></h3>";
        echo "<div id='todos' style='display:block; text-align:center;'><h3><b>Escolha, em cima, uma Unidade Curricular para ver os campos de submissão disponíveis.</b></div>";
        $resultadoucruc->reset();
        while($row = $resultadoucruc->fetchArray(SQLITE3_ASSOC))
        {
          echo "<div id='" . $row['id'] . "' class='campos-submissao' style='display:none;'><h3><b>Campos de submissão para " . $row['SIGLA'] . ":</b></h3>";
          //Verificar se existe uma edição aberta dentro do prazo
          $query = $conexao->prepare("SELECT * FROM AVALIACOES WHERE EDICAO_UC = :id");
          $query->bindValue(':id', $row['id']);
          $resultado = $query->execute();
          if($resultado)
          {
            if ($resultado->fetchArray() != false) 
            {
              echo "Existem campos de submissão disponíveis neste momento:";
              $resultado->reset();
              echo "<br><br>";
              while ($row = $resultado->fetchArray(SQLITE3_ASSOC))
              {
                $dinicial = strtotime($row["INICIO"]); //Converte a data do formato string para timestamp
                $dfinal = strtotime($row["FIM"]); //Converte a data do formato string para timestamp
                $hoje = strtotime($datual); //Converte a data atual para timestamp
                $cor = ($hoje >= $dinicial && $hoje <= $dfinal) ? "green" : "red"; //Verifica se o prazo está ultrapassado
                echo "<b>Época:</b> <span style='color:blue;'>". $row["EPOCA"]. "</span> - <b>Prazo: <span style='color: $cor; text-decoration: underline;'>" . $row["FIM"] . "</span></b><br>";
        ?>
    <form action="" enctype="multipart/form-data" method="POST">
      <input type="file" name="file"/>
      <input type="submit" name="enviar" value="Submeter"/>
    </form>
    <?php
                if(isset($_POST["enviar"]))
                {
                  $arq = $_FILES["file"];
                  $narq = explode(".", $arq["name"]);
                  if($narq[sizeof($narq)-1] != "pdf") die("Não é possível dar upload do arquivo");
                  else
                  {
                    move_uploaded_file($arq["tmp_name"], "relatorios/" . $arq["name"]);
                    echo "Upload realizado";
                  }
                }
                echo "<br>";
              }
            }
            else echo "<br><b>Não há nenhuma edição aberta dentro do prazo para submissão de relatórios.</b>";
          }
          else echo "<br><b>Erro na consulta da base de dados.</b>";
          echo "</div>";
        }
      }
//--------------------------------------SEPARADOR ALUNO--------------------------------------\\
      else
      {
        echo "<b><h2>Bem-vindo, aluno nº" . $_SESSION['user_aka'] . "!"
      ?>
      <form action="login.php" style="display: inline; float: right;" method="POST">
      <input type="submit" name="logout" value="Logout"/>
      </form>
      </b></h2>
      <!--Selecionar UC-->
      <br><br><b><h3>Listar Unidades Curriculares:</b>
      <select id='FiltroDisciplinas' name='FiltroDisciplinas' size='' style='width:100%;' onchange='mostrar()'>
      <option value='0' selected>Ver todas as Unidades Curriculares</option>
      <?php
        $queryuc = $conexao->prepare("SELECT id, SIGLA FROM UC");
        $resultadouc = $queryuc->execute();
        while($row = $resultadouc->fetchArray(SQLITE3_ASSOC)) echo "<option value='" . $row['id'] . "'>" . $row['SIGLA'] . "</option>";
        echo "</select></h3>";
        echo "<div id='todos' style='display:block; text-align:center;'><h3><b>Escolha, em cima, uma Unidade Curricular para ver os campos de submissão disponíveis.</b></div>";
        $resultadouc->reset();
        while($row = $resultadouc->fetchArray(SQLITE3_ASSOC))
        {
          echo "<div id='" . $row['id'] . "' class='campos-submissao' style='display:none;'><h3><b>Campos de submissão para " . $row['SIGLA'] . ":</b></h3>";
          //Verificar se existe uma edição aberta dentro do prazo
          $query = $conexao->prepare("SELECT * FROM AVALIACOES WHERE EDICAO_UC = :id AND INICIO <= '$datual' AND FIM >= '$datual'");
          $query->bindValue(':id', $row['id']);
          $resultado = $query->execute();
          if($resultado)
          {
            if ($resultado->fetchArray() != false) 
            {
              echo "Existem campos de submissão disponíveis neste momento:";
              $resultado->reset();
              echo "<br><br>";
              while ($row = $resultado->fetchArray(SQLITE3_ASSOC))
              {
                echo "<b>Época:</b> <span style='color: blue;'>". $row["EPOCA"]. "</span> - <b>Prazo: <span style='text-decoration: underline;'>" . $row["FIM"] . "</span></b><br>";
      ?>
      <form action="" enctype="multipart/form-data" method="POST">
      <input type="file" name="file"/>
      <input type="submit" name="enviar" value="Submeter"/>
      </form>
      <?php
                if(isset($_POST["enviar"]))
                {
                  $arq = $_FILES["file"];
                  $narq = explode(".", $arq["name"]);
                  if($narq[sizeof($narq)-1] != "pdf") die("Não é possível dar upload do arquivo");
                  else
                  {
                    move_uploaded_file($arq["tmp_name"], "relatorios/" . $arq["name"]);
                    echo "Upload realizado";
                  }
                }
                echo "<br>";
              }
            }
            else echo "<br><b>Não há nenhuma edição aberta dentro do prazo para submissão de relatórios.</b>";
          }
          else echo "<br><b>Erro na consulta da base de dados.</b>";
          echo "</div>";
        }
      }
    ?>
    <?php
      $conexao->close();  //Fechar conexão com o banco de dados
    ?>
  </body>
</html>