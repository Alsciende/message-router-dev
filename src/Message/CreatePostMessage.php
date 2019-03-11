<?php

declare(strict_types=1);

namespace App\Message;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class CreatePostMessage.
 *
 * @Route(path="/posts",methods={"POST"},name="create_post")
 */
class CreatePostMessage
{
    /**
     * @var string|null
     *
     * @Assert\NotBlank()
     */
    public $title;

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string|null $title
     */
    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }
}
