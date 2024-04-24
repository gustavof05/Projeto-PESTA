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
      echo "<b><u>Criação de uma edição de uma Unidade Curricular:</u></b>";
      echo "<br><br>";
    ?>
    <form action="" method="POST">
        Sigla da Unidade Curricular: <input type="text" name="sig" value="" autocomplete="off" placeholder="Sigla da UC" required>
        <br>
        Ano: <input type="text" name="ano" value="" autocomplete="off" placeholder="Ano" required>
        <br>
        Responsável da Unidade Curricular: <input type="text" name="ruc" value="" autocomplete="off" placeholder="RUC" required>
        <br>
        <br>
        <input type="submit" name="env" value="Enviar"/>
    </form>
    <?php
      echo "<br><b><u>NOTAS IMPORTANTES:</u></b>";
      echo "<ul><li>O 'Ano' corresponde ao ano em que se inicia a Unidade Curricular.</li>";
      $conexao->close();  //Fechar conexão com o banco de dados
    ?>
  </body>
</html>