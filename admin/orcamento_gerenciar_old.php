<?php
session_start (); 
$pagina_link = 'orcamento_gerenciar';
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
<script type="text/javascript" src="../mod_includes/js/tooltip.js"></script>

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
$page = "Orçamentos &raquo; <a href='orcamento_gerenciar.php?pagina=orcamento_gerenciar".$autenticacao."'>Gerenciar</a>";
if($action == "adicionar")
{
	$orc_cliente = $_POST['orc_cliente_id'];
	$orc_tipo_servico = $_POST['orc_tipo_servico'];
	$orc_andamento = $_POST['orc_andamento'];
	$orc_observacoes = $_POST['orc_observacoes'];
	$orc_status = $_POST['orc_status'];
	$sql = "INSERT INTO orcamento_gerenciar (
	orc_cliente,
	orc_tipo_servico,
	orc_andamento,
	orc_observacoes
	) 
	VALUES 
	(
	'$orc_cliente',
	'$orc_tipo_servico',
	'$orc_andamento',
	'$orc_observacoes'
	)";
	
	if(mysql_query($sql,$conexao))
	{		
		$orc_fornecedor = $_POST['orc_fornecedor'];
		$orc_valor = $_POST['orc_valor'];
		$orc_obs = $_POST['orc_obs'];
		$orc_anexo = $_FILES['orc_anexo']["name"];
		$tmp_anexo = $_FILES['orc_anexo']["tmp_name"];
		$ultimo_id = str_pad(mysql_insert_id(),6,"0",STR_PAD_LEFT);
		$ultimo_id2 = mysql_insert_id();
		$caminho = "../admin/anexos/$ultimo_id/";
		
		$sql_status = "INSERT INTO cadastro_status_orcamento (
					   sto_orcamento,
					   sto_status,
					   sto_observacao 
					   )
					   VALUES
					   (
					   '$ultimo_id',
					   '$orc_status',
					   null
					   )";

		
		mysql_query($sql_status,$conexao);
		
		if(!file_exists($caminho))
		{
			 mkdir($caminho, 0755, true); 
		}
		foreach($orc_fornecedor as $k => $value)
		{
			if($orc_anexo[$k] != '')
			{
				$extensao = pathinfo($orc_anexo[$k], PATHINFO_EXTENSION);
				$arquivo = $caminho;
				$arquivo .= md5(mt_rand(1,10000).$orc_anexo[$k]).'.'.$extensao;
				move_uploaded_file($tmp_anexo[$k], ($arquivo));
			}
			
			$sql = "INSERT INTO orcamento_fornecedor (
					orf_orcamento,
					orf_fornecedor,
					orf_valor,
					orf_obs,
					orf_anexo
					) 
					VALUES 
					(
					'$ultimo_id',
					'".$orc_fornecedor[$k]."',
					'".str_replace(",",".",str_replace(".","",$orc_valor[$k]))."',
					'".$orc_obs[$k]."',
					'".$arquivo."'
					)";
					mysql_query($sql,$conexao);
		}
		
		$orc_planilha = $_FILES['orc_planilha']["name"];
		$tmp_planilha = $_FILES['orc_planilha']["tmp_name"];
		$ultimo_id = str_pad($ultimo_id2,6,"0",STR_PAD_LEFT);;
		$caminho = "../admin/planilha/$ultimo_id/";
		if(!file_exists($caminho))
		{
			 mkdir($caminho, 0755, true); 
		}
		foreach($orc_planilha as $k => $value)
		{
			if($orc_planilha[$k] != '')
			{
				$extensao = pathinfo($orc_planilha[$k], PATHINFO_EXTENSION);
				$arquivo = $caminho;
				$arquivo .= md5(mt_rand(1,10000).$orc_planilha[$k]).'.'.$extensao;
				move_uploaded_file($tmp_planilha[$k], ($arquivo));
			
			
				$sql = "UPDATE orcamento_gerenciar SET 
						orc_planilha = '".$arquivo."'
						WHERE orc_id = $ultimo_id ";
						mysql_query($sql,$conexao);
			}
		}
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
	$orc_id = $_GET['orc_id'];
	$sql_status_antigo = "SELECT * FROM orcamento_gerenciar 
					      LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = orcamento_gerenciar.orc_cliente
						  LEFT JOIN cadastro_tipos_servicos ON cadastro_tipos_servicos.tps_id = orcamento_gerenciar.orc_tipo_servico
						  LEFT JOIN cadastro_status_orcamento h1 ON h1.sto_orcamento = orcamento_gerenciar.orc_id 
						  WHERE h1.sto_id = (SELECT MAX(h2.sto_id) FROM cadastro_status_orcamento h2 where h2.sto_orcamento = h1.sto_orcamento) AND						  		
						   	  	orc_id = $orc_id ";
	$query_status_antigo = mysql_query($sql_status_antigo,$conexao);
	$rows_status_antigo = mysql_num_rows($query_status_antigo);
	if($rows_status_antigo > 0)
	{
		$status_antigo = mysql_result($query_status_antigo,0,'sto_status');
		$cli_nome_razao = mysql_result($query_status_antigo,0,'cli_nome_razao');
		$tps_nome = mysql_result($query_status_antigo,0,'tps_nome');
	}
	$orc_cliente = $_POST['orc_cliente_id'];
	$orc_andamento = $_POST['orc_andamento'];
	$orc_observacoes = $_POST['orc_observacoes'];
	$orc_tipo_servico = $_POST['orc_tipo_servico'];
	$orc_status = $_POST['orc_status'];
	if($orc_status == 3)
	{
		$orc_data_aprovacao = "'".date("Y-m-d")."'";
		/*$sql_seleciona_renova = "SELECT * FROM documento_gerenciar WHERE doc_orcamento = '$orc_id' AND doc_cliente = '$orc_cliente'";
		$query_seleciona_renova = mysql_query($sql_seleciona_renova,$conexao);
		$rows_seleciona_renova = mysql_num_rows($query_seleciona_renova);
		if($rows_seleciona_renova > 0)
		{
			$doc_id = mysql_result($query_seleciona_renova, 0, 'doc_id');
			$doc_periodicidade = mysql_result($query_seleciona_renova, 0, 'doc_periodicidade');
			$doc_data_vigencia = mysql_result($query_seleciona_renova, 0, 'doc_data_vigencia');
			if($doc_periodicidade == 6)
			{
				$nova_data = date("Y-m-d",strtotime("+ 6 month",strtotime($doc_data_vigencia)));
			}
			elseif($doc_periodicidade == 12)
			{
				$nova_data = date("Y-m-d",strtotime("+ 1 year",strtotime($doc_data_vigencia)));
			}
			elseif($doc_periodicidade == 24)
			{
				$nova_data = date("Y-m-d",strtotime("+ 2 year",strtotime($doc_data_vigencia)));
			}
			elseif($doc_periodicidade == 36)
			{
				$nova_data = date("Y-m-d",strtotime("+ 3 year",strtotime($doc_data_vigencia)));
			}
			$sql_renova = "UPDATE documento_gerenciar SET doc_data_vigencia = '".$nova_data."' WHERE doc_id = $doc_id";
			mysql_query($sql_renova,$conexao);
		}*/
	}
	else
	{
		$orc_data_aprovacao = "null";
	}
	$sqlEnviaEdit = "UPDATE orcamento_gerenciar SET 
					 orc_tipo_servico = '$orc_tipo_servico',
					 orc_andamento = '$orc_andamento',
					 orc_observacoes = '".str_replace("'","",str_replace('"','',$orc_observacoes))."',
					 orc_data_aprovacao = $orc_data_aprovacao
					 WHERE orc_id = $orc_id ";
	
	if(mysql_query($sqlEnviaEdit,$conexao))
	{
		$ultimo_id = $orc_id;
		$erro=0;
		
		// INICIA STATUS ORÇAMENTO
		$sql_verifica = "SELECT * FROM orcamento_gerenciar 
						 LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = orcamento_gerenciar.orc_cliente
						 LEFT JOIN cadastro_tipos_servicos ON cadastro_tipos_servicos.tps_id = orcamento_gerenciar.orc_tipo_servico
						 LEFT JOIN cadastro_status_orcamento h1 ON h1.sto_orcamento = orcamento_gerenciar.orc_id 
						 WHERE h1.sto_id = (SELECT MAX(h2.sto_id) FROM cadastro_status_orcamento h2 where h2.sto_orcamento = h1.sto_orcamento) AND
						 orc_id = '$ultimo_id'";
		$query_verifica = mysql_query($sql_verifica,$conexao);
		$rows_verifica = mysql_num_rows($query_verifica);
		if($rows_verifica > 0)
		{
			$sto_status = mysql_result($query_verifica,0,'sto_status');
		}
		if($sto_status != $orc_status)
		{
			$sql_status = "INSERT INTO cadastro_status_orcamento (
					   sto_orcamento,
					   sto_status,
					   sto_observacao 
					   )
					   VALUES
					   (
					   '$ultimo_id',
					   '$orc_status',
					   null
					   )";
			if(mysql_query($sql_status,$conexao)){ }else {$erro=1;}
		}
		
		
		
		$orc_fornecedor = $_POST['orc_fornecedor'];
		$orc_valor = $_POST['orc_valor'];
		$orc_obs = $_POST['orc_obs'];
		$orc_anexo = $_FILES['orc_anexo']["name"];
		$tmp_anexo = $_FILES['orc_anexo']["tmp_name"];
		$caminho = "../admin/anexos/$ultimo_id/";
		if(!file_exists($caminho))
		{
			 mkdir($caminho, 0755, true); 
		}
		

		$sql_orcamento = "SELECT * FROM orcamento_fornecedor
						  INNER JOIN orcamento_gerenciar ON orcamento_gerenciar.orc_id = orcamento_fornecedor.orf_orcamento
						  WHERE orf_orcamento = $orc_id ";
		$query_orcamento = mysql_query($sql_orcamento,$conexao);
		$query_f = mysql_query($sql_orcamento,$conexao);
		$rows_orcamento = mysql_num_rows($query_orcamento);
		if($rows_orcamento > 0)
		{
			for($x=0;$x < $rows_orcamento; $x++)
			{
				$fornecedor = mysql_result($query_orcamento, $x, 'orf_fornecedor');
				$anexo = mysql_result($query_orcamento, $x, 'orf_anexo');
				//echo "X: $x | Fornecedor: $fornecedor <br>";
				//print_r($orc_anexo);
				///echo "<br>";
				//exit;
				
				if(in_array($fornecedor,$orc_fornecedor))
				{
					//PEGA KEY
					reset($orc_fornecedor);
					while (($vetor_fornecedor = current($orc_fornecedor)) !== FALSE ) 
					{
						if ($vetor_fornecedor == $fornecedor) {
							$k = key($orc_fornecedor);
							end($orc_fornecedor);
						}
						
						next($orc_fornecedor);
					}
					//echo "Key: $k <br>";
					
					//PEGA ANEXO
					if($orc_anexo[$k] != '')
					{
						$extensao = pathinfo($orc_anexo[$k], PATHINFO_EXTENSION);
						$arquivo = $caminho;
						$arquivo .= md5(mt_rand(1,10000).$orc_anexo[$k]).'.'.$extensao;
						move_uploaded_file($tmp_anexo[$k], ($arquivo));
						unlink($anexo);
					}
					else
					{
						$sql_anexo = "SELECT * FROM orcamento_fornecedor
									  INNER JOIN orcamento_gerenciar  ON orcamento_gerenciar.orc_id = orcamento_fornecedor.orf_orcamento
									  WHERE orf_fornecedor = ".$orc_fornecedor[$k]." AND orf_orcamento = $orc_id ";
						$query_anexo = mysql_query($sql_anexo,$conexao);
						$rows_anexo = mysql_num_rows($query_anexo);
						if($rows_anexo > 0)
						{
							$arquivo = mysql_result($query_anexo, 0, 'orf_anexo');
						}
					}
					//UPDATE
					$sql_update = "UPDATE orcamento_fornecedor SET 
								   orf_valor = '".str_replace(",",".",str_replace(".","",$orc_valor[$k]))."',
								   orf_obs = '".$orc_obs[$k]."',
								   orf_anexo = '".$arquivo."' 
								   WHERE orf_orcamento = $orc_id AND orf_fornecedor = $fornecedor
								   ";
					   
					if(mysql_query($sql_update))
					{
						//echo "update<br>";
					}
					else
					{
						$erro=1;
					}
					
				}
				else
				{
					//DELETE
					$sql_delete = "DELETE FROM orcamento_fornecedor WHERE orf_orcamento = $orc_id AND orf_fornecedor = $fornecedor ";
					if(mysql_query($sql_delete))
					{
						//echo "delete<br>";
						unlink($anexo);
					}
					else
					{
						$erro=1;
					}
				}
			}
			$sql_f = "SELECT orf_fornecedor FROM orcamento_fornecedor
					  WHERE orf_orcamento = $orc_id ";
			$query_f = mysql_query($sql_f,$conexao);
			$array_banco = array();
			while($row_f = mysql_fetch_array($query_f))
			{
				$array_banco[] = $row_f['orf_fornecedor'];
			}
			$diferenca = array_diff($orc_fornecedor, $array_banco);
			//$k = key($diferenca);
			foreach($diferenca as $k => $value)
			{
				if($orc_anexo[$k] != '')
				{
					$extensao = pathinfo($orc_anexo[$k], PATHINFO_EXTENSION);
					$arquivo = $caminho;
					$arquivo .= md5(mt_rand(1,10000).$orc_anexo[$k]).'.'.$extensao;
					move_uploaded_file($tmp_anexo[$k], ($arquivo));
				}
				$sql_insere_item = "INSERT INTO orcamento_fornecedor (orf_orcamento, orf_fornecedor, orf_valor, orf_obs, orf_anexo) VALUES ($ultimo_id, ".$orc_fornecedor[$k].",'".str_replace(",",".",str_replace(".","",$orc_valor[$k]))."', '".$orc_obs[$k]."','".$arquivo."') ";
				if(mysql_query($sql_insere_item))
				{
					//echo "insert<br>";
				}
				else
				{
					$erro=1;
				}
			}
		}
		else
		{
			$sql_f = "SELECT orf_fornecedor FROM orcamento_fornecedor
					  WHERE orf_orcamento = $orc_id ";
			$query_f = mysql_query($sql_f,$conexao);
			$array_banco = array();
			while($row_f = mysql_fetch_array($query_f))
			{
				$array_banco[] = $row_f['orf_fornecedor'];
			}
			$diferenca = array_diff($orc_fornecedor, $array_banco);
			//$k = key($diferenca);
			foreach($diferenca as $k => $value)
			{
				if($orc_anexo[$k] != '')
				{
					$extensao = pathinfo($orc_anexo[$k], PATHINFO_EXTENSION);
					$arquivo = $caminho;
					$arquivo .= md5(mt_rand(1,10000).$orc_anexo[$k]).'.'.$extensao;
					move_uploaded_file($tmp_anexo[$k], ($arquivo));
					//unlink($anexo);
					
					$sql_insere_item = "INSERT INTO orcamento_fornecedor (orf_orcamento, orf_fornecedor, orf_valor, orf_obs, orf_anexo) VALUES ($ultimo_id, ".$orc_fornecedor[$k].",'".str_replace(",",".",str_replace(".","",$orc_valor[$k]))."', '".$orc_obs[$k]."','".$arquivo."') ";
		
					if(mysql_query($sql_insere_item))
					{
						//echo "insert<br>";
					}
					else
					{
						$erro=1;
					}
				}
			}
		}
		
		$orc_planilha = $_FILES['orc_planilha']["name"];
		$tmp_planilha = $_FILES['orc_planilha']["tmp_name"];
		$caminho = "../admin/planilha/$ultimo_id/";
		if(!file_exists($caminho))
		{
			 mkdir($caminho, 0755, true); 
		}
		$sql_orcamento = "SELECT * FROM orcamento_gerenciar
						  WHERE orc_id = $orc_id ";
		$query_orcamento = mysql_query($sql_orcamento,$conexao);
		$query_f = mysql_query($sql_orcamento,$conexao);
		$rows_orcamento = mysql_num_rows($query_orcamento);
		if($rows_orcamento > 0)
		{
			$planilha = mysql_result($query_orcamento, 0, 'orc_planilha');
		}
		foreach($orc_planilha as $k => $value)
		{
			if($orc_planilha[$k] != '')
			{
				$extensao = pathinfo($orc_planilha[$k], PATHINFO_EXTENSION);
				$arquivo = $caminho;
				$arquivo .= md5(mt_rand(1,10000).$orc_planilha[$k]).'.'.$extensao;
				move_uploaded_file($tmp_planilha[$k], ($arquivo));
				unlink($planilha);
				$sql_update = "UPDATE orcamento_gerenciar SET 
							   orc_planilha = '".$arquivo."' 
							   WHERE orc_id = $orc_id 
							   ";							   
				if(mysql_query($sql_update))
				{
					//echo "update<br>";
				}
				else
				{$erro=1;}
			}
		}
		
		if($erro != 1)
		{
			if($status_antigo != 2 && $orc_status == 2)
			{
				include("../mail/envia_email_orcamento_calculado.php");
			}
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
				'<img src=../imagens/x.png> Erro ao alterar dados, por favor tente novamente.<br><br>'+
				'<input value=\' Ok \' type=\'button\' onclick=javascript:window.history.back();>');
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
	$orc_id = $_GET['orc_id'];
	$sql = "DELETE FROM orcamento_gerenciar WHERE orc_id = '$orc_id'";
				
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
if($action == 'excluir_anexo')
{
	$orc_id = $_GET['orc_id'];
	$sql = "SELECT * FROM orcamento_gerenciar WHERE orc_id = '$orc_id'";
	$query = mysql_query($sql,$conexao);
	$rows = mysql_num_rows($query);
	if($rows > 0)
	{
		$orc_planilha = mysql_result($query, 0, 'orc_planilha');
	}
	
	$sql = "UPDATE orcamento_gerenciar SET orc_planilha = NULL WHERE orc_id = '$orc_id'";
				
	if(mysql_query($sql,$conexao))
	{
		unlink($orc_planilha);
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
	$orc_id = $_GET['orc_id'];
	$sql = "UPDATE orcamento_gerenciar SET orc_status = 1 WHERE orc_id = '$orc_id'";
				
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
	$orc_id = $_GET['orc_id'];
	$sql = "UPDATE orcamento_gerenciar SET orc_status = 0 WHERE orc_id = '$orc_id'";
				
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

$num_por_pagina = 10;
if(!$pag){$primeiro_registro = 0; $pag = 1;}
else{$primeiro_registro = ($pag - 1) * $num_por_pagina;}
$fil_orc = $_REQUEST['fil_orc'];
if($fil_orc == '')
{
	$orc_query = " 1 = 1 ";
}
else
{
	$orc_query = " (orc_id LIKE '%".$fil_orc."%') ";
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
$fil_tipo_servico = $_REQUEST['fil_tipo_servico'];
if($fil_tipo_servico == '')
{
	$tipo_servico_query = " 1 = 1 ";
	$fil_tipo_servico_n = "Tipo de Serviço Prestado";
}
else
{
	$tipo_servico_query = " orc_tipo_servico = '".$fil_tipo_servico."' ";
	$sql_tipos_servicos = "SELECT * FROM cadastro_tipos_servicos WHERE tps_id = $fil_tipo_servico ";
	$query_tipos_servicos = mysql_query($sql_tipos_servicos,$conexao);
	$fil_tipo_servico_n = mysql_result($query_tipos_servicos,0,'tps_nome');
}
$fil_data_inicio = implode('-',array_reverse(explode('/',$_REQUEST['fil_data_inicio'])));
$fil_data_fim = implode('-',array_reverse(explode('/',$_REQUEST['fil_data_fim'])));
if($fil_data_inicio == '' && $fil_data_fim == '')
{
	$data_query = " 1 = 1 ";
}
elseif($fil_data_inicio != '' && $fil_data_fim == '')
{
	$data_query = " orc_data_cadastro >= '$fil_data_inicio' ";
}
elseif($fil_data_inicio == '' && $fil_data_fim != '')
{
	$data_query = " orc_data_cadastro <= '$fil_data_fim 23:59:59' ";
}
elseif($fil_data_inicio != '' && $fil_data_fim != '')
{
	$data_query = " orc_data_cadastro BETWEEN '$fil_data_inicio' AND '$fil_data_fim 23:59:59' ";
}
$fil_status = $_REQUEST['fil_status'];
if($fil_status == '')
{
	$status_query = " 1 = 1 ";
	$fil_status_n = "Status";
}
else
{
	$status_query = " (sto_status = '".$fil_status."') ";
	switch($fil_status)
	{
		case 1: $fil_status_n = "<span class='laranja'>Pendente</span>";break;
		case 2: $fil_status_n = "<span class='azul'>Calculado</span>";break;
		case 3: $fil_status_n = "<span class='verde'>Aprovado</span>";break;
		case 4: $fil_status_n = "<span class='vermelho'>Reprovado</span>";break;
	}
}
$sql = "SELECT * FROM orcamento_gerenciar 
		LEFT JOIN ( cadastro_clientes 
			INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id )
		ON cadastro_clientes.cli_id = orcamento_gerenciar.orc_cliente
		LEFT JOIN cadastro_tipos_servicos ON cadastro_tipos_servicos.tps_id = orcamento_gerenciar.orc_tipo_servico
		LEFT JOIN (cadastro_status_orcamento h1 
			LEFT JOIN cadastro_fornecedores ON cadastro_fornecedores.for_id = h1.sto_fornecedor_aprovado)
		ON h1.sto_orcamento = orcamento_gerenciar.orc_id 
		WHERE h1.sto_id = (SELECT MAX(h2.sto_id) FROM cadastro_status_orcamento h2 where h2.sto_orcamento = h1.sto_orcamento) AND 
			  ucl_usuario = '".$_SESSION['usuario_id']."' AND
			  ".$orc_query." AND ".$nome_query." AND ".$tipo_servico_query." AND ".$data_query." AND ".$status_query."  
		ORDER BY orc_data_cadastro DESC
		LIMIT $primeiro_registro, $num_por_pagina ";
$cnt = "SELECT COUNT(*) FROM orcamento_gerenciar
		LEFT JOIN ( cadastro_clientes 
			INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id)
		ON cadastro_clientes.cli_id = orcamento_gerenciar.orc_cliente
		LEFT JOIN cadastro_tipos_servicos ON cadastro_tipos_servicos.tps_id = orcamento_gerenciar.orc_tipo_servico
		LEFT JOIN (cadastro_status_orcamento h1 
			LEFT JOIN cadastro_fornecedores ON cadastro_fornecedores.for_id = h1.sto_fornecedor_aprovado)
		ON h1.sto_orcamento = orcamento_gerenciar.orc_id 
		WHERE h1.sto_id = (SELECT MAX(h2.sto_id) FROM cadastro_status_orcamento h2 where h2.sto_orcamento = h1.sto_orcamento) AND
			  ucl_usuario = '".$_SESSION['usuario_id']."' AND
			  ".$orc_query." AND ".$nome_query." AND ".$tipo_servico_query." AND ".$data_query." AND ".$status_query."";

$query = mysql_query($sql,$conexao);
$rows = mysql_num_rows($query);
if($pagina == "orcamento_gerenciar")
{
	echo "
	<div class='centro'>
		<div class='titulo'> $page  </div>
		<div id='botoes'><input value='Novo Orçamento' type='button' onclick=javascript:window.location.href='orcamento_gerenciar.php?pagina=adicionar_orcamento_gerenciar".$autenticacao."'; /></div>
		<div class='filtro'>
			<form name='form_filtro' id='form_filtro' enctype='multipart/form-data' method='post' action='orcamento_gerenciar.php?pagina=orcamento_gerenciar".$autenticacao."'>
			<input name='fil_orc' id='fil_orc' value='$fil_orc' placeholder='N° Orçamento'>
			<input name='fil_nome' id='fil_nome' value='$fil_nome' placeholder='Cliente'>
			<select name='fil_tipo_servico' id='fil_tipo_servico'>
				<option value='$fil_tipo_servico'>$fil_tipo_servico_n</option>
				"; 
				$sql_tipo_servico = " SELECT * FROM cadastro_tipos_servicos ORDER BY tps_nome";
				$query_tipo_servico = mysql_query($sql_tipo_servico,$conexao);
				while($row_tipo_servico = mysql_fetch_array($query_tipo_servico) )
				{
					echo "<option value='".$row_tipo_servico['tps_id']."'>".$row_tipo_servico['tps_nome']."</option>";
				}
				echo "
				<option value=''>Todos</option>
			</select>
			<select name='fil_status' id='fil_status'>
				<option value='$fil_status'>$fil_status_n</option>
				<option value='1'>Pendente</option>
				<option value='2'>Calculado</option>
				<option value='3'>Aprovado</option>
				<option value='4'>Reprovado</option>
				<option value=''>Todos</option>
			</select>
			<input type='text' name='fil_data_inicio' id='fil_data_inicio' placeholder='Data Início' value='".implode('/',array_reverse(explode('-',$fil_data_inicio)))."' onkeypress='return mascaraData(this,event);'>
			<input type='text' name='fil_data_fim' id='fil_data_fim' placeholder='Data Fim' value='".implode('/',array_reverse(explode('-',$fil_data_fim)))."' onkeypress='return mascaraData(this,event);'>
			<input type='submit' value='Filtrar'> 
			</form>
		</div>
		";
		if ($rows > 0)
		{
			echo "
			<table align='center' width='100%' border='0' cellspacing='0' cellpadding='10' class='bordatabela'>
				<tr>
					<td class='titulo_tabela'>N° Orçamento</td>
					<td class='titulo_tabela'>Cliente</td>
					<td class='titulo_tabela'>Serviço</td>
					<td class='titulo_tabela' align='center'>Status</td>
					<td class='titulo_tabela' align='center'>Data Cadastro</td>
					<td class='titulo_tabela' align='center'>Fornecedor Aprovado</td>
					<td class='titulo_tabela' align='center'>Andamento</td>
					<td class='titulo_tabela' align='center'>Imprimir</td>
					<td class='titulo_tabela' align='center'>Gerenciar</td>
				</tr>";
				$c=0;
				for($x = 0; $x < $rows ; $x++)
				{
					$orc_andamento="";
					$orc_id = mysql_result($query, $x, 'orc_id');
					$cli_nome_razao = mysql_result($query, $x, 'cli_nome_razao');
					$tps_nome = mysql_result($query, $x, 'tps_nome');
					if($tps_nome == ''){$tps_nome = mysql_result($query, $x, 'orc_tipo_servico_cliente')."<br><span class='detalhe'>Digitado pelo cliente</span>";}
					$sto_status = mysql_result($query, $x, 'sto_status');
					$orc_data_cadastro = implode("/",array_reverse(explode("-",substr(mysql_result($query, $x, 'orc_data_cadastro'),0,10))));
					$orc_hora_cadastro = substr(mysql_result($query, $x, 'orc_data_cadastro'),11,5);
					
					$for_nome_razao = mysql_result($query, $x, 'for_nome_razao');
					$sto_observacao = mysql_result($query, $x, 'sto_observacao');
					$orc_andamento = mysql_result($query, $x, 'orc_andamento');
					
					switch($sto_status)
					{
						case 1: $sto_status_n = "<span class='laranja'>Pendente</span>";break;
						case 2: $sto_status_n = "<span class='azul'>Calculado</span>";break;
						case 3: $sto_status_n = "<span class='verde'>Aprovado</span>";break;
						case 4: $sto_status_n = "<span class='vermelho'>Reprovado</span>";break;
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
							$('#normal-button-$orc_id').toolbar({content: '#user-options-$orc_id', position: 'top', hideOnClick: true});
							$('#normal-button-bottom').toolbar({content: '#user-options', position: 'bottom'});
							$('#normal-button-small').toolbar({content: '#user-options-small', position: 'top', hideOnClick: true});
							$('#button-left').toolbar({content: '#user-options', position: 'left'});
							$('#button-right').toolbar({content: '#user-options', position: 'right'});
							$('#link-toolbar').toolbar({content: '#user-options', position: 'top' });
						});
					</script>
					<div id='user-options-$orc_id' class='toolbar-icons' style='display: none;'>
						<a href='orcamento_gerenciar.php?pagina=editar_orcamento_gerenciar&orc_id=$orc_id$autenticacao'><img border='0' src='../imagens/icon-editar.png'></a>
						<a onclick=\"
							abreMask(
								'Deseja realmente excluir o orçamento <b>$orc_nome_razao</b>?<br><br>'+
								'<input value=\' Sim \' type=\'button\' onclick=javascript:window.location.href=\'orcamento_gerenciar.php?pagina=orcamento_gerenciar&action=excluir&orc_id=$orc_id$autenticacao\';>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+
								'<input value=\' Não \' type=\'button\' class=\'close_janela\'>');
							\">
							<img border='0' src='../imagens/icon-excluir.png'></i>
						</a>
					</div>
					";
					echo "<tr class='$c1'>
							  <td>$orc_id</td>
							  <td>$cli_nome_razao</td>
							  <td>$tps_nome</td>
							  <td align=center>$sto_status_n</td>
							  <td align='center'>$orc_data_cadastro<br><span class='detalhe'>$orc_hora_cadastro</span></td>
							  <td>$for_nome_razao</td>
							  <td>";if($orc_andamento != ''){ echo "<img class='obs' ";if($orc_andamento != ''){ echo "onmouseover=\"toolTip('".preg_replace('/\r?\n|\r/','<br/>', $orc_andamento)."');\"";} echo " onmouseout=\"toolTip();\" src='../imagens/icon-obs.png' />";} echo "</td>
							  <td align='center'>
							  ";
							  //if($sto_status == 1)
							  //{}
							  //elseif($sto_status == 2 || $sto_status == 3 || $sto_status == 4)
							  //{
								  echo "<img class='mouse' src='../imagens/icon-pdf.png' onclick=javascript:window.open('orcamento_imprimir.php?orc_id=$orc_id$autenticacao');>";
							  //}
							  echo "</td>
							  <td align=center>
							  ";
							  if($orc_status == 2 && $_SESSION['setor'] != 1 && $_SESSION['setor'] != 2)
							  {
								  
							  }
							  else
							  {
							  	echo "<div id='normal-button-$orc_id' class='settings-button'><img src='../imagens/icon-cog-small.png' /></div>";
							  }
							  
							  echo "</td>
						  </tr>";
				}
				echo "</table>";
				$variavel = "&pagina=orcamento_gerenciar&fil_orc=$fil_orc&fil_nome=$fil_nome&fil_tipo_servico=$fil_tipo_servico&fil_data_inicio=$fil_data_inicio&fil_data_fim=$fil_data_fim&fil_status=$fil_status".$autenticacao."";
				include("../mod_includes/php/paginacao.php");
		}
		else
		{
			echo "<br><br><br>Não há nenhum orçamento cadastrado.";
		}
		echo "
		<div class='titulo'>  </div>				
	</div>";
}
if($pagina == 'adicionar_orcamento_gerenciar')
{
	echo "	
	<form name='form_orcamento_gerenciar' id='form_orcamento_gerenciar' enctype='multipart/form-data' method='post' action='orcamento_gerenciar.php?pagina=orcamento_gerenciar&action=adicionar$autenticacao'>
	<div class='centro'>
		<div class='titulo'> $page &raquo; Adicionar  </div>
		<table align='center' cellspacing='0' width='950'>
			<tr>
				<td align='left'>
					<div class='formtitulo'>Selecione o cliente que deseja abrir o orçamento</div>
					<div class='suggestion'>
						<input name='orc_cliente_id' id='orc_cliente_id'  type='hidden' value='' />
						<input name='orc_cliente' id='orc_cliente' type='text' placeholder='Digite as iniciais ou CNPJ do cliente para procurar' autocomplete='off' />
						<div class='suggestionsBox' id='suggestions' style='display: none;'>
							<div class='suggestionList' id='autoSuggestionsList'>
								&nbsp;
							</div>
						</div>
					</div>
					<p>
					<br><br>
					<select name='orc_tipo_servico' id='orc_tipo_servico' onchange='carregaFornecedor(this.value);'>
						<option value=''>Selecione o tipo de serviço</option>
						"; 
						$sql = "SELECT * FROM cadastro_tipos_servicos ORDER BY tps_nome";
						$query = mysql_query($sql,$conexao);
						while($row = mysql_fetch_array($query) )
						{
							echo "<option value='".$row['tps_id']."'>".$row['tps_nome']."</option>";
						} 
						echo "
					</select>
					<p>
					<div class='quadro'>
					<div class='formtitulo'>Adicione os fornecedores e seus respectivos orçamentos</div>
					<div id='p_scents'>
						<p>
							<label for='itens'>
								<select name='orc_fornecedor[]' id='orc_fornecedor'>
									<option value=''>Fornecedor</option>
								</select>
								<input type='text' id='orc_valor' size='12' name='orc_valor[]' value='' placeholder='Valor (em R$)' onkeypress='return MascaraMoeda(this,\".\",\",\",event);' />
								<input type='text' id='orc_obs' name='orc_obs[]' value='' placeholder='Observação' />
								<input name='orc_anexo[]' id='orc_anexo' type='file' onchange='verificaExtensao(this);'> &nbsp;
								<input type='button' id='addScnt' value='Adicionar'>
							</label>
						</p>
					</div>
					</div>
					<p>
					Planilha Comparativa: <input name='orc_planilha[]' id='orc_planilha' type='file' onchange='verificaExtensao(this);'>
					<p>
					<textarea name='orc_andamento' id='orc_andamento' placeholder='Andamento'></textarea>
					<p>
					<textarea name='orc_observacoes' id='orc_observacoes' placeholder='Observações'></textarea>
					<p>
					<div class='formtitulo'>Status do Orçamento</div>
					<input type='radio' name='orc_status' value='1' checked> Pendente &nbsp;&nbsp;&nbsp;
					<input type='radio' name='orc_status' id='calculado' value='2'> Calculado &nbsp;&nbsp;&nbsp;
					<input type='radio' name='orc_status' value='3'> Aprovado &nbsp;&nbsp;&nbsp;
					<input type='radio' name='orc_status' value='4'> Reprovado<br>
					<p>
					<center>
					<div id='erro' align='center'>&nbsp;</div>
					<input type='button' id='bt_orcamento_gerenciar' value='Salvar' />&nbsp;&nbsp;&nbsp;&nbsp; 
					<input type='button' id='botao_cancelar' onclick=javascript:window.location.href='orcamento_gerenciar.php?pagina=orcamento_gerenciar".$autenticacao."'; value='Cancelar'/></center>
					</center>
				</td>
			</tr>
		</table>
		<div class='titulo'> </div>
	</div>
	</form>
	";
}

