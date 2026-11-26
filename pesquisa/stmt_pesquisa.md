# O que é $stmt no PHP?

O `$stmt` é uma variável muito utilizada em PHP quando trabalhamos com banco de dados.

O nome vem da palavra *Statement*, que significa instrução ou comando.

Normalmente, essa variável guarda uma consulta preparada para ser executada no banco de dados.

## Para que serve?

O `$stmt` serve para preparar e executar comandos SQL, como consultar, cadastrar, atualizar e excluir informações.

Ele é bastante utilizado junto com o PDO, que é uma ferramenta do PHP para trabalhar com bancos de dados.

## Como funciona?


```php
$sql = "SELECT * FROM usuarios";

$stmt = $pdo->prepare($sql);

$stmt->execute();

$resultado = $stmt->fetchAll();
```

Entendendo cada linha:

- `$sql`: guarda o comando SQL que queremos executar.
- `$stmt`: recebe a consulta preparada.
- `prepare()`: prepara o comando para execução.
- `execute()`: executa o comando no banco.
- `fetchAll()`: pega todos os resultados encontrados.

## Exemplo com parâmetros

Também podemos utilizar o `$stmt` para buscar informações específicas.

```php
$id = 1;

$sql = "SELECT * FROM usuarios WHERE id = :id";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':id' => $id
]);

$usuario = $stmt->fetch();
```

Nesse exemplo, estamos procurando um usuário pelo seu ID.

O `:id` é um espaço reservado que recebe o valor da variável `$id`.

## Principais comandos utilizados com $stmt

- `execute()`: executa a consulta.
- `fetch()`: busca um registro.
- `fetchAll()`: busca todos os registros.
- `fetchColumn()`: busca o valor de uma coluna.
- `rowCount()`: informa a quantidade de linhas afetadas pela última operação, com algumas limitações dependendo do banco.

## Por que utilizar $stmt?

Uma das principais vantagens é a segurança.

Quando utilizamos consultas preparadas com parâmetros, ajudamos a evitar ataques chamados SQL Injection.

Esse tipo de ataque acontece quando alguém tenta inserir comandos maliciosos em campos de um sistema para manipular o banco de dados.

Por isso, é recomendado utilizar `prepare()` e `execute()` quando trabalhamos com informações recebidas dos usuários.

## Qual a diferença entre $pdo e $stmt?

A diferença é simples:

- `$pdo` representa a conexão com o banco de dados.
- `$stmt` representa a instrução preparada que será executada nessa conexão.

Exemplo:

```php
$stmt = $pdo->prepare($sql);
```

Nesse código, o PDO prepara a consulta e devolve um objeto que fica armazenado na variável `$stmt`.

## Conclusão

O `$stmt` é uma variável que facilita o trabalho com consultas no banco de dados.

Ele permite preparar comandos SQL, executar consultas e recuperar informações.

É muito comum encontrar essa variável em projetos PHP que utilizam PDO e MySQL.

## Fontes de pesquisa

- https://www.php.net/manual/pt_BR/pdo.prepare.php
- https://www.php.net/manual/pt_BR/pdostatement.execute.php
- https://www.php.net/manual/pt_BR/class.pdostatement.php
- https://www.php.net/manual/pt_BR/pdo.prepared-statements.php