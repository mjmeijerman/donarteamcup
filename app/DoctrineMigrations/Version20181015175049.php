<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181015175049 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX UNIQ_C4E0A61FE7927C74 ON team');
        $this->addSql('ALTER TABLE team ADD team_name VARCHAR(190) DEFAULT NULL, DROP email');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C4E0A61F8FC28A7D ON team (team_name)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX UNIQ_C4E0A61F8FC28A7D ON team');
        $this->addSql('ALTER TABLE team ADD email VARCHAR(190) NOT NULL COLLATE utf8_unicode_ci, DROP team_name');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C4E0A61FE7927C74 ON team (email)');
    }
}
