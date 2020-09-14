<?php
require_once __DIR__.'/vendor/autoload.php';
use Dotenv\Dotenv;

$env = Dotenv::createImmutable(__DIR__);
$env->load();

$usersTableQuery = 'CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_username_uniq` (`username`),
  UNIQUE KEY `users_email_uniq` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci';

$invitationsTableQuery = 'CREATE TABLE `invitations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(128) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `inviter` int NOT NULL,
  `invitee` int NOT NULL,
  `response` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `invitations_FK_inviter` (`inviter`),
  KEY `invitations_FK_invitee` (`invitee`),
  CONSTRAINT `invitations_FK_invitee` FOREIGN KEY (`invitee`) REFERENCES `users` (`id`),
  CONSTRAINT `invitations_FK_inviter` FOREIGN KEY (`inviter`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci';

echo "This script will prepare database\n";

$pdo = new PDO($_ENV['DB_CONNECTION'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD']);
$pdo->prepare($usersTableQuery)->execute();
echo "users table created successfully\n";
$pdo->prepare($invitationsTableQuery)->execute();
echo "invitations table created successfully\n";
$usersInsertQuery = 'INSERT INTO invite.users (username, password, email) 
VALUES(:username, :password, :email)';
$pdo->prepare($usersInsertQuery)->execute([
    ':username'=> 'admin',
    ':password' => password_hash('admin', PASSWORD_DEFAULT),
    ':email' => 'admin@example.com'
]);
echo "users table populated successfully, default username/password is admin/admin\n";
