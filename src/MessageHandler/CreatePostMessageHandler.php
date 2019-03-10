<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\CreatePostMessage;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class CreatePostMessageHandler implements MessageHandlerInterface
{
    public function __invoke(CreatePostMessage $message)
    {
//        dump($message);

        return [
            'success' => true,
            'title' => $message->getTitle(),
            'id' => Uuid::uuid4(),
        ];
    }
}
