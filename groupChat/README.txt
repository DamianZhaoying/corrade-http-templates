-:[ About ]:-

The group chat template uses Corrade [1] as a relay in order to send
and receive messages to and from a group on a Linden Lab grid.

-:[ Requirements ]:-

  * A modern browser: Chome, Opera, Firefox, Safari...
  * A webserver.
  * PHP version 5 and beyond.
    * the curl extension.

-:[ Setup ]:-

  1.) Rename "config.php.dist" to "config.php" and edit "config.php" 
      to reflect your settings in Corrade.ini
  2.) Enable the Corrade permissions for your configured group:
      * talk
      * notifications
  3.) Enable the notifications for your configured group:
      * group
  4.) Run the installGroup.php file from a console (you only need to 
      do this once):
      php installGroup.php
  5.) Place the all the files in a directory on your webserver.
  6.) Navigate to groupChat.html in your browser and enjoy.

-:[ References ]:-

[1] Corrade - http://grimore.org/secondlife/scripted_agents/corrade
