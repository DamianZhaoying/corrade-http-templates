-:[ About ]:-

This template allows tracking group members parting and leaving a group
using the Corrade [1] scripted agent. Furthermore, Corrade does memorize
the current group members such that if Corrade is not online, it will
report the members that have left or joined when Corrade gets back online.

-:[ Requirements ]:-

  * A modern browser: Chome, Opera, Firefox, Safari...
  * A webserver.
  * PHP version 5 and beyond.
    * the curl extension.

-:[ Setup ]:-

  1.) Rename "config.php.dist" to "config.php" and edit "config.php" 
      to reflect your settings in Corrade.ini
  2.) Enable the Corrade permissions for your configured group:
      * notifications
  3.) Enable the notifications for your configured group:
      * membership
  4.) Run the installMembership.php file from a console (you only 
      need to do this once):
      php installMembership.php
  5.) Place the all the files in a directory on your webserver.
  6.) Navigate to trackMembership.html in your browser and enjoy.

-:[ References ]:-

[1] Corrade - http://grimore.org/secondlife/scripted_agents/corrade
