CREATE TABLE user_details (
	user_id INTEGER PRIMARY KEY REFERENCES users(user_id) ON DELETE CASCADE,
	photo VARCHAR(255) NOT NULL DEFAULT ''
);

CREATE TABLE record_labels (
	name VARCHAR(255) PRIMARY KEY
);

CREATE TABLE record_deals (
	record_label VARCHAR(255) NOT NULL REFERENCES record_labels(name) ON UPDATE CASCADE ON DELETE CASCADE,
	artist_id INTEGER NOT NULL REFERENCES artists(artist_id) ON DELETE CASCADE,
	PRIMARY KEY (record_label, artist_id)
);

CREATE TABLE favorite_albums (
	email VARCHAR(200) NOT NULL REFERENCES users(email_address) ON UPDATE CASCADE ON DELETE CASCADE,
	album_id INTEGER NOT NULL REFERENCES albums(album_id) ON DELETE CASCADE,
	position INTEGER NOT NULL,
	UNIQUE (email, position),
	PRIMARY KEY (email, album_id)
);

CREATE TABLE top_albums (
	top_album_id INTEGER IDENTITY(1,1) PRIMARY KEY,
	album_id INTEGER NOT NULL UNIQUE REFERENCES albums(album_id) ON DELETE CASCADE,
	position INTEGER NOT NULL UNIQUE
);

CREATE TABLE invalid_tables (
	not_primary_key VARCHAR(200)
);

CREATE TABLE events (
	event_id INTEGER IDENTITY(1,1) PRIMARY KEY,
	title VARCHAR(255) NOT NULL,
	start_date DATETIME NOT NULL,
	end_date DATETIME NULL
);

BEGIN TRANSACTION;

INSERT INTO user_details (user_id, photo) VALUES (1, 'will.png');
INSERT INTO user_details (user_id, photo) VALUES (2, 'john.jpg');
INSERT INTO user_details (user_id, photo) VALUES (3, 'foo.gif');
INSERT INTO user_details (user_id, photo) VALUES (4, 'bar.gif');

INSERT INTO record_labels (name) VALUES ('EMI');
INSERT INTO record_labels (name) VALUES ('Sony Music Entertainment');

INSERT INTO record_deals (record_label, artist_id) VALUES ('EMI', 1);
INSERT INTO record_deals (record_label, artist_id) VALUES ('Sony Music Entertainment', 2);

INSERT INTO artists (name) VALUES ('Relient K');
INSERT INTO albums (name, year_released, msrp, genre, artist_id) VALUES ('Mmhmm', 2004, '12.99', 'Alternative', 3);
INSERT INTO albums (name, year_released, msrp, genre, artist_id) VALUES ('Five Score and Seven Years Ago', 2007, '12.99', 'Alternative', 3);
INSERT INTO albums (name, year_released, msrp, genre, artist_id) VALUES ('Forget and Not Slow Down', 2009, '12.99', 'Alternative', 3);
INSERT INTO albums (name, year_released, msrp, genre, artist_id) VALUES ('Two Lefts Don''t Make a Right...but Three Do', 2003, '11.99', 'Alternative', 3);

INSERT INTO favorite_albums (email, album_id, position) VALUES ('will@flourishlib.com', 2, 1);
INSERT INTO favorite_albums (email, album_id, position) VALUES ('will@flourishlib.com', 1, 2);
INSERT INTO favorite_albums (email, album_id, position) VALUES ('will@flourishlib.com', 3, 3);
INSERT INTO favorite_albums (email, album_id, position) VALUES ('will@flourishlib.com', 7, 4);
INSERT INTO favorite_albums (email, album_id, position) VALUES ('will@flourishlib.com', 4, 5);

INSERT INTO favorite_albums (email, album_id, position) VALUES ('john@smith.com', 2, 1);

INSERT INTO events (title, start_date, end_date) VALUES ('First Event',   '2008-01-01', '2008-01-01');
INSERT INTO events (title, start_date, end_date) VALUES ('Second Event',  '2008-02-01', '2008-02-08');
INSERT INTO events (title, start_date, end_date) VALUES ('Third Event',   '2008-02-01', '2008-02-02');
INSERT INTO events (title, start_date, end_date) VALUES ('Fourth Event',  '2009-01-01', '2010-01-01');
INSERT INTO events (title, start_date, end_date) VALUES ('Fifth Event',   '2005-06-03', '2008-06-02');
INSERT INTO events (title, start_date, end_date) VALUES ('Sixth Event',   '2009-05-29', '2009-05-30');
INSERT INTO events (title, start_date, end_date) VALUES ('Seventh Event', '2008-01-02', '2008-01-03');
INSERT INTO events (title, start_date, end_date) VALUES ('Eighth Event',  '2008-01-01', NULL);
INSERT INTO events (title, start_date, end_date) VALUES ('Ninth Event',   '2008-02-02', NULL); 

INSERT INTO top_albums (album_id, position) VALUES (1, 1);
INSERT INTO top_albums (album_id, position) VALUES (4, 2);
INSERT INTO top_albums (album_id, position) VALUES (5, 3);
INSERT INTO top_albums (album_id, position) VALUES (6, 4);
INSERT INTO top_albums (album_id, position) VALUES (2, 5);
INSERT INTO top_albums (album_id, position) VALUES (3, 6);



COMMIT;