<?php
/**
 * Yakuza Main config
 */

class Main
{
    /**
     * Configuration options
     * @var LOCAL_CONFIG
     */
    private static $LOCAL_CONFIG;

    /**
     * Configuration options
     * @var BotConfig
     */
    public static $BOT_CONFIG;

    /**
     * Ansi codes array
     * @var ANSI_CODES
     */
    protected static $ANSI_CODES = array(
        "off"        => 0,
        "bold"       => 1,
        "italic"     => 3,
        "underline"  => 4,
        "blink"      => 5,
        "inverse"    => 7,
        "hidden"     => 8,
        "black"      => 30,
        "red"        => 31,
        "green"      => 32,
        "yellow"     => 33,
        "blue"       => 34,
        "magenta"    => 35,
        "cyan"       => 36,
        "white"      => 37,
        "black_bg"   => 40,
        "red_bg"     => 41,
        "green_bg"   => 42,
        "yellow_bg"  => 43,
        "blue_bg"    => 44,
        "magenta_bg" => 45,
        "cyan_bg"    => 46,
        "white_bg"   => 47
    );

    /** Public construct */
    public function __construct()
    {
        self::$LOCAL_CONFIG    = (object) parse_ini_file("config.ini", true);
        self::$BOT_CONFIG      = [
            'BOT_NAME'         => self::$LOCAL_CONFIG->YakuzaBot['botname'],
            'BOT_CMD'          => self::$LOCAL_CONFIG->YakuzaBot['botCmd'],
            'SERVER_HOST'      => self::$LOCAL_CONFIG->YakuzaBot['connection']['host'],
            'SERVER_PORT'      => self::$LOCAL_CONFIG->YakuzaBot['connection']['port'],
            'SERVER_SSL'       => (bool) self::$LOCAL_CONFIG->YakuzaBot['connection']['ssl'],
            'SERVER_TIMEOUT'   => self::$LOCAL_CONFIG->YakuzaBot['connection']['timeout'],
            'SERVER_CALLBACK'  => self::$LOCAL_CONFIG->YakuzaBot['connection']['callback'],
            'SERVER_AUTH'      => self::$LOCAL_CONFIG->YakuzaBot['auths']['enabled'],
            'SERVER_AUTH_TYPE' => self::$LOCAL_CONFIG->YakuzaBot['auths']['type'],
            'BOT_IDENT'        => self::$LOCAL_CONFIG->YakuzaBot['auths']['ident'],
            'BOT_NICK'         => self::$LOCAL_CONFIG->YakuzaBot['auths']['nick'],
            'BOT_USERNAME'     => self::$LOCAL_CONFIG->YakuzaBot['auths']['username'],
            'BOT_PASSWORD'     => self::$LOCAL_CONFIG->YakuzaBot['auths']['password'],
            'CHANNELS'         => [
                'JOIN'         => explode(" ", self::$LOCAL_CONFIG->YakuzaBot['channels']['join']),
                'PART'         => explode(" ", self::$LOCAL_CONFIG->YakuzaBot['channels']['part'])
            ],
            'MAIN_CHANNEL'     => self::$LOCAL_CONFIG->YakuzaBot['bot']['mainchannel'],
            'BOT_COMMANDS'     => explode(" ", strtoupper(self::$LOCAL_CONFIG->YakuzaBot['bot']['commands'])),
            'BOT_DEBUG'        => self::$LOCAL_CONFIG->YakuzaBot['botdebug']
        ];
    }

    /** Check log type */
    private static function logType(int $type)
    {
        switch ($type) {
            case 1: return "<-";
            case 2: return "->";
            case 3: return "X";
            default: return "==";
        }
    }

    /** Print response from server in console */
    public static function log(string $text, string $color = "cyan", int $type = 0)
    {
        echo self::SetColor("[" . date("H:i:s") . "] [". self::logType($type)."] $text\n\r", $color);
    }

    /** Check the sock type */
    public static function SocketType()
    {
        return (self::$BOT_CONFIG['SERVER_SSL']) ? "tls": "tcp";
    }

    /** Check the type of chart was receive */
    public static function type($string)
    {
        return is_array($string) || is_object($string) ? count($string) : strlen($string);
    }

    /** Check if is a valid bot command */
    public static function validBotCommands(string $command)
    {
        return in_array($command, self::$BOT_CONFIG['BOT_COMMANDS']) ? true : false;
    }

