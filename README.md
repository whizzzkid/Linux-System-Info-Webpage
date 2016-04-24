# RPi-System-Info-Script
This script is inspired from https://gist.github.com/jvhaarst/4388108/ and uses [MDL](https://getmdl.io/) and [Google Charts](https://developers.google.com/chart/interactive/docs/gallery).

# Why
The original script renders a boring page with no beauty. What's more beautiful than material design. This script can be used on any linux based distro, but I am using this for my RPi Print Server which I set up [here](https://nishantarora.in/minimal-raspberry-pi-google-cloud-print-server.naml).

#Screenshots
![Dashboard](http://i.imgur.com/Fk1v8Tm.png)

![Sys Info](http://i.imgur.com/Cl1GxrJ.png)

# Installation

    #apt-get install -y libapache2-mod-php5 lsb-release lsscsi git -y
    # cd /var/www/html
    # wget https://raw.githubusercontent.com/whizzzkid/RPi-System-Info-Script/master/index.php

 Done!

# Features

 - Completely rewritten with performance in mind.
 - Less processing on server side (we’re running this on Pi) more processing to the user.
 - Complete Material Design Compliance.
 - Google Charts :-)
 - No dependency.
 - Responsive Design.

# License: MIT
