<?php
$init_sql = "PRAGMA foreign_keys = off;
BEGIN TRANSACTION;

-- Table: ANSWERS
CREATE TABLE ANSWERS (
    id         INTEGER  PRIMARY KEY AUTOINCREMENT
                        NOT NULL,
    student_id INT      REFERENCES STUDENTS (id) 
                        NOT NULL,
    correct    BOOLEAN  NOT NULL,
    timestamp  DATETIME NOT NULL
);


-- Table: globalPreferences
CREATE TABLE globalPreferences (
    id            INTEGER PRIMARY KEY AUTOINCREMENT
                          UNIQUE
                          NOT NULL
                          DEFAULT (0),
    defaultPeriod INTEGER NOT NULL
                          DEFAULT (1) 
                          UNIQUE
                          CHECK (defaultPeriod <= numPeriods),
    numPeriods    INTEGER NOT NULL
                          DEFAULT (6) 
                          UNIQUE
                          CHECK (numPeriods < 10),
    version       TEXT    NOT NULL
                          DEFAULT ('0.3.0'),
    lastID        TEXT
);
INSERT INTO globalPreferences (
                                  id,
                                  defaultPeriod,
                                  numPeriods,
                                  version,
                                  lastID
                              )
                              VALUES (
                                  4,
                                  1,
                                  6,
                                  '0.3.0',
                                  '[{},{},{}]'
                              );
CREATE TABLE periodPreferences (
    id              INTEGER PRIMARY KEY AUTOINCREMENT
                            NOT NULL
                            CHECK (id < 10),
    allowVolunteers BOOLEAN NOT NULL
                            DEFAULT true,
    minimumBetween  INTEGER CHECK (minimumBetween >= -1) 
                            DEFAULT (1) 
                            NOT NULL,
    nameSelection   INTEGER CHECK (nameSelection = 1 OR 
                                   nameSelection = 3 OR 
                                   nameSelection = 5) 
                            NOT NULL
                            DEFAULT (3) 
);

INSERT INTO periodPreferences (
                                  id,
                                  allowVolunteers,
                                  minimumBetween,
                                  nameSelection
                              )
                              VALUES (
                                  1,
                                  'false',
                                  0,
                                  3
                              );

INSERT INTO periodPreferences (
                                  id,
                                  allowVolunteers,
                                  minimumBetween,
                                  nameSelection
                              )
                              VALUES (
                                  2,
                                  'false',
                                  0,
                                  3
                              );

INSERT INTO periodPreferences (
                                  id,
                                  allowVolunteers,
                                  minimumBetween,
                                  nameSelection
                              )
                              VALUES (
                                  3,
                                  'false',
                                  0,
                                  3
                              );

INSERT INTO periodPreferences (
                                  id,
                                  allowVolunteers,
                                  minimumBetween,
                                  nameSelection
                              )
                              VALUES (
                                  4,
                                  'false',
                                  0,
                                  3
                              );

INSERT INTO periodPreferences (
                                  id,
                                  allowVolunteers,
                                  minimumBetween,
                                  nameSelection
                              )
                              VALUES (
                                  5,
                                  'false',
                                  0,
                                  3
                              );

INSERT INTO periodPreferences (
                                  id,
                                  allowVolunteers,
                                  minimumBetween,
                                  nameSelection
                              )
                              VALUES (
                                  6,
                                  'false',
                                  0,
                                  3
                              );

INSERT INTO periodPreferences (
                                  id,
                                  allowVolunteers,
                                  minimumBetween,
                                  nameSelection
                              )
                              VALUES (
                                  7,
                                  'false',
                                  0,
                                  3
                              );

INSERT INTO periodPreferences (
                                  id,
                                  allowVolunteers,
                                  minimumBetween,
                                  nameSelection
                              )
                              VALUES (
                                  8,
                                  'false',
                                  0,
                                  3
                              );

INSERT INTO periodPreferences (
                                  id,
                                  allowVolunteers,
                                  minimumBetween,
                                  nameSelection
                              )
                              VALUES (
                                  9,
                                  'false',
                                  0,
                                  3
                              );
CREATE TABLE STUDENTS (
    id          INT     PRIMARY KEY
                        UNIQUE
                        NOT NULL
                        DEFAULT (1),
    l_name      TEXT    NOT NULL,
    f_name      TEXT    NOT NULL,
    correct     INT     DEFAULT (0),
    incorrect   INT     DEFAULT (0),
    period      INT     CONSTRAINT Period CHECK (period > 0 AND 
                                                 period < 7) 
                        NOT NULL,
    enabled     BOOLEAN DEFAULT TRUE
                        NOT NULL,
    absent      BOOLEAN DEFAULT false
                        NOT NULL,
    absentDate  DATE    DEFAULT NULL,
    coefficient INTEGER DEFAULT (1) 
                        NOT NULL
                        CHECK (coefficient >= 0 AND 
                               coefficient <= 10) 
);
COMMIT TRANSACTION;
PRAGMA foreign_keys = on;

";
function db_init()
{
    $db_init = new PDO("sqlite:coldcalls.sqlite3");
    $init_queries = explode(";", $init_sql);
    foreach ($init_queries as $explodedQuery) {
        $db_init->query($explodedQuery . ";");
    }
    echo "Initializing database.";
}
