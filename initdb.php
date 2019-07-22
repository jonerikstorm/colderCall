<?php
$init_sql = "PRAGMA foreign_keys = off;
BEGIN TRANSACTION;

CREATE TABLE ANSWERS (
    id         INTEGER  PRIMARY KEY AUTOINCREMENT
                        NOT NULL,
    student_id INT      REFERENCES STUDENTS (id) 
                        NOT NULL,
    correct    BOOLEAN  NOT NULL,
    timestamp  DATETIME NOT NULL
);

CREATE TABLE STUDENTS (
    id        INT     PRIMARY KEY
                      UNIQUE
                      NOT NULL
                      DEFAULT (1),
    l_name    TEXT    NOT NULL,
    f_name    TEXT    NOT NULL,
    correct   INT     DEFAULT (0),
    incorrect INT     DEFAULT (0),
    period    INT     CONSTRAINT Period CHECK (period > 0 AND 
                                               period < 10) 
                      NOT NULL,
    enabled   BOOLEAN DEFAULT TRUE
                      NOT NULL
);


CREATE TABLE userPreferences (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    defaultPeriod   INT     CHECK (defaultPeriod > 0 AND 
                                   defaultPeriod < 10),
    allowVolunteers BOOLEAN NOT NULL,
    allowRepeats    BOOLEAN NOT NULL,
    numPeriods      INT     CHECK (numPeriods > 1 AND 
                                   numPeriods < 10) 
                            NOT NULL
);

INSERT INTO userPreferences (
                                id,
                                defaultPeriod,
                                allowVolunteers,
                                allowRepeats,
                                numPeriods
                            )
                            VALUES (
                                1,
                                1,
                                'true',
                                'false',
                                6
                            );

COMMIT TRANSACTION;
PRAGMA foreign_keys = on;
";

// Upload and receive a CSV file from Google Classroom.