# API Simples

Este é um projeto de API simples utilizando Laravel, Docker e Docker Compose.

## Pré-requisitos

Antes de começar, certifique-se de ter os seguintes softwares instalados na sua máquina:

- [Docker](https://www.docker.com/)
- [Docker Compose](https://docs.docker.com/compose/)

## Como rodar o projeto

1. **Clone este repositório**

```bash
git clone https://github.com/LuanMakohin/api-simples.git
cd api-simples
```

2. **Suba os containers com Docker Compose**

```bash
docker-compose up --build -d
```

Esse comando cria e inicializa os containers em segundo plano com base na configuração do `docker-compose.yml`.

3. **Instale as dependências do Laravel dentro do container**

```bash
docker exec -it simples-api composer install
```

Esse comando executa o `composer install` dentro do container `simples-api`, instalando os pacotes do Laravel.

4. **Ajuste as permissões das pastas necessárias**

```bash
docker exec -it simples-api chown -R www-data:www-data /var/www/html
docker exec -it simples-api chmod -R 755 /var/www/html/storage /var/www/html/bootstrap/cache
```

Esses comandos garantem que o Laravel tenha permissão para escrever nas pastas `storage` e `bootstrap/cache`.

5. **Execute as migrações do banco de dados**

```bash
docker exec -it simples-api php artisan migrate
```

Este comando cria as tabelas no banco de dados com base nas migrations definidas no projeto Laravel.

## Acessando a aplicação

Abra o navegador e acesse:

[http://localhost:8000/docs/api](http://localhost:8000/docs/api)

Essa é a rota onde a documentação da API estará disponível.
