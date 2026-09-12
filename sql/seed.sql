USE COP4331;

INSERT INTO Users (FirstName, LastName, Login, Password) VALUES
  ('Demo', 'User', 'demo',   '$2y$10$djASruchNW4Qgk9DLqqbYeAa687RkldoITo7GvWMdsO7wy9B/bnlK'),
  ('Test', 'User', 'tester', '$2y$10$djASruchNW4Qgk9DLqqbYeAa687RkldoITo7GvWMdsO7wy9B/bnlK');

INSERT INTO Colors (UserID, Name) VALUES
  (1, 'Red'),
  (1, 'Green'),
  (1, 'Blue'),
  (1, 'Yellow'),
  (1, 'Cyan'),
  (1, 'Magenta');

INSERT INTO Colors (UserID, Name) VALUES
  (2, 'Black');

INSERT INTO Sessions (Token, UserID, ExpiresAt) VALUES
  ('devtoken0000000000000000000000000000000000000000000000000000face', 1, '2030-01-01 00:00:00');
