<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261008025124 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE assunto (cod_as INT AUTO_INCREMENT NOT NULL, descricao VARCHAR(20) NOT NULL, PRIMARY KEY (cod_as)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE autor (cod_au INT AUTO_INCREMENT NOT NULL, nome VARCHAR(40) NOT NULL, PRIMARY KEY (cod_au)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE livro (codl INT AUTO_INCREMENT NOT NULL, titulo VARCHAR(40) NOT NULL, editora VARCHAR(40) NOT NULL, edicao INT NOT NULL, ano_publicacao VARCHAR(4) NOT NULL, PRIMARY KEY (codl)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE livro_autor (livro_codl INT NOT NULL, autor_cod_au INT NOT NULL, INDEX IDX_6749992A53550D7 (livro_codl), INDEX IDX_67499921AE83779 (autor_cod_au), PRIMARY KEY (livro_codl, autor_cod_au)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE livro_assunto (livro_codl INT NOT NULL, assunto_cod_as INT NOT NULL, INDEX IDX_53C2C52AA53550D7 (livro_codl), INDEX IDX_53C2C52A568DC9E5 (assunto_cod_as), PRIMARY KEY (livro_codl, assunto_cod_as)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE livro_autor ADD CONSTRAINT FK_6749992A53550D7 FOREIGN KEY (livro_codl) REFERENCES livro (codl)');
        $this->addSql('ALTER TABLE livro_autor ADD CONSTRAINT FK_67499921AE83779 FOREIGN KEY (autor_cod_au) REFERENCES autor (cod_au)');
        $this->addSql('ALTER TABLE livro_assunto ADD CONSTRAINT FK_53C2C52AA53550D7 FOREIGN KEY (livro_codl) REFERENCES livro (codl)');
        $this->addSql('ALTER TABLE livro_assunto ADD CONSTRAINT FK_53C2C52A568DC9E5 FOREIGN KEY (assunto_cod_as) REFERENCES assunto (cod_as)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE livro_autor DROP FOREIGN KEY FK_6749992A53550D7');
        $this->addSql('ALTER TABLE livro_autor DROP FOREIGN KEY FK_67499921AE83779');
        $this->addSql('ALTER TABLE livro_assunto DROP FOREIGN KEY FK_53C2C52AA53550D7');
        $this->addSql('ALTER TABLE livro_assunto DROP FOREIGN KEY FK_53C2C52A568DC9E5');
        $this->addSql('DROP TABLE assunto');
        $this->addSql('DROP TABLE autor');
        $this->addSql('DROP TABLE livro');
        $this->addSql('DROP TABLE livro_autor');
        $this->addSql('DROP TABLE livro_assunto');
    }
}
