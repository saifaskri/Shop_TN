<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220613222503 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_shop ADD owned_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user_shop ADD CONSTRAINT FK_D6EB006B5E70BCD7 FOREIGN KEY (owned_by_id) REFERENCES `user` (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D6EB006B5E70BCD7 ON user_shop (owned_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_shop DROP FOREIGN KEY FK_D6EB006B5E70BCD7');
        $this->addSql('DROP INDEX UNIQ_D6EB006B5E70BCD7 ON user_shop');
        $this->addSql('ALTER TABLE user_shop DROP owned_by_id');
    }
}
