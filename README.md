# hesk-external-list

#### HESK Version: 2.8.4 and 3.1+

## Atenção:

*Ao usar esse script, o conteúdo dos tickets serão facilmente acessados por qualquer um que conheça os e-mails dos usuários.*

## Warning:

*When using this script, the contents of the tickets will be easily accessed by anyone who knows the users' emails.*

## PT-BR - Lista externa de Tickets, usando e-mail do usuário.

### Como usar
1. Instale dentro do diretorio do HESK (Recomendo criar o diretório /list)
2. Renomeie config-sample.php para config.php
3. Configure os dados de acesso do banco de dados em config.php

### URL / Link
1. Envie via GET as variaveis $q e $name, onde $q é o e-mail e $name é o nome.
2. Ex: (mysite.com/hesk/list/?q=user@mail.com&name=Username)
3. Ex: Use base64 no e-mail (mysite.com/hesk/list/?q=dXNlckBtYWlsLmNvbQ==&name=Username)

### Exemplos
1. Exemplos de uso disponíveis no diretorio "Examples"

## EN - External list of tickets, using the user's email.

### How use
1. Install in a directory within hesk (Recommended create the directory /list)
2. RENAME config-sample.php to config.php
3. Configure your password in config.php

## Screenshot

![](/images/print/1-list.png)
