<?php
namespace App\DTO;
use Symfony\Component\Validator\Constraints as Assert;

class mailDTO
{
    #[Assert\NotBlank(message: "name tidak boleh kosong")]
    #[Assert\Length(
        min: 3,
        max: 20,
        minMessage: "name minimal 3 karakter",
        maxMessage: "name maksimal 20 karakter"
    )]
    public string $name;

    #[Assert\NotBlank(message: "email tidak boleh kosong")]
    #[Assert\Email(message: "Format email tidak valid")]
    public string $email;

    #[Assert\NotBlank(message: "subject tidak boleh kosong")]
    #[Assert\Length(
        min: 3,
        max: 100,
        minMessage: "subject minimal 3 karakter",
        maxMessage: "subject maksimal 100 karakter"
    )]
    public string $subject;

    #[Assert\NotBlank(message: "message tidak boleh kosong")]
    #[Assert\Length(
        min: 3,
        max: null,
        minMessage: "message minimal 3 karakter",
        maxMessage: "message tidak memiliki batas maksimal"
    )]
    public string $message;

    public function __construct(
        string $name,
        string $email,
        string $subject,
        string $message
    ) {
        $this->$name = $name;
        $this->$email = $email;
        $this->$subject = $subject;
        $this->$message = $message;

    }
}
?>