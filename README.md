# colderCall
A web-based tool for calling on random students to enhance checks for understanding as used in the latest instructional models.
##  Dependencies:
This app was designed with 
* PHP 7.3
* Javascript (using ECMAScript 6) with jQuery and Bootstrap 4.
* SQLite 3.

YMMV on what newer or older versions are compatible. 
## Set-Up
This version comes with the JavaScript dependencies in the static/ folder. You can use a program of your choice to update the class lists from CSV to SQLite. It will look for coldcalls.sqlite3. If the file is present, you're set. If not, it will create an empty database for you with nice defaults.
## Use
On load, it will pick a volunteer from your default class. Click on "correct", "skip", or "incorrect". The database will keep a count of correct and incorrect answers given for each student, including a time and date stamp. "Skip" just chooses a new person.
Clicking on "Students" shows you a list of the students in the current period. This lets you see how they are doing. If a student is absent, you can click the checkbox to check "absent" and that status will reset on the next day. If that student for whatever reason should not be cold-called on a permanent basis, you can uncheck "enabled" and save the statuses. 

## Preferences
You can set a default period and specify how many periods you want, up to 9. For each period, you can select whether you want volunteers, how
and if you want to allow volunteers from time to time by having "Volunteer" be a possible result.  Note that accidentally changing the number of periods will not delete your students data if it's in the database. Changing it on purpose won't do it either.
For each period, you can also specify how many people should go before a repeat is possible for each period. Dragging the slider all the way to the left will let people be called more than once in a row. Dragging it all the way to the right will make it wait until the entire class has been called first.
You can also add a "bias" to each student if you want to make it slightly more likely they get called on, up to 10 times more likely. I plan to add a "less likely" feature and one that lets this bias be affected by their correct/incorrect percentage.
These settings are saved in the database. 
## Bookmarks
You can create bookmarks in your browser to go directly to periods. For example "https://myserver/random.php?p=2" will take you directly to second period.
## Roadmap
* ~~Connect with Google Classroom~~ This is just a waste of time. Too much overhead to use their API. Instead I will try and streamline database import and export.
* Less likely and auto-biasing algorithm
* Appfying using electron.js or similar
