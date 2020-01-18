<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200113114251 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE wedstrijd_ronde CHANGE uitslag_gepubliceerd uitslag_gepubliceerd INT NOT NULL');
        $this->addSql('ALTER TABLE toegestane_niveaus ADD calculation_method_sprong_meerkamp VARCHAR(255) DEFAULT NULL, ADD calculation_method_sprong_toestel_prijs VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE toegestane_niveaus DROP calculation_method_sprong_meerkamp, DROP calculation_method_sprong_toestel_prijs');
        $this->addSql('ALTER TABLE wedstrijd_ronde CHANGE uitslag_gepubliceerd uitslag_gepubliceerd INT DEFAULT 0 NOT NULL');
    }
}
