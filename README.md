# Livraria

Aplicação web em Symfony 7.4 (PHP 8.2+) para cadastrar livros, autores e assuntos, com relatório. O banco é MySQL, acessado via Doctrine.

## Como subir

Pré-requisitos: PHP >= 8.2, Composer e MySQL 8.

1. Instalar as dependências:

```bash
composer install
```

2. Conferir a conexão em `.env`. O valor padrão é:

```
mysql://root:root@127.0.0.1:3306/livros
```

Ajuste usuário, senha e host se o MySQL local for diferente.

3. Criar o banco e aplicar as migrations:

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

4. Subir o servidor:

```bash
symfony server:start
```

Ou, sem o Symfony CLI:

```bash
php -S localhost:8000 -t public
```

5. Abrir [http://localhost:8000](http://localhost:8000).
