<?php

declare(strict_types = 1)
;

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260301000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create initial database schema with User, Plan, Generation, UserContact, and GenerationUserContact tables';
    }

    public function up(Schema $schema): void
    {
        // Plan table
        $this->addSql('CREATE TABLE plan (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(100) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            limit_generation INT DEFAULT NULL,
            image VARCHAR(255) DEFAULT NULL,
            role VARCHAR(50) DEFAULT NULL,
            price NUMERIC(10, 2) DEFAULT NULL,
            special_price NUMERIC(10, 2) DEFAULT NULL,
            special_price_from DATE DEFAULT NULL,
            special_price_to DATE DEFAULT NULL,
            active TINYINT(1) NOT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // User table
        $this->addSql('CREATE TABLE `user` (
            id INT AUTO_INCREMENT NOT NULL,
            plan_id INT DEFAULT NULL,
            email VARCHAR(180) NOT NULL,
            roles JSON NOT NULL,
            password VARCHAR(255) NOT NULL,
            lastname VARCHAR(100) NOT NULL,
            firstname VARCHAR(100) NOT NULL,
            dob DATE DEFAULT NULL,
            photo VARCHAR(255) DEFAULT NULL,
            favorite_color VARCHAR(50) DEFAULT NULL,
            phone VARCHAR(20) DEFAULT NULL,
            created_at DATETIME NOT NULL,
            is_verified TINYINT(1) NOT NULL,
            UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email),
            INDEX IDX_8D93D649E899029B (plan_id),
            PRIMARY KEY(id),
            CONSTRAINT FK_USER_PLAN FOREIGN KEY (plan_id) REFERENCES plan (id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Generation table
        $this->addSql('CREATE TABLE generation (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            file VARCHAR(255) NOT NULL,
            created_at DATETIME NOT NULL,
            type VARCHAR(50) DEFAULT NULL,
            source_url LONGTEXT DEFAULT NULL,
            INDEX IDX_66E23B4CA76ED395 (user_id),
            PRIMARY KEY(id),
            CONSTRAINT FK_GENERATION_USER FOREIGN KEY (user_id) REFERENCES `user` (id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // UserContact table
        $this->addSql('CREATE TABLE user_contact (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            lastname VARCHAR(100) NOT NULL,
            firstname VARCHAR(100) NOT NULL,
            email VARCHAR(180) NOT NULL,
            INDEX IDX_146FF832A76ED395 (user_id),
            PRIMARY KEY(id),
            CONSTRAINT FK_USERCONTACT_USER FOREIGN KEY (user_id) REFERENCES `user` (id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // GenerationUserContact table
        $this->addSql('CREATE TABLE generation_user_contact (
            id INT AUTO_INCREMENT NOT NULL,
            generation_id INT NOT NULL,
            user_contact_id INT NOT NULL,
            INDEX IDX_GUC_GENERATION (generation_id),
            INDEX IDX_GUC_USERCONTACT (user_contact_id),
            PRIMARY KEY(id),
            CONSTRAINT FK_GUC_GENERATION FOREIGN KEY (generation_id) REFERENCES generation (id),
            CONSTRAINT FK_GUC_USERCONTACT FOREIGN KEY (user_contact_id) REFERENCES user_contact (id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE generation_user_contact');
        $this->addSql('DROP TABLE user_contact');
        $this->addSql('DROP TABLE generation');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE plan');
    }
}
