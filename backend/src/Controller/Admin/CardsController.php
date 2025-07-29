<?php

namespace App\Controller\Admin;

use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Routing\Router;
use Cake\Log\Log;
use App\Lib\UploadUtils;
use App\Model\Entity\Card;
use Cake\Database\Expression\QueryExpression;

class CardsController extends AppController
{
    private $CardIdIndex = 0;
    private $CardTypeIndex = 1;
    private $LanguageIndex = 2;
    private $EnglishIndex = 3;
    private $GenderIndex = 4;
    private $AltLanguageIndex = 5;
    private $AltEnglishIndex = 6;
    private $AudioIndex = 7;
    private $ImageIndex = 8;
    private $VideoIndex = 9;
    private $IncludeReviewIndex = 10;
    private $ValidGenders = ['default', 'male', 'female', 'neuter'];

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
    }

    /**
     * Get a list of cards for DataTables.
     * This function is used by the DataTables plugin to populate the table.
     * - POST Parameters:
     *   - start: Start index for pagination
     *   - length: Number of items per page
     *   - search: Search term
     *   - name: Name of the table
     *   - class: Class of the table
     *   - value: Preselected card ID
     *   - blockNo: Lesson block number
     *   - responseType: Response type
     * @return mixed - JSON response with card data
     */
    public function getCardList()
    {
        $this->request->allowMethod(['post']);

        // Fetch data for the DataTables response
        $query = $this->getCardTable()->find('all')
            ->contain(['Cardtype']);

        // Get DataTables parameters
        $requestData = $this->request->getData();
        $start = $requestData['start'] ?? 0;
        $length = $requestData['length'] ?? 10;
        $searchValue = $requestData['search']['value'] ?? '';
        $name = $requestData['name'] ?? null;
        $class = $requestData['class'] ?? null;
        $value = $requestData['value'] ?? null;
        $blockNo = $requestData['blockNo'] ?? null;
        $responseType = $requestData['responseType'] ?? null;

        // Apply search filter if present
        if (!empty($searchValue)) {
            $query->where(function ($exp, $q) use ($searchValue) {
                $conditions = [];

                if (!is_numeric($searchValue)) {
                    $conditions = [
                        'lakota LIKE' => '%' . $searchValue . '%',
                        'english LIKE' => '%' . $searchValue . '%',
                        'alt_lakota LIKE' => '%' . $searchValue . '%',
                        'alt_english LIKE' => '%' . $searchValue . '%',
                    ];
                }

                // Add the 'id' filter only if $searchValue is an integer
                if (is_numeric($searchValue)) {
                    $conditions['Card.id'] = $searchValue;
                }

                return $exp->or($conditions);
            });
        }

        // Total records before slicing for pagination
        $totalRecords = $query->count();

        // Get the current page's data
        $filteredQuery = $query->limit($length)->offset($start);
        $totalFilteredRecords = $filteredQuery->count();

        // Fetch the selected card(s) explicitly if needed
        $selectedCardQuery = $this->getCardTable()->find('all')
            ->contain(['Cardtype'])
            ->where(['Card.id IS' => $value]);

            // Convert data to array for JSON response
        $selectedCards = [];
        $unselectedCards = [];

        foreach ($selectedCardQuery as $card) {
            $selectedCards[] = $this->createCardListRow($card, true, $name, $class, $responseType, $blockNo);
        }

        // Build data for the current page
        foreach ($filteredQuery as $card) {
            // Skip selected cards to avoid duplicates
            if ($card->id == $value) {
                continue;
            }

            $unselectedCards[] = $this->createCardListRow($card, false, $name, $class, $responseType, $blockNo);
        }

        // Combine selected and unselected cards
        $combinedData = array_merge($selectedCards, $unselectedCards);

        // Slice data for the current page (respecting pagination)
        $pagedData = array_slice($combinedData, 0, $length);

        // Adjust filtered record count if the selected card was added
        if (!empty($selectedCards) && $filteredQuery->where(['Card.id' => $value])->count() == 0) {
            $totalFilteredRecords++;
        }

        $response = [
            'draw' => intval($requestData['draw'] ?? 1),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalFilteredRecords,
            'data' => $pagedData,
        ];

        // Send JSON response, bypassing CakePHP's view rendering
        $this->response = $this->response->withType('application/json')->withStringBody(json_encode($response));
        return $this->response;
    }

    /**
     * Create a row for the card list.
     * @param $card - Card entity
     * @param $selected - True if the card is selected, false otherwise
     * @param $name - Name attribute of the input radio element
     * @param $class - Class attribute of the input radio element
     * @param $responseType - Response type of the card for data-type attribute
     * @param $blockNo - Lesson block number for data-block attribute
     * @return array - Array with card data
     */
    private function createCardListRow($card, $selected, $name, $class, $responseType, $blockNo) {
        $select = '<input type="radio" name="' . $name . $blockNo . '" class="' . $class . '" value="' . $card->id . '"';
        if ($selected) {
            $select .= ' checked="checked"';
        }
        $select .= ' data-type="' . (!empty($responseType) ? $responseType : '') . '"';
        $select .= ' data-block="' . (!empty($blockNo) ? $blockNo : '') . '"';
        $select .= '>';

        // Return the data for this row
        return [
            'select' => $select,
            'id' => $card->id,
            'type' => $card->cardtype->title,
            'lakota' => $card->lakota,
            'english' => $card->english,
            'alt_lakota' => $card->alt_lakota,
            'alt_english' => $card->alt_english,
            'gender' => $card->gender,
            'audio' => $card->audio ? '<i class="fa fa-volume-up"></i>' : '',
            'image' => $card->image_id ? '<i class="fa fa-picture-o"></i>' : '',
            'video' => $card->video_id ? '<i class="fa fa-video-camera"></i>' : '',
        ];
    }

    /**
     * Get a list of cards for Select2 dropdown.
     * This function is used by the Select2 plugin to populate the dropdown.
     * - GET Parameters
     *   - q: Search term. Inherent to Select2.
     * @return mixed - JSON response with card data
     */
    public function getCardListForSelect2() {
        $this->request->allowMethod(['get']);

        // Query card table with columns that are shown in the select2 dropdown
        $query = $this->getCardTable()->find('all')
            ->select(['id', 'lakota', 'english']);

        // Get search term and filter cards based on it
        $searchValue = $this->request->getQuery('q');
        if (!empty($searchValue)) {
            $query->where(function ($exp) use ($searchValue) {
                if (!is_numeric($searchValue)) {
                    // Search for the search term in the lakota, english, alt_lakota, and alt_english fields
                    return $exp->or([
                        'lakota LIKE' => '%' . $searchValue . '%',
                        'english LIKE' => '%' . $searchValue . '%',
                        'alt_lakota LIKE' => '%' . $searchValue . '%',
                        'alt_english LIKE' => '%' . $searchValue . '%',
                    ]);
                } else {
                    // Add the 'id' filter only if $searchValue is an integer
                    return $exp->eq('Card.id', $searchValue);
                }
            });
        }

        // Since we cna't strip the HTML tags from the lakota and english fields in the query,
        // due to not having MySql 8.0 or greater, we'll do it in PHP
        $cardIds = [];
        foreach ($query as $card) {
            if (!is_numeric($searchValue)) {
                if (stripos(strip_tags($card->lakota), $searchValue) !== false ||
                    stripos(strip_tags($card->english), $searchValue) !== false
                ) {
                    $cardIds[] = $card->id;
                }
            } else {
                $cardIds[] = $card->id;
            }
        }

        // Use the card IDs to filter the query again
        if (!empty($cardIds)) {
            $query->where(['id IN' => $cardIds])
                ->order([
                    new QueryExpression('LENGTH(lakota) ASC'),
                    new QueryExpression('LENGTH(english) ASC')
                ]);
        }

        // Pagination: Get the page number and limit from the query string
        $page = $this->request->getQuery('page', 1);
        $limit = 10; // Number of items per page
        $offset = ($page - 1) * $limit; // What item to start from

        // Get the total number of cards found and limit and offset the query
        // based on the pagination values.
        $totalRecords = $query->count();
        $query = $query->limit($limit)->offset($offset);

        // Convert data to array for JSON response. id, text, and html are required
        // by Select2, and html is used to display the card in the dropdown.
        $items = [];
        foreach ($query as $card) {
            $items[] = [
                'id' => $card->id,
                'text' => '(' . $card->id . ') ' . strip_tags($card->lakota) . ' | ' . strip_tags($card->english), // Plain text for safe display
                'html' => '(' . $card->id . ') ' . $card->lakota . ' | ' . $card->english // HTML-formatted content for dropdown customization
            ];
        }

        // Pagination information, required by Select2
        $pagination = [
            'more' => ($offset + $limit) < $totalRecords
        ];

        // Response data, required by Select2
        $response = [
            'items' => $items,
            'pagination' => $pagination
        ];

        // Send JSON response, bypassing CakePHP's view rendering
        $this->response = $this->response->withType('application/json')->withStringBody(json_encode($response));
        return $this->response;
    }

    /**
     * Get selected cards for Select2 dropdown.
     * This function is used by the Select2 plugin to populate the dropdown with selected cards.
     * - GET Parameters
     *   - ids: Array of card IDs
     * @return mixed - JSON response with selected card data
     */
    public function getSelectedCardsForSelect2()
    {
        $this->request->allowMethod(['get']);
        $ids = $this->request->getQuery('ids', []);

        if (empty($ids)) {
            return $this->response->withType('application/json')->withStringBody(json_encode([
                'items' => []
            ]));
        }

        $query = $this->getCardTable()->find()
            ->select(['id', 'lakota', 'english'])
            ->where(['id IN' => $ids]);

        $items = [];
        foreach ($query as $card) {
            $items[] = [
                'id' => $card->id,
                'text' => '(' . $card->id . ') ' . strip_tags($card->lakota) . ' | ' . strip_tags($card->english), // Plain text for safe display
                'html' => '(' . $card->id . ') ' . $card->lakota . ') | ' . $card->english // HTML-formatted content for dropdown customization
            ];
        }

        return $this->response->withType('application/json')->withStringBody(json_encode([
            'items' => $items
        ]));
    }

    // List all cards
    public function cardsList()
    {
        $condition = array();
        if (!empty($_GET['q'])) {
            $condition['OR'] = array(
                'Inflections.headword LIKE' => '%' . $_GET['q'] . '%',
                'Card.english LIKE' => '%' . $_GET['q'] . '%',
                'Card.lakota LIKE' => '%' . $_GET['q'] . '%',
                'Card.audio LIKE' => '%' . $_GET['q'] . '%',
                'Card.alt_lakota LIKE' => '%' . $_GET['q'] . '%',
                'Cardtype.title LIKE' => '%' . $_GET['q'] . '%',
                'Card.alt_english LIKE' => '%' . $_GET['q'] . '%',
                'Card.gender LIKE' => '%' . $_GET['q'] . '%',
                'image.file_name LIKE' => '%' . $_GET['q'] . '%',
                'video.file_name LIKE' => '%' . $_GET['q'] . '%'
            );
        }

        if (!empty($_GET['card_type'])) {
            $condition['Card.card_type_id'] = $_GET['card_type'];
        }

        if (!empty($_GET['gender'])) {
            $condition['Card.gender'] = $_GET['gender'];
        }

        if (!empty($_GET['lakota'])) {
            $condition['Card.lakota LIKE'] = '%' . $_GET['lakota'] . '%';
        }

        if (!empty($_GET['english'])) {
            $condition['Card.english LIKE'] = '%' . $_GET['english'] . '%';
        }

        if (!empty($_GET['audio'])) {
            if ($_GET['audio'] == 'y') {
                $condition['Card.audio IS NOT'] = null;
                $condition['Card.audio !='] = '';
            } else {
                $condition['OR']['Card.audio IS'] = null;
                $condition['OR']['Card.audio'] = '';
            }
        }

        if (!empty($_GET['video'])) {
            if ($_GET['video'] == 'y') {
                $condition['Card.video_id IS NOT'] = null;
            } else {
                $condition['OR']['Card.video_id IS'] = null;
                $condition['OR']['Card.video_id'] = '';
            }
        }

        if (!empty($_GET['image'])) {
            if ($_GET['image'] == 'y') {
                $condition['Card.image_id IS NOT'] = null;
            } else {
                $condition['OR']['Card.image_id IS'] = null;
                $condition['OR']['Card.image_id'] = '';
            }
        }

        if (!empty($_GET['alternate'])) {
            if ($_GET['alternate'] == 'y') {
                $condition['OR'] = array(
                    array(
                        'AND' => array(
                            'Card.alt_english IS NOT' => null,
                            'Card.alt_english !=' => '',
                        )
                    ),
                    array(
                        'AND' => array(
                            'Card.alt_lakota IS NOT' => null,
                            'Card.alt_lakota !=' => '',
                        )
                    )
                );
            } else {
                $condition['AND'] = array(
                    array(
                        'OR' => array(
                            'Card.alt_english IS' => null,
                            'Card.alt_english' => '',
                        )
                    ),
                    array(
                        'OR' => array(
                            'Card.alt_lakota IS' => null,
                            'Card.alt_lakota' => '',
                        )
                    )
                );
            }
        }

        $this->paginate = [
            'sortableFields' => [
                'Cardtype.title', 'video.file_name', 'image.file_name',
                'Inflections.headword', 'lakota', 'english', 'gender',
                'alt_lakota', 'alt_english', 'audio', 'id', 'include_review'
            ]
        ];

        $query = $this->getCardTable()->find()
            ->where($condition)
            ->contain(['Dictionary', 'Inflections', 'Cardtype', 'image', 'video']);

        $cards = $this->paginate($query);
        $allCardTypes = $this->getCardtypeTable()->find('all', array());
        $cardTypes = array();
        $optionTypes = array('y' => 'Yes', 'n' => 'No');
        $genders = array('male' => 'Male', 'female' => 'Female', 'default' => 'Default', 'neuter' => 'Neuter');
        foreach ($allCardTypes as $value) {
            $cardTypes[$value->id] = $value->title;
        }
        $languageName = Configure::read('LANGUAGE');

        $this->set(compact('cards', 'cardTypes', 'optionTypes', 'genders', 'languageName'));
        $this->viewBuilder()->setOption('serialize', [
            'cards', 'cardTypes', 'optionTypes', 'genders'
        ]);
    }

    /**
     * Add new cards to the database.
     */
    public function addCards()
    {
        $card = $this->getCardTable()->newEmptyEntity();
        $cardTypesQuery = $this->getCardtypeTable()->find('all', array())->toArray();
        $cardTypes = array_column($cardTypesQuery, 'title', 'id');
        $languageName = Configure::read('LANGUAGE');

        if ($this->request->is('post')) {
            $data = $this->request->getData();
            if (!empty($data['audios']) && gettype($data['audios']) == 'array') {
                $data['audio'] = implode(",", $data['audios']);
            }
            $card = $this->getCardTable()->patchEntity($card, $data);

            if ($card->hasErrors()) {
                $this->Flash->error(__('Please fill in all the fields.'));
                $this->set(compact('card', 'cardTypes', 'languageName'));
                return;
            } else {
                if ($this->getCardTable()->save($card)) {
                    $this->Flash->success(__('The card has been saved.'));
                    return $this->redirect(['action' => 'addCards']);
                }
            }
        }

        $this->set(compact('card', 'cardTypes', 'languageName'));
    }


    /**
     * Upload an Excel file for batch card creation, verify the cards,
     * and then save them to the database.
     * Once a file is selected and Preview is pressed, the cards are displayed
     * with rows for new cards highlighted in Green, and rows for existing cards
     * with values highlighted based on if the value has been added, updated,
     * or removed.
     */
    public function uploadCards()
    {
        $file = $this->getFilesTable()->newEmptyEntity();

        if (!$this->request->is('post')) {
            // If not data is posted, render the upload form
            return $this->renderUploadForm($file);
        }

        $data = $this->request->getData();

        if (empty($data['file'])) {
            return $this->handleInvalidFileUpload();
        }

        $uploadedFile = $data['file'];

        if ($this->isInvalidUpload($uploadedFile)) {
            return $this->redirectWithError(__('Please upload an excel file less then 2 MB.', 'uploadCards'), 'uploadCards');
        }

        if (!UploadUtils::isExcelFile($uploadedFile)) {
            return $this->redirectWithError(__('Please upload an Excel (.xls or .xlsx) file.'), 'uploadCards');
        }

        $spreadsheetData = $this->extractSpreadsheetData($uploadedFile);

        if (empty($spreadsheetData['Sheet1'])) {
            return $this->redirectWithError(__('No Sheet1 found. Make sure the sheet is named "Sheet1".'), 'uploadCards');
        }

        $validRows = UploadUtils::getValidRowsFromSheet(array_slice($spreadsheetData['Sheet1'], 1));

        // This is the main function that processes the cards
        $cards = $this->makeCardsFromArray($validRows);

        if (empty($cards)) {
            $this->Flash->warning(__('No cards found in the spreadsheet.'));
            return $this->redirect(['action' => 'uploadCards']);
        }

        if ($this->hasErrors($cards)) {
            return $this->handleCardErrors($cards);
        }

        return $this->displayUploadCardsPreview($cards);
    }

    /**
     * Creates Card entities from each row in the spreadsheet,
     * and if it's valid, either adds it to the list of cards to be verified by user,
     * or handles duplicates by checking Card ID in the database and marking which
     * fields have been added, updated, or removed.
     * @param $rows - Array of rows from the spreadsheet
     * @return array - Array of Card entities or errors
     */
    private function makeCardsFromArray($rows): array
    {
        $cards = [];
        $errors = [];
        $rowNumber = 2; // Row 1 is the header row in the spreadsheet

        // Make cards out of the php array
        foreach ($rows as $row) {
            $card = $this->makeCardFromArray($row);
            if ($this->isValidCard($card)) {
                $this->addCardOrHandleExisting($card, $cards, $errors, $rowNumber);
            } else {
                $errors['error'][] = $this->err($rowNumber, $card['error'] ?? 'Generic error: Could not create card.');
            }
            $rowNumber++;
        }

        return !empty($errors) ? $errors : $cards;
    }

    /**
     * Create a Card entity from an array of data.
     * If the card is valid, return the Card entity.
     * If the card is invalid, return an array with an error message.
     * @param $row - Array of card data
     * @return mixed - Card entity if valid, array with error message if invalid
     */
    private function makeCardFromArray(array $row)
    {
        $card = $this->getCardTable()->newEmptyEntity();

        $cardId = $row[$this->CardIdIndex];
        $language = $row[$this->LanguageIndex];
        $english = $row[$this->EnglishIndex];
        $gender = strtolower($row[$this->GenderIndex]);
        $cardTypeString = ucwords(strtolower($row[$this->CardTypeIndex]));

        if (empty($row[$this->GenderIndex])) {
            $row[$this->GenderIndex] = 'default';
        }

        if (empty($row[$this->CardIdIndex])) {
            $requiredFieldsErrors = $this->validateRequiredFields($language, $english, $gender, $cardTypeString);
            if ($requiredFieldsErrors) {
                return ['error' => $requiredFieldsErrors];
            }
        }

        //find IDs for linked objects, build informative errors
        $cardTypeId = null;
        if (empty($cardTypeString)) {
            if (empty($row[$this->CardIdIndex])) {
                return ['error' => 'Card ID is a required field, unless updating an existing card.'];
            }
        } else {
            $cardTypeId = (int)$this->findTypeId($cardTypeString);
            if ($cardTypeId === null) {
                return ['error' => 'Could not find card type "' . $cardTypeString . '".'];
            }
        }

        $audioIdsArray = $this->findFileIdIfExists($row[$this->AudioIndex], 'audio');
        if (isset($audioIdsArray['error'])) {
            return ['error' => $audioIdsArray['error']];
        }
        $audioIds = is_array($audioIdsArray) ? implode(',', $audioIdsArray) : '';
        $imageId = $this->findFileIdIfExists($row[$this->ImageIndex], 'image');
        if (isset($imageId['error'])) {
            return ['error' => $imageId['error']];
        }
        $videoId = $this->findFileIdIfExists($row[$this->VideoIndex], 'video');
        if (isset($videoId['error'])) {
            return ['error' => $videoId['error']];
        }

        $include_review = array_search(
            $row[$this->IncludeReviewIndex], [true, 1, '1', 'Y', 'y', 'yes', 'Yes', 'YES']
        ) !== false ? 1 : 0;

        //assign values to the Card object
        //required fields
        $card->id = $cardId;
        $card->lakota = $language;
        $card->english = $english;
        $card->gender = $gender;
        $card->is_active = 1;
        $card->card_type_id = $cardTypeId ?? "";
        //optional fields
        $card->alt_lakota = $row[$this->AltLanguageIndex];
        $card->alt_english = $row[$this->AltEnglishIndex];
        $card->image_id = $imageId;
        $card->video_id = $videoId;
        $card->audio = $audioIds;
        $card->include_review = $include_review;

        return $card;
    }

    /**
     * Render the upload form with the file entity.
     * @param $file - File entity
     * @return mixed - Rendered view
     */
    private function renderUploadForm($file)
    {
        $this->set(compact('file'));
        return $this->render('upload_cards');
    }

    /**
     * Handle an invalid file upload by redirecting with an error message.
     * @return mixed
     */
    private function handleInvalidFileUpload()
    {
        $this->Flash->error(__('Something is wrong with the file size or the file itself. Please try again.'));
        return $this->redirect(['action' => 'uploadCards']);
    }

    /**
     * Check if the uploaded file has an error.
     * @param $uploadedFile - Uploaded file
     * @return bool - True if the file has an error, false otherwise
     */
    private function isInvalidUpload($uploadedFile)
    {
        return $uploadedFile->getError() !== UPLOAD_ERR_OK;
    }

    /**
     * Redirect with an error message.
     * @param string $message - Error message
     * @param string $action - Action to redirect to
     * @return mixed - Redirect
     */
    private function redirectWithError(string $message, string $action)
    {
        $this->Flash->error($message);
        return $this->redirect(['action' => $action]);
    }

    /**
     * Extract data from the uploaded spreadsheet file.
     * @param $uploadedFile - Uploaded file
     * @return array - Array of spreadsheet data
     */
    private function extractSpreadsheetData($uploadedFile): array
    {
        $tempPath = $uploadedFile->getStream()->getMetadata('uri');
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($tempPath);

        $arrayData = [];
        foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
            $arrayData[$worksheet->getTitle()] = $worksheet->toArray();
        }
        return $arrayData;
    }

    /**
     * Checks if the cards array has errors.
     * @param mixed $cards - Card entity or array with error field
     * @return bool - True if the cards array has errors, false otherwise
     */
    private function hasErrors($cards): bool
    {
        return array_key_exists('error', $cards) || array_key_exists('duplicates', $cards);
    }

    /**
     * Display errors for cards.
     * @param $cards - Array of cards with errors
     */
    private function displayCardErrors($cards)
    {
        $errCount = 0;

        if (array_key_exists('error', $cards)) {
            foreach ($cards['error'] as $error) {
                $this->displayError($error);
                if (++$errCount > 5) break;
            }
        }

        if ($errCount > 5) {
            $this->Flash->warning(__('More than 5 errors found, only showing the first 5.'));
        }
    }

    /**
     * Handle card errors by displaying the errors and redirecting to the upload page.
     * @param $cards - Array of errors
     * @return mixed - Redirect to the upload page
     */
    private function handleCardErrors($cards)
    {
        $this->Flash->warning(__('One or more errors occurred while processing the file. No cards were created. Please make necessary corrections and retry the upload.'));
        $this->displayCardErrors($cards);

        return $this->redirect(['action' => 'uploadCards']);
    }

    /**
     * Display a duplicate error for a card.
     * @param $dup - Array with row number and card ID
     */
    private function displayDuplicateError($dup)
    {
        $this->Flash->carduploaderror(
            __('Duplicate at row {0}: Card already exists with ID {1}', $dup['row'], $dup['value']),
            [
                'params' => [
                    'url' => Router::url(['controller' => 'Cards', 'action' => 'edit', $dup['value']]),
                    'id' => $dup['value']
                ]
            ]
        );
    }

    /**
     * Display an error for a card.
     * @param $error - Array with row number and value
     */
    private function displayError($error)
    {
        $this->Flash->error(__('Error at row {0}: {1}', $error['row'], $error['value']));
    }

    /**
     * Check if a card is valid.
     * @param mixed $card - Card entity or array with error field
     * @return bool - True if the card is valid, false otherwise
     */
    private function isValidCard($card): bool
    {
        return $card instanceof Card && !isset($card['error']);
    }

    /**
     * Add a card to the list of cards to be verified by user, or handle an existing card.
     * If the card is new, add it to the list of cards to be verified by user.
     * If the card already exists in the database, handle it by checking which fields
     * have been added, updated, or removed.
     * @param $card - Card entity
     * @param $cards - Array of cards to be verified by user, by reference
     * @param $errors - Array of errors, by reference
     * @param $rowNumber - Row number in the spreadsheet
     * @return void. Modifies $cards and $errors by reference. Adds 'isNew' field to $card.
     */
    private function addCardOrHandleExisting($card, &$cards, &$errors, $rowNumber)
    {
        $existingCardInDb = $this->checkDbForCardById($card);
        if ($existingCardInDb) {
            $cards[] = $this->handleExistingCard($existingCardInDb, $card);
        } else {
            $card['isNew'] = true;
            $cards[] = $card;
        }
    }

    /**
     * Check if a card with the same ID already exists in the database.
     * If no ID is provided, it is meant to be a new card.
     * @param $card - Card entity
     * @return mixed - Card entity from database if it exists, false otherwise.
     */
    private function checkDbForCardById($card)
    {
        if (!isset($card['id'])) {
            return false;
        }

        return $this->getCardTable()->find()
            ->where(['id' => $card['id']])
            ->first();
    }

    /**
     * Handle existing card from database and add fields specifying which
     * fields were added, updated, or removed.
     * @param $existingCardInDb - Card entity from database
     * @param $importedCard - Card entity from imported spreadsheet
     * @return mixed - Card entity with added fields specifying changes
     */
    private function handleExistingCard($existingCardInDb, $importedCard)
    {
        $fields = [
            "card_type_id",
            "lakota",
            "english",
            "gender",
            "audio", // is actually a comma separated list of audio IDs
            "image_id",
            "video_id",
            "alt_lakota",
            "alt_english",
            "include_review"
        ];

        foreach ($fields as $field) {
            $importedValue = trim($importedCard->get($field)) ?? null;
            $dbValue = trim($existingCardInDb->get($field)) ?? null;

            if ($this->valueAdded($dbValue, $importedValue)) {
                // Replace empty DB card field with new card field
                $existingCardInDb->set($field, $importedValue);
                $existingCardInDb->set($field . "IsAdded", true);
            } else if ($this->valueUpdated($dbValue, $importedValue)) {
                // If both fields are not empty, and they are different, update DB card field with new card field
                $existingCardInDb->set($field, $importedValue);
                $existingCardInDb->set($field . "IsUpdated", true);
            } else if ($this->valueRemoved($dbValue, $importedValue)) {
                // If DB card has field and new card field is empty, keep DB card field, ignore new card's empty field
                $existingCardInDb->set($field, $importedValue);
                $existingCardInDb->set($field . "IsRemoved", true);
            } else {
                // If both fields are empty, do nothing
            }
        }

        return $existingCardInDb;
    }

    /**
     * Checks if a value is empty while also considering 0 and '0' as non-empty,
     * since those are valid values for some fields.
     */
    private function isEmpty($value)
    {
        return empty($value) && $value !== 0 && $value !== '0';
    }

    /**
     * Checks if a value was added.
     * @param $existingValue - Existing value
     * @param $newValue - New value
     * @return bool - True if the value was added, false otherwise
     */
    private function valueAdded($existingValue, $newValue): bool
    {
        return $this->isEmpty($existingValue) && !$this->isEmpty($newValue);
    }

    /**
     * Checks if a value was updated.
     * @param $existingValue - Existing value
     * @param $newValue - New value
     * @return bool - True if the value was updated, false otherwise
     */
    private function valueUpdated($existingValue, $newValue): bool
    {
        return !$this->isEmpty($existingValue) && !$this->isEmpty($newValue) && $existingValue != $newValue;
    }

    /**
     * Checks if a value was removed.
     * @param $existingValue - Existing value
     * @param $newValue - New value
     * @return bool - True if the value was removed, false otherwise
     */
    private function valueRemoved($existingValue, $newValue): bool
    {
        return !$this->isEmpty($existingValue) && $this->isEmpty($newValue);
    }

    /**
     * Find ID associated with a file name and type.
     * If the file name is empty, return null.
     * @param $filename - File name
     * @param $type - File type
     * @return mixed - File ID if found, null otherwise
     */
    private function findFileIdIfExists($filename, $type)
    {
        if (!$filename) {
            return null;
        }

        if ($type === 'audio') {
            $audioIds = [];
            $audioFileNames = explode(',', $filename);
            foreach ($audioFileNames as $audioFileName) {
                $fileId = $this->findFileId(trim($audioFileName), $type);
                if ($fileId === null) {
                    return ['error' => 'Could not find ' . $type . ' file "' . $audioFileName . '".'];
                } else {
                    $audioIds[] = $fileId;
                }
            }
            return $audioIds;
        } else {
            $fileId = $this->findFileId($filename, $type);
            if ($fileId === null) {
                return ['error' => 'Could not find ' . $type . ' file "' . $filename . '".'];
            }
            return $fileId;
        }
    }

    /**
     * Find card type ID based on card type.
     * If the card type is not found, return null.
     * @param string $type - Card type
     * @return mixed - Card type ID if found, null otherwise
     */
    private function findTypeId(string $type): ?int
    {
        $cardTypeCamelCase = ucwords(strtolower($type));
        $typeEntity = $this->getCardtypeTable()->find()
            ->where(['title' => $cardTypeCamelCase])
            ->first();
        return $typeEntity ? $typeEntity->id : null;
    }

    /**
     * Find file ID based on file name and type.
     * If the file is not found, return null.
     * @param string $filename - File name
     * @param string $type - File type
     * @return mixed - File ID if found, null otherwise
     */
    private function findFileId(string $filename, string $type): ?int
    {
        $file = $this->getFilesTable()->find()
            ->where(['file_name' => $filename, 'type' => $type])
            ->first();
        return $file ? $file->id : null;
    }

    /**
     * Validates fields required to save a card to the database.
     * These fields are card_type_id, lakota, english, and gender.
     * @param $row - Array of card data
     * @return string|null - Error message if any required field is missing, null otherwise
     */
    private function validateRequiredFields($language, $english, $gender, $cardTypeString)
    {
        $validCardTypes = array_values($this->getCardtypeTable()->getTypes());
        if (empty($cardTypeString)
            || !in_array($cardTypeString, $validCardTypes)
        ) {
            return 'Card Type is a required field. (' . $cardTypeString . ') received.';
        }

        if (!$language) {
            return Configure::read('LANGUAGE') . ' is a required field.';
        }
        if (!$english) {
            return 'English is a required field.';
        }
        if (!in_array(strtolower($gender), $this->ValidGenders)) {
            return '(' . $gender . ') is not a valid gender. Valid values are ' . implode(', ', $this->ValidGenders) . '.';
        }
        return null;
    }

    /**
     * Create an error array for a row in the spreadsheet.
     * @param $rowNumber - Row number in the spreadsheet
     * @param $value - Value in the row
     * @return array - Error array
     */
    private function err($rowNumber, $value): array
    {
        return ['row' => $rowNumber, 'value' => $value];
    }

    /**
     * Display the cards for verification by user, or upload them to the database,
     * or cancel the upload process. If there are cards passed to the function,
     * that means we are displaying the cards for verification by user.
     * Otherwise we are processing the cards and saving them to the database.
     * @param array $cards - Array of cards to be verified by user.
     */
    private function displayUploadCardsPreview($cards)
    {
        $languageName = Configure::read('LANGUAGE');
        $this->set(compact('cards', 'languageName'));

        $this->request->getSession()->write('cardsData', $cards);

        return $this->redirect(['controller' => 'Cards', 'action' => 'cardsVerify']);
    }

    /**
     * Create zeroed out counters for cards created, updated, and errors.
     * @return array - Array of counters
     */
    private function initializeCounters(): array {
        return [
            'cards_created' => 0,
            'cards_updated' => 0,
            'errors' => 0
        ];
    }

    private function getCheckboxData($cards) {
        if (empty($cards)) {
            return [];
        }

        $options = [
            'card_type_idIsUpdated' => 'cardTypeIdHasUpdates',
            'lakotaIsUpdated' => 'lakotaHasUpdates',
            'englishIsUpdated' => 'englishHasUpdates',
            'genderIsUpdated' => 'genderHasUpdates',
            'alt_lakotaIsUpdated' => 'altLakotaHasUpdates',
            'alt_lakotaIsAdded' => 'altLakotaHasAdditions',
            'alt_lakotaIsRemoved' => 'altLakotaHasRemovals',
            'alt_englishIsUpdated' => 'altEnglishHasUpdates',
            'alt_englishIsAdded' => 'altEnglishHasAdditions',
            'alt_englishIsRemoved' => 'altEnglishHasRemovals',
            'audioIsUpdated' => 'audioHasUpdates',
            'audioIsAdded' => 'audioHasAdditions',
            'audioIsRemoved' => 'audioHasRemovals',
            'image_idIsUpdated' => 'imageIdHasUpdates',
            'image_idIsAdded' => 'imageIdHasAdditions',
            'image_idIsRemoved' => 'imageIdHasRemovals',
            'video_idIsUpdated' => 'videoIdHasUpdates',
            'video_idIsAdded' => 'videoIdHasAdditions',
            'video_idIsRemoved' => 'videoIdHasRemovals',
            'include_reviewIsUpdated' => 'includeReviewHasUpdates',
        ];

        $checkboxData = [];

        foreach ($options as $key => $value) {
            if (!empty(array_filter($cards, function ($card) use ($key) {
                return !empty($card[$key]) && $card[$key] === true;
            }))) {
                $checkboxData[$value] = true;
            } else {
                $checkboxData[$value] = false;
            }
        }

        return $checkboxData;
    }

    /**
     * User has verified the cards and wants to save them to the database.
     * @return mixed - Redirect to the upload page
     */
    public function cardsVerify()
    {
        $cards = $this->request->getSession()->read('cardsData');

        if (!empty($cards)) {
            $this->request->getSession()->delete('cardsData');
            $languageName = Configure::read('LANGUAGE');
            $checkboxData = $this->getCheckboxData($cards);
            $this->set(compact('cards', 'languageName', 'checkboxData'));
            $this->render('cards_verify');
            return;
        }

        $requestData = $this->request->getData();

        // Check if cancel button was pressed
        if (isset($requestData['cancelbtn'])) {
            $this->Flash->success(__('Card creation was aborted.'));
            return $this->redirect(['action' => 'uploadCards']);
        }

        // Decode request data into array of previewed Cards
        if (!isset($requestData['cards'])) {
            return $this->redirect(['action' => 'uploadCards']);
        }

        $previewedCards = json_decode(htmlspecialchars_decode($requestData['cards']), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->Flash->error(__('Error: Invalid JSON format in cards data.'));
            return $this->redirect(['action' => 'uploadCards']);
        }

        $isUpdatedFields = $this->populateFields($requestData, "IsUpdated");
        $isAddedFields = $this->populateFields($requestData, "IsAdded");
        $isRemovedFields = $this->populateFields($requestData, "IsRemoved");
        $counters = $this->initializeCounters();
        $idsOfUpdatedCards = [];

        //iterate and build and save the cards
        foreach ($previewedCards as $card) {
            $this->processCard($card, $isUpdatedFields, $isAddedFields, $isRemovedFields, $counters, $idsOfUpdatedCards);
        }

        $message = $this->createUploadSummaryMsg($counters, $idsOfUpdatedCards);
        $this->Flash->success($message);
        return $this->redirect(['action' => 'uploadCards']);
    }

    private function createUploadSummaryMsg($counters, $idsOfUpdatedCards) {
        $message = "";
        if ($counters['cards_created'] === 1) {
            $message = $counters['cards_created'] . ' card created. ';
        } else {
            $message = $counters['cards_created'] . ' cards created. ';
        }
        if ($counters['cards_updated'] === 1) {
            $message .= $counters['cards_updated'] . ' card updated';
            $message .= ' (ID: ' . implode(', ', $idsOfUpdatedCards) . '). ';
        } else {
            $message .= $counters['cards_updated'] . ' cards updated';
            if (!empty($idsOfUpdatedCards)) {
                $message .= ' (IDs: ' . implode(', ', $idsOfUpdatedCards) . '). ';
            } else {
                $message .= '. ';
            }
        }
        if ($counters['errors'] > 0) {
            $message .= $counters['errors'] . ' errors occurred.';
        }
        return $message;
    }

    private function processCard(
        array $cardData, array $isUpdatedFields, array $isAddedFields, array $isRemovedFields, array &$counters, array &$idsOfUpdatedCards
    ): void {
        if (empty($cardData)) {
            $counters['errors']++;
            return;
        }

        $card = $this->findOrCreateCard($cardData);
        if (!$card) {
            $counters['errors']++;
            return;
        }

        if ($card->get('isNew')) {
            $this->saveNewCard($card, $counters);
        } else {
            $this->updateCardIfNecessary(
                $card, $cardData, $isUpdatedFields, $isAddedFields, $isRemovedFields, $counters, $idsOfUpdatedCards
            );
        }
    }

    /**
     * Find a card by ID in the database. If the card is not found, return null and display error.
     * If the card is found, return the card entity. If ID not supplied by user, create a new card
     * with the data provided.
     * @param $cardData - Array of card data
     * @param $counter - Array of counters for cards created, updated, and errors
     * @return mixed - Card entity if found or created, null otherwise
     */
    private function findOrCreateCard(array $cardData): ?Card
    {
        if (isset($cardData['id']) && $cardData['id'] >= 0) {
            $card = $this->getCardTable()->findById($cardData['id']);
            if (!$card) {
                $this->Flash->error(__('Error: Could not find card with ID ' . $cardData['id']));
                return null;
            }
            $card->set('isNew', false);
        } else {
            $card = $this->getCardTable()->newEntity($cardData);
            $card->set('isNew', true);
        }
        return $card;
    }

    /**
     * Saves a new card to the database. If the card is saved successfully, increment the
     * cards_created counter. If there is an error, increment the errors counter.
     * @param $card - Card entity to save
     * @param $counter - Array of counters for cards created, updated, and errors
     */
    private function saveNewCard(Card $card, array &$counters): void
    {
        $card->set('is_active', 1);
        if (!$this->getCardTable()->save($card)) {
            $counters['errors']++;
            Log::error("Failed to save card: " . print_r($card->getErrors(), true));
        } else {
            $counters['cards_created']++;
        }
    }

    /**
     * Updates a card in the database if any fields have been added, updated, or removed.
     * If the card is updated successfully, increment the cards_updated counter. If there is an error,
     * increment the errors counter.
     * @param $card - Card entity to update
     * @param $cardData - Array of card data
     * @param $isUpdatedFields - Array of fields that have been updated
     * @param $isAddedFields - Array of fields that have been added
     * @param $isRemovedFields - Array of fields that have been removed
     * @param $counters - Array of counters for cards created, updated, and errors
     * @return bool - True if the card was updated, false otherwise
     */
    private function updateCardIfNecessary(
        Card $card,
        array $cardData,
        array $isUpdatedFields,
        array $isAddedFields,
        array $isRemovedFields,
        array &$counters,
        array &$idsOfUpdatedCards
    ): void {
        $fields = [
            "card_type_id",
            "lakota",
            "english",
            "gender",
            "alt_lakota",
            "alt_english",
            "audio",
            "image_id",
            "video_id",
            "include_review",
        ];

        $cardUpdated = false;

        foreach ($fields as $field) {
            if (in_array($card->id, $isUpdatedFields[$field . 'IsUpdated'])
                || in_array($card->id, $isAddedFields[$field . 'IsAdded'])
                || in_array($card->id, $isRemovedFields[$field . 'IsRemoved'])
            ) {
                $card[$field] = $cardData[$field];
                $cardUpdated = true;
            }
        }

        if ($cardUpdated) {
            if ($this->getCardTable()->save($card)) {
                $counters['cards_updated']++;
                $idsOfUpdatedCards[] = $card->id;
            } else {
                $counters['errors']++;
            }
        }
    }

    private function populateFields($data, $suffix)
    {
        $fields = [
            "card_type_id" . $suffix => [],
            "lakota" . $suffix => [],
            "english" . $suffix => [],
            "gender" . $suffix => [],
            "alt_lakota" . $suffix => [],
            "alt_english" . $suffix => [],
            "audio" . $suffix => [],
            "image_id" . $suffix => [],
            "video_id" . $suffix => [],
            "include_review" . $suffix => [],
        ];

        foreach ($fields as $field => &$cardIds) {
            if (isset($data[$field])) {
                $cardIds = $data[$field];
            }
        }

        return $fields;
    }

    public function edit($id = null)
    {
        $languageName = Configure::read('LANGUAGE');
        $card = $this->getCardTable()->get($id, ['contain' => ['image', 'video', 'Dictionary', 'Inflections']]);
        //echo "<pre>"; print_r($card->image);die;
        $cardTypes = $this->getCardtypeTable()->find('all', array())->toArray();
        $cardTypes = array_column($cardTypes, "title", "id");
        //$image = $card
        if ($this->request->is(['PATCH', 'POST', 'PUT'])) {
            $data = $this->request->getData();
            if (!empty($data['audios'])) {
                $data['audio'] = implode(",", $data['audios']);
            } else {
                $data['audio'] = '';
            }
            //echo "<pre>"; print_r($data);die;
            $card = $this->getCardTable()->patchEntity($card, $data);

            if ($card->hasErrors()) {
                $this->Flash->error(__('Please fill in all the fields.'));
                $this->set(compact('card', 'cardTypes', 'languageName'));
                return;
            } else {
                if ($this->getCardTable()->save($card)) {
                    $this->Flash->success(__('The card has been saved.'));
                    return $this->redirect(['action' => 'addCards']);
                }
            }
        }

        $this->set(compact('card', 'cardTypes', 'languageName'));
        $this->render('add_cards');
    }

    //format the error so the user can find and fix the mistake in the excel file

    public function bulkAction()
    {
        $data = $_POST;
        $action = $data['action'];
        $response = array();
        if ($action == 'deletecard') {
            $ids = $data['ids'];
            $rescard = array();
            foreach ($ids as $id) {
                $card = $this->getCardTable()->get($id);
                if ($this->getCardTable()->delete($card)) {
                    $rescard[] = array('id' => $id, 'status' => 'Deleted');
                } else {
                    $rescard[] = array('id' => $id, 'status' => 'Not Deleted');
                }
            }
            $response = array('status' => 'success', 'data' => $rescard);
            echo json_encode($response);
        }
        die;
    }

    //find the ID of a card type, given its title

    public function delete($id = null)
    {
        //die;
        //Hard delete
        $card = $this->getCardTable()->get($id);
        if ($this->getCardTable()->delete($card)) {
            $this->Flash->success(__('The card has been deleted.'));
        } else {
            $this->Flash->error(__('The card could not be deleted. Please, try again.'));
        }
        return $this->redirect(['action' => 'cardsList']);
    }

    //find the ID of a File given a filename and valid formats

    public function cardDeleteWarning()
    {
        $data = $_POST;
        $lessons = array();
        $exercises = array();
        $ids = $data['cardids'];
        // get associated lesson IDs for card
        $lessonCondition = array();
        $lessonCondition['LessonFrameBlocks.type'] = 'card';
        if (is_array($ids)) {
            $lessonCondition['LessonFrameBlocks.card_id IN'] = $ids;
        } else {
            $lessonCondition['LessonFrameBlocks.card_id'] = $ids;
        }
        $lessonFrameBlocksCount = $this->getLessonFrameBlocksTable()
            ->find('all', [
                'conditions' => $lessonCondition,
                'keyField' => 'id',
                'valueField' => 'lesson_frame_id'])
            ->count();

        $lessonIds = array();
        if ($lessonFrameBlocksCount > 0) {
            $lessonFrameBlocks = $this->getLessonFrameBlocksTable()
                ->find('list', [
                    'conditions' => $lessonCondition,
                    'keyField' => 'id',
                    'valueField' => 'lesson_frame_id'])
                ->toArray();
            $lessonFrameIds = array_unique(array_values($lessonFrameBlocks));

            $lessonFrame = $this->getLessonFramesTables()
                ->find('list', [
                    'conditions' => ['id In' => $lessonFrameIds],
                    'keyField' => 'id',
                    'valueField' => 'lesson_id'])
                ->toArray();
            $lessonIds = array_unique(array_values($lessonFrame));
        }

        // get associated exercise IDs for card
        $exerciseCondition = array();
        $exerciseCondition['OR'] = array();
        if (is_array($ids)) {
            $exerciseCondition['OR']['Exerciseoptions.card_id IN'] = $ids;
            $exerciseCondition['OR']['Exerciseoptions.responce_card_id IN'] = $ids;
        } else {
            $exerciseCondition['OR']['Exerciseoptions.card_id'] = $ids;
            $exerciseCondition['OR']['Exerciseoptions.responce_card_id'] = $ids;
        }
        $exerciseFrameBlocksCount = $this->getExerciseoptionsTable()
            ->find('all', [
                'conditions' => $exerciseCondition,
                'keyField' => 'id',
                'valueField' => 'exercise_id'])
            ->count();

        $exerciseIds = array();
        if ($exerciseFrameBlocksCount != 0) {
            $exerciseOptions = $this->getExerciseoptionsTable()
                ->find('list', [
                    'conditions' => $exerciseCondition,
                    'keyField' => 'id',
                    'valueField' => 'exercise_id'])
                ->toArray();
            $exerciseIds = array_unique(array_values($exerciseOptions));
        }
        if (empty($lessonIds) && empty($exerciseIds)) {
            echo 'success';
            die;
        } else {
            if (!empty($lessonIds)) {
                $lessons = $this->getLessonsTable()
                    ->find('all', [
                        'conditions' => ['id IN' => $lessonIds]])
                    ->toArray();
            }
            if (!empty($exerciseIds)) {
                $exercises = $this->getExercisesTables()
                    ->find('all', [
                        'conditions' => ['id IN' => $exerciseIds]])
                    ->toArray();
            }
            ?>
              <div class="row">
                  <div class='col-sm-12 col-md-12'>
                      This cards are associated with Lessons and exercises.
                      Please change the card and try to delete again.
                  </div>
                  <div class='col-sm-6 col-md-6'>
                      <h3>Lessons List</h3>
                   <?php
                    foreach ($lessons as $l) {
                        ?>
                           <div><a href="<?php echo Router::url('/admin/lessons/manage-lesson/') . $l['id']; ?>"
                                   target="_blank"><i class="fa fa-pencil"></i> <?php echo $l['name']; ?></a></div>
                        <?php
                    }
                    if (empty($lessons)) {
                        echo 'No Lesson Found.';
                    }
                    ?>
                  </div>
                  <div class='col-sm-6 col-md-6'>
                      <h3>Excercise List</h3>
                   <?php
                    foreach ($exercises as $e) {
                        ?>
                           <div><a href="<?php echo Router::url('/admin/exercises/manage-exercises/') . $e['id']; ?>"
                                   target="_blank"><i class="fa fa-pencil"></i> <?php echo $e['name']; ?></a></div>
                        <?php
                    }
                    if (empty($exercises)) {
                        echo 'No exercise Found.';
                    }
                    ?>
                  </div>
              </div>
            <?php
        }
        die;
    }
}
