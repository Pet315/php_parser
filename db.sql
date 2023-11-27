CREATE DATABASE IF NOT exists php_parser;
use php_parser;

-- ALTER TABLE questions RENAME crossword;
-- SELECT COUNT(*) FROM crossword;
DROP TABLE IF EXISTS crossword;

CREATE TABLE IF NOT EXISTS `crossword` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `question` varchar(30) NOT NULL,
    `answer` varchar(30) NOT NULL,
    `length` int(7) NOT NULL,
    PRIMARY KEY (`id`)
    );