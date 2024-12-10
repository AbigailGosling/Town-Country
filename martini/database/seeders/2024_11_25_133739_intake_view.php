<?php

use App\Models\Report;
use App\Models\ReportColumn;
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
        $report = new Report();
        $report->mode = "product";
        $report->name = "Intake Report";
        $report->author_id = 54;
        $report->save();

        $reportOverviewTableI = new ReportTable();
        $reportOverviewTableI->name = "Intake Table";
        $reportOverviewTableI->mode = "intake";
        $reportOverviewTableI->version = 1;
        $reportOverviewTableI->isSup = false;
        $reportOverviewTableI->save();

        $link = new ReportTableLink();
        $link->report_id = $report->id;
        $link->table_id = $reportOverviewTableI->id;
        $link->order = 0;
        $link->save();

        $reportOverviewTableD = new ReportTable();
        $reportOverviewTableD->name = "Overview Table";
        $reportOverviewTableD->mode = "debits";
        $reportOverviewTableD->version = 1;
        $reportOverviewTableD->isSup = false;
        $reportOverviewTableD->save();

        $link = new ReportTableLink();
        $link->report_id = $report->id;
        $link->table_id = $reportOverviewTableD->id;
        $link->order = 1;
        $link->save();

        $reportOverviewTableC = new ReportTable();
        $reportOverviewTableC->name = "Credits Table";
        $reportOverviewTableC->mode = "credits";
        $reportOverviewTableC->version = 1;
        $reportOverviewTableC->isSup = false;
        $reportOverviewTableC->save();

        $link = new ReportTableLink();
        $link->report_id = $report->id;
        $link->table_id = $reportOverviewTableC->id;
        $link->order = 2;
        $link->save();


        $reportOverviewTableR = new ReportTable();
        $reportOverviewTableR->name = "Resales";
        $reportOverviewTableR->mode = "resales";
        $reportOverviewTableR->version = 1;
        $reportOverviewTableR->isSup = false;
        $reportOverviewTableR->save();

        $link = new ReportTableLink();
        $link->report_id = $report->id;
        $link->table_id = $reportOverviewTableR->id;
        $link->order = 3;
        $link->save();

        $reportOverviewTableS = new ReportTable();
        $reportOverviewTableS->name = "Summary";
        $reportOverviewTableS->mode = "summary";
        $reportOverviewTableS->version = 1;
        $reportOverviewTableS->isSup = false;
        $reportOverviewTableS->save();

        $link = new ReportTableLink();
        $link->report_id = $report->id;
        $link->table_id = $reportOverviewTableS->id;
        $link->order = 4;
        $link->save();

        $colIds = $this->makeColArray();

        $this->addCols($colIds,$reportOverviewTableD);
        $this->addCols($colIds,$reportOverviewTableC);
        $this->addCols($colIds,$reportOverviewTableR);
        $this->addCols($colIds,$reportOverviewTableS);
    }
    public function makeColArray()
    {

        $unitTotalCount = new ReportColumn();
        $unitTotalCount->label = ["Qty"];
        $unitTotalCount->data_type = "int";
        $unitTotalCount->processing_type = "none";
        $unitTotalCount->header = "%s";
        $unitTotalCount->cell = "%s";
        $unitTotalCount->footer = "%s";
        $unitTotalCount->pointers = ["weights.rows"];
        $unitTotalCount->metadata = ["footers"=>"array_sum"];
        $unitTotalCount->save();

        $unitType = new ReportColumn();
        $unitType->label = ["Unit"];
        $unitType->data_type = "string";
        $unitType->processing_type = "none";
        $unitType->header = "%s";
        $unitType->cell = "%s";
        $unitType->footer = "%s";
        $unitType->pointers = ["product.unit"];
        $unitType->metadata = null;
        $unitType->save();

        return [2,5,9,13,20,21,17,19,$unitTotalCount->id,$unitType->id,25,27,32,29,30,35];
    }
    public function addCols(array $colIds,ReportTable $rt)
    {

        foreach($colIds as $order=>$id)
        {
            $rtc = new ReportTableColumn();
            $rtc->table_id = $rt->id;
            $rtc->column_id = $id;
            $rtc->order = $order;
            $rtc->save();
        }
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        try{$this->trimmer(ReportTable::where("name","Intake Table")->first());}
        catch(\Exception $ex){}
        try{$this->trimmer(ReportTable::where("name","Overview Table")->first());}
        catch(\Exception $ex2){}
        try{$this->trimmer(ReportTable::where("name","Credits Table")->first());}
        catch(\Exception $ex3){}
        try{$this->trimmer(ReportTable::where("name","Resales")->first());}
        catch(\Exception $ex4){}
        try{$this->trimmer(ReportTable::where("name","Summary")->first());}
        catch(\Exception $ex5){}
    }
    public function trimmer(ReportTable $rt)
    {
        foreach(ReportTableColumn::where("table_id",$rt->id)->get() as $rtc)
        {
            $i = $rtc->id;
            $rtc->forceDelete();
            if (ReportTableColumn::where("column_id",$i)->get()->count()===0)
            {
                try{ReportColumn::findOrFail($i)->forceDelete();}
                catch(\Exception $ex){}
            }
        }
        $rt->forceDelete();
    }
};
