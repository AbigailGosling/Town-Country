<?php

use App\Models\CollectionReturnMetadata;
use App\Models\TransportPalletPickWeight;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

include('includes/frontHeader.php');

$recordId = (int) request()->input('id', 0);
if ($recordId <= 0) {
    header('Location: collectionReturnsList.php');
    exit;
}

$record = CollectionReturnMetadata::find($recordId);
if (!$record) {
    header('Location: collectionReturnsList.php');
    exit;
}

$approveError = trim((string) request()->input('error', ''));
$approveSuccess = trim((string) request()->input('success', ''));

$typeLabel = $record->type_selector === 'collection' ? 'Supplier Collection' : 'Customer Return';
$invoiceLabel = $record->invoice_id ? (string) $record->invoice_id : 'Manual entry';
$deliveryDate = $record->estimated_delivery_date ? date('d/m/Y', strtotime((string) $record->estimated_delivery_date)) : '-';
$approvedDate = $record->approved_date ? date('d/m/Y H:i:s', strtotime((string) $record->approved_date)) : 'Pending';
$submittedDate = $record->created_at ? date('d/m/Y H:i:s', strtotime((string) $record->created_at)) : '-';

$clientName = 'Unknown';
$invoiceAddressLines = [];
$deliveryAddressLines = [];

if ((bool) $record->is_supplier) {
    $supplier = prepareExecuteQuery('SELECT * FROM `supplier` WHERE `id` = ?', 'i', [(int) $record->client_id])->fetch_assoc();
    $clientName = $supplier['name'] ?? 'Supplier ' . (int) $record->client_id;

    $invoiceAddressLines = array_filter([
        $clientName,
        $supplier['address_1'] ?? '',
        $supplier['address_2'] ?? '',
        $supplier['address_3'] ?? '',
        $supplier['address_4'] ?? '',
        $supplier['postcode'] ?? '',
        $supplier['contact_number'] ?? '',
    ]);
    $deliveryAddressLines = $invoiceAddressLines;
} else {
    $customer = prepareExecuteQuery('SELECT * FROM `customers` WHERE `id` = ?', 'i', [(int) $record->client_id])->fetch_assoc();
    $clientName = $customer['businessname'] ?? 'Customer ' . (int) $record->client_id;

    $invoiceAddressLines = array_filter([
        $clientName,
        $customer['tradingas'] ?? '',
        $customer['accounts_address_1'] ?? '',
        $customer['accounts_address_2'] ?? '',
        $customer['accounts_address_3'] ?? '',
        $customer['accounts_address_4'] ?? '',
        $customer['contactnumber'] ?? ($customer['tel_number'] ?? ''),
    ]);

    $addressId = (int) ($record->address_id ?: 1);
    $deliveryAddress = prepareExecuteQuery(
        'SELECT * FROM `client_addresses` WHERE `client_id` = ? AND `address_id` = ? AND `client_type` = ? LIMIT 1',
        'iis',
        [(int) $record->client_id, $addressId, 'customer']
    )->fetch_assoc();

    if ($deliveryAddress) {
        $deliveryAddressLines = array_filter([
            $clientName,
            $deliveryAddress['address_1'] ?? '',
            $deliveryAddress['address_2'] ?? '',
            $deliveryAddress['address_3'] ?? '',
            $deliveryAddress['address_4'] ?? '',
            $deliveryAddress['postcode'] ?? '',
            $deliveryAddress['address_number'] ?? '',
        ]);
    } else {
        $deliveryAddressLines = $invoiceAddressLines;
    }
}

$submittedBy = '-';
if ($record->submitted_by) {
    $submittedByRow = prepareExecuteQuery('SELECT `name` FROM `users` WHERE `id` = ?', 'i', [(int) $record->submitted_by])->fetch_assoc();
    $submittedBy = $submittedByRow['name'] ?? 'User ' . (int) $record->submitted_by;
}

