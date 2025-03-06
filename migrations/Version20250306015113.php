<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250306015113 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE categorie ADD lft INT DEFAULT NULL, ADD rgt INT DEFAULT NULL, ADD lvl INT DEFAULT NULL, CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE parent_id parent_id INT DEFAULT NULL, CHANGE description description VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE inscription ADD travail VARCHAR(255) NOT NULL, ADD date_inscription DATETIME NOT NULL, CHANGE nom nom VARCHAR(255) NOT NULL, CHANGE prenom prenom VARCHAR(255) NOT NULL, CHANGE email email VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE produit DROP FOREIGN KEY FK_29A5EC27BCF5E72D');
        $this->addSql('ALTER TABLE produit ADD image_name VARCHAR(255) DEFAULT NULL, ADD rate NUMERIC(2, 1) DEFAULT NULL, DROP url_image_produit, CHANGE categorie_id categorie_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE produit ADD CONSTRAINT FK_29A5EC27BCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE categorie DROP lft, DROP rgt, DROP lvl, CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', CHANGE parent_id parent_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', CHANGE description description LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE inscription DROP travail, DROP date_inscription, CHANGE nom nom VARCHAR(255) DEFAULT NULL, CHANGE prenom prenom VARCHAR(255) DEFAULT NULL, CHANGE email email VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE produit DROP FOREIGN KEY FK_29A5EC27BCF5E72D');
        $this->addSql('ALTER TABLE produit ADD url_image_produit VARCHAR(255) NOT NULL, DROP image_name, DROP rate, CHANGE categorie_id categorie_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE produit ADD CONSTRAINT FK_29A5EC27BCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie (id)');
    }
}
