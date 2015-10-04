<?php
namespace LoopAnime\AppBundle\Response;

class GenericResponse implements ResponseInterface
{

    protected $errors;
    protected $payload;

    /**
     * @return boolean
     */
    public function isError()
    {
        if (count($this->errors) > 0) {
            return true;
        }
        return false;
    }

    /**
     * @param bool $asJson
     * @return array
     */
    public function send($asJson = true)
    {
        $response = [
            'isError' => $this->isError(),
            'payload' => $this->getPayLoad(),
        ];
        if ($asJson) {
            $response = json_encode($response);
        }
        return $response;
    }

    public function setError($errorMessage, $errorCode)
    {
        $this->errors[] = ['error' => $errorMessage, 'code' => $errorCode];
    }

    public function setPayloadData($payloadData)
    {
        $this->payload = $payloadData;
    }

    public function getPayload()
    {
        return $this->payload;
    }
}
