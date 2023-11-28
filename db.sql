CREATE DATABASE IF NOT exists reg_form;
use reg_form;

-- ALTER TABLE crossword RENAME crosswords;
-- SELECT COUNT(*) FROM crosswords;
DROP TABLE IF EXISTS crosswords;

CREATE TABLE IF NOT EXISTS `crosswords` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `question` varchar(30) NOT NULL,
    `answer` varchar(30) NOT NULL,
    `length` int(7) NOT NULL,
    PRIMARY KEY (`id`)
    );