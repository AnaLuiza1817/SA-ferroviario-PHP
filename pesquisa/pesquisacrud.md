# CRUD - Pesquisa e Documentação

## CRUD01 - O que é CRUD

CRUD é uma sigla que representa quatro operações fundamentais utilizadas no gerenciamento de dados em sistemas: Create (Criar), Read (Ler), Update (Atualizar) e Delete (Excluir).

Essas operações permitem que um sistema realize o gerenciamento completo de informações armazenadas em um banco de dados.

**Evidência:**

O CRUD está presente em sistemas de cadastro, gerenciamento de usuários, produtos, clientes e outras aplicações que precisam armazenar, consultar, modificar ou remover informações.

---

## CRUD02 - Create (Criar)

O Create é a operação responsável por inserir novos registros no banco de dados. Essa funcionalidade permite que informações preenchidas pelo usuário sejam armazenadas em uma tabela.

**Evidência:**

No SQL, a operação Create do CRUD geralmente é realizada utilizando o comando `INSERT INTO`, que permite adicionar novos registros a uma tabela.

Exemplo:

```sql
INSERT INTO usuarios (nome, email)
VALUES ('Ana', 'ana@email.com');
```

---

## CRUD03 - Read (Ler)

O Read é a operação responsável por consultar, recuperar e visualizar informações que já estão armazenadas no banco de dados.

**Evidência:**

No SQL, a operação Read é realizada utilizando o comando `SELECT`, que permite recuperar registros de uma ou mais tabelas, podendo utilizar condições para filtrar os resultados.

Exemplo:

```sql
SELECT * FROM usuarios;
```

---

## CRUD04 - Update (Atualizar)

O Update é a operação responsável por modificar informações de registros que já existem no banco de dados.

**Evidência:**

No SQL, a operação Update é realizada utilizando o comando `UPDATE`, que permite alterar valores de determinados campos de uma tabela. O comando `WHERE` pode ser utilizado para identificar quais registros devem ser modificados.

Exemplo:

```sql
UPDATE usuarios
SET nome = 'Ana Luiza'
WHERE id = 1;
```

---

## CRUD05 - Delete (Excluir)

O Delete é a operação responsável por remover registros armazenados no banco de dados.

**Evidência:**

No SQL, a operação Delete é realizada utilizando o comando `DELETE`, que permite excluir registros de uma tabela. A utilização do `WHERE` é importante para especificar quais registros serão removidos.

Exemplo:

```sql
DELETE FROM usuarios
WHERE id = 1;
```

---

## CRUD06 - CRUD e SQL

As operações CRUD são relacionadas diretamente aos comandos SQL utilizados para manipular os dados de um banco de dados relacional.

**Evidência:**

Cada operação CRUD possui um comando SQL geralmente associado:

- Create: `INSERT INTO`
- Read: `SELECT`
- Update: `UPDATE`
- Delete: `DELETE`

Esses comandos permitem realizar as principais ações de gerenciamento de registros em bancos de dados relacionais.

---

## CRUD07 - CRUD utilizando PHP

O CRUD pode ser implementado utilizando PHP para conectar uma aplicação ao banco de dados e executar operações de inserção, consulta, atualização e exclusão de registros.

**Evidência:**

O PHP permite receber informações de formulários HTML, processá-las e executar comandos SQL no banco de dados, possibilitando que o sistema realize as operações CRUD por meio de suas funcionalidades.

---

## CRUD08 - CRUD em sistemas web

O CRUD é utilizado em sistemas web que precisam administrar informações de maneira organizada, permitindo que usuários realizem diferentes ações de acordo com as permissões disponíveis.

**Evidência:**

Em sistemas web, o CRUD pode ser utilizado em funcionalidades como cadastro de usuários, gerenciamento de produtos, controle de registros, consultas, edição de informações e exclusão de dados.

---

## CRUD09 - Segurança nas operações CRUD

As operações CRUD precisam ser implementadas com cuidados de segurança para evitar alterações indevidas, exclusões acidentais e ataques que comprometam o banco de dados.

**Evidência:**

A utilização de consultas preparadas, validação de dados, controle de permissões e verificação das informações recebidas pelo servidor contribui para a segurança das operações CRUD.

Além disso, operações de exclusão e atualização devem identificar corretamente os registros que serão afetados, evitando alterações ou remoções indevidas.

---

## CRUD10 - Importância do CRUD

O CRUD é importante porque organiza as principais funcionalidades de manipulação de dados de um sistema, facilitando o desenvolvimento, a manutenção e a compreensão das aplicações.

**Evidência:**

A maioria dos sistemas que trabalha com informações armazenadas utiliza, de alguma forma, as operações de criação, leitura, atualização e exclusão, tornando o CRUD um conceito fundamental no desenvolvimento de sistemas.

---

## CRUD11 - Fontes

- PHP Politécnico - Operações CRUD com PHP e MySQL: https://aperezn298.github.io/PHPPolitecnico/pages/bd/crud.html
- Learning PHP - Working with Databases: https://learningphp.org/lessons/08-working-with-databases
- PHP - Documentação oficial: https://www.php.net/manual/pt_BR/
- MariaDB - Documentação oficial: https://mariadb.org/
