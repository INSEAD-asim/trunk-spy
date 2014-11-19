<?php

/**
 * Created by PhpStorm.
 * User: asim.mohammad
 * Date: 10/24/14
 * Time: 3:24 PM
 *
 * help from http://www.programmingfacts.com/sending-email-attachment-phps/
 */
class Mail
{
    private $from = 'mailer@trunkspy.net';

    function __construct($from = '')
    {
        if ($from != '') {
            $this->from = $from;
        }
    }

    public function send($to, $subject, $message, $files = array(), $reply_to = '')
    {
        $random_hash = md5(date('r', time()));

        $headers = "From: " . $this->from . "\r\n";

        if ($reply_to != '') {
            $headers .= "Reply-To: " . $reply_to . "\r\n";
        }

        $headers .= "Content-Type: multipart/mixed; boundary=\"PHP-mixed-" . $random_hash . "\"\r\n\r\n";
        $headers .= "This is a multi-part message in MIME format.\r\n";

        $text = $message;
        $html = nl2br($message);

        $output = "--PHP-mixed-$random_hash
Content-Type: multipart/alternative; boundary=\"PHP-alt-$random_hash\"
--PHP-alt-$random_hash
Content-Type: text/plain; charset=\"iso-8859-1\"
Content-Transfer-Encoding: 7bit

$text

--PHP-alt-$random_hash
Content-Type: text/html; charset=\"iso-8859-1\"
Content-Transfer-Encoding: 7bit

<div style='font-family: consolas;'>$html</div>

--PHP-alt-$random_hash--
";

        foreach ($files as $file) {
            $content_type = mime_content_type($file);
            $filename     = basename($file);
            $attachment   = chunk_split(base64_encode(file_get_contents($file)));
            $output .= "
--PHP-mixed-$random_hash
Content-Type: $content_type; name=$filename
Content-Transfer-Encoding: base64
Content-Disposition: attachment

$attachment

--PHP-mixed-$random_hash--
";
        }

        if (mail($to, $subject, $output, $headers)) {
            return true;
        }

        return false;
    }
}