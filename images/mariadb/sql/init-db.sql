USE `avito_test`;

CREATE TABLE `Statistic` (
                             `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
                             `date` DATE NOT NULL,
                             `views` int DEFAULT 0,
                             `clicks` int DEFAULT 0,
                             `cost` int DEFAULT 0
);