<?php
try {
    $conexao = new PDO('sqlite:bd_pesta.db');
    $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro ao conectar a base de dados: " . $e->getMessage());
}

date_default_timezone_set("Europe/Lisbon");
session_set_cookie_params(['httponly' => true]);
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

if ($_SESSION['user'] == "admin" || $_SESSION['user'] == "ruc") {
    if (isset($_POST["visualizado"]) && isset($_POST["id_relatorio"]) && isset($_POST["titulo"])) {
        foreach ($_POST["id_relatorio"] as $index => $id_relatorio) {
            $visualizado = isset($_POST["visualizado"][$index]) ? 1 : 0;
            $titulo = $_POST["titulo"][$index];

            $query = $conexao->prepare("UPDATE RELATORIOS_SUBMETIDOS SET VISUALIZADO = :visualizado, TITULO = :titulo WHERE EDICAO_AVALIACOES = :id_relatorio");
            $query->bindValue(':visualizado', $visualizado, PDO::PARAM_INT);
            $query->bindValue(':titulo', $titulo, PDO::PARAM_STR);
            $query->bindValue(':id_relatorio', $id_relatorio, PDO::PARAM_INT);
            $query->execute();
        }
        header("Location: exp_rel.php");
        exit();
    }

    $relatorios = $conexao->query("SELECT * FROM RELATORIOS_SUBMETIDOS");
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Visibilidade de relatórios</title>
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
      .edit-button {
        margin-left: 10px;
        cursor: pointer;
      }
    </style>
    <script>
      function enableEdit(id) {
        document.getElementById('titulo_' + id).removeAttribute('readonly');
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
        <form method="POST">
          <?php
            while ($row = $relatorios->fetch(PDO::FETCH_ASSOC)) {
              $visualizado = $row['VISUALIZADO'] ? 'checked' : '';
              $visibilidade = $row['VISUALIZADO'] ? 'Público' : 'Privado';
              echo "<div>
                <label for='titulo_" . htmlspecialchars($row['EDICAO_AVALIACOES']) . "'>Título:</label>
                <input type='text' name='titulo[" . htmlspecialchars($row['EDICAO_AVALIACOES']) . "]' id='titulo_" . htmlspecialchars($row['EDICAO_AVALIACOES']) . "' value='" . htmlspecialchars($row['TITULO']) . "' readonly>
                <button type='button' class='edit-button' onclick='enableEdit(" . htmlspecialchars($row['EDICAO_AVALIACOES']) . ")'>Editar</button>
                <label>Aluno: " . htmlspecialchars($row['ALUNO']) . "</label>
                <input type='checkbox' name='visualizado[" . htmlspecialchars($row['EDICAO_AVALIACOES']) . "]' id='visualizado_" . htmlspecialchars($row['EDICAO_AVALIACOES']) . "]' $visualizado>
                <span>Visibilidade: $visibilidade</span>
                <input type='hidden' name='id_relatorio[" . htmlspecialchars($row['EDICAO_AVALIACOES']) . "]' value='" . htmlspecialchars($row['EDICAO_AVALIACOES']) . "'>
              </div>";
            }
          ?>
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
} else {
    header("Location: error.php");
}

$conexao = null;
?>
