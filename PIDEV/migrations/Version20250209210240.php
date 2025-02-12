<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250209210240 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stock DROP FOREIGN KEY FK_4B3656603EED8672');
        $this->addSql('DROP INDEX IDX_4B3656603EED8672 ON stock');
        $this->addSql('ALTER TABLE stock ADD entrepot_id INT NOT NULL, DROP entrepot_id_id');
        $this->addSql('ALTER TABLE stock ADD CONSTRAINT FK_4B36566072831E97 FOREIGN KEY (entrepot_id) REFERENCES entrepot (id)');
        $this->addSql('CREATE INDEX IDX_4B36566072831E97 ON stock (entrepot_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stock DROP FOREIGN KEY FK_4B36566072831E97');
        $this->addSql('DROP INDEX IDX_4B36566072831E97 ON stock');
        $this->addSql('ALTER TABLE stock ADD entrepot_id_id INT DEFAULT NULL, DROP entrepot_id');
        $this->addSql('ALTER TABLE stock ADD CONSTRAINT FK_4B3656603EED8672 FOREIGN KEY (entrepot_id_id) REFERENCES entrepot (id)');
        $this->addSql('CREATE INDEX IDX_4B3656603EED8672 ON stock (entrepot_id_id)');
    }
}
