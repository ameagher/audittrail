<?php

/**
 * This is the model class for table "auditTrailDetail".
 *
 * The followings are the available columns in table 'auditTrailDetail':
 * @property integer $id
 * @property integer $id_auditTrail
 * @property string $field
 * @property string $oldValue
 * @property string $newValue
 *
 * The followings are the available model relations:
 * @property AuditTrail $idAuditTrail
 */
class AuditTrailDetail extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'auditTrailDetail';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('id_auditTrail, field', 'required'),
			array('id_auditTrail', 'numerical', 'integerOnly'=>true),
			array('field', 'length', 'max'=>255),
			array('oldValue, newValue', 'safe'),
			// The following rule is used by search().
			array('id, id_auditTrail, field, oldValue, newValue', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'idAuditTrail' => array(self::BELONGS_TO, 'AuditTrail', 'id_auditTrail'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'id_auditTrail' => 'Id Audit Trail',
			'field' => 'Field',
			'oldValue' => 'Old Value',
			'newValue' => 'New Value',
		);
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
		$criteria->compare('id_auditTrail',$this->id_auditTrail);
		$criteria->compare('field',$this->field,true);
		$criteria->compare('oldValue',$this->oldValue,true);
		$criteria->compare('newValue',$this->newValue,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return AuditTrailDetail the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
