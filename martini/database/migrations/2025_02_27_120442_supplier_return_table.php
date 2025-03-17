<?php

use App\Models\ReportTable;
use App\Models\ReportTableColumn;
use App\Models\ReportTableLink;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $rt = new ReportTable();
        $rt->name = "Supplier Returns";
        $rt->mode = "debits";
        $rt->isSup = 0;
        $rt->version = 1;
        $rt->save();
        /* @var $linkToCopy ReportTableLink */
        foreach (ReportTableLink::where("table_id",1)->get() as $linkToCopy)
        {
            $newTableLink = new ReportTableLink();
            $newTableLink->report_id = $linkToCopy->report_id;
            $newTableLink->table_id = $rt->id;
            $newTableLink->order = 4;
            $newTableLink->save();
        }
        /* @var $colToCopy ReportTableColumn */
        foreach (ReportTableColumn::where("table_id",1)->get() as $colToCopy)
        {
            $newColToCopy = new ReportTableColumn();
            $newColToCopy->table_id = $rt->id;
            $newColToCopy->column_id = $colToCopy->column_id;
            $newColToCopy->order = $colToCopy->order;
            $newColToCopy->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
