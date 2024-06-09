<?php
	try 
	{
		$conexao = new PDO('sqlite:bd_pesta.db');
		$conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	} 
	catch (PDOException $e) 
	{
		die("Erro ao conectar a base de dados: " . $e->getMessage());
	}
	//Query para selecionar relatórios visíveis e suas respectivas UC
	$query = "SELECT RELATORIOS_SUBMETIDOS.TITULO, RELATORIOS_SUBMETIDOS.iD AS ID_RELATORIO, AVALIACOES.EPOCA, UC.SIGLA, UC.ANO, RELATORIOS_SUBMETIDOS.ALUNO FROM RELATORIOS_SUBMETIDOS JOIN AVALIACOES ON RELATORIOS_SUBMETIDOS.EDICAO_AVALIACOES = AVALIACOES.ID JOIN UC ON AVALIACOES.EDICAO_UC = UC.id WHERE RELATORIOS_SUBMETIDOS.VISUALIZADO = 1";
	$relatorios = $conexao->prepare($query);
	$relatorios->execute();
	function gerarCaminhoArquivo($relatorio) 
	{
		$tituloFormatado = str_replace(' ', '', $relatorio['TITULO']);
		$aluno = $relatorio['ALUNO'];
		$anoLetivo = $relatorio['ANO'];
		$sigla = strtolower($relatorio['SIGLA']);
		$epoca = strtolower($relatorio['EPOCA']);
		return "uploads/$anoLetivo/$sigla/$epoca/$aluno";
	}
	// Organizar relatórios por categorias (UC)
	$ucs = array();
	while($row = $relatorios->fetch(PDO::FETCH_ASSOC)) $ucs[$row['SIGLA']][] = $row;	//$ucs = array(SIGLA=>dadosrelatorio1,dadosrelatorio2,...; ...)
?>
<html lang="pt">
	<head>
		<title>Relat&oacute;rios de exposi&ccedil;&atilde;o</title>
	</head>
	<body>
		<br>
		<div style="text-align:center"><h1>Relat&oacute;rios Expostos</h1></div>
		<p>
		<div style="text-align:center"><h2>LEEC</h2></div>
		<br>
		<?php
			reset($ucs);  // Reset the internal pointer of the array to the first element
			foreach($ucs as $uc => $relatoriosuc)	//$relatoriosuc com dados dos relatórios
			{
				echo "<div><h2>$uc</h2></div>";	//SIGLA da UC
				echo "<ul>";
				foreach($relatoriosuc as $relatorio)
				{
					echo "<li>";
					echo "<a href=" . gerarCaminhoArquivo($relatorio) . " target='_blank'>";
					echo "<img src='https://moodle.isep.ipp.pt/theme/image.php/isep/core/1702989422/f/pdf-24' width='18' height='18' style='vertical-align:middle'>";
					echo $relatorio['TITULO'];
					echo "</a>";
					echo "&nbsp&nbsp&nbsp<b><u>Realizado em: " . $relatorio['ANO'] . "</b></u>";
					echo "</li>";
					echo "<p>";
				}
				echo "</ul>";
				echo "<br>";
			}
		?>
	</body>
</html>
<?php
	$conexao = null;	//Fechar conexão com a base de dados
?>