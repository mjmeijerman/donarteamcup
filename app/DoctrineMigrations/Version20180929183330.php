<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180929183330 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE team (id INT AUTO_INCREMENT NOT NULL, team_soort_id INT DEFAULT NULL, wedstrijd_ronde_id INT DEFAULT NULL, user_id INT DEFAULT NULL, email VARCHAR(190) NOT NULL, UNIQUE INDEX UNIQ_C4E0A61FE7927C74 (email), INDEX IDX_C4E0A61F85AC8CA1 (team_soort_id), INDEX IDX_C4E0A61F3174D27A (wedstrijd_ronde_id), INDEX IDX_C4E0A61FA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE wedstrijd_ronde (id INT AUTO_INCREMENT NOT NULL, dag VARCHAR(255) NOT NULL, ronde INT NOT NULL, start_tijd DATETIME NOT NULL, eind_tijd DATETIME NOT NULL, max_teams INT NOT NULL, UNIQUE INDEX UNIQ_CEB470F158757EB9 (ronde), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE team ADD CONSTRAINT FK_C4E0A61F85AC8CA1 FOREIGN KEY (team_soort_id) REFERENCES team_soort (id)');
        $this->addSql('ALTER TABLE team ADD CONSTRAINT FK_C4E0A61F3174D27A FOREIGN KEY (wedstrijd_ronde_id) REFERENCES wedstrijd_ronde (id)');
        $this->addSql('ALTER TABLE team ADD CONSTRAINT FK_C4E0A61FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE team_soort ADD wedstrijd_ronde_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE team_soort ADD CONSTRAINT FK_D5F9E15B3174D27A FOREIGN KEY (wedstrijd_ronde_id) REFERENCES wedstrijd_ronde (id)');
        $this->addSql('CREATE INDEX IDX_D5F9E15B3174D27A ON team_soort (wedstrijd_ronde_id)');
        $this->addSql('ALTER TABLE turnster ADD team INT DEFAULT NULL');
        $this->addSql('ALTER TABLE turnster ADD CONSTRAINT FK_1F739A65C4E0A61F FOREIGN KEY (team) REFERENCES team (id)');
        $this->addSql('CREATE INDEX IDX_1F739A65C4E0A61F ON turnster (team)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE turnster DROP FOREIGN KEY FK_1F739A65C4E0A61F');
        $this->addSql('ALTER TABLE team DROP FOREIGN KEY FK_C4E0A61F3174D27A');
        $this->addSql('ALTER TABLE team_soort DROP FOREIGN KEY FK_D5F9E15B3174D27A');
        $this->addSql('DROP TABLE team');
        $this->addSql('DROP TABLE wedstrijd_ronde');
        $this->addSql('DROP INDEX IDX_D5F9E15B3174D27A ON team_soort');
        $this->addSql('ALTER TABLE team_soort DROP wedstrijd_ronde_id');
        $this->addSql('DROP INDEX IDX_1F739A65C4E0A61F ON turnster');
        $this->addSql('ALTER TABLE turnster DROP team');
    }
}
