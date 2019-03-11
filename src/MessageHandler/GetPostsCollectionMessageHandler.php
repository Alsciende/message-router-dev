<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\GetPostsCollectionMessage;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class GetPostsCollectionMessageHandler implements MessageHandlerInterface
{
    public function __invoke(GetPostsCollectionMessage $message)
    {
        return [
            'success' => true,
            'data' => [
                [
                    'id' => Uuid::uuid4(),
                    'title' => 'Hello World',
                ],
            ],
        ];
    }
}
