<?php

namespace amilna\cap\models;

use Yii;

/**
 * This is the model class for table "{{%cap_template}}".
 *
 * @property integer $id
 * @property string $title
 * @property string $json
 */
class Template extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%cap_template}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'json'], 'required'],
            [['json'], 'string'],
            [['title'], 'string', 'max' => 255],
            [['title'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'title' => Yii::t('app', 'Title'),
            'json' => Yii::t('app', 'Json'),
        ];
    }
}
