<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261008054000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Insere autores, assuntos e livros populares de exemplo';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT IGNORE INTO assunto (descricao) VALUES
            ('Romance'),
            ('Clássico'),
            ('Distopia'),
            ('Ficção'),
            ('Fantasia'),
            ('Aventura'),
            ('Suspense'),
            ('Infantil'),
            ('Terror'),
            ('HQ'),
            ('Filosofia'),
            ('Negócios'),
            ('Autoajuda')");

        $this->addSql("INSERT IGNORE INTO autor (nome) VALUES
            ('Machado de Assis'),
            ('Clarice Lispector'),
            ('George Orwell'),
            ('J.R.R. Tolkien'),
            ('Dan Brown'),
            ('Antoine de Saint-Exupéry'),
            ('Paulo Coelho'),
            ('Jane Austen'),
            ('Neil Gaiman'),
            ('Terry Pratchett'),
            ('Stephen King'),
            ('Peter Straub'),
            ('Alan Moore'),
            ('Dave Gibbons'),
            ('Robert Kiyosaki'),
            ('Sharon Lechter'),
            ('J.K. Rowling'),
            ('John Tiffany'),
            ('Jack Thorne'),
            ('Gene Kim'),
            ('Kevin Behr'),
            ('George Spafford')");

        $this->addSql("INSERT INTO livro (titulo, editora, edicao, ano_publicacao, valor) VALUES
            ('Dom Casmurro', 'Garnier', 1, '1899', '39.90'),
            ('A Hora da Estrela', 'Rocco', 1, '1977', '42.00'),
            ('1984', 'Companhia das Letras', 1, '1949', '49.90'),
            ('O Hobbit', 'HarperCollins', 5, '1937', '59.90'),
            ('O Código Da Vinci', 'Arqueiro', 1, '2003', '54.90'),
            ('O Pequeno Príncipe', 'Agir', 48, '1943', '29.90'),
            ('O Alquimista', 'Paralela', 1, '1988', '36.90'),
            ('Orgulho e Preconceito', 'Martin Claret', 3, '1813', '32.90'),
            ('Belas Maldições', 'Conrad', 1, '1990', '64.90'),
            ('O Talismã', 'Suma', 1, '1984', '69.90'),
            ('Watchmen', 'Panini', 1, '1986', '79.90'),
            ('Pai Rico, Pai Pobre', 'Alta Books', 2, '1997', '44.90'),
            ('Harry Potter e a Criança Amaldiçoada', 'Rocco', 1, '2016', '59.90'),
            ('O Projeto Fênix', 'Novatec', 1, '2013', '89.90')");

        $this->addSql("INSERT INTO livro_autor (livro_codl, autor_cod_au)
            SELECT l.codl, a.cod_au
            FROM livro l
            INNER JOIN autor a ON (l.titulo, a.nome) IN (
                ('Dom Casmurro', 'Machado de Assis'),
                ('A Hora da Estrela', 'Clarice Lispector'),
                ('1984', 'George Orwell'),
                ('O Hobbit', 'J.R.R. Tolkien'),
                ('O Código Da Vinci', 'Dan Brown'),
                ('O Pequeno Príncipe', 'Antoine de Saint-Exupéry'),
                ('O Alquimista', 'Paulo Coelho'),
                ('Orgulho e Preconceito', 'Jane Austen'),
                ('Belas Maldições', 'Neil Gaiman'),
                ('Belas Maldições', 'Terry Pratchett'),
                ('O Talismã', 'Stephen King'),
                ('O Talismã', 'Peter Straub'),
                ('Watchmen', 'Alan Moore'),
                ('Watchmen', 'Dave Gibbons'),
                ('Pai Rico, Pai Pobre', 'Robert Kiyosaki'),
                ('Pai Rico, Pai Pobre', 'Sharon Lechter'),
                ('Harry Potter e a Criança Amaldiçoada', 'J.K. Rowling'),
                ('Harry Potter e a Criança Amaldiçoada', 'John Tiffany'),
                ('Harry Potter e a Criança Amaldiçoada', 'Jack Thorne'),
                ('O Projeto Fênix', 'Gene Kim'),
                ('O Projeto Fênix', 'Kevin Behr'),
                ('O Projeto Fênix', 'George Spafford')
            )");

        $this->addSql("INSERT INTO livro_assunto (livro_codl, assunto_cod_as)
            SELECT l.codl, s.cod_as
            FROM livro l
            INNER JOIN assunto s ON (l.titulo, s.descricao) IN (
                ('Dom Casmurro', 'Romance'),
                ('Dom Casmurro', 'Clássico'),
                ('A Hora da Estrela', 'Romance'),
                ('1984', 'Distopia'),
                ('1984', 'Ficção'),
                ('O Hobbit', 'Fantasia'),
                ('O Hobbit', 'Aventura'),
                ('O Código Da Vinci', 'Suspense'),
                ('O Pequeno Príncipe', 'Infantil'),
                ('O Pequeno Príncipe', 'Ficção'),
                ('O Alquimista', 'Filosofia'),
                ('Orgulho e Preconceito', 'Romance'),
                ('Orgulho e Preconceito', 'Clássico'),
                ('Belas Maldições', 'Fantasia'),
                ('Belas Maldições', 'Ficção'),
                ('O Talismã', 'Terror'),
                ('O Talismã', 'Fantasia'),
                ('Watchmen', 'HQ'),
                ('Pai Rico, Pai Pobre', 'Negócios'),
                ('Pai Rico, Pai Pobre', 'Autoajuda'),
                ('Harry Potter e a Criança Amaldiçoada', 'Fantasia'),
                ('O Projeto Fênix', 'Negócios')
            )");
    }

    public function down(Schema $schema): void
    {
        $titulos = "'Dom Casmurro', 'A Hora da Estrela', '1984', 'O Hobbit', 'O Código Da Vinci', 'O Pequeno Príncipe', 'O Alquimista', 'Orgulho e Preconceito', 'Belas Maldições', 'O Talismã', 'Watchmen', 'Pai Rico, Pai Pobre', 'Harry Potter e a Criança Amaldiçoada', 'O Projeto Fênix'";

        $this->addSql("DELETE la FROM livro_autor la INNER JOIN livro l ON l.codl = la.livro_codl WHERE l.titulo IN ($titulos)");
        $this->addSql("DELETE la FROM livro_assunto la INNER JOIN livro l ON l.codl = la.livro_codl WHERE l.titulo IN ($titulos)");
        $this->addSql("DELETE FROM livro WHERE titulo IN ($titulos)");
        $this->addSql("DELETE a FROM autor a
            LEFT JOIN livro_autor la ON la.autor_cod_au = a.cod_au
            WHERE la.autor_cod_au IS NULL
            AND a.nome IN (
                'Machado de Assis',
                'Clarice Lispector',
                'George Orwell',
                'J.R.R. Tolkien',
                'Dan Brown',
                'Antoine de Saint-Exupéry',
                'Paulo Coelho',
                'Jane Austen',
                'Neil Gaiman',
                'Terry Pratchett',
                'Stephen King',
                'Peter Straub',
                'Alan Moore',
                'Dave Gibbons',
                'Robert Kiyosaki',
                'Sharon Lechter',
                'J.K. Rowling',
                'John Tiffany',
                'Jack Thorne',
                'Gene Kim',
                'Kevin Behr',
                'George Spafford'
            )");
        $this->addSql("DELETE s FROM assunto s
            LEFT JOIN livro_assunto la ON la.assunto_cod_as = s.cod_as
            WHERE la.assunto_cod_as IS NULL
            AND s.descricao IN (
                'Romance',
                'Clássico',
                'Distopia',
                'Ficção',
                'Fantasia',
                'Aventura',
                'Suspense',
                'Infantil',
                'Terror',
                'HQ',
                'Filosofia',
                'Negócios',
                'Autoajuda'
            )");
    }
}
