# username is "root"
# password is "password"

CREATE DATABASE `my_db` COLLATE=utf8_unicode_ci;

USE `my_db`;

CREATE TABLE `users` (
  `first_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `last_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `course` int(11) NOT NULL,
  `major` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `faculty_number` int(11) NOT NULL,
  `group` int(11) NOT NULL,
  `birth_date` date NOT NULL,
  `zodiac_name` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `zodiac_sign` char(1) COLLATE utf8_unicode_ci NOT NULL,
  `url` varchar(2000) COLLATE utf8_unicode_ci NOT NULL,
  `motivational_letter` text COLLATE utf8_unicode_ci NOT NULL,
  `photo` blob NOT NULL,
  `signature` blob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `users`
  ADD PRIMARY KEY (`faculty_number`);

