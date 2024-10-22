<?php

use App\Models\ReportColumn;
use App\Models\ReportTable;
use App\Models\ReportTableColumn;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $t = array(
            "label"             => ["Credit Num"],
            "data_type"         => "id",
            "processing_type"   => "none",
            "header"            => "%s",
            "cell"              => "%s",
            "footer"            => "%s",
            "pointers"          => ["credit_note_items.id"],
            );
        $rc = new ReportColumn($t);
        $rc->save();
        foreach (ReportTableColumn::where("column_id",">",14)->get() as $rtc)
        {
            if ($rtc->table_id % 2 == 0)
            {
                $rtc->order = $rtc->order+1;
                $rtc->save();
            }
        }
        foreach (ReportTableColumn::where("column_id","=",14)->get() as $rtc)
        {
            if ($rtc->table_id % 2 == 0)
            {
                $newRTC = new ReportTableColumn([
                    "table_id"=>$rtc->table_id,
                    "column_id"=>$rc->id,
                    "order"=>$rtc->order+1
                ]);
                $newRTC->save();
            }
        }
        $t = array(
            "label"             => ["Health Mark"],
            "data_type"         => "string",
            "processing_type"   => "none",
            "header"            => "%s",
            "cell"              => "%s",
            "footer"            => "%s",
            "pointers"          => ["health_mark.name"],
            );
        $rc1 = new ReportColumn($t);
        $rc1->save();
        $t = array(
            "label"             => ["T&C Number"],
            "data_type"         => "string",
            "processing_type"   => "none",
            "header"            => "%s",
            "cell"              => "%s",
            "footer"            => "%s",
            "pointers"          => ["intake.internal_num"],
            );
        $rc2 = new ReportColumn($t);
        $rc2->save();
        $t = array(
            "label"             => ["Customs Import Entry"],
            "data_type"         => "string",
            "processing_type"   => "none",
            "header"            => "%s",
            "cell"              => "%s",
            "footer"            => "%s",
            "pointers"          => ["intake.import_num"],
            );
        $rc3 = new ReportColumn($t);
        $rc3->save();
        $pallet = ReportColumn::where("label","like","%Pallet ID%")->firstOrFail();
        foreach(ReportTable::all() as $table)
        {
            foreach (ReportTableColumn::where("table_id",$table->id)->where("column_id",">",$pallet->id)->get() as $rtc)
            {
                $rtc->order = $rtc->order+3;
                $rtc->save();
            }
            $rtc = ReportTableColumn::where("table_id",$table->id)->where("column_id","=",$pallet->id)->firstOrFail();
            $newRTC = new ReportTableColumn([
                "table_id"=>$rtc->table_id,
                "column_id"=>$rc1->id,
                "order"=>$rtc->order+1
            ]);
            $newRTC->save();
            $newRTC = new ReportTableColumn([
                "table_id"=>$rtc->table_id,
                "column_id"=>$rc2->id,
                "order"=>$rtc->order+2
            ]);
            $newRTC->save();
            $newRTC = new ReportTableColumn([
                "table_id"=>$rtc->table_id,
                "column_id"=>$rc3->id,
                "order"=>$rtc->order+3
            ]);
            $newRTC->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $rc = ReportColumn::where("label","like","%Credit Num%")->firstOrFail();
        ReportTableColumn::where("column_id","=",$rc->id)->delete();
        $rc->delete();

        foreach (ReportTableColumn::where("column_id",">",14)->get() as $rtc)
        {
            if ($rtc->table_id % 2 == 0)
            {
                $rtc->order = $rtc->order-1;
                $rtc->save();
            }
        }
        $rc = ReportColumn::where("label","like","%Health Mark%")->firstOrFail();
        ReportTableColumn::where("column_id","=",$rc->id)->delete();
        $rc->delete();
        $rc = ReportColumn::where("label","like","%T&C Number%")->firstOrFail();
        ReportTableColumn::where("column_id","=",$rc->id)->delete();
        $rc->delete();
        $rc = ReportColumn::where("label","like","%Customs%")->firstOrFail();
        ReportTableColumn::where("column_id","=",$rc->id)->delete();
        $rc->delete();

        $pallet = ReportColumn::where("label","like","%Pallet ID%")->firstOrFail();
        foreach(ReportTable::all() as $table)
        {
            foreach (ReportTableColumn::where("table_id",$table->id)->where("column_id",">",$pallet->id)->get() as $rtc)
            {
                $rtc->order = $rtc->order-3;
                $rtc->save();
            }
        }
    }
};
