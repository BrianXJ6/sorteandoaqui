<?php

namespace App\Exceptions\Custom;

use Illuminate\Http\Client\{
    Response,
    HttpClientException,
};

class RequestException extends HttpClientException
{
    /**
     * The return message.
     *
     * @var string
     */
    public $message;

    /**
     * The response instance.
     *
     * @var \Illuminate\Http\Client\Response
     */
    public $response;

    /**
     * Create a new Request Exception instance.
     *
     * @param \Illuminate\Http\Client\Response $response
     * @param null|string $message
     */
    public function __construct(Response $response, ?string $message = '')
    {
        $this->message = $message;
        $this->response = $response;
        parent::__construct($this->prepareMessage($response, $message), $response->status());
    }

    /**
     * Prepare the exception message.
     *
     * @param \Illuminate\Http\Client\Response $response
     * @param string $message
     *
     * @return string
     */
    protected function prepareMessage(Response $response, string $message): string
    {
        if (!empty($message)) return $message;

        $host = $response->transferStats->getRequest()->getUri()->getHost();

        return "A Solicitação HTTP externa para \"$host\" retornou código de status: {$response->status()}";
    }
}
