# Validações de Requisitos Não Funcionais (RNF)

## Projeto: Hyper Sense — Sistema Ferroviário (SA-FERROVIARIO-PHP)

**Disciplina:** Análise e Desenvolvimento de Sistemas
**Data:** Setembro/2026
**Responsável técnico pela documentação:** Equipe Hyper Sense
**Versão:** 1.1

---

## 1. Objetivo

Este documento registra, de forma organizada e rastreável, os **Requisitos Não Funcionais (RNFs)** do sistema **Hyper Sense**, bem como a **estratégia de validação** aplicada a cada um, o **resultado obtido** nos testes e a **evidência** que comprova a conformidade.

Os RNFs foram agrupados em cinco categorias:

* **Segurança**
* **Validação de dados**
* **Desempenho**
* **Confiabilidade**
* **Usabilidade**

O formato adotado para cada requisito é:

> **Requisito → Regra esperada → Estratégia de validação → Resultado → Evidência**

---

## 2. Estrutura do Projeto (referência das evidências)

```text
SA-FERROVIARIO-PHP/
├── Banco de Dados/
│   └── banco.sql
├── Css/
│   └── style.css
├── infra/
│   └── conexao.php
├── Js/
│   └── main.js
├── Tela/
│   ├── auth.php
│   ├── Cadastro.php
│   ├── CadastroPublico.php
│   ├── criar_admin.php
│   ├── editar.php
│   ├── excluir.php
│   ├── grafico.php
│   ├── index.php
│   ├── Login.php
│   ├── logout.php
│   ├── mapa.php
│   ├── sensores.php
│   ├── teste_seguranca.php
│   ├── trens.php
│   └── usuario.php
├── PDO.MD
└── RNF.md
```

---

## 3. RNFs — Segurança

### RNF-SEG-01 — Senhas armazenadas com hash

* **Requisito:** Nenhuma senha pode ser armazenada em texto plano no banco de dados.

* **Regra esperada:** Toda senha criada ou alterada deve passar por `password_hash()` com `PASSWORD_DEFAULT` (bcrypt) antes de ser persistida; o login deve usar `password_verify()`.

* **Estratégia de validação:**

  1. Inspecionar `Cadastro.php`, `CadastroPublico.php` e `criar_admin.php` procurando `password_hash(`.
  2. Inspecionar `Login.php` procurando `password_verify(`.
  3. Consultar a coluna `senha` na tabela `usuarios` no MySQL e verificar que os valores começam com `$2y$`.
  4. Teste funcional: criar um usuário e tentar logar com a senha correta (deve funcionar) e com senha errada (deve falhar).

* **Resultado:** **Aprovado**

* **Evidência:**

  * `Tela/Cadastro.php` → `$senhaHash = password_hash($senha, PASSWORD_DEFAULT);`
  * `Tela/CadastroPublico.php` → mesma instrução.
  * `Tela/criar_admin.php` → mesma instrução.
  * `Tela/Login.php` → `if ($dados && !empty($dados["senha"]) && password_verify($senha, $dados["senha"]))`
  * Saída SQL:

```sql
SELECT email, senha FROM usuarios;

-- Exemplo:
-- $2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W
```

---

### RNF-SEG-02 — Bloqueio de acesso a páginas restritas

* **Requisito:** Páginas internas só podem ser acessadas por usuários autenticados.

* **Regra esperada:** Toda tela interna deve chamar `requireLogin()` no topo antes de qualquer saída HTML.

* **Estratégia de validação:**

  1. Verificar via leitura se cada arquivo interno contém `requireLogin();`.
  2. Teste manual: deslogar e tentar acessar `index.php`, `usuario.php`, `trens.php`, `mapa.php`, `grafico.php`, `sensores.php` diretamente pela URL.
  3. O sistema deve redirecionar para `Login.php`.
  4. Teste automatizado: `Tela/teste_seguranca.php` faz essa varredura por leitura dos arquivos.

* **Resultado:** **Aprovado**

* **Evidência:**

  * `Tela/auth.php` → função `requireLogin()` que redireciona para `Login.php` quando a sessão está vazia.
  * `Tela/teste_seguranca.php` → bloco:

