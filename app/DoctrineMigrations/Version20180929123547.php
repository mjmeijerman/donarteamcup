<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180929123547 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $sql = <<<EOQ
INSERT INTO `instellingen` (`instelling`, `datum`, `aantal`, `gewijzigd`)
VALUES
	('Max aantal teams', NULL, 100, :now);
EOQ;

        $this->addSql($sql, ['now' => new \DateTime()], ['now' => Type::DATETIME]);
    }

    public function down(Schema $schema) : void
    {
    }
}
