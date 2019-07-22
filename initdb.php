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
CREATE TABLE userPreferences (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    defaultPeriod   INT     CHECK (defaultPeriod > 0 AND 
                                   defaultPeriod < 7) 
                            DEFAULT (1) 
                            NOT NULL,
    allowVolunteers BOOLEAN NOT NULL
                            DEFAULT true,
    allowRepeats    BOOLEAN NOT NULL
                            DEFAULT false,
    numPeriods      INT     DEFAULT 6
                            NOT NULL,
    minimumBetween  INT     CHECK (minimumBetween >= -1) 
                            DEFAULT (1) 
                            NOT NULL,
    includeLastName BOOLEAN NOT NULL
                            DEFAULT true,
    includeLastInitial BOOLEAN NOT NULL
                            DEFAULT false
);

COMMIT TRANSACTION;
PRAGMA foreign_keys = on;
";

// Upload and receive a CSV file from Google Classroom.