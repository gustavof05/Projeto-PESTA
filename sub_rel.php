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
  //Ano letivo/selecionado
  $queryano = $conexao->query("SELECT MIN(ANO) AS amin FROM UC");
  $resultadoano = $queryano->fetch(PDO::FETCH_ASSOC);
  $ano_minimo = $resultadoano['amin'];
  $_SESSION['alat'] = $alatual;
  $_SESSION['alsel'] = isset($_POST['AnoLetivo']) ? $_POST['AnoLetivo'] : $alatual;
  //Listar UC's
  $queryuc = "SELECT id, SIGLA, ANO FROM UC WHERE ANO = :ano";
  if($_SESSION['user'] == "ruc") $queryuc .= " AND UC.RUC = :ruc";
  $resultadouc = $conexao->prepare($queryuc);
  if($_SESSION['user'] == "admin" || $_SESSION['user'] == "ruc") $resultadouc->bindValue(':ano', $_SESSION['alsel']);
  else $resultadouc->bindValue(':ano', $_SESSION['alat']);
  if($_SESSION['user'] == "ruc") $resultadouc->bindValue(':ruc', $_SESSION['user_aka']);
?>
<html lang="pt">
  <head>
    <title>Submiss&atilde;o de relat&oacute;rios</title>
    <style>
      .container 
      {
        max-width: 1024px;
        margin: 0 auto;
        text-align: center;
      }
      .navigation 
      {
        background-color: #006DA7;
        overflow: hidden;
      }
      .navigation a
      {
        float: left;
        display: block;
        color: white;
        text-align: center;
        padding: 7px 16px;
        text-decoration: none;
      }
      .navigation a:hover
      {
        background-color: #0C5595;
        color: black;
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
        function validarFormulario()
        {
          var numeroAluno = document.getElementById("numero_aluno").value;
          if(numeroAluno.length !== 7 || isNaN(numeroAluno))
          {
              alert("O número do aluno deve ter 7 dígitos numéricos.");
              return false; //Impede o envio do formulário
          }
          return true;  //Permite o envio do formulário
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
    //------------------------------------SEPARADOR ADMIN------------------------------------\\
          if($_SESSION['user'] == "admin") 
          {
            echo "<b><h2>Bem-vindo, professor " . $_SESSION['user_aka'] . "!";
        ?>
        <form action="login.php" style="display: inline; float: right;" method="POST">
          <input type="submit" name="logout" value="Logout"/>
        </form>
        <form method="POST" action="" id="formAnoLetivo">
          <select id='AnoLetivo' name='AnoLetivo' onchange='anoletivo()'>
            <?php
              for($ano = $_SESSION['alat']; $ano >= $ano_minimo; $ano--)
              {
                $selected = ($_SESSION['alsel'] == $ano) ? "selected" : "";
                echo "<option value='$ano' $selected>$ano/" . $ano+1 . "</option>";
              }
            ?>
          </select>
        </form>
        </b></h2>
        <nav class="navigation">
            <a href="criar_uc.php">Criar/Excluir edição UC</a>
            <a href="criar_av.php">Definições de épocas de submissão</a>
            <a href="visibilidade_rel.php">Seleção de relatórios em exposição</a>
            <!--a href="insc_alunos.php">Inscrever alunos (não implementado)</a-->
        </nav>
        <!--Selecionar UC-->
        <b><h3>Listar Unidades Curriculares:</b>
        <select id='FiltroDisciplinas' name='FiltroDisciplinas' size='' style='width:100%;' onchange='mostrar()'>
          <option value='0' selected>Ver todas as Unidades Curriculares</option>
          <?php
            $resultadouc->execute();
            while($row = $resultadouc->fetch(PDO::FETCH_ASSOC)) echo "<option value='" . $row['id'] . "'>" . $row['SIGLA'] . "</option>";
            echo "</select></h3>";
            echo "<div id='todos' style='display:block; text-align:center;'><h3><b>Escolha, em cima, uma Unidade Curricular para ver os campos de submissão disponíveis.</b></div>";
            $resultadouc->execute();  //"Refazer" a query
            while($row = $resultadouc->fetch(PDO::FETCH_ASSOC))
            {
              echo "<div id='" . $row['id'] . "' class='campos-submissao' style='display:none;'><h3><b>Campos de submissão para " . $row['SIGLA'] . ":</b></h3>";
              //Verificar se existe uma edição aberta dentro do prazo
              $query = $conexao->prepare("SELECT * FROM AVALIACOES JOIN UC ON AVALIACOES.EDICAO_UC = UC.id WHERE EDICAO_UC = :id"); //Usamos o prepare para executarmos depois (2 vezes)
              $query->bindValue(':id', $row['id']);
              $query->execute();
              if(count($query->fetchAll(PDO::FETCH_ASSOC)) > 0) 
              {
                echo "Existem campos de submissão disponíveis neste momento:";
                echo "<br><br>";
                $query->execute();  //"Refazer" a query
                while($row = $query->fetch(PDO::FETCH_ASSOC))
                {
                  $dinicial = strtotime($row["INICIO"]); //Converte a data do formato string para timestamp
                  $dfinal = strtotime($row["FIM"]); //Converte a data do formato string para timestamp
                  $hoje = strtotime($datual); //Converte a data atual para timestamp
                  $cor = ($hoje >= $dinicial && $hoje <= $dfinal) ? "green" : "red"; //Verifica se o prazo está ultrapassado
                  echo "<b>Época:</b> <span style='color:blue;'>". $row["EPOCA"]. "</span> - <b>Prazo: <span style='color: $cor; text-decoration: underline;'>" . $row["FIM"] . "</span></b><br>";
                  echo "<form action='upload.php?uc=" . $row['SIGLA'] . "&ano=" . $row['ANO'] . "' enctype='multipart/form-data' method='POST' onsubmit='return validarFormulario()'>
                    <input type='hidden' name='EPOCA' value='" . $row['EPOCA'] . "'/>
                    <input type='hidden' name='EDAV' value='" . $row['ID'] . "'/>
                    <b><u>Título do trabalho:</u></b> <input type='text' name='titulo' value='' autocomplete='off' placeholder='Exemplo: Trabalho 1' required/>
                    <br>
                    <b><u>Número do aluno:</u></b> <input type='text' id='numero_aluno' name='aluno' value='' autocomplete='off' placeholder='Exemplo: 1230001' required/>
                    <br>
                    <input type='file' name='file'/>
                    <br><br>
                    <input type='submit' name='enviar' value='Submeter'/>
                  </form>";
                  echo "<br>";
                }
              }
              else echo "<br><b>Não há nenhuma edição aberta dentro do prazo para submissão de relatórios.</b>";
              echo "</div>";
            }
          }
    //------------------------------------SEPARADOR RUC------------------------------------\\
          else if($_SESSION['user'] == "ruc") 
          {
            echo "<b><h2>Bem-vindo, professor " . $_SESSION['user_aka'] . "!";
        ?>
        <form action="login.php" style="display: inline; float: right;" method="POST">
          <input type="submit" name="logout" value="Logout"/>
        </form>
        <form method="POST" action="" id="formAnoLetivo">
          <select id='AnoLetivo' name='AnoLetivo' onchange='anoletivo()'>
            <?php
              for($ano = $_SESSION['alat']; $ano >= $ano_minimo; $ano--) 
              {
                $selected = ($_SESSION['alsel'] == $ano) ? "selected" : "";
                echo "<option value='$ano' $selected>$ano/" . $ano+1 . "</option>";
              }
            ?>
          </select>
        </form>
        </b></h2>
        <nav class="navigation">
            <a href="criar_av.php">Definições de épocas de submissão</a>
            <a href="visibilidade_rel.php">Seleção de relatórios em exposição</a>
            <!--a href="insc_alunos.php">Inscrever alunos (não implementado)</a-->
        </nav>
        <!--Selecionar UC-->
        <b><h3>Listar Unidades Curriculares:</b>
        <select id='FiltroDisciplinas' name='FiltroDisciplinas' size='' style='width:100%;' onchange='mostrar()'>
          <option value='0' selected>Ver todas as Unidades Curriculares</option>
          <?php
            $resultadouc->execute();
            while($row = $resultadouc->fetch(PDO::FETCH_ASSOC)) echo "<option value='" . $row['id'] . "'>" . $row['SIGLA'] . "</option>";
            echo "</select></h3>";
            echo "<div id='todos' style='display:block; text-align:center;'><h3><b>Escolha, em cima, uma Unidade Curricular para ver os campos de submissão disponíveis.</b></div>";
            $resultadouc->execute();  //"Refazer" a query
            while($row = $resultadouc->fetch(PDO::FETCH_ASSOC))
            {
              echo "<div id='" . $row['id'] . "' class='campos-submissao' style='display:none;'><h3><b>Campos de submissão para " . $row['SIGLA'] . ":</b></h3>";
              //Verificar se existe uma edição aberta dentro do prazo
              $query = $conexao->prepare("SELECT * FROM AVALIACOES JOIN UC ON AVALIACOES.EDICAO_UC = UC.id WHERE EDICAO_UC = :id"); //Usamos o prepare para executarmos depois (2 vezes)
              $query->bindValue(':id', $row['id']);
              $query->execute();
              if(count($query->fetchAll(PDO::FETCH_ASSOC)) > 0) 
              {
                echo "Existem campos de submissão disponíveis neste momento:";
                echo "<br><br>";
                $query->execute();  //"Refazer" a query
                while($row = $query->fetch(PDO::FETCH_ASSOC))
                {
                  $dinicial = strtotime($row["INICIO"]); //Converte a data do formato string para timestamp
                  $dfinal = strtotime($row["FIM"]); //Converte a data do formato string para timestamp
                  $hoje = strtotime($datual); //Converte a data atual para timestamp
                  $cor = ($hoje >= $dinicial && $hoje <= $dfinal) ? "green" : "red"; //Verifica se o prazo está ultrapassado
                  echo "<b>Época:</b> <span style='color:blue;'>". $row["EPOCA"]. "</span> - <b>Prazo: <span style='color: $cor; text-decoration: underline;'>" . $row["FIM"] . "</span></b><br>";
                  echo "<form action='upload.php?uc=" . $row['SIGLA'] . "&ano=" . $row['ANO'] . "' enctype='multipart/form-data' method='POST' onsubmit='return validarFormulario()'>
                    <input type='hidden' name='EPOCA' value='" . $row['EPOCA'] . "'/>
                    <input type='hidden' name='EDAV' value='" . $row['ID'] . "'/>
                    <b><u>Título do trabalho:</u></b> <input type='text' name='titulo' value='' autocomplete='off' placeholder='Exemplo: Trabalho 1' required/>
                    <br>
                    <b><u>Número do aluno:</u></b> <input type='text' id='numero_aluno' name='aluno' value='' autocomplete='off' placeholder='Exemplo: 1230001' required/>
                    <br>
                    <input type='file' name='file'/>
                    <br><br>
                    <input type='submit' name='enviar' value='Submeter'/>
                  </form>";
                  echo "<br>";
                }
              }
              else echo "<br><b>Não há nenhuma edição aberta dentro do prazo para submissão de relatórios.</b>";
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
            $resultadouc->execute();
            while($row = $resultadouc->fetch(PDO::FETCH_ASSOC)) echo "<option value='" . $row['id'] . "'>" . $row['SIGLA'] . "</option>";
            echo "</select></h3>";
            echo "<div id='todos' style='display:block; text-align:center;'><h3><b>Escolha, em cima, uma Unidade Curricular para ver os campos de submissão disponíveis.</b></div>";
            $resultadouc->execute();  //"Refazer" a query
            while($row = $resultadouc->fetch(PDO::FETCH_ASSOC))
            {
              echo "<div id='" . $row['id'] . "' class='campos-submissao' style='display:none;'><h3><b>Campos de submissão para " . $row['SIGLA'] . ":</b></h3>";
              //Verificar se existe uma edição aberta dentro do prazo
              $query = $conexao->prepare("SELECT * FROM AVALIACOES JOIN UC ON AVALIACOES.EDICAO_UC = UC.id WHERE EDICAO_UC = :id AND INICIO <= '$datual' AND FIM >= '$datual'");  //Usamos o prepare para executarmos depois (2 vezes)
              $query->bindValue(':id', $row['id']);
              $query->execute();
              if(count($query->fetchAll(PDO::FETCH_ASSOC)) > 0) 
              {
                echo "Existem campos de submissão disponíveis neste momento:";
                echo "<br><br>";
                $query->execute();  //"Refazer" a query
                while($row = $query->fetch(PDO::FETCH_ASSOC))
                {
                  echo "<b>Época:</b> <span style='color: blue;'>". $row["EPOCA"]. "</span> - <b>Prazo: <span style='text-decoration: underline;'>" . $row["FIM"] . "</span></b><br>";
                  echo "<form action='upload.php?uc=" . $row['SIGLA'] . "&ano=" . $row['ANO'] . "' enctype='multipart/form-data' method='POST'>
                    <input type='hidden' name='EPOCA' value='" . $row['EPOCA'] . "'/>
                    <input type='hidden' name='EDAV' value='" . $row['ID'] . "'/>
                    <b><u>Título do trabalho:</u></b> <input type='text' name='titulo' value='' autocomplete='off' placeholder='Exemplo: Trabalho 1' required/>
                    <br>
                    <input type='file' name='file'/>
                    <br><br>
                    <input type='submit' name='enviar' value='Submeter'/>
                  </form>";
                  echo "<br>";
                }
              }
              else echo "<br><b>Não há nenhuma edição aberta dentro do prazo para submissão de relatórios.</b>";
              echo "</div>";
            }
          }
        ?>
        <br><br><br><br><br><br><br>
        <b><u>NOTA IMPORTANTE:</u></b>
          <ul><li>Os ficheiros a submeter devem estar compilados num ficheiro no formato '.zip'.</li>
        <?php
          $conexao = null;  //Fechar conexão com o banco de dados
        ?>
      </div>
    </div>
  </body>
</html>