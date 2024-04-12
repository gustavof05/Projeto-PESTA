<!--?php
  $conexao = new SQLite3('caminho_para_seu_banco_de_dados.sqlite');
  if (!$conexao)  //Verificar se houve erros na conexão
  {
    die("Erro ao conectar ao banco de dados.");
  }
  /*// Verificar se existe uma edição aberta dentro do prazo
  $data_atual = date('Y-m-d H:i:s');
  $consulta = "SELECT COUNT(*) AS total FROM SUBMISSOES WHERE INICIO <= '$data_atual' AND FIM >= '$data_atual'";
  $resultado = $conexao->querySingle($consulta);
  if ($resultado > 0) echo "Você pode submeter relatórios.";
  else echo "Não há nenhuma edição aberta dentro do prazo para submissão de relatórios.";
  // Fechar conexão com o banco de dados
  $conexao->close();*/
?-->
<html lang="pt">
  <head>
    <title>Submiss&atilde;o de relat&oacute;rios</title>
    <script>
        function mostrar() {
            /*var selecao = document.getElementById("FiltroDisciplinas");
            var conteudo = document.getElementById("conteudo");

            //Lógica para determinar o conteúdo a ser exibido com base na opção selecionada
            if (selecao.value == "0") {
                conteudo.innerHTML = "Você selecionou: Todas as Unidades Curriculares";
            } else if (selecao.value == "1") {
                conteudo.innerHTML = "Você selecionou: Laborat&oacute;rio de Sistemas (LABSI)";
            } else if (selecao.value == "2") {
                conteudo.innerHTML = "Você selecionou: Projeto / Est&aacute;gio (PESTA)";
            }*/
            var selecao = document.getElementById("FiltroDisciplinas").value;
            // Ocultar todas as divs
            document.getElementById("todos").style.display = "none";
            document.getElementById("lab_si").style.display = "none";
            document.getElementById("pesta").style.display = "none";
            // Mostrar a div correspondente à opção selecionada
            if (selecao === "0") {
              document.getElementById("todos").style.display = "block";
            } else if (selecao === "1") {
              document.getElementById("lab_si").style.display = "block";
            } else if (selecao === "2") {
              document.getElementById("pesta").style.display = "block";
            }
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
        <select name='FiltroDisciplinas' size='' style='width:100%;' onchange='mostrar()'>
          <option value='0' selected>Todas as Unidades Curriculares</option>
          <option value='1' >Laborat&oacute;rio de Sistemas (LABSI)</option>
          <option value='2' >Projeto / Est&aacute;gio (PESTA)</option>
        </select>";
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
      else
      {
        echo "Bem-vindo, aluno nº" . $_SESSION['user_aka'] . "! "
      ?>
      <form action="login.php" style="display: inline; float: right;" method="POST">
          <input type="submit" name="logout" value="Logout"/>
      </form>
      <?php
        echo "</br>";
        //Exibir botões para visualizar UCs e respetivos campos de submissão (com prazos visíveis)
        echo "<br>Listar Unidades Curriculares:
        <select name='FiltroDisciplinas' size='' style='width:100%;' onchange='mostrar()'>
          <option value='0' selected>Todas as Unidades Curriculares</option>
          <option value='1' >Laborat&oacute;rio de Sistemas (LABSI)</option>
          <option value='2' >Projeto / Est&aacute;gio (PESTA)</option>
        </select>";
        echo "<br><div id='todos' style='display: none;'>
          <!-- Campos de submissão para todas as Unidades Curriculares -->
        </div>
        <div id='lab_si' style='display: none;'>
          <!-- Campos de submissão para Laboratório de Sistemas (LABSI) -->
        </div>
        <div id='pesta' style='display: none;'>
          <!-- Campos de submissão para Projeto / Estágio (PESTA) -->
        </div>"
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
    ?>
    <div id="conteudo"></div> 
  </body>
</html>
