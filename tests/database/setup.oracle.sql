CREATE TABLE users (
	user_id INTEGER PRIMARY KEY,
	first_name VARCHAR2(100) NOT NULL,
	middle_initial VARCHAR2(100),
	last_name VARCHAR2(100) NOT NULL,
	email_address VARCHAR2(200) UNIQUE NOT NULL,
	status VARCHAR2(8) DEFAULT 'Active' NOT NULL CHECK(status IN ('Active', 'Inactive', 'Pending')),
	times_logged_in INTEGER DEFAULT 0 NOT NULL,
	date_created TIMESTAMP NOT NULL,
	birthday DATE,
	time_of_last_login TIMESTAMP,
	is_validated NUMBER(1) DEFAULT 0 NOT NULL CHECK(is_validated IN (0,1)),
	hashed_password VARCHAR2(100) NOT NULL
);

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

BEGIN;

INSERT INTO users (first_name, middle_initial, last_name, email_address, status, times_logged_in, date_created, birthday, time_of_last_login, is_validated, hashed_password) VALUES ('Will', '', 'Bond', 'will@flourishlib.com', 'Active', 5, '2008-05-01 13:00:00', '1980-09-01', '1970-01-01 17:00:00', 1, '5527939aca3e9e80d5ab3bee47391f0f');
INSERT INTO users (first_name, middle_initial, last_name, email_address, status, times_logged_in, date_created, birthday, time_of_last_login, is_validated, hashed_password) VALUES ('John', '', 'Smith', 'john@smith.com', 'Active', 1, '2008-02-12 08:00:00', '1965-02-02', '1970-01-01 12:00:00', 1, 'a722c63db8ec8625af6cf71cb8c2d939');
INSERT INTO users (first_name, middle_initial, last_name, email_address, status, times_logged_in, date_created, birthday, time_of_last_login, is_validated, hashed_password) VALUES ('Bar', '', 'Sheba', 'bar@example.com', 'Inactive', 0, '2008-01-01 17:00:00', NULL, NULL, 1, 'c1572d05424d0ecb2a65ec6a82aeacbf');
INSERT INTO users (first_name, middle_initial, last_name, email_address, status, times_logged_in, date_created, birthday, time_of_last_login, is_validated, hashed_password) VALUES ('Foo', '', 'Barish', 'foo@example.com', 'Active', 0, '2008-03-02 20:00:00', NULL, NULL, 0, '3afc79b597f88a72528e864cf81856d2');

INSERT INTO groups (name, group_leader, group_founder) VALUES ('Music Lovers', 1, 2);
INSERT INTO groups (name, group_leader, group_founder) VALUES ('Musicians', 2, 2);

INSERT INTO users_groups (user_id, group_id) VALUES (1, 1);
INSERT INTO users_groups (user_id, group_id) VALUES (1, 2);
INSERT INTO users_groups (user_id, group_id) VALUES (2, 1);
INSERT INTO users_groups (user_id, group_id) VALUES (2, 2);
INSERT INTO users_groups (user_id, group_id) VALUES (3, 1);
INSERT INTO users_groups (user_id, group_id) VALUES (4, 1);

INSERT INTO artists (name) VALUES ('The Postal Service');
INSERT INTO artists (name) VALUES ('Rustic Overtones');

INSERT INTO albums (name, year_released, msrp, genre, artist_id) VALUES ('Give Up', 2003, '13.98', 'Alternative', 1);
INSERT INTO albums (name, year_released, msrp, genre, artist_id) VALUES ('Viva Nueva!', 2001, '15.99', 'Rock', 2);
INSERT INTO albums (name, year_released, msrp, genre, artist_id) VALUES ('Long Division', 1996, '9.99', 'Rock', 2);

INSERT INTO songs (name, length, album_id, track_number) VALUES ('The District Sleeps Alone Tonight', '1970-01-01 00:04:44', 1, 1);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Such Great Heights', '1970-01-01 00:04:26', 1, 2);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Sleeping In', '1970-01-01 00:04:21', 1, 3);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Nothing Better', '1970-01-01 00:03:46', 1, 4);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Recycled Air', '1970-01-01 00:04:29', 1, 5);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Clark Gable', '1970-01-01 00:04:54', 1, 6);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('We Will Become Silhouettes', '1970-01-01 00:05:00', 1, 7);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('This Place is a Prison', '1970-01-01 00:03:54', 1, 8);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Brand New Colony', '1970-01-01 00:04:12', 1, 9);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Natural Anthem', '1970-01-01 00:05:07', 1, 10);

INSERT INTO songs (name, length, album_id, track_number) VALUES ('C''Mon', '1970-01-01 00:03:05', 2, 1);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Gas On Skin', '1970-01-01 00:03:02', 2, 2);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Love Underground', '1970-01-01 00:03:49', 2, 3);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Hardest Way Possible', '1970-01-01 00:03:53', 2, 4);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Crash Landing', '1970-01-01 00:02:36', 2, 5);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Smoke', '1970-01-01 00:04:09', 2, 6);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Baby Blue', '1970-01-01 00:03:33', 2, 7);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Revolution AM', '1970-01-01 00:03:12', 2, 8);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Sector Z', '1970-01-01 00:04:17', 2, 9);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Combustible', '1970-01-01 00:03:12', 2, 10);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Valentine''s Day Massacre', '1970-01-01 00:05:17', 2, 11);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Hit Man', '1970-01-01 00:03:59', 2, 12);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Check', '1970-01-01 00:02:50', 2, 13);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Boys And Girls', '1970-01-01 00:03:43', 2, 14);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Beekeeper', '1970-01-01 00:00:35', 2, 15);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Man Without A Mouth', '1970-01-01 00:04:17', 2, 16);

INSERT INTO songs (name, length, album_id, track_number) VALUES ('About A Kid', '1970-01-01 00:03:20', 3, 1);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Simple Song', '1970-01-01 00:06:29', 3, 2);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Spunk Drive 185', '1970-01-01 00:05:07', 3, 3);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Colors Of Discipline', '1970-01-01 00:04:22', 3, 4);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('20 Years', '1970-01-01 00:02:42', 3, 5);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Fake Face', '1970-01-01 00:05:16', 3, 6);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Dig', '1970-01-01 00:07:34', 3, 7);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Feel', '1970-01-01 00:05:28', 3, 8);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Pimp', '1970-01-01 00:05:35', 3, 9);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Long Division', '1970-01-01 00:04:23', 3, 10);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Slowly', '1970-01-01 00:05:24', 3, 11);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Oulaw Biker', '1970-01-01 00:04:47', 3, 12);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Pop Trash', '1970-01-01 00:06:53', 3, 13);

INSERT INTO owns_on_cd (user_id, album_id) VALUES (1, 1);
INSERT INTO owns_on_cd (user_id, album_id) VALUES (1, 2);
INSERT INTO owns_on_cd (user_id, album_id) VALUES (1, 3);
INSERT INTO owns_on_cd (user_id, album_id) VALUES (2, 1);
INSERT INTO owns_on_cd (user_id, album_id) VALUES (3, 3);
INSERT INTO owns_on_cd (user_id, album_id) VALUES (4, 1);  
INSERT INTO owns_on_cd (user_id, album_id) VALUES (4, 2);

INSERT INTO owns_on_tape (user_id, album_id) VALUES (3, 1);
INSERT INTO owns_on_tape (user_id, album_id) VALUES (3, 2);   

INSERT INTO blobs (blob_id, data) VALUES (1, '5527939aca3e9e80d5ab3bee47391f0f');

COMMIT;