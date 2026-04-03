<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260304000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add queue_item table for async PDF processing';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE queue_item (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            type VARCHAR(50) NOT NULL,
            payload JSON DEFAULT NULL,
            status VARCHAR(20) NOT NULL DEFAULT \'pending\',
            result_file VARCHAR(255) DEFAULT NULL,
            error_message LONGTEXT DEFAULT NULL,
            created_at DATETIME NOT NULL,
            processed_at DATETIME DEFAULT NULL,
            INDEX IDX_QUEUE_USER (user_id),
            INDEX IDX_QUEUE_STATUS (status),
            PRIMARY KEY(id),
            CONSTRAINT FK_QUEUE_USER FOREIGN KEY (user_id) REFERENCES `user` (id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE queue_item');
    }
}
