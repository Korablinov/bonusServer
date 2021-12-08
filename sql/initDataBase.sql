CREATE TABLE contacts
(
    id         INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    contactID  INTEGER NOT NULL,
    bonusCount INTEGER NOT NULL
);

CREATE TABLE deals
(
    id         INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    dealID     INTEGER         NOT NULL,
    contactID  INTEGER         NOT NULL,
    dealStage  VARCHAR(15)     NOT NULL,
    processing INTEGER
);
