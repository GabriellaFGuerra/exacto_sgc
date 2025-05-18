<?php
session_start (); 
$pagina_link = 'malote_gerenciar';
include		('../mod_includes/php/connect.php');
?>
<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $titulo;?></title>
<meta name="author" content="MogiComp">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="shortcut icon" href="../imagens/favicon.png">
<?php include("../css/style.php"); ?>
<script src="../mod_includes/js/funcoes.js" type="text/javascript"></script>
<script type="text/javascript" src="../mod_includes/js/jquery-1.8.3.min.js"></script>
<!-- TOOLBAR -->
<link href="../mod_includes/js/toolbar/jquery.toolbars.css" rel="stylesheet" />
<link href="../mod_includes/js/toolbar/bootstrap.icons.css" rel="stylesheet">
<script src="../mod_includes/js/toolbar/jquery.toolbar.js"></script>
<!-- TOOLBAR -->
</head>
<body>
<?php	
include		('../mod_includes/php/funcoes-jquery.php');
require_once('../mod_includes/php/verificalogin.php');
include		("../mod_topo/topo.php");
require_once('../mod_includes/php/verificapermissao.php');

?>

<?php
$page = "Malotes &raquo; <a href='malote_gerenciar.php?pagina=malote_gerenciar".$autenticacao."'>Gerenciar</a>";
if($action == "adicionar")
{
	$mal_cliente = $_POST['mal_cliente_id'];
	$mal_lacre = $_POST['mal_lacre'];
	$mal_observacoes = $_POST['mal_observacoes'];
	$sql = "INSERT INTO malote_gerenciar (
	mal_cliente,
	mal_lacre,
	mal_observacoes
	) 
	VALUES 
	(
	'$mal_cliente',
	'$mal_lacre',
	'$mal_observacoes'
	)";
	
	if(mysql_query($sql,$conexao))
	{		
		$mal_fornecedor = $_POST['mal_fornecedor'];
		$mal_tipo_documento = $_POST['mal_tipo_documento'];
		$mal_num_cheque = $_POST['mal_num_cheque'];
		$mal_valor = $_POST['mal_valor'];
		$mal_data_vencimento = $_POST['mal_data_vencimento'];
		$ultimo_id = mysql_insert_id();
		
		//FORNECEDORES - CAMPOS DINÂMICOS
		if(!empty($_POST['fornecedores']) && is_array($_POST['fornecedores']))
		{
			//LIMPA ARRAY
			foreach($_POST['fornecedores'] as $item => $valor) 
			{
				$fornecedores_filtrado[$item] = array_filter($valor);
			}
			//
			
			foreach($fornecedores_filtrado as $item => $valor) 
			{
				if(!empty($valor))
				{
					//INVERTE DATA
					if(isset($valor['mai_data_vencimento']))
					{
						$data_nova = implode("-",array_reverse(explode("/",$valor['mai_data_vencimento'])));
						unset($valor['mai_data_vencimento']);
						$valor['mai_data_vencimento'] = $data_nova;
					}
					//
					
					//INVERTE VALOR
					if(isset($valor['mai_valor']))
					{
						$valor_novo = str_replace(",",".",str_replace(".","",$valor['mai_valor']));
						unset($valor['mai_valor']);
						$valor['mai_valor'] = $valor_novo;
					}
					//
					
					$valor['mai_malote'] = $ultimo_id;
					$sql = "INSERT INTO malote_itens SET 
							mai_fornecedor='".$valor['mai_fornecedor']."',
							mai_tipo_documento='".$valor['mai_tipo_documento']."',
							mai_num_cheque='".$valor['mai_num_cheque']."',
							mai_valor='".$valor['mai_valor']."',
							mai_data_vencimento='".$valor['mai_data_vencimento']."',
							mai_malote='".$valor['mai_malote']."' ";
					//echo $sql;
					if(mysql_query($sql,$conexao))
					{
						//INSERE
					}
					else{ $erro=1; }
				}
			}
		}

		$mal_pg_eletronico = $_FILES['mal_pg_eletronico']["name"];
		$tmp_anexo = $_FILES['mal_pg_eletronico']["tmp_name"];
		$caminho = "../admin/malote/$ultimo_id/";
		if(!file_exists($caminho))
		{
			 mkdir($caminho, 0755, true); 
		}
		foreach($mal_pg_eletronico as $k => $value)
		{
			if($mal_pg_eletronico[$k] != '')
			{
				$extensao = pathinfo($mal_pg_eletronico[$k], PATHINFO_EXTENSION);
				$arquivo = $caminho;
				$arquivo .= md5(mt_rand(1,10000).$mal_pg_eletronico[$k]).'.'.$extensao;
				move_uploaded_file($tmp_anexo[$k], ($arquivo));
			}
			
			$sql = "UPDATE malote_gerenciar SET 
					mal_pg_eletronico = '".$arquivo."'
					WHERE mal_id = $ultimo_id ";
					mysql_query($sql,$conexao);
		}
		
		$mal_pg_eletronico2 = $_FILES['mal_pg_eletronico2']["name"];
		$tmp_anexo2 = $_FILES['mal_pg_eletronico2']["tmp_name"];
		$caminho = "../admin/malote/$ultimo_id/";
		if(!file_exists($caminho))
		{
			 mkdir($caminho, 0755, true); 
		}
		foreach($mal_pg_eletronico2 as $k => $value)
		{
			if($mal_pg_eletronico2[$k] != '')
			{
				$extensao = pathinfo($mal_pg_eletronico2[$k], PATHINFO_EXTENSION);
				$arquivo = $caminho;
				$arquivo .= md5(mt_rand(1,10000).$mal_pg_eletronico2[$k]).'.'.$extensao;
				move_uploaded_file($tmp_anexo2[$k], ($arquivo));
			}
			
			$sql = "UPDATE malote_gerenciar SET 
					mal_pg_eletronico2 = '".$arquivo."'
					WHERE mal_id = $ultimo_id ";
					mysql_query($sql,$conexao);
		}

		/*foreach($mal_fornecedor as $k => $value)
		{
			$sql = "INSERT INTO malote_itens (
					mai_malote,
					mai_fornecedor,
					mai_tipo_documento,
					mai_num_cheque,
					mai_valor,
					mai_data_vencimento
					) 
					VALUES 
					(
					'$ultimo_id',
					'".$mal_fornecedor[$k]."',
					'".$mal_tipo_documento[$k]."',
					'".$mal_num_cheque[$k]."',
					'".str_replace(",",".",str_replace(".","",$mal_valor[$k]))."',
					'".implode("-",array_reverse(explode("/",$mal_data_vencimento[$k])))."'
					)";
					mysql_query($sql,$conexao);
		}*/
		
		echo "
		<SCRIPT language='JavaScript'>
			abreMask(
			'<img src=../imagens/ok.png> Cadastro efetuado com sucesso.<br><br>'+
			'<input value=\' Ok \' type=\'button\' class=\'close_janela\'>' );
		</SCRIPT>
			";
	}
	else
	{
		echo "
		<SCRIPT language='JavaScript'>
			abreMask(
			'<img src=../imagens/x.png> Erro ao efetuar cadastro, por favor tente novamente.<br><br>'+
			'<input value=\' Ok \' type=\'button\' onclick=javascript:window.history.back();>');
		</SCRIPT>
			"; 
	}
}

