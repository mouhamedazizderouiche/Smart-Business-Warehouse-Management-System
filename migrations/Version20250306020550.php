<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250306020550 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE categorie ADD slug VARCHAR(255) DEFAULT NULL, ADD lft INT DEFAULT NULL, ADD rgt INT DEFAULT NULL, ADD lvl INT DEFAULT NULL, CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE parent_id parent_id INT DEFAULT NULL, CHANGE description description VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_497DD634989D9B62 ON categorie (slug)');
        $this->addSql('ALTER TABLE inscription ADD travail VARCHAR(255) NOT NULL, ADD date_inscription DATETIME NOT NULL, CHANGE nom nom VARCHAR(255) NOT NULL, CHANGE prenom prenom VARCHAR(255) NOT NULL, CHANGE email email VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE produit ADD categorie_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE produit ADD CONSTRAINT FK_29A5EC27BCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE produit ADD CONSTRAINT FK_29A5EC27A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_29A5EC27BCF5E72D ON produit (categorie_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_497DD634989D9B62 ON categorie');
        $this->addSql('ALTER TABLE categorie DROP slug, DROP lft, DROP rgt, DROP lvl, CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', CHANGE parent_id parent_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', CHANGE description description LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE inscription DROP travail, DROP date_inscription, CHANGE nom nom VARCHAR(255) DEFAULT NULL, CHANGE prenom prenom VARCHAR(255) DEFAULT NULL, CHANGE email email VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE produit DROP FOREIGN KEY FK_29A5EC27BCF5E72D');
        $this->addSql('ALTER TABLE produit DROP FOREIGN KEY FK_29A5EC27A76ED395');
        $this->addSql('DROP INDEX IDX_29A5EC27BCF5E72D ON produit');
        $this->addSql('ALTER TABLE produit DROP categorie_id');
    }
}
