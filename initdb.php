<?php
$init_sql = "--
-- File generated with SQLiteStudio v3.2.1 on Thu Jul 25 13:26:42 2019
--
-- Text encoding used: UTF-8
--
PRAGMA foreign_keys = off;
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


-- Table: STUDENTS
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


-- Table: userPreferences
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
    nameSelection   INT     CHECK (nameSelection = 1 OR 
                                   nameSelection = 3 OR 
                                   nameSelection = 5) 
                            NOT NULL
                            DEFAULT (3) 
);


COMMIT TRANSACTION;
PRAGMA foreign_keys = on;
";

// Upload and receive a CSV file from Google Classroom.