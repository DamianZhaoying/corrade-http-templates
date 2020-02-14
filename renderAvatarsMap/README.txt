-:[ About ]:-

The scripts are meant to display a 3D overview of a region in 
a Linden-Lab grid (such as Second Life) with the help of the
Wizardry and Steamworks Corrade scripted agent [1].

-:[ Requirements ]:-

  * A WebGL enabled browser: Chome, Opera, Firefox, Safari...
  * A webserver.
  * PHP version 5 and beyond.
    * the gd extension (libgd).
    * the curl extension.

-:[ Setup ]:-

  1.) Rename "config.php.dist" to "config.php" and edit "config.php" 
      to reflect your settings in Corrade.ini
  2.) Enable the Corrade permissions for your configured group:
      * land
      * interact
  3.) Place the all the files in a directory on your webserver.
  4.) Navigate to renderMapAvatars.html and enjoy.

-:[ References ]:-

[1] Corrade - http://grimore.org/secondlife/scripted_agents/corrade
