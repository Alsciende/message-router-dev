<?php

namespace App\Factory;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;

/**
 * Class JsonBodyDecoder is responsible for decoding the JSON body of a POST|PUT|PATCH request
 * The body MUST be an encoded JSON object.
 */
class JsonBodyDecoder
{
    /**
     * @var DecoderInterface
     */
    private $jsonEncoder;

    public function __construct(DecoderInterface $jsonEncoder)
    {
        $this->jsonEncoder = $jsonEncoder;
    }

    public function decodeBody(Request $request): array
    {
        $content = (string) $request->getContent();

        try {
            $data = empty($content) ? [] : $this->jsonEncoder->decode($content, 'json');
        } catch (NotEncodableValueException $exception) {
            throw new InvalidRequestException($exception->getMessage());
        }

        if (!is_array($data)) {
            throw new InvalidRequestException('Request body top-level must be an object');
        }

        if (!$this->isAssociativeArray($data)) {
            throw new InvalidRequestException('Request body top-level must be an object');
        }

        return $data;
    }

    private function isAssociativeArray(array $array)
    {
        if (0 === count($array)) {
            return true;
        }

        $keys = array_keys($array);

        return $keys !== array_keys($keys);
    }
}
