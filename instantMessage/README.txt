-:[ About ]:-

The instant message template will allow any web client to send and receive 
messages to and from avatars on a Linden Lab grid to which Corrade [1]
connects to. In this case Corrade acts as a relay for both receiving
instant messages on the grid and then displaying the messages online via
this template as well as a relay for sending messages by sending an
instant message for every message sent by this template.

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
      * message
  4.) Run the installMessage.php file from a console (you only need to 
      do this once):
      php installMessage.php
  5.) Place the all the files in a directory on your webserver.
  6.) Navigate to instantMessage.html in your browser and enjoy.

-:[ References ]:-

[1] Corrade - http://grimore.org/secondlife/scripted_agents/corrade
