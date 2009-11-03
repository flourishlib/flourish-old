CREATE TABLE users (
	user_id INTEGER PRIMARY KEY AUTOINCREMENT,
	first_name VARCHAR(100) NOT NULL,
	middle_initial VARCHAR(100) NOT NULL DEFAULT '',
	last_name VARCHAR(100) NOT NULL,
	email_address VARCHAR(200) NOT NULL UNIQUE
);

CREATE TABLE groups (
	group_id INTEGER PRIMARY KEY AUTOINCREMENT,
	name VARCHAR(255) NOT NULL UNIQUE,
	group_leader INTEGER REFERENCES users(user_id) ON UPDATE CASCADE ON DELETE CASCADE,
	group_founder INTEGER REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE users_groups (
	user_id INTEGER NOT NULL REFERENCES users(user_id) ON UPDATE CASCADE ON DELETE CASCADE,
	group_id INTEGER NOT NULL REFERENCES groups(group_id) ON UPDATE CASCADE ON DELETE CASCADE,
	PRIMARY KEY(user_id, group_id)
);

BEGIN;

INSERT INTO users (first_name, middle_initial, last_name, email_address) VALUES ('Frank', '', 'Smith', 'frank@example.com');
INSERT INTO users (first_name, middle_initial, last_name, email_address) VALUES ('Jason', '', 'Johnson', 'jason@example.com');

INSERT INTO groups (name, group_leader, group_founder) VALUES ('Music Haters', 1, 2);

INSERT INTO users_groups (user_id, group_id) VALUES (1, 1);
INSERT INTO users_groups (user_id, group_id) VALUES (2, 1);

COMMIT;