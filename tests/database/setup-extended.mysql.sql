CREATE TABLE user_details (
	user_id INTEGER PRIMARY KEY,
	photo VARCHAR(255) NOT NULL DEFAULT '',
	FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
)ENGINE=InnoDB;

CREATE TABLE other_user_details (
	id INTEGER PRIMARY KEY,
	avatar VARCHAR(255) NOT NULL DEFAULT '',
	FOREIGN KEY (id) REFERENCES users(user_id) ON DELETE CASCADE
)ENGINE=InnoDB;

CREATE TABLE record_labels (
	name VARCHAR(255) PRIMARY KEY
)ENGINE=InnoDB;

CREATE TABLE record_deals (
	record_label VARCHAR(255) NOT NULL,
	artist_id INTEGER NOT NULL,
	FOREIGN KEY (artist_id) REFERENCES artists(artist_id) ON DELETE CASCADE,
	FOREIGN KEY (record_label)  REFERENCES record_labels(name) ON UPDATE CASCADE ON DELETE CASCADE,
	PRIMARY KEY (record_label, artist_id)
)ENGINE=InnoDB;

CREATE TABLE favorite_albums (
	email VARCHAR(200) NOT NULL,
	album_id INTEGER NOT NULL,
	position INTEGER NOT NULL,
	UNIQUE (email, position),
	FOREIGN KEY (album_id) REFERENCES albums(album_id) ON DELETE CASCADE,
	FOREIGN KEY (email) REFERENCES users(email_address) ON UPDATE CASCADE ON DELETE CASCADE,
	PRIMARY KEY (email, album_id)
)ENGINE=InnoDB;

CREATE TABLE year_favorite_albums (
	email VARCHAR(200) NOT NULL,
	year INTEGER NOT NULL,
	album_id INTEGER NOT NULL,
	position INTEGER NOT NULL,
	UNIQUE (email, year, position),
	FOREIGN KEY (album_id) REFERENCES albums(album_id) ON DELETE CASCADE,
	FOREIGN KEY (email) REFERENCES users(email_address) ON UPDATE CASCADE ON DELETE CASCADE,
	PRIMARY KEY (email, year, album_id)
)ENGINE=InnoDB;

CREATE TABLE top_albums (
	top_album_id INTEGER PRIMARY KEY AUTO_INCREMENT,
	album_id INTEGER NOT NULL UNIQUE,
	position INTEGER NOT NULL UNIQUE,
	FOREIGN KEY (album_id) REFERENCES albums(album_id) ON DELETE CASCADE
)ENGINE=InnoDB;

CREATE TABLE invalid_tables (
	not_primary_key VARCHAR(200)
)ENGINE=InnoDB;

CREATE TABLE event_slots (
	id INTEGER PRIMARY KEY AUTO_INCREMENT,
	name VARCHAR(255) NOT NULL
)ENGINE=InnoDB;

CREATE TABLE events (
	event_id INTEGER PRIMARY KEY AUTO_INCREMENT,
	title VARCHAR(255) NOT NULL,
	start_date DATE NOT NULL,
	end_date DATE,
	event_slot_id INTEGER UNIQUE,
	registration_url VARCHAR(255) NOT NULL DEFAULT '',
	FOREIGN KEY (event_slot_id) REFERENCES event_slots(id) ON DELETE SET NULL
)ENGINE=InnoDB;

CREATE TABLE registrations (
	event_id INTEGER NOT NULL,
	name VARCHAR(255) NOT NULL,
	PRIMARY KEY(event_id, name),
	FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE RESTRICT
)ENGINE=InnoDB;

CREATE TABLE event_details (
	event_id INTEGER NOT NULL PRIMARY KEY,
	allows_registration BOOLEAN NOT NULL,
	FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE RESTRICT
)ENGINE=InnoDB;

CREATE TABLE events_artists (
	event_id INTEGER NOT NULL,
	artist_id INTEGER NOT NULL,
	PRIMARY KEY(event_id, artist_id),
	FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE RESTRICT,
	FOREIGN KEY (artist_id) REFERENCES artists(artist_id) ON DELETE CASCADE
)ENGINE=InnoDB;

CREATE TABLE certification_levels (
	name VARCHAR(200) PRIMARY KEY
)ENGINE=InnoDB;

CREATE TABLE certifications (
	level VARCHAR(200) NOT NULL,
	album_id INTEGER NOT NULL,
	year INTEGER NOT NULL,
	PRIMARY KEY (album_id, level),
	FOREIGN KEY (level) REFERENCES certification_levels(name) ON DELETE CASCADE,
	FOREIGN KEY (album_id) REFERENCES albums(album_id) ON DELETE CASCADE
)ENGINE=InnoDB;

CREATE TABLE categories (
	category_id INTEGER PRIMARY KEY AUTO_INCREMENT,
	name VARCHAR(200) NOT NULL,
	parent INTEGER,
	FOREIGN KEY (parent) REFERENCES categories(category_id) ON DELETE CASCADE
)ENGINE=InnoDB;

CREATE TABLE people (
	person_id INTEGER PRIMARY KEY AUTO_INCREMENT,
	name VARCHAR(200) NOT NULL,
	category_id INTEGER,
	FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE CASCADE
)ENGINE=InnoDB;