```php
$arquivosLogin = [
    'index.php',
    'usuario.php',
    'trens.php',
    'mapa.php',
    'grafico.php',
    'sensores.php'
];

// ...

str_contains($conteudo, 'requireLogin();');
```

* Todos os 6 arquivos passam no teste.

---

### RNF-SEG-03 — Restrição administrativa (RBAC)

* **Requisito:** Somente usuários com perfil `Administrador` podem executar operações sensíveis (criar, editar, excluir usuários, criar admin).

* **Regra esperada:** Tais arquivos devem chamar `requireAdmin()` e validar `$_SESSION['usuario_tipo'] === 'Administrador'`.

* **Estratégia de validação:**

  1. Inspecionar `Cadastro.php`, `editar.php`, `excluir.php` e `criar_admin.php`.
  2. Teste manual: logar como `Usuário` e tentar acessar as URLs diretamente → deve retornar **403 Acesso negado**.
  3. Teste automatizado em `teste_seguranca.php`.

* **Resultado:** **Aprovado**

* **Evidência:**

  * `Tela/auth.php` → função `requireAdmin()` que retorna `http_response_code(403); exit('Acesso negado.');`
  * `Tela/teste_seguranca.php` → bloco:

```php
$arquivosAdmin = [
    'Cadastro.php',
    'editar.php',
    'excluir.php',
    'criar_admin.php'
];
```

* Todos os arquivos passam no teste.

---

### RNF-SEG-04 — Proteção CSRF em formulários administrativos

* **Requisito:** Formulários que alteram estado (cadastro, edição, exclusão, criação de admin) devem ser protegidos por token CSRF.

* **Regra esperada:** Token gerado no servidor (`csrfToken()`) e validado (`validarCsrf()`) em todo POST.

* **Estratégia de validação:**

  1. Verificar presença de `csrf_token` no HTML dos formulários.
  2. Verificar presença de `validarCsrf();` no topo do processamento POST.
  3. Teste manual: remover o campo `csrf_token` via DevTools e enviar o POST → deve responder **403 Requisição inválida**.

* **Resultado:** **Aprovado**

* **Evidência:**

  * `Tela/auth.php`:

```php
function csrfToken(): string
{
    /* gera bin2hex(random_bytes(32)) */
}

function validarCsrf(): void
{
    if (
        !$token ||
        empty($_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $token)
    ) {
        http_response_code(403);
        exit('Requisição inválida.');
    }
}
```

* `Tela/Cadastro.php`, `editar.php`, `excluir.php`, `criar_admin.php` → contêm `validarCsrf();` e:

```html
<input type="hidden" name="csrf_token" ...>
```

---

### RNF-SEG-05 — Bloqueio do último administrador ativo

* **Requisito:** O sistema não pode ficar sem nenhum administrador ativo.

* **Regra esperada:** A exclusão do último admin ativo deve ser impedida.

* **Estratégia de validação:**

  1. Inspecionar `excluir.php`.
  2. Teste manual: com apenas 1 admin ativo, tentar excluí-lo → deve retornar a mensagem `O último administrador ativo não pode ser excluído.`

* **Resultado:** **Aprovado**

* **Evidência:**

```php
if ($usuario['tipo'] === 'Administrador') {
    $resultadoAdmins = $conexao->query(
        "SELECT COUNT(*) AS total
         FROM usuarios
         WHERE tipo = 'Administrador'
         AND status = 'Ativo'"
    );

    $totalAdmins = ...;

    if ($totalAdmins <= 1) {
        $erro = 'O último administrador ativo não pode ser excluído.';
    }
}
```

---

### RNF-SEG-06 — Regeneração de sessão no login

* **Requisito:** Prevenir *session fixation*.
* **Regra esperada:** Ao autenticar, o PHP deve regenerar o ID da sessão.
* **Estratégia de validação:** Inspeção do `Login.php` procurando `session_regenerate_id(true)`.
* **Resultado:** **Aprovado**
* **Evidência:** `Tela/Login.php` → `session_regenerate_id(true);` imediatamente antes de gravar `$_SESSION`.