if($pagina == 'editar_orcamento_gerenciar')
{
	$orc_id = $_GET['orc_id'];
	$sqledit = "SELECT * FROM orcamento_gerenciar 
				LEFT JOIN ( cadastro_clientes 
					INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id )
				ON cadastro_clientes.cli_id = orcamento_gerenciar.orc_cliente
				LEFT JOIN cadastro_tipos_servicos ON cadastro_tipos_servicos.tps_id = orcamento_gerenciar.orc_tipo_servico
				LEFT JOIN cadastro_status_orcamento h1 ON h1.sto_orcamento = orcamento_gerenciar.orc_id 
				WHERE h1.sto_id = (SELECT MAX(h2.sto_id) FROM cadastro_status_orcamento h2 where h2.sto_orcamento = h1.sto_orcamento) AND 
					  ucl_usuario = '".$_SESSION['usuario_id']."' AND 
					  orc_id = '$orc_id'";
	$queryedit = mysql_query($sqledit,$conexao);
	$rowsedit = mysql_num_rows($queryedit);

	if($rowsedit > 0)
	{
		$orc_cliente = mysql_result($queryedit, 0, 'orc_cliente');
		$cli_nome_razao = mysql_result($queryedit, 0, 'cli_nome_razao');
		$cli_cnpj = mysql_result($queryedit, 0, 'cli_cnpj');
		$orc_tipo_servico = mysql_result($queryedit, 0, 'orc_tipo_servico');
		$orc_tipo_servico_cliente = mysql_result($queryedit, 0, 'orc_tipo_servico_cliente');
		$tps_nome = mysql_result($queryedit, 0, 'tps_nome');
		if($tps_nome == ''){$tps_nome = "Tipo de serviço";}
		$orc_planilha = mysql_result($queryedit, 0, 'orc_planilha');
		$orc_andamento = mysql_result($queryedit, 0, 'orc_andamento');
		$orc_observacoes = mysql_result($queryedit, 0, 'orc_observacoes');
		$sto_status = mysql_result($queryedit, 0, 'sto_status');
		echo "
		<form name='form_orcamento_gerenciar' id='form_orcamento_gerenciar' enctype='multipart/form-data' method='post' action='orcamento_gerenciar.php?pagina=orcamento_gerenciar&action=editar&orc_id=$orc_id$autenticacao'>
		<div class='centro'>
			<div class='titulo'> $page &raquo; Editar: $orc_nome </div>
			<table align='center' cellspacing='0'>
				<tr>
					<td align='left'>
						<input type='hidden' name='orc_id' id='orc_id' value='$orc_id' placeholder='ID'>
						<div class='formtitulo'>Selecione o cliente que deseja abrir o orçamento</div>
						<div class='suggestion'>
							<input name='orc_cliente_id' id='orc_cliente_id'  type='hidden' value='$orc_cliente' />
							<input name='orc_cliente_block' id='orc_cliente_block' value='$cli_nome_razao ($cli_cnpj)' type='text' placeholder='Digite as iniciais ou CNPJ do cliente para procurar' autocomplete='off' readonly />
							<div class='suggestionsBox' id='suggestions' style='display: none;'>
								<div class='suggestionList' id='autoSuggestionsList'>
									&nbsp;
								</div>
							</div>
						</div>
						<p>
						<br><br>
						
						<select name='orc_tipo_servico' id='orc_tipo_servico' onchange='carregaFornecedor(this.value);'>
							<option value='$orc_tipo_servico'>$tps_nome</option>
							"; 
							$sql = "SELECT * FROM cadastro_tipos_servicos ORDER BY tps_nome";
							$query = mysql_query($sql,$conexao);
							while($row = mysql_fetch_array($query) )
							{
								echo "<option value='".$row['tps_id']."'>".$row['tps_nome']."</option>";
							} 
							echo "
						</select> &nbsp;
						";
						if($orc_tipo_servico_cliente != '')
						{
							echo $orc_tipo_servico_cliente." (digitado pelo cliente)<br>";
						}
						echo "
						<p>
						<div class='quadro'>
						<div class='formtitulo'>Adicione os fornecedores e seus respectivos orçamentos</div>
						<div id='p_scents'>
							";
							$sql_fornecedores = "SELECT * FROM orcamento_fornecedor 
												 LEFT JOIN cadastro_fornecedores ON cadastro_fornecedores.for_id = orcamento_fornecedor.orf_fornecedor
												 WHERE orf_orcamento = $orc_id ORDER BY orf_id ASC";
							$query_fornecedores = mysql_query($sql_fornecedores,$conexao);
							$rows_fornecedores = mysql_num_rows($query_fornecedores);
							if($rows_fornecedores > 0)
							{
								while($row_fornecedores = mysql_fetch_array($query_fornecedores))
								{
									echo "
									<p>
										<label for='itens'>
											<select name='orc_fornecedor[]' id='orc_fornecedor'>
												<option value='".$row_fornecedores['orf_fornecedor']."'>".$row_fornecedores['for_nome_razao']."</option>
											</select>
											<input type='text' id='orc_valor' size='12' name='orc_valor[]' value='".number_format($row_fornecedores['orf_valor'],2,',','.')."' placeholder='Valor (em R$)' onkeypress='return MascaraMoeda(this,\".\",\",\",event);' />
											<input type='text' id='orc_obs' name='orc_obs[]' value='".$row_fornecedores['orf_obs']."' placeholder='Observação' />
											<input name='orc_anexo[]' id='orc_anexo' type='file' onchange='verificaExtensao(this);'> &nbsp;
											<input type='button' id='addScnt' value='Adicionar'> &nbsp; <input type='button' id='remScnt' value='X'> &nbsp;&nbsp;
											";
											if($row_fornecedores['orf_anexo'] != '')
											{
												echo "Anexo atual: <a href='".$row_fornecedores['orf_anexo']."' target='_blank'><img src='../imagens/pdf.png' valign='middle'></a>";
											}
											echo "
										</label>
									</p>
									";
								}
							}
							else
							{
								echo "
								<p>
									<label for='itens'>
										<select name='orc_fornecedor[]' id='orc_fornecedor'>
											<option value=''>Fornecedor</option>
											"; 
											$sql_f = "SELECT * FROM cadastro_fornecedores_servicos 
													INNER JOIN cadastro_fornecedores ON cadastro_fornecedores.for_id = cadastro_fornecedores_servicos.fse_fornecedor
													INNER JOIN cadastro_tipos_servicos ON cadastro_tipos_servicos.tps_id = cadastro_fornecedores_servicos.fse_servico
													WHERE fse_servico = $orc_tipo_servico
													GROUP BY for_id
													ORDER BY for_nome_razao ASC ";
											$query_f = mysql_query($sql_f,$conexao);
											while($row_f = mysql_fetch_array($query_f) )
											{
												echo "<option value='".$row_f['for_id']."'>".$row_f['for_nome_razao']."</option>";
											} 
											echo "
										</select>
										<input type='text' id='orc_valor' size='12' name='orc_valor[]' value='' placeholder='Valor (em R$)' onkeypress='return MascaraMoeda(this,\".\",\",\",event);' />
										<input type='text' id='orc_obs' name='orc_obs[]' value='' placeholder='Observação' />
										<input name='orc_anexo[]' id='orc_anexo' type='file' onchange='verificaExtensao(this);'> &nbsp;
										<input type='button' id='addScnt' value='Adicionar'> &nbsp; <input type='button' id='remScnt' value='X'> &nbsp;&nbsp;
										";
										if($row_fornecedores['orf_anexo'] != '')
										{
											echo "Anexo atual: <a href='".$row_fornecedores['orf_anexo']."' target='_blank'><img src='../imagens/pdf.png' valign='middle'></a>";
										}
										echo "
									</label>
								</p>
								";
							}
							echo "
						</div>
						</div>
						<p>
						Planilha Comparativa: <input name='orc_planilha[]' id='orc_planilha' type='file' onchange='verificaExtensao(this);'>
						";
						if($orc_planilha != '')
						{
							echo "Planilha atual: <a href='".$orc_planilha."' target='_blank'><img src='../imagens/pdf.png' valign='middle'></a>
							<p>
							<a onclick=\"
								abreMask(
									'Deseja realmente excluir este anexo?<br><br>'+
									'<input value=\' Sim \' type=\'button\' onclick=javascript:window.location.href=\'orcamento_gerenciar.php?pagina=editar_orcamento_gerenciar&action=excluir_anexo&orc_id=$orc_id$autenticacao\';>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+
									'<input value=\' Não \' type=\'button\' class=\'close_janela\'>');
								\">
								Excluir planilha <img border='0' src='../imagens/icon-excluir.png'></i>
							</a>";
						}
						echo "
						<p>
						<textarea name='orc_andamento' id='orc_andamento' placeholder='Andamento'>$orc_andamento</textarea>
						<p>
						<textarea name='orc_observacoes' id='orc_observacoes' placeholder='Observações'>$orc_observacoes</textarea>
						<p>
						<div class='formtitulo'>Status do Orçamento</div>
						<p>
							<input type='radio' name='orc_status' value='1' "; if($sto_status == 1){ echo "checked"; } echo "> Pendente &nbsp;&nbsp;&nbsp;
						  	<input type='radio' name='orc_status' id='calculado'  value='2' "; if($sto_status == 2){ echo "checked"; } echo "> Calculado &nbsp;&nbsp;&nbsp;
						  	<input type='radio' name='orc_status' value='3' "; if($sto_status == 3){ echo "checked"; } echo "> Aprovado &nbsp;&nbsp;&nbsp;
						  	<input type='radio' name='orc_status' value='4' "; if($sto_status == 4){ echo "checked"; } echo "> Reprovado<br>
						<p>
						<center>
						<div id='erro' align='center'>&nbsp;</div>
						<input type='button' id='bt_orcamento_gerenciar' value='Salvar' />&nbsp;&nbsp;&nbsp;&nbsp; 
						<input type='button' id='botao_cancelar' onclick=javascript:window.location.href='orcamento_gerenciar.php?pagina=orcamento_gerenciar$autenticacao'; value='Cancelar'/></center>
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