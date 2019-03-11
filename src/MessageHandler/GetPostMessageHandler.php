<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\GetPostMessage;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class GetPostMessageHandler implements MessageHandlerInterface
{
    public function __invoke(GetPostMessage $message)
    {
        return [
            'success' => true,
            'data' => [
                'id' => $message->getId(),
                'title' => 'Hello World',
            ],
        ];
    }
}
