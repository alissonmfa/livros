<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261008051800 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adiciona índice único em autor.nome e assunto.descricao';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE UNIQUE INDEX UNIQ_31075EBA54BD530C ON autor (nome)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B9F0BE022B85EB ON assunto (descricao)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_31075EBA54BD530C ON autor');
        $this->addSql('DROP INDEX UNIQ_B9F0BE022B85EB ON assunto');
    }
}
