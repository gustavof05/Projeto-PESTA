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
	//echo json_encode($relatorios->fetchAll(PDO::FETCH_ASSOC));
	//exit();
	$ucs = array();
	while($row = $relatorios->fetch(PDO::FETCH_ASSOC)) $ucs[$row['SIGLA']][] = $row;
   //$exemplo = [["UC"=>"LABSI","relatorios"=>$ucs["LABSI"]],["UC"=>"PESTA","relatorios"=>$ucs["PESTA"]]];
/*[
	{"UC":"LABSI", "relatórios":[{"sigla":"LABSI",...}, ... ] },
	{"UC":"PESTA", "relatórios":[{"sigla":"PESTA",...}, ... ] },
	...	
  ]*/
	//echo json_encode($exemplo);
	//exit();
	$lista=array();
	reset($ucs);  // Reset the internal pointer of the array to the first element
	foreach($ucs as $uc => $relatoriosuc) $lista[] = array("UC" => $uc, "relatorios" => $relatoriosuc);
	echo json_encode($lista);
	$conexao = null;	//Fechar conexão com a base de dados
?>