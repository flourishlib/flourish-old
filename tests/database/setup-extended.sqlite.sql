CREATE TABLE user_details (
	user_id INTEGER PRIMARY KEY REFERENCES users(user_id) ON DELETE CASCADE,
	photo VARCHAR(255) NOT NULL DEFAULT ''
);

CREATE TABLE record_labels (
	name VARCHAR(255) PRIMARY KEY
);

CREATE TABLE record_deals (
	record_label VARCHAR(8) NOT NULL REFERENCES record_labels(name) ON UPDATE CASCADE ON DELETE CASCADE,
	artist_id INTEGER NOT NULL REFERENCES artists(artist_id) ON DELETE CASCADE,
	PRIMARY KEY (record_label, artist_id)
);

CREATE TABLE favorite_albums (
	email_address VARCHAR(200) NOT NULL REFERENCES users(email_address) ON UPDATE CASCADE ON DELETE CASCADE,
	album_id INTEGER NOT NULL REFERENCES albums(album_id) ON DELETE CASCADE,
	position INTEGER NOT NULL,
	UNIQUE (email_address, position),
	PRIMARY KEY (email_address, album_id)
);

CREATE TABLE invalid_tables (
	not_primary_key VARCHAR
);

CREATE TABLE events (
	event_id INTEGER PRIMARY KEY AUTOINCREMENT,
	title VARCHAR(255) NOT NULL,
	start_date DATE NOT NULL,
	end_date DATE
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

INSERT INTO favorite_albums (email_address, album_id, position) VALUES ('will@flourishlib.com', 2, 1);
INSERT INTO favorite_albums (email_address, album_id, position) VALUES ('will@flourishlib.com', 1, 2);
INSERT INTO favorite_albums (email_address, album_id, position) VALUES ('will@flourishlib.com', 3, 3);

INSERT INTO favorite_albums (email_address, album_id, position) VALUES ('john@smith.com', 2, 1);

INSERT INTO events (title, start_date, end_date) VALUES ('First Event',   '2008-01-01', '2008-01-01');
INSERT INTO events (title, start_date, end_date) VALUES ('Second Event',  '2008-02-01', '2008-02-08');
INSERT INTO events (title, start_date, end_date) VALUES ('Third Event',   '2008-02-01', '2008-02-02');
INSERT INTO events (title, start_date, end_date) VALUES ('Fourth Event',  '2009-01-01', '2010-01-01');
INSERT INTO events (title, start_date, end_date) VALUES ('Fifth Event',   '2005-06-03', '2008-06-02');
INSERT INTO events (title, start_date, end_date) VALUES ('Sixth Event',   '2009-05-29', '2009-05-30');
INSERT INTO events (title, start_date, end_date) VALUES ('Seventh Event', '2008-01-02', '2008-01-03');
INSERT INTO events (title, start_date, end_date) VALUES ('Eight Event',   '2008-01-01', NULL);
INSERT INTO events (title, start_date, end_date) VALUES ('Ninth Event',   '2008-02-02', NULL); 

COMMIT;