<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\Migrations\AbstractMigration;

final class Version20180907183425 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $sql = <<<EOQ
INSERT INTO `hoofdmenuitems` (`naam`, `positie`)
VALUES
	('Laatste nieuws', 1),
	('Wedstrijdinformatie', 2),
	('Sponsors', 3),
	('Contact', 4),
	('Inloggen', 5);
EOQ;

        $this->addSql($sql);

        $sql = <<<EOQ
INSERT INTO `content` (`gewijzigd`, `pagina`, `content`)
VALUES
	(:now, 'Algemene informatie', '<h1>Algemene informatie</h1>'),
	(:now, 'Contact', '<h1>Contact</h1>'),
	(:now, 'Inschrijvingsinformatie', '<h1>Inschrijvingen gesloten </h1>'),
	(:now, 'Laatste nieuws', '<h1>Laatste nieuws</h1>'),
	(:now, 'Reglementen', '<h1>Reglementen</h1>'),
	(:now, 'Sponsors', '<h1>Sponsors</h1>'),
	(:now, 'Uitslagen', '<h1>Uitslagen</h1>'),
	(:now, 'Wedstrijdindeling', '<h1>Wedstrijdindeling</h1>'),
	(:now, 'Wedstrijdindeling Zaterdag', '<h1>Wedstrijdindeling Zaterdag</h1>'),
	(:now, 'Wedstrijdindeling Zondag', '<h1>Wedstrijdindeling Zondag</h1>');
EOQ;

        $this->addSql($sql, ['now' => new \DateTime()], ['now' => Type::DATETIME]);

        $sql = <<<EOQ
INSERT INTO `instellingen` (`instelling`, `datum`, `aantal`, `gewijzigd`)
VALUES
	('Factuur publiceren', '2018-12-01 00:00:00', NULL, :now),
	('Max aantal turnsters', NULL, 648, :now),
	('Opening inschrijving', '2018-12-01 18:30:02', NULL, :now),
	('Opening uploaden vloermuziek', '2018-12-10 01:00:00', NULL, :now),
	('Sluiting inschrijving juryleden', '2018-12-10 08:37:23', NULL, :now),
	('Sluiting inschrijving turnsters', '2018-12-10 00:00:00', NULL, :now),
	('Sluiting uploaden vloermuziek', '2018-12-20 00:00:00', NULL, :now),
	('tijdVol', '2017-03-22 08:42:43', NULL, :now),
	('Uiterlijke betaaldatum', '2018-12-30 00:00:00', NULL, :now);
EOQ;

        $this->addSql($sql, ['now' => new \DateTime()], ['now' => Type::DATETIME]);

        $sql = <<<EOQ
INSERT INTO `organisatiemenuitems` (`naam`, `positie`)
VALUES
	('Mijn gegevens', 1),
	('Instellingen', 3),
	('Inschrijvingen', 5),
	('Juryzaken', 7),
	('Financieel', 8),
	('Vloermuziek', 6);
EOQ;

        $this->addSql($sql);

        $sql = <<<EOQ
INSERT INTO `submenuitems` (`naam`, `positie`, `hoofdmenuItem_id`)
VALUES
	('Algemene informatie', 1, 2),
	('Reglementen', 2, 2),
	('Wedstrijdindeling', 3, 2),
	('Uitslagen', 5, 2);
EOQ;

        $this->addSql($sql);
    }

    public function down(Schema $schema) : void
    {

    }
}
