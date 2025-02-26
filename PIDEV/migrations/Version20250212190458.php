<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250212190458 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE entrepot CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE fournisseur DROP contact, DROP email, DROP prenom, DROP telephone');
        $this->addSql('ALTER TABLE stock ADD entrepot_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', DROP produit_id, DROP fournisseur_id, CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', CHANGE date_modification date_ajout DATETIME NOT NULL');
        $this->addSql('ALTER TABLE stock ADD CONSTRAINT FK_4B36566072831E97 FOREIGN KEY (entrepot_id) REFERENCES entrepot (id)');
        $this->addSql('CREATE INDEX IDX_4B36566072831E97 ON stock (entrepot_id)');
        $this->addSql('ALTER TABLE stock_fournisseur ADD CONSTRAINT FK_E9506C06DCD6110 FOREIGN KEY (stock_id) REFERENCES stock (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE stock_fournisseur ADD CONSTRAINT FK_E9506C06670C757F FOREIGN KEY (fournisseur_id) REFERENCES fournisseur (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE entrepot CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE fournisseur ADD contact VARCHAR(255) NOT NULL, ADD email VARCHAR(255) NOT NULL, ADD prenom VARCHAR(255) NOT NULL, ADD telephone VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE stock DROP FOREIGN KEY FK_4B36566072831E97');
        $this->addSql('DROP INDEX IDX_4B36566072831E97 ON stock');
        $this->addSql('ALTER TABLE stock ADD produit_id INT NOT NULL, ADD fournisseur_id INT NOT NULL, DROP entrepot_id, CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE date_ajout date_modification DATETIME NOT NULL');
        $this->addSql('ALTER TABLE stock_fournisseur DROP FOREIGN KEY FK_E9506C06DCD6110');
        $this->addSql('ALTER TABLE stock_fournisseur DROP FOREIGN KEY FK_E9506C06670C757F');
    }
}
