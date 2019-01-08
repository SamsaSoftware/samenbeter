<?php
namespace Application\Service;

define("ANDROID_DRIVER_PUSH_KEY", "AIzaSyDWF1UeuBmTun-bhaYW-szT8VGcMkYeyyo");
define("ANDROID_PASSENGER_PUSH_KEY", "AIzaSyD1Hh7L-UbWCfFnmztowIZyKhHNq7MkGnQ");
// app drive : AIzaSyCMh0QGbuIdj4x6CoWVxqaKdwz3bRqAbjk
// https://console.cloud.google.com/apis/credentials?project=ordertapp-driver-146814
class PushService extends Service
{

    /*
     * Method name: _sendPush
     * Desc: Divides the tokens according to device type and sends a push accordingly
     * Input: Request data, entity_id
     * Output: 1 - success, 0 - failure
     */
    protected function _sendPush($senderId, $aplTokenArr, $message, $datetime, $user_type, $aplContent, $andrContent, $device_type)
    {
        $entity_string = '';
        $aplTokenArr = array();
        $andiTokenArr = array();
        $return_arr = array();
        
        if ($device_type == "ANDROID")
            $aplResponse = $this->_sendApplePush($aplTokenArr, $aplContent, $user_type);
        
        if ($device_type == "IOS")
            $andiResponse = $this->_sendAndroidPush($aplTokenArr, $andrContent, $user_type);
        
        /*
         * foreach ($recEntityArr as $entity) {
         *
         * $ins_arr = array('notif_type' => (int) $notifType, 'sender' => (int) $senderId, 'reciever' => (int) $entity, 'message' => $message, 'notif_dt' => $datetime, 'apl' => $aplTokenArr, 'andr' => $andiTokenArr); //'aplTokens' => $aplTokenArr, 'andiTokens' => $andiTokenArr, 'andiRes' => $andiResponse,
         *
         * $notifications->insert($ins_arr);
         *
         * $newDocID = $ins_arr['_id'];
         *
         * $return_arr[] = array($entity => $newDocID);
         * }
         */
        
        /*
         * if ($aplResponse['errorNo'] != '')
         * $errNum = $aplResponse['errorNo'];
         * else if ($andiResponse['errorNo'] != '')
         * $errNum = $andiResponse['errorNo'];
         * else
         * $errNum = 46;
         *
         * return array('insEnt' => $return_arr, 'errNum' => $errNum, 'andiRes' => $andiResponse);
         * } else {
         * return array('insEnt' => $return_arr, 'errNum' => 45, 'andiRes' => $andiResponse); //means push not sent
         * }
         */
    }

    protected function _sendApplePush($tokenArr, $aplContent, $user_type)
    {
        $ctx = stream_context_create();
        stream_context_set_option($ctx, 'ssl', 'local_cert', $this->ios_cert_path);
        stream_context_set_option($ctx, 'ssl', 'passphrase', $this->ios_cert_pwd);
        $apns_fp = stream_socket_client($this->ios_cert_server, $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);
        
        if ($apns_fp) {
            
            $body['aps'] = $aplContent;
            
            $payload = json_encode($body);
            
            $msg = '';
            foreach ($tokenArr as $token) {
                $msg .= chr(0) . pack('n', 32) . pack('H*', $token) . pack('n', strlen($payload)) . $payload;
            }
            
            $result = fwrite($apns_fp, $msg, strlen($msg));
            
            if (! $result)
                return array(
                    'errorNo' => 46
                );
            else
                return array(
                    'errorNo' => 44
                );
        } else {
            return array(
                'errorNo' => 30,
                'error' => $errstr
            );
        }
    }

    protected function _sendAndroidPush($tokens, $andrContent, $user_type)
    {
        $chunks = array_chunk($tokens, 999);
        
        $sent = 0;
        
        if ($user_type == '1')
            $apiKey = ANDROID_DRIVER_PUSH_KEY;
        else
            $apiKey = ANDROID_PASSENGER_PUSH_KEY;
        
        $headers = array(
            'Authorization: key=' . $apiKey,
            'Content-Type: application/json'
        );
        
        foreach ($chunks as $tokenArr) {
            
            $fields = array(
                'registration_ids' => $tokenArr,
                'data' => $andrContent
            );
            
            // Open connection
            $ch = curl_init();
            
            // Set the url, number of POST vars, POST data
            curl_setopt($ch, CURLOPT_URL, $this->androidUrl);
            
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
            // Disabling SSL Certificate support temporarly
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
            
            // Execute post
            $result = curl_exec($ch);
            
            curl_close($ch);
            // echo 'Result from google:' . $result . '---';
            $res_dec = json_decode($result);
            
            if ($res_dec->success >= 1)
                $sent ++;
        }
        if ($sent > 0)
            return array(
                'errorNo' => 44,
                'result' => $tokens,
                'key' => $apiKey
            );
        else
            return array(
                'errorNo' => 46,
                'result' => $tokens
            );
    }
}

?>