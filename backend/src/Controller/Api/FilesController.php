<?php

namespace App\Controller\Api;

class FilesController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
    }

    // Api function for all the speed and get path by id.
    public function getfIle($id = null)
    {
        $data = $this->request->getData();
        $order = array();
        if (isset($data['sort']) && $data['sort'] != '') {
            $order[$data['sort']] = $data['sortType'];
        }

        if ($id != null) {
            $Files = $this->getFilesTable()->find()->where(['id =' => $id]);
        } else {
            $Files = $this->getFilesTable()
                ->find()
                ->order($order)
                ->where(function (\Cake\Database\Expression\QueryExpression $exp, \Cake\ORM\Query $query) {

                    $data = $this->request->getData();
                    $nameCondition = array();
                    if (isset($data['q']) && $data['q'] != '') {
                        $nameCondition['name LIKE'] = '%' . $data['q'] . '%';
                    }
                    $descriptionCondition = array();
                    if (isset($data['q']) && $data['q'] != '') {
                        $descriptionCondition['description LIKE'] = '%' . $data['q'] . '%';
                    }
                    $filterKeyCondition = array();
                    if (isset($data['filterkey']) && $data['filterkey'] != '') {
                        $filterKeyCondition[$data['filterkey'] . ' LIKE'] = '%' . $data['filtervalue'] . '%';
                    }

                    $nameAndDescription = $query->newExpr()->or([$nameCondition])->add([$descriptionCondition]);
                    $filterKey = $query->newExpr()->and([$filterKeyCondition]);
                    return $exp->and([$nameAndDescription, $filterKey]);
                });
        }
        $this->sendApiData(true, 'Result return successfully.', $Files);
    }

    //for delete the file
    public function deleteFile()
    {
        $data = $this->request->getData();
        $id = $data['id'];
        $file = $this->getFilesTable()->get($id);
        if ($this->getFilesTable()->delete($file)) {
            $this->sendApiData(true, 'File Deleted successfully.', array());
        } else {
            $this->sendApiData(true, 'The file could not be deleted. Please, try again.', array());
        }
    }
}
