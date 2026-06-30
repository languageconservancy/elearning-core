<?php
/**
 * Card Export Script
 *
 * This script exports card data to CSV format based on card IDs provided by a linguist.
 * Usage: php ../scripts/php/export-cards-to-rrc-csv.php [card_ids_file] [output_file]
 * 
 * [cards_ids_file] should be a file with one card ID per line
 *
 * Run from core/backend/. Example:
 *   php ../scripts/php/export-cards-to-rrc-csv.php card_ids.txt exported_cards.csv
 */

require_once __DIR__ . '/../../backend/vendor/autoload.php';

use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Datasource\ConnectionManager;

// Initialize CakePHP
require_once __DIR__ . '/../../backend/config/bootstrap.php';

class CardExporter
{
    private $connection;

    public function __construct()
    {
        $this->connection = ConnectionManager::get('default');
    }

    /**
     * Export cards to CSV based on card IDs
     *
     * @param array $cardIds Array of card IDs to export
     * @param string $outputFile Output CSV file path
     * @return bool Success status
     */
    public function exportCards($cardIds, $outputFile)
    {
        if (empty($cardIds)) {
            echo "No card IDs provided.\n";
            return false;
        }

        // Build the SQL query to get card data with related information
        $placeholders = str_repeat('?,', count($cardIds) - 1) . '?';
        $sql = "
            SELECT
                c.id,
                c.lakota,
                c.english,
                c.alt_lakota,
                c.alt_english,
                c.gender,
                c.audio,
                c.include_review,
                ct.title as card_type,
                f_audio.file_name as audio_filename,
                f_image.file_name as image_filename,
                f_video.file_name as video_filename,
                c.created,
                c.modified
            FROM cards c
            LEFT JOIN card_types ct ON c.card_type_id = ct.id
            LEFT JOIN files f_audio ON c.audio = f_audio.id
            LEFT JOIN files f_image ON c.image_id = f_image.id
            LEFT JOIN files f_video ON c.video_id = f_video.id
            WHERE c.id IN ($placeholders)
            ORDER BY c.id ASC
        ";

        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($cardIds);
            $cards = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($cards)) {
                echo "No cards found for the provided IDs.\n";
                return false;
            }

            // Write to CSV file
            $this->writeCsvFile($cards, $outputFile);

            echo "Successfully exported " . count($cards) . " cards to $outputFile\n";
            return true;

        } catch (Exception $e) {
            echo "Error exporting cards: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * Write card data to CSV file
     *
     * @param array $cards Card data
     * @param string $outputFile Output file path
     */
    private function writeCsvFile($cards, $outputFile)
    {
        $handle = fopen($outputFile, 'w');

        if (!$handle) {
            throw new Exception("Could not open file for writing: $outputFile");
        }

        // Write header row
        $headers = [
            'ID',
            'Lexeme',
            'Gloss',
            'Notes',
            'Image',
            'Audios',
            'AudiosNotes',
            'RRCGroup',
            'OriginalLexeme',
            'OriginalGloss',
            'OriginalHTMLLexeme',
            'OriginalHTMLGloss',
            'CardType',
            'gender',
            'card_id'
        ];

        fputcsv($handle, $headers);

        // Write data rows
        $rrcId = 1;
        foreach ($cards as $card) {
            $row = [
                $rrcId++,                                    // ID
                '',                                          // Lexeme (without HTML formatting)
                '',                                          // Gloss (without HTML formatting)
                '',                                          // Notes (empty for now)
                '',                                          // Image
                '',                                          // Audios
                '',                                          // AudiosNotes (empty for now)
                '',                                          // RRCGroup (empty for now)
                $this->stripHtml($card['lakota']),           // OriginalLexeme (without HTML formatting)
                $this->stripHtml($card['english']),          // OriginalGloss (without HTML formatting)
                $card['lakota'],                             // OriginalHTMLLexeme (with HTML formatting)
                $card['english'],                            // OriginalHTMLGloss (with HTML formatting)
                $card['card_type'],                          // CardType
                $card['gender'],                             // gender
                $card['id']                                  // card_id
            ];

            fputcsv($handle, $row);
        }

        fclose($handle);
    }

    /**
     * Format Lakota text with HTML formatting
     *
     * @param string $lakota Lakota text
     * @return string Formatted HTML
     */
    private function stripHtml($text)
    {
        if (empty($text)) {
            return '';
        }

        // remove all html tags and replace with spaces
        $text = strip_tags($text);
        $text = str_replace(' ', ' ', $text);

        // remove all html tags and replace with spaces
        $text = strip_tags($text);
        $text = str_replace(' ', ' ', $text);

        return $text;
    }

    /**
     * Read card IDs from a file
     *
     * @param string $filename File containing card IDs (one per line)
     * @return array Array of card IDs
     */
    public function readCardIdsFromFile($filename)
    {
        if (!file_exists($filename)) {
            throw new Exception("File not found: $filename");
        }

        $content = file_get_contents($filename);
        $lines = explode("\n", trim($content));

        $cardIds = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line) && is_numeric($line)) {
                $cardIds[] = (int)$line;
            }
        }

        return $cardIds;
    }
}

// Main execution
if ($argc < 2) {
    echo "Usage: php ../scripts/php/export-cards-to-rrc-csv.php [card_ids_file] [output_file]\n";
    echo "Example: php ../scripts/php/export-cards-to-rrc-csv.php card_ids.txt exported_cards.csv\n";
    exit(1);
}

$cardIdsFile = $argv[1];
$outputFile = $argv[2] ?? 'exported_cards.csv';

try {
    $exporter = new CardExporter();
    $cardIds = $exporter->readCardIdsFromFile($cardIdsFile);

    echo "Found " . count($cardIds) . " card IDs to export.\n";

    $success = $exporter->exportCards($cardIds, $outputFile);

    if ($success) {
        echo "Export completed successfully!\n";
        exit(0);
    } else {
        echo "Export failed.\n";
        exit(1);
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}