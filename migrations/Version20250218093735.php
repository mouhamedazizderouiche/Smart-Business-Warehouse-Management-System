<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250218093735 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE evenement (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, type VARCHAR(20) NOT NULL, statut VARCHAR(20) NOT NULL, date_debut DATETIME NOT NULL, date_fin DATETIME NOT NULL, photo VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE evenement_region (id INT AUTO_INCREMENT NOT NULL, evenement_id INT NOT NULL, region_id INT NOT NULL, INDEX IDX_BC9721F4FD02F13 (evenement_id), INDEX IDX_BC9721F498260155 (region_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE region (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, ville VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE evenement_region ADD CONSTRAINT FK_BC9721F4FD02F13 FOREIGN KEY (evenement_id) REFERENCES evenement (id)');
        $this->addSql('ALTER TABLE evenement_region ADD CONSTRAINT FK_BC9721F498260155 FOREIGN KEY (region_id) REFERENCES region (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE evenement_region DROP FOREIGN KEY FK_BC9721F4FD02F13');
        $this->addSql('ALTER TABLE evenement_region DROP FOREIGN KEY FK_BC9721F498260155');
        $this->addSql('DROP TABLE evenement');
        $this->addSql('DROP TABLE evenement_region');
        $this->addSql('DROP TABLE region');
    }
}
