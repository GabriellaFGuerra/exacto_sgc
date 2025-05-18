<?php
session_start();
$pagina_link = 'cadastro_clientes';
include '../mod_includes/php/connect.php';

// Função para obter input de forma segura
function getInput($key, $default = null, $method = 'POST')
{
	$array = $method === 'POST' ? $_POST : $_GET;
	return $array[$key] ?? $default;
}

// Função para exibir mensagens em modal
function showModalMessage($icon, $message, $extra = '')
{
	echo "<script>abreMask('<img src=../imagens/$icon.png> $message<br><br><input value=\" Ok \" type=\"button\" class=\"close_janela\">$extra');</script>";
}

// Função para buscar nome do cliente
function getClientName($pdo, $cli_id)
{
	$stmt = $pdo->prepare('SELECT cli_nome_razao FROM cadastro_clientes WHERE cli_id = ?');
	$stmt->execute([$cli_id]);
	return $stmt->fetchColumn();
}

// Função para buscar lista de UFs
function getUfOptions($pdo, $selected = '')
{
	$stmt = $pdo->query('SELECT * FROM end_uf ORDER BY uf_sigla');
	$options = "<option value=''>UF</option>";
	foreach ($stmt as $row) {
		$selectedAttr = $row['uf_id'] == $selected ? 'selected' : '';
		$options .= "<option value='{$row['uf_id']}' $selectedAttr>{$row['uf_sigla']}</option>";
	}
	return $options;
}

// Função para buscar nome do município
function getMunicipioOption($pdo, $mun_id)
{
	if (!$mun_id) return "<option value=''>Município</option>";
	$stmt = $pdo->prepare('SELECT mun_nome FROM end_municipios WHERE mun_id = ?');
	$stmt->execute([$mun_id]);
	$mun_nome = $stmt->fetchColumn();
	return "<option value='$mun_id'>$mun_nome</option>";
}

// Variáveis principais
$cli_id = getInput('cli_id', null, 'GET');
$action = getInput('action', null, 'GET');
$pagina = getInput('pagina', null, 'GET');
$pag = (int)getInput('pag', 1, 'GET');
$autenticacao = $_GET['autenticacao'] ?? '';
$titulo = 'Cadastro de Unidades';

// Página de navegação
$nome_cliente = getClientName($pdo, $cli_id);
$page = "Cadastros &raquo; <a href='cadastro_clientes.php?pagina=cadastro_clientes$autenticacao'>Clientes</a>: $nome_cliente &raquo;  <a href='cadastro_unidades.php?pagina=cadastro_unidades&cli_id=$cli_id$autenticacao'>Unidades</a> ";

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title><?= htmlspecialchars($titulo) ?></title>
	<meta name="author" content="MogiComp">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="shortcut icon" href="../imagens/favicon.png">
	<?php include '../css/style.php'; ?>
	<script src="../mod_includes/js/funcoes.js" type="text/javascript"></script>
	<script type="text/javascript" src="../mod_includes/js/jquery-1.8.3.min.js"></script>
	<link href="../mod_includes/js/toolbar/jquery.toolbars.css" rel="stylesheet" />
	<link href="../mod_includes/js/toolbar/bootstrap.icons.css" rel="stylesheet">
	<script src="../mod_includes/js/toolbar/jquery.toolbar.js"></script>
</head>
<body>
<?php
include '../mod_includes/php/funcoes-jquery.php';
require_once '../mod_includes/php/verificalogin.php';
include '../mod_topo/topo.php';
require_once '../mod_includes/php/verificapermissao.php';

// CRUD Actions
if ($action === 'adicionar') {
	$fields = [
		'uni_nome_razao', 'uni_cnpj', 'uni_cep', 'uni_uf', 'uni_municipio', 'uni_bairro',
		'uni_endereco', 'uni_numero', 'uni_comp', 'uni_responsavel', 'uni_telefone',
		'uni_celular', 'uni_email', 'uni_status'
	];
	$data = [];
	foreach ($fields as $f) $data[$f] = getInput($f);

	$sql = 'INSERT INTO cadastro_unidades (
		uni_cliente, uni_nome_razao, uni_cnpj, uni_cep, uni_uf, uni_municipio, uni_bairro,
		uni_endereco, uni_numero, uni_comp, uni_responsavel, uni_telefone, uni_celular, uni_email, uni_status
	) VALUES (
		:cli_id, :uni_nome_razao, :uni_cnpj, :uni_cep, :uni_uf, :uni_municipio, :uni_bairro,
		:uni_endereco, :uni_numero, :uni_comp, :uni_responsavel, :uni_telefone, :uni_celular, :uni_email, :uni_status
	)';
	$stmt = $pdo->prepare($sql);
	$params = array_merge(['cli_id' => $cli_id], $data);
	if ($stmt->execute($params)) {
		showModalMessage('ok', 'Cadastro efetuado com sucesso.');
	} else {
		showModalMessage('x', 'Erro ao efetuar cadastro, por favor tente novamente.', "<input value=' Ok ' type='button' onclick='window.history.back();'>");
	}
}

