<?php
session_start (); 
include('../mod_includes/php/connect.php');
?>
<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $titulo;?></title>
<meta name="author" content="MogiComp">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="shortcut icon" href="../imagens/favicon.png">
<?php include("../css/style.php"); ?>
<script type="text/javascript" src="../mod_includes/js/jquery-1.8.3.min.js"></script>
</head>
<body>
<?php	
include		('../mod_includes/php/funcoes-jquery.php');
require_once('../mod_includes/php/verificalogincliente.php');
include		("../mod_topo_cliente/topo.php");
?>

<?php
echo "
<div class='centro'>
	<div class='titulo'> Bem vindo ao SGO - Sistema de Gerenciamento de Orçamentos</div>
	<table with='100%'>
		<tr>
			<td align='justify' valign='top'>
				<div class='quadro_home'>
				<div class='formtitulo'>Últimos orçamentos realizados</div>
				";
				$sql = "SELECT * FROM orcamento_gerenciar 
						LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = orcamento_gerenciar.orc_cliente
						LEFT JOIN cadastro_tipos_servicos ON cadastro_tipos_servicos.tps_id = orcamento_gerenciar.orc_tipo_servico
						LEFT JOIN cadastro_status_orcamento h1 ON h1.sto_orcamento = orcamento_gerenciar.orc_id 
						WHERE h1.sto_id = (SELECT MAX(h2.sto_id) FROM cadastro_status_orcamento h2 where h2.sto_orcamento = h1.sto_orcamento) AND
							  orc_cliente = ".$_SESSION['cliente_id']."
						ORDER BY orc_data_cadastro DESC
						LIMIT 0, 10";
				$query = mysql_query($sql,$conexao);
				$rows = mysql_num_rows($query);
				if ($rows > 0)
				{
					echo "
					<table align='center' width='100%' border='0' cellspacing='0' cellpadding='5'  class='bordatabela'>
						<tr>
							<td class='titulo_tabela'>N. Orçamento</td>
							<td class='titulo_tabela' width='150'>Tipo de Serviço</td>
							<td class='titulo_tabela'>Observações</td>
							<td class='titulo_tabela' align='center'>Data de Abertura</td>
							<td class='titulo_tabela' align='center'>Status</td>
							<td class='titulo_tabela' align='center'>Visualizar</td>
						</tr>";
						$c=0;
						for($x = 0; $x < $rows ; $x++)
						{
							$orc_id = mysql_result($query, $x, 'orc_id');
							$tps_nome = mysql_result($query, $x, 'tps_nome');
							if($tps_nome == ''){$tps_nome = mysql_result($query, $x, 'orc_tipo_servico_cliente');}
							$orc_data_cadastro = implode("/",array_reverse(explode("-",substr(mysql_result($query, $x, 'orc_data_cadastro'),0,10))));
							$orc_hora_cadastro = substr(mysql_result($query, $x, 'orc_data_cadastro'),11,5);
							$orc_observacoes = mysql_result($query, $x, 'orc_observacoes');
							$sto_status = mysql_result($query, $x, 'sto_status');
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
							echo "<tr class='$c1'>
									  <td>$orc_id</td>
									  <td>$tps_nome</td>
									  <td>$orc_observacoes</td>
									  <td align='center'>$orc_data_cadastro<br><span class='detalhe'>$orc_hora_cadastro</span></td>
									  <td align='center'>$sto_status_n</td>
									  <td align='center'>
									  ";
									 // if($sto_status == 1)
									  //{}
									  //elseif($sto_status == 2 || $sto_status == 3 || $sto_status == 4)
									  //{
										  echo "<img class='mouse' src='../imagens/icon-pdf.png' onclick=javascript:window.open('orcamento_imprimir.php?orc_id=$orc_id$autenticacao');>";
									  //}
									  echo "</td>
								  </tr>";
						}
						echo "</table>";
						$variavel = "&pagina=processo".$autenticacao."";
						include("../mod_includes/php/paginacao.php");
				}
				else
				{
					echo "<br><br><br>Não há nenhum orçamento cadastrado.";
				}
				echo "
				</div>
				
			</td>
		</tr>
	</table>
	<div class='titulo'>  </div>
</div>";

include('../mod_rodape/rodape.php');
?>
