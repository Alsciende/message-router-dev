<?php

declare(strict_types=1);

namespace App\Controller;

use App\Factory\MessageFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\ValidationFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Serializer\SerializerInterface;

class ApiController
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var MessageBusInterface
     */
    private $bus;

    /**
     * @var MessageFactory
     */
    private $factory;

    /**
     * ApiController constructor.
     *
     * @param MessageFactory      $factory
     * @param MessageBusInterface $bus
     * @param SerializerInterface $serializer
     */
    public function __construct(
        MessageFactory $factory,
        MessageBusInterface $bus,
        SerializerInterface $serializer
    ) {
        $this->factory = $factory;
        $this->bus = $bus;
        $this->serializer = $serializer;
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function __invoke(Request $request): Response
    {
        $message = $this->factory->createFromRequest($request);

        try {
            $envelope = $this->bus->dispatch($message);
            $stamp = $envelope->last(HandledStamp::class);
            if ($stamp instanceof HandledStamp) {
                $response = $stamp->getResult();
            } else {
                $response = ['success' => false, 'error' => 'no_message_handler'];
            }
        } catch (ValidationFailedException $e) {
            $response = ['success' => false, 'error' => $e->getViolations()];
        }

        return JsonResponse::fromJsonString($this->serializer->serialize($response, 'json'));
    }
}
