<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250315201815 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE prix_vente ADD produit_id INT NOT NULL');
        $this->addSql('ALTER TABLE prix_vente ADD CONSTRAINT FK_68C54412F347EFB FOREIGN KEY (produit_id) REFERENCES produit (id)');
        $this->addSql('CREATE INDEX IDX_68C54412F347EFB ON prix_vente (produit_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE prix_vente DROP FOREIGN KEY FK_68C54412F347EFB');
        $this->addSql('DROP INDEX IDX_68C54412F347EFB ON prix_vente');
        $this->addSql('ALTER TABLE prix_vente DROP produit_id');
    }
}
