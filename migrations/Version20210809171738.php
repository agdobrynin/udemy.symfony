<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210809171738 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_BA5AE01DF675F31B');
        $this->addSql('CREATE TEMPORARY TABLE __temp__blog_post AS SELECT id, author_id, title, created_at, content, slug, update_at FROM blog_post');
        $this->addSql('DROP TABLE blog_post');
        $this->addSql('CREATE TABLE blog_post (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, author_id INTEGER NOT NULL, title VARCHAR(255) NOT NULL COLLATE BINARY, created_at DATETIME NOT NULL, content CLOB NOT NULL COLLATE BINARY, slug VARCHAR(255) DEFAULT NULL COLLATE BINARY, update_at DATETIME NOT NULL, CONSTRAINT FK_BA5AE01DF675F31B FOREIGN KEY (author_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO blog_post (id, author_id, title, created_at, content, slug, update_at) SELECT id, author_id, title, created_at, content, slug, update_at FROM __temp__blog_post');
        $this->addSql('DROP TABLE __temp__blog_post');
        $this->addSql('CREATE INDEX IDX_BA5AE01DF675F31B ON blog_post (author_id)');
        $this->addSql('DROP INDEX IDX_F6F4088764DE5A5');
        $this->addSql('DROP INDEX IDX_F6F40887A77FBEAF');
        $this->addSql('CREATE TEMPORARY TABLE __temp__blog_post_media_object AS SELECT blog_post_id, media_object_id FROM blog_post_media_object');
        $this->addSql('DROP TABLE blog_post_media_object');
        $this->addSql('CREATE TABLE blog_post_media_object (blog_post_id INTEGER NOT NULL, media_object_id INTEGER NOT NULL, PRIMARY KEY(blog_post_id, media_object_id), CONSTRAINT FK_F6F40887A77FBEAF FOREIGN KEY (blog_post_id) REFERENCES blog_post (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_F6F4088764DE5A5 FOREIGN KEY (media_object_id) REFERENCES media_object (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO blog_post_media_object (blog_post_id, media_object_id) SELECT blog_post_id, media_object_id FROM __temp__blog_post_media_object');
        $this->addSql('DROP TABLE __temp__blog_post_media_object');
        $this->addSql('CREATE INDEX IDX_F6F4088764DE5A5 ON blog_post_media_object (media_object_id)');
        $this->addSql('CREATE INDEX IDX_F6F40887A77FBEAF ON blog_post_media_object (blog_post_id)');
        $this->addSql('DROP INDEX IDX_9474526C4B89032C');
        $this->addSql('DROP INDEX IDX_9474526CF675F31B');
        $this->addSql('CREATE TEMPORARY TABLE __temp__comment AS SELECT id, author_id, post_id, content, created_at, is_published, update_at FROM comment');
        $this->addSql('DROP TABLE comment');
        $this->addSql('CREATE TABLE comment (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, author_id INTEGER NOT NULL, post_id INTEGER NOT NULL, content CLOB NOT NULL COLLATE BINARY, created_at DATETIME NOT NULL, is_published BOOLEAN NOT NULL, update_at DATETIME NOT NULL, CONSTRAINT FK_9474526CF675F31B FOREIGN KEY (author_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_9474526C4B89032C FOREIGN KEY (post_id) REFERENCES blog_post (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO comment (id, author_id, post_id, content, created_at, is_published, update_at) SELECT id, author_id, post_id, content, created_at, is_published, update_at FROM __temp__comment');
        $this->addSql('DROP TABLE __temp__comment');
        $this->addSql('CREATE INDEX IDX_9474526C4B89032C ON comment (post_id)');
        $this->addSql('CREATE INDEX IDX_9474526CF675F31B ON comment (author_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__media_object AS SELECT id, file_path, mime_type FROM media_object');
        $this->addSql('DROP TABLE media_object');
        $this->addSql('CREATE TABLE media_object (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, mime_type VARCHAR(255) DEFAULT \'\' NOT NULL COLLATE BINARY, file_name VARCHAR(255) DEFAULT NULL)');
        $this->addSql('INSERT INTO media_object (id, file_name, mime_type) SELECT id, file_path, mime_type FROM __temp__media_object');
        $this->addSql('DROP TABLE __temp__media_object');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_BA5AE01DF675F31B');
        $this->addSql('CREATE TEMPORARY TABLE __temp__blog_post AS SELECT id, author_id, title, created_at, update_at, content, slug FROM blog_post');
        $this->addSql('DROP TABLE blog_post');
        $this->addSql('CREATE TABLE blog_post (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, author_id INTEGER NOT NULL, title VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, update_at DATETIME NOT NULL, content CLOB NOT NULL, slug VARCHAR(255) DEFAULT NULL)');
        $this->addSql('INSERT INTO blog_post (id, author_id, title, created_at, update_at, content, slug) SELECT id, author_id, title, created_at, update_at, content, slug FROM __temp__blog_post');
        $this->addSql('DROP TABLE __temp__blog_post');
        $this->addSql('CREATE INDEX IDX_BA5AE01DF675F31B ON blog_post (author_id)');
        $this->addSql('DROP INDEX IDX_F6F40887A77FBEAF');
        $this->addSql('DROP INDEX IDX_F6F4088764DE5A5');
        $this->addSql('CREATE TEMPORARY TABLE __temp__blog_post_media_object AS SELECT blog_post_id, media_object_id FROM blog_post_media_object');
        $this->addSql('DROP TABLE blog_post_media_object');
        $this->addSql('CREATE TABLE blog_post_media_object (blog_post_id INTEGER NOT NULL, media_object_id INTEGER NOT NULL, PRIMARY KEY(blog_post_id, media_object_id))');
        $this->addSql('INSERT INTO blog_post_media_object (blog_post_id, media_object_id) SELECT blog_post_id, media_object_id FROM __temp__blog_post_media_object');
        $this->addSql('DROP TABLE __temp__blog_post_media_object');
        $this->addSql('CREATE INDEX IDX_F6F40887A77FBEAF ON blog_post_media_object (blog_post_id)');
        $this->addSql('CREATE INDEX IDX_F6F4088764DE5A5 ON blog_post_media_object (media_object_id)');
        $this->addSql('DROP INDEX IDX_9474526CF675F31B');
        $this->addSql('DROP INDEX IDX_9474526C4B89032C');
        $this->addSql('CREATE TEMPORARY TABLE __temp__comment AS SELECT id, author_id, post_id, content, created_at, update_at, is_published FROM comment');
        $this->addSql('DROP TABLE comment');
        $this->addSql('CREATE TABLE comment (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, author_id INTEGER NOT NULL, post_id INTEGER NOT NULL, content CLOB NOT NULL, created_at DATETIME NOT NULL, update_at DATETIME NOT NULL, is_published BOOLEAN NOT NULL)');
        $this->addSql('INSERT INTO comment (id, author_id, post_id, content, created_at, update_at, is_published) SELECT id, author_id, post_id, content, created_at, update_at, is_published FROM __temp__comment');
        $this->addSql('DROP TABLE __temp__comment');
        $this->addSql('CREATE INDEX IDX_9474526CF675F31B ON comment (author_id)');
        $this->addSql('CREATE INDEX IDX_9474526C4B89032C ON comment (post_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__media_object AS SELECT id, file_name, mime_type FROM media_object');
        $this->addSql('DROP TABLE media_object');
        $this->addSql('CREATE TABLE media_object (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, mime_type VARCHAR(255) DEFAULT \'\' NOT NULL, file_path VARCHAR(255) DEFAULT NULL COLLATE BINARY)');
        $this->addSql('INSERT INTO media_object (id, file_path, mime_type) SELECT id, file_name, mime_type FROM __temp__media_object');
        $this->addSql('DROP TABLE __temp__media_object');
    }
}
