<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230323102524 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE evenement (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, date_debut DATE NOT NULL, date_fin DATE NOT NULL, image VARCHAR(255) DEFAULT NULL, type_evenement VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE evenement_user (evenement_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_2EC0B3C4FD02F13 (evenement_id), INDEX IDX_2EC0B3C4A76ED395 (user_id), PRIMARY KEY(evenement_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE invite (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE invite_evenement (invite_id INT NOT NULL, evenement_id INT NOT NULL, INDEX IDX_9FF89C5BEA417747 (invite_id), INDEX IDX_9FF89C5BFD02F13 (evenement_id), PRIMARY KEY(invite_id, evenement_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE localisation (id INT AUTO_INCREMENT NOT NULL, evenement_id INT DEFAULT NULL, user_id INT DEFAULT NULL, rue VARCHAR(255) DEFAULT NULL, cp VARCHAR(255) DEFAULT NULL, ville VARCHAR(255) DEFAULT NULL, coordonnees VARCHAR(255) DEFAULT NULL, INDEX IDX_BFD3CE8FFD02F13 (evenement_id), INDEX IDX_BFD3CE8FA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, photo VARCHAR(255) DEFAULT NULL, role VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_evenement (user_id INT NOT NULL, evenement_id INT NOT NULL, INDEX IDX_BC6E5FAA76ED395 (user_id), INDEX IDX_BC6E5FAFD02F13 (evenement_id), PRIMARY KEY(user_id, evenement_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE evenement_user ADD CONSTRAINT FK_2EC0B3C4FD02F13 FOREIGN KEY (evenement_id) REFERENCES evenement (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE evenement_user ADD CONSTRAINT FK_2EC0B3C4A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE invite_evenement ADD CONSTRAINT FK_9FF89C5BEA417747 FOREIGN KEY (invite_id) REFERENCES invite (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE invite_evenement ADD CONSTRAINT FK_9FF89C5BFD02F13 FOREIGN KEY (evenement_id) REFERENCES evenement (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE localisation ADD CONSTRAINT FK_BFD3CE8FFD02F13 FOREIGN KEY (evenement_id) REFERENCES evenement (id)');
        $this->addSql('ALTER TABLE localisation ADD CONSTRAINT FK_BFD3CE8FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_evenement ADD CONSTRAINT FK_BC6E5FAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_evenement ADD CONSTRAINT FK_BC6E5FAFD02F13 FOREIGN KEY (evenement_id) REFERENCES evenement (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE evenement_user DROP FOREIGN KEY FK_2EC0B3C4FD02F13');
        $this->addSql('ALTER TABLE evenement_user DROP FOREIGN KEY FK_2EC0B3C4A76ED395');
        $this->addSql('ALTER TABLE invite_evenement DROP FOREIGN KEY FK_9FF89C5BEA417747');
        $this->addSql('ALTER TABLE invite_evenement DROP FOREIGN KEY FK_9FF89C5BFD02F13');
        $this->addSql('ALTER TABLE localisation DROP FOREIGN KEY FK_BFD3CE8FFD02F13');
        $this->addSql('ALTER TABLE localisation DROP FOREIGN KEY FK_BFD3CE8FA76ED395');
        $this->addSql('ALTER TABLE user_evenement DROP FOREIGN KEY FK_BC6E5FAA76ED395');
        $this->addSql('ALTER TABLE user_evenement DROP FOREIGN KEY FK_BC6E5FAFD02F13');
        $this->addSql('DROP TABLE evenement');
        $this->addSql('DROP TABLE evenement_user');
        $this->addSql('DROP TABLE invite');
        $this->addSql('DROP TABLE invite_evenement');
        $this->addSql('DROP TABLE localisation');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_evenement');
    }
}
