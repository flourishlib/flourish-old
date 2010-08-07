CREATE TABLE user_details (
	user_id INTEGER PRIMARY KEY REFERENCES user(user_id) ON DELETE CASCADE,
	photo VARCHAR(255) NOT NULL DEFAULT ''
);

CREATE TABLE other_user_details (
	id INTEGER PRIMARY KEY REFERENCES user(user_id) ON DELETE CASCADE,
	avatar VARCHAR(255) NOT NULL DEFAULT ''
);

CREATE TABLE record_labels (
	name VARCHAR(255) PRIMARY KEY
);

CREATE TABLE record_deals (
	record_label VARCHAR(255) NOT NULL REFERENCES record_labels(name) ON UPDATE CASCADE ON DELETE CASCADE,
	artist_id INTEGER NOT NULL REFERENCES popular_artists(artist_id) ON DELETE CASCADE,
	PRIMARY KEY (record_label, artist_id)
);

CREATE TABLE favorite_albums (
	email VARCHAR(200) NOT NULL REFERENCES user(email_address) ON UPDATE CASCADE ON DELETE CASCADE,
	album_id INTEGER NOT NULL REFERENCES records(album_id) ON DELETE CASCADE,
	position INTEGER NOT NULL,
	UNIQUE (email, position),
	PRIMARY KEY (email, album_id)
);

CREATE TABLE year_favorite_albums (
	email VARCHAR(200) NOT NULL REFERENCES user(email_address) ON UPDATE CASCADE ON DELETE CASCADE,
	year integer NOT NULL,
	album_id INTEGER NOT NULL REFERENCES records(album_id) ON DELETE CASCADE,
	position INTEGER NOT NULL,
	UNIQUE (email, year, position),
	PRIMARY KEY (email, year, album_id)
);

CREATE TABLE top_albums (
	top_album_id INTEGER PRIMARY KEY AUTOINCREMENT,
	album_id INTEGER NOT NULL UNIQUE REFERENCES records(album_id) ON DELETE CASCADE,
	position INTEGER NOT NULL UNIQUE
);

CREATE TABLE invalid_tables (
	not_primary_key VARCHAR(200)
);

CREATE TABLE event_slots (
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	name VARCHAR(255) NOT NULL
);

CREATE TABLE events (
	event_id INTEGER PRIMARY KEY AUTOINCREMENT,
	title VARCHAR(255) NOT NULL,
	start_date DATE NOT NULL,
	end_date DATE,
	event_slot_id INTEGER UNIQUE REFERENCES event_slots(id) ON DELETE SET NULL,
	registration_url VARCHAR(255) NOT NULL DEFAULT ''
);

CREATE TABLE registrations (
	event_id INTEGER NOT NULL REFERENCES events(event_id) ON DELETE RESTRICT,
	name VARCHAR(255) NOT NULL,
	PRIMARY KEY(event_id, name)
);

CREATE TABLE event_details (
	event_id INTEGER NOT NULL PRIMARY KEY REFERENCES events(event_id) ON DELETE RESTRICT,
	allows_registration BOOLEAN NOT NULL
);

CREATE TABLE events_artists (
	event_id INTEGER NOT NULL REFERENCES events(event_id) ON DELETE RESTRICT,
	artist_id INTEGER NOT NULL REFERENCES popular_artists(artist_id) ON DELETE CASCADE,
	PRIMARY KEY(event_id, artist_id)
);

CREATE TABLE certification_levels (
	name VARCHAR(200) PRIMARY KEY
);

CREATE TABLE certifications (
	level VARCHAR(200) NOT NULL REFERENCES certification_levels(name) ON DELETE CASCADE,
	album_id INTEGER NOT NULL REFERENCES records(album_id) ON DELETE CASCADE,
	year INTEGER NOT NULL,
	PRIMARY KEY (album_id, level)
);

CREATE TABLE categories (
	category_id INTEGER PRIMARY KEY AUTOINCREMENT,
	name VARCHAR(200) NOT NULL,
	parent INTEGER REFERENCES categories(category_id) ON DELETE CASCADE
);

CREATE TABLE people (
	person_id INTEGER PRIMARY KEY AUTOINCREMENT,
	name VARCHAR(200) NOT NULL,
	category_id INTEGER REFERENCES categories(category_id) ON DELETE CASCADE
);

