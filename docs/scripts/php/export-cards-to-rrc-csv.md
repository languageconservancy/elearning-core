# Card Export Script for Record Tool CSV

Script: [`core/scripts/php/export-cards-to-rrc-csv.php`](../../../scripts/php/export-cards-to-rrc-csv.php)

This script allows you to export card data from the database to CSV format based on card IDs provided by a linguist.

## How It Works

1. **Linguist provides card IDs**: The linguist gives you a list of card IDs (one per line in a text file)
2. **Script exports data**: The script queries the database for those specific cards and exports them to CSV
3. **CSV format matches your needs**: The output CSV has the fields required for the Record Tool CSV and columns to keep track of the original values (w/ and w/o HTML)

## Important

-   Script must be run on the server where the platform backend is deployed.
-   Run from the **backend** directory so CakePHP can load `vendor/` and `config/`:

```bash
cd core/backend
php ../scripts/php/export-cards-to-rrc-csv.php card_ids.txt exported_cards.csv
```

## Usage

### Basic Usage

```bash
cd core/backend
php ../scripts/php/export-cards-to-rrc-csv.php card_ids.txt exported_cards.csv
```

### Parameters

-   `card_ids.txt`: File containing card IDs (one per line)
-   `exported_cards.csv`: Output CSV file (optional, defaults to `exported_cards.csv`)

### Example

```bash
cd core/backend
php ../scripts/php/export-cards-to-rrc-csv.php card_ids.txt peter_recordings_export.csv
```

## Input Format

The card IDs file should contain one card ID per line:

```
2521
2522
2534
2560
...
```

## Output Format

The script exports a CSV file with the following columns:

| Column             | Description            | Source                                   |
| ------------------ | ---------------------- | ---------------------------------------- |
| ID                 | ID for Record Tool     | index                                    |
| Lexeme             | Lakota text            | `cards.lakota`                           |
| Gloss              | English translation    | `cards.english`                          |
| Notes              | Notes field            | Empty (can be customized)                |
| Image              | Image filename         | `files.file_name` (via `cards.image_id`) |
| Audios             | Audio filename         | `files.file_name` (via `cards.audio`)    |
| AudiosNotes        | Audio notes            | Empty (can be customized)                |
| RRCGroup           | RRC group              | Empty (can be customized)                |
| OriginalLexeme     | Original Lakota        | stripHtml(`cards.lakota`)                |
| OriginalGloss      | Original English       | stripHtml(`cards.english`)               |
| OriginalHTMLLexeme | HTML formatted Lakota  | `cards.lakota` with HTML formatting      |
| OriginalHTMLGloss  | HTML formatted English | `cards.english` with HTML formatting     |
| CardType           | Card type              | `card_types.title`                       |
| gender             | Gender                 | `cards.gender`                           |
| card_id            | Card ID                | `cards.id`                               |

## Customization

### Adding More Fields

To add more fields to the export, modify the `writeCsvFile()` method in [`scripts/php/export-cards-to-rrc-csv.php`](../../../scripts/php/export-cards-to-rrc-csv.php):

1. Add the field to the `$headers` array
2. Add the corresponding data to the `$row` array
3. Update the SQL query if needed

### Example: Adding Notes Field

```php
// In the SQL query, add:
$sql = "
    SELECT
        c.id,
        c.lakota,
        c.english,
        -- Add your custom field here
        c.notes,  -- if you have a notes field
        ...
";

// In writeCsvFile(), update the row array:
$row = [
    $card['id'],
    $card['lakota'],
    $card['english'],
    $card['notes'] ?? '',  // Add your custom field
    // ... rest of fields
];
```

## Error Handling

The script includes error handling for:

-   Missing input files
-   Invalid card IDs
-   Database connection issues
-   File writing permissions

## Requirements

-   PHP 7.4+
-   CakePHP 4.x
-   Database connection configured
-   Write permissions for output directory

## Troubleshooting

### Common Issues

1. **"File not found"**: Make sure the card IDs file exists and the path is correct
2. **"No cards found"**: Check that the card IDs exist in the database
3. **"Could not open file for writing"**: Check write permissions in the output directory
4. **Database connection errors**: Verify your database configuration in `config/app.php`

### Debug Mode

To see more detailed output, you can add debug statements to the script:

```php
// Add this line to see the SQL query
echo "Executing query: $sql\n";
echo "Card IDs: " . implode(', ', $cardIds) . "\n";
```

## Integration with Existing Workflow

This script can be easily integrated into your existing workflow:

1. **Automated processing**: Run the script as part of a batch process
2. **Web interface**: Call the script from a web controller
3. **Scheduled exports**: Use cron jobs to run exports automatically

## Example Web Controller Integration

You could also create a web endpoint for this functionality:

```php
// In a controller
public function exportCards()
{
    $cardIds = $this->request->getData('card_ids');
    $exporter = new CardExporter();

    $filename = 'export_' . date('Y-m-d_H-i-s') . '.csv';
    $filepath = WWW_ROOT . 'exports/' . $filename;

    if ($exporter->exportCards($cardIds, $filepath)) {
        return $this->response->withFile($filepath);
    } else {
        throw new Exception('Export failed');
    }
}
```
