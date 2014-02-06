<?php

class m110517_155003_create_tables_audit_trail extends CDbMigration
{
	/**
	 * Creates initial version of the audit trail table
	 */
	public function up()
	{
		$this->createTable('auditTrail',
			array(
				'id' => 'pk',
				'action' => 'string NOT NULL',
				'model' => 'string NOT NULL',
				'model_pk' => 'string NOT NULL',
				'id_users' => 'string',
				'occurredOn' => 'datetime NOT NULL',
			)
		);

		$this->createTable('auditTrailDetail',
			array(
				'id' => 'pk',
				'id_auditTrail' => 'integer NOT NULL',
				'field' => 'string NOT NULL',
				'oldValue' => 'text',
				'newValue' => 'text',
			)
		);

		//Index these bad boys for speedy lookups
		$this->createIndex('idx_auditTrail_action', 'auditTrail', 'action');
		$this->createIndex('idx_auditTrail_model', 'auditTrail', 'model');
		$this->createIndex('idx_auditTrail_model_pk', 'auditTrail', 'model_pk');
		$this->createIndex('idx_auditTrail_id_users', 'auditTrail', 'id_users');
		$this->createIndex('idx_auditTrailDetail_field', 'auditTrailDetail', 'field');

		$this->addForeignKey('fk_auditTrailDetail_auditTrail', 'auditTrailDetail', 'id_auditTrail', 'auditTrail', 'id');
	}

	/**
	 * Drops the audit trail table
	 */
	public function down()
	{
		$this->dropForeignKey('fk_auditTrailDetail_auditTrail', 'auditTrailDetail');
		$this->dropTable('auditTrail');
		$this->dropTable('auditTrailDetail');
	}

	/**
	 * Creates initial version of the audit trail table in a transaction-safe way.
	 * Uses $this->up to not duplicate code.
	 */
	public function safeUp()
	{
		$this->up();
	}

	/**
	 * Drops the audit trail table in a transaction-safe way.
	 * Uses $this->down to not duplicate code.
	 */
	public function safeDown()
	{
		$this->down();
	}
}
