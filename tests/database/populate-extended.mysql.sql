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
