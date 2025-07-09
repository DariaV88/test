<?php
include_once('functions.php');

$webhookUrl = 'https://b24-hmuplh.bitrix24.ru/rest/1/здесь был код/';

$amocrmDomain = 'https://dariav3888.amocrm.ru';
$amocrmToken = 'здесь был токен';

// Получаем данные из формы
$name = htmlspecialchars($_POST["name"]);
$phone = htmlspecialchars($_POST["phone"]);
$comment = htmlspecialchars($_POST["comment"]);
$tag = 'сайт';
$dateTime = date('d.m.Y H:i');

// Создаем контакт в Bitrix24
$bitrixContactData = [
    'FIELDS' => [
        'NAME' => $name,
        'PHONE' => [
            [
                'VALUE' => $phone,
                'VALUE_TYPE' => 'WORK',
            ],
        ],
        'SOURCE_ID' => 'WEB',
    ],
];

$bitrixContactResponse = bitrixApiPost('crm.contact.add', $bitrixContactData);

if (isset($bitrixContactResponse['result'])) {
    $bitrixContactId = $bitrixContactResponse['result'];
    echo "Контакт Bitrix24 успешно создан.\n";
} else {
    die("Ошибка при создании контакта Bitrix24: " . print_r($bitrixContactResponse, true));
}

// Создаем сделку в Bitrix24
$bitrixDealData = [
    'FIELDS' => [
        'TITLE' => "Заявка с сайта {$dateTime}",
        'COMMENTS' => $comment,
        'SOURCE_ID' => 'WEB',
        'UF_CRM_1752012119876' => $tag,
    ],
];

$bitrixDealResponse = bitrixApiPost('crm.deal.add', $bitrixDealData);

if (isset($bitrixDealResponse['result'])) {
    $bitrixDealId = $bitrixDealResponse['result'];
    echo "Сделка Bitrix24 успешно создана.\n";
} else {
    die("Ошибка при создании сделки Bitrix24: " . print_r($bitrixDealResponse, true));
}

// Связываем сделку с контактом
$bitrixLinkResponse = bitrixApiPost('crm.deal.contact.add', [
    'ID' => $bitrixDealId,
    'FIELDS' => ['CONTACT_ID' => $bitrixContactId,],
]);

if (isset($bitrixLinkResponse['result'])) {
    echo "Контакт Bitrix24 успешно связан со сделкой.\n";
} else {
    die("Ошибка при связывании Bitrix24: " . print_r($bitrixLinkResponse, true));
}

//////////////////////////////////////////////////////////

// Создаем связанные контакт и сделку в AmoCRM
$dataAmoLead = [
  [
      "name" => "Заявка с сайта {$dateTime}",
      "custom_fields_values" => [
          [
              "field_id" => 5603,
              "values" => [["value" => $comment]],
          ],
          [
              "field_id" => 5599,
              "values" => [["enum_id" => 2841]],
          ],
      ],
      "_embedded" => [
          "contacts" => [
              [
                  "name" => $name,
                  "custom_fields_values" => [
                      [
                          "field_code" => "PHONE",
                          "values" => [["enum_code" => "WORK", "value" =>$phone]],
                      ],
                  ],
              ],
          ],
      ],
  ],
];

    $responseAmo = amoApiPost($dataAmoLead);

if ($responseAmo[0]['id']) {
  echo "Контакт и сделка Amo успешно созданы.\n";
} else {
  echo("Ошибка при создании контакта и сделки Amo". print_r($responseAmoTag));
  die;
}

// Добавляем тег в AmoCRM
function updateAmoLeadTag($leadId, $data) {

    global $amocrmDomain;
    global $amocrmToken;
    $url = "$amocrmDomain/api/v4/leads/$leadId";
    $jsonData = json_encode($data);
    $headers = [
  'Authorization: Bearer '.$amocrmToken,
  'Content-Type: application/json',];

    $response = makeCurlRequest(
        $url,
        'PATCH',
        $jsonData,
        $headers
    );
    
    return $response;
}
      $leadId = $responseAmo[0]['id'];
      $dataAmoTag = [
        "_embedded" => [
            "tags"=> [
                [
                    "name" => "$tag",
                ]
            ]
        ]
    ];
    
     $responseAmoTag = updateAmoLeadTag($leadId, $dataAmoTag);
     if ($responseAmoTag['id']) {
  echo "Тег Amo успешно создан.\n";
  } else {
  die("Ошибка при создании тега Amo: " . print_r($responseAmoTag));
}

