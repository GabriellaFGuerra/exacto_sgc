<?php
include('connect.php');

$busca = $_POST['busca'];
if($busca != "")
{
	$sqlprocura = " SELECT * FROM cadastro_fornecedores_servicos
					INNER JOIN cadastro_fornecedores ON cadastro_fornecedores.for_id = cadastro_fornecedores_servicos.fse_fornecedor
					INNER JOIN cadastro_tipos_servicos ON cadastro_tipos_servicos.tps_id = cadastro_fornecedores_servicos.fse_servico
					WHERE fse_servico = '$busca'
					GROUP BY for_id
					ORDER BY for_nome_razao ASC";
}
$queryprocura = mysql_query($sqlprocura, $conexao);
$rowsprocura = mysql_num_rows($queryprocura);
if($rowsprocura>0)
{
	echo "<option value=''>Fornecedor</option>";
	while($rowsprocura = mysql_fetch_array($queryprocura) )
	{
		echo "<option value='".$rowsprocura['for_id']."'>".$rowsprocura['for_nome_razao']."</option>";
	}
	
}
else
{
	echo "<option value=''>Nenhum fornecedor cadastrado para este tipo de serviço.</option>";
}
?>