    /** Check if the operation is valid */
    public static function isValidOperation($operation)
    {
        switch ($operation) {
            case 'JOIN':
            case 'QUIT':
            case 'PART':
            case 'NICK':
            case 'MODE':
            case 'PING':
            case 'PRIVMSG': return true;
            default: return false;
        }
    }

    /** Parse mask */
    public static function ParseMask($data)
    {
        $data = preg_split("/(!|@)/", $data, 3);
        unset($data[1]);
        //array_diff($data, [$data[1]]
        return $data;
    }

    /** Parse Command */
    public static function parseCommand($data)
    {
        return strtoupper(explode(' ', $data)[0]);
    }

    /** Check if the msg have the bot delimiter */
    public static function validBotCMD(string $msg)
    {
        return self::$BOT_CONFIG['BOT_CMD'] == substr($msg, 0, 1) ? true : false;
    }

    /** Parse the irc response */
    public static function ParseFilter($message)
    {
        if ($message[0] != ':') $message = ":" . self::$BOT_CONFIG['SERVER_HOST'] . " $message ";

        $message = mb_strcut($message, 0, self::type($message), 'UTF-8');

        $message = explode(" ", str_replace(array('[', ']',' *', '*', ':', '***'), "", $message), 4);

        $nArray = NULL;
        if (self::isValidOperation($message[1])) {
            switch ($message[1]) {
                case 'JOIN':
                    $pNick = self::ParseMask($message[0]);
                    $nArray = [
                        'MASK' => $message[0],
                        'OPERATION' => $message[1],
                        'NICK' => $pNick[0],
                        'HOST' => $pNick[2],
                        'CHANNEL' => $message[2]
                    ];
                    self::log("[+JOIN] NICK: $pNick[0] | CHANNEL: $message[2]", "magenta", 1);
                    break;
                case 'QUIT':
                    $nArray = [
                        'MASK' => $message[0],
                        'OPERATION' => $message[1],
                        'REASON' => $message[2]
                    ];
                    self::log("[-QUIT] MASK: $message[0] | REASON: $message[2]", "magenta", 1);
                    break;
                case 'PART':
                    $pNick = self::ParseMask($message[0]);
                    $nArray = [
                        'MASK' => $message[0],
                        'OPERATION' => $message[1],
                        'NICK' => $pNick[0],
                        'HOST' => $pNick[2],
                        'CHANNEL' => $message[2]
                    ];
                    self::log("[-PART] NICK: $pNick[0] | CHANNEL: $message[2]", "magenta", 1);
                    break;
                case 'NICK':
                    $pNick = self::ParseMask($message[0]);
                    $nArray = [
                        'MASK' => $message[0],
                        'OPERATION' => $message[1],
                        'NICK' => $pNick[0],
                        'HOST' => $pNick[2],
                        'NEW_NICK' => $message[2]
                    ];
                    self::log("[*NICK] OLD_NICK: $pNick[0] | NEW_NICK: $message[2]", "magenta", 1);
                    break;
                case 'MODE':
                    $nArray = [
                        'MASK' => $message[0],
                        'OPERATION' => $message[1],
                        'CHANNEL' => $message[2],
                        'CMD' => $message[3]
                    ];
                    self::log("[*MODE] MASK: $message[0] | CHANNEL: $message[2] | CMD: $message[3]", "magenta", 1);
                    break;
                case 'PRIVMSG':
                    $pNick = self::ParseMask($message[0]);
                    $nArray = [
                        'MASK' => $message[0],
                        'OPERATION' => $message[1],
                        'CHANNEL' => $message[2],
                        'NICK' => $pNick[0],
                        'HOST' => $pNick[2],
                        'MSG' => substr($message[3], 0)
                    ];
                    self::log("[*MSG] NICK: $pNick[0] | CHANNEL: $message[2] | MSG: " . substr($message[3], 0, 70), "magenta", 1);
                    break;
                case 'PING':
                    $nArray = [
                        'MASK' => $message[0],
                        'OPERATION' => $message[1],
                        'DATA' => $message[2]
                    ];
                    break;
            }
            unset($message);
            return $nArray;
        }
        unset($message);
        return false;
    }

    /** Set string color name to change with a ansi code */
    public static function SetColor($str, $color)
    {
        $color_attrs = explode("+", $color);
        $ansi_str = "";
        foreach ($color_attrs as $attr) {
            $ansi_str .= "\033[" . self::$ANSI_CODES[$attr]. "m";
        }
        $ansi_str .= $str . "\033[" . self::$ANSI_CODES["off"] . "m";;
        return $ansi_str;
    }
}
