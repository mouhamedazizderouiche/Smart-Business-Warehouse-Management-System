<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250218212711 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE entrepot (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', stock_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', nom VARCHAR(255) NOT NULL, adresse VARCHAR(255) NOT NULL, ville VARCHAR(255) DEFAULT NULL, espace DOUBLE PRECISION NOT NULL, UNIQUE INDEX UNIQ_D805175A6C6E55B5 (nom), INDEX IDX_D805175ADCD6110 (stock_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE evenement (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, type VARCHAR(20) NOT NULL, statut VARCHAR(20) NOT NULL, date_debut DATETIME NOT NULL, date_fin DATETIME NOT NULL, photo VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE evenement_region (id INT AUTO_INCREMENT NOT NULL, evenement_id INT NOT NULL, region_id INT NOT NULL, INDEX IDX_BC9721F4FD02F13 (evenement_id), INDEX IDX_BC9721F498260155 (region_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE fournisseur (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', nom VARCHAR(255) NOT NULL, adresse VARCHAR(255) NOT NULL, telephone VARCHAR(20) NOT NULL, email VARCHAR(180) NOT NULL, actif VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE fournisseur_stock (fournisseur_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', stock_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_5660E395670C757F (fournisseur_id), INDEX IDX_5660E395DCD6110 (stock_id), PRIMARY KEY(fournisseur_id, stock_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE region (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, ville VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE stock (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', produit_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', quantite INT NOT NULL, date_entree DATETIME NOT NULL, date_sortie DATETIME DEFAULT NULL, INDEX IDX_4B365660F347EFB (produit_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE entrepot ADD CONSTRAINT FK_D805175ADCD6110 FOREIGN KEY (stock_id) REFERENCES stock (id)');
        $this->addSql('ALTER TABLE evenement_region ADD CONSTRAINT FK_BC9721F4FD02F13 FOREIGN KEY (evenement_id) REFERENCES evenement (id)');
        $this->addSql('ALTER TABLE evenement_region ADD CONSTRAINT FK_BC9721F498260155 FOREIGN KEY (region_id) REFERENCES region (id)');
        $this->addSql('ALTER TABLE fournisseur_stock ADD CONSTRAINT FK_5660E395670C757F FOREIGN KEY (fournisseur_id) REFERENCES fournisseur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE fournisseur_stock ADD CONSTRAINT FK_5660E395DCD6110 FOREIGN KEY (stock_id) REFERENCES stock (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE stock ADD CONSTRAINT FK_4B365660F347EFB FOREIGN KEY (produit_id) REFERENCES produit (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE entrepot DROP FOREIGN KEY FK_D805175ADCD6110');
        $this->addSql('ALTER TABLE evenement_region DROP FOREIGN KEY FK_BC9721F4FD02F13');
        $this->addSql('ALTER TABLE evenement_region DROP FOREIGN KEY FK_BC9721F498260155');
        $this->addSql('ALTER TABLE fournisseur_stock DROP FOREIGN KEY FK_5660E395670C757F');
        $this->addSql('ALTER TABLE fournisseur_stock DROP FOREIGN KEY FK_5660E395DCD6110');
        $this->addSql('ALTER TABLE stock DROP FOREIGN KEY FK_4B365660F347EFB');
        $this->addSql('DROP TABLE entrepot');
        $this->addSql('DROP TABLE evenement');
        $this->addSql('DROP TABLE evenement_region');
        $this->addSql('DROP TABLE fournisseur');
        $this->addSql('DROP TABLE fournisseur_stock');
        $this->addSql('DROP TABLE region');
        $this->addSql('DROP TABLE stock');
    }
}
