<?php

declare(strict_types=1);

require '../../StreamCMSInit.php';

//$authCode = 'c3868qt44uarwp0oqdydvihrva1356';
//$grantType = 'authorization_code';
//
//$twitchController = new TwitchController();
//$twitchController->setTwitchAuth($authCode, $grantType);
//dump($twitchController->getTwitchUser());

function camelCaseKeys(array $data): array
{
    $newArray = [];
    foreach ($data as $key => &$value) {
        if (is_array($value)) {
            $value = camelCaseKeys($value);
        }
        if (is_string($key)) {
            $key = str_replace('_', ' ', $key);
            $key = ucwords($key);
            $key = str_replace(' ', '', $key);
            $key = strtolower($key[0]) . substr($key, 1);
        }
        $newArray[$key] = $value;
    }
    return $newArray;
}

$data = [
    "data" => [
        [
            'id' => "661415316",
            'login' => "sheriffoftiltover",
            "display_name" => "sheriffoftiltover",
            "type" => "",
            "broadcaster_type" => "",
            "description" => "",
            "profile_image_url" => "https://static-cdn.jtvnw.net/user-default-pictures-uv/998f01ae-def8-11e9-b95c-784f43822e80-profile_image-300x300.png",
            "offline_image_url" => "",
            "view_count" => 3,
            "email" => "",
            "created_at" => "2021-03-13T03:32:45Z",
        ],
    ],
];

dump(camelCaseKeys($data));