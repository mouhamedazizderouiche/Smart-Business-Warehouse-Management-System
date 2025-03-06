<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250306035114 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE commentaire (id INT AUTO_INCREMENT NOT NULL, produit_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', user_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', auteur VARCHAR(255) NOT NULL, contenu LONGTEXT NOT NULL, note NUMERIC(2, 1) NOT NULL, date_creation DATETIME NOT NULL, INDEX IDX_67F068BCF347EFB (produit_id), INDEX IDX_67F068BCA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE commentaire_event (id INT AUTO_INCREMENT NOT NULL, user_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', evenement_id INT NOT NULL, contenu LONGTEXT NOT NULL, date_creation DATETIME NOT NULL, INDEX IDX_BE22C2BBA76ED395 (user_id), INDEX IDX_BE22C2BBFD02F13 (evenement_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE inscription (id INT AUTO_INCREMENT NOT NULL, user_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', evenement_id INT NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, num_tel VARCHAR(20) DEFAULT NULL, travail VARCHAR(255) NOT NULL, date_inscription DATETIME NOT NULL, INDEX IDX_5E90F6D6A76ED395 (user_id), INDEX IDX_5E90F6D6FD02F13 (evenement_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BCF347EFB FOREIGN KEY (produit_id) REFERENCES produit (id)');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BCA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE commentaire_event ADD CONSTRAINT FK_BE22C2BBA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE commentaire_event ADD CONSTRAINT FK_BE22C2BBFD02F13 FOREIGN KEY (evenement_id) REFERENCES evenement (id)');
        $this->addSql('ALTER TABLE inscription ADD CONSTRAINT FK_5E90F6D6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE inscription ADD CONSTRAINT FK_5E90F6D6FD02F13 FOREIGN KEY (evenement_id) REFERENCES evenement (id)');
        $this->addSql('ALTER TABLE categorie ADD lft INT DEFAULT NULL, ADD rgt INT DEFAULT NULL, ADD lvl INT DEFAULT NULL, CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE parent_id parent_id INT DEFAULT NULL, CHANGE description description VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE produit DROP FOREIGN KEY FK_29A5EC27BCF5E72D');
        $this->addSql('ALTER TABLE produit ADD image_name VARCHAR(255) DEFAULT NULL, ADD rate NUMERIC(2, 1) DEFAULT NULL, DROP url_image_produit, CHANGE categorie_id categorie_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE produit ADD CONSTRAINT FK_29A5EC27BCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BCF347EFB');
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BCA76ED395');
        $this->addSql('ALTER TABLE commentaire_event DROP FOREIGN KEY FK_BE22C2BBA76ED395');
        $this->addSql('ALTER TABLE commentaire_event DROP FOREIGN KEY FK_BE22C2BBFD02F13');
        $this->addSql('ALTER TABLE inscription DROP FOREIGN KEY FK_5E90F6D6A76ED395');
        $this->addSql('ALTER TABLE inscription DROP FOREIGN KEY FK_5E90F6D6FD02F13');
        $this->addSql('DROP TABLE commentaire');
        $this->addSql('DROP TABLE commentaire_event');
        $this->addSql('DROP TABLE inscription');
        $this->addSql('ALTER TABLE categorie DROP lft, DROP rgt, DROP lvl, CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', CHANGE parent_id parent_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', CHANGE description description LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE produit DROP FOREIGN KEY FK_29A5EC27BCF5E72D');
        $this->addSql('ALTER TABLE produit ADD url_image_produit VARCHAR(255) NOT NULL, DROP image_name, DROP rate, CHANGE categorie_id categorie_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE produit ADD CONSTRAINT FK_29A5EC27BCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie (id)');
    }
}
