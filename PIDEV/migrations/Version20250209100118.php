<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250209100118 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE produit (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, prix_unitaire NUMERIC(12, 2) NOT NULL, url_image_produit LONGBLOB DEFAULT NULL, date_création DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE produit_fournisseur (produit_id INT NOT NULL, fournisseur_id INT NOT NULL, INDEX IDX_48868EB6F347EFB (produit_id), INDEX IDX_48868EB6670C757F (fournisseur_id), PRIMARY KEY(produit_id, fournisseur_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE stock_fournisseur (stock_id INT NOT NULL, fournisseur_id INT NOT NULL, INDEX IDX_E9506C06DCD6110 (stock_id), INDEX IDX_E9506C06670C757F (fournisseur_id), PRIMARY KEY(stock_id, fournisseur_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE produit_fournisseur ADD CONSTRAINT FK_48868EB6F347EFB FOREIGN KEY (produit_id) REFERENCES produit (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE produit_fournisseur ADD CONSTRAINT FK_48868EB6670C757F FOREIGN KEY (fournisseur_id) REFERENCES fournisseur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE stock_fournisseur ADD CONSTRAINT FK_E9506C06DCD6110 FOREIGN KEY (stock_id) REFERENCES stock (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE stock_fournisseur ADD CONSTRAINT FK_E9506C06670C757F FOREIGN KEY (fournisseur_id) REFERENCES fournisseur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE stock ADD entrepot_id_id INT DEFAULT NULL, DROP fournisseur_id');
        $this->addSql('ALTER TABLE stock ADD CONSTRAINT FK_4B3656603EED8672 FOREIGN KEY (entrepot_id_id) REFERENCES entrepot (id)');
        $this->addSql('CREATE INDEX IDX_4B3656603EED8672 ON stock (entrepot_id_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE produit_fournisseur DROP FOREIGN KEY FK_48868EB6F347EFB');
        $this->addSql('ALTER TABLE produit_fournisseur DROP FOREIGN KEY FK_48868EB6670C757F');
        $this->addSql('ALTER TABLE stock_fournisseur DROP FOREIGN KEY FK_E9506C06DCD6110');
        $this->addSql('ALTER TABLE stock_fournisseur DROP FOREIGN KEY FK_E9506C06670C757F');
        $this->addSql('DROP TABLE produit');
        $this->addSql('DROP TABLE produit_fournisseur');
        $this->addSql('DROP TABLE stock_fournisseur');
        $this->addSql('ALTER TABLE stock DROP FOREIGN KEY FK_4B3656603EED8672');
        $this->addSql('DROP INDEX IDX_4B3656603EED8672 ON stock');
        $this->addSql('ALTER TABLE stock ADD fournisseur_id INT NOT NULL, DROP entrepot_id_id');
    }
}
