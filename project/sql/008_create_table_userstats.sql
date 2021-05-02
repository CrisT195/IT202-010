CREATE TABLE IF NOT EXISTS `Userstats`(
    `id` int AUTO_INCREMENT,
    `user_id` int not null unique,
    `points` int default 0,
    `modified` TIMESTAMP default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
    `created` TIMESTAMP default CURRENT_TIMESTAMP,
    primary key(`id`),
    foreign key (`user_id`) references Users (`id`)
)
