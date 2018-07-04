<?php
/*
* @author Ronit Mukherjee
* @createdOn 03-07-2018
*/

namespace apple_push_notification;

require_once "ApplePushNotificationException.php";

class ApplePushNotification
{
    private $pemPassphrase;
    private $pemFile;
    private $pushUrl;
    private const IN_SANDBOX_MODE = TRUE;//Change to FALSE when going live

    /**
     * ApplePushNotification constructor.
     */
    public function __construct($passphrase = NULL, $sandboxFile = NULL, $prodFile = NULL)
    {
        if (is_null($passphrase)) {
            throw new ApplePushNotificationException("PEM file passpharase not passed");
        }


        if (self::IN_SANDBOX_MODE) {
            $file = $sandboxFile;
            $this->pushUrl = "ssl://gateway.sandbox.push.apple.com:2195";
        } else {
            $file = $prodFile;
            $this->pushUrl = "ssl://gateway.push.apple.com:2195";
        }

        if (is_null($file)) {
            throw new ApplePushNotificationException("PEM file not passed");
        }

        $this->setPemFile($file);
        $this->setPemPassphrase($passphrase);
    }


    /**
     * @return mixed
     */
    private function getPemPassphrase()
    {
        return $this->pemPassphrase;
    }

    /**
     * @param mixed $pemPassphrase
     */
    private function setPemPassphrase($pemPassphrase)
    {
        $this->pemPassphrase = $pemPassphrase;
    }

    /**
     * @return mixed
     */
    private function getPemFile()
    {
        return $this->pemFile;
    }

    /**
     * @param mixed $pemFile
     */
    private function setPemFile($pemFile)
    {
        $this->pemFile = $pemFile;
    }


    public function sendPush($deviceIds = NULL, $message = "", $data = array())
    {
        if (empty($deviceIds)) {
            throw new \apple_push_notification\ApplePushNotificationException("Device token(s) not passed to which push need to be sent");
        } else {
            if (empty($message)) {
                throw new \apple_push_notification\ApplePushNotificationException("Push message is not passed");
            } else {
                // tr_to_utf function needed to fix the Turkish characters
                $message = $this->tr_to_utf($message);

                // load your device ids to an array
                if (gettype($deviceIds) === "string") {
                    $deviceIds = array($deviceIds);
                }


                // this is where you can customize your notification
                $payloadArr = array(
                    "aps" => array(
                        "alert" => $message,
                        "sound" => "default",
                    )
                );

                if (!empty($data)) {
                    $payloadArr["data"] = $data;
                }


                $payload = json_encode($payloadArr);


                ////////////////////////////////////////////////////////////////////////////////
                // start to create connection
                $ctx = stream_context_create();
                stream_context_set_option($ctx, 'ssl', 'local_cert', $this->getPemFile());
                stream_context_set_option($ctx, 'ssl', 'passphrase', $this->getPemPassphrase());

                // Open a connection to the APNS server
                $fp = stream_socket_client($this->pushUrl, $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);

                //echo count($deviceIds) . ' devices will receive notifications.<br />';
                $undeliveredMsgCount = 0;
                foreach ($deviceIds as $item) {
                    // wait for some time
                    sleep(1);


                    if (!$fp) {
                        throw new \apple_push_notification\ApplePushNotificationException("Failed to connect: $err $errstr");
                    } else {
                        //echo 'Apple service is online. ' . '<br />';
                    }

                    // Build the binary notification
                    $msg = chr(0) . pack('n', 32) . pack('H*', $item) . pack('n', strlen($payload)) . $payload;

                    // Send it to the server
                    $result = fwrite($fp, $msg, strlen($msg));

                    if (!$result) {
                        $undeliveredMsgCount++;
                    } else {
                        //echo 'Delivered message count: ' . $item . '<br />';
                    }
                }

                if ($fp) {
                    fclose($fp);
                }

                //echo count($deviceIds) . ' devices have received notifications.<br />';
            }
        }
    }


    // function for fixing Turkish characters
    private function tr_to_utf($text)
    {
        $text = trim($text);

        $search = array('Ü', 'Þ', 'Ð', 'Ç', 'Ý', 'Ö', 'ü', 'þ', 'ð', 'ç', 'ý', 'ö');
        $replace = array('Ãœ', 'Åž', '&#286;ž', 'Ã‡', 'Ä°', 'Ã–', 'Ã¼', 'ÅŸ', 'ÄŸ', 'Ã§', 'Ä±', 'Ã¶');
        $new_text = str_replace($search, $replace, $text);

        return $new_text;
    }
}

?>

<?php


?>