---

### RNF-SEG-07 — Logout seguro

* **Requisito:** O logout deve limpar a sessão e o cookie de sessão.

* **Regra esperada:** `logout.php` deve limpar `$_SESSION`, apagar o cookie e destruir a sessão.

* **Estratégia de validação:** Leitura do arquivo e teste manual (após logout, botão "voltar" não deve reabrir a sessão).

* **Resultado:** **Aprovado**

* **Evidência:**

  * `Tela/logout.php`:

```php
$_SESSION = [];

setcookie(
    session_name(),
    "",
    time() - 42000,
    ...
);

session_destroy();

header("Location: Login.php");
```

---

### RNF-SEG-08 — Perfil "Administrador" definido apenas no servidor

* **Requisito:** O usuário não pode se autopromover a Administrador por meio do formulário público.

* **Regra esperada:** `CadastroPublico.php` deve fixar `'Usuário'`; `criar_admin.php` (protegido por `requireAdmin()`) é o único ponto que define `'Administrador'`; `Cadastro.php` (uso interno, também protegido) tem suas próprias regras.

* **Estratégia de validação:**

  1. Inspecionar `CadastroPublico.php` — tipo fixo `'Usuário'` direto no `INSERT`.
  2. Inspecionar `criar_admin.php` — `$tipo = "Administrador";` fixo no servidor, sem input do usuário.
  3. Teste manual: abrir `CadastroPublico.php`, injetar via DevTools um campo `tipo=Administrador` e enviar o POST → o sistema ignora o campo e cria o usuário com `tipo='Usuário'`.
  4. Teste manual de confirmação: consultar `SELECT id, nome, email, tipo FROM usuarios ORDER BY id DESC LIMIT 1;` no MySQL → confirmar `tipo = 'Usuário'`.

* **Resultado:** **Aprovado** (validado em teste manual pela equipe)

* **Evidência:**

`Tela/CadastroPublico.php`:

```php
$stmt = $conexao->prepare(
    "INSERT INTO usuarios (nome, email, telefone, tipo, status, senha)
     VALUES (?, ?, ?, 'Usuário', 'Ativo', ?)"
);
```

O valor `'Usuário'` está **literal na string SQL** — nada do `$_POST` é usado para definir o tipo.

`Tela/criar_admin.php`:

```php
$tipo = "Administrador";
$status = "Ativo";
$senhaHash = password_hash($senha, PASSWORD_DEFAULT);
```

**Print do teste manual:** anexar imagem mostrando o campo `tipo=Administrador` injetado no DevTools e a resposta do sistema criando o usuário como `Usuário`.

**Saída SQL do teste:**

```sql
SELECT id, nome, email, tipo
FROM usuarios
ORDER BY id DESC
LIMIT 1;

-- Exemplo:
-- +----+----------+------------------+---------+
-- | id | nome     | email            | tipo    |
-- +----+----------+------------------+---------+
-- | 12 | Teste X  | teste@teste.com  | Usuário |
-- +----+----------+------------------+---------+
```

---

## 4. RNFs — Validação de Dados

### RNF-VAL-01 — Campos obrigatórios no cadastro

* **Requisito:** Nome, e-mail, telefone e senha são obrigatórios.

* **Regra esperada:** Envio com qualquer campo vazio deve retornar erro e não persistir no banco.

* **Estratégia de validação:**

  1. Validação client-side: `main.js` → `inicializarValidacaoFormularios()` com `required` no HTML.
  2. Validação server-side: `Cadastro.php` e `CadastroPublico.php` verificam `trim(...) === ''`.
  3. Teste manual: submeter o formulário vazio → deve exibir lista de erros.

* **Resultado:** **Aprovado**

* **Evidência:**

  * `Js/main.js` → `formulario.checkValidity()` + classe `was-validated`.
  * `Tela/CadastroPublico.php` → `if ($nome === '') $erros[] = 'Informe seu nome completo.';` (idem para e-mail, telefone e senha).
  * `Tela/Cadastro.php` → mesma estrutura de validação.

---

### RNF-VAL-02 — Formato de e-mail

