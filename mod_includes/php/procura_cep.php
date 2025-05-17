<?php
include('connect.php');
$cep = $_POST['cep'];
$up = $_POST['up'];
$sqlprocura = "SELECT * FROM end_enderecos
			   LEFT JOIN (end_bairros 
				   LEFT JOIN (end_municipios 
						LEFT JOIN end_uf
						ON end_uf.uf_id = end_municipios.mun_uf)
				   ON end_municipios.mun_id = end_bairros.bai_municipio)
			   ON end_bairros.bai_id = end_enderecos.end_bairro
			   WHERE end_cep = '$cep'";
$queryprocura = mysql_query($sqlprocura, $conexao);
$rowsprocura = mysql_num_rows($queryprocura);
if($up == 'uf')
{
	if($rowsprocura>0)
	{
		while($row = mysql_fetch_array($queryprocura) )
		{
			echo "<option value='".$row['uf_id']."' selected>".$row['uf_sigla']."</option>";
		}
	}
	else
	{
		echo "<option value=''>UF</option>";
		$sql = " SELECT * FROM end_uf ORDER BY uf_sigla";
		$query = mysql_query($sql,$conexao);
		while($row = mysql_fetch_array($query) )
		{
			echo "<option value='".$row['uf_id']."'>".$row['uf_sigla']."</option>";
		}
	}
}

if($up == 'municipio')
{
	if($rowsprocura>0)
	{
		while($row = mysql_fetch_array($queryprocura) )
		{
			echo "<option value='".$row['mun_id']."' selected>".$row['mun_nome']."</option>";
		}
	}
	else
	{
		echo "<option value=''>Municípios</option>";
	}
}

if($up == 'bairro')
{
	if($rowsprocura>0)
	{
		while($row = mysql_fetch_array($queryprocura) )
		{
			echo $row['bai_nome'];
		}
	}
	else
	{
		echo "";
	}
}

if($up == 'endereco')
{
	if($rowsprocura>0)
	{
		while($row = mysql_fetch_array($queryprocura) )
		{
			echo $row['end_endereco'];
		}
	}
	else
	{
		echo "";
	}
}
?>