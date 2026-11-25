<?php

use App\Models\CollectionReturnMetadata;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

require(__DIR__.'/../functions.php');

$recordId = (int) request()->input('collection_return_id', 0);
if ($recordId <= 0) {
    header('Location: ../collectionReturnsList.php');
    exit;
}

$record = CollectionReturnMetadata::find($recordId);
if (!$record) {
    header('Location: ../collectionReturnNote.php?id=' . $recordId . '&error=invalid');
    exit;
}

$currentUser = User::find(Auth::id());
if (!$currentUser || (!$currentUser->hasPermission('approve_intake') && !$currentUser->isAdmin())) {
    header('Location: ../collectionReturnNote.php?id=' . $recordId . '&error=permission');
    exit;
}

if ($record->approved_by === null || $record->approved_date === null) {
    $record->approved_by = (int) Auth::id();
    $record->approved_date = Carbon::now();
    $record->save();
}

header('Location: ../collectionReturnNote.php?id=' . $recordId . '&success=approved');
exit;
