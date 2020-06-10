<?php
/**
 * Yakuza bot - irc Class
 *
 * @version 0.0.1 - Basic bot
 *
*/

class irc extends Main {
    /**
     * @array $ServerSocket
     */
    private static $ServerSocket = [];

    /**
     * @var $authComplete
     */
    private static $authComplete = false;

    /**
     * Local contruct
     */
    function __construct() {
        Main::__construct();
    }

    /**
     * function send(string message) => write a binary data.
     *
     * Max len 510
     *
     * @param message
     *
     * @return void
     *
     */
    private static function send(string $message)
    {
        if (strlen($message) > 510) {
            Main::log("Notice: An attempt was made to send an excessively long string. The string will be trimmed to 510 characters.", "red", 3);
        } else {
            Main::log($message, "green", 2);
            fwrite(irc::$ServerSocket['socket'], "$message\r\n", 510);
        }
    }

    /**
     * function ResponseType(int type, array options) => set a type of operation and response to server.
     *
     * In @var type you can set your type of operation.
     *
     * Case 0 = Send a custom message to specify channel.
     * Case 1 = Send a QUERY message / Private message to specify user.
     * Case 2 = Send a NOTICE to specify channel.
     * Case 3 = Kick a User from a specify channel.
     * Case 4 = Banned or Unbanned a specify User, this process requires high privileges on the bot.
     * Case 5 = Join or Part from specify channel.
     * Case 6 = Ping Pong.
     *
     * In @var options array you can set your specify array-data to your custom response by type.
     *
     * Case 0:
     * ResponseType(0, ['method' => 1, 'message' => "Hello mom!"]) ELSE ResponseType(0, ['method' => 0, 'channel' => "#MyCustomChannel", 'message' => "Hello mom!"])
     *
     * Case 1:
     * ResponseType(1, ['nickname' => "Jonny", 'message' => "Hello mom!"])
     *
     * Case 2:
     * ResponseType(2, ['channel' => "#MyChannel", 'message' => "Hello mom!"])
     *
     * Case 3:
     * ResponseType(3, ['channel' => "#MyChannel", 'nickname' => "Jack", 'message' => "Bye bye babe!"])
     *
     * Case 4:
     * ResponseType(4, ['method' => 1, 'channel' => "#MyChannel", 'usermask' => "5B6D.8D5A.9A6S.IP"]) ELSE ResponseType(4, ['method' => 0, 'channel' => "#MyChannel", 'usernick' => "Jack"])
     *
     * Case 5:
     * ResponseType(5, ['method' => 1, 'channel' => "#myOtherChannel"]) ELSE ResponseType(5, ['method' => 0, 'channel' => "#ads"])
     *
     * Case 6:
     * ResponseType(6, ['message' => "123456789"])
     *
     * @param type
     * @param options
     *
     * @return void
     */
    private static function ResponseType(int $type, array $options)
    {
        switch ($type) {
            case 0:
                ($options['method'] == 1) ? irc::send("PRIVMSG " . Main::$BOT_CONFIG['MAIN_CHANNEL'] . " :{$options['message']}"): irc::send("PRIVMSG {$options['channel']} :{$options['message']}");
                break;
            case 1:
                irc::send("QUERY {$options['nickname']} :{$options['message']}");
                break;
            case 2:
                irc::send("NOTICE {$options['channel']} :{$options['message']}");
                break;
            case 3:
                irc::send("KICK {$options['channel']} {$options['nickname']} :{$options['message']}");
                break;
            case 4:
                ($options['method'] == 1) ? irc::send("MODE {$options['channel']} +b ~t:120:*!*@{$options['userhost']}"): irc::send("MODE {$options['channel']} -b {$options['usernick']}");
                break;
            case 5:
                ($options['method'] == 1) ? irc::send("JOIN {$options['channel']}"): irc::send("PART {$options['channel']}");
                break;
            case 6:
                irc::send("PONG {$options['message']}");
                break;
        }
    }

    /**
     * Automatic auth to irc server
     * @param AUTH_TYPE
     */
    protected static function Auth(int $AUTH_TYPE)
    {
        if (Main::$BOT_CONFIG['SERVER_AUTH']) {
            Main::log("Trying to login with server...");
            if ($AUTH_TYPE == 0) {
                Main::log("Sasl Authentication...");
                irc::send("CAP REQ :sasl");
                irc::send("AUTHENTICATE PLAIN");
                irc::send("AUTHENTICATE ". base64_encode("\0".Main::$BOT_CONFIG['BOT_USERNAME'] . "\0".Main::$BOT_CONFIG['BOT_PASSWORD']));
                irc::send("CAP END");
            } elseif ($AUTH_TYPE == 1) {
                Main::log("Default Authentication...");
                irc::send("NICK " . Main::$BOT_CONFIG['BOT_NICK']);
                irc::send("USER " . Main::$BOT_CONFIG['BOT_IDENT'] . " " . Main::$BOT_CONFIG['BOT_USERNAME'] . " " . Main::$BOT_CONFIG['BOT_USERNAME'] . " :" . Main::$BOT_CONFIG['BOT_NICK']);
                irc::send("PASS " . Main::$BOT_CONFIG['BOT_PASSWORD']);
                irc::send("CAP REQ account-notify");
                irc::send("CAP REQ extended-join");
                irc::send("CAP END");
            } elseif ($AUTH_TYPE == 2) {
                Main::log("Authenticating with Nickserv...");
                irc::send("NICK " . Main::$BOT_CONFIG['BOT_NICK']);
                irc::send("USER " . Main::$BOT_CONFIG['BOT_IDENT'] . " " . Main::$BOT_CONFIG['BOT_USERNAME'] . " " . Main::$BOT_CONFIG['BOT_USERNAME'] . " :" . Main::$BOT_CONFIG['BOT_NICK']);
                irc::send("PRIVMSG NickServ :IDENTIFY ". Main::$BOT_CONFIG['BOT_NICK']. " " . Main::$BOT_CONFIG['BOT_PASSWORD']);
            } else {
                trigger_error("Invalid option was set on (auths[type]) in file config.ini", E_USER_ERROR);
            }
        } else {
            Main::log("Authentication disable, starting guess auth...");
            irc::send("NICK Y4kvz4B0T");
            irc::send("USER Y4kvz4B0T C0D3D D3VBL4CK :Y4kvz4B0T");
        }
    }

