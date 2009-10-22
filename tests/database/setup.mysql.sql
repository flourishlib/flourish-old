CREATE TABLE users (
	user_id INTEGER PRIMARY KEY AUTO_INCREMENT,
	first_name VARCHAR(100) NOT NULL,
	middle_initial VARCHAR(100) NOT NULL DEFAULT '',
	last_name VARCHAR(100) NOT NULL,
	email_address VARCHAR(200) NOT NULL UNIQUE,
	status ENUM('Active', 'Inactive', 'Pending') NOT NULL DEFAULT 'Active',
	times_logged_in INTEGER NOT NULL DEFAULT 0,
	date_created DATETIME NOT NULL,
	birthday DATE,
	time_of_last_login TIME,
	is_validated BOOLEAN NOT NULL DEFAULT FALSE,
	hashed_password VARCHAR(100) NOT NULL
)ENGINE=InnoDB;

CREATE TABLE groups (
	group_id INTEGER PRIMARY KEY AUTO_INCREMENT,
	name VARCHAR(255) NOT NULL UNIQUE,
	group_leader INTEGER,
	FOREIGN KEY (group_leader) REFERENCES users(user_id) ON UPDATE CASCADE ON DELETE CASCADE,
	group_founder INTEGER,
	FOREIGN KEY (group_founder) REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB;

CREATE TABLE users_groups (
	user_id INTEGER NOT NULL,
	FOREIGN KEY (user_id) REFERENCES users(user_id) ON UPDATE CASCADE ON DELETE CASCADE,
	group_id INTEGER NOT NULL,
	FOREIGN KEY (group_id) REFERENCES groups(group_id) ON UPDATE CASCADE ON DELETE CASCADE,
	PRIMARY KEY(user_id, group_id)
)ENGINE=InnoDB;

CREATE TABLE artists (
	artist_id INTEGER PRIMARY KEY AUTO_INCREMENT,
	name VARCHAR(255) NOT NULL UNIQUE
)ENGINE=InnoDB;

CREATE TABLE albums (
	album_id INTEGER PRIMARY KEY AUTO_INCREMENT,
	name VARCHAR(255) NOT NULL,
	year_released INTEGER NOT NULL,
	msrp DECIMAL(10,2) NOT NULL,
	genre VARCHAR(100) NOT NULL DEFAULT '',
	artist_id INTEGER NOT NULL,
	FOREIGN KEY (artist_id) REFERENCES artists(artist_id) ON UPDATE CASCADE ON DELETE CASCADE,
	UNIQUE (artist_id, name)
)ENGINE=InnoDB;

CREATE TABLE songs (
	song_id INTEGER PRIMARY KEY AUTO_INCREMENT,
	name VARCHAR(255) NOT NULL,
	length TIME NOT NULL,
	album_id INTEGER NOT NULL,
	FOREIGN KEY (album_id) REFERENCES albums(album_id) ON UPDATE CASCADE ON DELETE CASCADE,
	track_number INTEGER NOT NULL,
	UNIQUE(track_number, album_id)
)ENGINE=InnoDB;

CREATE TABLE owns_on_cd (
	user_id INTEGER NOT NULL,
	FOREIGN KEY (user_id) REFERENCES users(user_id) ON UPDATE CASCADE ON DELETE CASCADE,
	album_id INTEGER NOT NULL,
	FOREIGN KEY (album_id) REFERENCES albums(album_id) ON UPDATE CASCADE ON DELETE CASCADE,
	PRIMARY KEY(user_id, album_id)
)ENGINE=InnoDB;

CREATE TABLE owns_on_tape (
	user_id INTEGER NOT NULL,
	FOREIGN KEY (user_id) REFERENCES users(user_id) ON UPDATE CASCADE ON DELETE CASCADE,
	album_id INTEGER NOT NULL,
	FOREIGN KEY (album_id) REFERENCES albums(album_id) ON UPDATE CASCADE ON DELETE CASCADE,
	PRIMARY KEY(user_id, album_id)
)ENGINE=InnoDB;

CREATE TABLE blobs (
	blob_id INTEGER PRIMARY KEY,
	data BLOB NOT NULL
)ENGINE=InnoDB;

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

INSERT INTO albums (name, year_released, msrp, genre, artist_id) VALUES ('Give Up', 2003, '13.98', 'Alternative', 1);
INSERT INTO albums (name, year_released, msrp, genre, artist_id) VALUES ('Viva Nueva!', 2001, '15.99', 'Rock', 2);
INSERT INTO albums (name, year_released, msrp, genre, artist_id) VALUES ('Long Division', 1996, '9.99', 'Rock', 2);

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

INSERT INTO blobs (blob_id, data) VALUES (1, x'5527939aca3e9e80d5ab3bee47391f0f');

COMMIT;