* **Requisito:** E-mails devem ser sintaticamente válidos.
* **Regra esperada:** Uso de `filter_var($email, FILTER_VALIDATE_EMAIL)`.
* **Estratégia de validação:** Inspeção e teste manual enviando `teste@` e `teste` → devem ser rejeitados.
* **Resultado:** **Aprovado**
* **Evidência:** `Cadastro.php`, `CadastroPublico.php`, `editar.php`, `criar_admin.php` → `!filter_var($email, FILTER_VALIDATE_EMAIL)`.

---

### RNF-VAL-03 — Força mínima da senha

* **Requisito:** Senha com no mínimo 6 caracteres.

* **Regra esperada:** Bloqueio client-side (`minlength="6"`) e server-side (`strlen($senha) < 6`).

* **Estratégia de validação:** Teste manual enviando senha de 3 caracteres → deve ser rejeitada.

* **Resultado:** **Aprovado**

* **Evidência:**

  * HTML:

```html
<input
    type="password"
    minlength="6"
    required
>
```

* PHP:

```php
if (strlen($senha) < 6) {
    $erros[] = 'A senha deve ter pelo menos 6 caracteres.';
}
```

---

### RNF-VAL-04 — Confirmação de senha

* **Requisito:** `senha` e `confirmar_senha` devem ser iguais.
* **Regra esperada:** Comparação server-side.
* **Estratégia de validação:** Teste manual com senhas divergentes → deve retornar `As senhas não coincidem.`
* **Resultado:** **Aprovado**
* **Evidência:** `CadastroPublico.php` e `criar_admin.php` →:

```php
if ($senha !== $confirmarSenha) {
    $erros[] = 'As senhas não coincidem.';
}
```

---

### RNF-VAL-05 — E-mail único

* **Requisito:** Não podem existir dois usuários com o mesmo e-mail.

* **Regra esperada:** Verificação prévia via `SELECT` e tratamento do erro MySQL 1062 como *"Este e-mail já está cadastrado."*

* **Estratégia de validação:**

  1. Inspeção.
  2. Teste manual: cadastrar o mesmo e-mail duas vezes → deve retornar erro amigável.

* **Resultado:** **Aprovado**

* **Evidência:**

  * `CadastroPublico.php` → `SELECT id FROM usuarios WHERE email = ? LIMIT 1` antes do INSERT.
  * `Cadastro.php`, `editar.php`, `criar_admin.php` → `$stmt->errno === 1062 ? 'Este e-mail já está cadastrado...'`.

---

### RNF-VAL-06 — Sanitização de saída (XSS)

* **Requisito:** Nenhum dado vindo do banco pode ser impresso sem escape.

* **Regra esperada:** Uso de `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')` em todas as saídas dinâmicas; no JS, função `escaparHtml()`.

* **Estratégia de validação:**

  1. Inspeção das telas `usuario.php`, `trens.php`, `sensores.php`, `mapa.php`, `grafico.php`.
  2. Teste manual: cadastrar usuário com nome `<script>alert(1)</script>` → deve ser exibido como texto, sem executar.

* **Resultado:** **Aprovado**

* **Evidência:**

  * `Tela/usuario.php` → `htmlspecialchars($usuario["nome"], ENT_QUOTES, "UTF-8")`.
  * `Tela/sensores.php` → função local `e($valor)` que encapsula `htmlspecialchars`.
  * `Js/main.js` → função `escaparHtml(valor)` que substitui `& < > ' "`.

---

### RNF-VAL-07 — Validação de tipos permitidos

* **Requisito:** `tipo` e `status` só podem assumir valores pré-definidos.

* **Regra esperada:** Uso de `in_array($valor, $permitidos, true)`.

* **Estratégia de validação:** Inspeção + teste manual alterando o `<option>` via DevTools.

* **Resultado:** **Aprovado**

* **Evidência:**

  * `Cadastro.php` → `$statusPermitidos = ['Ativo', 'Inativo'];`
  * `editar.php` → `if (!in_array($usuario['status'], $statusPermitidos, true)) $erros[] = ...`
  * `trens.php` → `$permitidos = ['Em operação', 'Parado', 'Em manutenção', 'Atrasado'];`

