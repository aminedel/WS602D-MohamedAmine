<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260303000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add ResetPasswordRequest table for password reset functionality';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE reset_password_request (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            selector VARCHAR(20) NOT NULL,
            hashed_token VARCHAR(100) NOT NULL,
            requested_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_7CE748AA76ED395 (user_id),
            PRIMARY KEY(id),
            CONSTRAINT FK_RESET_PASSWORD_USER FOREIGN KEY (user_id) REFERENCES `user` (id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE reset_password_request');
    }
}
