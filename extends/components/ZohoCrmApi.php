<?php
/**
 * Created by PhpStorm.
 * User: borz
 * Date: 20/03/2019
 * Time: 19:10
 */

namespace app\components;
use Yii;

class ZohoCrmApi
{

    const HOST          = "https://www.zohoapis.eu/crm/v2/";
    const OAUTH         = 'https://accounts.zoho.eu/oauth/v2/';

    const SCOPE         = "ZohoCRM.users.ALL,ZohoCRM.modules.ALL,ZohoCRM.settings.ALL,ZohoCRM.org.ALL";

    const CLIENT_ID     = '1000.8J4LO5O78VD274983XS2C3XTWBR93S';
    const CLIENT_SECRET = '6effe783d059570daaf2306aab997ff36434e11f51';
    const REDIRECT_URL  = 'http://zoho:8888/index.php?r=site/auth';

    public $TOKEN;

    public function __construct()
    {
        $session = Yii::$app->session;
        if($session->has('token')) {
            $this->TOKEN = $session->get('token');
        }
    }

    /**
     * get leads
     * @param $id
     * @return mixed
     */
    public function getLeads($id)
    {
        return $this->makeCurl(self::HOST . "Leads/{$id}");
    }

    /**
     * cretea leads
     * @param $fields
     * @return mixed
     */
    public function setLeads($fields)
    {
        $curl = $this->makeCurl(self::HOST . "Leads", $fields, 'post');
        if($curl  != null && isset($curl->data)) {
            return true;
        }
        return false;
    }

    /**
     * Конвертация лида
     * @param $id
     * @param $userId
     * @param array $deal
     * @return bool
     */
    public function convertLeads($id, $userId, array $deal = [])
    {
        $fields = [
            [
                "overwrite" => true,
                "notify_lead_owner" => true,
                "notify_new_entity_owner" => true,
                "assign_to" => (int) $userId,
                "Deals" => $deal
            ]
        ];
        $curl = $this->makeCurl(self::HOST . "Leads/{$id}/actions/convert", $fields, 'post');

        if($curl  != null && isset($curl->data)) {
            return true;
        }
        return false;
    }

    /**
     * search leads
     * @param $column
     * @param $query
     * @return mixed
     */
    public function searchLeads($column, $query)
    {
        return $this->makeCurl(self::HOST . "Leads/search?criteria=({$column}:equals:{$query})");
    }

    /**
     * get headers
     * @param $fields
     * @return array
     */
    private function getHeaders($fields) : array
    {
        return [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($fields),
            sprintf('Authorization: Zoho-oauthtoken %s', $this->TOKEN)
        ];
    }

    /**
     * Создание ссылки для получения CODE
     * @return string
     */
    public static function getUriForCode()
    {
        return sprintf('%s?scope=%s&client_id=%s&response_type=code&access_type=online&redirect_uri=%s',
            self::OAUTH . 'auth',
            self::SCOPE,
            self::CLIENT_ID,
            self::REDIRECT_URL
        );
    }

    /**
     * Получния access token из code
     * @param $code
     * @return mixed|null
     */
    public function getAccessCode($code)
    {
        $params = [
            'code'          => $code,
            'client_id'     => self::CLIENT_ID,
            'client_secret' => self::CLIENT_SECRET,
            'redirect_uri'  => self::REDIRECT_URL,
            'grant_type'    => 'authorization_code'
        ];
        return $this->makeCurl(self::OAUTH . 'token', $params, 'post', true);
    }

    /**
     * set curl
     * @param $url
     * @param string $data
     * @param string $method
     * @return mixed|null
     */
    private function makeCurl($url, $data = '', $method = 'get', $formData = false)
    {
        $ch = curl_init();
        $repSpaceUrl = str_replace(" ", "+", $url);
        curl_setopt($ch, CURLOPT_URL, $repSpaceUrl);

        if(!$formData) {
            $data = !empty($data) ? json_encode(['data' => $data]) : '';
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getHeaders($data));
        }

        if($method == 'post') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        $result = curl_exec($ch);
        curl_close($ch);

        return json_decode($result) ?? null;
    }
}


//https://accounts.zoho.eu/oauth/v2/auth?scope=ZohoCRM.users.ALL&client_id=1000.8J4LO5O78VD274983XS2C3XTWBR93S&response_type=code&access_type=offline&redirect_uri=http://zoho:8888/index.php?r=site/auth