<?php

declare(strict_types=1);

namespace App\Message;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class GetPostMessage.
 *
 * @Route("/posts/{id}", methods={"GET"}, name="get_post")
 */
class GetPostMessage
{
    /**
     * @var string|null
     *
     * @Assert\NotBlank()
     */
    private $id;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): self
    {
        $this->id = $id;

        return $this;
    }
}
