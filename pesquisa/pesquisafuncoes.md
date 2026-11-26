1. Funções de texto e caracteres

1. trim() – Remove espaços em branco do início e do final de um texto.

2. strtoupper() – Transforma todas as letras de um texto em maiúsculas.

3. ucfirst() – Transforma a primeira letra de um texto em maiúscula.

4. strlen() – Conta a quantidade de caracteres de um texto.

5. mb_strlen() – Conta a quantidade de caracteres considerando corretamente letras com acentos e caracteres especiais.

6. htmlspecialchars() – Converte caracteres especiais em códigos HTML, ajudando a proteger o sistema contra ataques de scripts.

7. implode() – Junta vários elementos de um array em uma única string, utilizando um separador.

2. Funções de validação e verificação

8. preg_match() – Verifica se um texto corresponde a um padrão específico, utilizando expressões regulares.

9. in_array() – Verifica se determinado valor existe dentro de um array.

10. empty() – Verifica se uma variável está vazia ou não possui um valor considerado válido.

11. is_numeric() – Verifica se um valor é numérico.

12. array_filter() – Filtra os elementos de um array, mantendo aqueles que atendem a uma condição.

13. hash_equals() – Compara dois valores de forma segura, sendo utilizada para verificar códigos de segurança.

14. password_verify() – Verifica se uma senha informada corresponde à senha criptografada armazenada no sistema.

3. Funções de entrada e saída de dados

15. filter_input() – Recebe dados de entradas externas, como formulários, permitindo aplicar filtros de validação.

16. json_encode() – Converte dados do PHP para o formato JSON, facilitando a comunicação com JavaScript.

17. array_merge() – Junta dois ou mais arrays em um único array.

18. array_push() – Adiciona um ou mais elementos ao final de um array.

19. echo – Exibe informações na tela ou envia conteúdo como resposta ao navegador.

20. header() – Envia cabeçalhos HTTP, podendo redirecionar o usuário para outra página.

21. http_response_code() – Define ou consulta o código de resposta HTTP, como 200, 404 e 500.

4. Funções de data e horário

22. date() – Formata e apresenta datas e horários.

23. time() – Retorna o horário atual em formato de timestamp.

24. strtotime() – Converte uma data ou horário escrito em texto para um timestamp.

25. filemtime() – Retorna a data da última modificação de um arquivo. No projeto, também pode ser utilizada para controlar a atualização de arquivos.

5. Funções de sessão e segurança

26. session_status() – Verifica a situação atual da sessão do usuário.

27. session_set_cookie_params() – Configura os parâmetros de segurança e duração do cookie da sessão.

28. ini_set() – Altera configurações do PHP durante a execução do sistema.

29. unset() – Remove uma variável ou um elemento de um array.

30. bin2hex() – Converte dados binários em uma representação hexadecimal.

31. session_regenerate_id() – Gera um novo identificador para a sessão, ajudando a aumentar a segurança do acesso.

6. Outras funções:

32. isset() – Verifica se uma variável foi definida e possui um valor diferente de null.

33. count() – Conta a quantidade de elementos de um array.

34. password_hash() – Cria um hash seguro para armazenar senhas sem guardar o texto original.

35. random_bytes() – Gera dados aleatórios utilizados em recursos de segurança.

36. session_start() – Inicia ou recupera uma sessão do usuário.

37. session_destroy() – Encerra a sessão atual.

38. filter_var() – Filtra ou valida dados, como endereços de e-mail.

39. strtolower() – Transforma todas as letras de um texto em minúsculas.

40. number_format() – Formata números, permitindo definir casas decimais e separadores.

41. basename() – Retorna o nome do arquivo a partir de um caminho completo.

42. is_string() – Verifica se uma variável é do tipo texto (string).

43. is_file() – Verifica se determinado caminho corresponde a um arquivo existente.

44. str_contains() – Verifica se um texto contém uma determinada palavra ou sequência de caracteres.

45. ctype_digit() – Verifica se todos os caracteres de um texto são números.

46. file_get_contents() – Lê o conteúdo de um arquivo.

47. headers_sent() – Verifica se os cabeçalhos HTTP já foram enviados.

7. Símbolos e comandos que também aparecem no código

Operadores ou estruturas da linguagem:

- ! – Operador de negação. Inverte o resultado de uma condição.

- !preg_match() – Verifica se um texto não corresponde ao padrão definido.

- !in_array() – Verifica se um valor não existe dentro de um array.

- !empty() – Verifica se uma variável não está vazia.

- !hash_equals() – Verifica se dois valores não são iguais na comparação segura.

- echo json_encode() – Converte os dados para JSON e envia o resultado como resposta.

- echo json_encode(array_merge()) – Junta arrays, converte o resultado para JSON e exibe a resposta.

- COUNT() – Função do SQL utilizada para contar registros em uma consulta ao banco de dados.