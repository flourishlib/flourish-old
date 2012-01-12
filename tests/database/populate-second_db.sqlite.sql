BEGIN;

INSERT INTO users (first_name, middle_initial, last_name, email_address) VALUES ('Frank', '', 'Smith', 'frank@example.com');
INSERT INTO users (first_name, middle_initial, last_name, email_address) VALUES ('Jason', '', 'Johnson', 'jason@example.com');

INSERT INTO groups (name, group_leader, group_founder) VALUES ('Music Haters', 1, 2);

INSERT INTO users_groups (user_id, group_id) VALUES (1, 1);
INSERT INTO users_groups (user_id, group_id) VALUES (2, 1);

COMMIT;