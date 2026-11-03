# SA Hyper Sense – Pesquisa sobre APIs

## 1. Introdução

Neste trabalho, nossa equipe irá apresentar o que são APIs, como elas funcionam e qual é sua importância na comunicação entre diferentes sistemas.

As APIs estão presentes em várias aplicações que utilizamos no dia a dia e também em sistemas empresariais. Elas permitem que diferentes programas compartilhem informações sem precisar ter acesso direto ao código ou ao banco de dados uns dos outros.

Na nossa SA Hyper Sense, que tem como proposta desenvolver um sistema de monitoramento ferroviário, esse conhecimento é importante para entender como as informações dos trens, sensores e rotas poderiam ser compartilhadas com outros sistemas.

## 2. O que é uma API e qual é sua finalidade?

API significa *Application Programming Interface*, que em português significa Interface de Programação de Aplicações.

Uma API é um meio de comunicação que permite que dois ou mais sistemas troquem informações e utilizem funcionalidades uns dos outros.

Sua principal finalidade é facilitar a integração entre aplicações, evitando que cada sistema precise desenvolver todas as funcionalidades sozinho.

Por exemplo, um sistema ferroviário pode utilizar uma API para receber informações de sensores, consultar dados de localização dos trens ou compartilhar informações sobre as rotas.

## 3. Como funciona a comunicação entre sistemas por meio de APIs?

A comunicação acontece quando um sistema envia uma solicitação para outro sistema por meio de uma API.

O sistema que solicita as informações é chamado de cliente, enquanto o sistema que recebe a solicitação e fornece os dados é chamado de servidor.

O funcionamento acontece da seguinte maneira:

1. O sistema cliente envia uma requisição para a API.
2. A API recebe e verifica a solicitação.
3. O servidor processa o pedido.
4. O servidor envia uma resposta.
5. O sistema cliente recebe e utiliza as informações.

Dessa forma, os sistemas conseguem trabalhar juntos mesmo que tenham sido desenvolvidos com linguagens de programação diferentes.

## 4. O que são APIs REST?

REST significa *Representational State Transfer*. É um estilo de arquitetura utilizado para desenvolver APIs que permitem a comunicação entre sistemas pela internet.

As APIs REST normalmente utilizam o protocolo HTTP e trabalham com recursos, como usuários, produtos, trens e rotas.

Uma característica importante é que cada requisição deve conter as informações necessárias para ser entendida pelo servidor, sem depender de informações temporárias de uma requisição anterior.

No nosso projeto, poderíamos utilizar uma API REST para disponibilizar informações sobre os trens e permitir que outros sistemas consultassem esses dados.

## 5. O papel dos métodos HTTP

Os métodos HTTP indicam qual ação o sistema deseja realizar em determinado recurso.

Os principais métodos são:

### GET

É utilizado para consultar ou buscar informações, sem alterar os dados cadastrados.

Exemplo:

```http
GET /api/trens
```

Essa requisição solicita a lista de trens cadastrados.

### POST

É utilizado para enviar informações e cadastrar um novo registro.

Exemplo:

```http
POST /api/trens
```

Essa requisição pode ser utilizada para cadastrar um novo trem.

### PUT

É utilizado para substituir completamente as informações de um registro existente.

Exemplo:

```http
PUT /api/trens/1
```

Essa requisição atualiza todos os dados do trem de identificação 1.

### PATCH

É utilizado para alterar apenas uma parte das informações de um registro.

Exemplo:

```http
PATCH /api/trens/1
```

Essa requisição poderia alterar somente a situação de um trem.

### DELETE

É utilizado para excluir um registro.

Exemplo:

```http
DELETE /api/trens/1
```

Essa requisição solicita a exclusão do trem de identificação 1.

## 6. O que são requisições e respostas HTTP?

As requisições HTTP são mensagens enviadas pelo cliente para solicitar alguma informação ou realizar uma ação em um servidor.

Já as respostas HTTP são mensagens devolvidas pelo servidor, informando se a solicitação foi realizada corretamente ou se aconteceu algum erro.

Uma requisição pode conter:

- Método HTTP utilizado.
- Endereço do recurso.
- Cabeçalhos (headers).
- Corpo da mensagem (body), quando necessário.

Uma resposta pode conter:

- Código de status.
- Cabeçalhos.
- Corpo com os dados solicitados ou uma mensagem de retorno.

Alguns códigos de resposta são:

- **200 OK:** solicitação realizada com sucesso.
- **201 Created:** registro criado com sucesso.
- **400 Bad Request:** solicitação enviada incorretamente.
- **401 Unauthorized:** falta de autenticação válida.
- **403 Forbidden:** acesso não permitido.
- **404 Not Found:** recurso não encontrado.
- **500 Internal Server Error:** erro interno no servidor.

