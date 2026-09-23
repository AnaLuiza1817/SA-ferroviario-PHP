# RNF — Requisitos Não Funcionais

## RNF01 — Controle de acesso

O sistema deve controlar o acesso de acordo com o perfil do usuário.

**Evidência:**
Foram implementados diferentes níveis de acesso: Administrador, Supervisor e Usuário.

* Administrador possui acesso completo à área de usuários.
* Supervisor pode visualizar os usuários, mas não pode cadastrar, editar ou excluir.
* Usuário comum não possui acesso ao menu de usuários.

---

## RNF02 — Segurança no cadastro

O cadastro público não permite que o usuário escolha o perfil de Administrador ou Supervisor.

**Evidência:**
No formulário de cadastro realizado pela tela de login, o perfil é definido automaticamente como Usuário. A opção de Administrador e Supervisor fica disponível somente para usuários autorizados na área administrativa.

---

## RNF03 — Proteção contra alteração pelo navegador

O sistema deve impedir que um usuário altere seu nível de acesso utilizando ferramentas do navegador, como o DevTools.

**Evidência:**
As permissões são verificadas no servidor. Mesmo que o usuário tente alterar campos do formulário ou parâmetros enviados pelo navegador, o sistema verifica novamente o nível de acesso antes de executar a operação.

---

## RNF04 — Bloqueio de acesso direto por URL

Páginas administrativas não devem ficar disponíveis apenas por esconder links no menu.

**Evidência:**
As páginas administrativas possuem verificação de sessão e permissão. Caso um usuário sem autorização tente acessar diretamente uma URL administrativa, o acesso é bloqueado.

---

## RNF05 — Proteção das operações de usuários

As operações de cadastro, edição e exclusão devem respeitar o nível de acesso.

**Evidência:**
Administradores podem cadastrar, editar e excluir usuários. Supervisores podem somente visualizar. Usuários comuns não possuem acesso à área administrativa.

---

## RNF06 — Autenticação

O sistema deve exigir autenticação para acessar as áreas protegidas.

**Evidência:**
Foi implementado controle de sessão no login. Usuários não autenticados são impedidos de acessar páginas protegidas diretamente.

---

## RNF07 — Segurança das senhas

As senhas dos usuários não devem ser armazenadas diretamente em texto simples.

**Evidência:**
As senhas são armazenadas utilizando hash de senha e verificadas durante o login através do mecanismo de autenticação do sistema.

---

## RNF08 — Proteção contra CSRF

Operações administrativas devem possuir proteção contra requisições não autorizadas.

**Evidência:**
Os formulários administrativos utilizam token de segurança para validar as requisições antes de executar operações de cadastro, edição ou exclusão.

---

## RNF09 — Interface de acordo com o perfil

O sistema deve apresentar somente as opções permitidas para cada tipo de usuário.

**Evidência:**
O menu superior é montado de acordo com o perfil autenticado. Administradores e Supervisores podem visualizar o menu de Usuários, enquanto usuários comuns não possuem essa opção.

---

## RNF10 — Funcionamento do menu

O menu de navegação deve permanecer consistente durante a utilização do sistema.

**Evidência:**
Foi corrigido o problema em que o menu "Usuários" aparecia somente após acessar a página de Sensores. Atualmente, a opção é exibida corretamente desde o carregamento da página quando o usuário possui permissão.

---

## RNF11 — Testes de segurança

O sistema deve possuir testes para verificar as restrições de acesso.

**Evidência:**
Foi criado o arquivo `testes_seguranca.php`, utilizado para verificar situações como acesso sem login, tentativa de acesso por URL direta e permissões de acordo com o perfil.

---

## RNF12 — Administradores iniciais

O sistema deve possuir usuários administradores previamente cadastrados para utilização inicial.

**Evidência:**
Foram cadastrados três administradores iniciais:

* Gabriel
* Arthur
* Ana

Os três possuem acesso administrativo ao sistema.

---

## RNF13 — Diferenciação de permissões

O sistema deve diferenciar as ações permitidas para cada perfil.

**Evidência:**

| Perfil        | Visualizar Usuários | Cadastrar | Editar | Excluir |
| ------------- | ------------------- | --------- | ------ | ------- |
| Administrador | Sim                 | Sim       | Sim    | Sim     |
| Supervisor    | Sim                 | Não       | Não    | Não     |
| Usuário       | Não                 | Não       | Não    | Não     |

---

## RNF14 — Restrição de criação de administradores

Somente usuários com permissão administrativa podem criar novos administradores.

**Evidência:**
A opção de selecionar o perfil Administrador está disponível somente na área administrativa. O cadastro público não permite a criação de administradores.

---

## RNF15 — Manutenção das funcionalidades existentes

As alterações de segurança não devem prejudicar as funcionalidades já existentes no sistema.

**Evidência:**
As funcionalidades existentes foram mantidas, sendo adicionadas apenas as verificações necessárias de autenticação, autorização e segurança.