if ($action === 'editar') {
	$uni_id = getInput('uni_id', null, 'GET');
	$fields = [
		'uni_nome_razao', 'uni_cnpj', 'uni_cep', 'uni_uf', 'uni_municipio', 'uni_bairro',
		'uni_endereco', 'uni_numero', 'uni_comp', 'uni_responsavel', 'uni_telefone',
		'uni_celular', 'uni_email', 'uni_status'
	];
	$data = [];
	foreach ($fields as $f) $data[$f] = getInput($f);

	$sql = 'UPDATE cadastro_unidades SET 
		uni_cliente = :cli_id, uni_nome_razao = :uni_nome_razao, uni_cnpj = :uni_cnpj, uni_cep = :uni_cep,
		uni_uf = :uni_uf, uni_municipio = :uni_municipio, uni_bairro = :uni_bairro, uni_endereco = :uni_endereco,
		uni_numero = :uni_numero, uni_comp = :uni_comp, uni_responsavel = :uni_responsavel, uni_telefone = :uni_telefone,
		uni_celular = :uni_celular, uni_email = :uni_email, uni_status = :uni_status
		WHERE uni_id = :uni_id';
	$stmt = $pdo->prepare($sql);
	$params = array_merge(['cli_id' => $cli_id, 'uni_id' => $uni_id], $data);
	if ($stmt->execute($params)) {
		showModalMessage('ok', 'Dados alterados com sucesso.');
	} else {
		showModalMessage('x', 'Erro ao alterar dados, por favor tente novamente.', "<input value=' Ok ' type='button' onclick='window.history.back();'>");
	}
}

if ($action === 'excluir') {
	$uni_id = getInput('uni_id', null, 'GET');
	$stmt = $pdo->prepare('DELETE FROM cadastro_unidades WHERE uni_id = ?');
	if ($stmt->execute([$uni_id])) {
		showModalMessage('ok', 'Exclusão realizada com sucesso');
	} else {
		showModalMessage('x', 'Este item não pode ser excluído pois está relacionado com alguma tabela.', "<input value=' Ok ' type='button' onclick='window.history.back();'>");
	}
}

if ($action === 'ativar' || $action === 'desativar') {
	$uni_id = getInput('uni_id', null, 'GET');
	$status = $action === 'ativar' ? 1 : 0;
	$stmt = $pdo->prepare('UPDATE cadastro_unidades SET uni_status = ? WHERE uni_id = ?');
	if ($stmt->execute([$status, $uni_id])) {
		$msg = $status ? 'Ativação realizada com sucesso' : 'Desativação realizada com sucesso';
		showModalMessage('ok', $msg);
	} else {
		showModalMessage('x', 'Erro ao alterar dados, por favor tente novamente.', "<input value=' Ok ' type='button' onclick='window.history.back();'>");
	}
}

// Paginação
$num_por_pagina = 10;
$primeiro_registro = ($pag - 1) * $num_por_pagina;

