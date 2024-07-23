<?php
namespace Cmrweb\UnityWebRequest;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class RequestHelper
{
    public function __construct(
        private readonly ParameterBagInterface $param
    ){}

    public function handleRequest(Request $request)
    { 
        if (!$request->isMethod('POST')) {
            throw new \Exception('invalid request');
        }
        $data = (object) $request->request->all();
        if($this->param->get('unity_request_token') !== $data?->token) { 
            return $this->handleError('invalid token');
        } 
        unset($data->token);
        return $this->handleSuccess($data);
    }

    private function handleSuccess(object $data): JsonResponse
    {
        ###> do something ###
        if(!isset($data->field)) {
            return $this->handleError('invalid field is missing');
        }
        if(empty($data->field)) {
            return $this->handleError('field cannot be empty'); 
        }
        $data->responseField = 'data added from server';
        ###< do something ### 
        return new JsonResponse(['Data'=>$data]);
    }

    private function handleError(string $message): JsonResponse
    {
        return new JsonResponse(['Error' => $message]);
    }
}