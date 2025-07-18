<?php

namespace App\Model\Table;

use App\Model\Entity\CardUnit;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Association\BelongsTo;
use Cake\ORM\Query;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * CardUnits Model
 *
 * @property CardTable|BelongsTo $Cards
 * @property UnitsTable|BelongsTo $Units
 *
 * @method CardUnit get($primaryKey, $options = [])
 * @method CardUnit newEmptyEntity($data = null, array $options = [])
 * @method CardUnit[] newEntities(array $data, array $options = [])
 * @method CardUnit|bool save(EntityInterface $entity, $options = [])
 * @method CardUnit patchEntity(EntityInterface $entity, array $data, array $options = [])
 * @method CardUnit[] patchEntities($entities, array $data, array $options = [])
 * @method CardUnit findOrCreate($search, callable $callback = null, $options = [])
 */
class CardUnitsTable extends Table
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

        $this->setTable('card_units');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->belongsTo('Cards', [
            'foreignKey' => 'card_id'
        ]);
        $this->belongsTo('Units', [
            'foreignKey' => 'unit_id'
        ]);
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

        return $validator;
    }

    /**
     * Get array of all unique cards in the specified unit.
     * @param $unitId ID of unit to get cards from
     * @return array of non-duplicate cards in the specified unit, or null
     */
    public function getCardsByUnitId($unitId)
    {
        /* Get array of cards in specified unit with just the fields 'id' and 'card_id' */
        $unitCards = $this->find()
            ->where(['unit_id' => $unitId])
            ->all()
            ->combine('id', 'card_id')
            ->toArray();

        /* Get rid of all duplicate cards */
        return array_values(array_unique($unitCards));
    }

    public function getReviewCardsInUnit($unitId): Query
    {
        return $this->find()
            ->contain(['Cards'])
            ->where(['unit_id' => $unitId, 'Cards.include_review' => '1']);
    }

    /**
     * Check if there are cards to review for the specified unit.
     * @param $unitId id of unit of which to check cards
     * @return int|null if no cards to review for this unit, otherwise false
     */
    public function numReviewCardsInUnit($unitId): int
    {
        $unitCards = $this->find()
            ->contain(['Cards'])
            ->where(['unit_id' => $unitId, 'Cards.include_review' => '1'])
            ->toArray();

        return count($unitCards);
    }
}
