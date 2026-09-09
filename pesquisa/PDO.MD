O que é o PDO
Para que ele é utilizado no PHP
Como funciona uma conexão utilizando PDO



# Diferenças entre PDO e MySQLi

No PHP, existem duas principais opções para trabalhar com bancos de dados MySQL: **PDO (PHP Data Objects)** e **MySQLi (MySQL Improved)**. Ambas permitem conectar uma aplicação PHP ao MySQL, executar consultas e utilizar recursos de segurança, como *Prepared Statements*. A principal diferença é que o PDO oferece uma interface mais independente do banco de dados, enquanto o MySQLi é específico para o MySQL.

## PDO x MySQLi

| Característica                  | PDO                                           | MySQLi                             |
| ------------------------------- | --------------------------------------------- | ---------------------------------- |
| Bancos de dados                 | Suporta diferentes bancos por meio de drivers | Focado exclusivamente no MySQL     |
| Interface                       | Orientada a objetos                           | Orientada a objetos e procedural   |
| Prepared Statements             | Sim                                           | Sim                                |
| Transações                      | Sim                                           | Sim                                |
| Portabilidade                   | Maior                                         | Menor                              |
| Facilidade para trocar de banco | Mais fácil                                    | Necessita alterações maiores       |
| Recursos específicos do MySQL   | Pode não disponibilizar todos diretamente     | Maior acesso aos recursos do MySQL |

O **PDO** funciona como uma interface consistente para diferentes sistemas de banco de dados. Dessa forma, uma aplicação pode utilizar PDO e, dependendo da situação, mudar o banco utilizado com menos alterações no código. Já o **MySQLi** foi desenvolvido especificamente para trabalhar com o MySQL.

## Prepared Statements: o que são?

*Prepared Statements*, ou **instruções preparadas**, são uma forma de executar comandos SQL utilizando parâmetros separados do código da consulta.

Em vez de colocar diretamente os dados fornecidos pelo usuário dentro da consulta, utiliza-se um marcador, como `?`, e o valor é enviado separadamente.

Exemplo:

```php
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
$stmt->execute([$email]);
```

O processo ocorre, de forma simplificada, em duas etapas: primeiro a consulta é preparada e depois os valores dos parâmetros são enviados para sua execução.

### Por que são importantes?

Um dos principais motivos para utilizar Prepared Statements é a **proteção contra SQL Injection**.

SQL Injection acontece quando dados fornecidos pelo usuário acabam sendo interpretados como parte do comando SQL. Com consultas parametrizadas, o banco consegue diferenciar o **código SQL** dos **dados enviados pelo usuário**, dificultando que um valor inserido altere a intenção original da consulta. A OWASP recomenda o uso de consultas parametrizadas como uma das principais formas de prevenção contra SQL Injection.

Além da segurança, instruções preparadas podem ser vantajosas quando a mesma consulta precisa ser executada diversas vezes com parâmetros diferentes, pois a consulta pode ser preparada uma vez e reutilizada.

## Vantagens do PDO

### 1. Maior portabilidade

Uma das principais vantagens do PDO é permitir trabalhar com diferentes bancos de dados utilizando uma interface semelhante. Existem drivers PDO para diferentes sistemas, como MySQL e PostgreSQL.

### 2. Interface consistente

O PDO oferece uma API considerada mais consistente para realizar operações com bancos de dados, facilitando a organização do código.

### 3. Suporte a Prepared Statements

O PDO permite utilizar instruções preparadas, ajudando a proteger as aplicações contra SQL Injection quando utilizadas corretamente.

### 4. Suporte a diferentes bancos

Como o PDO possui drivers para diferentes bancos, pode ser uma escolha interessante em projetos que não querem ficar totalmente dependentes do MySQL.

### 5. Código orientado a objetos

O PDO utiliza uma interface orientada a objetos, o que pode contribuir para uma estrutura de código mais organizada.

## Desvantagens do PDO

### 1. Não possui todos os recursos específicos de cada banco

O PDO oferece uma interface comum, mas isso significa que nem todos os recursos específicos de um determinado banco estarão disponíveis da mesma maneira. Para utilizar funcionalidades muito específicas do MySQL, pode ser necessário recorrer a recursos próprios do banco.

### 2. Necessidade de utilizar o driver correto

O PDO não acessa diretamente o banco de dados sozinho. É necessário utilizar um driver correspondente ao banco, como o **PDO_MYSQL** para trabalhar com MySQL.

### 3. Não elimina a necessidade de boas práticas

Usar PDO não significa que uma aplicação esteja automaticamente segura. É necessário utilizar corretamente Prepared Statements, validar entradas e seguir outras práticas de segurança. A OWASP recomenda a parametrização das consultas como uma das principais defesas contra SQL Injection.

## Conclusão

PDO e MySQLi são duas opções válidas para conectar aplicações PHP ao MySQL. O **MySQLi** é específico para MySQL e oferece interfaces procedural e orientada a objetos, além de suporte a recursos específicos desse banco. Já o **PDO** oferece uma interface orientada a objetos e possui maior portabilidade entre diferentes sistemas de banco de dados.

Para projetos que precisam trabalhar especificamente com MySQL, tanto PDO quanto MySQLi podem ser utilizados. Porém, quando a portabilidade e uma interface consistente são importantes, o PDO pode ser uma escolha mais adequada.

Independentemente da tecnologia escolhida, é importante utilizar **Prepared Statements** e outras boas práticas de segurança para reduzir o risco de SQL Injection.

## Fontes de pesquisa 

1. **PHP Manual — Visão geral do MySQLi e PDO**
   [PHP: Visão Geral — MySQLi](https://www.php.net/manual/pt_BR/mysqli.overview.php?utm_source=chatgpt.com)

2. **PHP Manual — PHP Data Objects (PDO)**
   [PHP: PHP Data Objects](https://www.php.net/manual/pt_BR/book.pdo.php?utm_source=chatgpt.com)

3. **PHP Manual — Instruções Preparadas com PDO**
   [PHP: Declarações preparadas e procedimentos armazenados](https://www.php.net/manual/pt_BR/pdo.prepared-statements.php?utm_source=chatgpt.com)

4. **PHP Manual — Instruções Preparadas com MySQLi**
   [PHP: Instruções Preparadas](https://www.php.net/manual/pt_BR/mysqli.quickstart.prepared-statements.php?utm_source=chatgpt.com)

5. **OWASP — SQL Injection Prevention Cheat Sheet**
   [OWASP: SQL Injection Prevention Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/SQL_Injection_Prevention_Cheat_Sheet.html?utm_source=chatgpt.com)

6. **OWASP — Query Parameterization Cheat Sheet**
   [OWASP: Query Parameterization Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Query_Parameterization_Cheat_Sheet.html?utm_source=chatgpt.com)


