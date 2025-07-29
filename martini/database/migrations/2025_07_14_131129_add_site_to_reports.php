<?php

use App\Models\ReportColumn;
use App\Models\ReportTable;
use App\Models\ReportTableColumn;
use Illuminate\Database\Eloquent\Collection;
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

        //Condense Order Number
        foreach(ReportTable::all() as $rt)
        {
            foreach(ReportTableColumn::where("table_id",$rt->id)->orderBy("order","ASC")->get() as $index=>$rtc)
            {
                $rtc->order = $index;
                $rtc->save();
            }
        }

        $t = array(
            "label"             => ["Site Name"],
            "data_type"         => "string",
            "processing_type"   => "none",
            "header"            => "%s",
            "cell"              => "%s",
            "footer"            => "%s",
            "pointers"          => ["site.name"],
            );
        $rc = new ReportColumn($t);
        $rc->save();

        foreach(ReportTable::all() as $rt)
        {
            $cols = ReportTableColumn::where("table_id",$rt->id)->orderBy("order","ASC")->get();
            if ($cols->contains("column_id",13) && $cols->contains("column_id",14)) $this->injectAfter($rc,$cols,14);
            else if ($cols->contains("column_id",13)) $this->injectAfter($rc,$cols,13);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
    private function injectAfter(ReportColumn $rc,Collection $cols,int $afterColID)
    {
        foreach ($cols->where("column_id",">",$afterColID) as $rtc)
        {
            $rtc->order = $rtc->order+1;
            $rtc->save();
        }
        foreach ($cols->where("column_id","=",$afterColID) as $rtc)
        {
            $newRTC = new ReportTableColumn([
                "table_id"=>$rtc->table_id,
                "column_id"=>$rc->id,
                "order"=>$rtc->order+1
            ]);
            $newRTC->save();
        }
    }
};
