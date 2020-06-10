# YakuzaBot
 A basic irc bot with some custom options and beautifully logs colors.

Instalation
--------

- For CentOS and Fedora: Copy the Centos file to root dir of your vps and type in terminal "bash Centos".
- For Ubuntu and Debian: Copy the Ubuntu file to root dir of your vps and type in terminal "bash Ubuntu".
- For Windows: [Guide][] (Only until step 3).

[Guide]: https://php.tutorials24x7.com/blog/how-to-install-php-7-on-windows

HOW ADD COMMANDS?
--------

- Step one, add a new command in config/config.ini, find "bot[commands]" and add !NEWCOMMAND inside of the " ", separated by a space from the other commands.
- Step two, Open the file lib/irc.yakuza.php and look up the "ResponseType(" name and read the commented text.
- Step three, in the same file look up the "FindCommands(" and read the commented text.
```php
//Example: Step one
bot[commands]     = "!HELP !CODER !MY_NEW_COMAND"

//Example: Step two
case '!MY_NEW_COMAND':
	irc::ResponseType(0, [
		'method' => 0,
		'channel' => $channel,
		'message' => "Hello $nick, my master is D3BL4CK ♥"
	]);
break;
```

TO RUN BOT
--------

### CentOS/Ubuntu
1. Copy all bot files to root dir
2. Go to bot dir: cd YakuzaBot/ and Configure the bot credentials in config/config.ini and saved it  (Here you can use any editor like vim, nano or visual code)
3. Go back to YakuzaBot main dir and type in terminal: bash RunBot and press Ctrl + a + d
4. Conneted to IRC-SERVER and join to your main-channel and type "!help".
5. have fun. 

### Windows
1. Go to bot files and Configure the bot credentials in config/config.ini and saved it.
2. open "wrun.bat".
3. Conneted to IRC-SERVER and join to your main-channel and type "!help".
4. have fun. 


More improved bot?
----------

Contact me B4$h%#0069