---

### RNF-VAL-08 — Validação de ID via filtro

* **Requisito:** Parâmetros `id` vindos da URL devem ser inteiros positivos.
* **Regra esperada:** Uso de `filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT)`.
* **Estratégia de validação:** Teste manual acessando `editar.php?id=abc` → deve redirecionar para `usuario.php?erro=id_invalido`.
* **Resultado:** **Aprovado**
* **Evidência:** `Tela/editar.php` e `Tela/excluir.php`:

```php
$id = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$id || $id <= 0) {
    header('Location: usuario.php?erro=id_invalido');
    exit;
}
```

---

## 5. RNFs — Desempenho

### RNF-DES-01 — Consultas com `prepared statements`

* **Requisito:** Consultas com parâmetros do usuário devem usar *prepared statements*.

* **Regra esperada:** Uso de `$conexao->prepare(...)` + `bind_param(...)`.

* **Estratégia de validação:** Inspeção de `Login.php`, `Cadastro.php`, `editar.php`, `excluir.php`, `criar_admin.php`, `sensores.php`, `trens.php`.

* **Resultado:** **Aprovado**

* **Evidência:** Exemplo em `Login.php`:

```php
$stmt = $conexao->prepare(
    "SELECT id, nome, email, senha, tipo, status
     FROM usuarios
     WHERE (email = ? OR nome = ?)
     AND status = 'Ativo'
     LIMIT 1"
);

$stmt->bind_param(
    "ss",
    $usuario,
    $usuario
);
```

---

### RNF-DES-02 — Uso de índices e chaves únicas

* **Requisito:** Colunas de busca frequente devem ter índice; identificadores únicos devem ter `UNIQUE`.

* **Regra esperada:** `email` (no app) validado como único via lógica; `codigo` com `UNIQUE` em `estacoes`, `rotas`, `trechos`, `trens`, `sensores`, `amvs`.

* **Estratégia de validação:** Inspeção do `banco.sql` + `SHOW INDEX FROM ...` no MySQL.

* **Resultado:** **Aprovado**

* **Evidência:** `Banco de Dados/banco.sql` → `codigo VARCHAR(20) NOT NULL UNIQUE` em todas as tabelas citadas.

> **Melhoria recomendada:** adicionar `UNIQUE` também em `usuarios.email` (hoje garantido somente via lógica).

---

### RNF-DES-03 — Paginação e ordenação no DataTables

* **Requisito:** A listagem de usuários não deve carregar tudo de uma vez para o usuário final (paginação client-side).

* **Regra esperada:** `pageLength: 5` + `responsive: true`.

* **Estratégia de validação:** Inspeção do `main.js` + teste visual.

* **Resultado:** **Aprovado**

* **Evidência:** `Js/main.js`:

```javascript
window.jQuery('#tabelaUsuarios').DataTable({
    language: {
        url: '.../pt-BR.json'
    },
    pageLength: 5,
    responsive: true,
    order: [[0, 'asc']]
});
```

---

### RNF-DES-04 — Redução de requisições com `json_encode` inline

* **Requisito:** O mapa e os gráficos não devem fazer requisições AJAX extras para carregar dados.

* **Regra esperada:** Dados injetados no HTML via `window.dadosMapa = <?= json_encode(...) ?>`.

* **Estratégia de validação:** Inspeção de `mapa.php` e `grafico.php`.

* **Resultado:** **Aprovado**

* **Evidência:**

  * `Tela/mapa.php` → `window.dadosMapa = { estacoes: <?= json_encode($estacoes, ...) ?>, ... }`
  * `Tela/grafico.php` → `window.dadosManutencoes`, `window.dadosOcorrencias`, `window.detalhesTrens`, `window.mesesGrafico`.

---

### RNF-DES-05 — Animações respeitam `prefers-reduced-motion`

* **Requisito:** O sistema deve respeitar preferências de acessibilidade do usuário para reduzir movimento.

* **Regra esperada:** CSS com `@media (prefers-reduced-motion: reduce)` e JS verificando `matchMedia('(prefers-reduced-motion: reduce)')`.

