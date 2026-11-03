# SA Hyper Sense – Sistema de Monitoramento Ferroviário Inteligente

## Sobre o projeto

A **SA Hyper Sense** é um projeto desenvolvido com o objetivo de criar um sistema de monitoramento ferroviário inteligente, buscando melhorar o acompanhamento e o controle das operações ferroviárias.

O sistema foi desenvolvido em PHP, com integração a um banco de dados, permitindo organizar informações sobre trens, rotas, sensores e usuários.

A proposta é facilitar o acesso às informações ferroviárias, contribuir para a segurança e ajudar no acompanhamento das atividades por meio de uma plataforma simples e organizada.

## Objetivo

Nosso principal objetivo é desenvolver uma solução tecnológica para o ambiente ferroviário, permitindo o gerenciamento das informações e o monitoramento dos trens.

Com o sistema, buscamos tornar o acompanhamento das operações mais prático, organizado e eficiente, utilizando a tecnologia para auxiliar na identificação de situações que precisam de atenção.

## Funcionalidades do sistema

O projeto conta com diferentes telas e recursos:

- **Login:** acesso ao sistema por meio de autenticação.
- **Cadastro:** registro de informações e usuários.
- **Cadastro de trens:** gerenciamento dos dados dos trens.
- **Rotas:** consulta e organização das rotas ferroviárias.
- **Mapa:** visualização das informações relacionadas ao percurso.
- **Sensores:** acompanhamento das informações dos sensores.
- **Gráficos:** apresentação visual de dados do sistema.
- **Edição e atualização:** alteração de informações cadastradas.
- **Exclusão:** remoção de registros.
- **Área administrativa:** gerenciamento de informações e usuários.
- **Segurança:** recursos relacionados à proteção e ao acesso ao sistema.

## Tecnologias utilizadas

Para desenvolver o projeto, utilizamos:

- PHP – programação e funcionamento do sistema.
- MySQL – armazenamento das informações.
- PDO – conexão entre PHP e banco de dados.
- HTML – estrutura das páginas.
- CSS – aparência e organização visual.
- JavaScript – funcionalidades e interações das páginas.
- XAMPP – ambiente local para executar o projeto.
- Visual Studio Code – editor utilizado durante o desenvolvimento.

## Organização dos arquivos

O projeto está organizado da seguinte maneira:

```text
SA-FERROVIARIO-PHP/
│
├── Banco de Dados/
│   └── banco.sql
│
├── Css/
│   └── style.css
│
├── infra/
│   └── conexao.php
│
├── Js/
│   └── main.js
│
├── Tela/
│   ├── atualizar.php
│   ├── auth.php
│   ├── cadastro Trem.php
│   ├── Cadastro.php
│   ├── criar_admin.php
│   ├── editar_trem.php
│   ├── editar.php
│   ├── excluir.php
│   ├── grafico.php
│   ├── index.php
│   ├── Login.php
│   ├── logout.php
│   ├── mapa.php
│   ├── menu.php
│   ├── rotas.php
│   ├── sensores.php
│   ├── termos.php
│   ├── teste_seguranca.php
│   ├── trens.php
│   └── usuario.php
│
├── PDO.MD
├── README.md
├── RNF.md
└── validacoes.RNF.MD
```

## Banco de dados

O banco de dados é utilizado para armazenar e organizar as informações necessárias para o funcionamento do sistema.

O arquivo `banco.sql` contém os comandos SQL utilizados na criação da estrutura do banco de dados.

A conexão entre o PHP e o MySQL é realizada pelo arquivo `conexao.php`, utilizando PDO.

## Como executar o projeto

Para testar o sistema no computador, siga os passos:

1. Instale o XAMPP e o Visual Studio Code.
2. Inicie o Apache e o MySQL pelo painel do XAMPP.
3. Coloque a pasta do projeto dentro da pasta `htdocs`.
4. Abra o phpMyAdmin pelo navegador.
5. Importe o arquivo `banco.sql` para criar o banco de dados.
6. Confira as configurações de conexão no arquivo `infra/conexao.php`.
7. Abra o navegador e acesse:

```text
http://localhost/SA-FERROVIARIO-PHP/Tela/
```

## Importância do projeto

O desenvolvimento da SA Hyper Sense permite colocar em prática os conhecimentos adquiridos durante o curso de Desenvolvimento de Sistemas.

Durante a construção do projeto, trabalhamos com programação PHP, banco de dados, integração entre tecnologias, organização de arquivos, segurança e desenvolvimento de funcionalidades.

Além disso, o projeto mostra como a tecnologia pode ser utilizada para ajudar no monitoramento ferroviário e na organização das informações.

## Sobre o desenvolvimento

Este projeto foi desenvolvido em equipe como parte da nossa Situação de Aprendizagem (SA), com a proposta de aplicar na prática os conhecimentos técnicos adquiridos durante as aulas.

A SA Hyper Sense representa nosso aprendizado, dedicação e desenvolvimento de habilidades na área de tecnologia e programação.

---

**SA Hyper Sense – Tecnologia e inovação para o monitoramento ferroviário.** 🚆