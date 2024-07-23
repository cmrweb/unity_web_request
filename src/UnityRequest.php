<?php
namespace Cmrweb\UnityWebRequest;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

abstract class UnityWebRequest implements UnityWebRequestInterface
{
    public function __construct(
        private readonly ParameterBagInterface $param
    ){}

    public function handleRequest(Request $request): JsonResponse|\Exception
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
}