* **Estratégia de validação:** Inspeção do `style.css` e `main.js`.

* **Resultado:** **Aprovado**

* **Evidência:**

```css
@media (prefers-reduced-motion: reduce) {
    .stat-card,
    .resource-card,
    .trecho-linha {
        transition: none;
    }
}
```

```javascript
const reduzirMovimento =
    window.matchMedia(
        '(prefers-reduced-motion: reduce)'
    ).matches;
```

---

## 6. RNFs — Confiabilidade

### RNF-CON-01 — Tratamento de falha de conexão

* **Requisito:** Se o MySQL estiver indisponível, o sistema não deve expor stack traces.

* **Regra esperada:** Mensagem amigável e interrupção controlada.

* **Estratégia de validação:** Inspeção + teste manual parando o MySQL e acessando `Login.php`.

* **Resultado:** **Aprovado** (mensagem simples, embora possa ser melhorada)

* **Evidência:** `infra/conexao.php`:

```php
if ($conexao->connect_error) {
    die("Erro na conexão: " . $conexao->connect_error);
}
```

> **Melhoria recomendada:** substituir `die()` por log interno + mensagem genérica ao usuário, evitando vazar detalhes do host.

---

### RNF-CON-02 — Consistência transacional em operações compostas

* **Requisito:** Operações que afetam várias tabelas devem ser consistentes.

* **Regra esperada:** Uso de `ON DELETE SET NULL` / `ON DELETE CASCADE` para manter integridade referencial.

* **Estratégia de validação:** Inspeção do `banco.sql` + teste manual excluindo uma estação e verificando os trechos.

* **Resultado:** **Aprovado**

* **Evidência:** `Banco de Dados/banco.sql`:

```sql
CONSTRAINT fk_trem_trecho
FOREIGN KEY (trecho_id)
REFERENCES trechos(id)
ON DELETE SET NULL,

CONSTRAINT fk_rota_trecho_rota
FOREIGN KEY (rota_id)
REFERENCES rotas(id)
ON DELETE CASCADE
```

---

### RNF-CON-03 — Idempotência do script de banco

* **Requisito:** O script `banco.sql` pode ser executado mais de uma vez sem gerar erro.

* **Regra esperada:** `CREATE TABLE IF NOT EXISTS`, `INSERT IGNORE`, `INSERT ... WHERE NOT EXISTS`.

* **Estratégia de validação:** Executar o script duas vezes seguidas e verificar ausência de erros.

* **Resultado:** **Aprovado**

* **Evidência:**

  * `CREATE TABLE IF NOT EXISTS usuarios (...)` e demais tabelas.
  * `INSERT IGNORE INTO estacoes ...`
  * Bloco:

```sql
INSERT INTO usuarios ...
WHERE NOT EXISTS (
    SELECT 1
    FROM usuarios
    WHERE LOWER(email) = '...'
);
```

* Usuários citados no script: Gabriel, Ana, Arthur e **Fernanda**.

---

### RNF-CON-04 — Integridade referencial entre tabelas

* **Requisito:** Não podem existir trechos apontando para estações inexistentes, trens apontando para trechos inexistentes, etc.

* **Regra esperada:** FKs declaradas em todas as relações.

* **Estratégia de validação:** Inspeção + `SHOW CREATE TABLE trechos;` no MySQL.

* **Resultado:** **Aprovado**

* **Evidência:** `Banco de Dados/banco.sql`:

```sql
FOREIGN KEY (origem_id)
REFERENCES estacoes(id),

FOREIGN KEY (destino_id)
REFERENCES estacoes(id)
```

---

### RNF-CON-05 — Teste automatizado de segurança

* **Requisito:** Deve existir uma página que execute testes automáticos de segurança de forma auditável.

* **Regra esperada:** `teste_seguranca.php` (acessível apenas a admin) percorrendo os arquivos e reportando PASSOU/FALHOU.

* **Estratégia de validação:** Acessar `teste_seguranca.php` logado como admin e conferir o relatório.

* **Resultado:** **Aprovado**

