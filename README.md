# unity_web_request

install:

```composer require cmrweb/unity_web_request```


usage :

```
namespace App\Service;

use Cmrweb\UnityWebRequest\UnityRequest;
use Symfony\Component\HttpFoundation\JsonResponse; 

class UnityRequestHandlerService extends UnityRequest
{ 
    public function handleSuccess(object $data): JsonResponse
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

    public function handleError(string $message): JsonResponse
    {
        return new JsonResponse(['Error' => $message]);
    }
}

```

.env file

```
###> cmrweb/unity_web_request ###
UNITY_REQUEST_TOKEN='mySuperSecretToken'
###< cmrweb/unity_web_request ###

```

config/services.yaml

```
parameters:
    unity_request_token: '%env(UNITY_REQUEST_TOKEN)%'
```