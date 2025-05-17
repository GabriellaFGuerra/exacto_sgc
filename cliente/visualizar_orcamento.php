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
<script type="text/javascript" src="../mod_includes/js/funcoes.js"></script>
<script type="text/javascript" src="../mod_includes/js/jquery-1.8.3.min.js"></script>
<!--   TimeLine   -->
<link rel="stylesheet" href="../mod_includes/js/timeline/style-timeline.css"> <!-- Resource style -->
<script src="../mod_includes/js/timeline/modernizr.js"></script> <!-- Modernizr -->
<script src="../mod_includes/js/timeline/main.js"></script> <!-- Modernizr -->
<!--   TimeLine   -->
</head>
<body>
<?php	
include		('../mod_includes/php/funcoes-jquery.php');
require_once('../mod_includes/php/verificalogincliente.php');
include		("../mod_topo_cliente/topo.php");
?>

<?php
$orc_id = $_GET['orc_id'];
$sql = "SELECT * FROM cadastro_orcamentos
		LEFT JOIN cadastro_equipamentos ON cadastro_equipamentos.equ_id = cadastro_orcamentos.orc_equipamento
		LEFT JOIN cadastro_tecnicos ON cadastro_tecnicos.tec_id = cadastro_orcamentos.orc_tecnico
		LEFT JOIN (cadastro_unidades 
			LEFT JOIN cadastro_clientes
			ON cadastro_clientes.cli_id = cadastro_unidades.uni_cliente )
		ON cadastro_unidades.uni_id = cadastro_orcamentos.orc_unidade
		LEFT JOIN cadastro_status_orcamento h1 ON h1.sto_orcamento = cadastro_orcamentos.orc_id 
		WHERE h1.sto_id = (SELECT MAX(h2.sto_id) FROM cadastro_status_orcamento h2 where h2.sto_orcamento = h1.sto_orcamento) AND
			  cli_id = ".$_SESSION['cliente_id']." AND orc_id = $orc_id
		GROUP BY orc_id
		";

