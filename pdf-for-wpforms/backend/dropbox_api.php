<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
class Yeepdf_Dropbox_API {
    public static function get_token( $clientId, $clientSecret, $authorizationCode ) {
        $response = wp_remote_post(
            'https://api.dropbox.com/oauth2/token',
            array(
                'timeout' => 20,
                'headers' => array(
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ),
                'body'    => array(
                    'code'          => $authorizationCode,
                    'grant_type'    => 'authorization_code',
                    'client_id'     => $clientId,
                    'client_secret' => $clientSecret,
                ),
            )
        );
        if ( is_wp_error( $response ) ) {
            return $response->get_error_message();
        }
        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );
        if ( isset( $data['access_token'] ) ) {
            update_option( '_yeepdf_dropbox_api_token', $data );
            if ( isset( $data['refresh_token'] ) ) {
                update_option( '_yeepdf_dropbox_api_token_refresh_token', $data['refresh_token'] );
            }
            return 'ok';
        }
        return isset( $data['error_description'] ) ? $data['error_description'] : 'error';
    }
    public static function uppload_files( $fileTmpPath ) {
        if ( ! file_exists( $fileTmpPath ) ) {
            return;
        }
        $data_dropbox  = get_option( '_yeepdf_dropbox_api_token' );
        $refresh_token = get_option( '_yeepdf_dropbox_api_token_refresh_token' );
        if ( ! isset( $data_dropbox['access_token'] ) ) {
            return;
        }
        $clientId     = get_option( 'pdf_creator_dropbox_token' );
        $clientSecret = get_option( 'pdf_creator_dropbox_token_secret' );
        $accessToken  = self::checkAccessToken(
            $data_dropbox['access_token'],
            $refresh_token,
            $clientId,
            $clientSecret
        );
        $filename    = basename( $fileTmpPath );
        $dropboxPath = '/' . $filename;
        $fileSize    = filesize( $fileTmpPath );
        $response = wp_remote_post(
            'https://content.dropboxapi.com/2/files/upload',
            array(
                'timeout' => 60,
                'headers' => array(
                    'Authorization'    => 'Bearer ' . $accessToken,
                    'Content-Type'     => 'application/octet-stream',
                    'Dropbox-API-Arg'  => wp_json_encode(
                        array(
                            'path'       => $dropboxPath,
                            'mode'       => 'add',
                            'autorename' => true,
                            'mute'       => false,
                        )
                    ),
                ),
                'body'    => file_get_contents( $fileTmpPath ),
            )
        );
        return $response;
    }
    public static function checkAccessToken( $access_token, $refresh_token, $clientId, $clientSecret ) {
        $response = wp_remote_post(
            'https://api.dropboxapi.com/2/users/get_current_account',
            array(
                'timeout' => 20,
                'headers' => array(
                    'Authorization' => 'Bearer ' . $access_token,
                ),
            )
        );
        if ( is_wp_error( $response ) ) {
            return $access_token;
        }
        $body   = wp_remote_retrieve_body( $response );
        $result = json_decode( $body, true );
        if ( ! isset( $result['account_id'] ) ) {
            return self::getNewAccessToken(
                $refresh_token,
                $clientId,
                $clientSecret,
                $access_token
            );
        }
        return $access_token;
    }
    public static function getNewAccessToken( $refresh_token, $clientId, $clientSecret, $access_token ) {
        $response = wp_remote_post(
            'https://api.dropbox.com/oauth2/token',
            array(
                'timeout' => 20,
                'headers' => array(
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ),
                'body'    => array(
                    'refresh_token' => $refresh_token,
                    'grant_type'    => 'refresh_token',
                    'client_id'     => $clientId,
                    'client_secret' => $clientSecret,
                ),
            )
        );
        if ( is_wp_error( $response ) ) {
            return $access_token;
        }
        $body   = wp_remote_retrieve_body( $response );
        $result = json_decode( $body, true );
        if ( isset( $result['access_token'] ) ) {
            update_option( '_yeepdf_dropbox_api_token', $result );
            return $result['access_token'];
        }
        return $access_token;
    }
}