## 7. O que são endpoints?

Endpoints são os endereços específicos disponibilizados por uma API para permitir o acesso a determinados recursos ou funcionalidades.

Cada endpoint representa um caminho pelo qual outro sistema pode realizar uma solicitação.

Exemplos relacionados à nossa SA Hyper Sense:

```text
/api/trens
/api/rotas
/api/sensores
/api/usuarios
```

Cada endereço pode ter diferentes funções, dependendo do método HTTP utilizado.

Por exemplo, o endpoint `/api/trens` pode ser utilizado com GET para consultar os trens e com POST para cadastrar um novo trem.

## 8. Como funciona a troca de dados no formato JSON?

JSON significa *JavaScript Object Notation*. É um formato utilizado para organizar e transmitir informações entre sistemas.

Ele é bastante utilizado em APIs porque possui uma estrutura simples, fácil de entender e compatível com várias linguagens de programação.

Exemplo de dados JSON relacionados à Hyper Sense:

```json
{
    "id": 1,
    "nome": "Trem 01",
    "rota": "Joinville - São Francisco do Sul",
    "status": "Em operação",
    "velocidade": 60,
    "sensor_ativo": true
}
```

Nesse exemplo, as informações representam um trem, sua rota, situação, velocidade e condição do sensor.

O JSON permite que outro sistema receba essas informações e consiga interpretá-las.

## 9. Diferenças entre uma API e um sistema web tradicional

Apesar de trabalharem com comunicação pela internet, uma API e um sistema web tradicional possuem finalidades diferentes.

**Sistema web tradicional:**

- É desenvolvido principalmente para interação com usuários.
- Apresenta páginas com textos, imagens, botões e formulários.
- Normalmente utiliza HTML, CSS e JavaScript.
- O usuário acessa as informações pelo navegador.

**API:**

- É desenvolvida para permitir a comunicação entre sistemas.
- Disponibiliza informações e funcionalidades.
- Geralmente envia e recebe dados em JSON.
- Pode ser utilizada por sites, aplicativos e outros sistemas.

Um exemplo seria um sistema ferroviário que apresenta um painel visual para os funcionários e, ao mesmo tempo, possui uma API para compartilhar os dados dos trens com outras aplicações.

## 10. Como uma aplicação PHP pode consumir uma API?

Uma aplicação PHP pode consumir uma API utilizando recursos como cURL, que permite realizar requisições HTTP para outros servidores.

Com isso, o PHP consegue buscar informações externas e utilizá-las dentro do próprio sistema.

Exemplo prático em PHP:

```php
<?php

// Endereço da API
$url = "https://exemplo.com/api/trens";

// Inicia a conexão
$ch = curl_init($url);

// Configura a requisição
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Executa a solicitação
$resposta = curl_exec($ch);

// Verifica se ocorreu algum erro
if ($resposta === false) {
    echo "Erro ao consultar a API.";
} else {

    // Converte o JSON em dados PHP
    $dados = json_decode($resposta, true);

    // Exibe as informações
    echo "<pre>";
    print_r($dados);
    echo "</pre>";
}

// Fecha a conexão
curl_close($ch);

?>
```

Nesse exemplo, o PHP acessa um endereço de API, recebe uma resposta e transforma os dados JSON em um array PHP.

O endereço utilizado é apenas ilustrativo e precisa ser substituído pelo endereço de uma API existente.

## 11. Como uma API pode disponibilizar informações para outros sistemas?

Uma API também pode funcionar como uma fonte de informações para outras aplicações.

Para isso, o sistema disponibiliza endpoints que permitem consultar ou enviar dados, de acordo com as permissões definidas.

Por exemplo, na SA Hyper Sense, poderíamos disponibilizar um endpoint para consultar os dados dos trens.

Exemplo de requisição:

```http
GET /api/trens/1
```

Exemplo de resposta:

```json
{
    "id": 1,
    "nome": "Trem 01",
    "rota": "Joinville - São Francisco do Sul",
    "status": "Em operação",
    "velocidade": 60
}
```

Assim, outro sistema poderia receber essas informações e apresentá-las em seu próprio painel de monitoramento.

É importante lembrar que esse exemplo representa uma possibilidade de integração para o projeto, não significa que esse endpoint já esteja implementado.

## 12. Importância da autenticação, autorização e segurança

A segurança é muito importante quando trabalhamos com APIs, principalmente quando elas disponibilizam informações importantes ou permitem alterações nos dados.

