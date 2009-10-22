CREATE SCHEMA flourish2; 

CREATE TABLE flourish2.users (
	user_id INTEGER IDENTITY(1,1) PRIMARY KEY,
	first_name VARCHAR(100) NOT NULL,
	middle_initial VARCHAR(100) NOT NULL DEFAULT '',
	last_name VARCHAR(100) NOT NULL
);

CREATE TABLE flourish2.groups (
	group_id INTEGER IDENTITY(1,1) PRIMARY KEY,
	name VARCHAR(255) NOT NULL UNIQUE,
	group_leader INTEGER NULL REFERENCES flourish2.users(user_id) ON UPDATE CASCADE ON DELETE CASCADE,
	group_founder INTEGER NULL REFERENCES flourish2.users(user_id) ON DELETE NO ACTION ON UPDATE NO ACTION
);

CREATE TABLE flourish2.users_groups (
	user_id INTEGER NOT NULL REFERENCES flourish2.users(user_id) ON UPDATE CASCADE ON DELETE CASCADE,
	group_id INTEGER NOT NULL REFERENCES flourish2.groups(group_id) ON UPDATE NO ACTION ON DELETE NO ACTION,
	PRIMARY KEY(user_id, group_id)
);

CREATE TABLE flourish2.artists (
	artist_id INTEGER IDENTITY(1,1) PRIMARY KEY,
	name VARCHAR(255) NOT NULL UNIQUE
);

CREATE TABLE flourish2.albums (
	album_id INTEGER IDENTITY(1,1) PRIMARY KEY,
	name VARCHAR(255) NOT NULL,
	year_released INTEGER NOT NULL,
	artist_id INTEGER NOT NULL REFERENCES flourish2.artists(artist_id) ON UPDATE CASCADE ON DELETE CASCADE,
	UNIQUE (artist_id, name)
);

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