## Requisitos
* PHP ou superior
* MySQL ou superior
* Composer

## Como rodar o projeto?
Duplicar o arquivo ".env.example" e renomear para ".env"<br>
Altere no arquivo ".env" as credenciais do banco de dados<br>

Instalar as dependências do PHP
```
composer install
```

Gerar a chave no arquivo ".env"
```
php artisan key:generate
```

## Sequencia para criar o projeto
Criar projeto com Laravel
```
composer create-project laravel/laravel NOME-DO-PROJETO
```

Alterar no arquivo '.env' as credenciasis do banco de dados <br>

Criar o arquivo de rotas para API
```
php artisan install:api
```
