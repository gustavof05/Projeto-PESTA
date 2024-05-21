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
  //Ano letivo (próximas 2 linhas)
  $alatual = date('Y');
  if(($datual > date('Y-01-01')) && ($datual < date('Y-09-20'))) $alatual = date('Y', strtotime('-1 year'));
  session_set_cookie_params(['httponly' => true]);  //Proteção contra roubos de sessão
  session_start();  //Inicio de sessão
  if(!isset($_SESSION['user']))  //Se o usuário não estiver logado
  {
    header('Location: login.php');  //Redirecionar para a página de login
    exit();
  }
  $queryano = $conexao->query("SELECT MIN(ANO) AS amin FROM UC");
  $resultadoano = $queryano->fetch(PDO::FETCH_ASSOC);
  $ano_minimo = $resultadoano['amin'];
  $anoSelecionado = isset($_POST['AnoLetivo']) ? $_POST['AnoLetivo'] : $alatual;
?>
<html lang="pt">
  <head>
    <title>Submiss&atilde;o de relat&oacute;rios</title>
    <style>
      .container {
        max-width: 1024px;
        margin: 0 auto;
        text-align: center;
      }
      body {
        text-align: center;
      }
      .content {
        text-align: left;
        margin: 0 auto;
        max-width: 1024px;
      }
      .banner img {
        width: 100%;
        height: auto;
      }
    </style>
    <script>
        function mostrar() 
        {
          //Barra de seleção
          var todos = document.getElementById("FiltroDisciplinas").value;
          document.getElementById("todos").style.display = "none";  //Não se vê
          if(todos == "0") document.getElementById("todos").style.display = "block";  //Dá para ver
          //UC's selecionadas
          var uc = document.getElementsByClassName("campos-submissao");
          for(var i = 0; i < uc.length; i++) uc[i].style.display = "none";
          var campoSubmissao = document.getElementById(todos);
          if(campoSubmissao) campoSubmissao.style.display = "block";
        }
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
        <?php
          if($_SESSION['user'] == "admin") 
          {
            echo "<b><h2>Bem-vindo, professor " . $_SESSION['user_aka'] . "!</h2></b>";
        ?>
        <form action="login.php" style="display: inline; float: right;" method="POST">
          <input type="submit" name="logout" value="Logout"/>
        </form>
        <form method="POST" action="" id="formAnoLetivo">
          <select id='AnoLetivo' name='AnoLetivo' onchange='anoletivo()'>
            <?php
              echo "<option value='$alatual' selected>$alatual/" . ($alatual+1) . "</option>";
              for($ano = $alatual-1; $ano >= $ano_minimo; $ano--)
              {
                $selected = ($anoSelecionado == $ano) ? "selected" : "";
                echo "<option value='$ano' $selected>$ano/" . ($ano+1) . "</option>";
              }
            ?>
          </select>
        </form>
        <br>
        <form action="criar_uc.php" method="POST">
          <input type="submit" name="cuc" value="Criar/Excluir edição UC"/>
        </form>
        <br>
        <form action="criar_av.php" style="display: inline;" method="POST">
          <input type="submit" name="ca" value="Criar/Editar/Excluir campo de submissão"/>
        </form>
        <br><br><b><h3>Listar Unidades Curriculares:</h3></b>
        <select id='FiltroDisciplinas' name='FiltroDisciplinas' style='width:100%;' onchange='mostrar()'>
          <option value='0' selected>Ver todas as Unidades Curriculares</option>
          <?php
            $queryuc = $conexao->prepare("SELECT id, SIGLA, ANO FROM UC WHERE ANO = :als");
            $queryuc->bindParam(':als', $anoSelecionado);
            $queryuc->execute();
            while($row = $queryuc->fetch(PDO::FETCH_ASSOC)) echo "<option value='" . $row['id'] . "'>" . $row['SIGLA'] . " " . $row['ANO'] . "</option>";
            echo "</select>";
            echo "<div id='todos' style='display:block; text-align:center;'><h3><b>Escolha, em cima, uma Unidade Curricular para ver os campos de submissão disponíveis.</b></h3></div>";
            $queryuc->execute();
            while($row = $queryuc->fetch(PDO::FETCH_ASSOC))
            {
              echo "<div id='" . $row['id'] . "' class='campos-submissao' style='display:none;'><h3><b>Campos de submissão para " . $row['SIGLA'] . ":</b></h3>";
              $query = $conexao->prepare("SELECT * FROM AVALIACOES JOIN UC ON AVALIACOES.EDICAO_UC = UC.id WHERE EDICAO_UC = :id");
              $query->bindValue(':id', $row['id']);
              $query->execute();
              if(count($query->fetchAll(PDO::FETCH_ASSOC)) > 0) 
              {
                echo "Existem campos de submissão disponíveis neste momento:";
                echo "<br><br>";
                $query->execute();
                while($row = $query->fetch(PDO::FETCH_ASSOC))
                {
                  $dinicial = strtotime($row["INICIO"]);
                  $dfinal = strtotime($row["FIM"]);
                  $hoje = strtotime($datual);
                  $cor = ($hoje >= $dinicial && $hoje <= $dfinal) ? "green" : "red";
                  echo "<b>Época:</b> <span style='color:blue;'>". $row["EPOCA"]. "</span> - <b>Prazo: <span style='color: $cor; text-decoration: underline;'>" . $row["FIM"] . "</span></b><br>";
                  echo "<form action='upload.php?uc=" . $row['SIGLA'] . "&ano=" . $row['ANO'] . "' enctype='multipart/form-data' method='POST'>
                    <input type='file' name='file'/>
                    <input type='submit' name='enviar' value='Submeter'/>
                  </form>";
                  echo "<br>";
                }
              }
              else echo "<br><b>Não há nenhuma edição aberta dentro do prazo para submissão de relatórios.</b>";
              echo "</div>";
            }
          }
          else if($_SESSION['user'] == "ruc") 
          {
            echo "<b><h2>Bem-vindo, professor " . $_SESSION['user_aka'] . "!</h2></b>";
        ?>
        <form action="login.php" style="display: inline; float: right;" method="POST">
          <input type="submit" name="logout" value="Logout"/>
        </form>
        <form method="POST" action="" id="formAnoLetivo">
          <select id='AnoLetivo' name='AnoLetivo' onchange='anoletivo()'>
            <?php
              echo "<option value='$alatual' selected>$alatual/" . ($alatual+1) . "</option>";
              for($ano = $alatual-1; $ano >= $ano_minimo; $ano--) 
              {
                $selected = ($anoSelecionado == $ano) ? "selected" : "";
                echo "<option value='$ano' $selected>$ano/" . ($ano+1) . "</option>";
              }
            ?>
          </select>
        </form>
        <br>
        <form action="criar_av.php" style="display: inline;" method="POST">
          <input type="submit" name="ca" value="Criar/Editar/Excluir campo de submissão"/>
        </form>
        <br><br><b><h3>Listar Unidades Curriculares:</h3></b>
        <select id='FiltroDisciplinas' name='FiltroDisciplinas' style='width:100%;' onchange='mostrar()'>
          <option value='0' selected>Ver todas as Unidades Curriculares</option>
          <?php
            $queryuc = $conexao->prepare("SELECT id, SIGLA, ANO FROM UC WHERE ANO = :als");
            $queryuc->bindParam(':als', $anoSelecionado);
            $queryuc->execute();
            while($row = $queryuc->fetch(PDO::FETCH_ASSOC)) echo "<option value='" . $row['id'] . "'>" . $row['SIGLA'] . " " . $row['ANO'] . "</option>";
            echo "</select>";
            echo "<div id='todos' style='display:block; text-align:center;'><h3><b>Escolha, em cima, uma Unidade Curricular para ver os campos de submissão disponíveis.</b></h3></div>";
            $queryuc->execute();
            while($row = $queryuc->fetch(PDO::FETCH_ASSOC)) 
            {
              echo "<div id='" . $row['id'] . "' class='campos-submissao' style='display:none;'><h3><b>Campos de submissão para " . $row['SIGLA'] . ":</b></h3>";
              $query = $conexao->prepare("SELECT * FROM AVALIACOES JOIN UC ON AVALIACOES.EDICAO_UC = UC.id WHERE EDICAO_UC = :id");
              $query->bindValue(':id', $row['id']);
              $query->execute();
              if(count($query->fetchAll(PDO::FETCH_ASSOC)) > 0) 
              {
                echo "Existem campos de submissão disponíveis neste momento:";
                echo "<br><br>";
                $query->execute();
                while($row = $query->fetch(PDO::FETCH_ASSOC)) 
                {
                  $dinicial = strtotime($row["INICIO"]);
                  $dfinal = strtotime($row["FIM"]);
                  $hoje = strtotime($datual);
                  $cor = ($hoje >= $dinicial && $hoje <= $dfinal) ? "green" : "red";
                  echo "<b>Época:</b> <span style='color:blue;'>". $row["EPOCA"]. "</span> - <b>Prazo: <span style='color: $cor; text-decoration: underline;'>" . $row["FIM"] . "</span></b><br>";
                  echo "<form action='upload.php?uc=" . $row['SIGLA'] . "&ano=" . $row['ANO'] . "' enctype='multipart/form-data' method='POST'>
                    <input type='file' name='file'/>
                    <input type='submit' name='enviar' value='Submeter'/>
                  </form>";
                  echo "<br>";
                }
              }
              else echo "<br><b>Não há nenhuma edição aberta dentro do prazo para submissão de relatórios.</b>";
              echo "</div>";
            }
          }
          else echo "<br><b>Usuário desconhecido.</b>";
        ?>
      </div>
    </div>
  </body>
</html>
