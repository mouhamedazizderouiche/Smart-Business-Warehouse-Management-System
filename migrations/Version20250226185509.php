<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250226185509 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE categorie DROP lft, DROP rgt, DROP lvl, CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', CHANGE parent_id parent_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', CHANGE slug slug VARCHAR(255) DEFAULT NULL, CHANGE description description LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE produit CHANGE categorie_id categorie_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE user ADD face_token VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE categorie ADD lft INT NOT NULL, ADD rgt INT NOT NULL, ADD lvl INT NOT NULL, CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE parent_id parent_id INT DEFAULT NULL, CHANGE description description VARCHAR(255) DEFAULT NULL, CHANGE slug slug VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE produit CHANGE categorie_id categorie_id INT NOT NULL');
        $this->addSql('ALTER TABLE user DROP face_token');
    }
}
