<?php
session_start();
$pagina_link = 'cadastro_clientes';
include('../mod_includes/php/connect.php'); // $pdo deve estar disponível aqui

function getPost($key, $default = '')
{
	return $_POST[$key] ?? $default;
}

function getRequest($key, $default = '')
{
	return $_REQUEST[$key] ?? $default;
}

function getGet($key, $default = '')
{
	return $_GET[$key] ?? $default;
}

function abreMask($msg)
{
	echo "<SCRIPT language='JavaScript'>abreMask('$msg');</SCRIPT>";
}

$page = "Cadastros &raquo; <a href='cadastro_clientes.php?pagina=cadastro_clientes'>Clientes</a>";
$action = getRequest('action');
$pagina = getRequest('pagina');
$pag = (int) getRequest('pag', 1);

if ($action === "adicionar") {
	$cli_nome_razao = getPost('cli_nome_razao');
	$cli_cnpj = getPost('cli_cnpj');
	$cli_cep = getPost('cli_cep');
	$cli_uf = getPost('cli_uf');
	$cli_municipio = getPost('cli_municipio');
	$cli_bairro = getPost('cli_bairro');
	$cli_endereco = getPost('cli_endereco');
	$cli_numero = getPost('cli_numero');
	$cli_comp = getPost('cli_comp');
	$cli_telefone = getPost('cli_telefone');
	$cli_email = getPost('cli_email');
	$cli_senha = password_hash(getPost('cli_senha'), PASSWORD_DEFAULT);
	$cli_status = getPost('cli_status', 1);

	$stmt = $pdo->prepare("INSERT INTO cadastro_clientes (
		cli_nome_razao, cli_cnpj, cli_cep, cli_uf, cli_municipio, cli_bairro, cli_endereco, cli_numero, cli_comp, cli_telefone, cli_email, cli_senha, cli_status
	) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
	$ok = $stmt->execute([
		$cli_nome_razao,
		$cli_cnpj,
		$cli_cep,
		$cli_uf,
		$cli_municipio,
		$cli_bairro,
		$cli_endereco,
		$cli_numero,
		$cli_comp,
		$cli_telefone,
		$cli_email,
		$cli_senha,
		$cli_status
	]);
	if ($ok) {
		$ultimo_id = $pdo->lastInsertId();
		$caminho = "../admin/clientes/";
		$arquivo = '';
		if (!empty($_FILES['cli_foto']['name'][0])) {
			if (!file_exists($caminho))
				mkdir($caminho, 0755, true);
			$nomeArquivo = $_FILES['cli_foto']['name'][0];
			$nomeTemporario = $_FILES['cli_foto']['tmp_name'][0];
			$extensao = pathinfo($nomeArquivo, PATHINFO_EXTENSION);
			$arquivo = $caminho . md5(mt_rand(1, 10000) . $nomeArquivo) . '.' . $extensao;
			move_uploaded_file($nomeTemporario, $arquivo);
			$stmt = $pdo->prepare("UPDATE cadastro_clientes SET cli_foto = ? WHERE cli_id = ?");
			$stmt->execute([$arquivo, $ultimo_id]);
		}
		abreMask("<img src=../imagens/ok.png> Cadastro efetuado com sucesso.<br><br><input value=' Ok ' type='button' class='close_janela'>");
	} else {
		abreMask("<img src=../imagens/x.png> Erro ao efetuar cadastro, por favor tente novamente.<br><br><input value=' Ok ' type='button' onclick=javascript:window.history.back();>");
	}
}

