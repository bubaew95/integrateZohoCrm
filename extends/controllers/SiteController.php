<?php

namespace app\controllers;

use app\components\ZohoCrmApi;
use app\models\ZohoForm;
use Yii;
use yii\web\Controller;

class SiteController extends Controller
{

    public function actionIndex()
    {
        $model = new ZohoForm();

        if($model->load(Yii::$app->request->post())) {
            if($model->validate() && $model->searchLead()) {
                return $this->goHome();
            }
        }

        return $this->render('index', [
            'model' => $model
        ]);
    }

    public function actionAuth($code)
    {
        $model = new ZohoCrmApi();
        $session = Yii::$app->session;
        if($code){
            $response = $model->getAccessCode($code);
            if(!empty($response->access_token)){
                $session->setFlash('success', "Новый Access Token создан<br>{$response->access_token}");
                $session->set('token', $response->access_token);
            }else {
                $session->setFlash('danger', 'Access Token не создан!');
            }
        }
        return $this->redirect('/');
    }

}