Existem dois conceitos principais:

**Autenticação:** é o processo utilizado para confirmar a identidade de quem está tentando acessar o sistema.

**Autorização:** é responsável por verificar quais informações e funcionalidades aquela pessoa ou aplicação pode acessar.

Algumas medidas importantes de segurança são:

- Utilizar HTTPS para proteger a comunicação.
- Utilizar tokens de acesso quando necessário.
- Criar permissões diferentes para cada tipo de usuário.
- Validar as informações recebidas.
- Não deixar senhas e chaves de acesso expostas no código.
- Impedir acessos não autorizados.
- Registrar atividades importantes para facilitar o controle.

Na nossa SA Hyper Sense, esses cuidados são importantes para proteger informações relacionadas aos trens, usuários, sensores e operações ferroviárias.

## 13. Exemplos de utilização de APIs em sistemas empresariais

As APIs são utilizadas em diversas áreas e ajudam as empresas a integrar seus sistemas.

Alguns exemplos são:

**Bancos:** utilizam APIs para consultar informações financeiras, realizar pagamentos e integrar serviços.

**Comércio eletrônico:** utilizam APIs para consultar produtos, calcular fretes e acompanhar pedidos.

**Hospitais:** podem utilizar APIs para compartilhar informações entre sistemas de atendimento, agendamento e exames, respeitando as permissões de acesso.

**Transportes:** podem utilizar APIs para compartilhar informações sobre localização, horários, rotas e condições de veículos.

**Empresas ferroviárias:** podem utilizar APIs para integrar sistemas de monitoramento, sensores, controle de trens e gerenciamento de rotas.

No caso da Hyper Sense, uma API poderia facilitar a comunicação entre o sistema ferroviário e outras plataformas, permitindo que informações importantes fossem consultadas de maneira organizada.

## 14. Exemplo prático dos métodos HTTP na SA Hyper Sense

Para entender melhor como os métodos HTTP poderiam ser utilizados no nosso projeto, apresentamos alguns exemplos:

| Método | Exemplo | Finalidade |
|---|---|---|
| GET | /api/trens | Consultar trens |
| POST | /api/trens | Cadastrar trem |
| PUT | /api/trens/1 | Atualizar todos os dados |
| PATCH | /api/trens/1 | Alterar parte dos dados |
| DELETE | /api/trens/1 | Excluir trem |

Esses exemplos mostram como uma API poderia ajudar a integrar as funcionalidades da nossa aplicação com outros sistemas.

## 15. Conclusão

Com essa pesquisa, nossa equipe conseguiu compreender melhor o funcionamento das APIs e sua importância para a comunicação entre sistemas.

Aprendemos que elas permitem compartilhar informações, integrar aplicações e facilitar diferentes processos dentro de uma empresa.

Também entendemos a função dos métodos HTTP, dos endpoints, das requisições, das respostas e do formato JSON.

Relacionando o conteúdo com a SA Hyper Sense, percebemos que as APIs poderiam contribuir para a integração das informações ferroviárias, permitindo que dados sobre trens, rotas e sensores fossem compartilhados com outras aplicações.

Dessa forma, o estudo das APIs amplia nossos conhecimentos em Desenvolvimento de Sistemas e mostra como podemos utilizar a programação para desenvolver soluções tecnológicas cada vez mais completas.

## 16. Fontes consultadas

As fontes abaixo foram utilizadas como referência para compreender os conceitos apresentados na pesquisa. Demos preferência à documentação oficial e a materiais técnicos reconhecidos.

1. **MDN Web Docs – Métodos de requisição HTTP.**  
   https://developer.mozilla.org/pt-BR/docs/Web/HTTP/Reference/Methods

2. **MDN Web Docs – Mensagens HTTP.**  
   https://developer.mozilla.org/en-US/docs/Web/HTTP/Guides/Messages

3. **PHP – Manual oficial da linguagem.**  
   https://www.php.net/manual/pt_BR/

4. **PHP – Exemplos básicos de cURL.**  
   https://www.php.net/manual/en/curl.examples-basic.php

5. **OWASP – REST Security Cheat Sheet.**  
   https://cheatsheetseries.owasp.org/cheatsheets/REST_Security_Cheat_Sheet.html

6. **Roy Fielding – Architectural Styles and the Design of Network-based Software Architectures.**  
   https://ics.uci.edu/~fielding/pubs/dissertation/abstract.htm

---

**SA Hyper Sense – Sistema de Monitoramento Ferroviário Inteligente** 
*Desenvolvido pela equipe como parte da Situação de Aprendizagem do curso de Desenvolvimento de Sistemas.*