BEGIN;

INSERT INTO user_details (user_id, photo) VALUES (1, 'will.png');
INSERT INTO user_details (user_id, photo) VALUES (2, 'john.jpg');
INSERT INTO user_details (user_id, photo) VALUES (3, 'foo.gif');
INSERT INTO user_details (user_id, photo) VALUES (4, 'bar.gif');

INSERT INTO record_labels (name) VALUES ('EMI');
INSERT INTO record_labels (name) VALUES ('Sony Music Entertainment');

INSERT INTO record_deals (record_label, artist_id) VALUES ('EMI', 1);
INSERT INTO record_deals (record_label, artist_id) VALUES ('Sony Music Entertainment', 2);

INSERT INTO favorite_albums (email, album_id, position) VALUES ('will@flourishlib.com', 2, 1);
INSERT INTO favorite_albums (email, album_id, position) VALUES ('will@flourishlib.com', 1, 2);
INSERT INTO favorite_albums (email, album_id, position) VALUES ('will@flourishlib.com', 3, 3);
INSERT INTO favorite_albums (email, album_id, position) VALUES ('will@flourishlib.com', 7, 4);
INSERT INTO favorite_albums (email, album_id, position) VALUES ('will@flourishlib.com', 4, 5);

INSERT INTO year_favorite_albums (email, year, album_id, position) VALUES ('will@flourishlib.com', 2009, 2, 1);
INSERT INTO year_favorite_albums (email, year, album_id, position) VALUES ('will@flourishlib.com', 2009, 1, 2);
INSERT INTO year_favorite_albums (email, year, album_id, position) VALUES ('will@flourishlib.com', 2009, 3, 3);
INSERT INTO year_favorite_albums (email, year, album_id, position) VALUES ('will@flourishlib.com', 2009, 7, 4);
INSERT INTO year_favorite_albums (email, year, album_id, position) VALUES ('will@flourishlib.com', 2009, 4, 5);

INSERT INTO favorite_albums (email, album_id, position) VALUES ('john@smith.com', 2, 1);

INSERT INTO events (title, start_date, end_date) VALUES ('First Event',   '2008-01-01', '2008-01-01');
INSERT INTO events (title, start_date, end_date) VALUES ('Second Event',  '2008-02-01', '2008-02-08');
INSERT INTO events (title, start_date, end_date) VALUES ('Third Event',   '2008-02-01', '2008-02-02');
INSERT INTO events (title, start_date, end_date) VALUES ('Fourth Event',  '2009-01-01', '2010-01-01');
INSERT INTO events (title, start_date, end_date) VALUES ('Fifth Event',   '2005-06-03', '2008-06-02');
INSERT INTO events (title, start_date, end_date) VALUES ('Sixth Event',   '2009-05-29', '2009-05-30');
INSERT INTO events (title, start_date, end_date) VALUES ('Seventh Event', '2008-01-02', '2008-01-03');
INSERT INTO events (title, start_date, end_date) VALUES ('Eight Event',   '2008-01-01', NULL);
INSERT INTO events (title, start_date, end_date) VALUES ('Ninth Event',   '2008-02-02', NULL); 

INSERT INTO top_albums (album_id, position) VALUES (1, 1);
INSERT INTO top_albums (album_id, position) VALUES (4, 2);
INSERT INTO top_albums (album_id, position) VALUES (5, 3);
INSERT INTO top_albums (album_id, position) VALUES (6, 4);
INSERT INTO top_albums (album_id, position) VALUES (2, 5);
INSERT INTO top_albums (album_id, position) VALUES (3, 6);

INSERT INTO categories (name, parent) VALUES ('Top Level', NULL);
INSERT INTO categories (name, parent) VALUES ('Top Level, No Children', NULL);
INSERT INTO categories (name, parent) VALUES ('Second Level', 1);
INSERT INTO categories (name, parent) VALUES ('Second Level #2', 1);
INSERT INTO categories (name, parent) VALUES ('Second Level #3', 1);

INSERT INTO people (name, category_id) VALUES ('John', 1);
INSERT INTO people (name, category_id) VALUES ('Ben', 1);
INSERT INTO people (name, category_id) VALUES ('Fred', 1);
INSERT INTO people (name, category_id) VALUES ('Steve', 2);

COMMIT;