    /** Connect to irc server */
    public static function Connect()
    {
        Main::log("<C0D3D BY D3VBL4CK> - " . Main::$BOT_CONFIG['BOT_NAME'] . " - </C0D3D BY D3VBL4CK>");
        Main::log("Starting up...");
        Main::log("Connecting to Server: " . Main::$BOT_CONFIG['SERVER_HOST'] . ":" . Main::$BOT_CONFIG['SERVER_PORT']  .", Using SSL: " . Main::$BOT_CONFIG['SERVER_SSL']);
        $SocketType = Main::SocketType() . "://" . Main::$BOT_CONFIG['SERVER_HOST'] . ":" . Main::$BOT_CONFIG['SERVER_PORT'];
        $ServerStream = stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);
        @irc::$ServerSocket['socket'] = stream_socket_client($SocketType, $errno, $errstr, Main::$BOT_CONFIG['SERVER_TIMEOUT'], STREAM_CLIENT_CONNECT, $ServerStream);
        stream_set_timeout(irc::$ServerSocket['socket'], Main::$BOT_CONFIG['SERVER_CALLBACK']);
        if (irc::$ServerSocket['socket']) {
            Main::log("Connection established...");
            irc::Auth(Main::$BOT_CONFIG['SERVER_AUTH_TYPE']);
            while (!feof(irc::$ServerSocket['socket'])) {
                if (irc::$ServerSocket['buffer'] = trim(fgets(irc::$ServerSocket['socket'], 1024))) {
                    if (Main::$BOT_CONFIG['BOT_DEBUG']) Main::log(irc::$ServerSocket['buffer']);

                    irc::$ServerSocket['newBuffer'] = irc::filterBuffer(irc::$ServerSocket['buffer']);
                    if (irc::$authComplete) {
                        if (irc::$ServerSocket['newBuffer']['OPERATION'] == "PRIVMSG" && Main::validBotCMD(irc::$ServerSocket['newBuffer']['MSG'])) {
                            irc::FindCommands(
                                strtoupper(Main::parseCommand(irc::$ServerSocket['newBuffer']['MSG'])),
                                irc::$ServerSocket['newBuffer']['CHANNEL'],
                                irc::$ServerSocket['newBuffer']['NICK']
                            );
                        }
                    }
                }
            }
        } else {
            Main::log("Server connection failed: $errstr ($errno)", "red", 3);
        }
        Main::log("Socket disconnected.", "red", 3);
    }

    /** Filter buffer socket */
    private static function filterBuffer($message)
    {
        $message = Main::ParseFilter($message);

        if ($message) {
            if ($message['OPERATION'] == 'PING') {
                irc::ResponseType(6, ['message' => $message['DATA']]);

                if (!irc::$authComplete) {
                    Main::log("Auth complete!");
                    if (count(Main::$BOT_CONFIG['CHANNELS']['PART']) > 0) {
                        foreach (Main::$BOT_CONFIG['CHANNELS']['PART'] as $PChannels) {
                            irc::ResponseType(5, ['method' => 0, 'channel' => $PChannels]);
                        }
                    }

                    foreach (Main::$BOT_CONFIG['CHANNELS']['JOIN'] as $JChannels) {
                        irc::ResponseType(5, ['method' => 1, 'channel' => $JChannels]);
                    }
                    irc::$authComplete = true;
                }
            }
            return $message;
        }
    }

    /** If is valid command, find the response for that command */
    private static function FindCommands(string $command, string $channel, string $nick)
    {
        if (Main::validBotCommands($command)) {
            switch ($command) {
                case '!HELP':
                    irc::ResponseType(0, [
                        'method' => 0,
                        'channel' => $channel,
                        'message' => "Hello $nick, help command is active :)"
                    ]);
                break;
                case '!CODER':
                    irc::ResponseType(0, [
                        'method' => 0,
                        'channel' => $channel,
                        'message' => "Hello $nick, my master is D3BL4CK♥"
                    ]);
                break;
                /**
                 * TO ADD NEW COMMAND YOU NEED TO MAKE A NEW CASE LIKE:
                 * case '!MY_NEW_COMAND':
                 *  irc::ResponseType(0, [
                 *      'method' => 0,
                 *      'channel' => $channel,
                 *      'message' => "Hello $nick, my master is D3BL4CK ♥"
                 *     ]);
                 *  break;
                 */
            }
        }
    }
}