if($action == 'editar')
{
	$mal_id = $_GET['mal_id'];
	$mal_lacre = $_POST['mal_lacre'];
	$mal_observacoes = $_POST['mal_observacoes'];

	$sqlEnviaEdit = "UPDATE malote_gerenciar SET 
					 mal_lacre = '$mal_lacre',
					 mal_observacoes = '$mal_observacoes'
					 WHERE mal_id = $mal_id ";
	
	if(mysql_query($sqlEnviaEdit,$conexao))
	{
		$ultimo_id = $mal_id;
		$erro=0;
		
		$mal_fornecedor = $_POST['mal_fornecedor'];
		$mal_tipo_documento = $_POST['mal_tipo_documento'];
		$mal_num_cheque = $_POST['mal_num_cheque'];
		$mal_valor = $_POST['mal_valor'];
		$mal_data_vencimento = $_POST['mal_data_vencimento'];
		
		// DEPARTAMENTOS - EXCLUI OS REMOVIDOS

		if(!empty($_POST['fornecedores']) && is_array($_POST['fornecedores']))
		{
			//LIMPA ARRAY
			foreach($_POST['fornecedores'] as $item => $valor) 
			{
				$fornecedores_filtrado[$item] = array_filter($valor);
			}
			//
			$a_excluir = array();
			foreach($fornecedores_filtrado as $item) 
			{
				if(isset($item['mai_id']))
				{
					$a_excluir[] = $item['mai_id'];
				}
			}
			if(!empty($a_excluir))
			{
				$sql = "DELETE FROM malote_itens WHERE mai_malote = $mal_id AND mai_id NOT IN (".implode(",",$a_excluir).") ";
				if(mysql_query($sql,$conexao))
				{
					//echo "Excluido <br>";
				}
				else{ $erro=1; }
			}
			else
			{
				$sql = "DELETE FROM malote_itens WHERE mai_malote = $mal_id ";
				if(mysql_query($sql,$conexao))
				{
					//echo "Excluido todos <br>";
				}
				else{ $erro=1; echo "a"; }
			}
		}
		else
		{
			$sql = "DELETE FROM malote_itens WHERE mai_malote = $mal_id ";
			if(mysql_query($sql,$conexao))
			{
				//echo "Excluido todos 2 <br>";
			}
			else{ $erro=1;  echo "b"; }
		}
		// DEPARTAMENTOS - ATUALIZA OU INSERE NOVOS
		if(!empty($_POST['fornecedores']) && is_array($_POST['fornecedores']))
		{
			//LIMPA ARRAY
			foreach($_POST['fornecedores'] as $item => $valor) 
			{
				$fornecedores_filtrado[$item] = array_filter($valor);
			}
			//
			
			foreach(array_filter($fornecedores_filtrado) as $item => $valor) 
			{					
				if(isset($valor['mai_id']))
				{
					$valor2 = $valor;
					//unset($valor2['mai_id']);
					
					$sql = "UPDATE malote_itens SET 
							mai_fornecedor='".$valor2['mai_fornecedor']."',
							mai_tipo_documento='".$valor2['mai_tipo_documento']."',
							mai_num_cheque='".$valor2['mai_num_cheque']."',
							mai_valor='".str_replace(",",".",str_replace(".","",$valor2['mai_valor']))."',
							mai_data_vencimento='".implode("-",array_reverse(explode("/",$valor2['mai_data_vencimento'])))."'
							WHERE mai_id = ".$valor2['mai_id']."";

					if(mysql_query($sql,$conexao))
					{
						//echo "Atualizado <br>";
					}
					else{ $erro=1;  echo "c"; }
				}
				else
				{
					$valor['mai_malote'] = $mal_id;
					$sql = "INSERT INTO malote_itens SET 
							mai_fornecedor='".$valor['mai_fornecedor']."',
							mai_tipo_documento='".$valor['mai_tipo_documento']."',
							mai_num_cheque='".$valor['mai_num_cheque']."',
							mai_valor='".str_replace(",",".",str_replace(".","",$valor['mai_valor']))."',
							mai_data_vencimento='".implode("-",array_reverse(explode("/",$valor['mai_data_vencimento'])))."',
							mai_malote='".$valor['mai_malote']."' ";
					if(mysql_query($sql,$conexao))
					{
						//echo "Inserido <br>";
					}
					else{ $erro=1; }						
				}
			}
		}
		if($erro != 1)
		{
			echo "
			<SCRIPT language='JavaScript'>
				abreMask(
				'<img src=../imagens/ok.png> Dados alterados com sucesso.<br><br>'+
				'<input value=\' Ok \' type=\'button\' class=\'close_janela\'>' );
			</SCRIPT>
				";
		}
		else
		{
			echo "
			<SCRIPT language='JavaScript'>
				abreMask(
				'<img src=../imagens/x.png> Erro ao alterar dados.<br>Por favor verifique a exclusão de algum registro relacionado a outra tabela.<br><br>'+
				'<input value=\' Ok \' type=\'button\' class=\'close_janela\'>' );
			</SCRIPT>
			";
		}
	}
	else
	{
		echo "
		<SCRIPT language='JavaScript'>
			abreMask(
			'<img src=../imagens/x.png> Erro ao alterar dados, por favor tente novamente.<br><br>'+
			'<input value=\' Ok \' type=\'button\' onclick=javascript:window.history.back();>');
		</SCRIPT>
		";
	}
}

