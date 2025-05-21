# Análise de Redundância no Banco de Dados `sistemae_sistema`

**Data:** 21/05/2025   
**Fonte:** Dump SQL do phpMyAdmin   
**Desenvolvedora responsável:** Gabriella Ferreira Guerra   

## Redundâncias Encontradas

- **Endereços Duplicados:** As tabelas `cadastro_clientes`, `cadastro_fornecedores` e `parametros_gerais` repetem campos de endereço (CEP, UF, município, bairro, endereço, número, complemento).
- **Logs de Login Separados:** Existem duas tabelas de log (`admin_log_login` e `cliente_log_login`) com a mesma estrutura, apenas separando tipos de usuário.
- **Campos Semelhantes:** Campos como nomes de usuários e status aparecem em várias tabelas.
- **Tabela parametros_gerais é desnecessária:** A tabela carrega apenas um registo estático, que pode ser codada diretamente nas páginas como HTML, sendo assim renderizada via navegador, garantindo melhor performance ao servidor e funcionando melhor em conexões mais

## Propostas de Melhoria

- **Centralizar Endereços:** Criar uma tabela única de endereços e usar chaves estrangeiras nas tabelas de cadastro, eliminando colunas duplicadas.
- **Unificar Logs de Login:** Criar uma tabela única de logs com um campo para o tipo de usuário, substituindo as tabelas atuais.
- **Aproveitar Tabelas Existentes:** Usar as tabelas `end_bairros` e `end_municipios` via a tabela central de endereços, evitando duplicidade.

Essas mudanças tornam o banco mais simples, eficiente e fácil de manter, além de melhorar a performance do sistema.
