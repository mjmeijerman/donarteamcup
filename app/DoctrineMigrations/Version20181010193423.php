<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181010193423 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE team_soort_wedstrijd_ronde (team_soort_id INT NOT NULL, wedstrijd_ronde_id INT NOT NULL, INDEX IDX_E72A18E685AC8CA1 (team_soort_id), INDEX IDX_E72A18E63174D27A (wedstrijd_ronde_id), PRIMARY KEY(team_soort_id, wedstrijd_ronde_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE team_soort_wedstrijd_ronde ADD CONSTRAINT FK_E72A18E685AC8CA1 FOREIGN KEY (team_soort_id) REFERENCES team_soort (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE team_soort_wedstrijd_ronde ADD CONSTRAINT FK_E72A18E63174D27A FOREIGN KEY (wedstrijd_ronde_id) REFERENCES wedstrijd_ronde (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE team_soort DROP FOREIGN KEY FK_D5F9E15B3174D27A');
        $this->addSql('DROP INDEX IDX_D5F9E15B3174D27A ON team_soort');
        $this->addSql('ALTER TABLE team_soort DROP wedstrijd_ronde_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE team_soort_wedstrijd_ronde');
        $this->addSql('ALTER TABLE team_soort ADD wedstrijd_ronde_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE team_soort ADD CONSTRAINT FK_D5F9E15B3174D27A FOREIGN KEY (wedstrijd_ronde_id) REFERENCES wedstrijd_ronde (id)');
        $this->addSql('CREATE INDEX IDX_D5F9E15B3174D27A ON team_soort (wedstrijd_ronde_id)');
    }
}
