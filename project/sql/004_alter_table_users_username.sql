ALTER TABLE Users
    ADD COLUMN username varchar(60) default null,
    ADD UNIQUE (username);
