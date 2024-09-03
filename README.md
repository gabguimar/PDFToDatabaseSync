# PDFToDatabaseSync

O **PDFToDatabaseSync** é um microsserviço da intranet da empresa que automatiza a extração e sincronização de dados de PDFs com um banco de dados. Desenvolvido para otimizar o processamento de faturas e documentos internos, ele melhora a eficiência e precisão na gestão de informações.

## Funcionalidades

- **Processamento de PDFs**: O microsserviço acessa e processa documentos PDF armazenados em um diretório designado.
- **Extração de Texto**: Utiliza a biblioteca `Smalot\PdfParser` para extrair o texto dos PDFs de maneira eficiente.
- **Classificação de Documentos**: Identifica e classifica os PDFs com base em seu conteúdo, como faturas ou garantias.
- **Manipulação e Formatação de Dados**: Processa e formata informações extraídas dos documentos conforme os requisitos internos.
- **Sincronização com Banco de Dados**: Insere os dados extraídos em um banco de dados SQL Server, organizando as informações de forma estruturada.
- **Validação e Processamento Condicional**: Aplica regras para validar e ajustar os dados extraídos, garantindo a precisão das informações.

## Tecnologias Utilizadas

- **PHP**: Linguagem de programação utilizada para desenvolver o microsserviço.
- **Smalot\PdfParser**: Biblioteca PHP usada para a análise e extração de texto dos documentos PDF.
- **SQL Server**: Sistema de gerenciamento de banco de dados utilizado para armazenar e gerenciar as informações extraídas.
- **Framework Proprietário da Empresa**: O microsserviço é integrado a um framework desenvolvido internamente pela empresa, que fornece suporte e consistência para os aplicativos e serviços internos.

## Contexto do Projeto

O **PDFToDatabaseSync** faz parte da intranet da empresa e é projetado para atender às necessidades específicas de processamento de documentos internos. Este microsserviço ilustra como soluções especializadas são implementadas para automatizar e melhorar processos dentro do ambiente corporativo.