// Listagem de unidades
if ($pagina === 'cadastro_unidades') {
	// Contagem total para paginação
	$stmtCount = $pdo->prepare('SELECT COUNT(*) FROM cadastro_unidades WHERE uni_cliente = :cli_id');
	$stmtCount->execute(['cli_id' => $cli_id]);
	$total_registros = $stmtCount->fetchColumn();

	$sql = 'SELECT cadastro_unidades.*, cadastro_clientes.cli_nome_razao
		FROM cadastro_unidades 
		LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = cadastro_unidades.uni_cliente
		WHERE cadastro_unidades.uni_cliente = :cli_id
		ORDER BY uni_nome_razao ASC
		LIMIT :offset, :limit';
	$stmt = $pdo->prepare($sql);
	$stmt->bindValue(':cli_id', $cli_id, PDO::PARAM_INT);
	$stmt->bindValue(':offset', $primeiro_registro, PDO::PARAM_INT);
	$stmt->bindValue(':limit', $num_por_pagina, PDO::PARAM_INT);
	$stmt->execute();
	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

	echo "<div class='centro'>
		<div class='titulo'> $page  </div>
		<div id='botoes'><input value='Nova Unidade' type='button' onclick=\"window.location.href='cadastro_unidades.php?pagina=adicionar_cadastro_unidades&cli_id=$cli_id$autenticacao'\" /></div>";

	if ($rows) {
		echo "<table align='center' width='100%' border='0' cellspacing='0' cellpadding='10' class='bordatabela'>
		<tr>
			<td class='titulo_tabela'>Razão Social</td>
			<td class='titulo_tabela'>CNPJ</td>
			<td class='titulo_tabela'>Responsável</td>
			<td class='titulo_tabela'>Telefone</td>
			<td class='titulo_tabela'>Celular</td>
			<td class='titulo_tabela'>Email</td>
			<td class='titulo_tabela'>Status</td>
			<td class='titulo_tabela' align='center'>Gerenciar</td>
		</tr>";
		$c = 0;
		foreach ($rows as $row) {
			$uni_id = $row['uni_id'];
			$uni_nome_razao = htmlspecialchars($row['uni_nome_razao']);
			$uni_cnpj = htmlspecialchars($row['uni_cnpj']);
			$uni_responsavel = htmlspecialchars($row['uni_responsavel']);
			$uni_telefone = htmlspecialchars($row['uni_telefone']);
			$uni_celular = htmlspecialchars($row['uni_celular']);
			$uni_email = htmlspecialchars($row['uni_email']);
			$uni_status = $row['uni_status'];
			$c1 = $c++ % 2 == 0 ? 'linhaimpar' : 'linhapar';

			echo "
			<script>
			jQuery(function($) {
				$('#normal-button-$uni_id').toolbar({content: '#user-options-$uni_id', position: 'top', hideOnClick: true});
			});
			</script>
			<div id='user-options-$uni_id' class='toolbar-icons' style='display: none;'>";
			if ($uni_status == 1) {
				echo "<a href='cadastro_unidades.php?pagina=cadastro_unidades&action=desativar&uni_id=$uni_id&cli_id=$cli_id$autenticacao'><img border='0' src='../imagens/icon-ativa-desativa.png'></a>";
			} else {
				echo "<a href='cadastro_unidades.php?pagina=cadastro_unidades&action=ativar&uni_id=$uni_id&cli_id=$cli_id$autenticacao'><img border='0' src='../imagens/icon-ativa-desativa.png'></a>";
			}
			echo "
				<a href='cadastro_unidades.php?pagina=editar_cadastro_unidades&uni_id=$uni_id&cli_id=$cli_id$autenticacao'><img border='0' src='../imagens/icon-editar.png'></a>
				<a onclick=\"
					abreMask(
						'Deseja realmente excluir a unidade <b>$uni_nome_razao</b>?<br><br>'+
						'<input value=\\' Sim \\' type=\\'button\\' onclick=javascript:window.location.href=\\'cadastro_unidades.php?pagina=cadastro_unidades&action=excluir&uni_id=$uni_id&cli_id=$cli_id$autenticacao\\';>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+
						'<input value=\\' Não \\' type=\\'button\\' class=\\'close_janela\\'>');
					\">
					<img border='0' src='../imagens/icon-excluir.png'>
				</a>
			</div>
			<tr class='$c1'>
				<td>$uni_nome_razao</td>
				<td>$uni_cnpj</td>
				<td>$uni_responsavel</td>
				<td>$uni_telefone</td>
				<td>$uni_celular</td>
				<td>$uni_email</td>
				<td align=center>";
			echo $uni_status == 1
				? "<img border='0' src='../imagens/icon-ativo.png' width='15' height='15'>"
				: "<img border='0' src='../imagens/icon-inativo.png' width='15' height='15'>";
			echo "</td>
				<td align=center><div id='normal-button-$uni_id' class='settings-button'><img src='../imagens/icon-cog-small.png' /></div></td>
			</tr>";
		}
		echo "</table>";

		// Paginação
		$total_paginas = ceil($total_registros / $num_por_pagina);
		$variavel = "&pagina=cadastro_unidades$autenticacao";
		if ($total_paginas > 1) {
			echo "<div class='paginacao'>";
			for ($i = 1; $i <= $total_paginas; $i++) {
				$active = $i == $pag ? "style='font-weight:bold;'" : '';
				echo "<a href='cadastro_unidades.php?pag=$i&cli_id=$cli_id$variavel' $active>$i</a> ";
			}
			echo "</div>";
		}
	} else {
		echo "<br><br><br>Não há nenhuma unidade cadastrada.";
	}
	echo "<div class='titulo'>  </div></div>";
}

