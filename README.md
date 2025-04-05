# API Simples
Este é um projeto de API simples utilizando Laravel, Docker e Docker Compose.
## Pré-requisitos
Para rodar este projeto, você precisa ter os seguintes softwares instalados na sua máquina:
 - [Docker](https://www.docker.com/)
 - [Docker Compose](https://docs.docker.com/compose/)
## Como rodar o projeto
 1. Clone este repositório:
     - ```git clone https://github.com/LuanMakohin/api-simples.git```
     - ```cd api-simples```
 2. Renomeie o arquivo `.env.example` para `.env`:
     ### Windows (CMD)
    ```copy .env.example .env```
    ### Linux / macOS
    ```cp .env.example .env```

3. Suba os containers com o Docker Compose:
    - ```docker-compose up -d```
 4. Acesse a aplicação:
    - Abra o navegador e vá para:
    - http://localhost:8000/docs/api