$approvedBy = 'Pending';
if ($record->approved_by) {
    $approvedByRow = prepareExecuteQuery('SELECT `name` FROM `users` WHERE `id` = ?', 'i', [(int) $record->approved_by])->fetch_assoc();
    $approvedBy = $approvedByRow['name'] ?? 'User ' . (int) $record->approved_by;
}

$currentUser = User::find(Auth::id());
$canApprove = $currentUser && ($currentUser->hasPermission('approve_intake') || $currentUser->isAdmin());
$isApproved = $record->approved_by !== null && $record->approved_date !== null;

$itemsByName = [];
foreach (TransportPalletPickWeight::where('transport_pallet_id', (int) $record->transport_pallet_id)->get() as $link) {
    $pickWeightOut = $link->pickWeightOut;
    if (!$pickWeightOut) {
        continue;
    }

    foreach ($pickWeightOut->getCutQuantities() as $cut) {
        $itemName = trim((string) ($cut['cut_name'] ?? ''));
        if ($itemName === '') {
            $itemName = 'Unknown item';
        }

        if (!isset($itemsByName[$itemName])) {
            $itemsByName[$itemName] = [
                'name' => $itemName,
                'quantity' => 0,
                'weight' => 0.0,
                'unit' => 'kg',
            ];
        }

        $itemsByName[$itemName]['quantity'] += (int) ($cut['quantity'] ?? 0);
        $itemsByName[$itemName]['weight'] += (float) ($cut['total_weight'] ?? 0);
    }
}

$items = array_values($itemsByName);
if (empty($items)) {
    $items[] = [
        'name' => 'No item details recorded',
        'quantity' => 0,
        'weight' => 0,
        'unit' => 'kg',
    ];
}

$totalWeight = 0.0;
$totalQty = 0;
foreach ($items as $item) {
    $totalWeight += (float) $item['weight'];
    $totalQty += (int) $item['quantity'];
}
?>

<div id="top">
    <a href="menu.php" id="menu">MENU</a>
    <a href="logout" id="logout">LOGOUT</a>
</div>

<style type="text/css">
    .cr-grid {
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 20px;
    }

    .cr-card {
        width: 48%;
        padding: 15px;
        border: 1px solid grey;
        box-sizing: border-box;
        background: #fff;
    }

    .cr-card--full {
        width: 100%;
    }

    .cr-card h2 {
        margin: 0 0 10px;
        font-size: 20px;
        color: #222;
    }

    .cr-address {
        line-height: 1.6;
        font-size: 13px;
    }

    .status-message {
        margin: 0 0 12px;
        padding: 10px 12px;
        border: 1px solid #cccccc;
        font-size: 13px;
        background: #fafafa;
    }

    .status-message--success {
        border-color: #79b77b;
        color: #1f6d24;
        background: #edf9ef;
    }

    .status-message--error {
        border-color: #d07f7f;
        color: #8f1d1d;
        background: #feecec;
    }

    @media (max-width: 900px) {
        .cr-card {
            width: 100%;
        }
    }
</style>

