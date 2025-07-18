<?php

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use App\Lib\UtilLibrary;

class RolesTable extends Table
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
        $this->setTable('roles');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->hasMany('Users', [
            'foreignKey' => 'role_id'
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
        $validator
            ->allowEmptyString('role');

        return $validator;
    }

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['role'], 'The Role Name already listed.'));
        return $rules;
    }

    public function getRoleId(string $roleName): int
    {
        if (empty($roleName)) {
            throw new \Exception('Role name is required');
        }
        $role = $this->find()->where(['role' => $roleName])->first();
        if (empty($role) || empty($role->id)) {
            throw new \Exception('Role with name ' . $roleName . ' not found');
        }
        return $role->id;
    }

    public function getRoleIds(array $roleNames): array
    {
        if (empty($roleNames)) {
            throw new \Exception('Role names is required');
        }
        $role = $this->find()
            ->where(['role IN' => $roleNames])
            ->toArray();

        // Map roles to an associative array for easy lookup
        $roleIdMap = [];
        foreach ($role as $role) {
            $roleIdMap[$role->role] = $role->id;
        }

        $roleIds = [];
        foreach ($roleNames as $roleName) {
            if (empty($roleIdMap[$roleName])) {
                throw new \Exception('Role with name ' . $roleName . ' not found');
            }
            $roleIds[] = $roleIdMap[$roleName];
        }

        return $roleIds;
    }

    public function getRoleIdsThatHaveAllContentUnlocked(): array
    {
        $privilegedRoles = [
            UtilLibrary::ROLE_SUPERADMIN_STR,
            UtilLibrary::ROLE_CONTENT_DEVELOPER_STR,
            UtilLibrary::ROLE_TEACHER_STR,
        ];
        return $this->getRoleIds($privilegedRoles);
    }

    public function getRoleIdsThatCanAccessAllPaths(): array
    {
        $roles = [
            UtilLibrary::ROLE_SUPERADMIN_STR,
            UtilLibrary::ROLE_CONTENT_DEVELOPER_STR,
        ];
        return $this->getRoleIds($roles);
    }

    public function getRoleName(int $roleId): string
    {
        if (empty($roleId)) {
            throw new \Exception('Role id is required');
        }
        $result = $this->find()->where(['id' => $roleId])->first();
        if (empty($result) || empty($result->role)) {
            throw new \Exception('Role with id ' . $roleId . ' not found');
        }
        return $result->role;
    }
}
