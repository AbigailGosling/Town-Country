<?php

use App\Models\CollectionReturnMetadata;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

include_once('functions.php');

$searchTerm = trim((string) request()->input('searchterm', ''));
$typeSelector = trim((string) request()->input('type_selector', 'all'));
$dateFrom = trim((string) request()->input('date_from', ''));
$dateTo = trim((string) request()->input('date_to', ''));
?>
<!doctype html>
<html class="int">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Collections / Returns</title>
    <link href="css/style.css" rel="stylesheet" type="text/css">
    <link href="css/font-awesome.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://malsup.github.io/jquery.form.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
</head>
<body class="menu">
<div id="top">
    <a href="menu.php" id="menu">MENU</a>
    <a href="logout" id="logout">LOGOUT</a>
</div>
<main>
    <div id="intakelist">
        <h1 class="int">Collections / Returns</h1>
        <input type="text" id="instantSearch" value="<?php echo htmlspecialchars($searchTerm, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Search by transaction, invoice or reference..." style="width:360px;height:28px;padding-left:10px;" enterkeyhint="go">
        <a href="collectionReturnsList.php" class="resetBtn">Clear</a>
        <div class="datesearchcontainer">
            <label>Type</label>
            <select id="typeFilter" style="width:180px;height:32px;padding-left:10px;">
                <option value="all" <?php echo ($typeSelector === 'all' || $typeSelector === '') ? 'selected' : ''; ?>>All</option>
                <option value="return" <?php echo $typeSelector === 'return' ? 'selected' : ''; ?>>Customer Return</option>
                <option value="collection" <?php echo $typeSelector === 'collection' ? 'selected' : ''; ?>>Supplier Collection</option>
            </select>

            <label>From</label>
            <input type="text" id="dateFrom" value="<?php echo htmlspecialchars($dateFrom, ENT_QUOTES, 'UTF-8'); ?>" style="width:110px;height:28px;padding-left:10px;">

            <label>To</label>
            <input type="text" id="dateTo" value="<?php echo htmlspecialchars($dateTo, ENT_QUOTES, 'UTF-8'); ?>" style="width:110px;height:28px;padding-left:10px;">
        </div>

        <div style="margin: 10px 0 18px;">
            <a href="collectionReturns.php" class="intake" style="display:inline-block;padding:10px 16px;border:1px solid #cfcfcf;background:#f8f8f8;">New Collection / Return</a>
        </div>

        <table width="100%" border="0" cellpadding="0" cellspacing="0" id="intakeAjax">
            <?php include('ajax/page-list/collectionReturnsList.php'); ?>
        </table>
    </div>
</main>
<div id="btm"></div>
<script type="text/javascript">
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': "<?php echo csrf_token(); ?>" }
    });

    $(document).ready(function () {
        $('#dateFrom').datepicker({ dateFormat: 'dd/mm/yy', onSelect: doSearch });
        $('#dateTo').datepicker({ dateFormat: 'dd/mm/yy', onSelect: doSearch });

        $('#instantSearch').on('keypress', function (e) {
            if (e.which === 13) {
                doSearch();
            }
        });

        $('#typeFilter').on('change', doSearch);
    });

    function doSearch() {
        var searchterm = $('#instantSearch').val() || '';
        var typeSelector = $('#typeFilter').val() || 'all';
        var dateFrom = $('#dateFrom').val() || '';
        var dateTo = $('#dateTo').val() || '';

        $.ajax({
            type: 'POST',
            url: 'ajax/page-list/collectionReturnsList.php',
            data: {
                searchterm: searchterm,
                type_selector: typeSelector,
                date_from: dateFrom,
                date_to: dateTo
            },
            dataType: 'html'
        }).done(function (data) {
            $('#intakeAjax').html(data);
        }).fail(function () {
            $('#intakeAjax').html('<tr><td>No results available.</td></tr>');
        });
    }
</script>
</body>
</html>
