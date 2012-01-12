CREATE TABLE users (
	user_id SERIAL PRIMARY KEY,
	first_name VARCHAR(100) NOT NULL,
	middle_initial VARCHAR(100) NOT NULL DEFAULT '',
	last_name VARCHAR(100) NOT NULL,
	email_address VARCHAR(200) NOT NULL UNIQUE,
	status VARCHAR(8) NOT NULL DEFAULT 'Active' CHECK(status IN ('Active', 'Inactive', 'Pending')),
	times_logged_in INTEGER NOT NULL DEFAULT 0,
	date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	birthday DATE,
	time_of_last_login TIME,
	is_validated BOOLEAN NOT NULL DEFAULT FALSE,
	hashed_password VARCHAR(100) NOT NULL
);
COMMENT ON COLUMN users.hashed_password IS 'This hash is generated using fCryptography::hashPassword()';
COMMENT ON COLUMN users.time_of_last_login IS 'When the user last logged in';
COMMENT ON COLUMN users.birthday IS 'The birthday';

CREATE TABLE groups (
	group_id SERIAL PRIMARY KEY,
	name VARCHAR(255) NOT NULL UNIQUE,
	group_leader INTEGER REFERENCES users(user_id) ON UPDATE CASCADE ON DELETE CASCADE,
	group_founder INTEGER REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE users_groups (
	user_id INTEGER NOT NULL REFERENCES users(user_id) ON UPDATE CASCADE ON DELETE CASCADE,
	group_id INTEGER NOT NULL REFERENCES groups(group_id) ON UPDATE CASCADE ON DELETE CASCADE,
	PRIMARY KEY(user_id, group_id)
);

CREATE TABLE artists (
	artist_id SERIAL PRIMARY KEY,
	name VARCHAR(255) NOT NULL UNIQUE
);

CREATE TABLE albums (
	album_id SERIAL PRIMARY KEY,
	name VARCHAR(255) NOT NULL,
	year_released INTEGER NOT NULL,
	msrp DECIMAL(10,2) NOT NULL,
	genre VARCHAR(100) NOT NULL DEFAULT '',
	artist_id INTEGER NOT NULL REFERENCES artists(artist_id) ON UPDATE CASCADE ON DELETE CASCADE,
	UNIQUE (artist_id, name)
);

CREATE TABLE songs (
	song_id SERIAL PRIMARY KEY,
	name VARCHAR(255) NOT NULL,
	length TIME NOT NULL,
	album_id INTEGER NOT NULL REFERENCES albums(album_id) ON UPDATE CASCADE ON DELETE CASCADE,
	track_number INTEGER NOT NULL,
	UNIQUE(track_number, album_id)
);
CREATE UNIQUE INDEX uniq_name_album_idx ON songs (album_id, (lower(name)));

CREATE TABLE owns_on_cd (
	user_id INTEGER REFERENCES users(user_id) ON UPDATE CASCADE ON DELETE CASCADE,
	album_id INTEGER REFERENCES albums(album_id) ON UPDATE CASCADE ON DELETE CASCADE,
	PRIMARY KEY(user_id, album_id)
);

CREATE TABLE owns_on_tape (
	user_id INTEGER REFERENCES users(user_id) ON UPDATE CASCADE ON DELETE CASCADE,
	album_id INTEGER REFERENCES albums(album_id) ON UPDATE CASCADE ON DELETE CASCADE,
	PRIMARY KEY(user_id, album_id)
);

CREATE TABLE blobs (
	blob_id INTEGER PRIMARY KEY,
	data BYTEA NOT NULL
);