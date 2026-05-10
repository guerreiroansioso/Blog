# Sobre o Projeto

Este projeto é um site de conteúdo feito em *PHP 8.5* e organizado para publicar páginas escritas em *Markdown*. A proposta é separar o conteúdo do código, deixando os textos, o menu, as configurações principais e o rodapé dentro da pasta *Content*. Dessa forma, uma pessoa pode alterar grande parte do site editando arquivos *.md*, sem precisar modificar diretamente as classes *PHP* ou os arquivos de *view*.

## Criação de páginas

Para criar uma nova página, basta adicionar um arquivo *Markdown* dentro da pasta *Content*. O nome desse arquivo é usado para formar o endereço da página, chamado de *slug*. Um arquivo com o nome *Minha-Pagina.md*, por exemplo, passa a representar uma página que pode ser acessada pelo *slug* *minha-pagina*. O sistema normaliza o nome do arquivo, transformando letras em minúsculas e substituindo caracteres que não fazem parte do formato do endereço. O primeiro título escrito com o símbolo *#* é usado como título da página, e o restante do arquivo se torna o conteúdo principal exibido no card de leitura.

## Escrita em Markdown

O conteúdo das páginas aceita uma forma simples de *Markdown*. Títulos podem ser criados com *#*, *##* e *###*, enquanto os parágrafos são escritos como texto comum separado por linhas em branco. Também é possível usar itálico com um asterisco e negrito com dois asteriscos. O *parser* do projeto é intencionalmente pequeno, então ele não tenta cobrir todos os recursos de *Markdown*; ele existe para manter a escrita simples e previsível dentro do site.

## Configuração do site

As configurações gerais ficam no arquivo *Config.md*. Esse arquivo define informações que aparecem em várias partes do site, como o nome usado no título do navegador, o nome exibido no topo da página, a descrição abaixo desse nome e o comportamento da página inicial. A configuração *siteName* representa o nome institucional do site, *displayName* controla o título visível no cabeçalho, *description* controla o texto curto exibido abaixo do título, e *hidePageList* decide se a página inicial deve mostrar uma lista de páginas ou se deve exibir diretamente o conteúdo escrito em *Home.md*. Quando *hidePageList* está como *yes*, o conteúdo de *Home.md* vira a tela inicial; quando está como *no*, a tela inicial mostra a lista das páginas disponíveis.

## Menu de navegação

O arquivo *Menu.md* controla os links exibidos no menu superior. Cada link do menu é escrito em formato *Markdown*, com um texto visível e um endereço de destino. Esse arquivo permite alterar a navegação principal do site sem tocar nas *views*. Um link pode apontar para a página inicial usando o endereço */*, ou pode apontar para uma página interna usando o formato */page?slug=* seguido do *slug* da página desejada.

## Página inicial

O arquivo *Home.md* tem uma função especial quando a configuração *hidePageList* está ativada. Nesse caso, ele deixa de ser apenas um arquivo interno e passa a fornecer o conteúdo principal da página inicial. Ele pode ser escrito como qualquer outra página *Markdown*, incluindo título, subtítulos, parágrafos e seções laterais. Mesmo assim, *Home.md* não aparece na lista de páginas comuns, porque é tratado como parte da estrutura interna do site.

## Paginação

A paginação pode ser ativada dentro de qualquer página *Markdown* usando uma linha com o título *# Pagination*. O conteúdo antes da primeira ocorrência desse marcador aparece como a primeira parte da página, e cada novo marcador inicia uma nova parte. Quando o site encontra mais de uma parte, ele exibe botões de paginação abaixo do conteúdo principal, permitindo navegar entre as seções do mesmo arquivo sem criar arquivos separados.

## Sidebars

As *sidebars* são criadas dentro de qualquer página *Markdown* usando uma linha com o título *# Sidebar*. Tudo que vem antes da primeira ocorrência desse título permanece no conteúdo principal da página. Tudo que vem depois passa a ser renderizado na lateral. Se o arquivo tiver mais de uma seção chamada *# Sidebar*, cada uma delas vira um bloco lateral separado. Isso permite colocar resumo, informações extras, observações ou qualquer conteúdo complementar sem misturar esse material com o artigo principal.

## Rodapé

O rodapé é configurado no arquivo *Footer.md*. Diferente de uma página comum, esse arquivo é lido como uma coleção de seções para montar o *footer* do site. Cada título escrito em *Footer.md* inicia uma nova área do rodapé, e o texto abaixo desse título se torna o conteúdo daquela área. Assim, o rodapé pode ter informações institucionais, contato, direitos, descrição do projeto ou outros textos importantes, mantendo tudo editável em *Markdown*.

## Arquivos internos

Alguns arquivos da pasta *Content* são considerados internos pelo sistema. *Config.md*, *Menu.md*, *Home.md* e *Footer.md* são usados para configurar partes globais do site, por isso não aparecem como páginas públicas na listagem. Os demais arquivos *Markdown* da pasta *Content* são tratados como páginas comuns e podem ser acessados pelo *slug* gerado a partir de seus nomes.

## Organização do código

Por trás dessas regras, o código *PHP* separa as responsabilidades em pequenas classes. *Repository* lê os arquivos de conteúdo e configuração, *Parser* converte o *Markdown* simples em *HTML*, *PageFactory* cria objetos de página a partir dos arquivos, *ResponseHandler* prepara os dados que serão enviados para as *views*, e *App* coordena o fluxo entre rotas e respostas. Essa organização mantém o projeto pequeno, mas fácil de entender e expandir.

## Objetivo

O objetivo do projeto é servir como uma base simples para um site de conteúdo administrado por arquivos *Markdown*. Ele não depende de painel administrativo nem de banco de dados para publicar páginas. A edição acontece diretamente nos arquivos da pasta *Content*, enquanto o *PHP* cuida da navegação, da renderização, do layout, das *sidebars* e do *footer*.
