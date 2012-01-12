BEGIN TRANSACTION;

INSERT INTO flourish2.users (first_name, middle_initial, last_name) VALUES ('James', '', 'Doe');
INSERT INTO flourish2.users (first_name, middle_initial, last_name) VALUES ('Steve', '', 'Johnson');

INSERT INTO flourish2.groups (name, group_leader, group_founder) VALUES ('Sound Engineers', 1, 2);

INSERT INTO flourish2.users_groups (user_id, group_id) VALUES (1, 1);

INSERT INTO flourish2.artists (name) VALUES ('Phish');
INSERT INTO flourish2.artists (name) VALUES ('Grateful Dead');
INSERT INTO flourish2.artists (name) VALUES ('The Allman Brothers Band');

INSERT INTO flourish2.albums (name, year_released, artist_id) VALUES ('Junta', 1989, 1);
INSERT INTO flourish2.albums (name, year_released, artist_id) VALUES ('Rift', 1993, 1);
INSERT INTO flourish2.albums (name, year_released, artist_id) VALUES ('Hoist', 1994, 1);

INSERT INTO flourish2.albums (name, year_released, artist_id) VALUES ('American Beauty', 1970, 2);
INSERT INTO flourish2.albums (name, year_released, artist_id) VALUES ('Terrapin Station', 1977, 2);

INSERT INTO flourish2.albums (name, year_released, artist_id) VALUES ('Idlewild South', 1870, 3);

COMMIT;