<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230321152338 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE abonnement ADD type_abonnement VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE gallerie_vendeur DROP FOREIGN KEY FK_A78061F4858C065E');
        $this->addSql('ALTER TABLE gallerie_vendeur ADD CONSTRAINT FK_A78061F4858C065E FOREIGN KEY (vendeur_id) REFERENCES vendeur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE vendeur ADD type_etablissement VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE abonnement DROP type_abonnement');
        $this->addSql('ALTER TABLE gallerie_vendeur DROP FOREIGN KEY FK_A78061F4858C065E');
        $this->addSql('ALTER TABLE gallerie_vendeur ADD CONSTRAINT FK_A78061F4858C065E FOREIGN KEY (vendeur_id) REFERENCES vendeur (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE vendeur DROP type_etablissement');
    }
}
