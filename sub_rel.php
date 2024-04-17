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
          var uc = document.getElementById("FiltroDisciplinas").value;
          document.getElementById("todos").style.display = "none";
          document.getElementById("labsi").style.display = "none";
          document.getElementById("pesta").style.display = "none";
          if(uc == "0") document.getElementById("todos").style.display = "block";
          else if(uc == "1") document.getElementById("labsi").style.display = "block";
          else if(uc == "2") document.getElementById("pesta").style.display = "block";
        }
    </script>
  </head>
  <body>
    <?php
      session_set_cookie_params(['httponly' => true]);  //Proteção contra roubos de sessão
      session_start();  //Inicio de sessão

      if (!isset($_SESSION['user']))  //Se o usuário não estiver logado
      {
        header('Location: login.php');  //Redirecionar para a página de login
        exit();
      }

      if ($_SESSION['user'] == "admin") 
      {
        echo "Bem-vindo, professor <b>" . strtoupper($_SESSION['user_aka']) . "</b>!"
    ?>
    <form action="login.php" style="display: inline; float: right;" method="POST">
      <input type="submit" name="logout" value="Logout"/>
    </form>
    <?php
        echo "</br>";
        //Selecionar UC
        echo "<br>Listar Unidades Curriculares:
        <select id='FiltroDisciplinas' name='FiltroDisciplinas' size='' style='width:100%;' onchange='mostrar()'>
          <option value='0' selected>Ver todas as Unidades Curriculares</option>
          <option value='1' >Laborat&oacute;rio de Sistemas (LABSI)</option>
          <option value='2' >Projeto / Est&aacute;gio (PESTA)</option>
        </select>";
        echo "<br><div id='todos' style='display: none;'>
          <!-- Campos de submissão para todas as Unidades Curriculares -->
        </div>
        <div id='labsi' style='display: none;'>
          <!-- Campos de submissão para Laboratório de Sistemas (LABSI) -->
        </div>
        <div id='pesta' style='display: none;'>
          <!-- Campos de submissão para Projeto / Estágio (PESTA) -->
        </div>"
        //Exibir botões para criar, editar/adicionar ou excluir relatórios da UC selecionada
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
      }
//---------------------------------SEPARADOR PROFESSOR-ALUNO---------------------------------\\
      else
      {
        echo "<b><h2>Bem-vindo, aluno nº" . $_SESSION['user_aka'] . "!"
    ?>
    <form action="login.php" style="display: inline; float: right;" method="POST">
      <input type="submit" name="logout" value="Logout"/>
    </form>
    <?php
        echo "</b></h2>";
        //Botões para visualizar UCs e respetivos campos de submissão (com prazos visíveis)
        echo "<br><b><h3>Listar Unidades Curriculares:</b>
        <select id='FiltroDisciplinas' name='FiltroDisciplinas' size='' style='width:100%;' onchange='mostrar()'>
          <option value='0' selected>Ver todas as Unidades Curriculares</option>
          <option value='1' >Laborat&oacute;rio de Sistemas (LABSI)</option>
          <option value='2' >Projeto / Est&aacute;gio (PESTA)</option>
        </select></h3>";
        echo "<div id='todos' style='display: block; text-align:center;'><h3><b>Escolha, em cima, uma Unidade Curricular para ver os campos de submissão disponíveis.</b></div>";
        echo "<div id='labsi' style='display: none;'><h3><b>Campos de submissão para Laboratório de Sistemas (LABSI):</b></h3>";
        //Verificar se existe uma edição aberta dentro do prazo
        $resultado = $conexao->query("SELECT * FROM SUBMISSOES WHERE EDICAO_UC = 1 AND INICIO <= '$datual' AND FIM >= '$datual'");
        if($resultado !== false) 
        {       
          if ($resultado->fetchArray()) //Verifica se há pelo menos uma linha retornada
          {
            echo "Você pode submeter relatórios.<br>";           
            $resultado->reset();  //Reinicia o ponteiro do resultado para o início           
            while($row = $resultado->fetch_Array(SQLITE3_ASSOC)) //Processar cada linha do resultado
            {
              echo "<br> Época: ". $row["EPOCA"]. " - Prazo: " . $row["FIM"] . "<br>";
          
    ?>
    <form action="" enctype="multipart/form-data" method="POST">
      <!--input type="hidden" name="id_submissao" value="<!-?php echo $row['ID']; ?->"-->
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
            }
          }
          else echo "<br><b>Não há nenhuma edição aberta dentro do prazo para submissão de relatórios.</b>";
        }
        echo "</div>";
        echo "<div id='pesta' style='display: none;'><h3><b>Campos de submissão para Projeto / Estágio (PESTA):</b></h3>";
        //Verificar se existe uma edição aberta dentro do prazo
        $resultado = $conexao->query("SELECT * FROM SUBMISSOES WHERE EDICAO_UC = 2 AND INICIO <= '$datual' AND FIM >= '$datual'");
        if($resultado->num_rows > 0) 
        {
          echo "Você pode submeter relatórios.";
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
        }
        else echo "<br><b>Não há nenhuma edição aberta dentro do prazo para submissão de relatórios.</b>";
        echo "</div>";
      }
    ?>
    <?php
      $conexao->close();  //Fechar conexão com o banco de dados
    ?>
  </body>
</html>
