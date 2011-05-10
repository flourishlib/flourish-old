CREATE TABLE users (
	user_id INTEGER PRIMARY KEY AUTOINCREMENT,
	first_name VARCHAR(100) NOT NULL,
	middle_initial VARCHAR(100) NOT NULL DEFAULT '',
	last_name VARCHAR(100) NOT NULL,
	email_address VARCHAR(200) NOT NULL UNIQUE,
	status VARCHAR(8) NOT NULL DEFAULT 'Active' CHECK(status IN ('Active', 'Inactive', 'Pending')),
	times_logged_in INTEGER NOT NULL DEFAULT 0,
	date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	birthday DATE, -- The birthday
	time_of_last_login TIME /* When the user last logged in */,
	is_validated BOOLEAN NOT NULL DEFAULT FALSE,
	/* This comment is ignored */
	hashed_password VARCHAR(100) NOT NULL -- This hash is generated using fCryptography::hashPassword()
);

CREATE TABLE groups (
	group_id INTEGER PRIMARY KEY AUTOINCREMENT,
	name VARCHAR(255) NOT NULL UNIQUE,
	group_leader INTEGER REFERENCES users(user_id) ON UPDATE CASCADE ON DELETE CASCADE,
	group_founder INTEGER,
	/* comment before foreign key */
	FOREIGN KEY (group_founder) REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TRIGGER fki_ver_groups_group_leader
	BEFORE INSERT ON "groups"
	FOR EACH ROW BEGIN
		SELECT RAISE(ROLLBACK, 'insert on table "groups" violates foreign key constraint on column "group_leader"')
			WHERE NEW."group_leader" IS NOT NULL AND  (SELECT "user_id" FROM "users" WHERE "user_id" = NEW."group_leader") IS NULL\;
	END;
CREATE TRIGGER fku_ver_groups_group_leader
	BEFORE UPDATE ON "groups"
	FOR EACH ROW BEGIN
		SELECT RAISE(ROLLBACK, 'update on table "groups" violates foreign key constraint on column "group_leader"')
			WHERE NEW."group_leader" IS NOT NULL AND  (SELECT "user_id" FROM "users" WHERE "user_id" = NEW."group_leader") IS NULL\;
	END;
CREATE TRIGGER fkd_cas_groups_group_leader
	BEFORE DELETE ON "users"
	FOR EACH ROW BEGIN
		DELETE FROM "groups" WHERE "group_leader" = OLD."user_id"\;
	END;
CREATE TRIGGER fku_cas_groups_group_leader
	BEFORE UPDATE ON "users"
	FOR EACH ROW BEGIN
		UPDATE "groups" SET "group_leader" = NEW."user_id" WHERE OLD."user_id" <> NEW."user_id" AND "group_leader" = OLD."user_id"\;
	END;
CREATE TRIGGER fki_ver_groups_group_founder
	BEFORE INSERT ON "groups"
	FOR EACH ROW BEGIN
		SELECT RAISE(ROLLBACK, 'insert on table "groups" violates foreign key constraint on column "group_founder"')
		WHERE NEW."group_founder" IS NOT NULL AND  (SELECT "user_id" FROM "users" WHERE "user_id" = NEW."group_founder") IS NULL\;
	END;
CREATE TRIGGER fku_ver_groups_group_founder
	BEFORE UPDATE ON "groups"
	FOR EACH ROW BEGIN
		SELECT RAISE(ROLLBACK, 'update on table "groups" violates foreign key constraint on column "group_founder"')
			WHERE NEW."group_founder" IS NOT NULL AND  (SELECT "user_id" FROM "users" WHERE "user_id" = NEW."group_founder") IS NULL\;
	END;
CREATE TRIGGER fkd_cas_groups_group_founder
	BEFORE DELETE ON "users"
	FOR EACH ROW BEGIN
		DELETE FROM "groups" WHERE "group_founder" = OLD."user_id"\;
	END;
CREATE TRIGGER fku_cas_groups_group_founder
	BEFORE UPDATE ON "users"
	FOR EACH ROW BEGIN
		UPDATE "groups" SET "group_founder" = NEW."user_id" WHERE OLD."user_id" <> NEW."user_id" AND "group_founder" = OLD."user_id"\;
	END;


CREATE TABLE users_groups (
	user_id INTEGER NOT NULL REFERENCES users(user_id) ON UPDATE CASCADE ON DELETE CASCADE,
	group_id INTEGER NOT NULL REFERENCES groups(group_id) ON UPDATE CASCADE ON DELETE CASCADE,
	PRIMARY KEY(user_id, group_id)
);

CREATE TRIGGER fki_ver_users_groups_user_id
	BEFORE INSERT ON "users_groups"
	FOR EACH ROW BEGIN
		SELECT RAISE(ROLLBACK, 'insert on table "users_groups" violates foreign key constraint on column "user_id"')
			WHERE (SELECT "user_id" FROM "users" WHERE "user_id" = NEW."user_id") IS NULL\;
	END;
CREATE TRIGGER fku_ver_users_groups_user_id
	BEFORE UPDATE ON "users_groups"
	FOR EACH ROW BEGIN
		SELECT RAISE(ROLLBACK, 'update on table "users_groups" violates foreign key constraint on column "user_id"')
			WHERE (SELECT "user_id" FROM "users" WHERE "user_id" = NEW."user_id") IS NULL\;
	END;
CREATE TRIGGER fkd_cas_users_groups_user_id
	BEFORE DELETE ON "users"
	FOR EACH ROW BEGIN
		DELETE FROM "users_groups" WHERE "user_id" = OLD."user_id"\;
	END;
CREATE TRIGGER fku_cas_users_groups_user_id
	BEFORE UPDATE ON "users"
	FOR EACH ROW BEGIN
		UPDATE "users_groups" SET "user_id" = NEW."user_id" WHERE OLD."user_id" <> NEW."user_id" AND "user_id" = OLD."user_id"\;
	END;
CREATE TRIGGER fki_ver_users_groups_group_id
	BEFORE INSERT ON "users_groups"
	FOR EACH ROW BEGIN
		SELECT RAISE(ROLLBACK, 'insert on table "users_groups" violates foreign key constraint on column "group_id"')
			WHERE (SELECT "group_id" FROM "groups" WHERE "group_id" = NEW."group_id") IS NULL\;
	END;
CREATE TRIGGER fku_ver_users_groups_group_id
	BEFORE UPDATE ON "users_groups"
	FOR EACH ROW BEGIN
		SELECT RAISE(ROLLBACK, 'update on table "users_groups" violates foreign key constraint on column "group_id"')
			WHERE (SELECT "group_id" FROM "groups" WHERE "group_id" = NEW."group_id") IS NULL\;
	END;
CREATE TRIGGER fkd_cas_users_groups_group_id
	BEFORE DELETE ON "groups"
	FOR EACH ROW BEGIN
		DELETE FROM "users_groups" WHERE "group_id" = OLD."group_id"\;
	END;
CREATE TRIGGER fku_cas_users_groups_group_id
	BEFORE UPDATE ON "groups"
	FOR EACH ROW BEGIN
		UPDATE "users_groups" SET "group_id" = NEW."group_id" WHERE OLD."group_id" <> NEW."group_id" AND "group_id" = OLD."group_id"\;
	END;


CREATE TABLE artists (
	artist_id INTEGER PRIMARY KEY AUTOINCREMENT,
	name VARCHAR(255) NOT NULL
);
CREATE UNIQUE INDEX artists_name_uniq_idx ON artists(name);

CREATE TABLE albums (
	album_id INTEGER PRIMARY KEY AUTOINCREMENT,
	name VARCHAR(255) NOT NULL,
	year_released INTEGER NOT NULL,
	msrp DECIMAL(10,2) NOT NULL,
	genre VARCHAR(100) NOT NULL DEFAULT '',
	artist_id INTEGER NOT NULL REFERENCES artists(artist_id) ON UPDATE CASCADE ON DELETE CASCADE,
	UNIQUE (artist_id, name)
);

CREATE TRIGGER fki_ver_albums_artist_id
	BEFORE INSERT ON "albums"
	FOR EACH ROW BEGIN
		SELECT RAISE(ROLLBACK, 'insert on table "albums" violates foreign key constraint on column "artist_id"')
			WHERE  (SELECT "artist_id" FROM "artists" WHERE "artist_id" = NEW."artist_id") IS NULL\;
	END;
CREATE TRIGGER fku_ver_albums_artist_id
	BEFORE UPDATE ON "albums"
	FOR EACH ROW BEGIN
		SELECT RAISE(ROLLBACK, 'update on table "albums" violates foreign key constraint on column "artist_id"')
			WHERE  (SELECT "artist_id" FROM "artists" WHERE "artist_id" = NEW."artist_id") IS NULL\;
	END;
CREATE TRIGGER fkd_cas_albums_artist_id
	BEFORE DELETE ON "artists"
	FOR EACH ROW BEGIN
		DELETE FROM "albums" WHERE "artist_id" = OLD."artist_id"\;
	END;
CREATE TRIGGER fku_cas_albums_artist_id
	BEFORE UPDATE ON "artists"
	FOR EACH ROW BEGIN
		UPDATE "albums" SET "artist_id" = NEW."artist_id" WHERE OLD."artist_id" <> NEW."artist_id" AND "artist_id" = OLD."artist_id"\;
	END;


CREATE TABLE songs (
	song_id INTEGER PRIMARY KEY AUTOINCREMENT,
	name VARCHAR(255) NOT NULL,
	length TIME NOT NULL,
	album_id INTEGER NOT NULL REFERENCES albums(album_id) ON UPDATE CASCADE ON DELETE CASCADE,
	track_number INTEGER NOT NULL,
	UNIQUE(track_number, album_id)
);

CREATE TRIGGER fki_ver_songs_album_id
	BEFORE INSERT ON "songs"
	FOR EACH ROW BEGIN
		SELECT RAISE(ROLLBACK, 'insert on table "songs" violates foreign key constraint on column "album_id"')
			WHERE (SELECT "album_id" FROM "albums" WHERE "album_id" = NEW."album_id") IS NULL\;
	END;
CREATE TRIGGER fku_ver_songs_album_id
	BEFORE UPDATE ON "songs"
	FOR EACH ROW BEGIN
		SELECT RAISE(ROLLBACK, 'update on table "songs" violates foreign key constraint on column "album_id"')
			WHERE (SELECT "album_id" FROM "albums" WHERE "album_id" = NEW."album_id") IS NULL\;
	END;
CREATE TRIGGER fkd_cas_songs_album_id
	BEFORE DELETE ON "albums"
	FOR EACH ROW BEGIN
		DELETE FROM "songs" WHERE "album_id" = OLD."album_id"\;
	END;
CREATE TRIGGER fku_cas_songs_album_id
	BEFORE UPDATE ON "albums"
	FOR EACH ROW BEGIN
		UPDATE "songs" SET "album_id" = NEW."album_id" WHERE OLD."album_id" <> NEW."album_id" AND "album_id" = OLD."album_id"\;
	END;


CREATE TABLE owns_on_cd (
	user_id INTEGER REFERENCES users(user_id) ON UPDATE CASCADE ON DELETE CASCADE,
	album_id INTEGER REFERENCES albums(album_id) ON UPDATE CASCADE ON DELETE CASCADE,
	PRIMARY KEY(user_id, album_id)
);

CREATE TRIGGER fki_ver_owns_on_cd_user_id
	BEFORE INSERT ON "owns_on_cd"
	FOR EACH ROW BEGIN
		SELECT RAISE(ROLLBACK, 'insert on table "owns_on_cd" violates foreign key constraint on column "user_id"')
			WHERE NEW."user_id" IS NOT NULL AND  (SELECT "user_id" FROM "users" WHERE "user_id" = NEW."user_id") IS NULL\;
	END;
CREATE TRIGGER fku_ver_owns_on_cd_user_id
	BEFORE UPDATE ON "owns_on_cd"
	FOR EACH ROW BEGIN
		SELECT RAISE(ROLLBACK, 'update on table "owns_on_cd" violates foreign key constraint on column "user_id"')
			WHERE NEW."user_id" IS NOT NULL AND  (SELECT "user_id" FROM "users" WHERE "user_id" = NEW."user_id") IS NULL\;
	END;
CREATE TRIGGER fkd_cas_owns_on_cd_user_id
	BEFORE DELETE ON "users"
	FOR EACH ROW BEGIN
		DELETE FROM "owns_on_cd" WHERE "user_id" = OLD."user_id"\;
	END;
CREATE TRIGGER fku_cas_owns_on_cd_user_id
	BEFORE UPDATE ON "users"
	FOR EACH ROW BEGIN
		UPDATE "owns_on_cd" SET "user_id" = NEW."user_id" WHERE OLD."user_id" <> NEW."user_id" AND "user_id" = OLD."user_id"\;
	END;
CREATE TRIGGER fki_ver_owns_on_cd_album_id
	BEFORE INSERT ON "owns_on_cd"
	FOR EACH ROW BEGIN
		SELECT RAISE(ROLLBACK, 'insert on table "owns_on_cd" violates foreign key constraint on column "album_id"')
			WHERE NEW."album_id" IS NOT NULL AND  (SELECT "album_id" FROM "albums" WHERE "album_id" = NEW."album_id") IS NULL\;
	END;
CREATE TRIGGER fku_ver_owns_on_cd_album_id
	BEFORE UPDATE ON "owns_on_cd"
	FOR EACH ROW BEGIN
		SELECT RAISE(ROLLBACK, 'update on table "owns_on_cd" violates foreign key constraint on column "album_id"')
			WHERE NEW."album_id" IS NOT NULL AND  (SELECT "album_id" FROM "albums" WHERE "album_id" = NEW."album_id") IS NULL\;
	END;
CREATE TRIGGER fkd_cas_owns_on_cd_album_id
	BEFORE DELETE ON "albums"
	FOR EACH ROW BEGIN
		DELETE FROM "owns_on_cd" WHERE "album_id" = OLD."album_id"\;
	END;
CREATE TRIGGER fku_cas_owns_on_cd_album_id
	BEFORE UPDATE ON "albums"
	FOR EACH ROW BEGIN
		UPDATE "owns_on_cd" SET "album_id" = NEW."album_id" WHERE OLD."album_id" <> NEW."album_id" AND "album_id" = OLD."album_id"\;
	END;


CREATE TABLE owns_on_tape (
	user_id INTEGER REFERENCES users(user_id) ON UPDATE CASCADE ON DELETE CASCADE,
	album_id INTEGER REFERENCES albums(album_id) ON UPDATE CASCADE ON DELETE CASCADE,
	PRIMARY KEY(user_id, album_id)
);

CREATE TRIGGER fki_ver_owns_on_tape_user_id
	BEFORE INSERT ON "owns_on_tape"
	FOR EACH ROW BEGIN
		SELECT RAISE(ROLLBACK, 'insert on table "owns_on_tape" violates foreign key constraint on column "user_id"')
			WHERE NEW."user_id" IS NOT NULL AND  (SELECT "user_id" FROM "users" WHERE "user_id" = NEW."user_id") IS NULL\;
	END;
CREATE TRIGGER fku_ver_owns_on_tape_user_id
	BEFORE UPDATE ON "owns_on_tape"
	FOR EACH ROW BEGIN
		SELECT RAISE(ROLLBACK, 'update on table "owns_on_tape" violates foreign key constraint on column "user_id"')
			WHERE NEW."user_id" IS NOT NULL AND  (SELECT "user_id" FROM "users" WHERE "user_id" = NEW."user_id") IS NULL\;
	END;
CREATE TRIGGER fkd_cas_owns_on_tape_user_id
	BEFORE DELETE ON "users"
	FOR EACH ROW BEGIN
		DELETE FROM "owns_on_tape" WHERE "user_id" = OLD."user_id"\;
	END;
CREATE TRIGGER fku_cas_owns_on_tape_user_id
	BEFORE UPDATE ON "users"
	FOR EACH ROW BEGIN
		UPDATE "owns_on_tape" SET "user_id" = NEW."user_id" WHERE OLD."user_id" <> NEW."user_id" AND "user_id" = OLD."user_id"\;
	END;
CREATE TRIGGER fki_ver_owns_on_tape_album_id
	BEFORE INSERT ON "owns_on_tape"
	FOR EACH ROW BEGIN
		SELECT RAISE(ROLLBACK, 'insert on table "owns_on_tape" violates foreign key constraint on column "album_id"')
			WHERE NEW."album_id" IS NOT NULL AND  (SELECT "album_id" FROM "albums" WHERE "album_id" = NEW."album_id") IS NULL\;
	END;
CREATE TRIGGER fku_ver_owns_on_tape_album_id
	BEFORE UPDATE ON "owns_on_tape"
	FOR EACH ROW BEGIN
		SELECT RAISE(ROLLBACK, 'update on table "owns_on_tape" violates foreign key constraint on column "album_id"')
			WHERE NEW."album_id" IS NOT NULL AND  (SELECT "album_id" FROM "albums" WHERE "album_id" = NEW."album_id") IS NULL\;
	END;
CREATE TRIGGER fkd_cas_owns_on_tape_album_id
	BEFORE DELETE ON "albums"
	FOR EACH ROW BEGIN
		DELETE FROM "owns_on_tape" WHERE "album_id" = OLD."album_id"\;
	END;
CREATE TRIGGER fku_cas_owns_on_tape_album_id
	BEFORE UPDATE ON "albums"
	FOR EACH ROW BEGIN
		UPDATE "owns_on_tape" SET "album_id" = NEW."album_id" WHERE OLD."album_id" <> NEW."album_id" AND "album_id" = OLD."album_id"\;
	END;


CREATE TABLE blobs (
	blob_id INTEGER PRIMARY KEY,
	data BLOB NOT NULL
);

BEGIN;

INSERT INTO users (first_name, middle_initial, last_name, email_address, status, times_logged_in, date_created, birthday, time_of_last_login, is_validated, hashed_password) VALUES ('Will', '', 'Bond', 'will@flourishlib.com', 'Active', 5, '2008-05-01 13:00:00', '1980-09-01', '17:00:00', '1', '5527939aca3e9e80d5ab3bee47391f0f');
INSERT INTO users (first_name, middle_initial, last_name, email_address, status, times_logged_in, date_created, birthday, time_of_last_login, is_validated, hashed_password) VALUES ('John', '', 'Smith', 'john@smith.com', 'Active', 1, '2008-02-12 08:00:00', '1965-02-02', '12:00:00', '1', 'a722c63db8ec8625af6cf71cb8c2d939');
INSERT INTO users (first_name, middle_initial, last_name, email_address, status, times_logged_in, date_created, birthday, time_of_last_login, is_validated, hashed_password) VALUES ('Bar', '', 'Sheba', 'bar@example.com', 'Inactive', 0, '2008-01-01 17:00:00', NULL, NULL, '1', 'c1572d05424d0ecb2a65ec6a82aeacbf');
INSERT INTO users (first_name, middle_initial, last_name, email_address, status, times_logged_in, date_created, birthday, time_of_last_login, is_validated, hashed_password) VALUES ('Foo', '', 'Barish', 'foo@example.com', 'Active', 0, '2008-03-02 20:00:00', NULL, NULL, '0', '3afc79b597f88a72528e864cf81856d2');

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
INSERT INTO artists (name) VALUES ('Relient K');

INSERT INTO albums (name, year_released, msrp, genre, artist_id) VALUES ('Give Up', 2003, '13.98', 'Alternative', 1);
INSERT INTO albums (name, year_released, msrp, genre, artist_id) VALUES ('Viva Nueva!', 2001, '15.99', 'Rock', 2);
INSERT INTO albums (name, year_released, msrp, genre, artist_id) VALUES ('Long Division', 1996, '9.99', 'Rock', 2);
INSERT INTO albums (name, year_released, msrp, genre, artist_id) VALUES ('Mmhmm', 2004, '12.99', 'Alternative', 3);
INSERT INTO albums (name, year_released, msrp, genre, artist_id) VALUES ('Five Score and Seven Years Ago', 2007, '12.99', 'Alternative', 3);
INSERT INTO albums (name, year_released, msrp, genre, artist_id) VALUES ('Forget and Not Slow Down', 2009, '12.99', 'Alternative', 3);
INSERT INTO albums (name, year_released, msrp, genre, artist_id) VALUES ('Two Lefts Don''t Make a Right...but Three Do', 2003, '11.99', 'Alternative', 3);

INSERT INTO songs (name, length, album_id, track_number) VALUES ('The District Sleeps Alone Tonight', '00:04:44', 1, 1);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Such Great Heights', '00:04:26', 1, 2);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Sleeping In', '00:04:21', 1, 3);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Nothing Better', '00:03:46', 1, 4);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Recycled Air', '00:04:29', 1, 5);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Clark Gable', '00:04:54', 1, 6);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('We Will Become Silhouettes', '00:05:00', 1, 7);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('This Place is a Prison', '00:03:54', 1, 8);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Brand New Colony', '00:04:12', 1, 9);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Natural Anthem', '00:05:07', 1, 10);