if($action == 'excluir')
{
	$mal_id = $_GET['mal_id'];
	$sql = "DELETE FROM malote_gerenciar WHERE mal_id = '$mal_id'";
				
	if(mysql_query($sql,$conexao))
	{
		echo "
		<SCRIPT language='JavaScript'>
			abreMask(
			'<img src=../imagens/ok.png> Exclusão realizada com sucesso<br><br>'+
			'<input value=\' OK \' type=\'button\' class=\'close_janela\'>' );
		</SCRIPT>
			";
	}
	else
	{
		echo "
		<SCRIPT language='JavaScript'>
			abreMask(
			'<img src=../imagens/x.png> Este item não pode ser excluído pois está relacionado com alguma tabela.<br><br>'+
			'<input value=\' Ok \' type=\'button\' onclick=javascript:window.history.back(); >');
		</SCRIPT>
		";
	}
}
if($action == 'ativar')
{
	$mal_id = $_GET['mal_id'];
	$sql = "UPDATE malote_gerenciar SET mal_status = 1 WHERE mal_id = '$mal_id'";
				
	if(mysql_query($sql,$conexao))
	{
		echo "
		<SCRIPT language='JavaScript'>
			abreMask(
			'<img src=../imagens/ok.png> Ativação realizada com sucesso<br><br>'+
			'<input value=\' OK \' type=\'button\' class=\'close_janela\'>' );
		</SCRIPT>
			";
	}
	else
	{
		echo "
		<SCRIPT language='JavaScript'>
			abreMask(
			'<img src=../imagens/x.png> Erro ao alterar dados, por favor tente novamente.<br><br>'+
			'<input value=\' Ok \' type=\'button\' onclick=javascript:window.history.back(); >');
		</SCRIPT>
		";
	}
}
if($action == 'desativar')
{
	$mal_id = $_GET['mal_id'];
	$sql = "UPDATE malote_gerenciar SET mal_status = 0 WHERE mal_id = '$mal_id'";
				
	if(mysql_query($sql,$conexao))
	{
		echo "
		<SCRIPT language='JavaScript'>
			abreMask(
			'<img src=../imagens/ok.png> Desativação realizada com sucesso<br><br>'+
			'<input value=\' OK \' type=\'button\' class=\'close_janela\'>' );
		</SCRIPT>
			";
	}
	else
	{
		echo "
		<SCRIPT language='JavaScript'>
			abreMask(
			'<img src=../imagens/x.png> Erro ao alterar dados, por favor tente novamente.<br><br>'+
			'<input value=\' Ok \' type=\'button\' onclick=javascript:window.history.back(); >');
		</SCRIPT>
		";
	}
}

if($action == 'baixa_malote')
{
	$mal_id = $_GET['mal_id'];
	//$mai_id = $_POST['mai_id'];
	$mai_observacao = $_POST['mai_observacao'];
	foreach($_POST['check'] as $mai_id)
	{
		$sql = "UPDATE malote_itens SET mai_baixado = 1, mai_data_baixa = '".date("Y-m-d H:i:s")."', mai_observacao = '$mai_observacao' WHERE mai_id = '$mai_id'";
		if(mysql_query($sql,$conexao))
		{

		}
		else
		{
			$erro = 1;
		}
	}
		
	if($erro == 0)
	{
		echo "
		<SCRIPT language='JavaScript'>
			abreMask(
			'<img src=../imagens/ok.png> Baixa realizada com sucesso<br><br>'+
			'<input value=\' OK \' type=\'button\' class=\'close_janela\'>' );
		</SCRIPT>
			";
	}
	else
	{
		echo "
		<SCRIPT language='JavaScript'>
			abreMask(
			'<img src=../imagens/x.png> Erro ao alterar dados, por favor tente novamente.<br><br>'+
			'<input value=\' Ok \' type=\'button\' onclick=javascript:window.history.back(); >');
		</SCRIPT>
		";
	}
}
if($action == 'baixa_malote_todos')
{
	$mal_id = $_GET['mal_id'];
	$mai_observacao = $_POST['mai_observacao'];
	$sql = "UPDATE malote_itens SET mai_baixado = 1, mai_data_baixa = '".date("Y-m-d H:i:s")."', mai_observacao = '$mai_observacao' WHERE mai_malote = '$mal_id'";
	if(mysql_query($sql,$conexao))
	{
		echo "
		<SCRIPT language='JavaScript'>
			abreMask(
			'<img src=../imagens/ok.png> Baixa realizada com sucesso<br><br>'+
			'<input value=\' OK \' type=\'button\' class=\'close_janela\'>' );
		</SCRIPT>
			";
	}
	else
	{
		echo "
		<SCRIPT language='JavaScript'>
			abreMask(
			'<img src=../imagens/x.png> Erro ao alterar dados, por favor tente novamente.<br><br>'+
			'<input value=\' Ok \' type=\'button\' onclick=javascript:window.history.back(); >');
		</SCRIPT>
		";
	}
}

