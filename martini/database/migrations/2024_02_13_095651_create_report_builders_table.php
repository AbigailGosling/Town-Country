<?php

use App\Models\Report;
use App\Models\ReportColumn;
use App\Models\ReportVersion;
use App\Models\ReportVersionColumn;
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
        Schema::connection("tandc_live")->create('reports', function (Blueprint $table) {
            $table->id();
            $table->integer("author_id")->index();
            $table->string("name");
            $table->timestamps();
        });
        Schema::connection("tandc_live")->create('report_versions', function (Blueprint $table) {
            $table->id();
            $table->integer("report_id")->index();
            $table->integer("version");
            $table->timestamps();
        });
        Schema::connection("tandc_live")->create('report_columns', function (Blueprint $table) {
            $table->id();
            $table->string("label");
            $table->string("data_type");
            $table->string("processing_type");
            $table->string("html_header");
            $table->string("html_cell");
            $table->string("html_footer");
            $table->text("pointers")->nullable();
            $table->text("metadata")->nullable();
        });
        Schema::connection("tandc_live")->create('report_version_column', function (Blueprint $table) {
            $table->id();
            $table->integer("report_version_id")->index();
            $table->integer("report_column_id")->index();
            $table->integer("order");
        });
        $columns = array(
            array(  
                "label"             => "NOTE",
                "data_type"         => "string",
                "processing_type"   => "item_type",
                "html_header"       => "%s",
                "html_cell"         => "%s",
                "html_footer"       => "",
                "pointers"          => NULL,
                "metadata"          => NULL,
            ),
            array(  
                "label"             => "User",
                "data_type"         => "string",
                "processing_type"   => "none",
                "html_header"       => "%s",
                "html_cell"         => "%s",
                "html_footer"       => "",
                "pointers"          => ["users.name"],
                "metadata"          => NULL,
            ),
            array(  
                "label"             => "Date Created",
                "data_type"         => "date",
                "processing_type"   => "date_format",
                "html_header"       => "%s",
                "html_cell"         => "%s",
                "html_footer"       => "",
                "pointers"          => ["pickerSheets.date"],
                "metadata"          => ['format_from' => "Y-m-d H:i:s", 'format_to' => "d/m/Y H:i:s"],
            ),
            array(  
                "label"             => "Date Assembled",
                "data_type"         => "date",
                "processing_type"   => "date_format",
                "html_header"       => "%s",
                "html_cell"         => "%s",
                "html_footer"       => "",
                "pointers"          => ["pickerSheets.date_completed"],
                "metadata"          => ['format_from' => "Y-m-d H:i:s", 'format_to' => "d/m/Y H:i:s"],
            ),
            array(  
                "label"             => "Date Delivered",
                "data_type"         => "date",
                "processing_type"   => "date_format",
                "html_header"       => "%s",
                "html_cell"         => "%s",
                "html_footer"       => "",
                "pointers"          => ["pickerSheets.estimated_delivery_date"],
                "metadata"          => ['format_from' => "d/m/Y", 'format_to' => "d/m/Y"],
            ),
            array(  
                "label"             => "Customer",
                "data_type"         => "string",
                "processing_type"   => "none",
                "html_header"       => "%s",
                "html_cell"         => "%s",
                "html_footer"       => "",
                "pointers"          => ["customers.businessname"],
                "metadata"          => NULL,
            ),
            array(  
                "label"             => "Invoice",
                "data_type"         => "int",
                "processing_type"   => "none",
                "html_header"       => "%s",
                "html_cell"         => "%d",
                "html_footer"       => "",
                "pointers"          => ["pickerSheets.id"],
                "metadata"          => NULL,
            ),
            array(  
                "label"             => "Intake ID",
                "data_type"         => "int",
                "processing_type"   => "none",
                "html_header"       => "%s",
                "html_cell"         => "%d",
                "html_footer"       => "",
                "pointers"          => ["intake.id"],
                "metadata"          => NULL,
            ),
            array(  
                "label"             => "Transport",
                "data_type"         => "string",
                "processing_type"   => "none",
                "html_header"       => "%s",
                "html_cell"         => "%s",
                "html_footer"       => "",
                "pointers"          => NULL,//["transport.name"],
                "metadata"          => NULL,
            ),
            array(  
                "label"             => "Pallet ID",
                "data_type"         => "int",
                "processing_type"   => "none",
                "html_header"       => "%s",
                "html_cell"         => "%d",
                "html_footer"       => "",
                "pointers"          => ["pallet.id"],
                "metadata"          => NULL,
            ),
            array(  
                "label"             => "Species",
                "data_type"         => "string",
                "processing_type"   => "none",
                "html_header"       => "%s",
                "html_cell"         => "%s",
                "html_footer"       => "",
                "pointers"          => ["species.name"],
                "metadata"          => NULL,
            ),
            array(  
                "label"             => "Group",
                "data_type"         => "string",
                "processing_type"   => "none",
                "html_header"       => "%s",
                "html_cell"         => "%s",
                "html_footer"       => "",
                "pointers"          => ["cutgroups.name"],
                "metadata"          => NULL,
            ),
            array(  
                "label"             => "Cut",
                "data_type"         => "string",
                "processing_type"   => "none",
                "html_header"       => "%s",
                "html_cell"         => "%s",
                "html_footer"       => "",
                "pointers"          => ["cuts.name"],
                "metadata"          => NULL,
            ),
            array(  
                "label"             => "Brand",
                "data_type"         => "string",
                "processing_type"   => "none",
                "html_header"       => "%s",
                "html_cell"         => "%s",
                "html_footer"       => "",
                "pointers"          => ["brands.name"],
                "metadata"          => NULL,
            ),
            array(  
                "label"             => "Supplier",
                "data_type"         => "string",
                "processing_type"   => "none",
                "html_header"       => "%s",
                "html_cell"         => "%s",
                "html_footer"       => "",
                "pointers"          => ["supplier.name"],
                "metadata"          => NULL,
            ),
            array(  
                "label"             => "Nationality",
                "data_type"         => "string",
                "processing_type"   => "none",
                "html_header"       => "%s",
                "html_cell"         => "%s",
                "html_footer"       => "",
                "pointers"          => ["nationality.name"],
                "metadata"          => NULL,
            ),
            array(  
                "label"             => "Temp",
                "data_type"         => "string",
                "processing_type"   => "none",
                "html_header"       => "%s",
                "html_cell"         => "%s",
                "html_footer"       => "",
                "pointers"          => ["temperature.temperature"],
                "metadata"          => NULL,
            ),
            array(  
                "label"             => "Cases",
                "data_type"         => "int",
                "processing_type"   => "none",
                "html_header"       => "%s",
                "html_cell"         => "%d",
                "html_footer"       => "%d",
                "pointers"          => ["weights.rows"],
                "metadata"          => ['filters'=>['product.unit'=>'C'],'footer'=>'array_sum'],
            ),
            array(  
                "label"             => "G/T",
                "data_type"         => "double",
                "processing_type"   => "none",
                "html_header"       => "%s",
                "html_cell"         => "%d",
                "html_footer"       => "%d",
                "pointers"          => ["product.ubbb"],
                "metadata"          => ['filters'=>['product.unit'=>'P'],'footer'=>'array_sum'],
            ),            
            array(  
                "label"             => "PPC",
                "data_type"         => "int",
                "processing_type"   => "none",
                "html_header"       => "%s",
                "html_cell"         => "%d",
                "html_footer"       => "%d",
                "pointers"          => ["product.ubbb"],
                "metadata"          => ['filters'=>['product.unit'=>'PPC'],'footer'=>'array_sum'],
            ),        
            array(  
                "label"             => "kg",
                "data_type"         => "double",
                "processing_type"   => "none",
                "html_header"       => "%s",
                "html_cell"         => "%d",
                "html_footer"       => "%d",
                "pointers"          => ["weights.weight_tear"],
                "metadata"          => ['footer'=>'array_sum'],
            ),        
            array(  
                "label"             => "Cost/Unit",
                "data_type"         => "currency",
                "processing_type"   => "none",
                "html_header"       => "%s",
                "html_cell"         => "%d",
                "html_footer"       => "%d",
                "pointers"          => ["product.cost"],
                "metadata"          => NULL,
            ),                    
            array(  
                "label"             => "Cost Value",
                "data_type"         => "currency",
                "processing_type"   => "calculate",
                "html_header"       => "%s",
                "html_cell"         => "%d",
                "html_footer"       => "%d",
                "pointers"          => null,
                "metadata"          => ['calculate'=>['operator'=>'*','args'=>["this.kg","this.Cost/Unit"]],'footer'=>'array_sum'],
            ),
            array(  
                "label"             => "Sell/Unit",
                "data_type"         => "currency",
                "processing_type"   => "none",
                "html_header"       => "%s",
                "html_cell"         => "%d",
                "html_footer"       => "%d",
                "pointers"          => ["pickerItems.price"],
                "metadata"          => NULL,
            ),                       
            array(  
                "label"             => "Sell Value",
                "data_type"         => "currency",
                "processing_type"   => "calculate",
                "html_header"       => "%s",
                "html_cell"         => "%d",
                "html_footer"       => "%d",
                "pointers"          => NULL,
                "metadata"          => ['calculate'=>['operator'=>'*','args'=>["this.kg","this.Sell/Unit"]],'footer'=>'array_sum'],
            ),
            array(  
                "label"             => "Profit",
                "data_type"         => "currency",
                "processing_type"   => "calculate",
                "html_header"       => "%s",
                "html_cell"         => "%d",
                "html_footer"       => "%d",
                "pointers"          => NULL,
                "metadata"          => ['calculate'=>['operator'=>'-','args'=>["this.Sell Value","this.Cost Value"]],'footer'=>'array_sum'],
            ), 
            array(  
                "label"             => "Actual Cost/Unit",
                "data_type"         => "currency",
                "processing_type"   => "none",
                "html_header"       => "%s",
                "html_cell"         => "%d",
                "html_footer"       => "%d",
                "pointers"          => ["product.price"],
                "metadata"          => ['fallback'=>'this.Cost/Unit'],
            ),                    
            array(  
                "label"             => "Actual Cost Value",
                "data_type"         => "currency",
                "processing_type"   => "calculate",
                "html_header"       => "%s",
                "html_cell"         => "%d",
                "html_footer"       => "%d",
                "pointers"          => NULL,
                "metadata"          => ['calculate'=>['operator'=>'*','args'=>["this.kg","this.Actual Cost/Unit"]],'footer'=>'array_sum'],
            ),
            array(  
                "label"             => "Actual Sell/Unit",
                "data_type"         => "currency",
                "processing_type"   => "none",
                "html_header"       => "%s",
                "html_cell"         => "%d",
                "html_footer"       => "%d",
                "pointers"          => ["pickerItems.price"],
                "metadata"          => NULL,
            ),                       
            array(  
                "label"             => "Actual Sell Value",
                "data_type"         => "currency",
                "processing_type"   => "calculate",
                "html_header"       => "%s",
                "html_cell"         => "%d",
                "html_footer"       => "%d",
                "pointers"          => NULL,
                "metadata"          => ['calculate'=>['operator'=>'*','args'=>["this.kg","this.Actual Sell/Unit"]],'footer'=>'array_sum'],
            ),
            array(  
                "label"             => "Actual Profit",
                "data_type"         => "currency",
                "processing_type"   => "calculate",
                "html_header"       => "%s",
                "html_cell"         => "%d",
                "html_footer"       => "%d",
                "pointers"          => NULL,
                "metadata"          => ['calculate'=>['operator'=>'-','args'=>["this.Actual Sell Value","this.Actual Cost Value"]],'footer'=>'array_sum'],
            ), 
        );
        foreach ($columns as $column){
            $rc  = new ReportColumn($column);
            $rc->save();
        }
        $r = new Report();
        $r->author_id = 54;
        $r->name = "master";
        $r->save();
        $rv = new ReportVersion();
        $rv->report_id = $r->id;
        $rv->version = 1;
        $rv->save();
        $order = 0;
        foreach(ReportColumn::all() as $rc) {
            $rvc = new ReportVersionColumn();
            $rvc->report_version_id = $rv->id;
            $rvc->report_column_id = $rc->id;
            $rvc->order = $order;
            $rvc->save();
            $order++;
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection("tandc_live")->dropIfExists('reports');
        Schema::connection("tandc_live")->dropIfExists('report_versions');
        Schema::connection("tandc_live")->dropIfExists('report_columns');
        Schema::connection("tandc_live")->dropIfExists('report_version_column');
    }
};
