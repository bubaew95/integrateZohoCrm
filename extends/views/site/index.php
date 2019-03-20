<?php

/* @var $this yii\web\View */

use yii\widgets\ActiveForm;
use yii\helpers\Html;
use app\components\ZohoCrmApi;


$this->title = 'My Yii Application';
?>
<div class="site-index">


    <div class="row">
        <div class="col-md-6 col-md-offset-3">

            <div class="pull-right">
                <?= Html::a('Получить токен', ZohoCrmApi::getUriForCode(), ['class' => 'btn btn-warning btn-sm'])?>
            </div>
            <div class="clearfix"></div>

            <h3 class="text-center">Интеграция с Zoho CRM</h3>
            <?php $form = ActiveForm::begin(['options' => ['id' => 'form-submit']]); ?>
                <?= $form->field($model, 'name')->textInput() ?>

                <div class="row">
                    <div class="col-md-6">
                        <?= $form->field($model, 'phone')->textInput(['value' => '89280010203']) ?>
                    </div>
                    <div class="col-md-6">
                        <?= $form->field($model, 'email')->textInput() ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <?= $form->field($model, 'price')->textInput() ?>
                    </div>
                    <div class="col-md-6">
                        <?= $form->field($model, 'source')->dropDownList([
                                '-None-' => '-None-',
                                'Оценка пригодности' => 'Оценка пригодности',
                                'Требуется анализ' => 'Требуется анализ',
                                'Ценностное предложение' => 'Ценностное предложение',
                        ]) ?>
                    </div>
                </div>

                <div class="form-group pull-right">
                    <?= Html::submitButton("Отправить", ['class' => 'btn btn-success', 'id' => 'btn_submit']) ?>
                </div>
            <div class="clearfix"></div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>

</div>