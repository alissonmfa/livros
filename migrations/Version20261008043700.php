<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261008043700 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE VIEW vw_relatorio_autor AS SELECT a.nome AS autor, l.titulo, l.editora, l.edicao, l.ano_publicacao, l.valor, (SELECT GROUP_CONCAT(s.descricao ORDER BY s.descricao SEPARATOR \', \') FROM livro_assunto la INNER JOIN assunto s ON s.cod_as = la.assunto_cod_as WHERE la.livro_codl = l.codl) AS assuntos FROM autor a INNER JOIN livro_autor lau ON lau.autor_cod_au = a.cod_au INNER JOIN livro l ON l.codl = lau.livro_codl');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP VIEW vw_relatorio_autor');
    }
}
