<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250315195722 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE prix_vente ADD CONSTRAINT FK_68C54412F347EFB FOREIGN KEY (produit_id) REFERENCES produit (id)');
        $this->addSql('CREATE INDEX IDX_68C54412F347EFB ON prix_vente (produit_id)');
        $this->addSql('ALTER TABLE produit DROP FOREIGN KEY FK_29A5EC276F80E78');
        $this->addSql('DROP INDEX IDX_29A5EC276F80E78 ON produit');
        $this->addSql('ALTER TABLE produit DROP prix_vente_id');
        $this->addSql('ALTER TABLE produit RENAME INDEX uniq_identifier_email TO UNIQ_NAME_PRODUCT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE produit ADD prix_vente_id INT NOT NULL');
        $this->addSql('ALTER TABLE produit ADD CONSTRAINT FK_29A5EC276F80E78 FOREIGN KEY (prix_vente_id) REFERENCES prix_vente (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_29A5EC276F80E78 ON produit (prix_vente_id)');
        $this->addSql('ALTER TABLE produit RENAME INDEX uniq_name_product TO UNIQ_IDENTIFIER_EMAIL');
        $this->addSql('ALTER TABLE prix_vente DROP FOREIGN KEY FK_68C54412F347EFB');
        $this->addSql('DROP INDEX IDX_68C54412F347EFB ON prix_vente');
    }
}