if ($action === 'editar') {
	$cli_id = getGet('cli_id');
	$cli_nome_razao = getPost('cli_nome_razao');
	$cli_cnpj = getPost('cli_cnpj');
	$cli_cep = getPost('cli_cep');
	$cli_uf = getPost('cli_uf');
	$cli_municipio = getPost('cli_municipio');
	$cli_bairro = getPost('cli_bairro');
	$cli_endereco = getPost('cli_endereco');
	$cli_numero = getPost('cli_numero');
	$cli_comp = getPost('cli_comp');
	$cli_telefone = getPost('cli_telefone');
	$cli_email = getPost('cli_email');
	$cli_senha = getPost('cli_senha');
	$cli_status = getPost('cli_status', 1);

	// Recupera senha antiga
	$stmt = $pdo->prepare("SELECT cli_senha FROM cadastro_clientes WHERE cli_id = ?");
	$stmt->execute([$cli_id]);
	$senhacompara = $stmt->fetchColumn();
	if (password_verify($cli_senha, $senhacompara)) {
		$cli_senha = $senhacompara;
	} else {
		$cli_senha = password_hash($cli_senha, PASSWORD_DEFAULT);
	}

	$stmt = $pdo->prepare("UPDATE cadastro_clientes SET 
		cli_nome_razao = ?, cli_cnpj = ?, cli_cep = ?, cli_uf = ?, cli_municipio = ?, cli_bairro = ?, cli_endereco = ?, cli_numero = ?, cli_comp = ?, cli_telefone = ?, cli_email = ?, cli_senha = ?, cli_status = ?
		WHERE cli_id = ?");
	$ok = $stmt->execute([
		$cli_nome_razao,
		$cli_cnpj,
		$cli_cep,
		$cli_uf,
		$cli_municipio,
		$cli_bairro,
		$cli_endereco,
		$cli_numero,
		$cli_comp,
		$cli_telefone,
		$cli_email,
		$cli_senha,
		$cli_status,
		$cli_id
	]);
	if ($ok) {
		$caminho = "../admin/clientes/";
		if (!empty($_FILES['cli_foto']['name'][0])) {
			if (!file_exists($caminho))
				mkdir($caminho, 0755, true);
			$nomeArquivo = $_FILES['cli_foto']['name'][0];
			$nomeTemporario = $_FILES['cli_foto']['tmp_name'][0];
			$extensao = pathinfo($nomeArquivo, PATHINFO_EXTENSION);
			$arquivo = $caminho . md5(mt_rand(1, 10000) . $nomeArquivo) . '.' . $extensao;

			// Remove foto antiga
			$stmt = $pdo->prepare("SELECT cli_foto FROM cadastro_clientes WHERE cli_id = ?");
			$stmt->execute([$cli_id]);
			$cli_foto_old = $stmt->fetchColumn();
			if ($cli_foto_old && file_exists($cli_foto_old))
				unlink($cli_foto_old);

			move_uploaded_file($nomeTemporario, $arquivo);
			$stmt = $pdo->prepare("UPDATE cadastro_clientes SET cli_foto = ? WHERE cli_id = ?");
			$stmt->execute([$arquivo, $cli_id]);
		}
		abreMask("<img src=../imagens/ok.png> Dados alterados com sucesso.<br><br><input value=' Ok ' type='button' class='close_janela'>");
	} else {
		abreMask("<img src=../imagens/x.png> Erro ao alterar dados, por favor tente novamente.<br><br><input value=' Ok ' type='button' onclick=javascript:window.history.back();>");
	}
}

if ($action === 'excluir') {
	$cli_id = getGet('cli_id');
	$stmt = $pdo->prepare("UPDATE cadastro_clientes SET cli_deletado = 0 WHERE cli_id = ?");
	if ($stmt->execute([$cli_id])) {
		abreMask("<img src=../imagens/ok.png> Exclusão realizada com sucesso<br><br><input value=' OK ' type='button' class='close_janela'>");
	} else {
		abreMask("<img src=../imagens/x.png> Este item não pode ser excluído pois está relacionado com alguma tabela.<br><br><input value=' Ok ' type='button' onclick=javascript:window.history.back(); >");
	}
}

if ($action === 'ativar') {
	$cli_id = getGet('cli_id');
	$stmt = $pdo->prepare("UPDATE cadastro_clientes SET cli_status = 1 WHERE cli_id = ?");
	if ($stmt->execute([$cli_id])) {
		abreMask("<img src=../imagens/ok.png> Ativação realizada com sucesso<br><br><input value=' OK ' type='button' class='close_janela'>");
	} else {
		abreMask("<img src=../imagens/x.png> Erro ao alterar dados, por favor tente novamente.<br><br><input value=' Ok ' type='button' onclick=javascript:window.history.back(); >");
	}
}

if ($action === 'desativar') {
	$cli_id = getGet('cli_id');
	$stmt = $pdo->prepare("UPDATE cadastro_clientes SET cli_status = 0 WHERE cli_id = ?");
	if ($stmt->execute([$cli_id])) {
		abreMask("<img src=../imagens/ok.png> Desativação realizada com sucesso<br><br><input value=' OK ' type='button' class='close_janela'>");
	} else {
		abreMask("<img src=../imagens/x.png> Erro ao alterar dados, por favor tente novamente.<br><br><input value=' Ok ' type='button' onclick=javascript:window.history.back(); >");
	}
}

$num_por_pagina = 10;
$primeiro_registro = ($pag - 1) * $num_por_pagina;
$fil_nome = getRequest('fil_nome');
$fil_cli_cnpj = str_replace([".", "-"], "", getRequest('fil_cli_cnpj'));

$nome_query = $fil_nome ? "cli_nome_razao LIKE :fil_nome" : "1=1";
$cnpj_query = $fil_cli_cnpj ? "REPLACE(REPLACE(cli_cnpj, '.', ''), '-', '') LIKE :fil_cli_cnpj" : "1=1";

$sql = "SELECT * FROM cadastro_clientes 
		INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id
		WHERE cli_deletado = 1 and cli_status = 1 and ucl_usuario = :usuario_id AND $nome_query AND $cnpj_query
		ORDER BY cli_nome_razao ASC
		LIMIT $primeiro_registro, $num_por_pagina";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':usuario_id', $_SESSION['usuario_id']);
if ($fil_nome)
	$stmt->bindValue(':fil_nome', "%$fil_nome%");
if ($fil_cli_cnpj)
	$stmt->bindValue(':fil_cli_cnpj', "%$fil_cli_cnpj%");
$stmt->execute();
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($pagina == "cadastro_clientes") {
	echo "
	<div class='centro'>
		<div class='titulo'> $page  </div>
		<div id='botoes'><input value='Novo Cliente' type='button' onclick=javascript:window.location.href='cadastro_clientes.php?pagina=adicionar_cadastro_clientes'; /></div>
		<div class='filtro'>
			<form name='form_filtro' id='form_filtro' enctype='multipart/form-data' method='post' action='cadastro_clientes.php?pagina=cadastro_clientes'>
			<input name='fil_nome' id='fil_nome' value='$fil_nome' placeholder='Nome/Razão Social'>
			<input type='text' name='fil_cli_cnpj' id='fil_cli_cnpj' placeholder='C.N.P.J' value='$fil_cli_cnpj'>						
			<input type='submit' value='Filtrar'> 
			</form>
		</div>
		";
	if (count($clientes) > 0) {
		echo "
		<table align='center' width='100%' border='0' cellspacing='0' cellpadding='10' class='bordatabela'>
			<tr>
				<td class='titulo_tabela'>Logo</td>
				<td class='titulo_tabela'>Razão Social</td>
				<td class='titulo_tabela'>CNPJ</td>
				<td class='titulo_tabela'>Telefone</td>
				<td class='titulo_tabela'>Email</td>
				<td class='titulo_tabela'>Status</td>
				<td class='titulo_tabela' align='center'>Gerenciar</td>
			</tr>";
		$c = 0;
		foreach ($clientes as $cliente) {
			$cli_id = $cliente['cli_id'];
			$cli_nome_razao = $cliente['cli_nome_razao'];
			$cli_cnpj = $cliente['cli_cnpj'];
			$cli_telefone = $cliente['cli_telefone'];
			$cli_email = $cliente['cli_email'];
			$cli_foto = $cliente['cli_foto'] ?: '../imagens/nophoto.png';
			$cli_status = $cliente['cli_status'];
			$c1 = $c % 2 == 0 ? "linhaimpar" : "linhapar";
			$c++;
			echo "
			<tr class='$c1'>
				<td><img src='$cli_foto' width='100'></td>
				<td>$cli_nome_razao</td>
				<td>$cli_cnpj</td>
				<td>$cli_telefone</td>
				<td>$cli_email</td>
				<td align=center>";
			echo $cli_status == 1
				? "<img border='0' src='../imagens/icon-ativo.png' width='15' height='15'>"
				: "<img border='0' src='../imagens/icon-inativo.png' width='15' height='15'>";
			echo "</td>
				<td align=center>
					<a href='cadastro_clientes.php?pagina=cadastro_clientes&action=" . ($cli_status ? "desativar" : "ativar") . "&cli_id=$cli_id'><img border='0' src='../imagens/icon-ativa-desativa.png'></a>
					<a href='cadastro_clientes.php?pagina=editar_cadastro_clientes&cli_id=$cli_id'><img border='0' src='../imagens/icon-editar.png'></a>
				</td>
			</tr>";
		}
		echo "</table>";
		// Inclua aqui a paginação se necessário
	} else {
		echo "<br><br><br>Não há nenhum cliente cadastrado.";
	}
	echo "<div class='titulo'>  </div></div>";
}

if ($pagina == 'adicionar_cadastro_clientes') {
	echo "	
	<form name='form_cadastro_clientes' id='form_cadastro_clientes' enctype='multipart/form-data' method='post' action='cadastro_clientes.php?pagina=cadastro_clientes&action=adicionar'>
	<div class='centro'>
		<div class='titulo'> $page &raquo; Adicionar  </div>
		<table align='center' cellspacing='0' width='580'>
			<tr>
				<td align='left'>
					<input type='file' name='cli_foto[]' id='cli_foto'> Logo
					<p>
					<input name='cli_nome_razao' id='cli_nome_razao' placeholder='Razão Social'>
					<p>
					<div style='display:table; width:100%'>
					<input name='cli_cnpj' id='cli_cnpj' placeholder='CNPJ' maxlength='18' class='left'>
					<div id='cli_cnpj_erro' class='left'>&nbsp;</div>
					</div>
					<p>
					<div class='formtitulo'>Endereço</div>
					<input name='cli_cep' id='cli_cep' placeholder='CEP' maxlength='9' />
					<select name='cli_uf' id='cli_uf'>
						<option value=''>UF</option>";
	$stmt = $pdo->query("SELECT * FROM end_uf ORDER BY uf_sigla");
	foreach ($stmt as $row) {
		echo "<option value='{$row['uf_id']}'>{$row['uf_sigla']}</option>";
	}
	echo "
					</select>
					<select name='cli_municipio' id='cli_municipio'>
						<option value=''>Município</option>
					</select>
					<input name='cli_bairro' id='cli_bairro' placeholder='Bairro' />
					<p>
					<input name='cli_endereco' id='cli_endereco' placeholder='Endereço' />
					<input name='cli_numero' id='cli_numero' placeholder='Número' />
					<input name='cli_comp' id='cli_comp' placeholder='Complemento' />
					<p>
					<div class='formtitulo'>Dados de Contato</div>
					<input name='cli_telefone' id='cli_telefone' placeholder='Telefone (c/ DDD)'>
					<p>
					<input name='cli_email' id='cli_email' placeholder='Email'>
					<input name='cli_senha' id='cli_senha' placeholder='Senha' type='password'>
					<div id='cli_email_erro'>&nbsp;</div>
					<p>
					<div class='formtitulo'>Status do Cliente</div>
					<input type='radio' name='cli_status' value='1' checked> Ativo &nbsp;&nbsp;&nbsp;
					<input type='radio' name='cli_status' value='0'> Inativo<br>
					<p>
					<center>
					<div id='erro' align='center'>&nbsp;</div>
					<input type='submit' id='bt_cadastro_clientes' value='Salvar' />&nbsp;&nbsp;&nbsp;&nbsp; 
					<input type='button' id='botao_cancelar' onclick=javascript:window.location.href='cadastro_clientes.php?pagina=cadastro_clientes'; value='Cancelar'/></center>
					</center>
				</td>
			</tr>
		</table>
		<div class='titulo'> </div>
	</div>
	</form>
	";
}

if ($pagina == 'editar_cadastro_clientes') {
	$cli_id = getGet('cli_id');
	$stmt = $pdo->prepare("SELECT * FROM cadastro_clientes 
		LEFT JOIN end_uf ON end_uf.uf_id = cadastro_clientes.cli_uf
		LEFT JOIN end_municipios ON end_municipios.mun_id = cadastro_clientes.cli_municipio
		WHERE cli_id = ?");
	$stmt->execute([$cli_id]);
	$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

	if ($cliente) {
		$cli_nome_razao = $cliente['cli_nome_razao'];
		$cli_cnpj = $cliente['cli_cnpj'];
		$cli_cep = $cliente['cli_cep'];
		$cli_uf = $cliente['cli_uf'];
		$uf_sigla = $cliente['uf_sigla'] ?? '';
		$cli_municipio = $cliente['cli_municipio'];
		$mun_nome = $cliente['mun_nome'] ?? '';
		$cli_bairro = $cliente['cli_bairro'];
		$cli_endereco = $cliente['cli_endereco'];
		$cli_numero = $cliente['cli_numero'];
		$cli_comp = $cliente['cli_comp'];
		$cli_telefone = $cliente['cli_telefone'];
		$cli_email = $cliente['cli_email'];
		$cli_foto = $cliente['cli_foto'];
		$cli_senha = $cliente['cli_senha'];
		$cli_status = $cliente['cli_status'];
		echo "
		<form name='form_cadastro_clientes' id='form_cadastro_clientes' enctype='multipart/form-data' method='post' action='cadastro_clientes.php?pagina=cadastro_clientes&action=editar&cli_id=$cli_id'>
		<div class='centro'>
			<div class='titulo'> $page &raquo; Editar: $cli_nome_razao </div>
			<table align='center' cellspacing='0'>
				<tr>
					<td align='left'>
						<input type='hidden' name='cli_id' id='cli_id' value='$cli_id' placeholder='ID'>
						Foto Atual:<br>
						<img src='$cli_foto'><br>
						<input type='file' name='cli_foto[]' id='cli_foto' value='$cli_foto' > Alterar Foto
						<p>
						<input name='cli_nome_razao' id='cli_nome_razao' value='$cli_nome_razao' placeholder='Razão Social'>
						<p>
						<div style='display:table; width:100%'>
						<input name='cli_cnpj' id='cli_cnpj' value='$cli_cnpj' placeholder='CNPJ' maxlength='18' class='left'>
						<div id='cli_cnpj_erro' class='left'>&nbsp;</div>
						</div>
						<p>
						<div class='formtitulo'>Endereço</div>
						<input name='cli_cep' id='cli_cep' value='$cli_cep' placeholder='CEP' maxlength='9' />
						<select name='cli_uf' id='cli_uf'>
							<option value='$cli_uf'>$uf_sigla</option>";
		$stmt = $pdo->query("SELECT * FROM end_uf ORDER BY uf_sigla");
		foreach ($stmt as $row) {
			echo "<option value='{$row['uf_id']}'>{$row['uf_sigla']}</option>";
		}
		echo "
						</select>
						<select name='cli_municipio' id='cli_municipio'>
							<option value='$cli_municipio'>$mun_nome</option>
						</select>
						<input name='cli_bairro' id='cli_bairro' value='$cli_bairro'  placeholder='Bairro' />
						<p>
						<input name='cli_endereco' id='cli_endereco' value='$cli_endereco' placeholder='Endereço' />
						<input name='cli_numero' id='cli_numero' value='$cli_numero' placeholder='Número' />
						<input name='cli_comp' id='cli_comp' value='$cli_comp' placeholder='Complemento' />
						<p>
						<div class='formtitulo'>Dados de Contato</div>
						<input name='cli_telefone' id='cli_telefone' value='$cli_telefone' placeholder='Telefone (c/ DDD)'>
						<p>
						<input name='cli_email' id='cli_email' value='$cli_email' placeholder='Email'>
						<input type='password' name='cli_senha' id='cli_senha' value='' placeholder='Senha'>
						<div id='cli_email_erro'>&nbsp;</div>
						<p>
						<div class='formtitulo'>Status do Cliente</div>";
		if ($cli_status == 1) {
			echo "<input type='radio' name='cli_status' value='1' checked> Ativo &nbsp;&nbsp;&nbsp;
								  <input type='radio' name='cli_status' value='0'> Inativo";
		} else {
			echo "<input type='radio' name='cli_status' value='1'> Ativo &nbsp;&nbsp;&nbsp;
								  <input type='radio' name='cli_status' value='0' checked> Inativo";
		}
		echo "
						<p>
						<center>
						<div id='erro' align='center'>&nbsp;</div>
						<input type='submit' id='bt_cadastro_clientes' value='Salvar' />&nbsp;&nbsp;&nbsp;&nbsp; 
						<input type='button' id='botao_cancelar' onclick=javascript:window.location.href='cadastro_clientes.php?pagina=cadastro_clientes'; value='Cancelar'/></center>
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