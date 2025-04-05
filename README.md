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

2. Suba os containers com o Docker Compose:
    - ``` docker-compose up --build -d ```
    - ``` docker exec -it simples-api composer install  ```
    - ``` docker exec -it simples-api chown -R www-data:www-data /var/www/html ```
    - ``` docker exec -it simples-api chmod -R 755 /var/www/html/storage /var/www/html/bootstrap/cache ```
    - ``` docker exec -it simples-api php artisan migrate ```
 3. Acesse a aplicação:
    - Abra o navegador e vá para:
    - http://localhost:8000/docs/api
