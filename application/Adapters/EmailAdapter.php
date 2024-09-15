<?php

namespace Agencia\Close\Adapters;

use Agencia\Close\Helpers\Result;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailAdapter
{
    private PHPMailer $mail;
    private Result $result;
    const Host = MAIL_HOST;
    const Email = MAIL_EMAIL;
    const User = MAIL_USER;
    const Password = MAIL_PASSWORD;
    const name_site = NAME;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->result = new Result();
        $this->mail = new PHPMailer(false);
        //Server settings
//        $this->mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
        $this->mail->isSMTP();
        $this->mail->CharSet = 'UTF-8';//Send using SMTP
        $this->mail->Host = self::Host;                     //Set the SMTP server to send through
        $this->mail->SMTPAuth = true;                                   //Enable SMTP authentication
        $this->mail->Username = self::User;                     //SMTP username
        $this->mail->Password = self::Password;                               //SMTP password
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         //Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
        $this->mail->Port = 587;
        $this->mail->setFrom(self::Email, name_site);
        $this->mail->isHTML(true);
    }

    public function addAddress(string $email)
    {
        //$this->mail->addAddress('joe@example.net', 'Joe User');     //Add a recipient
        $this->mail->addAddress($email); //Name is optional
    }

    public function setSubject($subject)
    {
        $this->mail->Subject = $subject;
    }

    public function setBody(string $file, array $data = [])
    {
        $template = new TemplateAdapter();
        $mail = $template->render($file, $data);
        $this->mail->Body = $mail;
    }

    public function send($result)
    {
        try {
            $this->mail->send();
            $this->result->setError(false);
            $this->result->setMessage($result);
        } catch (Exception $e) {
            $this->result->setError(true);
            $this->result->setMessage('Falha ao enviar o E-mail!!!');
            $this->result->setInfo([
                'message' => "Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}"
            ]);
        }
    }

    public function getResult(): Result
    {
        return $this->result;
    }

    public function o()
    {
//            $this->mail->addReplyTo('info@example.com', 'Information');
//            $this->mail->addCC('cc@example.com');
//            $this->mail->addBCC('bcc@example.com');

//            //Attachments
//            $this->mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
//            $this->mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name
    }
}