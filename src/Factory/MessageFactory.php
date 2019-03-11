<?php

declare(strict_types=1);

namespace App\Factory;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class MessageFactory
{
    /**
     * @var JsonBodyDecoder
     */
    private $bodyDecoder;

    /**
     * @var DenormalizerInterface
     */
    private $denormalizer;

    public function __construct(JsonBodyDecoder $bodyDecoder, DenormalizerInterface $denormalizer)
    {
        $this->bodyDecoder = $bodyDecoder;
        $this->denormalizer = $denormalizer;
    }

    /**
     * @param Request $request The Request to transform into a Message
     */
    public function createFromRequest(Request $request)
    {
        $body = $this->bodyDecoder->decodeBody($request);
        $routeParams = $request->attributes->get('_route_params');

        return $this->denormalizer->denormalize(
            array_merge($body, $routeParams),
            $routeParams['_message']
        );
    }
}
