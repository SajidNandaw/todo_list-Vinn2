-- --------------------------------------------------------
-- Struktur Database untuk Aplikasi To-Do List
-- --------------------------------------------------------

CREATE DATABASE IF NOT EXISTS `todo_list` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `todo_list`;

-- --------------------------------------------------------
-- Tabel: lists
-- --------------------------------------------------------
CREATE TABLE `lists` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Tabel: tasks
-- --------------------------------------------------------
CREATE TABLE `tasks` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `list_id` INT(11) NOT NULL,
  `task_name` VARCHAR(255) NOT NULL,
  `deadline` DATETIME NOT NULL,
  `status` ENUM('pending','done') DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`list_id`) REFERENCES `lists`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `lists` (`name`) VALUES
('Senin'),
('Tugas Sekolah');

INSERT INTO `tasks` (`list_id`, `task_name`, `deadline`, `status`) VALUES
(1, 'Belajar Fisika', '2025-10-30 10:00:00', 'pending'),
(1, 'Kerjakan PR Matematika', '2025-10-31 08:00:00', 'done'),
(2, 'Presentasi PKN', '2025-11-01 09:30:00', 'pending');