$query = mysql_query($sql,$conexao);
$rows = mysql_num_rows($query);
if($pagina == 'visualizar_orcamento')
{
	if ($rows > 0)
	{
		$orc_id = mysql_result($query, 0, 'orc_id');
		$orc_ano = mysql_result($query, 0, 'orc_ano');
		$uni_nome_razao = mysql_result($query, 0, 'uni_nome_razao');
		$orc_equipamento = mysql_result($query, 0, 'orc_equipamento');
		$orc_avul_tipo = mysql_result($query, 0, 'orc_avul_tipo');
		$orc_avul_marca = mysql_result($query, 0, 'orc_avul_marca');
		$orc_avul_modelo = mysql_result($query, 0, 'orc_avul_modelo');
		$orc_avul_num_serie = mysql_result($query, 0, 'orc_avul_num_serie');
		$equ_tipo = mysql_result($query, 0, 'equ_tipo');
		$equ_marca = mysql_result($query, 0, 'equ_marca');
		$equ_modelo = mysql_result($query, 0, 'equ_modelo');
		$equ_num_serie = mysql_result($query, 0, 'equ_num_serie');
		$equ_num_pat = mysql_result($query, 0, 'equ_num_pat');
		$equ_nosso_num = mysql_result($query, 0, 'equ_nosso_num');
		$orc_verif_disjuntor = mysql_result($query, 0, 'orc_verif_disjuntor');
		$orc_verif_agua = mysql_result($query, 0, 'orc_verif_agua');
		$orc_verif_ar = mysql_result($query, 0, 'orc_verif_ar');
		$orc_responsavel = mysql_result($query, 0, 'orc_responsavel');
		$orc_telefone = mysql_result($query, 0, 'orc_telefone');
		$orc_descricao = mysql_result($query, 0, 'orc_descricao');
		$sto_status = mysql_result($query, 0, 'sto_status');
		$tec_nome = mysql_result($query, 0, 'tec_nome'); if($tec_nome == ''){$tec_nome = "Nenhum técnico foi atrabuído ainda a este orcamento.";}
		$orc_data_cadastro = implode("/",array_reverse(explode("-",substr(mysql_result($query, 0, 'orc_data'),0,10))));
		$orc_hora_cadastro = substr(mysql_result($query, 0, 'orc_data'),11,5);
		if($orc_equipamento == '' && ($orc_avul_tipo != '' || $orc_avul_marca != '' || $orc_avul_modelo != '' || $orc_avul_num_serie != ''))
		{
			$avulso = "Sim";
			$equ_tipo = $orc_avul_tipo;
			$equ_marca = $orc_avul_marca;
			$equ_modelo = $orc_avul_modelo;
			$equ_num_serie = $orc_avul_num_serie;
			
		}
		else
		{
			$avulso = "Não";
		}				
		switch($sto_status)
		{
			case 1: $sto_status_n = "<span class='preto'>Em análise</span>";break;
			case 2: $sto_status_n = "<span class='azul'>Aberto</span>";break;
			case 3: $sto_status_n = "<span class='laranja'>Pendente</span>";break;
			case 4: $sto_status_n = "<span class='verde'>Finalizado</span>";break;
			case 5: $sto_status_n = "<span class='vermelho'>Cancelado</span>";break;
		}
		echo "
		<div class='centro'>
			<div class='titulo'> Visualizar Orçamento </div>
			<div class='quadro'>
			<div style='width:90%; margin:0 auto; line-height:25px;'>
			<div class='formtitulo'>Dados do Orçamento</div>			
			<b>Nº Protocolo:</b> $orc_ano$orc_id <br>
			<b>Orçamento avulso?</b> $avulso <br>
			<b>Situação atual:</b> $sto_status_n <br>
			<b>Data de abertura:</b> $orc_data_cadastro às $orc_hora_cadastro <p>
			<b>Unidade Solicitante:</b> $uni_nome_razao <br>
			<b>Equipamento:</b>
			<ul>
				<li><b>Tipo:</b> $equ_tipo </li>
				<li><b>Marca:</b> $equ_marca </li>
				<li><b>Modelo:</b> $equ_modelo </li>
				<li><b>Nº Série:</b> $equ_num_serie </li>
				<li><b>Nº Patrimônio:</b> $equ_num_pat </li>
				<li><b>Nosso Nº:</b> $equ_nosso_num </li>
			</ul>
			<b>Itens verificados:</b>
			<ul>
				<li><b>Disjuntor:</b> $orc_verif_disjuntor </li>
				<li><b>Registro de Água:</b> $orc_verif_agua </li>
				<li><b>Registro de Ar:</b> $orc_verif_ar </li>
			</ul>
			<b>Responsável:</b> $orc_responsavel <br>
			<b>Telefone:</b> $orc_telefone <br>
			<b>Descrição do orcamento/problema:</b> <br> 
			".nl2br($orc_descricao)." <p>
			<br>
			<b>Técnico Responsável:</b> $tec_nome <br>
			</div>
			</div>
			<br>
			
			<div style='width:90%; margin:0 auto; line-height:25px;'>
			<div class='formtitulo'>Histórico do Orçamento</div>			
			";
			$sql = "SELECT * FROM cadastro_orcamentos 
					LEFT JOIN cadastro_equipamentos ON cadastro_equipamentos.equ_id = cadastro_orcamentos.orc_equipamento
					LEFT JOIN cadastro_status_orcamento ON cadastro_status_orcamento.sto_orcamento = cadastro_orcamentos.orc_id
					LEFT JOIN (cadastro_unidades 
						LEFT JOIN cadastro_clientes
						ON cadastro_clientes.cli_id = cadastro_unidades.uni_cliente )
					ON cadastro_unidades.uni_id = cadastro_orcamentos.orc_unidade
					WHERE orc_id = $orc_id
					GROUP BY sto_id
					ORDER BY sto_data ASC
					";
			$query = mysql_query($sql,$conexao);
			$rows = mysql_num_rows($query);
			if ($rows > 0)
			{
				$c=0;
				echo "<section id='cd-timeline' class='cd-container'>";
				for($x = 0; $x < $rows ; $x++)
				{
					$orc_id = str_pad(mysql_result($query, $x, 'orc_id'),6,"0", STR_PAD_LEFT);
					$sto_data = implode("/",array_reverse(explode("-",substr(mysql_result($query, $x, 'sto_data'),0,10))));
					$sto_hora = substr(mysql_result($query, $x, 'sto_data'),11,5);
					
					$sto_status = mysql_result($query, $x, 'sto_status');
					switch($sto_status)
					{
						case 1: $sto_status_n = "<span class='preto'>Em análise</span>";break;
						case 2: $sto_status_n = "<span class='azul'>Aberto</span>";break;
						case 3: $sto_status_n = "<span class='laranja'>Pendente</span>";break;
						case 4: $sto_status_n = "<span class='verde'>Finalizado</span>";break;
						case 5: $sto_status_n = "<span class='vermelho'>Cancelado";break;
					}
					$sto_observacao = mysql_result($query, $x, 'sto_observacao');
					
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
					<div class='cd-timeline-block'>
						<div class='cd-timeline-img cd-location'>
							<img src='../imagens/cd-icon-location.svg' alt='Location'>
						</div> <!-- cd-timeline-img -->
			
						<div class='cd-timeline-content'>
							<h2></h2>
							<p><b>Status:</b> ".$sto_status_n."
							<p><b>Observações:</b> ".$sto_observacao."
							<span class='cd-date'>".$sto_data."<br>às ".$sto_hora."</span>
						</div> <!-- cd-timeline-content -->
					</div> <!-- cd-timeline-block -->
					
					";
				}
				echo "</section>";

			}
			else
			{
				echo "<br><br><br>Nenhum histórico encontrado.";
			}
			echo "
			</div>
			<div class='titulo'>  </div>				
		</div>";
	}
	else
	{
		echo "<div class='centro'><br><br><br>Nenhum orcamento encontrado.</div>";
	}
		
}


include('../mod_rodape/rodape.php');
?>