// Formulário de adição
if ($pagina === 'adicionar_cadastro_unidades') {
	echo "<form name='form_cadastro_unidades' id='form_cadastro_unidades' enctype='multipart/form-data' method='post' action='cadastro_unidades.php?pagina=adicionar_cadastro_unidades&action=adicionar&cli_id=$cli_id$autenticacao'>
	<div class='centro'>
		<div class='titulo'> $page &raquo; Adicionar  </div>
		<table align='center' cellspacing='0' width='580'>
			<tr>
				<td align='left'>
					<input name='uni_nome_razao' id='uni_nome_razao' placeholder='Razão Social'>
					<p>
					<div style='display:table; width:100%'>
					<input name='uni_cnpj' id='uni_cnpj' placeholder='CNPJ' maxlength='18' onkeypress='mascaraCNPJ(this); return SomenteNumero(event);' class='left'>
					<div id='uni_cnpj_erro' class='left'>&nbsp;</div>
					</div>
					<p>
					<div class='formtitulo'>Endereço</div>
					<input name='uni_cep' id='uni_cep' placeholder='CEP' maxlength='9' onkeypress='mascaraCEP(this); return SomenteNumero(event);' />
					<select name='uni_uf' id='uni_uf'>" . getUfOptions($pdo) . "</select>
					<select name='uni_municipio' id='uni_municipio'><option value=''>Município</option></select>
					<input name='uni_bairro' id='uni_bairro' placeholder='Bairro' />
					<p>
					<input name='uni_endereco' id='uni_endereco' placeholder='Endereço' />
					<input name='uni_numero' id='uni_numero' placeholder='Número' />
					<input name='uni_comp' id='uni_comp' placeholder='Complemento' />
					<p>
					<div class='formtitulo'>Dados de Contato</div>
					<input name='uni_responsavel' id='uni_responsavel' placeholder='Responsável'>
					<p>
					<input name='uni_telefone' id='uni_telefone' placeholder='Telefone (c/ DDD)' onkeypress='mascaraTELEFONE(this); return SomenteNumeroCEL(this,event);'>
					<input name='uni_celular' id='uni_celular' placeholder='Celular (c/ DDD)' onkeypress='mascaraTELEFONE(this); return SomenteNumeroCEL(this,event);'>
					<p>
					<input name='uni_email' id='uni_email' placeholder='Email'>
					<p>
					<input type='radio' name='uni_status' value='1' checked> Ativo &nbsp;&nbsp;&nbsp;
					<input type='radio' name='uni_status' value='0'> Inativo<br>
					<p>
					<center>
					<div id='erro' align='center'>&nbsp;</div>
					<input type='button' id='bt_cadastro_unidades' value='Salvar' />&nbsp;&nbsp;&nbsp;&nbsp; 
					<input type='button' id='botao_cancelar' onclick=\"window.location.href='cadastro_unidades.php?pagina=cadastro_unidades&cli_id=$cli_id$autenticacao'\" value='Cancelar'/></center>
					</center>
				</td>
			</tr>
		</table>
		<div class='titulo'> </div>
	</div>
	</form>";
}

