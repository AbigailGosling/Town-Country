<?php
require_once('functions.php');
$dups = prepareExecuteQuery('SELECT `pickerSheets`.transaction_id FROM `pickerSheets` WHERE transaction_id IS NOT NULL GROUP BY transaction_id HAVING count(*) > 1');
while ($dup = $dups->fetch_assoc())
{
    prepareExecuteQuery('UPDATE `pickerSheets` SET `transaction_id` = NULL WHERE deleted = 1 AND `transaction_id` = ?','s',[$dup['transaction_id']]);
}
prepareExecuteQuery('ALTER TABLE `tandc_live`.`pickerSheets` ADD UNIQUE `transaction_id` (`transaction_id`)');
?>
