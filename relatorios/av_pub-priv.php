<?php
  $conexao = new SQLite3('bd_pesta.db');
  if(!$conexao) die("Erro ao conectar a base de dados."); //Houve erros na conexão
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
?>
<html lang="pt">
	<head>
		<title>Relat&oacute;rios de exposi&ccedil;&atilde;o</title>
	</head>
	<body>
    <?php
      if($_SERVER["REQUEST_METHOD"] == "POST") //Verificar se o formulário foi enviado
      {
        if(isset($_POST["visualizado"]) && isset($_POST["id_relatorio"])) //Verificar se os dados foram recebidos
        {
          foreach($_POST["id_relatorio"] as $index => $id_relatorio)  //Loop sobre os relatórios enviados pelo formulário
          {
            $visualizado = isset($_POST["visualizado"][$index]) ? 1 : 0;  //Verificar se o relatório foi marcado como visualizado
            //Atualizar o status de visualização na base de dados
            $query = $conexao->prepare("UPDATE DOCUM_SUBMETIDOS SET visualizado = :visualizado WHERE EDICAO_AVALIACOES = :id_relatorio");
            $query->bindValue(':visualizado', $visualizado, SQLITE3_INTEGER);
            $query->bindValue(':id_relatorio', $id_relatorio, SQLITE3_INTEGER);
            $query->execute();
          }
          header("Location: exprel.html");
          exit();
        }
      }
  }
  else header("Location: error.php");
  $conexao->close();  //Fechar conexão com a BD
    ?>
  </body>
</html>