<main class="int">
    <a href="collectionReturnsList.php" class="backbtn">< Back</a>

    <?php if ($approveSuccess === 'approved'): ?>
        <div class="status-message status-message--success">Collection / return has been approved.</div>
    <?php endif; ?>
    <?php if ($approveError === 'permission'): ?>
        <div class="status-message status-message--error">You do not have permission to approve this collection / return.</div>
    <?php endif; ?>
    <?php if ($approveError === 'invalid'): ?>
        <div class="status-message status-message--error">Unable to approve this collection / return record.</div>
    <?php endif; ?>

    <div class="overview">
        <div class="overview_block">
            <label>Record ID</label>
            <?php echo (int) $record->id; ?>
        </div>
        <div class="overview_block">
            <label>Type</label>
            <?php echo htmlspecialchars($typeLabel); ?>
        </div>
        <div class="overview_block">
            <label>Client</label>
            <?php echo htmlspecialchars($clientName); ?>
        </div>
        <div class="overview_block">
            <label>Invoice</label>
            <?php echo htmlspecialchars($invoiceLabel); ?>
        </div>
        <div class="overview_block">
            <label>Transport Pallet</label>
            <?php echo (int) ($record->transport_pallet_id ?? 0); ?>
        </div>
        <div class="overview_block">
            <label>Delivery Date</label>
            <?php echo htmlspecialchars($deliveryDate); ?>
        </div>
        <div class="overview_block">
            <label>Submitted By</label>
            <?php echo htmlspecialchars($submittedBy); ?>
        </div>
        <div class="overview_block">
            <label>Submitted At</label>
            <?php echo htmlspecialchars($submittedDate); ?>
        </div>
        <div class="overview_block">
            <label>Approved By</label>
            <?php echo htmlspecialchars($approvedBy); ?>
        </div>
        <div class="overview_block">
            <label>Approved Date</label>
            <?php echo htmlspecialchars($approvedDate); ?>
        </div>
        <div class="overview_block">
            <label>Total Items / Weight</label>
            <?php echo (int) $totalQty; ?> / <?php echo number_format($totalWeight, 3); ?> kg
        </div>
        <div style="clear:both;"></div>
    </div>

    <br/>

    <table border="1" cellpadding="5" width="100%">
        <tr>
            <td colspan="3" align="center"><b>Collection / Return Approval</b></td>
        </tr>
        <tr>
            <?php if ($isApproved): ?>
                <td style="width:33%"><b style="color:green">Record Approved</b></td>
                <td align="center" style="width:33%"><?php echo htmlspecialchars($approvedBy); ?></td>
                <td align="right" style="width:33%"><?php echo htmlspecialchars($approvedDate); ?></td>
            <?php else: ?>
                <td><b style="color:red">Record Not Yet Approved</b></td>
                <?php if ($canApprove): ?>
                    <td style="width:50%" align="right">
                        <form id="approveCollectionReturn" method="POST" action="scripts/approveCollectionReturn.php">
                            <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
                            <input type="hidden" name="collection_return_id" value="<?php echo (int) $record->id; ?>">
                            <input id="approveCollectionReturnBtn" type="button" onclick="approvingCollectionReturn();" value="Approve Record">
                        </form>
                    </td>
                <?php endif; ?>
            <?php endif; ?>
        </tr>
    </table>

    <br/><br/>

    <div class="cr-grid">
        <div class="cr-card">
            <h2>Invoice Address</h2>
            <div class="cr-address">
                <?php echo implode('<br/>', array_map('htmlspecialchars', $invoiceAddressLines)); ?>
            </div>
        </div>
        <div class="cr-card">
            <h2>Collection Address</h2>
            <div class="cr-address">
                <?php echo implode('<br/>', array_map('htmlspecialchars', $deliveryAddressLines)); ?>
            </div>
        </div>
        <div class="cr-card cr-card--full">
            <h2>Collection / Return Notes</h2>
            <?php echo nl2br(htmlspecialchars((string) ($record->picksheet_note ?: 'No notes supplied.'))); ?>
        </div>
    </div>

    <br/><br/>

    <table border="1" cellpadding="5" width="100%">
        <tr>
            <td colspan="4" align="center"><b>Items Overview</b></td>
        </tr>
        <tr>
            <th style="background:#3faddd;">Item</th>
            <th style="background:#3faddd;">Cases</th>
            <th style="background:#3faddd;">Total Weight (kg)</th>
            <th style="background:#3faddd;">Unit</th>
        </tr>
        <?php foreach ($items as $item): ?>
            <tr>
                <td><?php echo htmlspecialchars((string) $item['name']); ?></td>
                <td><?php echo (int) $item['quantity']; ?></td>
                <td><?php echo number_format((float) $item['weight'], 3); ?></td>
                <td><?php echo htmlspecialchars((string) $item['unit']); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</main>

<div id="btm"></div>
<script type="text/javascript">
    function approvingCollectionReturn() {
        var btn = document.getElementById('approveCollectionReturnBtn');
        if (btn) {
            btn.disabled = true;
            btn.value = 'Approving...';
        }
        document.getElementById('approveCollectionReturn').submit();
    }
</script>
