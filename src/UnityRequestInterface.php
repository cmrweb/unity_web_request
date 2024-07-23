<?php
namespace Cmrweb\UnityWebRequest;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

interface UnityRequestInterface
{    
    public function handleRequest(Request $request): JsonResponse|\Exception;
    public function handleSuccess(object $data): JsonResponse;
    public function handleError(string $message): JsonResponse;
}