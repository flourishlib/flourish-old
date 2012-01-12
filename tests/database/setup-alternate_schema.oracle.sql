CREATE TABLE flourish2.users (
	user_id INTEGER PRIMARY KEY,
	first_name VARCHAR2(100) NOT NULL,
	middle_initial VARCHAR2(100),
	last_name VARCHAR2(100) NOT NULL
);

CREATE SEQUENCE flourish2.users_user_id_seq;

CREATE OR REPLACE TRIGGER flourish2.users_user_id_trg
BEFORE INSERT ON flourish2.users
FOR EACH ROW
BEGIN
  IF :new.user_id IS NULL THEN
	SELECT flourish2.users_user_id_seq.nextval INTO :new.user_id FROM dual\;
  END IF\;
END\;
;


CREATE TABLE flourish2.groups (
	group_id INTEGER PRIMARY KEY,
	name VARCHAR2(255) UNIQUE NOT NULL,
	group_leader INTEGER REFERENCES flourish2.users(user_id) ON DELETE CASCADE,
	group_founder INTEGER REFERENCES flourish2.users(user_id) ON DELETE CASCADE
);

CREATE SEQUENCE flourish2.groups_group_id_seq;

CREATE OR REPLACE TRIGGER flourish2.groups_group_id_trg
BEFORE INSERT ON flourish2.groups
FOR EACH ROW
BEGIN
  IF :new.group_id IS NULL THEN
	SELECT flourish2.groups_group_id_seq.nextval INTO :new.group_id FROM dual\;
  END IF\;
END\;
;


CREATE TABLE flourish2.users_groups (
	user_id INTEGER NOT NULL REFERENCES flourish2.users(user_id) ON DELETE CASCADE,
	group_id INTEGER NOT NULL REFERENCES flourish2.groups(group_id) ON DELETE CASCADE,
	PRIMARY KEY(user_id, group_id)
);


CREATE TABLE flourish2.artists (
	artist_id INTEGER PRIMARY KEY,
	name VARCHAR2(255) UNIQUE NOT NULL
);

CREATE SEQUENCE flourish2.artists_artist_id_seq;

CREATE OR REPLACE TRIGGER flourish2.artists_artist_id_trg
BEFORE INSERT ON flourish2.artists
FOR EACH ROW
BEGIN
  IF :new.artist_id IS NULL THEN
	SELECT flourish2.artists_artist_id_seq.nextval INTO :new.artist_id FROM dual\;
  END IF\;
END\;
;


CREATE TABLE flourish2.albums (
	album_id INTEGER PRIMARY KEY,
	name VARCHAR2(255) NOT NULL,
	year_released INTEGER NOT NULL,
	artist_id INTEGER NOT NULL REFERENCES flourish2.artists(artist_id) ON DELETE CASCADE,
	UNIQUE (artist_id, name)
);

CREATE SEQUENCE flourish2.albums_album_id_seq;

CREATE OR REPLACE TRIGGER flourish2.albums_album_id_trg
BEFORE INSERT ON flourish2.albums
FOR EACH ROW
BEGIN
  IF :new.album_id IS NULL THEN
	SELECT flourish2.albums_album_id_seq.nextval INTO :new.album_id FROM dual\;
  END IF\;
END\;
;

GRANT SELECT, INSERT, UPDATE, DELETE ON flourish2.users TO flourish_role;
GRANT SELECT, INSERT, UPDATE, DELETE ON flourish2.groups TO flourish_role;
GRANT SELECT, INSERT, UPDATE, DELETE ON flourish2.users_groups TO flourish_role;
GRANT SELECT, INSERT, UPDATE, DELETE ON flourish2.artists TO flourish_role;
GRANT SELECT, INSERT, UPDATE, DELETE ON flourish2.albums TO flourish_role;