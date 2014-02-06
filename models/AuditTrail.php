<?php

/**
 * This is the model class for table "auditTrail".
 *
 * The followings are the available columns in table 'auditTrail':
 * @property integer $id
 * @property string $action
 * @property string $model
 * @property string $model_pk
 * @property string $id_users
 * @property string $occurredOn
 *
 * The followings are the available model relations:
 * @property AuditTrailDetail[] $auditTrailDetails
 * @property Users $user
 */
class AuditTrail extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return AuditTrail the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'auditTrail';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('action, model, model_pk, occurredOn', 'required'),
			array('action, model, model_pk, id_users', 'length', 'max'=>255),
			// The following rule is used by search().
			array('id, action, model, model_pk, id_users, occurredOn', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'auditTrailDetails' => array(self::HAS_MANY, 'AuditTrailDetail', 'id_auditTrail'),
			'user' => array(self::BELONGS_TO, 'Users', 'id_users'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'action' => 'Action',
			'model' => 'Model',
			'model_pk' => 'Model Pk',
			'id_users' => 'Id Users',
			'occurredOn' => 'Occurred On',
		);
	}

	function getParent(){
		$model_name = $this->model;
		return $model_name::model();
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('action',$this->action,true);
		$criteria->compare('model',$this->model,true);
		$criteria->compare('model_pk',$this->model_pk,true);
		$criteria->compare('id_users',$this->id_users,true);
		$criteria->compare('occurredOn',$this->occurredOn,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	public function scopes() {
		return array(
			'recently' => array(
				'order' => ' t.occurredOn DESC ',
			),

		);
	}
}