$num_por_pagina = 10;
if(!$pag){$primeiro_registro = 0; $pag = 1;}
else{$primeiro_registro = ($pag - 1) * $num_por_pagina;}
$fil_malote = $_REQUEST['fil_malote'];
if($fil_malote == '')
{
	$malote_query = " 1 = 1 ";
}
else
{
	$malote_query = " (mal_id = '".$fil_malote."') ";
}
$fil_lacre = $_REQUEST['fil_lacre'];
if($fil_lacre == '')
{
	$lacre_query = " 1 = 1 ";
}
else
{
	$lacre_query = " (mal_lacre LIKE '%".$fil_lacre."%') ";
}
$fil_cheque = $_REQUEST['fil_cheque'];
if($fil_cheque == '')
{
	$cheque_query = " 1 = 1 ";
}
else
{
	$cheque_query = " 
	mal_id IN 
	(
		SELECT mal_id FROM malote_itens
		LEFT JOIN malote_gerenciar ON malote_gerenciar.mal_id = malote_itens.mai_malote 
		WHERE mai_num_cheque = '".$fil_cheque."'
	)
	";
}
$fil_nome = $_REQUEST['fil_nome'];
if($fil_nome == '')
{
	$nome_query = " 1 = 1 ";
}
else
{
	$nome_query = " (cli_nome_razao LIKE '%".$fil_nome."%') ";
}
$fil_data_inicio = implode('-',array_reverse(explode('/',$_REQUEST['fil_data_inicio'])));
$fil_data_fim = implode('-',array_reverse(explode('/',$_REQUEST['fil_data_fim'])));
if($fil_data_inicio == '' && $fil_data_fim == '')
{
	$data_query = " 1 = 1 ";
}
elseif($fil_data_inicio != '' && $fil_data_fim == '')
{
	$data_query = " mai_data_vencimento >= '$fil_data_inicio' ";
}
elseif($fil_data_inicio == '' && $fil_data_fim != '')
{
	$data_query = " mai_data_vencimento <= '$fil_data_fim 23:59:59' ";
}
elseif($fil_data_inicio != '' && $fil_data_fim != '')
{
	$data_query = " mai_data_vencimento BETWEEN '$fil_data_inicio' AND '$fil_data_fim 23:59:59' ";
}
$fil_baixado = $_REQUEST['fil_baixado'];
if($fil_baixado == '')
{
	$baixado_query = " 1 = 1 ";
	$fil_baixado_n = "Baixado?";
}
elseif($fil_baixado == 1)
{
	$baixado_query = " 
	mal_id NOT IN 
	(
		SELECT mal_id FROM malote_itens
		LEFT JOIN malote_gerenciar ON malote_gerenciar.mal_id = malote_itens.mai_malote 
		WHERE mai_baixado IS NULL
	)
	AND
	mal_id IN 
	(
		SELECT mal_id FROM malote_itens
		LEFT JOIN malote_gerenciar ON malote_gerenciar.mal_id = malote_itens.mai_malote 
		WHERE ".$data_query."
	)
	";
	switch($fil_baixado)
	{
		case 0: $fil_baixado_n = "Não";break;
		case 1: $fil_baixado_n = "Sim";break;
		case '': $fil_baixado_n = "Todos";break;
	}
}
elseif($fil_baixado == '0')
{
	$baixado_query = " 
	mal_id IN 
	(
		SELECT mal_id FROM malote_itens
		LEFT JOIN malote_gerenciar ON malote_gerenciar.mal_id = malote_itens.mai_malote 
		WHERE mai_baixado IS NULL
	) 
	AND
	mal_id IN 
	(
		SELECT mal_id FROM malote_itens
		LEFT JOIN malote_gerenciar ON malote_gerenciar.mal_id = malote_itens.mai_malote 
		WHERE ".$data_query."
	)
	";
	switch($fil_baixado)
	{
		case 0: $fil_baixado_n = "Não";break;
		case 1: $fil_baixado_n = "Sim";break;
		case '': $fil_baixado_n = "Todos";break;
	}
}

$sql = "SELECT * FROM malote_gerenciar 
		LEFT JOIN ( cadastro_clientes 
			INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id )
		ON cadastro_clientes.cli_id = malote_gerenciar.mal_cliente
		WHERE ucl_usuario = '".$_SESSION['usuario_id']."' AND ".$malote_query." AND ".$lacre_query." AND ".$nome_query." AND
			  ".$baixado_query." AND ".$cheque_query."
		ORDER BY mal_data_cadastro DESC
		LIMIT $primeiro_registro, $num_por_pagina ";
		
$cnt = "SELECT COUNT(*) FROM malote_gerenciar
		LEFT JOIN ( cadastro_clientes 
			INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id )
		ON cadastro_clientes.cli_id = malote_gerenciar.mal_cliente
		WHERE ucl_usuario = '".$_SESSION['usuario_id']."' AND ".$malote_query." AND ".$lacre_query." AND ".$nome_query." AND
			  ".$baixado_query." ";

