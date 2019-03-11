<?php

namespace App\Factory;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class JsonBodyDecoderTest extends TestCase
{
    /**
     * @var JsonBodyDecoder
     */
    private $underTest;

    protected function setUp()
    {
        parent::setUp();
        $this->underTest = new JsonBodyDecoder(
            new Serializer([new ObjectNormalizer()], [new JsonEncoder()])
        );
    }

    public function testDecodeBodyEmpty()
    {
        $request = $this->createMock(Request::class);
        $request->method('getContent')->willReturn('');

        $this->expectException(InvalidRequestException::class);
        $this->underTest->decodeBody($request);
    }

    public function testDecodeBodySyntax()
    {
        $request = $this->createMock(Request::class);
        $request->method('getContent')->willReturn('fu');

        $this->expectException(InvalidRequestException::class);
        $this->underTest->decodeBody($request);
    }

    public function testDecodeBodyScalar()
    {
        $request = $this->createMock(Request::class);
        $request->method('getContent')->willReturn('42');

        $this->expectException(InvalidRequestException::class);
        $this->underTest->decodeBody($request);
    }

    public function testDecodeBodyArray()
    {
        $request = $this->createMock(Request::class);
        $request->method('getContent')->willReturn('["fu","bar"]');

        $this->expectException(InvalidRequestException::class);
        $this->underTest->decodeBody($request);
    }

    public function testDecodeBodyObject()
    {
        $request = $this->createMock(Request::class);
        $request->method('getContent')->willReturn('{"fu": "bar"}');

        $this->assertEquals(['fu' => 'bar'], $this->underTest->decodeBody($request));
    }
}
