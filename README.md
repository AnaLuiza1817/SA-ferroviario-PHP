# SA Hyper Sense – Sistema de Monitoramento Ferroviário

## 1. Apresentação do Projeto

A SA Hyper Sense é um projeto desenvolvido como parte da Situação de Aprendizagem (SA) do curso Técnico em Desenvolvimento de Sistemas.

O projeto tem como proposta desenvolver um sistema de monitoramento ferroviário, utilizando recursos tecnológicos para auxiliar no gerenciamento de informações relacionadas aos trens, rotas, sensores e usuários.

A aplicação busca oferecer uma plataforma organizada e de fácil utilização, permitindo o acesso e o gerenciamento das informações necessárias para o acompanhamento das atividades ferroviárias.

Além do desenvolvimento do sistema, a equipe também trabalha com conceitos de programação, banco de dados, segurança da informação, integração entre sistemas e documentação técnica.

## 2. Objetivo Geral

Desenvolver uma aplicação web voltada ao monitoramento ferroviário, utilizando tecnologias de desenvolvimento de sistemas para organizar informações, facilitar o gerenciamento dos dados e contribuir para o acompanhamento das operações.

## 3. Objetivos Específicos

- Desenvolver uma aplicação utilizando PHP.
- Implementar a conexão com banco de dados MySQL.
- Permitir o cadastro, a edição, a consulta e a exclusão de informações.
- Organizar os dados relacionados aos trens e às rotas.
- Disponibilizar telas para acompanhamento de sensores.
- Desenvolver recursos de visualização de informações por meio de gráficos e mapas.
- Trabalhar com autenticação e controle de acesso.
- Aplicar conceitos de segurança e boas práticas de programação.
- Estudar a integração entre sistemas por meio de APIs.

## 4. Funcionalidades do Sistema

O sistema Hyper Sense possui uma estrutura dividida em diferentes áreas, cada uma responsável por determinadas operações.

### Gerenciamento de usuários
- Cadastro de usuários.
- Autenticação para acesso ao sistema.
- Controle de acesso.
- Gerenciamento de informações dos usuários.

### Gerenciamento ferroviário
- Cadastro e consulta de trens.
- Edição e atualização de informações.
- Exclusão de registros.
- Organização das informações relacionadas às rotas.

### Monitoramento
- Área destinada às informações dos sensores.
- Visualização de informações por meio de gráficos.
- Tela de mapa para apresentação das informações ferroviárias.

### Segurança
- Autenticação de usuários.
- Verificação de acesso.
- Testes relacionados à segurança.
- Validação das informações recebidas pelo sistema.

## 5. Tecnologias Utilizadas

Durante o desenvolvimento da SA Hyper Sense, são utilizadas as seguintes tecnologias:

- **PHP:** linguagem responsável pelo processamento e funcionamento da aplicação.
- **MySQL:** sistema de gerenciamento do banco de dados.
- **PDO:** recurso utilizado para realizar a conexão entre PHP e banco de dados.
- **HTML:** estruturação das páginas.
- **CSS:** estilização e organização visual da aplicação.
- **JavaScript:** criação de interações e funcionalidades nas páginas.
- **XAMPP:** ambiente utilizado para executar o projeto localmente.
- **Visual Studio Code:** editor de código utilizado no desenvolvimento.
- **Git e GitHub:** ferramentas utilizadas para versionamento e armazenamento do projeto.

## 6. Estrutura do Projeto

O projeto está organizado em pastas para facilitar a localização dos arquivos e a manutenção do sistema.

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
├── pesquisa/
│   ├── apis.md
│   └── PDO.MD
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
├── README.md
├── RNF.md
└── validacoes.RNF.MD
```

## 7. Banco de Dados

O projeto utiliza o MySQL para armazenar e organizar as informações necessárias para o funcionamento da aplicação.

O arquivo `banco.sql`, localizado na pasta Banco de Dados, contém os comandos responsáveis pela criação e organização da estrutura do banco.

A conexão entre a aplicação PHP e o banco de dados é realizada pelo arquivo `conexao.php`, localizado na pasta `infra`.

Para essa comunicação, utilizamos PDO, que permite executar consultas e operações no banco de dados de maneira organizada e segura.

## 8. Pesquisas e Documentação Técnica

Além da implementação do sistema, a equipe desenvolve pesquisas para aprofundar os conhecimentos utilizados no projeto.

Na pasta `pesquisa`, estão organizados os materiais referentes aos seguintes assuntos:

**APIs**

Estudo sobre interfaces de programação de aplicações, comunicação entre sistemas, APIs REST, métodos HTTP, endpoints, requisições, respostas e troca de informações no formato JSON.

**PDO**

Pesquisa sobre PHP Data Objects, sua finalidade, funcionamento, conexão com bancos de dados e utilização nas operações de uma aplicação PHP.

Esses conhecimentos contribuem para compreender melhor a integração, a organização e a segurança dos sistemas desenvolvidos.

## 9. Requisitos Não Funcionais e Segurança

O projeto também considera aspectos relacionados à qualidade e à segurança da aplicação.

O arquivo `RNF.md` apresenta a documentação dos requisitos não funcionais, enquanto `validacoes.RNF.MD` reúne informações relacionadas às validações desses requisitos.

Entre os aspectos considerados estão:

- Segurança no acesso ao sistema.
- Organização das informações.
- Facilidade de utilização.
- Validação dos dados.
- Confiabilidade das operações.
- Manutenção e organização do código.

## 10. Como Executar o Projeto

Para executar a aplicação localmente, é necessário ter o XAMPP e o Visual Studio Code instalados.

Siga os passos:

1. Abra o XAMPP.
2. Inicie os serviços Apache e MySQL.
3. Coloque a pasta `SA-FERROVIARIO-PHP` dentro do diretório `htdocs`.
4. Acesse o phpMyAdmin pelo navegador.
5. Importe o arquivo `banco.sql` para configurar o banco de dados.
6. Abra o projeto no Visual Studio Code.
7. Verifique as configurações de conexão no arquivo `infra/conexao.php`.
8. Abra o navegador e acesse:

```text
http://localhost/SA-FERROVIARIO-PHP/Tela/
```

Após realizar essas etapas, a aplicação poderá ser acessada pelo navegador.

## 11. Considerações Finais

O desenvolvimento da SA Hyper Sense representa uma oportunidade de colocar em prática os conhecimentos adquiridos durante o curso Técnico em Desenvolvimento de Sistemas.

Ao longo do projeto, trabalhamos com diferentes tecnologias, organização de arquivos, banco de dados, programação, segurança e documentação.

A proposta também permite compreender como os sistemas podem auxiliar no gerenciamento e no monitoramento de informações ferroviárias, mostrando a importância da tecnologia na criação de soluções para situações reais.

O trabalho em equipe contribui para a troca de conhecimentos, a resolução de problemas e o desenvolvimento de novas habilidades na área de tecnologia.

## 12. Equipe

Projeto desenvolvido pelos integrantes da equipe como parte da Situação de Aprendizagem do curso Técnico em Desenvolvimento de Sistemas.

**Projeto:** SA Hyper Sense  
**Área:** Desenvolvimento de Sistemas  
**Tema:** Monitoramento Ferroviário

---

**SA Hyper Sense – Tecnologia aplicada ao monitoramento ferroviário.**