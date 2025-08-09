<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
class Yeepdf_Dropbox_API {
    public static function get_token($clientId,$clientSecret,$authorizationCode){
        $url = "https://api.dropbox.com/oauth2/token";
        //$authorizationCode = "BJ8qO0zpOjAAAAAAAAAyYfC1TjEznVFRrWsE3DSARjI";
        $data = [
            "code" => $authorizationCode,
            "grant_type" => "authorization_code",
            "client_id" => $clientId,
            "client_secret" => $clientSecret
        ];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $response = curl_exec($ch);
        curl_close($ch);
        $response = json_decode($response, true);
        if (isset($response["access_token"])) {
            update_option( "_yeepdf_dropbox_api_token", $response);
            update_option( "_yeepdf_dropbox_api_token_refresh_token", $response["refresh_token"]);
            return "ok";
        }else{
            if(isset($response["error_description"])){
                return $response["error_description"];
            }else{
                return "error";
            }
        }
    }
    public static function uppload_files($fileTmpPath) {
        $data_dropbox = get_option("_yeepdf_dropbox_api_token");
        $refresh_token = get_option("_yeepdf_dropbox_api_token_refresh_token");
        if(isset($data_dropbox["access_token"])) {
            $clientId = get_option("pdf_creator_dropbox_token");
            $clientSecret = get_option("pdf_creator_dropbox_token_secret");
            $accessToken = $data_dropbox["access_token"];
            $accessToken_ok = self::checkAccessToken($accessToken,$refresh_token,$clientId,$clientSecret);
            $filename = basename($fileTmpPath);
            $dropboxPath = '/' . $filename;
            $file = fopen($fileTmpPath, 'rb');
            $fileSize = filesize($fileTmpPath);
            $ch = curl_init('https://content.dropboxapi.com/2/files/upload');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $accessToken_ok,
                'Content-Type: application/octet-stream',
                'Dropbox-API-Arg: ' . json_encode([
                    "path" => $dropboxPath,
                    "mode" => "add",
                    "autorename" => true,
                    "mute" => false
                ])
            ]);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, fread($file, $fileSize));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);
            fclose($file);
        }
    }
    public static function checkAccessToken($access_token,$refresh_token,$clientId,$clientSecret) {
       $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://api.dropboxapi.com/2/users/get_current_account',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer '.$access_token
          ),
        ));
        $response = curl_exec($curl);
        $result = json_decode($response, true);
        if(!isset($result["account_id"])) {
            return self::getNewAccessToken($refresh_token, $clientId, $clientSecret,$access_token);
        }else{
            return $access_token;
        }
    }
    public static function getNewAccessToken($refresh_token, $clientId, $clientSecret,$access_token) {
        $url = "https://api.dropbox.com/oauth2/token";
        $data = [
            "refresh_token" => $refresh_token,
            "grant_type" => "refresh_token",
            "client_id" => $clientId,
            "client_secret" => $clientSecret
        ];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/x-www-form-urlencoded"]);
        $response = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($response, true);
        if (isset($result['access_token'])) {
            update_option( "_yeepdf_dropbox_api_token", $result);
            return $result['access_token'];
        }
        return $access_token;
    }
}