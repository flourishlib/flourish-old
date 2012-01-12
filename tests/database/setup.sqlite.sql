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
	user_id INTEGER,
	album_id INTEGER REFERENCES albums(album_id) ON UPDATE CASCADE ON DELETE CASCADE,
	PRIMARY KEY(user_id, album_id),
	CONSTRAINT "owns_on_cd_user_id_fkey" FOREIGN KEY (user_id) REFERENCES users(user_id) ON UPDATE CASCADE ON DELETE CASCADE
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
	CONSTRAINT "owns_on_tape_pkey" PRIMARY KEY(user_id, album_id)
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