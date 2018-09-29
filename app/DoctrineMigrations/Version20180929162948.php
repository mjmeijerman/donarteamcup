<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180929162948 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE team_soort (id INT AUTO_INCREMENT NOT NULL, categorie VARCHAR(156) NOT NULL, niveau VARCHAR(156) NOT NULL, uitslag_gepubliceerd INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE toegestane_niveaus ADD team_soort INT DEFAULT NULL');
        $this->addSql('ALTER TABLE toegestane_niveaus ADD CONSTRAINT FK_3A5896D9D5F9E15B FOREIGN KEY (team_soort) REFERENCES team_soort (id)');
        $this->addSql('CREATE INDEX IDX_3A5896D9D5F9E15B ON toegestane_niveaus (team_soort)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE toegestane_niveaus DROP FOREIGN KEY FK_3A5896D9D5F9E15B');
        $this->addSql('DROP TABLE team_soort');
        $this->addSql('DROP INDEX IDX_3A5896D9D5F9E15B ON toegestane_niveaus');
        $this->addSql('ALTER TABLE toegestane_niveaus DROP team_soort');
    }
}
