<?php
session_start();
$pagina_link = 'chamado_consultar';
include '../mod_includes/php/connect.php';

// Função para buscar dados do chamado
function buscarChamado($pdo, $chamado_id)
{
	$sql = "SELECT * FROM cadastro_chamados
		LEFT JOIN cadastro_equipamentos ON cadastro_equipamentos.equ_id = cadastro_chamados.cha_equipamento
		LEFT JOIN cadastro_tecnicos ON cadastro_tecnicos.tec_id = cadastro_chamados.cha_tecnico
		LEFT JOIN (cadastro_unidades 
			LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = cadastro_unidades.uni_cliente )
		ON cadastro_unidades.uni_id = cadastro_chamados.cha_unidade
		LEFT JOIN cadastro_status_chamado h1 ON h1.stc_chamado = cadastro_chamados.cha_id 
		WHERE h1.stc_id = (SELECT MAX(h2.stc_id) FROM cadastro_status_chamado h2 WHERE h2.stc_chamado = h1.stc_chamado) 
		AND cha_id = :cha_id
		GROUP BY cha_id";
	$stmt = $pdo->prepare($sql);
	$stmt->execute(['cha_id' => $chamado_id]);
	return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Função para buscar histórico paginado
function buscarHistoricoChamado($pdo, $chamado_id, $offset, $limite)
{
	$sql = "SELECT * FROM cadastro_chamados 
		LEFT JOIN cadastro_equipamentos ON cadastro_equipamentos.equ_id = cadastro_chamados.cha_equipamento
		LEFT JOIN cadastro_status_chamado ON cadastro_status_chamado.stc_chamado = cadastro_chamados.cha_id
		LEFT JOIN (cadastro_unidades 
			LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = cadastro_unidades.uni_cliente )
		ON cadastro_unidades.uni_id = cadastro_chamados.cha_unidade
		WHERE cha_id = :cha_id
		GROUP BY stc_id
		ORDER BY stc_data ASC
		LIMIT :offset, :limite";
	$stmt = $pdo->prepare($sql);
	$stmt->bindValue(':cha_id', $chamado_id, PDO::PARAM_INT);
	$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
	$stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
	$stmt->execute();
	return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para contar histórico
function contarHistoricoChamado($pdo, $chamado_id)
{
	$sql = "SELECT COUNT(*) as total FROM cadastro_status_chamado WHERE stc_chamado = :cha_id";
	$stmt = $pdo->prepare($sql);
	$stmt->execute(['cha_id' => $chamado_id]);
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	return $row['total'] ?? 0;
}

// Função para buscar técnicos
function buscarTecnicos($pdo)
{
	$sql = "SELECT * FROM cadastro_tecnicos ORDER BY tec_nome ASC";
	return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

include '../mod_includes/php/funcoes-jquery.php';
require_once '../mod_includes/php/verificalogin.php';
include "../mod_topo/topo.php";
require_once '../mod_includes/php/verificapermissao.php';

$chamado_id = $_GET['cha_id'] ?? null;
$acao = $_GET['action'] ?? null;
$pagina = $_GET['pagina'] ?? null;
$autenticacao = $_GET['autenticacao'] ?? '';
$pagina_atual = isset($_GET['pagina_hist']) ? max(1, intval($_GET['pagina_hist'])) : 1;
$itens_por_pagina = 20;
$offset = ($pagina_atual - 1) * $itens_por_pagina;

// Processa ações de salvar status ou técnico
if ($acao === "salvar_status" && $_SERVER['REQUEST_METHOD'] === 'POST') {
	$chamado = buscarChamado($pdo, $chamado_id);
	if ($chamado) {
		$email_cliente = $chamado['cli_email'] ?? '';
		$responsavel = $chamado['cha_responsavel'] ?? '';
		$protocolo = ($chamado['cha_ano'] ?? '') . ($chamado['cha_id'] ?? '');
	}
	$status = $_POST['stc_status'] ?? '';
	$observacao = $_POST['stc_observacao'] ?? '';
	$sql = "INSERT INTO cadastro_status_chamado (stc_chamado, stc_status, stc_observacao) VALUES (:cha_id, :stc_status, :stc_observacao)";
	$stmt = $pdo->prepare($sql);
	if ($stmt->execute(['cha_id' => $chamado_id, 'stc_status' => $status, 'stc_observacao' => $observacao])) {
		include "../mail/envia_email_status_chamado.php";
	} else {
		echo "<script>abreMask('<img src=../imagens/x.png> Erro ao efetuar cadastro, por favor tente novamente.<br><br><input value=\' Ok \' type=\'button\' onclick=javascript:window.history.back();>');</script>";
	}
}

if ($acao === "salvar_tecnico" && $_SERVER['REQUEST_METHOD'] === 'POST') {
	$tecnico_id = $_POST['cha_tecnico'] ?? '';
	$sql = "UPDATE cadastro_chamados SET cha_tecnico = :cha_tecnico WHERE cha_id = :cha_id";
	$stmt = $pdo->prepare($sql);
	if ($stmt->execute(['cha_tecnico' => $tecnico_id, 'cha_id' => $chamado_id])) {
		echo "<script>abreMask('<img src=../imagens/ok.png> Cadastro efetuado com sucesso.<br><input value=\' Ok \' type=\'button\' class=\'close_janela\'>' );</script>";
	} else {
		echo "<script>abreMask('<img src=../imagens/x.png> Erro ao efetuar cadastro, por favor tente novamente.<br><br><input value=\' Ok \' type=\'button\' onclick=javascript:window.history.back();>');</script>";
	}
}

// Exibe detalhes do chamado
$chamado = buscarChamado($pdo, $chamado_id);

if ($pagina === 'visualizar_chamado') {
	if ($chamado) {
		// Extrai dados do chamado
		extract($chamado);

		// Verifica se é avulso
		$avulso = (empty($cha_equipamento) && ($cha_avul_tipo || $cha_avul_marca || $cha_avul_modelo || $cha_avul_num_serie)) ? "Sim" : "Não";
		if ($avulso === "Sim") {
			$equ_tipo = $cha_avul_tipo;
			$equ_marca = $cha_avul_marca;
			$equ_modelo = $cha_avul_modelo;
			$equ_num_serie = $cha_avul_num_serie;
		}

		// Status
		$status_labels = [
			1 => "<span class='preto'>Em análise</span>",
			2 => "<span class='azul'>Aberto</span>",
			3 => "<span class='laranja'>Pendente</span>",
			4 => "<span class='verde'>Finalizado</span>",
			5 => "<span class='vermelho'>Cancelado</span>"
		];
		$status_atual = $status_labels[$stc_status] ?? '';

		// Datas formatadas
		$data_cadastro = date('d/m/Y', strtotime($cha_data));
		$hora_cadastro = date('H:i', strtotime($cha_data));
		$nome_tecnico = $tec_nome ?: "Selecione o técnico responsável por este chamado";

		echo "
		<div class='centro'>
			<img src='../imagens/pdf.png' class='right mouse' onclick=\"window.open('imprimir_chamado.php?pagina=imprimir_chamado&cha_id=$cha_id&autenticacao');\">
			<div class='titulo'> Visualizar Chamado </div>
			<div class='quadro'>
				<div style='width:90%; margin:0 auto; line-height:25px;'>
					<div class='formtitulo'>Dados do Chamado</div>
					<b>Cliente/Unidade:</b> <a href='cadastro_clientes.php?pagina=editar_cadastro_clientes&cli_id=$cli_id$autenticacao'><b>$cli_nome_razao</b></a> / <a href='cadastro_unidades.php?pagina=editar_cadastro_unidades&uni_id=$uni_id&cli_id=$cli_id$autenticacao'>$uni_nome_razao</a> <br>
					<b>Nº Protocolo:</b> $cha_ano$cha_id <br>
					<b>Chamado avulso?</b> $avulso <br>
					<b>Situação atual:</b> $status_atual <br>
					<b>Data de abertura:</b> $data_cadastro às $hora_cadastro <p>
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
					" . nl2br($cha_descricao) . " <p>
				</div>
			</div>
			<br>
			<div style='width:90%; margin:0 auto; line-height:25px;'>
				<div class='formtitulo'>Histórico do Chamado</div>
		";

		// Histórico com paginação
		$total_historico = contarHistoricoChamado($pdo, $cha_id);
		$historico = buscarHistoricoChamado($pdo, $cha_id, $offset, $itens_por_pagina);

		if ($historico) {
			echo "<section id='cd-timeline' class='cd-container'>";
			foreach ($historico as $item) {
				$data_hist = date('d/m/Y', strtotime($item['stc_data']));
				$hora_hist = date('H:i', strtotime($item['stc_data']));
				$status_hist = $status_labels[$item['stc_status']] ?? '';
				$observacao_hist = $item['stc_observacao'];
				echo "
				<div class='cd-timeline-block'>
					<div class='cd-timeline-img cd-location'>
						<img src='../imagens/cd-icon-location.svg' alt='Location'>
					</div>
					<div class='cd-timeline-content'>
						<p><b>Status:</b> $status_hist</p>
						<p><b>Observações:</b> $observacao_hist</p>
						<span class='cd-date'>$data_hist<br>às $hora_hist</span>
					</div>
				</div>
				";
			}
			echo "</section>";

			// Paginação
			$total_paginas = ceil($total_historico / $itens_por_pagina);
			if ($total_paginas > 1) {
				echo "<div class='paginacao'>";
				for ($i = 1; $i <= $total_paginas; $i++) {
					$classe = ($i == $pagina_atual) ? "pagina-ativa" : "";
					$url = "visualizar_chamado.php?pagina=visualizar_chamado&cha_id=$cha_id$autenticacao&pagina_hist=$i";
					echo "<a class='$classe' href='$url'>$i</a> ";
				}
				echo "</div>";
			}
		} else {
			echo "<br><br><br>Nenhum histórico encontrado.";
		}

		// Formulários de status e técnico
		echo "
			</div>
			<div style='display:table; width:100%;'>
				<form enctype='multipart/form-data' method='post' action='visualizar_chamado.php?pagina=visualizar_chamado&action=salvar_status&cha_id=$cha_id$autenticacao'>
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
				<form enctype='multipart/form-data' method='post' action='visualizar_chamado.php?pagina=visualizar_chamado&action=salvar_tecnico&cha_id=$cha_id$autenticacao'>
					<div class='subquadro'  style='width:45%; float:right; line-height:25px;'>
						<div class='status'>
							<p class='subtitle'><input type='button' id='bt_status' value='Adicionar Técnico' /></p>
							<div class='conteudo'>
								<select name='cha_tecnico' id='cha_tecnico'>
									<option value='$cha_tecnico'>$nome_tecnico</option>";
		foreach (buscarTecnicos($pdo) as $tecnico) {
			echo "<option value='{$tecnico['tec_id']}'>{$tecnico['tec_nome']}</option>";
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
	} else {
		echo "<div class='centro'><br><br><br>Nenhum chamado encontrado.</div>";
	}
}

include '../mod_rodape/rodape.php';
?>