INSERT INTO songs (name, length, album_id, track_number) VALUES ('C''Mon', '00:03:05', 2, 1);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Gas On Skin', '00:03:02', 2, 2);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Love Underground', '00:03:49', 2, 3);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Hardest Way Possible', '00:03:53', 2, 4);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Crash Landing', '00:02:36', 2, 5);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Smoke', '00:04:09', 2, 6);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Baby Blue', '00:03:33', 2, 7);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Revolution AM', '00:03:12', 2, 8);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Sector Z', '00:04:17', 2, 9);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Combustible', '00:03:12', 2, 10);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Valentine''s Day Massacre', '00:05:17', 2, 11);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Hit Man', '00:03:59', 2, 12);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Check', '00:02:50', 2, 13);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Boys And Girls', '00:03:43', 2, 14);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Beekeeper', '00:00:35', 2, 15);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Man Without A Mouth', '00:04:17', 2, 16);

INSERT INTO songs (name, length, album_id, track_number) VALUES ('About A Kid', '00:03:20', 3, 1);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Simple Song', '00:06:29', 3, 2);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Spunk Drive 185', '00:05:07', 3, 3);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Colors Of Discipline', '00:04:22', 3, 4);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('20 Years', '00:02:42', 3, 5);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Fake Face', '00:05:16', 3, 6);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Dig', '00:07:34', 3, 7);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Feel', '00:05:28', 3, 8);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Pimp', '00:05:35', 3, 9);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Long Division', '00:04:23', 3, 10);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Slowly', '00:05:24', 3, 11);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Oulaw Biker', '00:04:47', 3, 12);
INSERT INTO songs (name, length, album_id, track_number) VALUES ('Pop Trash', '00:06:53', 3, 13);

INSERT INTO owns_on_cd (user_id, album_id) VALUES (1, 1);
INSERT INTO owns_on_cd (user_id, album_id) VALUES (1, 2);
INSERT INTO owns_on_cd (user_id, album_id) VALUES (1, 3);
INSERT INTO owns_on_cd (user_id, album_id) VALUES (2, 1);
INSERT INTO owns_on_cd (user_id, album_id) VALUES (3, 3);
INSERT INTO owns_on_cd (user_id, album_id) VALUES (4, 1);  
INSERT INTO owns_on_cd (user_id, album_id) VALUES (4, 2);

INSERT INTO owns_on_tape (user_id, album_id) VALUES (3, 1);
INSERT INTO owns_on_tape (user_id, album_id) VALUES (3, 2);   

INSERT INTO blobs (blob_id, data) VALUES (1, X'5527939aca3e9e80d5ab3bee47391f0f');

COMMIT;