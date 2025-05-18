<?php
session_start (); 
$pagina_link = 'chamado_consultar';
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
<!--   TimeLine   -->
<link rel="stylesheet" href="../mod_includes/js/timeline/style-timeline.css"> <!-- Resource style -->
<script src="../mod_includes/js/timeline/modernizr.js"></script> <!-- Modernizr -->
<script src="../mod_includes/js/timeline/main.js"></script> <!-- Modernizr -->
<!--   TimeLine   -->

</head>
<body>
<?php	
include		('../mod_includes/php/funcoes-jquery.php');
require_once('../mod_includes/php/verificalogin.php');
include		("../mod_topo/topo.php");
require_once('../mod_includes/php/verificapermissao.php');

?>

<?php
$cha_id = $_GET['cha_id'];
if($action == "salvar_status")
{
	$sql_chamado = "SELECT * FROM cadastro_chamados 
					LEFT JOIN cadastro_equipamentos ON cadastro_equipamentos.equ_id = cadastro_chamados.cha_equipamento
					LEFT JOIN cadastro_tecnicos ON cadastro_tecnicos.tec_id = cadastro_chamados.cha_tecnico
					LEFT JOIN (cadastro_unidades 
						LEFT JOIN cadastro_clientes
						ON cadastro_clientes.cli_id = cadastro_unidades.uni_cliente )
					ON cadastro_unidades.uni_id = cadastro_chamados.cha_unidade
					LEFT JOIN cadastro_status_chamado h1 ON h1.stc_chamado = cadastro_chamados.cha_id 
					WHERE h1.stc_id = (SELECT MAX(h2.stc_id) FROM cadastro_status_chamado h2 where h2.stc_chamado = h1.stc_chamado) AND
						  cha_id = $cha_id
					GROUP BY cha_id";
	$query_chamado = mysql_query($sql_chamado,$conexao);
	$rows_chamado = mysql_num_rows($query_chamado);
	if($rows_chamado > 0 )
	{
		$cli_email = mysql_result($query_chamado,0,'cli_email');
		$cha_responsavel = mysql_result($query_chamado,0,'cha_responsavel');
		$protocolo = mysql_result($query_chamado,0,'cha_ano').mysql_result($query_chamado,0,'cha_id');
	}
	$stc_status = $_POST['stc_status'];
	$stc_observacao = $_POST['stc_observacao'];
	$sql = "INSERT INTO cadastro_status_chamado (
	stc_chamado,
	stc_status,
	stc_observacao
	) 
	VALUES 
	(
	'$cha_id',
	'$stc_status',
	'$stc_observacao'
	)";
	if(mysql_query($sql,$conexao))
	{	
		include("../mail/envia_email_status_chamado.php");			
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
if($action == "salvar_tecnico")
{
	$cha_tecnico = $_POST['cha_tecnico'];
	$sql = "UPDATE cadastro_chamados SET
			cha_tecnico = '$cha_tecnico'
			WHERE cha_id = $cha_id ";
	if(mysql_query($sql,$conexao))
	{		
		echo "
		<SCRIPT language='JavaScript'>
			abreMask(
			'<img src=../imagens/ok.png> Cadastro efetuado com sucesso.<br>'+
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
$sql = "SELECT * FROM cadastro_chamados
		LEFT JOIN cadastro_equipamentos ON cadastro_equipamentos.equ_id = cadastro_chamados.cha_equipamento
		LEFT JOIN cadastro_tecnicos ON cadastro_tecnicos.tec_id = cadastro_chamados.cha_tecnico
		LEFT JOIN (cadastro_unidades 
			LEFT JOIN cadastro_clientes
			ON cadastro_clientes.cli_id = cadastro_unidades.uni_cliente )
		ON cadastro_unidades.uni_id = cadastro_chamados.cha_unidade
		LEFT JOIN cadastro_status_chamado h1 ON h1.stc_chamado = cadastro_chamados.cha_id 
		WHERE h1.stc_id = (SELECT MAX(h2.stc_id) FROM cadastro_status_chamado h2 where h2.stc_chamado = h1.stc_chamado) AND
		      cha_id = $cha_id
		GROUP BY cha_id
		";

$query = mysql_query($sql,$conexao);
$rows = mysql_num_rows($query);
if($pagina == 'visualizar_chamado')
{
	if ($rows > 0)
	{
		$cha_id = mysql_result($query, 0, 'cha_id');
		$cha_ano = mysql_result($query, 0, 'cha_ano');
		$cli_id = mysql_result($query, 0, 'cli_id');
		$cli_nome_razao = mysql_result($query, 0, 'cli_nome_razao');
		$uni_id = mysql_result($query, 0, 'uni_id');
		$uni_nome_razao = mysql_result($query, 0, 'uni_nome_razao');
		$cha_equipamento = mysql_result($query, 0, 'cha_equipamento');
		$cha_avul_tipo = mysql_result($query, 0, 'cha_avul_tipo');
		$cha_avul_marca = mysql_result($query, 0, 'cha_avul_marca');
		$cha_avul_modelo = mysql_result($query, 0, 'cha_avul_modelo');
		$cha_avul_num_serie = mysql_result($query, 0, 'cha_avul_num_serie');
		$equ_tipo = mysql_result($query, 0, 'equ_tipo');
		$equ_marca = mysql_result($query, 0, 'equ_marca');
		$equ_modelo = mysql_result($query, 0, 'equ_modelo');
		$equ_num_serie = mysql_result($query, 0, 'equ_num_serie');
		$equ_num_pat = mysql_result($query, 0, 'equ_num_pat');
		$equ_nosso_num = mysql_result($query, 0, 'equ_nosso_num');
		$cha_verif_disjuntor = mysql_result($query, 0, 'cha_verif_disjuntor');
		$cha_verif_agua = mysql_result($query, 0, 'cha_verif_agua');
		$cha_verif_ar = mysql_result($query, 0, 'cha_verif_ar');
		$cha_responsavel = mysql_result($query, 0, 'cha_responsavel');
		$cha_telefone = mysql_result($query, 0, 'cha_telefone');
		$cha_descricao = mysql_result($query, 0, 'cha_descricao');
		$stc_status = mysql_result($query, 0, 'stc_status');
		$cha_tecnico = mysql_result($query, 0, 'cha_tecnico');
		$tec_nome = mysql_result($query, 0, 'tec_nome'); if($tec_nome == ''){$tec_nome = "Selecione o técnico responsável por este chamado";}
		$cha_data_cadastro = implode("/",array_reverse(explode("-",substr(mysql_result($query, 0, 'cha_data'),0,10))));
		$cha_hora_cadastro = substr(mysql_result($query, 0, 'cha_data'),11,5);
		if($cha_equipamento == '' && ($cha_avul_tipo != '' || $cha_avul_marca != '' || $cha_avul_modelo != '' || $cha_avul_num_serie != ''))
		{
			$avulso = "Sim";
			$equ_tipo = $cha_avul_tipo;
			$equ_marca = $cha_avul_marca;
			$equ_modelo = $cha_avul_modelo;
			$equ_num_serie = $cha_avul_num_serie;
			
		}
		else
		{
			$avulso = "Não";
		}		
		switch($stc_status)
		{
			case 1: $stc_status_n = "<span class='preto'>Em análise</span>";break;
			case 2: $stc_status_n = "<span class='azul'>Aberto</span>";break;
			case 3: $stc_status_n = "<span class='laranja'>Pendente</span>";break;
			case 4: $stc_status_n = "<span class='verde'>Finalizado</span>";break;
			case 5: $stc_status_n = "<span class='vermelho'>Cancelado</span>";break;
		}
		echo "
		<div class='centro'>
			<img src='../imagens/pdf.png' class='right mouse' onclick=\"javascript:window.open('imprimir_chamado.php?pagina=imprimir_chamado&cha_id=$cha_id&autenticacao');\">
			<div class='titulo'> Visualizar Chamado </div>
			<div class='quadro'>
			<div style='width:90%; margin:0 auto; line-height:25px;'>
			<div class='formtitulo'>Dados do Chamado</div>			
			<b>Cliente/Unidade:</b> <a href='cadastro_clientes.php?pagina=editar_cadastro_clientes&cli_id=$cli_id$autenticacao'><b>$cli_nome_razao</b></a> / <a href='cadastro_unidades.php?pagina=editar_cadastro_unidades&uni_id=$uni_id&cli_id=$cli_id$autenticacao'>$uni_nome_razao</a> <br>
			<b>Nº Protocolo:</b> $cha_ano$cha_id <br>
			<b>Chamado avulso?</b> $avulso <br>
			<b>Situação atual:</b> $stc_status_n <br>
			<b>Data de abertura:</b> $cha_data_cadastro às $cha_hora_cadastro <p>
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
				<li><b>Disjuntor:</b> $cha_verif_disjuntor </li>
				<li><b>Registro de Água:</b> $cha_verif_agua </li>
				<li><b>Registro de Ar:</b> $cha_verif_ar </li>
			</ul>
			<b>Responsável:</b> $cha_responsavel <br>
			<b>Telefone:</b> $cha_telefone <br>
			<b>Descrição do chamado/problema:</b> <br> 
			".nl2br($cha_descricao)." <p>
			</div>
			</div>
			<br>
			<div style='width:90%; margin:0 auto; line-height:25px;'>
			<div class='formtitulo'>Histórico do Chamado</div>			
			";
			$sql = "SELECT * FROM cadastro_chamados 
					LEFT JOIN cadastro_equipamentos ON cadastro_equipamentos.equ_id = cadastro_chamados.cha_equipamento
					LEFT JOIN cadastro_status_chamado ON cadastro_status_chamado.stc_chamado = cadastro_chamados.cha_id
					LEFT JOIN (cadastro_unidades 
						LEFT JOIN cadastro_clientes
						ON cadastro_clientes.cli_id = cadastro_unidades.uni_cliente )
					ON cadastro_unidades.uni_id = cadastro_chamados.cha_unidade
					WHERE cha_id = $cha_id
					GROUP BY stc_id
					ORDER BY stc_data ASC
					";
			$query = mysql_query($sql,$conexao);
			$rows = mysql_num_rows($query);
			if ($rows > 0)
			{
				$c=0;
				echo "<section id='cd-timeline' class='cd-container'>";
				for($x = 0; $x < $rows ; $x++)
				{
					$cha_id = str_pad(mysql_result($query, $x, 'cha_id'),6,"0", STR_PAD_LEFT);
					$stc_data = implode("/",array_reverse(explode("-",substr(mysql_result($query, $x, 'stc_data'),0,10))));
					$stc_hora = substr(mysql_result($query, $x, 'stc_data'),11,5);
					
					$stc_status = mysql_result($query, $x, 'stc_status');
					switch($stc_status)
					{
						case 1: $stc_status_n = "<span class='preto'>Em análise</span>";break;
						case 2: $stc_status_n = "<span class='azul'>Aberto</span>";break;
						case 3: $stc_status_n = "<span class='laranja'>Pendente</span>";break;
						case 4: $stc_status_n = "<span class='verde'>Finalizado</span>";break;
						case 5: $stc_status_n = "<span class='vermelho'>Cancelado";break;
					}
					$stc_observacao = mysql_result($query, $x, 'stc_observacao');
					
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
							<p><b>Status:</b> ".$stc_status_n."
							<p><b>Observações:</b> ".$stc_observacao."
							<span class='cd-date'>".$stc_data."<br>às ".$stc_hora."</span>
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
			<div style='display:table; width:100%;'>
				<form name='form_filtro' id='form_filtro' enctype='multipart/form-data' method='post' action='visualizar_chamado.php?pagina=visualizar_chamado&action=salvar_status&cha_id=$cha_id".$autenticacao."'>
				<div class='subquadro' style='width:45%; float:left; line-height:25px;'>
					<div class='status'>
						<p class='subtitle'><input type='button' id='bt_status' value='Adicionar Novo Status' /></p>
						<div class='conteudo'>
							<select name='stc_status' id='stc_status'>
								<option value=''>Status</option>
								<option value='1'>Em análise</option>
								<option value='2'>Aberto</option>
								<option value='3'>Pendente</option>
								<option value='4'>Finalizado</option>
								<option value='5'>Cancelado</option>
							</select>
							<p>
							<textarea name='stc_observacao' id='stc_observacao' placeholder='Observação'></textarea>
							<p>
							<input type='submit' id='bt_status' value='Salvar' />
						</div>
					</div>
				</div>
				</form>
				<form name='form_filtro' id='form_filtro' enctype='multipart/form-data' method='post' action='visualizar_chamado.php?pagina=visualizar_chamado&action=salvar_tecnico&cha_id=$cha_id".$autenticacao."'>
				<div class='subquadro'  style='width:45%; float:right; line-height:25px;'>
					<div class='status'>
						<p class='subtitle'><input type='button' id='bt_status' value='Adicionar Técnico' /></p>
						<div class='conteudo'>
							<select name='cha_tecnico' id='cha_tecnico'>
								<option value='$cha_tecnico'>$tec_nome</option>
								"; 
								$sql = "SELECT * FROM cadastro_tecnicos
										ORDER BY tec_nome ASC";
								$query = mysql_query($sql,$conexao);
								while($row = mysql_fetch_array($query) )
								{
									echo "<option value='".$row['tec_id']."'>".$row['tec_nome']."</option>";
								}
								echo "
							</select>
							<p>
							<input type='submit' id='bt_tecnico' value='Salvar' />
						</div>
					</div>
				</div>
				</form>
			</div>
			
			
			<div class='titulo'>  </div>				
		</div>";
	}
	else
	{
		echo "<div class='centro'><br><br><br>Nenhum chamado encontrado.</div>";
	}
		
}


include('../mod_rodape/rodape.php');
?>
