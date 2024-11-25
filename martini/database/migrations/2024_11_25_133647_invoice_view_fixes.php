<?php

use App\Models\ReportColumn;
use App\Models\ReportTableColumn;
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
        $this->deleter(ReportColumn::where("label","like","%Less Transport%")->first());
        $this->deleter(ReportColumn::where("label","like","%Less Overriders%")->first());
        $this->deleter(ReportColumn::where("label","like","%Less Credits%")->first());
        $this->deleter(ReportColumn::where("label","like","%Less Other%")->first());
    }
    public function deleter(ReportColumn $rc)
    {
        foreach (ReportTableColumn::where("column_id",$rc->id)->get() as $rtc)
        {
            $rtc->forceDelete();
        }
        $rc->forceDelete();
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
