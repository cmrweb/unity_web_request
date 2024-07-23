<?php
namespace Cmrweb\UnityWebRequest;

use Symfony\Component\HttpFoundation\JsonResponse;

interface UnityWebRequestInterface
{    
    public function handleSuccess(object $data): JsonResponse;
    public function handleError(string $message): JsonResponse;
}