$query = mysql_query($sql,$conexao);
$rows = mysql_num_rows($query);
if($pagina == "malote_gerenciar")
{
	echo "
	<div class='centro'>
		<div class='titulo'> $page  </div>
		<div id='botoes'><input value='Novo Malote' type='button' onclick=javascript:window.location.href='malote_gerenciar.php?pagina=adicionar_malote_gerenciar".$autenticacao."'; /></div>
		<div class='filtro'>
			<form name='form_filtro' id='form_filtro' enctype='multipart/form-data' method='post' action='malote_gerenciar.php?pagina=malote_gerenciar".$autenticacao."'>
			<input name='fil_malote' id='fil_malote' value='$fil_malote' placeholder='N° malote'  size='10'>
			<input name='fil_lacre' id='fil_lacre' value='$fil_lacre' placeholder='N° lacre'  size='10'>
			<input name='fil_cheque' id='fil_cheque' value='$fil_cheque' placeholder='N° Cheque' size='10'>
			<input name='fil_nome' id='fil_nome' value='$fil_nome' placeholder='Cliente'>
			<input type='text' name='fil_data_inicio' id='fil_data_inicio' placeholder='Data Início'  size='10' value='".implode('/',array_reverse(explode('-',$fil_data_inicio)))."' onkeypress='return mascaraData(this,event);'>
			<input type='text' name='fil_data_fim' id='fil_data_fim' placeholder='Data Fim'  size='10' value='".implode('/',array_reverse(explode('-',$fil_data_fim)))."' onkeypress='return mascaraData(this,event);'>
			<select name='fil_baixado' id='fil_baixado'>
				<option value='$fil_baixado'>$fil_baixado_n</option>
				<option value='1'>Sim</option>
				<option value='0'>Não</option>
				<option value=''>Todos</option>
			</select>
			<input type='submit' value='Filtrar'> 
			</form>
		</div>
		";
		if ($rows > 0)
		{
			echo "
			<table align='center' width='100%' border='0' cellspacing='0' cellpadding='10' class='bordatabela'>
				<tr>
					<td class='titulo_tabela'>N° Malote</td>
					<td class='titulo_tabela'>N° Lacre</td>
					<td class='titulo_tabela'>Cliente</td>
					<td class='titulo_tabela'>Observação</td>
					<td class='titulo_tabela' align='center'>Data Cadastro</td>
					<td class='titulo_tabela' align='center'>Protocolo</td>
					<td class='titulo_tabela' align='center'>Baixado?</td>
					<td class='titulo_tabela' align='center'>Gerenciar</td>
				</tr>";
				$c=0;
				for($x = 0; $x < $rows ; $x++)
				{
					$mal_id = mysql_result($query, $x, 'mal_id');
					$mal_lacre = mysql_result($query, $x, 'mal_lacre');
					$cli_nome_razao = mysql_result($query, $x, 'cli_nome_razao');
					$mal_observacoes = mysql_result($query, $x, 'mal_observacoes');
					$mal_data_cadastro = implode("/",array_reverse(explode("-",substr(mysql_result($query, $x, 'mal_data_cadastro'),0,10))));
					$mal_hora_cadastro = substr(mysql_result($query, $x, 'mal_data_cadastro'),11,5);
					
					
					$sql_baixado = "SELECT * FROM malote_gerenciar 
									LEFT JOIN malote_itens ON malote_itens.mai_malote = malote_gerenciar.mal_id
									WHERE mal_id = $mal_id ";
					$query_baixado = mysql_query($sql_baixado,$conexao);
					$rows_baixado = mysql_num_rows($query_baixado);
					if($rows_baixado > 0)
					{
						$mai_baixado = 1;
						while($row_baixado = mysql_fetch_array($query_baixado))
						{
							if($row_baixado['mai_baixado'] != 1)
							{
								$mai_baixado = 0;
							}
						}
					}
					switch($mai_baixado)
					{
						case 0: $mai_baixado_n = "<span class='vermelho'>Não</span>";break;
						case 1: $mai_baixado_n = "<span class='verde'>Sim</span>";break;
					}
					if ($c == 0)
					{
					 $c1 = "linhaimpar";
					 $c=1;
					}
					else
					{
					$c1 = "linhapar";
					 $c=0;
					} 
					echo "
					<script type='text/javascript'>
						jQuery(document).ready(function($) {
					
							// Define any icon actions before calling the toolbar
							$('.toolbar-icons a').on('click', function( event ) {
								$(this).click();
								
							});
							$('#normal-button-$mal_id').toolbar({content: '#user-options-$mal_id', position: 'top', hideOnClick: true});
							$('#normal-button-bottom').toolbar({content: '#user-options', position: 'bottom'});
							$('#normal-button-small').toolbar({content: '#user-options-small', position: 'top', hideOnClick: true});
							$('#button-left').toolbar({content: '#user-options', position: 'left'});
							$('#button-right').toolbar({content: '#user-options', position: 'right'});
							$('#link-toolbar').toolbar({content: '#user-options', position: 'top' });
						});
					</script>
					<div id='user-options-$mal_id' class='toolbar-icons' style='display: none;'>
						<a href='malote_gerenciar.php?pagina=exibir_malote_gerenciar&mal_id=$mal_id$autenticacao'><img border='0' src='../imagens/icon-exibir.png'></a>
						<a href='malote_gerenciar.php?pagina=editar_malote_gerenciar&mal_id=$mal_id$autenticacao'><img border='0' src='../imagens/icon-editar.png'></a>
						<a onclick=\"
							abreMask(
								'Deseja realmente excluir o malote <b>$mal_nome_razao</b>?<br><br>'+
								'<input value=\' Sim \' type=\'button\' onclick=javascript:window.location.href=\'malote_gerenciar.php?pagina=malote_gerenciar&action=excluir&mal_id=$mal_id$autenticacao\';>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+
								'<input value=\' Não \' type=\'button\' class=\'close_janela\'>');
							\">
							<img border='0' src='../imagens/icon-excluir.png'></i>
						</a>
					</div>
					";
					echo "<tr class='$c1'>
							  <td><a href='malote_gerenciar.php?pagina=exibir_malote_gerenciar&mal_id=$mal_id$autenticacao'><b>$mal_id</b></a></td>
							  <td>$mal_lacre</td>
							  <td>$cli_nome_razao</td>
							  <td>$mal_observacoes</td>
							  <td align='center'>$mal_data_cadastro<br><span class='detalhe'>$mal_hora_cadastro</span></td>
							  <td align='center'><img class='mouse' src='../imagens/icon-pdf.png' onclick=javascript:window.open('malote_imprimir.php?mal_id=$mal_id$autenticacao');></td>
							  <td align='center'>$mai_baixado_n</td>
							  <td align=center><div id='normal-button-$mal_id' class='settings-button'><img src='../imagens/icon-cog-small.png' /></div></td>
						  </tr>";
				}
				echo "</table>";
				$variavel = "&pagina=malote_gerenciar&fil_malote=$fil_malote&fil_lacre=$fil_lacre&fil_nome=$fil_nome&fil_data_inicio=$fil_data_inicio&fil_data_fim=$fil_data_fim&fil_baixado=$fil_baixado".$autenticacao."";
				include("../mod_includes/php/paginacao.php");
		}
		else
		{
			echo "<br><br><br>Não há nenhum malote cadastrado.";
		}
		echo "
		<div class='titulo'>  </div>				
	</div>";
}
if($pagina == 'adicionar_malote_gerenciar')
{
	echo "	
	<form name='form_malote_gerenciar' id='form_malote_gerenciar' enctype='multipart/form-data' method='post' action='malote_gerenciar.php?pagina=malote_gerenciar&action=adicionar$autenticacao'>
	<div class='centro'>
		<div class='titulo'> $page &raquo; Adicionar  </div>
		<table align='center' cellspacing='0' width='950'>
			<tr>
				<td align='left'>
					<div class='formtitulo'>Selecione o cliente</div>
					<div class='suggestion'>
						<input name='mal_cliente_id' id='mal_cliente_id'  type='hidden' value='' />
						<input name='mal_cliente' id='mal_cliente' type='text' placeholder='Digite as iniciais ou CNPJ do cliente para procurar' autocomplete='off' />
						<div class='suggestionsBox' id='suggestions' style='display: none;'>
							<div class='suggestionList' id='autoSuggestionsList'>
								&nbsp;
							</div>
						</div>
					</div>
					<p>
					<br><br>
					<input name='mal_lacre' id='mal_lacre' type='text' placeholder='N° do lacre'/>
					<p>
					<div class='quadro'>
					<div class='formtitulo'>Adicione os documentos deste malote</div>
					<div id='p_scents_malote'>
					<div class='bloco_fornecedores'>
						<p>
							<label for='itens'>
								<input name='fornecedores[1][mai_fornecedor]' id='mai_fornecedor' placeholder='Fornecedor'>									
								<select name='fornecedores[1][mai_tipo_documento]' id='mai_tipo_documento'>
									<option value=''>Tipo Documento</option>
									<option value='Boleto'>Boleto</option>
									<option value='Guia'>Guia</option>
									<option value='Depósito'>Depósito</option>
									<option value='Reembolso'>Reembolso</option>
									<option value='Carteira'>Carteira</option>
									<option value='Cheque sem retorno'>Cheque sem retorno</option>
									<option value='O.P.'>O.P.</option>
									<option value='O.P. Agendada'>O.P. Agendada</option>
									<option value='Outros'>Outros</option>
								</select>
								<input type='text' name='fornecedores[1][mai_num_cheque]' id='mai_num_cheque' value='' placeholder='N° Cheque' />
								<input type='text' id='mai_valor' size='12' name='fornecedores[1][mai_valor]' value='' placeholder='Valor (em R$)' onkeypress='return MascaraMoeda(this,\".\",\",\",event);' />
								<input type='text' id='mai_data_vencimento' name='fornecedores[1][mai_data_vencimento]' value='' placeholder='Data Vencimento' onkeypress='return mascaraData(this,event);' />
								&nbsp;
								<input type='button' id='addScnt_malote' value='Adicionar'>
							</label>
						</p>
					</div>
					</div>
					</div>
					<p>
					<p>Pagamento eletrônico: <input name='mal_pg_eletronico[]' id='mal_pg_eletronico' type='file' >
					<p>Pagamento eletrônico 2: <input name='mal_pg_eletronico2[]' id='mal_pg_eletronico2' type='file' >
					<p>
					<textarea name='mal_observacoes' id='mal_observacoes' placeholder='Observações'></textarea>
					<p>
					<center>
					<div id='erro' align='center'>&nbsp;</div>
					<input type='button' id='bt_malote_gerenciar' value='Salvar' />&nbsp;&nbsp;&nbsp;&nbsp; 
					<input type='button' id='botao_cancelar' onclick=javascript:window.location.href='malote_gerenciar.php?pagina=malote_gerenciar".$autenticacao."'; value='Cancelar'/></center>
					</center>
				</td>
			</tr>
		</table>
		<div class='titulo'> </div>
	</div>
	</form>
	";
}

