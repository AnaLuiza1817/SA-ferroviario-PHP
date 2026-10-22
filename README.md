# Hyper Sense

Sistema acadêmico de gestão ferroviária desenvolvido em PHP + MySQL, com interface Bootstrap 5.

## Funcionalidades

- Login com controle de sessão e perfis (Administrador, Supervisor, Usuário).
- Dashboard com indicadores operacionais.
- CRUD de usuários (criar, editar, excluir com *soft delete* e bloqueio de exclusão quando há trens vinculados).
- CRUD de trens (criar, editar, excluir, alterar status).
- CRUD de rotas (criar, editar, excluir com bloqueio quando há trens vinculados).
- Mapa ferroviário interativo com arrastar de trens (Administrador/Supervisor).
- Gráficos de manutenções e ocorrências por trem.
- Sensores com edição de leitura/status.
- Pesquisa em usuários, trens e rotas.
- Cadeia de dependência: todo trem pertence a uma rota; rota com trens não pode ser excluída.

## Requisitos

- PHP 8.1+
- MySQL 8+
- Extensão `mysqli`

## Estrutura
SA-FERROVIARIO-PHP/
├── Banco de Dados/banco.sql
├── Css/style.css
├── infra/conexao.php
├── Js/main.js
└── Tela/*.php

text

## Instalação

1. Importe `Banco de Dados/banco.sql` no MySQL.
2. Ajuste as credenciais em `infra/conexao.php`.
3. Acesse `Tela/Login.php`.

## Perfis iniciais (senha padrão `Admin@123`)

- gabriel@gmail.com (Administrador)
- ana@gmail.com (Administrador)
- arthur@gmail.com (Administrador)
- fernanda@gmail.com (Administrador)

## Segurança

- Senhas com `password_hash` (bcrypt) e `password_verify`.
- CSRF em todos os formulários que alteram estado.
- Sessão regenerada no login e destruída no logout.
- Verificação de permissão no servidor (`requireAdmin`, `requireOperationalControl`).
- Acesso negado redireciona para a tela de login com aviso.

## Testes

Acesse `Tela/teste_seguranca.php` como Administrador para rodar as verificações automáticas.