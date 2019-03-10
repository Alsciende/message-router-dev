<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */

declare(strict_types=1);

namespace App\Message;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class CreatePostMessage
 * @package App\Message
 *
 * @Route(path="/posts",methods={"POST"})
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

    public function __invoke()
    {
        // TODO: Implement __invoke() method.
    }

}
