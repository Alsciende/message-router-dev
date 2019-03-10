<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ApiController
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var MessageBusInterface
     */
    private $bus;

    /**
     * ApiController constructor.
     *
     * @param SerializerInterface $serializer
     * @param ValidatorInterface  $validator
     * @param MessageBusInterface $bus
     */
    public function __construct(SerializerInterface $serializer, ValidatorInterface $validator, MessageBusInterface $bus
    ) {
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->bus = $bus;
    }

    /**
     * @param Request $request
     * @param string  $type
     *
     * @return Response
     */
    public function __invoke(Request $request, string $type): Response
    {
        $content = (string) $request->getContent();

        $message = $this->serializer->deserialize($content, $type, 'json');

        $violations = $this->validator->validate($message);

        if (count($violations) > 0) {
            $response = ['success' => false, 'error' => $violations];
        } else {
            $envelope = $this->bus->dispatch($message);
            $stamp = $envelope->last(HandledStamp::class);
            if ($stamp instanceof HandledStamp) {
                $response = $stamp->getResult();
            } else {
                $response = ['success' => false, 'error' => 'no_message_handler'];
            }
        }

        return JsonResponse::fromJsonString($this->serializer->serialize($response, 'json'));
    }
}
