<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230504170117 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE abonnement (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, code VARCHAR(255) NOT NULL, montant INT NOT NULL, description VARCHAR(255) NOT NULL, duree_abonnement INT NOT NULL, devise VARCHAR(255) NOT NULL, type_abonnement VARCHAR(255) NOT NULL, en_nom VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE abonnement_vendeur (id INT AUTO_INCREMENT NOT NULL, vendeur_id INT DEFAULT NULL, abonnement_id INT DEFAULT NULL, expiration VARCHAR(255) NOT NULL, date_activation VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_1AA624FE858C065E (vendeur_id), INDEX IDX_1AA624FEF1D74413 (abonnement_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE administrateur (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_32EB52E8E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE categorie (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, en_nom VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE gallerie_vendeur (id INT AUTO_INCREMENT NOT NULL, vendeur_id INT DEFAULT NULL, image LONGTEXT NOT NULL, INDEX IDX_A78061F4858C065E (vendeur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE geolocalisation (id INT AUTO_INCREMENT NOT NULL, longitude VARCHAR(255) NOT NULL, latitude VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE horaire_ouverture (id INT AUTO_INCREMENT NOT NULL, vendeur_id INT DEFAULT NULL, jour VARCHAR(255) NOT NULL, heure_ouverture VARCHAR(255) NOT NULL, heure_fermeture VARCHAR(255) NOT NULL, INDEX IDX_D97D2495858C065E (vendeur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lien_reseaux_sociaux (id INT AUTO_INCREMENT NOT NULL, vendeur_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, username VARCHAR(255) NOT NULL, INDEX IDX_9F5AE9C4858C065E (vendeur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE prestations (id INT AUTO_INCREMENT NOT NULL, sous_categorie_id INT DEFAULT NULL, vendeur_id INT DEFAULT NULL, prix VARCHAR(255) NOT NULL, duree VARCHAR(255) NOT NULL, produit VARCHAR(255) NOT NULL, devise VARCHAR(255) NOT NULL, INDEX IDX_B338D8D1365BF48 (sous_categorie_id), INDEX IDX_B338D8D1858C065E (vendeur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE rendez_vous (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT DEFAULT NULL, vendeur_id INT DEFAULT NULL, date VARCHAR(255) NOT NULL, prestation VARCHAR(255) NOT NULL, heure VARCHAR(255) NOT NULL, prix VARCHAR(255) NOT NULL, statut VARCHAR(255) NOT NULL, INDEX IDX_65E8AA0AFB88E14F (utilisateur_id), INDEX IDX_65E8AA0A858C065E (vendeur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sous_categorie (id INT AUTO_INCREMENT NOT NULL, categorie_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, en_nom VARCHAR(255) NOT NULL, INDEX IDX_52743D7BBCF5E72D (categorie_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE utilisateurs (id INT AUTO_INCREMENT NOT NULL, geolocalisation_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, mobile VARCHAR(255) NOT NULL, photo VARCHAR(255) NOT NULL, role JSON NOT NULL, date_creation VARCHAR(255) NOT NULL, login VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, pays VARCHAR(255) NOT NULL, langue VARCHAR(255) NOT NULL, code_postal VARCHAR(255) NOT NULL, token VARCHAR(255) DEFAULT NULL, compte_actif VARCHAR(3) DEFAULT NULL, compte_confirme VARCHAR(3) DEFAULT NULL, UNIQUE INDEX UNIQ_497B315EE7927C74 (email), UNIQUE INDEX UNIQ_497B315EAA08CB10 (login), INDEX IDX_497B315E4046BF3D (geolocalisation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vendeur (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT DEFAULT NULL, geolocalisation_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, mail VARCHAR(255) NOT NULL, mobile VARCHAR(255) NOT NULL, adresse VARCHAR(255) NOT NULL, corps_metier VARCHAR(255) NOT NULL, stripe_account_id VARCHAR(255) DEFAULT NULL, categorie JSON NOT NULL, nom_responsable VARCHAR(255) NOT NULL, sciem VARCHAR(255) NOT NULL, poste_responsable VARCHAR(255) NOT NULL, compte_actif INT NOT NULL, compte_confirme INT NOT NULL, code_confirmation VARCHAR(255) NOT NULL, logo LONGTEXT DEFAULT NULL, type_etablissement VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_7AF499965126AC48 (mail), UNIQUE INDEX UNIQ_7AF499963C7323E0 (mobile), INDEX IDX_7AF49996FB88E14F (utilisateur_id), INDEX IDX_7AF499964046BF3D (geolocalisation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vendeur_note (id INT AUTO_INCREMENT NOT NULL, vendeur_id INT DEFAULT NULL, utilisateur_id INT DEFAULT NULL, note VARCHAR(255) NOT NULL, INDEX IDX_6B8BBCE1858C065E (vendeur_id), INDEX IDX_6B8BBCE1FB88E14F (utilisateur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vendeur_prestation_principale (id INT AUTO_INCREMENT NOT NULL, vendeur_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, INDEX IDX_29E34E12858C065E (vendeur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vendeur_sous_categorie (id INT AUTO_INCREMENT NOT NULL, vendeur_id INT DEFAULT NULL, sous_categorie VARCHAR(255) NOT NULL, INDEX IDX_F9DFADAC858C065E (vendeur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vendeur_sous_prestation (id INT AUTO_INCREMENT NOT NULL, vendeur_prestation_principale_id INT DEFAULT NULL, titre VARCHAR(255) NOT NULL, sous_titre VARCHAR(255) NOT NULL, prix VARCHAR(255) NOT NULL, duree VARCHAR(255) NOT NULL, INDEX IDX_AFAC36A02E1A634E (vendeur_prestation_principale_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE abonnement_vendeur ADD CONSTRAINT FK_1AA624FE858C065E FOREIGN KEY (vendeur_id) REFERENCES vendeur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE abonnement_vendeur ADD CONSTRAINT FK_1AA624FEF1D74413 FOREIGN KEY (abonnement_id) REFERENCES abonnement (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE gallerie_vendeur ADD CONSTRAINT FK_A78061F4858C065E FOREIGN KEY (vendeur_id) REFERENCES vendeur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE horaire_ouverture ADD CONSTRAINT FK_D97D2495858C065E FOREIGN KEY (vendeur_id) REFERENCES vendeur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE lien_reseaux_sociaux ADD CONSTRAINT FK_9F5AE9C4858C065E FOREIGN KEY (vendeur_id) REFERENCES vendeur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE prestations ADD CONSTRAINT FK_B338D8D1365BF48 FOREIGN KEY (sous_categorie_id) REFERENCES sous_categorie (id)');
        $this->addSql('ALTER TABLE prestations ADD CONSTRAINT FK_B338D8D1858C065E FOREIGN KEY (vendeur_id) REFERENCES vendeur (id)');
        $this->addSql('ALTER TABLE rendez_vous ADD CONSTRAINT FK_65E8AA0AFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs (id)');
        $this->addSql('ALTER TABLE rendez_vous ADD CONSTRAINT FK_65E8AA0A858C065E FOREIGN KEY (vendeur_id) REFERENCES vendeur (id)');
        $this->addSql('ALTER TABLE sous_categorie ADD CONSTRAINT FK_52743D7BBCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie (id)');
        $this->addSql('ALTER TABLE utilisateurs ADD CONSTRAINT FK_497B315E4046BF3D FOREIGN KEY (geolocalisation_id) REFERENCES geolocalisation (id)');
        $this->addSql('ALTER TABLE vendeur ADD CONSTRAINT FK_7AF49996FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs (id)');
        $this->addSql('ALTER TABLE vendeur ADD CONSTRAINT FK_7AF499964046BF3D FOREIGN KEY (geolocalisation_id) REFERENCES geolocalisation (id)');
        $this->addSql('ALTER TABLE vendeur_note ADD CONSTRAINT FK_6B8BBCE1858C065E FOREIGN KEY (vendeur_id) REFERENCES vendeur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE vendeur_note ADD CONSTRAINT FK_6B8BBCE1FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs (id)');
        $this->addSql('ALTER TABLE vendeur_prestation_principale ADD CONSTRAINT FK_29E34E12858C065E FOREIGN KEY (vendeur_id) REFERENCES vendeur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE vendeur_sous_categorie ADD CONSTRAINT FK_F9DFADAC858C065E FOREIGN KEY (vendeur_id) REFERENCES vendeur (id)');
        $this->addSql('ALTER TABLE vendeur_sous_prestation ADD CONSTRAINT FK_AFAC36A02E1A634E FOREIGN KEY (vendeur_prestation_principale_id) REFERENCES vendeur_prestation_principale (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE abonnement_vendeur DROP FOREIGN KEY FK_1AA624FE858C065E');
        $this->addSql('ALTER TABLE abonnement_vendeur DROP FOREIGN KEY FK_1AA624FEF1D74413');
        $this->addSql('ALTER TABLE gallerie_vendeur DROP FOREIGN KEY FK_A78061F4858C065E');
        $this->addSql('ALTER TABLE horaire_ouverture DROP FOREIGN KEY FK_D97D2495858C065E');
        $this->addSql('ALTER TABLE lien_reseaux_sociaux DROP FOREIGN KEY FK_9F5AE9C4858C065E');
        $this->addSql('ALTER TABLE prestations DROP FOREIGN KEY FK_B338D8D1365BF48');
        $this->addSql('ALTER TABLE prestations DROP FOREIGN KEY FK_B338D8D1858C065E');
        $this->addSql('ALTER TABLE rendez_vous DROP FOREIGN KEY FK_65E8AA0AFB88E14F');
        $this->addSql('ALTER TABLE rendez_vous DROP FOREIGN KEY FK_65E8AA0A858C065E');
        $this->addSql('ALTER TABLE sous_categorie DROP FOREIGN KEY FK_52743D7BBCF5E72D');
        $this->addSql('ALTER TABLE utilisateurs DROP FOREIGN KEY FK_497B315E4046BF3D');
        $this->addSql('ALTER TABLE vendeur DROP FOREIGN KEY FK_7AF49996FB88E14F');
        $this->addSql('ALTER TABLE vendeur DROP FOREIGN KEY FK_7AF499964046BF3D');
        $this->addSql('ALTER TABLE vendeur_note DROP FOREIGN KEY FK_6B8BBCE1858C065E');
        $this->addSql('ALTER TABLE vendeur_note DROP FOREIGN KEY FK_6B8BBCE1FB88E14F');
        $this->addSql('ALTER TABLE vendeur_prestation_principale DROP FOREIGN KEY FK_29E34E12858C065E');
        $this->addSql('ALTER TABLE vendeur_sous_categorie DROP FOREIGN KEY FK_F9DFADAC858C065E');
        $this->addSql('ALTER TABLE vendeur_sous_prestation DROP FOREIGN KEY FK_AFAC36A02E1A634E');
        $this->addSql('DROP TABLE abonnement');
        $this->addSql('DROP TABLE abonnement_vendeur');
        $this->addSql('DROP TABLE administrateur');
        $this->addSql('DROP TABLE categorie');
        $this->addSql('DROP TABLE gallerie_vendeur');
        $this->addSql('DROP TABLE geolocalisation');
        $this->addSql('DROP TABLE horaire_ouverture');
        $this->addSql('DROP TABLE lien_reseaux_sociaux');
        $this->addSql('DROP TABLE prestations');
        $this->addSql('DROP TABLE rendez_vous');
        $this->addSql('DROP TABLE sous_categorie');
        $this->addSql('DROP TABLE utilisateurs');
        $this->addSql('DROP TABLE vendeur');
        $this->addSql('DROP TABLE vendeur_note');
        $this->addSql('DROP TABLE vendeur_prestation_principale');
        $this->addSql('DROP TABLE vendeur_sous_categorie');
        $this->addSql('DROP TABLE vendeur_sous_prestation');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