* **Evidência:** `Tela/teste_seguranca.php` percorre:

  * `index.php`
  * `usuario.php`
  * `trens.php`
  * `mapa.php`
  * `grafico.php`
  * `sensores.php`
  * `Cadastro.php`
  * `editar.php`
  * `excluir.php`
  * `criar_admin.php`
  * `Login.php`
  * coluna `senha` do banco.

---

## 7. RNFs — Usabilidade

### RNF-USA-01 — Layout responsivo

* **Requisito:** O sistema deve funcionar em desktop, tablet e celular.

* **Regra esperada:** Bootstrap 5 + media queries próprias para telas < 768px.

* **Estratégia de validação:** Teste manual redimensionando a janela / DevTools → dispositivo móvel.

* **Resultado:** **Aprovado**

* **Evidência:** `Css/style.css`:

```css
@media (max-width: 767.98px) {
    .dashboard-hero-icon {
        font-size: 5rem;
    }

    .chart-container,
    .chart-container-small {
        height: 280px;
    }

    .mapa-viewport {
        min-height: 360px;
        height: 55vh;
    }

    .page-header {
        border-radius: 0 0 20px 20px;
    }
}
```

---

### RNF-USA-02 — Feedback visual de status

* **Requisito:** Status (trem, sensor, trecho) devem ser visualmente distintos.

* **Regra esperada:** Badges e classes CSS semânticas (verde = ok, vermelho = crítico, amarelo = atenção, roxo = manutenção).

* **Estratégia de validação:** Inspeção + teste visual.

* **Resultado:** **Aprovado**

* **Evidência:**

  * `Css/style.css` → `.badge-status-em-operacao`, `.badge-status-parado`, `.badge-status-manutencao`, `.badge-status-atrasado`, `.status-normal`, `.status-atencao`, `.status-em-manutencao`, `.status-bloqueado`.
  * `Tela/trens.php` → função `classeBadgeTrem($status)`.

---

### RNF-USA-03 — Acessibilidade do mapa (teclado + ARIA)

* **Requisito:** O mapa ferroviário deve ter rótulo acessível.

* **Regra esperada:** `role="img"` + `aria-label` no `<svg>`.

* **Estratégia de validação:** Inspeção.

* **Resultado:** **Aprovado**

* **Evidência:** `Tela/mapa.php`:

```html
<svg
    id="mapa-ferroviario"
    viewBox="0 0 1040 430"
    role="img"
    aria-label="Mapa ferroviário interativo"
>
```

---

### RNF-USA-04 — Mensagens de erro orientativas

* **Requisito:** Erros devem ser listados de forma clara e agrupada.

* **Regra esperada:** `alert alert-danger` com `<ul>` de itens.

* **Estratégia de validação:** Teste manual enviando formulário inválido.

* **Resultado:** **Aprovado**

* **Evidência:** `Cadastro.php`, `CadastroPublico.php`, `criar_admin.php` → bloco:

```php
<?php if ($erros): ?>
    <div class="alert alert-danger">
        <ul>
            ...
        </ul>
    </div>
<?php endif; ?>
```

---

### RNF-USA-05 — Auto-ocultar mensagens de sucesso

* **Requisito:** Alertas de sucesso somem sozinhos para não poluir a tela.

* **Regra esperada:** `data-auto-hide="true"` + timeout no JS.

* **Estratégia de validação:** Inspeção + teste manual.

* **Resultado:** **Aprovado**

* **Evidência:** `Js/main.js` → `ocultarMensagensAutomaticamente()` com `window.setTimeout(..., 4000)`.

---

### RNF-USA-06 — Máscara de telefone

* **Requisito:** O campo de telefone deve ser formatado automaticamente.

* **Regra esperada:** `(XX) XXXXX-XXXX`.

* **Estratégia de validação:** Digitar `47999991111` → deve virar `(47) 99999-1111`.

* **Resultado:** **Aprovado**

* **Evidência:** `Js/main.js` → `inicializarMascaraTelefone()` com regex:

```javascript
/^(\d{2})(\d)/
/^(\d{5})(\d)/
```

---

