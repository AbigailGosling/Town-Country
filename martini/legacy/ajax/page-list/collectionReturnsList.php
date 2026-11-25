<?php

use App\Models\CollectionReturnMetadata;
use Carbon\Carbon;

require_once(__DIR__ . '/../../functions.php');

$searchTerm = trim((string) request()->input('searchterm', ''));
$typeSelector = trim((string) request()->input('type_selector', 'all'));
$dateFrom = trim((string) request()->input('date_from', ''));
$dateTo = trim((string) request()->input('date_to', ''));

$query = CollectionReturnMetadata::query()->orderByDesc('created_at');

if ($typeSelector !== '' && $typeSelector !== 'all') {
    $query->where('type_selector', $typeSelector);
}

if ($searchTerm !== '') {
    $searchTermLike = '%' . $searchTerm . '%';
    $query->where(function ($q) use ($searchTermLike) {
        $q->where('transaction_id', 'like', $searchTermLike)
            ->orWhere('order_reference_number', 'like', $searchTermLike)
            ->orWhere('invoice_id', 'like', $searchTermLike)
            ->orWhere('client_id', 'like', $searchTermLike);
    });
}

if ($dateFrom !== '') {
    $dateFromValue = Carbon::createFromFormat('d/m/Y', $dateFrom)->format('Y-m-d');
    $query->whereDate('estimated_delivery_date', '>=', $dateFromValue);
}

if ($dateTo !== '') {
    $dateToValue = Carbon::createFromFormat('d/m/Y', $dateTo)->format('Y-m-d');
    $query->whereDate('estimated_delivery_date', '<=', $dateToValue);
}

$records = $query->get();

if ($records->isEmpty()) {
    echo '<tr><td colspan="7" align="center" style="padding:20px;">No collection / return records found.</td></tr>';
    return;
}

foreach ($records as $row) {
    $typeLabel = $row->type_selector === 'collection' ? 'Supplier Collection' : 'Customer Return';
    $clientLabel = 'Unknown';
    $clientId = (int) $row->client_id;

    if ($row->is_supplier) {
        $supplierRow = prepareExecuteQuery('SELECT * FROM `supplier` WHERE `id` = ?', 'i', [$clientId])->fetch_assoc();
        $clientLabel = $supplierRow['name'] ?? 'Supplier ' . $clientId;
    } else {
        $customerRow = prepareExecuteQuery('SELECT * FROM `customers` WHERE `id` = ?', 'i', [$clientId])->fetch_assoc();
        $clientLabel = $customerRow['businessname'] ?? 'Customer ' . $clientId;
    }

    $invoiceLabel = $row->invoice_id ? 'Invoice ' . $row->invoice_id : 'Manual entry';
    $deliveryDate = $row->estimated_delivery_date ? Carbon::parse($row->estimated_delivery_date)->format('d/m/Y') : '-';
    $submittedBy = $row->submitted_by ? (prepareExecuteQuery('SELECT `name` FROM `users` WHERE `id` = ?', 'i', [$row->submitted_by])->fetch_assoc()['name'] ?? 'User ' . $row->submitted_by) : '-';
    ?>
    <tr class="pages">
        <td align="center" class="pos">
            <a href="collectionReturnNote.php?id=<?php echo (int) $row->id; ?>" class="intake" style="padding-left:10px;padding-right:10px;">
                <table width="100%" border="0">
                    <tr>
                        <td width="90" align="left">ID: <?php echo (int) $row->id; ?></td>
                        <td width="150" align="left"><?php echo $typeLabel; ?></td>
                        <td width="220" align="left"><?php echo htmlspecialchars($clientLabel); ?></td>
                        <td width="200" align="left"><?php echo htmlspecialchars($invoiceLabel); ?></td>
                        <td width="120" align="left"><?php echo htmlspecialchars($deliveryDate); ?></td>
                        <td width="150" align="left"><?php echo htmlspecialchars($submittedBy); ?></td>
                    </tr>
                </table>
            </a>
        </td>
    </tr>
    <?php
}
