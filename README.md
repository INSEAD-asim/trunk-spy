# Trunk Spy

Notifies recipients everytime trunk or any branch is updated.

## Installation

Below are the installation instructions. Should be straightforward.

* Get the full source code here at github and put it on any server.
* Copy `config.php.sample` to create a new file called `config.php`.
* Fill in the correct configurations on `config.php`. Enter as many as possible.
* Make sure the directory where you save the XML files are writable by you.
* Make sure your server is capable of sending email like sendmail. Otherwise, you will need to implement your own email notification script. Modify `Svn/Agent.php` method `notifyUsers()` to implement your own email capability.
* Run the script via cron or any scheduler, ex: `php /path/to/trunk-spy.php`.
* For post update actions, configure `postUpdate` for each entry in 'config.php'. You can run scripts and send report(s) as attachment(s) to recipients i.e. run unit tests and send report.

## Questions? Suggestions?

You may contact Lysender at (http://www.lysender.com/contact)
You may contact me at (https://github.com/INSEAD-asim)
