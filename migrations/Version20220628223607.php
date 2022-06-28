<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220628223607 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE products ADD belongs_to_shop_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE products ADD CONSTRAINT FK_B3BA5A5A26CAE23A FOREIGN KEY (belongs_to_shop_id) REFERENCES user_shop (id)');
        $this->addSql('CREATE INDEX IDX_B3BA5A5A26CAE23A ON products (belongs_to_shop_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE products DROP FOREIGN KEY FK_B3BA5A5A26CAE23A');
        $this->addSql('DROP INDEX IDX_B3BA5A5A26CAE23A ON products');
        $this->addSql('ALTER TABLE products DROP belongs_to_shop_id');
    }
}
