DROP TABLE IF EXISTS `posts`;
CREATE TABLE `posts` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `title` varchar(50),
  `body` text,
  `created` datetime,
  `modified` datetime
);

