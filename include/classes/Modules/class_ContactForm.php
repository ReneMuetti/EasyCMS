<?php
class ContactForm
{
    private $moduleName = 'contact_form';
    private $moduleId   = 10001;

    private $active = true;

    private $debugMode = false;
    private $bccToDev = true;

    private $registry;
    private $renderer;

    public function __construct()
    {
        global $website, $renderer;

        $this -> registry = $website;
        $this -> renderer = $renderer;
    }

    public function __destruct()
    {
        unset($this -> registry);
        unset($this -> renderer);
    }

    public function setActiveState($state)
    {
        if ( $state == true ) {
            $this -> active = true;
        }
        else {
            $this -> active = false;
        }
    }

    public function getModuleName()
    {
        return $this -> moduleName;
    }

    public function getModuleId()
    {
        return $this -> moduleId;
    }

    public function getFrontendBlock()
    {
        if ( !$this -> active ) {
            $block = new UnderConstruction();
            return $block -> getBlockContent();
        }
        else {
            $this -> renderer -> loadTemplate('frontend' . DS . 'module' . DS . 'contact_form.htm');
            return $this -> renderer -> renderTemplate();
        }
    }

    public function sendMessage($name, $phone, $email, $message, $sendCC = false)
    {
        $result = array(
                      'error'   => false,
                      'message' => null,
                      'data'    => null
                  );

        include_once realpath("./include/classes/PHPMailer/Exception.php");
        include_once realpath("./include/classes/PHPMailer/PHPMailer.php");
        include_once realpath("./include/classes/PHPMailer/SMTP.php");

        $mail = new PHPMailer\PHPMailer\PHPMailer(true);

        try {
            if ( $this -> debugMode == true ) {
                $mail -> SMTPDebug = 2;
            }

            // Connection-Settings
            $mail -> isSMTP();
            $mail -> Host     = $this -> registry -> config['Mail']['host'];
            $mail -> SMTPAuth = $this -> registry -> config['Mail']['smtpauth'];
            $mail -> Username = $this -> registry -> config['Mail']['username'];
            $mail -> Password = $this -> registry -> config['Mail']['password'];
            $mail -> Port     = $this -> registry -> config['Mail']['port'];
            $mail -> CharSet  = $this -> registry -> config['Mail']['charset'];

            if ( $this -> registry -> config['Mail']['secure'] ) {
                $mail -> SMTPSecure = $this -> registry -> config['Mail']['protocol'];
            }

            // Recipients
            if ( $this -> debugMode == true ) {
                $mail -> addBCC( $this -> registry -> config['Mail']['dev_mail'] );
            }
            else {
                $mail -> addAddress($this -> registry -> config['Mail']['rec_mail'], $this -> registry -> config['Mail']['rec_name']);
            }

            // CC Recipient
            if ( $sendCC == true ) {
                $mail -> addCC($email);
            }

            // BCC Recipient
            if ( $this -> bccToDev == true ) {
                $mail -> addBCC( $this -> registry -> config['Mail']['dev_mail'] );
            }

            // set as HTML-Mail
            $mail -> isHTML(true);

            // Sender
            $mail -> setFrom($this -> registry -> config['Mail']['address'], $this -> registry -> config['Mail']['sender']);

            // Subject
            $mail -> Subject = $this -> registry -> config['Mail']['subject'];

            // Mail-Body
            $mail -> Body = $this -> _renderMailBody($name, $email, $phone, $message);

            //send mail
            $mail -> send();

            $result['message'] = $this -> registry -> user_lang['mail']['send_mail_success'];
        }
        catch(Exception $e) {
            $logMessage = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}\n\n" .
                          '|' . $name . '|' . $phone . '|' . $email . '|' . $message . '|';
            new Logging('php_mailer', $logMessage);

            $result['error']   = true;
            $result['message'] = $this -> registry -> user_lang['mail']['error_mail_not_send'];
        }

        return $result;
    }


    private function _renderMailBody($name, $mail, $phone, $message)
    {
        $this -> renderer -> loadTemplate('frontend' . DS . 'module' . DS . 'email_template.htm');
            $this -> renderer -> setVariable('contact_name'   , $name);
            $this -> renderer -> setVariable('contact_mail'   , $mail);
            $this -> renderer -> setVariable('contact_phone'  , $phone);
            $this -> renderer -> setVariable('contact_message', nl2br($message) );
        return $this -> renderer -> renderTemplate();
    }
}