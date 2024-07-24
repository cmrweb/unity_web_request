# unity_web_request

install:

```composer require cmrweb/unity_web_request```


usage :

service
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

controller

```
    #[Route('/unity/request', name: 'app_unity_request')]
    public function index(Request $request, UnityRequestHandlerService $unityRequestHandler): Response
    { 
        return $unityRequestHandler->handleRequest($request);
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


UNITY C#

web request
```
using System;
using System.Collections;
using UnityEngine;
using UnityEngine.Networking;
 
public class WebRequest : MonoBehaviour
{
    private static Uri uri = new Uri("http://127.0.0.1:8000/unity/request");
    private static string token = "mySuperSecretToken";

    public static IEnumerator Post(WWWForm form)
    {
        form.AddField("token", token);

        using (UnityWebRequest www = UnityWebRequest.Post(uri, form))
        {
            yield return www.SendWebRequest();

            if (www.result == UnityWebRequest.Result.Success) {
                HandleResponse(www.downloadHandler.text); 
            } else { 
                RequestManager.FlashError("invalid request");
            }
        }
    }

    private static void HandleResponse(string response) { 
        Request request = Request.Create(response); 

        if (String.IsNullOrEmpty(request.Error))
        { 
            RequestManager.FlashSuccess(request.ToJson()); 
        } else {
            RequestManager.FlashError(request.Error); 
        }
    }

}
 ```

serializer

```
using System; 
using UnityEngine;

[Serializable]
public class Request 
{
    public string Error;
    public DataStruct Data;
    [Serializable]
    public class DataStruct
    {
        public string field;
        public string responseField;
    }
    public static Request Create(string jsonString)
    {
        return JsonUtility.FromJson<Request>(jsonString);
    }
    public string ToJson()
    {
        Debug.Log(Data.field);
        return JsonUtility.ToJson(Data);
    }
}

```


front

```

using UnityEngine;
using UnityEngine.UIElements;

public class RequestManager : MonoBehaviour
{
    private UIDocument document; 
    private Button requestBtn;
    public static VisualElement flashPanel;
    public static Label flashMessage;

    void Start()
    {
        document = GetComponent<UIDocument>(); 
        requestBtn = document.rootVisualElement.Q<Button>("requestBtn");
        requestBtn.RegisterCallback<ClickEvent>(Submit);
        flashPanel = document.rootVisualElement.Q<VisualElement>("flashPanel");
        flashMessage = flashPanel.Q<Label>("flashMessage");
    }
    private void Submit(ClickEvent evt)
    { 
        WWWForm form = new WWWForm();

        TextField textField = document.rootVisualElement.Q<TextField>("inputKey");
        form.AddField("field", textField.text);

        StartCoroutine(WebRequest.Post(form));
    } 

    public static void FlashSuccess(string message)
    {
        flashPanel.style.display = DisplayStyle.Flex;
        flashPanel.style.backgroundColor = new Color(0,100,0, 80);
        flashMessage.text = message;
    }
    
    public static void FlashError(string message)
    {
        flashPanel.style.display = DisplayStyle.Flex;
        flashPanel.style.backgroundColor = new Color(100, 0, 0, 80); 
        flashMessage.text = message;
    }
}

```
