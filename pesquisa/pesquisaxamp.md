# XAMPP - Pesquisa e Documentação

## XAMPP01 - Servidor local

O XAMPP é um pacote de ferramentas que permite executar um servidor web localmente no computador, facilitando o desenvolvimento e os testes de sistemas web.

**Evidência:**

O XAMPP reúne ferramentas como Apache, MariaDB, PHP e Perl, permitindo executar aplicações web sem a necessidade de disponibilizá-las na internet.

---

## XAMPP02 - Apache

O Apache é o servidor web responsável por receber as requisições realizadas pelo navegador e disponibilizar os arquivos do sistema.

**Evidência:**

No XAMPP, o Apache é iniciado pelo painel de controle. Quando está funcionando, permite acessar projetos locais por meio do endereço `localhost`.

---

## XAMPP03 - MariaDB

O MariaDB é um sistema de gerenciamento de banco de dados relacional utilizado para armazenar, organizar e consultar informações.

**Evidência:**

O MariaDB permite que o sistema armazene informações, como usuários, cadastros e registros, por meio de comandos SQL.

---

## XAMPP04 - PHP

O PHP é uma linguagem de programação utilizada no desenvolvimento de aplicações web. Seu código é executado no servidor, permitindo gerar páginas dinâmicas e realizar operações com bancos de dados.

**Evidência:**

Os arquivos PHP são processados pelo servidor local, possibilitando funcionalidades como cadastro, login, consultas, alterações e exclusões de registros.

---

## XAMPP05 - phpMyAdmin

O phpMyAdmin é uma ferramenta que permite administrar bancos de dados MySQL ou MariaDB por meio de uma interface gráfica acessada pelo navegador.

**Evidência:**

Por meio do phpMyAdmin, é possível criar bancos de dados, tabelas, inserir informações, executar comandos SQL e visualizar registros.

---

## XAMPP06 - Localhost

O localhost é o endereço utilizado para acessar serviços hospedados no próprio computador.

**Evidência:**

Quando o Apache está iniciado, o endereço `http://localhost` permite acessar o servidor local, possibilitando a execução e os testes de projetos web.

---

## XAMPP07 - Pasta htdocs

A pasta `htdocs` é o diretório utilizado pelo Apache no XAMPP para armazenar os arquivos dos projetos que podem ser acessados pelo servidor local.

**Evidência:**

Os projetos precisam estar dentro da pasta `htdocs` para serem acessados por meio do localhost, conforme a configuração padrão do XAMPP.

Exemplo de localização:

`C:\xampp\htdocs\Sistema`

---

## XAMPP08 - Integração com VS Code

O Visual Studio Code é utilizado para escrever, editar e organizar os arquivos do projeto, enquanto o XAMPP disponibiliza o ambiente de servidor local para executar aplicações PHP.

**Evidência:**

O projeto pode ser editado pelo VS Code e executado pelo Apache do XAMPP, permitindo que o desenvolvedor teste as funcionalidades pelo navegador.

---

## XAMPP09 - Execução do projeto

Para executar um projeto PHP utilizando o XAMPP, é necessário iniciar o Apache, armazenar o projeto na pasta `htdocs` e acessar o endereço correspondente pelo navegador.

**Evidência:**

Após iniciar o Apache pelo painel de controle do XAMPP, o sistema pode ser acessado utilizando o endereço `http://localhost/nome-do-projeto`.

---

## XAMPP10 - Utilização do banco de dados

O XAMPP permite integrar aplicações PHP com bancos de dados MariaDB, possibilitando armazenar e manipular informações utilizadas pelo sistema.

**Evidência:**

A aplicação pode utilizar conexões com o banco de dados para realizar operações de inserção, consulta, atualização e exclusão de informações, utilizando comandos SQL.

---

## XAMPP11 - Segurança

O XAMPP é voltado principalmente para ambientes de desenvolvimento e testes. Sua configuração padrão não deve ser considerada adequada para exposição direta em servidores públicos.

**Evidência:**

A utilização em ambientes de produção exige configurações de segurança, controle de acesso, proteção de dados e ajustes no servidor.

---

## XAMPP12 - Fontes

- Apache Friends: https://www.apachefriends.org/pt_br/index.html
- PHP: https://www.php.net/manual/pt_BR/
- Visual Studio Code: https://code.visualstudio.com/docs/languages/php
- MariaDB: https://mariadb.org/
