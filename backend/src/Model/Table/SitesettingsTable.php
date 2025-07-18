<?php

namespace App\Model\Table;

use App\Model\Entity\Sitesetting;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Sitesettings Model
 *
 * @method Sitesetting get($primaryKey, $options = [])
 * @method Sitesetting newEmptyEntity($data = null, array $options = [])
 * @method Sitesetting[] newEntities(array $data, array $options = [])
 * @method Sitesetting|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method Sitesetting patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method Sitesetting[] patchEntities($entities, array $data, array $options = [])
 * @method Sitesetting findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class SitesettingsTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('sitesettings');
        $this->setPrimaryKey('id');
        //$this->addBehavior('Timestamp');
    }

    /**
     * Default validation rules.
     *
     * @param Validator $validator Validator instance.
     * @return Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
                ->integer('id')
                ->allowEmptyString('id', 'create');


        $validator
                ->allowEmptyString('value');

        return $validator;
    }

    public function getPrefixedKeys($prefix)
    {
        $settings = $this->find()
            ->where(['`key` LIKE' => $prefix . '%'])
            ->toArray();
        $result = [];
        foreach ($settings as $setting) {
            $result[$setting->key] = $setting->value;
        }
        return $result;
    }
}
