<?php
/**
 * Created by PhpStorm.
 * User: borz
 * Date: 20/03/2019
 * Time: 14:03
 */

namespace app\models;

use Yii;
use app\components\ZohoCrmApi;
use yii\base\Model;

class ZohoForm extends Model
{
    public $name;
    public $phone;
    public $email;
    public $price;
    public $source;

    /**
     * Правила валидации
     * @return array
     */
    public function rules()
    {
        return [
            [['name', 'phone'], 'required'],
            [['email','price', 'source'], 'required'],
            [['price'], 'double'],
            [['name', 'source'], 'string', 'max' => 255],
            [['email'], 'email']
        ];
    }

    /**
     * Аттрибуты
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'name' => 'Имя',
            'phone' => 'Телефон',
            'email' => 'E-mail',
            'price' => 'Сумма',
            'source' => 'Источник',
        ];
    }

    /**
     * Поиск номера в Лиде.
     * @return bool|mixed|void
     */
    public function searchLead()
    {
        $model = new ZohoCrmApi();
        if($itemsSearch = $model->searchLeads('Phone', $this->phone)) {
            if(isset($itemsSearch->data)){
                return $this->convertLead(
                    $model,
                    $itemsSearch->data[0] //проверяю на первого найденного item
                );
            } else {
                Yii::$app->session->setFlash('danger', $itemsSearch->code);
                return false;
            }
        }
        return $this->createLead($model);
    }

    /**
     * Создания лида
     * @param ZohoCrmApi $model
     * @return mixed
     */
    private function createLead(ZohoCrmApi $model)
    {
        $response = $model->setLeads([
            [
                'Last_Name'     => $this->name,
                'First_Name'    => 'Api',
                'Phone'         => $this->phone,
                'Email'         => $this->email,
            ],
        ]);
        if($response)
            Yii::$app->session->setFlash('success', 'Новый лид создан');
        else
            Yii::$app->session->setFlash('danger', 'При создании Лида что-то пошло не так');
        return $response;
    }

    /**
     * создание сделки из существующего лида
     * @param ZohoCrmApi $model
     * @param $response
     */
    private function convertLead(ZohoCrmApi $model, $response )
    {
        $response = $model->convertLeads(
            $response->id,
            $response->Owner->id, [
                "Deal_Name"     => $response->Full_Name,
                "Closing_Date"  => date('Y-m-d'),
                "Stage"         => $this->source,
                "Amount"        => (double) $this->price
            ]
        );
        if($response)
            Yii::$app->session->setFlash('success',
                'Лид с таким номером существовал в ZohoCrm, его конвертировали в следку'
            );
        else
            Yii::$app->session->setFlash('danger', 'При конвертировании Лида что-то пошло не так');
    }
}