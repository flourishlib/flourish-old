BEGIN;
DELETE FROM flourish2.albums;
DELETE FROM flourish2.artists;
DELETE FROM flourish2.users_groups;
DELETE FROM flourish2.groups;
DELETE FROM flourish2.users;
COMMIT;

DROP SEQUENCE flourish2.users_user_id_seq;
DROP SEQUENCE flourish2.groups_group_id_seq;
DROP SEQUENCE flourish2.artists_artist_id_seq;
DROP SEQUENCE flourish2.albums_album_id_seq;

CREATE SEQUENCE flourish2.users_user_id_seq;
CREATE SEQUENCE flourish2.groups_group_id_seq;
CREATE SEQUENCE flourish2.artists_artist_id_seq;
CREATE SEQUENCE flourish2.albums_album_id_seq;
