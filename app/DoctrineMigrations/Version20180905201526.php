<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180905201526 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE tijd_schema (id INT AUTO_INCREMENT NOT NULL, naam VARCHAR(300) NOT NULL, locatie VARCHAR(300) NOT NULL, uploader VARCHAR(300) NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sponsor (id INT AUTO_INCREMENT NOT NULL, locatie VARCHAR(300) NOT NULL, locatie2 VARCHAR(300) NOT NULL, naam VARCHAR(300) NOT NULL, website VARCHAR(300) DEFAULT NULL, omschrijving LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reglementen (id INT AUTO_INCREMENT NOT NULL, naam VARCHAR(300) NOT NULL, locatie VARCHAR(300) NOT NULL, uploader VARCHAR(300) NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vereniging (id INT AUTO_INCREMENT NOT NULL, naam VARCHAR(256) NOT NULL, plaats VARCHAR(256) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE jury_indeling (id INT AUTO_INCREMENT NOT NULL, naam VARCHAR(300) NOT NULL, locatie VARCHAR(300) NOT NULL, uploader VARCHAR(300) NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE hoofdmenuitems (id INT AUTO_INCREMENT NOT NULL, naam VARCHAR(156) NOT NULL, positie INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE scores (id INT AUTO_INCREMENT NOT NULL, wedstrijdnummer INT DEFAULT NULL, wedstrijddag VARCHAR(55) DEFAULT NULL, wedstrijdronde VARCHAR(55) DEFAULT NULL, baan VARCHAR(55) DEFAULT NULL, groep VARCHAR(55) DEFAULT NULL, begintoestel VARCHAR(55) DEFAULT NULL, d_sprong1 NUMERIC(5, 3) NOT NULL, e_sprong1 NUMERIC(5, 3) NOT NULL, n_sprong1 NUMERIC(5, 3) NOT NULL, d_sprong2 NUMERIC(5, 3) NOT NULL, e_sprong2 NUMERIC(5, 3) NOT NULL, n_sprong2 NUMERIC(5, 3) NOT NULL, getoond_sprong INT NOT NULL, gepubliceerd_sprong TINYINT(1) NOT NULL, updated_sprong DATETIME DEFAULT NULL, d_brug NUMERIC(5, 3) NOT NULL, e_brug NUMERIC(5, 3) NOT NULL, n_brug NUMERIC(5, 3) NOT NULL, getoond_brug INT NOT NULL, gepubliceerd_brug TINYINT(1) NOT NULL, updated_brug DATETIME DEFAULT NULL, d_balk NUMERIC(5, 3) NOT NULL, e_balk NUMERIC(5, 3) NOT NULL, n_balk NUMERIC(5, 3) NOT NULL, getoond_balk INT NOT NULL, gepubliceerd_balk TINYINT(1) NOT NULL, updated_balk DATETIME DEFAULT NULL, d_vloer NUMERIC(5, 3) NOT NULL, e_vloer NUMERIC(5, 3) NOT NULL, n_vloer NUMERIC(5, 3) NOT NULL, getoond_vloer INT NOT NULL, gepubliceerd_vloer TINYINT(1) NOT NULL, updated_vloer DATETIME DEFAULT NULL, geturnd_vloer TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE voorinschrijving (id INT AUTO_INCREMENT NOT NULL, token VARCHAR(256) NOT NULL, created_at DATETIME NOT NULL, used_at DATETIME DEFAULT NULL, token_sent_to VARCHAR(256) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE fotoupload (id INT AUTO_INCREMENT NOT NULL, naam VARCHAR(300) NOT NULL, locatie VARCHAR(300) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE betaling (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, bedrag NUMERIC(6, 2) NOT NULL, datum_betaald DATETIME NOT NULL, INDEX IDX_4DD0001A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, vereniging_id INT DEFAULT NULL, username VARCHAR(190) NOT NULL, role VARCHAR(60) NOT NULL, email VARCHAR(190) NOT NULL, voornaam VARCHAR(255) NOT NULL, achternaam VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, isActive TINYINT(1) NOT NULL, verantwoordelijkheid VARCHAR(255) DEFAULT NULL, telefoonnummer VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, factuur_nummer VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), INDEX IDX_8D93D64917080D2E (vereniging_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE submenuitems (id INT AUTO_INCREMENT NOT NULL, naam VARCHAR(156) NOT NULL, positie INT NOT NULL, hoofdmenuItem_id INT NOT NULL, INDEX IDX_E01BC2217375D3BD (hoofdmenuItem_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE jurylid (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, email VARCHAR(190) NOT NULL, voornaam VARCHAR(255) NOT NULL, achternaam VARCHAR(255) NOT NULL, brevet VARCHAR(255) NOT NULL, opmerking LONGTEXT DEFAULT NULL, zaterdag TINYINT(1) NOT NULL, zondag TINYINT(1) NOT NULL, maandag TINYINT(1) NOT NULL, INDEX IDX_C34D4BF5A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE content (id INT AUTO_INCREMENT NOT NULL, gewijzigd DATETIME NOT NULL, pagina VARCHAR(156) NOT NULL, content LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE nieuwsbericht (id INT AUTO_INCREMENT NOT NULL, datumtijd VARCHAR(156) NOT NULL, jaar INT NOT NULL, titel VARCHAR(156) NOT NULL, bericht LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE organisatiemenuitems (id INT AUTO_INCREMENT NOT NULL, naam VARCHAR(156) NOT NULL, positie INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE instellingen (id INT AUTO_INCREMENT NOT NULL, instelling VARCHAR(156) NOT NULL, datum DATETIME DEFAULT NULL, aantal INT DEFAULT NULL, gewijzigd DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE fileupload (id INT AUTO_INCREMENT NOT NULL, naam VARCHAR(300) NOT NULL, locatie VARCHAR(300) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE turnster (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, vloermuziek_id INT DEFAULT NULL, score_id INT DEFAULT NULL, voornaam VARCHAR(255) NOT NULL, achternaam VARCHAR(255) NOT NULL, geboortajaar INT NOT NULL, niveau VARCHAR(12) NOT NULL, categorie VARCHAR(12) NOT NULL, afgemeld TINYINT(1) NOT NULL, wachtlijst TINYINT(1) NOT NULL, ingevuld TINYINT(1) NOT NULL, creation_date DATETIME NOT NULL, expiration_date DATETIME DEFAULT NULL, opmerking LONGTEXT DEFAULT NULL, INDEX IDX_1F739A65A76ED395 (user_id), UNIQUE INDEX UNIQ_1F739A65D42CC2DA (vloermuziek_id), UNIQUE INDEX UNIQ_1F739A6512EB0A51 (score_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE toegestane_niveaus (id INT AUTO_INCREMENT NOT NULL, categorie VARCHAR(156) NOT NULL, niveau VARCHAR(156) NOT NULL, uitslag_gepubliceerd INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE send_mail (id INT AUTO_INCREMENT NOT NULL, datum DATETIME NOT NULL, bericht LONGTEXT NOT NULL, aan VARCHAR(300) NOT NULL, van VARCHAR(300) NOT NULL, onderwerp VARCHAR(300) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vloermuziek (id INT AUTO_INCREMENT NOT NULL, locatie VARCHAR(300) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE betaling ADD CONSTRAINT FK_4DD0001A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64917080D2E FOREIGN KEY (vereniging_id) REFERENCES vereniging (id)');
        $this->addSql('ALTER TABLE submenuitems ADD CONSTRAINT FK_E01BC2217375D3BD FOREIGN KEY (hoofdmenuItem_id) REFERENCES hoofdmenuitems (id)');
        $this->addSql('ALTER TABLE jurylid ADD CONSTRAINT FK_C34D4BF5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE turnster ADD CONSTRAINT FK_1F739A65A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE turnster ADD CONSTRAINT FK_1F739A65D42CC2DA FOREIGN KEY (vloermuziek_id) REFERENCES vloermuziek (id)');
        $this->addSql('ALTER TABLE turnster ADD CONSTRAINT FK_1F739A6512EB0A51 FOREIGN KEY (score_id) REFERENCES scores (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64917080D2E');
        $this->addSql('ALTER TABLE submenuitems DROP FOREIGN KEY FK_E01BC2217375D3BD');
        $this->addSql('ALTER TABLE turnster DROP FOREIGN KEY FK_1F739A6512EB0A51');
        $this->addSql('ALTER TABLE betaling DROP FOREIGN KEY FK_4DD0001A76ED395');
        $this->addSql('ALTER TABLE jurylid DROP FOREIGN KEY FK_C34D4BF5A76ED395');
        $this->addSql('ALTER TABLE turnster DROP FOREIGN KEY FK_1F739A65A76ED395');
        $this->addSql('ALTER TABLE turnster DROP FOREIGN KEY FK_1F739A65D42CC2DA');
        $this->addSql('DROP TABLE tijd_schema');
        $this->addSql('DROP TABLE sponsor');
        $this->addSql('DROP TABLE reglementen');
        $this->addSql('DROP TABLE vereniging');
        $this->addSql('DROP TABLE jury_indeling');
        $this->addSql('DROP TABLE hoofdmenuitems');
        $this->addSql('DROP TABLE scores');
        $this->addSql('DROP TABLE voorinschrijving');
        $this->addSql('DROP TABLE fotoupload');
        $this->addSql('DROP TABLE betaling');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE submenuitems');
        $this->addSql('DROP TABLE jurylid');
        $this->addSql('DROP TABLE content');
        $this->addSql('DROP TABLE nieuwsbericht');
        $this->addSql('DROP TABLE organisatiemenuitems');
        $this->addSql('DROP TABLE instellingen');
        $this->addSql('DROP TABLE fileupload');
        $this->addSql('DROP TABLE turnster');
        $this->addSql('DROP TABLE toegestane_niveaus');
        $this->addSql('DROP TABLE send_mail');
        $this->addSql('DROP TABLE vloermuziek');
    }
}
