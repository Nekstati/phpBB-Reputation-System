phpBB-Reputation-System
=======================

**This is the fork of the [Reputation System by Pico/jbreu](https://github.com/jbreu/phpBB-Reputation-System), but with heavily rewritten style and partially changed logic. The original extension can be updated to this one, but not vice versa.**

A full reputation (karma) system for phpBB3.3, with adjustable reputation settings.

## Features
* AJAX post rating
* AJAX user rating
* possibility to choose reputation power during giving a reputation point
* lots of ACP setting such as: enable/disable the whole system, enable/disable negative points, enable/disable comment, force to write a reputation comment, reputation power settings (base on post, groups, etc.)
* permissions system - you can adjust it to your board
* and more

## Quick Install
You can install this on the latest copy of phpBB ([phpBB 3.3](https://github.com/phpbb/phpbb3)) by following the steps below:

1. Download the latest repository.
2. Unzip the downloaded release, and change the name of the folder to `reputation`.
3. In the `ext` directory of your phpBB board, create a new directory named `pico` (if it does not already exist).
4. Copy the `reputation` folder to `phpBB/ext/pico/` (if done correctly, you'll have the main extension class at (your forum root)/ext/pico/reputation/ext.php).
5. Navigate in the ACP to `Customise -> Manage extensions`.
6. Look for `Reputation System` under the Disabled Extensions list, and click its `Enable` link.
7. Set up and configure Reputation System by navigating in the ACP to `Extensions` -> `Reputation System`.

## Uninstall
1. Navigate in the ACP to `Customise -> Extension Management -> Extensions`.
2. Look for `Reputation System` under the Enabled Extensions list, and click its `Disable` link.
3. To permanently uninstall, click `Delete Data` and then delete the `/ext/pico/reputation` folder.

## License
[GNU General Public License v2](http://opensource.org/licenses/GPL-2.0)

## Screenshots

### Miniprofile buttons

![Miniprofile buttons](/doc/miniprofile_widget.png)

### Post buttons (option)

![Post buttons (option)](/doc/post_widget.png)

### User reputation page

![User reputation page](/doc/user_rep.png)

### Summary reputation page

![Summary reputation page](/doc/all_reps.png)

### Positive rating popup

![Positive rating popup](/doc/positive.png)

### Negative rating popup

![Negative rating popup](/doc/negative.png)
