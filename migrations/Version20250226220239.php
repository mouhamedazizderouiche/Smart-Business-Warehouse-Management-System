<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250226220239 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE message_reclamation (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', user_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', reclamation_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', contenu VARCHAR(255) NOT NULL, date_message DATETIME NOT NULL, INDEX IDX_AE1F3AFDA76ED395 (user_id), INDEX IDX_AE1F3AFD2D6BA2D9 (reclamation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE notifications (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', user_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, is_read TINYINT(1) NOT NULL, is_for_admins TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_6000B0D3A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reclamations (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', user_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', tag_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', date_reclamation DATETIME NOT NULL, rate INT NOT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, statut VARCHAR(255) NOT NULL, INDEX IDX_1CAD6B76A76ED395 (user_id), INDEX IDX_1CAD6B76BAD26311 (tag_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reset_password_request (id VARCHAR(255) NOT NULL, user_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_7CE748AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tag (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_389B7835E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', email VARCHAR(180) NOT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, travail VARCHAR(255) NOT NULL, date_iscri DATETIME DEFAULT NULL, photo_url VARCHAR(255) DEFAULT NULL, is_verified TINYINT(1) NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, num_tel INT NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE message_reclamation ADD CONSTRAINT FK_AE1F3AFDA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE message_reclamation ADD CONSTRAINT FK_AE1F3AFD2D6BA2D9 FOREIGN KEY (reclamation_id) REFERENCES reclamations (id)');
        $this->addSql('ALTER TABLE notifications ADD CONSTRAINT FK_6000B0D3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reclamations ADD CONSTRAINT FK_1CAD6B76A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reclamations ADD CONSTRAINT FK_1CAD6B76BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE message_reclamation DROP FOREIGN KEY FK_AE1F3AFDA76ED395');
        $this->addSql('ALTER TABLE message_reclamation DROP FOREIGN KEY FK_AE1F3AFD2D6BA2D9');
        $this->addSql('ALTER TABLE notifications DROP FOREIGN KEY FK_6000B0D3A76ED395');
        $this->addSql('ALTER TABLE reclamations DROP FOREIGN KEY FK_1CAD6B76A76ED395');
        $this->addSql('ALTER TABLE reclamations DROP FOREIGN KEY FK_1CAD6B76BAD26311');
        $this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395');
        $this->addSql('DROP TABLE message_reclamation');
        $this->addSql('DROP TABLE notifications');
        $this->addSql('DROP TABLE reclamations');
        $this->addSql('DROP TABLE reset_password_request');
        $this->addSql('DROP TABLE tag');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
