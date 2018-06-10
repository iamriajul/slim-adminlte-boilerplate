<?php
/**
 * Created by PhpStorm.
 * User: Riajul
 * Date: 02-Mar-18
 * Time: 11:21 AM
 */

namespace App\Controllers;


use Monolog\Logger;
use phpDocumentor\Reflection\Types\Array_;
use Psr\Container\ContainerInterface;
use PDO;
use Slim\App;
use Slim\Http\UploadedFile;
use Slim\Router;
use Slim\Views\Twig;

/**
 * Class Controller
 * @package App\Controllers
 *
 * Properties you can have access:
 * @property Logger $logger
 * @property Twig $view
 * @property PDO $db
 * @property Router $router
 *
 */
abstract  class Controller
{
    public $appName = "My App";

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var array
     */
    protected $settings;

    /**
     * @var array
     */
    protected $forbidden_emails;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->settings = $container->get('settings');

        $this->view->offsetSet('appName', $this->appName);
        $username = $_SESSION['username'] ?: 'Admin';
        $this->view->offsetSet('username', $username);
        $this->view->offsetSet('back', @$_GET['back']);
        $this->view->offsetSet('backGet', base64_decode(@$_GET['back_get']));
        $this->view->offsetSet('getEncoded', base64_encode(http_build_query($_GET)));

        $statement = $this->db->prepare("SELECT email FROM email_bounced");
        $statement->execute();
        $this->forbidden_emails = $statement->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Moves the uploaded file to the upload directory and assigns it a unique name
     * to avoid overwriting an existing uploaded file.
     *
     * @param string $directory directory to which the file is moved
     * @param UploadedFile $uploaded file uploaded file to move
     * @return string filename of moved file
     */
    public function moveUploadedFile($directory, UploadedFile $uploadedFile)
    {
        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        $basename = bin2hex(random_bytes(8)); // see http://php.net/manual/en/function.random-bytes.php
        $filename = sprintf('%s.%0.8s', $basename, $extension);

        $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

        return $filename;
    }

    public function generateEmailTableFromData(array $data)
    {

        $thStyle = "style='border: 1px solid #ddd;padding: 8px;padding-top: 12px;padding-bottom: 12px;text-align: left;background-color: #4CAF50;color: white;'";
        $tdStyle = "style='border: 1px solid #ddd;padding: 8px;max-width: 100px;'";

        $table = "
<table style='font-family: \"Trebuchet MS\", Arial, Helvetica, sans-serif;border-collapse: collapse;width: 100%;'>
    <thead>
    <tr>";
        foreach (array_keys($data[0]) as $key) {
            // $key as table header
            $key = str_replace("_", " ", $key);
            $table .= "<th $thStyle>$key</th>";
        }
        $table .= "</tr></thead><tbody>";

        foreach ($data as $row) {
            $table .= "<tr>";
            foreach ($row as $item) {
                $table .= "<td $tdStyle>$item</td>";
            }
            $table .= "</tr>";
        }
        $table .= "</tbody></table>";
        return $table;
    }

    /**
     * @param $to
     * @param $subject
     * @param $message
     * @return bool|string
     */
    public function send_mail($to, $subject, $message)
    {
        if (in_array($to, $this->forbidden_emails)) return false; // Do not send email to email_bounced

        $headers = "From: Support<support@example.com> \n";
        $headers .= "Content-type:text/html;charset=UTF-8";
        $result = mail($to, $subject, $message, $headers);
        if ($result) {
            $statement = $this->db->prepare("INSERT INTO email_history (to_email, email_subject, email_message) VALUES (?, ?, ?)");
            $statement->execute([$to, $subject, $message]);
            return $this->db->lastInsertId();
        } else {
            return $result;
        }
    }

    /**
     * @param $phone //Mobile Phone number
     * @param $sms //SMS body
     * @return bool Whether sms sent or not
     */
    public function send_sms($phone, $sms)
    {
        // Number formatting for Bangladesh mobile
        if (strpos($phone, '01') === 0 && strlen($phone) === 11) {
            $phone = '88' . $phone;
        } elseif (strpos($phone, '1') === 0 && strlen($phone) === 10) {
            $phone = '880' . $phone;
        } elseif (strpos($phone, '+880') === 0) {
            $phone = str_replace('+880', '880', $phone);
        }

        // create a new cURL resource
        $ch = curl_init();
        // set URL and other appropriate options
        curl_setopt( $ch, CURLOPT_URL, "http://api.a7zbd.com/api/sendsms/plain?user=${this->settings['sms']['user']}&password=${this->settings['sms']['password']}&sender=TMS&SMSText=" . urlencode( $sms ) . "&GSM=" . $phone . "" );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        $result = curl_exec( $ch );
        if ($result <= 0) {
            return false;
        } else {
            $statement = $this->db->prepare("INSERT INTO sms_history (to_number, sms_message) VALUES (?, ?)");
            $statement->execute([$phone, $sms]);
            return $this->db->lastInsertId();
        }

    }

    public function __get($name)
    {
        if (@$this->container->{$name}) {
            return $this->container->{$name};
        }
        return $this->{$name};
    }
}