<?php

declare(strict_types=1);

namespace App\Controller\NoMail;

use App\Repository\HandlerMail;
use App\DTO\mailDTO;
use App\Core\Validator;

class MailDriver extends HandlerMail
{
    private Validator $validator;
    public function __construct()
    {
        parent::__construct();
        $this->validator = new Validator();
    }

    public function send_mail(string $name, string $email, string $subject, string $message)
    {
        $data = new mailDTO(
            name: $name,
            email: $email,
            subject: $subject,
            message: $message,
        );

        $this->validator->validate($data);

        $response = (array) $this->handleIncomingMail($name, $email, $subject, $message);
        if (!empty($response)) {
            flash_set("success", "mail sended and saved in our inbox!");
            return $response;
        }

        flash_set("failed", "mail not sended, maybe we have problem in our internal system.");
        return $response;
    }

}
