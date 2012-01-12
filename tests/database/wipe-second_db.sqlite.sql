DELETE FROM groups;
DELETE FROM users;
DELETE FROM sqlite_sequence WHERE name = 'users';
DELETE FROM sqlite_sequence WHERE name = 'groups';