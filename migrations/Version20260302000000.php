<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260302000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Tool entity, Plan-Tool ManyToMany, stripe_price_id to Plan, stripe_customer_id to User';
    }

    public function up(Schema $schema): void
    {
        // Tool table
        $this->addSql('CREATE TABLE tool (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(100) NOT NULL,
            slug VARCHAR(100) NOT NULL,
            UNIQUE INDEX UNIQ_20F33ED1989D9B62 (slug),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Plan-Tool join table
        $this->addSql('CREATE TABLE plan_tool (
            plan_id INT NOT NULL,
            tool_id INT NOT NULL,
            INDEX IDX_PLAN_TOOL_PLAN (plan_id),
            INDEX IDX_PLAN_TOOL_TOOL (tool_id),
            PRIMARY KEY(plan_id, tool_id),
            CONSTRAINT FK_PLAN_TOOL_PLAN FOREIGN KEY (plan_id) REFERENCES plan (id) ON DELETE CASCADE,
            CONSTRAINT FK_PLAN_TOOL_TOOL FOREIGN KEY (tool_id) REFERENCES tool (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Add stripe_price_id to plan
        $this->addSql('ALTER TABLE plan ADD stripe_price_id VARCHAR(255) DEFAULT NULL');

        // Add stripe_customer_id to user
        $this->addSql('ALTER TABLE `user` ADD stripe_customer_id VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` DROP stripe_customer_id');
        $this->addSql('ALTER TABLE plan DROP stripe_price_id');
        $this->addSql('DROP TABLE plan_tool');
        $this->addSql('DROP TABLE tool');
    }
}