if($pagina == 'editar_malote_gerenciar')
{
	$mal_id = $_GET['mal_id'];
	$sqledit = "SELECT * FROM malote_gerenciar 
				LEFT JOIN ( cadastro_clientes 
					INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id )
				ON cadastro_clientes.cli_id = malote_gerenciar.mal_cliente
				LEFT JOIN ( malote_itens 
					LEFT JOIN cadastro_fornecedores ON cadastro_fornecedores.for_id = malote_itens.mai_fornecedor
					)
				ON malote_itens.mai_malote = malote_gerenciar.mal_id
				WHERE ucl_usuario = '".$_SESSION['usuario_id']."' AND mal_id = '$mal_id'";
	$queryedit = mysql_query($sqledit,$conexao);
	$rowsedit = mysql_num_rows($queryedit);

	if($rowsedit > 0)
	{
		$mal_cliente = mysql_result($queryedit, 0, 'mal_cliente');
		$cli_nome_razao = mysql_result($queryedit, 0, 'cli_nome_razao');
		$cli_cnpj = mysql_result($queryedit, 0, 'cli_cnpj');
		$mal_lacre = mysql_result($queryedit, 0, 'mal_lacre');
		$mal_pg_eletronico = mysql_result($queryedit, 0, 'mal_pg_eletronico');
		$mal_pg_eletronico2 = mysql_result($queryedit, 0, 'mal_pg_eletronico2');
		$mal_observacoes = mysql_result($queryedit, 0, 'mal_observacoes');
		echo "
		<form name='form_malote_gerenciar' id='form_malote_gerenciar' enctype='multipart/form-data' method='post' action='malote_gerenciar.php?pagina=malote_gerenciar&action=editar&mal_id=$mal_id$autenticacao'>
		<div class='centro'>
			<div class='titulo'> $page &raquo; Editar: $mal_nome </div>
			<table align='center' cellspacing='0'>
				<tr>
					<td align='left'>
						<div class='formtitulo'>Selecione o cliente</div>
						<div class='suggestion'>
							<input name='mal_cliente_id' id='mal_cliente_id'  type='hidden' value='$mal_cliente' />
							<input name='mal_cliente_block' id='mal_cliente_block' value='$cli_nome_razao ($cli_cnpj)' type='text' placeholder='Digite as iniciais ou CNPJ do cliente para procurar' autocomplete='off' readonly />
							<div class='suggestionsBox' id='suggestions' style='display: none;'>
								<div class='suggestionList' id='autoSuggestionsList'>
									&nbsp;
								</div>
							</div>
						</div>
						<p>
						<br><br>
						<input name='mal_lacre' id='mal_lacre' value='$mal_lacre' type='text' placeholder='N° do lacre'/>
						<p>
						<div class='quadro'  style='display:table;'>
						<div class='formtitulo'>Documentos deste malote</div>
						<input type='checkbox' class='todos' onclick='marcardesmarcar();' /> Marcar todos
						<div id='p_scents_malote'>
							";
							$sql_fornecedores = "SELECT * FROM malote_itens 
												 WHERE mai_malote = $mal_id ORDER BY mai_id ASC";
							$query_fornecedores = mysql_query($sql_fornecedores,$conexao);
							$rows_fornecedores = mysql_num_rows($query_fornecedores);
							if($rows_fornecedores > 0)
							{
								$baixado = 1;
								$x=0;
								while($row_fornecedores = mysql_fetch_array($query_fornecedores))
								{
									$x++;
									$mai_id = $row_fornecedores['mai_id'];
									$mai_baixado = $row_fornecedores['mai_baixado'];
									if($mai_baixado != 1 && $b != 1){$baixado = 0; $b=1;}
									echo "
										<div class='bloco_fornecedores'>										
											<input type='hidden' name='fornecedores[$x][mai_id]' id='mai_id' value='".$row_fornecedores['mai_id']."'>
											<br>
											";
											if($mai_baixado != 1)
											{
												echo "<input type='checkbox' class='marcar' name='check[]' value='$mai_id'>";
											}
											elseif($mai_baixado == 1)
											{
												echo "<input disabled readonly='readonly' type='checkbox' class='marcar' name='check[]' value='$mai_id'>";
											}
											echo "
											<input name='fornecedores[$x][mai_fornecedor]' id='mai_fornecedor' value='".$row_fornecedores['mai_fornecedor']."'>
											<select name='fornecedores[$x][mai_tipo_documento]' id='mai_tipo_documento'>
												<option value='".$row_fornecedores['mai_tipo_documento']."'>".$row_fornecedores['mai_tipo_documento']."</option>
												<option value='Boleto'>Boleto</option>
												<option value='Guia'>Guia</option>
												<option value='Depósito'>Depósito</option>
												<option value='Reembolso'>Reembolso</option>
												<option value='Carteira'>Carteira</option>
												<option value='Cheque sem retorno'>Cheque sem retorno</option>
												<option value='O.P.'>O.P.</option>
												<option value='O.P. Agendada'>O.P. Agendada</option>
												<option value='Outros'>Outros</option>
											</select>
											<input type='text' id='mai_num_cheque' name='fornecedores[$x][mai_num_cheque]' value='".$row_fornecedores['mai_num_cheque']."' placeholder='N° Cheque' />
											<input type='text' id='mai_valor' size='12' name='fornecedores[$x][mai_valor]' value='".number_format($row_fornecedores['mai_valor'],2,',','.')."' placeholder='Valor (em R$)' onkeypress='return MascaraMoeda(this,\".\",\",\",event);' />
											<input type='text' id='mai_data_vencimento' name='fornecedores[$x][mai_data_vencimento]' value='".implode("/",array_reverse(explode("-",$row_fornecedores['mai_data_vencimento'])))."' placeholder='Data Vencimento' onkeypress='return mascaraData(this,event);' />
											<input type='button' id='addScnt_malote' value='Adicionar'> &nbsp; <input type='button' id='remScnt_malote' value='X'> &nbsp;&nbsp;
											";
											/*if($row_fornecedores['mai_baixado'] != 1)
											{
												echo "
												<a class='mouse' onclick=\"
													abreMaskAcao(
														'<form name=\'form_baixa\' id=\'form_baixa\' enctype=\'multipart/form-data\' method=\'post\' action=\'malote_gerenciar.php?pagina=editar_malote_gerenciar&action=baixa_malote&mal_id=$mal_id&mai_id=$mai_id$autenticacao\'>'+
														'Deseja realmente dar baixa neste documento?.<br><br>'+
														'<input type=\'hidden\' name=\'mai_id\' id=\'mai_id\' value=\'".$mai_id."\'>'+
														'<input type=\'text\' name=\'mai_observacao\' id=\'mai_observacao\' placeholder=\'Observação\'><br><br>'+
														'<div id=\'erro\'>&nbsp;</div><br>'+
														'<input value=\' Sim \' id=\'bt_baixa\' type=\'submit\'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+
														'<input value=\' Não \' type=\'button\' class=\'close_janela\'>'+
														'</form>');
													\">
													<img src='../imagens/icon-baixa.png' border='0' valign='middle'>
												</a>
												";
											}*/
											echo "
											
										</label>
										
									</div>
									";
								}
								/*if($baixado != 1)
								{
									echo "
									<a class='baixa_todos' onclick=\"
										abreMaskAcao(
											'<form name=\'form_baixa\' id=\'form_baixa\' enctype=\'multipart/form-data\' method=\'post\' action=\'malote_gerenciar.php?pagina=editar_malote_gerenciar&action=baixa_malote_todos&mal_id=$mal_id&mai_id=$mai_id$autenticacao\'>'+
											'Deseja realmente dar baixa em todos documentos deste malote?.<br><br>'+
											'<input type=\'text\' name=\'mai_observacao\' id=\'mai_observacao\' placeholder=\'Observação\'><br><br>'+
											'<div id=\'erro\'>&nbsp;</div><br>'+
											'<input value=\' Sim \' id=\'bt_baixa\' type=\'submit\'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+
											'<input value=\' Não \' type=\'button\' class=\'close_janela\'>'+
											'</form>');
										\">
										dar baixa em todos: <img src='../imagens/icon-baixa.png' border='0' valign='middle'> &nbsp;&nbsp;&nbsp;&nbsp;
									</a>
									<br><br>
									";
								}*/
								
								
							}
							else
							{
								echo "
								<div class='bloco_fornecedores'>
										<input type='hidden' name='fornecedores[1][mai_id]' id='mai_id'>
										<input name='fornecedores[1][mai_fornecedor]' id='mai_fornecedor' placeholder='Fornecedor'>
										<select name='fornecedores[1][mai_tipo_documento]' id='mai_tipo_documento'>
											<option value=''>Tipo Documento</option>
											<option value='Boleto'>Boleto</option>
											<option value='Guia'>Guia</option>
											<option value='Depósito'>Depósito</option>
											<option value='Reembolso'>Reembolso</option>
											<option value='Outros'>Outros</option>
										</select>
										<input type='text' id='mai_num_cheque' name='fornecedores[1][mai_num_cheque]' value='' placeholder='N° Cheque' />
										<input type='text' id='mai_valor' size='12' name='fornecedores[1][mai_valor]' value='' placeholder='Valor (em R$)' onkeypress='return MascaraMoeda(this,\".\",\",\",event);' />
										<input type='text' id='mai_data_vencimento' name='fornecedores[1][mai_data_vencimento]' value='' placeholder='Data Vencimento' onkeypress='return mascaraData(this,event);' />
										&nbsp;
										<input type='button' id='addScnt_malote' value='Adicionar'>									
									</label>
								</div>
								";
							}
							echo "
						</div>
						<p><input value=' Baixar selecionados ' id='bt_baixa' onClick='darBaixa($mal_id)' type='button'>
						
						</div>
						<p>Pagamento eletrônico: <input name='mal_pg_eletronico[]' id='mal_pg_eletronico' type='file' >
						";
						if($mal_pg_eletronico != '')
						{
							echo "Anexo atual: <a href='".$mal_pg_eletronico."' target='_blank'><img src='../imagens/pdf.png' valign='middle'></a>";
						}
						echo "
						<p>Pagamento eletrônico 2: <input name='mal_pg_eletronico2[]' id='mal_pg_eletronico2' type='file' >
						";
						if($mal_pg_eletronico2 != '')
						{
							echo "Anexo atual 2: <a href='".$mal_pg_eletronico2."' target='_blank'><img src='../imagens/pdf.png' valign='middle'></a>";
						}
						echo "
						<p>
						<textarea name='mal_observacoes' id='mal_observacoes' placeholder='Observações'>$mal_observacoes</textarea>
						<p>
						<center>
						<div id='erro' align='center'>&nbsp;</div>
						<input type='button' id='bt_malote_gerenciar' value='Salvar' />&nbsp;&nbsp;&nbsp;&nbsp; 
						<input type='button' id='botao_cancelar' onclick=javascript:window.location.href='malote_gerenciar.php?pagina=malote_gerenciar$autenticacao'; value='Cancelar'/></center>
						</center>
					</td>
				</tr>
			</table>
			<div class='titulo'>   </div>
		</div>
		</form>
		";
	}
}	
if($pagina == 'exibir_malote_gerenciar')
{
	$mal_id = $_GET['mal_id'];
	$sqledit = "SELECT * FROM malote_gerenciar 
				LEFT JOIN ( cadastro_clientes 
					INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id )
				ON cadastro_clientes.cli_id = malote_gerenciar.mal_cliente
				LEFT JOIN ( malote_itens 
					LEFT JOIN cadastro_fornecedores ON cadastro_fornecedores.for_id = malote_itens.mai_fornecedor
					)
				ON malote_itens.mai_malote = malote_gerenciar.mal_id
				WHERE ucl_usuario = '".$_SESSION['usuario_id']."' AND mal_id = '$mal_id'";
	$queryedit = mysql_query($sqledit,$conexao);
	$rowsedit = mysql_num_rows($queryedit);

	if($rowsedit > 0)
	{
		$mal_cliente = mysql_result($queryedit, 0, 'mal_cliente');
		$cli_nome_razao = mysql_result($queryedit, 0, 'cli_nome_razao');
		$cli_cnpj = mysql_result($queryedit, 0, 'cli_cnpj');
		$mal_lacre = mysql_result($queryedit, 0, 'mal_lacre');
		$mal_observacoes = mysql_result($queryedit, 0, 'mal_observacoes');
		$mal_data_cadastro = implode("/",array_reverse(explode("-",substr(mysql_result($queryedit, 0, 'mal_data_cadastro'),0,10))));
		$mal_hora_cadastro = substr(mysql_result($queryedit, 0, 'mal_data_cadastro'),11,5);
		$mal_pg_eletronico = mysql_result($queryedit, 0, 'mal_pg_eletronico');
		$mal_pg_eletronico2 = mysql_result($queryedit, 0, 'mal_pg_eletronico2');
		echo "
		<form name='form_malote_gerenciar' id='form_malote_gerenciar' enctype='multipart/form-data' method='post' action='malote_gerenciar.php?pagina=malote_gerenciar&action=editar&mal_id=$mal_id$autenticacao'>
		<div class='centro'>
			<div class='titulo'> $page &raquo; Editar: $mal_nome </div>
			<table align='center' cellspacing='0'>
				<tr>
					<td align='left'>
						<b>N° Malote:</b> $mal_id <p>
						<b>N° Lacre:</b> $mal_lacre <p>
						<b>Cliente:</b> $cli_nome_razao <p>
						<div class='quadro'>
						<div class='formtitulo'>Documentos deste malote</div>
							";
							$sql_fornecedores = "SELECT * FROM malote_itens 
												 WHERE mai_malote = $mal_id ORDER BY mai_id ASC";
							$query_fornecedores = mysql_query($sql_fornecedores,$conexao);
							$rows_fornecedores = mysql_num_rows($query_fornecedores);
							if($rows_fornecedores > 0)
							{
								$baixado = 1;
								echo "
								<table align='center' width='100%' border='0' cellspacing='0' cellpadding='10' class='bordatabela'>
									<tr>
										<td class='titulo_tabela'>Fornecedor</td>
										<td class='titulo_tabela'>Tipo Documento</td>
										<td class='titulo_tabela'>N° Cheque</td>
										<td class='titulo_tabela'>Valor</td>
										<td class='titulo_tabela' align='center'>Data Vencimento</td>
										<td class='titulo_tabela' align='center'>Baixado?</td>
										<td class='titulo_tabela' align='center'>Data Baixa</td>
									</tr>
								";
								for($y=0; $y < $rows_fornecedores; $y++)
								{
									$mai_fornecedor = mysql_result($query_fornecedores,$y,'mai_fornecedor');
									$mai_tipo_documento = mysql_result($query_fornecedores,$y,'mai_tipo_documento');
									$mai_num_cheque = mysql_result($query_fornecedores,$y,'mai_num_cheque');
									$mai_valor = number_format(mysql_result($query_fornecedores,$y,'mai_valor'),2,',','.');
									$mai_data_vencimento = implode("/",array_reverse(explode("-",mysql_result($query_fornecedores,$y,'mai_data_vencimento'))));
									$mai_baixado = mysql_result($query_fornecedores,$y,'mai_baixado');
									$mai_data_baixa = implode("/",array_reverse(explode("-",substr(mysql_result($query_fornecedores, $y, 'mai_data_baixa'),0,10))));
									$mai_hora_baixa = substr(mysql_result($query_fornecedores, $y, 'mai_data_baixa'),11,5);
									switch($mai_baixado)
									{
										case 0: $mai_baixado_n = "<span class='vermelho'>Não</span>";break;
										case 1: $mai_baixado_n = "<span class='verde'>Sim</span>";break;
									}
									echo "<tr class='$c1' >
											<td>".$mai_fornecedor."</td>
											<td>".$mai_tipo_documento."</td>
											<td>".$mai_num_cheque."</td>
											<td>R$ ".$mai_valor."</td>
											<td align='center'>".$mai_data_vencimento."</td>
											<td align='center'>".$mai_baixado_n."</td>
											<td align='center'>$mai_data_baixa<br><span class='detalhe'>$mai_hora_baixa</span></td>
										  </tr>";
																										
								}								
							echo "</table>";
							}
						echo "	
						</div>
						<p>Pagamento eletrônico: 
						";
						if($mal_pg_eletronico != '')
						{
							echo "<a href='".$mal_pg_eletronico."' target='_blank'><img src='../imagens/pdf.png' valign='middle'></a>";
						}
						echo "
						<p>Pagamento eletrônico 2: 
						";
						if($mal_pg_eletronico2 != '')
						{
							echo "<a href='".$mal_pg_eletronico2."' target='_blank'><img src='../imagens/pdf.png' valign='middle'></a>";
						}
						echo "
						<p>
						<p>
						<b>Observações:</b> $mal_observacoes <p>
						<b>Data Cadastro:</b> $mal_data_cadastro às $mal_hora_cadastro
						<p>
						<center>
						<div id='erro' align='center'>&nbsp;</div>
						<input type='button' id='botao_cancelar' onclick=javascript:window.location.href='malote_gerenciar.php?pagina=malote_gerenciar$autenticacao'; value='Voltar'/></center>
						</center>
					</td>
				</tr>
			</table>
			<div class='titulo'>   </div>
		</div>
		</form>
		";
	}
}	
include('../mod_rodape/rodape.php');
?>
</body>
</html>