CREATE TABLE user_details (
	user_id INTEGER PRIMARY KEY REFERENCES users(user_id) ON DELETE CASCADE,
	photo VARCHAR(255) NOT NULL DEFAULT ''
);

CREATE TABLE other_user_details (
	id INTEGER PRIMARY KEY REFERENCES users(user_id) ON DELETE CASCADE,
	avatar VARCHAR(255) NOT NULL DEFAULT ''
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

CREATE TABLE year_favorite_albums (
	email VARCHAR(200) NOT NULL REFERENCES users(email_address) ON UPDATE CASCADE ON DELETE CASCADE,
	year integer NOT NULL,
	album_id INTEGER NOT NULL REFERENCES albums(album_id) ON DELETE CASCADE,
	position INTEGER NOT NULL,
	UNIQUE (email, year, position),
	PRIMARY KEY (email, year, album_id)
);

CREATE TABLE top_albums (
	top_album_id INTEGER PRIMARY KEY AUTOINCREMENT,
	album_id INTEGER NOT NULL UNIQUE REFERENCES albums(album_id) ON DELETE CASCADE,
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
	artist_id INTEGER NOT NULL REFERENCES artists(artist_id) ON DELETE CASCADE,
	PRIMARY KEY(event_id, artist_id)
);

CREATE TABLE certification_levels (
	name VARCHAR(200) PRIMARY KEY
);

CREATE TABLE certifications (
	level VARCHAR(200) NOT NULL REFERENCES certification_levels(name) ON DELETE CASCADE,
	album_id INTEGER NOT NULL REFERENCES albums(album_id) ON DELETE CASCADE,
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