<?php
  $conexao = new SQLite3('bd_pesta.db');
  if(!$conexao) die("Erro ao conectar a base de dados."); //Houve erros na conexão
  date_default_timezone_set("Europe/Lisbon");
?>
<html lang="pt">
  <head>
    <title>Cria&ccedil;&atilde;o de submiss&otilde;es</title>
  </head>
  <body>
    <?php
      session_set_cookie_params(['httponly' => true]);  //Proteção contra roubos de sessão
      echo "<b><u>Criação de um campo de submissão de relatórios:</u></b>";
      echo "<br><br>";
    ?>
    <form action="" method="POST">
        ID da UC: <input type="text" name="id" value="" autocomplete="off" placeholder="ID da UC" required>
        <br>
        Época de submissão: <input type="text" name="epoca" value="" autocomplete="off" placeholder="Época de submissão" required>
        <br>
        Data de início: <input type="date" id="ini" name="inicio" required>
        <input type="time" step="1" name="di" required>
        <br>
        Data de fim: <input type="date" id="fm" name="fim" required>
        <input type="time" step="1" name="df" required>
        <br>
        <br>
        <input type="submit" name="env" value="Enviar"/>
    </form>
    <?php
      echo "<br><b><u>NOTAS IMPORTANTES:</u></b>";
      echo "<ul><li>No âmbito da criação de uma submissão, <u>a UC deve ter uma edição disponível</u>. Se a edição ainda não tiver sido criada, é necessário criá-la na página anterior, no botão 'Criar edição UC'.</li>";
      echo "<br><li>As épocas de submissão disponíveis são <u>'Época Normal'</u>, <u>'Época de recurso'</u> e <u>'Época especial'</u>.</li>";
      //echo "<br><li>As datas devem ter o formato 'AAAA-MM-DD HH:mm:ss'.</li></ul>";
      $conexao->close();  //Fechar conexão com o banco de dados
    ?>
  </body>
</html>
