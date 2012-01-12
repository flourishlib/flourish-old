CREATE TABLE users (
	user_id INTEGER PRIMARY KEY,
	first_name VARCHAR2(100) NOT NULL,
	middle_initial VARCHAR2(100),
	last_name VARCHAR2(100) NOT NULL,
	email_address VARCHAR2(200) UNIQUE NOT NULL,
	status VARCHAR2(8) DEFAULT 'Active' NOT NULL CHECK(status IN ('Active', 'Inactive', 'Pending')),
	times_logged_in INTEGER DEFAULT 0 NOT NULL,
	date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
	birthday DATE,
	time_of_last_login TIMESTAMP,
	is_validated NUMBER(1) DEFAULT 0 NOT NULL CHECK(is_validated IN (0,1)),
	hashed_password VARCHAR2(100) NOT NULL
);
COMMENT ON COLUMN users.hashed_password IS 'This hash is generated using fCryptography::hashPassword()';
COMMENT ON COLUMN users.time_of_last_login IS 'When the user last logged in';
COMMENT ON COLUMN users.birthday IS 'The birthday';


CREATE SEQUENCE users_user_id_seq;

CREATE OR REPLACE TRIGGER users_user_id_trg
BEFORE INSERT ON users
FOR EACH ROW
BEGIN
  IF :new.user_id IS NULL THEN
	SELECT users_user_id_seq.nextval INTO :new.user_id FROM dual\;
  END IF\;
END\;
;


CREATE TABLE groups (
	group_id INTEGER PRIMARY KEY,
	name VARCHAR2(255) UNIQUE NOT NULL,
	group_leader INTEGER REFERENCES users(user_id) ON DELETE CASCADE,
	group_founder INTEGER REFERENCES users(user_id) ON DELETE CASCADE
);

CREATE SEQUENCE groups_group_id_seq;

CREATE OR REPLACE TRIGGER groups_group_id_trg
BEFORE INSERT ON groups
FOR EACH ROW
BEGIN
  IF :new.group_id IS NULL THEN
	SELECT groups_group_id_seq.nextval INTO :new.group_id FROM dual\;
  END IF\;
END\;
;


CREATE TABLE users_groups (
	user_id INTEGER NOT NULL REFERENCES users(user_id) ON DELETE CASCADE,
	group_id INTEGER NOT NULL REFERENCES groups(group_id) ON DELETE CASCADE,
	PRIMARY KEY(user_id, group_id)
);


CREATE TABLE artists (
	artist_id INTEGER PRIMARY KEY,
	name VARCHAR2(255) UNIQUE NOT NULL
);

CREATE SEQUENCE artists_artist_id_seq;

CREATE OR REPLACE TRIGGER artists_artist_id_trg
BEFORE INSERT ON artists
FOR EACH ROW
BEGIN
  IF :new.artist_id IS NULL THEN
	SELECT artists_artist_id_seq.nextval INTO :new.artist_id FROM dual\;
  END IF\;
END\;
;


CREATE TABLE albums (
	album_id INTEGER PRIMARY KEY,
	name VARCHAR2(255) NOT NULL,
	year_released INTEGER NOT NULL,
	msrp DECIMAL(10,2) NOT NULL,
	genre VARCHAR2(100),
	artist_id INTEGER NOT NULL REFERENCES artists(artist_id) ON DELETE CASCADE,
	UNIQUE (artist_id, name)
);

CREATE SEQUENCE albums_album_id_seq;

CREATE OR REPLACE TRIGGER albums_album_id_trg
BEFORE INSERT ON albums
FOR EACH ROW
BEGIN
  IF :new.album_id IS NULL THEN
	SELECT albums_album_id_seq.nextval INTO :new.album_id FROM dual\;
  END IF\;
END\;
;


CREATE TABLE songs (
	song_id INTEGER PRIMARY KEY,
	name VARCHAR2(255) NOT NULL,
	length TIMESTAMP NOT NULL,
	album_id INTEGER NOT NULL REFERENCES albums(album_id) ON DELETE CASCADE,
	track_number INTEGER NOT NULL,
	UNIQUE(track_number, album_id)
);

CREATE SEQUENCE songs_song_id_seq;

CREATE OR REPLACE TRIGGER songs_song_id_trg
BEFORE INSERT ON songs
FOR EACH ROW
BEGIN
  IF :new.song_id IS NULL THEN
	SELECT songs_song_id_seq.nextval INTO :new.song_id FROM dual\;
  END IF\;
END\;
;


CREATE TABLE owns_on_cd (
	user_id INTEGER REFERENCES users(user_id) ON DELETE CASCADE,
	album_id INTEGER REFERENCES albums(album_id) ON DELETE CASCADE,
	PRIMARY KEY(user_id, album_id)
);

CREATE TABLE owns_on_tape (
	user_id INTEGER REFERENCES users(user_id) ON DELETE CASCADE,
	album_id INTEGER REFERENCES albums(album_id) ON DELETE CASCADE,
	PRIMARY KEY(user_id, album_id)
);

CREATE TABLE blobs (
	blob_id INTEGER PRIMARY KEY,
	data BLOB NOT NULL
);