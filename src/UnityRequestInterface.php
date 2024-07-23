<?php
namespace Cmrweb\UnityWebRequest;

use Symfony\Component\HttpFoundation\JsonResponse;

interface UnityRequestInterface
{    
    public function handleSuccess(object $data): JsonResponse;
    public function handleError(string $message): JsonResponse;
}