## 8. Tabela-resumo das validações

| ID         | Categoria      | Requisito                 | Resultado | Evidência principal                       |
| ---------- | -------------- | ------------------------- | --------- | ----------------------------------------- |
| RNF-SEG-01 | Segurança      | Senhas com hash           | ✅         | `Cadastro.php`, `Login.php`, `banco.sql`  |
| RNF-SEG-02 | Segurança      | Bloqueio de URL direta    | ✅         | `auth.php` (`requireLogin`)               |
| RNF-SEG-03 | Segurança      | RBAC (admin)              | ✅         | `auth.php` (`requireAdmin`)               |
| RNF-SEG-04 | Segurança      | Proteção CSRF             | ✅         | `auth.php` + forms                        |
| RNF-SEG-05 | Segurança      | Último admin ativo        | ✅         | `excluir.php`                             |
| RNF-SEG-06 | Segurança      | `session_regenerate_id`   | ✅         | `Login.php`                               |
| RNF-SEG-07 | Segurança      | Logout seguro             | ✅         | `logout.php`                              |
| RNF-SEG-08 | Segurança      | Admin só no servidor      | ✅         | `CadastroPublico.php` + `criar_admin.php` |
| RNF-VAL-01 | Validação      | Campos obrigatórios       | ✅         | `main.js`, `Cadastro*.php`                |
| RNF-VAL-02 | Validação      | Formato de e-mail         | ✅         | `filter_var(..., FILTER_VALIDATE_EMAIL)`  |
| RNF-VAL-03 | Validação      | Senha ≥ 6 caracteres      | ✅         | HTML + PHP                                |
| RNF-VAL-04 | Validação      | Confirmação de senha      | ✅         | `CadastroPublico.php`, `criar_admin.php`  |
| RNF-VAL-05 | Validação      | E-mail único              | ✅         | `SELECT` + tratamento 1062                |
| RNF-VAL-06 | Validação      | Sanitização XSS           | ✅         | `htmlspecialchars()` + `escaparHtml()`    |
| RNF-VAL-07 | Validação      | Tipos permitidos          | ✅         | `in_array()`                              |
| RNF-VAL-08 | Validação      | Validação de ID           | ✅         | `filter_input()`                          |
| RNF-DES-01 | Desempenho     | Prepared Statements       | ✅         | `prepare()` + `bind_param()`              |
| RNF-DES-02 | Desempenho     | Índices e UNIQUE          | ✅         | `banco.sql`                               |
| RNF-DES-03 | Desempenho     | Paginação DataTables      | ✅         | `main.js`                                 |
| RNF-DES-04 | Desempenho     | `json_encode` inline      | ✅         | `mapa.php`, `grafico.php`                 |
| RNF-DES-05 | Desempenho     | Reduced Motion            | ✅         | `style.css`, `main.js`                    |
| RNF-CON-01 | Confiabilidade | Falha de conexão          | ✅         | `infra/conexao.php`                       |
| RNF-CON-02 | Confiabilidade | Integridade referencial   | ✅         | `banco.sql`                               |
| RNF-CON-03 | Confiabilidade | Idempotência do banco     | ✅         | `banco.sql`                               |
| RNF-CON-04 | Confiabilidade | Integridade entre tabelas | ✅         | Foreign Keys                              |
| RNF-CON-05 | Confiabilidade | Teste automatizado        | ✅         | `teste_seguranca.php`                     |
| RNF-USA-01 | Usabilidade    | Layout responsivo         | ✅         | `style.css`                               |
| RNF-USA-02 | Usabilidade    | Feedback de status        | ✅         | `style.css`, `trens.php`                  |
| RNF-USA-03 | Usabilidade    | Acessibilidade do mapa    | ✅         | `mapa.php`                                |
| RNF-USA-04 | Usabilidade    | Mensagens de erro         | ✅         | `Cadastro.php`, `CadastroPublico.php`     |
| RNF-USA-05 | Usabilidade    | Auto-hide de mensagens    | ✅         | `main.js`                                 |
| RNF-USA-06 | Usabilidade    | Máscara de telefone       | ✅         | `main.js`                                 |

