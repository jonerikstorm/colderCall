# colderCall
A web-based tool for calling on random students to enhance checks for understanding.
##  Dependencies:
This app was designed with 
* PHP 7.3
* ECMAScript 6
* SQLite 3.
YMMV on what is compatible.
## Set-Up
This version comes with the JavaScript dependencies in the static/ folder and with a stub database. If I write more for this, I will have it initialize a database. You can use a program of your choice to update the class lists from CSV to SQLite. 
## Use
On load, it will pick a volunteer from your default class. Click on "correct", "skip", or "incorrect". The database will keep a count of correct and incorrect answers given. "Skip" just chooses a new person.
Clicking on "Students" shows you a list of the students in the current period. This lets you see how they are doing. If a student is absent, you can click the checkbox to uncheck "enabled." If that student for whatever reason should not be cold-called, you can save the statuses. (Note for future: we should separate absent and disabled).
## Preferences
You can set a default period, whether you want it to be truly random and allow repeats, and, if you want to allow volunteers from time to time by having "Volunteer" be a possible result. This settings are saved in the database. 
## Bookmarks
You can create bookmarks in your browser to go directly to periods. For example "https://myserver/random.php?p=2" will take you directly to second period.
## Roadmap
* Connect with Google Classroom
* Database initialization
* Absent / Permanent disable
* Appfying using electron.js or similar
