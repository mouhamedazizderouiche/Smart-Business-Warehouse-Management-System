<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250225213018 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notifications DROP FOREIGN KEY FK_6000B0D3A76ED395');
        $this->addSql('DROP INDEX IDX_6000B0D3A76ED395 ON notifications');
        $this->addSql('ALTER TABLE notifications ADD is_for_admins TINYINT(1) NOT NULL, DROP user_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notifications ADD user_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', DROP is_for_admins');
        $this->addSql('ALTER TABLE notifications ADD CONSTRAINT FK_6000B0D3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_6000B0D3A76ED395 ON notifications (user_id)');
    }
}
