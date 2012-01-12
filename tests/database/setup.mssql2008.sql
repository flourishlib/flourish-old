CREATE TABLE users (
	user_id INTEGER IDENTITY(1,1) PRIMARY KEY,
	first_name VARCHAR(100) NOT NULL,
	middle_initial VARCHAR(100) NOT NULL DEFAULT '',
	last_name VARCHAR(100) NOT NULL,
	email_address VARCHAR(200) NOT NULL UNIQUE,
	status VARCHAR(8) NOT NULL DEFAULT 'Active' CHECK(status IN ('Active', 'Inactive', 'Pending')),
	times_logged_in INTEGER NOT NULL DEFAULT 0,
	date_created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	birthday DATE NULL,
	time_of_last_login TIME NULL,
	is_validated BIT NOT NULL DEFAULT 0,
	hashed_password VARCHAR(100) NOT NULL
);
EXEC sys.sp_addextendedproperty @name='MS_Description',
   @value='This hash is generated using fCryptography::hashPassword()',
   @level0type='SCHEMA', @level0name='dbo',
   @level1type='TABLE',  @level1name='users',
   @level2type='COLUMN', @level2name='hashed_password';
EXEC sys.sp_addextendedproperty @name='MS_Description',
   @value='When the user last logged in',
   @level0type='SCHEMA', @level0name='dbo',
   @level1type='TABLE',  @level1name='users',
   @level2type='COLUMN', @level2name='time_of_last_login';
EXEC sys.sp_addextendedproperty @name='MS_Description',
   @value='The birthday',
   @level0type='SCHEMA', @level0name='dbo',
   @level1type='TABLE',  @level1name='users',
   @level2type='COLUMN', @level2name='birthday';

CREATE TABLE groups (
	group_id INTEGER IDENTITY(1,1) PRIMARY KEY,
	name VARCHAR(255) NOT NULL UNIQUE,
	group_leader INTEGER NULL REFERENCES users(user_id) ON UPDATE CASCADE ON DELETE CASCADE,
	group_founder INTEGER NULL REFERENCES users(user_id) ON UPDATE NO ACTION ON DELETE NO ACTION
);

CREATE TABLE users_groups (
	user_id INTEGER NOT NULL REFERENCES users(user_id) ON UPDATE CASCADE ON DELETE CASCADE,
	group_id INTEGER NOT NULL REFERENCES groups(group_id) ON UPDATE NO ACTION ON DELETE NO ACTION,
	PRIMARY KEY(user_id, group_id)
);

CREATE TABLE artists (
	artist_id INTEGER IDENTITY(1,1) PRIMARY KEY,
	name VARCHAR(255) NOT NULL UNIQUE
);

CREATE TABLE albums (
	album_id INTEGER IDENTITY(1,1) PRIMARY KEY,
	name VARCHAR(255) NOT NULL,
	year_released INTEGER NOT NULL,
	msrp DECIMAL(10,2) NOT NULL,
	genre VARCHAR(100) NOT NULL DEFAULT '',
	artist_id INTEGER NOT NULL REFERENCES artists(artist_id) ON UPDATE CASCADE ON DELETE CASCADE,
	UNIQUE (artist_id, name)
);

CREATE TABLE songs (
	song_id INTEGER IDENTITY(1,1) PRIMARY KEY,
	name VARCHAR(255) NOT NULL,
	length TIME NOT NULL,
	album_id INTEGER NOT NULL REFERENCES albums(album_id) ON UPDATE CASCADE ON DELETE CASCADE,
	track_number INTEGER NOT NULL,
	UNIQUE(track_number, album_id)
);

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
	data VARBINARY(MAX) NOT NULL
);