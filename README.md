# 📄 Documentação Técnica – Atualização para PHP 8

## 🏢 Sistema de Gestão de Condomínios

Este documento descreve as alterações realizadas no sistema de gestão de condomínios durante o processo de atualização da versão PHP 5 para PHP 8.

---

## 🎯 Objetivo

Atualizar o sistema legado para PHP 8.x, garantindo total compatibilidade com as versões mais recentes da linguagem, bibliotecas utilizadas e boas práticas de segurança, sem alterar a estrutura lógica e visual do sistema.

---

## ⚙️ Tecnologias Utilizadas

- **PHP 8.x**
- **PDO (PHP Data Objects)** – conexão com banco de dados
- **mPDF** – biblioteca de geração de PDFs (versão mais recente)
- **PHPMailer** - atualizado pelo composer (versão mais recente)
- **HTML / CSS / JavaScript**
- **jQuery**

---

## ✅ Alterações Realizadas

### 🔐 Segurança e Conectividade

- Substituição total da extensão `mysql_*` (removida no PHP 7) por **PDO**.
- Implementação de prepared statements para proteger contra **SQL Injection**.
- Uso de `htmlspecialchars`, `filter_input` e validações básicas nos formulários.

```php
$sql_par = 'SELECT * FROM parametros_gerais
            LEFT JOIN end_uf ON end_uf.uf_id = parametros_gerais.ger_uf
            LEFT JOIN end_municipios ON end_municipios.mun_id = parametros_gerais.ger_municipio';
$stmt = $pdo->prepare($sql_par);
$stmt->execute();
$parametros = $stmt->fetch(PDO::FETCH_ASSOC);
```

```php
$titulo = htmlspecialchars($parametros['ger_nome'] ?? '', ENT_QUOTES, 'UTF-8');
```

### 🛠️ Compatibilidade com PHP 8

- Remoção de funções obsoletas (`create_function`, `each`, etc.).
- Correções em chamadas de função com parâmetros não obrigatórios.
- Adoção de operadores modernos como `??` e `??=`.
- Atualização da manipulação de arrays, objetos e chamadas estáticas.

### 📄 Geração de PDFs

- Atualização da biblioteca **mPDF** para a versão mais recente.
- Refatoração de chamadas mPDF para aderência à nova API.
- Testes e validação de geração de boletos, recibos e relatórios.

---

## Revisão do armazenamento de senhas

* As senhas, antes armazenadas em md5 (não recomendáavel), agora são armazenadas utilizando BCRYPT.
* Usuários poderão logar com suas senhas normalmente e o sistema as atualizará para a nova criptografia após o primeiro login com o hash antigo.
* A atualização foi feita tanto para clientes como para administradores.

```php
// Verifica senha: primeiro pelo hash seguro, depois pelo antigo
$senhaCorreta = false;
if (password_verify($senha, $user['usu_senha'])) {
	$senhaCorreta = true;
} elseif ($user['usu_senha'] === md5($senha)) {
	$senhaCorreta = true;
	// Atualiza para hash seguro
	$novoHash = password_hash($senha, PASSWORD_DEFAULT);
	$update = $pdo->prepare("UPDATE admin_usuarios SET usu_senha = :hash WHERE usu_id = :id");
	$update->bindValue(':hash', $novoHash);
	$update->bindValue(':id', $user['usu_id'], PDO::PARAM_INT);
	$update->execute();
}

```
---

## 📌 Observações Finais

- O sistema **não utiliza o padrão MVC**, e sua estrutura modular foi mantida conforme o legado.
- A migração para frameworks modernos (como **Laravel** ou **CodeIgniter**) poderá ser considerada em futuras fases do projeto para facilitar manutenção, escalabilidade e testes.
- Sugiro um esquema de revisão e deleção de imagens e planilhas muito antigas de tempos em tempos para diminuir o tamanho da pasta e acelerar o carregamento do sistema.
- As pastas de imagens e planilhas não foram upadas por limitações de armazenamento, portanto não foram alteradas.

---

## 👩‍💻 Desenvolvedora Responsável

**Gabriella Ferreira Guerra**  
Backend Developer – Especialista em Laravel  
📧 Email: gabriellafguerra21@gmail.com