// Formulário de edição
if ($pagina === 'editar_cadastro_unidades') {
	$uni_id = getInput('uni_id', null, 'GET');
	$stmt = $pdo->prepare('SELECT cadastro_unidades.*, cadastro_clientes.cli_nome_razao, end_uf.uf_sigla, end_municipios.mun_nome
		FROM cadastro_unidades
		LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = cadastro_unidades.uni_cliente
		LEFT JOIN end_uf ON end_uf.uf_id = cadastro_unidades.uni_uf
		LEFT JOIN end_municipios ON end_municipios.mun_id = cadastro_unidades.uni_municipio
		WHERE uni_id = ?');
	$stmt->execute([$uni_id]);
	$row = $stmt->fetch(PDO::FETCH_ASSOC);

	if ($row) {
		$uni_nome_razao = htmlspecialchars($row['uni_nome_razao']);
		$uni_cnpj = htmlspecialchars($row['uni_cnpj']);
		$uni_cep = htmlspecialchars($row['uni_cep']);
		$uni_uf = $row['uni_uf'];
		$uni_municipio = $row['uni_municipio'];
		$uni_bairro = htmlspecialchars($row['uni_bairro']);
		$uni_endereco = htmlspecialchars($row['uni_endereco']);
		$uni_numero = htmlspecialchars($row['uni_numero']);
		$uni_comp = htmlspecialchars($row['uni_comp']);
		$uni_responsavel = htmlspecialchars($row['uni_responsavel']);
		$uni_telefone = htmlspecialchars($row['uni_telefone']);
		$uni_celular = htmlspecialchars($row['uni_celular']);
		$uni_email = htmlspecialchars($row['uni_email']);
		$uni_status = $row['uni_status'];

		echo "<form name='form_cadastro_unidades' id='form_cadastro_unidades' enctype='multipart/form-data' method='post' action='cadastro_unidades.php?pagina=cadastro_unidades&action=editar&uni_id=$uni_id&cli_id=$cli_id$autenticacao'>
		<div class='centro'>
			<div class='titulo'> $page &raquo; Editar: $uni_nome_razao </div>
			<table align='center' cellspacing='0'>
				<tr>
					<td align='left'>
						<input type='hidden' name='uni_id' id='uni_id' value='$uni_id'>
						<input name='uni_nome_razao' id='uni_nome_razao' value='$uni_nome_razao' placeholder='Razão Social'>
						<p>
						<div style='display:table; width:100%'>
						<input name='uni_cnpj' id='uni_cnpj' value='$uni_cnpj' placeholder='CNPJ' maxlength='18' onkeypress='mascaraCNPJ(this); return SomenteNumero(event);' class='left'>
						<div id='uni_cnpj_erro' class='left'>&nbsp;</div>
						</div>
						<p>
						<div class='formtitulo'>Endereço</div>
						<input name='uni_cep' id='uni_cep' value='$uni_cep' placeholder='CEP' maxlength='9' onkeypress='mascaraCEP(this); return SomenteNumero(event);' />
						<select name='uni_uf' id='uni_uf'>" . getUfOptions($pdo, $uni_uf) . "</select>
						<select name='uni_municipio' id='uni_municipio'>" . getMunicipioOption($pdo, $uni_municipio) . "</select>
						<input name='uni_bairro' id='uni_bairro' value='$uni_bairro'  placeholder='Bairro' />
						<p>
						<input name='uni_endereco' id='uni_endereco' value='$uni_endereco' placeholder='Endereço' />
						<input name='uni_numero' id='uni_numero' value='$uni_numero' placeholder='Número' />
						<input name='uni_comp' id='uni_comp' value='$uni_comp' placeholder='Complemento' />
						<p>
						<div class='formtitulo'>Dados de Contato</div>
						<input name='uni_responsavel' id='uni_responsavel' value='$uni_responsavel' placeholder='Responsável'>
						<p>
						<input name='uni_telefone' id='uni_telefone' value='$uni_telefone' placeholder='Telefone (c/ DDD)' onkeypress='mascaraTELEFONE(this); return SomenteNumeroCEL(this,event);'>
						<input name='uni_celular' id='uni_celular' value='$uni_celular' placeholder='Celular (c/ DDD)' onkeypress='mascaraTELEFONE(this); return SomenteNumeroCEL(this,event);'>
						<p>
						<input name='uni_email' id='uni_email' value='$uni_email' placeholder='Email'>
						<p>
						<center>
						<div id='erro' align='center'>&nbsp;</div>
						<input type='button' id='bt_cadastro_unidades' value='Salvar' />&nbsp;&nbsp;&nbsp;&nbsp; 
						<input type='button' id='botao_cancelar' onclick=\"window.location.href='cadastro_unidades.php?pagina=cadastro_unidades&cli_id=$cli_id$autenticacao'\" value='Cancelar'/></center>
						</center>
					</td>
				</tr>
			</table>
			<div class='titulo'>   </div>
		</div>
		</form>";
	}
}

include '../mod_rodape/rodape.php';
?>
</body>
</html>